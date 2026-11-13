/**
 * Content v3 — single post wizard, step 1 (W2, W5, W6, W12, W15).
 *
 * Loaded only while Content v3 is active for this site (modules/content-v3.php). It owns:
 *  - the Target town choice for a local business (#iseo_v3_target_town: a saved town, "A new
 *    town…", or "Not about a specific location");
 *  - Generate / Regenerate title: generateAITitle() (custom-plugin-script.js) hands over to
 *    window.iseoV3GenerateTitle(), which asks the server for the search data lookup and the v3
 *    Title. Only the title comes back; Regenerate sends the session's earlier titles server-side;
 *  - the town check line: one sentence when the keyword names a town other than the chosen one.
 *
 * When the server answers that v3 is off (fallback: 'v2'), the v2 title path runs instead, so a
 * rollback never leaves the wizard stuck.
 */
(function ($) {
  'use strict';

  var cfg = window.iseoContentV3 || {};
  if (cfg.active !== '1') {
    return;
  }
  var t = cfg.i18n || {};
  var generatedFor = ''; // keyword the current title was generated for (Regenerate detection)
  var checkTimer = null;

  function sessionKey() {
    return $('#iseo_target_key').val() || '';
  }

  function seedKeyword() {
    return ($('#seed_keyword').val() || '').trim();
  }

  /** {town, notAbout, ok} for the current choice; ok is false for a local business with no choice. */
  function townChoice() {
    var $sel = $('#iseo_v3_target_town');
    if (!$sel.length) {
      return { town: '', notAbout: false, ok: true }; // nationwide business: no town question
    }
    var v = $sel.val() || '';
    if (v === '__none') {
      return { town: '', notAbout: true, ok: true };
    }
    if (v === '__new') {
      var typed = ($('#iseo_v3_new_town').val() || '').trim();
      return { town: typed, notAbout: false, ok: typed !== '' };
    }
    return { town: v, notAbout: false, ok: v !== '' };
  }

  function nicheLabel() {
    var key = $('#niche_select').val() || '';
    if (key === 'other') {
      return ($('#niche_other').val() || '').trim();
    }
    return ($('#niche_select option:selected').text() || '').trim();
  }

  function sprintf2(tpl, a, b) {
    return String(tpl || '').replace('%1$s', a).replace('%2$s', b);
  }

  function showTownCheck(check) {
    var $line = $('#iseo_v3_town_check');
    if (!$line.length) {
      return;
    }
    if (check && check.keyword_town && check.target_town) {
      $line.text(sprintf2(t.townCheck, check.keyword_town, check.target_town)).show();
    } else {
      $line.text('').hide();
    }
  }

  function runTownCheck() {
    var c = townChoice();
    var kw = seedKeyword();
    if (!kw || !c.town || c.notAbout) {
      showTownCheck(null);
      return;
    }
    $.post(cfg.ajaxUrl, { action: 'improveseo_v3_town_check', nonce: cfg.nonce, seed_keyword: kw, target_town: c.town })
      .done(function (res) {
        showTownCheck(res && res.success && res.data ? res.data.town_check : null);
      })
      .fail(function () {
        showTownCheck(null);
      });
  }

  function scheduleTownCheck() {
    clearTimeout(checkTimer);
    // The old answer is about the old keyword or town: hide it until the new one is in.
    showTownCheck(null);
    checkTimer = setTimeout(runTownCheck, 600);
  }

  function notify(kind, message, heading) {
    if (typeof window.ImproveSEONotification !== 'undefined' && window.ImproveSEONotification[kind]) {
      window.ImproveSEONotification[kind](message, heading);
    } else {
      window.alert(message);
    }
  }

  function hideLoading() {
    $('#loader').hide();
    if (typeof window.ImproveSEOLoading !== 'undefined') {
      window.ImproveSEOLoading.hide();
    }
  }

  window.iseoV3GenerateTitle = function () {
    if (typeof window.iseoRequireConnection === 'function' && !window.iseoRequireConnection()) {
      return;
    }
    var seedtype = $('#seed_select').val();
    if (seedtype === 'seed_option1') {
      // Exact keyword as title: nothing to generate. The search data lookup runs on Approve title.
      $('#loader').hide();
      $('.hide_on_seed_option1').hide();
      $('#gettitle').hide();
      return;
    }
    var kw = seedKeyword();
    if (!kw) {
      notify('warning', t.keywordNeeded, t.keywordHeading);
      return;
    }
    $('#error_seed_keyword').html('');
    var c = townChoice();
    if (!c.ok) {
      var msg = $('#iseo_v3_target_town').val() === '__new' ? t.newTownEmpty : t.chooseTown;
      $('#error_iseo_v3_target_town').text(msg);
      notify('warning', msg, t.townHeading);
      return;
    }
    $('#error_iseo_v3_target_town').text('');

    $('#gettitle').css({ display: 'flex' });
    $('.hide_on_seed_option1').show();
    if (typeof window.ImproveSEOLoading !== 'undefined') {
      window.ImproveSEOLoading.show({ title: t.generating, message: t.generatingMsg });
    }

    var regenerate = generatedFor !== '' && generatedFor === kw.toLowerCase();
    $.post(cfg.ajaxUrl, {
      action: 'improveseo_v3_generate_title',
      nonce: cfg.nonce,
      session_key: sessionKey(),
      seed_keyword: kw,
      target_town: c.town,
      not_about_location: c.notAbout ? '1' : '0',
      language: $('#language').val() || '',
      niche: nicheLabel(),
      regenerate: regenerate ? '1' : '0'
    })
      .done(function (res) {
        hideLoading();
        if (res && res.success && res.data && res.data.fallback === 'v2') {
          window.iseoContentV3.active = '0';
          if (typeof window.generateAITitle === 'function') {
            window.generateAITitle({ forceV2: true });
          }
          return;
        }
        if (!res || !res.success || !res.data) {
          var m = res && res.data && res.data.message ? res.data.message : t.genericError;
          notify('error', m, t.failedTitle);
          return;
        }
        showTownCheck(res.data.town_check);
        if (res.data.failed || !res.data.title) {
          // T6: three failed attempts leave the title box empty for the user to type.
          $('#maintitlearea').val('').trigger('focus');
          $('#aigeneratedtitle').val('');
          notify('warning', res.data.message || t.genericError, t.failedTitle);
          return;
        }
        $('#maintitlearea').val(res.data.title);
        $('#aigeneratedtitle').val(res.data.title);
        $('#reload-btn-text').text('Regenerate');
        generatedFor = kw.toLowerCase();
      })
      .fail(function (xhr) {
        hideLoading();
        var m = xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message ? xhr.responseJSON.data.message : t.genericError;
        notify('error', m, t.failedTitle);
      });
  };

  $(function () {
    $(document).on('change', '#iseo_v3_target_town', function () {
      var isNew = $(this).val() === '__new';
      $('#iseo_v3_new_town').toggle(isNew);
      if (isNew) {
        $('#iseo_v3_new_town').trigger('focus');
      }
      $('#error_iseo_v3_target_town').text('');
      scheduleTownCheck();
    });
    $(document).on('input', '#iseo_v3_new_town', scheduleTownCheck);
    $(document).on('change blur', '#seed_keyword', function () {
      // A new keyword starts a new title series: the next Generate is not a Regenerate.
      if (generatedFor && generatedFor !== seedKeyword().toLowerCase()) {
        generatedFor = '';
      }
      scheduleTownCheck();
    });
    if (seedKeyword()) {
      scheduleTownCheck();
    }
  });
})(jQuery);
