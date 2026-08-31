<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


if ( ! function_exists( 'improveseo_normalize_generated_title' ) ) {
	/**
	 * Strip a leading label the model prepended to a generated title.
	 *
	 * The /auxiliary title route sometimes answers with the label included —
	 * 'Title: Mindfulness Techniques…' — and we stored that verbatim, so the
	 * word leaked into ai_title, post_title AND the permalink slug. This runs
	 * at the source (before storage) so all three are clean.
	 *
	 * Only a LEADING label is removed, case-insensitively, and only when it is
	 * immediately followed by a separator — a title that merely *contains* the
	 * word "title" is untouched. Wrapping quotes (straight or curly) and
	 * surrounding whitespace go too.
	 *
	 * @param string $title Raw title as returned by the model / stored in the row.
	 * @return string Cleaned title ('' if nothing is left, so callers keep their
	 *                existing keyword fallback).
	 */
	function improveseo_normalize_generated_title( $title ) {
		$title = trim( (string) $title );
		if ( $title === '' ) {
			return '';
		}

		// Peel repeatedly: the model occasionally emits `"Title: ..."` — quotes
		// on the OUTSIDE of the label — so one pass is not always enough.
		for ( $i = 0; $i < 3; $i++ ) {
			$before = $title;

			// Markdown heading markers the model sometimes wraps the title in.
			$title = trim( preg_replace( '~^#{1,6}\s*~', '', $title ) );

			// Wrapping quotes: straight, curly, and back-ticks.
			$title = preg_replace(
				'~^["\'`\x{201C}\x{201D}\x{2018}\x{2019}]+|["\'`\x{201C}\x{201D}\x{2018}\x{2019}]+$~u',
				'',
				$title
			);
			$title = trim( $title );

			// Leading label + separator (colon, en/em dash, or hyphen).
			$title = preg_replace(
				'~^(?:seo\s+title|meta\s+title|blog\s+title|post\s+title|article\s+title|title)\s*(?::|\x{2013}|\x{2014}|-)\s*~iu',
				'',
				$title,
				1
			);
			$title = trim( $title );

			if ( $title === $before ) {
				break;
			}
		}

		return trim( $title );
	}
}

if ( ! function_exists( 'improveseo_strip_style_script_tags' ) ) {
	/**
	 * Remove any <style>/<script> block — tag AND its contents — from generated
	 * post content.
	 *
	 * These must never end up in post_content. A block like
	 * `<style>p{padding-bottom:2px !important;}</style>` looks harmless, but every
	 * sanitizer this content passes through (WordPress' wp_kses_post, TinyMCE's own
	 * schema on load) treats <style>/<script> as tags NOT valid in body content: they
	 * unwrap the tag but keep its text children, so the raw CSS/JS source is left
	 * behind as plain visible text ("p {padding-bottom: 2px !important;}") in the
	 * editor, every preview, and the published post. Removing the whole block here —
	 * tag and contents together — is what wp_kses_post deliberately does NOT do for
	 * style/script (kses only guarantees the TAGS can't inject markup, not that their
	 * text payload is dropped), so this has to run before that, at the source.
	 *
	 * @param string $html Post content, or any fragment of it.
	 * @return string Same content with every complete <style>/<script> block removed.
	 */
	function improveseo_strip_style_script_tags( $html ) {
		if ( empty( $html ) ) {
			return $html;
		}
		return preg_replace( '~<(style|script)\b[^>]*>.*?</\1>~is', '', $html );
	}
}

/**
 * Call the ImproveSEO admin server's /auxiliary endpoint.
 *
 * Used for small supporting GPT calls (meta title, meta description, audience data)
 * that previously hit OpenAI directly via improveseo_chatgpt_api_key. The server
 * route does not charge credits; it only requires a valid API key + site code.
 *
 * @param string $type    'meta_title' | 'meta_description' | 'audience_data'
 * @param array  $payload Extra fields: seed_keyword (required), title, content
 * @return string Generated text, or '' on any failure (logged via error_log).
 */
