<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


use ImproveSEO\View;

$from_onboarding = isset( $_GET['from'] ) && $_GET['from'] === 'onboarding';

?>



<?php View::startSection('breadcrumbs') ?>

<!-- <span>Improve SEO</span> -->

<?php View::endSection('breadcrumbs') ?>



<?php View::startSection('content'); ?>

<h1 class="hidden"> Posting </h1>

<div class="global-wrap">
	<div class="head-bar">
		<img src="<?php echo esc_url( improveseo_logo_url() ); ?>" alt="ImproveSEO logo">
		<h1 style="font-size: 36px; font-weight: 500;"> What would you like to create? </h1>
	</div>
	<div class="container">
		<div class="create-ai-intro">
			<p>Time to create content that helps customers find you &mdash; in Google and in AI assistants like ChatGPT.</p>
			<p>Choose how you want to start:</p>
			<ul>
				<li><strong>Single Post</strong> &mdash; one post built from one keyword.</li>
				<li><strong>Bulk Post Project</strong> &mdash; multiple posts built from a keyword list.</li>
			</ul>
			<p>Either way, a short wizard walks you through every step. Look for the [i] icon &mdash; it explains what each step does and why it matters.</p>
			<p>Bulk Post Projects need a keyword list. Don&rsquo;t have one? <a href="<?php echo esc_url( admin_url( 'admin.php?page=improveseo_lists' ) ); ?>">Create one here</a></p>
		</div>
		<div class="create-ai-post">
			<div class="create-ai">
				<div class="create-ai-col">
					<a class="Posting__post-button" href="<?php echo esc_url( admin_url( 'admin.php?page=improveseo_posting&action=create_post_single' . ( $from_onboarding ? '&from=onboarding' : '' ) ) ); ?>" aria-describedby="iseo-tip-single-post">
						<img src="<?php echo esc_url( WT_URL . '/assets/images/latest-images/Mobile-UX-rafiki.png' ); ?>"
							alt="Create Single Post">
						<h3>Create Single Post
							<span class="create-ai-tip">
								<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
								<span class="create-ai-tip-bubble" id="iseo-tip-single-post" role="tooltip">Create a single post based on a chosen seed keyword</span>
							</span>
						</h3>
					</a>
					<a class="Posting__post-button create-ai-continue" href="<?php echo esc_url( admin_url( 'admin.php?page=improveseo_posting&action=create_post_single' . ( $from_onboarding ? '&from=onboarding' : '' ) ) ); ?>">Click on &lsquo;Create Single Post&rsquo; to continue</a>
				</div>


				<div class="create-ai-col">
					<a class="Posting__page-button" href="<?php echo esc_url( admin_url( 'admin.php?page=improveseo_posting&action=create_post_bulk' . ( $from_onboarding ? '&from=onboarding' : '' ) ) ); ?>">
						<img src="<?php echo esc_url( WT_URL . '/assets/images/latest-images/Multi-device.png' ); ?>"
							alt="Multi-device">
					</a>
					<?php // The hint links to Keyword Lists, and a link cannot be nested inside the card's
					      // own <a> — so the title carries the link and the hint sits outside it. ?>
					<h3>
						<a class="Posting__page-button" href="<?php echo esc_url( admin_url( 'admin.php?page=improveseo_posting&action=create_post_bulk' . ( $from_onboarding ? '&from=onboarding' : '' ) ) ); ?>" aria-describedby="iseo-tip-bulk-post">Create Bulk Post Project</a>
						<span class="create-ai-tip">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
							<span class="create-ai-tip-bubble is-wide is-interactive" id="iseo-tip-bulk-post" role="tooltip">Create a project with multiple posts based on a keyword list. Don&rsquo;t have a keyword list? <a href="<?php echo esc_url( admin_url( 'admin.php?page=improveseo_lists' ) ); ?>">Create one</a> before you get started.</span>
						</span>
					</h3>
					<a class="Posting__page-button create-ai-continue" href="<?php echo esc_url( admin_url( 'admin.php?page=improveseo_posting&action=create_post_bulk' . ( $from_onboarding ? '&from=onboarding' : '' ) ) ); ?>">Click on &lsquo;Create Bulk Post Project&rsquo; to continue</a>
				</div>
			</div>
		</div>

	</div>
</div>

