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

	// The main admin server. Keyword data is the only thing that lives on the second
	// Render service (IMPROVESEO_KEYWORD_DATA_SERVER); the v3 content routes do not.
	$base = defined( 'IMPROVESEO_CONNECTION_SERVER' )
		? IMPROVESEO_CONNECTION_SERVER
		: 'https://imporve-seo-admin-server-nzbm.onrender.com';

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

	// D: the wizard session key, so a plugin line and the server's lines of one run are found together.
	$run = is_array( $body ) && ! empty( $body['session_key'] ) ? ' run=' . preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $body['session_key'] ) : '';
	if ( is_wp_error( $response ) ) {
		error_log( 'improveseo_content_v3_request: HTTP error for ' . $path . $run . ' — ' . $response->get_error_message() );
		$error = __( 'Could not reach the ImproveSEO server. Check this site\'s internet connection and try again.', 'improveseo' );
		improveseo_content_v3_last_response( array( 'status' => 0, 'code' => '' ) );
		return null;
	}

	$status = (int) wp_remote_retrieve_response_code( $response );
	$data   = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( 200 !== $status || ! is_array( $data ) || empty( $data['success'] ) ) {
		$code = is_array( $data ) && isset( $data['error'] ) && is_string( $data['error'] ) ? sanitize_key( $data['error'] ) : '';
		error_log( 'improveseo_content_v3_request: failure path=' . $path . $run . ' | status=' . $status . ' | code=' . ( $code ? $code : 'unknown' ) );
		if ( is_array( $data ) && ! empty( $data['message'] ) && is_string( $data['message'] ) ) {
			$error = $data['message'];
		} elseif ( 401 === $status || 403 === $status ) {
			$error = __( 'ImproveSEO rejected this site\'s API Key or Site Code. Check them in Settings.', 'improveseo' );
		} elseif ( 402 === $status ) {
			$error = __( 'You don\'t have enough credits for this post size. Buy more credits or choose a smaller size.', 'improveseo' );
		} else {
			$error = __( 'Something went wrong on the ImproveSEO server. Please try again.', 'improveseo' );
		}
		improveseo_content_v3_last_response( array( 'status' => $status, 'code' => $code ) );
		return null;
	}

	improveseo_content_v3_last_response( array( 'status' => $status, 'code' => '' ) );
	return $data;
}

/**
 * The status and error code of the last server answer in this request (B-7): bulk tells a server
 * outage (status 0 or 5xx: pause, don't count an attempt) from a real failure, and a switched-off
 * v3 (409) from an error. Pass an array to set it.
 *
 * @param array|null $set array( 'status' => int, 'code' => string ).
 * @return array{status: int, code: string}
 */
function improveseo_content_v3_last_response( $set = null ) {
	static $last = array( 'status' => -1, 'code' => '' );
	if ( is_array( $set ) ) {
		$last = array( 'status' => (int) $set['status'], 'code' => (string) $set['code'] );
	}
	return $last;
}

/**
 * The server's Content v3 status, cached.
 *
 * @param bool $force Skip the cache.
 * @return array{enabled: bool, keyword_data: bool, disclaimer_niches: string[], reached: bool}
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
		$niches = isset( $data['data']['disclaimer_niches'] ) && is_array( $data['data']['disclaimer_niches'] ) ? $data['data']['disclaimer_niches'] : array();
		$result = array(
			'enabled'           => ! empty( $data['data']['enabled'] ),
			'keyword_data'      => ! empty( $data['data']['keyword_data'] ),
			// A3: niche/profile keys with a disclaimer text (a Regulated business needs one for v3).
			'disclaimer_niches' => array_values( array_filter( array_map( 'sanitize_key', array_map( 'strval', $niches ) ) ) ),
			'reached'           => true,
		);
		set_transient( IMPROVESEO_CONTENT_V3_STATUS_TRANSIENT, $result, 5 * MINUTE_IN_SECONDS );
		// B-8: remembered, so one slow check cannot switch a running wizard or batch to v2.
		update_option( 'improveseo_content_v3_last_good', array_merge( $result, array( 'at' => time() ) ), false );
		return $result;
	}

	// The check failed (a slow server, a cold start, a network blip). B-8: keep the last answer the
	// server gave (for up to a day) instead of switching v3 off for a minute; only a server that
	// says v3 is off, or has never answered, means v2. Ask again in a minute.
	$last = get_option( 'improveseo_content_v3_last_good', array() );
	if ( is_array( $last ) && isset( $last['enabled'], $last['at'] ) && time() - (int) $last['at'] < DAY_IN_SECONDS ) {
		$result = array(
			'enabled'           => ! empty( $last['enabled'] ),
			'keyword_data'      => ! empty( $last['keyword_data'] ),
			'disclaimer_niches' => isset( $last['disclaimer_niches'] ) && is_array( $last['disclaimer_niches'] ) ? $last['disclaimer_niches'] : array(),
			'reached'           => false,
		);
	} else {
		$result = array(
			'enabled'           => false,
			'keyword_data'      => false,
			'disclaimer_niches' => array(),
			'reached'           => false,
		);
	}
	set_transient( IMPROVESEO_CONTENT_V3_STATUS_TRANSIENT, $result, MINUTE_IN_SECONDS );
	return $result;
}

/**
 * A3 (decision 14 Nov): a business set to Regulated stays on v2 until the server holds a disclaimer
 * text for its niche (or its profile). Standard businesses are never held back here.
 *
 * @return bool
 */
