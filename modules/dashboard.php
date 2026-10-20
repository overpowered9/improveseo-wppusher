<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}






use ImproveSEO\View;


use ImproveSEO\Spintax;





use ImproveSEO\Validator;


use ImproveSEO\LiteSpintax;





use ImproveSEO\Models\Task;
use ImproveSEO\Models\Bulktask;
use ImproveSEO\Models\Bulktasksdetail;


use ImproveSEO\FlashMessage;





/**
 * Dashboard "Content Metrics": every piece of content this plugin made, sorted into
 * Published / In Draft / Scheduled, plus the newest few and the full draft list.
 *
 * Most drafts and schedules here are not WordPress posts yet, so three sources are read,
 * one item per post or per not-yet-posted project/task:
 *
 * 1. WP posts the plugin built. Single (modules/builder.php) and the posting cron tag the
 *    post with the improveseo_project_id meta; Bulk (saveContentInTaskList() and the manual
 *    publish in modules/bulkprojects.php) sets no meta, the post is only referenced by
 *    improveseo_bulktasksdetails.post_id. Preview Post builds a real draft too (flagged
 *    _improveseo_preview, kept 30+ minutes and sometimes orphaned); it is not output.
 *    WordPress' own status decides the bucket, so a post published or edited by hand counts
 *    as what it is now. The exception is a scheduled Bulk post: it waits as a WP draft until
 *    the plugin's cron publishes it (bulk_AI_post_function.php, same conditions as below),
 *    so it counts as Scheduled. Single scheduling uses WP's own 'future' status.
 * 2. Single projects saved with "Save As Draft" (modules/posting.php): state 'Draft' and no
 *    post is built. The Single Projects list shows them as Draft ("Edit Draft Post").
 * 3. Bulk tasks with generated content but no post. "Save as Drafts" tasks (state 'Draft')
 *    never get a post until they are published from the project, and an input-wise schedule
 *    (state 'Scheduled') gets its draft post only after generation. Same gate as the task
 *    screen's Post Status (views/bulkprojects/bulktask-details.php): content generated
 *    (status 'Done'), not cancelled. A "publish now" task waiting for the cron is in flight,
 *    not scheduled, so it is not counted until its post exists.
 *
 * @param int $days Only items dated within the last $days days; 0 for all time. Scheduled
 *                  items always count: they are due in the future.
 * @return array{total:int, published:int, draft:int, scheduled:int, latest:object[], drafts:object[]}
 *               total is before the period filter, so the card can tell "nothing built yet"
 *               from "nothing in this period". Each item has title, status ('publish',
 *               'draft' or 'future'), date and url (where to open it).
 */
