<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


use ImproveSEO\View;

?>

<?php View::startSection('breadcrumbs') ?>
<a href="<?php echo esc_url( admin_url('admin.php?page=improveseo_dashboard') ); ?>">Improve SEO</a>
&raquo;
<span>Preview Post</span>
<?php View::endSection('breadcrumbs') ?>


<?php View::startSection('content') ?>

<?php View::render('import/import') ?>


<h1 class="hidden">Preview Post</h1>
<div class="show_loading alert-modal">
	<h1 class="hidden">Preview Post</h1>
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
	   style; round the corners like the rest of the preview. The renderer's
	   inline margin-bottom is what sets the gap to the first paragraph (it has
	   to be inline so the published post matches) — this only guarantees the
	   image doesn't also push itself away from the title above it. */
	.iseo-aicontent-body > img:first-child {
		border-radius: 10px;
		margin-top: 0;
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
	   the body heading scale defined above.

	   Widened for the "Key Takeaways" callout, which came through bigger than
	   the surrounding article: the size can arrive as an inline font-size, as
	   the `font:` SHORTHAND (which the font-size substring match misses), or as
	   a legacy <font>/<big> tag, so all four are normalised. */
	.iseo-aicontent-body [style*="font-size"],
	.iseo-aicontent-body [style*="font:"],
	.iseo-aicontent-body font,
	.iseo-aicontent-body big {
		font-size: inherit !important;
	}

	/* Body copy is ONE scale. Only h1-h6 are allowed to differ from it, so a
	   callout wrapper (Key Takeaways and friends) can never scale up the text
	   inside it — whatever it carries its size on, the paragraphs and list
	   items within resolve back to the article's body size.
	   `inherit` rather than a fixed px so the mobile step-down below still
	   applies. */
	.iseo-aicontent-body div,
	.iseo-aicontent-body section,
	.iseo-aicontent-body aside,
	.iseo-aicontent-body blockquote,
	.iseo-aicontent-body p,
	.iseo-aicontent-body ul,
	.iseo-aicontent-body ol,
	.iseo-aicontent-body li,
	.iseo-aicontent-body span,
	.iseo-aicontent-body strong,
	.iseo-aicontent-body em,
	.iseo-aicontent-body td,
	.iseo-aicontent-body th {
		font-size: inherit !important;
	}

	.iseo-aicontent-body p {
		margin: 0 0 18px;
	}

	/* Generated FAQ blocks. Same values as assets/css/improveseo-front.css and the
	   instant-preview modal in assets/css/made_by_me.css, so a FAQ looks the same on
	   this page, in the modal and on the published post. Two classes outrank the bare
	   `h3` / `p` rules above, so no !important is needed. */
	.iseo-aicontent-body .improveseo-faq-item {
		margin-bottom: 24px;
	}

	.iseo-aicontent-body .improveseo-faq-question {
		font-size: 18px;
		font-weight: 600;
		margin: 0 0 8px;
		line-height: 1.4;
	}

	.iseo-aicontent-body .improveseo-faq-answer {
		margin: 0;
		line-height: 1.7;
	}

	/* Lists render with their markers, exactly as the published post does.

	   The content is already correct — the generator emits real <ul><li> — but
	   wp-admin's common.css carries a blanket `ul { list-style: none; }`, so every
	   bulleted list in this preview lost its bullets while <ol> kept its numbers
	   (the reset only names ul). Nothing here changes the markup; it re-states the
	   markers the front end applies, including the nested levels, so a list under
	   "Key Takeaways" looks the same here as it does once published.

	   Indentation is expressed in em so it tracks this preview's own font size, the
	   same proportion (~1.8em) the front end leaves for its markers. */
	.iseo-aicontent-body ul,
	.iseo-aicontent-body ol {
		margin: 0 0 18px;
		padding-left: 1.8em;
	}

	.iseo-aicontent-body ul {
		list-style: disc outside;
	}

	.iseo-aicontent-body ol {
		list-style: decimal outside;
	}

	.iseo-aicontent-body ul ul {
		list-style-type: circle;
	}

	.iseo-aicontent-body ul ul ul {
		list-style-type: square;
	}

	.iseo-aicontent-body ol ol {
		list-style-type: lower-alpha;
	}

	.iseo-aicontent-body ol ol ol {
		list-style-type: lower-roman;
	}

	/* Nested lists sit tight against their parent item, not floating in extra space. */
	.iseo-aicontent-body li > ul,
	.iseo-aicontent-body li > ol {
		margin: 8px 0 0;
	}

	.iseo-aicontent-body li {
		display: list-item;
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
