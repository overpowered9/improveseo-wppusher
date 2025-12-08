<?php

if (file_exists(dirname(__FILE__) . '/single_and_bulk_AI_post_function.php'))

include_once dirname(__FILE__) . '/single_and_bulk_AI_post_function.php';
include_once dirname(__FILE__) . '/GenerateAIpopup.php';

add_action('cronjob_request_event', 'CronjobRequest');

// TOC Helper Functions
if (!function_exists('verifyAndFixTOCLinks')) {
    /**
     * Function to verify and fix Table of Contents links
     * This function ensures that TOC links properly connect to their corresponding headings
     */
    function verifyAndFixTOCLinks($content) {
        // Parse the content to extract TOC links and headings
        $toc_links = [];
        $headings = [];
        
        // Extract TOC links using regex
        preg_match_all('/<a href="#([^"]+)">([^<]+)<\/a>/', $content, $toc_matches);
        if (!empty($toc_matches[1])) {
            foreach ($toc_matches[1] as $index => $anchor) {
                $toc_links[$anchor] = $toc_matches[2][$index];
            }
        }
        
        // Extract headings with IDs using regex
        preg_match_all('/<h([2-6])(?:\s+id="([^"]+)")?>([^<]+)<\/h[2-6]>/', $content, $heading_matches);
        if (!empty($heading_matches[2])) {
            foreach ($heading_matches[2] as $index => $id) {
                if (!empty($id)) {
                    $headings[$id] = $heading_matches[3][$index];
                }
            }
        }
        
        // Check for missing IDs and fix them
        $updated_content = $content;
        
        foreach ($toc_links as $anchor => $title) {
            if (!isset($headings[$anchor])) {
                // Find the heading with matching title and add ID
                $heading_pattern = '/<h([2-6])(?:\s+[^>]*)?>(' . preg_quote(trim($title), '/') . ')<\/h[2-6]>/i';
                $replacement = '<h$1 id="' . $anchor . '">$2</h$1>';
                $updated_content = preg_replace($heading_pattern, $replacement, $updated_content, 1);
            }
        }
        
        // Generate missing TOC links for headings without them
        foreach ($headings as $id => $title) {
            if (!isset($toc_links[$id])) {
                // Log missing TOC link (for debugging)
                error_log("Missing TOC link for heading: " . $title . " (ID: " . $id . ")");
            }
        }
        
        return $updated_content;
    }
}

if (!function_exists('generateAnchorId')) {
    /**
     * Function to generate URL-friendly anchor IDs from heading text
     */
    function generateAnchorId($text) {
        // Remove HTML tags, convert to lowercase, replace spaces and special chars with hyphens
        $id = strtolower(strip_tags($text));
        $id = preg_replace('/[^a-z0-9\s-]/', '', $id);
        $id = preg_replace('/[\s-]+/', '-', $id);
        $id = trim($id, '-');
        return $id;
    }
}

if (!function_exists('generateTOCFromContent')) {
    /**
     * Function to automatically generate TOC from content headings
     */
    function generateTOCFromContent($content) {
        preg_match_all('/<h([2-6])(?:\s+id="([^"]+)")?>([^<]+)<\/h[2-6]>/', $content, $matches);
        
        if (empty($matches[0])) {
            return '<h2 id="table-of-contents">Table of Contents</h2><p>No headings found.</p>';
        }
        
        $toc = '<h2 id="table-of-contents">Table of Contents</h2><ul>';
        
        foreach ($matches[0] as $index => $match) {
            $level = $matches[1][$index];
            $id = !empty($matches[2][$index]) ? $matches[2][$index] : generateAnchorId($matches[3][$index]);
            $title = trim($matches[3][$index]);
            
            // Only include H2 and H3 in TOC for better readability
            if ($level <= 3) {
                $indent = $level == 3 ? 'style="margin-left: 20px;"' : '';
                $toc .= '<li ' . $indent . '><a href="#' . $id . '">' . $title . '</a></li>';
            }
        }
        
        $toc .= '</ul>';
        return $toc;
    }
}

