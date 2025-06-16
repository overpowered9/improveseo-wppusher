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
		<div class="create-ai-post">
			<div class="create-ai">
				<h2 class="title">What would you like to create?</h2>
				<div class="create-ai-col">
					<a class="Posting__post-button" href="<?php echo admin_url("admin.php?page=improveseo_posting&action=create_post&mode=single"); ?>">
						<img src="<?php echo WT_URL . '/assets/images/latest-images/Mobile-UX-rafiki.png'; ?>"
							alt="Create Single AI Post">
						<h3>Create Single AI Post</h3>
					</a>
				</div>


				<div class="create-ai-col">
					<a class="Posting__page-button" href="<?php echo admin_url("admin.php?page=improveseo_posting&action=create_page"); ?>">
						<img src="<?php echo WT_URL . '/assets/images/latest-images/Multi-device.png' ?>"
							alt="Multi-device">
						<h3>Bulk Create AI Posts</h3>
					</a>
				</div>
			</div>
		</div>

		<div class="category-box category_improveseo">
			<h3 class="category-title">Choose or Create Category</h3>
			<div class="category-list">
				<?php
				$select = '';
				$args = array(
					"hide_empty" => 0,
					"type" => "post",
					"orderby" => "name",
					"order" => "ASC"
				);
				$cats = get_categories($args);
				foreach ($cats as $category) {

					if ($category->slug == "improve-seo") {
						$checked = 'checked  onclick="return false"';
					} else {
						$checked = '';
					}

					$select .= "<span class='category'>
    <label for='" . $category->term_id . "'>
        <input style='display:none;' type='checkbox' " . $checked . " value='" . $category->term_id . "' id='" . $category->term_id . "' name='cats[]'>
        " . $category->name . "
    </label>
</span>";


				}
				echo $select;
				?>
				<!-- <span class="category active">Anil</span> -->
			</div>

			<div class="add-category">
				<form method="post">
					<input type="text" placeholder="Write Here" name="cat_name" class="input-box" required>
					<input type="submit" class="add-button" value="Add Category">
					<!-- <button class="add-button">Add Category</button> -->
				</form>
			</div>
		</div>
	</div>
</div>



<?php View::endSection('content') ?>



<?php echo View::make('layouts.main') ?>