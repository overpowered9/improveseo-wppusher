<?php
	$saved_random_nos = get_option('get_saved_random_numbers');
	
	$specific_no = isset($_GET['rand_id']) ? $_GET['rand_id'] : '';
	$action = isset($_GET['action']) ? $_GET['action'] : '';

	// For testimonial
	$data = get_option('get_testimonials_'.$specific_no);
	$testi_img_src = isset($data['testi_img_src']) ? $data['testi_img_src'] : '';
	$tw_testi_content = isset($data['tw_testi_content']) ? $data['tw_testi_content'] : '';
	$tw_testi_name = isset($data['tw_testi_name']) ? $data['tw_testi_name'] : '';
	$tw_testi_position = isset($data['tw_testi_position']) ? $data['tw_testi_position'] : '';
	$tw_box_color = isset($data['tw_box_color']) ? $data['tw_box_color'] : '#ffffff';
	$tw_font_color = isset($data['tw_font_color']) ? $data['tw_font_color'] : '#ffffff';
	$tw_testi_outline_color = isset($data['tw_testi_outline_color']) ? $data['tw_testi_outline_color'] : '#ffffff';
	// for testimonial
	// for google_maps
	$data_gm = get_option('get_googlemaps_'.$specific_no);
	$tw_maps_apikey = isset($data_gm['tw_maps_apikey']) ? $data_gm['tw_maps_apikey'] : '';
	// for google_maps
	
	// For Buttons
	$data_btn = get_option('get_buttons_'.$specific_no);
	
	$tw_btn_text = isset($data_btn['tw_btn_text']) ? $data_btn['tw_btn_text'] : '';
	$tw_btn_link = isset($data_btn['tw_btn_link']) ? $data_btn['tw_btn_link'] : '';
	$tw_buttontxt_color = isset($data_btn['tw_buttontxt_color']) ? $data_btn['tw_buttontxt_color'] : '#ffffff';
	$tw_buttonbg_color = isset($data_btn['tw_buttonbg_color']) ? $data_btn['tw_buttonbg_color'] : '#ffffff';
	$tw_button_outline_color = isset($data_btn['tw_button_outline_color']) ? $data_btn['tw_button_outline_color'] : '#ffffff';
	$tw_button_size = isset($data_btn['tw_button_size']) ? $data_btn['tw_button_size'] : 'sm';
	$tw_button_border_type = isset($data_btn['tw_button_border_type']) ? $data_btn['tw_button_border_type'] : 'square';
	$tw_button_type = isset($data_btn['tw_button_type']) ? $data_btn['tw_button_type'] : 'normal_btn';

	$tw_tap_to_call_img_source = isset($data_btn['tw_tap_to_call_img_source']) ? $data_btn['tw_tap_to_call_img_source'] : '';
	$tw_tap_btn_text = isset($data_btn['tw_tap_btn_text']) ? $data_btn['tw_tap_btn_text'] : '';
	$tw_tap_btn_number = isset($data_btn['tw_tap_btn_number']) ? $data_btn['tw_tap_btn_number'] : '';
	

	// For Videos
	$data_video = get_option('get_videos_'.$specific_no);
	$video_type = isset($data_video['video_type'])?$data_video['video_type']:'upload_video';
	$video_poster_img_source = isset($data_video['video_poster_img_source'])?$data_video['video_poster_img_source']:'';
	$video_poster_img_id = isset($data_video['video_poster_img_id'])?$data_video['video_poster_img_id']:'';

	$video_id_mp4 = isset($data_video['video_id_mp4'])?$data_video['video_id_mp4']:'';
	$video_url_mp4 = isset($data_video['video_url_mp4'])?$data_video['video_url_mp4']:'';

	$video_id_ogv = isset($data_video['video_id_ogv'])?$data_video['video_id_ogv']:'';
	$video_url_ogv = isset($data_video['video_url_ogv'])?$data_video['video_url_ogv']:'';

	$video_id_webm = isset($data_video['video_id_webm'])?$data_video['video_id_webm']:'';
	$video_url_webm = isset($data_video['video_url_webm'])?$data_video['video_url_webm']:'';

	$video_url_vimeo = isset($data_video['video_url_vimeo'])?$data_video['video_url_vimeo']:'';
	$video_url_youtube = isset($data_video['video_url_youtube'])?$data_video['video_url_youtube']:'';

	$video_autoplay = isset($data_video['video_autoplay'])?$data_video['video_autoplay']:'no';
	$video_muted = isset($data_video['video_muted'])?$data_video['video_muted']:'no';
	$video_controls = isset($data_video['video_controls'])?$data_video['video_controls']:'no';
	$video_loop = isset($data_video['video_loop'])?$data_video['video_loop']:'no';
	$video_height = isset($data_video['video_height'])?$data_video['video_height']:'auto';
	$video_width = isset($data_video['video_width'])?$data_video['video_width']:'100%';
	
