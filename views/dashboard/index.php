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

<?php
// Line icons for the dashboard cards, all on one 24px grid with stroke="currentColor" so
// CSS alone sets their colour (navy on the light cards, white on the dark ones). Guarded
// because this view file is included, not loaded once, and a second render would
// otherwise redeclare the function.
if ( ! function_exists( 'iseo_dash_icon' ) ) {
	function iseo_dash_icon( $name, $size = 22 ) {
		$paths = array(
			'rocket'    => '<path d="M12 15l-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"></path><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"></path><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"></path><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"></path>',
			'database'  => '<ellipse cx="12" cy="5" rx="8" ry="3"></ellipse><path d="M4 5v14c0 1.66 3.58 3 8 3s8-1.34 8-3V5"></path><path d="M4 12c0 1.66 3.58 3 8 3s8-1.34 8-3"></path>',
			'pie'       => '<path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path><path d="M22 12A10 10 0 0 0 12 2v10z"></path>',
			'crown'     => '<path d="M3 8l4.5 4L12 5l4.5 7L21 8l-2 11H5z"></path>',
			'send'      => '<line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>',
			'book'      => '<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>',
			'lifebuoy'  => '<circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="4"></circle><line x1="4.93" y1="4.93" x2="9.17" y2="9.17"></line><line x1="14.83" y1="14.83" x2="19.07" y2="19.07"></line><line x1="14.83" y1="9.17" x2="19.07" y2="4.93"></line><line x1="4.93" y1="19.07" x2="9.17" y2="14.83"></line>',
			'external'  => '<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line>',
			'check'     => '<polyline points="20 6 9 17 4 12"></polyline>',
			'copy'      => '<rect x="9" y="9" width="13" height="13" rx="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>',
			'chart'     => '<line x1="3" y1="21" x2="21" y2="21"></line><rect x="5" y="12" width="3" height="6"></rect><rect x="10.5" y="7" width="3" height="11"></rect><rect x="16" y="3" width="3" height="15"></rect>',
			'building'  => '<rect x="4" y="3" width="16" height="18" rx="1"></rect><path d="M10 21v-4h4v4"></path><line x1="8" y1="7" x2="10" y2="7"></line><line x1="14" y1="7" x2="16" y2="7"></line><line x1="8" y1="11" x2="10" y2="11"></line><line x1="14" y1="11" x2="16" y2="11"></line>',
			'window'    => '<rect x="3" y="4" width="18" height="16" rx="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="7" y1="13" x2="17" y2="13"></line><line x1="7" y1="16.5" x2="13" y2="16.5"></line>',
			'document'  => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="8" y1="13" x2="16" y2="13"></line><line x1="8" y1="17" x2="14" y2="17"></line>',
			'documents' => '<rect x="8" y="7" width="13" height="15" rx="2"></rect><path d="M16 7V4a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h3"></path><line x1="11.5" y1="12" x2="17.5" y2="12"></line><line x1="11.5" y1="16" x2="15.5" y2="16"></line>',
			'list'      => '<rect x="3" y="3" width="18" height="14" rx="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line><line x1="7" y1="7.5" x2="17" y2="7.5"></line><line x1="7" y1="11.5" x2="13" y2="11.5"></line>',
			'chevron'   => '<polyline points="9 6 15 12 9 18"></polyline>',
			'chevron-down' => '<polyline points="6 9 12 15 18 9"></polyline>',
			'calendar'  => '<rect x="3" y="4" width="18" height="18" rx="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line>',
			'bolt'      => '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>',
			'shield'    => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><polyline points="9 12 11 14 15 10"></polyline>',
			'alert'     => '<path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line>',
			'plus-square' => '<rect x="3" y="3" width="18" height="18" rx="2"></rect><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line>',
			'search'    => '<circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line>',
			'chat'      => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>',
		);
		if ( ! isset( $paths[ $name ] ) ) {
			return '';
		}
		return '<svg width="' . (int) $size . '" height="' . (int) $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $paths[ $name ] . '</svg>';
	}
}
?>

