<?php
use ImproveSEO\View;
?>
<?php View::startSection('breadcrumbs') ?>
<a href="<?= admin_url('admin.php?page=improveseo') ?>">Improve SEO</a>
&raquo;
<span>Improve SEO Lists</span>
<?php View::endSection('breadcrumbs') ?>
<?php View::startSection('content') ?>
<h2 class="hidden">Improve SEO Lists</h2>
<div class="notice notice-success notice-improveseo">
	<p>
		Improve SEO lists allow you to input a list of keywords or niches that you would like to target and use Improve SEO to quickly create posts for all of them.
	</p>
	<p>
		This is handy if you have a list of hundreds of keywords that you would like to bulk create posts for (for example, lists of Amazon products or a list of long tail keywords).
	</p>
	<p>
		Improve SEO will create one post/page for every item in the list. In order to  activate Improve SEO lists, be sure to use the shortcode in the title (you can use it elsewhere too, but make sure it's in the title).
	</p>
	<p>
		You can embed your list by using <strong>@list:listname</strong> (this will also be shown on the create a project page and when you create your list, so no need to memorize it).
	</p>
</div>
<div class="listing improveseo_wrapper p-3 p-lg-4">
	<section class="project-section border-bottom d-flex flex-row  justify-content-between align-items-center pb-2 mb-3">
		<div class="project-heading d-flex flex-row">
			<img class="mr-2" src="<?php echo WT_URL.'/assets/images/project-list-logo.png'?>" alt="ImproveSeo">
			<h1>Improve SEO Lists</h1>
		</div>
		<a href="<?= admin_url('admin.php?page=improveseo_lists&action=create') ?>"  class="btn btn-outline-primary btn-small py-2 px-3">Create New</a>
	</section>

	<section class="pagination-wrapper text-right py-3">
		<span class="pagination-links">
			<?= paginate_links(array(
			'total' => $pages,
			'current' => $page,
			'format' => '&paged=%#%',
			'base' => admin_url('admin.php?page=improveseo_lists%_%')
			)) ?>
		</span>
	</section>
	<section class="project-table-wrapper">
		<form method="get">
			<div class="table-responsive-sm">
				<div class="tablenav top">
					<div class="alignright actions">
						<label for="bulk-action-selector-top" class="screen-reader-text">Search Lists</label>
						<input type="search" id="post-search-input" name="s" value="<?= $s; ?>">
						<input type="hidden" name="page" value="improveseo_lists" />
						<input type="hidden" name="action" value="index" />
						<input type="submit" id="doaction" class="btn btn-outline-primary button action" value="Search Lists">
					</div>
					<br class="clear">
				</div>	
				<table class="table widefat fixed wp-list-table widefat fixed table-view-list posts">
					<thead>
						<tr>
							<th scope="col" class="manage-column manage-column column-title column-primary">Name</th>
							<th scope="col" class="manage-column">Content</th>
							<th style="width: 20%;" scope="col" class="manage-column"></th>
						</tr>
					</thead>
					<tbody>
						<?php if(!empty($lists)): ?>
						<?php foreach ($lists as $item): ?>
						<tr>
							<td class="column-title column-primary has-row-actions">
								<strong>
								<a class="row-title" href="<?= admin_url('admin.php?page=improveseo_lists&action=edit&id='. $item->id) ?>"><?= $item->name ?></a>
								</strong>
								<button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button>
							</td>
							<td data-colname="Content">
								<?php 
									if(str_word_count($item->list) > 50):
										echo "<span class='list-content-overflow'>".$item->list."</span>";
									else:
										echo $item->list;
									endif;
								?>

								
							</td>
							<td class="text-lg-right pr-2 actions-btn" data-colname="Action">
								<div class="row-actions">
									<span class="edit">
										<a href="<?= admin_url('admin.php?page=improveseo_lists&action=edit&id='. $item->id) ?>" title="Edit this item" class="btn btn-outline-primary">Edit</a>
									</span>
									<span class="trash">
										<a class="submitdelete btn btn-outline-danger" href="<?= admin_url('admin.php?page=improveseo_lists&action=delete&id='. $item->id .'&noheader=true') ?>" onclick="return confirm('Are you sure you want to delete the list?')">Trash</a>
									</span>
								</div>
							</td>
							</tr>
							<?php endforeach; ?>
							
							<?php
							else: ?>
							<tr>
								<td colspan="3">No Lists Available.</td>
							</tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
				
			</form>
		</section>
	<section class="pagination-wrapper text-right py-3">
		<span class="pagination-links">
			<?= paginate_links(array(
			'total' => $pages,
			'current' => $page,
			'format' => '&paged=%#%',
			'base' => admin_url('admin.php?page=improveseo_lists%_%')
			)) ?>
		</span>
	</section>
	</div>
	<?php View::endSection('content') ?>
	<?php View::make('layouts.main') ?>