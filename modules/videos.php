<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly  

foreach ($id as $p) {
    $data_video = get_option('get_videos_' . $p);

    $video_type = isset($data_video['video_type']) ? $data_video['video_type'] : 'upload_video';
    $video_settings = array_map('esc_html', $data_video); // Escape HTML for all settings

    $video = '<span class="improveseo_row">';

    switch ($video_type) {
        case 'upload_video':
            $video .= improveseo_generate_upload_video_html($video_settings);
            break;
        case 'youtube':
            $video .= improveseo_generate_youtube_video_html($video_settings);
            break;
        case 'vimeo':
            $video .= improveseo_generate_vimeo_video_html($video_settings);
            break;
    }

    $video .= '</span>';
    echo $video;
}

// Functions to generate HTML for each video type
function improveseo_generate_upload_video_html($settings)
{
    $video_html = '';

    if (!empty($settings['video_url_mp4']) || !empty($settings['video_url_ogv']) || !empty($settings['video_url_webm'])) {
        $video_html .= '<video id="improveseo_custom_video_upload" width="' . $settings['video_width'] . '" height="' . $settings['video_height'] . '"';
        $video_html .= ' poster="' . esc_url($settings['video_poster_img_source']) . '"';

        if ($settings['video_autoplay'] === 'yes') {
            $video_html .= ' autoplay';
        }

        if ($settings['video_muted'] === 'yes') {
            $video_html .= ' muted';
        }

        if ($settings['video_controls'] === 'yes') {
            $video_html .= ' controls';
        }

        if ($settings['video_loop'] === 'yes') {
            $video_html .= ' loop';
        }

        $video_html .= '>';

        if (!empty($settings['video_url_mp4'])) {
            $video_html .= '<source src="' . esc_url($settings['video_url_mp4']) . '" type="video/mp4">';
        }

        if (!empty($settings['video_url_ogv'])) {
            $video_html .= '<source src="' . esc_url($settings['video_url_ogv']) . '" type="video/ogg">';
        }

        if (!empty($settings['video_url_webm'])) {
            $video_html .= '<source src="' . esc_url($settings['video_url_webm']) . '" type="video/webm">';
        }

        $video_html .= 'Your browser does not support the video tag.';
        $video_html .= '</video>';
    }

    return $video_html;
}

function improveseo_generate_youtube_video_html($settings)
{
    $youtube_url = improveseo_generate_youtube_url($settings['video_url_youtube']);
    $youtube_id = improveseo_get_youtube_id($settings['video_url_youtube']);
    $youtube_url .= '?rel=0&showinfo=0';
    $allow = '';

    if ($settings['video_autoplay'] === 'yes') {
        $youtube_url .= '&autoplay=1';
        $allow .= 'autoplay';
    }

    $youtube_url .= '&mute=1';
    if ($settings['video_muted'] === 'yes') {
        $youtube_url .= '&mute=1';
    } else {
        $youtube_url .= '&mute=0';
    }

    if ($settings['video_controls'] === 'yes') {
        $youtube_url .= '&controls=1';
    } else {
        $youtube_url .= '&controls=0';
    }

    if ($settings['video_loop'] === 'yes') {
        $youtube_url .= '&loop=1';
        $youtube_url .= '&playlist=' . $youtube_id;
    }

    return '<iframe type="text/html" class="fitvidsignore" src="' . $youtube_url . '" width="' . $settings['video_width'] . '" height="' . $settings['video_height'] . '" allow="' . $allow . '" style="margin:10px 0;" frameborder="0" allowfullscreen></iframe>';
}

function improveseo_generate_vimeo_video_html($settings)
{
    $vimeo_url = improveseo_generate_vimeo_url($settings['video_url_vimeo']);
    $vimeo_url .= '?';
    $allow = $video_style = '';

    if ($settings['video_autoplay'] === 'yes') {
        $vimeo_url .= '&autoplay=1';
        $allow .= 'autoplay;';
    }
    $allow .= 'fullscreen; picture-in-picture;';

    if ($settings['video_loop'] === 'yes') {
        $vimeo_url .= '&loop=1';
    }

    if ($settings['video_muted'] === 'yes') {
        $vimeo_url .= '&muted=1';
    }

    if ($settings['video_controls'] === 'yes') {
        $vimeo_url .= '&controls=0';
    }

    $vimeo_url .= '&portrait=0';

    $video_html = '';
    if ($settings['video_height'] === 'auto' && $settings['video_width'] === '100%') {
        $video_html .= '<div style="padding:56.25% 0 0 0;position:relative;">';
        $video_style .= 'position:absolute;top:0;left:0;width:100%;height:100%;';
    }

    $video_html .= '<iframe class="fitvidsignore" src="' . $vimeo_url . '" width="' . $settings['video_width'] . '" height="' . $settings['video_height'] . '" frameborder="0" allow="' . $allow . '" style="' . $video_style . ' margin:10px 0;"></iframe>';

    if ($settings['video_height'] === 'auto' && $settings['video_width'] === '100%') {
        $video_html .= '</div><script src="https://player.vimeo.com/api/player.js"></script>';
    }

    return $video_html;
}
