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
                            <input type="button" class="clear-search-results keyword_clear_btn" value="Clear Results"></input>
                            <input type="button" class="sw-save-search-results keyword_save_result_btn" value="Save Results"></input>
                        </div>
                    </div>
                    
                </div>          
            </form>     
        </div>  
    </div>

    <div class="global-wrap seo-mt-30">
        <div class="improve-seo-container">
            <div class="project-lists">
                <table>
                    <thead>
                      <tr>
                        <th> No </th>
                        <th>Project Name</th>
                        <th> </th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td data-label="Name"> 1 </td>
                        <td data-label="Project Name"> <strong> Cricket </strong> </td>
                        <td data-label="Last Update"> 
                          <div>
                            <a href="#"> <img src="<?php echo WT_URL . '/assets/images/latest-images/write.svg' ?>" alt="write"> </a> 
                            <a href="#"> <img src="<?php echo WT_URL . '/assets/images/latest-images/delete.svg' ?>" alt="delete"> </a>
                          </div>
                        </td>
                      </tr>
                      <tr>
                        <td data-label="Name"> 2 </td>
                        <td data-label="Project Name"> <strong> Shoes </strong> </td>
                        <td data-label="Last Update"> 
                          <div>
                            <a href="#"> <img src="<?php echo WT_URL . '/assets/images/latest-images/write.svg' ?>"  alt="write"> </a> 
                            <a href="#"> <img src="<?php echo WT_URL . '/assets/images/latest-images/delete.svg' ?>" alt="delete"> </a>
                          </div>
                        </td>
                      </tr>
                    </tbody>
                </table>
            </div>
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
    function generate()
    {
    if(doWork == false) {
    queryKeywords = [];
    queryKeywordsIndex = 0;
    displayKeywords = [];
    results = {'': 1, ' ': 1, '  ': 1};
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
    }
    }
    function tick()
    {
    if(doWork == true && queryflag == false) {
    if(queryKeywordsIndex < queryKeywords.length) {
    var currentKw = queryKeywords[queryKeywordsIndex];
    query(currentKw);
    queryKeywordsIndex++;
    }
    else {
    if (initialKeywords != displayKeywords.length) {
    doWork = false;
    jQuery('#startjob').val('Start');
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