function improveseo_call_auxiliary_api( $type, array $payload = array() ) {
	$api_key   = get_option( 'improveseo_api_key' );
	$site_code = get_option( 'improveseo_site_code' );

	if ( empty( $api_key ) || empty( $site_code ) ) {
		error_log( 'improveseo_call_auxiliary_api: missing API key or site code; cannot call /auxiliary' );
		return '';
	}

	$admin_server_url = 'https://imporve-seo-admin-server-nzbm.onrender.com';
	$endpoint         = $admin_server_url . '/api/v1/generate/auxiliary';

	$body = array_merge( array( 'type' => $type ), $payload );

	$response = wp_remote_post( $endpoint, array(
		'headers' => array(
			'Content-Type' => 'application/json',
			'Accept'       => 'application/json',
			'X-API-Key'    => $api_key,
			'X-Site-Code'  => $site_code,
		),
		'body'    => wp_json_encode( $body ),
		'timeout' => 60,
	) );

	if ( is_wp_error( $response ) ) {
		error_log( 'improveseo_call_auxiliary_api: HTTP error for type=' . $type . ' — ' . $response->get_error_message() );
		return '';
	}

	$status = wp_remote_retrieve_response_code( $response );
	$raw    = wp_remote_retrieve_body( $response );
	$data   = json_decode( $raw, true );

	if ( $status !== 200 || ! is_array( $data ) || empty( $data['success'] ) ) {
		$err = is_array( $data ) && isset( $data['error'] ) ? $data['error'] : 'unknown';
		error_log( 'improveseo_call_auxiliary_api: failure type=' . $type . ' | status=' . $status . ' | error=' . $err );
		return '';
	}

	return isset( $data['data']['text'] ) ? (string) $data['data']['text'] : '';
}


/**
 * Fetch the ImproveSEO account email from the admin server using the stored
 * API key + site code. This is the exact address bulk completion notifications
 * are sent to (the server resolves it from the same credentials), so the UI
 * always shows the real recipient instead of a stale/hardcoded value.
 *
 * Cached in a transient to avoid a blocking HTTP call on every render. Falls
 * back to the stored improveseo_account_email option when the server can't be
 * reached, and keeps that option in sync as a durable offline fallback.
 *
 * @param bool $force   Bypass the cache and re-fetch.
 * @param int  $timeout HTTP timeout in seconds. Callers on a background/AJAX path
 *                      can pass a larger value to ride out a server cold start.
 * @return string The account email, or '' if it can't be determined.
 */
function improveseo_get_account_email( $force = false, $timeout = 15 ) {
	$cached = get_transient( 'improveseo_account_email_cache' );
	if ( ! $force && ! empty( $cached ) ) {
		return $cached;
	}

	$api_key   = get_option( 'improveseo_api_key' );
	$site_code = get_option( 'improveseo_site_code' );

	// No credentials yet — best we can do is any previously stored value.
	if ( empty( $api_key ) || empty( $site_code ) ) {
		return (string) get_option( 'improveseo_account_email', '' );
	}

	$response = wp_remote_get(
		'https://imporve-seo-admin-server-nzbm.onrender.com/api/v1/users/status',
		array(
			'headers' => array(
				'x-api-key'   => $api_key,
				'x-site-code' => $site_code,
				'Accept'      => 'application/json',
			),
			'timeout' => max( 5, (int) $timeout ),
		)
	);

	// Server unreachable — fall back to the last known value.
	if ( is_wp_error( $response ) ) {
		return (string) get_option( 'improveseo_account_email', '' );
	}

	$data  = json_decode( wp_remote_retrieve_body( $response ), true );
	$email = ( is_array( $data ) && ! empty( $data['email'] ) && is_email( $data['email'] ) )
		? sanitize_email( $data['email'] )
		: '';

	if ( $email ) {
		set_transient( 'improveseo_account_email_cache', $email, 6 * HOUR_IN_SECONDS );
		update_option( 'improveseo_account_email', $email ); // durable offline fallback
		return $email;
	}

	// Reached the server but no email came back — use any stored value.
	return (string) get_option( 'improveseo_account_email', '' );
}