<div class="global-wrap iseo-dash">
	<div class="improve-seo-container">
		<?php // Design 2 header: logo tile, name + version over the breadcrumb, connection pill on the right. ?>
		<div class="iseo-dash-header">
			<span class="iseo-dash-logo"><img src="<?php echo esc_url( improveseo_logo_url() ); ?>" alt="ImproveSEO logo"></span>
			<div class="iseo-dash-header-text">
				<h1 class="iseo-dash-title">ImproveSEO <span class="iseo-dash-version">| <?php echo esc_html( IMPROVESEO_VERSION ); ?></span></h1>
				<ul class="breadcrumb-seo">
					<li><a href="<?php echo esc_url( admin_url('admin.php?page=improveseo_dashboard') ); ?>">Improve SEO</a></li>
					<li>Modules</li>
				</ul>
			</div>
			<?php
			// Live connection status. Saved credentials alone prove nothing (a rotated key is still
			// "saved"), so the pill starts as "Checking…" and the account check below
			// (test_improveseo_connection, the same call that fills the plan and credit cards)
			// sets it: active, rejected (401/403) or unavailable (network/server error).
			// Same credentials test as $iseo_qs_has_creds below, which decides whether that check runs.
			$iseo_conn_creds = improveseo_connection_credentials();
			$iseo_conn_saved = ( $iseo_conn_creds['api_key'] !== '' && $iseo_conn_creds['site_code'] !== '' );
			?>
			<span class="iseo-dash-status" id="iseo-dash-status" role="status" data-state="<?php echo $iseo_conn_saved ? 'checking' : 'off'; ?>"><span class="iseo-dash-status-dot" aria-hidden="true"></span><span id="iseo-dash-status-text"><?php echo $iseo_conn_saved ? 'Checking connection&hellip;' : 'Not connected'; ?></span></span>
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
		// Last known balance (includes/connection-status.php, improveseo_store_credit_snapshot()),
		// so a connected site opens on its real state instead of "Checking your account…" and
		// every card in the row is drawn up front — the live check below only refreshes values
		// in place, it no longer pops cards in a few seconds after the page loads.
		$iseo_qs_snapshot    = $iseo_qs_has_creds ? get_option('improveseo_credit_snapshot') : null;
		$iseo_qs_snap_total  = ( is_array( $iseo_qs_snapshot ) && isset( $iseo_qs_snapshot['total'] ) ) ? (int) $iseo_qs_snapshot['total'] : null;
		if ( $iseo_qs_snap_total !== null ) {
			$iseo_qs_state = ( $iseo_qs_snap_total < IMPROVESEO_LOW_CREDIT_THRESHOLD ) ? 'low' : 'ready';
		}
		$iseo_qs_create_url  = admin_url('admin.php?page=improveseo_posting');
		// No #iseo-connect-guide fragment: landing mid-page, scrolled past the page's own
		// header, read as broken. Settings opens at the top like any other page.
		$iseo_qs_connect_url = admin_url('admin.php?page=improveseo_settings');
		$iseo_qs_plans_url   = 'https://account.improveseoplugin.com/credits?view=plans';

		/**
		 * The same four conditional lines Quick Start's own card (below) prints inline —
		 * factored out so the Content Metrics card's empty state ("no posts yet" reuses the
		 * same "what do I do next" line, per request) cannot drift into different wording
		 * from a second hand-copied set of <p> tags. Quick Start's own markup is left as its
		 * existing inline copy rather than switched to call this, so this addition cannot
		 * alter behaviour already shipped and verified.
		 *
		 * iseoQsShow() below updates every element carrying a given data-qs-msg value on the
		 * PAGE, not just within one container, so both copies stay in sync from one fetch.
		 */
		function iseo_render_qs_messages( $state, $create_url, $connect_url, $plans_url ) {
			?>
			<p class="iseo-quickstart-msg" data-qs-msg="loading" <?php echo ( 'loading' === $state ) ? '' : 'hidden'; ?>>
				Checking your account&hellip;
			</p>

			<p class="iseo-quickstart-msg" data-qs-msg="ready" <?php echo ( 'ready' === $state ) ? '' : 'hidden'; ?>>
				Create local SEO content now!
				<a href="<?php echo esc_url( $create_url ); ?>" class="iseo-quickstart-link">Create now</a>
			</p>

			<p class="iseo-quickstart-msg" data-qs-msg="low" <?php echo ( 'low' === $state ) ? '' : 'hidden'; ?>>
				It looks like you are low on credits.
				<a href="<?php echo esc_url( $plans_url ); ?>" class="iseo-quickstart-link" target="_blank" rel="noopener noreferrer">Get more credits now</a>
				to create content!
			</p>

			<p class="iseo-quickstart-msg" data-qs-msg="disconnected" <?php echo ( 'disconnected' === $state ) ? '' : 'hidden'; ?>>
				It looks like this website is not connected to your ImproveSEO user account yet.
				<a href="<?php echo esc_url( $connect_url ); ?>" class="iseo-quickstart-link">Connect now</a>
				to create content!
			</p>
			<?php
		}
		?>
		<?php
		// ── Row 2: Content Metrics (left, two-card width) + Review Business Details
		// (right, one-card width) ───────────────────────────────────────────────────
		//
		// Content Metrics counts every piece of content this plugin made: the posts it built,
		// plus drafts and schedules that are not WordPress posts yet (Single "Save As Draft"
		// projects, Bulk draft and scheduled tasks). Preview drafts are left out. See
		// improveseo_dashboard_content_metrics() in modules/dashboard.php. Entirely local
		// WordPress data — no network call, no waiting on the admin server, unlike
		// everything else on this page so far.
		//
		// Period picker ("Last 30 Days" etc.) — a plain GET parameter, so the choice
		// survives a reload and is shareable as a URL. The helper's total is taken before
		// the period filter, so the card can still tell "nothing in this period" apart
		// from "nothing built yet" (which shows the Quick Start messages instead).
		// Scheduled posts are always counted: their post_date is in the future, so a
		// "last N days" window would otherwise hide every one of them.
		$iseo_cm_ranges = array(
			'7'   => 'Last 7 Days',
			'30'  => 'Last 30 Days',
			'90'  => 'Last 90 Days',
			'365' => 'Last 12 Months',
			'all' => 'All Time',
		);
		$iseo_cm_range = isset( $_GET['iseo_range'] ) ? sanitize_key( wp_unslash( $_GET['iseo_range'] ) ) : '30'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only display filter.
		if ( ! isset( $iseo_cm_ranges[ $iseo_cm_range ] ) ) {
			$iseo_cm_range = '30';
		}
		$iseo_cm = improveseo_dashboard_content_metrics( 'all' === $iseo_cm_range ? 0 : (int) $iseo_cm_range );

		$iseo_cm_total       = $iseo_cm['total'];
		$iseo_cm_published   = $iseo_cm['published'];
		$iseo_cm_draft       = $iseo_cm['draft'];
		$iseo_cm_scheduled   = $iseo_cm['scheduled'];
		$iseo_cm_draft_posts = $iseo_cm['drafts'];
		$iseo_cm_latest3     = $iseo_cm['latest'];

		$iseo_cm_status_labels = array(
			'publish' => 'Published',
			'draft'   => 'Draft',
			'future'  => 'Scheduled',
		);

		// Business Details completeness — the three fields Settings' "Business Details"
		// section collects (views/settings/index.php, includes/settings.php). Only three
		// exist today, so "percent complete" is just filled-count / 3; if more fields are
		// added later this list is the one place to extend, not a separately maintained count.
		$iseo_bd_fields = array(
			'improveseo_business_type'    => 'Business Type',
			'improveseo_business_city'    => 'City / Location',
			'improveseo_business_service' => 'Main Service',
		);
		$iseo_bd_missing = array();
		foreach ( $iseo_bd_fields as $iseo_bd_option => $iseo_bd_label ) {
			if ( '' === trim( (string) get_option( $iseo_bd_option, '' ) ) ) {
				$iseo_bd_missing[] = $iseo_bd_label;
			}
		}
		$iseo_bd_filled_count = count( $iseo_bd_fields ) - count( $iseo_bd_missing );
		$iseo_bd_percent      = (int) round( ( $iseo_bd_filled_count / count( $iseo_bd_fields ) ) * 100 );
		$iseo_bd_settings_url = admin_url( 'admin.php?page=improveseo_settings#iseo-business-details' );
		?>

		<?php // Design 2 — row 1: plan, credits remaining, credits used, business details. ?>
		<div class="iseo-quickstart-row iseo-status-row">
			<?php
			// Active Plan card — upper row, right of Quick Start. Mirrors the account CMS's
			// own "Active Plan Card" (user-cms/src/components/CreditManagement.jsx): plan
			// name, next billing (or trial end) date, and the same two-button pattern
			// (derivePlanActions in utils/subscriptionState.js always returns exactly two —
			// "Manage Plan" + "Cancel Subscription" for a running paid plan, "Choose a Plan"
			// + "Get more credits" otherwise). Both buttons point at the CMS's plans page —
			// that is genuinely where "Cancel Subscription" lives (the CMS's own Cancel
			// button opens its confirm dialog from that same screen), not a plugin-side
			// workaround for lacking a real cancel action.
			//
			// Plan name goes through window.iseoPlanLabel() (views/layouts/main.php) — the
			// plugin's own existing canonical resolver, already used by Settings, so this
			// card cannot name a plan differently from anywhere else in the plugin.
			//
			// For a connected site it is drawn up front with placeholders and filled in when the
			// same AJAX call Quick Start already makes resolves — no second network request. A disconnected site has no plan data
			// to wait for, so it renders straight away in a "not connected" state pointing at
			// the same connect guide Quick Start does, rather than leaving a hole in the row.
			?>
			<div class="iseo-quickstart-card iseo-plan-card" id="iseo-plan-card" data-plan-state="<?php echo $iseo_qs_has_creds ? 'loading' : 'disconnected'; ?>">
				<?php // Design 2: "Subscription" label + plan name on the left, shield icon on the right. ?>
				<div class="iseo-stat-head">
					<div class="iseo-stat-head-text">
						<p class="iseo-stat-label">Subscription</p>
						<?php // Still filled in by JS (ACTIVE PLAN / CANCELLING / …) but no longer drawn as a badge: the
						// date line beneath the plan name already says the same thing ("Renews on" / "Access ends"). ?>
						<span class="screen-reader-text" id="iseo-plan-status"><?php echo $iseo_qs_has_creds ? 'ACTIVE PLAN' : 'NOT CONNECTED'; ?></span>
						<h3 class="iseo-quickstart-title" id="iseo-plan-name"><?php echo $iseo_qs_has_creds ? '&mdash;' : 'No active plan'; ?></h3>
					</div>
					<div class="iseo-quickstart-icon"><?php echo iseo_dash_icon( 'shield' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
				</div>
				<p class="iseo-quickstart-msg" id="iseo-plan-date"><?php echo $iseo_qs_has_creds ? '' : 'Connect this site to see your plan and credits.'; ?></p>
				<div class="iseo-plan-actions">
					<?php if ( $iseo_qs_has_creds ) : ?>
					<a href="<?php echo esc_url( $iseo_qs_plans_url ); ?>" class="iseo-btn iseo-btn-solid" id="iseo-plan-btn-primary" target="_blank" rel="noopener noreferrer">Manage Plan</a>
					<?php else : ?>
					<a href="<?php echo esc_url( $iseo_qs_connect_url ); ?>" class="iseo-btn iseo-btn-solid" id="iseo-plan-btn-primary">Connect now</a>
					<?php endif; ?>
					<a href="<?php echo esc_url( $iseo_qs_plans_url ); ?>" class="iseo-plan-textlink" id="iseo-plan-btn-secondary" target="_blank" rel="noopener noreferrer"><?php echo $iseo_qs_has_creds ? 'Cancel Subscription' : 'View plans'; ?></a>
				</div>
			</div>

			<?php
			// Credits Remaining card — upper row, left of Active Plan. Mirrors the CMS's own
			// "Total Credits Remaining" panel (user-cms/src/components/CreditManagement.jsx):
			// the balance, and when the soonest batch expires. The expiry line reuses the exact
			// wording the site-wide notice already uses (includes/connection-status.php,
			// improveseo_global_notices()) — one sentence for "credits are expiring", not two
			// that could disagree.
			//
			// Same data the site-wide notice's snapshot is built from (credit_details.content,
			// balance.next_expiry_at/amount) — read here straight from the SAME AJAX response
			// Quick Start's own check already fetches, not a second network call. Drawn up front
			// from the cached snapshot (or "—") so the row never reflows, then refreshed in place.
			//
			// "iseo-stat-card", not the older "iseo-credits-card": settings-redesign.css is
			// enqueued on every plugin screen and styles .iseo-credits-card (the Settings
			// connection panel) with a top border and spacing that leaked onto these tiles.
			$iseo_credits_url = 'https://account.improveseoplugin.com/credits';
			?>
			<?php
			$iseo_cr_expiry = '';
			if ( $iseo_qs_snap_total !== null ) {
				$iseo_cr_expiry_ts = ! empty( $iseo_qs_snapshot['next_expiry_at'] ) ? strtotime( $iseo_qs_snapshot['next_expiry_at'] . ' 00:00:00 UTC' ) : false;
				$iseo_cr_expiry    = ( $iseo_cr_expiry_ts && isset( $iseo_qs_snapshot['next_expiry_amount'] ) )
					? number_format_i18n( (int) $iseo_qs_snapshot['next_expiry_amount'] ) . ' credits expire on ' . gmdate( 'j M Y', $iseo_cr_expiry_ts )
					: 'No credits expiring soon';
			}
			?>
			<div class="iseo-quickstart-card iseo-stat-card" id="iseo-credits-remaining-card" <?php echo $iseo_qs_has_creds ? '' : 'hidden'; ?>>
				<div class="iseo-stat-head">
					<h3 class="iseo-quickstart-title">Credits Remaining</h3>
					<span class="iseo-stat-dot" aria-hidden="true"></span>
				</div>
				<div class="iseo-quickstart-body">
					<?php // Number and "credits" in separate spans so the number can be set large; same text. ?>
					<p class="iseo-quickstart-msg iseo-credits-figure" id="iseo-credits-remaining-figure"><?php
						if ( $iseo_qs_snap_total !== null ) {
							echo '<span class="iseo-credits-num">' . esc_html( number_format_i18n( $iseo_qs_snap_total ) ) . '</span> <span class="iseo-credits-unit">credits</span>';
						} else {
							echo '&mdash;';
						}
					?></p>
					<p class="iseo-quickstart-msg" id="iseo-credits-remaining-expiry"><?php echo esc_html( $iseo_cr_expiry ); ?></p>
				</div>
			</div>

			<?php
			// Credits Used card — right of Credits Remaining, still left of Active Plan.
			// Mirrors the CMS's "Credits Used This Cycle" card (CreditUsageCard.jsx), but only
			// the pooled total: the CMS's per-action split (content/images/keywords, with its
			// own segmented bar) is computed from GET /credit-usage/:user_id, an endpoint the
			// plugin has no access to — it authenticates with the api_key/site_code pair that
			// only /users/status accepts (see validateApiAccess in the admin server's routes),
			// not the Supabase session the CMS calls that endpoint with. Building that split
			// blind, against an endpoint never exercised from the plugin side, risks shipping
			// something wrong with no way to verify it here.
			//
			// What IS available on /users/status: credit_details.content.allotment (the plan's
			// per-cycle grant) and .plan_remaining (what's left of it) — allotment minus
			// plan_remaining is genuinely "spent from this cycle's allowance", just not broken
			// down by what it was spent on.
			?>
			<div class="iseo-quickstart-card iseo-stat-card" id="iseo-credits-used-card" <?php echo $iseo_qs_has_creds ? '' : 'hidden'; ?>>
				<div class="iseo-stat-head">
					<h3 class="iseo-quickstart-title">Credits Used</h3>
					<?php // Share of this cycle's allotment used; filled in by iseoPopulateCreditsUsed(). ?>
					<span class="iseo-stat-pill" id="iseo-credits-used-pct" hidden></span>
				</div>
				<div class="iseo-quickstart-body">
					<p class="iseo-quickstart-msg iseo-credits-figure" id="iseo-credits-used-figure">&mdash;</p>
					<p class="iseo-quickstart-msg" id="iseo-credits-used-note">this cycle</p>
					<div class="iseo-usage-track" id="iseo-credits-used-track" hidden><div class="iseo-usage-fill" id="iseo-credits-used-fill"></div></div>
				</div>
			</div>

			<?php // Design 2: amber accent and warning icon while details are missing. ?>
			<div class="iseo-quickstart-card iseo-bizdetails-card<?php echo empty( $iseo_bd_missing ) ? '' : ' is-incomplete'; ?>">
				<div class="iseo-stat-head">
					<div class="iseo-bizdetails-icon"><?php echo iseo_dash_icon( empty( $iseo_bd_missing ) ? 'building' : 'alert', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
					<h3 class="iseo-quickstart-title">Review Business Details</h3>
					<p class="iseo-bizdetails-progress-label"><?php echo esc_html( $iseo_bd_percent ); ?>% complete</p>
				</div>
				<div class="iseo-quickstart-body">
					<p class="iseo-quickstart-msg iseo-bizdetails-text">Make sure it's complete and always up to date, to produce the best possible content for your business.</p>

					<?php if ( ! empty( $iseo_bd_missing ) ) : ?>
						<p class="iseo-bizdetails-missing"><strong>Missing:</strong> <?php echo esc_html( implode( ', ', $iseo_bd_missing ) ); ?></p>
					<?php endif; ?>

					<div class="iseo-bizdetails-progress-track" role="progressbar" aria-valuenow="<?php echo esc_attr( $iseo_bd_percent ); ?>" aria-valuemin="0" aria-valuemax="100" aria-label="Business details completeness">
						<div class="iseo-bizdetails-progress-fill" style="width: <?php echo esc_attr( $iseo_bd_percent ); ?>%;"></div>
					</div>

					<div class="iseo-bizdetails-actions"><a href="<?php echo esc_url( $iseo_bd_settings_url ); ?>" class="iseo-btn iseo-btn-solid iseo-btn-arrow"><?php echo empty( $iseo_bd_missing ) ? 'Review details' : 'Complete details'; ?></a></div>
				</div>
			</div>
		</div>

		<?php // Row 2: Quick Start and Guided Start. ?>
		<div class="iseo-quickstart-row iseo-start-row">
			<?php
			// Every card in this row carries a chevron in its top-right corner pointing at the
			// same place as the card's own call to action. Quick Start's destination depends on
			// a state only JS learns (ready / low / disconnected), so its chevron carries all
			// three and iseoQsShow() below picks one.
			$iseo_qs_chevron_urls = array(
				'loading'      => $iseo_qs_create_url,
				'ready'        => $iseo_qs_create_url,
				'low'          => $iseo_qs_plans_url,
				'disconnected' => $iseo_qs_connect_url,
			);
			?>
			<div class="iseo-quickstart-card" id="iseo-quickstart-card" data-state="<?php echo esc_attr( $iseo_qs_state ); ?>">
				<div class="iseo-card-top">
					<div class="iseo-quickstart-icon"><?php echo iseo_dash_icon( 'bolt' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed SVG markup. ?></div>
					<a class="iseo-card-chevron" id="iseo-quickstart-chevron" href="<?php echo esc_url( $iseo_qs_chevron_urls[ $iseo_qs_state ] ); ?>"
						data-href-ready="<?php echo esc_url( $iseo_qs_chevron_urls['ready'] ); ?>"
						data-href-low="<?php echo esc_url( $iseo_qs_chevron_urls['low'] ); ?>"
						data-href-disconnected="<?php echo esc_url( $iseo_qs_chevron_urls['disconnected'] ); ?>"
						aria-label="Quick Start"><?php echo iseo_dash_icon( 'chevron', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
				</div>
				<div class="iseo-quickstart-body">
					<h3 class="iseo-quickstart-title">Quick Start</h3>

					<p class="iseo-quickstart-msg" data-qs-msg="loading" <?php echo ( 'loading' === $iseo_qs_state ) ? '' : 'hidden'; ?>>
						Checking your account&hellip;
					</p>

					<p class="iseo-quickstart-msg" data-qs-msg="ready" <?php echo ( 'ready' === $iseo_qs_state ) ? '' : 'hidden'; ?>>
						Create local SEO content now!
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

					<?php // Same button as the support cards'; carries data-qs-msg so iseoQsShow() shows it only in the 'ready' state. ?>
					<a href="<?php echo esc_url( $iseo_qs_create_url ); ?>" class="iseo-btn iseo-btn-solid iseo-support-cta iseo-btn-arrow" data-qs-msg="ready" <?php echo ( 'ready' === $iseo_qs_state ) ? '' : 'hidden'; ?>>Create now</a>

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
			<?php // Drawn up front for a connected site unless the snapshot already says "low"; the live check hides it if the account turns out not to be ready. ?>
			<div class="iseo-quickstart-card iseo-guidedstart-card" id="iseo-guidedstart-card" <?php echo ( $iseo_qs_has_creds && 'low' !== $iseo_qs_state ) ? '' : 'hidden'; ?>>
				<div class="iseo-card-top">
					<div class="iseo-quickstart-icon"><?php echo iseo_dash_icon( 'send' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
					<a class="iseo-card-chevron" href="<?php echo esc_url( $iseo_gs_guide_url ); ?>" aria-label="Guided Start"><?php echo iseo_dash_icon( 'chevron', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
				</div>
				<div class="iseo-quickstart-body">
					<h3 class="iseo-quickstart-title">Guided Start</h3>
					<p class="iseo-quickstart-msg" data-qs-msg="ready">
						Still learning how to get started? Create content with our step-by-step Wizard Guide.
					</p>
					<a href="<?php echo esc_url( $iseo_gs_guide_url ); ?>" class="iseo-btn iseo-btn-solid iseo-support-cta iseo-btn-arrow">Start the guide</a>
				</div>
			</div>
		</div>
		<?php if ( $iseo_qs_has_creds ) : ?>
		<script>
		document.addEventListener('DOMContentLoaded', function () {
			var card       = document.getElementById('iseo-quickstart-card');
			var guideCard  = document.getElementById('iseo-guidedstart-card');
			if (!card) { return; }

			function iseoQsShow(state) {
				card.setAttribute('data-state', state);
				// document-wide, not card-scoped: the Content Metrics card's empty state mirrors
				// these same four lines (iseo_render_qs_messages() in PHP above) so both copies
				// resolve from this one fetch instead of needing a second AJAX call.
				document.querySelectorAll('[data-qs-msg]').forEach(function (el) {
					el.hidden = el.getAttribute('data-qs-msg') !== state;
				});
				// Guided Start only makes sense once we know the account is connected AND has
				// enough credits — the same 'ready' state Quick Start's own message uses.
				if (guideCard) { guideCard.hidden = (state !== 'ready'); }
				// The corner chevron goes wherever this state's own message links to.
				var chevron = document.getElementById('iseo-quickstart-chevron');
				var chevronHref = chevron && chevron.getAttribute('data-href-' + state);
				if (chevronHref) { chevron.href = chevronHref; }
			}

			// "21 Oct 2026" — day-first, as the dashboard design shows it.
			function iseoFormatDate(iso) {
				if (!iso) { return null; }
				var parsed = new Date(iso);
				if (isNaN(parsed.getTime())) { return null; }
				return parsed.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
			}

			// Populated only on a successful response — a failed/unreachable check has no
			// plan data to show, so the card stays hidden rather than guessing.
			function iseoPopulatePlanCard(d) {
				var planCard = document.getElementById('iseo-plan-card');
				if (!planCard) { return; }

				var plan  = d.plan  || {};
				var sub   = d.subscription || {};
				var trial = d.trial || {};

				var label = (typeof window.iseoPlanLabel === 'function')
					? window.iseoPlanLabel(plan, d.subscription, trial)
					: (plan.name || 'Your Plan');

				var isPaid     = plan.is_paid === true;
				var cancelling = isPaid && sub && sub.cancel_at_period_end === true;

				document.getElementById('iseo-plan-name').textContent = label;

				var statusEl     = document.getElementById('iseo-plan-status');
				var dateEl       = document.getElementById('iseo-plan-date');
				var primaryBtn   = document.getElementById('iseo-plan-btn-primary');
				var secondaryBtn = document.getElementById('iseo-plan-btn-secondary');

				if (isPaid) {
					planCard.setAttribute('data-plan-state', cancelling ? 'cancelling' : 'active');
					statusEl.textContent = cancelling ? 'CANCELLING' : 'ACTIVE PLAN';

					var billDate = iseoFormatDate(sub.next_billing_date);
					dateEl.textContent = billDate ? (cancelling ? 'Access ends ' + billDate : 'Renews on ' + billDate) : '';

					primaryBtn.textContent   = 'Manage Plan';
					secondaryBtn.textContent = 'Cancel Subscription';
				} else {
					planCard.setAttribute('data-plan-state', 'free');
					statusEl.textContent = label.toUpperCase();

					var trialEnd = trial.active ? iseoFormatDate(trial.ends_at) : null;
					dateEl.textContent = trialEnd ? ('Trial ends ' + trialEnd) : '';

					primaryBtn.textContent   = 'Choose a Plan';
					secondaryBtn.textContent = 'Get more credits';
				}

				planCard.hidden = false;
			}

			// The check failed, so there is no plan to name — but the card is still shown, so
			// the row doesn't lose a slot. Rejected credentials read as "not connected" (the
			// same call Quick Start makes on 401/403); anything else just says the plan
			// couldn't be loaded, never guessing one.
			function iseoPlanCardUnavailable(disconnected) {
				var planCard = document.getElementById('iseo-plan-card');
				if (!planCard) { return; }

				var primaryBtn = document.getElementById('iseo-plan-btn-primary');
				planCard.setAttribute('data-plan-state', disconnected ? 'disconnected' : 'unavailable');
				document.getElementById('iseo-plan-status').textContent = disconnected ? 'NOT CONNECTED' : 'PLAN';
				document.getElementById('iseo-plan-name').textContent = disconnected ? 'No active plan' : 'Plan unavailable';
				document.getElementById('iseo-plan-date').textContent = disconnected
					? 'Connect this site to see your plan and credits.'
					: 'We couldn’t load your plan right now. Try again shortly.';

				if (disconnected) {
					primaryBtn.textContent = 'Connect now';
					primaryBtn.href = <?php echo wp_json_encode( $iseo_qs_connect_url ); ?>;
					primaryBtn.removeAttribute('target');

					var planChevron = document.getElementById('iseo-plan-chevron');
					if (planChevron) {
						planChevron.href = primaryBtn.href;
						planChevron.removeAttribute('target');
					}
				}
				document.getElementById('iseo-plan-btn-secondary').textContent = 'View plans';

				planCard.hidden = false;
			}

			// Same fallback chain used everywhere else in this file and in includes/ajax.php's
			// test_improveseo_connection() — one place this total is read from a response body.
			function iseoReadCreditsTotal(d) {
				var cd = (d.credit_details && d.credit_details.content) ? d.credit_details.content : null;
				if (cd && cd.total != null) { return cd.total; }
				if (d.credits && d.credits.total != null) { return d.credits.total; }
				if (d.credits && d.credits.content != null) { return d.credits.content; }
				return null;
			}

			// "5,466 credits": the number and the word in separate spans, so the number can be
			// set large and "credits" small. Same text as before.
			function iseoSetCreditsFigure(figureEl, n) {
				figureEl.textContent = '';
				if (n == null) { figureEl.textContent = '—'; return; }
				var num = document.createElement('span');
				num.className = 'iseo-credits-num';
				num.textContent = n.toLocaleString();
				var unit = document.createElement('span');
				unit.className = 'iseo-credits-unit';
				unit.textContent = 'credits';
				figureEl.appendChild(num);
				figureEl.appendChild(document.createTextNode(' '));
				figureEl.appendChild(unit);
			}

			function iseoPopulateCreditsRemaining(d) {
				var el = document.getElementById('iseo-credits-remaining-card');
				if (!el) { return; }

				var total = iseoReadCreditsTotal(d);
				iseoSetCreditsFigure(document.getElementById('iseo-credits-remaining-figure'), total);

				// Same fields, same wording as the site-wide notice (includes/connection-status.php,
				// improveseo_global_notices()) — one description of "credits are expiring soon".
				var expiryEl   = document.getElementById('iseo-credits-remaining-expiry');
				var balance    = d.balance || {};
				var expiryAt   = balance.next_expiry_at || null;
				var expiryAmt  = balance.next_expiry_amount;
				var expiryDate = iseoFormatDate(expiryAt);
				expiryEl.textContent = (expiryDate && expiryAmt != null)
					? expiryAmt.toLocaleString() + ' credits expire on ' + expiryDate
					: (total != null ? 'No credits expiring soon' : '');

				el.hidden = false;
			}

			function iseoPopulateCreditsUsed(d) {
				var el = document.getElementById('iseo-credits-used-card');
				if (!el) { return; }

				var cd        = (d.credit_details && d.credit_details.content) ? d.credit_details.content : null;
				var allotment = cd && cd.allotment != null ? cd.allotment : null;
				var planLeft  = cd && cd.plan_remaining != null ? cd.plan_remaining : null;

				var figureEl = document.getElementById('iseo-credits-used-figure');
				var noteEl   = document.getElementById('iseo-credits-used-note');

				var pctEl   = document.getElementById('iseo-credits-used-pct');
				var trackEl = document.getElementById('iseo-credits-used-track');
				var fillEl  = document.getElementById('iseo-credits-used-fill');

				if (allotment != null && allotment > 0 && planLeft != null) {
					var used = Math.max(0, allotment - planLeft);
					iseoSetCreditsFigure(figureEl, used);
					noteEl.textContent = 'of ' + allotment.toLocaleString() + ' this cycle';
					// Share of this cycle's allotment used: the pill and the bar under the figure.
					var pct = Math.min(100, Math.round((used / allotment) * 100));
					if (pctEl)   { pctEl.textContent = pct + '%'; pctEl.hidden = false; }
					if (fillEl)  { fillEl.style.width = pct + '%'; }
					if (trackEl) { trackEl.hidden = false; }
				} else {
					if (pctEl)   { pctEl.hidden = true; }
					if (trackEl) { trackEl.hidden = true; }
					// No monthly allotment to measure against — a Basic/pack-only account, or an
					// older server response missing these fields. Nothing false to report.
					figureEl.textContent = '—';
					noteEl.textContent = 'No monthly allotment on your current plan';
				}

				el.hidden = false;
			}

			// Paint the plan and credit cards straight away from the last known account state
			// (improveseo_store_credit_snapshot()); the live check below then refreshes them.
			var cached = <?php echo wp_json_encode( ( is_array( $iseo_qs_snapshot ) && ! empty( $iseo_qs_snapshot['status'] ) ) ? $iseo_qs_snapshot['status'] : null ); ?>;
			if (cached) {
				iseoPopulatePlanCard(cached);
				iseoPopulateCreditsRemaining(cached);
				iseoPopulateCreditsUsed(cached);
			}

			var data = new FormData();
			data.append('action', 'test_improveseo_connection');
			data.append('api_key', <?php echo wp_json_encode( $iseo_qs_creds['api_key'] ); ?>);
			data.append('site_code', <?php echo wp_json_encode( $iseo_qs_creds['site_code'] ); ?>);
			data.append('nonce', <?php echo wp_json_encode( wp_create_nonce('test_connection_nonce') ); ?>);

			// Header pill: the result of this live check, never just "credentials are saved".
			function iseoSetConnStatus(state) {
				var pill = document.getElementById('iseo-dash-status');
				var text = document.getElementById('iseo-dash-status-text');
				if (!pill || !text) { return; }
				var labels = {
					active:      'API Connected & Active',
					rejected:    'API connection rejected',
					unavailable: 'Connection status unavailable'
				};
				pill.setAttribute('data-state', state);
				text.textContent = labels[state];
			}

			fetch(<?php echo wp_json_encode( admin_url('admin-ajax.php') ); ?>, { method: 'POST', body: data })
				.then(function (r) { return r.json(); })
				.then(function (result) {
					if (!result || !result.success) {
						// 401/403 means the stored credentials no longer work (key rotated, site
						// removed on the CMS) — everything else (timeout, 5xx, offline) says
						// nothing about whether we're connected, so don't accuse the user of being
						// disconnected over a network hiccup; default to letting them try to create.
						var status = result && result.data && result.data.status;
						var rejected = (status === 401 || status === 403);
						iseoSetConnStatus(rejected ? 'rejected' : 'unavailable');
						iseoPlanCardUnavailable(rejected);
						iseoQsShow(rejected ? 'disconnected' : 'ready');
						return;
					}
					iseoSetConnStatus('active');

					// Same fields the settings panel reads (views/settings/index.php,
					// renderConnectionPanel) — one place this total is parsed.
					var d     = result.data || {};
					iseoPopulatePlanCard(d);
					iseoPopulateCreditsRemaining(d);
					iseoPopulateCreditsUsed(d);
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
				.catch(function () { iseoSetConnStatus('unavailable'); iseoPlanCardUnavailable(false); iseoQsShow('ready'); });
		});
		</script>
		<?php endif; ?>

		<?php // Row 3: Content Metrics, full width. ?>
		<div class="iseo-row-2">
			<div class="iseo-panel-card iseo-metrics-card">
				<div class="iseo-metrics-head">
					<div class="iseo-quickstart-icon"><?php echo iseo_dash_icon( 'chart' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
					<div class="iseo-metrics-head-text">
						<h3 class="iseo-quickstart-title">Content Metrics</h3>
						<p class="iseo-metrics-subtitle">Summary of output</p>
					</div>

					<?php if ( $iseo_cm_total > 0 ) : ?>
					<form class="iseo-range" method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>">
						<input type="hidden" name="page" value="improveseo_dashboard">
						<span class="iseo-range-icon"><?php echo iseo_dash_icon( 'calendar', 15 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<select name="iseo_range" id="iseo-range-select" aria-label="Period">
							<?php foreach ( $iseo_cm_ranges as $iseo_cm_range_key => $iseo_cm_range_label ) : ?>
								<option value="<?php echo esc_attr( $iseo_cm_range_key ); ?>" <?php selected( $iseo_cm_range, (string) $iseo_cm_range_key ); ?>><?php echo esc_html( $iseo_cm_range_label ); ?></option>
							<?php endforeach; ?>
						</select>
						<span class="iseo-range-caret"><?php echo iseo_dash_icon( 'chevron-down', 15 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<noscript><button type="submit" class="iseo-btn iseo-btn-outline">Apply</button></noscript>
					</form>
					<script>
					document.getElementById('iseo-range-select').addEventListener('change', function () { this.form.submit(); });
					</script>
					<?php endif; ?>
				</div>

				<?php if ( $iseo_cm_total > 0 ) : ?>
					<div class="iseo-metrics-stats" role="group" aria-label="Content metrics">
						<div class="iseo-metrics-stat">
							<span class="iseo-metrics-num"><?php echo esc_html( $iseo_cm_published ); ?></span>
							<span class="iseo-metrics-label">Published</span>
						</div>
						<?php if ( $iseo_cm_draft > 0 ) : ?>
						<button type="button" class="iseo-metrics-stat iseo-metrics-stat-clickable" id="iseo-drafts-toggle" aria-haspopup="dialog">
							<span class="iseo-metrics-num"><?php echo esc_html( $iseo_cm_draft ); ?></span>
							<span class="iseo-metrics-label">In Draft</span>
						</button>
						<?php else : ?>
						<div class="iseo-metrics-stat">
							<span class="iseo-metrics-num"><?php echo esc_html( $iseo_cm_draft ); ?></span>
							<span class="iseo-metrics-label">In Draft</span>
						</div>
						<?php endif; ?>
						<div class="iseo-metrics-stat">
							<span class="iseo-metrics-num"><?php echo esc_html( $iseo_cm_scheduled ); ?></span>
							<span class="iseo-metrics-label">Scheduled</span>
						</div>
					</div>

					<div class="iseo-metrics-latest">
						<div class="iseo-metrics-latest-top">
							<p class="iseo-metrics-latest-heading">Latest projects</p>
							<a class="iseo-metrics-viewall" href="<?php echo esc_url( admin_url( 'edit.php' ) ); ?>">View all posts</a>
						</div>
						<?php if ( empty( $iseo_cm_latest3 ) ) : ?>
							<p class="iseo-quickstart-msg">No posts in this period.</p>
						<?php endif; ?>
						<?php foreach ( $iseo_cm_latest3 as $iseo_cm_p ) : ?>
							<a class="iseo-metrics-latest-row" href="<?php echo esc_url( $iseo_cm_p->url ); ?>">
								<span class="iseo-metrics-latest-icon"><?php echo iseo_dash_icon( 'document', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
								<span class="iseo-metrics-latest-title"><?php echo esc_html( $iseo_cm_p->title ); ?></span>
								<span class="iseo-metrics-latest-badge iseo-metrics-badge-<?php echo esc_attr( $iseo_cm_p->status ); ?>"><?php echo esc_html( $iseo_cm_status_labels[ $iseo_cm_p->status ] ); ?></span>
							</a>
						<?php endforeach; ?>
					</div>
				<?php else : ?>
					<p class="iseo-quickstart-msg">No posts created yet.</p>
					<?php iseo_render_qs_messages( $iseo_qs_state, $iseo_qs_create_url, $iseo_qs_connect_url, $iseo_qs_plans_url ); ?>
				<?php endif; ?>
			</div>

			<?php
			// Review Business Details — right, one-card width, underneath Credits Remaining.
			// The three fields it points at live in Settings' "Business Details" section
			// (views/settings/index.php), which is what makes them usable for AI content —
			// see modules/single_AI_post_function.php's brand_profile, built from these same
			// three options.
			?>
		</div>

		<?php if ( ! empty( $iseo_cm_draft_posts ) ) : ?>
		<!-- Drafts modal — every draft this plugin made (not just the latest 3), each
		     linking to where it is edited: the post editor for a draft post, the Single
		     draft editor for a "Save As Draft" project, or the Bulk project's task list.
		     Pure local DOM toggle: the list is rendered once, server-side, from the same
		     data as the card — no AJAX round trip to open it. -->
		<div id="iseo-drafts-modal-overlay" class="iseo-drafts-modal-overlay" hidden>
			<div class="iseo-drafts-modal" role="dialog" aria-modal="true" aria-labelledby="iseo-drafts-modal-title">
				<button type="button" id="iseo-drafts-modal-close" class="iseo-drafts-modal-close" aria-label="Close">&times;</button>
				<h3 id="iseo-drafts-modal-title" class="iseo-drafts-modal-title">Draft posts (<?php echo esc_html( $iseo_cm_draft ); ?>)</h3>
				<div class="iseo-drafts-modal-list">
					<?php foreach ( $iseo_cm_draft_posts as $iseo_cm_draft_post ) : ?>
						<div class="iseo-drafts-modal-row">
							<span class="iseo-drafts-modal-row-title"><?php echo esc_html( $iseo_cm_draft_post->title ); ?></span>
							<a href="<?php echo esc_url( $iseo_cm_draft_post->url ); ?>" class="iseo-btn iseo-btn-outline">Edit</a>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<script>
		document.addEventListener('DOMContentLoaded', function () {
			var toggle  = document.getElementById('iseo-drafts-toggle');
			var overlay = document.getElementById('iseo-drafts-modal-overlay');
			var closeBtn = document.getElementById('iseo-drafts-modal-close');
			if (!toggle || !overlay) { return; }

			function openModal() { overlay.hidden = false; }
			function closeModal() { overlay.hidden = true; }

			toggle.addEventListener('click', openModal);
			if (closeBtn) { closeBtn.addEventListener('click', closeModal); }
			overlay.addEventListener('click', function (e) {
				if (e.target === overlay) { closeModal(); }
			});
			document.addEventListener('keydown', function (e) {
				if (e.key === 'Escape' && !overlay.hidden) { closeModal(); }
			});
		});
		</script>
		<?php endif; ?>

		<div class="iseo-section-head">
			<h2 class="iseo-section-title">Quick Links</h2>
			<span class="iseo-section-tag">Tools &amp; builders</span>
		</div>
		<?php // Quick Links: same four cards, titles and descriptions; teal icons in a light-teal tile. ?>
		<div class="iseo-qlinks">
			<div class="iseo-qlink">
				<a class="iseo-qlink-link" href="<?php echo esc_url( admin_url('admin.php?page=improveseo_posting') ); ?>">
					<span class="iseo-qlink-icon"><?php echo iseo_dash_icon( 'plus-square' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed SVG markup. ?></span>
					<h3 class="iseo-qlink-title">Create Posts</h3>
					<p class="iseo-qlink-desc">Create keyword-rich posts or pages. Preview content, schedule, and more!</p>
				</a>
			</div>
			<div class="iseo-qlink">
				<a class="iseo-qlink-link" href="<?php echo esc_url( admin_url('admin.php?page=improveseo_projects') ); ?>">
					<span class="iseo-qlink-icon"><?php echo iseo_dash_icon( 'document' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed SVG markup. ?></span>
					<h3 class="iseo-qlink-title">Single Post Projects</h3>
					<p class="iseo-qlink-desc">View, edit, and manage every single AI-generated post or page you've created.</p>
				</a>
			</div>
			<div class="iseo-qlink">
				<a class="iseo-qlink-link" href="<?php echo esc_url( admin_url('admin.php?page=improveseo_bulkprojects') ); ?>">
					<span class="iseo-qlink-icon"><?php echo iseo_dash_icon( 'documents' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed SVG markup. ?></span>
					<h3 class="iseo-qlink-title">Bulk Post Projects</h3>
					<p class="iseo-qlink-desc">Create projects. Option to duplicate project, update all published content, download content URLs to
					desktop, delete all posts/pages and project</p>
				</a>
			</div>
			<div class="iseo-qlink">
				<a class="iseo-qlink-link" href="<?php echo esc_url( admin_url('admin.php?page=improveseo_lists') ); ?>">
					<span class="iseo-qlink-icon"><?php echo iseo_dash_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed SVG markup. ?></span>
					<h3 class="iseo-qlink-title">Keyword Lists &amp; Tool</h3>
					<p class="iseo-qlink-desc">Add keywords you want to target and use Google autosuggest to build keyword lists you can bulk
					create posts from.</p>
				</a>
			</div>
		</div>

		<?php
		// ── Help & Support: "New here?" (1 part) + "Need a hand?" (2 parts) ─────────────────
		//
		// These two used to close the status row at the top, two of seven ~200px slots, so
		// "Need a hand?" could only say "Typical reply within one business day". Their own
		// compact row gives the account cards up there the width back, and the ticket card room
		// to say what happens after you submit one. Same palette as the rest of the dashboard:
		// navy, greys, and the icon tiles, lines and buttons the cards above already use — no
		// tints, chips or badges.
		//
		// "New here?" links only to things that exist today: the public Quick Start Guide
		// (/tutorials sits outside the CMS's login guard), its "How credits are used" section
		// (the #how-credits-are-used anchor scrolls there on the live page) and the Support
		// center. It doesn't repeat the CMS card's "4-minute setup walkthrough": no such video
		// exists yet.
		//
		// "Submit a ticket" opens the CMS's "Create Support Ticket" dialog (?newTicket=1,
		// SupportTickets.jsx). The steps describe what the CMS and admin server do today: a
		// confirmation email with the ticket ID, an email + account notification for each reply,
		// the CMS's own status names. See docs/DASHBOARD_HELP_SUPPORT.md for where each comes
		// from — change them together.
		// Label, one-line description, destination.
		$iseo_help_links = array(
			array( 'Quick Start Guide', 'Connect this site and publish your first post', 'https://account.improveseoplugin.com/tutorials' ),
			array( 'How credits work', 'Post and image costs, and when credits expire', 'https://account.improveseoplugin.com/tutorials#how-credits-are-used' ),
			array( 'Support center', 'Knowledge Base, service status and your tickets', 'https://account.improveseoplugin.com/support' ),
		);
		$iseo_ticket_url   = 'https://account.improveseoplugin.com/support?newTicket=1';
		// Title, then the line under it. <strong> marks the CMS's own status names.
		$iseo_ticket_steps = array(
			array( 'Submit a ticket', 'Sign in, choose a category and priority, and describe the issue.' ),
			array( 'Get your ticket ID', 'A confirmation email arrives and the ticket shows as <strong>Open</strong>.' ),
			array( 'We reply', 'By email and in your account. <strong>Awaiting Reply</strong> means we need your input.' ),
			array( 'Resolved', 'Still stuck? Open a new ticket and quote your ticket ID.' ),
		);
		// What support otherwise asks for in its first reply, copied as one line. Admin-only
		// page (manage_options), so the versions are fine to expose.
		$iseo_sysinfo = sprintf( 'Site: %s | ImproveSEO %s | WordPress %s | PHP %s', home_url(), IMPROVESEO_VERSION, get_bloginfo( 'version' ), PHP_VERSION );
		?>
		<h2 class="iseo-section-title">Help &amp; Support</h2>
		<div class="iseo-help-wrap">
			<div class="iseo-help-row">
				<div class="iseo-quickstart-card iseo-help-card">
					<div class="iseo-help-head">
						<div class="iseo-quickstart-icon"><?php echo iseo_dash_icon( 'book' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
						<h3 class="iseo-quickstart-title">New here?</h3>
						<span class="iseo-card-tag">Docs</span>
					</div>
					<p class="iseo-quickstart-msg iseo-help-intro">Short guides for getting started with ImproveSEO.</p>
					<ul class="iseo-help-links">
						<?php foreach ( $iseo_help_links as $iseo_link ) : ?>
						<li>
							<a href="<?php echo esc_url( $iseo_link[2] ); ?>" target="_blank" rel="noopener noreferrer">
								<span class="iseo-help-link-text">
									<span class="iseo-help-link-label"><?php echo esc_html( $iseo_link[0] ); ?></span>
									<span class="iseo-help-link-desc"><?php echo esc_html( $iseo_link[1] ); ?></span>
								</span>
								<?php echo iseo_dash_icon( 'external', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span class="screen-reader-text"> (opens in a new tab)</span>
							</a>
						</li>
						<?php endforeach; ?>
					</ul>
				</div>

				<div class="iseo-quickstart-card iseo-help-card">
					<div class="iseo-help-head">
						<div class="iseo-quickstart-icon"><?php echo iseo_dash_icon( 'lifebuoy' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
						<h3 class="iseo-quickstart-title">Need a hand?</h3>
						<span class="iseo-card-tag iseo-card-tag--mint">Priority</span>
					</div>
					<p class="iseo-quickstart-msg iseo-help-intro">Typical reply within 1 business day, for setup, credits, billing or content issues.</p>
					<a class="iseo-btn iseo-btn-solid iseo-help-action" href="<?php echo esc_url( $iseo_ticket_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo iseo_dash_icon( 'chat', 15 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>Submit a ticket<span class="screen-reader-text"> (opens in a new tab)</span></a>
					<ol class="iseo-steps" aria-label="How a support ticket works">
						<?php foreach ( $iseo_ticket_steps as $iseo_step_i => $iseo_step ) : ?>
						<li class="iseo-step">
							<span class="iseo-step-num" aria-hidden="true"><?php echo (int) $iseo_step_i + 1; ?></span>
							<span class="iseo-step-body">
								<span class="iseo-step-title"><?php echo esc_html( $iseo_step[0] ); ?></span>
								<span class="iseo-step-text"><?php echo wp_kses( $iseo_step[1], array( 'strong' => array() ) ); ?></span>
							</span>
						</li>
						<?php endforeach; ?>
					</ol>
					<div class="iseo-help-foot">
						<p class="iseo-help-tip">Include the exact error or a screenshot, what you clicked, and your site details.</p>
						<button type="button" class="iseo-btn iseo-btn-outline iseo-copy-btn" id="iseo-ticket-copy" data-copy="<?php echo esc_attr( $iseo_sysinfo ); ?>" title="<?php echo esc_attr( $iseo_sysinfo ); ?>">
							<span class="iseo-copy-icon"><?php echo iseo_dash_icon( 'copy', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><span class="iseo-copy-done-icon"><?php echo iseo_dash_icon( 'check', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><span class="iseo-copy-label">Copy site details</span>
						</button>
						<span class="screen-reader-text" id="iseo-ticket-copy-status" aria-live="polite"></span>
					</div>
				</div>
			</div>
		</div>
		<script>
		document.addEventListener('DOMContentLoaded', function () {
			var btn    = document.getElementById('iseo-ticket-copy');
			var status = document.getElementById('iseo-ticket-copy-status');
			var label  = btn && btn.querySelector('.iseo-copy-label');
			if (!btn || !label) { return; }
			var idleLabel = label.textContent;

			// navigator.clipboard only exists on HTTPS (or localhost); plenty of wp-admins
			// still run on plain HTTP, so fall back to the old textarea + execCommand route.
			function legacyCopy(text) {
				var area = document.createElement('textarea');
				area.value = text;
				area.setAttribute('readonly', '');
				area.style.position = 'fixed';
				area.style.opacity = '0';
				document.body.appendChild(area);
				area.select();
				var ok = false;
				try { ok = document.execCommand('copy'); } catch (e) { ok = false; }
				document.body.removeChild(area);
				return ok ? Promise.resolve() : Promise.reject();
			}

			var resetTimer;
			btn.addEventListener('click', function () {
				var text = btn.getAttribute('data-copy');
				var copy = (navigator.clipboard && window.isSecureContext)
					? navigator.clipboard.writeText(text).catch(function () { return legacyCopy(text); })
					: legacyCopy(text);
				copy.then(function () {
					label.textContent = 'Copied';
					btn.classList.add('is-copied');
					if (status) { status.textContent = 'Site details copied to clipboard'; }
					clearTimeout(resetTimer);
					resetTimer = setTimeout(function () {
						label.textContent = idleLabel;
						btn.classList.remove('is-copied');
					}, 2000);
				}, function () {
					// Nothing reached the clipboard: hand the text over in a prompt, pre-selected.
					window.prompt('Copy your site details:', text);
				});
			});
		});
		</script>

	</div>
</div>

<?php View::endSection('content') ?>

<?php View::make('layouts.main') ?>