if (!function_exists('enhanceContentWithTOC')) {
    /**
     * Function to enhance content with proper TOC formatting
     * This is a comprehensive function that applies all TOC enhancements
     */
    function enhanceContentWithTOC($content) {
        // First verify and fix existing TOC links
        $enhanced_content = verifyAndFixTOCLinks($content);
        
        // Additional enhancements can be added here in the future
        // - Smooth scrolling JavaScript
        // - Back to top links
        // - Section numbering
        
        return $enhanced_content;
    }
}

if (!function_exists('removeConsecutiveSpecialCharacters')) {
    /**
     * Remove runs of 2+ special characters from visible text, preserving single occurrences.
     * Does not alter HTML tags or attributes.
     */
    function removeConsecutiveSpecialCharacters($content) {
        $parts = preg_split('/(<[^>]+>)/', $content, -1, PREG_SPLIT_DELIM_CAPTURE);
        if ($parts === false) {
            return $content;
        }
        foreach ($parts as $i => $part) {
            // Skip HTML tags
            if ($part !== '' && $part[0] === '<') {
                continue;
            }
            // Remove any sequence of 2+ non-letter/number/space chars (e.g., ###, ***, ----, !!!, ???)
            $parts[$i] = preg_replace('/([^\p{L}\p{N}\s]){2,}/u', '', $part);
        }
        return implode('', $parts);
    }
}

// Add helpers to handle parentheses around links/CTAs in bulk content
if (!function_exists('stripParenthesesWrappingContactTokens')) {
    // Strip parentheses that wrap raw URLs or emails in plain text.
    // Examples:
    //  - (https://example.com) => https://example.com
    //  - (www.example.com/path) => www.example.com/path
    //  - (user@example.com) => user@example.com
    function stripParenthesesWrappingContactTokens($content) {
        // URLs wrapped in parentheses
        $content = preg_replace('/\(\s*((?:https?:\/\/|www\.)[^\s)]+)\s*\)/i', '$1', $content);
        // Emails wrapped in parentheses
        $content = preg_replace('/\(\s*([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[A-Za-z]{2,})\s*\)/', '$1', $content);
        return $content;
    }
}

if (!function_exists('stripParenthesesAroundAnchorTags')) {
    // Strip parentheses that directly surround an anchor tag.
    // Example: (<a href="...">link</a>) => <a href="...">link</a>
    function stripParenthesesAroundAnchorTags($content) {
        return preg_replace('/\(\s*(<a\b[^>]*>.*?<\/a>)\s*\)/i', '$1', $content);
    }
}

// Keep parent bulk project's number_of_completed_task in sync with details
if (!function_exists('improveseo_sync_bulk_parent_progress')) {
    function improveseo_sync_bulk_parent_progress($bulktask_id) {
        global $wpdb;
        $bulktask_id = (int) $bulktask_id;
        if ($bulktask_id <= 0) {
            return;
        }
        $completed = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(1) FROM {$wpdb->prefix}improveseo_bulktasksdetails WHERE bulktask_id = %d AND status = %s",
                $bulktask_id,
                'Done'
            )
        );
        
        // Get total number of tasks for this bulk project
        $total = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(1) FROM {$wpdb->prefix}improveseo_bulktasksdetails WHERE bulktask_id = %d",
                $bulktask_id
            )
        );
        
        // Determine if project should be marked as finished
        $update_data = array(
            'number_of_completed_task' => $completed,
            'updated_at' => current_time('mysql'),
        );
        
        // If all tasks are completed, mark project as finished
        if ($total > 0 && $completed == $total) {
            $update_data['state'] = 'Finished';
        }
        
        // Update the parent row with the recalculated count and state
        $wpdb->update(
            $wpdb->prefix . 'improveseo_bulktasks',
            $update_data,
            array('id' => $bulktask_id),
            array('%d', '%s', '%s'),
            array('%d')
        );
    }
}

function CronjobRequest()

