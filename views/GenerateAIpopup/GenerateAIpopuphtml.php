<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
<script type="text/javascript"
    src="https://cdn.jsdelivr.net/gh/bbbootstrap/libraries@main/jquery.smartWizard.min.js"></script>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
<link href="https://cdn.jsdelivr.net/gh/bbbootstrap/libraries@main/smart_wizard.min.css" rel="stylesheet"
    type="text/css" />
<link href="https://cdn.jsdelivr.net/gh/bbbootstrap/libraries@main/smart_wizard_theme_dots.min.css" rel="stylesheet"
    type="text/css" />
<?php
$plugin_url = plugin_dir_url(dirname(__FILE__, 2)); // Go up 2 levels to the root of the plugin
$image_url = $plugin_url . 'assets/images/AI-generated.gif';
$image_url1 = $plugin_url . 'assets/images/Writing-Optimization.gif';
$image_url2 = $plugin_url . 'assets/images/loaderr.gif';
?>
<style>
    .modal {
        max-width: unset;
    }

    /*#exampleModal { z-index: 9999; }*/
    .modal-backdrop {
        height: unset;
    }

    .input-group>.form-control {
        width: 100%;
    }

    #popupcontainer input[type=checkbox] {
        display: none
    }

    #getpopupselected {
        margin: 20px 0;
    }

    .overlay {
        // margin :54px;
        display: none;
        position: fixed;
        width: 100%;
        height: 100%;
        // top: 209px;
        left: 0;
        z-index: 999;
        background: rgb(255, 255, 255) url("<?php echo $image_url1; ?>") center no-repeat;

    }

    .overlay2 {
        // margin :54px;
        display: none;
        position: fixed;
        width: 100%;
        height: 100%;
        // top: 209px;
        left: 0;
        z-index: 999;
        background: rgb(255, 255, 255) url("<?php echo $image_url2; ?>") center no-repeat;

    }

    .overlay_ai_data {
        // margin :54px;
        display: none;
        position: fixed;
        width: 100%;
        height: 100%;
        // top: 209px;
        left: 0;
        z-index: 999;
        background: rgb(255, 255, 255) url("<?php echo $image_url1; ?>") center no-repeat;

    }


    .overlay_ai_image {
        display: none;
        position: fixed;
        width: 100%;
        height: 100%;
        left: 0;
        z-index: 999;
        background: rgb(255, 255, 255) url("<?php echo $image_url; ?>") center no-repeat;
    }

    .sw-theme-dots>ul.step-anchor:before {
        top: 58px !important;
    }

    .selected {
        background: #b0b0b0 !important;
        color: #fff !important;
        font-weight: 600 !important;
    }

    .content_type {
        margin-top: 20px;
    }

    #gettitle span {
        display: flex;
        align-items: center;
    }

    #gettitle {
        display: flex;
        align-items: center;
    }

    .resultdata {
        /* border: 1px solid #bfbfbf; */
        padding: 0px !important;
        align-items: center;
        width: 100%;
        border-radius: 20px;
        margin-right: 10px;
    }

    #langerror {
        color: #f00;
    }


    .multi-upload-gallery span {
        height: 100px;
        width: 100px;
        background-size: cover;
        background-position: center;
        display: block;
    }

    ul.multi-upload-gallery.ui-sortable {
        grid-template-columns: repeat(6, 1fr);
        display: grid;
        column-gap: 5px;
    }

    a.multi-upload-gallery-remove {
        color: black;
        position: relative;
        padding-right: -10px !important;
        right: 20px;
        top: -25px;
        font-size: 30px;
    }
</style>




<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="false" aria-modal="false">

    <div class="improve-ai-modal" role="document">
        <div class="improve-ai-header">
            <span class="improve-ai-title" id="exampleModalLabel"> <img
                    src="<?php echo WT_URL . '/assets/images/latest-images/iconoir_sparks-solid.svg' ?>"
                    alt="iconoir_sparks-solid"> Generate AI Content</span>
            <span class="improve-ai-close-btn"> <img id="close_generate_ai_popup"
                    src="<?php echo WT_URL . '/assets/images/latest-images/akar-icons_cross.svg' ?>"
                    alt="akar-icons_cross"> </span>
        </div>
        <div class="improve-ai-body">
            <div class="improve-ai-option">
                <img src="<?php echo WT_URL . '/assets/images/latest-images/aiai.svg' ?>" alt="Single AI Post">
                <div class="improve-ai-option-box">
                    <button class="btn open_single_AI_Post_popup" id="#exampleModal1" data-toggle="modal"
                        data-target="#exampleModal1">Create
                        Single AI Post </button>
                    <img src="<?php echo WT_URL . '/assets/images/latest-images/ep_info-filled.svg' ?>"
                        alt="ep_info-filled">
                </div>
            </div>
            <div class="improve-ai-option">
                <img src="<?php echo WT_URL . '/assets/images/latest-images/bulk.svg' ?>" alt="Bulk AI Posts">
                <div class="improve-ai-option-box">
                    <button class="btn open_bulk_AI_Post_popup" id="#exampleModal1" data-toggle="modal"
                        data-target="#exampleModal2">Bulk Create
                        AI Posts </button>
                    <img src="<?php echo WT_URL . '/assets/images/latest-images/ep_info-filled.svg' ?>"
                        alt="ep_info-filled">
                </div>
            </div>
        </div>
    </div>

</div>

