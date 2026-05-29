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
                        <span class="iseo-version">2.0.11</span>
                        <span class="iseo-badge-pro">Pro</span>
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
                                <input type="text" id="iseo_api_key" class="iseo-input" placeholder="Your API Key from ImproveSEO Dashboard" name="improveseo_api_key" value="<?php echo esc_attr( get_option('improveseo_api_key') ); ?>">
                                <span class="iseo-helper-text">Get this from your ImproveSEO Dashboard overview tab</span>
                            </div>

                            <div class="iseo-field-group">
                                <label class="iseo-label" for="iseo_site_code">Site Code</label>
                                <input type="text" id="iseo_site_code" class="iseo-input" placeholder="Your Site Code from ImproveSEO Dashboard" name="improveseo_site_code" value="<?php echo esc_attr( get_option('improveseo_site_code') ); ?>">
                                <span class="iseo-helper-text">Get this from your ImproveSEO Dashboard websites tab</span>
                            </div>

                            <div class="iseo-guide-block">
                                <p class="iseo-guide-title"><strong>How to get credentials:</strong></p>
                                <ol class="iseo-guide-steps">
                                    <li>Visit your <a href="https://account.improveseoplugin.com/" target="_blank">ImproveSEO Dashboard</a></li>
                                    <li>Copy your API Key from the Overview tab</li>
                                    <li>Go to Websites tab and add this domain</li>
                                    <li>Copy the Site Code and paste above</li>
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
                                <p class="iseo-card-subtitle">These details were collected during onboarding and are used for schema markup and AI content personalisation. Update them here at any time.</p>
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
                <?php settings_fields('improveseo_settings'); ?>
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
                                <span class="iseo-toggle-label">Enable Featured Images</span>
                                <span class="iseo-toggle-desc">Automatically generate &amp; attach a featured image to AI-created posts.</span>
                            </div>
                            <label class="iseo-toggle-switch" aria-label="Enable Featured Images">
                                <input type="hidden" name="improveseo_featured_images_enabled" value="0">
                                <input type="checkbox" id="iseo_featured_images_enabled" name="improveseo_featured_images_enabled" value="1" <?php checked( get_option( 'improveseo_featured_images_enabled', '0' ), '1' ); ?>>
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
                                    <input type="checkbox" id="iseo_featured_images_bulk" name="improveseo_featured_images_bulk" value="1" <?php checked( get_option( 'improveseo_featured_images_bulk', '0' ), '1' ); ?>>
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
                                    <input type="checkbox" id="iseo_featured_images_single" name="improveseo_featured_images_single" value="1" <?php checked( get_option( 'improveseo_featured_images_single', '0' ), '1' ); ?>>
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
                statusDiv.innerHTML = `
                    <div class="iseo-status-success">
                        ✅ <div><strong>Connection Successful!</strong><br>
                        📊 Credits: ${result.data.credits ?
                            `Images: ${result.data.credits.images}, Content: ${result.data.credits.content}, Keywords: ${result.data.credits.keywords}` :
                            'Available'}<br>
                        🔑 User: ${result.data.user || 'Authenticated'}<br>
                        🌐 Server: ${result.data.server || 'Connected'}</div>
                    </div>
                `;
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
