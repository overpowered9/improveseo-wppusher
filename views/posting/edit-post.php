<?php

use WorkHorse\View;

?>

<?php View::startSection('breadcrumbs') ?>
	<a href="<?= admin_url('admin.php?page=workhorse') ?>">Improve SEO</a>
	&raquo;
	<a href="<?= admin_url('admin.php?page=workhorse_projects') ?>">Improve SEO Projects</a>
	&raquo;
	<span>Edit Project</span>
<?php View::endSection('breadcrumbs') ?>

<?php View::startSection('content') ?>
<h1 class="hidden">Edit Post</h1>
<div class="CreatePost improveseo_wrapper">
	<section class="project-section border-bottom d-flex flex-row  justify-content-between align-items-center pb-2">
		<div class="project-heading d-flex flex-row">
			<img class="mr-2" src="<?php echo WT_URL.'/assets/images/project-list-logo.png'?>" alt="ImproveSeo">
			<h1>Edit Project</h1>
		</div>
	</section>

	<form id="main_form" class="form-wrap" action="/wp-admin/admin.php?page=workhorse&action=<?php echo isset($_GET['update']) ? 'do_update_post' : 'do_create_post'; ?>&id=<?= $task->id ?>&noheader=true" method="post">
		<?php 
			$post_type = $task->content['post_type'];

			WorkHorse\View::render('posting.form', compact('post_type', 'task'));
		?>
	</form>
</div>
<?php View::endSection('content') ?>

<?php echo View::make('layouts.main') ?>