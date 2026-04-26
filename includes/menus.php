<?php








function custom_testimonials_settings(){


	$ob = new WC_Testimonial;


	$ob->general_admin_notice();


    wt_load_templates('cm-admin-settings.php');


}











add_action('admin_menu', 'improveseo_add_menu_items');


function improveseo_add_menu_items()


{


    //add_menu_page('Improve SEO', 'Improve SEO', 6, 'improveseo');


    add_menu_page('Improve SEO', 'Improve SEO', 'manage_options', 'improveseo_dashboard');


    


    add_submenu_page('improveseo_dashboard', 'Dashboard', 'Dashboard', 'manage_options', 'improveseo_dashboard', 'improveseo_dashboard');


    


add_submenu_page('improveseo_dashboard', 'Posting', 'Create Posts', 'manage_options', 'improveseo_posting', 'improveseo_posting');
add_submenu_page(
        'improveseo_dashboard',
        'Create Single Post',         // Page title
        'Create Single Post',         // Menu title
        'manage_options',
        'improveseo_create_single',   // Unique slug
        function() {
            include_once WT_PATH . '/views/posting/index_single.php';
        }
    );
    add_submenu_page(
        'improveseo_dashboard',
        'Create Bulk Post',           // Page title
        'Create Bulk Post',           // Menu title
        'manage_options',
        'improveseo_create_bulk',     // Unique slug
        function() {
            include_once WT_PATH . '/views/posting/index_multipost.php';
        }
    );




    add_submenu_page('improveseo_dashboard', 'Projects', 'Single Post Projects', 'manage_options', 'improveseo_projects', 'improveseo_projects');





    add_submenu_page('improveseo_dashboard', 'Bulk Projects Overview', 'Bulk Post Projects', 'manage_options', 'improveseo_bulkprojects', 'improveseo_bulkprojects');


    //add_submenu_page('improveseo', 'Shortcodes', 'Shortcodes', 'manage_options', 'improveseo_shortcodes', 'improveseo_shortcodes');


    add_submenu_page('improveseo_dashboard', 'Lists', 'Keyword Lists', 'manage_options', 'improveseo_lists', 'improveseo_lists');

    add_submenu_page('improveseo_dashboard', 'Keyword Generator', 'Keywords Generator Tool', 'manage_options', 'improveseo_keyword_generator', 'improveseo_keyword_generator');

    add_submenu_page('improveseo_dashboard', 'Settings', 'Settings', 'manage_options', 'improveseo_settings', 'improveseo_settings');


    // add_submenu_page('improveseo_dashboard', 'Authors', 'Authors', 'manage_options', 'improveseo_authors', 'improveseo_authors');


    


    //add_submenu_page('improveseo', 'Builder', 'Builder', 'manage_options', 'improveseo_builder', 'improveseo_builder');


    //add_submenu_page('improveseo', 'BuilderUpdate', 'BuilderUpdate', 'manage_options', 'improveseo_update_builder', 'improveseo_update_builder');





    


    //add_submenu_page('improveseo', 'Noindex Tags', 'Noindex Tags', 'manage_options', 'improveseo_noindex', 'improveseo_noindex');


    

    // add_submenu_page('improveseo_dashboard', 'Shortcodes', 'Shortcodes', 'manage_options','improveseo_shortcodes', 'custom_testimonials_settings');

    // Hidden onboarding wizard page — not shown in the sidebar nav (parent slug = null)
    add_submenu_page( null, 'ImproveSEO Setup', '', 'manage_options', 'improveseo_onboarding', 'improveseo_onboarding_page' );

}

function improveseo_onboarding_page() {
    include WT_PATH . '/views/onboarding/index.php';
}





add_action('admin_menu', function () {


    global $submenu;





    $submenu['improveseo_dashboard'][] = array('FAQ', 'manage_options', 'http://bit.ly/improveseofaq');


});