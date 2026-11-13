<?php
/**
 * Content v3 — the plugin side.
 *
 * TWO SWITCHES. Content v3 runs only when the site's setting "Use Content v3" is on (Settings →
 * Content Features, option improveseo_content_v3, default off) AND the ImproveSEO server answers
 * GET /api/v1/content-v3/status with enabled: true (the server's CONTENT_V3_ENABLED). Anything
 * else — the setting off, the server off, the server unreachable — and every generation uses the
 * v2 pipeline exactly as before. Turning the server flag off is therefore the rollback.
 *
 * The server's answer is cached for five minutes (one minute after a failure) so a wizard load
 * does not wait on it; saving the setting clears the cache.
 *
 * Every request carries the site's API key and site code; no secret of any provider is ever on
 * this side. Error messages come from the server already written for people (server
 * services/content-v3/user-errors.ts) and are shown as-is.
 *
 * @package ImproveSEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const IMPROVESEO_CONTENT_V3_STATUS_TRANSIENT = 'improveseo_content_v3_status';

/**
 * Is "Use Content v3" switched on for this site?
 *
 * @return bool
 */
function improveseo_content_v3_site_enabled() {
	return '1' === (string) get_option( 'improveseo_content_v3', '0' );
}

/**
 * Forget the cached server answer (called when the setting is saved).
 *
 * @return void
 */
function improveseo_content_v3_forget_server_status() {
	delete_transient( IMPROVESEO_CONTENT_V3_STATUS_TRANSIENT );
}

/**
 * One request to the ImproveSEO server's Content v3 API.
 *
 * @param string     $method  GET or POST.
 * @param string     $path    Path under the server, e.g. /api/v1/content-v3/status.
 * @param array|null $body    JSON body for POST.
 * @param string     $error   Set to a user-facing sentence on failure.
 * @param int        $status  Set to the HTTP status (0 when the server was not reached).
 * @param int        $timeout Seconds.
 * @return array|null Decoded body on success (HTTP 200 and success: true), else null.
 */
function improveseo_content_v3_request( $method, $path, $body = null, &$error = null, &$status = null, $timeout = 30 ) {
	$error  = '';
	$status = 0;

	$api_key   = get_option( 'improveseo_api_key' );
	$site_code = get_option( 'improveseo_site_code' );
	if ( empty( $api_key ) || empty( $site_code ) ) {
		$error = __( 'Your ImproveSEO API Key or Site Code is not saved on this site.', 'improveseo' );
		return null;
	}

	$base = function_exists( 'improveseo_keyword_data_server_url' )
		? improveseo_keyword_data_server_url()
		: ( defined( 'IMPROVESEO_CONNECTION_SERVER' ) ? IMPROVESEO_CONNECTION_SERVER : 'https://imporve-seo-admin-server-nzbm.onrender.com' );

	$headers = array(
		'Accept'      => 'application/json',
		'X-API-Key'   => $api_key,
		'X-Site-Code' => $site_code,
	);
	if ( function_exists( 'improveseo_connection_domain_header' ) ) {
		$headers['X-Site-Domain'] = improveseo_connection_domain_header();
	}

	$args = array(
		'headers' => $headers,
		'timeout' => (int) $timeout,
	);
	if ( 'POST' === $method ) {
		$args['headers']['Content-Type'] = 'application/json';
		$args['body']                    = wp_json_encode( is_array( $body ) ? $body : array() );
		$response                        = wp_remote_post( $base . $path, $args );
	} else {
		$response = wp_remote_get( $base . $path, $args );
	}

	if ( is_wp_error( $response ) ) {
		error_log( 'improveseo_content_v3_request: HTTP error for ' . $path . ' — ' . $response->get_error_message() );
		$error = __( 'Could not reach the ImproveSEO server. Check this site\'s internet connection and try again.', 'improveseo' );
		return null;
	}

	$status = (int) wp_remote_retrieve_response_code( $response );
	$data   = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( 200 !== $status || ! is_array( $data ) || empty( $data['success'] ) ) {
		$code = is_array( $data ) && isset( $data['error'] ) && is_string( $data['error'] ) ? $data['error'] : '';
		error_log( 'improveseo_content_v3_request: failure path=' . $path . ' | status=' . $status . ' | code=' . ( $code ? $code : 'unknown' ) );
		if ( is_array( $data ) && ! empty( $data['message'] ) && is_string( $data['message'] ) ) {
			$error = $data['message'];
		} elseif ( 401 === $status || 403 === $status ) {
			$error = __( 'ImproveSEO rejected this site\'s API Key or Site Code. Check them in Settings.', 'improveseo' );
		} elseif ( 402 === $status ) {
			$error = __( 'You don\'t have enough credits for this post size. Buy more credits or choose a smaller size.', 'improveseo' );
		} else {
			$error = __( 'Something went wrong on the ImproveSEO server. Please try again.', 'improveseo' );
		}
		return null;
	}

	return $data;
}

