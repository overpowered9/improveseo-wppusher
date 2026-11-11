/**
 * Post wizards: search data for the chosen keyword.
 *
 * Single wizard (views/GenerateAIpopup/GenerateAIpopuphtml.php, #popup_form):
 *   - When the page opened from the Keyword Generator's "Write a post" link, or when the user
 *     leaves the seed field with a keyword in it, one admin-ajax call (improveseo_keyword_summary)
 *     fetches the keyword's search volume / difficulty / intent and shows one line under the
 *     field: "US monthly searches: 2,400 · Difficulty: 18 · Intent: commercial". No data: hidden.
 *   - The item is mirrored into #iseo_keyword_data_json so copyAIFieldsToHiddenInputs()
 *     (custom-plugin-script.js) carries it into the project and the post's _iseo_keyword_data.
 *
 * Bulk wizard: bulkSummary(keywords, metricsMap) writes "Search data is available for N of M
 * keywords" under the list, from the metrics saved with the list (never a lookup).
 *
 * Option B (Phase 4) adds the debounced prefetch and the overlap notice in this same file,
 * all of it gated on cfg.v3KeywordData === '1'.
 *
 * Built with DOM APIs and textContent only — nothing from the server is parsed as HTML.
 */
(function (window, document, $) {
	'use strict';

	var cfg = window.improveseoKeywordWizard || {};
	var i18n = cfg.i18n || {};

	var lookups = {};        // normalised keyword -> true, so a keyword is looked up once per page
	var LOOKUP_CAP = 3;      // typed keywords looked up per wizard session (the finder's are free: cached)
	var lookupCount = 0;
	var current = null;      // the item currently shown

	function normalize(kw) {
		return String(kw || '').replace(/\s+/g, ' ').trim().toLowerCase();
	}

	function showLine(line, item) {
		var box = document.getElementById('iseo-kw-summary');
		var hidden = document.getElementById('iseo_keyword_data_json');
		current = item || null;
		if (hidden) { hidden.value = item ? JSON.stringify(item) : ''; }
		if (!box) { return; }
		box.textContent = line || '';
		box.style.display = line ? 'block' : 'none';
	}

	function fetchSummary(keyword, listId, fromFinder) {
		var key = normalize(keyword);
		if (!key || !cfg.ajaxUrl) { return; }
		if (lookups[key]) { return; }
		if (!fromFinder && lookupCount >= LOOKUP_CAP) { return; }
		lookups[key] = true;
		if (!fromFinder) { lookupCount++; }

		$.ajax({
			url: cfg.ajaxUrl,
			type: 'POST',
			dataType: 'json',
			timeout: 30000,
			data: { action: 'improveseo_keyword_summary', nonce: cfg.nonce, keyword: keyword, kw_list: listId || 0 }
		}).done(function (resp) {
			// The field may have changed while the request ran; only show the line for what is in it now.
			var now = normalize($('#seed_keyword').val());
			if (now !== key) { return; }
			if (resp && resp.success && resp.data && resp.data.item && resp.data.line) {
				showLine(resp.data.line, resp.data.item);
			} else {
				showLine('', null);
				if (resp && resp.data && resp.data.reason && window.console) {
					window.console.info('[ImproveSEO] no search data for "' + keyword + '": ' + resp.data.reason);
				}
			}
		}).fail(function () {
			showLine('', null);
		});
	}

	function bulkSummary(keywords, metricsMap) {
		var box = document.getElementById('iseo-bulk-kw-summary');
		if (!box) { return; }
		var list = String(keywords || '').split(/\r\n|\r|\n/).map(function (k) { return k.trim(); }).filter(Boolean);
		var have = 0;
		if (metricsMap && typeof metricsMap === 'object') {
			list.forEach(function (kw) { if (metricsMap[normalize(kw)]) { have++; } });
		}
		if (!list.length || !have) {
			box.textContent = '';
			box.style.display = 'none';
			return;
		}
		var tpl = i18n.bulkSummary || 'Search data (US monthly searches) is available for %1$s of %2$s keywords.';
		box.textContent = tpl.replace('%1$s', String(have)).replace('%2$s', String(list.length));
		box.style.display = 'block';
	}

	/* ---------- Option B (beta setting ON only): prefetch and the overlap notice ---------- */

	var PREFETCH_DEBOUNCE_MS = 600;
	var PREFETCH_CAP = 3;          // per wizard session
	var prefetchCount = 0;
	var prefetchTimer = null;
	var prefetched = {};           // normalised keyword -> true

	function v3Enabled() { return String(cfg.v3KeywordData) === '1'; }

	// Warms the server's keyword cache for the seed being typed, so the Title step finds it.
	// Skipped when the keyword came from the Keyword Generator: the server cached it then.
	function prefetch(keyword) {
		var key = normalize(keyword);
		if (!v3Enabled() || !key || !cfg.ajaxUrl) { return; }
		if ($('#iseo_keyword_source').val() === 'keyword_finder') { return; }
		if (prefetched[key] || prefetchCount >= PREFETCH_CAP) { return; }
		prefetched[key] = true;
		prefetchCount++;
		$.ajax({
			url: cfg.ajaxUrl, type: 'POST', dataType: 'json', timeout: 20000,
			data: { action: 'improveseo_keyword_prefetch', nonce: cfg.nonce, keyword: keyword }
		}).done(function (resp) {
			var d = (resp && resp.data) || {};
			if (window.console) { window.console.info('[ImproveSEO] keyword prefetch "' + keyword + '": ' + (d.status || 'unknown') + (d.reason ? ' (' + d.reason + ')' : '')); }
		}).fail(function () {
			if (window.console) { window.console.info('[ImproveSEO] keyword prefetch "' + keyword + '" failed; generation is not affected.'); }
		});
	}

	function schedulePrefetch(keyword) {
		if (!v3Enabled()) { return; }
		if (prefetchTimer) { clearTimeout(prefetchTimer); }
		prefetchTimer = setTimeout(function () { prefetch(keyword); }, PREFETCH_DEBOUNCE_MS);
	}

	// "A recent post may already cover this: <title>. <reason>" with Keep keyword / Change keyword.
	// Called by iseoHandlePlannerResponse() once the plugin makes Planner requests.
	function showOverlapNotice(opts) {
		opts = opts || {};
		if (!window.iseoModal || typeof window.iseoModal.confirm !== 'function') { return false; }
		var msg = 'A recent post may already cover this: ' + (opts.title || '') + (opts.reason ? '. ' + opts.reason : '');
		// iseoModal.confirm({title, message, primary:{label}, secondary:{label}}) resolves true for primary.
		window.iseoModal.confirm({
			title: 'Similar post found',
			message: msg,
			primary: { label: 'Keep keyword' },
			secondary: { label: 'Change keyword' }
		}).then(function (keep) {
			if (keep) {
				if (typeof opts.onKeep === 'function') { opts.onKeep(); }
			} else {
				$('#seed_keyword').trigger('focus');
				if (typeof opts.onChange === 'function') { opts.onChange(); }
			}
		});
		return true;
	}

	// The Planner response hook: data.overlap is { post_id, reason } or null per the contract.
	function handlePlannerResponse(resp, handlers) {
		var data = (resp && resp.data) || resp || {};
		if (!data.overlap || !data.overlap.post_id) { return false; }
		var title = data.overlap.title || ('post #' + data.overlap.post_id);
		return showOverlapNotice({ title: title, reason: data.overlap.reason || '', onKeep: handlers && handlers.onKeep, onChange: handlers && handlers.onChange });
	}

	$(function () {
		var $seed = $('#seed_keyword');
		if (!$seed.length) { return; }

		if (v3Enabled()) {
			$seed.on('input', function () { schedulePrefetch($(this).val() || ''); });
		}

		var source = $('#iseo_keyword_source').val() || '';
		var listId = parseInt($('#iseo_keyword_list').val(), 10) || 0;

		// Arrived from the Keyword Generator: show the line straight away.
		if (source === 'keyword_finder' && $seed.val()) {
			fetchSummary($seed.val(), listId, true);
		}

		// A keyword the user typed or edited: look it up when they leave the field. The field
		// value drives the stored data too, so editing it clears what was shown for the old one.
		$seed.on('change', function () {
			var val = $(this).val() || '';
			if (current && normalize(val) !== normalize(current.keyword)) {
				showLine('', null);
				$('#iseo_keyword_source').val('typed');
				$('#iseo_keyword_list').val('0');
				listId = 0;
			}
			if (val.trim()) { fetchSummary(val, listId, false); }
		});
	});

	window.iseoKeywordWizard = { fetchSummary: fetchSummary, bulkSummary: bulkSummary, showLine: showLine, prefetch: prefetch };
	window.iseoShowOverlapNotice = showOverlapNotice;
	window.iseoHandlePlannerResponse = handlePlannerResponse;
})(window, document, jQuery);
