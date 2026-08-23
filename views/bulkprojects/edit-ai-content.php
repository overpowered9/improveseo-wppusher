<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


use ImproveSEO\View;

$project_name = isset($project_name) ? $project_name : '';
$keyword_name = trim((string) $task->keyword_name);

?>

<?php View::startSection('breadcrumbs') ?>

<a href="<?php echo esc_url( admin_url('admin.php?page=improveseo_dashboard') ); ?>">Improve SEO</a>

&raquo;

<a href="<?php echo esc_url( admin_url('admin.php?page=improveseo_bulkprojects') ); ?>">Bulk Projects List</a>

&raquo;

<?php if ($task->bulktask_id): ?>
<a href="<?php echo esc_url( admin_url('admin.php?page=improveseo_bulkprojects&action=viewAllTasks&id=' . $task->bulktask_id) ); ?>">All Tasks</a>
&raquo;
<?php endif; ?>

<span>Edit Bulk Post Content</span>

<?php View::endSection('breadcrumbs') ?>


<?php View::startSection('content') ?>

<h1 class="hidden">Draft: Edit Bulk Post Content</h1>

<?php
// Same wrapper/classes as the SINGLE draft-edit screen (views/posting/edit-post.php
// + views/posting/form.php) so the two screens are visually identical: the rounded
// pill fields come from `.form-wrap .form-control` and the rounded outline buttons
// from `.styling_post_page_action_buttons` — no bespoke geometry here.
?>
<div class="CreatePost improveseo_wrapper">

	<section class="project-section border-bottom d-flex flex-row justify-content-between align-items-center pb-2">
		<div class="project-heading d-flex flex-row">
			<img class="mr-2" src="<?php echo esc_url( improveseo_logo_url() ); ?>" alt="ImproveSeo">
			<h1>Draft: Edit Bulk Post Content</h1>
		</div>
	</section>

	<?php // Which draft this is. The keyword lives here now — it is context, not an
	// editable field, and the project name it belongs to is the field below. ?>
	<ul class="breadcrumb-seo iseo-bulk-edit-crumbs">
		<li><a href="<?php echo esc_url( admin_url('admin.php?page=improveseo_dashboard') ); ?>">Improve SEO</a></li>
		<li><a href="<?php echo esc_url( admin_url('admin.php?page=improveseo_bulkprojects') ); ?>">Bulk Projects</a></li>
		<?php if ($task->bulktask_id): ?>
			<li><a href="<?php echo esc_url( admin_url('admin.php?page=improveseo_bulkprojects&action=viewAllTasks&id=' . $task->bulktask_id) ); ?>"><?php echo  $project_name !== '' ? esc_html($project_name) : 'Untitled project' ?></a></li>
		<?php endif; ?>
		<li><?php echo  $keyword_name !== '' ? esc_html($keyword_name) : 'No keyword' ?></li>
	</ul>

	<form id="main_form" class="form-wrap" method="post"
		action="<?php echo esc_url( admin_url('admin.php?page=improveseo_bulkprojects&action=save_ai_content&id=' . $task->id . '&noheader=true') ); ?>">

		<?php wp_nonce_field('improveseo_save_ai_content_' . $task->id) ?>
		<input type="hidden" name="max_posts" value="1">
		<input type="hidden" name="cats[]" value="">

		<div id="poststuff" class="PostForm">
			<div id="post-body">
				<div id="post-body-content">

					<?php // Editable, and identical markup to the single editor's Project Name
					// field (views/posting/form.php) — same wrapper, label and classes. It
					// renames the parent bulk project, which is where the name lives. ?>
					<div class="PostForm__name-wrap input-group" style="margin-top: 20px;">
						<label class="form-label" for="name"
							style="display:block; font-weight:600; margin-bottom:5px;">Project Name</label>
						<input type="text" id="name" name="name" class="PostForm__name form-control"
							placeholder="Project name here" value="<?php echo  esc_attr($project_name) ?>" required>
					</div>

					<div class="PostForm__title-wrap input-group">
						<label class="form-label" for="title"
							style="display:block; font-weight:600; margin-bottom:5px;">Post Title</label>
						<input type="text" id="title" name="ai_title" class="PostForm__title form-control"
							placeholder="Enter title here" value="<?php echo  esc_attr($task->ai_title) ?>">
					</div>

					<div class="PostForm__body-wrap">
						<?php
						// ai_content is stored base64-encoded, so decode it for the editor and
						// re-encode on save (see the save_ai_content action). The editor id is
						// `content` because that is the id form.js reads the preview body from;
						// textarea_name keeps the save posting ai_content as before.
						wp_editor(base64_decode($task->ai_content), 'content', array(
							'textarea_name' => 'ai_content',
							'editor_height' => 400,
							'drag_drop_upload' => true,
							'tinymce' => array(
								'resize' => false,
								'add_unload_trigger' => false,
							),
						));
						?>
					</div>

					<div id="post_form_buttons" class="PostForm__buttons">
						<?php // Publish submits this same form with publish=1: save_ai_content
						// stores the edits and then hands off to the existing bulk publish
						// action — the identical path the list's Publish menu item uses, so
						// state/post_id/published_on are written in exactly one place. ?>
						<button name="publish" value="1" type="submit" formtarget="_self"
							class="btn styling_post_page_action_buttons btn-outline-primary"
							onclick="return confirm('Publish this post now? Your edits will be saved first.')">
							Publish Post
						</button>
						<button name="save" value="1" type="submit" formtarget="_self"
							class="btn styling_post_page_action_buttons btn-outline-primary">
							Save Changes
						</button>
						<a class="btn styling_post_page_action_buttons btn-outline-primary"
							href="<?php echo esc_url( admin_url('admin.php?page=improveseo_bulkprojects&action=viewAllTasks&id=' . $task->bulktask_id) ); ?>">
							Cancel
						</a>
						<button id="preview_on" type="button"
							class="btn styling_post_page_action_buttons btn-outline-primary">Preview Post</button>
						<?php wp_nonce_field('improveseo_instant_preview', 'iseo_preview_nonce', false); ?>
					</div>

					<?php // Post preview: renders the current title + content server-side
					// (same card styling as the bulk "View AI Content" page) instead of
					// building a temporary post, so it opens instantly. Same markup and
					// styling as the single editor (views/posting/form.php) — styles live
					// in assets/css/made_by_me.css, scoped under #preview_content_area. ?>
					<div id="preview_popup" class="modal" style="text-align:center; width:90%; max-width:1100px;">
						<div id="wh_prev_modal_1">
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
							<small style="color:#666; display:block; margin-top:8px;">This preview reflects your
								unsaved changes and is not published to your site.</small>
						</div>
					</div>

				</div>
			</div>
		</div>

	</form>
</div>

<style>
	/* Geometry comes from the single editor's own classes; only the breadcrumb line
	   and the button row's layout are stated here. */
	.CreatePost .iseo-bulk-edit-crumbs {
		margin: 16px 0 4px;
		color: #50575e;
	}

	.CreatePost .PostForm__title-wrap {
		display: block;
		margin-bottom: 18px;
	}

	.CreatePost .PostForm__buttons {
		display: flex;
		justify-content: flex-end;
		gap: 20px;
		align-items: center;
		margin-top: 20px;
	}

	.CreatePost .PostForm__buttons a {
		display: inline-flex;
		align-items: center;
		text-decoration: none;
	}

	@media (max-width: 782px) {
		.CreatePost .PostForm__buttons {
			flex-wrap: wrap;
			justify-content: flex-start;
		}
	}
</style>

<?php View::endSection('content') ?>
<?php View::make('layouts.main') ?>
