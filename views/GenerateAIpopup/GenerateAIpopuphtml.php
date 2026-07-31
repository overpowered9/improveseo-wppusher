<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
<script type="text/javascript"
    src="https://cdn.jsdelivr.net/gh/bbbootstrap/libraries@main/jquery.smartWizard.min.js"></script>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
<link href="https://cdn.jsdelivr.net/gh/bbbootstrap/libraries@main/smart_wizard.min.css" rel="stylesheet"
    type="text/css" />
<link href="https://cdn.jsdelivr.net/gh/bbbootstrap/libraries@main/smart_wizard_theme_dots.min.css" rel="stylesheet"
    type="text/css" />
<?php
$plugin_url = plugin_dir_url(dirname(__FILE__, 2)); // Go up 2 levels to the root of the plugin
$image_url = $plugin_url . 'assets/images/AI-generated.gif';
$image_url1 = $plugin_url . 'assets/images/Writing-Optimization.gif';
$image_url2 = $plugin_url . 'assets/images/loaderr.gif';
global $ai_modal_type;
?>
<style>
    .modal {
        max-width: unset;
    }

    /*#exampleModal { z-index: 9999; }*/
    .modal-backdrop {
        height: unset;
    }

    .input-group>.form-control {
        width: 100%;
    }

    #popupcontainer input[type=checkbox] {
        display: none
    }

    #getpopupselected {
        margin: 20px 0;
    }

    .overlay {
        // margin :54px;
        display: none;
        position: fixed;
        width: 100%;
        height: 100%;
        // top: 209px;
        left: 0;
        z-index: 999;
        background: rgb(255, 255, 255) url("<?php echo $image_url1; ?>") center no-repeat;

    }

    .overlay2 {
        // margin :54px;
        display: none;
        position: fixed;
        width: 100%;
        height: 100%;
        // top: 209px;
        left: 0;
        z-index: 999;
        background: rgb(255, 255, 255) url("<?php echo $image_url2; ?>") center no-repeat;

    }

    .overlay_ai_data {
        // margin :54px;
        display: none;
        position: fixed;
        width: 100%;
        height: 100%;
        // top: 209px;
        left: 0;
        z-index: 999;
        /* Keep the 400x400 gif centred in the overlay. */
        background: rgb(255, 255, 255) url("<?php echo $image_url1; ?>") center no-repeat;

    }


    .overlay_ai_image {
        display: none;
        position: fixed;
        width: 100%;
        height: 100%;
        left: 0;
        z-index: 999;
        background: rgb(255, 255, 255) url("<?php echo $image_url; ?>") center no-repeat;
    }

    .sw-theme-dots>ul.step-anchor:before {
        top: 58px !important;
    }

    .selected {
        background: #b0b0b0 !important;
        color: #fff !important;
        font-weight: 600 !important;
    }

    .content_type {
        margin-top: 20px;
    }

    #gettitle span {
        display: flex;
        align-items: center;
    }

    #gettitle {
        display: flex;
        align-items: center;
    }

    .resultdata {
        /* border: 1px solid #bfbfbf; */
        padding: 0px !important;
        align-items: center;
        width: 100%;
        border-radius: 20px;
        margin-right: 10px;
    }

    #langerror {
        color: #f00;
    }

    /* Step 4 content preview: image on the left (sticky, small), article on the right */
    .iseo-preview-layout {
        display: flex;
        gap: 24px;
        align-items: flex-start;
    }

    .iseo-preview-media {
        flex: 0 0 260px;
        max-width: 260px;
        position: sticky;
        top: 0;
        align-self: flex-start;
    }

    .iseo-preview-media img {
        max-width: 100% !important;
        max-height: 220px;
        width: 100%;
        height: auto;
        object-fit: cover;
        border-radius: 8px;
    }

    .iseo-preview-body {
        flex: 1 1 auto;
        min-width: 0;
    }

    @media (max-width: 768px) {
        .iseo-preview-layout {
            flex-direction: column;
        }

        .iseo-preview-media {
            position: static;
            flex-basis: auto;
            max-width: 100%;
            align-self: center;
        }
    }

    /* Step 4 preview laid out as ONE post in three stacked "fields": the title, then the
       cover image, then the content (without its title). Spacing (not borders) separates
       them so they read as a single post, and the image sits in its own field instead of
       floating over the text. Each field is dynamic-height (fits its own content). */
    .iseo-post-field {
        margin: 0;
    }
    .iseo-post-title {
        margin: 0 0 16px;              /* grows to 1 or 2 lines as needed */
    }
    .iseo-post-title h1,
    .iseo-post-title h2 {
        margin: 0;
        font-size: 30px;
        line-height: 1.25;
        font-weight: 700;
        color: #1a1a1a;
    }
    .iseo-post-image {
        margin: 0 0 20px;             /* sizes to the image */
    }
    .iseo-post-image img {
        display: block;
        max-width: 100%;
        height: auto;
        border-radius: 8px;
    }
    .iseo-post-content {
        color: #2b2b2b;
        font-size: 16px;
        line-height: 1.7;
    }
    /* Normalise the article body to ONE consistent size. The AI HTML sometimes carries
       inline font-size on paragraphs/spans/divs, which made the preview mix sizes. Force
       a single body size (overriding inline styles); the heading rules below are more
       specific, so they restore the hierarchy. Scoped to the preview content only. */
    .iseo-post-content * {
        font-size: 16px !important;
        line-height: 1.7 !important;
    }
    .iseo-post-content .main-content-section-improveseo > *:first-child { margin-top: 0; }
    /* Headings (and any inline spans inside them) keep their size — a bit smaller than
       the 30px title above, in a clear hierarchy. */
    .iseo-post-content h1, .iseo-post-content h1 * { font-size: 26px !important; line-height: 1.25 !important; }
    .iseo-post-content h2, .iseo-post-content h2 * { font-size: 23px !important; line-height: 1.3 !important; }
    .iseo-post-content h3, .iseo-post-content h3 * { font-size: 19px !important; line-height: 1.35 !important; }
    .iseo-post-content h4, .iseo-post-content h4 * { font-size: 17px !important; line-height: 1.4 !important; }
    .iseo-post-content h2,
    .iseo-post-content h3 { font-weight: 700; color: #1a1a1a; }
    .iseo-post-content h2 { margin: 26px 0 10px; }
    .iseo-post-content h3 { margin: 22px 0 8px; }
    .iseo-post-content h4 { font-weight: 600; color: #1a1a1a; margin: 18px 0 6px; }
    .iseo-post-content p { margin: 0 0 14px; }
    .iseo-post-content ul,
    .iseo-post-content ol { margin: 0 0 14px; padding-left: 22px; }
    .iseo-post-content li { margin: 4px 0; }
    .iseo-post-content a { color: #1C7293; text-decoration: underline; }
    /* Fallback: if a leading title heading is still present (its text didn't match the
       known title), hide it so the title never appears twice. */
    .iseo-post-content > .main-content-section-improveseo > h1:first-child { display: none; }

    /* Live character counter + pass/fail check under each meta field. Colours use
       !important so admin/theme styles can't wash them out. */
    .iseo-meta-count {
        display: inline-block !important;
        margin-top: 6px;
        font-size: 13px !important;
        font-weight: 600 !important;
        color: #6b7280 !important;               /* empty field — neutral */
        font-family: "Poppins", sans-serif;
    }
    .iseo-meta-count.is-ideal { color: #2E9E6B !important; }  /* ideal range — check passes */
    .iseo-meta-count.is-over  { color: #d92d20 !important; }  /* over the limit — check fails */
    .iseo-meta-count.is-under { color: #b45309 !important; }  /* under the ideal — too short */

    /* Disclaimer under the meta description: other SEO plugins may override these. */
    .iseo-meta-note {
        margin: 12px 0 0;
        font-size: 13px;
        line-height: 1.5;
        color: #6b7280;
        font-family: "Poppins", sans-serif;
    }
    .iseo-meta-note strong { color: #23282d; }
    .iseo-meta-note a { color: #1C7293; font-weight: 600; text-decoration: underline; }
    .iseo-meta-note a:hover { color: #0B132B; }

    /* Step 4 Approve / Regenerate controls. The same pair appears at the top-right of
       the content (hidden until content exists) and below it. All four buttons share
       one class so they are exactly the same size. */
    .iseo-content-actions {
        align-items: center;
        gap: 14px;
    }
    /* Step 4 content preview: use far more of the wizard width than the default 790px
       .generate-data cap, so the article reads wide and clear like the post-preview
       screen. Caps at 1040px and shrinks to fit narrower wizards (width:100%). */
    .generate-data.generate-data--preview {
        max-width: 1040px !important;
    }
    /* Collapsed content preview: show only the top of the article and fade it out, so the
       user sees a taste of the content. Clicking it (or the button) opens the full content
       in a new tab. */
    .iseo-preview-collapse {
        position: relative;
        max-height: 520px;
        overflow: hidden;
        cursor: pointer;
        border-radius: 12px;
    }
    .iseo-preview-collapse::after {
        content: "";
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        height: 150px;
        background: linear-gradient(to bottom, rgba(255, 255, 255, 0) 0%, #ffffff 92%);
        pointer-events: none;
        border-radius: 0 0 12px 12px;
    }
    .iseo-preview-expand-row {
        text-align: center;
        position: relative;
        z-index: 2;
        margin-top: -26px;              /* lift the button up over the fade */
    }
    .iseo-open-full-preview {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 24px;
        border-radius: 50px;
        background: #1C7293;
        color: #fff;
        border: none;
        cursor: pointer;
        font-family: "Poppins", sans-serif;
        font-weight: 500;
        font-size: 14px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
    }
    .iseo-open-full-preview:hover {
        background: #0B132B;
    }
    .iseo-open-full-preview.is-hidden {
        display: none;
    }
    /* Two-column content preview: the article fills the left column, the action
       stack sits in a right-hand column beside it (not above/overlapping it). */
    .iseo-preview-row {
        display: flex;
        align-items: flex-start;       /* stack aligns with the top of the content */
        gap: 24px;
    }
    .iseo-preview-main {               /* left column: content card + the bottom button row */
        flex: 1 1 auto;
        min-width: 0;                  /* let the article shrink, not overflow */
    }
    .iseo-content-actions-top {
        display: none;                 /* revealed once content exists (.is-visible) */
        flex: 0 0 auto;                /* fixed right-hand column */
        flex-direction: column;        /* the two buttons stacked one above the other */
        align-items: stretch;
        gap: 10px;
        margin: 0;
    }
    .iseo-content-actions-top.is-visible {
        display: flex;
    }
    /* Compact buttons for the right-hand stack; the pair below stays full size. */
    .iseo-content-actions-top .iseo-content-btn {
        width: 180px;
        padding: 8px 16px !important;
        font-size: 14px !important;
    }
    @media (max-width: 768px) {
        /* On small screens drop the side column; the full-size pair below serves. */
        .iseo-preview-row { display: block; }
        .iseo-content-actions-top { display: none !important; }
    }
    .iseo-content-actions-bottom {
        display: flex !important;
        flex-wrap: wrap;               /* wrap on very narrow cards */
        justify-content: center !important;
        align-items: center !important;
        gap: 12px !important;
        margin: 20px 0 8px;            /* directly under the content card */
    }
    /* Bottom buttons: compact (same as the top-right stack) and close together. The
       fixed width keeps the group well within the card, and margin:0 defeats any
       inherited spacing that would spread or widen them. */
    .iseo-content-actions-bottom .iseo-content-btn {
        width: 180px !important;
        padding: 8px 16px !important;
        font-size: 14px !important;
        margin: 0 !important;
    }
    .iseo-content-btn {
        background: #1C7293 !important;
        color: #fff !important;
        border: 1px solid #1C7293 !important;
        border-radius: 50px !important;
        padding: 11px 20px !important;
        font-size: 18px !important;
        font-weight: 500 !important;
        line-height: 1.4 !important;
        /* Fixed width + border-box so every button is exactly the same size,
           regardless of label length ("Approve Content" vs "Regenerate Content"). */
        width: 235px;
        box-sizing: border-box !important;
        text-align: center;
        cursor: pointer !important;
        font-family: "Poppins", sans-serif;
    }
    .iseo-content-btn:hover {
        background: #0B132B !important;
        border-color: #0B132B !important;
        color: #fff !important;
    }
    .iseo-content-btn.is-hidden {
        display: none !important;
    }
    /* Guidance under the (now single, centred) Regenerate button: tells the user how to get
       different content. Muted so it supports the button without competing with it. */
    .iseo-regen-hint {
        margin: 12px auto 4px !important;
        max-width: 470px;
        text-align: center !important;
        font-family: "Poppins", sans-serif;
        font-size: 13.5px !important;
        line-height: 1.55 !important;
        color: rgba(80, 87, 94, 0.9) !important;
    }
    .iseo-regen-hint b {
        color: #1C7293;
        font-weight: 600;
    }


    .multi-upload-gallery span {
        height: 100px;
        width: 100px;
        background-size: cover;
        background-position: center;
        display: block;
    }

    ul.multi-upload-gallery.ui-sortable {
        grid-template-columns: repeat(6, 1fr);
        display: grid;
        column-gap: 5px;
    }

    a.multi-upload-gallery-remove {
        color: black;
        position: relative;
        padding-right: -10px !important;
        right: 20px;
        top: -25px;
        font-size: 30px;
    }
    
    /* Bulk Category Styles */
    .bulk-category-item {
        display: inline-flex;
        align-items: center;
        padding: 10px 18px;
        background: white;
        border: 2px solid #1C7293;
        border-radius: 25px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 14px;
        font-weight: 500;
        color: #333;
    }
    
    .bulk-category-item:hover {
        border-color: #1C7293;
        background: #f0f8ff;
        box-shadow: 0 3px 8px rgba(30, 112, 184, 0.15);
        transform: translateY(-1px);
    }
    
    .bulk-category-item input[type="checkbox"] {
        display: inline-block !important;
        margin-right: 10px;
        cursor: pointer;
        width: 18px;
        height: 18px;
    }
    
    .bulk-category-item input[type="checkbox"]:checked {
        accent-color: #1C7293;
    }
    
    .category-selection-section h2 {
        font-size: 18px;
        font-weight: 600;
        color: #23282d;
        margin-bottom: 15px;
    }
    
    .category-selection-section h3 {
        font-size: 16px;
        font-weight: 500;
        color: #23282d;
        margin-bottom: 12px;
    }
    
    .bulk-category-box {
        background: white !important;
        padding: 25px !important;
        border-radius: 15px !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        margin-bottom: 20px !important;
    }
    
    .add-category-section {
        border-top: 1px solid #e8e8e8 !important;
        padding-top: 20px !important;
        margin-top: 20px;
    }
    
    /* Height/border/radius/font come from the shared bulk-field rule in
       made_by_me.css so this input matches every other field in the wizard. */
    .add-category-section input[type="text"] {
        flex: 1;
        transition: all 0.3s ease;
    }

    .add-category-section input[type="text"]:focus {
        outline: none;
        border-color: #1C7293 !important;
        box-shadow: 0 0 0 3px rgba(28, 114, 147, 0.1);
    }
    
    .add-category-section button {
        padding: 0 30px !important;
        height: 52px !important;
        box-sizing: border-box !important;
        border: 1px solid #1C7293 !important;
        border-radius: 50px !important;
        font-weight: normal !important;
        cursor: pointer !important;
        font-size: 18px !important;
        background: #1C7293 !important;
        color: #fff !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 5px !important;
        justify-content: center !important;
        box-shadow: 0 2px 8px rgba(30, 112, 184, 0.15);
        transition: all 0.3s ease;
    }
    
    .add-category-section button:hover {
        background: #0B132B !important;
        transform: translateY(-1px);
        box-shadow: 0 3px 12px rgba(30, 112, 184, 0.25) !important;
    }
    
    .category-selection-section > p {
        padding: 15px 20px !important;
        background: #fff9e6;
        border-left: 4px solid #ffc107;
        border-radius: 8px;
        color: #666 !important;
        font-size: 14px !important;
        margin-top: 15px !important;
    }

    /* ImproveSEO Notification Popup Styles */
    .improveseo-notification-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        z-index: 99999;
        justify-content: center;
        align-items: center;
        animation: fadeIn 0.3s ease-in-out;
    }

    .improveseo-notification-overlay.active {
        display: flex;
    }

    .improveseo-notification-box {
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        max-width: 450px;
        width: 90%;
        overflow: hidden;
        animation: slideUp 0.3s ease-out;
        position: relative;
    }

    .improveseo-notification-header {
        padding: 24px 24px 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        border-bottom: 1px solid #f0f0f0;
    }

    .improveseo-notification-icon {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        font-weight: 700;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        position: relative;
    }
    
    .improveseo-notification-icon::before {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: inherit;
        opacity: 0.2;
        animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
            opacity: 0.2;
        }
        50% {
            transform: scale(1.1);
            opacity: 0;
        }
    }

    .improveseo-notification-icon.warning {
        background: linear-gradient(135deg, #FFA500 0%, #FF8C00 100%);
        color: white;
    }
    
    .improveseo-notification-icon.warning::after {
        content: '⚠';
        position: absolute;
        font-size: 32px;
        font-weight: 300;
        line-height: 1;
    }

    .improveseo-notification-icon.error {
        background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        color: white;
    }
    
    .improveseo-notification-icon.error::after {
        content: '✕';
        position: absolute;
        font-size: 32px;
        font-weight: 300;
        line-height: 1;
    }

    .improveseo-notification-icon.success {
        background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
        color: white;
    }

    .improveseo-notification-icon.info {
        background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        color: white;
    }

    .improveseo-notification-title {
        flex: 1;
    }

    .improveseo-notification-title h3 {
        margin: 0;
        font-size: 20px;
        font-weight: 600;
        color: #23282d;
        line-height: 1.3;
    }

    .improveseo-notification-body {
        padding: 20px 24px;
    }

    .improveseo-notification-body p {
        margin: 0;
        font-size: 15px;
        color: #50575e;
        line-height: 1.6;
        /* Render \n in notification messages as line breaks (credit breakdowns). */
        white-space: pre-line;
    }

    .improveseo-notification-footer {
        padding: 16px 24px 24px;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
    }

    .improveseo-notification-btn {
        padding: 10px 24px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        border: none;
        outline: none;
        transition: all 0.2s ease;
        font-family: inherit;
    }

    .improveseo-notification-btn-primary {
        background: linear-gradient(135deg, #0073aa 0%, #005a87 100%);
        color: white;
    }

    .improveseo-notification-btn-primary:hover {
        background: linear-gradient(135deg, #005a87 0%, #00405f 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 115, 170, 0.3);
    }

    .improveseo-notification-btn-secondary {
        background: #f0f0f0;
        color: #50575e;
    }

    .improveseo-notification-btn-secondary:hover {
        background: #e0e0e0;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    @keyframes slideUp {
        from {
            transform: translateY(50px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    /* Loading Notification Styles */
    .improveseo-loading-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        z-index: 99999;
        justify-content: center;
        align-items: center;
        animation: fadeIn 0.3s ease-in-out;
    }

    .improveseo-loading-overlay.active {
        display: flex;
    }

    .improveseo-loading-box {
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        max-width: 400px;
        width: 90%;
        padding: 32px 24px;
        text-align: center;
        animation: slideUp 0.3s ease-out;
    }

    .improveseo-loading-spinner {
        width: 60px;
        height: 60px;
        margin: 0 auto 20px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #0073aa;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    .improveseo-loading-title {
        font-size: 20px;
        font-weight: 600;
        color: #23282d;
        margin: 0 0 12px 0;
    }

    .improveseo-loading-message {
        font-size: 15px;
        color: #50575e;
        margin: 0;
        line-height: 1.6;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>




<!-- ImproveSEO Notification Popup -->
<div class="improveseo-notification-overlay" id="improveseoNotification">
    <div class="improveseo-notification-box">
        <div class="improveseo-notification-header">
            <div class="improveseo-notification-icon" id="improveseoNotificationIcon"></div>
            <div class="improveseo-notification-title">
                <h3 id="improveseoNotificationTitle">Notification</h3>
            </div>
        </div>
        <div class="improveseo-notification-body">
            <p id="improveseoNotificationMessage">This is a notification message.</p>
        </div>
        <div class="improveseo-notification-footer">
            <button class="improveseo-notification-btn improveseo-notification-btn-primary" id="improveseoNotificationOk">
                OK
            </button>
        </div>
    </div>
</div>

<!-- ImproveSEO Loading Popup -->
<div class="improveseo-loading-overlay" id="improveseoLoading">
    <div class="improveseo-loading-box">
        <div class="improveseo-loading-spinner"></div>
        <h3 class="improveseo-loading-title" id="improveseoLoadingTitle">Generating Title...</h3>
        <p class="improveseo-loading-message" id="improveseoLoadingMessage">Please wait while AI generates your title.</p>
    </div>
</div>

<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="false" aria-modal="false">

    <div class="improve-ai-modal" role="document">
        <div class="improve-ai-header">
            <span class="improve-ai-title" id="exampleModalLabel"> <img
                    src="<?php echo WT_URL . '/assets/images/latest-images/iconoir_sparks-solid.svg' ?>"
                    alt="iconoir_sparks-solid"> Generate AI Content</span>
            <span class="improve-ai-close-btn"> <img id="close_generate_ai_popup"
                    src="<?php echo WT_URL . '/assets/images/latest-images/akar-icons_cross.svg' ?>"
                    alt="akar-icons_cross"> </span>
        </div>
        <div class="improve-ai-body">
            <div class="improve-ai-option">
                <img src="<?php echo WT_URL . '/assets/images/latest-images/aiai.svg' ?>" alt="Single AI Post">
                <div class="improve-ai-option-box">
                    <button class="btn open_single_AI_Post_popup" id="#exampleModal1" data-toggle="modal"
                        data-target="#exampleModal1">Create
                        Single AI Post </button>
                    <img src="<?php echo WT_URL . '/assets/images/latest-images/ep_info-filled.svg' ?>"
                        alt="ep_info-filled">
                </div>
            </div>
            <div class="improve-ai-option">
                <img src="<?php echo WT_URL . '/assets/images/latest-images/bulk.svg' ?>" alt="Bulk AI Posts">
                <div class="improve-ai-option-box">
                    <button class="btn open_bulk_AI_Post_popup" id="#exampleModal1" data-toggle="modal"
                        data-target="#exampleModal2">Bulk Create
                        AI Posts </button>
                    <img src="<?php echo WT_URL . '/assets/images/latest-images/ep_info-filled.svg' ?>"
                        alt="ep_info-filled">
                </div>
            </div>
        </div>
    </div>

</div>

<div class="modal fade" id="exampleModal1" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">


    <div id="loadingImage" style="display:none ;" class="overlay2">
        <!-- <img src="' . home_url('/') . 'wp-content/plugins/jobseq_jobs_pugin/assets/image/loader.gif" alt="Loading..."> -->
    </div>
    <div id="loadingAIData" style="display:none;" class="overlay_ai_data"></div>

    <div id="loadingAIImage" style="display:none;" class="overlay_ai_image"></div>

    <!-- Shared lightbox: clicking any generated/uploaded cover image opens it enlarged here.
         Close via the X, clicking the backdrop, or pressing Escape. -->
    <div id="iseoImageLightbox" class="iseo-lightbox" role="dialog" aria-modal="true" aria-label="Enlarged image">
        <button type="button" class="iseo-lightbox-close" id="iseoLightboxClose" aria-label="Close">&times;</button>
        <img id="iseoLightboxImg" src="" alt="Enlarged cover image">
    </div>

    <div class="improveseo-bulk-ai">
        <div class="singlepost-title">
            <h1> <img src="<?php echo WT_URL . '/assets/images/latest-images/iconoir_sparks-solid.svg' ?>"
                    alt="iconoir_sparks"> Generate Single Al Post</h1>
            <!-- Close (X) removed: X'ing out dropped the user onto the bare edit-post screen with
                 no way forward. The wizard is completed via its Next/Submit flow instead. -->
        </div>

        <div class="steps">
            <div class="line-step">
                <div class="percent active" id="progressLine">
                    <div class="step" id="step-1">
                        <div class="circle">1</div>
                        <p>Keyword & <br>Post Title</p>
                    </div>
                </div>

                <div class="percent" id="progressLine">

                    <div class="step" id="step-2">
                        <div class="circle">2</div>
                        <p>Content <br> Settings</p>
                    </div>

                </div>

                <div class="percent" id="progressLine">

                    <div class="step" id="step-3">
                        <div class="circle">3</div>
                        <p>Add Media</p>
                    </div>
                </div>


                <div class="percent" id="progressLine">

                    <div class="step" id="step-4">
                        <div class="circle">4</div>
                        <p>Generate AI <br>Content</p>
                    </div>
                </div>

                <div class="percent">
                    <div class="step" id="step-5">
                        <div class="circle">5</div>
                        <p>Media Title & <br>Description</p>
                    </div>
                </div>

                <div class="percent">
                    <div class="step" id="step-6">
                        <div class="circle">6</div>
                        <p>Project <br>Name</p>
                    </div>
                </div>
            </div>
        </div>

        <form id="popup_form" method="post" class="pop_up_form improve-seo-form-global">

            <div class="improveseo-sections">

                <!-- option 1  -->
                <div class="data dataJS">
                    <!-- <form class="improve-seo-form-global"> -->
                    <input type="hidden" name="step_value" id="step_value" value="1">
                    <?php
                    // Pre-fill seed keyword when arriving from the onboarding wizard
                    $prefill_keyword = '';
                    if ( isset( $_GET['from'] ) && $_GET['from'] === 'onboarding' ) {
                        $service = get_option( 'improveseo_business_service', '' );
                        $city    = get_option( 'improveseo_business_city', '' );
                        if ( $service ) {
                            $prefill_keyword = trim( $service );
                            if ( $city ) {
                                $prefill_keyword .= ' in ' . trim( $city );
                            }
                        }
                        $prefill_keyword = esc_attr( $prefill_keyword );
                    }
                    ?>
                    <div class="seo-form-field">
                        <label class="data-label" for="seed_keyword">Seed keyword</label>
                        <input type="text" class="form-control" placeholder="Enter Seed Keyword" id="seed_keyword"
                            name="seed_keyword" value="<?php echo $prefill_keyword; ?>"></input>
                        <span id="error_seed_keyword" style="color: red;"></span>
                        <?php if ( $prefill_keyword ) : ?>
                        <span class="iseo-prefill-hint" style="font-size:12px;color:#888;display:block;">
                            Pre-filled from your business setup — you can edit this.
                        </span>
                        <?php endif; ?>
                    </div>
                    <?php
                    // Pre-select the niche from the onboarding business type when it maps cleanly.
                    // Token => niche key; first substring match of the business type wins.
                    $iseo_prefill_niche = '';
                    $iseo_bt = strtolower( (string) get_option( 'improveseo_business_type', '' ) );
                    if ( $iseo_bt ) {
                        $iseo_niche_tokens = array(
                            'hvac' => 'hvac', 'heating' => 'hvac', 'cooling' => 'hvac',
                            'plumb' => 'plumbing',
                            'electric' => 'electrical',
                            'roof' => 'roofing',
                            'contractor' => 'contractor', 'remodel' => 'contractor', 'construction' => 'contractor',
                            'landscap' => 'landscaping', 'tree service' => 'landscaping',
                            'pest' => 'home_services', 'cleaning' => 'home_services', 'locksmith' => 'home_services', 'moving' => 'home_services',
                            'dental' => 'dental', 'dentist' => 'dental', 'orthodont' => 'dental',
                            'medical' => 'medical', 'clinic' => 'medical',
                            'wellness' => 'wellness', 'chiropract' => 'wellness', 'massage' => 'wellness',
                            'fitness' => 'fitness', 'personal train' => 'fitness', 'gym' => 'fitness',
                            'beauty' => 'beauty', 'salon' => 'beauty', ' spa' => 'beauty',
                            'vet' => 'pet_services', 'pet ' => 'pet_services',
                            'auto repair' => 'auto_repair', 'body shop' => 'auto_repair', 'automotive' => 'auto_repair',
                            'legal' => 'legal_services', 'attorney' => 'legal_services', 'lawyer' => 'legal_services', 'law firm' => 'legal_services',
                            'real estate' => 'realestate', 'realestate' => 'realestate', 'realtor' => 'realestate',
                            'mortgage' => 'mortgage', 'insurance' => 'mortgage', 'financ' => 'mortgage',
                            'account' => 'accounting', 'bookkeep' => 'accounting', 'tax prep' => 'accounting',
                            'tutor' => 'education', 'test prep' => 'education', 'education' => 'education',
                            'restaurant' => 'restaurant', 'cafe' => 'restaurant', 'bakery' => 'restaurant',
                            'ecommerce' => 'ecommerce', 'e-commerce' => 'ecommerce',
                        );
                        foreach ( $iseo_niche_tokens as $iseo_tok => $iseo_nk ) {
                            if ( strpos( $iseo_bt, $iseo_tok ) !== false ) { $iseo_prefill_niche = $iseo_nk; break; }
                        }
                    }
                    ?>
                    <div class="seo-form-field">
                        <label class="data-label" for="niche_select">Business niche</label>
                        <select id="niche_select" name="niche" class="form-control custom-selcected" style="max-width:100% !important;">
                            <?php
                            $iseo_niches = array(
                                'hvac' => 'HVAC / Heating & Cooling',
                                'plumbing' => 'Plumbing',
                                'electrical' => 'Electrical',
                                'roofing' => 'Roofing / Exterior',
                                'contractor' => 'Contractor / Remodeling / Construction',
                                'landscaping' => 'Landscaping / Tree Service',
                                'home_services' => 'Home Services (Pest Control, Cleaning, Locksmith, Moving, etc.)',
                                'dental' => 'Dental / Orthodontics',
                                'medical' => 'Medical / Health Clinic',
                                'wellness' => 'Wellness / Chiropractic / Massage',
                                'fitness' => 'Fitness / Personal Training',
                                'beauty' => 'Beauty / Salon / Spa',
                                'pet_services' => 'Pet Services / Vet',
                                'auto_repair' => 'Auto Repair / Body Shop',
                                'legal_services' => 'Legal Services (Personal Injury, Family Law, etc.)',
                                'realestate' => 'Real Estate Agent / Broker',
                                'mortgage' => 'Financial Services (Mortgage, Insurance, etc.)',
                                'accounting' => 'Accounting / Bookkeeping / Tax',
                                'education' => 'Tutoring / Test Prep / Education',
                                'restaurant' => 'Restaurant / Cafe / Bakery',
                                'ecommerce' => 'Ecommerce / Product Review',
                                'general_blog' => 'General Blog / Digital Content',
                                'other' => 'Other',
                            );
                            $iseo_selected = $iseo_prefill_niche ? $iseo_prefill_niche : 'general_blog';
                            foreach ( $iseo_niches as $iseo_val => $iseo_label ) {
                                printf( '<option value="%s"%s>%s</option>', esc_attr( $iseo_val ), selected( $iseo_selected, $iseo_val, false ), esc_html( $iseo_label ) );
                            }
                            ?>
                        </select>
                        <span class="iseo-prefill-hint" style="font-size:12px;color:#888;display:block;">
                            Drives the writing style, structure and cover-image look for your niche.
                        </span>
                    </div>
                    <div class="seo-form-field" id="niche_other_wrap" style="display:none;">
                        <label class="data-label" for="niche_other">What's your niche?</label>
                        <input type="text" class="form-control" placeholder="e.g. Wedding photography, solar installation, boat rentals"
                            id="niche_other" name="niche_other">
                    </div>
                    <div class="seo-form-field">
                        <div class="title-tune">
                            <div class="title">
                                <label for="seed_options">Select Title Type</label>
                                <select id="seed_select" name="seed_options" id="title" class="custom-selcected">

                                    <option value="seed_option2">Smart Title (AI-Generated)</option>
                                    <option value="seed_option3">Question-Style Title (AI-Generated)</option>
                                    <option value="seed_option1">Exact Keyword as Title</option>
                                </select>
                            </div>
                            <span id="error_seed_select" style="color: red;"></span>
                            <div style="clear: both"> </div>
                            <div class="tune" id="seed">
                                <label for="tune">Tone of voice</label>
                                <select name="content_type" id="cotnt_type" id="tune" class="custom-selcected">
                                    
                                    <option value="friendly">Friendly</option>
                                    <option value="professional">Professional</option>
                                    <option value="informational">Informational</option>
                                    <option value="transactional">Transactional</option>
                                    <option value="inspirational">Inspirational</option>
                                    <option value="neutral">Neutral</option>
                                    <option value="witty">Witty</option>
                                    <option value="casual">Casual</option>
                                    <option value="authoritative">Authoritative</option>
                                    <option value="encouraging">Encouraging</option>
                                    <option value="persuasive">Persuasive</option>
                                    <option value="poetic">Poetic</option>
                                </select>
                                <span id="error_cotnt_type" style="color: red;"></span>
                                <div class="form-group col-md-1"></div>
                            </div>
                        </div>
                    </div>
                    <div style="clear: both"> </div>
                    
                    <div style="clear: both"> </div>
                    <!-- Visible by default: the default title type is Smart Title (seed_option2),
                         which needs the Generate/Approve area. JS hides it for Exact Keyword. -->
                    <div class="seo-form-field hide_on_seed_option1 ">
                        <div class="generate-title">
                            <div class="title-input">
                                <label for="Generate">AI Generated Title</label>
                                <span id="maintitle">
                                    <div class="resultdata">
                                        <textarea class="title-text" name="maintitlearea" id="maintitlearea"
                                            required></textarea>
                                        <div id="for_approve_content_validation" style="display: none;">
                                            <p class="for_approve_content_validation_error"
                                                style="color: #FFC107 !important;">Approve AI generated title in order to
                                                continue</p>
                                        </div>
                                    </div>
                                </span>

                                <label id=""><span><input style=" display: none;" type="checkbox" id="" /></span><span
                                        id="maintitle"> </span><label style=" display: none;" id=""><i
                                            class="fa fa-refresh" aria-hidden="true"></i></label></label>
                                <input type="hidden" name="aigeneratedtitle" id="aigeneratedtitle" />




                            </div>

                            <div class="title-btn">
                                <button type="button" id="reload"><img
                                        src="<?php echo WT_URL . '/assets/images/Vector.png' ?>"
                                        alt="ep_arrow-rights">&nbsp;<span id="reload-btn-text">Generate</span></button>
                                <label class="step_one_approve_button" for="checkbox_need"><input type="checkbox"
                                        id="checkbox_need" /><span style="display: none;" class="icon"><img
                                            src="<?php echo WT_URL . '/assets/images/hugeicons_tick-01.png' ?>"
                                            alt="ep_arrow-rights"></span><span class="label-text">Approve</span></label>
                            </div>
                        </div>
                    </div>


                </div>

                <!-- option 1  -->

                <!-- option 2 -->
                <div class="data">
                    <div class="step-opton2-content-box">
                        <div class="step-opton-col">
                            <div class="seo-form-field">
                                <label for="sel1">Article Size</label>
                                <!-- <select class="content-opt custom-selcected" name="nos_of_words" required
                                    id="post_size">
                                    <option value="600 to 1200 words">Small (600-1200 words)</option>
                                    <option value="1200 to 2400 words">Medium (1200 to 2400 words)</option>
                                    <option value="2400 to 3600 words">Large (2400 to 3600 words)</option>
                                </select> -->
                                <select class="form-control" name="nos_of_words" required
                                    style="max-width: 100% !important;" id="post_size">
                                      <option value="600 to 1200 words">Small (600 to 1200 words) </option>
                                        <option value="1200 to 2400 words">Medium (1200 to 2400 words) </option>
                                        <option value="2400 to 3600 words">Large (2400 to 3600 words)</option>

                                </select>
                                <input type="text" id="post_size_select" readonly style="width: 100% !important; display: none !important;"
                                    value="600-1200 words">
                            </div>

                        </div>

                        <div class="step-opton-col">
                            <div class="seo-form-field">
                                <label for="size">Point of View</label>
                                <select class="content-opt custom-selcected" name="point_of_view" id="size">
                                    <option value="none">Auto (AI Decides)</option>
                                    <option value="Second Person (you,your,yours)">Speaking to the Reader ("you", "your")</option>
                                    <option value="First person plural (we,us,our,ours)">Business Voice ("we", "our")</option>
                                    <option value="First person singular (I,me,my,mine)">Personal Voice ("I", "my")</option>

                                </select>
                            </div>
                        </div>

                        <div class="step-opton-col">
                            <div class="seo-form-field">
                                <label for="language">Select Language</label>
                                <select class="content-opt custom-selcected" name="content_lang" id="language">
                                    <option value="US English">English (US)</option>
                                    <option value="UK English">English (Uk)</option>
                                    <option value="German">German (De)</option>

                                </select>
                            </div>
                        </div>

                    </div>
                    <div class="seo-form-field iseo-details-block">
                        <label class="iseo-details-head">Details for this article <small style="color:#888;font-weight:normal;margin-left:6px;">(optional &mdash; tailored to your niche)</small></label>
                        <!-- Niche-specific fields are rendered here by iseoRenderNicheFields() based on the
                             Business Niche selected in Step 1. Each input is named nd_<id> and flows into niche_data. -->
                        <div id="niche_fields_container" class="niche-fields"></div>
                        <span class="iseo-prefill-hint" style="font-size:12px;color:#888;display:block;">
                            These prompts adapt to your niche. Your business name, location and services come from <strong>Settings &rarr; Business Details</strong> automatically.
                        </span>
                    </div>
                    <div class="seo-form-field">
                        <label for="call_to_action">Call to Action Text<a href="#" data-toggle="Information" title="Information">
                                <div class="dashicons dashicons-info-outline" aria-hidden="true"><br>
                                </div>
                            </a></label>
                        <textarea class="form-control" id="call_to_action" rows="3" name="call_to_action"
                            onkeypress="return countContentCallToAction()" onblur="LimitText(this,1000,2)"
                            placeholder="What action would you like the reader of your content to take?"></textarea>
                        <span id="countContentCallToAction"></span>
                    </div>
                    <div class="seo-form-field">
                        <label for="cta_url">Call to Action URL <small style="color:#888; font-weight:normal; margin-left:6px;">(optional)</small></label>
                        <input type="text" id="cta_url" name="cta_url" class="form-control"
                            placeholder="https://example.com/contact">
                        <span id="error_cta_url" style="color:red; display:block;"></span>
                    </div>
                </div>

                <!-- option 2 -->



                <!-- option 3 -->
                <div class="data">
                    <div class="seo-slide-steps-fours">

                        <div class="iseo-media-head">
                            <h2>Add your cover image</h2>
                            <p>Choose how you want the cover image created.</p>
                        </div>

                        <div class="iseo-media-methods" role="radiogroup" aria-label="Cover image method">
                            <input type="radio" class="iseo-media-radio" name="aiImage" value="AI_image" id="AI_image">
                            <label class="iseo-media-card" for="AI_image">
                                <span class="iseo-media-check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg></span>
                                <span class="iseo-media-icon"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l1.9 5.1L19 9l-5.1 1.9L12 16l-1.9-5.1L5 9l5.1-1.9L12 2z"/><path d="M19 13l.9 2.4L22 16l-2.1.9L19 19l-.9-2.1L16 16l2.1-.6L19 13z"/></svg></span>
                                <span class="iseo-media-copy">
                                    <span class="iseo-media-title">AI Image From Title</span>
                                    <span class="iseo-media-desc">Fastest &mdash; we write the prompt from your title and generate a cover for you.</span>
                                </span>
                                <span class="iseo-media-cost"><span class="iseo-cost-dot"></span>Uses 1 image credit</span>
                            </label>

                            <input type="radio" class="iseo-media-radio" name="aiImage" value="manually_promt_image" id="manually_promt_image">
                            <label class="iseo-media-card" for="manually_promt_image">
                                <span class="iseo-media-check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg></span>
                                <span class="iseo-media-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg></span>
                                <span class="iseo-media-copy">
                                    <span class="iseo-media-title">AI Image - Custom Prompt</span>
                                    <span class="iseo-media-desc">We draft a prompt from your title &mdash; edit it your way, then generate.</span>
                                </span>
                                <span class="iseo-media-cost"><span class="iseo-cost-dot"></span>Uses 1 image credit</span>
                            </label>

                            <input type="radio" class="iseo-media-radio" name="aiImage" value="Manually_image" id="Manually_image">
                            <label class="iseo-media-card" for="Manually_image">
                                <span class="iseo-media-check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg></span>
                                <span class="iseo-media-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M17 8l-5-5-5 5"/><path d="M12 3v12"/></svg></span>
                                <span class="iseo-media-copy">
                                    <span class="iseo-media-title">Upload your own</span>
                                    <span class="iseo-media-desc">Use an image from your computer. Nothing is generated.</span>
                                </span>
                                <span class="iseo-media-cost free"><span class="iseo-cost-dot"></span>No credits used</span>
                            </label>
                        </div>
                        <!-- <div class="flex_imgae_gereater_radio">
                            <div class="col" style="text-align: left; margin-top: 30px;">
                                <label for="AI_image" class="style_imgae_gereater_radio">
                                    <input class="for_styling_generate_image_radio_buttons" type="radio" name="aiImage"
                                        onclick="SeedHide()" value="AI_image" id="AI_image">
                                    <span class="custom-radio"></span>
                                    Generate AI Image Based On Title
                                </label>
                            </div>
                            <div class="col" style="text-align: left; margin-top: 30px;">
                                <label for="manually_promt_image" class="style_imgae_gereater_radio">
                                    <input class="for_styling_generate_image_radio_buttons" type="radio" name="aiImage"
                                        value="manually_promt_image" id="manually_promt_image">
                                    <span class="custom-radio"></span>
                                    Generate AI Image - Edit Prompt
                                </label>
                            </div>
                            <div class="col" style="text-align: left; margin-top: 30px;">
                                <label for="Manually_image" class="style_imgae_gereater_radio">
                                    <input class="for_styling_generate_image_radio_buttons" type="radio" name="aiImage"
                                        onclick="SeedShow()" value="Manually_image" id="Manually_image">
                                    <span class="custom-radio"></span>
                                    Manually Upload Image
                                </label>
                            </div>
                        </div> -->

                        <div id="AI_image_div" class="iseo-media-panel" style="display:none;">
                            <div class="iseo-title-gen">
                                <div class="iseo-for-line iseo-title-lead">Click the button below to generate your cover image.</div>
                                <div class="iseo-title-image" id="iseo-preview-title">
                                    <div id="ai-image-display"></div>
                                </div>
                                <div class="iseo-title-actions" id="AIrefreshOption">
                                    <button type="button" class="style_next_button_in_popup" onclick="return refreshAIImage()" style="padding: 12px 30px !important;">Generate AI image</button>
                                    <span class="iseo-hint">Costs 1 image credit when you press Generate.</span>
                                </div>
                            </div>
                            <input type="hidden" id="AI-Image-uploaded-path" name="AI-Image-uploaded-path">
                        </div>

                        <div id="manually_image_div" class="iseo-media-panel" style="display:none;">
                            <div class="improve-seo-upload-box">
                                <h2 class="frg-drp">Drag & Drop Your File here <br> <span> or </span></h2>
                                <label for="upload-image-button">Choose a File</label>
                                <p style="font-size: 14px; color: #adb5bd;">from your Storage</p>
                                <input type="file" id="upload-image-button" name="Manually_image">
                                <div id="manually-image-display"></div>
                                <input type="hidden" id="manually-image-uploaded-path"
                                    name="manually-image-uploaded-path">



                            </div>
                        </div>



                        <div class="iseo-media-panel" id="Prompt_to_create_Dalle_Image"
                            style="display: none;">

                            <div id="manually_promt" style="margin: 0;">
                                <div class="textarea_in_image_genration_center">
                                    <textarea class="form-control" id="manually_promt_for_image" rows="7"
                                        style="background-color:white !important; padding: 20px 20px 0px 20px; width: 100% !important;"
                                        name="manually_promt_for_image" onkeypress="return countContent()"
                                        OnBlur="LimitText(this,1000,3)"></textarea>
                                    <span id="error_manually_promt_for_image"></span>
                                </div>
                                <div class="generate-data-widt-ai">
                                    <!-- <h3> Retrieving Article Size... </h3>-->
                                    <!-- <img id="hide_older_genrated_image_on_step3"
                                    src="<?php echo WT_URL . '/assets/images/83d0e43cd6c5b0bd5633c1a8567f877a.jpeg' ?>"
                                    alt="ep_arrow-rights"> -->
                                    <div id="prompt_image_div" class="col-md-12" style="display:none;">
                                        <div class="iseo-for-line iseo-title-lead">Click the image below to view it at full size.</div>
                                        <div id="ai-with-prompt-image-display" style="margin-bottom: 10px;"></div>

                                        <input type="hidden" id="AI-Prompt-Image-uploaded-path"
                                            name="AI-Prompt-Image-uploaded-path">
                                    </div>
                                </div>

                                <input type="button" name="generate_i_image" class="styling_post_page_action_buttons "
                                    id="generate_i_image" value="Generate AI Image" style="margin: 10px auto;" />
                            </div>
                        </div>

                    </div>

                </div>

                <!-- option 3 -->


                <!-- option 4 -->
                <div class="data">
                    <div class="generate-data generate-data--preview">

                        <textarea class="form-control" id="showmydataindivText" rows="1" style="opacity: 0;"></textarea>

                        <!-- Two-column layout: the content preview fills the left column and the
                             Approve / Regenerate stack sits in a right-hand column beside it (revealed
                             once content exists via .is-visible). -->
                        <div class="iseo-preview-row">
                            <div class="iseo-preview-main">
                                <!-- Collapsed preview: shows the top of the article and fades out. Click it
                                     (or the button below) to open the full content in a new tab. -->
                                <div class="iseo-preview-collapse" onclick="iseoOpenFullPreview()"
                                    title="Open the full preview in a new tab">
                                    <div id="showmydataindiv1" name="showmydataindiv1"
                                        style="display: block;max-width: 100%;"></div>
                                </div>
                                <div class="iseo-preview-expand-row">
                                    <button type="button" class="iseo-open-full-preview is-hidden"
                                        onclick="iseoOpenFullPreview(); return false;">&#10530; Open full preview in a new tab</button>
                                </div>

                                <!-- Single, centred Regenerate button (#generateapivalue, relabelled after the
                                     first generation). The old in-preview "Approve Content" button was removed:
                                     the wizard's own bottom nav button ("Approve Content" on this step) advances
                                     the flow, so it was redundant. The hint tells the user how to get different
                                     content — go back and change their inputs. -->
                                <div class="iseo-content-actions iseo-content-actions-bottom">
                                    <input type="button" name="genaipost" class="iseo-content-btn iseo-regenerate-content"
                                        id="generateapivalue" value="Generate AI Post" />
                                </div>
                                <p class="iseo-regen-hint">Want a different result? Go back to <b>Step&nbsp;1</b>, tweak your inputs, then regenerate.</p>
                            </div>
                        </div>
                        <input type="hidden" name="ai_tittle" id="ai_title" />
                        <span style="display:none;"><input type="checkbox" value="1" id="for_testing_only" name="for_testing_only"></span>
                        <input type="hidden" name="AI_Title" id="AI_Title">
                        <input type="hidden" name="AI_descreption" id="AI_descreption">
                    </div>
                </div>
                <!-- option 5 -->
                <div class="data meta-data">
                    <div class="seo-form-field">
                        <label for="meta_title">Meta Title</label>
                        <input type="text" id="meta_title" name="meta_title" placeholder="Enter title..." />
                        <span class="iseo-meta-count" id="meta_title_count" data-max="60" data-min="0">0 / 60</span>
                    </div>
                    <div class="seo-form-field">
                        <label for="meta_descreption">Meta Description</label>
                        <textarea id="meta_descreption" name="meta_descreption"
                            placeholder="Enter description...."></textarea>
                        <span class="iseo-meta-count" id="meta_descreption_count" data-max="160" data-min="150">0 / 160</span>
                    </div>
                    <p class="iseo-meta-note">
                        <strong>Note:</strong> If you have another SEO plugin installed on this website, the
                        meta title and meta description for this post might be generated according to those SEO
                        plugins, and may differ and override the meta title and description above.
                        <a href="https://account.improveseoplugin.com/tutorial" target="_blank" rel="noopener noreferrer">Learn More</a>
                    </p>
                </div>

                <!-- option 6 — Project Name & Category -->
                <div class="data project-name-data">
                    <div class="seo-form-field">
                        <label for="modal_project_name">Project Name</label>
                        <input type="text" id="modal_project_name" name="modal_project_name" class="form-control" placeholder="Enter project name...">
                        <span>Auto-filled from your keyword — you can edit it.</span>
                    </div>

                    <div class="category-selection-section" style="margin-top: 20px;">
                        <h3 style="font-size:18px; font-weight:400; color:rgba(80,87,94,0.8); margin-bottom:10px; text-align:left;">Assign Categories</h3>
                        <div class="bulk-category-box" style="padding: 16px !important; margin-bottom: 0 !important;">
                            <div class="bulk-category-list" id="single-modal-category-list" style="display:flex; flex-wrap:wrap; gap:10px; margin-bottom:16px;">
                                <?php
                                $args = array("hide_empty" => 0, "type" => "post", "orderby" => "name", "order" => "ASC");
                                $cats = get_categories($args);
                                foreach ($cats as $category) {
                                    $checked  = ($category->slug === 'improve-seo') ? 'checked' : '';
                                    $disabled = ($category->slug === 'improve-seo') ? 'onclick="return false"' : '';
                                    $style    = ($category->slug === 'improve-seo') ? ' style="background:#1C7293;color:white;"' : '';
                                    echo '<label class="bulk-category-item"' . $style . '>
                                        <input type="checkbox" name="modal_single_cats[]" value="' . esc_attr($category->term_id) . '" ' . $checked . ' ' . $disabled . '>
                                        <span>' . esc_html($category->name) . '</span>
                                    </label>';
                                }
                                ?>
                            </div>
                            <div class="add-category-section">
                                <h3 style="font-size:18px; font-weight:400; color:rgba(80,87,94,0.8); margin-bottom:10px; text-align:left;">Create New Category</h3>
                                <div style="display:flex; gap:10px; align-items:center;">
                                    <input type="text" id="new_category_name_single" placeholder="Enter category name">
                                    <button type="button" id="add_category_single_btn">Add Category</button>
                                </div>
                                <p id="category_add_message_single" style="margin-top:8px; font-size:13px;"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <div class="btn-dev">
            <button id="prevStepButton"> <img
                    src="<?php echo WT_URL . '/assets/images/latest-images/ep_arrow-left.svg' ?>" alt="ep_arrow-left">
                Previous</button>
            <button id="nextStepButton" class="style_next_button_in_popup">Next <img
                    src="<?php echo WT_URL . '/assets/images/latest-images/ep_arrow-rights.svg' ?>"
                    alt="ep_arrow-rights"> </button>
        </div>
    </div>

</div>

<div class="modal fade" id="exampleModal2" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="false" aria-modal="false">

    <div id="loadingImage" style="display:none ;" class="overlay2">

        <!-- <img src="' . home_url('/') . 'wp-content/plugins/jobseq_jobs_pugin/assets/image/loader.gif" alt="Loading..."> -->
    </div>
    <div id="loadingAIData" style="display:none;" class="overlay_ai_data"></div>

    <div id="loadingAIImage" style="display:none;" class="overlay_ai_image"></div>

    <div class="improveseo-bulk-ai_multi">
        <div class="singlepost-title_multi">
            <h1><img src="<?php echo WT_URL . '/assets/images/latest-images/iconoir_sparks-solid.svg' ?>"
                    alt="iconoir_sparks"> Bulk Create AI Posts</h1>
            <div class="singlepost-close_multi"><img id="close_bulk_post"
                    src="<?php echo WT_URL . '/assets/images/latest-images/akar-icons_cross.svg' ?>" alt="icons_cross">
            </div>
        </div>
        <form id="pop_up_multi_form" action="multipost_form_submit" method="post" class="pop_up_multi_form">
            <div class="steps_multi">
                <div class="line-step_multi">
                    <!-- Step 1 -->
                    <div class="percent_multi active_multi">
                        <div class="step_multi">
                            <div class="circle_multi">1</div>
                            <p>Keyword & <br>Post Title</p>
                        </div>
                    </div>
                    <!-- Step 2 -->
                    <div class="percent_multi">
                        <div class="step_multi">
                            <div class="circle_multi">2</div>
                            <p>Content <br>Settings</p>
                        </div>
                    </div>
                    <!-- Step 3 -->
                    <div class="percent_multi">
                        <div class="step_multi">
                            <div class="circle_multi">3</div>
                            <p>Add <br>Media</p>
                        </div>
                    </div>
                    <!-- Step 4 -->
                    <div class="percent_multi">
                        <div class="step_multi">
                            <div class="circle_multi">4</div>
                            <p>Select <br>Category</p>
                        </div>
                    </div>
                    <!-- Step 5 -->
                    <div class="percent_multi">
                        <div class="step_multi">
                            <div class="circle_multi">5</div>
                            <p>Meta Title & <br>Description</p>
                        </div>
                    </div>
                    <!-- Step 6 -->
                    <div class="percent_multi">
                        <div class="step_multi">
                            <div class="circle_multi">6</div>
                            <p>Publish <br>Settings</p>
                        </div>
                    </div>
                    <!-- Step 7 -->
                    <div class="percent_multi">
                        <div class="step_multi">
                            <div class="circle_multi">7</div>
                            <p>Finalize</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="improveseo-sections_multi">
                <!-- Step 1 Content -->
                <div class="data_multi dataJSmulti_multi">
                    <div class="bulk-widths1170_multi">
                        <div class="improve-seo-form-global_multi">
                            <div class="form-group">
                                <label style="padding-left:20px;" for="keyword_list_name">Create New or Select an exisiting keyword list.</label>
                                <p style="font-size: 14px; color: #666; padding-left: 20px; margin-top: 5px;">
                                    Keyword list allows you to generate posts in bulk, one post per keyword.
                                </p>
                                <p style="font-size: 14px; color: #0073aa; padding-left: 20px; margin-top: 8px; margin-bottom: 12px;">
                                    To create a new keyword list, click 
                                    <a href="<?php echo admin_url('admin.php?page=improveseo_keyword_generator'); ?>" 
                                       target="_blank" 
                                       style="color: #0073aa; text-decoration: underline; font-weight: 500;">
                                        Generate Keywords
                                    </a>. 
                                    Once you are done, 
                                    <a href="javascript:void(0);" 
                                       id="refresh_keyword_lists" 
                                       style="color: #0073aa; text-decoration: underline; font-weight: 500; cursor: pointer;">
                                        click here to refresh
                                    </a>.
                                </p>
                                <select id="keyword_list_name" name="keyword_list_name"
                                    class="form-control bulk_post_input_style"
                                    style="max-width: 100% !important; width: 100%; padding: 10px 20px !important;">
                                    <option value="">Select a Keyword List</option>
                                    
                                    <?php echo $html_key_to_send; ?>
                                </select>
                                <span id="error_keyword_list_name" style="color: red;"></span>
                            </div>
                            <div class="form-group" id="keyword_list_container" style="display: none;">
                                <label style="padding-left:20px;" for="keyword_list">Keywords (at least one)</label>
                                <textarea id="keyword_list" name="keyword_list" class="form-control" rows="10"
                                    style="max-width: 100% !important; width: 100%;"></textarea>
                                <div id="keyword_count"></div>
                            </div>
                            <div id="create_keyword_container"
                                style="display: none; max-width: 100% !important; width: 100%; padding-bottom:15px;">
                                <label style="padding-left:20px;"> How do you want to create a new list?</label><br>
                                <select id="create_keyword" name="create_keyword" class="form-control"
                                    style="max-width: 100% !important; width: 100%;    padding: 10px 20px !important;">
                                    <!-- <option value="none">Select</option> -->
                                    <option value="copy_paste">Copy & Paste</option>
                                    <option value="google_suggestion">Generate Google Suggest KW list
                                    </option>
                                    
                                </select>
                                <div id="copy_paste_container" style="width:100%;"></div>
                                <div id="google_suggestion_container" style="width:100%;"></div>
                                <div id="ai_suggestion_container" style="width:100%;"></div>
                            </div>
                            <div id="tone_of_voice">
                                <label style="padding-left:20px;" for="cotnt_type"> Tone of Voice</label>
                                <select class="form-control" name="content_type" id="cotnt_type"
                                    style="max-width: 100% !important; width: 100%;    padding: 10px 20px !important;">
                                    <!-- <option value="">Tone of Voice</option> -->
                                    <option value="friendly">Friendly</option>
                                    <option value="professional">Professional</option>
                                    <option value="informational">Informational</option>
                                    <option value="transactional">Transactional</option>
                                    <option value="inspirational">Inspirational</option>
                                    <option value="neutral">Neutral</option>
                                    <option value="witty">Witty</option>
                                    <option value="casual">Casual</option>
                                    <option value="authoritative">Authoritative</option>
                                    <option value="encouraging">Encouraging</option>
                                    <option value="persuasive">Persuasive</option>
                                    <option value="poetic">Poetic</option>
                                </select>
                                <span id="error_cotnt_type" style="color: red;"></span>
                                <div class="form-group col-md-1"></div>
                            </div>
                            <label style="padding-left:20px;" for="existing_select">Select Title Type</label>
                            <select id="existing_select" name="select_exisiting_options" class="form-control"
                                style="max-width: 100% !important; width: 100%;    padding: 10px 20px !important;">
                                <!-- <option value="">Select</option> -->
                                <option value="seed_option2">Smart Title (AI-Generated)</option>
                                <option value="seed_option3">Question-Style Title (AI-Generated)</option>
                                <option value="seed_option1">Exact Keyword as Title</option>
                            </select>
                            <span id="error_existing_select" style="color: red;"></span>
                            <div class="form-group col-md-12" style="padding: 20px 0px !important;">
                                <label style="padding-left:20px;" for="sel1">Details to Include <a href="#"
                                        class="underline_none_for_a_tag"
                                        data-toggle="Please ensure the information you input aligns with the Main Keyword and Title. For example, information about dogs should not be added if you are writing about roofing."
                                        title="Please ensure the information you input aligns with the Main Keyword and Title. For example, information about dogs should not be added if you are writing about roofing.">
                                        <div class="dashicons dashicons-info-outline" aria-hidden="true">
                                            <br>
                                        </div>
                                    </a></label>
                                <textarea class="form-control" id="exampleFormControlTextarea1" rows="3"
                                    name="details_to_include" style=" max-width: 100% !important; width: 100%;"
                                    onkeypress="return countContent()" OnBlur="LimitText(this,1000,1)"></textarea>

                                <div class="BasicForm__row mb-3"
                                    style="max-width:100%; text-align:end; justify-content: end;  padding:15px 0px;">
                                    <input type="button" style="margin-top:20px;" onclick="return SaveResultsButton();"
                                        class="styling_post_page_action_buttons only_for_mobile_style"
                                        value="AI Generate Context Based On Keyword List">
                                    <span id="countContent"></span>
                                </div>

                            </div>
                            <div style="margin-top: 20px;" class="show_lists"><?php echo $list_to_send; ?> </div>
                            <textarea placeholder="Context Prompt"
                                style="margin-top: 20px; max-width: 100% !important; width: 100%; display: none; resize:none;"
                                class="form-control" name="existing_keyword"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Step 2 Content -->
                <div class="data_multi">
                    <div class="bulk-widths1170_multi">
                        <div class="improve-seo-form-global_multi">
                            <div class="row">

                                <div class="form-group col-md-12">
                                    <label style="padding-left:20px;" for="sel1">Article size</label>
                                    <select class="form-control" name="nos_of_words" required
                                        style="max-width: 100% !important;  padding: 10px 20px !important;"
                                        id="post_size_bulk">

                                        <option value="600 to 1200 words">Small (600 to 1200 words) </option>
                                        <option value="1200 to 2400 words">Medium (1200 to 2400 words) </option>
                                        <option value="2400 to 3600 words">Large (2400 to 3600 words)</option>

                                    </select>
                                </div>
                            </div>
                            <div class="row" style="display: none;">

                                <div class="form-group col-md-12">

                                    <input type="text" id="post_size_select_bulk" readonly
                                        style="width: 100% !important;  padding: 10px 20px !important;"
                                        value="600-1200 words">

                                </div>
                            </div>
                            <div class="row only_for_multi_mobile">
                                <div class="form-group col">
                                    <label style="padding-left:20px;" for="sel1">Point of View</label>
                                    <select class="form-control" name="point_of_view"
                                        style="max-width: 100% !important;  padding: 10px 20px !important;">

                                        <option value="Whatever is suited to keyword">Auto (AI Decides)</option>
                                        <option value="Second Person (you,your,yours)">Speaking to the Reader ("you", "your")</option>
                                        <option value="First person plural (we,us,our,ours)">Business Voice ("we", "our")</option>
                                        <option value="First person singular (I,me,my,mine)">Personal Voice ("I", "my")</option>

                                    </select>
                                </div>
                                <div class="form-group col">
                                    <label style="padding-left:20px;" for="language">Select Language</label>
                                    <select class="form-control" name="content_lang" id="language"
                                        style="max-width: 100% !important;  padding: 10px 20px !important;">

                                        
                                        <option value="US English">English (US)</option>
                                        <option value="UK English">English (Uk)</option>
                                        <option value="German">German (De)</option>

                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-12">
                                    <div class="seo-form-field">
                                        <label for="call_to_action_multi">Call to Action Text<a href="#"
                                                class="underline_none_for_a_tag" data-toggle="Information"
                                                title="Information">
                                                <div class="dashicons dashicons-info-outline" aria-hidden="true"><br>
                                                </div>
                                            </a></label>
                                        <textarea class="form-control" id="call_to_action_multi" rows="3" name="call_to_action"
                                            onkeypress="return countContentCallToAction()" onblur="LimitText(this,1000,2)"
                                            placeholder="What action would you like the reader of your content to take?"
                                            style="max-width: 100% !important;"></textarea><span
                                            id="countContentCallToAction"></span>
                                    </div>
                                    <div class="seo-form-field" style="margin-top:30px;">
                                        <label for="cta_url_multi">Call to Action URL <small style="color:#888; font-weight:normal; margin-left:6px;">(optional)</small></label>
                                        <input type="text" id="cta_url_multi" name="cta_url" class="form-control"
                                            placeholder="https://example.com/contact" style="max-width:100% !important;">
                                        <span id="error_cta_url_multi" style="color:red; display:block; padding-left:20px;"></span>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3 Content -->
                <div class="data_multi">
                    <div class="seo-slide-steps-fours_multi bulk-boxx_multi">
                        <!-- New per-keyword image selection container -->
                        <div id="keyword-image-selection-container" style="display:none; padding: 20px;">
                            <h3 style="margin-bottom: 20px; font-size: 18px; font-weight: 500; color: rgba(80, 87, 94, 0.8);">Select Image Method for Each Keyword</h3>
                            <div id="keyword-image-list">
                                <!-- Will be populated dynamically when Step 3 loads -->
                            </div>
                        </div>
                        
                        <!-- Keep old radio buttons hidden as fallback (for backwards compatibility) -->
                        <div id="old-image-selection" style="display:none;">
                            <div class="seo-slide-steps-fours bulk-boxx">
                                <div class="radio-container">
                                    <input type="radio" name="aiImage" class="show_hide_content_in_bulk_part_3"
                                        value="AI_image_one" id="AI_image_multi" onclick="multiple_image_div();">
                                    <label for="AI_image_multi">Generate AI Image Based On Title</label>

                                    <input type="radio" name="aiImage" value="Multiple_images"
                                        onclick="SelectexisitingHide();" id="Multiple_images">
                                    <label for="Multiple_images">Upload Your Own Images (Up to 10)</label>
                                </div>
                            </div>
                            <div class="form-group col-md-12" style="margin: 0 0 0 0;">
                                <div id="multiple_image_div" style="display:none;">
                                    <form id="uploadForm">
                                        <div class="improve-seo-upload-box">
                                            <h2 class="frg-drp">Drag & Drop Your File here <br> <span> or </span></h2>
                                            <input type="file" id="images" name="images[]" multiple>
                                            <div
                                                style="width: 100%; display: flex; justify-content: center; align-items: center; flex-direction: column;">
                                                <label style="max-width: max-content;"
                                                    class="styling_post_page_action_buttons2 styling_post_page_action_buttons"
                                                    for="images">Choose a File</label>
                                                <div id="preview"></div>
                                                <div id="response"></div>
                                                <div id="hiddenInputs"></div>
                                                <button style="max-width: max-content; padding: 5px 54px !important; "
                                                    type="button" class="styling_post_page_action_buttons"
                                                    id="uploadBtn">Upload</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div id="ai-image-generating" class="ai-image-content active text-center ok-aii">
                                <h3> Ok, AI images will be generated based on the post titles. </h3>
                                <p> <strong> Note: </strong> a preview of the images is not available yet at this step. </p>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Step 4 Content -->
                <div class="data_multi">
                    <div class="fourth_ttepss_multi">
                        <div class="category-selection-section">
                            <h2 style="padding-left:20px; margin-bottom: 20px;">Assign Categories to Posts</h2>
                            
                            <!-- Category Selection -->
                            <div class="bulk-category-box">
                                <h3>Select Categories</h3>
                                <div class="bulk-category-list" id="bulk-category-list" style="display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 25px;">
                                    <?php
                                    $args = array(
                                        "hide_empty" => 0,
                                        "type" => "post",
                                        "orderby" => "name",
                                        "order" => "ASC"
                                    );
                                    $cats = get_categories($args);
                                    foreach ($cats as $category) {
                                        $checked = '';
                                        $disabled = '';
                                        
                                        if ($category->slug == "improve-seo") {
                                            $checked = 'checked';
                                            $disabled = 'onclick="return false"';
                                        }
                                        
                                        echo '<label class="bulk-category-item">
                                            <input type="checkbox" name="cats[]" value="' . $category->term_id . '" ' . $checked . ' ' . $disabled . '>
                                            <span>' . $category->name . '</span>
                                        </label>';
                                    }
                                    ?>
                                </div>
                                
                                <!-- Add New Category -->
                                <div class="add-category-section">
                                    <h3>Create New Category</h3>
                                    <div style="display: flex; gap: 12px; align-items: center;">
                                        <input type="text" id="new_category_name_bulk" placeholder="Enter category name">
                                        <button type="button" id="add_category_bulk_btn">
                                            Add Category
                                        </button>
                                    </div>
                                    <p id="category_add_message_bulk" style="margin-top: 12px; color: #46b450; font-size: 14px;"></p>
                                </div>
                            </div>
                            
                            <p style="padding-left: 20px; color: #666; font-size: 14px; margin-top: 10px;">
                                <strong>Note:</strong> Selected categories will be assigned to all posts in this bulk project. You can select multiple categories.
                            </p>
                        </div>
                        <!-- <div class="fourth_ttepss_outer_multi">
                            <div class="fourth_ttepss-lft_multi">Testimonial:</div>
                            <div class="fourth_ttepss-rgt_multi">
                                <div class="category-box_multi">
                                    <div class="category-list_multi">
                                        <span class="category_multi active_multi">Test 1</span>
                                        <span class="category_multi">Testimonial 1</span>
                                        <span class="category_multi">Testimonial 2</span>
                                        <span class="category_multi">SAT testimonial</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="fourth_ttepss_outer_multi">
                            <div class="fourth_ttepss-lft_multi">Button:</div>
                            <div class="fourth_ttepss-rgt_multi whit-bbg_multi">
                                <div class="category-box_multi">
                                    <div class="category-list_multi">
                                        <span class="category_multi active_multi">Test button</span>
                                        <span class="category_multi">Janaki button</span>
                                        <span class="category_multi">Free trial button</span>
                                        <span class="category_multi">Janaki test</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="fourth_ttepss_outer_multi">
                            <div class="fourth_ttepss-lft_multi">Google map:</div>
                            <div class="fourth_ttepss-rgt_multi whit-bbg_multi">
                                <div class="category-box_multi">
                                    <div class="category-list_multi">
                                        <span class="category_multi active_multi">Google map</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="fourth_ttepss_outer_multi">
                            <div class="fourth_ttepss-lft_multi">Video:</div>
                            <div class="fourth_ttepss-rgt_multi whit-bbg_multi">
                                <div class="category-box_multi">
                                    <div class="category-list_multi">
                                        <span class="category_multi active_multi">Video 1</span>
                                        <span class="category_multi">Video 2</span>
                                        <span class="category_multi">Mr test free trial video</span>
                                        <span class="category_multi">Kaizen- mads</span>
                                    </div>
                                </div>
                            </div>
                        </div> -->
                    </div>
                </div>

                <!-- Step 5 Content -->
                <div class="data_multi meta-data_multi text-center_multi height-adjusts_multi">
                    <h4>An SEO optimized meta title and meta description will be automatically “AI Generated”. A preview
                        is
                        not available.</h4>
                </div>

                <!-- Step 6 Content -->
                <div class="data_multi">
                    <div class="seo-slide-steps-fours_multi seps-six_multi">
                        <h2 style="padding-left:20px;">Define Save & Publish Preference <img
                                src="<?php echo WT_URL . '/assets/images/latest-images/nfo-filledss.svg' ?>" alt="info">
                        </h2>
                        <div class="radio-container_multi seps-six-col_multi">
                            <div class="schedule_posts_parent">
                                <div class="flex_schedule_radio">
                                    <div class="col_schedule">
                                        <label class="style_schedule_radio">
                                            <input type="radio" name="schedule_posts" value="schedule_all_posts"
                                                id="AI_image">&nbsp;&nbsp;Publish all selected posts immediately
                                        </label>
                                    </div>
                                    <div class="col_schedule">
                                        <label class="style_schedule_radio">
                                            <input type="radio" name="schedule_posts" value="draft_posts"
                                                id="AI_image">&nbsp;&nbsp;Save all selected posts in draft mode, so you
                                            can review them before
                                            publishing
                                        </label>
                                    </div>
                                    <div class="col_schedule">
                                        <label class="style_schedule_radio">
                                            <input type="radio" name="schedule_posts" value="schedule_posts_input_wise"
                                                id="schedule_posts_input_wise">&nbsp;&nbsp;Create a publishing schedule
                                            for the selected posts (if you don’t want to
                                            publish them all at once)
                                        </label>
                                    </div>
                                </div>
                                <span id="error_schedule_posts" style="color:red; display:block; padding-left:20px;"></span>
                            </div>
                            <div class="defines-saves_multi">
                                <div class="col-md-12" id="number_of_post_schedule_box"
                                    style="display:none; padding: 0px !important;">
                                    <span>
                                        <Input type="number" name="number_of_post_schedule" id="number_of_post_schedule"
                                            style=" padding: 8px 23px !important;" Placeholder="Number of post">
                                        <select class="schedule_frequency" name="schedule_frequency"
                                            style="padding: 8px 20px !important;">
                                            <option value="per_day" selected>Per Day</option>
                                            <option value="per_week">Per Week</option>
                                        </select></span>
                                    <p id="error_number_of_post_schedule" style="color: red;"></p>
                                </div>
                            </div>
                        </div>

                        <h2 style="padding-left:20px;">Assign Author</h2>
                        <div style="padding: 0 20px 20px;">
                            <?php echo $all_auths; ?>
                        </div>

                        <!-- <h2 style="padding-left:20px;">Choose or Create Category</h2>
                        <div class="category-box_multi">
                            <div class="cta-check_multi clearfix ">
                                <?php //echo $select_to_send; ?>
                            </div>
                            <div class="add_cat">
                                <form method="post" action="add_category_form" class="form-wrap m-0">
                                    <div class="add-category">
                                        <input type="text" class="form-control" style="padding:20px !important;"
                                            name="cat_name_1" placeholder="Write Your Category" value=""
                                            aria-label="default input example" id="add_category_1">
                                        <input type="button"
                                            class="btn-trans btn btn-outline-primary btn-lg px-5 mx-auto"
                                            onclick="addcategory()" value="Add Category">
                                    </div>
                                </form>
                            </div>
                        </div> -->
                    </div>
                </div>

                <!-- Step 7 Content -->
                <div class="data_multi height-adjusts_multi">
                    <div class="bulk-widths1170_multi">
                        <div class="improve-seo-form-global_multi improve-seo-form-global_multi_step7">
                            <div class="seo-form-field_multi">
                                <label class="data-label_multi " style="padding: 0px 20px !important;"
                                    for="project_name">Project Name</label>
                                <input type="text" style=" padding: 10px 20px !important;" class="form-control"
                                    id="project_name" name="project_name" placeholder="Enter project name">
                                <span style="padding-left:20px; font-size:13px; color:#666;">Auto-filled from your keyword list — you can edit it.</span>
                            </div>
                            <div class="seo-form-field_multi" style="padding-left: 20px;">
                                <p class="font-20_multi" style="color:#0061B6;">
                                    <strong>Next:</strong> Once you click the submit button below, all posts within this
                                    project will be generated in the background. We&rsquo;ll email you at
                                    <strong id="iseo-notif-email">the address registered to your ImproveSEO account</strong>
                                    when they are finished.
                                </p>
                            </div>
                            <script>
                            // Fill the notification email live from the server so it's never a
                            // stale/hardcoded value baked into (possibly cached) page HTML.
                            (function () {
                                function run() {
                                    var el = document.getElementById('iseo-notif-email');
                                    if (!el || typeof jQuery === 'undefined' || typeof ajaxurl === 'undefined') { return; }
                                    jQuery.post(ajaxurl, {
                                        action: 'improveseo_get_notification_email',
                                        nonce: '<?php echo esc_js( wp_create_nonce('improveseo_notif_email_nonce') ); ?>'
                                    }).done(function (resp) {
                                        if (resp && resp.success && resp.data && resp.data.email) {
                                            el.textContent = resp.data.email;
                                        }
                                    });
                                }
                                if (document.readyState === 'loading') {
                                    document.addEventListener('DOMContentLoaded', run);
                                } else {
                                    run();
                                }
                            })();
                            </script>
                            <input type="submit" value="Submit" id="bulk_ai_post_submi_button">
                            <div class="seo-form-field_multi" style="padding-left: 20px;">
                                <p class="font-20_multi"><strong>Note:</strong> Based on your selections, this project
                                    will generate <span id="keywordcounts">0</span> AI posts. Generation runs in the
                                    background and should take up to <span id="keywordtime">a few minutes</span>.</p>
                            </div>
                            <script>
                            // Estimated time = number of posts × an upper-bound of ~3 minutes each,
                            // shown as a human-readable maximum ("X minutes" / "H hours M minutes")
                            // rather than a fractional-hour value like "0.2 hr".
                            window.formatEstimatedTime = function (postCount) {
                                var MAX_MINUTES_PER_POST = 3;
                                var total = Math.max(1, Math.ceil((parseInt(postCount, 10) || 0) * MAX_MINUTES_PER_POST));
                                if (total < 60) {
                                    return total + (total === 1 ? ' minute' : ' minutes');
                                }
                                var hours = Math.floor(total / 60);
                                var mins = total % 60;
                                var out = hours + (hours === 1 ? ' hour' : ' hours');
                                if (mins > 0) { out += ' ' + mins + (mins === 1 ? ' minute' : ' minutes'); }
                                return out;
                            };
                            </script>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <div class="btn-dev_multi">
            <button id="prevStepButton_multi"><img
                    src="<?php echo WT_URL . '/assets/images/latest-images/ep_arrow-left.svg' ?>" alt="prev">
                Previous</button>
            <button id="nextStepButton_multi" class="style_next_button_in_popup">Next <img
                    src="<?php echo WT_URL . '/assets/images/latest-images/ep_arrow-rights.svg' ?>" alt="next"></button>
        </div>

    </div>
</div>




<script>
    document.addEventListener("DOMContentLoaded", () => {
        let currentStep = 0;

        const nextStepButton = document.getElementById("nextStepButton");
        const prevStepButton = document.getElementById("prevStepButton");
        const close_single_post = document.getElementById("close_single_post");
        const close_generate_ai_popup = document.getElementById("close_generate_ai_popup");
        const close_bulk_post = document.getElementById("close_bulk_post");
        const generate_ai_popup_open = document.getElementById("generate_ai_popup_open");
        const open_single_AI_Post_popup = document.querySelectorAll(".open_single_AI_Post_popup");
        const open_bulk_AI_Post_popup = document.querySelectorAll(".open_bulk_AI_Post_popup");

        // Close Single Post Modal — also copy AI field values to hidden inputs
        if (close_single_post) {
            close_single_post.addEventListener('click', () => {
                if (typeof copyAIFieldsToHiddenInputs === 'function') copyAIFieldsToHiddenInputs();
                document.getElementById("exampleModal1").classList.add("hide_and_show_ai_popup");
            });
        }

        // Close Bulk Post Modal
        if (close_bulk_post) {
            close_bulk_post.addEventListener('click', () => {
                document.getElementById("exampleModal2").classList.add("hide_and_show_ai_popup");
            });
        }

        // Close Generate AI Popup Modal
        if (close_generate_ai_popup) {
            close_generate_ai_popup.addEventListener('click', () => {
                document.getElementById("exampleModal").classList.add("hide_and_show_ai_popup");
            });
        }
        
    var aiModalType = "<?php echo isset($ai_modal_type) ? esc_js($ai_modal_type) : 'default'; ?>";
    console.log("modal type in generate ai popup", aiModalType);
   
        
        // Open Generate AI Popup Modal
        if (generate_ai_popup_open) {
            
            generate_ai_popup_open.addEventListener('click', () => {
                switch (aiModalType) {
                    case 'bulk':
                        document.getElementById("exampleModal2").classList.remove("hide_and_show_ai_popup");
                        break;
                    case 'single':
                        document.getElementById("exampleModal1").classList.remove("hide_and_show_ai_popup");
                        break;
                    default:
                        document.getElementById("exampleModal").classList.remove("hide_and_show_ai_popup");
                        break;
                }
            });
        }

      







        function updateSteps() {
            document.querySelectorAll(".percent").forEach((step, index) => {
                step.classList.remove("completed", "active");
                if (index < currentStep) {
                    step.classList.add("completed");
                } else if (index === currentStep) {
                    step.classList.add("active");
                }
            });
        }



        nextStepButton.addEventListener("click", () => {
            if (currentStep < document.querySelectorAll(".percent").length) {



                prevStepButton.disabled = false;


            }
        });



        prevStepButton.addEventListener("click", () => {
            if (currentStep > 0) {

                nextStepButton.disabled = false;

                if (currentStep === 0) {
                    prevStepButton.disabled = true;
                }
            }
        });

        updateSteps();
        prevStepButton.disabled = true;
    });






    // document.addEventListener("DOMContentLoaded", () => {
    //     // DOM Elements
    //     const data = document.querySelectorAll('.data');
    //     const nextStepButton = document.getElementById("nextStepButton");
    //     const prevStepButton = document.getElementById("prevStepButton");
    //     const approve_content_check_box = document.getElementById('checkbox_need');
    //     const mainTitleArea = document.getElementById('maintitlearea');
    //     const forApproveValidation = document.getElementById('for_approve_content_validation');
    //     const stepInput = document.getElementById('step_value');

    //     // State Management
    //     let currentStep = 0;

    //     // Initialize the form
    //     initForm();

    //     // Core Functions
    //     function initForm() {
    //         updateDataDisplay();
    //         updateSteps();
    //         prevStepButton.disabled = true;
    //         nextStepButton.disabled = false; // Enabled by default

    //         // Special case for step 1 validation
    //         if (currentStep === 0) {
    //             nextStepButton.disabled = false;
    //         }
    //     }

    //     function updateDataDisplay() {
    //         data.forEach((item, index) => {
    //             item.classList.remove('dataJS');
    //             if (index === currentStep) item.classList.add('dataJS');
    //         });
    //     }

    //     function updateSteps() {
    //         document.querySelectorAll(".percent").forEach((step, index) => {
    //             step.classList.remove("completed", "active");
    //             if (index < currentStep) step.classList.add("completed");
    //             else if (index === currentStep) step.classList.add("active");
    //         });
    //     }

    //     function validateCheckbox(showError = false) {
    //         if (approve_content_check_box.checked) {
    //             resetValidationUI();
    //             return true;
    //         } else {
    //             if (showError) showValidationError();
    //             return false;
    //         }
    //     }

    //     function resetValidationUI() {
    //         mainTitleArea.style.border = '1px solid #D2D2D2';
    //         forApproveValidation.style.display = 'none';
    //     }

    //     function showValidationError() {
    //         mainTitleArea.style.border = '2px solid red';
    //         forApproveValidation.style.display = "block";
    //     }

    //     function updateButtonText() {
    //         const stepValue = parseInt(stepInput.value, 10);
    //         let buttonText = 'Next';

    //         if (stepValue === 3) {
    //             buttonText = 'Generate AI Post';
    //         } else if (stepValue === 4) {
    //             buttonText = 'Approve Content';
    //             nextStepButton.disabled = false;
    //             jQuery("#generateapivalue").trigger("click");
    //         } else if (stepValue === 5) {
    //             buttonText = 'Submit';
    //             nextStepButton.disabled = false;
    //         } else if (stepValue === 6) {
    //             saveFinalData();
    //             nextStepButton.disabled = true;
    //         }

    //         nextStepButton.innerHTML = `${buttonText} <img src="<?php echo WT_URL . '/assets/images/latest-images/ep_arrow-rights.svg' ?>" alt="arrow-right">`;
    //     }

    //     // Event Handlers
    //     approve_content_check_box.addEventListener('change', () => {
    //         if (currentStep === 0) {
    //             validateCheckbox(true);
    //             nextStepButton.disabled = !approve_content_check_box.checked;
    //         }
    //     });

    //     nextStepButton.addEventListener("click", () => {
    //         if (currentStep === 0 && !validateCheckbox(true)) {
    //             return;
    //         }

    //         if (currentStep >= data.length) return;

    //         // Step-specific validation

    //         // Progress to next step
    //         currentStep++;
    //         updateDataDisplay();
    //         updateSteps();

    //         // Update step tracking
    //         const stepValue = parseInt(stepInput.value, 10);
    //         if (!isNaN(stepValue)) {
    //             stepInput.value = stepValue + 1;
    //             updateButtonText();
    //         }

    //         // Enable previous button
    //         prevStepButton.disabled = false;
    //     });

    //     prevStepButton.addEventListener("click", () => {
    //         if (currentStep <= 0) return;

    //         // Go back to previous step
    //         currentStep--;
    //         updateDataDisplay();
    //         updateSteps();
    //         resetValidationUI();

    //         // Update step tracking
    //         const stepValue = parseInt(stepInput.value, 10);
    //         if (!isNaN(stepValue)) {
    //             stepInput.value = stepValue - 1;
    //             updateButtonText();
    //         }

    //         // Update button states
    //         nextStepButton.disabled = false;
    //         prevStepButton.disabled = (currentStep === 0);
    //     });
    // });






    // document.addEventListener("DOMContentLoaded", () => {
    //     // DOM Elements
    //     const data = document.querySelectorAll('.data');
    //     const nextStepButton = document.getElementById("nextStepButton");
    //     const prevStepButton = document.getElementById("prevStepButton");
    //     const approve_content_check_box = document.getElementById('checkbox_need');
    //     const mainTitleArea = document.getElementById('maintitlearea');
    //     const forApproveValidation = document.getElementById('for_approve_content_validation');
    //     const stepInput = document.getElementById('step_value');
    //     const seedSelect = document.getElementById("seed_select");

    //     // State Management
    //     let currentStep = 0;

    //     // Initialize the form
    //     initForm();

    //     // Core Functions
    //     function initForm() {
    //         updateDataDisplay();
    //         updateSteps();
    //         prevStepButton.disabled = true;
    //         updateNextButtonState();
    //     }

    //     function updateNextButtonState() {
    //         if (currentStep === 0) {
    //             if (seedSelect && seedSelect.value === "seed_option1") {
    //                 // Always enable for seed_option1
    //                 nextStepButton.disabled = false;
    //             } else {
    //                 // For other options, check checkbox state
    //                 nextStepButton.disabled = !approve_content_check_box.checked;
    //                 // Show validation error if needed
    //                 if (!approve_content_check_box.checked) {
    //                     showValidationError();
    //                 } else {
    //                     resetValidationUI();
    //                 }
    //             }
    //         } else {
    //             // For steps other than 0, always enable
    //             nextStepButton.disabled = false;
    //         }
    //     }

    //     function updateDataDisplay() {
    //         data.forEach((item, index) => {
    //             item.classList.remove('dataJS');
    //             if (index === currentStep) item.classList.add('dataJS');
    //         });
    //     }

    //     function updateSteps() {
    //         document.querySelectorAll(".percent").forEach((step, index) => {
    //             step.classList.remove("completed", "active");
    //             if (index < currentStep) step.classList.add("completed");
    //             else if (index === currentStep) step.classList.add("active");
    //         });
    //     }

    //     function validateCheckbox(showError = false) {
    //         if (seedSelect && seedSelect.value === "seed_option1") {
    //             return true;
    //         }

    //         if (approve_content_check_box.checked) {
    //             resetValidationUI();
    //             return true;
    //         } else {
    //             if (showError) showValidationError();
    //             return false;
    //         }
    //     }

    //     function resetValidationUI() {
    //         mainTitleArea.style.border = '1px solid #D2D2D2';
    //         forApproveValidation.style.display = 'none';
    //     }

    //     function showValidationError() {
    //         mainTitleArea.style.border = '2px solid red';
    //         forApproveValidation.style.display = "block";
    //     }

    //     function updateButtonText() {
    //         const stepValue = parseInt(stepInput.value, 10);
    //         let buttonText = 'Next';

    //         if (stepValue === 3) {
    //             buttonText = 'Generate AI Post';
    //         } else if (stepValue === 4) {
    //             buttonText = 'Approve Content';
    //             nextStepButton.disabled = false;
    //             jQuery("#generateapivalue").trigger("click");
    //         } else if (stepValue === 5) {
    //             buttonText = 'Submit';
    //             nextStepButton.disabled = false;
    //         } else if (stepValue === 6) {
    //             saveFinalData();
    //             nextStepButton.disabled = true;
    //         }

    //         nextStepButton.innerHTML = `${buttonText} <img src="<?php echo WT_URL . '/assets/images/latest-images/ep_arrow-rights.svg' ?>" alt="arrow-right">`;
    //     }

    //     // Event Handlers
    //     approve_content_check_box.addEventListener('change', () => {
    //         if (currentStep === 0) {
    //             updateNextButtonState();
    //         }
    //     });

    //     // Add seed select change handler
    //     if (seedSelect) {
    //         seedSelect.addEventListener("change", function () {
    //             if (currentStep === 0) {
    //                 if (this.value !== "seed_option1" && !approve_content_check_box.checked) {
    //                     showValidationError();
    //                 } else {
    //                     resetValidationUI();
    //                 }
    //                 updateNextButtonState();
    //             }
    //         });
    //     }

    //     nextStepButton.addEventListener("click", () => {
    //         if (currentStep === 0 && !validateCheckbox(true)) {
    //             return;
    //         }

    //         if (currentStep >= data.length) return;

    //         currentStep++;
    //         updateDataDisplay();
    //         updateSteps();

    //         const stepValue = parseInt(stepInput.value, 10);
    //         if (!isNaN(stepValue)) {
    //             stepInput.value = stepValue + 1;
    //             updateButtonText();
    //         }

    //         prevStepButton.disabled = false;
    //         updateNextButtonState();
    //     });

    //     prevStepButton.addEventListener("click", () => {
    //         if (currentStep <= 0) return;

    //         currentStep--;
    //         updateDataDisplay();
    //         updateSteps();
    //         resetValidationUI();

    //         const stepValue = parseInt(stepInput.value, 10);
    //         if (!isNaN(stepValue)) {
    //             stepInput.value = stepValue - 1;
    //             updateButtonText();
    //         }

    //         prevStepButton.disabled = (currentStep === 0);
    //         updateNextButtonState();
    //     });
    // });



    document.addEventListener("DOMContentLoaded", () => {
        // DOM Elements
        const data = document.querySelectorAll('.data');
        const nextStepButton = document.getElementById("nextStepButton");
        const prevStepButton = document.getElementById("prevStepButton");
        const approve_content_check_box = document.getElementById('checkbox_need');
        const mainTitleArea = document.getElementById('maintitlearea');
        const forApproveValidation = document.getElementById('for_approve_content_validation');
        const stepInput = document.getElementById('step_value');
        const seedSelect = document.getElementById("seed_select");

        // State Management
        let currentStep = 0;
        let hasInteracted = false; // Track if user has interacted with the form

        // Initialize the form
        initForm();

        // Core Functions
        function initForm() {
            updateDataDisplay();
            updateSteps();
            prevStepButton.disabled = true;
            resetValidationUI(); // Start with no validation errors
            updateNextButtonState();
        }

        function updateNextButtonState() {
            if (currentStep === 0) {
                if (seedSelect && seedSelect.value === "seed_option1") {
                    // Always enable for seed_option1
                    nextStepButton.disabled = false;
                } else {
                    // For other options that require AI title approval, keep the button
                    // enabled but do NOT surface the approval message here — that would fire
                    // prematurely on title-type selection. The message is only shown when the
                    // user actually clicks Next without approving (see nextStepButton handler).
                    resetValidationUI();
                }
            } else {
                // For steps other than 0, always enable
                nextStepButton.disabled = false;
            }
        }

        function updateDataDisplay() {
            data.forEach((item, index) => {
                item.classList.remove('dataJS');
                if (index === currentStep) item.classList.add('dataJS');
            });
        }

        function updateSteps() {
            document.querySelectorAll(".percent").forEach((step, index) => {
                step.classList.remove("completed", "active");
                if (index < currentStep) step.classList.add("completed");
                else if (index === currentStep) step.classList.add("active");
            });
        }

        function validateCheckbox(showError = false) {
            if (seedSelect && seedSelect.value === "seed_option1") {
                return true;
            }

            if (approve_content_check_box.checked) {
                resetValidationUI();
                return true;
            } else {
                if (showError) {
                    hasInteracted = true; // Mark as interacted when validation fails
                    showValidationError();
                }
                return false;
            }
        }

        function resetValidationUI() {
            mainTitleArea.style.border = '1px solid #D2D2D2';
            forApproveValidation.style.display = 'none';
        }

        function showValidationError() {
            mainTitleArea.style.border = '2px solid #FFC107';
            forApproveValidation.style.display = "block";
        }

        function autoPopulateProjectName() {
            var nameInput = document.getElementById('modal_project_name');
            if (!nameInput || nameInput.value.trim()) return;
            var keyword = (document.getElementById('seed_keyword') || {}).value || '';
            var now = new Date();
            var mm = String(now.getMonth() + 1).padStart(2, '0');
            var dd = String(now.getDate()).padStart(2, '0');
            var yyyy = now.getFullYear();
            var rand = Math.floor(100 + Math.random() * 900);
            nameInput.value = (keyword ? keyword + ' ' : '') + mm + '/' + dd + '/' + yyyy + ' #' + rand;
        }

        // Gate on the final ("Submit") step, same shape as the bulk wizard's
        // validateBulkProjectName()/validateBulkKeywordListForSubmit(). Submitting hands
        // #main_form to do_create_post, which requires name/title/content — without this
        // an incomplete project bounces off the server-side validator and the user is
        // redirected to a fresh, empty create screen with everything lost.
        function validateSinglePostBeforeSubmit() {
            var nameInput = document.getElementById('modal_project_name');
            if (!nameInput || !nameInput.value.trim()) {
                showImproveSEONotification(
                    'warning',
                    'Project Name Required',
                    'Please enter a project name for this post before submitting.',
                    null
                );
                nameInput && nameInput.focus();
                return false;
            }

            var generated = document.getElementById('showmydataindivText');
            if (!generated || !generated.value.trim()) {
                showImproveSEONotification(
                    'warning',
                    'Content Not Ready',
                    'Please generate and approve your post content before submitting.',
                    null
                );
                return false;
            }

            return true;
        }

        function updateButtonText() {
            const stepValue = parseInt(stepInput.value, 10);
            let buttonText = 'Next';

            if (stepValue === 3) {
                // On the Add Media step this button normally kicks off generation. But if content
                // was already generated (the user navigated back here), advancing to Step 4 won't
                // regenerate — so label it 'Next' to reflect that no generation will happen.
                var generatedArticle = jQuery("#showmydataindiv1").data("articleHtml");
                buttonText = (generatedArticle && jQuery.trim(generatedArticle)) ? 'Next' : 'Generate AI Post';
            } else if (stepValue === 4) {
                buttonText = 'Approve Content';
                nextStepButton.disabled = false;
                // Auto-generate only the first time we land on Step 4. If content already
                // exists (the user navigated back from a later step), preserve it instead
                // of regenerating — same as every other input the wizard keeps. The user
                // can still re-run it via the panel's "Re-Generate AI Post" button.
                var existingAIContent = jQuery("#showmydataindiv1").html();
                if (!existingAIContent || !existingAIContent.trim()) {
                    jQuery("#generateapivalue").trigger("click");
                } else if (typeof recomposePreviewWithCurrentImage === 'function') {
                    // Content is preserved, but the cover image may have changed in Step 3 —
                    // re-composite the preview + saved content with the current image selection.
                    recomposePreviewWithCurrentImage();
                }
            } else if (stepValue === 5) {
                buttonText = 'Next';
                nextStepButton.disabled = false;
            } else if (stepValue === 6) {
                buttonText = 'Submit';
                nextStepButton.disabled = false;
                autoPopulateProjectName();
            } else if (stepValue === 7) {
                saveFinalData();
                nextStepButton.disabled = true;
            }

            nextStepButton.innerHTML = `${buttonText} <img src="<?php echo WT_URL . '/assets/images/latest-images/ep_arrow-rights.svg' ?>" alt="arrow-right">`;
        }

        // Event Handlers
        approve_content_check_box.addEventListener('change', () => {
            hasInteracted = true; // Mark as interacted when checkbox changes
            if (currentStep === 0) {
                updateNextButtonState();
            }
        });

        // Add seed select change handler
        if (seedSelect) {
            seedSelect.addEventListener("change", function () {
                hasInteracted = true; // Mark as interacted when seed changes
                if (currentStep === 0) {
                    // Changing the title type should never surface the "Approve AI generated
                    // title" message on its own — that's premature. Clear any stale error and
                    // defer the approval gate to the Next click.
                    resetValidationUI();
                    updateNextButtonState();
                }
            });
        }

        // Helper to advance single post wizard by one step
        function advanceSingleStep() {
            currentStep++;
            updateDataDisplay();
            updateSteps();
            var sv = parseInt(stepInput.value, 10);
            if (!isNaN(sv)) {
                stepInput.value = sv + 1;
                updateButtonText();
            }
            prevStepButton.disabled = false;
            updateNextButtonState();
        }

        nextStepButton.addEventListener("click", () => {
            // Require a seed keyword before leaving step 1 — nothing downstream works without it.
            if (currentStep === 0) {
                const seedKeywordInput = document.getElementById('seed_keyword');
                const seedKeywordError = document.getElementById('error_seed_keyword');
                if (!seedKeywordInput || !seedKeywordInput.value.trim()) {
                    if (seedKeywordError) seedKeywordError.innerText = 'Please enter a seed keyword.';
                    seedKeywordInput && seedKeywordInput.focus();
                    return;
                }
                if (seedKeywordError) seedKeywordError.innerText = '';
            }

            // Enforce AI title approval: if in AI title generation mode (seed_select not 'seed_option1')
            // and approval checkbox not checked, show popup and move title to editable prompt.
            if (currentStep === 0 && seedSelect && seedSelect.value !== 'seed_option1' && !approve_content_check_box.checked) {
                showValidationError();
                // Shift current AI title into seed keyword prompt (if exists) for user edits
                const seedKeywordInput = document.getElementById('seed_keyword');
                if (seedKeywordInput && mainTitleArea && mainTitleArea.value) {
                    seedKeywordInput.value = mainTitleArea.value;
                }
                showImproveSEONotification(
                    'warning',
                    'Approve AI Title',
                    'Please approve or edit the AI generated title before continuing.',
                    null
                );
                mainTitleArea && mainTitleArea.focus();
                return;
            }

            if (currentStep === 0 && !validateCheckbox(true)) {
                showValidationError();
                return;
            }

            // Single Post: Check CONTENT credits at step 0
            if (currentStep === 0) {
                var savedHTML = nextStepButton.innerHTML;
                nextStepButton.disabled = true;
                nextStepButton.textContent = 'Validating...';
                checkSingleContentCredits()
                    .then(function() {
                        nextStepButton.disabled = false;
                        nextStepButton.innerHTML = savedHTML;
                        advanceSingleStep();
                    })
                    .catch(function() {
                        nextStepButton.disabled = false;
                        nextStepButton.innerHTML = savedHTML;
                    });
                return;
            }

            // Normalize + validate the Call to Action URL when leaving Step 2 (index 1) —
            // same gate as the bulk wizard: a bare domain gets https:// prepended and
            // written back; an invalid URL shows an inline error and keeps the wizard here.
            if (currentStep === 1 && !normalizeAndValidateCtaUrl('#cta_url', '#error_cta_url')) {
                return;
            }

            // Final step: this click is "Submit", and advancing runs saveFinalData(),
            // which posts the real create/publish form. Validate first so we never
            // hand a half-filled project to the controller.
            if (currentStep === data.length - 1 && !validateSinglePostBeforeSubmit()) {
                return;
            }

            if (currentStep >= data.length) return;

            advanceSingleStep();
        });

        prevStepButton.addEventListener("click", () => {
            if (currentStep <= 0) return;

            currentStep--;
            updateDataDisplay();
            updateSteps();
            resetValidationUI();

            const stepValue = parseInt(stepInput.value, 10);
            if (!isNaN(stepValue)) {
                stepInput.value = stepValue - 1;
                updateButtonText();
            }

            prevStepButton.disabled = (currentStep === 0);
            updateNextButtonState();
        });
    });




    document.querySelectorAll('input[name="imageOption"]').forEach((radio) => {
        radio.addEventListener('change', function () {
            document.querySelectorAll('.ai-image-content').forEach(div => div.classList.remove('active'));
            document.getElementById(this.value).classList.add('active');
        });
    });

    // Select option display setup

</script>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        let currentStep = 0;
        const steps = document.querySelectorAll(".percent_multi");
        const dataSections = document.querySelectorAll(".data_multi");
        const prevButton = document.getElementById("prevStepButton_multi");
        const nextButton = document.getElementById("nextStepButton_multi");

        function updateSteps() {
            steps.forEach((step, index) => {
                step.classList.remove("active_multi", "completed_multi");
                if (index < currentStep) {
                    step.classList.add("completed_multi");
                } else if (index === currentStep) {
                    step.classList.add("active_multi");
                }
            });
        }

        function updateDataDisplay() {
            dataSections.forEach((section, index) => {
                section.classList.remove("dataJSmulti_multi");
                if (index === currentStep) {
                    section.classList.add("dataJSmulti_multi");
                }
            });
        }

        function autoPopulateBulkProjectName() {
            var nameInput = document.getElementById('project_name');
            if (!nameInput || nameInput.value.trim()) return;
            var listName = jQuery('#keyword_list_name option:selected').text().trim();
            if (!listName || listName === 'Select a Keyword List') listName = '';
            var now = new Date();
            var mm   = String(now.getMonth() + 1).padStart(2, '0');
            var dd   = String(now.getDate()).padStart(2, '0');
            var yyyy = now.getFullYear();
            var rand = Math.floor(100 + Math.random() * 900);
            nameInput.value = (listName ? listName + ' ' : '') + mm + '/' + dd + '/' + yyyy + ' #' + rand;
        }

        function updateButtonText() {
            let buttonText = 'Next';
            const totalSteps = steps.length;

            if (currentStep === totalSteps - 1) { // Last step (step 6)
                buttonText = 'Submit';
                autoPopulateBulkProjectName();
            } else if (currentStep === totalSteps - 2) { // Last step (step 6)
                var text = jQuery('#keyword_list').val();

                // Split the text into lines
                var lines = text.split('\n');

                // Filter out empty lines
                var nonEmptyLines = lines.filter(function (line) {
                    return line.trim().length > 0;
                });

                // Count the non-empty lines
                var keywordCount = nonEmptyLines.length;

                // Update the text in the elements
                jQuery('#keywordcounts').text(keywordCount);
                jQuery('#keywordtime').text(window.formatEstimatedTime(keywordCount));
            }

            nextButton.innerHTML = `${buttonText} <img src="<?php echo WT_URL . '/assets/images/latest-images/ep_arrow-rights.svg' ?>" alt="arrow-right">`;
        }

        // Validate if keyword list has keywords
        function validateKeywordList() {
            var text = jQuery('#keyword_list').val();
            
            if (!text || text.trim().length === 0) {
                return false;
            }
            
            // Split by lines and filter empty ones
            var lines = text.split('\n');
            var nonEmptyLines = lines.filter(function(line) {
                return line.trim().length > 0;
            });
            
            return nonEmptyLines.length > 0;
        }
        
        // Step 6: a publish preference must be chosen before leaving the step.
        // Also enforces the "how many posts" input that belongs to the
        // schedule option, which the old step handler validated but the current
        // Next handler never called.
        function validateSchedulePosts() {
            var $error = jQuery('#error_schedule_posts');
            $error.text('');

            var chosen = jQuery('input[name="schedule_posts"]:checked').val();
            if (!chosen) {
                $error.text('Please choose how these posts should be saved or published.');
                jQuery('.schedule_posts_parent')[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false;
            }

            if (chosen === 'schedule_posts_input_wise') {
                var $count = jQuery('#number_of_post_schedule');
                var count = parseInt(($count.val() || '').trim(), 10);
                if (!count || count < 1) {
                    jQuery('#error_number_of_post_schedule')
                        .text('Please enter how many posts to publish per day/week.');
                    $count.focus();
                    return false;
                }
                jQuery('#error_number_of_post_schedule').text('');
            }

            return true;
        }

        // Clear the message as soon as the user makes a choice.
        jQuery(document).on('change', 'input[name="schedule_posts"]', function () {
            jQuery('#error_schedule_posts').text('');
        });

        // Initialize button state manager
        BulkSubmitButton.init(nextButton);

        nextButton.addEventListener("click", () => {
            // Validate keyword list and check CONTENT credits on step 0 (first step)
            if (currentStep === 0) {
                var keywordListName = jQuery('#keyword_list_name').val();
                
                // Check if a list is selected
                if (!keywordListName || keywordListName === '' || keywordListName === 'none') {
                    showImproveSEONotification(
                        'warning',
                        'Keyword List Required',
                        'Please select a keyword list or create a new one before proceeding.',
                        null
                    );
                    return;
                }
                
                // Check if selected list has keywords
                if (!validateKeywordList()) {
                    showImproveSEONotification(
                        'warning',
                        'Empty Keyword List',
                        'The selected keyword list is empty. Please add at least one keyword before proceeding.',
                        null
                    );
                    return;
                }
                
                // NEW: Check CONTENT GENERATION credits at step 1
                BulkSubmitButton.setValidating();
                
                const proceedToStep2 = () => {
                    if (currentStep < steps.length - 1) {
                        currentStep++;
                        updateSteps();
                        updateDataDisplay();
                        updateButtonText();
                        prevButton.disabled = false;
                        BulkSubmitButton.reset();
                    }
                };
                
                // Run content credit check
                checkBulkContentCredits()
                    .then((creditData) => {
                        console.log('✅ Content credit check passed:', creditData);
                        proceedToStep2();
                    })
                    .catch((error) => {
                        console.log('❌ Content credit check failed:', error);
                        BulkSubmitButton.setError();
                    });
                
                return; // Don't proceed automatically
            }
            
            // NEW: Check IMAGE credits when moving from Step 3 (index 2) to Step 4 (index 3)
            if (currentStep === 2) {
                // Set button to validating state
                BulkSubmitButton.setValidating();
                
                // Prevent default progression
                const proceedToNextStep = () => {
                    if (currentStep < steps.length - 1) {
                        currentStep++;
                        updateSteps();
                        updateDataDisplay();
                        updateButtonText();
                        prevButton.disabled = false;
                        BulkSubmitButton.reset();
                    }
                };
                
                // Run IMAGE credit check only
                checkBulkImageCredits()
                    .then((creditData) => {
                        // Image credits OK - proceed to next step
                        console.log('✅ Image credit check passed:', creditData);
                        proceedToNextStep();
                    })
                    .catch((error) => {
                        // Image credits insufficient or error - stay on current step
                        console.log('❌ Image credit check failed:', error);
                        BulkSubmitButton.setError();
                        // User already notified via popup
                    });
                
                return; // Don't proceed automatically
            }
            
            // Normalize + validate the Call to Action URL when leaving Step 2 (index 1).
            // Without this, an unschemed URL (e.g. "example.com") that used to sit in a
            // type="url" field silently blocked native form submission, leaving the final
            // "Processing..." button stuck forever.
            if (currentStep === 1) {
                if (!normalizeAndValidateCtaUrl('#cta_url_multi', '#error_cta_url_multi')) {
                    return;
                }
            }

            // Step 6 (index 5) — Save & Publish Preference is a required choice.
            // None of the three radios is pre-selected, so without this the user
            // could walk past the step and the project was created with an empty
            // schedule_posts. Same inline-error pattern as the CTA URL above.
            if (currentStep === 5) {
                if (!validateSchedulePosts()) {
                    return;
                }
            }

            if (currentStep < steps.length - 1) { // Only go up to step 6
                currentStep++;
                updateSteps();
                updateDataDisplay();
                updateButtonText();
                prevButton.disabled = false;

                // Generate keyword image options when moving to Step 3 (index 2)
                if (currentStep === 2) {
                    generateKeywordImageOptionsForBulk();
                }

            }
            else if (currentStep === steps.length - 1) { // When on last step
                // Validate before submission
                if (!validateBulkProjectName()) {
                    return;
                }
                
                if (!validateBulkKeywordListForSubmit()) {
                    return;
                }
                
                // Set button to processing state
                BulkSubmitButton.setProcessing();
                
                // Trigger bulk submission
                jQuery("#bulk_ai_post_submi_button").trigger("click");
            }
        });

        prevButton.addEventListener("click", () => {
            if (currentStep > 0) {
                currentStep--;
                updateSteps();
                updateDataDisplay();
                updateButtonText();
                
                // Reset button state when going back
                BulkSubmitButton.reset();
                
                if (currentStep === 0) {
                    prevButton.disabled = true;
                }
            }
        });

        // Initialize
        updateSteps();
        updateDataDisplay();
        updateButtonText();
        prevButton.disabled = true;
    });
</script>

<script>
    // Bulk Post: Per-Keyword Image Selection Functions
    function generateKeywordImageOptionsForBulk() {
        const keywordText = jQuery('#keyword_list').val();
        
        if (!keywordText || keywordText.trim() === '') {
            return;
        }
        
        // Split keywords and filter empty lines
        const keywords = keywordText.split('\n').filter(k => k.trim() !== '');
        
        if (keywords.length === 0) {
            return;
        }
        
        const container = jQuery('#keyword-image-list');
        container.empty();
        
        keywords.forEach((keyword, index) => {
            const trimmedKeyword = keyword.trim();
            
            const html = `
                <div class="keyword-image-row" style="padding: 15px; border: 1px solid #ddd; margin-bottom: 15px; border-radius: 12px; background: #f9f9f9;">
                    <div style="margin-bottom: 10px;">
                        <strong style="font-size: 16px; color: rgba(80, 87, 94, 0.8);">Keyword:</strong>
                        <span style="font-size: 16px; color: rgba(80, 87, 94, 0.8);">${trimmedKeyword}</span>
                    </div>
                    <div class="image-method-radio" style="margin-bottom: 10px;">
                        <label style="margin-right: 20px; cursor: pointer; display: inline-flex; align-items: center; font-size: 16px; color: rgba(80, 87, 94, 0.8);">
                            <input type="radio" name="image_method_${index}" value="AI_image_one" checked style="margin-right: 5px;">
                            Generate Image Using AI
                        </label>
                        <label style="cursor: pointer; display: inline-flex; align-items: center; font-size: 16px; color: rgba(80, 87, 94, 0.8);">
                            <input type="radio" name="image_method_${index}" value="Multiple_images" style="margin-right: 5px;">
                            Upload Image
                        </label>
                    </div>
                    <div class="upload-section-${index}" style="display: none; padding: 15px; background: #fff; border: 1px solid #e0e0e0; border-radius: 10px; margin-top: 10px;">
                        <input type="file" class="keyword-upload-input" data-index="${index}" accept="image/*" 
                               style="margin-bottom: 10px; padding: 5px;">
                        <div class="preview-${index}" style="margin: 10px 0;"></div>
                        <button type="button" class="btn-remove-image" data-index="${index}" 
                                style="display: none; background: #dc3545; color: #fff; padding: 8px 15px; border: none; border-radius: 3px; cursor: pointer; font-size: 13px;">
                            Remove Image
                        </button>
                        <input type="hidden" name="keyword_image_url_${index}" value="">
                    </div>
                </div>
            `;
            container.append(html);
        });
        
        // Show the container
        jQuery('#keyword-image-selection-container').show();
        jQuery('#old-image-selection').hide();
    }
    
    // Toggle upload section when radio changes
    jQuery(document).on('change', 'input[name^="image_method_"]', function() {
        const index = jQuery(this).attr('name').match(/\d+/)[0];
        const uploadSection = jQuery('.upload-section-' + index);
        
        if (jQuery(this).val() === 'Multiple_images') {
            uploadSection.show();
        } else {
            uploadSection.hide();
            // Clear uploaded image data when switching back to AI
            jQuery('input[name="keyword_image_url_' + index + '"]').val('');
            jQuery('.preview-' + index).empty();
            jQuery('.btn-remove-image[data-index="' + index + '"]').hide();
            jQuery('.keyword-upload-input[data-index="' + index + '"]').val('');
        }
    });
    
    // Handle file upload for each keyword
    jQuery(document).on('change', '.keyword-upload-input', function() {
        const index = jQuery(this).data('index');
        const file = this.files[0];
        
        if (!file) return;
        
        // Validate file type
        if (!file.type.match('image.*')) {
            alert('Please select a valid image file.');
            jQuery(this).val('');
            return;
        }
        
        // Validate file size (max 5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('Image size should not exceed 5MB.');
            jQuery(this).val('');
            return;
        }
        
        const formData = new FormData();
        formData.append('action', 'upload_keyword_image');
        formData.append('image', file);
        
        // Show loading indicator
        jQuery('.preview-' + index).html('<p style="color: #0073aa;">Uploading...</p>');
        
        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    // Store URL in hidden input
                    jQuery('input[name="keyword_image_url_' + index + '"]').val(response.data.image_url);
                    
                    // Show preview
                    jQuery('.preview-' + index).html(
                        '<img src="' + response.data.image_url + '" style="max-width: 200px; max-height: 150px; border-radius: 3px; border: 1px solid #ddd;">'
                    );
                    
                    // Show remove button
                    jQuery('.btn-remove-image[data-index="' + index + '"]').show();
                } else {
                    jQuery('.preview-' + index).html('<p style="color: #dc3545;">Upload failed. Please try again.</p>');
                    setTimeout(() => {
                        jQuery('.preview-' + index).empty();
                    }, 3000);
                }
            },
            error: function() {
                jQuery('.preview-' + index).html('<p style="color: #dc3545;">Upload failed. Please try again.</p>');
                setTimeout(() => {
                    jQuery('.preview-' + index).empty();
                }, 3000);
            }
        });
    });
    
    // Handle remove image
    jQuery(document).on('click', '.btn-remove-image', function() {
        const index = jQuery(this).data('index');
        jQuery('input[name="keyword_image_url_' + index + '"]').val('');
        jQuery('.preview-' + index).empty();
        jQuery('.keyword-upload-input[data-index="' + index + '"]').val('');
        jQuery(this).hide();
    });
    
    // Single Post: Content Credit Check Function
    function checkSingleContentCredits() {
        return new Promise(function(resolve, reject) {
            var apiKey = '<?php echo esc_js(get_option("improveseo_api_key")); ?>';
            var siteCode = '<?php echo esc_js(get_option("improveseo_site_code")); ?>';

            if (!apiKey || !siteCode) {
                showImproveSEONotification('error', 'Configuration Required', 'Please configure your API Key and Site Code in ImproveSEO settings first.', null);
                reject('missing_credentials');
                return;
            }

            if (typeof ImproveSEOLoading !== 'undefined' && ImproveSEOLoading.show) {
                ImproveSEOLoading.show({ title: 'Checking Content Credits...', message: 'Verifying you have enough credits for this post.' });
            }

            jQuery.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'check_bulk_credits',
                    api_key: apiKey,
                    site_code: siteCode,
                    keyword_count: 1,
                    ai_image_count: 0,
                    nonce: '<?php echo wp_create_nonce("check_credits_nonce"); ?>'
                },
                success: function(response) {
                    if (typeof ImproveSEOLoading !== 'undefined' && ImproveSEOLoading.hide) { ImproveSEOLoading.hide(); }
                    if (response.success) {
                        var d = response.data;
                        if (d.content_check && !d.content_check.sufficient) {
                            showImproveSEONotification('warning', 'Not Enough Content Credits', 'Content credits required: 1\nCredits available now: ' + d.content_check.available + '\n\nYou don\'t have enough credits to generate this post. Please purchase more to continue.', 'https://account.improveseoplugin.com//pricing');
                            reject('insufficient_content_credits');
                            return;
                        }
                        resolve(d);
                    } else {
                        showImproveSEONotification('error', 'Credit Check Failed', (response.data && response.data.message) ? response.data.message : 'Unable to verify credits. Please try again.', null);
                        reject('check_failed');
                    }
                },
                error: function() {
                    if (typeof ImproveSEOLoading !== 'undefined' && ImproveSEOLoading.hide) { ImproveSEOLoading.hide(); }
                    showImproveSEONotification('error', 'Connection Error', 'Unable to verify credits. Please check your connection and try again.', null);
                    reject('ajax_error');
                }
            });
        });
    }

    // Bulk Post: Content Credit Check Function (Step 1)
    function checkBulkContentCredits() {
        return new Promise((resolve, reject) => {
            // Get API credentials
            const apiKey = '<?php echo esc_js(get_option("improveseo_api_key")); ?>';
            const siteCode = '<?php echo esc_js(get_option("improveseo_site_code")); ?>';
            
            if (!apiKey || !siteCode) {
                showImproveSEONotification(
                    'error',
                    'Configuration Required',
                    'Please configure your API Key and Site Code in ImproveSEO settings first.',
                    null
                );
                reject('missing_credentials');
                return;
            }
            
            // Get keyword list and count
            const keywordText = jQuery('#keyword_list').val();
            const keywords = keywordText.split('\n').filter(k => k.trim() !== '');
            const keywordCount = keywords.length;
            
            // Show loading
            if (typeof ImproveSEOLoading !== 'undefined' && ImproveSEOLoading.show) {
                ImproveSEOLoading.show({
                    title: 'Checking Content Credits...',
                    message: 'Verifying you have enough credits for ' + keywordCount + ' posts.'
                });
            }
            
            // Make AJAX call to check credits
            jQuery.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'check_bulk_credits',
                    api_key: apiKey,
                    site_code: siteCode,
                    keyword_count: keywordCount,
                    ai_image_count: 0, // Not checking images at step 1
                    nonce: '<?php echo wp_create_nonce("check_credits_nonce"); ?>'
                },
                success: function(response) {
                    if (typeof ImproveSEOLoading !== 'undefined' && ImproveSEOLoading.hide) {
                        ImproveSEOLoading.hide();
                    }
                    
                    if (response.success) {
                        const data = response.data;
                        
                        // Check 1: Plan restriction (Grow/Basic plan)
                        if (data.plan_check && !data.plan_check.allowed) {
                            showImproveSEONotification(
                                'warning',
                                'Plan Upgrade Required',
                                'Your current plan (' + data.plan_check.plan_name + ') does not support Bulk Posts. Please upgrade to access this feature.',
                                'https://account.improveseoplugin.com//pricing'
                            );
                            reject('plan_restriction');
                            return;
                        }
                        
                        // Check 2: Content credits insufficient
                        if (data.content_check && !data.content_check.sufficient) {
                            const shortBy = keywordCount - data.content_check.available;
                            const msg =
                                'Content credits required: ' + keywordCount + '\n' +
                                'Credits available now: ' + data.content_check.available + '\n' +
                                'Short by: ' + shortBy + '\n\n' +
                                'Reduce your keyword list to ' + data.content_check.available +
                                ' post(s), or purchase more credits to continue.';

                            showImproveSEONotification(
                                'warning',
                                'Not Enough Content Credits',
                                msg,
                                'https://account.improveseoplugin.com//pricing'
                            );
                            reject('insufficient_content_credits');
                            return;
                        }
                        
                        // Content credits are sufficient — confirm before advancing.
                        // resolve() runs on OK/close so the wizard only moves forward
                        // once the user acknowledges the credit usage.
                        ImproveSEONotification.show({
                            type: 'success',
                            title: 'Confirm Content Generation',
                            message:
                                'Content credits required: ' + keywordCount + '\n' +
                                'Credits available now: ' + data.content_check.available + '\n' +
                                'Remaining after generation: ' + (data.content_check.available - keywordCount) + '\n\n' +
                                'Click OK to confirm and continue.',
                            buttonText: 'OK',
                            onClose: function () { resolve(data); }
                        });
                        
                    } else {
                        showImproveSEONotification(
                            'error',
                            'Credit Check Failed',
                            response.data && response.data.message ? response.data.message : 'Unable to verify credits. Please try again.',
                            null
                        );
                        reject('check_failed');
                    }
                },
                error: function(xhr, status, error) {
                    if (typeof ImproveSEOLoading !== 'undefined' && ImproveSEOLoading.hide) {
                        ImproveSEOLoading.hide();
                    }
                    
                    showImproveSEONotification(
                        'error',
                        'Connection Error',
                        'Unable to verify credits. Please check your connection and try again.',
                        null
                    );
                    reject('ajax_error');
                }
            });
        });
    }
    
    // Bulk Post: Image Credit Check Function (Step 3)
    function checkBulkImageCredits() {
        return new Promise((resolve, reject) => {
            // Get API credentials
            const apiKey = '<?php echo esc_js(get_option("improveseo_api_key")); ?>';
            const siteCode = '<?php echo esc_js(get_option("improveseo_site_code")); ?>';
            
            if (!apiKey || !siteCode) {
                showImproveSEONotification(
                    'error',
                    'Configuration Required',
                    'Please configure your API Key and Site Code in ImproveSEO settings first.',
                    null
                );
                reject('missing_credentials');
                return;
            }
            
            // Get keyword list and count
            const keywordText = jQuery('#keyword_list').val();
            const keywords = keywordText.split('\n').filter(k => k.trim() !== '');
            const keywordCount = keywords.length;
            
            // Count how many keywords selected AI image generation
            let aiImageCount = 0;
            keywords.forEach((keyword, index) => {
                const imageMethod = jQuery('input[name="image_method_' + index + '"]:checked').val();
                if (imageMethod === 'AI_image_one') {
                    aiImageCount++;
                }
            });
            
            // Show loading
            if (typeof ImproveSEOLoading !== 'undefined' && ImproveSEOLoading.show) {
                ImproveSEOLoading.show({
                    title: 'Checking Image Credits...',
                    message: 'Verifying you have enough credits for ' + aiImageCount + ' AI images.'
                });
            }
            
            // Make AJAX call to check credits
            jQuery.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'check_bulk_credits',
                    api_key: apiKey,
                    site_code: siteCode,
                    keyword_count: keywordCount,
                    ai_image_count: aiImageCount,
                    nonce: '<?php echo wp_create_nonce("check_credits_nonce"); ?>'
                },
                success: function(response) {
                    if (typeof ImproveSEOLoading !== 'undefined' && ImproveSEOLoading.hide) {
                        ImproveSEOLoading.hide();
                    }
                    
                    if (response.success) {
                        const data = response.data;
                        
                        // Only check image credits at this step (content already checked at step 1)
                        // Check: Image credits insufficient
                        if (data.image_check && !data.image_check.sufficient) {
                            const needed = data.image_check.needed;
                            const available = data.image_check.available;
                            const shortage = needed - available;

                            const msg =
                                'Image credits required: ' + needed + '\n' +
                                'Credits available now: ' + available + '\n' +
                                'Short by: ' + shortage + '\n\n' +
                                'Switch ' + shortage + ' keyword(s) to "Upload Image", or purchase more credits to continue.';

                            showImproveSEONotification(
                                'warning',
                                'Not Enough Image Credits',
                                msg,
                                'https://account.improveseoplugin.com//pricing'
                            );
                            reject('insufficient_image_credits');
                            return;
                        }

                        // Image credits are sufficient.
                        console.log('✅ Image credit check passed:', data);
                        if (aiImageCount > 0) {
                            // Confirm before advancing — OK gates the step change.
                            ImproveSEONotification.show({
                                type: 'success',
                                title: 'Confirm AI Image Generation',
                                message:
                                    'Image credits required: ' + aiImageCount + '\n' +
                                    'Credits available now: ' + data.image_check.available + '\n' +
                                    'Remaining after generation: ' + (data.image_check.available - aiImageCount) + '\n\n' +
                                    'Click OK to confirm and continue.',
                                buttonText: 'OK',
                                onClose: function () { resolve(data); }
                            });
                        } else {
                            resolve(data);
                        }
                    } else {
                        showImproveSEONotification(
                            'error',
                            'Credit Check Failed',
                            response.data.error || 'Unable to verify credits. Please try again.',
                            null
                        );
                        reject('check_failed');
                    }
                },
                error: function(xhr, status, error) {
                    if (typeof ImproveSEOLoading !== 'undefined' && ImproveSEOLoading.hide) {
                        ImproveSEOLoading.hide();
                    }
                    showImproveSEONotification(
                        'error',
                        'Connection Error',
                        'Unable to connect to ImproveSEO server. Please check your internet connection and settings.',
                        null
                    );
                    reject('connection_error');
                }
            });
        });
    }
    
    // Bulk Post: Button State Management
    const BulkSubmitButton = {
        button: null,
        originalHTML: '',
        
        init: function(buttonElement) {
            this.button = buttonElement;
            if (this.button) {
                this.originalHTML = this.button.innerHTML;
            }
        },
        
        setValidating: function() {
            if (!this.button) return;
            this.button.disabled = true;
            this.button.innerHTML = 'Validating... <img src="<?php echo WT_URL . '/assets/images/latest-images/ep_arrow-rights.svg' ?>" alt="arrow-right">';
        },
        
        setProcessing: function() {
            if (!this.button) return;
            this.button.disabled = true;
            this.button.innerHTML = 'Processing... <img src="<?php echo WT_URL . '/assets/images/latest-images/ep_arrow-rights.svg' ?>" alt="arrow-right">';
        },
        
        setError: function() {
            if (!this.button) return;
            this.button.disabled = false;
            this.button.innerHTML = this.originalHTML;
        },
        
        setSuccess: function() {
            if (!this.button) return;
            this.button.disabled = true;
            this.button.innerHTML = 'Completed! ✓';
        },
        
        reset: function() {
            if (!this.button) return;
            this.button.disabled = false;
            this.button.innerHTML = this.originalHTML;
        }
    };
    
    // Bulk Post: Validation Functions
    function validateBulkProjectName() {
        const projectName = jQuery('#project_name').val();
        
        if (!projectName || projectName.trim() === '') {
            showImproveSEONotification(
                'warning',
                'Project Name Required',
                'Please enter a project name for this bulk task before submitting.',
                null
            );
            return false;
        }
        
        return true;
    }
    
    function validateBulkKeywordListForSubmit() {
        const keywordListName = jQuery('#keyword_list_name').val();
        const keywordList = jQuery('#keyword_list').val();
        
        if (!keywordListName || keywordListName === '' || keywordListName === 'none') {
            showImproveSEONotification(
                'warning',
                'Keyword List Required',
                'Please select a keyword list before submitting.',
                null
            );
            return false;
        }
        
        if (!keywordList || keywordList.trim() === '') {
            showImproveSEONotification(
                'warning',
                'Empty Keyword List',
                'The selected keyword list is empty. Please select a list with keywords.',
                null
            );
            return false;
        }
        
        const keywords = keywordList.split('\n').filter(k => k.trim() !== '');
        if (keywords.length === 0) {
            showImproveSEONotification(
                'warning',
                'No Valid Keywords',
                'Please ensure your keyword list contains at least one valid keyword.',
                null
            );
            return false;
        }

        return true;
    }

    // Normalize a Call-to-Action URL in place and validate it.
    // - Empty is allowed (the field is optional) → returns true.
    // - A bare host like "example.com/contact" is upgraded to "https://example.com/contact".
    // - If it still doesn't look like a valid URL, shows an inline error and returns false
    //   so the wizard stays put instead of the submit button hanging on "Processing...".
    window.normalizeAndValidateCtaUrl = function (inputSelector, errorSelector) {
        var $input = jQuery(inputSelector);
        var $error = jQuery(errorSelector);
        if (!$input.length) { return true; }
        if ($error.length) { $error.text(''); }

        var raw = (($input.val() || '') + '').trim();
        if (raw === '') { $input.val(''); return true; } // optional

        // Add a scheme when the user typed a bare domain.
        var normalized = raw;
        if (!/^https?:\/\//i.test(normalized)) {
            normalized = normalized.replace(/^\/+/, '');
            normalized = 'https://' + normalized;
        }

        var valid = false;
        try {
            var u = new URL(normalized);
            // Require a dotted host (or localhost) so "https://foo" is rejected.
            valid = (u.protocol === 'http:' || u.protocol === 'https:') &&
                    (/\./.test(u.hostname) || u.hostname === 'localhost');
        } catch (e) {
            valid = false;
        }

        if (!valid) {
            var msg = 'Please enter a valid URL (e.g. https://example.com/contact).';
            if ($error.length) {
                $error.text(msg);
            } else {
                showImproveSEONotification('warning', 'Invalid Call to Action URL', msg, null);
            }
            return false;
        }

        $input.val(normalized); // write the normalized value back so the server stores it clean
        return true;
    };
