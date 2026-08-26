<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


use ImproveSEO\View;
use ImproveSEO\FlashMessage;

function improveseo_noindex() {
	global $wpdb;

	$action = isset($_GET['action']) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : 'index'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only list screen, no state change.

	// absint() on both, and a floor of 1 on the page size.
	//
	// $limit was previously taken raw from $_GET and interpolated straight into the
	// LIMIT clause below, so "?limit=50 UNION SELECT ..." was injectable. $offset only
	// escaped that because the arithmetic coerced it to a number. Casting here fixes the
	// injection at the source; the query below is also prepared, so neither alone is
	// load-bearing.
	//
	// The floor of 1 additionally prevents "?limit=0" from reaching the division on the
	// $pages line below, which was a division by zero.
	$limit = isset($_GET['limit']) ? max( 1, absint( wp_unslash( $_GET['limit'] ) ) ) : 50; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only list screen, no state change.
	$offset = isset($_GET['paged']) ? max( 0, absint( wp_unslash( $_GET['paged'] ) ) * $limit - $limit ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only list screen, no state change.

	if ($action == 'index'):
		$results = $wpdb->get_row("SELECT COUNT(*) AS total FROM {$wpdb->prefix}termmeta WHERE meta_key = 'improveseo_noindex_tag'"); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter -- fixed query against core tables; the only variable is the table prefix

		// LIMIT is bound with %d rather than interpolated. The previous comment here read
		// "the only variable is the table prefix", which was not true — $offset and $limit
		// were interpolated from $_GET as well. Only the prefix is interpolated now.
		$tags = $wpdb->get_results( $wpdb->prepare( "SELECT t.* FROM {$wpdb->prefix}termmeta AS m INNER JOIN {$wpdb->prefix}terms AS t ON t.term_id = m.term_id WHERE m.meta_key = 'improveseo_noindex_tag' LIMIT %d, %d", $offset, $limit ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter -- core tables; only $wpdb->prefix is interpolated, the user-supplied LIMIT values are bound.

		$pages = ceil($results->total / $limit);
		$page = floor($offset / $limit) + 1;

		View::render('noindex.index', compact('tags', 'results', 'page', 'pages'));
	elseif ($action == 'remove'):
		$id = (int) $_GET['id'];

		$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}termmeta WHERE term_id = %s AND meta_key = 'improveseo_noindex_tag'", array($id))); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter -- fixed query against core tables; the only variable is the table prefix

		FlashMessage::success('Noindex tag removed.');
		wp_redirect(admin_url('admin.php?page=improveseo_noindex'));
		exit;

	endif;
}
