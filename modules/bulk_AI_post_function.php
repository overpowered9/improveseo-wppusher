<?php

if (file_exists(dirname(__FILE__) . '/single_and_bulk_AI_post_function.php'))

include_once dirname(__FILE__) . '/single_and_bulk_AI_post_function.php';
include_once dirname(__FILE__) . '/GenerateAIpopup.php';

add_action('cronjob_request_event', 'CronjobRequest');

// TOC Helper Functions
if (!function_exists('verifyAndFixTOCLinks')) {
    /**
     * Function to verify and fix Table of Contents links
     * This function ensures that TOC links properly connect to their corresponding headings
     */
    function verifyAndFixTOCLinks($content) {
        // Parse the content to extract TOC links and headings
        $toc_links = [];
        $headings = [];
        
        // Extract TOC links using regex
        preg_match_all('/<a href="#([^"]+)">([^<]+)<\/a>/', $content, $toc_matches);
        if (!empty($toc_matches[1])) {
            foreach ($toc_matches[1] as $index => $anchor) {
                $toc_links[$anchor] = $toc_matches[2][$index];
            }
        }
        
        // Extract headings with IDs using regex
        preg_match_all('/<h([2-6])(?:\s+id="([^"]+)")?>([^<]+)<\/h[2-6]>/', $content, $heading_matches);
        if (!empty($heading_matches[2])) {
            foreach ($heading_matches[2] as $index => $id) {
                if (!empty($id)) {
                    $headings[$id] = $heading_matches[3][$index];
                }
            }
        }
        
        // Check for missing IDs and fix them
        $updated_content = $content;
        
        foreach ($toc_links as $anchor => $title) {
            if (!isset($headings[$anchor])) {
                // Find the heading with matching title and add ID
                $heading_pattern = '/<h([2-6])(?:\s+[^>]*)?>(' . preg_quote(trim($title), '/') . ')<\/h[2-6]>/i';
                $replacement = '<h$1 id="' . $anchor . '">$2</h$1>';
                $updated_content = preg_replace($heading_pattern, $replacement, $updated_content, 1);
            }
        }
        
        // Generate missing TOC links for headings without them
        foreach ($headings as $id => $title) {
            if (!isset($toc_links[$id])) {
                // Log missing TOC link (for debugging)
                error_log("Missing TOC link for heading: " . $title . " (ID: " . $id . ")");
            }
        }
        
        return $updated_content;
    }
}

if (!function_exists('generateAnchorId')) {
    /**
     * Function to generate URL-friendly anchor IDs from heading text
     */
    function generateAnchorId($text) {
        // Remove HTML tags, convert to lowercase, replace spaces and special chars with hyphens
        $id = strtolower(strip_tags($text));
        $id = preg_replace('/[^a-z0-9\s-]/', '', $id);
        $id = preg_replace('/[\s-]+/', '-', $id);
        $id = trim($id, '-');
        return $id;
    }
}

if (!function_exists('generateTOCFromContent')) {
    /**
     * Function to automatically generate TOC from content headings
     */
    function generateTOCFromContent($content) {
        preg_match_all('/<h([2-6])(?:\s+id="([^"]+)")?>([^<]+)<\/h[2-6]>/', $content, $matches);
        
        if (empty($matches[0])) {
            return '<h2 id="table-of-contents">Table of Contents</h2><p>No headings found.</p>';
        }
        
        $toc = '<h2 id="table-of-contents">Table of Contents</h2><ul>';
        
        foreach ($matches[0] as $index => $match) {
            $level = $matches[1][$index];
            $id = !empty($matches[2][$index]) ? $matches[2][$index] : generateAnchorId($matches[3][$index]);
            $title = trim($matches[3][$index]);
            
            // Only include H2 and H3 in TOC for better readability
            if ($level <= 3) {
                $indent = $level == 3 ? 'style="margin-left: 20px;"' : '';
                $toc .= '<li ' . $indent . '><a href="#' . $id . '">' . $title . '</a></li>';
            }
        }
        
        $toc .= '</ul>';
        return $toc;
    }
}

if (!function_exists('enhanceContentWithTOC')) {
    /**
     * Function to enhance content with proper TOC formatting
     * This is a comprehensive function that applies all TOC enhancements
     */
    function enhanceContentWithTOC($content) {
        // First verify and fix existing TOC links
        $enhanced_content = verifyAndFixTOCLinks($content);
        
        // Additional enhancements can be added here in the future
        // - Smooth scrolling JavaScript
        // - Back to top links
        // - Section numbering
        
        return $enhanced_content;
    }
}

if (!function_exists('removeConsecutiveSpecialCharacters')) {
    /**
     * Remove runs of 2+ special characters from visible text, preserving single occurrences.
     * Does not alter HTML tags or attributes.
     */
    function removeConsecutiveSpecialCharacters($content) {
        $parts = preg_split('/(<[^>]+>)/', $content, -1, PREG_SPLIT_DELIM_CAPTURE);
        if ($parts === false) {
            return $content;
        }
        foreach ($parts as $i => $part) {
            // Skip HTML tags
            if ($part !== '' && $part[0] === '<') {
                continue;
            }
            // Remove any sequence of 2+ non-letter/number/space chars (e.g., ###, ***, ----, !!!, ???)
            $parts[$i] = preg_replace('/([^\p{L}\p{N}\s]){2,}/u', '', $part);
        }
        return implode('', $parts);
    }
}

// Add helpers to handle parentheses around links/CTAs in bulk content
if (!function_exists('stripParenthesesWrappingContactTokens')) {
    // Strip parentheses that wrap raw URLs or emails in plain text.
    // Examples:
    //  - (https://example.com) => https://example.com
    //  - (www.example.com/path) => www.example.com/path
    //  - (user@example.com) => user@example.com
    function stripParenthesesWrappingContactTokens($content) {
        // URLs wrapped in parentheses
        $content = preg_replace('/\(\s*((?:https?:\/\/|www\.)[^\s)]+)\s*\)/i', '$1', $content);
        // Emails wrapped in parentheses
        $content = preg_replace('/\(\s*([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[A-Za-z]{2,})\s*\)/', '$1', $content);
        return $content;
    }
}

if (!function_exists('stripParenthesesAroundAnchorTags')) {
    // Strip parentheses that directly surround an anchor tag.
    // Example: (<a href="...">link</a>) => <a href="...">link</a>
    function stripParenthesesAroundAnchorTags($content) {
        return preg_replace('/\(\s*(<a\b[^>]*>.*?<\/a>)\s*\)/i', '$1', $content);
    }
}

// Keep parent bulk project's number_of_completed_task in sync with details
if (!function_exists('improveseo_sync_bulk_parent_progress')) {
    function improveseo_sync_bulk_parent_progress($bulktask_id) {
        global $wpdb;
        $bulktask_id = (int) $bulktask_id;
        if ($bulktask_id <= 0) {
            return;
        }
        
        
        
        // Count tasks with status='Done' (content generation completed)
        $done = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(1) FROM {$wpdb->prefix}improveseo_bulktasksdetails WHERE bulktask_id = %d AND status = %s",
                $bulktask_id,
                'Done'
            )
        );
        
        // Get count of canceled tasks (status='Stoped')
        $canceled = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(1) FROM {$wpdb->prefix}improveseo_bulktasksdetails WHERE bulktask_id = %d AND status = %s",
                $bulktask_id,
                'Stoped'
            )
        );
        
        // Get total number of tasks for this bulk project
        $total = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(1) FROM {$wpdb->prefix}improveseo_bulktasksdetails WHERE bulktask_id = %d",
                $bulktask_id
            )
        );
        
        // Determine parent project state based on content generation status
        // number_of_completed_task tracks successfully generated content (status='Done')
        $update_data = array(
            'number_of_completed_task' => $done,
            'updated_at' => current_time('mysql'),
        );
        
        // Check if all tasks have reached a terminal status (Done or Canceled)
        if ($total > 0 && ($done + $canceled) >= $total) {
            // If ALL tasks are canceled, mark project as Cancelled
            if ($canceled === $total) {
                $update_data['state'] = 'Cancelled';
            } else {
                // Otherwise, at least some content was generated - mark as Finished
                $update_data['state'] = 'Finished';
            }

            // Notify admin server — bulk task completed
            // Guard: only send when the state is actually TRANSITIONING to terminal.
            // sync() is called from multiple places (generateBulkAiContent, saveContentInTaskList x2,
            // bulkprojects.php) so without this check the user would get duplicate emails.
            if (function_exists('improveseo_notify_bulk_status')) {
                $parent_row = $wpdb->get_row(
                    $wpdb->prepare(
                        "SELECT name, state FROM {$wpdb->prefix}improveseo_bulktasks WHERE id = %d",
                        $bulktask_id
                    )
                );
                // Only notify if parent is NOT already in a terminal state
                if ($parent_row && !in_array($parent_row->state, array('Finished', 'Cancelled'), true)) {
                    improveseo_notify_bulk_status('task_completed', array(
                        'task_name'       => $parent_row->name ?: 'Bulk Project #' . $bulktask_id,
                        'completed_tasks' => $done,
                        'failed_tasks'    => $canceled,
                        'project_id'      => $bulktask_id,
                    ));
                }
            }
        }
        
        // Update the parent row with the recalculated count and state
        $wpdb->update(
            $wpdb->prefix . 'improveseo_bulktasks',
            $update_data,
            array('id' => $bulktask_id),
            array('%d', '%s', '%s'),
            array('%d')
        );
    }
}

/**
 * Send notification to the admin server for bulk task events.
 * Returns true on success, false on failure. Errors are logged.
 *
 * @param string $event   'task_created' or 'task_completed'
 * @param array  $payload Associative array merged into the request body.
 * @return bool  Whether the notification was sent successfully.
 */
if (!function_exists('improveseo_notify_bulk_status')) {
    function improveseo_notify_bulk_status($event, $payload = array()) {
        $api_key   = get_option('improveseo_api_key');
        $site_code = get_option('improveseo_site_code');

        if (empty($api_key) || empty($site_code)) {
            my_plugin_log('[BulkNotify] Skipped — api_key or site_code empty');
            return false;
        }

        $admin_server_url = 'https://imporve-seo-admin-server-nzbm.onrender.com';
        $endpoint = $admin_server_url . '/api/v1/bulk-notifications/status';

        $body = array_merge(array('event' => $event), $payload);

        my_plugin_log('[BulkNotify] Sending ' . $event . ' to ' . $endpoint);
        my_plugin_log('[BulkNotify] Body: ' . wp_json_encode($body));

        $response = wp_remote_post($endpoint, array(
            'headers'  => array(
                'x-api-key'     => $api_key,
                'x-site-code'   => $site_code,
                'Content-Type'  => 'application/json',
            ),
            'body'     => wp_json_encode($body),
            'timeout'  => 15,
            'blocking' => true,
        ));

        if (is_wp_error($response)) {
            my_plugin_log('[BulkNotify] WP_Error: ' . $response->get_error_message());
            return false;
        }

        $code = wp_remote_retrieve_response_code($response);
        $resp_body = wp_remote_retrieve_body($response);
        my_plugin_log('[BulkNotify] Response ' . $code . ': ' . $resp_body);

        return ($code >= 200 && $code < 300);
    }
}

/**
 * Remove orphaned preview projects and their draft posts.
 *
 * A preview is normally cleaned up the moment its popup window closes
 * (preview_delete_ajax). This sweep is the safety net for cases where that
 * never ran — popup blocked, tab crashed, build stalled, etc.
 *
 * Deletion is deliberately constrained so it can NEVER touch real content:
 *   - the project must carry the dedicated state 'Preview'
 *     (real projects are Draft / Published / Updated / Building — never that), and
 *   - it must be older than 30 minutes (an actively-open preview is left alone), and
 *   - a post is only removed when it is simultaneously a draft, flagged with
 *     postmeta _improveseo_preview = '1', AND linked to that preview project.
 * No genuine post or project can satisfy that combination.
 */
