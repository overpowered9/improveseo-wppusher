<?php
/**
 * ImproveSEO Onboarding
 *
 * Handles:
 *   1. One-time activation redirect to the onboarding wizard page.
 *   2. AJAX: exchange a connect token for api_key + site_code.
 *   3. AJAX: save business setup answers and mark onboarding complete.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ─── Activation redirect ─────────────────────────────────────────────────────

add_action( 'admin_init', 'improveseo_maybe_redirect_onboarding' );

function improveseo_maybe_redirect_onboarding() {
	// Only redirect when ALL of the following are true:
	//  - The transient was set by improveseo_install()
	//  - Onboarding has not yet been completed
	//  - The current user can manage options (admin)
	//  - This is not a bulk-activation (activate-multi) request
	//  - We are not in an AJAX request or doing a WP-CLI run
	if (
		! get_transient( 'improveseo_activation_redirect' )
		|| get_option( 'improveseo_onboarding_complete' ) === '1'
		|| ! current_user_can( 'manage_options' )
		|| isset( $_GET['activate-multi'] )
		|| ( defined( 'DOING_AJAX' ) && DOING_AJAX )
		|| ( defined( 'WP_CLI' ) && WP_CLI )
	) {
		return;
	}

	delete_transient( 'improveseo_activation_redirect' );
	wp_safe_redirect( admin_url( 'admin.php?page=improveseo_onboarding' ) );
	exit;
}

// ─── AJAX: Exchange connect token ────────────────────────────────────────────

add_action( 'wp_ajax_improveseo_onboarding_exchange_token', 'improveseo_onboarding_exchange_token' );

function improveseo_onboarding_exchange_token() {
	// Security: only site administrators may connect credentials
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Insufficient permissions.' ), 403 );
	}

	// Security: verify nonce
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'improveseo_onboarding_nonce' ) ) {
		wp_send_json_error( array( 'message' => 'Security check failed.' ), 403 );
	}

	// Validate and sanitise token (64 hex chars = 32 bytes)
	$raw_token = isset( $_POST['connect_token'] ) ? sanitize_text_field( wp_unslash( $_POST['connect_token'] ) ) : '';
	if ( strlen( $raw_token ) !== 64 || ! ctype_xdigit( $raw_token ) ) {
		wp_send_json_error( array( 'message' => 'Invalid token format.' ), 400 );
	}

	// Exchange token with the admin server
	$response = wp_remote_post(
		'https://imporve-seo-admin-server-nzbm.onrender.com/api/v1/auth/exchange-connect-token',
		array(
			'body'    => wp_json_encode( array( 'connect_token' => $raw_token ) ),
			'headers' => array( 'Content-Type' => 'application/json' ),
			'timeout' => 20,
		)
	);

	if ( is_wp_error( $response ) ) {
		wp_send_json_error( array( 'message' => 'Could not reach ImproveSEO servers. Check your connection.' ), 502 );
	}

	$code = wp_remote_retrieve_response_code( $response );
	$body = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( $code !== 200 || empty( $body['success'] ) ) {
		$server_message = isset( $body['error'] ) ? $body['error'] : 'Exchange failed.';
		$server_code    = isset( $body['code'] )  ? $body['code']  : 'EXCHANGE_FAILED';
		wp_send_json_error(
			array(
				'message' => $server_message,
				'code'    => $server_code,
			),
			$code >= 400 ? $code : 502
		);
	}

	$data = $body['data'];

	// Persist credentials to wp_options
	update_option( 'improveseo_api_key',  sanitize_text_field( $data['api_key'] ) );
	update_option( 'improveseo_site_code', sanitize_text_field( $data['site_code'] ) );

	// Store the ImproveSEO account email so UI (e.g. the bulk wizard) can tell the
	// user which address completion notifications are sent to.
	if ( ! empty( $data['email'] ) ) {
		update_option( 'improveseo_account_email', sanitize_email( $data['email'] ) );
	}

	// Store trial info (used on Screen 3 and the credit banner)
	if ( ! empty( $data['trial_ends_at'] ) ) {
		update_option( 'improveseo_trial_ends_at', sanitize_text_field( $data['trial_ends_at'] ) );
	}

	// Calculate days remaining for Screen 3 display
	$days_remaining = null;
	if ( ! empty( $data['trial_ends_at'] ) ) {
		$trial_ts       = strtotime( $data['trial_ends_at'] );
		$diff_secs      = $trial_ts - time();
		$days_remaining = max( 0, (int) ceil( $diff_secs / DAY_IN_SECONDS ) );
	}

	wp_send_json_success(
		array(
			'user_name'      => isset( $data['user_name'] ) ? sanitize_text_field( $data['user_name'] ) : '',
			'trial_ends_at'  => isset( $data['trial_ends_at'] ) ? $data['trial_ends_at'] : null,
			'days_remaining' => $days_remaining,
			// null here lets the JS fall back to the trial_status from the popup postMessage,
			// which is the authoritative value set by issue-connect-token.
			'trial_status'   => isset( $data['trial_status'] ) ? $data['trial_status'] : null,
		)
	);
}

// ─── AJAX: Save business setup + mark onboarding complete ────────────────────

add_action( 'wp_ajax_improveseo_onboarding_save_business', 'improveseo_onboarding_save_business' );

function improveseo_onboarding_save_business() {
	// Security: only site administrators may update plugin settings
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Insufficient permissions.' ), 403 );
	}

	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'improveseo_onboarding_nonce' ) ) {
		wp_send_json_error( array( 'message' => 'Security check failed.' ), 403 );
	}

	$business_type = isset( $_POST['business_type'] ) ? sanitize_text_field( wp_unslash( $_POST['business_type'] ) ) : '';
	$city          = isset( $_POST['city'] )          ? sanitize_text_field( wp_unslash( $_POST['city'] ) )          : '';
	$service       = isset( $_POST['service'] )       ? sanitize_text_field( wp_unslash( $_POST['service'] ) )       : '';

	update_option( 'improveseo_business_type',    $business_type );
	update_option( 'improveseo_business_city',    $city );
	update_option( 'improveseo_business_service', $service );

	// Mark onboarding as done — activation redirect will no longer fire
	update_option( 'improveseo_onboarding_complete', '1' );

	wp_send_json_success();
}
