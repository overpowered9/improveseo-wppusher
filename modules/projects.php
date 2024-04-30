<?php

use ImproveSEO\View;
use ImproveSEO\Validator;
use ImproveSEO\Models\Task;
use ImproveSEO\FlashMessage;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly  

function improveseo_projects()
{
	global $wpdb;

	$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'index';
	$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
	$offset = isset($_GET['paged']) ? max(0, intval($_GET['paged']) * $limit - $limit) : 0;
	
	$model = new Task();


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


	
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash ( $_POST['_wpnonce'] ) ) , 'import_project_nonce' ) ){
			wp_redirect(admin_url('admin.php?page=improveseo_projects'));
			exit();
		}


		if (!current_user_can('upload_files')) {
			FlashMessage::success('Current user can\'t upload file');
			wp_redirect(admin_url('admin.php?page=improveseo_projects'));
			exit();
		}

		if (in_array($_FILES['upload_csv']['type'], $fileMimes) === false) {
			FlashMessage::success('Please Upload a Valid CSV file');
			wp_redirect(admin_url('admin.php?page=improveseo_projects'));
			exit();
		}


		//Import uploaded file to Database
		$file = fopen($_FILES['upload_csv']['tmp_name'], "r");

		$counter = 0;
		while (!feof($file)) {

			$file_content = fgetcsv($file);

			if ($counter != 0) {

				$wpdb->insert($wpdb->prefix . "improveseo_tasks", array(
					'id' => $file_content[0],
					'name' => $file_content[1],
					'content' => $file_content[2],
					'options' => $file_content[3],
					'iteration' => $file_content[4],
					'spintax_iterations' => $file_content[5],
					'max_iterations' => $file_content[6],
					'state' => "Draft",
					'created_at' => $file_content[8],
					'updated_at' => $file_content[9],
					'finished_at' => $file_content[10],
					'deleted_at' => $file_content[11],
					'cats' => $file_content[12],
				));
			}
			
			$counter++;

		}

		$counter = $counter-2;  

		fclose($file);

		FlashMessage::success($counter . ' Project Imported Successfully.');

	}


	if ($action == 'index') :
		// Filters
        $orderBy = isset($_GET['orderBy']) && in_array($_GET['orderBy'], $allowed_order_by) ? sanitize_text_field($_GET['orderBy']) : 'created_at';

        $order = isset($_GET['order']) && in_array($_GET['order'], $allowed_order) ? sanitize_text_field($_GET['order']) : 'asce';


        $highlight = isset($_GET['highlight']) ? sanitize_text_field($_GET['highlight']) : null;

		$where = array();

		$params = array();
        $tablename = $model->getTable();
		$sql = 'SELECT * FROM tablename';
        $sql = str_replace("tablename",$tablename,$sql);

		if (sizeof($where)) {
            $where = implode(' AND ', $where);
			$sql .= "WHERE $where";
		}

		$sqlTotal = 'SELECT COUNT(id) AS total FROM tablename';
        $sqlTotal = str_replace("tablename",$tablename,$sqlTotal);
		if (sizeof($where)) {
            $where = implode(' AND ', $where);
			$sqlTotal .= "WHERE $where";
		}

		$sqlTotal = $wpdb->prepare($sqlTotal, $params);


		$sql .= " ORDER BY %s %s";

        $sql .= " LIMIT %d, %d";
        $params[] = $orderBy;
        $params[] = $order;
		$params[] = $offset;
		$params[] = $limit;

		$sql = $wpdb->prepare($sql, $params);