function replace_content($content, $remove)


{


	// Use preg_replace if you want more complex pattern matching


	return preg_replace('/' . preg_quote($remove, '/') . '/', '', $content);


}





function removePTags($html)


{


	$html = preg_replace('/<p>(\s|&nbsp;)*<\/p>/', '', $html);


	$html = str_replace("\n", '<br>', $html);





	$html = str_replace('<h2>Table of Contents</h2>', '<h2 style="margin-top: 35px;">Table of Contents</h2>', $html);


	// Remove any text inside square brackets [example]


	$html = preg_replace('/\[[^\]]*\]/', '', $html);





	// Remove parentheses but keep the text inside


	// $html = preg_replace('/\(([^)]+)\)/', '$1', $html);


	return $html;


}





function convert_emails_to_links($content)


{


	// Convert any email address to a mailto link


	$content = preg_replace(


		'/\b([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})\b/',


		'<a href="mailto:$1">$1</a>',


		$content


	);


	return $content;


}





function convert_urls_to_links($content)


{


	// Regex to match URLs that are not inside HTML tags


	$content = preg_replace_callback(


		'/(<a\b[^>]*>.*?<\/a>)|((https?:\/\/|www\.)[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}([\/\w.-]*)?)/',


		function ($matches) {


			// If it's an existing link, return it as is


			if (!empty($matches[1])) {


				return $matches[1];


			}





			// If it's a plain URL, convert it to a clickable link


			$url = $matches[2];


			$href = preg_match('/^https?:\/\//', $url) ? $url : "http://$url";


			return "<a href=\"$href\" target=\"_blank\" rel=\"noopener\">$url</a>";


		},


		$content


	);





	return $content;


}


function ImageBasicPrompt($title)


{


	$apiUrl = 'https://api.openai.com/v1/chat/completions';


	$apiKey = get_option('improveseo_chatgpt_api_key');


	$imageBasicPrompt = "Write a one-paragraph scene description for the cover photo of a blog article titled: \"" . $title . "\". Describe one concrete, real-world scene that clearly shows the article's specific subject: name the main subject, the setting, and two or three supporting details. Keep it under 80 words. Plain visual description only - no camera or style words, no text or logos in the scene. Reply with the description only, no explanation, no quotation marks, and do not use the word 'prompt'.";


	// Your chat messages


	$messages = [


		['role' => 'system', 'content' => 'You are a helpful assistant.'],


		['role' => 'user', 'content' => $imageBasicPrompt],


	];





	// Additional parameters, including language setting (replace with actual parameters)


	$data = [


		'messages' => $messages,


		"model" => "gpt-4o",


		// 'language' => 'fr',  // Specify the result language as French


	];





	// Set up cURL


	// wp_remote_post() rather than cURL. No CURLOPT_TIMEOUT was set here, so this inherited
	// cURL's "no limit" default; WordPress defaults to 5 seconds, too short for a model call,
	// so 60s is set explicitly - the same value the other auxiliary calls in this plugin use.
	$response_obj = wp_remote_post( $apiUrl, array(
		'method'  => 'POST',
		'timeout' => 60,
		'headers' => array(
			'Content-Type'  => 'application/json',
			'Authorization' => 'Bearer ' . $apiKey,
		),
		'body'    => wp_json_encode( $data ),
	) );

	// The transport error used to be echoed straight to the page. Logged instead; the
	// caller already copes with an empty response.
	if ( is_wp_error( $response_obj ) ) {
		error_log( 'improveseo auxiliary request failed: ' . $response_obj->get_error_message() );
		$response = '';
	} else {
		$response = wp_remote_retrieve_body( $response_obj );
	}


	// Decode and display the response


	$result = json_decode($response, true);





	$response_data = $result['choices'][0]['message']['content'];


	return $response_data;





}


