<?php





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





add_action('wp_ajax_getPromptForImages', 'getPromptForImages');


function getPromptForImages()


{


	if (!empty($_POST['title'])) {


		$title = $_POST['title'];


		$basicImagePromptResponse = ImageBasicPrompt($title);


		wp_send_json_success($basicImagePromptResponse);


	}


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
        $admin_server_url = 'https://imporve-seo-admin-server.onrender.com';
        $api_endpoint = $admin_server_url . '/api/v1/generate/generateimage';
        
        // Prepare request payload
        $payload = array(
            'title' => $title,
            'noedit' => $noedit,
            'seed_title' => $seed_title,
            'width' => 1024,
            'height' => 768
        );
        
        // If noedit mode, send the raw prompt
        if ($noedit) {
            $payload['prompt'] = $title;
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
        
        // Extract image URL from successful response
        if (!isset($result['data']['image_url'])) {
            error_log("fetch_AI_image Missing Image URL: " . $response);
            wp_send_json_error('No image URL returned from generation server.');
            wp_die();
        }
        
        $image_url = $result['data']['image_url'];
        
        // Download the image from admin server and save to WordPress uploads
        $upload_dir = wp_upload_dir();
        
        $ch = curl_init($image_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        $image_data = curl_exec($ch);
        curl_close($ch);
        
        if (!$image_data) {
            error_log("fetch_AI_image Error: Failed to download image from URL: " . $image_url);
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