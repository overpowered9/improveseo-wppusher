<?php
if(get_option("improveseo_scheduled_last_execute_time") == false){
    update_option('improveseo_scheduled_last_execute_time', time());
}

if(get_option("improveseo_scheduled_execute_time") == false){
    update_option("improveseo_scheduled_execute_time", 20);
}
if(time() >= get_option("improveseo_scheduled_last_execute_time") + 300){
	add_action('wp_head', 'improveseo_publish_missed_posts');
}
function improveseo_publish_missed_posts() {
    global $wpdb;
    $now	=	gmdate("Y-m-d H:i:00");
    $sql = "Select ID from {$wpdb->posts} where post_status='future' and post_date_gmt<='$now'";
    $resulto = $wpdb->get_results($sql);
    if($resulto) {
        foreach( $resulto as $thisarr ) {
            wp_publish_post($thisarr->ID);
        }
    }
    update_option('improveseo_scheduled_last_execute_time', time());
}