<?php

use ImproveSEO\View;

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

<span>Edit Post Content</span>

<?php View::endSection('breadcrumbs') ?>


<?php View::startSection('content') ?>

<h1 class="hidden">Edit Post Content</h1>

<div class="CreatePost improveseo_wrapper">

	<section class="project-section border-bottom d-flex flex-row justify-content-between align-items-center pb-2">
		<div class="project-heading d-flex flex-row">
			<img class="mr-2" src="<?php echo WT_URL . '/assets/images/project-list-logo.png' ?>" alt="ImproveSeo">
			<h1>Edit Post Content</h1>
		</div>
	</section>

	<p class="iseo-edit-ai-note">
		Editing the content of <strong><?= esc_html($task->keyword_name) ?></strong>. Saving updates the
		content only &mdash; it stays a draft until you publish it.
	</p>

	<form id="main_form" class="form-wrap" method="post"
		action="<?= admin_url('admin.php?page=improveseo_bulkprojects&action=save_ai_content&id=' . $task->id . '&noheader=true') ?>">

		<?php wp_nonce_field('improveseo_save_ai_content_' . $task->id) ?>

		<div class="PostForm__row">
			<label class="PostForm__label" for="ai_title">Title</label>
			<input type="text" id="ai_title" name="ai_title" class="PostForm__input widefat"
				value="<?= esc_attr($task->ai_title) ?>">
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
			<button name="save" type="submit" formtarget="_self"
				class="btn styling_post_page_action_buttons btn-outline-primary">
				Save Changes
			</button>
			<a class="btn styling_post_page_action_buttons btn-outline-primary"
				href="<?= admin_url('admin.php?page=improveseo_bulkprojects&action=viewAllTasks&id=' . $task->bulktask_id) ?>">
				Cancel
			</a>
		</div>

	</form>
</div>

<style>
	.iseo-edit-ai-note {
		margin: 16px 0 20px;
		color: #50575e;
		font-size: 14px;
	}

	.CreatePost .PostForm__row {
		margin-bottom: 18px;
	}

	.CreatePost .PostForm__label {
		display: block;
		margin-bottom: 6px;
		font-weight: 600;
		color: #1d2327;
	}

	.CreatePost .PostForm__input {
		width: 100%;
		max-width: 100%;
		padding: 8px 12px;
	}

	.CreatePost .PostForm__buttons {
		display: flex;
		gap: 12px;
		align-items: center;
		margin-top: 20px;
	}

	.CreatePost .PostForm__buttons a {
		display: inline-flex;
		align-items: center;
		text-decoration: none;
	}
</style>

<?php View::endSection('content') ?>
<?php View::make('layouts.main') ?>
