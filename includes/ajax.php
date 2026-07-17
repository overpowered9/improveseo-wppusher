<?php


/* This file will handle all ajax requests from plugin */

// Test ImproveSEO server connection
add_action('wp_ajax_test_improveseo_connection', 'test_improveseo_connection');

function test_improveseo_connection() {
    // Verify nonce for security
    if (!wp_verify_nonce($_POST['nonce'], 'test_connection_nonce')) {
        wp_die('Security check failed');
    }
    
    // Use fixed server URL
    $server_url = 'https://imporve-seo-admin-server-nzbm.onrender.com';
    $api_key = sanitize_text_field($_POST['api_key']);
    $site_code = sanitize_text_field($_POST['site_code']);
    
    // Validate inputs
    if (empty($api_key) || empty($site_code)) {
        wp_send_json_error(array(
            'error' => 'Missing required fields: API Key or Site Code'
        ));
        return;
    }
    
    // Test the connection by making a simple request to the server
    $test_url = rtrim($server_url, '/') . '/api/v1/users/status';
    
    $response = wp_remote_get($test_url, array(
        'timeout' => 10,
        'headers' => array(
            'x-api-key' => $api_key,
            'x-site-code' => $site_code,
            'Content-Type' => 'application/json'
        )
    ));
    
    if (is_wp_error($response)) {
        wp_send_json_error(array(
            'error' => 'Failed to connect to server: ' . $response->get_error_message()
        ));
        return;
    }
    
    $status_code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    
    if ($status_code === 200) {
        $result = json_decode($body, true);
        wp_send_json_success(array(
            'server'         => 'Connected successfully',
            'user'           => isset($result['user']) ? $result['user'] : 'Authenticated',
            'email'          => isset($result['email']) ? $result['email'] : null,
            'credits'        => isset($result['credits']) ? $result['credits'] : null,
            // Richer plan/trial/credit context (added server-side) so the settings panel can
            // explain the account state. Null-safe: older servers simply omit these.
            'credit_details' => isset($result['credit_details']) ? $result['credit_details'] : null,
            'plan'           => isset($result['plan']) ? $result['plan'] : null,
            'trial'          => isset($result['trial']) ? $result['trial'] : null,
        ));
    } else {
        $error_data = json_decode($body, true);
        $error_message = isset($error_data['error']) ? $error_data['error'] : "Server returned status code: $status_code";
        
        wp_send_json_error(array(
            'error' => $error_message
        ));
    }
}


// Return the ImproveSEO account email the bulk-completion notification is sent to.
// Fetched live from the admin server (via the stored API key + site code) so the
// wizard never shows a stale/hardcoded address. Called client-side so a slow/cold
// server can't block page render and the value can't be frozen into cached HTML.
add_action('wp_ajax_improveseo_get_notification_email', 'improveseo_ajax_get_notification_email');

function improveseo_ajax_get_notification_email() {
    if ( ! current_user_can('manage_options') ) {
        wp_send_json_error(array('email' => ''), 403);
    }
    if ( ! isset($_POST['nonce']) || ! wp_verify_nonce( sanitize_text_field(wp_unslash($_POST['nonce'])), 'improveseo_notif_email_nonce' ) ) {
        wp_send_json_error(array('email' => ''), 403);
    }

    // Longer timeout than the render path — this runs async, so it can ride out a cold start.
    $email = function_exists('improveseo_get_account_email')
        ? improveseo_get_account_email(true, 30)
        : (string) get_option('improveseo_account_email', '');

    wp_send_json_success(array('email' => is_email($email) ? $email : ''));
}


add_action('wp_ajax_improveseo_get_shortcodes', 'improveseo_get_shortcodes');


function improveseo_get_shortcodes(){


    $improveseo_shortcode_type = sanitize_text_field($_POST['improveseo_shortcode_type']);


    $saved_rnos =  get_option('get_saved_random_numbers');


    $allowed_shortcode_types = array('testimonial', 'googlemap', 'button', 'video', 'list');


    $shortcode_html = '';





    if(in_array($improveseo_shortcode_type, $allowed_shortcode_types)){


        switch($improveseo_shortcode_type){


            case 'testimonial':


                foreach($saved_rnos as $id){


                    $testimonial = get_option('get_testimonials_'.$id);


                    if(!empty($testimonial)){


                        $display_name = 'Testimonial - '.$id;


				        $data_name = '';


                        if(isset($testimonial['tw_testi_shortcode_name'])){


                            if($testimonial['tw_testi_shortcode_name']!=""){


                                $data_name = $display_name = $testimonial['tw_testi_shortcode_name'];


                            }


                        }


                        $shortcode_html .= '<option value="'.$id.'" data-name="'.$data_name.'">'.$display_name.'</option>';


                    }


                }


            break;


            case 'button':


                foreach($saved_rnos as $id){


                    $button = get_option('get_buttons_'.$id);


                    $display_name = 'Button - '.$id;


                    $data_name = '';


                    if(isset($button['tw_button_shortcode_name'])){


                        if($button['tw_button_shortcode_name']!=""){


                            $data_name = $display_name = $button['tw_button_shortcode_name'];


                        }


                    }


                    if(!empty($button)){


                        $shortcode_html .= '<option value="'.$id.'" data-name="'.$data_name.'">'.$display_name.'</option>';


                    }


                }


            break;


            case 'googlemap':


                foreach($saved_rnos as $id){


                    $googlemap = get_option('get_googlemaps_'.$id);


                    if(!empty($googlemap)){


                        $display_name = 'GoogleMap - '.$id;


                        $data_name = '';


                        if(isset($googlemap['tw_maps_shortcode_name'])){


                            if($googlemap['tw_maps_shortcode_name']!=""){


                                $data_name = $display_name = $googlemap['tw_maps_shortcode_name'];


                            }


                        }


                        $shortcode_html .= '<option value="'.$id.'" data-name="'.$data_name.'">'.$display_name.'</option>';


                    }


                }


            break;


            case 'video':


                foreach($saved_rnos as $id){


                    $videos = get_option('get_videos_'.$id);


                    $display_name = 'Video - '.$id;


                    $data_name = '';


                    if(isset($videos['video_shortcode_name'])){


                        if($videos['video_shortcode_name']!=""){


                            $data_name = $display_name = $videos['video_shortcode_name'];


                        }


                    }


                    if(!empty($videos)){


                        $shortcode_html .= '<option value="'.$id.'" data-name="'.$data_name.'">'.$display_name.'</option>';


                    }


                }


            break;


            case 'list':


                $seo_list = improve_seo_lits();


                if(!empty($seo_list)){


                    foreach($seo_list as $list){


                        $shortcode_html .= '<option value="'.$list.'">@list: '.$list.'</option>';


                    }


                }


            break;





        }


    }else{


        echo json_encode(array('status' => 'empty array', 'data' => $improveseo_shortcode_type));


    }


    if($shortcode_html!=""){


        echo json_encode(array('status' => 'success', 'shortcode_html' => $shortcode_html));


    }else{


        echo json_encode(array('status' => 'failed'));


    }


    die;


}