/**
 * The server's Content v3 status, cached.
 *
 * @param bool $force Skip the cache.
 * @return array{enabled: bool, keyword_data: bool, reached: bool}
 */
function improveseo_content_v3_server_status( $force = false ) {
	if ( ! $force ) {
		$cached = get_transient( IMPROVESEO_CONTENT_V3_STATUS_TRANSIENT );
		if ( is_array( $cached ) && isset( $cached['enabled'] ) ) {
			return $cached;
		}
	}

	$error  = '';
	$status = 0;
	$data   = improveseo_content_v3_request( 'GET', '/api/v1/content-v3/status', null, $error, $status, 8 );

	if ( is_array( $data ) && isset( $data['data'] ) && is_array( $data['data'] ) ) {
		$result = array(
			'enabled'      => ! empty( $data['data']['enabled'] ),
			'keyword_data' => ! empty( $data['data']['keyword_data'] ),
			'reached'      => true,
		);
		set_transient( IMPROVESEO_CONTENT_V3_STATUS_TRANSIENT, $result, 5 * MINUTE_IN_SECONDS );
		return $result;
	}

	// Unreachable, an old server without the route (404), or a rejected key: v2 for now, ask again soon.
	$result = array(
		'enabled'      => false,
		'keyword_data' => false,
		'reached'      => false,
	);
	set_transient( IMPROVESEO_CONTENT_V3_STATUS_TRANSIENT, $result, MINUTE_IN_SECONDS );
	return $result;
}

/**
 * Does the server have Content v3 switched on?
 *
 * @return bool
 */
function improveseo_content_v3_server_enabled() {
	$s = improveseo_content_v3_server_status();
	return ! empty( $s['enabled'] );
}

/**
 * Should this request use Content v3? The setting first, so a site with it off never calls the
 * server for the status at all.
 *
 * @return bool
 */
function improveseo_content_v3_active() {
	return improveseo_content_v3_site_enabled() && improveseo_content_v3_server_enabled();
}

/**
 * The per-wizard session key the plugin already mints (#iseo_target_key, iseo_tk_<32 hex>), or ''.
 *
 * @param mixed $raw Raw request value.
 * @return string
 */
function improveseo_content_v3_session_key( $raw ) {
	$key = is_string( $raw ) ? sanitize_text_field( wp_unslash( $raw ) ) : '';
	return preg_match( '/^[A-Za-z0-9_-]{8,80}$/', $key ) ? $key : '';
}

/**
 * Wizard Leave (W10): delete everything the server stored for this wizard session. Always
 * answers success so leaving the page is never blocked; `cleared` says whether it worked.
 */
add_action( 'wp_ajax_improveseo_content_v3_clear_session', 'improveseo_ajax_content_v3_clear_session' );
function improveseo_ajax_content_v3_clear_session() {
	check_ajax_referer( 'improveseo_ajax', 'nonce' );
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'improveseo' ) ), 403 );
	}
	$key = improveseo_content_v3_session_key( isset( $_POST['session_key'] ) ? $_POST['session_key'] : '' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitized in improveseo_content_v3_session_key().
	if ( '' === $key ) {
		wp_send_json_success( array( 'cleared' => false ) );
	}
	$error  = '';
	$status = 0;
	$data   = improveseo_content_v3_request( 'POST', '/api/v1/content-v3/session/clear', array( 'session_key' => $key ), $error, $status, 10 );
	wp_send_json_success( array( 'cleared' => is_array( $data ) && ! empty( $data['cleared'] ) ) );
}
