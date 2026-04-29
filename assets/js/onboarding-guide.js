/**
 * ImproveSEO Onboarding Guide
 *
 * Step-by-step spotlight + tooltip overlay for the single post creation flow.
 * Only activates when window.iseoGuideConfig.active === true.
 *
 * Steps:
 *   0  open-modal      (page)  #generate_ai_popup_open     click-target
 *   1  seed-keyword    (modal) #seed_keyword                next-btn
 *   2  title-type      (modal) #seed_select                 next-btn
 *   3  tone            (modal) #cotnt_type                  wizard-next
 *   4  word-count      (modal) #post_size                   wizard-next
 *   5  images          (modal) #step-3                      wizard-next
 *   6  generate        (modal) #generateapivalue            click-target
 *   7  approve         (modal) #generateapi                 click-target
 *   8  meta-title      (modal) #meta_title                  next-btn
 *   9  meta-desc       (modal) #meta_descreption            next-btn
 *   10 meta-submit     (modal) #step-5 input[type="button"] click-target
 *   11 project-name    (form)  .PostForm__name              next-btn
 *   12 post-title      (form)  #title                       next-btn
 *   13 publish         (form)  button[name="create"]        final
 */
(function ($) {
    'use strict';

    /* ── Guard ──────────────────────────────────────────────── */
    if (!window.iseoGuideConfig || !window.iseoGuideConfig.active) return;

    /* ── Constants ──────────────────────────────────────────── */
    var FORM_PHASE_START = 11;
    var STEP_APPROVE     = 7;

    /* ── Steps Definition ───────────────────────────────────── */
    var STEPS = [
        /* 0 */ {
            phase: 'page',  wizardStep: -1, target: '#generate_ai_popup_open',
            title: 'Let\u2019s create your first article! \uD83D\uDE80',
            message: 'Click the <strong>Generate AI Content</strong> button to open the content wizard. We\u2019ll guide you through each step.',
            position: 'bottom', advance: 'click-target'
        },
        /* 1 */ {
            phase: 'modal', wizardStep: 0,  target: '#seed_keyword',
            title: 'Enter your Keyword',
            message: 'This is the main topic of your article. We\u2019ve pre-filled a keyword based on your business details \u2014 feel free to adjust it.',
            position: 'right', advance: 'next-btn'
        },
        /* 2 */ {
            phase: 'modal', wizardStep: 0,  target: '#seed_select',
            title: 'Choose Title Style',
            message: '<em>Use Keyword As Is</em> keeps it exact; <em>Create Best Title</em> lets the AI optimise the headline for SEO. Either works well.',
            position: 'right', advance: 'next-btn'
        },
        /* 3 */ {
            phase: 'modal', wizardStep: 0,  target: '#cotnt_type',
            title: 'Set Your Tone',
            message: 'Choose the writing style for your audience. <em>Professional</em> suits service businesses; <em>Informational</em> works well for how-to guides.',
            position: 'right', advance: 'wizard-next'
        },
        /* 4 */ {
            phase: 'modal', wizardStep: 1,  target: '#post_size',
            title: 'Article Length',
            message: 'Longer articles often rank better for competitive keywords. 1,200\u20132,400 words is a great starting point for most niches.',
            position: 'right', advance: 'wizard-next'
        },
        /* 5 */ {
            phase: 'modal', wizardStep: 2,  target: '#step-3',
            title: 'Add Images (Optional)',
            message: 'You can add AI-generated images or skip this step for now. Images help with engagement but are not required to publish.',
            position: 'top', advance: 'wizard-next'
        },
        /* 6 */ {
            phase: 'modal', wizardStep: 3,  target: '#generateapivalue',
            title: 'Generate your Article',
            message: 'Everything is set! Click <strong>Generate AI Post</strong> to create your article. This usually takes 20\u201360 seconds.',
            position: 'top', advance: 'click-target'
        },
        /* 7 */ {
            phase: 'modal', wizardStep: 3,  target: '#generateapi',
            title: 'Review & Approve',
            message: 'Your article is ready! Scroll up to review the content. When you\u2019re happy with it, click <strong>Approve Content</strong> to continue.',
            position: 'top', advance: 'click-target'
        },
        /* 8 */ {
            phase: 'modal', wizardStep: 4,  target: '#meta_title',
            title: 'SEO Meta Title',
            message: 'This title appears in Google search results. Keep it under 60 characters. The AI has suggested one \u2014 you can edit it freely.',
            position: 'right', advance: 'next-btn'
        },
        /* 9 */ {
            phase: 'modal', wizardStep: 4,  target: '#meta_descreption',
            title: 'Meta Description',
            message: 'A short description shown under your page title in Google. Keep it under 160 characters \u2014 make it compelling to increase clicks.',
            position: 'right', advance: 'next-btn'
        },
        /* 10 */ {
            phase: 'modal', wizardStep: 4,  target: '#step-5 input[type="button"]',
            title: 'Save & Continue',
            message: 'Happy with your SEO details? Click <strong>Submit</strong> to save them and return to the post editor.',
            position: 'top', advance: 'click-target'
        },
        /* 11 */ {
            phase: 'form',  wizardStep: -1, target: '.PostForm__name',
            title: 'Name Your Project',
            message: 'Give this project an internal name (e.g. <em>Homepage Blog Q1</em>). This is for your reference only \u2014 it won\u2019t be published.',
            position: 'bottom', advance: 'next-btn',
            formFocus: '.PostForm__name-wrap'
        },
        /* 12 */ {
            phase: 'form',  wizardStep: -1, target: '#title',
            title: 'Post Title',
            message: 'This is the public title of your article. The AI has pre-filled it from the generated content \u2014 you can edit it here.',
            position: 'bottom', advance: 'next-btn',
            formFocus: '.PostForm__title-wrap'
        },
        /* 13 */ {
            phase: 'form',  wizardStep: -1, target: 'button[name="create"]',
            title: 'Publish your Article! \uD83C\uDF89',
            message: 'You\u2019re all set! Click <strong>Create &amp; Publish Post</strong> to publish your first SEO-optimised article.',
            position: 'top', advance: 'final',
            formFocus: '#post_form_buttons'
        }
    ];

    /* ── State ──────────────────────────────────────────────── */
    var currentStep  = 0;
    var $spotlight   = null;
    var $tooltip     = null;
    var _waiting     = false; // true while polling for #generateapi
    var _reposTmr    = null;

    /* ─────────────────────────────────────────────────────────
       INIT
    ───────────────────────────────────────────────────────── */
    function init() {
        $spotlight = $('<div id="iseo-guide-spotlight"></div>').appendTo('body');
        $tooltip   = $('<div id="iseo-guide-tooltip"></div>').appendTo('body');
        $('body').addClass('iseo-guide-active');
        bindEvents();
        showStep(0);
    }

    /* ─────────────────────────────────────────────────────────
       SHOW STEP
    ───────────────────────────────────────────────────────── */
    function showStep(index) {
        if (index >= STEPS.length) {
            destroyGuide();
            return;
        }
        _waiting     = false;
        currentStep  = index;
        var step     = STEPS[index];
        var $target  = $(step.target);

        // Remove all previous highlights
        $('.iseo-guide-highlight').removeClass('iseo-guide-highlight');

        // Update form-focus classes
        updateFormFocus(step);

        // Build tooltip content
        buildTooltip(step, index);

        if (step.phase === 'page' || step.phase === 'form') {
            $spotlight.show();
            if ($target.length) {
                $target[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                setTimeout(function () {
                    positionSpotlight($target);
                    positionTooltip($target, step.position);
                    $tooltip.show();
                }, 180);
            }
        } else {
            // Inside Bootstrap modal — use blue glow instead of spotlight
            $spotlight.hide();
            if ($target.length) {
                $target.addClass('iseo-guide-highlight');
                scrollWithinModal($target);
                setTimeout(function () {
                    positionTooltip($target, step.position);
                    $tooltip.show();
                }, 120);
            } else {
                $tooltip.show();
            }
        }
    }

    /* ─────────────────────────────────────────────────────────
       FORM FOCUS (grey-out helper)
    ───────────────────────────────────────────────────────── */
    function updateFormFocus(step) {
        // Clear previous focus markers
        $('.iseo-guide-form-focus').removeClass('iseo-guide-form-focus');

        if (step.phase === 'form') {
            $('body').addClass('iseo-guide-form-phase');
            if (step.formFocus) {
                $(step.formFocus).addClass('iseo-guide-form-focus');
            }
        }
    }

    /* ─────────────────────────────────────────────────────────
       BUILD TOOLTIP
    ───────────────────────────────────────────────────────── */
    function buildTooltip(step, index) {
        var total   = STEPS.length + 1; // +1 for Step 1 shown on the card-choice page
        var pct     = Math.round(((index + 1) / total) * 100);
        var showNext = (step.advance === 'next-btn' || step.advance === 'wizard-next');
        var isFinal  = (step.advance === 'final');

        var html = '<div class="iseo-guide-tooltip-inner">';
        html += '<div class="iseo-guide-progress"><div class="iseo-guide-progress-bar" style="width:' + pct + '%"></div></div>';
        html += '<div class="iseo-guide-header">';
        html += '<span class="iseo-guide-bot">&#x1F916;</span>';
        html += '<span class="iseo-guide-step-counter">Step ' + (index + 2) + ' of ' + total + '</span>';
        html += '</div>';
        html += '<div class="iseo-guide-title">' + step.title + '</div>';
        html += '<div class="iseo-guide-message">' + step.message + '</div>';
        html += '<div class="iseo-guide-actions">';

        if (showNext) {
            html += '<button class="iseo-guide-btn-next" type="button">Next &#8594;</button>';
        } else if (isFinal) {
            html += '<button class="iseo-guide-btn-next iseo-guide-btn-final" type="button">Done &#10003;</button>';
        }
        // click-target steps: no Next button — user advances by clicking the highlighted element

        html += '<button class="iseo-guide-btn-skip" type="button">Skip guide</button>';
        html += '</div></div>';

        $tooltip.html(html);
        $tooltip.find('.iseo-guide-btn-next').off('click.guide').on('click.guide', onNextClicked);
        $tooltip.find('.iseo-guide-btn-skip').off('click.guide').on('click.guide', destroyGuide);
    }

    /* ─────────────────────────────────────────────────────────
       WAITING TOOLTIP (shown during AI generation)
    ───────────────────────────────────────────────────────── */
    function showWaitingTooltip() {
        var html  = '<div class="iseo-guide-tooltip-inner">';
        html += '<div class="iseo-guide-progress"><div class="iseo-guide-progress-bar" style="width:55%"></div></div>';
        html += '<div class="iseo-guide-header"><span class="iseo-guide-bot">&#x1F916;</span><span class="iseo-guide-step-counter">Generating\u2026</span></div>';
        html += '<div class="iseo-guide-title">Generating your Article &#x23F3;</div>';
        html += '<div class="iseo-guide-message">Your article is being written by AI.<br><small>This may take 20\u201360 seconds \u2014 please wait.</small></div>';
        html += '<div class="iseo-guide-actions"><button class="iseo-guide-btn-skip" type="button">Skip guide</button></div>';
        html += '</div>';
        $tooltip.html(html).show();
        $tooltip.find('.iseo-guide-btn-skip').off('click.guide').on('click.guide', destroyGuide);
    }

    /* ─────────────────────────────────────────────────────────
       POSITION SPOTLIGHT
    ───────────────────────────────────────────────────────── */
    function positionSpotlight($target) {
        if (!$target.length) return;
        var r   = $target[0].getBoundingClientRect();
        var pad = 8;
        $spotlight.css({
            top:    (r.top    - pad) + 'px',
            left:   (r.left   - pad) + 'px',
            width:  (r.width  + pad * 2) + 'px',
            height: (r.height + pad * 2) + 'px'
        });
    }

    /* ─────────────────────────────────────────────────────────
       POSITION TOOLTIP
    ───────────────────────────────────────────────────────── */
    function positionTooltip($target, position) {
        if (!$target.length) {
            $tooltip.css({ top: '120px', left: '50px' }).attr('data-pos', '');
            return;
        }
        var r   = $target[0].getBoundingClientRect();
        var ttW = 300;
        var ttH = $tooltip.outerHeight(true) || 230;
        var gap = 14;
        var top, left;

        switch (position) {
            case 'bottom':
                top  = r.bottom + gap;
                left = r.left + r.width / 2 - ttW / 2;
                break;
            case 'top':
                top  = r.top - ttH - gap;
                left = r.left + r.width / 2 - ttW / 2;
                break;
            case 'right':
                top  = r.top + r.height / 2 - ttH / 2;
                left = r.right + gap;
                break;
            default: // left
                top  = r.top + r.height / 2 - ttH / 2;
                left = r.left - ttW - gap;
                break;
        }

        // Clamp to viewport with margin
        var vw = window.innerWidth, vh = window.innerHeight;
        top  = Math.max(10, Math.min(top,  vh - ttH - 10));
        left = Math.max(10, Math.min(left, vw - ttW - 10));

        $tooltip.css({ top: top + 'px', left: left + 'px', width: ttW + 'px' });
        $tooltip.attr('data-pos', position);
    }

    /* ─────────────────────────────────────────────────────────
       SCROLL WITHIN MODAL (keep highlighted field visible)
    ───────────────────────────────────────────────────────── */
    function scrollWithinModal($target) {
        var $body = $('#exampleModal1 .modal-body');
        if (!$body.length || !$target.length) return;
        var offset = $target.offset().top - $body.offset().top + $body.scrollTop() - 60;
        $body.stop(true).animate({ scrollTop: offset }, 200);
    }

    /* ─────────────────────────────────────────────────────────
       ON NEXT CLICKED
    ───────────────────────────────────────────────────────── */
    function onNextClicked() {
        var step = STEPS[currentStep];
        if (step.advance === 'wizard-next') {
            // Advance SmartWizard the same way saveData() does
            $('#nextStepButton').trigger('click');
            setTimeout(function () { showStep(currentStep + 1); }, 400);
        } else if (step.advance === 'final') {
            destroyGuide();
        } else {
            showStep(currentStep + 1);
        }
    }

    /* ─────────────────────────────────────────────────────────
       WAIT FOR ELEMENT TO BECOME VISIBLE
    ───────────────────────────────────────────────────────── */
    function waitForVisible(selector, callback, maxSeconds) {
        var tries = 0;
        var max   = (maxSeconds || 90) * 5; // 200 ms intervals
        var iv = setInterval(function () {
            if ($(selector).is(':visible')) {
                clearInterval(iv);
                callback();
            } else if (++tries >= max) {
                clearInterval(iv);
            }
        }, 200);
    }

    /* ─────────────────────────────────────────────────────────
       BIND EVENTS
    ───────────────────────────────────────────────────────── */
    function bindEvents() {
        // AI modal opens → advance from step 0 to step 1
        $(document).on('shown.bs.modal.iseoguide', '#exampleModal1', function () {
            if (currentStep === 0) {
                setTimeout(function () { showStep(1); }, 300);
            }
        });

        // AI modal closes
        $(document).on('hidden.bs.modal.iseoguide', '#exampleModal1', function () {
            if (currentStep >= FORM_PHASE_START) return; // Already in form phase

            if (currentStep >= STEP_APPROVE) {
                // Content was approved → move to form phase
                setTimeout(function () {
                    // Reveal the form (it starts hidden in guide mode)
                    $('.style_create_page_form')
                        .removeAttr('style')
                        .css({ opacity: '1', visibility: 'visible' });
                    showStep(FORM_PHASE_START);
                }, 350);
            } else {
                // Modal closed before content generation — hide guide UI.
                // The spotlight/tooltip are orphaned while modal is closed.
                $tooltip.hide();
                $spotlight.hide();
                $('.iseo-guide-highlight').removeClass('iseo-guide-highlight');
            }
        });

        // "Generate AI Post" clicked (step 6) → switch to waiting state
        $(document).on('click.iseoguide', '#generateapivalue', function () {
            if (currentStep !== 6) return;
            _waiting = true;
            showWaitingTooltip();
            waitForVisible('#generateapi', function () {
                if (_waiting) showStep(7); // Approve step
            }, 90);
        });

        // "Approve Content" clicked (step 7) → advance to meta-title (step 8)
        $(document).on('click.iseoguide', '#generateapi', function () {
            if (currentStep !== 7) return;
            // SmartWizard advances to Step 5 via saveData() → #nextStepButton
            setTimeout(function () { showStep(8); }, 400);
        });

        // Step 10: "Submit" in the meta step → saveFinalData() hides the modal
        // via jQuery .hide() before triggering #butn. Bootstrap may not fire
        // hidden.bs.modal if isShown is already false. Fallback: listen directly.
        $(document).on('click.iseoguide', '#step-5 input[type="button"]', function () {
            if (currentStep < STEP_APPROVE) return;
            setTimeout(function () {
                if (currentStep >= FORM_PHASE_START) return; // already handled by hidden.bs.modal
                $('.style_create_page_form')
                    .removeAttr('style')
                    .css({ opacity: '1', visibility: 'visible' });
                showStep(FORM_PHASE_START);
            }, 600); // allow hidden.bs.modal to fire first; only act if it didn't
        });

        // Reposition on window resize / scroll
        $(window).on('resize.iseoguide scroll.iseoguide', function () {
            clearTimeout(_reposTmr);
            _reposTmr = setTimeout(function () {
                if (currentStep < 0 || currentStep >= STEPS.length) return;
                var step    = STEPS[currentStep];
                var $target = $(step.target);
                if (step.phase !== 'modal') positionSpotlight($target);
                positionTooltip($target, step.position);
            }, 80);
        });
    }

    /* ─────────────────────────────────────────────────────────
       DESTROY GUIDE
    ───────────────────────────────────────────────────────── */
    function destroyGuide() {
        if ($spotlight) $spotlight.remove();
        if ($tooltip)   $tooltip.remove();
        $('.iseo-guide-highlight').removeClass('iseo-guide-highlight');
        $('.iseo-guide-form-focus').removeClass('iseo-guide-form-focus');
        $('body').removeClass('iseo-guide-active iseo-guide-form-phase');
        $(window).off('.iseoguide');
        $(document).off('.iseoguide');
        clearTimeout(_reposTmr);
    }

    /* ─────────────────────────────────────────────────────────
       START
    ───────────────────────────────────────────────────────── */
    $(document).ready(function () {
        init();
    });

}(jQuery));
