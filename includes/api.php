<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

use ImproveSEO\View;
use ImproveSEO\Spintax;
use ImproveSEO\Validator;
use ImproveSEO\Models\Country;
use ImproveSEO\Models\GeoData;
use ImproveSEO\Models\Shortcode;

global $wpdb;


if (isset($_GET['api']) && $_GET['api'] == 'improveseo') {
    $act = isset($_GET['action']) ? filter_var($_GET['action'], FILTER_SANITIZE_STRING) : '';
    $results = array();

    // Generate JSON data for GEO Tree
    if ($act == 'geo-tree') {
        $country = $_GET['country'] ? sanitize_text_field($_GET['country']) : "";

        // Show US Data
        if ($country == 'us') {
            if (isset($_GET['id']) && $_GET['id'] != '#') {
                $id = isset($_GET['id']) ? urldecode(filter_var($_GET['id'], FILTER_SANITIZE_STRING)) : '';
                // Show zip codes
                if (substr_count($id, '/') == 1) {
                    list($state, $city) = explode('/', $id);
                    $city_obj = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}improveseo_us_cities WHERE id = %s", $city));


                    $codes = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}improveseo_us_cities WHERE county = %s AND state_code = %s AND city = %s ORDER BY zip", $city_obj->county, $city_obj->state_code, $city_obj->city));

                    foreach ($codes as $code) {
                        $results[] = array(
                            'id' => $state . '/' . $city . '/' . $code->zip,
                            'text' => $code->zip,
                            'children' => false
                        );
                    }
                }                // Show cities
                else {
                    $cities = $wpdb->get_results(
                        $wpdb->prepare(
                            "SELECT * FROM {$wpdb->prefix}improveseo_us_cities WHERE state_code = %s GROUP BY county, state_code, city ORDER BY city",
                            $id
                        )
                    );

                    foreach ($cities as $city) {
                        $results[] = array(
                            'id' => $id . '/' . $city->id,
                            'text' => $city->city,
                            'children' => true
                        );
                    }
                }
            }            // Show states
            else {
                $states = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}improveseo_us_states"));
                foreach ($states as $state) {
                    $results[] = array(
                        'id' => $state->state_code,
                        'text' => $state->state,
                        'children' => true
                    );
                }
                if (count($states) == 0) {
                    global $wpdb;
                    global $improveseo_db_version;
                    global $wp_rewrite;

                    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                    include_once 'geo/installer.php';
                    $states = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}improveseo_us_states"));
                    foreach ($states as $state) {
                        $results[] = array(
                            'id' => $state->state_code,
                            'text' => $state->state,
                            'children' => true
                        );
                    }
                }
            }
        }        // Show UK Data
        elseif ($country == 'uk') {
            if (isset($_GET['id']) && $_GET['id'] != '#') {
                $id = isset($_GET['id']) ? urldecode(filter_var($_GET['id'], FILTER_SANITIZE_STRING)) : '';


                // Show zip codes
                if (substr_count($id, '/')) {
                    list($state, $city) = explode('/', $id);
                    $city_obj = $wpdb->get_row(
                        $wpdb->prepare(
                            "SELECT * FROM {$wpdb->prefix}improveseo_uk_cities WHERE id = %d",
                            $city
                        )
                    );


                    $codes = $wpdb->get_results(
                        $wpdb->prepare(
                            "SELECT * FROM {$wpdb->prefix}improveseo_uk_cities WHERE region_id = %d AND name = %s ORDER BY postcode",
                            $city_obj->region_id,
                            $city_obj->name
                        )
                    );


                    foreach ($codes as $code) {
                        $results[] = array(
                            'id' => $state . '/' . $city . '/' . $code->postcode,
                            'text' => $code->postcode,
                            'children' => false
                        );
                    }
                }                // Show cities
                else {
                    $cities = $wpdb->get_results(
                        $wpdb->prepare(
                            "SELECT * FROM {$wpdb->prefix}improveseo_uk_cities WHERE region_id = %d GROUP BY region_id, name ORDER BY name",
                            $id
                        )
                    );

                    foreach ($cities as $city) {
                        $results[] = array(
                            'id' => $id . '/' . $city->id,
                            'text' => $city->name,
                            'children' => true
                        );
                    }
                }
            }            // Show states
            else {
                $states = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}improveseo_uk_states ORDER BY name"));
                foreach ($states as $state) {
                    $results[] = array(
                        'id' => $state->id,
                        'text' => $state->name . ' [' . $state->country_short . ']',
                        'children' => true
                    );
                }
                if (count($states) == 0) {
                    global $wpdb;
                    global $improveseo_db_version;
                    global $wp_rewrite;

                    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                    include_once 'geo/installer.php';
                    $states = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}improveseo_uk_states ORDER BY name"));
                    foreach ($states as $state) {
                        $results[] = array(
                            'id' => $state->id,
                            'text' => $state->name . ' [' . $state->country_short . ']',
                            'children' => true
                        );
                    }
                }
            }
        }        // Show all other countries
        else {
            $model = new GeoData();

            if (isset($_GET['id']) && $_GET['id'] != '#') {
                $id = isset($_GET['id']) ? urldecode(filter_var($_GET['id'], FILTER_SANITIZE_STRING)) : '';


                // Show zip codes
                if (substr_count($id, '/')) {
                    list($state, $city) = explode('/', $id);

                    $zippo = $model->zippo($country, $state, $city);
                    foreach ($zippo as $zip) {
                        $results[] = array(
                            'id' => "$state/$city/{$zip->postal}",
                            'text' => $zip->postal,
                            'children' => false
                        );
                    }
                }                // Show cities
                else {
                    $cities = $model->cities($country, $id);
                    foreach ($cities as $city) {
                        $results[] = array(
                            'id' => "$id/{$city->id}",
                            'text' => $city->place,
                            'children' => true
                        );
                    }
                }
            } else {
                // Show states
                $states = $model->states($country);
                foreach ($states as $state) {
                    if (!$state->state)
                        continue;

                    $results[] = array(
                        'id' => $state->state_code,
                        'text' => $state->state,
                        'children' => true
                    );
                }
            }
        }
    }


    //    elseif ($act == 'count_posts') {
    //        include_once('functions.php');
    //
    //        // Sanitize the data
    //        $title = sanitize_text_field($_POST['title']);
    //        $content = sanitize_text_field($_POST['content']);
    //        $custom_title = sanitize_text_field($_POST['custom_title']);
    //        $custom_description = sanitize_text_field($_POST['custom_description']);
    //        $custom_keywords = sanitize_text_field($_POST['custom_keywords']);
    //        $permalink = sanitize_text_field($_POST['permalink']);
    //        $tags = sanitize_text_field($_POST['tags']);
    //
    //        // Validate the data
    //        if (empty($title) || empty($content) || empty($permalink)) {
    //            // Handle validation errors
    //            // For example, return an error message or redirect back to the form
    //            exit('Error: Missing required fields');
    //        }
    //
    //        $title = esc_html($title);
    //        $content = esc_html($content);
    //        $custom_title = esc_html($custom_title);
    //        $custom_description = esc_html($custom_description);
    //        $custom_keywords = esc_html($custom_keywords);
    //        $permalink = esc_url($permalink);
    //        $tags = esc_html($tags);
    //
    //
    //        $tags = improveseo_search_geotags(array(
    //            $title,
    //            $content,
    //            $custom_title,
    //            $custom_description,
    //            $custom_keywords,
    //            $permalink,
    //            $tags
    //        ));
    //
    //        $geo_iterations = 0;
    //
    //        if ($_POST['locations']) {
    //            $locations = improveseo_expand_geodata($_POST['country'], $_POST['locations'], $tags);
    //            $geo_iterations = sizeof($locations);
    //        }
    //
    //        if ($geo_iterations == 0)
    //            $iterations = max(1, Spintax::count(Spintax::parse($title)));
    //        else
    //            $iterations = $geo_iterations;
    //
    //        // Count list items
    //        $filteredObjects = array_filter($_POST, function ($obj) {
    //            return isset($obj->size);
    //        });
    //
    //
    //        $items = improveseo_count_list_items($filteredObjects);
    //
    //        if ($items == 0) {
    //            $items = 1;
    //        }
    //
    //        $results['posts'] = $geo_iterations ? $geo_iterations * $items : ($items ? $items : $iterations);
    //    }
    //
    elseif ($act == 'shortcode') {

        // Check for nonce and user permissions
        check_ajax_referer('_wpnonce', 'security');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized', 403);
            return;
        }

        // Verify the nonce



        $shortcodeModel = new Shortcode();

        $shortcode = isset($_POST['shortcode']) ? sanitize_text_field($_POST['shortcode']) : '';
        $media = isset($_POST['media']) ? sanitize_text_field($_POST['media']) : '';

        // Check if required fields are present
        if (empty($shortcode) || empty($media)) {
            // Handle missing required fields
            header(sanitize_text_field($_SERVER['SERVER_PROTOCOL']) . ' 400 Bad Request', true, 400);
            echo 'Bad Request: Missing required fields.';
            exit();
        }
        if (!Validator::validate(compact('shortcode', 'media'), array(
            'shortcode' => 'required|unique:' . $shortcodeModel->getTable() . ',shortcode',
            'media' => 'required'
        ))) {
            header(sanitize_text_field($_SERVER['SERVER_PROTOCOL']) . ' 500 Internal Server Error', true, 500);
            echo esc_html(Validator::get('shortcode'));
            exit();
        }
        $shortcode = isset($_POST['shortcode']) ? sanitize_text_field($_POST['shortcode']) : '';

        // Download all media files
        foreach (sanitize_text_field($_POST['media']) as &$media) {
            if (isset($media['url'])) {
                $image = str_replace(':8000', '', esc_attr(sanitize_text_field($media['url']))); // Fix only for local dev
                $filename = sha1($image . '-' . $shortcode) . '.jpg';

                $exploded = explode('.', $image);
                $ext = $exploded[count($exploded) - 1];

                if (preg_match('/jpg|jpeg/i', $ext))
                    $imageSrc = imagecreatefromjpeg($image);
                else if (preg_match('/png/i', $ext))
                    $imageSrc = imagecreatefrompng($image);
                else if (preg_match('/gif/i', $ext))
                    $imageSrc = imagecreatefromgif($image);
                else if (preg_match('/bmp/i', $ext))
                    $imageSrc = imagecreatefromwbmp($image);

                $imagedir = 'uploads/' . date('Y') . '/' . date('m') . '/' . $filename;

                include_once 'functions.php';
                improveseo_check_dir($imagedir);

                imagejpeg($imageSrc, WP_CONTENT_DIR . '/' . $imagedir);

                $media['url'] = "/wp-content/$imagedir";
            }
        }

        $media = isset($_POST['media']) ? sanitize_text_field($_POST['media']) : '';

        $media = json_encode($media);

        $sql = $wpdb->prepare("INSERT INTO {$wpdb->prefix}improveseo_shortcodes (shortcode, type, content) VALUES (%s, %s, %s)", array(
            $shortcode,
            'static',
            $media
        ));

        $wpdb->query($sql);

        $results['success'] = 1;
    } elseif ($act == 'exif') {
        View::render('exif.index');

        exit();
    } elseif ($act == 'word-ai') {
        $text = sanitize_text_field($_POST['text']);
        $quality = isset($_POST['quality']) ? sanitize_text_field($_POST['quality']) : '';

        $email = sanitize_email($_POST['email']);
        $pass = sanitize_text_field($_POST['pass']);

        $query = array(
            's' => esc_html($text),
            'quality' => $quality,
            'email' => get_option('improveseo_word_ai_email'),
            'pass' => get_option('improveseo_word_ai_pass'),
            'nonested' => isset($_POST['nonested']) ? 1 : 0,
            'paragraph' => isset($_POST['paragraph']) ? 1 : 0,
            'nooriginal' => isset($_POST['nooriginal']) ? 1 : 0,
            'protected' => isset($_POST['protected']) ? 1 : 0,
            'output' => 'json'
        );


        $response = wp_safe_remote_post('http://wordai.com/users/turing-api.php', array(
            'body' => $query,
        ));

        if (!is_wp_error($response)) {
            $results = json_decode(wp_remote_retrieve_body($response), true);
        } else {
            // Handle the error, e.g., $response->get_error_message()
        }
    }
}
