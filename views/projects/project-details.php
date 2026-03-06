<?php

use ImproveSEO\View;

?>

<?php View::startSection('breadcrumbs') ?>

<a href="<?= admin_url('admin.php?page=improveseo_dashboard') ?>">Improve SEO</a>

&raquo;

<a href="<?= admin_url('admin.php?page=improveseo_projects') ?>">Projects List</a>

&raquo;

<span>Project Details</span>

<?php View::endSection('breadcrumbs') ?>

<?php View::startSection('content') ?>

<?php
// Helper to display a value or N/A
function pd_val($arr, $key, $default = 'N/A') {
    if (!is_array($arr)) return $default;
    return isset($arr[$key]) && $arr[$key] !== '' ? esc_html($arr[$key]) : $default;
}

// Friendly labels for stored values
function pd_tone_label($val) {
    return $val && $val !== 'N/A' ? ucfirst($val) : 'N/A';
}

function pd_seed_option_label($val) {
    $map = array(
        'seed_option1' => 'Use Keyword As-Is in Title',
        'seed_option2' => 'Create Best Title from Keyword',
        'seed_option3' => 'Create Best Question from Keyword',
    );
    return isset($map[$val]) ? $map[$val] : ($val ?: 'N/A');
}

function pd_pov_label($val) {
    if (!$val) return 'N/A';
    return esc_html($val);
}

function pd_image_label($val) {
    $map = array(
        'AI_image' => 'AI Generated Image',
        'manually_promt_image' => 'AI Image – Edit Prompt',
        'Manually_image' => 'Manual Image Upload',
        'google_image' => 'Google Image',
        'pexels_image' => 'Pexels Image',
        'pixabay_image' => 'Pixabay Image',
    );
    return isset($map[$val]) ? $map[$val] : ($val ? esc_html($val) : 'N/A');
}
?>