function improveseo_sweep_preview_orphans()
{
	global $wpdb;

	$tasks_table = $wpdb->prefix . 'improveseo_tasks';

	// created_at is written with NOW() in AbstractModel::create(), so compare
	// against NOW() here too — consistent regardless of the DB server timezone.
	$preview_ids = $wpdb->get_col(
		"SELECT id FROM {$tasks_table}
		 WHERE state = 'Preview'
		   AND created_at < (NOW() - INTERVAL 30 MINUTE)"
	);

	if (empty($preview_ids)) {
		return;
	}

	$removed_posts = 0;

	foreach ($preview_ids as $preview_id) {
		$post_ids = $wpdb->get_col($wpdb->prepare(
			"SELECT p.ID
			 FROM {$wpdb->posts} p
			 INNER JOIN {$wpdb->postmeta} link ON link.post_id = p.ID
				 AND link.meta_key = 'improveseo_project_id' AND link.meta_value = %s
			 INNER JOIN {$wpdb->postmeta} flag ON flag.post_id = p.ID
				 AND flag.meta_key = '_improveseo_preview' AND flag.meta_value = '1'
			 WHERE p.post_status = 'draft'",
			$preview_id
		));

		foreach ($post_ids as $post_id) {
			// force delete: skip trash and clear the post's meta too.
			wp_delete_post($post_id, true);
			$removed_posts++;
		}

		// Only ever delete the row if it is still a Preview project.
		$wpdb->delete($tasks_table, array('id' => $preview_id, 'state' => 'Preview'));
	}

	my_plugin_log(sprintf(
		'IMPROVESEO PREVIEW SWEEP: removed %d orphaned preview project(s) and %d draft post(s).',
		count($preview_ids),
		$removed_posts
	));
}

function CronjobRequest()

{

	my_plugin_log("=== CRON JOB STARTED ===");
	my_plugin_log("cron calling working...");

	global $wpdb;

	my_plugin_log("Calling generateBulkAiContent()...");

	$returndata = generateBulkAiContent();
	
	my_plugin_log("generateBulkAiContent() returned: " . json_encode($returndata));

	// $wpdb->insert($wpdb->prefix . "improveseo_cron_job_status", array(

	// 	'time' => date("Y-m-d H:i:s"),

	// 	'input' => '',

	// 	'content_data' => json_encode($returndata),

	// 	'status' => 1

	// ));
	
	my_plugin_log("Calling saveContentInTaskList()...");

	$publishContent = saveContentInTaskList();
	
	my_plugin_log("saveContentInTaskList() returned: " . json_encode($publishContent));

	// Generate Content



	//$lastid = $wpdb->insert_id;

	//error_log('This is a log message : '.current_time('mysql'));
	
	// Safety-net cleanup for previews whose popup-close handler never fired.
	improveseo_sweep_preview_orphans();

	my_plugin_log("=== CRON JOB COMPLETED ===");

}

function generateBulkAiContent($id = '', $regenerate = '')

