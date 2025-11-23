<?php

// AJAX handler for refreshing keyword lists dropdown
add_action('wp_ajax_refresh_keyword_lists', 'improveseo_refresh_keyword_lists');
function improveseo_refresh_keyword_lists() {
    global $wpdb;
    
    // Fetch all keyword lists from database with their keywords
    $lists = $wpdb->get_results("SELECT id, name, keywords FROM {$wpdb->prefix}improveseo_lists ORDER BY name ASC");
    
    $keyword_lists = array();
    $all_keywords = array();
    
    if ($lists) {
        foreach ($lists as $list) {
            $keyword_lists[$list->id] = $list->name;
            $all_keywords[$list->id] = $list->keywords;
        }
    }
    
    wp_send_json_success(array(
        'lists' => $keyword_lists,
        'keywords' => $all_keywords,
        'count' => count($keyword_lists)
    ));
}
