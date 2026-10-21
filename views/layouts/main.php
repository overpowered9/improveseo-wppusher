<?php if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly. ?>
<div class="wrap improveseo-page">

	<div class="Breadcrumbs">
		<?php
		// Rendered template output from this plugin's own view layer, not user data.
		// It is echoed unescaped by design: section('content') below carries entire admin
		// screens — <style>, <script>, <form>, <input>, <select> — and wp_kses_post() would
		// strip all of it. Escaping belongs in the individual templates, at the point each
		// value is emitted, which is what the rest of this pass is doing.
		echo ImproveSEO\View::section('breadcrumbs'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- plugin view-layer output; see note above.
		?>
	</div>

	<?php
		ImproveSEO\FlashMessage::handle();
	?>

	<?php echo ImproveSEO\View::section('content'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- plugin view-layer output; escaping here would strip every form control and stylesheet on every admin screen. ?>
</div>

<div id="improveseo-help-fab">
	<div id="improveseo-help-menu" class="improveseo-help-menu" aria-hidden="true">
		<a href="https://account.improveseoplugin.com/support" target="_blank" rel="noopener noreferrer" class="improveseo-help-menu-item">
			<span class="dashicons dashicons-sos"></span> Support
		</a>
		<a href="https://account.improveseoplugin.com/tutorial" target="_blank" rel="noopener noreferrer" class="improveseo-help-menu-item">
			<span class="dashicons dashicons-welcome-learn-more"></span> Tutorial
		</a>
	</div>
	<button id="improveseo-help-btn" aria-label="Help" aria-expanded="false">
		<span class="dashicons dashicons-editor-help"></span>
	</button>
</div>

<script>
(function() {
	var btn = document.getElementById('improveseo-help-btn');
	var menu = document.getElementById('improveseo-help-menu');
	if (!btn || !menu) return;
	btn.addEventListener('click', function(e) {
		e.stopPropagation();
		var open = menu.getAttribute('aria-hidden') === 'false';
		menu.setAttribute('aria-hidden', open ? 'true' : 'false');
		btn.setAttribute('aria-expanded', open ? 'false' : 'true');
		menu.classList.toggle('improveseo-help-menu--open', !open);
	});
	document.addEventListener('click', function() {
		menu.setAttribute('aria-hidden', 'true');
		btn.setAttribute('aria-expanded', 'false');
		menu.classList.remove('improveseo-help-menu--open');
	});
})();
</script>

<!-- ── Connection Guard Modal ─────────────────────────────────────────────
     Shown globally when an unconnected site attempts a credit-consuming action
     (content generation, image generation, keyword generation). The modal blocks
     the action and sends the user to Settings, where credentials are entered and
     verified. Not the onboarding wizard: that is a free-trial sign-up flow, wrong for
     someone who already has an account or whose saved key is simply incorrect.
     The guard function iseoRequireConnection() returns true when connected (caller
     should proceed) or false when not (modal is shown, caller should return).

     Shown in the shared popup (assets/js/iseo-popups.js), so it looks like every other one.
     "Connect Website" opens Settings in a NEW TAB so the work on this page (a wizard half
     filled in) survives; the flag is re-read when the user comes back to this tab and when
     the popup is dismissed — see iseoRefreshConnectionState(). -->

<script>
/**
 * Global connection guard for ImproveSEO.
 *
 * Call iseoRequireConnection() before any credit-consuming action.
 * Returns true  → site is connected, proceed with the action.
 * Returns false → site is NOT connected, modal is shown, caller must abort.
 */
(function() {
	// Settings — see the comment above for why this is not the onboarding wizard. Rendered by
	// PHP so it is correct at parse time; main_ajax_vars is localized onto a footer script.
	var settingsUrl = <?php echo wp_json_encode( admin_url( 'admin.php?page=improveseo_settings' ) ); ?>;
	var guardOpen = false;

	function showModal() {
		if (guardOpen) { return; }
		if (typeof iseoModal === 'undefined') {
			if (window.confirm("This site isn't connected to an ImproveSEO account. Open Settings to connect it?")) {
				window.open(settingsUrl, '_blank', 'noopener');
			}
			return;
		}
		guardOpen = true;
		iseoModal.warning({
			title: <?php echo wp_json_encode( __( "This site isn't connected to an ImproveSEO account", 'improveseo' ) ); ?>,
			message: <?php echo wp_json_encode( __( 'Connect to start generating SEO content.', 'improveseo' ) ); ?>,
			primary: {
				label: <?php echo wp_json_encode( __( 'Connect Website', 'improveseo' ) ); ?>,
				// New tab, so the work on this page survives; see the comment above.
				onClick: function() { window.open(settingsUrl, '_blank', 'noopener'); }
			},
			// However it closes (button, ×, Esc, outside), re-read the flag: the user may have
			// just saved valid credentials in the other tab.
			onClose: function() { guardOpen = false; iseoRefreshConnectionState(); }
		});
	}
	function hideModal() {
		if (guardOpen && typeof iseoModal !== 'undefined') { iseoModal.close(); }
	}

	/* ── Keeping the connection flag fresh ──────────────────────────────────────
	   Everything below exists because the flag is a PAGE-LOAD SNAPSHOT, and the
	   recovery flow this modal prescribes spans two tabs.

	   The user is sent to Settings in a second tab, saves a valid API key and site
	   code, and returns here. The save worked — but THIS page still holds the '0'
	   it was rendered with, so closing the modal and pressing Generate raised the
	   modal straight back. It looked exactly like "my settings did not save", when
	   in fact nothing had re-read them.

	   Re-checked on the two moments that precede a retry: returning to the tab, and
	   dismissing the modal. Both fire well before the user can reach a Generate
	   button, so the flag is current by the time the guard consults it. The check is
	   a local options lookup, so it is cheap enough to run on focus.
	───────────────────────────────────────────────────────────────────────────── */
	var iseoRefreshInFlight = false;

	function iseoRefreshConnectionState() {
		if (iseoRefreshInFlight) { return; }
		if (typeof main_ajax_vars === 'undefined' || !main_ajax_vars.iseo_connection_nonce) { return; }
		if (typeof ajaxurl === 'undefined') { return; }

		// Already connected — nothing a refresh could improve, and the guard is not in the way.
		if (window.iseoIsConnected && window.iseoIsConnected()) { return; }

		iseoRefreshInFlight = true;

		var xhr = new XMLHttpRequest();
		xhr.open('POST', ajaxurl, true);
		xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		xhr.onload = function() {
			iseoRefreshInFlight = false;
			try {
				var res = JSON.parse(xhr.responseText);
				if (res && res.success && res.data && res.data.connected === '1') {
					// Update BOTH sources iseoIsConnected() consults, so the guard agrees with
					// itself no matter which one it falls back to.
					if (typeof main_ajax_vars !== 'undefined') { main_ajax_vars.iseo_connected = '1'; }
					iseoConnectedFlag = true;
					hideModal();
				}
			} catch (e) {
				// A malformed reply leaves the flag alone: staying blocked is the safe failure,
				// since the server is the real gate and would refuse the generation anyway.
			}
		};
		xhr.onerror = function() { iseoRefreshInFlight = false; };
		xhr.send('action=improveseo_connection_state&nonce=' + encodeURIComponent(main_ajax_vars.iseo_connection_nonce));
	}

	// Coming back from the Settings tab is the moment the flag most needs re-reading.
	window.addEventListener('focus', iseoRefreshConnectionState);
	document.addEventListener('visibilitychange', function() {
		if (!document.hidden) { iseoRefreshConnectionState(); }
	});

	// Exposed so a caller that has just fixed credentials can force a re-read.
	window.iseoRefreshConnectionState = iseoRefreshConnectionState;

	// Connection state, rendered by PHP for the same reason as the href above: reading it
	// only from main_ajax_vars meant "footer script not loaded yet" was indistinguishable
	// from "not connected", which would pop the modal on a perfectly connected site.
	// main_ajax_vars still wins when present so anything that refreshes it stays authoritative.
	var iseoConnectedFlag = <?php echo ( ! empty( get_option( 'improveseo_api_key', '' ) ) && ! empty( get_option( 'improveseo_site_code', '' ) ) ) ? 'true' : 'false'; ?>;

	/**
	 * Silent connection check, for background work the user did not initiate. Interrupting an
	 * automatic retry with a modal would blame the user for something they never asked for.
	 *
	 * @returns {boolean} true if connected.
	 */
	window.iseoIsConnected = function() {
		return (typeof main_ajax_vars !== 'undefined' && typeof main_ajax_vars.iseo_connected !== 'undefined')
			? (main_ajax_vars.iseo_connected === '1')
			: iseoConnectedFlag;
	};

	/**
	 * @returns {boolean} true if connected, false if not (modal shown).
	 */
	window.iseoRequireConnection = function() {
		if (!window.iseoIsConnected()) {
			showModal();
			return false;
		}
		return true;
	};

	/**
	 * Show this same modal on a LIVE rejection — the admin server actually returned
	 * 401/403 on a real request (wrong key, wrong-account site code, or a site code
	 * that belongs to a different one of the account's own websites) — as opposed to
	 * iseoRequireConnection()'s pre-flight check, which only ever catches empty fields.
	 * Exposed directly so any AJAX error handler in the plugin can call it without
	 * re-deriving showModal() or duplicating this markup.
	 */
	window.iseoShowConnectionGuard = showModal;
})();

/**
 * Canonical plan label for the whole plugin.
 *
 * The server's plan.name is a display string that has already been rebranded once: a Scale
 * account still answers "Pro" and an Optimize account answers "Starter". Echoing it gave two
 * screens two different names for one account. Resolution order is therefore most-stable
 * first — subscription.plan.slug, then the (de-suffixed) name, then the plan id.
 *
 * @param {object} plan         The `plan` block: { is_paid, name }.
 * @param {object} subscription The `subscription` block: { plan: { id, slug, name } }.
 * @param {object} trial        The `trial` block: { active, expired, days_remaining, ends_at }.
 * @returns {string} One of: Free Trial, Trial ended, Free Plan, Grow Plan, Optimize Plan,
 *                   Scale Plan, Enterprise Plan, or a title-cased passthrough.
 */
window.iseoPlanLabel = function(plan, subscription, trial) {
	plan  = plan  || {};
	trial = trial || {};

	var subPlan = (subscription && subscription.plan) ? subscription.plan : {};

	// Trial states stand on their own — they are not a plan tier.
	if (trial.expired && plan.is_paid !== true) { return 'Trial ended'; }
	if (trial.active  && plan.is_paid !== true) { return 'Free Trial'; }

	// An account without a paid subscription is on the free plan, whatever stale paid name
	// the server may still be carrying against it. This is what stopped free accounts from
	// being labelled "Pro Plan".
	if (plan.is_paid !== true) { return 'Free Plan'; }

	// Legacy names on the left, current lineup on the right. Keys are lowercase and carry no
	// trailing "plan" — the caller strips that below, so "Pro" and "Pro Plan" both key as 'pro'.
	var canonical = {
		'pro':        'Scale',
		'scale':      'Scale',
		'starter':    'Optimize',
		'optimize':   'Optimize',
		'basic':      'Grow',
		'grow':       'Grow',
		'free':       'Free',
		'enterprise': 'Enterprise'
	};

	function bare(value) {
		return String(value == null ? '' : value).replace(/\s+plan$/i, '').trim();
	}

	var candidates = [ bare(subPlan.slug), bare(subPlan.name), bare(plan.name) ];
	for (var i = 0; i < candidates.length; i++) {
		if (!candidates[i]) { continue; }
		var key = candidates[i].toLowerCase().replace(/[\s_]+/g, '-');
		if (canonical[key]) { return canonical[key] + ' Plan'; }
		// A slug like 'free-trial' has no tier of its own.
		if (key === 'free-trial' || key === 'trial') { return 'Free Trial'; }
	}

	// Last resort. The id map predates the five-plan lineup, so it is only consulted when the
	// server sent neither a slug nor a name.
	var byId = { 1: 'Grow', 2: 'Scale', 3: 'Enterprise' };
	var planId = (subPlan.id != null) ? parseInt(subPlan.id, 10) : NaN;
	if (!isNaN(planId) && byId[planId]) { return byId[planId] + ' Plan'; }

	// Unknown but paid: show what the server said rather than guessing a tier, title-cased and
	// suffixed exactly once.
	var raw = bare(subPlan.name) || bare(plan.name);
	if (!raw) { return 'Paid Plan'; }
	return raw.replace(/\S+/g, function(w) { return w.charAt(0).toUpperCase() + w.slice(1); }) + ' Plan';
};
</script>
