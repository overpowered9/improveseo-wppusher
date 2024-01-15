<?php

use ImproveSEO\View;
use ImproveSEO\Validator;
use ImproveSEO\FlashMessage;
use ImproveSEO\Models\Lists;
use ImproveSEO\Models\Shortcode;

function improveseo_lists()
{
	global $wpdb;
	$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'index';
	$limit = isset($_GET['limit']) ? $_GET['limit'] : 20;
	$offset = isset($_GET['paged']) ? $_GET['paged'] * $limit - $limit : 0;
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

	//Upload CSV File
	if (isset($_POST['submit'])) {


		if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'import_project_nonce')) {
			wp_redirect(admin_url('admin.php?page=improveseo_lists'));
			exit();
		}


		if (!current_user_can('upload_files')) {
			FlashMessage::success('Current user can\'t upload file');
			wp_redirect(admin_url('admin.php?page=improveseo_lists'));
			exit();
		}

		if (in_array($_FILES['upload_csv']['type'], $fileMimes) === false) {
			FlashMessage::success('Please Upload a Valid CSV file');
			wp_redirect(admin_url('admin.php?page=improveseo_lists'));
			exit();
		}


		//Import uploaded file to Database
		$file = fopen($_FILES['upload_csv']['tmp_name'], "r");

		$counter = 0;
		while (!feof($file)) {

			$file_content = fgetcsv($file);

			if ($counter != 0) {

				$wpdb->insert($wpdb->prefix . "improveseo_lists", array(
					'id' => $file_content[0],
					'name' => $file_content[1],
					'list' => $file_content[2],
					'size' => $file_content[3],
					'created_at' => $file_content[4],
				));
			}

			$counter++;
		}

		$counter = $counter - 2;

		fclose($file);

		FlashMessage::success($counter . ' List Imported Successfully.');
	}


	if ($action == 'index') :

		// Filters
		$orderBy = isset($_GET['orderBy']) ? $_GET['orderBy'] : 'name';
		$order = isset($_GET['order']) ? $_GET['order'] : 'ASC';
		$s = isset($_GET['s']) ? $_GET['s'] : '';

		$where = array();
		$params = array();

		$sql = 'SELECT * FROM ' . $model->getTable();
		/* if (sizeof($where)) {
			$sql .= ' WHERE '. implode(' AND ', $where);
		}

		if (sizeof($where)) {
			$sqlTotal .= ' WHERE '. implode(' AND ', $where);
		} */
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
		$list = isset($_POST['list']) ? sanitize_text_field($_POST['list']) : '';

		// Validate the sanitized fields
		if (!Validator::validate(array("name" => $name, "list" => $list), array(
			'name' => 'required|unique:' . $model->getTable(),
			'list' => 'required'
		))) {
			wp_redirect(admin_url('admin.php?page=improveseo_lists&action=create'));
			exit;
		}
		$_POST['list'] = trim(stripslashes($_POST['list']));
		$_POST['size'] = sizeof(explode("\n", $_POST['list']));
		$id = $model->create($_POST);

		FlashMessage::success('
			<p>
				Congratulations! To use your newly created list, call <strong>@list:' . $model->setNameAttribute($_POST['name']) . '</strong>.
			</p>
			<p>
				To activate your list, make sure to use it in the title of the post/page (you can use it everywhere else too, but it must be included in the title).
			</p>
		');
		wp_redirect(admin_url('admin.php?page=improveseo_lists'));
		exit;

	elseif ($action == 'edit') :

		$id = $_GET['id'];
		$list = $model->find($id);

		View::render('lists.edit', compact('list'));

	elseif ($action == 'do_edit') :

		$id = $_GET['id'];
		$list = $model->find($id);

		$fields = array("name" => isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '', "list" => isset($_POST['list']) ? sanitize_text_field($_POST['list']) : '');


		if (!Validator::validate($fields, array(
			'name' => 'required|unique:' . $model->getTable() . ',name,' . $id,
			'list' => 'required'
		))) {
			wp_redirect(admin_url("admin.php?page=improveseo_lists&action=edit&id={$id}"));
			exit;
		}

		$_POST['list'] = trim(stripslashes($_POST['list']));
		$_POST['size'] = sizeof(explode("\n", $_POST['list']));
		$model->update($_POST, $id);

		FlashMessage::success('List has been updated.');
		wp_redirect(admin_url("admin.php?page=improveseo_lists&action=edit&id={$id}"));
		exit;

	elseif ($action == 'delete') :

		$id = $_GET['id'];
		$model->delete($id);

		FlashMessage::success('List has been deleted.');
		wp_redirect(admin_url('admin.php?page=improveseo_lists'));
		exit;


	elseif ($action == 'export_all_list') :

		$data = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}improveseo_lists"));

		if (empty($data)) {
			wp_redirect(admin_url('admin.php?page=export_all_list'));
		}

		wt_load_templates('import-export.php');
		$exportRecords = new improveseo_import_export();
		$exportRecords->export($data, 'all-lists');

		exit;

	endif;
}
