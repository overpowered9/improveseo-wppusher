<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/*
 * Leave-wizard confirmation, shared by the Single and Bulk post wizards so both ask the
 * same way. Render it once per page:
 *
 *   View::render('posting.leave-wizard-modal', array(
 *       'while_open'      => '#exampleModal2', // ask only while this wizard modal is open; '' = the whole page
 *       'saves_on_submit' => 'main_form',      // a native submit of this form saves the project
 *   ));
 *
 * A wizard that saves over AJAX calls window.iseoLeaveWizard.disarm() once it has saved.
 */
$while_open      = isset( $while_open ) ? (string) $while_open : '';
$saves_on_submit = isset( $saves_on_submit ) ? (string) $saves_on_submit : '';
?>
<!-- ── Leave-wizard confirmation ──────────────────────────────────────────────
     Nothing in a post wizard is saved until its final step submits, so a click on the
     WordPress admin sidebar or toolbar mid-wizard silently threw the whole project away.
     Those links are intercepted and this modal asks first, on every step — including
     step 1, before anything has been typed.

     Only the sidebar and toolbar. Closing the tab, reloading or pressing Back still falls
     to posting.js' window.onbeforeunload, since browsers show only their own stock text
     for those and a page can't substitute this message there.

     z-index above the wizard's Bootstrap modal (1050) AND the admin sidebar itself (9990),
     which sits over that modal's backdrop — that is why the sidebar was clickable at all. -->
<div id="iseo-leave-wizard-overlay" hidden data-while-open="<?php echo esc_attr( $while_open ); ?>" data-saves-on-submit="<?php echo esc_attr( $saves_on_submit ); ?>" style="position:fixed; inset:0; z-index:1000000; background:rgba(0,0,0,0.55); backdrop-filter:blur(2px); display:flex; align-items:center; justify-content:center;">
	<div role="alertdialog" aria-modal="true" aria-labelledby="iseo-leave-wizard-title" aria-describedby="iseo-leave-wizard-text" style="background:#fff; border-radius:14px; padding:32px 28px 24px; max-width:440px; width:90%; text-align:center; box-shadow:0 20px 60px rgba(0,0,0,0.25); font-family:'Poppins','Lato',sans-serif;">
		<div style="margin-bottom:14px;">
			<svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="display:inline-block;">
				<path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
				<line x1="12" y1="9" x2="12" y2="13"></line>
				<line x1="12" y1="17" x2="12.01" y2="17"></line>
			</svg>
		</div>
		<h3 id="iseo-leave-wizard-title" style="margin:0 0 8px; font-size:18px; font-weight:700; color:#111827; line-height:1.3;">
			Are you sure you want to leave?
		</h3>
		<p id="iseo-leave-wizard-text" style="margin:0 0 24px; font-size:14px; color:#4b5563; line-height:1.55;">
			This project will only be saved at the end of this Wizard. You will need to start over again if you leave now.
		</p>
		<div style="display:flex; gap:10px; justify-content:center; flex-wrap:wrap;">
			<button type="button" id="iseo-leave-wizard-stay" class="iseo-leave-wizard-btn iseo-leave-wizard-btn-primary">Stay on this page</button>
			<button type="button" id="iseo-leave-wizard-go" class="iseo-leave-wizard-btn iseo-leave-wizard-btn-quiet">Leave anyway</button>
		</div>
	</div>
</div>