</script>

<script>
    document.querySelectorAll('.style_imgae_gereater_radio input[type="radio"]').forEach(input => {
        input.addEventListener('change', function () {
            // Remove active class from all labels
            document.querySelectorAll('.style_imgae_gereater_radio').forEach(label => {
                label.classList.remove('active');
            });

            // Add active class to the parent label of the checked input
            if (this.checked) {
                this.closest('.style_imgae_gereater_radio').classList.add('active');
            }
        });
    });

</script>
<script>
    document.querySelectorAll('.style_imgae_gereater_radio input[type="radio"]').forEach(input => {
        input.addEventListener('change', function () {
            // Remove active class from all labels
            document.querySelectorAll('.style_imgae_gereater_radio').forEach(label => {
                label.classList.remove('active');
            });

            // Add active class to the parent label of the checked input
            if (this.checked) {
                this.closest('.style_imgae_gereater_radio').classList.add('active');
            }
        });
    });

</script>
<script>
    document.querySelectorAll('.style_schedule_radio input[type="radio"]').forEach(input => {
        input.addEventListener('change', function () {
            // Remove active class from all labels
            document.querySelectorAll('.style_schedule_radio').forEach(label => {
                label.classList.remove('active');
            });

            // Add active class to the parent label of the checked input
            if (this.checked) {
                this.closest('.style_schedule_radio').classList.add('active');
            }
        });
    });
