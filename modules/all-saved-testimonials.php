<section>
    <div class="project-heading pt-4">
        <h1>Testimonials</h1>
    </div>
    <section class="project-table-wrapper">
        <div class="form table-responsive-sm">
            <table class="table widefat fixed wp-list-table widefat fixed table-view-list posts text-center">
                <thead>
                    <tr>
                        <th scope="col" class="text-center manage-column manage-column column-title column-primary" style="width:10%">#ID</th>
                        <th scope="col" class="text-center manage-column">Testimonial IMG</th>
                        <th scope="col" class="text-center manage-column">Content</th>
                        <th scope="col" class="text-center manage-column">Name</th>
                        <th scope="col" class="text-center manage-column">Position</th>
                        <th scope="col" class="text-center manage-column">Box Color</th>
                        <th scope="col" class="text-center manage-column">Font Color</th>
                        <th scope="col" class="text-center manage-column" style="width:20%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $ids = get_option('get_saved_random_numbers');
                    if (empty($ids)) {
                    return;
                    }
                    $html = '';
                    foreach ($ids as $id) {
                    $get_data = get_option('get_testimonials_'.$id);
                    if (empty($get_data)) {
                    continue;
                    }
                    $html .= '<tr>';
                        $html .= '<td class="column-title column-primary has-row-actions">'.$id.'</td>';
                        $testi_img_src = isset($get_data['testi_img_src']) ? $get_data['testi_img_src'] : '';
                        $tw_testi_content = isset($get_data['tw_testi_content']) ? $get_data['tw_testi_content'] : '';
                        $tw_testi_name = isset($get_data['tw_testi_name']) ? $get_data['tw_testi_name'] : '';
                        $tw_testi_position = isset($get_data['tw_testi_position']) ? $get_data['tw_testi_position'] : '';
                        
                        $tw_box_color = isset($get_data['tw_box_color']) ? $get_data['tw_box_color'] : '';
                        $tw_font_color = isset($get_data['tw_font_color']) ? $get_data['tw_font_color'] : '';
                        
                        $html .= '<td data-colname="Testimonial IMG">';
                        if($testi_img_src!=""){
                            $html .= '<img style="width:45px;height:45px;" src='.$testi_img_src.' />';
                        }else{
                            $html .= 'No Image';
                        }
                        $html .= '</td>';
                        $html .= '<td data-colname="Content">'.$tw_testi_content.'</td>
                        <td data-colname="Name">'.$tw_testi_name.'</td>
                        <td data-colname="Position">'.$tw_testi_position.'</td>
                        <td data-colname="Box Color">'.$tw_box_color.'</td>
                        <td data-colname="Font Color">'.$tw_font_color.'</td>
                        <td class="actions-btn" data-colname="Actions"><span data-action="testimonial" data-rand_id='.$id.' class="wt-edit-testi btn btn-outline-primary mr-2">Edit</span><span data-action="testimonial" data-rand_id='.$id.' class="wt-dlt-testi wt-icons btn btn-outline-danger">Remove</span></td>
                    </tr>';
                    }
                    echo $html;
                    ?>
                </tbody>
            </table>
        </div>
    </section>
    
    <div class="project-heading pt-4">
        <h1>Google Maps</h1>
    </div>
    <section class="project-table-wrapper">
        <div class="form table-responsive-sm">
            <table class="table widefat fixed wp-list-table widefat fixed table-view-list posts text-center">
                <thead>
                    <tr>
                        <th scope="col" class="text-center manage-column manage-column column-title column-primary" style="width:10%">#ID</th>
                        <th scope="col" class="text-center manage-column">Google Maps APIKEY</th>
                        <th scope="col" class="text-center manage-column" style="width:20%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $ids = get_option('get_saved_random_numbers');
                    if (empty($ids)) {
                    return;
                    }
                    $html = '';
                    foreach ($ids as $id) {
                    $get_data = get_option('get_googlemaps_'.$id);
                    if (empty($get_data)) {
                    continue;
                    }
                    $html .= '<tr>';
                        $html .= '<td class="column-title column-primary has-row-actions">'.$id.'</td>';
                        $tw_maps_apikey = isset($get_data['tw_maps_apikey']) ? $get_data['tw_maps_apikey'] : '';
                        $html .= '<td data-colname="Google Maps APIKEY">'.$tw_maps_apikey.'</td>
                        <td class="actions-btn" data-colname="Actions"><span data-action="googlemaps" data-rand_id='.$id.' class="wt-edit-testi btn btn-outline-primary px-4 mr-2 py-2">Edit</span><span data-action="googlemaps" data-rand_id='.$id.' class="wt-dlt-testi wt-icons btn btn-outline-danger py-2">Remove</span></td>
                    </tr>';
                    }
                    echo $html;
                    ?>
                </tbody>
            </table>
        </div>
    </section>
    <div class="project-heading pt-4">
        <h1>Buttons</h1>
    </div>
    <section class="project-table-wrapper">
        <div class="form table-responsive-sm">
            <table class="table widefat fixed wp-list-table widefat fixed table-view-list posts text-center">
                <thead>
                    <tr>
                        <th scope="col" class="text-center manage-column manage-column column-title column-primary" style="width:10%">#ID</th>
                        <th scope="col" class="text-center manage-column">Button Type</th>
                        <th scope="col" class="text-center manage-column">Button Text</th>
                        <th scope="col" class="text-center manage-column">Button Link/Number</th>
                        <th scope="col" class="text-center manage-column">Button Color</th>
                        <th scope="col" class="text-center manage-column">Button BG Color</th>
                        <th scope="col" class="text-center manage-column" style="width:20%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $ids = get_option('get_saved_random_numbers');
                    if (empty($ids)) {
                    return;
                    }
                    $html = '';
                    foreach ($ids as $id) {
                    $get_data = get_option('get_buttons_'.$id);
                    if (empty($get_data)) {
                    continue;
                    }
                    $html .= '<tr>';
                        $html .= '<td class="column-title column-primary has-row-actions">'.$id.'<button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button></td>';
                        $tw_btn_text = isset($get_data['tw_btn_text']) ? $get_data['tw_btn_text'] : '';
                        $tw_btn_link = isset($get_data['tw_btn_link']) ? $get_data['tw_btn_link'] : '';
                        
                        $tw_buttontxt_color = isset($get_data['tw_buttontxt_color']) ? $get_data['tw_buttontxt_color'] : '';
                        $tw_buttonbg_color = isset($get_data['tw_buttonbg_color']) ? $get_data['tw_buttonbg_color'] : '';
                    	$tw_button_type = isset($get_data['tw_button_type']) ? $get_data['tw_button_type'] : 'normal_btn';
                        $tw_tap_btn_text = isset($get_data['tw_tap_btn_text']) ? $get_data['tw_tap_btn_text'] : '';
                    	$tw_tap_btn_number = isset($get_data['tw_tap_btn_number']) ? $get_data['tw_tap_btn_number'] : '';

                        if($tw_button_type=="normal_btn"){
                            $html .= '<td data-colname="Button Type">Normal Button</td>';
                            $html .= '<td data-colname="Button Text">'.$tw_btn_text.'</td>';
                            $html .= '<td data-colname="Button Link">'.$tw_btn_link.'</td>';
                        }else{
                            $html .= '<td data-colname="Button Type">Tap To Call</td>';
                            $html .= '<td data-colname="Button Text">'.$tw_tap_btn_text.'</td>';
                            $html .= '<td data-colname="Button Link">'.$tw_tap_btn_number.'</td>';
                        }
                        $html .= '
                        <td data-colname="Button Color">'.$tw_buttontxt_color.'</td>
                        <td data-colname="Button BG Color">'.$tw_buttonbg_color.'</td>
                        <td class="actions-btn" data-colname="Actions"><span  data-action="buttons" data-rand_id='.$id.' class="wt-edit-testi wt-icons btn btn-outline-primary px-4 mr-2 py-2">Edit</span><span data-rand_id='.$id.' data-action="buttons" class="wt-dlt-testi wt-icons btn btn-outline-danger py-2">Remove</span></td>
                    </tr>';
                    }
                    echo $html;
                    ?>
                </tbody>
            </table>
        </div>
    </section>

    <div class="project-heading pt-4">
        <h1>Videos</h1>
    </div>
    <section class="project-table-wrapper">
        <div class="form table-responsive-sm">
            <table class="table widefat fixed wp-list-table widefat fixed table-view-list posts text-center">
                <thead>
                    <tr>
                        <th scope="col" class="text-center manage-column manage-column column-title column-primary" style="width:10%">#ID</th>
                        <th scope="col" class="text-center manage-column" style="width:17%;">Video Poster</th>
                        <th scope="col" class="text-center manage-column" style="width:17%;">Video URLs</th>
                        <th scope="col" class="text-center manage-column" style="width:18%;">Autoplay - Mute - Controls - Loop</th>
                        <th scope="col" class="text-center manage-column">Height</th>
                        <th scope="col" class="text-center manage-column">Width</th>
                        <th scope="col" class="text-center manage-column" style="width:20%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $ids = get_option('get_saved_random_numbers');
                    if (empty($ids)) {
                    return;
                    }
                    $html = '';
                    foreach ($ids as $id) {
                        $get_data = get_option('get_videos_'.$id);
                        if (empty($get_data)) {
                            continue;
                        }
                        $video_poster_img_source = isset($get_data['video_poster_img_source'])?$get_data['video_poster_img_source']:'';
                        $video_url_mp4 = isset($get_data['video_url_mp4'])?$get_data['video_url_mp4']:'';
                        $video_url_ogv = isset($get_data['video_url_ogv'])?$get_data['video_url_ogv']:'';
                        $video_url_webm = isset($get_data['video_url_webm'])?$get_data['video_url_webm']:'';
                        $video_autoplay = isset($get_data['video_autoplay'])?$get_data['video_autoplay']:'no';
                        $video_muted = isset($get_data['video_muted'])?$get_data['video_muted']:'no';
                        $video_controls = isset($get_data['video_controls'])?$get_data['video_controls']:'no';
                        $video_loop = isset($get_data['video_loop'])?$get_data['video_loop']:'no';
                        $video_height = isset($get_data['video_height'])?$get_data['video_height']:'Default';
                        $video_width = isset($get_data['video_width'])?$get_data['video_width']:'Default';

                        if($video_height=="")
                            $video_height = 'Default';

                        if($video_width=="")
                            $video_width = 'Default';

                        $html .= '<tr>';
                        $html .= '<td class="column-title column-primary has-row-actions">'.$id.'</td>';
                        
                        if($video_poster_img_source!="")
                            $html .= '<td data-colname="video_poster_img"><img src="'.$video_poster_img_source.'" style="max-width:200px; height:100px;" /></td>';
                        else
                            $html .= '<td data-colname="video_poster_img">No Poster Image</td>';
                        
                        $html .= '<td data-colname="video_urls">';
                            if($video_url_mp4!="")
                                $html .= '<a href="'.$video_url_mp4.'" target="_blank">'.$video_url_mp4."</a><hr />";
                            
                            if($video_url_ogv!="")
                                $html .= '<a href="'.$video_url_ogv.'" target="_blank">'.$video_url_ogv."</a><hr />";

                            if($video_url_webm!="")
                                $html .= '<a href="'.$video_url_webm.'" target="_blank">'.$video_url_webm."</a><hr />";

                        $html .= '</td>';

                        $html .= '<td data-colname="video_extras">'.ucfirst($video_autoplay).' - '.ucfirst($video_muted).' - '.ucfirst($video_controls).' - '.ucfirst($video_loop).'</td>';
                        
                        $html .= '<td data-colname="video_height">'.$video_height.'</td>';
                        $html .= '<td data-colname="video_width">'.$video_width.'</td>';

                        $html .= '
                            <td class="actions-btn" data-colname="Actions"><span data-action="videos" data-rand_id='.$id.' class="wt-edit-testi btn btn-outline-primary px-4 mr-2 py-2">Edit</span><span data-action="videos" data-rand_id='.$id.' class="wt-dlt-testi wt-icons btn btn-outline-danger py-2">Remove</span></td>
                        </tr>';
                    }
                    echo $html;
                    ?>
                </tbody>
            </table>
        </div>
    </section>
</section>