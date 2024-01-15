<?php

use ImproveSEO\View;
use ImproveSEO\Validator;
use ImproveSEO\FlashMessage;
use ImproveSEO\Models\Shortcode;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly  


add_action('init', 'improveseo_init_shortcodes');

function improveseo_init_shortcodes()
{
	$model = new Shortcode();

	$shortcodes = $model->all();

	foreach ($shortcodes as $shortcode) {
		add_shortcode($shortcode->shortcode, 'improveseo_handle_shortcode');
	}
}

function improveseo_handle_shortcode($attributes, $content = null, $called = null)
{
	$model = new Shortcode();

	$shortcode = $model->getByShortcode($called);
	if (isJSON($shortcode->content)) {
		$mediaObjects = json_decode($shortcode->content);
		$media = $mediaObjects[isset($attributes['key']) ? $attributes['key'] : 0]; //array_rand($mediaObjects)];

		// Images
		if (isset($media->id)) {
			return '<img src="' . $media->url . '" alt="' . $media->tags . '">';
		}
		// Videos
		if (isset($media->videoId)) {
			return '<iframe type="text/html" width="640" height="390" src="http://www.youtube.com/embed/' . $media->videoId . '" frameborder="0"></iframe>';
		}
	} else return $shortcode->content;
}

function improveseo_shortcodes()
{
	global $wpdb;

	$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'index';
	$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
	$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
	$model = new Shortcode();

	if ($action == 'index') :

		// Filters
		$type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : 'all';
		$orderBy = isset($_GET['orderBy']) ? sanitize_text_field($_GET['orderBy']) : 'shortcode';
		$order = isset($_GET['order']) && in_array(strtoupper($_GET['order']), array('ASC', 'DESC')) ? strtoupper($_GET['order']) : 'ASC';

		$where = array();
		$params = array();


		if ($type != 'all') {
			$params[] = $type;
			$where[] = '`type` = %s';
		}

		$sql = 'SELECT * FROM ' . $model->getTable();
		if (sizeof($where)) {
			$sql .= ' WHERE ' . implode(' AND ', $where);
		}

		$sqlTotal = 'SELECT COUNT(id) AS total FROM ' . $model->getTable();
		if (sizeof($where)) {
			$sqlTotal .= ' WHERE ' . implode(' AND ', $where);
		}

		$sqlTotal = $wpdb->prepare($sqlTotal, $params);

		$sql .= " ORDER BY $orderBy $order";
		$sql .= " LIMIT %d, %d";

		$params[] = $offset;
		$params[] = $limit;

		$sql = $wpdb->prepare($sql, $params);

		// Data
		$shortcodes = $wpdb->get_results($sql);
		$total_row = $wpdb->get_row($sqlTotal);
		$total = $total_row->total;

		$all = $model->count();
		$static = $model->countStatic();
		$dynamic = $model->countDynamic();

		View::render('shortcodes.index', compact('shortcodes', 'total', 'all', 'static', 'dynamic', 'type', 'order', 'orderBy'));

	elseif ($action == 'create') :

		View::render('shortcodes.create');

	elseif ($action == 'do_create') :

		$shortcode = isset($_POST['shortcode']) ? $_POST['shortcode'] : '';
		$type = isset($_POST['type']) ? $_POST['type'] : '';
		$content = isset($_POST['content']) ? $_POST['content'] : '';

		if (!Validator::validate(
			compact('shortcode', 'type', 'content'),
			array(
				'shortcode' => 'required|unique:' . $model->getTable() . ',shortcode',
				'type' => 'required',
				'content' => 'required'
			)
		)) {
			wp_redirect(admin_url('admin.php?page=improveseo_shortcodes&action=create'));
			exit;
		}

		$id = $model->create($_POST);

		FlashMessage::success('Shortcode created.');
		wp_redirect(admin_url('admin.php?page=improveseo_shortcodes'));
		exit;

	elseif ($action == 'edit') :

		$id = $_GET['id'];
		$shortcode = $model->find($id);

		View::render('shortcodes.edit', compact('shortcode'));

	elseif ($action == 'do_edit') :

		$id = $_GET['id'];
		$shortcode = $model->find($id);

		if (!Validator::validate(compact('shortcode','content'), array(
			'shortcode' => 'required|unique:' . $model->getTable() . ',shortcode,' . $id,
			'content' => 'if_not:dynamic,' . $shortcode->type
		))) {
			wp_redirect(admin_url("admin.php?page=improveseo_shortcodes&action=edit&id={$id}"));
			exit;
		}

		$model->update($_POST, $id);

		FlashMessage::success('Shortcode updated.');
		wp_redirect(admin_url("admin.php?page=improveseo_shortcodes&action=edit&id={$id}"));
		exit;

	elseif ($action == 'delete') :

		$id = $_GET['id'];
		$model->delete($id);

		FlashMessage::success('Shortcode deleted.');
		wp_redirect(admin_url('admin.php?page=improveseo_shortcodes'));
		exit;

	endif;
}
