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
		<h3>Add New Page</h3>
		<div id="post-body-content">
			<div class="PostForm__name-wrap input-group <?php if (Validator::hasError('name')) echo 'PostForm--error' ?>">
				<label class="form-label">Project name here</label>
				<input type="text" name="name" class="PostForm__name form-control" placeholder="Project name here" value="<?= Validator::old('name', $task->name) ?>">
				<?php if (Validator::hasError('name')): ?>
				<span class="PostForm__error"><?= Validator::get('name') ?></span>
				<?php endif; ?>
			</div>

			<div class="PostForm__title-wrap input-group <?php if (Validator::hasError('title')) echo ' PostForm--error' ?>">
				<label class="form-label">Enter title here</label>
				<input type="text" id="title" name="title" class="PostForm__title form-control" placeholder="Enter title here" value="<?= Validator::old('title', $task->content['title']) ?>">
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
					<a id="cancel-permalink" class="cancel btn btn-outline-primary" style="display: none">Cancel</a><br />
					<div class="howto">
						The non-editable URL structure is determined by your <a href="/wp-admin/options-permalink.php">permalink settings</a>.
					</div>
					<p id="too-many-posts" class="notice notice-error" style="display: none;">Your project contains more than 5,000 pages. While Improve SEO can create hundreds of thousands of posts per project, it is recommended to split your project into multiple smaller projects if you are using shared hosting for maximum efficiency. VPS and dedicated server users can ignore this message. </p>
				</div>
			</div>
						
			<div class="PostForm__body-wrap <?php if (Validator::hasError('content')) echo ' PostForm--error' ?>">
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

			<div id ="post_form_buttons" class="PostForm__buttons">
				<button name="create" type="submit" formtarget="_self" class="btn btn-outline-primary">
				<?php if($_GET['action']=='edit_post'):?>
					Update project and posts
				<?php else:?>
					Create Project
				<?php endif;?>
				</button>
				<button name="draft" type="submit" formtarget="_self" class="btn btn-outline-primary">Save As Draft</button>
				<button id="preview_on" type="submit" class="btn btn-outline-primary" onclick= "openWin()">Post preview</button>
			</div>

			<?php echo $site_link; ?>


			<!-- HTML modal for preview button -->
			<div id="preview_popup" class="modal" style="text-align:center">
				<div id="wh_prev_modal_1"> 					
					<?php $gif_src = IMPROVESEO_DIR . '/assets/images/loader.gif'?>
					<b style="font-size:20px">Generating preview</b>
					<br /><br />
					<img id="preview_rcube" src="<?=$gif_src?>" width="200">
				</div>
				<div id="wh_prev_modal_2"> 
					<b style="font-size:18px">Close preview to continue editing the project</b>
					<br><br>
					<button id="open_win" class="button button-primary" onclick= "closeWin()" rel="modal:close">Close preview</button>
					&nbsp; &nbsp; 
					<button id="close_win" class="button button-primary" onclick= "changeWin()">Switch preview</button>
				</div>
			</div>
			
			<!-- HTML modal for close preview button -->
			<div id="shortcode_popup" class="modal shortcode_popup" tabindex="-1" role="dialog" aria-labelledby="shortcode_popup" aria-hidden="true" data-focus="true">
				<h3>Select ImproveSEO Shortcode</h3>
				<div class="form-wrap">
					<input type="hidden" id="is_shortcode_popup_open" value="no" />
					<p class="hidden improveseo_shortcode_error" id="improveseo_shortcode_error">No shortcodes available. </p>
					<div class="form-group">
						<label for="improveseo_shortcode_type">Select Shortcode Type</label>
						<select class="form-control" id="improveseo_shortcode_type" name="improveseo_shortcode_type">
							<option value="">Select Shortcode Type</option>
							<option value="testimonial">Testimonials</option>
							<option value="googlemap">Google Maps</option>
							<option value="button">Buttons</option>
							<option value="list">Lists</option>
						</select>
					</div>
					<div class="form-group">
						<label for="improveseo_shortcode">Select Shortcode</label>
						<select class="form-control" id="improveseo_shortcode" name="improveseo_shortcode" disabled>
						</select>
					</div>
					<div class="form-group hidden">
						<button type="button" class="btn btn-outline-primary" id="improveseo_shortcode_add_btn">Add</button>
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
						    $cat_pre = $_GET['cat_pre'];
						    $cat_pre = explode(",",$cat_pre);
						    $args = array("hide_empty" => 0,
                            "type"      => "post",      
                            "orderby"   => "name",
                            "order"     => "ASC" );
						    $cats = get_categories($args);
						    foreach($cats as $category){
						        if(in_array($category->term_id, $cat_pre)){
						            $checked = 'checked';
						        }else{
						            $checked = '';
						        }
						        $select.= "<div class='input-group cta-check m-0'><span><input ".$checked." id='".$category->term_id."' type='checkbox' value='".$category->term_id."' name='cats[]'><label for='".$category->term_id."'>".$category->name."</label></span></div>";
						 
						    }
						    echo $select;
						?>
					</div>
				</div>
				
				<!-- Options -->
				<div class="postbox">
					<button type="button" class="handlediv button-link" aria-expanded="true">
						<span class="toggle-indicator" aria-hidden="true"></span>
					</button>
					<h3 class="hndle ui-sortable-handle"><span>Improve SEO Options</span></h3>
					<div class="inside mt-3">
						<p>
							<strong>Max Posts:</strong> <br>
							<span class="d-block mb-2">
								Maximum number of posts to generate. Input `0` if you want to generate all available posts from spintax.
							</span>
							<div class="input-group">
								<input type="number" id="max-posts" name="max_posts" class="form-control" value="<?= Validator::old('max_posts', (int) $task->options['max_posts']) ?>" min="0" />
							</div>
						</p>

						<p>
							<strong>Distribute among authors randomly:</strong> </br>
							<span>
								Distribute posts among <a href="<?= admin_url('admin.php?page=improveseo_authors') ?>">Improve SEO authors</a> randomly.
							</span>
						</p>
						<div class="input-group m-0 cta-check">
							<span>
								<input type="checkbox" id="distribute" name="distribute" value="1" <?= Validator::old('distribute', $task->options['distribute']) == 1 ? 'checked' : ''; ?>>
								<label for="distribute">Distribute</label>
							</span>
						</div>
					</div>
				</div>

				<!-- DripFeed Property -->
				<div class="postbox">
					<button type="button" class="handlediv button-link" aria-expanded="true">
						<span class="toggle-indicator" aria-hidden="true"></span>
					</button>
					<h3 class="hndle ui-sortable-handle"><span>Improve SEO Dripfeed Property</span></h3>
					<div class="inside">
						<?php
							$old_dripfeed_enabler = Validator::old('dripfeed_enabler', $task->options['dripfeed_type'] ? 1 : 0);
						?>
						<div class="input-group my-3 cta-check">
							<span>
								<input id="dripfeed-enabler" name="dripfeed_enabler" type="checkbox" value="1" <?= $old_dripfeed_enabler == 1 ? 'checked' : ''; ?>>
								<label for="dripfeed-enabler" class="selectit"> Enable Feature</label>
							</span>
						</div>

						<div id="dripfeed-wrap" style="display: <?= $old_dripfeed_enabler == 1 ? 'block' : 'none'; ?>;">
							<div class="input-group">
								<label for="dripfeed-type" class="form-label">Dripfeed Type:</label>
								<?php $old_dripfeed_type = Validator::old('dripfeed_type', $task->options['dripfeed_type']) ?>
								<select id="dripfeed-type" name="dripfeed_type" class="form-control">
									<option value="per-day"<?= $old_dripfeed_type == 'per-day' ? ' selected' : '' ?>>X posts/pages per day</option>
									<option value="over-days"<?= $old_dripfeed_type == 'over-days' ? ' selected' : '' ?>>Whole project dripped over X days</option>
								</select>
							</div>
							<div class="input-group <?= (Validator::hasError('dripfeed_x')) ? 'PostForm--error' : '' ?>">
								<label for="dripfeed-x" class="form-label">X Parameter:</label>
								<input type="text" id="dripfeed-x" name="dripfeed_x" class="form-control" value="<?= Validator::old('dripfeed_x', $task->options['dripfeed_x']) ?>">
								<?php if (Validator::hasError('dripfeed_x')): ?>
								<span class="PostForm__error"><?= Validator::get('dripfeed_x') ?></span>
								<?php endif; ?>
							</div>
						</div>
						<p>
							It is not recommended to dripfeed multiple projects at the same time on the same server as it puts an elongated load on your server. Creating all pages at once is recommended unless you have a reason not to.
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
							<input type="text" id="tagsinput" class="form-control" size="16" autocomplete="off" value="<?= Validator::old('tags', $task->options['tags']) ?>">
							<div class="input-group-append">
								<a id="add-tags" class="btn btn-primary ">Add</a>
							</div>
						</div>
						<small class="howto">Separate tags with commas</small>
						<div id="tags" class="tagchecklist"></div>

						<div class="from-group cta-check my-3">
							<span>
								<input id="noindex_tags" name="noindex_tags" type="checkbox" value="1" <?= Validator::old('noindex_tags', $task->options['noindex_tags']) == 1 ? 'checked' : ''; ?>>
								<label for="noindex_tags" class="selectit ">Noindex tags</label>
							</span>
						</div>
						<p class="howto">Helps fight duplicate content on tag pages; not recommended</p>
					</div>
				</div> */ ?>

				<?php if ($word_ai_email && $word_ai_pass): ?>
				<!-- Word AI -->
				<div class="postbox">
					<button type="button" class="handlediv button-link" aria-expanded="true">
						<span class="toggle-indicator" aria-hidden="true"></span>
					</button>
					<h3 class="hndle ui-sortable-handle"><span>Word AI Options</span></h3>
					<div class="inside mt-3">
						<p>
							<a href="<?= IMPROVESEO_DIR ?>/wordai.php" onclick="return WordAI.start(this)">Launch Word AI Console</a>
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
								<input id="enable_categorization" name="enable_categorization" type="checkbox" value="1" <?= $old_enable_categorization == 1 ? 'checked' : ''; ?>>
								<label for="enable_categorization" class="selectit">Enable Categorization</label>
							</span>
						</div>
						<p class="howto mb-3">
							This will create pages like <strong>/plumber/michigan/troy/48098</strong>, instead of <strong>/plumber-michigan-troy-48098</strong> <br>
							In this case, "plumber" would be the URL prefix
						</p>
						<div class="input-group">
							<label for="url-prefix" class="form-label" >URL Prefix</label>
							<input type="text" name="permalink_prefix" class="form-control" value="<?= Validator::old('permalink_prefix', $task->options['permalink_prefix']) ?>">
						</div>
						<p class="howto">
							This will override Wordpress settings and put a heavy load on your server. It is not recommended.
						</p>
					</div>
				</div>
				*/ ?>

				<!-- Images Scraper -->
				<div class="postbox">
					<button type="button" class="handlediv button-link" aria-expanded="true">
						<span class="toggle-indicator" aria-hidden="true"></span>
					</button>
					<h3 class="hndle ui-sortable-handle"><span>Improve SEO Images</span></h3>
					<div class="inside mt-3">
						<?php
							if(!empty($task->options['exif_locations']))
								$old_exif_enabler = Validator::old('exif_enabler', sizeof($task->options['exif_locations']) > 0);
							else
								$old_exif_enabler = 0;

							if (!empty($google_api_key)):
						?>
						<div class="input-group cta-check">
							<span>
								<input id="exif-enabler" name="exif_enabler" type="checkbox" value="1" <?= $old_exif_enabler == 1 ? 'checked' : ''; ?>>
								<label for="exif-enabler" class="selectit">Enable Image EXIF</label>
							</span>
						</div>
						<?php else: ?>
							<div class="PixabayKeyWarning info-sec">
								Please, enter Google Maps API Key in <a href="<?php echo admin_url('admin.php?page=improveseo_settings'); ?>">Plugin Settings</a>.
							</div>
						<?php endif; ?>

						<div id="exif-wrap" style="display: <?= $old_exif_enabler == 1 ? 'block' : 'none' ?>">
							<div class="input-group cta-check">
								<span>
									<input id="use-post-location" name="use_post_location" type="checkbox" value="1" <?= Validator::old('use_post_location', $task->options['use_post_location']) == 1 ? 'checked' : ''; ?>>
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

								if (!empty($pixabay_key)):
							?>
							<input type="hidden" id="pixabay-api-key" value="<?= $pixabay_key ?>">
							<a href="<?= IMPROVESEO_DIR ?>/imagescraper.php" title="Image Scraper" onclick="return ImageScraper.start(this)">Launch Images Scraper</a>
							<?php else: ?>
								<div class="PixabayKeyWarning info-sec">
									Please, enter Pixabay API Key in <a href="<?php echo admin_url('admin.php?page=improveseo_settings'); ?>">Plugin Settings</a>.
								</div>
							<?php endif; ?>
						</p>
					</div>
				</div>

				<!-- Videos Scraper -->
				<div class="postbox">
					<button type="button" class="handlediv button-link" aria-expanded="true">
						<span class="toggle-indicator" aria-hidden="true"></span>
					</button>
					<h3 class="hndle ui-sortable-handle"><span>Improve SEO Videos</span></h3>
					<div class="inside mt-3">
						<p>
							<?php
								$youtube_key = get_option('improveseo_google_api_key');

								if (!empty($youtube_key)):
							?>
							<input type="hidden" id="youtube-api-key" value="<?= $youtube_key ?>">
							<a href="<?= IMPROVESEO_DIR ?>/videoscraper.php" title="Image Scraper" onclick="return VideoScraper.start(this)">Launch Videos Scraper</a>
							<?php else: ?>
								<div class="PixabayKeyWarning">
									Please, enter YouTube API Key in <a href="<?php echo admin_url('admin.php?page=improveseo_settings'); ?>">Plugin Settings</a>.
								</div>
							<?php endif; ?>
						</p>
					</div>
				</div>
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
						<h3 class="hndle"><span>Improve SEO Options</span></h3>
						<div class="inside">
							<div class="customizer-wrapper seo-post-meta-wrapper">
								<?php
									$old_on_page_seo = Validator::old('on_page_seo', !empty($task->options['custom_title']) || !empty($task->options['custom_description']) || !empty($task->options['custom_keywords']));
								?>
								<div class="input-group my-4 cta-check">
									<span>
										<input id="on-page-seo" name="on_page_seo"  type="checkbox" value="1"<?= $old_on_page_seo == 1 ? ' checked' : '' ?>>
										<label for="on-page-seo">Enable Improve SEO On-Page Customizer</label>
									</span>
								</div>
								<div id="on-page-seo-wrap" style="display: <?= $old_on_page_seo == 1 ? 'block' : 'none' ?>;">
									<div id="google-preview" style="display:none;">
										<div class="input-group mt-4 cta-check">
											<label for="preview-label" class="form-label">Google Preview</label>
											<span>
												<input id="mobile-preview" name="preview-type"  type="radio" value="mobile" checked />
												<label for="mobile-preview">Mobile result</label>
												&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
												<input id="desktop-preview" name="preview-type"  type="radio" value="desktop" />
												<label for="desktop-preview">Desktop result</label>
											</span>
										</div>
										<div id="google-mobile-preview" class="google-mobile-preview">
											<div class="google-preview-content-wrapper">
												<div class="google-preview-content-wrapper2">
													<div class="google-preview-logo">
														<i class="fa fa-globe"></i>
														<span class="google-preview-mobile-url">
															<span class="google-preview-mobile-disabled">localhost</span> › demo
														</span>
													</div>
													<div class="google-mobile-preview-pagename">
														Demo - test
													</div>
												</div>
												
											</div>
											<div class="google-preview-content-wrapper"></div>
										</div>
									</div>
									<div class="input-group">
										<label for="custom-title" class="form-label">Title</label>
										<div class="input-prefix">
											<input id="custom-title" name="custom_title" class="form-control" type="text" class="full-width form-control textarea-control" placeholder="Title" value="<?= Validator::old('custom_title', $task->options['custom_title']) ?>">
											<span>Ex.</span>
										</div>
									</div>
									<div class="input-group">
										<label for="custom-description" class="form-label">Description</label>
										<div class="input-prefix">
											<textarea id="custom-description" placeholder="Description" name="custom_description" class="full-width textarea-control"><?= Validator::old('custom_description', $task->options['custom_description']) ?></textarea>
											<span>Ex.</span>
										</div>
									</div>
									<div class="input-group">
										<label for="custom-keywords" class="form-label">Keywords</label>
										<div class="input-prefix">
											<textarea id="custom-keywords" placeholder="Keywords" name="custom_keywords" class="full-width textarea-control"><?= Validator::old('custom_keywords', $task->options['custom_keywords']) ?></textarea>
											<span>Ex.</span>
										</div>
									</div>
								</div>
							</div>
							<div class="seo-post-meta-wrapper feature-wrapper">
								<div class="input-group my-4 cta-check">
									<span>
										<input id="local-seo-enabler" name="local_seo_enabler" type="checkbox" value="1" <?= $old_local_seo_enabler == 1 ? 'checked' : ''; ?>>
										<label for="local-seo-enabler">Enable Improve SEO Local SEO Feature</label>
									</span>
								</div>
								<div id="local-seo-wrap" style="display: <?= $old_local_seo_enabler == 1 ? 'block' : 'none'; ?>;">
									<div class="input-group cta-check">
										<span>
											<input id="local-randomize" name="local_randomize" type="checkbox" value="1" <?= Validator::old('local_randomize', $task->options['local_randomize']) == 1 ? 'checked' : '' ?>>
											<label for="local-randomize" class="form-label">Randomize Results</label>
										</span>
									</div>
									<div class="input-group">
										<label for="local-country" class="form-label">Country</label>
										<?php
											$countries = array('us' => 'United States', 'uk' => 'United Kingdom');
											$countryModel = new Country();
											$otherCountries = $countryModel->all('name');
										?>
										<select id="local-country" class="form-control" name="local_country">
											<option value>- Select Country -</option>
											<?php foreach ($countries as $short => $name): ?>
											<option value="<?= $short ?>" <?= Validator::old('local_geo_country', $task->options['local_geo_country']) == $short ? 'selected':'' ?>>
												<?= $name ?>
											</option>
											<?php endforeach; ?>
											<?php foreach ($otherCountries as $other): ?>
											<option value="<?= $other->id ?>">
												<?= $other->name ?>
											</option>
											<?php endforeach; ?>
										</select>
									</div>
									<div class="input-group">
										<label>Choose locations</label> <br>
										<small class="d-block w-100">
											Press 'Shift + Left Mouse' to select all tree nodes
										</small>
										<div id="jstree"></div>
										<?php if ($task->options['local_geo_locations']): ?>
										<script>
											var local_geo_locations = <?= json_encode($task->options['local_geo_locations']) ?>;
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
									<span>
										<input id="schema" name="schema" type="checkbox" value="1"<?= $old_schema == 1 ? ' checked' : '' ?>>
										<label for="schema" class="form-label">Enable Improve SEO Schema</label>
									</span>
								</div>
								<div id="schema-wrap" style="display: <?= $old_schema == 1 ? 'block' : 'none' ?>;">
									<div class="input-group my-4 cta-check">
										<span>
											<input type="checkbox" name="hide_schema" id="hide-schema" value="1" <?= Validator::old('hide_schema', $task->options['hide_schema']) ? 'checked' : '' ?>>
											<label for="hide-schema" class="form-label">Hide schema from authors</label>
										</span>
									</div>
									<div class="input-group">
										<label for="schema-business" class="form-label">Business Name</label>
										<div class="input-prefix">
											<input id="schema-business" placeholder="Business Name" name="schema_business" type="text" class="full-width form-control" value="<?= Validator::old('schema_business', $task->options['schema_business']) ?>">
											<span>Ex.</span>
										</div>
									</div>
									<div class="input-group">
										<label for="schema-description" class="form-label">Description</label>
										<div class="input-prefix">
											<textarea id="schema-description" placeholder="Description" name="schema_description" class="full-width textarea-control"><?= Validator::old('schema_description', $task->options['schema_description']) ?></textarea>
											<span>Ex.</span>
										</div>
									</div>
									<div class="input-group">
										<label for="schema-email" class="form-label">E-mail</label>
										<div class="input-prefix">
											<input type="text" id="schema-email" placeholder="E-mail" name="schema_email" class="full-width form-control" value="<?= Validator::old('schema_email', $task->options['schema_email']) ?>">
											<span>Ex.</span>
										</div>
									</div>
									<div class="input-group">
										<label for="schema-telephone" class="form-label">Telephone</label>
										<div class="input-prefix">
											<input type="tel" id="schema-telephone" placeholder="Telephone" name="schema_telephone" class="full-width form-control" value="<?= Validator::old('schema_telephone', $task->options['schema_telephone']) ?>">
											<span>Ex.</span>
										</div>
									</div>
									
										
									<div class="input-group">
										<label for="schema-social" class="form-label">Social pages</label>
										<div class="input-prefix">
											<textarea id="schema-social" placeholder="Social pages" name="schema_social" class="full-width textarea-control"><?= Validator::old('schema_social', $task->options['schema_social']) ?></textarea>
											<span>Ex.</span>
										</div>
									</div>
									<div class="input-group">
										<label for="schema-rating-object" class="form-label">Rating Object</label>
										<div class="input-prefix">
											<input id="schema-rating-object" placeholder="Rating Object" name="schema_rating_object" type="text" class="full-width form-control" value="<?= Validator::old('schema_rating_object', $task->options['schema_rating_object']) ?>">
											<span>Ex.</span>
										</div>
									</div>
									<div class="input-group">
										<label for="schema-rating" class="form-label">Rating</label>
										<div class="input-prefix">
											<input id="schema-rating" placeholder="Rating" name="schema_rating" type="text" class="full-width form-control" value="<?= Validator::old('schema_rating', $task->options['schema_rating']) ?>">
											<span>Ex.</span>
										</div>
									</div>
									<div class="input-group">
										<label for="schema-rating-count" class="form-label">Rating Count</label>
										<div class="input-prefix">
											<input id="schema-rating-count" placeholder="Rating Count" name="schema_rating_count" type="text" class="full-width form-control" value="<?= Validator::old('schema_rating_count', $task->options['schema_rating_count']) ?>">
											<span>Ex.</span>
										</div>
									</div>
									<div class="input-group">
										<label for="schema-address" class="form-label">Address</label>
										<div class="input-prefix">
											<textarea id="schema-address" placeholder="Address" name="schema_address" class="full-width textarea-control"><?= Validator::old('schema_address', $task->options['schema_address']) ?></textarea>
											<span>Ex.</span>
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
							<p id="channel-howto" class="howto my-3" <?= $old_enable_categorization == 1 ? 'style="display: none;"' : '' ?>>
								You need enable categorization for using channel pages.
							</p>
							<div class="seo-post-meta-wrapper">
								<div class="input-group my-4 cta-check">
									<span>
										<input id="state-channel-page" name="state_channel_page" type="checkbox" value="1" <?= $old_enable_categorization == 1 && $old_state_channel == 1 ? 'checked' : ''; ?> <?= $old_enable_categorization == 1 ? '' : 'disabled' ?>>
										<label for="state-channel-page" class="selectit">Enable State Channel Pages</label>
									</span>
								</div>

								<div id="state-channel-page-wrap" <?= $old_state_channel == 1 ? '' : 'style="display: none;"'; ?>>
									<div class="PostForm__title-wrap input-group <?php if (Validator::hasError('state_channel_title')) echo ' PostForm--error' ?>">
										<label class="form-label"> Enter title here </label>
										<div class="input-prefix">
											<input type="text" id="state-channel-title" name="state_channel_title" class="PostForm__title form-control" placeholder="Enter title here" value="<?= Validator::old('state_channel_title', $task->content['state_channel_title']) ?>">
											<span>Ex.</span>
										</div>
										<?php if (Validator::hasError('state_channel_title')): ?>
										<span class="PostForm__error"><?= Validator::get('state_channel_title') ?></span>
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
										<input id="city-channel-page" name="city_channel_page" type="checkbox" value="1" <?= $old_enable_categorization == 1 && $old_city_channel == 1 ? 'checked' : ''; ?> <?= $old_enable_categorization == 1 ? '' : 'disabled' ?>>
										<label for="city-channel-page" class="selectit">Enable City Channel Pages</label>
									</span>
								</div>

								<div id="city-channel-page-wrap" <?= $old_city_channel == 1 ? '' : 'style="display: none;"'; ?>>
									<div class="PostForm__title-wrap input-group <?php if (Validator::hasError('city_channel_title')) echo ' PostForm--error' ?>">
										<label class="form-label">Enter title here</label>
										<div class="input-prefix">
											<input type="text" id="city-channel-title" name="city_channel_title" class="PostForm__title form-control" placeholder="Enter title here" value="<?= Validator::old('city_channel_title', $task->content['city_channel_title']) ?>">
											<span>Ex.</span>
										</div>
										<?php if (Validator::hasError('city_channel_title')): ?>
										<span class="PostForm__error"><?= Validator::get('city_channel_title') ?></span>
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
<?php
$admin_url = admin_url('admin.php'); 
/*?>
<script type="text/javascript">
	
				let form_action_old_wh = document.getElementById('main_form').action;
				//let reset_hidden_input_wh=document.getElementById('hidden_input_wh').value;
				function openWin() {
					document.getElementById("main_form").action = "<?php echo $admin_url; ?>?page=improveseo&action=do_create_post&noheader=true";
					//var prev_hidden_input = "post";
					//document.getElementById('hidden_input_wh').setAttribute('value', prev_hidden_input);
					
					let max_no_posts_old = document.getElementById('max-posts').value;					
					if (max_no_posts_old > 50) {
						alert("Recommended no. of total posts for preiew is less than 50");				
					}
					
					var myForm = document.getElementById('main_form');
					myForm.onsubmit = function() {
						myWindow = window.open('about:blank','Popup_Window','toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=0,width=200,height=200,left = 5000,top = 5000');
						this.target = 'Popup_Window';
					}
					
					var wh_modal_1_style = document.getElementById("wh_prev_modal_1");
					var wh_modal_2_style = document.getElementById("wh_prev_modal_2");
					wh_modal_1_style.style.display = "block";
					wh_modal_2_style.style.display = "none";
					
					var preview_id= 0;					
					var check_status = setInterval(function() {
						
												if(myWindow.location.href.indexOf('?page=improveseo_projects') > 0) {
													var location = myWindow.location.search;
													var location_2 = location.split('&');
													var location_3 = location_2[1];
													var preview_ids = location_3.split('=');
													preview_id = preview_ids[1];
													
													myWindow.location.href= "<?php echo $admin_url; ?>?page=improveseo_projects&post_preview=true"; 
													clearInterval(check_status);
												}

										}, 2000);
					
					var check_status_2 = setInterval(function() {
						
												if(myWindow.location.href.indexOf('?id=') > 0) {
													wh_modal_1_style.style.display = "none";
													wh_modal_2_style.style.display = "block";																		myWindow.resizeTo(720, 360);																					myWindow.moveTo(312, 234); 
													clearInterval(check_status_2);
												}

										}, 2500);
					
					
					function preview_delete_ajax(prev_id){
						jQuery
						.ajax({
							url : ajaxurl,
							data : ({
								action : 'preview_delete_ajax',
								prev_id : prev_id,
								ajax : 1,
							}),
							success : function(data) {
								//alert(data);
							}
						});
					}
					
					
					var check_prev_win = setInterval(function() {
		
						if(typeof (myWindow) == 'undefined' || myWindow.closed) {
							if(jQuery.modal.isActive()){
							   preview_delete_ajax(preview_id);
								jQuery.modal.close();
								document.getElementById("main_form").action = form_action_old_wh;
								//document.getElementById('hidden_input_wh').setAttribute('value', reset_hidden_input_wh);
								var myForm = document.getElementById('main_form');
								myForm.onsubmit = function() {}
							   }
							clearInterval(check_prev_win);
							
							}
						}, 500);
					
				}
				


				function closeWin() {
					
					jQuery.modal.close();
					document.getElementById("main_form").action = form_action_old_wh;
					//document.getElementById('hidden_input_wh').setAttribute('value', reset_hidden_input_wh);
					var location = myWindow.location.search;
					var preview_ids = location.split("=");
					var id = preview_ids[1];
					myWindow.location.href= "<?php echo $admin_url; ?>?page=improveseo_projects&action=delete&id="+ id +"&noheader=true";
					setTimeout(function() {
						myWindow.close();
					}, 10000);
					
					var myForm = document.getElementById('main_form');
					myForm.onsubmit = function() {}

				}
				
				function changeWin(){
					myWindow.focus(); 
					myWindow.location.href= "<?php echo $admin_url; ?>?page=improveseo_projects&post_preview=true";
				}
	

</script>
*/ ?>