<div class="modal fade" id="exampleModal1" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">


    <div id="loadingImage" style="display:none ;" class="overlay2">
        <!-- <img src="' . home_url('/') . 'wp-content/plugins/jobseq_jobs_pugin/assets/image/loader.gif" alt="Loading..."> -->
    </div>
    <div id="loadingAIData" style="display:none;" class="overlay_ai_data"></div>

    <div id="loadingAIImage" style="display:none;" class="overlay_ai_image"></div>
    <div class="improveseo-bulk-ai">
        <div class="singlepost-title">
            <h1> <img src="<?php echo WT_URL . '/assets/images/latest-images/iconoir_sparks-solid.svg' ?>"
                    alt="iconoir_sparks"> Generate Single Al Post</h1>
            <div class="singlepost-close"> <img id="close_single_post"
                    src="<?php echo WT_URL . '/assets/images/latest-images/akar-icons_cross.svg' ?>" alt="icons_cross">
            </div>
        </div>

        <div class="steps">
            <div class="line-step">
                <div class="percent active" id="progressLine">
                    <div class="step" id="step-1">
                        <div class="circle">1</div>
                        <p>Keyword & <br>Post Title</p>
                    </div>
                </div>

                <div class="percent" id="progressLine">

                    <div class="step" id="step-2">
                        <div class="circle">2</div>
                        <p>Content <br> Settings</p>
                    </div>

                </div>

                <div class="percent" id="progressLine">

                    <div class="step" id="step-3">
                        <div class="circle">3</div>
                        <p>Add Media</p>
                    </div>
                </div>


                <div class="percent" id="progressLine">

                    <div class="step" id="step-4">
                        <div class="circle">4</div>
                        <p>Generate AI <br>Content</p>
                    </div>
                </div>

                <div class="percent">
                    <div class="step" id="step-5">
                        <div class="circle">5</div>
                        <p>Media Title & <br>Description</p>
                    </div>
                </div>
            </div>
        </div>

        <form id="popup_form" method="post" class="pop_up_form improve-seo-form-global">

            <div class="improveseo-sections">

                <!-- option 1  -->
                <div class="data dataJS">
                    <!-- <form class="improve-seo-form-global"> -->
                    <input type="hidden" name="step_value" id="step_value" value="1">
                    <div class="seo-form-field">
                        <label class="data-label" for="seed_keyword">Seed keyword</label>
                        <input type="text" class="form-control" placeholder="Enter Seed Keyword" id="seed_keyword"
                            name="seed_keyword"></input>
                        <span id="error_seed_keyword" style="color: red;"></span>
                    </div>
                    <div class="seo-form-field">
                        <div class="title-tune">
                            <div class="title">
                                <label for="seed_options">Select Title Type</label>
                                <select id="seed_select" name="seed_options" id="title" class="custom-selcected">
                                    <option value="">Select Title Type</option>
                                    <option value="seed_option1">USE KEYWORD AS IS IN TITLE [A.I. will build
                                        content]</option>
                                    <option value="seed_option2">CREATE BEST TITLE FROM KEYWORD [A.I. will
                                        choose/build content]</option>
                                    <option value="seed_option3">CREATE BEST QUESTION FROM KEYWORD [A.I. will
                                        choose/build content]</option>
                                </select>
                            </div>
                            <span id="error_seed_select" style="color: red;"></span>
                            <div style="clear: both"> </div>
                            <div class="tune" id="seed">
                                <label for="tune">Tone of voice</label>
                                <select name="content_type" id="cotnt_type" id="tune" class="custom-selcected">
                                    <option value="">Tone of Voice</option>
                                    <option value="friendly">Friendly</option>
                                    <option value="professional">Professional</option>
                                    <option value="informational">Informational</option>
                                    <option value="transactional">Transactional</option>
                                    <option value="inspirational">Inspirational</option>
                                    <option value="neutral">Neutral</option>
                                    <option value="witty">Witty</option>
                                    <option value="casual">Casual</option>
                                    <option value="authoritative">Authoritative</option>
                                    <option value="encouraging">Encouraging</option>
                                    <option value="persuasive">Persuasive</option>
                                    <option value="poetic">Poetic</option>
                                </select>
                                <span id="error_cotnt_type" style="color: red;"></span>
                                <div class="form-group col-md-1"></div>
                            </div>
                        </div>
                    </div>
                    <div style="clear: both"> </div>
                    <div id="loader" style="display: none;">Loading...</div>
                    <div style="clear: both"> </div>
                    <div class="seo-form-field hide_on_seed_option1">
                        <div class="generate-title">
                            <div class="title-input">
                                <label for="Generate">AI Generated Title</label>
                                <span id="maintitle">
                                    <div class="resultdata">
                                        <textarea class="title-text" name="maintitlearea" id="maintitlearea"
                                            required></textarea>
                                        <div id="for_approve_content_validation" style="display: none;">
                                            <p class="for_approve_content_validation_error"
                                                style="color: red !important;">Approve AI generated title in order to
                                                continue</p>
                                        </div>
                                    </div>
                                </span>

                                <label id=""><span><input style=" display: none;" type="checkbox" id="" /></span><span
                                        id="maintitle"> </span><label style=" display: none;" id=""><i
                                            class="fa fa-refresh" aria-hidden="true"></i></label></label>
                                <input type="hidden" name="aigeneratedtitle" id="aigeneratedtitle" />




                            </div>

                            <div class="title-btn">
                                <button type="button" id="reload"><img
                                        src="<?php echo WT_URL . '/assets/images/Vector.png' ?>"
                                        alt="ep_arrow-rights">&nbsp;Regenerate</button>
                                <label class="step_one_approve_button" for="checkbox_need"><input type="checkbox"
                                        id="checkbox_need" /><span style="display: none;" class="icon"><img
                                            src="<?php echo WT_URL . '/assets/images/hugeicons_tick-01.png' ?>"
                                            alt="ep_arrow-rights"></span><span class="label-text">Approve</span></label>
                            </div>
                        </div>
                    </div>


                </div>

                <!-- option 1  -->

                <!-- option 2 -->
                <div class="data">
                    <div class="step-opton2-content-box">
                        <div class="step-opton-col">
                            <div class="seo-form-field">
                                <label for="sel1">Article Size</label>
                                <!-- <select class="content-opt custom-selcected" name="nos_of_words" required
                                    id="post_size">
                                    <option value="600 to 1200 words">Small (600-1200 words)</option>
                                    <option value="1200 to 2400 words">Medium (1200 to 2400 words)</option>
                                    <option value="2400 to 3600 words">Large (2400 to 3600 words)</option>
                                </select> -->
                                <select class="form-control" name="nos_of_words" required
                                    style="max-width: 100% !important;" id="post_size">
                                    <option value="600 to 1200 words">Small </option>
                                    <option value="1200 to 2400 words">Medium </option>
                                    <option value="2400 to 3600 words">Large</option>

                                </select>
                                <input type="text" id="post_size_select" readonly style="width: 100% !important; display: none !important;"
                                    value="600-1200 words">
                            </div>

                        </div>

                        <div class="step-opton-col">
                            <div class="seo-form-field">
                                <label for="size">Point of Views</label>
                                <select class="content-opt custom-selcected" name="point_of_view" id="size">
                                    <option value="First person singular (I,me,my,mine)">First person singular
                                        (I, me, my, mine)
                                    </option>
                                    <option value="First person plural (we,us,our,ours)">First person plural
                                        (we, us, our, ours)</option>
                                    <option value="Second Person (you,your,yours)">Second Person
                                        (you, your, yours)</option>

                                </select>
                            </div>
                        </div>

                        <div class="step-opton-col">
                            <div class="seo-form-field">
                                <label for="language">Select Language</label>
                                <select class="content-opt custom-selcected" name="content_lang" id="language">
                                    <option value="US English">English (US)</option>
                                    <option value="UK English">English (Uk)</option>
                                    <option value="German">German (De)</option>

                                </select>
                            </div>
                        </div>

                    </div>
                    <div class="seo-form-field">
                        <label for="sel1">Details to Include <a href="#"
                                data-toggle="Please ensure the information you input aligns with the Main Keyword and Title. For example, information about dogs should not be added if you are writing about roofing."
                                title="Please ensure the information you input aligns with the Main Keyword and Title. For example, information about dogs should not be added if you are writing about roofing.">
                                <div class="dashicons dashicons-info-outline" aria-hidden="true"><br>
                                </div>
                            </a></label>
                        <!-- <textarea class="detail-text" id="exampleFormControlTextarea" rows="6" name="details_to_include"
                            onkeypress="return countContent()" OnBlur="LimitText(this,1000,1)"
                            placeholder="Any details you want to include in the content? Example: 'focus on the services that my nail salon 'Goddess Nail Bar' offers : manicures, pedicures, and nail enhancements like acrylics or gel nails, also consider local and cultural details about Brooklyn, NY, where my nail salon is located"></textarea> -->
                        <textarea class="form-control" id="exampleFormControlTextarea" rows="3"
                            name="details_to_include" onkeypress="return countContent()" onblur="LimitText(this,1500,1)"
                            placeholder="Any details you want to include in the content? Example: 'focus on the services that my nail salon 'Goddess Nail Bar' offers : manicures, pedicures, and nail enhancements like acrylics or gel nails, also consider local and cultural details about Brooklyn, NY, where my nail salon is located"></textarea>
                        <span id="countContent"></span>
                    </div>
                    <div class="seo-form-field">
                        <label for="sel1">Call to Action<a href="#" data-toggle="Information" title="Information">
                                <div class="dashicons dashicons-info-outline" aria-hidden="true"><br>
                                </div>
                            </a></label>
                        <!-- <textarea class="detail-text2" id="call_to_action" rows="6" name="call_to_action"
                            onkeypress="return countContentCallToAction()" OnBlur="LimitText(this,1000,2)"
                            placeholder="What action would you like the reader of your content to take?"></textarea> -->
                        <textarea class="form-control" id="call_to_action" rows="3" name="call_to_action"
                            onkeypress="return countContentCallToAction()" onblur="LimitText(this,1000,2)"
                            placeholder="What action would you like the reader of your content to take?"></textarea>
                        <span id="countContentCallToAction"></span>
                    </div>
                </div>

                <!-- option 2 -->



                <!-- option 3 -->
                <div class="data">
                    <div class="seo-slide-steps-fours">

                        <div class="radio-container">
                            <input type="radio" name="aiImage" onclick="SeedHide()" value="AI_image" id="AI_image">
                            <label for="AI_image">Generate AI Image Based on Title</label>

                            <input type="radio" name="aiImage" value="manually_promt_image" id="manually_promt_image">
                            <label for="manually_promt_image">Generate AI Image - Edit Prompt</label>

                            <input type="radio" name="aiImage" onclick="SeedShow()" value="Manually_image"
                                id="Manually_image">
                            <label for="Manually_image">Manually Upload Image</label>
                        </div>
                        <!-- <div class="flex_imgae_gereater_radio">
                            <div class="col" style="text-align: left; margin-top: 30px;">
                                <label for="AI_image" class="style_imgae_gereater_radio">
                                    <input class="for_styling_generate_image_radio_buttons" type="radio" name="aiImage"
                                        onclick="SeedHide()" value="AI_image" id="AI_image">
                                    <span class="custom-radio"></span>
                                    Generate AI Image Based On Title
                                </label>
                            </div>
                            <div class="col" style="text-align: left; margin-top: 30px;">
                                <label for="manually_promt_image" class="style_imgae_gereater_radio">
                                    <input class="for_styling_generate_image_radio_buttons" type="radio" name="aiImage"
                                        value="manually_promt_image" id="manually_promt_image">
                                    <span class="custom-radio"></span>
                                    Generate AI Image - Edit Prompt
                                </label>
                            </div>
                            <div class="col" style="text-align: left; margin-top: 30px;">
                                <label for="Manually_image" class="style_imgae_gereater_radio">
                                    <input class="for_styling_generate_image_radio_buttons" type="radio" name="aiImage"
                                        onclick="SeedShow()" value="Manually_image" id="Manually_image">
                                    <span class="custom-radio"></span>
                                    Manually Upload Image
                                </label>
                            </div>
                        </div> -->

                        <div id="AI_image_div" class="col-md-12" style="display:none;">
                            <div id="ai-image-display"></div>
                            <div class="form-group col-md-12" style="margin: 0 0 0 40%;" id="AIrefreshOption">
                                <button class="styling_post_page_action_buttons " onclick="return refreshAIImage()"
                                    style="cursor:pointer;">Regenerate AI Image</button>
                                <!-- <i class="fa fa-refresh" aria-hidden="true" onclick="return refreshAIImage()"
                                style="cursor:pointer;"></i> -->
                            </div>
                            <input type="hidden" id="AI-Image-uploaded-path" name="AI-Image-uploaded-path">
                        </div>

                        <div id="manually_image_div" class="col-md-12" style="display:none;">
                            <div class="improve-seo-upload-box">
                                <h2 class="frg-drp">Drag & Drop Your File here <br> <span> or </span></h2>
                                <label for="upload-image-button">Choose a File</label>
                                <p style="font-size: 14px; color: #adb5bd;">from your Storage</p>
                                <input type="file" id="upload-image-button" name="Manually_image">
                                <div id="manually-image-display"></div>
                                <input type="hidden" id="manually-image-uploaded-path"
                                    name="manually-image-uploaded-path">



                            </div>
                        </div>



                        <div class="form-group col-md-12" id="Prompt_to_create_Dalle_Image"
                            style="margin: 0 0 40px 0px; display: none;">

                            <div id="manually_promt" style="margin: 40px 40px 40px 40px;">
                                <div class="textarea_in_image_genration_center">
                                    <textarea class="form-control" id="manually_promt_for_image" rows="7"
                                        style="background-color:white !important; padding: 20px 20px 0px 20px; width: 100% !important;"
                                        name="manually_promt_for_image" onkeypress="return countContent()"
                                        OnBlur="LimitText(this,1000,3)"></textarea>
                                    <span id="error_manually_promt_for_image"></span>
                                </div>
                                <div class="generate-data-widt-ai">
                                    <!-- <h3> Retrieving Article Size... </h3>-->
                                    <!-- <img id="hide_older_genrated_image_on_step3"
                                    src="<?php echo WT_URL . '/assets/images/83d0e43cd6c5b0bd5633c1a8567f877a.jpeg' ?>"
                                    alt="ep_arrow-rights"> -->
                                    <div id="prompt_image_div" class="col-md-12" style="display:none;">
                                        <div id="ai-with-prompt-image-display" style="margin-bottom: 10px;"></div>

                                        <input type="hidden" id="AI-Prompt-Image-uploaded-path"
                                            name="AI-Prompt-Image-uploaded-path">
                                    </div>
                                </div>

                                <input type="button" name="generate_i_image" class="styling_post_page_action_buttons "
                                    id="generate_i_image" value="Generate AI Image" style="margin: 10px auto;" />
                            </div>
                        </div>
                    </div>

                </div>

                <!-- option 3 -->


                <!-- option 4 -->
                <div class="data">
                    <div class="generate-data">

                        <textarea class="form-control" id="showmydataindivText" rows="1" style="opacity: 0;"></textarea>

                        <div id="showmydataindiv1" name="showmydataindiv1"
                            style="display: block;max-width: 100%;overflow-y: scroll; "></div>
                        <input type="hidden" name="ai_tittle" id="ai_title" />
                        <div
                            style="text-align: center; display: flex; justify-content: center; align-items: center; gap: 10px; margin: 20px;">
                            <!-- <input type="button" value="Approve Content" class="btn btn-primary" onclick="return saveData()"
                            id="generateapi" style="display:none;" style="margin: 0px 0px -37px 0px;"> -->

                            <span><input type="checkbox" value="1" id="for_testing_only" name="for_testing_only">
                                <!-- <label for="for_testing_only">For Testing Only</label> -->
                            </span><br>


                            <input type="button" name="genaipost" class="styling_post_page_action_buttons"
                                id="generateapivalue" value="Generate AI Post" />
                            <input type="hidden" name="AI_Title" id="AI_Title">
                            <input type="hidden" name="AI_descreption" id="AI_descreption">
                        </div>
                    </div>
                </div>
                <!-- option 5 -->
                <div class="data meta-data">
                    <div class="seo-form-field">
                        <label for="meta_title">Meta Title</label>
                        <input type="text" id="meta_title" name="meta_title" placeholder="Enter title..." />
                    </div>
                    <div class="seo-form-field">
                        <label for="meta_descreption">Meta Descripttion</label>
                        <textarea id="meta_descreption" name="meta_descreption"
                            placeholder="Enter description...."></textarea>
                    </div>
                    <!-- <input type="button" value="Submit" onclick="return saveFinalData()"> -->


                </div>
            </div>
        </form>
        <div class="btn-dev">
            <button id="prevStepButton"> <img
                    src="<?php echo WT_URL . '/assets/images/latest-images/ep_arrow-left.svg' ?>" alt="ep_arrow-left">
                Previous</button>
            <button id="nextStepButton" class="style_next_button_in_popup">Next <img
                    src="<?php echo WT_URL . '/assets/images/latest-images/ep_arrow-rights.svg' ?>"
                    alt="ep_arrow-rights"> </button>
        </div>
    </div>

