<?php



use ImproveSEO\View;
use ImproveSEO\Validator;
global $ai_modal_type;
$ai_modal_type = 'single';


?>

<?php
// TinyMCE: render plain spaces without converting to NBSP and avoid encoding them.
// Scoped to this screen; runs before wp_editor initializes in posting.form.
add_filter('tiny_mce_before_init', function ($init) {
	$style = 'body.mce-content-body{white-space:pre-wrap;}';
	if (!empty($init['content_style'])) {
		$init['content_style'] .= ' ' . $style;
	} else {
		$init['content_style'] = $style;
	}
	// Prevent TinyMCE from encoding spaces as entities like &nbsp;
	$init['entity_encoding'] = 'raw';
	// Do not force tabs/non-breaking spaces
	$init['nonbreaking_force_tab'] = false;
	return $init;
}, 50);

// Server-side safety: strip NBSP when saving from the ImproveSEO single create screen
add_filter('wp_insert_post_data', function ($data, $postarr) {
	if (isset($_POST['ai_modal_type']) && $_POST['ai_modal_type'] === 'single' && isset($data['post_content'])) {
		$data['post_content'] = str_replace('&nbsp;', ' ', $data['post_content']);
		$data['post_content'] = preg_replace('/\xC2\xA0/u', ' ', $data['post_content']); // U+00A0
	}
	return $data;
}, 10, 2);
?>

<!-- <?php View::startSection('breadcrumbs') ?>

<a href="<?= admin_url('admin.php?page=improveseo_dashboard') ?>">Improve SEO</a>

&raquo;

<span>Create Post</span>

<?php View::endSection('breadcrumbs') ?> -->





<?php improveseo\View::render('notices.notice'); ?>

<?php if ( isset( $_GET['from'] ) && $_GET['from'] === 'onboarding' ) : ?>
<script>window.iseoGuideConfig = { active: true };</script>
<?php endif; ?>

<?php View::startSection('content') ?>

