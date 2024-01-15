<?php

use ImproveSEO\View;
?>
<?php View::startSection('breadcrumbs') ?>
<a href="<?php echo admin_url('admin.php?page=improveseo_dashboard') ?>">Improve SEO</a>
&raquo;
<span>Settings</span>
<?php View::endSection('breadcrumbs') ?>
<?php View::startSection('content') ?>
<h1 class="hidden">Improve SEO Settings</h1>
<div class="settings improveseo_wrapper p-3 p-lg-4">
    <section class="project-section border-bottom">
        <div class="project-heading d-flex flex-row align-items-center pb-2">
            <img class="mr-2" src="<?php echo esc_url(IMPROVESEO_WT_URL . '/assets/images/project-list-logo.png'); ?>" alt="<?php esc_attr_e('ImproveSeo', 'improve-seo'); ?>">
            <h1><?php esc_html_e('Improve SEO Settings', 'improve-seo'); ?></h1>
        </div>
    </section>
    <section class="form-wrap pt-3">
        <form method="post" action="options.php" class="form-wrap">
            <?php settings_fields('improveseo_settings'); ?>
            <div class="BasicForm__row">
                <div class="input-group">
                    <label class="form-label"><?php esc_html_e('Pixabay API Key', 'improve-seo'); ?></label>
                    <div class="input-prefix">
                        <input type="text" name="improveseo_pixabay_key" placeholder="<?php esc_attr_e('495873243', 'improve-seo'); ?>" class="form-control mb-2" value="<?php echo esc_attr(get_option('improveseo_pixabay_key')); ?>" />
                        <span><?php esc_html_e('Ex.', 'improve-seo'); ?></span>
                    </div>
                    <p class="howto"><?php printf(esc_html__('How to Get a Free Pixabay API Key: %s', 'improve-seo'), '<a href="https://www.youtube.com/watch?v=t3mxF7m2wWw" target="_blank">https://www.youtube.com/watch?v=t3mxF7m2wWw</a>'); ?></p>
                </div>
            </div>
            <div class="BasicForm__row">
                <div class="input-group">
                    <label class="form-label"><?php esc_html_e('Google API Key', 'improve-seo'); ?></label>
                    <div class="input-prefix">
                        <input type="text" name="improveseo_google_api_key" placeholder="<?php esc_attr_e('49587434353', 'improve-seo'); ?>" class="form-control mb-2" value="<?php echo esc_attr(get_option('improveseo_google_api_key')); ?>" />
                        <span><?php esc_html_e('Ex.', 'improve-seo'); ?></span>
                    </div>
                    <p class="howto"><?php printf(esc_html__('How to Get a Free Google Maps API Key: %s', 'improve-seo'), '<a href="http://www.youtube.com/watch?v=arWQ9Wk3t1w" target="_blank">http://www.youtube.com/watch?v=arWQ9Wk3t1w</a>'); ?></p>
                </div>
            </div>
            <div class="BasicForm__row">
                <div class="input-group">
                    <label class="form-label"><?php esc_html_e('Word AI Email', 'improve-seo'); ?></label>
                    <div class="input-prefix">
                        <input type="text" name="improveseo_word_ai_email" placeholder="<?php esc_attr_e('Ex.', 'improve-seo'); ?>" class="form-control mb-2" value="<?php echo esc_attr(get_option('improveseo_word_ai_email')); ?>" />
                        <span><?php esc_html_e('Ex.', 'improve-seo'); ?></span>
                    </div>
                </div>
            </div>
            <div class="BasicForm__row">
                <div class="input-group">
                    <label class="form-label"><?php esc_html_e('Word AI Password', 'improve-seo'); ?></label>
                    <div class="input-prefix">
                        <input type="text" name="improveseo_word_ai_pass" placeholder="<?php esc_attr_e('Ex.', 'improve-seo'); ?>" class="form-control mb-2" value="<?php echo esc_attr(get_option('improveseo_word_ai_pass')); ?>" />
                        <span><?php esc_html_e('Ex.', 'improve-seo'); ?></span>
                    </div>
                </div>
            </div>
            <div class="notice notice-success notice-improveseo">
                <p><?php esc_html_e('WordAi is a third party spinner that allows you to generate spun content on the fly. If you have a WordAi account, input your settings here. If not, leave the boxes blank. <strong>This is NOT a necessity to use Improve SEO and only here for convenience for users who need it.</strong>', 'improve-seo'); ?></p>
            </div>
            <p class="submit shortcode-form-btn text-center">
                <input type="submit" class="btn btn-outline-primary" value="<?php esc_attr_e('Save Changes', 'improve-seo'); ?>" />
            </p>
        </form>
    </section>

    <section>
        <h2><?php esc_html_e('Local SEO Countries', 'improve-seo'); ?></h2>
        <div class="update-nag m-0 pl-0 pt-0">
            <p class="mb-0">
                <?php esc_html_e('Here you can select the countries that you would like included in the local SEO feature. It’s recommended to only select the countries that you need as the files can get fairly large. Upon selecting the desired country(s), the files will be downloaded from the Improve SEO cloud and be ready for use within 1-2 minutes.', 'improve-seo'); ?></p>
        </div>
        <?php
        $countries = ImproveSEO\Geo::getCountriesList();
        $countryModel = new ImproveSEO\Models\Country();
        $installedCountries = $countryModel->all('name');
        $installed = array();
        foreach ($installedCountries as $cc) {
            $installed[] = $cc->name;
        }
        ?>
        <section class="project-table-wrapper">
            <div class="table-responsive-sm form">
                <table class="table widefat fixed wp-list-table widefat fixed table-view-list posts">
                    <thead>
                        <tr>
                            <th scope="col" class="manage-column manage-column column-title column-primary"><?php esc_html_e('Country Name', 'improve-seo'); ?></th>
                            <th scope="col" class="manage-column"><?php esc_html_e('Filesize', 'improve-seo'); ?></th>
                            <th scope="col" class="manage-column"><?php esc_html_e('Action', 'improve-seo'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($countries as $country) : ?>
                            <tr>
                                <td class="column-title column-primary has-row-actions">
                                    <?php echo esc_html($country->country); ?>
                                    <button type="button" class="toggle-row"><span class="screen-reader-text"><?php esc_html_e('Show more details', 'improve-seo'); ?></span></button>
                                </td>
                                <td data-colname="Filesize"><?php echo esc_html($country->size); ?></td>
                                <td data-colname="Action" class="actions-btn">
                                    <?php if (in_array($country->country, $installed)) : ?>
                                        <a href="<?php echo esc_url(admin_url("admin.php?page=improveseo_settings&action=delete_country&country={$country->code}&noheader=true")); ?>" class="btn btn-outline-danger"><?php esc_html_e('Delete', 'improve-seo'); ?></a>
                                    <?php else : ?>
                                        <a href="<?php echo esc_url(admin_url("admin.php?page=improveseo_settings&action=add_country&country={$country->code}&noheader=true")); ?>" class="btn btn-outline-primary"><?php esc_html_e('Download', 'improve-seo'); ?></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </section>

</div>
<?php View::endSection('content') ?>
<?php View::make('layouts.main') ?>