<?php

use ImproveSEO\View;

?>

<?php View::startSection('breadcrumbs') ?>
<a href="<?= admin_url('admin.php?page=improveseo_dashboard') ?>">Improve SEO</a>
&raquo;
<span>View AI Content</span>
<?php View::endSection('breadcrumbs') ?>


<?php View::startSection('content') ?>

<?php View::render('import/import') ?>


<h1 class="hidden">View AI Content</h1>
<div class="show_loading alert-modal">
	<h1 class="hidden">View AI Content</h1>
	<h2 id="mid_notice"><a href="<?php echo $url; ?>">Refresh List</a></h2>
</div>

<div class="iseo-aicontent-wrap">
	<?php foreach ($projects as $key => $value) : ?>
		<article class="iseo-aicontent-article">
			<h1 class="iseo-aicontent-title"><?php echo esc_html($value->ai_title); ?></h1>

			<?php
			$iseo_img = !empty($value->ai_image) ? base64_decode($value->ai_image) : '';
			if ($iseo_img && filter_var($iseo_img, FILTER_VALIDATE_URL)) : ?>
				<div class="iseo-aicontent-hero">
					<img src="<?php echo esc_url($iseo_img); ?>" alt="<?php echo esc_attr($value->ai_title); ?>">
				</div>
			<?php endif; ?>

			<div class="iseo-aicontent-body">
				<?php
				// ai_content is stored as base64-encoded HTML. Render it as HTML (NOT nl2br,
				// which would double-space real markup) so it reads like the final published post.
				echo wp_kses_post(base64_decode($value->ai_content));
				?>
			</div>

			<?php if (!empty($value->testimonial)) {
				$testimonial_ids = '';
				$all_testimonial = explode("||", $value->testimonial);
				foreach ($all_testimonial as $key1 => $value1) {
					if (!empty($value1)) {
						$testimonial_ids = $value1 . ',' . $testimonial_ids;
					}
				}
				echo '<div class="iseo-aicontent-extra">' . do_shortcode('[improveseo_testimonial id="' . $testimonial_ids . '"]') . '</div>';
			} ?>

			<?php if (!empty($value->Button_SC)) {
				echo '<div class="iseo-aicontent-extra">' . do_shortcode('[improveseo_buttons id="' . $value->Button_SC . '"]') . '</div>';
			} ?>

			<?php if (!empty($value->GoogleMap_SC)) {
				echo '<div class="iseo-aicontent-extra">' . do_shortcode('[improveseo_googlemaps id="' . $value->GoogleMap_SC . '"]') . '</div>';
			} ?>

			<?php if (!empty($value->Video_SC)) {
				echo '<div class="iseo-aicontent-extra">' . do_shortcode('[improveseo_video id="' . $value->Video_SC . '"]') . '</div>';
			} ?>
		</article>
	<?php endforeach; ?>
</div>

<style>
	.iseo-aicontent-wrap {
		padding: 30px 20px 60px;
		background: #f6f8fa;
	}

	.iseo-aicontent-article {
		max-width: 820px;
		margin: 0 auto;
		background: #fff;
		border: 1px solid #e2e6ea;
		border-radius: 12px;
		box-shadow: 0 2px 10px rgba(0, 0, 0, .05);
		padding: 40px 48px 48px;
	}

	.iseo-aicontent-title {
		font-size: 34px;
		line-height: 1.25;
		font-weight: 700;
		color: #1d2327;
		margin: 0 0 24px;
	}

	.iseo-aicontent-hero {
		margin: 0 0 28px;
	}

	.iseo-aicontent-hero img {
		width: 100%;
		height: auto;
		border-radius: 10px;
		display: block;
	}

	.iseo-aicontent-body {
		font-size: 17px;
		line-height: 1.75;
		color: #2c3338;
	}

	.iseo-aicontent-body h2 {
		font-size: 26px;
		font-weight: 700;
		color: #1d2327;
		margin: 34px 0 14px;
		line-height: 1.3;
	}

	.iseo-aicontent-body h3 {
		font-size: 21px;
		font-weight: 600;
		color: #1d2327;
		margin: 28px 0 12px;
		line-height: 1.35;
	}

	.iseo-aicontent-body p {
		margin: 0 0 18px;
	}

	.iseo-aicontent-body ul,
	.iseo-aicontent-body ol {
		margin: 0 0 18px;
		padding-left: 26px;
	}

	.iseo-aicontent-body li {
		margin-bottom: 8px;
	}

	.iseo-aicontent-body img {
		max-width: 100%;
		height: auto;
		border-radius: 8px;
		margin: 18px 0;
	}

	.iseo-aicontent-body a {
		color: #0061B6;
		text-decoration: underline;
	}

	.iseo-aicontent-body table {
		width: 100%;
		border-collapse: collapse;
		margin: 0 0 20px;
	}

	.iseo-aicontent-body th,
	.iseo-aicontent-body td {
		border: 1px solid #dce1e5;
		padding: 10px 12px;
		text-align: left;
	}

	.iseo-aicontent-extra {
		margin-top: 24px;
	}

	@media (max-width: 782px) {
		.iseo-aicontent-article {
			padding: 24px 20px 32px;
		}

		.iseo-aicontent-title {
			font-size: 26px;
		}

		.iseo-aicontent-body {
			font-size: 16px;
		}
	}
</style>
<?php View::endSection('content') ?>
<?php View::make('layouts.main') ?>
