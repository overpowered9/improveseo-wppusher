<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}









// Removed with modules/cm-admin-settings.php in 2.0.12: this rendered the
// Shortcodes settings screen, whose add_submenu_page() registration had already
// been commented out. wt_load_templates() die()s on a missing file, so leaving
// the function behind would have turned re-enabling that menu line into a fatal.











add_action('admin_menu', 'improveseo_add_menu_items');


function improveseo_add_menu_items()


{


    //add_menu_page('Improve SEO', 'Improve SEO', 6, 'improveseo');


    add_menu_page('Improve SEO', 'Improve SEO', 'manage_options', 'improveseo_dashboard');


    


    add_submenu_page('improveseo_dashboard', 'Dashboard', 'Dashboard', 'manage_options', 'improveseo_dashboard', 'improveseo_dashboard');


    


add_submenu_page('improveseo_dashboard', 'Posting', 'Create Posts', 'manage_options', 'improveseo_posting', 'improveseo_posting');
// Hidden — redundant with improveseo_posting index cards; pages still accessible via direct URL
    add_submenu_page(
        null,
        'Create Single Post',
        'Create Single Post',
        'manage_options',
        'improveseo_create_single',
        function() {
            include_once WT_PATH . '/views/posting/index_single.php';
        }
    );
    add_submenu_page(
        null,
        'Create Bulk Post',
        'Create Bulk Post',
        'manage_options',
        'improveseo_create_bulk',
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

    add_submenu_page(
        'improveseo_dashboard',
        'Cron Status',
        'Cron Status',
        'manage_options',
        'improveseo_cron_status',
        function () {
            include_once WT_PATH . '/views/cron-status.php';
        }
    );


    // add_submenu_page('improveseo_dashboard', 'Authors', 'Authors', 'manage_options', 'improveseo_authors', 'improveseo_authors');


    


    //add_submenu_page('improveseo', 'Builder', 'Builder', 'manage_options', 'improveseo_builder', 'improveseo_builder');


    //add_submenu_page('improveseo', 'BuilderUpdate', 'BuilderUpdate', 'manage_options', 'improveseo_update_builder', 'improveseo_update_builder');





    


    //add_submenu_page('improveseo', 'Noindex Tags', 'Noindex Tags', 'manage_options', 'improveseo_noindex', 'improveseo_noindex');


    


    // Hidden onboarding wizard page — not shown in the sidebar nav (parent slug = null)
    add_submenu_page( null, 'ImproveSEO Setup', '', 'manage_options', 'improveseo_onboarding', 'improveseo_onboarding_page' );

}

function improveseo_onboarding_page() {
    include WT_PATH . '/views/onboarding/index.php';
}

/**
 * The plugin editor edits a project, not a post. Once a project has built its
 * WordPress post that post is the source of truth, so saving through the plugin
 * editor runs improveseo_builder_update(), which deletes the post and rebuilds
 * it from the project — throwing away anything edited in WordPress. Hand those
 * projects off to the real post editor; the plugin editor stays for drafts,
 * which have no post yet.
 *
 * Runs on admin_init because the page callback fires after the admin header is
 * printed, and wp_redirect() cannot send headers by then.
 */
function improveseo_redirect_edit_post_to_wp() {
    if (!isset($_GET['page'], $_GET['action'], $_GET['id'])) return;
    if ($_GET['page'] !== 'improveseo_dashboard' || $_GET['action'] !== 'edit_post') return;
    if (!current_user_can('manage_options')) return;

    global $wpdb;

    $post_id = $wpdb->get_var($wpdb->prepare(
        "SELECT post_id FROM {$wpdb->postmeta}
         WHERE meta_key = 'improveseo_project_id' AND meta_value = %s
         LIMIT 1",
        intval($_GET['id'])
    ));

    $post = $post_id ? get_post($post_id) : null;

    if ($post && $post->post_status !== 'trash' && current_user_can('edit_post', $post->ID)) {
        $edit_link = get_edit_post_link($post->ID, 'raw');

        if ($edit_link) {
            wp_safe_redirect($edit_link);
            exit;
        }
    }

    // No post to hand off to. Without '&update=true' this is the draft editor,
    // which is still the right place — let it render. With it, the form posts to
    // do_update_post and rebuilds on save, so refuse rather than fall through.
    if (isset($_GET['update'])) {
        ImproveSEO\FlashMessage::message('That project has no WordPress post to edit yet.', 'error');
        wp_safe_redirect(admin_url('admin.php?page=improveseo_projects'));
        exit;
    }
}
add_action('admin_init', 'improveseo_redirect_edit_post_to_wp');





add_action('admin_menu', function () {


    global $submenu;





    $submenu['improveseo_dashboard'][] = array('Support', 'manage_options', 'https://account.improveseoplugin.com/support');


});