<?php

use ImproveSEO\View;
use ImproveSEO\Validator;
use ImproveSEO\Models\Task;
use ImproveSEO\FlashMessage;

if (!defined('ABSPATH')) exit; // Exit if accessed directly  

function improveseo_projects()
{
	global $wpdb;
	$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'index';
	$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
	$offset = isset($_GET['paged']) ? max(0, intval($_GET['paged']) * $limit - $limit) : 0;

    error_log("ACTION => ". $action);
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



		if (!isset($_POST['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'import_project_nonce')) {
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
		$file = fopen(sanitize_text_field($_FILES['upload_csv']['tmp_name']), "r");

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

		$counter = $counter - 2;

		fclose($file);

		FlashMessage::success($counter . ' Project Imported Successfully.');
	}


	if ($action == 'index') :

		// Filters
		$orderBy = isset($_GET['orderBy']) && in_array($_GET['orderBy'], $allowed_order_by) ? sanitize_text_field($_GET['orderBy']) : 'created_at';
		$order = isset($_GET['order']) && in_array($_GET['order'], $allowed_order) ? sanitize_text_field($_GET['order']) : 'desc';
		$highlight = isset($_GET['highlight']) ? sanitize_text_field($_GET['highlight']) : null;
	
		$tablename = $wpdb->prefix . "improveseo_tasks"; // Ensure correct table name
	
		// Fetch total count
		$sqlTotal = "SELECT COUNT(id) AS total FROM $tablename";
		$total_row = $wpdb->get_row($sqlTotal);
		$total = isset($total_row->total) ? $total_row->total : 0;
	
		// Fetch paginated data
		$sql = $wpdb->prepare(
			"SELECT * FROM $tablename ORDER BY $orderBy $order LIMIT %d, %d",
			$offset,
			$limit
		);
	
		$projects = $wpdb->get_results($sql);
		$pages = ($limit > 0) ? ceil($total / $limit) : 1;
		$page = floor($offset / $limit) + 1;
	
		View::render('projects.index', compact('projects', 'page', 'pages', 'order', 'orderBy', 'highlight'));
	
	elseif ($action == 'delete') :
	
		$id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;
		if ($id) {
			$post_table = $wpdb->prefix . "posts";
			$postmeta_table = $wpdb->prefix . "postmeta";
	
			// Delete all posts from this project
			$wpdb->query($wpdb->prepare("DELETE FROM $post_table WHERE ID IN (SELECT post_id FROM $postmeta_table WHERE meta_key = 'improveseo_project_id' AND meta_value = %d)", $id));
			$wpdb->query($wpdb->prepare("DELETE FROM $postmeta_table WHERE meta_key = 'improveseo_project_id' AND meta_value = %d", $id));
	
			$model->delete($id);
			FlashMessage::success('Project and all posts/pages deleted.');
		}
		
		wp_redirect(admin_url('admin.php?page=improveseo_projects'));
		exit;
	elseif ($action == 'export_preview_url') :
	
		$id = $_GET['id'];
		@set_time_limit(0);

		$urls = [];
		$posts = $wpdb->get_results($wpdb->prepare("SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = 'improveseo_project_id' AND meta_value = %s", $id));
		error_log(print_r($posts, true));
		foreach ($posts as $post) {
			$url = get_permalink($post->post_id);
			$url .= "?id=" . $id;
			array_push($urls, $url);
		}

		$preview_url_key = array_rand($urls, 1);
		$preview_url = $urls[$preview_url_key];

		header("location: $preview_url");
		exit;
	
	elseif ($action == 'export_urls') :
	
		$id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;
		$project_name = isset($_GET['name']) ? sanitize_file_name($_GET['name']) : 'exported_urls';
	
		if ($id) {
			$urls = [];
			$posts = $wpdb->get_results($wpdb->prepare("SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = 'improveseo_project_id' AND meta_value = %d", $id));
			
			foreach ($posts as $post) {
				$urls[] = get_permalink($post->post_id);
			}
	
			$file_path = WP_CONTENT_DIR . "/uploads/$project_name.txt";
			file_put_contents($file_path, implode("\r\n", $urls));
	
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename=' . basename($file_path));
			header('Content-Length: ' . filesize($file_path));
			readfile($file_path);
			exit;
		}
	
	elseif ($action == 'export_project') 
	
		$id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;
		$project_name = isset($_GET['name']) ? sanitize_file_name($_GET['name']) : 'exported_project';
	
		if ($id) {
			$data = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}improveseo_tasks WHERE id = %d", $id));
	
			if (!empty($data)) {
				$header_row = array_keys((array) $data[0]);
				$data_row = array_values((array) $data[0]);
	
				header('Content-type: text/csv');
				header('Content-Disposition: attachment; filename=' . $project_name . '.csv');
	
				$fh = fopen('php://output', 'w');
				fputcsv($fh, $header_row);
				fputcsv($fh, $data_row);
				fclose($fh);
	
				exit;
			}
		}
	
		wp_redirect(admin_url('admin.php?page=improveseo_projects'));
		exit;
	
	elseif ($action == 'bulk-delete-all') :
	
		if (!empty($_GET['project_ids']) && is_array($_GET['project_ids'])) {
			$project_ids = array_map('intval', $_GET['project_ids']);
	
			foreach ($project_ids as $project_id) {
				$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}posts WHERE ID IN (SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = 'improveseo_project_id' AND meta_value = %d)", $project_id));
				$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}postmeta WHERE meta_key = 'improveseo_project_id' AND meta_value = %d", $project_id));
				$model->delete($project_id);
			}
	
			FlashMessage::success('All selected projects and their posts deleted.');
		} else {
			FlashMessage::message('Please select projects', 'error');
		}
	
		wp_redirect(admin_url('admin.php?page=improveseo_projects'));
		exit;
	
	elseif ($action == 'bulk-delete-posts') :
	
		if (!empty($_GET['project_ids']) && is_array($_GET['project_ids'])) {
			$project_ids = array_map('intval', $_GET['project_ids']);
	
			foreach ($project_ids as $project_id) {
				$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}postmeta WHERE post_id IN (SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = 'improveseo_project_id' AND meta_value = %d) AND meta_key = 'improveseo_channel'", $project_id));
				$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}posts WHERE ID IN (SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = 'improveseo_project_id' AND meta_value = %d)", $project_id));
				$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}postmeta WHERE meta_key = 'improveseo_project_id' AND meta_value = %d", $project_id));
				$model->update(['iteration' => 0], $project_id);
			}
	
			FlashMessage::success('All selected posts/pages deleted.');
		}
	
		wp_redirect(admin_url('admin.php?page=improveseo_projects'));
		exit;
	
	elseif ($action == 'bulk-empty') :
		FlashMessage::message('Please select an option from bulk actions', 'error');
		wp_redirect(admin_url('admin.php?page=improveseo_projects'));
		exit;
	endif;
	
}
