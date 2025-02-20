<?php

use ImproveSEO\View;


if ( ! defined( 'ABSPATH' ) ) exit;

?>
<?php View::startSection('breadcrumbs') ?>
<a href="<?php echo esc_url(admin_url('admin.php?page=improveseo_dashboard')); ?>"><?php esc_html_e('Improve SEO', 'improve-seo'); ?></a>
&raquo;
<span><?php esc_html_e('Improve SEO Lists', 'improve-seo'); ?></span>
<?php View::endSection('breadcrumbs') ?>


<?php View::startSection('content') ?>


<h2 class="hidden"><?php esc_html_e('Improve SEO Lists', 'improve-seo'); ?></h2>
<div class="notice notice-success notice-improveseo">
	<p>
		<?php esc_html_e('Improve SEO lists allow you to input a list of keywords or niches that you would like to target and use Improve SEO to quickly create posts for all of them.', 'improve-seo'); ?>
	</p>
	<p>
		<?php esc_html_e('This is handy if you have a list of hundreds of keywords that you would like to bulk create posts for (for example, lists of Amazon products or a list of long-tail keywords).', 'improve-seo'); ?>
	</p>
	<p>
		<?php esc_html_e('Improve SEO will create one post/page for every item in the list. In order to activate Improve SEO lists, be sure to use the shortcode in the title (you can use it elsewhere too, but make sure it\'s in the title).', 'improve-seo'); ?>
	</p>
	<p>
		<?php esc_html_e('You can embed your list by using', 'improve-seo'); ?> <strong>@list:listname</strong> (<?php esc_html_e('this will also be shown on the create a project page and when you create your list, so no need to memorize it.', 'improve-seo'); ?>)
	</p>
</div>



<?php View::render('import/import') ?>

<div class="listing improveseo_wrapper p-3 p-lg-4">
	<section class="project-section border-bottom d-flex flex-row  justify-content-between align-items-center pb-2 mb-3">
		<div class="project-heading d-flex flex-row">
			<img class="mr-2" src="<?php echo esc_url(WT_URL . '/assets/images/project-list-logo.png'); ?>" alt="<?php esc_attr_e('ImproveSeo', 'improve-seo'); ?>">
			<h1><?php esc_html_e('Improve SEO Lists', 'improve-seo'); ?></h1>
		</div>

		<div class="action-buttons">
			<a onclick="return confirm('<?php esc_attr_e('Please purchase the Pro version to access this feature and many more..', 'improve-seo'); ?>');" href="javascript:void()" class="btn btn-outline-primary btn-small" id="exportProject"><?php esc_html_e('Export All List', 'improve-seo'); ?></a>
			<a href="javascript:void(0)" onclick="return confirm('<?php esc_attr_e('Please purchase the Pro version to access this feature and many more..', 'improve-seo'); ?>');" class="btn btn-outline-primary btn-small" id=""><?php esc_html_e('Import', 'improve-seo'); ?></a>
			<a href="<?php echo esc_url(admin_url('admin.php?page=improveseo_lists&action=create')); ?>" class="btn btn-outline-primary btn-small py-2 px-3"><?php esc_html_e('Create New', 'improve-seo'); ?></a>
		</div>

	</section>

	<section class="pagination-wrapper text-right py-3">
		<span class="pagination-links">
			<?php echo wp_kses_post(paginate_links(array(
				'total' => $pages,
				'current' => $page,
				'format' => '&paged=%#%',
				'base' => esc_url(admin_url('admin.php?page=improveseo_lists%_%'))
			))) ?>
		</span>
	</section>
	<section class="project-table-wrapper">
		<form method="get">
			<div class="table-responsive-sm">
				<div class="tablenav top">
					<div class="alignright actions">
						<label for="bulk-action-selector-top" class="screen-reader-text"><?php esc_html_e('Search Lists', 'improve-seo'); ?></label>
						<input type="search" id="post-search-input" name="s" value="<?php echo esc_attr($s); ?>">
						<input type="hidden" name="page" value="improveseo_lists" />
						<input type="hidden" name="action" value="index" />
						<input type="submit" id="doaction" class="btn btn-outline-primary button action" value="<?php esc_html_e('Search Lists', 'improve-seo'); ?>">
					</div>
					<br class="clear">
				</div>
				<table class="table widefat fixed wp-list-table widefat fixed table-view-list posts">
					<thead>
						<tr>
							<th scope="col" class="manage-column manage-column column-title column-primary"><?php esc_html_e('Name', 'improve-seo'); ?></th>
							<th scope="col" class="manage-column"><?php esc_html_e('Content', 'improve-seo'); ?></th>
							<th style="width: 20%;" scope="col" class="manage-column"></th>
						</tr>
					</thead>
					<tbody>
						<?php if (!empty($lists)) : ?>
							<?php foreach ($lists as $item) : ?>
								<tr>
									<td class="column-title column-primary has-row-actions">
										<strong>
											<a class="row-title" href="<?php echo esc_url(admin_url('admin.php?page=improveseo_lists&action=edit&id=' . $item->id)); ?>"><?php echo esc_html($item->name); ?></a>
										</strong>
										<button type="button" class="toggle-row"><span class="screen-reader-text"><?php esc_html_e('Show more details', 'improve-seo'); ?></span></button>
									</td>
									<td data-colname="Content">
										<?php
										if (str_word_count($item->list) > 50) :
											echo "<span class='list-content-overflow'>" . esc_html($item->list) . "</span>";
										else :
											echo esc_html($item->list);
										endif;
										?>
									</td>
									<td class="text-lg-right pr-2 actions-btn" data-colname="Action">
										<div class="row-actions">
											<span class="edit">
												<a href="<?php echo esc_url(admin_url('admin.php?page=improveseo_lists&action=edit&id=' . $item->id)); ?>" title="<?php esc_attr_e('Edit this item', 'improve-seo'); ?>" class="btn btn-outline-primary"><?php esc_html_e('Edit', 'improve-seo'); ?></a>
											</span>
											<span class="trash">
												<a class="submitdelete btn btn-outline-danger" href="<?php echo esc_url(admin_url('admin.php?page=improveseo_lists&action=delete&id=' . $item->id . '&noheader=true')); ?>" onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete the list?', 'improve-seo'); ?>')"><?php esc_html_e('Trash', 'improve-seo'); ?></a>
											</span>
										</div>
									</td>
								</tr>
							<?php endforeach; ?>

						<?php else : ?>
							<tr>
								<td colspan="3"><?php esc_html_e('No Lists Available.', 'improve-seo'); ?></td>
							</tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>

		</form>
	</section>

	<section class="pagination-wrapper text-right py-3">
		<span class="pagination-links">
			<?php echo wp_kses_post(paginate_links(array(
				'total' => $pages,
				'current' => $page,
				'format' => '&paged=%#%',
				'base' => admin_url('admin.php?page=improveseo_lists%_%')
			))) ?>
		</span>
	</section>
</div>
<?php View::endSection('content') ?>
<?php View::make('layouts.main') ?>