</div>

<div class="modal fade" id="exampleModal2" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="false" aria-modal="false">

    <div id="loadingImage" style="display:none ;" class="overlay2">

        <!-- <img src="' . home_url('/') . 'wp-content/plugins/jobseq_jobs_pugin/assets/image/loader.gif" alt="Loading..."> -->
    </div>
    <div id="loadingAIData" style="display:none;" class="overlay_ai_data"></div>

    <div id="loadingAIImage" style="display:none;" class="overlay_ai_image"></div>

    <div class="improveseo-bulk-ai_multi">
        <div class="singlepost-title_multi">
            <h1><img src="<?php echo WT_URL . '/assets/images/latest-images/iconoir_sparks-solid.svg' ?>"
                    alt="iconoir_sparks"> Bulk Create AI Posts</h1>
            <div class="singlepost-close_multi"><img id="close_bulk_post"
                    src="<?php echo WT_URL . '/assets/images/latest-images/akar-icons_cross.svg' ?>" alt="icons_cross">
            </div>
        </div>
        <form id="pop_up_multi_form" action="multipost_form_submit" method="post" class="pop_up_multi_form">
            <div class="steps_multi">
                <div class="line-step_multi">
                    <!-- Step 1 -->
                    <div class="percent_multi active_multi">
                        <div class="step_multi">
                            <div class="circle_multi">1</div>
                            <p>Keyword & <br>Post Title</p>
                        </div>
                    </div>
                    <!-- Step 2 -->
                    <div class="percent_multi">
                        <div class="step_multi">
                            <div class="circle_multi">2</div>
                            <p>Content <br>Settings</p>
                        </div>
                    </div>
                    <!-- Step 3 -->
                    <div class="percent_multi">
                        <div class="step_multi">
                            <div class="circle_multi">3</div>
                            <p>Add <br>Media</p>
                        </div>
                    </div>
                    <!-- Step 4 -->
                    <div class="percent_multi">
                        <div class="step_multi">
                            <div class="circle_multi">4</div>
                            <p>SEO <br>Visuals</p>
                        </div>
                    </div>
                    <!-- Step 5 -->
                    <div class="percent_multi">
                        <div class="step_multi">
                            <div class="circle_multi">5</div>
                            <p>Meta Title & <br>Description</p>
                        </div>
                    </div>
                    <!-- Step 6 -->
                    <div class="percent_multi">
                        <div class="step_multi">
                            <div class="circle_multi">6</div>
                            <p>Publish <br>Settings</p>
                        </div>
                    </div>
                    <!-- Step 7 -->
                    <div class="percent_multi">
                        <div class="step_multi">
                            <div class="circle_multi">7</div>
                            <p>Finalize</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="improveseo-sections_multi">
                <!-- Step 1 Content -->
                <div class="data_multi dataJSmulti_multi">
                    <div class="bulk-widths1170_multi">
                        <div class="improve-seo-form-global_multi">
                            <div class="form-group">
                                <label style="padding-left:20px;" for="keyword_list_name">Select a project</label>
                                <select id="keyword_list_name" name="keyword_list_name"
                                    class="form-control bulk_post_input_style"
                                    style="max-width: 100% !important; width: 100%; padding: 10px 20px !important;">
                                    <option value="">Select a project</option>
                                    <option value="create_new_project">Create New KW List</option>
                                    <?php echo $html_key_to_send; ?>
                                </select>
                                <span id="error_keyword_list_name" style="color: red;"></span>
                            </div>
                            <div class="form-group" id="keyword_list_container" style="display: none;">
                                <label style="padding-left:20px;" for="keyword_list">Keywords</label>
                                <textarea id="keyword_list" name="keyword_list" class="form-control" rows="10"
                                    style="max-width: 100% !important; width: 100%;"></textarea>
                                <div id="keyword_count"></div>
                            </div>
                            <div id="create_keyword_container"
                                style="display: none; max-width: 100% !important; width: 100%; padding-bottom:15px;">
                                <label style="padding-left:20px;"> How do you want to create a new list?</label><br>
                                <select id="create_keyword" name="create_keyword" class="form-control"
                                    style="max-width: 100% !important; width: 100%;    padding: 10px 20px !important;">
                                    <option value="none">Select</option>
                                    <option value="copy_paste">Copy & Paste</option>
                                    <option value="google_suggestion">Generate Google Suggest KW list
                                    </option>
                                    <option value="ai_create_keyword">AI generated KW list</option>
                                </select>
                                <div id="copy_paste_container" style="width:100%;"></div>
                                <div id="google_suggestion_container" style="width:100%;"></div>
                                <div id="ai_suggestion_container" style="width:100%;"></div>
                            </div>
                            <div id="tone_of_voice">
                                <label style="padding-left:20px;" for="cotnt_type"> Tone of Voice</label>
                                <select class="form-control" name="content_type" id="cotnt_type"
                                    style="max-width: 100% !important; width: 100%;    padding: 10px 20px !important;">
                                    <option value="">Tone of Voice</option>
                                    <option value="friendly">Friendly</option>
                                    <option value="professional">Professional</option>
                                    <option value="informational">Informational</option>
                                    <option value="transactional">Transactional</option>
                                    <option value="inspirational">Inspirational</option>
                                    <option value="neutral">Neutral</option>
                                    <option value="witty">Witty</option>
                                    <option value="casual">Casual</option>
                                    <option value="authoritative">Authoritative</option>
                                    <option value="encouraging">Encouraging</option>
                                    <option value="persuasive">Persuasive</option>
                                    <option value="poetic">Poetic</option>
                                </select>
                                <span id="error_cotnt_type" style="color: red;"></span>
                                <div class="form-group col-md-1"></div>
                            </div>
                            <label style="padding-left:20px;" for="existing_select"> Select</label>
                            <select id="existing_select" name="select_exisiting_options" class="form-control"
                                style="max-width: 100% !important; width: 100%;    padding: 10px 20px !important;">
                                <option value="">Select</option>
                                <option value="seed_option1">USE KEYWORD AS IS IN TITLE [A.I. will build
                                    content]</option>
                                <option value="seed_option2">CREATE BEST TITLE FROM KEYWORD [A.I. will
                                    choose/build content]</option>
                                <option value="seed_option3">CREATE BEST QUESTION FROM KEYWORD [A.I. will
                                    choose/build content]</option>
                            </select>
                            <span id="error_existing_select" style="color: red;"></span>
                            <div class="form-group col-md-12" style="padding: 20px 0px !important;">
                                <label style="padding-left:20px;" for="sel1">Details to Include <a href="#"
                                        class="underline_none_for_a_tag"
                                        data-toggle="Please ensure the information you input aligns with the Main Keyword and Title. For example, information about dogs should not be added if you are writing about roofing."
                                        title="Please ensure the information you input aligns with the Main Keyword and Title. For example, information about dogs should not be added if you are writing about roofing.">
                                        <div class="dashicons dashicons-info-outline" aria-hidden="true">
                                            <br>
                                        </div>
                                    </a></label>
                                <textarea class="form-control" id="exampleFormControlTextarea1" rows="3"
                                    name="details_to_include" style=" max-width: 100% !important; width: 100%;"
                                    onkeypress="return countContent()" OnBlur="LimitText(this,1000,1)"></textarea>

                                <div class="BasicForm__row mb-3"
                                    style="max-width:100%; text-align:end; justify-content: end;  padding:15px 0px;">
                                    <input type="button" style="margin-top:20px;" onclick="return SaveResultsButton();"
                                        class="styling_post_page_action_buttons only_for_mobile_style"
                                        value="AI Generate Context Based On Keyword List">
                                    <span id="countContent"></span>
                                </div>

                            </div>
                            <div style="margin-top: 20px;" class="show_lists"><?php echo $list_to_send; ?> </div>
                            <textarea placeholder="Context Prompt"
                                style="margin-top: 20px; max-width: 100% !important; width: 100%; display: none; resize:none;"
                                class="form-control" name="existing_keyword"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Step 2 Content -->
                <div class="data_multi">
                    <div class="bulk-widths1170_multi">
                        <div class="improve-seo-form-global_multi">
                            <div class="row">

                                <div class="form-group col-md-12">
                                    <label style="padding-left:20px;" for="sel1">Article size</label>
                                    <select class="form-control" name="nos_of_words" required
                                        style="max-width: 100% !important;  padding: 10px 20px !important;"
                                        id="post_size_bulk">

                                        <option value="600 to 1200 words">Small (600 to 1200 words) </option>
                                        <option value="1200 to 2400 words">Medium (1200 to 2400 words) </option>
                                        <option value="2400 to 3600 words">Large (2400 to 3600 words)</option>

                                    </select>
                                </div>
                            </div>
                            <div class="row" style="display: none;">

                                <div class="form-group col-md-12">

                                    <input type="text" id="post_size_select_bulk" readonly
                                        style="width: 100% !important;  padding: 10px 20px !important;"
                                        value="600-1200 words">

                                </div>
                            </div>
                            <div class="row only_for_multi_mobile">
                                <div class="form-group col">
                                    <label style="padding-left:20px;" for="sel1">Point of View</label>
                                    <select class="form-control" name="point_of_view"
                                        style="max-width: 100% !important;  padding: 10px 20px !important;">

                                        <option value="none">None
                                        </option>
                                        <option value="First person singular (I,me,my,mine)">First person singular
                                            (I, me, my, mine)
                                        </option>
                                        <option value="First person plural (we,us,our,ours)">First person plural
                                            (we, us, our, ours)</option>
                                        <option value="Second Person (you,your,yours)">Second Person
                                            (you, your, yours)</option>


                                    </select>
                                </div>
                                <div class="form-group col">
                                    <label style="padding-left:20px;" for="language">Select Language</label>
                                    <select class="form-control" name="content_lang" id="language"
                                        style="max-width: 100% !important;  padding: 10px 20px !important;">

                                        <option value="">Select Language</option>
                                        <option value="US English">English (US)</option>
                                        <option value="UK English">English (Uk)</option>
                                        <option value="German">German (De)</option>

                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-12">
                                    <label style="padding-left:20px;" for="sel1">Call to action <a href="#"
                                            class="underline_none_for_a_tag" data-toggle="Information"
                                            title="Information">
                                            <div class="dashicons dashicons-info-outline" aria-hidden="true"><br>
                                            </div>
                                        </a></label>
                                    <textarea class="form-control" id="call_to_action" rows="6" name="call_to_action"
                                        onkeypress="return countContentCallToAction()" OnBlur="LimitText(this,1000,2)"
                                        style="max-width: 100% !important;"></textarea><span
                                        id="countContentCallToAction"></span>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3 Content -->
                <div class="data_multi">
                    <div class="seo-slide-steps-fours_multi bulk-boxx_multi">
                        <!-- <div class="style_bulk_image_section_parent">
                            <div class="style_bulk_image_section flex_imgae_gereater_radio">
                                <div class="col-md-6 col">
                                    <label class="style_imgae_gereater_radio">
                                        <input type="radio" name="aiImage" value="AI_image_one" id="AI_image"
                                            class="show_hide_content_in_bulk_part_3" onclick="multiple_image_div();">
                                        Generate AI Image Based On Title
                                    </label>
                                </div>

                                <div class="col-md-6 col">
                                    <label class="style_imgae_gereater_radio">
                                        <input type="radio" name="aiImage" value="Multiple_images"
                                            onclick="SelectexisitingHide();" id="Multiple_images">
                                        Upload Your Own Images (Up to 10)
                                    </label>
                                </div>
                            </div>
                        </div> -->
                        <div class="seo-slide-steps-fours bulk-boxx">
                            <div class="radio-container">
                                <input type="radio" name="aiImage" class="show_hide_content_in_bulk_part_3"
                                    value="AI_image_one" id="AI_image_multi" onclick="multiple_image_div();">
                                <label for="AI_image_multi">Generate AI Image Based On Title</label>

                                <input type="radio" name="aiImage" value="Multiple_images"
                                    onclick="SelectexisitingHide();" id="Multiple_images">
                                <label for="Multiple_images">Upload Your Own Images (Up to 10)</label>
                            </div>
                        </div>
                        <div class="form-group col-md-12" style="margin: 0 0 0 0;">
                            <div id="multiple_image_div" style="display:none;">
                                <form id="uploadForm">
                                    <div class="improve-seo-upload-box">
                                        <h2 class="frg-drp">Drag & Drop Your File here <br> <span> or </span></h2>
                                        <input type="file" id="images" name="images[]" multiple>
                                        <div
                                            style="width: 100%; display: flex; justify-content: center; align-items: center; flex-direction: column;">
                                            <label style="max-width: max-content;"
                                                class="styling_post_page_action_buttons2 styling_post_page_action_buttons"
                                                for="images">Choose a File</label>
                                            <div id="preview"></div>
                                            <div id="response"></div>
                                            <div id="hiddenInputs"></div>
                                            <button style="max-width: max-content; padding: 5px 54px !important; "
                                                type="button" class="styling_post_page_action_buttons"
                                                id="uploadBtn">Upload</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div id="ai-image-generating" class="ai-image-content active text-center ok-aii">
                            <h3> Ok, AI images will be generated based on the post titles. </h3>
                            <p> <strong> Note: </strong> a preview of the images is not available yet at this step. </p>
                        </div>
                    </div>
                </div>

                <!-- Step 4 Content -->
                <div class="data_multi">
                    <div class="fourth_ttepss_multi">
                        <div class="fourth_ttepss_outer_multi">
                            <div class="fourth_ttepss-lft_multi">Testimonial:</div>
                            <div class="fourth_ttepss-rgt_multi">
                                <div class="category-box_multi">
                                    <div class="category-list_multi">
                                        <span class="category_multi active_multi">Test 1</span>
                                        <span class="category_multi">Testimonial 1</span>
                                        <span class="category_multi">Testimonial 2</span>
                                        <span class="category_multi">SAT testimonial</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="fourth_ttepss_outer_multi">
                            <div class="fourth_ttepss-lft_multi">Button:</div>
                            <div class="fourth_ttepss-rgt_multi whit-bbg_multi">
                                <div class="category-box_multi">
                                    <div class="category-list_multi">
                                        <span class="category_multi active_multi">Test button</span>
                                        <span class="category_multi">Janaki button</span>
                                        <span class="category_multi">Free trial button</span>
                                        <span class="category_multi">Janaki test</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="fourth_ttepss_outer_multi">
                            <div class="fourth_ttepss-lft_multi">Google map:</div>
                            <div class="fourth_ttepss-rgt_multi whit-bbg_multi">
                                <div class="category-box_multi">
                                    <div class="category-list_multi">
                                        <span class="category_multi active_multi">Google map</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="fourth_ttepss_outer_multi">
                            <div class="fourth_ttepss-lft_multi">Video:</div>
                            <div class="fourth_ttepss-rgt_multi whit-bbg_multi">
                                <div class="category-box_multi">
                                    <div class="category-list_multi">
                                        <span class="category_multi active_multi">Video 1</span>
                                        <span class="category_multi">Video 2</span>
                                        <span class="category_multi">Mr test free trial video</span>
                                        <span class="category_multi">Kaizen- mads</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 5 Content -->
                <div class="data_multi meta-data_multi text-center_multi height-adjusts_multi">
                    <h4>An SEO optimized meta title and meta description will be automatically “AI Generated”. A preview
                        is
                        not available.</h4>
                </div>

                <!-- Step 6 Content -->
                <div class="data_multi">
                    <div class="seo-slide-steps-fours_multi seps-six_multi">
                        <h2 style="padding-left:20px;">Define Save & Publish Preference <img
                                src="<?php echo WT_URL . '/assets/images/latest-images/nfo-filledss.svg' ?>" alt="info">
                        </h2>
                        <div class="radio-container_multi seps-six-col_multi">
                            <div class="schedule_posts_parent">
                                <div class="flex_schedule_radio">
                                    <div class="col_schedule">
                                        <label class="style_schedule_radio">
                                            <input type="radio" name="schedule_posts" value="schedule_all_posts"
                                                id="AI_image">&nbsp;&nbsp;Publish all selected posts immediately
                                        </label>
                                    </div>
                                    <div class="col_schedule">
                                        <label class="style_schedule_radio">
                                            <input type="radio" name="schedule_posts" value="draft_posts"
                                                id="AI_image">&nbsp;&nbsp;Save all selected posts in draft mode, so you
                                            can review them before
                                            publishing
                                        </label>
                                    </div>
                                    <div class="col_schedule">
                                        <label class="style_schedule_radio">
                                            <input type="radio" name="schedule_posts" value="schedule_posts_input_wise"
                                                id="schedule_posts_input_wise">&nbsp;&nbsp;Create a publishing schedule
                                            for the selected posts (if you don’t want to
                                            publish them all at once)
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="defines-saves_multi">
                                <div class="col-md-12" id="number_of_post_schedule_box"
                                    style="display:none; padding: 0px !important;">
                                    <span>
                                        <Input type="number" name="number_of_post_schedule" id="number_of_post_schedule"
                                            style=" padding: 8px 23px !important;" Placeholder="Number of post">
                                        <select class="schedule_frequency" name="schedule_frequency"
                                            style="padding: 8px 20px !important;">
                                            <option value="per_day" selected>Per Day</option>
                                            <option value="per_week">Per Week</option>
                                        </select></span>
                                    <p id="error_number_of_post_schedule" style="color: red;"></p>
                                </div>
                            </div>
                        </div>

                        <h2 style="padding-left:20px;">Define Author Settings</h2>
                        <div class="radio-container_multi seps-six-col_multi">
                            <div class="groupAuthor-box_multi">
                                <div class="improve-seo-form-global_multi gap30_multi">
                                    <div class="seo-form-field_multi">
                                        <label><input type="radio" name="assigning_authors" value="assigning_authors"
                                                id="assigning_authors">&nbsp;&nbsp;Assign all posts of this project to
                                            one author
                                        </label>

                                    </div>
                                    <div class="seo-form-field_multi">
                                        <!-- <div class="input-groupAuthor_multi"> -->
                                        <div id="author_number" style="display:none">
                                            <?php echo $all_auths; ?>
                                        </div>
                                        <div id="author_number" style="display:none">
                                            <?php echo $all_auths_to_send; ?>
                                        </div>
                                        <!-- <input style="border: none !important;" type="text"
                                                placeholder="Add Author Name">
                                            <button
                                                class="groupAuthor-add-btn_multi"><span>+</span><span>Add</span></button> -->
                                        <!-- </div> -->
                                    </div>
                                </div>
                                <div class="improve-seo-form-global_multi">
                                    <div class="seo-form-field_multi only_for_step_6_radio"
                                        style="min-width: 100% !important; display: flex !important; column-gap: 20px;">
                                        <label class=" max_width_70%_ "> <input type="radio" name="assigning_authors"
                                                value="assigning_multi_authors" id="assigning_multi_authors">
                                            &nbsp;&nbsp;Assign all
                                            posts of this project to a number of authors and distribute them evenly
                                        </label>
                                        <div id="authors_number" style="display:none">
                                            <input type="number"
                                                style="border: 1px solid #D2D2D2 !important;  padding: 8px 23px !important;"
                                                name="authors_number" min="1" max="100" placeholder="Number of Authors">

                                        </div>
                                    </div>
                                    <div>
                                        <img src="<?php echo WT_URL . '/assets/images/latest-images/nfo-filledss.svg' ?>"
                                            alt="info">
                                    </div>
                                    <!-- <div class="seo-form-field_multi">
                                        <div class="input-groupAuthor_multi">
                                            <input type="text" placeholder="Add Author Name">
                                            <button class="groupAuthor-add-btn_multi">+ Add</button>
                                        </div>
                                    </div> -->
                                </div>
                            </div>
                        </div>

                        <h2 style="padding-left:20px;">Choose or Create Category</h2>
                        <div class="category-box_multi">
                            <div class="cta-check_multi clearfix ">
                                <?php echo $select_to_send; ?>
                            </div>
                            <div class="add_cat">
                                <form method="post" action="add_category_form" class="form-wrap m-0">
                                    <div class="add-category">
                                        <input type="text" class="form-control" style="padding:20px !important;"
                                            name="cat_name_1" placeholder="Write Your Category" value=""
                                            aria-label="default input example" id="add_category_1">
                                        <input type="button"
                                            class="btn-trans btn btn-outline-primary btn-lg px-5 mx-auto"
                                            onclick="addcategory()" value="Add Category">
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 7 Content -->
                <div class="data_multi height-adjusts_multi">
                    <div class="bulk-widths1170_multi">
                        <div class="improve-seo-form-global_multi improve-seo-form-global_multi_step7">
                            <div class="seo-form-field_multi">
                                <label class="data-label_multi " style="padding: 0px 20px !important;"
                                    for="project_name">Project Name</label>
                                <input type="text" style=" padding: 10px 20px !important;" class="form-control"
                                    id="project_name" name="project_name" placeholder="Enter project name">
                            </div>
                            <div class="seo-form-field_multi">
                                <label class="data-label_multi" style="padding: 0px 20px !important;"
                                    for="notify_email">Email address for
                                    notification</label>
                                <input type="text" style=" padding: 10px 20px !important;" class="form-control"
                                    id="notify_email" name="notify_email" placeholder="Enter your email here">
                            </div>
                            <input type="submit" value="Submit" id="bulk_ai_post_submi_button">
                            <div class="seo-form-field_multi" style="padding-left: 20px;">
                                <p class="font-20_multi"><strong>Note:</strong> Based on the selections you made, this
                                    project will generate [ <span id="keywordcounts"></span> ] AI posts. This will take
                                    approximately [ <span id="keywordtime"></span> hr ] to
                                    complete.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <div class="btn-dev_multi">
            <button id="prevStepButton_multi"><img
                    src="<?php echo WT_URL . '/assets/images/latest-images/ep_arrow-left.svg' ?>" alt="prev">
                Previous</button>
            <button id="nextStepButton_multi" class="style_next_button_in_popup">Next <img
                    src="<?php echo WT_URL . '/assets/images/latest-images/ep_arrow-rights.svg' ?>" alt="next"></button>
        </div>

    </div>
