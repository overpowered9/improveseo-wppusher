<?php
$saved_random_nos = get_option('get_saved_random_numbers');

$search_shortcode = isset($_GET['search_shortcode']) ? $_GET['search_shortcode'] : '';
$search_shortcode_type = isset($_GET['search-shortcode-type']) ? $_GET['search-shortcode-type'] : array();

$specific_no = isset($_GET['rand_id']) ? $_GET['rand_id'] : '';
$action = isset($_GET['action']) ? $_GET['action'] : '';

// For testimonial
$data = get_option('get_testimonials_' . $specific_no);
$tw_testi_shortcode_name = isset($data['tw_testi_shortcode_name']) ? $data['tw_testi_shortcode_name'] : '';
$testi_img_src = isset($data['testi_img_src']) ? $data['testi_img_src'] : '';
$tw_testi_content = isset($data['tw_testi_content']) ? $data['tw_testi_content'] : '';
$tw_testi_name = isset($data['tw_testi_name']) ? $data['tw_testi_name'] : '';
$tw_testi_position = isset($data['tw_testi_position']) ? $data['tw_testi_position'] : '';
$tw_box_color = isset($data['tw_box_color']) ? $data['tw_box_color'] : '#ffffff';
$tw_font_color = isset($data['tw_font_color']) ? $data['tw_font_color'] : '#ffffff';
$tw_testi_outline_color = isset($data['tw_testi_outline_color']) ? $data['tw_testi_outline_color'] : '#ffffff';
// for testimonial

// for google_maps
$data_gm = get_option('get_googlemaps_' . $specific_no);
$tw_maps_shortcode_name = isset($data_gm['tw_maps_shortcode_name']) ? $data_gm['tw_maps_shortcode_name'] : '';
$tw_maps_apikey = isset($data_gm['tw_maps_apikey']) ? $data_gm['tw_maps_apikey'] : '';
// for google_maps

// For Buttons
$data_btn = get_option('get_buttons_' . $specific_no);

$tw_button_shortcode_name = isset($data_btn['tw_button_shortcode_name']) ? $data_btn['tw_button_shortcode_name'] : '';
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
$data_video = get_option('get_videos_' . $specific_no);
$video_shortcode_name = isset($data_video['video_shortcode_name']) ? $data_video['video_shortcode_name'] : '';
$video_type = isset($data_video['video_type']) ? $data_video['video_type'] : 'upload_video';
$video_poster_img_source = isset($data_video['video_poster_img_source']) ? $data_video['video_poster_img_source'] : '';
$video_poster_img_id = isset($data_video['video_poster_img_id']) ? $data_video['video_poster_img_id'] : '';

$video_id_mp4 = isset($data_video['video_id_mp4']) ? $data_video['video_id_mp4'] : '';
$video_url_mp4 = isset($data_video['video_url_mp4']) ? $data_video['video_url_mp4'] : '';

$video_id_ogv = isset($data_video['video_id_ogv']) ? $data_video['video_id_ogv'] : '';
$video_url_ogv = isset($data_video['video_url_ogv']) ? $data_video['video_url_ogv'] : '';

$video_id_webm = isset($data_video['video_id_webm']) ? $data_video['video_id_webm'] : '';
$video_url_webm = isset($data_video['video_url_webm']) ? $data_video['video_url_webm'] : '';

$video_url_vimeo = isset($data_video['video_url_vimeo']) ? $data_video['video_url_vimeo'] : '';
$video_url_youtube = isset($data_video['video_url_youtube']) ? $data_video['video_url_youtube'] : '';

$video_autoplay = isset($data_video['video_autoplay']) ? $data_video['video_autoplay'] : 'no';
$video_muted = isset($data_video['video_muted']) ? $data_video['video_muted'] : 'no';
$video_controls = isset($data_video['video_controls']) ? $data_video['video_controls'] : 'no';
$video_loop = isset($data_video['video_loop']) ? $data_video['video_loop'] : 'no';
$video_height = isset($data_video['video_height']) ? $data_video['video_height'] : 'auto';
$video_width = isset($data_video['video_width']) ? $data_video['video_width'] : '100%';