//        die(print_r($sql));
		// Data
		$projects = $wpdb->get_results($sql);

		$total_row = $wpdb->get_row($sqlTotal);
		$total = $total_row->total;

		$pages = ceil($total / $limit);
		$page = floor($offset / $limit) + 1;

		View::render('projects.index', compact('projects', 'page', 'pages', 'order', 'orderBy', 'highlight'));

	elseif ($action == 'delete') :

        $id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;
        $post_tablename = $wpdb->prefix . "posts";
        $postmeta_tablename = $wpdb->prefix."postmeta";
        $sql = "DELETE FROM post_tablename WHERE ID IN (SELECT post_id FROM postmeta_tablename WHERE meta_key = 'improveseo_project_id' AND meta_value = %d)";
        // Delete all posts from this project  
        $sql = str_replace(array("post_tablename","postmeta_tablename"),array($post_tablename,$postmeta_tablename),$sql);
		$wpdb->query($wpdb->prepare($sql, $id));
        $sql = "DELETE FROM " . $wpdb->prefix . "postmeta WHERE meta_key = 'improveseo_project_id' AND meta_value = %s";
		$wpdb->query($wpdb->prepare($sql, $id));

		$model->delete($id);

		FlashMessage::success('Project and all posts/pages deleted.');
		wp_redirect(admin_url('admin.php?page=improveseo_projects'));
		exit;

	elseif ($action == 'delete_posts') :

        $id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;


        // Delete all posts from this project
		$wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->prefix . "postmeta WHERE post_id IN (SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = 'improveseo_project_id' AND meta_value = %s) AND meta_key = 'improveseo_channel'", $id));
		$wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->prefix . "posts WHERE ID IN (SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = 'improveseo_project_id' AND meta_value = %s)", $id));
		$wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->prefix . "postmeta WHERE meta_key = 'improveseo_project_id' AND meta_value = %s", $id));

		$model->update(array('iteration' => 0), $id);

		FlashMessage::success('All posts/pages deleted.');
		wp_redirect(admin_url('admin.php?page=improveseo_projects'));
		exit;

	elseif ($action == 'stop') :

        $id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;


        $model->update(array('deleted_at' => '1970-01-01 11:11:11'), $id);

		FlashMessage::success('Project stopped. You can continue process by clicking Build posts');
		wp_redirect(admin_url('admin.php?page=improveseo_projects'));
		exit;

	elseif ($action == 'export_urls') :

        $id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;

        $project_name = sanitize_title_with_dashes($_GET['name']);

		

		$urls = "";
		$posts = $wpdb->get_results($wpdb->prepare("SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = 'improveseo_project_id' AND meta_value = %s", $id));
		foreach ($posts as $post) {
			$urls .= get_permalink($post->post_id) . "\r\n";
		}

		file_put_contents("$project_name.txt", $urls);

		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename=' . basename("$project_name.txt"));
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize("$project_name.txt"));
		readfile("$project_name.txt");
		exit;

	elseif ($action == 'export_all_project') :

		$data = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}improveseo_tasks"));

		if (empty($data)) {
			wp_redirect(admin_url('admin.php?page=improveseo_projects'));
		}

		improveseo_wt_load_templates('import-export.php');
		$exportRecords = new improveseo_import_export();
		$exportRecords->export($data, 'all-project');

		exit;

	elseif ($action == 'export_project') :

        $id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;

        $project_name = sanitize_title_with_dashes($_GET['name']);

		

		$data = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}improveseo_tasks where id = %s", $id));

		$header_row = [];
		$data_row = [];
		foreach ($data[0] as $key => $value) {
			$header_row[] = $key;
			$data_row[] = $value;
		}

		header('Content-type: text/csv');
		header('Content-Disposition: attachment; filename=' . basename("$project_name.csv"));
		header('Expires: 0');
		header('Pragma: public');

		$fh = @fopen('php://output', 'w');

		fprintf($fh, chr(0xEF) . chr(0xBB) . chr(0xBF));
		fputcsv($fh, $header_row);
		fputcsv($fh, $data_row);
		fclose($fh);

		exit;

	elseif ($action == 'export_preview_url') :

        $id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;




        $urls = [];
		$posts = $wpdb->get_results($wpdb->prepare("SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = 'improveseo_project_id' AND meta_value = %s", $id));
		foreach ($posts as $post) {
			$url = get_permalink($post->post_id);
			$url .= "?id=" . $id;
			array_push($urls, $url);
		}

		$preview_url_key = array_rand($urls, 1);
		$preview_url = $urls[$preview_url_key];

		header("location: $preview_url");
		exit;




	elseif ($action == 'duplicate') :

        $id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;


        $task = $model->find($id);

		$new_id = $model->create(array(
			'name' => $task->name . ' - Copy',
			'content' => base64_encode(json_encode($task->content)),
			'options' => base64_encode(json_encode($task->options)),
			'spintax_iterations' => $task->spintax_iterations,
			'max_iterations' => $task->max_iterations,
			'state' => 'Draft'
		));

		FlashMessage::success('Project duplicated.');
		wp_redirect(admin_url("admin.php?page=improveseo_projects&highlight={$new_id}"));
		exit;

	elseif ($action == 'bulk-delete-all') :
		if (isset($_GET['project_ids'])) {
            $project_ids = isset($_GET['project_ids']) ? sanitize_text_field($_GET['project_ids']) : '';

            if (!empty($project_ids)) {
				foreach ($project_ids as $project_id) {
					// Delete all posts from this project
					$wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->prefix . "posts WHERE ID IN (SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = 'improveseo_project_id' AND meta_value = %s)", $project_id));

					$wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->prefix . "postmeta WHERE meta_key = 'improveseo_project_id' AND meta_value = %s", $project_id));

					$model->delete($project_id);
				}
				FlashMessage::success('All posts/pages deleted.');
			}
		} else {
			FlashMessage::message('Please select projects', 'error');
		}
		wp_redirect(admin_url('admin.php?page=improveseo_projects'));
		exit;
	elseif ($action == 'bulk-delete-posts') :
		if (isset($_GET['project_ids'])) {
            $project_ids = isset($_GET['project_ids']) ? sanitize_text_field($_GET['project_ids']) : '';

            if (!empty($project_ids)) {
				foreach ($project_ids as $project_id) {
					// Delete all posts from this project
					$wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->prefix . "postmeta WHERE post_id IN (SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = 'improveseo_project_id' AND meta_value = %s) AND meta_key = 'improveseo_channel'", $project_id));
					$wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->prefix . "posts WHERE ID IN (SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = 'improveseo_project_id' AND meta_value = %s)", $project_id));
					$wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->prefix . "postmeta WHERE meta_key = 'improveseo_project_id' AND meta_value = %s", $project_id));

					$model->update(array('iteration' => 0), $project_id);
				}
				FlashMessage::success('All posts/pages deleted.');
			}
		}
		wp_redirect(admin_url('admin.php?page=improveseo_projects'));
		exit;
	elseif ($action == 'bulk-empty') :
		FlashMessage::message('Please select an option from bulk actions', 'error');
		wp_redirect(admin_url('admin.php?page=improveseo_projects'));
		exit;
	endif;
}
