<?php

use ImproveSEO\View;
use ImproveSEO\Validator;
?>

<?php View::startSection('breadcrumbs') ?>

<a href="<?= admin_url('admin.php?page=improveseo_dashboard') ?>">Improve SEO</a>

&raquo;

<a href="<?= admin_url('admin.php?page=improveseo_lists') ?>">Improve SEO Lists</a>

&raquo;

<span>Edit List</span>

<?php View::endSection('breadcrumbs') ?>

<?php View::startSection('content') ?>

<h1 class="hidden">Edit List</h1>

<div class="listing improveseo_wrapper p-3 p-lg-4">

	<div class="global-wrap">
		<div class="head-bar">
			<img src="<?php echo WT_URL . '/assets/images/latest-images/seo-latest-logo.svg' ?>"
				alt="project-list-logo">
			<h1> ImproveSEO | 2.0.11 </h1>
			<span>Pro</span>
		</div>
		<div class="box-top">
			<ul class="breadcrumb-seo">
				<li><a href="<?= admin_url('admin.php?page=improveseo_dashboard') ?>">Improve SEO</a></li>
				<li><a href="<?= admin_url('admin.php?page=improveseo_lists') ?>"> Keyword Lists </a></li>
				<li>Edit List</li>
			</ul>
		</div>
		<div class="improve-seo-form-box">
			<form class="improve-seo-form-global"
				action="<?= admin_url('admin.php?page=improveseo_lists&action=do_edit&id=' . $list->id . '&noheader=true') ?>"
				method="post">

				<div style="width:100%; margin-bottom:0px;"  class="BasicForm__row<?php if (Validator::hasError('name'))
					echo ' PostForm--error' ?>">
						<div class="seo-form-field" style="margin: 0px;">
							<label> Shortcode Name </label>
							<input style="width:100%;" type="text" name="name" placeholder="Ex. List 1"
								value="<?= Validator::old('name', $list->name) ?>">
					</div>
					<?php if (Validator::hasError('name')): ?>

						<span class="PostForm__error"><?= Validator::get('name') ?></span>

					<?php endif; ?>
				</div>
				<div  style="width:100%;" class="BasicForm__row<?php if (Validator::hasError('list'))
					echo ' PostForm--error' ?>">
						<div class="seo-form-field">
							<label> List of Keywords (one per line) </label>
							<textarea  style="width:100%;"  name="list"
								placeholder="Type Here..."><?= Validator::old('list', $list->list) ?></textarea>
						<?php if (Validator::hasError('list')): ?>

							<span class="PostForm__error"><?= Validator::get('list') ?></span>

						<?php endif; ?>
					</div>
				</div>
				<div class="seo-form-field">
					<input type="submit" class="styling_post_page_action_buttons2 styling_post_page_action_buttons" value="Save">
				</div>
			</form>
		</div>
	</div>

</div>

<?php View::endSection('content') ?>

<?php View::make('layouts.main') ?>