</script>
<script>
    // Select all radio buttons with the name "assigning_authors"
    document.querySelectorAll('input[name="assigning_authors"]').forEach(input => {
        input.addEventListener('change', function () {
            // Remove schdule_active class from all labels
            document.querySelectorAll('.seo-form-field_multi label').forEach(label => {
                label.classList.remove('schdule_active');
            });

            // Add schdule_active class to the parent label of the checked input
            if (this.checked) {
                this.closest('label').classList.add('schdule_active');
            }
        });
    });
</script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        document.getElementById("checkbox_need").addEventListener("change", function () {
            const label = document.querySelector(".step_one_approve_button");
            const labelText = label.querySelector(".label-text");
            const icon = label.querySelector(".icon");
            const titleArea = document.getElementById('maintitlearea');

            if (this.checked) {
                // Validate that the AI generated title is not empty before allowing approve
                if (!titleArea || titleArea.value.trim() === "") {
                    showImproveSEONotification(
                        'warning',
                        'Empty Title',
                        'AI generated title cannot be empty. Please generate a title first.',
                        null
                    );
                    this.checked = false;
                    // Reset label state
                    label.classList.remove("approve_active");
                    labelText.textContent = 'Approve';
                    icon.style.display = 'none';
                    // Highlight the textarea and focus it
                    if (titleArea) {
                        titleArea.style.border = '2px solid red';
                        titleArea.focus();
                    }
                    return;
                } else {
                    // Clear any prior error styling if present
                    titleArea.style.border = '1px solid #D2D2D2';
                }

                label.classList.add("approve_active");
                labelText.textContent = 'Approved';
                icon.style.display = 'block'; // Show the icon
            } else {
                label.classList.remove("approve_active");
                labelText.textContent = 'Approve';
                icon.style.display = 'none'; // Hide the icon
            }
        });
    });

    document.addEventListener("DOMContentLoaded", function () {
        const radioButtons = document.querySelectorAll('.show_hide_content_in_bulk_part_3');
        const aiImageElement = document.querySelector('#ai-image-generating');

        radioButtons.forEach(radio => {
            radio.addEventListener('change', function () {
                if (this.checked) {
                    aiImageElement.style.visibility = "visible";
                } else {
                    aiImageElement.style.visibility = "hidden";
                }
            });
        });
    });
    
    // Bulk Category Management
    jQuery(document).ready(function($) {
        // Handle category selection styling
        $('.bulk-category-item input[type="checkbox"]').on('change', function() {
            const label = $(this).closest('.bulk-category-item');
            if ($(this).is(':checked')) {
                label.css({
                    'background': '#1C7293',
                    'color': 'white',
                    'border': '2px solid #1C7293'
                });
            } else {
                label.css({
                    'background': 'white',
                    'color': '#333',
                    'border': '2px solid #1C7293'
                });
            }
        });
        
        // Initialize checked categories styling
        $('.bulk-category-item input[type="checkbox"]:checked').each(function() {
            $(this).closest('.bulk-category-item').css({
                'background': '#1C7293',
                'color': 'white',
                'border': '2px solid #1C7293'
            });
        });
        
        // Add new category
        $('#add_category_bulk_btn').on('click', function() {
            const categoryName = $('#new_category_name_bulk').val().trim();
            const messageEl = $('#category_add_message_bulk');
            
            if (!categoryName) {
                messageEl.text('Please enter a category name').css('color', '#dc3232');
                return;
            }
            
            // Send AJAX request to create category
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'create_bulk_category',
                    cat_name: categoryName,
                    nonce: '<?php echo wp_create_nonce("create_category_nonce"); ?>'
                },
                beforeSend: function() {
                    $('#add_category_bulk_btn').prop('disabled', true).text('Adding...');
                },
                success: function(response) {
                    if (response.success) {
                        // Add new category to the list - using CSS class like pre-existing categories
                        const newCategoryHtml = '<label class="bulk-category-item" style="background: #1C7293; color: white;">' +
                            '<input type="checkbox" name="cats[]" value="' + response.data.term_id + '" checked>' +
                            '<span>' + response.data.name + '</span>' +
                        '</label>';
                        
                        $('#bulk-category-list').append(newCategoryHtml);
                        
                        // Re-bind event handler for new checkbox
                        $('#bulk-category-list .bulk-category-item:last input[type="checkbox"]').on('change', function() {
                            const label = $(this).closest('.bulk-category-item');
                            if ($(this).is(':checked')) {
                                label.css({
                                    'background': '#1C7293',
                                    'color': 'white'
                                });
                            } else {
                                label.css({
                                    'background': 'white',
                                    'color': '#333'
                                });
                            }
                        });
                        
                        $('#new_category_name_bulk').val('');
                        messageEl.text('Category added successfully!').css('color', '#46b450');
                        
                        setTimeout(function() {
                            messageEl.text('');
                        }, 3000);
                    } else {
                        messageEl.text(response.data || 'Error creating category').css('color', '#dc3232');
                    }
                },
                error: function() {
                    messageEl.text('Error creating category. Please try again.').css('color', '#dc3232');
                },
                complete: function() {
                    $('#add_category_bulk_btn').prop('disabled', false).text('Add Category');
                }
            });
        });
        
        // Allow Enter key to add category
        $('#new_category_name_bulk').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                $('#add_category_bulk_btn').click();
            }
        });

        // ── Single post modal — category checkbox styling ──
        $(document).on('change', '#single-modal-category-list input[type="checkbox"]', function() {
            var label = $(this).closest('.bulk-category-item');
            if ($(this).is(':checked')) {
                label.css({ background: '#1C7293', color: 'white', borderColor: '#1C7293' });
            } else {
                label.css({ background: '', color: '', borderColor: '' });
            }
        });

        // ── Single post modal — create new category ──
        $('#add_category_single_btn').on('click', function() {
            var categoryName = $('#new_category_name_single').val().trim();
            var messageEl    = $('#category_add_message_single');
            if (!categoryName) {
                messageEl.text('Please enter a category name').css('color', '#dc3232');
                return;
            }
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'create_bulk_category',
                    cat_name: categoryName,
                    nonce: '<?php echo wp_create_nonce("create_category_nonce"); ?>'
                },
                beforeSend: function() {
                    $('#add_category_single_btn').prop('disabled', true).text('Adding...');
                },
                success: function(response) {
                    if (response.success) {
                        var newHtml = '<label class="bulk-category-item" style="background:#1C7293;color:white;">' +
                            '<input type="checkbox" name="modal_single_cats[]" value="' + response.data.term_id + '" checked ' +
                            'data-new-cat="1" data-cat-name="' + $('<div>').text(response.data.name).html() + '">' +
                            '<span>' + $('<div>').text(response.data.name).html() + '</span>' +
                        '</label>';
                        $('#single-modal-category-list').append(newHtml);

                        // Also add it to the project form's Categories sidebar as a real,
                        // checked checkbox — the sidebar is rendered from get_categories() at
                        // page load, so a category created afterwards would otherwise be missing
                        // there and not carried over to the saved project. This makes a new
                        // category behave exactly like a pre-existing one.
                        try {
                            var firstSidebarCat = document.querySelector('#main_form input[name="cats[]"]');
                            var sidebarContainer = firstSidebarCat ? firstSidebarCat.closest('.inside') : null;
                            if (sidebarContainer &&
                                !sidebarContainer.querySelector('input[name="cats[]"][value="' + response.data.term_id + '"]')) {
                                var safeName = $('<div>').text(response.data.name).html();
                                $(sidebarContainer).append(
                                    "<div class='input-group cta-check m-0'><span>" +
                                    "<input checked id='" + response.data.term_id + "' type='checkbox' value='" +
                                    response.data.term_id + "' name='cats[]'>" +
                                    "<label for='" + response.data.term_id + "'>" + safeName + "</label></span></div>"
                                );
                            }
                        } catch (e) { /* sidebar not present on this page — modal selection still applies */ }

                        $('#new_category_name_single').val('');
                        messageEl.text('Category added!').css('color', '#46b450');
                        setTimeout(function() { messageEl.text(''); }, 3000);
                    } else {
                        messageEl.text(response.data || 'Error creating category').css('color', '#dc3232');
                    }
                },
                error: function() {
                    messageEl.text('Error creating category. Please try again.').css('color', '#dc3232');
                },
                complete: function() {
                    $('#add_category_single_btn').prop('disabled', false).text('Add Category');
                }
            });
        });

        $('#new_category_name_single').on('keypress', function(e) {
            if (e.which === 13) { e.preventDefault(); $('#add_category_single_btn').click(); }
        });
    });
