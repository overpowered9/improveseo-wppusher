<?php
use ImproveSEO\View;

?>

<?php View::startSection('breadcrumbs') ?>
	<a href="<?php echo admin_url('admin.php?page=improveseo_dashboard') ?>"><?php esc_html_e('Improve SEO', 'improve-seo'); ?></a>
	&raquo;
	<span><?php esc_html_e('Tags List', 'improve-seo'); ?></span>
<?php View::endSection('breadcrumbs') ?>

<?php View::startSection('content') ?>
	<h2>
		<?php esc_html_e('Tags List', 'improve-seo'); ?>
	</h2>

	<p class="howto">
		<?php esc_html_e('You can remove noindex meta tag from tag page.', 'improve-seo'); ?>
	</p>
	
	<p>
		<span class="displaying-num"><?php echo esc_html($results->total); ?> <?php esc_html_e('tags', 'improve-seo'); ?></span>
		|
		<span class="pagination-links">
			<?php echo paginate_links(array(
				'total' => $pages,
				'current' => $page,
				'format' => '&paged=%#%',
				'base' => admin_url('admin.php?page=improveseo_noindex%_%')
			)); ?>
		</span>
	</p>
	<form method="get">
		<table class="wp-list-table widefat fixed striped">
		<thead>
		<tr>
			<th><?php esc_html_e('Tag', 'improve-seo'); ?></th>
			<th><?php esc_html_e('Slug', 'improve-seo'); ?></th>
		</tr>
		</thead>
		<tbody>
			<?php foreach ($tags as $tag): ?>
			<tr>
				<td class="column-title has-row-actions">
					<strong>
						<a class="row-title"><?php echo esc_html($tag->name); ?></a>
					</strong>
					<div class="row-actions">
						<span class="trash">
							<a href="<?php echo wp_nonce_url(admin_url('admin.php?page=improveseo_noindex&action=remove&id='. $tag->term_id .'&noheader=true'), 'remove_noindex_nonce'); ?>" onclick="return confirm('<?php printf(esc_html__('Are you sure to delete noindex meta tag from %s?', 'improve-seo'), esc_html($tag->name)); ?>')"><?php esc_html_e('Delete noindex key', 'improve-seo'); ?></a>
						</span>
					</div>
				</td>
				<td>
					<?php echo esc_html($tag->slug); ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
		</table>
	</form>

	<p>
		<span class="displaying-num"><?php echo esc_html($results->total); ?> <?php esc_html_e('tags', 'improve-seo'); ?></span>
		|
		<span class="pagination-links">
			<?php echo paginate_links(array(
				'total' => $pages,
				'current' => $page,
				'format' => '&paged=%#%',
				'base' => admin_url('admin.php?page=improveseo_noindex%_%')
			)); ?>
		</span>
	</p>
<?php View::endSection('content') ?>

<?php View::make('layouts.main') ?>
