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
		$counter = $counter - 2;
		fclose($file);
		FlashMessage::success($counter . ' Project Imported Successfully.');
	}

	if (isset($_POST['action']) && $_POST['action'] === 'bulk-delete-projects') {
		if (!isset($_POST['bulk_delete_nonce']) || !wp_verify_nonce($_POST['bulk_delete_nonce'], 'bulk_delete_projects')) {
			FlashMessage::message('Security check failed. Please try again.', 'error');
			wp_redirect(admin_url('admin.php?page=improveseo_bulkprojects'));
			exit;
		}

		if (!current_user_can('delete_posts')) {
			FlashMessage::message('You do not have permission to delete projects.', 'error');
			wp_redirect(admin_url('admin.php?page=improveseo_bulkprojects'));
			exit;
		}

		if (!isset($_POST['project_ids']) || empty($_POST['project_ids'])) {
			FlashMessage::message('No projects selected for deletion.', 'error');
			wp_redirect(admin_url('admin.php?page=improveseo_bulkprojects'));
			exit;
		}

		$project_ids = array_map('intval', $_POST['project_ids']);
		$deleted_count = 0;

		foreach ($project_ids as $project_id) {
			if ($project_id > 0) {
				$post_ids = $wpdb->get_col($wpdb->prepare(
					"SELECT post_id FROM {$wpdb->prefix}postmeta 
						WHERE meta_key = 'improveseo_project_id' AND meta_value = %d",
					$project_id
				));

				if (!empty($post_ids)) {
					$post_ids_str = implode(',', array_map('intval', $post_ids));
					$wpdb->query("DELETE FROM {$wpdb->prefix}posts WHERE ID IN ($post_ids_str)");

					$wpdb->query("DELETE FROM {$wpdb->prefix}postmeta WHERE post_id IN ($post_ids_str)");
				}

				$wpdb->query($wpdb->prepare(
					"DELETE FROM {$wpdb->prefix}postmeta 
						WHERE meta_key = 'improveseo_project_id' AND meta_value = %d",
					$project_id
				));

				$wpdb->query($wpdb->prepare(
					"DELETE FROM " . $detailsTaskModel->getTable() . " 
						WHERE bulktask_id = %d",
					$project_id
				));

				if ($model->delete($project_id)) {
					$deleted_count++;
				}
			}
		}

		if ($deleted_count > 0) {
			FlashMessage::success("Successfully deleted {$deleted_count} project(s) and all associated data.");
		} else {
			FlashMessage::message('No projects were deleted. Please try again.', 'error');
		}

		$_GET['action'] = 'index';
		$action = 'index';
	}

	if (isset($_POST['action']) && $_POST['action'] === 'bulk-delete-tasks') {
		if (!isset($_POST['bulk_delete_nonce']) || !wp_verify_nonce($_POST['bulk_delete_nonce'], 'bulk_delete_tasks')) {
			FlashMessage::message('Security check failed. Please try again.', 'error');
			wp_redirect(admin_url('admin.php?page=improveseo_bulkprojects&action=viewAllTasks&id=' . intval($_POST['main_id'])));
			exit;
		}

		if (!current_user_can('delete_posts')) {
			FlashMessage::message('You do not have permission to delete tasks.', 'error');
			wp_redirect(admin_url('admin.php?page=improveseo_bulkprojects&action=viewAllTasks&id=' . intval($_POST['main_id'])));
			exit;
		}

		if (!isset($_POST['project_ids']) || empty($_POST['project_ids'])) {
			FlashMessage::message('No tasks selected for deletion.', 'error');
			wp_redirect(admin_url('admin.php?page=improveseo_bulkprojects&action=viewAllTasks&id=' . intval($_POST['main_id'])));
			exit;
		}

		$task_ids = array_map('intval', $_POST['project_ids']);
		$main_id = intval($_POST['main_id']);
		$deleted_count = 0;

		foreach ($task_ids as $task_id) {
			if ($task_id > 0) {
				$task_data = $wpdb->get_row($wpdb->prepare(
					"SELECT post_id FROM {$wpdb->prefix}improveseo_bulktasksdetails WHERE id = %d",
					$task_id
				));

				if ($task_data && !empty($task_data->post_id)) {
					wp_delete_post($task_data->post_id, true);

					$wpdb->delete(
						$wpdb->prefix . 'postmeta',
						array('post_id' => $task_data->post_id),
						array('%d')
					);
				}

				$deleted = $wpdb->delete(
					$wpdb->prefix . 'improveseo_bulktasksdetails',
					array('id' => $task_id),
					array('%d')
				);

				if ($deleted !== false) {
					$deleted_count++;
				}
			}
		}

		if ($deleted_count > 0) {
			FlashMessage::success("Successfully deleted {$deleted_count} task(s) and their associated posts.");
		} else {
			FlashMessage::message('No tasks were deleted. Please try again.', 'error');
		}

		$_GET['action'] = 'viewAllTasks';
		$action = 'viewAllTasks';
	}

	if ($action == 'index'):
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
		View::render('bulkprojects.index', compact('projects', 'page', 'pages', 'total', 'order', 'orderBy', 'highlight'));

	elseif ($action == 'viewAllTasks'):
		if (isset($_GET['id'])) {
			$id = $_GET['id'];
			$where = array('id' => $id);
			$sql = 'SELECT * FROM ' . $model->getTable();
			$sql .= ' WHERE `id`=' . $id;
			$params = [];
			$sql = $wpdb->prepare($sql, $params);
			$projects = $wpdb->get_results($sql);
			if (!empty($projects[0]->name)) {
				$project_name = $projects[0]->name;
			} else {
				$project_name = '';
			}
		} else {
			$project_name = '';
		}

		// Filters
		$id = isset($_GET['id']) ? $_GET['id'] : '';
		$orderBy = isset($_GET['orderBy']) ? $_GET['orderBy'] : 'created_at';
		$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
		$highlight = isset($_GET['highlight']) ? $_GET['highlight'] : null;
		$where = array();
		$params = array();
		$sql = 'SELECT * FROM ' . $detailsTaskModel->getTable();
		$sql .= ' WHERE `bulktask_id` = ' . $id;
		$sqlTotal = 'SELECT COUNT(id) AS total FROM ' . $detailsTaskModel->getTable();
		$sqlTotal .= ' WHERE `bulktask_id` = ' . $id;
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
		View::render('bulkprojects.alltasks', compact('projects', 'project_name', 'id', 'page', 'pages', 'total', 'order', 'orderBy', 'highlight'));

	elseif ($action == 'viewAiContent'):
		// Filters
		$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
		$orderBy = isset($_GET['orderBy']) ? $_GET['orderBy'] : 'created_at';
		$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
		$highlight = isset($_GET['highlight']) ? $_GET['highlight'] : null;
		
		$sql = 'SELECT * FROM ' . $detailsTaskModel->getTable();
		$sql .= ' WHERE `id` = %d';
		$sql .= " ORDER BY $orderBy $order";
		$sql = $wpdb->prepare($sql, $id);

		// Data
		$projects = $wpdb->get_results($sql);
		$total = count($projects);
		$pages = 1;
		$page = 1;
		View::render('bulkprojects.viewAIContent', compact('projects', 'id', 'page', 'pages', 'order', 'orderBy', 'highlight'));

	elseif ($action == 'delete'):
		$id = $_GET['id'];
		// Delete all posts from this project
		$wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->prefix . "posts WHERE ID IN (SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = 'improveseo_project_id' AND meta_value = %s)", $id));
		$wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->prefix . "postmeta WHERE meta_key = 'improveseo_project_id' AND meta_value = %s", $id));
		// Delete all tasks for this project
		$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}improveseo_bulktasksdetails WHERE bulktask_id = %d", $id));
		$model->delete($id);
		FlashMessage::success('Project, all posts/pages, and all tasks deleted.');
		wp_redirect(admin_url('admin.php?page=improveseo_bulkprojects'));
		exit;

	elseif ($action == 'delete_posts'):
		$id = $_GET['id'];
		// Delete all posts from this project
		$wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->prefix . "postmeta WHERE post_id IN (SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = 'improveseo_project_id' AND meta_value = %s) AND meta_key = 'improveseo_channel'", $id));
		$wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->prefix . "posts WHERE ID IN (SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = 'improveseo_project_id' AND meta_value = %s)", $id));
		$wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->prefix . "postmeta WHERE meta_key = 'improveseo_project_id' AND meta_value = %s", $id));
		$model->update(array('iteration' => 0), $id);
		FlashMessage::success('All posts/pages deleted.');
		wp_redirect(admin_url('admin.php?page=improveseo_projects'));
		exit;

	elseif ($action == 'stop'):
		$id = $_GET['id'];
		$mainid = $_GET['mainid'];
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE `" . $detailsTaskModel->getTable() . "`
				SET status = %s, published_on = %s WHERE id = %d",
				'Stoped',
				'0000-00-00 00:00:00',
				$id
			)
		);

		$wpdb->last_error;
		FlashMessage::success('Task has been canceled. Content generation has been halted.');
		wp_redirect(admin_url('admin.php?page=improveseo_bulkprojects&action=viewAllTasks&id=' . $mainid));
		exit;

	elseif ($action == 'stop_bulk_task'):
		// Stop entire bulk task (from main bulkprojects list page)
		$id = intval($_GET['id']);
		
		// Update the bulk task's state to 'Stopped' using wpdb->update (same pattern as Finished status)
		$wpdb->update(
			$wpdb->prefix . 'improveseo_bulktasks',
			array('state' => 'Stopped'),
			array('id' => $id),
			array('%s'),
			array('%d')
		);
		
		// Cancel ALL unpublished tasks:
		// 1. Tasks still generating content (status != 'Done')
		// 2. Tasks with content ready but not yet published (status = 'Done' AND post_id IS NULL)
		// This prevents orphaned scheduled posts that can't be published because parent is stopped
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE `" . $wpdb->prefix . "improveseo_bulktasksdetails`
				SET status = %s 
				WHERE bulktask_id = %d 
				AND (state != %s OR post_id IS NULL)",
				'Stoped',
				$id,
				'Published'
			)
		);
		
		FlashMessage::success('Project has been canceled. All ongoing and scheduled tasks have been halted.');
		wp_redirect(admin_url('admin.php?page=improveseo_bulkprojects'));
		exit;

	elseif ($action == 'publish'):
		$id = $_GET['id'];
		$mainid = sanitize_title_with_dashes($_GET['mainid']);
		global $wpdb;
		$sql = "SELECT * FROM `" . $wpdb->prefix . "improveseo_bulktasksdetails` WHERE `id`=" . $id;
		$Bulktasks = $wpdb->get_results($sql);
		$content = '';
		foreach ($Bulktasks as $key => $value) {
			// short code
			if (!empty($value->testimonial)) {
				$testimonial_ids = '';
				$all_testimonial = explode("||", $value->testimonial);
				foreach ($all_testimonial as $key1 => $value1) {
					if (!empty($value1)) {
						$testimonial_ids = $value1 . ',' . $testimonial_ids;
					}
				}
				$content = $content . '<p>[improveseo_testimonial id="' . $testimonial_ids . '"]</p>';
			}
			if (!empty($value->Button_SC)) {
				$content = $content . '<p>[improveseo_buttons id="' . $value->Button_SC . '"]</p>';
			}
			if (!empty($value->GoogleMap_SC)) {
				$content = $content . '<p>[improveseo_googlemaps id="' . $value->GoogleMap_SC . '"]</p>';
			}
			if (!empty($value->Video_SC)) {
				$content = $content . '<p style="width:100%">[improveseo_video id="' . $value->Video_SC . '"]</p>';
			}
			$catids = [];
			if (!empty($value->cats)) {
				$categories = explode("||", $value->cats);
				foreach ($categories as $ckey => $cvalue) {
					if (!empty($cvalue)) {
						array_push($catids, $cvalue);
					}
				}
			} else {
				$categories = '';
			}
			$tags = array('-');
			
			// Validate and decode image URL before using it (same as cron job)
			$image_html = '';
			if (!empty($value->ai_image)) {
				$decoded_image = base64_decode($value->ai_image);
				// Validate that decoded image is a proper URL
				if (filter_var($decoded_image, FILTER_VALIDATE_URL)) {
					$image_html = "<img src='" . esc_url($decoded_image) . "' style='width:100%; margin-bottom: 100px;' alt='" . esc_attr($value->ai_title) . "'>";
					my_plugin_log('Publish action: Valid image URL found for task ' . $value->id);
				} else {
					my_plugin_log('Publish action: Invalid or missing image URL for task ' . $value->id . ', skipping image');
				}
			} else {
				my_plugin_log('Publish action: No image data for task ' . $value->id);
			}
			
			// Assemble content with optional image
			$fullcontent = $image_html . base64_decode($value->ai_content) . $content;
			
			$post_date = date('Y-m-d H:i:s');
			$post_status = 'publish';
			if ($value->assigning_authors == 'assigning_authors') {
				$post_author = $value->assigning_authors_value;
			}
			if ($value->assigning_authors == 'assigning_multi_authors') {
				$first_names = array('John', 'Jane', 'Michael', 'Emily', 'David', 'Sarah', 'James', 'Linda', 'Robert', 'Jessica', 'Daniel', 'Laura', 'Chris', 'Amy', 'Mark', 'Angela', 'Steven', 'Megan', 'Paul', 'Rachel', 'Peter', 'Hannah', 'Kevin', 'Sophia', 'Edward', 'Emma', 'Jason', 'Grace', 'Tom', 'Alice'); // Add more names as needed to increase uniqueness
				$last_names = array('Smith', 'Johnson', 'Brown', 'Williams', 'Jones', 'Miller', 'Davis', 'Garcia', 'Martinez', 'Taylor', 'Wilson', 'Moore', 'Anderson', 'Thomas', 'Jackson', 'White', 'Harris', 'Martin', 'Thompson', 'Lopez', 'Gonzalez', 'Clark', 'Lewis', 'Walker', 'Hall', 'Allen', 'Young', 'King', 'Wright', 'Scott');
				// Pick a random first and last name
				$first_name = $first_names[array_rand($first_names)];
				$last_name = $last_names[array_rand($last_names)];
				// Combine to create a full name
				$fullname = array('first_name' => $first_name, 'Last_name' => $last_name);
				$first_name = $fullname['first_name'];
				$last_name = $fullname['Last_name'];
				$username = str_replace(" ", "", $fullname);
				// Check if the username already exists
				if (username_exists($username) || email_exists($first_name . '@example.com')) {
					my_plugin_log('author recreate : ' . $username);
					$first_name = $first_names[array_rand($first_names)];
					$last_name = $last_names[array_rand($last_names)];
					$username = str_replace(" ", "", $first_name . $last_name);
				}
				// Define user information
				$user_data = array(
					'user_login' => $username,
					'user_pass' => 'PASssword123@4jkkhk$qwrfg123',
					'user_email' => $first_name . '@example.com',
					'first_name' => $first_name,
					'last_name' => $last_name,
					'role' => 'author',
				);
				my_plugin_log('author created : ' . $username);
				// Create the user
				$post_author = wp_insert_user($user_data);
			}
			if (!empty($value->post_id)) {
				$post_array = array(
					'ID' => $value->post_id,
					'post_author' => $post_author,
					'post_content' => $fullcontent,
					'post_title' => $value->ai_title,
					'comment_status' => 'closed',
					'ping_status' => 'closed',
					'post_type' => "post",
					'post_date' => $post_date,
					'post_status' => $post_status
				);
				wp_update_post($post_array);
				$post_id = $value->post_id;
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
			wp_set_post_tags($post_id, '-');
			if ((!empty($catids))) {
				wp_set_post_categories($post_id, $catids, false);
			}
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE `" . $wpdb->prefix . "improveseo_bulktasksdetails`
					SET state = %s, post_id = %d WHERE id = %d",
					'Published',  // Use 'Published' (capital) for internal state
					$post_id,
					$value->id
				)
			);
			my_plugin_log('Published post ID: ' . $post_id . ' for task: ' . $value->id . ', state set to Published');
			
			// Sync parent project progress after publishing
			if (!empty($mainid)) {
				improveseo_sync_bulk_parent_progress((int) $mainid);
			}
			