{

	global $wpdb;

	if ($id != '') {

		$sql = $wpdb->prepare(
			"SELECT * FROM `" . $wpdb->prefix . "improveseo_bulktasksdetails` WHERE `id` = %d",
			$id
		);
		my_plugin_log('generateBulkAiContent: Querying specific task ID: ' . $id);

	} else {

		// Also recover tasks stuck in 'Processing' for more than 10 minutes (600 seconds)
		$sql = "SELECT * FROM `" . $wpdb->prefix . "improveseo_bulktasksdetails` 
				WHERE (`status`='Pending' OR (`status`='Processing' AND updated_at < DATE_SUB(NOW(), INTERVAL 10 MINUTE))) 
				ORDER BY `id` ASC LIMIT 1";
		my_plugin_log('generateBulkAiContent: Querying next pending task (including stuck tasks)');

	}



	$tasks = $wpdb->get_results($sql);

	if (empty($tasks)) {
		my_plugin_log('generateBulkAiContent: No tasks found to process');
		return true;
	}

	my_plugin_log('generateBulkAiContent: Found ' . count($tasks) . ' task(s) to process');



	//seed_option1

	foreach ($tasks as $key => $value) {

		$id = $value->id;
		my_plugin_log('generateBulkAiContent: Processing task ID: ' . $id . ' | Keyword: "' . $value->keyword_name . '" | Bulk Task: ' . $value->bulktask_id);

		// ✅ STEP 1: Lock the task by setting status to 'Processing'
		// This prevents another cron from processing the same task
		if (empty($regenerate)) {
			my_plugin_log('generateBulkAiContent: Attempting to lock task ID ' . $id);
			
			// Accept both Pending and stuck Processing tasks (older than 10 min)
			$rows_affected = $wpdb->query(
				$wpdb->prepare(
					"UPDATE `{$wpdb->prefix}improveseo_bulktasksdetails`
					 SET status = %s, updated_at = NOW()
					 WHERE id = %d AND (status = %s OR (status = %s AND updated_at < DATE_SUB(NOW(), INTERVAL 10 MINUTE)))",
					'Processing',
					$id,
					'Pending',
					'Processing'
				)
			);
			
			if ($rows_affected === 0) {
				my_plugin_log('generateBulkAiContent: Task ID ' . $id . ' already locked by another process or not in Pending/stuck state. Skipping.');
				return false;
			}
			
			my_plugin_log('generateBulkAiContent: Successfully locked task ID ' . $id . ' - status set to Processing');
		} else {
			// Even in regeneration mode, we need locking to prevent duplicate processing
			my_plugin_log('generateBulkAiContent: Regeneration mode - attempting to lock task');
			$rows_affected = $wpdb->query(
				$wpdb->prepare(
					"UPDATE `{$wpdb->prefix}improveseo_bulktasksdetails`
					 SET status = %s, updated_at = NOW()
					 WHERE id = %d AND status != %s",
					'Processing',
					$id,
					'Processing'
				)
			);
			
			if ($rows_affected === 0) {
				my_plugin_log('generateBulkAiContent: Task ID ' . $id . ' already being regenerated by another process. Skipping.');
				return false;
			}
			
			my_plugin_log('generateBulkAiContent: Successfully locked task ID ' . $id . ' for regeneration');
		}

		// ✅ STEP 2: Check if parent bulk task is stopped
		if (isset($value->bulktask_id)) {
			$parent_task = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT state FROM {$wpdb->prefix}improveseo_bulktasks WHERE id = %d",
					$value->bulktask_id
				)
			);
			
			// Check if parent is stopped or inactive
			// Valid active states: 'Processing', 'Finished', 'Unpublished' (initial state)
			// Invalid states: 'Stopped', 'Draft', or any other unknown state
			if ($parent_task && !in_array($parent_task->state, array('Processing', 'Finished', 'Unpublished'))) {
				my_plugin_log('generateBulkAiContent: Parent bulk task ' . $value->bulktask_id . ' is in inactive state: ' . $parent_task->state . '. Marking task ID ' . $id . ' as Stoped.');
				
				// Mark task as canceled
				$wpdb->query(
					$wpdb->prepare(
						"UPDATE `{$wpdb->prefix}improveseo_bulktasksdetails`
						 SET status = %s WHERE id = %d",
						'Stoped',
						$id
					)
				);
				
				echo '<h3>Bulk project is not active.</h3>';
				return false;
			}
			
			// If parent is still 'Unpublished', update it to 'Processing' since we're starting work
			// Use atomic update with WHERE clause to prevent race condition
			if ($parent_task && $parent_task->state == 'Unpublished') {
				my_plugin_log('generateBulkAiContent: Parent bulk task ' . $value->bulktask_id . ' is Unpublished. Attempting atomic update to Processing.');
				$parent_rows = $wpdb->query(
					$wpdb->prepare(
						"UPDATE {$wpdb->prefix}improveseo_bulktasks 
						 SET state = %s 
						 WHERE id = %d AND state = %s",
						'Processing',
						$value->bulktask_id,
						'Unpublished'
					)
				);
				
				if ($parent_rows > 0) {
					my_plugin_log('generateBulkAiContent: Parent bulk task ' . $value->bulktask_id . ' updated to Processing');
				} else {
					my_plugin_log('generateBulkAiContent: Parent bulk task ' . $value->bulktask_id . ' already updated by another process');
				}
			}
			
			my_plugin_log('generateBulkAiContent: Parent bulk task ' . $value->bulktask_id . ' state: ' . ($parent_task ? $parent_task->state : 'NOT FOUND'));
		}

		// AI Title



		$getAudienceData = getAudienceData($value->keyword_name);

		if ($value->select_exisiting_options == 'seed_option1') {

			$ai_title = $value->keyword_name;

		} else if ($value->select_exisiting_options == 'seed_option2') {

			$ai_title = bulkAiTitle($getAudienceData, 'normal', $value->keyword_name, $value->tone_of_voice);

		} else if ($value->select_exisiting_options == 'seed_option3') {

			$ai_title = bulkAiTitle($getAudienceData, 'question', $value->keyword_name, $value->tone_of_voice);

		} else {

			// Unknown option — fall back to keyword so title is never empty
			my_plugin_log('generateBulkAiContent: Unknown select_exisiting_options value "' . $value->select_exisiting_options . '" for task ' . $id . ' — using keyword as title fallback');
			$ai_title = $value->keyword_name;

		}

		$ai_title = str_replace('"', '', $ai_title);
		$ai_title = trim($ai_title, '"');

		// Final safety net: if title is still empty after all attempts, use keyword directly
		if (empty($ai_title)) {
			my_plugin_log('generateBulkAiContent: Title still empty after generation for task ' . $id . ' — forcing keyword as title');
			$ai_title = $value->keyword_name;
		}

		my_plugin_log('generateBulkAiContent: Generated title for task ' . $id . ': "' . $ai_title . '"');









	// ----- v2 fields (one niche for the whole batch, derived from the business setup) -----
	$iseo_bt = strtolower((string) get_option('improveseo_business_type', ''));
	$iseo_niche = 'general_blog';
	foreach (array('hvac','plumbing','electrical','roofing','landscaping','dental','medical','restaurant','fitness','beauty','mortgage','realestate','ecommerce') as $iseo_nk) {
		if ($iseo_bt && strpos($iseo_bt, $iseo_nk) !== false) { $iseo_niche = $iseo_nk; break; }
	}
	$iseo_niche_data = array();
	if (!empty($value->details_to_include)) { $iseo_niche_data['details'] = $value->details_to_include; }
	if (!empty($value->tone_of_voice))      { $iseo_niche_data['preferred_tone'] = $value->tone_of_voice; }
	$iseo_brand_profile = array();
	$iseo_bp_city    = get_option('improveseo_business_city', '');
	$iseo_bp_service = get_option('improveseo_business_service', '');
	if ($iseo_bp_city)    { $iseo_brand_profile['location'] = array('city' => $iseo_bp_city); }
	if ($iseo_bp_service) { $iseo_brand_profile['services'] = array($iseo_bp_service); }

	// ✅ STEP 3: Generate AI Image
	my_plugin_log('generateBulkAiContent: Starting image generation for task ' . $id . ' | Image option: ' . $value->aiImage);

	if ($value->aiImage == 'AI_image_one') {

		$imageURL = generateBulkAiImage($ai_title, $getAudienceData, $iseo_niche);
		
		// Check if image generation failed (returns false)
		if ($imageURL === false) {
			my_plugin_log('generateBulkAiContent: Image generation FAILED for task ' . $id);
			$imageURL = ''; // Set empty string for database
		} else {
			my_plugin_log('generateBulkAiContent: Image generated successfully for task ' . $id . ' | URL: ' . substr($imageURL, 0, 100) . '...');
			// Encode the valid URL for storage
			$imageURL = base64_encode($imageURL);
		}

	} else {

		$imageURL = $value->ai_image;
		my_plugin_log('generateBulkAiContent: Using existing image for task ' . $id);

	}

	// ✅ STEP 4: Generate AI Content
	my_plugin_log('generateBulkAiContent: Starting content generation for task ' . $id . ' | Words: ' . $value->nos_of_words . ' | Language: ' . $value->content_lang);
	
	$keyword_selection = '';

	$generation_result = createAIpost2bulk($value->keyword_name, $keyword_selection, $value->select_exisiting_options, $value->nos_of_words, $value->content_lang, $shortcode = '', $is_single_keyword = '', $value->tone_of_voice, $value->point_of_view, $ai_title, $value->call_to_action, $value->details_to_include, '', $iseo_niche, $iseo_niche_data, $iseo_brand_profile, '');

	// Extract content from the result array
	$AI_Content = $generation_result['content'];
	$meta_title = $generation_result['meta_title'];
	$meta_description = $generation_result['meta_description'];

	my_plugin_log('generateBulkAiContent: Content generation result for task ' . $id . ' | Content length: ' . strlen($AI_Content) . ' characters | Meta title: ' . $meta_title);

	// ✅ CRITICAL FIX: Validate title and image FIRST - these are mandatory fields
	// These MUST NOT be NULL in database - hard fail if missing
	if (empty($ai_title)) {
		my_plugin_log('generateBulkAiContent: CRITICAL ERROR - Missing title for task ' . $id . '. Cannot proceed.');
		
		// Keep in Processing state to prevent retry loop - manual intervention needed
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE `{$wpdb->prefix}improveseo_bulktasksdetails`
				 SET status = %s WHERE id = %d",
				'Processing',
				$id
			)
		);
		
		return false;
	}
	
	// Image validation - if image generation was requested but failed, reset to Pending for retry
	if ($value->aiImage == 'AI_image_one' && empty($imageURL)) {
		my_plugin_log('generateBulkAiContent: Image generation failed for task ' . $id . '. Saving title, resetting status to Pending for retry.');

		$wpdb->query(
			$wpdb->prepare(
				"UPDATE `{$wpdb->prefix}improveseo_bulktasksdetails`
				 SET status = %s, ai_title = %s WHERE id = %d",
				'Pending',
				$ai_title,
				$id
			)
		);

		return false;
	}

	// ✅ STEP 5: Validate content - check for errors
	if (empty($AI_Content)) {
		my_plugin_log('generateBulkAiContent: ERROR - Empty content received for task ' . $id . '. Saving title/image, resetting status for content retry.');
		
		// ✅ CRITICAL: Save title and image even if content failed!
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE `{$wpdb->prefix}improveseo_bulktasksdetails`
				 SET status = %s, ai_title = %s, ai_image = %s WHERE id = %d",
				'Pending',
				$ai_title,
				$imageURL,
				$id
			)
		);
		
		return false;
	}
	
	// Check if content is suspiciously short (likely an error message)
	// Real articles should be at least 100 characters
	if (strlen($AI_Content) < 100 && (
		stripos($AI_Content, 'Error') !== false || 
		stripos($AI_Content, 'Failed') !== false ||
		stripos($AI_Content, 'failed to open stream') !== false ||
		stripos($AI_Content, 'connection') !== false)) {
		
		my_plugin_log('generateBulkAiContent: ERROR - Content appears to be an error message for task ' . $id . ': ' . substr($AI_Content, 0, 100) . '... | Saving title/image, resetting status for content retry.');
		
		// ✅ CRITICAL: Save title and image even if content is an error message!
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE `{$wpdb->prefix}improveseo_bulktasksdetails`
				 SET status = %s, ai_title = %s, ai_image = %s, ai_content = %s WHERE id = %d",
				'Pending',
				$ai_title,
				$imageURL,
				base64_encode($AI_Content), // Store error for debugging
				$id
			)
		);
		
		return false;
	}
	
	my_plugin_log('generateBulkAiContent: Content validation passed for task ' . $id);

		$data_array = array('ai_title' => $ai_title, 'imageURL' => $imageURL, 'AI_Content' => $AI_Content);

		$AI_Content = base64_encode($AI_Content);

        // ✅ STEP 6: FINAL PRE-PERSISTENCE VALIDATION
		// CRITICAL: Assert that NO mandatory fields are NULL before database write
		// This is our last line of defense against data corruption
		if (empty($ai_title) || $ai_title === null) {
			my_plugin_log('generateBulkAiContent: FATAL - ai_title is NULL at persistence point for task ' . $id . '. This should NEVER happen. Aborting.');
			return false;
		}
		
		if ($imageURL === null) {
			my_plugin_log('generateBulkAiContent: FATAL - ai_image is NULL at persistence point for task ' . $id . '. This should NEVER happen. Aborting.');
			return false;
		}
		
		if (empty($AI_Content)) {
			my_plugin_log('generateBulkAiContent: FATAL - ai_content is NULL/empty at persistence point for task ' . $id . '. This should NEVER happen. Aborting.');
			return false;
		}
		
		// Log exactly what we're about to save
		my_plugin_log('generateBulkAiContent: Pre-persistence check passed | ai_title: "' . substr($ai_title, 0, 50) . '" | ai_image length: ' . strlen($imageURL) . ' | ai_content length: ' . strlen($AI_Content) . ' bytes');

        // ✅ STEP 7: Persist content as Done + AI payload
		// DO NOT modify state - it represents user's original publishing intent
		// Only update status to 'Done' when content is ready
		my_plugin_log('generateBulkAiContent: Saving generated content for task ' . $id . ' | Setting status to Done');

		// Self-heal: make sure the meta columns exist before writing to them, in
		// case the dbDelta migration didn't add them on this install.
		improveseo_ensure_bulk_meta_columns();

        $update_result = $wpdb->query(
            $wpdb->prepare(
                "UPDATE `{$wpdb->prefix}improveseo_bulktasksdetails`
                 SET status = %s, ai_title = %s, ai_content = %s, ai_image = %s, meta_title = %s, meta_description = %s
                 WHERE id = %d",
                'Done', $ai_title, $AI_Content, $imageURL, $meta_title, $meta_description, $id
            )
        );
		
		// ✅ CRITICAL: Verify the update succeeded
		if ($update_result === false) {
			my_plugin_log('generateBulkAiContent: FATAL - Database UPDATE failed for task ' . $id . ' | MySQL Error: ' . $wpdb->last_error);
			return false;
		}
		
		if ($update_result === 0) {
			my_plugin_log('generateBulkAiContent: WARNING - UPDATE affected 0 rows for task ' . $id . '. Task may not exist or values unchanged.');
		}
		
		// ✅ CRITICAL: Verify the data was actually written to database
		$verification = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT ai_title, ai_image, ai_content FROM `{$wpdb->prefix}improveseo_bulktasksdetails` WHERE id = %d",
				$id
			)
		);
		
		if (!$verification) {
			my_plugin_log('generateBulkAiContent: FATAL - Cannot verify saved data for task ' . $id . '. Record not found after UPDATE.');
			return false;
		}
		
		// Assert mandatory fields are NOT NULL in database
		if ($verification->ai_title === null || $verification->ai_title === '') {
			my_plugin_log('generateBulkAiContent: FATAL - ai_title is NULL in database after UPDATE for task ' . $id . '. DATA CORRUPTION DETECTED!');
			return false;
		}
		
		if ($verification->ai_image === null) {
			my_plugin_log('generateBulkAiContent: FATAL - ai_image is NULL in database after UPDATE for task ' . $id . '. DATA CORRUPTION DETECTED!');
			return false;
		}
		
		if ($verification->ai_content === null || $verification->ai_content === '') {
			my_plugin_log('generateBulkAiContent: FATAL - ai_content is NULL in database after UPDATE for task ' . $id . '. DATA CORRUPTION DETECTED!');
			return false;
		}
		
		my_plugin_log('generateBulkAiContent: ✅ Database verification passed | ai_title: "' . substr($verification->ai_title, 0, 50) . '" | ai_image: ' . strlen($verification->ai_image) . ' bytes | ai_content: ' . strlen($verification->ai_content) . ' bytes');

		my_plugin_log('generateBulkAiContent: ✅ SUCCESS - Content generation complete for task ID: ' . $id . ' | Status: Done | State: ' . $value->state);

		// Sync parent bulk project after changing child status
		if (isset($value->bulktask_id)) {
			my_plugin_log('generateBulkAiContent: Syncing parent bulk task progress for bulktask_id: ' . $value->bulktask_id);
			improveseo_sync_bulk_parent_progress((int) $value->bulktask_id);
		}

		// If regenerating and post already exists and is published, update the WordPress post content
		if (!empty($regenerate) && !empty($value->post_id)) {
			$post = get_post($value->post_id);
			if ($post && $post->post_status == 'publish') {
				my_plugin_log('generateBulkAiContent: Regeneration mode - updating existing WordPress post ID: ' . $value->post_id);
				
				$post_update = array(
					'ID' => $value->post_id,
					'post_title' => $ai_title,
					'post_content' => base64_decode($AI_Content),
				);
				wp_update_post($post_update);

				// Refresh the SEO meta too so the regenerated post's title/description
				// stay in sync with the new content (improveseo_custom_* + active SEO plugin).
				improveseo_write_seo_meta($value->post_id, $meta_title, $meta_description);

				my_plugin_log('generateBulkAiContent: WordPress post updated successfully');
			}
		}
	}

	//$wpdb->query ( "UPDATE `".$wpdb->prefix."improveseo_bulktasksdetails` SET status='Done',`ai_title`=".$ai_title.",`ai_content`='".$AI_Content."',`ai_image`='".$imageURL."', WHERE id=".$id );





	//update_option("work_dex_schedule",time());





	return $data_array;

}

function saveContentInTaskList()