</script>
<script>

    // Refresh keyword lists dropdown - simplified to just reload page
    jQuery(document).ready(function() {
        jQuery('#refresh_keyword_lists').on('click', function(e) {
            e.preventDefault();
            location.reload();
        });
    });

    jQuery(document).ready(function () {
        jQuery('#keyword_list_name').on('change', function () {
            var selectedOption = jQuery(this).val();
            
            if (selectedOption == 'create_new_project' || selectedOption == 'none') {
                jQuery('#keyword_list_container').hide();
                jQuery('#create_keyword_container').toggle(selectedOption == 'create_new_project');
                return;
            }
            
            // Show container immediately
            jQuery('#keyword_list_container').show();
            jQuery('#create_keyword_container').hide();
            
            // Try to load from cached PHP data first
            var allKeywords = <?php echo json_encode($all_keywords); ?>;
            
            if (allKeywords[selectedOption]) {
                // Data exists in cache - use it immediately
                var keywordCount = allKeywords[selectedOption].split('\n').filter(function (k) { return k.trim() !== ''; }).length;

                jQuery('#keywordcounts').text(keywordCount);
                jQuery('#keywordtime').text(window.formatEstimatedTime(keywordCount));
                jQuery('#keyword_list').val(allKeywords[selectedOption]);
            } else {
                // Data not in cache (newly created list) - fetch from server
                jQuery('#keyword_list').val('Loading keywords...');
                jQuery.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'get_keyword_list_data',
                        list_id: selectedOption
                    },
                    success: function(response) {
                        if (response.success) {
                            jQuery('#keywordcounts').text(response.data.count);
                            jQuery('#keywordtime').text(window.formatEstimatedTime(response.data.count));
                            jQuery('#keyword_list').val(response.data.keywords);
                            
                            // Cache it for next time
                            allKeywords[selectedOption] = response.data.keywords;
                        } else {
                            jQuery('#keyword_list').val('');
                            showImproveSEONotification(
                                'error',
                                'Load Failed',
                                'Failed to load keywords. Please try again.',
                                null
                            );
                        }
                    },
                    error: function() {
                        jQuery('#keyword_list').val('');
                        showImproveSEONotification(
                            'error',
                            'Load Failed',
                            'An error occurred while loading keywords.',
                            null
                        );
                    }
                });
            }
        });
    });

