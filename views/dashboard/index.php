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
			'play'      => '<rect x="3" y="3" width="18" height="18" rx="4"></rect><polygon points="10 8.5 15.5 12 10 15.5 10 8.5"></polygon>',
			'chat'      => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path><line x1="8" y1="8.5" x2="16" y2="8.5"></line><line x1="8" y1="12.5" x2="13" y2="12.5"></line>',
			'chart'     => '<line x1="3" y1="21" x2="21" y2="21"></line><rect x="5" y="12" width="3" height="6"></rect><rect x="10.5" y="7" width="3" height="11"></rect><rect x="16" y="3" width="3" height="15"></rect>',
			'building'  => '<rect x="4" y="3" width="16" height="18" rx="1"></rect><path d="M10 21v-4h4v4"></path><line x1="8" y1="7" x2="10" y2="7"></line><line x1="14" y1="7" x2="16" y2="7"></line><line x1="8" y1="11" x2="10" y2="11"></line><line x1="14" y1="11" x2="16" y2="11"></line>',
			'window'    => '<rect x="3" y="4" width="18" height="16" rx="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="7" y1="13" x2="17" y2="13"></line><line x1="7" y1="16.5" x2="13" y2="16.5"></line>',
			'document'  => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="8" y1="13" x2="16" y2="13"></line><line x1="8" y1="17" x2="14" y2="17"></line>',
			'documents' => '<rect x="8" y="7" width="13" height="15" rx="2"></rect><path d="M16 7V4a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h3"></path><line x1="11.5" y1="12" x2="17.5" y2="12"></line><line x1="11.5" y1="16" x2="15.5" y2="16"></line>',
			'list'      => '<rect x="3" y="3" width="18" height="14" rx="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line><line x1="7" y1="7.5" x2="17" y2="7.5"></line><line x1="7" y1="11.5" x2="13" y2="11.5"></line>',
			'chevron'   => '<polyline points="9 6 15 12 9 18"></polyline>',
			'chevron-down' => '<polyline points="6 9 12 15 18 9"></polyline>',
			'calendar'  => '<rect x="3" y="4" width="18" height="18" rx="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line>',
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
		<div class="iseo-quickstart-row">
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
					<div class="iseo-quickstart-icon"><?php echo iseo_dash_icon( 'rocket' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed SVG markup. ?></div>
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
			$iseo_cr_figure = ( $iseo_qs_snap_total !== null ) ? number_format_i18n( $iseo_qs_snap_total ) . ' credits' : '';
			$iseo_cr_expiry = '';
			if ( $iseo_qs_snap_total !== null ) {
				$iseo_cr_expiry_ts = ! empty( $iseo_qs_snapshot['next_expiry_at'] ) ? strtotime( $iseo_qs_snapshot['next_expiry_at'] . ' 00:00:00 UTC' ) : false;
				$iseo_cr_expiry    = ( $iseo_cr_expiry_ts && isset( $iseo_qs_snapshot['next_expiry_amount'] ) )
					? number_format_i18n( (int) $iseo_qs_snapshot['next_expiry_amount'] ) . ' credits expire on ' . gmdate( 'j M Y', $iseo_cr_expiry_ts )
					: 'No credits expiring soon';
			}
			?>
			<div class="iseo-quickstart-card iseo-stat-card" id="iseo-credits-remaining-card" <?php echo $iseo_qs_has_creds ? '' : 'hidden'; ?>>
				<div class="iseo-card-top">
					<div class="iseo-quickstart-icon"><?php echo iseo_dash_icon( 'database' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
					<a class="iseo-card-chevron" href="<?php echo esc_url( $iseo_credits_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="Credits"><?php echo iseo_dash_icon( 'chevron', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
				</div>
				<div class="iseo-quickstart-body">
					<h3 class="iseo-quickstart-title">Credits Remaining</h3>
					<p class="iseo-quickstart-msg iseo-credits-figure" id="iseo-credits-remaining-figure"><?php echo $iseo_cr_figure !== '' ? esc_html( $iseo_cr_figure ) : '&mdash;'; ?></p>
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
				<div class="iseo-card-top">
					<div class="iseo-quickstart-icon"><?php echo iseo_dash_icon( 'pie' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
					<a class="iseo-card-chevron" href="<?php echo esc_url( $iseo_credits_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="Credit usage"><?php echo iseo_dash_icon( 'chevron', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
				</div>
				<div class="iseo-quickstart-body">
					<h3 class="iseo-quickstart-title">Credits Used</h3>
					<p class="iseo-quickstart-msg iseo-credits-figure" id="iseo-credits-used-figure">&mdash;</p>
					<p class="iseo-quickstart-msg" id="iseo-credits-used-note">this cycle</p>
				</div>
			</div>

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
				<div class="iseo-card-top">
					<div class="iseo-quickstart-icon"><?php echo iseo_dash_icon( 'crown' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
					<a class="iseo-card-chevron" id="iseo-plan-chevron" href="<?php echo esc_url( $iseo_qs_has_creds ? $iseo_qs_plans_url : $iseo_qs_connect_url ); ?>" <?php echo $iseo_qs_has_creds ? 'target="_blank" rel="noopener noreferrer"' : ''; ?> aria-label="Plan"><?php echo iseo_dash_icon( 'chevron', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
				</div>
				<?php // Still filled in by JS (ACTIVE PLAN / CANCELLING / …) but no longer drawn as a badge: the
				// date line beneath the plan name already says the same thing ("Renews on" / "Access ends"). ?>
				<span class="screen-reader-text" id="iseo-plan-status"><?php echo $iseo_qs_has_creds ? 'ACTIVE PLAN' : 'NOT CONNECTED'; ?></span>
				<h3 class="iseo-quickstart-title" id="iseo-plan-name"><?php echo $iseo_qs_has_creds ? '&mdash;' : 'No active plan'; ?></h3>
				<p class="iseo-quickstart-msg" id="iseo-plan-date"><?php echo $iseo_qs_has_creds ? '' : 'Connect this site to see your plan and credits.'; ?></p>
				<div class="iseo-plan-actions">
					<?php if ( $iseo_qs_has_creds ) : ?>
					<a href="<?php echo esc_url( $iseo_qs_plans_url ); ?>" class="iseo-btn iseo-btn-solid" id="iseo-plan-btn-primary" target="_blank" rel="noopener noreferrer">Manage Plan</a>
					<?php else : ?>
					<a href="<?php echo esc_url( $iseo_qs_connect_url ); ?>" class="iseo-btn iseo-btn-solid" id="iseo-plan-btn-primary">Connect now</a>
					<?php endif; ?>
					<a href="<?php echo esc_url( $iseo_qs_plans_url ); ?>" class="iseo-btn iseo-btn-outline" id="iseo-plan-btn-secondary" target="_blank" rel="noopener noreferrer"><?php echo $iseo_qs_has_creds ? 'Cancel Subscription' : 'View plans'; ?></a>
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
					<a href="<?php echo esc_url( $iseo_gs_guide_url ); ?>" class="iseo-quickstart-link">Start the guide</a>
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
			<div class="iseo-quickstart-card iseo-support-card">
				<div class="iseo-card-top">
					<div class="iseo-quickstart-icon"><?php echo iseo_dash_icon( 'play' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
					<a class="iseo-card-chevron" href="<?php echo esc_url( $iseo_support_kb_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="Tutorials"><?php echo iseo_dash_icon( 'chevron', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
				</div>
				<div class="iseo-quickstart-body">
					<h3 class="iseo-quickstart-title">New here?</h3>
					<p class="iseo-quickstart-msg">Watch the 4-minute setup walkthrough or browse the Knowledge Base.</p>
					<a href="<?php echo esc_url( $iseo_support_kb_url ); ?>" class="iseo-btn iseo-btn-outline iseo-support-cta" target="_blank" rel="noopener noreferrer">Open tutorials</a>
				</div>
			</div>
			<div class="iseo-quickstart-card iseo-support-card">
				<div class="iseo-card-top">
					<div class="iseo-quickstart-icon"><?php echo iseo_dash_icon( 'chat' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
					<a class="iseo-card-chevron" href="<?php echo esc_url( $iseo_support_ticket_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="Support"><?php echo iseo_dash_icon( 'chevron', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
				</div>
				<div class="iseo-quickstart-body">
					<h3 class="iseo-quickstart-title">Need a hand?</h3>
					<p class="iseo-quickstart-msg">Typical reply within one business day.</p>
					<a href="<?php echo esc_url( $iseo_support_ticket_url ); ?>" class="iseo-btn iseo-btn-solid iseo-support-cta" target="_blank" rel="noopener noreferrer">Submit a ticket</a>
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

			function iseoPopulateCreditsRemaining(d) {
				var el = document.getElementById('iseo-credits-remaining-card');
				if (!el) { return; }

				var total = iseoReadCreditsTotal(d);
				document.getElementById('iseo-credits-remaining-figure').textContent =
					(total != null) ? total.toLocaleString() + ' credits' : '—';

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

				if (allotment != null && allotment > 0 && planLeft != null) {
					var used = Math.max(0, allotment - planLeft);
					figureEl.textContent = used.toLocaleString() + ' credits';
					noteEl.textContent = 'of ' + allotment.toLocaleString() + ' this cycle';
				} else {
					// No monthly allotment to measure against — a Basic/pack-only account, or an
					// older server response missing these fields. Nothing false to report.
					figureEl.textContent = '—';
					noteEl.textContent = 'No monthly allotment on your current plan';
				}

				el.hidden = false;
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
						var rejected = (status === 401 || status === 403);
						iseoPlanCardUnavailable(rejected);
						iseoQsShow(rejected ? 'disconnected' : 'ready');
						return;
					}

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
				.catch(function () { iseoPlanCardUnavailable(false); iseoQsShow('ready'); });
		});
		</script>
		<?php endif; ?>

		<?php
		// ── Row 2: Content Metrics (left, two-card width) + Review Business Details
		// (right, one-card width) ───────────────────────────────────────────────────
		//
		// Content Metrics counts real WordPress posts, not rows in this plugin's own
		// project tables — a project only becomes a post once it's actually built, and
		// single-post and bulk projects both tag the post they build with the SAME
		// postmeta key (improveseo_project_id; see modules/projects.php and
		// modules/bulkprojects.php), so one query covers both without caring which
		// created it. This is entirely local WordPress data — no network call, no
		// waiting on the admin server, unlike everything else on this page so far.
		global $wpdb;

		// DISTINCT rather than GROUP BY: guards the (currently never-happening, but
		// unenforced) case of a post carrying the meta key twice, without pulling in
		// MySQL's stricter GROUP BY column rules for no benefit.
		$iseo_cm_posts = $wpdb->get_results(
			"SELECT DISTINCT p.ID, p.post_status, p.post_date
			 FROM {$wpdb->posts} p
			 INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key = 'improveseo_project_id'
			 WHERE p.post_status IN ('publish','draft','future')
			 ORDER BY p.post_date DESC"
		); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter -- fixed query text, no user input.

		// Period picker ("Last 30 Days" etc.) — a plain GET parameter, so the choice
		// survives a reload and is shareable as a URL. Filtered here in PHP rather than in
		// the query, so $iseo_cm_total below can still tell "nothing in this period" apart
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
		$iseo_cm_total = count( $iseo_cm_posts );
		if ( 'all' !== $iseo_cm_range ) {
			// post_date is site-local time, and wp_date() formats in the site's timezone too.
			$iseo_cm_cutoff = wp_date( 'Y-m-d H:i:s', time() - (int) $iseo_cm_range * DAY_IN_SECONDS );
			$iseo_cm_posts  = array_values( array_filter( $iseo_cm_posts, function ( $p ) use ( $iseo_cm_cutoff ) {
				return 'future' === $p->post_status || $p->post_date >= $iseo_cm_cutoff;
			} ) );
		}

		$iseo_cm_published = 0;
		$iseo_cm_draft      = 0;
		$iseo_cm_scheduled  = 0;
		$iseo_cm_draft_posts = array();

		foreach ( $iseo_cm_posts as $iseo_cm_post ) {
			if ( 'publish' === $iseo_cm_post->post_status ) {
				$iseo_cm_published++;
			} elseif ( 'draft' === $iseo_cm_post->post_status ) {
				$iseo_cm_draft++;
				$iseo_cm_draft_posts[] = $iseo_cm_post;
			} elseif ( 'future' === $iseo_cm_post->post_status ) {
				$iseo_cm_scheduled++;
			}
		}

		$iseo_cm_latest3 = array_slice( $iseo_cm_posts, 0, 3 );

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
		<div class="iseo-row-2">
			<div class="iseo-dark-card iseo-metrics-card">
				<div class="iseo-metrics-head">
					<div class="iseo-quickstart-icon"><?php echo iseo_dash_icon( 'chart' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
					<h3 class="iseo-quickstart-title">Content Metrics</h3>

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
						<p class="iseo-metrics-latest-heading">Latest projects</p>
						<?php if ( empty( $iseo_cm_latest3 ) ) : ?>
							<p class="iseo-quickstart-msg">No posts in this period.</p>
						<?php endif; ?>
						<?php foreach ( $iseo_cm_latest3 as $iseo_cm_p ) : ?>
							<a class="iseo-metrics-latest-row" href="<?php echo esc_url( get_edit_post_link( $iseo_cm_p->ID ) ); ?>">
								<span class="iseo-metrics-latest-title"><?php echo esc_html( get_the_title( $iseo_cm_p->ID ) ? get_the_title( $iseo_cm_p->ID ) : '(no title)' ); ?></span>
								<span class="iseo-metrics-latest-badge iseo-metrics-badge-<?php echo esc_attr( $iseo_cm_p->post_status ); ?>"><?php echo esc_html( $iseo_cm_status_labels[ $iseo_cm_p->post_status ] ); ?></span>
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
			<div class="iseo-dark-card iseo-bizdetails-card">
				<div class="iseo-quickstart-icon"><?php echo iseo_dash_icon( 'building' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
				<div class="iseo-quickstart-body">
					<h3 class="iseo-quickstart-title">Review Business Details</h3>
					<p class="iseo-quickstart-msg">Make sure it's complete and always up to date, to produce the best possible content for your business.</p>

					<div class="iseo-bizdetails-progress-track" role="progressbar" aria-valuenow="<?php echo esc_attr( $iseo_bd_percent ); ?>" aria-valuemin="0" aria-valuemax="100" aria-label="Business details completeness">
						<div class="iseo-bizdetails-progress-fill" style="width: <?php echo esc_attr( $iseo_bd_percent ); ?>%;"></div>
					</div>
					<p class="iseo-bizdetails-progress-label"><?php echo esc_html( $iseo_bd_percent ); ?>% complete</p>

					<?php if ( ! empty( $iseo_bd_missing ) ) : ?>
						<p class="iseo-bizdetails-missing">Missing: <?php echo esc_html( implode( ', ', $iseo_bd_missing ) ); ?></p>
					<?php endif; ?>

					<a href="<?php echo esc_url( $iseo_bd_settings_url ); ?>" class="iseo-quickstart-link"><?php echo empty( $iseo_bd_missing ) ? 'Review details' : 'Complete details'; ?></a>
				</div>
			</div>
		</div>

		<?php if ( ! empty( $iseo_cm_draft_posts ) ) : ?>
		<!-- Drafts modal — every draft this plugin built (not just the latest 3), each
		     linking straight to its own post editor so it can be edited, published, or
		     deleted from there. Pure local DOM toggle: the list is rendered once, server-
		     side, from the same query above — no AJAX round trip to open it. -->
		<div id="iseo-drafts-modal-overlay" class="iseo-drafts-modal-overlay" hidden>
			<div class="iseo-drafts-modal" role="dialog" aria-modal="true" aria-labelledby="iseo-drafts-modal-title">
				<button type="button" id="iseo-drafts-modal-close" class="iseo-drafts-modal-close" aria-label="Close">&times;</button>
				<h3 id="iseo-drafts-modal-title" class="iseo-drafts-modal-title">Draft posts (<?php echo esc_html( $iseo_cm_draft ); ?>)</h3>
				<div class="iseo-drafts-modal-list">
					<?php foreach ( $iseo_cm_draft_posts as $iseo_cm_draft_post ) : ?>
						<div class="iseo-drafts-modal-row">
							<span class="iseo-drafts-modal-row-title"><?php echo esc_html( get_the_title( $iseo_cm_draft_post->ID ) ? get_the_title( $iseo_cm_draft_post->ID ) : '(no title)' ); ?></span>
							<a href="<?php echo esc_url( get_edit_post_link( $iseo_cm_draft_post->ID ) ); ?>" class="iseo-btn iseo-btn-outline">Edit</a>
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