{



	global $wpdb;
	
	my_plugin_log('saveContentInTaskList: === FUNCTION START ===');
	
	// ========================================
	// PART 1: PUBLISH SCHEDULED DRAFTS
	// ========================================
	// This runs FIRST and independently - publishes existing drafts whose date has arrived
	
	$today = date('Y-m-d');
	
	my_plugin_log('saveContentInTaskList: === SCHEDULED POST PUBLISHER START ===');
	my_plugin_log('saveContentInTaskList: Looking for scheduled posts to publish | Today: ' . $today);
	
	$sql_scheduled = $wpdb->prepare(
		"SELECT d.* FROM `{$wpdb->prefix}improveseo_bulktasksdetails` d
		 INNER JOIN `{$wpdb->prefix}improveseo_bulktasks` p ON d.bulktask_id = p.id
		 WHERE d.published_on IS NOT NULL
		 AND d.published_on <= %s 
		 AND d.post_id IS NOT NULL 
		 AND d.state = 'Scheduled'
		 AND d.status = 'Done'
		 AND p.state IN ('Processing', 'Finished', 'Unpublished')
		 ORDER BY d.id ASC",
		$today
	);

	$scheduledPosts = $wpdb->get_results($sql_scheduled);

	my_plugin_log('saveContentInTaskList: Found ' . count($scheduledPosts) . ' scheduled posts to publish');

	if (!empty($scheduledPosts)) {
		foreach ($scheduledPosts as $task) {

			// Verify the WordPress post actually exists before trying to publish
			$wp_post = get_post($task->post_id);
			
			if (!$wp_post) {
				my_plugin_log('saveContentInTaskList: ❌ WordPress post not found | Task ID: ' . $task->id . ' | Post ID: ' . $task->post_id . ' | Skipping');
				continue;
			}
			
			// Only publish if post is currently a draft
			if ($wp_post->post_status !== 'draft') {
				my_plugin_log('saveContentInTaskList: ⚠️ Post not in draft status | Task ID: ' . $task->id . ' | Post ID: ' . $task->post_id . ' | Current status: ' . $wp_post->post_status . ' | Skipping');
				continue;
			}

			my_plugin_log('saveContentInTaskList: 📅 Publishing scheduled post | Task ID: ' . $task->id . ' | Post ID: ' . $task->post_id . ' | Keyword: ' . $task->keyword_name . ' | Scheduled: ' . $task->published_on);

			// Change post status from draft to publish
			$post_data = array(
				'ID' => $task->post_id,
				'post_status' => 'publish'
			);

			$update_result = wp_update_post($post_data, true);
			
			if (is_wp_error($update_result)) {
				my_plugin_log('saveContentInTaskList: ❌ Failed to publish post | Task ID: ' . $task->id . ' | Post ID: ' . $task->post_id . ' | Error: ' . $update_result->get_error_message());
				continue;
			}
			
			my_plugin_log('saveContentInTaskList: ✅ Post published successfully | Post ID: ' . $task->post_id . ' | Task ID: ' . $task->id);

			// Add keyword as tag
			if (!empty($task->keyword_name)) {
				$tags = array($task->keyword_name);
				wp_set_post_tags($task->post_id, $tags);
				my_plugin_log('saveContentInTaskList: 🏷️ Added keyword tag | Post ID: ' . $task->post_id . ' | Tag: ' . $task->keyword_name);
			}

			// Update task state to Published
			my_plugin_log('saveContentInTaskList: Preparing to UPDATE database | Task ID: ' . $task->id . ' | Setting state=Published, is_published_by_plugin=1');
			
			$update_rows = $wpdb->query(
				$wpdb->prepare(
					"UPDATE `{$wpdb->prefix}improveseo_bulktasksdetails`
					 SET `state` = %s, `updated_at` = NOW()
					 WHERE id = %d",
					'Published',
					$task->id
				)
			);
			
			// Log MySQL error if any
			if ($wpdb->last_error) {
				my_plugin_log('saveContentInTaskList: ❌ MySQL ERROR during UPDATE | Task ID: ' . $task->id . ' | Error: ' . $wpdb->last_error);
			}
			
			if ($update_rows === false) {
				my_plugin_log('saveContentInTaskList: ❌ Database UPDATE returned FALSE | Task ID: ' . $task->id);
			} elseif ($update_rows === 0) {
				my_plugin_log('saveContentInTaskList: ⚠️ UPDATE affected 0 rows | Task ID: ' . $task->id);
				
				// Verify the task still exists and check its current state
				$verification = $wpdb->get_row(
					$wpdb->prepare(
						"SELECT id, state, is_published_by_plugin FROM `{$wpdb->prefix}improveseo_bulktasksdetails` WHERE id = %d",
						$task->id
					)
				);
				
				if ($verification) {
					my_plugin_log('saveContentInTaskList: Task exists | Current state: ' . $verification->state . ' | is_published_by_plugin: ' . $verification->is_published_by_plugin);
				} else {
					my_plugin_log('saveContentInTaskList: ❌ Task ID ' . $task->id . ' not found in database!');
				}
			} else {
				my_plugin_log('saveContentInTaskList: ✅ Task state updated to Published | Task ID: ' . $task->id . ' | Rows affected: ' . $update_rows);
			}

			// Sync parent bulk project progress
			if (isset($task->bulktask_id)) {
				improveseo_sync_bulk_parent_progress((int) $task->bulktask_id);
				my_plugin_log('saveContentInTaskList: 🔄 Synced parent bulk task progress | Bulktask ID: ' . $task->bulktask_id);
			}
		}
	} else {
		my_plugin_log('saveContentInTaskList: No scheduled posts found needing publication');
	}
	
	my_plugin_log('saveContentInTaskList: === SCHEDULED POST PUBLISHER END ===');
	
	// ========================================
	// PART 2: CREATE WORDPRESS POSTS
	// ========================================
	// This runs SECOND - creates WordPress posts for tasks with generated content
	// Posts are created as 'draft' or 'publish' based on state and published_on date
	
	my_plugin_log('saveContentInTaskList: === WORDPRESS POST CREATION START ===');
	
// Query finds tasks with generated content but no WordPress post created yet:
// 1. state='Scheduled' - posts that should be created as drafts for future publishing
// 2. state='Published' - posts that should be published immediately
// 3. status='Done' - content generation completed
// 4. post_id IS NULL - WordPress post not yet created
	$sql = "SELECT * FROM `" . $wpdb->prefix . "improveseo_bulktasksdetails` 
			WHERE `state` IN('Scheduled','Published') 
			AND `status` = 'Done' 
			AND `post_id` IS NULL 
			ORDER BY `id` ASC LIMIT 1";

	my_plugin_log("saveContentInTaskList: Executing query to find tasks ready to create WordPress posts");
	my_plugin_log("saveContentInTaskList: Query: " . $sql);

	$Bulktasks = $wpdb->get_results($sql);
	
	if (empty($Bulktasks)) {
		my_plugin_log("saveContentInTaskList: No tasks found needing WordPress post creation");
		my_plugin_log('saveContentInTaskList: === WORDPRESS POST CREATION END ===');
		my_plugin_log('saveContentInTaskList: === FUNCTION END ===');
		return true;
	}
	
	my_plugin_log("saveContentInTaskList: Found " . count($Bulktasks) . " task(s) ready for WordPress post creation");
	my_plugin_log("saveContentInTaskList: Task details - ID: " . $Bulktasks[0]->id . " | Bulktask: " . $Bulktasks[0]->bulktask_id . " | Keyword: " . $Bulktasks[0]->keyword_name . " | State: " . $Bulktasks[0]->state);







	$content = '';

	if (!empty($Bulktasks)) {

		foreach ($Bulktasks as $key => $value) {
			
			$task_id = $value->id;
			
			my_plugin_log("saveContentInTaskList: Processing task ID " . $task_id . " for publishing");

			// ✅ STEP 1: Lock the task by setting a temporary status
			// This prevents duplicate post creation if multiple crons run
			my_plugin_log("saveContentInTaskList: Attempting to lock task " . $task_id . " for publishing");
			
			// Accept both Done tasks without post_id AND stuck Publishing tasks (older than 10 min)
			$rows_affected = $wpdb->query(
				$wpdb->prepare(
					"UPDATE `{$wpdb->prefix}improveseo_bulktasksdetails`
					 SET status = %s, updated_at = NOW()
					 WHERE id = %d 
					 AND post_id IS NULL
					 AND (status = %s OR (status = %s AND updated_at < DATE_SUB(NOW(), INTERVAL 10 MINUTE)))",
					'Publishing',
					$task_id,
					'Done',
					'Publishing'
				)
			);
			
			if ($rows_affected === 0) {
				my_plugin_log("saveContentInTaskList: Task " . $task_id . " already being published by another process, post already exists, or not in correct state. Skipping.");
				return false;
			}
			
			my_plugin_log("saveContentInTaskList: Successfully locked task " . $task_id . " - status set to Publishing");

			// ✅ STEP 2: Check if parent bulk task is stopped
			if (isset($value->bulktask_id)) {
				$parent_task = $wpdb->get_row(
					$wpdb->prepare(
						"SELECT state FROM {$wpdb->prefix}improveseo_bulktasks WHERE id = %d",
						$value->bulktask_id
					)
				);
				
				// Check if parent is stopped or inactive
				// Valid active states: 'Processing', 'Finished', 'Unpublished' (initial state)
				// Invalid states: 'Stopped', 'Draft', or any other unknown state
				if ($parent_task && !in_array($parent_task->state, array('Processing', 'Finished', 'Unpublished'))) {
					my_plugin_log('saveContentInTaskList: Parent bulk task ' . $value->bulktask_id . ' is in inactive state: ' . $parent_task->state . '. Aborting publish for task ' . $task_id);
					
					// Reset status back to Done so it can be published later if project is reactivated
					$wpdb->query(
						$wpdb->prepare(
							"UPDATE `{$wpdb->prefix}improveseo_bulktasksdetails`
							 SET status = %s WHERE id = %d",
							'Done',
							$task_id
						)
					);
					
					echo '<h3>Bulk project is not active.</h3>';
					return false;
				}
				
				my_plugin_log('saveContentInTaskList: Parent bulk task ' . $value->bulktask_id . ' state: ' . ($parent_task ? $parent_task->state : 'NOT FOUND'));
			}

			// ✅ STEP 3: CRITICAL PRE-PUBLISH VALIDATION
			// Assert that mandatory fields are NOT NULL before attempting to create WordPress post
			// This prevents publishing incomplete/corrupted data
			if ($value->ai_title === null || $value->ai_title === '') {
				my_plugin_log('saveContentInTaskList: FATAL - ai_title is NULL for task ' . $task_id . '. Cannot create post with empty title. Resetting to Pending.');
				
				$wpdb->query(
					$wpdb->prepare(
						"UPDATE `{$wpdb->prefix}improveseo_bulktasksdetails`
						 SET status = %s WHERE id = %d",
						'Pending',
						$task_id
					)
				);
				
				return false;
			}
			
			if ($value->ai_content === null || $value->ai_content === '') {
				my_plugin_log('saveContentInTaskList: FATAL - ai_content is NULL for task ' . $task_id . '. Cannot create post with empty content. Resetting to Pending.');
				
				$wpdb->query(
					$wpdb->prepare(
						"UPDATE `{$wpdb->prefix}improveseo_bulktasksdetails`
						 SET status = %s WHERE id = %d",
						'Pending',
						$task_id
					)
				);
				
				return false;
			}
			
			// Note: ai_image CAN be empty if user chose not to use images, so we only log a warning
			if ($value->ai_image === null || $value->ai_image === '') {
				my_plugin_log('saveContentInTaskList: WARNING - ai_image is NULL/empty for task ' . $task_id . '. Post will be created without featured image.');
			}
			
			my_plugin_log('saveContentInTaskList: Pre-publish validation passed for task ' . $task_id . ' | Title: "' . substr($value->ai_title, 0, 50) . '..." | Content length: ' . strlen($value->ai_content) . ' bytes');

			// short code

			if (!empty($value->testimonial)) {

				$testimonial_ids = '';

				$all_testimonial = explode("||", $value->testimonial);

				foreach ($all_testimonial as $key1 => $value1) {

					if (!empty($value1)) {

						$testimonial_ids = $value1 . ',' . $testimonial_ids;

					}

				}

				$content = $content . '<p>[improveseo_testimonial id="' . $testimonial_ids . '"]</p>';

			}



			if (!empty($value->Button_SC)) {

				$content = $content . '<p>[improveseo_buttons id="' . $value->Button_SC . '"]</p>';

			}



			if (!empty($value->GoogleMap_SC)) {

				$content = $content . '<p>[improveseo_googlemaps id="' . $value->GoogleMap_SC . '"]</p>';

			}



			if (!empty($value->Video_SC)) {

				$content = $content . '<p style="width:100%">[improveseo_video id="' . $value->Video_SC . '"]</p>';

			}

			$catids = [];

			if (!empty($value->cats)) {

				$categories = explode("||", $value->cats);

				foreach ($categories as $ckey => $cvalue) {

					if (!empty($cvalue)) {

						array_push($catids, $cvalue);

						//$catids = $value1.','.$cvalue;

					}

				}

			} else {

				$categories = '';

			}

		$tags = array();

		// ✅ Validate and decode image URL before using it
		$image_html = '';
		if (!empty($value->ai_image)) {
			$decoded_image = base64_decode($value->ai_image);
			error_log("saveContentInTaskList: Decoded ai_image for task " . $value->id . ": " . $decoded_image);
			
			// Validate that decoded image is a proper URL
			if (filter_var($decoded_image, FILTER_VALIDATE_URL)) {
				$image_html = "<img src='" . esc_url($decoded_image) . "' style='width:100%; margin-bottom: 100px;' alt='" . esc_attr($value->ai_title) . "'>";
				error_log("saveContentInTaskList: Valid image URL, including in post");
			} else {
				error_log("saveContentInTaskList: Invalid or missing image URL (value: '" . $decoded_image . "'), skipping image");
			}
		} else {
			error_log("saveContentInTaskList: No image data for task " . $value->id);
		}
		
		// Assemble content with optional image
		$fullcontent = $image_html . base64_decode($value->ai_content) . $content;
	
	// Sanitize title: remove all double quotes and trim any quotes from start/end
	$post_title = str_replace('"', '', $value->ai_title);
	$post_title = trim($post_title, '"');
		
		// Determine WordPress post_status (lowercase for WordPress)
		$post_status = 'publish';  // Default: publish immediately
		// Determine internal state value (Capital for tracking)
		$internal_state = 'Published';  // Will be 'Published' after creating live post

		if ($value->schedule_posts == 'draft_posts') {
			$post_status = 'draft';  // WordPress uses lowercase
			$internal_state = 'Draft';  // Internal tracking uses Capital
		} elseif ($value->schedule_posts == 'schedule_posts_input_wise') {
			$post_status = 'draft';  // Create as draft, will be published on scheduled date
			$internal_state = 'Scheduled';  // Keep scheduling intent until published
			$tags = array('This post will published on ' . $value->published_on . ' automatically.');
		}









			if ($value->assigning_authors == 'assigning_authors') {

				$post_author = $value->assigning_authors_value;

			}



			if ($value->assigning_authors == 'assigning_multi_authors') {
				$first_names = array(
					'John',
					'Jane',
					'Michael',
					'Emily',
					'David',
					'Sarah',
					'James',
					'Linda',
					'Robert',
					'Jessica',
					'Daniel',
					'Laura',
					'Chris',
					'Amy',
					'Mark',
					'Angela',
					'Steven',
					'Megan',
					'Paul',
					'Rachel',
					'Peter',
					'Hannah',
					'Kevin',
					'Sophia',
					'Edward',
					'Emma',
					'Jason',
					'Grace',
					'Tom',
					'Alice'
					// Add more names as needed to increase uniqueness

				);



				$last_names = array(
					'Smith',
					'Johnson',
					'Brown',
					'Williams',
					'Jones',
					'Miller',
					'Davis',
					'Garcia',
					'Martinez',
					'Taylor',
					'Wilson',
					'Moore',
					'Anderson',
					'Thomas',
					'Jackson',
					'White',
					'Harris',
					'Martin',
					'Thompson',
					'Lopez',
					'Gonzalez',
					'Clark',
					'Lewis',
					'Walker',
					'Hall',
					'Allen',
					'Young',
					'King',
					'Wright',
					'Scott'
					// Add more names as needed
				);



				// Pick a random first and last name with retry logic for username collision

				$max_attempts = 50;
				$attempt = 0;
				$post_author = null;
				$username = '';
				
				while ($attempt < $max_attempts && !$post_author) {
					$attempt++;
					
					$first_name = $first_names[array_rand($first_names)];
					$last_name = $last_names[array_rand($last_names)];
					
					// Add counter/timestamp to username after first 10 attempts
					if ($attempt <= 10) {
						$username = str_replace(" ", "", $first_name . $last_name);
					} elseif ($attempt <= 30) {
						$username = str_replace(" ", "", $first_name . $last_name) . $attempt;
					} else {
						$username = str_replace(" ", "", $first_name . $last_name) . time();
					}

					// Check if the username already exists
					if (username_exists($username)) {
						my_plugin_log('saveContentInTaskList: Username collision, retrying | Username: ' . $username . ' | Attempt: ' . $attempt);
						$post_author = username_exists($username);
						break; // Use existing author
					} else {
						// Define user information
						$user_data = array(
							'user_login' => $username,
							'user_pass' => wp_generate_password(12, true, true),
							'user_email' => strtolower($first_name) . time() . '@example.com',
							'first_name' => $first_name,
							'last_name' => $last_name,
							'role' => 'author',
						);

						my_plugin_log('saveContentInTaskList: Creating new author | Username: ' . $username . ' | Attempt: ' . $attempt);

						// Create the user
						$post_author = wp_insert_user($user_data);
						
						if (is_wp_error($post_author)) {
							my_plugin_log('saveContentInTaskList: Failed to create author | Username: ' . $username . ' | Error: ' . $post_author->get_error_message());
							$post_author = null; // Retry
						} else {
							my_plugin_log('saveContentInTaskList: Author created successfully | Username: ' . $username . ' | Author ID: ' . $post_author);
							break; // Success
						}
					}
				}
				
				// Fallback if all attempts failed
				if (!$post_author || is_wp_error($post_author)) {
					my_plugin_log('saveContentInTaskList: Failed to create/find author after ' . $max_attempts . ' attempts, using default author');
					$post_author = 1; // Fallback to admin
				}



				





			}



			my_plugin_log('saveContentInTaskList: Author assigned for post | Author ID: ' . $post_author . ' | Task ID: ' . $task_id);

			$post_array = array(

				'post_author' => $post_author,

				'post_content' => $fullcontent,

			'post_title' => $post_title,

				'post_type' => "post",

				'post_date' => $post_date,

				'post_status' => $post_status

			);







			my_plugin_log('saveContentInTaskList: Creating WordPress post for task ' . $task_id . ' | Title: "' . $value->ai_title . '" | Post Status: ' . $post_status);

			$post_id = wp_insert_post($post_array, true); // 'true' enables WP_Error return

			if (is_wp_error($post_id)) {
				$error_message = $post_id->get_error_message();
				my_plugin_log('saveContentInTaskList: ❌ ERROR - Failed to create WordPress post for task ' . $task_id . ': ' . $error_message);
				
				// Reset status back to Done so it can be retried
				$wpdb->query(
					$wpdb->prepare(
						"UPDATE `{$wpdb->prefix}improveseo_bulktasksdetails`
						 SET status = %s WHERE id = %d",
						'Done',
						$task_id
					)
				);
				
				return false;
			} else {
				my_plugin_log('saveContentInTaskList: ✅ WordPress post created successfully | Post ID: ' . $post_id . ' | Task ID: ' . $task_id);

				// ── Featured image (bulk) ───────────────────────────────────────
				try {
					if (
						get_option( 'improveseo_featured_images_enabled', '0' ) &&
						get_option( 'improveseo_featured_images_bulk',    '0' ) &&
						isset( $decoded_image ) &&
						filter_var( $decoded_image, FILTER_VALIDATE_URL )
					) {
						$att_id = improveseo_set_featured_image_from_url( $post_id, $decoded_image, $post_title );
						if ( $att_id ) {
							my_plugin_log( 'saveContentInTaskList: ✅ Featured image set | Post ID: ' . $post_id . ' | Attachment ID: ' . $att_id );
						} else {
							my_plugin_log( 'saveContentInTaskList: ⚠️ Featured image failed | Post ID: ' . $post_id . ' | Image URL: ' . $decoded_image );
						}
					}
				} catch ( \Throwable $e ) {
					error_log( 'improveseo featured-image (bulk) failed: ' . $e->getMessage() );
				}
				// ───────────────────────────────────────────────────────────────
			}


			// Write the AI-generated SEO meta onto the post: the plugin's own
			// improveseo_custom_* keys (shown on the post details page) plus the
			// active SEO plugin's fields so the live post carries real static meta.
			// Older tasks (pre-migration) have NULL/absent meta and are safely skipped.
			$new_meta_title = isset($value->meta_title) ? $value->meta_title : '';
			$new_meta_desc  = isset($value->meta_description) ? $value->meta_description : '';
			improveseo_write_seo_meta($post_id, $new_meta_title, $new_meta_desc);
			my_plugin_log('saveContentInTaskList: Wrote SEO meta to post ' . $post_id . ' | Meta title: ' . $new_meta_title);

			// Replace with your desired tags

			if (!empty($tags)) {

				wp_set_post_tags($post_id, $tags);
				my_plugin_log('saveContentInTaskList: Added tags to post ' . $post_id);

			}



			//$post_id = $wpdb->insert_id;



			if ((!empty($catids))) {

				wp_set_post_categories($post_id, $catids, false);
				my_plugin_log('saveContentInTaskList: Added ' . count($catids) . ' categories to post ' . $post_id);

			}



			my_plugin_log('saveContentInTaskList: Updating task ' . $task_id . ' | Setting post_id: ' . $post_id . ' | Setting state: ' . $internal_state . ' | Setting status: Done');
			
			$wpdb->query(

				$wpdb->prepare(

					"UPDATE `" . $wpdb->prefix . "improveseo_bulktasksdetails`

						SET state = %s, status = %s, post_id = %d WHERE id = %d",

					$internal_state,  // Use internal_state instead of post_status
					'Done',  // Set status back to Done now that post is created
					$post_id,

					$value->id

				)

			 );

			my_plugin_log('saveContentInTaskList: ✅ SUCCESS - Publishing complete for task ' . $task_id . ' | Post ID: ' . $post_id . ' | WP Status: ' . $post_status . ' | Internal State: ' . $internal_state);

            // Also refresh parent progress (keeps updated_at fresh in UI)
            if (isset($value->bulktask_id)) {
				my_plugin_log('saveContentInTaskList: Syncing parent bulk task progress for bulktask_id: ' . $value->bulktask_id);
                improveseo_sync_bulk_parent_progress((int) $value->bulktask_id);
            }

			my_plugin_log('This is a log message : ' . $value->id);

			//wp_send_json_success(array('status' => 'false',"message"=>'here 1 : '. $wpdb->last_error  ));

		}
	}







}

