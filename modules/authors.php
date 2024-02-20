<?php

use ImproveSEO\View;
use ImproveSEO\FlashMessage;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly  

function improveseo_authors() {
    global $wpdb;

    $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'index';
    $limit = isset($_GET['limit']) ? absint($_GET['limit']) : 50;
    $offset = isset($_GET['paged']) ? absint($_GET['paged']) * $limit - $limit : 0;

    switch ($action) {
        case 'index':
            improveseo_handleIndexAction($offset, $limit);
            break;

        case 'create':
            improveseo_handleCreateAction();
            break;

        case 'do_create':
            improveseo_handleDoCreateAction();
            break;
    }
}

function improveseo_handleIndexAction($offset, $limit) {
    $results = count_users();

    $users = get_users([
        'role'   => 'improveseo_user',
        'offset' => $offset,
        'number' => $limit,
    ]);

    $pages = ceil($results['avail_roles']['improveseo_user'] / $limit);
    $page = floor($offset / $limit) + 1;

    View::render('authors.index', compact('users', 'results', 'page', 'pages'));
}

function improveseo_handleCreateAction() {
    View::render('authors.create');
}

function improveseo_handleDoCreateAction() {
    $total = isset($_POST['users']) ? absint($_POST['users']) : 0;

    if ($total <= 0) {
        wp_redirect(admin_url('admin.php?page=improveseo_authors&action=create'));
        exit;
    }

    

    $firstnames = include IMPROVESEO_ROOT . '/includes/authors/firstnames.php';
    $lastnames = include IMPROVESEO_ROOT . '/includes/authors/lastnames.php';

    $firstnamesTotal = count($firstnames);
    $lastnamesTotal = count($lastnames);
    $url = parse_url(get_option('siteurl'));

    for ($i = 1; $i <= $total; $i++) {
        $fidx = rand(0, $firstnamesTotal - 1);
        $lidx = rand(0, $lastnamesTotal - 1);

        $firstname = sanitize_text_field($firstnames[$fidx]);
        $lastname = sanitize_text_field($lastnames[$lidx]);

        $email = sanitize_email($firstname . $lastname . '@' . $url['host']);

        if (email_exists($email)) {
            $total++;
            continue;
        }

        $formatData = [
            'user_login'    => $firstname . $lastname,
            'user_pass'     => $firstname . $lastname . time() . rand(0, 1000000),
            'user_email'    => $email,
            'display_name'  => $firstname . ' ' . $lastname,
            'role'          => 'improveseo_user',
        ];

        $user_id = wp_insert_user(wp_slash($formatData));

        if (!is_wp_error($user_id)) {
            add_user_meta($user_id, 'improveseo_user', 1);
        }
    }

    FlashMessage::success("All $total authors created.");
    wp_redirect(admin_url('admin.php?page=improveseo_authors'));
    exit;
}
