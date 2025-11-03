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
           <p>Configure your ImproveSEO server settings to enable AI content generation. Get your API Key and Site Code from your <a href="http://localhost:3000" target="_blank">ImproveSEO Dashboard</a>.</p>          
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
                
                <!-- ImproveSEO Server Configuration -->
                <div class="seo-form-field" style="margin-top: 0px !important; border: 2px solid #007cba; background: #f7f9fc; padding: 15px; border-radius: 5px;">
                    <h3 style="margin-top: 0; color: #007cba;">🚀 ImproveSEO Server Settings</h3>
                    
                    <div style="margin-bottom: 15px;">
                        <label> API Key </label>
                        <input type="text" placeholder="Your API Key from ImproveSEO Dashboard" name="improveseo_api_key" value="<?php echo get_option('improveseo_api_key'); ?>" > 
                        <span>Get this from your ImproveSEO Dashboard overview tab</span>
                    </div>
                    
                    <div style="margin-bottom: 15px;">
                        <label> Site Code </label>
                        <input type="text" placeholder="Your Site Code from ImproveSEO Dashboard" name="improveseo_site_code" value="<?php echo get_option('improveseo_site_code'); ?>" > 
                        <span>Get this from your ImproveSEO Dashboard websites tab</span>
                    </div>
                    
                    <div style="background: #e8f5e8; border: 1px solid #28a745; padding: 10px; border-radius: 3px; margin-top: 10px;">
                        
                        <strong>How to get credentials:</strong><br>
                        1. Visit your <a href="https://dashboard.improveseoplugin.com" target="_blank">ImproveSEO Dashboard</a><br>
                        2. Copy your API Key from the Overview tab<br>
                        3. Go to Websites tab and add this domain<br>
                        4. Copy the Site Code and paste above<br>
                        5. Save settings and start generating content!
                    </div>
                </div>
                
                <!-- Connection Test Button -->
                <div class="seo-form-field">
                    <button type="button" id="test_server_connection" class="button button-secondary">
                        🔌 Test Server Connection
                    </button>
                    <div id="connection_status" style="margin-top: 10px;"></div>
                </div>

                <!-- Legacy Settings (Hidden but kept for compatibility) -->
                <div style="display: none;">
                    <div class="seo-form-field">
                        <label> Chat GPT Key (Legacy - Hidden) </label>
                        <input type="text" placeholder="Ex. sadfe456fds2v1xczv86s65g4s5fd4gr6e5tge5r4g54321xc86dssdfewtwerPP" name="improveseo_chatgpt_api_key" value="<?php echo get_option('improveseo_chatgpt_api_key'); ?>" > 
                    </div>
                </div>

                <!-- Commented out irrelevant fields for now
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
                    <label> Word AI Email </label>
                    <input type="text" placeholder="Ex. Ex." name="improveseo_word_ai_email" value="<?php echo get_option('improveseo_word_ai_email'); ?>"  > 
                </div>
                <div class="seo-form-field">
                    <label> Word AI Password </label>
                    <input type="text" placeholder="Ex. Ex." name="improveseo_word_ai_pass" value="<?php echo get_option('improveseo_word_ai_pass'); ?>"  > 
                </div>
                -->          
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Test server connection
    document.getElementById('test_server_connection').addEventListener('click', function() {
        const button = this;
        const statusDiv = document.getElementById('connection_status');
        
        // Get form values (server URL is fixed)
        const apiKey = document.querySelector('input[name="improveseo_api_key"]').value;
        const siteCode = document.querySelector('input[name="improveseo_site_code"]').value;
        
        // Validate inputs
        if (!apiKey || !siteCode) {
            statusDiv.innerHTML = '<div style="color: #dc3545; padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 3px;">❌ Please fill in API Key and Site Code first.</div>';
            return;
        }
        
        // Show loading
        button.disabled = true;
        button.textContent = '🔄 Testing Connection...';
        statusDiv.innerHTML = '<div style="color: #856404; padding: 10px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 3px;">⏳ Testing connection to ImproveSEO server...</div>';
        
        // Test connection using WordPress AJAX
        const data = new FormData();
        data.append('action', 'test_improveseo_connection');
        data.append('api_key', apiKey);
        data.append('site_code', siteCode);
        data.append('nonce', '<?php echo wp_create_nonce("test_connection_nonce"); ?>');
        
        fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
            method: 'POST',
            body: data
        })
        .then(response => response.json())
        .then(result => {
            button.disabled = false;
            button.textContent = '🔌 Test Server Connection';
            
            if (result.success) {
                statusDiv.innerHTML = `
                    <div style="color: #155724; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 3px;">
                        ✅ <strong>Connection Successful!</strong><br>
                        📊 Credits: ${result.data.credits ? 
                            `Images: ${result.data.credits.images}, Content: ${result.data.credits.content}, Keywords: ${result.data.credits.keywords}` : 
                            'Available'}<br>
                        🔑 User: ${result.data.user || 'Authenticated'}<br>
                        🌐 Server: ${result.data.server || 'Connected'}
                    </div>
                `;
            } else {
                statusDiv.innerHTML = `
                    <div style="color: #721c24; padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 3px;">
                        ❌ <strong>Connection Failed!</strong><br>
                        Error: ${result.data.error || 'Unknown error'}<br>
                        Please check your settings and try again.
                    </div>
                `;
            }
        })
        .catch(error => {
            button.disabled = false;
            button.textContent = '🔌 Test Server Connection';
            statusDiv.innerHTML = `
                <div style="color: #721c24; padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 3px;">
                    ❌ <strong>Connection Test Failed!</strong><br>
                    Error: ${error.message}<br>
                    Please check your server URL and network connection.
                </div>
            `;
        });
    });
});
</script>

<?php View::endSection('content') ?>

<?php View::make('layouts.main') ?>