</script>
<script>
  // ImproveSEO Notification System
  const ImproveSEONotification = {
    show: function(options) {
      const {
        title = 'Notification',
        message = '',
        type = 'warning', // warning, error, success, info
        buttonText = 'OK',
        onClose = null
      } = options;

      const overlay = document.getElementById('improveseoNotification');
      const iconEl = document.getElementById('improveseoNotificationIcon');
      const titleEl = document.getElementById('improveseoNotificationTitle');
      const messageEl = document.getElementById('improveseoNotificationMessage');
      const okBtn = document.getElementById('improveseoNotificationOk');

      // warning/error: CSS ::after renders the symbol — textContent must be empty to avoid double icon
      // success/info: no ::after defined, so textContent carries the symbol
      const icons = {
        warning: '',
        error: '',
        success: '✓',
        info: 'ℹ'
      };

      // Remove all type classes
      iconEl.classList.remove('warning', 'error', 'success', 'info');
      // Add current type class
      iconEl.classList.add(type);
      iconEl.textContent = icons[type];

      // Set content
      titleEl.textContent = title;
      messageEl.textContent = message;
      okBtn.textContent = buttonText;

      // Clear any onclick handler left by showImproveSEONotification() (a separate
      // system that assigns okBtn.onclick). Without this, a prior modal's handler
      // would still fire alongside ours on the same OK button.
      okBtn.onclick = null;

      // Show overlay
      overlay.classList.add('active');

      // Handle close
      const closeHandler = function() {
        overlay.classList.remove('active');
        okBtn.removeEventListener('click', closeHandler);
        overlay.removeEventListener('click', overlayClickHandler);
        if (onClose && typeof onClose === 'function') {
          onClose();
        }
      };

      const overlayClickHandler = function(e) {
        if (e.target === overlay) {
          closeHandler();
        }
      };

      okBtn.addEventListener('click', closeHandler);
      overlay.addEventListener('click', overlayClickHandler);

      // Return API for external control
      return {
        close: closeHandler
      };
    },

    warning: function(message, title = 'Warning') {
      return this.show({ title, message, type: 'warning' });
    },

    error: function(message, title = 'Error') {
      return this.show({ title, message, type: 'error' });
    },

    success: function(message, title = 'Success') {
      return this.show({ title, message, type: 'success' });
    },

    info: function(message, title = 'Information') {
      return this.show({ title, message, type: 'info' });
    }
  };

  // Make it globally available
  window.ImproveSEONotification = ImproveSEONotification;

  /**
   * Show credit exhaustion notification with link to buy more credits
   */
  function showImproveSEONotification(type, title, message, actionUrl) {
    const overlay = document.getElementById('improveseoNotification');
    const iconEl = document.getElementById('improveseoNotificationIcon');
    const titleEl = document.getElementById('improveseoNotificationTitle');
    const messageEl = document.getElementById('improveseoNotificationMessage');
    const okBtn = document.getElementById('improveseoNotificationOk');
    
    // Set icon based on type - use Unicode symbols for better display
    const icons = {
      warning: '', // CSS ::after handles the symbol
      error: '',   // CSS ::after handles the symbol
      success: '✓',
      info: 'ℹ'
    };
    
    iconEl.classList.remove('warning', 'error', 'success', 'info');
    iconEl.classList.add(type);
    iconEl.textContent = icons[type] || '';
    
    titleEl.textContent = title;
    messageEl.textContent = message;
    
    if (actionUrl) {
      // Determine button text based on URL type
      if (actionUrl.startsWith('mailto:')) {
        okBtn.textContent = 'Contact Support';
      } else if (actionUrl.includes('dashboard.improveseoplugin.com')) {
        okBtn.textContent = 'Buy Credits';
      } else {
        okBtn.textContent = 'Learn More';
      }
      
      okBtn.onclick = function() {
        if (actionUrl.startsWith('mailto:')) {
          window.location.href = actionUrl;
        } else {
          window.open(actionUrl, '_blank');
        }
        overlay.classList.remove('active');
      };
    } else {
      okBtn.textContent = 'OK';
      okBtn.onclick = function() {
        overlay.classList.remove('active');
      };
    }
    
    overlay.classList.add('active');
    overlay.onclick = function(e) {
      if (e.target === overlay) {
        overlay.classList.remove('active');
      }
    };
  }
  
  // Make globally available
  window.showImproveSEONotification = showImproveSEONotification;

  // ImproveSEO Loading System
  const ImproveSEOLoading = {
    show: function(options) {
      const {
        title = 'Loading...',
        message = 'Please wait...'
      } = options || {};

      const overlay = document.getElementById('improveseoLoading');
      const titleEl = document.getElementById('improveseoLoadingTitle');
      const messageEl = document.getElementById('improveseoLoadingMessage');

      titleEl.textContent = title;
      messageEl.textContent = message;
      overlay.classList.add('active');
    },

    hide: function() {
      const overlay = document.getElementById('improveseoLoading');
      overlay.classList.remove('active');
    }
  };

  // Make it globally available
  window.ImproveSEOLoading = ImproveSEOLoading;
