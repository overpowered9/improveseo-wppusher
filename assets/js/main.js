(function($) {
    $(document).on("click", '.category_improveseo input[type="checkbox"]', function() {
        var vals = $('input[type="checkbox"]:checked').map(function() { return this.value; }).get();
        var mainDomain = ajax_object.site_url; 

        $('.Posting__post-button').attr('href', mainDomain + '/wp-admin/admin.php?page=improveseo_posting&action=create_post&cat_pre=' + vals.join(","));
        $('.Posting__page-button').attr('href', mainDomain + '/wp-admin/admin.php?page=improveseo_posting&action=create_page&cat_pre=' + vals.join(","));
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

    $(document).ready(function () {
        $('#on-page-seo').change(function () {
            if ($(this).is(':checked')) {
                $('#on-page-seo-wrap').show();
            } else {
                $('#on-page-seo-wrap').hide();
            }
        }).trigger('change'); 
    });

})(jQuery); 
