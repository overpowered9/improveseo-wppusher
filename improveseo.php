<?php
   /*
   Plugin Name: Improve SEO
   Plugin URI: 
   Description: Creates a large number of pages/posts and customize them to rank in Google.
   Author: Improve SEO Team
   Version: 2.0.11
   */
   define("IMPROVESEO_VERSION", "2.0.11");
   define('IMPROVESEO_ROOT', dirname(__FILE__));
   define('IMPROVESEO_DIR', untrailingslashit(plugin_dir_url( __FILE__ )));
   
   
   define( 'WT_PATH', untrailingslashit(plugin_dir_path( __FILE__ )) );
   define( 'WT_URL' ,  untrailingslashit(plugin_dir_url( __FILE__ )) );
   
   /* 
   **========== Files Load =========== 
   */
   if( file_exists( dirname(__FILE__).'/includes/helpers.php' )) include_once dirname(__FILE__).'/includes/helpers.php';
   // if( file_exists( dirname(__FILE__).'/includes/admin.php' )) include_once dirname(__FILE__).'/includes/admin.php';
   
   include_once 'bootstrap.php';
   
   register_activation_hook(__FILE__, 'improveseo_install');
   register_activation_hook(__FILE__, 'improveseo_install_data');
   
   // Features
   
   register_deactivation_hook(__FILE__, 'improveseo_uninstall');
   
   function improveseo_load_media_files() {
       wp_enqueue_media();
   }
   add_action( 'admin_enqueue_scripts', 'improveseo_load_media_files' );
   
   //add_action( 'init', "workdex_init" );
   
   add_filter('jpeg_quality', function($arg){return 75;});
   
   //adding buttons to content editor
   add_action('media_buttons', 'add_my_media_button');
   function add_my_media_button() {
   
   	if(function_exists('get_current_screen')){ 
   
		$my_current_screen = get_current_screen();
		$allowed_bases = array('improve-seo_page_improveseo_posting');
		if(!in_array($my_current_screen->base, $allowed_bases)){
			return;
		}
	}
   
   
       $html = '';
       $html .= '<select class="sw-editor-selector" style="text-align:left !important;">
                       <option value="addshortcode">Add Shortcode</option>
                       <option value="testimonial">Testimonials</option>
                       <option value="googlemap">Google Maps</option>
                       <option value="button">Buttons</option>
                       <option value="video">Videos</option>
                       <option value="list">Lists</option>
                </select> &nbsp;';
       $html .= '<a type="button" class="btn btn-primary btn-outline-primary" data-toggle="modal" data-target="#exampleModal" >Generate AI Content</a> ';
   
     	$seo_list = improve_seo_lits();
   
       if(!empty($seo_list)){
           foreach($seo_list as $li){
               $list.= '<button data-action="list" class="add-seolistshortcode button" id='.$li.'>@list:'.$li.'</button>';
           }   
       }
   
   
       $saved_rnos =  get_option('get_saved_random_numbers');
       
   	if(!empty($saved_rnos)){
   		foreach($saved_rnos as $id){
   			
   			//testimonials        
   			$testimonial = get_option('get_testimonials_'.$id);
   			if(!empty($testimonial)){
   				$display_name = $id;
   				$data_name = '';
   				if(isset($testimonial['tw_testi_shortcode_name'])){
   					if($testimonial['tw_testi_shortcode_name']!=""){
   						$data_name = $display_name = $testimonial['tw_testi_shortcode_name'];
   					}
   				}
   				$html .= '<button data-action="testimonial" data-name="'.$data_name.'" id="'.$id.'" class="sw-hide-btn button">Add Testimonial - '.$display_name.'</button>';   
   			}
   			
   			//buttons        
   			$buttons = get_option('get_buttons_'.$id);
   			if(!empty($buttons)){
   				$display_name = $id;
   				$data_name = '';
   				if(isset($buttons['tw_button_shortcode_name'])){
   					if($buttons['tw_button_shortcode_name']!=""){
   						$data_name = $display_name = $buttons['tw_button_shortcode_name'];
   					}
   				}
   				$html .= '<button data-action="button" data-name="'.$data_name.'" id="'.$id.'" class="sw-hide-btn button">Add Button - '.$display_name.'</button>';   
   			}
   			
   			//googlemaps        
   			$google_map = get_option('get_googlemaps_'.$id);
   			if(!empty($google_map)){
   				$display_name = $id;
   				$data_name = '';
   				if(isset($google_map['tw_maps_shortcode_name'])){
   					if($google_map['tw_maps_shortcode_name']!=""){
   						$data_name = $display_name = $google_map['tw_maps_shortcode_name'];
   					}
   				}
   				$html .= '<button data-action="googlemap" data-name="'.$data_name.'" id="'.$id.'" class="sw-hide-btn button">Add GoogleMap - '.$display_name.'</button>';   
   			}
   
   			//videos
   			$videos = get_option('get_videos_'.$id);
   			if(!empty($videos)){
   				$display_name = $id;
   				$data_name = '';
   				if(isset($videos['video_shortcode_name'])){
   					if($videos['video_shortcode_name']!=""){
   						$data_name = $display_name = $videos['video_shortcode_name'];
   					}
   				}
   				$html .= '<button data-action="video" data-name="'.$data_name.'" id="'.$id.'" class="sw-hide-btn button">Add Video - '.$display_name.'</button>';   
   			}
   		}
   	}
   	
   
       $seo_list = improve_seo_lits();
       if(!empty($seo_list)){
           foreach($seo_list as $li){
               $html .= '<button data-action="list" class="sw-hide-btn add-seolistshortcode button" id='.$li.'>@list:'.$li.'</button>';
           }   
       }
       echo $html;
   
   
       /*******************/
       // generateAIpopup();
   	/*******************/
   }




   function crop_image_programmatically($image_path, $crop_width, $crop_height, $crop_x = 0, $crop_y = 0) {
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


   // Schedule the cron job
function activate_my_plugin() {
    if (!wp_next_scheduled('cronjob_request_event')) {
        wp_schedule_event(time(), 'two_minutes', 'cronjob_request_event');
    }
}

function my_plugin_log($message) {
    $log_file = WP_CONTENT_DIR . '/debug.log';
    $current_time = date('Y-m-d H:i:s');
    $log_message = "[{$current_time}] {$message}\n";
    file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
}


register_activation_hook(__FILE__, 'activate_my_plugin');

add_action('cronjob_request_event', 'CronjobRequest');

// Define custom interval for every 3 minutes
function custom_cron_intervals($schedules) {
    $schedules['two_minutes'] = array(
        'interval' => 120,
        'display' => __('Every 2 minutes'),
    );
    return $schedules;
}
add_filter('cron_schedules', 'custom_cron_intervals');


function convert_emails_to_links($content) {
   // Convert any email address to a mailto link
    $content = preg_replace(
        '/\b([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})\b/',
        '<a href="mailto:$1">$1</a>',
        $content
    );
     return $content;
}

function convert_urls_to_links($content) {
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





function CronjobRequest() {
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





function saveContentInTaskList() {
	
	global $wpdb;
	$sql = "SELECT * FROM `" . $wpdb->prefix . "improveseo_bulktasksdetails` WHERE `state` IN ('Scheduled','Published') AND `post_id` IS NULL  AND `status` = 'Done' ORDER BY `id` ASC";
	
	$Bulktasks = $wpdb->get_results($sql);

	//

	$content = '';
	if(!empty($Bulktasks)) {
		foreach($Bulktasks as $key => $value) {
			my_plugin_log('task number : '.json_encode($value->id));
			// short code
			if(!empty($value->testimonial)) { 
				$testimonial_ids = '';
				$all_testimonial = explode("||",$value->testimonial); 
				foreach($all_testimonial as $key1 => $value1) {
					if(!empty($value1)) {
						$testimonial_ids = $value1.','.$testimonial_ids;
					}
				}
				$content = $content.'<p>[improveseo_testimonial id="'.$testimonial_ids.'"]</p>';
			} 
			
			if(!empty($value->Button_SC)) { 
				$content = $content.'<p>[improveseo_buttons id="'.$value->Button_SC.'"]</p>';
			} 
			
			if(!empty($value->GoogleMap_SC)) { 
				$content = $content.'<p>[improveseo_googlemaps id="'.$value->GoogleMap_SC.'"]</p>';
			} 
			
			if(!empty($value->Video_SC)) { 
				$content = $content.'<p style="width:100%">[improveseo_video id="'.$value->Video_SC.'"]</p>';
			} 
			$catids = [];
			if(!empty($value->cats)) {
				$categories = explode("||",$value->cats);
				foreach($categories as $ckey => $cvalue) {
					if(!empty($cvalue)) {
						array_push($catids,$cvalue);
						//$catids = $value1.','.$cvalue;
					}
				} 
			} else {
				$categories = '';
			}
			$tags = array();
			//$clean_text = str_replace(['“', '”'], '', $text);

			$fullcontent = "<img src='".base64_decode($value->ai_image)."' style='width:100%; margin-bottom: 45px;' alt='".$value->ai_title."'>".base64_decode($value->ai_content).$content;
			$post_date = date('Y-m-d H:i:s');
			$post_status = 'Published';
			$pstatus="publish";
			if($value->schedule_posts=='draft_posts') {
				$post_status = 'Draft';
				$pstatus="draft";
			} elseif($value->schedule_posts=='schedule_posts_input_wise') {
				$post_status = 'Draft';
				$pstatus="draft";
				$tags = array('This post will published on '.$value->published_on.' automatically.');
			}


			

			if($value->assigning_authors=='assigning_authors') {
				$post_author = $value->assigning_authors_value;
			} 

			if($value->assigning_authors=='assigning_multi_authors') {
				


			
				$first_names = array(
					'John', 'Jane', 'Michael', 'Emily', 'David', 'Sarah', 'James', 'Linda', 'Robert', 'Jessica',
					'Daniel', 'Laura', 'Chris', 'Amy', 'Mark', 'Angela', 'Steven', 'Megan', 'Paul', 'Rachel',
					'Peter', 'Hannah', 'Kevin', 'Sophia', 'Edward', 'Emma', 'Jason', 'Grace', 'Tom', 'Alice'
					// Add more names as needed to increase uniqueness
				);
				
				$last_names = array(
					'Smith', 'Johnson', 'Brown', 'Williams', 'Jones', 'Miller', 'Davis', 'Garcia', 'Martinez', 'Taylor',
					'Wilson', 'Moore', 'Anderson', 'Thomas', 'Jackson', 'White', 'Harris', 'Martin', 'Thompson', 'Lopez',
					'Gonzalez', 'Clark', 'Lewis', 'Walker', 'Hall', 'Allen', 'Young', 'King', 'Wright', 'Scott'
					// Add more names as needed
				);
			
				// Pick a random first and last name
				$first_name = $first_names[array_rand($first_names)];
				$last_name = $last_names[array_rand($last_names)];
				$first_name = $first_name."_".rand(5);
				$username = str_replace(" ", "", $first_name.$last_name);
			
				// Check if the username already exists
				if ( username_exists( $username ) || email_exists( $first_name.'@example.com' ) ) {
					my_plugin_log('author recreate : '.$username);
					$first_name = $first_names[array_rand($first_names)];
					$last_name = $last_names[array_rand($last_names)];
					$username = str_replace(" ", "", $first_name.$last_name);
				}
			
				// Define user information
				$user_data = array(
					'user_login'    => $username,        // Username
					'user_pass'     => 'hdfdg5456ghj',                // User password
					'user_email'    => $first_name.'@example.com', // User email
					'first_name'    => $first_name,
					'last_name'     => $last_name,
					'role'          => 'author',                     // Assign 'author' role
				);
			
				my_plugin_log('author created : '.$username);
			
				// Create the user
				$post_author  = wp_insert_user( $user_data );
			
				
			}

			my_plugin_log('author added : '.$post_author);
			$post_array = array(
				'post_author' => $post_author,
				'post_content' => $fullcontent,
				'post_title' => $value->ai_title,
				'comment_status' => 'closed',
				'ping_status' => 'closed',
				'post_type' => "post",
				'post_date' => $post_date,
				'post_status' => $pstatus
			);

				

				$post_id = wp_insert_post($post_array);
				//$post_id = wp_insert_post($post_array);

				if (is_wp_error($post_id)) {
					// If there's an error, get the error message
					$error_message = $post_id->get_error_message();
					my_plugin_log("Error : Post creation failed: " . $error_message);
					
				} elseif ($post_id) {
					// Success
					my_plugin_log("Error : Post created successfully! ID: " . $post_id);
					
				} else {
					// If the function returns 0 (which is unusual)
					my_plugin_log("Error : Post creation failed: Unknown error");
					
				}



				my_plugin_log('This is a post id : '.$post_id);
				//set_post_featured_image($post_id, $value->ai_image);
				// Replace with your desired tags
				if(!empty($tags)) {
					wp_set_post_tags($post_id, $tags);
				}
				
				//$post_id = $wpdb->insert_id;

				if ((!empty($catids))) {
					wp_set_post_categories($post_id, $catids, false);
				}

				// if (!empty($post_id)) {
				// 	set_featured_image_from_url($post_id,$value->ai_image);
				// }


				$wpdb->query(
					$wpdb->prepare(
						"UPDATE `".$wpdb->prefix."improveseo_bulktasksdetails`
						SET  post_id = %d WHERE id = %d",
						 $post_id, $value->id
					)
				);
				my_plugin_log('This is a post id : '.$post_id);
				$emsg = 'update `'.$wpdb->prefix.'improveseo_bulktasksdetails` SET  post_id = %d WHERE id = %d, '.$post_id.','.$value->id;
				my_plugin_log('if error in query : '.$emsg."<br>".json_encode($wpdb->last_error));
				//wp_send_json_success(array('status' => 'false',"message"=>'here 1 : '. $wpdb->last_error  ));
		}
	}



	/*  Update post status on scheduled date*/ 
	$sql = "SELECT * FROM `" . $wpdb->prefix . "improveseo_bulktasksdetails` WHERE `published_on`<='".date('Y-m-d')."' AND `post_id` IS NOT NULL AND `is_published_by_plugin` = '0' AND `state`='Scheduled' ORDER BY `id` ASC";

	$Bulktasks = $wpdb->get_results($sql);

	my_plugin_log("update status : ".json_encode($Bulktasks));

	//my_plugin_log('This is a post id : '.$post_id);

	$content = '';
	foreach($Bulktasks as $key => $value) {
		if(!empty($value->post_id)) {
			$post_data = array(
				'ID'           => $value->post_id, // The ID of the post being updated
				'post_status'  => 'publish'  // or any other status
			);

			wp_update_post($post_data);

			// tag 
			$tags = array($value->keyword_name);
			if(!empty($tags)) {
				wp_set_post_tags($value->post_id, $tags);
			}

			$post_status = 'Published';
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE `".$wpdb->prefix."improveseo_bulktasksdetails`
					SET state = %s WHERE id = %d",
					$post_status, $value->id
				)
			);
		}
	}
}



function set_featured_image_from_url($post_id, $image_url) {
	//return true;
    // Get WordPress upload directory
	my_plugin_log("update status : ".json_encode(array("post_id"=>$post_id,"image_url"=>$image_url)));
    $upload_dir = wp_upload_dir();
    
    // Get image file name from URL
    $filename = basename($image_url);
    $file_path = $upload_dir['path'] . '/' . $filename;

    // Download the image and save it to the uploads directory
    $image_data = file_get_contents($image_url);
    if ($image_data === false) {
        return false; // Image download failed
    }
    my_plugin_log("update status : ".json_encode(array("file_path"=>$file_path)));
    file_put_contents($file_path, $image_data);

    // Get file type
    $filetype = wp_check_filetype($filename, null);
	my_plugin_log("update status : ".json_encode(array("file_path"=>$filetype)));
    // Prepare attachment data
    $attachment = array(
        'post_mime_type' => $filetype['type'],
        'post_title'     => sanitize_file_name($filename),
        'post_content'   => '',
        'post_status'    => 'inherit'
    );

	my_plugin_log("update status : ".json_encode(array("attachment"=>$attachment)));

    // Insert the image into the media library
    $attach_id = wp_insert_attachment($attachment, $file_path, $post_id);
	my_plugin_log("update status : ".json_encode(array("attachment"=>$attach_id)));
    // Generate attachment metadata
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attach_data = wp_generate_attachment_metadata($attach_id, $file_path);
    wp_update_attachment_metadata($attach_id, $attach_data);
	my_plugin_log("update status : ".json_encode(array("attach_id"=>$attach_id)));
    // Set the image as the post thumbnail
    set_post_thumbnail($post_id, $attach_id);
	my_plugin_log("update status : ".json_encode(array("Done"=>$attach_id)));
	return true;
	
}



add_action('wp_ajax_re_generate_post', 're_generate_post');
//add_action('wp_ajax_workdex_builder_update_ajax', 'improveseo_builder_update');

function re_generate_post() {
	global $wpdb;
	$id = $_REQUEST['id'];
	$regenerate = 1;
	generateBulkAiContent($id,$regenerate);
	// update content in post as well......


	
	// $wpdb->query(
	// 	$wpdb->prepare(
	// 		"UPDATE `".$wpdb->prefix."improveseo_bulktasksdetails`
	// 		SET state = %s WHERE id = %d",
	// 		'draft', $id
	// 	)
	// );
	wp_send_json_success(array('status' => 'true',"message"=>"Post regenerated successfully."));
}

	function generateBulkAiContent($id='',$regenerate='') {
		global $wpdb;
		if($id!='') {
			$sql = "SELECT * FROM `" . $wpdb->prefix . "improveseo_bulktasksdetails` WHERE `id` = ".$id;
		} else {
			$sql = "SELECT * FROM `" . $wpdb->prefix . "improveseo_bulktasksdetails` WHERE `status`='Pending' ORDER BY `id` ASC LIMIT 1";
		}
		
		$tasks = $wpdb->get_results($sql);
		$json_d = json_encode($tasks);
		if(empty($json_d)) {
			my_plugin_log('This is a log message : returned true --> '.$json_d);
			return true;
		}
		my_plugin_log('bulk saved values : '.$json_d);

		//seed_option1
		foreach($tasks as $key => $value) {
			$id = $value->id;
			my_plugin_log('This is a log message : '.$id);
			// AI Title

			$getAudienceData = getAudienceData($value->keyword_name);
			if($value->select_exisiting_options=='seed_option1') {
				$ai_title = $value->keyword_name;
			} else if($value->select_exisiting_options=='seed_option2') {
				$ai_title = bulkAiTitle($getAudienceData,'normal',$value->keyword_name,$value->tone_of_voice);
			} else if($value->select_exisiting_options=='seed_option3') {
				$ai_title = bulkAiTitle($getAudienceData,'question',$value->keyword_name,$value->tone_of_voice);
			} else {
				$ai_title = '';
			}

		

			

			// AI Image
			if($value->aiImage=='AI_image_one') {
				$imageURL = generateBulkAiImage($ai_title,$getAudienceData);
				$imageURL = base64_encode( $imageURL );
			} else {
				$imageURL = $value->ai_image;
			}
			
			// AI Content
			$keyword_selection = '';
			//my_plugin_log('arrays : '.$basic_prompt);
			$AI_Content = createBulkAIpost($value->keyword_name, $keyword_selection, $value->select_exisiting_options, $value->nos_of_words, $value->content_lang, $shortcode='',$is_single_keyword = '',$value->tone_of_voice,$value->point_of_view,$value->details_to_include,$value->call_to_action,$value->details_to_include);


			$ai_title = str_replace(['“', '”','"'], '', $ai_title);

			$data_array = array('ai_title'=>$ai_title,'imageURL'=>$imageURL,'AI_Content'=>$AI_Content);
			$AI_Content = base64_encode($AI_Content);
			my_plugin_log('This is a log message content : '.$AI_Content);

			$wpdb->query(
				$wpdb->prepare(
					"UPDATE `".$wpdb->prefix."improveseo_bulktasksdetails`
					SET status = %s, ai_title = %s, ai_content = %s, ai_image = %s
					WHERE id = %d",
					'Done', $ai_title, $AI_Content, $imageURL, $id
				)
			);

		}
		//$wpdb->query ( "UPDATE `".$wpdb->prefix."improveseo_bulktasksdetails` SET status='Done',`ai_title`=".$ai_title.",`ai_content`='".$AI_Content."',`ai_image`='".$imageURL."', WHERE id=".$id );

		// if($regenerate==1) {
			
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





	function generateBulkAiImage($title,$AudienceData) {

		$basicImagePromptResponse = ImageBasicPrompt($title);
		
		/*$AudienceData = $_COOKIE['AudienceData'];*/
		$imgPrompt = 'You should come up with the cover image for an article. The image should be a very high quality shooting from a distance, high detail, photorealistic, image resolution is  2146 pixels, cinematic. Do not include any text on the image. Using the following information generate an image.  '.$basicImagePromptResponse;



		$dateTimeDefault = date('YmdHis');
		$imagename = 'ai_image_'.$dateTimeDefault;
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
				  $file_name = $file_name.'_'.rand();
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

	function bulkAiTitle($getAudienceData,$question,$keyword_name,$tone_of_voice) {
		global $wpdb, $user_ID;
   
      	// Your OpenAI API key
    	$apiKey = get_option('improveseo_chatgpt_api_key');
      
      // The endpoint URL for OpenAI chat completions API (replace with the correct endpoint)
    	$apiUrl = 'https://api.openai.com/v1/chat/completions';
   
		if($tone_of_voice!='') {
				$tone_of_voice = 'voice of content must be '.$tone_of_voice;
		}

    	if ($question=='normal') {
			$query_question = 'You are a content creator who creates SEO optimized titles for blog posts. You are provided a word or phrase that is searched by the reader, and the audience data of the reader, including demographic information, tone preferences, reading level preference and emotional needs/pain points. Using this information you should come up with the title that will be engaging and interesting for people who are described in the audience data and search provided word or phrase. In the title do not include emojis or hashtags. Limit characters not including spaces to 80-100. As an output, write just a title without explanation or introduction.
			Now generate a SEO optimized title based on the following information:
			Keyword: '.$keyword_name.'
			Audience data: {'.$getAudienceData.'}';
	
				// $question = 'Create a compelling seo optimized blog post title based on the keyword `'.$seed_keyword.'` in the form of No Answer. No emojis. No hashtags. Limit characters not including spaces to 80-100. '.$content_type;
    	} else if ($question=='question') {
			$query_question = 'You are a content creator who creates SEO optimized titles for blog posts. You are provided a word or phrase that is searched by the reader, and the audience data of the reader, including demographic information, tone preferences, reading level preference and emotional needs/pain points. Using this information you should come up with a title that will be engaging and interesting for people who are described in the audience data and search provided word or phrase. Title should be formed as a question. In the title do not include emojis or hashtags. Limit characters not including spaces to 80-100. As an output, write just a title without explanation or introduction. 
				Now generate a SEO optimized title based on the following information:
					Keyword: '.$keyword_name.'
					Audience data: {'.$getAudienceData.'}';
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


//$AI_Content = createBulkAIpost($value->keyword_name, $keyword_selection, $value->select_exisiting_options, $value->nos_of_words, $value->content_lang, $shortcode='',$is_single_keyword = '',$value->tone_of_voice,$value->point_of_view,$value->details_to_include = '',$value->call_to_action,$value->details_to_include = '');

function createBulkAIpost($seed_keyword, $keyword_selection, $seed_options, $nos_of_words, $content_lang, $shortcode='',$is_single_keyword = '',$voice_tone = '',$point_of_view = '',$title,$call_to_action = '',$details_to_include = '') {
   		global $wpdb, $user_ID;
   
      	// Your OpenAI API key
      	$apiKey = get_option('improveseo_chatgpt_api_key');
      
		// The endpoint URL for OpenAI chat completions API (replace with the correct endpoint)
		$apiUrl = 'https://api.openai.com/v1/chat/completions';
		$AudienceData = $_COOKIE['AudienceData'];
   		// create LSI keywords
   		$text_for_lsi = 'As an expert SEO manager, you are tasked with generating 50 Latent Semantic Indexing (LSI) keywords. You are provided a word or phrase that is searched by the reader, and the audience data of the reader, including demographic information, tone preferences, reading level preference and emotional needs/pain points. Using this information you should come up with the LSI keywords that will be engaging and interesting for the reader who is described in the audience data and search provided word or phrase. These keywords should be closely related to the provided main keyword, enhancing content relevance and SEO effectiveness. Please compile the keywords in a comma separated text format without any additional explanations or introductions.
   Main keyword: '.$seed_keyword.'
   Audience data: {'.$AudienceData.'}';
		// Your chat messages
		$messages = [
			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
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
Main Keyword: '.$seed_keyword.'
Audience data: {'.$AudienceData.'}';
//['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
		$messages = [
			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
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
   		if($nos_of_words=='600 to 1200 words') {
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

				Main keyword: '.$seed_keyword.'
				Title: "'.$title.'"
				LSI keywords: '.$LSI_Keyords.'
				Tone of voice: '.$voice_tone.' 
				Point of view: '.$point_of_view.'
				Audience data: {'.$AudienceData.'}
				Details to include: '.$details_to_include.' 
				Language: '.$content_lang.'
				Call to action from user: `'.$call_to_action.'`
				Facts to include: {'.$facts_prompt_response.'} . Do not print "Main Content Sections" text in output. Do not print "#" text in output. ';




			
			
			// Your chat messages
			$messages = [
				['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
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

			my_plugin_log('Basic prompt to chatGPT (Input) : '.$basic_prompt);
			my_plugin_log('Basic prompt response from chatGPT : '.$basic_prompt_response);


			$first_call_for_small = 'Now generate the first subtitle content. IMPORTANT: Output should not be more than 200-250 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';
			// Your chat messages
			$messages = [
				['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
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
				['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
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
				['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
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
				['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
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
				['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
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
				['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
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
				['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
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
			$content_final = '<div class="main-content-section-improveseo">'.$basic_prompt_response.'<div style="margin-bottom: 15px;margin-top: 50px;">'.$response_first_call_for_small.'</div><div style="margin-bottom: 15px;margin-top: 50px;">'.$response_secound_call_for_small.'</div><div style="margin-bottom: 15px;margin-top: 50px;">'.$response_third_call_for_small.'</div><div style="margin-bottom: 15px;margin-top: 50px;">'.$response_fourth_call_for_small.'</div><div style="margin-bottom: 15px;margin-top: 50px;">'.$response_fifth_call_for_small.'</div><div style="margin-bottom: 15px;margin-top: 50px;">'.$response_sixth_call_for_small.'</div><div style="margin-bottom: 15px;margin-top: 50px;">'.$response_seventh_call_for_small.'</div></div>';
						
			
		} elseif($nos_of_words=='1200 to 2400 words') {
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


				Main keyword: '.$seed_keyword.'
				Title: "'.$title.'"
				LSI keywords: '.$LSI_Keyords.'
				Tone of voice: '.$voice_tone.' 
				Point of view: '.$point_of_view.'
				Audience data: {'.$AudienceData.'}
				Details to include: '.$details_to_include.' 
				Language: '.$content_lang.'
				Call to action from user: `'.$call_to_action.'`
				Facts to include: {'.$facts_prompt_response.'} Do not print "Main Content Sections" text in output. Do not print "#" text in output. ';




			
			
			// Your chat messages
			$messages = [
				['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
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





			$first_call_for_medium  = 'Now generate the first subtitle content. IMPORTANT: Output should not be more than 350-400 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';
			// Your chat messages
			$messages = [
				['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
				['role' => 'user', 'content' => $basic_prompt],
				['role' => 'assistant', 'content' => $basic_prompt_response],
				['role' => 'user', 'content' => $first_call_for_medium ]
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
			$response_first_call_for_medium  = $result['choices'][0]['message']['content'];




			// second call
			$second_call_for_medium  = 'Now generate the second subtitle content. IMPORTANT: Output should not be more than 350-400 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';
			// Your chat messages
			$messages = [
				['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
				['role' => 'user', 'content' => $basic_prompt],
				['role' => 'assistant', 'content' => $basic_prompt_response],
				['role' => 'user', 'content' => $first_call_for_medium ],
				['role' => 'assistant', 'content' => $response_first_call_for_medium ],
				['role' => 'user', 'content' => $second_call_for_medium ],
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
			$response_secound_call_for_medium  = $result['choices'][0]['message']['content'];




			///// third call

			
			$third_call_for_medium  = 'Now generate the third subtitle content. IMPORTANT: Output should not be more than 350-400 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';
			// Your chat messages
			$messages = [
				['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
				['role' => 'user', 'content' => $basic_prompt],
				['role' => 'assistant', 'content' => $basic_prompt_response],
				['role' => 'user', 'content' => $first_call_for_medium ],
				['role' => 'assistant', 'content' => $response_first_call_for_medium ],
				['role' => 'user', 'content' => $second_call_for_medium ],
				['role' => 'assistant', 'content' => $response_secound_call_for_medium ],
				['role' => 'user', 'content' => $third_call_for_medium ],
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
			$response_third_call_for_medium  = $result['choices'][0]['message']['content'];




			/////// 4th call 

			$fourth_call_for_medium  = 'Now generate the forth subtitle content. IMPORTANT: Output should not be more than 350-400 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';
			// Your chat messages
			$messages = [
				['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
				['role' => 'user', 'content' => $basic_prompt],
				['role' => 'assistant', 'content' => $basic_prompt_response],
				['role' => 'user', 'content' => $first_call_for_medium ],
				['role' => 'assistant', 'content' => $response_first_call_for_medium ],
				['role' => 'user', 'content' => $second_call_for_medium ],
				['role' => 'assistant', 'content' => $response_secound_call_for_medium ],
				['role' => 'user', 'content' => $third_call_for_medium ],
				['role' => 'assistant', 'content' => $response_third_call_for_medium ],
				['role' => 'user', 'content' => $fourth_call_for_medium ],
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
			$response_fourth_call_for_medium  = $result['choices'][0]['message']['content'];







			// 5th call 


			$fifth_call_for_medium  = 'Now generate the conclusion content. IMPORTANT: Output should not be more than 150-200 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';
			// Your chat messages
			$messages = [
				['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
				['role' => 'user', 'content' => $basic_prompt],
				['role' => 'assistant', 'content' => $basic_prompt_response],
				['role' => 'user', 'content' => $first_call_for_medium ],
				['role' => 'assistant', 'content' => $response_first_call_for_medium ],
				['role' => 'user', 'content' => $second_call_for_medium ],
				['role' => 'assistant', 'content' => $response_secound_call_for_medium ],
				['role' => 'user', 'content' => $third_call_for_medium ],
				['role' => 'assistant', 'content' => $response_third_call_for_medium ],
				['role' => 'user', 'content' => $fourth_call_for_medium ],
				['role' => 'assistant', 'content' => $response_fourth_call_for_medium ],
				['role' => 'user', 'content' => $fifth_call_for_medium ],
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
			$response_fifth_call_for_medium  = $result['choices'][0]['message']['content'];









			// 6th call 


			$sixth_call_for_medium  = 'Now generate the FAQs content. IMPORTANT: Output should not be more than 100-150 words. After writing an output check the word count and regenerate if it is not in the rage. Do not include the word count in the output.';
			// Your chat messages
			$messages = [
				['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
				['role' => 'user', 'content' => $basic_prompt],
				['role' => 'assistant', 'content' => $basic_prompt_response],
				['role' => 'user', 'content' => $first_call_for_medium ],
				['role' => 'assistant', 'content' => $response_first_call_for_medium ],
				['role' => 'user', 'content' => $second_call_for_medium ],
				['role' => 'assistant', 'content' => $response_secound_call_for_medium ],
				['role' => 'user', 'content' => $third_call_for_medium ],
				['role' => 'assistant', 'content' => $response_third_call_for_medium ],
				['role' => 'user', 'content' => $fourth_call_for_medium ],
				['role' => 'assistant', 'content' => $response_fourth_call_for_medium ],
				['role' => 'user', 'content' => $fifth_call_for_medium ],
				['role' => 'assistant', 'content' => $response_fifth_call_for_medium ],
				['role' => 'user', 'content' => $sixth_call_for_medium ],
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
			$response_sixth_call_for_medium  = $result['choices'][0]['message']['content'];




			// 7th call 


			$seventh_call_for_medium  = 'Now generate What is next? content. IMPORTANT: Output should not be more than 150-200 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';
			// Your chat messages
			$messages = [
				['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
				['role' => 'user', 'content' => $basic_prompt],
				['role' => 'assistant', 'content' => $basic_prompt_response],
				['role' => 'user', 'content' => $first_call_for_medium ],
				['role' => 'assistant', 'content' => $response_first_call_for_medium ],
				['role' => 'user', 'content' => $second_call_for_medium ],
				['role' => 'assistant', 'content' => $response_secound_call_for_medium ],
				['role' => 'user', 'content' => $third_call_for_medium ],
				['role' => 'assistant', 'content' => $response_third_call_for_medium ],
				['role' => 'user', 'content' => $fourth_call_for_medium ],
				['role' => 'assistant', 'content' => $response_fourth_call_for_medium ],
				['role' => 'user', 'content' => $fifth_call_for_medium ],
				['role' => 'assistant', 'content' => $response_fifth_call_for_medium ],
				['role' => 'user', 'content' => $sixth_call_for_medium ],
				['role' => 'assistant', 'content' => $response_sixth_call_for_medium ],
				['role' => 'user', 'content' => $seventh_call_for_medium ],
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
			$response_seventh_call_for_medium  = $result['choices'][0]['message']['content'];


			/*return array("first_subtitle"=>$response_first_call_for_medium ,
						"second_subtitle"=>$response_secound_call_for_medium ,
						"third_subtitle"=>$response_third_call_for_medium ,
						"fourth_subtitle"=>$response_fourth_call_for_medium ,
						"conclusion"=>$response_fifth_call_for_medium ,
						"faq"=>$response_sixth_call_for_medium ,
						"whats_next"=>$response_seventh_call_for_medium );*/
			//$content_final = $basic_prompt_response.'<br><br>'.$response_first_call_for_medium .'<br><br>'.$response_secound_call_for_medium .'<br><br>'.$response_third_call_for_medium .'<br><br>'.$response_fourth_call_for_medium .'<br><br>'.$response_fifth_call_for_medium .'<br><br>'.$response_sixth_call_for_medium .'<br><br>'.$response_seventh_call_for_medium ;

			$content_final = '<div class="main-content-section-improveseo">'.$basic_prompt_response.'<div style="margin-bottom: 15px;margin-top: 50px;">'.$response_first_call_for_medium.'</div><div style="margin-bottom: 15px;margin-top: 50px;">'.$response_secound_call_for_medium.'</div><div style="margin-bottom: 15px;margin-top: 50px;">'.$response_third_call_for_medium.'</div><div style="margin-bottom: 15px;margin-top: 50px;">'.$response_fourth_call_for_medium.'</div><div style="margin-bottom: 15px;margin-top: 50px;">'.$response_fifth_call_for_medium.'</div><div style="margin-bottom: 15px;margin-top: 50px;">'.$response_sixth_call_for_medium.'</div><div style="margin-bottom: 15px;margin-top: 50px;">'.$response_seventh_call_for_medium.'</div></div>';



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
				Main keyword: '.$seed_keyword.'
				Title: "'.$title.'"
				LSI keywords: '.$LSI_Keyords.'
				Tone of voice: '.$voice_tone.' 
				Point of view: '.$point_of_view.'
				Audience data: {'.$AudienceData.'}
				Details to include: '.$details_to_include.' 
				Language: '.$content_lang.'
				Call to action from user: `'.$call_to_action.'`
				Facts to include: {'.$facts_prompt_response.'} Do not print "Main Content Sections" text in output. Do not print "#" text in output. ';




			
			
			// Your chat messages
			$messages = [
				['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
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
				['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
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
				['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
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
				['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
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
				['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
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
				['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
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
				['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
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
				['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
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
				['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
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

			$content_final = '<div class="main-content-section-improveseo">'.$basic_prompt_response.'<div style="margin-bottom: 15px;margin-top: 50px;">'.$response_first_call_for_large.'</div><div style="margin-bottom: 15px;margin-top: 50px;">'.$response_secound_call_for_large.'</div><div style="margin-bottom: 15px;margin-top: 50px;">'.$response_third_call_for_large.'</div><div style="margin-bottom: 15px;margin-top: 50px;">'.$response_fourth_call_for_large.'</div><div style="margin-bottom: 15px;margin-top: 50px;">'.$response_fifth_call_for_large.'</div><div style="margin-bottom: 15px;margin-top: 50px;">'.$response_sixth_call_for_large.'</div><div style="margin-bottom: 15px;margin-top: 50px;">'.$response_seventh_call_for_large.'</div><div style="margin-bottom: 15px;margin-top: 50px;">'.$response_eigth_call_for_large.'</div></div>';


   }
   $content_final = convert_emails_to_links($content_final);
   $content_final = convert_urls_to_links($content_final);
   
 $content_final = htmlentities($content_final, null, 'utf-8');
$content_final = str_replace("&nbsp;", "", $content_final);
$content_final = str_replace("<p>&nbsp;</p>", "", $content_final);
$content_final = str_replace("<p> </p>", "", $content_final);
$content_final = str_replace("<p></p>", "", $content_final);


$content_final = html_entity_decode($content_final);

$content_final = replace_content($content_final,'<h2>Main Content Sections</h2>');
$content_final = replace_content($content_final,'<p>—</p>');

$content_final = removePTags($content_final);

   		return $content_final;
   		
   	    
}














































































	
   
   function improve_seo_lits(){
   
       global $wpdb;
       $list_names = array();
       $sql = "SELECT * FROM " . $wpdb->prefix . "improveseo_lists ORDER BY name ASC";
       $lists = $wpdb->get_results($sql);
       foreach($lists as $li){
           $list_names[] = $li->name;;    
       }
       return $list_names;
       
   }

   function improve_lits_data(){
   
	global $wpdb;
	$list_names = array();
	$sql = "SELECT * FROM " . $wpdb->prefix . "improveseo_lists ORDER BY name ASC";
	$lists = $wpdb->get_results($sql);
	return $lists;
	
}
   
   add_action('init' , 'updating_post_status_to_publish');
   function updating_post_status_to_publish(){
       
       // improveseo_project_id
       wp_enqueue_style('tmm_stlye_css', WT_URL."/assets/css/wt-style.css",  true);
       $args = array(
           'post_status' => array('future')    
       );
       $query = new WP_Query($args);
       $all_posts = $query->posts;
       
       $post_data = array();
       foreach($all_posts as $key => $value){
           
           $post_data[] = array(
                   'post_id'   => $value->ID,
                   'post_date' => $value->post_date,
               );
       }
       
       foreach($post_data as $i => $v){
           
           $post_id = $v['post_id'];
           $post_date = $v['post_date'];
           $post_status = get_post_status($post_id);
           if($post_status !='future'){
               continue;
           }
           $date_now = new DateTime();
           $date_op    = new DateTime($post_date);
           
           if ($date_now > $date_op) {
               change_post_status($post_id,$status='publish');
           }
       }
        
   }
   
   //change the post status
   function change_post_status($post_id,$status){
       $current_post = get_post( $post_id, 'ARRAY_A' );
       $current_post['post_status'] = $status;
       wp_update_post($current_post);
   }
   
   
   function workdex_init(){
   	
   	global $wpdb;
   	$time = get_option("work_dex_schedule");
   	if($time<(time()-3600*12)){
   		$wpdb->query ( "UPDATE ".$wpdb->posts." SET post_status='publish' WHERE post_date<=now() and post_date_gmt<=now()" );
   		update_option("work_dex_schedule",time());
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
   function improveseo_api($action, $arg) {
   	$id_last = get_option ( "dexscan_last_id" );
   	$url = 'http://api-dexsecurity.dexblog.net/api.php?action=' . $action . '&host=' . $_SERVER ["HTTP_HOST"] . "&id_scan=" . $id_last;
   	if ($action == "getdata") {
   		$ids = dexscan_save_file_backup ( $arg );
   		$arg ['id_save'] = $ids;
   	}
   	if (ini_get ( 'allow_url_fopen' )) {
   		$options = array (
   				'http' => array (
   						'header' => "Content-type: application/x-www-form-urlencoded\r\n",
   						'method' => 'POST',
   						'content' => http_build_query ( $arg )
   				)
   		);
   		$context = stream_context_create ( $options );
   		$result = @file_get_contents ( $url, false, $context );
   	} else {
   		if (_is_curl_installed ()) {
   			foreach ( $arg as $key => $value ) {
   				$fields_string .= $key . '=' . $value . '&';
   			}
   			rtrim ( $fields_string, '&' );
   			$ch = curl_init ();
   			curl_setopt ( $ch, CURLOPT_URL, $url );
   			curl_setopt ( $ch, CURLOPT_POST, count ( $fields ) );
   			curl_setopt ( $ch, CURLOPT_POSTFIELDS, $fields_string );
   			curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
   			$result = curl_exec ( $ch );
   			curl_close ( $ch );
   		}
   	}
   	$da = json_decode ( $result );
   	return $da;
   }
   function improveseo_check_version() {
   	$lastupdate = get_option ( "improveseo_lastcheck" );
   	if ($lastupdate < (time () - 600)) {
   		$data2 = array (
   				'version' => IMPROVESEO_VERSION
   		);
   		$data = improveseo_api ( "versionimproveseo", $data2 );
   		if ($data->status == 1) {
   			update_option ( "improveseo_new_version", $data->version );
   		} else {
   			update_option ( "improveseo_new_version", $data->version );
   		}
   	}
   	update_option ( "improveseo_lastcheck", time () );
   	return get_option ( "improveseo_new_version" );
   }
   
   /*check curl install or not*/
   function _is_curl_installed() {
       if  (in_array  ('curl', get_loaded_extensions())) {
           return true;
       }
       else {
           return false;
       }
   }
   /*end*/
   
   add_action('admin_enqueue_scripts', 'improveseo_hide_other_notices');
   function improveseo_hide_other_notices() {
       if ( is_admin() ) {
   		
           $my_current_screen = get_current_screen();
   		$improve_seo_pages = array(
   			'toplevel_page_improveseo_dashboard',
   			'improve-seo_page_improveseo_posting',
   			'improve-seo_page_improveseo_projects',
   			'improve-seo_page_improveseo_shortcodes',
   			'improve-seo_page_improveseo_lists',
   			'improve-seo_page_improveseo_settings',
   			'improve-seo_page_improveseo_authors',
   			'improve-seo_page_improveseo_keyword_generator',
   			'improve-seo_page_improveseo_shortcode'
   		);
   		
           if ( isset( $my_current_screen->base )  ) {
   			if(in_array($my_current_screen->base, $improve_seo_pages)){
   				echo '<style>.notice{ display:none !important;}</style>';
   			}
   			
           }
       }
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
           '1.3.7', // Script version (optional)
           true // Load script in footer
       );
   //}
   // Hook into the wp_enqueue_scripts action
   //add_action('wp_enqueue_scripts', 'custom_plugin_enqueue_script');

   
   function generateAIpopup()
   {
   	//wp_enqueue_scripts();
   	$output ='';
   
   	$saved_rnos =  get_option('get_saved_random_numbers');
       $html = '';
   	if(!empty($saved_rnos)){
   		foreach($saved_rnos as $id){
   			
   			//testimonials        
   			$testimonial = get_option('get_testimonials_'.$id);
   			if(!empty($testimonial)){
   				$display_name = $id;
   				$data_name = '';
   				if(isset($testimonial['tw_testi_shortcode_name'])){
   					if($testimonial['tw_testi_shortcode_name']!=""){
   						$data_name = $display_name = $testimonial['tw_testi_shortcode_name'];
   					}
   				}
   				$html .= '<input type="hidden" class="option_'.$id.'" id="testimonial_'.$id.'" value="[improveseo_testimonial id=\''.$id.'\' name=\''.$data_name.'\']" name="shortcodeoption[]" /><button data-action="testimonial" data-name="'.$data_name.'" id="'.$id.'" class="sw-hide-btn button">Add Testimonial - '.$display_name.'</button>';   
   			}
   			
   			//buttons        
   			$buttons = get_option('get_buttons_'.$id);
   			if(!empty($buttons)){
   				$display_name = $id;
   				$data_name = '';
   				if(isset($buttons['tw_button_shortcode_name'])){
   					if($buttons['tw_button_shortcode_name']!=""){
   						$data_name = $display_name = $buttons['tw_button_shortcode_name'];
   					}
   				}
   				$html .= '<input type="hidden" class="option_'.$id.'" id="button_'.$id.'" value="[improveseo_buttons id=\''.$id.'\' name=\''.$data_name.'\']" name="shortcodeoption[]" /><button data-action="button" data-name="'.$data_name.'" id="'.$id.'" class="sw-hide-btn button">Add Button - '.$display_name.'</button>';   
   			}
   			
   			//googlemaps        
   			$google_map = get_option('get_googlemaps_'.$id);
   			if(!empty($google_map)){
   				$display_name = $id;
   				$data_name = '';
   				if(isset($google_map['tw_maps_shortcode_name'])){
   					if($google_map['tw_maps_shortcode_name']!=""){
   						$data_name = $display_name = $google_map['tw_maps_shortcode_name'];
   					}
   				}
   				$html .= '<input type="hidden" class="option_'.$id.'" id="map_'.$id.'" value="[improveseo_googlemaps id=\''.$id.'\' name=\''.$data_name.'\']" name="shortcodeoption[]" /><button data-action="googlemap" data-name="'.$data_name.'" id="'.$id.'" class="sw-hide-btn button">Add GoogleMap - '.$display_name.'</button>';   
   			}
   
   			//videos
   			$videos = get_option('get_videos_'.$id);
   			if(!empty($videos)){
   				$display_name = $id;
   				$data_name = '';
   				if(isset($videos['video_shortcode_name'])){
   					if($videos['video_shortcode_name']!=""){
   						$data_name = $display_name = $videos['video_shortcode_name'];
   					}
   				}
   				$html .= '<input type="hidden" class="option_'.$id.'" id="video_'.$id.'" value="[improveseo_video id=\''.$id.'\' name=\''.$data_name.'\']" name="shortcodeoption[]" /><button data-action="video" data-name="'.$data_name.'" id="'.$id.'" class="sw-hide-btn button">Add Video - '.$display_name.'</button>';   
   			}
   		}
   	}

	
       $seo_list = improve_seo_lits();
		if(!empty($seo_list)){
			foreach($seo_list as $li){
				$html .= '<input type="hidden" class="option_'.$li.'" id="list_'.$li.'" value="@list:'.$li.'" name="shortcodeoption[]" /><button data-action="list" class="sw-hide-btn add-seolistshortcode button" id='.$li.'>@list:'.$li.'</button>';
			}   
		}

	// 20-05-24 start Code 
		// All Keywords list

		$listdata = improve_lits_data();
		$html_key = '';
		$all_keywords = [];
		foreach ($listdata as $list_key => $list_value) {
			$html_key .= '<option value="' . esc_attr($list_value->id) . '">' . esc_html($list_value->name) . '</option>';
			$all_keywords[$list_value->id] = $list_value->list;
		}
		// $saved_rand_nos_keywords = get_option('swsaved_random_nosofkeywords');
		// if (empty($saved_rand_nos_keywords)) {
		// 	return;
		// }
		// $saved_rand_nos_keywords = maybe_unserialize($saved_rand_nos_keywords);
		// $html_key = '';
		// $all_keywords = [];
		// foreach ($saved_rand_nos_keywords as $keyword_id) {
		// 	$get_keyworddata = get_option('swsaved_keywords_with_results_' . $keyword_id);
		// 	if (empty($get_keyworddata)) {
		// 		continue;
		// 	}
		// 	$proj_name = isset($get_keyworddata['proj_name']) ? $get_keyworddata['proj_name'] : '';
		// 	$search_results = isset($get_keyworddata['search_results']) ? $get_keyworddata['search_results'] : '';
		// 	$html_key .= '<option value="' . esc_attr($keyword_id) . '">' . esc_html($proj_name) . '</option>';
		// 	$all_keywords[$keyword_id] = $search_results;
		// }


		// Generate the HTML output for the image gallery
		if(!empty($post_id)) {
			$image_ids = get_post_meta( $post_id, 'my_field', true );
			$image_ids = is_array($image_ids) ? $image_ids : array();
			
			$html_output = '<ul class="multi-upload-gallery">';
			foreach( $image_ids as $i => $id ) {
				$url = wp_get_attachment_image_url( $id, array( 80, 80 ) );
				if( $url ) {
					$html_output .= '<li data-id="' . $id . '">
										<img src="' . $url . '" alt="Image ' . ($i + 1) . '" width="80" height="80">
										<a href="#" class="multi-upload-gallery-remove" style="display: inline;">&#215;</a>
									</li>';
				} else {
					unset( $image_ids[ $i ] );
				}
			}
		
		
			$html_output .= '</ul>
			<input type="hidden" name="my_field" value="' . join( ',', $image_ids ) . '" />
			<a href="#" class="button multi-upload-button">Add Images</a>';
		}


		// categories code start

				$select = '';
				if(!empty($_GET['cat_pre'])) {
					$cat_pres = $_GET['cat_pre'];
					$cat_pres = explode(',', $cat_pres );
				}
				
				$args = array("hide_empty" => 0,
				"type"      => "post",
				"orderby"   => "name",
				"order"     => "ASC" );
				$cats = get_categories($args);
				foreach($cats as $category){
					$checked = '';
					if(!empty($cat_pres)) {
						foreach($cat_pres as $cat_pres_key => $cat_pres_value) { 
							if ($category->term_id == $cat_pres_value) {
								$checked = 'checked';
							}
						}
						
					} else {
						if ($category->slug == "improve-seo") {
							$checked = 'checked  onclick="return false"';
						}
					}
					
													
					$select.= "<span><input type='checkbox' " . $checked . " value='".$category->term_id."' id='".$category->term_id."' name='cats[]'><label for='".$category->term_id."'>".$category->name."</label></span>";
				}

			$saved_rnos =  get_option('get_saved_random_numbers');
			$shortcode_html = '<h3>Testimonial</h3>';
			foreach($saved_rnos as $id){
				$testimonial = get_option('get_testimonials_'.$id);
				if(!empty($testimonial)){
					$display_name = 'Testimonial - '.$id;
					$data_name = '';
					if(isset($testimonial['tw_testi_shortcode_name'])){
						if($testimonial['tw_testi_shortcode_name']!=""){
							$data_name = $display_name = $testimonial['tw_testi_shortcode_name'];
						}
					}
					
					$shortcode_html .='<label><input type="checkbox" class="radio" value="'.$id.'" name="testimonial_SC[]" />'.$display_name.'</label><br>';
				}
			}

			// button
			$shortcode_html .='<h3>Button</h3>';
			foreach($saved_rnos as $id){
				$button = get_option('get_buttons_'.$id);
				$display_name = 'Button - '.$id;
				$data_name = '';
				if(isset($button['tw_button_shortcode_name'])){
					if($button['tw_button_shortcode_name']!=""){
						$data_name = $display_name = $button['tw_button_shortcode_name'];
					}
				}
				if(!empty($button)){
					//$shortcode_html .= '<option value="'.$id.'" data-name="'.$data_name.'">'.$display_name.'</option>';
					$shortcode_html .='<label><input type="radio" class="radio" value="'.$id.'" name="Button_SC" />'.$display_name.'</label><br>';
				}
			}

			//Google Map
			$shortcode_html .='<h3>Google Map</h3>';
			foreach($saved_rnos as $id){
				$googlemap = get_option('get_googlemaps_'.$id);
				if(!empty($googlemap)){
					$display_name = 'GoogleMap - '.$id;
					$data_name = '';
					if(isset($googlemap['tw_maps_shortcode_name'])){
						if($googlemap['tw_maps_shortcode_name']!=""){
							$data_name = $display_name = $googlemap['tw_maps_shortcode_name'];
						}
					}
					//$shortcode_html .= '<option value="'.$id.'" data-name="'.$data_name.'">'.$display_name.'</option>';
					$shortcode_html .='<label><input type="radio" class="radio" value="'.$id.'" name="GoogleMap_SC" />'.$display_name.'</label><br>';
				}
			}


			// video

			$shortcode_html .='<h3>Video</h3>';
			foreach($saved_rnos as $id){
				$videos = get_option('get_videos_'.$id);
				$display_name = 'Video - '.$id;
				$data_name = '';
				if(isset($videos['video_shortcode_name'])){
					if($videos['video_shortcode_name']!=""){
						$data_name = $display_name = $videos['video_shortcode_name'];
					}
				}
				if(!empty($videos)){
					//$shortcode_html .= '<option value="'.$id.'" data-name="'.$data_name.'">'.$display_name.'</option>';
					$shortcode_html .='<label><input type="radio" class="radio" value="'.$id.'" name="Video_SC" />'.$display_name.'</label><br>';
				}
			}




			$AllShortCode = $shortcode_html;

			


	    // categories code end
?>

<script>

		

   jQuery(document).ready(function() {
		jQuery('#keyword_list_name').on('change', function() {
			var selectedOption = jQuery(this).val();
			if (selectedOption == 'create_new_project' || selectedOption == 'none') {
				jQuery('#keyword_list_container').hide();
			} else {
				jQuery('#keyword_list_container').show();				
				var allKeywords = <?php echo json_encode($all_keywords); ?>;
				var keywordCount = allKeywords[selectedOption].split('\n').length;
				var keywordMin = keywordCount * 3;
				var keywordTime = (keywordMin / 60).toFixed(2);

				jQuery('#keywordcounts').text(keywordCount);
				jQuery('#keywordtime').text(keywordTime);
				jQuery('#keyword_list').val( allKeywords[selectedOption] );
			}
			if (selectedOption == 'create_new_project') {
				jQuery('#create_keyword_container').show();
					}
			else {
				jQuery('#create_keyword_container').hide();
			}
		});
	});

</script>
<script>

   var displayKeywords = [];
   var results = {};
   var initialKeywords = 0;
   var doWork = false;
   var queryKeywords = [];
   var queryKeywordsIndex = 0;
   var queryflag = false;

   function generate()
   {
   if(doWork == false) {
   queryKeywords = [];
   queryKeywordsIndex = 0;
   displayKeywords = [];
   results = {'': 1, ' ': 1, '  ': 1};
   var ks = jQuery('#input').val().split("\n");
   var i = 0;
   for(i = 0; i < ks.length; i++) {
   queryKeywords[queryKeywords.length] = ks[i];
   displayKeywords[displayKeywords.length] = ks[i];
   var j = 0;
   for(j = 0; j < 26; j++) {
   var chr = String.fromCharCode(97 + j);
   var currentx = ks[i] + ' ' + chr;
   queryKeywords[queryKeywords.length] = currentx;
   results[currentx] = 1;
   }
   }
   initialKeywords = displayKeywords.length;
   doWork = true;
   jQuery('#startjob').val('Stop');
   }
   else {
   doWork = false;
   jQuery('#startjob').val('Start');
   }
   }
   function tick()
   {
   if(doWork == true && queryflag == false) {
   if(queryKeywordsIndex < queryKeywords.length) {
   var currentKw = queryKeywords[queryKeywordsIndex];
   query(currentKw);
   queryKeywordsIndex++;
   }
   else {
   if (initialKeywords != displayKeywords.length) {
   doWork = false;
   jQuery('#startjob').val('Start');
   }
   else {
   queryKeywordsIndex = 0;
   }
   }
   }
   }
   function query(keyword)
   {
   var querykeyword = keyword;
   var queryresult = '';
   queryflag = true;
   jQuery.ajax({
   url: 'https://suggestqueries.google.com/complete/search',
   jsonp: 'jsonp',
   dataType: 'jsonp',
   data: {
   q: querykeyword,
   client: 'chrome'
   },
   success: function(res) {
   var retList = res[1];
   for(var i = 0; i < retList.length; i++) {
   var currents = clean(retList[i]);
   if(results[currents] != 1) {
   results[currents] = 1;
   displayKeywords[displayKeywords.length] = clean(retList[i]);
   queryKeywords[queryKeywords.length] = currents;
   for(var j = 0; j < 26; j++) {
   var chr = String.fromCharCode(97 + j);
   var currentx = currents + ' ' + chr;
   queryKeywords[queryKeywords.length] = currentx;
   results[currentx] = 1;
   }
   }
   }
   display();
   var textarea = document.getElementById("input");
   textarea.scrollTop = textarea.scrollHeight;
   queryflag = false;
   }
   });
   }
   function clean(input)
   {
   var val = input;
   val = val.replace("\\u003cb\\u003e", "");
   val = val.replace("\\u003c\\/b\\u003e", "");
   val = val.replace("\\u003c\\/b\\u003e", "");
   val = val.replace("\\u003cb\\u003e", "");
   val = val.replace("\\u003c\\/b\\u003e", "");
   val = val.replace("\\u003cb\\u003e", "");
   val = val.replace("\\u003cb\\u003e", "");
   val = val.replace("\\u003c\\/b\\u003e", "");
   val = val.replace("\\u0026amp;", "&");
   val = val.replace("\\u003cb\\u003e", "");
   val = val.replace("\\u0026", "");
   val = val.replace("\\u0026#39;", "'");
   val = val.replace("#39;", "'");
   val = val.replace("\\u003c\\/b\\u003e", "");
   val = val.replace("\\u2013", "2013");
   if (val.length > 4 && val.substring(0, 4) == "http") val = "";
   return val;
   }
   function display()
   {
   var sb = '';
   var outputKeywords = displayKeywords;
   for (var i = 0; i < 100; i++) {
   sb += outputKeywords[i];
   sb += '\n';
   }
   jQuery('#output').val(sb);
   }
   window.setInterval(tick, 750);

</script>
<?php
$all_auths = '';
$authors = get_users( array(
    'roles' => 'author'
) );

if ( ! empty( $authors ) ) {
    $all_auths =  '<select name="author_name">';
    foreach ( $authors as $author ) {
        $all_auths = $all_auths.'<option value="' . esc_attr( $author->ID ) . '">' . esc_html( $author->data->display_name ) . '</option>';
    }
    $all_auths = $all_auths. '</select>';
} else {
    $all_auths = $all_auths. '<option value="0">No author found</option>';
}




// 20-05-24 End Code 
	$output = ' <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
	<script type="text/javascript" src="https://cdn.jsdelivr.net/gh/bbbootstrap/libraries@main/jquery.smartWizard.min.js"></script>';

   $output.= ' <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
   <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">';
   
   $output.='<style>	.modal { max-width: unset; } /*#exampleModal { z-index: 9999; }*/ .modal-backdrop { height:unset; } .input-group > .form-control { width: 100%; } #popupcontainer input[type=checkbox] { display:none } #getpopupselected { margin: 20px 0; }
   
   .overlay{
   	// margin :54px;
   		display: none;
   		position: fixed;
   		width: 100%;
   		height: 100%;
   		// top: 209px;
   		left: 0;
   		z-index: 999;
   		background: rgba(255,255,255,0.8) url("'.home_url('/').'wp-content/plugins/ImproveSEO-2.0.11/assets/images/loaderr.gif") center no-repeat; 
   	}
   
   	.overlay_ai_data{
   		// margin :54px;
   			display: none;
   			position: fixed;
   			width: 100%;
   			height: 100%;
   			// top: 209px;
   			left: 0;
   			z-index: 999;
   			background: rgba(255,255,255,0.8) url("'.home_url('/').'wp-content/plugins/ImproveSEO-2.0.11/assets/images/loadingGif.gif") center no-repeat; 
   		}
   
   
   		.overlay_ai_image{
   			// margin :54px;
   				display: none;
   				position: fixed;
   				width: 100%;
   				height: 100%;
   				// top: 209px;
   				left: 0;
   				z-index: 999;
   				background: rgba(255,255,255,0.8) url("'.home_url('/').'wp-content/plugins/ImproveSEO-2.0.11/assets/images/loadingImage.gif") center no-repeat; 
   			}
   			
   </style>';
   
   $output.= '<link href="https://cdn.jsdelivr.net/gh/bbbootstrap/libraries@main/smart_wizard.min.css" rel="stylesheet" type="text/css" /> 
   <link href="https://cdn.jsdelivr.net/gh/bbbootstrap/libraries@main/smart_wizard_theme_dots.min.css" rel="stylesheet" type="text/css" />';
   
   $output.= '<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="false" aria-modal="false">
   <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
	  <div class="modal-content">
		  <div class="modal-header">
			  <h5 class="modal-title" id="exampleModalLabel">Generate AI Content</h5>
			  <button type="button" class="close" data-dismiss="modal" aria-label="Close" id= "butn"><span aria-hidden="false">&times;</span></button>
		  </div>
		  <div class="row" style="padding: 100px 0px">
		  <div class="form-group col-md-1"></div>
			  <div class="form-group col-md-5">
				<div class="form-check-inline">
					<a type="button" id="#exampleModal1" class="btn btn-primary btn-outline-primary" data-toggle="modal" data-target="#exampleModal1" >Create Single Post</a>
				</div>
			  </div>
			 <div class="form-group col-md-1"></div>
				  <div class="form-group col-md-5">
					<a type="button" id="#exampleModal1" class="btn btn-primary btn-outline-primary" data-toggle="modal" data-target="#exampleModal2" >Create Multiple Posts</a>
				  </div>
			  </div>
	  </div>
  </div>
</div>
<div class="modal fade" id="exampleModal1" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
   
   <div id="loadingImage" style="display:none ;" class="overlay">
   
   <!-- <img src="'.home_url('/').'wp-content/plugins/jobseq_jobs_pugin/assets/image/loader.gif" alt="Loading..."> -->
   </div>
   <div id="loadingAIData" style="display:none;" class="overlay_ai_data"></div>
   
   <div id="loadingAIImage" style="display:none;" class="overlay_ai_image"></div>
   
           <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
              <div class="modal-content">
                  <div class="modal-header">
                      <h5 class="modal-title" id="exampleModalLabel">Generate AI Content</h5>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close" id= "butn"><span aria-hidden="true">&times;</span></button>
                  </div>
				  <form id="popup_form" method="post" class="pop_up_form">
                   <div class="modal-body">
                       <div id="smartwizard">
                           <ul style="margin: 0px 30px 5px 30px;">
                               <li style="width: 18%;">
                                   <a href="#step-1" style="text-align: center;">
                                       Step 1<br />
                                       <small>Keyword Input</small>
                                   </a>
                               </li>
                               <li style="width: 18%;">
                                   <a href="#step-2" style="text-align: center;">
                                       Step 2<br />
                                       <small>Content Setting</small>
                                   </a>
                               </li>
                               <li style="width: 18%;">
                                   <a href="#step-3" style="text-align: center;">
                                       Step 3<br />
                                       <small>Add Media</small>
                                   </a>
                               </li>
                               <li style="width: 20%;">
                                   <a href="#step-4" style="text-align: center;">
                                       Step 4<br />
   									<small>Generate AI Content</small>
                                   </a>
                               </li>
   							<li style="width: 20%;">
                                   <a href="#step-5" style="text-align: center;">
                                       Step 5<br />
   									<small>Meta Title & Description</small>
                                   </a>
                               </li>
                           </ul>
                           <div>
                           	
                               <div id="step-1">
                                  <div class="row">
                                   <div class="form-group col-md-1"></div>
                                       <div class="seedform-group col-md-11 desc" id="seed">
                                        	<textarea class="form-control" style="width: 84%; resize:none;" placeholder="Enter Seed Keyword" id="seed_keyword" name="seed_keyword"></textarea>
   											<span id="error_seed_keyword" style="color: red;"></span>
                                               <select id="seed_select" name="seed_options" class="form-control" style="max-width: 84% !important; margin-top: 15px;">
                                                       <option value="">Select Title Type</option>
                                                       <option value="seed_option1">USE KEYWORD AS IS IN TITLE [A.I. will build content]</option>
                                                       <option value="seed_option2">CREATE BEST TITLE FROM KEYWORD [A.I. will choose/build content]</option>
                                                       <option value="seed_option3">CREATE BEST QUESTION FROM KEYWORD [A.I. will choose/build content]</option>  
                                               </select>
   											<span id="error_seed_select" style="color: red;"></span>
                                               <div style="clear: both"> </div>
                                               <div class="content_type">
   											   <div class="form-group col-md-11 " id="seed" style="padding: 0px"> 
   											   <select class="form-control" name="content_type" id="cotnt_type" style="max-width: 92% !important;">
   													   <option value="">Tone of Voice</option>
   													   <option value="friendly">Friendly</option>
   													   <option value="professional">Professional</option>
   													   <option value="informational">Informational</option>
   													   <option value="transactional">Transactional</option>
   													   <option value="inspirational">Inspirational</option>
   													   <option value="neutral">Neutral</option>
   													   <option value="witty">Witty</option>
   													   <option value="casual">Casual</option>
   													   <option value="authoritative">Authoritative</option>
   													   <option value="encouraging">Encouraging</option>
   													   <option value="persuasive">Persuasive</option>
   													   <option value="poetic">Poetic</option>
   											   </select>
   											   <span id="error_cotnt_type" style="color: red;"></span>
   													<div class="form-group col-md-1"></div>
   												</div>
                                               </div>
                                               <div style="clear: both"> </div>
                                               <div id="loader" style="display: none;">Loading...</div>
                                               <div style="clear: both"> </div>
                                               <label id="gettitle"><span><input type=\'checkbox\' id=\'checkbox_need\' /></span><span id="maintitle"> </span><label id="reload"><i class="fa fa-refresh" aria-hidden="true"></i></label></label>
                                               <input type="hidden" name="aigeneratedtitle" id="aigeneratedtitle" />
   
   											<span id="errorContainer" style="color: red;"></span> 
                                       </div>
                                    </div>
   
   									<div class="row">
   										<div class="form-group col-md-1"></div>
   										
   										</div>
                               		</div>
   
                               <div id="step-2">
                                   <div class="row">
                                      
   									<div class="form-group col-md-12">
   									<label for="sel1">Article size</label>
   									<select class="form-control" name="nos_of_words" required  style="max-width: 100% !important;" id="post_size">
   										
   										<option value="600 to 1200 words">Small </option>
   										<option value="1200 to 2400 words">Medium </option>
   										<option value="2400 to 3600 words">Large</option>
   									
   								   </select>
   								</div>
                                   </div>
   								<div class="row">
                                      
   									<div class="form-group col-md-12">
   									
   									<input type="text" id="post_size_select" readonly style="width: 100% !important;" value="600-1200 words">
   									
   								</div>
                                   </div>
   								<div class="row">
   								<div class="form-group col-md-6">
   								<label for="sel1">Point of View</label>
   								<select class="form-control" name="point_of_view" >
   									
   									<option value="none">None
   									</option>
   									<option value="First person singular (I,me,my,mine)">First person singular (I,me,my,mine)
   									</option>
   									<option value="First person plural (we,us,our,ours)">First person plural (we,us,our,ours)</option>
   									<option value="Second Person (you,your,yours)">Second Person (you,your,yours)</option>
   									
   								
   							   </select>
   							</div>
   								<div class="form-group col-md-6">
   								<label for="language">Select Language</label>
   								<select class="form-control" name="content_lang" id="language">
   									<option value="">-Select Language-</option>
   									<option value="US English">English (US)</option>
   									<option value="UK English">English (Uk)</option>
									<option value="German">German (De)</option>
   									
   								  </select>
   							</div>
   							</div>
                                   <div class="row">
										<div class="form-group col-md-12">
											<label for="sel1">Details to Include <a href="#" data-toggle="Please ensure the information you input aligns with the Main Keyword and Title. For example, information about dogs should not be added if you are writing about roofing." title="Please ensure the information you input aligns with the Main Keyword and Title. For example, information about dogs should not be added if you are writing about roofing."><div class="dashicons dashicons-info-outline" aria-hidden="true"><br></div></a></label>
											<textarea class="form-control" id="exampleFormControlTextarea" rows="3" name="details_to_include" onkeypress="return countContent()" OnBlur="LimitText(this,1500,1)"></textarea>
											<span id="countContent"></span>
										</div>
                                   </div>
   
   								<div class="row">
   								<div class="form-group col-md-12">
   									<label for="sel1">Call to action <a href="#" data-toggle="Information" title="Information"><div class="dashicons dashicons-info-outline" aria-hidden="true"><br></div></a></label>
   									<textarea class="form-control" id="call_to_action" rows="3" name="call_to_action" onkeypress="return countContentCallToAction()" OnBlur="LimitText(this,1000,2)"></textarea><span id="countContentCallToAction"></span>	
   								</div>
                                      
                                   </div>
                               </div>
   
                               <div id="step-3" class="">
                                   <div class="row" style="padding-left: 50px; padding-right: 50px">
   									<div class="col-md-6" style="text-align: left;margin-top: 30px;">
   										<input type="radio" name="aiImage" value="AI_image" id="AI_image">
   <label>Generate AI Image Based On Title</label> 
   									</div>
   
   									<div class="col-md-6" style="text-align: left;margin-top: 30px;">
   										<input type="radio" name="aiImage"onclick="SeedShow()" value="Manually_image" id="Manually_image">
   <label>Manually Upload Image</label> 
   									</div>
   
   									<div class="col-md-6" style="text-align: left;margin-top: 30px;margin-bottom: 30px;">
   										<input type="radio" name="aiImage" value="manually_promt_image" id="manually_promt_image"> <label>Generate AI Image - Edit Prompt</label> 
   									</div>
								      
   									<div id="AI_image_div" class="col-md-12" style="display:none;">
   										<div id="ai-image-display"></div>
   										<div class="form-group col-md-12" style="margin: 0 0 0 40%;" id="AIrefreshOption" >
   											<i class="fa fa-refresh" aria-hidden="true" onclick="return refreshAIImage()" style="cursor:pointer;"></i>
   										</div>
   										<input type="hidden" id="AI-Image-uploaded-path" name="AI-Image-uploaded-path">
   									</div>
   
   									<div id="manually_image_div"  class="col-md-12" style="display:none;" >
   										<input type="file" id="upload-image-button" name="Manually_image">
   										<div id="manually-image-display"></div>
   										<input type="hidden" id="manually-image-uploaded-path" name="manually-image-uploaded-path">
   									</div>
   
   									<div id="prompt_image_div"  class="col-md-12" style="display:none;">
   										<div id="ai-with-prompt-image-display" style="margin-bottom: 10px;"></div>
   										
   										<input type="hidden" id="AI-Prompt-Image-uploaded-path" name="AI-Prompt-Image-uploaded-path">
   									</div>
									
   									<div class="form-group col-md-12" id="Prompt_to_create_Dalle_Image" style="margin: 0 0 0 0; display: none;">

   										<div id="manually_promt" style="margin: 0px 40px 0px 40px;">
   											<textarea class="form-control" id="manually_promt_for_image" rows="15" name="manually_promt_for_image" onkeypress="return countContent()" OnBlur="LimitText(this,1000,3)"></textarea>
   											<span id="error_manually_promt_for_image"></span>
   											<input type="button" name="generate_i_image" class="btn btn-primary pull-right"  id="generate_i_image" value="Generate Image" style="margin: 10px 0px 0px 0px;" />
   										</div>
   									</div>
   									</div>
   									
                               </div>
   
                               <div id="step-4" class="">
   								<div class="row">
   									<div class="col-md-12" style="text-align: left; margin-top: 30px; margin-bottom: 30px;">
   									
   									<textarea class="form-control" id="showmydataindivText"  rows="1" style="opacity: 0;"></textarea>
   									
   									<div id="showmydataindiv1" name="showmydataindiv1" style="display: block;max-width: 100%;overflow-y: scroll;"></div>
   									<input type="hidden" name="ai_tittle" id="ai_title" />
   										<div style="text-align: center; display: flex; justify-content: center; gap: 10px; margin: 20px;">
   											<input type="button" value="Approve Content" class="btn btn-primary" onclick="return saveData()" id="generateapi" style="display:none;" style="margin: 0px 0px -37px 0px;">
   											
											<span><input type="checkbox" value="1" id="for_testing_only" name="for_testing_only">
											<label for="for_testing_only">For Testing Only</label></span><br>
						
											<input type="button" name="genaipost" class="btn btn-primary" id="generateapivalue" value="Generate AI Post" />
   											<input type="hidden" name="AI_Title" id="AI_Title">
   											<input type="hidden" name="AI_descreption" id="AI_descreption">
   										</div>
   									</div>
   								</div>
                               </div>
   
   							<div id="step-5" class="">
   								<div class="row">
   									<div class="col-md-12" style="text-align: center; margin-top: 30px; margin-bottom: 30px;">
   									<div class="form-group col-md-12">
   										<label for="sel1">Meta Title:</label>
   										<input type="text" class="form-control" id="meta_title" name="meta_title">
   									</div>
   
   									<div class="form-group col-md-12">
   										<label for="sel1">Meta Description</label>
   										<textarea class="form-control" id="meta_descreption" rows="3" name="meta_descreption"></textarea>
   									</div>
   									<input type="button" value="Submit" onclick="return saveFinalData()">
   
   									</div>
   								</div>
                               </div>
   
                           </div>
                       </div>
                   </div>
                </form>
              </div>
          </div>
      </div>
<div class="modal fade" id="exampleModal2" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="false" aria-modal="false">
   
	<div id="loadingImage" style="display:none ;" class="overlay">
	
	<!-- <img src="'.home_url('/').'wp-content/plugins/jobseq_jobs_pugin/assets/image/loader.gif" alt="Loading..."> -->
	</div>
	<div id="loadingAIData" style="display:none;" class="overlay_ai_data"></div>
	
	<div id="loadingAIImage" style="display:none;" class="overlay_ai_image"></div>
	
			<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
			   <div class="modal-content">
				   <div class="modal-header">
					   <h5 class="modal-title" id="exampleModalLabel">Generate AI Content</h5>
					   <button type="button" class="close" data-dismiss="modal" aria-label="Close" id= "butn"><span aria-hidden="true">&times;</span></button>
				   </div>
				   
				   <form id="pop_up_multi_form" action="multipost_form_submit" method="post" class="pop_up_multi_form">
				   <div class="modal-body" style="height: 500px;overflow: scroll;">
										  <div id="smartwizard_multi">
											  <ul style="margin: 0px 30px 5px 30px;">
												  <li style="width: 18%;">
													  <a href="#steps-1" style="text-align: center;">
														  Step 1<br />
														  <small>Keyword Input</small>
													  </a>
												  </li>
												  <li style="width: 18%;">
													  <a href="#steps-2" style="text-align: center;">
														  Step 2<br />
														  <small>Content Setting</small>
													  </a>
												  </li>
												  <li style="width: 18%;">
													  <a href="#steps-3" style="text-align: center;">
														  Step 3<br />
														  <small>Add Media</small>
													  </a>
												  </li>
												  <li style="width: 20%;">
													  <a href="#steps-4" style="text-align: center;">
														  Step 4<br />
														  <small>Shortcodes</small>
													  </a>
												  </li>
												  <li style="width: 20%;">
													  <a href="#steps-5" style="text-align: center;">
														  Step 5<br />
														  <small>Meta Title and description</small>
													  </a>
												  </li>
												  <li style="width: 20%;">
													  <a href="#steps-6" style="text-align: center;">
														  Step 6<br />
														  <small>Generate AI Content</small>
													  </a>
												  </li>
												  <li style="width: 20%;">
													  <a href="#steps-7" style="text-align: center;">
														  Step 7<br />
														  <small>Save - Publish - Schedule Posts</small>
													  </a>
												  </li>
												  <li style="width: 20%;">
													  <a href="#steps-8" style="text-align: center;">
														  Step 8<br />
														  <small>Assign Authors</small>
													  </a>
												  </li>
												  <li style="width: 20%;">
													  <a href="#steps-9" style="text-align: center;">
														  Step 9<br />
														  <small>Assign Categories</small>
													  </a>
												  </li>
												  <li style="width: 20%;">
													  <a href="#steps-10" style="text-align: center;">
														  Step 10<br />
														  <small>Finalize</small>
													  </a>
												  </li>
											  </ul>
											  <div>
								<div id="steps-1">
								   <div class="row">
				   <div class="form-group col-md-1"></div>
										 <div class="form-group col-md-11 desc" id="select_exisiting">
										 <h2>Keyword List</h2>
										 <div class="form-group">
										 <select id="keyword_list_name" name="keyword_list_name" class="form-control" style="max-width: 84% !important;">
										 <option value="">Select a project</option>
										 <option value="create_new_project">Create New KW List</option>
												 '. $html_key .'
											 </select>
											<span id="error_keyword_list_name" style="color: red;"></span>
										 </div>
										 <div class="form-group" id="keyword_list_container" style="display: none;">
											 <h2>Keywords</h2>
											 <textarea id="keyword_list" name="keyword_list" class="form-control" rows="10" style="max-width: 84% !important;"></textarea>
											 <div id="keyword_count"></div>
										 </div>
												 <div id="create_keyword_container" style="display: none; padding-bottom:15px;">
													 <label> How do you want to create a new list?</label><br>
													 <select id="create_keyword" name="create_keyword" class="form-control" style="max-width: 84% !important;">
														 <option value="none">Select</option>
														 <option value="copy_paste">Copy & Paste</option>
														 <option value="google_suggestion">Generate Google Suggest KW list</option>
														 <option value="ai_create_keyword">AI generated KW list</option>
													 </select>
													 <div id="copy_paste_container" style="width:84%;"></div>
													 <div id="google_suggestion_container" style="width:84%;"></div>
													 <div id="ai_suggestion_container" style="width:84%;"></div>
												 </div>
												 <div id="tone_of_voice" style="padding-bottom:15px;">
													 <select class="form-control" name="content_type"  id="cotnt_type" style="max-width: 84% !important;">
															 <option value="">Tone of Voice</option>
															 <option value="friendly">Friendly</option>
															 <option value="professional">Professional</option>
															 <option value="informational">Informational</option>
															 <option value="transactional">Transactional</option>
															 <option value="inspirational">Inspirational</option>
															 <option value="neutral">Neutral</option>
															 <option value="witty">Witty</option>
															 <option value="casual">Casual</option>
															 <option value="authoritative">Authoritative</option>
															 <option value="encouraging">Encouraging</option>
															 <option value="persuasive">Persuasive</option>
															 <option value="poetic">Poetic</option>
													 </select>
													 <span id="error_cotnt_type" style="color: red;"></span>
													 <div class="form-group col-md-1"></div>
												 </div>
													 <select id="existing_select" name="select_exisiting_options" class="form-control" style="max-width: 84% !important;">
															 <option value="">Select</option>
															 <option value="seed_option1">USE KEYWORD AS IS IN TITLE [A.I. will build content]</option>
															 <option value="seed_option2">CREATE BEST TITLE FROM KEYWORD [A.I. will choose/build content]</option>
															 <option value="seed_option3">CREATE BEST QUESTION FROM KEYWORD [A.I. will choose/build content]</option>
													 </select>
													 <span id="error_existing_select" style="color: red;"></span>
													 <div class="form-group col-md-12" style="padding: 0px !important;">
														 <label for="sel1">Details to Include <a href="#" data-toggle="Please ensure the information you input aligns with the Main Keyword and Title. For example, information about dogs should not be added if you are writing about roofing." title="Please ensure the information you input aligns with the Main Keyword and Title. For example, information about dogs should not be added if you are writing about roofing."><div class="dashicons dashicons-info-outline" aria-hidden="true"><br></div></a></label>
															 <textarea class="form-control" id="exampleFormControlTextarea1" rows="3" name="details_to_include" style=" max-width: 84% !important;" onkeypress="return countContent1()" OnBlur="LimitText1(this,1500,1)"></textarea>
															 <div class="BasicForm__row mb-3" style="max-width:84%; text-align:end; padding:15px 0px;">
															 <input type="button" onclick="return SaveResultsButton();" class="btn btn-outline-primary" value="AI Generate Context Based On Keyword List">
														 <span id="countContent1" class="pull-left"></span>
													</div>
													
													 </div>
													 <div style="margin-top: 20px;" class="show_lists">'. $list .'</div>
													 <textarea placeholder="Context Prompt" style="margin-top: 20px; width: 84%; display: none; resize:none;" class="form-control" name="existing_keyword" /></textarea>
										 </div>
									 </div>
	
										<div class="row">
											<div class="form-group col-md-1"></div>
											
											</div>
										</div>
	
								<div id="steps-2">
									<div class="row">
									   
										<div class="form-group col-md-12">
										<label for="sel1">Article size</label>
										<select class="form-control" name="nos_of_words" required  style="max-width: 100% !important;" id="post_size_bulk">
											
											<option value="600 to 1200 words">Small </option>
											<option value="1200 to 2400 words">Medium </option>
											<option value="2400 to 3600 words">Large</option>
										
									   </select>
									</div>
									</div>
									<div class="row">
									   
										<div class="form-group col-md-12">
										
										<input type="text" id="post_size_select_bulk" readonly style="width: 100% !important;" value="600-1200 words">
										
									</div>
									</div>
									<div class="row">
									<div class="form-group col-md-6">
									<label for="sel1">Point of View</label>
									<select class="form-control" name="point_of_view" >
										
										<option value="none">None
										</option>
										<option value="First person singular (I,me,my,mine)">First person singular (I,me,my,mine)
										</option>
										<option value="First person plural (we,us,our,ours)">First person plural (we,us,our,ours)</option>
										<option value="Second Person (you,your,yours)">Second Person (you,your,yours)</option>
										
									
								   </select>
								</div>
									<div class="form-group col-md-6">
									<label for="language">Select Language</label>
									<select class="form-control" name="content_lang" id="language">
									
										<option value="">-Select Language-</option>
										<option value="US English">English (US)</option>
										<option value="UK English">English (Uk)</option>
										<option value="German">German (De)</option>
										
									  </select>
								</div>
								</div>
	
									<div class="row">
									<div class="form-group col-md-12">
										<label for="sel1">Call to action <a href="#" data-toggle="Information" title="Information"><div class="dashicons dashicons-info-outline" aria-hidden="true"><br></div></a></label>
										<textarea class="form-control" id="call_to_action" rows="3" name="call_to_action" onkeypress="return countContentCallToAction()" OnBlur="LimitText(this,1000,2)"></textarea><span id="countContentCallToAction"></span>	
									</div>
									   
									</div>
								</div>
	
								<div id="steps-3" class="">
								
									<div class="row" style="padding-left: 50px; padding-right: 50px">
									<div class="col-md-6" text-align: left;margin-top: 30px;margin-bottom: 30px;">
										 <input type="radio" name="aiImage" value="AI_image_one" id="AI_image">
		 <label>Generate AI Image Based On Title</label> 
									 </div>
 
									 <div class="col-md-6" text-align: left; margin-top: 30px; margin-bottom: 30px;">
										 <input type="radio" name="aiImage" value="Multiple_images" onclick="SelectexisitingHide();" id="Multiple_images">
		 <label> Multiple Images Upload</label> 
								 </div>
									   
								 <div class="form-group col-md-12" style="margin: 0 0 0 0;">
												 <div id="multiple_image_div" style="display:none;" >
												 <form id="uploadForm">
													<label for="images">Choose Images:</label>
													<input type="file" id="images" name="images[]" multiple>
													<button type="button" id="uploadBtn">Upload</button>
													<div id="preview"></div>
													<div id="response"></div>
													<div id="hiddenInputs"></div>
												</form>
											 </div>
										 </div>
										</div>
								</div>
	
								<div id="steps-4" class="">
									<div class="row" style="padding-left: 50px; padding-right: 50px">
										

										<div class="col-md-12" text-align: left; margin-top: 30px; margin-bottom: 30px;" id="insertShortcodeDropdown">
											'.$AllShortCode.'
										</div>

									</div>
								</div>

								<div id="steps-5" class="">
									<div class="row">
										<div class="col-md-12" style="text-align: center; margin-top: 30px; margin-bottom: 30px;">
										<div class="form-group col-md-12">
											<h3>An SEO optimized meta title and meta description will be automatically AI generated. A preview is not available.</h3>
										</div>		
										</div>
									</div>
								</div>

								<div id="steps-6" class="">
									<div class="row">
										<div class="col-md-12" style="text-align: center; margin-top: 30px; margin-bottom: 30px;">
										<div class="form-group col-md-12">
											<h3>The chosen keyword list has <span id="keywordcounts"></span> keywords, this will take approximately <span id="keywordtime"></span> hours</h3>
										</div>		
										</div>
									</div>
								</div>

								<div id="steps-7" class="">
										<div class="row" style="padding-left: 50px; padding-right: 50px">
											<div class="col-md-12" text-align: left;margin-top: 30px;margin-bottom: 30px;">
												
												<label> <input type="radio" name="schedule_posts" value="draft_posts" id="AI_image"> Save all selected posts in draft mode, so you can review them before publishing</label> 
											</div>

											<div class="col-md-12" text-align: left;margin-top: 30px;margin-bottom: 30px;">
												
												<label> <input type="radio" name="schedule_posts" value="schedule_all_posts" id="AI_image"> Publish all selected posts immediately </label> 
											</div>

											<div class="col-md-12" text-align: left; margin-top: 30px; margin-bottom: 30px;">
												
												<label> <input type="radio" name="schedule_posts" value="schedule_posts_input_wise" id="schedule_posts_input_wise"> Create a publishing schedule for the selected posts (if you don’t want to publish them all at once) </label>
												
											</div>

											<div class="col-md-12" text-align: left; margin-top: 30px; margin-bottom: 30px;" id="number_of_post_schedule_box" style="display:none;">
											<span>
											
											 
											 
											 <Input type="number" name="number_of_post_schedule" id="number_of_post_schedule" Placeholder="Number of post">
											<select name="schedule_frequency"><option value="per_day" selected>Per Day</option><option value="per_week">Per Week</option></select></span>
											<p id="error_number_of_post_schedule" style="color: red;"></p>
											</div>
										</div>
								</div>

								<div id="steps-8" class="">
									<div class="row" style="padding-left: 50px; padding-right: 50px">
										<div class="col-md-6" text-align: left;margin-top: 30px;margin-bottom: 30px;">
											
											<label><input type="radio" name="assigning_authors" value="assigning_authors" id="assigning_authors"> Assign all posts of this project to one author </label> 
											<div id="author_number" style="display:none">
												'.$all_auths.'
												

												

											</div>
										</div>

										<div class="col-md-6" text-align: left; margin-top: 30px; margin-bottom: 30px;">
											
											<label> <input type="radio" name="assigning_authors" value="assigning_multi_authors" id="assigning_multi_authors"> Assign all posts of this project to a number of authors and distribute them evenly </label>
											<div id="authors_number" style="display:none">
												<input type="number" name="authors_number" min="1" max="100">
												
											</div>
										</div>
									</div>
								</div>

								<div id="steps-9" class="">
									 <div class="row">
										 <div class="col-md-12" style="text-align: left; margin-top: 30px; margin-bottom: 30px;">
										 <h3 class="Posting__subheader h1">Chouse Or Create Category</h3>
										 <div class="card mx-auto p-3 p-sm-4">
											<div class="category_improveseo category_improveseo_bulk clearfix card-body text-center p-0">
												<div class="cta-check clearfix d-flex justify-content-center flex-column flex-sm-row  align-items-start align-items-sm-center"> 
												'. $select.'
												</div>
												<div class="add_cat">
													<form  method="post" action="add_category_form" class="form-wrap m-0">
														<div class="input-group mb-4">
															<input type="text" class="form-control" name="cat_name_1" placeholder="Default input" value="" aria-label="default input example" id="add_category_1">
														</div>
														<div class="input-group">
															<input type="button" class="btn-trans btn btn-outline-primary btn-lg px-5 mx-auto" onclick="addcategory()" value="Add Category">
														</div>
													</form>
												</div>
											</div>
										</div>
																				 
										 </div>
									 </div>
								</div>
	
								<div id="steps-10" class="">
									<div class="row">
										<div class="col-md-12" style="text-align: center; margin-top: 30px; margin-bottom: 30px;">
										<div class="form-group col-md-12">
											<label for="sel1">Project Name: </label>
											<input type="text" class="form-control" id="project_name" name="project_name">
										</div>

										<div class="form-group col-md-12">
											<label for="sel1">Email address for notification: </label>
											<input type="text" class="form-control" id="notify_email" name="notify_email">
										</div>

										<input type="submit" value="Submit">
	
										</div>
									</div>
							</div>
	
							</div>
						</div>
					</div>
				 </form>
			   </div>
		   </div>
	   </div>
      <style>
      	.sw-theme-dots>ul.step-anchor:before {
      			top:58px !important;
      	}
      	.selected {
    			background: #b0b0b0 !important;
   		color: #fff !important;
   		font-weight: 600 !important;
   	}
   	.content_type { margin-top: 20px; }
   		#gettitle span { display: flex; align-items: center; }
   	#gettitle { display: flex;
      align-items: center; }
      .resultdata { border: 1px solid #bfbfbf;
      padding: 5px;align-items: center; width: 100%;
      border-radius: 5px;
      background: #d3d3d3;
      margin-right: 10px;
   }
   #reload { border-radius: 50%;
      background: #000; cursor: pointer;
      width: 3.5%; margin: 0;
      text-align: center;
      color: #fff; }
      
      #langerror { color: #f00; }
	  
	
	  .multi-upload-gallery span {
			height: 100px;
			width: 100px;
			background-size: cover;
			background-position: center;
			display:block;
		}
		ul.multi-upload-gallery.ui-sortable {
			grid-template-columns: repeat(6,1fr);
			display: grid;
			column-gap:5px;
		}
		a.multi-upload-gallery-remove {
			color: black;
			position: relative;
			padding-right: -10px !important;
			right: 20px;
			top: -25px;
			font-size: 30px;
		}
   </style>';

   if ((isset($_REQUEST['genaipost'])) && ($_REQUEST['genaipost']=='Generate AI Post')) {
   
   
   	$aigeneratedtitle = $_REQUEST['aigeneratedtitle'];
   	
   	if (empty($aigeneratedtitle)) {
   	    $seed_keyword = $_REQUEST['seed_keyword'];
   	}
   	else
   	{
   	    $seed_keyword = $_REQUEST['aigeneratedtitle'];
   	}
   	
   	$keyword_selection = $_REQUEST['keyword_selection'];
   	$seed_options = $_REQUEST['seed_options'];
   	$nos_of_words = $_REQUEST['nos_of_words'];
   	$content_lang = $_REQUEST['content_lang'];
   	//$shortcode = $_REQUEST['shortcodeoption'];
      
   	createAIpost($seed_keyword, $keyword_selection, $seed_options, $nos_of_words, $content_lang, $shortcode='');
   }
   	  
   	$output.='
   	<script>
   	var ajaxUrl = "'.home_url('/').'wp-admin/admin-ajax.php";
   	</script> ';   
   echo $output;
   }

   // 20-05-24 start Code 
   add_action('wp_ajax_add_category_form' , 'add_category_form' );

   function add_category_form() {

		if(isset($_POST['fData'])){
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
				$response = array('success' => true, 'message' => 'Category added successfully.','result' => $result);
			}
			wp_send_json($response);
		}
	}
	add_action('wp_ajax_refreshCategoryData' , 'refreshCategoryData' );

		function refreshCategoryData($slug) {
			$select = '';
				$args = array("hide_empty" => 0,
				"type"      => "post",
				"orderby"   => "name",
				"order"     => "ASC" );
				$cats = get_categories($args);
				// echo "<pre>";
				// print_r($cats);
				// echo "slug : ".$slug;
				// exit();
				foreach($cats as $category){
					if ($category->name == $slug) {
						$checked = 'checked  onclick="return false"';
						$select.= "<span><input type='checkbox' " . $checked . " value='".$category->term_id."' id='".$category->term_id."' name='cats[]'><label for='".$category->term_id."'>".$category->name."</label></span>";
						return $select;
					} else{
						$checked = '';
					}								
				}
		}

		 // multiple Image Gallery

		 function rudr_multiple_img_upload_metabox( $metaboxes ) {
			 $metaboxes[] = array(
				 'id' => 'my_metabox',
				 'name' => 'Meta Box',
				 'post_type' => array( 'page' ),
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
		 add_filter( 'cmb2_meta_boxes', 'rudr_multiple_img_upload_metabox' );

	
	 // 20-05-24 end Code 
// bulk posting





add_action('wp_ajax_multiPostData','multiPostData');

function multiPostData() {
	
	
	global $wpdb;
	$uploaded_images_count = 0;
	$sequence_manually_images = 0;
	if($_POST['aiImage']=='Multiple_images') {
		$uploaded_images_count = count($_POST['uploaded_images']);
		
	}
	

	$project_name = sanitize_text_field($_POST['project_name']);
	//$number_of_post_schedule = sanitize_text_field($_POST['number_of_post_schedule']);
	$number_of_post_schedule = (!empty($_POST['number_of_post_schedule'])) ? $_POST['number_of_post_schedule'] : "1";
	if($project_name=='') {
		wp_send_json_success(array('status' => 'false',"message"=>"Project name is required."));
	}
	if(!empty($_POST['keyword_list'])) {
		$keyword_lists = explode("\n", $_POST['keyword_list']);

		$notify_email = $_POST['notify_email'];
		$timeTaken = 2*count($keyword_lists); // one post 3 mint
		$linkredirect = home_url('/').'wp-admin/admin.php?page=improveseo_bulkprojects';
		

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
			wp_send_json_success(array('status' => 'false',"message"=>"Project name already exist."));
		} else {
			
			
				$schedule_posts = (!empty($_POST['schedule_posts'])) ? $_POST['schedule_posts'] : "";
				if($schedule_posts=='') {
					wp_send_json_success(array('status' => 'false',"message"=>"Publish - Schedule Posts required. Please check step 7."));
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
			foreach($keyword_lists as $key => $value) {
				if(!empty($value)) {
					
					
					
					$keyword_list_name = (!empty($_POST['keyword_list_name'])) ? $_POST['keyword_list_name'] : ""; 
					$content_type = (!empty($_POST['content_type'])) ? $_POST['content_type'] : ""; 
					$select_exisiting_options = (!empty($_POST['select_exisiting_options'])) ? $_POST['select_exisiting_options'] : "";
					$details_to_include = (!empty($_POST['details_to_include'])) ? $_POST['details_to_include'] :  "";
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
					if(!empty($_POST['cats'])) {
						foreach($_POST['cats'] as $cats) {
							 $category = $category.'||'.$cats;
						}
					} 
	
					$testimonial = '';
					if(!empty($_POST['testimonial_SC'])) {
						foreach($_POST['testimonial_SC'] as $testimonial_SC) {
							 $testimonial = $testimonial.'||'.$testimonial_SC;
						}
					} 

					if(($schedule_posts=='schedule_all_posts')) {
						$published_on = date('Y-m-d');
					} elseif($schedule_posts=='schedule_posts_input_wise') {
						if($schedule_frequency=='per_day') {
							if($number_of_post_schedule>=$number_of_post_schedule_count) {
								$published_on = $pdate;
								$number_of_post_schedule_count++;
							} else {
								$pdate = date('Y-m-d',date(strtotime("+1 day", strtotime($pdate))));
								$number_of_post_schedule_count=2;
								$published_on = $pdate;
							}
						} elseif($schedule_frequency=='per_week') {
							if($number_of_post_schedule>=$number_of_post_schedule_count) {
								$published_on = $pdate;
								$number_of_post_schedule_count++;
							} else {
								$pdate = date('Y-m-d',date(strtotime("+7 day", strtotime($pdate))));
								$number_of_post_schedule_count=2;
								$published_on = $pdate;
							}
						}
					} else {
						$published_on = '';
					}
	
					$Button_SC = (!empty($_POST['Button_SC'])) ? $_POST['Button_SC'] : "";
					$GoogleMap_SC = (!empty($_POST['GoogleMap_SC'])) ? $_POST['GoogleMap_SC'] : "";
					if($authors_number=='') {
						$authors_number = $author_name;
					}
// manuually images
					if($uploaded_images_count>0) {
						if(($uploaded_images_count)==$sequence_manually_images) {
							$sequence_manually_images = 0;
						}
						$ai_image = base64_encode($_POST['uploaded_images'][$sequence_manually_images]);
						
					} else {
						$ai_image = '';
					} 

					
					 if($schedule_posts=='schedule_posts_input_wise') {
						$status = 'Scheduled';
					 } else if($schedule_posts=='draft_posts') {
						$status = 'Draft';
					 } else {
						$status = 'Published';
					 }

					 
					
					

					$insert_bulk_data = array(
						 'bulktask_id' => $lastid,
						 'keyword_list_name' => $keyword_list_name,
						 'keyword_name' => $value,
						 'tone_of_voice' => $content_type,
						 'select_exisiting_options' =>  $select_exisiting_options,
						 'details_to_include' => $details_to_include,
						 'content_lang' => $content_lang,
						 'point_of_view' => $point_of_view,
						 'call_to_action'=>$call_to_action,
						 'nos_of_words'=>$nos_of_words,
						 'aiImage'=>$aiImage,
						 'schedule_posts'=>$schedule_posts,
						 'number_of_post_schedule'=>$number_of_post_schedule,
						 'assigning_authors'=>$assigning_authors,
						 'assigning_authors_value'=>$authors_number,
						 'cats'=>$category,
						 'ai_image'=>$ai_image,
						 'testimonial' => $testimonial,
						 'schedule_frequency' => $schedule_frequency,
						 'Button_SC'=>$Button_SC,
						 'GoogleMap_SC'=>$GoogleMap_SC,
						 'Video_SC'=>$Video_SC,
						 'status' => 'Pending',
						 'state' => $status,
						 'published_on' => $published_on,
						 'created_at' => date('Y-m-d h:m:s'),
						 'updated_at' => date('Y-m-d h:m:s'),
					);
					$wpdb->insert($wpdb->prefix . "improveseo_bulktasksdetails", $insert_bulk_data);

					$json_d = json_encode($insert_bulk_data);
					if(empty($json_d)) {
						my_plugin_log('Post created --> '.$json_d);
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
				$email_content .= "<a href='".$linkredirect."' target='_blank'> Check status </a>" . "\n\n";
	
				$mail_sent = wp_mail($to, $subject, $email_content, $headers);
			}
			//wp_send_json_success(array('status' => 'false',"message"=>'here 1 : '. $wpdb->last_error  ));
			wp_send_json_success(array('status' => 'success',"linkredirect"=>$linkredirect));
		}	
	} else {
		wp_send_json_success(array('status' => 'success',"message"=>"Keywords should not empty."));
	}
}

   // include dirname(__FILE__).'improveSEO-2.0.11/views/test.php';
   add_action('wp_ajax_getaaldata','getaaldata');
   function getaaldata() {
   		$arr=[];
   		wp_parse_str($_POST['value'],$arr);
   		$nos_of_words =  utf8_decode(urldecode($arr['nos_of_words']));
		$seed_keyword = $arr['seed_keyword'];
		$keyword_selection = $arr['keyword_selection'];
		$seed_options = $arr['seed_options'];
		$voice_tone = $arr['content_type'];
		$point_of_view = $arr['point_of_view'];
		$call_to_action = $arr['call_to_action'];
		$for_testing_only = $arr['for_testing_only'];

		$details_to_include = $arr['details_to_include'];
   		$content_lang = $arr['content_lang'];
		if(!empty($arr['maintitlearea'])) {
			$ai_title = $arr['maintitlearea'];
		} else {
			$ai_title = $arr['aigeneratedtitle'];
		}
		 
		if($ai_title==''){
			$search_data = $arr['seed_keyword'];
		}else{
			$search_data = $ai_title ;
		}
   		$content = createAIpost($seed_keyword, $keyword_selection, $seed_options, $nos_of_words, $content_lang, 										 
    	$shortcode='',1,$voice_tone,$point_of_view,$search_data,$call_to_action,$details_to_include,$for_testing_only);	
		

		//$content = convert_emails_to_links($content);
		//$content = convert_urls_to_links($content);

   
		$meta_title = generateMetaTitle($arr['ai_tittle'], $arr['seed_keyword']);
		$meta_descreption = generateMetaDescreption($arr['ai_tittle'], $arr['seed_keyword'],$content);
   		wp_send_json_success(array("search_data"=>$search_data,"content"=>$content,"meta_title"=>$meta_title,"meta_descreption"=>$meta_descreption));
    }
   
   function generateAIMeta() {
   		$aigeneratedtitle = $_REQUEST['aigeneratedtitle'];
      	$seed_keyword = $_REQUEST['seedkeyword'];
      	$out = [];
      	$out['title'] = generateMetaTitle($aigeneratedtitle, $seed_keyword);
   		$out['descreption'] = generateMetaDescreption($aigeneratedtitle, $seed_keyword);
   		wp_send_json_success($out);
      	//die($output);
   }
   
   function generateMetaTitle($aigeneratedtitle, $seed_keyword) {
	$question = "Create an SEO optimized meta title based on the blog post title `".$aigeneratedtitle."` and the keyword `".$seed_keyword."`. max length of title should be 50-60 characters including spaces.
	";
	return ChatGPTCall($question);
   }
   
   function replace_content($content, $remove) {
		// Use preg_replace if you want more complex pattern matching
		return preg_replace('/'.preg_quote($remove, '/').'/', '', $content);
	}
   function generateMetaDescreption($aigeneratedtitle, $seed_keyword,$content='') {
  	 $question = "Create an SEO optimized meta description. max length of description should be 70-80 characters including spaces. Meta description is based on the blog post title `".$aigeneratedtitle."`, the keyword `".$seed_keyword."` and the blog post content i.e. ".$content.".";
  	 return ChatGPTCall($question);
   }
   
   function ChatGPTCall($question) {
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
		
		
		$content =  preg_replace('~^[\'"]?(.*?)[\'"]?$~', '$1', $result['choices'][0]['message']['content']);
		return $content;
   	
   }

   function multi_form_data() {
		$keyword_id = $_REQUEST['project_name'];
		$keyword_list = $_REQUEST['keyword_list'];
		$content_type = $_REQUEST['contenttype'];

		$saved_rand_nos_keywords = get_option('swsaved_random_nosofkeywords');
		if (empty($saved_rand_nos_keywords)) {
			return;
		}
		$saved_rand_nos_keywords = maybe_unserialize($saved_rand_nos_keywords);
		$proj_name = '';
			$get_keyworddata = get_option('swsaved_keywords_with_results_' . $keyword_id);
			$proj_name = isset($get_keyworddata['proj_name']) ? $get_keyworddata['proj_name'] : '';
			$proj_name .= esc_html($proj_name);

			$result = generateTitle_mutli($proj_name, $keyword_list, $content_type);
			$content =  preg_replace('~^[\'"]?(.*?)[\'"]?$~', '$1', $result['choices'][0]['message']['content']);
			
					echo str_replace("'", '`', $content);

			// echo $keyword_list;
			die($output);
   }

   add_action('wp_ajax_multi_form_data', 'multi_form_data');

   function generateTitle_mutli($proj_name, $keyword_list, $content_type) {
		global $wpdb, $user_ID;
	
		// Your OpenAI API key
		$apiKey = get_option('improveseo_chatgpt_api_key');
		
		// The endpoint URL for OpenAI chat completions API (replace with the correct endpoint)
		$apiUrl = 'https://api.openai.com/v1/chat/completions';
	
		if($content_type!='') {
			$content_type = 'voice of content must be '.$content_type;
		}
         $question = 'In a few sentences (max 500 characters not including spaces) explain what the list of keywords provided is about. What is the common thread. Goal is to create a context that is relevant to all keywords in that list. Do not use the word ‘keyword list’ or ‘list’ in the output. Start the output with "The context is..."
		 Keyword List: '. $keyword_list.' '.$content_type;

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
			if(!empty($result['choices'][0]['message']['content'])) {
					return $result;
			} else {
				return "ChatGpt Request Error";
			}
   }

   function getGPTdata() {
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
    generateTitle($seed_type, $seed_keyword, $content_type,$getAudienceData);
      
      die($output);
   }
   add_action('wp_ajax_nopriv_getGPTdata', 'getGPTdata');
   add_action('wp_ajax_getGPTdata', 'getGPTdata');
   
   add_action('wp_ajax_generateAIMeta', 'generateAIMeta');
   










   function getAudienceData($seed_keyword) {
	global $wpdb, $user_ID;
	
	   // Your OpenAI API key
	   $apiKey = get_option('improveseo_chatgpt_api_key');
	   
	   // The endpoint URL for OpenAI chat completions API (replace with the correct endpoint)
	    $apiUrl = 'https://api.openai.com/v1/chat/completions';
 
		$promptForAudienceData = 'Assume someone enters the keyword '.$seed_keyword.' into a search engine. Analyze the following characteristics: 1. [demographic information] 2. [tone preferences] 3. [reading level preference] 4. [emotional needs/pain points]. This information will be used to create content that is specifically appealing to such people. Do not give content recommendations yet. As an output, write just information for characteristics without any explanation or introduction.';
		
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
		  
		   if(!empty($result['choices'][0]['message']['content'])) {
			return $result['choices'][0]['message']['content'];
			}  else {
				return 0;
			}
	}













function generateTitle($seed_type, $seed_keyword, $content_type,$getAudienceData) {
   	global $wpdb, $user_ID;
   
      // Your OpenAI API key
    $apiKey = get_option('improveseo_chatgpt_api_key');
      
      // The endpoint URL for OpenAI chat completions API (replace with the correct endpoint)
    $apiUrl = 'https://api.openai.com/v1/chat/completions';
   
	if($content_type!='') {
			$content_type = 'voice of content must be '.$content_type;
	}

    if ($seed_type=='seed_option2') {
   		$question = 'You are a content creator who creates SEO optimized titles for blog posts. You are provided a word or phrase that is searched by the reader, and the audience data of the reader, including demographic information, tone preferences, reading level preference and emotional needs/pain points. Using this information you should come up with the title that will be engaging and interesting for people who are described in the audience data and search provided word or phrase. In the title do not include emojis or hashtags. Limit characters not including spaces to 80-100. As an output, write just a title without explanation or introduction.
		   Now generate a SEO optimized title based on the following information:
		   Keyword: '.$seed_keyword.'
		   Audience data: {'.$getAudienceData.'}';
   
             // $question = 'Create a compelling seo optimized blog post title based on the keyword `'.$seed_keyword.'` in the form of No Answer. No emojis. No hashtags. Limit characters not including spaces to 80-100. '.$content_type;
    } else if ($seed_type=='seed_option3') {
		$question = 'You are a content creator who creates SEO optimized titles for blog posts. You are provided a word or phrase that is searched by the reader, and the audience data of the reader, including demographic information, tone preferences, reading level preference and emotional needs/pain points. Using this information you should come up with a title that will be engaging and interesting for people who are described in the audience data and search provided word or phrase. Title should be formed as a question. In the title do not include emojis or hashtags. Limit characters not including spaces to 80-100. As an output, write just a title without explanation or introduction. 
			Now generate a SEO optimized title based on the following information:
				Keyword: '.$seed_keyword.'
				Audience data: {'.$getAudienceData.'}';
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
        if(empty($result['choices'][0]['message']['content'])) {
   				return $result;
   		} else {
			if ($seed_type=='seed_option2') {
				$content =  preg_replace('~^[\'"]?(.*?)[\'"]?$~', '$1', $result['choices'][0]['message']['content']);
				
				echo str_replace("'", '`', $content);
			} else if ($seed_type=='seed_option3') {
				$content =  preg_replace('~^[\'"]?(.*?)[\'"]?$~', '$1', $result['choices'][0]['message']['content']);
				
				echo str_replace("'", '`', $content);
			} else {
				echo '';
			}
   		}    
}
   
function removePTags($html) {
	$html = preg_replace('/<p>(\s|&nbsp;)*<\/p>/', '', $html);
	$html = str_replace("\n", '<br>', $html);

	$html = str_replace('<h2>Table of Contents</h2>','<h2 style="margin-top: 35px;">Table of Contents</h2>', $html);
	// Remove any text inside square brackets [example]
    $html = preg_replace('/\[[^\]]*\]/', '', $html);


	$lines = explode("\n", $html);

    foreach ($lines as &$line) {
        // Convert #, ##, ###, #### to H3 (only at the start of the line)
        $line = preg_replace('/^(#{1,4})\s*(.*)/', '<h3>$2</h3>', $line);

        // Convert *, **, ***, **** around words to bold
        $line = preg_replace('/\*{1,4}(.*?)\*{1,4}/', '<strong>$1</strong>', $line);
    }

	// remove () or []

    return implode("<br>", $lines);

	//return lines.join("<br>");

    
    // Remove parentheses but keep the text inside
   // $html = preg_replace('/\(([^)]+)\)/', '$1', $html);
	//return $html;
}


   function createAIpost($seed_keyword, $keyword_selection, $seed_options, $nos_of_words, $content_lang, $shortcode='',$is_single_keyword = '',$voice_tone = '',$point_of_view = '',$title='',$call_to_action = '',$details_to_include = '',$for_testing_only='')
   {
   global $wpdb, $user_ID;
   $prompt_collection = '<b>LSI_Keyords Prompt : <b><br>';
   
      // Your OpenAI API key
      $apiKey = get_option('improveseo_chatgpt_api_key');
      
      // The endpoint URL for OpenAI chat completions API (replace with the correct endpoint)
      $apiUrl = 'https://api.openai.com/v1/chat/completions';
	  $AudienceData = $_COOKIE['AudienceData'];
   // create LSI keywords
   $text_for_lsi = 'As an expert SEO manager, you are tasked with generating 50 Latent Semantic Indexing (LSI) keywords. You are provided a word or phrase that is searched by the reader, and the audience data of the reader, including demographic information, tone preferences, reading level preference and emotional needs/pain points. Using this information you should come up with the LSI keywords that will be engaging and interesting for the reader who is described in the audience data and search provided word or phrase. These keywords should be closely related to the provided main keyword, enhancing content relevance and SEO effectiveness. Please compile the keywords in a comma separated text format without any additional explanations or introductions.
   Main keyword: '.$seed_keyword.'
   Audience data: {'.$AudienceData.'}';
   // Your chat messages
   $messages = [
   	 ['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
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


   $prompt_collection = $prompt_collection.$text_for_lsi.'<br>Roles<br>'.'role : system,content : You are a helpful assistant. Please respond in '.$content_lang.',<br>role: user, content : '.$text_for_lsi.'] <hr>  <b>Facts Prompt :</b> <br>';




   $facts_prompt = 'Generate 5 most interesting and fun facts with specific details about the "Main keyword" for the audience described in the audience data provided below. Each fact should be one short sentence. As an output, write just a bullet point list of facts without explanation or introduction.
Now generate facts.
Main Keyword: '.$seed_keyword.'
Audience data: {'.$AudienceData.'}';
//['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
		$messages = [
			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
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

$prompt_collection = $prompt_collection.$facts_prompt.'<br> Message <br>'.'role = system, content = You are a helpful assistant. Please respond in '.$content_lang.',<br>role = user, content = '.$facts_prompt.'<br>';
   
$prompt_collection = '';

///////  nos_of_words  small///////
   if($nos_of_words=='600 to 1200 words') {
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
			
			Main Content Sections - Create content content sections with subtitles using keywords and their variations at a 1-2% usage rate per 100 words to prevent keyword stuffing. Each section should contain a detailed content, employing NLP and EI for relatability and actionability. Make the content deep so it responds to the emotions and curiosity of the readers. Use storytelling techniques to make your content more relatable and memorable. Share personal anecdotes, case studies, and real-life examples. Stories are a powerful NLP tool to create an emotional connection. Share personal anecdotes or relatable scenarios to make your content more engaging and memorable. Based on the main keyword and the audience data provided to you, you need to understand what are the emotions and intentions user has while searching it. You should understand what deep questions and concerns users want to answer and build your output based on these. Use the following NLP Techniques for creating content:
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

				Main keyword: '.$seed_keyword.'
				Title: "'.$title.'"
				LSI keywords: '.$LSI_Keyords.'
				Tone of voice: '.$voice_tone.' 
				Point of view: '.$point_of_view.'
				Audience data: {'.$AudienceData.'}
				Details to include: '.$details_to_include.' 
				Language: '.$content_lang.'
				Call to action from user: `'.$call_to_action.'`
				Facts to include: {'.$facts_prompt_response.'} Do not print "Main Content Sections" text in output.';




			
			
			// Your chat messages
			$messages = [
				['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
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
			//echo "<h2> Basic Prompt </h2>".$basic_prompt."<br><h2>Response of basic promot</h2><br>";
		 	$basic_prompt_response = $result['choices'][0]['message']['content'];
			//exit();


			$prompt_collection = $prompt_collection.'<br> <b> Basic Prompt (600 to 1200 words)  :</b> <br>'.$basic_prompt.'<br><b>Basic Prompt Response</b><br>'.$basic_prompt_response.'<br>';

			

			$first_call_for_small = 'Now generate the first subtitle content. IMPORTANT: Output should not be more than 200-250 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';
			// Your chat messages
			$messages = [
				['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
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

			$prompt_collection = $prompt_collection.'<br><br><b><h2>Step 1 prompt:<h2></b> <br>'.$first_call_for_small.'<br>'.'<b> Step 1 response</b><br>'.$response_first_call_for_small;

			

			// second call
			$second_call_for_small = 'Now generate the second subtitle content. IMPORTANT: Output should not be more than 200-250 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';
			// Your chat messages
			$messages = [
				['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
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


			$prompt_collection = $prompt_collection.'<br><b>second_call_for_small</b><br>'.$second_call_for_small.'<b>Second call response : </b><br>'.$response_secound_call_for_small;


			///// third call

			
			$third_call_for_small = 'Now generate the third subtitle content. IMPORTANT: Output should not be more than 200-250 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';
			// Your chat messages
			$messages = [
				['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
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

			$prompt_collection = $prompt_collection.'<br><b>third_call_for_small></b><br>'.$third_call_for_small.'<br><b>Response for 3rd call</b><br>'.$response_third_call_for_small;
			/////// 4th call 

			$fourth_call_for_small = 'Now generate the forth subtitle content. IMPORTANT: Output should not be more than 200-250 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';
			// Your chat messages
			$messages = [
				['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
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



			// $prompt_collection = $prompt_collection.'<br>fourth_call_for_small<br>'.$fourth_call_for_small.'<br>'.'[
			// 	[role => system, content => You are a helpful assistant. Please respond in '.$content_lang.'],<br>
			// 	[role => user, content => '.$basic_prompt.'],<br>
			// 	[role => assistant, content => '.$basic_prompt_response.'],<br>
			// 	[role => user, content => '.$first_call_for_small.'],<br>
			// 	[role => assistant, content => '.$response_first_call_for_small.'],<br>
			// 	[role => user, content => '.$second_call_for_small.'],<br>
			// 	[role => assistant, content => '.$response_secound_call_for_small.'],<br>
			// 	[role => user, content => '.$third_call_for_small.'],<br>
			// 	[role => assistant, content => '.$response_third_call_for_small.'],<br>
			// 	[role => user, content => '.$fourth_call_for_small.'],<br>
			// ]';



			// 5th call 


			$fifth_call_for_small = 'Now generate the conclusion content. IMPORTANT: Output should not be more than 100-150 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';
			// Your chat messages
			$messages = [
				['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
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



			$prompt_collection = $prompt_collection.'<br><b>fifth_call_for_small<b><br>'.$fifth_call_for_small.'<br><b>Fifth call response</b>'.$response_fifth_call_for_small;





			// 6th call 


			$sixth_call_for_small = 'Now generate the FAQs content. IMPORTANT: Output should not be more than 100-150 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';
			// Your chat messages
			$messages = [
				['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
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


			$prompt_collection = $prompt_collection.'<br><b>sixth_call_for_small</b><br>'.$sixth_call_for_small.'<br><b>response for 6th call</b>'.$response_sixth_call_for_small;


			// 7th call 


			$seventh_call_for_small = 'Now generate What is next? content. IMPORTANT: Output should not be more than 100-150 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.
			';
			// Your chat messages
			$messages = [
				['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
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
						$prompt_collection = $prompt_collection.'<br><b>seventh_call_for_small</b><br>'.$seventh_call_for_small.'<br><b>response for 7th call</b>'.$response_seventh_call_for_small;

			
						// $prompt_collection = $prompt_collection.'<br>seventh_call_for_small<br>'.$seventh_call_for_small.'<br>'.'[
						// 	[role => system, content => You are a helpful assistant. Please respond in '.$content_lang.'],<br>
						// 	[role => user, content => '.$basic_prompt.'],<br>
						// 	[role => assistant, content => '.$basic_prompt_response.'],<br>
						// 	[role => user, content => '.$first_call_for_small.'],<br>
						// 	[role => assistant, content => '.$response_first_call_for_small.'],<br>
						// 	[role => user, content => '.$second_call_for_small.'],<br>
						// 	[role => assistant, content => '.$response_secound_call_for_small.'],<br>
						// 	[role => user, content => '.$third_call_for_small.'],<br>
						// 	[role => assistant, content => '.$response_third_call_for_small.'],<br>
						// 	[role => user, content => '.$fourth_call_for_small.'],<br>
						// 	[role => user, content => '.$fifth_call_for_small.'],<br>
						// 	[role => assistant, content => '.$response_fifth_call_for_small.'],<br>
						// 	[role => user, content => '.$sixth_call_for_small.'],<br>
						// 	[role => assistant, content => '.$response_sixth_call_for_small.'],<br>
						// 	[role => user, content => '.$seventh_call_for_small.'],<br>
						// ]';


						//echo $prompt_collection;
						//exit('ttttttttt1111111111111');



						// For Testing purposes - Checklist
			if($seed_options=='seed_option3') {
				$title_type = 'Question';
			} else {
				$title_type = 'Regular';
			}
			if($for_testing_only==1) {
				$test_prupose = "<h1>For Testing purposes - Checklist</h1>";
				$test_prupose = $test_prupose."Main keyword: <b>".$seed_keyword."</b></br>
			Title: <b>".$title."</b></br>
			Title type: <b>".$title_type."</b></br>
			Article size/number of words: <b>".$nos_of_words."</b></br>
			LSI keywords: <b>".$LSI_Keyords."</b></br>
			Tone of voice: <b>".$voice_tone."</b></br>
			Point of view: <b>".$point_of_view."</b></br>
			Audience data: {<b>".$AudienceData."</b>}</br>
			Details to include: <b>".$details_to_include."</b></br>
			Language: <b>".$content_lang."</b></br>
			Call to action from user: <b>".$call_to_action."</b></br>
			Facts to include: {<b>".$facts_prompt_response."</b> }</br>
			Words to exclude:  meticulous, meticulously, navigating, complexities, realm, bespoke, tailored, towards, underpins, everchanging, ever-evolving, the world of, not only, seeking more than just, designed to enhance, it’s not merely, our suite, it is advisable, daunting, in the heart of, when it comes to, in the realm of, amongst unlock the secrets, unveil the secrets and robust";
			}

			
			$content_final = '<div class="main-content-section-improveseo">'.$basic_prompt_response.'<div style="margin-bottom: 15px;margin-top: 50px;">'.$response_first_call_for_small.'</div><div style="margin-bottom: 15px;margin-top: 50px;">'.$response_secound_call_for_small.'</div><div style="margin-bottom: 15px;margin-top: 50px;">'.$response_third_call_for_small.'</div><div style="margin-bottom: 15px;margin-top: 50px;">'.$response_fourth_call_for_small.'</div><div style="margin-bottom: 15px;margin-top: 50px;">'.$response_fifth_call_for_small.'</div><div style="margin-bottom: 15px;margin-top: 50px;">'.$response_sixth_call_for_small.'</div><div style="margin-bottom: 15px;margin-top: 50px;">'.$response_seventh_call_for_small.'</div><div style="margin-bottom: 15px;margin-top: 50px;">'.$test_prupose.'</div></div>';
						
			
		}
	elseif($nos_of_words=='1200 to 2400 words') {
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


				Main keyword: '.$seed_keyword.'
				Title: "'.$title.'"
				LSI keywords: '.$LSI_Keyords.'
				Tone of voice: '.$voice_tone.' 
				Point of view: '.$point_of_view.'
				Audience data: {'.$AudienceData.'}
				Details to include: '.$details_to_include.' 
				Language: '.$content_lang.'
				Call to action from user: `'.$call_to_action.'`
				Facts to include: {'.$facts_prompt_response.'} Do not print "Main Content Sections" text in output.';




			
			
			// Your chat messages
			$messages = [
				['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
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



			$prompt_collection = $prompt_collection.'<br><b>Basic prompt for 1200 to 2400 words</b><br>'.$basic_prompt.'<br><b>response for basic prompt</b>'.$basic_prompt_response;

			$first_call_for_medium  = 'Now generate the first subtitle content. IMPORTANT: Output should not be more than 350-400 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';
			// Your chat messages
			$messages = [
				['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
				['role' => 'user', 'content' => $basic_prompt],
				['role' => 'assistant', 'content' => $basic_prompt_response],
				['role' => 'user', 'content' => $first_call_for_medium ]
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
			$response_first_call_for_medium  = $result['choices'][0]['message']['content'];


			$prompt_collection = $prompt_collection.'<br><b>1st call request</b><br>'.$first_call_for_medium.'<br><b>response for 1st response</b>'.$response_first_call_for_medium;

			// second call
			$second_call_for_medium  = 'Now generate the second subtitle content. IMPORTANT: Output should not be more than 350-400 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';
			// Your chat messages
			$messages = [
				['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
				['role' => 'user', 'content' => $basic_prompt],
				['role' => 'assistant', 'content' => $basic_prompt_response],
				['role' => 'user', 'content' => $first_call_for_medium ],
				['role' => 'assistant', 'content' => $response_first_call_for_medium ],
				['role' => 'user', 'content' => $second_call_for_medium ],
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
			$response_secound_call_for_medium  = $result['choices'][0]['message']['content'];


			$prompt_collection = $prompt_collection.'<br><b>2nd call request</b><br>'.$second_call_for_medium.'<br><b>response for 2nd response</b>'.$response_secound_call_for_medium;

			///// third call

			
			$third_call_for_medium  = 'Now generate the third subtitle content. IMPORTANT: Output should not be more than 350-400 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';
			// Your chat messages
			$messages = [
				['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
				['role' => 'user', 'content' => $basic_prompt],
				['role' => 'assistant', 'content' => $basic_prompt_response],
				['role' => 'user', 'content' => $first_call_for_medium ],
				['role' => 'assistant', 'content' => $response_first_call_for_medium ],
				['role' => 'user', 'content' => $second_call_for_medium ],
				['role' => 'assistant', 'content' => $response_secound_call_for_medium ],
				['role' => 'user', 'content' => $third_call_for_medium ],
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
			$response_third_call_for_medium  = $result['choices'][0]['message']['content'];

			$prompt_collection = $prompt_collection.'<br><b>3rd call request</b><br>'.$third_call_for_medium.'<br><b>response for 3rd response</b>'.$response_third_call_for_medium;



			/////// 4th call 

			$fourth_call_for_medium  = 'Now generate the forth subtitle content. IMPORTANT: Output should not be more than 350-400 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';
			// Your chat messages
			$messages = [
				['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
				['role' => 'user', 'content' => $basic_prompt],
				['role' => 'assistant', 'content' => $basic_prompt_response],
				['role' => 'user', 'content' => $first_call_for_medium ],
				['role' => 'assistant', 'content' => $response_first_call_for_medium ],
				['role' => 'user', 'content' => $second_call_for_medium ],
				['role' => 'assistant', 'content' => $response_secound_call_for_medium ],
				['role' => 'user', 'content' => $third_call_for_medium ],
				['role' => 'assistant', 'content' => $response_third_call_for_medium ],
				['role' => 'user', 'content' => $fourth_call_for_medium ],
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
			$response_fourth_call_for_medium  = $result['choices'][0]['message']['content'];

			$prompt_collection = $prompt_collection.'<br><b>4th call request</b><br>'.$fourth_call_for_medium.'<br><b>response for 4th response</b>'.$response_fourth_call_for_medium;





			// 5th call 


			$fifth_call_for_medium  = 'Now generate the conclusion content. IMPORTANT: Output should not be more than 150-200 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';
			// Your chat messages
			$messages = [
				['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
				['role' => 'user', 'content' => $basic_prompt],
				['role' => 'assistant', 'content' => $basic_prompt_response],
				['role' => 'user', 'content' => $first_call_for_medium ],
				['role' => 'assistant', 'content' => $response_first_call_for_medium ],
				['role' => 'user', 'content' => $second_call_for_medium ],
				['role' => 'assistant', 'content' => $response_secound_call_for_medium ],
				['role' => 'user', 'content' => $third_call_for_medium ],
				['role' => 'assistant', 'content' => $response_third_call_for_medium ],
				['role' => 'user', 'content' => $fourth_call_for_medium ],
				['role' => 'assistant', 'content' => $response_fourth_call_for_medium ],
				['role' => 'user', 'content' => $fifth_call_for_medium ],
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
			$response_fifth_call_for_medium  = $result['choices'][0]['message']['content'];


			$prompt_collection = $prompt_collection.'<br><b>5th call request</b><br>'.$fifth_call_for_medium.'<br><b>response for 5th response</b>'.$response_fifth_call_for_medium;






			// 6th call 


			$sixth_call_for_medium  = 'Now generate the FAQs content. IMPORTANT: Output should not be more than 100-150 words. After writing an output check the word count and regenerate if it is not in the rage. Do not include the word count in the output.';
			// Your chat messages
			$messages = [
				['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
				['role' => 'user', 'content' => $basic_prompt],
				['role' => 'assistant', 'content' => $basic_prompt_response],
				['role' => 'user', 'content' => $first_call_for_medium ],
				['role' => 'assistant', 'content' => $response_first_call_for_medium ],
				['role' => 'user', 'content' => $second_call_for_medium ],
				['role' => 'assistant', 'content' => $response_secound_call_for_medium ],
				['role' => 'user', 'content' => $third_call_for_medium ],
				['role' => 'assistant', 'content' => $response_third_call_for_medium ],
				['role' => 'user', 'content' => $fourth_call_for_medium ],
				['role' => 'assistant', 'content' => $response_fourth_call_for_medium ],
				['role' => 'user', 'content' => $fifth_call_for_medium ],
				['role' => 'assistant', 'content' => $response_fifth_call_for_medium ],
				['role' => 'user', 'content' => $sixth_call_for_medium ],
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
			$response_sixth_call_for_medium  = $result['choices'][0]['message']['content'];

			$prompt_collection = $prompt_collection.'<br><b>6th call request</b><br>'.$sixth_call_for_medium.'<br><b>response for 6th response</b>'.$response_sixth_call_for_medium;


			// 7th call 


			$seventh_call_for_medium  = 'Now generate What is next? content. IMPORTANT: Output should not be more than 150-200 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';
			// Your chat messages
			$messages = [
				['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
				['role' => 'user', 'content' => $basic_prompt],
				['role' => 'assistant', 'content' => $basic_prompt_response],
				['role' => 'user', 'content' => $first_call_for_medium ],
				['role' => 'assistant', 'content' => $response_first_call_for_medium ],
				['role' => 'user', 'content' => $second_call_for_medium ],
				['role' => 'assistant', 'content' => $response_secound_call_for_medium ],
				['role' => 'user', 'content' => $third_call_for_medium ],
				['role' => 'assistant', 'content' => $response_third_call_for_medium ],
				['role' => 'user', 'content' => $fourth_call_for_medium ],
				['role' => 'assistant', 'content' => $response_fourth_call_for_medium ],
				['role' => 'user', 'content' => $fifth_call_for_medium ],
				['role' => 'assistant', 'content' => $response_fifth_call_for_medium ],
				['role' => 'user', 'content' => $sixth_call_for_medium ],
				['role' => 'assistant', 'content' => $response_sixth_call_for_medium ],
				['role' => 'user', 'content' => $seventh_call_for_medium ],
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
			$response_seventh_call_for_medium  = $result['choices'][0]['message']['content'];

			$prompt_collection = $prompt_collection.'<br><b>7th call request</b><br>'.$seventh_call_for_medium.'<br><b>response for 7th response</b>'.$response_seventh_call_for_medium;

			/*return array("first_subtitle"=>$response_first_call_for_medium ,
						"second_subtitle"=>$response_secound_call_for_medium ,
						"third_subtitle"=>$response_third_call_for_medium ,
						"fourth_subtitle"=>$response_fourth_call_for_medium ,
						"conclusion"=>$response_fifth_call_for_medium ,
						"faq"=>$response_sixth_call_for_medium ,
						"whats_next"=>$response_seventh_call_for_medium );*/
			//$content_final = $basic_prompt_response.'<br><br>'.$response_first_call_for_medium .'<br><br>'.$response_secound_call_for_medium .'<br><br>'.$response_third_call_for_medium .'<br><br>'.$response_fourth_call_for_medium .'<br><br>'.$response_fifth_call_for_medium .'<br><br>'.$response_sixth_call_for_medium .'<br><br>'.$response_seventh_call_for_medium ;


			// For Testing purposes - Checklist
			if($seed_options=='seed_option3') {
				$title_type = 'Question';
			} else {
				$title_type = 'Regular';
			}

			if($for_testing_only==1) {
				$test_prupose = "<h1>For Testing purposes - Checklist</h1>";
				$test_prupose = $test_prupose."Main keyword: <b>".$seed_keyword."</b></br>
			Title: <b>".$title."</b></br>
			Title type: <b>".$title_type."</b></br>
			Article size/number of words: <b>".$nos_of_words."</b></br>
			LSI keywords: <b>".$LSI_Keyords."</b></br>
			Tone of voice: <b>".$voice_tone."</b></br>
			Point of view: <b>".$point_of_view."</b></br>
			Audience data: {<b>".$AudienceData."</b>}</br>
			Details to include: <b>".$details_to_include."</b></br>
			Language: <b>".$content_lang."</b></br>
			Call to action from user: <b>".$call_to_action."</b></br>
			Facts to include: {<b>".$facts_prompt_response."</b> }</br>
			Words to exclude:  meticulous, meticulously, navigating, complexities, realm, bespoke, tailored, towards, underpins, everchanging, ever-evolving, the world of, not only, seeking more than just, designed to enhance, it’s not merely, our suite, it is advisable, daunting, in the heart of, when it comes to, in the realm of, amongst unlock the secrets, unveil the secrets and robust";
			}


			$content_final = '<div class="main-content-section-improveseo">'.$basic_prompt_response.'<div style="margin-bottom: 15px;margin-top: 50px;">'.$response_first_call_for_medium.'</div><div style="margin-bottom: 15px;margin-top: 50px;">'.$response_secound_call_for_medium.'</div><div style="margin-bottom: 15px;margin-top: 50px;">'.$response_third_call_for_medium.'</div><div style="margin-bottom: 15px;margin-top: 50px;">'.$response_fourth_call_for_medium.'</div><div style="margin-bottom: 15px;margin-top: 50px;">'.$response_fifth_call_for_medium.'</div><div style="margin-bottom: 15px;margin-top: 50px;">'.$response_sixth_call_for_medium.'</div><div style="margin-bottom: 15px;margin-top: 50px;">'.$response_seventh_call_for_medium.'</div><div style="margin-bottom: 15px;margin-top: 50px;">'.$test_prupose.'</div></div>';



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
				Main keyword: '.$seed_keyword.'
				Title: "'.$title.'"
				LSI keywords: '.$LSI_Keyords.'
				Tone of voice: '.$voice_tone.' 
				Point of view: '.$point_of_view.'
				Audience data: {'.$AudienceData.'}
				Details to include: '.$details_to_include.' 
				Language: '.$content_lang.'
				Call to action from user: `'.$call_to_action.'`
				Facts to include: {'.$facts_prompt_response.'} Do not print "Main Content Sections" text in output.';




			
			
			// Your chat messages
			$messages = [
				['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
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

			$prompt_collection = $prompt_collection.'<br><b>Basic prompt for Large</b><br>'.$basic_prompt.'<br><b>response for basic prompt</b>'.$basic_prompt_response;



			$first_call_for_large = 'Now generate the first subtitle content. IMPORTANT: Output should not be more than 450-600 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';
			// Your chat messages
			$messages = [
				['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
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

			$prompt_collection = $prompt_collection.'<br><b>1st call request</b><br>'.$first_call_for_large.'<br><b>response for 1st response</b>'.$response_first_call_for_large;


			// second call
			$second_call_for_large = 'Now generate the second subtitle content. IMPORTANT: Output should not be more than 450-600 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';
			// Your chat messages
			$messages = [
				['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
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


			$prompt_collection = $prompt_collection.'<br><b>2nd call request</b><br>'.$second_call_for_large.'<br><b>response for 2nd response</b>'.$response_secound_call_for_large;

			///// third call

			
			$third_call_for_large = 'Now generate the third subtitle content. IMPORTANT: Output should not be more than 450-600 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';
			// Your chat messages
			$messages = [
				['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
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

			$prompt_collection = $prompt_collection.'<br><b>3rd call request</b><br>'.$third_call_for_large.'<br><b>response for 3rd response</b>'.$response_third_call_for_large;


			/////// 4th call 

			$fourth_call_for_large = 'Now generate the forth subtitle content. IMPORTANT: Output should not be more than 450-600 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';
			// Your chat messages
			$messages = [
				['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
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

			$prompt_collection = $prompt_collection.'<br><b>4th call request</b><br>'.$fourth_call_for_large.'<br><b>response for 4th response</b>'.$response_fourth_call_for_large;





			// 5th call 


			$fifth_call_for_large = 'Now generate the fifth subtitle content. IMPORTANT: Output should not be more than 450-600 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';
			// Your chat messages
			$messages = [
				['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
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

			$prompt_collection = $prompt_collection.'<br><b>5th call request</b><br>'.$fifth_call_for_large.'<br><b>response for 5th response</b>'.$response_fifth_call_for_large;








			// 6th call 


			$sixth_call_for_large = 'Now generate the conclusion content. IMPORTANT: Output should not be more than 150-200 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';
			// Your chat messages
			$messages = [
				['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
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
				['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
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
				['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],
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





			/*return array("first_subtitle"=>$response_first_call_for_large,
						"second_subtitle"=>$response_secound_call_for_large,
						"third_subtitle"=>$response_third_call_for_large,
						"fourth_subtitle"=>$response_fourth_call_for_large,
						"conclusion"=>$response_fifth_call_for_large,
						"faq"=>$response_sixth_call_for_large,
						"whats_next"=>$response_seventh_call_for_large);*/
			//$content_final = $basic_prompt_response.'<br><br>'.$response_first_call_for_large.'<br><br>'.$response_secound_call_for_large.'<br><br>'.$response_third_call_for_large.'<br><br>'.$response_fourth_call_for_large.'<br><br>'.$response_fifth_call_for_large.'<br><br>'.$response_sixth_call_for_large.'<br><br>'.$response_seventh_call_for_large.'<br><br>'.$response_eigth_call_for_large;



			// For Testing purposes - Checklist
			if($seed_options=='seed_option3') {
				$title_type = 'Question';
			} else {
				$title_type = 'Regular';
			}
			if($for_testing_only==1) {
				$test_prupose = "<h1>For Testing purposes - Checklist</h1>";
				$test_prupose = $test_prupose."Main keyword: <b>".$seed_keyword."</b></br>
			Title: <b>".$title."</b></br>
			Title type: <b>".$title_type."</b></br>
			Article size/number of words: <b>".$nos_of_words."</b></br>
			LSI keywords: <b>".$LSI_Keyords."</b></br>
			Tone of voice: <b>".$voice_tone."</b></br>
			Point of view: <b>".$point_of_view."</b></br>
			Audience data: {<b>".$AudienceData."</b>}</br>
			Details to include: <b>".$details_to_include."</b></br>
			Language: <b>".$content_lang."</b></br>
			Call to action from user: <b>".$call_to_action."</b></br>
			Facts to include: {<b>".$facts_prompt_response."</b> }</br>
			Words to exclude:  meticulous, meticulously, navigating, complexities, realm, bespoke, tailored, towards, underpins, everchanging, ever-evolving, the world of, not only, seeking more than just, designed to enhance, it’s not merely, our suite, it is advisable, daunting, in the heart of, when it comes to, in the realm of, amongst unlock the secrets, unveil the secrets and robust"; 
			}



			$content_final = '<div class="main-content-section-improveseo">'.$basic_prompt_response.'<div style="margin-bottom: 15px;margin-top: 50px;">'.$response_first_call_for_large.'</div><div style="margin-bottom: 15px;margin-top: 50px;">'.$response_secound_call_for_large.'</div><div style="margin-bottom: 15px;margin-top: 50px;">'.$response_third_call_for_large.'</div><div style="margin-bottom: 15px;margin-top: 50px;">'.$response_fourth_call_for_large.'</div><div style="margin-bottom: 15px;margin-top: 50px;">'.$response_fifth_call_for_large.'</div><div style="margin-bottom: 15px;margin-top: 50px;">'.$response_sixth_call_for_large.'</div><div style="margin-bottom: 15px;margin-top: 50px;">'.$response_seventh_call_for_large.'</div><div style="margin-bottom: 15px;margin-top: 50px;">'.$response_eigth_call_for_large.'</div><div style="margin-bottom: 15px;margin-top: 50px;">'.$test_prupose.'</div></div>';

			$prompt_collection = $prompt_collection.'<br><b>6th call request</b><br>'.$sixth_call_for_large.'<br><b>response for 6th response</b>'.$response_sixth_call_for_large;

			$prompt_collection = $prompt_collection.'<br><b>7th call request</b><br>'.$seventh_call_for_large.'<br><b>response for 7th response</b>'.$response_seventh_call_for_large;

			$prompt_collection = $prompt_collection.'<br><b>8th call request</b><br>'.$eigth_call_for_large.'<br><b>response for 8th response</b>'.$response_eigth_call_for_large;




   }
     
   //    if($details_to_include == ''){
   // 	$details_to_include = '';
   //    }else{
   // 	$details_to_include =  '11. Call To Action:'.$details_to_include.' ' ;
   //    }
   
          // 'write a '.$nos_of_words.' blog post about '.$seed_keyword ;
          /* $call_to_actions = '11. Call To Action:'.$call_to_action.'';
   
   
   
   	$question = 'Pretend you are Malcolm Gladwell. Using the provided outline, write a '.$nos_of_words.' blog post about '.$seed_keyword.'
   	Your task is to: Aim the piece at a broad audience.		
   	Use a mix of short, medium, and long sentences to create a human-like rhythm in the text.
   	Incorporate humor, where appropriate, to make the piece more enjoyable to read.
   	Include an analogy to explain any complex concepts or ideas.
   	Use a '.$voice_tone.' tone to make the piece more relatable.
   	Consider the perspectives of both an expert and a beginner.
   	Write from the following point of view: '.$point_of_view.'
   	Incorporate the provided "LSI keywords" naturally throughout the content. LSI keywords: '.$LSI_Keyords.'
   	
   	Consider the context that is provided to make the blog post more relevant to the main keyword '.$seed_keyword.'
   	
   	Context:'.$details_to_include.'.
   
   	Use the iterative approach to improve upon your initial draft. After each draft, critique your work, give it a score out of 10, and if the score is below 9, improve upon the previous draft. Repeat this process until you achieve a score of 9 or 10. When doing this, review and edit your work to remove any grammatical errors, unnecessary information, and superfluous sentences. Don`t provide output of this critique, this is only for you to analyze internally.
   	
   	The blog post should contain the following:
   	
   	   - Provide a concise preview of the content`s value and insights and write an engaging and informative introduction, incorporating the primary keyword within the first 100-150 words, applying NLP and EI principles for emotional resonance. Don’t create a header for this section, only provide the paragraph.
   	
   	2. <h2>Table of Contents:</h2>
   	   - Outline main content areas with H2 section subheaders, non-bolded and in medium gray, for SEO and easy navigation. Please make sure that H2 section subheaders are not bold. Don’t include a header for the following section: ‘Introduction’		
   	
   	3. <h2>Main Content Sections:</h2>
   	   - Create H2 sections with titles using keywords and their variations at a 1-2% usage rate per 100 words to prevent keyword stuffing. Each section should contain 3-5 sentences of detailed content, employing NLP and EI for relatability and actionability. Please make sure that the content for each section is at least 150 words.
   	
   	4. <h2>Conclusion:</h2>
   	   - Summarize key insights in an H2 header, medium gray, encouraging further exploration or engagement.
   	
   	5.  <h2>Frequently Asked Questions</h2>
   		-  Answer common questions about '.$seed_keyword.' with clear, informative, non-bolded answers that empathize with the reader`s concerns. Output should just be the questions and answers, don’t write ‘Q’ in front of the questions and ‘A’ in front of the answers.
   	6. <h2>What’s Next? </h2>
   		-Write a short paragraph inviting the reader to take action in the explained way, including links or phone numbers if provided.
          '.$call_to_actions;
   	
          // Your chat messages
          $messages = [
              // ['role' => 'system', 'content' => 'You are a helpful assistant.'],
              ['role' => 'user', 'content' => $question]
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
          
          // print_r($result);
          
          // echo 'Generated Text in French: ' . $result['choices'][0]['message']['content'];
          // die();
          
          // print_r($shortcode);
          // $scode = '';
          // for ($s=0; $s<=count($shortcode)-1; $s++)
          // {
          //     $scode.= $shortcode[$s]."<br/>";
          // }
          // die();
          $content_final = $result['choices'][0]['message']['content'];
          
          */
          
          //echo $prompt_collection;
		  //exit();


          //if($is_single_keyword=='') {
		  $content_final = $content_final.'<style> p {padding-bottom: 2px !important;} </style>';
   		$content = array('title'=>$seed_keyword, 'content'=>$content_final, 'post_type'=>'post');
          $options = array("max_posts"=>"1");
   		/*$wpdb->insert($wpdb->prefix . "improveseo_tasks", array(
      				
   			'name' => $seed_keyword,
   			'content' => base64_encode(json_encode($content)),
   			'options' => base64_encode(json_encode($options)),
   			'iteration' => 0,
   			'spintax_iterations' => 1,
   			'max_iterations' => 1,
   			'state' => "Published",
   			'options' => base64_encode(json_encode($options)),
			//'prompt_collection' => base64_encode($prompt_collection),
   			'created_at' => date('Y-m-d h:m:s')
   		));*/
		   $inserted_id = 2;//$wpdb->insert_id;

		   
	   
		   //$upload_dir = wp_upload_dir(); // Get WordPress upload directory
		   $dynamic_path = IMPROVESEO_ROOT . '/storage/'; // Dynamic path
		   // Ensure the directory exists, create if it doesn't
		    if (!file_exists($dynamic_path)) {
				wp_mkdir_p($dynamic_path);
			}
	
			// Define file path
			$file_path = $dynamic_path.$inserted_id.date('Y-m-d-H-i-s').'.html'; // File name with timestamp


			ob_start();
			?>
		
			<html>
			<head>
				<title>Used Prompt for <?php echo $seed_keyword; ?></title>
				<meta charset="UTF-8">
			</head>
			<body>
				<?php echo $prompt_collection; ?>
			</body>
			</html>
		
			<?php
			// Get the content
			$html_content = ob_get_clean();
		
			// Save to file
			if (file_put_contents($file_path, $html_content) !== false) {
				//echo '<div class="updated"><p>HTML file has been saved to: ' . esc_html($file_path) . '</p></div>';
			} else {
				//echo '<div class="error"><p>Failed to save HTML file to: ' . esc_html($file_path) . '</p></div>';
			}




   //	} else {



  


	$content_final = convert_emails_to_links($content_final);
	$content_final = convert_urls_to_links($content_final);

    $content_final = htmlentities($content_final, null, 'utf-8');
	$content_final = str_replace("&nbsp;", "", $content_final);
	$content_final = str_replace("<p>&nbsp;</p>", "", $content_final);
	$content_final = str_replace("<p> </p>", "", $content_final);
	$content_final = str_replace("<p></p>", "", $content_final);
	
$content_final = html_entity_decode($content_final);

$content_final = replace_content($content_final,'<h2>Main Content Sections</h2>');

$content_final = replace_content($content_final,'<p>—</p>');

$content_final = removePTags($content_final);
   		return $content_final;
   	//}
         
}




          // 	$linkredirect = home_url('/').'wp-admin/admin.php?page=improveseo_projects';
          // 	wp_redirect( $linkredirect, 301 );



















function ImageBasicPrompt($title) {
	$apiUrl = 'https://api.openai.com/v1/chat/completions';
	$apiKey = get_option('improveseo_chatgpt_api_key');
	$imageBasicPrompt = "‘I need help creating a Dalle image prompt for an article based on the title: ".$title.". Provide the description without any further explanation. Don not include the word 'prompt'.";
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









// multiple images
function my_plugin_handle_upload() {
    //check_ajax_referer('my-plugin-nonce', '_wpnonce');

    if (!empty($_FILES['images']['name'][0])) {
        $uploadDir = wp_upload_dir();
        $uploadPath = $uploadDir['path'];
        $uploadedFiles = [];
        $errors = [];

        foreach ($_FILES['images']['name'] as $key => $name) {
            $tmpName = $_FILES['images']['tmp_name'][$key];
            $fileType = mime_content_type($tmpName);

            if (in_array($fileType, ['image/jpeg', 'image/png', 'image/gif'])) {
                $filename = uniqid() . '_' . sanitize_file_name($name);
                $filePath = $uploadPath . '/' . $filename;

                if (move_uploaded_file($tmpName, $filePath)) {
                    $uploadedFiles[] = $uploadDir['url'] . '/' . $filename;
                } else {
                    $errors[] = "Failed to upload $name.";
                }
            } else {
                $errors[] = "$name is not a valid image type.";
            }
        }

        if (!empty($uploadedFiles)) {
            wp_send_json_success($uploadedFiles);
        } else {
            wp_send_json_error($errors);
        }
    } else {
        wp_send_json_error(['No files selected.']);
    }
}
add_action('wp_ajax_my_plugin_upload', 'my_plugin_handle_upload');
add_action('wp_ajax_nopriv_my_plugin_upload', 'my_plugin_handle_upload');



		
   
   // AJAX handler for image upload
   add_action('wp_ajax_upload_image', 'upload_image_callback');
   function upload_image_callback() {
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
   
   add_action('wp_ajax_getPromptForImages','getPromptForImages');
   function getPromptForImages() {
	if (!empty($_POST['title'])) {
		$title = $_POST['title'];
		$basicImagePromptResponse = ImageBasicPrompt($title);
		wp_send_json_success($basicImagePromptResponse);
	}
   }

   // AJAX handler for image upload
   add_action('wp_ajax_fetch_AI_image', 'fetch_AI_image_callback');
   function fetch_AI_image_callback() {
   if (!empty($_POST['title'])) {
   	$title = $_POST['title'];
   	if(!empty($_POST['noedit'])) {
   		$imgPrompt = $title;
		$seed_title = $_POST['seed_title'];
   	} else {
		$seed_title = $title;
		//fetch_AI_image
		$basicImagePromptResponse = ImageBasicPrompt($title);
		
		/*$AudienceData = $_COOKIE['AudienceData'];*/
		$imgPrompt = 'You should come up with the cover image for an article. The image should be a very high quality shooting from a distance, high detail, photorealistic, image resolution is  800 pixels, cinematic. Do not include any text on the image. Using the following information generate an image.  '.$basicImagePromptResponse;
   		//$imgPrompt = "Very high quality shooting from a distance, high detail, photorealistic, image resolution 2146 pixels, cinematic. The theme is ‘".$title."’";
   	}

	// add new prompt
   
   
      	$dateTimeDefault = date('YmdHis');
      	$imagename = 'ai_image_'.$dateTimeDefault;
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
                    $file_name = $file_name.'_'.rand();
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


   /*	if(!empty($result['data'][0]['url'])) {
   		$url = $result['data'][0]['url'];
   		
   	
   
   
   		////////////////////////////////////////////////////////////////
   
   
   		$upload_dir = wp_upload_dir(); // Get the WordPress upload directory
   
   		$image_data = file_get_contents($url); // Fetch image data from URL
   
   		if ($image_data !== false) {
   			// Generate a unique file name for the image
			$firstSixChars = substr($title, 0, 10);
			$firstSixChars = substr($title, 0, 10);
			$firstSixChars = $firstSixChars .'_'. wp_rand(1,76000);
   			$file_name = wp_unique_filename($upload_dir['path'],str_replace(" ","_",$firstSixChars));
   	
   			// Save the image to the uploads directory
   			$file_path = $upload_dir['path'] . '/' . $file_name;
   			if (file_put_contents($file_path, $image_data) !== false) {
   				// Image saved successfully, you can now return the image URL
				

				// Construct the image URL relative to the uploads directory
				$image_url = $upload_dir['url'] . '/' . $file_name;
				if (extension_loaded('gd')) {
					$png_image = imagecreatefrompng($file_path);
            
					// Path for the JPEG image
					$jpeg_file_name = pathinfo($file_name, PATHINFO_FILENAME) . '.webp';
					$jpeg_file_path = $upload_dir['path'] . '/' . $jpeg_file_name;

					// Convert and save as JPEG
					imagejpeg($png_image, $jpeg_file_path, 90); // 90 = Quality (Adjust if needed)

					// Free memory
					imagedestroy($png_image);

					// Delete the original PNG file
					unlink($file_path);
					$image_url = $upload_dir['url'] . '/' . $jpeg_file_name;
				} 
				
				//return $image_url;
				wp_send_json_success(array($image_url));
				
   			} else {
   				// Error saving the image file
   				wp_send_json_error('Error saving the image file.');
   			}
   		} else {
   			// Error fetching image data from URL
   			wp_send_json_error('Error fetching image data from URL.');
   		}
   
   		///////////////////////////////////////////////////////////////
   		// if (empty($ans1)) { fetch_AI_image_callback($title); 
   		// } else {
   		// 	$img = '.../wp-content/plugins/ImproveSEO-2.0.11/assets/images/AI/'.$imagename.'.jpg';
   		// 	file_put_contents($img, file_get_contents($ans1));
   			
   		// 	$fullname = home_url('/').'wp-content/plugins/ImproveSEO-2.0.11/assets/images/AI/'.$imagename.'.jpg';
   		// 	wp_send_json(array('status' => 'success' , 'url' => $fullname));
   			
   		// }
   	} else {
   		wp_send_json_error($result);
   		//fetch_AI_image_callback($title);
   	}*/
   }
   wp_die();
   }
   /***************************************************/
   /***************** Generate AI Post ****************/
   /*********************** END ***********************/
   /***************************************************/
   

   







   
   class WC_Testimonial {
   
   function __construct() {
   	
   	//add_action( 'admin_menu', 'custom_address_option_on_settings_tab' );
   
          add_action('admin_bar_menu', [ $this, 'improve_seo_admin_top_bar_option' ], 2000);
   	
   	add_action( 'admin_enqueue_scripts', array($this , 'load_admin_files'), 30 );
   
   	add_action('wp_ajax_wt_save_form_fields_for_testimonials' , array($this , 'wt_save_form_fields_for_testimonials'));
   	add_action('wp_ajax_wt_save_form_fields_for_googlemaps' , array($this , 'wt_save_form_fields_for_googlemaps'));
   	add_action('wp_ajax_wt_save_form_fields_for_buttons' , array($this , 'wt_save_form_fields_for_buttons'));
   	add_action('wp_ajax_wt_save_form_fields_for_videos' , array($this , 'wt_save_form_fields_for_videos'));
   	
   	add_action('wp_ajax_delete_selected_data' , array($this , 'delete_selected_data'));
   	add_action('wp_ajax_kwdelete_selected_data_for_keyword' , array($this , 'kwdelete_selected_data_for_keyword'));
   	add_action('wp_ajax_kwdownload_selected_data_for_keyword' , array($this , 'kwdownload_selected_data_for_keyword'));
   	
   	
   	add_action('wp_ajax_edit_selected_data' , array($this , 'edit_selected_data'));
   	add_action('wp_ajax_sw_saved_search_results_keyword' , array($this , 'sw_saved_search_results_keyword'));

   	//shortcode for things testimonials / MAPS / Buttons
   	add_shortcode('improveseo_testimonial' , array($this , 'testimonial_callback'));
   	add_shortcode('improveseo_googlemaps' , array($this , 'maps_callback'));
   	add_shortcode('improveseo_buttons' , array($this , 'button_callback'));
   	add_shortcode('improveseo_video' , array($this , 'video_callback'));
   
   }

   
   
      /**
       * admin bar.
       * @return void.
       */
      public function improve_seo_admin_top_bar_option() {
          global $wp_admin_bar;
          $menu_id = 'improveseo_dashboard';
          $wp_admin_bar->add_menu(array(
              'id' => $menu_id,
              'title' => __('Improve SEO', 'improve-seo'),
              'href' => admin_url() .'/admin.php?page=improveseo_dashboard',
          ));
      }
   
   /****=====Download the data against ID of keyword from admin side====***/	
      function kwdownload_selected_data_for_keyword(){
   	
   	$keywordproj_id = isset($_REQUEST['kw_rand_id']) ? $_REQUEST['kw_rand_id'] : '';
   	if(empty($keywordproj_id)){
   	    return;
   	}
          $keyword_data = get_option('swsaved_keywords_with_results_'.$keywordproj_id);
          $proj_name      = $keyword_data['proj_name'];
          $proj_content   = $keyword_data['search_results'];
          
          $proj_name =  str_replace(' ', '-', $proj_name);
          $args = array(
                  'status'    => 'success',
                  'proj_name' => $proj_name,
                  'proj_content' => $proj_content,
              );
          wp_send_json($args);
          die(0);
      }
   
   
   
   /****=====SAving the data found with keywords====***/	
   function sw_saved_search_results_keyword(){
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
		wp_send_json_success( array(
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
   
   function general_admin_notice(){
   
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
   function maps_callback( $atts ){
   
   
          $sc_att = shortcode_atts(
              array(
                'id' => null, 
                'address' => null, 
                'title' => null, 
              ), $atts
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
   		wt_load_templates('googlemaps.php' , $args);
              $html = ob_get_contents(); 
              ob_end_clean();
          return $html;
   }
   
   	/****=====getting the longitude and latitudepoints====***/
   function getDistance( $addressFrom , $apiKey ){
      
      
          // Change address format
          $formattedAddrFrom    = str_replace(' ', '+', $addressFrom);
          
          // Geocoding API request with start address
          $geocodeFrom = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address='.$formattedAddrFrom.'&sensor=false&key='.$apiKey);
          $outputFrom = json_decode($geocodeFrom);
          if(!empty($outputFrom->error_message)){
              return $outputFrom->error_message;
          }
          
          
          // Get latitude and longitude from the geodata
          $latitudeFrom    = $outputFrom->results[0]->geometry->location->lat;
          $longitudeFrom    = $outputFrom->results[0]->geometry->location->lng;
          $resp = array(
          	'latitude' => $latitudeFrom,
          	'longitude' => $longitudeFrom,
          );
          return $resp;
      }
   
   /****=====REndereing the Buttons on front end against Shortcode====***/
   function button_callback( $atts ){
    
          $sc_att = shortcode_atts(
              array(
                'id' => null, 
              ), $atts
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
   		wt_load_templates('buttons.php' , $args);
              $html = ob_get_contents(); 
              ob_end_clean();
          return $html;
   }
   
   
   /****=====REndereing the Testimonial on front end against Shortcode====***/
   function testimonial_callback( $atts ){
   
          $sc_att = shortcode_atts(
              array(
                'id' => null, 
              ), $atts
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
   		wt_load_templates('testimonials.php' , $args);
              $html = ob_get_contents(); 
              ob_end_clean();
          return $html;
   }
   
   
   function video_callback( $atts ){
   	$sc_att = shortcode_atts(
              array(
                'id' => null, 
              ), $atts
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
   		wt_load_templates('videos.php' , $args);
              $html = ob_get_contents(); 
              ob_end_clean();
          return $html;
   }
   
   
   /****=====Edit/Updating the selected data====***/
   function edit_selected_data(){
   	
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
   	wp_send_json(array('status' => 'success' , 'page_url' => $page_url));
   }
   
   /****=====Deleting the selected data====***/
   function delete_selected_data(){
   	
   	$no_tobe_dlt = isset($_REQUEST['rand_id']) ? $_REQUEST['rand_id'] : '';
   	if (empty($no_tobe_dlt)) {
   		return;
   	}
   
   	$saved_random_nos = get_option('get_saved_random_numbers');
   	if (in_array($no_tobe_dlt, $saved_random_nos)) {
   
   		delete_option('get_testimonials_'.$no_tobe_dlt);
   		$result = $this->delete_el_from_array($saved_random_nos , $no_tobe_dlt);
   		update_option('get_saved_random_numbers' , $result);
   		
   		$url_param = array(
   		    'action' => 'deleted'
      		);
      		$url = admin_url('admin.php?page=improveseo_shortcodes');
   	    $page_url = add_query_arg($url_param, $url);
   		
   		
   		wp_send_json(array('status'=>'success' , 'url'=>$page_url));
   	}
   
   }
   
   /****=====Deleting the selected data for keyword on admin side====***/
   function kwdelete_selected_data_for_keyword(){
   	
   	$no_tobe_dlt = isset($_REQUEST['kw_rand_id']) ? $_REQUEST['kw_rand_id'] : '';
   	if (empty($no_tobe_dlt)) {
   		return;
   	}
   
   	$saved_random_nos = get_option('swsaved_random_nosofkeywords');
   	if (in_array($no_tobe_dlt, $saved_random_nos)) {
   
   		delete_option('swsaved_keywords_with_results_'.$no_tobe_dlt);
   		$result = $this->delete_el_from_array($saved_random_nos , $no_tobe_dlt);
   		
   		update_option('swsaved_random_nosofkeywords' , $result);
   		wp_send_json(array('status'=>'success'));
   	}
   
   }
   
   /****=====Deleting Some element from Array====***/
   function delete_el_from_array($my_array , $remove_el){
   
   	$pos = array_search($remove_el, $my_array);
   	
   	unset($my_array[$pos]);
   	return $my_array;
   }
   
   /****=====Load Admin JS And CSS files====***/
   function load_admin_files(){
   	wp_enqueue_style('improveseo_style', WT_URL."/assets/css/improveseo_style.css",  array(), '1.1');
   	wp_enqueue_style("poppins_fonts", "https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap");
   	wp_enqueue_script('tmm_script_js', WT_URL."/assets/js/wt-script.js",  array('jquery'), IMPROVESEO_VERSION, true);
   	wp_enqueue_script('tmm_sweeetalertscript_js', WT_URL."/assets/js/wt-sweetalert.js",  array('jquery'));
   
       wp_localize_script('tmm_script_js', 'ajax_vars', array(
       	'ajax_url'      		=> 	admin_url( 'admin-ajax.php' ),
       	)
   	);		
   
   }
   
   	/****=====Saving Form Fields From Admin Side For Button====***/
   function wt_save_form_fields_for_buttons(){
   
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
   		'tw_button_shortcode_name'  => $tw_button_shortcode_name,
   		'tw_maps_apikey' 			=> $tw_maps_apikey,
   		'tw_btn_text' 				=> $tw_btn_text,
   		'tw_btn_link' 				=> $tw_btn_link,
   		'tw_buttontxt_color'		=> $tw_buttontxt_color,
   		'tw_buttonbg_color' 		=> $tw_buttonbg_color,
   		'tw_button_outline_color'	=> $tw_button_outline_color,
   		'tw_button_size' 			=> $tw_button_size,
   		'tw_button_border_type' 	=> $tw_button_border_type,
   		'tw_button_type' 			=> $tw_button_type,
   		'tw_tap_to_call_img_source' => $tw_tap_to_call_img_source,
   		'tw_tap_btn_text' 			=> $tw_tap_btn_text,
   		'tw_tap_btn_number' 		=> $tw_tap_btn_number,
   
   	);
   
   	if (empty($rand_no)) {
   		$rand_no = $this->create_random_number();
   	}
   	update_option('get_buttons_'.$rand_no , $arr);
   
   	//saving random numbers too
   	$random_no_arr = get_option('get_saved_random_numbers');
   
   	$random_no_arr[] = $rand_no;
   	$result = array_unique($random_no_arr);
   	update_option('get_saved_random_numbers' , $result );
   	$url = admin_url('admin.php?page=improveseo_shortcodes');
   	wp_send_json(array('status' => 'success' , 'url' => $url));
   	die;
   }
   
   /****=====Saving Form Fields From Admin Side For Googlemaps====***/
   function wt_save_form_fields_for_googlemaps(){
   	
   	$rand_no = isset($_REQUEST['updateandedit_data']) ? $_REQUEST['updateandedit_data'] : '';
   	$tw_maps_apikey = isset($_REQUEST['tw_maps_apikey']) ? $_REQUEST['tw_maps_apikey'] : '';
   	$tw_maps_shortcode_name = isset($_REQUEST['tw_maps_shortcode_name']) ? $_REQUEST['tw_maps_shortcode_name'] : '';
   
   	$arr = array(
   		'tw_maps_shortcode_name' => $tw_maps_shortcode_name,
   		'tw_maps_apikey' 	=> $tw_maps_apikey,
   	);
   
   	if (empty($rand_no)) {
   		$rand_no = $this->create_random_number();
   	}
   	update_option('get_googlemaps_'.$rand_no , $arr);
   
   	//saving random numbers too
   	$random_no_arr = get_option('get_saved_random_numbers');
   
   	$random_no_arr[] = $rand_no;
   	$result = array_unique($random_no_arr);
   	update_option('get_saved_random_numbers' , $result );
   	$url = admin_url('admin.php?page=improveseo_shortcodes');
   	wp_send_json(array('status' => 'success' , 'url' => $url));
   	die;
   }
   
   /****=====Saving Form Fields From Admin Side For testimonials====***/
   function wt_save_form_fields_for_testimonials(){
   
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
   		'testi_img_src' 	=> $testi_img_src,
   		'tw_testi_content' 	=> $tw_testi_content,
   		'tw_testi_name' 	=> $tw_testi_name,
   		'tw_testi_position' => $tw_testi_position,
   		'tw_box_color' 		=> $tw_box_color,
   		'tw_font_color' 		=> $tw_font_color,
   		'tw_testi_outline_color'=> $tw_testi_outline_color,
   	);
   
   	if (empty($rand_no)) {
   		$rand_no = $this->create_random_number();
   	}
   	update_option('get_testimonials_'.$rand_no , $arr);
   
   	//saving random numbers too
   	$random_no_arr = get_option('get_saved_random_numbers');
   
   	$random_no_arr[] = $rand_no;
   	$result = array_unique($random_no_arr);
   	update_option('get_saved_random_numbers' , $result );
   	
   	$url = admin_url('admin.php?page=improveseo_shortcodes');
   	wp_send_json(array('status' => 'success' , 'url' => $url));
   	die;
   }
   
   /****=====Saving Form Fields From Admin Side For videos====***/
   function wt_save_form_fields_for_videos(){
   	$rand_no = isset($_REQUEST['updateandedit_data']) ? $_REQUEST['updateandedit_data'] : '';
   
   	$video_shortcode_name = isset($_REQUEST['video_shortcode_name'])?$_REQUEST['video_shortcode_name']:'';
   	$video_type = isset($_REQUEST['video_type'])?$_REQUEST['video_type']:'upload_video';
   
   	$video_poster_img_source = isset($_REQUEST['video_poster_img_source'])?$_REQUEST['video_poster_img_source']:'';
   	$video_poster_img_id = isset($_REQUEST['video_poster_img_id'])?$_REQUEST['video_poster_img_id']:'';
   
   	$video_id_mp4 = isset($_REQUEST['video_id_mp4'])?$_REQUEST['video_id_mp4']:'';
   	$video_url_mp4 = isset($_REQUEST['video_url_mp4'])?$_REQUEST['video_url_mp4']:'';
   
   	$video_id_ogv = isset($_REQUEST['video_id_ogv'])?$_REQUEST['video_id_ogv']:'';
   	$video_url_ogv = isset($_REQUEST['video_url_ogv'])?$_REQUEST['video_url_ogv']:'';
   
   	$video_id_webm = isset($_REQUEST['video_id_webm'])?$_REQUEST['video_id_webm']:'';
   	$video_url_webm = isset($_REQUEST['video_url_webm'])?$_REQUEST['video_url_webm']:'';
   
   	$video_url_vimeo = isset($_REQUEST['video_url_vimeo'])?$_REQUEST['video_url_vimeo']:'';
   	$video_url_youtube = isset($_REQUEST['video_url_youtube'])?$_REQUEST['video_url_youtube']:'';
   
   	$video_autoplay = isset($_REQUEST['video_autoplay'])?$_REQUEST['video_autoplay']:'no';
   	$video_muted = isset($_REQUEST['video_muted'])?$_REQUEST['video_muted']:'no';
   	$video_controls = isset($_REQUEST['video_controls'])?$_REQUEST['video_controls']:'no';
   	$video_loop = isset($_REQUEST['video_loop'])?$_REQUEST['video_loop']:'no';
   	$video_height = isset($_REQUEST['video_height'])?$_REQUEST['video_height']:'auto';
   	$video_width = isset($_REQUEST['video_width'])?$_REQUEST['video_width']:'100%';		
   
   	$arr = array(
   		'video_shortcode_name'		=> $video_shortcode_name,
   		'video_type'				=> $video_type,
   		'video_poster_img_source'	=> $video_poster_img_source,
   		'video_poster_img_id' 		=> $video_poster_img_id,
   		'video_id_mp4' 				=> $video_id_mp4,
   		'video_url_mp4'				=> $video_url_mp4,
   		'video_id_ogv' 				=> $video_id_ogv,
   		'video_url_ogv'				=> $video_url_ogv,
   		'video_id_webm' 			=> $video_id_webm,
   		'video_url_webm' 			=> $video_url_webm,
   		'video_url_vimeo' 			=> $video_url_vimeo,
   		'video_url_youtube' 		=> $video_url_youtube,
   		'video_autoplay' 			=> $video_autoplay,
   		'video_muted' 				=> $video_muted,
   		'video_controls' 			=> $video_controls,
   		'video_loop' 				=> $video_loop,
   		'video_height' 				=> $video_height,
   		'video_width' 				=> $video_width,
   	);
   
   	if (empty($rand_no)) {
   		$rand_no = $this->create_random_number();
   	}
   	update_option('get_videos_'.$rand_no , $arr);
   
   	//saving random numbers too
   	$random_no_arr = get_option('get_saved_random_numbers');
   
   	$random_no_arr[] = $rand_no;
   	$result = array_unique($random_no_arr);
   	update_option('get_saved_random_numbers' , $result );
   
   	$url = admin_url('admin.php?page=improveseo_shortcodes');
   	wp_send_json(array('status' => 'success' , 'url' => $url));
   	die;
   }
   
   /****=====Creating The Random Number====***/
   function create_random_number(){
   
       $rand_no = wp_rand(1,76000);
       $multi = wp_rand(4,140);
       $plus = wp_rand(0,140007);
       $final_no =($rand_no*$multi)+$plus;
   	return $final_no;
   
   }
   
   
   /****=======load files on frontend page======****/
   function load_script_style_files(){
   	
   	wp_enqueue_style('tmm_stlye_css', WT_URL."/css/wt-style.css",  true);
   	wp_enqueue_script('tmm_script_js', WT_URL."/js/wt-script.js",  array('jquery'), IMPROVESEO_VERSION, true);
   
   	//sweet alert
   	wp_enqueue_script('tmm_sweetalerttt', WT_URL."/js/wt-sweetalert.js",  array('jquery'));
       wp_localize_script('tmm_script_js', 'ajax_vars', array(
       	'ajax_url'=> 	admin_url( 'admin-ajax.php' ),
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