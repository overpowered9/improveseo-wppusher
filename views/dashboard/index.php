<?php

use ImproveSEO\View;

?>

<?php View::startSection('breadcrumbs') ?>

<a href="<?= admin_url('admin.php?page=improveseo_dashboard') ?>">Improve SEO</a>

&raquo;

<span>Dashboard</span>

<?php View::endSection('breadcrumbs') ?>

<?php View::startSection('content') ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
	integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
	crossorigin="anonymous" referrerpolicy="no-referrer" />
<link
	href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
	rel="stylesheet">

<h1 class="hidden">Dashboard</h1>

<div class="global-wrap">
	<div class="improve-seo-container">
		<div class="head-bar">
			<img src="<?php echo WT_URL . '/assets/images/latest-images/seo-latest-logo.svg' ?>" alt="project-list-logo">
			<h1>ImproveSEO | <?php echo IMPROVESEO_VERSION; ?></h1>
			<span>Pro</span>
		</div>
		<div class="box-top">
			<ul class="breadcrumb-seo">
				<li><a href="<?= admin_url('admin.php?page=improveseo_dashboard') ?>">Improve SEO</a></li>
				<li>Modules</li>
			</ul>
		</div>
		<div class="modules-row text-left">
			<div class="module-box">
			<a href="<?= admin_url('admin.php?page=improveseo_create_single') ?>">
				<div class="module-icon justify-between m-0">
					<img src="<?php echo WT_URL . '/assets/images/latest-images/icon2.svg' ?>" alt="icon2">
				</div>
				<div class="line"></div>
				<h3>Single AI Post </h3>
				<p>Create keyword-rich posts or pages. Preview content, schedule, and more!</p>
			</a>
			</div>
			<div class="module-box">
			<a href="">
				<div class="module-icon justify-between m-0">
					<img src="<?php echo WT_URL . '/assets/images/latest-images/icon1.svg' ?>" alt="icon1">
				</div>
				<div class="line"></div>
				<h3>Meta</h3>
				<p>Create keyword-rich metadata for posts or pages. Customize with ease.</p>
				</a>
			</div>
			<div class="module-box">
			<a href="">
				<div class="module-icon justify-between m-0">
					<img src="<?php echo WT_URL . '/assets/images/latest-images/icon3.svg' ?>" alt="icon3">
				</div>
				<div class="line"> </div>
				<h3>Tutorials & FAQ</h3>
				<p>User guide : Latest Updates- Improve SEO FAQ & Common Problems (and Workarounds)</p>
				</a>
			</div>
			<div class="module-box">
			<a href="#">
				<div class="module-icon justify-between m-0">
					<img src="<?php echo WT_URL . '/assets/images/latest-images/icon4.svg' ?>" alt="icon4">
				</div>
				<div class="line"> </div>
				<h3>Support</h3>
				<p>Get assistance for all your ImproveSEO needs and queries.</p>
				</a>
			</div>
			<div class="module-box">
			<a href="<?= admin_url('admin.php?page=improveseo_create_bulk') ?>">
				<div class="module-icon justify-between m-0">
					<img src="<?php echo WT_URL . '/assets/images/latest-images/icon5.svg' ?>" alt="icon5">
				</div>
				<div class="line"></div>
				<h3>Bulk AI Posts </h3>
				<p>Create projects. Option to duplicate project, update all published content, download content URLs to
					desktop, delete all posts/pages and project</p>
					</a>
			</div>
			<div class="module-box">
			<a href="<?= admin_url('admin.php?page=improveseo_lists') ?>">
				<div class="module-icon justify-between m-0">
					<img src="<?php echo WT_URL . '/assets/images/latest-images/icon6.svg' ?>" alt="icon6">
				</div>
				<div class="line"> </div>
				<h3>Keyword Lists </h3>
				<p>Add keywords that you would like to target and use to quickly create posts for all of them. This is
					handy for lists of keywords that you would like to bulk create posts for.</p>
					</a>
			</div>
			<div class="module-box">
			<a href="<?= admin_url('admin.php?page=improveseo_keyword_generator') ?>">
				<div class="module-icon justify-between m-0">
					<img src="<?php echo WT_URL . '/assets/images/latest-images/icon7.svg' ?>" alt="icon7">
				</div>
				<div class="line"> </div>
				<h3>Keyword Generator </h3>
				<p>Add seed keyword and uses the Google autosuggest feature to generate a list of keywords. Save
					projects and put these keywords into a your list projects.</p>
					</a>
			</div>
			
			
			
		</div>
	</div>
</div>

<?php View::endSection('content') ?>

<?php View::make('layouts.main') ?>