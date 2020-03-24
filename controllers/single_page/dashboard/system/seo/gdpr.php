<?php
namespace Concrete\Package\BeanzGdpr\Controller\SinglePage\Dashboard\System\Seo;

use Beanz\Gdpr\CookieType;
use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Core\Routing\RedirectResponse;
use Concrete\Core\Support\Facade\Config;
use Concrete\Core\Support\Facade\Site;
use Concrete\Core\Support\Facade\Url;

class Gdpr extends DashboardPageController
{
    public function view(string $status = null): void
    {
        $this->set('status', $status);
        $this->set('pageTitle', t('GDPR') . ' - ' . t('Cookie Types'));

        $this->set('cookieTypes', CookieType::getAll());
    }

    public function type(string $cookieTypeHandle = null, string $status = null): void
    {
        $this->set('status', $status);
        $this->set('pageTitle', t('GDPR') . ' - ' . t('Cookie Type'));

        if ($cookieTypeHandle) {
            $this->set('cookieType', CookieType::getByHandle($cookieTypeHandle));
        }

        $this->registerLocales();
    }

    public function saveType(string $cookieTypeHandle = null): void
    {
        $args = $this->post();
        $errors = $this->validateType($args, $cookieTypeHandle);

        if ($errors->has()) {
            $this->error = $errors;
            $this->type($cookieTypeHandle);

            return;
        }

        if ($cookieTypeHandle) {
            $cookieType = CookieType::getByHandle($cookieTypeHandle);
            $cookieType->update($args['handle'], $args['name'], $args['description'], strip_tags($args['scripts']));
        } else {
            $cookieType = CookieType::add($args['handle'], $args['name'], $args['description'], strip_tags($args['scripts']));
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

        $response = new RedirectResponse(URL::to('/dashboard/system/seo/gdpr/type', $cookieType->getHandle(), 'success'));
        $response->send();
    }

    private function validateType(array $args, ?string $cookieTypeHandle)
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

    public function settings(string $status = null): void
    {
        $this->set('status', $status);
        $this->set('pageTitle', t('GDPR') . ' - ' . t('General Settings'));

        $this->registerLocales();

        $this->set('ps', $this->app->make('helper/form/page_selector'));
    }

    public function saveSettings(): void
    {
        $args = $this->post();

        $site = Site::getSite();
        $locales = $site->getLocales();

        foreach ($locales as $locale) {
            $code = $locale->getLocale();

            Config::save('beanz.gdpr.privacy-policy.' . $code, $args['privacyPolicy'][$code] ?? null);

            Config::save('beanz.gdpr.disclosure.general.' . $code . '.title', $args['title'][$code] ?? null);
            Config::save('beanz.gdpr.disclosure.general.' . $code . '.message', $args['message'][$code] ?? null);
            Config::save('beanz.gdpr.disclosure.general.' . $code . '.accept_button', $args['acceptButton'][$code] ?? null);
            Config::save('beanz.gdpr.disclosure.general.' . $code . '.advanced_button', $args['advancedButton'][$code] ?? null);
            Config::save('beanz.gdpr.disclosure.general.' . $code . '.cookie_types', $args['cookieTypes'][$code] ?? null);
            Config::save('beanz.gdpr.disclosure.general.' . $code . '.fixed_cookie_label', $args['fixedCookieLabel'][$code] ?? null);
            Config::save('beanz.gdpr.disclosure.general.' . $code . '.fixed_cookie_description', $args['fixedCookieDescription'][$code] ?? null);
        }

        $response = new RedirectResponse(URL::to('/dashboard/system/seo/gdpr/settings/success'));
        $response->send();
    }

    public function activate(string $cookieTypeHandle): void
    {
        $cookieType = CookieType::getByHandle($cookieTypeHandle);
        $cookieType->activate();

        $response = new RedirectResponse(URL::to('/dashboard/system/seo/gdpr/type', $cookieType->getHandle(), 'activated'));
        $response->send();
    }

    public function archive(string $cookieTypeHandle): void
    {
        $cookieType = CookieType::getByHandle($cookieTypeHandle);
        $cookieType->archive();

        $response = new RedirectResponse(URL::to('/dashboard/system/seo/gdpr/type', $cookieType->getHandle(), 'archived'));
        $response->send();
    }

    public function delete(string $cookieTypeHandle): void
    {
        $cookieType = CookieType::getByHandle($cookieTypeHandle);
        $cookieType->delete();

        $response = new RedirectResponse(URL::to('/dashboard/system/seo/gdpr/deleted'));
        $response->send();
    }

    private function registerLocales(): void
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