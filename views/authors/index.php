<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


use ImproveSEO\View;

?>



<?php View::startSection('breadcrumbs') ?>

<a href="<?php echo esc_url( admin_url('admin.php?page=improveseo_dashboard') ); ?>">Improve SEO</a>

&raquo;

<span>Authors List</span>

<?php View::endSection('breadcrumbs') ?>



<?php View::startSection('content') ?>

<h1 class="hidden">Authors List</h1>



<div class="seo-breadcumb">
	<div class="seo-text">
		<p> Here you can create authors that are only for use within ImproveSEO. The posts and projects you make will be
			distributed among these authors to make your site look more authoritative (multiple authors) and matural.
		</p>
	</div>
</div>
<div class="global-wrap">
	<div class="head-bar">
		<img src="<?php echo esc_url( improveseo_logo_url() ); ?>" alt="ImproveSEO logo">
		<h1>ImproveSEO | <?php echo IMPROVESEO_VERSION; ?></h1>
	</div>
	<div class="box-top">
		<ul class="breadcrumb-seo">
			<li><a href="#">Improve SEO</a></li>
			<li>Authors List</li>
		</ul>
		<div class="import-export-btn">
			<button class="active" id="btn-add"
				onclick="window.location.href='<?php echo esc_url( admin_url('admin.php?page=improveseo_authors&action=create') ); ?>'">
				Create Authors
			</button>

		</div>
	</div>
	<div class="actions">
		<div class="pagination">
			<?php if ($page > 1): ?>
				<button class="prev pagination-btn"
					onclick="window.location.href='<?php echo esc_url( admin_url('admin.php?page=improveseo_authors&paged=' . ($page - 1)) ); ?>'">
					&lt; Prev
				</button>
			<?php else: ?>
				<button class="prev pagination-btn" disabled style="opacity: 0.5; cursor: not-allowed;">
					&lt; Prev
				</button>
			<?php endif; ?>

			<?php for ($i = 1; $i <= $pages; $i++): ?>
				<?php if ($i == $page): ?>
					<button class="active"><?php echo  $i ?></button>
				<?php else: ?>
					<button onclick="window.location.href='<?php echo esc_url( admin_url('admin.php?page=improveseo_authors&paged=' . $i) ); ?>'"><?php echo  $i ?></button>
				<?php endif; ?>
			<?php endfor; ?>

			<?php if ($page < $pages): ?>
				<button class="next pagination-btn"
					onclick="window.location.href='<?php echo esc_url( admin_url('admin.php?page=improveseo_authors&paged=' . ($page + 1)) ); ?>'">
					Next &gt;
				</button>
			<?php else: ?>
				<button class="next pagination-btn" disabled style="opacity: 0.5; cursor: not-allowed;">
					Next &gt;
				</button>
			<?php endif; ?>
		</div>
		<div class="import-export">
			<p><?php echo  $results['avail_roles']['improveseo_user'] ?> Items</p>
		</div>
	</div>
	<div class="improve-seo-container">
		<div class="project-lists">
			<div class="table-responsive">
				<table class="table">
					<thead>
						<tr>
							<th> Name </th>
							<th>E-Mail</th>
							<th> </th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($users as $user): ?>
							<tr>
								<td data-label="Name"> <strong>
										<div class="improve-seo-letter">A</div> <?php echo  $user->display_name ?>
									</strong> </td>
								<td data-label="E-Mail"> <?php echo  $user->user_email ?> </td>
								<td data-label="Actions" class="Actions_td"> <a href="#"> <img
											src="<?php echo esc_url( WT_URL . '/assets/images/latest-images/ri_more-2-fill.svg' ); ?>"
											alt="ri_more-2-fill"> </a> </td>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>


<?php View::endSection('content') ?>



<?php View::make('layouts.main') ?>