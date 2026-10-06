/**
 * ImproveSEO Onboarding Guide — Bulk AI Posts Project
 *
 * The bulk counterpart of onboarding-guide.js: the same card and glow (it reuses
 * assets/css/onboarding-guide.css), walking the "Bulk Create AI Posts" wizard
 * (#exampleModal2 in views/GenerateAIpopup/GenerateAIpopuphtml.php) one field at a time.
 *
 * Loaded only on admin.php?page=improveseo_posting&action=create_post_bulk&from=onboarding
 * (see improveseo_enqueue_guide_assets() in includes/assets.php).
 *
 * It is deliberately its own file rather than more branches in onboarding-guide.js:
 * that guide is built around the single-post wizard's own ids (#nextStepButton,
 * #step_value, the cover-image generation waits), none of which exist here, and the
 * bulk wizard has a different panel list (6 panels, no generation inside the modal).
 *
 * How it follows the wizard: the bulk wizard keeps its panel in a closure-local
 * variable, so the guide reads which panel is on screen from the DOM instead —
 * the one .data_multi carrying .dataJSmulti_multi. A watcher compares that with the
 * step being shown and moves the card to the right panel in either direction, so
 * Next (which can be held back by validation or a credit check) and Previous never
 * leave the card describing a panel the user has left.
 *
 * Every element is scoped under #exampleModal2: the single-post wizard markup is
 * printed on the same page and reuses several of the same ids (#cotnt_type,
 * #language, …).
 */
