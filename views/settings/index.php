<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


use ImproveSEO\View;

?>

<?php View::startSection('breadcrumbs') ?>

<a href="<?php echo esc_url( admin_url('admin.php?page=improveseo_dashboard') ); ?>">Improve SEO</a>

&raquo;

<span>Settings</span>

<?php View::endSection('breadcrumbs') ?>

<?php View::startSection('content') ?>

<h1 class="hidden">Improve SEO Settings</h1>

<div class="iseo-settings-page">

    <!-- Intro guidance banner -->
    <div class="iseo-intro-banner">
        <svg class="iseo-intro-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
        <p>Configure your ImproveSEO server settings to enable AI content generation. Get your API Key and Site Code from your <a href="https://account.improveseoplugin.com/" target="_blank">ImproveSEO Dashboard</a>.</p>
    </div>

    <!-- Two-column layout -->
    <div class="iseo-settings-grid">

        <!-- ── LEFT COLUMN: main settings ─────────────────── -->
        <div class="iseo-col-main">

            <!-- Main Settings Panel -->
            <div class="iseo-settings-container">

                <!-- Plugin Identity Bar -->
                <div class="iseo-identity-bar">
                    <div class="iseo-identity-brand">
                        <img src="<?php echo esc_url( improveseo_logo_url() ); ?>" alt="ImproveSEO">
                        <span class="iseo-brand-name">ImproveSEO</span>
                        <span class="iseo-version"><?php echo esc_html( defined('IMPROVESEO_VERSION') ? IMPROVESEO_VERSION : '' ); ?></span>
                    </div>
                </div>

                <form class="improve-seo-form-global iseo-settings-form" method="post" action="options.php">
                    <?php settings_fields('improveseo_settings'); ?>

                    <?php
                    // Errors recorded by improveseo_sanitize_and_verify_credentials_field() during
                    // the save this page just redirected from — e.g. the API Key / Site Code being
                    // rejected by the live connection check. Without reading them here they are
                    // recorded but never shown, and a refused save would look identical to a
                    // successful one.
                    //
                    // The three credential codes are lifted OUT of the inline notice list and handed
                    // to the modal at the bottom of this file instead. That is where a refusal is
                    // reported when JavaScript is on (the submit gate never reaches options.php at
                    // all), so routing the no-JS path to the same place keeps one refusal looking
                    // like one refusal rather than two unrelated messages. Everything else — above
                    // all WordPress's own "Settings saved." — still renders as a normal notice.
                    $iseo_modal_error_codes = array( 'iseo_incomplete_pair', 'iseo_not_connected', 'iseo_verify_unreachable' );
                    $iseo_save_failure      = '';
                    $iseo_save_failure_code = '';

                    foreach ( get_settings_errors( 'improveseo_settings' ) as $iseo_error ) {
                        if ( in_array( $iseo_error['code'], $iseo_modal_error_codes, true ) ) {
                            if ( '' === $iseo_save_failure ) {
                                $iseo_save_failure      = $iseo_error['message'];
                                $iseo_save_failure_code = $iseo_error['code'];
                            }
                            continue;
                        }

                        // Same markup settings_errors() would have emitted, including its
                        // 'updated' → 'success' rename, so the surviving notices are unchanged.
                        $iseo_notice_type = ( 'updated' === $iseo_error['type'] ) ? 'success' : $iseo_error['type'];
                        if ( ! in_array( $iseo_notice_type, array( 'error', 'success', 'warning', 'info' ), true ) ) {
                            $iseo_notice_type = 'info';
                        }
                        printf(
                            '<div id="%s" class="notice notice-%s settings-error is-dismissible"><p><strong>%s</strong></p></div>',
                            esc_attr( 'setting-error-' . $iseo_error['code'] ),
                            esc_attr( $iseo_notice_type ),
                            esc_html( $iseo_error['message'] )
                        );
                    }
                    ?>

                    <!-- Form top bar: breadcrumb navigation + save button -->
                    <div class="iseo-form-topbar">
                        <nav class="iseo-breadcrumb" aria-label="Settings breadcrumb">
                            <a href="<?php echo esc_url( admin_url('admin.php?page=improveseo_dashboard') ); ?>">Improve SEO</a>
                            <span class="iseo-breadcrumb-sep">›</span>
                            <span>Settings</span>
                        </nav>
                        <input type="submit" class="iseo-btn-save active setting_submit" value="<?php esc_html_e('Save Changes', 'improveseo') ?>">
                    </div>

                    <!-- ── Section 1: Server Connection ──────────────── -->
                    <div class="iseo-card-section">
                        <div class="iseo-card-header">
                            <div class="iseo-card-icon iseo-icon-server">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
                            </div>
                            <div class="iseo-card-header-text">
                                <h3 class="iseo-card-title">ImproveSEO Server Settings</h3>
                                <p class="iseo-card-subtitle">Connect your site to the ImproveSEO AI engine to start generating content.</p>
                            </div>
                        </div>
                        <div class="iseo-card-body">

                            <div class="iseo-field-group">
                                <label class="iseo-label" for="iseo_api_key">API Key</label>
                                <input type="text" id="iseo_api_key" class="iseo-input" placeholder="Paste your API Key here" name="improveseo_api_key" value="<?php echo esc_attr( get_option('improveseo_api_key') ); ?>">
                                <!-- I1 — API Key now lives on the Websites tab, not Settings.
                                     OLD: Find your API Key in your ImproveSEO Dashboard &rarr; Settings tab -->
                                <span class="iseo-helper-text">Find your API Key in your ImproveSEO Dashboard &rarr; Websites tab</span>
                            </div>

                            <div class="iseo-field-group">
                                <label class="iseo-label" for="iseo_site_code">Site Code</label>
                                <input type="text" id="iseo_site_code" class="iseo-input" placeholder="Paste your Site Code here" name="improveseo_site_code" value="<?php echo esc_attr( get_option('improveseo_site_code') ); ?>">
                                <span class="iseo-helper-text">Find your Site Code in your ImproveSEO Dashboard &rarr; Websites tab</span>
                            </div>

                            <!-- I2 — both credentials now come from the Websites tab, so the
                                 steps no longer send the user to Settings for the API Key,
                                 and the block is titled by what it helps you do rather than
                                 by which fields it fills.
                                 OLD:
                                   <p class="iseo-guide-title"><strong>How to Get API Key &amp; Site Code - Step by Step</strong></p>
                                   <li>Visit your ImproveSEO Dashboard</li>
                                   <li><strong>API Key:</strong> Go to the Settings tab, copy your API Key and paste above</li>
                                   <li><strong>Site Code:</strong> Go to the Websites tab and add the domain you want to connect</li>
                                   <li>A Site Code will be generated, copy the Site Code and paste above</li>
                                   <li>Save settings and start generating content!</li>
                            -->
                            <div class="iseo-guide-block">
                                <p class="iseo-guide-title"><strong>How to connect this website to your ImproveSEO user account</strong></p>
                                <ol class="iseo-guide-steps">
                                    <li>Visit your <a href="https://account.improveseoplugin.com/" target="_blank">ImproveSEO Dashboard</a></li>
                                    <li>Go to the <strong>Websites</strong> tab and add the website you want to connect</li>
                                    <li>Once the website is added, copy and paste the corresponding <strong>API Key</strong> and <strong>Site Code</strong> one by one into the designated fields above.</li>
                                    <li>Save changes and start generating content!</li>
                                </ol>
                            </div>

                        </div>
                        <div class="iseo-card-footer">
                            <div class="iseo-connection-actions">
                                <button type="button" id="test_server_connection" class="iseo-btn-secondary" aria-describedby="iseo-confirm-connection-tip">
                                    🔌 <?php esc_html_e( 'Confirm website connection', 'improveseo' ); ?>
                                </button>
                                <span class="iseo-info-tip" tabindex="0" role="button" aria-label="<?php esc_attr_e( 'About Confirm website connection', 'improveseo' ); ?>">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                                    <span id="iseo-confirm-connection-tip" class="iseo-info-tip-bubble iseo-info-tip-bubble--field iseo-info-tip-bubble--above" role="tooltip">
                                        <?php esc_html_e( "Click 'Confirm website connection' to confirm that this website is properly connected to your ImproveSEO user account.", 'improveseo' ); ?>
                                    </span>
                                </span>
                            </div>
                            <div id="connection_status" class="iseo-connection-status"></div>
                        </div>
                    </div>

                    <!-- ── Section 2: Business Details ───────────────── -->
                    <div class="iseo-card-section">
                        <div class="iseo-card-header">
                            <div class="iseo-card-icon iseo-icon-business">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                            </div>
                            <div class="iseo-card-header-text">
                                <h3 class="iseo-card-title">Business Details</h3>
                                <p class="iseo-card-subtitle">These details were collected during onboarding and are used for schema markup and AI content personalization. Update them here at any time.</p>
                            </div>
                        </div>
                        <div class="iseo-card-body">

                            <div class="iseo-field-group">
                                <label class="iseo-label" for="iseo_business_type">Business Type</label>
                                <select id="iseo_business_type" name="improveseo_business_type" class="iseo-select">
                                    <option value="">— Select type —</option>
                                    <option value="local_service" <?php selected( get_option('improveseo_business_type'), 'local_service' ); ?>>Local Service Business</option>
                                    <option value="ecommerce"     <?php selected( get_option('improveseo_business_type'), 'ecommerce'     ); ?>>E-Commerce / Online Store</option>
                                    <option value="blog"          <?php selected( get_option('improveseo_business_type'), 'blog'          ); ?>>Blog / Content Site</option>
                                    <option value="saas"          <?php selected( get_option('improveseo_business_type'), 'saas'          ); ?>>SaaS / Software</option>
                                    <option value="agency"        <?php selected( get_option('improveseo_business_type'), 'agency'        ); ?>>Agency / Freelancer</option>
                                    <option value="healthcare"    <?php selected( get_option('improveseo_business_type'), 'healthcare'    ); ?>>Healthcare / Medical</option>
                                    <option value="real_estate"   <?php selected( get_option('improveseo_business_type'), 'real_estate'   ); ?>>Real Estate</option>
                                    <option value="restaurant"    <?php selected( get_option('improveseo_business_type'), 'restaurant'    ); ?>>Restaurant / Hospitality</option>
                                    <option value="education"     <?php selected( get_option('improveseo_business_type'), 'education'     ); ?>>Education / Coaching</option>
                                    <option value="other"         <?php selected( get_option('improveseo_business_type'), 'other'         ); ?>>Other</option>
                                </select>
                                <span class="iseo-helper-text">Used for local schema markup type</span>
                            </div>

                            <div class="iseo-field-group">
                                <label class="iseo-label" for="iseo_business_city">City / Location</label>
                                <input type="text" id="iseo_business_city" class="iseo-input" placeholder="e.g. New York, London, Sydney" name="improveseo_business_city" value="<?php echo esc_attr( get_option('improveseo_business_city', '') ); ?>">
                                <span class="iseo-helper-text">Used for local SEO targeting in AI-generated content</span>
                            </div>

                            <div class="iseo-field-group">
                                <label class="iseo-label" for="iseo_business_service">Main Service or Topic</label>
                                <input type="text" id="iseo_business_service" class="iseo-input" placeholder="e.g. Plumbing, Wedding Photography, Digital Marketing" name="improveseo_business_service" value="<?php echo esc_attr( get_option('improveseo_business_service', '') ); ?>">
                                <span class="iseo-helper-text">Used as the default keyword seed for AI article generation</span>
                            </div>

                        </div>
                    </div>

                    <!-- Legacy Settings (hidden — kept for backwards compatibility) -->
                    <div style="display: none;">
                        <div class="seo-form-field">
                            <label> Chat GPT Key (Legacy - Hidden) </label>
                            <input type="text" placeholder="Ex. sadfe456fds2v1xczv86s65g4s5fd4gr6e5tge5r4g54321xc86dssdfewtwerPP" name="improveseo_chatgpt_api_key" value="<?php echo esc_attr( get_option('improveseo_chatgpt_api_key') ); ?>">
                        </div>
                    </div>

                </form>

            </div><!-- .iseo-settings-container -->

            <!-- Account & Subscription -->
            <div class="iseo-standalone-card iseo-account-card">
                <div class="iseo-card-header">
                    <div class="iseo-card-icon iseo-icon-account">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    </div>
                    <div class="iseo-card-header-text">
                        <h3 class="iseo-card-title">ImproveSEO Account</h3>
                        <p class="iseo-card-subtitle">Manage your subscription, credits, support tickets, and website settings from your ImproveSEO dashboard.</p>
                    </div>
                </div>
                <div class="iseo-account-actions">
                    <a href="https://account.improveseoplugin.com/" target="_blank" rel="noopener noreferrer" class="iseo-btn-primary">
                        Open Dashboard &rarr;
                    </a>
                    <a href="https://account.improveseoplugin.com/support" target="_blank" rel="noopener noreferrer" class="iseo-btn-outlined">
                        Support Tickets
                    </a>
                </div>
            </div>

        </div><!-- .iseo-col-main -->

        <!-- ── RIGHT COLUMN: additional settings ──────────── -->
        <div class="iseo-col-sidebar">

            <!-- Content Features Card (toggles auto-save via AJAX — no Save button) -->
            <div class="iseo-standalone-card iseo-features-card">
                <div class="iseo-card-header">
                    <div class="iseo-card-icon iseo-icon-image">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                    </div>
                    <div class="iseo-card-header-text">
                        <h3 class="iseo-card-title">Content Features</h3>
                        <p class="iseo-card-subtitle">Control which AI content generation options are active on your site.</p>
                    </div>
                </div>
                <div class="iseo-card-body iseo-toggles-body">

                    <!-- Main toggle: Enable Featured Images -->
                    <div class="iseo-toggle-group">

                        <div class="iseo-toggle-row iseo-toggle-main">
                            <div class="iseo-toggle-info">
                                <span class="iseo-toggle-label">
                                    Enable Featured Images
                                    <span class="iseo-info-tip" tabindex="0" role="button" aria-label="What is a Featured Image?">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                                        <span class="iseo-info-tip-bubble" role="tooltip">
                                            <strong>What is a Featured Image?</strong>
                                            This is the main image WordPress uses to represent your post — in blog listings, social media shares, and sometimes at the top of the post itself. By default, we set your hero image as the Featured Image. If your theme already displays the hero image inside the post, turn this off to avoid showing it twice.
                                        </span>
                                    </span>
                                </span>
                                <span class="iseo-toggle-desc">Automatically attach main post image (AI generated or uploaded) as WP featured image to AI-created posts.</span>
                            </div>
                            <label class="iseo-toggle-switch" aria-label="Enable Featured Images">
                                <input type="checkbox" id="iseo_featured_images_enabled" name="improveseo_featured_images_enabled" value="1" <?php checked( get_option( 'improveseo_featured_images_enabled', '1' ), '1' ); ?>>
                                <span class="iseo-toggle-track"></span>
                            </label>
                        </div>

                        <!-- Sub-toggles (indented) -->
                        <div class="iseo-sub-toggles">

                            <div class="iseo-toggle-row iseo-toggle-sub">
                                <div class="iseo-toggle-info">
                                    <span class="iseo-toggle-label">For Bulk Posts</span>
                                    <span class="iseo-toggle-desc">Apply when running bulk post generation projects.</span>
                                </div>
                                <label class="iseo-toggle-switch" aria-label="Enable for Bulk Posts">
                                    <input type="checkbox" id="iseo_featured_images_bulk" name="improveseo_featured_images_bulk" value="1" <?php checked( get_option( 'improveseo_featured_images_bulk', '1' ), '1' ); ?>>
                                    <span class="iseo-toggle-track"></span>
                                </label>
                            </div>

                            <div class="iseo-toggle-row iseo-toggle-sub iseo-toggle-last">
                                <div class="iseo-toggle-info">
                                    <span class="iseo-toggle-label">For Single Post</span>
                                    <span class="iseo-toggle-desc">Apply when generating a single post at a time.</span>
                                </div>
                                <label class="iseo-toggle-switch" aria-label="Enable for Single Post">
                                    <input type="checkbox" id="iseo_featured_images_single" name="improveseo_featured_images_single" value="1" <?php checked( get_option( 'improveseo_featured_images_single', '1' ), '1' ); ?>>
                                    <span class="iseo-toggle-track"></span>
                                </label>
                            </div>

                        </div><!-- .iseo-sub-toggles -->

                    </div><!-- .iseo-toggle-group -->

                    <div class="iseo-features-save" id="iseo_toggle_save_status" aria-live="polite" style="font-size:12px; color:#6b7280; min-height:18px;"></div>
                </div>
            </div><!-- .iseo-features-card -->

        </div><!-- .iseo-col-sidebar -->

    </div><!-- .iseo-settings-grid -->

