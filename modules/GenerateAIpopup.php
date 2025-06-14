<?php

function improve_seo_lits()

{



	global $wpdb;

	$list_names = array();

	$sql = "SELECT * FROM " . $wpdb->prefix . "improveseo_lists ORDER BY name ASC";

	$lists = $wpdb->get_results($sql);

	foreach ($lists as $li) {

		$list_names[] = $li->name;

		;

	}

	return $list_names;



}



function improve_lits_data()

{



	global $wpdb;

	$list_names = array();

	$sql = "SELECT * FROM " . $wpdb->prefix . "improveseo_lists ORDER BY name ASC";

	$lists = $wpdb->get_results($sql);

	return $lists;



}

function generateAIpopup()

{

    //wp_enqueue_scripts();

    $output = '';



    $saved_rnos = get_option('get_saved_random_numbers');

    $html = '';

    if (!empty($saved_rnos)) {

        foreach ($saved_rnos as $id) {



            //testimonials        

            $testimonial = get_option('get_testimonials_' . $id);

            if (!empty($testimonial)) {

                $display_name = $id;

                $data_name = '';

                if (isset($testimonial['tw_testi_shortcode_name'])) {

                    if ($testimonial['tw_testi_shortcode_name'] != "") {

                        $data_name = $display_name = $testimonial['tw_testi_shortcode_name'];

                    }

                }

                $html .= '<input type="hidden" class="option_' . $id . '" id="testimonial_' . $id . '" value="[improveseo_testimonial id=\'' . $id . '\' name=\'' . $data_name . '\']" name="shortcodeoption[]" /><button data-action="testimonial" data-name="' . $data_name . '" id="' . $id . '" class="sw-hide-btn button">Add Testimonial - ' . $display_name . '</button>';

            }



            //buttons        

            $buttons = get_option('get_buttons_' . $id);

            if (!empty($buttons)) {

                $display_name = $id;

                $data_name = '';

                if (isset($buttons['tw_button_shortcode_name'])) {

                    if ($buttons['tw_button_shortcode_name'] != "") {

                        $data_name = $display_name = $buttons['tw_button_shortcode_name'];

                    }

                }

                $html .= '<input type="hidden" class="option_' . $id . '" id="button_' . $id . '" value="[improveseo_buttons id=\'' . $id . '\' name=\'' . $data_name . '\']" name="shortcodeoption[]" /><button data-action="button" data-name="' . $data_name . '" id="' . $id . '" class="sw-hide-btn button">Add Button - ' . $display_name . '</button>';

            }



            //googlemaps        

            $google_map = get_option('get_googlemaps_' . $id);

            if (!empty($google_map)) {

                $display_name = $id;

                $data_name = '';

                if (isset($google_map['tw_maps_shortcode_name'])) {

                    if ($google_map['tw_maps_shortcode_name'] != "") {

                        $data_name = $display_name = $google_map['tw_maps_shortcode_name'];

                    }

                }

                $html .= '<input type="hidden" class="option_' . $id . '" id="map_' . $id . '" value="[improveseo_googlemaps id=\'' . $id . '\' name=\'' . $data_name . '\']" name="shortcodeoption[]" /><button data-action="googlemap" data-name="' . $data_name . '" id="' . $id . '" class="sw-hide-btn button">Add GoogleMap - ' . $display_name . '</button>';

            }



            //videos

            $videos = get_option('get_videos_' . $id);

            if (!empty($videos)) {

                $display_name = $id;

                $data_name = '';

                if (isset($videos['video_shortcode_name'])) {

                    if ($videos['video_shortcode_name'] != "") {

                        $data_name = $display_name = $videos['video_shortcode_name'];

                    }

                }

                $html .= '<input type="hidden" class="option_' . $id . '" id="video_' . $id . '" value="[improveseo_video id=\'' . $id . '\' name=\'' . $data_name . '\']" name="shortcodeoption[]" /><button data-action="video" data-name="' . $data_name . '" id="' . $id . '" class="sw-hide-btn button">Add Video - ' . $display_name . '</button>';

            }

        }

    }





    $seo_list = improve_seo_lits();

    if (!empty($seo_list)) {

        foreach ($seo_list as $li) {

            $html .= '<input type="hidden" class="option_' . $li . '" id="list_' . $li . '" value="@list:' . $li . '" name="shortcodeoption[]" /><button data-action="list" class="sw-hide-btn add-seolistshortcode button" id=' . $li . '>@list:' . $li . '</button>';

        }

    }



    // 20-05-24 start Code 

    // All Keywords list



    $listdata = improve_lits_data();

    $html_key = '';

    $all_keywords = [];

    foreach ($listdata as $list_key => $list_value) {

        $html_key .= '<option value="' . esc_attr($list_value->id) . '">' . esc_html($list_value->name) . '</option>';

        $all_keywords[$list_value->id] = $list_value->list;

    }

    // $saved_rand_nos_keywords = get_option('swsaved_random_nosofkeywords');

    // if (empty($saved_rand_nos_keywords)) {

    // 	return;

    // }

    // $saved_rand_nos_keywords = maybe_unserialize($saved_rand_nos_keywords);

    // $html_key = '';

    // $all_keywords = [];

    // foreach ($saved_rand_nos_keywords as $keyword_id) {

    // 	$get_keyworddata = get_option('swsaved_keywords_with_results_' . $keyword_id);

    // 	if (empty($get_keyworddata)) {

    // 		continue;

    // 	}

    // 	$proj_name = isset($get_keyworddata['proj_name']) ? $get_keyworddata['proj_name'] : '';

    // 	$search_results = isset($get_keyworddata['search_results']) ? $get_keyworddata['search_results'] : '';

    // 	$html_key .= '<option value="' . esc_attr($keyword_id) . '">' . esc_html($proj_name) . '</option>';

    // 	$all_keywords[$keyword_id] = $search_results;

    // }





    // Generate the HTML output for the image gallery

    if (!empty($post_id)) {

        $image_ids = get_post_meta($post_id, 'my_field', true);

        $image_ids = is_array($image_ids) ? $image_ids : array();



        $html_output = '<ul class="multi-upload-gallery">';

        foreach ($image_ids as $i => $id) {

            $url = wp_get_attachment_image_url($id, array(80, 80));

            if ($url) {

                $html_output .= '<li data-id="' . $id . '">

										<img src="' . $url . '" alt="Image ' . ($i + 1) . '" width="80" height="80">

										<a href="#" class="multi-upload-gallery-remove" style="display: inline;">&#215;</a>

									</li>';

            } else {

                unset($image_ids[$i]);

            }

        }





        $html_output .= '</ul>

			<input type="hidden" name="my_field" value="' . join(',', $image_ids) . '" />

			<a href="#" class="button multi-upload-button">Add Images</a>';

    }





    // categories code start



    $select = '';

    if (!empty($_GET['cat_pre'])) {

        $cat_pres = $_GET['cat_pre'];

        $cat_pres = explode(',', $cat_pres);

    }



    $args = array(

        "hide_empty" => 0,

        "type" => "post",

        "orderby" => "name",

        "order" => "ASC"

    );

    $cats = get_categories($args);

    foreach ($cats as $category) {

        $checked = '';

        if (!empty($cat_pres)) {

            foreach ($cat_pres as $cat_pres_key => $cat_pres_value) {

                if ($category->term_id == $cat_pres_value) {

                    $checked = 'checked';

                }

            }



        } else {

            if ($category->slug == "improve-seo") {

                $checked = 'checked  onclick="return false"';

            }

        }





        $select .= "<span class='category'><input type='checkbox' " . $checked . " value='" . $category->term_id . "' id='" . $category->term_id . "' name='cats[]'><label style='margin:0px;' for='" . $category->term_id . "'>" . $category->name . "</label></span>";

    }



    $saved_rnos = get_option('get_saved_random_numbers');

    $shortcode_html = '<h3>Testimonial</h3>';

    foreach ($saved_rnos as $id) {

        $testimonial = get_option('get_testimonials_' . $id);

        if (!empty($testimonial)) {

            $display_name = 'Testimonial - ' . $id;

            $data_name = '';

            if (isset($testimonial['tw_testi_shortcode_name'])) {

                if ($testimonial['tw_testi_shortcode_name'] != "") {

                    $data_name = $display_name = $testimonial['tw_testi_shortcode_name'];

                }

            }



            $shortcode_html .= '<label><input type="checkbox" class="radio" value="' . $id . '" name="testimonial_SC[]" />' . $display_name . '</label><br>';

        }

    }



    // button

    $shortcode_html .= '<h3>Button</h3>';

    foreach ($saved_rnos as $id) {

        $button = get_option('get_buttons_' . $id);

        $display_name = 'Button - ' . $id;

        $data_name = '';

        if (isset($button['tw_button_shortcode_name'])) {

            if ($button['tw_button_shortcode_name'] != "") {

                $data_name = $display_name = $button['tw_button_shortcode_name'];

            }

        }

        if (!empty($button)) {

            //$shortcode_html .= '<option value="'.$id.'" data-name="'.$data_name.'">'.$display_name.'</option>';

            $shortcode_html .= '<label><input type="radio" class="radio" value="' . $id . '" name="Button_SC" />' . $display_name . '</label><br>';

        }

    }



    //Google Map

    $shortcode_html .= '<h3>Google Map</h3>';

    foreach ($saved_rnos as $id) {

        $googlemap = get_option('get_googlemaps_' . $id);

        if (!empty($googlemap)) {

            $display_name = 'GoogleMap - ' . $id;

            $data_name = '';

            if (isset($googlemap['tw_maps_shortcode_name'])) {

                if ($googlemap['tw_maps_shortcode_name'] != "") {

                    $data_name = $display_name = $googlemap['tw_maps_shortcode_name'];

                }

            }

            //$shortcode_html .= '<option value="'.$id.'" data-name="'.$data_name.'">'.$display_name.'</option>';

            $shortcode_html .= '<label><input type="radio" class="radio" value="' . $id . '" name="GoogleMap_SC" />' . $display_name . '</label><br>';

        }

    }





    // video



    $shortcode_html .= '<h3>Video</h3>';

    foreach ($saved_rnos as $id) {

        $videos = get_option('get_videos_' . $id);

        $display_name = 'Video - ' . $id;

        $data_name = '';

        if (isset($videos['video_shortcode_name'])) {

            if ($videos['video_shortcode_name'] != "") {

                $data_name = $display_name = $videos['video_shortcode_name'];

            }

        }

        if (!empty($videos)) {

            //$shortcode_html .= '<option value="'.$id.'" data-name="'.$data_name.'">'.$display_name.'</option>';

            $shortcode_html .= '<label><input type="radio" class="radio" value="' . $id . '" name="Video_SC" />' . $display_name . '</label><br>';

        }

    }









    $AllShortCode = $shortcode_html;









    // categories code end

    ?>



    <script>







        jQuery(document).ready(function () {

            jQuery('#keyword_list_name').on('change', function () {

                var selectedOption = jQuery(this).val();

                if (selectedOption == 'create_new_project' || selectedOption == 'none') {

                    jQuery('#keyword_list_container').hide();

                } else {

                    jQuery('#keyword_list_container').show();

                    var allKeywords = <?php echo json_encode($all_keywords); ?>;

                    var keywordCount = allKeywords[selectedOption].split('\n').length;

                    var keywordMin = keywordCount * 3;

                    var keywordTime = (keywordMin / 60).toFixed(2);



                    jQuery('#keywordcounts').text(keywordCount);

                    jQuery('#keywordtime').text(keywordTime);

                    jQuery('#keyword_list').val(allKeywords[selectedOption]);

                }

                if (selectedOption == 'create_new_project') {

                    jQuery('#create_keyword_container').show();

                }

                else {

                    jQuery('#create_keyword_container').hide();

                }

            });

        });



    </script>

    <script>



        var displayKeywords = [];

        var results = {};

        var initialKeywords = 0;

        var doWork = false;

        var queryKeywords = [];

        var queryKeywordsIndex = 0;

        var queryflag = false;



        function generate() {

            if (doWork == false) {

                queryKeywords = [];

                queryKeywordsIndex = 0;

                displayKeywords = [];

                results = { '': 1, ' ': 1, '  ': 1 };

                var ks = jQuery('#input').val().split("\n");

                var i = 0;

                for (i = 0; i < ks.length; i++) {

                    queryKeywords[queryKeywords.length] = ks[i];

                    displayKeywords[displayKeywords.length] = ks[i];

                    var j = 0;

                    for (j = 0; j < 26; j++) {

                        var chr = String.fromCharCode(97 + j);

                        var currentx = ks[i] + ' ' + chr;

                        queryKeywords[queryKeywords.length] = currentx;

                        results[currentx] = 1;

                    }

                }

                initialKeywords = displayKeywords.length;

                doWork = true;

                jQuery('#startjob').val('Stop');

            }

            else {

                doWork = false;

                jQuery('#startjob').val('Start');

            }

        }

        function tick() {

            if (doWork == true && queryflag == false) {

                if (queryKeywordsIndex < queryKeywords.length) {

                    var currentKw = queryKeywords[queryKeywordsIndex];

                    query(currentKw);

                    queryKeywordsIndex++;

                }

                else {

                    if (initialKeywords != displayKeywords.length) {

                        doWork = false;

                        jQuery('#startjob').val('Start');

                    }

                    else {

                        queryKeywordsIndex = 0;

                    }

                }

            }

        }

        function query(keyword) {

            var querykeyword = keyword;

            var queryresult = '';

            queryflag = true;

            jQuery.ajax({

                url: 'https://suggestqueries.google.com/complete/search',

                jsonp: 'jsonp',

                dataType: 'jsonp',

                data: {

                    q: querykeyword,

                    client: 'chrome'

                },

                success: function (res) {

                    var retList = res[1];

                    for (var i = 0; i < retList.length; i++) {

                        var currents = clean(retList[i]);

                        if (results[currents] != 1) {

                            results[currents] = 1;

                            displayKeywords[displayKeywords.length] = clean(retList[i]);

                            queryKeywords[queryKeywords.length] = currents;

                            for (var j = 0; j < 26; j++) {

                                var chr = String.fromCharCode(97 + j);

                                var currentx = currents + ' ' + chr;

                                queryKeywords[queryKeywords.length] = currentx;

                                results[currentx] = 1;

                            }

                        }

                    }

                    display();

                    var textarea = document.getElementById("input");

                    textarea.scrollTop = textarea.scrollHeight;

                    queryflag = false;

                }

            });

        }

        function clean(input) {

            var val = input;

            val = val.replace("\\u003cb\\u003e", "");

            val = val.replace("\\u003c\\/b\\u003e", "");

            val = val.replace("\\u003c\\/b\\u003e", "");

            val = val.replace("\\u003cb\\u003e", "");

            val = val.replace("\\u003c\\/b\\u003e", "");

            val = val.replace("\\u003cb\\u003e", "");

            val = val.replace("\\u003cb\\u003e", "");

            val = val.replace("\\u003c\\/b\\u003e", "");

            val = val.replace("\\u0026amp;", "&");

            val = val.replace("\\u003cb\\u003e", "");

            val = val.replace("\\u0026", "");

            val = val.replace("\\u0026#39;", "'");

            val = val.replace("#39;", "'");

            val = val.replace("\\u003c\\/b\\u003e", "");

            val = val.replace("\\u2013", "2013");

            if (val.length > 4 && val.substring(0, 4) == "http") val = "";

            return val;

        }

        function display() {

            var sb = '';

            var outputKeywords = displayKeywords;

            for (var i = 0; i < 100; i++) {

                sb += outputKeywords[i];

                sb += '\n';

            }

            jQuery('#output').val(sb);

        }

        window.setInterval(tick, 750);



    </script>

    <?php

    $all_auths = '';

    $authors = get_users(array(

        'roles' => 'author'

    ));



    if (!empty($authors)) {

        $all_auths = '<select style="padding:10px 20px !important;" name="author_name">';

        foreach ($authors as $author) {

            $all_auths = $all_auths . '<option value="' . esc_attr($author->ID) . '">' . esc_html($author->data->display_name) . '</option>';

        }

        $all_auths = $all_auths . '</select>';

    } else {

        $all_auths = $all_auths . '<option value="0">No author found</option>';

    }







    if ((isset($_REQUEST['genaipost'])) && ($_REQUEST['genaipost'] == 'Generate AI Post')) {





        $aigeneratedtitle = $_REQUEST['aigeneratedtitle'];



        if (empty($aigeneratedtitle)) {

            $seed_keyword = $_REQUEST['seed_keyword'];

        } else {

            $seed_keyword = $_REQUEST['aigeneratedtitle'];

        }



        $keyword_selection = $_REQUEST['keyword_selection'];

        $seed_options = $_REQUEST['seed_options'];

        $nos_of_words = $_REQUEST['nos_of_words'];

        $content_lang = $_REQUEST['content_lang'];

        //$shortcode = $_REQUEST['shortcodeoption'];



        createAIpost($seed_keyword, $keyword_selection, $seed_options, $nos_of_words, $content_lang, $shortcode = '');

    }

    

    ob_start();

    $html_key_to_send = $html_key;
//check list error
    $list_to_send = $list;

    $AllShortCode_to_send = $AllShortCode;

    $all_auths_to_send = $all_auths;

    $select_to_send = $select;

    $file_path = dirname(__DIR__) . '/views/GenerateAIpopup/GenerateAIpopuphtml.php';

    if (file_exists($file_path)) {

        require $file_path;

    } else {

        die("File not found: $file_path");

    }

    $output = ob_get_clean();

    echo $output;

}



?>