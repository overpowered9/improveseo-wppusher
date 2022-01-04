<?php
/* This file will handle all ajax requests from plugin */
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
                        $shortcode_html .= '<option value="'.$id.'">'.$testimonial['tw_testi_name'].' - '.$id.'</option>';
                    }
                }
            break;
            case 'button':
                foreach($saved_rnos as $id){
                    $button = get_option('get_buttons_'.$id);
                    if(!empty($button)){
                        $shortcode_html .= '<option value="'.$id.'">'.$button['tw_btn_text'].' - '.$id.'</option>';
                    }
                }
            break;
            case 'googlemap':
                foreach($saved_rnos as $id){
                    $googlemap = get_option('get_googlemaps_'.$id);
                    if(!empty($googlemap)){
                        $shortcode_html .= '<option value="'.$id.'">GoogleMap - '.$id.'</option>';
                    }
                }
            break;
            case 'video':
                foreach($saved_rnos as $id){
                    $videos = get_option('get_videos_'.$id);
                    if(!empty($videos)){
                        $shortcode_html .= '<option value="'.$id.'">Video - '.$id.'</option>';
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