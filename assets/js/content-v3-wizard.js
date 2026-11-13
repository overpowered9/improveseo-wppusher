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

  /** The wizard's niche key (the server maps v2 keys); Business details' niche wins server-side. */
  function nicheLabel() {
    return $('#niche_select').val() || '';
  }

  function nicheOther() {
    return ($('#niche_other').val() || '').trim();
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
      niche_other: nicheOther(),
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

  /* ----------------------------------------------------------------------------------
   * Approve title → step 2 (W3, W7, W8). Runs when the user leaves step 1, after the
   * existing title approval and credit check. The plan is cached per title + keyword + town
   * so going back and forward does not plan twice.
   * ---------------------------------------------------------------------------------- */
  var SIZE_VALUES = { small: '600 to 1200 words', medium: '1200 to 2400 words', large: '2400 to 3600 words' };
  var SIZE_ORDER = ['small', 'medium', 'large'];
  var lastApproveSig = '';
  window.iseoV3Plan = null;

  function approvedTitle() {
    if ($('#seed_select').val() === 'seed_option1') {
      return seedKeyword(); // "Exact Keyword as Title"
    }
    return ($('#maintitlearea').val() || '').trim();
  }

  function sizeKeyFromValue(v) {
    for (var k in SIZE_VALUES) {
      if (Object.prototype.hasOwnProperty.call(SIZE_VALUES, k) && SIZE_VALUES[k] === v) {
        return k;
      }
    }
    return '';
  }

  /** W8: sizes below the minimum cannot be chosen, in step 2 and in step 1's priced select. */
  function applyMinimumSize(minimum) {
    var minIdx = Math.max(0, SIZE_ORDER.indexOf(minimum));
    $('#iseo_v3_post_size option').each(function () {
      $(this).prop('disabled', SIZE_ORDER.indexOf(this.value) < minIdx);
    });
    $('#post_size option').each(function () {
      var key = sizeKeyFromValue(this.value);
      $(this).prop('disabled', key !== '' && SIZE_ORDER.indexOf(key) < minIdx);
    });
  }

  function setSize(key) {
    if (!SIZE_VALUES[key]) {
      return;
    }
    $('#iseo_v3_post_size').val(key);
    // #post_size is what the credit estimate and the generation read; keep it in step.
    $('#post_size').val(SIZE_VALUES[key]).trigger('change');
  }

  function renderPlan(d) {
    var plan = d.plan || {};
    $('#iseo_v3_article_type').val(plan.article_type || 'topic');
    applyMinimumSize(plan.minimum_size || 'small');
    setSize(plan.post_size || plan.minimum_size || 'small');
    $('#iseo_v3_size_reason').text(plan.size_reason || '');

    var $q = $('#iseo_v3_owner_questions').empty();
    (plan.owner_questions || []).forEach(function (question, i) {
      var id = 'iseo_v3_owner_answer_' + i;
      var $field = $('<div class="seo-form-field iseo-v3-owner-q"></div>');
      $field.append($('<label></label>').attr('for', id).text(question));
      $field.append($('<textarea class="form-control" rows="2" maxlength="600"></textarea>').attr('id', id).attr('data-question', question).attr('placeholder', t.ownerPlaceholder || ''));
      $q.append($field);
    });
    if ((plan.owner_questions || []).length) {
      $q.prepend($('<p style="margin:0 0 6px;font-weight:600;"></p>').text(t.ownerHeading || ''));
    }

    var r = d.regulated || {};
    if (r.suggested) {
      var text;
      if (r.by === 'business') {
        text = t.regulatedBusiness;
      } else if (r.phrase) {
        text = sprintf2(t.regulatedTopic, r.phrase, '');
      } else {
        text = sprintf2(t.regulatedPlanner, r.reason || t.regulatedGeneric, '');
      }
      $('#iseo_v3_regulated_text').text(text);
      $('input[name="iseo_v3_regulated_choice"][value="keep"]').prop('checked', true);
      $('#iseo_v3_regulated').show();
    } else {
      $('#iseo_v3_regulated').hide();
    }

    var notes = [];
    if (d.fallback) {
      notes.push(t.planFallback);
    }
    if (d.research && d.research.status && d.research.status !== 'ok') {
      notes.push(t.researchSkipped);
    }
    $('#iseo_v3_plan_note').text(notes.join(' ')).toggle(notes.length > 0);
    $('#iseo_v3_plan').show();
  }

  window.iseoV3BeforeAdvance = function () {
    return new Promise(function (resolve) {
      if (!window.iseoContentV3 || window.iseoContentV3.active !== '1') {
        resolve(true);
        return;
      }
      var title = approvedTitle();
      var kw = seedKeyword();
      var c = townChoice();
      if (!c.ok) {
        var msg = $('#iseo_v3_target_town').val() === '__new' ? t.newTownEmpty : t.chooseTown;
        $('#error_iseo_v3_target_town').text(msg);
        notify('warning', msg, t.townHeading);
        resolve(false);
        return;
      }
      var sig = [title, kw, c.town, c.notAbout ? '1' : '0'].join('|').toLowerCase();
      if (sig === lastApproveSig && window.iseoV3Plan) {
        resolve(true);
        return;
      }
      if (typeof window.ImproveSEOLoading !== 'undefined') {
        window.ImproveSEOLoading.show({ title: t.planning, message: t.planningMsg });
      }
      $.ajax({
        url: cfg.ajaxUrl,
        type: 'POST',
        timeout: 450000,
        data: {
          action: 'improveseo_v3_approve_title',
          nonce: cfg.nonce,
          session_key: sessionKey(),
          title: title,
          seed_keyword: kw,
          target_town: c.town,
          not_about_location: c.notAbout ? '1' : '0',
          language: $('#language').val() || '',
          niche: nicheLabel(),
          niche_other: nicheOther()
        }
      })
        .done(function (res) {
          hideLoading();
          if (res && res.success && res.data && res.data.fallback === 'v2') {
            window.iseoContentV3.active = '0';
            resolve(true);
            return;
          }
          if (!res || !res.success || !res.data) {
            notify('error', (res && res.data && res.data.message) || t.planFailed, t.planFailedHeading);
            resolve(false);
            return;
          }
          window.iseoV3Plan = res.data;
          lastApproveSig = sig;
          renderPlan(res.data);
          if (res.data.title_notes && res.data.title_notes.length) {
            notify('info', res.data.title_notes.join(' '), t.titleNotesHeading);
          }
          resolve(true);
        })
        .fail(function (xhr) {
          hideLoading();
          var m = xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message ? xhr.responseJSON.data.message : t.planFailed;
          notify('error', m, t.planFailedHeading);
          resolve(false);
        });
    });
  };

  /** What step 2 decided, for the article request (phase 8). */
  window.iseoV3StepTwoChoices = function () {
    var answers = [];
    $('#iseo_v3_owner_questions textarea').each(function () {
      var a = ($(this).val() || '').trim();
      if (a) {
        answers.push({ question: $(this).attr('data-question') || '', answer: a });
      }
    });
    return {
      article_type: $('#iseo_v3_article_type').val() || '',
      post_size: $('#iseo_v3_post_size').val() || '',
      owner_answers: answers,
      regulated: $('#iseo_v3_regulated').is(':visible') ? ($('input[name="iseo_v3_regulated_choice"]:checked').val() === 'keep') : null
    };
  };

  /** G5: the Witty tone is not offered for a regulated niche. */
  function hideWittyForRegulated() {
    if (cfg.regulatedNiche !== '1') {
      return;
    }
    // Every tone select: the single and bulk popups both use id "cotnt_type".
    $('select[name="content_type"]').each(function () {
      var $tone = $(this);
      if ($tone.val() === 'witty') {
        $tone.val('professional').trigger('change');
      }
      $tone.find('option[value="witty"]').remove();
    });
  }

  $(function () {
    hideWittyForRegulated();
    $(document).on('change', '#iseo_v3_post_size', function () {
      setSize($(this).val());
    });
    $(document).on('change', '#post_size', function () {
      var key = sizeKeyFromValue($(this).val());
      if (key && $('#iseo_v3_post_size').val() !== key) {
        $('#iseo_v3_post_size').val(key);
      }
    });
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
