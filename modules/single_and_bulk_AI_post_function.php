<?php

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


	$imageBasicPrompt = "‘I need help creating a Dalle image prompt for an article based on the title: " . $title . ". Provide the description without any further explanation. Don not include the word 'prompt'.";


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





	$response_data = $result['choices'][0]['message']['content'];


	return $response_data;





}


add_action('wp_ajax_upload_image', 'upload_image_callback');


function upload_image_callback()


{


	if (!empty($_FILES['image'])) {


		$uploaded_file = $_FILES['image'];


		$upload_overrides = array('test_form' => false);


		$movefile = wp_handle_upload($uploaded_file, $upload_overrides);





		if ($movefile && !isset($movefile['error'])) {


			$image_url = $movefile['url'];


			echo $image_url; // Return the image URL


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


// AJAX handler for bulk post credit check
add_action('wp_ajax_check_bulk_credits', 'check_bulk_credits_callback');

function check_bulk_credits_callback() {
	// Verify nonce
	if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'check_credits_nonce')) {
		wp_send_json_error(array('error' => 'Invalid security token'));
	}
	
	$api_key = sanitize_text_field($_POST['api_key']);
	$site_code = sanitize_text_field($_POST['site_code']);
	$keyword_count = intval($_POST['keyword_count']);
	$ai_image_count = intval($_POST['ai_image_count']);
	
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
	
	// Check 2: Content credits
	$content_credits = intval($credits['content']);
	$checks['content_check'] = array(
		'sufficient' => $content_credits >= $keyword_count,
		'needed' => $keyword_count,
		'available' => $content_credits
	);
	
	// Check 3: Image credits (only for AI-generated images)
	$image_credits = intval($credits['images']);
	$checks['image_check'] = array(
		'sufficient' => $image_credits >= $ai_image_count,
		'needed' => $ai_image_count,
		'available' => $image_credits
	);
	
	wp_send_json_success($checks);
}





add_action('wp_ajax_getPromptForImages', 'getPromptForImages');


function getPromptForImages()
{
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
        curl_setopt($ch, CURLOPT_TIMEOUT, 120); // 2 minutes timeout for image generation
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        
        // Execute the request
        $response = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        // Check for cURL errors
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            error_log("fetch_AI_image cURL Error: " . $error);
            wp_send_json_error('Failed to connect to image generation server. Please try again.');
            wp_die();
        }
        
        curl_close($ch);
        
        // Check HTTP status
        if ($http_status !== 200) {
            error_log("fetch_AI_image HTTP Error: Status $http_status, Response: " . $response);
            wp_send_json_error("Image generation server returned error status: $http_status");
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
            $ch = curl_init($image_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            $image_data = curl_exec($ch);
            curl_close($ch);
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
        $file_name = $file_name . '_' . rand();
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
                unlink($file_path);
                
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