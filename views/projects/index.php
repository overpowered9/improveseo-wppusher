<?php

use ImproveSEO\View;

function get_post_by_project_id($project_id)
{
	global $wpdb;

	$post_id = $wpdb->get_var($wpdb->prepare(
		"SELECT post_id FROM {$wpdb->postmeta} 
         WHERE meta_key = 'improveseo_project_id' 
         AND meta_value = %s 
         LIMIT 1",
		$project_id
	));

	if ($post_id) {
		$post = get_post($post_id);
		if ($post && $post->post_status !== 'trash') {
			return $post;
		}
	}

	return null;
}

if (isset($_GET['post_preview']) && $_GET['post_preview'] == 'true' && isset($_GET['preview_id'])) {
	global $wpdb;
	$preview_id = intval($_GET['preview_id']);
	// Only ever act on a genuine preview project, identified by the dedicated
	// 'Preview' state — never on a real Draft/Published/Updated project.
	$preview_project = $wpdb->get_row($wpdb->prepare(
		"SELECT id, state, iteration, max_iterations FROM {$wpdb->prefix}improveseo_tasks WHERE id = %d AND state = 'Preview'",
		$preview_id
	));
	if ($preview_project && (int) $preview_project->iteration >= (int) $preview_project->max_iterations) {
		$export_url = admin_url("admin.php?page=improveseo_projects&action=export_preview_url&id={$preview_project->id}&noheader=true");
		header("Location:" . $export_url);
		exit;
	}
}

?>

<?php View::startSection('breadcrumbs') ?>

<a href="<?= admin_url('admin.php?page=improveseo_dashboard') ?>">Improve SEO</a>

&raquo;

<span>Projects List</span>

<?php View::endSection('breadcrumbs') ?>

<?php View::startSection('content') ?>

<?php View::render('import/import') ?>

<h1 class="hidden">Product Listing</h1>

