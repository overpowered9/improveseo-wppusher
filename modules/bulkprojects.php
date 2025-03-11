<?php

use ImproveSEO\View;
use ImproveSEO\Validator;
use ImproveSEO\Models\Bulktask;
use ImproveSEO\Models\Bulktasksdetail;
use ImproveSEO\FlashMessage;


function improveseo_bulkprojects()
{
	global $wpdb;

	$action = isset($_GET['action']) ? $_GET['action'] : 'index';
	$limit = isset($_GET['limit']) ? $_GET['limit'] : 20;
	$offset = isset($_GET['paged']) ? $_GET['paged'] * $limit - $limit : 0;
	$model = new Bulktask();
	$detailsTaskModel = new Bulktasksdetail();


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
		$orderBy = isset($_GET['orderBy']) ? $_GET['orderBy'] : 'created_at';
		$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

		$highlight = isset($_GET['highlight']) ? $_GET['highlight'] : null;

		$where = array();
		$params = array();

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
		$projects = $wpdb->get_results($sql);
		$total_row = $wpdb->get_row($sqlTotal);
		$total = $total_row->total;

		$pages = ceil($total / $limit);
		$page = floor($offset / $limit) + 1;

		// echo "<pre>";
		// print_r($projects);
		// exit();

		View::render('bulkprojects.index', compact('projects', 'page', 'pages', 'order', 'orderBy', 'highlight'));


	elseif($action == 'viewAllTasks') :
		if(isset($_GET['id'])) {
			$id = $_GET['id'];
			$where = array('id'=>$id);
			$sql = 'SELECT * FROM ' . $model->getTable();
			//if (sizeof($where)) {
				$sql .= ' WHERE `id`='.$id;
			//}
			$params = [];
			$sql = $wpdb->prepare($sql, $params);
			$projects = $wpdb->get_results($sql);
			if(!empty($projects[0]->name)) {
				$project_name = $projects[0]->name;
			} else {
				$project_name = '';
			}
			
		} else {
			$project_name = '';
		}
		
		

		

		// Filters
		$id = isset($_GET['id']) ? $_GET['id'] : '';
		//exit();
		$orderBy = isset($_GET['orderBy']) ? $_GET['orderBy'] : 'created_at';
		$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

		$highlight = isset($_GET['highlight']) ? $_GET['highlight'] : null;
		// if(!empty($id)) {
		// 	$where = array('bulktask_id'=>$id);
		// } else {
			$where = array();
		//}

		
		
		$params = array();

		$sql = 'SELECT * FROM ' . $detailsTaskModel->getTable();
		//if (sizeof($where)) {
			$sql .= ' WHERE `bulktask_id` = ' .$id;
		//}

		$sqlTotal = 'SELECT COUNT(id) AS total FROM ' . $detailsTaskModel->getTable();
		//if (sizeof($where)) {
			$sqlTotal .= ' WHERE `bulktask_id` = ' .$id;
		//}

		$sqlTotal = $wpdb->prepare($sqlTotal, $params);

		$sql .= " ORDER BY $orderBy $order";
		$sql .= " LIMIT %d, %d";

		$params[] = $offset;
		$params[] = $limit;

		$sql = $wpdb->prepare($sql, $params);

		// Data
		$projects = $wpdb->get_results($sql);
		$total_row = $wpdb->get_row($sqlTotal);
		$total = $total_row->total;

		$pages = ceil($total / $limit);
		$page = floor($offset / $limit) + 1;

		

		View::render('bulkprojects.alltasks', compact('projects','project_name','id', 'page', 'pages', 'order', 'orderBy', 'highlight'));



	elseif ($action == 'viewAiContent') : 
		// Filters
		$id = isset($_GET['id']) ? $_GET['id'] : '';
		//exit();
		$orderBy = isset($_GET['orderBy']) ? $_GET['orderBy'] : 'created_at';
		$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

		$highlight = isset($_GET['highlight']) ? $_GET['highlight'] : null;
		
		$params = array();

		$sql = 'SELECT * FROM ' . $detailsTaskModel->getTable();
		//if (sizeof($where)) {
			$sql .= ' WHERE `id` = ' .$id;
		//}

		$sqlTotal = 'SELECT COUNT(id) AS total FROM ' . $detailsTaskModel->getTable();
		//if (sizeof($where)) {
			$sqlTotal .= ' WHERE `id` = ' .$id;
		//}

		$sqlTotal = $wpdb->prepare($sqlTotal, $params);

		$sql .= " ORDER BY $orderBy $order";
		$sql .= " LIMIT %d, %d";

		$params[] = $offset;
		$params[] = $limit;

		$sql = $wpdb->prepare($sql, $params);

		// Data
		$projects = $wpdb->get_results($sql);
		
		$total_row = $wpdb->get_row($sqlTotal);
		$total = $total_row->total;

		$pages = ceil($total / $limit);
		$page = floor($offset / $limit) + 1;

		

		View::render('bulkprojects.viewAIContent', compact('projects','id', 'page', 'pages', 'order', 'orderBy', 'highlight'));







	elseif ($action == 'delete') :

		$id = $_GET['id'];

		// Delete all posts from this project
		$wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->prefix . "posts WHERE ID IN (SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = 'improveseo_project_id' AND meta_value = %s)", $id));
		$wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->prefix . "postmeta WHERE meta_key = 'improveseo_project_id' AND meta_value = %s", $id));

		$model->delete($id);

		FlashMessage::success('Project and all posts/pages deleted.');
		wp_redirect(admin_url('admin.php?page=improveseo_projects'));
		exit;

	elseif ($action == 'delete_posts') :

		$id = $_GET['id'];

		// Delete all posts from this project
		$wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->prefix . "postmeta WHERE post_id IN (SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = 'improveseo_project_id' AND meta_value = %s) AND meta_key = 'improveseo_channel'", $id));
		$wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->prefix . "posts WHERE ID IN (SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = 'improveseo_project_id' AND meta_value = %s)", $id));
		$wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->prefix . "postmeta WHERE meta_key = 'improveseo_project_id' AND meta_value = %s", $id));

		$model->update(array('iteration' => 0), $id);

		FlashMessage::success('All posts/pages deleted.');
		wp_redirect(admin_url('admin.php?page=improveseo_projects'));
		exit;

	elseif ($action == 'stop') :

		 $id = $_GET['id'];
		 $mainid = $_GET['mainid'];

		//$detailsTaskModel->update(array('status' => 'Stoped'), $id);
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE `".$detailsTaskModel->getTable()."`
				SET status = %s WHERE id = %d",
				'Stoped', $id)
		);
		$wpdb->last_error;
