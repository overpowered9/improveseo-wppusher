<?php
/**
 * Telling ImproveSEO whether this site is still connected.
 *
 * ── THE PROBLEM ─────────────────────────────────────────────────────────────
 * The CMS shows every website as "Active" or "Not Connected", and the customer
 * reads that as "can I use the plugin here?". Until now the plugin gave it no
 * way to answer:
 *
 *   • The plugin contacted the server ONLY on "Test Server Connection" and
 *     during content generation. A site that was connected but idle looked
 *     exactly like a site that had been disconnected — silence either way.
 *   • Clearing the API Key or Site Code on the settings page was completely
 *     invisible: options.php writes the option and nothing else happens, so
 *     the CMS went on showing a site the customer had just unhooked as Active.
 *   • Deactivating the plugin was invisible too.
 *
 * ── WHAT THIS ADDS ──────────────────────────────────────────────────────────
 * Three signals, in order of how much work they do:
 *
 *   1. ON SAVE — the moment credentials are added, changed, or cleared, tell
 *      the server. This is what makes the CMS correct IMMEDIATELY in the two
 *      cases a customer actually reports, because they are the two a customer
 *      performs deliberately and then goes to check.
 *
 *   2. ON DEACTIVATION — same report, sent while the credentials are still
 *      readable.
 *
 *   3. HOURLY HEARTBEAT — an unprompted ping proving the site is still there
 *      and still holds working credentials. This is what licenses the CMS to
 *      treat silence as a disconnection: it may only do that for sites that
 *      have shown they would otherwise have spoken. Sites running an older
 *      build never send it and keep the old, never-expiring behaviour.
 *
 * The heartbeat is the backstop, not the mechanism. It covers what cannot
 * report itself — the plugin folder deleted, the site taken offline, the
 * credentials changed straight in the database.
 */

if (!defined('ABSPATH')) exit;

/**
 * The admin server.
 *
 * Hardcoded, and the misspelling is genuine — that IS the Render hostname. It
 * is written out at a dozen other call sites in this plugin; this file follows
 * the same convention rather than introducing a constant only it would use.
 */
define('IMPROVESEO_CONNECTION_SERVER', 'https://imporve-seo-admin-server-nzbm.onrender.com');

/**
 * Request timeout, in seconds.
 *
 * NOT the 10 the settings panel's Test Connection uses. The server is on
 * Render's free tier and cold-starts, which routinely takes longer than 10
 * seconds — the bulk-post path in this plugin already uses 30 for exactly that
 * reason. A heartbeat that gave up at 10 would report healthy sites as silent,
 * which is the failure this whole change exists to prevent.
 */
define('IMPROVESEO_CONNECTION_TIMEOUT', 30);

define('IMPROVESEO_HEARTBEAT_HOOK', 'improveseo_heartbeat_event');

/** The two options that together make up a connection. */
function improveseo_connection_option_names() {
	return array('improveseo_api_key', 'improveseo_site_code');
}

/** Current credentials, trimmed. Either may be an empty string. */
function improveseo_connection_credentials() {
	return array(
		'api_key'   => trim((string) get_option('improveseo_api_key', '')),
		'site_code' => trim((string) get_option('improveseo_site_code', '')),
	);
}

/** Quiet by default; this is bookkeeping, not something to fill debug.log with. */
function improveseo_connection_log($message) {
	if (defined('WP_DEBUG') && WP_DEBUG && function_exists('my_plugin_log')) {
		my_plugin_log('IMPROVESEO CONNECTION: ' . $message);
	}
}


/* ── 1 & 2. Reporting a change of credentials ───────────────────────────── */

/**
 * The values the two options held BEFORE anything in this request changed them.
 *
 * Recorded per-option, first change only. Both are saved by the same form
 * submission, so by the time the second one fires the first has already been
 * overwritten — reading them at report time would give the new value for one
 * and the old for the other, and the disconnect report needs BOTH old values to
 * authenticate with.
 */
$improveseo_connection_before = array();

/** True once a report has been queued, so one form save sends one request. */
$improveseo_connection_queued = false;

function improveseo_connection_remember($option, $old_value) {
	global $improveseo_connection_before, $improveseo_connection_queued;

	if (!array_key_exists($option, $improveseo_connection_before)) {
		$improveseo_connection_before[$option] = trim((string) $old_value);
	}

	// Deferred to shutdown rather than sent here: this fires once per option,
	// and both options change in a single save. Reporting from the hook would
	// send two requests describing a half-applied state — the first one seeing
	// a new API key next to the OLD site code.
	if (!$improveseo_connection_queued) {
		$improveseo_connection_queued = true;
		add_action('shutdown', 'improveseo_connection_report_change', 5);
	}
}

