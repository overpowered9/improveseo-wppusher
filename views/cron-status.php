<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * ImproveSEO — Cron Diagnostic Page
 *
 * Renders WP-Cron health for `cronjob_request_event` and offers
 * "Force Re-Schedule" / "Run Now" actions.
 */

if (!current_user_can('manage_options')) {
	wp_die('Unauthorized');
}

$hook       = 'cronjob_request_event';
$notice     = '';
$notice_cls = 'notice-success';

// Handle POST actions
if (isset($_POST['improveseo_cron_action'])) {
	check_admin_referer('improveseo_cron_status_action');
	$action = sanitize_text_field($_POST['improveseo_cron_action']);

	if ($action === 'reschedule') {
		wp_clear_scheduled_hook($hook);
		if (function_exists('improveseo_ensure_cron_scheduled')) {
			improveseo_ensure_cron_scheduled();
		}
		$notice = 'Cron event cleared and re-scheduled.';
	} elseif ($action === 'run_now') {
		ob_start();
		do_action($hook);
		ob_end_clean();
		$notice = 'cronjob_request_event fired manually. Check debug.log for "=== CRON JOB STARTED ===".';
	}
}

$next_ts          = wp_next_scheduled($hook);
$schedules        = wp_get_schedules();
$two_min          = isset($schedules['two_minutes']) ? $schedules['two_minutes'] : null;
$wp_cron_disabled = defined('DISABLE_WP_CRON') && DISABLE_WP_CRON;
$log_file         = WP_CONTENT_DIR . '/debug.log';
$log_lines        = array();

if (file_exists($log_file) && is_readable($log_file)) {
	$fp        = @fopen($log_file, 'r');
	$buffer    = '';
	$chunk     = 65536;
	if ($fp) {
		fseek($fp, 0, SEEK_END);
		$size = ftell($fp);
		$read = min($chunk, $size);
		fseek($fp, -$read, SEEK_END);
		$buffer = fread($fp, $read);
		fclose($fp);
		$all = explode("\n", $buffer);
		foreach ($all as $line) {
			if (preg_match('/IMPROVESEO CRON|=== CRON JOB/', $line)) {
				$log_lines[] = $line;
			}
		}
		$log_lines = array_slice($log_lines, -20);
	}
}
?>

<div class="wrap">
	<h1>ImproveSEO — Cron Status</h1>

	<?php if ($notice): ?>
		<div class="notice <?php echo esc_attr($notice_cls); ?> is-dismissible">
			<p><?php echo esc_html($notice); ?></p>
		</div>
	<?php endif; ?>

	<h2>Schedule</h2>
	<table class="widefat striped" style="max-width: 900px;">
		<tbody>
			<tr>
				<th style="width: 280px;">Hook</th>
				<td><code><?php echo esc_html($hook); ?></code></td>
			</tr>
			<tr>
				<th>Next scheduled run</th>
				<td>
					<?php if ($next_ts): ?>
						<strong style="color: #2e7d32;">
							<?php echo esc_html(gmdate('Y-m-d H:i:s', $next_ts)); ?>
						</strong>
						(in <?php echo esc_html(human_time_diff(time(), $next_ts)); ?>)
					<?php else: ?>
						<strong style="color: #c62828;">NOT SCHEDULED</strong>
						— click "Force Re-Schedule" below.
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th><code>two_minutes</code> interval registered?</th>
				<td>
					<?php if ($two_min): ?>
						<span style="color: #2e7d32;">Yes</span>
						— interval = <?php echo esc_html((int) $two_min['interval']); ?>s,
						display = "<?php echo esc_html($two_min['display']); ?>"
					<?php else: ?>
						<strong style="color: #c62828;">NO</strong>
						— the <code>cron_schedules</code> filter did not register <code>two_minutes</code>.
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th><code>DISABLE_WP_CRON</code></th>
				<td>
					<?php if ($wp_cron_disabled): ?>
						<strong style="color: #c62828;">TRUE</strong>
						— WP-Cron is disabled in <code>wp-config.php</code>.
						A real system cron must hit <code>wp-cron.php</code> for events to fire,
						regardless of plugin code.
					<?php else: ?>
						<span style="color: #2e7d32;">false</span>
					<?php endif; ?>
				</td>
			</tr>
		</tbody>
	</table>

	<h2 style="margin-top: 24px;">Actions</h2>
	<form method="post" style="display:inline-block; margin-right: 8px;">
		<?php wp_nonce_field('improveseo_cron_status_action'); ?>
		<input type="hidden" name="improveseo_cron_action" value="reschedule" />
		<button type="submit" class="button button-secondary">Force Re-Schedule</button>
	</form>
	<form method="post" style="display:inline-block;">
		<?php wp_nonce_field('improveseo_cron_status_action'); ?>
		<input type="hidden" name="improveseo_cron_action" value="run_now" />
		<button type="submit" class="button button-primary">Run Now</button>
	</form>

	<h2 style="margin-top: 24px;">Recent cron log entries</h2>
	<p style="color: #666;">Last 20 lines from <code><?php echo esc_html($log_file); ?></code> matching <code>IMPROVESEO CRON</code> or <code>=== CRON JOB</code>.</p>
	<?php if (empty($log_lines)): ?>
		<p><em>No matching entries found. If you just clicked "Run Now", refresh the page — log writes may take a moment.</em></p>
	<?php else: ?>
		<pre style="background:#1e1e1e; color:#dcdcdc; padding:12px; max-height:400px; overflow:auto; font-size:12px; line-height:1.5;"><?php
			foreach ($log_lines as $line) {
				echo esc_html($line) . "\n";
			}
		?></pre>
	<?php endif; ?>
</div>
