<?php



use ImproveSEO\View;



?>

<!-- <?php View::startSection('breadcrumbs') ?>

<a href="<?= admin_url('admin.php?page=improveseo_dashboard') ?>">Improve SEO</a>

&raquo;

<span>Create Post</span>

<?php View::endSection('breadcrumbs') ?> -->





<?php improveseo\View::render('notices.notice'); ?>



<?php View::startSection('content') ?>

<h1 class="hidden">Create Post</h1>

<div class="CreatePost improveseo_wrapper">

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

	generateAIpopup();

	/*******************/

	?>

	<form id="main_form"
		action="<?php echo admin_url('admin.php?page=improveseo_dashboard&action=do_create_post&noheader=true'); ?>"
		class="form-wrap" method="post">

		<?php

		$post_type = 'post';

		$form_id_preview = 'create_post';

		improveseo\View::render('posting.form', compact('post_type'));

		?>

	</form>



</div>

<?php View::endSection('content') ?>



<?php echo View::make('layouts.main') ?>