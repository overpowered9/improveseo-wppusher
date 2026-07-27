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
		$allowed_order_by = array('created_at', 'name');
		$orderBy   = (isset($_GET['orderBy']) && in_array($_GET['orderBy'], $allowed_order_by)) ? $_GET['orderBy'] : 'created_at';
		$order     = (isset($_GET['order']) && in_array(strtoupper($_GET['order']), array('ASC','DESC'))) ? strtoupper($_GET['order']) : 'DESC';
		$search    = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
		$highlight = isset($_GET['highlight']) ? $_GET['highlight'] : null;

		$where  = array();
		$params = array();

		if ($search !== '') {
			$where[]  = 'name LIKE %s';
			$params[] = '%' . $wpdb->esc_like($search) . '%';
		}

		$sql = 'SELECT * FROM ' . $model->getTable();
		if (sizeof($where)) {
			$sql .= ' WHERE ' . implode(' AND ', $where);
		}
		$sqlTotal = 'SELECT COUNT(id) AS total FROM ' . $model->getTable();
		if (sizeof($where)) {
			$sqlTotal .= ' WHERE ' . implode(' AND ', $where);
		}

		if ($params) {
			$sqlTotal = $wpdb->prepare($sqlTotal, $params);
		}

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
		View::render('bulkprojects.index', compact('projects', 'page', 'pages', 'total', 'order', 'orderBy', 'search', 'highlight'));

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
		$id      = isset($_GET['id'])      ? intval($_GET['id'])                                    : '';
		$orderBy = isset($_GET['orderBy']) ? $_GET['orderBy']                                       : 'created_at';
		$order   = isset($_GET['order'])   ? strtoupper($_GET['order'])                             : 'DESC';
		$search  = isset($_GET['search'])  ? sanitize_text_field($_GET['search'])                   : '';
		$highlight = isset($_GET['highlight']) ? $_GET['highlight']                                 : null;

		$allowed_order_by = array('created_at', 'keyword_name', 'status');
		if (!in_array($orderBy, $allowed_order_by)) $orderBy = 'created_at';
		if (!in_array($order, array('ASC', 'DESC')))  $order   = 'DESC';

		$params = array();
		$where_sql = '`bulktask_id` = %d';
		$params[] = $id;

		if ($search !== '') {
			$where_sql .= ' AND `keyword_name` LIKE %s';
			$params[] = '%' . $wpdb->esc_like($search) . '%';
		}

		$sql      = 'SELECT * FROM ' . $detailsTaskModel->getTable() . ' WHERE ' . $where_sql;
		$sqlTotal = 'SELECT COUNT(id) AS total FROM ' . $detailsTaskModel->getTable() . ' WHERE ' . $where_sql;
		$sqlTotal = $wpdb->prepare($sqlTotal, $params);
		$sql     .= " ORDER BY $orderBy $order LIMIT %d, %d";
		$params[] = $offset;
		$params[] = $limit;
		$sql = $wpdb->prepare($sql, $params);

		// Data
		$projects = $wpdb->get_results($sql);
		$total_row = $wpdb->get_row($sqlTotal);
		$total = $total_row->total;
		$pages = ceil($total / $limit);
		$page = floor($offset / $limit) + 1;
		View::render('bulkprojects.alltasks', compact('projects', 'project_name', 'id', 'page', 'pages', 'total', 'order', 'orderBy', 'search', 'highlight'));

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

		// Draft-only: the preview exists to review content BEFORE it goes live.
		// Published/Scheduled tasks have a real post — use View Post instead.
		// (The list already hides the link for non-drafts; this enforces it.)
		if (empty($projects) || $projects[0]->state !== 'Draft') {
			FlashMessage::message('View AI content is only available for draft posts. Use View Post for published or scheduled posts.', 'error');
			$back = !empty($projects) && !empty($projects[0]->bulktask_id)
				? 'admin.php?page=improveseo_bulkprojects&action=viewAllTasks&id=' . intval($projects[0]->bulktask_id)
				: 'admin.php?page=improveseo_bulkprojects';
			wp_redirect(admin_url($back));
			exit;
		}

		$total = count($projects);
		$pages = 1;
		$page = 1;
		View::render('bulkprojects.viewAIContent', compact('projects', 'id', 'page', 'pages', 'order', 'orderBy', 'highlight'));

	elseif ($action == 'edit_ai_content'):
		// Drafts never get a WordPress post (the post-creation query in
		// saveContentInTaskList only picks up Scheduled/Published), so the
		// generated content is the only thing there is to edit until publish.
		$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

		$task = $wpdb->get_row($wpdb->prepare(
			"SELECT * FROM `{$wpdb->prefix}improveseo_bulktasksdetails` WHERE id = %d",
			$id
		));

		if (!$task) {
			wp_die('Task not found.');
		}

		// Once a post exists it is the source of truth — edit it in WordPress.
		if (!empty($task->post_id) && get_post($task->post_id)) {
			wp_die('This task already has a WordPress post. Edit it in the WordPress editor instead.');
		}

		// The screen shows which project this draft belongs to, so resolve the
		// parent's name here rather than querying from the view.
		$project_name = '';
		if (!empty($task->bulktask_id)) {
			$project_name = (string) $wpdb->get_var($wpdb->prepare(
				"SELECT name FROM " . $model->getTable() . " WHERE id = %d",
				$task->bulktask_id
			));
		}

		View::render('bulkprojects.edit-ai-content', compact('task', 'project_name'));

	elseif ($action == 'save_ai_content'):
		$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

		if (!current_user_can('manage_options')) {
			wp_die('You are not allowed to edit this content.');
		}

		check_admin_referer('improveseo_save_ai_content_' . $id);

		$task = $wpdb->get_row($wpdb->prepare(
			"SELECT bulktask_id FROM `{$wpdb->prefix}improveseo_bulktasksdetails` WHERE id = %d",
			$id
		));

		if (!$task) {
			wp_die('Task not found.');
		}

		// Content only: state and status are deliberately left alone, so saving a
		// draft never publishes it or re-enters the generation queue.
		$wpdb->update(
			$wpdb->prefix . 'improveseo_bulktasksdetails',
			array(
				'ai_title'   => sanitize_text_field(wp_unslash(isset($_POST['ai_title']) ? $_POST['ai_title'] : '')),
				'ai_content' => base64_encode(wp_kses_post(wp_unslash(isset($_POST['ai_content']) ? $_POST['ai_content'] : ''))),
				'updated_at' => current_time('mysql'),
			),
			array('id' => $id),
			array('%s', '%s', '%s'),
			array('%d')
		);

		// The screen's Publish button submits this same form with publish=1 so the
		// user's edits are never lost by publishing. Publishing itself is NOT
		// reimplemented here — we hand off to the existing publish action, the
		// same one the list's Publish menu item uses, which is what writes
		// state/post_id/published_on.
		if (!empty($_POST['publish'])) {
			wp_redirect(admin_url(
				'admin.php?page=improveseo_bulkprojects&action=publish'
				. '&mainid=' . intval($task->bulktask_id)
				. '&id=' . $id
			));
			exit;
		}

		FlashMessage::success('Content saved. The task is still a draft — use Publish when you are ready.');
		wp_redirect(admin_url('admin.php?page=improveseo_bulkprojects&action=viewAllTasks&id=' . $task->bulktask_id . '&highlight=' . $id));
		exit;

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
		// Only cancel tasks that have no WordPress post yet — once a post exists
		// (scheduled or published) its status is real and must not be overwritten.
		$stopped = $wpdb->query(
			$wpdb->prepare(
				"UPDATE `" . $detailsTaskModel->getTable() . "`
				SET status = %s, published_on = %s WHERE id = %d AND post_id IS NULL",
				'Stoped',
				'',
				$id
			)
		);

		if ($stopped) {
			FlashMessage::success('Task has been canceled. Content generation has been halted.');
		} else {
			FlashMessage::message('This task already has a WordPress post and cannot be canceled.', 'error');
		}
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
		
		// Cancel ONLY tasks that were not already generated into a WordPress post:
		// a task with a post_id (published or scheduled draft) is real content and
		// must keep its actual status — cancelling the project stops future work,
		// it does not un-happen posts that already exist. Tasks whose content was
		// generated but that have no post yet are still cancelled, which prevents
		// orphaned rows from publishing later (the cron also refuses to create
		// posts while the parent is Stopped).
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE `" . $wpdb->prefix . "improveseo_bulktasksdetails`
				SET status = %s
				WHERE bulktask_id = %d
				AND post_id IS NULL",
				'Stoped',
				$id
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
		foreach ($Bulktasks as $key => $value) {
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

			// Assemble the post body through the shared bulk renderer (hero image +
			// h1-deduped article + shortcode blocks) — same output the preview shows.
			$built = improveseo_bulk_build_post_content($value);
			$fullcontent = $built['html'];
			$decoded_image = $built['image'];
			if ($decoded_image === '') {
				my_plugin_log('Publish action: Invalid or missing image URL for task ' . $value->id . ', publishing without hero image');
			}

			// Strip any leading "Title:"-style label the model prepended. New rows are
			// cleaned at generation time; this covers rows generated before that fix,
			// whose stored ai_title would otherwise become post_title and the permalink.
			$post_title = improveseo_normalize_generated_title($value->ai_title);
			if ($post_title === '') {
				$post_title = $value->keyword_name;
			}

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
					'post_title' => $post_title,
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
					'post_title' => $post_title,
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

			// ── Featured image (bulk, manual publish) ───────────────────────
			// This path publishes drafts (and creates missing posts) from the UI;
			// it must honour the featured-image toggles like every other path.
			try {
				if (
					improveseo_featured_images_enabled_for( 'bulk' ) &&
					! empty( $decoded_image ) &&
					! has_post_thumbnail( $post_id )
				) {
					improveseo_set_featured_image_from_url( $post_id, $decoded_image, $post_title );
				}
			} catch ( \Throwable $e ) {
				error_log( 'improveseo featured-image (bulk manual publish) failed: ' . $e->getMessage() );
			}
			// ────────────────────────────────────────────────────────────────
			// Record the actual publish datetime (site timezone). Creation only writes
			// published_on for the direct/scheduled paths; drafts published from the
			// UI (this path) otherwise stay empty forever and the list shows N/A.
			improveseo_ensure_bulk_published_on_column();
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE `" . $wpdb->prefix . "improveseo_bulktasksdetails`
					SET state = %s, post_id = %d, published_on = %s, ai_title = %s WHERE id = %d",
					'Published',  // Use 'Published' (capital) for internal state
					$post_id,
					current_time('mysql'),
					// Persist the cleaned title so the row matches the post it just
					// created — otherwise the list keeps showing "Title: …".
					$post_title,
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

	elseif ($action == 'view_task_details'):
		$task_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
		$parent_id = isset($_GET['parent_id']) ? intval($_GET['parent_id']) : 0;

		if (!$task_id) {
			FlashMessage::error('Invalid task ID.');
			wp_redirect(admin_url('admin.php?page=improveseo_bulkprojects'));
			exit;
		}

		// Fetch the bulk task detail row
		$sql = $wpdb->prepare("SELECT * FROM " . $detailsTaskModel->getTable() . " WHERE id = %d", $task_id);
		$task = $wpdb->get_row($sql);

		if (!$task) {
			FlashMessage::error('Task not found.');
			wp_redirect(admin_url('admin.php?page=improveseo_bulkprojects'));
			exit;
		}

		// Get parent project name
		$parent_name = '';
		if ($task->bulktask_id) {
			$parent = $wpdb->get_row($wpdb->prepare("SELECT name FROM " . $model->getTable() . " WHERE id = %d", $task->bulktask_id));
			if ($parent) {
				$parent_name = $parent->name;
			}
		}

		// keyword_list_name actually stores the improveseo_lists ID the user picked
		// in the create form (the <select> option value is the list id) — resolve it
		// to the list's real name for display. Older rows that stored a name pass through.
		$keyword_list_label = '';
		if (!empty($task->keyword_list_name)) {
			if (ctype_digit((string) $task->keyword_list_name)) {
				$list_name = $wpdb->get_var($wpdb->prepare(
					"SELECT name FROM {$wpdb->prefix}improveseo_lists WHERE id = %d",
					$task->keyword_list_name
				));
				$keyword_list_label = $list_name !== null
					? $list_name
					: 'List #' . intval($task->keyword_list_name) . ' (deleted)';
			} else {
				$keyword_list_label = $task->keyword_list_name;
			}
		}

		// Get associated WordPress post
		$associated_post = null;
		$post_url = '';
		if (!empty($task->post_id)) {
			$associated_post = get_post($task->post_id);
			if ($associated_post && $associated_post->post_status !== 'trash') {
				$post_url = get_permalink($task->post_id);
			} else {
				$associated_post = null;
			}
		}

		View::render('bulkprojects.bulktask-details', compact('task', 'parent_name', 'associated_post', 'post_url', 'keyword_list_label'));

	endif;
}