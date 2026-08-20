<?php

/*
Plugin Name: Improve SEO
Plugin URI: https://wordpress.org/plugins/improveseo/
Description: Creates a large number of pages/posts and customize them to rank in Google.
Author: Improve SEO Team
Author URI: https://improveseoplugin.com/
Version: 2.0.12
Requires at least: 5.0
Tested up to: 6.7
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: improve-seo
*/

define("IMPROVESEO_VERSION", "2.0.12");

define('IMPROVESEO_ROOT', dirname(__FILE__));

define('IMPROVESEO_DIR', untrailingslashit(plugin_dir_url(__FILE__)));





define('WT_PATH', untrailingslashit(plugin_dir_path(__FILE__)));

define('WT_URL', untrailingslashit(plugin_dir_url(__FILE__)));

wp_enqueue_script(

	'custom-plugin-script',

	plugin_dir_url(__FILE__) . 'assets/js/custom-plugin-script.js',

	array('jquery'),

	filemtime(plugin_dir_path(__FILE__) . 'assets/js/custom-plugin-script.js'),

	true

);



$standred_variable = array(

	'ajax_url' => admin_url('admin-ajax.php')

);



wp_localize_script('custom-plugin-script', 'standred_var', $standred_variable);



/**
 * Nonces for the admin AJAX endpoints the plugin's scripts call.
 *
 * Deliberately hooked rather than localized alongside the enqueue above: nonces are
 * per-user, and wp_create_nonce() is not safe to call while plugin files are still
 * loading, because pluggable.php has not been read yet and there is no current user.
 * Priority 20 keeps it after the handles are registered.
 */
function improveseo_localize_ajax_nonces()
{
	$data = array(
		'ajax_url'     => admin_url( 'admin-ajax.php' ),
		'upload_nonce' => wp_create_nonce( 'improveseo_upload_nonce' ),
		'gpt_nonce'    => wp_create_nonce( 'improveseo_gpt_nonce' ),
		// Shared nonce for the remaining admin AJAX endpoints.
		'nonce'        => wp_create_nonce( 'improveseo_ajax' ),
	);

	// Attach to every handle that talks to admin-ajax.php. wp_localize_script() is a
	// no-op for a handle not registered on the current screen, so listing them all
	// here keeps the nonce source in one place.
	foreach ( array( 'custom-plugin-script', 'improveseo-form', 'improveseo-main', 'improveseo-posting', 'tmm_script_js' ) as $handle ) {
		wp_localize_script( $handle, 'improveseo_vars', $data );
	}
}

add_action( 'admin_enqueue_scripts', 'improveseo_localize_ajax_nonces', 20 );









/* 

 **========== Files Load =========== 

 */

if (file_exists(dirname(__FILE__) . '/includes/helpers.php'))

	include_once dirname(__FILE__) . '/includes/helpers.php';

if (file_exists(dirname(__FILE__) . '/modules/GenerateAIpopup.php'))

	include_once dirname(__FILE__) . '/modules/GenerateAIpopup.php';

if (file_exists(dirname(__FILE__) . '/modules/single_and_bulk_AI_post_function.php'))

	include_once dirname(__FILE__) . '/modules/single_and_bulk_AI_post_function.php';

if (file_exists(dirname(__FILE__) . '/modules/single_AI_post_function.php'))

	include_once dirname(__FILE__) . '/modules/single_AI_post_function.php';

if (file_exists(dirname(__FILE__) . '/modules/bulk_AI_post_function.php'))

	include_once dirname(__FILE__) . '/modules/bulk_AI_post_function.php';


// if( file_exists( dirname(__FILE__).'/includes/admin.php' )) include_once dirname(__FILE__).'/includes/admin.php';



include_once 'bootstrap.php';



register_activation_hook(__FILE__, 'improveseo_install');

register_activation_hook(__FILE__, 'improveseo_install_data');



// Features



register_deactivation_hook(__FILE__, 'improveseo_uninstall');



function improveseo_load_media_files()
{

	wp_enqueue_media();

}

add_action('admin_enqueue_scripts', 'improveseo_load_media_files');



//add_action( 'init', "workdex_init" );



add_filter('jpeg_quality', function ($arg) {

	return 75;

});

//adding buttons to content editor

add_action('media_buttons', 'add_my_media_button');

function add_my_media_button()
{



	if (function_exists('get_current_screen')) {



		$my_current_screen = get_current_screen();

		$allowed_bases = array('improve-seo_page_improveseo_posting');

		if (!in_array($my_current_screen->base, $allowed_bases)) {

			return;

		}

	}





	$html = '';

	$html .= '<select class="sw-editor-selector styling_post_page_action_buttons " style="text-align:left !important; width:230px;">

                       <option value="addshortcode">Add Shortcode</option>

                       <option value="testimonial">Testimonials</option>

                       <option value="googlemap">Google Maps</option>

                       <option value="button">Buttons</option>

                       <option value="video">Videos</option>

                       <option value="list">Lists</option>

                </select> &nbsp;';
	// $html .= '
	// 			<div style=" display:flex; justify-content:end;">
					
	// 				<a type="button" style="margin-left:10px;" id="generate_ai_popup_open" class="styling_post_page_action_buttons2 styling_post_page_action_buttons" data-toggle="modal" data-target="#exampleModal1"><img src="' . WT_URL . '/assets/images/latest-images/iconoir_sparks.svg" alt="iconoir_sparks">Generate AI Content</a>
	// 			</div>';
global $ai_modal_type;
switch ($ai_modal_type) {
    case 'single':
        $html .= '
        <div style="display:flex; justify-content:end;">
            <a type="button" style="margin-left:10px;" id="generate_ai_popup_open" class="styling_post_page_action_buttons2 styling_post_page_action_buttons" data-toggle="modal" data-target="#exampleModal1">
                <img src="' . WT_URL . '/assets/images/latest-images/iconoir_sparks.svg" alt="iconoir_sparks">Generate AI Content
            </a>
        </div>';
        break;
    case 'bulk':
        $html .= '
        <div style="display:flex; justify-content:end;">
            <a type="button" style="margin-left:10px;" id="generate_ai_popup_open" class="styling_post_page_action_buttons2 styling_post_page_action_buttons" data-toggle="modal" data-target="#exampleModal2">
                <img src="' . WT_URL . '/assets/images/latest-images/iconoir_sparks.svg" alt="iconoir_sparks">Generate AI Content
            </a>
        </div>';
        break;
    default:
        $html .= '
        <div style="display:flex; justify-content:end;">
            <a type="button" style="margin-left:10px;" id="generate_ai_popup_open" class="styling_post_page_action_buttons2 styling_post_page_action_buttons" data-toggle="modal" data-target="#exampleModal">
                <img src="' . WT_URL . '/assets/images/latest-images/iconoir_sparks.svg" alt="iconoir_sparks">Generate AI Content
            </a>
        </div>';
}


	$seo_list = improve_seo_lits();



	if (!empty($seo_list)) {

		foreach ($seo_list as $li) {

			$list .= '<button data-action="list" class="add-seolistshortcode styling_post_page_shortcode_action_buttons button" id=' . $li . '>@list:' . $li . '</button>';

		}

	}





	$saved_rnos = get_option('get_saved_random_numbers');



	if (!empty($saved_rnos)) {

		foreach ($saved_rnos as $id) {



			//testimonials        

			$testimonial = get_option('get_testimonials_' . $id);

			if (!empty($testimonial)) {

				$display_name = $id;

				$data_name = '';

				if (isset($testimonial['tw_testi_shortcode_name'])) {

					if ($testimonial['tw_testi_shortcode_name'] != "") {

						$data_name = $display_name = $testimonial['tw_testi_shortcode_name'];

					}

				}

				$html .= '<button data-action="testimonial" data-name="' . $data_name . '" id="' . $id . '" class="sw-hide-btn styling_post_page_shortcode_action_buttons button">Add Testimonial - ' . $display_name . '</button>';

			}



			//buttons        

			$buttons = get_option('get_buttons_' . $id);

			if (!empty($buttons)) {

				$display_name = $id;

				$data_name = '';

				if (isset($buttons['tw_button_shortcode_name'])) {

					if ($buttons['tw_button_shortcode_name'] != "") {

						$data_name = $display_name = $buttons['tw_button_shortcode_name'];

					}

				}

				$html .= '<button data-action="button" data-name="' . $data_name . '" id="' . $id . '" class="sw-hide-btn styling_post_page_shortcode_action_buttons button">Add Button - ' . $display_name . '</button>';

			}



			//googlemaps        

			$google_map = get_option('get_googlemaps_' . $id);

			if (!empty($google_map)) {

				$display_name = $id;

				$data_name = '';

				if (isset($google_map['tw_maps_shortcode_name'])) {

					if ($google_map['tw_maps_shortcode_name'] != "") {

						$data_name = $display_name = $google_map['tw_maps_shortcode_name'];

					}

				}

				$html .= '<button data-action="googlemap" data-name="' . $data_name . '" id="' . $id . '" class="sw-hide-btn styling_post_page_shortcode_action_buttons button">Add GoogleMap - ' . $display_name . '</button>';

			}



			//videos

			$videos = get_option('get_videos_' . $id);

			if (!empty($videos)) {

				$display_name = $id;

				$data_name = '';

				if (isset($videos['video_shortcode_name'])) {

					if ($videos['video_shortcode_name'] != "") {

						$data_name = $display_name = $videos['video_shortcode_name'];

					}

				}

				$html .= '<button data-action="video" data-name="' . $data_name . '" id="' . $id . '" class="sw-hide-btn styling_post_page_shortcode_action_buttons button">Add Video - ' . $display_name . '</button>';

			}

		}

	}





	$seo_list = improve_seo_lits();

	if (!empty($seo_list)) {

		foreach ($seo_list as $li) {

			$html .= '<button data-action="list" class="sw-hide-btn styling_post_page_shortcode_action_buttons add-seolistshortcode button" id=' . $li . '>@list:' . $li . '</button>';

		}

	}

	echo $html;





	/*******************/

	// generateAIpopup();

	/*******************/

}