</div>




<script>
    document.addEventListener("DOMContentLoaded", () => {
        let currentStep = 0;

        const nextStepButton = document.getElementById("nextStepButton");
        const prevStepButton = document.getElementById("prevStepButton");
        const close_single_post = document.getElementById("close_single_post");
        const close_generate_ai_popup = document.getElementById("close_generate_ai_popup");
        const close_bulk_post = document.getElementById("close_bulk_post");
        const generate_ai_popup_open = document.getElementById("generate_ai_popup_open");
        const open_single_AI_Post_popup = document.querySelectorAll(".open_single_AI_Post_popup");
        const open_bulk_AI_Post_popup = document.querySelectorAll(".open_bulk_AI_Post_popup");

        // Close Single Post Modal
        if (close_single_post) {
            close_single_post.addEventListener('click', () => {
                document.getElementById("exampleModal1").classList.add("hide_and_show_ai_popup");
            });
        }

        // Close Bulk Post Modal
        if (close_bulk_post) {
            close_bulk_post.addEventListener('click', () => {
                document.getElementById("exampleModal2").classList.add("hide_and_show_ai_popup");
            });
        }

        // Close Generate AI Popup Modal
        if (close_generate_ai_popup) {
            close_generate_ai_popup.addEventListener('click', () => {
                document.getElementById("exampleModal").classList.add("hide_and_show_ai_popup");
            });
        }

        // Open Generate AI Popup Modal
        if (generate_ai_popup_open) {
            generate_ai_popup_open.addEventListener('click', () => {
                document.getElementById("exampleModal").classList.remove("hide_and_show_ai_popup");
            });
        }

        // Open Single AI Post Popup Modal
        open_single_AI_Post_popup.forEach(element => {
            element.addEventListener('click', () => {
                document.getElementById("exampleModal1").classList.remove("hide_and_show_ai_popup");
            });
        });

        // Open Bulk AI Post Popup Modal
        open_bulk_AI_Post_popup.forEach(element => {
            element.addEventListener('click', () => {
                document.getElementById("exampleModal2").classList.remove("hide_and_show_ai_popup");
            });
        });







        function updateSteps() {
            document.querySelectorAll(".percent").forEach((step, index) => {
                step.classList.remove("completed", "active");
                if (index < currentStep) {
                    step.classList.add("completed");
                } else if (index === currentStep) {
                    step.classList.add("active");
                }
            });
        }



        nextStepButton.addEventListener("click", () => {
            if (currentStep < document.querySelectorAll(".percent").length) {



                prevStepButton.disabled = false;


            }
        });



        prevStepButton.addEventListener("click", () => {
            if (currentStep > 0) {

                nextStepButton.disabled = false;

                if (currentStep === 0) {
                    prevStepButton.disabled = true;
                }
            }
        });

        updateSteps();
        prevStepButton.disabled = true;
    });






    // document.addEventListener("DOMContentLoaded", () => {
    //     // DOM Elements
    //     const data = document.querySelectorAll('.data');
    //     const nextStepButton = document.getElementById("nextStepButton");
    //     const prevStepButton = document.getElementById("prevStepButton");
    //     const approve_content_check_box = document.getElementById('checkbox_need');
    //     const mainTitleArea = document.getElementById('maintitlearea');
    //     const forApproveValidation = document.getElementById('for_approve_content_validation');
    //     const stepInput = document.getElementById('step_value');

    //     // State Management
    //     let currentStep = 0;

    //     // Initialize the form
    //     initForm();

    //     // Core Functions
    //     function initForm() {
    //         updateDataDisplay();
    //         updateSteps();
    //         prevStepButton.disabled = true;
    //         nextStepButton.disabled = false; // Enabled by default

    //         // Special case for step 1 validation
    //         if (currentStep === 0) {
    //             nextStepButton.disabled = false;
    //         }
    //     }

    //     function updateDataDisplay() {
    //         data.forEach((item, index) => {
    //             item.classList.remove('dataJS');
    //             if (index === currentStep) item.classList.add('dataJS');
    //         });
    //     }

    //     function updateSteps() {
    //         document.querySelectorAll(".percent").forEach((step, index) => {
    //             step.classList.remove("completed", "active");
    //             if (index < currentStep) step.classList.add("completed");
    //             else if (index === currentStep) step.classList.add("active");
    //         });
    //     }

    //     function validateCheckbox(showError = false) {
    //         if (approve_content_check_box.checked) {
    //             resetValidationUI();
    //             return true;
    //         } else {
    //             if (showError) showValidationError();
    //             return false;
    //         }
    //     }

    //     function resetValidationUI() {
    //         mainTitleArea.style.border = '1px solid #D2D2D2';
    //         forApproveValidation.style.display = 'none';
    //     }

    //     function showValidationError() {
    //         mainTitleArea.style.border = '2px solid red';
    //         forApproveValidation.style.display = "block";
    //     }

    //     function updateButtonText() {
    //         const stepValue = parseInt(stepInput.value, 10);
    //         let buttonText = 'Next';

    //         if (stepValue === 3) {
    //             buttonText = 'Generate AI Post';
    //         } else if (stepValue === 4) {
    //             buttonText = 'Approve Content';
    //             nextStepButton.disabled = false;
    //             jQuery("#generateapivalue").trigger("click");
    //         } else if (stepValue === 5) {
    //             buttonText = 'Submit';
    //             nextStepButton.disabled = false;
    //         } else if (stepValue === 6) {
    //             saveFinalData();
    //             nextStepButton.disabled = true;
    //         }

    //         nextStepButton.innerHTML = `${buttonText} <img src="<?php echo WT_URL . '/assets/images/latest-images/ep_arrow-rights.svg' ?>" alt="arrow-right">`;
    //     }

    //     // Event Handlers
    //     approve_content_check_box.addEventListener('change', () => {
    //         if (currentStep === 0) {
    //             validateCheckbox(true);
    //             nextStepButton.disabled = !approve_content_check_box.checked;
    //         }
    //     });

    //     nextStepButton.addEventListener("click", () => {
    //         if (currentStep === 0 && !validateCheckbox(true)) {
    //             return;
    //         }

    //         if (currentStep >= data.length) return;

    //         // Step-specific validation

    //         // Progress to next step
    //         currentStep++;
    //         updateDataDisplay();
    //         updateSteps();

    //         // Update step tracking
    //         const stepValue = parseInt(stepInput.value, 10);
    //         if (!isNaN(stepValue)) {
    //             stepInput.value = stepValue + 1;
    //             updateButtonText();
    //         }

    //         // Enable previous button
    //         prevStepButton.disabled = false;
    //     });

    //     prevStepButton.addEventListener("click", () => {
    //         if (currentStep <= 0) return;

    //         // Go back to previous step
    //         currentStep--;
    //         updateDataDisplay();
    //         updateSteps();
    //         resetValidationUI();

    //         // Update step tracking
    //         const stepValue = parseInt(stepInput.value, 10);
    //         if (!isNaN(stepValue)) {
    //             stepInput.value = stepValue - 1;
    //             updateButtonText();
    //         }

    //         // Update button states
    //         nextStepButton.disabled = false;
    //         prevStepButton.disabled = (currentStep === 0);
    //     });
    // });






    // document.addEventListener("DOMContentLoaded", () => {
    //     // DOM Elements
    //     const data = document.querySelectorAll('.data');
    //     const nextStepButton = document.getElementById("nextStepButton");
    //     const prevStepButton = document.getElementById("prevStepButton");
    //     const approve_content_check_box = document.getElementById('checkbox_need');
    //     const mainTitleArea = document.getElementById('maintitlearea');
    //     const forApproveValidation = document.getElementById('for_approve_content_validation');
    //     const stepInput = document.getElementById('step_value');
    //     const seedSelect = document.getElementById("seed_select");

    //     // State Management
    //     let currentStep = 0;

    //     // Initialize the form
    //     initForm();

    //     // Core Functions
    //     function initForm() {
    //         updateDataDisplay();
    //         updateSteps();
    //         prevStepButton.disabled = true;
    //         updateNextButtonState();
    //     }

    //     function updateNextButtonState() {
    //         if (currentStep === 0) {
    //             if (seedSelect && seedSelect.value === "seed_option1") {
    //                 // Always enable for seed_option1
    //                 nextStepButton.disabled = false;
    //             } else {
    //                 // For other options, check checkbox state
    //                 nextStepButton.disabled = !approve_content_check_box.checked;
    //                 // Show validation error if needed
    //                 if (!approve_content_check_box.checked) {
    //                     showValidationError();
    //                 } else {
    //                     resetValidationUI();
    //                 }
    //             }
    //         } else {
    //             // For steps other than 0, always enable
    //             nextStepButton.disabled = false;
    //         }
    //     }

    //     function updateDataDisplay() {
    //         data.forEach((item, index) => {
    //             item.classList.remove('dataJS');
    //             if (index === currentStep) item.classList.add('dataJS');
    //         });
    //     }

    //     function updateSteps() {
    //         document.querySelectorAll(".percent").forEach((step, index) => {
    //             step.classList.remove("completed", "active");
    //             if (index < currentStep) step.classList.add("completed");
    //             else if (index === currentStep) step.classList.add("active");
    //         });
    //     }

    //     function validateCheckbox(showError = false) {
    //         if (seedSelect && seedSelect.value === "seed_option1") {
    //             return true;
    //         }

    //         if (approve_content_check_box.checked) {
    //             resetValidationUI();
    //             return true;
    //         } else {
    //             if (showError) showValidationError();
    //             return false;
    //         }
    //     }

    //     function resetValidationUI() {
    //         mainTitleArea.style.border = '1px solid #D2D2D2';
    //         forApproveValidation.style.display = 'none';
    //     }

    //     function showValidationError() {
    //         mainTitleArea.style.border = '2px solid red';
    //         forApproveValidation.style.display = "block";
    //     }

    //     function updateButtonText() {
    //         const stepValue = parseInt(stepInput.value, 10);
    //         let buttonText = 'Next';

    //         if (stepValue === 3) {
    //             buttonText = 'Generate AI Post';
    //         } else if (stepValue === 4) {
    //             buttonText = 'Approve Content';
    //             nextStepButton.disabled = false;
    //             jQuery("#generateapivalue").trigger("click");
    //         } else if (stepValue === 5) {
    //             buttonText = 'Submit';
    //             nextStepButton.disabled = false;
    //         } else if (stepValue === 6) {
    //             saveFinalData();
    //             nextStepButton.disabled = true;
    //         }

    //         nextStepButton.innerHTML = `${buttonText} <img src="<?php echo WT_URL . '/assets/images/latest-images/ep_arrow-rights.svg' ?>" alt="arrow-right">`;
    //     }

    //     // Event Handlers
    //     approve_content_check_box.addEventListener('change', () => {
    //         if (currentStep === 0) {
    //             updateNextButtonState();
    //         }
    //     });

    //     // Add seed select change handler
    //     if (seedSelect) {
    //         seedSelect.addEventListener("change", function () {
    //             if (currentStep === 0) {
    //                 if (this.value !== "seed_option1" && !approve_content_check_box.checked) {
    //                     showValidationError();
    //                 } else {
    //                     resetValidationUI();
    //                 }
    //                 updateNextButtonState();
    //             }
    //         });
    //     }

    //     nextStepButton.addEventListener("click", () => {
    //         if (currentStep === 0 && !validateCheckbox(true)) {
    //             return;
    //         }

    //         if (currentStep >= data.length) return;

    //         currentStep++;
    //         updateDataDisplay();
    //         updateSteps();

    //         const stepValue = parseInt(stepInput.value, 10);
    //         if (!isNaN(stepValue)) {
    //             stepInput.value = stepValue + 1;
    //             updateButtonText();
    //         }

    //         prevStepButton.disabled = false;
    //         updateNextButtonState();
    //     });

    //     prevStepButton.addEventListener("click", () => {
    //         if (currentStep <= 0) return;

    //         currentStep--;
    //         updateDataDisplay();
    //         updateSteps();
    //         resetValidationUI();

    //         const stepValue = parseInt(stepInput.value, 10);
    //         if (!isNaN(stepValue)) {
    //             stepInput.value = stepValue - 1;
    //             updateButtonText();
    //         }

    //         prevStepButton.disabled = (currentStep === 0);
    //         updateNextButtonState();
    //     });
    // });



    document.addEventListener("DOMContentLoaded", () => {
        // DOM Elements
        const data = document.querySelectorAll('.data');
        const nextStepButton = document.getElementById("nextStepButton");
        const prevStepButton = document.getElementById("prevStepButton");
        const approve_content_check_box = document.getElementById('checkbox_need');
        const mainTitleArea = document.getElementById('maintitlearea');
        const forApproveValidation = document.getElementById('for_approve_content_validation');
        const stepInput = document.getElementById('step_value');
        const seedSelect = document.getElementById("seed_select");

        // State Management
        let currentStep = 0;
        let hasInteracted = false; // Track if user has interacted with the form

        // Initialize the form
        initForm();

        // Core Functions
        function initForm() {
            updateDataDisplay();
            updateSteps();
            prevStepButton.disabled = true;
            resetValidationUI(); // Start with no validation errors
            updateNextButtonState();
        }

        function updateNextButtonState() {
            if (currentStep === 0) {
                if (seedSelect && seedSelect.value === "seed_option1") {
                    // Always enable for seed_option1
                    nextStepButton.disabled = false;
                } else {
                    // For other options, check checkbox state
                    // nextStepButton.disabled = !approve_content_check_box.checked;
                    // Only show validation error if user has interacted
                    if (hasInteracted && !approve_content_check_box.checked) {
                        showValidationError();
                    } else {
                        resetValidationUI();
                    }
                }
            } else {
                // For steps other than 0, always enable
                nextStepButton.disabled = false;
            }
        }

        function updateDataDisplay() {
            data.forEach((item, index) => {
                item.classList.remove('dataJS');
                if (index === currentStep) item.classList.add('dataJS');
            });
        }

        function updateSteps() {
            document.querySelectorAll(".percent").forEach((step, index) => {
                step.classList.remove("completed", "active");
                if (index < currentStep) step.classList.add("completed");
                else if (index === currentStep) step.classList.add("active");
            });
        }

        function validateCheckbox(showError = false) {
            if (seedSelect && seedSelect.value === "seed_option1") {
                return true;
            }

            if (approve_content_check_box.checked) {
                resetValidationUI();
                return true;
            } else {
                if (showError) {
                    hasInteracted = true; // Mark as interacted when validation fails
                    showValidationError();
                }
                return false;
            }
        }

        function resetValidationUI() {
            mainTitleArea.style.border = '1px solid #D2D2D2';
            forApproveValidation.style.display = 'none';
        }

        function showValidationError() {
            mainTitleArea.style.border = '2px solid red';
            forApproveValidation.style.display = "block";
        }

        function updateButtonText() {
            const stepValue = parseInt(stepInput.value, 10);
            let buttonText = 'Next';

            if (stepValue === 3) {
                buttonText = 'Generate AI Post';
            } else if (stepValue === 4) {
                buttonText = 'Approve Content';
                nextStepButton.disabled = false;
                jQuery("#generateapivalue").trigger("click");
            } else if (stepValue === 5) {
                buttonText = 'Submit';
                nextStepButton.disabled = false;
            } else if (stepValue === 6) {
                saveFinalData();
                nextStepButton.disabled = true;
            }

            nextStepButton.innerHTML = `${buttonText} <img src="<?php echo WT_URL . '/assets/images/latest-images/ep_arrow-rights.svg' ?>" alt="arrow-right">`;
        }

        // Event Handlers
        approve_content_check_box.addEventListener('change', () => {
            hasInteracted = true; // Mark as interacted when checkbox changes
            if (currentStep === 0) {
                updateNextButtonState();
            }
        });

        // Add seed select change handler
        if (seedSelect) {
            seedSelect.addEventListener("change", function () {
                hasInteracted = true; // Mark as interacted when seed changes
                if (currentStep === 0) {
                    if (this.value !== "seed_option1" && !approve_content_check_box.checked) {
                        showValidationError();
                    } else {
                        resetValidationUI();
                    }
                    updateNextButtonState();
                }
            });
        }

        nextStepButton.addEventListener("click", () => {
            if (currentStep === 0 && !validateCheckbox(true)) {
                showValidationError();
                return;
            }

            if (currentStep >= data.length) return;

            currentStep++;
            updateDataDisplay();
            updateSteps();

            const stepValue = parseInt(stepInput.value, 10);
            if (!isNaN(stepValue)) {
                stepInput.value = stepValue + 1;
                updateButtonText();
            }

            prevStepButton.disabled = false;
            updateNextButtonState();
        });

        prevStepButton.addEventListener("click", () => {
            if (currentStep <= 0) return;

            currentStep--;
            updateDataDisplay();
            updateSteps();
            resetValidationUI();

            const stepValue = parseInt(stepInput.value, 10);
            if (!isNaN(stepValue)) {
                stepInput.value = stepValue - 1;
                updateButtonText();
            }

            prevStepButton.disabled = (currentStep === 0);
            updateNextButtonState();
        });
    });




    document.querySelectorAll('input[name="imageOption"]').forEach((radio) => {
        radio.addEventListener('change', function () {
            document.querySelectorAll('.ai-image-content').forEach(div => div.classList.remove('active'));
            document.getElementById(this.value).classList.add('active');
        });
    });

    // Select option display setup

