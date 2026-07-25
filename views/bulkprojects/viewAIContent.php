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
			<?php
			// The one and only title: ai_title, exactly what becomes post_title on
			// publish (the theme renders it as the page heading). The duplicate
			// in-content <h1> the generator emits is stripped by the shared renderer.
			?>
			<h1 class="iseo-aicontent-title"><?php echo esc_html($value->ai_title); ?></h1>

			<div class="iseo-aicontent-body">
				<?php
				// Render through the SAME builder used when the post is created
				// (hero image + h1-deduped article + shortcode blocks), so what
				// you preview here is exactly what gets published.
				$iseo_built = improveseo_bulk_build_post_content($value);
				echo do_shortcode(wp_kses_post($iseo_built['html']));
				?>
			</div>
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

	/* Heading hierarchy: page title (34px) must outrank EVERYTHING in the body.
	   Scoped + !important so no admin/theme stylesheet can invert it again. */
	.iseo-aicontent-article .iseo-aicontent-title {
		font-size: 34px !important;
		line-height: 1.25;
		font-weight: 700;
		color: #1d2327;
		margin: 0 0 24px;
	}

	.iseo-aicontent-body {
		font-size: 17px;
		line-height: 1.75;
		color: #2c3338;
	}

	/* Hero image comes through the shared renderer with its publish-time inline
	   style; round the corners like the rest of the preview. */
	.iseo-aicontent-body > img:first-child {
		border-radius: 10px;
	}

	/* Any h1 left inside the body must stay BELOW the page title. */
	.iseo-aicontent-body h1 {
		font-size: 30px;
		font-weight: 700;
		color: #1d2327;
		margin: 34px 0 16px;
		line-height: 1.3;
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

	.iseo-aicontent-body h4 {
		font-size: 18px;
		font-weight: 600;
		color: #1d2327;
		margin: 24px 0 10px;
		line-height: 1.4;
	}

	.iseo-aicontent-body h5,
	.iseo-aicontent-body h6 {
		font-size: 17px;
		font-weight: 600;
		color: #1d2327;
		margin: 20px 0 8px;
		line-height: 1.4;
	}

	/* FAQ (and any other block) consistency: the generator sometimes carries
	   its own font sizing on questions/answers — force everything back onto
	   the body heading scale defined above. */
	.iseo-aicontent-body [style*="font-size"] {
		font-size: inherit !important;
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
