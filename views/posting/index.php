<?php

use ImproveSEO\View;

if (! defined('ABSPATH')) exit;
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

<?php View::endSection('breadcrumbs') ?>

<?php View::startSection('content'); ?>
<h1 class="hidden"><?php esc_html_e('Posting', 'improve_seo'); ?></h1>
<div class="Posting improveseo_wrapper text-center">
	<section class="wellcom-section border-bottom">
		<img class="d-block mx-auto mb-4" src="<?php echo esc_url(IMPROVESEO_WT_URL . '/assets/images/improve-logo.png'); ?>" alt="<?php esc_attr_e('ImproveSeo', 'improve_seo'); ?>">
		<h1 class="Posting__header"><?php esc_html_e('Welcome to Improve SEO!', 'improve_seo'); ?></h1>
	</section>
	<section class="create_sec col-lg-12 border-bottom mx-auto p-4">
		<h3 class="Posting__subheader h1"><?php esc_html_e('What would you like to create?', 'improve_seo'); ?></h3>
		<div class="Posting__buttons clearfix d-flex justify-content-center flex-column flex-sm-row align-items-center mt-4">
			<a href="<?php echo esc_url(admin_url("admin.php?page=improveseo_posting&action=create_post")); ?>" class="btn-trans btn btn-outline-primary Posting__post-button mr-0 mr-sm-3 mb-3 mb-sm-0"><?php esc_html_e('Create Post', 'improve_seo'); ?></a>
			<a href="<?php echo esc_url(admin_url("admin.php?page=improveseo_posting&action=create_page")); ?>" class="btn-trans btn btn-outline-primary mb-3 mb-sm-0 Posting__page-button"><?php esc_html_e('Create Page', 'improve_seo'); ?></a>
		</div>
	</section>
	<section class="create_cat_sec col-lg-12 mx-auto">
		<h3 class="Posting__subheader h1"><?php esc_html_e('Choose or Create Category', 'improve_seo'); ?></h3>
		<div class="card mx-auto p-3 p-sm-4">
			<div class="category_improveseo clearfix card-body text-center p-0">
				<div class="cta-check clearfix d-flex justify-content-center flex-column flex-sm-row  align-items-start align-items-sm-center">
					<?php
					$allowed_html = array(
						'span' => array(),  // Allow <span> tags with no attributes
						'input' => array(   // Allow <input> tags with these attributes
							'type' => true,
							'value' => true,
							'id' => true,
							'name' => true,
							'checked' => true,
							'onclick' => true,
						),
						'label' => array(   // Allow <label> tags with these attributes
							'for' => true,
						),
					);

					$select = '';
					$args = array(
						'hide_empty' => 0,
						'type'       => 'post',
						'orderby'    => 'name',
						'order'      => 'ASC',
					);
					$cats = get_categories($args);
					foreach ($cats as $category) {
						$checked = ($category->slug === '') ? 'checked onclick="return false"' : '';
						$select .= sprintf(
							'<span><input type="checkbox" %s value="%s" id="%s" name="cats[]"><label for="%s">%s</label></span>',
							esc_attr($checked),
							esc_attr($category->term_id),
							esc_attr($category->term_id),
							esc_attr($category->term_id),
							esc_html($category->name)
						);
					}

					echo wp_kses($select, $allowed_html);
					?>
				</div>

				<div class="add_cat">
					<form method="post" class="form-wrap m-0">
						<div class="input-group mb-4">
							<input type="text" class="form-control" name="cat_name" placeholder="<?php esc_attr_e('Default input', 'improve_seo'); ?>" value="" aria-label="<?php esc_attr_e('default input example', 'improve_seo'); ?>" required>
						</div>
						<div class="input-group">
							<input type="submit" class="btn-trans btn btn-outline-primary btn-lg px-5 mx-auto" value="<?php esc_attr_e('Add Category', 'improve_seo'); ?>">
						</div>
					</form>
				</div>
			</div>
		</div>
	</section>
</div>

<?php View::endSection('content') ?>

<?php echo esc_html(View::make('layouts.main')); ?>