<h1 class="hidden">Create Post</h1>
<form id="main_form"
	action="<?php echo admin_url('admin.php?page=improveseo_dashboard&action=do_create_post&noheader=true'); ?>"
	class="form-wrap" method="post" onsubmit="if(typeof copyAIFieldsToHiddenInputs==='function') copyAIFieldsToHiddenInputs();">
	<input type="hidden" name="ai_modal_type" value="single" />
	<!-- Hidden fields to capture AI popup values for project details -->
	<input type="hidden" name="ai_seed_keyword" id="ai_seed_keyword_hidden" />
	<input type="hidden" name="ai_seed_options" id="ai_seed_options_hidden" />
	<input type="hidden" name="ai_content_type" id="ai_content_type_hidden" />
	<input type="hidden" name="ai_nos_of_words" id="ai_nos_of_words_hidden" />
	<input type="hidden" name="ai_point_of_view" id="ai_point_of_view_hidden" />
	<input type="hidden" name="ai_content_lang" id="ai_content_lang_hidden" />
	<input type="hidden" name="ai_details_to_include" id="ai_details_to_include_hidden" />
	<input type="hidden" name="ai_call_to_action" id="ai_call_to_action_hidden" />
	<input type="hidden" name="ai_image_option" id="ai_image_option_hidden" />
	<input type="hidden" name="ai_generated_title" id="ai_generated_title_hidden" />
	<input type="hidden" name="ai_for_testing_only" id="ai_for_testing_only_hidden" value="0" />
    
	<div class="style_create_page_form iseo-single-post-view" style="display: none;">
		<div class="CreatePost improveseo_wrapper create_page_cont_1">

			<section class="project-section d-flex flex-row  justify-content-between align-items-center pb-2">
				<div class="head-bar">
					<img src="<?php echo improveseo_logo_url() ?>" alt="ImproveSeo">
					<h1>ImproveSEO | <?php echo IMPROVESEO_VERSION; ?></h1>
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
					<?php // stopPropagation keeps the marker from also collapsing the postbox, whose
					      // toggle is bound to this handle. ?>
					<h3 class="hndle ui-sortable-handle"><span>Categories</span>
						<span class="iseo-info-tip" tabindex="0" role="button" aria-label="Why assign a category?" onclick="event.stopPropagation();">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
							<?php // --end: this sidebar is only 28% wide, so the box opens leftward. ?>
							<span class="iseo-info-tip-bubble iseo-info-tip-bubble--field iseo-info-tip-bubble--end" role="tooltip">
								Choose one or more categories to keep your posts organized on your website. Improve SEO is selected by default &mdash; you can add other categories or create a new one below.
							</span>
						</span>
					</h3>
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
						<!-- Add a new category inline (reuses the create_bulk_category AJAX handler).
						     A brand-new category is appended as a checked cats[] checkbox so it behaves
						     exactly like a pre-existing one and is saved with the post. -->
						<div class="iseo-add-cat-row" style="margin-top:12px; display:flex; gap:6px; align-items:center;">
							<input type="text" id="new_category_name_form" placeholder="New category name"
								style="flex:1; min-width:0; height:42px; padding:0 18px; box-sizing:border-box; border:1px solid #e9e9e9; border-radius:50px;" />
							<button type="button" id="add_category_form_btn" class="button"
								data-nonce="<?php echo esc_attr( wp_create_nonce( 'create_category_nonce' ) ); ?>"
								style="border-radius:50px; white-space:nowrap; height:42px; box-sizing:border-box; display:inline-flex; align-items:center; justify-content:center; line-height:1; padding-left:22px; padding-right:22px;">Add</button>
						</div>
						<p id="category_add_message_form" style="margin:6px 0 0; font-size:12px;"></p>
						<script>
						(function ($) {
							function iseoAddCategoryFromForm() {
								var $btn = $('#add_category_form_btn');
								var name = $.trim($('#new_category_name_form').val() || '');
								var $msg = $('#category_add_message_form');
								if (!name) { $msg.text('Please enter a category name').css('color', '#dc3232'); return; }
								$.ajax({
									url: ajaxurl,
									type: 'POST',
									data: { action: 'create_bulk_category', cat_name: name, nonce: $btn.data('nonce') },
									beforeSend: function () { $btn.prop('disabled', true).text('Adding...'); },
									success: function (r) {
										if (r && r.success) {
											var id = r.data.term_id;
											var safe = $('<div>').text(r.data.name).html();
											var $box = $btn.closest('.inside');
											if (!$box.find('input[name="cats[]"][value="' + id + '"]').length) {
												$box.find('.iseo-add-cat-row').before(
													"<div class='input-group cta-check m-0'><span>" +
													"<input checked id='" + id + "' type='checkbox' value='" + id + "' name='cats[]'>" +
													"<label for='" + id + "'>" + safe + "</label></span></div>"
												);
											} else {
												$box.find('input[name="cats[]"][value="' + id + '"]').prop('checked', true);
											}
											$('#new_category_name_form').val('');
											$msg.text('Category added!').css('color', '#46b450');
											setTimeout(function () { $msg.text(''); }, 3000);
										} else {
											$msg.text((r && r.data) || 'Error creating category').css('color', '#dc3232');
										}
									},
									error: function () { $msg.text('Error creating category. Please try again.').css('color', '#dc3232'); },
									complete: function () { $btn.prop('disabled', false).text('Add'); }
								});
							}
							$(document).on('click', '#add_category_form_btn', iseoAddCategoryFromForm);
							// Enter in the name field adds the category instead of submitting the whole form.
							$(document).on('keydown', '#new_category_name_form', function (e) {
								if (e.which === 13) { e.preventDefault(); iseoAddCategoryFromForm(); }
							});
						})(jQuery);
						</script>
					</div>
				</div>
				<div class="postbox">
					<button type="button" class="handlediv button-link" aria-expanded="true">
						<span class="toggle-indicator" aria-hidden="true"></span>
					</button>
					<h3 class="hndle ui-sortable-handle"><span>Google Preview</span>
						<span class="iseo-info-tip" tabindex="0" role="button" aria-label="What is the Google preview?" onclick="event.stopPropagation();">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
							<span class="iseo-info-tip-bubble iseo-info-tip-bubble--field iseo-info-tip-bubble--end is-interactive" role="tooltip">
								Below you can preview how your post will show inside Google on their search results page. The below meta title and meta description were approved by you inside the Wizard. You can make now further edits as needed.
								<span class="iseo-tip-para">Note: If you have another SEO plugin installed on this website, the meta title and meta description for this post might be generated according to those SEO plugins, and may differ and override the meta title and description below. <a href="https://account.improveseoplugin.com/tutorial" target="_blank" rel="noopener noreferrer">Learn More</a></span>
							</span>
						</span>
					</h3>
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
										style="padding: 15px 22px; border-radius: 50px !important; border-color: #e9e9e9; margin-bottom: 20px; "
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
								<textarea id="custom-description" style="border-radius: 8px; border-color: #e9e9e9; padding: 15px 22px;"
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