add_action('wp_ajax_upload_image', 'upload_image_callback');


function upload_image_callback()


{
	check_ajax_referer( 'improveseo_ajax', 'nonce' );
	if ( ! current_user_can( 'upload_files' ) ) {
		wp_send_json_error( array( 'message' => 'Insufficient permissions.' ), 403 );
	}



	if (!empty($_FILES['image'])) {


		$uploaded_file = $_FILES['image'];


		$upload_overrides = array('test_form' => false);


		$movefile = wp_handle_upload($uploaded_file, $upload_overrides);





		if ($movefile && !isset($movefile['error'])) {


			$image_url = $movefile['url'];


			echo esc_url( $image_url ); // Return the image URL


		} else {


			echo 'Error uploading image.';


		}


	}


	wp_die();


}


// AJAX handler for per-keyword image uploads in bulk form
add_action('wp_ajax_upload_keyword_image', 'upload_keyword_image_callback');

function upload_keyword_image_callback()
{
	check_ajax_referer( 'improveseo_ajax', 'nonce' );
	if ( ! current_user_can( 'upload_files' ) ) {
		wp_send_json_error( array( 'message' => 'Insufficient permissions.' ), 403 );
	}

	if (empty($_FILES['image'])) {
		wp_send_json_error(array('message' => 'No file uploaded'));
	}
	
	$uploaded_file = $_FILES['image'];
	$upload_overrides = array('test_form' => false);
	$movefile = wp_handle_upload($uploaded_file, $upload_overrides);

	if ($movefile && !isset($movefile['error'])) {
		wp_send_json_success(array('image_url' => $movefile['url']));
	} else {
		$error_message = isset($movefile['error']) ? $movefile['error'] : 'Upload failed';
		wp_send_json_error(array('message' => $error_message));
	}
}


/**
 * PHP mirror of the admin server's variantFromWords().
 *
 * The server prices content per size variant and resolves that variant from the SAME three
 * literal strings the Article Size dropdowns carry as their <option value>s — they are the wire
 * format, not display text. Matching them here is what lets the pre-check quote the price the
 * server will actually charge.
 *
 * Anything unrecognised falls back to 'medium' rather than erroring, because that is what the
 * server does. Failing closed to a DIFFERENT variant on either side would be worse than not
 * checking at all: the gate would pass a run the server then refuses, or block one it would
 * have allowed.
 *
 * @param string $words One of the three <option value> literals.
 * @return string 'small'|'medium'|'large'
 */
function improveseo_content_variant_from_words( $words ) {
	switch ( trim( (string) $words ) ) {
		case '600 to 1200 words':
			return 'small';
		case '2400 to 3600 words':
			return 'large';
		case '1200 to 2400 words':
			return 'medium';
	}

	return 'medium';
}


// AJAX handler for bulk post credit check
add_action('wp_ajax_check_bulk_credits', 'check_bulk_credits_callback');

