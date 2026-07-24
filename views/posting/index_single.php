<?php

use ImproveSEO\View;

// When arriving from the onboarding wizard, skip the single/bulk selection screen
// and go straight to the single post creation form, carrying the param forward.
if ( isset( $_GET['from'] ) && $_GET['from'] === 'onboarding' ) {
	wp_redirect( admin_url( 'admin.php?page=improveseo_posting&action=create_post_single&from=onboarding' ) );
	exit;
}

?>



<?php View::startSection('breadcrumbs') ?>

<!-- <span>Improve SEO</span> -->

<?php View::endSection('breadcrumbs') ?>



<?php View::startSection('content'); ?>

<h1 class="hidden"> Posting </h1>

<div class="global-wrap">
	<div class="head-bar">
		<img src="<?php echo improveseo_logo_url() ?>" alt="ImproveSEO logo">
		<h1 style="font-size: 36px; font-weight: 500;"> Welcome To Improve SEO! </h1>
	</div>
	<div class="container">
		<div class="breadcrumb text-center">
			<span class="active">Creation</span> &gt;
			<span>Category</span> &gt;
			<span>Content</span> &gt;
			<span>Publish</span>
		</div>
		

		<div class="create-ai-post">
			<div class="create-ai">
				
				<div class="create-ai-col">
					<a class="Posting__post-button" href="<?php echo admin_url("admin.php?page=improveseo_posting&action=create_post_single"); ?>">
						<img src="<?php echo WT_URL . '/assets/images/latest-images/Mobile-UX-rafiki.png'; ?>"
							alt="Create Single AI Post">
						<h3>Create Single AI Post</h3>
					</a>
				</div>
			</div>
		</div>
	</div>
</div>



<?php View::endSection('content') ?>



<?php echo View::make('layouts.main') ?>