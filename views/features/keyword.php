<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


use ImproveSEO\View;

?>

<?php View::startSection('breadcrumbs') ?>

<a href="<?php echo esc_url( admin_url('admin.php?page=improveseo_dashboard') ); ?>">Improve SEO</a>

&raquo;

<span>Keyword Generator</span>

<?php View::endSection('breadcrumbs') ?>

<?php View::startSection('content') ?>

<style>
/* Clear and Save Results buttons hover state */
input.keyword_clear_btn:hover,
input.clear-search-results.keyword_clear_btn:hover {
    color: #fff !important;
    background-color: #ff4d4f !important;
}

input.keyword_save_result_btn:hover,
input.sw-save-search-results.keyword_save_result_btn:hover {
    color: #fff !important;
    background-color: #59c174 !important;
}

/* Style View Keyword Lists button to match Generate Keywords button */
.view-keyword-lists-btn {
    background: linear-gradient(181deg, #ff9c33 0%, #f07d00 48%, #e07d00 100%) !important;
    color: #fff !important;
    max-width: 263px;
    width: 100%;
    padding: 9px 26px;
    border-radius: 50px;
    cursor: pointer;
    font-size: 20px;
    outline: none;
    border: 1px solid #e07d00 !important;
    font-family: "Poppins", serif;
    font-weight: 500;
    transition: background 0.3s ease;
}

.view-keyword-lists-btn:hover,
.view-keyword-lists-btn:focus {
    background: linear-gradient(100deg, #ff9c33 0%, #e07d00 48%, #e07d00 100%) !important;
}
</style>

<h2 class="hidden">Keyword Generator</h2>

<div class="seo-breadcumb">
        <div class="seo-text">
           <p> The Improve SEO Keyword Generator takes a seed keyword and uses AI to generate a list of long tail keywords. You can put these long tail keywords into a Improve SEO List and make posts/pages for each keyword in the list!</p>
        </div>
    </div>
    <div class="global-wrap">
        <div class="head-bar">
            <img src="<?php echo esc_url( improveseo_logo_url() ); ?>" alt="ImproveSEO logo">
            <h1>ImproveSEO | <?php echo IMPROVESEO_VERSION; ?></h1>
        </div>
        <div class="box-top">
            <ul class="breadcrumb-seo">
                <li><a href="#">Improve SEO</a></li>
                <li> Keyword Generator</li>
                

            </ul>
        </div>
        <div class="improve-seo-form-box" style="padding-bottom: 0;">
            <form class="improve-seo-form-global">
                <div class="seo-form-field">
                    <label> Seed Keyword </label>
                    <input type="text" id="input" class="sw-project-name keyword_input" placeholder="Write your Seed Keywords here"> 
                </div> 
                <div class="seo-form-field">
                    <label> Results </label>
                    <textarea type="text" id="output" rows="5" class="textarea-control sw-output-ta keyword_input" placeholder="" style="height: 140px;"></textarea>
                </div>
                <div class="seo-form-field">
                    <div class="improve-submit-box"> 
                        <div style="display: flex; align-items: center; gap: 15px; flex-wrap: nowrap;">
                            <input id="startjob" onclick="generate();" type="button" value="Generate Keywords!">
                            <input id="viewkeywordlists" type="button" onclick="window.location.href='<?php echo esc_url( admin_url('admin.php?page=improveseo_lists') ); ?>'" value="View Keyword lists" class="view-keyword-lists-btn">
                        </div>
                        <div class="improve-submit-box-btns">
                            <input type="button" class="clear-search-results keyword_clear_btn" value="Clear Results"></input>
                            <input type="button" class="sw-save-search-results keyword_save_result_btn" value="Save Results"></input>
                        </div>
                    </div>
                    
                </div>          
            </form>     
        </div>  
    </div>

   
    <?php
                
                wt_load_templates('sw-all-saved-keywords.php')
                
                ?>
                <script>
    var maxKeywords = 30; // Limit keywords to 30

    function generate()
    {
        console.log("🔵 KEYWORD.PHP: generate() called from views/features/keyword.php");

        var seed = (jQuery('#input').val() || '').trim();
        if (!seed) {
            alert('Please enter a seed keyword first.');
            return;
        }

        var apiKey = '<?php echo esc_js(get_option("improveseo_api_key")); ?>';
        var siteCode = '<?php echo esc_js(get_option("improveseo_site_code")); ?>';
        if (!apiKey || !siteCode) {
            alert('Missing API credentials. Please configure API Key and Site Code in settings.');
            return;
        }

        var btn = jQuery('#startjob');
        if (btn.prop('disabled')) {
            return;
        }
        var originalLabel = btn.val();
        btn.prop('disabled', true).val('Generating...');

        jQuery.ajax({
            url: 'https://imporve-seo-admin-server-nzbm.onrender.com/api/v1/generate/generatekeyword',
            type: 'POST',
            contentType: 'application/json',
            timeout: 90000,
            headers: {
                'X-API-Key': apiKey,
                'X-Site-Code': siteCode
            },
            data: JSON.stringify({
                seed_keyword: seed,
                count: maxKeywords
            }),
            success: function(response) {
                var keywords = (response.data && response.data.keywords) || [];
                jQuery('#output').val(keywords.join('\n'));
                console.log('✅ ' + keywords.length + ' keywords generated (1 credit deducted server-side)');
            },
            error: function(xhr, status, error) {
                console.error('❌ Keyword generation failed:', status, error, xhr.responseText);
                var serverError = (xhr.responseJSON && xhr.responseJSON.error) || '';
                if (xhr.status === 402) {
                    alert(serverError || 'Insufficient keyword credits. Please upgrade your plan or buy credits.');
                } else if (xhr.status === 401) {
                    alert('Authentication failed. Please check your API credentials in settings.');
                } else {
                    alert(serverError || 'Keyword generation failed. Please try again.');
                }
            },
            complete: function() {
                btn.prop('disabled', false).val(originalLabel);
            }
        });
    }
    </script>
<?php View::endSection('content') ?>

<?php View::make('layouts.main') ?>