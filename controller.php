<?php
namespace Concrete\Package\BeanzGdpr;

use Beanz\Gdpr\CookieType;
use Concrete\Core\Asset\Asset;
use Concrete\Core\Asset\AssetList;
use Concrete\Core\Backup\ContentImporter;
use Concrete\Core\Multilingual\Page\Section\Section;
use Concrete\Core\Package\Package;
use Concrete\Core\Page\Event;
use Concrete\Core\Page\Page;
use Concrete\Core\Page\Type\Type;
use Concrete\Core\Support\Facade\Config;
use Concrete\Core\Support\Facade\Events;
use Concrete\Core\Support\Facade\Site;
use Concrete\Core\User\User;
use Concrete\Core\View\View;

class Controller extends Package
{
    /** @var string */
    protected $pkgHandle = 'beanz_gdpr';

    /** @var string */
    protected $appVersionRequired = '8.2.1';

    /** @var string */
    protected $pkgVersion = '0.9.0';

    /** @var array */
    protected $pkgAutoloaderRegistries = [
        'src/Gdpr' => '\Beanz\Gdpr',
    ];

    public function getPackageName(): string
    {
        return t('GDPR');
    }

    public function getPackageDescription(): string
    {
        return t('Add a GDPR Cookie Disclosure to your website');
    }

    public function install(): void
    {
        parent::install();

        $this->installXml();
        $this->installPrivacyPolicyPages();
        $this->setDefaultConfigValues();
        $this->installCookieTypes();
    }

    public function upgrade(): void
    {
        parent::upgrade();

        $this->installXml();
        $this->installPrivacyPolicyPages();
        $this->setDefaultConfigValues();
    }

    public function on_start()
    {
        $al = AssetList::getInstance();

        $al->register(
            'javascript',
            'ihavecookies',
            'js/jquery.ihavecookies.min.js',
            ['position' => Asset::ASSET_POSITION_FOOTER, 'version' => '0.3.2', 'minify' => true, 'combine' => true],
            $this->pkgHandle
        );

        $al->register(
            'css',
            'ihavecookies',
            'css/ihavecookies.css',
            ['position' => Asset::ASSET_POSITION_HEADER, 'version' => '0.3.2', 'minify' => true, 'combine' => true],
            $this->pkgHandle
        );

        Events::addListener('on_page_view', function(Event $event) {
            $this->addCookieDisclosure($event->getPageObject(), new User());
        });
    }

    private function installXml(): void
    {
        $ci = new ContentImporter();
        $ci->importContentFile($this->getPackagePath() . '/config/install/install.xml');
    }

    private function installPrivacyPolicyPages(): void
    {
        $site = Site::getSite();
        $locales = $site->getLocales();

        foreach ($locales as $locale) {
            $code = $locale->getLocale();

            if ($pageID = Config::get('beanz.gdpr.privacy-policy.' . $code)) {
                $page = Page::getByID($pageID);

                if ($page->getCollectionID()) {
                    continue;
                }
            }

            $tree = $locale->getSiteTree();

            if (!is_object($tree)) {
                continue;
            }

            /** @var Page $homePage */
            $homePage = $tree->getSiteHomePageObject();

            if ($page = Page::getByPath($homePage->getCollectionPath() . '/privacy-policy')) {
                Config::save('beanz.gdpr.privacy-policy.' . $code, $page->getCollectionID());
            }

            $privacyPolicyPage = $homePage->add(
                Type::getByHandle('page'),
                [
                    'cName' => 'Privacy Policy',
                    'cHandle' => 'privacy-policy',
                    'pkgID' => $this->getPackageEntity()->getPackageID(),
                ]
            );

            Config::save('beanz.gdpr.privacy-policy.' . $code, $privacyPolicyPage->getCollectionID());
        }
    }

    private function setDefaultConfigValues(): void
    {
        $this->setConfig('title', 'Cookies');
        $this->setConfig('message', 'This website uses cookies to optimise your experience. By continuing to surf on this website, you accept our privacy- and cookie policy. Click on \"Accept cookies\" to immediately continue to our website or click \"Customise cookies\" to change your preferences.');
        $this->setConfig('accept_button', 'Accept cookies');
        $this->setConfig('advanced_button', 'Customise cookies');
        $this->setConfig('more_info_button', 'More information');
        $this->setConfig('cookie_types', 'Select which cookies you want to accept');
        $this->setConfig('fixed_cookie_label', 'Essentials');
        $this->setConfig('fixed_cookie_description', 'These are cookies that are essential for the website to work correctly.');
    }

