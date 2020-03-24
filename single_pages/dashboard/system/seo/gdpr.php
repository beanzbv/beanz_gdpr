<?php defined('C5_EXECUTE') or die('Access Denied.'); ?>

<?php if ($controller->getAction() === 'settings') { ?>

    <?php if ($status && $status === 'success') { ?>
        <div class="alert alert-success">
            <?php echo t('GDPR default settings are saved successfully.'); ?>
        </div>
    <?php } ?>

    <form method="post" action="<?php echo $this->action('saveSettings'); ?>">
        <fieldset>
            <legend><?php echo t('Default settings'); ?></legend>

            <?php if (count($tabs) > 1) { ?>
                <?php echo $ui->tabs($tabs); ?>
            <?php } ?>

            <?php foreach ($locales as $locale) { ?>
                <?php $code = $locale->getLocale(); ?>
                <div id="ccm-tab-content-<?php echo $code; ?>" class="<?php echo count($locales) > 1 ? 'ccm-tab-content' : ''; ?>">
                    <div class="form-group">
                        <?php echo $form->label('title[' . $code . ']', t('Privacy Policy')); ?>
                        <?php echo $ps->selectPage('privacyPolicy[' . $code . ']',  Config::get('beanz.gdpr.privacy-policy.' . $code), 'ccm_selectSitemapNode'); ?>
                    </div>

                    <div class="form-group">
                        <?php echo $form->label('title[' . $code . ']', t('Title')); ?>
                        <?php echo $form->text(
                            'title[' . $code . ']',
                            Config::get('beanz.gdpr.disclosure.' . $code . '.title'),
                            ['placeholder' => Config::get('beanz.gdpr.disclosure.fallback.title')]
                        ); ?>
                    </div>

                    <div class="form-group">
                        <?php echo $form->label('message[' . $code . ']', t('Message')); ?>
                        <?php echo $form->textarea(
                            'message[' . $code . ']',
                            Config::get('beanz.gdpr.disclosure.' . $code . '.message'),
                            ['placeholder' => Config::get('beanz.gdpr.disclosure.fallback.message'), 'rows' => 5]
                        ); ?>
                    </div>

                    <div class="form-group">
                        <?php echo $form->label('acceptButton[' . $code . ']', t('Accept button')); ?>
                        <?php echo $form->text(
                            'acceptButton[' . $code . ']',
                            Config::get('beanz.gdpr.disclosure.' . $code . '.accept_button'),
                            ['placeholder' => Config::get('beanz.gdpr.disclosure.fallback.accept_button')]
                        ); ?>
                    </div>

                    <div class="form-group">
                        <?php echo $form->label('advancedButton[' . $code . ']', t('Advanced button')); ?>
                        <?php echo $form->text(
                            'advancedButton[' . $code . ']',
                            Config::get('beanz.gdpr.disclosure.' . $code . '.advanced_button'),
                            ['placeholder' => Config::get('beanz.gdpr.disclosure.fallback.advanced_button')]
                        ); ?>
                    </div>

                    <div class="form-group">
                        <?php echo $form->label('cookieTypes[' . $code . ']', t('Cookie types')); ?>
                        <?php echo $form->text(
                            'cookieTypes[' . $code . ']',
                            Config::get('beanz.gdpr.disclosure.' . $code . '.cookie_types'),
                            ['placeholder' => Config::get('beanz.gdpr.disclosure.fallback.cookie_types')]
                        ); ?>
                    </div>

                    <div class="alert alert-info">
                        <?php echo t('There are a few cookies that Concrete5 sets automatically for the website to work correctly.'); ?><br>
                        <?php echo t('These cookies fall under the "Fixed Cookie" in the disclosure and visitors will not be able to deselect it.'); ?>
                    </div>

                    <div class="form-group">
                        <?php echo $form->label('fixedCookieLabel[' . $code . ']', t('Fixed cookie label')); ?>
                        <?php echo $form->text(
                            'fixedCookieLabel[' . $code . ']',
                            Config::get('beanz.gdpr.disclosure.' . $code . '.fixed_cookie_label'),
                            ['placeholder' => Config::get('beanz.gdpr.disclosure.fallback.fixed_cookie_label')]
                        ); ?>
                    </div>

                    <div class="form-group">
                        <?php echo $form->label('fixedCookieDescription[' . $code . ']', t('Fixed cookie description')); ?>
                        <?php echo $form->textarea(
                            'fixedCookieDescription[' . $code . ']',
                            Config::get('beanz.gdpr.disclosure.' . $code . '.fixed_cookie_description'),
                            ['placeholder' => Config::get('beanz.gdpr.disclosure.fallback.fixed_cookie_description'), 'rows' => 5]
                        ); ?>
                    </div>
                </div>
            <?php } ?>
        </fieldset>

        <div class="ccm-dashboard-form-actions-wrapper">
            <div class="ccm-dashboard-form-actions">
                <?php echo $form->submit('save', t('Save'), ['class' => 'btn-primary pull-right']); ?>
            </div>
        </div>
    </form>

    <div class="ccm-dashboard-header-buttons">
        <a href="<?php echo $this->action('view'); ?>" class="btn btn-default">
            <?php echo t('Cookie Types'); ?>
        </a>
    </div>

<?php } else if ($controller->getAction() === 'type' || $controller->getAction() === 'saveType') { ?>

    <?php if ($status === 'success') { ?>
        <div class="alert alert-success">
            <?php echo t('Cookie type %s is successfully saved.', $cookieType->getName()); ?>
        </div>
    <?php } ?>

    <?php if ($status === 'activated') { ?>
        <div class="alert alert-info">
            <?php echo t('Cookie type %s is successfully activated.', $cookieType->getName()); ?>
        </div>
    <?php } ?>

    <?php if ($status === 'archived') { ?>
        <div class="alert alert-info">
            <?php echo t('Cookie type %s is successfully archived.', $cookieType->getName()); ?>
        </div>
    <?php } ?>

    <form method="post" action="<?php echo $this->action('saveType', $cookieType ? $cookieType->getHandle() : null); ?>">
        <fieldset>
            <legend><?php echo t('Cookie Type'); ?></legend>

            <?php if ($cookieType && $cookieType->isActive() === false) { ?>
                <div class="alert alert-warning">
                    <?php echo t('This Cookie Type is inactive. This option will not be shown in the cookie disclosure and the scripts will never be loaded.'); ?>
                </div>
            <?php } ?>

            <div class="form-group">
                <?php echo $form->label('name', t('Name')); ?>
                <?php echo $form->text('name', $cookieType ? $cookieType->getName() : ''); ?>
            </div>

            <div class="form-group">
                <?php echo $form->label('handle', t('Handle')); ?>
                <?php echo $form->text('handle', $cookieType ? $cookieType->getHandle() : ''); ?>
            </div>

            <div class="form-group">
                <?php echo $form->label('description', t('Description')); ?>
                <?php echo $form->textarea('description', $cookieType ? $cookieType->getDescription() : '', ['rows' => 5]); ?>
            </div>
        </fieldset>

        <fieldset>
            <legend><?php echo t('Scripts'); ?></legend>

            <div class="alert alert-info">
                <?php echo t('These scripts will be executed when the visitor accepted the cookies.'); ?><br>
                <?php echo t('This is where you can put tracking codes, etc...'); ?><br>
                <?php echo t('Script-tags should not be present, and will be removed.'); ?><br>
            </div>

            <div class="form-group">
                <?php echo $form->label('headerScripts', t('Header')); ?>
                <?php echo $form->textarea('headerScripts', $cookieType ? $cookieType->getHeaderScripts() : '', ['rows' => 15]); ?>
            </div>

            <div class="form-group">
                <?php echo $form->label('footerScripts', t('Footer')); ?>
                <?php echo $form->textarea('footerScripts', $cookieType ? $cookieType->getFooterScripts() : '', ['rows' => 15]); ?>
            </div>
        </fieldset>


        <?php if (count($locales) > 1) { ?>
            <fieldset>
                <legend><?php echo t('Translations'); ?></legend>

                <?php echo $ui->tabs($tabs); ?>

                <?php foreach ($locales as $locale) { ?>
                    <?php $code = $locale->getLocale(); ?>

                    <div id="ccm-tab-content-<?php echo $code; ?>" class="<?php echo count($locales) > 1 ? 'ccm-tab-content' : ''; ?>">
                        <div class="form-group">
                            <?php echo $form->label('translatedName[' . $code . ']', t('Name')); ?>
                            <?php echo $form->text('translatedName[' . $code . ']', $cookieType ? $cookieType->getTranslatedName($code) : ''); ?>
                        </div>

                        <div class="form-group">
                            <?php echo $form->label('translatedDescription[' . $code . ']', t('Description')); ?>
                            <?php echo $form->textarea('translatedDescription[' . $code . ']', $cookieType ? $cookieType->getTranslatedDescription($code) : '', ['rows' => 5]); ?>
                        </div>
                    </div>
                <?php } ?>
        <?php } ?>
        </fieldset>

        <div class="ccm-dashboard-form-actions-wrapper">
            <div class="ccm-dashboard-form-actions">
                <a href="<?php echo $this->action('view'); ?>" class="btn btn-default">
                    <?php echo t('Back to List'); ?>
                </a>
                <?php echo $form->submit('save', t('Save'), ['class' => 'btn-primary pull-right']); ?>
            </div>
        </div>
    </form>

    <?php if ($cookieType) { ?>
        <div class="ccm-dashboard-header-buttons">
            <?php if ($cookieType->isActive() === false) { ?>
                <form method="post" action="<?php echo $this->action('activate', $cookieType->getHandle()); ?>" class="form-inline" style="display: inline-block">
                    <?php echo $form->submit('activate', t('Activate'), ['class' => 'btn-success']); ?>
                </form>
            <?php } else { ?>
                <form method="post" action="<?php echo $this->action('archive', $cookieType->getHandle()); ?>" class="form-inline" style="display: inline-block">
                    <?php echo $form->submit('archive', t('Archive'), ['class' => 'btn-default']); ?>
                </form>
            <?php } ?>

            <form method="post" action="<?php echo $this->action('delete', $cookieType->getHandle()); ?>" class="form-inline" style="display: inline-block"
                  onClick="return confirm('<?php echo t('Are you sure you want to delete %s?', $cookieType->getName()); ?>');">
                <?php echo $form->submit('delete', t('Delete'), ['class' => 'btn-danger']); ?>
            </form>
        </div>
    <?php } ?>

<?php } else { ?>

    <?php if ($status === 'deleted') { ?>
        <div class="alert alert-danger">
            <?php echo t('Cookie type is successfully deleted.'); ?>
        </div>
    <?php } ?>

    <div class="ccm-dashboard-content-full">
        <div data-search-element="results">
            <div class="table-responsive">
                <table class="ccm-search-results-table" cellspacing="0" cellpadding="0" border="0">
                    <thead>
                    <tr>
                        <th><span><?php echo t('Name')?></span></th>
                        <th><span><?php echo t('Handle')?></span></th>
                        <th><span><?php echo t('Active')?></span></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($cookieTypes as $cookieType) { ?>
                        <tr id="<?php echo $cookieType->getHandle()?>" data-details-url="<?php echo $this->action('type', $cookieType->getHandle()); ?>">
                            <td><a href="<?php echo $this->action('type', $cookieType->getHandle()); ?>"><?php echo $cookieType->getName(); ?></a></td>
                            <td><?php echo $cookieType->getHandle(); ?></td>
                            <td>
                                <?php if ($cookieType->isActive()) { ?>
                                    <label class="label label-success">
                                        <?php echo t('Active'); ?>
                                    </label>
                                <?php } else { ?>
                                    <label class="label label-danger">
                                        <?php echo t('Archived'); ?>
                                    </label>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="ccm-dashboard-header-buttons">
        <a href="<?php echo $this->action('settings'); ?>" class="btn btn-default">
            <?php echo t('General Settings'); ?>
        </a>
        <a href="<?php echo $this->action('type'); ?>" class="btn btn-primary">
            <?php echo t('Add Cookie Type'); ?>
        </a>
    </div>

<?php } ?>