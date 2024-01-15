<section>
    <div class="project-heading pt-4">
        <h1>Testimonials</h1>
    </div>
    <section class="project-table-wrapper">
    <div class="form table-responsive-sm">
        <table class="table widefat fixed wp-list-table widefat fixed table-view-list posts text-center">
            <thead>
                <tr>
                    <th scope="col" class="text-center manage-column manage-column column-title column-primary" style="width:12%">Name/#ID</th>
                    <th scope="col" class="text-center manage-column">Testimonial IMG</th>
                    <th scope="col" class="text-center manage-column" style="width:25%;">Content</th>
                    <th scope="col" class="text-center manage-column">Name</th>
                    <th scope="col" class="text-center manage-column" style="width:20%">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $ids = get_option('improveseo_get_saved_random_numbers');

                if (!empty($ids)) {
                    foreach ($ids as $id) {
                        $get_data = get_option('get_testimonials_' . $id);

                        // Skip if data is empty
                        if (empty($get_data)) {
                            continue;
                        }

                        $testi_img_src = isset($get_data['testi_img_src']) ? esc_url($get_data['testi_img_src']) : '';
                        $tw_testi_content = isset($get_data['tw_testi_content']) ? wp_kses_post($get_data['tw_testi_content']) : '';
                        $tw_testi_name = isset($get_data['tw_testi_name']) ? esc_html($get_data['tw_testi_name']) : '';

                        $shortcode_name = isset($get_data['tw_testi_shortcode_name']) ? esc_html($get_data['tw_testi_shortcode_name']) : '';
                        $display_name = ($shortcode_name != "") ? $shortcode_name : $id;

                        // Trim content to a maximum of 100 characters
                        $content = strlen($tw_testi_content) > 100 ? substr($tw_testi_content, 0, 100) . '...' : $tw_testi_content;

                        ?>
                        <tr>
                            <td class="column-title column-primary has-row-actions"><?php echo esc_html($display_name); ?></td>

                            <td data-colname="Testimonial IMG">
                                <?php
                                if ($testi_img_src != "") {
                                    echo '<img style="width:45px;height:45px;" src="' . $testi_img_src . '" alt="Testimonial Image" />';
                                } else {
                                    echo 'No Image';
                                }
                                ?>
                            </td>

                            <td data-colname="Content"><?php echo $content; ?></td>
                            <td data-colname="Name"><?php echo esc_html($tw_testi_name); ?></td>
                            <td class="actions-btn" data-colname="Actions">
                                <span data-action="testimonial" data-rand_id="<?php echo esc_attr($id); ?>" class="wt-edit-testi btn btn-outline-primary mr-2">Edit</span>
                                <span data-action="testimonial" data-rand_id="<?php echo esc_attr($id); ?>" class="wt-dlt-testi wt-icons btn btn-outline-danger">Remove</span>
                            </td>
                        </tr>
                    <?php
                    }
                } else {
                    // Output something when no testimonials are found
                    echo '<tr><td colspan="5">No testimonials found.</td></tr>';
                }
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
                    <th scope="col" class="text-center manage-column manage-column column-title column-primary" style="width:10%">Name/#ID</th>
                    <th scope="col" class="text-center manage-column">Google Maps APIKEY</th>
                    <th scope="col" class="text-center manage-column" style="width:20%">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $ids = get_option('improveseo_get_saved_random_numbers');
                
                if (!empty($ids)) {
                    foreach ($ids as $id) {
                        $get_data = get_option('get_googlemaps_' . $id);

                        // Skip if data is empty
                        if (empty($get_data)) {
                            continue;
                        }

                        $shortcode_name = isset($get_data['tw_maps_shortcode_name']) ? esc_html($get_data['tw_maps_shortcode_name']) : '';
                        $display_name = ($shortcode_name != "") ? $shortcode_name : $id;

                        ?>
                        <tr>
                            <td class="column-title column-primary has-row-actions"><?php echo esc_html($display_name); ?></td>

                            <?php
                            $tw_maps_apikey = isset($get_data['tw_maps_apikey']) ? esc_html($get_data['tw_maps_apikey']) : '';
                            echo '<td data-colname="Google Maps APIKEY">' . $tw_maps_apikey . '</td>';
                            ?>

                            <td class="actions-btn" data-colname="Actions">
                                <span data-action="googlemaps" data-rand_id="<?php echo esc_attr($id); ?>" class="wt-edit-testi btn btn-outline-primary px-4 mr-2 py-2">Edit</span>
                                <span data-action="googlemaps" data-rand_id="<?php echo esc_attr($id); ?>" class="wt-dlt-testi wt-icons btn btn-outline-danger py-2">Remove</span>
                            </td>
                        </tr>
                    <?php
                    }
                } else {
                    // Output something when no Google Maps configurations are found
                    echo '<tr><td colspan="3">No Google Maps configurations found.</td></tr>';
                }
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
                    <th scope="col" class="text-center manage-column manage-column column-title column-primary" style="width:10%">Name/#ID</th>
                    <th scope="col" class="text-center manage-column">Button Type</th>
                    <th scope="col" class="text-center manage-column">Button Text</th>
                    <th scope="col" class="text-center manage-column">Button Link/Number</th>
                    <th scope="col" class="text-center manage-column" style="width:20%">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $ids = get_option('improveseo_get_saved_random_numbers');
                
                if (!empty($ids)) {
                    foreach ($ids as $id) {
                        $get_data = get_option('get_buttons_' . $id);

                        // Skip if data is empty
                        if (empty($get_data)) {
                            continue;
                        }

                        $shortcode_name = isset($get_data['tw_button_shortcode_name']) ? esc_html($get_data['tw_button_shortcode_name']) : '';
                        $display_name = ($shortcode_name != "") ? $shortcode_name : $id;

                        ?>
                        <tr>
                            <td class="column-title column-primary has-row-actions">
                                <?php echo esc_html($display_name); ?>
                                <button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button>
                            </td>

                            <?php
                            $tw_btn_text = isset($get_data['tw_btn_text']) ? esc_html($get_data['tw_btn_text']) : '';
                            $tw_btn_link = isset($get_data['tw_btn_link']) ? esc_html($get_data['tw_btn_link']) : '';
                            $tw_button_type = isset($get_data['tw_button_type']) ? esc_html($get_data['tw_button_type']) : 'normal_btn';
                            $tw_tap_btn_text = isset($get_data['tw_tap_btn_text']) ? esc_html($get_data['tw_tap_btn_text']) : '';
                            $tw_tap_btn_number = isset($get_data['tw_tap_btn_number']) ? esc_html($get_data['tw_tap_btn_number']) : '';

                            ?>
                            <td data-colname="Button Type"><?php echo ($tw_button_type == "normal_btn") ? 'Normal Button' : 'Tap To Call'; ?></td>
                            <td data-colname="Button Text"><?php echo esc_html($tw_button_type == "normal_btn" ? $tw_btn_text : $tw_tap_btn_text); ?></td>
                            <td data-colname="Button Link/Number"><?php echo esc_html($tw_button_type == "normal_btn" ? $tw_btn_link : $tw_tap_btn_number); ?></td>
                            <td class="actions-btn" data-colname="Actions">
                                <span data-action="buttons" data-rand_id="<?php echo esc_attr($id); ?>" class="wt-edit-testi wt-icons btn btn-outline-primary px-4 mr-2 py-2">Edit</span>
                                <span data-rand_id="<?php echo esc_attr($id); ?>" data-action="buttons" class="wt-dlt-testi wt-icons btn btn-outline-danger py-2">Remove</span>
                            </td>
                        </tr>
                    <?php
                    }
                } else {
                    // Output something when no buttons are found
                    echo '<tr><td colspan="5">No buttons found.</td></tr>';
                }
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
                    <th scope="col" class="text-center manage-column manage-column column-title column-primary" style="width:10%">Name/#ID</th>
                    <th scope="col" class="text-center manage-column">Video Type</th>
                    <th scope="col" class="text-center manage-column" style="width:17%;">Video Poster</th>
                    <th scope="col" class="text-center manage-column" style="width:20%;">Video URLs</th>
                    <th scope="col" class="text-center manage-column" style="width:18%;">Autoplay - Mute - Controls - Loop</th>
                    <th scope="col" class="text-center manage-column" style="width:20%">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $ids = get_option('improveseo_get_saved_random_numbers');

                if (!empty($ids)) {
                    foreach ($ids as $id) {
                        $get_data = get_option('get_videos_' . $id);

                        // Skip if data is empty
                        if (empty($get_data)) {
                            continue;
                        }

                        $shortcode_name = isset($get_data['video_shortcode_name']) ? esc_html($get_data['video_shortcode_name']) : '';
                        $display_name = ($shortcode_name != "") ? $shortcode_name : $id;

                        $video_type = isset($get_data['video_type']) ? esc_html($get_data['video_type']) : 'upload_video';

                        $video_poster_img_source = isset($get_data['video_poster_img_source']) ? esc_url($get_data['video_poster_img_source']) : '';
                        $video_url_mp4 = isset($get_data['video_url_mp4']) ? esc_url($get_data['video_url_mp4']) : '';
                        $video_url_ogv = isset($get_data['video_url_ogv']) ? esc_url($get_data['video_url_ogv']) : '';
                        $video_url_webm = isset($get_data['video_url_webm']) ? esc_url($get_data['video_url_webm']) : '';
                        $video_url_vimeo = isset($get_data['video_url_vimeo']) ? esc_url($get_data['video_url_vimeo']) : '';
                        $video_url_youtube = isset($get_data['video_url_youtube']) ? esc_url($get_data['video_url_youtube']) : '';

                        $video_autoplay = isset($get_data['video_autoplay']) ? esc_html($get_data['video_autoplay']) : 'no';
                        $video_muted = isset($get_data['video_muted']) ? esc_html($get_data['video_muted']) : 'no';
                        $video_controls = isset($get_data['video_controls']) ? esc_html($get_data['video_controls']) : 'no';
                        $video_loop = isset($get_data['video_loop']) ? esc_html($get_data['video_loop']) : 'no';

                        if ($video_type == "upload_video") {
                            $video_type_display = "Upload Video";
                        } elseif ($video_type == "youtube") {
                            $video_type_display = "YouTube";
                        } elseif ($video_type == "vimeo") {
                            $video_type_display = "Vimeo";
                        }

                        ?>
                        <tr>
                            <td class="column-title column-primary has-row-actions"><?php echo esc_html($display_name); ?></td>
                            <td data-colname="video_type"><?php echo esc_html($video_type_display); ?></td>

                            <?php if ($video_poster_img_source != "") : ?>
                                <td data-colname="video_poster_img">
                                    <img src="<?php echo esc_url($video_poster_img_source); ?>" style="max-width:200px; height:100px;" />
                                </td>
                            <?php else : ?>
                                <td data-colname="video_poster_img">No Poster Image</td>
                            <?php endif; ?>

                            <td data-colname="video_urls">
                                <?php
                                if ($video_type == "upload_video") {
                                    if ($video_url_mp4 != "") {
                                        echo '<a href="' . esc_url($video_url_mp4) . '" target="_blank">' . esc_url($video_url_mp4) . "</a><hr />";
                                    }

                                    if ($video_url_ogv != "") {
                                        echo '<a href="' . esc_url($video_url_ogv) . '" target="_blank">' . esc_url($video_url_ogv) . "</a><hr />";
                                    }

                                    if ($video_url_webm != "") {
                                        echo '<a href="' . esc_url($video_url_webm) . '" target="_blank">' . esc_url($video_url_webm) . "</a><hr />";
                                    }
                                } elseif ($video_type == "youtube") {
                                    echo '<a href="' . esc_url($video_url_youtube) . '" target="_blank">' . esc_url($video_url_youtube) . "</a><hr />";
                                } elseif ($video_type == "vimeo") {
                                    echo '<a href="' . esc_url($video_url_vimeo) . '" target="_blank">' . esc_url($video_url_vimeo) . "</a><hr />";
                                }
                                ?>
                            </td>

                            <td data-colname="video_extras">
                                <?php
                                echo ucfirst($video_autoplay) . ' - ' . ucfirst($video_muted) . ' - ' . ucfirst($video_controls) . ' - ' . ucfirst($video_loop);
                                ?>
                            </td>

                            <td class="actions-btn" data-colname="Actions">
                                <span data-action="videos" data-rand_id="<?php echo esc_attr($id); ?>" class="wt-edit-testi btn btn-outline-primary px-4 mr-2 py-2">Edit</span>
                                <span data-action="videos" data-rand_id="<?php echo esc_attr($id); ?>" class="wt-dlt-testi wt-icons btn btn-outline-danger py-2">Remove</span>
                            </td>
                        </tr>
                    <?php
                    }
                    echo $html;
                }
                ?>
            </tbody>
        </table>
    </div>
</section>

</section>