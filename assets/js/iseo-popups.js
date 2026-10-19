/**
 * ImproveSEO popup system — the one place every popup in the plugin comes from.
 *
 *   iseoModal.error / warning / success / info(opts)   → Promise<'primary'|'secondary'|'dismiss'>
 *   iseoModal.confirm(opts) / iseoModal.destructive(opts) → Promise<boolean>
 *       Resolves true ONLY for the primary button. Cancel, ×, Esc and a click outside all
 *       resolve false, so a dismissal can never be mistaken for a confirmation.
 *   iseoModal.loading.show({ title, message }) / iseoModal.loading.hide()
 *   iseoToast.success / error / warning / info(title, message, { action, duration })
 *   iseoFieldError(field, message, { container, focus }) / iseoFieldError.clear(field)
 *   data-iseo-confirm="…" on a link, button or form — an async replacement for
 *       onclick="return confirm(…)"; the original click/submit runs once, after confirming.
 *
 * modal opts: { title, message, details: [{ label, value, tone }], icon,
 *               primary: { label, onClick }, secondary: { label, onClick },
 *               dismissible (default true), onClose }
 *
 * Markup is built on first use, so it works on every admin screen without per-view HTML.
 * Styles: assets/css/iseo-popups.css. The legacy ImproveSEONotification /
 * showImproveSEONotification / ImproveSEOLoading APIs are kept at the bottom as thin wrappers.
 */
