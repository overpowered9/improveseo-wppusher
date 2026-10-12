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
 * A stored timestamp as a short date: "Sep 12, 2026". Always the three-letter month (translated by
 * mysql2date), not the site's date_format, which can spell it out in full and push "Created …" and
 * "Last used …" onto two lines.
 * Empty for a missing or zero date, so the caller can print its own placeholder.
 */
$iseo_kwl_date = function ( $mysql_date ) {
	if ( empty( $mysql_date ) || 0 === strpos( (string) $mysql_date, '0000-00-00' ) ) {
		return '';
	}
	$formatted = mysql2date( 'M j, Y', $mysql_date );
	return $formatted ? $formatted : '';
};

/** A Bulk project's task list — where a "Project" link in the Usage column goes. */
$iseo_project_url = function ( $project_id ) {
	return admin_url( 'admin.php?page=improveseo_bulkprojects&action=viewAllTasks&id=' . absint( $project_id ) );
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
	</div>

	<?php // Same search row and "+ New …" button as the Single Projects screen (views/projects/index.php):
	      // the same classes, so the button's size, shape, colour and the space beneath it come from
	      // one set of rules instead of a look-alike copy. The onclick is the way in when the dialog
	      // cannot open (a browser without <dialog>); the script at the bottom swaps it for the dialog. ?>
	<div class="iseo-search-sort-row">
		<form method="GET" action="" class="iseo-search-form">
			<input type="hidden" name="page" value="improveseo_lists">
			<input type="hidden" name="action" value="index">
			<input type="text" name="s" value="<?php echo esc_attr( $s ); ?>"
				class="iseo-search-input" placeholder="Search lists or keywords"
				aria-label="Search keyword lists by name or keyword">
			<button type="submit" class="iseo-search-btn">
				<img src="<?php echo esc_url( WT_URL . '/assets/images/latest-images/clarity_search-line.svg' ); ?>" alt="Search">
			</button>
		</form>
		<?php if ( '' !== $s ) : ?>
			<a href="<?php echo esc_url( $iseo_lists_url ); ?>" class="iseo-clear-btn">&#x2715; Clear</a>
		<?php endif; ?>
		<div class="import-export-btn">
			<button type="button" id="iseo-kwl-new-list" class="active" aria-haspopup="dialog"
				onclick="window.location.href='<?php echo esc_url( admin_url( 'admin.php?page=improveseo_lists&action=create' ) ); ?>';">+ New List</button>
		</div>
	</div>

	<div class="actions">
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
											<?php // Both dates on one line; each part is nowrap, so a narrow cell breaks
											      // between them rather than inside a date. ?>
											<span class="iseo-kwl-meta iseo-kwl-dates">
												<span>Created <?php echo esc_html( $iseo_created ? $iseo_created : '—' ); ?></span>
												<span class="iseo-kwl-dates-sep" aria-hidden="true">&middot;</span>
												<span>Last used <?php echo esc_html( $iseo_used_on ? $iseo_used_on : '—' ); ?></span>
											</span>
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
											<?php $iseo_projects = $iseo_use ? $iseo_use->projects : array(); ?>
											<span class="iseo-kwl-status iseo-kwl-status--<?php echo esc_attr( $iseo_status ); ?>"
												<?php if ( count( $iseo_projects ) > 1 ) : ?>title="Status of the latest project"<?php endif; ?>><?php echo esc_html( $iseo_use ? $iseo_use->label : 'Not used yet' ); ?></span>
											<?php if ( 1 === count( $iseo_projects ) ) : ?>
												<span class="iseo-kwl-meta">
													Project:
													<a class="iseo-kwl-project" href="<?php echo esc_url( $iseo_project_url( $iseo_projects[0]->id ) ); ?>"><?php echo esc_html( $iseo_projects[0]->name ); ?></a>
												</span>
											<?php elseif ( count( $iseo_projects ) > 1 ) : ?>
												<?php // Every project that used the list, newest first, behind a native
												      // disclosure: no script, keyboard and screen-reader support built in.
												      // The status pill above describes the first (latest) one. ?>
												<details class="iseo-kwl-projects">
													<summary class="iseo-kwl-meta"><?php echo esc_html( sprintf( 'Used in %d projects', count( $iseo_projects ) ) ); ?></summary>
													<ul>
														<?php foreach ( $iseo_projects as $iseo_i => $iseo_project ) :
															$iseo_project_date = $iseo_kwl_date( $iseo_project->created ); ?>
															<li>
																<a class="iseo-kwl-project" href="<?php echo esc_url( $iseo_project_url( $iseo_project->id ) ); ?>"><?php echo esc_html( $iseo_project->name ); ?></a>
																<span class="iseo-kwl-meta"><?php echo esc_html( implode( ' · ', array_filter( array( 0 === $iseo_i ? 'Latest' : '', $iseo_project_date ) ) ) ); ?></span>
															</li>
														<?php endforeach; ?>
													</ul>
												</details>
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
		// Without showModal() the button keeps its onclick: straight to the create screen.
		if (!dialog || !opener || typeof dialog.showModal !== 'function') { return; }

		opener.onclick = null;
		opener.addEventListener('click', function () {
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
