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
function improveseo_content_v3_request( $method, $path, $body = null, &$error = null, &$status = null, $timeout = 30, &$code = null ) {
	$error  = '';
	$status = 0;
	$code   = '';

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
		$code = is_array( $data ) && isset( $data['error'] ) && is_string( $data['error'] ) ? sanitize_key( $data['error'] ) : '';
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

/**
 * The niche to send: the Business details niche key when set; otherwise the wizard's niche select
 * key (v2 list, which the server maps), or the "Other" text the user typed.
 *
 * @return string
 */
function improveseo_content_v3_niche_for_request() {
	$business = (string) get_option( 'improveseo_business_niche', '' );
	if ( '' !== $business ) {
		return $business;
	}
	$wizard = improveseo_content_v3_post_text( 'niche', 120 );
	if ( 'other' === $wizard ) {
		$other = improveseo_content_v3_post_text( 'niche_other', 120 );
		return '' !== $other ? $other : 'other';
	}
	return $wizard;
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
		'niche'              => improveseo_content_v3_niche_for_request(),
		'reach'              => $reach,
		'featured_service'   => trim( (string) get_option( 'improveseo_business_service', '' ) ),
		'target_town'        => $target_town,
		'not_about_location' => $not_about,
		'state_hint'         => improveseo_business_state_hint(),
		'regenerate'         => isset( $_POST['regenerate'] ) && '1' === $_POST['regenerate'], // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- compared to a literal.
		// T5: the business-level proof level (decided 3 Nov). Standard unless the user chose Regulated.
		'proof_level'        => improveseo_business_proof_level(),
	);
	$ideal_client = trim( (string) get_option( 'improveseo_business_ideal_client', '' ) );
	if ( '' !== $ideal_client ) {
		$body['ideal_client'] = $ideal_client;
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
		// G5: no Witty tone for a regulated niche (the business set Regulated, or its niche usually is).
		'regulatedNiche' => ( 'regulated' === improveseo_business_proof_level() || ( ( $iseo_row = improveseo_business_niche_row() ) && 'regulated' === $iseo_row['proof_level'] ) ) ? '1' : '0',
		'pov'            => improveseo_business_pov(),
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
			'planning'          => __( 'Planning your article...', 'improveseo' ),
			'planningMsg'       => __( 'Researching the topic and planning the article. This can take up to a minute.', 'improveseo' ),
			'planFailed'        => __( 'We couldn\'t plan the article this time. Please try again.', 'improveseo' ),
			'planFailedHeading' => __( 'Planning failed', 'improveseo' ),
			'titleNotesHeading' => __( 'About your title', 'improveseo' ),
			'ownerHeading'      => __( 'A quick question for you (optional, makes the post more specific)', 'improveseo' ),
			'ownerPlaceholder'  => __( 'Your answer, in a few words', 'improveseo' ),
			/* translators: %1$s: what the topic involves, e.g. "medical treatment". */
			'regulatedTopic'    => __( 'This topic involves %1$s, so this article uses stricter sourcing. Keep this, or turn it off for this post.', 'improveseo' ),
			/* translators: %1$s: the reason the planner gave. */
			'regulatedPlanner'  => __( 'This topic may be regulated (%1$s), so this article uses stricter sourcing. Keep this, or turn it off for this post.', 'improveseo' ),
			'regulatedGeneric'  => __( 'health, legal, financial or insurance', 'improveseo' ),
			'regulatedBusiness' => __( 'Your business is set as Regulated, so this article uses stricter sourcing. You can turn it off for this post.', 'improveseo' ),
			'planFallback'      => __( 'We could not plan this article in detail, so it uses a simple plan. You can still generate it.', 'improveseo' ),
			'researchSkipped'   => __( 'Topic research was not available this time; the article will not quote figures.', 'improveseo' ),
			'reviewTitle'       => __( 'Please check this article', 'improveseo' ),
			'reviewLead'        => __( 'We wrote this article twice, but it still misses some of our quality checks:', 'improveseo' ),
			'useAnyway'         => __( 'Use anyway', 'improveseo' ),
			'regenerate'        => __( 'Regenerate', 'improveseo' ),
		),
	) );
}

