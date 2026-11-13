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

/* ======================================================================================
 * Business details for Content v3: reach and served towns (Settings → Business Details).
 * ====================================================================================*/

/**
 * @param mixed $value Raw option value.
 * @return string 'local', 'nationwide' or '' (not chosen yet).
 */
function improveseo_sanitize_business_reach( $value ) {
	$value = is_string( $value ) ? sanitize_key( $value ) : '';
	return in_array( $value, array( 'local', 'nationwide' ), true ) ? $value : '';
}

/**
 * One town per line; each sanitized, duplicates removed, at most 20, at most 120 characters.
 *
 * @param mixed $value Raw textarea value.
 * @return string
 */
function improveseo_sanitize_business_towns( $value ) {
	$lines = preg_split( '/\r\n|\r|\n/', is_string( $value ) ? $value : '' );
	$out   = array();
	$seen  = array();
	foreach ( (array) $lines as $line ) {
		$town = trim( sanitize_text_field( $line ) );
		if ( '' === $town ) {
			continue;
		}
		$town = function_exists( 'mb_substr' ) ? mb_substr( $town, 0, 120 ) : substr( $town, 0, 120 );
		$key  = function_exists( 'mb_strtolower' ) ? mb_strtolower( $town ) : strtolower( $town );
		if ( isset( $seen[ $key ] ) ) {
			continue;
		}
		$seen[ $key ] = true;
		$out[]        = $town;
		if ( count( $out ) >= 20 ) {
			break;
		}
	}
	return implode( "\n", $out );
}

/**
 * Local or nationwide. Unset: derived from the business type chosen at onboarding (online stores,
 * blogs and software are nationwide; everything else local, which most of these businesses are).
 *
 * @return string 'local' or 'nationwide'.
 */
function improveseo_business_reach() {
	$reach = improveseo_sanitize_business_reach( get_option( 'improveseo_business_reach', '' ) );
	if ( '' !== $reach ) {
		return $reach;
	}
	$type = (string) get_option( 'improveseo_business_type', '' );
	return in_array( $type, array( 'ecommerce', 'blog', 'saas' ), true ) ? 'nationwide' : 'local';
}

/**
 * The towns offered as Target town: the served-towns list, with the business city first when it
 * is not already in it.
 *
 * @return string[]
 */
function improveseo_business_towns() {
	$towns = array_filter( array_map( 'trim', explode( "\n", (string) get_option( 'improveseo_business_towns', '' ) ) ) );
	$city  = trim( (string) get_option( 'improveseo_business_city', '' ) );
	if ( '' !== $city ) {
		$lower = array_map( 'strtolower', $towns );
		if ( ! in_array( strtolower( $city ), $lower, true ) ) {
			array_unshift( $towns, $city );
		}
	}
	return array_values( $towns );
}

/**
 * "Española, NM" → "NM": the state the business city names, to place a town typed without one.
 *
 * @return string
 */
function improveseo_business_state_hint() {
	$city = (string) get_option( 'improveseo_business_city', '' );
	$parts = array_map( 'trim', explode( ',', $city ) );
	return isset( $parts[1] ) ? sanitize_text_field( $parts[1] ) : '';
}

/* ======================================================================================
 * Wizard step 1: Generate / Regenerate title through Content v3, and the town check line.
 * ====================================================================================*/

/**
 * Text field from $_POST, sanitized and trimmed.
 *
 * @param string $key Field name.
 * @param int    $max Max length.
 * @return string
 */
function improveseo_content_v3_post_text( $key, $max = 200 ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- every caller has checked the nonce.
	$value = isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';
	$value = trim( $value );
	return function_exists( 'mb_substr' ) ? mb_substr( $value, 0, $max ) : substr( $value, 0, $max );
}

/**
 * The server answered 409 v3_disabled: forget the cached status so the next page load uses v2,
 * and tell the browser to fall back now.
 *
 * @param int $status HTTP status of the failed request.
 * @return void
 */
