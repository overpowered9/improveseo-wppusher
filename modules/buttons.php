<?php 

	
	foreach ($id as $p) {
      $dataaa = get_option('get_buttons_'.$p);
      
      $link = isset($dataaa['tw_btn_link']) ? $dataaa['tw_btn_link'] : '';
      $text = isset($dataaa['tw_btn_text']) ? $dataaa['tw_btn_text'] : '';
      $color = isset($dataaa['tw_buttontxt_color']) ? $dataaa['tw_buttontxt_color'] : '';
      $bgcolor = isset($dataaa['tw_buttonbg_color']) ? $dataaa['tw_buttonbg_color'] : '';

      $text_clr = '';
      $bg_clr = '';

      if (!empty($color)) {
      	$text_clr = 'color :'.$color.'!important';
      }

      if (!empty($color)) {
      	$bg_clr = 'background-color :'.$bgcolor.'!important';
      }

      echo '<button style="'.$bg_clr.'" class="btn btn-info"><a style="'.$text_clr.'" href='.$link.'>'.$text.'</a></button>';
	}