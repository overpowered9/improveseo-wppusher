<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


if (file_exists(dirname(__FILE__) . '/single_and_bulk_AI_post_function.php'))

	include_once dirname(__FILE__) . '/single_and_bulk_AI_post_function.php';
include_once dirname(__FILE__) . '/GenerateAIpopup.php';

add_action('wp_ajax_getaaldata', 'getaaldata');

function getaaldata()
{
	check_ajax_referer( 'improveseo_ajax', 'nonce' );
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_send_json_error( array( 'message' => 'Insufficient permissions.' ), 403 );
	}

	$arr = [];

	wp_parse_str($_POST['value'], $arr);

	// utf8_decode() removed: deprecated in PHP 8.2 and removed in 9.0, and a no-op
	// here anyway - nos_of_words is a numeric word count, pure ASCII.
	$nos_of_words = urldecode($arr['nos_of_words']);

	$seed_keyword = $arr['seed_keyword'];

	$keyword_selection = $arr['keyword_selection'];

	$seed_options = $arr['seed_options'];

	$voice_tone = $arr['content_type'];

	$point_of_view = $arr['point_of_view'];

	$call_to_action = $arr['call_to_action'];

	$for_testing_only = $arr['for_testing_only'];



	$details_to_include = isset($arr['details_to_include']) ? $arr['details_to_include'] : '';

	$content_lang = $arr['content_lang'];

	if (!empty($arr['maintitlearea'])) {

		$ai_title = $arr['maintitlearea'];

	} else {

		$ai_title = $arr['aigeneratedtitle'];

	}



	if ($ai_title == '') {

		$search_data = $arr['seed_keyword'];

	} else {

		$search_data = $ai_title;

	}

	// ----- v2 fields -----
	$niche = ( isset( $arr['niche'] ) && $arr['niche'] !== '' ) ? sanitize_text_field( $arr['niche'] ) : 'general_blog';
	// "Other" niche: pass the user's typed-in niche through so the writer gets real
	// signal instead of the literal string "other".
	if ( $niche === 'other' && isset( $arr['niche_other'] ) ) {
		$niche_custom = sanitize_text_field( $arr['niche_other'] );
		if ( $niche_custom !== '' ) {
			$niche = $niche_custom;
		}
	}
	$cta_url = isset( $arr['cta_url'] ) ? trim( $arr['cta_url'] ) : '';
	// Normalize so an unschemed URL like "example.com" becomes "https://example.com"
	// and anything invalid is dropped rather than breaking generation.
	if ( $cta_url !== '' && function_exists( 'improveseo_normalize_cta_url' ) ) {
		$cta_url = improveseo_normalize_cta_url( $cta_url );
	}

	// Collect the structured per-niche fields (rendered in Step 2, named nd_<id>) into labelled
	// niche_data for the v2 writer. Falls back to the legacy freeform details field if present.
	$niche_data = array();
	foreach ( $arr as $nd_key => $nd_val ) {
		if ( strpos( $nd_key, 'nd_' ) === 0 ) {
			$nd_val = is_array( $nd_val ) ? implode( ', ', $nd_val ) : trim( (string) $nd_val );
			if ( $nd_val !== '' ) {
				$niche_data[ substr( $nd_key, 3 ) ] = sanitize_text_field( $nd_val );
			}
		}
	}
	if ( empty( $niche_data ) && ! empty( $details_to_include ) ) {
		$niche_data['details'] = $details_to_include;
	}
	if ( ! empty( $voice_tone ) ) {
		$niche_data['preferred_tone'] = $voice_tone;
	}

	// Build a brand profile from the onboarding business setup (optional, enriches local SEO).
	$brand_profile = array();
	$bp_city    = get_option( 'improveseo_business_city', '' );
	$bp_service = get_option( 'improveseo_business_service', '' );
	if ( $bp_city ) {
		$brand_profile['location'] = array( 'city' => $bp_city );
	}
	if ( $bp_service ) {
		$brand_profile['services'] = array( $bp_service );
	}

	$generation_result = createAIpost2(

		$seed_keyword,

		$keyword_selection,

		$seed_options,

		$nos_of_words,

		$content_lang,

		$shortcode = '',

		1,

		$voice_tone,

		$point_of_view,

		$search_data,

		$call_to_action,

		$details_to_include,

		$for_testing_only,

		$niche,

		$niche_data,

		$brand_profile,

		$cta_url

	);

