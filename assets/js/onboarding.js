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

  // localStorage key the redirected popup sets once the connect completes, so an
  // orphaned opener tab (Safari/Google path) can move forward instead of spinning.
  var CONNECT_DONE_KEY = 'improveseo_connect_done';

  // ── State ───────────────────────────────────────────────────────────────────
  var connectPopup     = null;
  var pollTimer        = null;
  var messageListener  = null;
  var storageListener  = null;
  var lastTab          = 'signup';
  var lastConnectUrl   = '';
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

  function removeStorageListener() {
    if (storageListener) {
      window.removeEventListener('storage', storageListener);
      storageListener = null;
    }
  }

  function resetToScreen1() {
    stopPolling();
    removeMessageListener();
    removeStorageListener();
    connectPopup = null;
    hidePopupClosedWarning();
    showScreen(1);
  }

  // This onboarding page's own URL, used as the popup's redirect-fallback target.
  // Strips the hash and any stale connect token so we never loop or leak it.
  function currentReturnUrl() {
    try {
      var u = new URL(window.location.href);
      u.hash = '';
      u.searchParams.delete('improveseo_connect_token');
      return u.toString();
    } catch (e) {
      return window.location.href.split('#')[0];
    }
  }

  // ── Popup flow ───────────────────────────────────────────────────────────────

  function openConnectPopup(tab) {
    lastTab = tab || 'signup';
    hidePopupBlockedWarning();

    // Pass this window's origin so the popup knows where to postMessage back.
    // CMS_ORIGIN is the popup's own origin — incorrect as the target.
    var wpOrigin = window.location.protocol + '//' + window.location.host;

    // Safari/WebKit drops window.opener after the popup's Google OAuth redirect,
    // which breaks postMessage. Give the popup a return URL so it can hand the
    // single-use connect token back via a top-level redirect as a fallback.
    var returnUrl = currentReturnUrl();

    var connectUrl = cfg.cmsConnectUrl
      + '?site_domain=' + encodeURIComponent(cfg.siteDomain)
      + '&origin='      + encodeURIComponent(wpOrigin)
      + '&return_url='  + encodeURIComponent(returnUrl)
      + (tab === 'login' ? '&tab=0' : '&tab=1');

    // Store so the "connect in this window" fallback can reuse it.
    lastConnectUrl = connectUrl;

    // Open as a new tab (no window-features string). Popup windows are treated as
    // a restricted context by Safari/Chrome: OAuth flows inside them are blocked
    // when the CMS page tries to open a second popup (e.g. Google login). A tab
    // is a full navigation context so nested popups are allowed.
    connectPopup = window.open(connectUrl, 'improveseo_connect');

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
      removeStorageListener();

      exchangeToken(event.data.connect_token, event.data.trial_status);
    };
    window.addEventListener('message', messageListener);

    // Same-origin cross-tab fallback: when the popup completes via the redirect
    // path (Safari/Google), this tab never gets the postMessage. The redirected
    // popup sets CONNECT_DONE_KEY in localStorage instead; reload to move forward
    // (credentials are saved server-side, so we land on the next onboarding step).
    removeStorageListener();
    storageListener = function (event) {
      if (event.key === CONNECT_DONE_KEY && event.newValue) {
        stopPolling();
        removeMessageListener();
        removeStorageListener();
        window.location.reload();
      }
    };
    window.addEventListener('storage', storageListener);

    // Poll every 500ms to detect if the popup was closed early
    pollTimer = setInterval(function () {
      if (connectPopup && connectPopup.closed) {
        stopPolling();
        removeMessageListener();
        removeStorageListener();
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
          // Signal any orphaned opener tab (Safari/Google redirect path) that the
          // connect finished. Harmless on the normal path — storage events don't
          // fire in the same tab that sets the value.
          try { localStorage.setItem(CONNECT_DONE_KEY, String(Date.now())); } catch (e) { /* ignore */ }
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
        var msg;
        if (status === 'timeout') {
          msg = 'Request timed out. Please check your connection and try again.';
        } else if (xhr.status === 403 && serverMsg && serverMsg.indexOf('Security') !== -1) {
          msg = 'Your session has expired. Please refresh the page and try again.';
        } else {
          msg = serverMsg || 'Connection error. Please try again.';
        }
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
      var days = (data.days_remaining !== null && data.days_remaining !== undefined)
        ? data.days_remaining : 14;
      if (days === 0) {
        $('#iseo-s3-new .iseo-subline').html(
          'Your <strong>14-day free trial</strong> has ended. ' +
          '<a href="' + cfg.dashboardUrl + '">Upgrade your plan</a> to continue using all features.'
        );
      } else {
        $('#iseo-days-remaining').text(days);
      }
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

    // ── Redirect-fallback: popup handed the token back via the URL ───────────
    // When window.opener is lost (Safari after the Google OAuth redirect), the
    // CMS popup redirects this page to itself with ?improveseo_connect_token=…
    // instead of using postMessage. Detect it, scrub the URL, and exchange.
    try {
      var _params = new URLSearchParams(window.location.search);
      var _redirectToken = _params.get('improveseo_connect_token');
      if (_redirectToken) {
        _params.delete('improveseo_connect_token');
        var _qs    = _params.toString();
        var _clean = window.location.pathname + (_qs ? '?' + _qs : '');
        window.history.replaceState({}, document.title, _clean);
        showScreen(2);
        exchangeToken(_redirectToken);
      }
    } catch (e) { /* URLSearchParams unsupported — ignore, postMessage path still works */ }

    // ── Edge-case detection on page load ────────────────────────────────────
    if (cfg.partialConnect) {
      // One credential exists but not the other — something went wrong mid-connect
      $('#iseo-partial-connect-msg').show();
    }

    if (cfg.isConnected) {
      if (cfg.onboardingDone) {
        // Fully set up — user manually navigated back; surface a notice and let
        // them proceed to dashboard or reconnect if they need to switch accounts
        $('#iseo-already-connected-msg').show();
        $('#iseo-btn-create').text('Reconnect Account');
        $('#iseo-btn-login').text('Sign In with a Different Account');
      } else {
        // Credentials are saved but the business form was never submitted.
        // Skip the popup flow and go straight to the business setup screen.
        showScreen(4);
        return;
      }
    }

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
      removeStorageListener();
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

    // Screen 2: "Connect in this window" fallback — redirects the current page to
    // the CMS connect URL. On return, the CMS adds ?improveseo_connect_token= to
    // return_url and the token-handling code on page load picks it up automatically.
    // This path avoids all cross-tab postMessage / localStorage communication,
    // fixing the issue on browsers where window.opener is silently null.
    $('#iseo-connect-in-window').on('click', function (e) {
      e.preventDefault();
      if (lastConnectUrl) {
        window.location.href = lastConnectUrl;
      }
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
