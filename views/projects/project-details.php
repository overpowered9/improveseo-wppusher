<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


use ImproveSEO\View;

?>

<?php View::startSection('breadcrumbs') ?>

<a href="<?php echo esc_url( admin_url('admin.php?page=improveseo_dashboard') ); ?>">Improve SEO</a>

&raquo;

<a href="<?php echo esc_url( admin_url('admin.php?page=improveseo_projects') ); ?>">Projects List</a>

&raquo;

<span>Project Details</span>

<?php View::endSection('breadcrumbs') ?>

<?php View::startSection('content') ?>

<?php
// Helper to display a value or N/A
function pd_val($arr, $key, $default = 'N/A') {
    if (!is_array($arr) || !isset($arr[$key])) return $default;
    // Trim leading/trailing whitespace — stored details/CTA text often carries
    // blank lines that, under white-space: pre-wrap, push the text to the
    // bottom of the field. Internal line breaks are preserved.
    $val = trim((string) $arr[$key]);
    return $val === '' ? $default : esc_html($val);
}

// Friendly labels for stored values
function pd_tone_label($val) {
    // esc_html() here, not at the call site: every other pd_* / btd_* helper
    // escapes before returning, and btd_tone_label - this function's twin on the
    // bulk-task screen - already did. This one did not, so a stored tone value was
    // echoed raw. Escaping at the call site instead would double-escape the others.
    return $val && $val !== 'N/A' ? ucfirst(esc_html($val)) : 'N/A';
}

function pd_seed_option_label($val) {
    $map = array(
        'seed_option1' => 'Exact Keyword as Title',
        'seed_option2' => 'Smart Title (AI-Generated)',
        'seed_option3' => 'Question-Style Title (AI-Generated)',
    );
    return isset($map[$val]) ? $map[$val] : pd_humanize($val);
}

function pd_pov_label($val) {
    if (!$val) return 'N/A';
    $map = array(
        'none'                                 => 'Auto (AI Decides)',
        'Second Person (you,your,yours)'       => 'Speaking to the Reader ("you", "your")',
        'First person plural (we,us,our,ours)' => 'Business Voice ("we", "our")',
        'First person singular (I,me,my,mine)' => 'Personal Voice ("I", "my")',
    );
    return isset($map[$val]) ? $map[$val] : pd_humanize($val);
}

// Same fallback rule as the bulk task-details screen: an unmapped enum is a
// snake_case machine value, so de-underscore + title-case it rather than
// showing "Multiple_images" to the user.
function pd_humanize($val, $default = 'N/A') {
    $val = trim((string) $val);
    if ($val === '') return $default;
    if (strpos($val, '_') === false) return esc_html($val);
    return esc_html(ucwords(str_replace('_', ' ', $val)));
}

function pd_image_label($val) {
    $map = array(
        'AI_image' => 'AI Generated Image',
        'AI_image_one' => 'AI Generated Image (One)',
        'manually_promt_image' => 'AI Image – Edit Prompt',
        'Manually_image' => 'Manual Image Upload',
        'Multiple_images' => 'Multiple Images',
        'google_image' => 'Google Image',
        'pexels_image' => 'Pexels Image',
        'pixabay_image' => 'Pixabay Image',
    );
    return isset($map[$val]) ? $map[$val] : pd_humanize($val);
}

// Resolve SEO title/description/focus keyword from the live post. Checks the
// active SEO plugin's meta first (Yoast, RankMath, SEOPress), then the plugin's
// own keys — the builder stores spintax-resolved values as improveseo_custom_*
// on every generated post (see modules/builder.php). Returns '' when nothing set.
function pd_seo_meta($post_id, $what) {
    if (!$post_id) return '';
    $keys = array(
        'title'   => array('_yoast_wpseo_title', 'rank_math_title', '_seopress_titles_title', 'improveseo_custom_title'),
        'desc'    => array('_yoast_wpseo_metadesc', 'rank_math_description', '_seopress_titles_desc', 'improveseo_custom_description'),
        'focuskw' => array('_yoast_wpseo_focuskw', 'rank_math_focus_keyword', '_seopress_analysis_target_kw', 'improveseo_custom_keywords'),
    );
    if (!isset($keys[$what])) return '';
    foreach ($keys[$what] as $k) {
        $v = trim((string) get_post_meta($post_id, $k, true));
        if ($v !== '') {
            // Focus keyword: some plugins store a comma/pipe list — show the first.
            if ($what === 'focuskw') {
                $parts = preg_split('/[,|]/', $v);
                return trim($parts[0]);
            }
            return $v;
        }
    }
    return '';
}
?>