</script>
<!-- <script>
    document.addEventListener("DOMContentLoaded", () => {
        let currentStep = 0;
        const steps = document.querySelectorAll(".percent_multi");
        const dataSections = document.querySelectorAll(".data_multi");
        const prevButton = document.getElementById("prevStepButton_multi");
        const nextButton = document.getElementById("nextStepButton_multi");

        function updateSteps() {
            steps.forEach((step, index) => {
                step.classList.remove("active_multi", "completed_multi");
                if (index < currentStep) {
                    step.classList.add("completed_multi");
                } else if (index === currentStep) {
                    step.classList.add("active_multi");
                }
            });
        }

        function updateDataDisplay() {
            dataSections.forEach((section, index) => {
                section.classList.remove("dataJSmulti_multi");
                if (index === currentStep) {
                    section.classList.add("dataJSmulti_multi");
                }
            });
        }
        function updateButtonText() {
            let buttonText = 'Next';
            const totalSteps = steps.length;

            if (currentStep === totalSteps - 1) {
                buttonText = 'Submit';
                console.log(totalSteps);
            } else if (currentStep === 8) {
                jQuery("#bulk_ai_post_submi_button").trigger("click");
            } else {
                buttonText = 'Next';
            }

            nextButton.innerHTML = `${buttonText} <img src="<?php echo WT_URL . '/assets/images/latest-images/ep_arrow-rights.svg' ?>" alt="arrow-right">`;
        }

        nextButton.addEventListener("click", () => {
            if (currentStep < steps.length - 1) {
                currentStep++;
                updateSteps();
                updateDataDisplay();
                updateButtonText();
                prevButton.disabled = false;
                // if (currentStep === steps.length) {
                //     nextButton.disabled = true;
                // }
            }
        });

        prevButton.addEventListener("click", () => {
            if (currentStep > 0) {
                currentStep--;
                updateSteps();
                updateDataDisplay();
                updateButtonText();
                nextButton.disabled = false;
                if (currentStep === 0) {
                    prevButton.disabled = true;
                }
            }
        });

        updateSteps();
        updateDataDisplay();
        prevButton.disabled = true;
    });