// End of saveContentInTaskList function

function getAudienceData($seed_keyword)
{
	$text = improveseo_call_auxiliary_api( 'audience_data', array(
		'seed_keyword' => (string) $seed_keyword,
	) );

	// Preserve historical contract: empty -> 0 (some callers check loosely).
	return $text !== '' ? $text : 0;
}

function bulkAiTitle($getAudienceData, $question, $keyword_name, $tone_of_voice)

{

	global $wpdb, $user_ID;



	// Your OpenAI API key

	$apiKey = get_option('improveseo_chatgpt_api_key');



	// The endpoint URL for OpenAI chat completions API (replace with the correct endpoint)

	$apiUrl = 'https://api.openai.com/v1/chat/completions';



	if ($tone_of_voice != '') {

		$tone_of_voice = 'voice of content must be ' . $tone_of_voice;

	}



	if ($question == 'normal') {

		$query_question = 'You are a content creator who creates SEO optimized titles for blog posts. You are provided a word or phrase that is searched by the reader, and the audience data of the reader, including demographic information, tone preferences, reading level preference and emotional needs/pain points. Using this information you should come up with the title that will be engaging and interesting for people who are described in the audience data and search provided word or phrase. In the title do not include emojis or hashtags. Limit characters not including spaces to 80-100. As an output, write just a title without explanation or introduction.

			Now generate a SEO optimized title based on the following information:

			Keyword: ' . $keyword_name . '

			Audience data: {' . $getAudienceData . '}';



		// $question = 'Create a compelling seo optimized blog post title based on the keyword `'.$seed_keyword.'` in the form of No Answer. No emojis. No hashtags. Limit characters not including spaces to 80-100. '.$content_type;

	} else if ($question == 'question') {

		$query_question = 'You are a content creator who creates SEO optimized titles for blog posts. You are provided a word or phrase that is searched by the reader, and the audience data of the reader, including demographic information, tone preferences, reading level preference and emotional needs/pain points. Using this information you should come up with a title that will be engaging and interesting for people who are described in the audience data and search provided word or phrase. Title should be formed as a question. In the title do not include emojis or hashtags. Limit characters not including spaces to 80-100. As an output, write just a title without explanation or introduction. 

				Now generate a SEO optimized title based on the following information:

					Keyword: ' . $keyword_name . '

					Audience data: {' . $getAudienceData . '}';

	} else {

		$query_question = $keyword_name;

	}



	// echo "????".$question;



	// Your chat messages

	$messages = [

		//['role' => 'system', 'content' => $getAudienceData],

		['role' => 'user', 'content' => $query_question]

		// ['role' => 'assistant', 'content' => 'Hello, how can I help you today?'],

	];





	// Additional parameters, including language setting (replace with actual parameters)

	$data = [

		'messages' => $messages,

		'model' => "gpt-4o"



		//'language' => 'fr',  // Specify the result language as French

	];



	// Set up cURL

	$ch = curl_init($apiUrl);



	// Set cURL options

	curl_setopt($ch, CURLOPT_POST, 1);

	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	curl_setopt($ch, CURLOPT_HTTPHEADER, [

		'Content-Type: application/json',

		'Authorization: Bearer ' . $apiKey,

	]);



	// Execute the cURL request

	$response = curl_exec($ch);



	// Check for cURL errors

	if (curl_errno($ch)) {

		echo 'Curl error: ' . curl_error($ch);

	}

	// Close cURL session

	curl_close($ch);



	// Decode and display the response

	$result = json_decode($response, true);

	return $result['choices'][0]['message']['content'];

}

