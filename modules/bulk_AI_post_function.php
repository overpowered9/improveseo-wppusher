<?php

if (file_exists(dirname(__FILE__) . '/modules/single_and_bulk_AI_post_function.php'))

include_once dirname(__FILE__) . '/modules/single_and_bulk_AI_post_function.php';
include_once dirname(__FILE__) . '/modules/GenerateAIpopup.php';









add_action('cronjob_request_event', 'CronjobRequest');

function CronjobRequest()

{

	my_plugin_log("cron calling working...");

	global $wpdb;



	$returndata = generateBulkAiContent();

	// $wpdb->insert($wpdb->prefix . "improveseo_cron_job_status", array(

	// 	'time' => date("Y-m-d H:i:s"),

	// 	'input' => '',

	// 	'content_data' => json_encode($returndata),

	// 	'status' => 1

	// ));

	$publishContent = saveContentInTaskList();

	// Generate Content



	//$lastid = $wpdb->insert_id;

	//error_log('This is a log message : '.date('Y-m-d H:i:s'));

}

function generateBulkAiContent($id = '', $regenerate = '')

{

	global $wpdb;

	if ($id != '') {

		$sql = "SELECT * FROM `" . $wpdb->prefix . "improveseo_bulktasksdetails` WHERE `id` = " . $id;

	} else {

		$sql = "SELECT * FROM `" . $wpdb->prefix . "improveseo_bulktasksdetails` WHERE `status`='Pending' ORDER BY `id` ASC LIMIT 1";

	}



	$tasks = $wpdb->get_results($sql);

	$json_d = json_encode($tasks);

	if (empty($json_d)) {

		my_plugin_log('This is a log message : returned true --> ' . $json_d);

		return true;

	}

	my_plugin_log('bulk saved values : ' . $json_d);



	//seed_option1

	foreach ($tasks as $key => $value) {

		$id = $value->id;

		my_plugin_log('This is a log message : ' . $id);

		// AI Title



		$getAudienceData = getAudienceData($value->keyword_name);

		if ($value->select_exisiting_options == 'seed_option1') {

			$ai_title = $value->keyword_name;

		} else if ($value->select_exisiting_options == 'seed_option2') {

			$ai_title = bulkAiTitle($getAudienceData, 'normal', $value->keyword_name, $value->tone_of_voice);

		} else if ($value->select_exisiting_options == 'seed_option3') {

			$ai_title = bulkAiTitle($getAudienceData, 'question', $value->keyword_name, $value->tone_of_voice);

		} else {

			$ai_title = '';

		}











		// AI Image

		if ($value->aiImage == 'AI_image_one') {

			$imageURL = generateBulkAiImage($ai_title, $getAudienceData);

			$imageURL = base64_encode($imageURL);

		} else {

			$imageURL = $value->ai_image;

		}



		// AI Content

		$keyword_selection = '';

		//my_plugin_log('arrays : '.$basic_prompt);

		$AI_Content = createBulkAIpost($value->keyword_name, $keyword_selection, $value->select_exisiting_options, $value->nos_of_words, $value->content_lang, $shortcode = '', $is_single_keyword = '', $value->tone_of_voice, $value->point_of_view, $value->details_to_include, $value->call_to_action, $value->details_to_include);







		$data_array = array('ai_title' => $ai_title, 'imageURL' => $imageURL, 'AI_Content' => $AI_Content);

		$AI_Content = base64_encode($AI_Content);

		my_plugin_log('This is a log message content : ' . $AI_Content);

	}

	//$wpdb->query ( "UPDATE `".$wpdb->prefix."improveseo_bulktasksdetails` SET status='Done',`ai_title`=".$ai_title.",`ai_content`='".$AI_Content."',`ai_image`='".$imageURL."', WHERE id=".$id );



	// if($regenerate==1) {

	$wpdb->query(

		$wpdb->prepare(

			"UPDATE `" . $wpdb->prefix . "improveseo_bulktasksdetails`

					SET status = %s, ai_title = %s, ai_content = %s, ai_image = %s

					WHERE id = %d",

			'Done',

			$ai_title,

			$AI_Content,

			$imageURL,

			$id

		)

	);

	// } else {

	// 	$wpdb->query(

	// 		$wpdb->prepare(

	// 			"UPDATE `".$wpdb->prefix."improveseo_bulktasksdetails`

	// 			SET ai_title = %s, ai_content = %s, ai_image = %s

	// 			WHERE id = %d",

	// 			 $ai_title, $AI_Content, $imageURL, $id

	// 		)

	// 	);

	// }





	//update_option("work_dex_schedule",time());





	return $data_array;

}

function saveContentInTaskList()

{



	global $wpdb;
//SELECT * FROM `wpdb_improveseo_bulktasksdetails` WHERE `state` IN('Scheduled','Published') AND `status` = 'Done' AND `post_id` IS NULL ORDER BY `id` ASC LIMIT 1
	$sql = "SELECT * FROM `" . $wpdb->prefix . "improveseo_bulktasksdetails` WHERE `state` IN('Scheduled','Published') AND `status` = 'Done' AND `post_id` IS NULL 
	 ORDER BY `id` ASC LIMIT 1";



	$Bulktasks = $wpdb->get_results($sql);







	$content = '';

	if (!empty($Bulktasks)) {

		foreach ($Bulktasks as $key => $value) {

			// short code

			if (!empty($value->testimonial)) {

				$testimonial_ids = '';

				$all_testimonial = explode("||", $value->testimonial);

				foreach ($all_testimonial as $key1 => $value1) {

					if (!empty($value1)) {

						$testimonial_ids = $value1 . ',' . $testimonial_ids;

					}

				}

				$content = $content . '<p>[improveseo_testimonial id="' . $testimonial_ids . '"]</p>';

			}



			if (!empty($value->Button_SC)) {

				$content = $content . '<p>[improveseo_buttons id="' . $value->Button_SC . '"]</p>';

			}



			if (!empty($value->GoogleMap_SC)) {

				$content = $content . '<p>[improveseo_googlemaps id="' . $value->GoogleMap_SC . '"]</p>';

			}



			if (!empty($value->Video_SC)) {

				$content = $content . '<p style="width:100%">[improveseo_video id="' . $value->Video_SC . '"]</p>';

			}

			$catids = [];

			if (!empty($value->cats)) {

				$categories = explode("||", $value->cats);

				foreach ($categories as $ckey => $cvalue) {

					if (!empty($cvalue)) {

						array_push($catids, $cvalue);

						//$catids = $value1.','.$cvalue;

					}

				}

			} else {

				$categories = '';

			}

			$tags = array();

			$fullcontent = "<img src='" . base64_decode($value->ai_image) . "' style='width:100%; margin-bottom: 100px;' alt='" . $value->ai_title . "'>" . base64_decode($value->ai_content) . $content;

			$post_date = date('Y-m-d H:i:s');

			$post_status = 'Published';

			if ($value->schedule_posts == 'draft_posts') {

				$post_status = 'Draft';

			} elseif ($value->schedule_posts == 'schedule_posts_input_wise') {

				$post_status = 'Draft';

				$tags = array('This post will published on ' . $value->published_on . ' automatically.');

			}









			if ($value->assigning_authors == 'assigning_authors') {

				$post_author = $value->assigning_authors_value;

			}



			if ($value->assigning_authors == 'assigning_multi_authors') {
				$first_names = array(
					'John',
					'Jane',
					'Michael',
					'Emily',
					'David',
					'Sarah',
					'James',
					'Linda',
					'Robert',
					'Jessica',
					'Daniel',
					'Laura',
					'Chris',
					'Amy',
					'Mark',
					'Angela',
					'Steven',
					'Megan',
					'Paul',
					'Rachel',
					'Peter',
					'Hannah',
					'Kevin',
					'Sophia',
					'Edward',
					'Emma',
					'Jason',
					'Grace',
					'Tom',
					'Alice'
					// Add more names as needed to increase uniqueness

				);



				$last_names = array(
					'Smith',
					'Johnson',
					'Brown',
					'Williams',
					'Jones',
					'Miller',
					'Davis',
					'Garcia',
					'Martinez',
					'Taylor',
					'Wilson',
					'Moore',
					'Anderson',
					'Thomas',
					'Jackson',
					'White',
					'Harris',
					'Martin',
					'Thompson',
					'Lopez',
					'Gonzalez',
					'Clark',
					'Lewis',
					'Walker',
					'Hall',
					'Allen',
					'Young',
					'King',
					'Wright',
					'Scott'
					// Add more names as needed
				);



				// Pick a random first and last name

				$first_name = $first_names[array_rand($first_names)];

				$last_name = $last_names[array_rand($last_names)];



				$username = str_replace(" ", "", $first_name . $last_name);



				// Check if the username already exists

				if (username_exists($username)) {

					$post_author = username_exists($username);

				} else {
					// Define user information
					my_plugin_log('author recreate : ' . $username);

					$first_name = $first_names[array_rand($first_names)];

					$last_name = $last_names[array_rand($last_names)];

					$username = str_replace(" ", "", $first_name . $last_name);

					$user_data = array(

						'user_login' => $username,        // Username

						'user_pass' => 'hdfdg5456ghj',                // User password

						'user_email' => $first_name . '@example.com', // User email

						'first_name' => $first_name,

						'last_name' => $last_name,

						'role' => 'author',                     // Assign 'author' role

					);



					my_plugin_log('author created : ' . $username);



					// Create the user

					$post_author = wp_insert_user($user_data);

				}



				





			}



			my_plugin_log('author added : ' . $post_author);

			$post_array = array(

				'post_author' => $post_author,

				'post_content' => $fullcontent,

				'post_title' => $value->ai_title,

				'comment_status' => 'closed',

				'ping_status' => 'closed',

				'post_type' => "post",

				'post_date' => $post_date,

				'post_status' => $post_status

			);







			$post_id = wp_insert_post($post_array, true); // 'true' enables WP_Error return

			if (is_wp_error($post_id)) {
				$error_message = $post_id->get_error_message();
				//echo 'Error inserting post: ' . esc_html($error_message);
				my_plugin_log('Error inserting post: ' . $error_message);
			} else {
				$smsg = 'Post inserted successfully with ID: ' . intval($post_id);
				my_plugin_log('Post id insert: ' . $smsg);
			}


			// Replace with your desired tags

			if (!empty($tags)) {

				wp_set_post_tags($post_id, $tags);

			}



			//$post_id = $wpdb->insert_id;



			if ((!empty($catids))) {

				wp_set_post_categories($post_id, $catids, false);

			}



			$wpdb->query(

				$wpdb->prepare(

					"UPDATE `" . $wpdb->prefix . "improveseo_bulktasksdetails`

						SET state = %s, post_id = %d WHERE id = %d",

					$post_status,

					$post_id,

					$value->id

				)

			);

			my_plugin_log('This is a log message : ' . $value->id);

			//wp_send_json_success(array('status' => 'false',"message"=>'here 1 : '. $wpdb->last_error  ));

		}

	}







	/*  Update post status on scheduled date*/

	$sql = "SELECT * FROM `" . $wpdb->prefix . "improveseo_bulktasksdetails` WHERE `published_on`<='" . date('Y-m-d') . "' AND `post_id` IS NOT NULL AND `is_published_by_plugin` = '0' AND `status`='Done' ORDER BY `id` ASC";



	$Bulktasks = $wpdb->get_results($sql);


	my_plugin_log('Bulktasks : ' . json_encode($Bulktasks));




	$content = '';

	foreach ($Bulktasks as $key => $value) {

		if (!empty($value->post_id)) {

			$post_data = array(

				'ID' => $value->post_id, // The ID of the post being updated

				'post_status' => 'publish'  // or any other status

			);



			wp_update_post($post_data);
			my_plugin_log('updated post info ' . json_encode($post_data));


			// tag 

			$tags = array($value->keyword_name);

			if (!empty($tags)) {

				wp_set_post_tags($value->post_id, $tags);

			}



			// $post_status = 'publish';

			$wpdb->query(

				$wpdb->prepare(

					"UPDATE `" . $wpdb->prefix . "improveseo_bulktasksdetails`
					SET `is_published_by_plugin` = %d, `state` = %s WHERE id = %d",
					1,
					'Published',
					$value->id

				)

			);

		}

	}

}