<style>
    .pd-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        padding: 0 20px 20px;
    }
    @media (max-width: 782px) {
        .pd-grid {
            grid-template-columns: 1fr;
        }
    }
    .pd-card {
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 10px;
        box-shadow: 0 1px 4px rgba(0,0,0,.06);
    }
    .pd-card-header {
        padding: 12px 16px;
        border-bottom: 1px solid #dee2e6;
        background: #f8f9fa;
        font-weight: 600;
        font-size: 14px;
        color: #1d2327;
        border-radius: 10px 10px 0 0;
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
        text-align: left;
    }
    /* Stacked label/value rows (Details & CTA). In a flex COLUMN the label's
       `flex: 0 0 160px` basis applies to HEIGHT (a 160px-tall label shoves the value
       down); reset both to natural height, and keep the value full-width, top and
       left-aligned so N/A / text never sits centred or pushed down. */
    .pd-row-stacked {
        display: block !important;          /* plain block stacking, never flex */
    }
    .pd-row-stacked .pd-label,
    .pd-row-stacked .pd-value {
        display: block !important;          /* value sits directly under the label */
        width: 100% !important;
        max-width: 100% !important;
        flex: none !important;
        text-align: left !important;        /* stuck to the left */
        margin-left: 0 !important;
    }
    .pd-value {
        flex: 1;
        color: #1d2327;
        font-size: 13px;
        word-break: break-word;
        text-align: left;
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
    .pd-badge-scheduled { background: #cce5ff; color: #004085; }
    .pd-full-width {
        grid-column: 1 / -1;
    }

</style>

<?php
// `improveseo-project-details` scopes this page's header rules in
// assets/css/made_by_me.css. The action buttons deliberately do NOT use the
// plugin-wide `.import-export-btn` class: that rule gives every button a 50px
// pill radius with no flex-shrink guard, so once flex compressed them below
// their intrinsic width the radius started swallowing the label. This page owns
// `.improveseo-header-actions` / `.improveseo-header-btn` instead, which keeps
// the shared class (and the 6 other screens using it) untouched and means the
// new rules never have to fight it with !important.
?>
<div class="global-wrap improveseo-project-details">
    <div class="head-bar">
        <img src="<?php echo esc_url( improveseo_logo_url() ); ?>" alt="ImproveSEO logo">
        <h1>ImproveSEO | Project Details
            <span class="iseo-info-tip" tabindex="0" role="button" aria-label="What is on this page?">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                <span class="iseo-info-tip-bubble iseo-info-tip-bubble--field" role="tooltip">
                    On this page you will find basic information about this post, and content details that were used to create your post. The information shown on this page is for information purposes only.
                </span>
            </span>
        </h1>
    </div>
    <div class="box-top">
        <div class="improveseo-header-title">
            <ul class="breadcrumb-seo">
                <li><a href="<?php echo esc_url( admin_url('admin.php?page=improveseo_dashboard') ); ?>">Improve SEO</a></li>
                <li><a href="<?php echo esc_url( admin_url('admin.php?page=improveseo_projects') ); ?>">Projects List</a></li>
                <?php // Ellipsised when long (see CSS); title="" keeps the full name readable on hover. ?>
                <li class="improveseo-header-project-name" title="<?php echo  esc_attr($project->name) ?>"><?php echo  esc_html($project->name) ?></li>
            </ul>
        </div>
        <div class="improveseo-header-actions">
            <a href="<?php echo esc_url( admin_url('admin.php?page=improveseo_projects') ); ?>" style="text-decoration:none;">
                <button class="improveseo-header-btn">← Back to Projects</button>
            </a>
            <?php $edit_link = $associated_post ? get_edit_post_link($associated_post->ID, 'raw') : ''; ?>
            <?php if ($edit_link): ?>
                <a href="<?php echo  esc_url($edit_link) ?>" target="_blank" style="text-decoration:none;">
                    <button class="improveseo-header-btn active">Edit Post</button>
                </a>
            <?php elseif ($project->state === 'Draft'): ?>
                <a href="<?php echo esc_url( admin_url("admin.php?page=improveseo_dashboard&action=edit_post&id={$project->id}") ); ?>" style="text-decoration:none;">
                    <button class="improveseo-header-btn active">Edit Draft</button>
                </a>
            <?php endif; ?>
            <?php
            // A draft (no post yet, or a real WP post still in draft/pending) has no public
            // permalink to open — get_permalink() on a draft returns a link that just 404s
            // for visitors. Preview it in the same in-modal instant preview the edit form's
            // "Preview Post" button uses instead (improveseo_instant_preview in
            // modules/ajax.php), fed with this project's current stored title/content via
            // the hidden fields below. Published/private/scheduled posts keep the existing
            // live-link behavior unchanged.
            $pd_view_post_is_draft = $associated_post
                ? in_array($associated_post->post_status, array('draft', 'pending', 'auto-draft'), true)
                : true;
            $pd_preview_title   = (isset($content['title']) && $content['title'] !== '') ? $content['title'] : ($associated_post ? $associated_post->post_title : '');
            $pd_preview_content = (isset($content['content']) && $content['content'] !== '') ? $content['content'] : ($associated_post ? $associated_post->post_content : '');
            ?>
            <?php if (!$pd_view_post_is_draft && $associated_post && $post_url): ?>
                <a href="<?php echo  esc_url($post_url) ?>" target="_blank" style="text-decoration:none;">
                    <button class="improveseo-header-btn">View Post</button>
                </a>
            <?php elseif (trim((string) $pd_preview_title) !== '' || trim((string) $pd_preview_content) !== ''): ?>
                <button type="button" class="improveseo-header-btn" onclick="iseoPreviewProjectDraft()">Preview Post</button>
            <?php endif; ?>
        </div>
    </div>
    <div class="improve-seo-container">
    <div class="project-lists" style="padding: 20px 0 0;">

    <div class="pd-grid">
        <!-- Card 1: Basic Info -->
        <div class="pd-card">
            <div class="pd-card-header">Basic Information</div>
            <div class="pd-card-body">
                <div class="pd-row">
                    <div class="pd-label">Project Name</div>
                    <div class="pd-value"><?php echo  esc_html($project->name) ?></div>
                </div>
                <?php $pd_title = (isset($content['title']) && $content['title'] !== '') ? $content['title'] : ($associated_post ? $associated_post->post_title : ''); ?>
                <div class="pd-row">
                    <div class="pd-label">Post Title</div>
                    <div class="pd-value <?php echo  $pd_title === '' ? 'na' : '' ?>"><?php echo  $pd_title !== '' ? esc_html($pd_title) : 'N/A' ?></div>
                </div>
                <div class="pd-row">
                    <div class="pd-label">Status</div>
                    <div class="pd-value">
                        <?php
                        // Prefer the real WordPress post status (Published / Draft / Scheduled)
                        // when a post exists. The builder writes the project state 'Updated' on
                        // completion — that is not a user edit, so a live published post would
                        // otherwise read "Updated". Fall back to the project state when there is
                        // no associated post yet.
                        $state = $project->state;
                        $badge_class = 'pd-badge-draft';
                        if ($project->deleted_at && $project->deleted_at !== '0000-00-00 00:00:00') {
                            $status_label = 'Stopped';
                            $badge_class  = 'pd-badge-stopped';
                        } elseif ($associated_post) {
                            switch ($associated_post->post_status) {
                                case 'publish': $status_label = 'Published'; $badge_class = 'pd-badge-published'; break;
                                case 'future':  $status_label = 'Scheduled'; $badge_class = 'pd-badge-scheduled'; break;
                                case 'private': $status_label = 'Private';   $badge_class = 'pd-badge-published'; break;
                                case 'pending': $status_label = 'Pending';   $badge_class = 'pd-badge-draft';     break;
                                case 'draft':   $status_label = 'Draft';     $badge_class = 'pd-badge-draft';     break;
                                default:        $status_label = ucfirst($associated_post->post_status);          break;
                            }
                        } else {
                            // No post yet — normalise the completion state to Published.
                            $status_label = ($state === 'Published' || $state === 'Updated') ? 'Published' : ($state ?: 'Draft');
                            if ($status_label === 'Published') $badge_class = 'pd-badge-published';
                        }
                        ?>
                        <span class="pd-badge <?php echo esc_attr( $badge_class ); ?>"><?php echo  esc_html($status_label) ?></span>
                    </div>
                </div>
                <?php $pd_post_type = (isset($content['post_type']) && $content['post_type'] !== '') ? $content['post_type'] : ($associated_post ? $associated_post->post_type : ''); ?>
                <div class="pd-row">
                    <div class="pd-label">Post Type</div>
                    <div class="pd-value <?php echo  $pd_post_type === '' ? 'na' : '' ?>"><?php echo  $pd_post_type !== '' ? esc_html( ucfirst( $pd_post_type ) ) : 'N/A' ?></div>
                </div>
                <?php if (intval($project->max_iterations) > 1): // single-post projects are always 1/1 — only meaningful for multi-post ?>
                <div class="pd-row">
                    <div class="pd-label">Progress</div>
                    <div class="pd-value"><?php echo  intval($project->iteration) ?> / <?php echo  intval($project->max_iterations) ?> posts</div>
                </div>
                <?php endif; ?>
                <div class="pd-row">
                    <div class="pd-label">Categories</div>
                    <div class="pd-value">
                        <?php
                        $cat_names = array();
                        // Prefer the live post's real categories; the project's stored
                        // IDs are only the wizard's original picks and can be stale.
                        if ($associated_post) {
                            $pd_post_cats = wp_get_post_terms($associated_post->ID, 'category', array('fields' => 'names'));
                            if (is_array($pd_post_cats)) $cat_names = $pd_post_cats;
                        }
                        if (empty($cat_names)) {
                            $cat_ids = json_decode($project->cats, true);
                            if (!empty($cat_ids) && is_array($cat_ids)) {
                                foreach ($cat_ids as $cat_id) {
                                    $cat = get_category($cat_id);
                                    if ($cat && !is_wp_error($cat)) {
                                        $cat_names[] = $cat->name;
                                    }
                                }
                            }
                        }
                        echo !empty($cat_names) ? esc_html(implode(', ', $cat_names)) : '<span class="na">None</span>';
                        ?>
                    </div>
                </div>
                <div class="pd-row">
                    <div class="pd-label">Created</div>
                    <div class="pd-value"><?php echo  $project->created_at ? esc_html(gmdate('M j, Y g:i A', strtotime($project->created_at))) : 'N/A' ?></div>
                </div>
                <div class="pd-row">
                    <div class="pd-label">Updated</div>
                    <div class="pd-value"><?php echo  $project->updated_at ? esc_html(gmdate('M j, Y g:i A', strtotime($project->updated_at))) : 'N/A' ?></div>
                </div>
                <?php if ($associated_post): ?>
                <div class="pd-row">
                    <div class="pd-label">WordPress Post</div>
                    <div class="pd-value">
                        <a href="<?php echo  esc_url($post_url) ?>" target="_blank"><?php echo  esc_html($associated_post->post_title) ?></a>
                        (ID: <?php echo esc_html( $associated_post->ID ); ?>)
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Card 2: AI Content Settings -->
        <?php
        // These are generation-time settings — the WordPress post stores none of
        // them, so they are only ever available from the project's saved options.
        // When none were recorded (older posts / other create flows), show one
        // honest line instead of a column of N/A that reads like a loading bug.
        $pd_ai_keys = array('ai_seed_keyword', 'ai_seed_options', 'ai_content_type', 'ai_nos_of_words', 'ai_point_of_view', 'ai_content_lang', 'ai_image_option');
        $pd_has_ai = false;
        foreach ($pd_ai_keys as $pd_k) {
            if (isset($options[$pd_k]) && trim((string) $options[$pd_k]) !== '') { $pd_has_ai = true; break; }
        }
        ?>
        <div class="pd-card">
            <div class="pd-card-header">AI Content Settings</div>
            <div class="pd-card-body">
                <?php if (!$pd_has_ai): ?>
                <p class="na" style="margin: 0; font-style: italic; color: #a7aaad;">These generation settings weren't recorded for this post.</p>
                <?php else: ?>
                <div class="pd-row">
                    <div class="pd-label">Seed Keyword</div>
                    <div class="pd-value <?php echo  pd_val($options, 'ai_seed_keyword') === 'N/A' ? 'na' : '' ?>">
                        <?php echo pd_val($options, 'ai_seed_keyword'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- helper escapes internally; wrapping again would double-encode ?>
                    </div>
                </div>
                <div class="pd-row">
                    <div class="pd-label">Title Type</div>
                    <div class="pd-value <?php echo  pd_val($options, 'ai_seed_options') === 'N/A' ? 'na' : '' ?>">
                        <?php echo pd_seed_option_label(isset($options['ai_seed_options']) ? $options['ai_seed_options'] : ''); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- helper escapes internally; wrapping again would double-encode ?>
                    </div>
                </div>
                <div class="pd-row">
                    <div class="pd-label">Tone of Voice</div>
                    <div class="pd-value <?php echo  pd_val($options, 'ai_content_type') === 'N/A' ? 'na' : '' ?>">
                        <?php echo pd_tone_label(pd_val($options, 'ai_content_type')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- helper escapes internally; wrapping again would double-encode ?>
                    </div>
                </div>
                <div class="pd-row">
                    <div class="pd-label">Article Size</div>
                    <div class="pd-value <?php echo  pd_val($options, 'ai_nos_of_words') === 'N/A' ? 'na' : '' ?>">
                        <?php echo pd_val($options, 'ai_nos_of_words'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- helper escapes internally; wrapping again would double-encode ?>
                    </div>
                </div>
                <div class="pd-row">
                    <div class="pd-label">Point of View</div>
                    <div class="pd-value <?php echo  pd_val($options, 'ai_point_of_view') === 'N/A' ? 'na' : '' ?>">
                        <?php echo pd_pov_label(pd_val($options, 'ai_point_of_view')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- helper escapes internally; wrapping again would double-encode ?>
                    </div>
                </div>
                <div class="pd-row">
                    <div class="pd-label">Language</div>
                    <div class="pd-value <?php echo  pd_val($options, 'ai_content_lang') === 'N/A' ? 'na' : '' ?>">
                        <?php echo pd_val($options, 'ai_content_lang'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- helper escapes internally; wrapping again would double-encode ?>
                    </div>
                </div>
                <div class="pd-row">
                    <div class="pd-label">Image Option</div>
                    <div class="pd-value <?php echo  pd_val($options, 'ai_image_option') === 'N/A' ? 'na' : '' ?>">
                        <?php echo pd_image_label(isset($options['ai_image_option']) ? $options['ai_image_option'] : ''); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- helper escapes internally; wrapping again would double-encode ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Card 3: Details & Call to Action -->
        <div class="pd-card">
            <div class="pd-card-header">Details & Call to Action</div>
            <div class="pd-card-body">
                <?php
                // Stacking/alignment is handled by .pd-row-stacked (see the CSS above).
                // On top of that: these two values render with white-space: pre-wrap, so
                // ANY template whitespace inside the div is preserved literally — the
                // newline after the opening tag becomes a blank first line, and the
                // indentation in front of the echo becomes a wide left indent on the
                // first line, so the text reads as centred/indented instead of
                // left-aligned. The echo must butt directly against the tags.
                ?>
                <div class="pd-row pd-row-stacked">
                    <div class="pd-label" style="margin-bottom: 6px;">Details to Include</div>
                    <div class="pd-value <?php echo pd_val($options, 'ai_details_to_include') === 'N/A' ? 'na' : '' ?>" style="white-space: pre-wrap;"><?php echo  pd_val($options, 'ai_details_to_include'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- helper escapes internally; wrapping again would double-encode ?></div>
                </div>
                <div class="pd-row pd-row-stacked" style="margin-top: 8px;">
                    <div class="pd-label" style="margin-bottom: 6px;">Call to Action</div>
                    <div class="pd-value <?php echo pd_val($options, 'ai_call_to_action') === 'N/A' ? 'na' : '' ?>" style="white-space: pre-wrap;"><?php echo  pd_val($options, 'ai_call_to_action'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- helper escapes internally; wrapping again would double-encode ?></div>
                </div>
            </div>
        </div>

        <!-- Card 4: SEO & Additional Settings -->
        <div class="pd-card">
            <div class="pd-card-header">SEO & Additional Settings</div>
            <div class="pd-card-body">
                <?php
                // The single-post wizard does not store these into the project
                // options, so pull them from the generated post instead: real
                // permalink and tags always exist, and title/description/keyword
                // come from the active SEO plugin's meta or the plugin's own
                // improveseo_custom_* meta (see pd_seo_meta). Fall back sensibly.
                $pd_post_id    = $associated_post ? $associated_post->ID : 0;

                // Meta Title is deliberately NOT backfilled from the post title:
                // that made an unset meta title read as "same as the post title",
                // which is a different (and wrong) statement about the post's SEO.
                // When nothing is set, say so via $pd_seo_placeholder below.
                $pd_meta_title = pd_seo_meta($pd_post_id, 'title');
                if ($pd_meta_title === '' && isset($options['custom_title'])) $pd_meta_title = trim((string) $options['custom_title']);

                // Meta Description keeps its existing fallback chain (incl. the
                // post excerpt) — unchanged pending the product decision.
                $pd_meta_desc = pd_seo_meta($pd_post_id, 'desc');
                if ($pd_meta_desc === '' && isset($options['custom_description'])) $pd_meta_desc = trim((string) $options['custom_description']);
                if ($pd_meta_desc === '' && $associated_post)                     $pd_meta_desc = trim((string) $associated_post->post_excerpt);

                $pd_focus_kw = pd_seo_meta($pd_post_id, 'focuskw');
                if ($pd_focus_kw === '' && isset($options['ai_seed_keyword'])) $pd_focus_kw = trim((string) $options['ai_seed_keyword']);
                if ($pd_focus_kw === '' && isset($options['custom_keywords'])) $pd_focus_kw = trim((string) $options['custom_keywords']);

                // "Auto-generated on publish" only makes sense before a post exists.
                // Once a post is live with no SEO meta set, "Not set" is the truth.
                $pd_seo_placeholder = $associated_post ? 'Not set' : 'Auto-generated on publish';
                ?>
                <div class="pd-row">
                    <div class="pd-label">Meta Title</div>
                    <div class="pd-value <?php echo  $pd_meta_title === '' ? 'na' : '' ?>">
                        <?php echo  $pd_meta_title !== '' ? esc_html($pd_meta_title) : esc_html($pd_seo_placeholder) ?>
                    </div>
                </div>
                <div class="pd-row">
                    <div class="pd-label">Meta Description</div>
                    <div class="pd-value <?php echo  $pd_meta_desc === '' ? 'na' : '' ?>">
                        <?php echo  $pd_meta_desc !== '' ? esc_html($pd_meta_desc) : esc_html($pd_seo_placeholder) ?>
                    </div>
                </div>
                <div class="pd-row">
                    <div class="pd-label">Focus Keyword</div>
                    <div class="pd-value <?php echo  $pd_focus_kw === '' ? 'na' : '' ?>">
                        <?php echo  $pd_focus_kw !== '' ? esc_html($pd_focus_kw) : 'N/A' ?>
                    </div>
                </div>
                <div class="pd-row">
                    <div class="pd-label">Permalink</div>
                    <div class="pd-value <?php echo  $post_url ? '' : 'na' ?>">
                        <?php if ($post_url): ?>
                            <a href="<?php echo  esc_url($post_url) ?>" target="_blank" rel="noopener"><?php echo  esc_html($post_url) ?></a>
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </div>
                </div>
                <?php if (intval($project->max_iterations) > 1): // single-post projects always cap at 1 ?>
                <div class="pd-row">
                    <div class="pd-label">Max Posts</div>
                    <div class="pd-value"><?php echo  intval($project->max_iterations) ?></div>
                </div>
                <?php endif; ?>
                <?php if (isset($options['dripfeed_type'])): ?>
                <div class="pd-row">
                    <div class="pd-label">Dripfeed</div>
                    <div class="pd-value"><?php echo  esc_html($options['dripfeed_type']) ?> – every <?php echo  esc_html($options['dripfeed_x']) ?> hours</div>
                </div>
                <?php endif; ?>
                <?php if (isset($options['schema']) && $options['schema']): ?>
                <div class="pd-row">
                    <div class="pd-label">Schema</div>
                    <div class="pd-value">Enabled – <?php echo  esc_html($options['schema_business'] ?? '') ?></div>
                </div>
                <?php endif; ?>
                <?php if (isset($options['local_geo_country'])): ?>
                <div class="pd-row">
                    <div class="pd-label">Local SEO</div>
                    <div class="pd-value">
                        Country: <?php echo  esc_html($options['local_geo_country']) ?>
                        (<?php echo  is_array($options['local_geo_locations'] ?? null) ? count($options['local_geo_locations']) : 0 ?> locations)
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    </div>
    </div>
</div>

<?php // Post preview: same in-modal instant preview the single post's create/edit form
// "Preview Post" button uses (assets/js/form.js -> improveseo_instant_preview in
// modules/ajax.php). That flow normally reads the live TinyMCE editor, which this
// read-only details page doesn't have, so the project's currently stored title/content
// are handed over via hidden fields instead. Styles live in assets/css/made_by_me.css,
// scoped under #preview_content_area. ?>
<textarea id="pd_preview_title_src" style="display:none;"><?php echo  esc_textarea($pd_preview_title) ?></textarea>
<textarea id="pd_preview_content_src" style="display:none;"><?php echo  esc_textarea($pd_preview_content) ?></textarea>
<?php wp_nonce_field('improveseo_instant_preview', 'pd_preview_nonce', false); ?>

<div id="preview_popup" class="modal" style="text-align:center; width:90%; max-width:1100px;">
    <div id="wh_prev_modal_1">
        <div id="iseo_preview_loading" class="iseo-preview-loading">
            <div class="iseo-preview-spinner" role="status" aria-label="Generating preview"></div>
            <b class="iseo-preview-loading-title">Generating preview</b>
            <span class="iseo-preview-loading-note">Rendering this post.</span>
            <button type="button" id="iseo_preview_cancel" class="button iseo-preview-action">Cancel</button>
        </div>
        <div id="iseo_preview_error" class="iseo-preview-error" style="display:none;">
            <p id="iseo_preview_error_text"></p>
            <button type="button" class="button iseo-preview-action iseo-preview-action--primary"
                onclick="pdClosePreview()">Close</button>
        </div>
    </div>
    <div id="wh_prev_modal_2" style="display:none;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
            <b style="font-size:18px">Post preview</b>
            <button type="button" id="open_win"
                class="button button-primary iseo-preview-action iseo-preview-action--primary"
                onclick="pdClosePreview()">Close preview</button>
        </div>
        <div id="preview_content_area" class="iseo-aicontent-wrap"></div>
    </div>
</div>

<script>
    var _pdPreviewXhr = null;

    jQuery(document).on('click', '#iseo_preview_cancel', function (e) {
        e.preventDefault();
        if (_pdPreviewXhr) { try { _pdPreviewXhr.abort(); } catch (ex) { /* done */ } _pdPreviewXhr = null; }
        jQuery.modal.close();
    });

    function pdClosePreview() {
        if (_pdPreviewXhr) { try { _pdPreviewXhr.abort(); } catch (ex) { /* done */ } _pdPreviewXhr = null; }
        jQuery.modal.close();
    }

    function pdPreviewFailed(message) {
        if (typeof message !== 'string' || !message) {
            message = 'Could not generate preview. Please close this and try again.';
        }
        jQuery('#iseo_preview_loading').hide();
        jQuery('#iseo_preview_error_text').text(message);
        jQuery('#iseo_preview_error').show();
    }

    function iseoPreviewProjectDraft() {
        jQuery('#iseo_preview_error').hide();
        jQuery('#iseo_preview_loading').show();
        jQuery('#wh_prev_modal_1').show();
        jQuery('#wh_prev_modal_2').hide();
        jQuery('#preview_popup').modal({
            escapeClose: false,
            clickClose: false,
            showClose: false,
            fadeDuration: 150,
            fadeDelay: 0.35
        });

        var title = jQuery.trim(jQuery('#pd_preview_title_src').val() || '');
        var content = jQuery.trim(jQuery('#pd_preview_content_src').val() || '');
        var nonce = jQuery('#pd_preview_nonce').val() || '';

        _pdPreviewXhr = jQuery.ajax({
            url: "<?php echo esc_url( admin_url('admin-ajax.php') ); ?>",
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'improveseo_instant_preview',
                title: title,
                content: content,
                nonce: nonce
            },
            success: function (res) {
                _pdPreviewXhr = null;
                if (!res || !res.success || !res.data) {
                    pdPreviewFailed(res && res.data ? res.data.message : '');
                    return;
                }
                var html = '<article class="iseo-aicontent-article">';
                html += '<h1 class="iseo-aicontent-title">' + jQuery('<div>').text(res.data.title).html() + '</h1>';
                html += '<div class="iseo-aicontent-body">' + res.data.html + '</div>';
                html += '</article>';
                jQuery('#preview_content_area').html(html);
                jQuery('#wh_prev_modal_1').hide();
                jQuery('#wh_prev_modal_2').show();
            },
            error: function () {
                _pdPreviewXhr = null;
                pdPreviewFailed();
            }
        });
    }
</script>

<?php View::endSection('content') ?>
<?php View::make('layouts.main') ?>