/** update_option_{$option}: ($old_value, $value, $option) */
function improveseo_connection_on_update($old_value, $value, $option) {
	improveseo_connection_remember($option, $old_value);
}

/** add_option_{$option}: ($option, $value). No previous value — it did not exist. */
function improveseo_connection_on_add($option, $value) {
	improveseo_connection_remember($option, '');
}

foreach (improveseo_connection_option_names() as $improveseo_connection_option) {
	// Both hooks matter: update_option_* fires when an option changes, add_option_*
	// when it is written for the very first time. A brand-new install saving
	// credentials for the first time only ever fires the latter — which is
	// precisely the "I connected it by hand and it still says Not Connected"
	// case, so missing it would leave the main bug in place.
	add_action('update_option_' . $improveseo_connection_option, 'improveseo_connection_on_update', 10, 3);
	add_action('add_option_' . $improveseo_connection_option, 'improveseo_connection_on_add', 10, 2);
}
unset($improveseo_connection_option);

/**
 * Decide what just happened and tell the server.
 *
 * Runs on `shutdown`, after options.php has written everything and redirected.
 * WordPress registers its shutdown handler with register_shutdown_function, so
 * this still runs even though options.php ends in exit().
 */
function improveseo_connection_report_change() {
	global $improveseo_connection_before;

	$now = improveseo_connection_credentials();

	// For an option that did not change, "before" is what it still is.
	$was_key  = array_key_exists('improveseo_api_key', $improveseo_connection_before)
		? $improveseo_connection_before['improveseo_api_key']
		: $now['api_key'];
	$was_code = array_key_exists('improveseo_site_code', $improveseo_connection_before)
		? $improveseo_connection_before['improveseo_site_code']
		: $now['site_code'];

	if ($now['api_key'] !== '' && $now['site_code'] !== '') {
		// Connected, or reconnected with different credentials. Ping as the new
		// site; the server stamps it and the CMS shows Active without waiting
		// for this site's first article.
		improveseo_connection_ping($now['api_key'], $now['site_code'], false);

		// Make sure the heartbeat is running for the newly connected site.
		improveseo_connection_ensure_heartbeat();
		return;
	}

	if ($was_key !== '' && $was_code !== '') {
		// One or both credentials have just been removed. This is the ONLY
		// moment the old pair is still available to authenticate with — a
		// second from now the options are empty and the site has no way to
		// identify itself at all.
		improveseo_connection_report_disconnect($was_key, $was_code);
		return;
	}

	// Never connected and still not connected. Nothing to report.
}


/* ── 3. The hourly heartbeat ────────────────────────────────────────────── */

/**
 * Schedule the heartbeat if it is not already scheduled.
 *
 * Mirrors improveseo_ensure_cron_scheduled() in the main plugin file, including
 * the self-heal on `init`: WP-Cron events live in wp_options and do go missing
 * (a restored database snapshot, another plugin clearing schedules), and a
 * heartbeat that has silently stopped is worse than none — the CMS would report
 * a healthy site as disconnected.
 *
 * 'hourly' is a WordPress built-in, so no cron_schedules filter is needed.
 */
function improveseo_connection_ensure_heartbeat() {
	if (wp_next_scheduled(IMPROVESEO_HEARTBEAT_HOOK)) {
		return;
	}
	wp_schedule_event(time(), 'hourly', IMPROVESEO_HEARTBEAT_HOOK);
	improveseo_connection_log('heartbeat scheduled');
}
add_action('init', 'improveseo_connection_ensure_heartbeat');

function improveseo_connection_heartbeat() {
	$creds = improveseo_connection_credentials();

	// Nothing to prove. An unconfigured site has no identity to check in with,
	// and calling without credentials would only earn a 401.
	if ($creds['api_key'] === '' || $creds['site_code'] === '') {
		return;
	}

	improveseo_connection_ping($creds['api_key'], $creds['site_code'], true);
}
add_action(IMPROVESEO_HEARTBEAT_HOOK, 'improveseo_connection_heartbeat');


/* ── The two requests ───────────────────────────────────────────────────── */

