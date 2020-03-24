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

    public function getPackageName()
    {
        return t('GDPR');
    }

    public function getPackageDescription()
    {
        return t('Add a GDPR Cookie Disclosure to your website');
    }

    public function install()
    {
        parent::install();

        $this->installXml();
        $this->installDisclosureFallbackConfigValues();
        $this->installDisclosureMultilingualConfigValues();
        $this->installCookieTypes();
    }

    public function upgrade()
    {
        parent::upgrade();

        $this->installXml();
        $this->installDisclosureFallbackConfigValues();
        $this->installDisclosureMultilingualConfigValues();
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
            $this->addCookieDisclosure($event->getPageObject());
        });
    }

    private function installXml()
    {
        $ci = new ContentImporter();
        $ci->importContentFile($this->getPackagePath() . '/config/install/install.xml');
    }

    private function installDisclosureMultilingualConfigValues()
    {
        $site = Site::getSite();
        $locales = $site->getLocales();

        foreach ($locales as $locale) {
            $translations = [];

            $code = $locale->getLocale();
            $translationsPath = $this->getPackagePath() . '/config/disclosure/' . $code . '.php';

            if (file_exists($translationsPath)) {
                require($translationsPath);
            }

            if ($translations) {
                foreach ($translations as $key => $translation) {
                    $this->setConfig($key, $translation, $code);
                }
            }

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
            $privacyPolicyPage = Page::getByPath($homePage->getCollectionPath() . '/privacy-policy');

            if ($privacyPolicyPageID = $privacyPolicyPage->getCollectionID()) {
                Config::save('beanz.gdpr.privacy-policy.' . $code, $privacyPolicyPageID);

                continue;
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

    private function installDisclosureFallbackConfigValues()
    {
        $translations = [];
        require($this->getPackagePath() . '/config/disclosure/en_US.php');

        if ($translations) {
            foreach ($translations as $key => $translation) {
                $this->setConfig($key, $translation, 'fallback');
            }
        }
    }

    private function setConfig(string $key, string $value, string $locale)
    {
        $configKey = 'beanz.gdpr.disclosure.' . $locale . '.' . $key;

        if (empty(Config::get($configKey))) {
            Config::save($configKey, $value);
        }
    }

    private function installCookieTypes()
    {
        if (CookieType::getByHandle('analytics') === null) {
            CookieType::add('analytics', 'Analytics', 'Cookies related to site visits, browser types, etc...', null, null);
        }

        if (CookieType::getByHandle('marketing') === null) {
            CookieType::add('marketing', 'Marketing', 'Cookies related to marketing, e.g. newsletters, social media, etc...', null, null);
        }
    }

    /**
     * The cookie disclosure is only shown when:
     * - It is not a page in the admin area (Dashboard),
     * - The page is not in edit mode,
     * - The user is not logged in.
     */
    private function addCookieDisclosure(Page $page)
    {
        if ($page->isAdminArea() || $page->isEditMode()) {
            return;
        }

        $section = Section::getBySectionOfSite($page);

        if ($section === null) {
            return;
        }

        $code = $section->getLocale();
        $privacyPolicy = Page::getByID(Config::get('beanz.gdpr.privacy-policy.' . $code));
        $cookieTypes = CookieType::getActive();

        $v = View::getInstance();
        $v->requireAsset('javascript', 'jquery');
        $v->requireAsset('javascript', 'ihavecookies');
        $v->requireAsset('css', 'ihavecookies');

        $v->addFooterItem('<script type="text/javascript">
            const options = {
                title: "' . $this->getConfigItem('title', $code) . '",
                message: "' . $this->getConfigItem('message', $code) . '",
                delay: 600,
                expires: 1,
                link: "' . ($privacyPolicy ? $privacyPolicy->getCollectionPath() : '/') . '",
                acceptBtnLabel: "' . $this->getConfigItem('accept_button', $code) . '",
                advancedBtnLabel: "' . $this->getConfigItem('advanced_button', $code) . '",
                moreInfoLabel: "' . $this->getConfigItem('more_info_button', $code) . '",
                cookieTypesTitle: "' . $this->getConfigItem('cookie_types', $code) . '",
                fixedCookieTypeLabel: "' . $this->getConfigItem('fixed_cookie_label', $code) . '",
                fixedCookieTypeDesc: "' . $this->getConfigItem('fixed_cookie_description', $code) . '",
                onAccept: function () { location.reload(); },
                cookieTypes: [' . implode(',', array_map(function (CookieType $cookieType) use ($code) { return $this->addCookieType($cookieType, $code); }, $cookieTypes)) . ']
            }
            
            $(document).ready(function() {
                $("body").ihavecookies(options);
            });
        </script>');

        $al = AssetList::getInstance();

        foreach ($cookieTypes as $cookieType) {
            if (file_exists(DIR_APPLICATION . '/js/generated/gdpr/header/' . $cookieType->getHandle() . '.js')) {
                $al->register(
                    'javascript',
                    'gdpr-header-' . $cookieType->getHandle(),
                    '../application/js/generated/gdpr/header/' . $cookieType->getHandle() . '.js',
                    ['position' => Asset::ASSET_POSITION_HEADER, 'version' => '1.0.0', 'minify' => true, 'combine' => true],
                );

                $v->requireAsset('javascript', 'gdpr-header-' . $cookieType->getHandle());
            }

            if (file_exists(DIR_APPLICATION . '/js/generated/gdpr/footer/' . $cookieType->getHandle() . '.js')) {
                $al->register(
                    'javascript',
                    'gdpr-footer-' . $cookieType->getHandle(),
                    '../application/js/generated/gdpr/footer/' . $cookieType->getHandle() . '.js',
                    ['position' => Asset::ASSET_POSITION_FOOTER, 'version' => '1.0.0', 'minify' => true, 'combine' => true],
                );

                $v->requireAsset('javascript', 'gdpr-footer-' . $cookieType->getHandle());
            }
        }
    }

    private function addCookieType(CookieType $cookieType, string $locale)
    {
        $name = $cookieType->getTranslatedName($locale);
        $description = $cookieType->getTranslatedDescription($locale);

        return '{
            "type": "' . (!empty($name) ? $name : t($cookieType->getName())) . '",
            "value": "' . $cookieType->getHandle() . '",
            "description": "' . (!empty($description) ? $description : $cookieType->getDescription()) . '"
        }';
    }

    private function getConfigItem(string $value, string $locale)
    {
        $title = Config::get('beanz.gdpr.disclosure.' . $locale . '.' . $value);

        if (!empty($title) && $title !== '') {
            return $title;
        }

        return Config::get('beanz.gdpr.disclosure.fallback.' . $value);
    }
}
