<?php
use ImproveSEO\View;
?>
<?php View::startSection('breadcrumbs') ?>
<a href="<?= admin_url('admin.php?page=improveseo_dashboard') ?>">Improve SEO</a>
&raquo;
<span>Shortcodes List</span>
<?php View::endSection('breadcrumbs') ?>
<?php View::startSection('content') ?>
<h1 class="hidden">Shortcodes List</h1>
<div class="notice notice-success is-dismissible notice-improveseo">
	<p>
		Shortcodes are created for use within the YouTube video and image scraper, but you can create shortcodes here manually. Once you've created a shortcode, simply use <strong>[name of your shortcode]</strong> to implement it within your post!
	</p>
</div>
<div class="shortcode improveseo_wrapper p-3 p-lg-4">
	<section class="project-section border-bottom d-flex flex-row  justify-content-between align-items-center pb-2">
		<div class="project-heading d-flex flex-row align-items-center">
			<img class="mr-2" src="<?php echo WT_URL.'/assets/images/project-list-logo.png'?>" alt="ImproveSeo">
			<h1>Shortcodes List</h1>
		</div>
		<a href="<?= admin_url('admin.php?page=improveseo_shortcodes&action=create') ?>" class="btn btn-outline-primary btn-small" id="btn-add">Add New</a>
	</section>
	<section class="tabs-wrap clearfix border-bottom mb-4">
		<ul class="subsubsub m-0">
			<li class="all">
				<a href="<?= admin_url('admin.php?page=improveseo_shortcodes&type=all') ?>" class="<?php if ($type == 'all') echo 'current' ?>">
					All
					<span class="count">(<?= $all ?>)</span>
				</a>
			</li>
			<li class="static">
				<a href="<?= admin_url('admin.php?page=improveseo_shortcodes&type=static') ?>" class="<?php if ($type == 'static') echo 'current' ?>">
					Static
					<span class="count">(<?= $static ?>)</span>
				</a>
			</li>
			<li class="dynamic">
				<a href="<?= admin_url('admin.php?page=improveseo_shortcodes&type=dynamic') ?>" class="<?php if ($type == 'dynamic') echo 'current' ?>">
					Dynamic
					<span class="count">(<?= $dynamic ?>)</span>
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
							<th scope="col" class="manage-column manage-column column-title column-primary">Shortcode</th>
							<th scope="col" class="manage-column">Type</th>
							<th scope="col" class="manage-column">Created At</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($shortcodes as $code): ?>
						<tr>
							<td class="column-title column-primary has-row-actions">
								<strong class="d-none">
								<a class="row-title" href="<?= admin_url('admin.php?page=improveseo_shortcodes&action=edit&id='. $code->id) ?>"><?= $code->shortcode ?></a>
								</strong>
								<p>
									<?= $code->content ?>
								</p>
								<div class="row-actions">
									<span class="edit">
										<a href="<?= admin_url('admin.php?page=improveseo_shortcodes&action=edit&id='. $code->id) ?>" title="Edit this item">Edit</a>
										|
									</span>
									<span class="trash">
										<a class="submitdelete" href="<?= admin_url('admin.php?page=improveseo_shortcodes&action=delete&id='. $code->id .'&noheader=true') ?>">Trash</a>
									</span>
								</div>
								<button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button>
							</td>
							<td data-colname="Type"><?= $code->type ?></td>
							<td data-colname="Created At">
								<?php
									$date = new DateTime($code->created_at);
									echo $date->format('d/m/Y H:i:s');
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