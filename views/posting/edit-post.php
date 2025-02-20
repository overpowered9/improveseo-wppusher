<?php

use ImproveSEO\View;
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<?php View::startSection('breadcrumbs') ?>
<a href="<?php echo esc_url(admin_url('admin.php?page=improveseo_dashboard')); ?>"><?php esc_html_e('Improve SEO', 'improve-seo'); ?></a>
&raquo;
<a href="<?php echo esc_url(admin_url('admin.php?page=improveseo_projects')); ?>"><?php esc_html_e('Improve SEO Projects', 'improve-seo'); ?></a>
&raquo;
<span><?php esc_html_e('Edit Project', 'improve-seo'); ?></span>
<?php View::endSection('breadcrumbs') ?>

<?php View::startSection('content') ?>
<h1 class="hidden"><?php esc_html_e('Edit Project', 'improve-seo'); ?></h1>
<div class="CreatePost improveseo_wrapper">
	<section class="project-section border-bottom d-flex flex-row justify-content-between align-items-center pb-2">
		<div class="project-heading d-flex flex-row">
			<img class="mr-2" src="<?php echo esc_url(WT_URL . '/assets/images/project-list-logo.png'); ?>" alt="<?php esc_attr_e('ImproveSeo', 'improve-seo'); ?>">
			<h1><?php esc_html_e('Edit Project', 'improve-seo'); ?></h1>
		</div>
	</section>
	<?php
	$form_action = isset($_GET['update']) ? 'do_update_post' : 'do_create_post';
	?>
	<form id="main_form" class="form-wrap" action="<?php echo esc_url(admin_url("admin.php?page=improveseo_dashboard&action={$form_action}&id={$task->id}&noheader=true")); ?>" method="post">
		<?php
		$post_type = $task->content['post_type'];
		improveseo\View::render('posting.form', compact('post_type', 'task'));
		?>
		<?php wp_nonce_field("improveseo_{$form_action}_nonce", "improveseo_{$form_action}_nonce"); ?>
	</form>
</div>
<?php View::endSection('content') ?>

<?php echo esc_html( View::make('layouts.main') ); ?>
