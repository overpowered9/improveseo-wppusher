<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}






add_action('admin_init', 'improveseo_init_settings');





function improveseo_init_settings() {

	// Legacy settings (kept for compatibility)
	register_setting('improveseo_settings', 'improveseo_pixabay_key',     array( 'sanitize_callback' => 'sanitize_text_field' ));
	register_setting('improveseo_settings', 'improveseo_google_api_key',  array( 'sanitize_callback' => 'sanitize_text_field' ));
	register_setting('improveseo_settings', 'improveseo_chatgpt_api_key', array( 'sanitize_callback' => 'sanitize_text_field' ));
	register_setting('improveseo_settings', 'improveseo_word_ai_pass',    array( 'sanitize_callback' => 'sanitize_text_field' ));
	register_setting('improveseo_settings', 'improveseo_word_ai_email',   array( 'sanitize_callback' => 'sanitize_email' ));

	// New ImproveSEO server settings
	// register_setting('improveseo_settings', 'improveseo_server_url'); // Fixed URL, not user configurable
	//
	// sanitize_callback here doubles as the connection gate: a plain sanitize_text_field
	// would write whatever was typed straight to wp_options with no check that it actually
	// works. improveseo_sanitize_api_key/site_code below call out to the admin server and
	// refuse the save (reverting to the previously-saved value) unless the pair is a
	// confirmed live match — see improveseo_sanitize_and_verify_credentials_field().
	register_setting('improveseo_settings', 'improveseo_api_key',   array( 'sanitize_callback' => 'improveseo_sanitize_api_key' ));
	register_setting('improveseo_settings', 'improveseo_site_code', array( 'sanitize_callback' => 'improveseo_sanitize_site_code' ));

	// Business details (collected during onboarding, used for schema markup & AI content)
	register_setting('improveseo_settings', 'improveseo_business_type',    array( 'sanitize_callback' => 'sanitize_text_field' ));
	register_setting('improveseo_settings', 'improveseo_business_city',    array( 'sanitize_callback' => 'sanitize_text_field' ));
	register_setting('improveseo_settings', 'improveseo_business_service', array( 'sanitize_callback' => 'sanitize_text_field' ));

	// Featured image toggles
	// NOTE: these live in their own settings group because they are saved from a
	// SEPARATE <form> in the settings sidebar. options.php nulls out every option
	// registered in a group that is missing from the submitted form, so keeping
	// these in 'improveseo_settings' would wipe the API key / site code (and vice
	// versa) whenever one form was saved. A dedicated group keeps the two forms
	// from clobbering each other.
	register_setting('improveseo_feature_settings', 'improveseo_featured_images_enabled', array( 'sanitize_callback' => 'absint' ));
	register_setting('improveseo_feature_settings', 'improveseo_featured_images_bulk',    array( 'sanitize_callback' => 'absint' ));
	register_setting('improveseo_feature_settings', 'improveseo_featured_images_single',  array( 'sanitize_callback' => 'absint' ));

}

/**
 * Reject a Settings save unless the API Key + Site Code are a confirmed, live
 * match for THIS website, instead of writing whatever was typed straight to
 * wp_options and only finding out later via "Confirm website connection".
 *
 * Registered as the sanitize_callback for BOTH improveseo_api_key and
 * improveseo_site_code (via the two thin wrappers below) because a wrong
 * PAIRING can only be judged with both values at once — and both are present
 * in $_POST together on this save regardless of which option's callback WP
 * happens to be running right now. The static cache below means the live
 * check (a real HTTP round trip to the admin server) runs at most once per
 * save, not once per option.
 *
 * Deliberately blocking, including on a timeout/5xx from the admin server: a
 * pair that could not be verified is treated the same as a pair that was
 * rejected, rather than being saved "for now" and left for the user to
 * discover was never actually valid.
 *
 * @return string The new value if it verified, otherwise the option's previous value.
 */