</script> -->
<!-- forimage generaton in single post -->
<script>
    document.addEventListener("DOMContentLoaded", () => {
        let currentStep = 0;
        const steps = document.querySelectorAll(".percent_multi");
        const dataSections = document.querySelectorAll(".data_multi");
        const prevButton = document.getElementById("prevStepButton_multi");
        const nextButton = document.getElementById("nextStepButton_multi");

        function updateSteps() {
            steps.forEach((step, index) => {
                step.classList.remove("active_multi", "completed_multi");
                if (index < currentStep) {
                    step.classList.add("completed_multi");
                } else if (index === currentStep) {
                    step.classList.add("active_multi");
                }
            });
        }

        function updateDataDisplay() {
            dataSections.forEach((section, index) => {
                section.classList.remove("dataJSmulti_multi");
                if (index === currentStep) {
                    section.classList.add("dataJSmulti_multi");
                }
            });
        }

        function updateButtonText() {
            let buttonText = 'Next';
            const totalSteps = steps.length;

            if (currentStep === totalSteps - 1) { // Last step (step 6)
                buttonText = 'Submit';
            } else if (currentStep === totalSteps - 2) { // Last step (step 6)
                var text = jQuery('#keyword_list').val();

                // Split the text into lines
                var lines = text.split('\n');

                // Filter out empty lines
                var nonEmptyLines = lines.filter(function (line) {
                    return line.trim().length > 0;
                });

                // Count the non-empty lines
                var keywordCount = nonEmptyLines.length;

                // Calculate the minimum time and format it in minutes
                var keywordMin = keywordCount * 3;

                // Convert the time to hours and format to two decimal places
                var keywordTime = (keywordMin / 60).toFixed(2);

                // Update the text in the elements
                jQuery('#keywordcounts').text(keywordCount);
                jQuery('#keywordtime').text(keywordTime);
            }

            nextButton.innerHTML = `${buttonText} <img src="<?php echo WT_URL . '/assets/images/latest-images/ep_arrow-rights.svg' ?>" alt="arrow-right">`;
        }

        nextButton.addEventListener("click", () => {
            if (currentStep < steps.length - 1) { // Only go up to step 6
                currentStep++;
                updateSteps();
                updateDataDisplay();
                updateButtonText();
                prevButton.disabled = false;

            }
            else if (currentStep === steps.length - 1) { // When on last step
                // Trigger bulk submission
                jQuery("#bulk_ai_post_submi_button").trigger("click");
                nextButton.disabled = true;
                nextButton.innerHTML = `Processing... <img src="<?php echo WT_URL . '/assets/images/latest-images/ep_arrow-rights.svg' ?>" alt="arrow-right">`;
            }
        });

        prevButton.addEventListener("click", () => {
            if (currentStep > 0) {
                currentStep--;
                updateSteps();
                updateDataDisplay();
                updateButtonText();
                nextButton.disabled = false;
                if (currentStep === 0) {
                    prevButton.disabled = true;
                }
            }
        });

        // Initialize
        updateSteps();
        updateDataDisplay();
        updateButtonText();
        prevButton.disabled = true;
    });
