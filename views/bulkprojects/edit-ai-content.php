<?php

use ImproveSEO\View;

$project_name = isset($project_name) ? $project_name : '';

?>

<?php View::startSection('breadcrumbs') ?>

<a href="<?= admin_url('admin.php?page=improveseo_dashboard') ?>">Improve SEO</a>

&raquo;

<a href="<?= admin_url('admin.php?page=improveseo_bulkprojects') ?>">Bulk Projects List</a>

&raquo;

<?php if ($task->bulktask_id): ?>
<a href="<?= admin_url('admin.php?page=improveseo_bulkprojects&action=viewAllTasks&id=' . $task->bulktask_id) ?>">All Tasks</a>
&raquo;
<?php endif; ?>

<span>Edit Bulk Post Content</span>

<?php View::endSection('breadcrumbs') ?>


<?php View::startSection('content') ?>

<h1 class="hidden">Edit Bulk Post Content</h1>

<?php
// Same wrapper/classes as the SINGLE draft-edit screen (views/posting/edit-post.php
// + views/posting/form.php) so the two screens are visually identical: the rounded
// pill fields come from `.form-wrap .form-control` and the rounded outline buttons
// from `.styling_post_page_action_buttons` — no bespoke geometry here.
?>
<div class="CreatePost improveseo_wrapper">

	<section class="project-section border-bottom d-flex flex-row justify-content-between align-items-center pb-2">
		<div class="project-heading d-flex flex-row">
			<img class="mr-2" src="<?php echo improveseo_logo_url() ?>" alt="ImproveSeo">
			<h1>Edit Bulk Post Content</h1>
		</div>
	</section>

	<form id="main_form" class="form-wrap" method="post"
		action="<?= admin_url('admin.php?page=improveseo_bulkprojects&action=save_ai_content&id=' . $task->id . '&noheader=true') ?>">

		<?php wp_nonce_field('improveseo_save_ai_content_' . $task->id) ?>

		<div id="poststuff" class="PostForm">
			<div id="post-body">
				<div id="post-body-content">

					<?php // Which draft this is: project it belongs to, the keyword it was
					// generated for, and the title that becomes post_title on publish. ?>
					<div class="iseo-bulk-edit-meta">
						<div class="iseo-bulk-edit-meta__item">
							<span class="iseo-bulk-edit-meta__label">Project Name</span>
							<span class="iseo-bulk-edit-meta__value<?= $project_name === '' ? ' is-empty' : '' ?>">
								<?= $project_name !== '' ? esc_html($project_name) : 'N/A' ?>
							</span>
						</div>
						<div class="iseo-bulk-edit-meta__item">
							<span class="iseo-bulk-edit-meta__label">Keyword</span>
							<span class="iseo-bulk-edit-meta__value<?= trim((string) $task->keyword_name) === '' ? ' is-empty' : '' ?>">
								<?= trim((string) $task->keyword_name) !== '' ? esc_html($task->keyword_name) : 'N/A' ?>
							</span>
						</div>
						<div class="iseo-bulk-edit-meta__item">
							<span class="iseo-bulk-edit-meta__label">Title</span>
							<span class="iseo-bulk-edit-meta__value<?= trim((string) $task->ai_title) === '' ? ' is-empty' : '' ?>">
								<?= trim((string) $task->ai_title) !== '' ? esc_html($task->ai_title) : 'N/A' ?>
							</span>
						</div>
					</div>

					<p class="iseo-edit-ai-note">
						This post is still a draft. <strong>Save Changes</strong> keeps it a draft;
						<strong>Publish</strong> saves your edits and publishes it.
					</p>

					<div class="PostForm__title-wrap input-group">
						<label class="form-label" for="ai_title"
							style="display:block; font-weight:600; margin-bottom:5px;">Post Title</label>
						<input type="text" id="ai_title" name="ai_title" class="PostForm__title form-control"
							placeholder="Enter title here" value="<?= esc_attr($task->ai_title) ?>">
					</div>

					<div class="PostForm__body-wrap">
						<?php
						// ai_content is stored base64-encoded, so decode it for the editor and
						// re-encode on save (see the save_ai_content action).
						wp_editor(base64_decode($task->ai_content), 'ai_content', array(
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
							Publish
						</button>
						<button name="save" value="1" type="submit" formtarget="_self"
							class="btn styling_post_page_action_buttons btn-outline-primary">
							Save Changes
						</button>
						<a class="btn styling_post_page_action_buttons btn-outline-primary"
							href="<?= admin_url('admin.php?page=improveseo_bulkprojects&action=viewAllTasks&id=' . $task->bulktask_id) ?>">
							Cancel
						</a>
					</div>

				</div>
			</div>
		</div>

	</form>
</div>

<style>
	/* Only the project/keyword/title summary strip is new — every field and
	   button above reuses the single draft-edit screen's classes so the two
	   screens stay in sync automatically. */
	.CreatePost .iseo-bulk-edit-meta {
		display: flex;
		flex-wrap: wrap;
		gap: 12px 32px;
		margin: 20px 0 4px;
		padding: 16px 22px;
		background: #f6f8fa;
		border: 1px solid #e2e6ea;
		border-radius: 12px;
	}

	.CreatePost .iseo-bulk-edit-meta__item {
		display: flex;
		flex-direction: column;
		gap: 2px;
		min-width: 180px;
		max-width: 100%;
	}

	.CreatePost .iseo-bulk-edit-meta__label {
		font-size: 12px;
		font-weight: 600;
		letter-spacing: .04em;
		text-transform: uppercase;
		color: #6b747c;
	}

	.CreatePost .iseo-bulk-edit-meta__value {
		font-size: 15px;
		color: #1d2327;
		word-break: break-word;
	}

	.CreatePost .iseo-bulk-edit-meta__value.is-empty {
		color: #8c8f94;
		font-style: italic;
	}

	.iseo-edit-ai-note {
		margin: 16px 0 20px;
		color: #50575e;
		font-size: 14px;
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
