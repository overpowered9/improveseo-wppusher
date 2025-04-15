<div class="shortcodes-box">
    <div class="project-lists">
        <h3> Testimonials </h3>
        <table>
            <thead>
                <tr>
                    <th> Name/#ID </th>
                    <th>Testimonial IMG</th>
                    <th> Content </th>
                    <th> Name </th>
                    <th> Actions </th>
                </tr>
            </thead>
            <tbody>
                <?php
                $ids = get_option('get_saved_random_numbers');

                $html = '';
                if (!empty($ids)) {
                    foreach ($ids as $id) {
                        $get_data = get_option('get_testimonials_' . $id);
                        if (empty($get_data)) {
                            continue;
                        }

                        $testi_img_src = isset($get_data['testi_img_src']) ? $get_data['testi_img_src'] : '';
                        $tw_testi_content = isset($get_data['tw_testi_content']) ? $get_data['tw_testi_content'] : '';
                        $tw_testi_name = isset($get_data['tw_testi_name']) ? $get_data['tw_testi_name'] : '';


                        $shortcode_name = isset($get_data['tw_testi_shortcode_name']) ? $get_data['tw_testi_shortcode_name'] : '';
                        $display_name = ($shortcode_name != "") ? $shortcode_name : $id;

                        $content = stripslashes($tw_testi_content);
                        $content = (strlen($content) > 100) ? substr($content, 0, 100) . '...' : $content;


                        $html .= '<tr>';
                        $html .= '<td data-label="Name/#ID" style="width: 23%;"><strong> <b>' . $display_name . '</b></strong> </td>';

                        $html .= '<td data-label="Testimonial IMG" class="table-seo-mg">';
                        if ($testi_img_src != "") {
                            $html .= '<img style="width:45px;height:45px;" src=' . $testi_img_src . ' />';
                        } else {
                            $html .= 'No Image';
                        }
                        $html .= '</td>';
                        $html .= '<td data-label="Content"><div class="linmit-text">' . $content . '</div></td>
                        <td data-label="Name">' . $tw_testi_name . '</td>';
                        $html .= '<td class="actions-btn" data-label="Actions">
                        <div style="
    display: flex;
    justify-content: space-evenly;
" >
                        <span data-action="testimonial" data-rand_id=' . $id . ' class="wt-edit-testi "> 
                        <img src="' . WT_URL . '/assets/images/latest-images/write.svg" alt="write"> 
                        </span>
                        <span data-rand_id=' . $id . ' data-action="testimonial" class="wt-dlt-testi"> 
                        <img src="' . WT_URL . '/assets/images/latest-images/delete.svg" alt="delete"> 
                       </span>
                       </div>
                       </td>';

                    }
                }
                echo $html;
                ?>

            </tbody>
        </table>
    </div>
</div>
<div class="shortcodes-box">
    <div class="project-lists">
        <h3> Google Maps </h3>
        <table>
            <thead>
                <tr>
                    <th> Name/#ID </th>
                    <th>Google Maps APIKEY</th>
                    <th> Actions </th>
                </tr>
            </thead>
            <tbody>
                <?php
                $ids = get_option('get_saved_random_numbers');
                $html = '';
                if (!empty($ids)) {
                    foreach ($ids as $id) {
                        $get_data = get_option('get_googlemaps_' . $id);
                        if (empty($get_data)) {
                            continue;
                        }
                        $shortcode_name = isset($get_data['tw_maps_shortcode_name']) ? $get_data['tw_maps_shortcode_name'] : '';
                        $display_name = ($shortcode_name != "") ? $shortcode_name : $id;
                        $html .= '<tr>';
                        $html .= '<td><strong> <b>' . $display_name . '</b></strong> </td>';
                        $tw_maps_apikey = isset($get_data['tw_maps_apikey']) ? $get_data['tw_maps_apikey'] : '';
                        $html .= '<td data-colname="Google Maps APIKEY">' . $tw_maps_apikey . '</td>';
                        $html .= '<td class="actions-btn" data-label="Actions">
                        <div style="
    display: flex;
    justify-content: space-evenly;
" >
                        <span data-action="googlemaps" data-rand_id=' . $id . ' class="wt-edit-testi "> 
                        <img src="' . WT_URL . '/assets/images/latest-images/write.svg" alt="write"> 
                        </span>
                        <span data-rand_id=' . $id . ' data-action="googlemaps" class="wt-dlt-testi"> 
                        <img src="' . WT_URL . '/assets/images/latest-images/delete.svg" alt="delete"> 
                       </span>
                       </div>
                       </td>';
                    }
                }
                echo $html;
                ?>
            </tbody>
        </table>
    </div>
