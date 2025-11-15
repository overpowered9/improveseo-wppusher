<?php
/**
 * AJAX handler for keyword-specific image uploads in bulk post creation
 * Handles individual image uploads for each keyword
 */

add_action('wp_ajax_upload_keyword_image', 'improveseo_upload_keyword_image');

function improveseo_upload_keyword_image() {
    // Verify nonce for security
    if (!current_user_can('upload_files')) {
        wp_send_json_error('You do not have permission to upload files.');
        return;
    }

    // Check if image file is present
    if (!isset($_FILES['image']) || empty($_FILES['image']['name'])) {
        wp_send_json_error('No image file provided.');
        return;
    }

    // Get keyword
    $keyword = isset($_POST['keyword']) ? sanitize_text_field($_POST['keyword']) : '';
    
    if (empty($keyword)) {
        wp_send_json_error('Keyword is required.');
        return;
    }

    // Handle the file upload
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');

    // Set up the upload overrides
    $upload_overrides = array(
        'test_form' => false,
        'mimes' => array(
            'jpg|jpeg|jpe' => 'image/jpeg',
            'gif' => 'image/gif',
            'png' => 'image/png',
            'webp' => 'image/webp'
        )
    );

    // Handle the upload
    $uploaded_file = wp_handle_upload($_FILES['image'], $upload_overrides);

    if (isset($uploaded_file['error'])) {
        wp_send_json_error($uploaded_file['error']);
        return;
    }

    // Get the uploaded file information
    $file_path = $uploaded_file['file'];
    $file_url = $uploaded_file['url'];
    $file_type = $uploaded_file['type'];

    // Prepare attachment data
    $attachment_data = array(
        'post_mime_type' => $file_type,
        'post_title' => sanitize_file_name(pathinfo($file_path, PATHINFO_FILENAME)),
        'post_content' => '',
        'post_status' => 'inherit',
        'post_excerpt' => 'Keyword: ' . $keyword
    );

    // Insert the attachment
    $attachment_id = wp_insert_attachment($attachment_data, $file_path);

    if (is_wp_error($attachment_id)) {
        wp_send_json_error('Failed to create attachment.');
        return;
    }

    // Generate attachment metadata
    $attachment_metadata = wp_generate_attachment_metadata($attachment_id, $file_path);
    wp_update_attachment_metadata($attachment_id, $attachment_metadata);

    // Return success with image data
    wp_send_json_success(array(
        'attachment_id' => $attachment_id,
        'url' => $file_url,
        'keyword' => $keyword,
        'message' => 'Image uploaded successfully for keyword: ' . $keyword
    ));
}