function generateBulkAiImage($title, $AudienceData, $niche = 'general_blog')
{
    // Get API credentials from WordPress options
    $api_key = get_option('improveseo_api_key');
    $site_code = get_option('improveseo_site_code');

    // Validate credentials
    if (empty($api_key) || empty($site_code)) {
        error_log("generateBulkAiImage Error: Missing API credentials");
        return false; // ✅ Return false instead of error string
    }

    // Admin server configuration — v2 OpenAI image route
    $admin_server_url = 'https://imporve-seo-admin-server-nzbm.onrender.com';
    $api_endpoint = $admin_server_url . '/api/v1/content-v2/image';

    $seed_title = 'ai_image_' . date('YmdHis');

    // Prepare request payload for the v2 image route (documentary niche templates)
    $payload = array(
        'niche'   => $niche ?: 'general_blog',
        'title'   => $title,
        'size'    => '1536x1024',
        'quality' => 'medium',
    );
    $bp_city = get_option('improveseo_business_city', '');
    if ($bp_city) { $payload['city'] = $bp_city; }
    
    error_log("generateBulkAiImage: Calling API for title: " . $title);
    
    // Set up cURL for HTTP request to admin server
    $ch = curl_init($api_endpoint);
    
    // Configure cURL options
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Accept: application/json',
        'X-API-Key: ' . $api_key,
        'X-Site-Code: ' . $site_code
    ));
    curl_setopt($ch, CURLOPT_TIMEOUT, 120);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    
    // Execute the request
    $response = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Check for cURL errors
    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        error_log("generateBulkAiImage cURL Error: " . $error);
        return false; // ✅ Return false on cURL error
    }
    
    curl_close($ch);
    
    error_log("generateBulkAiImage: HTTP Status: " . $http_status);
    error_log("generateBulkAiImage: Response: " . substr($response, 0, 500));
    
    // ✅ CRITICAL FIX: Check HTTP status FIRST - do NOT use response if status is not 200
    if ($http_status !== 200) {
        error_log("generateBulkAiImage HTTP Error: Status $http_status, Response: " . $response);
        
        // Check for specific error types
        if ($http_status === 402) {
            error_log("generateBulkAiImage: Insufficient credits (HTTP 402)");
        } else if ($http_status === 401) {
            error_log("generateBulkAiImage: Authentication failed (HTTP 401)");
        } else if ($http_status === 400) {
            error_log("generateBulkAiImage: Bad request (HTTP 400)");
        } else if ($http_status >= 500) {
            error_log("generateBulkAiImage: Server error (HTTP " . $http_status . ")");
        }
        
        return false; // ✅ Return false - do NOT store HTTP status as image URL
    }
    
    // Parse JSON response - only if HTTP status was 200
    $result = json_decode($response, true);
    
    if (!$result || !is_array($result)) {
        error_log("generateBulkAiImage Invalid JSON Response: " . $response);
        return false; // ✅ Return false on invalid JSON
    }
    
    // ✅ Check success field in response
    if (!isset($result['success']) || $result['success'] !== true) {
        $error_msg = isset($result['error']) ? $result['error'] : 'Unknown error';
        error_log("generateBulkAiImage API Error: success=" . ($result['success'] ?? 'not set') . ", error=" . $error_msg);
        return false; // ✅ Return false if success is not true
    }
    
    // v2 returns a hosted image_url only when Supabase hosting is configured; otherwise a base64
    // data_uri. Support both: download a URL, or decode the data URI directly.
    $data = isset($result['data']) ? $result['data'] : array();
    $image_url = isset($data['image_url']) ? $data['image_url'] : '';
    $data_uri  = isset($data['data_uri']) ? $data['data_uri'] : '';

    $upload_dir = wp_upload_dir();
    $image_data = false;

    if (!empty($image_url) && filter_var($image_url, FILTER_VALIDATE_URL)) {
        $ch = curl_init($image_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $image_data = curl_exec($ch);
        $download_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if (!$image_data || $download_status !== 200) {
            error_log("generateBulkAiImage Error: Failed to download image from URL: " . $image_url . " (HTTP " . $download_status . ")");
            return false;
        }
    } elseif (!empty($data_uri)) {
        $comma = strpos($data_uri, ',');
        $b64 = ($comma !== false) ? substr($data_uri, $comma + 1) : $data_uri;
        $image_data = base64_decode($b64);
    } else {
        error_log("generateBulkAiImage Missing image_url/data_uri in response data");
        return false;
    }

    if (!$image_data) {
        error_log("generateBulkAiImage Error: Failed to obtain image bytes.");
        return false;
    }
    
    error_log("generateBulkAiImage: Image downloaded successfully (" . strlen($image_data) . " bytes)");
    
    // Generate unique filename
    $file_name = wp_unique_filename($upload_dir['path'], str_replace(" ", "_", str_replace(".", "", $seed_title)));
    $file_name = $file_name . '_' . rand();
    $file_path = $upload_dir['path'] . '/' . $file_name;
    
    if (file_put_contents($file_path, $image_data) !== false) {
        error_log("generateBulkAiImage: Image saved to: " . $file_path);
        
        // Convert to WebP if GD is available
        if (extension_loaded('gd')) {
            $original_image = imagecreatefromstring($image_data);
            
            if ($original_image !== false) {
                $webp_file_name = pathinfo($file_name, PATHINFO_FILENAME) . '.webp';
                $webp_file_path = $upload_dir['path'] . '/' . $webp_file_name;
                
                if (imagewebp($original_image, $webp_file_path, 90)) {
                    imagedestroy($original_image);
                    unlink($file_path);
                    
                    $final_url = $upload_dir['url'] . '/' . $webp_file_name;
                    error_log("generateBulkAiImage: Success! WebP image URL: " . $final_url);
                    return $final_url; // ✅ Return valid URL
                } else {
                    imagedestroy($original_image);
                    error_log("generateBulkAiImage: WebP conversion failed, using original");
                }
            }
        }
        
        $final_url = $upload_dir['url'] . '/' . $file_name;
        error_log("generateBulkAiImage: Success! Image URL: " . $final_url);
        return $final_url; // ✅ Return valid URL
    } else {
        error_log("generateBulkAiImage Error: Failed to save image file to: " . $file_path);
        return false; // ✅ Return false on save failure
    }
}
function createAIpost2bulk($seed_keyword, $keyword_selection, $seed_options, $nos_of_words, $content_lang, $shortcode = '', $is_single_keyword = '', $voice_tone = '', $point_of_view = '', $title = '', $call_to_action = '', $details_to_include = '', $for_testing_only = '', $niche = 'general_blog', $niche_data = array(), $brand_profile = array(), $cta_url = '')
{
	global $wpdb, $user_ID;

	my_plugin_log("createAIpost2bulk called for keyword: " . $seed_keyword);

	// Get API credentials from WordPress options
	$api_key = get_option('improveseo_api_key');
	$site_code = get_option('improveseo_site_code');
	
	my_plugin_log("API Key: " . ($api_key ? "Configured" : "MISSING") . ", Site Code: " . ($site_code ? "Configured" : "MISSING"));
	
	// Validate credentials
	if (empty($api_key) || empty($site_code)) {
		my_plugin_log("createAIpost2bulk Error: Missing API credentials!");
		error_log("createAIpost2bulk Error: Missing API credentials. Please configure API Key and Site Code in settings.");
		return array(
			'content' => "Error: Missing API credentials. Please configure your API Key and Site Code in ImproveSEO settings.",
			'meta_title' => '',
			'meta_description' => ''
		);
	}
	
	my_plugin_log("Connecting to admin server for bulk generation...");
	
	// Admin server configuration — v2 Claude content route
	$admin_server_url = 'https://imporve-seo-admin-server-nzbm.onrender.com';
    $api_endpoint = $admin_server_url . '/api/v1/content-v2/article';

	// Prepare request payload matching the /content-v2/article (GenerateV2Params) interface.
	$payload = array(
		'seed_keyword' => $seed_keyword,
		'niche' => $niche ?: 'general_blog',
		'title' => $title,
		'nos_of_words' => $nos_of_words,
		'content_lang' => $content_lang,
		'call_to_action' => $call_to_action,
		'humanize' => true,
	);
	if (!empty($niche_data)) {
		$payload['niche_data'] = (object) $niche_data;
	}
	if (!empty($brand_profile)) {
		$payload['brand_profile'] = $brand_profile;
	}
	if (!empty($cta_url)) {
		$payload['cta_url'] = $cta_url;
	}
	
	// Set up cURL for HTTP request to admin server
	$ch = curl_init($api_endpoint);
	
	// Configure cURL options
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json',
		'Accept: application/json',
		'X-API-Key: ' . $api_key,
		'X-Site-Code: ' . $site_code
	));
	curl_setopt($ch, CURLOPT_TIMEOUT, 480); // 2 minutes timeout for AI generation
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 100); // 10 seconds connection timeout
	
	// Execute the request
	$response = curl_exec($ch);
	$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	
	// Check for cURL errors
	if (curl_errno($ch)) {
		$error = curl_error($ch);
		curl_close($ch);
		error_log("createAIpost2 cURL Error: " . $error);
		return array(
			'content' => "Error: Failed to connect to content generation server. Please try again.",
			'meta_title' => '',
			'meta_description' => ''
		);
	}
	
	curl_close($ch);
	
	// Check HTTP status
	if ($http_status !== 200) {
		error_log("createAIpost2 HTTP Error: Status $http_status, Response: " . $response);
		// Keep the legacy prefix (the admin JS matches on it) and append the server's own
		// message, so a trial-ended block reads differently from plain out-of-credits.
		$err_body = json_decode($response, true);
		$err_msg  = ( is_array($err_body) && ! empty($err_body['error']) ) ? ' — ' . $err_body['error'] : '';
		return array(
			'content' => "Error: Content generation server returned error status: $http_status" . $err_msg,
			'meta_title' => '',
			'meta_description' => ''
		);
	}
	
	// Parse JSON response
	$result = json_decode($response, true);
	
	if (!$result || !isset($result['success'])) {
		error_log("createAIpost2 Invalid Response: " . $response);
		return array(
			'content' => "Error: Invalid response from content generation server.",
			'meta_title' => '',
			'meta_description' => ''
		);
	}
	
	if (!$result['success']) {
		$error_msg = isset($result['error']) ? $result['error'] : 'Unknown error';
		error_log("createAIpost2 API Error: " . $error_msg);
		return array(
			'content' => "Error: " . $error_msg,
			'meta_title' => '',
			'meta_description' => ''
		);
	}
	
	// Extract content from successful response
	if (!isset($result['data']['content'])) {
		error_log("createAIpost2 Missing Content: " . $response);
		return array(
			'content' => "Error: No content returned from generation server.",
			'meta_title' => '',
			'meta_description' => ''
		);
	}
	
	$content_final = $result['data']['content'];
	$meta_title = isset($result['data']['meta_title']) ? $result['data']['meta_title'] : '';
	// v2 returns the correctly-spelled key; keep the legacy fallback for safety.
	$meta_description = isset($result['data']['meta_description'])
		? $result['data']['meta_description']
		: (isset($result['data']['meta_descreption']) ? $result['data']['meta_descreption'] : '');
	
	
	
	// Add styling like original function
	$content_final = '<div class="main-content-section-improveseo">' . $content_final . '</div>';
	
	
	// Log the generation metadata if available (for debugging)
	if (isset($result['data']['generationMetadata'])) {
		$metadata = $result['data']['generationMetadata'];
		error_log("createAIpost2 Generation Metadata: " . json_encode($metadata));
	}
	
	// Return array with all three values
	return array(
		'content' => $content_final,
		'meta_title' => $meta_title,
		'meta_description' => $meta_description,
		'metadata' => isset($result['data']['generationMetadata']) ? $result['data']['generationMetadata'] : array()
	);
}