</div>
<div class="shortcodes-box">
    <div class="project-lists">
        <h3> Buttons </h3>
        <table>
            <thead>
                <tr>
                    <th> Name/#ID </th>
                    <th> Button Type </th>
                    <th> Button Text </th>
                    <th> Buttons Link/Number </th>
                    <th> Actions </th>
                </tr>
            </thead>
            <tbody>
                <?php
                $ids = get_option('get_saved_random_numbers');

                $html = '';
                if (!empty($ids)) {
                    foreach ($ids as $id) {
                        $get_data = get_option('get_buttons_' . $id);
                        if (empty($get_data)) {
                            continue;
                        }
                        $shortcode_name = isset($get_data['tw_button_shortcode_name']) ? $get_data['tw_button_shortcode_name'] : '';
                        $display_name = ($shortcode_name != "") ? $shortcode_name : $id;

                        $html .= '<tr>';
                        $html .= '<td data-label="Name/#ID"style="width: 30%;"><strong> <b>' . $display_name . '</b></strong> </td>';
                        $tw_btn_text = isset($get_data['tw_btn_text']) ? $get_data['tw_btn_text'] : '';
                        $tw_btn_link = isset($get_data['tw_btn_link']) ? $get_data['tw_btn_link'] : '';

                        $tw_buttontxt_color = isset($get_data['tw_buttontxt_color']) ? $get_data['tw_buttontxt_color'] : '';
                        $tw_buttonbg_color = isset($get_data['tw_buttonbg_color']) ? $get_data['tw_buttonbg_color'] : '';
                        $tw_button_type = isset($get_data['tw_button_type']) ? $get_data['tw_button_type'] : 'normal_btn';
                        $tw_tap_btn_text = isset($get_data['tw_tap_btn_text']) ? $get_data['tw_tap_btn_text'] : '';
                        $tw_tap_btn_number = isset($get_data['tw_tap_btn_number']) ? $get_data['tw_tap_btn_number'] : '';

                        if ($tw_button_type == "normal_btn") {
                            $html .= '<td data-label="Button Type">Normal Button</td>';
                            $html .= '<td data-label="Button Text">' . $tw_btn_text . '</td>';
                            $html .= '<td data-label="Buttons Link/Number">' . $tw_btn_link . '</td>';
                        } else {

                            $html .= '<td data-label="Button Type">Tap To Call</td>';
                            $html .= '<td data-label="Button Text">' . $tw_tap_btn_text . '</td>';
                            $html .= '<td data-label="Buttons Link/Number">' . $tw_tap_btn_number . '</td>';
                        }
                        $html .= '<td class="actions-btn" data-label="Actions">
                        <div style="
    display: flex;
    justify-content: space-evenly;
" >
                        <span data-action="buttons" data-rand_id=' . $id . ' class="wt-edit-testi "> 
                        <img src="' . WT_URL . '/assets/images/latest-images/write.svg" alt="write"> 
                        </span>
                        <span data-rand_id=' . $id . ' data-action="buttons" class="wt-dlt-testi"> 
                        <img src="' . WT_URL . '/assets/images/latest-images/delete.svg" alt="delete"> 
                       </span>
                       </div>
                       </td>';
                    }
                }
                echo $html;
                ?>
            </tbody>
        </table>
    </div>
