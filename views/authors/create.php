<?php

use ImproveSEO\View;
if ( ! defined( 'ABSPATH' ) ) exit;

?>

<?php View::startSection('breadcrumbs') ?>
<a href="<?php echo esc_url(admin_url('admin.php?page=improveseo_dashboard')); ?>"><?php esc_html_e('Improve SEO', 'improve-seo'); ?></a>

&raquo;
<span><?php esc_html_e('Authors List', 'improve-seo'); ?></span>
<?php View::endSection('breadcrumbs') ?>

<?php View::startSection('content') ?>
<h1 class="hidden"><?php esc_html_e('Authors List', 'improve-seo'); ?></h1>

<div class="notice notice-success notice-improveseo">
	<p><?php esc_html_e('Here you can create authors that are only for use within ImproveSEO. The posts and projects you make will be distributed among these authors to make your site look more authoritative (multiple authors) and natural.', 'improve-seo'); ?></p>
</div>

<div class="shortcode improveseo_wrapper intro_page  p-3 p-lg-4">
	<section class="project-section border-bottom d-flex flex-row  justify-content-between align-items-center pb-2">
		<div class="project-heading d-flex flex-row align-items-center">
			<img class="mr-2" src="<?php echo esc_url(IMPROVESEO_WT_URL . '/assets/images/project-list-logo.png'); ?>" alt="<?php esc_attr_e('ImproveSeo', 'improve-seo'); ?>">
			<h1><?php esc_html_e('Authors List', 'improve-seo'); ?></h1>

		</div>
		<a href="<?php echo esc_url(admin_url('admin.php?page=improveseo_authors&action=create')); ?>" id="btn-add" class="btn btn-outline-primary btn-small"><?php esc_html_e('Create Authors', 'improve-seo'); ?></a>

	</section>

	<section class="pagination-wrapper text-right d-flex flex-row align-items-center justify-content-between py-3">
		<span class="displaying-num"><?php echo esc_html($results['avail_roles']['improveseo_user']); ?> <?php esc_html_e('authors', 'improve-seo'); ?></span>

		<span class="pagination-links">
			<?php echo paginate_links(array(
				'total' => $pages,
				'current' => $page,
				'format' => '&paged=%#%',
				'base' => admin_url('admin.php?page=improveseo_authors%_%')
			)) ?>
		</span>
	</section>

	<section class="project-table-wrapper">
		<form method="get">
			<div class="table-responsive-sm">
				<table class="table fixed wp-list-table fixed table-view-list posts">
					<thead>
						<tr>
							<th class="manage-column column-title column-primary"><?php echo esc_html__('Name', 'improve-seo'); ?></th>

							<th class="manage-column"><?php echo esc_html__('E-Mail', 'improve-seo'); ?></th>

						</tr>
					</thead>
					<tbody>
						<?php foreach ($users as $user) : ?>
							<tr>
								<td class="column-title column-primary has-row-actions">
									<strong class="mb-0">
										<a class="row-title"><span class="name-icon">A</span><?php echo esc_html($user->display_name); ?></a>
									</strong>
									<div class="row-actions"></div>
									<button type="button" class="toggle-row"><span class="screen-reader-text"><?php echo esc_html__('Show more details', 'improve-seo'); ?></span></button>

								</td>
								<td data-colname="Email">
									<?php echo esc_html($user->user_email); ?>

								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</form>
	</section>

	<section class="pagination-wrapper text-right d-flex flex-row align-items-center justify-content-between py-3">
		<span class="displaying-num"><?php echo esc_html($results['avail_roles']['improveseo_user']); ?> <?php esc_html_e('authors', 'improve-seo'); ?></span>

		<span class="pagination-links">
			<?php echo paginate_links(array(
				'total' => $pages,
				'current' => $page,
				'format' => '&paged=%#%',
				'base' => admin_url('admin.php?page=improveseo_authors%_%')
			)) ?>
		</span>
	</section>
</div>
<?php View::endSection('content') ?>

<?php View::make('layouts.main') ?>