(function (window, document) {
	'use strict';

	if (window.iseoModal) { return; }

	// wp.i18n when it is loaded (it is a declared dependency); plain strings otherwise.
	function __(text, domain) {
		return (window.wp && window.wp.i18n && typeof window.wp.i18n.__ === 'function')
			? window.wp.i18n.__(text, domain)
			: text;
	}

	// ── Icons: rounded 2px line icons, matching the brand icon set ─────────────────────────
	var ICON_PATHS = {
		'alert-circle': '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>',
		'alert-triangle': '<path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
		'check-circle': '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
		'info': '<circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>',
		'help': '<circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
		'trash': '<polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>',
		'image': '<rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>',
		'file-text': '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>',
		'link': '<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>',
		'x': '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>'
	};

	function icon(name) {
		return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' +
			(ICON_PATHS[name] || ICON_PATHS.info) + '</svg>';
	}

	// type → { tone (colour set), default icon }
	var TYPES = {
		error:       { tone: 'error',   icon: 'alert-circle' },
		warning:     { tone: 'warning', icon: 'alert-triangle' },
		success:     { tone: 'success', icon: 'check-circle' },
		info:        { tone: 'info',    icon: 'info' },
		confirm:     { tone: 'info',    icon: 'help' },
		destructive: { tone: 'error',   icon: 'trash' }
	};

	function el(tag, className, text) {
		var node = document.createElement(tag);
		if (className) { node.className = className; }
		if (text != null) { node.textContent = String(text); }
		return node;
	}

	function whenBody(fn) {
		if (document.body) { fn(); return; }
		document.addEventListener('DOMContentLoaded', fn);
	}

	var FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

	// Third-party admin scripts (Bootstrap's _enforceFocus, jQuery-Modal, Select2) listen on
	// document and pull focus or swallow keys while their own modal is open — and the wizard
	// popups ARE such modals. Stopping these events at our root means none of those listeners
	// ever see them; our own handlers are bound directly on the root, so they still run.
	var ISOLATED_EVENTS = ['focusin', 'focusout', 'keydown', 'keypress', 'keyup', 'input', 'mousedown', 'touchstart'];

	function isolate(root) {
		ISOLATED_EVENTS.forEach(function (type) {
			root.addEventListener(type, function (e) { e.stopPropagation(); });
		});
	}

	// ── Modal ───────────────────────────────────────────────────────────────────────────────
	var current = null; // { overlay, card, finish }
	var uid = 0;

	function openModal(type, opts) {
		opts = opts || {};
		var meta = TYPES[type] || TYPES.info;

		return new Promise(function (resolve) {
			whenBody(function () {
				// One modal at a time. A replaced modal resolves as a dismissal, so a confirm
				// that gets superseded can only ever come back false. Focus goes back to what
				// opened the FIRST modal, not to a button of the one being replaced.
				var returnFocus = document.activeElement;
				if (current) {
					returnFocus = current.returnFocus;
					current.finish('dismiss', true);
				}

				var id = 'iseo-ui-modal-' + (++uid);
				var dismissible = opts.dismissible !== false;
				var settled = false;

				var overlay = el('div', 'iseo-ui-overlay');
				var card = el('div', 'iseo-ui-modal');
				var isConfirm = (type === 'confirm' || type === 'destructive');
				card.setAttribute('role', (isConfirm || type === 'error' || type === 'warning') ? 'alertdialog' : 'dialog');
				card.setAttribute('aria-modal', 'true');
				card.setAttribute('aria-labelledby', id + '-title');
				card.setAttribute('tabindex', '-1');

				if (dismissible) {
					var closeBtn = el('button', 'iseo-ui-close');
					closeBtn.type = 'button';
					closeBtn.setAttribute('aria-label', __('Close', 'improveseo'));
					closeBtn.innerHTML = icon('x');
					closeBtn.addEventListener('click', function () { finish('dismiss'); });
					card.appendChild(closeBtn);
				}

				var iconTile = el('div', 'iseo-ui-icon iseo-ui-icon--' + meta.tone);
				iconTile.innerHTML = icon(opts.icon || meta.icon);
				card.appendChild(iconTile);

				var title = el('h2', 'iseo-ui-title', opts.title || '');
				title.id = id + '-title';
				card.appendChild(title);

				if (opts.message) {
					var message = el('p', 'iseo-ui-message', opts.message);
					message.id = id + '-message';
					card.setAttribute('aria-describedby', message.id);
					card.appendChild(message);
				}

				if (opts.details && opts.details.length) {
					var details = el('div', 'iseo-ui-details');
					opts.details.forEach(function (row) {
						if (!row) { return; }
						var r = el('div', 'iseo-ui-details-row');
						r.appendChild(el('span', 'iseo-ui-details-label', row.label));
						r.appendChild(el('span', 'iseo-ui-details-value' + (row.tone === 'primary' ? ' iseo-ui-details-value--primary' : ''), row.value));
						details.appendChild(r);
					});
					card.appendChild(details);
				}

				// Buttons: secondary first (left / bottom when stacked), primary last.
				var actions = el('div', 'iseo-ui-actions');
				var primarySpec = opts.primary || { label: __('OK', 'improveseo') };
				var primaryBtn = el('button', 'iseo-ui-btn ' + (type === 'destructive' ? 'iseo-ui-btn--destructive' : 'iseo-ui-btn--primary'), primarySpec.label);
				primaryBtn.type = 'button';
				primaryBtn.addEventListener('click', function () { finish('primary'); });

				var secondaryBtn = null;
				if (opts.secondary) {
					secondaryBtn = el('button', 'iseo-ui-btn iseo-ui-btn--secondary', opts.secondary.label);
					secondaryBtn.type = 'button';
					secondaryBtn.addEventListener('click', function () { finish('secondary'); });
					actions.appendChild(secondaryBtn);
				}
				actions.appendChild(primaryBtn);
				card.appendChild(actions);

				overlay.appendChild(card);
				isolate(overlay);

				// Outside click. Only when the press also STARTED on the overlay, so a text
				// selection dragged out of the card never counts as a dismissal.
				var downOnOverlay = false;
				overlay.addEventListener('mousedown', function (e) { downOnOverlay = (e.target === overlay); });
				overlay.addEventListener('click', function (e) {
					if (e.target === overlay && downOnOverlay && dismissible) { finish('dismiss'); }
					downOnOverlay = false;
				});

				overlay.addEventListener('keydown', function (e) {
					if (e.key === 'Escape' || e.keyCode === 27) {
						e.preventDefault();
						if (dismissible) { finish('dismiss'); }
						return;
					}
					if (e.key === 'Tab' || e.keyCode === 9) {
						var items = Array.prototype.filter.call(card.querySelectorAll(FOCUSABLE), function (n) {
							return n.offsetParent !== null;
						});
						if (!items.length) { e.preventDefault(); return; }
						var first = items[0];
						var last = items[items.length - 1];
						if (e.shiftKey && (document.activeElement === first || document.activeElement === card)) {
							e.preventDefault();
							last.focus();
						} else if (!e.shiftKey && document.activeElement === last) {
							e.preventDefault();
							first.focus();
						}
					}
				});

				document.body.appendChild(overlay);

				// Labels never wrap: if any label overflows its half-width button, stack the
				// buttons full width (primary on top via column-reverse).
				if (secondaryBtn) {
					var overflows = [primaryBtn, secondaryBtn].some(function (b) {
						return b.scrollWidth > b.clientWidth + 1;
					});
					if (overflows) { actions.className += ' iseo-ui-actions--stacked'; }
				}

				// Destructive actions start on Cancel so Enter can't delete by accident.
				var initial = (type === 'destructive' && secondaryBtn) ? secondaryBtn : primaryBtn;
				initial.focus();

				function finish(result, replaced) {
					if (settled) { return; }
					settled = true;
					if (overlay.parentNode) { overlay.parentNode.removeChild(overlay); }
					if (current && current.overlay === overlay) { current = null; }
					if (!replaced && returnFocus && typeof returnFocus.focus === 'function' && document.contains(returnFocus)) {
						try { returnFocus.focus(); } catch (err) { /* element no longer focusable */ }
					}
					resolve(result);
					// Callbacks run after the modal is gone, so one may open the next popup.
					var spec = result === 'primary' ? primarySpec : (result === 'secondary' ? opts.secondary : null);
					if (spec && typeof spec.onClick === 'function') { spec.onClick(); }
					if (typeof opts.onClose === 'function') { opts.onClose(result); }
				}

				current = { overlay: overlay, card: card, finish: finish, returnFocus: returnFocus };
			});
		});
	}

	function confirmModal(type, opts) {
		opts = opts || {};
		var o = {};
		for (var k in opts) { if (Object.prototype.hasOwnProperty.call(opts, k)) { o[k] = opts[k]; } }
		o.primary = o.primary || { label: type === 'destructive' ? __('Delete', 'improveseo') : __('Confirm', 'improveseo') };
		o.secondary = o.secondary || { label: __('Cancel', 'improveseo') };
		return openModal(type, o).then(function (result) { return result === 'primary'; });
	}

	// ── Loading ─────────────────────────────────────────────────────────────────────────────
	var loadingEl = null;

	var loading = {
		show: function (opts) {
			opts = opts || {};
			whenBody(function () {
				if (!loadingEl) {
					loadingEl = el('div', 'iseo-ui-overlay iseo-ui-overlay--loading');
					var card = el('div', 'iseo-ui-modal');
					card.setAttribute('role', 'status');
					card.setAttribute('aria-live', 'polite');
					card.appendChild(el('div', 'iseo-ui-spinner'));
					card.appendChild(el('h2', 'iseo-ui-title'));
					card.appendChild(el('p', 'iseo-ui-message'));
					loadingEl.appendChild(card);
					isolate(loadingEl);
					document.body.appendChild(loadingEl);
				}
				loadingEl.querySelector('.iseo-ui-title').textContent = opts.title || __('Loading…', 'improveseo');
				loadingEl.querySelector('.iseo-ui-message').textContent = opts.message || __('Please wait…', 'improveseo');
				loadingEl.hidden = false;
			});
		},
		hide: function () {
			if (loadingEl) { loadingEl.hidden = true; }
		}
	};

	// ── Toasts ──────────────────────────────────────────────────────────────────────────────
	var toastStack = null;
	var MAX_TOASTS = 3;

	function toast(type, title, message, opts) {
		opts = opts || {};
		var meta = TYPES[type] || TYPES.info;
		// Errors stay until closed; everything else auto-dismisses after 6s.
		var duration = (opts.duration != null) ? opts.duration : (type === 'error' ? 0 : 6000);
		var handle = { close: function () {} };

		whenBody(function () {
			if (!toastStack) {
				toastStack = el('div', 'iseo-ui-toasts');
				document.body.appendChild(toastStack);
			}
			while (toastStack.children.length >= MAX_TOASTS) {
				toastStack.removeChild(toastStack.firstChild);
			}

			var node = el('div', 'iseo-ui-toast iseo-ui-toast--' + meta.tone);
			node.setAttribute('role', type === 'error' ? 'alert' : 'status');

			var ic = el('span', 'iseo-ui-toast-icon');
			ic.innerHTML = icon(meta.icon);
			node.appendChild(ic);

			var body = el('div', 'iseo-ui-toast-body');
			body.appendChild(el('p', 'iseo-ui-toast-title', title || ''));
			if (message) { body.appendChild(el('p', 'iseo-ui-toast-message', message)); }
			if (opts.action && opts.action.label) {
				var action;
				if (opts.action.href) {
					action = el('a', 'iseo-ui-toast-action', opts.action.label);
					action.href = opts.action.href;
				} else {
					action = el('button', 'iseo-ui-toast-action', opts.action.label);
					action.type = 'button';
				}
				action.addEventListener('click', function (e) {
					if (typeof opts.action.onClick === 'function') { opts.action.onClick(e); }
					dismiss();
				});
				body.appendChild(action);
			}
			node.appendChild(body);

			var closeBtn = el('button', 'iseo-ui-close');
			closeBtn.type = 'button';
			closeBtn.setAttribute('aria-label', __('Dismiss notification', 'improveseo'));
			closeBtn.innerHTML = icon('x');
			closeBtn.addEventListener('click', dismiss);
			node.appendChild(closeBtn);

			if (duration > 0) {
				// The bar's animation is the timer; hover/focus pause it in CSS.
				var bar = el('span', 'iseo-ui-toast-progress');
				bar.style.animationDuration = duration + 'ms';
				bar.addEventListener('animationend', dismiss);
				node.appendChild(bar);
			}

			toastStack.appendChild(node);

			function dismiss() {
				if (node.parentNode) { node.parentNode.removeChild(node); }
			}
			handle.close = dismiss;
		});

		return handle;
	}

	// ── Inline field errors ────────────────────────────────────────────────────────────────
	function resolveEl(target) {
		if (!target) { return null; }
		if (typeof target === 'string') { return document.querySelector(target); }
		if (target.jquery) { return target[0] || null; }
		return target;
	}

	function errorNodeFor(field, container) {
		var node = resolveEl(container);
		if (node) { return node; }
		var key = field.id || field.name;
		var next = field.nextElementSibling;
		if (next && next.classList.contains('iseo-ui-field-error') && next.getAttribute('data-iseo-error-for') === key) {
			return next;
		}
		node = el('div');
		node.setAttribute('data-iseo-error-for', key || '');
		field.insertAdjacentElement('afterend', node);
		return node;
	}

	function fieldError(target, message, opts) {
		opts = opts || {};
		var field = resolveEl(target);
		if (!field) { return; }
		var node = errorNodeFor(field, opts.container);
		if (!node.id) { node.id = (field.id || 'iseo-field') + '-iseo-error'; }
		node.classList.add('iseo-ui-field-error');
		node.setAttribute('role', 'alert');
		node.innerHTML = icon('alert-circle');
		node.appendChild(el('span', null, message));
		node.hidden = false;

		field.classList.add('iseo-ui-field-invalid');
		field.setAttribute('aria-invalid', 'true');
		field.setAttribute('aria-describedby', node.id);
		field.__iseoErrorNode = node;

		if (!field.__iseoErrorBound) {
			field.__iseoErrorBound = true;
			var clear = function () { clearFieldError(field); };
			field.addEventListener('input', clear);
			field.addEventListener('change', clear);
		}
		if (opts.focus !== false && typeof field.focus === 'function') { field.focus(); }
	}

	function clearFieldError(target) {
		var field = resolveEl(target);
		if (!field) { return; }
		field.classList.remove('iseo-ui-field-invalid');
		field.removeAttribute('aria-invalid');
		var node = field.__iseoErrorNode;
		if (node) {
			field.removeAttribute('aria-describedby');
			node.innerHTML = '';
			node.hidden = true;
		}
	}

	fieldError.clear = clearFieldError;

	// ── data-iseo-confirm: async replacement for onclick="return confirm(…)" ──────────────
	// The original click (link navigation, submit button) or form submit is held, the confirm
	// is shown, and only the primary button replays it — exactly once. Cancel, ×, Esc and an
	// outside click leave the page untouched.
	function confirmFromAttrs(node) {
		var variant = node.getAttribute('data-iseo-confirm-variant') === 'destructive' ? 'destructive' : 'confirm';
		var opts = {
			title: node.getAttribute('data-iseo-confirm-title') || __('Are you sure?', 'improveseo'),
			message: node.getAttribute('data-iseo-confirm') || ''
		};
		var label = node.getAttribute('data-iseo-confirm-label');
		if (label) { opts.primary = { label: label }; }
		var cancel = node.getAttribute('data-iseo-confirm-cancel');
		if (cancel) { opts.secondary = { label: cancel }; }
		return confirmModal(variant, opts);
	}

	document.addEventListener('click', function (e) {
		var node = e.target && e.target.closest ? e.target.closest('[data-iseo-confirm]') : null;
		if (!node || node.tagName === 'FORM') { return; }
		if (node.__iseoConfirmBypass) { node.__iseoConfirmBypass = false; return; }
		e.preventDefault();
		e.stopImmediatePropagation();
		confirmFromAttrs(node).then(function (ok) {
			if (!ok) { return; }
			node.__iseoConfirmBypass = true;
			node.click();
		});
	}, true);

	document.addEventListener('submit', function (e) {
		var form = e.target;
		if (!form || !form.hasAttribute || !form.hasAttribute('data-iseo-confirm')) { return; }
		if (form.__iseoConfirmBypass) { form.__iseoConfirmBypass = false; return; }
		e.preventDefault();
		e.stopImmediatePropagation();
		var submitter = e.submitter || null;
		confirmFromAttrs(form).then(function (ok) {
			if (!ok) { return; }
			form.__iseoConfirmBypass = true;
			if (typeof form.requestSubmit === 'function') {
				form.requestSubmit(submitter && submitter.form === form ? submitter : undefined);
			} else {
				form.submit();
			}
		});
	}, true);

	// ── Public API ──────────────────────────────────────────────────────────────────────────
	var iseoModal = {
		error:       function (opts) { return openModal('error', opts); },
		warning:     function (opts) { return openModal('warning', opts); },
		success:     function (opts) { return openModal('success', opts); },
		info:        function (opts) { return openModal('info', opts); },
		confirm:     function (opts) { return confirmModal('confirm', opts); },
		destructive: function (opts) { return confirmModal('destructive', opts); },
		close:       function () { if (current) { current.finish('dismiss'); } },
		loading:     loading
	};

	var iseoToast = {
		success: function (title, message, opts) { return toast('success', title, message, opts); },
		error:   function (title, message, opts) { return toast('error', title, message, opts); },
		warning: function (title, message, opts) { return toast('warning', title, message, opts); },
		info:    function (title, message, opts) { return toast('info', title, message, opts); }
	};

	window.iseoModal = iseoModal;
	window.iseoToast = iseoToast;
	window.iseoFieldError = fieldError;

	// ── Legacy APIs (kept so existing callers keep working; they render the new design) ────

	/**
	 * ImproveSEONotification.show({ title, message, type, buttonText, onClose })
	 * Single-button notice. onClose runs however it is closed (button, ×, Esc, outside) — the
	 * original contract. Anything that must only run from the button belongs in iseoModal.
	 */
	window.ImproveSEONotification = {
		show: function (options) {
			options = options || {};
			var type = TYPES[options.type] ? options.type : 'warning';
			var promise = openModal(type, {
				title: options.title || __('Notification', 'improveseo'),
				message: options.message || '',
				primary: { label: options.buttonText || __('OK', 'improveseo') },
				onClose: typeof options.onClose === 'function' ? function () { options.onClose(); } : null
			});
			return { close: iseoModal.close, promise: promise };
		},
		warning: function (message, title) { return this.show({ type: 'warning', message: message, title: title || __('Warning', 'improveseo') }); },
		error:   function (message, title) { return this.show({ type: 'error', message: message, title: title || __('Error', 'improveseo') }); },
		success: function (message, title) { return this.show({ type: 'success', message: message, title: title || __('Success', 'improveseo') }); },
		info:    function (message, title) { return this.show({ type: 'info', message: message, title: title || __('Information', 'improveseo') }); }
	};

	/**
	 * showImproveSEONotification(type, title, message, actionUrl)
	 * With an actionUrl the single button follows it (mailto in place, anything else in a new
	 * tab). Closing with ×, Esc or a click outside never follows the link.
	 */
	window.showImproveSEONotification = function (type, title, message, actionUrl) {
		var t = TYPES[type] ? type : 'info';
		var opts = { title: title, message: message };
		if (actionUrl) {
			var label;
			if (actionUrl.indexOf('mailto:') === 0) {
				label = __('Contact support', 'improveseo');
			} else if (actionUrl.indexOf('dashboard.improveseoplugin.com') !== -1) {
				label = __('Buy credits', 'improveseo');
			} else {
				label = __('Learn more', 'improveseo');
			}
			opts.primary = {
				label: label,
				onClick: function () {
					if (actionUrl.indexOf('mailto:') === 0) {
						window.location.href = actionUrl;
					} else {
						window.open(actionUrl, '_blank', 'noopener');
					}
				}
			};
		}
		return openModal(t, opts);
	};

	window.ImproveSEOLoading = {
		show: function (options) { loading.show(options); },
		hide: function () { loading.hide(); }
	};
})(window, document);
