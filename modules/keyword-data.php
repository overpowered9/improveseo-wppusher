<?php
/**
 * Keyword data: the plugin side of the server's DataForSEO-backed keyword routes.
 *
 * Phase 1 (this file's first section): the API client.
 *
 *   improveseo_keyword_metrics()   POST /api/v1/generate/keywords/metrics        (Option A)
 *   improveseo_keyword_prefetch()  POST /api/v1/content-v3/keyword-data/prefetch (Option B)
 *
 * Both mirror improveseo_call_auxiliary_api() (modules/single_and_bulk_AI_post_function.php):
 * the same stored credentials, the same three headers, wp_remote_post(), an $error string by
 * reference for the UI, and $is_not_connected set only on a real 401/403 so a caller can route
 * that one case to the shared connection-guard modal. The server's contract shapes are documented
 * in the admin server's docs/KEYWORD_DATA_V3.md (branch feature/keyword-data-v3).
 *
 * Numbers are never sent TO the server by this plugin — the user picks the keyword; the server
 * reads the metrics from its own cache. The plugin only ever receives them.
 *
 * @package ImproveSEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Admin server base URL.
 *
 * Reuses the constant includes/connection-status.php defines; the literal is the fallback for
 * the (unexpected) case where that file is not loaded yet.
 *
 * @return string
 */
function improveseo_keyword_data_server_url() {
	if ( defined( 'IMPROVESEO_CONNECTION_SERVER' ) ) {
		return IMPROVESEO_CONNECTION_SERVER;
	}
	return 'https://imporve-seo-admin-server-nzbm.onrender.com';
}

/**
 * The server's error codes, translated into a sentence a site owner can act on.
 *
 * The server already writes its `error` field for humans; these only replace it where the
 * generic sentence would be misleading (a 503 "not configured" is the server operator's job,
 * not the user's) or where there is no body at all.
 *
 * @param int    $status  HTTP status.
 * @param string $code    Server `code` field, if any.
 * @param string $message Server `error` field, if any.
 * @return string
 */
function improveseo_keyword_data_error_message( $status, $code, $message ) {
	$status = (int) $status;
	$code   = (string) $code;

	if ( 401 === $status || 403 === $status ) {
		return __( 'ImproveSEO rejected this site\'s API Key or Site Code.', 'improveseo' );
	}
	if ( 402 === $status ) {
		return $message ? $message : __( 'Not enough credits for keyword data.', 'improveseo' );
	}
	if ( 429 === $status ) {
		return __( 'Too many keyword data requests. Please wait a minute and try again.', 'improveseo' );
	}
	if ( 'KEYWORD_DATA_NOT_CONFIGURED' === $code ) {
		return __( 'Keyword data is not enabled on the ImproveSEO server yet.', 'improveseo' );
	}
	if ( 504 === $status || 'KEYWORD_DATA_TIMEOUT' === $code ) {
		return __( 'The keyword data provider took too long to answer. Please try again.', 'improveseo' );
	}
	if ( 'KEYWORD_DATA_RATE_LIMITED' === $code ) {
		return __( 'The keyword data provider is busy. Please try again shortly.', 'improveseo' );
	}
	if ( 'KEYWORD_DATA_PROVIDER_UNAVAILABLE' === $code || 'KEYWORD_DATA_PROVIDER_ERROR' === $code ) {
		return __( 'The keyword data provider is unavailable right now. Please try again later.', 'improveseo' );
	}
	if ( is_string( $message ) && '' !== trim( $message ) ) {
		return $message;
	}
	/* translators: %d: HTTP status code */
	return sprintf( __( 'The ImproveSEO server returned an error (HTTP %d).', 'improveseo' ), $status );
}

/**
 * One POST to a keyword-data route on the admin server.
 *
 * @param string $path             Route path beginning with /api/v1/.
 * @param array  $body             JSON body.
 * @param string $error            OUT. Human-readable reason on failure, '' on success.
 * @param bool   $is_not_connected OUT. True only on a real 401/403.
 * @param int    $timeout          Seconds. 20 by default: the server itself gives the provider
 *                                 8 s, so anything much longer only delays the error.
 * @return array|null Decoded JSON (with success:true) or null on any failure.
 */
