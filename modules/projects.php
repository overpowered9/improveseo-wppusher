<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


use ImproveSEO\View;
use ImproveSEO\Validator;
use ImproveSEO\Models\Task;
use ImproveSEO\FlashMessage;

function improveseo_projects()
{
  global $wpdb;
  $action = isset($_GET['action']) ? $_GET['action'] : 'index';
  $limit = isset($_GET['limit']) ? $_GET['limit'] : 20;
  $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
  $offset = ($paged - 1) * $limit;

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
    $file = fopen($_FILES['upload_csv']['tmp_name'], "r"); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- streaming fgetcsv() over an uploaded temp file; WP_Filesystem would have to read the whole upload into memory first

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

    fclose($file); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- streaming fgetcsv() over an uploaded temp file; WP_Filesystem would have to read the whole upload into memory first

    FlashMessage::success($counter . ' Project Imported Successfully.');
  }

  if (isset($_POST['action']) && $_POST['action'] === 'bulk_delete') {
    if (!isset($_POST['bulk_delete_nonce']) || !wp_verify_nonce($_POST['bulk_delete_nonce'], 'bulk_delete_projects')) {
      FlashMessage::error('Security check failed.');
      wp_redirect(admin_url('admin.php?page=improveseo_projects'));
      exit();
    }

    if (!current_user_can('delete_posts')) {
      FlashMessage::error('You do not have permission to delete projects.');
      wp_redirect(admin_url('admin.php?page=improveseo_projects'));
      exit();
    }

    if (empty($_POST['project_ids']) || !is_array($_POST['project_ids'])) {
      FlashMessage::error('No projects selected for deletion.');
      wp_redirect(admin_url('admin.php?page=improveseo_projects'));
      exit();
    }

    $project_ids = array_map('intval', $_POST['project_ids']);
    $deleted_count = 0;

    foreach ($project_ids as $project_id) {
      $result = $wpdb->delete(
        $wpdb->prefix . 'improveseo_tasks',
        array('id' => $project_id),
        array('%d')
      );

      if ($result !== false) {
        $deleted_count++;

        $post_id = $wpdb->get_var($wpdb->prepare(
          "SELECT post_id FROM {$wpdb->postmeta} 
                 WHERE meta_key = 'improveseo_project_id' 
                 AND meta_value = %s",
          $project_id
        ));

        if ($post_id) {
          wp_delete_post($post_id, true);
        }
      }
    }

    if ($deleted_count > 0) {
      FlashMessage::success("{$deleted_count} project(s) deleted successfully.");
    } else {
      FlashMessage::error('Failed to delete projects.');
    }

    $_GET['action'] = 'index';
    $action = 'index';
  }

  if ($action == 'index'):
    // Filters
    $allowed_order_by = array('created_at', 'name');
    $orderBy = (isset($_GET['orderBy']) && in_array($_GET['orderBy'], $allowed_order_by)) ? $_GET['orderBy'] : 'created_at';
    $order   = (isset($_GET['order']) && in_array(strtoupper($_GET['order']), array('ASC', 'DESC'))) ? strtoupper($_GET['order']) : 'DESC';
    $search  = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

    $highlight = isset($_GET['highlight']) ? $_GET['highlight'] : null;

    $where  = array();
    $params = array();

    // Hide transient preview projects from the listing — they are short-lived
    // drafts cleaned up automatically and should never appear as real projects.
    $where[] = "state <> 'Preview'";

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
      $sqlTotal = $wpdb->prepare($sqlTotal, $params); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- table name comes from AbstractModel::getTable(), which builds it from $wpdb->prefix and the class name; every user value is bound.
    }

    $sql .= " ORDER BY $orderBy $order";
    $sql .= " LIMIT %d, %d";

    $params[] = $offset;
    $params[] = $limit;

    $sql = $wpdb->prepare($sql, $params); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- table name comes from AbstractModel::getTable(), which builds it from $wpdb->prefix and the class name; every user value is bound.

    // Data
    $projects = $wpdb->get_results($sql); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter -- the query in this variable is prepared where it is built, above
    $total_row = $wpdb->get_row($sqlTotal); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter -- the query in this variable is prepared where it is built, above
    $total = $total_row->total;

    $pages = ceil($total / $limit);
    $page = $paged;

    View::render('projects.index', compact('projects', 'page', 'pages', 'order', 'orderBy', 'search', 'highlight', 'total', 'limit'));

  elseif ($action == 'delete'):
    $id = $_GET['id'];

    // Delete all posts from this project
    $wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->prefix . "posts WHERE ID IN (SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = 'improveseo_project_id' AND meta_value = %s)", $id));
    $wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->prefix . "postmeta WHERE meta_key = 'improveseo_project_id' AND meta_value = %s", $id));

    $model->delete($id);

    FlashMessage::success('Project and all posts/pages deleted.');
    wp_redirect(admin_url('admin.php?page=improveseo_projects'));
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

    $model->update(array('deleted_at' => '1970-01-01 11:11:11'), $id);

    FlashMessage::success('Project stopped. You can continue process by clicking Build posts');
    wp_redirect(admin_url('admin.php?page=improveseo_projects'));
    exit;





  elseif ($action == 'export_urls'):
    $id = $_GET['id'];
    $project_name = sanitize_title_with_dashes($_GET['name']);

    @set_time_limit(0);

    $urls = "";
    $posts = $wpdb->get_results($wpdb->prepare("SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = 'improveseo_project_id' AND meta_value = %s", $id));
    foreach ($posts as $post) {
      $urls .= get_permalink($post->post_id) . "\r\n";
    }

    // Streamed straight from memory. This used to file_put_contents() the list to
    // "$project_name.txt" and then readfile() it back. That path is RELATIVE, so it
    // resolved against the process working directory - the WordPress root - and the
    // file was never deleted, so every export left a .txt behind. The content is
    // already in $urls, so no file needs to exist at all.
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=' . $project_name . '.txt');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . strlen($urls));
    echo $urls; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- plain-text file download, not HTML; escaping would corrupt the URLs
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

    $fh = @fopen('php://output', 'w'); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- PHP output stream for a file download, not a filesystem path; WP_Filesystem has no equivalent

    fprintf($fh, chr(0xEF) . chr(0xBB) . chr(0xBF));
    fputcsv($fh, $header_row);
    fputcsv($fh, $data_row);
    fclose($fh); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- PHP output stream for a file download, not a filesystem path; WP_Filesystem has no equivalent

    exit;

  elseif ($action == 'export_preview_url'):
    $id = intval($_GET['id']);

    @set_time_limit(0);

    $post_ids = $wpdb->get_col($wpdb->prepare(
      "SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = 'improveseo_project_id' AND meta_value = %s",
      $id
    ));

    // No preview post was generated (still building, or already swept). Fail
    // gracefully instead of fataling on array_rand() with an empty array.
    if (empty($post_ids)) {
      wp_die(
        'The preview is still being generated or has already expired. Please close this window and click "Post preview" again. Previews are automatically removed after 30 minutes.',
        'Preview not ready',
        array('response' => 200, 'back_link' => false)
      );
    }

    $preview_post_id = $post_ids[array_rand($post_ids)];

    // Preview posts are drafts, so they are only viewable through WordPress'
    // nonce'd preview link — shown exactly as they would look, never published.
    $preview_url = get_preview_post_link($preview_post_id, array('id' => $id));

    header("location: " . $preview_url);
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

  elseif ($action == 'bulk-empty'):
    FlashMessage::message('Please select an option from bulk actions', 'error');
    wp_redirect(admin_url('admin.php?page=improveseo_projects'));
    exit;

  elseif ($action == 'view_details'):
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if (!$id) {
      FlashMessage::error('Invalid project ID.');
      wp_redirect(admin_url('admin.php?page=improveseo_projects'));
      exit;
    }

    $project = $model->find($id);

    if (!$project) {
      FlashMessage::error('Project not found.');
      wp_redirect(admin_url('admin.php?page=improveseo_projects'));
      exit;
    }

    // $project->options and $project->content are already decoded by the Task model (array|b64 cast)
    $options = is_array($project->options) ? $project->options : array();
    $content = is_array($project->content) ? $project->content : array();

    // Get associated WordPress post
    $associated_post = null;
    $post_url = '';
    $post_id_result = $wpdb->get_var($wpdb->prepare(
      "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'improveseo_project_id' AND meta_value = %s",
      $id
    ));
    if ($post_id_result) {
      $associated_post = get_post($post_id_result);
      $post_url = get_permalink($post_id_result);
    }

    View::render('projects.project-details', compact('project', 'options', 'content', 'associated_post', 'post_url'));

  endif;
}


