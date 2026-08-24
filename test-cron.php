<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Manual Cron Test Script
 * 
 * This script manually triggers the cron job to test if it's working.
 * 
 * Usage:
 * 1. Upload this file to your WordPress root directory
 * 2. Visit: http://yoursite.com/test-cron.php
 * 3. Check wp-content/debug.log for output
 * 
 * IMPORTANT: Delete this file after testing for security!
 */

// Load WordPress
require_once('wp-load.php');

echo "<h1>ImproveSEO Cron Job Test</h1>";
echo "<p>Testing cron job execution...</p>";

// Check if cron is scheduled
$next_scheduled = wp_next_scheduled('cronjob_request_event');
if ($next_scheduled) {
    echo "<p>✅ Cron is scheduled. Next run: " . gmdate('Y-m-d H:i:s', $next_scheduled) . "</p>";
} else {
    echo "<p>❌ Cron is NOT scheduled!</p>";
    echo "<p>Attempting to schedule it now...</p>";
    
    // Try to schedule it
    if (!wp_next_scheduled('cronjob_request_event')) {
        wp_schedule_event(time(), 'every_minute', 'cronjob_request_event');
        echo "<p>✅ Cron scheduled successfully!</p>";
    }
}

// Manually trigger the cron job
echo "<h2>Manually Triggering Cron Job...</h2>";

if (function_exists('CronjobRequest')) {
    echo "<p>✅ CronjobRequest function exists</p>";
    
    echo "<pre>";
    ob_start();
    CronjobRequest();
    $output = ob_get_clean();
    echo $output;
    echo "</pre>";
    
    echo "<p>✅ Cron job executed. Check wp-content/debug.log for details.</p>";
} else {
    echo "<p>❌ ERROR: CronjobRequest function not found!</p>";
    echo "<p>This means the bulk_AI_post_function.php file is not loaded properly.</p>";
}

// Check for pending tasks
global $wpdb;
$pending_count = $wpdb->get_var("SELECT COUNT(*) FROM `" . $wpdb->prefix . "improveseo_bulktasksdetails` WHERE `status`='Pending'");
echo "<h2>Database Status</h2>";
echo "<p>Pending tasks in queue: <strong>" . $pending_count . "</strong></p>";

if ($pending_count > 0) {
    echo "<p>✅ There are tasks to process</p>";
    
    // Show first pending task
    $first_task = $wpdb->get_row("SELECT * FROM `" . $wpdb->prefix . "improveseo_bulktasksdetails` WHERE `status`='Pending' ORDER BY `id` ASC LIMIT 1");
    if ($first_task) {
        echo "<h3>Next Task to Process:</h3>";
        echo "<pre>";
        print_r($first_task);
        echo "</pre>";
    }
} else {
    echo "<p>⚠️ No pending tasks found. Add some bulk posts to test.</p>";
}

// Check if debug.log exists
$debug_log = WP_CONTENT_DIR . '/debug.log';
if (file_exists($debug_log)) {
    echo "<h2>Recent Debug Log Entries (Last 20 lines)</h2>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd; max-height: 300px; overflow-y: scroll;'>";
    $log_lines = file($debug_log);
    $last_lines = array_slice($log_lines, -20);
    echo htmlspecialchars(implode('', $last_lines));
    echo "</pre>";
} else {
    echo "<p>❌ Debug log file not found at: " . $debug_log . "</p>";
}

echo "<hr>";
echo "<p><strong>IMPORTANT:</strong> Delete this file (test-cron.php) after testing for security reasons!</p>";
?>