function improveseo_keyword_data_request( $path, array $body, &$error = null, &$is_not_connected = null, $timeout = 20 ) {
	$error            = '';
	$is_not_connected = false;

	$api_key   = get_option( 'improveseo_api_key' );
	$site_code = get_option( 'improveseo_site_code' );

	if ( empty( $api_key ) || empty( $site_code ) ) {
		error_log( 'improveseo_keyword_data_request: missing API key or site code; cannot call ' . $path );
		$error = __( 'Your ImproveSEO API Key or Site Code is not saved on this site.', 'improveseo' );
		return null;
	}

	$endpoint = improveseo_keyword_data_server_url() . $path;

	$headers = array(
		'Content-Type' => 'application/json',
		'Accept'       => 'application/json',
		'X-API-Key'    => $api_key,
		'X-Site-Code'  => $site_code,
	);
	if ( function_exists( 'improveseo_connection_domain_header' ) ) {
		$headers['X-Site-Domain'] = improveseo_connection_domain_header();
	}

	$response = wp_remote_post( $endpoint, array(
		'headers' => $headers,
		'body'    => wp_json_encode( $body ),
		'timeout' => (int) $timeout,
	) );

	if ( is_wp_error( $response ) ) {
		error_log( 'improveseo_keyword_data_request: HTTP error for ' . $path . ' — ' . $response->get_error_message() );
		$error = __( 'Could not reach the ImproveSEO server. Check this site\'s internet connection and try again.', 'improveseo' );
		return null;
	}

	$status = (int) wp_remote_retrieve_response_code( $response );
	$raw    = wp_remote_retrieve_body( $response );
	$data   = json_decode( $raw, true );

	if ( 200 !== $status || ! is_array( $data ) || empty( $data['success'] ) ) {
		$code = is_array( $data ) && isset( $data['code'] ) ? (string) $data['code'] : '';
		$msg  = is_array( $data ) && isset( $data['error'] ) && is_string( $data['error'] ) ? $data['error'] : '';
		error_log( 'improveseo_keyword_data_request: failure path=' . $path . ' | status=' . $status . ' | code=' . $code . ' | error=' . ( $msg ? $msg : 'unknown' ) );
		if ( 401 === $status || 403 === $status ) {
			$is_not_connected = true;
		}
		$error = improveseo_keyword_data_error_message( $status, $code, $msg );
		return null;
	}

	return $data;
}

/**
 * Normalise a keyword the way the server does (trim, collapse whitespace, lower-case), so a
 * lookup by keyword in a metrics map always finds the server's echoed key.
 *
 * @param string $keyword Raw keyword.
 * @return string
 */
function improveseo_keyword_data_normalize( $keyword ) {
	$keyword = preg_replace( '/\s+/u', ' ', trim( (string) $keyword ) );
	if ( function_exists( 'mb_strtolower' ) ) {
		return mb_strtolower( $keyword, 'UTF-8' );
	}
	return strtolower( $keyword );
}

/**
 * The allowed shape of one metrics item, enforced on everything that comes back from the server
 * (and, later, on anything posted back from the browser): the contract's fields and nothing else.
 *
 * @param mixed $item Candidate item.
 * @return array|null Clean item or null when it is not an item at all.
 */
function improveseo_keyword_data_clean_item( $item ) {
	if ( ! is_array( $item ) || empty( $item['keyword'] ) || ! is_string( $item['keyword'] ) ) {
		return null;
	}
	$intents = array( 'informational', 'commercial', 'transactional', 'navigational' );

	$volume     = isset( $item['search_volume'] ) && is_numeric( $item['search_volume'] ) ? max( 0, (int) $item['search_volume'] ) : null;
	$difficulty = isset( $item['keyword_difficulty'] ) && is_numeric( $item['keyword_difficulty'] ) ? min( 100, max( 0, (int) $item['keyword_difficulty'] ) ) : null;
	$intent     = isset( $item['intent'] ) && in_array( $item['intent'], $intents, true ) ? $item['intent'] : null;
	$fetched    = isset( $item['fetched_at'] ) && is_string( $item['fetched_at'] ) ? sanitize_text_field( $item['fetched_at'] ) : '';

	return array(
		'keyword'            => improveseo_keyword_data_normalize( sanitize_text_field( $item['keyword'] ) ),
		'search_volume'      => $volume,
		'keyword_difficulty' => $difficulty,
		'intent'             => $intent,
		// Version 1 is national data only; the server says so too, but it is pinned here so a
		// future town-level figure can never be shown under a national label by accident.
		'scope'              => 'national',
		'fetched_at'         => $fetched,
	);
}

/**
 * Search volume, difficulty and intent for a set of keywords (Option A).
 *
 * @param string[] $keywords         Up to any number; sent in chunks of 100 (the contract's max).
 * @param string   $error            OUT. Human-readable reason on failure.
 * @param bool     $is_not_connected OUT. True only on a real 401/403.
 * @return array|null { items: array<string, item> keyed by normalised keyword, cached_count, cost_usd }
 *                    or null when nothing could be fetched. A partial failure on a later chunk
 *                    returns the items already fetched and sets $error.
 */
