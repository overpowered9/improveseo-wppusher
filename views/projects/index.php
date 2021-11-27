<?php
use ImproveSEO\View;
?>
<?php View::startSection('breadcrumbs') ?>
<a href="<?= admin_url('admin.php?page=improveseo') ?>">Improve SEO</a>
&raquo;
<span>Projects List</span>
<?php View::endSection('breadcrumbs') ?>
<?php View::startSection('content') ?>
<h1 class="hidden">Product Listing</h1>
<div class="show_loading alert-modal">
	<h1 class="hidden">Projects List</h1>
	<h2 id="mid_notice"></h2>
</div>
<div class="projectes improveseo_wrapper intro_page  p-3 p-lg-4">
	<section class="project-section border-bottom d-flex flex-row  justify-content-between align-items-center pb-2">
		<div class="project-heading d-flex flex-row">
			<img class="mr-2" src="<?php echo WT_URL.'/assets/images/project-list-logo.png'?>" alt="ImproveSeo">
			<h1>Projects List</h1>
		</div>
		<a href="<?= admin_url('admin.php?page=improveseo') ?>" class="btn btn-outline-primary btn-small" id="btn-add">Add New</a>
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
			<div class="table-responsive-sm">
			<table class="table widefat fixed wp-list-table widefat fixed striped table-view-list posts">
				<thead>
					<tr>
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
					<?php foreach ($projects as $project): ?>
					<tr <?= $highlight == $project->id ? ' class="WHProject--highlight"' : '' ?>>
						<td scope="col" class="column-title column-primary has-row-actions">
							<strong> 
								<a href="javascript:void(0)" class="primary"><?= $project->name ?></a>
							</strong>
							<div class="row-actions">
								<span class="edit">
									<a class="ct-btn btn btn-outline-primary" href="<?= admin_url("admin.php?page=improveseo_projects&action=export_urls&id={$project->id}&noheader=true") ?>">
										Export a list of all posts/pages URLs
									</a>
								</span>
								
								<span class="edit">
									<a class="ct-btn btn btn-outline-primary" href="<?= admin_url("admin.php?page=improveseo&action=edit_post&id={$project->id}&update=true") ?>">
										Update posts
									</a>
								</span>
								
								<span class="edit">
									<a class="ct-btn btn btn-outline-primary" href="<?= admin_url('admin.php?page=improveseo_projects&action=duplicate&id='. $project->id .'&noheader=true') ?>">
										Duplicate project
									</a>
								</span>
								
								<span class="edit">
									<a class="ct-btn btn btn-outline-primary" href="<?= admin_url('admin.php?page=improveseo_projects&action=stop&id='. $project->id .'&noheader=true') ?>">
										Stop process
									</a>
								</span>
								
								<span class="trash">
									<a class="del-btn btn btn-outline-danger" class="submitdelete" href="<?= admin_url('admin.php?page=improveseo_projects&action=delete&id='. $project->id .'&noheader=true') ?>" onclick="return confirm('This action will delete project and all generated posts/pages')">Delete project and all posts/pages</a>
								</span>
								
								<span class="trash">
									<a class="del-btn btn btn-outline-danger" class="submitdelete" href="<?= admin_url('admin.php?page=improveseo_projects&action=delete_posts&id='. $project->id .'&noheader=true') ?>" onclick="return confirm('This action will delete all generated posts/pages')">Delete only posts/pages</a>
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
							<?php if ($project->state == 'Published' && $project->iteration < $project->max_iterations): ?>
								<a href="javascript:build_project(<?= $project->id ?>)" class="btn btn-primary" target="_self">Build posts</a>
							<?php endif; ?>
							<?php if ($project->state == 'Updated' && $project->iteration < $project->max_iterations): ?>
								<!--<a href="javascript:update_project(<?//= $project->id ?>)" class="btn btn-outline-primary" target="_self">Update posts</a>-->
							<?php endif; ?>
							<?php if ($project->state == 'Draft'): ?>
								<a href="<?= admin_url('admin.php?page=improveseo&action=edit_post&id='. $project->id) ?>" class="btn btn-outline-primary">Continue</a>
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
	function build_project(id){
		jQuery('.show_loading').css("display", "block");
		jQuery(".show_loading h1")
		.html("Building project.... in progress");
		start_build(id);
	}
	var numm;
	function start_build(ids){
		jQuery
		.ajax({
			url : ajaxurl,
			data : ({
				action : 'workdex_builder_ajax',
				page : 100,
				ajax : 1,
				id: ids
			}),
			success : function(data) {
				jQuery(".show_loading h1")
						.html("Building project.... in progress");
				jQuery(".show_loading h2")
				.html("Posts generated by now "+data);
				if(numm==data){
					jQuery('.show_loading').css("display", "none");
				}else{
					numm = data;
					setTimeout("start_build("+ids+")", 500);
				}
				location.reload(true);
			}
		});
	}
	function update_project(id){
		jQuery('.show_loading').css("display", "block");
		jQuery(".show_loading h1")
		.html("Updating project.... in progress");
		start_update(id);
	}
	var numm_update;
	function start_update(ids){
		var new_location = window.location.protocol + "//" + window.location.hostname +"/wp-admin/admin.php?page=improveseo_projects";
		jQuery
		.ajax({
			url : ajaxurl,
			data : ({
				action : 'workdex_builder_update_ajax',
				page : 100,
				ajax : 1,
				id: ids
			}),
			success : function(data) {
				jQuery(".show_loading h1")
						.html("Updating posts.... in progress");
				jQuery(".show_loading h2")
				.html("Posts generated by now "+data);
				if(numm_update==data){
					jQuery('.show_loading').css("display", "none");
				}else{
					numm_update = data;
					setTimeout("start_update("+ids+")", 500);
				}
				location.reload(true);
				window.location.href= new_location;
			}
		});
		
	}
	</script>
	
	<!-- Building Post Preview -->
	<?php 

	if(isset($_GET['post_preview'])){
		
		if($_GET['post_preview'] == 'true'){
			
			$project = $projects[0];
			if ($project->state == 'Published' && $project->iteration < $project->max_iterations){?>
				
				<script type="text/javascript">
					build_project(<?php echo $project->id ?>);
				</script>
			<?php 
			}
			
			elseif($project->state == 'Published' && $project->iteration == $project->max_iterations){
				wp_redirect(admin_url("admin.php?page=improveseo_projects&action=export_preview_url&id={$project->id}&noheader=true"));
				exit;
			}			
		}		
	}


	if(isset($_GET['build_posts_id'])){ ?>

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