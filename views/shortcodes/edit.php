<?php

use ImproveSEO\View;
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<?php View::startSection('breadcrumbs') ?>
<a href="<?php echo esc_url(admin_url('admin.php?page=improveseo_dashboard')); ?>"><?php esc_html_e('Improve SEO', 'improve-seo'); ?></a>
&raquo;
<a href="<?php echo esc_url(admin_url('admin.php?page=improveseo_shortcodes')); ?>"><?php esc_html_e('Shortcodes List', 'improve-seo'); ?></a>
&raquo;
<span><?php esc_html_e('Edit Shortcode', 'improve-seo'); ?></span>
<?php View::endSection('breadcrumbs') ?>
<?php View::startSection('content') ?>
<h1 class="hidden"><?php esc_html_e('Edit Shortcode', 'improve-seo'); ?></h1>
<div class="shortcode improveseo_wrapper p-3 p-lg-4">
	<section class="project-section">
		<div class="project-heading d-flex flex-row align-items-center border-bottom pb-2">
			<img class="mr-2" src="<?php echo esc_url(IMPROVESEO_WT_URL . '/assets/images/project-list-logo.png'); ?>" alt="<?php esc_attr_e('ImproveSeo', 'improve-seo'); ?>">
			<h1><?php esc_html_e('Edit Shortcode', 'improve-seo'); ?></h1>
		</div>
		<div class="Breadcrumbs custom-breadcrumbs border-top-0 border-left-0 border-right-0 border-bottom rounded-0 m-0 py-3 px-0 mb-3">
			<a href="<?php echo esc_url(admin_url('admin.php?page=improveseo_dashboard')); ?>"><?php esc_html_e('Improve SEO', 'improve-seo'); ?></a>
			&raquo;
			<a href="<?php echo esc_url(admin_url('admin.php?page=improveseo_shortcodes')); ?>"><?php esc_html_e('Shortcodes List', 'improve-seo'); ?></a>
			&raquo;
			<span><?php esc_html_e('Edit Shortcode', 'improve-seo'); ?></span>
		</div>
	</section>
	<section class="form-wrap">
		<form action="<?php echo esc_url(admin_url('admin.php?page=improveseo_shortcodes&action=do_edit&id=' . esc_attr($shortcode->id) . '&noheader=true')); ?>" method="post">
			<?php View::render('shortcodes.form', compact('shortcode')); ?>
			<div class="Posting__buttons my-0 shortcode-form-btn text-center">
				<button class="btn btn-outline-primary"><?php esc_html_e('Save', 'improve-seo'); ?></button>
			</div>
		</form>
	</section>
</div>
<?php View::endSection('content') ?>
<?php View::make('layouts.main') ?>