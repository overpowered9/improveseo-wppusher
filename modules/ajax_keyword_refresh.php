<?php

// AJAX handler for refreshing keyword lists dropdown
add_action('wp_ajax_refresh_keyword_lists', 'improveseo_refresh_keyword_lists');
function improveseo_refresh_keyword_lists() {
    global $wpdb;
    
    // Fetch all keyword lists from database
    $lists = $wpdb->get_results("SELECT id, name, keyword FROM {$wpdb->prefix}improveseo_lists ORDER BY name ASC");
    
    $keyword_lists = array();
    $keywords_data = array();
    
    if ($lists) {
        foreach ($lists as $list) {
            $keyword_lists[$list->id] = $list->name;
            $keywords_data[$list->id] = $list->keyword ? $list->keyword : '';
        }
    }
    
    wp_send_json_success(array(
        'lists' => $keyword_lists,
        'keywords' => $keywords_data,
        'count' => count($keyword_lists)
    ));
}
