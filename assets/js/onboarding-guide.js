/**
 * ImproveSEO Onboarding Guide
 *
 * Step-by-step spotlight + tooltip overlay for the single post creation flow.
 * Only activates when window.iseoGuideConfig.active === true.
 *
 * The numbering the user sees on this page is this file's own: STEPS index 0 is
 * "Step 1 of N". STEPS.length is 26, but N (see visibleStepTotal()) is usually less:
 * a few cards are marked optional: true — they describe a field the wizard only
 * shows in some configurations (the "Other" niche box; the custom-length box, which
 * is permanently hidden markup and never shows at all). Which of those apply is
 * decided ONCE at init, from each field's controlling value — see
 * isOptionalStepApplicable() — not from on-screen geometry as the flow happens to
 * reach each one. N is therefore fixed for the whole journey and X is contiguous
 * 1..N: deciding lazily, panel by panel, was what previously made a user see Step 2
 * followed by Step 4 (a skipped card still uncounted) or watched "of 26" quietly
 * become "of 24" mid-flow. The card-choice page (views/posting/index.php) shows an
 * unnumbered "Getting started" card before the user ever reaches this screen, so it
 * isn't part of N.
 *
 * Every page load starts on Step 1: the wizard keeps no state across a reload, so
 * neither does the guide — see startStep().
 *
 * The indices used in the logic below (STEP_MEDIA_IDX etc.) are all resolved by key
 * via stepIndexByKey() — never written as a literal — so inserting or reordering a
 * card here cannot silently repoint them at the wrong step. Two early handlers
 * (#reload, #checkbox_need) were once written with literal indices and broke
 * silently the last time a card was inserted above them; they're key-based now too.
 *
 * The panel watcher (syncGuideToWizardPanel) is the floor under all of this: it
 * reads the wizard's own #step_value and follows it in BOTH directions — forward to
 * a later panel's first (visible) card, backward on Previous to an earlier panel's
 * last (visible) card — so the card can never get stuck describing a panel the user
 * has left, in either direction.
 *
 * Wizard-step 0 (Keyword & Post Title):
 *    0  seed-keyword      (modal) #seed_keyword              next-btn
 *    1  niche             (modal) #niche_select              next-btn      ← business niche, pre-selected from setup
 *    2  niche-other       (modal) #niche_other                next-btn      ← optional: only when niche === "Other"
 *    3  title-type        (modal) #seed_select               next-btn      ← seed_option2 pre-selected
 *    4  tone              (modal) #cotnt_type                next-btn      ← select before generating
 *    5  title-generate    (modal) #reload                    click-target  ← click to generate AI title
 *    6  read-title        (modal) #maintitlearea              next-btn      ← optional: only when the field is actually on screen
 *    7  title-approve     (modal) .step_one_approve_button   click-target  ← click Approve checkbox
 *    8  title-next        (modal) #nextStepButton            wizard-next   ← glow Next after approval
 * Wizard-step 1 (Content Settings):
 *    9  article-size      (modal) #post_size                 next-btn
 *   10  custom-length     (modal) #post_size_select           next-btn      ← optional: only when length is set to Custom
 *   11  point-of-view     (modal) #size                       next-btn
 *   12  language          (modal) #language                   next-btn
 *   13  niche-details     (modal) #niche_fields_container      next-btn      ← dynamic per-niche fields
 *   14  call-to-action    (modal) #call_to_action              next-btn      ← fill in CTA text, click guide’s Next
 *   15  cta-url           (modal) #cta_url                     next-btn      ← optional CTA URL
 *   16  cta-next          (modal) #nextStepButton              wizard-next   ← click modal Next to go to media
 * Wizard-step 2 (Add Media):
 *   17  media-select      (modal) label[for="AI_image"]        click-target  ← per-method: pick → work → that method's own completion field (MEDIA_METHODS)
 *   18  media-next        (modal) #nextStepButton              wizard-next   ← "Generate AI Post" label; generation triggered automatically
 * Wizard-step 3 (Generate AI Content):
 *   19  approve-content   (modal) #nextStepButton              wizard-next   ← "Approve Content" label
 * Wizard-step 4 (Meta Title & Description):
 *   20  meta-title        (modal) #meta_title                  next-btn
 *   21  meta-desc         (modal) #meta_descreption             next-btn
 *   22  meta-next         (modal) #nextStepButton               wizard-next   ← advances to Project Name panel (pollTarget)
 * Wizard-step 5 (Project Name & Categories):
 *   23  project-name      (modal) #modal_project_name           next-btn
 *   24  categories        (modal) .category-selection-section   next-btn
 *   25  submit            (modal) #nextStepButton                wizard-next   ← closes modal, fills form; guide transitions to review steps
 * Post Review (create-post form page):
 *   26  review-project    (page)  .PostForm__name-wrap           next-btn      ← review project name
 *   27  review-title      (page)  .PostForm__title-wrap          next-btn      ← review post title
 *   28  review-content    (page)  .PostForm__body-wrap           next-btn      ← review article content
 *   29  review-publish    (page)  #post_form_buttons             final         ← Done → submits form
 */