<div class="global-wrap">
	<div class="head-bar">
		<img src="<?php echo improveseo_logo_url() ?>" alt="ImproveSEO logo">
		<h1>ImproveSEO | <?php echo IMPROVESEO_VERSION; ?></h1>
	</div>
	<div class="box-top">
		<ul class="breadcrumb-seo">
			<li><a href="<?= admin_url('admin.php?page=improveseo_dashboard') ?>">Improve SEO</a></li>
			<li>Projects List</li>
		</ul>
	</div>
	<div class="iseo-search-sort-row">
		<form method="GET" action="" class="iseo-search-form">
			<input type="hidden" name="page" value="improveseo_projects">
			<input type="text" name="search" value="<?= esc_attr($search) ?>"
				class="iseo-search-input" placeholder="Search Here">
			<button type="submit" class="iseo-search-btn">
				<img src="<?php echo WT_URL . '/assets/images/latest-images/clarity_search-line.svg' ?>" alt="search">
			</button>
		</form>
		<?php if ($search): ?>
			<a href="<?= admin_url('admin.php?page=improveseo_projects') ?>" class="iseo-clear-btn">&#x2715; Clear</a>
		<?php endif; ?>
		<?php
		$_sbase = admin_url('admin.php?page=improveseo_projects&paged=1' . ($search ? '&search=' . urlencode($search) : ''));
		$_name_order  = ($orderBy === 'name' && $order === 'ASC') ? 'DESC' : 'ASC';
		$_date_order  = ($orderBy === 'created_at' && $order === 'ASC') ? 'DESC' : 'ASC';
		$_name_arrow  = $orderBy === 'name' ? ($order === 'ASC' ? ' ↑' : ' ↓') : '';
		$_date_arrow  = $orderBy === 'created_at' ? ($order === 'ASC' ? ' ↑' : ' ↓') : '';
		?>
		<div class="iseo-sort-controls">
			<span class="iseo-sort-label">Sort by</span>
			<a href="<?= esc_url($_sbase . '&orderBy=name&order=' . $_name_order) ?>"
				class="iseo-sort-pill<?= $orderBy === 'name' ? ' iseo-sort-on' : '' ?>">Name<?= $_name_arrow ?></a>
			<a href="<?= esc_url($_sbase . '&orderBy=created_at&order=' . $_date_order) ?>"
				class="iseo-sort-pill<?= $orderBy === 'created_at' ? ' iseo-sort-on' : '' ?>">Date<?= $_date_arrow ?></a>
		</div>
		<div class="import-export-btn">
			<button onclick="window.location.href='<?= admin_url('admin.php?page=improveseo_posting&action=create_post_single') ?>';"
				class="active">Add New</button>
		</div>
	</div>
	<div class="actions">
		<div>
			<button type="button" class="btn_delete" id="bulk-delete-btn">Delete Selected Projects</button>
		</div>
		<div class="pagination">
			<?php if ($page > 1): ?>
				<a href="<?= admin_url('admin.php?page=improveseo_projects' . ($search ? '&search=' . urlencode($search) : '') . '&orderBy=' . urlencode($orderBy) . '&order=' . urlencode($order) . '&paged=' . ($page - 1)) ?>"
					class="prev pagination-btn">
					< Prev</a>
					<?php else: ?>
						<span class="prev pagination-btn disabled">
							< Prev</span>
							<?php endif; ?>

							<?php
							// Calculate pagination range
							$start_page = max(1, $page - 2);
							$end_page = min($pages, $page + 2);

							// Show first page if we're not showing it
							if ($start_page > 1): ?>
								<a href="<?= admin_url('admin.php?page=improveseo_projects' . ($search ? '&search=' . urlencode($search) : '') . '&orderBy=' . urlencode($orderBy) . '&order=' . urlencode($order) . '&paged=1') ?>"
									class="pagination-btn">1</a>
								<?php if ($start_page > 2): ?>
									<span class="pagination-btn">...</span>
								<?php endif; ?>
							<?php endif; ?>

							<?php for ($i = $start_page; $i <= $end_page; $i++): ?>
								<?php if ($i == $page): ?>
									<button class="pagination-btn active"><?= $i ?></button>
								<?php else: ?>
									<a href="<?= admin_url('admin.php?page=improveseo_projects' . ($search ? '&search=' . urlencode($search) : '') . '&orderBy=' . urlencode($orderBy) . '&order=' . urlencode($order) . '&paged=' . $i) ?>"
										class="pagination-btn"><?= $i ?></a>
								<?php endif; ?>
							<?php endfor; ?>

							<?php
							// Show last page if we're not showing it
							if ($end_page < $pages): ?>
								<?php if ($end_page < $pages - 1): ?>
									<span class="pagination-btn">...</span>
								<?php endif; ?>
								<a href="<?= admin_url('admin.php?page=improveseo_projects' . ($search ? '&search=' . urlencode($search) : '') . '&orderBy=' . urlencode($orderBy) . '&order=' . urlencode($order) . '&paged=' . $pages) ?>"
									class="pagination-btn"><?= $pages ?></a>
							<?php endif; ?>

							<?php if ($page < $pages): ?>
								<a href="<?= admin_url('admin.php?page=improveseo_projects' . ($search ? '&search=' . urlencode($search) : '') . '&orderBy=' . urlencode($orderBy) . '&order=' . urlencode($order) . '&paged=' . ($page + 1)) ?>"
									class="next pagination-btn">Next ></a>
							<?php else: ?>
								<span class="next pagination-btn disabled">Next ></span>
							<?php endif; ?>
		</div>
		<div class="import-export">
			<p>Showing <?= (($page - 1) * $limit) + 1 ?> to <?= min($page * $limit, $total) ?> of <?= $total ?> Items
			</p>
		</div>
	</div>
	<div class="improve-seo-container">
		<div class="project-lists">
			<form id="bulk-delete-form" method="POST" action="<?= admin_url('admin.php?page=improveseo_projects') ?>">
				<?php wp_nonce_field('bulk_delete_projects', 'bulk_delete_nonce'); ?>
				<input type="hidden" name="action" value="bulk_delete">

				<div class="table-responsive">
					<table class="table project_table_listing">
						<?php
						// Build a base URL that preserves search and resets to page 1
						$sort_base = admin_url('admin.php?page=improveseo_projects&paged=1' . ($search ? '&search=' . urlencode($search) : ''));
						function iseo_sort_link($base, $col, $label, $currentCol, $currentOrder) {
							$nextOrder = ($currentCol === $col && $currentOrder === 'ASC') ? 'DESC' : 'ASC';
							$url = $base . '&orderBy=' . $col . '&order=' . $nextOrder;
							$arrow = '';
							if ($currentCol === $col) {
								$arrow = $currentOrder === 'ASC' ? ' ↑' : ' ↓';
							}
							return '<a href="' . esc_url($url) . '" style="color:inherit; text-decoration:none; white-space:nowrap;">' . esc_html($label) . $arrow . '</a>';
						}
						?>
						<thead>
							<tr>
								<th>
									<label class="checkbox style-c" for="cb-select-all">
										<input type="checkbox" id="cb-select-all">
										<div class="checkbox__checkmark"></div>
									</label>
									<h4><?= iseo_sort_link($sort_base, 'name', 'Name', $orderBy, $order) ?></h4>
								</th>
								<th><?= iseo_sort_link($sort_base, 'created_at', 'Created At', $orderBy, $order) ?></th>
								<th>Last Update</th>
								<th>Post Status</th>
								<td></td>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($projects as $project): ?>
								<tr>
									<td data-label="Name">
										<div class="styling_projects_name_td">
											<label class="checkbox style-c">
												<input id="cb-select-<?php echo $project->id; ?>" type="checkbox"
													name="project_ids[]" value="<?php echo $project->id; ?>"
													class="project-checkbox">
												<div class="checkbox__checkmark"></div>
											</label>
											<h4><?= $project->name ?></h4>
										</div>
									</td>
									<td data-label="Created At"><?php
									$date = new DateTime($project->created_at);
									echo $date->format('m/d/Y H:i:s');
									?></td>
									<td data-label="Last Update"><?php
									$date = new DateTime($project->updated_at);
									echo $date->format('m/d/Y H:i:s');
									?></td>
									<td data-label="Status" class="status finished">

										<?php
										if ($project->state == 'Draft')
											echo 'Draft';
										else {
											if ($project->iteration >= $project->max_iterations)
												echo '<p class="post-fd">Published</p>';
											else {
												$updated = strtotime($project->updated_at);
												if ($project->deleted_at == '1970-01-01 11:11:11')
													echo '<p class="post-fd">Canceled</p>';
												elseif (time() - $updated > 1200)
													echo '<p class="post-fd">Paused</p>';
												else
													echo 'Processing';
											}
										}
										?>
									</td>
									<td>
										<?php if ($project->state == 'Published' && $project->iteration < $project->max_iterations): ?>
											<a href="javascript:build_project(<?= $project->id ?>)"
												class="styling_post_page_action_buttons for_width_only_" target="_self">Publish
												</a>
										<?php endif; ?>
										<?php if ($project->state == 'Updated' && $project->iteration < $project->max_iterations): ?>
											<a href="javascript:update_project(<?= $project->id ?>)"
												style="width: 160px !important;" class="styling_post_page_action_buttons"
												target="_self">Update posts</a>
										<?php endif; ?>
										<input type="hidden" name="max-iterations" id="max-iterations"
											data-project="<?php echo $project->id; ?>"
											value="<?php echo $project->max_iterations; ?>" />
										<?php if ($project->state == 'Draft'): ?>
											<a href="<?= admin_url('admin.php?page=improveseo_dashboard&action=edit_post&id=' . $project->id) ?>"
												style="width: 160px !important;"
												class="styling_post_page_action_buttons">Edit Draft</a>
										<?php endif; ?>
									</td>
									<td style="width: 4%;" scope="col" data-label="Action" class="actions-btn">
										<a href="#" class="action-btn-pop">
											<img src="<?php echo WT_URL . '/assets/images/latest-images/ri_more-2-fill.svg' ?>"
												alt="ri_more-2-fill">
										</a>
										<div class="actionpopup">
											<div class="popup-arrow"></div>
											<ul class="popup-menu">
												<li>
													<?php
													$associated_post = get_post_by_project_id($project->id);
													if ($associated_post): ?>
														<a href="<?= get_permalink($associated_post->ID) ?>" target="_blank"
															style="max-width: max-content !important;" class="popup-link">
															View Post
														</a>
													<?php else: ?>
														<span
															style="max-width: max-content !important; color: #999; cursor: not-allowed;"
															class="popup-link disabled">
															No Post Created
														</span>
													<?php endif; ?>
												</li>
												<li><a href="<?= admin_url('admin.php?page=improveseo_projects&action=view_details&id=' . $project->id) ?>" style="max-width: max-content !important;"
														class="popup-link">View Details</a></li>
												<?php $edit_link = $associated_post ? get_edit_post_link($associated_post->ID, 'raw') : ''; ?>
												<?php if ($edit_link): ?>
												<li><a target="_blank"
														href="<?= esc_url($edit_link) ?>"
														style="max-width: max-content !important;" class="popup-link">Edit
														Post</a>
												</li>
												<?php endif; ?>
												<li style="margin: 0px !important;"><a target="_blank"
														href="<?= admin_url('admin.php?page=improveseo_projects&action=delete&id=' . $project->id . '&noheader=true') ?>"
														style="max-width: max-content !important;"
														class="popup-link delete-link">Delete Post</a></li>
											</ul>
										</div>

									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</form>
		</div>
	</div>
