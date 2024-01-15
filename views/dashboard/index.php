<?php

use ImproveSEO\View;
?>
<?php View::startSection('breadcrumbs') ?>
<a href="<?php echo admin_url('admin.php?page=improveseo_dashboard') ?>">Improve SEO</a>
&raquo;
<span>Dashboard</span>
<?php View::endSection('breadcrumbs') ?>
<?php View::startSection('content') ?>
<h1 class="hidden">Dashboard</h1>
<div class="modulePage improveseo_wrapper p-4">
	<section class="project-section ">
		<div class="project-heading border-bottom justify-content-between d-flex flex-row align-items-center pb-2">
			<div class="project-header-left d-flex">
				<img class="mr-2" src="<?php echo esc_url(IMPROVESEO_WT_URL . '/assets/images/project-list-logo.png'); ?>" alt="<?php esc_attr_e('ImproveSeo', 'improve-seo'); ?>">
				<h1><?php printf(esc_html__('ImproveSEO | %s', 'improve-seo'), esc_html(IMPROVESEO_VERSION)); ?></h1>
				<div class="pro-tag">
					<span><?php esc_html_e('Lite', 'improve-seo'); ?></span>
				</div>
			</div>

			<div class="project-header-right">
				<!--<div class="mode-selector">
					<a href="#" class="active" data-mode="easy">Easy Mode</a>
					<a href="#" class="ml-2" data-mode="advanced">Advanced Mode</a>
				</div>-->
			</div>
		</div>
		<div class="Breadcrumbs custom-breadcrumbs border-0 m-0 pt-3 pb-0 px-0">
			<a href="<?php echo esc_url(admin_url('admin.php?page=improveseo_dashboard')); ?>"><?php esc_html_e('Improve SEO', 'improve-seo'); ?></a> /
			<span><?php esc_html_e('Modules', 'improve-seo'); ?></span>
		</div>

	</section>
	<section class="tabs-wrap-content">
		<ul class="nav nav-tabs" role="tablist">
			<li class="nav-item">
				<a class="nav-link active" data-toggle="tab" href="#wt_modules" role="tab" aria-controls="Modules" aria-selected="true">
					<?php esc_html_e('Modules', 'improve-seo'); ?>
				</a>
			</li>

			<!--<li class="nav-item">
				<a class="nav-link" data-toggle="tab" href="#wt_help" role="tab" aria-controls="Help" aria-selected="false">Help</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" data-toggle="tab" href="#setup_wizard" role="tab" aria-controls="Setup Wizard" aria-selected="false">Setup Wizard</a>
			</li>-->
		</ul>
		<div class="tab-content dashboard-tabpanel">
			<div class="tab-pane fade show active" id="wt_modules" role="tabpanel" aria-labelledby="wt_modules">
				<div class="row">
					<div class="col-12 col-xl-3 col-sm-6 col-lg-4">
						<div class="card">
							<a href="<?php echo esc_url(admin_url('admin.php?page=improveseo_posting')); ?>">
								<div class="card-body text-center">
									<div class="icon-wrap">
										<img src="<?php echo esc_url(IMPROVESEO_WT_URL . '/assets/images/posting-icon.png'); ?>" alt="" class="icon">
									</div>
									<div class="title">
										<h4><?php esc_html_e('Posting', 'improve-seo'); ?></h4>
									</div>
									<div class="text">
										<span><?php esc_html_e('Create keyword rich Posts or Pages. Preview content. Add spintax for unique content versions. Schedule/drip feed content. Add Shortcodes and more!', 'improve-seo'); ?></span>
									</div>
								</div>
							</a>

							<div class="card-footer text-right">
								<div class="custom-control custom-switch">
									<input type="checkbox" checked="checked" class="custom-control-input" id="customSwitch1">
									<label class="custom-control-label" for="customSwitch1"></label>
								</div>
							</div>
						</div>
					</div>

					<div class="col-12 col-xl-3 col-sm-6 col-lg-4">
						<div class="card">
							<a href="<?php echo esc_url(admin_url('admin.php?page=improveseo_projects')); ?>">
								<div class="card-body text-center">
									<div class="icon-wrap">
										<img src="<?php echo esc_url(IMPROVESEO_WT_URL . '/assets/images/posting-icon.png'); ?>" alt="" class="icon">
									</div>
									<div class="title">
										<h4><?php esc_html_e('Projects', 'improve-seo'); ?></h4>
									</div>
									<div class="text">
										<span><?php esc_html_e('Create projects. Option to duplicate project, update all published content, download content urls to desktop, delete all posts/pages and project', 'improve-seo'); ?></span>
									</div>
								</div>
							</a>
							<div class="card-footer text-right">
								<div class="custom-control custom-switch">
									<input type="checkbox" checked="checked" class="custom-control-input" id="customSwitch2">
									<label class="custom-control-label" for="customSwitch2"></label>
								</div>
							</div>
						</div>
					</div>

					<!-- <div class="col-12 col-xl-3 col-sm-6 col-lg-4">
						<div class="card">
							<a href="<?php echo admin_url('admin.php?page=improveseo_shortcodes') ?>">
								<div class="card-body text-center">
									<div class="icon-wrap">
										<img src="<?php echo IMPROVESEO_WT_URL . '/assets/images/posting-icon.png' ?>" alt="" class="icon">
									</div>
									<div class="title">
										<h4>Shortcodes</h4>
									</div>
									<div class="text">
										<span>Create shortcodes here manually. Once you've created a shortcode, simply use [name of your shortcode] to implement it within your post!</span>
									</div>
								</div>
							</a>
							<div class="card-footer text-right">
							    <div class="custom-control custom-switch">
								  	<input type="checkbox" checked="checked" class="custom-control-input" id="customSwitch3">
								  	<label class="custom-control-label" for="customSwitch3"></label>
								</div>
							  </div>
						 </div>
					</div> -->
					<div class="col-12 col-xl-3 col-sm-6 col-lg-4">
						<div class="card">
							<a href="<?php echo esc_url(admin_url('admin.php?page=improveseo_lists')); ?>">
								<div class="card-body text-center">
									<div class="icon-wrap">
										<img src="<?php echo esc_url(IMPROVESEO_WT_URL . '/assets/images/posting-icon.png'); ?>" alt="" class="icon">
									</div>
									<div class="title">
										<h4><?php esc_html_e('Lists', 'improve-seo'); ?></h4>
									</div>
									<div class="text">
										<span><?php esc_html_e('Add keywords that you would like to target and use to quickly create posts for all of them. This is handy for lists of keywords that you would like to bulk create posts for.', 'improve-seo'); ?></span>
									</div>
								</div>
							</a>
							<div class="card-footer text-right">
								<div class="custom-control custom-switch">
									<input type="checkbox" checked="checked" class="custom-control-input" id="customSwitch4">
									<label class="custom-control-label" for="customSwitch4"></label>
								</div>
							</div>
						</div>
					</div>

					<div class="col-12 col-xl-3 col-sm-6 col-lg-4">
						<div class="card">
							<div class="card-body text-center">
								<div class="icon-wrap">
									<img src="<?php echo esc_url(IMPROVESEO_WT_URL . '/assets/images/posting-icon.png'); ?>" alt="" class="icon">
								</div>
								<div class="title">
									<h4><?php esc_html_e('Meta', 'improve-seo'); ?></h4>
								</div>
								<div class="text">
									<span><?php esc_html_e('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'improve-seo'); ?></span>
								</div>
							</div>
							<div class="card-footer text-right">
								<div class="custom-control custom-switch">
									<input type="checkbox" checked="checked" class="custom-control-input" id="customSwitch5">
									<label class="custom-control-label" for="customSwitch5"></label>
								</div>
							</div>
						</div>
					</div>

					<div class="col-12 col-xl-3 col-sm-6 col-lg-4">
						<div class="card">
							<a href="http://bit.ly/improveseofaq" target="_blank">
								<div class="card-body text-center">
									<div class="icon-wrap">
										<img src="<?php echo esc_url(IMPROVESEO_WT_URL . '/assets/images/posting-icon.png'); ?>" alt="" class="icon">
									</div>
									<div class="title">
										<h4><?php esc_html_e('FAQ', 'improve-seo'); ?></h4>
									</div>
									<div class="text">
										<span><?php esc_html_e('User guide: Latest Updates - Improve SEO FAQ & Common Problems (and Workarounds)', 'improve-seo'); ?></span>
									</div>
								</div>
							</a>
							<div class="card-footer text-right">
								<div class="custom-control custom-switch">
									<input type="checkbox" checked="checked" class="custom-control-input" id="customSwitch6">
									<label class="custom-control-label" for="customSwitch6"></label>
								</div>
							</div>
						</div>
					</div>

					<div class="col-12 col-xl-3 col-sm-6 col-lg-4">
						<div class="card pro-tile">
							<a href="javascript:void(0)" onclick="return confirm('<?php esc_html_e('Please purchase the Pro version to access this feature and many more..', 'improve-seo'); ?>');">
								<div class="card-body text-center">
									<div class="icon-wrap">
										<img src="<?php echo esc_url(IMPROVESEO_WT_URL . '/assets/images/posting-icon.png'); ?>" alt="" class="icon">
									</div>
									<div class="title">
										<h4><?php esc_html_e('Author List', 'improve-seo'); ?> <span class="module-pro-tab"><?php esc_html_e('Pro', 'improve-seo'); ?></span></h4>
									</div>
									<div class="text">
										<span><?php esc_html_e('Create authors that are only for use within Improve SEO. The authors you make will be distributed among these multiple posts to make your site look more authoritative and natural.', 'improve-seo'); ?></span>
									</div>
								</div>
							</a>
							<div class="card-footer text-right">
								<div class="custom-control custom-switch">
									<input type="checkbox" disabled class="custom-control-input" id="customSwitch7">
									<label class="custom-control-label" for="customSwitch7"></label>
								</div>
							</div>
						</div>
					</div>
					<div class="col-12 col-xl-3 col-sm-6 col-lg-4">
						<div class="card pro-tile">
							<a href="javascript:void(0)" onclick="return confirm('<?php esc_html_e('Please purchase the Pro version to access this feature and many more..', 'improve-seo'); ?>');">
								<div class="card-body text-center">
									<div class="icon-wrap">
										<img src="<?php echo esc_url(IMPROVESEO_WT_URL . '/assets/images/posting-icon.png'); ?>" alt="" class="icon">
									</div>
									<div class="title">
										<h4><?php esc_html_e('Keyword Generator', 'improve-seo'); ?> <span class="module-pro-tab"><?php esc_html_e('Pro', 'improve-seo'); ?></span></h4>
									</div>
									<div class="text">
										<span><?php esc_html_e('Add seed keyword and use the Google autosuggest feature to generate a list of keywords. Save projects and put these keywords into your list projects.', 'improve-seo'); ?></span>
									</div>
								</div>
							</a>
							<div class="card-footer text-right">
								<div class="custom-control custom-switch">
									<input type="checkbox" disabled class="custom-control-input" id="customSwitch8">
									<label class="custom-control-label" for="customSwitch8"></label>
								</div>
							</div>
						</div>
					</div>

					<div class="col-12 col-xl-3 col-sm-6 col-lg-4">
						<div class="card pro-tile">
							<a href="javascript:void(0)" onclick="return confirm('<?php esc_html_e('Please purchase the Pro version to access this feature and many more..', 'improve-seo'); ?>');">
								<div class="card-body text-center">
									<div class="icon-wrap">
										<img src="<?php echo esc_url(IMPROVESEO_WT_URL . '/assets/images/posting-icon.png'); ?>" alt="" class="icon">
									</div>
									<div class="title">
										<h4><?php esc_html_e('Testimonials', 'improve-seo'); ?> <span class="module-pro-tab"><?php esc_html_e('Pro', 'improve-seo'); ?></span></h4>
									</div>
									<div class="text">
										<span><?php esc_html_e('Create one or multiple testimonial shortcodes to add to your posts for social proof of your products and services', 'improve-seo'); ?></span>
									</div>
								</div>
							</a>
							<div class="card-footer text-right">
								<div class="custom-control custom-switch">
									<input type="checkbox" disabled class="custom-control-input" id="customSwitch9">
									<label class="custom-control-label" for="customSwitch9"></label>
								</div>
							</div>
						</div>
					</div>

					<div class="col-12 col-xl-3 col-sm-6 col-lg-4">
						<div class="card pro-tile">
							<a href="javascript:void(0)" onclick="return confirm('<?php esc_html_e('Please purchase the Pro version to access this feature and many more..', 'improve-seo'); ?>');">
								<div class="card-body text-center">
									<div class="icon-wrap">
										<img src="<?php echo esc_url(IMPROVESEO_WT_URL . '/assets/images/posting-icon.png'); ?>" alt="" class="icon">
									</div>
									<div class="title">
										<h4><?php esc_html_e('Buttons', 'improve-seo'); ?> <span class="module-pro-tab"><?php esc_html_e('Pro', 'improve-seo'); ?></span></h4>
									</div>
									<div class="text">
										<span><?php esc_html_e('Create one or multiple buy/book button shortcodes with hyperlinks to your calendar or shopping cart to add to your posts.', 'improve-seo'); ?></span>
									</div>
								</div>
							</a>
							<div class="card-footer text-right">
								<div class="custom-control custom-switch">
									<input type="checkbox" disabled class="custom-control-input" id="customSwitch10">
									<label class="custom-control-label" for="customSwitch10"></label>
								</div>
							</div>
						</div>
					</div>

					<div class="col-12 col-xl-3 col-sm-6 col-lg-4">
						<div class="card pro-tile">
							<a href="javascript:void(0)" onclick="return confirm('<?php esc_html_e('Please purchase the Pro version to access this feature and many more..', 'improve-seo'); ?>');">
								<div class="card-body text-center">
									<div class="icon-wrap">
										<img src="<?php echo esc_url(IMPROVESEO_WT_URL . '/assets/images/posting-icon.png'); ?>" alt="" class="icon">
									</div>
									<div class="title">
										<h4><?php esc_html_e('Maps', 'improve-seo'); ?> <span class="module-pro-tab"><?php esc_html_e('Pro', 'improve-seo'); ?></span></h4>
									</div>
									<div class="text">
										<span><?php esc_html_e('Create map shortcodes to add to your posts adding your Google API [great for local SEO]', 'improve-seo'); ?></span>
									</div>
								</div>
							</a>
							<div class="card-footer text-right">
								<div class="custom-control custom-switch">
									<input type="checkbox" disabled class="custom-control-input" id="customSwitch11">
									<label class="custom-control-label" for="customSwitch11"></label>
								</div>
							</div>
						</div>
					</div>

					<div class="col-12 col-xl-3 col-sm-6 col-lg-4">
						<div class="card pro-tile">
							<div class="card-body text-center">
								<div class="icon-wrap">
									<img src="<?php echo esc_url(IMPROVESEO_WT_URL . '/assets/images/posting-icon.png'); ?>" alt="" class="icon">
								</div>
								<div class="title">
									<h4><?php esc_html_e('Schema', 'improve-seo'); ?> <span class="module-pro-tab"><?php esc_html_e('Pro', 'improve-seo'); ?></span></h4>
								</div>
								<div class="text">
									<span><?php esc_html_e('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'improve-seo'); ?></span>
								</div>
							</div>
							<div class="card-footer text-right">
								<div class="custom-control custom-switch">
									<input type="checkbox" disabled class="custom-control-input" id="customSwitch12">
									<label class="custom-control-label" for="customSwitch12"></label>
								</div>
							</div>
						</div>
					</div>

				</div>
			</div>
			<div class="tab-pane fade" id="wt_help" role="tabpanel" aria-labelledby="wt_help">
			</div>
			<div class="tab-pane fade" id="setup_wizard" role="tabpanel" aria-labelledby="setup_wizard">
			</div>
		</div>
	</section>
</div>
<?php View::endSection('content') ?>
<?php View::make('layouts.main') ?>