/* ======================================================================================
 * Approve title (W3): title check → research → planner on the server; step 2 gets the plan.
 * ====================================================================================*/

add_action( 'wp_ajax_improveseo_v3_approve_title', 'improveseo_ajax_v3_approve_title' );
function improveseo_ajax_v3_approve_title() {
	check_ajax_referer( 'improveseo_ajax', 'nonce' );
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'improveseo' ) ), 403 );
	}
	if ( ! improveseo_content_v3_active() ) {
		wp_send_json_success( array( 'fallback' => 'v2' ) );
	}

	$session_key = improveseo_content_v3_session_key( isset( $_POST['session_key'] ) ? $_POST['session_key'] : '' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitized in improveseo_content_v3_session_key().
	$keyword     = improveseo_content_v3_post_text( 'seed_keyword', 200 );
	$title       = improveseo_content_v3_post_text( 'title', 300 );
	if ( '' === $session_key || '' === $keyword ) {
		wp_send_json_error( array( 'message' => __( 'Please enter a seed keyword.', 'improveseo' ) ), 400 );
	}
	if ( '' === $title ) {
		wp_send_json_error( array( 'message' => __( 'Please write or generate a title first.', 'improveseo' ) ), 400 );
	}

	$reach       = improveseo_business_reach();
	$not_about   = isset( $_POST['not_about_location'] ) && '1' === $_POST['not_about_location']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- compared to a literal.
	$target_town = $not_about ? '' : improveseo_content_v3_post_text( 'target_town', 120 );

	$body = array(
		'session_key'        => $session_key,
		'title'              => $title,
		'seed_keyword'       => $keyword,
		'language'           => improveseo_content_v3_post_text( 'language', 40 ),
		'niche'              => improveseo_content_v3_niche_for_request(),
		'niche_profile'      => (string) get_option( 'improveseo_business_profile', '' ),
		'reach'              => $reach,
		'featured_service'   => trim( (string) get_option( 'improveseo_business_service', '' ) ),
		'target_town'        => $target_town,
		'not_about_location' => $not_about,
		'state_hint'         => improveseo_business_state_hint(),
		'proof_level'        => improveseo_business_proof_level(),
	);
	$description = trim( (string) get_option( 'improveseo_business_description', '' ) );
	if ( '' !== $description ) {
		$body['business_description'] = $description;
	}
	$ideal_client = trim( (string) get_option( 'improveseo_business_ideal_client', '' ) );
	if ( '' !== $ideal_client ) {
		$body['ideal_client'] = $ideal_client;
	}
	if ( function_exists( 'improveseo_v3_step_fields' ) ) {
		$body = array_merge( $body, improveseo_v3_step_fields( 'planner' ) );
	}
	$body = array_filter( $body, function ( $v ) {
		return '' !== $v && null !== $v;
	} );

	$error  = '';
	$status = 0;
	// Research (up to 3 Perplexity attempts) and the Planner (up to 3) can take a while.
	$data = improveseo_content_v3_request( 'POST', '/api/v1/content-v3/wizard/approve', $body, $error, $status, 420 );
	if ( ! is_array( $data ) ) {
		improveseo_content_v3_maybe_fallback( $status );
		wp_send_json_error( array( 'message' => $error ), $status ? $status : 502 );
	}
	$d    = isset( $data['data'] ) && is_array( $data['data'] ) ? $data['data'] : array();
	$plan = isset( $d['plan'] ) && is_array( $d['plan'] ) ? $d['plan'] : array();
	$reg  = isset( $d['regulated'] ) && is_array( $d['regulated'] ) ? $d['regulated'] : array();
	$res  = isset( $d['research'] ) && is_array( $d['research'] ) ? $d['research'] : array();

	$types = array( 'cost', 'decision', 'question', 'topic' );
	$sizes = array( 'small', 'medium', 'large' );
	$owner = array();
	foreach ( (array) ( isset( $plan['owner_questions'] ) ? $plan['owner_questions'] : array() ) as $q ) {
		if ( is_string( $q ) && '' !== trim( $q ) && count( $owner ) < 2 ) {
			$owner[] = sanitize_text_field( $q );
		}
	}
	$notes = array();
	foreach ( (array) ( isset( $d['title_notes'] ) ? $d['title_notes'] : array() ) as $n ) {
		if ( is_string( $n ) ) {
			$notes[] = sanitize_text_field( $n );
		}
	}
	$by = isset( $reg['by'] ) && in_array( $reg['by'], array( 'list', 'planner', 'both', 'business' ), true ) ? $reg['by'] : '';

	wp_send_json_success( array(
		'title'       => isset( $d['title'] ) ? sanitize_text_field( (string) $d['title'] ) : $title,
		'title_notes' => $notes,
		'plan'        => array(
			'article_type'    => isset( $plan['article_type'] ) && in_array( $plan['article_type'], $types, true ) ? $plan['article_type'] : 'topic',
			'post_size'       => isset( $plan['post_size'] ) && in_array( $plan['post_size'], $sizes, true ) ? $plan['post_size'] : 'small',
			'minimum_size'    => isset( $plan['minimum_size'] ) && in_array( $plan['minimum_size'], $sizes, true ) ? $plan['minimum_size'] : 'small',
			'size_reason'     => isset( $plan['size_reason'] ) ? sanitize_text_field( (string) $plan['size_reason'] ) : '',
			'owner_questions' => $owner,
		),
		'regulated'   => array(
			'suggested' => ! empty( $reg['suggested'] ),
			'by'        => $by,
			'phrase'    => isset( $reg['phrase'] ) ? sanitize_text_field( (string) $reg['phrase'] ) : '',
			'reason'    => isset( $reg['reason'] ) ? sanitize_text_field( (string) $reg['reason'] ) : '',
		),
		'research'    => array(
			'status' => isset( $res['status'] ) ? sanitize_key( (string) $res['status'] ) : '',
			'facts'  => isset( $res['facts'] ) ? absint( $res['facts'] ) : 0,
		),
		'fallback'    => ! empty( $d['fallback'] ),
	) );
}

