<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


use ImproveSEO\View;

?>



<?php View::startSection('breadcrumbs') ?>

<!-- <span>Improve SEO</span> -->

<?php View::endSection('breadcrumbs') ?>



<?php View::startSection('content'); ?>

<h1 class="hidden"> Posting </h1>

<div class="global-wrap">
	<div class="head-bar">
		<img src="<?php echo esc_url( improveseo_logo_url() ); ?>" alt="ImproveSEO logo">
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
					<a class="Posting__page-button" href="<?php echo esc_url( admin_url("admin.php?page=improveseo_posting&action=create_post_bulk") ); ?>">
						<img src="<?php echo esc_url( WT_URL . '/assets/images/latest-images/Multi-device.png' ); ?>"
							alt="Multi-device">
					</a>
					<?php // The hint links to Keyword Lists, and a link cannot be nested inside the card's
					      // own <a> — so the title carries the link and the hint sits outside it. ?>
					<h3>
						<a class="Posting__page-button" href="<?php echo esc_url( admin_url("admin.php?page=improveseo_posting&action=create_post_bulk") ); ?>" aria-describedby="iseo-tip-bulk-post">Bulk Create AI Posts</a>
						<span class="create-ai-tip">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
							<span class="create-ai-tip-bubble is-wide is-interactive" id="iseo-tip-bulk-post" role="tooltip">Create a project with multiple posts based on a keyword list. Don&rsquo;t have a keyword list? <a href="<?php echo esc_url( admin_url( 'admin.php?page=improveseo_lists' ) ); ?>">Create one</a> before you get started.</span>
						</span>
					</h3>
				</div>
			</div>
		</div>

		
	</div>
</div>



<?php View::endSection('content') ?>



<?php View::make('layouts.main'); ?>