function improveseo_content_v3_business_allowed() {
	if ( 'regulated' !== improveseo_business_proof_level() ) {
		return true;
	}
	$s       = improveseo_content_v3_server_status();
	$keys    = isset( $s['disclaimer_niches'] ) && is_array( $s['disclaimer_niches'] ) ? $s['disclaimer_niches'] : array();
	$niche   = sanitize_key( (string) get_option( 'improveseo_business_niche', '' ) );
	$profile = sanitize_key( (string) get_option( 'improveseo_business_profile', '' ) );
	return ( '' !== $niche && in_array( $niche, $keys, true ) ) || ( '' !== $profile && in_array( $profile, $keys, true ) );
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
	return improveseo_content_v3_site_enabled() && improveseo_content_v3_server_enabled() && improveseo_content_v3_business_allowed();
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
	if ( ! improveseo_can_generate() ) {
		wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'improveseo' ) ), 403 );
	}
	$key = improveseo_content_v3_session_key( isset( $_POST['session_key'] ) ? $_POST['session_key'] : '' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitized in improveseo_content_v3_session_key().
	if ( '' === $key ) {
		wp_send_json_success( array( 'cleared' => false, 'files_deleted' => 0 ) );
	}
	// On Submit the post keeps its image: only the server session goes (keep_files=1). On Leave,
	// the image files generated for this post go too (W10).
	$keep_files = isset( $_POST['keep_files'] ) && '1' === $_POST['keep_files']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- compared to a literal.
	$deleted    = $keep_files ? 0 : improveseo_content_v3_delete_wizard_files( $key );
	delete_transient( improveseo_content_v3_files_transient( $key ) );
	improveseo_content_v3_files_index_forget( $key );
	$error  = '';
	$status = 0;
	// Leave (no keep_files): the server also drops the unsubmitted post's history row and article text.
	$data   = improveseo_content_v3_request( 'POST', '/api/v1/content-v3/session/clear', array( 'session_key' => $key, 'left' => ! $keep_files ), $error, $status, 10 );
	wp_send_json_success( array( 'cleared' => is_array( $data ) && ! empty( $data['cleared'] ), 'files_deleted' => $deleted ) );
}

/** Transient name holding the image files one wizard session created (7 days, like the server session). */
function improveseo_content_v3_files_transient( $key ) {
	return 'iseo_v3_files_' . md5( (string) $key );
}

/**
 * Remember an image file the wizard created for this session (W10). Only files inside uploads.
 *
 * @param string $key  The wizard's session key.
 * @param string $path Absolute path of the saved file.
 * @return void
 */
function improveseo_content_v3_track_wizard_file( $key, $path ) {
	$key = improveseo_content_v3_session_key( $key );
	if ( '' === $key || ! improveseo_content_v3_active() ) {
		return;
	}
	$uploads = wp_upload_dir();
	$base    = wp_normalize_path( trailingslashit( $uploads['basedir'] ) );
	$path    = wp_normalize_path( (string) $path );
	if ( 0 !== strpos( $path, $base ) ) {
		return;
	}
	$name  = improveseo_content_v3_files_transient( $key );
	$files = get_transient( $name );
	$files = is_array( $files ) ? $files : array();
	if ( ! in_array( $path, $files, true ) && count( $files ) < 50 ) {
		$files[] = $path;
	}
	set_transient( $name, $files, 7 * DAY_IN_SECONDS );

	// D: also in a small index, so files of a wizard that was closed without Leave or Submit are
	// found by the daily cleanup after the transient has expired.
	$index = get_option( IMPROVESEO_V3_FILES_INDEX, array() );
	$index = is_array( $index ) ? $index : array();
	$entry = isset( $index[ $key ] ) && is_array( $index[ $key ] ) ? $index[ $key ] : array( 't' => time(), 'files' => array() );
	if ( ! in_array( $path, (array) $entry['files'], true ) && count( (array) $entry['files'] ) < 50 ) {
		$entry['files'][] = $path;
	}
	$index[ $key ] = $entry;
	if ( count( $index ) > 500 ) {
		uasort( $index, function ( $a, $b ) {
			return (int) $a['t'] - (int) $b['t'];
		} );
		$index = array_slice( $index, -500, null, true );
	}
	update_option( IMPROVESEO_V3_FILES_INDEX, $index, false );
}

const IMPROVESEO_V3_FILES_INDEX = 'improveseo_v3_wizard_files';

/** Submit or Leave ended this wizard: its files are no longer pending. */
function improveseo_content_v3_files_index_forget( $key ) {
	$index = get_option( IMPROVESEO_V3_FILES_INDEX, array() );
	if ( is_array( $index ) && isset( $index[ $key ] ) ) {
		unset( $index[ $key ] );
		update_option( IMPROVESEO_V3_FILES_INDEX, $index, false );
	}
}

/**
 * Is this image used anywhere: an attachment, a post's content or GUID, or any post meta?
 * The daily cleanup deletes nothing that answers yes (trashed posts count as using it).
 *
 * @param string $path Absolute path inside uploads.
 * @return bool
 */
function improveseo_content_v3_file_in_use( $path ) {
	global $wpdb;
	$uploads = wp_upload_dir();
	$url     = str_replace( wp_normalize_path( $uploads['basedir'] ), $uploads['baseurl'], wp_normalize_path( $path ) );
	if ( attachment_url_to_postid( $url ) ) {
		return true;
	}
	$like = '%' . $wpdb->esc_like( basename( $path ) ) . '%';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- a daily cleanup check, nothing to cache.
	if ( $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_content LIKE %s OR guid LIKE %s LIMIT 1", $like, $like ) ) ) {
		return true;
	}
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- as above.
	return (bool) $wpdb->get_var( $wpdb->prepare( "SELECT meta_id FROM {$wpdb->postmeta} WHERE meta_value LIKE %s LIMIT 1", $like ) );
}

/**
 * Deletes one wizard image: only inside uploads, never with "..", and only an image (by extension,
 * or by its content for a file saved without one when GD was missing).
 *
 * @param string $path Absolute path.
 * @return bool Deleted.
 */
function improveseo_content_v3_delete_wizard_file( $path ) {
	$uploads = wp_upload_dir();
	$base    = wp_normalize_path( trailingslashit( $uploads['basedir'] ) );
	$path    = wp_normalize_path( (string) $path );
	if ( 0 !== strpos( $path, $base ) || false !== strpos( $path, '..' ) || ! is_file( $path ) ) {
		return false;
	}
	$is_image = preg_match( '/\.(webp|png|jpe?g)$/i', $path )
		|| ( false === strpos( basename( $path ), '.' ) && function_exists( 'wp_get_image_mime' ) && wp_get_image_mime( $path ) );
	if ( ! $is_image ) {
		return false;
	}
	wp_delete_file( $path );
	return true;
}