/* ======================================================================================
 * Business details for Content v3, part 2: niche, profile, proof level, point of view and the
 * details the Writer uses (N1, G1, G3, W9). The niche list comes from the server's settings
 * (GET /api/v1/content-v3/niches) so "adding a niche is one row" there; cached for 12 hours.
 * ====================================================================================*/

const IMPROVESEO_V3_PROFILES = array( 'trades', 'health', 'money_legal', 'appointment', 'expertise', 'food_venues', 'shops', 'content_only' );

function improveseo_sanitize_business_niche( $value ) {
	$value = is_string( $value ) ? sanitize_key( $value ) : '';
	return substr( $value, 0, 40 );
}

function improveseo_sanitize_business_profile( $value ) {
	$value = is_string( $value ) ? sanitize_key( $value ) : '';
	return in_array( $value, IMPROVESEO_V3_PROFILES, true ) ? $value : '';
}

/**
 * Proof level: only what the user chose. Switching to Regulated queues the one-time notice (G3).
 *
 * @param mixed $value Raw value.
 * @return string 'standard', 'regulated' or ''.
 */
function improveseo_sanitize_business_proof_level( $value ) {
	$value = is_string( $value ) ? sanitize_key( $value ) : '';
	$value = in_array( $value, array( 'standard', 'regulated' ), true ) ? $value : '';
	if ( 'regulated' === $value && 'regulated' !== get_option( 'improveseo_business_proof_level', '' ) && ! get_option( 'improveseo_regulated_notice_seen', false ) ) {
		set_transient( 'improveseo_regulated_notice', 1, HOUR_IN_SECONDS );
	}
	return $value;
}

