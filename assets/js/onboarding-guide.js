/**
 * ImproveSEO Onboarding Guide
 *
 * Step-by-step spotlight + tooltip overlay for the single post creation flow.
 * Only activates when window.iseoGuideConfig.active === true.
 *
 * Step 1 (card-choice page) is handled by index.php.
 * Steps 2–22 are handled here (indices 0–20 in STEPS array).
 *
 * Wizard-step 0 (Keyword & Post Title):
 *   0  seed-keyword      (modal) #seed_keyword              next-btn
 *   1  title-type        (modal) #seed_select               next-btn      ← seed_option2 pre-selected
 *   2  tone              (modal) #cotnt_type                next-btn      ← select before generating
 *   3  title-generate    (modal) #reload                    click-target  ← click to generate AI title
 *   4  title-approve     (modal) .step_one_approve_button   click-target  ← click Approve checkbox
 *   5  title-next        (modal) #nextStepButton            wizard-next   ← glow Next after approval
 * Wizard-step 1 (Content Settings):
 *   6  article-size      (modal) #post_size                 next-btn
 *   7  point-of-view     (modal) #size                      next-btn
 *   8  language          (modal) #language                  next-btn
 *   9  details           (modal) #exampleFormControlTextarea next-btn
 *   10 call-to-action    (modal) #call_to_action            wizard-next   ← user clicks wizard Next
 * Wizard-step 2 (Add Media):
 *   11 media-select      (modal) #AI_image                  click-target  ← click to select
 *   12 media-next        (modal) #nextStepButton            wizard-next   ← user clicks wizard Next
 * Wizard-step 3 (Generate AI Content):
 *   13 generate-post     (modal) #generateapivalue          click-target  ← click to generate
 *   14 approve-content   (modal) #nextStepButton            wizard-next   ← "Approve Content" label
 * Wizard-step 4 (Meta Title & Description):
 *   15 meta-title        (modal) #meta_title                next-btn
 *   16 meta-desc         (modal) #meta_descreption          next-btn
 *   17 meta-submit       (modal) #nextStepButton            wizard-next   ← "Submit" label
 * Form phase:
 *   18 project-name      (form)  .PostForm__name            next-btn
 *   19 post-title        (form)  #title                     next-btn
 *   20 publish           (form)  button[name="create"]      final
 */
