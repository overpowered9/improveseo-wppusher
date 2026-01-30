<?php








function custom_testimonials_settings(){


	$ob = new WC_Testimonial;


	$ob->general_admin_notice();


    wt_load_templates('cm-admin-settings.php');


}











add_action('admin_menu', 'improveseo_add_menu_items');


function improveseo_add_menu_items()


{


    // Main menu
    add_menu_page('Improve SEO', 'Improve SEO', 'manage_options', 'improveseo_dashboard');

    // 1. Dashboard
    add_submenu_page('improveseo_dashboard', 'Dashboard', 'Dashboard', 'manage_options', 'improveseo_dashboard', 'improveseo_dashboard');

    // 2. Create Single Post
    add_submenu_page(
        'improveseo_dashboard',
        'Create Single Post',
        'Create Single Post',
        'manage_options',
        'improveseo_create_single',
        function() {
            include_once WT_PATH . '/views/posting/index_single.php';
        }
    );

    // 3. Create Bulk Post
    add_submenu_page(
        'improveseo_dashboard',
        'Create Bulk Post',
        'Create Bulk Post',
        'manage_options',
        'improveseo_create_bulk',
        function() {
            include_once WT_PATH . '/views/posting/index_multipost.php';
        }
    );

    // 4. Single Post Projects (was "Projects")
    add_submenu_page('improveseo_dashboard', 'Single Post Projects', 'Single Post Projects', 'manage_options', 'improveseo_projects', 'improveseo_projects');

    // 5. Bulk Post Projects (was "Bulk Projects Overview")
    add_submenu_page('improveseo_dashboard', 'Bulk Post Projects', 'Bulk Post Projects', 'manage_options', 'improveseo_bulkprojects', 'improveseo_bulkprojects');

    // 6. Keyword Lists (was "Lists")
    add_submenu_page('improveseo_dashboard', 'Keyword Lists', 'Keyword Lists', 'manage_options', 'improveseo_lists', 'improveseo_lists');

    // 7. Keyword Generator Tool (was "Keyword Generator")
    add_submenu_page('improveseo_dashboard', 'Keyword Generator Tool', 'Keyword Generator Tool', 'manage_options', 'improveseo_keyword_generator', 'improveseo_keyword_generator');

    // 8. Settings
    add_submenu_page('improveseo_dashboard', 'Settings', 'Settings', 'manage_options', 'improveseo_settings', 'improveseo_settings');

}





add_action('admin_menu', function () {


    global $submenu;





    $submenu['improveseo_dashboard'][] = array('FAQ', 'manage_options', 'http://bit.ly/improveseofaq');


});