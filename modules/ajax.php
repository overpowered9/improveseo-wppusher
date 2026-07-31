<?php


use ImproveSEO\View;


use ImproveSEO\Spintax;





use ImproveSEO\Validator;


use ImproveSEO\LiteSpintax;





use ImproveSEO\Models\Task;


use ImproveSEO\FlashMessage;





add_action('wp_ajax_improveseo_generate_preview', 'improveseo_generate_preview');


function improveseo_generate_preview(){


    global $wpdb;


    $model = new Task();


    $name = $_POST['name'];


    $title = $_POST['title'];


    $content = $_POST['content'];


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





		// On-Page SEO


		if (isset($_POST['on_page_seo'])) {


			$options_data['custom_title'] = stripslashes($_POST['custom_title']);


			$options_data['custom_description'] = stripslashes($_POST['custom_description']);


			$options_data['custom_keywords'] = stripslashes($_POST['custom_keywords']);





			$iterations[] = Spintax::count(Spintax::parse($options_data['custom_title']));


			$iterations[] = Spintax::count(Spintax::parse($options_data['custom_description']));


			$iterations[] = Spintax::count(Spintax::parse($options_data['custom_keywords']));


		}





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





		$data = array(


			'name' => $name,


			'content' => base64_encode(json_encode($project_data)),


			'options' => base64_encode(json_encode($options_data)),


			// Preview projects are transient: a dedicated state lets the cron
			// sweep identify and remove only preview rows, never real projects.
			'state' => 'Preview',


			'iteration' => 0,


			'spintax_iterations' => max($iterations),


			//'max_iterations' => max($iterations) * $geo_iterations


			// A preview only needs one representative post, not the whole project.
			'max_iterations' => 1,


			'cats' => json_encode($_POST['cats'])


		);


		$wpdb->query("SET GLOBAL max_allowed_packet = 268435456");


        $project_id = $model->create($data);


        echo json_encode(array('status' => 'success', 'project_id' => $project_id));


        die;


}

add_action('wp_ajax_improveseo_preview_url', 'improveseo_preview_url');

/**
 * Return the front-end preview link for an already-built preview project.
 *
 * This is the admin-ajax equivalent of the projects-list 'export_preview_url'
 * action. The browser used to reach that action by loading the whole Projects
 * List admin page (twice — once to start the build, once after it), which cost
 * two full wp-admin renders before the preview itself began loading.
 *
 * @return void
 */
function improveseo_preview_url()
{
	global $wpdb;

	if (!current_user_can('manage_options')) {
		wp_send_json_error(array('message' => 'Permission denied'));
	}

	$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

	if (!$id) {
		wp_send_json_error(array('message' => 'Missing preview id'));
	}

	$post_ids = $wpdb->get_col($wpdb->prepare(
		"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'improveseo_project_id' AND meta_value = %s",
		$id
	));

	// No preview post was generated (build failed, or it was already swept).
	if (empty($post_ids)) {
		wp_send_json_error(array(
			'message' => 'The preview could not be generated. Please close this and try again.'
		));
	}

	$preview_post_id = $post_ids[array_rand($post_ids)];

	// Preview posts are drafts, so they are only viewable through WordPress'
	// nonce'd preview link — shown exactly as they would look, never published.
	wp_send_json_success(array(
		'url' => get_preview_post_link($preview_post_id, array('id' => $id))
	));
}

// ── Instant preview ──────────────────────────────────────────────────────────
// Renders title + content server-side and returns HTML. No temporary project,
// no builder, no iframe — the preview appears in under a second.
add_action('wp_ajax_improveseo_instant_preview', 'improveseo_instant_preview');

function improveseo_instant_preview() {
	if ( ! current_user_can('manage_options') ) {
		wp_send_json_error( array( 'message' => 'Permission denied' ) );
	}

	if ( ! isset($_POST['nonce']) || ! wp_verify_nonce($_POST['nonce'], 'improveseo_instant_preview') ) {
		wp_send_json_error( array( 'message' => 'Security check failed. Please reload the page and try again.' ) );
	}

	$title   = isset($_POST['title'])   ? sanitize_text_field( wp_unslash($_POST['title']) )   : '';
	$content = isset($_POST['content']) ? improveseo_strip_stray_css(wp_kses_post( wp_unslash($_POST['content']) ))        : '';

	if ( $title === '' ) {
		$title = '(Untitled)';
	}

	// AI-generated content (single post or bulk) always opens with an <h1>
	// duplicating the title — improveseo_bulk_strip_content_h1() is the exact
	// function improveseo_bulk_build_post_content() uses to drop it there;
	// despite the name it is a plain string helper with no bulk-only
	// dependency, so reusing it here keeps a single post's preview from
	// showing its title twice, the same as the bulk preview already avoids.
	if ( function_exists( 'improveseo_bulk_strip_content_h1' ) ) {
		$content = improveseo_bulk_strip_content_h1( $content );
	}

	// Render any ImproveSEO shortcodes (testimonials, maps, buttons, etc.), the
	// same call the bulk "View AI Content" preview renders through
	// (improveseo_bulk_build_post_content), so both previews treat shortcodes
	// identically.
	$rendered = do_shortcode( $content );

	wp_send_json_success( array(
		'title' => $title,
		'html'  => $rendered,
	) );
}