(function ($) {
    'use strict';

    /* ── Guard ──────────────────────────────────────────────── */
    if (!window.iseoGuideConfig || !window.iseoGuideConfig.active) return;

    /* ── Constants ──────────────────────────────────────────── */
    var FORM_PHASE_START  = 18; // index of first form-phase step
    var STEP_GENERATE_IDX = 13; // index of "Generate AI Post" click step
    var STEP_APPROVE_IDX  = 14; // index of "Approve Content" wizard-next step
    var STEP_META_SUBMIT  = 17; // index of meta "Submit" wizard-next step

    /* ── Steps Definition ───────────────────────────────────── */
    var STEPS = [
        /* 0 */ {
            phase: 'modal', wizardStep: 0, target: '#seed_keyword',
            title: 'Enter your Keyword',
            message: 'This is the main topic of your article. We\u2019ve pre-filled a keyword based on your business details \u2014 feel free to adjust it.',
            position: 'right', advance: 'next-btn'
        },
        /* 1 */ {
            phase: 'modal', wizardStep: 0, target: '#seed_select',
            title: 'Choose Title Style',
            message: 'We\u2019ve selected <strong>Create Best Title from Keyword</strong> \u2014 the AI will craft an SEO-optimised headline for you. You can change this if you prefer.',
            position: 'right', advance: 'next-btn'
        },
        /* 2 */ {
            phase: 'modal', wizardStep: 0, target: '#cotnt_type',
            title: 'Set Your Tone of Voice',
            message: 'Choose the writing style for your audience. <em>Professional</em> suits service businesses; <em>Informational</em> works well for how-to guides.',
            position: 'right', advance: 'next-btn'
        },
        /* 3 */ {
            phase: 'modal', wizardStep: 0, target: '#reload',
            title: 'Generate your Title',
            message: 'Click the <strong>Generate</strong> button to let the AI create an optimised title from your keyword.',
            position: 'right', advance: 'click-target'
        },
        /* 4 */ {
            phase: 'modal', wizardStep: 0, target: '.step_one_approve_button',
            title: 'Approve the AI Title',
            message: 'Review the suggested title above. Happy with it? Click <strong>Approve</strong> to confirm and proceed.',
            position: 'right', advance: 'click-target'
        },
        /* 5 */ {
            phase: 'modal', wizardStep: 0, target: '#nextStepButton',
            title: 'Title Approved!',
            message: 'Great! Now click <strong>Next \u2192</strong> below to move on to the content settings.',
            position: 'top', advance: 'wizard-next'
        },
        /* 6 */ {
            phase: 'modal', wizardStep: 1, target: '#post_size',
            title: 'Article Length',
            message: 'Longer articles often rank better for competitive keywords. <em>Medium (1,200\u20132,400 words)</em> is a great starting point for most niches.',
            position: 'right', advance: 'next-btn'
        },
        /* 7 */ {
            phase: 'modal', wizardStep: 1, target: '#size',
            title: 'Point of View',
            message: 'Choose how the article addresses the reader. <em>Second Person (you, your)</em> is recommended for engaging, direct copy.',
            position: 'right', advance: 'next-btn'
        },
        /* 7 */ {
            phase: 'modal', wizardStep: 1, target: '#language',
            title: 'Content Language',
            message: 'Select the language and regional variant for your article. Match this to your target audience\u2019s location.',
            position: 'right', advance: 'next-btn'
        },
        /* 8 */ {
            phase: 'modal', wizardStep: 1, target: '#exampleFormControlTextarea',
            title: 'Details to Include',
            message: '(Optional) Add specific facts, services, or local details you want the AI to weave into the article. Leave blank to let the AI decide.',
            position: 'right', advance: 'next-btn'
        },
        /* 9 */ {
            phase: 'modal', wizardStep: 1, target: '#call_to_action',
            title: 'Call to Action',
            message: '(Optional) Tell the AI what action you want readers to take \u2014 e.g. <em>Contact us for a free quote</em>.',
            position: 'right', advance: 'wizard-next'
        },
        /* 10 */ {
            phase: 'modal', wizardStep: 2, target: '#AI_image',
            title: 'Select Image Option',
            message: 'Click <strong>Generate AI Image Based on Title</strong> to automatically create an image for your article.',
            position: 'right', advance: 'click-target'
        },
        /* 11 */ {
            phase: 'modal', wizardStep: 2, target: '#nextStepButton',
            title: 'Image Option Set',
            message: 'Your image preference has been saved. Proceed to the content generation step.',
            position: 'top', advance: 'wizard-next'
        },
        /* 12 */ {
            phase: 'modal', wizardStep: 3, target: '#generateapivalue',
            title: 'Generate your Article',
            message: 'Everything is set! Click <strong>Generate AI Post</strong> to create your article. This usually takes 20\u201360 seconds.',
            position: 'top', advance: 'click-target'
        },
        /* 13 */ {
            phase: 'modal', wizardStep: 3, target: '#nextStepButton',
            title: 'Review & Approve',
            message: 'Your article is ready! Scroll up in this window to read it. When you\u2019re happy, click the <strong>Approve Content</strong> button to continue.',
            position: 'top', advance: 'wizard-next'
        },
        /* 14 */ {
            phase: 'modal', wizardStep: 4, target: '#meta_title',
            title: 'SEO Meta Title',
            message: 'This title appears in Google search results. Keep it under 60 characters. The AI has suggested one \u2014 you can edit it freely.',
            position: 'right', advance: 'next-btn'
        },
        /* 15 */ {
            phase: 'modal', wizardStep: 4, target: '#meta_descreption',
            title: 'Meta Description',
            message: 'A short description shown under your page title in Google. Keep it under 160 characters \u2014 make it compelling to increase clicks.',
            position: 'right', advance: 'next-btn'
        },
        /* 16 */ {
            phase: 'modal', wizardStep: 4, target: '#nextStepButton',
            title: 'Save & Continue',
            message: 'Happy with your SEO details? Click the <strong>Submit</strong> button to save them and return to the post editor.',
            position: 'top', advance: 'wizard-next'
        },
        /* 17 */ {
            phase: 'form', wizardStep: -1, target: '.PostForm__name',
            title: 'Name Your Project',
            message: 'Give this project an internal name (e.g. <em>Homepage Blog Q1</em>). This is for your reference only \u2014 it won\u2019t be published.',
            position: 'bottom', advance: 'next-btn',
            formFocus: '.PostForm__name-wrap'
        },
        /* 18 */ {
            phase: 'form', wizardStep: -1, target: '#title',
            title: 'Post Title',
            message: 'This is the public title of your article. The AI has pre-filled it from the generated content \u2014 you can edit it here.',
            position: 'bottom', advance: 'next-btn',
            formFocus: '.PostForm__title-wrap'
        },
        /* 19 */ {
            phase: 'form', wizardStep: -1, target: 'button[name="create"]',
            title: 'Publish your Article! \uD83C\uDF89',
            message: 'You\u2019re all set! Click <strong>Create &amp; Publish Post</strong> to publish your first SEO-optimised article.',
            position: 'top', advance: 'final',
            formFocus: '#post_form_buttons'
        }
    ];

    /* ── State ──────────────────────────────────────────────── */
    var currentStep  = -1;  // -1 = waiting for modal to open
    var $spotlight   = null;
    var $tooltip     = null;
    var _waiting     = false; // true while polling for generated content
    var _reposTmr    = null;

    /* ─────────────────────────────────────────────────────────
       INIT
    ───────────────────────────────────────────────────────── */
    function init() {
        $spotlight = $('<div id="iseo-guide-spotlight"></div>').appendTo('body');
        $tooltip   = $('<div id="iseo-guide-tooltip"></div>').appendTo('body');
        $('body').addClass('iseo-guide-active');
        bindEvents();

        // Trigger modal open (button may be hidden but trigger works)
        setTimeout(function () {
            $('#generate_ai_popup_open').trigger('click');

            // Fallback: shown.bs.modal may not fire in all Bootstrap versions.
            waitForVisible('#exampleModal1', function () {
                if (currentStep === -1) {
                    preSelectTitleOption();
                    setTimeout(function () { showStep(0); }, 300);
                }
            }, 10);
        }, 100);
    }

    /* ─────────────────────────────────────────────────────────
       PRE-SELECT TITLE OPTION (seed_option2 = Create Best Title)
    ───────────────────────────────────────────────────────── */
    function preSelectTitleOption() {
        var $sel = $('#seed_select');
        if ($sel.length && $sel.val() === 'seed_option1') {
            $sel.val('seed_option2').trigger('change');
        }
    }

    /* ─────────────────────────────────────────────────────────
       REVEAL FORM (called when modal closes after generation)
    ───────────────────────────────────────────────────────── */
    function revealForm() {
        $('.style_create_page_form')
            .removeAttr('style')
            .css({ opacity: '1', visibility: 'visible' });
    }

    /* ─────────────────────────────────────────────────────────
       SHOW STEP
    ───────────────────────────────────────────────────────── */
    function showStep(index) {
        if (index >= STEPS.length) {
            destroyGuide();
            return;
        }
        _waiting    = false;
        currentStep = index;
        var step    = STEPS[index];
        var $target = $(step.target);

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
        // Total = 21 guide steps + 1 card-choice step (handled by index.php) = 22
        var total      = STEPS.length + 1;
        var pct        = Math.round(((index + 1) / total) * 100);
        var isNextBtn  = (step.advance === 'next-btn');
        var isWizNext  = (step.advance === 'wizard-next');
        var isFinal    = (step.advance === 'final');

        var html = '<div class="iseo-guide-tooltip-inner">';
        html += '<div class="iseo-guide-progress"><div class="iseo-guide-progress-bar" style="width:' + pct + '%"></div></div>';
        html += '<div class="iseo-guide-header">';
        html += '<span class="iseo-guide-bot">&#x1F916;</span>';
        html += '<span class="iseo-guide-step-counter">Step ' + (index + 2) + ' of ' + total + '</span>';
        html += '</div>';
        html += '<div class="iseo-guide-title">' + step.title + '</div>';
        html += '<div class="iseo-guide-message">' + step.message + '</div>';

        // wizard-next: instruct user to click the real Next button — no tooltip Next button
        if (isWizNext) {
            html += '<div class="iseo-guide-wizard-hint">&#8595; Click the <strong>Next &#8594;</strong> button below to continue</div>';
        }

        html += '<div class="iseo-guide-actions">';
        if (isNextBtn) {
            html += '<button class="iseo-guide-btn-next" type="button">Next &#8594;</button>';
        } else if (isFinal) {
            html += '<button class="iseo-guide-btn-next iseo-guide-btn-final" type="button">Done &#10003;</button>';
        }
        // click-target and wizard-next steps: no guide Next button

        html += '<button class="iseo-guide-btn-skip" type="button">Skip guide</button>';
        html += '</div></div>';

        $tooltip.html(html);
        $tooltip.find('.iseo-guide-btn-next').off('click.guide').on('click.guide', onNextClicked);
        $tooltip.find('.iseo-guide-btn-skip').off('click.guide').on('click.guide', destroyGuide);
    }

    /* ─────────────────────────────────────────────────────────
       WAITING TOOLTIP (shown during AI generation)
    ───────────────────────────────────────────────────────── */
    function showWaitingTooltip(title, message, pct) {
        var html = '<div class="iseo-guide-tooltip-inner">';
        html += '<div class="iseo-guide-progress"><div class="iseo-guide-progress-bar" style="width:' + (pct || 50) + '%"></div></div>';
        html += '<div class="iseo-guide-header"><span class="iseo-guide-bot">&#x1F916;</span><span class="iseo-guide-step-counter">Please wait\u2026</span></div>';
        html += '<div class="iseo-guide-title">' + (title || 'Generating\u2026 &#x23F3;') + '</div>';
        html += '<div class="iseo-guide-message">' + (message || 'This may take a moment \u2014 please wait.') + '</div>';
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
       ON NEXT CLICKED  (tooltip "Next →" button — only for next-btn steps)
    ───────────────────────────────────────────────────────── */
    function onNextClicked() {
        var step = STEPS[currentStep];
        if (step.advance === 'final') {
            destroyGuide();
        } else {
            showStep(currentStep + 1);
        }
    }

    /* ─────────────────────────────────────────────────────────
       WAIT FOR ELEMENT TO BECOME VISIBLE  (200 ms polling)
    ───────────────────────────────────────────────────────── */
    function waitForVisible(selector, callback, maxSeconds) {
        var tries = 0;
        var max   = (maxSeconds || 90) * 5;
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
       WAIT FOR INPUT VALUE TO BE NON-EMPTY  (200 ms polling)
    ───────────────────────────────────────────────────────── */
    function waitForValue(selector, callback, maxSeconds) {
        var tries = 0;
        var max   = (maxSeconds || 30) * 5;
        var iv = setInterval(function () {
            var val = $(selector).val();
            if (val && val.trim().length > 0) {
                clearInterval(iv);
                callback();
            } else if (++tries >= max) {
                clearInterval(iv);
            }
        }, 200);
    }

    /* ─────────────────────────────────────────────────────────
       WAIT FOR ELEMENT HTML TO BE NON-EMPTY  (200 ms polling)
    ───────────────────────────────────────────────────────── */
    function waitForContent(selector, callback, maxSeconds) {
        var tries = 0;
        var max   = (maxSeconds || 90) * 5;
        var iv = setInterval(function () {
            var html = $(selector).html();
            if (html && html.trim().length > 100) {
                clearInterval(iv);
                callback();
            } else if (++tries >= max) {
                clearInterval(iv);
            }
        }, 200);
    }

    /* ─────────────────────────────────────────────────────────
       WAIT FOR WIZARD PANEL TO BECOME ACTIVE  (MutationObserver)
       Fires when the next guide step's target element is visible,
       which happens only after advanceSingleStep() runs in the popup.
    ───────────────────────────────────────────────────────── */
    function waitForWizardPanel(nextIndex, callback) {
        var nextTarget = STEPS[nextIndex].target;
        var container  = document.querySelector('.improveseo-bulk-ai');
        if (!container) {
            // No container found — fall back to polling
            waitForVisible(nextTarget, callback, 10);
            return;
        }
        var obs = new MutationObserver(function () {
            if ($(nextTarget).is(':visible')) {
                obs.disconnect();
                callback();
            }
        });
        obs.observe(container, { subtree: true, attributes: true, attributeFilter: ['class'] });
        // Safety: disconnect after 10 s to avoid leaking observers
        setTimeout(function () { obs.disconnect(); }, 10000);
    }

    /* ─────────────────────────────────────────────────────────
       BIND EVENTS
    ───────────────────────────────────────────────────────── */
    function bindEvents() {

        /* ── Modal open ─────────────────────────────────────── */
        $(document).on('shown.bs.modal.iseoguide', '#exampleModal1', function () {
            if (currentStep === -1) {
                preSelectTitleOption();
                setTimeout(function () { showStep(0); }, 300);
            }
        });

        /* ── Modal close ────────────────────────────────────── */
        $(document).on('hidden.bs.modal.iseoguide', '#exampleModal1', function () {
            if (currentStep < 0 || currentStep >= FORM_PHASE_START) return;

            if (currentStep >= STEP_GENERATE_IDX) {
                // Modal closed after generation started → transition to form phase
                setTimeout(function () {
                    if (currentStep >= FORM_PHASE_START) return; // already handled
                    revealForm();
                    showStep(FORM_PHASE_START);
                }, 350);
            } else {
                // Modal closed before guide reached generation — hide guide UI
                $tooltip.hide();
                $spotlight.hide();
                $('.iseo-guide-highlight').removeClass('iseo-guide-highlight');
            }
        });

        /* ── Wizard Next button ─────────────────────────────── */
        // For wizard-next steps the user must click the real Next button.
        // We observe the modal panels via MutationObserver so the guide
        // advances only after advanceSingleStep() has actually run in the
        // popup (i.e. after any async credit check completes).
        $(document).on('click.iseoguide', '#nextStepButton', function () {
            if (currentStep < 0 || currentStep >= STEPS.length) return;
            var step = STEPS[currentStep];
            if (step.advance !== 'wizard-next') return;

            var capturedStep = currentStep;
            if (capturedStep === STEP_META_SUBMIT) {
                // saveFinalData() will hide the modal — reveal the form
                setTimeout(function () {
                    if (currentStep >= FORM_PHASE_START) return;
                    revealForm();
                    showStep(FORM_PHASE_START);
                }, 600);
            } else {
                var nextIdx = capturedStep + 1;
                waitForWizardPanel(nextIdx, function () {
                    if (currentStep === capturedStep) {
                        showStep(nextIdx);
                    }
                });
            }
        });

        /* ── Step 3: click #reload → wait for AI title ──────── */
        $(document).on('click.iseoguide', '#reload', function () {
            if (currentStep !== 3) return;
            _waiting = true;
            showWaitingTooltip(
                'Generating your AI Title &#x23F3;',
                'The AI is crafting an optimised title from your keyword \u2014 please wait.',
                30
            );
            waitForValue('#maintitlearea', function () {
                if (_waiting) {
                    _waiting = false;
                    showStep(4); // → approve title
                }
            }, 30);
        });

        /* ── Step 4: approve title checkbox ─────────────────── */
        $(document).on('change.iseoguide', '#checkbox_need', function () {
            if (currentStep !== 4) return;
            if ($(this).is(':checked')) {
                setTimeout(function () { showStep(5); }, 200); // → title-next (wizard-next glow on Next button)
            }
        });

        /* ── Step 11: select AI image radio ─────────────────── */
        $(document).on('click.iseoguide', '#AI_image', function () {
            if (currentStep !== 11) return;
            setTimeout(function () { showStep(12); }, 200); // → media wizard-next
        });

        /* ── Step 12: click Generate AI Post ────────────────── */
        $(document).on('click.iseoguide', '#generateapivalue', function () {
            if (currentStep !== STEP_GENERATE_IDX) return;
            _waiting = true;
            showWaitingTooltip(
                'Generating your Article &#x23F3;',
                'Your article is being written by AI.<br><small>This may take 20\u201360 seconds \u2014 please wait.</small>',
                65
            );
            waitForContent('#showmydataindiv1', function () {
                if (_waiting) {
                    _waiting = false;
                    showStep(STEP_APPROVE_IDX); // → approve content (wizard-next)
                }
            }, 90);
        });

        /* ── Reposition on resize / scroll ──────────────────── */
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