function improveseo_dashboard_content_metrics( $days ) {
	global $wpdb;

	$items = array();

	// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table names from $wpdb; no user input in these queries.

	// 1. WP posts the plugin built.
	$rows = (array) $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,PluginCheck.Security.DirectDB.UnescapedDBParameter -- plugin-owned tables, read once per Dashboard load.
		"SELECT p.ID, p.post_status, p.post_date, MAX( src.bulk_scheduled ) AS bulk_scheduled
		   FROM (
		         SELECT pm.post_id, 0 AS bulk_scheduled
		           FROM {$wpdb->postmeta} pm
		           LEFT JOIN {$wpdb->prefix}improveseo_tasks t ON t.id = pm.meta_value
		          WHERE pm.meta_key = 'improveseo_project_id'
		            AND ( t.state IS NULL OR t.state <> 'Preview' )
		         UNION ALL
		         SELECT d.post_id,
		                ( d.state = 'Scheduled' AND d.status = 'Done'
		                  AND b.state IN ( 'Processing', 'Finished', 'Unpublished' ) )
		           FROM {$wpdb->prefix}improveseo_bulktasksdetails d
		           JOIN {$wpdb->prefix}improveseo_bulktasks b ON b.id = d.bulktask_id
		          WHERE d.post_id > 0
		        ) src
		   JOIN {$wpdb->posts} p ON p.ID = src.post_id
		  WHERE p.post_status IN ( 'publish', 'draft', 'future' )
		    AND NOT EXISTS ( SELECT 1 FROM {$wpdb->postmeta} pv
		                      WHERE pv.post_id = p.ID AND pv.meta_key = '_improveseo_preview' )
		  GROUP BY p.ID, p.post_status, p.post_date"
	);
	foreach ( $rows as $row ) {
		$title   = get_the_title( $row->ID );
		$items[] = (object) array(
			'title'  => '' !== $title ? $title : '(no title)',
			'status' => ( 'draft' === $row->post_status && (int) $row->bulk_scheduled ) ? 'future' : $row->post_status,
			'date'   => $row->post_date,
			'url'    => (string) get_edit_post_link( $row->ID ),
		);
	}

	// 2. Single "Save As Draft" projects that have no real post.
	$rows = (array) $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,PluginCheck.Security.DirectDB.UnescapedDBParameter -- plugin-owned tables, read once per Dashboard load.
		"SELECT t.id, t.name, COALESCE( t.updated_at, t.created_at ) AS item_date
		   FROM {$wpdb->prefix}improveseo_tasks t
		  WHERE t.state = 'Draft'
		    AND NOT EXISTS ( SELECT 1
		                       FROM {$wpdb->postmeta} pm
		                       JOIN {$wpdb->posts} p ON p.ID = pm.post_id
		                      WHERE pm.meta_key = 'improveseo_project_id' AND pm.meta_value = t.id
		                        AND p.post_status IN ( 'publish', 'draft', 'future' )
		                        AND NOT EXISTS ( SELECT 1 FROM {$wpdb->postmeta} pv
		                                          WHERE pv.post_id = p.ID AND pv.meta_key = '_improveseo_preview' ) )"
	);
	foreach ( $rows as $row ) {
		$items[] = (object) array(
			'title'  => '' !== (string) $row->name ? $row->name : '(no title)',
			'status' => 'draft',
			'date'   => (string) $row->item_date,
			'url'    => admin_url( 'admin.php?page=improveseo_dashboard&action=edit_post&id=' . (int) $row->id ),
		);
	}

	// 3. Bulk tasks with generated content and no post yet: drafts, and input-wise schedules.
	$rows = (array) $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,PluginCheck.Security.DirectDB.UnescapedDBParameter -- plugin-owned tables, read once per Dashboard load.
		"SELECT d.bulktask_id, d.keyword_name, d.ai_title, d.state, d.published_on,
		        COALESCE( d.updated_at, d.created_at ) AS item_date
		   FROM {$wpdb->prefix}improveseo_bulktasksdetails d
		   JOIN {$wpdb->prefix}improveseo_bulktasks b ON b.id = d.bulktask_id
		  WHERE ( d.post_id IS NULL OR d.post_id = 0 )
		    AND d.status = 'Done'
		    AND b.state NOT IN ( 'Stopped', 'Cancelled' )
		    AND ( d.state = 'Draft'
		          OR ( d.state = 'Scheduled'
		               AND COALESCE( NULLIF( d.schedule_posts, '' ), b.schedule_posts ) = 'schedule_posts_input_wise' ) )"
	);
	foreach ( $rows as $row ) {
		$title = trim( str_replace( '"', '', (string) $row->ai_title ) );
		if ( function_exists( 'improveseo_normalize_generated_title' ) ) {
			$title = improveseo_normalize_generated_title( $title );
		}
		$scheduled = 'Scheduled' === $row->state;
		$items[]   = (object) array(
			'title'  => '' !== $title ? $title : (string) $row->keyword_name,
			'status' => $scheduled ? 'future' : 'draft',
			// A schedule sorts by when it goes out; a draft by when its content was made.
			'date'   => ( $scheduled && ! empty( $row->published_on ) ) ? (string) $row->published_on : (string) $row->item_date,
			'url'    => admin_url( 'admin.php?page=improveseo_bulkprojects&action=viewAllTasks&id=' . (int) $row->bulktask_id ),
		);
	}

	// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

	usort( $items, function ( $a, $b ) {
		return strcmp( $b->date, $a->date );
	} );

	$metrics = array(
		'total'     => count( $items ),
		'published' => 0,
		'draft'     => 0,
		'scheduled' => 0,
		'latest'    => array(),
		'drafts'    => array(),
	);

	// Dates are site-local time (post_date, current_time()), and wp_date() formats in the
	// site's timezone too.
	$cutoff = $days > 0 ? wp_date( 'Y-m-d H:i:s', time() - (int) $days * DAY_IN_SECONDS ) : '';

	foreach ( $items as $item ) {
		if ( '' !== $cutoff && 'future' !== $item->status && $item->date < $cutoff ) {
			continue;
		}

		if ( 'publish' === $item->status ) {
			$metrics['published']++;
		} elseif ( 'future' === $item->status ) {
			$metrics['scheduled']++;
		} else {
			$metrics['draft']++;
			$metrics['drafts'][] = $item;
		}

		if ( count( $metrics['latest'] ) < 3 ) {
			$metrics['latest'][] = $item;
		}
	}

	return $metrics;
}