<?php if ( $from_onboarding ) : ?>
<script>
jQuery(function ($) {
    'use strict';
    // Both choices are guided (single: onboarding-guide.js, bulk: onboarding-guide-bulk.js).
    // They are introduced one at a time — each card gets its own spotlight and its own
    // explanation — rather than framing both under one message: Next/Back on the card
    // switches between them, and so does pointing at the other card. Clicking whichever
    // card is lit starts that card's guide.
    var $cols  = $('.create-ai-col');
    var $links = $('.Posting__post-button, .Posting__page-button');
    var OPTIONS = [
        {
            $el:   $cols.eq(0),
            title: 'Create a single article &#x1F680;',
            message: 'Writes <strong>one post</strong> from one keyword. You pick the title, settings and cover image, review the article, and publish it. Best for your first try.'
                   + '<br><br>Click <strong>Create Single Post</strong> to start — we’ll guide you through every step.'
        },
        {
            $el:   $cols.eq(1),
            title: 'Or create many posts at once &#x1F4DA;',
            message: 'A <strong>Bulk Post Project</strong> writes one post for every keyword in a keyword list, in the background. You’ll need a keyword list first.'
                   + '<br><br>Click <strong>Create Bulk Post Project</strong> to start — we’ll guide you through every step.'
        }
    ];
    var current = 0;

    // Clears the step key an earlier build of onboarding-guide.js used to persist, so a
    // browser that still has one is not carrying dead data. The guide no longer reads or
    // writes it — every page load starts on Step 1. Safe to delete after a release or two.
    try { localStorage.removeItem('improveseo_guide_step'); } catch (e) { /* ignore */ }

    // ── Spotlight (reuse existing CSS, add coral accent) ──────────────
    var $spot = $('<div id="iseo-guide-spotlight"></div>').css({
        border:    '3px solid #F77A59',
        boxShadow: '0 0 0 5px rgba(247,122,89,0.25), 0 0 0 9999px rgba(0,0,0,0.72)'
    }).appendTo('body');

    // ── Tooltip (reuse existing CSS classes) ─────────────────────────
    var $tip = $('<div id="iseo-guide-tooltip"></div>').appendTo('body');

    function skipGuide() {
        $spot.remove();
        $tip.remove();
        $('body').removeClass('iseo-guide-active');
        $cols.off('mouseenter.iseoguide');
        $(window).off('.iseoguide');
        // Navigate the cards without onboarding flag
        $links.each(function () {
            $(this).attr('href', $(this).attr('href').replace('&from=onboarding', ''));
        });
    }

    function render() {
        var opt   = OPTIONS[current];
        var total = OPTIONS.length;
        var html  = '<div class="iseo-guide-tooltip-inner">'
            + '<div class="iseo-guide-progress"><div class="iseo-guide-progress-bar" style="width:' + Math.round(((current + 1) / total) * 100) + '%"></div></div>'
            + '<div class="iseo-guide-header">'
            + '<span class="iseo-guide-bot">&#x1F916;</span>'
            + '<span class="iseo-guide-step-counter">Getting started \u00b7 ' + (current + 1) + ' of ' + total + '</span>'
            + '</div>'
            + '<div class="iseo-guide-title">' + opt.title + '</div>'
            + '<div class="iseo-guide-message">' + opt.message + '</div>'
            + '<div class="iseo-guide-actions">';
        if (current > 0) {
            html += '<button class="iseo-guide-btn-next iseo-guide-btn-back" type="button">\u2190 Back</button>';
        }
        if (current < total - 1) {
            html += '<button class="iseo-guide-btn-next iseo-guide-btn-more" type="button">Other option \u2192</button>';
        }
        html += '<button class="iseo-guide-btn-skip" type="button">Skip guide</button></div></div>';

        $tip.html(html);
        $tip.find('.iseo-guide-btn-skip').on('click', skipGuide);
        $tip.find('.iseo-guide-btn-more').on('click', function () { show(current + 1); });
        $tip.find('.iseo-guide-btn-back').on('click', function () { show(current - 1); });
    }

    // Below the lit card if there is room, else above it, else clamped on screen.
    function positionGuide() {
        var $el = OPTIONS[current].$el;
        if (!$el.length) return;
        var r   = $el[0].getBoundingClientRect();
        var pad = 10;
        $spot.css({
            top:    (r.top    - pad) + 'px',
            left:   (r.left   - pad) + 'px',
            width:  (r.width  + pad * 2) + 'px',
            height: (r.height + pad * 2) + 'px'
        });
        var ttW = 300;
        var ttH = $tip.outerHeight(true) || 220;
        var vw  = window.innerWidth, vh = window.innerHeight;
        var top = r.bottom + 14, side = 'bottom';
        if (top + ttH > vh - 10 && r.top - ttH - 14 >= 10) {
            top  = r.top - ttH - 14;
            side = 'top';
        }
        var left = r.left + r.width / 2 - ttW / 2;
        top  = Math.max(10, Math.min(top,  vh - ttH - 10));
        left = Math.max(10, Math.min(left, vw - ttW - 10));
        $tip.css({ top: top + 'px', left: left + 'px', width: ttW + 'px' });
        $tip.attr('data-pos', side);
    }

    function show(i) {
        if (i < 0 || i >= OPTIONS.length || !OPTIONS[i].$el.length) return;
        current = i;
        render();
        positionGuide();
    }

    $('body').addClass('iseo-guide-active');
    $spot.show();

    setTimeout(function () {
        show(0);
        $tip.show();
    }, 200);

    // Pointing at the other card introduces that one instead.
    $cols.each(function (i) {
        $(this).on('mouseenter.iseoguide', function () {
            if (i !== current) show(i);
        });
    });

    $(window).on('resize.iseoguide scroll.iseoguide', function () { positionGuide(); });
});
</script>
<?php endif; ?>

<?php View::endSection('content') ?>



<?php View::make('layouts.main'); ?>