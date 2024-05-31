<?php

use ImproveSEO\View;
use ImproveSEO\Validator;
use ImproveSEO\FlashMessage;
use ImproveSEO\Models\Lists;
use ImproveSEO\Models\Shortcode;

if (!defined('ABSPATH')) exit;

function improveseo_lists()
{
	global $wpdb;
	$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'index';
	$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
	$offset = isset($_GET['paged']) ? intval($_GET['paged']) * $limit - $limit : 0;

	$model = new Lists();


	// Allowed mime types
	$fileMimes = array(
		'application/vnd.ms-excel',
		'application/x-csv',
		'text/x-csv',
		'text/csv',
		'application/csv',
		'application/excel',
		'application/vnd.msexcel'
	);

	if ($action == 'index') :

		// Filters
		$orderBy = isset($_GET['orderBy']) ? filter_var($_GET['orderBy'], FILTER_SANITIZE_STRING) : 'created_at';
		$order = isset($_GET['order']) ? filter_var($_GET['order'], FILTER_SANITIZE_STRING) : 'desc';
		$s = isset($_GET['s']) ? filter_var($_GET['s'], FILTER_SANITIZE_STRING) : '';


		$where = array();
		$params = array();

		$sql = 'SELECT * FROM ' . $model->getTable();

		$sqlTotal = 'SELECT COUNT(id) AS total FROM ' . $model->getTable();
		if ($s != "") {
			$sql .= " WHERE name like '%%%s%%'";
			$sqlTotal .= " WHERE name like '%%%s%%'";
			$params[] = $s;
		}

		$sqlTotal = $wpdb->prepare($sqlTotal, $params);

		$sql .= " ORDER BY $orderBy $order";
		$sql .= " LIMIT %d, %d";


		$params[] = $offset;
		$params[] = $limit;

		$sql = $wpdb->prepare($sql, $params);
		// Data
		$lists = $wpdb->get_results($sql);
		$total_row = $wpdb->get_row($sqlTotal);
		$total = $total_row->total;

		$pages = ceil($total / $limit);
		$page = floor($offset / $limit) + 1;

		$all = $model->count();

		View::render('lists.index', compact('lists', 'total', 'all', 'order', 'orderBy', 'pages', 'page', 's'));

	elseif ($action == 'create') :

		View::render('lists.create');

	elseif ($action == 'do_create') :

		$name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
		
		$list = isset($_POST['list']) ? trim(sanitize_text_field($_POST['list'])) : ''; // Check if $_POST['list'] is set




		// Validate the sanitized fields
		if (!Validator::validate(array("name" => $name, "list" => $list), array(
			'name' => 'required|unique:' . $model->getTable(),
			'list' => 'required'
		))) {
			wp_redirect(admin_url('admin.php?page=improveseo_lists&action=create'));
			exit;
		}
		
		$list = isset($_POST['list']) ? trim(sanitize_text_field($_POST['list'])) : ''; // Check if $_POST['list'] is set

		
		
		$lines = explode("\n", $list); // Split the input into lines
		$size = count($lines); // Calculate the number of lines

		$id = $model->create(array("name" => $name, "list" => $list));
		// Assuming $_POST['name'] contains the name attribute value
		$nameAttribute = $model->setNameAttribute(sanitize_text_field($_POST['name']));
		$sanitizedAttribute = sanitize_text_field($nameAttribute); // Sanitize the attribute

		// Validate the sanitized attribute if needed
		// Example: if (empty($sanitizedAttribute)) { handle validation error }

		// Escape the output
		$escapedAttribute = esc_html($sanitizedAttribute);

		FlashMessage::success('
			<p>
				Congratulations! To use your newly created list, call <strong>@list:' . $escapedAttribute . '</strong>.
			</p>
			<p>
				To activate your list, make sure to use it in the title of the post/page (you can use it everywhere else too, but it must be included in the title).
			</p>
		');
		wp_redirect(admin_url('admin.php?page=improveseo_lists'));
		exit;

	elseif ($action == 'edit') :
		$id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;

		$list = $model->find($id);

		View::render('lists.edit', compact('list'));

	elseif ($action == 'do_edit') :

		$id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;

		$list = $model->find($id);

		$fields = array("name" => isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '', "list" => isset(($_POST['list'])) ? sanitize_text_field($_POST['list']) : '');


		if (!Validator::validate($fields, array(
			'name' => 'required|unique:' . $model->getTable() . ',name,' . $id,
			'list' => 'required'
		))) {
			wp_redirect(admin_url("admin.php?page=improveseo_lists&action=edit&id={$id}"));
			exit;
		}

		$list = isset($_POST['list']) ? trim(sanitize_text_field($_POST['list'])) : ''; // Check if $_POST['list'] is set/PelConvert.php


		// $list = array_map(function ($item) {
		// 	$sanitized_item = sanitize_text_field($item); // Sanitize the input
		// 	if (empty($sanitized_item)) {
		// 		// Handle the error - the input is invalid (e.g., empty after sanitization)
		// 		wp_die('Invalid list item.');
		// 	}
		// 	return esc_html($sanitized_item); // Escape the input for safe output
		// }, $list_no_sanitized);


		
		$lines = explode("\n", $list); // Split the input into lines
		$size = count($lines); // Calculate the number of lines


		$model->update($fields, $id);

		FlashMessage::success('List has been updated.');
		wp_redirect(admin_url("admin.php?page=improveseo_lists&action=edit&id={$id}"));
		exit;

	elseif ($action == 'delete') :

		$id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;

		$model->delete($id);

		FlashMessage::success('List has been deleted.');
		wp_redirect(admin_url('admin.php?page=improveseo_lists'));
		exit;


	elseif ($action == 'export_all_list') :

		$data = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}improveseo_lists"));

		if (empty($data)) {
			wp_redirect(admin_url('admin.php?page=export_all_list'));
		}

		improveseo_wt_load_templates('import-export.php');
		$exportRecords = new improveseo_import_export();
		$exportRecords->export($data, 'all-lists');

		exit;

	endif;
}
