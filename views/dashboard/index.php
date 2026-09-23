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
			// Credits Remaining card — upper row, left of Active Plan. Mirrors the CMS's own
			// "Total Credits Remaining" panel (user-cms/src/components/CreditManagement.jsx):
			// the balance, and when the soonest batch expires. The expiry line reuses the exact
			// wording the site-wide notice already uses (includes/connection-status.php,
			// improveseo_global_notices()) — one sentence for "credits are expiring", not two
			// that could disagree.
			//
			// Same data the site-wide notice's snapshot is built from (credit_details.content,
			// balance.next_expiry_at/amount) — read here straight from the SAME AJAX response
			// Quick Start's own check already fetches, not a second network call. Hidden until
			// that resolves, like Active Plan.
			?>
			<div class="module-box iseo-quickstart-card iseo-credits-card" id="iseo-credits-remaining-card" hidden>
				<div class="iseo-quickstart-icon iseo-credits-icon" aria-hidden="true">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"></circle><path d="M12 7v10M9 9.5a2.5 2.5 0 0 1 2.5-2.5h1a2 2 0 1 1 0 4h-1a2 2 0 1 0 0 4h1a2.5 2.5 0 0 0 2.5-2.5"></path></svg>
				</div>
				<div class="iseo-quickstart-body">
					<h3 class="iseo-quickstart-title">Credits Remaining</h3>
					<p class="iseo-quickstart-msg iseo-credits-figure" id="iseo-credits-remaining-figure">&mdash;</p>
					<p class="iseo-quickstart-msg" id="iseo-credits-remaining-expiry"></p>
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
			<div class="module-box iseo-quickstart-card iseo-credits-card iseo-credits-used-card" id="iseo-credits-used-card" hidden>
				<div class="iseo-quickstart-icon iseo-credits-icon" aria-hidden="true">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path><path d="M22 12A10 10 0 0 0 12 2v10z"></path></svg>
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
			// Hidden until the same AJAX call Quick Start already makes resolves — no second
			// network request, and (like Guided Start) it never renders server-side since
			// plan/subscription data isn't known at render time.
			?>
			<div class="module-box iseo-quickstart-card iseo-plan-card" id="iseo-plan-card" hidden>
				<div class="iseo-plan-head">
					<div class="iseo-quickstart-icon iseo-plan-icon" aria-hidden="true">
						<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="6"></circle><path d="M8.21 13.89 7 23l5-3 5 3-1.21-9.12"></path></svg>
					</div>
					<span class="iseo-plan-eyebrow" id="iseo-plan-status">ACTIVE PLAN</span>
				</div>
				<h3 class="iseo-quickstart-title" id="iseo-plan-name">&mdash;</h3>
				<p class="iseo-quickstart-msg" id="iseo-plan-date"></p>
				<div class="iseo-plan-actions">
					<a href="<?php echo esc_url( $iseo_qs_plans_url ); ?>" class="iseo-plan-btn iseo-plan-btn-primary" id="iseo-plan-btn-primary" target="_blank" rel="noopener noreferrer">Manage Plan</a>
					<a href="<?php echo esc_url( $iseo_qs_plans_url ); ?>" class="iseo-plan-btn iseo-plan-btn-quiet" id="iseo-plan-btn-secondary" target="_blank" rel="noopener noreferrer">Cancel Subscription</a>
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
				// document-wide, not card-scoped: the Content Metrics card's empty state mirrors
				// these same four lines (iseo_render_qs_messages() in PHP above) so both copies
				// resolve from this one fetch instead of needing a second AJAX call.
				document.querySelectorAll('[data-qs-msg]').forEach(function (el) {
					el.hidden = el.getAttribute('data-qs-msg') !== state;
				});
				// Guided Start only makes sense once we know the account is connected AND has
				// enough credits — the same 'ready' state Quick Start's own message uses.
				if (guideCard) { guideCard.hidden = (state !== 'ready'); }
			}

			function iseoFormatDate(iso) {
				if (!iso) { return null; }
				var parsed = new Date(iso);
				if (isNaN(parsed.getTime())) { return null; }
				return parsed.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
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
						iseoQsShow((status === 401 || status === 403) ? 'disconnected' : 'ready');
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
				.catch(function () { iseoQsShow('ready'); });
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

		$iseo_cm_total   = count( $iseo_cm_posts );
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
			<div class="module-box iseo-metrics-card">
				<h3 class="iseo-quickstart-title">Content Metrics</h3>

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
			<div class="module-box iseo-quickstart-card iseo-bizdetails-card">
				<div class="iseo-quickstart-icon iseo-bizdetails-icon" aria-hidden="true">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
				</div>
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
							<a href="<?php echo esc_url( get_edit_post_link( $iseo_cm_draft_post->ID ) ); ?>" class="iseo-plan-btn iseo-plan-btn-quiet">Edit</a>
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