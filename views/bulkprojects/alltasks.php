<?php

use ImproveSEO\View;

?>

<?php View::startSection('breadcrumbs') ?>

<a href="<?= admin_url('admin.php?page=improveseo_dashboard') ?>">Improve SEO</a>

&raquo;

<a href="<?= admin_url('admin.php?page=improveseo_bulkprojects') ?>">Bulk Projects List</a>

&raquo;

<span><?php echo esc_html($project_name); ?></span>

<?php

if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
	$url = "https://";
else
	$url = "http://";

// Append the host(domain name, ip) to the URL.   
$url .= $_SERVER['HTTP_HOST'];

// Append the requested resource location to the URL   
$url .= $_SERVER['REQUEST_URI'];

?>

<?php View::endSection('breadcrumbs') ?>

<?php View::startSection('content') ?>

<?php View::render('import/import') ?>

<h1 class="hidden">All Keywords of <?php echo $project_name; ?></h1>
<div class="show_loading alert-modal">
	<h1 class="hidden">All Keywords List</h1>
	<h2 id="mid_notice"></h2>

</div>

<div class="global-wrap">

	<div class="head-bar">
		<img src="<?php echo WT_URL . '/assets/images/latest-images/seo-latest-logo.svg'; ?>" alt="project-list-logo">
		<h1> ImproveSEO | 2.0.11 </h1>
	</div>
	<div class="box-top">
		<ul class="breadcrumb-seo">
			<li><a href="<?= admin_url('admin.php?page=improveseo_bulkprojects') ?>">Improve SEO</a></li>
			<li><a href="<?= admin_url('admin.php?page=improveseo_bulkprojects') ?>">Bulk Projects</a></li>
			<li><?php echo esc_html($project_name); ?></li>
		</ul>
	</div>
	<?php $search = isset($search) ? $search : ''; ?>
	<div class="iseo-search-sort-row">
		<form method="GET" action="" class="iseo-search-form">
			<input type="hidden" name="page" value="improveseo_bulkprojects">
			<input type="hidden" name="action" value="viewAllTasks">
			<input type="hidden" name="id" value="<?= esc_attr($id) ?>">
			<input type="text" name="search" value="<?= esc_attr($search) ?>"
				class="iseo-search-input" placeholder="Search Here">
			<button type="submit" class="iseo-search-btn">
				<img src="<?php echo WT_URL . '/assets/images/latest-images/clarity_search-line.svg' ?>" alt="search">
			</button>
		</form>
		<?php if ($search): ?>
			<a href="<?= admin_url('admin.php?page=improveseo_bulkprojects&action=viewAllTasks&id=' . esc_attr($id)) ?>" class="iseo-clear-btn">&#x2715; Clear</a>
		<?php endif; ?>
		<?php
		$_atsbase        = admin_url('admin.php?page=improveseo_bulkprojects&action=viewAllTasks&id=' . $id . '&paged=1' . ($search ? '&search=' . urlencode($search) : ''));
		$_atkw_order     = ($orderBy === 'keyword_name' && $order === 'ASC') ? 'DESC' : 'ASC';
		$_atdate_order   = ($orderBy === 'created_at'   && $order === 'ASC') ? 'DESC' : 'ASC';
		$_atstatus_order = ($orderBy === 'status'       && $order === 'ASC') ? 'DESC' : 'ASC';
		$_atkw_arrow     = $orderBy === 'keyword_name' ? ($order === 'ASC' ? ' ↑' : ' ↓') : '';
		$_atdate_arrow   = $orderBy === 'created_at'   ? ($order === 'ASC' ? ' ↑' : ' ↓') : '';
		$_atstatus_arrow = $orderBy === 'status'       ? ($order === 'ASC' ? ' ↑' : ' ↓') : '';
		?>
		<div class="iseo-sort-controls">
			<span class="iseo-sort-label">Sort by</span>
			<a href="<?= esc_url($_atsbase . '&orderBy=keyword_name&order=' . $_atkw_order) ?>"
				class="iseo-sort-pill<?= $orderBy === 'keyword_name' ? ' iseo-sort-on' : '' ?>">Keyword<?= $_atkw_arrow ?></a>
			<a href="<?= esc_url($_atsbase . '&orderBy=created_at&order=' . $_atdate_order) ?>"
				class="iseo-sort-pill<?= $orderBy === 'created_at' ? ' iseo-sort-on' : '' ?>">Date<?= $_atdate_arrow ?></a>
			<a href="<?= esc_url($_atsbase . '&orderBy=status&order=' . $_atstatus_order) ?>"
				class="iseo-sort-pill<?= $orderBy === 'status' ? ' iseo-sort-on' : '' ?>">Status<?= $_atstatus_arrow ?></a>
		</div>
		<div class="import-export-btn">
			<button type="button" onclick="window.location.href='<?= admin_url('admin.php?page=improveseo_posting&action=create_post_bulk') ?>';"
				class="active">Add New Project</button>
		</div>
	</div>
	<?php
	$_atbase = admin_url('admin.php?page=improveseo_bulkprojects&action=viewAllTasks&id=' . $id . ($search ? '&search=' . urlencode($search) : '') . ($orderBy !== 'created_at' ? '&orderBy=' . urlencode($orderBy) : '') . ($order !== 'DESC' ? '&order=' . urlencode($order) : ''));
	$is_preview = isset($_GET['post_preview']) && $_GET['post_preview'] == 'true' ? 'yes' : 'no';
	?>
	<form method="post">
		<input type="hidden" name="is_preview_available" id="is_preview_available" value="<?php echo $is_preview; ?>" />
		<?php wp_nonce_field('bulk_delete_tasks', 'bulk_delete_nonce'); ?>
		<div class="actions">
			<div>
				<input type="hidden" name="page" value="improveseo_bulkprojects" />
				<input type="hidden" name="noheader" value="true" />
				<input type="hidden" name="main_id" value="<?php echo esc_attr($_GET['id']); ?>" />
				<input type="hidden" value="bulk-delete-tasks" name="action">
				<button type="submit" id="doaction" class="btn_delete action" disabled style="opacity: 0.5;">Delete Selected Posts</button>
			</div>
			<div class="pagination">
				<?php if ($page > 1): ?>
					<button type="button" class="prev pagination-btn"
						onclick="window.location.href='<?= esc_js($_atbase . '&paged=' . ($page - 1) . ($highlight ? '&highlight=' . $highlight : '')) ?>'">
						&lt; Prev
					</button>
				<?php else: ?>
					<button type="button" class="prev pagination-btn" disabled style="opacity: 0.5; cursor: not-allowed;">
						&lt; Prev
					</button>
				<?php endif; ?>
				<?php for ($i = 1; $i <= $pages; $i++): ?>
					<?php if ($i == $page): ?>
						<button type="button" class="active"><?= $i ?></button>
					<?php else: ?>
						<button type="button"
							onclick="window.location.href='<?= esc_js($_atbase . '&paged=' . $i . ($highlight ? '&highlight=' . $highlight : '')) ?>'"><?= $i ?></button>
					<?php endif; ?>
				<?php endfor; ?>
				<?php if ($page < $pages): ?>
					<button type="button" class="next pagination-btn"
						onclick="window.location.href='<?= esc_js($_atbase . '&paged=' . ($page + 1) . ($highlight ? '&highlight=' . $highlight : '')) ?>'">
						Next &gt;
					</button>
				<?php else: ?>
					<button type="button" class="next pagination-btn" disabled style="opacity: 0.5; cursor: not-allowed;">
						Next &gt;
					</button>
				<?php endif; ?>
			</div>
			<div class="import-export">
				<p><?= $total ?> Items</p>
			</div>
			<div class="import-refrsh-seo">
				<button type="button" class="toggle-row pull-right" onclick="return refreshPage()"> Refresh List
				</button>
			</div>
		</div>
		<?php function generate_slug_replace_quotes($title)
		{
			$slug = strtolower($title); // Convert to lowercase
			$slug = str_replace('"', '-', $slug); // Replace double quotes with "-"
			$slug = preg_replace('/[^a-z0-9\s-]/', '', $slug); // Remove other special characters
			$slug = preg_replace('/[\s-]+/', ' ', $slug); // Replace multiple spaces or hyphens
			$slug = trim($slug); // Trim leading/trailing spaces
			$slug = str_replace(' ', '-', $slug); // Replace spaces with hyphens
			return $slug;
		}
		?>
		<div class="improve-seo-container">
			<div class="project-lists">
				<div style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-left: 4px solid #0073aa;">
					<h2 style="margin: 0; font-size: 18px; color: #23282d;">
						Bulk Project: <strong><?php echo esc_html($project_name); ?></strong>
					</h2>
					<p style="margin: 5px 0 0 0; color: #666; font-size: 14px;">
						Viewing all posts/pages for this bulk project
					</p>
				</div>
				<div class="table-responsive">
					<table class="table ">
						<thead>
							<tr>
								<th>
									<label class="checkbox style-c">
										<input id="cb-select-all" type="checkbox">
										<div class="checkbox__checkmark"></div>
									</label>
									<h4> Keyword Name </h4>
								</th>
								<th>Language</th>
								<th>Size</th>
								<th>Processing</th>
								<th>Publish Date</th>
								<th>Post Status</th>
								<th> </th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($projects as $key => $project): ?>
								<tr <?= $highlight == $project->id ? ' class="WHProject--highlight"' : '' ?>>
									<td data-label="Name" style="vertical-align: middle; padding: 15px 10px;">
										<div style="display: flex; align-items: flex-start; gap: 0px;">
											<label class="checkbox style-c" style="margin: 0;">
												<input id="cb-select-<?php echo $project->id; ?>" type="checkbox"
													name="project_ids[]" value="<?php echo $project->id; ?>">
												<div class="checkbox__checkmark"></div>
											</label>
											<h4 style="margin: 0; word-break: break-word; white-space: pre-line;"> <?= $project->keyword_name ?> </h4>
										</div>
									</td>
									<td data-label="Language"><?= $project->content_lang ?></td>
									<td data-label="Size"><?= $project->nos_of_words ?></td>
									<td data-label="Processing" class="status finished"><?php
									if ($project->status == 'Processing') {
										echo 'Generating';
									} else if ($project->status == 'Done') {
										echo 'Completed';
								} else if ($project->status == 'Stoped') {
									echo '<span style="color: #ff4d4f; font-weight: 600;">Canceled</span>';
									} else {
										echo 'Queued';
									}
									?> </td>
									<td data-label="Publish Date"> <?php
									if ($project->status == 'Stoped' || $project->published_on == '0000-00-00 00:00:00' || empty($project->published_on)) {
										echo 'N/A';
									} else {
										echo esc_html(date('m/d/Y H:i:s', strtotime($project->published_on)));
									}
									?> </td>
									<td data-label="Post Status" class="status paused">									
										<?php if($project->status == 'Stoped') {
											echo '<span style="color: #ff4d4f; font-weight: 600;">Canceled</span>';
										} else if($project->status != 'Done') {
											echo 'Pending Creation';
										} else if($project->state == 'Published' && $project->post_id) {
											$iseo_post_url = get_permalink($project->post_id);
											if ($iseo_post_url) {
												echo '<a href="' . esc_url($iseo_post_url) . '" target="_blank" rel="noopener" title="View published post">Published</a>';
											} else {
												echo 'Published';
											}
										} else if($project->state == 'Draft') {
											echo 'Draft';
										} else if($project->state == 'Scheduled') {
											echo 'Scheduled';
										} else {
											echo 'Pending Creation';
										}
										?>
									</td>
									<td scope="col" data-label="Action" class="actions-btn" style="width: 4%;">
										<a href="#" class="action-btn-pop"> <img
												src="<?php echo WT_URL . '/assets/images/latest-images/ri_more-2-fill.svg' ?>"
												alt="ri_more-2-fill"> </a>
										<div class="actionpopup">
											<div class="popup-arrow"></div>
											<ul class="popup-menu">
												<div class="row-actions"
													style="display: flex; flex-direction: column !important;">
											<?php if ($project->status != 'Done' && $project->status != 'Stoped') { ?>
												<span class="edit">
													<?php $task_id = $_GET['id']; ?>
													<a class="popup-link"
														href="<?= admin_url('admin.php?page=improveseo_bulkprojects&action=stop&mainid=' . $task_id . '&id=' . $project->id) ?>"
														onclick="return confirm('Are you sure you want to cancel this task? Content generation will be halted.')">
														Cancel Process
													</a>
												</span>
											<?php }
													if ($project->state == 'Draft') {
														$task_id = $_GET['id']; ?>
														<span class="primary">
															<a class="popup-link"
																href="<?= admin_url('admin.php?page=improveseo_bulkprojects&action=publish&mainid=' . $task_id . '&id=' . $project->id) ?>">
																Publish
														</a>
													</span>
												<?php }
												if ($project->status != 'Stoped') { ?>
													<span class="primary">
														<a href="javascript:re_generatepost(<?= $project->id ?>)"
															class="popup-link" target="_self"
															onclick="return confirm('This will delete the existing content and regenerate from scratch. Continue?')">Re-Generate Content</a>
													</span>
												<?php } ?>
													<?php // "View AI content" is only useful for Drafts (to review before publishing).
													// Published/Scheduled posts are viewed via "View Post" instead.
													if ($project->state == 'Draft' && !empty($project->ai_content)) { ?>
														<span class="primary">
															<a class="popup-link" class="submitdelete" target="_blank"
																href="<?= admin_url('admin.php?page=improveseo_bulkprojects&action=viewAiContent&id=' . $project->id) ?>">View AI
																content</a>
														</span>
													<?php } ?>

													<span class="primary">
														<a class="popup-link"
															href="<?= admin_url('admin.php?page=improveseo_bulkprojects&action=view_task_details&id=' . $project->id . '&parent_id=' . $id) ?>">View
															Details</a>
													</span>

													<?php // View Post is enabled for both Published and Scheduled posts
													// (a scheduled post already has a permalink to preview).
													$iseo_live_url = !empty($project->post_id) ? get_permalink($project->post_id) : '';
													if (($project->state == 'Published' || $project->state == 'Scheduled') && $iseo_live_url) { ?>
														<span class="primary">
															<a class="popup-link" target="_blank" rel="noopener"
																href="<?php echo esc_url($iseo_live_url); ?>">View Post</a>
														</span>
													<?php } ?>
													<?php if (!empty($project->post_id)) {
														$edit_link = admin_url('post.php?action=edit&post=' . $project->post_id); ?>
														<span class="primary">
															<a class="popup-link" class="submitdelete" target="_blank"
																href="<?php echo $edit_link; ?>">Edit
																Post Content</a>
														</span>
													<?php } else { ?>
														<span class="primary">
															<a class="popup-link" class="submitdelete" target="_blank" href="#"
																onclick="alert('Post is not generated yet. Please wait'); return false;">Edit
																Post Content</a>
														</span>
													<?php } ?>
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
</div>
<script>
	function re_generatepost(id) {
		jQuery('.show_loading').css("display", "block");
		jQuery(".show_loading h2").html("Post is re-generating, Please wait ...<br><strong style='color: #d63638; margin-top: 10px; display: inline-block;'>Do not close this page!</strong>");
		re_generate(id);
	}

	function re_generate(ids) {
		jQuery
			.ajax({
				url: "<?php echo admin_url("admin-ajax.php"); ?>",
				data: ({
					action: 're_generate_post',
					id: ids
				}),
				success: function (data) {
					console.log(data);
					jQuery('.show_loading').css("display", "none");
					alert("Content has been Re-Generated successfully.");
					location.reload(true);
				},
				error: function(xhr, status, error) {
					jQuery('.show_loading').css("display", "none");
					alert("Failed to regenerate content. Please try again.");
					console.error(error);
				}
			});
	}

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
	
	function refreshPage() {
		location.reload();
		return false;
	}
	
	jQuery(document).ready(function ($) {
		$('#cb-select-all').on('change', function () {
			var isChecked = $(this).prop('checked');
			$('input[name="project_ids[]"]').prop('checked', isChecked);
			updateDeleteButtonState();
		});

		$('input[name="project_ids[]"]').on('change', function () {
			var totalCheckboxes = $('input[name="project_ids[]"]').length;
			var checkedCheckboxes = $('input[name="project_ids[]"]:checked').length;

			if (checkedCheckboxes === 0) {
				$('#cb-select-all').prop('indeterminate', false).prop('checked', false);
			} else if (checkedCheckboxes === totalCheckboxes) {
				$('#cb-select-all').prop('indeterminate', false).prop('checked', true);
			} else {
				$('#cb-select-all').prop('indeterminate', true);
			}
			updateDeleteButtonState();
		});

		function updateDeleteButtonState() {
			var checkedCount = $('input[name="project_ids[]"]:checked').length;
			if (checkedCount > 0) {
				$('#doaction').prop('disabled', false).css('opacity', '1').text('Delete ' + checkedCount + ' Selected Post' + (checkedCount > 1 ? 's' : ''));
			} else {
				$('#doaction').prop('disabled', true).css('opacity', '0.5').text('Delete Selected Posts');
			}
		}

		updateDeleteButtonState();

		$('#doaction').on('click', function (e) {
			var checkedItems = $('input[name="project_ids[]"]:checked').length;
			if (checkedItems === 0) {
				e.preventDefault();
				alert('Please select at least one task to delete.');
				return false;
			}

			return confirm('Are you sure you want to delete ' + checkedItems + ' selected post(s) and their associated data? This action cannot be undone.');
		});
	});
</script>

<?php

if (isset($_GET['post_preview'])) {
	if ($_GET['post_preview'] == 'true') {
		$project = $projects[0];
		if ($project->state == 'Published' && $project->iteration < $project->max_iterations) { ?>
			<script type="text/javascript">
				build_project(<?php echo $project->id ?>);
			</script>
			<?php
		} elseif ($project->state == 'Published' && $project->iteration == $project->max_iterations) {
			$export_url = admin_url("admin.php?page=improveseo_projects&action=export_preview_url&id={$project->id}&noheader=true");
		}
	}
}

if (isset($_GET['build_posts_id'])) { ?>
	<script type='text/javascript'>
		update_project(<?= $_GET['build_posts_id'] ?>);
	</script>
<?php } ?>
<?php View::endSection('content') ?>
<?php View::make('layouts.main') ?>