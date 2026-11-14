/**
 * Business details: the niche picker (Content v3, U6 interim, approved 14 Nov).
 *
 * Search-as-you-type over the server's niche list (iseoNichePicker.niches, from
 * GET /api/v1/content-v3/niches): matches the niche label, each part of it ("Roofing/Exterior"
 * → roofing, exterior) and its synonyms; small typos are tolerated (one edit for 4–6 letters,
 * two from 7). Choosing a niche fills the hidden key, shows the "closest kind of business" select
 * for the "Other …" rows, and shows the niche's suggested proof level as a line of text. The proof
 * level itself is never changed here (G1: the user confirms).
 */
(function () {
  'use strict';
  var cfg = window.iseoNichePicker || {};
  var niches = Array.isArray(cfg.niches) ? cfg.niches : [];
  var t = cfg.i18n || {};

  function norm(s) {
    return String(s || '')
      .normalize('NFD')
      .replace(/[̀-ͯ]/g, '')
      .toLowerCase()
      .replace(/[^a-z0-9]+/g, ' ')
      .trim();
  }

  function editDistance(a, b, max) {
    if (Math.abs(a.length - b.length) > max) return max + 1;
    var prev = [];
    for (var j = 0; j <= b.length; j++) prev[j] = j;
    for (var i = 1; i <= a.length; i++) {
      var cur = [i];
      var rowMin = i;
      for (var k = 1; k <= b.length; k++) {
        cur[k] = Math.min(prev[k] + 1, cur[k - 1] + 1, prev[k - 1] + (a[i - 1] === b[k - 1] ? 0 : 1));
        if (cur[k] < rowMin) rowMin = cur[k];
      }
      if (rowMin > max) return max + 1;
      prev = cur;
    }
    return prev[b.length];
  }

  /** Every searchable phrase of a niche: the label, its parts, its synonyms. */
  function phrases(n) {
    var out = [norm(n.label)];
    String(n.label || '').split(/[\/(),]/).forEach(function (p) {
      var x = norm(p);
      if (x) out.push(x);
    });
    (n.synonyms || []).forEach(function (s) {
      out.push(norm(s));
    });
    return out;
  }

  /** Lower is better; -1 = no match. */
  function score(n, q) {
    var best = -1;
    var tol = q.length >= 7 ? 2 : q.length >= 4 ? 1 : 0;
    phrases(n).forEach(function (p) {
      var s = -1;
      if (p === q) s = 0;
      else if (p.indexOf(q) === 0) s = 1;
      else if ((' ' + p).indexOf(' ' + q) >= 0) s = 2;
      else if (tol > 0) {
        var words = p.split(' ');
        for (var w = 0; w < words.length; w++) {
          var d = editDistance(q, words[w].slice(0, Math.max(q.length, words[w].length)), tol);
          var dp = editDistance(q, words[w].slice(0, q.length), tol);
          if (Math.min(d, dp) <= tol) {
            s = 3 + Math.min(d, dp);
            break;
          }
        }
      }
      if (s >= 0 && (best < 0 || s < best)) best = s;
    });
    return best;
  }

  function search(query) {
    var q = norm(query);
    if (!q) return [];
    return niches
      .map(function (n) { return { n: n, s: score(n, q) }; })
      .filter(function (x) { return x.s >= 0; })
      .sort(function (a, b) { return a.s - b.s || a.n.label.localeCompare(b.n.label); })
      .slice(0, 8)
      .map(function (x) { return x.n; });
  }

  function byKey(key) {
    for (var i = 0; i < niches.length; i++) if (niches[i].key === key) return niches[i];
    return null;
  }

  document.addEventListener('DOMContentLoaded', function () {
    var input = document.getElementById('iseo_business_niche_search');
    var hidden = document.getElementById('iseo_business_niche');
    var list = document.getElementById('iseo_niche_suggestions');
    var profileGroup = document.getElementById('iseo_profile_group');
    var suggestion = document.getElementById('iseo_proof_suggestion');
    var help = document.getElementById('iseo_niche_help');
    if (!input || !hidden || !list) return;

    if (!niches.length) {
      // B-5: with Content v3 off the list is not loaded; say why instead of a connection error.
      var why = cfg.state === 'off_site' ? t.offSite : cfg.state === 'off_server' ? t.offServer : t.unavailable;
      if (help) help.textContent = why || '';
      input.disabled = true;
      return;
    }

    function showSuggestion(n) {
      if (!suggestion) return;
      if (!n) {
        suggestion.textContent = '';
        return;
      }
      var tpl = n.proof_level === 'regulated' ? t.suggestRegulated : n.proof_open ? t.suggestOpen : t.suggestStandard;
      suggestion.textContent = String(tpl || '').replace('%s', n.label);
    }

    function choose(n) {
      hidden.value = n.key;
      input.value = n.label;
      list.style.display = 'none';
      if (profileGroup) profileGroup.style.display = n.choose_profile ? '' : 'none';
      showSuggestion(n);
    }

    function render(items) {
      list.innerHTML = '';
      if (!items.length) {
        var li = document.createElement('li');
        li.className = 'iseo-niche-empty';
        li.textContent = t.noMatch || '';
        list.appendChild(li);
      }
      items.forEach(function (n) {
        var li = document.createElement('li');
        li.setAttribute('role', 'option');
        li.tabIndex = -1;
        li.textContent = n.label;
        var small = document.createElement('small');
        small.textContent = ' · ' + n.profile_name;
        small.style.color = '#6b7280';
        li.appendChild(small);
        li.addEventListener('mousedown', function (e) {
          e.preventDefault();
          choose(n);
        });
        list.appendChild(li);
      });
      list.style.display = '';
    }

    input.addEventListener('input', function () {
      hidden.value = ''; // typed text is not a niche until one is chosen
      render(search(input.value));
    });
    input.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        var first = search(input.value)[0];
        if (first) choose(first);
      } else if (e.key === 'Escape') {
        list.style.display = 'none';
      }
    });
    input.addEventListener('blur', function () {
      setTimeout(function () { list.style.display = 'none'; }, 150);
      // Leaving the box with a typed value that was never chosen: keep the previous choice.
      if (!hidden.value && input.dataset.savedKey) {
        var prev = byKey(input.dataset.savedKey);
        if (prev) choose(prev);
      }
    });
    input.dataset.savedKey = hidden.value;
    showSuggestion(byKey(hidden.value));
  });

  // For tests and the console.
  window.iseoNicheSearch = search;
})();