function check_bulk_credits_callback() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_send_json_error( array( 'message' => 'Insufficient permissions.' ), 403 );
	}

	// Verify nonce
	if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'check_credits_nonce')) {
		wp_send_json_error(array('error' => 'Invalid security token'));
	}
	
	$api_key = sanitize_text_field($_POST['api_key']);
	$site_code = sanitize_text_field($_POST['site_code']);
	$keyword_count = intval($_POST['keyword_count']);
	$ai_image_count = intval($_POST['ai_image_count']);
	// Optional: the Article Size dropdown's raw value. Absent from older callers, and absent is
	// indistinguishable from unrecognised here — both resolve to medium, which is the variant the
	// check assumed unconditionally before this parameter existed.
	$article_size = isset($_POST['article_size']) ? sanitize_text_field(wp_unslash($_POST['article_size'])) : '';

	if (empty($api_key) || empty($site_code)) {
		wp_send_json_error(array('error' => 'API credentials not configured'));
	}
	
	// Call ImproveSEO server to get user status with subscription info
	$admin_server_url = 'https://imporve-seo-admin-server-nzbm.onrender.com';
	$api_endpoint = $admin_server_url . '/api/v1/users/status';
	
	$response = wp_remote_get($api_endpoint, array(
		'headers' => array(
			'x-api-key' => $api_key,
			'x-site-code' => $site_code,
			'Content-Type' => 'application/json'
		),
		'timeout' => 30
	));
	
	if (is_wp_error($response)) {
		wp_send_json_error(array('error' => 'Unable to connect to ImproveSEO server'));
	}
	
	$body = wp_remote_retrieve_body($response);
	$data = json_decode($body, true);
	
	if (!$data || !isset($data['success']) || !$data['success']) {
		wp_send_json_error(array('error' => $data['error'] ?? 'Failed to retrieve user status'));
	}
	
	// Extract data from response
	$credits = $data['credits'] ?? array('images' => 0, 'content' => 0, 'keywords' => 0);
	$subscription = $data['subscription'] ?? null;
	
	// Get plan information from subscription
	$plan_slug = 'unknown';
	$plan_id = 0;
	$plan_name = 'Unknown';
	
	if ($subscription && isset($subscription['plan'])) {
		$plan = $subscription['plan'];
		$plan_slug = strtolower($plan['slug'] ?? 'unknown');
		$plan_id = $plan['id'] ?? 0;
		$plan_name = $plan['name'] ?? 'Unknown';
	}
	
	// Perform checks
	$checks = array();
	
	// Check 1: Plan restriction (Basic/Grow plan cannot use bulk)
	// Plan IDs: 1 = Grow/Basic, 2 = Scale, 3 = Enterprise
	$checks['plan_check'] = array(
		'allowed' => $plan_id !== 1 && !in_array($plan_slug, array('basic', 'grow')),
		'plan_slug' => $plan_slug,
		'plan_id' => $plan_id,
		'plan_name' => $plan_name
	);
	
	// Checks 2 and 3 used to assume a flat 1 credit per post and 1 per image, and read the
	// per-type balances. There is one ISEO pool now, priced per action, so both are costed from
	// the server's published pricing block and checked against the SAME pooled balance.
	//
	// This is a UX pre-check only. The real gate is the server's atomic reservation at generation
	// time — this exists so a bulk run fails early and legibly instead of part-way through.
	$pool = isset($credits['total']) ? intval($credits['total']) : intval($credits['content']);
	$pricing = isset($data['pricing']) && is_array($data['pricing']) ? $data['pricing'] : array();

	// Price the size the user actually picked. This used to read $pricing['content']['medium']
	// unconditionally, which was wrong the moment the sizes stopped costing the same: a Large run
	// was checked at the medium price and passed a gate it could not afford, then died part-way
	// through when the server's reservation refused it — the exact failure this pre-check exists
	// to prevent.
	//
	// Two separate fallbacks, and they mean different things:
	//   - unknown/absent article_size    -> 'medium' variant (improveseo_content_variant_from_words)
	//   - server published no price      -> flat 1, the pre-pricing behaviour, so an un-redeployed
	//                                       server still gates exactly as it did before.
	$content_variant = improveseo_content_variant_from_words($article_size);

	$content_unit = 1;
	if (isset($pricing['content'][$content_variant])) {
		$content_unit = max(1, intval($pricing['content'][$content_variant]));
	} elseif (isset($pricing['content']['medium'])) {
		// Variant priced nowhere but medium is — better a known-wrong-but-published number than
		// the flat 1, which would under-state the cost by an order of magnitude.
		$content_unit = max(1, intval($pricing['content']['medium']));
	}
	$image_unit = isset($pricing['image']) ? max(1, intval($pricing['image'])) : 1;

	$content_needed = $keyword_count * $content_unit;
	$image_needed   = $ai_image_count * $image_unit;

	// 'needed' is CREDITS, not a count of posts.
	//
	// It used to be $keyword_count compared against the balance, which assumed a post costs one
	// credit. At the medium price of 10 a 12-post run was announced as "required: 12" and its
	// "remaining after" was 108 credits too high, while the estimate line directly above the same
	// dialog correctly said 120 — the two disagreed on screen.
	//
	// $content_needed is $keyword_count * $content_unit, and $content_unit comes from the server's
	// published table for the size the user actually picked, so Small/Medium/Large price at their
	// own rates rather than all at one. Every client consumer already expects credits here; this
	// was the only half still reporting a count.
	$checks['content_check'] = array(
		'sufficient' => $pool >= $content_needed,
		'needed' => $content_needed,
		'available' => $pool
	);
	
	// Check 3: Image credits (only for AI-generated images)
	//
	// 'needed' is CREDITS, not a count of images. It used to be $ai_image_count compared against
	// the balance, which silently assumed one image costs one credit. At ISEO_COST_IMAGE=5 that
	// under-gated a run by 5x: the pre-check passed and the server's reservation then refused it
	// part-way through — the exact failure this pre-check exists to prevent. $image_needed is
	// $ai_image_count * $image_unit, priced from the server's published table just above.
	//
	// This mirrors what content already does. $checks['image_unit'] is emitted below so a caller
	// can render "N images x unit credits" against the SAME unit price the gate used, rather than
	// re-deriving it and risking showing one number while gating on another.
	$checks['image_check'] = array(
		'sufficient' => $pool >= $image_needed,
		'needed' => $image_needed,
		'available' => $pool
	);

	// Both actions draw on ONE pool, so a run needs the sum — checking them separately would pass
	// a bulk job that can afford the articles or the images but not both.
	$checks['combined_check'] = array(
		'sufficient' => $pool >= ($content_needed + $image_needed),
		'needed' => $content_needed + $image_needed,
		'available' => $pool
	);
	$checks['pricing'] = $pricing;

	// Echo back the unit prices and the variant they came from, so a caller renders the SAME
	// numbers this check was made against. Without this the dialog would have to re-derive the
	// price from the pricing table on its own, and any disagreement between the two derivations
	// would show the user one number while gating them on another.
	$checks['content_variant'] = $content_variant;
	$checks['content_unit']    = $content_unit;
	$checks['image_unit']      = $image_unit;

	// The pooled balance under its own name. content_check.available already carries it, but that
	// reads as "available for the content check" — callers that only want the balance (the cost
	// preview under Article Size) should not have to go through a check to find it.
	$checks['credits_total'] = $pool;

	wp_send_json_success($checks);
}





