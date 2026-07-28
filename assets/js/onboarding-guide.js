/**
 * ImproveSEO Onboarding Guide
 *
 * Step-by-step spotlight + tooltip overlay for the single post creation flow.
 * Only activates when window.iseoGuideConfig.active === true.
 *
 * Step 1 (card-choice page) is handled by index.php.
 * Steps 2–24 are handled here (indices 0–22 in STEPS array).
 *
 * Wizard-step 0 (Keyword & Post Title):
 *   0  seed-keyword      (modal) #seed_keyword              next-btn
 *   1  niche             (modal) #niche_select              next-btn      ← business niche (new), pre-selected from setup
 *   2  title-type        (modal) #seed_select               next-btn      ← seed_option2 pre-selected
 *   3  tone              (modal) #cotnt_type                next-btn      ← select before generating
 *   4  title-generate    (modal) #reload                    click-target  ← click to generate AI title
 *   5  title-approve     (modal) .step_one_approve_button   click-target  ← click Approve checkbox
 *   6  title-next        (modal) #nextStepButton            wizard-next   ← glow Next after approval
 * Wizard-step 1 (Content Settings):
 *   7  article-size      (modal) #post_size                 next-btn
 *   8  point-of-view     (modal) #size                      next-btn
 *   9  language          (modal) #language                  next-btn
 *   10 niche-details     (modal) #niche_fields_container    next-btn      ← dynamic per-niche fields
 *   11 call-to-action    (modal) #call_to_action            next-btn      ← fill in CTA text, click guide’s Next
 *   12 cta-url           (modal) #cta_url                   next-btn      ← optional CTA URL
 *   13 cta-next          (modal) #nextStepButton            wizard-next   ← click modal Next to go to media
 * Wizard-step 2 (Add Media):
 *   14 media-select      (modal) label[for="AI_image"]      click-target  ← click to select
 *   15 media-next        (modal) #nextStepButton            wizard-next   ← "Generate AI Post" label; generation triggered automatically
 * Wizard-step 3 (Generate AI Content):
 *   16 approve-content   (modal) #nextStepButton            wizard-next   ← "Approve Content" label
 * Wizard-step 4 (Meta Title & Description):
 *   17 meta-title        (modal) #meta_title                next-btn
 *   18 meta-desc         (modal) #meta_descreption          next-btn
 *   19 meta-next         (modal) #nextStepButton            wizard-next   ← "Next" label; advances to Project Name panel (pollTarget)
 * Wizard-step 5 (Project Name & Categories):
 *   20 project-name      (modal) #modal_project_name        next-btn
 *   21 categories        (modal) .category-selection-section next-btn
 *   22 submit            (modal) #nextStepButton            final         ← "Submit" label; publishes via saveFinalData()
 */
