<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


use ImproveSEO\View;

?>

<?php View::startSection('breadcrumbs') ?>

<a href="<?php echo esc_url( admin_url('admin.php?page=improveseo_dashboard') ); ?>">Improve SEO</a>

&raquo;

<span>Dashboard</span>

<?php View::endSection('breadcrumbs') ?>

<?php View::startSection('content') ?>
<?php
// Font Awesome 6 and the Google Fonts stylesheet that were hardcoded here are now enqueued
// in includes/assets.php (improveseo_enqueue_vendor_assets). Font Awesome is served from
// assets/vendor/; Google Fonts stays remote, which wp.org permits.
?>

<h1 class="hidden">Dashboard</h1>

<div class="global-wrap">
	<div class="improve-seo-container">
		<div class="head-bar">
			<img src="<?php echo esc_url( improveseo_logo_url() ); ?>" alt="ImproveSEO logo">
			<h1>ImproveSEO | <?php echo esc_html( IMPROVESEO_VERSION ); ?></h1>
		</div>
		<div class="box-top">
			<ul class="breadcrumb-seo">
				<li><a href="<?php echo esc_url( admin_url('admin.php?page=improveseo_dashboard') ); ?>">Improve SEO</a></li>
				<li>Modules</li>
			</ul>
		</div>
		<?php
		// Quick Start card — one glance at "can I create content right now?", which the module
		// cards below never answered: they look identical whether the site is connected, out
		// of credits, or neither.
		//
		// Whether credentials exist at all is known locally (get_option, no network), so a
		// disconnected site renders its final state immediately. Whether the account actually
		// has enough credits is not local — that needs the admin server — so a connected site
		// renders a "checking" state and JS fills in "ready" or "low credits" the same way
		// Settings already does: the existing test_improveseo_connection AJAX action (see
		// includes/ajax.php and its use in views/settings/index.php), so a cold admin server
		// never blocks this page from loading, and there is one definition of "connected" /
		// one place credit totals are parsed, not a second copy here.
		$iseo_qs_creds       = improveseo_connection_credentials();
		$iseo_qs_has_creds   = ($iseo_qs_creds['api_key'] !== '' && $iseo_qs_creds['site_code'] !== '');
		$iseo_qs_state       = $iseo_qs_has_creds ? 'loading' : 'disconnected';
		$iseo_qs_create_url  = admin_url('admin.php?page=improveseo_posting');
		$iseo_qs_connect_url = admin_url('admin.php?page=improveseo_settings#iseo-connect-guide');
		$iseo_qs_plans_url   = 'https://account.improveseoplugin.com/credits?view=plans';
		?>
		<div class="iseo-quickstart-row">
			<div class="module-box iseo-quickstart-card" id="iseo-quickstart-card" data-state="<?php echo esc_attr( $iseo_qs_state ); ?>">
				<div class="iseo-quickstart-icon" aria-hidden="true">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 15l-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"></path><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"></path><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"></path><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"></path></svg>
				</div>
				<div class="iseo-quickstart-body">
					<h3 class="iseo-quickstart-title">Quick Start</h3>

					<p class="iseo-quickstart-msg" data-qs-msg="loading" <?php echo ( 'loading' === $iseo_qs_state ) ? '' : 'hidden'; ?>>
						Checking your account&hellip;
					</p>

					<p class="iseo-quickstart-msg" data-qs-msg="ready" <?php echo ( 'ready' === $iseo_qs_state ) ? '' : 'hidden'; ?>>
						Create local SEO content now!
						<a href="<?php echo esc_url( $iseo_qs_create_url ); ?>" class="iseo-quickstart-link">Create now</a>
					</p>

					<p class="iseo-quickstart-msg" data-qs-msg="low" <?php echo ( 'low' === $iseo_qs_state ) ? '' : 'hidden'; ?>>
						It looks like you are low on credits.
						<a href="<?php echo esc_url( $iseo_qs_plans_url ); ?>" class="iseo-quickstart-link" target="_blank" rel="noopener noreferrer">Get more credits now</a>
						to create content!
					</p>

					<p class="iseo-quickstart-msg" data-qs-msg="disconnected" <?php echo ( 'disconnected' === $iseo_qs_state ) ? '' : 'hidden'; ?>>
						It looks like this website is not connected to your ImproveSEO user account yet.
						<a href="<?php echo esc_url( $iseo_qs_connect_url ); ?>" class="iseo-quickstart-link">Connect now</a>
						to create content!
					</p>

					<?php if ( $iseo_qs_has_creds ) : ?>
					<!-- No-JS fallback: with JS disabled the credit check never runs, so show the
					     optimistic "ready" message (we DO know credentials exist) instead of leaving
					     the page stuck on "Checking your account…" forever. -->
					<noscript>
						<style>
							#iseo-quickstart-card [data-qs-msg="loading"] { display: none; }
							#iseo-quickstart-card [data-qs-msg="ready"]   { display: block; }
						</style>
					</noscript>
					<?php endif; ?>
				</div>
			</div>

			<?php
			// Guided Start — a second, optional card next to Quick Start: "I don't just want to
			// create content, I want a guided tour of how." Only makes sense once Quick Start's
			// own check has settled on 'ready' (connected AND enough credits), so it starts
			// hidden and JS reveals it — it can never be shown server-side, credits are never
			// known at render time. No no-JS fallback here (unlike Quick Start): this is a
			// nice-to-have, not the answer to "can I create content", so with JS off it simply
			// stays hidden rather than guessing an account state it cannot verify.
			//
			// The "modified onboarding flow" is not a new flow: it's the SAME guided,
			// tooltip-driven tour of the single-post wizard that step 5 of the original setup
			// wizard already links to (assets/js/onboarding.js' firstContentUrl) — see
			// assets/js/onboarding-guide.js and its activation in
			// views/posting/create-post-single.php ($_GET['from'] === 'onboarding'). Reusing
			// that URL means there is one guided tour, entered from two places, not a second
			// one to keep in sync.
			$iseo_gs_guide_url = admin_url('admin.php?page=improveseo_posting&from=onboarding');
			?>
			<div class="module-box iseo-quickstart-card iseo-guidedstart-card" id="iseo-guidedstart-card" hidden>
				<div class="iseo-quickstart-icon" aria-hidden="true">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="3 11 22 2 13 21 11 13 3 11"></polygon></svg>
				</div>
				<div class="iseo-quickstart-body">
					<h3 class="iseo-quickstart-title">Guided Start</h3>
					<p class="iseo-quickstart-msg" data-qs-msg="ready">
						Still learning how to get started? Create content with our step-by-step Wizard Guide.
						<a href="<?php echo esc_url( $iseo_gs_guide_url ); ?>" class="iseo-quickstart-link">Start the guide</a>
					</p>
				</div>
			</div>

			<?php
			// Support cards — styled and worded to match the account CMS's own "New here? /
			// Need a hand?" pair (user-cms/src/components/Dashboard.js), not a new design: same
			// warm-card background, same orange accent, same copy, same destinations (the CMS's
			// Support page holds both the Knowledge Base/tutorials and the ticket form). Always
			// shown — unlike Quick Start/Guided Start, "get help" doesn't depend on whether the
			// site is connected or has credits.
			$iseo_support_kb_url     = 'https://account.improveseoplugin.com/support';
			$iseo_support_ticket_url = 'https://account.improveseoplugin.com/support?newTicket=1';
			?>
			<div class="module-box iseo-quickstart-card iseo-support-card">
				<div class="iseo-quickstart-icon iseo-support-icon" aria-hidden="true">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polygon points="10 8 16 12 10 16 10 8"></polygon></svg>
				</div>
				<div class="iseo-quickstart-body">
					<h3 class="iseo-quickstart-title">New here?</h3>
					<p class="iseo-quickstart-msg">Watch the 4-minute setup walkthrough or browse the Knowledge Base.</p>
					<a href="<?php echo esc_url( $iseo_support_kb_url ); ?>" class="iseo-support-cta" target="_blank" rel="noopener noreferrer">Open tutorials</a>
				</div>
			</div>
			<div class="module-box iseo-quickstart-card iseo-support-card">
				<div class="iseo-quickstart-icon iseo-support-icon" aria-hidden="true">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2z"></path><line x1="9" y1="4" x2="9" y2="20"></line></svg>
				</div>
				<div class="iseo-quickstart-body">
					<h3 class="iseo-quickstart-title">Need a hand?</h3>
					<p class="iseo-quickstart-msg">Typical reply within one business day.</p>
					<a href="<?php echo esc_url( $iseo_support_ticket_url ); ?>" class="iseo-support-cta" target="_blank" rel="noopener noreferrer">Submit a ticket</a>
				</div>
			</div>
		</div>
		<?php if ( $iseo_qs_has_creds ) : ?>
		<script>
		document.addEventListener('DOMContentLoaded', function () {
			var card       = document.getElementById('iseo-quickstart-card');
			var guideCard  = document.getElementById('iseo-guidedstart-card');
			if (!card || card.getAttribute('data-state') !== 'loading') { return; }

			function iseoQsShow(state) {
				card.setAttribute('data-state', state);
				card.querySelectorAll('[data-qs-msg]').forEach(function (el) {
					el.hidden = el.getAttribute('data-qs-msg') !== state;
				});
				// Guided Start only makes sense once we know the account is connected AND has
				// enough credits — the same 'ready' state Quick Start's own message uses.
				if (guideCard) { guideCard.hidden = (state !== 'ready'); }
			}

			var data = new FormData();
			data.append('action', 'test_improveseo_connection');
			data.append('api_key', <?php echo wp_json_encode( $iseo_qs_creds['api_key'] ); ?>);
			data.append('site_code', <?php echo wp_json_encode( $iseo_qs_creds['site_code'] ); ?>);
			data.append('nonce', <?php echo wp_json_encode( wp_create_nonce('test_connection_nonce') ); ?>);

			fetch(<?php echo wp_json_encode( admin_url('admin-ajax.php') ); ?>, { method: 'POST', body: data })
				.then(function (r) { return r.json(); })
				.then(function (result) {
					if (!result || !result.success) {
						// 401/403 means the stored credentials no longer work (key rotated, site
						// removed on the CMS) — everything else (timeout, 5xx, offline) says
						// nothing about whether we're connected, so don't accuse the user of being
						// disconnected over a network hiccup; default to letting them try to create.
						var status = result && result.data && result.data.status;
						iseoQsShow((status === 401 || status === 403) ? 'disconnected' : 'ready');
						return;
					}

					// Same fields the settings panel reads (views/settings/index.php,
					// renderConnectionPanel) — one place this total is parsed.
					var d     = result.data || {};
					var cd    = (d.credit_details && d.credit_details.content) ? d.credit_details.content : null;
					var total = null;
					if (cd && cd.total != null) { total = cd.total; }
					else if (d.credits && d.credits.total != null) { total = d.credits.total; }
					else if (d.credits && d.credits.content != null) { total = d.credits.content; }

					// IMPROVESEO_LOW_CREDIT_THRESHOLD (includes/connection-status.php) — the same
					// number the site-wide low-credits notice uses, so "low" never means two
					// different things on the same site.
					iseoQsShow((total != null && total < <?php echo (int) IMPROVESEO_LOW_CREDIT_THRESHOLD; ?>) ? 'low' : 'ready');
				})
				.catch(function () { iseoQsShow('ready'); });
		});
		</script>
		<?php endif; ?>
		<h2 class="iseo-section-title">Quick Links</h2>
		<div class="modules-row text-left">
			<div class="module-box">
			<a href="<?php echo esc_url( admin_url('admin.php?page=improveseo_posting') ); ?>">
				<div class="module-icon justify-between m-0">
					<img src="<?php echo esc_url( WT_URL . '/assets/images/latest-images/icon2.svg' ); ?>" alt="icon2">
				</div>
				<div class="line"></div>
				<h3>Create Posts</h3>
				<p>Create keyword-rich posts or pages. Preview content, schedule, and more!</p>
			</a>
			</div>
			<div class="module-box">
			<a href="<?php echo esc_url( admin_url('admin.php?page=improveseo_projects') ); ?>">
				<div class="module-icon justify-between m-0">
					<img src="<?php echo esc_url( WT_URL . '/assets/images/latest-images/icon1.svg' ); ?>" alt="icon1">
				</div>
				<div class="line"></div>
				<h3>Single Post Projects</h3>
				<p>View, edit, and manage every single AI-generated post or page you've created.</p>
				</a>
			</div>
			<div class="module-box">
			<a href="<?php echo esc_url( admin_url('admin.php?page=improveseo_bulkprojects') ); ?>">
				<div class="module-icon justify-between m-0">
					<img src="<?php echo esc_url( WT_URL . '/assets/images/latest-images/icon5.svg' ); ?>" alt="icon5">
				</div>
				<div class="line"></div>
				<h3>Bulk Post Projects</h3>
				<p>Create projects. Option to duplicate project, update all published content, download content URLs to
					desktop, delete all posts/pages and project</p>
					</a>
			</div>
			<div class="module-box">
			<a href="<?php echo esc_url( admin_url('admin.php?page=improveseo_lists') ); ?>">
				<div class="module-icon justify-between m-0">
					<img src="<?php echo esc_url( WT_URL . '/assets/images/latest-images/icon6.svg' ); ?>" alt="icon6">
				</div>
				<div class="line"> </div>
				<h3>Keyword Lists &amp; Tool</h3>
				<p>Add keywords you want to target and use Google autosuggest to build keyword lists you can bulk
					create posts from.</p>
					</a>
			</div>
		</div>
	</div>
</div>

<?php View::endSection('content') ?>

<?php View::make('layouts.main') ?>