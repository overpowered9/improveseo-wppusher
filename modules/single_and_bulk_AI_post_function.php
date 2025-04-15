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
		if (!empty($_POST['noedit'])) {
			$imgPrompt = $title;
			$seed_title = $_POST['seed_title'];
		} else {
			$seed_title = $title;
			//fetch_AI_image
			$basicImagePromptResponse = ImageBasicPrompt($title);

			/*$AudienceData = $_COOKIE['AudienceData'];*/
			$imgPrompt = 'You should come up with the cover image for an article. The image should be a very high quality shooting from a distance, high detail, photorealistic, image resolution is  800 pixels, cinematic. Do not include any text on the image. Using the following information generate an image.  ' . $basicImagePromptResponse;
			//$imgPrompt = "Very high quality shooting from a distance, high detail, photorealistic, image resolution 2146 pixels, cinematic. The theme is ‘".$title."’";
		}

		// add new prompt


		$dateTimeDefault = date('YmdHis');
		$imagename = 'ai_image_' . $dateTimeDefault;
		// Your OpenAI API key
		//$apiKey = get_option('improveseo_chatgpt_api_key');
		// The endpoint URL for OpenAI chat completions API (replace with the correct endpoint)
		//$apiUrl = 'https://api.openai.com/v1/images/generations';

		$apiKey = 'c0a5519b-922b-4ba9-8a32-0ba118286265';//replace with above function when you have added the flux_ai_api_key to the options, also do not forget to remove this hardcoeded api key as it can lead to api key leak

		// Flux AI API endpoint
		$apiUrl = 'https://api.us1.bfl.ai/v1/flux-pro-1.1';

		// Your input data or parameters
		$data = array(
			// 'prompt' => $term.' '.accordingtoterm($call, $_REQUEST['wordlimit']),
			'prompt' => $imgPrompt,//.' '.accordingtoterm($imgdisc, $_REQUEST['wordlimit']),
			'width' => 1024,  // use your desired dimensions Width of the generated image in pixels. Must be a multiple of 32. min 256 max 1440
			'height' => 768,  // use your desired dimensions height of the generated image in pixels. Must be a multiple of 32.min 256 max 1440
			'prompt_upsampling' => false,//Whether to perform upsampling on the prompt. If true, automatically modifies the prompt for more creative generation.
			'safety_tolerance' => 2,//Tolerance level for input and output moderation. Between 0 and 6, 0 being most strict, 6 being least strict.
			'output_format' => 'jpeg'//Output format for the generated image. Can be 'jpeg' or 'png'.
			// 'model'     => 'dall-e-3',
			// 'n'         => 1,
			// 'size'  => '1792x1024'
		);

		// Set up cURL
		$ch = curl_init($apiUrl);

		// Set cURL options
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'X-Key: ' . $apiKey
			//'Authorization: Bearer ' . $apiKey,
		));

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



		if (!empty($result['id'])) {
			// Get the task ID and poll for results
			$taskId = $result['id'];
			$maxAttempts = 10;
			$attempt = 0;

			do {
				if ($attempt > 0) {
					sleep(4); // Wait before checking again
				}

				// Check result
				$ch = curl_init("https://api.us1.bfl.ai/v1/get_result?id=" . $taskId);
				curl_setopt_array($ch, [
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_SSL_VERIFYPEER => false, // For development only, Change/remove both if the SSL certificate is valid on Wordpress
					CURLOPT_SSL_VERIFYHOST => 0,     // For development only, 
					CURLOPT_HTTPHEADER => [
						'X-Key: ' . $apiKey
					]
				]);

				$resultResponse = curl_exec($ch);
				curl_close($ch);

				$resultData = json_decode($resultResponse, true);

				if ($resultData['status'] === 'Ready') {



					$url = $resultData['result']['sample'];

					// Get WordPress upload directory
					$upload_dir = wp_upload_dir();


					$ch = curl_init($url);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
					$image_data = curl_exec($ch);
					curl_close($ch);

					if (!$image_data) {
						die('Error fetching image.');
					}


					// Fetch image data from URL
					// $image_data = file_get_contents($url);

					if ($image_data !== false) {
						// Generate unique filename

						$file_name = wp_unique_filename($upload_dir['path'], str_replace(" ", "_", str_replace(".", "", $seed_title)));
						$file_name = $file_name . '_' . rand();
						$file_path = $upload_dir['path'] . '/' . $file_name;
						//exit();
						if (file_put_contents($file_path, $image_data) !== false) {
							//Convert to WebP if GD is available
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

								$image_url = $upload_dir['url'] . '/' . $webp_file_name;
								//exit('here');
							} else {
								$image_url = $upload_dir['url'] . '/' . $file_name;
								//exit();
							}

							wp_send_json_success(array($image_url));
							exit();
						} else {
							echo 'Error saving the image file.';
							exit();
						}
					} else {
						return 'Error fetching image data from URL.';
					}
				}

				$attempt++;
			} while ($attempt < $maxAttempts);

			return 'Timeout waiting for image generation.';
		} else {
			return $result;
		}
	}
	wp_die();
}
?>