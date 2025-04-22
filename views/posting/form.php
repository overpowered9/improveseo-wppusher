<?php

use ImproveSEO\Validator;
use ImproveSEO\Models\Country;

wp_enqueue_script('post');
?>

<input type="hidden" name="post_type" value="<?= isset($task) ? $task->content['post_type'] : $post_type ?>" />
<?php
$word_ai_pass = get_option('improveseo_word_ai_pass');
$word_ai_email = get_option('improveseo_word_ai_email');

$pixabay_key = get_option('improveseo_pixabay_key');
$google_api_key = get_option('improveseo_google_api_key');
?>


<div id="poststuff" class="PostForm">
	<div id="post-body" class="metabox-holder columns-2">
		<!-- <h3>Add New Page</h3> -->
		<div id="post-body-content">
			<div class="PostForm__name-wrap input-group <?php if (Validator::hasError('name'))
				echo 'PostForm--error' ?>">
					<!-- <label class="form-label">Project name here</label> -->
					<input type="text" name="name" class="PostForm__name form-control" placeholder="Project name here"
						value="<?= Validator::old('name', $task->name) ?>" required>
				<?php if (Validator::hasError('name')): ?>
					<span class="PostForm__error"><?= Validator::get('name') ?></span>
				<?php endif; ?>
			</div>

			<div class="PostForm__title-wrap input-group <?php if (Validator::hasError('title'))
				echo ' PostForm--error' ?>">
					<!-- <label class="form-label">Enter title here</label> -->
					<input type="text" id="title" name="title" class="PostForm__title form-control"
						placeholder="Enter title here" value="<?= Validator::old('title', $task->content['title']) ?>">
				<?php if (Validator::hasError('title')): ?>
					<span class="PostForm__error"><?= Validator::get('title') ?></span>
				<?php endif; ?>

				<div id="edit-slug-box">
					<?php
					$old_permalink = Validator::old('permalink', $task->options['permalink']);
					?>
					<input type="hidden" class="form-control" name="permalink" value="<?= $old_permalink ?>">
					<strong>Permalink:<?php echo improveseo_permalink($old_permalink) ?></strong>
					<!--<span><?= improveseo_permalink($old_permalink) ?></span>-->
					<a id="edit-permalink" class="btn btn-outline-primary" aria-label="Edit permalink">Edit</a>
					<a id="save-permalink" class="btn btn-outline-primary" style="display: none">OK</a>
					<a id="prefix-permalink" class="btn btn-outline-primary" style="display: none;">Add Prefix</a>
					<a id="cancel-permalink" class="cancel btn btn-outline-primary"
						style="display: none">Cancel</a><br />
					<div class="howto">
						The non-editable URL structure is determined by your <a
							href="<?php echo site_url(); ?>/wp-admin/options-permalink.php">permalink settings</a>.
					</div>
					<p id="too-many-posts" class="notice notice-error" style="display: none;">Your project contains more
						than 5,000 pages. While Improve SEO can create hundreds of thousands of posts per project, it is
						recommended to split your project into multiple smaller projects if you are using shared hosting
						for maximum efficiency. VPS and dedicated server users can ignore this message. </p>
				</div>
			</div>

			<div class="PostForm__body-wrap <?php if (Validator::hasError('content'))
				echo ' PostForm--error' ?>">
				<?php wp_editor(Validator::old('content', $task->content['content']), 'content', array(
				'_content_editor_dfw' => '',
				'drag_drop_upload' => true,
				'tabfocus_elements' => 'content-html,save-post',
				'editor_height' => 300,
				'tinymce' => array(
					'resize' => false,
					'add_unload_trigger' => false,
					'setup' => 'function (ed) { ed.on("change", function(e) { determineMaxPosts(); }) }'
				),
			)); ?>
				<?php if (Validator::hasError('content')): ?>
					<span class="PostForm__error"><?= Validator::get('content') ?></span>
				<?php endif; ?>
			</div>

			<div id="post_form_buttons" class="PostForm__buttons">
				<button name="create" type="submit" formtarget="_self"
					class="btn styling_post_page_action_buttons btn-outline-primary"
					onclick="return validateBeforeSubmit()">
					<?php if ($_GET['action'] == 'edit_post'): ?>
						Update project and posts
					<?php else: ?>
						Create Project
					<?php endif; ?>
				</button>
				<button name="draft" type="submit" formtarget="_self"
					class="btn styling_post_page_action_buttons btn-outline-primary"
					onclick="return validateBeforeSubmit()">Save As Draft</button>
				<button id="preview_on" type="submit" class="btn styling_post_page_action_buttons btn-outline-primary"
					onclick="return validateBeforeSubmit()">Post preview</button>
				<input type="hidden" name="preview_id" id="preview_id" />
				<input type="hidden" name="is_preview_available" id="is_preview_available" value="no" />
			</div>

			<?php echo $site_link; ?>


			<!-- HTML modal for preview button -->
			<div id="preview_popup" class="modal" style="text-align:center">
				<div id="wh_prev_modal_1">
					<?php $gif_src = IMPROVESEO_DIR . '/assets/images/loader.gif' ?>
					<b style="font-size:20px">Generating preview</b>
					<br /><br />
					<img id="preview_rcube" src="<?= $gif_src ?>" width="200">
				</div>
				<div id="wh_prev_modal_2">
					<b style="font-size:18px">Close preview to continue editing the project</b>
					<br><br>
					<button id="open_win" class="button button-primary" onclick="closeWin()" rel="modal:close">Close
						preview</button>
					&nbsp; &nbsp;
					<button id="close_win" class="button button-primary" onclick="changeWin()">Switch preview</button>
				</div>
			</div>


			<div id="shortcode_popup" class="modal shortcode_popup" tabindex="-1" role="dialog"
				aria-labelledby="shortcode_popup" aria-hidden="true" data-focus="true">
				<h3>Select ImproveSEO Shortcode</h3>
				<div class="form-wrap">
					<input type="hidden" id="is_shortcode_popup_open" value="no" />
					<p class="hidden improveseo_shortcode_error" id="improveseo_shortcode_error">No shortcodes
						available. </p>
					<div class="form-group">
						<label for="improveseo_shortcode_type">Select Shortcode Type</label>
						<select class="form-control" id="improveseo_shortcode_type" name="improveseo_shortcode_type">
							<option value="">Select Shortcode Type</option>
							<option value="testimonial">Testimonials</option>
							<option value="googlemap">Google Maps</option>
							<option value="button">Buttons</option>
							<option value="video">Videos</option>
							<option value="list">Lists</option>
						</select>
					</div>
					<div class="form-group">
						<label for="improveseo_shortcode">Select Shortcode</label>
						<select class="form-control" id="improveseo_shortcode" name="improveseo_shortcode" disabled>
						</select>
					</div>
					<div class="form-group hidden">
						<button type="button" class="btn btn-outline-primary"
							id="improveseo_shortcode_add_btn">Add</button>
					</div>
				</div>
			</div>

			<div id="all_shortcode_popup" class="modal all_shortcode_popup" tabindex="-1" role="dialog"
				aria-labelledby="all_shortcode_popup" aria-hidden="true" data-focus="true">
				<h3>Search ImproveSEO Shortcode</h3>
				<div class="form-wrap">
					<div class="form-group">
						<label for="improveseo_shortcode_text">Search Shortcode</label>
						<input type="text" class="form-control" id="improveseo_shortcode_text"
							name="improveseo_shortcode_text">
					</div>
				</div>
			</div>
		</div>

		<div id="postbox-container-1" class="postbox-container">
			<div id="side-sortables" class="meta-box-sortables ui-sortable">
				<!-- Options -->

				<div class="postbox">
					<button type="button" class="handlediv button-link" aria-expanded="true">
						<span class="toggle-indicator" aria-hidden="true"></span>
					</button>
					<h3 class="hndle ui-sortable-handle"><span>Categories</span></h3>
					<div class="inside mt-2">
						<?php
						$cat_pre = array();
						if (isset($_GET['cat_pre'])) {
							$cat_pre = $_GET['cat_pre'];
							$cat_pre = explode(",", $cat_pre);
						} else if ($task->cats != "") {
							$cat_pre = json_decode($task->cats, true);
						}

						$args = array(
							"hide_empty" => 0,
							"type" => "post",
							"orderby" => "name",
							"order" => "ASC"
						);
						$cats = get_categories($args);
						foreach ($cats as $category) {
							// do not show improve SEO category
						
							$checked = '';

							if (!empty($cat_pre)) {
								if (in_array($category->term_id, $cat_pre)) {

									if ($category->slug == "improve-seo") {
										$checked = 'checked  onclick="return false"';
									} else {
										$checked = 'checked';
									}
								} else {
									$checked = '';
								}
							}

							if ($category->slug == "improve-seo") {
								$checked = 'checked  onclick="return false"';
							}

							$select .= "<div class='input-group cta-check m-0'><span><input " . $checked . " id='" . $category->term_id . "' type='checkbox' value='" . $category->term_id . "' name='cats[]'><label for='" . $category->term_id . "'>" . $category->name . "</label></span></div>";
						}
						echo $select;
						?>
					</div>
				</div>
				<div class="postbox">
					<button type="button" class="handlediv button-link" aria-expanded="true">
						<span class="toggle-indicator" aria-hidden="true"></span>
					</button>
					<h3 class="hndle ui-sortable-handle"><span>Google Preview</span></h3>
					<div class="inside mt-2">
						<div id="google-preview" class="google-preview">
							<div class="input-group mt-4 cta-check">
								<label for="preview-label" class="form-label">Preview As:</label>
								<span>
									<input id="mobile-preview" name="preview-type" type="radio"
										class="google-preview-type" value="mobile" checked />
									<label for="mobile-preview">Mobile result</label>
									&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
									<input id="desktop-preview" name="preview-type" type="radio"
										class="google-preview-type" value="desktop" />
									<label for="desktop-preview">Desktop result</label>
								</span>
							</div>
							<div id="google-mobile-preview" class="google-mobile-preview">
								<div class="google-preview-content-wrapper">
									<div class="google-preview-content-wrapper2">
										<div class="google-preview-logo">
											<i class="fa fa-globe"></i>
											<span class="google-preview-mobile-url">
												<span class="google-preview-mobile-disabled">
													<?php echo site_url(); ?></span> › @title
											</span>
										</div>
										<div class="google-mobile-preview-pagename">
											<?php
											if ($task->options['custom_title'] == "")
												echo '@title';
											else
												echo $task->options['custom_title']; ?>
										</div>
										<div class="google-mobile-preview-description">
											<span
												class="google-mobile-preview-description-date"><?php echo date('M d, Y'); ?>
												－ </span>
											<span class="google-description-content">
												<?php
												if ($task->options['custom_title'] == "") {
													echo "Please provide a meta description by editing the snippet below. If you don't, Google will try to find a relevant part of your post to show in the search results.";
												} else {
													echo $task->options['custom_title'];
												}
												?>
											</span>
										</div>
									</div>
								</div>
							</div>
							<div id="google-desktop-preview" class="google-desktop-preview" style="display:none;">
								<div class="google-preview-content-wrapper">
									<div class="google-preview-content-wrapper2">
										<div class="google-preview-logo-desktop">
											<span class="google-preview-desktop-url">
												<span
													class="google-preview-desktop-disabled"><?php echo site_url(); ?></span>
												› @title
											</span>
										</div>
										<div class="google-desktop-preview-pagename">
											<?php
											if ($task->options['custom_title'] == "")
												echo '@title';
											else
												echo $task->options['custom_title']; ?>
										</div>
										<div class="google-desktop-preview-description">
											<span class="google-desktop-preview-description-date">
												<?php echo date('M d, Y'); ?>－
											</span>
											<span class="google-description-content">
												<?php
												if ($task->options['custom_title'] == "") {
													echo "Please provide a meta description by editing the snippet below. If you don't, Google will try to find a relevant part of your post to show in the search results.";
												} else {
													echo $task->options['custom_title'];
												}
												?>
											</span>
										</div>
									</div>
								</div>
							</div>
							<div class="input-group" style="padding-top: 30px;">
								<label for="custom-title" class="form-label"> Meta Title & Meta Description</label>
								<div class="input-prefix">
									<input id="custom-title" name="custom_title" class="form-control" type="text"
										style="padding: 20px; border-radius: 10px !important; border-color: #cccccc; margin-bottom: 20px; "
										class="full-width form-control textarea-control"
										placeholder="Meta Title:Here Ex:Mango: Health Benefits"
										value="<?= Validator::old('custom_title', $task->options['custom_title']) ?>">
									<!-- <span>Ex.</span> -->
								</div>
								<div id="custom-title-error" style="display:none; color:red; margin-top:5px;">
									Your meta title contains more than 60 characters! <br />
									<b>PRO TIP</b>: the meta title should not contain more than 60 characters
									for best results on Google.
								</div>
							</div>
							<div class="input-group">
								<label for="custom-description" class="form-label"></label>
								<!-- <div class="input-prefix"> -->
								<textarea id="custom-description"
									placeholder="Meta Description:Discover Mango Magic: Health Benefits, Recipes, and Tips for the Perfect Fruit."
									name="custom_description" rows="5"
									class="full-width textarea-control"><?= Validator::old('custom_description', $task->options['custom_description']) ?></textarea>
								<!-- <span>Ex.</span> -->
								<!-- </div> -->
								<div id="custom-description-error" style="display:none; color:red; margin-top:5px;">Your
									meta description
									contains more than 160 characters! <br />
									<b>PRO TIP</b>: the meta description should not contain more than 160
									characters for best results on Google.
								</div>
							</div>
						</div>
					</div>
				</div>


				<?php if ($word_ai_email && $word_ai_pass): ?>
					<!-- Word AI -->
					<div class="postbox">
						<button type="button" class="handlediv button-link" aria-expanded="true">
							<span class="toggle-indicator" aria-hidden="true"></span>
						</button>
						<h3 class="hndle ui-sortable-handle"><span>Word AI Options</span></h3>
						<div class="inside mt-3">
							<p>
								<a href="<?= IMPROVESEO_DIR ?>/wordai.php" onclick="return WordAI.start(this)">Launch Word
									AI Console</a>
							</p>
						</div>
					</div>
				<?php endif; ?>


			</div>
		</div>

		<div id="postbox-container-2" class="postbox-container">
			<div id="normal-sortables" class="meta-box-sortables ui-sortable">
				<div class="PostForm__boxes">



				</div>
			</div>
		</div>
	</div>
</div>