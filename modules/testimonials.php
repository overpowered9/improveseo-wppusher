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
        ?>        
        <style>#improveseo_testimonial_wrapper_<?php echo $i; ?>::before{ border-color: <?php echo $tw_testi_outline_color; ?> }</style>
        <div class="improveseo_row" style="width:100%;">
            <div class="improveseo_testimonial_wrapper" style="border-color:<?php echo $tw_testi_outline_color; ?>; background-color:<?php echo $tw_box_color; ?>; color: <?php echo $tw_font_color; ?>" id="improveseo_testimonial_wrapper_<?php echo $i; ?>">
                <div style="<?php echo ($testi_img_src!='')?'background-image:url('.$testi_img_src.')':''; ?>" class="improveseo_testimonial_portrait"></div>
                <div class="improveseo_testimonial_description">
                    <div class="improveseo_testimonial_description_inner">
                        <div class="improveseo_testimonial_content">
                            <p><span style=""><?php echo $tw_testi_content; ?></span></p>
                        </div>
                    </div>
                    <?php if($tw_testi_name!=""): ?>
                    <span class="improveseo_testimonial_author">Testimonia Author</span>
                    <p class="improveseo_testimonial_meta">
                        <span class="improveseo_testimonial_position"><?php echo $tw_testi_name; ?></span>
                        <span class="improveseo_testimonial_separator">,</span> 
                        <span class="improveseo_testimonial_company"><?php echo $tw_testi_position; ?></span>
                    </p>
                    <?php
                    endif; ?>
                </div>
            </div>
        </div>
<?php
    } ?>