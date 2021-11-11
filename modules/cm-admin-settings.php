<?php
	$saved_random_nos = get_option('get_saved_random_numbers');
	$specific_no = isset($_GET['rand_id']) ? $_GET['rand_id'] : '';
	
	// For testimonial
	$data = get_option('get_testimonials_'.$specific_no);
	$testi_img_src = isset($data['testi_img_src']) ? $data['testi_img_src'] : '';
	$tw_testi_content = isset($data['tw_testi_content']) ? $data['tw_testi_content'] : '';
	$tw_testi_name = isset($data['tw_testi_name']) ? $data['tw_testi_name'] : '';
	$tw_testi_position = isset($data['tw_testi_position']) ? $data['tw_testi_position'] : '';
	$tw_box_color = isset($data['tw_box_color']) ? $data['tw_box_color'] : '#ffffff';
	$tw_font_color = isset($data['tw_font_color']) ? $data['tw_font_color'] : '#ffffff';
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
	// For Buttons
	
?>
<!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script> -->
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
				<a class="nav-link active" data-toggle="tab" href="#wt_testimonial" role="tab" aria-controls="Testimonial" aria-selected="true">Testimonial</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" data-toggle="tab" href="#google_maps" role="tab" aria-controls="Google Maps" aria-selected="false">Google Maps</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" data-toggle="tab" href="#button_wt" role="tab" aria-controls="Button Settings" aria-selected="false">Button Settings</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" data-toggle="tab" href="#saved_testimonials" role="tab" aria-controls="All Saved Testimonials/Maps/Buttons" aria-selected="false">Saved Testimonials/Maps/Buttons</a>
			</li>
		</ul>
		<div class="tab-content" id="myTabContent">
			<div class="tab-pane fade show active" id="wt_testimonial" role="tabpanel" aria-labelledby="wt_testimonial">
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
								<input type="hidden" class="form-control img-source" name="img_source" value="<?php echo $testi_img_src; ?>">
								<input type="hidden" class="updateingdata" name="updateandedit_data" value="<?php echo $no; ?>">
								<label class="form-label">Testimonial Image</label>
								<button class="btn btn-outline-primary w-100 upload-image-js">
								Upload Image
								<img class="testimonial-img" style="width: 62px;" src="<?php echo $testi_img_src; ?>" />
								</button>
							</div>
							<div class="BasicForm__row">
								<div class="input-group">
									<label class="form-label">Client Name:</label>
									<div class="input-prefix">
										<input type="text" class="form-control name" name="tw_testi_name" placeholder="Robbert Aleex." value="<?php echo $tw_testi_name; ?>">
										<span>Ex.</span>
									</div>
								</div>
							</div>
							<div class="BasicForm__row">
								<label class="form-label">Box Color:</label>
								<input type="color" class="form-control name color-pick" name="tw_box_color" value="<?php echo $tw_box_color; ?>">
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
							<div class="BasicForm__row col-lg-2 p-0">
								<div class="input-group">
									<label class="form-label">Client Position:</label>
									<div class="input-prefix">
										<input type="text" class="form-control name" name="tw_testi_position" placeholder="Founder" value="<?php echo $tw_testi_position; ?>">
										<span>Ex.</span>
									</div>
								</div>
								
							</div>
							<div class="BasicForm__row col-lg-2 p-0">
								<label class="form-label">Font Color:</label>
								<input type="color" class="form-control color-pick name" name="tw_font_color" value="<?php echo $tw_font_color; ?>">
							</div>
						</div>
						<div class="col-lg-2 mr-auto">
							<input type="submit" class="btn btn-outline-primary py-3 px-4 cm-custom-btn" value="Save Testimonial">
						</div>
					</div>
					
					
				</form>
			</div>
			<div class="tab-pane fade" id="google_maps" role="tabpanel" aria-labelledby="google_maps">
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
								<input type="text" class="form-control name" name="tw_maps_apikey" placeholder="Lorem Ipsome" value="<?php echo $tw_maps_apikey; ?>">
								<span>Ex.</span>
							</div>
						</div>
						<input type="submit" class="btn btn-outline-primary py-3 px-4 cm-custom-btn" value="Save API Key">
					</div>
				</form>
			</div>
			<!-- Button settings -->
			<div class="tab-pane fade" id="button_wt" role="tabpanel" aria-labelledby="button_wt">
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
								<div class="BasicForm__row col-lg-2">
									<div class="input-group">
										<label class="form-label">Button Text:</label>
										<div class="input-prefix">
											<input type="text" class="form-control name" placeholder="Next"name="tw_btn_text" value="<?php echo $tw_btn_text; ?>">
											<span>Ex.</span>
										</div>
									</div>
								</div>
								<div class="BasicForm__row col-lg-2">
									<div class="input-group">
										<label class="form-label">Button Link:</label>
										<div class="input-prefix">
											<input type="text" class="form-control name" placeholder="Florem"name="tw_btn_link" value="<?php echo $tw_btn_link; ?>">
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
										<label class="form-label">Text Color:</label>
										<input type="color" class="form-control name color-pick name" name="tw_buttontxt_color" value="<?php echo $tw_buttontxt_color; ?>">
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
			<!-- Saved TEstimonails -->
			<div class="tab-pane fade" id="saved_testimonials" role="tabpanel" aria-labelledby="saved_testimonials">
				
				<?php
					wt_load_templates('all-saved-testimonials.php');
				?>
			</div>
		</div>
	</section>
</div>