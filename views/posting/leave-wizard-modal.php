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
 *       'wizard_action'   => 'create_post_single', // this wizard's ?action=, for its close (X)
 *   ));
 *
 * A wizard that saves over AJAX calls window.iseoLeaveWizard.disarm() once it has saved.
 * A wizard's close (X) is any `.iseo-wizard-close` button: it asks the same question and, on
 * "Leave anyway", returns to the page the wizard was started from (see the script at the end).
 */
$while_open      = isset( $while_open ) ? (string) $while_open : '';
$saves_on_submit = isset( $saves_on_submit ) ? (string) $saves_on_submit : '';
$wizard_action   = isset( $wizard_action ) ? (string) $wizard_action : '';
?>
<!-- ── Leave-wizard confirmation ──────────────────────────────────────────────
     Nothing in a post wizard is saved until its final step submits, so a click on the
     WordPress admin sidebar or toolbar mid-wizard silently threw the whole project away.
     Those links are intercepted and this modal asks first, on every step — including
     step 1, before anything has been typed.

     Only the sidebar and toolbar. Closing the tab, reloading or pressing Back still falls
     to posting.js' window.onbeforeunload, since browsers show only their own stock text
     for those and a page can't substitute this message there.

     Shown in the shared popup (assets/js/iseo-popups.js), so it looks like every other popup.
     Its title, message and two buttons are unchanged. -->
<div id="iseo-leave-wizard-config" hidden data-while-open="<?php echo esc_attr( $while_open ); ?>" data-saves-on-submit="<?php echo esc_attr( $saves_on_submit ); ?>"></div>

<script>
(function () {
	var config = document.getElementById('iseo-leave-wizard-config');
	if (!config || typeof iseoModal === 'undefined') { return; }

	var whileOpen     = config.getAttribute('data-while-open');
	var savesOnSubmit = config.getAttribute('data-saves-on-submit');
	var asking        = false;
	var saved         = false;

	// Once the project is saved, leaving is exactly what should happen, so the prompt
	// stands down. AJAX-saving wizards (Bulk) call this from their success handler.
	// confirm(url): the same prompt, for the wizard's own close (X) rather than a link.
	window.iseoLeaveWizard = {
		disarm: function () { saved = true; },
		confirm: function (url) { open(url); }
	};

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

	// Same title, message and buttons as before, in the shared popup. "Stay on this page" is
	// the primary (teal) button and has focus; "Leave anyway" is the secondary button and is
	// the only way to leave. ×, Esc and a click outside also stay.
	function open(url) {
		if (asking) { return; }
		asking = true;
		iseoModal.warning({
			title: <?php echo wp_json_encode( __( 'Are you sure you want to leave?', 'improveseo' ) ); ?>,
			message: <?php echo wp_json_encode( __( 'This project will only be saved at the end of this Wizard. You will need to start over again if you leave now.', 'improveseo' ) ); ?>,
			primary: { label: <?php echo wp_json_encode( __( 'Stay on this page', 'improveseo' ) ); ?> },
			secondary: { label: <?php echo wp_json_encode( __( 'Leave anyway', 'improveseo' ) ); ?> }
		}).then(function (result) {
			asking = false;
			if (result !== 'secondary') { return; }
			// Already confirmed here — don't let posting.js' generic beforeunload
			// prompt ask a second time.
			window.onbeforeunload = null;
			window.location.href = url;
		});
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
})();
</script>

<script>
/*
 * The wizard's close (X): ask "Are you sure?", then go back to the page the wizard was
 * started from. Each wizard is started from several places (the Create page, the Single
 * Post screen, Bulk Post Projects, Keyword Lists, onboarding…), all of them links to the
 * wizard page, so the page that linked here (the referrer) is where to return.
 *
 * The referrer is remembered per wizard for this tab, because a failed save posts the form
 * and redirects back to the wizard, and the referrer is then the wizard itself. Only
 * same-site wp-admin addresses are used. With nothing usable, the Create page.
 */
(function () {
	var action   = <?php echo wp_json_encode( $wizard_action ); ?>;
	var KEY      = 'iseoWizardReturn:' + (action || 'wizard');
	var fallback = <?php echo wp_json_encode( admin_url( 'admin.php?page=improveseo_posting' ) ); ?>;

	function adminUrl(raw) {
		try {
			var u = new URL(raw, window.location.href);
			if (u.origin !== window.location.origin || u.pathname.indexOf('/wp-admin/') === -1) { return null; }
			return u;
		} catch (e) { return null; }
	}
	function isThisWizard(u) {
		return u.searchParams.get('page') === 'improveseo_posting'
			&& (!action || u.searchParams.get('action') === action);
	}
	function remember(href) { try { sessionStorage.setItem(KEY, href); } catch (e) {} }
	function remembered() {
		try { var u = adminUrl(sessionStorage.getItem(KEY) || ''); return u ? u.href : null; } catch (e) { return null; }
	}

	var ref = document.referrer ? adminUrl(document.referrer) : null;
	var returnUrl;
	if (ref && !isThisWizard(ref)) {
		returnUrl = ref.href;
		remember(returnUrl);
	} else if (ref) {
		// Back here from the wizard's own form (a save that failed validation).
		returnUrl = remembered() || fallback;
	} else {
		returnUrl = fallback;
	}

	document.addEventListener('click', function (e) {
		var btn = e.target && e.target.closest ? e.target.closest('.iseo-wizard-close') : null;
		if (!btn) { return; }
		e.preventDefault();
		if (window.iseoLeaveWizard && typeof window.iseoLeaveWizard.confirm === 'function') {
			window.iseoLeaveWizard.confirm(returnUrl);
		} else {
			window.onbeforeunload = null;
			window.location.href = returnUrl;
		}
	});
})();
</script>