?>
<div class="cm-admin improveseo_wrapper p-3 p-lg-4">
	<section class="project-section">
		<div class="project-heading border-bottom d-flex flex-row pb-2">
			<img class="mr-2" src="<?php echo WT_URL.'/assets/images/project-list-logo.png'?>" alt="ImproveSeo">
			<h1>ImproveSEO</h1>
		</div>
	</section>
	<section class="tabs-wrap-content">
		<ul class="nav nav-tabs" role="tablist">
			<li class="nav-item">
				<a class="nav-link <?php echo ($action!="googlemaps" && $action!="buttons"  && $action!="videos" && $action!="testimonial")?'active':''; ?>" data-toggle="tab" href="#saved_testimonials" role="tab" aria-controls="All Saved Shortcodes" aria-selected="false">Saved Shortcodes</a>
			</li>
			<li class="nav-item">
				<a class="nav-link <?php echo ($action=="testimonial")?'active':''; ?>" data-toggle="tab" href="#wt_testimonial" role="tab" aria-controls="Testimonial" aria-selected="true">Testimonial</a>
			</li>
			<li class="nav-item">
				<a class="nav-link <?php echo ($action=="googlemaps")?'active':''; ?>" data-toggle="tab" href="#google_maps" role="tab" aria-controls="Google Maps" aria-selected="false">Google Maps</a>
			</li>
			<li class="nav-item">
				<a class="nav-link <?php echo ($action=="buttons")?'active':''; ?>" data-toggle="tab" href="#button_wt" role="tab" aria-controls="Button Settings" aria-selected="false">Button Settings</a>
			</li>
			<li class="nav-item">
				<a class="nav-link <?php echo ($action=="videos")?'active':''; ?>" data-toggle="tab" href="#videos" role="tab" aria-controls="Button Settings" aria-selected="false">Videos</a>
			</li>
			
		</ul>
		<div class="tab-content" id="myTabContent">
			<!-- Saved TEstimonails -->
			<div class="tab-pane fade <?php echo ($action!="googlemaps" && $action!="buttons" && $action!="videos" && $action!="testimonial")?'show active':''; ?>" id="saved_testimonials" role="tabpanel" aria-labelledby="saved_testimonials">
				
				<?php
					wt_load_templates('all-saved-testimonials.php');
				?>
			</div>
			<div class="tab-pane fade <?php echo ($action=="testimonial")?'show active':''; ?>" id="wt_testimonial" role="tabpanel" aria-labelledby="wt_testimonial">
				<?php
				$no = isset($_GET['action']) ? $_GET['action'] : '';
				if($no == 'testimonial'){
					$no = $specific_no;
				}else $no = '';
				?>
				<form class="wt-save-admin-settings-testimonials form-wrap mt-3">
					<div class="row">
						<div class="col-lg-2">
							<div class="BasicForm__row">
								<input type="hidden" name="action" value="wt_save_form_fields_for_testimonials">
								<input type="hidden" name="active_action" value="testimonial">
								<input type="hidden" class="img-source" name="img_source" value="<?php echo $testi_img_src; ?>">
								<input type="hidden" class="updateingdata" name="updateandedit_data" value="<?php echo $no; ?>">
								<label class="form-label">Testimonial Image</label>
								<button class="btn btn-outline-primary w-100 upload-image-js upload-btn-padding">
								Upload Image
									<?php
									if($testi_img_src!=""): ?>
										<img class="testimonial-img" style="width: 62px;" src="<?php echo $testi_img_src; ?>" />
									<?php endif; ?>
								</button>
							</div>
						</div>
						<div class="col-lg-10">
							<div class="BasicForm__row">
								<div class="input-group">
									<label class="form-label">Testimonial Content:</label>
									<div class="input-prefix">
										<input type="text" name="tw_testi_content" placeholder="lorem ipsum" class="form-control"><?php echo $tw_testi_content; ?>
										<span>Ex.</span>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-2">
							<div class="BasicForm__row">
								<div class="input-group">
									<label class="form-label">Client Name:</label>
									<div class="input-prefix">
										<input type="text" class="form-control name" name="tw_testi_name" placeholder="Robbert Aleex." value="<?php echo $tw_testi_name; ?>">
										<span>Ex.</span>
									</div>
								</div>
							</div>
						</div>
						<div class="col-lg-2">
							<div class="BasicForm__row">
								<div class="input-group">
									<label class="form-label">Client Position:</label>
									<div class="input-prefix">
										<input type="text" class="form-control name" name="tw_testi_position" placeholder="Founder" value="<?php echo $tw_testi_position; ?>">
										<span>Ex.</span>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-2">
							<div class="BasicForm__row">
								<label class="form-label">Box Color:</label>
								<input type="color" class="form-control name color-pick" name="tw_box_color" value="<?php echo $tw_box_color; ?>">
							</div>
						</div>
						<div class="col-lg-2">	
							<div class="BasicForm__row p-0">
								<label class="form-label">Font Color:</label>
								<input type="color" class="form-control color-pick name" name="tw_font_color" value="<?php echo $tw_font_color; ?>">
								
							</div>
						</div>
						<div class="col-lg-2">
							<label class="form-label">Outline Color:</label>
							<input type="color" class="form-control color-pick name" name="tw_testi_outline_color" value="<?php echo $tw_testi_outline_color; ?>" />
						</div>
					</div>
					<div class="row">
						<div class="col-lg-2">
							<input type="submit" class="btn btn-outline-primary py-3 px-4 cm-custom-btn" value="Save Testimonial">
						</div>
					</div>
				</form>
			</div>
			<div class="tab-pane fade <?php echo ($action=="googlemaps")?'show active':''; ?>" id="google_maps" role="tabpanel" aria-labelledby="google_maps">
				<!-- For Google Maps -->
				<?php
				$no = isset($_GET['action']) ? $_GET['action'] : '';
				if($no == 'googlemaps'){
				$no = $specific_no;
				}else $no = '';
				?>
				<form class="wt-save-admin-settings-googlemaps form-wrap mt-3">
					<div class="BasicForm__row">
						<input type="hidden" name="action" value="wt_save_form_fields_for_googlemaps">
						<input type="hidden" name="active_action" value="googlemap">
						<input type="hidden" class="updateingdata" name="updateandedit_data" value="<?php echo $no; ?>">
						<div class="input-group">
							<label class="form-label">Google Maps API Key:</label>
							<div class="input-prefix">
								<input type="text" class="form-control name" name="tw_maps_apikey" placeholder="" value="<?php echo $tw_maps_apikey; ?>">
							</div>
						</div>
						<input type="submit" class="btn btn-outline-primary py-3 px-4 cm-custom-btn" value="Save API Key">
					</div>
				</form>
			</div>
			<!-- Button settings -->
			<div class="tab-pane fade <?php echo ($action=="buttons")?'show active':''; ?>" id="button_wt" role="tabpanel" aria-labelledby="button_wt">
				<?php
						$no = isset($_GET['action']) ? $_GET['action'] : '';
						if($no == 'buttons'){
						$no = $specific_no;
						}else $no = '';
				?>
				<form class="wt-save-admin-settings-buttons mt-3 form-wrap">
					<div class="row">
						<input type="hidden" name="action" value="wt_save_form_fields_for_buttons">
						<input type="hidden" name="active_action" value="button">
						<input type="hidden" class="updateingdata" name="updateandedit_data" value="<?php echo $no; ?>">
						<div class="col-12">
							<div class="row">
								<div class="BasicForm__row col-lg-6 radio-btns-wrapper">
									<label class="form-label">Button Type:</label>
									<div class="form-check form-check-inline">
										<input class="form-check-input tw_button_type" type="radio" name="tw_button_type" id="tw_button_type_normal" value="normal_btn" <?= checked('normal_btn', $tw_button_type); ?> />
										<label class="form-check-label" for="tw_button_type_normal">Normal Button</label>
									</div>
									<div class="form-check form-check-inline">
										<input class="form-check-input tw_button_type" type="radio" name="tw_button_type" id="tw_button_type_tap_to_call" value="tap_to_call" <?= checked('tap_to_call', $tw_button_type); ?> />
										<label class="form-check-label" for="tw_button_type_tap_to_call">Tap to Call</label>
									</div>
									
								</div>
							</div>
						</div>
						<div class="col-12" id="normal_btn_wrapper" style="<?php echo ($tw_button_type=="tap_to_call")?'display:none':''; ?>">
							<div class="row">
								<div class="BasicForm__row col-lg-2">
									<div class="input-group">
										<label class="form-label">Button Text:</label>
										<div class="input-prefix">
											<input type="text" class="form-control name" placeholder="Next"name="tw_btn_text" value="<?php echo $tw_btn_text; ?>">
											<span>Ex.</span>
										</div>
									</div>
								</div>
								<div class="BasicForm__row col-lg-4">
									<div class="input-group">
										<label class="form-label">Button Link:</label>
										<div class="input-prefix">
											<input type="text" class="form-control name" placeholder="https://google.com"name="tw_btn_link" value="<?php echo $tw_btn_link; ?>">
											<span>Ex.</span>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="col-12" id="tap_to_call_btn_wrapper" style="<?php echo ($tw_button_type=="normal_btn")?'display:none':''; ?>">
							<div class="row">
								<div class="BasicForm_row col-lg-2">
									<input type="hidden" class="tap-to-call-img-source" name="tw_tap_to_call_img_source" value="<?php echo $tw_tap_to_call_img_source; ?>" />
									<label class="form-label">Tap to Call Icon</label>
									<button class="btn btn-outline-primary w-100 tap-to-call-upload-image-js upload-btn-padding">
									Upload Image
										<?php
										if($tw_tap_to_call_img_source!=""): ?>
											<img class="tap-to-call-img" style="width: 62px;" src="<?php echo $tw_tap_to_call_img_source; ?>" />
										<?php endif; ?>
									</button>
								</div>
								<div class="BasicForm__row col-lg-2">
									<div class="input-group">
										<label class="form-label">Button Text:</label>
										<div class="input-prefix">
											<input type="text" class="form-control name" placeholder="Next"name="tw_tap_btn_text" value="<?php echo $tw_tap_btn_text; ?>">
											<span>Ex.</span>
										</div>
									</div>
								</div>
								<div class="BasicForm__row col-lg-3">
									<div class="input-group">
										<label class="form-label">Number:</label>
										<div class="input-prefix">
											<input type="text" class="form-control name" placeholder="+1 1231231231"name="tw_tap_btn_number" value="<?php echo $tw_tap_btn_number; ?>" />
											<span>Ex.</span>
										</div>
									</div>
								</div>
							</div>
						</div>
						
						<div class="col-12">
							<div class="row">
								<div class="BasicForm__row col-lg-2">
									<div class="input-group">
										<label class="form-label">Background Color:</label>
										<input type="color" class="form-control name color-pick name" name="tw_buttonbg_color" value="<?php echo $tw_buttonbg_color; ?>">
									</div>
								</div>
								<div class="BasicForm__row col-lg-2">
									<div class="input-group">
										<label class="form-label">Outline Color:</label>
										<input type="color" class="form-control name color-pick name" name="tw_button_outline_color" value="<?php echo $tw_button_outline_color; ?>">
									</div>
								</div>
								<div class="BasicForm__row col-lg-2">
									<div class="input-group">
										<label class="form-label">Text Color:</label>
										<input type="color" class="form-control name color-pick name" name="tw_buttontxt_color" value="<?php echo $tw_buttontxt_color; ?>">
									</div>
								</div>
							</div>
						</div>

						<div class="col-12">
							<div class="row">
								<div class="BasicForm__row col-lg-6 radio-btns-wrapper">
									<label class="form-label">Button Size:</label>
									<div class="form-check form-check-inline">
										<input class="form-check-input" type="radio" name="tw_button_size" id="tw_button_size_small" value="sm" <?= checked('sm', $tw_button_size); ?> />
										<label class="form-check-label" for="tw_button_size_small">Small</label>
									</div>
									<div class="form-check form-check-inline">
										<input class="form-check-input" type="radio" name="tw_button_size" id="tw_button_size_medium" value="md" <?= checked('md', $tw_button_size); ?> />
										<label class="form-check-label" for="tw_button_size_medium">Medium</label>
									</div>
									<div class="form-check form-check-inline">
										<input class="form-check-input" type="radio" name="tw_button_size" id="tw_button_size_large" value="lg" <?= checked('lg', $tw_button_size); ?> />
										<label class="form-check-label" for="tw_button_size_large">Large</label>
									</div>
								</div>
							</div>
						</div>

						<div class="col-12">
							<div class="row">
								<div class="BasicForm__row col-lg-6 radio-btns-wrapper">
									<label class="form-label">Button Border Type:</label>
									<div class="form-check form-check-inline">
										<input class="form-check-input" type="radio" name="tw_button_border_type" id="tw_button_border_type_square" value="square" <?= checked('square', $tw_button_border_type); ?> />
										<label class="form-check-label" for="tw_button_border_type_square">Square Corners</label>
									</div>
									<div class="form-check form-check-inline">
										<input class="form-check-input" type="radio" name="tw_button_border_type" id="tw_button_border_type_round" value="round" <?= checked('round', $tw_button_border_type); ?> />
										<label class="form-check-label" for="tw_button_border_type_round">Round Corners</label>
									</div>
									
								</div>
							</div>
						</div>
						<div class="col-lg-2 mr-auto">
							<input type="submit" class="btn btn-outline-primary py-3 px-5" value="Save Button">
						</div>
						
					</div>
				</form>
			</div>
			<div class="tab-pane fade <?php echo ($action=="videos")?'show active':''; ?>" id="videos" role="tabpanel" aria-labelledby="videos">
				<?php
						$no = isset($_GET['action']) ? $_GET['action'] : '';
						if($no == 'videos'){
						$no = $specific_no;
						}else $no = '';
				?>
				<form class="wt-save-admin-settings-videos mt-3 form-wrap">
					<div class="row">
						<input type="hidden" name="action" value="wt_save_form_fields_for_videos">
						<input type="hidden" name="active_action" value="videos">
						<input type="hidden" class="updateingdata" name="updateandedit_data" value="<?php echo $no; ?>">
						<div class="col-12">
							<div class="row">
								<div class="BasicForm__row col-lg-6 radio-btns-wrapper">
									<label class="form-label">Video Type:</label>
									<div class="form-check form-check-inline">
										<input class="form-check-input video_type" type="radio" name="video_type" id="video_type_upload" value="upload_video" <?= checked('upload_video', $video_type); ?> />
										<label class="form-check-label" for="video_type_upload">Upload Video</label>
									</div>
									<div class="form-check form-check-inline">
										<input class="form-check-input video_type" type="radio" name="video_type" id="video_type_youtube" value="youtube" <?= checked('youtube', $video_type); ?> />
										<label class="form-check-label" for="video_type_youtube">Youtube Video</label>
									</div>
									<div class="form-check form-check-inline">
										<input class="form-check-input video_type" type="radio" name="video_type" id="video_type_vimeo" value="vimeo" <?= checked('vimeo', $video_type); ?> />
										<label class="form-check-label" for="video_type_vimeo">Vimeo Video</label>
									</div>
								</div>
							</div>
						</div>
						<div class="col-12 upload_video_wrapper" style="<?php echo ($video_type!='upload_video')?'display:none':''; ?>">
							<div class="row">
								<div class="BasicForm__row col-lg-2">
									<div class="input-group">
										<input type="hidden" class="video-poster-img-source" name="video_poster_img_source" value="<?php echo $video_poster_img_source; ?>" />
										<input type="hidden" class="video-poster-img-id" name="video_poster_img_id" value="<?php echo $video_poster_img_id; ?>" />

										<label class="form-label">Poster Image</label>
										<button class="btn btn-outline-primary w-100 video-poster-image-js upload-btn-padding">
											Upload Image
										</button>
									</div>
								</div>
								<div class="BasicForm__row col-lg-4 video-poster-img-wrapper">
									<?php
									if($video_poster_img_source!=""): ?>
										<img class="video-poster-img" style="width: auto; height:100px;" src="<?php echo $video_poster_img_source; ?>" />
									<?php endif; ?>
								</div>
							</div>
						</div>
						<div class="col-12 upload_video_wrapper" style="<?php echo ($video_type!='upload_video')?'display:none':''; ?>">
							<div class="row">
								<div class="BasicForm__row col-lg-2">
									<div class="input-group">
										<label class="form-label">Upload Video (MP4)</label>
										<button class="btn btn-outline-primary w-100 video-upload-btn upload-btn-padding" data-video-type="video/mp4" >
											Upload Video
										</button>
									</div>
								</div>
								<div class="BasicForm__row col-lg-4 video-url-mp4-wrapper">
									<div class="input-group">
										<label class="form-label">Video MP4 URL</label>
										<input type="hidden" class="form-control video-id-mp4" name="video_id_mp4" value="<?php echo $video_id_mp4; ?>" />
										<input type="text" class="form-control video-url-mp4" name="video_url_mp4" value="<?php echo $video_url_mp4; ?>" disabled  />
									</div>
								</div>
							</div>
						</div>

						<div class="col-12 upload_video_wrapper" style="<?php echo ($video_type!='upload_video')?'display:none':''; ?>">
							<div class="row">
								<div class="BasicForm__row col-lg-2">
									<div class="input-group">
										<label class="form-label">Upload Video (OGV)</label>
										<button class="btn btn-outline-primary w-100 video-upload-btn upload-btn-padding" data-video-type="video/ogg" >
											Upload Video
										</button>
									</div>
								</div>
								<div class="BasicForm__row col-lg-4 video-url-ogv-wrapper">
									<div class="input-group">
										<label class="form-label">Video OGV URL</label>
										<input type="hidden" class="form-control video-id-ogv" name="video_id_ogv" value="<?php echo $video_id_ogv; ?>" />

										<input type="text" class="form-control video-url-ogv" name="video_url_ogv" value="<?php echo $video_url_ogv; ?>" disabled  />
									</div>
								</div>
							</div>
						</div>

						<div class="col-12 upload_video_wrapper" style="<?php echo ($video_type!='upload_video')?'display:none':''; ?>">
							<div class="row">
								<div class="BasicForm__row col-lg-2">
									<div class="input-group">
										
										<label class="form-label">Upload Video (WebM)</label>
										<button class="btn btn-outline-primary w-100 video-upload-btn upload-btn-padding" data-video-type="video/webm" >
											Upload Video
										</button>
									</div>
								</div>
								<div class="BasicForm__row col-lg-4 video-url-webm-wrapper">
									<div class="input-group">
										<label class="form-label">Video WebM URL</label>
										<input type="hidden" class="form-control video-id-webm" name="video_id_webm" value="<?php echo $video_id_webm; ?>" />
										<input type="text" class="form-control video-url-webm" name="video_url_webm" value="<?php echo $video_url_webm; ?>" disabled  />
									</div>
								</div>
							</div>
						</div>

						<div class="col-12 youtube_wrapper" style="<?php echo ($video_type!='youtube')?'display:none':''; ?>">
							<div class="row">
								<div class="BasicForm__row col-lg-4">
									<div class="input-group">
										<label class="form-label">YouTube URL</label>
										<input type="text" class="form-control video-url-youtube" name="video_url_youtube" placeholder="https://www.youtube.com/embed/{video-id}" value="<?php echo $video_url_youtube; ?>"  />
									</div>
								</div>
							</div>
						</div>

						<div class="col-12 vimeo_wrapper" style="<?php echo ($video_type!='vimeo')?'display:none':''; ?>">
							<div class="row">
								<div class="BasicForm__row col-lg-4">
									<div class="input-group">
										<label class="form-label">Vimeo URL</label>
										<input type="text" class="form-control video-url-vimeo" name="video_url_vimeo" value="<?php echo $video_url_vimeo; ?>"  />
									</div>
								</div>
							</div>
						</div>

						<div class="col-12">
							<div class="row">
								<div class="BasicForm__row col-lg-2">
									<div class="input-group">
										<label class="form-label">Video Width:</label>
										<div class="input-prefix">
											<input type="number" class="form-control name" placeholder="320"name="video_width" value="<?php echo $video_width; ?>" min="1" max="1920" />
											<span>Ex.</span>
										</div>
									</div>
								</div>
								<div class="BasicForm__row col-lg-2">
									<div class="input-group">
										<label class="form-label">Video Height:</label>
										<div class="input-prefix">
											<input type="number" class="form-control name" placeholder="240"name="video_height" value="<?php echo $video_height; ?>" min="1" max="700" />
											<span>Ex.</span>
										</div>
									</div>
								</div>
								
							</div>
						</div>
						<div class="col-12">
							<div class="row">
								<div class="BasicForm__row col-lg-6 radio-btns-wrapper">
									<label class="form-label">Video Autoplay:</label>
									<div class="form-check form-check-inline">
										<input class="form-check-input video_autoplay" type="radio" name="video_autoplay" id="video_autoplay_yes" value="yes" <?= checked('yes', $video_autoplay); ?> />
										<label class="form-check-label" for="video_autoplay_yes">Yes</label>
									</div>
									<div class="form-check form-check-inline">
										<input class="form-check-input video_autoplay" type="radio" name="video_autoplay" id="video_autoplay_no" value="no" <?= checked('no', $video_autoplay); ?> />
										<label class="form-check-label" for="video_autoplay_no">No</label>
									</div>
								</div>
							</div>
						</div>
						
						<div class="col-12">
							<div class="row">
								<div class="BasicForm__row col-lg-6 radio-btns-wrapper">
									<label class="form-label">Video Muted:</label>
									<div class="form-check form-check-inline">
										<input class="form-check-input video_muted" type="radio" name="video_muted" id="video_muted_yes" value="yes" <?= checked('yes', $video_muted); ?> />
										<label class="form-check-label" for="video_muted_yes">Yes</label>
									</div>
									<div class="form-check form-check-inline">
										<input class="form-check-input video_muted" type="radio" name="video_muted" id="video_muted_no" value="no" <?= checked('no', $video_muted); ?> />
										<label class="form-check-label" for="video_muted_no">No</label>
									</div>
								</div>
							</div>
						</div>
						<div class="col-12">
							<div class="row">
								<div class="BasicForm__row col-lg-6 radio-btns-wrapper">
									<label class="form-label">Show Video Controls:</label>
									<div class="form-check form-check-inline">
										<input class="form-check-input video_controls" type="radio" name="video_controls" id="video_controls_yes" value="yes" <?= checked('yes', $video_controls); ?> />
										<label class="form-check-label" for="video_controls_yes">Yes</label>
									</div>
									<div class="form-check form-check-inline">
										<input class="form-check-input video_controls" type="radio" name="video_controls" id="video_controls_no" value="no" <?= checked('no', $video_controls); ?> />
										<label class="form-check-label" for="video_controls_no">No</label>
									</div>
								</div>
							</div>
						</div>

						<div class="col-12">
							<div class="row">
								<div class="BasicForm__row col-lg-6 radio-btns-wrapper">
									<label class="form-label">Video Loop:</label>
									<div class="form-check form-check-inline">
										<input class="form-check-input video_loop" type="radio" name="video_loop" id="video_loop_yes" value="yes" <?= checked('yes', $video_loop); ?> />
										<label class="form-check-label" for="video_loop_yes">Yes</label>
									</div>
									<div class="form-check form-check-inline">
										<input class="form-check-input video_loop" type="radio" name="video_loop" id="video_loop_no" value="no" <?= checked('no', $video_loop); ?> />
										<label class="form-check-label" for="video_loop_no">No</label>
									</div>
								</div>
							</div>
						</div>

						<div class="col-lg-2 mr-auto">
							<input type="submit" class="btn btn-outline-primary py-3 px-5" value="Save Video">
						</div>
						
					</div>
				</form>
			</div>
			
		</div>
	</section>
</div>