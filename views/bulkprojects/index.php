<?php

use ImproveSEO\View;

if (isset($_GET['post_preview'])) {
	if ($_GET['post_preview'] == 'true') {
		$project = $projects[0];
		if ($project->state == 'Published' && $project->iteration == $project->max_iterations) {
			$export_url = admin_url("admin.php?page=improveseo_bulkprojects&action=export_preview_url&id={$project->id}&noheader=true");
			header("Location:" . $export_url);
			exit;
		}
	}
}
?>

<?php View::startSection('breadcrumbs') ?>

<a href="<?= admin_url('admin.php?page=improveseo_dashboard') ?>">Improve SEO</a>

&raquo;

<span>Bulk Projects List</span>

<?php View::endSection('breadcrumbs') ?>

<?php View::startSection('content') ?>

<?php View::render('import/import') ?>

<h1 class="hidden">Bulk Product Listing</h1>
<div class="global-wrap">
	<div class="head-bar">
		<img src="<?php echo WT_URL . '/assets/images/latest-images/seo-latest-logo.svg'; ?>" alt="project-list-logo">
		<h1> ImproveSEO | 2.0.11 </h1>
		<span>Pro</span>
	</div>
	<div class="box-top">
		<ul class="breadcrumb-seo">
			<li><a href="#">Improve SEO</a></li>
			<li>Bulk Projects List</li>
		</ul>
		<div class="import-export-btn">
			<button onclick="window.location.href='<?= admin_url('admin.php?page=improveseo_create_bulk') ?>';"
				class="active">Add New</button>
		</div>
	</div>
	<div class="actions">
		<div>
			<button type="button" class="btn_delete" onclick="handleBulkDelete()" disabled style="opacity: 0.5;">Delete
				Selected Projects</button>
		</div>
		<div class="pagination">
			<?php if ($page > 1): ?>
				<button class="prev pagination-btn"
					onclick="window.location.href='<?= admin_url('admin.php?page=improveseo_bulkprojects&paged=' . ($page - 1) . ($highlight ? '&highlight=' . $highlight : '')) ?>'">
					&lt; Prev
				</button>
			<?php else: ?>
				<button class="prev pagination-btn" disabled style="opacity: 0.5; cursor: not-allowed;">
					&lt; Prev
				</button>
			<?php endif; ?>

			<?php for ($i = 1; $i <= $pages; $i++): ?>
				<?php if ($i == $page): ?>
					<button class="active"><?= $i ?></button>
				<?php else: ?>
					<button
						onclick="window.location.href='<?= admin_url('admin.php?page=improveseo_bulkprojects&paged=' . $i . ($highlight ? '&highlight=' . $highlight : '')) ?>'"><?= $i ?></button>
				<?php endif; ?>
			<?php endfor; ?>

			<?php if ($page < $pages): ?>
				<button class="next pagination-btn"
					onclick="window.location.href='<?= admin_url('admin.php?page=improveseo_bulkprojects&paged=' . ($page + 1) . ($highlight ? '&highlight=' . $highlight : '')) ?>'">
					Next &gt;
				</button>
			<?php else: ?>
				<button class="next pagination-btn" disabled style="opacity: 0.5; cursor: not-allowed;">
					Next &gt;
				</button>
			<?php endif; ?>
		</div>
		<div class="import-export">
			<p><?= $total ?> Items</p>
		</div>
	</div>
	<form id="bulk-actions-form" method="post" action="<?= admin_url('admin.php?page=improveseo_bulkprojects') ?>">
		<?php wp_nonce_field('bulk_delete_projects', 'bulk_delete_nonce'); ?>
		<input type="hidden" name="action" value="" id="bulk-action-input">
		<div class="improve-seo-container">
			<div class="project-lists">
				<div class="table-responsive">
					<table class="table project_table_listing">
						<thead>
							<tr>
								<th>
									<label class="checkbox style-c" for="cb-select-all">
										<input type="checkbox" id="cb-select-all">
										<div class="checkbox__checkmark"></div>
									</label>
									<h4> Name </h4>
								</th>
								<th> Post Count </th>
								<th>Created At</th>
								<th>Last Update</th>
								<th> Publish Option </th>
								<th>Status</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($projects as $project): ?>
								<tr <?= $highlight == $project->id ? ' class="WHProject--highlight"' : '' ?>>
									<td data-label="Name">
										<div class="styling_projects_name_td"
											style="display: flex; width: 100%; flex-wrap: nowrap; padding: 30px 0px; overflow-wrap: break-word;">
											<label class="checkbox style-c">
												<input id="cb-select-<?php echo $project->id; ?>" type="checkbox"
													name="project_ids[]" value="<?php echo $project->id; ?>">
												<div class="checkbox__checkmark"></div>
											</label>

											<h4> <?= $project->name ?> </h4>
										</div>
									</td>
									<td data-label="Post Count" style="text-align:center;"><?= $project->number_of_tasks ?>
									</td>
									<td data-label="Created At"> <?php
									$date = new DateTime($project->created_at);
									echo $date->format('d/m/Y H:i:s');
									?></td>
									<td data-label="Last Update"><?php
									$date = new DateTime($project->updated_at);
									echo $date->format('d/m/Y H:i:s');
									?></td>
									<td data-label="Publish Option"> <?php
									if ($project->schedule_posts == 'draft_posts') {
										echo 'Draft';
									} else if ($project->schedule_posts == 'schedule_all_posts') {
										echo 'Publish All';
									} else {
										echo 'Publish : ' . $project->number_of_post_schedule . ' Post / ' . $project->schedule_frequency;
									}
									?> </td>
								<td data-label="Status" class="status finished"><?php
								if ($project->state == 'Stopped')
									echo '<p class="post-st" style="color: #ff4d4f; font-weight: 600;">Canceled</p>';
								elseif ($project->state == 'Finished')
									echo '<p class="post-fd">Finished</p>';
								elseif ($project->state == 'Draft')
									echo 'Draft';
								else
									echo 'Processing';
								?></td>
									<td scope="col" data-label="Action" class="actions-btn" style="width: 4%;">

										<a href="#" class="action-btn-pop"> <img
												src="<?php echo WT_URL . '/assets/images/latest-images/ri_more-2-fill.svg' ?>"
												alt="ri_more-2-fill"> </a>
										<div class="actionpopup">
											<div class="popup-arrow"></div>
											<ul class="popup-menu">
												<div class="row-actions"
													style="display: flex; flex-direction: column !important;">

													<span class="edit">
														<a class="popup-link"
															href="<?= admin_url('admin.php?page=improveseo_bulkprojects&action=export_urls_excel&id=' . $project->id . '&name=' . $project->name) ?>">
															Export a list of all posts/pages URLs (Excel)
														</a>
													</span>
										<?php if ($project->state != 'Stopped' && $project->state != 'Finished' && $project->state != 'Draft') { ?>
										<span class="edit">
											<a class="popup-link"
												href="<?= admin_url('admin.php?page=improveseo_bulkprojects&action=stop_bulk_task&id=' . $project->id) ?>"
												onclick="if(confirm('Are you sure you want to cancel this project? All ongoing tasks will be halted immediately.')) { window.location.href=this.href; } return false;">
												Cancel Process
											</a>
										</span>
										<?php } ?>
												<span class="edit">
														<a class="popup-link" class="submitdelete" target="_blank"
															href="<?= admin_url('admin.php?page=improveseo_bulkprojects&action=viewAllTasks&id=' . $project->id) ?>">View
															all AI posts/pages</a>
													</span>
													<span class="trash">
														<a class="popup-link delete-link submitdelete"
															href="<?= admin_url('admin.php?page=improveseo_bulkprojects&action=delete&id=' . $project->id) ?>"
															onclick="if(confirm('This action will delete project and all generated posts/pages')) { window.location.href=this.href; } return false;">Delete
															project and all posts/pages</a>
													</span>
												</div>
											</ul>
										</div>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</form>
	<script>
		jQuery(document).ready(function ($) {
			$('#cb-select-all').click(function (e) {
				$("input[name='project_ids[]']").prop('checked', $(this).prop('checked'));
				updateDeleteButton();
			});

			$("input[name='project_ids[]']").change(function () {
				updateDeleteButton();
				updateSelectAll();
			});

			function updateDeleteButton() {
				const checkedCount = $("input[name='project_ids[]']:checked").length;
				const deleteBtn = $('.btn_delete');

				if (checkedCount > 0) {
					deleteBtn.prop('disabled', false).css('opacity', '1');
				} else {
					deleteBtn.prop('disabled', true).css('opacity', '0.5');
				}
			}

			function updateSelectAll() {
				const totalCheckboxes = $("input[name='project_ids[]']").length;
				const checkedCheckboxes = $("input[name='project_ids[]']:checked").length;

				$('#cb-select-all').prop('checked', totalCheckboxes === checkedCheckboxes);
			}

			updateDeleteButton();
		});

		function handleBulkDelete() {
			const checkedBoxes = jQuery("input[name='project_ids[]']:checked");

			if (checkedBoxes.length === 0) {
				alert('Please select at least one project to delete.');
				return false;
			}

			const projectCount = checkedBoxes.length;
			const confirmMessage = `Are you sure you want to delete ${projectCount} project(s) and all their associated posts/pages? This action cannot be undone.`;

			if (confirm(confirmMessage)) {
				document.getElementById('bulk-action-input').value = 'bulk-delete-projects';
				document.getElementById('bulk-actions-form').submit();
			}
		}
	</script>
</div>

<?php View::endSection('content') ?>

<?php View::make('layouts.main') ?>