</script>
<script>
  document.addEventListener("DOMContentLoaded", function () {
    const seedInput = document.getElementById("seed_keyword");
    const seedSelect = document.getElementById("seed_select");
    const toneSelect = document.getElementById("cotnt_type");
    const reloadBtn = document.getElementById("reload");
    const approveCheckbox = document.getElementById("checkbox_need");
    const approveLabel = document.querySelector(".step_one_approve_button");

    const controls = [
      { el: seedSelect, type: "select" },
      { el: toneSelect, type: "select" },
      { el: reloadBtn, type: "button" },
    ];

    function hasSeed() {
      return (seedInput?.value || "").trim().length > 0;
    }

    function applyDisabledUI(disabled) {
      const targets = [seedSelect, toneSelect, reloadBtn, approveLabel];
      targets.forEach((t) => {
        if (!t) return;
        if (disabled) {
          t.classList.add("is-disabled");
          t.setAttribute("aria-disabled", "true");
        } else {
          t.classList.remove("is-disabled");
          t.removeAttribute("aria-disabled");
        }
      });
    }

    function guardEvent(e) {
      if (!hasSeed()) {
        e.preventDefault();
        e.stopPropagation();
        // Show styled notification instead of alert
        ImproveSEONotification.warning(
          'Please enter a seed keyword before selecting title type or tone of voice.',
          'Seed Keyword Required'
        );
        seedInput?.focus();
        return true;
      }
      return false;
    }

    // Attach guards
    controls.forEach(({ el, type }) => {
      if (!el) return;
      if (type === "select") {
        el.addEventListener("mousedown", guardEvent, true);
        el.addEventListener("keydown", function (e) {
          // Block opening with keyboard (Space/Arrow) if no seed
          if ([" ", "ArrowDown", "Enter"].includes(e.key)) {
            guardEvent(e);
          }
        }, true);
      } else if (type === "button") {
        el.addEventListener("click", guardEvent, true);
      }
    });

    if (approveLabel) {
      // Guard label click
      approveLabel.addEventListener("click", function (e) {
        if (guardEvent(e)) return;
      }, true);
    }
    if (approveCheckbox) {
      // Guard checkbox change
      approveCheckbox.addEventListener("change", function (e) {
        if (!hasSeed()) {
          e.preventDefault();
          e.stopPropagation();
          // Show styled notification instead of alert
          ImproveSEONotification.warning(
            'Please enter a seed keyword before approving.',
            'Seed Keyword Required'
          );
          this.checked = false;
          seedInput?.focus();
        }
      }, true);
    }

    // Update UI on seed input changes
    seedInput?.addEventListener("input", function () {
      applyDisabledUI(!hasSeed());
    });

    // Initialize state
    applyDisabledUI(!hasSeed());
  });
</script>