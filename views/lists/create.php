<?php
use ImproveSEO\View;
?>
<?php View::startSection('breadcrumbs') ?>
<a href="<?= admin_url('admin.php?page=improveseo_dashboard') ?>">Improve SEO</a>
&raquo;
<a href="<?= admin_url('admin.php?page=improveseo_lists') ?>">Improve SEO Lists</a>
&raquo;
<span>Create New List</span>
<?php View::endSection('breadcrumbs') ?>
<?php View::startSection('content') ?>
<h1 class="hidden">Create New List</h1>
<div class="listing improveseo_wrapper p-3 p-lg-4">
	<section class="project-section">
		<div class="project-heading d-flex flex-row align-items-center border-bottom pb-2">
			<img class="mr-2" src="<?php echo WT_URL.'/assets/images/project-list-logo.png'?>" alt="ImproveSeo">
			<h1>Create New List</h1>
		</div>
		<div class="Breadcrumbs custom-breadcrumbs border-top-0 border-left-0 border-right-0 border-bottom rounded-0 m-0 py-3 px-0 mb-3">
			<a href="<?= admin_url('admin.php?page=improveseo_dashboard') ?>">Improve SEO</a>
			»
			<a href="<?= admin_url('admin.php?page=improveseo_lists') ?>">Improve SEO Lists</a>
			»
			<span>Create New List</span>
		</div>
	</section>
	<section class="form-wrap">
	<form action="<?= admin_url('admin.php?page=improveseo_lists&action=do_create&noheader=true') ?>" method="post">
		<?php View::render('lists.form') ?>
		<div class="Posting__buttons shortcode-form-btn my-0 text-center">
			<button class="btn btn-outline-primary">Create</button>
		</div>
	</form>
</section>
</div>
<?php View::endSection('content') ?>
<?php View::make('layouts.main') ?>