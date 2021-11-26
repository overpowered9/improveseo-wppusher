<?php


function custom_testimonials_settings(){
	$ob = new WC_Testimonial;
	$ob->general_admin_notice();
    wt_load_templates('cm-admin-settings.php');
}



add_action('admin_menu', 'improveseo_add_menu_items');
function improveseo_add_menu_items()
{
    add_menu_page('Improve SEO', 'Improve SEO', 6, 'improveseo');
    
    
    add_submenu_page('improveseo', 'Posting', 'Posting', 1, 'improveseo', 'improveseo_posting');
    add_submenu_page('improveseo', 'Dashboard', 'Dashboard', 1, 'improveseo_dashboard', 'improveseo_dashboard');
    add_submenu_page('improveseo', 'Projects', 'Projects', 1, 'improveseo_projects', 'improveseo_projects');
    add_submenu_page('improveseo', 'Shortcodes', 'Shortcodes', 2, 'improveseo_shortcodes', 'improveseo_shortcodes');
    add_submenu_page('improveseo', 'Lists', 'Lists', 2, 'improveseo_lists', 'improveseo_lists');
    add_submenu_page('improveseo', 'Settings', 'Settings', 2, 'improveseo_settings', 'improveseo_settings');
    add_submenu_page('improveseo', 'Users', 'Users', 2, 'improveseo_users', 'improveseo_users');
    
    //add_submenu_page('improveseo', 'Builder', 'Builder', 0, 'improveseo_builder', 'improveseo_builder');
    //add_submenu_page('improveseo', 'BuilderUpdate', 'BuilderUpdate', 0, 'improveseo_update_builder', 'improveseo_update_builder');

    
    //add_submenu_page('improveseo', 'Noindex Tags', 'Noindex Tags', 0, 'improveseo_noindex', 'improveseo_noindex');
    add_submenu_page('improveseo', 'Keyword Generator', 'Keyword Generator', 0, 'improveseo_keyword_generator', 'improveseo_keyword_generator');
    add_submenu_page('improveseo', 'Testimonials', 'Testimonials/Buttons/Maps', 0 ,'testimonials_googlemaps', 'custom_testimonials_settings');
}

add_action('admin_menu', function () {
    global $submenu;

    $submenu['improveseo'][] = array('FAQ', 0, 'http://bit.ly/improveseofaq');
});