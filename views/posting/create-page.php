<?php
use ImproveSEO\View;
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<?php View::startSection('breadcrumbs') ?>
	<a href="<?php echo esc_url(admin_url('admin.php?page=improveseo_dashboard')) ?>"><?php esc_html_e('Improve SEO', 'improve-seo'); ?></a>
	&raquo;
	<span><?php esc_html_e('Create Page', 'improve-seo'); ?></span>
<?php View::endSection('breadcrumbs') ?>

<?php improveseo\View::render('notices.notice'); ?>

<?php View::startSection('content') ?>
<h1 class="hidden"><?php esc_html_e('Create', 'improve-seo'); ?></h1>
<div class="CreatePost improveseo_wrapper">
	<section class="project-section border-bottom d-flex flex-row justify-content-between align-items-center pb-2">
		<div class="project-heading d-flex flex-row">
			<img class="mr-2" src="<?php echo esc_url(WT_URL.'/assets/images/project-list-logo.png'); ?>" alt="<?php esc_attr_e('ImproveSeo', 'improve-seo'); ?>">
			<h1><?php esc_html_e('Create Page', 'improve-seo'); ?></h1>
		</div>
	</section>
	<form id="main_form" action="<?php echo esc_url(admin_url('admin.php?page=improveseo_dashboard&action=do_create_post&noheader=true')); ?>" class="form-wrap" method="post">
		<?php 
			$post_type = 'page';

			improveseo\View::render('posting.form', compact('post_type'));
		?>
		<?php wp_nonce_field('improveseo_do_create_post_nonce', 'improveseo_do_create_post_nonce'); ?>
	</form>
</div>
<?php View::endSection('content') ?>

<?php echo esc_html( View::make('layouts.main') ); ?>