// Extract values from the result array
$content = $generation_result['content'];
$meta_title = $generation_result['meta_title'];
$meta_descreption = $generation_result['meta_description'];

// Fallback to separate GPT calls only if server didn't return meta data
if (empty($meta_title)) {
	$meta_title = generateMetaTitle($arr['ai_tittle'], $arr['seed_keyword']);
}
if (empty($meta_descreption)) {
	$meta_descreption = generateMetaDescreption($arr['ai_tittle'], $arr['seed_keyword'], $content);
}

// Meta title must stay within the SEO limit (ideal 50–60, hard max 60 chars). If it's
// over, rerun the meta-title prompt a few times; a final word-boundary trim guarantees
// it is never above the limit.
$meta_title = improveseo_enforce_meta_title_length($meta_title, $search_data, $seed_keyword);

// Meta description must stay within the SEO limit (ideal 150–160, hard max 160 chars).
// If it's over, rerun the meta-description prompt a few times; a final word-boundary
// trim guarantees it is never above the limit.
$meta_descreption = improveseo_enforce_meta_description_length($meta_descreption, $search_data, $seed_keyword, $content);

//$content = convert_emails_to_links($content);

// $content = convert_urls_to_links($content);

wp_send_json_success(array("search_data" => $search_data, "content" => $content, "meta_title" => $meta_title, "meta_descreption" => $meta_descreption));
}

function generateMetaDescreption($aigeneratedtitle, $seed_keyword, $content = '')
{
	return improveseo_call_auxiliary_api( 'meta_description', array(
		'seed_keyword' => (string) $seed_keyword,
		'title'        => (string) $aigeneratedtitle,
		'content'      => (string) $content,
	) );
}

/**
 * Keep a meta description within the SEO limit (ideal 150–160, hard max 160 chars).
 * If it is over the limit, rerun the meta-description prompt up to $max_tries times
 * (the auxiliary route does not charge credits). As a final guard, trim to $max on a
 * word boundary so the result is never above the limit.
 */
function improveseo_enforce_meta_description_length( $meta, $title, $seed_keyword, $content, $max = 160, $max_tries = 3 )
{
	$meta   = trim( (string) $meta );
	$strlen = function ( $s ) { return function_exists( 'mb_strlen' ) ? mb_strlen( $s ) : strlen( $s ); };

	// Rerun the meta-description prompt until it fits, or we run out of tries.
	$tries = 0;
	while ( $strlen( $meta ) > $max && $tries < $max_tries ) {
		$tries++;
		$regen = trim( (string) generateMetaDescreption( $title, $seed_keyword, $content ) );
		if ( $regen === '' ) {
			break; // API returned nothing; stop and fall through to the trim guard
		}
		$meta = $regen;
	}

	// Final safety net: never exceed the limit — trim to $max, then back to the last
	// full word, and drop any dangling punctuation so it still reads cleanly.
	if ( $strlen( $meta ) > $max ) {
		$cut   = function_exists( 'mb_substr' ) ? mb_substr( $meta, 0, $max ) : substr( $meta, 0, $max );
		$cut   = rtrim( $cut );
		$space = function_exists( 'mb_strrpos' ) ? mb_strrpos( $cut, ' ' ) : strrpos( $cut, ' ' );
		if ( $space !== false && $space >= ( $max - 40 ) ) {
			$cut = function_exists( 'mb_substr' ) ? mb_substr( $cut, 0, $space ) : substr( $cut, 0, $space );
		}
		$meta = rtrim( $cut, " ,;:" );
	}

	return $meta;
}

function generateMetaTitle($aigeneratedtitle, $seed_keyword)
{
	return improveseo_call_auxiliary_api( 'meta_title', array(
		'seed_keyword' => (string) $seed_keyword,
		'title'        => (string) $aigeneratedtitle,
	) );
}

/**
 * Keep a meta title within the SEO limit (ideal 50–60, hard max 60 chars). If it is over
 * the limit, rerun the meta-title prompt up to $max_tries times (the auxiliary route does
 * not charge credits). As a final guard, trim to $max on a word boundary so the result is
 * never above the limit.
 */