//exit('here');
		FlashMessage::success('Project stopped. You can continue process by clicking Build posts');
		wp_redirect(admin_url('admin.php?page=improveseo_bulkprojects&action=viewAllTasks&id='.$mainid));
		exit;

	elseif($action == 'publish') : 

		$id = $_GET['id'];
		$mainid = sanitize_title_with_dashes($_GET['mainid']);


		global $wpdb;
	$sql = "SELECT * FROM `" . $wpdb->prefix . "improveseo_bulktasksdetails` WHERE `id`=".$id;
	
	$Bulktasks = $wpdb->get_results($sql);

	

	$content = '';
	foreach($Bulktasks as $key => $value) {
		// short code
		if(!empty($value->testimonial)) { 
			$testimonial_ids = '';
			$all_testimonial = explode("||",$value->testimonial); 
			foreach($all_testimonial as $key1 => $value1) {
				if(!empty($value1)) {
					$testimonial_ids = $value1.','.$testimonial_ids;
				}
			}
			$content = $content.'<p>[improveseo_testimonial id="'.$testimonial_ids.'"]</p>';
		} 
		
		if(!empty($value->Button_SC)) { 
			$content = $content.'<p>[improveseo_buttons id="'.$value->Button_SC.'"]</p>';
		} 
		
		if(!empty($value->GoogleMap_SC)) { 
			$content = $content.'<p>[improveseo_googlemaps id="'.$value->GoogleMap_SC.'"]</p>';
		} 
		
		if(!empty($value->Video_SC)) { 
			$content = $content.'<p style="width:100%">[improveseo_video id="'.$value->Video_SC.'"]</p>';
		} 
		$catids = [];
		if(!empty($value->cats)) {
			$categories = explode("||",$value->cats);
			foreach($categories as $ckey => $cvalue) {
				if(!empty($cvalue)) {
					array_push($catids,$cvalue);
					//$catids = $value1.','.$cvalue;
				}
			} 
		} else {
			$categories = '';
		}
		$tags = array('-');
		$fullcontent = "<img src='".base64_decode($value->ai_image)."' style='width:100%; margin-bottom: 100px;' alt='".$value->ai_title."'>".base64_decode($value->ai_content).$content;
		$post_date = date('Y-m-d H:i:s');
		$post_status = 'publish';
	


		


		
		if($value->assigning_authors=='assigning_authors') {
			$post_author = $value->assigning_authors_value;
		} 

		if($value->assigning_authors=='assigning_multi_authors') {
			


		
			$first_names = array(
				'John', 'Jane', 'Michael', 'Emily', 'David', 'Sarah', 'James', 'Linda', 'Robert', 'Jessica',
				'Daniel', 'Laura', 'Chris', 'Amy', 'Mark', 'Angela', 'Steven', 'Megan', 'Paul', 'Rachel',
				'Peter', 'Hannah', 'Kevin', 'Sophia', 'Edward', 'Emma', 'Jason', 'Grace', 'Tom', 'Alice'
				// Add more names as needed to increase uniqueness
			);
			
			$last_names = array(
				'Smith', 'Johnson', 'Brown', 'Williams', 'Jones', 'Miller', 'Davis', 'Garcia', 'Martinez', 'Taylor',
				'Wilson', 'Moore', 'Anderson', 'Thomas', 'Jackson', 'White', 'Harris', 'Martin', 'Thompson', 'Lopez',
				'Gonzalez', 'Clark', 'Lewis', 'Walker', 'Hall', 'Allen', 'Young', 'King', 'Wright', 'Scott'
				// Add more names as needed
			);
		
			// Pick a random first and last name
			$first_name = $first_names[array_rand($first_names)];
			$last_name = $last_names[array_rand($last_names)];
		
			// Combine to create a full name
			$fullname = array('first_name'=>$first_name,'Last_name'=> $last_name);
		
		
		
			$first_name =  $fullname['first_name'];
			$last_name = $fullname['Last_name'];
			$username = str_replace(" ", "", $fullname);
		
			// Check if the username already exists
			if ( username_exists( $username ) || email_exists( $first_name.'@example.com' ) ) {
				my_plugin_log('author recreate : '.$username);
				
				$first_name = $first_names[array_rand($first_names)];
				$last_name = $last_names[array_rand($last_names)];
				$username = str_replace(" ", "", $first_name.$last_name);
		
			}
		
			// Define user information
			$user_data = array(
				'user_login'    => $username,        // Username
				'user_pass'     => 'PASssword123@4jkkhk$qwrfg123',                // User password
				'user_email'    => $first_name.'@example.com', // User email
				'first_name'    => $first_name,
				'last_name'     => $last_name,
				'role'          => 'author',                     // Assign 'author' role
			);
		
			my_plugin_log('author created : '.$username);
		
			// Create the user
			$post_author  = wp_insert_user( $user_data );
			//$post_author = 10;
		
			
		}











			
		if(!empty($value->post_id)) {
			$post_array = array(
				'ID' =>  $value->post_id,
				'post_author' => $post_author ,
				'post_content' => $fullcontent,
				'post_title' => $value->ai_title,
				'comment_status' => 'closed',
				'ping_status' => 'closed',
				'post_type' => "post",
				'post_date' => $post_date,
				'post_status' => $post_status
			);
			wp_update_post( $post_array );
			$post_id =$value->post_id;
		} else {
			$post_array = array(
				'post_author' => $post_author,
				'post_content' => $fullcontent,
				'post_title' => $value->ai_title,
				'comment_status' => 'closed',
				'ping_status' => 'closed',
				'post_type' => "post",
				'post_date' => $post_date,
				'post_status' => $post_status
			);
			$post_id = wp_insert_post($post_array);
		}
			
			  // Replace with your desired tags
			//if(!empty($tags)) {
			wp_set_post_tags($post_id, '-');
			//}
    		
			//$post_id = $wpdb->insert_id;

			if ((!empty($catids))) {
				wp_set_post_categories($post_id, $catids, false);
			}

			$wpdb->query(
				$wpdb->prepare(
					"UPDATE `".$wpdb->prefix."improveseo_bulktasksdetails`
					SET state = %s, post_id = %d WHERE id = %d",
					$post_status, $post_id, $value->id
				)
			);
			my_plugin_log('This is a log message : '.$value->id);

			FlashMessage::success('Post hasb been published successfully.');
			
		wp_redirect(admin_url("admin.php?page=improveseo_bulkprojects&action=viewAllTasks&id={$mainid}"));
		//wp_redirect(admin_url("admin.php?page=improveseo_projects&highlight={$new_id}"));
		//exit;

			//wp_send_json_success(array('status' => 'true',"message"=>'Post has been published successfully.'));
	}


















	elseif ($action == 'export_urls') :

		$id = $_GET['id'];
		$project_name = sanitize_title_with_dashes($_GET['name']);

		@set_time_limit(0);

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

		wt_load_templates('import-export.php');
		$exportRecords = new improveseo_import_export();
		$exportRecords->export($data, 'all-project');

		exit;

	elseif ($action == 'export_project') :

		$id = $_GET['id'];
		$project_name = sanitize_title_with_dashes($_GET['name']);

		@set_time_limit(0);

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

		$id = $_GET['id'];

		@set_time_limit(0);

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

		$id = $_GET['id'];

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
			$project_ids = $_GET['project_ids'];
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
			$project_ids = $_GET['project_ids'];
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


