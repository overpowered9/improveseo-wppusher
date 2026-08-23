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
					<a class="Posting__post-button" href="<?php echo esc_url( admin_url( 'admin.php?page=improveseo_posting&action=create_post_single' . ( $from_onboarding ? '&from=onboarding' : '' ) ) ); ?>">
						<img src="<?php echo esc_url( WT_URL . '/assets/images/latest-images/Mobile-UX-rafiki.png' ); ?>"
							alt="Create Single AI Post">
						<h3>Create Single AI Post</h3>
					</a>
				</div>


				<div class="create-ai-col"<?php if ( $from_onboarding ) { echo ' style="opacity:0.4;pointer-events:none;"'; } ?>>
					<a class="Posting__page-button" href="<?php echo esc_url( admin_url("admin.php?page=improveseo_posting&action=create_post_bulk") ); ?>">
						<img src="<?php echo esc_url( WT_URL . '/assets/images/latest-images/Multi-device.png' ); ?>"
							alt="Multi-device">
						<h3>Create Bulk AI Posts Project</h3>
					</a>
				</div>
			</div>
		</div>

	</div>
</div>

<?php if ( $from_onboarding ) : ?>
<script>
jQuery(function ($) {
    'use strict';
    var $card = $('.Posting__post-button');

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
    $tip.html(
        '<div class="iseo-guide-tooltip-inner">'
        + '<div class="iseo-guide-progress"><div class="iseo-guide-progress-bar" style="width:0%"></div></div>'
        + '<div class="iseo-guide-header">'
        + '<span class="iseo-guide-bot">&#x1F916;</span>'
        + '<span class="iseo-guide-step-counter">Getting started</span>'
        + '</div>'
        + '<div class="iseo-guide-title">Let\u2019s create your first article! &#x1F680;</div>'
        + '<div class="iseo-guide-message">Click <strong>Create Single AI Post</strong> to begin. We\u2019ll guide you through each step of the process.</div>'
        + '<div class="iseo-guide-actions"><button class="iseo-guide-btn-skip" type="button">Skip guide</button></div>'
        + '</div>'
    );

    $tip.find('.iseo-guide-btn-skip').on('click', function () {
        $spot.remove();
        $tip.remove();
        $('body').removeClass('iseo-guide-active');
        // Navigate the card without onboarding flag
        $card.attr('href', $card.attr('href').replace('&from=onboarding', ''));
        $('.create-ai-col[style]').removeAttr('style');
    });

    function positionGuide() {
        if (!$card.length) return;
        var r   = $card[0].getBoundingClientRect();
        var pad = 10;
        $spot.css({
            top:    (r.top    - pad) + 'px',
            left:   (r.left   - pad) + 'px',
            width:  (r.width  + pad * 2) + 'px',
            height: (r.height + pad * 2) + 'px'
        });
        var ttW = 300;
        var ttH = $tip.outerHeight(true) || 200;
        var top  = r.bottom + 14;
        var left = r.left + r.width / 2 - ttW / 2;
        var vw   = window.innerWidth, vh = window.innerHeight;
        top  = Math.max(10, Math.min(top,  vh - ttH - 10));
        left = Math.max(10, Math.min(left, vw - ttW - 10));
        $tip.css({ top: top + 'px', left: left + 'px', width: ttW + 'px' });
        $tip.attr('data-pos', 'bottom');
    }

    $('body').addClass('iseo-guide-active');
    $spot.show();

    setTimeout(function () {
        positionGuide();
        $tip.show();
    }, 200);

    $(window).on('resize scroll', function () { positionGuide(); });
});
</script>
<?php endif; ?>

<?php View::endSection('content') ?>



<?php echo View::make('layouts.main') ?>