<script>
jQuery(document).ready(function($) {
    // In onboarding guide mode the guide reveals and controls the form.
    // In normal mode: hide the form, auto-open the AI modal, then reveal form.
    if (!window.iseoGuideConfig || !window.iseoGuideConfig.active) {
        // Ensure form starts hidden with opacity
        $('.style_create_page_form').css({
            'opacity': '0',
            'visibility': 'hidden'
        });

        setTimeout(function() {
            $('#generate_ai_popup_open').trigger('click');

            // Smoothly reveal the form after modal opens (remove inline display:none and fade in)
            setTimeout(function() {
                $('.style_create_page_form')
                    .removeAttr('style')
                    .css({
                        'opacity': '0',
                        'visibility': 'visible'
                    })
                    .animate({
                        'opacity': '1'
                    }, 400);
            }, 350); // Wait for modal animation to complete
        }, 100); // Small initial delay to ensure page is fully rendered
    }
    
    // Also handle manual button clicks
    $('#generate_ai_popup_open').on('click', function() {
        setTimeout(function() {
            $('.style_create_page_form')
                .removeAttr('style')
                .css({
                    'opacity': '1',
                    'visibility': 'visible'
                });
        }, 350);
    });
    
    // Handle modal close - keep form visible after first open
    $('#close_single_post, #exampleModal1 .close').on('click', function() {
        $('.style_create_page_form')
            .removeAttr('style')
            .css({
                'opacity': '1',
                'visibility': 'visible'
            });
    });
});
</script>

<script>
(function () {
	function normalizeSpaces(s) {
		if (typeof s !== 'string') return s;
		
		// 1. Replace &nbsp; and U+00A0 with regular spaces
		s = s.replace(/&nbsp;/g, ' ').replace(/\u00A0/g, ' ');
		
		// 2. Collapse multiple blank lines (3+) into max 2 newlines (one blank line)
		s = s.replace(/\n{3,}/g, '\n\n');
		
		// 3. Remove <p>&nbsp;</p> empty paragraph artifacts
		s = s.replace(/<p>\s*(&nbsp;|\u00A0|\s)*\s*<\/p>/gi, '<p></p>');
		
		return s;
	}

	function onEditorReady(id, cb) {
		(function wait() {
			if (window.tinymce && tinymce.get(id)) cb(tinymce.get(id));
			else setTimeout(wait, 100);
		})();
	}

	function attachGuards(editor) {
		// Visual rendering: preserve spaces without NBSP in the editor UI
		try { editor.getBody().style.whiteSpace = 'pre-wrap'; } catch (e) {}

		// Normalize content on all main TinyMCE pipelines
		editor.on('BeforeSetContent', function (e) { if (e.content) e.content = normalizeSpaces(e.content); });
		editor.on('GetContent',        function (e) { if (e.content) e.content = normalizeSpaces(e.content); });
		editor.on('SaveContent',       function (e) { if (e.content) e.content = normalizeSpaces(e.content); });
		editor.on('PastePreProcess',   function (e) { if (e.content) e.content = normalizeSpaces(e.content); });
		editor.on('PastePostProcess',  function (e) {
			if (e.node && e.node.innerHTML) e.node.innerHTML = normalizeSpaces(e.node.innerHTML);
		});

		// Clean when switching between Visual/Text tabs
		jQuery(document).on('click', '#content-html, #content-tmce', function () {
			setTimeout(function() {
				var raw = editor.getContent({ format: 'raw' });
				editor.setContent(normalizeSpaces(raw), { format: 'raw' });
			}, 50);
		});

		// Final guard on submit (covers preview/draft/publish and this form)
		jQuery(document).on('submit', '#post, #main_form', function () {
			var ed = tinymce.get('content');
			if (ed) {
				var raw = ed.getContent({ format: 'raw' });
				ed.setContent(normalizeSpaces(raw), { format: 'raw' });
			}
			var ta = jQuery('#content');
			if (ta.length) ta.val(normalizeSpaces(ta.val()));
		});
	}

	document.addEventListener('DOMContentLoaded', function () {
		onEditorReady('content', attachGuards);
	});
})();
</script>

<?php
// When arriving from the onboarding wizard, pre-fill #seed_keyword with the
// suggested keyword that was built from the business details the user entered.
if ( isset( $_GET['from'] ) && $_GET['from'] === 'onboarding' ) :
	$_service = esc_js( get_option( 'improveseo_business_service', '' ) );
	$_city    = esc_js( get_option( 'improveseo_business_city',    '' ) );
	$_kw      = $_service ?: 'seo services';
	if ( $_city ) $_kw .= ' in ' . $_city;
?>
<script>
jQuery(document).ready(function($) {
	var kw = <?php echo wp_json_encode( $_kw ); ?>;
	if (kw) {
		// Pre-fill on DOM ready (covers cases where the modal opens instantly)
		$('#seed_keyword').val(kw);
		// Also fill once the modal opens, in case it renders after this script
		$(document).on('click', '#generate_ai_popup_open', function() {
			setTimeout(function() { $('#seed_keyword').val(kw); }, 50);
		});
		// Fallback: poll briefly in case the modal auto-opens before the click handler fires
		var _tries = 0;
		var _poll = setInterval(function() {
			if ($('#seed_keyword').length) {
				if (!$('#seed_keyword').val()) $('#seed_keyword').val(kw);
				if (++_tries >= 20) clearInterval(_poll);
			}
		}, 150);
	}
});
</script>
<?php endif; ?>

<?php echo View::make('layouts.main') ?>