(function ($) {
    'use strict';

    /* ── Guard ──────────────────────────────────────────────── */
    if (!window.iseoGuideConfig || !window.iseoGuideConfig.active) return;

    /* ── Constants ──────────────────────────────────────────── */
    // The step indices the logic below refers to are derived from STEPS by key, further
    // down — never written as literals. Adding a step used to silently repoint them at
    // the wrong card.

    // How often the panel watcher re-checks which wizard panel the user is actually on.
    var SYNC_INTERVAL_MS  = 400;

    /* ── Cover-image methods (Add Media step) ───────────────────
       The Add Media panel offers three methods and they are three different actions,
       not three routes to the same one. Each entry therefore carries:

         done      the hidden field that method's own handler fills — its ONLY true
                   completion signal (the same field that ends the plugin's
                   "we're getting your AI image ready" loading screen)
         startOn   the control that starts that method's wait, and the event it fires
         select*   the card shown the moment the method is picked
         busy*     the card shown while that method is working
         done*     the card shown once `done` fills — see applyMediaDoneState()

       Values match the aiImage radio values in views/GenerateAIpopup/GenerateAIpopuphtml.php.
       Nothing here is shared between methods: the upload path must never claim an
       image is being generated or that a credit is spent, because neither is true. */
    var MEDIA_METHODS = {
        AI_image: {
            // custom-plugin-script.js refreshAIImage() → fills on success
            done: '#AI-Image-uploaded-path',
            startOn: { target: '#AIrefreshOption button', event: 'click' },
            selectTitle:   'AI Image From Title',
            selectMessage: 'We’ll write a prompt from your title and create a cover. Press <strong>Generate AI image</strong>. Uses 1 image credit.',
            busyTitle:     'Creating your cover image &#x23F3;',
            busyMessage:   'Creating your cover image — this usually takes 20–60 seconds. Please wait.',
            doneTitle:     'Cover Image Ready',
            doneMessage:   'Your cover image is saved. Click <strong>{button}</strong> below — the AI will write your article automatically.'
        },
        manually_promt_image: {
            // custom-plugin-script.js #generate_i_image handler → fills on success
            done: '#AI-Prompt-Image-uploaded-path',
            startOn: { target: '#generate_i_image', event: 'click' },
            selectTitle:   'AI Image - Custom Prompt',
            selectMessage: 'Describe the cover image you want, then press <strong>Generate AI Image</strong>. Uses 1 image credit.',
            busyTitle:     'Creating your cover image &#x23F3;',
            busyMessage:   'Creating your cover image — please wait.',
            doneTitle:     'Cover Image Ready',
            doneMessage:   'Your cover image is saved. Click <strong>{button}</strong> below — the AI will write your article automatically.'
        },
        Manually_image: {
            // custom-plugin-script.js #upload-image-button change handler → fills on upload
            done: '#manually-image-uploaded-path',
            startOn: { target: '#upload-image-button', event: 'change' },
            selectTitle:   'Upload your own',
            selectMessage: 'Choose an image from your computer to use as the cover. Nothing is generated and no credit is used.',
            busyTitle:     'Uploading your image…',
            busyMessage:   'Uploading your image…',
            // Nothing was generated, so this must not read like it was (no "Generate"
            // language about the IMAGE). The button itself still legitimately reads
            // "Generate AI Post" the first time through — that names the ARTICLE step,
            // not another image generation — so it comes from {button}, never
            // hardcoded, and reads "Next" instead if the user returns here later with
            // an article already generated (updateButtonText() in
            // GenerateAIpopuphtml.php).
            doneTitle:     'Image Uploaded',
            doneMessage:   'Your image is uploaded and saved as the cover. Click <strong>{button}</strong> below to continue.'
        }
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
        /* 1b */ {
            // Only exists when the niche is "Other" — see optional/skip in showStep().
            phase: 'modal', wizardStep: 0, target: '#niche_other', optional: true,
            title: 'Name your Niche',
            message: 'You picked <strong>Other</strong>, so tell us what your business actually does — a few words is enough (e.g. <em>mobile dog grooming</em>). The AI writes to this.',
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
            key: 'title-generate', phase: 'modal', wizardStep: 0, target: '#reload',
            title: 'Generate your Title',
            message: 'Click the <strong>Generate</strong> button to let the AI create an optimised title from your keyword.',
            position: 'left', advance: 'click-target'
        },
        /* 4b */ {
            // The generated title is editable before approval — the guide used to jump
            // straight from Generate to Approve and never said so.
            key: 'read-title', phase: 'modal', wizardStep: 0, target: '#maintitlearea', optional: true,
            title: 'Read your Title',
            message: 'Here is the AI’s headline. Edit it right here if you want to change a word — what you leave in this box is what gets published.',
            position: 'right', advance: 'next-btn'
        },
        /* 5 */ {
            key: 'title-approve', phase: 'modal', wizardStep: 0, target: '.step_one_approve_button',
            title: 'Approve the AI Title',
            message: 'Happy with the title? Click <strong>Approve</strong> to confirm it and unlock the next step.',
            position: 'right', advance: 'click-target'
        },
        /* 6 */ {
            key: 'title-next', phase: 'modal', wizardStep: 0, target: '#nextStepButton',
            title: 'Title Approved!',
            message: 'Great! Now click <strong>{button}</strong> below to move on to the content settings.',
            position: 'left', advance: 'wizard-next', pollTarget: '#post_size'
        },
        /* 7 */ {
            phase: 'modal', wizardStep: 1, target: '#post_size',
            title: 'Article Length',
            message: 'Longer articles often rank better for competitive keywords. <em>Medium (1,200\u20132,400 words)</em> is a great starting point for most niches.',
            position: 'right', advance: 'next-btn'
        },
        /* 7b */ {
            // Only shown when the length dropdown is set to a custom range.
            phase: 'modal', wizardStep: 1, target: '#post_size_select', optional: true,
            title: 'Your Custom Length',
            message: 'This is the word range your custom choice works out to. Change the dropdown above if it is not what you wanted.',
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
            message: 'All content settings are saved. Click <strong>{button}</strong> below to move on to image selection.',
            position: 'left', advance: 'wizard-next'
        },
        /* 14 */ {
            key: 'media-select', phase: 'modal', wizardStep: 2, target: 'label[for="AI_image"]',
            title: 'Select Image Option',
            message: 'Pick <strong>AI image from title</strong> — the quickest option. We’ll create a cover from your article title. You can switch methods anytime before generating.',
            position: 'right', advance: 'click-target', dock: true
        },
        /* 15 */ {
            key: 'media-next', phase: 'modal', wizardStep: 2, target: '#nextStepButton',
            title: 'Cover Image Ready',
            // Reached only once the chosen method actually finished (image generated, or
            // file uploaded). Fallback only \u2014 applyMediaDoneState() overwrites this with
            // the method-specific copy (MEDIA_METHODS[x].doneTitle/doneMessage) every time
            // this step renders; this generic wording is what would show if the method
            // could somehow not be determined.
            message: 'Your cover image is saved. Click <strong>{button}</strong> below \u2014 the AI will write your article automatically.',
            position: 'left', advance: 'wizard-next', dock: true
            // No custom wizardHint: it used to hardcode "Generate AI Post" here too,
            // which showed on the upload path's card as well (nothing was generated).
            // Falls through to buildTooltip()'s own {button}-tokenized default hint,
            // same as every other wizard-next step.
        },
        /* 16 */ {
            key: 'approve-content', phase: 'modal', wizardStep: 3, target: '#nextStepButton',
            title: 'Review & Approve',
            message: 'Your article is ready \u2014 scroll up in this window to read it. Not quite right? Use <strong>Re-Generate AI Post</strong> to write it again. Happy with it? Click <strong>{button}</strong> below to continue.',
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
            message: 'Tick any categories this post belongs to. Need a new one? Type it in the box below and press <strong>Add</strong> \u2014 it is created and ticked for you. When done, click <strong>Next \u2192</strong>.',
            position: 'top', advance: 'next-btn'
        },
        /* 22 */ {
            key: 'submit', phase: 'modal', wizardStep: 5, target: '#nextStepButton',
            title: 'Publish your Article! \uD83C\uDF89',
            // {button} is replaced with the wizard button's live label — see
            // applyCopyTokens(). Hard-coding it here is what produced a card naming a
            // button the user could not find on screen.
            message: 'You\u2019re all set! Click <strong>{button}</strong> below to publish your first article.',
            position: 'left', advance: 'wizard-next'
        },

        /* ── Post Review Steps (create-post form page) ─────────────────
           After the wizard modal closes and fills the form, these four steps
           walk the user through reviewing the filled-in form before publishing.
           NOTE: copy strings below are placeholders — reword as needed.       */
        /* 23 */ {
            key: 'review-project', phase: 'page', target: '.PostForm__name-wrap',
            title: 'Check your project name',
            message: 'This is an internal name for your reference. Review it and edit if you\u2019d like, then click <strong>Next \u2192</strong>.',
            position: 'bottom', advance: 'next-btn'
        },
        /* 24 */ {
            key: 'review-title', phase: 'page', target: '.PostForm__title-wrap',
            title: 'Review your post title',
            message: 'This is the title readers and search engines will see. Make sure it reads well, then click <strong>Next \u2192</strong>.',
            position: 'bottom', advance: 'next-btn'
        },
        /* 25 */ {
            key: 'review-content', phase: 'page', target: '.PostForm__body-wrap',
            title: 'Review your content',
            message: 'Read through your article. Edit anything you\u2019d like directly here, then click <strong>Next \u2192</strong>.',
            position: 'top', advance: 'next-btn'
        },
        /* 26 */ {
            key: 'review-publish', phase: 'page', target: '#post_form_buttons',
            title: 'You\u2019re ready to publish! \uD83C\uDF89',
            message: 'Click <strong>Create \u0026amp; Publish Post</strong> to publish, or <strong>Save As Draft</strong> to finish later. <strong>Post preview</strong> shows how it will look.',
            position: 'top', advance: 'final'
        }
    ];

    /* ── Step indices, derived from STEPS ───────────────────────
       Keyed lookups so inserting or reordering a card cannot desynchronise the media /
       generate / publish logic from the cards it drives. */
    function stepIndexByKey(key) {
        for (var i = 0; i < STEPS.length; i++) {
            if (STEPS[i].key === key) return i;
        }
        return -1;
    }

    var STEP_MEDIA_IDX    = stepIndexByKey('media-select');    // "Select Image Option"
    var STEP_GENERATE_IDX = stepIndexByKey('media-next');      // where "Generate AI Post" is clicked
    var STEP_APPROVE_IDX  = stepIndexByKey('approve-content'); // "Approve Content"
    var STEP_SUBMIT_IDX   = stepIndexByKey('submit');          // wizard Submit — now wizard-next, not final
    var STEP_REVIEW_START  = stepIndexByKey('review-project');  // first page-review step
    var STEP_REVIEW_END    = stepIndexByKey('review-publish');  // final step (Done → submit form)

    var STEP_TITLE_GENERATE_IDX = stepIndexByKey('title-generate'); // click #reload
    var STEP_READ_TITLE_IDX     = stepIndexByKey('read-title');     // editable-title card (skipped if hidden)
    var STEP_TITLE_APPROVE_IDX  = stepIndexByKey('title-approve');  // approve checkbox
    var STEP_TITLE_NEXT_IDX     = stepIndexByKey('title-next');     // glow real Next after approval

    /* ── Where a page load starts ───────────────────────────────
       Always Step 1 — the guide has no state that outlives the page, by design.

       The wizard it narrates has none either: #step_value is a plain hidden input that
       renders as 1 (GenerateAIpopuphtml.php:936), every field comes back empty or
       re-prefilled from Settings, and the generated title and article live only in the
       DOM. So a reload genuinely puts the user back at the first panel with a blank
       form. A card restored to "Step 3" over that form describes work the user has to
       do again — the card must restart exactly when the thing it points at restarts.

       Carrying the step in localStorage was tried and removed: it made a reload open on
       whatever step the previous run had reached, which is the "reload lands on Step 2"
       report. If the wizard ever learns to persist a draft, resume belongs here — keyed
       off that draft, not off the guide's own counter. */
    function startStep() {
        return 0;
    }

    // Which wizard panel the page has actually rendered (0-based). #step_value is the
    // wizard's own 1-based counter and defaults to 1, so a page load reports panel 0.
    function currentWizardPanel() {
        var sv = parseInt($('#step_value').val(), 10);
        return isNaN(sv) ? 0 : Math.max(0, sv - 1);
    }

    /* ── State ──────────────────────────────────────────────── */
    var currentStep  = -1;  // -1 = waiting for modal to open
    var $spotlight   = null;
    var $tooltip     = null;
    var _waiting     = false; // true while polling for generated content
    var _waitingFor  = -1;    // step index that started the current wait
    var _reposTmr    = null;
    var _syncTmr     = null;  // panel watcher (see syncGuideToWizardPanel)
    var _mediaWaitTimer = null; // the ACTIVE per-method poll — see startMediaWait()

    /* ─────────────────────────────────────────────────────────
       INIT
    ───────────────────────────────────────────────────────── */
    function init() {
        // Before anything renders: the "Step X of N" total has to be right from Step 1,
        // so every optional card is decided now, not as the flow happens to reach it.
        computeStepCounting();

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
                    setTimeout(function () { showStep(startStep()); }, 300);
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

    // Where a Previous click into an EARLIER panel should land: that panel's LAST
    // card, not its first — the user is returning to wherever they left off, not
    // starting the panel over. Walks past a hidden optional step the same way
    // showStep() does going forward, just backward and bounded to this panel.
    function lastStepForWizardPanel(panel) {
        var i;
        for (i = STEPS.length - 1; i >= 0; i--) {
            if (STEPS[i].wizardStep === panel) break;
        }
        while (i >= 0 && STEPS[i].wizardStep === panel &&
               STEPS[i].optional && !stepCounts(i)) {
            i--;
        }
        return (i >= 0 && STEPS[i].wizardStep === panel) ? i : firstStepForWizardPanel(panel);
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

        // Page-review steps live outside the wizard modal — no panel to sync.
        if (STEPS[currentStep].wizardStep === undefined) return;

        // The one wait that legitimately runs while the wizard is already on the next
        // panel: clicking "Generate AI Post" advances the wizard to the content panel
        // and *then* generates. Superseding that card would claim the article is ready
        // while it is still being written.
        if (_waiting && _waitingFor === STEP_GENERATE_IDX) return;

        var sv = parseInt($('#step_value').val(), 10);
        if (isNaN(sv)) return;

        var panel     = sv - 1; // #step_value is 1-based, STEPS[].wizardStep is 0-based
        var cardPanel = STEPS[currentStep].wizardStep;
        if (panel === cardPanel) return; // in sync

        // A pending wait belongs to the panel the card was just describing — Previous
        // or Next both invalidate it. The field it was watching can still fill in
        // later; nothing is left listening for it on the step we're leaving.
        _waiting    = false;
        _waitingFor = -1;

        if (panel < cardPanel) {
            // User clicked Previous: follow the wizard back to that earlier panel's
            // last card. Nothing to wait for going backward — a plain re-render, not
            // the two-signal dance forward sync needs.
            var back = lastStepForWizardPanel(panel);
            if (back === -1 || back >= currentStep) return;
            showStep(back);
            return;
        }

        var target = firstStepForWizardPanel(panel);
        if (target <= currentStep) return;

        // Landing on the content panel before the article exists: the user advanced the
        // wizard themselves, so generation is running right now. Show that, rather than
        // approve-content's "Your article is ready!".
        var article = $('#showmydataindiv1').html();
        if (target === STEP_APPROVE_IDX && (!article || article.trim().length <= 100)) {
            currentStep = STEP_GENERATE_IDX;
            startArticleWait();
            return;
        }

        showStep(target);
    }

    /* ─────────────────────────────────────────────────────────
       WILL THIS OPTIONAL STEP EVER BE SHOWN?

       Decided from the field's CONTROLLING value, not its on-screen geometry. Geometry
       is only meaningful once that field's own panel is the active one — deciding "is
       it hidden" step-by-step as the flow reached each panel is what made the "Step X
       of N" total drift as the user progressed (N looked stable only until the next
       optional step was reached, then dropped). Every controlling value here is set at
       page load (server pre-fill / static markup) and doesn't depend on which panel is
       currently visible, so every optional step can be — and is — decided once, up
       front, in computeStepCounting() before Step 1 ever shows. N is then fixed for the
       whole journey, per fix.md Issue 4.
    ───────────────────────────────────────────────────────── */
    function isOptionalStepApplicable(step) {
        switch (step.target) {
            case '#niche_other':
                return $('#niche_select').val() === 'other';
            case '#post_size_select':
                // GenerateAIpopuphtml.php:1143 — style="display:none !important" in the
                // markup itself, with no dropdown option or script that ever clears it.
                // Permanently hidden; never worth a card.
                return false;
            default:
                // e.g. read-title (#maintitlearea): not actually conditional in
                // practice, the field is always on screen once a title exists.
                return true;
        }
    }

    // Decided once, at init — see isOptionalStepApplicable(). Every later lookup
    // (the walk-past-hidden logic, the counters) reads this same fixed answer, so the
    // total can never re-derive a different one mid-flow.
    var _stepCountedCache = {};
    function computeStepCounting() {
        for (var i = 0; i < STEPS.length; i++) {
            _stepCountedCache[i] = !STEPS[i].optional || isOptionalStepApplicable(STEPS[i]);
        }
    }

    // Whether a step counts toward the "Step X of N" numbering AND toward what
    // showStep()'s walk-past-hidden loop treats as present. Always resolved by
    // computeStepCounting() before first use.
    function stepCounts(index) {
        return _stepCountedCache[index];
    }

    function visibleStepNumber(index) {
        var n = 0;
        for (var i = 0; i <= index; i++) {
            if (stepCounts(i)) n++;
        }
        return n;
    }

    function visibleStepTotal() {
        var n = 0;
        for (var i = 0; i < STEPS.length; i++) {
            if (stepCounts(i)) n++;
        }
        return n;
    }

    /* ─────────────────────────────────────────────────────────
       COPY TOKENS

       {button} → whatever the wizard's own Next/Approve/Submit button says right now.
       The wizard relabels #nextStepButton per panel (updateButtonText() in
       GenerateAIpopuphtml.php), and hard-coding those labels here is what produced a
       card telling the user to click a button that was not on screen under that name.
    ───────────────────────────────────────────────────────── */
    function wizardButtonLabel() {
        var $btn = $('#nextStepButton');
        if (!$btn.length) return 'Next';
        var text = $.trim($btn.text());
        return text || 'Next';
    }

    function applyCopyTokens(text) {
        if (typeof text !== 'string' || text.indexOf('{button}') === -1) return text;
        return text.split('{button}').join(wizardButtonLabel());
    }

    /* ─────────────────────────────────────────────────────────
       SHOW STEP
    ───────────────────────────────────────────────────────── */
    function showStep(index) {
        if (index >= STEPS.length) {
            destroyGuide();
            return;
        }

        // Steps marked optional describe a field the wizard only shows in some
        // configurations — the "Other" niche box, the custom article length. Walk past
        // any that are not on screen rather than pointing at nothing: a click-target or
        // next-btn step whose field is hidden is exactly how this guide used to stall.
        // Bounded by STEPS.length, so a run of hidden steps ends the guide rather than
        // recursing forever.
        var guard = 0;
        while (index < STEPS.length && STEPS[index].optional &&
               !stepCounts(index) && guard++ < STEPS.length) {
            index++;
        }
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

        // Docked steps park the card clear of the panel's own controls — see
        // dockTooltip()/dockAboveMediaRow() for where "docked" actually places it.
        $('body').toggleClass('iseo-guide-dock', !!step.dock);

        // Update form-focus classes
        updateFormFocus(step);

        // Build tooltip content
        buildTooltip(step, index);

        // Cover-image step: if a method is already picked (re-entered from a later
        // panel, or resumed after a reload), the card belongs to THAT method, not to
        // the generic "pick an option" prompt. Overwrites the card just built.
        if (index === STEP_MEDIA_IDX) {
            applyMediaMethodState(currentMediaMethod());
            // Covers the no-selection-yet case (applyMediaMethodState is a no-op
            // without a method) so the default nudge still lands on card 1 — see
            // updateMediaHighlight().
            updateMediaHighlight();
        }

        // The step right after it: which method produced the cover determines what
        // "ready" should say — see applyMediaDoneState().
        if (index === STEP_GENERATE_IDX) {
            applyMediaDoneState(step, index, currentMediaMethod());
        }

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
            $spotlight.hide();
            if ($target.length) {
                if (index !== STEP_MEDIA_IDX) {
                    $target.addClass('iseo-guide-highlight');
                }
                $target[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                setTimeout(function () {
                    placeTooltip(step, $target);
                    $tooltip.show();
                }, 180);
            } else {
                placeTooltip(step, $target);
                $tooltip.show();
            }
        } else {
            // Inside Bootstrap modal — use blue glow instead of spotlight
            $spotlight.hide();
            if ($target.length) {
                // STEP_MEDIA_IDX owns its own highlight target (the card the user
                // actually selected, not this step's fixed default) — already applied
                // above by updateMediaHighlight(). Adding it here too would leave BOTH
                // card 1 (this step's literal step.target) and the selected card glowing.
                if (index !== STEP_MEDIA_IDX) {
                    $target.addClass('iseo-guide-highlight');
                }
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
       set dock:true and the card is docked instead — see dockTooltip()/
       dockAboveMediaRow() for where "docked" actually puts it on this step.
    ───────────────────────────────────────────────────────── */
    function placeTooltip(step, $target) {
        if (step && step.dock) {
            dockTooltip();
        } else {
            positionTooltip($target, step ? step.position : 'top');
        }
    }

    // Card width for the (currently unused) plain corner dock — see dockTooltip()'s
    // fallback branch. Must stay in step with the width rule for
    // #iseo-guide-tooltip[data-pos="docked"] in onboarding-guide.css.
    function dockWidth() {
        return window.innerWidth <= 1100 ? 240 : 300;
    }
    var DOCK_GAP = 16;

    function dockTooltip() {
        // Drop the inline coordinates a previous anchored step left behind so the
        // [data-pos="docked"] rule in onboarding-guide.css can take over.
        $tooltip.css({ top: '', left: '', width: '' }).attr('data-pos', 'docked');

        // The card is position:fixed and the wizard panel scrolls behind it. This is a
        // pure overlay: no CSS reserves space for it (see onboarding-guide.css).
        // Measured rather than hard-coded because the panel moves with the admin bar,
        // notices and the viewport.
        var $panel  = $('#exampleModal1 .improveseo-bulk-ai').first();
        var $modal  = $('#exampleModal1');
        var $box    = $panel.length ? $panel : $modal;
        if (!$box.length || !$box[0] || !$box[0].getBoundingClientRect) return;

        var m = $box[0].getBoundingClientRect();
        if (!m.width) return; // panel not laid out yet — keep the CSS fallback corner

        var $row = $modal.find('.iseo-media-methods').first();
        if ($row.length && $row[0] && $row[0].getBoundingClientRect) {
            dockAboveMediaRow(m, $row[0].getBoundingClientRect());
            return;
        }

        // No card row on this dock step (none currently defined) — original narrow
        // corner-below-the-stepper placement, unchanged.
        var $above  = $modal.find('.steps').first();
        if (!$above.length) $above = $modal.find('.singlepost-title').first();
        var hdr = ($above.length && $above[0]) ? $above[0].getBoundingClientRect() : null;

        var ttW = dockWidth();
        var ttH = $tooltip.outerHeight(true) || 230;
        var top  = (hdr && hdr.height ? hdr.bottom : m.top) + DOCK_GAP;
        var left = m.right - ttW - DOCK_GAP;

        $tooltip.css({
            top:   Math.max(10, Math.min(top,  window.innerHeight - ttH  - 10)) + 'px',
            left:  Math.max(10, Math.min(left, window.innerWidth  - ttW  - 10)) + 'px',
            right: 'auto',
            width: ttW + 'px'
        });
    }

    // Add Media step: a narrow card pinned to a corner ran through the card row no
    // matter which corner it used — top-right sat on whichever card was at that
    // edge (reported for both "Upload your own" and the right-most card generally),
    // and dropping down far enough to clear the row instead landed on the
    // Previous/Next bar below. Neither corner is dead space here; the only place
    // that reliably is is the "Add your cover image" header band ABOVE the row.
    //
    // The card keeps its compact width (dockWidth: 300/240px) and aligns to the
    // RIGHT of the panel, in the header band between the stepper and the card row.
    // Clamped below the stepper's bottom so it never overlaps step labels.
    function dockAboveMediaRow(panelRect, rowRect) {
        // Compact card width — same as the non-media dock and every other step.
        // The previous full-panel-width stretch (panelRect.width - gap) made the
        // card cover the stepper and look nothing like the guide's normal card.
        var ttW = dockWidth();
        // Width has to be applied before measuring height: the card's height is a
        // function of how its text wraps at THIS width.
        $tooltip.css({ width: ttW + 'px' });
        var ttH = $tooltip.outerHeight(true) || 150;

        // Right-aligned, above the card row — doubled gap so the card bottom
        // never bleeds into the method cards even when text wraps tall.
        var top  = rowRect.top - ttH - DOCK_GAP * 2;
        var left = panelRect.right - ttW - DOCK_GAP;

        // Never overlap the stepper, but prefer overlapping the stepper a
        // little over overlapping the method cards — tight 4px gap here so
        // the card is pushed down only as a last resort.
        var $modal   = $('#exampleModal1');
        var $stepper = $modal.find('.steps').first();
        if (!$stepper.length) $stepper = $modal.find('.singlepost-title').first();
        if ($stepper.length && $stepper[0] && $stepper[0].getBoundingClientRect) {
            var stepperBottom = $stepper[0].getBoundingClientRect().bottom;
            if (top < stepperBottom + 4) {
                top = stepperBottom + 4;
            }
        }

        $tooltip.css({
            // Clamp to the viewport only — never pulled back down toward the row,
            // which would reintroduce the overlap this exists to prevent.
            top:   Math.max(10, Math.min(top, window.innerHeight - ttH - 10)) + 'px',
            left:  Math.max(10, Math.min(left, window.innerWidth - ttW - 10)) + 'px',
            right: 'auto',
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
    // The guide's own steps ARE the numbering the user sees on this page: the first card
    // it shows is Step 1. It used to render index + 2, reserving Step 1 for the
    // card-choice page in views/posting/index.php — which is a different page the user
    // has already left, so opening (or reloading) this one always started at "Step 2".
    //
    // The numerator/denominator come from visibleStepNumber()/visibleStepTotal(), NOT
    // index+1 / STEPS.length directly: an optional step the wizard is skipping (niche
    // other than "Other", a non-custom length) still occupies a STEPS[] slot, and
    // counting it anyway is what put a hole in the numbers the user sees — Step 2
    // followed by Step 4, no Step 3 anywhere, for a niche that was never "Other".
    function stepCounterLabel(index) {
        return 'Step ' + visibleStepNumber(index) + ' of ' + visibleStepTotal();
    }

    function stepPercent(index) {
        return Math.round((visibleStepNumber(index) / visibleStepTotal()) * 100);
    }

    function buildTooltip(step, index) {
        var pct        = stepPercent(index);
        var isNextBtn  = (step.advance === 'next-btn');
        var isWizNext  = (step.advance === 'wizard-next');
        var isFinal    = (step.advance === 'final');

        var html = '<div class="iseo-guide-tooltip-inner">';
        html += '<div class="iseo-guide-progress"><div class="iseo-guide-progress-bar" style="width:' + pct + '%"></div></div>';
        html += '<div class="iseo-guide-header">';
        html += '<span class="iseo-guide-bot">&#x1F916;</span>';
        html += '<span class="iseo-guide-step-counter">' + stepCounterLabel(index) + '</span>';
        html += '</div>';
        html += '<div class="iseo-guide-title">' + applyCopyTokens(step.title) + '</div>';
        html += '<div class="iseo-guide-message">' + applyCopyTokens(step.message) + '</div>';

        // wizard-next: instruct user to click the real Next button — no tooltip Next button
        if (isWizNext) {
            var hintText = step.wizardHint || '&#8595; Click the <strong>{button}</strong> button below to continue';
            html += '<div class="iseo-guide-wizard-hint">' + applyCopyTokens(hintText) + '</div>';
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
       COVER-IMAGE STEP — one state machine per method

       The card has to be right at every moment of the method the user picked:
       what to do when it is picked, what is happening while it works, and it may
       only move on when THAT method's own completion signal fires (its hidden path
       field being filled — the same thing that ends the plugin's loading screen).

       Advancing on "an option was selected" is what previously put the next step's
       card ("Image Option Set / Generate AI Post") on screen while the loading
       screen was still up, with generation copy shown even for the upload path.
    ───────────────────────────────────────────────────────── */
    function selectedMediaValue() {
        return $('#exampleModal1 input[name="aiImage"]:checked').val() || '';
    }

    function currentMediaMethod() {
        return MEDIA_METHODS[selectedMediaValue()] || null;
    }

    // The guide's blue ring must sit on whichever card is ACTUALLY selected, not on
    // STEP_MEDIA_IDX's literal step.target (card 1 — the recommended default, used
    // only until a real choice is made). Native radios already move the CSS :checked
    // border correctly on their own (assets/css/made_by_me.css:2776); this is what
    // keeps the separate guide-highlight ring in sync with it, since nothing else
    // clears it off a previously-highlighted card when the selection changes.
    function updateMediaHighlight() {
        var val   = selectedMediaValue();
        var $card = val ? $('label[for="' + val + '"]') : $(STEPS[STEP_MEDIA_IDX].target);
        $('#exampleModal1 .iseo-media-card').removeClass('iseo-guide-highlight');
        if ($card.length) $card.addClass('iseo-guide-highlight');
    }

    // Picked (or re-entered) a method: show that method's own instructions and start
    // watching that method's own completion field.
    function applyMediaMethodState(def) {
        if (!def) return;
        // Move the ring to this card before anything else — covers every caller:
        // the radio change handler, re-entering this step with a method already
        // picked, and the busy-timeout retry (all three call this function).
        updateMediaHighlight();
        // Still Step N — the user is being asked to do something, not to wait.
        showWaitingTooltip(
            def.selectTitle,
            def.selectMessage,
            stepPercent(STEP_MEDIA_IDX),
            stepCounterLabel(STEP_MEDIA_IDX)
        );
        redockMediaCard();
        startMediaWait(def);
    }

    // Shallow-copies a STEPS entry with its title/message swapped out — used to
    // render the same step (same target/advance/dock/pollTarget/wizardHint) with
    // different copy without mutating the shared STEPS array.
    function cloneStepWithCopy(step, title, message) {
        var copy = {};
        for (var key in step) {
            if (step.hasOwnProperty(key)) copy[key] = step[key];
        }
        copy.title   = title;
        copy.message = message;
        return copy;
    }

    // media-next ("Cover Image Ready"): which method actually produced the cover
    // decides what "ready" means. The upload path generated nothing, so it must not
    // read like it did — its own doneMessage never says "Generate" about the image.
    // Overwrites the generic card buildTooltip() just rendered for this step; falls
    // through to that generic copy only if the method can't be determined (should
    // not happen in practice — a method must be picked to ever reach this step).
    function applyMediaDoneState(step, index, def) {
        if (!def || !def.doneMessage) return;
        buildTooltip(cloneStepWithCopy(step, def.doneTitle, def.doneMessage), index);
    }

    // These cards replace the whole card body, so their height differs from the one
    // dockTooltip() measured — re-dock so the corner placement stays honest and the
    // card never runs off the bottom of the panel.
    function redockMediaCard() {
        placeTooltip(STEPS[STEP_MEDIA_IDX], $(STEPS[STEP_MEDIA_IDX].target));
    }

    // That method is now working. The glow goes (z-index 1060, it would pulse through
    // the loading overlay); the card stays, because this is the moment the user most
    // needs to be told what is happening and that it is normal to wait.
    function showMediaBusyCard(def) {
        if (!def) return;
        $('.iseo-guide-highlight').removeClass('iseo-guide-highlight');
        showWaitingTooltip(def.busyTitle, def.busyMessage, stepPercent(STEP_MEDIA_IDX));
        redockMediaCard();
    }

    // Advance ONLY on this method's true completion signal.
    //
    // Switching methods (AI_image -> Manually_image, say) calls this again without
    // currentStep or _waiting ever changing — both stay "on the media step, waiting" the
    // whole time, so neither guarded against the PREVIOUS method's poll. If that poll was
    // still ticking (its own field not yet empty-checked-and-abandoned — e.g. the user
    // switched away from AI-from-title before it finished, and the AI response arrived
    // later), it would see #AI-Image-uploaded-path fill and advance the guide to "Cover
    // Image Ready" while Upload — the method actually on screen — had done nothing at
    // all. Cancelling the previous timer before starting a new one means only the
    // CURRENTLY selected method's own field can ever trigger the advance.
    function startMediaWait(def) {
        if (_mediaWaitTimer) {
            clearInterval(_mediaWaitTimer);
            _mediaWaitTimer = null;
        }
        _waiting    = true;
        _waitingFor = STEP_MEDIA_IDX;
        _mediaWaitTimer = waitForValue(def.done, function () {
            _mediaWaitTimer = null;
            if (_waiting && currentStep === STEP_MEDIA_IDX) {
                _waiting = false;
                showStep(STEP_MEDIA_IDX + 1); // → media-next (wizard-next)
            }
        }, 90, function () {
            _mediaWaitTimer = null;
            // Nothing after 90s — it failed, or the user walked away from it. Put this
            // method's own instructions back rather than leaving a stale "please wait";
            // re-renders the SAME step, it does not advance.
            if (currentStep === STEP_MEDIA_IDX) {
                var def2 = currentMediaMethod();
                if (def2) applyMediaMethodState(def2);
                else showStep(STEP_MEDIA_IDX);
            }
        });
    }

    /* ─────────────────────────────────────────────────────────
       WAITING TOOLTIP (shown during AI generation)
    ───────────────────────────────────────────────────────── */
    function showWaitingTooltip(title, message, pct, counterLabel) {
        var html = '<div class="iseo-guide-tooltip-inner">';
        html += '<div class="iseo-guide-progress"><div class="iseo-guide-progress-bar" style="width:' + (pct || 50) + '%"></div></div>';
        html += '<div class="iseo-guide-header"><span class="iseo-guide-bot">&#x1F916;</span><span class="iseo-guide-step-counter">' + (counterLabel || 'Please wait\u2026') + '</span></div>';
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
            // The final review step: submit the create-post form, then end the guide.
            destroyGuide();
            submitReviewForm();
        } else {
            showStep(currentStep + 1);
        }
    }

    // Submit the create-post form that saveFinalData() populated. Called when the
    // user clicks Done on the last review step.
    function submitReviewForm() {
        var form = document.getElementById('main_form');
        if (!form) return;
        var btn = form.querySelector('button[name="create"]');
        if (!btn) return;
        if (window.ImproveSEOLoading && ImproveSEOLoading.show) {
            ImproveSEOLoading.show({
                title: 'Publishing your post\u2026',
                message: 'Creating your project and publishing the post. This only takes a moment.'
            });
        }
        setTimeout(function () {
            if (window.tinymce && typeof tinymce.triggerSave === 'function') {
                tinymce.triggerSave();
            }
            btn.click();
        }, 300);
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
    function waitForValue(selector, callback, maxSeconds, onTimeout) {
        var tries = 0;
        var max   = (maxSeconds || 30) * 5;
        var iv = setInterval(function () {
            var val = $(selector).val();
            if (val && val.trim().length > 0) {
                clearInterval(iv);
                callback();
            } else if (++tries >= max) {
                clearInterval(iv);
                if (onTimeout) onTimeout();
            }
        }, 200);
        return iv; // callers that can run more than one of these (startMediaWait) need
                   // the id to cancel a still-running one before starting the next.
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
                setTimeout(function () { showStep(startStep()); }, 300);
            }
        });

        /* ── Modal close ────────────────────────────────────── */
        $(document).on('hidden.bs.modal.iseoguide', '#exampleModal1', function () {
            if (currentStep < 0) return;
            // At or past the submit step: the click handler on #nextStepButton already
            // handles the modal→form transition. If hidden.bs.modal fires at all (it
            // usually won't — saveFinalData uses jQuery .hide()), just let it pass.
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
            } else if (currentStep === STEP_SUBMIT_IDX) {
                // Submit: the wizard's own handler calls saveFinalData(), which uses
                // jQuery.hide() (NOT Bootstrap .modal('hide')) — so hidden.bs.modal
                // never fires. Do the modal→form transition right here instead.
                $tooltip.hide();
                $('.iseo-guide-highlight').removeClass('iseo-guide-highlight');

                // Stop the panel watcher — it reads #step_value which only exists
                // inside the wizard modal; continuing would misfire on the form page.
                clearInterval(_syncTmr);
                _syncTmr = null;

                // Give saveFinalData() time to populate the form fields, insert
                // content into TinyMCE, and hide the modal — then reveal the form
                // and start the page-review steps.
                setTimeout(function () {
                    // Remove the Bootstrap modal backdrop that may linger after .hide()
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                    // Reveal the underlying form
                    $('.style_create_page_form').removeAttr('style').css({ opacity: 1, visibility: 'visible' });
                    // Remove the dock body class — page steps are not docked.
                    $('body').removeClass('iseo-guide-dock');
                    showStep(STEP_REVIEW_START);
                }, 800);
            } else {
                // The panel watcher polls #step_value on its own fixed interval,
                // independent of this click, and the wizard updates #step_value
                // near-instantly on click — so it can already have advanced the card
                // by the time this timeout fires. Re-reading currentStep then would
                // add a SECOND +1 on top of that, skipping the card the watcher just
                // landed on. Capture the step this click was for and only act if
                // nothing else has moved the card since.
                var fromStep = currentStep;
                setTimeout(function () {
                    if (currentStep === fromStep) showStep(fromStep + 1);
                }, 350);
            }
        });

        /* ── title-generate: click #reload → wait for AI title ─ */
        $(document).on('click.iseoguide', '#reload', function () {
            if (currentStep !== STEP_TITLE_GENERATE_IDX) return;
            _waiting    = true;
            _waitingFor = STEP_TITLE_GENERATE_IDX;
            showWaitingTooltip(
                'Generating your AI Title &#x23F3;',
                'The AI is crafting an optimised title from your keyword \u2014 please wait.',
                30
            );
            waitForValue('#maintitlearea', function () {
                if (_waiting && currentStep === STEP_TITLE_GENERATE_IDX) {
                    _waiting = false;
                    // → read-title (falls through to title-approve via showStep's own
                    // hidden-optional walk if the field isn't actually on screen)
                    showStep(STEP_READ_TITLE_IDX);
                }
            }, 30);
        });

        /* ── title-approve: approve title checkbox ──────────── */
        $(document).on('change.iseoguide', '#checkbox_need', function () {
            if (currentStep !== STEP_TITLE_APPROVE_IDX) return;
            if ($(this).is(':checked')) {
                setTimeout(function () { showStep(STEP_TITLE_NEXT_IDX); }, 200); // → glow on Next button
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
            applyMediaMethodState(MEDIA_METHODS[this.value]);
        });

        /* ── This method started working ────────────────────── */
        // One binding per method, so the busy card belongs to the method that is
        // actually running: the two AI methods are generating, the upload is uploading.
        // The old single binding covered the two Generate buttons only and hid the card
        // outright, which left the upload path with no progress copy at all and every
        // path with the next step's card the instant a hidden field filled.
        //
        // Bound DIRECTLY to each element (jQuery('selector').on(...)), NOT delegated via
        // $(document).on(event, selector, ...). refreshAIImage() (the AI-from-title
        // Generate button's own handler) disables that button as its very first line,
        // synchronously, before the click event finishes bubbling. jQuery's delegated
        // dispatch explicitly skips a target that is .disabled for a 'click' event when
        // matching a delegated selector — so a document-level delegated handler here
        // never fired for that method: showMediaBusyCard() never ran, the highlight glow
        // (z-index:1060) was never removed from the selected card, and it kept floating
        // above the loading overlay (z-index:999) with stale select-state copy — Issues
        // 1-3 all traced back to this one non-firing handler. A DIRECTLY bound handler
        // runs in the same target-phase pass as the inline onclick and isn't subject to
        // that skip. The elements exist in the DOM from page load (their panel is just
        // display:none until picked), so binding at init time is safe.
        Object.keys(MEDIA_METHODS).forEach(function (value) {
            var def = MEDIA_METHODS[value];
            if (!def.startOn) return;
            $(def.startOn.target).on(def.startOn.event + '.iseoguide', function () {
                if (currentStep !== STEP_MEDIA_IDX) return;
                if (selectedMediaValue() !== value) return; // a hidden panel's control
                showMediaBusyCard(def);
            });
        });

        /* ── Reposition on resize / scroll ──────────────────── */
        function reposition() {
            clearTimeout(_reposTmr);
            _reposTmr = setTimeout(function () {
                if (currentStep < 0 || currentStep >= STEPS.length) return;
                var step    = STEPS[currentStep];
                var $target = $(step.target);
                if (step.phase !== 'modal') positionSpotlight($target);
                placeTooltip(step, $target);
            }, 80);
        }

        $(window).on('resize.iseoguide scroll.iseoguide', reposition);

        // The modal is its own scroll container and scroll does not bubble, so the window
        // handler never hears it. Without this the docked card keeps a top measured
        // against a stepper that has since scrolled — scroll back up and the card is
        // sitting on it.
        $('#exampleModal1').on('scroll.iseoguide', reposition);
    }

    /* ─────────────────────────────────────────────────────────
       DESTROY GUIDE
    ───────────────────────────────────────────────────────── */
    function destroyGuide() {
        if ($spotlight) $spotlight.remove();
        if ($tooltip)   $tooltip.remove();
        $('.iseo-guide-highlight').removeClass('iseo-guide-highlight');
        $('.iseo-guide-form-focus').removeClass('iseo-guide-form-focus');
        $('body').removeClass('iseo-guide-active iseo-guide-form-phase iseo-guide-dock');
        $(window).off('.iseoguide');
        $(document).off('.iseoguide');
        $('#exampleModal1').off('.iseoguide');
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
