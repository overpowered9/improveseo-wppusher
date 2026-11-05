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
			</div>

			<?php // The card title link is stretched over the whole card (::after), so clicking anywhere on
			      // the card opens it. The (i) marker and the Start button sit above that overlay and
			      // outside every link, so clicking (i) only toggles its tooltip. The arrow on the button
			      // comes from .iseo-btn-arrow, as on the Dashboard. ?>
			<div class="create-ai-post">
				<div class="create-ai">
					<div class="create-ai-col iseo-create-col iseo-create-col--single">
						<?php // Header: coloured icon tile on the left, title, (i) and one-line description beside it. ?>
						<div class="iseo-create-cardhead">
							<span class="iseo-create-tile" aria-hidden="true">
								<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="8" y1="13" x2="16" y2="13"></line><line x1="8" y1="17" x2="14" y2="17"></line></svg>
							</span>
							<div class="iseo-create-headtext">
								<h3 class="iseo-create-title">
									<a class="Posting__post-button iseo-create-card-link" href="<?php echo esc_url( $single_url ); ?>"><?php esc_html_e( 'Create Single Post', 'improveseo' ); ?></a>
									<span class="iseo-info-tip" tabindex="0" role="button" aria-label="<?php esc_attr_e( 'What is a Single Post?', 'improveseo' ); ?>">
										<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
										<span class="iseo-info-tip-bubble iseo-info-tip-bubble--above" role="tooltip"><?php esc_html_e( 'Create a single post based on a chosen seed keyword', 'improveseo' ); ?></span>
									</span>
								</h3>
								<p class="iseo-create-desc"><?php esc_html_e( 'One post built from one keyword.', 'improveseo' ); ?></p>
							</div>
						</div>
						<?php // Keyword search pill -> curved arrow -> post in a browser window. The arrow ends
						      // 10px short of the window's left edge, so it never crosses the post. ?>
						<div class="iseo-create-art">
							<svg viewBox="0 0 400 220" role="img" aria-label="<?php esc_attr_e( 'One keyword, Lawn mowing tips, becomes one published post', 'improveseo' ); ?>">
								<defs>
									<filter id="iseo-single-shadow" x="-20%" y="-20%" width="140%" height="150%"><feDropShadow dx="0" dy="4" stdDeviation="5" flood-color="#0B132B" flood-opacity="0.10"></feDropShadow></filter>
									<clipPath id="iseo-single-img"><rect x="238" y="100" width="122" height="58" rx="6"></rect></clipPath>
								</defs>
								<g filter="url(#iseo-single-shadow)">
									<rect x="8" y="86" width="180" height="48" rx="24" fill="#fff" stroke="#D3E8F2"></rect>
									<rect x="224" y="24" width="150" height="176" rx="10" fill="#fff" stroke="#DCE7EF"></rect>
								</g>
								<circle cx="34" cy="110" r="16" fill="#1C7293"></circle>
								<circle cx="32" cy="108" r="6" fill="none" stroke="#fff" stroke-width="2.4"></circle>
								<line x1="36.5" y1="112.5" x2="41" y2="117" stroke="#fff" stroke-width="2.4" stroke-linecap="round"></line>
								<text x="60" y="115" font-size="13" font-weight="600" fill="#0B132B"><?php esc_html_e( 'Lawn mowing tips', 'improveseo' ); ?></text>
								<path d="M96 144C110 186 176 190 212 148" fill="none" stroke="#1C8FC4" stroke-width="3.5" stroke-linecap="round"></path>
								<path d="M202.6 151.3L212 148L210.2 157.8" fill="none" stroke="#1C8FC4" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"></path>
								<path d="M224 34a10 10 0 0 1 10-10h130a10 10 0 0 1 10 10v12H224z" fill="#DDF0FA"></path>
								<circle cx="238" cy="35" r="3" fill="#7CC4E4"></circle>
								<circle cx="248" cy="35" r="3" fill="#7CC4E4"></circle>
								<rect x="238" y="58" width="96" height="6" rx="3" fill="#CBD8E2"></rect>
								<rect x="238" y="70" width="116" height="5" rx="2.5" fill="#DCE5EC"></rect>
								<rect x="238" y="81" width="80" height="5" rx="2.5" fill="#DCE5EC"></rect>
								<g clip-path="url(#iseo-single-img)">
									<rect x="238" y="100" width="122" height="58" fill="#D6EEFB"></rect>
									<circle cx="342" cy="114" r="6" fill="#FFC94D"></circle>
									<path d="M238 140C258 124 276 126 294 136C312 124 336 122 360 134V158H238z" fill="#8FD9A0"></path>
									<path d="M238 150C262 138 290 140 312 148C330 142 346 142 360 146V158H238z" fill="#34B36A"></path>
								</g>
								<rect x="238" y="168" width="110" height="5" rx="2.5" fill="#DCE5EC"></rect>
								<rect x="238" y="179" width="76" height="5" rx="2.5" fill="#DCE5EC"></rect>
								<circle cx="370" cy="192" r="16" fill="#fff"></circle>
								<circle cx="370" cy="192" r="12.5" fill="#22B07D"></circle>
								<path d="M364.5 192.2l3.8 3.8 7-7.4" fill="none" stroke="#fff" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"></path>
								<g stroke="#1C8FC4" stroke-width="3" stroke-linecap="round">
									<line x1="379" y1="15" x2="382" y2="5"></line>
									<line x1="386" y1="22" x2="394" y2="16"></line>
									<line x1="387" y1="31" x2="396" y2="31"></line>
								</g>
							</svg>
						</div>
						<a class="Posting__post-button iseo-btn iseo-btn-solid iseo-btn-arrow iseo-create-cta" href="<?php echo esc_url( $single_url ); ?>"><?php esc_html_e( 'Start single post', 'improveseo' ); ?></a>
					</div>

					<div class="create-ai-col iseo-create-col iseo-create-col--bulk">
						<div class="iseo-create-cardhead">
							<span class="iseo-create-tile" aria-hidden="true">
								<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 12 12 17 22 12"></polyline><polyline points="2 17 12 22 22 17"></polyline></svg>
							</span>
							<div class="iseo-create-headtext">
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
								<p class="iseo-create-desc"><?php esc_html_e( 'Multiple posts built from a keyword list.', 'improveseo' ); ?></p>
							</div>
						</div>
						<?php // Keyword sheet -> curved arrow -> stack of posts. The arrow runs only in the gap
						      // between the sheet and the stack, touching neither. ?>
						<div class="iseo-create-art">
							<svg viewBox="0 0 400 220" role="img" aria-label="<?php esc_attr_e( 'A keyword list of five keywords becomes a stack of posts', 'improveseo' ); ?>">
								<defs>
									<filter id="iseo-bulk-shadow" x="-20%" y="-20%" width="140%" height="150%"><feDropShadow dx="0" dy="4" stdDeviation="5" flood-color="#0B132B" flood-opacity="0.10"></feDropShadow></filter>
									<clipPath id="iseo-bulk-img"><rect x="248" y="74" width="96" height="56" rx="6"></rect></clipPath>
								</defs>
								<g filter="url(#iseo-bulk-shadow)">
									<rect x="6" y="20" width="160" height="182" rx="10" fill="#fff" stroke="#EBE3D7"></rect>
									<rect x="254" y="20" width="124" height="150" rx="10" fill="#CFE6F2"></rect>
									<rect x="244" y="30" width="124" height="150" rx="10" fill="#E4F1F8"></rect>
									<rect x="234" y="40" width="124" height="150" rx="10" fill="#fff" stroke="#E2E8EE"></rect>
								</g>
								<path d="M6 30a10 10 0 0 1 10-10h140a10 10 0 0 1 10 10v20H6z" fill="#F7F3ED"></path>
								<rect x="16" y="28" width="16" height="16" rx="3" fill="#1D8F4E"></rect>
								<text x="24" y="40" font-size="10" font-weight="700" fill="#fff" text-anchor="middle">X</text>								<?php
								$iseo_keywords = array(
									__( 'Lawn care tips', 'improveseo' ),
									__( 'Weed control', 'improveseo' ),
									__( 'Garden maintenance', 'improveseo' ),
									__( 'Seasonal landscaping', 'improveseo' ),
									__( 'Irrigation systems', 'improveseo' ),
								);
								foreach ( $iseo_keywords as $iseo_i => $iseo_kw ) :
									$iseo_y = 50 + $iseo_i * 30;
								?>
								<?php if ( $iseo_i % 2 ) : ?><rect x="7" y="<?php echo (int) $iseo_y; ?>" width="158" height="30" fill="#FBF8F4"></rect><?php endif; ?>
								<line x1="7" y1="<?php echo (int) $iseo_y; ?>" x2="165" y2="<?php echo (int) $iseo_y; ?>" stroke="#F0EAE1"></line>
								<text x="21" y="<?php echo (int) $iseo_y + 19; ?>" font-size="10" fill="#8A97A4" text-anchor="middle"><?php echo (int) $iseo_i + 1; ?></text>
								<text x="44" y="<?php echo (int) $iseo_y + 19; ?>" font-size="10.5" fill="#26303B"><?php echo esc_html( $iseo_kw ); ?></text>
								<?php endforeach; ?>
								<line x1="34" y1="50" x2="34" y2="200" stroke="#F0EAE1"></line>
								<path d="M176 112C184 152 204 150 224 124" fill="none" stroke="#F2711C" stroke-width="3.5" stroke-linecap="round"></path>
								<path d="M215.7 127.4L224 124L222.8 132.9" fill="none" stroke="#F2711C" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"></path>
								<rect x="248" y="54" width="70" height="6" rx="3" fill="#CBD8E2"></rect>
								<g clip-path="url(#iseo-bulk-img)">
									<rect x="248" y="74" width="96" height="56" fill="#D6EEFB"></rect>
									<circle cx="328" cy="87" r="5.5" fill="#FFC94D"></circle>
									<path d="M248 114C264 100 280 102 294 111C308 100 328 98 344 108V130H248z" fill="#8FD9A0"></path>
									<path d="M248 123C266 113 288 115 304 121C318 116 332 116 344 119V130H248z" fill="#34B36A"></path>
								</g>
								<rect x="248" y="142" width="90" height="5" rx="2.5" fill="#DCE5EC"></rect>
								<rect x="248" y="154" width="60" height="5" rx="2.5" fill="#DCE5EC"></rect>
								<g stroke="#F2711C" stroke-width="3" stroke-linecap="round">
									<line x1="382" y1="14" x2="385" y2="4"></line>
									<line x1="388" y1="20" x2="396" y2="14"></line>
									<line x1="389" y1="29" x2="398" y2="29"></line>
								</g>
							</svg>
						</div>
						<a class="Posting__page-button iseo-btn iseo-btn-solid iseo-btn-arrow iseo-create-cta" href="<?php echo esc_url( $bulk_url ); ?>"><?php esc_html_e( 'Start bulk project', 'improveseo' ); ?></a>
						<?php // A Bulk Post Project needs a keyword list, so the way to make one sits right under its button. ?>
						<p class="iseo-create-needlist"><?php
							/* translators: %s: "Create one here" link to the Keyword Lists screen */
							echo sprintf( esc_html__( 'Need a keyword list? %s', 'improveseo' ), '<a href="' . esc_url( $lists_url ) . '">' . esc_html__( 'Create one here', 'improveseo' ) . '</a>' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- every part is escaped above.
						?></p>
					</div>
				</div>
			</div>

			<?php // Below the cards: how the wizards help, whichever card was chosen. ?>
			<p class="iseo-create-help"><?php
				$iseo_i_icon = '<svg class="iseo-create-i" width="15" height="15" viewBox="0 0 16 16" role="img" aria-label="' . esc_attr__( 'information', 'improveseo' ) . '"><circle cx="8" cy="8" r="8" fill="#E9EDF2"></circle><line x1="8" y1="7.2" x2="8" y2="11.4" stroke="#5C6B7A" stroke-width="1.5" stroke-linecap="round"></line><circle cx="8" cy="4.7" r="0.95" fill="#5C6B7A"></circle></svg>';
				/* translators: %s: the (i) information icon */
				echo sprintf( esc_html__( 'Either way, a short wizard walks you through every step. Look for the %s icon — it explains what each step does and why it matters.', 'improveseo' ), $iseo_i_icon ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG; the text is escaped.
			?></p>

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