<?php
use ImproveSEO\View;
?>
<?php View::startSection('breadcrumbs') ?>
<a href="<?= admin_url('admin.php?page=improveseo') ?>">Improve SEO</a>
&raquo;
<span>Settings</span>
<?php View::endSection('breadcrumbs') ?>
<?php View::startSection('content') ?>
<h1 class="hidden">Improve SEO Settings</h1>
<div class="settings improveseo_wrapper p-3 p-lg-4">
    <section class="project-section border-bottom">
        <div class="project-heading d-flex flex-row align-items-center pb-2">
            <img class="mr-2" src="<?php echo WT_URL.'/assets/images/project-list-logo.png'?>" alt="ImproveSeo">
            <h1>Improve SEO Settings</h1>
        </div>
    </section>
    <section class="form-wrap pt-3">
        <form method="post" action="options.php" class="form-wrap">
            <?php settings_fields('improveseo_settings'); ?>
            <div class="BasicForm__row">
                <div class="input-group">
                    <label class="form-label">Pixabay API Key</label>
                    <div class="input-prefix">
                        <input type="text" name="improveseo_pixabay_key" placeholder="495873243" class="form-control mb-2" value="<?php echo get_option('improveseo_pixabay_key'); ?>" />
                        <span>Ex.</span>
                    </div>
                    <p class="howto">How to Get a Free Pixabay API Key: <a href="https://www.youtube.com/watch?v=t3mxF7m2wWw" target="_blank">https://www.youtube.com/watch?v=t3mxF7m2wWw</a></p>
                </div> 
            </div>
            <div class="BasicForm__row">
                <div class="input-group">
                    <label class="form-label">Google API Key</label>
                    <div class="input-prefix">
                        <input type="text" name="improveseo_google_api_key" placeholder="49587434353" class="form-control mb-2" value="<?php echo get_option('improveseo_google_api_key'); ?>" />
                        <span>Ex.</span>
                    </div>
                    <p class="howto">How to Get a Free Google Maps API Key: <a href="http://www.youtube.com/watch?v=arWQ9Wk3t1w" target="_blank">http://www.youtube.com/watch?v=arWQ9Wk3t1w</a></p>
                </div> 
            </div>
            <div class="BasicForm__row">
                <div class="input-group">
                    <label class="form-label">Word AI Email</label>
                    <div class="input-prefix">
                        <input type="text" name="improveseo_word_ai_email" placeholder="Ex." class="form-control mb-2" value="<?php echo get_option('improveseo_word_ai_email'); ?>" />
                        <span>Ex.</span>
                    </div>
                </div>
            </div>
            <div class="BasicForm__row">
                <div class="input-group">
                    <label class="form-label">Word AI Password</label>
                    <div class="input-prefix">
                        <input type="text" name="improveseo_word_ai_pass" placeholder="Ex." class="form-control mb-2" value="<?php echo get_option('improveseo_word_ai_pass'); ?>" />
                        <span>Ex.</span>
                    </div>
                </div>
            </div>
            <div class="notice notice-success">
                <p>WordAi is a third party spinner that allows you to generate spun content on the fly. If you have a WordAi account, input your settings here. If not, leave the boxes blank. <strong>This is NOT a necessity to use Improve SEO and only here for convenience for users who need it.</strong></p>
            </div>
            <p class="submit shortcode-form-btn text-center">
                <input type="submit" class="btn btn-outline-primary" value="<?php _e('Save Changes') ?>" />
            </p>
        </form>
    </section>
    <section>
        <h2>Local SEO Countries</h2>
        <div class="update-nag m-0 pl-0 pt-0">
            <p class="mb-0">
            Here you can select the countries that you would like included in the local SEO feature. It’s recommended to only select the countries that you need as the files can get fairly large. Upon selecting the desired country(s), the files will be downloaded from the Improve SEO cloud and be ready for use within 1-2 minutes.</p>
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
                            <th scope="col" class="manage-column manage-column column-title column-primary">Country Name</th>
                            <th scope="col" class="manage-column">Filesize</th>
                            <th scope="col" class="manage-column">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($countries as $country): ?>
                        <tr>
                            <td class="column-title column-primary has-row-actions" >
                                <?= $country->country ?>
                                <button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button>
                            </td>
                            <td data-colname="Filesize"><?= $country->size ?></td>
                            <td data-colname="Action" class="actions-btn">
                                <?php if (in_array($country->country, $installed)): ?>
                                <a href="/wp-admin/admin.php?page=improveseo_settings&action=delete_country&country=<?= $country->code ?>&noheader=true" class="btn btn-outline-danger">Delete</a>
                                <?php else: ?>
                                <a href="/wp-admin/admin.php?page=improveseo_settings&action=add_country&country=<?= $country->code ?>&noheader=true" class="btn btn-outline-primary">Download</a>
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