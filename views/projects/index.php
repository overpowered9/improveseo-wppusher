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
<a href="<?= admin_url('admin.php?page=improveseo_dashboard') ?>">Improve SEO</a>
&raquo;
<span>Projects List</span>
<?php View::endSection('breadcrumbs') ?>


<?php View::startSection('content') ?>

<?php View::render('import/import') ?>


<h1 class="hidden">Product Listing</h1>
<div class="show_loading alert-modal">
	<h1 class="hidden">Projects List</h1>
	<h2 id="mid_notice"></h2>
</div>
<div class="projectes improveseo_wrapper intro_page  p-3 p-lg-4">
	<section class="project-section border-bottom d-flex flex-row  justify-content-between align-items-center pb-2">
		<div class="project-heading d-flex flex-row">
			<img class="mr-2" src="<?= IMPROVESEO_WT_URL . '/assets/images/project-list-logo.png' ?>" alt="ImproveSeo">
			<h1>Projects List</h1>
		</div>
		<div class="action-buttons">
			<a href="<?= admin_url('admin.php?page=improveseo_posting') ?>" class="btn btn-outline-primary btn-small" id="btn-add">Add New</a>
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
			<input type="hidden" name="is_preview_available" id="is_preview_available" value="<?= $is_preview; ?>" />

			<div class="table-responsive-sm">
				<div class="tablenav top">
					<div class="alignleft actions bulkactions">
						<label for="bulk-action-selector-top" class="screen-reader-text">Select bulk action</label>
						<select name="action" id="bulk-action-selector-top">
							<option value="">Bulk actions</option>
							<option value="bulk-delete-all" class="hide-if-no-js">Delete project and all posts/pages</option>
							<option value="bulk-delete-posts">Delete only posts/pages</option>

						</select>
						<input type="hidden" name="page" value="improveseo_projects" />
						<input type="hidden" name="noheader" value="true" />
						<input type="submit" id="doaction" class="del-btn btn btn-outline-danger button action" value="Delete Projects">
					</div>
					<div class="tablenav-pages one-page"><span class="displaying-num"><?= count($projects); ?> items</span></div>
					<br class="clear">
				</div>
				<table class="table widefat fixed wp-list-table widefat fixed striped table-view-list posts">
					<thead>
						<tr>
							<th scope="col" class="manage-column column-cb">
								<label class="screen-reader-text" for="cb-select-all">Select All</label>
								<input id="cb-select-all" type="checkbox">
							</th>
							<th scope="col" class="manage-column column-title column-primary" style="width: 26.66%">Name</th>
							<th scope="col" class="manage-column">Created Posts</th>
							<th scope="col" class="manage-column">Max Posts</th>
							<th scope="col" class="manage-column">Created At</th>
							<th scope="col" class="manage-column">Last Update</th>
							<th scope="col" class="manage-column">Status</th>
							<th scope="col" class="manage-column"></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($projects as $project) : ?>
							<!-- extract categories  -->
							<?php
								$cateJson = $project->cats; 
								$cateArray = json_decode($cateJson, true); 
								$categories = implode(",", $cateArray) ?? '';
							?>
							<tr <?= $highlight == $project->id ? ' class="WHProject--highlight"' : '' ?>>
								<td><input id="cb-select-<?= $project->id; ?>" type="checkbox" name="project_ids[]" value="<?= $project->id; ?>"></td>
								<td scope="col" class="column-title column-primary has-row-actions">
									<strong>
										<a href="javascript:void(0)" class="primary"><?= $project->name ?></a>
									</strong>
									<div class="row-actions">

										<span class="edit">
											<a class="ct-btn btn btn-outline-primary" href="<?= admin_url("admin.php?page=improveseo_projects&action=export_urls&id={$project->id}&name={$project->name}&noheader=true") ?>">
												Export a list of all posts/pages URLs
											</a>
										</span>

										<span class="edit">
											<a class="ct-btn btn btn-outline-primary" href="<?= admin_url("admin.php?page=improveseo_dashboard&action=edit_post&id={$project->id}&update=true&cat_pre=").$categories ?>">
												Update posts
											</a>
										</span>

										<span class="edit">
											<a class="ct-btn btn btn-outline-primary" href="<?= admin_url('admin.php?page=improveseo_projects&action=duplicate&id=' . $project->id . '&noheader=true') ?>">
												Duplicate project
											</a>
										</span>

										<span class="edit">
											<a class="ct-btn btn btn-outline-primary" href="<?= admin_url('admin.php?page=improveseo_projects&action=stop&id=' . $project->id . '&noheader=true') ?>">
												Stop process
											</a>
										</span>

										<span class="edit">
											<a class="ct-btn btn btn-outline-primary" href="<?= admin_url("admin.php?page=improveseo_projects&action=export_project&id={$project->id}&name={$project->name}&noheader=true") ?>">
												Export Project
											</a>
										</span>

										<span class="trash">
											<a class="del-btn btn btn-outline-danger" class="submitdelete" href="<?= admin_url('admin.php?page=improveseo_projects&action=delete&id=' . $project->id . '&noheader=true') ?>" onclick="return confirm('This action will delete project and all generated posts/pages')">Delete project and all posts/pages</a>
										</span>

										<span class="trash">
											<a class="del-btn btn btn-outline-danger" class="submitdelete" href="<?= admin_url('admin.php?page=improveseo_projects&action=delete_posts&id=' . $project->id . '&noheader=true') ?>" onclick="return confirm('This action will delete all generated posts/pages')">Delete only posts/pages</a>
										</span>
									</div>
									<button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button>
								</td>
								<td scope="col" data-colname="Created Posts"><?= $project->iteration ?></td>
								<td scope="col" data-colname="Max Posts"><?= $project->max_iterations ?></td>
								<td scope="col" data-colname="Created At">
									<?php
									$date = new DateTime($project->created_at);
									echo $date->format('d/m/Y H:i:s');
									?>
								</td>
								<td scope="col" data-colname="Last Update">
									<?php
									$date = new DateTime($project->updated_at);
									echo $date->format('d/m/Y H:i:s');
									?>
								</td>
								<td scope="col" data-colname="Status">
									<strong>
										<?php
										if ($project->state == 'Draft') echo 'Draft';
										else {
											if ($project->iteration >= $project->max_iterations) echo '<p class="post-fd">Finished</p>';
											else {
												$updated = strtotime($project->updated_at);
												if ($project->deleted_at == '1970-01-01 11:11:11') echo '<p class="post-st">Stopped</p>';
												elseif (time() - $updated > 1200) echo '<p class="post-pd">Paused</p>';
												else echo 'Processing';
											}
										}
										?>
									</strong>
								</td>
								<td scope="col" data-colname="Actions" class="actions-btn">
									<?php if ($project->state == 'Published' && $project->iteration < $project->max_iterations) : ?>
										<button class="btn btn-primary build-project" data-id="<?=$project->id?>">Build posts</button>
									<?php endif; ?>
									<?php if ($project->state == 'Updated' && $project->iteration < $project->max_iterations) : ?>
										<button class="btn btn-primary update-project" data-id="<?=$project->id?>">Update posts</button>
									<?php endif; ?>
									<input type="hidden" name="max-iterations" id="max-iterations" data-project="<?= $project->id; ?>" value="<?= $project->max_iterations; ?>" />
									<?php if ($project->state == 'Draft') : ?>
										<a href="<?= admin_url('admin.php?page=improveseo_dashboard&action=edit_post&id=' . $project->id) ?>" class="btn btn-outline-primary">Continue</a>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</form>
	</section>


	<!-- Building Post Preview -->
	<?php

	if (isset($_GET['post_preview'])) {

		if ($_GET['post_preview'] == 'true') {

			$project = $projects[0];
			if ($project->state == 'Published' && $project->iteration < $project->max_iterations) { ?>

				<script type="text/javascript">
					build_project(<?= $project->id ?>);
				</script>
		<?php
			} elseif ($project->state == 'Published' && $project->iteration == $project->max_iterations) {
				$export_url = admin_url("admin.php?page=improveseo_projects&action=export_preview_url&id={$project->id}&noheader=true");
				header("Location:" . $export_url);
				exit;
			}
		}
	}

	?>
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