/**
 * "This site is here and its credentials work."
 *
 * Hits the same endpoint, with the same headers, as the settings panel's Test
 * Server Connection button — that is the contract the server already
 * authenticates, and reusing it means there is one definition of "connected"
 * rather than two that can drift.
 *
 * @param bool $is_heartbeat Adds x-plugin-heartbeat, which tells the server this
 *                           was UNPROMPTED. That distinction is the whole point:
 *                           it is what lets the CMS start treating this site's
 *                           silence as meaningful. A ping sent because the user
 *                           just pressed Save proves the credentials work but
 *                           proves nothing about whether cron runs here, so it
 *                           deliberately does NOT claim the capability.
 */
function improveseo_connection_ping($api_key, $site_code, $is_heartbeat) {
	$headers = array(
		'x-api-key'    => $api_key,
		'x-site-code'  => $site_code,
		'Content-Type' => 'application/json',
	);
	if ($is_heartbeat) {
		$headers['x-plugin-heartbeat'] = '1';
	}

	$args = array(
		'timeout' => IMPROVESEO_CONNECTION_TIMEOUT,
		'headers' => $headers,
	);

	// A save-time ping runs during a page request the admin is waiting on, so
	// it is fired and forgotten: a cold-starting server must not hold the
	// settings page for half a minute. Nothing here reads the response — the
	// settings panel already has a Test Server Connection button for the
	// customer who wants an answer. The heartbeat has nobody waiting, so it
	// blocks and can be logged.
	if (!$is_heartbeat) {
		$args['blocking'] = false;
		$args['timeout']  = 1;
	}

	$response = wp_remote_get(
		IMPROVESEO_CONNECTION_SERVER . '/api/v1/users/status',
		$args
	);

	if (!$is_heartbeat) {
		return;
	}

	if (is_wp_error($response)) {
		improveseo_connection_log('heartbeat failed: ' . $response->get_error_message());
		return;
	}

	$code = wp_remote_retrieve_response_code($response);
	if ($code !== 200) {
		// 401/403 means the credentials no longer work — the key was rotated in
		// the CMS, or the site was deleted there. Nothing to do about it from
		// here: we stop being counted as connected because we stop checking in,
		// which is the correct outcome.
		improveseo_connection_log('heartbeat rejected with HTTP ' . $code);
	}
}

/**
 * "Stop counting this site as connected."
 *
 * Sent with the credentials being retired, which is what authenticates it —
 * hence the insistence above on capturing the OLD values. The server clears the
 * site's connection timestamps and the CMS shows Not Connected on the next load.
 *
 * Fire-and-forget, for the same reason as the save-time ping: this runs while
 * the admin waits on a settings save or a plugin deactivation. If the request
 * is lost, the site falls back to going stale on its own once the heartbeat
 * stops — later than it should, but never wrong.
 */
function improveseo_connection_report_disconnect($api_key, $site_code) {
	if ($api_key === '' || $site_code === '') {
		return;
	}

	improveseo_connection_log('reporting disconnect for site code ' . $site_code);

	wp_remote_post(
		IMPROVESEO_CONNECTION_SERVER . '/api/v1/users/disconnect',
		array(
			'blocking' => false,
			'timeout'  => 1,
			'headers'  => array(
				'x-api-key'    => $api_key,
				'x-site-code'  => $site_code,
				'Content-Type' => 'application/json',
			),
			'body' => '{}',
		)
	);
}


/* ── Deactivation ───────────────────────────────────────────────────────── */

/**
 * Report the disconnection and stop the heartbeat.
 *
 * Registered from the main plugin file, because register_deactivation_hook()
 * keys on the plugin's entry file and __FILE__ here is an include.
 *
 * Credentials are deliberately NOT deleted: deactivating a plugin is routinely
 * temporary, and wiping the API Key would make the customer re-enter it to
 * reactivate. The server is told to stop counting the site as connected, which
 * is true; reactivating sends a heartbeat and undoes it.
 *
 * NOTE this only clears the heartbeat's own hook. improveseo_uninstall() clears
 * 'improveseo_parse_tasks_hook', which has never actually been scheduled — the
 * wp_schedule_event call for it is commented out in installer.php, while the
 * cron that IS scheduled ('cronjob_request_event') is left running. That is a
 * pre-existing bug and is left alone here rather than fixed in passing.
 */
function improveseo_connection_on_deactivate() {
	$creds = improveseo_connection_credentials();
	improveseo_connection_report_disconnect($creds['api_key'], $creds['site_code']);

	wp_clear_scheduled_hook(IMPROVESEO_HEARTBEAT_HOOK);
}