<style>
    .project-details-wrap {
        max-width: 1100px;
        margin: 20px auto;
    }
    .project-details-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 12px;
    }
    .project-details-header h1 {
        margin: 0;
        font-size: 22px;
        font-weight: 600;
        color: #1d2327;
    }
    .project-details-header .pd-actions a {
        text-decoration: none;
        margin-left: 8px;
    }
    .pd-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    @media (max-width: 782px) {
        .pd-grid {
            grid-template-columns: 1fr;
        }
    }
    .pd-card {
        background: #fff;
        border: 1px solid #c3c4c7;
        border-radius: 4px;
        box-shadow: 0 1px 1px rgba(0,0,0,.04);
    }
    .pd-card-header {
        padding: 12px 16px;
        border-bottom: 1px solid #c3c4c7;
        background: #f6f7f7;
        font-weight: 600;
        font-size: 14px;
        color: #1d2327;
    }
    .pd-card-body {
        padding: 16px;
    }
    .pd-row {
        display: flex;
        padding: 8px 0;
        border-bottom: 1px solid #f0f0f1;
    }
    .pd-row:last-child {
        border-bottom: none;
    }
    .pd-label {
        flex: 0 0 160px;
        font-weight: 500;
        color: #50575e;
        font-size: 13px;
    }
    .pd-value {
        flex: 1;
        color: #1d2327;
        font-size: 13px;
        word-break: break-word;
    }
    .pd-value.na {
        color: #a7aaad;
        font-style: italic;
    }
    .pd-badge {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
    }
    .pd-badge-published { background: #d4edda; color: #155724; }
    .pd-badge-draft { background: #fff3cd; color: #856404; }
    .pd-badge-stopped { background: #f8d7da; color: #721c24; }
    .pd-full-width {
        grid-column: 1 / -1;
    }
    .pd-content-preview {
        max-height: 300px;
        overflow-y: auto;
        padding: 12px;
        background: #f9f9f9;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        font-size: 13px;
        line-height: 1.6;
    }
</style>

<div class="project-details-wrap">
    <div class="project-details-header">
        <h1><?= esc_html($project->name) ?> – Project Details</h1>
        <div class="pd-actions">
            <a href="<?= admin_url('admin.php?page=improveseo_projects') ?>" class="button">
                ← Back to Projects
            </a>
            <a href="<?= admin_url("admin.php?page=improveseo_dashboard&action=edit_post&id={$project->id}&update=true") ?>" class="button button-primary" target="_blank">
                Edit Project
            </a>
            <?php if ($associated_post && $post_url): ?>
                <a href="<?= esc_url($post_url) ?>" class="button" target="_blank">View Post</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="pd-grid">
        <!-- Card 1: Basic Info -->
        <div class="pd-card">
            <div class="pd-card-header">Basic Information</div>
            <div class="pd-card-body">
                <div class="pd-row">
                    <div class="pd-label">Project Name</div>
                    <div class="pd-value"><?= esc_html($project->name) ?></div>
                </div>
                <div class="pd-row">
                    <div class="pd-label">Post Title</div>
                    <div class="pd-value"><?= isset($content['title']) && $content['title'] ? esc_html($content['title']) : '<span class="na">N/A</span>' ?></div>
                </div>
                <div class="pd-row">
                    <div class="pd-label">Status</div>
                    <div class="pd-value">
                        <?php
                        $state = $project->state;
                        $badge_class = 'pd-badge-draft';
                        if ($state === 'Published') $badge_class = 'pd-badge-published';
                        if ($project->deleted_at && $project->deleted_at !== '0000-00-00 00:00:00') $badge_class = 'pd-badge-stopped';
                        ?>
                        <span class="pd-badge <?= $badge_class ?>"><?= esc_html($state) ?></span>
                    </div>
                </div>
                <div class="pd-row">
                    <div class="pd-label">Post Type</div>
                    <div class="pd-value"><?= isset($content['post_type']) ? ucfirst(esc_html($content['post_type'])) : 'N/A' ?></div>
                </div>
                <div class="pd-row">
                    <div class="pd-label">Progress</div>
                    <div class="pd-value"><?= intval($project->iteration) ?> / <?= intval($project->max_iterations) ?> posts</div>
                </div>
                <div class="pd-row">
                    <div class="pd-label">Categories</div>
                    <div class="pd-value">
                        <?php
                        $cat_ids = json_decode($project->cats, true);
                        if (!empty($cat_ids) && is_array($cat_ids)) {
                            $cat_names = array();
                            foreach ($cat_ids as $cat_id) {
                                $cat = get_category($cat_id);
                                if ($cat && !is_wp_error($cat)) {
                                    $cat_names[] = esc_html($cat->name);
                                }
                            }
                            echo implode(', ', $cat_names) ?: '<span class="na">None</span>';
                        } else {
                            echo '<span class="na">None</span>';
                        }
                        ?>
                    </div>
                </div>
                <div class="pd-row">
                    <div class="pd-label">Created</div>
                    <div class="pd-value"><?= $project->created_at ? esc_html(date('M j, Y g:i A', strtotime($project->created_at))) : 'N/A' ?></div>
                </div>
                <div class="pd-row">
                    <div class="pd-label">Updated</div>
                    <div class="pd-value"><?= $project->updated_at ? esc_html(date('M j, Y g:i A', strtotime($project->updated_at))) : 'N/A' ?></div>
                </div>
                <?php if ($associated_post): ?>
                <div class="pd-row">
                    <div class="pd-label">WordPress Post</div>
                    <div class="pd-value">
                        <a href="<?= esc_url($post_url) ?>" target="_blank"><?= esc_html($associated_post->post_title) ?></a>
                        (ID: <?= $associated_post->ID ?>)
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Card 2: AI Content Settings -->
        <div class="pd-card">
            <div class="pd-card-header">AI Content Settings</div>
            <div class="pd-card-body">
                <div class="pd-row">
                    <div class="pd-label">Seed Keyword</div>
                    <div class="pd-value <?= pd_val($options, 'ai_seed_keyword') === 'N/A' ? 'na' : '' ?>">
                        <?= pd_val($options, 'ai_seed_keyword') ?>
                    </div>
                </div>
                <div class="pd-row">
                    <div class="pd-label">Title Type</div>
                    <div class="pd-value <?= pd_val($options, 'ai_seed_options') === 'N/A' ? 'na' : '' ?>">
                        <?= pd_seed_option_label(isset($options['ai_seed_options']) ? $options['ai_seed_options'] : '') ?>
                    </div>
                </div>
                <div class="pd-row">
                    <div class="pd-label">Tone of Voice</div>
                    <div class="pd-value <?= pd_val($options, 'ai_content_type') === 'N/A' ? 'na' : '' ?>">
                        <?= pd_tone_label(pd_val($options, 'ai_content_type')) ?>
                    </div>
                </div>
                <div class="pd-row">
                    <div class="pd-label">Article Size</div>
                    <div class="pd-value <?= pd_val($options, 'ai_nos_of_words') === 'N/A' ? 'na' : '' ?>">
                        <?= pd_val($options, 'ai_nos_of_words') ?>
                    </div>
                </div>
                <div class="pd-row">
                    <div class="pd-label">Point of View</div>
                    <div class="pd-value <?= pd_val($options, 'ai_point_of_view') === 'N/A' ? 'na' : '' ?>">
                        <?= pd_pov_label(pd_val($options, 'ai_point_of_view')) ?>
                    </div>
                </div>
                <div class="pd-row">
                    <div class="pd-label">Language</div>
                    <div class="pd-value <?= pd_val($options, 'ai_content_lang') === 'N/A' ? 'na' : '' ?>">
                        <?= pd_val($options, 'ai_content_lang') ?>
                    </div>
                </div>
                <div class="pd-row">
                    <div class="pd-label">Image Option</div>
                    <div class="pd-value <?= pd_val($options, 'ai_image_option') === 'N/A' ? 'na' : '' ?>">
                        <?= pd_image_label(isset($options['ai_image_option']) ? $options['ai_image_option'] : '') ?>
                    </div>
                </div>
                <div class="pd-row">
                    <div class="pd-label">AI Generated Title</div>
                    <div class="pd-value <?= pd_val($options, 'ai_generated_title') === 'N/A' ? 'na' : '' ?>">
                        <?= pd_val($options, 'ai_generated_title') ?>
                    </div>
                </div>
                <div class="pd-row">
                    <div class="pd-label">Testing Only</div>
                    <div class="pd-value">
                        <?= (isset($options['ai_for_testing_only']) && $options['ai_for_testing_only'] === '1') ? 'Yes' : 'No' ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Details & Call to Action -->
        <div class="pd-card">
            <div class="pd-card-header">Details & Call to Action</div>
            <div class="pd-card-body">
                <div class="pd-row" style="flex-direction: column;">
                    <div class="pd-label" style="margin-bottom: 6px;">Details to Include</div>
                    <div class="pd-value <?= pd_val($options, 'ai_details_to_include') === 'N/A' ? 'na' : '' ?>" style="white-space: pre-wrap;">
                        <?= pd_val($options, 'ai_details_to_include') ?>
                    </div>
                </div>
                <div class="pd-row" style="flex-direction: column; margin-top: 8px;">
                    <div class="pd-label" style="margin-bottom: 6px;">Call to Action</div>
                    <div class="pd-value <?= pd_val($options, 'ai_call_to_action') === 'N/A' ? 'na' : '' ?>" style="white-space: pre-wrap;">
                        <?= pd_val($options, 'ai_call_to_action') ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 4: SEO & Additional Settings -->
        <div class="pd-card">
            <div class="pd-card-header">SEO & Additional Settings</div>
            <div class="pd-card-body">
                <div class="pd-row">
                    <div class="pd-label">Meta Title</div>
                    <div class="pd-value <?= pd_val($options, 'custom_title') === 'N/A' ? 'na' : '' ?>">
                        <?= pd_val($options, 'custom_title') ?>
                    </div>
                </div>
                <div class="pd-row">
                    <div class="pd-label">Meta Description</div>
                    <div class="pd-value <?= pd_val($options, 'custom_description') === 'N/A' ? 'na' : '' ?>">
                        <?= pd_val($options, 'custom_description') ?>
                    </div>
                </div>
                <div class="pd-row">
                    <div class="pd-label">Meta Keywords</div>
                    <div class="pd-value <?= pd_val($options, 'custom_keywords') === 'N/A' ? 'na' : '' ?>">
                        <?= pd_val($options, 'custom_keywords') ?>
                    </div>
                </div>
                <div class="pd-row">
                    <div class="pd-label">Permalink</div>
                    <div class="pd-value <?= pd_val($options, 'permalink') === 'N/A' ? 'na' : '' ?>">
                        <?= pd_val($options, 'permalink') ?>
                    </div>
                </div>
                <div class="pd-row">
                    <div class="pd-label">Tags</div>
                    <div class="pd-value <?= pd_val($options, 'tags') === 'N/A' ? 'na' : '' ?>">
                        <?= pd_val($options, 'tags') ?>
                    </div>
                </div>
                <div class="pd-row">
                    <div class="pd-label">Max Posts</div>
                    <div class="pd-value"><?= pd_val($options, 'max_posts', '1') ?></div>
                </div>
                <?php if (isset($options['dripfeed_type'])): ?>
                <div class="pd-row">
                    <div class="pd-label">Dripfeed</div>
                    <div class="pd-value"><?= esc_html($options['dripfeed_type']) ?> – every <?= esc_html($options['dripfeed_x']) ?> hours</div>
                </div>
                <?php endif; ?>
                <?php if (isset($options['schema']) && $options['schema']): ?>
                <div class="pd-row">
                    <div class="pd-label">Schema</div>
                    <div class="pd-value">Enabled – <?= esc_html($options['schema_business'] ?? '') ?></div>
                </div>
                <?php endif; ?>
                <?php if (isset($options['local_geo_country'])): ?>
                <div class="pd-row">
                    <div class="pd-label">Local SEO</div>
                    <div class="pd-value">
                        Country: <?= esc_html($options['local_geo_country']) ?>
                        (<?= is_array($options['local_geo_locations'] ?? null) ? count($options['local_geo_locations']) : 0 ?> locations)
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Card 5: Content Preview (full width) -->
        <div class="pd-card pd-full-width">
            <div class="pd-card-header">Content Preview</div>
            <div class="pd-card-body">
                <?php if (isset($content['content']) && $content['content']): ?>
                    <div class="pd-content-preview">
                        <?= wp_kses_post($content['content']) ?>
                    </div>
                <?php else: ?>
                    <p class="na" style="margin: 0; font-style: italic; color: #a7aaad;">No content stored in project.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php View::endSection('content') ?>
<?php View::make('layouts.main') ?>
