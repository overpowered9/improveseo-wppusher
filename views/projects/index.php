<?php

use ImproveSEO\View;

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
<a href="<?= esc_url(admin_url('admin.php?page=improveseo_dashboard')) ?>"><?php esc_html_e('Improve SEO', 'improve-seo') ?></a>
&raquo;
<span><?php esc_html_e('Projects List', 'improve-seo') ?></span>
<?php View::endSection('breadcrumbs') ?>



<?php View::startSection('content') ?>

<?php View::render('import/import') ?>


<h1 class="hidden"><?php esc_html_e('Product Listing', 'improve-seo'); ?></h1>
<div class="show_loading alert-modal">
	<h1 class="hidden"><?php esc_html_e('Projects List', 'improve-seo'); ?></h1>
	<h2 id="mid_notice"></h2>
</div>

<div class="projectes improveseo_wrapper intro_page  p-3 p-lg-4">
	<section class="project-section border-bottom d-flex flex-row  justify-content-between align-items-center pb-2">
		<div class="project-heading d-flex flex-row">
			<img class="mr-2" src="<?php echo esc_url(IMPROVESEO_WT_URL . '/assets/images/project-list-logo.png'); ?>" alt="<?php esc_attr_e('ImproveSeo', 'improve-seo'); ?>">
			<h1><?php esc_html_e('Projects List', 'improve-seo'); ?></h1>
		</div>
		<div class="action-buttons">
			<a onclick="return confirm('<?php esc_attr_e('Are you sure you want to export all items?', 'improve-seo'); ?>');" href="<?= admin_url('admin.php?page=improveseo_projects&action=export_all_project&noheader=true') ?>" class="btn btn-outline-primary btn-small" id="exportProject"><?php esc_html_e('Export All Project', 'improve-seo'); ?></a>
			<a href="#" class="btn btn-outline-primary btn-small" id="importProject"><?php esc_html_e('Import', 'improve-seo'); ?></a>
			<a onclick="return confirm('<?php esc_attr_e('Please purchase the Pro version to access this feature and many more..', 'improve-seo'); ?>');" href="javascript:void()" class="btn btn-outline-primary btn-small" id="exportProject"><?php esc_html_e('Export All Project', 'improve-seo'); ?></a>
			<a href="javascript:void()" class="btn btn-outline-primary btn-small" onclick="return confirm('<?php esc_attr_e('Please purchase the Pro version to access this feature and many more..', 'improve-seo'); ?>');" id="importProject"><?php esc_html_e('Import', 'improve-seo'); ?></a>
			<a href="<?= admin_url('admin.php?page=improveseo_posting') ?>" class="btn btn-outline-primary btn-small" id="btn-add"><?php esc_html_e('Add New', 'improve-seo'); ?></a>
		</div>
	</section>
	<section class="pagination-wrapper text-right py-3">
		<span class="pagination-links">
			<?= paginate_links(array(
				'total' => $pages,
				'current' => $page,
				'format' => '&paged=%#%',
				'base' => admin_url('admin.php?page=improveseo_projects%_%')
			)) ?>
		</span>
	</section>

	<section class="project-table-wrapper">
		<form method="get">
			<?php
			$is_preview = 'no';
			if (isset($_GET['post_preview'])) {
				if ($_GET['post_preview'] == 'true') {
					$is_preview = 'yes';
				}
			} ?>
			<input type="hidden" name="is_preview_available" id="is_preview_available" value="<?php echo $is_preview; ?>" />

			<div class="table-responsive-sm">
				<div class="tablenav top">
					<div class="alignleft actions bulkactions">
						<label for="bulk-action-selector-top" class="screen-reader-text"><?php esc_html_e('Select bulk action', 'improve-seo'); ?></label>
						<select name="action" id="bulk-action-selector-top">
							<option value="bulk-empty"><?php esc_html_e('Bulk actions', 'improve-seo'); ?></option>
							<option value="bulk-delete-all" class="hide-if-no-js"><?php esc_html_e('Delete project and all posts/pages', 'improve-seo'); ?></option>
							<option value="bulk-delete-posts"><?php esc_html_e('Delete only posts/pages', 'improve-seo'); ?></option>
						</select>
						<input type="hidden" name="page" value="improveseo_projects" />
						<input type="hidden" name="noheader" value="true" />
						<input type="submit" id="doaction" class="del-btn btn btn-outline-danger button action" value="<?php esc_attr_e('Delete Projects', 'improve-seo'); ?>">
					</div>
					<div class="tablenav-pages one-page">
						<span class="displaying-num"><?php echo esc_html(count($projects)); ?> <?php esc_html_e('items', 'improve-seo'); ?></span>
					</div>
					<br class="clear">
				</div>


				<table class="table widefat fixed wp-list-table widefat fixed striped table-view-list posts">
					<thead>
						<tr>
							<th scope="col" class="manage-column column-cb">
								<label class="screen-reader-text" for="cb-select-all"><?php esc_html_e('Select All', 'your-text-domain'); ?></label>
								<input id="cb-select-all" type="checkbox">
							</th>
							<th scope="col" class="manage-column column-title column-primary" style="width: 26.66%"><?php esc_html_e('Name', 'your-text-domain'); ?></th>
							<th scope="col" class="manage-column"><?php esc_html_e('Created Posts', 'your-text-domain'); ?></th>
							<th scope="col" class="manage-column"><?php esc_html_e('Max Posts', 'your-text-domain'); ?></th>
							<th scope="col" class="manage-column"><?php esc_html_e('Created At', 'your-text-domain'); ?></th>
							<th scope="col" class="manage-column"><?php esc_html_e('Last Update', 'your-text-domain'); ?></th>
							<th scope="col" class="manage-column"><?php esc_html_e('Status', 'your-text-domain'); ?></th>
							<th scope="col" class="manage-column"></th>
						</tr>
					</thead>

					<tbody>
						<?php foreach ($projects as $project) : ?>
							<tr <?= $highlight == $project->id ? ' class="WHProject--highlight"' : '' ?>>
								<td><input id="cb-select-<?php echo $project->id; ?>" type="checkbox" name="project_ids[]" value="<?php echo $project->id; ?>"></td>
								<td scope="col" class="column-title column-primary has-row-actions">
									<strong>
										<a href="javascript:void(0)" class="primary"><?= esc_html($project->name) ?></a>
									</strong>
									<div class="row-actions">
										<span class="edit">
											<a class="ct-btn btn btn-outline-primary" href="<?= esc_url(admin_url("admin.php?page=improveseo_projects&action=export_urls&id={$project->id}&name={$project->name}&noheader=true")) ?>">
												<?= esc_html__('Export a list of all posts/pages URLs', 'your-text-domain') ?>
											</a>
										</span>
										<span class="edit">
											<a class="ct-btn btn btn-outline-primary" href="<?= esc_url(admin_url("admin.php?page=improveseo_dashboard&action=edit_post&id={$project->id}&update=true")) ?>">
												<?= esc_html__('Update posts', 'your-text-domain') ?>
											</a>
										</span>
										<span class="edit">
											<a class="ct-btn btn btn-outline-primary" href="<?= esc_url(admin_url('admin.php?page=improveseo_projects&action=duplicate&id=' . $project->id . '&noheader=true')) ?>">
												<?= esc_html__('Duplicate project', 'your-text-domain') ?>
											</a>
										</span>
										<span class="edit">
											<a class="ct-btn btn btn-outline-primary" href="<?= esc_url(admin_url('admin.php?page=improveseo_projects&action=stop&id=' . $project->id . '&noheader=true')) ?>">
												<?= esc_html__('Stop process', 'your-text-domain') ?>
											</a>
										</span>
										<span class="edit">
											<a class="ct-btn btn btn-outline-primary" onclick="return confirm('<?= esc_html__('Please purchase the Pro version to access this feature and many more..', 'your-text-domain') ?>');" href="javascript:void()">
												<?= esc_html__('Export Project', 'your-text-domain') ?>
											</a>
										</span>
										<span class="trash">
											<a class="del-btn btn btn-outline-danger" class="submitdelete" href="<?= esc_url(admin_url('admin.php?page=improveseo_projects&action=delete&id=' . $project->id . '&noheader=true')) ?>" onclick="return confirm('<?= esc_html__('This action will delete project and all generated posts/pages', 'your-text-domain') ?>')">
												<?= esc_html__('Delete project and all posts/pages', 'your-text-domain') ?>
											</a>
										</span>
										<span class="trash">
											<a class="del-btn btn btn-outline-danger" class="submitdelete" href="<?= esc_url(admin_url('admin.php?page=improveseo_projects&action=delete_posts&id=' . $project->id . '&noheader=true')) ?>" onclick="return confirm('<?= esc_html__('This action will delete all generated posts/pages', 'your-text-domain') ?>')">
												<?= esc_html__('Delete only posts/pages', 'your-text-domain') ?>
											</a>
										</span>
									</div>
									<button type="button" class="toggle-row"><span class="screen-reader-text"><?= esc_html__('Show more details', 'your-text-domain') ?></span></button>
								</td>

								<td scope="col" data-colname="<?php esc_attr_e('Created Posts', 'improve-seo'); ?>"><?php echo esc_html($project->iteration); ?></td>
								<td scope="col" data-colname="<?php esc_attr_e('Max Posts', 'improve-seo'); ?>"><?php echo esc_html($project->max_iterations); ?></td>
								<td scope="col" data-colname="<?php esc_attr_e('Created At', 'improve-seo'); ?>">
									<?php
									$date = new DateTime($project->created_at);
									echo esc_html($date->format('d/m/Y H:i:s'));
									?>
								</td>
								<td scope="col" data-colname="<?php esc_attr_e('Last Update', 'improve-seo'); ?>">
									<?php
									$date = new DateTime($project->updated_at);
									echo esc_html($date->format('d/m/Y H:i:s'));
									?>
								</td>
								<td scope="col" data-colname="<?php esc_attr_e('Status', 'improve-seo'); ?>">
									<strong>
										<?php
										if ($project->state == 'Draft') {
											esc_html_e('Draft', 'improve-seo');
										} else {
											if ($project->iteration >= $project->max_iterations) {
												echo '<p class="post-fd">';
												esc_html_e('Finished', 'improve-seo');
												echo '</p>';
											} else {
												$updated = strtotime($project->updated_at);
												if ($project->deleted_at == '1970-01-01 11:11:11') {
													echo '<p class="post-st">';
													esc_html_e('Stopped', 'improve-seo');
													echo '</p>';
												} elseif (time() - $updated > 1200) {
													echo '<p class="post-pd">';
													esc_html_e('Paused', 'improve-seo');
													echo '</p>';
												} else {
													esc_html_e('Processing', 'improve-seo');
												}
											}
										}
										?>
									</strong>
								</td>
								<td scope="col" data-colname="<?php esc_attr_e('Actions', 'improve-seo'); ?>" class="actions-btn">
									<?php if ($project->state == 'Published' && $project->iteration < $project->max_iterations) : ?>
										<a href="javascript:build_project(<?php echo esc_attr($project->id); ?>)" class="btn btn-primary" target="_self">
											<?php esc_html_e('Build posts', 'improve-seo'); ?>
										</a>
									<?php endif; ?>
									<?php if ($project->state == 'Updated' && $project->iteration < $project->max_iterations) : ?>
										<a href="javascript:update_project(<?php echo esc_attr($project->id); ?>)" class="btn btn-outline-primary" target="_self">
											<?php esc_html_e('Update posts', 'improve-seo'); ?>
										</a>
									<?php endif; ?>
									<input type="hidden" name="max-iterations" id="max-iterations" data-project="<?php echo esc_attr($project->id); ?>" value="<?php echo esc_attr($project->max_iterations); ?>" />
									<?php if ($project->state == 'Draft') : ?>
										<a href="<?php echo esc_url(admin_url('admin.php?page=improveseo_dashboard&action=edit_post&id=' . $project->id)); ?>" class="btn btn-outline-primary">
											<?php esc_html_e('Continue', 'improve-seo'); ?>
										</a>
									<?php endif; ?>
								</td>

							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</form>
	</section>

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
					success: function(data) {
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
								setTimeout(function() {
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
				success: function(data) {
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
		jQuery('#cb-select-all').click(function(e) {
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
				/* header("Location:".$export_url);
				exit; */
			}
		}
	}


	if (isset($_GET['build_posts_id'])) { ?>

		<script type='text/javascript'>
			update_project(<?= $_GET['build_posts_id'] ?>);
		</script>

	<?php } ?>

	<section class="pagination-wrapper text-right">
		<span class="pagination-links">
			<?= paginate_links(array(
				'total' => $pages,
				'current' => $page,
				'format' => '&paged=%#%',
				'base' => admin_url('admin.php?page=improveseo_projects%_%')
			)) ?>
		</span>
	</section>
</div>
<?php View::endSection('content') ?>
<?php View::make('layouts.main') ?>