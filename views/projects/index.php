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

if (isset($_GET['post_preview'])) {
	if ($_GET['post_preview'] == 'true') {
		$project = $projects[0];
		if ($project->state == 'Published' && $project->iteration == $project->max_iterations) {
			$export_url = admin_url("admin.php?page=improveseo_projects&action=export_preview_url&id={$project->id}&noheader=true");
			header("Location:" . $export_url);
			exit;
		}
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
		<img src="<?php echo WT_URL . '/assets/images/latest-images/seo-latest-logo.svg' ?>" alt="project-list-logo">
		<h1> ImproveSEO | 2.0.11 </h1>
		<span>Pro</span>
	</div>
	<div class="box-top">
		<ul class="breadcrumb-seo">
			<li><a href="<?= admin_url('admin.php?page=improveseo_dashboard') ?>">Improve SEO</a></li>
			<li>Projects List</li>
		</ul>
		<div class="import-export-btn">
			<button onclick="window.location.href='<?= admin_url('admin.php?page=improveseo_posting') ?>';"
				class="active">Add New</button>
		</div>
	</div>
	<div class="actions">
		<div>
			<button class="btn_delete">Delete Selected Projects</button>
		</div>
		<div class="pagination">
			<button class="prev pagination-btn">
				< Prev </button>
					<button class="active">1</button>
					<button>2</button>
					<button>3</button>
					<button>4</button>
					<button>5</button>
					<button class="next pagination-btn"> Next ></button>
		</div>
		<div class="import-export">
			<p><?php echo count($projects); ?> Items</p>
		</div>
	</div>
	<div class="improve-seo-container">
		<div class="project-lists ">
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
							<th>Created At</th>
							<th>Last Update</th>
							<th>Status</th>
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
												name="project_ids[]" value="<?php echo $project->id; ?>">
											<div class="checkbox__checkmark"></div>
										</label>
										<h4><?= $project->name ?></h4>
									</div>
								</td>
								<td data-label="Created At"><?php
								$date = new DateTime($project->created_at);
								echo $date->format('d/m/Y H:i:s');
								?></td>
								<td data-label="Last Update"><?php
								$date = new DateTime($project->updated_at);
								echo $date->format('d/m/Y H:i:s');
								?></td>
								<td data-label="Status" class="status finished">

									<?php
									if ($project->state == 'Draft')
										echo 'Draft';
									else {
										if ($project->iteration >= $project->max_iterations)
											echo '<p class="post-fd">Finished</p>';
										else {
											$updated = strtotime($project->updated_at);
											if ($project->deleted_at == '1970-01-01 11:11:11')
												echo '<p class="post-fd">Stopped</p>';
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
											class="styling_post_page_action_buttons for_width_only_" target="_self">Build
											posts</a>
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
											class="styling_post_page_action_buttons">Continue</a>
									<?php endif; ?>
								</td>
								<td style="width: 4%;" scope="col" data-label="Action" class="actions-btn">

									<a href="#" class="action-btn-pop"> <img
											src="<?php echo WT_URL . '/assets/images/latest-images/ri_more-2-fill.svg' ?>"
											alt="ri_more-2-fill"> </a>

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
											<li><a href="#" target="_blank" style="max-width: max-content !important;"
													class="popup-link">Project Details</a></li>
											<li><a target="_blank"
													href="<?= admin_url("admin.php?page=improveseo_dashboard&action=edit_post&id={$project->id}&update=true") ?>"
													style="max-width: max-content !important;" class="popup-link">Edit
													Post</a>
											</li>
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
			<div class="pagination">
				<button class="prev pagination-btn">
					< Prev </button>
						<button class="active">1</button>
						<button>2</button>
						<button>3</button>
						<button>4</button>
						<button>5</button>
						<button class="next pagination-btn"> Next ></button>
			</div>
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
</script>

<!-- Building Post Preview -->
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