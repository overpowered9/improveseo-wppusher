<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use ImproveSEO\View;
?>

<?php View::startSection('breadcrumbs') ?>
	<a href="<?php echo esc_url( admin_url('admin.php?page=improveseo_dashboard') ); ?>">Improve SEO</a>
	&raquo;
	<span>Tags List</span>
<?php View::endSection('breadcrumbs') ?>

<?php View::startSection('content') ?>
	<h2>
		Tags List
	</h2>

	<p class="howto">
		You can remove noindex meta tag from tag page.
	</p>
	
	<p>
		<span class="displaying-num"><?php echo esc_html( $results->total ); ?> tags</span>
		|
		<span class="pagination-links">
			<?php echo  paginate_links(array(
				'total' => $pages,
				'current' => $page,
				'format' => '&paged=%#%',
				'base' => admin_url('admin.php?page=improveseo_noindex%_%')
			)) ?>
		</span>
	</p>
	<form method="get">
		<table class="wp-list-table widefat fixed striped">
		<thead>
		<tr>
			<th>Tag</th>
			<th>Slug</th>
		</tr>
		</thead>
		<tbody>
			<?php foreach ($tags as $tag): ?>
			<tr>
				<td class="column-title has-row-actions">
					<strong>
						<a class="row-title"><?php echo esc_html( $tag->name ); ?></a>
					</strong>
					<div class="row-actions">
						<span class="trash">
							<a href="<?php echo esc_url( admin_url('admin.php?page=improveseo_noindex&action=remove&id='. $tag->term_id .'&noheader=true') ); ?>" onclick="return confirm('Are you sure to delete noindex meta tag from <?php echo esc_html( $tag->name ); ?>?')">Delete noindex key</a>
						</span>
					</div>
				</td>
				<td>
					<?php echo esc_html( $tag->slug ); ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
		</table>
	</form>

	<p>
		<span class="displaying-num"><?php echo esc_html( $results->total ); ?> tags</span>
		|
		<span class="pagination-links">
			<?php echo  paginate_links(array(
				'total' => $pages,
				'current' => $page,
				'format' => '&paged=%#%',
				'base' => admin_url('admin.php?page=improveseo_noindex%_%')
			)) ?>
		</span>
	</p>
<?php View::endSection('content') ?>

<?php View::make('layouts.main') ?>