function improveseo_enforce_meta_title_length( $meta, $title, $seed_keyword, $max = 60, $max_tries = 3 )
{
	$meta   = trim( (string) $meta );
	$strlen = function ( $s ) { return function_exists( 'mb_strlen' ) ? mb_strlen( $s ) : strlen( $s ); };

	// Rerun the meta-title prompt until it fits, or we run out of tries.
	$tries = 0;
	while ( $strlen( $meta ) > $max && $tries < $max_tries ) {
		$tries++;
		$regen = trim( (string) generateMetaTitle( $title, $seed_keyword ) );
		if ( $regen === '' ) {
			break; // API returned nothing; stop and fall through to the trim guard
		}
		$meta = $regen;
	}

	// Final safety net: never exceed the limit — trim to $max, then back to the last
	// full word, and drop any dangling punctuation so it still reads cleanly.
	if ( $strlen( $meta ) > $max ) {
		$cut   = function_exists( 'mb_substr' ) ? mb_substr( $meta, 0, $max ) : substr( $meta, 0, $max );
		$cut   = rtrim( $cut );
		$space = function_exists( 'mb_strrpos' ) ? mb_strrpos( $cut, ' ' ) : strrpos( $cut, ' ' );
		if ( $space !== false && $space >= ( $max - 20 ) ) {
			$cut = function_exists( 'mb_substr' ) ? mb_substr( $cut, 0, $space ) : substr( $cut, 0, $space );
		}
		$meta = rtrim( $cut, " ,;:" );
	}

	return $meta;
}