function getAudienceData($seed_keyword)

{

	global $wpdb, $user_ID;



	// Your OpenAI API key

	$apiKey = get_option('improveseo_chatgpt_api_key');



	// The endpoint URL for OpenAI chat completions API (replace with the correct endpoint)

	$apiUrl = 'https://api.openai.com/v1/chat/completions';



	$promptForAudienceData = 'Assume someone enters the keyword ' . $seed_keyword . ' into a search engine. Analyze the following characteristics: 1. [demographic information] 2. [tone preferences] 3. [reading level preference] 4. [emotional needs/pain points]. This information will be used to create content that is specifically appealing to such people. Do not give content recommendations yet. As an output, write just information for characteristics without any explanation or introduction.';



	// Your chat messages

	$messages = [

		// ['role' => 'system', 'content' => 'You are a helpful assistant.'],

		['role' => 'user', 'content' => $promptForAudienceData]

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



	if (!empty($result['choices'][0]['message']['content'])) {

		return $result['choices'][0]['message']['content'];

	} else {

		return 0;

	}

}

function bulkAiTitle($getAudienceData, $question, $keyword_name, $tone_of_voice)

{

	global $wpdb, $user_ID;



	// Your OpenAI API key

	$apiKey = get_option('improveseo_chatgpt_api_key');



	// The endpoint URL for OpenAI chat completions API (replace with the correct endpoint)

	$apiUrl = 'https://api.openai.com/v1/chat/completions';



	if ($tone_of_voice != '') {

		$tone_of_voice = 'voice of content must be ' . $tone_of_voice;

	}



	if ($question == 'normal') {

		$query_question = 'You are a content creator who creates SEO optimized titles for blog posts. You are provided a word or phrase that is searched by the reader, and the audience data of the reader, including demographic information, tone preferences, reading level preference and emotional needs/pain points. Using this information you should come up with the title that will be engaging and interesting for people who are described in the audience data and search provided word or phrase. In the title do not include emojis or hashtags. Limit characters not including spaces to 80-100. As an output, write just a title without explanation or introduction.

			Now generate a SEO optimized title based on the following information:

			Keyword: ' . $keyword_name . '

			Audience data: {' . $getAudienceData . '}';



		// $question = 'Create a compelling seo optimized blog post title based on the keyword `'.$seed_keyword.'` in the form of No Answer. No emojis. No hashtags. Limit characters not including spaces to 80-100. '.$content_type;

	} else if ($question == 'question') {

		$query_question = 'You are a content creator who creates SEO optimized titles for blog posts. You are provided a word or phrase that is searched by the reader, and the audience data of the reader, including demographic information, tone preferences, reading level preference and emotional needs/pain points. Using this information you should come up with a title that will be engaging and interesting for people who are described in the audience data and search provided word or phrase. Title should be formed as a question. In the title do not include emojis or hashtags. Limit characters not including spaces to 80-100. As an output, write just a title without explanation or introduction. 

				Now generate a SEO optimized title based on the following information:

					Keyword: ' . $keyword_name . '

					Audience data: {' . $getAudienceData . '}';

	} else {

		$query_question = $keyword_name;

	}



	// echo "????".$question;



	// Your chat messages

	$messages = [

		//['role' => 'system', 'content' => $getAudienceData],

		['role' => 'user', 'content' => $query_question]

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

	return $result['choices'][0]['message']['content'];

}

function generateBulkAiImage($title, $AudienceData)

{



	$basicImagePromptResponse = ImageBasicPrompt($title);



	/*$AudienceData = $_COOKIE['AudienceData'];*/

	$imgPrompt = 'You should come up with the cover image for an article. The image should be a very high quality shooting from a distance, high detail, photorealistic, image resolution is  2146 pixels, cinematic. Do not include any text on the image. Using the following information generate an image.  ' . $basicImagePromptResponse;







	$dateTimeDefault = date('YmdHis');

	$imagename = 'ai_image_' . $dateTimeDefault;

	$seed_title = $imagename;

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



						return $image_url;

						// exit();

					} else {

						//   echo 'Error saving the image file.';

						//   exit();

					}

				} else {

					//return 'Error fetching image data from URL.';

				}

			}



			$attempt++;

		} while ($attempt < $maxAttempts);



		return 'Timeout waiting for image generation.';

	} else {

		return $result;

	}



}

function createBulkAIpost($seed_keyword, $keyword_selection, $seed_options, $nos_of_words, $content_lang, $shortcode = '', $is_single_keyword = '', $voice_tone = '', $point_of_view = '', $title, $call_to_action = '', $details_to_include = '')