</script>
<script>
    document.querySelectorAll('.style_imgae_gereater_radio input[type="radio"]').forEach(input => {
        input.addEventListener('change', function () {
            // Remove active class from all labels
            document.querySelectorAll('.style_imgae_gereater_radio').forEach(label => {
                label.classList.remove('active');
            });

            // Add active class to the parent label of the checked input
            if (this.checked) {
                this.closest('.style_imgae_gereater_radio').classList.add('active');
            }
        });
    });

</script>
<script>
    document.querySelectorAll('.style_imgae_gereater_radio input[type="radio"]').forEach(input => {
        input.addEventListener('change', function () {
            // Remove active class from all labels
            document.querySelectorAll('.style_imgae_gereater_radio').forEach(label => {
                label.classList.remove('active');
            });

            // Add active class to the parent label of the checked input
            if (this.checked) {
                this.closest('.style_imgae_gereater_radio').classList.add('active');
            }
        });
    });

</script>
<script>
    document.querySelectorAll('.style_schedule_radio input[type="radio"]').forEach(input => {
        input.addEventListener('change', function () {
            // Remove active class from all labels
            document.querySelectorAll('.style_schedule_radio').forEach(label => {
                label.classList.remove('active');
            });

            // Add active class to the parent label of the checked input
            if (this.checked) {
                this.closest('.style_schedule_radio').classList.add('active');
            }
        });
    });
