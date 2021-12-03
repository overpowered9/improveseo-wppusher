<?php
use ImproveSEO\View;
use ImproveSEO\Validator;
?>
<?php View::startSection('breadcrumbs') ?>
<a href="<?= admin_url('admin.php?page=improveseo') ?>">Improve SEO</a>
&raquo;
<a href="<?= admin_url('admin.php?page=improveseo_authors') ?>">Authors List</a>
&raquo;
<span>Create Authors</span>
<?php View::endSection('breadcrumbs') ?>
<?php View::startSection('content') ?>
<h1 class="hidden">Create New Authors</h1>
<div class="CreatePost p-0">
	<div class="improveseo_wrapper intro_page  p-3 p-lg-4">
		<section class="project-section">
			<div class="project-heading border-bottom d-flex flex-row align-items-center pb-2">
				<img class="mr-2" src="<?php echo WT_URL.'/assets/images/project-list-logo.png'?>" alt="ImproveSeo">
				<h1>Create New Authors</h1>
			</div>
			<div class="Breadcrumbs custom-breadcrumbs border-top-0 border-left-0 border-right-0 border-bottom rounded-0 m-0 py-3 px-0 mb-3">
				<a href="<?php echo admin_url('admin.php?page=improveseo'); ?>">Improve SEO</a>
				»
				<a href="<?php echo admin_url('admin.php?page=improveseo_authors'); ?>">Authors List</a>
				»
				<span>Create Authors</span>
			</div>
		</section>
		<section class="form-wrap">
			<form action="<?php echo admin_url('admin.php?page=improveseo_authors&action=do_create&noheader=true'); ?>" method="post">
				<div class="BasicForm__row">
					<div class="input-group">
					<label class="form-label">Number of authors to create</label>
					<div class="input-prefix">
					<input type="number" class="form-control" placeholder=" 06" name="users" value="<?= Validator::old('users') ?>" required />
					<span>Ex.</span>
					</div>
				</div>
				</div>
				<p class="howto form-label">It will take some time to create all authors.</p>
				<p class="submit text-center pt-5">
					<input type="submit" class="btn btn-outline-primary px-5 py-3" value="<?php _e('Create Now') ?>" />
				</p>
			</form>
		</section>
	</div>
</div>
<?php View::endSection('content') ?>
<?php echo View::make('layouts.main') ?>