(function ($) {
    'use strict';

    var MODAL = '#exampleModal2';
    var NEXT  = '#nextStepButton_multi';

    // Two places this guide runs: the wizard (create_post_bulk) and, after Submit, the
    // Bulk Projects list the wizard redirects to (page=improveseo_bulkprojects — the
    // redirect carries from=onboarding while this guide is active, see
    // custom-plugin-script.js). init() picks one; SCOPE is where step targets are looked up.
    var PAGE_MODE = !$(MODAL).length && $('.project_table_listing').length > 0;
    var SCOPE     = PAGE_MODE ? 'body' : MODAL;

    /* ── Steps ───────────────────────────────────────────────────
       panel   0-based wizard panel the step belongs to
       kind    'field'  — the card has its own Next button
               'next'   — the user presses the wizard's Next; the panel watcher moves on
               'submit' — the wizard's Submit, last step
       needs   (optional) step only counts when this element exists on the page */
    var WIZARD_STEPS = [
        /* Panel 0 — Keyword & Post Title */
        {
            panel: 0, kind: 'field', target: '#keyword_list_name', position: 'bottom',
            title: 'Choose a keyword list',
            message: 'A bulk project writes <strong>one post per keyword</strong>. Pick the keyword list to use. No list yet? Hover the <strong>ⓘ</strong> next to <strong>Keyword List</strong> for the <strong>Generate Keywords</strong> link, then <strong>click here to refresh</strong>.',
            requireValue: '#keyword_list_name',
            waitMessage: 'Select a keyword list to continue.'
        },
        {
            panel: 0, kind: 'field', target: '#keyword_list_container', position: 'top',
            title: 'Check the keywords',
            message: 'These are the keywords from your list — each line becomes one post. Remove or add any for this project; you need at least one.'
        },
        {
            panel: 0, kind: 'field', target: '#bulk_article_size', position: 'top',
            title: 'Post size',
            message: 'Choose how long every post in this project should be. The estimate underneath shows what the whole project will cost in credits.'
        },
        {
            panel: 0, kind: 'field', target: '#tone_of_voice', position: 'top',
            title: 'Tone of voice',
            message: 'Pick the writing style for all posts — e.g. <strong>Professional</strong> for service businesses, <strong>Informational</strong> for how-to guides.'
        },
        {
            panel: 0, kind: 'field', target: '#existing_select', position: 'top',
            title: 'Title type',
            message: '<strong>Smart Title</strong> writes an SEO title for each keyword, <strong>Question-Style</strong> turns it into a question, and <strong>Exact Keyword</strong> uses the keyword itself as the title.'
        },
        {
            panel: 0, kind: 'field', target: '#exampleFormControlTextarea1', position: 'top',
            title: 'Details to include',
            message: 'Add details about your business that every post should know — services, location, what makes you different. Or press <strong>AI Generate Context Based On Keyword List</strong> to draft them.'
        },
        {
            panel: 0, kind: 'next', target: NEXT, position: 'top',
            title: 'Keywords & titles done!',
            message: 'Click <strong>Next</strong> below. We’ll check you have enough credits for this project before moving on.'
        },

        /* Panel 1 — Content Settings */
        {
            panel: 1, kind: 'field', target: 'select[name="point_of_view"]', position: 'bottom',
            title: 'Point of view',
            message: 'Choose who the posts speak as: to the reader (“you”), as your business (“we”), or as you personally (“I”). <strong>Auto</strong> lets the AI decide.'
        },
        {
            panel: 1, kind: 'field', target: '#language', position: 'bottom',
            title: 'Language',
            message: 'The language every post in this project is written in.'
        },
        {
            panel: 1, kind: 'field', target: '#call_to_action_multi', position: 'top',
            title: 'Call to action',
            message: 'What should readers do after reading? e.g. <em>Call us for a free quote</em>. It is added to every post in this project.'
        },
        {
            panel: 1, kind: 'field', target: '#cta_url_multi', position: 'top',
            title: 'Call to action link (optional)',
            message: 'Where the call to action should send readers — your contact or booking page. Leave it empty for no link.'
        },
        {
            panel: 1, kind: 'next', target: NEXT, position: 'top',
            title: 'Content settings done!',
            message: 'Click <strong>Next</strong> below to choose the images.'
        },

        /* Panel 2 — Add Media */
        {
            panel: 2, kind: 'field', target: '#keyword-image-selection-container', position: 'top',
            title: 'Images for each post',
            message: 'Every keyword gets its own cover image. Leave <strong>Generate Image Using AI</strong> on to have one created from the post title, or pick <strong>Upload Image</strong> to use your own.'
        },
        {
            panel: 2, kind: 'next', target: NEXT, position: 'top',
            title: 'Images set!',
            message: 'Click <strong>Next</strong> below. We’ll check you have enough image credits first.'
        },

        /* Panel 3 — Select Category */
        {
            panel: 3, kind: 'field', target: '.bulk-category-box', position: 'top',
            title: 'Assign categories',
            message: 'Tick the categories all posts in this project belong to. Need a new one? Type it under <strong>Create New Category</strong> and press <strong>Add Category</strong>.'
        },
        {
            panel: 3, kind: 'next', target: NEXT, position: 'top',
            title: 'Categories set!',
            message: 'Click <strong>Next</strong> below.'
        },

        /* Panel 4 — Publish Settings (also notes the automatic meta title & description) */
        {
            panel: 4, kind: 'field', target: '.schedule_posts_parent', position: 'bottom',
            title: 'Publish or save as drafts?',
            message: 'Choose what happens when the posts are ready: publish them all straight away, save them as <strong>drafts</strong> to review first, or set a <strong>schedule</strong> (so many per day or week).',
            requireValue: 'input[name="schedule_posts"]:checked',
            waitMessage: 'Choose one of the options to continue.'
        },
        {
            panel: 4, kind: 'field', target: 'select[name="author_name"]', position: 'top',
            needs: 'select[name="author_name"]',
            title: 'Assign an author',
            message: 'The WordPress user these posts will be published under.'
        },
        {
            panel: 4, kind: 'next', target: NEXT, position: 'top',
            title: 'Publish settings done!',
            message: 'An SEO meta title and description are written automatically for every post — nothing to fill in. Click <strong>Next</strong> below for the last step.'
        },

        /* Panel 5 — Finalize */
        {
            panel: 5, kind: 'field', target: '#project_name', position: 'bottom',
            title: 'Name your project',
            message: 'We’ve filled in a name from your keyword list — it’s only for you, to find this project later. Edit it if you like.'
        },
        {
            panel: 5, kind: 'submit', target: NEXT, position: 'top',
            title: 'Create your bulk project! 🎉',
            message: 'Click <strong>Submit</strong> below. All posts are written in the background — you can leave this page, and we’ll email you when they’re done.'
        }
    ];

    /* ── After Submit: the Bulk Projects list ────────────────────
       The newest project is the highlighted row when the list marks one, otherwise the
       first row (the list opens newest-first). 'done' is the last card: Done closes it. */
    var NEW_ROW = '@new-row'; // resolved by targetOf()
    var PAGE_STEPS = [
        {
            kind: 'field', target: NEW_ROW, position: 'bottom',
            title: 'Your bulk project is created! 🎉',
            message: 'This is your new project. <strong>Post Count</strong> is how many posts it will write — one per keyword. The posts are written in the background, so you can leave this page.'
        },
        {
            kind: 'field', target: NEW_ROW, cell: 'td[data-label="Project Status"]', position: 'left',
            title: 'Project status',
            message: '<strong>Processing</strong> while the posts are being written, <strong>Completed</strong> once they all are. Refresh this page to check — we’ll also email you when it’s done.'
        },
        {
            kind: 'field', target: NEW_ROW, cell: 'td[data-label="Publish Mode"]', position: 'left',
            title: 'Publish mode',
            message: 'What happens to each post when it’s ready — published straight away, saved as a draft, or published on your schedule. This is the choice you made in the wizard.'
        },
        {
            kind: 'done', target: NEW_ROW, cell: '.action-btn-pop', position: 'left',
            title: 'Manage your project',
            message: 'Open this <strong>⋯</strong> menu to <strong>View All Posts Within Project</strong> (review or edit each one), cancel while it’s processing, export the post URLs, or delete the project.'
        }
    ];

    var STEPS = PAGE_MODE ? PAGE_STEPS : WIZARD_STEPS;

    /* ── State ─────────────────────────────────────────────────── */
    var current   = -1;
    var $tooltip  = null;
    var $spotlight = null; // list page only — see positionSpotlight()
    var _watchTmr = null;
    var _posTmr   = null;
    var _done     = false;
    var counted   = [];   // indices of the steps that count toward "Step X of N"

    function $in(selector) {
        return $(SCOPE).find(selector);
    }

    // What a step points at. List-page steps can narrow to one cell (cell) of the new
    // project's row: the row the list highlights, else its first row (newest first).
    function targetOf(step) {
        var $t;
        if (step.target === NEW_ROW) {
            $t = $('.project_table_listing tbody tr.WHProject--highlight').filter(':visible').first();
            if (!$t.length) $t = $('.project_table_listing tbody tr').filter(':visible').first();
        } else {
            $t = $in(step.target).filter(':visible').first();
        }
        if (step.cell && $t.length) $t = $t.find(step.cell).filter(':visible').first();
        return $t;
    }

    function stepCounts(i) {
        var s = STEPS[i];
        return !s.needs || $in(s.needs).length > 0;
    }

    // The wizard's panel on screen right now (0-based).
    function currentPanel() {
        var $sections = $in('.data_multi');
        var idx = $sections.index($sections.filter('.dataJSmulti_multi').first());
        return idx < 0 ? 0 : idx;
    }

    function modalOpen() {
        return $(MODAL).is(':visible') && !$(MODAL).hasClass('hide_and_show_ai_popup');
    }

    function firstStepOfPanel(p) {
        for (var i = 0; i < STEPS.length; i++) {
            if (STEPS[i].panel === p && stepCounts(i)) return i;
        }
        return -1;
    }

    function lastStepOfPanel(p) {
        for (var i = STEPS.length - 1; i >= 0; i--) {
            if (STEPS[i].panel === p && stepCounts(i)) return i;
        }
        return -1;
    }

    function nextCountedStep(i) {
        for (var j = i + 1; j < STEPS.length; j++) {
            if (stepCounts(j)) return j;
        }
        return -1;
    }

    /* ── Card ──────────────────────────────────────────────────── */
    function stepHasValue(step) {
        if (!step.requireValue) return true;
        var $el = $in(step.requireValue);
        if (!$el.length) return false;
        if ($el.is('select, input[type="text"], textarea')) return !!$.trim($el.val() || '');
        return true; // e.g. a :checked radio was found
    }

    function render(i) {
        var step = STEPS[i];
        var pos  = counted.indexOf(i) + 1;
        var pct  = Math.round((pos / counted.length) * 100);

        var html = '<div class="iseo-guide-tooltip-inner">';
        html += '<div class="iseo-guide-progress"><div class="iseo-guide-progress-bar" style="width:' + pct + '%"></div></div>';
        html += '<div class="iseo-guide-header"><span class="iseo-guide-bot">&#x1F916;</span>'
             +  '<span class="iseo-guide-step-counter">Step ' + pos + ' of ' + counted.length + '</span></div>';
        html += '<div class="iseo-guide-title">' + step.title + '</div>';
        html += '<div class="iseo-guide-message">' + step.message;
        if (step.kind === 'next' || step.kind === 'submit') {
            html += '<br><small>↓ Click the <strong>' + (step.kind === 'submit' ? 'Submit' : 'Next') + '</strong> button below to continue</small>';
        }
        html += '<span class="iseo-bulk-guide-wait" style="display:none;"><br><small>' + (step.waitMessage || '') + '</small></span>';
        html += '</div>';
        html += '<div class="iseo-guide-actions">';
        if (step.kind === 'field') {
            html += '<button class="iseo-guide-btn-next" type="button">Next →</button>';
        } else if (step.kind === 'done') {
            html += '<button class="iseo-guide-btn-next iseo-guide-btn-final iseo-bulk-guide-done" type="button">Done ✓</button>';
        }
        html += '<button class="iseo-guide-btn-skip" type="button">Skip guide</button>';
        html += '</div></div>';

        $tooltip.html(html).show();
        $tooltip.find('.iseo-guide-btn-skip, .iseo-bulk-guide-done').on('click', destroy);
        $tooltip.find('.iseo-guide-btn-next').not('.iseo-bulk-guide-done').on('click', function () {
            if (!stepHasValue(STEPS[current])) {
                $tooltip.find('.iseo-bulk-guide-wait').show();
                return;
            }
            var n = nextCountedStep(current);
            if (n >= 0) showStep(n);
        });
    }

    function showWaiting(title, message) {
        var html = '<div class="iseo-guide-tooltip-inner">'
            + '<div class="iseo-guide-progress"><div class="iseo-guide-progress-bar" style="width:100%"></div></div>'
            + '<div class="iseo-guide-header"><span class="iseo-guide-bot">&#x1F916;</span><span class="iseo-guide-step-counter">Please wait…</span></div>'
            + '<div class="iseo-guide-title">' + title + '</div>'
            + '<div class="iseo-guide-message">' + message + '</div>'
            + '</div>';
        $('.iseo-guide-highlight').removeClass('iseo-guide-highlight');
        $tooltip.html(html).attr('data-pos', 'docked').css({ top: '', left: '', width: '' }).show();
    }

    /* ── Positioning ───────────────────────────────────────────── */
    // Tries the step's own side first, then the others, and takes the first that fits
    // the viewport; if none does, the closest one clamped on screen.
    function place() {
        if (current < 0 || !$tooltip.is(':visible')) return;
        var $t = targetOf(STEPS[current]);
        if (!$t.length) return;

        var r   = $t[0].getBoundingClientRect();
        positionSpotlight(r);
        var ttW = Math.min(300, window.innerWidth - 20);
        $tooltip.css({ width: ttW + 'px' });
        var ttH = $tooltip.outerHeight(true) || 200;
        var gap = 14;
        var vw  = window.innerWidth;
        var vh  = window.innerHeight;

        function calc(p) {
            switch (p) {
                case 'bottom': return { top: r.bottom + gap,           left: r.left + r.width / 2 - ttW / 2 };
                case 'top':    return { top: r.top - ttH - gap,        left: r.left + r.width / 2 - ttW / 2 };
                case 'right':  return { top: r.top + r.height / 2 - ttH / 2, left: r.right + gap };
                default:       return { top: r.top + r.height / 2 - ttH / 2, left: r.left - ttW - gap };
            }
        }
        // Above/below can always be slid sideways onto the screen, so only their height
        // has to fit; beside the target, the width has to fit too.
        function fits(side, p) {
            var vertical = p.top >= 10 && p.top + ttH <= vh - 10;
            if (side === 'top' || side === 'bottom') return vertical;
            return vertical && p.left >= 10 && p.left + ttW <= vw - 10;
        }

        var order = [STEPS[current].position, 'top', 'bottom', 'left', 'right'];
        var side  = order[0];
        var pos   = calc(side);
        for (var k = 0; k < order.length; k++) {
            var c = calc(order[k]);
            if (fits(order[k], c)) {
                side = order[k];
                pos  = c;
                break;
            }
        }

        $tooltip.css({
            top:  Math.max(10, Math.min(pos.top,  vh - ttH - 10)) + 'px',
            left: Math.max(10, Math.min(pos.left, vw - ttW - 10)) + 'px',
            right: 'auto'
        }).attr('data-pos', side);
    }

    // List page only: the dimmed-page spotlight around the step's target.
    function positionSpotlight(r) {
        if (!$spotlight) return;
        var pad = 8;
        $spotlight.css({
            top:    (r.top    - pad) + 'px',
            left:   (r.left   - pad) + 'px',
            width:  (r.width  + pad * 2) + 'px',
            height: (r.height + pad * 2) + 'px'
        }).show();
    }

    function schedulePlace(delay) {
        clearTimeout(_posTmr);
        _posTmr = setTimeout(place, delay || 60);
    }

    /* ── Steps ─────────────────────────────────────────────────── */
    function showStep(i) {
        if (i < 0 || i >= STEPS.length) return;
        var step = STEPS[i];

        // A field that isn't on screen (e.g. the keywords box before a list is chosen)
        // has nothing to point at — move on to the next step of the same panel.
        if (step.kind === 'field' && !targetOf(step).length) {
            var n = nextCountedStep(i);
            if (n >= 0 && STEPS[n].panel === step.panel) { showStep(n); return; }
        }

        current = i;
        $('.iseo-guide-highlight').removeClass('iseo-guide-highlight');
        render(i);

        var $t = targetOf(step);
        if ($t.length) {
            // Inside the modal the field glows; on the list page the spotlight frames it
            // instead (an outline on a table row doesn't reliably render).
            if (!PAGE_MODE) $t.addClass('iseo-guide-highlight');
            // The modal is its own scroll container; this scrolls it, not the page.
            $t[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        place();
        schedulePlace(400); // again once the smooth scroll has settled
    }

    // Keeps the card on the panel the user is actually on. Runs on a short interval
    // because the wizard exposes no event for a panel change.
    function watch() {
        if (_done) return;
        if (!modalOpen()) {
            $tooltip.hide();
            $('.iseo-guide-highlight').removeClass('iseo-guide-highlight');
            return;
        }
        var p = currentPanel();
        if (current < 0 || STEPS[current].panel !== p) {
            var forward = current < 0 || p > STEPS[current].panel;
            var target  = forward ? firstStepOfPanel(p) : lastStepOfPanel(p);
            if (target >= 0) showStep(target);
            return;
        }
        if (!$tooltip.is(':visible')) showStep(current); // modal re-opened

        // The "Select a keyword list" hint disappears as soon as there is one.
        if (stepHasValue(STEPS[current])) $tooltip.find('.iseo-bulk-guide-wait').hide();
    }

    /* ── Teardown ──────────────────────────────────────────────── */
    function destroy() {
        _done = true;
        clearInterval(_watchTmr);
        clearTimeout(_posTmr);
        $('.iseo-guide-highlight').removeClass('iseo-guide-highlight');
        if ($tooltip) $tooltip.remove();
        if ($spotlight) $spotlight.remove();
        window.iseoBulkGuideActive = false;
        $('body').removeClass('iseo-guide-active');
        $(window).off('.iseobulkguide');
        $(NEXT).off('.iseobulkguide');
        document.removeEventListener('scroll', onScroll, true);
    }

    function onScroll() { schedulePlace(30); }

    /* ── Start ─────────────────────────────────────────────────── */
    function init() {
        if (!$(MODAL).length && !PAGE_MODE) return;

        for (var i = 0; i < STEPS.length; i++) {
            if (stepCounts(i)) counted.push(i);
        }

        $tooltip = $('<div id="iseo-guide-tooltip"></div>').appendTo('body');
        $('body').addClass('iseo-guide-active');

        if (PAGE_MODE) {
            // No project rows (e.g. the create failed and the list is empty): nothing to show.
            if (!targetOf(PAGE_STEPS[0]).length) { destroy(); return; }
            $spotlight = $('<div id="iseo-guide-spotlight"></div>').appendTo('body');
            $(window).on('resize.iseobulkguide', function () { schedulePlace(80); });
            document.addEventListener('scroll', onScroll, true);
            setTimeout(function () { showStep(0); }, 300);
            return;
        }

        // Read by the wizard's submit handler (custom-plugin-script.js): while this is set,
        // the redirect to the Bulk Projects list carries from=onboarding, so the guide
        // continues there.
        window.iseoBulkGuideActive = true;

        // Submit. Bound directly, not delegated from document: a delegated jQuery click
        // is skipped when the target is disabled, and the wizard's own handler disables
        // this button (BulkSubmitButton.setProcessing) synchronously on Submit.
        $(NEXT).on('click.iseobulkguide', function () {
            if (current < 0 || STEPS[current].kind !== 'submit') return;
            // Validation (project name, keywords) may stop the submit; only switch to the
            // waiting card once the wizard has actually gone into its processing state.
            setTimeout(function () {
                if ($(NEXT).prop('disabled') || /process/i.test($(NEXT).text())) {
                    _done = true;
                    clearInterval(_watchTmr);
                    showWaiting('Creating your bulk project &#x23F3;',
                        'Your project is being set up. You’ll be taken to your projects in a moment — the posts are written in the background and we’ll email you when they’re done.');
                }
            }, 400);
        });

        $(window).on('resize.iseobulkguide', function () { schedulePlace(80); });
        // Scroll events don't bubble, and the modal scrolls on its own — capture them.
        document.addEventListener('scroll', onScroll, true);

        _watchTmr = setInterval(watch, 300);
    }

    $(document).ready(init);

})(jQuery);
