/**
 * Keyword Generator daily allowance notice.
 *
 * The allowance is enforced by the ImproveSEO server — a daily number of Keyword Generator runs,
 * set by plan. When it is spent the server answers 429 with code KEYWORD_DAILY_LIMIT_REACHED, and
 * this file turns that answer into the notice: the message and a single [Ok]. On the plans that
 * also get the manual route (the server says which via `manual_lists_hint`) the notice links to
 * the manual keyword-list screen.
 *
 * Shown in the shared popup (assets/js/iseo-popups.js), so it has the same look as every other
 * popup in the plugin; that file also owns focus, Esc and click-outside.
 *
 * Loaded on every plugin screen (includes/assets.php) because generate() runs both on the Keyword
 * Generator page (views/features/keyword.php) and inside the post wizards
 * (modules/GenerateAIpopup.php).
 *
 * Built with DOM APIs and textContent only — nothing from the server is ever parsed as HTML.
 */
(function (window, document) {
	'use strict';

	var LIMIT_CODE = 'KEYWORD_DAILY_LIMIT_REACHED';
	// Fallback when an older server omits manual_lists_hint: Optimize and Scale, by slug or name.
	var MANUAL_LISTS_PLAN_SLUGS = ['starter', 'pro', 'optimize', 'scale'];

	var config = window.improveseoKeywordAllowance || {};
	var i18n = config.i18n || {};
	var TEXT = {
		title: i18n.title || 'Daily limit reached',
		exhausted: i18n.exhausted || 'Your daily Keyword Generator tool allowance is exhausted. You will be able to use the Keyword Generator tool again tomorrow.',
		manualBefore: i18n.manualBefore || 'Meanwhile you have the option to manually create',
		manualLink: i18n.manualLink || 'keyword lists',
		ok: i18n.ok || 'Ok'
	};

	/** The server's limit payload when this failure is the daily allowance, otherwise null. */
	function limitPayload(xhr) {
		if (!xhr || xhr.status !== 429) {
			return null;
		}
		var body = xhr.responseJSON;
		if (!body && xhr.responseText) {
			try { body = JSON.parse(xhr.responseText); } catch (e) { body = null; }
		}
		return body && body.code === LIMIT_CODE ? body : null;
	}

	function offersManualLists(body) {
		if (typeof body.manual_lists_hint === 'boolean') {
			return body.manual_lists_hint;
		}
		return MANUAL_LISTS_PLAN_SLUGS.indexOf(String(body.plan_slug || '').toLowerCase()) !== -1;
	}

	/** "Meanwhile you have the option to manually create <keyword lists>." */
	function manualListsLine(newTab) {
		var p = document.createElement('p');
		p.appendChild(document.createTextNode(TEXT.manualBefore + ' '));
		if (config.manualListUrl) {
			var link = document.createElement('a');
			link.href = config.manualListUrl;
			link.textContent = TEXT.manualLink;
			if (newTab) {
				link.target = '_blank';
				link.rel = 'noopener noreferrer';
			}
			p.appendChild(link);
		} else {
			p.appendChild(document.createTextNode(TEXT.manualLink));
		}
		p.appendChild(document.createTextNode('.'));
		return p;
	}

	function show(opts) {
		if (!window.iseoModal) {
			window.alert(TEXT.exhausted);
			return;
		}
		var returnFocus = opts.returnFocus;
		window.iseoModal.info({
			title: TEXT.title,
			message: TEXT.exhausted,
			content: opts.manualLists ? manualListsLine(opts.newTab) : null,
			primary: { label: TEXT.ok },
			onClose: function () {
				if (returnFocus && typeof returnFocus.focus === 'function') {
					try { returnFocus.focus(); } catch (e) { /* element may be gone */ }
				}
			}
		});
	}

	/**
	 * Call first in a keyword-generation error handler. Returns true when the failure was the
	 * daily allowance and the notice is now showing (the caller should stop), false otherwise.
	 *
	 * opts.newTab      open the keyword-lists link in a new tab (used inside the post wizards)
	 * opts.returnFocus element to focus again when the notice closes
	 */
	window.iseoHandleKeywordGeneratorError = function (xhr, opts) {
		var body = limitPayload(xhr);
		if (!body) {
			return false;
		}
		opts = opts || {};
		show({
			manualLists: offersManualLists(body),
			newTab: !!opts.newTab,
			returnFocus: opts.returnFocus || null
		});
		return true;
	};
})(window, document);