function improveseo_keyword_metrics( array $keywords, &$error = null, &$is_not_connected = null ) {
	$error            = '';
	$is_not_connected = false;

	$clean = array();
	foreach ( $keywords as $kw ) {
		$kw = improveseo_keyword_data_normalize( sanitize_text_field( (string) $kw ) );
		if ( '' !== $kw ) {
			$clean[ $kw ] = true;
		}
	}
	$clean = array_keys( $clean );
	if ( empty( $clean ) ) {
		$error = __( 'No keywords to look up.', 'improveseo' );
		return null;
	}

	$items        = array();
	$cached_count = 0;
	$cost_usd     = 0.0;
	$got_any      = false;

	foreach ( array_chunk( $clean, 100 ) as $chunk ) {
		$chunk_error = '';
		$chunk_nc    = false;
		$data        = improveseo_keyword_data_request(
			'/api/v1/generate/keywords/metrics',
			array(
				'keywords'      => array_values( $chunk ),
				'location_code' => 2840,
				'language_code' => 'en',
			),
			$chunk_error,
			$chunk_nc
		);
		if ( null === $data ) {
			$error            = $chunk_error;
			$is_not_connected = $chunk_nc;
			break;
		}
		$got_any = true;
		if ( isset( $data['items'] ) && is_array( $data['items'] ) ) {
			foreach ( $data['items'] as $item ) {
				$item = improveseo_keyword_data_clean_item( $item );
				if ( $item ) {
					$items[ $item['keyword'] ] = $item;
				}
			}
		}
		$cached_count += isset( $data['cached_count'] ) ? (int) $data['cached_count'] : 0;
		$cost_usd     += isset( $data['cost_usd'] ) ? (float) $data['cost_usd'] : 0.0;
	}

	if ( ! $got_any ) {
		return null;
	}

	return array(
		'items'        => $items,
		'cached_count' => $cached_count,
		'cost_usd'     => $cost_usd,
	);
}

/**
 * Warm the server's keyword cache for one seed (Option B).
 *
 * Never throws and never blocks anything: a provider problem comes back as status
 * "unavailable" (which is also what the server itself answers for a provider problem).
 *
 * @param string $keyword Seed keyword as typed.
 * @param string $error   OUT. Reason when the call itself failed; '' otherwise.
 * @return array { status: 'ready'|'unavailable', seed: item|null, related_count: int, reason: string }
 */
function improveseo_keyword_prefetch( $keyword, &$error = null ) {
	$error   = '';
	$keyword = sanitize_text_field( (string) $keyword );
	$result  = array(
		'status'        => 'unavailable',
		'seed'          => null,
		'related_count' => 0,
		'reason'        => '',
	);
	if ( '' === trim( $keyword ) ) {
		$result['reason'] = 'empty';
		return $result;
	}

	$nc   = false;
	$data = improveseo_keyword_data_request(
		'/api/v1/content-v3/keyword-data/prefetch',
		array(
			'keyword'       => $keyword,
			'location_code' => 2840,
			'language_code' => 'en',
		),
		$error,
		$nc,
		15
	);
	if ( null === $data ) {
		$result['reason'] = $nc ? 'not_connected' : 'request_failed';
		if ( function_exists( 'my_plugin_log' ) ) {
			my_plugin_log( 'keyword prefetch failed for "' . $keyword . '": ' . $error );
		}
		return $result;
	}

	$result['status']        = ( isset( $data['status'] ) && 'ready' === $data['status'] ) ? 'ready' : 'unavailable';
	$result['seed']          = isset( $data['seed'] ) ? improveseo_keyword_data_clean_item( $data['seed'] ) : null;
	$result['related_count'] = isset( $data['related_count'] ) ? (int) $data['related_count'] : 0;
	$result['reason']        = isset( $data['reason'] ) && is_string( $data['reason'] ) ? sanitize_key( $data['reason'] ) : '';

	if ( 'ready' !== $result['status'] && function_exists( 'my_plugin_log' ) ) {
		my_plugin_log( 'keyword prefetch unavailable for "' . $keyword . '"' . ( $result['reason'] ? ' (' . $result['reason'] . ')' : '' ) );
	}

	return $result;
}

/* ======================================================================================
 * Phase 2: keyword tool metrics — storage column, validation, AJAX and page assets.
 * ====================================================================================*/