add_action('wp_ajax_multiPostData', 'multiPostData');

/**
 * Normalize a user-supplied Call-to-Action URL.
 *
 * A bare host like "example.com/contact" is upgraded to "https://example.com/contact".
 * Returns '' when the value is empty or cannot be made into a valid http(s) URL, so a
 * malformed CTA URL never blocks or breaks bulk project processing.
 */
if (!function_exists('improveseo_normalize_cta_url')) {
    function improveseo_normalize_cta_url($url)
    {
        $url = trim((string) $url);
        if ($url === '') {
            return '';
        }

        // Prepend a scheme when the user typed a bare domain.
        if (!preg_match('~^https?://~i', $url)) {
            $url = 'https://' . ltrim($url, '/');
        }

        $filtered = filter_var($url, FILTER_VALIDATE_URL);
        if ($filtered === false) {
            return '';
        }

        // Require a dotted host (or localhost) so "https://foo" is discarded.
        $host = parse_url($filtered, PHP_URL_HOST);
        if (!$host || (strpos($host, '.') === false && strtolower($host) !== 'localhost')) {
            return '';
        }

        return esc_url_raw($filtered);
    }
}

function multiPostData()

{





	global $wpdb;

	$uploaded_images_count = 0;

	$sequence_manually_images = 0;

	if ($_POST['aiImage'] == 'Multiple_images') {

		$uploaded_images_count = count($_POST['uploaded_images']);



	}





	$project_name = sanitize_text_field($_POST['project_name']);

	//$number_of_post_schedule = sanitize_text_field($_POST['number_of_post_schedule']);

	$number_of_post_schedule = (!empty($_POST['number_of_post_schedule'])) ? $_POST['number_of_post_schedule'] : "1";

	if ($project_name == '') {

		wp_send_json_success(array('status' => 'false', "message" => "Project name is required."));

	}

	// Validate keyword list exists and is not empty
	if (empty($_POST['keyword_list']) || trim($_POST['keyword_list']) == '') {
		wp_send_json_success(array('status' => 'false', "message" => "Keyword list is required. Please add at least one keyword."));
		return;
	}

	if (!empty($_POST['keyword_list'])) {

		$keyword_lists = explode("\n", $_POST['keyword_list']);
		
		// Filter out empty lines and validate keyword length (255 char DB limit)
		$keyword_lists = array_filter($keyword_lists, function($keyword) {
			return trim($keyword) !== '';
		});
		
		// Truncate keywords that exceed 255 characters and log warning
		$keyword_lists = array_map(function($keyword) {
			$keyword = trim($keyword);
			if (strlen($keyword) > 255) {
				my_plugin_log('multiPostData: Keyword exceeds 255 chars, truncating | Original length: ' . strlen($keyword) . ' | Keyword: ' . substr($keyword, 0, 50) . '...');
				return substr($keyword, 0, 255);
			}
			return $keyword;
		}, $keyword_lists);
		
		// Check if we have any valid keywords after filtering
		if (empty($keyword_lists) || count($keyword_lists) == 0) {
			wp_send_json_success(array('status' => 'false', "message" => "Keyword list is empty. Please add at least one keyword."));
			return;
		}



		$timeTaken = 2 * count($keyword_lists); // one post 3 mint

		$linkredirect = home_url('/') . 'wp-admin/admin.php?page=improveseo_bulkprojects';

		$schedule_posts = (!empty($_POST['schedule_posts'])) ? $_POST['schedule_posts'] : "";

		if ($schedule_posts == '') {

			wp_send_json_success(array('status' => 'false', "message" => "Publish - Schedule Posts required. Please check step 7."));

		}

		$number_of_post_schedule = (!empty($_POST['number_of_post_schedule'])) ? $_POST['number_of_post_schedule'] : "1";



		$schedule_frequency = (!empty($_POST['schedule_frequency'])) ? $_POST['schedule_frequency'] : "";



		// Start transaction to ensure data integrity
		my_plugin_log('multiPostData: Starting transaction for project creation | Project: ' . $_POST['project_name']);
		$wpdb->query('START TRANSACTION');
		
		try {
			// Check if the project name already exists - INSIDE transaction for atomicity
			$existing_project = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->prefix}improveseo_bulktasks WHERE name = %s",
					$project_name
				)
			);

			// If the project name exists, rollback and return error
			if ($existing_project > 0) {
				$wpdb->query('ROLLBACK');
				my_plugin_log('multiPostData: Project name already exists | Project: ' . $project_name);
				wp_send_json_success(array('status' => 'false', "message" => "Project name already exist."));
				return;
			}
			
		$wpdb->insert($wpdb->prefix . "improveseo_bulktasks", array(

			'name' => $_POST['project_name'],

			'number_of_tasks' => count($keyword_lists),

			'schedule_posts' => $schedule_posts,

			'number_of_post_schedule' => $number_of_post_schedule,

			'number_of_completed_task' => 0,

			'schedule_frequency' => $schedule_frequency,

			'state' => "Unpublished",

			'created_at' => current_time('mysql'),
			'updated_at' => current_time('mysql')

		));
		
		if ($wpdb->last_error) {
			throw new Exception('Failed to insert parent project: ' . $wpdb->last_error);
		}





		$lastid = $wpdb->insert_id;
		
		if (!$lastid) {
			throw new Exception('Failed to get parent project ID');
		}
		
		my_plugin_log('multiPostData: Parent project created | Project ID: ' . $lastid . ' | Total tasks: ' . count($keyword_lists));



		$pdate = date('Y-m-d');

		$number_of_post_schedule_count = $number_of_post_schedule;
		
		$tasks_inserted = 0;

		foreach ($keyword_lists as $key => $value) {

		if (!empty($value)) {

					// ✅ CRITICAL FIX: These form values MUST be captured - they're used by generateBulkAiContent()
					$keyword_list_name = (!empty($_POST['keyword_list_name'])) ? $_POST['keyword_list_name'] : "";
					$content_type = (!empty($_POST['content_type'])) ? $_POST['content_type'] : "";
					$select_exisiting_options = (!empty($_POST['select_exisiting_options'])) ? $_POST['select_exisiting_options'] : "";

					$details_to_include = (!empty($_POST['details_to_include'])) ? $_POST['details_to_include'] : "";

					$content_lang = (!empty($_POST['content_lang'])) ? $_POST['content_lang'] : "";

					$point_of_view = (!empty($_POST['point_of_view'])) ? $_POST['point_of_view'] : "";

					$call_to_action = (!empty($_POST['call_to_action'])) ? $_POST['call_to_action'] : "";

					// Fold the normalized CTA URL into call_to_action so it's stored and reaches
					// the generator without needing a separate DB column (avoids the
					// "Unknown column 'cta_url'" failure on installs where the column is missing).
					$cta_url = (!empty($_POST['cta_url'])) ? improveseo_normalize_cta_url($_POST['cta_url']) : "";
					if ($cta_url !== "") {
						$call_to_action = trim($call_to_action . "\n\nCTA URL: " . $cta_url);
					}

					$nos_of_words = (!empty($_POST['nos_of_words'])) ? $_POST['nos_of_words'] : "";

					$schedule_posts = (!empty($_POST['schedule_posts'])) ? $_POST['schedule_posts'] : "";

					$number_of_post_schedule = (!empty($_POST['number_of_post_schedule'])) ? $_POST['number_of_post_schedule'] : "";

					$schedule_frequency = (!empty($_POST['schedule_frequency'])) ? $_POST['schedule_frequency'] : "";

					$assigning_authors = (!empty($_POST['assigning_authors'])) ? $_POST['assigning_authors'] : "";

					$authors_number = (!empty($_POST['authors_number'])) ? $_POST['authors_number'] : "";

					$author_name = (!empty($_POST['author_name'])) ? $_POST['author_name'] : "";



					$category = '';

					if (!empty($_POST['cats'])) {

						foreach ($_POST['cats'] as $cats) {

							$category = $category . '||' . $cats;

						}

					}



					$testimonial = '';

					if (!empty($_POST['testimonial_SC'])) {

						foreach ($_POST['testimonial_SC'] as $testimonial_SC) {

							$testimonial = $testimonial . '||' . $testimonial_SC;

						}

					}



					if (($schedule_posts == 'schedule_all_posts')) {

						$published_on = date('Y-m-d');

					} elseif ($schedule_posts == 'schedule_posts_input_wise') {

						if ($schedule_frequency == 'per_day') {

							// Check if current date has slots available
							if ($number_of_post_schedule_count > 0) {

								// Assign to current date
								$published_on = $pdate;

								$number_of_post_schedule_count--;  // Use one slot

							} else {

								// No slots left, move to next day
								$pdate = date('Y-m-d', strtotime("+1 day", strtotime($pdate)));

								$number_of_post_schedule_count = $number_of_post_schedule;  // Reset to full quota

								$published_on = $pdate;

								$number_of_post_schedule_count--;  // Use one slot

							}

						} elseif ($schedule_frequency == 'per_week') {

							// Check if current week has slots available
							if ($number_of_post_schedule_count > 0) {

								// Assign to current date
								$published_on = $pdate;

								$number_of_post_schedule_count--;  // Use one slot

							} else {

								// No slots left, move to next week
								$pdate = date('Y-m-d', strtotime("+7 day", strtotime($pdate)));

								$number_of_post_schedule_count = $number_of_post_schedule;  // Reset to full quota

								$published_on = $pdate;

								$number_of_post_schedule_count--;  // Use one slot

							}

						}

					} else {

						$published_on = '';

					}



					$Button_SC = (!empty($_POST['Button_SC'])) ? $_POST['Button_SC'] : "";

					$GoogleMap_SC = (!empty($_POST['GoogleMap_SC'])) ? $_POST['GoogleMap_SC'] : "";
					
					$Video_SC = (!empty($_POST['Video_SC'])) ? $_POST['Video_SC'] : "";

					if ($authors_number == '') {

						$authors_number = $author_name;

					}
					
					// NEW IMAGE LOGIC - Get image method for THIS specific keyword
					$aiImage = isset($_POST['image_method_' . $key]) 
						? $_POST['image_method_' . $key] 
						: 'AI_image_one';
					
					// Get uploaded image URL for THIS specific keyword
					$ai_image = '';
					if ($aiImage == 'Multiple_images' && isset($_POST['keyword_image_url_' . $key])) {
						$image_url = $_POST['keyword_image_url_' . $key];
						if (!empty($image_url)) {
							$ai_image = base64_encode($image_url);
						}
					}
					// If aiImage is 'AI_image_one', leave ai_image empty for cron to generate

					$sequence_manually_images++;




					if ($schedule_posts == 'schedule_posts_input_wise') {

						$status = 'Scheduled';

					} else if ($schedule_posts == 'draft_posts') {

						$status = 'Draft';

					} else {
						// 'schedule_all_posts' - will be published immediately by cron
						// Set as Scheduled initially, will change to Published after post is created
						$status = 'Scheduled';

					}











					$insert_bulk_data = array(

						'bulktask_id' => $lastid,

						'keyword_list_name' => $keyword_list_name,

						'keyword_name' => $value,

						'tone_of_voice' => $content_type,

						'select_exisiting_options' => $select_exisiting_options,

						'details_to_include' => $details_to_include,

						'content_lang' => $content_lang,

						'point_of_view' => $point_of_view,

						'call_to_action' => $call_to_action,

						'nos_of_words' => $nos_of_words,

						'aiImage' => $aiImage,

						'schedule_posts' => $schedule_posts,

						'number_of_post_schedule' => $number_of_post_schedule,

						'assigning_authors' => $assigning_authors,

						'assigning_authors_value' => $authors_number,

						'cats' => $category,

						'ai_image' => $ai_image,

						'testimonial' => $testimonial,

						'schedule_frequency' => $schedule_frequency,

						'Button_SC' => $Button_SC,

						'GoogleMap_SC' => $GoogleMap_SC,

						'Video_SC' => $Video_SC,

						'status' => 'Pending',

						'state' => $status,

						'published_on' => $published_on,

						'created_at' => current_time('mysql'),

						'updated_at' => current_time('mysql'),

					);

					$insert_result = $wpdb->insert($wpdb->prefix . "improveseo_bulktasksdetails", $insert_bulk_data);
					
					if ($insert_result === false) {
						throw new Exception('Failed to insert child task #' . ($tasks_inserted + 1) . ' for keyword: ' . substr($value, 0, 50) . ' | Error: ' . $wpdb->last_error);
					}
					
					$tasks_inserted++;
					my_plugin_log('multiPostData: Inserted child task ' . $tasks_inserted . '/' . count($keyword_lists) . ' | Task ID: ' . $wpdb->insert_id . ' | Keyword: ' . substr($value, 0, 50));

					$json_d = json_encode($insert_bulk_data);

					if (empty($json_d)) {

						my_plugin_log('Post created --> ' . $json_d);

						return true;

					}

					$sequence_manually_images++;



			}

		}
		
		// All tasks inserted successfully, commit transaction
		// This MUST be outside the foreach loop to ensure it always executes
		if ($tasks_inserted == count($keyword_lists)) {
			$wpdb->query('COMMIT');
			my_plugin_log('multiPostData: Transaction committed successfully | Project ID: ' . $lastid . ' | Tasks inserted: ' . $tasks_inserted);
		} else {
			// Mismatch in expected vs actual inserts - rollback
			$wpdb->query('ROLLBACK');
			my_plugin_log('multiPostData: Transaction rolled back - task count mismatch | Expected: ' . count($keyword_lists) . ' | Inserted: ' . $tasks_inserted);
			throw new Exception('Task insertion incomplete: ' . $tasks_inserted . '/' . count($keyword_lists) . ' tasks inserted');
		}

		if (function_exists('improveseo_notify_bulk_status')) {
			improveseo_notify_bulk_status('task_created', array(
				'task_name'   => $project_name,
				'total_tasks' => count($keyword_lists),
				'project_id'  => $lastid,
			));
		}

		//wp_send_json_success(array('status' => 'false',"message"=>'here 1 : '. $wpdb->last_error  ));

		wp_send_json_success(array('status' => 'success', "linkredirect" => $linkredirect));
			
		} catch (Exception $e) {
			// Rollback transaction on any error
			$wpdb->query('ROLLBACK');
			my_plugin_log('multiPostData: Transaction rolled back due to error | Error: ' . $e->getMessage());
			wp_send_json_success(array('status' => 'false', "message" => "Failed to create project: " . $e->getMessage()));
		}

	} else {

		wp_send_json_success(array('status' => 'success', "message" => "Keywords should not empty."));

	}

}



