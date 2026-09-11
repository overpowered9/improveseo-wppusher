/**
 * Keyword Generator daily allowance notice.
 *
 * The allowance is enforced by the ImproveSEO server — a daily number of Keyword Generator runs,
 * set by plan. When it is spent the server answers 429 with code KEYWORD_DAILY_LIMIT_REACHED, and
 * this file turns that answer into the notice: the message and a single [Ok]. On the plans that
 * also get the manual route (the server says which via `manual_lists_hint`) the notice links to
 * the manual keyword-list screen.
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
		exhausted: i18n.exhausted || 'Your daily Keyword Generator tool allowance is exhausted. You will be able to use the Keyword Generator tool again tomorrow.',
		manualBefore: i18n.manualBefore || 'Meanwhile you have the option to manually create',
		manualLink: i18n.manualLink || 'keyword lists',
		ok: i18n.ok || 'Ok'
	};

	var overlay = null;
	var returnFocusTo = null;

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

	function el(tag, className) {
		var node = document.createElement(tag);
		if (className) {
			node.className = className;
		}
		return node;
	}

	function infoIcon() {
		var ns = 'http://www.w3.org/2000/svg';
		var svg = document.createElementNS(ns, 'svg');
		svg.setAttribute('width', '44');
		svg.setAttribute('height', '44');
		svg.setAttribute('viewBox', '0 0 24 24');
		svg.setAttribute('fill', 'none');
		svg.setAttribute('stroke', 'currentColor');
		svg.setAttribute('stroke-width', '1.5');
		svg.setAttribute('stroke-linecap', 'round');
		svg.setAttribute('stroke-linejoin', 'round');
		svg.setAttribute('aria-hidden', 'true');
		svg.setAttribute('focusable', 'false');
		var circle = document.createElementNS(ns, 'circle');
		circle.setAttribute('cx', '12');
		circle.setAttribute('cy', '12');
		circle.setAttribute('r', '10');
		svg.appendChild(circle);
		[['12', '8', '12', '12'], ['12', '16', '12.01', '16']].forEach(function (p) {
			var line = document.createElementNS(ns, 'line');
			line.setAttribute('x1', p[0]);
			line.setAttribute('y1', p[1]);
			line.setAttribute('x2', p[2]);
			line.setAttribute('y2', p[3]);
			svg.appendChild(line);
		});
		return svg;
	}

	function focusables() {
		return overlay ? overlay.querySelectorAll('a[href], button:not([disabled])') : [];
	}

	function onKeydown(e) {
		if (!overlay) {
			return;
		}
		if (e.key === 'Escape' || e.key === 'Esc') {
			e.preventDefault();
			close();
			return;
		}
		if (e.key === 'Tab') {
			// Keep focus inside the dialog while it is open.
			var items = focusables();
			if (!items.length) {
				return;
			}
			var first = items[0];
			var last = items[items.length - 1];
			if (e.shiftKey && document.activeElement === first) {
				e.preventDefault();
				last.focus();
			} else if (!e.shiftKey && document.activeElement === last) {
				e.preventDefault();
				first.focus();
			}
		}
	}

	function close() {
		if (!overlay) {
			return;
		}
		if (overlay.parentNode) {
			overlay.parentNode.removeChild(overlay);
		}
		overlay = null;
		document.removeEventListener('keydown', onKeydown, true);
		var target = returnFocusTo;
		returnFocusTo = null;
		if (target && typeof target.focus === 'function') {
			try { target.focus(); } catch (e) { /* element may be gone */ }
		}
	}

	function show(opts) {
		close();
		returnFocusTo = opts.returnFocus || document.activeElement;

		overlay = el('div', 'iseo-kw-allowance-overlay');
		var dialog = el('div', 'iseo-kw-allowance-dialog');
		dialog.setAttribute('role', 'alertdialog');
		dialog.setAttribute('aria-modal', 'true');
		dialog.setAttribute('aria-describedby', 'iseo-kw-allowance-message');
		dialog.setAttribute('aria-label', 'Keyword Generator');

		var icon = el('div', 'iseo-kw-allowance-icon');
		icon.appendChild(infoIcon());

		var message = el('p', 'iseo-kw-allowance-message');
		message.id = 'iseo-kw-allowance-message';
		message.appendChild(document.createTextNode(TEXT.exhausted));
		if (opts.manualLists) {
			message.appendChild(document.createTextNode(' ' + TEXT.manualBefore + ' '));
			if (config.manualListUrl) {
				var link = el('a', 'iseo-kw-allowance-link');
				link.href = config.manualListUrl;
				link.textContent = TEXT.manualLink;
				if (opts.newTab) {
					link.target = '_blank';
					link.rel = 'noopener noreferrer';
				}
				message.appendChild(link);
			} else {
				message.appendChild(document.createTextNode(TEXT.manualLink));
			}
			message.appendChild(document.createTextNode('.'));
		}

		var ok = el('button', 'iseo-kw-allowance-ok');
		ok.type = 'button';
		ok.textContent = TEXT.ok;
		ok.addEventListener('click', close);

		dialog.appendChild(icon);
		dialog.appendChild(message);
		dialog.appendChild(ok);
		overlay.appendChild(dialog);

		// A click on the backdrop itself (not a drag that ends there) dismisses, like [Ok].
		overlay.addEventListener('mousedown', function (e) {
			if (e.target === overlay) {
				close();
			}
		});

		document.body.appendChild(overlay);
		document.addEventListener('keydown', onKeydown, true);
		ok.focus();
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
