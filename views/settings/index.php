<?php

use ImproveSEO\View;

?>

<?php View::startSection('breadcrumbs') ?>

<a href="<?= admin_url('admin.php?page=improveseo_dashboard') ?>">Improve SEO</a>

&raquo;

<span>Settings</span>

<?php View::endSection('breadcrumbs') ?>

<?php View::startSection('content') ?>

<h1 class="hidden">Improve SEO Settings</h1>

<div class="seo-breadcumb">
        <div class="seo-text">
           <p> WordAi is a third party spinner that allows you to generate spun content on the fly. If you have a WordAi account, input your settings here. If not, leave the boxes blank. This is NOT a necessity to use Improve <b> SEO and only herefor convenience for users who need it.  </b></p>          
        </div>
    </div>
    <div class="global-wrap">
        <div class="head-bar">
            <img src="<?php echo WT_URL.'/assets/images/latest-images/seo-latest-logo.svg'?>" alt="project-list-logo"> 
            <h1> ImproveSEO | 2.0.11 </h1>
            <span>Pro</span>
        </div>
        <form class="improve-seo-form-global" method="post" action="options.php">
        <div class="box-top" style="display: flex; justify-content: space-between; align-items: center;">
            <ul class="breadcrumb-seo">
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<a href="#">Improve SEO</a></li>
                <li>Settings</li>
            </ul>
            <div class="import-export-btn" style="margin: 0px !important;">
                    <input type="submit" class="active setting_submit"  value="<?php _e('Save Changes') ?>" ></input>
                </div>
        </div>
        <div class="improve-seo-form-box"  style="padding-top: 0px !important;">
            <?php settings_fields('improveseo_settings'); ?>
                
                <div class="seo-form-field" style="margin-top: 0px !important;">
                    <label> Pixabay API Key </label>
                    <input type="text" placeholder="Ex. 236548568" name="improveseo_pixabay_key" value="<?php echo get_option('improveseo_pixabay_key'); ?>" > 
                    <span> How to Get a Free Pixabay API Key: </span>
                </div> 
                <div class="seo-form-field">
                    <label> Google API Key </label>
                    <input type="text" placeholder="Ex. 23654856845" name="improveseo_google_api_key" value="<?php echo get_option('improveseo_google_api_key'); ?>" > 
                    <span> How to Get a Free Google Maps API Key: </span>
                </div>
                <div class="seo-form-field">
                    <label> Chat GPT Key </label>
                    <input type="text" placeholder="Ex. sadfe456fds2v1xczv86s65g4s5fd4gr6e5tge5r4g54321xc86dssdfewtwerPP" name="improveseo_chatgpt_api_key" value="<?php echo get_option('improveseo_chatgpt_api_key'); ?>" > 
                </div>
                <div class="seo-form-field">
                    <label> Word AI Email </label>
                    <input type="text" placeholder="Ex. Ex." name="improveseo_word_ai_email" value="<?php echo get_option('improveseo_word_ai_email'); ?>"  > 
                </div>
                <div class="seo-form-field">
                    <label> Word AI Password </label>
                    <input type="text" placeholder="Ex. Ex." name="improveseo_word_ai_pass" value="<?php echo get_option('improveseo_word_ai_pass'); ?>"  > 
                </div>          
            </form>     
        </div>  
    </div>

    <div class="global-wrap seo-mt-30">
        <div class="local-seo">
            <h2>Local SEO Countries</h2>
            <p>Here you can select the countries that you would like included in the local SEO feature. 
               It's recommended to only select the countries that you need as the files are fairly large. 
               Upon selecting the desired country(s), the files will be downloaded from the ImprovedSEO cloud 
               and be ready for use within 1-2 minutes.
            </p>
            <?php
        $countries = ImproveSEO\Geo::getCountriesList();
        $countryModel = new ImproveSEO\Models\Country();
        $installedCountries = $countryModel->all('name');
        $installed = array();
        foreach ($installedCountries as $cc) {
        $installed[] = $cc->name;
        }
        ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Country Name</th>
                            <th>Filesize</th>
                            <th>Action</th>
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
                                <a href="<?php echo admin_url("admin.php?page=improveseo_settings&action=delete_country&country={$country->code}&noheader=true"); ?>" class="btn btn-outline-danger">Delete</a>
                                <?php else: ?>
                                <a href="<?php echo admin_url("admin.php?page=improveseo_settings&action=add_country&country={$country->code}&noheader=true"); ?>" class="btn btn-outline-primary">Download</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php View::endSection('content') ?>

<?php View::make('layouts.main') ?>