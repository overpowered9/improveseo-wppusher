<?php
/**
 * ImproveSEO Onboarding Wizard — full-page view.
 *
 * This page hides the normal WP admin chrome so the wizard fills the screen.
 * JavaScript (onboarding.js) drives all screen transitions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php esc_html_e( 'ImproveSEO Setup', 'improve-seo' ); ?></title>
<?php
// Load WP admin styles (so wp_enqueue'd assets work)
do_action( 'admin_print_styles' );
do_action( 'admin_print_scripts' );
?>
<style>
/* Hide the normal WP admin chrome on this page */
#adminmenuwrap,
#adminmenuback,
#wpadminbar,
#wpfooter,
#update-nag,
.update-nag {
	display: none !important;
}
html.wp-toolbar {
	padding-top: 0 !important;
}
#wpcontent,
#wpbody-content {
	padding: 0 !important;
	margin: 0 !important;
}
</style>
</head>
<body class="wp-admin improveseo-onboarding-body">

<div id="improveseo-onboarding-wrap">

	<!-- ══════════════════ Screen 1 — Welcome ══════════════════ -->
	<div class="iseo-screen" id="iseo-screen-1">
		<div class="iseo-card">
			<div class="iseo-logo">
				<img src="<?php echo esc_url( IMPROVESEO_DIR . '/assets/images/logo.png' ); ?>" alt="ImproveSEO" onerror="this.style.display='none'">
				<span class="iseo-logo-text">ImproveSEO</span>
			</div>

			<h1 class="iseo-headline">Connect &amp; Get Free Credits</h1>
			<p class="iseo-subline">
				Connect your WordPress site to your ImproveSEO account and unlock
				AI-powered SEO content generation, bulk publishing, and keyword research.
			</p>

			<ul class="iseo-benefits">
				<li><span class="iseo-check">✓</span> Free starter credits to try it out</li>
				<li><span class="iseo-check">✓</span> Auto-publish directly to WordPress</li>
				<li><span class="iseo-check">✓</span> Full keyword research toolkit</li>
				<li><span class="iseo-check">✓</span> No credit card required</li>
			</ul>

			<div id="iseo-already-connected-msg" class="iseo-alert iseo-alert-info" style="display:none;">
				<strong>Already connected!</strong> This site is already linked to ImproveSEO.
				Use the buttons below if you need to switch or reconnect your account.
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=improveseo_dashboard' ) ); ?>" class="iseo-alert-link">Go to Dashboard →</a>
			</div>

			<div id="iseo-partial-connect-msg" class="iseo-alert iseo-alert-warning" style="display:none;">
				<strong>Incomplete connection detected.</strong> Your credentials appear partially saved.
				Please connect your account again to fix this.
			</div>

			<div id="iseo-popup-blocked-msg" class="iseo-alert iseo-alert-warning" style="display:none;">
				<strong>Popup blocked!</strong> Please allow popups for this page and try again.
			</div>

			<button type="button" id="iseo-btn-create" class="iseo-btn iseo-btn-primary iseo-btn-large">
				Create Free Account &amp; Connect
			</button>
			<button type="button" id="iseo-btn-login" class="iseo-btn iseo-btn-ghost iseo-btn-large">
				I already have an account — Sign In
			</button>

			<p class="iseo-skip-line">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=improveseo_dashboard' ) ); ?>" id="iseo-skip-all">
					Skip for now — I'll configure manually
				</a>
			</p>
		</div>
	</div><!-- /screen-1 -->

	<!-- ══════════════════ Screen 2 — Connecting (tab open) ══════════════════ -->
	<div class="iseo-screen" id="iseo-screen-2" style="display:none;">
		<div class="iseo-card iseo-card-center">
			<div class="iseo-spinner"></div>
			<h2 class="iseo-headline" style="margin-top:24px;">Complete Setup in the New Tab</h2>
			<p class="iseo-subline">
				A new tab has opened for your ImproveSEO account.<br>
				<strong>Don't close this page</strong> — it will update automatically once you finish in the tab.
			</p>

			<div id="iseo-popup-closed-msg" class="iseo-alert iseo-alert-warning" style="display:none;">
				The tab was closed before completing setup.
				<button type="button" id="iseo-retry-btn" class="iseo-btn iseo-btn-ghost iseo-btn-sm">Try Again</button>
			</div>

			<!-- Always-visible small fallback link -->
			<p class="iseo-subline-sm">
				Tab not working? <a href="#" id="iseo-connect-in-window">Connect in this window instead &rarr;</a>
			</p>

			<!-- Shown after 20 s if no token arrives — prominent for Safari/Mac users -->
			<div id="iseo-connect-fallback-box" class="iseo-alert iseo-alert-info" style="display:none; margin-top:8px; text-align:left;">
				<strong>Tab taking a while?</strong> Safari and some browsers block the connection flow in a separate tab.
				<button type="button" id="iseo-connect-in-window-btn" class="iseo-btn iseo-btn-primary iseo-btn-large" style="margin-top:12px;">
					Connect in This Window Instead &rarr;
				</button>
			</div>

			<a href="#" id="iseo-cancel-connect" class="iseo-link-muted">Cancel</a>
		</div>
	</div><!-- /screen-2 -->

	<!-- ══════════════════ Screen 3 — Connected ══════════════════ -->
	<div class="iseo-screen" id="iseo-screen-3" style="display:none;">
		<div class="iseo-card iseo-card-center">
			<div class="iseo-success-icon">✓</div>
			<h2 class="iseo-headline">Account Connected!</h2>

			<div id="iseo-s3-new">
				<p class="iseo-subline">
					<strong>Free starter credits</strong> have been added to your account so you can
					try things out right away. No time limit — use them whenever you like.
				</p>
			</div>
			<div id="iseo-s3-reconnect" style="display:none;">
				<p class="iseo-subline">
					Your site has been reconnected to your account.
					Your existing credits apply.
				</p>
			</div>
			<div id="iseo-s3-none" style="display:none;">
				<p class="iseo-subline">
					<!-- Filled in by onboarding.js based on trial_reason -->
					Your account has already used its free credits.
				</p>
			</div>

			<p id="iseo-welcome-name" class="iseo-welcome-name" style="display:none;">
				Welcome, <span id="iseo-user-name"></span>!
			</p>

			<div class="iseo-credit-notice">
				<span class="iseo-credit-icon">&#9432;</span>
				<span>Each AI-generated article uses <strong>1 credit</strong>. When your free credits run out, upgrade any time — no surprise charges.</span>
			</div>

			<button type="button" id="iseo-btn-continue-setup" class="iseo-btn iseo-btn-primary iseo-btn-large">
				Continue Setup →
			</button>
		</div>
	</div><!-- /screen-3 -->

	<!-- ══════════════════ Screen 4 — Business Setup ══════════════════ -->
	<div class="iseo-screen" id="iseo-screen-4" style="display:none;">
		<div class="iseo-card">
			<h2 class="iseo-headline">Tell Us About Your Business</h2>
			<p class="iseo-subline">
				This helps ImproveSEO generate more relevant content for your site.
			</p>

			<form id="iseo-business-form" novalidate>
				<div class="iseo-field">
					<label for="iseo-business-type" class="iseo-label">Business Type</label>
					<select id="iseo-business-type" name="business_type" class="iseo-select">
						<option value="">— Select type —</option>
						<option value="local_service">Local Service Business</option>
						<option value="ecommerce">E-Commerce / Online Store</option>
						<option value="blog">Blog / Content Site</option>
						<option value="saas">SaaS / Software</option>
						<option value="agency">Agency / Freelancer</option>
						<option value="healthcare">Healthcare / Medical</option>
						<option value="real_estate">Real Estate</option>
						<option value="restaurant">Restaurant / Hospitality</option>
						<option value="education">Education / Coaching</option>
						<option value="other">Other</option>
					</select>
				</div>

				<div class="iseo-field">
					<label for="iseo-city" class="iseo-label">City / Location <span class="iseo-optional">(optional)</span></label>
					<input type="text" id="iseo-city" name="city" class="iseo-input" placeholder="e.g. New York, London, Sydney">
				</div>

				<div class="iseo-field">
					<label for="iseo-service" class="iseo-label">Main Service or Topic</label>
					<input type="text" id="iseo-service" name="service" class="iseo-input" placeholder="e.g. Plumbing, Wedding Photography, Digital Marketing">
				</div>

				<div class="iseo-readonly-info">
					<p><span class="iseo-label">Site Name:</span> <span id="iseo-ro-sitename"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span></p>
					<p><span class="iseo-label">Site URL:</span> <span id="iseo-ro-siteurl"><?php echo esc_html( home_url( '/' ) ); ?></span></p>
				</div>

				<div id="iseo-business-error" class="iseo-alert iseo-alert-error" style="display:none;"></div>

				<div class="iseo-button-row">
					<button type="submit" class="iseo-btn iseo-btn-primary">
						Save &amp; Continue →
					</button>
					<button type="button" id="iseo-skip-business" class="iseo-btn iseo-btn-ghost">
						Skip
					</button>
				</div>
			</form>
		</div>
	</div><!-- /screen-4 -->

	<!-- ══════════════════ Screen 5 — First Content ══════════════════ -->
	<div class="iseo-screen" id="iseo-screen-5" style="display:none;">
		<div class="iseo-card iseo-card-center">
			<div class="iseo-rocket-icon">🚀</div>
			<h2 class="iseo-headline">Let's Generate Your First SEO Article</h2>
			<p class="iseo-subline">
				Based on your setup, here's a suggested keyword to start with:
			</p>

			<div class="iseo-suggested-keyword">
				<span id="iseo-suggested-kw">seo services in your city</span>
			</div>

			<p class="iseo-subline-sm">
				This will be pre-filled in the article generator. You can change it at any time.
			</p>

			<div class="iseo-guide-tip">
				<span class="iseo-guide-tip-icon">&#x1F4A1;</span>
				<span>A step-by-step guide will walk you through the article wizard — look for the <strong>?</strong> tooltips at each stage.</span>
			</div>

			<a id="iseo-btn-first-content" href="#" class="iseo-btn iseo-btn-primary iseo-btn-large">
				Generate First Article →
			</a>
			<a id="iseo-btn-dashboard" href="<?php echo esc_url( admin_url( 'admin.php?page=improveseo_dashboard' ) ); ?>" class="iseo-link-muted">
				Go to Dashboard
			</a>
		</div>
	</div><!-- /screen-5 -->

</div><!-- /#improveseo-onboarding-wrap -->

<?php
do_action( 'admin_print_footer_scripts' );
wp_print_scripts( 'jquery' );
wp_print_scripts( 'improveseo-onboarding' );
?>
</body>
</html>
