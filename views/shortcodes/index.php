<?php

use ImproveSEO\View;
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<?php View::startSection('breadcrumbs') ?>
<a href="<?php echo esc_url(admin_url('admin.php?page=improveseo_dashboard')); ?>"><?php esc_html_e('Improve SEO', 'improve-seo'); ?></a>
&raquo;
<span><?php esc_html_e('Shortcodes List', 'improve-seo'); ?></span>
<?php View::endSection('breadcrumbs') ?>

<?php View::startSection('content') ?>
<div class="notice notice-success is-dismissible notice-improveseo">
	<p>
		<?php
		esc_html_e('Shortcodes are created for use within the YouTube video and image scraper, but you can create shortcodes here manually. Once you\'ve created a shortcode, simply use', 'improve-seo');
		?>
		<strong>[<?php echo esc_html__('name of your shortcode', 'improve-seo'); ?>]</strong>
		<?php
		esc_html_e('to implement it within your post!', 'improve-seo');
		?>
	</p>
</div>

<div class="shortcode improveseo_wrapper p-3 p-lg-4">
	<section class="project-section border-bottom d-flex flex-row  justify-content-between align-items-center pb-2">
		<div class="project-heading d-flex flex-row align-items-center">
			<img class="mr-2" src="<?php echo IMPROVESEO_WT_URL . '/assets/images/project-list-logo.png' ?>" alt="ImproveSeo">
			<h1>Shortcodes List</h1>
		</div>
		<a href="<?php echo admin_url('admin.php?page=improveseo_shortcodes&action=create') ?>" class="btn btn-outline-primary btn-small" id="btn-add">Add New</a>
	</section>
	<section class="tabs-wrap clearfix border-bottom mb-4">
		<ul class="subsubsub m-0">
			<li class="all">
				<a href="<?php echo admin_url('admin.php?page=improveseo_shortcodes&type=all') ?>" class="<?php if ($type == 'all') echo 'current' ?>">
					All
					<span class="count">(<?php echo $all ?>)</span>
				</a>
			</li>
			<li class="static">
				<a href="<?php echo admin_url('admin.php?page=improveseo_shortcodes&type=static') ?>" class="<?php if ($type == 'static') echo 'current' ?>">
					Static
					<span class="count">(<?php echo $static ?>)</span>
				</a>
			</li>
			<li class="dynamic">
				<a href="<?php echo admin_url('admin.php?page=improveseo_shortcodes&type=dynamic') ?>" class="<?php if ($type == 'dynamic') echo 'current' ?>">
					Dynamic
					<span class="count">(<?php echo $dynamic ?>)</span>
				</a>
			</li>
		</ul>
	</section>
	<section class="project-table-wrapper">
		<form method="get">
			<div class="table-responsive-sm">
				<table class="table fixed wp-list-table fixed table-view-list posts">
					<thead>
						<tr>
							<th scope="col" class="manage-column manage-column column-title column-primary"><?php esc_html_e('Shortcode', 'improve-seo'); ?></th>
							<th scope="col" class="manage-column"><?php esc_html_e('Type', 'improve-seo'); ?></th>
							<th scope="col" class="manage-column"><?php esc_html_e('Created At', 'improve-seo'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($shortcodes as $code) : ?>
							<tr>
								<td class="column-title column-primary has-row-actions">
									<strong class="d-none">
										<a class="row-title" href="<?php echo esc_url(admin_url('admin.php?page=improveseo_shortcodes&action=edit&id=' . $code->id)); ?>"><?php echo esc_html($code->shortcode); ?></a>
									</strong>
									<p>
										<?php echo esc_html($code->content); ?>
									</p>
									<div class="row-actions">
										<span class="edit">
											<a href="<?php echo esc_url(admin_url('admin.php?page=improveseo_shortcodes&action=edit&id=' . $code->id)); ?>" title="<?php esc_attr_e('Edit this item', 'improve-seo'); ?>"><?php esc_html_e('Edit', 'improve-seo'); ?></a>
											|
										</span>
										<span class="trash">
											<a class="submitdelete" href="<?php echo esc_url(admin_url('admin.php?page=improveseo_shortcodes&action=delete&id=' . $code->id . '&noheader=true')); ?>"><?php esc_html_e('Trash', 'improve-seo'); ?></a>
										</span>
									</div>
									<button type="button" class="toggle-row"><span class="screen-reader-text"><?php esc_html_e('Show more details', 'improve-seo'); ?></span></button>
								</td>
								<td data-colname="Type"><?php echo esc_html($code->type); ?></td>
								<td data-colname="Created At">
									<?php
									$date = new DateTime($code->created_at);
									echo esc_html($date->format('d/m/Y H:i:s'));
									?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</form>
	</section>

</div>
<?php View::endSection('content') ?>
<?php View::make('layouts.main') ?>