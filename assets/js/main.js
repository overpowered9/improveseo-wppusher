(function($) {
    var get_al = '';
    $(document).on("click", '.category_improveseo input[type="checkbox"]', function() {
        var vals = $('input[type="checkbox"]:checked').map(function() { return this.value; }).get();
        $('.Posting__post-button').attr('href', main_ajax_vars.site_url + '/wp-admin/admin.php?page=improveseo_posting&action=create_post&cat_pre='+vals.join(","));
        $('.Posting__page-button').attr('href', main_ajax_vars.site_url + '/wp-admin/admin.php?page=improveseo_posting&action=create_page&cat_pre='+vals.join(","));
    });
     $(document).on("click", '.improveseo_wrapper .nav-tabs .nav-link', function(e) {
        e.preventDefault();
        $(".improveseo_wrapper .nav-tabs .nav-link").removeClass('active');
        $(".improveseo_wrapper .tab-content .tab-pane").removeClass("show active");
        var contentId = $(this).attr("href");
        $(this).addClass('active');
        $('.improveseo_wrapper '+contentId).addClass('show active');
    });


    $('#importProject').click(function(){
        $('.project-import-box').show('slow');
    });

})(jQuery);

/* Info (ⓘ) markers, .iseo-info-tip. CSS opens the bubble on hover and keyboard focus;
   this adds click/tap, which touch screens need (they have no hover). A click pins the
   bubble open (.is-open) and a second click, Escape or a click elsewhere closes it.
   .is-dismissed hides a bubble that :hover or :focus would otherwise keep showing
   right after it was closed.

   Most markers sit inside a <label for="...">, so a plain click would also move focus
   into that field and close the bubble straight away; preventDefault stops that.

   The click listener runs in the capture phase because some markers (the postbox
   headings on the Create a Post screen) stop click propagation inline so that
   clicking them doesn't collapse the postbox. */
(function () {
    var TIP = '.iseo-info-tip';

    function tipOf(node) {
        return node && node.closest ? node.closest(TIP) : null;
    }

    function setOpen(tip, open) {
        tip.classList.toggle('is-open', open);
        tip.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    function closeOthers(keep) {
        document.querySelectorAll(TIP + '.is-open').forEach(function (tip) {
            if (tip !== keep) { setOpen(tip, false); }
        });
    }

    function isShowing(tip) {
        if (tip.classList.contains('is-dismissed')) { return false; }
        return tip.classList.contains('is-open') || tip.contains(document.activeElement);
    }

    function open(tip) {
        closeOthers(tip);
        tip.classList.remove('is-dismissed');
        setOpen(tip, true);
    }

    // keepFocus: keyboard users stay on the marker. Pointer users don't need the
    // focus, and leaving it there would keep the bubble pinned by :focus.
    function close(tip, keepFocus) {
        setOpen(tip, false);
        if (!keepFocus && tip.contains(document.activeElement)) {
            document.activeElement.blur();
        }
        // Added after blur(): the focusout handler below clears it.
        tip.classList.add('is-dismissed');
    }

    document.addEventListener('click', function (e) {
        var tip = tipOf(e.target);
        if (!tip) {
            closeOthers(null);
            return;
        }
        // Links inside a bubble (Learn More, Generate Keyword List, refresh) keep
        // their own behaviour.
        if (e.target.closest('a')) { return; }
        e.preventDefault();
        // A click on the bubble's own text keeps it open.
        if (e.target.closest('.iseo-info-tip-bubble')) { return; }
        if (tip.classList.contains('is-open')) {
            close(tip, false);
        } else {
            open(tip);
        }
    }, true);

    document.addEventListener('keydown', function (e) {
        var tip = tipOf(e.target);
        if (!tip) { return; }

        if (e.key === 'Escape' || e.key === 'Esc') {
            // Only swallow the key when there's a bubble to close; otherwise Escape
            // still reaches the wizard modal behind it.
            if (!isShowing(tip)) { return; }
            e.preventDefault();
            e.stopPropagation();
            close(tip, true);
            return;
        }

        // Enter/Space on the marker itself (role="button"), not on a link inside it.
        if (e.target === tip && (e.key === 'Enter' || e.key === ' ' || e.key === 'Spacebar')) {
            e.preventDefault();
            if (isShowing(tip)) {
                close(tip, true);
            } else {
                open(tip);
            }
        }
    }, true);

    // Focus leaving the marker and its bubble resets it, so the next hover or focus
    // starts fresh.
    document.addEventListener('focusout', function (e) {
        var tip = tipOf(e.target);
        if (tip && !tip.contains(e.relatedTarget)) {
            setOpen(tip, false);
            tip.classList.remove('is-dismissed');
        }
    });

    // A pointer user who clicked a bubble closed has no focus to lose. Clear the
    // dismissal once the pointer leaves, so the next hover shows the bubble again.
    document.addEventListener('mouseout', function (e) {
        var tip = tipOf(e.target);
        if (tip && !tip.contains(e.relatedTarget) && !tip.contains(document.activeElement)) {
            tip.classList.remove('is-dismissed');
        }
    });
})();