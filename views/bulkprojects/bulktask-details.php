<?php

use ImproveSEO\View;

?>

<?php View::startSection('breadcrumbs') ?>

<a href="<?= admin_url('admin.php?page=improveseo_dashboard') ?>">Improve SEO</a>

&raquo;

<a href="<?= admin_url('admin.php?page=improveseo_bulkprojects') ?>">Bulk Projects List</a>

&raquo;

<?php if ($task->bulktask_id): ?>
<a href="<?= admin_url('admin.php?page=improveseo_bulkprojects&action=viewAllTasks&id=' . $task->bulktask_id) ?>"><?= esc_html($parent_name) ?></a>
&raquo;
<?php endif; ?>

<span>Post Details</span>

<?php View::endSection('breadcrumbs') ?>

<?php View::startSection('content') ?>

<?php
// Helper to display a value or fallback
function btd_val($val, $default = 'N/A') {
    if ($val === null) return $default;
    // Trim leading/trailing whitespace — stored details/CTA text often carries
    // blank lines that, under white-space: pre-wrap, push the text to the
    // bottom of the field. Internal line breaks are preserved.
    $val = trim((string) $val);
    return $val === '' ? $default : esc_html($val);
}

function btd_tone_label($val) {
    return ($val && $val !== '') ? ucfirst(esc_html($val)) : 'N/A';
}

function btd_seed_option_label($val) {
    $map = array(
        'seed_option1' => 'Exact Keyword as Title',
        'seed_option2' => 'Smart Title (AI-Generated)',
        'seed_option3' => 'Question-Style Title (AI-Generated)',
    );
    return isset($map[$val]) ? $map[$val] : btd_val($val);
}

function btd_pov_label($val) {
    $map = array(
        'Whatever is suited to keyword'          => 'Auto (AI Decides)',
        'Second Person (you,your,yours)'         => 'Speaking to the Reader ("you", "your")',
        'First person plural (we,us,our,ours)'   => 'Business Voice ("we", "our")',
        'First person singular (I,me,my,mine)'   => 'Personal Voice ("I", "my")',
        'none'                                   => 'Auto (AI Decides)',
    );
    return isset($map[$val]) ? $map[$val] : btd_val($val);
}

function btd_image_label($val) {
    $map = array(
        'AI_image' => 'AI Generated Image (Single)',
        'AI_image_one' => 'AI Generated Image (One)',
        'manually_promt_image' => 'AI Image – Edit Prompt',
        'Manually_image' => 'Manual Image Upload',
        'google_image' => 'Google Image',
        'pexels_image' => 'Pexels Image',
        'pixabay_image' => 'Pixabay Image',
    );
    return isset($map[$val]) ? $map[$val] : btd_val($val);
}

function btd_schedule_label($val) {
    $map = array(
        'schedule_all_posts'        => 'Publish Now',
        'publish_immediately'       => 'Publish Now',
        'draft_posts'               => 'Save As Draft',
        'schedule_posts_input_wise' => 'Schedule',
    );
    return isset($map[$val]) ? $map[$val] : btd_val($val);
}

// Combine schedule frequency + posts-per-schedule into a human phrase like "2 posts/day".
// Only meaningful when the schedule type is an actual publishing schedule.
function btd_schedule_cadence($task) {
    if ($task->schedule_posts !== 'schedule_posts_input_wise') {
        return 'N/A';
    }
    $count = intval($task->number_of_post_schedule);
    if ($count < 1) {
        return 'N/A';
    }
    $unit = ($task->schedule_frequency === 'per_week') ? 'week' : 'day';
    return $count . ' post' . ($count === 1 ? '' : 's') . '/' . $unit;
}

// Content-generation status vocabulary, matching the "Processing" column on the
// View All Posts screen so the same post never reads differently in two places.
function btd_content_status_label($status) {
    $map = array(
        'Done'       => 'Completed',
        'Processing' => 'Generating',
        'Pending'    => 'Queued',
        'Stoped'     => 'Canceled',
        'Draft'      => 'Completed',
    );
    return isset($map[$status]) ? $map[$status] : 'Queued';
}

?>

