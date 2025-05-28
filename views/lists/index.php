<?php

use ImproveSEO\View;

?>

<?php View::startSection('breadcrumbs') ?>

<a href="<?= admin_url('admin.php?page=improveseo_dashboard') ?>">Improve SEO</a>

&raquo;

<span>Improve SEO Lists</span>



<?php View::endSection('breadcrumbs') ?>



<?php View::startSection('content') ?>





<h2 class="hidden">Improve SEO Lists</h2>







<?php View::render('import/import') ?>


<div class="seo-breadcumb">
	<div class="seo-text">
		<p> Improve SEO lists allow you to input a list of keywords or niches that you would like to target and use
			improve SEO to quickly create posts for all of them. </p>
		<p> This is handy if you have a list of hundreds of keywords that you would like to bulk create posts for (for
			example, lists of amazon products or a list of long-tail keywords). </p>
		<p> Improve SEO will create one post/page for every item in the list. In order to activate improve SEO lists, be
			sure to use the shortcode in the title (you can use it elsewhere too, but make sure it's in the title). </p>
		<p> You can embed your list by using <code>@list:listname</code> (this will also be shown on the create a
			project page and when you create your list, so no need to memorize it). </p>
	</div>
</div>
<div class="global-wrap">
	<div class="head-bar">
		<img src="<?php echo WT_URL . '/assets/images/latest-images/seo-latest-logo.svg' ?>" alt="project-list-logo">
		<h1> ImproveSEO | 2.0.11 </h1>
		<span>Pro</span>
	</div>
	<div class="box-top">
		<ul class="breadcrumb-seo">
			<li><a href="#">Improve SEO</a></li>
			<li>Keyword Lists</li>
		</ul>
		<div class="import-export-btn">
			<!-- <button
				onclick="if(confirm('Are you sure you want to export all item?')) { window.location.href = '<?= admin_url('admin.php?page=improveseo_lists&action=export_all_list&noheader=true') ?>'; }">
				Export all Projects
			</button>
			<button id="importProject">Import</button> -->
			<button class="active"
				onclick="window.location.href='<?= admin_url('admin.php?page=improveseo_lists&action=create') ?>'">
				Create New
			</button>
		</div>
	</div>
	<div class="actions search-form-box">
		<form class="improve-seo-form-global">
			<input type="text" id="post-search-input" name="s" value="<?= $s; ?>" placeholder="Search Here">
			<input type="hidden" name="page" value="improveseo_lists" />
			<input type="hidden" name="action" value="index" />
			<button class="search-btn"> <img
					src="<?php echo WT_URL . '/assets/images/latest-images/clarity_search-line.svg' ?>"
					alt="clarity_search-line"> </button>
		</form>
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
			<p>112 Items</p>
		</div>
	</div>
	<div class="improve-seo-container">
		<div class="project-lists">
			<div class="table-responsive">
				<table class="table project_table_listing">
					<thead>
						<tr>
							<th> Name </th>
							<th>Content</th>
							<th> </th>
						</tr>
					</thead>
					<tbody>
						<?php if (!empty($lists)): ?>
							<?php foreach ($lists as $item): ?>
								<tr>
									<td data-label="Name"
										onclick="window.location.href='<?= admin_url('admin.php?page=improveseo_lists&action=edit&id=' . $item->id) ?>'"
										style="cursor: pointer;  padding-top: 20px; vertical-align: text-top;">
										<strong><?= $item->name ?> </strong>
									</td>
									<td data-label="Content"> <?php
									if (str_word_count($item->list) > 50):
										echo "<span class='list-content-overflow'>" . $item->list . "</span>";
									else:
										echo $item->list;
									endif;
									?></td>
									<td data-label="Action">
										<div style="display: flex;justify-content: center;">
											<a
												href="<?= admin_url('admin.php?page=improveseo_lists&action=edit&id=' . $item->id) ?>">
												<img src="<?php echo WT_URL . '/assets/images/latest-images/write.svg' ?>"
													alt="write"> </a>
											<a class="submitdelete"
												href="<?= admin_url('admin.php?page=improveseo_lists&action=delete&id=' . $item->id . '&noheader=true') ?>"
												onclick="return confirm('Are you sure you want to delete the list?')"> <img
													src="<?php echo WT_URL . '/assets/images/latest-images/delete.svg' ?>"
													alt="delete"> </a>
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
		</div>
	</div>
</div>

<?php View::endSection('content') ?>

<?php View::make('layouts.main') ?>