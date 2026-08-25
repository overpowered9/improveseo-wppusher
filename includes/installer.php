<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}






global $improveseo_db_version;


$improveseo_db_version = '0.9';





function improveseo_update_db_check() {


    global $improveseo_db_version;


    if ( get_site_option( 'improveseo_db_version' ) != $improveseo_db_version ) {


        improveseo_install();


    }


}


add_action('plugins_loaded', 'improveseo_update_db_check');





function improveseo_uninstall() {


	wp_clear_scheduled_hook('improveseo_parse_tasks_hook');





	delete_option('improveseo_scheduled_last_execute_time');


	delete_option('improveseo_scheduled_execute_time');




}








// 2.0.12. They rewrote wp-config.php with preg_replace to inject
// WP_MEMORY_LIMIT / WP_MAX_MEMORY_LIMIT at 5000M. Both call sites were already
// commented out, but the code shipped.
//
// Modifying wp-config.php is not permitted for a WordPress.org plugin, 5000M is
// not a sensible limit, and neither function checked a return value: if
// file_get_contents() ever failed it returned false, preg_replace() turned that
// into '', and file_put_contents() would then have written an EMPTY wp-config.php
// and taken the whole site down. A site needing more memory should set it in
// wp-config.php or php.ini directly.





function improveseo_install() {


	global $wpdb;


	global $improveseo_db_version;


	global $wp_rewrite;





	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');





	// Config







	// Roles


	add_role('improveseo_user', 'Improve SEO User');





	// Scheduler


	//wp_schedule_event(time(), 'every_minute', 'improveseo_parse_tasks_hook');


	


	// Tasks table


	$table_name = $wpdb->prefix . 'improveseo_tasks';


	


	$charset_collate = $wpdb->get_charset_collate();





	$sql = "CREATE TABLE $table_name (


		`id` mediumint(9) NOT NULL AUTO_INCREMENT,


		`name` VARCHAR(255) NOT NULL,


		`content` MEDIUMTEXT NOT NULL,


		`options` LONGTEXT NOT NULL,


		`iteration` INT UNSIGNED NOT NULL,


		`spintax_iterations` INT UNSIGNED NOT NULL,


		`max_iterations` INT UNSIGNED NOT NULL,


		`state` VARCHAR(30) NOT NULL DEFAULT 'Draft',


		`created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',


		`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,


		`finished_at` TIMESTAMP NOT NULL,


		`deleted_at` TIMESTAMP NOT NULL,


		UNIQUE KEY id (id)


	) $charset_collate;";





	dbDelta($sql);


	





	// Updated by Shahid for new Column


	$sql = "ALTER TABLE ".$table_name." ADD `cats` VARCHAR(255) NOT NULL";


	$wpdb->query($sql);





	// Shortcodes table


	$table_name = $wpdb->prefix . 'improveseo_shortcodes';


	


	$charset_collate = $wpdb->get_charset_collate();





	$sql = "CREATE TABLE $table_name (


		id mediumint(9) NOT NULL AUTO_INCREMENT,


		shortcode VARCHAR(255) NOT NULL,


		type VARCHAR(40) NOT NULL,


		content LONGTEXT NOT NULL,


		created_at TIMESTAMP NOT NULL,


		updated_at TIMESTAMP NOT NULL,


		UNIQUE KEY id (id)


	) $charset_collate;";





	dbDelta($sql);





	// Tags noindex





	// Lists table


	$table_name = $wpdb->prefix .'improveseo_lists';





	$charset_collate = $wpdb->get_charset_collate();





	$sql = "CREATE TABLE `$table_name` (


		`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,


		`name` VARCHAR(50) NOT NULL,


		`list` LONGTEXT NOT NULL,


		`size` MEDIUMINT(8) UNSIGNED NOT NULL,


		`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,


		PRIMARY KEY (`id`)


	) $charset_collate;";





	dbDelta($sql);





	// Geo codes


	include_once 'geo/installer.php';

	// Explicitly ensure the bulk task meta columns exist — dbDelta above is
	// unreliable at adding columns to an already-existing table.
	if ( function_exists( 'improveseo_ensure_bulk_meta_columns' ) ) {
		delete_option( 'improveseo_bulk_meta_cols' ); // force a fresh check
		improveseo_ensure_bulk_meta_columns();
	}

	// published_on must be wide enough to hold a full datetime (varchar(12) → 19).
	if ( function_exists( 'improveseo_ensure_bulk_published_on_column' ) ) {
		delete_option( 'improveseo_bulk_published_on_col' ); // force a fresh check
		improveseo_ensure_bulk_published_on_column();
	}


	


	// Update improveseo DB Schema


	$installed_ver = get_option("improveseo_db_version");


	if ($installed_ver && $installed_ver != $improveseo_db_version) {


		update_option( "improveseo_db_version", $improveseo_db_version );


	}


	else add_option('improveseo_db_version', $improveseo_db_version);





	// Rebuild URL rules


	$wp_rewrite->flush_rules();





	update_option('improveseo_scheduled_last_execute_time', time());


	update_option('improveseo_scheduled_execute_time', 20);


	// Onboarding: mark as incomplete on first install (add_option is a no-op if key already exists)
	add_option('improveseo_onboarding_complete', '0');

	// Set a short-lived transient so the activation redirect fires exactly once
	set_transient('improveseo_activation_redirect', true, 30);


}





function improveseo_install_data() {


	global $wpdb;


	


	


}