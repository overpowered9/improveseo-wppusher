<?php


function custom_testimonials_settings(){
	$ob = new WC_Testimonial;
	$ob->general_admin_notice();
    wt_load_templates('cm-admin-settings.php');
}



add_action('admin_menu', 'workhorse_add_menu_items');
function workhorse_add_menu_items()
{
    add_menu_page('Improve SEO', 'Improve SEO', 6, 'workhorse');
    
    
    add_submenu_page('workhorse', 'Posting', 'Posting', 1, 'workhorse', 'workhorse_posting');
    add_submenu_page('workhorse', 'Dashboard', 'Dashboard', 1, 'workhorse_dashboard', 'workhorse_dashboard');
    add_submenu_page('workhorse', 'Projects', 'Projects', 1, 'workhorse_projects', 'workhorse_projects');
    add_submenu_page('workhorse', 'Shortcodes', 'Shortcodes', 2, 'workhorse_shortcodes', 'workhorse_shortcodes');
    add_submenu_page('workhorse', 'Lists', 'Lists', 2, 'workhorse_lists', 'workhorse_lists');
    add_submenu_page('workhorse', 'Settings', 'Settings', 2, 'workhorse_settings', 'workhorse_settings');
    add_submenu_page('workhorse', 'Users', 'Users', 2, 'workhorse_users', 'workhorse_users');
    
    //add_submenu_page('workhorse', 'Builder', 'Builder', 0, 'workhorse_builder', 'workhorse_builder');
    //add_submenu_page('workhorse', 'BuilderUpdate', 'BuilderUpdate', 0, 'workhorse_update_builder', 'workhorse_update_builder');

    
    //add_submenu_page('workhorse', 'Noindex Tags', 'Noindex Tags', 0, 'workhorse_noindex', 'workhorse_noindex');
    add_submenu_page('workhorse', 'Keyword Generator', 'Keyword Generator', 0, 'workhorse_keyword_generator', 'workhorse_keyword_generator');
    add_submenu_page('workhorse', 'Testimonials', 'Testimonials/Buttons/Maps', 0 ,'testimonials_googlemaps', 'custom_testimonials_settings');
}

add_action('admin_menu', function () {
    global $submenu;

    $submenu['workhorse'][] = array('FAQ', 0, 'http://bit.ly/workhorsefaq');
});