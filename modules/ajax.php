<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use ImproveSEO\View;
use ImproveSEO\Spintax;

use ImproveSEO\Validator;
use ImproveSEO\LiteSpintax;

use ImproveSEO\Models\Task;
use ImproveSEO\FlashMessage;

add_action('wp_ajax_improveseo_generate_preview', 'improveseo_generate_preview');


function improveseo_generate_preview() {
    global $wpdb;
    $model = new Task();
    $name = sanitize_text_field($_POST['name']);
    $title = wp_kses_post($_POST['title']);
    $content = wp_kses_post($_POST['content']);
    $post_type = sanitize_text_field($_POST['post_type']);

    $project_data = array(
        'title' => $title,
        'content' => $content,
        'post_type' => $post_type
    );

    $iterations = [
        // title
        Spintax::count(Spintax::parse($title)),
        // content
        Spintax::count(Spintax::parse($content))
    ];

    $options_data = array();

    // On-Page SEO
    if (isset($_POST['on_page_seo'])) {
        $options_data['custom_title'] = wp_kses_post($_POST['custom_title']);
        $options_data['custom_description'] = wp_kses_post($_POST['custom_description']);
        $options_data['custom_keywords'] = wp_kses_post($_POST['custom_keywords']);

        $iterations[] = Spintax::count(Spintax::parse($options_data['custom_title']));
        $iterations[] = Spintax::count(Spintax::parse($options_data['custom_description']));
        $iterations[] = Spintax::count(Spintax::parse($options_data['custom_keywords']));
    }

    // Local SEO
    $geo_iterations = 1;

    if (isset($_POST['local_seo_enabler'])) {
        // Search tags and remove non-used locations
        $tags = improveseo_search_geotags(array(
            $title,
            $content,
            sanitize_text_field($_POST['custom_title']),
            sanitize_text_field($_POST['custom_description']),
            sanitize_text_field($_POST['custom_keywords']),
            sanitize_text_field($_POST['permalink']),
            sanitize_text_field($_POST['tags'])
        ));
        $options_data['local_geo_country'] = sanitize_text_field($_POST['local_country']);
        $options_data['local_geo_locations'] = json_decode(sanitize_text_field(stripslashes($_POST['local_geo_locations'])), true);

        // Do not expand geo data if saving as draft
        if (isset($_POST['create'])) {
            $options_data['local_geo_locations'] = improveseo_expand_geodata($options_data['local_geo_country'], $options_data['local_geo_locations'], $tags);
        }

        $geo_iterations = count($options_data['local_geo_locations']);
        if ($geo_iterations == 0) $geo_iterations = 1;

        if (isset($_POST['local_randomize'])) {
            shuffle($options_data['local_geo_locations']);
            $options_data['local_randomize'] = sanitize_text_field($_POST['local_randomize']);
        }

        // Categorization
        if (isset($_POST['enable_categorization'])) {
            $options_data['enable_categorization'] = true;

            $tags_order = array('country', 'state', 'city', 'zip');

            foreach ($tags_order as $tag) {
                if (!in_array($tag, $tags)) continue;

                $options_data['categorization'][] = $tag;
            }
        }
    }

    // Schema SEO
    if (isset($_POST['schema'])) {
        $options_data['schema'] = true;
        $options_data['schema_business'] = wp_kses_post($_POST['schema_business']);
        $options_data['schema_description'] = wp_kses_post($_POST['schema_description']);
        $options_data['schema_email'] = sanitize_email($_POST['schema_email']);
        $options_data['schema_telephone'] = sanitize_text_field($_POST['schema_telephone']);
        $options_data['schema_social'] = sanitize_text_field($_POST['schema_social']);
        $options_data['schema_rating_object'] = sanitize_text_field($_POST['schema_rating_object']);
        $options_data['schema_rating'] = sanitize_text_field($_POST['schema_rating']);
        $options_data['schema_rating_count'] = sanitize_text_field($_POST['schema_rating_count']);
        $options_data['schema_address'] = wp_kses_post($_POST['schema_address']);

        if (isset($_POST['hide_schema'])) {
            $options_data['hide_schema'] = true;
        }

        $iterations[] = Spintax::count(Spintax::parse($options_data['schema_business']));
        $iterations[] = Spintax::count(Spintax::parse($options_data['schema_description']));
        $iterations[] = Spintax::count(Spintax::parse($options_data['schema_email']));
        $iterations[] = Spintax::count(Spintax::parse($options_data['schema_social']));
        $iterations[] = Spintax::count(Spintax::parse($options_data['schema_address']));
        $iterations[] = Spintax::count(Spintax::parse($options_data['schema_rating_object']));
    }

    // Dripfeed Feature
    if (isset($_POST['dripfeed_enabler'])) {
        $options_data['dripfeed_type'] = sanitize_text_field($_POST['dripfeed_type']);
        $options_data['dripfeed_x'] = sanitize_text_field($_POST['dripfeed_x']);
    }

    // Image EXIF
    if (isset($_POST['exif_enabler'])) {
        $options_data['exif_locations'] = sanitize_text_field($_POST['exif_locations']);
    }
    if (isset($_POST['use_post_location'])) {
        $options_data['use_post_location'] = true;
    }

    // Permalink
    if (!empty($_POST['permalink'])) {
        $options_data['permalink'] = sanitize_text_field($_POST['permalink']);
    }
    if (!empty($_POST['permalink_prefix'])) {
        $options_data['permalink_prefix'] = sanitize_title($_POST['permalink_prefix']);
    }

    // Tags
    if (!empty($_POST['tags'])) {
        $options_data['tags'] = sanitize_text_field($_POST['tags']);
    }
    if (isset($_POST['noindex_tags'])) {
        $options_data['noindex_tags'] = true;
    }

    // Distribute
    if (isset($_POST['distribute'])) {
        $options_data['distribute'] = true;
    }

    // Channel pages
    if (isset($_POST['state_channel_page'])) {
        $project_data['state_channel_enabled'] = true;
        $project_data['state_channel_title'] = sanitize_text_field($_POST['state_channel_title']);
        $project_data['state_channel_page'] = sanitize_text_field($_POST['state_channel_content']);
    }
    if (isset($_POST['city_channel_page'])) {
        $project_data['city_channel_enabled'] = true;
        $project_data['city_channel_title'] = sanitize_text_field($_POST['city_channel_title']);
        $project_data['city_channel_page'] = sanitize_text_field($_POST['city_channel_content']);
    }


    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    $content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_STRING);
    $custom_title = filter_input(INPUT_POST, 'custom_title', FILTER_SANITIZE_STRING);
    $custom_description = filter_input(INPUT_POST, 'custom_description', FILTER_SANITIZE_STRING);
    $custom_keywords = filter_input(INPUT_POST, 'custom_keywords', FILTER_SANITIZE_STRING);
    $permalink = filter_input(INPUT_POST, 'permalink', FILTER_SANITIZE_URL);
    $tags = filter_input(INPUT_POST, 'tags', FILTER_SANITIZE_STRING);

    $fields = array($title, $content, $custom_title, $custom_description, $custom_keywords, $permalink, $tags);
    // Math maximum number of posts
    // Count list items
    $items = improveseo_count_list_items($fields);

    if (isset($_POST['local_seo_enabler'])) {
        if (!$items) $items = 1;
        $max = ($_POST['max_posts'] <= 0) ? $geo_iterations * $items : intval($_POST['max_posts']);
    } else {
        $max = ($_POST['max_posts'] <= 0) ? ($items ? $items : Spintax::count(Spintax::parse($title))) : intval($_POST['max_posts']);
    }
    if (isset($_POST['max_posts'])) {
        $options_data['max_posts'] = sanitize_text_field($_POST['max_posts']);
    }

    $data = array(
        'name' => $name,
        'content' => base64_encode(wp_json_encode($project_data)),
        'options' => base64_encode(wp_json_encode($options_data)),
        'state' => isset($_POST['draft']) ? 'Draft' : 'Published',
        'iteration' => 0,
        'spintax_iterations' => 1,
        'max_iterations' => 1,
        'cats' => wp_json_encode($_POST['cats'])
    );

    $wpdb->query("SET GLOBAL max_allowed_packet = 268435456");
    $project_id = $model->create($data);

    echo wp_json_encode(array('status' => 'success', 'project_id' => intval($project_id)));

    die;
}
