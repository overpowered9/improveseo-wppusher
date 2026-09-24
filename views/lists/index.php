<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


use ImproveSEO\View;

// Two screens in one template:
//   - no keyword list saved yet → only the "Create Keyword List" cards (they ARE the screen);
//   - otherwise → "My Keyword Lists", with those same cards one click away in a dialog.
// $all counts every saved list, not just this page or this search, so a search that matches
// nothing still shows the list screen (with a "no match" row), never the first-run screen.
$iseo_has_lists = ( (int) $all ) > 0;

$iseo_lists_url = admin_url( 'admin.php?page=improveseo_lists' );

/**
 * A stored timestamp as a date in the site's own format ("12 Sep 2026", "September 12, 2026", …).
 * Empty for a missing or zero date, so the caller can print its own placeholder.
 */
$iseo_kwl_date = function ( $mysql_date ) {
	if ( empty( $mysql_date ) || 0 === strpos( (string) $mysql_date, '0000-00-00' ) ) {
		return '';
	}
	$formatted = mysql2date( get_option( 'date_format' ), $mysql_date );
	return $formatted ? $formatted : '';
};

?>

<?php View::startSection('breadcrumbs') ?>

<a href="<?php echo esc_url( admin_url('admin.php?page=improveseo_dashboard') ); ?>">Improve SEO</a>

&raquo;

<span>Improve SEO Lists</span>

<?php View::endSection('breadcrumbs') ?>

<?php View::startSection('content') ?>

<h2 class="hidden">Improve SEO Lists</h2>

<?php View::render('import/import') ?>

<div class="global-wrap">
	<div class="head-bar">
		<img src="<?php echo esc_url( improveseo_logo_url() ); ?>" alt="ImproveSEO logo">
		<h1>ImproveSEO | <?php echo esc_html( IMPROVESEO_VERSION ); ?></h1>
	</div>
	<div class="box-top">
		<ul class="breadcrumb-seo">
			<li><a href="#">Improve SEO</a></li>
			<li>Keyword Lists</li>
		</ul>
	</div>

<?php if ( ! $iseo_has_lists ) : ?>

	<?php View::render( 'lists.create-choice' ); ?>

