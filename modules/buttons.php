<?php 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly  

foreach ($id as $p) {
      $data_btn = get_option('get_buttons_'.$p);
      
      $link = isset($data_btn['tw_btn_link']) ? $data_btn['tw_btn_link'] : '';
      $text = isset($data_btn['tw_btn_text']) ? $data_btn['tw_btn_text'] : '';
      $color = isset($data_btn['tw_buttontxt_color']) ? $data_btn['tw_buttontxt_color'] : '';
      $bgcolor = isset($data_btn['tw_buttonbg_color']) ? $data_btn['tw_buttonbg_color'] : '';

      $tw_button_outline_color = isset($data_btn['tw_button_outline_color']) ? $data_btn['tw_button_outline_color'] : '';
	$tw_button_size = isset($data_btn['tw_button_size']) ? $data_btn['tw_button_size'] : 'sm';
	$tw_button_border_type = isset($data_btn['tw_button_border_type']) ? $data_btn['tw_button_border_type'] : 'square';

      $tw_button_type = isset($data_btn['tw_button_type']) ? $data_btn['tw_button_type'] : 'normal_btn';
	$tw_tap_to_call_img_source = isset($data_btn['tw_tap_to_call_img_source']) ? $data_btn['tw_tap_to_call_img_source'] : '';
	$tw_tap_btn_text = isset($data_btn['tw_tap_btn_text']) ? $data_btn['tw_tap_btn_text'] : '';
	$tw_tap_btn_number = isset($data_btn['tw_tap_btn_number']) ? $data_btn['tw_tap_btn_number'] : '';

      $style = $classes = $btn = '';

      if (!empty($color)) {
      	$style .= 'color :'.$color.' !important;';
      }

      if (!empty($bgcolor)) {
      	$style .= 'background-color :'.$bgcolor.' !important;';
      }

      if($tw_button_outline_color!=""){
            $style .= 'border-color :'.$tw_button_outline_color.' !important;';
      }

      if($tw_button_size=="sm")
            $classes .= ' btn_sm';
      elseif($tw_button_size=="md")
            $classes .= ' btn_md';
      elseif($tw_button_size=="lg")
            $classes .= ' btn_lg';

      if($tw_button_border_type=="round")
            $classes .= ' btn_round';
      
      $btn .= '<span class="improveseo_row">';
      if($tw_button_type=="normal_btn"){
            $btn .= '<a style="'.$style.'" class="improveseo_btn '.$classes.'" href="'.improveseo_addHttp($link).'">'.$text.'</a>';
      }else{
            $btn .= '<a style="'.$style.'" class="improveseo_btn '.$classes.'" href="tel:'.$tw_tap_btn_number.'">';
            if($tw_tap_to_call_img_source!=""){
                  $btn .= '<img src="'.$tw_tap_to_call_img_source.'" />';
            }

            $btn .= $tw_tap_btn_text;
            $btn .= '</a>';
      }
      $btn .= '</span>';
    echo esc_html($btn);

}