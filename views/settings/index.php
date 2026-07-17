<?php

use ImproveSEO\View;

?>

<?php View::startSection('breadcrumbs') ?>

<a href="<?= admin_url('admin.php?page=improveseo_dashboard') ?>">Improve SEO</a>

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
                        <img src="<?php echo WT_URL.'/assets/images/latest-images/seo-latest-logo.svg'?>" alt="ImproveSEO">
                        <span class="iseo-brand-name">ImproveSEO</span>
                        <span class="iseo-version"><?php echo esc_html( defined('IMPROVESEO_VERSION') ? IMPROVESEO_VERSION : '' ); ?></span>
                    </div>
                </div>

                <form class="improve-seo-form-global iseo-settings-form" method="post" action="options.php">
                    <?php settings_fields('improveseo_settings'); ?>

                    <!-- Form top bar: breadcrumb navigation + save button -->
                    <div class="iseo-form-topbar">
                        <nav class="iseo-breadcrumb" aria-label="Settings breadcrumb">
                            <a href="<?= admin_url('admin.php?page=improveseo_dashboard') ?>">Improve SEO</a>
                            <span class="iseo-breadcrumb-sep">›</span>
                            <span>Settings</span>
                        </nav>
                        <input type="submit" class="iseo-btn-save active setting_submit" value="<?php _e('Save Changes') ?>">
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
                                <span class="iseo-helper-text">Find your API Key in your ImproveSEO Dashboard &rarr; Settings tab</span>
                            </div>

                            <div class="iseo-field-group">
                                <label class="iseo-label" for="iseo_site_code">Site Code</label>
                                <input type="text" id="iseo_site_code" class="iseo-input" placeholder="Paste your Site Code here" name="improveseo_site_code" value="<?php echo esc_attr( get_option('improveseo_site_code') ); ?>">
                                <span class="iseo-helper-text">Find your Site Code in your ImproveSEO Dashboard &rarr; Websites tab</span>
                            </div>

                            <div class="iseo-guide-block">
                                <p class="iseo-guide-title"><strong>How to Get API Key &amp; Site Code - Step by Step</strong></p>
                                <ol class="iseo-guide-steps">
                                    <li>Visit your <a href="https://account.improveseoplugin.com/" target="_blank">ImproveSEO Dashboard</a></li>
                                    <li><strong>API Key:</strong> Go to the Settings tab, copy your API Key and paste above</li>
                                    <li><strong>Site Code:</strong> Go to the Websites tab and add the domain you want to connect</li>
                                    <li>A Site Code will be generated, copy the Site Code and paste above</li>
                                    <li>Save settings and start generating content!</li>
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
                            <input type="text" placeholder="Ex. sadfe456fds2v1xczv86s65g4s5fd4gr6e5tge5r4g54321xc86dssdfewtwerPP" name="improveseo_chatgpt_api_key" value="<?php echo get_option('improveseo_chatgpt_api_key'); ?>">
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

            <!-- Content Features Card -->
            <form class="iseo-features-form" method="post" action="options.php">
                <?php settings_fields('improveseo_feature_settings'); ?>
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
                                <input type="hidden" name="improveseo_featured_images_enabled" value="0">
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
                                    <input type="hidden" name="improveseo_featured_images_bulk" value="0">
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
                                    <input type="hidden" name="improveseo_featured_images_single" value="0">
                                    <input type="checkbox" id="iseo_featured_images_single" name="improveseo_featured_images_single" value="1" <?php checked( get_option( 'improveseo_featured_images_single', '1' ), '1' ); ?>>
                                    <span class="iseo-toggle-track"></span>
                                </label>
                            </div>

                        </div><!-- .iseo-sub-toggles -->

                    </div><!-- .iseo-toggle-group -->

                    <div class="iseo-features-save">
                        <input type="submit" class="iseo-btn-save active setting_submit" value="<?php _e('Save Changes') ?>">
                    </div>
                </div>
            </div><!-- .iseo-features-card -->
            </form><!-- .iseo-features-form -->

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

        main.addEventListener('change', function() {
            subs.forEach(function(sub) { sub.checked = main.checked; });
        });

        subs.forEach(function(sub) {
            sub.addEventListener('change', function() {
                if (sub.checked) {
                    main.checked = true;
                } else if (subs.every(function(s) { return !s.checked; })) {
                    main.checked = false;
                }
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
        var badgeText, badgeColor, statusLine;
        if (plan && plan.is_paid) {
            badgeText = esc(plan.name || 'Paid') + ' plan';
            badgeColor = '#0f7b6c';
            statusLine = 'Active subscription — full access.';
        } else if (trial && trial.expired) {
            badgeText = 'Trial ended';
            badgeColor = '#b3521a';
            statusLine = 'Your free trial has ended. Upgrade to restore full access — any credits you purchased remain usable.';
        } else if (trial && trial.active) {
            badgeText = 'Free Trial';
            badgeColor = '#0f7b6c';
            var days = (trial.days_remaining != null) ? trial.days_remaining : null;
            var ends = trial.ends_at ? new Date(trial.ends_at).toLocaleDateString() : '';
            statusLine = 'Free trial active'
                + (days != null ? ' — ' + days + ' day' + (days === 1 ? '' : 's') + ' left' : '')
                + (ends ? ' (ends ' + esc(ends) + ')' : '') + '.';
        } else {
            badgeText = plan ? esc(plan.name || 'Free') : 'Connected';
            badgeColor = '#0f7b6c';
            statusLine = 'Account connected.';
        }

        // Credit rows — prefer the plan/purchased breakdown, fall back to flat totals.
        var types = [['content', 'Content'], ['images', 'Images'], ['keywords', 'Keyword lists']];
        var rows = types.map(function(t) {
            var key = t[0], label = t[1];
            var total = '—', breakdown = '';
            if (cd && cd[key]) {
                total = cd[key].total;
                var pr = cd[key].plan_remaining, pu = cd[key].purchased_remaining;
                if (pr != null && pu != null) {
                    breakdown = '<span style="color:#6b7280;font-weight:400;"> (' + pr + ' plan + ' + pu + ' purchased)</span>';
                }
            } else if (d.credits && d.credits[key] != null) {
                total = d.credits[key];
            }
            return '<div style="display:flex;justify-content:space-between;align-items:baseline;padding:4px 0;">'
                 + '<span style="color:#374151;">' + label + '</span>'
                 + '<span style="font-weight:700;color:#111827;">' + total + breakdown + '</span>'
                 + '</div>';
        }).join('');

        var who = esc(d.email || d.user || 'Authenticated');

        return ''
          + '<div style="border:1px solid #d5e5e2;background:#f6faf9;border-radius:10px;padding:14px 16px;margin-top:10px;">'
          +   '<div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">'
          +     '<span style="color:#0f7b6c;font-size:16px;">✅</span>'
          +     '<strong style="color:#111827;">Connected</strong>'
          +     '<span style="margin-left:auto;background:' + badgeColor + ';color:#fff;font-size:11px;font-weight:700;letter-spacing:.03em;padding:3px 9px;border-radius:9999px;">' + badgeText + '</span>'
          +   '</div>'
          +   '<div style="color:#4b5563;font-size:13px;margin-bottom:12px;line-height:1.5;">' + statusLine + '</div>'
          +   '<div style="border-top:1px solid #e5eeec;padding-top:10px;">'
          +     '<div style="font-size:11px;font-weight:700;letter-spacing:.06em;color:#6b7280;text-transform:uppercase;margin-bottom:6px;">Credits</div>'
          +     rows
          +   '</div>'
          +   '<div style="border-top:1px solid #e5eeec;margin-top:10px;padding-top:8px;font-size:12px;color:#6b7280;">Account: ' + who + ' · Server: ' + esc(d.server || 'Connected') + '</div>'
          + '</div>';
    }

    // Test server connection
    document.getElementById('test_server_connection').addEventListener('click', function() {
        const button = this;
        const statusDiv = document.getElementById('connection_status');

        // Get form values (server URL is fixed)
        const apiKey = document.querySelector('input[name="improveseo_api_key"]').value;
        const siteCode = document.querySelector('input[name="improveseo_site_code"]').value;

        // Validate inputs
        if (!apiKey || !siteCode) {
            statusDiv.innerHTML = '<div class="iseo-status-error">❌ Please fill in API Key and Site Code first.</div>';
            return;
        }

        // Show loading
        button.disabled = true;
        button.textContent = '🔄 Testing Connection...';
        statusDiv.innerHTML = '<div class="iseo-status-loading">⏳ Testing connection to ImproveSEO server...</div>';

        // Test connection using WordPress AJAX
        const data = new FormData();
        data.append('action', 'test_improveseo_connection');
        data.append('api_key', apiKey);
        data.append('site_code', siteCode);
        data.append('nonce', '<?php echo wp_create_nonce("test_connection_nonce"); ?>');

        fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
            method: 'POST',
            body: data
        })
        .then(response => response.json())
        .then(result => {
            button.disabled = false;
            button.textContent = '🔌 Test Server Connection';

            if (result.success) {
                statusDiv.innerHTML = renderConnectionPanel(result.data);
            } else {
                statusDiv.innerHTML = `
                    <div class="iseo-status-error">
                        ❌ <div><strong>Connection Failed!</strong><br>
                        Error: ${result.data.error || 'Unknown error'}<br>
                        Please check your settings and try again.</div>
                    </div>
                `;
            }
        })
        .catch(error => {
            button.disabled = false;
            button.textContent = '🔌 Test Server Connection';
            statusDiv.innerHTML = `
                <div class="iseo-status-error">
                    ❌ <div><strong>Connection Test Failed!</strong><br>
                    Error: ${error.message}<br>
                    Please check your server URL and network connection.</div>
                </div>
            `;
        });
    });
});
</script>

<?php View::endSection('content') ?>

<?php View::make('layouts.main') ?>
