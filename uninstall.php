<?php
/**
 * Runs when the plugin is deleted from WordPress (not on deactivation).
 *
 * Removes what Content v3 and its keyword data added: their options, transients, post meta, the
 * columns they added to the bulk task and keyword list tables, and their scheduled cleanup.
 * Nothing the plugin stored before Content v3 is touched: the plugin has never removed its own
 * data on uninstall, and this file does not start doing that for older data.
 *
 * @package ImproveSEO
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// Options: the Content v3 switches, its Business details, the bulk queue state, caches and indexes.
$improveseo_v3_options = array(
	'improveseo_content_v3',
	'improveseo_content_v3_last_good',
	'improveseo_v3_keyword_data',
	'improveseo_regulated_notice_seen',
	'improveseo_bulk_pause',
	'improveseo_bulk_outages',
	'improveseo_bulk_v3_cols',
	'improveseo_lists_metrics_col',
	'improveseo_v3_wizard_files',
	'improveseo_business_name',
	'improveseo_business_description',
	'improveseo_business_ideal_client',
	'improveseo_business_niche',
	'improveseo_business_profile',
	'improveseo_business_proof_level',
	'improveseo_business_pov',
	'improveseo_business_reach',
	'improveseo_business_stand_out',
	'improveseo_business_towns',
);
foreach ( $improveseo_v3_options as $improveseo_option ) {
	delete_option( $improveseo_option );
}
// Each bulk project's Target town (improveseo_bulk_v3_town_<project id>).
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- one-time uninstall cleanup.
$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $wpdb->esc_like( 'improveseo_bulk_v3_town_' ) . '%' ) );

// Transients: the server status and niche list caches, the Regulated notice, and the image
// files each wizard session created (iseo_v3_files_<md5>).
foreach ( array( 'improveseo_content_v3_status', 'improveseo_content_v3_niches', 'improveseo_regulated_notice' ) as $improveseo_transient ) {
	delete_transient( $improveseo_transient );
}
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- one-time uninstall cleanup.
$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
		$wpdb->esc_like( '_transient_iseo_v3_files_' ) . '%',
		$wpdb->esc_like( '_transient_timeout_iseo_v3_files_' ) . '%'
	)
);

// Post meta written on Content v3 posts and on posts generated with keyword data.
foreach ( array( '_iseo_proof_level', '_iseo_v3_generation_id', '_iseo_seed_keyword', '_iseo_keyword_data' ) as $improveseo_meta_key ) {
	delete_post_meta_by_key( $improveseo_meta_key );
}

// Columns Content v3 added to the bulk task table, and the keyword list metrics column.
$improveseo_drop_columns = array(
	$wpdb->prefix . 'improveseo_bulktasksdetails' => array( 'attempts', 'last_error', 'v3_review', 'lease_token', 'lease_until', 'next_attempt_at', 'v3_request_id' ),
	$wpdb->prefix . 'improveseo_lists'            => array( 'metrics' ),
);
foreach ( $improveseo_drop_columns as $improveseo_table => $improveseo_columns ) {
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- schema check during uninstall.
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $improveseo_table ) ) !== $improveseo_table ) {
		continue;
	}
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- the table name is an identifier and cannot be bound.
	$improveseo_existing = $wpdb->get_col( "SHOW COLUMNS FROM `$improveseo_table`" );
	$improveseo_drops    = array();
	foreach ( $improveseo_columns as $improveseo_column ) {
		if ( is_array( $improveseo_existing ) && in_array( $improveseo_column, $improveseo_existing, true ) ) {
			$improveseo_drops[] = 'DROP COLUMN `' . $improveseo_column . '`';
		}
	}
	if ( $improveseo_drops ) {
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange -- fixed column names from the list above; the table name is an identifier.
		$wpdb->query( "ALTER TABLE `$improveseo_table` " . implode( ', ', $improveseo_drops ) );
	}
}

// The daily cleanup of abandoned wizard images.
wp_clear_scheduled_hook( 'improveseo_v3_files_cleanup' );