</script>
<script>
    // Select all radio buttons with the name "assigning_authors"
    document.querySelectorAll('input[name="assigning_authors"]').forEach(input => {
        input.addEventListener('change', function () {
            // Remove schdule_active class from all labels
            document.querySelectorAll('.seo-form-field_multi label').forEach(label => {
                label.classList.remove('schdule_active');
            });

            // Add schdule_active class to the parent label of the checked input
            if (this.checked) {
                this.closest('label').classList.add('schdule_active');
            }
        });
    });
</script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        document.getElementById("checkbox_need").addEventListener("change", function () {
            const label = document.querySelector(".step_one_approve_button");
            const labelText = label.querySelector(".label-text");
            const icon = label.querySelector(".icon");

            if (this.checked) {
                label.classList.add("approve_active");
                labelText.textContent = 'Approved';
                icon.style.display = 'block'; // Show the icon
            } else {
                label.classList.remove("approve_active");
                labelText.textContent = 'Approve';
                icon.style.display = 'none'; // Hide the icon
            }
        });
    });






    document.addEventListener("DOMContentLoaded", function () {
        const radioButtons = document.querySelectorAll('.show_hide_content_in_bulk_part_3');
        const aiImageElement = document.querySelector('#ai-image-generating');

        radioButtons.forEach(radio => {
            radio.addEventListener('change', function () {
                if (this.checked) {
                    aiImageElement.style.visibility = "visible";
                } else {
                    aiImageElement.style.visibility = "hidden";
                }
            });
        });
    });
</script>
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