add_action('wp_ajax_getPromptForImages', 'getPromptForImages');


function getPromptForImages()
{
	check_ajax_referer( 'improveseo_ajax', 'nonce' );
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_send_json_error( array( 'message' => 'Insufficient permissions.' ), 403 );
	}

	if ( ! empty( $_POST['title'] ) ) {
		$title                    = sanitize_text_field( wp_unslash( $_POST['title'] ) );
		$basicImagePromptResponse = ImageBasicPrompt( $title );
		wp_send_json_success( $basicImagePromptResponse );
	} else {
		wp_send_json_error( array( 'message' => 'Title is required.' ) );
	}
	wp_die();
}





add_action('wp_ajax_fetch_AI_image', 'fetch_AI_image_callback');



function fetch_AI_image_callback()
{
	check_ajax_referer( 'improveseo_ajax', 'nonce' );
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_send_json_error( array( 'message' => 'Insufficient permissions.' ), 403 );
	}

    if (!empty($_POST['title'])) {
        $title = $_POST['title'];
        $noedit = !empty($_POST['noedit']);
        $seed_title = $noedit ? $_POST['seed_title'] : $title;
        
        // Get API credentials from WordPress options
        $api_key = get_option('improveseo_api_key');
        $site_code = get_option('improveseo_site_code');
        
        // Validate credentials
        if (empty($api_key) || empty($site_code)) {
            wp_send_json_error('Missing API credentials. Please configure your API Key and Site Code in ImproveSEO settings.');
            wp_die();
        }
        
        // Admin server configuration
        $admin_server_url = 'https://imporve-seo-admin-server-nzbm.onrender.com';

        // The single-post wizard sends use_v2/niche → OpenAI v2 image route. Everything else
        // (bulk, keyword, manual flows) stays on the legacy Flux route, untouched.
        $use_v2_image = !empty($_POST['use_v2']) || !empty($_POST['niche']);

        if ($use_v2_image) {
            $api_endpoint = $admin_server_url . '/api/v1/content-v2/image';
            $payload = array(
                'niche'   => isset($_POST['niche']) && $_POST['niche'] !== '' ? sanitize_text_field($_POST['niche']) : 'general_blog',
                'title'   => $title,
                'size'    => '1536x1024',
                'quality' => 'medium',
            );
            $bp_city = get_option('improveseo_business_city', '');
            if ($bp_city) { $payload['city'] = $bp_city; }
            // The slot this image occupies. The v2 route reads target_key from the body and prices
            // the second and later generation of the same slot at ISEO_COST_IMAGE_REGEN instead of
            // ISEO_COST_IMAGE. Without it isRegeneration() sees an empty key, returns false, and
            // every regeneration is charged at the full first-generation price.
            //
            // Only sent when non-empty: an empty string would be indistinguishable from absent to
            // the server anyway, and omitting it keeps the payload honest about what is known.
            $target_key = isset($_POST['target_key']) ? sanitize_text_field(wp_unslash($_POST['target_key'])) : '';
            if ($target_key !== '') { $payload['target_key'] = $target_key; }
            // noedit: pass the raw prompt straight to the image model (v2 uses it verbatim)
            if ($noedit) { $payload['prompt'] = $title; }
        } else {
            $api_endpoint = $admin_server_url . '/api/v1/generate/generateimage';
            $payload = array(
                'title' => $title,
                'noedit' => $noedit,
                'seed_title' => $seed_title,
                'width' => 1024,
                'height' => 768
            );
            if ($noedit) {
                $payload['prompt'] = $title;
            }
        }
        
        // Set up cURL for HTTP request to admin server
        // wp_remote_post() rather than cURL. The 120s timeout is carried over from
        // CURLOPT_TIMEOUT and is load-bearing: WordPress defaults to 5 seconds, which would
        // abort every image generation. CURLOPT_CONNECTTIMEOUT has no separate equivalent in
        // the WP HTTP API - 'timeout' covers the whole request.
        $response_obj = wp_remote_post( $api_endpoint, array(
            'method'  => 'POST',
            'timeout' => 120,
            'headers' => array(
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
                'X-API-Key'    => $api_key,
                'X-Site-Code'  => $site_code,
            ),
            'body'    => wp_json_encode( $payload ),
        ) );

        if ( is_wp_error( $response_obj ) ) {
            error_log("fetch_AI_image HTTP Error: " . $response_obj->get_error_message());
            wp_send_json_error('Failed to connect to image generation server. Please try again.');
            wp_die();
        }

        $response    = wp_remote_retrieve_body( $response_obj );
        $http_status = (int) wp_remote_retrieve_response_code( $response_obj );
        
        // Check HTTP status
        if ($http_status !== 200) {
            error_log("fetch_AI_image HTTP Error: Status $http_status, Response: " . $response);
            // Forward the server's own message (e.g. the trial-ended vs out-of-credits copy)
            // so the UI can say what actually happened instead of showing a bare status code.
            $err_body = json_decode($response, true);
            $err_msg  = ( is_array($err_body) && ! empty($err_body['error']) )
                ? $err_body['error']
                : "Image generation server returned error status: $http_status";
            wp_send_json_error($err_msg);
            wp_die();
        }
        
        // Parse JSON response
        $result = json_decode($response, true);
        
        if (!$result || !isset($result['success'])) {
            error_log("fetch_AI_image Invalid Response: " . $response);
            wp_send_json_error('Invalid response from image generation server.');
            wp_die();
        }
        
        if (!$result['success']) {
            $error_msg = isset($result['error']) ? $result['error'] : 'Unknown error';
            error_log("fetch_AI_image API Error: " . $error_msg);
            wp_send_json_error($error_msg);
            wp_die();
        }
        
        // Acquire image bytes. A hosted URL (Flux, or v2 when Supabase hosting is configured) is
        // downloaded; otherwise the v2 route returns a base64 data URI we decode directly.
        $img = isset($result['data']) ? $result['data'] : array();
        $image_url = isset($img['image_url']) ? $img['image_url'] : '';
        $data_uri  = isset($img['data_uri']) ? $img['data_uri'] : '';

        $upload_dir = wp_upload_dir();
        $image_data = false;

        if (!empty($image_url)) {
            // wp_remote_get() returns the raw body, which is what the image bytes are.
            // 60s carried over from CURLOPT_TIMEOUT. TLS verification stays on - it is the
            // WP HTTP API default, and it was previously disabled here, which let anyone on
            // the network path substitute the image.
            $img_response = wp_remote_get( $image_url, array( 'timeout' => 60 ) );
            if ( is_wp_error( $img_response ) ) {
                error_log( 'fetch_AI_image download failed: ' . $img_response->get_error_message() );
                $image_data = false;
            } else {
                $image_data = wp_remote_retrieve_body( $img_response );
            }
        } elseif (!empty($data_uri)) {
            $comma = strpos($data_uri, ',');
            $b64 = ($comma !== false) ? substr($data_uri, $comma + 1) : $data_uri;
            $image_data = base64_decode($b64);
        } else {
            error_log("fetch_AI_image Missing image_url/data_uri: " . $response);
            wp_send_json_error('No image returned from generation server.');
            wp_die();
        }

        if (!$image_data) {
            error_log("fetch_AI_image Error: Failed to obtain image bytes.");
            wp_send_json_error('Error fetching generated image.');
            wp_die();
        }
        
        // Generate unique filename
        $file_name = wp_unique_filename($upload_dir['path'], str_replace(" ", "_", str_replace(".", "", $seed_title)));
        $file_name = $file_name . '_' . wp_rand();
        $file_path = $upload_dir['path'] . '/' . $file_name;
        
        if (file_put_contents($file_path, $image_data) !== false) {
            // Convert to WebP if GD is available
            if (extension_loaded('gd')) {
                $original_image = imagecreatefromstring($image_data);
                
                // Path for the WebP image
                $webp_file_name = pathinfo($file_name, PATHINFO_FILENAME) . '.webp';
                $webp_file_path = $upload_dir['path'] . '/' . $webp_file_name;
                
                // Convert and save as WebP
                imagewebp($original_image, $webp_file_path, 90);
                
                // Free memory
                imagedestroy($original_image);
                
                // Delete the original file
                wp_delete_file($file_path);
                
                $final_image_url = $upload_dir['url'] . '/' . $webp_file_name;
            } else {
                $final_image_url = $upload_dir['url'] . '/' . $file_name;
            }
            
            wp_send_json_success(array($final_image_url));
            exit();
        } else {
            error_log("fetch_AI_image Error: Failed to save image file to: " . $file_path);
            wp_send_json_error('Error saving the image file.');
            wp_die();
        }
    }
    
    wp_die();
}


?>