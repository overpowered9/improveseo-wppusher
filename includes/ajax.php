<?php
/* This file will handle all ajax requests from plugin */
add_action('wp_ajax_improveseo_get_shortcodes', 'improveseo_get_shortcodes');
function improveseo_get_shortcodes(){
    $improveseo_shortcode_type = sanitize_text_field($_POST['improveseo_shortcode_type']);
    $saved_rnos =  get_option('get_saved_random_numbers');
    $allowed_shortcode_types = array('testimonial', 'googlemap', 'button', 'list');
    $shortcode_html = '';

    if(in_array($improveseo_shortcode_type, $allowed_shortcode_types)){
        switch($improveseo_shortcode_type){
            case 'testimonial':
                foreach($saved_rnos as $id){
                    $testimonial = get_option('get_testimonials_'.$id);
                    if(!empty($testimonial)){
                        $shortcode_html .= '<option value="'.$id.'">'.$testimonial['tw_testi_name'].' - '.$id.'</option>';
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