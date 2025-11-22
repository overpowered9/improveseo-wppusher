<?php

use ImproveSEO\View;

?>

<?php View::startSection('breadcrumbs') ?>

<a href="<?= admin_url('admin.php?page=improveseo_dashboard') ?>">Improve SEO</a>

&raquo;

<span>Keyword Generator</span>

<?php View::endSection('breadcrumbs') ?>

<?php View::startSection('content') ?>

<h2 class="hidden">Keyword Generator</h2>

<div class="seo-breadcumb">
        <div class="seo-text">
           <p> The Improve SEO Keyword Generator takes a seed keyword and uses the Google autosuggest feature to generate a list of long tail keywords. You can put these long tail keywords into a Improve SEO List and make posts/pages for each keyword in the list!</p>          
        </div>
    </div>
    <div class="global-wrap">
        <div class="head-bar">
            <img src="<?php echo WT_URL . '/assets/images/latest-images/seo-latest-logo.svg' ?>" alt="project-list-logo">
            <h1> ImproveSEO | 2.0.11 </h1>
            <span>Pro</span>
        </div>
        <div class="box-top">
            <ul class="breadcrumb-seo">
                <li><a href="#">Improve SEO</a></li>
                <li> Keyword Generator</li>
                

            </ul>
            <ul><li>
                <button class=""
                    style="background:#ff9c33;color:#fff;border:1px solid #e07d00;padding:8px 14px;border-radius:4px;cursor:pointer;"
                    onclick="window.location.href='<?= admin_url('admin.php?page=improveseo_lists') ?>'">
                    View Keyword lists
                </button>
            </li></ul>
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
                        <input id="startjob" onclick="generate();" type="button" value="Generate Keywords!">
                        <div class="improve-submit-box-btns">
                            <input type="button" style="color:#ff4d4f !important" class="clear-search-results keyword_clear_btn" value="Clear Results"></input>
                            <input type="button" style="color:#59c174 !important" class="sw-save-search-results keyword_save_result_btn" value="Save Results"></input>
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
    var displayKeywords = [];
    var results = {};
    var initialKeywords = 0;
    var doWork = false;
    var queryKeywords = [];
    var queryKeywordsIndex = 0;
    var queryflag = false;
    var maxKeywords = 30; // Limit keywords to 30
    var creditsDeducted = false; // Flag to prevent duplicate credit deductions
    
    function generate()
    {
        console.log("🔵 KEYWORD.PHP: generate() called from views/features/keyword.php");
        
        if(doWork == false) {
            queryKeywords = [];
            queryKeywordsIndex = 0;
            displayKeywords = [];
            results = {'': 1, ' ': 1, '  ': 1};
            creditsDeducted = false; // Reset flag on new generation
            
            var ks = jQuery('#input').val().split("\n");
            var i = 0;
            for(i = 0; i < ks.length; i++) {
                queryKeywords[queryKeywords.length] = ks[i];
                displayKeywords[displayKeywords.length] = ks[i];
                var j = 0;
                for(j = 0; j < 26; j++) {
                    var chr = String.fromCharCode(97 + j);
                    var currentx = ks[i] + ' ' + chr;
                    queryKeywords[queryKeywords.length] = currentx;
                    results[currentx] = 1;
                }
            }
            initialKeywords = displayKeywords.length;
            doWork = true;
            jQuery('#startjob').val('Stop');
        }
        else {
            doWork = false;
            jQuery('#startjob').val('Start');
            // Deduct credits when generation is stopped manually (if keywords were generated)
            if(!creditsDeducted && displayKeywords.length > initialKeywords) {
                deductKeywordCredits();
            }
        }
    }
    function tick()
    {
        if(doWork == true && queryflag == false) {
            // Stop if we've reached the maximum number of keywords
            if(displayKeywords.length >= maxKeywords) {
                doWork = false;
                jQuery('#startjob').val('Start');
                // Deduct credits when max keywords reached
                if(!creditsDeducted) {
                    deductKeywordCredits();
                }
                return;
            }
            if(queryKeywordsIndex < queryKeywords.length) {
                var currentKw = queryKeywords[queryKeywordsIndex];
                query(currentKw);
                queryKeywordsIndex++;
            }
            else {
                if (initialKeywords != displayKeywords.length) {
                    doWork = false;
                    jQuery('#startjob').val('Start');
                    // Deduct credits when generation completes successfully
                    if(!creditsDeducted) {
                        deductKeywordCredits();
                    }
                }
                else {
                    queryKeywordsIndex = 0;
                }
            }
        }
    }
    function query(keyword)
    {
    var querykeyword = keyword;
    var queryresult = '';
    queryflag = true;
    jQuery.ajax({
    url: 'https://suggestqueries.google.com/complete/search',
    jsonp: 'jsonp',
    dataType: 'jsonp',
    data: {
    q: querykeyword,
    client: 'chrome'
    },
    success: function(res) {
        var retList = res[1];
        for(var i = 0; i < retList.length; i++) {
            // Stop adding keywords if we've reached the limit
            if(displayKeywords.length >= maxKeywords) {
                doWork = false;
                jQuery('#startjob').val('Start');
                queryflag = false;
                // Deduct credits when limit reached
                if(!creditsDeducted) {
                    deductKeywordCredits();
                }
                return;
            }
            var currents = clean(retList[i]);
            if(results[currents] != 1) {
                results[currents] = 1;
                displayKeywords[displayKeywords.length] = clean(retList[i]);
                queryKeywords[queryKeywords.length] = currents;
                for(var j = 0; j < 26; j++) {
                    var chr = String.fromCharCode(97 + j);
                    var currentx = currents + ' ' + chr;
                    queryKeywords[queryKeywords.length] = currentx;
                    results[currentx] = 1;
                }
            }
        }
        display();
        var textarea = document.getElementById("input");
        textarea.scrollTop = textarea.scrollHeight;
        queryflag = false;
    }
    });
    }
    function clean(input)
    {
        var val = input;
        val = val.replace("\\u003cb\\u003e", "");
        val = val.replace("\\u003c\\/b\\u003e", "");
        val = val.replace("\\u003c\\/b\\u003e", "");
        val = val.replace("\\u003cb\\u003e", "");
        val = val.replace("\\u003c\\/b\\u003e", "");
        val = val.replace("\\u003cb\\u003e", "");
        val = val.replace("\\u003cb\\u003e", "");
        val = val.replace("\\u003c\\/b\\u003e", "");
        val = val.replace("\\u0026amp;", "&");
        val = val.replace("\\u003cb\\u003e", "");
        val = val.replace("\\u0026", "");
        val = val.replace("\\u0026#39;", "'");
        val = val.replace("#39;", "'");
        val = val.replace("\\u003c\\/b\\u003e", "");
        val = val.replace("\\u2013", "2013");
        if (val.length > 4 && val.substring(0, 4) == "http") val = "";
        return val;
    }
    
    function deductKeywordCredits()
    {
        // Prevent duplicate calls
        if(creditsDeducted) {
            console.log('⚠️ Credits already deducted, skipping duplicate call');
            return;
        }
        
        var generatedKeywordsCount = displayKeywords.length - initialKeywords;
        
        // Only deduct if new keywords were actually generated
        if(generatedKeywordsCount <= 0) {
            console.log('⚠️ No new keywords generated, skipping credit deduction');
            return;
        }
        
        // Mark as deducted immediately to prevent race conditions
        creditsDeducted = true;
        
        console.log('💳 Deducting credits for ' + generatedKeywordsCount + ' keywords...');
        
        // Get API credentials from WordPress options
        var apiKey = '<?php echo esc_js(get_option("improveseo_api_key")); ?>';
        var siteCode = '<?php echo esc_js(get_option("improveseo_site_code")); ?>';
        
        if(!apiKey || !siteCode) {
            console.error('❌ Missing API credentials. Please configure API Key and Site Code in settings.');
            creditsDeducted = false; // Reset flag on error
            return;
        }
        
        jQuery.ajax({
            url: 'https://imporve-seo-admin-server.onrender.com/api/v1/generate/generatekeywordded',
            type: 'POST',
            contentType: 'application/json',
            headers: {
                'X-API-Key': apiKey,
                'X-Site-Code': siteCode
            },
            data: JSON.stringify({
                keywords_generated: generatedKeywordsCount,
                total_keywords: displayKeywords.length,
                seed_keyword: jQuery('#input').val(),
                max_limit: maxKeywords
            }),
            success: function(response) {
                console.log('✅ Credits deducted successfully:', response);
                // Optionally show user notification
                if(response.remaining_credits !== undefined) {
                    console.log('📊 Remaining credits: ' + response.remaining_credits);
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ Failed to deduct credits:', error);
                console.error('Response:', xhr.responseText);
                creditsDeducted = false; // Reset flag on error to allow retry
                
                // Show error message to user
                if(xhr.status === 401) {
                    alert('Authentication failed. Please check your API credentials in settings.');
                } else if(xhr.status === 402) {
                    alert('Insufficient credits. Please upgrade your plan.');
                } else {
                    alert('Failed to deduct credits. Please contact support if this issue persists.');
                }
            }
        });
    }
    
    function display()
    {
        var sb = '';
        var outputKeywords = displayKeywords;
        for (var i = 0; i < outputKeywords.length; i++) {
            sb += outputKeywords[i];
            sb += '\n';
        }
        jQuery('#output').val(sb);
    }
    window.setInterval(tick, 750);
    </script>
<?php View::endSection('content') ?>

<?php View::make('layouts.main') ?>