<style>
#iseo-leave-wizard-overlay[hidden] { display: none !important; }
.iseo-leave-wizard-btn {
	padding: 10px 20px;
	border-radius: 8px;
	font-size: 14px;
	font-weight: 600;
	cursor: pointer;
	font-family: inherit;
	transition: background-color 0.15s ease, color 0.15s ease, border-color 0.15s ease;
}
.iseo-leave-wizard-btn-primary { background: #1C7293; color: #fff; border: 1px solid #1C7293; }
.iseo-leave-wizard-btn-primary:hover,
.iseo-leave-wizard-btn-primary:focus-visible { background: #fff; color: #000; border-color: #000; }
.iseo-leave-wizard-btn-quiet { background: #fff; color: #374151; border: 1px solid #d1d5db; }
.iseo-leave-wizard-btn-quiet:hover,
.iseo-leave-wizard-btn-quiet:focus-visible { background: #f3f4f6; color: #111827; }
</style>

<script>
(function () {
	var overlay = document.getElementById('iseo-leave-wizard-overlay');
	if (!overlay) { return; }

	var stayBtn       = document.getElementById('iseo-leave-wizard-stay');
	var leaveBtn      = document.getElementById('iseo-leave-wizard-go');
	var whileOpen     = overlay.getAttribute('data-while-open');
	var savesOnSubmit = overlay.getAttribute('data-saves-on-submit');
	var pendingUrl    = null;
	var saved         = false;

	// Once the project is saved, leaving is exactly what should happen, so the prompt
	// stands down. AJAX-saving wizards (Bulk) call this from their success handler.
	window.iseoLeaveWizard = { disarm: function () { saved = true; } };

	// A form-saving wizard (Single) saves by submitting its form natively.
	// Bubble phase + defaultPrevented: a submit that another handler cancels (e.g. the
	// project-name check refusing a duplicate) leaves the user on the page, so the
	// prompt must stay armed for it.
	if (savesOnSubmit) {
		document.addEventListener('submit', function (e) {
			if (e.target && e.target.id === savesOnSubmit && !e.defaultPrevented) { saved = true; }
		});
	}

	// Whether leaving now would throw away an unsaved project. A wizard that is only a
	// modal on its page (Bulk) is in progress only while that modal is showing.
	function inProgress() {
		if (saved) { return false; }
		if (!whileOpen) { return true; }
		var wizard = document.querySelector(whileOpen);
		return !!wizard
			&& !wizard.classList.contains('hide_and_show_ai_popup')
			&& wizard.getClientRects().length > 0
			&& getComputedStyle(wizard).visibility !== 'hidden';
	}

	function open(url) {
		pendingUrl = url;
		overlay.hidden = false;
		if (stayBtn) { stayBtn.focus(); }
	}

	function close() {
		overlay.hidden = true;
		pendingUrl = null;
	}

	// Capture phase, so this runs before WordPress' own menu handlers and before the
	// browser follows the link.
	function guardLinks(e) {
		if (!inProgress()) { return; }

		var link = e.target.closest ? e.target.closest('a[href]') : null;
		if (!link || !e.currentTarget.contains(link)) { return; }

		var href = link.getAttribute('href');
		if (!href || href.charAt(0) === '#' || /^javascript:/i.test(href)) { return; }

		// Opening in a new tab or window leaves this page, and the wizard, untouched.
		if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) { return; }
		if (link.target && link.target !== '_self') { return; }

		e.preventDefault();
		e.stopPropagation();
		open(link.href);
	}

	['adminmenuwrap', 'wpadminbar'].forEach(function (id) {
		var area = document.getElementById(id);
		if (area) { area.addEventListener('click', guardLinks, true); }
	});

	if (stayBtn) { stayBtn.addEventListener('click', close); }

	if (leaveBtn) {
		leaveBtn.addEventListener('click', function () {
			var url = pendingUrl;
			close();
			if (!url) { return; }
			// Already confirmed here — don't let posting.js' generic beforeunload
			// prompt ask a second time.
			window.onbeforeunload = null;
			window.location.href = url;
		});
	}

	overlay.addEventListener('click', function (e) {
		if (e.target === overlay) { close(); }
	});

	// The wizard underneath is a Bootstrap modal, whose focus trap (jQuery's focusin on
	// document, which jQuery may build from a capturing 'focus' listener) pulls focus back
	// into the wizard — away from these buttons, leaving keyboard users tabbing through the
	// form behind the overlay. Window capture runs before anything on document, so focus
	// landing in this prompt is stopped there.
	['focus', 'focusin'].forEach(function (type) {
		window.addEventListener(type, function (e) {
			if (e.target && e.target.nodeType === 1 && overlay.contains(e.target)) { e.stopPropagation(); }
		}, true);
	});

	document.addEventListener('keydown', function (e) {
		if (overlay.hidden) { return; }
		if (e.key === 'Escape') {
			// Close only this prompt, not the wizard modal underneath it.
			e.stopPropagation();
			close();
		} else if (e.key === 'Tab' && stayBtn && leaveBtn) {
			// Two buttons, so Tab and Shift+Tab simply alternate between them.
			e.preventDefault();
			e.stopPropagation();
			(document.activeElement === stayBtn ? leaveBtn : stayBtn).focus();
		}
	}, true);
})();
</script>