?>
<div class="global-wrap" style="max-width: 99% !important;">
	<div class="head-bar">
		<img src="<?php echo WT_URL . '/assets/images/latest-images/seo-latest-logo.svg'; ?>" alt="project-list-logo">
		<h1> ImproveSEO | 2.0.11 </h1>
		<span>Pro</span>
	</div>
	<div class="box-top">
		<ul class="breadcrumb-seo">
			<li><a href="#">Improve SEO</a></li>
			<li>Shortcodes</li>
		</ul>
	</div>

	<div class="setting-seo-tab">
		<!-- Tabs & Search -->
		<div class="setting-seo-tab-header">
			<div class="setting-seo-tabs">
				<button
					class="setting-seo-tab-btn <?php echo ($action != "googlemaps" && $action != "buttons" && $action != "videos" && $action != "testimonial") ? 'active' : ''; ?>"
					data-tab="setting-seo-shortcodes" data-toggle="tab" href="#saved_testimonials" role="tab"
					aria-controls="All Saved Shortcodes" aria-selected="false">Saved Shortcodes</button>
				<button class="setting-seo-tab-btn <?php echo ($action == "testimonial") ? 'active' : ''; ?> "
					data-tab="setting-seo-testimonials" data-toggle="tab" href="#wt_testimonial" role="tab"
					aria-controls="Testimonial" aria-selected="true">Testimonials</button>
				<button class="setting-seo-tab-btn  <?php echo ($action == "googlemaps") ? 'active' : ''; ?> "
					data-tab="setting-seo-maps" data-toggle="tab" href="#google_maps" role="tab"
					aria-controls="Google Maps" aria-selected="false">Google Maps</button>
				<button class="setting-seo-tab-btn  <?php echo ($action == "buttons") ? 'active' : ''; ?> "
					data-tab="setting-seo-button" data-toggle="tab" href="#button_wt" role="tab"
					aria-controls="Button Settings" aria-selected="false">Button</button>
				<button class="setting-seo-tab-btn <?php echo ($action == "videos") ? 'active' : ''; ?>"
					data-tab="setting-seo-videos" data-toggle="tab" href="#videos" role="tab" aria-controls="Videos"
					aria-selected="false">Videos</button>
			</div>

			<!-- Search Bar -->
			<div class="setting-seo-search-container">
				<input type="text" class="setting-seo-search-input" placeholder="Search Shortcodes Here">
				<span class="setting-seo-search-icon"> <img
						src="<?php echo WT_URL . '/assets/images/latest-images/round-search.svg'; ?>"
						alt="round-search"> </span>
			</div>
		</div>

		<div id="setting-seo-testimonials"
			class="setting-seo-tab-content <?php echo ($action == "testimonial") ? 'show active' : ''; ?>"
			id="wt_testimonial" role="tabpanel" aria-labelledby="wt_testimonial">
			<?php
			$no = isset($_GET['action']) ? $_GET['action'] : '';
			if ($no == 'testimonial') {
				$no = $specific_no;
			} else
				$no = '';
			?>
			<div class="setting-testimonials-seo-box">
				<div class="setting-testimonials-left-box">
					<form class="improve-seo-form-global wt-save-admin-settings-testimonials">

						<div class="seo-form-field">
							<label for="testimonialName">Testimonial Name</label>
							<input type="text" name="tw_testi_shortcode_name" id="testimonialName"
								placeholder="Ex. Test 1" value="<?php echo $tw_testi_shortcode_name; ?>">
						</div>
						<div class="seo-form-field">
							<label for="testimonialContent">Testimonial Content</label>
							<textarea id="testimonialContent" name="tw_testi_content" placeholder="Ex. Test"
							style=" border-radius: 11px; height: 85px;" rows="1" value="<?php echo $tw_testi_content; ?>"><?php echo $tw_testi_content; ?></textarea>
							<!-- <input type="text" id="testimonialContent" name="tw_testi_content" placeholder="Ex. Test"
								style=" border-radius: 11px;" value="<?php echo $tw_testi_content; ?>"></input> -->
						</div>
						<div class="setting-tab-colss-col">
							<div class="seo-form-field">
								<label for="clientName">Client Name</label>
								<input type="text" name="tw_testi_name" value="<?php echo $tw_testi_name; ?>"
									id="clientName" placeholder="Ex. Anil">
							</div>
							<div class="seo-form-field">
								<label for="clientPosition">Client Position</label>
								<input type="text" name="tw_testi_position"
									value="<?php echo stripslashes($tw_testi_position); ?>" id="clientPosition"
									placeholder="Ex. CEO">
							</div>
						</div>

						<div class="seo-color-container">
							<!-- Box Color -->
							<div>
								<span class="seo-color-label">Box Color:</span>
								<div class="seo-color-picker">
									<div class="seo-color-box" id="boxColor" style="background: #A2845E;"></div>
									<input type="color" id="boxColorPicker" onchange="updateColor('boxColor', this)"
										name="tw_box_color" value="<?php echo $tw_box_color; ?>">
									<input type="text" id="boxColorCode" class="seo-color-input" readonly>
								</div>
							</div>

							<!-- Font Color -->
							<div>
								<span class="seo-color-label">Font Color:</span>
								<div class="seo-color-picker">
									<div class="seo-color-box" id="fontColor" style="background: #00A3E3;"></div>
									<input type="color" id="fontColorPicker" onchange="updateColor('fontColor', this)"
										name="tw_font_color" value="<?php echo $tw_font_color; ?>">
									<input type="text" id="fontColorCode" class="seo-color-input" readonly>
								</div>
							</div>

							<!-- Outline Color -->
							<div>
								<span class="seo-color-label">Outline Color:</span>
								<div class="seo-color-picker">
									<div class="seo-color-box" id="outlineColor" style="background: #6BBA41;"></div>
									<input type="color" id="outlineColorPicker"
										onchange="updateColor('outlineColor', this)" name="tw_testi_outline_color"
										value="<?php echo $tw_testi_outline_color; ?>">
									<input type="text" id="outlineColorCode" class="seo-color-input" readonly>
								</div>
							</div>
						</div>

						<div class="seo-form-field">
							<input type="submit" class="styling_post_page_action_buttons2 styling_post_page_action_buttons" value="Save Testimonial">
						</div>

				</div>
				<div class="setting-testimonials-right-box" style="margin-top: 25px;">
					<label class="form-label text-muted">Testimonial Image</label>
					<input type="hidden" name="action" value="wt_save_form_fields_for_testimonials">
					<input type="hidden" name="active_action" value="testimonial">
					<input type="hidden" class="img-source" name="img_source" value="<?php echo $testi_img_src; ?>">
					<input type="hidden" class="updateingdata" name="updateandedit_data" value="<?php echo $no; ?>">

					<div class="upload-box upload-image-js">
						<?php
						if ($testi_img_src != ""): ?>
							<img class="testimonial-img" style="width: 62px;" src="<?php echo $testi_img_src; ?>" />
						<?php endif; ?>
						<p>Drag & Drop Your File here</p>
						<div class="divider">or</div>
						<input type="file" id="file-upload">
						<label for="file-upload">Choose a File</label>
						<span> from your Storage</span>
					</div>
				</div>
				</form>
			</div>
		</div>

		<div id="setting-seo-maps"
			class="setting-seo-tab-content <?php echo ($action == "googlemaps") ? 'show active' : ''; ?>"
			id="google_maps" role="tabpanel" aria-labelledby="google_maps">
			<?php
			$no = isset($_GET['action']) ? $_GET['action'] : '';
			if ($no == 'googlemaps') {
				$no = $specific_no;
			} else
				$no = '';
			?>
			<form class="wt-save-admin-settings-googlemaps improve-seo-form-global">
				<div class="seo-form-field">
					<label> Shortcode Name </label>
					<input type="text" name="tw_maps_shortcode_name" placeholder="Ex. Map 1"
						value="<?php echo $tw_maps_shortcode_name; ?>">
				</div>
				<div class="seo-form-field">
					<label> Google Maps API Key </label>
					<input type="hidden" name="action" value="wt_save_form_fields_for_googlemaps">
					<input type="hidden" name="active_action" value="googlemap">
					<input type="hidden" class="updateingdata" name="updateandedit_data" value="<?php echo $no; ?>">
					<input type="text" name="tw_maps_apikey" placeholder="" value="<?php echo $tw_maps_apikey; ?>">
				</div>
				<div class="seo-form-field">
					<input type="submit" class="styling_post_page_action_buttons2 styling_post_page_action_buttons" value="Save API Key">
				</div>
			</form>
		</div>

		<div id="setting-seo-button"
			class="setting-seo-tab-content <?php echo ($action == "buttons") ? 'show active' : ''; ?>" id="button_wt"
			role="tabpanel" aria-labelledby="button_wt">
			<?php
			$no = isset($_GET['action']) ? $_GET['action'] : '';
			if ($no == 'buttons') {
				$no = $specific_no;
			} else
				$no = '';
			?>
			<div class="setting-seo-button-outer">
				<div class="setting-seo-button-left">
					<form class="wt-save-admin-settings-buttons improve-seo-form-global">
						<input type="hidden" name="action" value="wt_save_form_fields_for_buttons">
						<input type="hidden" name="active_action" value="button">
						<input type="hidden" class="updateingdata" name="updateandedit_data" value="<?php echo $no; ?>">
						<div class="seo-form-field">
							<label> Shortcode Name </label>
							<input type="text" placeholder="Ex. Button 1" name="tw_button_shortcode_name"
								value="<?php echo $tw_button_shortcode_name; ?>" />
						</div>
						<div class="seo-form-field">
							<div class="setting-toggle-group">
								<label>Button type :</label>
								<div class="setting-toggle-container">
									<label for="tw_button_type_normal" class="setting-toggle toggle_Nomarmal_Button "
										onclick="toggle(this)">Normal Button</label>

									<input class="form-check-input tw_button_type" type="radio" name="tw_button_type"
										id="tw_button_type_normal" value="normal_btn" <?= checked('normal_btn', $tw_button_type); ?> />

									<label for="tw_button_type_tap_to_call" class="setting-toggle toggle_Tap_to_call"
										onclick="toggle(this)">Tap to call</label>

									<input class="form-check-input tw_button_type" type="radio" name="tw_button_type"
										id="tw_button_type_tap_to_call" value="tap_to_call" <?= checked('tap_to_call', $tw_button_type); ?> />


								</div>
							</div>
						</div>
						<div class="setting-tab-colss-col" id="normal_btn_wrapper"
							style="<?php echo ($tw_button_type == "tap_to_call") ? 'display:none' : ''; ?>">
							<div class="seo-form-field">
								<label> Button Text </label>
								<input type="text" placeholder="Ex. Next" class="name" name="tw_btn_text"
									value="<?php echo $tw_btn_text; ?>">
							</div>
							<div class="seo-form-field">
								<label> Button Link </label>
								<input type="text" placeholder="Ex. https://google.com" class="name" name="tw_btn_link"
									value="<?php echo $tw_btn_link; ?>">
							</div>
						</div>
						<div class="row">
							<div class="col-12" id="tap_to_call_btn_wrapper"
								style="<?php echo ($tw_button_type == "normal_btn") ? 'display:none' : ''; ?>">
								<div class="row">
									<div class="BasicForm_row">
										<input type="hidden" class="tap-to-call-img-source"
											name="tw_tap_to_call_img_source"
											value="<?php echo $tw_tap_to_call_img_source; ?>" />
										<label style="padding: 10px 20px; color: rgba(80, 87, 94, 0.8); font-size: 18px;" class="seo-color-labe">Tap to Call Icon</label>
										<button  class="styling_post_page_action_buttons  tap-to-call-upload-image-js upload-btn-padding">
											Upload Image
											<?php
											if ($tw_tap_to_call_img_source != ""): ?>
												<img class="tap-to-call-img" style="width: 62px;"
													src="<?php echo $tw_tap_to_call_img_source; ?>" />
											<?php endif; ?>
										</button>
									</div>
									<div class="BasicForm__row">
										<div class="input-group" style="margin-top: 10px;">
											<label style="margin: 8px ;" class="seo-color-label">Button Text:</label>
											<div class="input-prefix">
												<input  style="margin-top: 10px;" type="text" class="form-control name" placeholder="Ex. Next"
													name="tw_tap_btn_text" value="<?php echo $tw_tap_btn_text; ?>">

											</div>
										</div>
									</div>
									<div class="BasicForm__row">
										<div class="input-group" style="margin-top: 10px;">
											<label  style="margin: 8px;" class="seo-color-label">Number:</label>
											<div class="input-prefix">
												<input  style="margin-top: 10px;" type="text" class="form-control name"
													placeholder="Ex. +1 1231231231" name="tw_tap_btn_number"
													value="<?php echo $tw_tap_btn_number; ?>" />

											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="seo-color-container">
							<!-- Box Color -->
							<div>
								<span class="seo-color-label">Box Color:</span>
								<div class="seo-color-picker">
									<div class="seo-color-box" id="boxColor_button" style="background: #ffffff;"></div>
									<input type="color" id="boxColorPicker" value="#ffffff"
										onchange="updateColor('boxColor_button', this)">
									<input type="text" id="boxColorCode" class="seo-color-input" value="#ffffff"
										readonly>
								</div>
							</div>

							<!-- Font Color -->
							<div>
								<span class="seo-color-label">Font Color:</span>
								<div class="seo-color-picker">
									<div class="seo-color-box" id="fontColor_button" style="background: #00A3E3;"></div>
									<input type="color" id="fontColorPicker" value="#00A3E3"
										onchange="updateColor('fontColor_button', this)">
									<input type="text" id="fontColorCode" class="seo-color-input" value="#00A3E3"
										readonly>
								</div>
							</div>

							<!-- Outline Color -->
							<div>
								<span class="seo-color-label">Outline Color:</span>
								<div class="seo-color-picker">
									<div class="seo-color-box" id="outlineColor_button" style="background: #6BBA41;">
									</div>
									<input type="color" id="outlineColorPicker" value="#6BBA41"
										onchange="updateColor('outlineColor_button', this)">
									<input type="text" id="outlineColorCode" class="seo-color-input" value="#6BBA41"
										readonly>
								</div>
							</div>
						</div>
						<div class="setting-toggle-group">
							<label>Button Size :</label>
							<div class="setting-toggle-container">

								<label for="tw_button_size_small" class="setting-toggle toggle_size_small"
									onclick="toggle(this)">Small</label>

								<input class="form-check-input" type="radio" name="tw_button_size"
									id="tw_button_size_small" value="sm" <?= checked('sm', $tw_button_size); ?> />

								<label for="tw_button_size_medium" class="setting-toggle toggle_size_medium"
									onclick="toggle(this)">Medium</label>

								<input class="form-check-input" type="radio" name="tw_button_size"
									id="tw_button_size_medium" value="md" <?= checked('md', $tw_button_size); ?> />

								<label for="tw_button_size_large" class="setting-toggle toggle_size_large"
									onclick="toggle(this)">Large</label>

								<input class="form-check-input" type="radio" name="tw_button_size"
									id="tw_button_size_large" value="lg" <?= checked('lg', $tw_button_size); ?> />

							</div>
						</div>
						<div class="setting-toggle-group">
							<label>Button Border Type:</label>
							<div class="setting-toggle-container">
								<label for="tw_button_border_type_square"
									class="setting-toggle toggle_border_type__square" onclick="toggle(this)">Square
									corners</label>
								<input class="form-check-input" type="radio" name="tw_button_border_type"
									id="tw_button_border_type_square" value="square" <?= checked('square', $tw_button_border_type); ?> />
								<label for="tw_button_border_type_round"
									class="setting-toggle toggle_border_type__round" onclick="toggle(this)">Round
									corners</label>
								<input class="form-check-input" type="radio" name="tw_button_border_type"
									id="tw_button_border_type_round" value="round" <?= checked('round', $tw_button_border_type); ?> />
							</div>
						</div>
						<div class="seo-form-field">
							<input type="submit" class="styling_post_page_action_buttons2 styling_post_page_action_buttons" value="Save Button">
						</div>
					</form>
				</div>
				<div class="setting-seo-button-right">
					<div class="setting-seo-button-preview">
						<h4> Button Preview </h4>
						<button> Button </button>
					</div>
				</div>
			</div>
		</div>

		<div id="setting-seo-videos"
			class="setting-seo-tab-content <?php echo ($action == "videos") ? 'show active' : ''; ?>" id="videos"
			role="tabpanel" aria-labelledby="videos">
			<?php
			$no = isset($_GET['action']) ? $_GET['action'] : '';
			if ($no == 'videos') {
				$no = $specific_no;
			} else
				$no = '';
			?>
			<div class="setting-video-tab">
				<div class="setting-video-tab-lft">
					<form class="wt-save-admin-settings-videos improve-seo-form-global">
						<input type="hidden" name="action" value="wt_save_form_fields_for_videos">
						<input type="hidden" name="active_action" value="videos">
						<input type="hidden" class="updateingdata" name="updateandedit_data" value="<?php echo $no; ?>">
						<div class="seo-form-field">
							<label> Shortcode Name </label>
							<input type="text" name="video_shortcode_name" class="form-control name"
								value="<?php echo $video_shortcode_name; ?>" placeholder="Shortcode Name" />
						</div>
						<div class="seo-form-field">
							<div class="setting-toggle-group fullwidth">
								<label>Video Type:</label>
								<div class="setting-toggle-container">
									<label for="video_type_upload" class="setting-toggle  toggle_video_type_upload"
										onclick="toggle(this)">Upload video</label>
									<input class="form-check-input video_type" type="radio" name="video_type"
										id="video_type_upload" value="upload_video" <?= checked('upload_video', $video_type); ?> />

									<label for="video_type_youtube" class="setting-toggle toggle_video_type_youtube"
										onclick="toggle(this)">Youtube video</label>
									<input class="form-check-input video_type" type="radio" name="video_type"
										id="video_type_youtube" value="youtube" <?= checked('youtube', $video_type); ?> />

									<label for="video_type_vimeo" class="setting-toggle toggle_video_type_vimeo"
										onclick="toggle(this)">Vimeo
										Video</label>
									<input class="form-check-input video_type" type="radio" name="video_type"
										id="video_type_vimeo" value="vimeo" <?= checked('vimeo', $video_type); ?> />
								</div>
							</div>
						</div>
						<div class="seo-form-field">
						<label style="padding: 10px 20px;"> Upload video preview image </label>
							<div class="setting-upload-box video-poster-image-js">
							<!-- <div class="BasicForm__row col-lg-4 video-poster-img-wrapper"> -->
							<span id="file-name" class="file-name"><?php
								if ($video_poster_img_source != ""): ?>
										<img class="video-poster-img" style="width: auto; height:100px;"
											src="<?php echo $video_poster_img_source; ?>" />
									<?php endif; ?></span>
							<!-- </div> -->
								Drag & Drop Your File here <span> OR </span>
								<input type="hidden" class="video-poster-img-source " name="video_poster_img_source"
									value="<?php echo $video_poster_img_source; ?>" />
								<input type="hidden" class="video-poster-img-id" name="video_poster_img_id"
									value="<?php echo $video_poster_img_id; ?>" />

								<label for="file-upload" class="custom-file-upload">Upload Preview Image</label>
								<input id="file-upload" type="file" class="file-input" onchange="updateFileName()">
								
							</div>
							</div>
						<div class="seo-form-field upload_video_wrapper" style="margin-bottom: 40px;"
							style="<?php echo ($video_type != 'upload_video') ? 'display:none' : ''; ?>">
							
							<label> Upload video file </label>
							<div class="setting-upload-box video-upload-btn"
								style="<?php echo ($video_type != 'upload_video') ? 'display:none' : ''; ?>">
								Drag & Drop Your File here <span> OR </span>
								<label for="file-upload" class="custom-file-upload">Upload Video File (MP4)</label>
								<input id="file-upload" type="file" class="file-input" onchange="updateFileName()"
									data-video-type="video/mp4">
								<span id="file-name" class="file-name"></span>
								
							</div>
							<div class="seo-form-field"
							style="<?php echo ($video_type != 'upload_video') ? 'display:none' : ''; ?>">
							<label> Video URL </label>
							<input type="hidden" class="form-control video-id-mp4" name="video_id_mp4"
								value="<?php echo $video_id_mp4; ?>" readonly/>
							<input type="text" placeholder="Video MP4 Link" class="form-control video-url-mp4"
								name="video_url_mp4" value="<?php echo $video_url_mp4; ?>" readonly />
						</div>
						</div>
						<div class="row">
							<div class="col-12 youtube_wrapper"
								style="<?php echo ($video_type != 'youtube') ? 'display:none' : ''; ?>">
								<div class="row">
									<div class=" col-lg">
										<div class="input-group">
											<label class="form-label" style="padding-left: 20px; color: rgba(80, 87, 94, 0.8); font-size: 18px;">YouTube URL</label>
											<input type="text" class="form-control video-url-youtube"
												name="video_url_youtube"
												placeholder="https://www.youtube.com/embed/{video-id}"
												value="<?php echo $video_url_youtube; ?>" />
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-12 vimeo_wrapper"
								style="<?php echo ($video_type != 'vimeo') ? 'display:none' : ''; ?>">
								<div class="row">
									<div class="col-lg">
										<div class="input-group">
											<label class="form-label" style="padding-left: 20px; color: rgba(80, 87, 94, 0.8); font-size: 18px;">Vimeo URL</label>
											<input type="text" class="form-control video-url-vimeo"
												name="video_url_vimeo" value="<?php echo $video_url_vimeo; ?>" />
										</div>
									</div>
								</div>
							</div>
						</div>
						
						<div class="setting-tab-colss-col">
							<div class="seo-form-field">
								<label> Video width </label>
								<input type="number" class="name" placeholder="800" name="video_width"
									value="<?php echo $video_width; ?>" min="1" max="1920" />
							</div>
							<div class="setting-cross">
								<label> &nbsp; </label>
								<img src="<?php echo WT_URL . '/assets/images/latest-images/cross-x.svg'; ?>"
									alt="cross-x">
							</div>
							<div class="seo-form-field">
								<label> Video height </label>
								<input type="number" class="name" placeholder="800" name="video_height"
									value="<?php echo $video_height; ?>" min="1" max="700" />
							</div>
						</div>

						<div class="setting-toggle-group">
							<label>Video Auto play:</label>
							<div class="setting-toggle-container">
								<input class="form-check-input video_autoplay" type="radio" name="video_autoplay"
									id="video_autoplay_yes" value="yes" <?= checked('yes', $video_autoplay); ?> />
								<label for="video_autoplay_yes" class="setting-toggle toggle_video_autoplay_yes"
									onclick="toggle(this)">Yes</label>
								<input class="form-check-input video_autoplay" type="radio" name="video_autoplay"
									id="video_autoplay_no" value="no" <?= checked('no', $video_autoplay); ?> />
								<label for="video_autoplay_no" class="setting-toggle toggle_video_autoplay_no"
									onclick="toggle(this)">No</label>
							</div>
						</div>
						<div class="setting-toggle-group">
							<label>Video Muted:</label>
							<div class="setting-toggle-container">
								<label for="video_muted_yes" class="setting-toggle toggle_video_muted_yes"
									onclick="toggle(this)">Yes</label>
								<input class="form-check-input video_muted" type="radio" name="video_muted"
									id="video_muted_yes" value="yes" <?= checked('yes', $video_muted); ?> />
								<label for="video_muted_no" class="setting-toggle toggle_video_muted_no"
									onclick="toggle(this)">No</label>
								<input class="form-check-input video_muted" type="radio" name="video_muted"
									id="video_muted_no" value="no" <?= checked('no', $video_muted); ?> />
							</div>
						</div>
						<div class="setting-toggle-group">
							<label>Video Controls:</label>
							<div class="setting-toggle-container">
								<label for="video_controls_yes" class="setting-toggle toggle_video_controls_yes"
									onclick="toggle(this)">Yes</label>
								<input class="form-check-input video_controls" type="radio" name="video_controls"
									id="video_controls_yes" value="yes" <?= checked('yes', $video_controls); ?> />
								<label for="video_controls_no" class="setting-toggle toggle_video_controls_no"
									onclick="toggle(this)">No</label>
								<input class="form-check-input video_controls" type="radio" name="video_controls"
									id="video_controls_no" value="no" <?= checked('no', $video_controls); ?> />
							</div>
						</div>
						<div class="setting-toggle-group">
							<label>Video Loop:</label>
							<div class="setting-toggle-container">
								<label for="video_loop_yes" class="setting-toggle toggle_video_loop_yes"
									onclick="toggle(this)">Yes</label>
								<input class="form-check-input video_loop" type="radio" name="video_loop"
									id="video_loop_yes" value="yes" <?= checked('yes', $video_loop); ?> />
								<label for="video_loop_no" class="setting-toggle toggle_video_loop_no" onclick="toggle(this)">No</label>
								<input class="form-check-input video_loop" type="radio" name="video_loop"
									id="video_loop_no" value="no" <?= checked('no', $video_loop); ?> />
							</div>
						</div>
						<div class="seo-form-field">
							<input type="submit" class="styling_post_page_action_buttons2 styling_post_page_action_buttons" value="Save Video">
						</div>
					</form>
				</div>
				<div class="setting-video-tab-rgt">
					<h4> Video Preview </h4>
					<img src="<?php echo WT_URL . '/assets/images/latest-images/video.png'; ?>" alt="video">
				</div>

			</div>
		</div>
	</div>