</div><!-- .iseo-settings-page -->

<!-- ── Save-refused modal ─────────────────────────────────────────────────
     Save Changes already refuses an API Key + Site Code pair the server does not accept —
     the submit gate in the script below client-side, improveseo_sanitize_and_verify_credentials_field()
     server-side. It used to say so only by rewriting #connection_status, at the BOTTOM of the
     connection card, below the guide block and a scroll or two down on a laptop, while the Save
     button is at the top. Pressing Save and seeing the page sit still is indistinguishable from
     pressing Save and having it work, which is exactly the complaint.

     Deliberately its own element rather than the shared connection guard in views/layouts/main.php:
     that modal's whole job is to send someone TO this page, and its copy ("This site isn't
     connected", button "Connect Website" → Settings) is nonsense once you are standing on Settings.
     What it borrows is the look — card, type scale, greys, amber icon, teal button — via the same
     stylesheet the Keyword Generator's allowance notice uses, so every blocking notice in the
     plugin reads as one family. -->
<div id="iseo-save-issue-overlay" class="iseo-save-issue-overlay" hidden>
	<div class="iseo-save-issue-dialog" role="alertdialog" aria-modal="true" aria-labelledby="iseo-save-issue-title" aria-describedby="iseo-save-issue-text">

		<button type="button" id="iseo-save-issue-close" class="iseo-save-issue-close" aria-label="<?php esc_attr_e( 'Close', 'improveseo' ); ?>">&times;</button>

		<div class="iseo-save-issue-icon">
			<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
				<circle cx="12" cy="12" r="10"></circle>
				<line x1="12" y1="8" x2="12" y2="12"></line>
				<line x1="12" y1="16" x2="12.01" y2="16"></line>
			</svg>
		</div>

		<h3 id="iseo-save-issue-title" class="iseo-save-issue-title"></h3>

		<div class="iseo-save-issue-body">
			<p id="iseo-save-issue-text" class="iseo-save-issue-text"></p>
			<!-- The server's or the network's own words, when there are any. Quieter than the
			     sentence above: it explains nothing to most people, but it is the one thing that
			     helps when someone sends the screenshot to support. -->
			<p id="iseo-save-issue-detail" class="iseo-save-issue-detail" hidden></p>
			<!-- Shown only when the credentials themselves are the problem — pointless, and
			     actively misleading, when the server simply could not be reached. -->
			<p id="iseo-save-issue-hint" class="iseo-save-issue-hint" hidden>
				Both values come from your <a class="iseo-save-issue-link" href="https://account.improveseoplugin.com/" target="_blank" rel="noopener noreferrer">ImproveSEO Dashboard</a> &rarr; Websites tab, for THIS website.
			</p>
		</div>

		<!-- Dismiss, and nothing else. The fields that need correcting are on the page behind this
		     card, so the button closes and puts the cursor in the first of them. -->
		<button type="button" id="iseo-save-issue-ok" class="iseo-save-issue-ok">Got it</button>
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    // ── Featured-image toggle cascade ─────────────────────
    // Main toggle drives both sub-toggles; the sub-toggles drive the main
    // back. Rules:
    //   • Main ON/OFF  → both subs follow the main.
    //   • A sub turns ON → main turns ON (the other sub is left untouched).
    //   • Both subs OFF  → main turns OFF.
    (function() {
        var main = document.getElementById('iseo_featured_images_enabled');
        var subs = [
            document.getElementById('iseo_featured_images_bulk'),
            document.getElementById('iseo_featured_images_single')
        ];
        if (!main || subs.some(function(s) { return !s; })) return;

        // Auto-save (no Save button): every toggle change is written immediately
        // via the same admin-ajax + nonce pattern as the connection test. The
        // cascade can flip more than one toggle per interaction, so the current
        // state of all three is sent together — only these three options, nothing else.
        var statusEl = document.getElementById('iseo_toggle_save_status');
        var statusTimer = null;
        function showToggleStatus(text, ok) {
            if (!statusEl) return;
            statusEl.textContent = text;
            statusEl.style.color = ok ? '#0f7b6c' : '#b32d2e';
            if (statusTimer) clearTimeout(statusTimer);
            if (ok) statusTimer = setTimeout(function() { statusEl.textContent = ''; }, 2500);
        }
        function saveFeatureToggles() {
            var data = new FormData();
            data.append('action', 'improveseo_save_feature_toggles');
            data.append('nonce', '<?php echo esc_js( wp_create_nonce("improveseo_feature_toggles_nonce") ); ?>');
            data.append('enabled', main.checked ? '1' : '0');
            data.append('bulk', subs[0].checked ? '1' : '0');
            data.append('single', subs[1].checked ? '1' : '0');
            showToggleStatus('Saving…', true);
            fetch('<?php echo esc_url( admin_url("admin-ajax.php") ); ?>', { method: 'POST', body: data })
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    showToggleStatus(res && res.success ? '✓ Saved' : '✗ Save failed — try again', !!(res && res.success));
                })
                .catch(function() {
                    showToggleStatus('✗ Save failed — try again', false);
                });
        }

        main.addEventListener('change', function() {
            subs.forEach(function(sub) { sub.checked = main.checked; });
            saveFeatureToggles();
        });

        subs.forEach(function(sub) {
            sub.addEventListener('change', function() {
                if (sub.checked) {
                    main.checked = true;
                } else if (subs.every(function(s) { return !s.checked; })) {
                    main.checked = false;
                }
                saveFeatureToggles();
            });
        });
    })();

    // Build the connection-test status panel — mirrors the dashboard's plan/credit view.
    // Null-safe: if the server omits the richer plan/trial/credit_details fields (older
    // build), it falls back to the flat credit totals.
    function renderConnectionPanel(d) {
        d = d || {};
        function esc(s) {
            return String(s == null ? '' : s).replace(/[&<>"']/g, function(c) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
            });
        }

        var plan = d.plan || null;
        var trial = d.trial || null;
        var cd = d.credit_details || null;

        // Plan / trial status line + badge.
        //
        // The label itself comes from iseoPlanLabel() in views/layouts/main.php — one resolver
        // shared with the bulk-post gate, so the same account can never be named "Scale" here
        // and "Pro" there. This screen only chooses the colour and the sentence beneath it.
        var badgeText, badgeColor, statusLine;
        badgeText = (typeof iseoPlanLabel === 'function')
            ? iseoPlanLabel(plan, d.subscription, trial)
            : ((plan && plan.name) ? plan.name : 'Connected');

        if (plan && plan.is_paid) {
            badgeColor = '#0f7b6c';
            statusLine = 'Active subscription — full access.';
        } else if (trial && trial.expired) {
            badgeColor = '#b3521a';
            statusLine = 'Your free trial has ended. Upgrade to restore full access — any credits you purchased remain usable.';
        } else if (trial && trial.active) {
            badgeColor = '#0f7b6c';
            var days = (trial.days_remaining != null) ? trial.days_remaining : null;
            var ends = trial.ends_at ? new Date(trial.ends_at).toLocaleDateString() : '';
            statusLine = 'Free trial active'
                + (days != null ? ' — ' + days + ' day' + (days === 1 ? '' : 's') + ' left' : '')
                + (ends ? ' (ends ' + esc(ends) + ')' : '') + '.';
        } else {
            badgeColor = '#0f7b6c';
            statusLine = 'Account connected.';
        }
        badgeText = esc(badgeText);

        // ── Credits ────────────────────────────────────────────────────────────────────
        // One pooled balance, presented the way the CMS presents it under TOTAL CREDITS
        // REMAINING. This used to be three rows — Content / Images / Keyword lists — which
        // read as three separate allowances but were the same pool printed three times, so
        // the two screens described the same account in incompatible ways.
        var pooled = (cd && cd.content) ? cd.content : null;
        var total = null;
        if (pooled && pooled.total != null)              { total = pooled.total; }
        else if (d.credits && d.credits.total != null)   { total = d.credits.total; }
        else if (d.credits && d.credits.content != null) { total = d.credits.content; }

        // What the balance buys. The number is the admin server's (GET /credits/estimate,
        // fetched by test_improveseo_connection), the same one the CMS shows — this used to
        // divide here by article + image and read 7 pieces where the CMS read 5.
        var pieces = (d.pieces != null && !isNaN(parseInt(d.pieces, 10))) ? parseInt(d.pieces, 10) : null;

        // Breakdown rows.
        //
        // Mirrors CreditBreakdown.jsx / creditBreakdown.js on the CMS: one row per
        // BATCH (source + expiry date), not one row per source. A pooled
        // plan_remaining/purchased_remaining split cannot say which batch expires
        // when — the CMS moved off that shape for the same reason. d.lots carries
        // the batches (added server-side alongside credit_details, see
        // getCreditLotSummary in users.routes.ts); an older, un-redeployed server
        // omits it, and the two-row pooled view below is what this card showed
        // before lots existed.
        var breakdownRows = '';
        var breakdownFooterHtml = '';
        var lots = Array.isArray(d.lots) ? d.lots : null;
        var trialActive = !!(trial && trial.active);
        var trialEndsOn = trialActive ? trial.ends_at : null;

        function formatExpiry(value) {
            if (!value) { return null; }
            var parsed = new Date(value);
            return isNaN(parsed.getTime())
                ? null
                : parsed.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', timeZone: 'UTC' });
        }
        // Whole calendar days from today (UTC) to an expiry date, so "expiring
        // soon" cannot flip a day early or late depending on the visitor's
        // timezone offset.
        function daysUntil(value) {
            var parsed = value ? new Date(value) : null;
            if (!parsed || isNaN(parsed.getTime())) { return null; }
            var now = new Date();
            var startUTC = Date.UTC(now.getFullYear(), now.getMonth(), now.getDate());
            var endUTC = Date.UTC(parsed.getUTCFullYear(), parsed.getUTCMonth(), parsed.getUTCDate());
            return Math.round((endUTC - startUTC) / 86400000);
        }
        function fmtAmount(n) {
            return Number(n).toLocaleString();
        }

        if (lots && lots.length) {
            // A trial's plan lot IS the trial grant; the expiry stored on it is a
            // two-month default that never actually applies (the trial-end path
            // zeroes those credits first) — see creditBreakdown.js on the CMS for
            // the full reasoning. Substituting the real trial end here keeps this
            // card from promising two months of use for credits with days left.
            var cleanLots = lots
                .filter(function (l) { return l && Number(l.remaining) > 0; })
                .map(function (l) {
                    var isPlan = l.source === 'plan';
                    return {
                        source: isPlan ? 'plan' : 'purchased',
                        // From the server's is_free (credit_lots joined to
                        // subscription_plans) — true only for a Free Plan's
                        // own allotment, never for a paid plan's. Threaded
                        // through so planLabel() can name it, without
                        // touching `source` itself (still just plan/purchased
                        // everywhere else this card uses it — sorting, the
                        // trial-active branch below).
                        isFree: isPlan && !!l.is_free,
                        expiresOn: (trialEndsOn && isPlan) ? trialEndsOn : (l.expires_on || null),
                        remaining: Number(l.remaining) || 0,
                    };
                });

            // A plan balance spans at most two billing cycles, so at most two plan
            // batches exist; the later-expiring one is necessarily this cycle's.
            var planExpiries = cleanLots
                .filter(function (l) { return l.source === 'plan'; })
                .map(function (l) { return l.expiresOn; })
                .filter(Boolean)
                .sort();
            var latestPlanExpiry = planExpiries.length ? planExpiries[planExpiries.length - 1] : null;
            var severalPlanBatches = new Set(planExpiries).size > 1;

            function planLabel(expiresOn, isFree) {
                // Active-trial wording takes priority over isFree: the two can
                // both be true (a Free Trial's own lot IS free-plan-sourced),
                // and "Free trial credits" is the more specific, already-correct
                // answer for that case — this must not regress it.
                if (trialActive) { return 'Free trial credits'; }
                // Free Plan (not mid-trial — trial ended, or none at all) still
                // holding its free allotment: this is the case that used to fall
                // all the way through to generic "Plan credits", which is what
                // the account's own "Free Plan" status line was reported as
                // contradicting.
                if (isFree) { return 'Free credits'; }
                if (!severalPlanBatches) { return 'Plan credits'; }
                return expiresOn === latestPlanExpiry ? 'Plan credits — this cycle' : 'Plan credits — last cycle';
            }

            var lotRows = cleanLots.map(function (l) {
                var days = daysUntil(l.expiresOn);
                return {
                    source: l.source,
                    label: l.source === 'plan' ? planLabel(l.expiresOn, l.isFree) : 'Purchased credits',
                    remaining: l.remaining,
                    expiresOn: l.expiresOn,
                    expiresLabel: formatExpiry(l.expiresOn),
                    // Same 14-day window the CMS flags (EXPIRING_SOON_DAYS).
                    expiringSoon: days != null && days >= 0 && days <= 14,
                };
            });

            // Soonest-first — both the display order and the order the credits are
            // actually spent (first-expiring-first), so the list cannot misrepresent
            // which balance is at risk. No date sorts last: it cannot be urgent.
            lotRows.sort(function (a, b) {
                if (a.expiresOn == null) { return 1; }
                if (b.expiresOn == null) { return -1; }
                if (a.expiresOn !== b.expiresOn) { return a.expiresOn < b.expiresOn ? -1 : 1; }
                if (a.source === b.source) { return 0; }
                return a.source === 'plan' ? -1 : 1;
            });

            breakdownRows = lotRows.map(function (row) {
                // The flag span is rendered on every row, visible or not — a
                // conditionally-present 3rd element would change the flex
                // distribution and shift the amount sideways only on flagged
                // rows. Reserving it keeps the amount column aligned down every
                // row, flagged or not (same reasoning as the CMS's grid column).
                return '<div class="iseo-credits-row' + (row.expiringSoon ? ' is-expiring-soon' : '') + '">'
                     + '<div class="iseo-credits-row-label"><span>' + esc(row.label) + '</span>'
                     + '<small>' + esc(row.expiresLabel ? ('expiring ' + row.expiresLabel) : 'no expiry date') + '</small>'
                     + '</div>'
                     + '<span class="iseo-credits-row-value">' + esc(fmtAmount(row.remaining)) + '</span>'
                     + '<span class="iseo-credits-row-flag"' + (row.expiringSoon ? '' : ' aria-hidden="true"') + '>Expiring soon</span>'
                     + '</div>';
            }).join('');

            breakdownFooterHtml = '<div class="iseo-credits-footer">' + esc(
                trialActive
                    ? 'Free trial credits are usable until your trial ends. Credits expiring soonest are used first.'
                    : 'Plan credits stay usable for two billing cycles. Credits expiring soonest are used first.'
            ) + '</div>';
        } else {
            // Fallback for a server that has not shipped per-batch data yet: the
            // pooled plan-vs-purchased split, no dates — what this card showed
            // before d.lots existed.
            function creditRow(label, note, value) {
                return '<div class="iseo-credits-row">'
                     + '<div class="iseo-credits-row-label"><span>' + esc(label) + '</span>'
                     + (note ? '<small>' + esc(note) + '</small>' : '')
                     + '</div>'
                     + '<span class="iseo-credits-row-value">' + esc(fmtAmount(value)) + '</span>'
                     + '</div>';
            }
            if (pooled && pooled.plan_remaining != null) {
                breakdownRows += creditRow('Plan credits', 'Included with your subscription', pooled.plan_remaining);
            }
            if (pooled && pooled.purchased_remaining != null) {
                breakdownRows += creditRow('Purchased credits', 'Top-ups you bought', pooled.purchased_remaining);
            }
        }

        var creditsCard = ''
          + '<div class="iseo-credits-card">'
          +   '<div class="iseo-credits-heading">Total credits remaining</div>'
          +   '<div class="iseo-credits-total">' + esc(total != null ? fmtAmount(total) : '—') + '</div>'
          +   (pieces != null
                ? '<div class="iseo-credits-hint">This equals approximately ' + esc(pieces)
                  + ' piece' + (pieces === 1 ? '' : 's') + ' of SEO content with AI Images</div>'
                : '')
          +   (breakdownRows
                ? '<button type="button" class="iseo-credits-toggle" aria-expanded="true">Hide breakdown</button>'
                  + '<div class="iseo-credits-breakdown">' + breakdownRows + breakdownFooterHtml + '</div>'
                : '')
          + '</div>';

        var who = esc(d.email || d.user || 'Authenticated');

        return ''
          + '<div style="border:1px solid #d5e5e2;background:#f6faf9;border-radius:10px;padding:14px 16px;margin-top:10px;">'
          +   '<div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">'
          +     '<span style="color:#0f7b6c;font-size:16px;">✅</span>'
          +     '<strong style="color:#111827;">Connected</strong>'
          +     '<span style="margin-left:auto;background:' + badgeColor + ';color:#fff;font-size:11px;font-weight:700;letter-spacing:.03em;padding:3px 9px;border-radius:9999px;">' + badgeText + '</span>'
          +   '</div>'
          +   '<div style="color:#4b5563;font-size:13px;margin-bottom:12px;line-height:1.5;">' + statusLine + '</div>'
          +   creditsCard
          +   '<div style="border-top:1px solid #e5eeec;margin-top:10px;padding-top:8px;font-size:12px;color:#6b7280;">Account: ' + who + ' · Server: ' + esc(d.server || 'Connected') + '</div>'
          + '</div>';
    }

    /**
     * Show/Hide breakdown, bound once by delegation because the card is re-rendered on every
     * connection check — a handler bound to the button itself would be thrown away with it.
     */
    document.addEventListener('click', function (e) {
        var toggle = e.target.closest ? e.target.closest('.iseo-credits-toggle') : null;
        if (!toggle) { return; }
        var breakdown = toggle.parentNode.querySelector('.iseo-credits-breakdown');
        if (!breakdown) { return; }
        var hidden = breakdown.classList.toggle('is-hidden');
        toggle.setAttribute('aria-expanded', hidden ? 'false' : 'true');
        toggle.textContent = hidden ? 'Show breakdown' : 'Hide breakdown';
    });

    /**
     * Ask the server whether the saved API Key and Site Code actually work together.
     *
     * The server is the only thing that can answer this: it resolves the user from the key, then
     * requires the site code to belong to a website that user owns (findWebsiteByCode in
     * apiAuth.middleware.ts). A key that is valid on its own paired with another website's code
     * comes back 403 "Website not found or not authorized", which is exactly the mistake this
     * screen invites — the key is shared across a customer's sites, the code is not.
     *
     * `auto` distinguishes the check this page runs for itself on load from the one the button
     * runs. They differ only in chrome: the automatic one must not seize the button or shout while
     * it is working, because the user did not ask for it and may be mid-edit.
     */
    // Same string as the button's markup, so restoring it after a click cannot drift from it.
    const ISEO_CONFIRM_LABEL = '🔌 ' + <?php echo wp_json_encode( __( 'Confirm website connection', 'improveseo' ) ); ?>;

    // Shown whenever the answer is "these credentials do not connect this website to an
    // account" — including a click with a field left empty. It points back at the steps printed
    // just above the button rather than paraphrasing them.
    const ISEO_NOT_CONNECTED_HTML =
        '<div class="iseo-status-error">❌ <div><strong>Not connected.</strong><br>' +
        'This website is not connected to your ImproveSEO user account. Follow the steps as outlined above under ' +
        '\'<strong>How to connect this website to your ImproveSEO user account</strong>\'</div></div>';

    // For text that did not originate on this page (server or network messages).
    function iseoEscapeHtml(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    /* ── "That save did not go through" ─────────────────────────────────────
       The refusal used to be reported only by rewriting #connection_status, which sits at the
       BOTTOM of the connection card — below the guide block, two screens down on a laptop —
       while Save Changes is at the top. Pressing Save and seeing the page sit still is
       indistinguishable from pressing Save and having it work, which is the whole complaint.
       The panel is still written (it is the connection card's own state, and it survives the
       modal being dismissed); the modal is what makes the refusal impossible to walk past.

       Markup and styling: see #iseo-save-issue-overlay above. */
    const iseoSaveIssueOverlay = document.getElementById('iseo-save-issue-overlay');

    // Where the cursor goes when the modal closes: the field most likely to be wrong, and the
    // reason the modal has no link of its own — everything it asks for is on the page behind it.
    function iseoFocusCredentialField() {
        const apiInput = document.querySelector('input[name="improveseo_api_key"]');
        if (apiInput) { apiInput.focus(); }
    }

    function iseoHideSaveIssue(refocus) {
        if (!iseoSaveIssueOverlay) { return; }
        iseoSaveIssueOverlay.hidden = true;
        if (refocus) { iseoFocusCredentialField(); }
    }

    /**
     * @param {{title: string, text: string, hint: boolean}} issue - the refusal, in the user's terms.
     * @param {string=} detail - the server's or the network's own words, when there are any.
     *   Shown underneath, smaller: it explains nothing to most people, but it is the only thing
     *   that helps when someone pastes a screenshot into support.
     */
    function iseoShowSaveIssue(issue, detail) {
        if (!iseoSaveIssueOverlay) { return; }

        document.getElementById('iseo-save-issue-title').textContent = issue.title;
        document.getElementById('iseo-save-issue-text').textContent  = issue.text;

        // textContent, not innerHTML: `detail` carries server and network strings that never
        // originated on this page.
        const detailEl = document.getElementById('iseo-save-issue-detail');
        detailEl.textContent = detail || '';
        detailEl.hidden      = !detail;

        document.getElementById('iseo-save-issue-hint').hidden = !issue.hint;

        iseoSaveIssueOverlay.hidden = false;

        const ok = document.getElementById('iseo-save-issue-ok');
        if (ok) { ok.focus(); }
    }

    // The three refusals this screen can produce. Worded for someone who has just pressed Save
    // Changes, so each one says what happened to their settings — "not saved", "previous
    // settings kept" — before it says what to do, because that is the question the silent
    // version of this screen left them asking.
    const ISEO_SAVE_ISSUES = {
        // Live 401/403: wrong key, a site code from another account, or a site code issued for
        // a DIFFERENT one of this account's websites (the admin server enforces x-site-domain).
        // All three are one thing to the user: this pair does not connect THIS website.
        rejected: {
            title: 'These credentials were not saved',
            text: 'ImproveSEO did not accept this API Key and Site Code for this website, so your previously saved settings have been kept. Check that you copied both values from the entry for this website, then press Save Changes again.',
            hint: true
        },
        incomplete: {
            title: 'Enter both the API Key and the Site Code',
            text: 'A connection needs both values, so nothing has been saved. Fill in both fields — or clear both, to disconnect this website from ImproveSEO.',
            hint: true
        },
        // Not a verdict on the credentials: the server never answered. Saying "not connected"
        // here would send someone off to fix something that is not broken.
        unreachable: {
            title: 'Could not verify your credentials',
            text: 'The ImproveSEO server did not answer, so nothing has been saved. This is usually temporary — press Save Changes again in a moment.',
            hint: false
        }
    };

    (function () {
        if (!iseoSaveIssueOverlay) { return; }

        const closeBtn = document.getElementById('iseo-save-issue-close');
        const okBtn    = document.getElementById('iseo-save-issue-ok');

        if (closeBtn) { closeBtn.addEventListener('click', function () { iseoHideSaveIssue(true); }); }
        if (okBtn)    { okBtn.addEventListener('click',    function () { iseoHideSaveIssue(true); }); }

        // Click outside the card, and Escape — same two dismissals as the connection guard, so
        // the two modals do not behave differently for looking the same.
        iseoSaveIssueOverlay.addEventListener('click', function (e) {
            if (e.target === iseoSaveIssueOverlay) { iseoHideSaveIssue(true); }
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !iseoSaveIssueOverlay.hidden) { iseoHideSaveIssue(true); }
        });
    })();

    /**
     * @param {boolean} auto - true for the silent on-load/on-focus check; false for the
     *   "Confirm website connection" button click, which owns the button's busy state.
     * @param {function(boolean, object=)=} onDone - called once with (connected, failure).
     *   Lets a caller (the Save-button submit gate below) react to the result without
     *   duplicating this function's request/response handling.
     * @param {string=} context - 'presubmit' swaps the network/5xx-failure copy from
     *   "your settings are saved" (true for the Confirm button, which runs after a save)
     *   to "settings have not been saved" (true here, which runs before one).
     */
    function improveseoRunConnectionCheck(auto, onDone, context) {
        const button    = document.getElementById('test_server_connection');
        const statusDiv = document.getElementById('connection_status');
        const apiKey    = document.querySelector('input[name="improveseo_api_key"]').value.trim();
        const siteCode  = document.querySelector('input[name="improveseo_site_code"]').value.trim();

        if (!apiKey || !siteCode) {
            // Nothing to check. Silent when automatic: a fresh install has no credentials yet and
            // an error on first sight of the screen would read as a fault.
            if (!auto) {
                statusDiv.innerHTML = ISEO_NOT_CONNECTED_HTML;
            }
            return;
        }

        if (!auto) {
            button.disabled = true;
            button.textContent = '🔄 Confirming connection…';
        }
        statusDiv.innerHTML = auto
            ? '<div class="iseo-status-loading">⏳ Checking connection…</div>'
            : '<div class="iseo-status-loading">⏳ Confirming this website\'s connection…</div>';

        const restore = function () {
            if (auto) { return; }
            button.disabled = false;
            button.textContent = ISEO_CONFIRM_LABEL;
        };

        const data = new FormData();
        data.append('action', 'test_improveseo_connection');
        data.append('api_key', apiKey);
        data.append('site_code', siteCode);
        data.append('nonce', '<?php echo esc_js( wp_create_nonce("test_connection_nonce") ); ?>');

        fetch('<?php echo esc_url( admin_url("admin-ajax.php") ); ?>', {
            method: 'POST',
            body: data
        })
        .then(response => response.json())
        .then(result => {
            restore();
            if (result.success) {
                statusDiv.innerHTML = renderConnectionPanel(result.data);
                if (onDone) { onDone(true); }
            } else {
                const failure = result.data || {};
                // 401 (API key unknown) and 403 (site code not on that key's account, OR — now
                // that the admin server enforces x-site-domain — a site code that belongs to a
                // DIFFERENT one of this account's websites) mean the same thing to the user:
                // this website is not connected to their account. The steps above are the fix.
                if (failure.status === 401 || failure.status === 403) {
                    statusDiv.innerHTML = ISEO_NOT_CONNECTED_HTML;
                    if (onDone) { onDone(false, failure); }
                    return;
                }
                // Anything else is the server failing to answer, not a verdict on the credentials,
                // so saying "not connected" would send the user to fix something that is not broken.
                const err = failure.error || failure.message || 'Unknown error';
                const savedNote = context === 'presubmit'
                    ? 'Settings have not been saved — try Save Changes again.'
                    : 'Your settings are saved. Press Confirm website connection to try again.';
                statusDiv.innerHTML = `
                    <div class="iseo-status-error">
                        ❌ <div><strong>Could not confirm the connection.</strong><br>
                        ${iseoEscapeHtml(err)}<br>
                        ${savedNote}</div>
                    </div>
                `;
                if (onDone) { onDone(false, failure); }
            }
        })
        .catch(error => {
            restore();
            const savedNote = context === 'presubmit'
                ? 'Settings have not been saved — try Save Changes again.'
                : 'Your settings are saved. Press Confirm website connection to try again.';
            statusDiv.innerHTML = `
                <div class="iseo-status-error">
                    ❌ <div><strong>Could not reach the ImproveSEO server.</strong><br>
                    ${iseoEscapeHtml(error.message)}<br>
                    ${savedNote}</div>
                </div>
            `;
            if (onDone) { onDone(false, { error: error.message }); }
        });
    }

    document.getElementById('test_server_connection').addEventListener('click', function () {
        improveseoRunConnectionCheck(false);
    });

    /**
     * Save-button gate: block the native options.php submit until we know the pair
     * verifies, using the same live check as the Confirm-connection button — so a bad
     * pairing is caught without a wasted page reload. This is UX only; includes/settings.php
     * enforces the real gate server-side (sanitize_callback on both options), so a direct
     * POST or JS-disabled browser is still refused there.
     */
    (function () {
        const settingsForm = document.querySelector('form.iseo-settings-form');
        if (!settingsForm) { return; }

        var iseoBypassSubmitGate = false;

        settingsForm.addEventListener('submit', function (e) {
            if (iseoBypassSubmitGate) { return; } // programmatic re-submit after a passed check

            const apiInput  = document.querySelector('input[name="improveseo_api_key"]');
            const codeInput = document.querySelector('input[name="improveseo_site_code"]');
            const apiKey    = apiInput.value.trim();
            const siteCode  = codeInput.value.trim();

            // Clearing both fields disconnects the site — nothing to verify, let it save.
            if (!apiKey && !siteCode) { return; }

            // Credentials untouched — don't hold this save hostage to a network call.
            //
            // defaultValue is the value PHP rendered into the markup, i.e. what is currently
            // stored, so this is "did the user actually edit either field". These inputs share
            // a form with Business Type / City / Service, and most saves after setup only touch
            // those: verifying anyway would mean a cold or unreachable admin server blocks an
            // edit that has nothing to do with the connection. Mirrors the same skip in
            // improveseo_sanitize_and_verify_credentials_field().
            if (apiKey === apiInput.defaultValue.trim() && siteCode === codeInput.defaultValue.trim()) {
                return;
            }

            e.preventDefault();

            const statusDiv = document.getElementById('connection_status');

            // Incomplete pair: same refusal the server-side gate would give, without spending
            // a round trip on the admin server to learn what we already know.
            if (!apiKey || !siteCode) {
                statusDiv.innerHTML = ISEO_NOT_CONNECTED_HTML;
                iseoShowSaveIssue(ISEO_SAVE_ISSUES.incomplete);
                return;
            }

            const saveBtn = settingsForm.querySelector('.setting_submit');
            const originalLabel = saveBtn ? saveBtn.value : '';
            if (saveBtn) {
                saveBtn.disabled = true;
                saveBtn.value = 'Verifying connection…';
            }

            improveseoRunConnectionCheck(true, function (connected, failure) {
                if (saveBtn) {
                    saveBtn.disabled = false;
                    saveBtn.value = originalLabel;
                }
                if (connected) {
                    iseoBypassSubmitGate = true;
                    settingsForm.submit();
                    return;
                }

                // Not connected: stay on the page. The inline panel has already been written by
                // the check itself; the modal is what actually tells someone standing at the Save
                // button, two screens above it, that their credentials were not stored.
                //
                // 401/403 is a verdict on the pair; anything else (timeout, 5xx, DNS) is the
                // server failing to answer, and the two must not be worded the same — see the
                // copy in ISEO_SAVE_ISSUES.
                failure = failure || {};
                if (failure.status === 401 || failure.status === 403) {
                    iseoShowSaveIssue(ISEO_SAVE_ISSUES.rejected);
                } else {
                    iseoShowSaveIssue(ISEO_SAVE_ISSUES.unreachable, failure.error || failure.message || '');
                }
            }, 'presubmit');
        });
    })();

