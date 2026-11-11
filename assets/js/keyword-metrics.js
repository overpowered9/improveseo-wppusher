/**
 * Keyword Generator: search volume, difficulty and intent for the generated keywords.
 *
 * After generate() (views/features/keyword.php) fills #output, it calls
 * window.iseoKeywordMetrics.load(keywords). This file fetches the metrics in ONE bulk call via
 * admin-ajax (improveseo_keyword_metrics, modules/keyword-data.php) and renders a sortable table:
 *
 *   Keyword · Monthly searches (US) · Difficulty · Intent · Write a post
 *
 * Missing values show as "—", never 0. The latest metrics are mirrored into the hidden
 * #iseo_kw_metrics_json so wt-script.js can store them with a saved list, and "Create bulk
 * project" saves the checked keywords as a list and opens the Bulk wizard on it.
 *
 * Built with DOM APIs and textContent only — nothing from the server is parsed as HTML.
 */
(function (window, document, $) {
	'use strict';

	var cfg = window.improveseoKeywordMetrics || {};
	var i18n = cfg.i18n || {};
	var T = function (key, fallback) { return i18n[key] || fallback; };

	var state = {
		keywords: [],      // display order as generated
		items: {},         // normalised keyword -> item
		sortKey: null,     // 'search_volume' | 'keyword_difficulty' | null
		sortDir: 'desc',
		listId: 0,         // set once the user saves the results as a list
		seed: '',
		loading: false
	};

	var root, tableWrap, stateBox, countEl, bulkBtn, hiddenJson;

	function normalize(kw) {
		return String(kw || '').replace(/\s+/g, ' ').trim().toLowerCase();
	}

	function fmtNumber(n) {
		if (n === null || n === undefined || isNaN(n)) { return null; }
		try { return Number(n).toLocaleString(); } catch (e) { return String(n); }
	}

	function el(tag, className, text) {
		var node = document.createElement(tag);
		if (className) { node.className = className; }
		if (text !== undefined && text !== null) { node.textContent = text; }
		return node;
	}

	function ensureRoot() {
		if (root) { return root; }
		root = document.getElementById('iseo-kw-metrics');
		if (!root) { return null; }
		tableWrap = root.querySelector('.iseo-kwm-wrap');
		stateBox = root.querySelector('.iseo-kwm-state');
		countEl = root.querySelector('.iseo-kwm-count');
		bulkBtn = root.querySelector('.iseo-kwm-bulk');
		hiddenJson = document.getElementById('iseo_kw_metrics_json');
		if (bulkBtn) { bulkBtn.addEventListener('click', createBulkProject); }
		return root;
	}

	function setState(kind, message, retry) {
		if (!stateBox) { return; }
		stateBox.className = 'iseo-kwm-state' + (kind ? ' iseo-kwm-state--' + kind : '');
		stateBox.textContent = '';
		stateBox.hidden = !message;
		if (!message) { return; }
		if (kind === 'loading') { stateBox.appendChild(el('span', 'iseo-kwm-spinner')); }
		stateBox.appendChild(document.createTextNode(message));
		if (retry) {
			var btn = el('button', 'iseo-kwm-retry', T('retry', 'Retry'));
			btn.type = 'button';
			btn.addEventListener('click', function () { load(state.keywords); });
			stateBox.appendChild(btn);
		}
	}

	function syncHidden() {
		if (!hiddenJson) { return; }
		var out = [];
		state.keywords.forEach(function (kw) {
			var item = state.items[normalize(kw)];
			if (item) { out.push(item); }
		});
		hiddenJson.value = out.length ? JSON.stringify(out) : '';
	}

	function sortedKeywords() {
		var list = state.keywords.slice();
		if (!state.sortKey) { return list; }
		var key = state.sortKey, dir = state.sortDir === 'asc' ? 1 : -1;
		return list
			.map(function (kw, idx) { return { kw: kw, idx: idx }; })
			.sort(function (a, b) {
				var ia = state.items[normalize(a.kw)], ib = state.items[normalize(b.kw)];
				var va = ia && ia[key] !== null && ia[key] !== undefined ? Number(ia[key]) : null;
				var vb = ib && ib[key] !== null && ib[key] !== undefined ? Number(ib[key]) : null;
				if (va === null && vb === null) { return a.idx - b.idx; }
				if (va === null) { return 1; }   // unknown values always last
				if (vb === null) { return -1; }
				if (va === vb) { return a.idx - b.idx; }
				return (va - vb) * dir;
			})
			.map(function (x) { return x.kw; });
	}

	function writePostUrl(kw) {
		var url = cfg.writePostUrl || '';
		if (!url) { return '#'; }
		url += '&keyword=' + encodeURIComponent(kw) + '&kw_src=finder';
		if (state.listId > 0) { url += '&kw_list=' + state.listId; }
		return url;
	}

	function updateSelection() {
		if (!tableWrap) { return; }
		var checked = tableWrap.querySelectorAll('tbody input[type="checkbox"]:checked').length;
		if (countEl) { countEl.textContent = checked ? checked + ' ' + T('selected', 'selected') : ''; }
		if (bulkBtn) { bulkBtn.disabled = checked === 0 || state.loading; }
	}

	function render() {
		if (!tableWrap) { return; }
		tableWrap.textContent = '';
		if (!state.keywords.length) {
			tableWrap.hidden = true;
			return;
		}
		tableWrap.hidden = false;

		var table = el('table', 'iseo-kwm-table');
		var thead = el('thead');
		var hr = el('tr');

		var thCheck = el('th', 'iseo-kwm-check');
		var all = document.createElement('input');
		all.type = 'checkbox';
		all.setAttribute('aria-label', T('selectAll', 'Select all'));
		all.addEventListener('change', function () {
			tableWrap.querySelectorAll('tbody input[type="checkbox"]').forEach(function (cb) { cb.checked = all.checked; });
			updateSelection();
		});
		thCheck.appendChild(all);
		hr.appendChild(thCheck);

		hr.appendChild(el('th', '', T('keyword', 'Keyword')));
		hr.appendChild(sortableTh('search_volume', T('volume', 'Monthly searches (US)')));
		hr.appendChild(sortableTh('keyword_difficulty', T('difficulty', 'Difficulty')));
		hr.appendChild(el('th', '', T('intent', 'Intent')));
		var thActions = el('th');
		thActions.appendChild(el('span', 'screen-reader-text', T('actions', 'Actions')));
		hr.appendChild(thActions);
		thead.appendChild(hr);
		table.appendChild(thead);

		var tbody = el('tbody');
		sortedKeywords().forEach(function (kw) {
			var item = state.items[normalize(kw)] || null;
			var tr = el('tr');

			var tdCheck = el('td', 'iseo-kwm-check');
			var cb = document.createElement('input');
			cb.type = 'checkbox';
			cb.value = kw;
			cb.setAttribute('aria-label', kw);
			cb.addEventListener('change', updateSelection);
			tdCheck.appendChild(cb);
			tr.appendChild(tdCheck);

			tr.appendChild(el('td', '', kw));

			var vol = item ? fmtNumber(item.search_volume) : null;
			tr.appendChild(el('td', 'iseo-kwm-num' + (vol === null ? ' iseo-kwm-na' : ''), vol === null ? '—' : vol));

			var diff = item && item.keyword_difficulty !== null && item.keyword_difficulty !== undefined ? String(item.keyword_difficulty) : null;
			tr.appendChild(el('td', 'iseo-kwm-num' + (diff === null ? ' iseo-kwm-na' : ''), diff === null ? '—' : diff));

			var tdIntent = el('td', item && item.intent ? '' : 'iseo-kwm-na');
			if (item && item.intent) {
				tdIntent.appendChild(el('span', 'iseo-kwm-intent iseo-kwm-intent--' + item.intent, item.intent));
			} else {
				tdIntent.textContent = '—';
			}
			tr.appendChild(tdIntent);

			var tdAct = el('td');
			var link = el('a', 'iseo-kwm-write', T('writePost', 'Write a post'));
			link.href = writePostUrl(kw);
			tdAct.appendChild(link);
			tr.appendChild(tdAct);

			tbody.appendChild(tr);
		});
		table.appendChild(tbody);
		tableWrap.appendChild(table);
		updateSelection();
	}

	function sortableTh(key, label) {
		var th = el('th', 'iseo-kwm-sortable', label);
		th.tabIndex = 0;
		th.setAttribute('role', 'button');
		th.title = T('sortHint', 'Click to sort');
		if (state.sortKey === key) { th.className += ' iseo-kwm-sort-' + state.sortDir; }
		var toggle = function () {
			if (state.sortKey === key) {
				state.sortDir = state.sortDir === 'desc' ? 'asc' : 'desc';
			} else {
				state.sortKey = key;
				state.sortDir = 'desc';
			}
			render();
		};
		th.addEventListener('click', toggle);
		th.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); toggle(); } });
		return th;
	}

	function load(keywords) {
		if (!ensureRoot()) { return; }
		keywords = (keywords || []).map(function (k) { return String(k).trim(); }).filter(Boolean);
		state.keywords = keywords;
		state.items = {};
		state.sortKey = null;
		state.listId = 0;
		state.seed = $('#input').val() || '';
		root.hidden = false;
		syncHidden();
		render();

		if (!keywords.length) {
			setState('', T('empty', 'No keywords yet. Generate keywords to see their search data.'));
			return;
		}

		state.loading = true;
		updateSelection();
		setState('loading', T('loading', 'Fetching search volume, difficulty and intent…'));

		$.ajax({
			url: cfg.ajaxUrl,
			type: 'POST',
			dataType: 'json',
			timeout: 60000,
			data: { action: 'improveseo_keyword_metrics', nonce: cfg.nonce, keywords: keywords }
		}).done(function (resp) {
			state.loading = false;
			if (!resp || !resp.success) {
				var msg = (resp && resp.data && resp.data.message) || T('failed', 'Search data could not be loaded.');
				if (resp && resp.data && resp.data.not_connected && typeof window.iseoShowConnectionGuard === 'function') {
					window.iseoShowConnectionGuard();
				}
				setState('error', msg, true);
				render();
				return;
			}
			var items = (resp.data && resp.data.items) || [];
			items.forEach(function (item) {
				if (item && item.keyword) { state.items[normalize(item.keyword)] = item; }
			});
			syncHidden();
			render();
			if (!items.length) {
				setState('warning', T('noData', 'No search data came back for these keywords.'));
			} else if (resp.data.warning) {
				setState('warning', resp.data.warning);
			} else {
				setState('', '');
			}
		}).fail(function (xhr) {
			state.loading = false;
			var msg = T('failed', 'Search data could not be loaded.');
			if (xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
				msg = xhr.responseJSON.data.message;
			}
			setState('error', msg, true);
			render();
		});
	}

	function clear() {
		if (!ensureRoot()) { return; }
		state.keywords = [];
		state.items = {};
		state.listId = 0;
		syncHidden();
		render();
		setState('', '');
		root.hidden = true;
	}

	function setListId(id) {
		state.listId = parseInt(id, 10) || 0;
		render();
	}

	function createBulkProject() {
		if (!tableWrap || state.loading) { return; }
		var selected = [];
		tableWrap.querySelectorAll('tbody input[type="checkbox"]:checked').forEach(function (cb) { selected.push(cb.value); });
		if (!selected.length) { return; }

		var metrics = [];
		selected.forEach(function (kw) {
			var item = state.items[normalize(kw)];
			if (item) { metrics.push(item); }
		});

		var today = new Date();
		var stamp = today.getFullYear() + '-' + ('0' + (today.getMonth() + 1)).slice(-2) + '-' + ('0' + today.getDate()).slice(-2);
		var name = ((state.seed || 'keywords').trim() + ' ' + T('selection', 'selection') + ' ' + stamp).slice(0, 50);

		var label = bulkBtn.textContent;
		bulkBtn.disabled = true;
		bulkBtn.textContent = T('saving', 'Saving…');

		var nonce = (typeof window.improveseo_vars !== 'undefined' && window.improveseo_vars.nonce) ? window.improveseo_vars.nonce : cfg.nonce;

		$.post(cfg.ajaxUrl, {
			action: 'sw_saved_search_results_keyword',
			nonce: nonce,
			proj_name: name,
			search_results: selected.join('\n'),
			metrics: metrics.length ? JSON.stringify(metrics) : ''
		}).done(function (resp) {
			var data = (resp && resp.data) || {};
			var id = parseInt(data.id, 10) || 0;
			if (resp && resp.success && id > 0 && cfg.bulkUrl) {
				window.location.href = cfg.bulkUrl + '&keyword_list=' + id;
				return;
			}
			bulkBtn.disabled = false;
			bulkBtn.textContent = label;
			if (window.iseoModal) { window.iseoModal.error({ title: T('bulkSelected', 'Create bulk project'), message: (data && data.message) || T('saveFailed', 'The keyword list could not be saved. Please try again.') }); }
		}).fail(function () {
			bulkBtn.disabled = false;
			bulkBtn.textContent = label;
			if (window.iseoModal) { window.iseoModal.error({ title: T('bulkSelected', 'Create bulk project'), message: T('saveFailed', 'The keyword list could not be saved. Please try again.') }); }
		});
	}

	$(function () {
		ensureRoot();
		// "Clear Results" (wt-script.js) empties #output; the table follows.
		$(document).on('click', '.clear-search-results', clear);
	});

	window.iseoKeywordMetrics = { load: load, clear: clear, setListId: setListId };
})(window, document, jQuery);
