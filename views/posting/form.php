<?php

use ImproveSEO\Validator;
use ImproveSEO\Models\Country;


if ( ! defined( 'ABSPATH' ) ) exit;

wp_enqueue_script('post');

$inputProjectType = isset($task) ? $task->content['post_type'] : $post_type;
$wordAiPass = get_option('improveseo_word_ai_pass');
$wordAiEmail = get_option('improveseo_word_ai_email');
$pixabayKey = get_option('improveseo_pixabay_key');
$googleApiKey = get_option('improveseo_google_api_key');

?>

<input type="hidden" name="post_type" value="<?php echo esc_attr($inputProjectType); ?>" />

<div id="poststuff" class="PostForm">
	<div id="post-body" class="metabox-holder columns-2">
		<h3><?php esc_html_e('Add New Page', 'improve-seo'); ?></h3>
		<div id="post-body-content">
			<div class="PostForm__name-wrap input-group <?php echo Validator::hasError('name') ? 'PostForm--error' : ''; ?>">
				<label class="form-label"><?php esc_html_e('Project name here', 'improve-seo'); ?></label>
				<input type="text" name="name" class="PostForm__name form-control" placeholder="<?php esc_attr_e('Project name here', 'improve-seo'); ?>" value="<?php echo esc_attr(Validator::old('name', $task->name)); ?>">
				<?php if (Validator::hasError('name')) : ?>
					<span class="PostForm__error"><?php echo esc_html(Validator::get('name')); ?></span>
				<?php endif; ?>
			</div>

			<div class="PostForm__title-wrap input-group <?php echo Validator::hasError('title') ? ' PostForm--error' : ''; ?>">
				<label class="form-label"><?php esc_html_e('Enter title here', 'improve-seo'); ?></label>
				<input type="text" id="title" name="title" class="PostForm__title form-control" placeholder="<?php esc_attr_e('Enter title here', 'improve-seo'); ?>" value="<?php echo esc_attr(Validator::old('title', $task->content['title'])); ?>">
				<?php if (Validator::hasError('title')) : ?>
					<span class="PostForm__error"><?php echo esc_html(Validator::get('title')); ?></span>
				<?php endif; ?>

				<div id="edit-slug-box">
					<?php
					$oldPermalink = Validator::old('permalink', $task->options['permalink']);
					?>
					<input type="hidden" class="form-control" name="permalink" value="<?php echo esc_attr($oldPermalink); ?>">
					<strong><?php esc_html_e('Permalink:', 'improve-seo'); ?><?php echo improveseo_permalink($oldPermalink); ?></strong>
					<a id="edit-permalink" class="btn btn-outline-primary" aria-label="<?php esc_attr_e('Edit permalink', 'improve-seo'); ?>"><?php esc_html_e('Edit', 'improve-seo'); ?></a>
					<a id="save-permalink" class="btn btn-outline-primary" style="display: none"><?php esc_html_e('OK', 'improve-seo'); ?></a>
					<a id="prefix-permalink" class="btn btn-outline-primary" style="display: none;"><?php esc_html_e('Add Prefix', 'improve-seo'); ?></a>
					<a id="cancel-permalink" class="cancel btn btn-outline-primary" style="display: none"><?php esc_html_e('Cancel', 'improve-seo'); ?></a><br />
					<div class="howto">
						<?php esc_html_e('The non-editable URL structure is determined by your', 'improve-seo'); ?> <a href="<?php echo esc_url(site_url()); ?>/wp-admin/options-permalink.php"><?php esc_html_e('permalink settings', 'improve-seo'); ?></a>.
					</div>
					<p id="too-many-posts" class="notice notice-error" style="display: none;"><?php esc_html_e('Your project contains more than 5,000 pages. While Improve SEO can create hundreds of thousands of posts per project, it is recommended to split your project into multiple smaller projects if you are using shared hosting for maximum efficiency. VPS and dedicated server users can ignore this message.', 'improve-seo'); ?></p>
				</div>
			</div>

			<div class="PostForm__body-wrap <?php echo Validator::hasError('content') ? ' PostForm--error' : ''; ?>">
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
				<?php if (Validator::hasError('content')) : ?>
					<span class="PostForm__error"><?php echo esc_html(Validator::get('content')); ?></span>
				<?php endif; ?>
			</div>

			<div id="post_form_buttons" class="PostForm__buttons">
				<button name="create" type="submit" formtarget="_self" class="btn btn-outline-primary">
					<?php echo ($_GET['action'] == 'edit_post') ? esc_html__('Update project and posts', 'improve-seo') : esc_html__('Create Project', 'improve-seo'); ?>
				</button>
				<button name="draft" type="submit" formtarget="_self" class="btn btn-outline-primary"><?php esc_html_e('Save As Draft', 'improve-seo'); ?></button>
				<button id="preview_on" type="submit" class="btn btn-outline-primary"><?php esc_html_e('Post preview', 'improve-seo'); ?></button>
				<input type="hidden" name="preview_id" id="preview_id" />
				<input type="hidden" name="is_preview_available" id="is_preview_available" value="no" />
			</div>

			<?php echo esc_url($site_link); ?>

			<!-- HTML modal for preview button -->
			<div id="preview_popup" class="modal" style="text-align:center">
				<div id="wh_prev_modal_1">
					<?php $gifSrc = IMPROVESEO_DIR . '/assets/images/loader.gif'; ?>
					<b style="font-size:20px"><?php esc_html_e('Generating preview', 'improve-seo'); ?></b>
					<br /><br />
					<img id="preview_rcube" src="<?php echo esc_url($gifSrc); ?>" width="200">
				</div>
				<div id="wh_prev_modal_2">
					<b style="font-size:18px"><?php esc_html_e('Close preview to continue editing the project', 'improve-seo'); ?></b>
					<br><br>
					<button id="open_win" class="button button-primary" onclick="closeWin()" rel="modal:close"><?php esc_html_e('Close preview', 'improve-seo'); ?></button>
					&nbsp; &nbsp;
					<button id="close_win" class="button button-primary" onclick="changeWin()"><?php esc_html_e('Switch preview', 'improve-seo'); ?></button>
				</div>
			</div>

			<div id="shortcode_popup" class="modal shortcode_popup" tabindex="-1" role="dialog" aria-labelledby="shortcode_popup" aria-hidden="true" data-focus="true">
				<h3><?php esc_html_e('Select ImproveSEO Shortcode', 'improve-seo'); ?></h3>
				<div class="form-wrap">
					<input type="hidden" id="is_shortcode_popup_open" value="no" />
					<p class="hidden improveseo_shortcode_error" id="improveseo_shortcode_error"><?php esc_html_e('No shortcodes available.', 'improve-seo'); ?></p>
					<div class="form-group">
						<label for="improveseo_shortcode_type"><?php esc_html_e('Select Shortcode Type', 'improve-seo'); ?></label>
						<select class="form-control" id="improveseo_shortcode_type" name="improveseo_shortcode_type">
							<option value=""><?php esc_html_e('Select Shortcode Type', 'improve-seo'); ?></option>
							<option value="list"><?php esc_html_e('Lists', 'improve-seo'); ?></option>
						</select>
					</div>
					<div class="form-group">
						<label for="improveseo_shortcode"><?php esc_html_e('Select Shortcode', 'improve-seo'); ?></label>
						<select class="form-control" id="improveseo_shortcode" name="improveseo_shortcode" disabled>
						</select>
					</div>
					<div class="form-group hidden">
						<button type="button" class="btn btn-outline-primary" id="improveseo_shortcode_add_btn"><?php esc_html_e('Add', 'improve-seo'); ?></button>
					</div>
				</div>
			</div>


			<div id="all_shortcode_popup" class="modal all_shortcode_popup" tabindex="-1" role="dialog" aria-labelledby="all_shortcode_popup" aria-hidden="true" data-focus="true">
				<h3><?php esc_html_e('Search ImproveSEO Shortcode', 'improve-seo'); ?></h3>
				<div class="form-wrap">
					<div class="form-group">
						<label for="improveseo_shortcode_text"><?php esc_html_e('Search Shortcode', 'improve-seo'); ?></label>
						<input type="text" class="form-control" id="improveseo_shortcode_text" name="improveseo_shortcode_text">
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
					<h3 class="hndle ui-sortable-handle"><span><?php esc_html_e('Categories', 'improve-seo'); ?></span></h3>
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
							"type"      => "post",
							"orderby"   => "name",
							"order"     => "ASC"
						);
						$cats = get_categories($args);
                        $select = "";
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

							$select .= "<div class='input-group cta-check m-0'><span><input " . esc_attr($checked) . " id='" . esc_attr($category->term_id) . "' type='checkbox' value='" . esc_attr($category->term_id) . "' name='cats[]'><label for='" . esc_attr($category->term_id) . "'>" . esc_html($category->name) . "</label></span></div>";
						}


						?>
                        <?php  echo ($select); ?>
					</div>
				</div>


				<!-- Options -->
				<div class="postbox">
					<button type="button" class="handlediv button-link" aria-expanded="true">
						<span class="toggle-indicator" aria-hidden="true"></span>
					</button>
					<h3 class="hndle ui-sortable-handle"><span><?php esc_html_e('Improve SEO Options', 'improve-seo'); ?></span></h3>
					<div class="inside mt-3">
						<p>
							<strong><?php esc_html_e('Max Posts:', 'improve-seo'); ?></strong> <br>
							<span class="d-block mb-2">
								<?php esc_html_e('Maximum number of posts to generate. Input `0` if you want to generate all available posts from spintax.', 'improve-seo'); ?>
							</span>
						<div class="input-group">
							<input type="number" id="max-posts" name="max_posts" class="form-control" value="<?php echo (Validator::old('max_posts', (int) $task->options['max_posts']) <= 0) ? '1' : esc_attr(Validator::old('max_posts', (int) $task->options['max_posts'])); ?>" min="1" />
						</div>
						</p>

						<p>
							<strong><?php esc_html_e('Distribute among authors randomly:', 'improve-seo'); ?></strong> <br>
						<p style="color:red"><?php esc_html_e('Upgrade to Improve SEO Pro version to enable this function', 'improve-seo'); ?></p>
						<span>
							<?php esc_html_e('Distribute posts among Improve SEO authors randomly.', 'improve-seo'); ?>
						</span>
						</p>
						<div class="input-group m-0 cta-check">
							<span>
								<input type="checkbox" disabled id="distribute" name="distribute" value="1" <?php echo Validator::old('distribute', ($task->options['distribute'] ?? 0)) == 1 ? 'checked' : ''; ?>>
								<label for="distribute"><?php esc_html_e('Distribute', 'improve-seo'); ?></label>
							</span>
						</div>
					</div>
				</div>

				<!-- DripFeed Property -->
				<div class="postbox">
					<button type="button" class="handlediv button-link" aria-expanded="true">
						<span class="toggle-indicator" aria-hidden="true"></span>
					</button>
					<h3 class="hndle ui-sortable-handle"><span><?php esc_html_e('Improve SEO Dripfeed Property', 'improve-seo'); ?></span></h3>
					<div class="inside">
						<?php
						$old_dripfeed_enabler = Validator::old('dripfeed_enabler', $task->options['dripfeed_type'] ? 1 : 0);
						?>
						<div class="input-group my-3 cta-check">
							<span>
								<input id="dripfeed-enabler" name="dripfeed_enabler" type="checkbox" value="1" <?php echo esc_attr($old_dripfeed_enabler == 1 ? 'checked' : ''); ?>
