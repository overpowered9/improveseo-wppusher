<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


use ImproveSEO\View;

$from_onboarding = isset( $_GET['from'] ) && $_GET['from'] === 'onboarding';

// Each card is reachable from its title (stretched over the whole card) and its Start button.
$single_url = admin_url( 'admin.php?page=improveseo_posting&action=create_post_single' . ( $from_onboarding ? '&from=onboarding' : '' ) );
$bulk_url   = admin_url( 'admin.php?page=improveseo_posting&action=create_post_bulk' . ( $from_onboarding ? '&from=onboarding' : '' ) );
$lists_url  = admin_url( 'admin.php?page=improveseo_lists' );

?>



<?php View::startSection('breadcrumbs') ?>

<!-- <span>Improve SEO</span> -->

<?php View::endSection('breadcrumbs') ?>



<?php View::startSection('content'); ?>

<h1 class="hidden"> Posting </h1>

<div class="global-wrap">
	<?php // One wrapper for the header, intro and cards, so they all share the same left and right edges. ?>
	<div class="iseo-create-wrap">
		<div class="head-bar iseo-create-head">
			<img src="<?php echo esc_url( improveseo_logo_url() ); ?>" alt="<?php esc_attr_e( 'ImproveSEO logo', 'improveseo' ); ?>">
			<h1><?php esc_html_e( 'What would you like to create?', 'improveseo' ); ?></h1>
		</div>
		<div class="container iseo-create">
			<?php // Intro and cards share this one wrapper, so they share the same left and right edges. ?>
			<div class="iseo-create-intro">
				<p class="iseo-create-lead"><?php esc_html_e( 'Time to create content that helps customers find you — in Google and in AI assistants like ChatGPT.', 'improveseo' ); ?></p>
				<p class="iseo-create-choose"><?php esc_html_e( 'Choose how you want to start:', 'improveseo' ); ?></p>
				<ul class="iseo-create-options">
					<li>
						<span class="iseo-create-chip iseo-create-chip--single"><?php esc_html_e( 'Single Post', 'improveseo' ); ?></span>
						<span class="iseo-create-dash" aria-hidden="true">&mdash;</span>
						<span><?php esc_html_e( 'One post built from one keyword.', 'improveseo' ); ?></span>
					</li>
					<li>
						<span class="iseo-create-chip iseo-create-chip--bulk"><?php esc_html_e( 'Bulk Post Project', 'improveseo' ); ?></span>
						<span class="iseo-create-dash" aria-hidden="true">&mdash;</span>
						<span><?php esc_html_e( 'Multiple posts built from a keyword list.', 'improveseo' ); ?></span>
					</li>
				</ul>
				<p class="iseo-create-help"><?php
					$iseo_i_icon = '<svg class="iseo-create-i" width="15" height="15" viewBox="0 0 16 16" role="img" aria-label="' . esc_attr__( 'information', 'improveseo' ) . '"><circle cx="8" cy="8" r="8" fill="#E9EDF2"></circle><line x1="8" y1="7.2" x2="8" y2="11.4" stroke="#5C6B7A" stroke-width="1.5" stroke-linecap="round"></line><circle cx="8" cy="4.7" r="0.95" fill="#5C6B7A"></circle></svg>';
					/* translators: %s: the (i) information icon */
					echo sprintf( esc_html__( 'Either way, a short wizard walks you through every step. Look for the %s icon — it explains what each step does and why it matters.', 'improveseo' ), $iseo_i_icon ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG; the text is escaped.
				?></p>
				<div class="iseo-create-callout">
					<svg class="iseo-create-callout-icon" width="22" height="22" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="12" fill="#1C7293"></circle><circle cx="12" cy="12" r="7.5" fill="none" stroke="#fff" stroke-width="1.6"></circle><line x1="12" y1="11" x2="12" y2="15.5" stroke="#fff" stroke-width="1.8" stroke-linecap="round"></line><circle cx="12" cy="8.3" r="1.1" fill="#fff"></circle></svg>
					<p><?php
						/* translators: %s: "Create one here" link to the Keyword Lists screen */
						echo sprintf( esc_html__( 'Bulk Post Projects need a keyword list. Don’t have one? %s', 'improveseo' ), '<a href="' . esc_url( $lists_url ) . '">' . esc_html__( 'Create one here', 'improveseo' ) . '</a>' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- every part is escaped above.
					?></p>
				</div>
			</div>

			<?php // The card title link is stretched over the whole card (::after), so clicking anywhere on
			      // the card opens it. The (i) marker and the Start button sit above that overlay and
			      // outside every link, so clicking (i) only toggles its tooltip. The arrow on the button
			      // comes from .iseo-btn-arrow, as on the Dashboard. ?>
			<div class="create-ai-post">
				<div class="create-ai">
					<div class="create-ai-col">
						<div class="iseo-create-art">
							<svg viewBox="0 0 360 172" role="img" aria-label="<?php esc_attr_e( 'One keyword, Lawn Mowing, becomes one published post', 'improveseo' ); ?>">
								<rect x="10" y="58" width="150" height="56" rx="28" fill="#fff" stroke="#1C7293" stroke-width="2"></rect>
								<circle cx="40" cy="86" r="18" fill="#1C7293" fill-opacity="0.12"></circle>
								<path d="M47 80H37l-6 6 6 6h10z" fill="#1C7293"></path>
								<circle cx="38" cy="86" r="1.8" fill="#fff"></circle>
								<text x="66" y="84" font-size="13" font-weight="600" fill="#0B132B"><?php esc_html_e( 'Lawn Mowing', 'improveseo' ); ?></text>
								<text x="66" y="101" font-size="10" fill="#5C6B7A"><?php esc_html_e( '1 Keyword', 'improveseo' ); ?></text>
								<line x1="166" y1="86" x2="214" y2="86" stroke="#1C7293" stroke-width="2" stroke-dasharray="5 4"></line>
								<circle cx="190" cy="86" r="7" fill="#1C7293" opacity="0.18"></circle>
								<circle cx="190" cy="86" r="4" fill="#1C7293"></circle>
								<path d="M214 80l8 6-8 6z" fill="#1C7293"></path>
								<rect x="230" y="16" width="120" height="140" rx="9" fill="#fff" stroke="#D5DEE6"></rect>
								<path d="M230 25a9 9 0 0 1 9-9h102a9 9 0 0 1 9 9v25H230z" fill="#1C7293" fill-opacity="0.12"></path>
								<rect x="240" y="24" width="26" height="18" rx="2" fill="#fff" stroke="#1C7293" stroke-opacity="0.45"></rect>
								<path d="M243 39l6-6 4 4 4-3 6 5z" fill="#1C7293" opacity="0.7"></path>
								<circle cx="260" cy="28.5" r="1.8" fill="#1C7293" opacity="0.7"></circle>
								<rect x="274" y="28" width="64" height="4" rx="2" fill="#1C7293" opacity="0.55"></rect>
								<rect x="274" y="37" width="44" height="4" rx="2" fill="#1C7293" opacity="0.3"></rect>
								<rect x="242" y="64" width="96" height="6" rx="3" fill="#0B132B"></rect>
								<rect x="242" y="78" width="96" height="4" rx="2" fill="#D5DEE6"></rect>
								<rect x="242" y="88" width="80" height="4" rx="2" fill="#D5DEE6"></rect>
								<rect x="242" y="98" width="90" height="4" rx="2" fill="#D5DEE6"></rect>
								<rect x="242" y="118" width="72" height="20" rx="10" fill="#E6F8F5" stroke="#2EC4B6"></rect>
								<circle cx="253" cy="128" r="5" fill="#2EC4B6"></circle>
								<path d="M250.8 128.1l1.6 1.6 2.9-3.1" fill="none" stroke="#fff" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
								<text x="262" y="131.5" font-size="9.5" font-weight="600" fill="#138A7E"><?php esc_html_e( 'Published', 'improveseo' ); ?></text>
							</svg>
						</div>
						<h3 class="iseo-create-title">
							<a class="Posting__post-button iseo-create-card-link" href="<?php echo esc_url( $single_url ); ?>"><?php esc_html_e( 'Create Single Post', 'improveseo' ); ?></a>
							<span class="iseo-info-tip" tabindex="0" role="button" aria-label="<?php esc_attr_e( 'What is a Single Post?', 'improveseo' ); ?>">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
								<span class="iseo-info-tip-bubble iseo-info-tip-bubble--above" role="tooltip"><?php esc_html_e( 'Create a single post based on a chosen seed keyword', 'improveseo' ); ?></span>
							</span>
						</h3>
						<a class="Posting__post-button iseo-btn iseo-btn-solid iseo-btn-arrow iseo-create-cta" href="<?php echo esc_url( $single_url ); ?>"><?php esc_html_e( 'Start single post', 'improveseo' ); ?></a>
					</div>

					<div class="create-ai-col">
						<div class="iseo-create-art">
							<svg viewBox="0 0 360 172" role="img" aria-label="<?php esc_attr_e( 'A keyword list of four keywords becomes four ready posts', 'improveseo' ); ?>">
								<rect x="6" y="8" width="128" height="156" rx="8" fill="#fff" stroke="#CDD6DF"></rect>
								<text x="16" y="27" font-size="8.5" font-weight="600" fill="#5C6B7A" letter-spacing="0.4"><?php esc_html_e( 'KEYWORD LIST (4)', 'improveseo' ); ?></text>
								<rect x="14" y="36" width="112" height="24" rx="5" fill="#1C7293" fill-opacity="0.12" stroke="#1C7293" stroke-opacity="0.45"></rect>
								<circle cx="25" cy="48" r="3" fill="#1C7293"></circle>
								<text x="34" y="51.5" font-size="9" font-weight="600" fill="#0B132B"><?php esc_html_e( 'Lawn Mowing', 'improveseo' ); ?></text>
								<rect x="14" y="66" width="112" height="24" rx="5" fill="#F5F7F9" stroke="#E9EDF2"></rect>
								<circle cx="25" cy="78" r="3" fill="#B9C3CC"></circle>
								<text x="34" y="81.5" font-size="9" fill="#5C6B7A"><?php esc_html_e( 'Weed Control', 'improveseo' ); ?></text>
								<rect x="14" y="96" width="112" height="24" rx="5" fill="#F5F7F9" stroke="#E9EDF2"></rect>
								<circle cx="25" cy="108" r="3" fill="#B9C3CC"></circle>
								<text x="34" y="111.5" font-size="9" fill="#5C6B7A"><?php esc_html_e( 'Lawn Aeration', 'improveseo' ); ?></text>
								<rect x="14" y="126" width="112" height="24" rx="5" fill="#F5F7F9" stroke="#E9EDF2"></rect>
								<circle cx="25" cy="138" r="3" fill="#B9C3CC"></circle>
								<text x="34" y="141.5" font-size="9" fill="#5C6B7A"><?php esc_html_e( 'Grass Seeding', 'improveseo' ); ?></text>
								<path d="M136 60L158 86L136 112" fill="none" stroke="#1C7293" stroke-width="1.8" stroke-linejoin="round"></path>
								<g stroke="#1C7293" stroke-width="4" stroke-linecap="round">
									<line x1="187" y1="86" x2="193" y2="86"></line>
									<line x1="183.8" y1="93.8" x2="188" y2="98"></line>
									<line x1="176" y1="97" x2="176" y2="103"></line>
									<line x1="168.2" y1="93.8" x2="164" y2="98"></line>
									<line x1="165" y1="86" x2="159" y2="86"></line>
									<line x1="168.2" y1="78.2" x2="164" y2="74"></line>
									<line x1="176" y1="75" x2="176" y2="69"></line>
									<line x1="183.8" y1="78.2" x2="188" y2="74"></line>
								</g>
								<circle cx="176" cy="86" r="12" fill="#1C7293"></circle>
								<circle cx="176" cy="86" r="4.5" fill="#fff"></circle>
								<line x1="198" y1="86" x2="216" y2="86" stroke="#1C7293" stroke-width="2" stroke-dasharray="4 3"></line>
								<path d="M216 81l7 5-7 5z" fill="#1C7293"></path>
								<rect x="250" y="10" width="100" height="146" rx="8" fill="#EEF2F6" stroke="#CDD6DF"></rect>
								<rect x="242" y="17" width="100" height="146" rx="8" fill="#F5F7F9" stroke="#CDD6DF"></rect>
								<rect x="234" y="24" width="100" height="146" rx="8" fill="#fff" stroke="#1C7293" stroke-width="1.5"></rect>
								<rect x="294" y="3" width="60" height="18" rx="9" fill="#1C7293"></rect>
								<text x="324" y="15.5" font-size="9" font-weight="700" fill="#fff" text-anchor="middle"><?php esc_html_e( '4 POSTS', 'improveseo' ); ?></text>
								<rect x="244" y="38" width="60" height="5" rx="2.5" fill="#0B132B"></rect>
								<rect x="244" y="49" width="80" height="3" rx="1.5" fill="#D5DEE6"></rect>
								<rect x="244" y="57" width="60" height="3" rx="1.5" fill="#D5DEE6"></rect>
								<?php foreach ( array( 70, 94, 118, 142 ) as $iseo_i => $iseo_y ) : ?>
								<rect x="244" y="<?php echo (int) $iseo_y; ?>" width="80" height="20" rx="4" fill="#E9F9F5" stroke="#2EC4B6"></rect>
								<circle cx="254" cy="<?php echo (int) $iseo_y + 10; ?>" r="5" fill="#2EC4B6"></circle>
								<path d="M251.8 <?php echo (int) $iseo_y + 10.1; ?>l1.6 1.6 2.9-3.1" fill="none" stroke="#fff" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
								<text x="263" y="<?php echo (int) $iseo_y + 13.5; ?>" font-size="8.5" font-weight="600" fill="#138A7E"><?php
									/* translators: %d: post number, 1 to 4 */
									echo esc_html( sprintf( __( 'Post %d Ready', 'improveseo' ), $iseo_i + 1 ) );
								?></text>
								<?php endforeach; ?>
							</svg>
						</div>
						<h3 class="iseo-create-title">
							<a class="Posting__page-button iseo-create-card-link" href="<?php echo esc_url( $bulk_url ); ?>"><?php esc_html_e( 'Create Bulk Post Project', 'improveseo' ); ?></a>
							<span class="iseo-info-tip" tabindex="0" role="button" aria-label="<?php esc_attr_e( 'What is a Bulk Post Project?', 'improveseo' ); ?>">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
								<span class="iseo-info-tip-bubble iseo-info-tip-bubble--above" role="tooltip"><?php
									/* translators: %s: "Create one" link to the Keyword Lists screen */
									echo sprintf( esc_html__( 'Create a project with multiple posts based on a keyword list. Don’t have a keyword list? %s before you get started.', 'improveseo' ), '<a href="' . esc_url( $lists_url ) . '">' . esc_html__( 'Create one', 'improveseo' ) . '</a>' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- every part is escaped above.
								?></span>
							</span>
						</h3>
						<a class="Posting__page-button iseo-btn iseo-btn-solid iseo-btn-arrow iseo-create-cta" href="<?php echo esc_url( $bulk_url ); ?>"><?php esc_html_e( 'Start bulk project', 'improveseo' ); ?></a>
					</div>
				</div>
			</div>

		</div>
	</div>
</div>

<script>
// The (i) markers sit inside the cards: a click on one toggles its tooltip (assets/js/main.js,
// which listens in the capture phase) and must go no further, so it never acts as a card click.
document.querySelectorAll('.iseo-create .iseo-info-tip').forEach(function (tip) {
    tip.addEventListener('click', function (e) { e.stopPropagation(); });
});
</script>

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