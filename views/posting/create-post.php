<?php



use ImproveSEO\View;
use ImproveSEO\Validator;



?>

<!-- <?php View::startSection('breadcrumbs') ?>

<a href="<?= admin_url('admin.php?page=improveseo_dashboard') ?>">Improve SEO</a>

&raquo;

<span>Create Post</span>

<?php View::endSection('breadcrumbs') ?> -->





<?php improveseo\View::render('notices.notice'); ?>



<?php View::startSection('content') ?>

<h1 class="hidden">Create Post</h1>
<form id="main_form"
	action="<?php echo admin_url('admin.php?page=improveseo_dashboard&action=do_create_post&noheader=true'); ?>"
	class="form-wrap" method="post">

	<div class="style_create_page_form">
		<div class="CreatePost improveseo_wrapper create_page_cont_1">

			<section class="project-section d-flex flex-row  justify-content-between align-items-center pb-2">
				<div class="head-bar">
					<img src="<?php echo WT_URL . '/assets/images/project-list-logo.png' ?>" alt="ImproveSeo">
					<h1> ImproveSEO | 2.0.11 </h1>
					<span>Pro</span>
				</div>


			</section>
			<div class="box-top">
				<ul class="breadcrumb-seo">
					<li><a href="<?= admin_url('admin.php?page=improveseo_dashboard') ?>">Improve SEO</a></li>
					<li>Create a Post</li>
				</ul>
			</div>


			<?php

			/*******************/

		

			/*******************/

			?>



			<?php

			$post_type = 'post';

			$form_id_preview = 'create_post';

			improveseo\View::render('posting.form', compact('post_type'));

			?>




		</div>
		<div id="postbox-container-1" class="postbox-container create_page_cont_2">
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
	</div>
</form>
<?php
	generateAIpopup();
?>
<?php View::endSection('content') ?>



<?php echo View::make('layouts.main') ?>