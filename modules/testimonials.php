<?php
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly  
if (empty($id)) {
    return;
}

$html = '';
foreach ($id as $i) {
    $data = get_option('improveseo_get_testimonials_' . $i);

    $testi_img_src = isset($data['testi_img_src']) ? esc_url($data['testi_img_src']) : '';
    $tw_testi_content = isset($data['tw_testi_content']) ? esc_attr($data['tw_testi_content']) : '';
    $tw_testi_name = isset($data['tw_testi_name']) ? esc_attr($data['tw_testi_name']) : '';
    $tw_testi_position = isset($data['tw_testi_position']) ? esc_attr($data['tw_testi_position']) : '';
    $tw_box_color = isset($data['tw_box_color']) ? esc_attr($data['tw_box_color']) : '#fff';
    $tw_font_color = isset($data['tw_font_color']) ? esc_attr($data['tw_font_color']) : '#000';
    $tw_testi_outline_color = isset($data['tw_testi_outline_color']) ? esc_attr($data['tw_testi_outline_color']) : '#ffffff';

    $style = 'border-color:' . $tw_testi_outline_color . '; background-color:' . $tw_box_color . '; color: ' . $tw_font_color . ';';

    $html .= '<div class="improveseo_row" style="width:100%;">
                <div class="improveseo_testimonial_wrapper" id="improveseo_testimonial_wrapper_' . $i . '" style="' . $style . '">';

    $margin = empty($testi_img_src) ? 'margin-left:10px !important' : '';

    if ($testi_img_src != '') {
        $html .= '<div style="background-image:url(' . $testi_img_src . ');" class="improveseo_testimonial_portrait"></div>';
    }

    $html .= '<div class="improveseo_testimonial_description" style="' . $margin . '">
                <div class="improveseo_testimonial_description_inner">
                    <div class="improveseo_testimonial_content">
                        <p><span style="">' . $tw_testi_content . '</span></p>
                    </div>
                </div>';

    if ($tw_testi_name != "") {
        $html .= '<span class="improveseo_testimonial_author">' . $tw_testi_name . '</span>
                  <p class="improveseo_testimonial_meta">
                    <span class="improveseo_testimonial_company">' . stripslashes($tw_testi_position) . '</span>
                  </p>';
    }

    $html .= '</div>
        </div>
    </div>';
}

echo wp_kses($html,array('a','div','span','p'));

