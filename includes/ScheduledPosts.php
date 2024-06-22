<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly  

if (get_option("improveseo_scheduled_last_execute_time") == false) {
    update_option('improveseo_scheduled_last_execute_time', time());
}

if (get_option("improveseo_scheduled_execute_time") == false) {
    update_option("improveseo_scheduled_execute_time", 20);
}

if (time() >= get_option("improveseo_scheduled_last_execute_time") + 300) {
    add_action('wp_head', 'improveseo_publish_missed_posts');
}

function improveseo_publish_missed_posts() {
    global $wpdb;
    $now = gmdate("Y-m-d H:i:00");
    $sql = "SELECT ID FROM {$wpdb->posts} WHERE post_status = %s AND post_date_gmt <= %s";
    $prepared_sql = $wpdb->prepare($sql, 'future', $now);
    $resulto = $wpdb->get_results($prepared_sql);
    if ($resulto) {
        foreach ($resulto as $thisarr) {
            wp_publish_post($thisarr->ID);
        }
    }
    update_option('improveseo_scheduled_last_execute_time', time());
}