function improveseo_content_v3_maybe_fallback( $status ) {
	if ( 409 === (int) $status ) {
		improveseo_content_v3_forget_server_status();
		wp_send_json_success( array( 'fallback' => 'v2' ) );
	}
}

add_action( 'wp_ajax_improveseo_v3_generate_title', 'improveseo_ajax_v3_generate_title' );
function improveseo_ajax_v3_generate_title() {
	check_ajax_referer( 'improveseo_ajax', 'nonce' );
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'improveseo' ) ), 403 );
	}
	if ( ! improveseo_content_v3_active() ) {
		wp_send_json_success( array( 'fallback' => 'v2' ) );
	}

	$session_key = improveseo_content_v3_session_key( isset( $_POST['session_key'] ) ? $_POST['session_key'] : '' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitized in improveseo_content_v3_session_key().
	$keyword     = improveseo_content_v3_post_text( 'seed_keyword', 200 );
	if ( '' === $session_key || '' === $keyword ) {
		wp_send_json_error( array( 'message' => __( 'Please enter a seed keyword.', 'improveseo' ) ), 400 );
	}

	$reach        = improveseo_business_reach();
	$not_about    = isset( $_POST['not_about_location'] ) && '1' === $_POST['not_about_location']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- compared to a literal.
	$target_town  = $not_about ? '' : improveseo_content_v3_post_text( 'target_town', 120 );
	if ( 'local' === $reach && ! $not_about && '' === $target_town ) {
		wp_send_json_error( array( 'message' => __( 'Choose the town this post is about, or "Not about a specific location".', 'improveseo' ) ), 400 );
	}

	$body = array(
		'session_key'        => $session_key,
		'seed_keyword'       => $keyword,
		'language'           => improveseo_content_v3_post_text( 'language', 40 ),
		'niche'              => improveseo_content_v3_post_text( 'niche', 120 ),
		'reach'              => $reach,
		'featured_service'   => trim( (string) get_option( 'improveseo_business_service', '' ) ),
		'target_town'        => $target_town,
		'not_about_location' => $not_about,
		'state_hint'         => improveseo_business_state_hint(),
		'regenerate'         => isset( $_POST['regenerate'] ) && '1' === $_POST['regenerate'], // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- compared to a literal.
	);
	$proof = (string) get_option( 'improveseo_business_proof_level', '' );
	if ( in_array( $proof, array( 'standard', 'regulated' ), true ) ) {
		$body['proof_level'] = $proof;
	}
	// Option B: the keyword data opt-in rides along only when its beta setting is on, exactly as
	// improveseo_v3_step_fields() documents.
	if ( function_exists( 'improveseo_v3_step_fields' ) ) {
		$body = array_merge( $body, improveseo_v3_step_fields( 'title' ) );
	}
	$body = array_filter( $body, function ( $v ) {
		return '' !== $v && null !== $v;
	} );

	$error  = '';
	$status = 0;
	$data   = improveseo_content_v3_request( 'POST', '/api/v1/content-v3/wizard/title', $body, $error, $status, 240 );
	if ( ! is_array( $data ) ) {
		improveseo_content_v3_maybe_fallback( $status );
		wp_send_json_error( array( 'message' => $error ), $status ? $status : 502 );
	}
	$d = isset( $data['data'] ) && is_array( $data['data'] ) ? $data['data'] : array();
	wp_send_json_success( array(
		'title'      => isset( $d['title'] ) ? sanitize_text_field( (string) $d['title'] ) : '',
		'failed'     => ! empty( $d['failed'] ),
		'message'    => isset( $d['message'] ) ? sanitize_text_field( (string) $d['message'] ) : '',
		'town_check' => isset( $d['town_check'] ) && is_array( $d['town_check'] ) ? array(
			'keyword_town' => sanitize_text_field( (string) ( isset( $d['town_check']['keyword_town'] ) ? $d['town_check']['keyword_town'] : '' ) ),
			'target_town'  => sanitize_text_field( (string) ( isset( $d['town_check']['target_town'] ) ? $d['town_check']['target_town'] : '' ) ),
		) : null,
	) );
}