</div>
<div class="shortcodes-box">
    <div class="project-lists">
        <h3> Videos </h3>
        <table>
            <thead>
                <tr>
                    <th> Name/#ID </th>
                    <th> Video Type </th>
                    <th> Video Poster </th>
                    <th style="text-align: center;"> Auto play </th>
                    <th style="text-align: center;"> Mute </th>
                    <th style="text-align: center;"> Controls </th>
                    <th style="text-align: center;"> Loop </th>
                    <th> Actions </th>
                </tr>
            </thead>
            <tbody>
                <?php
                $ids = get_option('get_saved_random_numbers');
                $html = '';
                if (!empty($ids)) {
                    foreach ($ids as $id) {
                        $get_data = get_option('get_videos_' . $id);
                        if (empty($get_data)) {
                            continue;
                        }
                        $shortcode_name = isset($get_data['video_shortcode_name']) ? $get_data['video_shortcode_name'] : '';
                        $display_name = ($shortcode_name != "") ? $shortcode_name : $id;

                        $video_type = isset($get_data['video_type']) ? $get_data['video_type'] : 'upload_video';

                        $video_poster_img_source = isset($get_data['video_poster_img_source']) ? $get_data['video_poster_img_source'] : '';
                        $video_url_mp4 = isset($get_data['video_url_mp4']) ? $get_data['video_url_mp4'] : '';
                        $video_url_ogv = isset($get_data['video_url_ogv']) ? $get_data['video_url_ogv'] : '';
                        $video_url_webm = isset($get_data['video_url_webm']) ? $get_data['video_url_webm'] : '';
                        $video_url_vimeo = isset($get_data['video_url_vimeo']) ? $get_data['video_url_vimeo'] : '';
                        $video_url_youtube = isset($get_data['video_url_youtube']) ? $get_data['video_url_youtube'] : '';

                        $video_autoplay = isset($get_data['video_autoplay']) ? $get_data['video_autoplay'] : 'no';
                        $video_muted = isset($get_data['video_muted']) ? $get_data['video_muted'] : 'no';
                        $video_controls = isset($get_data['video_controls']) ? $get_data['video_controls'] : 'no';
                        $video_loop = isset($get_data['video_loop']) ? $get_data['video_loop'] : 'no';

                        if ($video_type == "upload_video")
                            $video_type_display = "Upload Video";
                        elseif ($video_type == "youtube")
                            $video_type_display = "YouTube";
                        elseif ($video_type == "vimeo")
                            $video_type_display = "Vimeo";

                        $html .= '<tr>';
                        $html .= '<td data-label="Name/#ID"><strong> <b>' . $display_name . '</b></strong> </td>';
                        $html .= '<td data-label="Video Type">' . $video_type_display . '</td>';
                        if ($video_poster_img_source != "")
                            $html .= '<td data-label="Video Poster"><img style="width:50px;height:50px;object-fit: cover; border-radius: 5px;" src="' . $video_poster_img_source . '"/></td>';
                        else
                            $html .= '<td data-label="Video Poster">No Poster Image</td>';
                        $html .= '<td data-label="Auto play"  style="width:10%;"class="status paused" ><div style="width:100%; display:flex;justify-content:center;"><div class="' . (strtolower($video_autoplay) === 'yes' ? 'yes-btn-td' : '') . '"> ' . ucfirst($video_autoplay) . ' </div></div></td>';
                        $html .= '<td data-label="Mute" style="width:10%;" class="status paused"><div style="width:100%; display:flex;justify-content:center;"><div class="' . (strtolower($video_muted) === 'yes' ? 'yes-btn-td' : '') . '">' . ucfirst($video_muted) . '</div></div></td>';
                        $html .= '<td data-label="Controls"  style="width:10%;" class="status paused"><div style="width:100%; display:flex;justify-content:center;"><div class="' . (strtolower($video_controls) === 'yes' ? 'yes-btn-td' : '') . '"> ' . ucfirst($video_controls) . '</div></div> </td>';
                        $html .= '<td data-label="Loop" style="width:10%;" class="status paused"><div style="width:100%; display:flex;justify-content:center;"><div class="' . (strtolower($video_loop) === 'yes' ? 'yes-btn-td' : '') . '">  ' . ucfirst($video_loop) . '</div></div></td>';

                        $html .= '<td class="actions-btn" data-label="Actions">
                            <div  style="
    display: flex;
    justify-content: space-evenly;
" >
                            <span data-action="videos" data-rand_id=' . $id . ' class="wt-edit-testi "> 
                            <img src="' . WT_URL . '/assets/images/latest-images/write.svg" alt="write"> 
                            </span>
                            <span data-rand_id=' . $id . ' data-action="videos" class="wt-dlt-testi"> 
                            <img src="' . WT_URL . '/assets/images/latest-images/delete.svg" alt="delete"> 
                           </span>
                           </div>
                           </td>';

                    }
                    echo $html;
                }
                ?>
            </tbody>
        </table>
    </div>
</div>