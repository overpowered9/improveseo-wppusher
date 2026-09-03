<div class="wrap improveseo-page">

	<div class="Breadcrumbs">
		<?= ImproveSEO\View::section('breadcrumbs') ?>
	</div>

	<?php
		ImproveSEO\FlashMessage::handle();
	?>

	<?= ImproveSEO\View::section('content') ?>
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

		<!-- CTA Button -->
		<a id="iseo-guard-connect-btn" href="#" style="display:inline-block; padding:12px 28px; background:#0f7b6c; color:#fff; font-size:14px; font-weight:600; border-radius:8px; text-decoration:none; transition:background 0.2s; letter-spacing:0.01em;">
			Connect Website
		</a>

		<?php
		// Recovery note for the case this modal handles badly on its own.
		//
		// iseo_connected is read from a PHP value localised at PAGE LOAD (see
		// includes/assets.php), so a user who connects in another tab — or who fixes their
		// credentials in Settings and comes back — still trips this guard on the stale flag
		// until the page is reloaded. To them the modal looks broken: they ARE connected and
		// it keeps refusing them. Without this note the only obvious way out is the CTA,
		// which sends an already-connected user back through onboarding.
		//
		// So: name the real fix (Save Changes + Test Server Connection in Settings) and say
		// plainly that the ✕ is the way back to what they were doing. Button labels are
		// quoted verbatim from views/settings/index.php — if those are renamed, rename them
		// here too or this becomes a set of instructions pointing at nothing.
		?>
		<div style="margin-top:22px; padding-top:18px; border-top:1px solid #e5e7eb; font-size:13px; color:#6b7280; line-height:1.6; text-align:left;">
			<strong style="display:block; margin-bottom:4px; color:#374151; font-weight:600;">Already have your API Key and Site Code?</strong>
			Open <a id="iseo-guard-settings-link" href="<?php echo esc_url( admin_url( 'admin.php?page=improveseo_settings' ) ); ?>">Settings</a>,
			click <strong>Save Changes</strong>, then run <strong>Test Server Connection</strong>.
			Once it reports success, close this message with <strong>&times;</strong> and carry on generating.
		</div>
	</div>
</div>

<style>
@keyframes iseoGuardFadeIn {
	from { opacity: 0; transform: translateY(12px) scale(0.97); }
	to   { opacity: 1; transform: translateY(0) scale(1); }
}
/* Scoped to the CTA specifically. This was `#iseo-connection-guard-overlay a:hover`,
   which hit EVERY link in the modal — so the plain-text Settings link in the note
   below the button would grow a dark green block on hover and read as a second,
   competing button. */
#iseo-guard-connect-btn:hover {
	background: #0e6b5e !important;
}
#iseo-guard-settings-link {
	color: #0f7b6c;
	font-weight: 600;
	text-decoration: underline;
}
#iseo-guard-settings-link:hover {
	color: #0e6b5e;
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

	// Set the connect button's href from the localized PHP variable.
	var onboardingUrl = (typeof main_ajax_vars !== 'undefined' && main_ajax_vars.iseo_onboarding_url)
		? main_ajax_vars.iseo_onboarding_url
		: '';
	if (connectBtn && onboardingUrl) {
		connectBtn.href = onboardingUrl;
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

	/**
	 * @returns {boolean} true if connected, false if not (modal shown).
	 */
	window.iseoRequireConnection = function() {
		var connected = (typeof main_ajax_vars !== 'undefined' && main_ajax_vars.iseo_connected === '1');
		if (!connected) {
			showModal();
			return false;
		}
		return true;
	};
})();
</script>
