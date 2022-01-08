<?php

use ImproveSEO\View;

?>

<?php View::startSection('breadcrumbs') ?>
	<a href="<?= admin_url('admin.php?page=improveseo_dashboard') ?>">Improve SEO</a>
	&raquo;
	<span>Create Page</span>
<?php View::endSection('breadcrumbs') ?>

<?php improveseo\View::render('notices.notice'); ?>

<?php View::startSection('content') ?>
<h1 class="hidden">Create</h1>
<div class="CreatePost improveseo_wrapper">
	<section class="project-section border-bottom d-flex flex-row  justify-content-between align-items-center pb-2">
		<div class="project-heading d-flex flex-row">
			<img class="mr-2" src="<?php echo WT_URL.'/assets/images/project-list-logo.png'?>" alt="ImproveSeo">
			<h1>Create Page</h1>
		</div>
	</section>
	<form id="main_form" action="<?php echo admin_url('admin.php?page=improveseo_dashboard&action=do_create_post&noheader=true'); ?>" class="form-wrap" method="post">
		<?php 
			$post_type = 'page';

			improveseo\View::render('posting.form', compact('post_type'));
		?>
	</form>
</div>
<?php View::endSection('content') ?>

<?php echo View::make('layouts.main') ?>