/**
 * D: once a day, the files of wizards left open for more than 8 days without Leave or Submit
 * are deleted, unless something uses them.
 */
add_action( 'improveseo_v3_files_cleanup', 'improveseo_content_v3_files_cleanup' );
function improveseo_content_v3_files_cleanup() {
	$index = get_option( IMPROVESEO_V3_FILES_INDEX, array() );
	if ( ! is_array( $index ) || ! $index ) {
		return;
	}
	$deleted = 0;
	foreach ( $index as $key => $entry ) {
		if ( ! is_array( $entry ) || (int) ( isset( $entry['t'] ) ? $entry['t'] : 0 ) > time() - 8 * DAY_IN_SECONDS ) {
			continue;
		}
		foreach ( (array) ( isset( $entry['files'] ) ? $entry['files'] : array() ) as $path ) {
			if ( ! improveseo_content_v3_file_in_use( $path ) && improveseo_content_v3_delete_wizard_file( $path ) ) {
				$deleted++;
			}
		}
		unset( $index[ $key ] );
	}
	update_option( IMPROVESEO_V3_FILES_INDEX, $index, false );
	if ( $deleted ) {
		error_log( 'improveseo content v3: daily cleanup deleted ' . $deleted . ' image file(s) of abandoned wizards' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- one line a day at most.
	}
}
add_action( 'init', 'improveseo_content_v3_schedule_files_cleanup' );
function improveseo_content_v3_schedule_files_cleanup() {
	if ( ! wp_next_scheduled( 'improveseo_v3_files_cleanup' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'improveseo_v3_files_cleanup' );
	}
}

/**
 * Delete the image files recorded for this session — only those, only inside uploads, only images.
 *
 * @param string $key The wizard's session key.
 * @return int Files deleted.
 */
function improveseo_content_v3_delete_wizard_files( $key ) {
	$files = get_transient( improveseo_content_v3_files_transient( $key ) );
	if ( ! is_array( $files ) ) {
		$index = get_option( IMPROVESEO_V3_FILES_INDEX, array() );
		$files = is_array( $index ) && isset( $index[ $key ]['files'] ) ? (array) $index[ $key ]['files'] : array();
	}
	$deleted = 0;
	foreach ( $files as $path ) {
		if ( improveseo_content_v3_delete_wizard_file( $path ) ) {
			$deleted++;
		}
	}
	return $deleted;
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
	if ( ! improveseo_can_generate() ) {
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
	if ( ! improveseo_can_generate() ) {
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
		'regulatedNiche' => improveseo_v3_regulated_niche() ? '1' : '0',
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
			'leaveTitle'        => __( 'Leave this post?', 'improveseo' ),
			'leaveMessage'      => __( 'All data for this post will be lost: the title, the research, the plan, the article and any images generated for it.', 'improveseo' ),
			'leaveStay'         => __( 'Stay', 'improveseo' ),
			'leaveLeave'        => __( 'Leave', 'improveseo' ),
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
	if ( ! improveseo_can_generate() ) {
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
	// B-5: the server answers the niche list only while Content v3 is on; no request otherwise.
	if ( ! improveseo_content_v3_site_enabled() || ! improveseo_content_v3_server_enabled() ) {
		return null;
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
 * A point of view stored by a v3 wizard, for the v2 generator (a task made under v3 and run by v2
 * after v3 was switched off). v2's own values pass through unchanged.
 *
 * @param string $value Stored point_of_view.
 * @return string
 */
function improveseo_v2_point_of_view( $value ) {
	$map = array(
		'we'            => 'First person plural (we,us,our,ours)',
		'i'             => 'First person singular (I,me,my,mine)',
		'business_name' => 'Whatever is suited to keyword',
	);
	$value = (string) $value;
	return isset( $map[ $value ] ) ? $map[ $value ] : $value;
}

/**
 * G5: a regulated niche (the business is set to Regulated, or its niche usually is) gets no Witty tone.
 *
 * @return bool
 */
function improveseo_v3_regulated_niche() {
	if ( 'regulated' === improveseo_business_proof_level() ) {
		return true;
	}
	$row = improveseo_business_niche_row();
	return $row && 'regulated' === $row['proof_level'];
}

/**
 * G5: the tone a v3 article is written in; a stored "witty" becomes "friendly" for a regulated niche
 * (older bulk projects and tasks still hold it; the wizards no longer offer it there).
 *
 * @param string $tone Stored tone.
 * @return string
 */
function improveseo_v3_tone( $tone ) {
	$tone = sanitize_key( (string) $tone );
	return 'witty' === $tone && improveseo_v3_regulated_niche() ? 'friendly' : $tone;
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
	// A3: until a disclaimer text exists for this niche, a Regulated business keeps the current generator.
	$text = improveseo_content_v3_site_enabled() && ! improveseo_content_v3_business_allowed()
		? __( 'Content v3 is not available for regulated businesses yet, so this site keeps generating posts the current way. Once it is, every new post will use stricter sourcing: figures need a cited source, no outcomes are promised, and posts link to at least 3 sources.', 'improveseo' )
		: __( 'Every new Content v3 post uses stricter sourcing: figures need a cited source, no outcomes are promised, and posts link to at least 3 sources. Bulk posts are saved as drafts so you can review them before publishing. You can turn Regulated off for a single post in the post wizard.', 'improveseo' );
	echo '<div class="notice notice-info is-dismissible"><p><strong>' . esc_html__( 'Your business is set as Regulated.', 'improveseo' ) . '</strong> ' . esc_html( $text ) . '</p></div>';
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
	// G5: the wizard hides Witty for a regulated niche; a stored or forced "witty" is mapped too.
	$tone = isset( $arr['content_type'] ) ? improveseo_v3_tone( (string) $arr['content_type'] ) : '';

	// B-3: the wizard's per-click request id (kept until an article arrives): a retry replays.
	$request_id = isset( $_POST['request_id'] ) ? sanitize_text_field( wp_unslash( $_POST['request_id'] ) ) : '';
	$body = array(
		'session_key'      => $session_key,
		'request_id'       => preg_match( '/^[A-Za-z0-9_-]{8,120}$/', $request_id ) ? $request_id : null,
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
		// v3 switched off on the server, or (A3) a Regulated business without a disclaimer text yet:
		// forget the cached status and let the caller generate through v2.
		if ( 409 === $status && in_array( $code, array( 'v3_disabled', 'v3_regulated_disabled' ), true ) ) {
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
	// The server sanitizes the article to its allowlist; the plugin filters it again with
	// wp_kses_post() (keeps data-iseo, id and class) because the save flow does not, and the post is
	// written by an admin with unfiltered_html. Then the same FAQ normalising and content wrapper as
	// v2 (createAIpost2), so the FAQ schema and the plugin's styles apply to v3 posts too (BUG2).
	$content = isset( $d['content'] ) ? wp_kses_post( (string) $d['content'] ) : '';
	if ( function_exists( 'improveseo_normalize_faq_markup' ) ) {
		$content = improveseo_normalize_faq_markup( $content );
	}
	return array(
		// An empty article is an error, never a post (B-1: the wizard shows it as a notice).
		'content'          => '' === trim( wp_strip_all_tags( $content ) )
			? 'Error: ' . __( 'The article came back empty. Please try again.', 'improveseo' )
			: '<div class="main-content-section-improveseo">' . $content . '</div>',
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

/* ======================================================================================
 * Bulk (phase 10): failure bookkeeping for every bulk run (BULK5–BULK7), and the v3 chain per
 * task (BULK1–BULK3). Bulk runs one task per cron tick (bulk_AI_post_function.php).
 * ====================================================================================*/

/** A failed step is retried at most this many times in all; then the task is marked Failed. */
const IMPROVESEO_BULK_MAX_ATTEMPTS = 3;

/**
 * The columns phase 10 adds to the bulk task table: attempts, last_error, v3_review (JSON:
 * proof level, generation id, why the post was saved as a draft, the checks that failed).
 * Explicit, idempotent ALTER like improveseo_ensure_bulk_meta_columns(); improveseo_db_version
 * is never bumped for it.
 */
function improveseo_ensure_bulk_v3_columns() {
	global $wpdb;
	// 'ready2' adds the lease, retry-time and request-id columns (B-7) to 'ready' (attempts,
	// last_error, v3_review), so an install that already ran the first version gets them too.
	if ( 'ready2' === get_option( 'improveseo_bulk_v3_cols' ) ) {
		return;
	}
	$table = $wpdb->prefix . 'improveseo_bulktasksdetails';
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
		return;
	}
	$cols = $wpdb->get_col( "SHOW COLUMNS FROM `$table`" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter -- fixed query text; the only interpolation is the table name
	if ( ! is_array( $cols ) ) {
		return;
	}
	$adds = array();
	if ( ! in_array( 'attempts', $cols, true ) ) {
		$adds[] = 'ADD COLUMN `attempts` INT NOT NULL DEFAULT 0';
	}
	if ( ! in_array( 'last_error', $cols, true ) ) {
		$adds[] = 'ADD COLUMN `last_error` TEXT NULL DEFAULT NULL';
	}
	if ( ! in_array( 'v3_review', $cols, true ) ) {
		$adds[] = 'ADD COLUMN `v3_review` TEXT NULL DEFAULT NULL';
	}
	$more = array(
		'lease_token'     => 'ADD COLUMN `lease_token` VARCHAR(40) NULL DEFAULT NULL',
		'lease_until'     => 'ADD COLUMN `lease_until` DATETIME NULL DEFAULT NULL',
		'next_attempt_at' => 'ADD COLUMN `next_attempt_at` DATETIME NULL DEFAULT NULL',
		'v3_request_id'   => 'ADD COLUMN `v3_request_id` VARCHAR(120) NULL DEFAULT NULL',
	);
	foreach ( $more as $col => $sql ) {
		if ( ! in_array( $col, $cols, true ) ) {
			$adds[] = $sql;
		}
	}
	if ( $adds ) {
		$wpdb->query( "ALTER TABLE `$table` " . implode( ', ', $adds ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter -- fixed query text; the only interpolation is the table name
		$cols = $wpdb->get_col( "SHOW COLUMNS FROM `$table`" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter -- fixed query text; the only interpolation is the table name
	}
	if ( is_array( $cols ) && ! array_diff( array( 'attempts', 'last_error', 'v3_review', 'lease_token', 'lease_until', 'next_attempt_at', 'v3_request_id' ), $cols ) ) {
		update_option( 'improveseo_bulk_v3_cols', 'ready2', true );
	}
}

/** B-7: a claimed task's lease (minutes): longer than the longest v3 task (title + approve + article ≈ 21 min). */
if ( ! defined( 'IMPROVESEO_BULK_LEASE_MINUTES' ) ) {
	define( 'IMPROVESEO_BULK_LEASE_MINUTES', 30 );
}

/**
 * B-7: refresh a claimed task's lease between steps. False when another run took the task (its
 * lease expired), so this run stops instead of writing over it.
 *
 * @param object $task Row of improveseo_bulktasksdetails, with lease_token set by the claim.
 * @return bool
 */
function improveseo_bulk_heartbeat( $task ) {
	global $wpdb;
	if ( empty( $task->lease_token ) ) {
		return true;
	}
	$n = $wpdb->query(
		$wpdb->prepare(
			"UPDATE `{$wpdb->prefix}improveseo_bulktasksdetails` SET lease_until = DATE_ADD(NOW(), INTERVAL %d MINUTE), updated_at = NOW() WHERE id = %d AND lease_token = %s",
			IMPROVESEO_BULK_LEASE_MINUTES,
			(int) $task->id,
			(string) $task->lease_token
		)
	);
	return 1 === (int) $n;
}

/** The WHERE for a write by the run that holds the task (B-7). */
function improveseo_bulk_owner_where( $task ) {
	$where = array( 'id' => (int) $task->id );
	if ( ! empty( $task->lease_token ) ) {
		$where['lease_token'] = (string) $task->lease_token;
	}
	return $where;
}

/**
 * B-7: the server could not be reached (or answered 5xx). Not the task's fault: no attempt is
 * counted, the task waits, and the whole queue pauses with a growing wait (1, 2, 4 … 30 min) so an
 * outage cannot burn every task's attempts. The request id is kept: the server may have finished.
 *
 * @param object $task   The task.
 * @param string $reason Shown on the task details screen.
 */
function improveseo_bulk_task_outage( $task, $reason, $whole_queue = true ) {
	global $wpdb;
	$pause = get_option( 'improveseo_bulk_pause', array() );
	$level = is_array( $pause ) && isset( $pause['level'] ) ? min( 5, (int) $pause['level'] + 1 ) : 0;
	$wait  = min( 1800, 60 * ( 2 ** $level ) );
	if ( $whole_queue ) {
		update_option( 'improveseo_bulk_pause', array( 'until' => time() + $wait, 'level' => $level ), false );
	} else {
		$wait = 120; // Only this task waits (its earlier run is still finishing on the server).
	}
	$wpdb->update(
		$wpdb->prefix . 'improveseo_bulktasksdetails',
		array(
			'status'          => 'Pending',
			'last_error'      => (string) $reason,
			'next_attempt_at' => gmdate( 'Y-m-d H:i:s', time() + $wait ),
			'lease_token'     => null,
			'lease_until'     => null,
			'updated_at'      => current_time( 'mysql' ),
		),
		improveseo_bulk_owner_where( $task )
	);
	if ( function_exists( 'my_plugin_log' ) ) {
		my_plugin_log( 'bulk task ' . (int) $task->id . ' run=' . improveseo_bulk_v3_session_key( $task ) . ' paused (server unreachable), queue resumes in ' . $wait . ' s | ' . $reason );
	}
}

/**
 * H1: how many times one task may be put back as "retry later" (an outage, a restart, a rate
 * limit) before the next one counts as a failed attempt, so no task can wait forever.
 */
const IMPROVESEO_BULK_MAX_OUTAGES = 8;

/**
 * H1: counts one "retry later" for this task; false once its budget is used up.
 *
 * @param object $task The task.
 * @return bool
 */
function improveseo_bulk_outage_budget_left( $task ) {
	$map = get_option( 'improveseo_bulk_outages', array() );
	$map = is_array( $map ) ? $map : array();
	$id  = (string) absint( $task->id );
	$map[ $id ] = ( isset( $map[ $id ] ) ? (int) $map[ $id ] : 0 ) + 1;
	if ( count( $map ) > 500 ) {
		$map = array_slice( $map, -500, null, true );
	}
	update_option( 'improveseo_bulk_outages', $map, false );
	return $map[ $id ] <= IMPROVESEO_BULK_MAX_OUTAGES;
}

/** H1: the task finished (Done or Failed): forget its outage count. */
function improveseo_bulk_outage_forget( $task ) {
	$map = get_option( 'improveseo_bulk_outages', array() );
	if ( is_array( $map ) && isset( $map[ (string) absint( $task->id ) ] ) ) {
		unset( $map[ (string) absint( $task->id ) ] );
		update_option( 'improveseo_bulk_outages', $map, false );
	}
}

/** B-7: is the queue paused after a server outage? */
function improveseo_bulk_paused() {
	$pause = get_option( 'improveseo_bulk_pause', array() );
	return is_array( $pause ) && isset( $pause['until'] ) && (int) $pause['until'] > time();
}

/** B-7: the server answered again: lift the pause. */
function improveseo_bulk_unpause() {
	if ( false !== get_option( 'improveseo_bulk_pause', false ) ) {
		delete_option( 'improveseo_bulk_pause' );
	}
}

/**
 * B-7: the server switched v3 off for this request (409 v3_disabled, or v3_regulated_disabled for a
 * Regulated business without a disclaimer): the task goes back to the queue without counting an
 * attempt and the cached status is dropped, so the next run generates it through v2.
 *
 * @param object $task The task.
 */
function improveseo_bulk_task_requeue_v2( $task ) {
	global $wpdb;
	improveseo_content_v3_forget_server_status();
	$wpdb->update(
		$wpdb->prefix . 'improveseo_bulktasksdetails',
		array( 'status' => 'Pending', 'lease_token' => null, 'lease_until' => null, 'v3_request_id' => null, 'updated_at' => current_time( 'mysql' ) ),
		improveseo_bulk_owner_where( $task )
	);
	if ( function_exists( 'my_plugin_log' ) ) {
		my_plugin_log( 'bulk task ' . (int) $task->id . ' run=' . improveseo_bulk_v3_session_key( $task ) . ': the server answered v3 off — requeued for v2' );
	}
}

/**
 * B-7: decide what a failed v3 call means for the task, from the last server answer: an outage
 * pauses, a switched-off v3 requeues for v2, anything else is a counted failure.
 *
 * @param object $task   The task.
 * @param string $reason The failure text.
 * @param array  $keep   Columns to keep (as improveseo_bulk_task_failed).
 * @param bool   $final  Fail at once (no credits, not connected).
 * @return void
 */
function improveseo_bulk_v3_failure( $task, $reason, array $keep = array(), $final = false ) {
	$last   = improveseo_content_v3_last_response();
	$status = (int) $last['status'];
	$code   = (string) $last['code'];
	if ( 409 === $status && in_array( $code, array( 'v3_disabled', 'v3_regulated_disabled' ), true ) ) {
		improveseo_bulk_task_requeue_v2( $task );
		return;
	}
	// H1: "retry later" only when the server could not be reached, a gateway answered with no v3
	// error (a deploy or a cold start), the server restarted mid-article, or a limit was hit; a
	// step that failed (step_failed, timeout, server_error, …) is a counted attempt, so a task that
	// fails every time ends Failed instead of retrying forever.
	$unreachable = 0 === $status || ( in_array( $status, array( 502, 503, 504 ), true ) && '' === $code );
	$later       = $unreachable || in_array( $code, array( 'service_unavailable', 'rate_limited', 'already_generating' ), true );
	if ( ! $final && $later && improveseo_bulk_outage_budget_left( $task ) ) {
		if ( 'rate_limited' === $code ) {
			$why = __( 'Too many requests in a short time; this post will be retried automatically.', 'improveseo' );
		} elseif ( 'already_generating' === $code ) {
			$why = __( 'An earlier run of this post is still finishing; it will be retried automatically.', 'improveseo' );
		} else {
			$why = __( 'The ImproveSEO server could not be reached; this post will be retried automatically.', 'improveseo' );
		}
		improveseo_bulk_task_outage( $task, $why, 'already_generating' !== $code );
		return;
	}
	improveseo_bulk_task_failed( $task, $reason, $keep, $final );
}

/**
 * B-3/B-7: the task's article request id: created once, kept until the article is saved to the
 * task (or a definite failure), so a run that dies after the server finished replays that article
 * instead of buying it again.
 *
 * @param object $task The task.
 * @return string
 */
function improveseo_bulk_v3_request_id( $task ) {
	global $wpdb;
	if ( ! empty( $task->v3_request_id ) && preg_match( '/^[A-Za-z0-9_-]{8,120}$/', (string) $task->v3_request_id ) ) {
		return (string) $task->v3_request_id;
	}
	$id = improveseo_bulk_v3_session_key( $task ) . '_' . strtolower( wp_generate_password( 12, false, false ) );
	$wpdb->update( $wpdb->prefix . 'improveseo_bulktasksdetails', array( 'v3_request_id' => $id ), array( 'id' => (int) $task->id ) );
	$task->v3_request_id = $id;
	return $id;
}

/**
 * Is this generator output an error rather than an article? (BULK7)
 *
 * Every generator error is returned as content beginning "Error:" (createAIpost2bulk, the v3 path
 * below), whatever its length. The old check only caught errors under 100 characters, so a longer
 * message — a 402 "Not enough credits: this needs 10 and you have 4…" — was saved as Done and
 * PUBLISHED as the post body.
 *
 * @param string $content Generated content.
 * @return bool
 */
function improveseo_bulk_content_is_error( $content ) {
	$t = ltrim( (string) $content );
	if ( '' === $t ) {
		return true;
	}
	if ( 0 === stripos( $t, 'Error:' ) || 0 === strpos( $t, 'ISEO_NOT_CONNECTED' ) ) {
		return true;
	}
	return strlen( $t ) < 100 && (
		false !== stripos( $t, 'Error' ) ||
		false !== stripos( $t, 'Failed' ) ||
		false !== stripos( $t, 'failed to open stream' ) ||
		false !== stripos( $t, 'connection' )
	);
}

/**
 * A short, plain reason for the task details screen. Never the provider's raw text.
 *
 * @param string $content The error content.
 * @return string
 */
function improveseo_bulk_error_reason( $content ) {
	$t = trim( wp_strip_all_tags( (string) $content ) );
	if ( false !== strpos( $t, 'status: 402' ) || false !== stripos( $t, 'not enough credits' ) || false !== stripos( $t, 'insufficient' ) ) {
		return __( 'Not enough credits for this post. Buy credits, then regenerate it.', 'improveseo' );
	}
	if ( false !== strpos( $t, 'ISEO_NOT_CONNECTED' ) || false !== strpos( $t, 'API credentials' ) ) {
		return __( 'This site is not connected to ImproveSEO. Reconnect it in Settings, then regenerate.', 'improveseo' );
	}
	$t = preg_replace( '/^Error:\s*/i', '', $t );
	$t = preg_replace( '/^Content generation server returned error status: \d+\s*—?\s*/', '', $t );
	return function_exists( 'mb_substr' ) ? mb_substr( $t, 0, 300 ) : substr( $t, 0, 300 );
}

/**
 * Record a failed attempt (BULK6). Keeps whatever was already produced (title, image, content)
 * so the next run does not pay for it again (BULK5). After IMPROVESEO_BULK_MAX_ATTEMPTS, or at once
 * when retrying cannot help (no credits, not connected), the task is marked Failed — a terminal
 * status the project progress counts — instead of staying Pending forever.
 *
 * @param object $task   The task row.
 * @param string $reason Plain reason.
 * @param array  $keep   Columns to save (ai_title, ai_image, ai_content [base64], meta_title, meta_description).
 * @param bool   $final  Fail now regardless of the count.
 * @return string The new status.
 */
function improveseo_bulk_task_failed( $task, $reason, array $keep = array(), $final = false ) {
	global $wpdb;
	improveseo_ensure_bulk_v3_columns();
	$attempts = ( isset( $task->attempts ) ? (int) $task->attempts : 0 ) + 1;
	$status   = ( $final || $attempts >= IMPROVESEO_BULK_MAX_ATTEMPTS ) ? 'Failed' : 'Pending';
	if ( 'Failed' === $status ) {
		improveseo_bulk_outage_forget( $task );
	}
	$data     = array_merge(
		array_intersect_key( $keep, array_flip( array( 'ai_title', 'ai_image', 'ai_content', 'meta_title', 'meta_description', 'v3_review' ) ) ),
		array(
			'status'          => $status,
			'attempts'        => $attempts,
			'last_error'      => (string) $reason,
			'updated_at'      => current_time( 'mysql' ),
			// B-7: wait before the next attempt (2, 4, 8 … min), release the lease, start a new request.
			'next_attempt_at' => 'Pending' === $status ? gmdate( 'Y-m-d H:i:s', time() + 60 * min( 30, 2 ** $attempts ) ) : null,
			'lease_token'     => null,
			'lease_until'     => null,
			'v3_request_id'   => null,
		)
	);
	$wpdb->update( $wpdb->prefix . 'improveseo_bulktasksdetails', $data, improveseo_bulk_owner_where( $task ) );
	if ( function_exists( 'my_plugin_log' ) ) {
		my_plugin_log( 'bulk task ' . (int) $task->id . ' run=' . improveseo_bulk_v3_session_key( $task ) . ' failed attempt ' . $attempts . '/' . IMPROVESEO_BULK_MAX_ATTEMPTS . ' → ' . $status . ' | ' . $reason );
	}
	if ( 'Failed' === $status && ! empty( $task->bulktask_id ) && function_exists( 'improveseo_sync_bulk_parent_progress' ) ) {
		improveseo_sync_bulk_parent_progress( (int) $task->bulktask_id );
	}
	return $status;
}

/** Stable per task, so a regeneration is priced as one (server history keyed on it). */
function improveseo_bulk_v3_session_key( $task ) {
	// H3: task ids restart at 1 on every site, so a short hash of the site code makes the key unique
	// per account (server-side regeneration pricing and the one-running-run rule are keyed by it)
	// without writing the site code itself into every log line.
	$site = (string) get_option( 'improveseo_site_code', '' );
	return 'iseo_bulk_' . substr( md5( 'iseo-bulk|' . $site ), 0, 10 ) . '_t' . absint( $task->id );
}

/**
 * The most list items a size holds: its body words over the strictest per-item minimum (60),
 * capped at 15 — the server's minimum-size rule, so a bulk title never needs a larger size.
 *
 * @param string $size small|medium|large.
 * @return int
 */
function improveseo_bulk_v3_max_items( $size ) {
	$body = array( 'small' => 900 - 380, 'medium' => 1800 - 450, 'large' => 3000 - 540 );
	$n    = isset( $body[ $size ] ) ? (int) floor( $body[ $size ] / 60 ) : 15;
	return max( 1, min( 15, $n ) );
}

function improveseo_bulk_v3_size( $task ) {
	$map = array(
		'600 to 1200 words'  => 'small',
		'1200 to 2400 words' => 'medium',
		'2400 to 3600 words' => 'large',
	);
	$words = isset( $task->nos_of_words ) ? urldecode( (string) $task->nos_of_words ) : '';
	return isset( $map[ $words ] ) ? $map[ $words ] : 'medium';
}

/**
 * W5: a bulk project's Target town, chosen in the bulk wizard: one of the saved towns, or
 * "__none__" for "Not about a specific location". Stored per project as an option.
 *
 * @param int    $project_id improveseo_bulktasks id.
 * @param string $town       The choice.
 * @return void
 */
function improveseo_bulk_v3_set_town( $project_id, $town ) {
	$town = trim( (string) $town );
	if ( $project_id <= 0 || '' === $town ) {
		return;
	}
	if ( '__none__' !== $town && ! in_array( $town, improveseo_business_towns(), true ) ) {
		return;
	}
	update_option( 'improveseo_bulk_v3_town_' . (int) $project_id, $town, false );
}

/**
 * What every v3 bulk request shares. A local business writes for the project's Target town (W5;
 * default the first saved town), or for no town when the project chose "Not about a specific
 * location"; a nationwide one for no town.
 */
function improveseo_bulk_v3_common( $task ) {
	$reach  = improveseo_business_reach();
	$towns  = improveseo_business_towns();
	$chosen = isset( $task->bulktask_id ) ? (string) get_option( 'improveseo_bulk_v3_town_' . (int) $task->bulktask_id, '' ) : '';
	if ( 'local' !== $reach ) {
		$town = '';
	} elseif ( '__none__' === $chosen ) {
		$town = '';
	} elseif ( '' !== $chosen ) {
		$town = $chosen;
	} else {
		$town = $towns ? $towns[0] : '';
	}
	$niche = (string) get_option( 'improveseo_business_niche', '' );
	return array_filter(
		array(
			'session_key'          => improveseo_bulk_v3_session_key( $task ),
			'seed_keyword'         => trim( (string) $task->keyword_name ),
			'language'             => isset( $task->content_lang ) ? sanitize_text_field( (string) $task->content_lang ) : '',
			'niche'                => '' !== $niche ? $niche : 'general_blog',
			'niche_profile'        => (string) get_option( 'improveseo_business_profile', '' ),
			'reach'                => $reach,
			'target_town'          => $town,
			'not_about_location'   => 'local' === $reach && '' === $town,
			'state_hint'           => improveseo_business_state_hint(),
			'proof_level'          => improveseo_business_proof_level(),
			'featured_service'     => trim( (string) get_option( 'improveseo_business_service', '' ) ),
			'ideal_client'         => trim( (string) get_option( 'improveseo_business_ideal_client', '' ) ),
			'business_description' => trim( (string) get_option( 'improveseo_business_description', '' ) ),
		),
		function ( $v ) {
			return '' !== $v && null !== $v && false !== $v;
		}
	);
}

/**
 * The v3 title for a bulk task ('' with $error on failure). "Exact keyword as title" stays the keyword.
 */
function improveseo_bulk_v3_title( $task, &$error = null ) {
	$error = '';
	if ( isset( $task->select_exisiting_options ) && 'seed_option1' === $task->select_exisiting_options ) {
		return trim( (string) $task->keyword_name );
	}
	$body = improveseo_bulk_v3_common( $task );
	unset( $body['niche_profile'], $body['business_description'] );
	$body['max_list_items'] = improveseo_bulk_v3_max_items( improveseo_bulk_v3_size( $task ) );
	if ( function_exists( 'improveseo_v3_step_fields' ) ) {
		$body = array_merge( $body, improveseo_v3_step_fields( 'title' ) );
	}
	$status = 0;
	$data   = improveseo_content_v3_request( 'POST', '/api/v1/content-v3/wizard/title', $body, $error, $status, 240 );
	if ( ! is_array( $data ) ) {
		return '';
	}
	$title = isset( $data['data']['title'] ) ? sanitize_text_field( (string) $data['data']['title'] ) : '';
	if ( '' === $title ) {
		$error = isset( $data['data']['message'] ) ? sanitize_text_field( (string) $data['data']['message'] ) : __( 'No title was generated.', 'improveseo' );
	}
	return $title;
}

/**
 * Approve the title (research + planner) and write the article, for one bulk task.
 *
 * Returns the createAIpost2bulk() shape — content (or "Error: …"), meta_title, meta_description —
 * plus 'v3' with the proof level, the generation id, needs_review and the failed checks. In bulk the
 * regulated suggestion applies automatically (regulated: null on the article request).
 */
function improveseo_bulk_v3_article( $task, $title ) {
	$fail = function ( $message, $status = 0 ) {
		$prefix = 402 === (int) $status ? 'Content generation server returned error status: 402 — ' : '';
		return array( 'content' => 'Error: ' . $prefix . $message, 'meta_title' => '', 'meta_description' => '' );
	};

	$approve          = improveseo_bulk_v3_common( $task );
	$approve['title'] = $title;
	$approve['bulk_batch'] = 'bulk_' . absint( $task->bulktask_id );
	if ( function_exists( 'improveseo_v3_step_fields' ) ) {
		$approve = array_merge( $approve, improveseo_v3_step_fields( 'planner' ) );
	}
	$error  = '';
	$status = 0;
	$code   = '';
	$plan   = improveseo_content_v3_request( 'POST', '/api/v1/content-v3/wizard/approve', $approve, $error, $status, 420, $code );
	if ( ! is_array( $plan ) ) {
		return $fail( $error, $status );
	}

	$cta = function_exists( 'improveseo_split_cta_field' ) ? improveseo_split_cta_field( isset( $task->call_to_action ) ? $task->call_to_action : '' ) : array( 'text' => '', 'url' => '' );
	$body = array_filter(
		array(
			'session_key'   => improveseo_bulk_v3_session_key( $task ),
			// B-3: kept until the article is saved to the task, so a dead run replays instead of re-buying.
			'request_id'    => improveseo_bulk_v3_request_id( $task ),
			'post_size'     => improveseo_bulk_v3_size( $task ),
			'point_of_view' => improveseo_v3_map_point_of_view( isset( $task->point_of_view ) ? (string) $task->point_of_view : '' ),
			'tone'          => isset( $task->tone_of_voice ) ? improveseo_v3_tone( (string) $task->tone_of_voice ) : '',
			'language'      => isset( $task->content_lang ) ? sanitize_text_field( (string) $task->content_lang ) : '',
			'business_name' => trim( (string) get_option( 'improveseo_business_name', '' ) ) ? trim( (string) get_option( 'improveseo_business_name', '' ) ) : wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
			'stand_out'     => trim( (string) get_option( 'improveseo_business_stand_out', '' ) ),
			'cta_text'      => isset( $cta['text'] ) ? (string) $cta['text'] : '',
			'cta_url'       => isset( $cta['url'] ) ? (string) $cta['url'] : '',
			'anything_else' => isset( $task->details_to_include ) ? sanitize_textarea_field( (string) $task->details_to_include ) : '',
		),
		function ( $v ) {
			return '' !== $v && null !== $v;
		}
	);
	$data = improveseo_content_v3_request( 'POST', '/api/v1/content-v3/article', $body, $error, $status, 600, $code );
	if ( ! is_array( $data ) ) {
		return $fail( $error, $status );
	}
	$d      = isset( $data['data'] ) && is_array( $data['data'] ) ? $data['data'] : array();
	$issues = array();
	foreach ( (array) ( isset( $d['review']['issues'] ) ? $d['review']['issues'] : array() ) as $issue ) {
		if ( is_array( $issue ) && isset( $issue['severity'], $issue['message'] ) && 'error' === $issue['severity'] ) {
			$issues[] = sanitize_text_field( (string) $issue['message'] );
		}
	}
	// wp_kses_post() as in the single wizard; an empty article is a failed attempt, never a post (BULK7).
	$content = isset( $d['content'] ) ? wp_kses_post( (string) $d['content'] ) : '';
	if ( '' === trim( wp_strip_all_tags( $content ) ) ) {
		return $fail( __( 'The article came back empty.', 'improveseo' ), 0 );
	}
	if ( function_exists( 'improveseo_normalize_faq_markup' ) ) {
		$content = improveseo_normalize_faq_markup( $content );
	}
	return array(
		'content'          => '<div class="main-content-section-improveseo">' . $content . '</div>',
		// M4: the title the article was written for (a replay answers with the original one).
		'title'            => isset( $d['title'] ) ? sanitize_text_field( (string) $d['title'] ) : '',
		'meta_title'       => isset( $d['meta_title'] ) ? sanitize_text_field( (string) $d['meta_title'] ) : '',
		'meta_description' => isset( $d['meta_description'] ) ? sanitize_text_field( (string) $d['meta_description'] ) : '',
		'v3'               => array(
			'proof_level'   => isset( $d['proof_level'] ) && 'regulated' === $d['proof_level'] ? 'regulated' : 'standard',
			'generation_id' => isset( $d['generation_id'] ) ? absint( $d['generation_id'] ) : 0,
			'needs_review'  => ! empty( $d['needs_review'] ),
			'issues'        => $issues,
		),
	);
}

/**
 * Bulk v3 drafts (G3, U3): a Regulated business's posts, and any post that still failed the
 * checks, are saved as drafts for the owner to review. Returns the v3_review JSON for the task.
 */
function improveseo_bulk_v3_review( array $v3 ) {
	$reason = '';
	if ( 'regulated' === improveseo_business_proof_level() ) {
		$reason = 'regulated';
	}
	if ( ! empty( $v3['needs_review'] ) ) {
		$reason = 'needs_review';
	}
	return array(
		'proof_level'   => isset( $v3['proof_level'] ) ? $v3['proof_level'] : 'standard',
		'generation_id' => isset( $v3['generation_id'] ) ? (int) $v3['generation_id'] : 0,
		'draft_reason'  => $reason,
		'issues'        => isset( $v3['issues'] ) ? array_slice( (array) $v3['issues'], 0, 8 ) : array(),
	);
}

/** Post meta for a bulk v3 post, from the task's v3_review JSON (G6). */
function improveseo_bulk_v3_post_meta( $post_id, $task ) {
	if ( empty( $task->v3_review ) ) {
		return;
	}
	$r = json_decode( (string) $task->v3_review, true );
	if ( ! is_array( $r ) ) {
		return;
	}
	improveseo_save_v3_post_meta( $post_id, array(
		'ai_v3_proof_level'   => isset( $r['proof_level'] ) ? $r['proof_level'] : 'standard',
		'ai_v3_generation_id' => isset( $r['generation_id'] ) ? $r['generation_id'] : 0,
	) );
}