>
								<label for="dripfeed-enabler" class="selectit"> <?php esc_html_e('Enable Feature', 'improve-seo'); ?></label>
							</span>
						</div>

						<div id="dripfeed-wrap" style="display: <?php echo esc_attr($old_dripfeed_enabler == 1 ? 'block' : 'none'); ?>
                                ;">
							<div class="input-group">
								<label for="dripfeed-type" class="form-label"><?php esc_html_e('Dripfeed Type:', 'improve-seo'); ?></label>
								<?php $old_dripfeed_type = Validator::old('dripfeed_type', $task->options['dripfeed_type']) ?>
								<select id="dripfeed-type" name="dripfeed_type" class="form-control">
									<option value="per-day" <?php echo esc_attr($old_dripfeed_type == 'per-day' ? ' selected' : ''); ?>
                                     ><?php esc_html_e('X posts/pages per day', 'improve-seo'); ?></option>
									<option value="over-days" <?php echo esc_attr($old_dripfeed_type == 'over-days' ? ' selected' : ''); ?>
                                    ><?php esc_html_e('Whole project dripped over X days', 'improve-seo'); ?></option>
								</select>
							</div>
							<div class="input-group <?php echo (Validator::hasError('dripfeed_x')) ? 'PostForm--error' : '' ?>">
								<label for="dripfeed-x" class="form-label"><?php esc_html_e('X Parameter:', 'improve-seo'); ?></label>
								<input type="text" id="dripfeed-x" name="dripfeed_x" class="form-control" value="<?php echo Validator::old('dripfeed_x', $task->options['dripfeed_x']) ?>">
								<?php if (Validator::hasError('dripfeed_x')) : ?>
									<span class="PostForm__error"><?php echo esc_html(Validator::get('dripfeed_x')); ?></span>

								<?php endif; ?>
							</div>
						</div>
						<p>
							<?php esc_html_e('It is not recommended to dripfeed multiple projects at the same time on the same server as it puts an elongated load on your server. Creating all pages at once is recommended unless you have a reason not to.', 'improve-seo'); ?>
						</p>
					</div>
				</div>


				<!-- Tags -->
				<?php /*
				<div class="postbox">
					<button type="button" class="handlediv button-link" aria-expanded="true">
						<span class="toggle-indicator" aria-hidden="true"></span>
					</button>
					<h3 class="hndle ui-sortable-handle"><span>Improve SEO Tags</span></h3>
					<div class="inside">
						<input type="hidden" name="tags">
						<div class="input-group seo-input-tag mb-1 mt-3">
							<input type="text" id="tagsinput" class="form-control" size="16" autocomplete="off" value="<?php echo Validator::old('tags', $task->options['tags']) ?>">
							<div class="input-group-append">
								<a id="add-tags" class="btn btn-primary ">Add</a>
							</div>
						</div>
						<small class="howto">Separate tags with commas</small>
						<div id="tags" class="tagchecklist"></div>

						<div class="from-group cta-check my-3">
							<span>
								<input id="noindex_tags" name="noindex_tags" type="checkbox" value="1" <?php echo Validator::old('noindex_tags', $task->options['noindex_tags']) == 1 ? 'checked' : ''; ?>>
								<label for="noindex_tags" class="selectit ">Noindex tags</label>
							</span>
						</div>
						<p class="howto">Helps fight duplicate content on tag pages; not recommended</p>
					</div>
				</div> */ ?>

				<?php if ($word_ai_email && $word_ai_pass) : ?>
					<!-- Word AI -->
					<div class="postbox">
						<button type="button" class="handlediv button-link" aria-expanded="true">
							<span class="toggle-indicator" aria-hidden="true"></span>
						</button>
						<h3 class="hndle ui-sortable-handle"><span><?php esc_html_e('Word AI Options', 'improve-seo'); ?></span></h3>
						<div class="inside mt-3">
							<p>
								<a href="<?php echo esc_url(IMPROVESEO_DIR . '/wordai.php'); ?>" onclick="return WordAI.start(this)">
									<?php esc_html_e('Launch Word AI Console', 'improve-seo'); ?>
								</a>
							</p>
						</div>
					</div>

				<?php endif; ?>

				<!-- Categorization -->
				<?php /*
				<div class="postbox">
					<button type="button" class="handlediv button-link" aria-expanded="true">
						<span class="toggle-indicator" aria-hidden="true"></span>
					</button>
					<h3 class="hndle ui-sortable-handle"><span>Improve SEO Permalink Structure</span></h3>
					<div class="inside">
						<?php
							$old_enable_categorization = Validator::old('enable_categorization', isset($task->options['enable_categorization']));
						?>
						<div class="input-group cta-check">
							<span>
								<input id="enable_categorization" name="enable_categorization" type="checkbox" value="1" <?php echo $old_enable_categorization == 1 ? 'checked' : ''; ?>>
								<label for="enable_categorization" class="selectit">Enable Categorization</label>
							</span>
						</div>
						<p class="howto mb-3">
							This will create pages like <strong>/plumber/michigan/troy/48098</strong>, instead of <strong>/plumber-michigan-troy-48098</strong> <br>
							In this case, "plumber" would be the URL prefix
						</p>
						<div class="input-group">
							<label for="url-prefix" class="form-label" >URL Prefix</label>
							<input type="text" name="permalink_prefix" class="form-control" value="<?php echo Validator::old('permalink_prefix', $task->options['permalink_prefix']) ?>">
						</div>
						<p class="howto">
							This will override Wordpress settings and put a heavy load on your server. It is not recommended.
						</p>
					</div>
				</div>
				*/ ?>

				<!-- Images Scraper -->
				<!-- <div class="postbox">
					<button type="button" class="handlediv button-link" aria-expanded="true">
						<span class="toggle-indicator" aria-hidden="true"></span>
					</button>
					<h3 class="hndle ui-sortable-handle"><span>Improve SEO Images</span></h3>
					<div class="inside mt-3">
						<?php
						if (!empty($task->options['exif_locations']))
							$old_exif_enabler = Validator::old('exif_enabler', sizeof($task->options['exif_locations']) > 0);
						else
							$old_exif_enabler = 0;

						if (!empty($google_api_key)) :
						?>
							<div class="input-group cta-check">
								<span>
									<input id="exif-enabler" name="exif_enabler" type="checkbox" value="1" <?php echo $old_exif_enabler == 1 ? 'checked' : ''; ?>>
									<label for="exif-enabler" class="selectit">Enable Image EXIF</label>
								</span>
							</div>
						<?php else : ?>
							<div class="PixabayKeyWarning info-sec">
								Please, enter Google Maps API Key in <a href="<?php echo admin_url('admin.php?page=improveseo_settings'); ?>">Plugin Settings</a>.
							</div>
						<?php endif; ?>

						<div id="exif-wrap" style="display: <?php echo $old_exif_enabler == 1 ? 'block' : 'none' ?>">
							<div class="input-group cta-check">
								<span>
									<input id="use-post-location" name="use_post_location" type="checkbox" value="1" <?php echo Validator::old('use_post_location', $task->options['use_post_location']) == 1 ? 'checked' : ''; ?>>
									<label for="use-post-location" class="selectit">Use Post Location</label>
								</span>
							</div>
							<p>
								<a href="/index.php?api=improveseo&action=exif" onclick="return ImageEXIF.start(this)">Set Locations For Images</a>
							</p>
						</div>

						<p>
							<?php
							$old_local_seo_enabler = Validator::old('local_seo_enabler', !empty($task->options['local_geo_country']));

							if (!empty($pixabay_key)) :
							?>
								<input type="hidden" id="pixabay-api-key" value="<?php echo $pixabay_key ?>">
								<a href="<?php echo IMPROVESEO_DIR ?>/imagescraper.php" title="Image Scraper" onclick="return ImageScraper.start(this)">Launch Images Scraper</a>
							<?php else : ?>
						<div class="PixabayKeyWarning info-sec">
							Please, enter Pixabay API Key in <a href="<?php echo admin_url('admin.php?page=improveseo_settings'); ?>">Plugin Settings</a>.
						</div>
					<?php endif; ?>
					</p>
					</div>
				</div> -->

				<!-- Videos Scraper -->
				<!-- <div class="postbox">
					<button type="button" class="handlediv button-link" aria-expanded="true">
						<span class="toggle-indicator" aria-hidden="true"></span>
					</button>
					<h3 class="hndle ui-sortable-handle"><span>Improve SEO Videos</span></h3>
					<div class="inside mt-3">
						<p>
							<?php
							$youtube_key = get_option('improveseo_google_api_key');

							if (!empty($youtube_key)) :
							?>
								<input type="hidden" id="youtube-api-key" value="<?php echo $youtube_key ?>">
								<a href="<?php echo IMPROVESEO_DIR ?>/videoscraper.php" title="Image Scraper" onclick="return VideoScraper.start(this)">Launch Videos Scraper</a>
							<?php else : ?>
						<div class="PixabayKeyWarning">
							Please, enter YouTube API Key in <a href="<?php echo admin_url('admin.php?page=improveseo_settings'); ?>">Plugin Settings</a>.
						</div>
					<?php endif; ?>
					</p>
					</div>
				</div> -->
			</div>
		</div>

		<div id="postbox-container-2" class="postbox-container">
			<div id="normal-sortables" class="meta-box-sortables ui-sortable">
				<div class="PostForm__boxes">

					<!-- On-Page SEO -->
					<div class="postbox">
						<button type="button" class="handlediv button-link" aria-expanded="true">
							<span class="toggle-indicator" aria-hidden="true"></span>
						</button>
						<h3 class="hndle"><span><?php echo esc_attr_e("Improve SEO Options", "improve-seo") ?></span></h3>
						<div class="inside">
							<div class="customizer-wrapper seo-post-meta-wrapper">
								<?php
								$old_on_page_seo = Validator::old('on_page_seo', !empty($task->options['custom_title']) || !empty($task->options['custom_description']) || !empty($task->options['custom_keywords']));
								?>
								<div class="input-group my-4 cta-check">
									<span>
										<input id="on-page-seo" name="on_page_seo" type="checkbox" value="1" <?php echo $old_on_page_seo == 1 ? ' checked' : ''; ?>>
										<label for="on-page-seo"><?php esc_html_e('Enable Improve SEO On-Page Customizer', 'improve-seo'); ?></label>
									</span>
								</div>

								<div id="on-page-seo-wrap" style="display: <?php echo $old_on_page_seo == 1 ? 'block' : 'none' ?>;">

									<div id="google-preview" class="google-preview">
										<h4 class=""><span><?php esc_html_e('Google Preview', 'improve-seo'); ?></span></h4>
										<div class="input-group mt-4 cta-check">
											<label for="preview-label" class="form-label"><?php esc_html_e('Preview As:', 'improve-seo'); ?></label>
											<span>
												<input id="mobile-preview" name="preview-type" type="radio" class="google-preview-type" value="mobile" checked />
												<label for="mobile-preview"><?php esc_html_e('Mobile result', 'improve-seo'); ?></label>
												&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
												<input id="desktop-preview" name="preview-type" type="radio" class="google-preview-type" value="desktop" />
												<label for="desktop-preview"><?php esc_html_e('Desktop result', 'improve-seo'); ?></label>
											</span>
										</div>
										<div id="google-mobile-preview" class="google-mobile-preview">
											<div class="google-preview-content-wrapper">
												<div class="google-preview-content-wrapper2">
													<div class="google-preview-logo">
														<i class="fa fa-globe"></i>
														<span class="google-preview-mobile-url">
															<span class="google-preview-mobile-disabled">
																<?php echo esc_url(site_url()); ?></span> › @title
														</span>
													</div>
													<div class="google-mobile-preview-pagename">
														<?php
														if ($task->options['custom_title'] == "")
															echo '@title';
														else
															echo esc_html($task->options['custom_title']);
														?>
													</div>
													<div class="google-mobile-preview-description">
														<span class="google-mobile-preview-description-date"><?php echo esc_html(date('M d, Y')); ?> － </span>
														<span class="google-description-content">
															<?php
															if ($task->options['custom_title'] == "") {
																echo esc_html_e("Please provide a meta description by editing the snippet below. If you don't, Google will try to find a relevant part of your post to show in the search results.", 'improve-seo');
															} else {
																echo esc_html($task->options['custom_title']);
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
															<span class="google-preview-desktop-disabled"><?php echo esc_url(site_url()); ?></span> › @title
														</span>
													</div>
													<div class="google-desktop-preview-pagename">
														<?php
														if ($task->options['custom_title'] == "")
															echo '@title';
														else
															echo esc_html($task->options['custom_title']);
														?>
													</div>
													<div class="google-desktop-preview-description">
														<span class="google-desktop-preview-description-date">
															<?php echo esc_html(date('M d, Y')); ?>－
														</span>
														<span class="google-description-content">
															<?php
															if ($task->options['custom_title'] == "") {
																echo esc_attr_e("Please provide a meta description by editing the snippet below. If you don't, Google will try to find a relevant part of your post to show in the search results.", 'improve-seo');
															} else {
																echo esc_html($task->options['custom_title']);
															}
															?>
														</span>
													</div>
												</div>
											</div>
										</div>
									</div>

									<div class="input-group">
										<label for="custom-title" class="form-label">Title</label>
										<div class="input-prefix">
											<input id="custom-title" name="custom_title" class="form-control full-width textarea-control" type="text" placeholder="<?php esc_html_e('Title', 'your-text-domain'); ?>" value="<?php echo esc_attr(Validator::old('custom_title', $task->options['custom_title'])); ?>">

											<span>Ex.</span>
										</div>
										<div id="custom-title-error" style="display:none; color:red; margin-top:5px;">Your meta title contains more than 60 characters! <br />
											<b>PRO TIP</b>: the meta title should not contain more than 60 characters for best results on Google.
										</div>
									</div>
									<div class="input-group">
										<label for="custom-description" class="form-label">Description</label>
										<div class="input-prefix">
											<textarea id="custom-description" placeholder="Description" name="custom_description" class="full-width textarea-control"><?php echo esc_attr(Validator::old('custom_description', $task->options['custom_description'])) ?></textarea>
											<span>Ex.</span>
										</div>
										<div id="custom-description-error" style="display:none; color:red; margin-top:5px;">Your meta description contains more than 160 characters! <br />
											<b>PRO TIP</b>: the meta description should not contain more than 160 characters for best results on Google.
										</div>
									</div>
									<div class="input-group">
										<label for="custom-keywords" class="form-label">Keywords</label>
										<div class="input-prefix">
											<textarea id="custom-keywords" placeholder="Keywords" name="custom_keywords" class="full-width textarea-control"><?php echo esc_attr( Validator::old('custom_keywords', $task->options['custom_keywords'])) ?></textarea>
											<span>Ex.</span>
										</div>
									</div>
								</div>
							</div>
							<div class="seo-post-meta-wrapper feature-wrapper">
								<!-- <div class="input-group my-4 cta-check">
        <span>
            <input id="local-seo-enabler" name="local_seo_enabler" type="checkbox" value="1" <?php echo $old_local_seo_enabler == 1 ? 'checked' : ''; ?>>
            <label for="local-seo-enabler"><?php esc_html_e('Enable Improve SEO Local SEO Feature', 'improve-seo'); ?></label>
        </span>
    </div> -->
								<div id="local-seo-wrap" style="display: <?php echo $old_local_seo_enabler == 1 ? 'block' : 'none'; ?>;">
									<div class="input-group cta-check">
										<span>
											<input id="local-randomize" name="local_randomize" type="checkbox" value="1" <?php echo Validator::old('local_randomize', $task->options['local_randomize']) == 1 ? 'checked' : '' ?>>
											<label for="local-randomize" class="form-label"><?php esc_html_e('Randomize Results', 'improve-seo'); ?></label>
										</span>
									</div>
									<div class="input-group">
										<label for="local-country" class="form-label"><?php esc_html_e('Country', 'improve-seo'); ?></label>
										<?php
										$countries = array('us' => esc_html__('United States', 'improve-seo'), 'uk' => esc_html__('United Kingdom', 'improve-seo'));
										$countryModel = new Country();
										$otherCountries = $countryModel->all('name');
										?>
										<select id="local-country" class="form-control" name="local_country">
											<option value=""><?php esc_html_e('Select Country', 'improve-seo'); ?></option>
											<?php foreach ($countries as $short => $name) : ?>
												<option value="<?php echo esc_attr($short); ?>" <?php selected(Validator::old('local_geo_country', $task->options['local_geo_country']), $short); ?>>
													<?php echo esc_html($name); ?>
												</option>
											<?php endforeach; ?>
											<?php foreach ($otherCountries as $other) : ?>
												<option value="<?php echo esc_attr($other->id); ?>">
													<?php echo esc_html($other->name); ?>
												</option>
											<?php endforeach; ?>
										</select>

									</div>
									<div class="input-group">
										<label><?php esc_html_e('Choose locations', 'improve-seo'); ?></label> <br>
										<small class="d-block w-100">
											<?php esc_html_e("Press 'Shift + Left Mouse' to select all tree nodes", 'improve-seo'); ?>
										</small>
										<div id="jstree"></div>
										<?php if ($task->options['local_geo_locations']) : ?>
											<script>
												var local_geo_locations = <?php echo json_encode($task->options['local_geo_locations']) ?>;
											</script>
										<?php endif; ?>
									</div>
								</div>
							</div>

							<div class="seo-post-meta-wrapper schema-wrapper">
								<?php
								$old_schema = Validator::old('schema', $task->options['schema']);
								?>
								<div class="input-group my-4 cta-check">
									<p style="color:red"><?php esc_html_e('Upgrade to Improve SEO Pro version to enable this function', 'improve-seo'); ?></p>
									<span>
										<input id="schema" disabled name="schema" type="checkbox" value="1" <?php echo $old_schema == 1 ? ' checked' : '' ?>>
										<label for="schema" class="form-label"><?php esc_html_e('Enable Improve SEO Schema', 'improve-seo'); ?></label>
									</span>
								</div>

								<div id="schema-wrap" style="display: <?php echo $old_schema == 1 ? 'block' : 'none' ?>;">
									<div class="input-group my-4 cta-check">
										<span>
											<input type="checkbox" name="hide_schema" id="hide-schema" value="1" <?php echo Validator::old('hide_schema', $task->options['hide_schema']) ? 'checked' : '' ?>>
											<label for="hide-schema" class="form-label"><?php esc_html_e('Hide schema from authors', 'improve-seo'); ?></label>
										</span>
									</div>
									<div class="input-group">
										<label for="schema-business" class="form-label"><?php esc_html_e('Business Name', 'improve-seo'); ?></label>
										<div class="input-prefix">
											<input id="schema-business" placeholder="<?php esc_attr_e('Business Name', 'improve-seo'); ?>" name="schema_business" type="text" class="full-width form-control" value="<?php echo Validator::old('schema_business', $task->options['schema_business']) ?>">
											<span><?php esc_html_e('Ex.', 'improve-seo'); ?></span>
										</div>
									</div>
									<div class="input-group">
										<label for="schema-description" class="form-label"><?php esc_html_e('Description', 'improve-seo'); ?></label>
										<div class="input-prefix">
											<textarea id="schema-description" placeholder="<?php esc_attr_e('Description', 'improve-seo'); ?>" name="schema_description" class="full-width textarea-control"><?php echo Validator::old('schema_description', $task->options['schema_description']) ?></textarea>
											<span><?php esc_html_e('Ex.', 'improve-seo'); ?></span>
										</div>
									</div>
									<div class="input-group">
										<label for="schema-email" class="form-label"><?php esc_html_e('E-mail', 'improve-seo'); ?></label>
										<div class="input-prefix">
											<input type="text" id="schema-email" placeholder="<?php esc_attr_e('E-mail', 'improve-seo'); ?>" name="schema_email" class="full-width form-control" value="<?php echo Validator::old('schema_email', $task->options['schema_email']) ?>">
											<span><?php esc_html_e('Ex.', 'improve-seo'); ?></span>
										</div>
									</div>
									<div class="input-group">
										<label for="schema-telephone" class="form-label"><?php esc_html_e('Telephone', 'improve-seo'); ?></label>
										<div class="input-prefix">
											<input type="tel" id="schema-telephone" placeholder="<?php esc_attr_e('Telephone', 'improve-seo'); ?>" name="schema_telephone" class="full-width form-control" value="<?php echo Validator::old('schema_telephone', $task->options['schema_telephone']) ?>">
											<span><?php esc_html_e('Ex.', 'improve-seo'); ?></span>
										</div>
									</div>
									<div class="input-group">
										<label for="schema-social" class="form-label"><?php esc_html_e('Social pages', 'improve-seo'); ?></label>
										<div class="input-prefix">
											<textarea id="schema-social" placeholder="<?php esc_attr_e('Social pages', 'improve-seo'); ?>" name="schema_social" class="full-width textarea-control"><?php echo Validator::old('schema_social', $task->options['schema_social']) ?></textarea>
											<span><?php esc_html_e('Ex.', 'improve-seo'); ?></span>
										</div>
									</div>
									<div class="input-group">
										<label for="schema-rating-object" class="form-label"><?php esc_html_e('Rating Object', 'improve-seo'); ?></label>
										<div class="input-prefix">
											<input id="schema-rating-object" placeholder="<?php esc_attr_e('Rating Object', 'improve-seo'); ?>" name="schema_rating_object" type="text" class="full-width form-control" value="<?php echo esc_attr(Validator::old('schema_rating_object', $task->options['schema_rating_object'])); ?>">
											<span><?php esc_html_e('Ex.', 'improve-seo'); ?></span>

										</div>
									</div>
									<div class="input-group">
										<label for="schema-rating" class="form-label"><?php esc_html_e('Rating', 'improve-seo'); ?></label>
										<div class="input-prefix">
											<input id="schema-rating" placeholder="<?php esc_attr_e('Rating', 'improve-seo'); ?>" name="schema_rating" type="text" class="full-width form-control" value="<?php echo Validator::old('schema_rating', $task->options['schema_rating']) ?>">
											<span><?php esc_html_e('Ex.', 'improve-seo'); ?></span>
										</div>
									</div>
									<div class="input-group">
										<label for="schema-rating-count" class="form-label"><?php esc_html_e('Rating Count', 'improve-seo'); ?></label>
										<div class="input-prefix">
											<input id="schema-rating-count" placeholder="<?php esc_attr_e('Rating Count', 'improve-seo'); ?>" name="schema_rating_count" type="text" class="full-width form-control" value="<?php echo Validator::old('schema_rating_count', $task->options['schema_rating_count']) ?>">
											<span><?php esc_html_e('Ex.', 'improve-seo'); ?></span>
										</div>
									</div>
									<div class="input-group">
										<label for="schema-address" class="form-label"><?php esc_html_e('Address', 'improve-seo'); ?></label>
										<div class="input-prefix">
											<textarea id="schema-address" placeholder="<?php esc_attr_e('Address', 'improve-seo'); ?>" name="schema_address" class="full-width textarea-control"><?php echo Validator::old('schema_address', $task->options['schema_address']) ?></textarea>
											<span><?php esc_html_e('Ex.', 'improve-seo'); ?></span>
										</div>
									</div>

								</div>
							</div>
						</div>
					</div>
					<?php
					/* 
					<div class="postbox">
						<button type="button" class="handlediv button-link" aria-expanded="false">
							<span class="toggle-indicator" aria-hidden="true"></span>
						</button>
						<h3 class="hndle ui-sortable-handle"><span>Improve SEO Channel Pages</span></h3>
						<div class="inside">
							<?php
								$old_state_channel = Validator::old('state_channel_page', $task->content['state_channel_enabled']);
								$old_city_channel = Validator::old('city_channel_page', $task->content['city_channel_enabled']);
							?>
							<p id="channel-howto" class="howto my-3" <?php echo $old_enable_categorization == 1 ? 'style="display: none;"' : '' ?>>
								You need enable categorization for using channel pages.
							</p>
							<div class="seo-post-meta-wrapper">
								<div class="input-group my-4 cta-check">
									<span>
										<input id="state-channel-page" name="state_channel_page" type="checkbox" value="1" <?php echo $old_enable_categorization == 1 && $old_state_channel == 1 ? 'checked' : ''; ?> <?php echo $old_enable_categorization == 1 ? '' : 'disabled' ?>>
										<label for="state-channel-page" class="selectit">Enable State Channel Pages</label>
									</span>
								</div>

								<div id="state-channel-page-wrap" <?php echo $old_state_channel == 1 ? '' : 'style="display: none;"'; ?>>
									<div class="PostForm__title-wrap input-group <?php if (Validator::hasError('state_channel_title')) echo ' PostForm--error' ?>">
										<label class="form-label"> Enter title here </label>
										<div class="input-prefix">
											<input type="text" id="state-channel-title" name="state_channel_title" class="PostForm__title form-control" placeholder="Enter title here" value="<?php echo Validator::old('state_channel_title', $task->content['state_channel_title']) ?>">
											<span>Ex.</span>
										</div>
										<?php if (Validator::hasError('state_channel_title')): ?>
										<span class="PostForm__error"><?php echo Validator::get('state_channel_title') ?></span>
										<?php endif; ?>
									</div>

									<?php wp_editor(Validator::old('state_channel_content', $task->content['state_channel_page']), 'state_channel_content', array(
										'_content_editor_dfw' => '',
										'drag_drop_upload' => true,
										'tabfocus_elements' => 'content-html,save-post',
										'editor_class' => 'editor-hidden',
										'editor_height' => 300,
										'tinymce' => array(
											'resize' => false,
											'add_unload_trigger' => false,
										),
									)); ?>

								</div>
							</div>
							<div class="seo-post-meta-wrapper">
								<div class="input-group my-4 cta-check">
									<span>
										<input id="city-channel-page" name="city_channel_page" type="checkbox" value="1" <?php echo $old_enable_categorization == 1 && $old_city_channel == 1 ? 'checked' : ''; ?> <?php echo $old_enable_categorization == 1 ? '' : 'disabled' ?>>
										<label for="city-channel-page" class="selectit">Enable City Channel Pages</label>
									</span>
								</div>

								<div id="city-channel-page-wrap" <?php echo $old_city_channel == 1 ? '' : 'style="display: none;"'; ?>>
									<div class="PostForm__title-wrap input-group <?php if (Validator::hasError('city_channel_title')) echo ' PostForm--error' ?>">
										<label class="form-label">Enter title here</label>
										<div class="input-prefix">
											<input type="text" id="city-channel-title" name="city_channel_title" class="PostForm__title form-control" placeholder="Enter title here" value="<?php echo Validator::old('city_channel_title', $task->content['city_channel_title']) ?>">
											<span>Ex.</span>
										</div>
										<?php if (Validator::hasError('city_channel_title')): ?>
										<span class="PostForm__error"><?php echo Validator::get('city_channel_title') ?></span>
										<?php endif; ?>
									</div>

									<?php wp_editor(Validator::old('city_channel_content', $task->content['city_channel_page']), 'city_channel_content', array(
										'_content_editor_dfw' => '',
										'drag_drop_upload' => true,
										'tabfocus_elements' => 'content-html,save-post',
										'editor_class' => 'editor-hidden',
										'editor_height' => 300,
										'tinymce' => array(
											'resize' => false,
											'add_unload_trigger' => false,
										),
									)); ?>
								</div>
							</div>
						</div>
					</div> */ ?>
				</div>
			</div>
		</div>
	</div>
</div>