{

	my_plugin_log("=== CRON JOB STARTED ===");
	my_plugin_log("cron calling working...");

	global $wpdb;

	my_plugin_log("Calling generateBulkAiContent()...");

	$returndata = generateBulkAiContent();
	
	my_plugin_log("generateBulkAiContent() returned: " . json_encode($returndata));

	// $wpdb->insert($wpdb->prefix . "improveseo_cron_job_status", array(

	// 	'time' => date("Y-m-d H:i:s"),

	// 	'input' => '',

	// 	'content_data' => json_encode($returndata),

	// 	'status' => 1

	// ));
	
	my_plugin_log("Calling saveContentInTaskList()...");

	$publishContent = saveContentInTaskList();
	
	my_plugin_log("saveContentInTaskList() returned: " . json_encode($publishContent));

	// Generate Content



	//$lastid = $wpdb->insert_id;

	//error_log('This is a log message : '.date('Y-m-d H:i:s'));
	
	my_plugin_log("=== CRON JOB COMPLETED ===");

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

		// Check if parent bulk task is stopped
		if (isset($value->bulktask_id)) {
			$parent_task = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT state FROM {$wpdb->prefix}improveseo_bulktasks WHERE id = %d",
					$value->bulktask_id
				)
			);
			
			if ($parent_task && $parent_task->state == 'Stopped') {
				my_plugin_log('Bulk task stopped by user - bulktask_id: ' . $value->bulktask_id);
				echo '<h3>Bulk project stopped by user.</h3>';
				return false;
			}
		}

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
		
		// ✅ Check if image generation failed (returns false)
		if ($imageURL === false) {
			error_log("CronjobRequest: Image generation FAILED for task " . $id . " - keyword: " . $value->keyword_name);
			$imageURL = ''; // Set empty string for database
		} else {
			error_log("CronjobRequest: Image generated successfully for task " . $id);
			error_log("CronjobRequest: Image URL: " . $imageURL);
			// Encode the valid URL for storage
			$imageURL = base64_encode($imageURL);
		}

	} else {

		$imageURL = $value->ai_image;

	}

	// AI Content

	$keyword_selection = '';

	//my_plugin_log('arrays : '.$basic_prompt);

	$AI_Content = createAIpost2bulk($value->keyword_name, $keyword_selection, $value->select_exisiting_options, $value->nos_of_words, $value->content_lang, $shortcode = '', $is_single_keyword = '', $value->tone_of_voice, $value->point_of_view, $value->details_to_include, $value->call_to_action, $value->details_to_include);





		$data_array = array('ai_title' => $ai_title, 'imageURL' => $imageURL, 'AI_Content' => $AI_Content);

		$AI_Content = base64_encode($AI_Content);

		my_plugin_log('This is a log message content : ' . $AI_Content);

        // Persist one detail row as Done + AI payload
		// DO NOT modify state - it represents user's original publishing intent
		// Only update status to 'Done' when content is ready
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE `{$wpdb->prefix}improveseo_bulktasksdetails`
                 SET status = %s, ai_title = %s, ai_content = %s, ai_image = %s
                 WHERE id = %d",
                'Done', $ai_title, $AI_Content, $imageURL, $id
            )
        );

		my_plugin_log('Content generation complete for task ID: ' . $id . ', status set to Done');

		// Sync parent bulk project after changing child status
		if (isset($value->bulktask_id)) {
			improveseo_sync_bulk_parent_progress((int) $value->bulktask_id);
		}

		// If regenerating and post already exists and is published, update the WordPress post content
		if (!empty($regenerate) && !empty($value->post_id)) {
			$post = get_post($value->post_id);
			if ($post && $post->post_status == 'publish') {
				$post_update = array(
					'ID' => $value->post_id,
					'post_title' => $ai_title,
					'post_content' => base64_decode($AI_Content),
				);
				wp_update_post($post_update);
				my_plugin_log('Updated existing WordPress post ID: ' . $value->post_id);
			}
		}
	}

	//$wpdb->query ( "UPDATE `".$wpdb->prefix."improveseo_bulktasksdetails` SET status='Done',`ai_title`=".$ai_title.",`ai_content`='".$AI_Content."',`ai_image`='".$imageURL."', WHERE id=".$id );





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

			// Check if parent bulk task is stopped
			if (isset($value->bulktask_id)) {
				$parent_task = $wpdb->get_row(
					$wpdb->prepare(
						"SELECT state FROM {$wpdb->prefix}improveseo_bulktasks WHERE id = %d",
						$value->bulktask_id
					)
				);
				
				if ($parent_task && $parent_task->state == 'Stopped') {
					my_plugin_log('Bulk task stopped by user during publishing - bulktask_id: ' . $value->bulktask_id);
					echo '<h3>Bulk project stopped by user.</h3>';
					return false;
				}
			}

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

		// ✅ Validate and decode image URL before using it
		$image_html = '';
		if (!empty($value->ai_image)) {
			$decoded_image = base64_decode($value->ai_image);
			error_log("saveContentInTaskList: Decoded ai_image for task " . $value->id . ": " . $decoded_image);
			
			// Validate that decoded image is a proper URL
			if (filter_var($decoded_image, FILTER_VALIDATE_URL)) {
				$image_html = "<img src='" . esc_url($decoded_image) . "' style='width:100%; margin-bottom: 100px;' alt='" . esc_attr($value->ai_title) . "'>";
				error_log("saveContentInTaskList: Valid image URL, including in post");
			} else {
				error_log("saveContentInTaskList: Invalid or missing image URL (value: '" . $decoded_image . "'), skipping image");
			}
		} else {
			error_log("saveContentInTaskList: No image data for task " . $value->id);
		}
		
		// Assemble content with optional image
		$fullcontent = $image_html . base64_decode($value->ai_content) . $content;

		$post_date = date('Y-m-d H:i:s');
		
		// Determine WordPress post_status (lowercase for WordPress)
		$post_status = 'publish';  // Default: publish immediately
		// Determine internal state value (Capital for tracking)
		$internal_state = 'Published';  // Default

		if ($value->schedule_posts == 'draft_posts') {
			$post_status = 'draft';  // WordPress uses lowercase
			$internal_state = 'Draft';  // Internal tracking uses Capital
		} elseif ($value->schedule_posts == 'schedule_posts_input_wise') {
			$post_status = 'draft';  // Create as draft, will be published on scheduled date
			$internal_state = 'Scheduled';  // Keep original scheduling intent
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

					$internal_state,  // Use internal_state instead of post_status

					$post_id,

					$value->id

				)

			 );

			my_plugin_log('Created WordPress post ID: ' . $post_id . ' with post_status: ' . $post_status . ', set state to: ' . $internal_state);

            // Also refresh parent progress (keeps updated_at fresh in UI)
            if (isset($value->bulktask_id)) {
                improveseo_sync_bulk_parent_progress((int) $value->bulktask_id);
            }

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

		// Check if parent bulk task is stopped
		if (isset($value->bulktask_id)) {
			$parent_task = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT state FROM {$wpdb->prefix}improveseo_bulktasks WHERE id = %d",
					$value->bulktask_id
				)
			);
			
			if ($parent_task && $parent_task->state == 'Stopped') {
				my_plugin_log('Bulk task stopped by user during scheduled publishing - bulktask_id: ' . $value->bulktask_id);
				echo '<h3>Bulk project stopped by user.</h3>';
				return false;
			}
		}

		if (!empty($value->post_id)) {

			$post_data = array(

				'ID' => $value->post_id, // The ID of the post being updated

				'post_status' => 'publish'  // lowercase for WordPress

			);



			wp_update_post($post_data);
			my_plugin_log('Scheduled publishing: Changed post ID ' . $value->post_id . ' to publish status');


			// tag 

			$tags = array($value->keyword_name);

			if (!empty($tags)) {

				wp_set_post_tags($value->post_id, $tags);

			}



			// Update state to 'Published' after scheduled publishing

			$wpdb->query(

				$wpdb->prepare(

					"UPDATE `" . $wpdb->prefix . "improveseo_bulktasksdetails`
					SET `is_published_by_plugin` = %d, `state` = %s WHERE id = %d",
					1,
					'Published',  // Change state from 'Scheduled' to 'Published'
					$value->id

				)

			 );

            // Keep parent updated_at moving forward in UI
            if (isset($value->bulktask_id)) {
                improveseo_sync_bulk_parent_progress((int) $value->bulktask_id);
            }
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
    // Get API credentials from WordPress options
    $api_key = get_option('improveseo_api_key');
    $site_code = get_option('improveseo_site_code');
    
    // Validate credentials
    if (empty($api_key) || empty($site_code)) {
        error_log("generateBulkAiImage Error: Missing API credentials");
        return false; // ✅ Return false instead of error string
    }
    
    // Admin server configuration
    $admin_server_url = 'https://imporve-seo-admin-server.onrender.com';
    $api_endpoint = $admin_server_url . '/api/v1/generate/generateimage';
    
    $seed_title = 'ai_image_' . date('YmdHis');
    
    // Prepare request payload
    $payload = array(
        'title' => $title,
        'noedit' => false,
        'seed_title' => $seed_title,
        'width' => 1024,
        'height' => 768
    );
    
    error_log("generateBulkAiImage: Calling API for title: " . $title);
    
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
    curl_setopt($ch, CURLOPT_TIMEOUT, 120);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    
    // Execute the request
    $response = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Check for cURL errors
    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        error_log("generateBulkAiImage cURL Error: " . $error);
        return false; // ✅ Return false on cURL error
    }
    
    curl_close($ch);
    
    error_log("generateBulkAiImage: HTTP Status: " . $http_status);
    error_log("generateBulkAiImage: Response: " . substr($response, 0, 500));
    
    // ✅ CRITICAL FIX: Check HTTP status FIRST - do NOT use response if status is not 200
    if ($http_status !== 200) {
        error_log("generateBulkAiImage HTTP Error: Status $http_status, Response: " . $response);
        
        // Check for specific error types
        if ($http_status === 402) {
            error_log("generateBulkAiImage: Insufficient credits (HTTP 402)");
        } else if ($http_status === 401) {
            error_log("generateBulkAiImage: Authentication failed (HTTP 401)");
        } else if ($http_status === 400) {
            error_log("generateBulkAiImage: Bad request (HTTP 400)");
        } else if ($http_status >= 500) {
            error_log("generateBulkAiImage: Server error (HTTP " . $http_status . ")");
        }
        
        return false; // ✅ Return false - do NOT store HTTP status as image URL
    }
    
    // Parse JSON response - only if HTTP status was 200
    $result = json_decode($response, true);
    
    if (!$result || !is_array($result)) {
        error_log("generateBulkAiImage Invalid JSON Response: " . $response);
        return false; // ✅ Return false on invalid JSON
    }
    
    // ✅ Check success field in response
    if (!isset($result['success']) || $result['success'] !== true) {
        $error_msg = isset($result['error']) ? $result['error'] : 'Unknown error';
        error_log("generateBulkAiImage API Error: success=" . ($result['success'] ?? 'not set') . ", error=" . $error_msg);
        return false; // ✅ Return false if success is not true
    }
    
    // ✅ ONLY extract image URL if status is 200 AND success is true
    if (!isset($result['data']['image_url']) || empty($result['data']['image_url'])) {
        error_log("generateBulkAiImage Missing Image URL in response data");
        return false; // ✅ Return false if image URL is missing
    }
    
    $image_url = $result['data']['image_url'];
    error_log("generateBulkAiImage: Image URL received: " . $image_url);
    
    // Validate that image_url is a proper URL
    if (!filter_var($image_url, FILTER_VALIDATE_URL)) {
        error_log("generateBulkAiImage: Invalid URL format: " . $image_url);
        return false; // ✅ Return false if URL is invalid
    }
    
    // Download the image and save to WordPress uploads
    $upload_dir = wp_upload_dir();
    
    $ch = curl_init($image_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $image_data = curl_exec($ch);
    $download_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if (!$image_data || $download_status !== 200) {
        error_log("generateBulkAiImage Error: Failed to download image from URL: " . $image_url . " (HTTP " . $download_status . ")");
        return false; // ✅ Return false on download failure
    }
    
    error_log("generateBulkAiImage: Image downloaded successfully (" . strlen($image_data) . " bytes)");
    
    // Generate unique filename
    $file_name = wp_unique_filename($upload_dir['path'], str_replace(" ", "_", str_replace(".", "", $seed_title)));
    $file_name = $file_name . '_' . rand();
    $file_path = $upload_dir['path'] . '/' . $file_name;
    
    if (file_put_contents($file_path, $image_data) !== false) {
        error_log("generateBulkAiImage: Image saved to: " . $file_path);
        
        // Convert to WebP if GD is available
        if (extension_loaded('gd')) {
            $original_image = imagecreatefromstring($image_data);
            
            if ($original_image !== false) {
                $webp_file_name = pathinfo($file_name, PATHINFO_FILENAME) . '.webp';
                $webp_file_path = $upload_dir['path'] . '/' . $webp_file_name;
                
                if (imagewebp($original_image, $webp_file_path, 90)) {
                    imagedestroy($original_image);
                    unlink($file_path);
                    
                    $final_url = $upload_dir['url'] . '/' . $webp_file_name;
                    error_log("generateBulkAiImage: Success! WebP image URL: " . $final_url);
                    return $final_url; // ✅ Return valid URL
                } else {
                    imagedestroy($original_image);
                    error_log("generateBulkAiImage: WebP conversion failed, using original");
                }
            }
        }
        
        $final_url = $upload_dir['url'] . '/' . $file_name;
        error_log("generateBulkAiImage: Success! Image URL: " . $final_url);
        return $final_url; // ✅ Return valid URL
    } else {
        error_log("generateBulkAiImage Error: Failed to save image file to: " . $file_path);
        return false; // ✅ Return false on save failure
    }
}
function createAIpost2bulk($seed_keyword, $keyword_selection, $seed_options, $nos_of_words, $content_lang, $shortcode = '', $is_single_keyword = '', $voice_tone = '', $point_of_view = '', $title = '', $call_to_action = '', $details_to_include = '', $for_testing_only = '')
{
	global $wpdb, $user_ID;
	
	my_plugin_log("createAIpost2bulk called for keyword: " . $seed_keyword);
	
	// Get audience data from cookie (same as original function)
	$AudienceData = isset($_COOKIE['AudienceData']) ? $_COOKIE['AudienceData'] : '';
	
	// Get API credentials from WordPress options
	$api_key = get_option('improveseo_api_key');
	$site_code = get_option('improveseo_site_code');
	
	my_plugin_log("API Key: " . ($api_key ? "Configured" : "MISSING") . ", Site Code: " . ($site_code ? "Configured" : "MISSING"));
	
	// Validate credentials
	if (empty($api_key) || empty($site_code)) {
		my_plugin_log("createAIpost2bulk Error: Missing API credentials!");
		error_log("createAIpost2bulk Error: Missing API credentials. Please configure API Key and Site Code in settings.");
		return "Error: Missing API credentials. Please configure your API Key and Site Code in ImproveSEO settings.";
	}
	
	my_plugin_log("Connecting to admin server for bulk generation...");
	
	// Admin server configuration
	$admin_server_url = 'https://imporve-seo-admin-server.onrender.com';
    $api_endpoint = $admin_server_url . '/api/v1/generate/active';
	
	// Prepare request payload matching the /active route interface
	$payload = array(
		'seed_keyword' => $seed_keyword,
		'keyword_selection' => $keyword_selection,
		'seed_options' => $seed_options,
		'nos_of_words' => $nos_of_words,
		'content_lang' => $content_lang,
		'voice_tone' => $voice_tone,
		'point_of_view' => $point_of_view,
		'title' => $title,
		'call_to_action' => $call_to_action,
		'details_to_include' => $details_to_include,
		'for_testing_only' => intval($for_testing_only),
		'audienceData' => $AudienceData,
		'useActivePrompts' => true,
		'customPrompts' => new stdClass(), // Empty object
		'templateVariables' => array(
			'seed_keyword' => $seed_keyword,
			'context' => $details_to_include,
			'audience_data' => $AudienceData,
			'meta_title' => $title ?: $seed_keyword,
			'whats_next_content' => $call_to_action
		)
	);
	
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
		return "Error: Failed to connect to content generation server. Please try again.";
	}
	
	curl_close($ch);
	
	// Check HTTP status
	if ($http_status !== 200) {
		error_log("createAIpost2 HTTP Error: Status $http_status, Response: " . $response);
		return "Error: Content generation server returned error status: $http_status";
	}
	
	// Parse JSON response
	$result = json_decode($response, true);
	
	if (!$result || !isset($result['success'])) {
		error_log("createAIpost2 Invalid Response: " . $response);
		return "Error: Invalid response from content generation server.";
	}
	
	if (!$result['success']) {
		$error_msg = isset($result['error']) ? $result['error'] : 'Unknown error';
		error_log("createAIpost2 API Error: " . $error_msg);
		return "Error: " . $error_msg;
	}
	
	// Extract content from successful response
	if (!isset($result['data']['content'])) {
		error_log("createAIpost2 Missing Content: " . $response);
		return "Error: No content returned from generation server.";
	}
	
	$content_final = $result['data']['content'];
	
	// // Apply the same post-processing as original function
	// // Remove parentheses that wrap raw URLs/emails before converting them to links
	// $content_final = stripParenthesesWrappingContactTokens($content_final);
	// $content_final = convert_emails_to_links($content_final);
	// $content_final = convert_urls_to_links($content_final);
	
	// // Remove parentheses that wrap already-linked anchors like (<a href>..</a>)
	// $content_final = stripParenthesesAroundAnchorTags($content_final);
	
	// // HTML entity processing
	// $content_final = htmlentities($content_final, ENT_QUOTES, 'utf-8');
	// $content_final = str_replace("&nbsp;", "", $content_final);
	// $content_final = str_replace("<p>&nbsp;</p>", "", $content_final);
	// $content_final = str_replace("<p> </p>", "", $content_final);
	// $content_final = str_replace("<p></p>", "", $content_final);
	// $content_final = html_entity_decode($content_final);
	
	// // Remove unwanted content
	// $content_final = replace_content($content_final, '<h2>Main Content Sections</h2>');
	// $content_final = replace_content($content_final, '<p>—</p>');
	
	// // Apply final processing
	// $content_final = removePTags($content_final);
	// $content_final = removeConsecutiveSpecialCharacters($content_final);
	// $content_final = verifyAndFixTOCLinks($content_final);
	
	// Add styling like original function
	$content_final = '<div class="main-content-section-improveseo">' . $content_final . '</div>';
	
	
	// Log the generation metadata if available (for debugging)
	if (isset($result['data']['generationMetadata'])) {
		$metadata = $result['data']['generationMetadata'];
		error_log("createAIpost2 Generation Metadata: " . json_encode($metadata));
	}
	
	return $content_final;
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

			

			Table of Contents - Create a clickable table of contents with anchor links. Each item in the TOC should be formatted as a clickable link that jumps to the corresponding section. Use the format: <a href="#section-id">Section Title</a>. The section IDs should be URL-friendly (lowercase, hyphens instead of spaces, no special characters). Craft attention-grabbing subtitles that entice readers to click and read more. Use numbers, questions, and powerful words to draw interest. Use NLP techniques to craft subtitles that grab attention. Incorporate power words and questions to stimulate curiosity and engagement. Based on the main keyword and the audience data provided to you, you need to understand what are the emotions and intentions reader has while searching it. You should understand what deep questions and concerns user wants to answer and build your subtitles(subsections) based on these. Do not include numbering in the list of subtitles. Make engaging titles in the Table of Contents. 

			

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

			

			<h2 id="table-of-contents">Table of Contents</h2> (Heading 2) - should not be more than 50 words. Format as clickable links: <ul><li><a href="#section-id">Section Title</a></li></ul>

			

			Main Content Sections - Create 4 sections. Each section should not be more than 200-250 words of detailed content. Use format: <h2 id="section-id">Section Title</h2>

			

			<h2 id="conclusion">Conclusion</h2> (Heading 2) - Conclusion should not be more than 100-150 words.

			

			<h2 id="faqs">FAQs</h2> (Heading 2) - FAQs should not be more than 100-150 words.

			Q: 

			A:

			

			Q: 

			A: 

			

			Q:

			A: 

			

			<h2 id="whats-next">What\'s Next?</h2> (Heading 2) - What\'s next? should not be more than 100-150 words.

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

    // Preprocess: remove parentheses wrapping raw URLs/emails before converting to links
    $content_final = stripParenthesesWrappingContactTokens($content_final);

	$content_final = convert_emails_to_links($content_final);
	$content_final = convert_urls_to_links($content_final);

    // Postprocess: remove parentheses that wrap already-linked anchors like (<a href>..</a>)
    $content_final = stripParenthesesAroundAnchorTags($content_final);

	$content_final = htmlentities($content_final, null, 'utf-8');
	$content_final = str_replace("&nbsp;", "", $content_final);
	$content_final = str_replace("<p>&nbsp;</p>", "", $content_final);
	$content_final = str_replace("<p> </p>", "", $content_final);
	$content_final = str_replace("<p></p>", "", $content_final);

	$content_final = html_entity_decode($content_final);

	$content_final = replace_content($content_final, '<h2>Main Content Sections</h2>');
	$content_final = replace_content($content_final, '<p>—</p>');

	$content_final = removePTags($content_final);
	// Apply TOC enhancements and verification
	// Clean up: remove consecutive special characters in visible text (e.g., ###, ***)
	$content_final = removeConsecutiveSpecialCharacters($content_final);
	$content_final = enhanceContentWithTOC($content_final);

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

	// Validate keyword list exists and is not empty
	if (empty($_POST['keyword_list']) || trim($_POST['keyword_list']) == '') {
		wp_send_json_success(array('status' => 'false', "message" => "Keyword list is required. Please add at least one keyword."));
		return;
	}

	if (!empty($_POST['keyword_list'])) {

		$keyword_lists = explode("\n", $_POST['keyword_list']);
		
		// Filter out empty lines
		$keyword_lists = array_filter($keyword_lists, function($keyword) {
			return trim($keyword) !== '';
		});
		
		// Check if we have any valid keywords after filtering
		if (empty($keyword_lists) || count($keyword_lists) == 0) {
			wp_send_json_success(array('status' => 'false', "message" => "Keyword list is empty. Please add at least one keyword."));
			return;
		}



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
					
					$Video_SC = (!empty($_POST['Video_SC'])) ? $_POST['Video_SC'] : "";

					if ($authors_number == '') {

						$authors_number = $author_name;

					}
					
					// NEW IMAGE LOGIC - Get image method for THIS specific keyword
					$aiImage = isset($_POST['image_method_' . $key]) 
						? $_POST['image_method_' . $key] 
						: 'AI_image_one';
					
					// Get uploaded image URL for THIS specific keyword
					$ai_image = '';
					if ($aiImage == 'Multiple_images' && isset($_POST['keyword_image_url_' . $key])) {
						$image_url = $_POST['keyword_image_url_' . $key];
						if (!empty($image_url)) {
							$ai_image = base64_encode($image_url);
						}
					}
					// If aiImage is 'AI_image_one', leave ai_image empty for cron to generate

					$sequence_manually_images++;




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

	// State is already set correctly in generateBulkAiContent, no need to override it here

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