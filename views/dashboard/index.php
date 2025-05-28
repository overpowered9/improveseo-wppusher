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
			<a href="<?= admin_url('admin.php?page=improveseo_posting') ?>">
				<div class="module-icon justify-between m-0">
					<img src="<?php echo WT_URL . '/assets/images/latest-images/icon2.svg' ?>" alt="icon2">
					<div class="module-toggle">
						<label class="toggle-switch">
							<input type="checkbox">
							<span class="switch"></span>
						</label>
					</div>
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
					<div class="module-toggle">
						<label class="toggle-switch">
							<input type="checkbox">
							<span class="switch"></span>
						</label>
					</div>
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
					<div class="module-toggle">
						<label class="toggle-switch">
							<input type="checkbox">
							<span class="switch"></span>
						</label>
					</div>
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
					<div class="module-toggle">
						<label class="toggle-switch">
							<input type="checkbox">
							<span class="switch"></span>
						</label>
					</div>
				</div>
				<div class="line"> </div>
				<h3>Support</h3>
				<p>Get assistance for all your ImproveSEO needs and queries.</p>
				</a>
			</div>
			<div class="module-box">
			<a href="<?= admin_url('admin.php?page=improveseo_posting') ?>">
				<div class="module-icon justify-between m-0">
					<img src="<?php echo WT_URL . '/assets/images/latest-images/icon5.svg' ?>" alt="icon5">
					<div class="module-toggle">
						<label class="toggle-switch">
							<input type="checkbox">
							<span class="switch"></span>
						</label>
					</div>
				</div>
				<div class="line"></div>
				<h3>Bulk AI Posts <span> Pro </span></h3>
				<p>Create projects. Option to duplicate project, update all published content, download content URLs to
					desktop, delete all posts/pages and project</p>
					</a>
			</div>
			<div class="module-box">
			<a href="<?= admin_url('admin.php?page=improveseo_lists') ?>">
				<div class="module-icon justify-between m-0">
					<img src="<?php echo WT_URL . '/assets/images/latest-images/icon6.svg' ?>" alt="icon6">
					<div class="module-toggle">
						<label class="toggle-switch">
							<input type="checkbox">
							<span class="switch"></span>
						</label>
					</div>
				</div>
				<div class="line"> </div>
				<h3>Keyword Lists <span> Pro </span></h3>
				<p>Add keywords that you would like to target and use to quickly create posts for all of them. This is
					handy for lists of keywords that you would like to bulk create posts for.</p>
					</a>
			</div>
			<div class="module-box">
			<a href="<?= admin_url('admin.php?page=improveseo_keyword_generator') ?>">
				<div class="module-icon justify-between m-0">
					<img src="<?php echo WT_URL . '/assets/images/latest-images/icon7.svg' ?>" alt="icon7">
					<div class="module-toggle">
						<label class="toggle-switch">
							<input type="checkbox">
							<span class="switch"></span>
						</label>
					</div>
				</div>
				<div class="line"> </div>
				<h3>Keyword Generator <span> Pro </span></h3>
				<p>Add seed keyword and uses the Google autosuggest feature to generate a list of keywords. Save
					projects and put these keywords into a your list projects.</p>
					</a>
			</div>
			<div class="module-box">
			<a  href="<?= admin_url('admin.php?page=improveseo_authors') ?>">
				<div class="module-icon justify-between m-0">
					<img src="<?php echo WT_URL . '/assets/images/latest-images/icon8.svg' ?>" alt="icon8">
					<div class="module-toggle">
						<label class="toggle-switch">
							<input type="checkbox">
							<span class="switch"></span>
						</label>
					</div>
				</div>
				<div class="line"></div>
				<h3>Author List <span> Pro </span></h3>
				<p>Create authors that are only for use within Improve SEO. The authors you make will be distributed
					among these multiple posts to make your site look more authoritative and natural.</p>
					</a>
			</div>
			<div class="module-box">
			<a  href="<?= admin_url('admin.php?page=improveseo_shortcodes') ?>">
				<div class="module-icon justify-between m-0">
					<img src="<?php echo WT_URL . '/assets/images/latest-images/icon9.svg' ?>" alt="icon9">
					<div class="module-toggle">
						<label class="toggle-switch">
							<input type="checkbox">
							<span class="switch"></span>
						</label>
					</div>
				</div>
				<div class="line"></div>
				<h3>SEO Visuals <span> Pro </span></h3>
				<p>Create shortcodes here manually. Once you've created a shortcode, simply use [name of your shortcode]
					to implement it within your post!</p>
					</a>
			</div>
			<div class="module-box">
			<a href="<?= admin_url('admin.php?page=improveseo_shortcodes') ?>">
				<div class="module-icon justify-between m-0">
					<img src="<?php echo WT_URL . '/assets/images/latest-images/icon10.svg' ?>" alt="icon10">
					<div class="module-toggle">
						<label class="toggle-switch">
							<input type="checkbox">
							<span class="switch"></span>
						</label>
					</div>
				</div>
				<div class="line"></div>
				<h3>Testimonials <span> Pro </span></h3>
				<p>Create one or multiple testimonial shortcodes to add to your posts for social proof of your products
					and services</p>
					</a>
			</div>
			<div class="module-box">
			<a href="<?= admin_url('admin.php?page=improveseo_shortcodes') ?>">
				<div class="module-icon justify-between m-0">
					<img src="<?php echo WT_URL . '/assets/images/latest-images/icon11.svg' ?>" alt="icon11">
					<div class="module-toggle">
						<label class="toggle-switch">
							<input type="checkbox">
							<span class="switch"></span>
						</label>
					</div>
				</div>
				<div class="line"></div>
				<h3>Buttons <span> Pro </span></h3>
				<p>Create one or multiple buy/book button shortcodes with hyperlinks to your calender or shopping cart
					to add to your posts.</p>
					</a>
			</div>
			<div class="module-box">
			<a href="<?= admin_url('admin.php?page=improveseo_shortcodes') ?>">
				<div class="module-icon justify-between m-0">
					<img src="<?php echo WT_URL . '/assets/images/latest-images/icon12.svg' ?>" alt="icon12">
					<div class="module-toggle">
						<label class="toggle-switch">
							<input type="checkbox">
							<span class="switch"></span>
						</label>
					</div>
				</div>
				<div class="line"></div>
				<h3>Maps <span> Pro </span></h3>
				<p>Create map shortcodes to add to your posts adding your Google api [great for local seo ]</p>
				</a>
			</div>
			<div class="module-box">
			<a href="#">
				<div class="module-icon justify-between m-0">
					<img src="<?php echo WT_URL . '/assets/images/latest-images/icon13.svg' ?>" alt="icon13">
					<div class="module-toggle">
						<label class="toggle-switch">
							<input type="checkbox">
							<span class="switch"></span>
						</label>
					</div>
				</div>
				<div class="line"></div>
				<h3>Schema <span> Pro </span></h3>
				<p>Easily generate and manage structured data to improve your website's SEO and search engine
					visibility.</p>
					</a>
			</div>
		</div>
	</div>
</div>

<?php View::endSection('content') ?>

<?php View::make('layouts.main') ?>