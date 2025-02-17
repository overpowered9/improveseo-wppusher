<?php

use ImproveSEO\View;
use ImproveSEO\Spintax;
use ImproveSEO\Validator;
use ImproveSEO\LiteSpintax;
use ImproveSEO\Models\Task;
use ImproveSEO\FlashMessage;

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

function improveseo_posting()
{

  


    global $wpdb;

    $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'index';
    $model = new Task();

    // Main posting page
    if ($action == 'index'):
        View::render('posting.index');

        // Create post page
    elseif ($action == 'create_post'):
        View::render('posting.create-post');

    elseif ($action == 'create_page'):
        View::render('posting.create-page');

    elseif ($action == 'do_create_post'):
        // Verify the nonce
        if (!isset(($_POST['improveseo_do_create_post_nonce'])) || !wp_verify_nonce(sanitize_text_field($_POST['improveseo_do_create_post_nonce']), 'improveseo_do_create_post_nonce')) {
            wp_die("Nonce verification failed");  // If the nonce is invalid, terminate the script
        }

        // Check if the user has the right permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error("Unauthorized", 403);
            return;
        }

        if (isset($_POST['create'])) {
            $validation_rules = array(
                'name' => 'required',
                'title' => 'required',
                'content' => 'required',
                'post_type' => 'required|post_type',
                'max_posts' => 'numeric',
                'dripfeed_x' => 'required_if:dripfeed_enabler|numeric',
                'city_channel_title' => 'required_if:city_channel_page',
                'city_channel_content' => 'required_if:city_channel_page',
                'state_channel_title' => 'required_if:state_channel_page',
                'state_channel_content' => 'required_if:state_channel_page'
            );
            $fields_to_validate = array(
                'name' => filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING),
                'title' => filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING),
                'content' => filter_input(INPUT_POST, 'content', FILTER_UNSAFE_RAW),
                'post_type' => filter_input(INPUT_POST, 'post_type', FILTER_SANITIZE_STRING),
                'max_posts' => filter_input(INPUT_POST, 'max_posts', FILTER_VALIDATE_INT),
                'dripfeed_x' => filter_input(INPUT_POST, 'dripfeed_x', FILTER_VALIDATE_INT),
                'city_channel_title' => filter_input(INPUT_POST, 'city_channel_title', FILTER_SANITIZE_STRING),
                'city_channel_content' => filter_input(INPUT_POST, 'city_channel_content', FILTER_SANITIZE_STRING),
                'state_channel_title' => filter_input(INPUT_POST, 'state_channel_title', FILTER_SANITIZE_STRING),
                'state_channel_content' => filter_input(INPUT_POST, 'state_channel_content', FILTER_SANITIZE_STRING)
            );

            if (!Validator::validate($fields_to_validate, $validation_rules) && !isset($_POST['draft'])) {
                wp_redirect(admin_url('admin.php?page=improveseo_posting&action=create_post'));
                exit;
            }
        }

        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
        $content = isset($_POST['content']) ? wp_kses_post($_POST['content']) : ''; // Allow HTML tags

        $post_type = isset($_POST['post_type']) ? sanitize_text_field($_POST['post_type']) : '';

        $project_data = array(
            'title' => stripslashes($title),
            'content' => stripslashes($content),
            'post_type' => $post_type
        );

        $iterations = array(
            Spintax::count(Spintax::parse($title)),
            Spintax::count(Spintax::parse($content))
        );

        $options_data = array();

        // On-Page SEO
        if (isset($_POST['on_page_seo'])) {
            $options_data['custom_title'] = isset($_POST['custom_title']) ? sanitize_text_field($_POST['custom_title']) : '';
            $options_data['custom_description'] = isset($_POST['custom_description']) ? sanitize_text_field($_POST['custom_description']) : '';
            $options_data['custom_keywords'] = isset($_POST['custom_keywords']) ? sanitize_text_field($_POST['custom_keywords']) : '';

            $iterations[] = Spintax::count(Spintax::parse($options_data['custom_title']));
            $iterations[] = Spintax::count(Spintax::parse($options_data['custom_description']));
            $iterations[] = Spintax::count(Spintax::parse($options_data['custom_keywords']));
        }

        // Math maximum number of posts
        // Count list items
        // Sanitize and validate the input
        $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
        $content = filter_input(INPUT_POST, 'content', FILTER_UNSAFE_RAW);
        $custom_title = filter_input(INPUT_POST, 'custom_title', FILTER_SANITIZE_STRING);
        $custom_description = filter_input(INPUT_POST, 'custom_description', FILTER_SANITIZE_STRING);
        $custom_keywords = filter_input(INPUT_POST, 'custom_keywords', FILTER_SANITIZE_STRING);
        $permalink = filter_input(INPUT_POST, 'permalink', FILTER_SANITIZE_URL);
        $tags = filter_input(INPUT_POST, 'tags', FILTER_SANITIZE_STRING);

        $fields = array($title, $content, $custom_title, $custom_description, $custom_keywords, $permalink, $tags);

        $items = improveseo_count_list_items($fields);

        if (isset($_POST['local_seo_enabler'])) {
            if (!$items)
                $items = 1;
            $max = ($_POST['max_posts'] <= 0) ? $geo_iterations * $items : intval($_POST['max_posts']);
        } else {
            $max = ($_POST['max_posts'] <= 0) ? ($items ? $items : Spintax::count(Spintax::parse($title))) : intval($_POST['max_posts']);
        }
        if (isset($_POST['max_posts'])) {
            $options_data['max_posts'] = isset($_POST['max_posts']) ? intval($_POST['max_posts']) : 0;
        }

        $data = array(
            'name' => $name,
            'content' => base64_encode(json_encode($project_data)),
            'options' => base64_encode(json_encode($options_data)),
            'state' => isset($_POST['draft']) ? 'Draft' : 'Published',
            'iteration' => 0,
            'spintax_iterations' => max($iterations),
            //'max_iterations' => max($iterations) * $geo_iterations
            'max_iterations' => $max,
            'cats' => json_encode(sanitize_text_field($_POST['cats']))
        );
        $wpdb->query("SET GLOBAL max_allowed_packet = 268435456");

        $project_id = isset($_GET['id']) ? intval($_GET['id']) : null;
        if ($project_id) {
            $project_id = $model->update($data, $project_id);
        } else {
            $project_id = $model->create($data);
        }

        if (isset($_POST['create'])) {
            FlashMessage::success(
                'Project successfully created. It will generate (' . htmlspecialchars($data['max_iterations']) . ') posts/pages.'
            );
        } elseif (isset($_POST['draft'])) {
            FlashMessage::success('Project successfully saved. You can continue editing by pressing Continue button.');
        }

        wp_redirect(admin_url("admin.php?page=improveseo_projects&highlight={$project_id}"));
        exit;

    elseif ($action == 'do_update_post'):

        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
        $content = isset($_POST['content']) ? wp_kses_post($_POST['content']) : '';

        $post_type = isset($_POST['post_type']) ? sanitize_text_field($_POST['post_type']) : '';

        $project_data = array(
            'title' => stripslashes($title),
            'content' => stripslashes($content),
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
        if (isset($_POST['local_seo_enabler'])) {
            // Search tags and remove non-used locations
            $tags = improveseo_search_geotags(array(
                sanitize_text_field($title),
                sanitize_text_field($content),
                sanitize_text_field($_POST['custom_title']),
                sanitize_text_field($_POST['custom_description']),
                sanitize_text_field($_POST['custom_keywords']),
                esc_url_raw($_POST['permalink']),
                sanitize_text_field($_POST['tags'])
            ));

            $options_data['local_geo_country'] = isset($_POST['local_country']) ? sanitize_text_field($_POST['local_country']) : '';
            $local_geo_locations_raw = isset($_POST['local_geo_locations']) ? stripslashes(sanitize_text_field($_POST['local_geo_locations'])) : '';
            $options_data['local_geo_locations'] = json_decode($local_geo_locations_raw, true);

            // Do not expand geo data if saving as draft
            if (isset($_POST['create'])) {
                $options_data['local_geo_locations'] = improveseo_expand_geodata($options_data['local_geo_country'], $options_data['local_geo_locations'], $tags);
            }

            $geo_iterations = count($options_data['local_geo_locations']);
            $geo_iterations = $geo_iterations === 0 ? 1 : $geo_iterations;

            if (isset($_POST['local_randomize'])) {
                shuffle($options_data['local_geo_locations']);
                $options_data['local_randomize'] = isset($_POST['local_randomize']) ? sanitize_text_field($_POST['local_randomize']) : '';
            }

            // Categorization
            if (isset($_POST['enable_categorization'])) {
                $options_data['enable_categorization'] = true;

                $tags_order = array('country', 'state', 'city', 'zip');

                foreach ($tags_order as $tag) {
                    if (!in_array($tag, $tags)) {
                        continue;
                    }

                    $options_data['categorization'][] = sanitize_text_field($tag);
                }
            }
        }

        // Local SEO
        $geo_iterations = 1;

        if (isset($_POST['local_seo_enabler'])) {
            // Search tags and remove non-used locations
            $tags = improveseo_search_geotags(array(
                sanitize_text_field($title),
                sanitize_text_field($content),
                sanitize_text_field($_POST['custom_title']),
                sanitize_text_field($_POST['custom_description']),
                sanitize_text_field($_POST['custom_keywords']),
                esc_url_raw($_POST['permalink']),
                sanitize_text_field($_POST['tags'])
            ));

            $options_data['local_geo_country'] = isset($_POST['local_country']) ? sanitize_text_field($_POST['local_country']) : '';
            $local_geo_locations_raw = isset($_POST['local_geo_locations']) ? stripslashes(sanitize_text_field($_POST['local_geo_locations'])) : '';
            $options_data['local_geo_locations'] = json_decode($local_geo_locations_raw, true);

            // Do not expand geo data if saving as draft
            if (isset($_POST['create'])) {
                $options_data['local_geo_locations'] = improveseo_expand_geodata($options_data['local_geo_country'], $options_data['local_geo_locations'], $tags);
            }

            $geo_iterations = count($options_data['local_geo_locations']);
            $geo_iterations = $geo_iterations === 0 ? 1 : $geo_iterations;

            if (isset($_POST['local_randomize'])) {
                shuffle($options_data['local_geo_locations']);
                $options_data['local_randomize'] = isset($_POST['local_randomize']) ? sanitize_text_field($_POST['local_randomize']) : '';
            }

            // Categorization
            if (isset($_POST['enable_categorization'])) {
                $options_data['enable_categorization'] = true;

                $tags_order = array('country', 'state', 'city', 'zip');

                foreach ($tags_order as $tag) {
                    if (in_array($tag, $tags)) {
                        $options_data['categorization'][] = sanitize_text_field($tag);
                    }
                }
            }
        }


        // Schema SEO
        if (isset($_POST['schema'])) {
            $options_data['schema'] = true;
            $options_data['schema_business'] = sanitize_text_field($_POST['schema_business']);
            $options_data['schema_description'] = sanitize_text_field($_POST['schema_description']);
            $options_data['schema_email'] = sanitize_email($_POST['schema_email']);
            $options_data['schema_telephone'] = sanitize_text_field($_POST['schema_telephone']);
            $options_data['schema_social'] = sanitize_text_field($_POST['schema_social']);
            $options_data['schema_rating_object'] = sanitize_text_field($_POST['schema_rating_object']);
            $options_data['schema_rating'] = sanitize_text_field($_POST['schema_rating']);
            $options_data['schema_rating_count'] = intval($_POST['schema_rating_count']);
            $options_data['schema_address'] = sanitize_text_field($_POST['schema_address']);

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
            $dripfeed_type = filter_input(INPUT_POST, 'dripfeed_type', FILTER_SANITIZE_STRING);
            if ($dripfeed_type !== false && ($dripfeed_type === 'per-day' || $dripfeed_type === 'over-days')) {
                $options_data['dripfeed_type'] = $dripfeed_type;
            }
            $dripfeed_x = filter_input(INPUT_POST, 'dripfeed_x', FILTER_VALIDATE_INT);
            if ($dripfeed_x !== false && $dripfeed_x > 0) {
                $options_data['dripfeed_x'] = $dripfeed_x;
            }
        }

        // Image EXIF
        if (isset($_POST['exif_enabler'])) {
            $options_data['exif_locations'] = isset($_POST['exif_locations']) ? sanitize_text_field($_POST['exif_locations']) : '';

            // Validate the exif_locations data if needed (e.g., check if it is a valid format)

            // Escape the exif_locations data before using it (e.g., if it is displayed in HTML)
            $options_data['exif_locations'] = esc_html($options_data['exif_locations']);
        }
        if (isset($_POST['use_post_location'])) {
            $options_data['use_post_location'] = true;
        }

        // Permalink
        $options_data['permalink'] = isset($_POST['permalink']) ? esc_url_raw(sanitize_text_field($_POST['permalink'])) : '';

        $options_data['permalink_prefix'] = isset($_POST['permalink_prefix']) ? sanitize_title($_POST['permalink_prefix']) : '';

        // Sanitize and validate tags
        $options_data['tags'] = isset($_POST['tags']) ? sanitize_text_field($_POST['tags']) : '';

        // Sanitize and validate noindex_tags
        $options_data['noindex_tags'] = isset($_POST['noindex_tags']) ? true : false;

        // Sanitize and validate distribute
        $options_data['distribute'] = isset($_POST['distribute']) ? true : false;


        // Channel pages
        if (isset($_POST['state_channel_page'])) {
            $project_data['state_channel_enabled'] = true;
            $project_data['state_channel_title'] = sanitize_title($_POST['state_channel_title']);
            $project_data['state_channel_page'] = sanitize_title($_POST['state_channel_content']);
        }
        if (isset($_POST['city_channel_page'])) {
            $project_data['city_channel_enabled'] = true;
            $project_data['city_channel_title'] = sanitize_title($_POST['city_channel_title']);
            $project_data['city_channel_page'] = sanitize_title($_POST['city_channel_content']);
        }


        //        $filteredObjects = array_filter($_POST, function ($obj) {
        //            return isset($obj->size);
        //        });


        // Math maximum number of posts
        $items = improveseo_count_list_items(sanitize_text_field($_POST['size']));


        $max = isset($_POST['max_posts']) ? intval($_POST['max_posts']) : 0;
        if (isset($_POST['local_seo_enabler']) && !$items) {
            $items = 1;
        }

        $max = $max <= 0 ? ($items ? $items : Spintax::count(Spintax::parse($title))) : $max;
        // Sanitize and validate max_posts
        $options_data['max_posts'] = isset($_POST['max_posts']) ? absint($_POST['max_posts']) : '';

        $cats = isset($_POST['cats']) && is_array($_POST['cats']) ? sanitize_text_field($_POST['cats']) : [];
        $data = array(
            'name' => $name,
            'content' => base64_encode(json_encode($project_data)),
            'options' => base64_encode(json_encode($options_data)),
            'state' => 'Updated',
            'iteration' => 0,
            'spintax_iterations' => max($iterations),
            'max_iterations' => $max,
            'cats' => json_encode($cats)
        );
        $wpdb->query("SET GLOBAL max_allowed_packet = 268435456");

        $id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;
        $project_id = $model->update($data, $id);

        FlashMessage::success('Project successfully updated. You can update old post by clicking update my posts.');
        wp_redirect(admin_url("admin.php?page=improveseo_projects&highlight={$project_id}&build_posts_id={$project_id}"));
        exit;

    elseif ($action == 'edit_post'):
        $id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;
        $task = $model->find($id);

        View::render('posting.edit-post', compact('task'));
    endif;
}
