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
                        $html .= '<td data-colname="Testimonial IMG"><img style="width:45px;height:45px;" src='.$testi_img_src.'></td>
                        <td data-colname="Content">'.$tw_testi_content.'</td>
                        <td data-colname="Name">'.$tw_testi_name.'</td>
                        <td data-colname="Position">'.$tw_testi_position.'</td>
                        <td data-colname="Box Color">'.$tw_box_color.'</td>
                        <td data-colname="Font Color">'.$tw_font_color.'</td>
                        <td class="actions-btn" data-colname="Actions"><span data-action="testimonial" data-rand_id='.$id.' class="wt-edit-testi btn btn-outline-primary mr-2">Eidt</span><span data-action="testimonial" data-rand_id='.$id.' class="wt-dlt-testi wt-icons btn btn-outline-danger">Remove</span></td>
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
                        <td class="actions-btn" data-colname="Actions"><span data-action="googlemaps" data-rand_id='.$id.' class="wt-edit-testi btn btn-outline-primary px-4 mr-2 py-2">Eidt</span><span data-action="googlemaps" data-rand_id='.$id.' class="wt-dlt-testi wt-icons btn btn-outline-danger py-2">Remove</span></td>
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
                        <th scope="col" class="text-center manage-column">Button Text</th>
                        <th scope="col" class="text-center manage-column">Button Link</th>
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
                        $html .= '<td data-colname="Button Text">'.$tw_btn_text.'</td>
                        <td data-colname="Button Link">'.$tw_btn_link.'</td>
                        <td data-colname="Button Color">'.$tw_buttontxt_color.'</td>
                        <td data-colname="Button BG Color">'.$tw_buttonbg_color.'</td>
                        <td class="actions-btn" data-colname="Actions"><span  data-action="buttons" data-rand_id='.$id.' class="wt-edit-testi wt-icons btn btn-outline-primary px-4 mr-2 py-2">Eidt</span><span data-rand_id='.$id.' data-action="buttons" class="wt-dlt-testi wt-icons btn btn-outline-danger py-2">Remove</span></td>
                    </tr>';
                    }
                    echo $html;
                    ?>
                </tbody>
            </table>
        </div>
    </section>
</section>