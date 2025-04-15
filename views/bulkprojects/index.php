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
			<!-- <button>Export all Projects</button>
			<button>Import</button> -->
			<button class="active"> Add New </button>
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
		<div class="project-lists">
			<table>
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
						<th> </th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($projects as $project): ?>
						<tr <?= $highlight == $project->id ? ' class="WHProject--highlight"' : '' ?>>
							<td data-label="Name"style="width: 28%;">
								<div style="display: flex; width: 100%; flex-wrap: nowrap; padding: 30px 0px; overflow-wrap: break-word;">
									<label class="checkbox style-c">
										<input id="cb-select-<?php echo $project->id; ?>" type="checkbox"
											name="project_ids[]" value="<?php echo $project->id; ?>">
										<div class="checkbox__checkmark"></div>
									</label>

									<h4> <?= $project->name ?> </h4>
								</div>
							</td>
							<td data-label="Post Count" style="text-align:center;"><?= $project->number_of_tasks ?></td>
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
							if ($project->state == 'Draft')
								echo 'Draft';
							else {
								if ($project->number_of_tasks == $project->number_of_completed_task)
									echo '<p class="post-fd">Finished</p>';
								else {
									$updated = strtotime($project->updated_at);
									if (!empty($project->deleted_at) && ($project->deleted_at == '1970-01-01 11:11:11'))
										echo '<p class="post-st">Stopped</p>';
									//elseif (time() - $updated > 1200) echo '<p class="post-pd">Paused</p>';
									else
										echo 'Processing';
								}
							}
							?></td>
							<td scope="col" data-colname="Actions" class="actions-btn" style="width: 4%;">

								<a href="#" class="action-btn-pop"> <img
										src="<?php echo WT_URL . '/assets/images/latest-images/ri_more-2-fill.svg' ?>"
										alt="ri_more-2-fill"> </a>
								<div class="actionpopup">
									<div class="popup-arrow"></div>
									<ul class="popup-menu">
										<div class="row-actions" style="display: flex; flex-direction: column !important;">

											<span class="edit">
												<a class="popup-link"
													href="<?php /*admin_url("admin.php?page=improveseo_projects&action=export_urls&id={$project->id}&name={$project->name}&noheader=true")*/ ?>"
													disabled>
													Export a list of all posts/pages URLs
												</a>
											</span>
											<span class="edit">
												<a class="popup-link"
													href="<?php /*admin_url('admin.php?page=improveseo_projects&action=stop&id=' . $project->id . '&noheader=true') */ ?>">
													Stop process
												</a>
											</span>
											<span class="edit">
												<a class="popup-link" class="submitdelete" target="_blank"
													href="<?= admin_url('admin.php?page=improveseo_bulkprojects&action=viewAllTasks&id=' . $project->id) ?>">View
													all AI posts/pages</a>
											</span>
											<span class="trash">
												<a class="popup-link delete-link" class="submitdelete"
													href="<?php /* admin_url('admin.php?page=improveseo_projects&action=delete&id=' . $project->id . '&noheader=true') */ ?>"
													onclick="return confirm('This action will delete project and all generated posts/pages')">Delete
													project and all posts/pages</a>
											</span>


											<!-- <button type="button" class="toggle-row"><span class="screen-reader-text">Show
													more details</span></button> -->
										</div>

									</ul>
								</div>



							</td>
						</tr>
						<!-- Duplicate rows for demonstration -->
					<?php endforeach; ?>
				</tbody>
			</table>
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
	<script>
		jQuery('#cb-select-all').click(function (e) {
			jQuery("input[type=checkbox]").prop('checked', jQuery(this).prop('checked'));
		});
	</script>
</div>

<?php View::endSection('content') ?>

<?php View::make('layouts.main') ?>