<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


use ImproveSEO\Validator;
use ImproveSEO\Models\Country;

wp_enqueue_script('post');
?>

<input type="hidden" name="post_type" value="<?php echo  isset($task) ? $task->content['post_type'] : $post_type ?>" />
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
				echo 'PostForm--error' ?>" style="margin-top: 20px;">
					<label class="form-label" style="display:block; font-weight:600; margin-bottom:5px;">Project Name</label>
					<input type="text" name="name" class="PostForm__name form-control" placeholder="Project name here"
						value="<?php echo  Validator::old('name', $task->name) ?>" required>
				<?php if (Validator::hasError('name')): ?>
					<span class="PostForm__error"><?php echo  Validator::get('name') ?></span>
				<?php endif; ?>
			</div>

			<div class="PostForm__title-wrap input-group <?php if (Validator::hasError('title'))
				echo ' PostForm--error' ?>">
					<label class="form-label" style="display:block; font-weight:600; margin-bottom:5px;">Post Title</label>
					<input type="text" id="title" name="title" class="PostForm__title form-control"
						placeholder="Enter title here" value="<?php echo  Validator::old('title', $task->content['title']) ?>">
				<?php if (Validator::hasError('title')): ?>
					<span class="PostForm__error"><?php echo  Validator::get('title') ?></span>
				<?php endif; ?>

				<div id="edit-slug-box">
					<?php
					$old_permalink = Validator::old('permalink', $task->options['permalink']);
					?>
					<?php // Permalink display + "non-editable URL structure" note removed by request (single,
					// bulk and edit screens). Removed server-side so it never renders — no flash/flicker,
					// unlike the earlier JS hide. The hidden permalink input below still submits with the form. ?>
					<input type="hidden" class="form-control" name="permalink" value="<?php echo  $old_permalink ?>">
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
					'setup' => 'function (ed) {
						ed.on("change", function (e) { determineMaxPosts(); });
						/* Show the generated/loaded article from the TOP. After a big insert TinyMCE
						   parks the caret at the end and leaves the editor scrolled down. Reset the
						   editor internal scroll once, for the first substantial content (the article),
						   not on later edits. Lives in the editor setup (server-side) so it runs from
						   the same file the editor is built in, independent of the bundled JS. */
						var iseoTopped = false;
						function iseoTop() {
							try {
								var b = ed.getBody();
								if (b && b.firstChild && ed.selection) { ed.selection.setCursorLocation(b.firstChild, 0); }
								if (ed.getWin) { ed.getWin().scrollTo(0, 0); }
								var d = ed.getDoc && ed.getDoc();
								if (d) { if (d.scrollingElement) d.scrollingElement.scrollTop = 0; if (d.documentElement) d.documentElement.scrollTop = 0; if (d.body) d.body.scrollTop = 0; }
								if (b) { b.scrollTop = 0; }
							} catch (e) {}
						}
						function iseoBurst() { [0, 100, 250, 500, 800, 1200, 1800, 2400].forEach(function (t) { setTimeout(iseoTop, t); }); }
						ed.on("init", function () { try { if ((ed.getContent() || "").length > 50) { iseoTopped = true; iseoBurst(); } } catch (e) {} });
						ed.on("SetContent", function () { if (iseoTopped) return; try { if ((ed.getContent() || "").length < 200) return; } catch (e) { return; } iseoTopped = true; iseoBurst(); });
					}'
				),
			)); ?>
				<?php if (Validator::hasError('content')): ?>
					<span class="PostForm__error"><?php echo  Validator::get('content') ?></span>
				<?php endif; ?>
			</div>

			<?php
			$_isEditMode  = isset($_GET['action']) && $_GET['action'] === 'edit_post';
			$_isPublished = isset($task) && $task->state === 'Published';
			?>
			<?php if ($_isEditMode): ?>
			<script>
				// The Edit Post screen preloads existing content into TinyMCE (unlike the
				// wizard's insertContent(), which triggers on demand). TinyMCE places the
				// caret at the end of that pre-loaded content on init and the browser
				// scrolls to follow it, so the screen lands scrolled past the title/name
				// fields. Reuse the same reset already used for insertContent() in
				// custom-plugin-script.js (retries for ~1.2s to win over TinyMCE's own
				// late init/reflow).
				document.addEventListener('DOMContentLoaded', function () {
					if (typeof iseoScrollEditorToTop === 'function') {
						iseoScrollEditorToTop();
					} else {
						window.scrollTo(0, 0);
					}
				});
			</script>
			<?php endif; ?>
			<div id="post_form_buttons" class="PostForm__buttons">
				<button name="create" type="submit" formtarget="_self"
					class="btn styling_post_page_action_buttons btn-outline-primary">
					<?php echo $_isEditMode ? 'Publish Post' : 'Create &amp; Publish Post'; ?>
				</button>
				<?php if ( ! $_isPublished ) : ?>
				<button name="draft" type="submit" formtarget="_self"
					class="btn styling_post_page_action_buttons btn-outline-primary">
					<?php echo $_isEditMode ? 'Save Changes' : 'Save As Draft'; ?>
				</button>
				<?php endif; ?>
				<?php if ( $_isEditMode ) : ?>
				<a href="<?php echo esc_url( admin_url('admin.php?page=improveseo_projects') ); ?>"
					class="btn styling_post_page_action_buttons btn-outline-primary">Cancel</a>
				<?php endif; ?>
				<button id="preview_on" type="button" class="btn styling_post_page_action_buttons btn-outline-primary">Preview Post</button>
				<?php wp_nonce_field('improveseo_instant_preview', 'iseo_preview_nonce', false); ?>
			</div>

			<?php echo $site_link; ?>


			<!-- Post preview: renders the current title + content server-side (the
			     same card styling as the bulk "View AI Content" page) instead of
			     building a temporary post, so it opens instantly. Styles live in
			     assets/css/made_by_me.css, scoped under #preview_content_area. -->
			<div id="preview_popup" class="modal" style="text-align:center; width:90%; max-width:1100px;">
				<div id="wh_prev_modal_1">
					<!-- Loading and error are separate blocks so a failed preview no longer
					     overwrites the spinner markup, which then never came back. -->
					<div id="iseo_preview_loading" class="iseo-preview-loading">
						<div class="iseo-preview-spinner" role="status" aria-label="Generating preview"></div>
						<b class="iseo-preview-loading-title">Generating preview</b>
						<span class="iseo-preview-loading-note">Rendering the latest changes to this post.</span>
						<button type="button" id="iseo_preview_cancel"
							class="button iseo-preview-action">Cancel</button>
					</div>
					<div id="iseo_preview_error" class="iseo-preview-error" style="display:none;">
						<p id="iseo_preview_error_text"></p>
						<button type="button" class="button iseo-preview-action iseo-preview-action--primary"
							onclick="closeWin()">Close</button>
					</div>
				</div>
				<div id="wh_prev_modal_2" style="display:none;">
					<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
						<b style="font-size:18px">Post preview</b>
						<button type="button" id="open_win"
							class="button button-primary iseo-preview-action iseo-preview-action--primary"
							onclick="closeWin()">Close preview</button>
					</div>
					<div id="preview_content_area" class="iseo-aicontent-wrap"></div>
					<small style="color:#666; display:block; margin-top:8px;">This preview reflects your unsaved
						changes and is not published to your site.</small>
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

		

		<div id="postbox-container-2" class="postbox-container">
			<div id="normal-sortables" class="meta-box-sortables ui-sortable">
				<div class="PostForm__boxes">



				</div>
			</div>
		</div>
	</div>
</div>