function improveseo_sanitize_and_verify_credentials_field($option, $value) {
	static $checked = null;

	$new_value = sanitize_text_field($value);
	$old_value = get_option($option, '');

	// Only gate a real submission of THIS settings form.
	//
	// sanitize_callback is a filter on sanitize_option_{$option}, so it fires for EVERY
	// write to these options, including programmatic ones — notably the onboarding token
	// exchange (includes/onboarding.php), which calls update_option() directly with
	// credentials the server just issued. Those have nothing to do with $_POST, and
	// sniffing $_POST for the two field names without this guard would let an unrelated
	// request that happens to carry one of those keys trigger a bogus rejection.
	$is_settings_form_save = isset($_POST['option_page'])
		&& sanitize_text_field( wp_unslash($_POST['option_page']) ) === 'improveseo_settings';

	if (!$is_settings_form_save) {
		return $new_value;
	}

	$new_api_key   = sanitize_text_field( isset($_POST['improveseo_api_key'])   ? wp_unslash($_POST['improveseo_api_key'])   : '' );
	$new_site_code = sanitize_text_field( isset($_POST['improveseo_site_code']) ? wp_unslash($_POST['improveseo_site_code']) : '' );

	// Clearing both fields disconnects the site — always allowed, nothing to verify.
	if ($new_api_key === '' && $new_site_code === '') {
		return $new_value;
	}

	// Credentials unchanged — nothing to re-verify.
	//
	// These two fields share a form with Business Type / City / Service, so most saves after
	// setup don't touch the credentials at all. Without this, someone correcting their
	// business city pays for a live round trip to the admin server, and — worse — a cold or
	// unreachable server would reject that save and show them a credentials error for
	// something they never edited.
	//
	// Safe against the two callbacks running in sequence: this is only a skip when BOTH
	// values already match what is stored, so if either one is genuinely being changed, the
	// verification below still runs.
	if ($new_api_key === (string) get_option('improveseo_api_key', '')
		&& $new_site_code === (string) get_option('improveseo_site_code', '')) {
		return $new_value;
	}

	// Only one filled in: an incomplete pair, nothing to check yet.
	if ($new_api_key === '' || $new_site_code === '') {
		add_settings_error(
			'improveseo_settings',
			'iseo_incomplete_pair',
			__('To connect this website to your ImproveSEO account, both, the API Key and the Site Code are required.', 'improveseo'),
			'error'
		);
		return $old_value;
	}

	// Reuse a check already performed by the other option's callback in this same request.
	if (!is_array($checked) || $checked['api_key'] !== $new_api_key || $checked['site_code'] !== $new_site_code) {
		$checked = array(
			'api_key'   => $new_api_key,
			'site_code' => $new_site_code,
			'result'    => improveseo_verify_connection($new_api_key, $new_site_code, improveseo_settings_verify_timeout()),
		);
	}

	$result = $checked['result'];

	if ($result['connected']) {
		return $new_value;
	}

	if ($result['status'] === 401 || $result['status'] === 403) {
		// Covers a wrong key, a site code belonging to a different account, and — since
		// imporve-seo-admin-server's apiAuth middleware now enforces it — a site code
		// that is valid for THIS account but was issued for a different one of its
		// websites (see the plan's Part 0: x-site-domain / domain-binding enforcement).
		add_settings_error(
			'improveseo_settings',
			'iseo_not_connected',
			__('The API Key and Site Code combination for this website is not correct.', 'improveseo'),
			'error'
		);
	} else {
		add_settings_error(
			'improveseo_settings',
			'iseo_verify_unreachable',
			__('Could not verify your credentials right now — please try Save Changes again.', 'improveseo'),
			'error'
		);
	}

	return $old_value;
}

/**
 * How long the save-time verification may block, in seconds.
 *
 * This request is options.php with an admin waiting on it, so the outbound call has to
 * finish INSIDE the host's PHP execution budget. A flat 30 (the cold-start-friendly value
 * used by the heartbeat) would overrun the very common max_execution_time=30 and hand the
 * user a white screen instead of the error this gate exists to show — so leave headroom for
 * the rest of the request. 0 or unset means no limit (CLI, or a host that disabled it),
 * where the full 30 is safe.
 */
function improveseo_settings_verify_timeout() {
	$budget = (int) ini_get('max_execution_time');

	if ($budget <= 0) {
		return 30;
	}

	return max(5, min(30, $budget - 10));
}

function improveseo_sanitize_api_key($value) {
	return improveseo_sanitize_and_verify_credentials_field('improveseo_api_key', $value);
}

function improveseo_sanitize_site_code($value) {
	return improveseo_sanitize_and_verify_credentials_field('improveseo_site_code', $value);
}


