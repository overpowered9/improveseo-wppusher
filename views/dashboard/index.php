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
		</div>
		<?php if ( $iseo_qs_has_creds ) : ?>
		<script>
		document.addEventListener('DOMContentLoaded', function () {
			var card = document.getElementById('iseo-quickstart-card');
			if (!card || card.getAttribute('data-state') !== 'loading') { return; }

			function iseoQsShow(state) {
				card.setAttribute('data-state', state);
				card.querySelectorAll('[data-qs-msg]').forEach(function (el) {
					el.hidden = el.getAttribute('data-qs-msg') !== state;
				});
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

					iseoQsShow((total != null && total < 10) ? 'low' : 'ready');
				})
				.catch(function () { iseoQsShow('ready'); });
		});
		</script>
		<?php endif; ?>
		<div class="modules-row text-left">
			<div class="module-box">
			<a href="<?php echo esc_url( admin_url('admin.php?page=improveseo_create_single') ); ?>">
				<div class="module-icon justify-between m-0">
					<img src="<?php echo esc_url( WT_URL . '/assets/images/latest-images/icon2.svg' ); ?>" alt="icon2">
				</div>
				<div class="line"></div>
				<h3>Single AI Post </h3>
				<p>Create keyword-rich posts or pages. Preview content, schedule, and more!</p>
			</a>
			</div>
			<div class="module-box">
			<a href="">
				<div class="module-icon justify-between m-0">
					<img src="<?php echo esc_url( WT_URL . '/assets/images/latest-images/icon1.svg' ); ?>" alt="icon1">
				</div>
				<div class="line"></div>
				<h3>Meta</h3>
				<p>Create keyword-rich metadata for posts or pages. Customize with ease.</p>
				</a>
			</div>
			<div class="module-box">
			<a href="">
				<div class="module-icon justify-between m-0">
					<img src="<?php echo esc_url( WT_URL . '/assets/images/latest-images/icon3.svg' ); ?>" alt="icon3">
				</div>
				<div class="line"> </div>
				<h3>Tutorials & FAQ</h3>
				<p>User guide : Latest Updates- Improve SEO FAQ & Common Problems (and Workarounds)</p>
				</a>
			</div>
			<div class="module-box">
			<a href="#">
				<div class="module-icon justify-between m-0">
					<img src="<?php echo esc_url( WT_URL . '/assets/images/latest-images/icon4.svg' ); ?>" alt="icon4">
				</div>
				<div class="line"> </div>
				<h3>Support</h3>
				<p>Get assistance for all your ImproveSEO needs and queries.</p>
				</a>
			</div>
			<div class="module-box">
			<a href="<?php echo esc_url( admin_url('admin.php?page=improveseo_create_bulk') ); ?>">
				<div class="module-icon justify-between m-0">
					<img src="<?php echo esc_url( WT_URL . '/assets/images/latest-images/icon5.svg' ); ?>" alt="icon5">
				</div>
				<div class="line"></div>
				<h3>Bulk AI Posts </h3>
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
				<h3>Keyword Lists </h3>
				<p>Add keywords that you would like to target and use to quickly create posts for all of them. This is
					handy for lists of keywords that you would like to bulk create posts for.</p>
					</a>
			</div>
			<div class="module-box">
			<a href="<?php echo esc_url( admin_url('admin.php?page=improveseo_keyword_generator') ); ?>">
				<div class="module-icon justify-between m-0">
					<img src="<?php echo esc_url( WT_URL . '/assets/images/latest-images/icon7.svg' ); ?>" alt="icon7">
				</div>
				<div class="line"> </div>
				<h3>Keyword Generator </h3>
				<p>Add seed keyword and uses the Google autosuggest feature to generate a list of keywords. Save
					projects and put these keywords into a your list projects.</p>
					</a>
			</div>
			
			
			
		</div>
	</div>
</div>

<?php View::endSection('content') ?>

<?php View::make('layouts.main') ?>