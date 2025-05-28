<?php

use ImproveSEO\View;

?>



<?php View::startSection('breadcrumbs') ?>

<a href="<?= admin_url('admin.php?page=improveseo_dashboard') ?>">Improve SEO</a>

&raquo;

<span>Authors List</span>

<?php View::endSection('breadcrumbs') ?>



<?php View::startSection('content') ?>

<h1 class="hidden">Authors List</h1>



<div class="seo-breadcumb">
	<div class="seo-text">
		<p> Here you can create authors that are only for use within ImproveSEO. The posts and projects you make will be
			distributed among these authors to make your site look more authoritative (multiple authors) and matural.
		</p>
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
			<li>Authors List</li>
		</ul>
		<div class="import-export-btn">
			<button class="active" id="btn-add"
				onclick="window.location.href='<?= admin_url('admin.php?page=improveseo_authors&action=create') ?>'">
				Create Authors
			</button>

		</div>
	</div>
	<div class="actions">
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
			<p><?= $results['avail_roles']['improveseo_user'] ?> Items</p>
		</div>
	</div>
	<div class="improve-seo-container">
		<div class="project-lists">
			<div class="table-responsive">
				<table class="table">
					<thead>
						<tr>
							<th> Name </th>
							<th>E-Mail</th>
							<th> </th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($users as $user): ?>
							<tr>
								<td data-label="Name"> <strong>
										<div class="improve-seo-letter">A</div> <?= $user->display_name ?>
									</strong> </td>
								<td data-label="E-Mail"> <?= $user->user_email ?> </td>
								<td data-label="Actions" class="Actions_td"> <a href="#"> <img
											src="<?php echo WT_URL . '/assets/images/latest-images/ri_more-2-fill.svg' ?>"
											alt="ri_more-2-fill"> </a> </td>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<div class="actions">
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
					<p><?= $results['avail_roles']['improveseo_user'] ?> Items</p>
				</div>
			</div>
		</div>
	</div>
</div>


<?php View::endSection('content') ?>



<?php View::make('layouts.main') ?>