<?php 
    if (empty($id)) {
        return;
    }
    $html = '';
    foreach ($id as $i) {
        $data = get_option('get_testimonials_'.$i);
        
        $testi_img_src = isset($data['testi_img_src']) ? $data['testi_img_src'] : '';
        $tw_testi_content = isset($data['tw_testi_content']) ? $data['tw_testi_content'] : '';
        $tw_testi_name = isset($data['tw_testi_name']) ? $data['tw_testi_name'] : '';
	    $tw_testi_position = isset($data['tw_testi_position']) ? $data['tw_testi_position'] : '';
        $tw_box_color = isset($data['tw_box_color']) ? $data['tw_box_color'] : '#fff';
        $tw_font_color = isset($data['tw_font_color']) ? $data['tw_font_color'] : '#000';
        $tw_testi_outline_color = isset($data['tw_testi_outline_color']) ? $data['tw_testi_outline_color'] : '#ffffff';
        $style = 'border-color:'.$tw_testi_outline_color.'; background-color:'.$tw_box_color.'; color: '.$tw_font_color.';';
        $html .= '<style>
		#improveseo_testimonial_wrapper_'.$i.'::before{ border-color: '. $tw_testi_outline_color.' }
		#improveseo_testimonial_wrapper_'.$i.'{'.$style.'}
		</style>';
        $html .= '<div class="improveseo_row" style="width:100%;">
		<div class="improveseo_testimonial_wrapper" id="improveseo_testimonial_wrapper_'.$i.'">';
        $margin = '';
        if($testi_img_src != ''){
            $html .= '<div style="background-image:url('.$testi_img_src.');" class="improveseo_testimonial_portrait"></div>';
        }else{
            //$html .= '<div style="" class="improveseo_testimonial_portrait"></div>';
            $margin = 'margin-left:10px !important';
        }
        
        
        $html .= '<div class="improveseo_testimonial_description" style="'.$margin.'">
                    <div class="improveseo_testimonial_description_inner">
                        <div class="improveseo_testimonial_content">
                            <p><span style="">'.$tw_testi_content.'</span></p>
                        </div>
                    </div>';
        if($tw_testi_name!=""): 
            $html .= '<span class="improveseo_testimonial_author">'.$tw_testi_name.'</span>
                    <p class="improveseo_testimonial_meta">
                        <span class="improveseo_testimonial_company">'.stripslashes($tw_testi_position).'</span>
                    </p>';
        endif;
                $html .= '</div>
            </div>
        </div>';
    echo $html;
} ?>