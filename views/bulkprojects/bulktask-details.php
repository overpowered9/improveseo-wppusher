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

<span>Task Details</span>

<?php View::endSection('breadcrumbs') ?>

<?php View::startSection('content') ?>

<?php
// Helper to display a value or fallback
function btd_val($val, $default = 'N/A') {
    if ($val === null || $val === '') return $default;
    return esc_html($val);
}

function btd_tone_label($val) {
    return ($val && $val !== '') ? ucfirst(esc_html($val)) : 'N/A';
}

function btd_seed_option_label($val) {
    $map = array(
        'seed_option1' => 'Use Keyword As-Is in Title',
        'seed_option2' => 'Create Best Title from Keyword',
        'seed_option3' => 'Create Best Question from Keyword',
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
        'schedule_all_posts' => 'Schedule All Posts',
        'publish_immediately' => 'Publish Immediately',
    );
    return isset($map[$val]) ? $map[$val] : btd_val($val);
}

function btd_author_label($val) {
    $map = array(
        'assigning_authors' => 'Assigned Author',
        'random_authors' => 'Random Authors',
    );
    return isset($map[$val]) ? $map[$val] : btd_val($val);
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
        <h1>ImproveSEO | Task Details</h1>
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
                    <button>← Back to Tasks</button>
                </a>
            <?php else: ?>
                <a href="<?= admin_url('admin.php?page=improveseo_bulkprojects') ?>" style="text-decoration:none;">
                    <button>← Back to Bulk Projects</button>
                </a>
            <?php endif; ?>
            <?php if (!empty($task->ai_content)): ?>
                <a href="<?= admin_url('admin.php?page=improveseo_bulkprojects&action=viewAiContent&id=' . $task->id) ?>" target="_blank" style="text-decoration:none;">
                    <button>View AI Content</button>
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
                        <span class="btd-badge <?= $badge ?>"><?= $status === 'Stoped' ? 'Canceled' : esc_html($status) ?></span>
                    </div>
                </div>
                <div class="btd-row">
                    <div class="btd-label">Post Status</div>
                    <div class="btd-value">
                        <?php
                        $state = $task->state;
                        $state_badge = 'btd-badge-pending';
                        if ($state === 'Published') $state_badge = 'btd-badge-published';
                        elseif ($state === 'Draft') $state_badge = 'btd-badge-draft';
                        elseif ($state === 'Scheduled') $state_badge = 'btd-badge-scheduled';
                        ?>
                        <span class="btd-badge <?= $state_badge ?>"><?= esc_html($state ?: 'Pending') ?></span>
                    </div>
                </div>
                <div class="btd-row">
                    <div class="btd-label">Published On</div>
                    <div class="btd-value <?= (empty($task->published_on) || $task->published_on === '0000-00-00 00:00:00') ? 'na' : '' ?>">
                        <?php
                        if (empty($task->published_on) || $task->published_on === '0000-00-00 00:00:00' || $task->status === 'Stoped') {
                            echo 'N/A';
                        } else {
                            echo esc_html(date('m/d/Y H:i:s', strtotime($task->published_on)));
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
                        <?= btd_val($task->point_of_view) ?>
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
                    <div class="btd-value <?= empty($task->schedule_frequency) ? 'na' : '' ?>">
                        <?= btd_val($task->schedule_frequency) ?>
                    </div>
                </div>
                <div class="btd-row">
                    <div class="btd-label"># Posts per Schedule</div>
                    <div class="btd-value <?= empty($task->number_of_post_schedule) ? 'na' : '' ?>">
                        <?= btd_val($task->number_of_post_schedule) ?>
                    </div>
                </div>
                <div class="btd-row">
                    <div class="btd-label">Author Assignment</div>
                    <div class="btd-value <?= empty($task->assigning_authors) ? 'na' : '' ?>">
                        <?= btd_author_label($task->assigning_authors) ?>
                    </div>
                </div>
                <?php if (!empty($task->assigning_authors_value)): ?>
                <div class="btd-row">
                    <div class="btd-label">Assigned Author ID</div>
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
                <div class="btd-row">
                    <div class="btd-label">Published by Plugin</div>
                    <div class="btd-value"><?= $task->is_published_by_plugin ? 'Yes' : 'No' ?></div>
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