{

	global $wpdb, $user_ID;



	// Your OpenAI API key

	$apiKey = get_option('improveseo_chatgpt_api_key');



	// The endpoint URL for OpenAI chat completions API (replace with the correct endpoint)

	$apiUrl = 'https://api.openai.com/v1/chat/completions';

	$AudienceData = $_COOKIE['AudienceData'];

	// create LSI keywords

	$text_for_lsi = 'As an expert SEO manager, you are tasked with generating 50 Latent Semantic Indexing (LSI) keywords. You are provided a word or phrase that is searched by the reader, and the audience data of the reader, including demographic information, tone preferences, reading level preference and emotional needs/pain points. Using this information you should come up with the LSI keywords that will be engaging and interesting for the reader who is described in the audience data and search provided word or phrase. These keywords should be closely related to the provided main keyword, enhancing content relevance and SEO effectiveness. Please compile the keywords in a comma separated text format without any additional explanations or introductions.

   Main keyword: ' . $seed_keyword . '

   Audience data: {' . $AudienceData . '}';

	// Your chat messages

	$messages = [

		['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

		['role' => 'user', 'content' => $text_for_lsi]

		// ['role' => 'assistant', 'content' => 'Hello, how can I help you today?'],

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

	$LSI_Keyords = $result['choices'][0]['message']['content'];











	$facts_prompt = 'Generate 5 most interesting and fun facts with specific details about the "Main keyword" for the audience described in the audience data provided below. Each fact should be one short sentence. As an output, write just a bullet point list of facts without explanation or introduction.

Now generate facts.

Main Keyword: ' . $seed_keyword . '

Audience data: {' . $AudienceData . '}';

	//['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],

	$messages = [

		['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

		['role' => 'user', 'content' => $facts_prompt]

		// ['role' => 'assistant', 'content' => 'Hello, how can I help you today?'],

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

	$facts_prompt_response = $result['choices'][0]['message']['content'];







	///////  nos_of_words  small///////

	if ($nos_of_words == '600 to 1200 words') {

		// small













		$basic_prompt = 'You are a content creator who creates SEO-optimized blog posts. You should aim at a broad audience. Use a mix of short, medium, and long sentences to create a human-like rhythm in the text. Include an analogy to explain any complex concepts or ideas. You should identify the intentions and emotions of the readers as described in the audience data. Your goal is to respond to these emotions and interests with this blog post. Consider the perspectives of both an expert and a beginner. IMPORTANT: Use standard language; avoid academic, scholarly, slang, and jargon. Follow the instructions for the tone preferences based on audience data. Write in a conversational tone and let your personality shine through. This helps build a connection with your audience. It is also important to strike a balance between being relatable/personable and being factual/authoritative. Use positive and encouraging language. NLP emphasizes the impact of positive reinforcement, which can motivate and inspire your readers.



			The user defines the main keyword, and you should make sure that the post is relevant to the main keyword.

			The user provides a title and makes sure that the post is relevant to it. 

			The user provides 50 LSI keywords and tries to incorporate them naturally throughout the content.

			Audience data: The user will include the audience data of the reader, including demographic information, tone preferences, reading level preference, and emotional needs/pain points. Use this information to tailor the content to the audience described in the audience data. Content should respond to their Emotional Needs and Pain Points.

			Details to include: The user will define additional details that need to be incorporated into the blog post.

			Language - The user defines that you should use US English, UK English, or German for the output. The headlines should be in the defined language as well.

			

			Include the following sections in the post:

			

			Introduction - Provide a concise preview of the content`s value and insights and write an engaging and informative introduction, incorporating the primary keyword, applying NLP and EI principles for emotional resonance. Do not create a header for this section, only provide the paragraph. 

			

			Table of Contents - Outline main content areas of the post. Craft attention-grabbing subtitles that entice readers to click and read more. Use numbers, questions, and powerful words to draw interest. Use NLP techniques to craft subtitles that grab attention. Incorporate power words and questions to stimulate curiosity and engagement. Based on the main keyword and the audience data provided to you, you need to understand what are the emotions and intentions reader has while searching it. You should understand what deep questions and concerns user wants to answer and build your subtitles(subsections) based on these. Do not list Section titles, make short list of subtitles that will be described in Main Content Section, do not include numbering in the list of subtitles. Make engaging titles in the Table of Contents. 

			

			Main Content Sections - Create content sections with subtitles using keywords and their variations at a 1-2% usage rate per 100 words to prevent keyword stuffing. Each section should contain a detailed content, employing NLP and EI for relatability and actionability. Make the content deep so it responds to the emotions and curiosity of the readers. Use storytelling techniques to make your content more relatable and memorable. Share personal anecdotes, case studies, and real-life examples. Stories are a powerful NLP tool to create an emotional connection. Share personal anecdotes or relatable scenarios to make your content more engaging and memorable. Based on the main keyword and the audience data provided to you, you need to understand what are the emotions and intentions user has while searching it. You should understand what deep questions and concerns users want to answer and build your output based on these. Use the following NLP Techniques for creating content:

				Anchoring: Use anchoring to associate positive emotions with your content. For instance, repeatedly use a specific phrase or concept that evokes a positive response.

				Reframing: Present your points in a way that shifts the reader is perspective. For example, instead of highlighting a problem, focus on the opportunity it presents.

				Vivid Descriptions: Use descriptive language to paint vivid images and evoke emotions. This helps readers feel more connected to your content.

				Addressing Reader Emotions: Acknowledge and validate the emotions your readers might be experiencing. This creates a sense of understanding and connection.

				High-Quality Content: Ensure your content is well-researched, informative, and adds value to your readers. Provide actionable insights and practical tips.

			

			Conclusion - Summarize key insights, encouraging further exploration or engagement. 

			

			FAQ - Come up with 3 FAQ that the reader may have. Provide questions and answers with clear, informative, tone empathize with the reader`s concerns.

			

			What’s Next? - Write a short paragraph inviting the reader to take action in the explained way, including links or phone numbers if provided. Incorporate "Call tu action" provided by user. If call to action is blank you should write a general paragraph without specific contact details or further steps anyway.

			

			Use the following formatting and structure for the output:

			{

			IMPORTANT: Never include the Blog Post Title. Start with the introduction paragraph

			

			Introduction - Introduction should not be more than 100-150 words.(do not include any title, just paragraph)

			

			<h2>Table of Contents</h2> (Heading 2) - should not be more than 50 words

			

			<h2>Main Content Sections</h2> (Heading 2) - Create 4 sections. Each section should not be more than 200-250 words of detailed content.

			

			<h2>Conclusion</h2> (Heading 2) - Conclusion should not be more than 100-150 words.

			

			<h2>FAQs</h2> (Heading 2) - FAQs should not be more than 100-150 words.

			Q: 

			A:

			

			Q: 

			A: 

			

			Q:

			A: 

			

			<h2>What is next?</h2> (Heading 2) - What is next? should not be more than 100-150 words.

			}

			

			Use the iterative approach to improve upon your initial draft. After each draft, critique your work, give it a score out of 10, and if the score is below 9, improve upon the previous draft. Repeat this process until you achieve a score of 9 or 10. When doing this, review and edit your work to remove any grammatical errors, unnecessary information, and superfluous sentences. Don`t provide output of this critique, this is only for you to analyze internally. Also, check the formatting, output should not include a title of the blog post and each section/subsection should have a title with a specific heading type. 

			Now generate ONLY the Introduction and the Table of Contents based on the following parameters:



				Main keyword: ' . $seed_keyword . '

				Title: "' . $title . '"

				LSI keywords: ' . $LSI_Keyords . '

				Tone of voice: ' . $voice_tone . ' 

				Point of view: ' . $point_of_view . '

				Audience data: {' . $AudienceData . '}

				Details to include: ' . $details_to_include . ' 

				Language: ' . $content_lang . '

				Call to action from user: `' . $call_to_action . '`

				Facts to include: {' . $facts_prompt_response . '} . Do not print "Main Content Sections" text in output. Do not print "#" text in output. ';













		// Your chat messages

		$messages = [

			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

			['role' => 'user', 'content' => $basic_prompt]

			// ['role' => 'assistant', 'content' => 'Hello, how can I help you today?'],

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



		$basic_prompt_response = $result['choices'][0]['message']['content'];



		my_plugin_log('Basic prompt to chatGPT (Input) : ' . $basic_prompt);

		my_plugin_log('Basic prompt response from chatGPT : ' . $basic_prompt_response);





		$first_call_for_small = 'Now generate the first subtitle content. IMPORTANT: Output should not be more than 200-250 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';

		// Your chat messages

		$messages = [

			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

			['role' => 'user', 'content' => $basic_prompt],

			['role' => 'assistant', 'content' => $basic_prompt_response],

			['role' => 'user', 'content' => $first_call_for_small]

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

		$response_first_call_for_small = $result['choices'][0]['message']['content'];









		// second call

		$second_call_for_small = 'Now generate the second subtitle content. IMPORTANT: Output should not be more than 200-250 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';

		// Your chat messages

		$messages = [

			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

			['role' => 'user', 'content' => $basic_prompt],

			['role' => 'assistant', 'content' => $basic_prompt_response],

			['role' => 'user', 'content' => $first_call_for_small],

			['role' => 'assistant', 'content' => $response_first_call_for_small],

			['role' => 'user', 'content' => $second_call_for_small],

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

		$response_secound_call_for_small = $result['choices'][0]['message']['content'];









		///// third call





		$third_call_for_small = 'Now generate the third subtitle content. IMPORTANT: Output should not be more than 200-250 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';

		// Your chat messages

		$messages = [

			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

			['role' => 'user', 'content' => $basic_prompt],

			['role' => 'assistant', 'content' => $basic_prompt_response],

			['role' => 'user', 'content' => $first_call_for_small],

			['role' => 'assistant', 'content' => $response_first_call_for_small],

			['role' => 'user', 'content' => $second_call_for_small],

			['role' => 'assistant', 'content' => $response_secound_call_for_small],

			['role' => 'user', 'content' => $third_call_for_small],

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

		$response_third_call_for_small = $result['choices'][0]['message']['content'];









		/////// 4th call 



		$fourth_call_for_small = 'Now generate the forth subtitle content. IMPORTANT: Output should not be more than 200-250 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';

		// Your chat messages

		$messages = [

			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

			['role' => 'user', 'content' => $basic_prompt],

			['role' => 'assistant', 'content' => $basic_prompt_response],

			['role' => 'user', 'content' => $first_call_for_small],

			['role' => 'assistant', 'content' => $response_first_call_for_small],

			['role' => 'user', 'content' => $second_call_for_small],

			['role' => 'assistant', 'content' => $response_secound_call_for_small],

			['role' => 'user', 'content' => $third_call_for_small],

			['role' => 'assistant', 'content' => $response_third_call_for_small],

			['role' => 'user', 'content' => $fourth_call_for_small],

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

		$response_fourth_call_for_small = $result['choices'][0]['message']['content'];















		// 5th call 





		$fifth_call_for_small = 'Now generate the conclusion content. IMPORTANT: Output should not be more than 100-150 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';

		// Your chat messages

		$messages = [

			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

			['role' => 'user', 'content' => $basic_prompt],

			['role' => 'assistant', 'content' => $basic_prompt_response],

			['role' => 'user', 'content' => $first_call_for_small],

			['role' => 'assistant', 'content' => $response_first_call_for_small],

			['role' => 'user', 'content' => $second_call_for_small],

			['role' => 'assistant', 'content' => $response_secound_call_for_small],

			['role' => 'user', 'content' => $third_call_for_small],

			['role' => 'assistant', 'content' => $response_third_call_for_small],

			['role' => 'user', 'content' => $fourth_call_for_small],

			['role' => 'assistant', 'content' => $response_fourth_call_for_small],

			['role' => 'user', 'content' => $fifth_call_for_small],

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

		$response_fifth_call_for_small = $result['choices'][0]['message']['content'];



















		// 6th call 





		$sixth_call_for_small = 'Now generate the FAQs content. IMPORTANT: Output should not be more than 100-150 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';

		// Your chat messages

		$messages = [

			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

			['role' => 'user', 'content' => $basic_prompt],

			['role' => 'assistant', 'content' => $basic_prompt_response],

			['role' => 'user', 'content' => $first_call_for_small],

			['role' => 'assistant', 'content' => $response_first_call_for_small],

			['role' => 'user', 'content' => $second_call_for_small],

			['role' => 'assistant', 'content' => $response_secound_call_for_small],

			['role' => 'user', 'content' => $third_call_for_small],

			['role' => 'assistant', 'content' => $response_third_call_for_small],

			['role' => 'user', 'content' => $fourth_call_for_small],

			['role' => 'assistant', 'content' => $response_fourth_call_for_small],

			['role' => 'user', 'content' => $fifth_call_for_small],

			['role' => 'assistant', 'content' => $response_fifth_call_for_small],

			['role' => 'user', 'content' => $sixth_call_for_small],

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

		$response_sixth_call_for_small = $result['choices'][0]['message']['content'];









		// 7th call 





		$seventh_call_for_small = 'Now generate What is next? content. IMPORTANT: Output should not be more than 100-150 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.

			';

		// Your chat messages

		$messages = [

			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

			['role' => 'user', 'content' => $basic_prompt],

			['role' => 'assistant', 'content' => $basic_prompt_response],

			['role' => 'user', 'content' => $first_call_for_small],

			['role' => 'assistant', 'content' => $response_first_call_for_small],

			['role' => 'user', 'content' => $second_call_for_small],

			['role' => 'assistant', 'content' => $response_secound_call_for_small],

			['role' => 'user', 'content' => $third_call_for_small],

			['role' => 'assistant', 'content' => $response_third_call_for_small],

			['role' => 'user', 'content' => $fourth_call_for_small],

			['role' => 'assistant', 'content' => $response_fourth_call_for_small],

			['role' => 'user', 'content' => $fifth_call_for_small],

			['role' => 'assistant', 'content' => $response_fifth_call_for_small],

			['role' => 'user', 'content' => $sixth_call_for_small],

			['role' => 'assistant', 'content' => $response_sixth_call_for_small],

			['role' => 'user', 'content' => $seventh_call_for_small],

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

		$response_seventh_call_for_small = $result['choices'][0]['message']['content'];





		/*return array("first_subtitle"=>$response_first_call_for_small,

							 "second_subtitle"=>$response_secound_call_for_small,

							 "third_subtitle"=>$response_third_call_for_small,

							 "fourth_subtitle"=>$response_fourth_call_for_small,

							 "conclusion"=>$response_fifth_call_for_small,

							 "faq"=>$response_sixth_call_for_small,

							 "whats_next"=>$response_seventh_call_for_small);*/

		$content_final = '<div class="main-content-section-improveseo">' . $basic_prompt_response . '<div style="margin-bottom: 15px;margin-top: 50px;">' . $response_first_call_for_small . '</div><div style="margin-bottom: 15px;margin-top: 50px;">' . $response_secound_call_for_small . '</div><div style="margin-bottom: 15px;margin-top: 50px;">' . $response_third_call_for_small . '</div><div style="margin-bottom: 15px;margin-top: 50px;">' . $response_fourth_call_for_small . '</div><div style="margin-bottom: 15px;margin-top: 50px;">' . $response_fifth_call_for_small . '</div><div style="margin-bottom: 15px;margin-top: 50px;">' . $response_sixth_call_for_small . '</div><div style="margin-bottom: 15px;margin-top: 50px;">' . $response_seventh_call_for_small . '</div></div>';





	} elseif ($nos_of_words == '1200 to 2400 words') {

		// medium





		$basic_prompt = 'You are a content creator who creates SEO-optimized blog posts. You should aim at a broad audience. Ensure that sentence and paragraph lengths are varied, as is seen in natural human writing. Ensure heterogeneous paragraphs. Ensure heterogeneous sentence lengths. And stick to primarily short, straightforward sentences to create a human-like rhythm in the text. Engagement is the highest priority. Be conversational, empathetic, and occasionally humorous. Use idioms, metaphors, anecdotes and natural dialogue. Include an analogy to explain any complex concepts or ideas. You should identify the intentions and emotions of the readers as described in the audience data. Your goal is to respond to these emotions and interests with this blog post. Consider the perspectives of both an expert and a beginner. IMPORTANT: Use standard language; avoid academic, scholarly, slang, and jargon. Follow the instructions for the tone preferences based on audience data. Write in a conversational tone and let your personality shine through. This helps build a connection with your audience. It is also important to strike a balance between being relatable/personable and being factual/authoritative. Use positive and encouraging language. NLP emphasizes the impact of positive reinforcement, which can motivate and inspire your readers. Don’t always use the most natural words. Use the following words fewer than 3 times on this page: unique, ensure, utmost, transformative, transformation, routine. Before outputting the content, review it for the following words and rewrite those sentences with appropriate alternatives: meticulous, meticulously, navigating, complexities, realm, bespoke, tailored, towards, underpins, everchanging, ever-evolving, the world of, not only, seeking more than just, designed to enhance, it’s not merely, our suite, it is advisable, daunting, in the heart of, when it comes to, in the realm of, amongst unlock the secrets, unveil the secrets and robust.



The user defines the main keyword, and you should make sure that the post is relevant to the main keyword.

The user provides a title and makes sure that the post is relevant to it. 

The user provides 50 LSI keywords and tries to incorporate them naturally throughout the content.

The user provides "Facts to include" that should be smoothly incorporated in the introduction and the Main content sections of the output.

Audience data: The user will include the audience data of the reader, including demographic information, tone preferences, reading level preference, and emotional needs/pain points. Use this information to tailor the content to the audience described in the audience data. Content should respond to their Emotional Needs and Pain Points.

Details to include: The user will define additional details that need to be incorporated into the blog post.

Language - The user defines that you should use US English, UK English, or German for the output. The headlines should be in the defined language as well.



Include the following sections in the post:



Introduction - Provide a concise preview of the content`s value and insights and write an engaging and informative introduction, incorporating the primary keyword, applying NLP and EI principles for emotional resonance. Use the "Facts to include" provided by the user. Do not use all of them. Incorporate them smoothly so that it is part of the story flow and reads naturally. Don’t create a header for this section, only provide the paragraph. 



Table of Contents - Outline main content areas of the post. Craft attention-grabbing subtitles that entice readers to click and read more. Use numbers, questions, and powerful words to draw interest. Use NLP techniques to craft subtitles that grab attention. Incorporate power words and questions to stimulate curiosity and engagement. Based on the main keyword and the audience data provided to you, you need to understand what are the emotions and intentions reader has while searching it. You should understand what deep questions and concerns user wants to answer and build your subtitles(subsections) based on these. Do not list Section titles, make short list of subtitles that will be described in Main Content Section, do not include numbering in the list of subtitles. Make engaging titles in the Table of Contents. 



Main Content Sections - Create content sections with subtitles using keywords and their variations at a 1-2% usage rate per 100 words to prevent keyword stuffing. Each section should contain a detailed content, employing NLP and EI for relatability and actionability. Make the content deep so it responds to the emotions and curiosity of the readers. Use storytelling techniques to make your content more relatable and memorable. Share personal anecdotes, case studies, and real-life examples. Stories are a powerful NLP tool to create an emotional connection. Share personal anecdotes or relatable scenarios to make your content more engaging and memorable. Prevent from producing worthless fluff content that doesn’t add to the value of the blog post. Do not include any fluff when producing content. Each sentence should provide value to the overall goal of the content piece. Strictly follow this guideline. Ensure to insert interesting and fun facts about the Main keyword when producing the content: use the "Facts to include" provided by the user. Do not use all of them. Incorporate them smoothly so that it is part of the story flow and reads naturally. DO NOT include any conclusion or summary for each content sections. Based on the main keyword and the audience data provided to you, you need to understand what are the emotions and intentions user has while searching it. You should understand what deep questions and concerns users want to answer and build your output based on these. Use the following NLP Techniques for creating content:

    Anchoring: Use anchoring to associate positive emotions with your content. For instance, repeatedly use a specific phrase or concept that evokes a positive response.

    Reframing: Present your points in a way that shifts the reader’s perspective. For example, instead of highlighting a problem, focus on the opportunity it presents.

    Vivid Descriptions: Use descriptive language to paint vivid images and evoke emotions. This helps readers feel more connected to your content.

    Addressing Reader Emotions: Acknowledge and validate the emotions your readers might be experiencing. This creates a sense of understanding and connection.

    High-Quality Content: Ensure your content is well-researched, informative, and adds value to your readers. Provide actionable insights and practical tips.



Conclusion - Summarize key insights, encouraging further exploration or engagement. Do not include call to action details in the conclusion. 



FAQ - Come up with 3 FAQ that the reader may have. Provide questions and answers with clear, informative, tone empathize with the reader`s concerns.



What’s Next? - Write a short paragraph inviting the reader to take action in the explained way, including links or phone numbers if provided. Incorporate "Call to action" provided by user. If call to action is blank you should write a general paragraph without specific contact details or further steps anyway.



Use the following formatting and structure for the output:

{

IMPORTANT: Never include the Blog Post Title. Start with the introduction paragraph



Introduction - Introduction should not be more than 100-150 words.(do not include any title, just paragraph)



<h2>Table of Contents</h2> (Heading 2) - should not be more than 50 words and formatted as a list with bullet points with normal text format



<h2>Main Content Sections</h2> (Heading 2) - Create 4 sections. Create 2-3 subsections and subtitles with formatting H3 for the each section so it does not exceed required word quantity. IMPORTANT: Each section should not be more than 350-400 words



<h2>Conclusion</h2> (Heading 2) - Conclusion should not be more than 100-150 words. Do not include call to action details in the conclusion.



<h2>FAQs</h2> (Heading 2) - FAQs should not be more than 100-150 words.

Q: 

A:



Q: 

A: 



Q:

A: 



<h2>What’s next?</h2> (Heading 2) - What’s next? should not be more than 100-150 words.

}



Use the iterative approach to improve upon your initial draft. After each draft, critique your work, give it a score out of 10, and if the score is below 9, improve upon the previous draft. Repeat this process until you achieve a score of 9 or 10. When doing this, review and edit your work to remove any grammatical errors, unnecessary information, and superfluous sentences. Don`t provide output of this critique, this is only for you to analyze internally. Also, check the formatting, output should not include a title of the blog post and each section/subsection should have a title with a specific heading type. 

Now generate ONLY the Introduction and the Table of Contents based on the following parameters:





				Main keyword: ' . $seed_keyword . '

				Title: "' . $title . '"

				LSI keywords: ' . $LSI_Keyords . '

				Tone of voice: ' . $voice_tone . ' 

				Point of view: ' . $point_of_view . '

				Audience data: {' . $AudienceData . '}

				Details to include: ' . $details_to_include . ' 

				Language: ' . $content_lang . '

				Call to action from user: `' . $call_to_action . '`

				Facts to include: {' . $facts_prompt_response . '} Do not print "Main Content Sections" text in output. Do not print "#" text in output. ';













		// Your chat messages

		$messages = [

			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

			['role' => 'user', 'content' => $basic_prompt]

			// ['role' => 'assistant', 'content' => 'Hello, how can I help you today?'],

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

		$basic_prompt_response = $result['choices'][0]['message']['content'];











		$first_call_for_medium = 'Now generate the first subtitle content. IMPORTANT: Output should not be more than 350-400 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';

		// Your chat messages

		$messages = [

			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

			['role' => 'user', 'content' => $basic_prompt],

			['role' => 'assistant', 'content' => $basic_prompt_response],

			['role' => 'user', 'content' => $first_call_for_medium]

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

		$response_first_call_for_medium = $result['choices'][0]['message']['content'];









		// second call

		$second_call_for_medium = 'Now generate the second subtitle content. IMPORTANT: Output should not be more than 350-400 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';

		// Your chat messages

		$messages = [

			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

			['role' => 'user', 'content' => $basic_prompt],

			['role' => 'assistant', 'content' => $basic_prompt_response],

			['role' => 'user', 'content' => $first_call_for_medium],

			['role' => 'assistant', 'content' => $response_first_call_for_medium],

			['role' => 'user', 'content' => $second_call_for_medium],

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

		$response_secound_call_for_medium = $result['choices'][0]['message']['content'];









		///// third call





		$third_call_for_medium = 'Now generate the third subtitle content. IMPORTANT: Output should not be more than 350-400 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';

		// Your chat messages

		$messages = [

			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

			['role' => 'user', 'content' => $basic_prompt],

			['role' => 'assistant', 'content' => $basic_prompt_response],

			['role' => 'user', 'content' => $first_call_for_medium],

			['role' => 'assistant', 'content' => $response_first_call_for_medium],

			['role' => 'user', 'content' => $second_call_for_medium],

			['role' => 'assistant', 'content' => $response_secound_call_for_medium],

			['role' => 'user', 'content' => $third_call_for_medium],

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

		$response_third_call_for_medium = $result['choices'][0]['message']['content'];









		/////// 4th call 



		$fourth_call_for_medium = 'Now generate the forth subtitle content. IMPORTANT: Output should not be more than 350-400 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';

		// Your chat messages

		$messages = [

			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

			['role' => 'user', 'content' => $basic_prompt],

			['role' => 'assistant', 'content' => $basic_prompt_response],

			['role' => 'user', 'content' => $first_call_for_medium],

			['role' => 'assistant', 'content' => $response_first_call_for_medium],

			['role' => 'user', 'content' => $second_call_for_medium],

			['role' => 'assistant', 'content' => $response_secound_call_for_medium],

			['role' => 'user', 'content' => $third_call_for_medium],

			['role' => 'assistant', 'content' => $response_third_call_for_medium],

			['role' => 'user', 'content' => $fourth_call_for_medium],

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

		$response_fourth_call_for_medium = $result['choices'][0]['message']['content'];















		// 5th call 





		$fifth_call_for_medium = 'Now generate the conclusion content. IMPORTANT: Output should not be more than 150-200 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';

		// Your chat messages

		$messages = [

			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

			['role' => 'user', 'content' => $basic_prompt],

			['role' => 'assistant', 'content' => $basic_prompt_response],

			['role' => 'user', 'content' => $first_call_for_medium],

			['role' => 'assistant', 'content' => $response_first_call_for_medium],

			['role' => 'user', 'content' => $second_call_for_medium],

			['role' => 'assistant', 'content' => $response_secound_call_for_medium],

			['role' => 'user', 'content' => $third_call_for_medium],

			['role' => 'assistant', 'content' => $response_third_call_for_medium],

			['role' => 'user', 'content' => $fourth_call_for_medium],

			['role' => 'assistant', 'content' => $response_fourth_call_for_medium],

			['role' => 'user', 'content' => $fifth_call_for_medium],

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

		$response_fifth_call_for_medium = $result['choices'][0]['message']['content'];



















		// 6th call 





		$sixth_call_for_medium = 'Now generate the FAQs content. IMPORTANT: Output should not be more than 100-150 words. After writing an output check the word count and regenerate if it is not in the rage. Do not include the word count in the output.';

		// Your chat messages

		$messages = [

			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

			['role' => 'user', 'content' => $basic_prompt],

			['role' => 'assistant', 'content' => $basic_prompt_response],

			['role' => 'user', 'content' => $first_call_for_medium],

			['role' => 'assistant', 'content' => $response_first_call_for_medium],

			['role' => 'user', 'content' => $second_call_for_medium],

			['role' => 'assistant', 'content' => $response_secound_call_for_medium],

			['role' => 'user', 'content' => $third_call_for_medium],

			['role' => 'assistant', 'content' => $response_third_call_for_medium],

			['role' => 'user', 'content' => $fourth_call_for_medium],

			['role' => 'assistant', 'content' => $response_fourth_call_for_medium],

			['role' => 'user', 'content' => $fifth_call_for_medium],

			['role' => 'assistant', 'content' => $response_fifth_call_for_medium],

			['role' => 'user', 'content' => $sixth_call_for_medium],

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

		$response_sixth_call_for_medium = $result['choices'][0]['message']['content'];









		// 7th call 





		$seventh_call_for_medium = 'Now generate What is next? content. IMPORTANT: Output should not be more than 150-200 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';

		// Your chat messages

		$messages = [

			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

			['role' => 'user', 'content' => $basic_prompt],

			['role' => 'assistant', 'content' => $basic_prompt_response],

			['role' => 'user', 'content' => $first_call_for_medium],

			['role' => 'assistant', 'content' => $response_first_call_for_medium],

			['role' => 'user', 'content' => $second_call_for_medium],

			['role' => 'assistant', 'content' => $response_secound_call_for_medium],

			['role' => 'user', 'content' => $third_call_for_medium],

			['role' => 'assistant', 'content' => $response_third_call_for_medium],

			['role' => 'user', 'content' => $fourth_call_for_medium],

			['role' => 'assistant', 'content' => $response_fourth_call_for_medium],

			['role' => 'user', 'content' => $fifth_call_for_medium],

			['role' => 'assistant', 'content' => $response_fifth_call_for_medium],

			['role' => 'user', 'content' => $sixth_call_for_medium],

			['role' => 'assistant', 'content' => $response_sixth_call_for_medium],

			['role' => 'user', 'content' => $seventh_call_for_medium],

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

		$response_seventh_call_for_medium = $result['choices'][0]['message']['content'];





		/*return array("first_subtitle"=>$response_first_call_for_medium ,

							 "second_subtitle"=>$response_secound_call_for_medium ,

							 "third_subtitle"=>$response_third_call_for_medium ,

							 "fourth_subtitle"=>$response_fourth_call_for_medium ,

							 "conclusion"=>$response_fifth_call_for_medium ,

							 "faq"=>$response_sixth_call_for_medium ,

							 "whats_next"=>$response_seventh_call_for_medium );*/

		//$content_final = $basic_prompt_response.'<br><br>'.$response_first_call_for_medium .'<br><br>'.$response_secound_call_for_medium .'<br><br>'.$response_third_call_for_medium .'<br><br>'.$response_fourth_call_for_medium .'<br><br>'.$response_fifth_call_for_medium .'<br><br>'.$response_sixth_call_for_medium .'<br><br>'.$response_seventh_call_for_medium ;



		$content_final = '<div class="main-content-section-improveseo">' . $basic_prompt_response . '<div style="margin-bottom: 15px;margin-top: 50px;">' . $response_first_call_for_medium . '</div><div style="margin-bottom: 15px;margin-top: 50px;">' . $response_secound_call_for_medium . '</div><div style="margin-bottom: 15px;margin-top: 50px;">' . $response_third_call_for_medium . '</div><div style="margin-bottom: 15px;margin-top: 50px;">' . $response_fourth_call_for_medium . '</div><div style="margin-bottom: 15px;margin-top: 50px;">' . $response_fifth_call_for_medium . '</div><div style="margin-bottom: 15px;margin-top: 50px;">' . $response_sixth_call_for_medium . '</div><div style="margin-bottom: 15px;margin-top: 50px;">' . $response_seventh_call_for_medium . '</div></div>';







	} else {

		//large









		$basic_prompt = 'You are a content creator who creates SEO-optimized blog posts. You should aim at a broad audience. Ensure that sentence and paragraph lengths are varied, as is seen in natural human writing. Ensure heterogeneous paragraphs. Ensure heterogeneous sentence lengths. And stick to primarily short, straightforward sentences to create a human-like rhythm in the text. Engagement is the highest priority. Be conversational, empathetic, and occasionally humorous. Use idioms, metaphors, anecdotes and natural dialogue. Include an analogy to explain any complex concepts or ideas. You should identify the intentions and emotions of the readers as described in the audience data. Your goal is to respond to these emotions and interests with this blog post. Consider the perspectives of both an expert and a beginner. IMPORTANT: Use standard language; avoid academic, scholarly, slang, and jargon. Follow the instructions for the tone preferences based on audience data. Write in a conversational tone and let your personality shine through. This helps build a connection with your audience. It is also important to strike a balance between being relatable/personable and being factual/authoritative. Use positive and encouraging language. NLP emphasizes the impact of positive reinforcement, which can motivate and inspire your readers. Don’t always use the most natural words. Use the following words fewer than 3 times on this page: unique, ensure, utmost, transformative, transformation, routine. Before outputting the content, review it for the following words and rewrite those sentences with appropriate alternatives: meticulous, meticulously, navigating, complexities, realm, bespoke, tailored, towards, underpins, everchanging, ever-evolving, the world of, not only, seeking more than just, designed to enhance, it’s not merely, our suite, it is advisable, daunting, in the heart of, when it comes to, in the realm of, amongst unlock the secrets, unveil the secrets and robust.



The user defines the main keyword, and you should make sure that the post is relevant to the main keyword.

The user provides a title and makes sure that the post is relevant to it. 

The user provides 50 LSI keywords and tries to incorporate them naturally throughout the content.

The user provides "Facts to include" that should be smoothly incorporated in the introduction and the Main content sections of the output.

Audience data: The user will include the audience data of the reader, including demographic information, tone preferences, reading level preference, and emotional needs/pain points. Use this information to tailor the content to the audience described in the audience data. Content should respond to their Emotional Needs and Pain Points.

Details to include: The user will define additional details that need to be incorporated into the blog post.

Language - The user defines that you should use US English, UK English, or German for the output. The headlines should be in the defined language as well.



Include the following sections in the post:



Introduction - Provide a concise preview of the content`s value and insights and write an engaging and informative introduction, incorporating the primary keyword, applying NLP and EI principles for emotional resonance. Use the "Facts to include" provided by the user. Do not use all of them. Incorporate them smoothly so that it is part of the story flow and reads naturally. Don’t create a header for this section, only provide the paragraph. 



Table of Contents - Outline main content areas of the post. Craft attention-grabbing subtitles that entice readers to click and read more. Use numbers, questions, and powerful words to draw interest. Use NLP techniques to craft subtitles that grab attention. Incorporate power words and questions to stimulate curiosity and engagement. Based on the main keyword and the audience data provided to you, you need to understand what are the emotions and intentions reader has while searching it. You should understand what deep questions and concerns user wants to answer and build your subtitles(subsections) based on these. Do not list Section titles, make short list of subtitles that will be described in Main Content Section, do not include numbering in the list of subtitles. Make engaging titles in the Table of Contents. 



Main Content Sections - Create content sections with subtitles using keywords and their variations at a 1-2% usage rate per 100 words to prevent keyword stuffing. Each section should contain a detailed content, employing NLP and EI for relatability and actionability. Make the content deep so it responds to the emotions and curiosity of the readers. Use storytelling techniques to make your content more relatable and memorable. Share personal anecdotes, case studies, and real-life examples. Stories are a powerful NLP tool to create an emotional connection. Share personal anecdotes or relatable scenarios to make your content more engaging and memorable. Prevent from producing worthless fluff content that doesn’t add to the value of the blog post. Do not include any fluff when producing content. Each sentence should provide value to the overall goal of the content piece. Strictly follow this guideline. Ensure to insert interesting and fun facts about the Main keyword when producing the content: use the "Facts to include" provided by the user. Do not use all of them. Incorporate them smoothly so that it is part of the story flow and reads naturally. DO NOT include any conclusion or summary for each content sections. Based on the main keyword and the audience data provided to you, you need to understand what are the emotions and intentions user has while searching it. You should understand what deep questions and concerns users want to answer and build your output based on these. Use the following NLP Techniques for creating content:

    Anchoring: Use anchoring to associate positive emotions with your content. For instance, repeatedly use a specific phrase or concept that evokes a positive response.

    Reframing: Present your points in a way that shifts the reader’s perspective. For example, instead of highlighting a problem, focus on the opportunity it presents.

    Vivid Descriptions: Use descriptive language to paint vivid images and evoke emotions. This helps readers feel more connected to your content.

    Addressing Reader Emotions: Acknowledge and validate the emotions your readers might be experiencing. This creates a sense of understanding and connection.

    High-Quality Content: Ensure your content is well-researched, informative, and adds value to your readers. Provide actionable insights and practical tips.



Conclusion - Summarize key insights, encouraging further exploration or engagement. Do not include call to action details in the conclusion. 



FAQ - Come up with 3 FAQ that the reader may have. Provide questions and answers with clear, informative, tone empathize with the reader`s concerns.



What’s Next? - Write a short paragraph inviting the reader to take action in the explained way, including links or phone numbers if provided. Incorporate "Call to action" provided by user. If call to action is blank you should write a general paragraph without specific contact details or further steps anyway.



Use the following formatting and structure for the output:

{

IMPORTANT: Never include the Blog Post Title. Start with the introduction paragraph



Introduction - Introduction should not be more than 100-150 words.(do not include any title, just paragraph)



<h2>Table of Contents</h2> (Heading 2) - should not be more than 50 words and formatted as a list with bullet points with normal text format



<h2>Main Content Sections</h2> (Heading 2) - Create 5 sections. Create 2-3 subsections and subtitles with formatting H3 for each section so it does not exceed required word quantity. IMPORTANT: Each section should not be more than 450-600 words. (Do not include the header ‘Main Content Sections’)





<h2>Conclusion</h2> (Heading 2) - Conclusion should not be more than 100-150 words. Do not include call to action details in the conclusion.



<h2>FAQs</h2> (Heading 2) - FAQs should not be more than 100-150 words.

Q: 

A:



Q: 

A: 



Q:

A: 



<h2>What’s next?</h2> (Heading 2) - What’s next? should not be more than 100-150 words.

}



Use the iterative approach to improve upon your initial draft. After each draft, critique your work, give it a score out of 10, and if the score is below 9, improve upon the previous draft. Repeat this process until you achieve a score of 9 or 10. When doing this, review and edit your work to remove any grammatical errors, unnecessary information, and superfluous sentences. Don`t provide output of this critique, this is only for you to analyze internally. Also, check the formatting, output should not include a title of the blog post and each section/subsection should have a title with a specific heading type. 

Now generate ONLY the Introduction and the Table of Contents based on the following parameters:

				Main keyword: ' . $seed_keyword . '

				Title: "' . $title . '"

				LSI keywords: ' . $LSI_Keyords . '

				Tone of voice: ' . $voice_tone . ' 

				Point of view: ' . $point_of_view . '

				Audience data: {' . $AudienceData . '}

				Details to include: ' . $details_to_include . ' 

				Language: ' . $content_lang . '

				Call to action from user: `' . $call_to_action . '`

				Facts to include: {' . $facts_prompt_response . '} Do not print "Main Content Sections" text in output. Do not print "#" text in output. ';













		// Your chat messages

		$messages = [

			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

			['role' => 'user', 'content' => $basic_prompt]

			// ['role' => 'assistant', 'content' => 'Hello, how can I help you today?'],

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

		$basic_prompt_response = $result['choices'][0]['message']['content'];











		$first_call_for_large = 'Now generate the first subtitle content. IMPORTANT: Output should not be more than 450-600 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';

		// Your chat messages

		$messages = [

			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

			['role' => 'user', 'content' => $basic_prompt],

			['role' => 'assistant', 'content' => $basic_prompt_response],

			['role' => 'user', 'content' => $first_call_for_large]

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

		$response_first_call_for_large = $result['choices'][0]['message']['content'];









		// second call

		$second_call_for_large = 'Now generate the second subtitle content. IMPORTANT: Output should not be more than 450-600 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';

		// Your chat messages

		$messages = [

			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

			['role' => 'user', 'content' => $basic_prompt],

			['role' => 'assistant', 'content' => $basic_prompt_response],

			['role' => 'user', 'content' => $first_call_for_large],

			['role' => 'assistant', 'content' => $response_first_call_for_large],

			['role' => 'user', 'content' => $second_call_for_large],

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

		$response_secound_call_for_large = $result['choices'][0]['message']['content'];









		///// third call





		$third_call_for_large = 'Now generate the third subtitle content. IMPORTANT: Output should not be more than 450-600 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';

		// Your chat messages

		$messages = [

			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

			['role' => 'user', 'content' => $basic_prompt],

			['role' => 'assistant', 'content' => $basic_prompt_response],

			['role' => 'user', 'content' => $first_call_for_large],

			['role' => 'assistant', 'content' => $response_first_call_for_large],

			['role' => 'user', 'content' => $second_call_for_large],

			['role' => 'assistant', 'content' => $response_secound_call_for_large],

			['role' => 'user', 'content' => $third_call_for_large],

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

		$response_third_call_for_large = $result['choices'][0]['message']['content'];









		/////// 4th call 



		$fourth_call_for_large = 'Now generate the forth subtitle content. IMPORTANT: Output should not be more than 450-600 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';

		// Your chat messages

		$messages = [

			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

			['role' => 'user', 'content' => $basic_prompt],

			['role' => 'assistant', 'content' => $basic_prompt_response],

			['role' => 'user', 'content' => $first_call_for_large],

			['role' => 'assistant', 'content' => $response_first_call_for_large],

			['role' => 'user', 'content' => $second_call_for_large],

			['role' => 'assistant', 'content' => $response_secound_call_for_large],

			['role' => 'user', 'content' => $third_call_for_large],

			['role' => 'assistant', 'content' => $response_third_call_for_large],

			['role' => 'user', 'content' => $fourth_call_for_large],

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

		$response_fourth_call_for_large = $result['choices'][0]['message']['content'];















		// 5th call 





		$fifth_call_for_large = 'Now generate the fifth subtitle content. IMPORTANT: Output should not be more than 450-600 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';

		// Your chat messages

		$messages = [

			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

			['role' => 'user', 'content' => $basic_prompt],

			['role' => 'assistant', 'content' => $basic_prompt_response],

			['role' => 'user', 'content' => $first_call_for_large],

			['role' => 'assistant', 'content' => $response_first_call_for_large],

			['role' => 'user', 'content' => $second_call_for_large],

			['role' => 'assistant', 'content' => $response_secound_call_for_large],

			['role' => 'user', 'content' => $third_call_for_large],

			['role' => 'assistant', 'content' => $response_third_call_for_large],

			['role' => 'user', 'content' => $fourth_call_for_large],

			['role' => 'assistant', 'content' => $response_fourth_call_for_large],

			['role' => 'user', 'content' => $fifth_call_for_large],

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

		$response_fifth_call_for_large = $result['choices'][0]['message']['content'];



















		// 6th call 





		$sixth_call_for_large = 'Now generate the conclusion content. IMPORTANT: Output should not be more than 150-200 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';

		// Your chat messages

		$messages = [

			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

			['role' => 'user', 'content' => $basic_prompt],

			['role' => 'assistant', 'content' => $basic_prompt_response],

			['role' => 'user', 'content' => $first_call_for_large],

			['role' => 'assistant', 'content' => $response_first_call_for_large],

			['role' => 'user', 'content' => $second_call_for_large],

			['role' => 'assistant', 'content' => $response_secound_call_for_large],

			['role' => 'user', 'content' => $third_call_for_large],

			['role' => 'assistant', 'content' => $response_third_call_for_large],

			['role' => 'user', 'content' => $fourth_call_for_large],

			['role' => 'assistant', 'content' => $response_fourth_call_for_large],

			['role' => 'user', 'content' => $fifth_call_for_large],

			['role' => 'assistant', 'content' => $response_fifth_call_for_large],

			['role' => 'user', 'content' => $sixth_call_for_large],

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

		$response_sixth_call_for_large = $result['choices'][0]['message']['content'];









		// 7th call 





		$seventh_call_for_large = 'Now generate the FAQs content. IMPORTANT: Output should not be more than 100-150 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';

		// Your chat messages

		$messages = [

			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

			['role' => 'user', 'content' => $basic_prompt],

			['role' => 'assistant', 'content' => $basic_prompt_response],

			['role' => 'user', 'content' => $first_call_for_large],

			['role' => 'assistant', 'content' => $response_first_call_for_large],

			['role' => 'user', 'content' => $second_call_for_large],

			['role' => 'assistant', 'content' => $response_secound_call_for_large],

			['role' => 'user', 'content' => $third_call_for_large],

			['role' => 'assistant', 'content' => $response_third_call_for_large],

			['role' => 'user', 'content' => $fourth_call_for_large],

			['role' => 'assistant', 'content' => $response_fourth_call_for_large],

			['role' => 'user', 'content' => $fifth_call_for_large],

			['role' => 'assistant', 'content' => $response_fifth_call_for_large],

			['role' => 'user', 'content' => $sixth_call_for_large],

			['role' => 'assistant', 'content' => $response_sixth_call_for_large],

			['role' => 'user', 'content' => $seventh_call_for_large],

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

		$response_seventh_call_for_large = $result['choices'][0]['message']['content'];







		//8th call











		$eigth_call_for_large = 'Now generate What is next? content. IMPORTANT: Output should not be more than 150-200 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';

		// Your chat messages

		$messages = [

			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

			['role' => 'user', 'content' => $basic_prompt],

			['role' => 'assistant', 'content' => $basic_prompt_response],

			['role' => 'user', 'content' => $first_call_for_large],

			['role' => 'assistant', 'content' => $response_first_call_for_large],

			['role' => 'user', 'content' => $second_call_for_large],

			['role' => 'assistant', 'content' => $response_secound_call_for_large],

			['role' => 'user', 'content' => $third_call_for_large],

			['role' => 'assistant', 'content' => $response_third_call_for_large],

			['role' => 'user', 'content' => $fourth_call_for_large],

			['role' => 'assistant', 'content' => $response_fourth_call_for_large],

			['role' => 'user', 'content' => $fifth_call_for_large],

			['role' => 'assistant', 'content' => $response_fifth_call_for_large],

			['role' => 'user', 'content' => $sixth_call_for_large],

			['role' => 'assistant', 'content' => $response_sixth_call_for_large],

			['role' => 'user', 'content' => $seventh_call_for_large],

			['role' => 'assistant', 'content' => $response_seventh_call_for_large],

			['role' => 'user', 'content' => $eigth_call_for_large],

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

		$response_eigth_call_for_large = $result['choices'][0]['message']['content'];



		$content_final = '<div class="main-content-section-improveseo">' . $basic_prompt_response . '<div style="margin-bottom: 15px;margin-top: 50px;">' . $response_first_call_for_large . '</div><div style="margin-bottom: 15px;margin-top: 50px;">' . $response_secound_call_for_large . '</div><div style="margin-bottom: 15px;margin-top: 50px;">' . $response_third_call_for_large . '</div><div style="margin-bottom: 15px;margin-top: 50px;">' . $response_fourth_call_for_large . '</div><div style="margin-bottom: 15px;margin-top: 50px;">' . $response_fifth_call_for_large . '</div><div style="margin-bottom: 15px;margin-top: 50px;">' . $response_sixth_call_for_large . '</div><div style="margin-bottom: 15px;margin-top: 50px;">' . $response_seventh_call_for_large . '</div><div style="margin-bottom: 15px;margin-top: 50px;">' . $response_eigth_call_for_large . '</div></div>';





	}

	$content_final = convert_emails_to_links($content_final);

	$content_final = convert_urls_to_links($content_final);



	$content_final = htmlentities($content_final, null, 'utf-8');

	$content_final = str_replace("&nbsp;", "", $content_final);

	$content_final = str_replace("<p>&nbsp;</p>", "", $content_final);

	$content_final = str_replace("<p> </p>", "", $content_final);

	$content_final = str_replace("<p></p>", "", $content_final);





	$content_final = html_entity_decode($content_final);



	$content_final = replace_content($content_final, '<h2>Main Content Sections</h2>');

	$content_final = replace_content($content_final, '<p>—</p>');



	$content_final = removePTags($content_final);



	return $content_final;





}

add_action('wp_ajax_multiPostData', 'multiPostData');



function multiPostData()

{





	global $wpdb;

	$uploaded_images_count = 0;

	$sequence_manually_images = 0;

	if ($_POST['aiImage'] == 'Multiple_images') {

		$uploaded_images_count = count($_POST['uploaded_images']);



	}





	$project_name = sanitize_text_field($_POST['project_name']);

	//$number_of_post_schedule = sanitize_text_field($_POST['number_of_post_schedule']);

	$number_of_post_schedule = (!empty($_POST['number_of_post_schedule'])) ? $_POST['number_of_post_schedule'] : "1";

	if ($project_name == '') {

		wp_send_json_success(array('status' => 'false', "message" => "Project name is required."));

	}

	if (!empty($_POST['keyword_list'])) {

		$keyword_lists = explode("\n", $_POST['keyword_list']);



		$notify_email = $_POST['notify_email'];

		$timeTaken = 2 * count($keyword_lists); // one post 3 mint

		$linkredirect = home_url('/') . 'wp-admin/admin.php?page=improveseo_bulkprojects';





		// Check if the project name already exists

		$existing_project = $wpdb->get_var(

			$wpdb->prepare(

				"SELECT COUNT(*) FROM {$wpdb->prefix}improveseo_bulktasks WHERE name = %s",

				$project_name

			)

		);



		// If the project name exists, handle accordingly

		if ($existing_project > 0) {

			// Project name already exists, handle the error (e.g., show an error message)

			wp_send_json_success(array('status' => 'false', "message" => "Project name already exist."));

		} else {





			$schedule_posts = (!empty($_POST['schedule_posts'])) ? $_POST['schedule_posts'] : "";

			if ($schedule_posts == '') {

				wp_send_json_success(array('status' => 'false', "message" => "Publish - Schedule Posts required. Please check step 7."));

			}

			$number_of_post_schedule = (!empty($_POST['number_of_post_schedule'])) ? $_POST['number_of_post_schedule'] : "1";



			$schedule_frequency = (!empty($_POST['schedule_frequency'])) ? $_POST['schedule_frequency'] : "";



			$wpdb->insert($wpdb->prefix . "improveseo_bulktasks", array(

				'name' => $_POST['project_name'],

				'number_of_tasks' => count($keyword_lists),

				'schedule_posts' => $schedule_posts,

				'number_of_post_schedule' => $number_of_post_schedule,

				'number_of_completed_task' => 0,

				'schedule_frequency' => $schedule_frequency,

				'state' => "Unpublished",

				'created_at' => date('Y-m-d h:m:s')

			));





			$lastid = $wpdb->insert_id;



			$pdate = date('Y-m-d');

			$number_of_post_schedule_count = $number_of_post_schedule;

			foreach ($keyword_lists as $key => $value) {

				if (!empty($value)) {







					$keyword_list_name = (!empty($_POST['keyword_list_name'])) ? $_POST['keyword_list_name'] : "";

					$content_type = (!empty($_POST['content_type'])) ? $_POST['content_type'] : "";

					$select_exisiting_options = (!empty($_POST['select_exisiting_options'])) ? $_POST['select_exisiting_options'] : "";

					$details_to_include = (!empty($_POST['details_to_include'])) ? $_POST['details_to_include'] : "";

					$content_lang = (!empty($_POST['content_lang'])) ? $_POST['content_lang'] : "";

					$point_of_view = (!empty($_POST['point_of_view'])) ? $_POST['point_of_view'] : "";

					$call_to_action = (!empty($_POST['call_to_action'])) ? $_POST['call_to_action'] : "";

					$nos_of_words = (!empty($_POST['nos_of_words'])) ? $_POST['nos_of_words'] : "";

					$aiImage = (!empty($_POST['aiImage'])) ? $_POST['aiImage'] : "";

					$schedule_posts = (!empty($_POST['schedule_posts'])) ? $_POST['schedule_posts'] : "";

					$number_of_post_schedule = (!empty($_POST['number_of_post_schedule'])) ? $_POST['number_of_post_schedule'] : "";

					$schedule_frequency = (!empty($_POST['schedule_frequency'])) ? $_POST['schedule_frequency'] : "";

					$assigning_authors = (!empty($_POST['assigning_authors'])) ? $_POST['assigning_authors'] : "";

					$authors_number = (!empty($_POST['authors_number'])) ? $_POST['authors_number'] : "";

					$author_name = (!empty($_POST['author_name'])) ? $_POST['author_name'] : "";



					$category = '';

					if (!empty($_POST['cats'])) {

						foreach ($_POST['cats'] as $cats) {

							$category = $category . '||' . $cats;

						}

					}



					$testimonial = '';

					if (!empty($_POST['testimonial_SC'])) {

						foreach ($_POST['testimonial_SC'] as $testimonial_SC) {

							$testimonial = $testimonial . '||' . $testimonial_SC;

						}

					}



					if (($schedule_posts == 'schedule_all_posts')) {

						$published_on = date('Y-m-d');

					} elseif ($schedule_posts == 'schedule_posts_input_wise') {

						if ($schedule_frequency == 'per_day') {

							if ($number_of_post_schedule >= $number_of_post_schedule_count) {

								$published_on = $pdate;

								$number_of_post_schedule_count++;

							} else {

								$pdate = date('Y-m-d', date(strtotime("+1 day", strtotime($pdate))));

								$number_of_post_schedule_count = 2;

								$published_on = $pdate;

							}

						} elseif ($schedule_frequency == 'per_week') {

							if ($number_of_post_schedule >= $number_of_post_schedule_count) {

								$published_on = $pdate;

								$number_of_post_schedule_count++;

							} else {

								$pdate = date('Y-m-d', date(strtotime("+7 day", strtotime($pdate))));

								$number_of_post_schedule_count = 2;

								$published_on = $pdate;

							}

						}

					} else {

						$published_on = '';

					}



					$Button_SC = (!empty($_POST['Button_SC'])) ? $_POST['Button_SC'] : "";

					$GoogleMap_SC = (!empty($_POST['GoogleMap_SC'])) ? $_POST['GoogleMap_SC'] : "";

					if ($authors_number == '') {

						$authors_number = $author_name;

					}

					// manuually images

					if ($uploaded_images_count > 0) {

						if (($uploaded_images_count) == $sequence_manually_images) {

							$sequence_manually_images = 0;

						}

						$ai_image = base64_encode($_POST['uploaded_images'][$sequence_manually_images]);



					} else {

						$ai_image = '';

					}





					if ($schedule_posts == 'schedule_posts_input_wise') {

						$status = 'Scheduled';

					} else if ($schedule_posts == 'draft_posts') {

						$status = 'Draft';

					} else {

						$status = 'Published';

					}











					$insert_bulk_data = array(

						'bulktask_id' => $lastid,

						'keyword_list_name' => $keyword_list_name,

						'keyword_name' => $value,

						'tone_of_voice' => $content_type,

						'select_exisiting_options' => $select_exisiting_options,

						'details_to_include' => $details_to_include,

						'content_lang' => $content_lang,

						'point_of_view' => $point_of_view,

						'call_to_action' => $call_to_action,

						'nos_of_words' => $nos_of_words,

						'aiImage' => $aiImage,

						'schedule_posts' => $schedule_posts,

						'number_of_post_schedule' => $number_of_post_schedule,

						'assigning_authors' => $assigning_authors,

						'assigning_authors_value' => $authors_number,

						'cats' => $category,

						'ai_image' => $ai_image,

						'testimonial' => $testimonial,

						'schedule_frequency' => $schedule_frequency,

						'Button_SC' => $Button_SC,

						'GoogleMap_SC' => $GoogleMap_SC,

						'Video_SC' => $Video_SC,

						'status' => 'Pending',

						'state' => $status,

						'published_on' => $published_on,

						'created_at' => date('Y-m-d h:m:s'),

						'updated_at' => date('Y-m-d h:m:s'),

					);

					$wpdb->insert($wpdb->prefix . "improveseo_bulktasksdetails", $insert_bulk_data);



					$json_d = json_encode($insert_bulk_data);

					if (empty($json_d)) {

						my_plugin_log('Post created --> ' . $json_d);

						return true;

					}

					$sequence_manually_images++;



				}

			}



			if (!empty($notify_email)) {

				$to = $notify_email; // Replace with the recipient's email address

				$subject = "AI content generation notification";

				$headers = array('Content-Type: text/plain; charset=UTF-8');



				// Send the email

				$email_content = '';

				$email_content .= "Project successfully added:\n";

				$email_content .= "Project Name: " . $project_name . "\n";

				$email_content .= "Number of Keywords: " . count($keyword_lists) . "\n";

				$email_content .= "Time estimation for complete: " . $timeTaken . "\n";

				$email_content .= "State: In Process" . "\n";

				$email_content .= "Created At: " . date('Y-m-d H:i:s') . "\n\n";

				$email_content .= "<a href='" . $linkredirect . "' target='_blank'> Check status </a>" . "\n\n";



				$mail_sent = wp_mail($to, $subject, $email_content, $headers);

			}

			//wp_send_json_success(array('status' => 'false',"message"=>'here 1 : '. $wpdb->last_error  ));

			wp_send_json_success(array('status' => 'success', "linkredirect" => $linkredirect));

		}

	} else {

		wp_send_json_success(array('status' => 'success', "message" => "Keywords should not empty."));

	}

}



add_action('wp_ajax_generateAIMeta', 'generateAIMeta');

function generateAIMeta()

{

	$aigeneratedtitle = $_REQUEST['aigeneratedtitle'];

	$seed_keyword = $_REQUEST['seedkeyword'];

	$out = [];

	$out['title'] = generateMetaTitle($aigeneratedtitle, $seed_keyword);

	$out['descreption'] = generateMetaDescreption($aigeneratedtitle, $seed_keyword);

	wp_send_json_success($out);

	//die($output);

}



function multi_form_data()

{
	$keyword_id = $_REQUEST['project_name'];
	$keyword_list = $_REQUEST['keyword_list'];
	$content_type = $_REQUEST['contenttype'];
	$proj_name = '';
	$get_keyworddata = get_option('swsaved_keywords_with_results_' . $keyword_id);
	$proj_name = isset($get_keyworddata['proj_name']) ? $get_keyworddata['proj_name'] : '';
	$proj_name .= esc_html($proj_name);

	$result = generateTitle_mutli($proj_name, $keyword_list, $content_type);
	$content = preg_replace('~^[\'"]?(.*?)[\'"]?$~', '$1', $result['choices'][0]['message']['content']);

	echo str_replace("'", '`', $content);

	die($output);

}



add_action('wp_ajax_multi_form_data', 'multi_form_data');



function getGPTdata()

{

	$seed_type = $_REQUEST['seedtype'];

	$seed_keyword = $_REQUEST['seedkeyword'];

	$content_type = $_REQUEST['contenttype'];



	$getAudienceData = getAudienceData($seed_keyword);





	//   add_action('init', function() {

	// 		if (!isset($_COOKIE['AudienceData'])) {

	// 			setcookie('AudienceData', 'getAudienceData', strtotime('+1 day'));

	// 		}

	// 	});



	setcookie("AudienceData", $getAudienceData, time() + (86400 * 30), "/"); // 86400 = 1 day

	generateTitle($seed_type, $seed_keyword, $content_type, $getAudienceData);



	die($output);

}

add_action('wp_ajax_nopriv_getGPTdata', 'getGPTdata');

add_action('wp_ajax_getGPTdata', 'getGPTdata');

function rudr_multiple_img_upload_metabox($metaboxes)

{

	$metaboxes[] = array(

		'id' => 'my_metabox',

		'name' => 'Meta Box',

		'post_type' => array('page'),

		'fields' => array(

			array(

				'id' => 'my_field',

				'label' => 'Images',

				'type' => 'gallery'

			),

		)

	);

	return $metaboxes;

}

add_filter('cmb2_meta_boxes', 'rudr_multiple_img_upload_metabox');

add_action('wp_ajax_re_generate_post', 're_generate_post');

//add_action('wp_ajax_workdex_builder_update_ajax', 'improveseo_builder_update');



function re_generate_post()

{

	global $wpdb;

	$id = $_REQUEST['id'];

	$regenerate = 1;

	generateBulkAiContent($id, $regenerate);

	$wpdb->query(

		$wpdb->prepare(

			"UPDATE `" . $wpdb->prefix . "improveseo_bulktasksdetails`

			SET state = %s WHERE id = %d",

			'draft',

			$id

		)

	);

	wp_send_json_success(array('status' => 'true', "message" => "Post regenerated successfully."));

}





function generateTitle_mutli($proj_name, $keyword_list, $content_type)

{

	global $wpdb, $user_ID;



	// Your OpenAI API key

	$apiKey = get_option('improveseo_chatgpt_api_key');



	// The endpoint URL for OpenAI chat completions API (replace with the correct endpoint)

	$apiUrl = 'https://api.openai.com/v1/chat/completions';



	if ($content_type != '') {

		$content_type = 'voice of content must be ' . $content_type;

	}

	$question = 'In a few sentences (max 500 characters not including spaces) explain what the list of keywords provided is about. What is the common thread. Goal is to create a context that is relevant to all keywords in that list. Do not use the word ‘keyword list’ or ‘list’ in the output. Start the output with "The context is..."

		 Keyword List: ' . $keyword_list . ' ' . $content_type;



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



	//   print_r($result);

	//   die();

	if (!empty($result['choices'][0]['message']['content'])) {

		return $result;

	} else {

		return "ChatGpt Request Error";

	}

}

function generateTitle($seed_type, $seed_keyword, $content_type, $getAudienceData)

{

	global $wpdb, $user_ID;



	// Your OpenAI API key

	$apiKey = get_option('improveseo_chatgpt_api_key');



	// The endpoint URL for OpenAI chat completions API (replace with the correct endpoint)

	$apiUrl = 'https://api.openai.com/v1/chat/completions';



	if ($content_type != '') {

		$content_type = 'voice of content must be ' . $content_type;

	}



	if ($seed_type == 'seed_option2') {

		$question = 'You are a content creator who creates SEO optimized titles for blog posts. You are provided a word or phrase that is searched by the reader, and the audience data of the reader, including demographic information, tone preferences, reading level preference and emotional needs/pain points. Using this information you should come up with the title that will be engaging and interesting for people who are described in the audience data and search provided word or phrase. In the title do not include emojis or hashtags. Limit characters not including spaces to 80-100. As an output, write just a title without explanation or introduction.

		   Now generate a SEO optimized title based on the following information:

		   Keyword: ' . $seed_keyword . '

		   Audience data: {' . $getAudienceData . '}';



		// $question = 'Create a compelling seo optimized blog post title based on the keyword `'.$seed_keyword.'` in the form of No Answer. No emojis. No hashtags. Limit characters not including spaces to 80-100. '.$content_type;

	} else if ($seed_type == 'seed_option3') {

		$question = 'You are a content creator who creates SEO optimized titles for blog posts. You are provided a word or phrase that is searched by the reader, and the audience data of the reader, including demographic information, tone preferences, reading level preference and emotional needs/pain points. Using this information you should come up with a title that will be engaging and interesting for people who are described in the audience data and search provided word or phrase. Title should be formed as a question. In the title do not include emojis or hashtags. Limit characters not including spaces to 80-100. As an output, write just a title without explanation or introduction. 

			Now generate a SEO optimized title based on the following information:

				Keyword: ' . $seed_keyword . '

				Audience data: {' . $getAudienceData . '}';

	} else {

		$question = $seed_keyword;

	}



	// echo "????".$question;



	// Your chat messages

	$messages = [

		//['role' => 'system', 'content' => $getAudienceData],

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

	if (empty($result['choices'][0]['message']['content'])) {

		return $result;

	} else {

		if ($seed_type == 'seed_option2') {

			$content = preg_replace('~^[\'"]?(.*?)[\'"]?$~', '$1', $result['choices'][0]['message']['content']);



			echo str_replace("'", '`', $content);

		} else if ($seed_type == 'seed_option3') {

			$content = preg_replace('~^[\'"]?(.*?)[\'"]?$~', '$1', $result['choices'][0]['message']['content']);



			echo str_replace("'", '`', $content);

		} else {

			echo '';

		}

	}

}

?>