function ChatGPTCall($question)
{

	global $wpdb, $user_ID;



	// Your OpenAI API key

	$apiKey = get_option('improveseo_chatgpt_api_key');

	$apiUrl = 'https://api.openai.com/v1/chat/completions';

	// Your chat messages

	$messages = [

		// ['role' => 'system', 'content' => 'You are a helpful assistant.'],

		['role' => 'user', 'content' => $question]

		// ['role' => 'assistant', 'content' => 'Hello, how can I help you today?'],

	];





	// Additional parameters, including language setting (replace with actual parameters)

	$data = [

		'messages' => $messages,

		'model' => "gpt-4o"



		//'language' => 'fr',  // Specify the result language as French

	];



	// Set up cURL

	$ch = curl_init($apiUrl);



	// Set cURL options

	curl_setopt($ch, CURLOPT_POST, 1);

	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	curl_setopt($ch, CURLOPT_HTTPHEADER, [

		'Content-Type: application/json',

		'Authorization: Bearer ' . $apiKey,

	]);



	// Execute the cURL request

	$response = curl_exec($ch);



	// Check for cURL errors

	if (curl_errno($ch)) {

		echo 'Curl error: ' . curl_error($ch);

	}



	// Close cURL session

	curl_close($ch);



	// Decode and display the response

	$result = json_decode($response, true);



	// print_r($result);

	// die();





	$content = preg_replace('~^[\'"]?(.*?)[\'"]?$~', '$1', $result['choices'][0]['message']['content']);

	return $content;



}
function createAIpost2($seed_keyword, $keyword_selection, $seed_options, $nos_of_words, $content_lang, $shortcode = '', $is_single_keyword = '', $voice_tone = '', $point_of_view = '', $title = '', $call_to_action = '', $details_to_include = '', $for_testing_only = '', $niche = 'general_blog', $niche_data = array(), $brand_profile = array(), $cta_url = '')
{
	global $wpdb, $user_ID;

	// Get API credentials from WordPress options
	$api_key = get_option('improveseo_api_key');
	$site_code = get_option('improveseo_site_code');

	// Validate credentials
	if (empty($api_key) || empty($site_code)) {
		error_log("createAIpost2 Error: Missing API credentials. Please configure API Key and Site Code in settings.");
		return array(
			'content' => "Error: Missing API credentials. Please configure your API Key and Site Code in ImproveSEO settings.",
			'meta_title' => '',
			'meta_description' => ''
		);
	}

	// Admin server configuration — v2 Claude content route
	$admin_server_url = 'https://imporve-seo-admin-server-nzbm.onrender.com';
    $api_endpoint = $admin_server_url . '/api/v1/content-v2/article';

	// Prepare request payload matching the /content-v2/article (GenerateV2Params) interface.
	$payload = array(
		'seed_keyword' => $seed_keyword,
		'niche' => $niche ?: 'general_blog',
		'title' => $title,
		'nos_of_words' => $nos_of_words,
		'content_lang' => $content_lang,
		'call_to_action' => $call_to_action,
		'humanize' => true,
	);
	// niche_data must serialize as a JSON object (not an array) when present.
	if (!empty($niche_data)) {
		$payload['niche_data'] = (object) $niche_data;
	}
	if (!empty($brand_profile)) {
		$payload['brand_profile'] = $brand_profile;
	}
	if (!empty($cta_url)) {
		$payload['cta_url'] = $cta_url;
	}
	
	// Set up cURL for HTTP request to admin server
	$ch = curl_init($api_endpoint);
	
	// Configure cURL options
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json',
		'Accept: application/json',
		'X-API-Key: ' . $api_key,
		'X-Site-Code: ' . $site_code
	));
	curl_setopt($ch, CURLOPT_TIMEOUT, 480); // 2 minutes timeout for AI generation
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 100); // 10 seconds connection timeout
	
	// Execute the request
	$response = curl_exec($ch);
	$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	
	// Check for cURL errors
	if (curl_errno($ch)) {
		$error = curl_error($ch);
		curl_close($ch);
		error_log("createAIpost2 cURL Error: " . $error);
		return array(
			'content' => "Error: Failed to connect to content generation server. Please try again.",
			'meta_title' => '',
			'meta_description' => ''
		);
	}
	
	curl_close($ch);
	
	// Check HTTP status
	if ($http_status !== 200) {
		error_log("createAIpost2 HTTP Error: Status $http_status, Response: " . $response);
		// Keep the legacy prefix (the admin JS matches on it) and append the server's own
		// message, so a trial-ended block reads differently from plain out-of-credits.
		$err_body = json_decode($response, true);
		$err_msg  = ( is_array($err_body) && ! empty($err_body['error']) ) ? ' — ' . $err_body['error'] : '';
		return array(
			'content' => "Error: Content generation server returned error status: $http_status" . $err_msg,
			'meta_title' => '',
			'meta_description' => ''
		);
	}
	
	// Parse JSON response
	$result = json_decode($response, true);
	
	if (!$result || !isset($result['success'])) {
		error_log("createAIpost2 Invalid Response: " . $response);
		return array(
			'content' => "Error: Invalid response from content generation server.",
			'meta_title' => '',
			'meta_description' => ''
		);
	}
	
	if (!$result['success']) {
		$error_msg = isset($result['error']) ? $result['error'] : 'Unknown error';
		error_log("createAIpost2 API Error: " . $error_msg);
		return array(
			'content' => "Error: " . $error_msg,
			'meta_title' => '',
			'meta_description' => ''
		);
	}
	
	// Extract content and meta data from successful response
	if (!isset($result['data']['content'])) {
		error_log("createAIpost2 Missing Content: " . $response);
		return array(
			'content' => "Error: No content returned from generation server.",
			'meta_title' => '',
			'meta_description' => ''
		);
	}
	
	$content_final = $result['data']['content'];
	$meta_title = isset($result['data']['meta_title']) ? $result['data']['meta_title'] : '';
	// v2 returns the correctly-spelled key; keep the legacy fallback for safety.
	$meta_description = isset($result['data']['meta_description'])
		? $result['data']['meta_description']
		: (isset($result['data']['meta_descreption']) ? $result['data']['meta_descreption'] : '');
	
	// Rebuild the FAQ section as discrete <h3>/<p> blocks. The server returns every pair
	// concatenated into ONE <p> with the questions wrapped only in <strong>, which is inline —
	// so the whole section renders as continuous prose. Normalising HERE, at the source, means
	// the stored post_content is already correct for the editor, every preview and the published
	// post. Idempotent, so the render-time filter downstream is a no-op for this content.
	if ( function_exists( 'improveseo_normalize_faq_markup' ) ) {
		$content_final = improveseo_normalize_faq_markup( $content_final );
	}

	$content_final = '<div class="main-content-section-improveseo">' . $content_final . '</div>';


	// Log the generation metadata if available (for debugging)
	if (isset($result['data']['generationMetadata'])) {
		$metadata = $result['data']['generationMetadata'];
		error_log("createAIpost2 Generation Metadata: " . json_encode($metadata));
	}
	
	// Return array with all three values
	return array(
		'content' => $content_final,
		'meta_title' => $meta_title,
		'meta_description' => $meta_description,
		'metadata' => isset($result['data']['generationMetadata']) ? $result['data']['generationMetadata'] : array()
	);
}



?>