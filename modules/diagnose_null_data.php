<?php
/**
 * Database Diagnostic Tool - Check for NULL title/image data
 * 
 * This script scans the improveseo_bulktasksdetails table for records
 * with NULL or empty ai_title, ai_image, or ai_content fields and
 * provides a detailed report.
 * 
 * Usage: Run this from WordPress admin or wp-cli
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    die('Direct access not permitted');
}

function improveseo_diagnose_null_data() {
    global $wpdb;
    
    echo "<h2>ImproveSEO Database Diagnostic Report</h2>\n";
    echo "<p>Generated: " . gmdate('Y-m-d H:i:s') . "</p>\n";
    echo "<hr>\n";
    
    // Get total task count
    $total_tasks = $wpdb->get_var(
        "SELECT COUNT(*) FROM `{$wpdb->prefix}improveseo_bulktasksdetails`"
    );
    
    echo "<h3>Overview</h3>\n";
    echo "<p>Total tasks in database: <strong>$total_tasks</strong></p>\n";
    
    // Check for NULL ai_title
    $null_titles = $wpdb->get_results(
        "SELECT id, bulktask_id, keyword_name, status, state, created_at 
         FROM `{$wpdb->prefix}improveseo_bulktasksdetails` 
         WHERE ai_title IS NULL OR ai_title = ''
         ORDER BY id DESC
         LIMIT 50"
    );
    
    echo "<h3>Tasks with NULL/Empty Title</h3>\n";
    if (empty($null_titles)) {
        echo "<p style='color: green;'>✅ No tasks found with NULL/empty title</p>\n";
    } else {
        echo "<p style='color: red;'>❌ Found " . count($null_titles) . " tasks with NULL/empty title:</p>\n";
        echo "<table border='1' cellpadding='5' cellspacing='0'>\n";
        echo "<tr><th>ID</th><th>Bulk Task ID</th><th>Keyword</th><th>Status</th><th>State</th><th>Created</th></tr>\n";
        foreach ($null_titles as $task) {
            echo "<tr>";
            echo "<td>" . esc_html($task->id) . "</td>";
            echo "<td>" . esc_html($task->bulktask_id) . "</td>";
            echo "<td>" . esc_html($task->keyword_name) . "</td>";
            echo "<td>" . esc_html($task->status) . "</td>";
            echo "<td>" . esc_html($task->state) . "</td>";
            echo "<td>" . esc_html($task->created_at) . "</td>";
            echo "</tr>\n";
        }
        echo "</table>\n";
    }
    
    // Check for NULL ai_image
    $null_images = $wpdb->get_results(
        "SELECT id, bulktask_id, keyword_name, status, state, aiImage, created_at 
         FROM `{$wpdb->prefix}improveseo_bulktasksdetails` 
         WHERE (ai_image IS NULL OR ai_image = '') AND aiImage = 'AI_image_one'
         ORDER BY id DESC
         LIMIT 50"
    );
    
    echo "<h3>Tasks with NULL/Empty Image (when image generation was requested)</h3>\n";
    if (empty($null_images)) {
        echo "<p style='color: green;'>✅ No tasks found with NULL/empty image where generation was requested</p>\n";
    } else {
        echo "<p style='color: red;'>❌ Found " . count($null_images) . " tasks with NULL/empty image:</p>\n";
        echo "<table border='1' cellpadding='5' cellspacing='0'>\n";
        echo "<tr><th>ID</th><th>Bulk Task ID</th><th>Keyword</th><th>Status</th><th>State</th><th>Image Method</th><th>Created</th></tr>\n";
        foreach ($null_images as $task) {
            echo "<tr>";
            echo "<td>" . esc_html($task->id) . "</td>";
            echo "<td>" . esc_html($task->bulktask_id) . "</td>";
            echo "<td>" . esc_html($task->keyword_name) . "</td>";
            echo "<td>" . esc_html($task->status) . "</td>";
            echo "<td>" . esc_html($task->state) . "</td>";
            echo "<td>" . esc_html($task->aiImage) . "</td>";
            echo "<td>" . esc_html($task->created_at) . "</td>";
            echo "</tr>\n";
        }
        echo "</table>\n";
    }
    
    // Check for NULL ai_content
    $null_content = $wpdb->get_results(
        "SELECT id, bulktask_id, keyword_name, status, state, created_at 
         FROM `{$wpdb->prefix}improveseo_bulktasksdetails` 
         WHERE ai_content IS NULL OR ai_content = ''
         ORDER BY id DESC
         LIMIT 50"
    );
    
    echo "<h3>Tasks with NULL/Empty Content</h3>\n";
    if (empty($null_content)) {
        echo "<p style='color: green;'>✅ No tasks found with NULL/empty content</p>\n";
    } else {
        echo "<p style='color: red;'>❌ Found " . count($null_content) . " tasks with NULL/empty content:</p>\n";
        echo "<table border='1' cellpadding='5' cellspacing='0'>\n";
        echo "<tr><th>ID</th><th>Bulk Task ID</th><th>Keyword</th><th>Status</th><th>State</th><th>Created</th></tr>\n";
        foreach ($null_content as $task) {
            echo "<tr>";
            echo "<td>" . esc_html($task->id) . "</td>";
            echo "<td>" . esc_html($task->bulktask_id) . "</td>";
            echo "<td>" . esc_html($task->keyword_name) . "</td>";
            echo "<td>" . esc_html($task->status) . "</td>";
            echo "<td>" . esc_html($task->state) . "</td>";
            echo "<td>" . esc_html($task->created_at) . "</td>";
            echo "</tr>\n";
        }
        echo "</table>\n";
    }
    
    // Check for tasks marked Done but missing data
    $done_incomplete = $wpdb->get_results(
        "SELECT id, bulktask_id, keyword_name, 
                CASE WHEN ai_title IS NULL OR ai_title = '' THEN 'YES' ELSE 'NO' END as missing_title,
                CASE WHEN ai_image IS NULL OR ai_image = '' THEN 'YES' ELSE 'NO' END as missing_image,
                CASE WHEN ai_content IS NULL OR ai_content = '' THEN 'YES' ELSE 'NO' END as missing_content,
                created_at
         FROM `{$wpdb->prefix}improveseo_bulktasksdetails` 
         WHERE status = 'Done' 
           AND (ai_title IS NULL OR ai_title = '' OR ai_content IS NULL OR ai_content = '')
         ORDER BY id DESC
         LIMIT 50"
    );
    
    echo "<h3>CRITICAL: Tasks marked 'Done' with incomplete data</h3>\n";
    if (empty($done_incomplete)) {
        echo "<p style='color: green;'>✅ All 'Done' tasks have complete data</p>\n";
    } else {
        echo "<p style='color: red;'>❌ Found " . count($done_incomplete) . " 'Done' tasks with missing data (DATA CORRUPTION!):</p>\n";
        echo "<table border='1' cellpadding='5' cellspacing='0'>\n";
        echo "<tr><th>ID</th><th>Bulk Task ID</th><th>Keyword</th><th>Missing Title?</th><th>Missing Image?</th><th>Missing Content?</th><th>Created</th></tr>\n";
        foreach ($done_incomplete as $task) {
            echo "<tr>";
            echo "<td>" . esc_html($task->id) . "</td>";
            echo "<td>" . esc_html($task->bulktask_id) . "</td>";
            echo "<td>" . esc_html($task->keyword_name) . "</td>";
            echo "<td style='color: " . ($task->missing_title == 'YES' ? 'red' : 'green') . "'>" . esc_html($task->missing_title) . "</td>";
            echo "<td style='color: " . ($task->missing_image == 'YES' ? 'orange' : 'green') . "'>" . esc_html($task->missing_image) . "</td>";
            echo "<td style='color: " . ($task->missing_content == 'YES' ? 'red' : 'green') . "'>" . esc_html($task->missing_content) . "</td>";
            echo "<td>" . esc_html($task->created_at) . "</td>";
            echo "</tr>\n";
        }
        echo "</table>\n";
    }
    
    // Status breakdown
    $status_breakdown = $wpdb->get_results(
        "SELECT status, COUNT(*) as count 
         FROM `{$wpdb->prefix}improveseo_bulktasksdetails` 
         GROUP BY status 
         ORDER BY count DESC"
    );
    
    echo "<h3>Task Status Breakdown</h3>\n";
    echo "<table border='1' cellpadding='5' cellspacing='0'>\n";
    echo "<tr><th>Status</th><th>Count</th></tr>\n";
    foreach ($status_breakdown as $stat) {
        echo "<tr>";
        echo "<td>" . esc_html($stat->status) . "</td>";
        echo "<td>" . esc_html($stat->count) . "</td>";
        echo "</tr>\n";
    }
    echo "</table>\n";
    
    echo "<hr>\n";
    echo "<p><strong>Diagnostic complete.</strong></p>\n";
}

// Add admin page to run diagnostics
add_action('admin_menu', 'improveseo_add_diagnostic_menu');

function improveseo_add_diagnostic_menu() {
    add_submenu_page(
        'improveseo_settings',
        'Database Diagnostics',
        'Diagnostics',
        'manage_options',
        'improveseo-diagnostics',
        'improveseo_diagnostics_page'
    );
}

function improveseo_diagnostics_page() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized access');
    }
    
    echo '<div class="wrap">';
    improveseo_diagnose_null_data();
    echo '</div>';
}
