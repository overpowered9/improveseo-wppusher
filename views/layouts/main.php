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
     the action and directs the user to the onboarding wizard to connect first.
     The guard function iseoRequireConnection() returns true when connected (caller
     should proceed) or false when not (modal is shown, caller should return). -->
<div id="iseo-connection-guard-overlay" style="display:none; position:fixed; inset:0; z-index:999999; background:rgba(0,0,0,0.55); backdrop-filter:blur(2px); align-items:center; justify-content:center;">
	<div style="background:#fff; border-radius:14px; padding:36px 32px 28px; max-width:420px; width:90%; text-align:center; box-shadow:0 20px 60px rgba(0,0,0,0.25); position:relative; animation:iseoGuardFadeIn 0.25s ease-out;">

		<!-- Close button -->
		<button id="iseo-guard-close" type="button" style="position:absolute; top:12px; right:14px; background:none; border:none; font-size:20px; color:#9ca3af; cursor:pointer; line-height:1; padding:4px;" aria-label="Close">&times;</button>

		<!-- Icon -->
		<div style="margin-bottom:16px;">
			<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;">
				<circle cx="12" cy="12" r="10"></circle>
				<line x1="12" y1="8" x2="12" y2="12"></line>
				<line x1="12" y1="16" x2="12.01" y2="16"></line>
			</svg>
		</div>

		<!-- Title -->
		<h3 style="margin:0 0 8px; font-size:18px; font-weight:700; color:#111827; line-height:1.3;">
			This site isn't connected to an ImproveSEO account
		</h3>

		<!-- Subtitle -->
		<p style="margin:0 0 24px; font-size:14px; color:#6b7280; line-height:1.5;">
			Connect it to start generating content.
		</p>

		<!-- CTA Button.
		     The destination is rendered here by PHP rather than assigned by the script below.
		     It used to be href="#" with the real URL read from main_ajax_vars, but that object
		     is localized onto a FOOTER script while this markup is inline in the body, so the
		     read ran before the variable existed and the href stayed "#" — the button did
		     nothing at all. Rendering it server-side removes the ordering question entirely. -->
		<a id="iseo-guard-connect-btn" href="<?php echo esc_url( admin_url( 'admin.php?page=improveseo_onboarding' ) ); ?>" style="display:inline-block; padding:12px 28px; background:#0f7b6c; color:#fff; font-size:14px; font-weight:600; border-radius:8px; text-decoration:none; transition:background 0.2s; letter-spacing:0.01em;">
			Connect Website
		</a>
	</div>
</div>

<style>
@keyframes iseoGuardFadeIn {
	from { opacity: 0; transform: translateY(12px) scale(0.97); }
	to   { opacity: 1; transform: translateY(0) scale(1); }
}
#iseo-connection-guard-overlay a:hover {
	background: #0e6b5e !important;
}
#iseo-guard-close:hover {
	color: #374151 !important;
}
</style>

<script>
/**
 * Global connection guard for ImproveSEO.
 *
 * Call iseoRequireConnection() before any credit-consuming action.
 * Returns true  → site is connected, proceed with the action.
 * Returns false → site is NOT connected, modal is shown, caller must abort.
 */
(function() {
	var overlay  = document.getElementById('iseo-connection-guard-overlay');
	var closeBtn = document.getElementById('iseo-guard-close');
	var connectBtn = document.getElementById('iseo-guard-connect-btn');
	if (!overlay) return;

	// The onboarding wizard is the only route that actually stores an API key and site
	// code: it opens the CMS connect flow and exchanges the returned token. Rendered by
	// PHP so it is correct at parse time; main_ajax_vars is not consulted for it any more.
	var onboardingUrl = '<?php echo esc_js( admin_url( 'admin.php?page=improveseo_onboarding' ) ); ?>';

	// Belt and braces: if anything ever strips or blanks the href, a click still navigates
	// rather than silently scrolling to the top of the page.
	if (connectBtn) {
		connectBtn.addEventListener('click', function(e) {
			var href = connectBtn.getAttribute('href');
			if (!href || href === '#') {
				e.preventDefault();
				window.location.href = onboardingUrl;
			}
		});
	}

	function showModal() {
		overlay.style.display = 'flex';
	}
	function hideModal() {
		overlay.style.display = 'none';
	}

	if (closeBtn) {
		closeBtn.addEventListener('click', hideModal);
	}
	// Close on overlay click (outside the card)
	overlay.addEventListener('click', function(e) {
		if (e.target === overlay) hideModal();
	});
	// Close on Escape key
	document.addEventListener('keydown', function(e) {
		if (e.key === 'Escape' && overlay.style.display === 'flex') hideModal();
	});

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