// ── Bulk tasks list: instant preview of an already-saved task ────────────────
// The list's row-level "Preview Post" action (Draft rows) used to link
// straight to the full "View AI Content" admin page in a new tab
// (action=viewAiContent). Every other "Preview Post" button now opens the
// same in-modal card instead, so this one is rendered the same way — through
// improveseo_bulk_build_post_content(), the SAME renderer viewAiContent.php
// itself uses, so the modal shows byte-for-byte what that page would show.
add_action('wp_ajax_improveseo_bulk_preview_by_id', 'improveseo_bulk_preview_by_id');

function improveseo_bulk_preview_by_id() {
	global $wpdb;

	if ( ! current_user_can('manage_options') ) {
		wp_send_json_error( array( 'message' => 'Permission denied' ) );
	}

	if ( ! isset($_POST['nonce']) || ! wp_verify_nonce($_POST['nonce'], 'improveseo_bulk_preview_by_id') ) {
		wp_send_json_error( array( 'message' => 'Security check failed. Please reload the page and try again.' ) );
	}

	$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
	if ( ! $id ) {
		wp_send_json_error( array( 'message' => 'Missing task id.' ) );
	}

	$task = $wpdb->get_row( $wpdb->prepare(
		"SELECT * FROM {$wpdb->prefix}improveseo_bulktasksdetails WHERE id = %d",
		$id
	) );

	if ( ! $task || empty( $task->ai_content ) ) {
		wp_send_json_error( array( 'message' => 'This post has no generated content yet.' ) );
	}

	$built = improveseo_bulk_build_post_content( $task );

	wp_send_json_success( array(
		'title' => sanitize_text_field( $task->ai_title ),
		'html'  => do_shortcode( improveseo_strip_stray_css(wp_kses_post( $built['html'] )) ),
	) );
}

// AJAX handler for creating categories in bulk posts popup
add_action('wp_ajax_create_bulk_category', 'improveseo_create_bulk_category');
function improveseo_create_bulk_category() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'create_category_nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }
    
    // Check user permissions
    if (!current_user_can('manage_categories')) {
        wp_send_json_error('You do not have permission to create categories');
        return;
    }
    
    $cat_name = sanitize_text_field($_POST['cat_name']);
    
    if (empty($cat_name)) {
        wp_send_json_error('Category name is required');
        return;
    }
    
    // Create category slug
    $cat_slug = sanitize_title($cat_name);
    
    // Insert category
    $term = wp_insert_term(
        $cat_name,
        'category',
        array(
            'slug' => $cat_slug,
        )
    );
    
    if (is_wp_error($term)) {
        wp_send_json_error($term->get_error_message());
        return;
    }
    
    wp_send_json_success(array(
        'term_id' => $term['term_id'],
        'name' => $cat_name,
        'slug' => $cat_slug
    ));
}

// Rename a bulk project
add_action('wp_ajax_rename_bulk_project', 'improveseo_rename_bulk_project');
function improveseo_rename_bulk_project() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'rename_bulk_project_nonce')) {
        wp_send_json_error('Security check failed');
    }
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permission denied');
    }
    $id   = intval($_POST['id']);
    $name = sanitize_text_field($_POST['name']);
    if (!$id || $name === '') {
        wp_send_json_error('Invalid data');
    }
    global $wpdb;
    $updated = $wpdb->update(
        $wpdb->prefix . 'improveseo_bulktasks',
        array('name' => $name),
        array('id'   => $id),
        array('%s'),
        array('%d')
    );
    if ($updated === false) {
        wp_send_json_error('Database error');
    }
    wp_send_json_success(array('name' => $name));
}

// Rename a single-post project (improveseo_tasks). Mirrors the bulk handler above but
// targets the single-project table used by the Projects List.
add_action('wp_ajax_rename_project', 'improveseo_rename_project');
function improveseo_rename_project() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'rename_project_nonce')) {
        wp_send_json_error('Security check failed');
    }
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permission denied');
    }
    $id   = intval($_POST['id']);
    $name = sanitize_text_field($_POST['name']);
    if (!$id || $name === '') {
        wp_send_json_error('Invalid data');
    }
    global $wpdb;
    $updated = $wpdb->update(
        $wpdb->prefix . 'improveseo_tasks',
        array('name' => $name),
        array('id'   => $id),
        array('%s'),
        array('%d')
    );
    if ($updated === false) {
        wp_send_json_error('Database error');
    }
    wp_send_json_success(array('name' => $name));
}

// Note: wp_ajax_re_generate_post is handled by re_generate_post() in bulk_AI_post_function.php
function improveseo_re_generate_post() {
    global $wpdb;
    
    if (!isset($_POST['id'])) {
        wp_send_json_error('Task ID is required');
        return;
    }
    
    $id = intval($_POST['id']);
    
    // Clear existing AI content before regenerating
    $result = $wpdb->update(
        $wpdb->prefix . 'improveseo_bulktasksdetails',
        array(
            'ai_content' => '',
            'ai_title' => '',
            'ai_image' => '',
            'status' => 'Pending'
        ),
        array('id' => $id),
        array('%s', '%s', '%s', '%s'),
        array('%d')
    );
    
    if ($result === false) {
        wp_send_json_error('Failed to clear old content');
        return;
    }
    
    // Trigger content regeneration by calling the bulk AI function
    require_once plugin_dir_path(__FILE__) . 'bulk_AI_post_function.php';
    
    try {
        generateBulkAiContent($id);
        wp_send_json_success('Content regenerated successfully');
    } catch (Exception $e) {
        wp_send_json_error('Failed to regenerate content: ' . $e->getMessage());
    }
}