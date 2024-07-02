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

	.modal { 
		max-width: unset;
	} 
	 /*#exampleModal { 
		z-index: 9999; 
	}*/
	.modal-backdrop { 
		height:unset;
	}
	 .input-group > .form-control { 
		width: 100%; 
	}
	#popupcontainer input[type=checkbox] { 
		display:none 
	} 
	#getpopupselected {
		margin: 20px 0;
	}
   
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
   </style>

<?php 

		// All Keywords list

		$saved_rand_nos_keywords = get_option('swsaved_random_nosofkeywords');
		if (empty($saved_rand_nos_keywords)) {
			return;
		}
		$saved_rand_nos_keywords = maybe_unserialize($saved_rand_nos_keywords);

		$html_key = '';
		$all_keywords = [];
		foreach ($saved_rand_nos_keywords as $keyword_id) {
			$get_keyworddata = get_option('swsaved_keywords_with_results_' . $keyword_id);
			if (empty($get_keyworddata)) {
				continue;
			}
			$proj_name = isset($get_keyworddata['proj_name']) ? $get_keyworddata['proj_name'] : '';
			$search_results = isset($get_keyworddata['search_results']) ? $get_keyworddata['search_results'] : '';
			$html_key .= '<option value="' . esc_attr($keyword_id) . '">' . esc_html($proj_name) . '</option>';
			$all_keywords[$keyword_id] = $search_results;
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
		
		// Generate the HTML output for the image gallery
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


			function processFormData() {
				$proj_name = isset($_REQUEST['proj_name']) ? $_REQUEST['proj_name'] : '';
				$search_results = isset($_REQUEST['search_results']) ? $_REQUEST['search_results'] : '';

				// Assuming create_random_number() is a method within the current class context
				$rand_no = create_random_number(); // If this is a method within a class, make sure to call it appropriately.
				
				$save_keyword_data = array(
					'proj_name' => $proj_name,
					'search_results' => $search_results,
				);
				
				update_option('swsaved_keywords_with_results_' . $rand_no, $save_keyword_data);
				
				// Saving random numbers too
				$random_no_arr = get_option('swsaved_random_nosofkeywords');
				$random_no_arr[] = $rand_no;
				$result = array_unique($random_no_arr);
				
				update_option('swsaved_random_nosofkeywords', $result);

				wp_send_json_success(array(
					'status' => 'success',
					'id' => $rand_no,
					'proj_name' => $proj_name,
					'search_results' => $search_results,
				));
			}
		
		add_action('wp_ajax_process_form_data', 'process_form_data');
		//add_action('wp_ajax_nopriv_process_form_data', 'process_form_data');
		
		function enqueue_custom_scripts() {
			wp_enqueue_script('custom-ajax-script', get_template_directory_uri() . '/js/script.js', array('jquery'), null, true);
			wp_localize_script('custom-ajax-script', 'ajaxurl', admin_url('admin-ajax.php'));
		}
		
		add_action('wp_enqueue_scripts', 'enqueue_custom_scripts');

?>

<div class="modal fade" id="exampleModal2" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
   
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
				   
				   <form id="popup_multi_form" method="post" class="pop_up_multi_form">
				   <div class="modal-body">
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
														  <small>Generate AI Content</small>
													  </a>
												  </li>
												  <li style="width: 20%;">
													  <a href="#steps-5" style="text-align: center;">
														  Step 5<br />
														  <small>Meta Title & Description</small>
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
										 <select id="project_name" name="project_name" class="form-control" style="max-width: 84% !important;">
										 <option value="">Select a project</option>
										 <option value="create_new_project">create_new_project</option>
												 '. $html_key .'
											 </select>
											<span id="error_project_name" style="color: red;"></span>
										 </div>
										 <div class="form-group" id="keyword_list_container" style="display: none;">
											 <h2>Keywords</h2>
											 <textarea class="form-control" style="display: none; width: 84%; resize:none;" placeholder="Enter Seed Keyword" id="seed_keyword" name="seed_keyword"></textarea>
											 <textarea id="keyword_list" name="keyword_list" class="form-control" rows="10" style="max-width: 84% !important;" readonly></textarea>
										 </div>
												 <div id="create_keyword_container" style="display: none; padding-bottom:15px;">
													 <label>Create Keywords Types</label><br>
													 <select id="create_keyword" name="create_keyword" class="form-control" style="max-width: 84% !important;">
														 <option value="none">Select</option>
														 <option value="copy_paste">Copy & Paste</option>
														 <option value="google_suggestion">Create Google Suggestion Keyword</option>
														 <option value="ai_create_keyword">AI Create Keyword</option>
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
															 <textarea class="form-control" id="exampleFormControlTextarea1" rows="3" name="details_to_include" style=" max-width: 84% !important;" onkeypress="return countContent()" OnBlur="LimitText(this,500,1)"></textarea>
															 <div class="BasicForm__row mb-3" style="max-width:84%; text-align:end; padding:15px 0px;">
															 <input type="button" onclick="return SaveResultsButton();" class="btn btn-outline-primary" value="Save Results">
														 <span id="countContent"></span>
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
									<div class="form-group col-md-12">
										<label for="sel1">Call to action <a href="#" data-toggle="Information" title="Information"><div class="dashicons dashicons-info-outline" aria-hidden="true"><br></div></a></label>
										<textarea class="form-control" id="call_to_action" rows="3" name="call_to_action" onkeypress="return countContentCallToAction()" OnBlur="LimitText(this,500,2)"></textarea><span id="countContentCallToAction"></span>	
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
												 '. $html_output .'
											 </div>
										 </div>
										</div>
								</div>
	
								<div id="steps-4" class="">
									 <div class="row">

									 <div class="col-md-12" text-align: left; margin-top: 30px; margin-bottom: 30px;">
											 <select class="sw-editor-selector" style="text-align:left !important; width:100%; diplay:block;">
												 <option value="addshortcode">Add Shortcode</option>
												 <option value="testimonial">Testimonials</option>
												 <option value="googlemap">Google Maps</option>
												 <option value="button">Buttons</option>
												 <option value="video">Videos</option>
											 </select> &nbsp;
											 '. $html .'
										 </div>
										 <div class="col-md-12" style="text-align: left; margin-top: 30px; margin-bottom: 30px;">
										 
										 <textarea class="form-control" id="showmydataindivText"  rows="1" style="opacity: 0;"></textarea>
										 
										 <div id="showmydataindiv1" name="showmydataindiv1" style="display: block;max-width: 100%;overflow-y: scroll;"></div>
										 <input type="hidden" name="ai_tittle" id="ai_title" />
											 <div style="text-align: center; display: flex; justify-content: center; gap: 10px; margin: 20px;">
												 <input type="button" value="Approve Content" class="btn btn-primary" onclick="return saveData()" id="generateapi" style="display:none;" style="margin: 0px 0px -37px 0px;">
												 <input type="button" name="genaipost" class="btn btn-primary" id="generateapivalue" value="Generate AI Post" />
												 <input type="hidden" name="AI_Title" id="AI_Title">
												 <input type="hidden" name="AI_descreption" id="AI_descreption">
											 </div>
										 </div>
									 </div>
								</div>
	
								<div id="steps-5" class="">
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

   <script>
jQuery(document).ready(function() {

jQuery('#project_name').on('change', function() {
	var selectedOption = jQuery(this).val();
	if (selectedOption == 'create_new_project' || selectedOption == 'none') {
		jQuery('#keyword_list_container').hide();
	} else {
	 jQuery('#keyword_list_container').show();
		var allKeywords = <?php echo json_encode($all_keywords); ?>;
		jQuery('#keyword_list').val(allKeywords[selectedOption]);
	}
 if (selectedOption == 'create_new_project') {
	 jQuery('#create_keyword_container').show();
		 } else {
			 jQuery('#create_keyword_container').hide();
 } 
});
});
</script>