(function ($) {
    'use strict';

    /* ── Guard ──────────────────────────────────────────────── */
    if (!window.iseoGuideConfig || !window.iseoGuideConfig.active) return;

    /* ── Constants ──────────────────────────────────────────── */
    var STEP_GENERATE_IDX = 15; // index where "Generate AI Post" is clicked (wizard-next triggers generation)
    var STEP_APPROVE_IDX  = 16; // index of "Approve Content" wizard-next step
    var STEP_MEDIA_IDX    = 14; // index of the "Select Image Option" step (wizard panel 2)
    var STEP_SUBMIT_IDX   = 22; // index of final "Submit" step (publishes via saveFinalData(), closes modal & redirects)

    // How often the panel watcher re-checks which wizard panel the user is actually on.
    var SYNC_INTERVAL_MS  = 400;

    /* ── Cover-image methods (Add Media step) ───────────────────
       The Add Media panel offers three methods and each one finishes by filling a
       DIFFERENT hidden field, so the guide has to watch the field belonging to the
       method the user actually picked. Values match the aiImage radio values in
       views/GenerateAIpopup/GenerateAIpopuphtml.php.

       Only AI_image has guidance copy written for it. The other two deliberately carry
       no waiting card rather than reusing copy that would be wrong for them ("press
       Generate… uses 1 image credit" is false for an upload) — they still advance the
       guide, they just leave the step's own card up until they do. Copy for those two
       is listed as outstanding in the report. */
    var MEDIA_METHODS = {
        AI_image: {
            done: '#AI-Image-uploaded-path',
            waitingTitle:   'Now create your image',
            waitingMessage: 'Click the <strong>Generate AI image</strong> button in the panel — we’ll continue automatically once your cover is ready. (Uses 1 image credit.)'
        },
        manually_promt_image: { done: '#AI-Prompt-Image-uploaded-path' },
        Manually_image:       { done: '#manually-image-uploaded-path'  }
    };

    /* ── Steps Definition ───────────────────────────────────── */
    var STEPS = [
        /* 0 */ {
            phase: 'modal', wizardStep: 0, target: '#seed_keyword',
            title: 'Enter your Keyword',
            message: 'This is the main topic of your article. We\u2019ve pre-filled a keyword based on your business details \u2014 feel free to adjust it.',
            position: 'right', advance: 'next-btn'
        },
        /* 1 */ {
            phase: 'modal', wizardStep: 0, target: '#niche_select',
            title: 'Pick your Business Niche',
            message: 'Choose the niche that best matches your business. This tailors the writing style, article structure, and cover-image look to your industry. We\u2019ve pre-selected one from your setup \u2014 adjust if needed.',
            position: 'right', advance: 'next-btn'
        },
        /* 2 */ {
            phase: 'modal', wizardStep: 0, target: '#seed_select',
            title: 'Choose Title Style',
            message: 'We\u2019ve selected <strong>Create Best Title from Keyword</strong> \u2014 the AI will craft an SEO-optimised headline for you. You can change this if you prefer.',
            position: 'right', advance: 'next-btn'
        },
        /* 3 */ {
            phase: 'modal', wizardStep: 0, target: '#cotnt_type',
            title: 'Set Your Tone of Voice',
            message: 'Choose the writing style for your audience. <em>Professional</em> suits service businesses; <em>Informational</em> works well for how-to guides.',
            position: 'right', advance: 'next-btn'
        },
        /* 4 */ {
            phase: 'modal', wizardStep: 0, target: '#reload',
            title: 'Generate your Title',
            message: 'Click the <strong>Generate</strong> button to let the AI create an optimised title from your keyword.',
            position: 'left', advance: 'click-target'
        },
        /* 5 */ {
            phase: 'modal', wizardStep: 0, target: '.step_one_approve_button',
            title: 'Approve the AI Title',
            message: 'Review the suggested title above. Happy with it? Click <strong>Approve</strong> to confirm and proceed.',
            position: 'right', advance: 'click-target'
        },
        /* 6 */ {
            phase: 'modal', wizardStep: 0, target: '#nextStepButton',
            title: 'Title Approved!',
            message: 'Great! Now click <strong>Next \u2192</strong> below to move on to the content settings.',
            position: 'left', advance: 'wizard-next', pollTarget: '#post_size'
        },
        /* 7 */ {
            phase: 'modal', wizardStep: 1, target: '#post_size',
            title: 'Article Length',
            message: 'Longer articles often rank better for competitive keywords. <em>Medium (1,200\u20132,400 words)</em> is a great starting point for most niches.',
            position: 'right', advance: 'next-btn'
        },
        /* 8 */ {
            phase: 'modal', wizardStep: 1, target: '#size',
            title: 'Point of View',
            message: 'Choose how the article addresses the reader. <em>Second Person (you, your)</em> is recommended for engaging, direct copy.',
            position: 'right', advance: 'next-btn'
        },
        /* 9 */ {
            phase: 'modal', wizardStep: 1, target: '#language',
            title: 'Content Language',
            message: 'Select the language and regional variant for your article. Match this to your target audience\u2019s location.',
            position: 'right', advance: 'next-btn'
        },
        /* 10 */ {
            phase: 'modal', wizardStep: 1, target: '#niche_fields_container',
            title: 'Add Niche Details',
            message: '(Optional) These prompts adapt to your niche. Add real specifics — pricing, a local detail, a recent result — to make the article concrete and locally relevant. Your business name and location come from Settings automatically.',
            position: 'right', advance: 'next-btn'
        },
        /* 11 */ {
            phase: 'modal', wizardStep: 1, target: '#call_to_action',
            title: 'Call to Action',
            message: '(Optional) Tell the AI what action you want readers to take \u2014 e.g. <em>Contact us for a free quote</em>.',
            position: 'right', advance: 'next-btn'
        },
        /* 12 */ {
            phase: 'modal', wizardStep: 1, target: '#cta_url',
            title: 'Call to Action URL',
            message: '(Optional) Add the URL where you want readers to go \u2014 e.g. your contact page or booking form. Leave blank if you don\u2019t need a link.',
            position: 'right', advance: 'next-btn'
        },
        /* 13 */ {
            phase: 'modal', wizardStep: 1, target: '#nextStepButton',
            title: 'Content Settings Done!',
            message: 'All content settings are saved. Click the <strong>Next \u2192</strong> button below to move on to image selection.',
            position: 'left', advance: 'wizard-next'
        },
        /* 14 */ {
            phase: 'modal', wizardStep: 2, target: 'label[for="AI_image"]',
            title: 'Select Image Option',
            message: 'Pick <strong>AI image from title</strong> — the quickest option. We’ll create a cover from your article title. You can switch methods anytime before generating.',
            position: 'right', advance: 'click-target', dock: true
        },
        /* 15 */ {
            phase: 'modal', wizardStep: 2, target: '#nextStepButton',
            title: 'Image Option Set',
            message: 'Your image preference has been saved. Click the <strong>Generate AI Post</strong> button below \u2014 the AI will write your article automatically.',
            position: 'left', advance: 'wizard-next', dock: true,
            wizardHint: '&#8595; Click the <strong>Generate AI Post &#8594;</strong> button below to continue'
        },
        /* 16 */ {
            phase: 'modal', wizardStep: 3, target: '#nextStepButton',
            title: 'Review & Approve',
            message: 'Your article is ready! Scroll up in this window to read it. When you\u2019re happy, click the <strong>Approve Content</strong> button to continue.',
            position: 'left', advance: 'wizard-next'
        },
        /* 17 */ {
            phase: 'modal', wizardStep: 4, target: '#meta_title',
            title: 'SEO Meta Title',
            message: 'This title appears in Google search results. Keep it under 60 characters. The AI has suggested one \u2014 you can edit it freely.',
            position: 'right', advance: 'next-btn'
        },
        /* 18 */ {
            phase: 'modal', wizardStep: 4, target: '#meta_descreption',
            title: 'Meta Description',
            message: 'A short description shown under your page title in Google. Keep it under 160 characters \u2014 make it compelling to increase clicks.',
            position: 'right', advance: 'next-btn'
        },
        /* 19 */ {
            phase: 'modal', wizardStep: 4, target: '#nextStepButton',
            title: 'SEO Details Done!',
            message: 'Happy with your SEO title and description? Click <strong>Next \u2192</strong> below to continue to your project name.',
            position: 'left', advance: 'wizard-next', pollTarget: '#modal_project_name'
        },
        /* 20 */ {
            phase: 'modal', wizardStep: 5, target: '#modal_project_name',
            title: 'Check the Project Name',
            message: 'We\u2019ve auto-filled an internal name from your keyword \u2014 it\u2019s for your reference only and won\u2019t be published. Give it a quick read, edit it if you like, then click <strong>Next \u2192</strong>.',
            position: 'bottom', advance: 'next-btn'
        },
        /* 21 */ {
            phase: 'modal', wizardStep: 5, target: '.category-selection-section',
            title: 'Assign a Category',
            message: 'Choose one or more categories to organise this post. <em>Improve SEO</em> is selected by default \u2014 you can pick others or create a new one below. When done, click <strong>Next \u2192</strong>.',
            position: 'top', advance: 'next-btn'
        },
        /* 22 */ {
            phase: 'modal', wizardStep: 5, target: '#nextStepButton',
            title: 'Publish your Article! \uD83C\uDF89',
            message: 'You\u2019re all set! Click the <strong>Submit</strong> button below to publish your first SEO-optimised article.',
            position: 'left', advance: 'final'
        }
    ];

    /* ── State ──────────────────────────────────────────────── */
    var currentStep  = -1;  // -1 = waiting for modal to open
    var $spotlight   = null;
    var $tooltip     = null;
    var _waiting     = false; // true while polling for generated content
    var _waitingFor  = -1;    // step index that started the current wait
    var _reposTmr    = null;
    var _syncTmr     = null;  // panel watcher (see syncGuideToWizardPanel)

    /* ─────────────────────────────────────────────────────────
       INIT
    ───────────────────────────────────────────────────────── */
    function init() {
        $spotlight = $('<div id="iseo-guide-spotlight"></div>').appendTo('body');
        $tooltip   = $('<div id="iseo-guide-tooltip"></div>').appendTo('body');
        $('body').addClass('iseo-guide-active');
        bindEvents();

        // Floor for step tracking — see syncGuideToWizardPanel().
        _syncTmr = setInterval(syncGuideToWizardPanel, SYNC_INTERVAL_MS);

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
       KEEP THE CARD BOUND TO THE STEP THE USER IS ACTUALLY ON

       Every advance in this file hangs off one specific DOM signal (a click on one
       element, one hidden field being filled). Miss any single one of them and
       showStep() is never called again — the card then shows that step's copy for the
       whole rest of the flow, which is what made the image guidance look "stuck" on
       every later step.

       The wizard keeps its own panel counter in #step_value (1-based; it is written on
       every advance/back by advanceSingleStep() in GenerateAIpopuphtml.php). Watching it
       gives the guide a signal it cannot miss: whenever the user is on a later panel
       than the card is describing, jump the card forward to that panel's first step.
       The per-step signals above still drive the fine-grained steps within a panel —
       this is the floor, not a replacement.
    ───────────────────────────────────────────────────────── */
    function firstStepForWizardPanel(panel) {
        for (var i = 0; i < STEPS.length; i++) {
            if (STEPS[i].wizardStep === panel) return i;
        }
        return -1;
    }

    // Shown while the article is being written. Reached two ways: the user clicks
    // "Generate AI Post" on the media step, or the panel watcher finds them already on
    // the content panel with nothing generated yet (they advanced the wizard themselves
    // while the guide was on an earlier step).
    function startArticleWait() {
        _waiting    = true;
        _waitingFor = STEP_GENERATE_IDX;
        showWaitingTooltip(
            'Generating your Article &#x23F3;',
            'Your article is being written by AI.<br><small>This may take 20–60 seconds — please wait.</small>',
            65
        );
        waitForContent('#showmydataindiv1', function () {
            if (_waiting && _waitingFor === STEP_GENERATE_IDX) {
                _waiting = false;
                showStep(STEP_APPROVE_IDX);
            }
        }, 90);
    }

    function syncGuideToWizardPanel() {
        if (currentStep < 0 || currentStep >= STEPS.length) return;

        // The one wait that legitimately runs while the wizard is already on the next
        // panel: clicking "Generate AI Post" advances the wizard to the content panel
        // and *then* generates. Superseding that card would claim the article is ready
        // while it is still being written.
        if (_waiting && _waitingFor === STEP_GENERATE_IDX) return;

        var sv = parseInt($('#step_value').val(), 10);
        if (isNaN(sv)) return;

        var panel = sv - 1; // #step_value is 1-based, STEPS[].wizardStep is 0-based
        if (panel <= STEPS[currentStep].wizardStep) return; // in sync, or user stepped back

        var target = firstStepForWizardPanel(panel);
        if (target <= currentStep) return;

        _waiting = false;

        // Landing on the content panel before the article exists: the user advanced the
        // wizard themselves, so generation is running right now. Show that, rather than
        // step 16's "Your article is ready!".
        var article = $('#showmydataindiv1').html();
        if (target === STEP_APPROVE_IDX && (!article || article.trim().length <= 100)) {
            currentStep = STEP_GENERATE_IDX;
            startArticleWait();
            return;
        }

        showStep(target);
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
        _waitingFor = -1;
        currentStep = index;
        var step    = STEPS[index];
        var $target = $(step.target);

        // Remove all previous highlights
        $('.iseo-guide-highlight').removeClass('iseo-guide-highlight');

        // Update form-focus classes
        updateFormFocus(step);

        // Build tooltip content
        buildTooltip(step, index);

        // For wizard-next steps with a pollTarget: watch for the next panel to appear
        // and auto-trigger the hidden guide Next button — no click event dependency.
        if (step.pollTarget) {
            var pollIdx = index;
            waitForVisible(step.pollTarget, function () {
                if (currentStep === pollIdx) {
                    $tooltip.find('.iseo-guide-btn-next').trigger('click');
                }
            }, 10);
        }

        if (step.phase === 'page' || step.phase === 'form') {
            $spotlight.show();
            if ($target.length) {
                $target[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                setTimeout(function () {
                    positionSpotlight($target);
                    placeTooltip(step, $target);
                    $tooltip.show();
                }, 180);
            }
        } else {
            // Inside Bootstrap modal — use blue glow instead of spotlight
            $spotlight.hide();
            if ($target.length) {
                $target.addClass('iseo-guide-highlight');
                scrollWithinModal($target, function () {
                    placeTooltip(step, $target);
                    $tooltip.show();
                });
            } else {
                placeTooltip(step, $target);
                $tooltip.show();
            }
        }
    }

    /* ─────────────────────────────────────────────────────────
       PLACE TOOLTIP — anchored beside the target, or docked

       Anchoring works when the target sits in a single-column form. On the Add Media
       panel the three method cards are a 3-up grid, so a card anchored beside the first
       one lands squarely on top of the other two and the Generate button. Those steps
       set dock:true and the card goes to a fixed corner instead, out of the workflow.
    ───────────────────────────────────────────────────────── */
    function placeTooltip(step, $target) {
        if (step && step.dock) {
            dockTooltip();
        } else {
            positionTooltip($target, step ? step.position : 'top');
        }
    }

    function dockTooltip() {
        // Drop the inline coordinates a previous anchored step left behind so the
        // [data-pos="docked"] rule in onboarding-guide.css can take over.
        $tooltip.css({ top: '', left: '', width: '' }).attr('data-pos', 'docked');

        // The wizard panel is 92% wide (max 1470px), so there is no gutter beside it to
        // park in — the card has to sit over the panel. Top-right, tucked under the
        // panel's own header, keeps it clear of the method cards' content and of the
        // header's close button. Measured rather than hard-coded because the panel's
        // offset moves with the admin bar, notices and the viewport.
        var $modal  = $('#exampleModal1');
        var $header = $modal.find('.singlepost-title');
        if (!$modal.length || !$modal[0] || !$modal[0].getBoundingClientRect) return;

        var m   = $modal[0].getBoundingClientRect();
        var hdr = ($header.length && $header[0]) ? $header[0].getBoundingClientRect() : null;
        if (!m.width) return; // panel not laid out yet — keep the CSS fallback corner

        var ttW  = 300;
        var ttH  = $tooltip.outerHeight(true) || 230;
        var gap  = 16;
        var top  = (hdr && hdr.height ? hdr.bottom : m.top) + gap;
        var left = m.right - ttW - gap;

        $tooltip.css({
            top:   Math.max(10, Math.min(top,  window.innerHeight - ttH  - 10)) + 'px',
            left:  Math.max(10, Math.min(left, window.innerWidth  - ttW  - 10)) + 'px',
            width: ttW + 'px'
        });
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
        // Total = STEPS.length guide steps + 1 card-choice step (handled by index.php).
        // Derived, never hard-coded: steps have been added to STEPS since this counter was
        // written and a literal total silently drifts out of date.
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
            var hintText = step.wizardHint || '&#8595; Click the <strong>Next &#8594;</strong> button below to continue';
            html += '<div class="iseo-guide-wizard-hint">' + hintText + '</div>';
            if (step.pollTarget) {
                // Hidden button — auto-triggered by poll when the modal panel changes
                html += '<button class="iseo-guide-btn-next" type="button" style="display:none" aria-hidden="true">Next &#8594;</button>';
            }
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
       POSITION TOOLTIP  (with auto-flip when primary side overflows)
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
        var vw  = window.innerWidth;
        var vh  = window.innerHeight;
        var opposites = { right: 'left', left: 'right', top: 'bottom', bottom: 'top' };

        function calcPos(pos) {
            switch (pos) {
                case 'bottom': return { top: r.bottom + gap,                  left: r.left + r.width / 2 - ttW / 2 };
                case 'top':    return { top: r.top - ttH - gap,               left: r.left + r.width / 2 - ttW / 2 };
                case 'right':  return { top: r.top + r.height / 2 - ttH / 2, left: r.right + gap };
                default:       return { top: r.top + r.height / 2 - ttH / 2, left: r.left - ttW - gap };
            }
        }
        function fits(p) {
            return p.left >= 10 && p.left + ttW <= vw - 10 && p.top >= 10 && p.top + ttH <= vh - 10;
        }

        var pos = calcPos(position);
        if (!fits(pos)) {
            var alt = calcPos(opposites[position] || 'top');
            if (fits(alt)) {
                pos      = alt;
                position = opposites[position] || 'top';
            }
        }

        var top  = Math.max(10, Math.min(pos.top,  vh - ttH - 10));
        var left = Math.max(10, Math.min(pos.left, vw - ttW - 10));

        $tooltip.css({ top: top + 'px', left: left + 'px', width: ttW + 'px' });
        $tooltip.attr('data-pos', position);
    }

    /* ─────────────────────────────────────────────────────────
       SCROLL WITHIN MODAL (keep highlighted field visible)
       callback fires after scroll completes (or immediately if no scroll needed)
    ───────────────────────────────────────────────────────── */
    function scrollWithinModal($target, callback) {
        var cb = callback || $.noop;

        if (!$target.length) {
            setTimeout(cb, 60);
            return;
        }

        var $modal = $('#exampleModal1');

        // New single-post modal: no .modal-body — scroll is on the modal element itself.
        // Use native scrollIntoView which handles any scrollable ancestor correctly.
        if ($modal.length && $.contains($modal[0], $target[0])) {
            // #nextStepButton lives in .btn-dev which is outside .improveseo-sections,
            // so it is always visible — no scroll needed for those steps.
            var $sections = $modal.find('.improveseo-sections');
            if ($sections.length && $.contains($sections[0], $target[0])) {
                $target[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            setTimeout(cb, 200);
            return;
        }

        // Legacy fallback: Bootstrap .modal-body wrapper (old form).
        var $body = $modal.find('.modal-body');
        if (!$body.length || !$.contains($body[0], $target[0])) {
            setTimeout(cb, 60);
            return;
        }
        var offset = $target.offset().top - $body.offset().top + $body.scrollTop() - 60;
        $body.stop(true).animate({ scrollTop: Math.max(0, offset) }, 180, cb);
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
            if (currentStep < 0) return;
            // The final "Submit" step publishes via saveFinalData(), which hides the
            // modal and redirects — let that happen without touching the guide.
            if (currentStep >= STEP_SUBMIT_IDX) return;
            // Modal dismissed early (before publishing) — hide the guide UI.
            $tooltip.hide();
            $spotlight.hide();
            $('.iseo-guide-highlight').removeClass('iseo-guide-highlight');
        });

        /* ── Wizard Next button ─────────────────────────────── */
        // For wizard-next steps the user must click the real Next button;
        // guide advances when we detect that click.
        // Steps with pollTarget are handled by the poll started in showStep — skip them here.
        $(document).on('click.iseoguide', '#nextStepButton', function () {
            if (currentStep < 0 || currentStep >= STEPS.length) return;
            var step = STEPS[currentStep];
            if (step.advance !== 'wizard-next') return;
            if (step.pollTarget) return; // poll in showStep handles this step

            if (currentStep === STEP_GENERATE_IDX) {
                // User clicked "Generate AI Post".
                // The popup's own handler immediately relabels #nextStepButton to "Approve Content"
                // and fires #generateapivalue — same as the normal form flow.
                // The guide just removes the highlight and shows the waiting tooltip;
                // the button remains visible exactly as in the normal form.
                // When content arrives, showStep(STEP_APPROVE_IDX) adds the glow to the now-labelled "Approve Content".
                setTimeout(function () {
                    $('.iseo-guide-highlight').removeClass('iseo-guide-highlight');
                }, 0);
                startArticleWait();
            } else {
                setTimeout(function () { showStep(currentStep + 1); }, 350);
            }
        });

        /* ── Step 4: click #reload → wait for AI title ──────── */
        $(document).on('click.iseoguide', '#reload', function () {
            if (currentStep !== 4) return;
            _waiting    = true;
            _waitingFor = 4;
            showWaitingTooltip(
                'Generating your AI Title &#x23F3;',
                'The AI is crafting an optimised title from your keyword \u2014 please wait.',
                30
            );
            waitForValue('#maintitlearea', function () {
                if (_waiting && currentStep === 4) {
                    _waiting = false;
                    showStep(5); // → approve title
                }
            }, 30);
        });

        /* ── Step 5: approve title checkbox ─────────────────── */
        $(document).on('change.iseoguide', '#checkbox_need', function () {
            if (currentStep !== 5) return;
            if ($(this).is(':checked')) {
                setTimeout(function () { showStep(6); }, 200); // → title-next (wizard-next glow on Next button)
            }
        });

        /* ── Step 14: pick a cover-image method ─────────────── */
        // Bound to the whole radio group, not just #AI_image. The Add Media panel offers
        // three methods and this step is a click-target step — it has no guide Next
        // button, so the only way out is one of these signals firing. Listening to
        // #AI_image alone meant picking "AI Image - Custom Prompt" or "Upload your own"
        // emitted nothing at all: the guide sat here permanently, which is why Next
        // appeared to do nothing after Step 16 and why every later step kept showing
        // this step's image copy.
        $(document).on('change.iseoguide', '#exampleModal1 input[name="aiImage"]', function () {
            if (currentStep !== STEP_MEDIA_IDX) return;

            var method = MEDIA_METHODS[this.value];
            if (!method) return;

            // If this method already produced an image (re-visit), advance immediately.
            if ($(method.done).val()) {
                setTimeout(function () { showStep(STEP_MEDIA_IDX + 1); }, 200);
                return;
            }

            _waiting    = true;
            _waitingFor = STEP_MEDIA_IDX;
            if (method.waitingTitle) {
                showWaitingTooltip(method.waitingTitle, method.waitingMessage, 60);
            }
            // Resolves when the method's own handler fills its hidden path field.
            waitForValue(method.done, function () {
                if (_waiting && currentStep === STEP_MEDIA_IDX) {
                    _waiting = false;
                    showStep(STEP_MEDIA_IDX + 1); // → media-next (wizard-next)
                }
            }, 90);
        });

        /* ── Cover image generation started ─────────────────── */
        // Until now the card kept showing the pre-generation "Now create your image"
        // prompt for the whole time the image was being generated, so it read as stale
        // and told the user to press a button they had already pressed. Both generate
        // buttons (AI-from-title and custom-prompt) swap it for a progress card; the
        // waitForValue() poll started when the method was picked still does the advancing.
        $(document).on('click.iseoguide', '#AIrefreshOption button, #generate_i_image', function () {
            if (currentStep !== STEP_MEDIA_IDX) return;
            showWaitingTooltip(
                'Generating your cover image &#x23F3;',
                'Your cover image is being generated.<br><small>This may take 20–60 seconds — please wait.</small>',
                65
            );
        });

        /* ── Reposition on resize / scroll ──────────────────── */
        $(window).on('resize.iseoguide scroll.iseoguide', function () {
            clearTimeout(_reposTmr);
            _reposTmr = setTimeout(function () {
                if (currentStep < 0 || currentStep >= STEPS.length) return;
                var step    = STEPS[currentStep];
                var $target = $(step.target);
                if (step.phase !== 'modal') positionSpotlight($target);
                placeTooltip(step, $target);
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
        clearInterval(_syncTmr);
    }

    /* ─────────────────────────────────────────────────────────
       START
    ───────────────────────────────────────────────────────── */
    $(document).ready(function () {
        init();
    });

}(jQuery));
