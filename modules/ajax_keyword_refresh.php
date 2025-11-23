<?php

// AJAX handler for refreshing keyword lists dropdown
add_action('wp_ajax_refresh_keyword_lists', 'improveseo_refresh_keyword_lists');
function improveseo_refresh_keyword_lists() {
    global $wpdb;
    
    // Fetch all keyword lists from database
    $lists = $wpdb->get_results("SELECT id, name FROM {$wpdb->prefix}improveseo_lists ORDER BY name ASC");
    
    $keyword_lists = array();
    
    if ($lists) {
        foreach ($lists as $list) {
            $keyword_lists[$list->id] = $list->name;
        }
    }
    
    wp_send_json_success(array(
        'lists' => $keyword_lists,
        'count' => count($keyword_lists)
    ));
}

// AJAX handler for fetching keywords of a specific list
add_action('wp_ajax_get_keyword_list_data', 'improveseo_get_keyword_list_data');
function improveseo_get_keyword_list_data() {
    global $wpdb;
    
    $list_id = isset($_POST['list_id']) ? intval($_POST['list_id']) : 0;
    
    if (!$list_id) {
        wp_send_json_error(array('message' => 'Invalid list ID'));
        return;
    }
    
    $list = $wpdb->get_row($wpdb->prepare(
        "SELECT list FROM {$wpdb->prefix}improveseo_lists WHERE id = %d",
        $list_id
    ));
    
    if ($list && isset($list->list)) {
        $keywords = $list->list;
        $keyword_array = array_filter(array_map('trim', explode("\n", $keywords)));
        $keyword_count = count($keyword_array);
        $keyword_min = $keyword_count * 3;
        $keyword_time = number_format($keyword_min / 60, 2);
        
        wp_send_json_success(array(
            'keywords' => $keywords,
            'count' => $keyword_count,
            'time' => $keyword_time
        ));
    } else {
        wp_send_json_error(array('message' => 'List not found'));
    }
}
