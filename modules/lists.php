<?php

use ImproveSEO\View;
use ImproveSEO\Validator;
use ImproveSEO\FlashMessage;
use ImproveSEO\Models\Lists;
use ImproveSEO\Models\Shortcode;

function improveseo_lists() {
	global $wpdb;
	$action = isset($_GET['action']) ? $_GET['action'] : 'index';
	$limit = isset($_GET['limit']) ? $_GET['limit'] : 20;
	$offset = isset($_GET['paged']) ? $_GET['paged'] * $limit - $limit : 0;
	$model = new Lists();

	if ($action == 'index'):

		// Filters
		$orderBy = isset($_GET['orderBy']) ? $_GET['orderBy'] : 'name';
		$order = isset($_GET['order']) ? $_GET['order'] : 'ASC';
		$s = isset($_GET['s']) ? $_GET['s'] : '';

		$where = array();
		$params = array();

		$sql = 'SELECT * FROM '. $model->getTable();
		/* if (sizeof($where)) {
			$sql .= ' WHERE '. implode(' AND ', $where);
		}

		if (sizeof($where)) {
			$sqlTotal .= ' WHERE '. implode(' AND ', $where);
		} */
		$sqlTotal = 'SELECT COUNT(id) AS total FROM '. $model->getTable();
		if($s != ""){
			$sql .= ' WHERE name like "%'.$s.'%"';
			$sqlTotal .= ' WHERE name like "%'.$s.'%"';
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

	elseif ($action == 'create'):

		View::render('lists.create');

	elseif ($action == 'do_create'):

		if (!Validator::validate($_POST, array(
			'name' => 'required|unique:'. $model->getTable(),
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
				Congratulations! To use your newly created list, call <strong>@list:'. $model->setNameAttribute($_POST['name']) .'</strong>.
			</p>
			<p>
				To activate your list, make sure to use it in the title of the post/page (you can use it everywhere else too, but it must be included in the title).
			</p>
		');
		wp_redirect(admin_url('admin.php?page=improveseo_lists'));
		exit;

	elseif ($action == 'edit'):

		$id = $_GET['id'];
		$list = $model->find($id);

		View::render('lists.edit', compact('list'));

	elseif ($action == 'do_edit'):

		$id = $_GET['id'];
		$list = $model->find($id);

		if (!Validator::validate($_POST, array(
			'name' => 'required|unique:'. $model->getTable() .',name,'. $id,
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

	elseif ($action == 'delete'):

		$id = $_GET['id'];
		$model->delete($id);

		FlashMessage::success('List has been deleted.');
		wp_redirect(admin_url('admin.php?page=improveseo_lists'));
		exit;

	endif;
}
