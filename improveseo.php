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
        '1.401', // Script version (optional)
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
				$html .= '<input type="checkbox" class="option_'.$id.'" id="testimonial_'.$id.'" value="[improveseo_testimonial id=\''.$id.'\' name=\''.$data_name.'\']" name="shortcodeoption[]" /><button data-action="testimonial" data-name="'.$data_name.'" id="'.$id.'" class="sw-hide-btn button">Add Testimonial - '.$display_name.'</button>';   
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
				$html .= '<input type="checkbox" class="option_'.$id.'" id="button_'.$id.'" value="[improveseo_buttons id=\''.$id.'\' name=\''.$data_name.'\']" name="shortcodeoption[]" /><button data-action="button" data-name="'.$data_name.'" id="'.$id.'" class="sw-hide-btn button">Add Button - '.$display_name.'</button>';   
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
				$html .= '<input type="checkbox" class="option_'.$id.'" id="map_'.$id.'" value="[improveseo_googlemaps id=\''.$id.'\' name=\''.$data_name.'\']" name="shortcodeoption[]" /><button data-action="googlemap" data-name="'.$data_name.'" id="'.$id.'" class="sw-hide-btn button">Add GoogleMap - '.$display_name.'</button>';   
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
				$html .= '<input type="checkbox" class="option_'.$id.'" id="video_'.$id.'" value="[improveseo_video id=\''.$id.'\' name=\''.$data_name.'\']" name="shortcodeoption[]" /><button data-action="video" data-name="'.$data_name.'" id="'.$id.'" class="sw-hide-btn button">Add Video - '.$display_name.'</button>';   
			}
		}
	}
	

    $seo_list = improve_seo_lits();
    if(!empty($seo_list)){
        foreach($seo_list as $li){
            $html .= '<input type="checkbox" class="option_'.$li.'" id="list_'.$li.'" value="@list:'.$li.'" name="shortcodeoption[]" /><button data-action="list" class="sw-hide-btn add-seolistshortcode button" id='.$li.'>@list:'.$li.'</button>';
        }   
        
    }
    
	$output.= '
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/gh/bbbootstrap/libraries@main/jquery.smartWizard.min.js"></script>';
 
	$output.= '<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">';

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
	
				
	</style>'
	
	;

	$output.= '<link href="https://cdn.jsdelivr.net/gh/bbbootstrap/libraries@main/smart_wizard.min.css" rel="stylesheet" type="text/css" /> 
	<link href="https://cdn.jsdelivr.net/gh/bbbootstrap/libraries@main/smart_wizard_theme_dots.min.css" rel="stylesheet" type="text/css" />';

	$output.= '<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">

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
	                                    <div class="form-group col-md-5">
	                                        <div class="form-check-inline">
	                                            <label class="form-check-label">
	                                            <input type="radio" class="form-check-input" name="keyword_selection" value="seed" checked>Seed Keyword
	                                            </label>
	                                        </div>
	                                    </div>
	                                   <div class="form-group col-md-1"></div>
	                                    <div class="form-group col-md-5">
	                                        <div class="form-check-inline">
	                                            <label class="form-check-label">
	                                            <input type="radio" class="form-check-input" name="keyword_selection" value="select_exisiting">Select from keyword list
	                                            </label>
	                                        </div>
	                                    </div>

										
	



	                                </div>
									

									



	                                <div class="row">

	                                <div class="form-group col-md-1"></div>
	                                    <div class="form-group col-md-11 desc" id="seed">
	                                    
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
    	                                           <div class="row"> 
												   <div class="form-group col-md-11 desc" id="seed">
												   <select class="form-control" name="content_type" required  id="cotnt_type" style="max-width: 90% !important;">
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
											   </div>
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
	                                    <div class="form-group col-md-11 desc" id="select_exisiting" style="display: none;">
	                                            <select id="existing_select" name="select_exisiting_options" class="form-control" style="max-width: 84% !important;">
	                                                    <option value="">Select</option>
	                                                    <option value="seed_option1">USE KEYWORD AS IS IN TITLE [A.I. will build content]</option>
	                                                    <option value="seed_option2">CREATE BEST TITLE FROM KEYWORD [A.I. will choose/build content]</option>
	                                                    <option value="seed_option3">CREATE BEST QUESTION FROM KEYWORD [A.I. will choose/build content]</option>
	                                            </select>
	                                            <div style="margin-top: 20px;" class="show_lists">'.$list.'</div>
	                                            <textarea placeholder="Context Prompt" style="margin-top: 20px; width: 84%; display: none; resize:none;" class="form-control" name="existing_keyword" /></textarea>
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
										<option value="english_us">English (US)</option>
										<option value="english_uk">English (Uk)</option>
										
									  </select>
								</div>
								</div>
									<div class="row">
									
									
								</div>
	                                <div class="row">
									<div class="form-group col-md-12">
										<label for="sel1">Details to Include <a href="#" data-toggle="Please ensure the information you input aligns with the Main Keyword and Title. For example, information about dogs should not be added if you are writing about roofing." title="Please ensure the information you input aligns with the Main Keyword and Title. For example, information about dogs should not be added if you are writing about roofing."><div class="dashicons dashicons-info-outline" aria-hidden="true"><br></div></a></label>
										<textarea class="form-control" id="exampleFormControlTextarea1" rows="3" name="details_to_include" onkeypress="return countContent()" OnBlur="LimitText(this,500,1)"></textarea>
										<span id="countContent"></span>
									</div>
	                                   
	                                </div>

									<div class="row">
									<div class="form-group col-md-12">
										<label for="sel1">Call to action <a href="#" data-toggle="Information" title="Information"><div class="dashicons dashicons-info-outline" aria-hidden="true"><br></div></a></label>
										<textarea class="form-control" id="call_to_action" rows="3" name="call_to_action" onkeypress="return countContentCallToAction()" OnBlur="LimitText(this,500,2)"></textarea><span id="countContentCallToAction"></span>
										
									</div>
	                                   
	                                </div>
	                            </div>

	                            <div id="step-3" class="">
	                                <div class="row">
										<div class="col-md-5" style="text-align: center;margin-top: 30px;">
											<input type="radio" name="aiImage" value="AI_image">
	<label>Generate AI Image Based On Title</label> 
										</div>

										<div class="col-md-7" style="text-align: center;margin-top: 30px;margin-bottom: 30px;">
											<input type="radio" name="aiImage" value="Manually_image">
	<label>Manually Upload Image</label> 
										</div>

										<div class="col-md-12" style="margin-left: 16px; text-align: left;">
											<input type="radio" name="aiImage" value="manually_promt_image"> <label>Generate AI Image - Edit Prompt</label> 
										</div>



										<div id="AI_image_div" style="display:none; margin: 0 0 0 33%;">
											<div id="ai-image-display"></div>
											<div class="form-group col-md-12" style="margin: 0 0 0 40%;" id="AIrefreshOption" >
												<i class="fa fa-refresh" aria-hidden="true" onclick="return refreshAIImage()" style="cursor:pointer;"></i>
											</div>
											<input type="hidden" id="AI-Image-uploaded-path" name="AI-Image-uploaded-path">
										</div>

										<div id="Manually_image_div" style="display:none; margin: 0 0 0 33%;" >
											<input type="file" id="upload-image-button" name="Manually_image">
											<div id="manually-image-display"></div>
											<input type="hidden" id="manually-image-uploaded-path" name="manually-image-uploaded-path">
										</div>

										<div id="prompt_image_div" style="display:none; margin: 0 0 0 33%;">
											<div id="ai-with-prompt-image-display"></div>
											
											<input type="hidden" id="AI-Prompt-Image-uploaded-path" name="AI-Prompt-Image-uploaded-path">
										</div>

										

										<div class="form-group col-md-12" id="Prompt_to_create_Dalle_Image" style="margin: 0 0 0 0; display: none;">
											
											<div id="manually_promt" style="margin: 0px 40px 0px 40px;">
												<textarea class="form-control" id="manually_promt_for_image" rows="3" name="manually_promt_for_image" onkeypress="return countContent()" OnBlur="LimitText(this,500,3)"></textarea>
												<span id="error_manually_promt_for_image"></span>
												<input type="button" name="generate_i_image" class="btn btn-primary pull-right"  id="generate_i_image" value="Generate Image" style="margin: 10px 0px 0px 0px;" />
											</div>
										</div>
										</div>
										
	                            </div>

	                            <div id="step-4" class="">
									<div class="row">
										<div class="col-md-12" style="text-align: left; margin-top: 30px; margin-bottom: 30px;">
										
										<textarea class="form-control" id="showmydataindivText"  rows="20" style="display:none;"></textarea>
										<div class="form-control" id="showmydataindiv1" name="showmydataindiv1" style="display:none;"></div>
										<input type="hidden" name="ai_tittle" id="ai_title" />
											<div style="text-align: center; margin: 5% 0px 0px 20%;">
												<input type="button" value="Approve Content" class="btn btn-primary" onclick="return saveData()" id="generateapi" style="display:none;" style="margin: 0px 0px -37px 0px;">
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
	</style>';

	if ((isset($_REQUEST['genaipost'])) && ($_REQUEST['genaipost']=='Generate AI Post')) {


// print_r($_REQUEST) ;

// exit ;


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

    $output.="
    	<script>


		
		function resetSmartWizard() {
			// Destroy the existing SmartWizard instance
			jQuery('#smartwizard').smartWizard('destroy');
		
			// Reinitialize the SmartWizard plugin
			jQuery('#smartwizard').smartWizard({
				// Configuration options
				selected: 0,
				theme: 'default', // theme for the wizard, related CSS need to include for other than default theme
				transitionEffect: 'fade', // Effect on navigation, none/fade/slide/slideleft
				enableURLhash: false, // Enable selection of the step based on url hash
				toolbarSettings: {
					toolbarPosition: 'bottom', // none, top, bottom, both
					toolbarButtonPosition: 'right', // left, right
					showNextButton: true, // show/hide a Next button
					showPreviousButton: true, // show/hide a Previous button
				}
			});
		
			// Go to the first step
			jQuery('#smartwizard').smartWizard('reset');
		}

	    jQuery(document).ready(function(){
	    
	        jQuery('#gettitle').css({display: 'none'});
	        
	        jQuery('#reload').on('click', function(){
        	    jQuery('#seed_select').trigger('change');
	        });
	        
	        jQuery('input[name=\"content_type\"]').on('click', function(){
        	    jQuery('#seed_select').trigger('change');
	        });
	        

			
			 jQuery('#cotnt_type').on('change', function(){
        	    jQuery('#seed_select').trigger('change');
	        });



	        /* jQuery('select#shortcodetype').on('change', function() {
                jQuery('#popupcontainer input[type=checkbox]').prop('checked', false);
            }); */

    	    jQuery('#popupcontainer button').click(function(){
                var getid = jQuery(this).attr('id');
                //console.log(getid);
                if (jQuery('input[type=checkbox].option_'+getid).prop('checked')==true)
                {
                    jQuery('input[type=checkbox].option_'+getid).prop('checked',false);
                    jQuery('#getpopupselected .result_'+getid).remove();
                }
                else
                {
                    jQuery('input[type=checkbox].option_'+getid).prop('checked', true);
                    //console.log(jQuery('.option_'+getid).prop('checked'));
                    
                    if (jQuery('.option_'+getid).prop('checked')==true)
                    {
                        var selectedshortcode = '<div class=\"result_'+getid+'\">'+jQuery('.option_'+getid).val()+'</div>';
                        //console.log('ccc'+selectedshortcode);
                        jQuery('#getpopupselected').append(selectedshortcode);
                    }
                    
                }
            });

			jQuery('#smartwizard').smartWizard({
				selected: 0,
                theme: 'default', // theme for the wizard, related CSS need to include for other than default theme
                transitionEffect: 'fade', // Effect on navigation, none/fade/slide/slideleft
                enableURLhash: false, // Enable selection of the step based on url hash
                toolbarSettings: {
                    toolbarPosition: 'bottom', // none, top, bottom, both
                    toolbarButtonPosition: 'right', // left, right
                    showNextButton: true, // show/hide a Next button
                    showPreviousButton: true, // show/hide a Previous button
                }
            });

			


			jQuery('#smartwizard').on('leaveStep', function(e, anchorObject, stepNumber, stepDirection) {
				if (stepDirection === 'forward') {
					var seed_keyword = jQuery('#seed_keyword').val();
					var seed_select = jQuery('#seed_select').val();
					var step1_error = 0;
					if(seed_keyword=='') {
						document.getElementById('error_seed_keyword').innerText = 'Please enter seed keyword.';
						step1_error++;
					} else {
						jQuery('#error_seed_keyword').html('');
					}

					if(seed_select=='') {
						document.getElementById('error_seed_select').innerText = 'Please select title type.';
						step1_error++;
					} else {
						jQuery('#error_seed_select').html('');
						if(seed_select!='seed_option1')  {
							var checkbox = document.getElementById('checkbox_need');
							if (checkbox.checked) {
								return true;
							} else {
								var errorSpan = document.createElement('span');
									errorSpan.innerText = 'You need to check the checkbox if you want to use the AI-generated title as the title';
									errorContainer.innerHTML = ''; // Clear previous error messages
									errorContainer.appendChild(errorSpan); // Append the error message
								return false;
							}
						}
					}


					

					if(step1_error==0) {
						return true;
					} else {
						return false;
					}
					//alert('Next button clicked');
					// Your condition to prevent moving to the next step
					// if (someConditionIsNotMet) {
					     //return false;
					// }
				} else if (stepDirection === 'backward') {
					//alert('Previous button clicked');
					// Your condition to prevent moving to the previous step
					// if (someConditionIsNotMet) {
					    // return false;
					// }
				}
			});
			
			
			//jQuery('.sw-btn-next').prop('disabled', true);

			// Validate the checkbox
			// jQuery('#checkbox_need').on('change', function() {
			// 	alert('test');
			// 	if (jQuery(this).is(':checked')) {
			// 		// Enable the next button if the checkbox is checked
			// 		jQuery('.sw-btn-next').prop('disabled', false);
			// 	} else {
			// 		// Disable the next button if the checkbox is unchecked
			// 		jQuery('.sw-btn-next').prop('disabled', true);
			// 	}
			// });

			



			jQuery('#popupcontainer button').click(function(){
			 //   jQuery('button').removeClass('selected');
			 //   jQuery(this).addClass('selected');
			 jQuery(this).toggleClass('selected');
			});
		});

		jQuery(document).ready(function(){
			  jQuery(\"input[name$='keyword_selection']\").click(function() {
			  	var test = jQuery(this).val();
			  	console.log(test);

			  	jQuery('div.desc').hide();
		        jQuery('#' + test).show();

		        jQuery(\"#seed_select option[value='']\").attr('selected', true);
		        // jQuery('textarea[name=\"seed_keyword\"]').hide();

		        jQuery(\"#existing_select option[value='']\").attr('selected', true)
		        jQuery('.show_lists').hide();
		  		jQuery('textarea[name=\"existing_keyword\"]').hide();
			  });

			  jQuery('select[name=\"select_exisiting_options\"]').change(function(){
			  	var selvalue = jQuery(this).val();

			  	if (selvalue=='')
			  	{
			  		jQuery('.show_lists').hide();
			  		jQuery('textarea[name=\"existing_keyword\"]').hide();
			  	}
			  	else
			  	{
			  		jQuery('.show_lists').show();
			  		jQuery('textarea[name=\"existing_keyword\"]').show();
			  	}
			  });
		  });
		  
		</script> ";
		  
		$output.='
		<script>

		

		
		

		



		var ajaxUrl = "'.home_url('/').'wp-admin/admin-ajax.php";

        jQuery("#seed_select").on("change", function() {
            
            var seedtype = jQuery(this).val();
            
            if (seedtype!="seed_option1")
            {
                jQuery("#loader").show();
                jQuery("#gettitle").css({display: "flex"});
            }
            else
            {
                jQuery("#loader").hide();
                jQuery("#gettitle").hide();
            }
            
            var seedkeyword = jQuery("#seed_keyword").val();
            var contenttype = jQuery("#cotnt_type").val();
            
            // When btn is pressed.
            // jQuery("#more_posts").attr("disabled",true);

            // Disable the button, temp.
            jQuery.post(ajaxUrl, {
                action: "getGPTdata",
                // offset: (page * ppp) + 1,
                seedtype: seedtype,
                seedkeyword: seedkeyword,
                contenttype: contenttype,
            })
            .success(function(data) {
                // page++;
                // jQuery("#ajax-posts").append(posts);
                // CHANGE THIS!
                // jQuery("#more_posts").attr("disabled", false);

                // alert("????"+posts+">>>");
                // if (posts=="")
                // {
	               // jQuery("#more_posts").attr("disabled", false);
                // 	jQuery("#more_posts").html("End of results");
                // 	// jQuery("#more_posts").hide();
                // }
                
                jQuery("#loader").hide();
                
                
                jQuery("#maintitle").html(" <div class=\'resultdata\'><textarea id=\'maintitlearea\' class=\'form-control\' rows=\'3\' cols=\'70\'>"+data+"</textarea></div>");
                jQuery("#aigeneratedtitle").val(data);
				jQuery("#manually_promt_for_image").val("Very high quality shooting from a distance, high detail, photorealistic, image resolution 2146 pixels, cinematic. The theme is `"+data+"`");
                // alert(data);
            });
        });


		

		


		</script> ';
		
		$output.='
		<script>
		
        jQuery(document).ready(function(){
            jQuery("#language").on("change", function() {
                
                var language = jQuery(this).val();
                
                if ((language=="english_us") || (language=="english_uk")) {
                console.log(language);
                    jQuery("#langerror").html("");
                    jQuery(".sw-btn-next").removeClass("disabled");
                    jQuery(".sw-btn-next").removeAttr("disabled", "disabled");
                    //return false;
                }
                else
                {
                console.log(language);
                    jQuery("#langerror").html("Application API does not support this language");
                    jQuery(".sw-btn-next").addClass("disabled");
                    jQuery(".sw-btn-next").attr("disabled", "disabled");
                    //return true;
                }
            });
        });



		jQuery("#post_size").on("change", function() {
			// Get the selected option value
			var selectedOption = jQuery(this).val();
		
			// Display the selected option in the h2 element
			jQuery("#post_size_select").val(selectedOption);
		});
		
		</script> ';

	echo $output;
}
// include dirname(__FILE__).'improveSEO-2.0.11/views/test.php';
add_action('wp_ajax_getaaldata','getaaldata');
function getaaldata()
{
	$arr=[];
	wp_parse_str($_POST['value'],$arr);


	$nos_of_words =  utf8_decode(urldecode($arr['nos_of_words']));
// print ($nos_of_words) ;

// exit;

	$aigeneratedtitle = $arr['aigeneratedtitle'];
		
	if (empty($aigeneratedtitle)) {
		$seed_keyword = $arr['seed_keyword'];
	}else{
		$seed_keyword = $arr['aigeneratedtitle'];
	}
	
	$keyword_selection = $arr['keyword_selection'];
	$seed_options = $arr['seed_options'];
	$voice_tone = $arr['content_type'];
	$point_of_view = $arr['point_of_view'];
	$call_to_action = $arr['call_to_action'];
	$details_to_include = $arr['details_to_include'];
	

	$content_lang = $arr['content_lang'];
	//$shortcode = $arr['shortcodeoption'];

	wp_parse_str($_POST['value'],$arr);
	$ai_title = $arr['ai_tittle'];
	if($ai_title==''){
		$search_data = $arr['seed_keyword'];
	}else{
		$search_data = $ai_title ;
	}



	$content = createAIpost($seed_keyword, $keyword_selection, $seed_options, $nos_of_words, $content_lang, $shortcode='',1,$voice_tone,$point_of_view,$search_data,$call_to_action,$details_to_include);	

	$meta_title = generateMetaTitle($arr['ai_tittle'], $arr['seed_keyword']);
	$meta_descreption = generateMetaDescreption($arr['ai_tittle'], $arr['seed_keyword']);

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
	$question = "Create an SEO optimized meta title based on the blog post title `".$aigeneratedtitle."` and the keyword `".$seed_keyword."`. Limit characters to 50-60 including spaces.
	";
	return ChatGPTCall($question);
}

function generateMetaDescreption($aigeneratedtitle, $seed_keyword) {
	$question = "Create an SEO optimized meta description. Limit characters to 50-60 including spaces. Meta description is based on the blog post title `".$aigeneratedtitle."`, the keyword `".$seed_keyword."` and the blog post content.";
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
		'model' => "gpt-3.5-turbo"
		
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

function getGPTdata()
{
    $seed_type = $_REQUEST['seedtype'];
    $seed_keyword = $_REQUEST['seedkeyword'];
    $content_type = $_REQUEST['contenttype'];
    
    
    generateTitle($seed_type, $seed_keyword, $content_type);
    
    die($output);
}
add_action('wp_ajax_nopriv_getGPTdata', 'getGPTdata');
add_action('wp_ajax_getGPTdata', 'getGPTdata');

add_action('wp_ajax_generateAIMeta', 'generateAIMeta');


function generateTitle($seed_type, $seed_keyword, $content_type)
{
	global $wpdb, $user_ID;

    // Your OpenAI API key
    $apiKey = get_option('improveseo_chatgpt_api_key');
    
    // The endpoint URL for OpenAI chat completions API (replace with the correct endpoint)
    $apiUrl = 'https://api.openai.com/v1/chat/completions';

		if($content_type!='') {
			$content_type = 'voice of content must be '.$content_type;
		}
        if ($seed_type=='seed_option2')
        {
			//Create a compelling {title/question} to capture attention based on the keyword ‘{seed keyword}’
            $question = 'Create a compelling seo optimized blog post title based on the keyword `'.$seed_keyword.'` in the form of No Answer. No emojis. No hashtags. Limit characters not including spaces to 80-100. '.$content_type;
        }
        else if ($seed_type=='seed_option3')
        {
            //$question = 'Create '.$content_type.' question title for '.$seed_keyword.' maximum 30 words limit with sysmbol of "?"';

			$question = 'Create a compelling seo optimized blog post title based on the keyword `'.$seed_keyword.'` in the form of one question only. No Answer. No emojis. No hashtags. Limit characters not including spaces to 80-100. '.$content_type;
        }
        else {
            $question = $seed_keyword;
        }
        
        // echo "????".$question;
        
        // Your chat messages
        $messages = [
            // ['role' => 'system', 'content' => 'You are a helpful assistant.'],
            ['role' => 'user', 'content' => $question]
            // ['role' => 'assistant', 'content' => 'Hello, how can I help you today?'],
        ];
        
        
        // Additional parameters, including language setting (replace with actual parameters)
        $data = [
            'messages' => $messages,
            'model' => "gpt-3.5-turbo"
			
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
        
        if ($seed_type=='seed_option2')
        {
            $content =  preg_replace('~^[\'"]?(.*?)[\'"]?$~', '$1', $result['choices'][0]['message']['content']);
            
            echo str_replace("'", '`', $content);
        }
        else if ($seed_type=='seed_option3')
        {
            $content =  preg_replace('~^[\'"]?(.*?)[\'"]?$~', '$1', $result['choices'][0]['message']['content']);
            
            echo str_replace("'", '`', $content);
        }
        else {
            echo '';
        }
}

function createAIpost($seed_keyword, $keyword_selection, $seed_options, $nos_of_words, $content_lang, $shortcode='',$is_single_keyword = '',$voice_tone = '',$point_of_view = '',$title='',$call_to_action = '',$details_to_include = '')
{
	global $wpdb, $user_ID;

    // Your OpenAI API key
    $apiKey = get_option('improveseo_chatgpt_api_key');
    
    // The endpoint URL for OpenAI chat completions API (replace with the correct endpoint)
    $apiUrl = 'https://api.openai.com/v1/chat/completions';

	// create LSI keywords
	$text_for_lsi = 'You assume that you are an SEO manager. Create a list of 50 LSI keywords that are related to the keyword '.$title.' Give me only keywords without any explanation';
	// Your chat messages
	$messages = [
		// ['role' => 'system', 'content' => 'You are a helpful assistant.'],
		['role' => 'user', 'content' => $text_for_lsi]
		// ['role' => 'assistant', 'content' => 'Hello, how can I help you today?'],
	];
	
	// Additional parameters, including language setting (replace with actual parameters)
	$data = [
		'messages' => $messages,
		"model" => "gpt-3.5-turbo",
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
	


   if($call_to_action == ''){
	$call_to_actions = '';
   }else{
	$call_to_actions =
	$call_to_action ;
   }

   
//    if($details_to_include == ''){
// 	$details_to_include = '';
//    }else{
// 	$details_to_include =  '11. Call To Action:'.$details_to_include.' ' ;
//    }

        // 'write a '.$nos_of_words.' blog post about '.$seed_keyword ;
        // $call_to_actions = '11. Call To Action:'.$call_to_action.'';



		$question = 'Pretend you are Malcolm Gladwell. Using the provided outline, write a '.$nos_of_words.' blog post about '.$seed_keyword.'Your task is to: Aim the piece at a broad audience.		
		Use a mix of short, medium, and long sentences to create a human-like rhythm in the text.
		Incorporate humor, where appropriate, to make the piece more enjoyable to read.
		Include an analogy to explain any complex concepts or ideas.
		Use a '.$voice_tone.' tone to make the piece more relatable.
		Consider the perspectives of both an expert and a beginner.
		Write from the following point of view: '.$point_of_view.'
		Incorporate the provided "LSI keywords" naturally throughout the content. '.$LSI_Keyords.'
		
		Consider the context that is provided to make the blog post more relevant to the main keyword '.$seed_keyword.'
		
		Context:'.$details_to_include.' 
		Use the iterative approach to improve upon your initial draft. After each draft, critique your work, give it a score out of 10, and if the score is below 9, improve upon the previous draft. Repeat this process until you achieve a score of 9 or 10. When doing this, review and edit your work to remove any grammatical errors, unnecessary information, and superfluous sentences. Don`t provide output of this critique, this is only for you to analyze internally.
		
		The blog post should contain the following:
		
		   - Provide a concise preview of the content`s value and insights and write an engaging and informative introduction, incorporating the primary keyword within the first 100-150 words, applying NLP and EI principles for emotional resonance. Don’t create a header for this section, only provide the paragraph.
		
		2. **Table of Contents:****
		   - Outline main content areas with H2 section subheaders, non-bolded and in medium gray, for SEO and easy navigation. Please make sure that H2 section subheaders are not bold. Don’t include a header for the following section: ‘Introduction’		
		
		3. **Main Content Sections:****
		   - Create H2 sections with titles using keywords and their variations at a 1-2% usage rate per 100 words to prevent keyword stuffing. Each section should contain 3-5 sentences of detailed content, employing NLP and EI for relatability and actionability. Please make sure that the content for each section is at least 150 words.
		
		4. **Conclusion:****
		   - Summarize key insights in an H2 header, medium gray, encouraging further exploration or engagement.
		
		5.  **FAQ - Frequently Asked Questions****
			-  Answer common questions about '.$seed_keyword.' with clear, informative, non-bolded answers that empathize with the reader`s concerns. Output should just be the questions and answers, don’t write ‘Q’ in front of the questions and ‘A’ in front of the answers.
		6. **What’s Next? ****:
			-Write a short paragraph inviting the reader to take action in the explained way, including links or phone numbers if provided.
        '.$call_to_actions.' ** must be in the output and do not add # in the output.' ;
		
        // Your chat messages
        $messages = [
            // ['role' => 'system', 'content' => 'You are a helpful assistant.'],
            ['role' => 'user', 'content' => $question]
            // ['role' => 'assistant', 'content' => 'Hello, how can I help you today?'],
        ];
        
        // Additional parameters, including language setting (replace with actual parameters)
        $data = [
            'messages' => $messages,
            "model" => "gpt-3.5-turbo",
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
        
        
        
        
        if($is_single_keyword=='') {
			$content = array('title'=>$seed_keyword, 'content'=>$content_final, 'post_type'=>'post');
        $options = array("max_posts"=>"1");
			$wpdb->insert($wpdb->prefix . "improveseo_tasks", array(
    				
				'name' => $seed_keyword,
				'content' => base64_encode(json_encode($content)),
				'options' => base64_encode(json_encode($options)),
				'iteration' => 0,
				'spintax_iterations' => 1,
				'max_iterations' => 1,
				'state' => "Published",
				'created_at' => date('Y-m-d h:m:s')
			));
		} else {
			return $content_final;
		}
       
    	
        // 	$linkredirect = home_url('/').'wp-admin/admin.php?page=improveseo_projects';
        // 	wp_redirect( $linkredirect, 301 );
    
}

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


// AJAX handler for image upload
add_action('wp_ajax_fetch_AI_image', 'fetch_AI_image_callback');
function fetch_AI_image_callback() {
	if (!empty($_POST['title'])) {
		$title = $_POST['title'];
		if(!empty($_POST['noedit'])) {
			$imgPrompt = $title;
		} else {
			$imgPrompt = "Very high quality shooting from a distance, high detail, photorealistic, image resolution 2146 pixels, cinematic. The theme is ‘".$title."’";
		}


    	$dateTimeDefault = date('YmdHis');
    	$imagename = 'ai_image_'.$dateTimeDefault;
		// Your OpenAI API key
		$apiKey = get_option('improveseo_chatgpt_api_key');
		// The endpoint URL for OpenAI chat completions API (replace with the correct endpoint)
		$apiUrl = 'https://api.openai.com/v1/images/generations';
    
		// Your input data or parameters
		$data = array(
			// 'prompt' => $term.' '.accordingtoterm($call, $_REQUEST['wordlimit']),
			'prompt' => $imgPrompt,//.' '.accordingtoterm($imgdisc, $_REQUEST['wordlimit']),
			'model'     => 'dall-e-3',
			'n'         => 1,
			'size'  => '1792x1024'
		);
    
		// Set up cURL
		$ch = curl_init($apiUrl);
		
		// Set cURL options
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Authorization: Bearer ' . $apiKey,
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
		
		if(!empty($result['data'][0]['url'])) {
			$url = $result['data'][0]['url'];
			
		


			////////////////////////////////////////////////////////////////


			$upload_dir = wp_upload_dir(); // Get the WordPress upload directory

			$image_data = file_get_contents($url); // Fetch image data from URL

			if ($image_data !== false) {
				// Generate a unique file name for the image
				$file_name = wp_unique_filename($upload_dir['path'], basename($url));
		
				// Save the image to the uploads directory
				$file_path = $upload_dir['path'] . '/' . $file_name;
				if (file_put_contents($file_path, $image_data) !== false) {
					// Image saved successfully, you can now return the image URL
		
					// Construct the image URL relative to the uploads directory
					$image_url = $upload_dir['url'] . '/' . $file_name;
		
					// Return the image URL as JSON response
					wp_send_json_success($image_url);
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
			//print_r($result);
			//fetch_AI_image_callback($title);
		}
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
	    
	    $proj_name = isset($_REQUEST['proj_name']) ? $_REQUEST['proj_name'] : '';
	    $search_results = isset($_REQUEST['search_results']) ? $_REQUEST['search_results'] : '';
	    
	    $rand_no = $this->create_random_number();
	    $save_keyword_data = array(
	            'proj_name' => $proj_name,
	            'search_results' => $search_results,
	       );
	    update_option('swsaved_keywords_with_results_'.$rand_no , $save_keyword_data);
	    
	    		//saving random numbers too
		$random_no_arr = get_option('swsaved_random_nosofkeywords');

		$random_no_arr[] = $rand_no;
		$result = array_unique($random_no_arr);
		
		update_option('swsaved_random_nosofkeywords' , $result );
	    
	    $args = array(
	            'status' => 'success',
	        );
	    wp_send_json($args);
	    die(0);
	    
	}
	
	
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
	    	'ajax_url'      		=> 	admin_url( 'admin-ajax.php' ),
	    	)
		);

	}

}
new WC_Testimonial;