function improveseo_sanitize_business_pov( $value ) {
	$value = is_string( $value ) ? sanitize_key( $value ) : '';
	return in_array( $value, array( 'we', 'i', 'business_name' ), true ) ? $value : '';
}

function improveseo_sanitize_business_short_text( $value ) {
	$value = is_string( $value ) ? trim( sanitize_text_field( $value ) ) : '';
	return function_exists( 'mb_substr' ) ? mb_substr( $value, 0, 120 ) : substr( $value, 0, 120 );
}

function improveseo_sanitize_business_long_text( $value ) {
	$value = is_string( $value ) ? trim( sanitize_textarea_field( $value ) ) : '';
	return function_exists( 'mb_substr' ) ? mb_substr( $value, 0, 600 ) : substr( $value, 0, 600 );
}

/**
 * The niche list from the server (keys, labels, profiles, default proof levels, synonyms).
 *
 * @param bool $force Skip the cache.
 * @return array{niches: array, profiles: array}|null Null when the server could not be reached.
 */
function improveseo_content_v3_niches( $force = false ) {
	$cached = $force ? false : get_transient( 'improveseo_content_v3_niches' );
	if ( is_array( $cached ) && ! empty( $cached['niches'] ) ) {
		return $cached;
	}
	$error  = '';
	$status = 0;
	$data   = improveseo_content_v3_request( 'GET', '/api/v1/content-v3/niches', null, $error, $status, 10 );
	if ( ! is_array( $data ) || empty( $data['data']['niches'] ) || ! is_array( $data['data']['niches'] ) ) {
		return null;
	}
	$niches = array();
	foreach ( $data['data']['niches'] as $n ) {
		if ( ! is_array( $n ) || empty( $n['key'] ) || empty( $n['label'] ) ) {
			continue;
		}
		$niches[] = array(
			'key'            => sanitize_key( $n['key'] ),
			'label'          => sanitize_text_field( $n['label'] ),
			'profile'        => isset( $n['profile'] ) ? sanitize_key( $n['profile'] ) : '',
			'profile_name'   => isset( $n['profile_name'] ) ? sanitize_text_field( $n['profile_name'] ) : '',
			'proof_level'    => isset( $n['proof_level'] ) && 'regulated' === $n['proof_level'] ? 'regulated' : 'standard',
			'proof_open'     => ! empty( $n['proof_open'] ),
			'choose_profile' => ! empty( $n['choose_profile'] ),
			'synonyms'       => array_values( array_map( 'sanitize_text_field', array_filter( (array) ( isset( $n['synonyms'] ) ? $n['synonyms'] : array() ), 'is_string' ) ) ),
		);
	}
	$profiles = array();
	foreach ( (array) ( isset( $data['data']['profiles'] ) ? $data['data']['profiles'] : array() ) as $p ) {
		if ( is_array( $p ) && ! empty( $p['key'] ) && in_array( $p['key'], IMPROVESEO_V3_PROFILES, true ) ) {
			$profiles[] = array( 'key' => $p['key'], 'name' => sanitize_text_field( (string) ( isset( $p['name'] ) ? $p['name'] : $p['key'] ) ) );
		}
	}
	$result = array( 'niches' => $niches, 'profiles' => $profiles );
	set_transient( 'improveseo_content_v3_niches', $result, 12 * HOUR_IN_SECONDS );
	return $result;
}

/**
 * The niche row the business picked, or null.
 *
 * @return array|null
 */
function improveseo_business_niche_row() {
	$key = (string) get_option( 'improveseo_business_niche', '' );
	if ( '' === $key ) {
		return null;
	}
	$list = improveseo_content_v3_niches();
	foreach ( (array) ( $list ? $list['niches'] : array() ) as $n ) {
		if ( $n['key'] === $key ) {
			return $n;
		}
	}
	return null;
}

