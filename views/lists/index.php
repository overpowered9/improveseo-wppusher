<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


use ImproveSEO\View;

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
	<!-- The two ways to make a list, as cards rather than the old pair of pill buttons, so each
	     one can say what it is for. Each card is a single link: the whole card is the target. -->
	<section class="iseo-kwl-create" aria-labelledby="iseo-kwl-create-title">
		<h2 id="iseo-kwl-create-title" class="iseo-kwl-create-title">Create Keyword List</h2>
		<p class="iseo-kwl-create-lead">You need a keyword list to use Bulk Post Generation. Choose how you want to create it.</p>
		<div class="iseo-kwl-cards">
			<a class="iseo-kwl-card" href="<?php echo esc_url( admin_url( 'admin.php?page=improveseo_lists&action=create' ) ); ?>">
				<span class="iseo-kwl-card-icon" aria-hidden="true">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" focusable="false"><rect x="8" y="2" width="8" height="4" rx="1"></rect><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><line x1="8" y1="11" x2="16" y2="11"></line><line x1="8" y1="15" x2="16" y2="15"></line><line x1="8" y1="19" x2="13" y2="19"></line></svg>
				</span>
				<span class="iseo-kwl-card-body">
					<span class="iseo-kwl-card-title">Paste My Keywords</span>
					<span class="iseo-kwl-card-text">Use a list you've already prepared.</span>
				</span>
				<svg class="iseo-kwl-card-chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><polyline points="9 18 15 12 9 6"></polyline></svg>
			</a>
			<a class="iseo-kwl-card" href="<?php echo esc_url( admin_url( 'admin.php?page=improveseo_keyword_generator' ) ); ?>">
				<span class="iseo-kwl-card-icon" aria-hidden="true">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" focusable="false"><path d="M10 3l1.9 5.1L17 10l-5.1 1.9L10 17l-1.9-5.1L3 10l5.1-1.9z"></path><path d="M18 14l.9 2.1L21 17l-2.1.9L18 20l-.9-2.1L15 17l2.1-.9z"></path><path d="M18 3v3M16.5 4.5h3"></path></svg>
				</span>
				<span class="iseo-kwl-card-body">
					<span class="iseo-kwl-card-title">Generate Keywords for Me</span>
					<span class="iseo-kwl-card-text">Build a list of related keywords automatically.</span>
				</span>
				<svg class="iseo-kwl-card-chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><polyline points="9 18 15 12 9 6"></polyline></svg>
			</a>
		</div>
	</section>
	<div class="actions search-form-box">
		<form class="improve-seo-form-global" method="GET">
			<input type="text" id="post-search-input" name="s" value="<?php echo esc_attr( $s ); ?>"
				placeholder="Search Here">
			<input type="hidden" name="page" value="improveseo_lists" />
			<input type="hidden" name="action" value="index" />
			<button type="submit" class="search-btn">
				<img src="<?php echo esc_url( WT_URL . '/assets/images/latest-images/clarity_search-line.svg' ); ?>"
					alt="clarity_search-line">
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
				<table class="table project_table_listing">
					<thead>
						<tr>
							<th>Keyword List</th>
							<th>Keyword List Preview</th>
							<th> </th>
						</tr>
					</thead>
					<tbody>
						<?php if (!empty($lists)): ?>
							<?php foreach ($lists as $item): ?>
								<tr>
									<?php // data-label is not just markup: under 767px style.css prints it as the
									      // cell's visible label, so it has to match the column header. The class
									      // carries the column width that used to hang off data-label="Name". ?>
									<td data-label="Keyword List" class="iseo-kwl-col-name"
										onclick="window.location.href='<?php echo esc_url( admin_url('admin.php?page=improveseo_lists&action=edit&id=' . $item->id) ); ?>'"
										style="cursor: pointer;  padding-top: 20px; vertical-align: text-top;">
										<strong><?php echo esc_html( $item->name ); ?> </strong>
									</td>
									<td data-label="Keyword List Preview"> <?php
									if (str_word_count($item->list) > 50):
										echo "<span class='list-content-overflow'>" . esc_html( $item->list ) . "</span>";
									else:
										echo esc_html( $item->list );
									endif;
									?></td>
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
												href="<?php echo esc_url( admin_url('admin.php?page=improveseo_lists&action=edit&id=' . $item->id) ); ?>"
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

							<?php
						else: ?>
							<tr>
								<td colspan="3">No Lists Available.</td>
							</tr>
						<?php endif; ?>

					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<?php View::endSection('content') ?>

<?php View::make('layouts.main') ?>