<?php

use ImproveSEO\View;

if (isset($_POST['cat_name'])) {
	$cat_slug = sanitize_title($_POST['cat_name']);
	wp_insert_term(
		sanitize_text_field($_POST['cat_name']),
		'category',
		array(
			'slug' => $cat_slug,
		)
	);
}
?>

<?php View::startSection('breadcrumbs') ?>
<!-- <span><?php esc_html_e('Improve SEO', 'your-text-domain'); ?></span> -->
<?php View::endSection('breadcrumbs') ?>

<?php View::startSection('content'); ?>
<h1 class="hidden"><?php esc_html_e('Posting', 'your-text-domain'); ?></h1>
<div class="Posting improveseo_wrapper text-center">
	<section class="wellcom-section border-bottom">
		<img class="d-block mx-auto mb-4" src="<?php echo esc_url(IMPROVESEO_WT_URL . '/assets/images/improve-logo.png'); ?>" alt="<?php esc_attr_e('ImproveSeo', 'your-text-domain'); ?>">
		<h1 class="Posting__header"><?php esc_html_e('Welcome to Improve SEO!', 'your-text-domain'); ?></h1>
	</section>
	<section class="create_sec col-lg-12 border-bottom mx-auto p-4">
		<h3 class="Posting__subheader h1"><?php esc_html_e('What would you like to create?', 'your-text-domain'); ?></h3>
		<div class="Posting__buttons clearfix d-flex justify-content-center flex-column flex-sm-row align-items-center mt-4">
			<a href="<?php echo esc_url(admin_url("admin.php?page=improveseo_posting&action=create_post")); ?>" class="btn-trans btn btn-outline-primary Posting__post-button mr-0 mr-sm-3 mb-3 mb-sm-0"><?php esc_html_e('Create Post', 'your-text-domain'); ?></a>
			<a href="<?php echo esc_url(admin_url("admin.php?page=improveseo_posting&action=create_page")); ?>" class="btn-trans btn btn-outline-primary mb-3 mb-sm-0 Posting__page-button"><?php esc_html_e('Create Page', 'your-text-domain'); ?></a>
		</div>
	</section>
	<section class="create_cat_sec col-lg-12 mx-auto">
		<h3 class="Posting__subheader h1"><?php esc_html_e('Choose or Create Category', 'your-text-domain'); ?></h3>
		<div class="card mx-auto p-3 p-sm-4">
			<div class="category_improveseo clearfix card-body text-center p-0">
				<div class="cta-check clearfix d-flex justify-content-center flex-column flex-sm-row  align-items-start align-items-sm-center">
					<?php
					$select = '';
					$args = array(
						'hide_empty' => 0,
						'type'       => 'post',
						'orderby'    => 'name',
						'order'      => 'ASC',
					);
					$cats = get_categories($args);
					foreach ($cats as $category) {
						$checked = ($category->slug == '') ? 'checked onclick="return false"' : '';
						$select .= "<span><input type='checkbox' " . esc_attr($checked) . " value='" . esc_attr($category->term_id) . "' id='" . esc_attr($category->term_id) . "' name='cats[]'><label for='" . esc_attr($category->term_id) . "'>" . esc_html($category->name) . "</label></span>";
					}
					echo $select;
					?>
				</div>
				<div class="add_cat">
					<form method="post" class="form-wrap m-0">
						<div class="input-group mb-4">
							<input type="text" class="form-control" name="cat_name" placeholder="<?php esc_attr_e('Default input', 'your-text-domain'); ?>" value="" aria-label="<?php esc_attr_e('default input example', 'your-text-domain'); ?>" required>
						</div>
						<div class="input-group">
							<input type="submit" class="btn-trans btn btn-outline-primary btn-lg px-5 mx-auto" value="<?php esc_attr_e('Add Category', 'your-text-domain'); ?>">
						</div>
					</form>
				</div>
			</div>
		</div>
	</section>
</div>

<?php View::endSection('content') ?>

<?php echo View::make('layouts.main') ?>