    private function setConfig(string $key, $value): void
    {
        if (empty(Config::get($key))) {
            Config::save('beanz.gdpr.disclosure.general.fallback.' . $key, $value);
        }
    }

    private function installCookieTypes(): void
    {
        if (CookieType::getByHandle('analytics') === null) {
            CookieType::add('analytics', 'Analytics', 'Cookies related to site visits, browser types, etc...', null);
        }

        if (CookieType::getByHandle('marketing') === null) {
            CookieType::add('marketing', 'Marketing', 'Cookies related to marketing, e.g. newsletters, social media, etc...', null);
        }
    }

    /**
     * The cookie disclosure is only shown when:
     * - It is not a page in the admin area (Dashboard),
     * - The page is not in edit mode,
     * - The user is not logged in.
     */
    private function addCookieDisclosure(Page $page, User $user): void
    {
        if ($page->isAdminArea() || $page->isEditMode() || $user->isRegistered()) {
            return;
        }

        $section = Section::getBySectionOfSite($page);

        if ($section === null) {
            return;
        }

        $code = $section->getLocale();
        $disclosure = Config::get('beanz.gdpr.disclosure.general.' . $code);
        $fallback = Config::get('beanz.gdpr.disclosure.general.fallback');
        $privacyPolicy = Page::getByID(Config::get('beanz.gdpr.privacy-policy.' . $code));
        $cookieTypes = CookieType::getActive();

        $v = View::getInstance();
        $v->requireAsset('javascript', 'ihavecookies');
        $v->requireAsset('css', 'ihavecookies');
        $v->addFooterItem('<script type="text/javascript">
            const options = {
                title: "' . ($disclosure['title'] ?: t($fallback['title'])) . '",
                message: "' . ($disclosure['message'] ?: t($fallback['message'])) . '",
                delay: 600,
                expires: 1,
                link: "' . ($privacyPolicy ? $privacyPolicy->getCollectionPath() : '/') . '",
                acceptBtnLabel: "' . ($disclosure['accept_button'] ?: t($fallback['accept_button'])) . '",
                advancedBtnLabel: "' . ($disclosure['advanced_button'] ?: t($fallback['advanced_button'])) . '",
                moreInfoLabel: "' . ($disclosure['more_info_button'] ?: t($fallback['more_info_button'])) . '",
                cookieTypesTitle: "' . ($disclosure['cookie_types'] ?: t($fallback['cookie_types'])) . '",
                fixedCookieTypeLabel: "' . ($disclosure['fixed_cookie_label'] ?: t($fallback['fixed_cookie_label'])) . '",
                fixedCookieTypeDesc: "' . ($disclosure['fixed_cookie_description'] ?: t($fallback['fixed_cookie_description'])) . '",
                cookieTypes: [' . implode(',', array_map(function (CookieType $cookieType) use ($code) { return $this->addCookieType($cookieType, $code); }, $cookieTypes)) . ']
            }
            
            $(document).ready(function() {
                $("body").ihavecookies(options);
            
                cookiePreferences();
            })
            
            function cookiePreferences() {' . implode("\n", array_map('self::addCookieAction', $cookieTypes)) . '}
        </script>');
    }

    private function addCookieType(CookieType $cookieType, string $locale): string
    {
        return '{
            "type": "' . ($cookieType->getTranslatedName($locale) ?: t($cookieType->getName())) . '",
            "value": "' . $cookieType->getHandle() . '",
            "description": "' . ($cookieType->getTranslatedDescription($locale) ?: $cookieType->getDescription()) . '"
        }';
    }

    private function addCookieAction(CookieType $cookieType): string
    {
        $scripts = $cookieType->getScripts();

        if (empty($scripts) || trim($scripts) === '') {
            return '';
        }

        return 'if ($.fn.ihavecookies.preference("' . $cookieType->getHandle() . '") === true) {
            ' . $cookieType->getScripts() . ';
        }';
    }
}