/**
 * Make sure wp_improveseo_lists has the nullable `metrics` column (JSON, keyed by keyword).
 *
 * Same self-healing shape as improveseo_ensure_bulk_meta_columns() (improveseo.php): dbDelta is
 * unreliable at adding a column to an existing table, so this checks SHOW COLUMNS once, adds the
 * column with ALTER TABLE, and remembers the result in an option so it costs nothing afterwards.
 * Called from the save handler (before the INSERT) and from improveseo_install().
 *
 * @return bool True when the column exists (or was just added).
 */
function improveseo_ensure_lists_metrics_column() {
	global $wpdb;

	if ( get_option( 'improveseo_lists_metrics_col' ) === 'ready' ) {
		return true;
	}

	$table = $wpdb->prefix . 'improveseo_lists';

	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
		return false;
	}

	$cols = $wpdb->get_col( "SHOW COLUMNS FROM `$table`" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter -- fixed query text; the only interpolation is the table name, which cannot be bound
	if ( ! is_array( $cols ) ) {
		return false;
	}

	if ( ! in_array( 'metrics', $cols, true ) ) {
		$wpdb->query( "ALTER TABLE `$table` ADD COLUMN `metrics` LONGTEXT NULL DEFAULT NULL" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter -- schema statement; the table name is an identifier and cannot be bound
		if ( function_exists( 'my_plugin_log' ) ) {
			my_plugin_log( 'improveseo_ensure_lists_metrics_column: added metrics' . ( $wpdb->last_error ? ' | MySQL Error: ' . $wpdb->last_error : ' | OK' ) );
		}
		$cols = $wpdb->get_col( "SHOW COLUMNS FROM `$table`" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter -- fixed query text; the only interpolation is the table name, which cannot be bound
	}

	$ready = is_array( $cols ) && in_array( 'metrics', $cols, true );
	if ( $ready ) {
		update_option( 'improveseo_lists_metrics_col', 'ready', false );
	}
	return $ready;
}

/**
 * Validate a metrics payload (JSON text or decoded array) down to clean contract items.
 *
 * Accepts either a list of items or a map keyed by keyword; always returns a map keyed by the
 * normalised keyword. Anything that is not an item is dropped. Used for what the browser posts
 * back on Save (it originally came from the server, but it has been through the browser since).
 *
 * @param string|array $raw JSON string or array.
 * @return array<string, array>
 */
function improveseo_keyword_data_validate_metrics( $raw ) {
	if ( is_string( $raw ) ) {
		$raw = trim( wp_unslash( $raw ) );
		if ( '' === $raw ) {
			return array();
		}
		$raw = json_decode( $raw, true );
	}
	if ( ! is_array( $raw ) ) {
		return array();
	}
	$out = array();
	foreach ( $raw as $item ) {
		$item = improveseo_keyword_data_clean_item( $item );
		if ( $item ) {
			$out[ $item['keyword'] ] = $item;
		}
	}
	return $out;
}

/**
 * Metrics stored with a saved keyword list.
 *
 * @param int $list_id wp_improveseo_lists.id.
 * @return array<string, array> Map keyed by normalised keyword; empty when the list has none.
 */
function improveseo_lists_metrics_for( $list_id ) {
	global $wpdb;
	$list_id = absint( $list_id );
	if ( ! $list_id || ! improveseo_ensure_lists_metrics_column() ) {
		return array();
	}
	$raw = $wpdb->get_var( $wpdb->prepare( "SELECT metrics FROM {$wpdb->prefix}improveseo_lists WHERE id = %d", $list_id ) );
	if ( empty( $raw ) ) {
		return array();
	}
	return improveseo_keyword_data_validate_metrics( $raw );
}

/**
 * Format one metrics item as the one-line summary both wizards show under the keyword.
 *
 * @param array|null $item Clean item.
 * @return string '' when there is nothing to show.
 */
function improveseo_keyword_data_summary_line( $item ) {
	if ( ! is_array( $item ) ) {
		return '';
	}
	$parts = array();
	if ( null !== $item['search_volume'] ) {
		/* translators: %s: formatted number */
		$parts[] = sprintf( __( 'US monthly searches: %s', 'improveseo' ), number_format_i18n( (int) $item['search_volume'] ) );
	}
	if ( null !== $item['keyword_difficulty'] ) {
		/* translators: %d: keyword difficulty 0-100 */
		$parts[] = sprintf( __( 'Difficulty: %d', 'improveseo' ), (int) $item['keyword_difficulty'] );
	}
	if ( ! empty( $item['intent'] ) ) {
		/* translators: %s: search intent label */
		$parts[] = sprintf( __( 'Intent: %s', 'improveseo' ), $item['intent'] );
	}
	return implode( ' · ', $parts );
}

/**
 * AJAX: metrics for the keywords the Keyword Generator just produced.
 *
 * Browser → WordPress → server, like every other generation call that needs the X-Site-Domain
 * header. Same guards as the rest of the plugin's admin AJAX (modules/ajax_keyword_refresh.php);
 * manage_options because that is the capability the Keyword Generator page itself requires
 * (includes/menus.php).
 */
add_action( 'wp_ajax_improveseo_keyword_metrics', 'improveseo_ajax_keyword_metrics' );
function improveseo_ajax_keyword_metrics() {
	check_ajax_referer( 'improveseo_ajax', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'improveseo' ) ), 403 );
	}

	$keywords = array();
	if ( isset( $_POST['keywords'] ) ) {
		$posted = wp_unslash( $_POST['keywords'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- each entry is sanitized below
		if ( is_string( $posted ) ) {
			$posted = preg_split( '/\r\n|\r|\n/', $posted );
		}
		if ( is_array( $posted ) ) {
			foreach ( $posted as $kw ) {
				$kw = sanitize_text_field( (string) $kw );
				if ( '' !== trim( $kw ) ) {
					$keywords[] = $kw;
				}
			}
		}
	}
	if ( empty( $keywords ) ) {
		wp_send_json_error( array( 'message' => __( 'No keywords to look up.', 'improveseo' ) ), 400 );
	}
	// The tool itself never produces more than 50 (the server clamps count to 1-50); a hard cap
	// here keeps a hand-crafted request from fanning out into many provider calls.
	$keywords = array_slice( $keywords, 0, 200 );

	$error  = '';
	$nc     = false;
	$result = improveseo_keyword_metrics( $keywords, $error, $nc );
	if ( null === $result ) {
		wp_send_json_error( array(
			'message'       => $error,
			'not_connected' => $nc,
		) );
	}
	wp_send_json_success( array(
		'items'        => array_values( $result['items'] ),
		'cached_count' => $result['cached_count'],
		// Partial failure on a later chunk: items so far plus the reason, so the UI can say so.
		'warning'      => $error,
	) );
}

/**
 * Config for assets/js/keyword-metrics.js on the Keyword Generator page.
 *
 * Priority 20, after the handles are registered (includes/assets.php) and after pluggable.php
 * is loaded so wp_create_nonce() is safe — the same reason improveseo_localize_ajax_nonces()
 * (improveseo.php) is hooked this way.
 */
add_action( 'admin_enqueue_scripts', 'improveseo_keyword_metrics_localize', 20 );
function improveseo_keyword_metrics_localize() {
	if ( ! wp_script_is( 'improveseo-keyword-metrics', 'enqueued' ) ) {
		return;
	}
	wp_localize_script( 'improveseo-keyword-metrics', 'improveseoKeywordMetrics', array(
		'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
		'nonce'        => wp_create_nonce( 'improveseo_ajax' ),
		// Raw rather than esc_url(): assigned to hrefs from JS, where an entity-encoded & breaks.
		'writePostUrl' => esc_url_raw( admin_url( 'admin.php?page=improveseo_posting&action=create_post_single' ) ),
		'bulkUrl'      => esc_url_raw( admin_url( 'admin.php?page=improveseo_posting&action=create_post_bulk' ) ),
		'i18n'         => array(
			'keyword'      => __( 'Keyword', 'improveseo' ),
			'volume'       => __( 'Monthly searches (US)', 'improveseo' ),
			'difficulty'   => __( 'Difficulty', 'improveseo' ),
			'intent'       => __( 'Intent', 'improveseo' ),
			'actions'      => __( 'Actions', 'improveseo' ),
			'writePost'    => __( 'Write a post', 'improveseo' ),
			'bulkSelected' => __( 'Create bulk project', 'improveseo' ),
			'loading'      => __( 'Fetching search volume, difficulty and intent…', 'improveseo' ),
			'empty'        => __( 'No keywords yet. Generate keywords to see their search data.', 'improveseo' ),
			'noData'       => __( 'No search data came back for these keywords.', 'improveseo' ),
			'failed'       => __( 'Search data could not be loaded.', 'improveseo' ),
			'retry'        => __( 'Retry', 'improveseo' ),
			'selectAll'    => __( 'Select all', 'improveseo' ),
			'selected'     => __( 'selected', 'improveseo' ),
			'saving'       => __( 'Saving…', 'improveseo' ),
			'saveFailed'   => __( 'The keyword list could not be saved. Please try again.', 'improveseo' ),
			'selection'    => __( 'selection', 'improveseo' ),
			'sortHint'     => __( 'Click to sort', 'improveseo' ),
			'national'     => __( 'National (US) figures. Town-level data is not available yet.', 'improveseo' ),
		),
	) );
}