add_action( 'wp_ajax_improveseo_v3_town_check', 'improveseo_ajax_v3_town_check' );
function improveseo_ajax_v3_town_check() {
	check_ajax_referer( 'improveseo_ajax', 'nonce' );
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'improveseo' ) ), 403 );
	}
	if ( ! improveseo_content_v3_active() ) {
		wp_send_json_success( array( 'town_check' => null ) );
	}
	$keyword = improveseo_content_v3_post_text( 'seed_keyword', 200 );
	$town    = improveseo_content_v3_post_text( 'target_town', 120 );
	if ( '' === $keyword || '' === $town ) {
		wp_send_json_success( array( 'town_check' => null ) );
	}
	$error  = '';
	$status = 0;
	$data   = improveseo_content_v3_request( 'POST', '/api/v1/content-v3/wizard/town-check', array( 'seed_keyword' => $keyword, 'target_town' => $town ), $error, $status, 15 );
	$check  = is_array( $data ) && isset( $data['data']['town_check'] ) && is_array( $data['data']['town_check'] ) ? $data['data']['town_check'] : null;
	wp_send_json_success( array(
		'town_check' => $check ? array(
			'keyword_town' => sanitize_text_field( (string) ( isset( $check['keyword_town'] ) ? $check['keyword_town'] : '' ) ),
			'target_town'  => sanitize_text_field( (string) ( isset( $check['target_town'] ) ? $check['target_town'] : '' ) ),
		) : null,
	) );
}

/**
 * The v3 wizard script, on the post wizard screen only and only while v3 is active, so a site with
 * v3 off loads exactly what it loaded before.
 */
add_action( 'admin_enqueue_scripts', 'improveseo_content_v3_enqueue', 20 );
function improveseo_content_v3_enqueue() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- reading the current admin screen, not acting on input.
	if ( ! isset( $_GET['page'] ) || 'improveseo_posting' !== sanitize_key( wp_unslash( $_GET['page'] ) ) ) {
		return;
	}
	if ( ! improveseo_content_v3_active() ) {
		return;
	}
	$ver = function_exists( 'improveseo_asset_ver' ) ? improveseo_asset_ver( 'assets/js/content-v3-wizard.js' ) : null;
	wp_enqueue_script( 'improveseo-content-v3-wizard', IMPROVESEO_DIR . '/assets/js/content-v3-wizard.js', array( 'jquery' ), $ver, true );
	wp_localize_script( 'improveseo-content-v3-wizard', 'iseoContentV3', array(
		'active'  => '1',
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'improveseo_ajax' ),
		'reach'   => improveseo_business_reach(),
		'i18n'    => array(
			'chooseTown'     => __( 'Choose the town this post is about, or "Not about a specific location".', 'improveseo' ),
			'newTownEmpty'   => __( 'Type the town, for example "Española, NM".', 'improveseo' ),
			/* translators: 1: town named in the keyword, 2: the chosen Target town. */
			'townCheck'      => __( 'Your keyword mentions %1$s, but this post is set to %2$s. Please check the town.', 'improveseo' ),
			'generating'     => __( 'Generating Title...', 'improveseo' ),
			'generatingMsg'  => __( 'Looking up what people search for, then writing your title.', 'improveseo' ),
			'failedTitle'    => __( 'Title not generated', 'improveseo' ),
			'keywordNeeded'  => __( 'Please enter a seed keyword before generating a title.', 'improveseo' ),
			'keywordHeading' => __( 'Seed Keyword Required', 'improveseo' ),
			'townHeading'    => __( 'Target town required', 'improveseo' ),
			'genericError'   => __( 'We couldn\'t generate a title. Please try again.', 'improveseo' ),
		),
	) );
}