</div>

<script type="text/javascript">
	function build_project(id) {
		jQuery('.show_loading').css("display", "block");
		jQuery(".show_loading h1")
			.html("Building project.... in progress");
		start_build(id);
	}
	var numm;

	function start_build(ids) {
		var max_iterations = parseInt(jQuery('#max-iterations').val());
		var export_url = "<?php echo admin_url("admin.php?page=improveseo_projects&action=export_preview_url&id="); ?>";
		jQuery
			.ajax({
				url: "<?php echo admin_url("admin-ajax.php"); ?>",
				data: ({
					action: 'workdex_builder_ajax',
					page: 100,
					ajax: 1,
					id: ids
				}),
				success: function (data) {
					jQuery(".show_loading h1")
						.html("Building project.... in progress");
					jQuery(".show_loading h2")
						.html("Posts generated by now " + data);
					var is_preview_available = jQuery('#is_preview_available').val();
					if (max_iterations > 100) {
						if (numm == data) {
							jQuery('.show_loading').css("display", "none");
							if (is_preview_available == "yes") {
								window.location.href = export_url + ids + '&noheader=true';
							}
						} else {
							numm = data;
							setTimeout("start_build(" + ids + ")", 100);
						}
						location.reload(true);
					} else {

						if (is_preview_available == "yes") {
							jQuery(".show_loading h1").html("Exporting posts. Please wait");
							jQuery(".show_loading h2").html("");
							window.location.href = export_url + ids + '&noheader=true';
						} else {
							setTimeout(function () {
								jQuery('.show_loading').css("display", "none");
								location.reload(true);
							}, 100);
						}
					}
				}
			});
	}

	function update_project(id) {
		jQuery('.show_loading').css("display", "block");
		jQuery(".show_loading h1")
			.html("Updating project.... in progress");
		start_update(id);
	}
	var numm_update;

	function start_update(ids) {

		var new_location = "<?php echo admin_url('admin.php?page=improveseo_projects'); ?>";
		var max_iterations = parseInt(jQuery('#max-iterations[data-project="' + ids + '"]').val());
		jQuery.ajax({
			url: "<?php echo admin_url("admin-ajax.php"); ?>",
			data: ({
				action: 'workdex_builder_update_ajax',
				page: 100,
				ajax: 1,
				id: ids
			}),
			success: function (data) {
				jQuery(".show_loading h1")
					.html("Updating posts.... in progress");
				jQuery(".show_loading h2")
					.html("Posts generated by now " + data + '/' + max_iterations);

				if (numm_update == data) {
					jQuery('.show_loading').css("display", "none");
				} else {
					numm_update = data;
					if (max_iterations < data) {
						setTimeout("start_update(" + ids + ")", 500);
					}
				}
				if (max_iterations == data) {
					window.location.href = new_location;
				} else {
					location.reload(true);
				}
			}
		});
	}
	jQuery('#cb-select-all').click(function (e) {
		jQuery("input[type=checkbox]").prop('checked', jQuery(this).prop('checked'));
	});

	jQuery(document).ready(function ($) {
		$('#cb-select-all').click(function (e) {
			$('.project-checkbox').prop('checked', $(this).prop('checked'));
			updateDeleteButtonState();
		});

		$(document).on('change', '.project-checkbox', function () {
			updateSelectAllState();
			updateDeleteButtonState();
		});

		function updateSelectAllState() {
			var totalCheckboxes = $('.project-checkbox').length;
			var checkedCheckboxes = $('.project-checkbox:checked').length;

			$('#cb-select-all').prop('checked', totalCheckboxes === checkedCheckboxes);
			$('#cb-select-all').prop('indeterminate', checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes);
		}

		function updateDeleteButtonState() {
			var checkedCount = $('.project-checkbox:checked').length;
			if (checkedCount > 0) {
				$('#bulk-delete-btn').prop('disabled', false).text('Delete ' + checkedCount + ' Selected Project' + (checkedCount > 1 ? 's' : ''));
			} else {
				$('#bulk-delete-btn').prop('disabled', true).text('Delete Selected Projects');
			}
		}

		$('#bulk-delete-btn').click(function () {
			var checkedBoxes = $('.project-checkbox:checked');
			if (checkedBoxes.length === 0) {
				alert('Please select at least one project to delete.');
				return;
			}

			var projectNames = [];
			checkedBoxes.each(function () {
				var row = $(this).closest('tr');
				var projectName = row.find('h4').text();
				projectNames.push(projectName);
			});

			var confirmMessage = 'Are you sure you want to delete the following ' + checkedBoxes.length + ' project(s)?\n\n';
			confirmMessage += projectNames.join('\n');
			confirmMessage += '\n\nThis action cannot be undone.';

			if (confirm(confirmMessage)) {
				$('#bulk-delete-form').submit();
			}
		});

		updateDeleteButtonState();
	});
</script>

<!-- Building Post Preview -->
<?php

if (isset($_GET['post_preview']) && $_GET['post_preview'] == 'true' && isset($_GET['preview_id'])) {

	global $wpdb;
	$preview_id = intval($_GET['preview_id']);
	$preview_project = $wpdb->get_row($wpdb->prepare(
		"SELECT id, state, iteration, max_iterations FROM {$wpdb->prefix}improveseo_tasks WHERE id = %d AND state = 'Preview'",
		$preview_id
	));

	if ($preview_project && (int) $preview_project->iteration < (int) $preview_project->max_iterations) { ?>

		<script type="text/javascript">
			build_project(<?php echo (int) $preview_project->id ?>);
		</script>
		<?php
	}
}


if (isset($_GET['create_build_id'])) { ?>

	<script type='text/javascript'>
		build_project(<?= intval($_GET['create_build_id']) ?>);
	</script>

<?php } elseif (isset($_GET['build_posts_id'])) { ?>

	<script type='text/javascript'>
		update_project(<?= intval($_GET['build_posts_id']) ?>);
	</script>

<?php } ?>
<?php View::endSection('content') ?>
<?php View::make('layouts.main') ?>