// Check if all tasks have completed content generation
            $total_tasks = (int) $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}improveseo_bulktasksdetails WHERE bulktask_id = %d",
                $mainid
            ));
            $done_tasks = (int) $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}improveseo_bulktasksdetails WHERE bulktask_id = %d AND status = 'Done'",
                $mainid
            ));
            $canceled_tasks = (int) $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}improveseo_bulktasksdetails WHERE bulktask_id = %d AND status = 'Stoped'",
                $mainid
            ));
            
            // If all tasks have reached terminal status (Done or Canceled)
            if ($total_tasks > 0 && ($done_tasks + $canceled_tasks) >= $total_tasks) {
                // If ALL tasks are canceled, mark as Cancelled
                if ($canceled_tasks === $total_tasks) {
                    $wpdb->update(
                        $wpdb->prefix . 'improveseo_bulktasks',
                        array('state' => 'Cancelled'),
                        array('id' => $mainid),
                        array('%s'),
                        array('%d')
                    );
                } else {
                    // Otherwise at least some content generated - mark as Finished
                    $wpdb->update(
                        $wpdb->prefix . 'improveseo_bulktasks',
                        array('state' => 'Finished'),
                        array('id' => $mainid),
                        array('%s'),
                        array('%d')
                    );
                }
			}
			
			my_plugin_log('This is a log message : ' . $value->id);
			FlashMessage::success('Post hasb been published successfully.');
			wp_redirect(admin_url("admin.php?page=improveseo_bulkprojects&action=viewAllTasks&id={$mainid}"));
		}

	elseif ($action == 'export_urls'):
		// Clean all output buffers to prevent HTML from being sent
		while (ob_get_level()) {
			ob_end_clean();
		}
		
		$id = intval($_GET['id']);
		$project_name = isset($_GET['name']) ? sanitize_text_field($_GET['name']) : 'project-' . $id;
		$filename = sanitize_file_name($project_name) . '-urls.csv';
		
		@set_time_limit(0);
		
		// Get all tasks for this project with post data
		$tasks_data = $wpdb->get_results($wpdb->prepare(
			"SELECT btd.id, btd.post_id, btd.ai_title, btd.status, btd.state, btd.published_on, btd.content_lang
			FROM {$wpdb->prefix}improveseo_bulktasksdetails btd
			WHERE btd.bulktask_id = %d
			ORDER BY btd.id ASC",
			$id
		));
		
		// Set headers for download BEFORE any output
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		
		// Open output stream directly
		$output = fopen('php://output', 'w');
		
		// Add UTF-8 BOM for Excel compatibility
		fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
		
		// Add headers
		fputcsv($output, array('Title', 'URL', 'Status', 'Post Status', 'Published Date', 'Language'));
		
		// Add data rows
		foreach ($tasks_data as $task) {
			$url = '';
			$post_status = 'Not Published';
			
			// Get URL if post_id exists
			if (!empty($task->post_id)) {
				$url = get_permalink($task->post_id);
				$post = get_post($task->post_id);
				if ($post) {
					$post_status = ucfirst($post->post_status);
				}
			}
			
			// Determine content status
			$content_status = '';
			if ($task->status == 'Draft') {
				$content_status = 'Draft';
			} elseif ($task->status == 'Done') {
				$content_status = 'Done';
			} elseif ($task->status == 'Stoped') {
				$content_status = 'Canceled';
			} else {
				$content_status = $task->status;
			}
			
			fputcsv($output, array(
				$task->ai_title,
				$url,
				$content_status,
				$post_status,
				($task->status == 'Stoped' || $task->published_on == '0000-00-00 00:00:00' || empty($task->published_on)) ? 'N/A' : $task->published_on,
				$task->content_lang
			));
		}
		
		fclose($output);
		exit;

	elseif ($action == 'export_all_project'):
		$data = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}improveseo_tasks"));
		if (empty($data)) {
			wp_redirect(admin_url('admin.php?page=improveseo_projects'));
		}
		wt_load_templates('import-export.php');
		$exportRecords = new improveseo_import_export();
		$exportRecords->export($data, 'all-project');
		exit;

	elseif ($action == 'export_project'):
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

	elseif ($action == 'export_preview_url'):
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

	elseif ($action == 'duplicate'):
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

	elseif ($action == 'bulk-delete-all'):
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

	elseif ($action == 'bulk-delete-posts'):
		if (isset($_GET['project_ids'])) {
			$project_ids = [];
			$project_ids = $_GET['project_ids'];
			if (!empty($project_ids)) {
				foreach ($project_ids as $project_id) {
					// Check if there are any posts associated with this project_id
					$post_ids = $wpdb->get_col($wpdb->prepare(
						"SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = 'improveseo_project_id' AND meta_value = %s",
						$project_id
					));
					// If there are posts associated with this project_id, proceed with deletion
					if (!empty($post_ids)) {
						// Delete postmeta entries for 'improveseo_channel' associated with these posts
						$wpdb->query($wpdb->prepare(
							"DELETE FROM {$wpdb->prefix}postmeta WHERE post_id IN (" . implode(',', $post_ids) . ") AND meta_key = 'improveseo_channel'"
						));
						// Delete posts associated with this project_id
						$wpdb->query($wpdb->prepare(
							"DELETE FROM {$wpdb->prefix}posts WHERE ID IN (" . implode(',', $post_ids) . ")"
						));
						// Delete postmeta entries for 'improveseo_project_id' associated with this project_id
						$wpdb->query($wpdb->prepare(
							"DELETE FROM {$wpdb->prefix}postmeta WHERE meta_key = 'improveseo_project_id' AND meta_value = %s",
							$project_id
						));
					}
				}
				FlashMessage::success('All posts/pages deleted.');
			}
		}
		wp_redirect(admin_url('admin.php?page=improveseo_projects'));
		exit;

	elseif ($action == 'bulk-empty'):
		FlashMessage::message('Please select an option from bulk actions', 'error');
		wp_redirect(admin_url('admin.php?page=improveseo_projects'));
		exit;

	endif;
}