<?php else : ?>

	<div class="iseo-kwl-head">
		<h2 class="iseo-kwl-head-title">My Keyword Lists</h2>
		<?php // A real link to the manual create screen, so it still goes somewhere useful when the
		      // dialog cannot open (no JS, or a browser without <dialog>); the script at the bottom
		      // upgrades the click to the dialog. ?>
		<a id="iseo-kwl-new-list" class="iseo-kwl-new-btn" href="<?php echo esc_url( admin_url( 'admin.php?page=improveseo_lists&action=create' ) ); ?>" aria-haspopup="dialog">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true" focusable="false"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
			New List
		</a>
	</div>

	<div class="actions search-form-box">
		<form class="improve-seo-form-global" method="GET">
			<input type="text" id="post-search-input" name="s" value="<?php echo esc_attr( $s ); ?>"
				placeholder="Search lists or keywords" aria-label="Search keyword lists by name or keyword">
			<input type="hidden" name="page" value="improveseo_lists" />
			<input type="hidden" name="action" value="index" />
			<button type="submit" class="search-btn">
				<img src="<?php echo esc_url( WT_URL . '/assets/images/latest-images/clarity_search-line.svg' ); ?>"
					alt="Search">
			</button>
		</form>
		<div class="pagination">
			<?php if ($page > 1): ?>
				<button class="prev pagination-btn"
					onclick="window.location.href='<?php echo esc_url( admin_url('admin.php?page=improveseo_lists&paged=' . ($page - 1) . ($s ? '&s=' . urlencode($s) : '')) ); ?>'">
					&lt; Prev
				</button>
			<?php else: ?>
				<button class="prev pagination-btn" disabled style="opacity: 0.5; cursor: not-allowed;">
					&lt; Prev
				</button>
			<?php endif; ?>

			<?php for ($i = 1; $i <= $pages; $i++): ?>
				<?php if ($i == $page): ?>
					<button class="active"><?php echo esc_html( $i ); ?></button>
				<?php else: ?>
					<button
						onclick="window.location.href='<?php echo esc_url( admin_url('admin.php?page=improveseo_lists&paged=' . $i . ($s ? '&s=' . urlencode($s) : '')) ); ?>'"><?php echo esc_html( $i ); ?></button>
				<?php endif; ?>
			<?php endfor; ?>

			<?php if ($page < $pages): ?>
				<button class="next pagination-btn"
					onclick="window.location.href='<?php echo esc_url( admin_url('admin.php?page=improveseo_lists&paged=' . ($page + 1) . ($s ? '&s=' . urlencode($s) : '')) ); ?>'">
					Next &gt;
				</button>
			<?php else: ?>
				<button class="next pagination-btn" disabled style="opacity: 0.5; cursor: not-allowed;">
					Next &gt;
				</button>
			<?php endif; ?>
		</div>
		<div class="import-export">
			<p><?php echo esc_html( $total ); ?> Items</p>
		</div>
	</div>
	<div class="improve-seo-container">
		<div class="project-lists">
			<div class="table-responsive">
				<table class="table project_table_listing iseo-kwl-table">
					<thead>
						<tr>
							<th>Keyword List</th>
							<th>Keyword List Preview</th>
							<th>Usage</th>
							<th><span class="screen-reader-text">Actions</span></th>
						</tr>
					</thead>
					<tbody>
						<?php if (!empty($lists)): ?>
							<?php foreach ($lists as $item):
								$iseo_edit_url = admin_url( 'admin.php?page=improveseo_lists&action=edit&id=' . absint( $item->id ) );

								// Keywords are one per line; blank lines are not keywords. (The stored
								// `size` column counts them anyway, so it is not used for the count.)
								$iseo_keywords = array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) $item->list ) ), 'strlen' ) );
								$iseo_count    = count( $iseo_keywords );
								// A handful is plenty: the cell shows one line and CSS cuts it with an
								// ellipsis, so "2-4 keywords, whatever fits" falls out of the width.
								$iseo_preview  = implode( ', ', array_slice( $iseo_keywords, 0, 8 ) );

								$iseo_use      = isset( $usage[ (int) $item->id ] ) ? $usage[ (int) $item->id ] : null;
								$iseo_status   = $iseo_use ? $iseo_use->status : 'unused';
								$iseo_created  = $iseo_kwl_date( $item->created_at );
								$iseo_used_on  = $iseo_use ? $iseo_kwl_date( $iseo_use->last_used ) : '';
							?>
								<tr>
									<?php // data-label is not just markup: under 767px style.css prints it as the
									      // cell's visible label, so it has to match the column header. The class
									      // carries the column width that used to hang off data-label="Name". ?>
									<td data-label="Keyword List" class="iseo-kwl-col-name"
										onclick="window.location.href='<?php echo esc_url( $iseo_edit_url ); ?>'"
										style="cursor: pointer;">
										<div class="iseo-kwl-cell">
											<a class="iseo-kwl-name" href="<?php echo esc_url( $iseo_edit_url ); ?>"><?php echo esc_html( $item->name ); ?></a>
											<span class="iseo-kwl-meta">Created <?php echo esc_html( $iseo_created ? $iseo_created : '—' ); ?></span>
											<span class="iseo-kwl-meta">Last used <?php echo esc_html( $iseo_used_on ? $iseo_used_on : '—' ); ?></span>
										</div>
									</td>
									<td data-label="Keyword List Preview" class="iseo-kwl-col-preview">
										<div class="iseo-kwl-cell">
											<span class="iseo-kwl-preview" title="<?php echo esc_attr( $iseo_preview ); ?>"><?php echo esc_html( $iseo_preview ); ?></span>
											<span class="iseo-kwl-meta"><?php echo esc_html( sprintf( 1 === $iseo_count ? '%d keyword' : '%d keywords', $iseo_count ) ); ?></span>
										</div>
									</td>
									<td data-label="Usage" class="iseo-kwl-col-usage">
										<div class="iseo-kwl-cell">
											<span class="iseo-kwl-status iseo-kwl-status--<?php echo esc_attr( $iseo_status ); ?>"><?php echo esc_html( $iseo_use ? $iseo_use->label : 'Not used yet' ); ?></span>
											<?php if ( $iseo_use && $iseo_use->project_id ) : ?>
												<span class="iseo-kwl-meta">
													Project:
													<a class="iseo-kwl-project" href="<?php echo esc_url( admin_url( 'admin.php?page=improveseo_bulkprojects&action=viewAllTasks&id=' . $iseo_use->project_id ) ); ?>"><?php echo esc_html( '' !== trim( $iseo_use->project_name ) ? $iseo_use->project_name : 'Bulk Project #' . $iseo_use->project_id ); ?></a>
												</span>
												<?php if ( $iseo_use->more > 0 ) : ?>
													<a class="iseo-kwl-meta iseo-kwl-more" href="<?php echo esc_url( admin_url( 'admin.php?page=improveseo_bulkprojects' ) ); ?>"><?php echo esc_html( sprintf( 1 === $iseo_use->more ? '+%d more project' : '+%d more projects', $iseo_use->more ) ); ?></a>
												<?php endif; ?>
											<?php endif; ?>
										</div>
									</td>
									<td data-label="Action">
										<?php // Each icon names itself twice: aria-label for screen readers, and the
										      // hover/focus bubble for everyone else (aria-hidden, so it is not read
										      // out a second time; alt="" for the same reason). ?>
										<div class="iseo-kwl-actions">
											<a class="iseo-kwl-action"
												href="<?php echo esc_url( admin_url( 'admin.php?page=improveseo_posting&action=create_post_bulk&keyword_list=' . absint( $item->id ) ) ); ?>"
												aria-label="Create Bulk Project From Keyword List">
												<img src="<?php echo esc_url( WT_URL . '/assets/images/latest-images/create-bulk.svg' ); ?>" alt="">
												<span class="iseo-kwl-action-tip" aria-hidden="true">Create Bulk Project From Keyword List</span>
											</a>
											<a class="iseo-kwl-action"
												href="<?php echo esc_url( $iseo_edit_url ); ?>"
												aria-label="Edit Keyword List">
												<img src="<?php echo esc_url( WT_URL . '/assets/images/latest-images/write.svg' ); ?>" alt="">
												<span class="iseo-kwl-action-tip" aria-hidden="true">Edit Keyword List</span>
											</a>
											<a class="iseo-kwl-action submitdelete"
												href="<?php echo esc_url( admin_url('admin.php?page=improveseo_lists&action=delete&id=' . $item->id . '&noheader=true') ); ?>"
												onclick="return confirm('Are you sure you want to delete the list?')"
												aria-label="Delete Keyword List">
												<img src="<?php echo esc_url( WT_URL . '/assets/images/latest-images/delete.svg' ); ?>" alt="">
												<span class="iseo-kwl-action-tip" aria-hidden="true">Delete Keyword List</span>
											</a>
										</div>
									</td>
								</tr>
							<?php endforeach; ?>

						<?php else: ?>
							<tr>
								<td colspan="4" class="iseo-kwl-empty">
									<?php if ( '' !== $s ) : ?>
										No keyword lists match &ldquo;<?php echo esc_html( $s ); ?>&rdquo;.
										<a href="<?php echo esc_url( $iseo_lists_url ); ?>">Clear search</a>
									<?php else : ?>
										No keyword lists on this page.
										<a href="<?php echo esc_url( $iseo_lists_url ); ?>">Back to the first page</a>
									<?php endif; ?>
								</td>
							</tr>
						<?php endif; ?>

					</tbody>
				</table>
			</div>
		</div>
	</div>

	<?php // "+ New List" opens the same creation cards the first-run screen shows. A native <dialog>
	      // brings focus handling, Esc-to-close and the page-blocking layer with it; the wrapper div
	      // carries the padding so a click whose target is the <dialog> itself can only be the
	      // backdrop. ?>
	<dialog id="iseo-kwl-new-dialog" class="iseo-kwl-dialog" aria-labelledby="iseo-kwl-create-title">
		<div class="iseo-kwl-dialog-inner">
			<button type="button" class="iseo-kwl-dialog-close" aria-label="Close">&times;</button>
			<?php View::render( 'lists.create-choice' ); ?>
		</div>
	</dialog>

	<script>
	(function () {
		var dialog = document.getElementById('iseo-kwl-new-dialog');
		var opener = document.getElementById('iseo-kwl-new-list');
		// Without showModal() the button stays the plain link to the create screen it already is.
		if (!dialog || !opener || typeof dialog.showModal !== 'function') { return; }

		opener.addEventListener('click', function (e) {
			e.preventDefault();
			dialog.showModal();
		});
		dialog.querySelector('.iseo-kwl-dialog-close').addEventListener('click', function () {
			dialog.close();
		});
		dialog.addEventListener('click', function (e) {
			if (e.target === dialog) { dialog.close(); }
		});
		dialog.addEventListener('close', function () {
			opener.focus();
		});
	})();
	</script>

<?php endif; ?>
</div>

<?php View::endSection('content') ?>

<?php View::make('layouts.main') ?>
