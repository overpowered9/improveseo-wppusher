/**
 * ImproveSEO Onboarding Wizard — JavaScript
 *
 * Drives the 5-screen wizard. All communication with the admin server
 * goes through WordPress AJAX (no credentials are ever in the browser URL).
 *
 * State machine:
 *   Screen 1 → (popup opens) → Screen 2 → (postMessage received) → Screen 3
 *   Screen 3 → Screen 4 → Screen 5
 */

/* global jQuery, improveseoOnboarding */
(function ($) {
  'use strict';

  // ── Config from wp_localize_script ──────────────────────────────────────────
  var cfg = window.improveseoOnboarding || {};

  // Expected postMessage origin
  var CMS_ORIGIN = 'https://account.improveseoplugin.com';

  // ── State ───────────────────────────────────────────────────────────────────
  var connectPopup     = null;
  var pollTimer        = null;
  var messageListener  = null;
  var lastTab          = 'signup';
  var businessData     = { service: '', city: '' };

  // ── Helpers ──────────────────────────────────────────────────────────────────

  function showScreen(id) {
    $('.iseo-screen').hide();
    $('#iseo-screen-' + id).fadeIn(200);
  }

  function showPopupBlockedWarning() {
    $('#iseo-popup-blocked-msg').show();
  }

  function hidePopupBlockedWarning() {
    $('#iseo-popup-blocked-msg').hide();
  }

  function showPopupClosedWarning() {
    $('#iseo-popup-closed-msg').show();
    // Change spinner to muted to signal we're waiting for user action
    $('.iseo-spinner').css('border-top-color', '#d1d5db');
  }

  function hidePopupClosedWarning() {
    $('#iseo-popup-closed-msg').hide();
    $('.iseo-spinner').css('border-top-color', '');
  }

  function stopPolling() {
    if (pollTimer) {
      clearInterval(pollTimer);
      pollTimer = null;
    }
  }

  function removeMessageListener() {
    if (messageListener) {
      window.removeEventListener('message', messageListener);
      messageListener = null;
    }
  }

  function resetToScreen1() {
    stopPolling();
    removeMessageListener();
    connectPopup = null;
    hidePopupClosedWarning();
    showScreen(1);
  }

  // ── Popup flow ───────────────────────────────────────────────────────────────

  function openConnectPopup(tab) {
    lastTab = tab || 'signup';
    hidePopupBlockedWarning();

    var connectUrl = cfg.cmsConnectUrl
      + '?site_domain=' + encodeURIComponent(cfg.siteDomain)
      + '&origin='      + encodeURIComponent(CMS_ORIGIN)
      + (tab === 'login' ? '&tab=0' : '&tab=1');

    var popupWidth  = 520;
    var popupHeight = 680;
    var left = Math.max(0, Math.round((screen.width  - popupWidth)  / 2));
    var top  = Math.max(0, Math.round((screen.height - popupHeight) / 2));

    connectPopup = window.open(
      connectUrl,
      'improveseo_connect',
      'width=' + popupWidth  + ',height=' + popupHeight +
      ',left=' + left + ',top=' + top +
      ',scrollbars=yes,resizable=yes'
    );

    // Edge case: popup was blocked by the browser
    if (!connectPopup || connectPopup.closed) {
      connectPopup = null;
      showPopupBlockedWarning();
      return;
    }

    showScreen(2);

    // Listen for the connect_token postMessage from the CMS popup
    messageListener = function (event) {
      // Security: only accept messages from the known CMS origin
      if (event.origin !== CMS_ORIGIN) return;
      if (!event.data || !event.data.connect_token) return;

      stopPolling();
      removeMessageListener();

      exchangeToken(event.data.connect_token, event.data.trial_status);
    };
    window.addEventListener('message', messageListener);

    // Poll every 500ms to detect if the popup was closed early
    pollTimer = setInterval(function () {
      if (connectPopup && connectPopup.closed) {
        stopPolling();
        removeMessageListener();
        showPopupClosedWarning();
      }
    }, 500);
  }

  // ── Token exchange ───────────────────────────────────────────────────────────

  function exchangeToken(token, trialStatusFromMessage) {
    $.ajax({
      url:    cfg.ajaxUrl,
      method: 'POST',
      data: {
        action:        'improveseo_onboarding_exchange_token',
        nonce:         cfg.nonce,
        connect_token: token
      },
      timeout: 25000,
      success: function (response) {
        if (response.success) {
          populateScreen3(response.data, trialStatusFromMessage);
          showScreen(3);
        } else {
          var msg = (response.data && response.data.message)
            ? response.data.message
            : 'Connection failed. Please try again.';
          showErrorNotification(msg);
          showScreen(1);
        }
      },
      error: function (xhr, status) {
        var serverMsg = xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message;
        var msg = (status === 'timeout')
          ? 'Request timed out. Please check your connection and try again.'
          : serverMsg || 'Connection error. Please try again.';
        showErrorNotification(msg);
        showScreen(1);
      }
    });
  }

  // ── Screen 3 population ──────────────────────────────────────────────────────

  function populateScreen3(data, trialStatus) {
    // Prefer trial_status from the exchange response; fall back to the postMessage value
    var status = data.trial_status || trialStatus || 'new';

    // Show the correct message variant
    $('#iseo-s3-new, #iseo-s3-reconnect, #iseo-s3-transferred').hide();
    if (status === 'new') {
      $('#iseo-s3-new').show();
      var days = data.days_remaining !== null && data.days_remaining !== undefined
        ? data.days_remaining : 14;
      $('#iseo-days-remaining').text(days);
    } else if (status === 'reconnect') {
      $('#iseo-s3-reconnect').show();
    } else {
      // 'transferred' or unknown
      $('#iseo-s3-transferred').show();
    }

    // Show welcome name if provided
    if (data.user_name) {
      $('#iseo-user-name').text(data.user_name);
      $('#iseo-welcome-name').show();
    }
  }

  // ── Business form save ───────────────────────────────────────────────────────

  function saveBusiness(formData, onSuccess, onError) {
    $.ajax({
      url:    cfg.ajaxUrl,
      method: 'POST',
      data: $.extend({
        action: 'improveseo_onboarding_save_business',
        nonce:  cfg.nonce
      }, formData),
      timeout: 15000,
      success: function (response) {
        if (response.success) {
          onSuccess();
        } else {
          var msg = (response.data && response.data.message)
            ? response.data.message
            : 'Could not save. Please try again.';
          $('#iseo-business-error').text(msg).show();
          if (onError) onError();
        }
      },
      error: function () {
        $('#iseo-business-error').text('Network error. Please try again.').show();
        if (onError) onError();
      }
    });
  }

  // ── Notification helper (uses ImproveSEO overlay if available) ───────────────

  function showErrorNotification(message) {
    if (typeof showImproveSEONotification === 'function') {
      showImproveSEONotification('error', 'Connection Failed', message);
    } else {
      // Fallback: WP admin notice
      var $notice = $(
        '<div class="notice notice-error is-dismissible" style="margin:16px 0;">' +
        '<p><strong>Connection Failed:</strong> ' + $('<span>').text(message).html() + '</p>' +
        '</div>'
      );
      $('#improveseo-onboarding-wrap').prepend($notice);
    }
  }

  // ── Skip-all: mark complete and go to dashboard ───────────────────────────────

  function skipAll() {
    $.post(cfg.ajaxUrl, {
      action:        'improveseo_onboarding_save_business',
      nonce:         cfg.nonce,
      business_type: '',
      city:          '',
      service:       ''
    }).always(function () {
      // Redirect after the AJAX completes (success or fail) to ensure
      // onboarding_complete is saved before we navigate away.
      window.location.href = cfg.dashboardUrl;
    });
  }

  // ── Screen 5 — suggested keyword ─────────────────────────────────────────────

  function populateScreen5() {
    var service = businessData.service || '';
    var city    = businessData.city    || '';
    var kw      = service || 'seo services';
    if (city) kw += ' in ' + city;
    $('#iseo-suggested-kw').text(kw);

    // Point "Generate First Article" to the firstContentUrl
    $('#iseo-btn-first-content').attr('href', cfg.firstContentUrl);
  }

  // ── Event bindings ───────────────────────────────────────────────────────────

  $(function () {

    // Screen 1: Create Free Account (opens popup at Sign Up tab)
    $('#iseo-btn-create').on('click', function () {
      openConnectPopup('signup');
    });

    // Screen 1: Already have an account (opens popup at Sign In tab)
    $('#iseo-btn-login').on('click', function () {
      openConnectPopup('login');
    });

    // Screen 1: Skip all
    $('#iseo-skip-all').on('click', function (e) {
      e.preventDefault();
      skipAll();
    });

    // Screen 2: Cancel
    $('#iseo-cancel-connect').on('click', function (e) {
      e.preventDefault();
      stopPolling();
      removeMessageListener();
      if (connectPopup && !connectPopup.closed) {
        connectPopup.close();
      }
      connectPopup = null;
      hidePopupClosedWarning();
      showScreen(1);
    });

    // Screen 2: Retry (after popup was closed) — re-open with the same tab the user chose
    $(document).on('click', '#iseo-retry-btn', function () {
      hidePopupClosedWarning();
      openConnectPopup(lastTab);
    });

    // Screen 3: Continue Setup
    $('#iseo-btn-continue-setup').on('click', function () {
      showScreen(4);
    });

    // Screen 4: Business form submit
    $('#iseo-business-form').on('submit', function (e) {
      e.preventDefault();
      $('#iseo-business-error').hide();

      var formData = {
        business_type: $('#iseo-business-type').val() || '',
        city:          $.trim($('#iseo-city').val())   || '',
        service:       $.trim($('#iseo-service').val()) || ''
      };

      // Keep for Screen 5 keyword suggestion
      businessData.service = formData.service;
      businessData.city    = formData.city;

      var $btn = $(this).find('[type="submit"]');
      $btn.prop('disabled', true).text('Saving…');

      saveBusiness(formData, function () {
        $btn.prop('disabled', false).text('Save & Continue →');
        populateScreen5();
        showScreen(5);
      }, function () {
        // error — re-enable submit button so user can retry
        $btn.prop('disabled', false).text('Save & Continue →');
      });
    });

    // Screen 4: Skip business setup
    $('#iseo-skip-business').on('click', function () {
      var $btn = $(this);
      if ($btn.prop('disabled')) return; // prevent double-click
      $btn.prop('disabled', true).text('Skipping…');
      $('#iseo-business-error').hide();

      saveBusiness(
        { business_type: '', city: '', service: '' },
        function () {
          // success
          $btn.prop('disabled', false).text('Skip');
          populateScreen5();
          showScreen(5);
        },
        function () {
          // error — re-enable so user can retry
          $btn.prop('disabled', false).text('Skip');
        }
      );
    });

  });

}(jQuery));
