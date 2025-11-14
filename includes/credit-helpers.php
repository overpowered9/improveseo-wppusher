<?php
/**
 * ImproveSEO Credit System Helper Functions
 * 
 * These functions manage user credits for AI operations
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Get user's current credit balance
 * 
 * @param int $user_id User ID (defaults to current user)
 * @return int Credit balance
 */
function improveseo_get_user_credits($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    $credits = get_user_meta($user_id, 'improveseo_credits', true);
    return $credits ? intval($credits) : 0;
}

/**
 * Check if user has sufficient credits
 * 
 * @param int $credits_needed Number of credits required
 * @param int $user_id User ID (defaults to current user)
 * @return bool True if sufficient credits
 */
function improveseo_has_sufficient_credits($credits_needed, $user_id = null) {
    $current_credits = improveseo_get_user_credits($user_id);
    return $current_credits >= $credits_needed;
}

/**
 * Deduct credits from user's balance
 * 
 * @param int $credits_to_deduct Number of credits to deduct
 * @param int $user_id User ID (defaults to current user)
 * @return int|bool New credit balance, or false on failure
 */
function improveseo_deduct_credits($credits_to_deduct, $user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    $current_credits = improveseo_get_user_credits($user_id);
    
    if ($current_credits < $credits_to_deduct) {
        return false; // Insufficient credits
    }
    
    $new_credits = $current_credits - $credits_to_deduct;
    update_user_meta($user_id, 'improveseo_credits', $new_credits);
    
    return $new_credits;
}

/**
 * Add credits to user's balance
 * 
 * @param int $credits_to_add Number of credits to add
 * @param int $user_id User ID (defaults to current user)
 * @return int New credit balance
 */
function improveseo_add_credits($credits_to_add, $user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    $current_credits = improveseo_get_user_credits($user_id);
    $new_credits = $current_credits + $credits_to_add;
    
    update_user_meta($user_id, 'improveseo_credits', $new_credits);
    
    return $new_credits;
}

/**
 * Return structured credit error for AJAX responses
 * 
 * @param int $credits_needed Credits required for action
 * @param int $user_id User ID (defaults to current user)
 * @return void (outputs JSON and dies)
 */
function improveseo_send_credit_error($credits_needed, $user_id = null) {
    $current_credits = improveseo_get_user_credits($user_id);
    
    wp_send_json_error([
        'code' => 'INSUFFICIENT_CREDITS',
        'message' => 'You have run out of credits for this action.',
        'creditsNeeded' => $credits_needed,
        'currentCredits' => $current_credits
    ]);
    
    wp_die();
}

/**
 * Log credit operation for debugging
 * 
 * @param int $user_id User ID
 * @param int $credits_used Credits used
 * @param string $action Action performed
 */
function improveseo_log_credit_usage($user_id, $credits_used, $action) {
    $credits_remaining = improveseo_get_user_credits($user_id);
    
    error_log(sprintf(
        'ImproveSEO Credit Usage: User %d, Action: %s, Used: %d, Remaining: %d',
        $user_id,
        $action,
        $credits_used,
        $credits_remaining
    ));
}
