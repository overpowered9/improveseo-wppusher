<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

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
    $now    =    gmdate("Y-m-d H:i:00");
    $sql = $wpdb->prepare("Select ID from {$wpdb->posts} where post_status='future' and post_date_gmt<=%s", $now); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter -- the posts table name is an identifier and cannot be bound
    $resulto = $wpdb->get_results($sql); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter -- prepared on the line above
    if ($resulto) {
        foreach ($resulto as $thisarr) {
            wp_publish_post($thisarr->ID);
        }
    }
    update_option('improveseo_scheduled_last_execute_time', time());
}
