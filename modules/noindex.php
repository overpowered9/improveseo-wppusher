<?php
use ImproveSEO\View;
use ImproveSEO\FlashMessage;

function improveseo_noindex() {
    global $wpdb;

    $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'index';
    $limit = isset($_GET['limit']) ? absint($_GET['limit']) : 50;  // Ensure it's a positive integer
    $offset = isset($_GET['paged']) ? absint($_GET['paged']) * $limit - $limit : 0;  // Ensure it's a positive integer

    if ($action == 'index'):
        $results = $wpdb->get_row($wpdb->prepare(
            "SELECT COUNT(*) AS total FROM {$wpdb->prefix}termmeta WHERE meta_key = %s",
            'improveseo_noindex_tag'
        ));

        $tags = $wpdb->get_results($wpdb->prepare(
            "SELECT t.* FROM {$wpdb->prefix}termmeta AS m INNER JOIN {$wpdb->prefix}terms AS t ON t.term_id = m.term_id WHERE m.meta_key = %s LIMIT %d, %d",
            'improveseo_noindex_tag',
            $offset,
            $limit
        ));

        $pages = ceil($results->total / $limit);
        $page = floor($offset / $limit) + 1;

        View::render('noindex.index', compact('tags', 'results', 'page', 'pages'));
    elseif ($action == 'remove'):
        $id = isset($_GET['id']) ? absint($_GET['id']) : 0;  // Ensure it's a positive integer

        if ($id > 0) {
            $wpdb->query($wpdb->prepare(
                "DELETE FROM {$wpdb->prefix}termmeta WHERE term_id = %d AND meta_key = %s",
                $id,
                'improveseo_noindex_tag'
            ));

            FlashMessage::success('Noindex tag removed.');
        }

        wp_redirect(admin_url('admin.php?page=improveseo_noindex'));
        exit;
    endif;
}
