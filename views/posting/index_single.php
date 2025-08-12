<?php

use ImproveSEO\View;

if (isset($_POST['cat_name'])) {

	$cat_slug = $_POST['cat_name'];

	$cat_slug = preg_replace('/\s*/', '-', $cat_slug);

	$cat_slug = strtolower($cat_slug);

	wp_insert_term(

		// the name of the category

		$_POST['cat_name'],

		// the taxonomy, which in this case if category (don't change)

		'category',

		array(

			// what to use in the url for term archive

			'slug' => $_POST['cat_name'],

		)

	);

}

?>



<?php View::startSection('breadcrumbs') ?>

<!-- <span>Improve SEO</span> -->

<?php View::endSection('breadcrumbs') ?>



<?php View::startSection('content'); ?>

<h1 class="hidden"> Posting </h1>

<div class="global-wrap">
	<div class="head-bar">
		<img src="<?php echo WT_URL . '/assets/images/latest-images/seo-latest-logo.svg' ?>" alt="project-list-logo">
		<h1 style="font-size: 36px; font-weight: 500;"> Welcome To Improve SEO! </h1>
	</div>
	<div class="container">
		<div class="breadcrumb text-center">
			<span class="active">Creation</span> &gt;
			<span>Category</span> &gt;
			<span>Content</span> &gt;
			<span>Publish</span>
		</div>
		

		<div class="category-box category_improveseo">
			<h3 class="category-title">Create Category</h3>
			<p class="category-subtext" style="color: #666; font-size: 14px; margin-bottom: 15px;">
				Easily add new categories with a simple click. Create as many as you need, one at a time.
			</p>
			<div class="add-category">
				<form method="post">
					<input type="text" placeholder="Write Here" name="cat_name" class="input-box" required>
					<input type="submit" class="add-button" value="Add Category">
					<!-- <button class="add-button">Add Category</button> -->
				</form>
			</div>
		</div>
		<div class="create-ai-post">
			<div class="create-ai">
				
				<div class="create-ai-col">
					<a class="Posting__post-button" href="<?php echo admin_url("admin.php?page=improveseo_posting&action=create_post_single"); ?>">
						<img src="<?php echo WT_URL . '/assets/images/latest-images/Mobile-UX-rafiki.png'; ?>"
							alt="Create Single AI Post">
						<h3>Create Single AI Post</h3>
					</a>
				</div>
			</div>
		</div>
	</div>
</div>



<?php View::endSection('content') ?>



<?php echo View::make('layouts.main') ?>