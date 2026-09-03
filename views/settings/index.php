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
                            <button type="button" id="test_server_connection" class="iseo-btn-secondary">
                                🔌 Test Server Connection
                            </button>
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

        // What the balance buys. Priced from the server's own table — the same one
        // check_bulk_credits() gates against — so the estimate here and the cost shown in the
        // generation wizard cannot drift apart. No pricing published, no claim made.
        var pricing = d.pricing || null;
        var perPiece = null;
        if (pricing && pricing.content && pricing.content.medium != null && pricing.image != null) {
            perPiece = parseInt(pricing.content.medium, 10) + parseInt(pricing.image, 10);
        }
        var pieces = (perPiece > 0 && total != null && !isNaN(parseInt(total, 10)))
            ? Math.round(parseInt(total, 10) / perPiece)
            : null;

        // Breakdown rows. Each is emitted only when the server actually supplied the number,
        // so an older server shows a smaller card rather than a row of dashes.
        var breakdownRows = '';
        function creditRow(label, note, value) {
            return '<div class="iseo-credits-row">'
                 + '<div class="iseo-credits-row-label"><span>' + esc(label) + '</span>'
                 + (note ? '<small>' + esc(note) + '</small>' : '')
                 + '</div>'
                 + '<span class="iseo-credits-row-value">' + esc(value) + '</span>'
                 + '</div>';
        }
        if (pooled && pooled.plan_remaining != null) {
            breakdownRows += creditRow('Plan credits', 'Included with your subscription', pooled.plan_remaining);
        }
        if (pooled && pooled.purchased_remaining != null) {
            breakdownRows += creditRow('Purchased credits', 'Top-ups you bought', pooled.purchased_remaining);
        }
        // Expiry is optional: the endpoint does not publish it today. Read it defensively under
        // the names it would most plausibly arrive as, and render nothing at all when absent —
        // an invented or blank date on a billing screen is worse than no row.
        var nextExpiryAmount = (pooled && pooled.next_expiry_amount != null) ? pooled.next_expiry_amount
                             : ((d.credits && d.credits.next_expiry_amount != null) ? d.credits.next_expiry_amount : null);
        var nextExpiryDate = (pooled && (pooled.next_expiry_at || pooled.next_expiry)) || (d.credits && (d.credits.next_expiry_at || d.credits.next_expiry)) || null;
        if (nextExpiryDate) {
            var parsed = new Date(nextExpiryDate);
            var when = isNaN(parsed.getTime()) ? String(nextExpiryDate) : parsed.toLocaleDateString();
            breakdownRows += creditRow('Next to expire', 'on ' + when, nextExpiryAmount != null ? nextExpiryAmount : '—');
        }

        var creditsCard = ''
          + '<div class="iseo-credits-card">'
          +   '<div class="iseo-credits-heading">Total credits remaining</div>'
          +   '<div class="iseo-credits-total">' + esc(total != null ? total : '—') + '</div>'
          +   (pieces != null
                ? '<div class="iseo-credits-hint">This equals approximately ' + esc(pieces)
                  + ' piece' + (pieces === 1 ? '' : 's') + ' of SEO content with AI Images</div>'
                : '')
          +   (breakdownRows
                ? '<button type="button" class="iseo-credits-toggle" aria-expanded="true">Hide breakdown</button>'
                  + '<div class="iseo-credits-breakdown">' + breakdownRows + '</div>'
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
    function improveseoRunConnectionCheck(auto) {
        const button    = document.getElementById('test_server_connection');
        const statusDiv = document.getElementById('connection_status');
        const apiKey    = document.querySelector('input[name="improveseo_api_key"]').value.trim();
        const siteCode  = document.querySelector('input[name="improveseo_site_code"]').value.trim();

        if (!apiKey || !siteCode) {
            // Nothing to check. Silent when automatic: a fresh install has no credentials yet and
            // an error on first sight of the screen would read as a fault.
            if (!auto) {
                statusDiv.innerHTML = '<div class="iseo-status-error">❌ Please fill in API Key and Site Code first.</div>';
            }
            return;
        }

        if (!auto) {
            button.disabled = true;
            button.textContent = '🔄 Testing Connection...';
        }
        statusDiv.innerHTML = auto
            ? '<div class="iseo-status-loading">⏳ Checking connection…</div>'
            : '<div class="iseo-status-loading">⏳ Testing connection to ImproveSEO server...</div>';

        const restore = function () {
            if (auto) { return; }
            button.disabled = false;
            button.textContent = '🔌 Test Server Connection';
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
            } else {
                // The server's own message is shown verbatim — "Invalid API key" and "Website not
                // found or not authorized" say precisely which of the two fields is wrong, which a
                // generic "connection failed" would throw away.
                const err = (result.data && result.data.error) ? result.data.error : 'Unknown error';
                statusDiv.innerHTML = `
                    <div class="iseo-status-error">
                        ❌ <div><strong>Not connected.</strong><br>
                        ${err}<br>
                        Check that the API Key and Site Code are both copied from the same website in your ImproveSEO Dashboard.</div>
                    </div>
                `;
            }
        })
        .catch(error => {
            restore();
            statusDiv.innerHTML = `
                <div class="iseo-status-error">
                    ❌ <div><strong>Could not reach the ImproveSEO server.</strong><br>
                    ${error.message}<br>
                    Your settings are saved. Press Test Server Connection to try again.</div>
                </div>
            `;
        });
    }

    document.getElementById('test_server_connection').addEventListener('click', function () {
        improveseoRunConnectionCheck(false);
    });

    // Answer "did that work?" without making the user hunt for the button. The save itself stays
    // fire-and-forget on the PHP side (includes/connection-status.php) so a cold-starting server
    // cannot hold the settings page; this runs after the page is already interactive, so a slow
    // server costs nothing but a spinner in one corner.
    improveseoRunConnectionCheck(true);
});
</script>

<?php View::endSection('content') ?>

<?php View::make('layouts.main') ?>