/**
 * The proof level to send. Never Regulated unless the user chose it; unset counts as Standard so the
 * post-level check (topics list + Planner) runs (G1, G2).
 *
 * @return string
 */
function improveseo_business_proof_level() {
	return 'regulated' === get_option( 'improveseo_business_proof_level', '' ) ? 'regulated' : 'standard';
}

/**
 * Point of view from Business details: 'we', 'i' or 'business_name'. Unset → 'we' (a business site).
 *
 * @return string
 */
function improveseo_business_pov() {
	$pov = improveseo_sanitize_business_pov( get_option( 'improveseo_business_pov', '' ) );
	return '' !== $pov ? $pov : 'we';
}

/**
 * W9 migration at read time: the v2 wizard's values ("Auto (AI decides)", "Speaking to the reader")
 * map to the Business details value; the two v2 voices map to their v3 option.
 *
 * @param string $value A stored point_of_view value (v2 or v3).
 * @return string 'we', 'i' or 'business_name'.
 */
function improveseo_v3_map_point_of_view( $value ) {
	$value = (string) $value;
	if ( in_array( $value, array( 'we', 'i', 'business_name' ), true ) ) {
		return $value;
	}
	if ( 0 === strpos( $value, 'First person plural' ) ) {
		return 'we';
	}
	if ( 0 === strpos( $value, 'First person singular' ) ) {
		return 'i';
	}
	return improveseo_business_pov(); // "none", "Whatever is suited to keyword", "Second Person …"
}

/**
 * G3: the one-time notice after a business is set to Regulated.
 */
add_action( 'admin_notices', 'improveseo_regulated_notice' );
function improveseo_regulated_notice() {
	if ( ! current_user_can( 'manage_options' ) || ! get_transient( 'improveseo_regulated_notice' ) ) {
		return;
	}
	delete_transient( 'improveseo_regulated_notice' );
	update_option( 'improveseo_regulated_notice_seen', 1, false );
	echo '<div class="notice notice-info is-dismissible"><p><strong>' . esc_html__( 'Your business is set as Regulated.', 'improveseo' ) . '</strong> ' .
		esc_html__( 'Every new Content v3 post uses stricter sourcing: figures need a cited source, no outcomes are promised, and posts link to at least 3 sources. Bulk posts are saved as drafts so you can review them before publishing. You can turn Regulated off for a single post in the post wizard.', 'improveseo' ) .
		'</p></div>';
}

/* ======================================================================================
 * Generate AI Post (W4): the paid v3 article from the wizard session's plan.
 * ====================================================================================*/

/**
 * The v3 article for the single wizard. Called from getaaldata() (single_AI_post_function.php)
 * while v3 is active. Returns the same keys createAIpost2() returns, plus 'v3'; error texts use the
 * same sentinels the wizard's JS already handles (402 credit notice, ISEO_NOT_CONNECTED::).
 *
 * @param array  $arr   The wizard form, parsed.
 * @param string $title The approved title.
 * @return array|null Null when the server answered that v3 is off: the caller runs v2 instead.
 */