add_action('wp_ajax_generateAIMeta', 'generateAIMeta');

function generateAIMeta()

{

	$aigeneratedtitle = $_REQUEST['aigeneratedtitle'];

	$seed_keyword = $_REQUEST['seedkeyword'];

	$out = [];

	// Enforce the SEO length limits (title <=60, description <=160): rerun the prompt if
	// over the limit, then trim as a final guard — same helpers as the Step 4 flow.
	$title_raw = generateMetaTitle($aigeneratedtitle, $seed_keyword);
	$desc_raw  = generateMetaDescreption($aigeneratedtitle, $seed_keyword);

	$out['title'] = function_exists('improveseo_enforce_meta_title_length')
		? improveseo_enforce_meta_title_length($title_raw, $aigeneratedtitle, $seed_keyword)
		: $title_raw;
	$out['descreption'] = function_exists('improveseo_enforce_meta_description_length')
		? improveseo_enforce_meta_description_length($desc_raw, $aigeneratedtitle, $seed_keyword, '')
		: $desc_raw;

	wp_send_json_success($out);

	//die($output);

}



function multi_form_data()
{
	$keyword_list = isset( $_REQUEST['keyword_list'] ) ? sanitize_textarea_field( $_REQUEST['keyword_list'] ) : '';

	if ( empty( trim( $keyword_list ) ) ) {
		echo 'Error: No keywords provided.';
		die();
	}

	$text = improveseo_call_auxiliary_api( 'keyword_context', array( 'seed_keyword' => $keyword_list ) );

	if ( empty( $text ) ) {
		echo 'Error: Could not generate context. Please check your API credentials and try again.';
		die();
	}

	echo str_replace( "'", '`', $text );
	die();
}



add_action('wp_ajax_multi_form_data', 'multi_form_data');



function getGPTdata()

{

	$seed_type = $_REQUEST['seedtype'];

	$seed_keyword = $_REQUEST['seedkeyword'];

	$content_type = $_REQUEST['contenttype'];



	$getAudienceData = getAudienceData($seed_keyword);





	//   add_action('init', function() {

	// 		if (!isset($_COOKIE['AudienceData'])) {

	// 			setcookie('AudienceData', 'getAudienceData', strtotime('+1 day'));

	// 		}

	// 	});



	setcookie("AudienceData", $getAudienceData, time() + (86400 * 30), "/"); // 86400 = 1 day

	// generateTitle() echoes the title directly; just terminate the AJAX response.
	generateTitle($seed_type, $seed_keyword, $content_type, $getAudienceData);



	die();

}

add_action('wp_ajax_nopriv_getGPTdata', 'getGPTdata');

add_action('wp_ajax_getGPTdata', 'getGPTdata');

function rudr_multiple_img_upload_metabox($metaboxes)

{

	$metaboxes[] = array(

		'id' => 'my_metabox',

		'name' => 'Meta Box',

		'post_type' => array('page'),

		'fields' => array(

			array(

				'id' => 'my_field',

				'label' => 'Images',

				'type' => 'gallery'

			),

		)

	);

	return $metaboxes;

}

add_filter('cmb2_meta_boxes', 'rudr_multiple_img_upload_metabox');

add_action('wp_ajax_re_generate_post', 're_generate_post');

//add_action('wp_ajax_workdex_builder_update_ajax', 'improveseo_builder_update');



function re_generate_post()
{
	global $wpdb;
	$id = intval($_REQUEST['id']);
	
	my_plugin_log('re_generate_post: Starting regeneration | Task ID: ' . $id);
	
	// Clear old content and post_id so saveContentInTaskList picks it up after cron regenerates.
	// post_id must be set to SQL NULL (not 0/'') because saveContentInTaskList queries post_id IS NULL.
	$update_result = $wpdb->query(
		$wpdb->prepare(
			"UPDATE `{$wpdb->prefix}improveseo_bulktasksdetails`
			 SET ai_content = %s, ai_title = %s, ai_image = %s, post_id = NULL, status = %s, updated_at = %s
			 WHERE id = %d",
			'', '', '', 'Pending', current_time('mysql'), $id
		)
	);
	
	if ($update_result === false) {
		my_plugin_log('re_generate_post: Failed to reset task | Task ID: ' . $id . ' | Error: ' . $wpdb->last_error);
		wp_send_json_error(array('status' => 'false', 'message' => 'Failed to reset task for regeneration'));
		return;
	}
	
	my_plugin_log('re_generate_post: Task reset to Pending | Task ID: ' . $id . ' | Rows affected: ' . $update_result);
	
	// Verify the update was successful
	$task = $wpdb->get_row($wpdb->prepare(
		"SELECT status, ai_content, ai_title FROM {$wpdb->prefix}improveseo_bulktasksdetails WHERE id = %d",
		$id
	));
	
	if ($task && $task->status === 'Pending' && empty($task->ai_content)) {
		my_plugin_log('re_generate_post: Verification successful | Task ID: ' . $id . ' | Status: Pending | Content cleared');
		wp_send_json_success(array('status' => 'true', 'message' => 'Content cleared. Regeneration will start on next cron run.'));
	} else {
		my_plugin_log('re_generate_post: Verification failed | Task ID: ' . $id . ' | Status: ' . ($task ? $task->status : 'NULL'));
		wp_send_json_error(array('status' => 'false', 'message' => 'Task reset but verification failed'));
	}
}





function generateTitle_mutli($proj_name, $keyword_list, $content_type)

{

	global $wpdb, $user_ID;



	// Your OpenAI API key

	$apiKey = get_option('improveseo_chatgpt_api_key');



	// The endpoint URL for OpenAI chat completions API (replace with the correct endpoint)

	$apiUrl = 'https://api.openai.com/v1/chat/completions';



	if ($content_type != '') {

		$content_type = 'voice of content must be ' . $content_type;

	}

	$question = 'In a few sentences (max 500 characters not including spaces) explain what the list of keywords provided is about. What is the common thread. Goal is to create a context that is relevant to all keywords in that list. Do not use the word ‘keyword list’ or ‘list’ in the output. Start the output with "The context is..."

		 Keyword List: ' . $keyword_list . ' ' . $content_type;



	// Your chat messages

	$messages = [

		// ['role' => 'system', 'content' => 'You are a helpful assistant.'],

		['role' => 'user', 'content' => $question]

		// ['role' => 'assistant', 'content' => 'Hello, how can I help you today?'],

	];



	// Additional parameters, including language setting (replace with actual parameters)

	$data = [

		'messages' => $messages,

		'model' => "gpt-4o"



		//'language' => 'fr',  // Specify the result language as French

	];



	// Set up cURL

	$ch = curl_init($apiUrl);



	// Set cURL options

	curl_setopt($ch, CURLOPT_POST, 1);

	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	curl_setopt($ch, CURLOPT_HTTPHEADER, [

		'Content-Type: application/json',

		'Authorization: Bearer ' . $apiKey,

	]);



	// Execute the cURL request

	$response = curl_exec($ch);



	// Check for cURL errors

	if (curl_errno($ch)) {

		echo 'Curl error: ' . curl_error($ch);

	}



	// Close cURL session

	curl_close($ch);



	// Decode and display the response

	$result = json_decode($response, true);



	//   print_r($result);

	//   die();

	if (!empty($result['choices'][0]['message']['content'])) {

		return $result;

	} else {

		return "ChatGpt Request Error";

	}

}

function generateTitle($seed_type, $seed_keyword, $content_type, $getAudienceData)

{

	// seed_option1 uses the keyword as-is, so no AI title is generated here.
	if ($seed_type != 'seed_option2' && $seed_type != 'seed_option3') {

		echo '';

		return;

	}

	// Route through the admin server's /auxiliary endpoint (no credit cost) instead of
	// hitting OpenAI directly via improveseo_chatgpt_api_key, matching getAudienceData().
	// seed_option3 wants the title phrased as a question.
	$title_type = ($seed_type == 'seed_option3') ? 'question' : 'normal';

	$content = improveseo_call_auxiliary_api('title', array(
		'seed_keyword'  => (string) $seed_keyword,
		'audience_data' => (string) $getAudienceData,
		'title_type'    => $title_type,
	));

	// Strip any surrounding quotes the model may add, then mirror the historical
	// contract of swapping single quotes for backticks so they don't break the markup.
	$content = preg_replace('~^[\'"]?(.*?)[\'"]?$~', '$1', $content);

	echo str_replace("'", '`', $content);

}

?>