<?php
use ImproveSEO\View;
?>
<?php View::startSection('breadcrumbs') ?>
<a href="<?= admin_url('admin.php?page=improveseo_dashboard') ?>">Improve SEO</a>
&raquo;
<span>Dashboard</span>
<?php View::endSection('breadcrumbs') ?>
<?php View::startSection('content') ?>
<h1 class="hidden">Dashboard</h1>
<div class="modulePage improveseo_wrapper p-4">
	<section class="project-section ">
		<div class="project-heading border-bottom justify-content-between d-flex flex-row align-items-center pb-2">
			<div class="project-header-left d-flex">
				<img class="mr-2" src="<?php echo WT_URL.'/assets/images/project-list-logo.png'?>" alt="ImproveSeo">
				<h1>ImproveSEO | <?php echo IMPROVESEO_VERSION;?></h1>
				<div class="pro-tag">
					<span>Pro</span>
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
			<a href="<?= admin_url('admin.php?page=improveseo_dashboard') ?>">Improve SEO</a> /
			<span>Modules</span>
		</div>
	</section>
	<section class="tabs-wrap-content">
		<ul class="nav nav-tabs" role="tablist">
			<li class="nav-item">
				<a class="nav-link active" data-toggle="tab" href="#wt_modules" role="tab" aria-controls="Modules" aria-selected="true">Modules</a>
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
							<a href="<?= admin_url('admin.php?page=improveseo_posting') ?>">
								<div class="card-body text-center">
									<div class="icon-wrap">
										<img src="<?php echo WT_URL.'/assets/images/posting-icon.png'?>" alt="" class="icon">
									</div>
									<div class="title">
										<h4>Posting</h4>
									</div>
									<div class="text">
										<span>Create keyword rich Posts or Pages. Preview content. Add spintax for unique content versions. Schedule/drip feed content. Add Shortcodes and more!</span>
									</div>
								</div>
							</a>

							<div class="card-footer text-right">
							    <div class="custom-control custom-switch">
								  	<input type="checkbox" class="custom-control-input" id="customSwitch1">
								  	<label class="custom-control-label" for="customSwitch1"></label>
								</div>
							  </div>
						 </div>
					</div>
					<div class="col-12 col-xl-3 col-sm-6 col-lg-4">
						<div class="card">
							<a href="<?= admin_url('admin.php?page=improveseo_projects') ?>">
								<div class="card-body text-center">
									<div class="icon-wrap">
										<img src="<?php echo WT_URL.'/assets/images/posting-icon.png'?>" alt="" class="icon">
									</div>
									<div class="title">
										<h4>Projects</h4>
									</div>
									<div class="text">
										<span>Create projects. Option to duplicate project, update all published content, download content urls to desktop, delete all posts/pages and project</span>
									</div>
								</div>
							</a>
							<div class="card-footer text-right">
							    <div class="custom-control custom-switch">
								  	<input type="checkbox" class="custom-control-input" id="customSwitch2">
								  	<label class="custom-control-label" for="customSwitch2"></label>
								</div>
							  </div>
						 </div>
					</div>
					<div class="col-12 col-xl-3 col-sm-6 col-lg-4">
						<div class="card">
							<a href="<?= admin_url('admin.php?page=improveseo_shortcodes') ?>">
								<div class="card-body text-center">
									<div class="icon-wrap">
										<img src="<?php echo WT_URL.'/assets/images/posting-icon.png'?>" alt="" class="icon">
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
								  	<input type="checkbox" class="custom-control-input" id="customSwitch3">
								  	<label class="custom-control-label" for="customSwitch3"></label>
								</div>
							  </div>
						 </div>
					</div>
					<div class="col-12 col-xl-3 col-sm-6 col-lg-4">
						<div class="card">
							<a href="<?= admin_url('admin.php?page=improveseo_lists') ?>">
								<div class="card-body text-center">
									<div class="icon-wrap">
										<img src="<?php echo WT_URL.'/assets/images/posting-icon.png'?>" alt="" class="icon">
									</div>
									<div class="title">
										<h4>Lists</h4>
									</div>
									<div class="text">
										<span>Add keywords that you would like to target and use to quickly create posts for all of them. This is handy for lists of keywords that you would like to bulk create posts for.</span>
									</div>
								</div>
							</a>
							<div class="card-footer text-right">
							    <div class="custom-control custom-switch">
								  	<input type="checkbox" class="custom-control-input" id="customSwitch4">
								  	<label class="custom-control-label" for="customSwitch4"></label>
								</div>
							  </div>
						 </div>
					</div>
					<div class="col-12 col-xl-3 col-sm-6 col-lg-4">
						<div class="card">
							<div class="card-body text-center">
								<div class="icon-wrap">
		                        	<img src="<?php echo WT_URL.'/assets/images/posting-icon.png'?>" alt="" class="icon">
		                    	</div>
								<div class="title">
									<h4>Meta</h4>
								</div>
								<div class="text">
									<span>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</span>
								</div>
							</div>
							<div class="card-footer text-right">
							    <div class="custom-control custom-switch">
								  	<input type="checkbox" class="custom-control-input" id="customSwitch5">
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
										<img src="<?php echo WT_URL.'/assets/images/posting-icon.png'?>" alt="" class="icon">
									</div>
									<div class="title">
										<h4>FAQ</h4>
									</div>
									<div class="text">
										<span>User guide : Latest Updates- Improve SEO FAQ & Common Problems (and Workarounds)</span>
									</div>
								</div>
							</a>
							<div class="card-footer text-right">
							    <div class="custom-control custom-switch">
								  	<input type="checkbox" class="custom-control-input" id="customSwitch6">
								  	<label class="custom-control-label" for="customSwitch6"></label>
								</div>
							  </div>
						 </div>
					</div>
					<div class="col-12 col-xl-3 col-sm-6 col-lg-4">
						<div class="card pro-tile">
							<a href="<?= admin_url('admin.php?page=improveseo_authors') ?>">
								<div class="card-body text-center">
									<div class="icon-wrap">
										<img src="<?php echo WT_URL.'/assets/images/posting-icon.png'?>" alt="" class="icon">
									</div>
									<div class="title">
										<h4>Author List <span class="module-pro-tab">Pro</span></h4>
									</div>
									<div class="text">
										<span>Create authors that are only for use within Improve SEO. The authors you make will be distributed among these multiple posts to make your site look more authoritative and natural.</span>
									</div>
								</div>
							</a>
							<div class="card-footer text-right">
							    <div class="custom-control custom-switch">
								  	<input type="checkbox" class="custom-control-input" id="customSwitch7">
								  	<label class="custom-control-label" for="customSwitch7"></label>
								</div>
							  </div>
						 </div>
					</div>
					<div class="col-12 col-xl-3 col-sm-6 col-lg-4">
						<div class="card pro-tile">
							<a href="<?= admin_url('admin.php?page=improveseo_keyword_generator') ?>">
								<div class="card-body text-center">
									<div class="icon-wrap">
										<img src="<?php echo WT_URL.'/assets/images/posting-icon.png'?>" alt="" class="icon">
									</div>
									<div class="title">
										<h4>Keyword Generator <span class="module-pro-tab">Pro</span></h4>
									</div>
									<div class="text">
										<span>Add seed keyword and uses the Google autosuggest feature to generate a list of keywords. Save projects and put these keywords into a your list projects.</span>
									</div>
								</div>
							</a>
							<div class="card-footer text-right">
							    <div class="custom-control custom-switch">
								  	<input type="checkbox" class="custom-control-input" id="customSwitch8">
								  	<label class="custom-control-label" for="customSwitch8"></label>
								</div>
							  </div>
						 </div>
					</div>
					<div class="col-12 col-xl-3 col-sm-6 col-lg-4">
						<div class="card pro-tile">
							<a href="<?= admin_url('admin.php?page=improveseo_shortcodes') ?>">
								<div class="card-body text-center">
									<div class="icon-wrap">
										<img src="<?php echo WT_URL.'/assets/images/posting-icon.png'?>" alt="" class="icon">
									</div>
									<div class="title">
										<h4>Testimonials <span class="module-pro-tab">Pro</span></h4>
									</div>
									<div class="text">
										<span>Create one or multiple testimonial shortcodes to add to your posts for social proof of your products and services</span>
									</div>
								</div>
							</a>
							<div class="card-footer text-right">
							    <div class="custom-control custom-switch">
								  	<input type="checkbox" class="custom-control-input" id="customSwitch9">
								  	<label class="custom-control-label" for="customSwitch9"></label>
								</div>
							  </div>
						 </div>
					</div>
					<div class="col-12 col-xl-3 col-sm-6 col-lg-4">
						<div class="card pro-tile">
							<a href="<?= admin_url('admin.php?page=improveseo_shortcodes') ?>">
								<div class="card-body text-center">
									<div class="icon-wrap">
										<img src="<?php echo WT_URL.'/assets/images/posting-icon.png'?>" alt="" class="icon">
									</div>
									<div class="title">
										<h4>Buttons <span class="module-pro-tab">Pro</span></h4>
									</div>
									<div class="text">
										<span>Create one or multiple buy/book button shortcodes with hyperlinks to your calender or shopping cart to add to your posts.</span>
									</div>
								</div>
							</a>
							<div class="card-footer text-right">
							    <div class="custom-control custom-switch">
								  	<input type="checkbox" class="custom-control-input" id="customSwitch10">
								  	<label class="custom-control-label" for="customSwitch10"></label>
								</div>
							  </div>
						 </div>
					</div>
					<div class="col-12 col-xl-3 col-sm-6 col-lg-4">
						<div class="card pro-tile">
							<a href="<?= admin_url('admin.php?page=improveseo_shortcodes') ?>">
								<div class="card-body text-center">
									<div class="icon-wrap">
										<img src="<?php echo WT_URL.'/assets/images/posting-icon.png'?>" alt="" class="icon">
									</div>
									<div class="title">
										<h4>Maps <span class="module-pro-tab">Pro</span></h4>
									</div>
									<div class="text">
										<span>Create map shortcodes to add to your posts adding your Google api [great for local seo ]</span>
									</div>
								</div>
							</a>
							<div class="card-footer text-right">
							    <div class="custom-control custom-switch">
								  	<input type="checkbox" class="custom-control-input" id="customSwitch11">
								  	<label class="custom-control-label" for="customSwitch11"></label>
								</div>
							  </div>
						 </div>
					</div>
					<div class="col-12 col-xl-3 col-sm-6 col-lg-4">
						<div class="card pro-tile">
							<div class="card-body text-center">
								<div class="icon-wrap">
		                        	<img src="<?php echo WT_URL.'/assets/images/posting-icon.png'?>" alt="" class="icon">
		                    	</div>
								<div class="title">
									<h4>Schema <span class="module-pro-tab">Pro</span></h4>
								</div>
								<div class="text">
									<span>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</span>
								</div>
							</div>
							<div class="card-footer text-right">
							    <div class="custom-control custom-switch">
								  	<input type="checkbox" class="custom-control-input" id="customSwitch12">
								  	<label class="custom-control-label" for="customSwitch12"></label>
								</div>
							  </div>
						 </div>
					</div>
					<div class="col-12 col-xl-3 col-sm-6 col-lg-4">
						<div class="card pro-tile">
							<a href="<?= admin_url('admin.php?page=improveseo_posting') ?>">
								<div class="card-body text-center">
									<div class="icon-wrap">
			                        	<img src="<?php echo WT_URL.'/assets/images/posting-icon.png'?>" alt="" class="icon">
			                    	</div>
									<div class="title">
										<h4>Generate AI Content</h4>
									</div>
									<div class="text">
										<span>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</span>
									</div>
								</div>
								<div class="card-footer text-right">
								    <div class="custom-control custom-switch">
									  	<input type="checkbox" class="custom-control-input" id="customSwitch13">
									  	<label class="custom-control-label" for="customSwitch13"></label>
									</div>
								  </div>
							</a>
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