<?php
namespace Concrete\Package\BeanzGdpr\Controller\SinglePage\Dashboard\System\Seo;

use Beanz\Gdpr\CookieType;
use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Core\Routing\RedirectResponse;
use Concrete\Core\Support\Facade\Config;
use Concrete\Core\Support\Facade\Site;
use Concrete\Core\Support\Facade\Url;
use RuntimeException;

class Gdpr extends DashboardPageController
{
    public function view(string $status = null)
    {
        $this->set('status', $status);
        $this->set('pageTitle', t('GDPR') . ' - ' . t('Cookie Types'));

        $this->set('cookieTypes', CookieType::getAll());
    }

    public function type(string $cookieTypeHandle = null, string $status = null)
    {
        $this->set('status', $status);
        $this->set('pageTitle', t('GDPR') . ' - ' . t('Cookie Type'));

        if ($cookieTypeHandle) {
            $this->set('cookieType', CookieType::getByHandle($cookieTypeHandle));
        }

        $this->registerLocales();
    }

    public function saveType(string $cookieTypeHandle = null)
    {
        $args = $this->post();
        $errors = $this->validateType($args, $cookieTypeHandle);

        if ($errors->has()) {
            $this->error = $errors;
            $this->type($cookieTypeHandle);

            return true;
        }

        if ($cookieTypeHandle) {
            $cookieType = CookieType::getByHandle($cookieTypeHandle);
            $cookieType->update(
                $args['handle'],
                $args['name'],
                $args['description'],
                strip_tags($args['headerScripts']),
                strip_tags($args['footerScripts'])
            );
        } else {
            $cookieType = CookieType::add(
                $args['handle'],
                $args['name'],
                $args['description'],
                strip_tags($args['headerScripts']),
                strip_tags($args['footerScripts'])
            );
        }

        if (array_key_exists('translatedName', $args)) {
            $site = Site::getSite();
            $locales = $site->getLocales();

            foreach ($locales as $locale) {
                $code = $locale->getLocale();

                $cookieType->setTranslatedName($code, $args['translatedName'][$code]);
                $cookieType->setTranslatedDescription($code, $args['translatedDescription'][$code]);
            }
        }

        $this->addHeaderFile($cookieType);
        $this->addFooterFile($cookieType);

        $response = new RedirectResponse(URL::to('/dashboard/system/seo/gdpr/type', $cookieType->getHandle(), 'success'));

        return $response->send();
    }