function crop_image_programmatically($image_path, $crop_width, $crop_height, $crop_x = 0, $crop_y = 0)
{

	// Load the image editor

	$image_editor = wp_get_image_editor($image_path);



	// Check if the image editor was loaded successfully

	if (is_wp_error($image_editor)) {

		return $image_editor; // Returns the error if there was an issue loading the editor

	}



	// Crop the image

	$image_editor->crop($crop_x, $crop_y, $crop_width, $crop_height);



	// Save the cropped image

	$saved = $image_editor->save($image_path);



	// Return the result

	return !is_wp_error($saved) ? $saved : $saved->get_error_message();

}





// Ensure cronjob_request_event is scheduled. Safe to call repeatedly.
// Logs only when it had to (re)schedule, so this isn't noisy.
function improveseo_ensure_cron_scheduled()
{
	if (wp_next_scheduled('cronjob_request_event')) {
		return;
	}
	$ok = wp_schedule_event(time(), 'two_minutes', 'cronjob_request_event');
	if (false === $ok) {
		my_plugin_log('IMPROVESEO CRON: wp_schedule_event returned FALSE for cronjob_request_event');
	} else {
		my_plugin_log('IMPROVESEO CRON: cronjob_request_event was missing; rescheduled');
	}
}

function activate_my_plugin()
{
	improveseo_ensure_cron_scheduled();
}



function my_plugin_log($message)
{

	$log_file = WP_CONTENT_DIR . '/debug.log';

	$current_time = date('Y-m-d H:i:s');

	$log_message = "[{$current_time}] {$message}\n";

	file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);

}





register_activation_hook(__FILE__, 'activate_my_plugin');





// Define custom interval for every 3 minutes

function custom_cron_intervals($schedules)
{

	$schedules['two_minutes'] = array(

		'interval' => 120,

		'display' => __('Every 2 minutes'),

	);

	return $schedules;

}

add_filter('cron_schedules', 'custom_cron_intervals');

// Self-heal: if the cron event vanished from wp_options, restore it on next request.
// Runs on init (after cron_schedules filter is registered) so the 'two_minutes' interval is available.
add_action('init', 'improveseo_ensure_cron_scheduled');







add_action('init', 'updating_post_status_to_publish');

function updating_post_status_to_publish()
{



	// improveseo_project_id

	wp_enqueue_style('tmm_stlye_css', WT_URL . "/assets/css/wt-style.css", true);

	$args = array(

		'post_status' => array('future')

	);

	$query = new WP_Query($args);

	$all_posts = $query->posts;



	$post_data = array();

	foreach ($all_posts as $key => $value) {



		$post_data[] = array(

			'post_id' => $value->ID,

			'post_date' => $value->post_date,

		);

	}



	foreach ($post_data as $i => $v) {



		$post_id = $v['post_id'];

		$post_date = $v['post_date'];

		$post_status = get_post_status($post_id);

		if ($post_status != 'future') {

			continue;

		}

		$date_now = new DateTime();

		$date_op = new DateTime($post_date);



		if ($date_now > $date_op) {

			change_post_status($post_id, $status = 'publish');

		}

	}



}



//change the post status

function change_post_status($post_id, $status)
{

	$current_post = get_post($post_id, 'ARRAY_A');

	$current_post['post_status'] = $status;

	wp_update_post($current_post);

}





function workdex_init()
{



	global $wpdb;

	$time = get_option("work_dex_schedule");

	if ($time < (time() - 3600 * 12)) {

		$wpdb->query("UPDATE " . $wpdb->posts . " SET post_status='publish' WHERE post_date<=now() and post_date_gmt<=now()");

		update_option("work_dex_schedule", time());

	}

}



add_action('wp_ajax_workdex_builder_ajax', 'improveseo_builder');

add_action('wp_ajax_workdex_builder_update_ajax', 'improveseo_builder_update');



//AJAX call to check if preview window is open

add_action('wp_ajax_preview_delete_ajax', 'preview_delete_ajax');





$debug = 0;



//add_filter('pre_set_site_transient_update_plugins', 'improveseo_check_for_update');



function improveseo_check_for_update($transient)
{

	if (empty($transient->checked)) {

		return $transient;

	}

	if (improveseo_check_version()) {

		if (improveseo_check_version() != IMPROVESEO_VERSION) {

			$plugin_slug = plugin_basename("ImproveSEO/improveseo.php");

			$transient->response[$plugin_slug] = (object) array(

				'new_version' => workhorse_check_version(),

				'package' => "http://www.dexblog.net/workhorse/workhorse-by-dexblog-" . workhorse_check_version() . ".zip",

				'slug' => $plugin_slug

			);

		}

	}

	return $transient;

}



/**

 * Api handler

 */

function improveseo_api($action, $arg)
{

	$id_last = get_option("dexscan_last_id");

	$url = 'http://api-dexsecurity.dexblog.net/api.php?action=' . $action . '&host=' . $_SERVER["HTTP_HOST"] . "&id_scan=" . $id_last;

	if ($action == "getdata") {

		$ids = dexscan_save_file_backup($arg);

		$arg['id_save'] = $ids;

	}

	if (ini_get('allow_url_fopen')) {

		$options = array(

			'http' => array(

				'header' => "Content-type: application/x-www-form-urlencoded\r\n",

				'method' => 'POST',

				'content' => http_build_query($arg)

			)

		);

		$context = stream_context_create($options);

		$result = @file_get_contents($url, false, $context);

	} else {

		if (_is_curl_installed()) {

			foreach ($arg as $key => $value) {

				$fields_string .= $key . '=' . $value . '&';

			}

			rtrim($fields_string, '&');

			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, $url);

			curl_setopt($ch, CURLOPT_POST, count($fields));

			curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);

			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$result = curl_exec($ch);

			curl_close($ch);

		}

	}

	$da = json_decode($result);

	return $da;

}

