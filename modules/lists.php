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
			// Matches the list's name OR any keyword inside it — people remember a list by what
			// is in it as often as by what they called it. esc_like() so a "%" or "_" typed into
			// the search is looked for literally instead of acting as a wildcard.
			$like = '%' . $wpdb->esc_like( $s ) . '%';
			$sql .= " WHERE name LIKE %s OR list LIKE %s";
			$sqlTotal .= " WHERE name LIKE %s OR list LIKE %s";
			$params[] = $like;
			$params[] = $like;
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
		$all = (int) $model->count();
		$usage = improveseo_keyword_lists_usage( (array) $lists );
		View::render('lists.index', compact('lists', 'total', 'all', 'order', 'orderBy', 'pages', 'page', 's', 'usage'));

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





		// The old message told people to "call @list:name" in a post title — instructions for the
		// shortcode era, and at odds with a field now labelled "Keyword List Name". Lists are
		// picked by name in the Bulk wizard now. The name shown is the one actually stored
		// (setNameAttribute() lowercases and hyphenates it), so it matches the list screen.
		FlashMessage::success(
			sprintf(
				'Keyword list "%s" has been created.',
				esc_html( $model->setNameAttribute( sanitize_text_field( wp_unslash( $_POST['name'] ) ) ) )
			)
		);


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

/**
 * How each keyword list on the current page has been used by Bulk projects.
 *
 * Built from data the plugin already records — no schema change:
 *   - improveseo_bulktasks          one row per Bulk project (name, state, created_at)
 *   - improveseo_bulktasksdetails   one row per keyword of a project; keyword_list_name holds the
 *                                   ID of the list the wizard picked (older rows hold the list's
 *                                   NAME instead — the same two shapes modules/bulkprojects.php
 *                                   already resolves), status 'Stoped' marks a cancelled task,
 *                                   post_id the WordPress post it produced
 *   - wp_posts.post_status          whether that post is actually live. Read from the post rather
 *                                   than the task's own state, because a draft can be published
 *                                   by hand later and the task row would never know.
 *
 * When a list fed several projects, the MOST RECENT one decides the status, the link and the
 * "last used" date; `more` counts the others.
 *
 * Status of that project, first match wins:
 *   partial      the project was cancelled (state Stopped/Cancelled) or any of its tasks was
 *   published    at least one of its posts is published or scheduled
 *   unpublished  used, but nothing live yet — drafts, or content still being generated
 *   unused       no project has used the list
 *
 * @param object[] $lists Rows of improveseo_lists (id, name, …) — normally one page of the screen.
 * @return array<int, object> list id => { status, label, project_id, project_name, last_used, more }
 */
function improveseo_keyword_lists_usage( array $lists ) {
	global $wpdb;

	$labels = array(
		'unused'      => 'Not used yet',
		'published'   => 'Used & published',
		'unpublished' => 'Used & not published',
		'partial'     => 'Partially used',
	);

	$usage   = array();
	$by_name = array();
	$refs    = array();

	foreach ( $lists as $list ) {
		$id = (int) $list->id;
		$usage[ $id ] = (object) array(
			'status'       => 'unused',
			'label'        => $labels['unused'],
			'project_id'   => 0,
			'project_name' => '',
			'last_used'    => '',
			'more'         => 0,
		);
		$refs[] = (string) $id;
		if ( '' !== (string) $list->name && ! isset( $by_name[ $list->name ] ) ) {
			$by_name[ $list->name ] = $id;
			$refs[] = (string) $list->name;
		}
	}

	if ( ! $refs ) {
		return $usage;
	}

	$placeholders = implode( ', ', array_fill( 0, count( $refs ), '%s' ) );

	// One grouped query for the whole page: per (list reference, project) — the project's state
	// and date, how many of its tasks were cancelled, and how many of its posts are live.
	$rows = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- plugin-owned tables, read once per screen load.
		$wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table names from $wpdb; the IN list is a run of %s placeholders built above.
			"SELECT d.keyword_list_name AS ref, d.bulktask_id AS project_id, b.name AS project_name,
			        b.state AS project_state, b.created_at AS project_created,
			        SUM( d.status = 'Stoped' ) AS cancelled_tasks,
			        SUM( p.post_status IN ( 'publish', 'future' ) ) AS live_posts
			   FROM {$wpdb->prefix}improveseo_bulktasksdetails d
			   JOIN {$wpdb->prefix}improveseo_bulktasks b ON b.id = d.bulktask_id
			   LEFT JOIN {$wpdb->posts} p ON p.ID = d.post_id
			  WHERE d.keyword_list_name IN ( $placeholders )
			  GROUP BY d.keyword_list_name, d.bulktask_id, b.name, b.state, b.created_at",
			$refs
		)
	);

	// Group the projects under the list each one used.
	$projects = array();
	foreach ( (array) $rows as $row ) {
		$ref = (string) $row->ref;
		if ( ctype_digit( $ref ) && isset( $usage[ (int) $ref ] ) ) {
			$list_id = (int) $ref;
		} elseif ( isset( $by_name[ $ref ] ) ) {
			$list_id = $by_name[ $ref ];
		} else {
			continue;
		}
		// Keyed by project, so a project whose rows carry both shapes of reference counts once.
		$pid = (int) $row->project_id;
		if ( isset( $projects[ $list_id ][ $pid ] ) ) {
			$projects[ $list_id ][ $pid ]->cancelled_tasks += (int) $row->cancelled_tasks;
			$projects[ $list_id ][ $pid ]->live_posts      += (int) $row->live_posts;
		} else {
			$row->cancelled_tasks = (int) $row->cancelled_tasks;
			$row->live_posts      = (int) $row->live_posts;
			$projects[ $list_id ][ $pid ] = $row;
		}
	}

	foreach ( $projects as $list_id => $list_projects ) {
		usort( $list_projects, function ( $a, $b ) {
			$by_date = strcmp( (string) $b->project_created, (string) $a->project_created );
			return 0 !== $by_date ? $by_date : ( (int) $b->project_id - (int) $a->project_id );
		} );
		$latest = $list_projects[0];

		if ( in_array( $latest->project_state, array( 'Stopped', 'Cancelled' ), true ) || $latest->cancelled_tasks > 0 ) {
			$status = 'partial';
		} elseif ( $latest->live_posts > 0 ) {
			$status = 'published';
		} else {
			$status = 'unpublished';
		}

		$usage[ $list_id ] = (object) array(
			'status'       => $status,
			'label'        => $labels[ $status ],
			'project_id'   => (int) $latest->project_id,
			'project_name' => (string) $latest->project_name,
			'last_used'    => (string) $latest->project_created,
			'more'         => count( $list_projects ) - 1,
		);
	}

	return $usage;
}