<?php if ( '' !== $iseo_save_failure ) : ?>
    // A save that reached options.php and was refused THERE — the no-JS path, or anything that
    // POSTs this form directly. The submit gate above normally catches these before the request
    // leaves the page, so this only fires when it did not run; the wording is the server's own,
    // shown in the same card as every other refusal instead of as a notice that scrolls away.
    iseoShowSaveIssue({
        title: <?php echo wp_json_encode( __( 'Your settings were not saved', 'improveseo' ) ); ?>,
        text:  <?php echo wp_json_encode( $iseo_save_failure ); ?>,
        hint:  <?php echo ( 'iseo_verify_unreachable' === $iseo_save_failure_code ) ? 'false' : 'true'; ?>
    });
<?php endif; ?>

    // Answer "did that work?" on page load without making the user hunt for the button — it
    // runs after the page is already interactive, so a slow server costs nothing but a spinner
    // in one corner.
    //
    // NOTE this is no longer the only live check on this screen: editing either credential and
    // pressing Save Changes now verifies BEFORE the form is allowed through (see the submit gate
    // above, and the authoritative one in includes/sanitize callbacks). The connection ping in
    // includes/connection-status.php is still fire-and-forget — that one is only CMS status
    // bookkeeping and nothing waits on its answer.
    improveseoRunConnectionCheck(true);
});
</script>

<?php View::endSection('content') ?>

<?php View::make('layouts.main') ?>
