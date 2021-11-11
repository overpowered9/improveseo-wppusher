<?php
use WorkHorse\View;
?>

<?php View::startSection('breadcrumbs') ?>
	<a href="<?= admin_url('admin.php?page=workhorse') ?>">Improve SEO</a>
	&raquo;
	<span>Users List</span>
<?php View::endSection('breadcrumbs') ?>

<?php View::startSection('content') ?>
<h1 class="hidden">Users List</h1>

	<div class="notice notice-success">
		<p>Here you can create users that are only for use within WorkHorse. The posts and projects you make will be distributed among these users to make your site look more authoritative (multiple authors) and natural.</p>
	</div>

<div class="shortcode improveseo_wrapper intro_page  p-3 p-lg-4">
		<section class="project-section border-bottom d-flex flex-row  justify-content-between align-items-center pb-2">
		<div class="project-heading d-flex flex-row align-items-center">
			<img class="mr-2" src="<?php echo WT_URL.'/assets/images/project-list-logo.png'?>" alt="ImproveSeo">
			<h1>Users List</h1>
		</div>
		<a href="<?= admin_url('admin.php?page=workhorse_users&action=create') ?>" id="btn-add" class="btn btn-outline-primary btn-small">Create Users</a>
	</section>

<section class="pagination-wrapper text-right d-flex flex-row align-items-center justify-content-between py-3">
		<span class="displaying-num"><?= $results['avail_roles']['workhorse_user'] ?> users</span>
		<span class="pagination-links">
			<?= paginate_links(array(
				'total' => $pages,
				'current' => $page,
				'format' => '&paged=%#%',
				'base' => admin_url('admin.php?page=workhorse_users%_%')
			)) ?>
		</span>
	</section>

	<section class="project-table-wrapper">
	<form method="get">
		<div class="table-responsive-sm">
		<table class="table fixed wp-list-table fixed table-view-list posts">
		<thead>
		<tr>
			<th class="manage-column column-title column-primary">Name</th>
			<th class="manage-column">E-Mail</th>
		</tr>
		</thead>
		<tbody>
			<?php foreach ($users as $user): ?>
			<tr>
				<td class="column-title column-primary has-row-actions">
					<strong class="mb-0">
						<a class="row-title"><span class="name-icon">A</span><?= $user->display_name ?></a>
					</strong>
					<div class="row-actions">

					</div>
					<button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button>
				</td>
				<td data-colname="Email">
					<?= $user->user_email ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
		</table>
		</div>
	</form>
</section>

<section class="pagination-wrapper text-right d-flex flex-row align-items-center justify-content-between py-3">
		<span class="displaying-num"><?= $results['avail_roles']['workhorse_user'] ?> users</span>
		<span class="pagination-links">
			<?= paginate_links(array(
				'total' => $pages,
				'current' => $page,
				'format' => '&paged=%#%',
				'base' => admin_url('admin.php?page=workhorse_users%_%')
			)) ?>
		</span>
	</section>
</div>
<?php View::endSection('content') ?>

<?php View::make('layouts.main') ?>