function improveseo_check_version()
{

	$lastupdate = get_option("improveseo_lastcheck");

	if ($lastupdate < (time() - 600)) {

		$data2 = array(

			'version' => IMPROVESEO_VERSION

		);

		$data = improveseo_api("versionimproveseo", $data2);

		if ($data->status == 1) {

			update_option("improveseo_new_version", $data->version);

		} else {

			update_option("improveseo_new_version", $data->version);

		}

	}

	update_option("improveseo_lastcheck", time());

	return get_option("improveseo_new_version");

}



/*check curl install or not*/

function _is_curl_installed()
{

	if (in_array('curl', get_loaded_extensions())) {

		return true;

	} else {

		return false;

	}

}

/*end*/



add_action('admin_enqueue_scripts', 'improveseo_hide_other_notices');

function improveseo_hide_other_notices()
{

	if (is_admin()) {



		$my_current_screen = get_current_screen();

		if ( isset( $my_current_screen->base ) && strpos( $my_current_screen->base, 'improveseo' ) !== false ) {

			echo '<style>.notice{ display:none !important;}</style>';

		}

	}

}





/**
 * Single source of truth for the featured-image toggles.
 *
 * The settings card (views/settings/index.php) renders an unsaved toggle as ON
 * (get_option default '1'), so every enforcement site MUST resolve the same
 * way — reading with default '0' made the UI say ON while publishing acted OFF.
 *
 * @param string $context 'bulk' or 'single'.
 * @return bool
 */
function improveseo_featured_images_enabled_for( $context ) {
	return get_option( 'improveseo_featured_images_enabled', '1' ) == '1'
		&& get_option( 'improveseo_featured_images_' . $context, '1' ) == '1';
}

/**
 * The one logo asset every admin screen must render. Screens must call this
 * instead of hardcoding an asset path so a future logo swap is one change.
 */
function improveseo_logo_url() {
	return WT_URL . '/assets/images/latest-images/seo-latest-logo.svg';
}

/**
 * Registers an image as a media attachment and sets it as the featured image
 * (post thumbnail) for the given post. The hero image is honoured regardless of
 * how it got there — AI-generated or user-uploaded.
 *
 * Accepts three URL shapes and never silently drops a valid hero:
 *   1. A full URL inside this site's uploads dir → registered from the file on disk.
 *   2. A protocol-relative ("//host/…") or root-relative ("/wp-content/…") URL
 *      → normalised to an absolute URL first, then handled as (1) or (3).
 *   3. Any other (remote) URL → downloaded into uploads via media_sideload_image().
 *
 * @param int    $post_id    The post to attach the thumbnail to.
 * @param string $image_url  Hero image URL (local uploads, root-relative, or remote).
 * @param string $post_title Used as the attachment title; falls back to filename.
 * @return int|false  Attachment ID on success, false on any failure.
 */
/**
 * Read a post's SEO title / description / focus keyword from whichever source
 * has it: the active SEO plugin (Yoast, RankMath, SEOPress) or the plugin's own
 * improveseo_custom_* meta. Returns '' when nothing is set.
 *
 * @param int    $post_id
 * @param string $what  'title' | 'desc' | 'focuskw'
 */
function improveseo_get_seo_meta( $post_id, $what ) {
	if ( empty( $post_id ) ) {
		return '';
	}
	$keys = array(
		'title'   => array( '_yoast_wpseo_title', 'rank_math_title', '_seopress_titles_title', 'improveseo_custom_title' ),
		'desc'    => array( '_yoast_wpseo_metadesc', 'rank_math_description', '_seopress_titles_desc', 'improveseo_custom_description' ),
		'focuskw' => array( '_yoast_wpseo_focuskw', 'rank_math_focus_keyword', '_seopress_analysis_target_kw', 'improveseo_custom_keywords' ),
	);
	if ( ! isset( $keys[ $what ] ) ) {
		return '';
	}
	foreach ( $keys[ $what ] as $key ) {
		$value = trim( (string) get_post_meta( $post_id, $key, true ) );
		if ( $value !== '' ) {
			if ( $what === 'focuskw' ) {
				$parts = preg_split( '/[,|]/', $value );
				return trim( $parts[0] );
			}
			return $value;
		}
	}
	return '';
}

/**
 * Store a generated meta title / description on a post. Always writes the
 * plugin's own improveseo_custom_* keys (used by the project/post detail pages)
 * and mirrors into whichever SEO plugin is active so the live post gets real,
 * static meta instead of the plugin's on-the-fly template output.
 */
function improveseo_write_seo_meta( $post_id, $meta_title, $meta_desc ) {
	if ( empty( $post_id ) ) {
		return;
	}
	$meta_title = trim( (string) $meta_title );
	$meta_desc  = trim( (string) $meta_desc );
	if ( $meta_title === '' && $meta_desc === '' ) {
		return;
	}

	// Plugin's own copy — drives the details pages regardless of SEO plugin.
	if ( $meta_title !== '' ) {
		update_post_meta( $post_id, 'improveseo_custom_title', $meta_title );
	}
	if ( $meta_desc !== '' ) {
		update_post_meta( $post_id, 'improveseo_custom_description', $meta_desc );
	}

	// Mirror into the active SEO plugin(s) so the live post carries real meta.
	$targets = array();
	if ( defined( 'WPSEO_VERSION' ) || function_exists( 'YoastSEO' ) ) {
		$targets[] = array( '_yoast_wpseo_title', '_yoast_wpseo_metadesc' );
	}
	if ( defined( 'RANK_MATH_VERSION' ) || class_exists( 'RankMath' ) ) {
		$targets[] = array( 'rank_math_title', 'rank_math_description' );
	}
	if ( defined( 'SEOPRESS_VERSION' ) || function_exists( 'seopress_init' ) ) {
		$targets[] = array( '_seopress_titles_title', '_seopress_titles_desc' );
	}
	foreach ( $targets as $pair ) {
		if ( $meta_title !== '' ) {
			update_post_meta( $post_id, $pair[0], $meta_title );
		}
		if ( $meta_desc !== '' ) {
			update_post_meta( $post_id, $pair[1], $meta_desc );
		}
	}
}

/**
 * Collect the On-Page SEO fields (meta title / description / keywords) from a
 * create- or update-post submission into the project's options array.
 *
 * The redesigned create forms dropped the legacy "on_page_seo" opt-in checkbox
 * but still post custom_title / custom_description — the AI wizard copies its
 * Meta Title and Meta Description into them on submit (saveFinalData() in
 * assets/js/custom-plugin-script.js). The old gate, isset($_POST['on_page_seo']),
 * could therefore never be true, so the meta title the user entered was silently
 * dropped: it never reached the project options, the builder never wrote
 * improveseo_custom_title onto the post, and the details screen fell back to
 * showing the post title instead. Store whatever is actually posted; the legacy
 * checkbox, where a form still sends it, keeps forcing the block on.
 *
 * @param array $options_data Project options; modified in place.
 * @param array $iterations   Spintax iteration counts; modified in place.
 */
function improveseo_collect_on_page_seo( array &$options_data, array &$iterations ) {
	$legacy_optin = isset( $_POST['on_page_seo'] );

	foreach ( array( 'custom_title', 'custom_description', 'custom_keywords' ) as $field ) {
		$value = isset( $_POST[ $field ] ) ? stripslashes( (string) $_POST[ $field ] ) : '';

		// Without the legacy checkbox, an empty field means "not set" — don't
		// store an empty string that would read as a deliberate blank value.
		if ( ! $legacy_optin && trim( $value ) === '' ) {
			continue;
		}

		$options_data[ $field ] = $value;
		$iterations[]           = \ImproveSEO\Spintax::count( \ImproveSEO\Spintax::parse( $value ) );
	}
}

