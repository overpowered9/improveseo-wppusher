(function($) {
    var get_al = '';
    
    $(document).on("click", '.category_improveseo input[type="checkbox"]', function() {
        var vals = $('input[type="checkbox"]:checked').map(function() { return this.value; }).get();
        
        var mainDomain = improveSeoData.site_url; // Get main domain from localized data
        
        $('.Posting__post-button').attr('href', mainDomain + '/wp-admin/admin.php?page=improveseo_posting&action=create_post&cat_pre=' + vals.join(","));
        $('.Posting__page-button').attr('href', mainDomain + '/wp-admin/admin.php?page=improveseo_posting&action=create_page&cat_pre=' + vals.join(","));
        
        console.log(vals);
    });

    $(document).on("click", '.improveseo_wrapper .nav-tabs .nav-link', function(e) {
        e.preventDefault();
        $(".improveseo_wrapper .nav-tabs .nav-link").removeClass('active');
        $(".improveseo_wrapper .tab-content .tab-pane").removeClass("show active");
        var contentId = $(this).attr("href");
        $(this).addClass('active');
        $('.improveseo_wrapper ' + contentId).addClass('show active');
    });

    $('#importProject').click(function() {
        $('.project-import-box').show('slow');
    });

})(jQuery);
