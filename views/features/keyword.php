<?php
use ImproveSEO\View;
?>
<?php View::startSection('breadcrumbs') ?>
<a href="<?= admin_url('admin.php?page=improveseo') ?>">Improve SEO</a>
&raquo;
<span>Keyword Generator</span>
<?php View::endSection('breadcrumbs') ?>
<?php View::startSection('content') ?>
<h2 class="hidden">Keyword Generator</h2>
<div class="notice notice-success is-dismissible">
    <p>
        The Improve SEO Keyword Generator takes a seed keyword and uses the Google autosuggest feature to generate a list of long tail keywords. You can put these long tail keywords into a Improve SEO List and make posts/pages for each keyword in the list!
    </p>
</div>
<div class="keywords improveseo_wrapper p-3 p-lg-4">
    <section class="project-section border-bottom">
        <div class="project-heading d-flex flex-row align-items-center pb-2">
            <img class="mr-2" src="<?php echo WT_URL.'/assets/images/project-list-logo.png'?>" alt="ImproveSeo">
            <h1>Keyword Generator</h1>
        </div>
    </section>
    <section class="form-wrap">
        <div class="PostForm mt-3">
            <div class="BasicForm__row">
                <div class="input-group">
                    <label class="form-label">Seed Keyword</label>
                    <div class="input-prefix">
                        <input type="text" id="input" class="form-control" placeholder="Monkhk " class="sw-project-name keyword_input" value=""></input>
                        <span>Ex.</span>
                    </div>
                </div>
            </div>
            <div class="BasicForm__row">
                <label class="form-label">Results</label>
                <div class="input-prefix">
                    <textarea id="output" rows="5" class="textarea-control sw-output-ta keyword_input" placeholder="4Monkhk"></textarea>
                    <span>Ex.</span>
                </div>
            </div>
            <div class="BasicForm__row mb-3">
                <input id="startjob" onclick="generate();" type="button" class="btn btn-outline-primary mr-2 mb-2 mb-sm-0" value="Generate Keywords!">
                <input type="button" class="clear-search-results btn btn-outline-primary mr-2 mb-2 mb-sm-0" value="Clear Results">
                <input type="button" class="sw-save-search-results btn btn-outline-primary" value="Save Results">
                <?php
                
                wt_load_templates('sw-all-saved-keywords.php')
                
                ?>
                
            </div>
        </div>
    </section>
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
</div>
<?php View::endSection('content') ?>
<?php View::make('layouts.main') ?>