/**
 * Carry the AI generation settings across an update to an existing project.
 *
 * Every save path rebuilds $options_data from $_POST and then overwrites the
 * project's options column wholesale — it never merges with what is stored. The
 * ai_* values only ever reach $_POST from the AI popup's in-page state, which
 * the create form copies into empty hidden inputs on submit
 * (copyAIFieldsToHiddenInputs() in views/posting/create-post-single.php — the
 * inputs carry no value attribute, so they are blank on any later page load).
 *
 * So any subsequent save of the project posts those fields empty, they are
 * skipped, and the stored options lose them: the Project Details screen then
 * shows "These generation settings weren't recorded for this post" with N/A for
 * Details to Include, Call to Action and Focus Keyword. Reported as a draft
 * losing all its fields once published, because publishing routes through the
 * update path.
 *
 * These fields are a record of HOW the post was generated, not editable
 * settings, so restoring them is always the correct reading of a save that does
 * not mention them. Strictly additive: a key already present in $options_data is
 * never touched, so the create path and any genuine edit are unaffected.
 *
 * @param array $options_data Project options; modified in place.
 * @param int   $project_id   Project (task) being updated.
 */
function improveseo_preserve_generation_options( array &$options_data, $project_id ) {
	global $wpdb;

	$project_id = (int) $project_id;
	if ( ! $project_id ) {
		return;
	}

	$stored_raw = $wpdb->get_var(
		$wpdb->prepare(
			'SELECT options FROM ' . $wpdb->prefix . 'improveseo_tasks WHERE id = %d',
			$project_id
		)
	);
	if ( empty( $stored_raw ) ) {
		return;
	}

	// Stored as base64(json) — mirrors the Task model's 'array|b64' cast.
	$stored = json_decode( base64_decode( $stored_raw ), true );
	if ( ! is_array( $stored ) ) {
		return;
	}

	$generation_keys = array(
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
		'ai_for_testing_only',
		// On-Page SEO meta. The edit form (posting.form) renders no Meta Title/Description/
		// Keywords fields, so updating a draft (do_update_post) carries none and would drop
		// the wizard-generated meta. Preserve them from the stored project on any update that
		// didn't submit them — this is why a published-immediately post kept its meta but a
		// draft that was later edited lost it.
		'custom_title',
		'custom_description',
		'custom_keywords',
	);

	foreach ( $generation_keys as $key ) {
		$incoming_has_value = isset( $options_data[ $key ] ) && trim( (string) $options_data[ $key ] ) !== '';
		if ( $incoming_has_value ) {
			continue; // Never overwrite a value this save actually carried.
		}
		if ( isset( $stored[ $key ] ) && trim( (string) $stored[ $key ] ) !== '' ) {
			$options_data[ $key ] = $stored[ $key ];
		}
	}
}

/**
 * Ensure the bulk task table has the meta_title / meta_description columns.
 *
 * dbDelta (the version-gated installer path) is unreliable at ALTERing an
 * existing table to add columns, and once it bumps improveseo_db_version it
 * won't retry — leaving live sites with the new code but not the new columns.
 * This runs an explicit, idempotent ALTER and is safe to call on every request:
 * a one-row option short-circuits it once the columns are confirmed present.
 */
function improveseo_ensure_bulk_meta_columns() {
	global $wpdb;

	if ( get_option( 'improveseo_bulk_meta_cols' ) === 'ready' ) {
		return;
	}

	$table = $wpdb->prefix . 'improveseo_bulktasksdetails';

	// Brand-new install: the table itself may not exist yet — bail quietly.
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
		return;
	}

	$cols = $wpdb->get_col( "SHOW COLUMNS FROM `$table`" );
	if ( ! is_array( $cols ) ) {
		return;
	}

	$adds = array();
	if ( ! in_array( 'meta_title', $cols, true ) ) {
		$adds[] = "ADD COLUMN `meta_title` TEXT NULL DEFAULT NULL";
	}
	if ( ! in_array( 'meta_description', $cols, true ) ) {
		$adds[] = "ADD COLUMN `meta_description` TEXT NULL DEFAULT NULL";
	}

	if ( ! empty( $adds ) ) {
		$wpdb->query( "ALTER TABLE `$table` " . implode( ', ', $adds ) );
		if ( function_exists( 'my_plugin_log' ) ) {
			my_plugin_log( 'improveseo_ensure_bulk_meta_columns: added ' . implode( ', ', $adds ) . ( $wpdb->last_error ? ' | MySQL Error: ' . $wpdb->last_error : ' | OK' ) );
		}
		$cols = $wpdb->get_col( "SHOW COLUMNS FROM `$table`" );
	}

	// Only mark ready once both columns are confirmed present.
	if ( is_array( $cols ) && in_array( 'meta_title', $cols, true ) && in_array( 'meta_description', $cols, true ) ) {
		update_option( 'improveseo_bulk_meta_cols', 'ready' );
	}
}

/**
 * Widen improveseo_bulktasksdetails.published_on from varchar(12) to varchar(19).
 *
 * The original column can only hold a date ('YYYY-MM-DD'); a full publish
 * datetime ('YYYY-MM-DD HH:MM:SS', 19 chars) was silently truncated by MySQL,
 * which is why every publish time rendered as 00:00:00. Kept as varchar (not
 * DATETIME) because legacy rows contain '' and truncated junk that a type
 * conversion would reject under strict mode. Idempotent and option-guarded,
 * same pattern as improveseo_ensure_bulk_meta_columns().
 */
function improveseo_ensure_bulk_published_on_column() {
	global $wpdb;

	if ( get_option( 'improveseo_bulk_published_on_col' ) === 'ready' ) {
		return;
	}

	$table = $wpdb->prefix . 'improveseo_bulktasksdetails';

	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
		return;
	}

	$col = $wpdb->get_row( "SHOW COLUMNS FROM `$table` LIKE 'published_on'" );
	if ( ! $col ) {
		return;
	}

	if ( strtolower( $col->Type ) !== 'varchar(19)' ) {
		$wpdb->query( "ALTER TABLE `$table` MODIFY `published_on` varchar(19) DEFAULT NULL" );
		if ( function_exists( 'my_plugin_log' ) ) {
			my_plugin_log( 'improveseo_ensure_bulk_published_on_column: widened published_on to varchar(19)' . ( $wpdb->last_error ? ' | MySQL Error: ' . $wpdb->last_error : ' | OK' ) );
		}
		$col = $wpdb->get_row( "SHOW COLUMNS FROM `$table` LIKE 'published_on'" );
	}

	if ( $col && strtolower( $col->Type ) === 'varchar(19)' ) {
		update_option( 'improveseo_bulk_published_on_col', 'ready' );
	}
}