function improveseo_dashboard() {


	global $wpdb;





	$action = isset($_GET['action']) ? $_GET['action'] : 'index';


	$model = new Task();





	if ($action == 'index'):


		View::render('dashboard.index');


	elseif($action=='do_create_post'):


		





		if (isset($_POST['create'])) {


			if (!Validator::validate($_POST, array(


				'name' => 'required',


				'title' => 'required',


				'content' => 'required',


				'post_type' => 'required|post_type',





				'max_posts' => 'numeric',





				// Dripfeed


				'dripfeed_x' => 'required_if:dripfeed_enabler|numeric',





				// Image EXIF


				//'exif_locations' => 'required_if:exif_enabler'





				// Channel


				'city_channel_title' => 'required_if:city_channel_page',


				'city_channel_content' => 'required_if:city_channel_page',


				'state_channel_title' => 'required_if:state_channel_page',


				'state_channel_content' => 'required_if:state_channel_page'


			)) && !isset($_POST['draft'])) {


				$ai_modal_type2 = isset($_POST['ai_modal_type']) ? $_POST['ai_modal_type'] : '';
				if ($ai_modal_type2 === 'single') {
					wp_redirect(admin_url('admin.php?page=improveseo_posting&action=create_post_single'));
				} elseif ($ai_modal_type2 === 'bulk') {
					wp_redirect(admin_url('admin.php?page=improveseo_posting&action=create_post_bulk'));
				} else {
					wp_redirect(admin_url('admin.php?page=improveseo_posting&action=create_post'));
				}
				exit;


			}


		}





		$name = $_POST['name'];
		// Preview rows are hidden, temporary copies (named like the project being previewed)
		// and must not count as a clash, or Save is refused after using Preview Post.
		if (isset($_GET['id']) && (!isset($_POST['ai_modal_type']) || $_POST['ai_modal_type'] !== 'bulk')) {
			$name_taken = $wpdb->get_var($wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}improveseo_tasks WHERE name = %s AND id != %d AND state <> 'Preview'",
				$name,
				intval($_GET['id'])
			));
			if ($name_taken) {
				FlashMessage::message('A project with this name already exists. Please choose a different name.', 'error');
				wp_redirect(admin_url('admin.php?page=improveseo_dashboard&action=edit_post&id=' . intval($_GET['id'])));
				exit;
			}
		}

		// Editing an existing single project (the "Edit Draft Post" form posts here with
		// its id): its name must not collide with another project's.
		if (isset($_GET['id']) && (!isset($_POST['ai_modal_type']) || $_POST['ai_modal_type'] !== 'bulk')) {
			improveseo_refuse_duplicate_project_name(wp_unslash($name), (int) $_GET['id'], false);
		} elseif (!isset($_GET['id']) && isset($_POST['ai_modal_type']) && $_POST['ai_modal_type'] === 'single') {
			// New project from the single wizard. Its Project Name step checks the name
			// before submitting; if a duplicate still arrives, keep the (already generated)
			// project and give it the first free "Name (n)" instead of rejecting it.
			$unique_name = improveseo_unique_single_project_name(wp_unslash($name));
			if ($unique_name !== trim(wp_unslash($name))) {
				$name = wp_slash($unique_name);
			}
		}


		$title = $_POST['title'];


		$content = $_POST['content'];





		// $order   = array("\r\n", "\n");


		// $replace   = array("\n", "\\\n");


		//


		// $content = str_replace($order, $replace, $content);


		//





		$post_type = $_POST['post_type'];





		$project_data = array(


			'title' => stripslashes($title),


			'content' => stripslashes($content),


			'post_type' => $post_type


		);





		$iterations = [


			// title


			Spintax::count(Spintax::parse($title)),


			// content


			Spintax::count(Spintax::parse($content))


		];





		$options_data = array();





		// On-Page SEO — stores whatever meta the form posted (the create forms no
		// longer render the legacy on_page_seo opt-in checkbox, so gating on it
		// dropped the wizard's Meta Title). See improveseo.php.
		improveseo_collect_on_page_seo($options_data, $iterations);





		// Local SEO


		$geo_iterations = 1;





		if (isset($_POST['local_seo_enabler'])) {


			// Search tags and remove non-used locations


			$tags = improveseo_search_geotags(array(


				$title, $content, $_POST['custom_title'], $_POST['custom_description'], $_POST['custom_keywords'], $_POST['permalink'], $_POST['tags']


			));





			$options_data['local_geo_country'] = $_POST['local_country'];


			$options_data['local_geo_locations'] = json_decode(stripslashes($_POST['local_geo_locations']), true);





			// Do not expand geo data if saving as draft


			if (isset($_POST['create'])) {


				$options_data['local_geo_locations'] = improveseo_expand_geodata($options_data['local_geo_country'], $options_data['local_geo_locations'], $tags);


			}





			$geo_iterations = sizeof($options_data['local_geo_locations']);


			if ($geo_iterations == 0) $geo_iterations = 1;





			if (isset($_POST['local_randomize'])) {


				shuffle($options_data['local_geo_locations']);


				$options_data['local_randomize'] = $_POST['local_randomize'];


			}





			// Categorization


			if (isset($_POST['enable_categorization'])) {


				$options_data['enable_categorization'] = true;





				$tags_order = array('country', 'state', 'city', 'zip');





				foreach ($tags_order as $tag) {


					if (!in_array($tag, $tags)) continue;





					$options_data['categorization'][] = $tag;


				}


			}





			//$options_data['local_geo_locations'] = array_unique($options_data['local_geo_locations']);


		}





		// Schema SEO


		if (isset($_POST['schema'])) {


			$options_data['schema'] = true;


			$options_data['schema_business'] = stripslashes($_POST['schema_business']);


			$options_data['schema_description'] = stripslashes($_POST['schema_description']);


			$options_data['schema_email'] = $_POST['schema_email'];


			$options_data['schema_telephone'] = $_POST['schema_telephone'];


			$options_data['schema_social'] = $_POST['schema_social'];


			$options_data['schema_rating_object'] = $_POST['schema_rating_object'];


			$options_data['schema_rating'] = $_POST['schema_rating'];


			$options_data['schema_rating_count'] = $_POST['schema_rating_count'];


			$options_data['schema_address'] = stripslashes($_POST['schema_address']);





			if (isset($_POST['hide_schema'])) {


				$options_data['hide_schema'] = true;


			}





			$iterations[] = Spintax::count(Spintax::parse($options_data['schema_business']));


			$iterations[] = Spintax::count(Spintax::parse($options_data['schema_description']));


			$iterations[] = Spintax::count(Spintax::parse($options_data['schema_email']));


			$iterations[] = Spintax::count(Spintax::parse($options_data['schema_social']));


			$iterations[] = Spintax::count(Spintax::parse($options_data['schema_address']));


			$iterations[] = Spintax::count(Spintax::parse($options_data['schema_rating_object']));


		}





		// Dripfeed Feature


		if (isset($_POST['dripfeed_enabler'])) {


			$options_data['dripfeed_type'] = $_POST['dripfeed_type'];


			$options_data['dripfeed_x'] = $_POST['dripfeed_x'];


		}





		// Image EXIF


		if (isset($_POST['exif_enabler'])) {


			$options_data['exif_locations'] = $_POST['exif_locations'];


		}


		if (isset($_POST['use_post_location'])) {


			$options_data['use_post_location'] = true;


		}





		// Permalink


		if ($_POST['permalink']) {


			$options_data['permalink'] = $_POST['permalink'];


		}


		if ($_POST['permalink_prefix']) {


			$options_data['permalink_prefix'] = sanitize_title($_POST['permalink_prefix']);


		}





		// Tags


		if ($_POST['tags']) {


			$options_data['tags'] = $_POST['tags'];


		}


		if (isset($_POST['noindex_tags'])) {


			$options_data['noindex_tags'] = true;


		}





		// Distribute


		if (isset($_POST['distribute'])) {


			$options_data['distribute'] = true;


		}





		// Channel pages


		if (isset($_POST['state_channel_page'])) {


			$project_data['state_channel_enabled'] = true;


			$project_data['state_channel_title'] = $_POST['state_channel_title'];


			$project_data['state_channel_page'] = $_POST['state_channel_content'];


		}


		if (isset($_POST['city_channel_page'])) {


			$project_data['city_channel_enabled'] = true;


			$project_data['city_channel_title'] = $_POST['city_channel_title'];


			$project_data['city_channel_page'] = $_POST['city_channel_content'];


		}





		// Math maximum number of posts


		// Count list items


        $items = improveseo_count_list_items($_POST);





		if (isset($_POST['local_seo_enabler'])) {


			if (!$items) $items = 1;


			$max = ($_POST['max_posts'] <= 0) ? $geo_iterations * $items : intval($_POST['max_posts']);


		} else {


			$max = ($_POST['max_posts'] <= 0) ? ($items ? $items : Spintax::count(Spintax::parse($title))) : intval($_POST['max_posts']);


		}


		if (isset($_POST['max_posts'])) {


			$options_data['max_posts'] = $_POST['max_posts'];


		}


		// AI Generation Settings (from popup form hidden fields)

		if (isset($_POST['ai_modal_type']) && $_POST['ai_modal_type'] === 'single') {

			$ai_fields = array(

				'ai_seed_keyword',

				'ai_seed_options',

				'ai_content_type',

				'ai_nos_of_words',

				'ai_point_of_view',

				'ai_content_lang',

				'ai_details_to_include',

				'ai_call_to_action',

				'ai_image_option',

				'ai_generated_title',

				'ai_for_testing_only'

			);

			foreach ($ai_fields as $field) {

				if (isset($_POST[$field]) && $_POST[$field] !== '') {

					$options_data[$field] = stripslashes($_POST[$field]);

				}

			}

		}

		// Restore the ai_* generation settings this submission did not carry.
		//
		// Deliberately OUTSIDE the ai_modal_type === 'single' block above: only the
		// two create forms post ai_modal_type at all. Editing an existing project
		// goes through views/posting/edit-post.php, which does not — so on exactly
		// the path that loses the data (publishing a draft), that block never runs,
		// and a copy of this call placed inside it never ran either.
		//
		// The condition mirrors how $model is chosen below (Task unless the request
		// is explicitly bulk), because this helper reads the improveseo_tasks table
		// and must never be pointed at a bulk id.
		$is_bulk_submission = isset($_POST['ai_modal_type']) && $_POST['ai_modal_type'] == 'bulk';

		if (isset($_GET['id']) && !$is_bulk_submission) {

			improveseo_preserve_generation_options($options_data, $_GET['id']);

		}


		$data = array(


			'name' => $name,


			'content' => base64_encode(json_encode($project_data)),


			'options' => base64_encode(json_encode($options_data)),


			'state' => isset($_POST['draft']) ? 'Draft' : 'Published',


			'iteration' => 0,


			'spintax_iterations' => max($iterations),


			//'max_iterations' => max($iterations) * $geo_iterations


			'max_iterations' => $max,


			'cats' => json_encode($_POST['cats'])


		);


		$wpdb->query("SET GLOBAL max_allowed_packet = 268435456");

		$model = new Task();
		if (isset($_POST['ai_modal_type']) && $_POST['ai_modal_type'] == 'bulk') {
			$model = new Bulktask();
			$data['max_iterations'] = count(array_filter(array_map('trim', explode("\n", trim($_POST['keywords'] ?? '')))));
		}

		$project_id = isset($_GET['id']) ? $model->update($data, $_GET['id']) : $model->create($data);


		if (isset($_GET['id'])) $project_id = $_GET['id'];

		if (isset($_POST['ai_modal_type']) && $_POST['ai_modal_type'] == 'bulk') {
			$keywords = array_filter(array_map('trim', explode("\n", trim($_POST['keywords'] ?? ''))));
			$detailsModel = new Bulktasksdetail();
			foreach ($keywords as $keyword) {
				$detail_data = array(
					'bulktask_id' => $project_id,
					'keyword_name' => $keyword,
					'select_exisiting_options' => $_POST['select_exisiting_options'] ?? 'seed_option1',
					'nos_of_words' => $_POST['nos_of_words'] ?? '600 to 1200 words',
					'content_lang' => $_POST['content_lang'] ?? 'US English',
					'tone_of_voice' => $_POST['tone_of_voice'] ?? '',
					'point_of_view' => $_POST['point_of_view'] ?? '',
					'details_to_include' => $_POST['details_to_include'] ?? '',
					'call_to_action' => $_POST['call_to_action'] ?? '',
					'aiImage' => $_POST['aiImage'] ?? '',
					'ai_image' => '',
					'cats' => json_encode($_POST['cats']),
					'assigning_authors' => $_POST['assigning_authors'] ?? '',
					'assigning_authors_value' => $_POST['assigning_authors_value'] ?? '',
					'assigning_multi_authors' => $_POST['assigning_multi_authors'] ?? '',
					'schedule_posts' => $_POST['schedule_posts'] ?? '',
					'published_on' => $_POST['published_on'] ?? '',
					'testimonial' => $_POST['testimonial'] ?? '',
					'Button_SC' => $_POST['Button_SC'] ?? '',
					'GoogleMap_SC' => $_POST['GoogleMap_SC'] ?? '',
					'Video_SC' => $_POST['Video_SC'] ?? '',
					'state' => 'Scheduled',
					'status' => 'Pending',
					'post_id' => null,
					'is_published_by_plugin' => 0,
					'ai_title' => '',
					'ai_content' => '',
					'created_at' => current_time('mysql'),
					'updated_at' => current_time('mysql')
				);
				$detailsModel->create($detail_data);
			}

			// Notify admin server — bulk task created
			if (function_exists('improveseo_notify_bulk_status')) {
				$notified = improveseo_notify_bulk_status('task_created', array(
					'task_name'   => $name,
					'total_tasks' => count($keywords),
					'project_id'  => $project_id,
				));
				my_plugin_log('[Dashboard] Bulk creation notification result: ' . ($notified ? 'OK' : 'FAILED'));
			}
		}

		if (isset($_POST['create'])) {


			FlashMessage::success('Project successfully created. It will generate <strong>'. $data['max_iterations'] .'</strong> posts/pages.'


				);


			wp_redirect(admin_url("admin.php?page=improveseo_projects&highlight={$project_id}&build_posts_id={$project_id}"));


		}


		elseif (isset($_POST['draft'])) {


			FlashMessage::success('Project successfully saved. You can continue editing by pressing Continue button.');


			wp_redirect(admin_url("admin.php?page=improveseo_projects&highlight={$project_id}"));


		} 


		exit;





		elseif ($action == 'do_update_post'):


			


			$name = $_POST['name'];
			if (isset($_GET['id'])) {
				$name_taken = $wpdb->get_var($wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->prefix}improveseo_tasks WHERE name = %s AND id != %d AND state <> 'Preview'",
					$name,
					intval($_GET['id'])
				));
				if ($name_taken) {
					FlashMessage::message('A project with this name already exists. Please choose a different name.', 'error');
					wp_redirect(admin_url('admin.php?page=improveseo_dashboard&action=edit_post&id=' . intval($_GET['id']) . '&update=true'));
					exit;
				}
			}

			if (isset($_GET['id'])) {
				improveseo_refuse_duplicate_project_name(wp_unslash($name), (int) $_GET['id'], true);
			}


			$title = $_POST['title'];


			$content = $_POST['content'];





			// $order   = array("\r\n", "\n");


			// $replace   = array("\n", "\\\n");


			//


			// $content = str_replace($order, $replace, $content);


			//


	


			$post_type = $_POST['post_type'];


	


			$project_data = array(


				'title' => stripslashes($title),


				'content' => stripslashes($content),


				'post_type' => $post_type


			);


	


			$iterations = [


				// title


				Spintax::count(Spintax::parse($title)),


				// content


				Spintax::count(Spintax::parse($content))


			];


	


			$options_data = array();


	


			// On-Page SEO — same collector as do_create_post, so an edit keeps the
			// meta title instead of dropping it. See improveseo.php.
			improveseo_collect_on_page_seo($options_data, $iterations);


	


			// Local SEO


			$geo_iterations = 1;


	


			if (isset($_POST['local_seo_enabler'])) {


				// Search tags and remove non-used locations


				$tags = improveseo_search_geotags(array(


					$title, $content, $_POST['custom_title'], $_POST['custom_description'], $_POST['custom_keywords'], $_POST['permalink'], $_POST['tags']


				));


	


				$options_data['local_geo_country'] = $_POST['local_country'];


				$options_data['local_geo_locations'] = json_decode(stripslashes($_POST['local_geo_locations']), true);


	


				// Do not expand geo data if saving as draft


				if (isset($_POST['create'])) {


					$options_data['local_geo_locations'] = improveseo_expand_geodata($options_data['local_geo_country'], $options_data['local_geo_locations'], $tags);


				}


	


				$geo_iterations = sizeof($options_data['local_geo_locations']);


				if ($geo_iterations == 0) $geo_iterations = 1;


	


				if (isset($_POST['local_randomize'])) {


					shuffle($options_data['local_geo_locations']);


					$options_data['local_randomize'] = $_POST['local_randomize'];


				}


	


				// Categorization


				if (isset($_POST['enable_categorization'])) {


					$options_data['enable_categorization'] = true;


	


					$tags_order = array('country', 'state', 'city', 'zip');


	


					foreach ($tags_order as $tag) {


						if (!in_array($tag, $tags)) continue;


	


						$options_data['categorization'][] = $tag;


					}


				}


	


				//$options_data['local_geo_locations'] = array_unique($options_data['local_geo_locations']);


			}


	


			// Schema SEO


			if (isset($_POST['schema'])) {


				$options_data['schema'] = true;


				$options_data['schema_business'] = stripslashes($_POST['schema_business']);


				$options_data['schema_description'] = stripslashes($_POST['schema_description']);


				$options_data['schema_email'] = $_POST['schema_email'];


				$options_data['schema_telephone'] = $_POST['schema_telephone'];


				$options_data['schema_social'] = $_POST['schema_social'];


				$options_data['schema_rating_object'] = $_POST['schema_rating_object'];


				$options_data['schema_rating'] = $_POST['schema_rating'];


				$options_data['schema_rating_count'] = $_POST['schema_rating_count'];


				$options_data['schema_address'] = stripslashes($_POST['schema_address']);


	


				if (isset($_POST['hide_schema'])) {


					$options_data['hide_schema'] = true;


				}


	


				$iterations[] = Spintax::count(Spintax::parse($options_data['schema_business']));


				$iterations[] = Spintax::count(Spintax::parse($options_data['schema_description']));


				$iterations[] = Spintax::count(Spintax::parse($options_data['schema_email']));


				$iterations[] = Spintax::count(Spintax::parse($options_data['schema_social']));


				$iterations[] = Spintax::count(Spintax::parse($options_data['schema_address']));


				$iterations[] = Spintax::count(Spintax::parse($options_data['schema_rating_object']));


			}


	


			// Dripfeed Feature


			if (isset($_POST['dripfeed_enabler'])) {


				$options_data['dripfeed_type'] = $_POST['dripfeed_type'];


				$options_data['dripfeed_x'] = $_POST['dripfeed_x'];


			}


	


			// Image EXIF


			if (isset($_POST['exif_enabler'])) {


				$options_data['exif_locations'] = $_POST['exif_locations'];


			}


			if (isset($_POST['use_post_location'])) {


				$options_data['use_post_location'] = true;


			}


	


			// Permalink


			if ($_POST['permalink']) {


				$options_data['permalink'] = $_POST['permalink'];


			}


			if ($_POST['permalink_prefix']) {


				$options_data['permalink_prefix'] = sanitize_title($_POST['permalink_prefix']);


			}


	


			// Tags


			if ($_POST['tags']) {


				$options_data['tags'] = $_POST['tags'];


			}


			if (isset($_POST['noindex_tags'])) {


				$options_data['noindex_tags'] = true;


			}


	


			// Distribute


			if (isset($_POST['distribute'])) {


				$options_data['distribute'] = true;


			}


	


			// Channel pages


			if (isset($_POST['state_channel_page'])) {


				$project_data['state_channel_enabled'] = true;


				$project_data['state_channel_title'] = $_POST['state_channel_title'];


				$project_data['state_channel_page'] = $_POST['state_channel_content'];


			}


			if (isset($_POST['city_channel_page'])) {


				$project_data['city_channel_enabled'] = true;


				$project_data['city_channel_title'] = $_POST['city_channel_title'];


				$project_data['city_channel_page'] = $_POST['city_channel_content'];


			}


	


			// Math maximum number of posts


			// Count list items


			$items = improveseo_count_list_items($_POST);


	


			if (isset($_POST['local_seo_enabler'])) {


				if (!$items) $items = 1;


				$max = ($_POST['max_posts'] <= 0) ? $geo_iterations * $items : intval($_POST['max_posts']);


			} else {


				$max = ($_POST['max_posts'] <= 0) ? ($items ? $items : Spintax::count(Spintax::parse($title))) : intval($_POST['max_posts']);


			}


			if (isset($_POST['max_posts'])) {


				$options_data['max_posts'] = $_POST['max_posts'];


			}


	


			// This save rewrites the options column wholesale. The ai_* generation
			// settings only reach $_POST from the AI popup, so they arrive empty on
			// an update and would be lost — restore them from the stored project.
			improveseo_preserve_generation_options($options_data, isset($_GET['id']) ? $_GET['id'] : 0);


			$data = array(


				'name' => $name,


				'content' => base64_encode(json_encode($project_data)),


				'options' => base64_encode(json_encode($options_data)),


				'state' => 'Update_started',


				'iteration' => 0,


				'spintax_iterations' => max($iterations),


				//'max_iterations' => max($iterations) * $geo_iterations


				'max_iterations' => $max,


				'cats' => json_encode($_POST['cats'])


			);


			$wpdb->query("SET GLOBAL max_allowed_packet = 268435456");


	


			$project_id = $model->update($data, $_GET['id']) ;


			if (isset($_GET['id'])) $project_id = $_GET['id'];


			FlashMessage::success('Project successfully updated. You can update old post by clicking update my posts.');


			wp_redirect(admin_url("admin.php?page=improveseo_projects&highlight={$project_id}&build_posts_id={$project_id}"));


			exit;


	


		elseif ($action == 'edit_post'):


		$task = $model->find($_GET['id']);





		View::render('posting.edit-post', compact('task'));


	


	endif;


}