    protected function addHeaderFile(CookieType $cookieType): void
    {
        $dir = DIR_APPLICATION . '/js/generated/gdpr/header';

        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true) && !is_dir($dir)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $dir));
            }
        }

        $file = $dir . '/' . $cookieType->getHandle() . '.js';

        if (empty(trim($cookieType->getHeaderScripts()))) {
            if (file_exists($file)) {
                unlink($file);
            }

            return;
        }

        $javascript = fopen($file, 'w');
        fwrite($javascript, '$(function () { ' . PHP_EOL . 'if ($.fn.ihavecookies.preference("' . $cookieType->getHandle() . '") === true) { ' . PHP_EOL . $cookieType->getHeaderScripts() . PHP_EOL  . '}' . PHP_EOL  . '});');
        fclose($javascript);
    }

    protected function addFooterFile(CookieType $cookieType): void
    {
        $dir = DIR_APPLICATION . '/js/generated/gdpr/footer';

        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true) && !is_dir($dir)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $dir));
            }
        }

        $file = $dir . '/' . $cookieType->getHandle() . '.js';

        if (empty(trim($cookieType->getFooterScripts()))) {
            if (file_exists($file)) {
                unlink($file);
            }

            return;
        }

        $javascript = fopen($file, 'w');
        fwrite($javascript, '$(function () { ' . PHP_EOL . 'if ($.fn.ihavecookies.preference("' . $cookieType->getHandle() . '") === true) { ' . PHP_EOL . $cookieType->getFooterScripts() . PHP_EOL  . '}' . PHP_EOL  . '});');
        fclose($javascript);
    }

    /** @param string|null $cookieTypeHandle */
    private function validateType(array $args, $cookieTypeHandle)
    {
        $error = $this->app->make('helper/validation/error');

        if (empty($args['name']) || trim($args['name']) === '') {
            $error->add(t('%s is required', t('Name')));
        }

        if (empty($args['handle']) || trim($args['handle']) === '') {
            $error->add(t('%s is required', t('Handle')));
        } else {
            $stringValidator = $this->app->make('helper/validation/strings');
            if (!$stringValidator->handle($args['handle'])) {
                $error->add(t('Cookie Type handles may only contain letters, numbers and underscore "_" characters'));
            }

            $existing = CookieType::getByHandle($args['handle']);
            if (is_object($existing) && $cookieTypeHandle !== $args['handle']) {
                $error->add(t('A Cookie Type with the handle %s already exists', $args['handle']));
            }
        }

        if (empty($args['description']) || trim($args['description']) === '') {
            $error->add(t('%s is required', t('Description')));
        }

        return $error;
    }

    public function settings(string $status = null)
    {
        $this->set('status', $status);
        $this->set('pageTitle', t('GDPR') . ' - ' . t('General Settings'));

        $this->registerLocales();

        $this->set('ps', $this->app->make('helper/form/page_selector'));
    }

    public function saveSettings()
    {
        $args = $this->post();

        $site = Site::getSite();
        $locales = $site->getLocales();

        foreach ($locales as $locale) {
            $code = $locale->getLocale();

            Config::save('beanz.gdpr.privacy-policy.' . $code, $args['privacyPolicy'][$code]);

            Config::save('beanz.gdpr.disclosure.' . $code . '.title', $args['title'][$code]);
            Config::save('beanz.gdpr.disclosure.' . $code . '.message', $args['message'][$code]);
            Config::save('beanz.gdpr.disclosure.' . $code . '.accept_button', $args['acceptButton'][$code]);
            Config::save('beanz.gdpr.disclosure.' . $code . '.advanced_button', $args['advancedButton'][$code]);
            Config::save('beanz.gdpr.disclosure.' . $code . '.cookie_types', $args['cookieTypes'][$code]);
            Config::save('beanz.gdpr.disclosure.' . $code . '.fixed_cookie_label', $args['fixedCookieLabel'][$code]);
            Config::save('beanz.gdpr.disclosure.' . $code . '.fixed_cookie_description', $args['fixedCookieDescription'][$code]);
        }

        $response = new RedirectResponse(URL::to('/dashboard/system/seo/gdpr/settings/success'));
        $response->send();
    }

    public function activate(string $cookieTypeHandle)
    {
        $cookieType = CookieType::getByHandle($cookieTypeHandle);
        $cookieType->activate();

        $response = new RedirectResponse(URL::to('/dashboard/system/seo/gdpr/type', $cookieType->getHandle(), 'activated'));
        $response->send();
    }

    public function archive(string $cookieTypeHandle)
    {
        $cookieType = CookieType::getByHandle($cookieTypeHandle);
        $cookieType->archive();

        $response = new RedirectResponse(URL::to('/dashboard/system/seo/gdpr/type', $cookieType->getHandle(), 'archived'));
        $response->send();
    }

    public function delete(string $cookieTypeHandle)
    {
        $cookieType = CookieType::getByHandle($cookieTypeHandle);
        $cookieType->delete();

        $response = new RedirectResponse(URL::to('/dashboard/system/seo/gdpr/deleted'));
        $response->send();
    }

    private function registerLocales()
    {
        $site = Site::getSite();
        $locales = $site->getLocales();
        $defaultLocale = $site->getDefaultLocale();

        $tabs = [];

        foreach ($locales as $locale) {
            $tabs[] = [
                $locale->getLocale(),
                $locale->getLanguageText(),
                $locale === $defaultLocale,
            ];
        }

        $this->set('tabs', $tabs);
        $this->set('locales', $locales);
        $this->set('ui', $this->app->make('helper/concrete/ui'));
    }
}