function improveseo_content_v3_generate_article( array $arr, $title ) {
	$session_key = improveseo_content_v3_session_key( isset( $arr['target_key'] ) ? $arr['target_key'] : '' );
	if ( '' === $session_key ) {
		return array( 'content' => 'Error: ' . __( 'The wizard session could not be identified. Please reload the page and start again.', 'improveseo' ), 'meta_title' => '', 'meta_description' => '' );
	}
	$text = function ( $key, $max = 600 ) use ( $arr ) {
		$v = isset( $arr[ $key ] ) && is_string( $arr[ $key ] ) ? trim( sanitize_textarea_field( wp_unslash( $arr[ $key ] ) ) ) : '';
		return function_exists( 'mb_substr' ) ? mb_substr( $v, 0, $max ) : substr( $v, 0, $max );
	};

	$sizes = array(
		'600 to 1200 words'  => 'small',
		'1200 to 2400 words' => 'medium',
		'2400 to 3600 words' => 'large',
	);
	$size = isset( $arr['iseo_v3_post_size'] ) && in_array( $arr['iseo_v3_post_size'], array( 'small', 'medium', 'large' ), true )
		? $arr['iseo_v3_post_size']
		: ( isset( $arr['nos_of_words'], $sizes[ urldecode( $arr['nos_of_words'] ) ] ) ? $sizes[ urldecode( $arr['nos_of_words'] ) ] : 'medium' );

	$answers   = array();
	$questions = isset( $arr['iseo_v3_owner_q'] ) && is_array( $arr['iseo_v3_owner_q'] ) ? $arr['iseo_v3_owner_q'] : array();
	$replies   = isset( $arr['iseo_v3_owner_a'] ) && is_array( $arr['iseo_v3_owner_a'] ) ? $arr['iseo_v3_owner_a'] : array();
	foreach ( $questions as $i => $q ) {
		$a = isset( $replies[ $i ] ) ? trim( sanitize_textarea_field( wp_unslash( (string) $replies[ $i ] ) ) ) : '';
		if ( '' !== $a && count( $answers ) < 2 ) {
			$answers[] = array( 'question' => sanitize_text_field( wp_unslash( (string) $q ) ), 'answer' => $a );
		}
	}

	// "Details for this post" (nd_<id>) and the regenerate instruction travel as <anything_else>;
	// a pricing detail also tells the blueprint a price is available.
	$details = array();
	$pricing = '';
	foreach ( $arr as $k => $v ) {
		if ( 0 !== strpos( (string) $k, 'nd_' ) ) {
			continue;
		}
		$v = is_array( $v ) ? implode( ', ', array_map( 'sanitize_text_field', $v ) ) : sanitize_text_field( (string) $v );
		if ( '' === trim( $v ) ) {
			continue;
		}
		$label     = str_replace( '_', ' ', substr( (string) $k, 3 ) );
		$details[] = $label . ': ' . $v;
		if ( false !== strpos( $label, 'pric' ) || false !== strpos( $label, 'cost' ) ) {
			$pricing .= ( $pricing ? '; ' : '' ) . $v;
		}
	}
	$anything = trim( implode( "\n", array_filter( array( $text( 'custom_instruction', 500 ), implode( "\n", $details ) ) ) ) );

	$regulated = null;
	if ( isset( $arr['iseo_v3_regulated_shown'] ) && '1' === $arr['iseo_v3_regulated_shown'] ) {
		$regulated = ! ( isset( $arr['iseo_v3_regulated_choice'] ) && 'off' === $arr['iseo_v3_regulated_choice'] );
	}

	$cta_url = isset( $arr['cta_url'] ) ? trim( (string) $arr['cta_url'] ) : '';
	if ( '' !== $cta_url && function_exists( 'improveseo_normalize_cta_url' ) ) {
		$cta_url = improveseo_normalize_cta_url( $cta_url );
	}
	$tone = isset( $arr['content_type'] ) ? sanitize_key( $arr['content_type'] ) : '';

	$body = array(
		'session_key'      => $session_key,
		'post_size'        => $size,
		'article_type'     => isset( $arr['iseo_v3_article_type'] ) && in_array( $arr['iseo_v3_article_type'], array( 'cost', 'decision', 'question', 'topic' ), true ) ? $arr['iseo_v3_article_type'] : null,
		'regulated'        => $regulated,
		'point_of_view'    => improveseo_v3_map_point_of_view( isset( $arr['point_of_view'] ) ? (string) $arr['point_of_view'] : '' ),
		'tone'             => $tone,
		'language'         => $text( 'content_lang', 40 ),
		'business_name'    => trim( (string) get_option( 'improveseo_business_name', '' ) ) ? trim( (string) get_option( 'improveseo_business_name', '' ) ) : wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
		'stand_out'        => trim( (string) get_option( 'improveseo_business_stand_out', '' ) ),
		'business_pricing' => $pricing,
		'cta_text'         => $text( 'call_to_action', 300 ),
		'cta_url'          => $cta_url,
		'owner_answers'    => $answers,
		'anything_else'    => $anything,
	);
	$body = array_filter( $body, function ( $v ) {
		return null !== $v && '' !== $v && array() !== $v;
	} );

	$error  = '';
	$status = 0;
	$code   = '';
	$data   = improveseo_content_v3_request( 'POST', '/api/v1/content-v3/article', $body, $error, $status, 600, $code );
	if ( ! is_array( $data ) ) {
		if ( 409 === $status && 'v3_disabled' === $code ) {
			improveseo_content_v3_forget_server_status();
			return null;
		}
		if ( 401 === $status || 403 === $status ) {
			return array( 'content' => 'ISEO_NOT_CONNECTED::' . $error, 'meta_title' => '', 'meta_description' => '' );
		}
		if ( 402 === $status ) {
			return array( 'content' => 'Error: Content generation server returned error status: 402 — ' . $error, 'meta_title' => '', 'meta_description' => '' );
		}
		return array( 'content' => 'Error: ' . $error, 'meta_title' => '', 'meta_description' => '' );
	}

	$d      = isset( $data['data'] ) && is_array( $data['data'] ) ? $data['data'] : array();
	$review = isset( $d['review'] ) && is_array( $d['review'] ) ? $d['review'] : array();
	$issues = array();
	foreach ( (array) ( isset( $review['issues'] ) ? $review['issues'] : array() ) as $issue ) {
		if ( is_array( $issue ) && ! empty( $issue['message'] ) ) {
			$issues[] = array(
				'severity' => isset( $issue['severity'] ) && 'error' === $issue['severity'] ? 'error' : 'warning',
				'message'  => sanitize_text_field( (string) $issue['message'] ),
			);
		}
	}
	return array(
		// The article HTML is the server's sanitized output; it is shown in the preview and saved
		// through the normal post flow, which applies wp_kses_post().
		'content'          => isset( $d['content'] ) ? (string) $d['content'] : '',
		'meta_title'       => isset( $d['meta_title'] ) ? sanitize_text_field( (string) $d['meta_title'] ) : '',
		'meta_description' => isset( $d['meta_description'] ) ? sanitize_text_field( (string) $d['meta_description'] ) : '',
		'v3'               => array(
			'generation_id' => isset( $d['generation_id'] ) ? absint( $d['generation_id'] ) : 0,
			'proof_level'   => isset( $d['proof_level'] ) && 'regulated' === $d['proof_level'] ? 'regulated' : 'standard',
			'needs_review'  => ! empty( $d['needs_review'] ),
			'issues'        => $issues,
		),
	);
}

/**
 * Post meta for a v3 article (G6): the final proof level and the server's generation id.
 *
 * @param int   $post_id Post ID.
 * @param array $options The project options (ai_v3_proof_level, ai_v3_generation_id).
 * @return void
 */
function improveseo_save_v3_post_meta( $post_id, $options ) {
	$post_id = absint( $post_id );
	if ( ! $post_id || ! is_array( $options ) || empty( $options['ai_v3_proof_level'] ) ) {
		return;
	}
	$level = 'regulated' === $options['ai_v3_proof_level'] ? 'regulated' : 'standard';
	update_post_meta( $post_id, '_iseo_proof_level', $level );
	if ( ! empty( $options['ai_v3_generation_id'] ) ) {
		update_post_meta( $post_id, '_iseo_v3_generation_id', absint( $options['ai_v3_generation_id'] ) );
	}
}
