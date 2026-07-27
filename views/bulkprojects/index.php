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
		<img src="<?php echo improveseo_logo_url(); ?>" alt="ImproveSEO logo">
		<h1>ImproveSEO | <?php echo IMPROVESEO_VERSION; ?></h1>
	</div>
	<div class="box-top">
		<ul class="breadcrumb-seo">
			<li><a href="<?= admin_url('admin.php?page=improveseo_dashboard') ?>">Improve SEO</a></li>
			<li>Bulk Projects List</li>
		</ul>
	</div>
	<div class="iseo-search-sort-row">
		<form method="GET" action="" class="iseo-search-form">
			<input type="hidden" name="page" value="improveseo_bulkprojects">
			<input type="text" name="search" value="<?= esc_attr($search) ?>"
				class="iseo-search-input" placeholder="Search Here">
			<button type="submit" class="iseo-search-btn">
				<img src="<?php echo WT_URL . '/assets/images/latest-images/clarity_search-line.svg' ?>" alt="search">
			</button>
		</form>
		<?php if ($search): ?>
			<a href="<?= admin_url('admin.php?page=improveseo_bulkprojects') ?>" class="iseo-clear-btn">&#x2715; Clear</a>
		<?php endif; ?>
		<?php
		$_bsbase = admin_url('admin.php?page=improveseo_bulkprojects&paged=1' . ($search ? '&search=' . urlencode($search) : ''));
		$_bname_order = ($orderBy === 'name' && $order === 'ASC') ? 'DESC' : 'ASC';
		$_bdate_order = ($orderBy === 'created_at' && $order === 'ASC') ? 'DESC' : 'ASC';
		$_bname_arrow = $orderBy === 'name' ? ($order === 'ASC' ? ' ↑' : ' ↓') : '';
		$_bdate_arrow = $orderBy === 'created_at' ? ($order === 'ASC' ? ' ↑' : ' ↓') : '';
		?>
		<div class="iseo-sort-controls">
			<span class="iseo-sort-label">Sort by</span>
			<a href="<?= esc_url($_bsbase . '&orderBy=name&order=' . $_bname_order) ?>"
				class="iseo-sort-pill<?= $orderBy === 'name' ? ' iseo-sort-on' : '' ?>">Name<?= $_bname_arrow ?></a>
			<a href="<?= esc_url($_bsbase . '&orderBy=created_at&order=' . $_bdate_order) ?>"
				class="iseo-sort-pill<?= $orderBy === 'created_at' ? ' iseo-sort-on' : '' ?>">Date<?= $_bdate_arrow ?></a>
		</div>
		<div class="import-export-btn">
			<button onclick="window.location.href='<?= admin_url('admin.php?page=improveseo_posting&action=create_post_bulk') ?>';"
				class="active">+ New Bulk Project</button>
		</div>
	</div>
	<div class="actions">
		<div>
			<button type="button" id="bulk-delete-btn" class="btn_delete" onclick="handleBulkDelete()" disabled style="opacity: 0.5;">Delete Selected Projects</button>
		</div>
		<div class="pagination">
			<?php if ($page > 1): ?>
				<button class="prev pagination-btn"
					onclick="window.location.href='<?= admin_url('admin.php?page=improveseo_bulkprojects' . ($search ? '&search=' . urlencode($search) : '') . '&orderBy=' . urlencode($orderBy) . '&order=' . urlencode($order) . '&paged=' . ($page - 1) . ($highlight ? '&highlight=' . $highlight : '')) ?>'">
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
						onclick="window.location.href='<?= admin_url('admin.php?page=improveseo_bulkprojects' . ($search ? '&search=' . urlencode($search) : '') . '&orderBy=' . urlencode($orderBy) . '&order=' . urlencode($order) . '&paged=' . $i . ($highlight ? '&highlight=' . $highlight : '')) ?>'"><?= $i ?></button>
				<?php endif; ?>
			<?php endfor; ?>

			<?php if ($page < $pages): ?>
				<button class="next pagination-btn"
					onclick="window.location.href='<?= admin_url('admin.php?page=improveseo_bulkprojects' . ($search ? '&search=' . urlencode($search) : '') . '&orderBy=' . urlencode($orderBy) . '&order=' . urlencode($order) . '&paged=' . ($page + 1) . ($highlight ? '&highlight=' . $highlight : '')) ?>'">
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
						<?php
					$sort_base = admin_url('admin.php?page=improveseo_bulkprojects&paged=1' . ($search ? '&search=' . urlencode($search) : ''));
					function bkiseo_sort_link($base, $col, $label, $currentCol, $currentOrder) {
						$nextOrder = ($currentCol === $col && $currentOrder === 'ASC') ? 'DESC' : 'ASC';
						$url = $base . '&orderBy=' . $col . '&order=' . $nextOrder;
						$arrow = ($currentCol === $col) ? ($currentOrder === 'ASC' ? ' ↑' : ' ↓') : '';
						return '<a href="' . esc_url($url) . '" style="color:inherit;text-decoration:none;white-space:nowrap;">' . esc_html($label) . $arrow . '</a>';
					}
					?>
					<thead>
						<tr>
							<th>
								<label class="checkbox style-c" for="cb-select-all">
									<input type="checkbox" id="cb-select-all">
									<div class="checkbox__checkmark"></div>
								</label>
								<h4><?= bkiseo_sort_link($sort_base, 'name', 'Name', $orderBy, $order) ?></h4>
							</th>
							<th> Post Count </th>
							<th><?= bkiseo_sort_link($sort_base, 'created_at', 'Created At', $orderBy, $order) ?></th>
							<th>Last Update</th>
							<th> Publish Mode </th>
							<th>Project Status</th>
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

											<span class="bkiseo-project-name" data-id="<?= esc_attr($project->id) ?>"><?= esc_html($project->name) ?></span>
									<button type="button" class="bkiseo-rename-btn" data-id="<?= esc_attr($project->id) ?>" title="Rename project"
										style="background:none;border:none;cursor:pointer;padding:2px 4px;color:#aaa;font-size:14px;line-height:1;margin-left:6px;">&#9998;</button>
										</div>
									</td>
									<td data-label="Post Count" style="text-align:center;"><?= $project->number_of_tasks ?>
									</td>
									<td data-label="Created At"> <?php
									$date = new DateTime($project->created_at);
									echo $date->format('m/d/Y H:i:s');
									?></td>
									<td data-label="Last Update"><?php
									$date = new DateTime($project->updated_at);
									echo $date->format('m/d/Y H:i:s');
									?></td>
									<td data-label="Publish Mode"> <?php
									if ($project->schedule_posts == 'draft_posts') {
										echo 'Save as Drafts';
									} else if ($project->schedule_posts == 'schedule_all_posts') {
										echo 'Publish Now';
									} else {
											$pm_count = intval($project->number_of_post_schedule);
											$pm_unit  = ($project->schedule_frequency == 'per_week') ? 'week' : 'day';
											// "Schedule" on its own line; keep the "(1 post/day)" part together
											// so it never wraps to "(1 / post/day)".
											echo 'Schedule<br><span style="white-space:nowrap;">(' . $pm_count . ' post' . ($pm_count === 1 ? '' : 's') . '/' . $pm_unit . ')</span>';
									}
									?> </td>
								<td data-label="Project Status" class="status finished"><?php
								if ($project->state == 'Stopped' || $project->state == 'Cancelled')
									echo '<span style="color: #ff4d4f; font-weight: 600;">Canceled</span>';
								elseif ($project->state == 'Finished')
									echo '<p class="post-fd">Completed</p>';
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
													<?php
													// Project-level menu order is part of the spec:
													//   View All Posts Within Project, Cancel Process,
													//   Export URLs to Excel, Delete Project
													// Cancel Process is still only offered while the project
													// can actually be cancelled.
													?>
													<span class="edit">
														<a class="popup-link" target="_blank" rel="noopener"
															href="<?= admin_url('admin.php?page=improveseo_bulkprojects&action=viewAllTasks&id=' . $project->id) ?>">View All Posts Within Project</a>
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
														<a class="popup-link"
															href="<?= admin_url('admin.php?page=improveseo_bulkprojects&action=export_urls&id=' . $project->id . '&name=' . urlencode($project->name) . '&noheader=true') ?>">
															Export URLs to Excel
														</a>
													</span>
													<span class="trash">
														<a class="popup-link delete-link submitdelete"
															href="<?= admin_url('admin.php?page=improveseo_bulkprojects&action=delete&id=' . $project->id) ?>"
															onclick="if(confirm('This action will delete the project and all generated posts/pages')) { window.location.href=this.href; } return false;">Delete Project</a>
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
				$(this).prop('indeterminate', false);
				updateDeleteButton();
			});

			$("input[name='project_ids[]']").change(function () {
				updateDeleteButton();
				updateSelectAll();
			});

			function updateDeleteButton() {
				const checkedCount = $("input[name='project_ids[]']:checked").length;
				const deleteBtn = $('#bulk-delete-btn');

				if (checkedCount > 0) {
					deleteBtn.prop('disabled', false).css('opacity', '1').text('Delete ' + checkedCount + ' Selected Project' + (checkedCount > 1 ? 's' : ''));
				} else {
					deleteBtn.prop('disabled', true).css('opacity', '0.5').text('Delete Selected Projects');
				}
			}

			function updateSelectAll() {
				const totalCheckboxes = $("input[name='project_ids[]']").length;
				const checkedCheckboxes = $("input[name='project_ids[]']:checked").length;

				$('#cb-select-all').prop('checked', totalCheckboxes === checkedCheckboxes);
				$('#cb-select-all').prop('indeterminate', checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes);
			}

			updateDeleteButton();
		});

		// ── Inline rename ──
		jQuery(document).on('click', '.bkiseo-rename-btn', function (e) {
			e.stopPropagation();
			var btn      = jQuery(this);
			var nameSpan = btn.siblings('.bkiseo-project-name');
			var id       = btn.data('id');
			var current  = nameSpan.text().trim();

			// Already editing
			if (btn.data('editing')) return;
			btn.data('editing', true);

			var input  = jQuery('<input type="text" class="bkiseo-rename-input">').val(current)
				.css({ padding:'4px 8px', border:'1px solid #1C7293', borderRadius:'4px', fontSize:'13px', minWidth:'160px' });
			var save   = jQuery('<button type="button" class="bkiseo-rename-save active" style="margin-left:6px;padding:4px 10px;font-size:12px;">Save</button>');
			var cancel = jQuery('<button type="button" class="bkiseo-rename-cancel" style="margin-left:4px;padding:4px 10px;font-size:12px;background:none;border:1px solid #ccc;border-radius:50px;cursor:pointer;">Cancel</button>');

			nameSpan.hide();
			btn.hide();
			nameSpan.after(input, save, cancel);
			input.focus().select();

			function stopEdit() {
				input.remove(); save.remove(); cancel.remove();
				nameSpan.show(); btn.show();
				btn.data('editing', false);
			}

			cancel.on('click', stopEdit);

			save.on('click', function () {
				var newName = input.val().trim();
				if (!newName) { input.focus(); return; }
				save.prop('disabled', true).text('Saving…');
				jQuery.post(ajaxurl, {
					action: 'rename_bulk_project',
					id:     id,
					name:   newName,
					nonce:  '<?php echo wp_create_nonce("rename_bulk_project_nonce"); ?>'
				}, function (res) {
					if (res.success) {
						nameSpan.text(res.data.name);
					}
					stopEdit();
				}).fail(stopEdit);
			});

			input.on('keydown', function (e) {
				if (e.which === 13) save.trigger('click');
				if (e.which === 27) stopEdit();
			});
		});

		// Show pencil on row hover
		jQuery(document).on('mouseenter', '.bkiseo-rename-btn', function () {
			jQuery(this).css('color', '#1C7293');
		}).on('mouseleave', '.bkiseo-rename-btn', function () {
			jQuery(this).css('color', '#aaa');
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