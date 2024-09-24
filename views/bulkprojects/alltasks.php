<?php

use ImproveSEO\View;




?>

<?php View::startSection('breadcrumbs') ?>
<a href="<?= admin_url('admin.php?page=improveseo_dashboard') ?>">Improve SEO</a>
&raquo;
<span>Bulk Projects List</span>

<?php 
if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')   
         $url = "https://";   
    else  
         $url = "http://";   
    // Append the host(domain name, ip) to the URL.   
    $url.= $_SERVER['HTTP_HOST'];   
    // Append the requested resource location to the URL   
    $url.= $_SERVER['REQUEST_URI'];    
?>

<?php View::endSection('breadcrumbs') ?>


<?php View::startSection('content') ?>

<?php View::render('import/import') ?>


<h1 class="hidden">All Keywords of <?php echo $project_name; ?></h1>
<div class="show_loading alert-modal">
	<h1 class="hidden">All Keywords List</h1>
	<h2 id="mid_notice"></h2>
	
</div>
<div class="projectes improveseo_wrapper intro_page  p-3 p-lg-4">
	<section class="project-section border-bottom d-flex flex-row  justify-content-between align-items-center pb-2">
		<div class="project-heading d-flex flex-row">
			<img class="mr-2" src="<?php echo WT_URL . '/assets/images/project-list-logo.png' ?>" alt="ImproveSeo">
			<h1>All Keywords List</h1>
			<button type="button" class="toggle-row pull-right" onclick="return refreshPage()">Refresh List</button>
		</div>
		<div class="action-buttons">

			<?php /*<a onclick="return confirm('Are you sure you want to export all item?');" href="<?= admin_url('admin.php?page=improveseo_projects&action=export_all_project&noheader=true') ?>" class="btn btn-outline-primary btn-small" id="exportProject">Export All Project</a>
			<!-- <a href="#" class="btn btn-outline-primary btn-small" id="importProject">Import</a> -->
			<?php /*<a href="<?= admin_url('admin.php?page=improveseo_posting') ?>" class="btn btn-outline-primary btn-small" id="btn-add">Add New</a>*/ ?>
		</div>

	</section>
	<section class="pagination-wrapper text-right py-3">
		<span class="pagination-links">
			<?= paginate_links(array(
				'total' => $pages,
				'current' => $page,
				'format' => '&paged=%#%',
				'base' => admin_url('admin.php?page=improveseo_bulkprojects&id='.$id.'%_%')
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
						<label for="bulk-action-selector-top" class="screen-reader-text">Select bulk action</label>
						<select name="action" id="bulk-action-selector-top">
							<option value="bulk-empty">Bulk actions</option>
							<option value="bulk-delete-posts">Delete only posts/pages</option>

						</select>
						<input type="hidden" name="page" value="improveseo_projects" />
						<input type="hidden" name="noheader" value="true" />
						<input type="submit" id="doaction" class="del-btn btn btn-outline-danger button action" value="Delete Projects">
					</div>
					<div class="tablenav-pages one-page"><span class="displaying-num"><?php echo count($projects); ?> items</span></div>
					<br class="clear">
				</div>

				<table class="table widefat fixed wp-list-table widefat fixed striped table-view-list posts">
					<thead>
						<tr>
							<th scope="col" class="manage-column column-cb">
								<label class="screen-reader-text" for="cb-select-all">Select All</label>
								<input id="cb-select-all" type="checkbox">
							</th>
							<th scope="col" class="manage-column column-title column-primary" style="width: 26.66%">Keyword Name</th>
							<th scope="col" class="manage-column">Tone of voice</th>
                            <th scope="col" class="manage-column">Language</th>
                            <th scope="col" class="manage-column">Size</th>
							<th scope="col" class="manage-column">Content Status</th>
							<th scope="col" class="manage-column">Publish Date</th>
							<th scope="col" class="manage-column">Post Status</th>
							
						</tr>
					</thead>
					<tbody>
						<?php foreach ($projects as $key => $project) : ?>
							<tr <?= $highlight == $project->id ? ' class="WHProject--highlight"' : '' ?>>
								<td><input id="cb-select-<?php echo $project->id; ?>" type="checkbox" name="project_ids[]" value="<?php echo $project->id; ?>"></td>
								<td scope="col" class="column-title column-primary has-row-actions">
									<strong>
										<a href="javascript:void(0)" class="primary"><?= $project->keyword_name ?></a>
									</strong>

                                    <div class="row-actions">

										

										
									<?php if($project->status != 'Done') { ?> 
										<span class="edit">
											<?php $task_id = $_GET['id']; ?>
											<a class="ct-btn btn btn-outline-primary" href="<?= admin_url('admin.php?page=improveseo_bulkprojects&action=stop&mainid='.$task_id.'&id=' . $project->id) ?>">
												Stop process
											</a>
										</span>
									<?php } ?>

                                       

                                        <span class="trash">
											<a class="del-btn btn btn-outline-success" class="submitdelete" target="_blank" href="<?= admin_url('admin.php?page=improveseo_bulkprojects&action=viewAiContent&id=' . $project->id ) ?>">View AI content</a>
										</span>


										
									</div>
									<button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button>

									
								</td>
                                <td><?= $project->tone_of_voice ?></td>
                                <td><?= $project->content_lang ?></td>
                                <td><?= $project->nos_of_words ?></td>
                                <td><strong>
										<?php
										if ($project->status == 'Draft') { 
                                            echo 'Draft'; 
                                        } else if($project->status == 'Done'){
                                            echo 'Done';
                                        }else if($project->status == 'Stoped'){
                                            echo 'Stoped';
                                        } else {  echo 'Processing'; }
										?>
									</strong></td>

									<td><strong>
										<?php
										echo $project->published_on; 
										?>
									</strong></td>

									
									<td><strong>
										<?php
										echo $project->state; 
										?>
									</strong></td>
								
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
				'base' => admin_url('admin.php?page=improveseo_bulkprojects%_%')
			)) ?>
		</span>
	</section>
</div>
<?php View::endSection('content') ?>
<?php View::make('layouts.main') ?>
<script>
	function refreshPage() {
		location.reload();
	}
	</script>