function improveseo_set_featured_image_from_url( $post_id, $image_url, $post_title = '' ) {
	if ( empty( $image_url ) || empty( $post_id ) ) {
		return false;
	}

	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	$upload_info = wp_upload_dir();
	$base_url    = untrailingslashit( $upload_info['baseurl'] );
	$base_dir    = untrailingslashit( $upload_info['basedir'] );

	// Normalise relative URLs to absolute so both the local-file check below and
	// the remote sideload fallback receive a fully-qualified URL.
	if ( strpos( $image_url, '//' ) === 0 ) {
		$image_url = ( is_ssl() ? 'https:' : 'http:' ) . $image_url; // protocol-relative
	} elseif ( strpos( $image_url, '/' ) === 0 ) {
		$image_url = home_url( $image_url );                          // root-relative
	}

	// --- Case 1: the file already lives in this site's uploads directory ------
	$local_file_path = '';
	if ( strpos( $image_url, $base_url ) === 0 ) {
		$relative  = ltrim( substr( $image_url, strlen( $base_url ) ), '/' );
		$candidate = $base_dir . '/' . $relative;
		if ( file_exists( $candidate ) ) {
			$local_file_path = $candidate;
		}
	}

	if ( $local_file_path ) {
		$file_type = wp_check_filetype( basename( $local_file_path ) );
		if ( empty( $file_type['type'] ) ) {
			error_log( 'improveseo_set_featured_image_from_url: unrecognised mime type for ' . $local_file_path );
			return false;
		}

		$attachment = array(
			'post_mime_type' => $file_type['type'],
			'post_title'     => sanitize_text_field( $post_title ?: pathinfo( $local_file_path, PATHINFO_FILENAME ) ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		);

		$attachment_id = wp_insert_attachment( $attachment, $local_file_path, $post_id );

		if ( is_wp_error( $attachment_id ) ) {
			error_log( 'improveseo_set_featured_image_from_url: wp_insert_attachment error — ' . $attachment_id->get_error_message() );
			return false;
		}

		$metadata = wp_generate_attachment_metadata( $attachment_id, $local_file_path );
		wp_update_attachment_metadata( $attachment_id, $metadata );
	} else {
		// --- Case 3: remote (or non-resident) URL — download into uploads -----
		if ( ! filter_var( $image_url, FILTER_VALIDATE_URL ) ) {
			error_log( 'improveseo_set_featured_image_from_url: not a valid URL, skipping — ' . $image_url );
			return false;
		}

		$desc          = $post_title ? sanitize_text_field( $post_title ) : null;
		$attachment_id = media_sideload_image( $image_url, $post_id, $desc, 'id' );

		if ( is_wp_error( $attachment_id ) ) {
			error_log( 'improveseo_set_featured_image_from_url: media_sideload_image error — ' . $attachment_id->get_error_message() . ' | url=' . $image_url );
			return false;
		}
	}

	$result = set_post_thumbnail( $post_id, $attachment_id );
	error_log( 'improveseo_set_featured_image_from_url: ' . ( $result ? '✅ set' : '❌ failed' ) . ' | post=' . $post_id . ' attachment=' . $attachment_id );

	return $result ? $attachment_id : false;
}


/***************************************************/

/***************** Generate AI Post ****************/

/*********************** Start *********************/

/***************************************************/



//function custom_plugin_enqueue_script() {

// Enqueue the script

wp_enqueue_script(

	'custom-plugin-script', // Script handle

	plugin_dir_url(__FILE__) . 'assets/js/custom-plugin-script.js', // Script URL

	array('jquery'), // Dependencies (optional)

	filemtime(plugin_dir_path(__FILE__) . 'assets/js/custom-plugin-script.js'), // Script version (optional)

	true // Load script in footer

);



// 20-05-24 start Code 

add_action('wp_ajax_add_category_form', 'add_category_form');



function add_category_form()
{



	if (isset($_POST['fData'])) {

		$cat_slug = $_POST['fData'];

		//$cat_slug = preg_replace('/\s*/', '-', $cat_slug);

		//$cat_slug = strtolower($cat_slug);

		wp_insert_term(

			// the name of the category

			$_POST['fData'],

			// the taxonomy, which in this case if category (don't change)

			'category',

			array(

				// what to use in the url for term archive

				'slug' => $_POST['fData'],

			)

		);

		$result = refreshCategoryData($_POST['fData']);



		if (is_wp_error($result)) {

			$response = array('success' => false, 'message' => $result->get_error_message());

		} else {

			$response = array('success' => true, 'message' => 'Category added successfully.', 'result' => $result);

		}

		wp_send_json($response);

	}

}

add_action('wp_ajax_refreshCategoryData', 'refreshCategoryData');



function refreshCategoryData($slug)
{

	$select = '';

	$args = array(

		"hide_empty" => 0,

		"type" => "post",

		"orderby" => "name",

		"order" => "ASC"

	);

	$cats = get_categories($args);

	// echo "<pre>";

	// print_r($cats);

	// echo "slug : ".$slug;

	// exit();

	foreach ($cats as $category) {

		if ($category->name == $slug) {

			$checked = 'checked  onclick="return false"';

			$select .= "<span class='category active'><input type='checkbox' " . $checked . " value='" . $category->term_id . "' id='" . $category->term_id . "' name='cats[]'><label style='margin:0px;' for='" . $category->term_id . "'>" . $category->name . "</label></span>";


			return $select;

		} else {

			$checked = '';

		}

	}

}








// multiple images

/**
 * Handle the wizard's multi-image upload.
 *
 * Admin-only by design: a missing nonce or capability is a hard stop, and every
 * file goes through wp_handle_upload() with an image-only allowlist so the server
 * decides the final extension. The previous version trusted mime_content_type()
 * alone and then wrote the client's own extension with a raw move_uploaded_file(),
 * which let a GIF/PHP polyglot land in uploads as an executable .php file.
 * Reported by Joao Ramos Maciel, 2026-07-24.
 */
function my_plugin_handle_upload()
{
	check_ajax_referer( 'improveseo_upload_nonce', 'nonce' );

	if ( ! current_user_can( 'upload_files' ) ) {
		wp_send_json_error( array( 'You are not allowed to upload files.' ), 403 );
	}

	if ( empty( $_FILES['images']['name'][0] ) ) {
		wp_send_json_error( array( 'No files selected.' ) );
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';

	// Extension => mime. wp_handle_upload() runs the pair through
	// wp_check_filetype_and_ext(), so a file whose real content disagrees with its
	// extension - or whose extension is absent from this list - is rejected before
	// it ever reaches the uploads directory. This is what stops the polyglot.
	$allowed_mimes = array(
		'jpg|jpeg|jpe' => 'image/jpeg',
		'png'          => 'image/png',
		'gif'          => 'image/gif',
		'webp'         => 'image/webp',
	);

	$uploaded_files = array();
	$errors         = array();

	$count = count( $_FILES['images']['name'] );

	for ( $i = 0; $i < $count; $i++ ) {
		if ( empty( $_FILES['images']['name'][ $i ] ) ) {
			continue;
		}

		$name = sanitize_file_name( $_FILES['images']['name'][ $i ] );

		$file = array(
			'name'     => $name,
			'type'     => $_FILES['images']['type'][ $i ],
			'tmp_name' => $_FILES['images']['tmp_name'][ $i ],
			'error'    => $_FILES['images']['error'][ $i ],
			'size'     => $_FILES['images']['size'][ $i ],
		);

		// Second layer: reject anything that is not a decodable image even if it
		// somehow carried an allowed extension.
		if ( false === @getimagesize( $file['tmp_name'] ) ) {
			$errors[] = "$name is not a valid image.";
			continue;
		}

		$result = wp_handle_upload(
			$file,
			array(
				'test_form' => false,
				'mimes'     => $allowed_mimes,
			)
		);

		if ( ! is_array( $result ) || isset( $result['error'] ) ) {
			$errors[] = $name . ': ' . ( is_array( $result ) ? $result['error'] : 'Upload failed.' );
			continue;
		}

		$uploaded_files[] = $result['url'];
	}

	if ( ! empty( $uploaded_files ) ) {
		wp_send_json_success( $uploaded_files );
	}

	wp_send_json_error( $errors );
}

add_action( 'wp_ajax_my_plugin_upload', 'my_plugin_handle_upload' );





class WC_Testimonial
{



	function __construct()
	{



		//add_action( 'admin_menu', 'custom_address_option_on_settings_tab' );



		add_action('admin_bar_menu', [$this, 'improve_seo_admin_top_bar_option'], 2000);



		add_action('admin_enqueue_scripts', array($this, 'load_admin_files'), 30);



		add_action('wp_ajax_wt_save_form_fields_for_testimonials', array($this, 'wt_save_form_fields_for_testimonials'));

		add_action('wp_ajax_wt_save_form_fields_for_googlemaps', array($this, 'wt_save_form_fields_for_googlemaps'));

		add_action('wp_ajax_wt_save_form_fields_for_buttons', array($this, 'wt_save_form_fields_for_buttons'));

		add_action('wp_ajax_wt_save_form_fields_for_videos', array($this, 'wt_save_form_fields_for_videos'));



		add_action('wp_ajax_delete_selected_data', array($this, 'delete_selected_data'));

		add_action('wp_ajax_kwdelete_selected_data_for_keyword', array($this, 'kwdelete_selected_data_for_keyword'));

		add_action('wp_ajax_kwdownload_selected_data_for_keyword', array($this, 'kwdownload_selected_data_for_keyword'));





		add_action('wp_ajax_edit_selected_data', array($this, 'edit_selected_data'));

		add_action('wp_ajax_sw_saved_search_results_keyword', array($this, 'sw_saved_search_results_keyword'));



		//shortcode for things testimonials / MAPS / Buttons

		add_shortcode('improveseo_testimonial', array($this, 'testimonial_callback'));

		add_shortcode('improveseo_googlemaps', array($this, 'maps_callback'));

		add_shortcode('improveseo_buttons', array($this, 'button_callback'));

		add_shortcode('improveseo_video', array($this, 'video_callback'));



	}







	/**

		   * admin bar.

		   * @return void.

		   */

	public function improve_seo_admin_top_bar_option()
	{

		global $wp_admin_bar;

		$menu_id = 'improveseo_dashboard';

		$wp_admin_bar->add_menu(array(

			'id' => $menu_id,

			'title' => __('Improve SEO', 'improve-seo'),

			'href' => admin_url() . '/admin.php?page=improveseo_dashboard',

		));

	}



	/****=====Download the data against ID of keyword from admin side====***/

	function kwdownload_selected_data_for_keyword()
	{



		$keywordproj_id = isset($_REQUEST['kw_rand_id']) ? $_REQUEST['kw_rand_id'] : '';

		if (empty($keywordproj_id)) {

			return;

		}

		$keyword_data = get_option('swsaved_keywords_with_results_' . $keywordproj_id);

		$proj_name = $keyword_data['proj_name'];

		$proj_content = $keyword_data['search_results'];



		$proj_name = str_replace(' ', '-', $proj_name);

		$args = array(

			'status' => 'success',

			'proj_name' => $proj_name,

			'proj_content' => $proj_content,

		);

		wp_send_json($args);

		die(0);

	}







	/****=====SAving the data found with keywords====***/

	function sw_saved_search_results_keyword()
	{

		global $wpdb;

		$proj_name = isset($_REQUEST['proj_name']) ? $_REQUEST['proj_name'] : '';

		$search_results = isset($_REQUEST['search_results']) ? $_REQUEST['search_results'] : '';



		//    $rand_no = $this->create_random_number();

		//    $save_keyword_data = array(

		//            'proj_name' => $proj_name,

		//            'search_results' => $search_results,

		//       );

		//    update_option('swsaved_keywords_with_results_'.$rand_no , $save_keyword_data);



		// 	//saving random numbers too

		// 	$random_no_arr = get_option('swsaved_random_nosofkeywords');

		// 	$random_no_arr[] = $rand_no;

		// 	$result = array_unique($random_no_arr);



		// 	update_option('swsaved_random_nosofkeywords' , $result );

		/////////////////





		$list = trim(stripslashes($search_results));

		$list_size = sizeof(explode("\n", $search_results));

		$name = $proj_name;







		$wpdb->insert($wpdb->prefix . "improveseo_lists", array(



			'name' => $name,

			'list' => $list,

			'size' => $list_size,

			'created_at' => date('Y-m-d h:m:s')

		));

		$inserted_id = $wpdb->insert_id;



		/////////////////

		wp_send_json_success(array(

			'status' => 'success',

			'id' => $inserted_id,

			'proj_name' => $proj_name,

			'search_results' => $search_results,

		));

		wp_send_json($args);

		die(0);

	}





	/****=====SAving the data found with keywords====***/

	// function sw_saved_search_results_keyword(){



	// 	$proj_name = isset($_REQUEST['proj_name']) ? $_REQUEST['proj_name'] : '';

	// 	$search_results = isset($_REQUEST['search_results']) ? $_REQUEST['search_results'] : '';



	// 	$rand_no = $this->create_random_number();

	// 	$save_keyword_data = array(

	// 			'proj_name' => $proj_name,

	// 			'search_results' => $search_results,

	// 	);

	// 	update_option('swsaved_keywords_with_results_'.$rand_no , $save_keyword_data);



	// 			//saving random numbers too

	// 	$random_no_arr = get_option('swsaved_random_nosofkeywords');



	// 	$random_no_arr[] = $rand_no;

	// 	$result = array_unique($random_no_arr);



	// 	update_option('swsaved_random_nosofkeywords' , $result );



	// 	$args = array(

	// 			'status' => 'success',

	// 		);

	// 	wp_send_json($args);

	// 	die(0);



	// }



	/****=====Notice on how to use the shortcodes====***/



	function general_admin_notice()
	{



		echo '<div class="notice notice-warning is-dismissible notice-improveseo">

              <p><b>1)</b>For Testimonials You can use the shortcode as <b>[improveseo_testimonial id="YOURID"]</b> 

              	You can Add multiple Ids for testimonials just like <b>[improveseo_testimonial id="YOURID_1 , YOURID_2 , YOURID_3"]</b><br><br>

              	<b>2)</b>For Buttons You can use the shortcode as <b>[improveseo_buttons id="YOURID"]</b> 

              	You can Add multiple Ids for buttons just like <b>[improveseo_buttons id="YOURID_1 , YOURID_2 , YOURID_3"]</b><br><br>

              	<b>3)</b>For Google Maps You can use the shortcode as <b>[improveseo_googlemaps id="YOURID" address="YOURADDRESS" title="YOURTITLE" ]</b>

              </p>

          </div>';

	}



	/****=====REndereing the Google Maps on front end against Shortcode====***/

	function maps_callback($atts)
	{





		$sc_att = shortcode_atts(

			array(

				'id' => null,

				'address' => null,

				'title' => null,

			),

			$atts

		);



		$id = $sc_att['id'];

		$id = $new_str = str_replace(' ', '', $id);



		$address = $sc_att['address'];

		$title = isset($sc_att['title']) ? $sc_att['title'] : '';



		if (empty($id || $address)) {

			return;

		}



		$id = explode(',', $id);

		$args = array(

			'id' => $id,

			'title' => $title,

			'address' => $address,

		);





		ob_start();

		wt_load_templates('googlemaps.php', $args);

		$html = ob_get_contents();

		ob_end_clean();

		return $html;

	}



	/****=====getting the longitude and latitudepoints====***/

	function getDistance($addressFrom, $apiKey)
	{





		// Change address format

		$formattedAddrFrom = str_replace(' ', '+', $addressFrom);



		// Geocoding API request with start address

		$geocodeFrom = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address=' . $formattedAddrFrom . '&sensor=false&key=' . $apiKey);

		$outputFrom = json_decode($geocodeFrom);

		if (!empty($outputFrom->error_message)) {

			return $outputFrom->error_message;

		}





		// Get latitude and longitude from the geodata

		$latitudeFrom = $outputFrom->results[0]->geometry->location->lat;

		$longitudeFrom = $outputFrom->results[0]->geometry->location->lng;

		$resp = array(

			'latitude' => $latitudeFrom,

			'longitude' => $longitudeFrom,

		);

		return $resp;

	}



	/****=====REndereing the Buttons on front end against Shortcode====***/

	function button_callback($atts)
	{



		$sc_att = shortcode_atts(

			array(

				'id' => null,

			),

			$atts

		);



		$id = $sc_att['id'];

		$id = $new_str = str_replace(' ', '', $id);

		if (empty($id)) {

			return;

		}



		$id = explode(',', $id);

		$args = array(

			'id' => $id

		);





		ob_start();

		wt_load_templates('buttons.php', $args);

		$html = ob_get_contents();

		ob_end_clean();

		return $html;

	}





	/****=====REndereing the Testimonial on front end against Shortcode====***/

	function testimonial_callback($atts)
	{



		$sc_att = shortcode_atts(

			array(

				'id' => null,

			),

			$atts

		);



		$id = $sc_att['id'];

		$id = $new_str = str_replace(' ', '', $id);

		if (empty($id)) {

			return;

		}



		$id = explode(',', $id);

		$args = array(

			'id' => $id

		);





		ob_start();

		wt_load_templates('testimonials.php', $args);

		$html = ob_get_contents();

		ob_end_clean();

		return $html;

	}





	function video_callback($atts)
	{

		$sc_att = shortcode_atts(

			array(

				'id' => null,

			),

			$atts

		);



		$id = $sc_att['id'];

		$id = $new_str = str_replace(' ', '', $id);

		if (empty($id)) {

			return;

		}



		$id = explode(',', $id);

		$args = array(

			'id' => $id

		);





		ob_start();

		wt_load_templates('videos.php', $args);

		$html = ob_get_contents();

		ob_end_clean();

		return $html;

	}





	/****=====Edit/Updating the selected data====***/

	function edit_selected_data()
	{



		$rand_id = isset($_REQUEST['rand_id']) ? $_REQUEST['rand_id'] : '';

		$page_url = isset($_REQUEST['page_url']) ? $_REQUEST['page_url'] : '';

		$btn_action = isset($_REQUEST['btn_action']) ? $_REQUEST['btn_action'] : '';

		if (empty($rand_id)) {

			return;

		}



		$url_param = array(

			'rand_id' => $rand_id,

			'action' => $btn_action

		);

		$page_url = add_query_arg($url_param, $page_url);

		wp_send_json(array('status' => 'success', 'page_url' => $page_url));

	}



	/****=====Deleting the selected data====***/

	function delete_selected_data()
	{



		$no_tobe_dlt = isset($_REQUEST['rand_id']) ? $_REQUEST['rand_id'] : '';

		if (empty($no_tobe_dlt)) {

			return;

		}



		$saved_random_nos = get_option('get_saved_random_numbers');

		if (in_array($no_tobe_dlt, $saved_random_nos)) {



			delete_option('get_testimonials_' . $no_tobe_dlt);

			$result = $this->delete_el_from_array($saved_random_nos, $no_tobe_dlt);

			update_option('get_saved_random_numbers', $result);



			$url_param = array(

				'action' => 'deleted'

			);

			$url = admin_url('admin.php?page=improveseo_shortcodes');

			$page_url = add_query_arg($url_param, $url);





			wp_send_json(array('status' => 'success', 'url' => $page_url));

		}



	}



	/****=====Deleting the selected data for keyword on admin side====***/

	function kwdelete_selected_data_for_keyword()
	{



		$no_tobe_dlt = isset($_REQUEST['kw_rand_id']) ? $_REQUEST['kw_rand_id'] : '';

		if (empty($no_tobe_dlt)) {

			return;

		}



		$saved_random_nos = get_option('swsaved_random_nosofkeywords');

		if (in_array($no_tobe_dlt, $saved_random_nos)) {



			delete_option('swsaved_keywords_with_results_' . $no_tobe_dlt);

			$result = $this->delete_el_from_array($saved_random_nos, $no_tobe_dlt);



			update_option('swsaved_random_nosofkeywords', $result);

			wp_send_json(array('status' => 'success'));

		}



	}



	/****=====Deleting Some element from Array====***/

	function delete_el_from_array($my_array, $remove_el)
	{



		$pos = array_search($remove_el, $my_array);



		unset($my_array[$pos]);

		return $my_array;

	}



	/****=====Load Admin JS And CSS files====***/

	function load_admin_files()
	{

		wp_enqueue_style('improveseo_style', WT_URL . "/assets/css/improveseo_style.css", array(), '1.1');

		wp_enqueue_style("poppins_fonts", "https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap");

		wp_enqueue_script('tmm_script_js', WT_URL . "/assets/js/wt-script.js", array('jquery'), improveseo_asset_ver('assets/js/wt-script.js'), true);

		wp_enqueue_script('tmm_sweeetalertscript_js', WT_URL . "/assets/js/wt-sweetalert.js", array('jquery'));



		wp_localize_script(

			'tmm_script_js',

			'ajax_vars',

			array(

				'ajax_url' => admin_url('admin-ajax.php'),

			)

		);



	}



	/****=====Saving Form Fields From Admin Side For Button====***/

	function wt_save_form_fields_for_buttons()
	{



		$rand_no = isset($_REQUEST['updateandedit_data']) ? $_REQUEST['updateandedit_data'] : '';



		$tw_button_shortcode_name = isset($_REQUEST['tw_button_shortcode_name']) ? $_REQUEST['tw_button_shortcode_name'] : '';

		$tw_btn_text = isset($_REQUEST['tw_btn_text']) ? $_REQUEST['tw_btn_text'] : '';

		$tw_btn_link = isset($_REQUEST['tw_btn_link']) ? $_REQUEST['tw_btn_link'] : '';

		$tw_buttontxt_color = isset($_REQUEST['tw_buttontxt_color']) ? $_REQUEST['tw_buttontxt_color'] : '';

		$tw_buttonbg_color = isset($_REQUEST['tw_buttonbg_color']) ? $_REQUEST['tw_buttonbg_color'] : '';

		$tw_button_outline_color = isset($_REQUEST['tw_button_outline_color']) ? $_REQUEST['tw_button_outline_color'] : '#ffffff';

		$tw_button_size = isset($_REQUEST['tw_button_size']) ? $_REQUEST['tw_button_size'] : 'sm';

		$tw_button_border_type = isset($_REQUEST['tw_button_border_type']) ? $_REQUEST['tw_button_border_type'] : 'square';



		$tw_button_type = isset($_REQUEST['tw_button_type']) ? $_REQUEST['tw_button_type'] : 'normal_btn';

		$tw_tap_to_call_img_source = isset($_REQUEST['tw_tap_to_call_img_source']) ? $_REQUEST['tw_tap_to_call_img_source'] : '';

		$tw_tap_btn_text = isset($_REQUEST['tw_tap_btn_text']) ? $_REQUEST['tw_tap_btn_text'] : '';

		$tw_tap_btn_number = isset($_REQUEST['tw_tap_btn_number']) ? $_REQUEST['tw_tap_btn_number'] : '';



		$arr = array(

			'tw_button_shortcode_name' => $tw_button_shortcode_name,

			'tw_maps_apikey' => $tw_maps_apikey,

			'tw_btn_text' => $tw_btn_text,

			'tw_btn_link' => $tw_btn_link,

			'tw_buttontxt_color' => $tw_buttontxt_color,

			'tw_buttonbg_color' => $tw_buttonbg_color,

			'tw_button_outline_color' => $tw_button_outline_color,

			'tw_button_size' => $tw_button_size,

			'tw_button_border_type' => $tw_button_border_type,

			'tw_button_type' => $tw_button_type,

			'tw_tap_to_call_img_source' => $tw_tap_to_call_img_source,

			'tw_tap_btn_text' => $tw_tap_btn_text,

			'tw_tap_btn_number' => $tw_tap_btn_number,



		);



		if (empty($rand_no)) {

			$rand_no = $this->create_random_number();

		}

		update_option('get_buttons_' . $rand_no, $arr);



		//saving random numbers too

		$random_no_arr = get_option('get_saved_random_numbers');



		$random_no_arr[] = $rand_no;

		$result = array_unique($random_no_arr);

		update_option('get_saved_random_numbers', $result);

		$url = admin_url('admin.php?page=improveseo_shortcodes');

		wp_send_json(array('status' => 'success', 'url' => $url));

		die;

	}



	/****=====Saving Form Fields From Admin Side For Googlemaps====***/

	function wt_save_form_fields_for_googlemaps()
	{



		$rand_no = isset($_REQUEST['updateandedit_data']) ? $_REQUEST['updateandedit_data'] : '';

		$tw_maps_apikey = isset($_REQUEST['tw_maps_apikey']) ? $_REQUEST['tw_maps_apikey'] : '';

		$tw_maps_shortcode_name = isset($_REQUEST['tw_maps_shortcode_name']) ? $_REQUEST['tw_maps_shortcode_name'] : '';



		$arr = array(

			'tw_maps_shortcode_name' => $tw_maps_shortcode_name,

			'tw_maps_apikey' => $tw_maps_apikey,

		);



		if (empty($rand_no)) {

			$rand_no = $this->create_random_number();

		}

		update_option('get_googlemaps_' . $rand_no, $arr);



		//saving random numbers too

		$random_no_arr = get_option('get_saved_random_numbers');



		$random_no_arr[] = $rand_no;

		$result = array_unique($random_no_arr);

		update_option('get_saved_random_numbers', $result);

		$url = admin_url('admin.php?page=improveseo_shortcodes');

		wp_send_json(array('status' => 'success', 'url' => $url));

		die;

	}



	/****=====Saving Form Fields From Admin Side For testimonials====***/

	function wt_save_form_fields_for_testimonials()
	{



		$rand_no = isset($_REQUEST['updateandedit_data']) ? $_REQUEST['updateandedit_data'] : '';



		$tw_testi_shortcode_name = isset($_REQUEST['tw_testi_shortcode_name']) ? $_REQUEST['tw_testi_shortcode_name'] : '';

		$testi_img_src = isset($_REQUEST['img_source']) ? $_REQUEST['img_source'] : '';

		$tw_testi_content = isset($_REQUEST['tw_testi_content']) ? $_REQUEST['tw_testi_content'] : '';

		$tw_testi_name = isset($_REQUEST['tw_testi_name']) ? $_REQUEST['tw_testi_name'] : '';

		$tw_testi_position = isset($_REQUEST['tw_testi_position']) ? $_REQUEST['tw_testi_position'] : '';

		$tw_box_color = isset($_REQUEST['tw_box_color']) ? $_REQUEST['tw_box_color'] : '';

		$tw_font_color = isset($_REQUEST['tw_font_color']) ? $_REQUEST['tw_font_color'] : '';

		$tw_testi_outline_color = isset($_REQUEST['tw_testi_outline_color']) ? $_REQUEST['tw_testi_outline_color'] : '#000000';





		$arr = array(

			'tw_testi_shortcode_name' => $tw_testi_shortcode_name,

			'testi_img_src' => $testi_img_src,

			'tw_testi_content' => $tw_testi_content,

			'tw_testi_name' => $tw_testi_name,

			'tw_testi_position' => $tw_testi_position,

			'tw_box_color' => $tw_box_color,

			'tw_font_color' => $tw_font_color,

			'tw_testi_outline_color' => $tw_testi_outline_color,

		);



		if (empty($rand_no)) {

			$rand_no = $this->create_random_number();

		}

		update_option('get_testimonials_' . $rand_no, $arr);



		//saving random numbers too

		$random_no_arr = get_option('get_saved_random_numbers');



		$random_no_arr[] = $rand_no;

		$result = array_unique($random_no_arr);

		update_option('get_saved_random_numbers', $result);



		$url = admin_url('admin.php?page=improveseo_shortcodes');

		wp_send_json(array('status' => 'success', 'url' => $url));

		die;

	}



	/****=====Saving Form Fields From Admin Side For videos====***/

	function wt_save_form_fields_for_videos()
	{

		$rand_no = isset($_REQUEST['updateandedit_data']) ? $_REQUEST['updateandedit_data'] : '';



		$video_shortcode_name = isset($_REQUEST['video_shortcode_name']) ? $_REQUEST['video_shortcode_name'] : '';

		$video_type = isset($_REQUEST['video_type']) ? $_REQUEST['video_type'] : 'upload_video';



		$video_poster_img_source = isset($_REQUEST['video_poster_img_source']) ? $_REQUEST['video_poster_img_source'] : '';

		$video_poster_img_id = isset($_REQUEST['video_poster_img_id']) ? $_REQUEST['video_poster_img_id'] : '';



		$video_id_mp4 = isset($_REQUEST['video_id_mp4']) ? $_REQUEST['video_id_mp4'] : '';

		$video_url_mp4 = isset($_REQUEST['video_url_mp4']) ? $_REQUEST['video_url_mp4'] : '';



		$video_id_ogv = isset($_REQUEST['video_id_ogv']) ? $_REQUEST['video_id_ogv'] : '';

		$video_url_ogv = isset($_REQUEST['video_url_ogv']) ? $_REQUEST['video_url_ogv'] : '';



		$video_id_webm = isset($_REQUEST['video_id_webm']) ? $_REQUEST['video_id_webm'] : '';

		$video_url_webm = isset($_REQUEST['video_url_webm']) ? $_REQUEST['video_url_webm'] : '';



		$video_url_vimeo = isset($_REQUEST['video_url_vimeo']) ? $_REQUEST['video_url_vimeo'] : '';

		$video_url_youtube = isset($_REQUEST['video_url_youtube']) ? $_REQUEST['video_url_youtube'] : '';



		$video_autoplay = isset($_REQUEST['video_autoplay']) ? $_REQUEST['video_autoplay'] : 'no';

		$video_muted = isset($_REQUEST['video_muted']) ? $_REQUEST['video_muted'] : 'no';

		$video_controls = isset($_REQUEST['video_controls']) ? $_REQUEST['video_controls'] : 'no';

		$video_loop = isset($_REQUEST['video_loop']) ? $_REQUEST['video_loop'] : 'no';

		$video_height = isset($_REQUEST['video_height']) ? $_REQUEST['video_height'] : 'auto';

		$video_width = isset($_REQUEST['video_width']) ? $_REQUEST['video_width'] : '100%';



		$arr = array(

			'video_shortcode_name' => $video_shortcode_name,

			'video_type' => $video_type,

			'video_poster_img_source' => $video_poster_img_source,

			'video_poster_img_id' => $video_poster_img_id,

			'video_id_mp4' => $video_id_mp4,

			'video_url_mp4' => $video_url_mp4,

			'video_id_ogv' => $video_id_ogv,

			'video_url_ogv' => $video_url_ogv,

			'video_id_webm' => $video_id_webm,

			'video_url_webm' => $video_url_webm,

			'video_url_vimeo' => $video_url_vimeo,

			'video_url_youtube' => $video_url_youtube,

			'video_autoplay' => $video_autoplay,

			'video_muted' => $video_muted,

			'video_controls' => $video_controls,

			'video_loop' => $video_loop,

			'video_height' => $video_height,

			'video_width' => $video_width,

		);



		if (empty($rand_no)) {

			$rand_no = $this->create_random_number();

		}

		update_option('get_videos_' . $rand_no, $arr);



		//saving random numbers too

		$random_no_arr = get_option('get_saved_random_numbers');



		$random_no_arr[] = $rand_no;

		$result = array_unique($random_no_arr);

		update_option('get_saved_random_numbers', $result);



		$url = admin_url('admin.php?page=improveseo_shortcodes');

		wp_send_json(array('status' => 'success', 'url' => $url));

		die;

	}



	/****=====Creating The Random Number====***/

	function create_random_number()
	{



		$rand_no = wp_rand(1, 76000);

		$multi = wp_rand(4, 140);

		$plus = wp_rand(0, 140007);

		$final_no = ($rand_no * $multi) + $plus;

		return $final_no;



	}





	/****=======load files on frontend page======****/

	function load_script_style_files()
	{



		wp_enqueue_style('tmm_stlye_css', WT_URL . "/css/wt-style.css", true);

		wp_enqueue_script('tmm_script_js', WT_URL . "/js/wt-script.js", array('jquery'), improveseo_asset_ver('js/wt-script.js'), true);



		//sweet alert

		wp_enqueue_script('tmm_sweetalerttt', WT_URL . "/js/wt-sweetalert.js", array('jquery'));

		wp_localize_script(

			'tmm_script_js',

			'ajax_vars',

			array(

				'ajax_url' => admin_url('admin-ajax.php'),

			)

		);



	}









































	// function createMediumContent($AudienceData,$LSI_Keyords,$seed_keyword, $keyword_selection, $seed_options, $nos_of_words, $content_lang, $shortcode='',$is_single_keyword = '',$voice_tone = '',$point_of_view = '',$title='',$call_to_action = '',$details_to_include = '')

	// 	{



	// 	}



















	// function createLargeContent($AudienceData,$LSI_Keyords,$seed_keyword, $keyword_selection, $seed_options, $nos_of_words, $content_lang, $shortcode='',$is_single_keyword = '',$voice_tone = '',$point_of_view = '',$title='',$call_to_action = '',$details_to_include = '')

	// 	{



	// 	}





	//    }

}



new WC_Testimonial;