</div>
<!-- Tab Content -->
<div id="setting-seo-shortcodes"
	class="setting-seo-tab-content  <?php echo ($action != "googlemaps" && $action != "buttons" && $action != "videos" && $action != "testimonial") ? 'show active' : ''; ?>"
	id="saved_testimonials" role="tabpanel" aria-labelledby="saved_testimonials" style="max-width: 99%;">
	<?php
	wt_load_templates('all-saved-testimonials.php');
	?>

</div>

<script>
	document.addEventListener("DOMContentLoaded", function () {
		const tabs = document.querySelectorAll(".setting-seo-tab-btn");
		const contents = document.querySelectorAll(".setting-seo-tab-content");

		tabs.forEach(tab => {
			tab.addEventListener("click", function () {
				// Remove active class from all tabs
				tabs.forEach(t => t.classList.remove("active"));
				// Hide all content sections
				contents.forEach(c => c.classList.remove("active"));

				// Add active class to clicked tab
				this.classList.add("active");
				// Show corresponding content
				document.getElementById(this.dataset.tab).classList.add("active");
			});
		});
	});
	function toggle(el) {
		let parent = el.parentElement;
		let siblings = parent.querySelectorAll(".setting-toggle");
		siblings.forEach(btn => btn.classList.remove("active"));
		el.classList.add("active");
	}
	function updateFileName() {
		const input = document.getElementById('file-upload');
		const fileNameDisplay = document.getElementById('file-name');
		fileNameDisplay.textContent = input.files.length > 0 ? input.files[0].name : '';
	}
	function updateColor(elementId, input) {
		document.getElementById(elementId).style.background = input.value;
		document.getElementById(elementId + "Code").value = input.value.toUpperCase();
	}





	//                       for button tab secton

	document.addEventListener("DOMContentLoaded", function () {
		const normalButtonChecked = document.getElementById('tw_button_type_normal').checked;
		const tapToCallChecked = document.getElementById('tw_button_type_tap_to_call').checked;

		if (!normalButtonChecked && !tapToCallChecked) {
			document.getElementById('tw_button_type_normal').checked = true;
			document.querySelectorAll(".toggle_Nomarmal_Button").forEach(el => el.classList.add("active"));
			alert('nothing is checked');
		}

		if (normalButtonChecked) {
			// Activate Normal Button, deactivate Tap to Call
			document.querySelectorAll(".toggle_Nomarmal_Button").forEach(el => el.classList.add("active"));
			document.querySelectorAll(".toggle_Tap_to_call").forEach(el => el.classList.remove("active"));
			console.log('Normal button selected');
		}
		else if (tapToCallChecked) {
			// Activate Tap to Call, deactivate Normal Button
			document.querySelectorAll(".toggle_Tap_to_call").forEach(el => el.classList.add("active"));
			document.querySelectorAll(".toggle_Nomarmal_Button").forEach(el => el.classList.remove("active"));
			console.log('Tap to Call selected');
		}
	});

	document.addEventListener("DOMContentLoaded", function () {
		// Handle button size toggles
		const smallButtonChecked = document.getElementById('tw_button_size_small').checked;
		const mediumButtonChecked = document.getElementById('tw_button_size_medium').checked;
		const largeButtonChecked = document.getElementById('tw_button_size_large').checked;

		// If none are checked, default to medium
		if (!smallButtonChecked && !mediumButtonChecked && !largeButtonChecked) {
			document.getElementById('tw_button_size_medium').checked = true;
			document.querySelectorAll(".toggle_size_medium").forEach(el => el.classList.add("active"));
		}

		if (smallButtonChecked) {
			document.querySelectorAll(".toggle_size_small").forEach(el => el.classList.add("active"));
			document.querySelectorAll(".toggle_size_medium, .toggle_size_large").forEach(el => el.classList.remove("active"));
		}
		else if (mediumButtonChecked) {
			document.querySelectorAll(".toggle_size_medium").forEach(el => el.classList.add("active"));
			document.querySelectorAll(".toggle_size_small, .toggle_size_large").forEach(el => el.classList.remove("active"));
		}
		else if (largeButtonChecked) {
			document.querySelectorAll(".toggle_size_large").forEach(el => el.classList.add("active"));
			document.querySelectorAll(".toggle_size_small, .toggle_size_medium").forEach(el => el.classList.remove("active"));
		}
	});

	document.addEventListener("DOMContentLoaded", function () {
		// Handle border type toggle
		const squareBorderChecked = document.getElementById('tw_button_border_type_square').checked;
		const roundBorderChecked = document.getElementById('tw_button_border_type_round').checked;

		if (!squareBorderChecked && !roundBorderChecked) {
			// Default to square if nothing is checked
			document.getElementById('tw_button_border_type_square').checked = true;
			document.querySelectorAll(".toggle_border_type__square").forEach(el => el.classList.add("active"));
		}

		if (squareBorderChecked) {
			// Activate square, deactivate round
			document.querySelectorAll(".toggle_border_type__square").forEach(el => el.classList.add("active"));
			document.querySelectorAll(".toggle_border_type__round").forEach(el => el.classList.remove("active"));
		}
		else if (roundBorderChecked) {
			// Activate round, deactivate square
			document.querySelectorAll(".toggle_border_type__round").forEach(el => el.classList.add("active"));
			document.querySelectorAll(".toggle_border_type__square").forEach(el => el.classList.remove("active"));
		}
	});

	//                       for button tab secton



	//                       for video tab secton

	document.addEventListener("DOMContentLoaded", function () {
		// Get all relevant elements
		const videoUpload = document.getElementById('video_type_upload');
		const videoYoutube = document.getElementById('video_type_youtube');
		const videoVimeo = document.getElementById('video_type_vimeo');

		const toggleUpload = document.querySelector(".toggle_video_type_upload");
		const toggleYoutube = document.querySelector(".toggle_video_type_youtube");
		const toggleVimeo = document.querySelector(".toggle_video_type_vimeo");

		const videoTypeElement = document.querySelector('.video_type');

		// Set initial state
		if (!videoUpload.checked && !videoYoutube.checked && !videoVimeo.checked) {
			videoUpload.checked = true;
			toggleUpload.classList.add("active");
		}

		// Update UI based on checked state
		if (videoUpload.checked) {
			toggleUpload.classList.add("active");
			toggleYoutube.classList.remove("active");
			toggleVimeo.classList.remove("active");
			$('.video_type').trigger('click'); // Trigger jQuery click
		}
		else if (videoYoutube.checked) {
			toggleYoutube.classList.add("active");
			toggleUpload.classList.remove("active");
			toggleVimeo.classList.remove("active");
			$('.video_type').trigger('click'); // Trigger jQuery click
		}
		else if (videoVimeo.checked) {
			toggleVimeo.classList.add("active");
			toggleUpload.classList.remove("active");
			toggleYoutube.classList.remove("active");
			$('.video_type').trigger('click'); // Trigger jQuery click
		}
	});

	document.addEventListener("DOMContentLoaded", function () {
		const autoplayYesChecked = document.getElementById('video_autoplay_yes').checked;
		const autoplayNoChecked = document.getElementById('video_autoplay_no').checked;

		if (!autoplayYesChecked && !autoplayNoChecked) {
			// Default to "No" if neither is checked
			document.getElementById('video_autoplay_no').checked = true;
			document.querySelectorAll(".toggle_video_autoplay_no").forEach(el => el.classList.add("active"));
		}

		if (autoplayYesChecked) {
			// Activate Yes, deactivate No
			document.querySelectorAll(".toggle_video_autoplay_yes").forEach(el => el.classList.add("active"));
			document.querySelectorAll(".toggle_video_autoplay_no").forEach(el => el.classList.remove("active"));
			console.log('Autoplay: Yes');
		}
		else if (autoplayNoChecked) {
			// Activate No, deactivate Yes
			document.querySelectorAll(".toggle_video_autoplay_no").forEach(el => el.classList.add("active"));
			document.querySelectorAll(".toggle_video_autoplay_yes").forEach(el => el.classList.remove("active"));
			console.log('Autoplay: No');
		}
	});

	document.addEventListener("DOMContentLoaded", function () {
		const videoMutedYesChecked = document.getElementById('video_muted_yes').checked;
		const videoMutedNoChecked = document.getElementById('video_muted_no').checked;

		// If neither is checked, default to "No"
		if (!videoMutedYesChecked && !videoMutedNoChecked) {
			document.getElementById('video_muted_no').checked = true;
			document.querySelectorAll(".toggle_video_muted_no").forEach(el => el.classList.add("active"));
		}

		if (videoMutedYesChecked) {
			// Activate Yes, deactivate No
			document.querySelectorAll(".toggle_video_muted_yes").forEach(el => el.classList.add("active"));
			document.querySelectorAll(".toggle_video_muted_no").forEach(el => el.classList.remove("active"));
			console.log('Video muted: Yes');
		}
		else if (videoMutedNoChecked) {
			// Activate No, deactivate Yes
			document.querySelectorAll(".toggle_video_muted_no").forEach(el => el.classList.add("active"));
			document.querySelectorAll(".toggle_video_muted_yes").forEach(el => el.classList.remove("active"));
			console.log('Video muted: No');
		}
	});
	
	document.addEventListener("DOMContentLoaded", function () {
		const videoControlsYesChecked = document.getElementById('video_controls_yes').checked;
		const videoControlsNoChecked = document.getElementById('video_controls_no').checked;

		// If neither is checked, default to "Yes"
		if (!videoControlsYesChecked && !videoControlsNoChecked) {
			document.getElementById('video_controls_yes').checked = true;
			document.querySelectorAll(".toggle_video_controls_yes").forEach(el => el.classList.add("active"));
		}

		if (videoControlsYesChecked) {
			// Activate Yes, deactivate No
			document.querySelectorAll(".toggle_video_controls_yes").forEach(el => el.classList.add("active"));
			document.querySelectorAll(".toggle_video_controls_no").forEach(el => el.classList.remove("active"));
		}
		else if (videoControlsNoChecked) {
			// Activate No, deactivate Yes
			document.querySelectorAll(".toggle_video_controls_no").forEach(el => el.classList.add("active"));
			document.querySelectorAll(".toggle_video_controls_yes").forEach(el => el.classList.remove("active"));
		}
	});

	document.addEventListener("DOMContentLoaded", function () {
    const videoLoopYesChecked = document.getElementById('video_loop_yes').checked;
    const videoLoopNoChecked = document.getElementById('video_loop_no').checked;
    
    // If neither is checked (shouldn't happen due to PHP checked() but just in case)
    if (!videoLoopYesChecked && !videoLoopNoChecked) {
        document.getElementById('video_loop_no').checked = true;
        document.querySelectorAll(".toggle_video_loop_no").forEach(el => el.classList.add("active"));
    }

    if (videoLoopYesChecked) {
        // Activate Yes, deactivate No
        document.querySelectorAll(".toggle_video_loop_yes").forEach(el => el.classList.add("active"));
        document.querySelectorAll(".toggle_video_loop_no").forEach(el => el.classList.remove("active"));
        console.log('Video Loop: Yes selected');
    }
    else if (videoLoopNoChecked) {
        // Activate No, deactivate Yes
        document.querySelectorAll(".toggle_video_loop_no").forEach(el => el.classList.add("active"));
        document.querySelectorAll(".toggle_video_loop_yes").forEach(el => el.classList.remove("active"));
        console.log('Video Loop: No selected');
    }
});
	

	//                       for video tab secton


</script>