<style>
    .btd-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        padding: 0 20px 20px;
    }
    @media (max-width: 782px) {
        .btd-grid {
            grid-template-columns: 1fr;
        }
    }
    .btd-card {
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 10px;
        box-shadow: 0 1px 4px rgba(0,0,0,.06);
    }
    .btd-card-header {
        padding: 12px 16px;
        border-bottom: 1px solid #dee2e6;
        background: #f8f9fa;
        font-weight: 600;
        font-size: 14px;
        color: #1d2327;
        border-radius: 10px 10px 0 0;
    }
    .btd-card-body {
        padding: 16px;
    }
    .btd-row {
        display: flex;
        padding: 8px 0;
        border-bottom: 1px solid #f0f0f1;
    }
    .btd-row:last-child {
        border-bottom: none;
    }
    .btd-label {
        flex: 0 0 170px;
        font-weight: 500;
        color: #50575e;
        font-size: 13px;
    }
    /* Stacked rows switch to flex-direction: column, where the label's
       `flex: 0 0 170px` basis applies to HEIGHT — forcing a 170px-tall label
       that shoves the value far down. Reset to natural height when stacked. */
    .btd-row[style*="column"] .btd-label {
        flex: 0 0 auto;
    }
    .btd-value {
        flex: 1;
        color: #1d2327;
        font-size: 13px;
        word-break: break-word;
    }
    .btd-value.na {
        color: #a7aaad;
        font-style: italic;
    }
    .btd-badge {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
    }
    .btd-badge-done { background: #d4edda; color: #155724; }
    .btd-badge-published { background: #d4edda; color: #155724; }
    .btd-badge-pending { background: #fff3cd; color: #856404; }
    .btd-badge-draft { background: #fff3cd; color: #856404; }
    .btd-badge-stoped { background: #f8d7da; color: #721c24; }
    .btd-badge-scheduled { background: #cce5ff; color: #004085; }
    .btd-full-width {
        grid-column: 1 / -1;
    }
    .btd-content-preview {
        max-height: 300px;
        overflow-y: auto;
        padding: 12px;
        background: #f9f9f9;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        font-size: 13px;
        line-height: 1.6;
    }
    .btd-image-preview {
        max-width: 300px;
        max-height: 200px;
        border-radius: 4px;
        border: 1px solid #e0e0e0;
    }
</style>

<div class="global-wrap">
    <div class="head-bar">
        <img src="<?php echo WT_URL . '/assets/images/latest-images/seo-latest-logo.svg' ?>" alt="project-list-logo">
        <h1>ImproveSEO | Post Details</h1>
    </div>
    <div class="box-top">
        <ul class="breadcrumb-seo">
            <li><a href="<?= admin_url('admin.php?page=improveseo_dashboard') ?>">Improve SEO</a></li>
            <li><a href="<?= admin_url('admin.php?page=improveseo_bulkprojects') ?>">Bulk Projects</a></li>
            <?php if ($task->bulktask_id): ?>
                <li><a href="<?= admin_url('admin.php?page=improveseo_bulkprojects&action=viewAllTasks&id=' . $task->bulktask_id) ?>"><?= esc_html($parent_name) ?></a></li>
            <?php endif; ?>
            <li><?= esc_html($task->keyword_name) ?></li>
        </ul>
        <div class="import-export-btn">
            <?php if ($task->bulktask_id): ?>
                <a href="<?= admin_url('admin.php?page=improveseo_bulkprojects&action=viewAllTasks&id=' . $task->bulktask_id) ?>" style="text-decoration:none;">
                    <button>← Back to Post List</button>
                </a>
            <?php else: ?>
                <a href="<?= admin_url('admin.php?page=improveseo_bulkprojects') ?>" style="text-decoration:none;">
                    <button>← Back to Bulk Projects</button>
                </a>
            <?php endif; ?>
            <?php if ($associated_post && $post_url): ?>
                <a href="<?= esc_url($post_url) ?>" target="_blank" style="text-decoration:none;">
                    <button class="active">View Post</button>
                </a>
            <?php endif; ?>
            <?php if (!empty($task->post_id)): ?>
                <a href="<?= admin_url('post.php?action=edit&post=' . $task->post_id) ?>" target="_blank" style="text-decoration:none;">
                    <button>Edit Post</button>
                </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="improve-seo-container">
    <div class="project-lists" style="padding: 20px 0 0;">

    <div class="btd-grid">
        <!-- Card 1: Basic Information -->
        <div class="btd-card">
            <div class="btd-card-header">Basic Information</div>
            <div class="btd-card-body">
                <div class="btd-row">
                    <div class="btd-label">Keyword</div>
                    <div class="btd-value"><?= btd_val($task->keyword_name) ?></div>
                </div>
                <div class="btd-row">
                    <div class="btd-label">Parent Project</div>
                    <div class="btd-value">
                        <?php if ($parent_name): ?>
                            <a href="<?= admin_url('admin.php?page=improveseo_bulkprojects&action=viewAllTasks&id=' . $task->bulktask_id) ?>"><?= esc_html($parent_name) ?></a>
                        <?php else: ?>
                            <span class="na">N/A</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="btd-row">
                    <div class="btd-label">Keyword List</div>
                    <div class="btd-value <?= empty($task->keyword_list_name) ? 'na' : '' ?>">
                        <?= btd_val($task->keyword_list_name) ?>
                    </div>
                </div>
                <div class="btd-row">
                    <div class="btd-label">Content Status</div>
                    <div class="btd-value">
                        <?php
                        $status = $task->status;
                        $badge = 'btd-badge-pending';
                        if ($status === 'Done') $badge = 'btd-badge-done';
                        elseif ($status === 'Stoped') $badge = 'btd-badge-stoped';
                        elseif ($status === 'Draft') $badge = 'btd-badge-draft';
                        ?>
                        <span class="btd-badge <?= $badge ?>"><?= esc_html(btd_content_status_label($status)) ?></span>
                    </div>
                </div>
                <div class="btd-row">
                    <div class="btd-label">Post Status</div>
                    <div class="btd-value">
                        <?php
                        // Same rule as the View All Posts screen: the post doesn't exist until
                        // content generation finishes, so status gates what Post Status can be.
                        if ($task->status === 'Stoped') {
                            $post_status_label = 'Canceled';
                            $state_badge = 'btd-badge-stoped';
                        } elseif ($task->status !== 'Done') {
                            $post_status_label = 'Pending Creation';
                            $state_badge = 'btd-badge-pending';
                        } else {
                            $state = $task->state;
                            if ($state === 'Published') {
                                $post_status_label = 'Published';
                                $state_badge = 'btd-badge-published';
                            } elseif ($state === 'Draft') {
                                $post_status_label = 'Draft';
                                $state_badge = 'btd-badge-draft';
                            } elseif ($state === 'Scheduled') {
                                $post_status_label = 'Scheduled';
                                $state_badge = 'btd-badge-scheduled';
                            } else {
                                $post_status_label = 'Pending Creation';
                                $state_badge = 'btd-badge-pending';
                            }
                        }
                        ?>
                        <span class="btd-badge <?= $state_badge ?>"><?= esc_html($post_status_label) ?></span>
                    </div>
                </div>
                <div class="btd-row">
                    <div class="btd-label">Published On</div>
                    <div class="btd-value <?= (empty($task->published_on) || $task->published_on === '0000-00-00 00:00:00') ? 'na' : '' ?>">
                        <?php
                        if (empty($task->published_on) || $task->published_on === '0000-00-00 00:00:00' || $task->status === 'Stoped') {
                            echo 'N/A';
                        } else {
                            echo esc_html(date('M j, Y g:i A', strtotime($task->published_on)));
                        }
                        ?>
                    </div>
                </div>
                <div class="btd-row">
                    <div class="btd-label">Created</div>
                    <div class="btd-value"><?= $task->created_at ? esc_html(date('M j, Y g:i A', strtotime($task->created_at))) : 'N/A' ?></div>
                </div>
                <div class="btd-row">
                    <div class="btd-label">Updated</div>
                    <div class="btd-value"><?= $task->updated_at ? esc_html(date('M j, Y g:i A', strtotime($task->updated_at))) : 'N/A' ?></div>
                </div>
                <?php if ($associated_post): ?>
                <div class="btd-row">
                    <div class="btd-label">WordPress Post</div>
                    <div class="btd-value">
                        <a href="<?= esc_url($post_url) ?>" target="_blank"><?= esc_html($associated_post->post_title) ?></a>
                        (ID: <?= $associated_post->ID ?>)
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Card 2: AI Content Settings -->
        <div class="btd-card">
            <div class="btd-card-header">AI Content Settings</div>
            <div class="btd-card-body">
                <div class="btd-row">
                    <div class="btd-label">Title Type</div>
                    <div class="btd-value <?= empty($task->select_exisiting_options) ? 'na' : '' ?>">
                        <?= btd_seed_option_label($task->select_exisiting_options) ?>
                    </div>
                </div>
                <div class="btd-row">
                    <div class="btd-label">Tone of Voice</div>
                    <div class="btd-value <?= empty($task->tone_of_voice) ? 'na' : '' ?>">
                        <?= btd_tone_label($task->tone_of_voice) ?>
                    </div>
                </div>
                <div class="btd-row">
                    <div class="btd-label">Article Size</div>
                    <div class="btd-value <?= empty($task->nos_of_words) ? 'na' : '' ?>">
                        <?= btd_val($task->nos_of_words) ?>
                    </div>
                </div>
                <div class="btd-row">
                    <div class="btd-label">Point of View</div>
                    <div class="btd-value <?= empty($task->point_of_view) ? 'na' : '' ?>">
                        <?= btd_pov_label($task->point_of_view) ?>
                    </div>
                </div>
                <div class="btd-row">
                    <div class="btd-label">Language</div>
                    <div class="btd-value <?= empty($task->content_lang) ? 'na' : '' ?>">
                        <?= btd_val($task->content_lang) ?>
                    </div>
                </div>
                <div class="btd-row">
                    <div class="btd-label">Image Option</div>
                    <div class="btd-value <?= empty($task->aiImage) ? 'na' : '' ?>">
                        <?= btd_image_label($task->aiImage) ?>
                    </div>
                </div>
                <div class="btd-row">
                    <div class="btd-label">AI Generated Title</div>
                    <div class="btd-value <?= empty($task->ai_title) ? 'na' : '' ?>">
                        <?= btd_val($task->ai_title) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card: SEO Information -->
        <?php
        $btd_meta_title = '';
        $btd_meta_desc  = '';
        if (!empty($task->post_id)) {
            $btd_meta_title = get_post_meta($task->post_id, '_yoast_wpseo_title', true);
            $btd_meta_desc  = get_post_meta($task->post_id, '_yoast_wpseo_metadesc', true);
        }
        ?>
        <div class="btd-card">
            <div class="btd-card-header">SEO Information</div>
            <div class="btd-card-body">
                <div class="btd-row">
                    <div class="btd-label">Focus Keyword</div>
                    <div class="btd-value <?= empty($task->keyword_name) ? 'na' : '' ?>">
                        <?= btd_val($task->keyword_name) ?>
                    </div>
                </div>
                <div class="btd-row" style="flex-direction: column;">
                    <div class="btd-label" style="margin-bottom: 6px;">Meta Title</div>
                    <div class="btd-value <?= empty($btd_meta_title) ? 'na' : '' ?>">
                        <?= !empty($btd_meta_title) ? esc_html($btd_meta_title) : 'Auto-generated on publish' ?>
                    </div>
                </div>
                <div class="btd-row" style="flex-direction: column;">
                    <div class="btd-label" style="margin-bottom: 6px;">Meta Description</div>
                    <div class="btd-value <?= empty($btd_meta_desc) ? 'na' : '' ?>">
                        <?= !empty($btd_meta_desc) ? esc_html($btd_meta_desc) : 'Auto-generated on publish' ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Details & Call to Action -->
        <div class="btd-card">
            <div class="btd-card-header">Details & Call to Action</div>
            <div class="btd-card-body">
                <div class="btd-row" style="flex-direction: column;">
                    <div class="btd-label" style="margin-bottom: 6px;">Details to Include</div>
                    <div class="btd-value <?= empty($task->details_to_include) ? 'na' : '' ?>" style="white-space: pre-wrap;">
                        <?= btd_val($task->details_to_include) ?>
                    </div>
                </div>
                <div class="btd-row" style="flex-direction: column; margin-top: 8px;">
                    <div class="btd-label" style="margin-bottom: 6px;">Call to Action</div>
                    <div class="btd-value <?= empty($task->call_to_action) ? 'na' : '' ?>" style="white-space: pre-wrap;">
                        <?= btd_val($task->call_to_action) ?>
                    </div>
                </div>
                <div class="btd-row" style="flex-direction: column; margin-top: 8px;">
                    <div class="btd-label" style="margin-bottom: 6px;">Call to Action URL</div>
                    <div class="btd-value <?= empty($task->cta_url) ? 'na' : '' ?>">
                        <?php if (!empty($task->cta_url)): ?>
                            <a href="<?= esc_url($task->cta_url) ?>" target="_blank" rel="noopener"><?= esc_html($task->cta_url) ?></a>
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 4: Scheduling & Publishing -->
        <div class="btd-card">
            <div class="btd-card-header">Scheduling & Publishing</div>
            <div class="btd-card-body">
                <div class="btd-row">
                    <div class="btd-label">Schedule Type</div>
                    <div class="btd-value <?= empty($task->schedule_posts) ? 'na' : '' ?>">
                        <?= btd_schedule_label($task->schedule_posts) ?>
                    </div>
                </div>
                <div class="btd-row">
                    <div class="btd-label">Schedule Frequency</div>
                    <?php $btd_cadence = btd_schedule_cadence($task); ?>
                    <div class="btd-value <?= $btd_cadence === 'N/A' ? 'na' : '' ?>">
                        <?= esc_html($btd_cadence) ?>
                    </div>
                </div>
                <?php if (!empty($task->assigning_authors_value)): ?>
                <div class="btd-row">
                    <div class="btd-label">Assigned Author</div>
                    <div class="btd-value">
                        <?php
                        $author = get_user_by('id', intval($task->assigning_authors_value));
                        if ($author) {
                            echo esc_html($author->display_name) . ' (ID: ' . esc_html($task->assigning_authors_value) . ')';
                        } else {
                            echo btd_val($task->assigning_authors_value);
                        }
                        ?>
                    </div>
                </div>
                <?php endif; ?>
                <div class="btd-row">
                    <div class="btd-label">Categories</div>
                    <div class="btd-value <?= empty($task->cats) ? 'na' : '' ?>">
                        <?php
                        if (!empty($task->cats)) {
                            // Format: ||255 or 255,256
                            $cat_str = trim($task->cats, '|');
                            $cat_ids = array_filter(explode(',', str_replace('||', ',', $cat_str)));
                            $cat_names = array();
                            foreach ($cat_ids as $cid) {
                                $cid = intval(trim($cid));
                                if (!$cid) continue;
                                $cat = get_category($cid);
                                if ($cat && !is_wp_error($cat)) {
                                    $cat_names[] = esc_html($cat->name);
                                }
                            }
                            echo !empty($cat_names) ? implode(', ', $cat_names) : btd_val($task->cats);
                        } else {
                            echo 'N/A';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 5: Shortcodes & Extras -->
        <?php
        $has_extras = !empty($task->testimonial) || !empty($task->Button_SC) || !empty($task->GoogleMap_SC) || !empty($task->Video_SC);
        if ($has_extras):
        ?>
        <div class="btd-card">
            <div class="btd-card-header">Shortcodes & Extras</div>
            <div class="btd-card-body">
                <?php if (!empty($task->testimonial)): ?>
                <div class="btd-row">
                    <div class="btd-label">Testimonial</div>
                    <div class="btd-value"><?= esc_html($task->testimonial) ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($task->Button_SC)): ?>
                <div class="btd-row">
                    <div class="btd-label">Button Shortcode</div>
                    <div class="btd-value"><code><?= esc_html($task->Button_SC) ?></code></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($task->GoogleMap_SC)): ?>
                <div class="btd-row">
                    <div class="btd-label">Google Map SC</div>
                    <div class="btd-value"><code><?= esc_html($task->GoogleMap_SC) ?></code></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($task->Video_SC)): ?>
                <div class="btd-row">
                    <div class="btd-label">Video Shortcode</div>
                    <div class="btd-value"><code><?= esc_html($task->Video_SC) ?></code></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Card 6: AI Image (full width if image exists) -->
        <?php if (!empty($task->ai_image)): ?>
        <div class="btd-card <?= $has_extras ? '' : 'btd-full-width' ?>">
            <div class="btd-card-header">AI Generated Image</div>
            <div class="btd-card-body">
                <?php
                // ai_image is base64 encoded URL
                $image_url = base64_decode($task->ai_image);
                if (filter_var($image_url, FILTER_VALIDATE_URL)):
                ?>
                    <a href="<?= esc_url($image_url) ?>" target="_blank">
                        <img src="<?= esc_url($image_url) ?>" alt="AI Generated Image" class="btd-image-preview" />
                    </a>
                    <p style="margin-top: 8px; font-size: 12px; color: #666;">
                        <a href="<?= esc_url($image_url) ?>" target="_blank"><?= esc_html($image_url) ?></a>
                    </p>
                <?php else: ?>
                    <p class="na" style="margin: 0; font-style: italic; color: #a7aaad;">Invalid image URL</p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Card 7: Content Preview (full width) -->
        <div class="btd-card btd-full-width">
            <div class="btd-card-header">Content Preview</div>
            <div class="btd-card-body">
                <?php if (!empty($task->ai_content)):
                    $decoded_content = base64_decode($task->ai_content);
                    if ($decoded_content):
                ?>
                    <div class="btd-content-preview">
                        <?= wp_kses_post($decoded_content) ?>
                    </div>
                <?php else: ?>
                    <p class="na" style="margin: 0; font-style: italic; color: #a7aaad;">Could not decode content.</p>
                <?php endif; ?>
                <?php else: ?>
                    <p class="na" style="margin: 0; font-style: italic; color: #a7aaad;">Content not generated yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    </div>
    </div>
</div>

<?php View::endSection('content') ?>
<?php View::make('layouts.main') ?>
