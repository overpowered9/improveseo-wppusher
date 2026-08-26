<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


use ImproveSEO\View;
use ImproveSEO\Validator;
use ImproveSEO\FlashMessage;
use ImproveSEO\Models\Lists;
use ImproveSEO\Models\Shortcode;

function improveseo_lists() {
	global $wpdb;
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only list screen, no state change.
	$action = isset($_GET['action']) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : 'index';
	// Bound with %d in the LIMIT clause below, so casting here is belt-and-braces for the
	// query — but the floor of 1 is load-bearing: $limit divides $total further down, and
	// "?limit=0" reached that division.
	$limit = isset($_GET['limit']) ? max( 1, absint( wp_unslash( $_GET['limit'] ) ) ) : 20;
	$offset = isset($_GET['paged']) ? max( 0, absint( wp_unslash( $_GET['paged'] ) ) * $limit - $limit ) : 0;
	// phpcs:enable WordPress.Security.NonceVerification.Recommended
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
		$file = fopen($_FILES['upload_csv']['tmp_name'], "r"); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- streaming fgetcsv() over an uploaded temp file; WP_Filesystem would have to read the whole upload into memory first
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
		$counter = $counter-2;  
		fclose($file); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- streaming fgetcsv() over an uploaded temp file; WP_Filesystem would have to read the whole upload into memory first
		FlashMessage::success($counter . ' List Imported Successfully.');
	}
	
	if ($action == 'index'):
		// Filters
		//
		// orderBy and order are SQL IDENTIFIERS spliced into the ORDER BY clause below, and
		// $wpdb->prepare() cannot bind an identifier. They were previously taken raw from
		// $_GET, so "?orderBy=name,(SELECT ...)" was injectable. Both are now matched against
		// a fixed allowlist and fall back to the default on anything unrecognised — the same
		// pattern modules/projects.php already uses.
		$allowed_order_by = array( 'name', 'created_at', 'id' );
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only list screen, no state change.
		$orderBy = ( isset($_GET['orderBy']) && in_array( wp_unslash( $_GET['orderBy'] ), $allowed_order_by, true ) ) ? wp_unslash( $_GET['orderBy'] ) : 'name';
		$order = ( isset($_GET['order']) && in_array( strtoupper( wp_unslash( $_GET['order'] ) ), array( 'ASC', 'DESC' ), true ) ) ? strtoupper( wp_unslash( $_GET['order'] ) ) : 'ASC';
		$s = isset($_GET['s']) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
		$where = array();
		$params = array();
		$sql = 'SELECT * FROM '. $model->getTable();

		$sqlTotal = 'SELECT COUNT(id) AS total FROM '. $model->getTable();
		if($s != ""){
			$sql .= " WHERE name like '%%%s%%'";
			$sqlTotal .= " WHERE name like '%%%s%%'";
			$params[] = $s;
		}
		// Only prepare when there is something to bind. With no search term $sqlTotal
		// carries no placeholders, and $wpdb->prepare() on a placeholder-free query is a
		// _doing_it_wrong() notice in current WordPress — so the unfiltered list view was
		// emitting one on every load.
		if ( $params ) {
			$sqlTotal = $wpdb->prepare($sqlTotal, $params); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- built above from literals plus Lists::getTable(); the only bound value is the search term.
		}

		// $orderBy and $order are allowlisted above — prepare() cannot bind an identifier.
		$sql .= " ORDER BY $orderBy $order";
		$sql .= " LIMIT %d, %d";
		$params[] = $offset;
		$params[] = $limit;

		// Always has the two LIMIT placeholders, so this one never needs the guard above.
		$sql = $wpdb->prepare($sql, $params); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- built above from literals plus Lists::getTable(); every user value is bound.

		// Data
		$lists = $wpdb->get_results($sql); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter -- the query in this variable is prepared where it is built, above
		$total_row = $wpdb->get_row($sqlTotal); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter -- the query in this variable is prepared where it is built, above
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








	elseif ($action == 'export_all_list'):





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


