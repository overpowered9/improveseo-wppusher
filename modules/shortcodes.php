<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


use ImproveSEO\View;
use ImproveSEO\Validator;
use ImproveSEO\FlashMessage;
use ImproveSEO\Models\Shortcode;

add_action('init', 'improveseo_init_shortcodes');

function improveseo_init_shortcodes() {
	$model = new Shortcode();

	$shortcodes = $model->all();

	foreach ($shortcodes as $shortcode) {
		add_shortcode($shortcode->shortcode, 'improveseo_handle_shortcode');
	}
}

function improveseo_handle_shortcode($attributes, $content = null, $called = null) {
	$model = new Shortcode();

	$shortcode = $model->getByShortcode($called);
	if (isJSON($shortcode->content)) {
		$mediaObjects = json_decode($shortcode->content);
		$media = $mediaObjects[isset($attributes['key']) ? $attributes['key'] : 0];//array_rand($mediaObjects)];

		// Images
		if (isset($media->id)) {
			return '<img src="'. $media->url .'" alt="'. $media->tags .'">';
		}
		// Videos
		if (isset($media->videoId)) {
			return '<iframe type="text/html" width="640" height="390" src="http://www.youtube.com/embed/'. $media->videoId .'" frameborder="0"></iframe>';
		}
	} else return $shortcode->content;
}

function improveseo_shortcodes() {
	global $wpdb;

	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only list screen, no state change.
	$action = isset($_GET['action']) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : 'index';
	// Bound with %d in the LIMIT clause below; the floor of 1 also keeps "?limit=0" away
	// from the division that derives the page count.
	$limit = isset($_GET['limit']) ? max( 1, absint( wp_unslash( $_GET['limit'] ) ) ) : 20;
	$offset = isset($_GET['offset']) ? max( 0, absint( wp_unslash( $_GET['offset'] ) ) ) : 0;
	// phpcs:enable WordPress.Security.NonceVerification.Recommended
	$model = new Shortcode();

	if ($action == 'index'):

		// Filters
		//
		// orderBy and order are SQL IDENTIFIERS spliced into the ORDER BY clause below, which
		// $wpdb->prepare() cannot bind. They were previously taken raw from $_GET and were
		// therefore injectable. Both are now matched against a fixed allowlist, falling back
		// to the default on anything unrecognised.
		$allowed_order_by = array( 'shortcode', 'type', 'created_at', 'id' );
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only list screen, no state change.
		$type = isset($_GET['type']) ? sanitize_key( wp_unslash( $_GET['type'] ) ) : 'all';
		$orderBy = ( isset($_GET['orderBy']) && in_array( wp_unslash( $_GET['orderBy'] ), $allowed_order_by, true ) ) ? wp_unslash( $_GET['orderBy'] ) : 'shortcode';
		$order = ( isset($_GET['order']) && in_array( strtoupper( wp_unslash( $_GET['order'] ) ), array( 'ASC', 'DESC' ), true ) ) ? strtoupper( wp_unslash( $_GET['order'] ) ) : 'ASC';
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		$where = array();
		$params = array();


		if ($type != 'all') {
			$params[] = $type;
			$where[] = '`type` = %s';
		}

		$sql = 'SELECT * FROM '. $model->getTable();
		if (sizeof($where)) {
			$sql .= ' WHERE '. implode(' AND ', $where);
		}

		$sqlTotal = 'SELECT COUNT(id) AS total FROM '. $model->getTable();
		if (sizeof($where)) {
			$sqlTotal .= ' WHERE '. implode(' AND ', $where);
		}

		// Only prepare when there is something to bind. With type=all there is no WHERE
		// clause and so no placeholders, and $wpdb->prepare() on a placeholder-free query
		// is a _doing_it_wrong() notice in current WordPress — which is the default view.
		if ( $params ) {
			$sqlTotal = $wpdb->prepare($sqlTotal, $params); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- built above from literals plus Shortcode::getTable(); the only bound value is the type filter.
		}

		// $orderBy and $order are allowlisted above — prepare() cannot bind an identifier.
		$sql .= " ORDER BY $orderBy $order";
		$sql .= " LIMIT %d, %d";

		$params[] = $offset;
		$params[] = $limit;

		// Always has the two LIMIT placeholders, so this one never needs the guard above.
		$sql = $wpdb->prepare($sql, $params); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- built above from literals plus Shortcode::getTable(); every user value is bound.

		// Data
		$shortcodes = $wpdb->get_results($sql); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter -- the query in this variable is prepared where it is built, above
		$total_row = $wpdb->get_row($sqlTotal); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter -- the query in this variable is prepared where it is built, above
		$total = $total_row->total;

		$all = $model->count();
		$static = $model->countStatic();
		$dynamic = $model->countDynamic();

		View::render('shortcodes.index', compact('shortcodes', 'total', 'all', 'static', 'dynamic', 'type', 'order', 'orderBy'));

	elseif ($action == 'create'):

		View::render('shortcodes.create');

	elseif ($action == 'do_create'):

		if (!Validator::validate($_POST, array(
			'shortcode' => 'required|unique:'. $model->getTable().',shortcode',
			'type' => 'required',
			'content' => 'required'
		))) {
			wp_redirect(admin_url('admin.php?page=improveseo_shortcodes&action=create'));
			exit;
		}

		$id = $model->create($_POST);

		FlashMessage::success('Shortcode created.');
		wp_redirect(admin_url('admin.php?page=improveseo_shortcodes'));
		exit;

	elseif ($action == 'edit'):

		$id = $_GET['id'];
		$shortcode = $model->find($id);

		View::render('shortcodes.edit', compact('shortcode'));

	elseif ($action == 'do_edit'):

		$id = $_GET['id'];
		$shortcode = $model->find($id);

		if (!Validator::validate($_POST, array(
			'shortcode' => 'required|unique:'. $model->getTable() .',shortcode,'. $id,
			'content' => 'if_not:dynamic,'. $shortcode->type
		))) {
			wp_redirect(admin_url("admin.php?page=improveseo_shortcodes&action=edit&id={$id}"));
			exit;
		}

		$model->update($_POST, $id);

		FlashMessage::success('Shortcode updated.');
		wp_redirect(admin_url("admin.php?page=improveseo_shortcodes&action=edit&id={$id}"));
		exit;

	elseif ($action == 'delete'):

		$id = $_GET['id'];
		$model->delete($id);

		FlashMessage::success('Shortcode deleted.');
		wp_redirect(admin_url('admin.php?page=improveseo_shortcodes'));
		exit;

	endif;
}
