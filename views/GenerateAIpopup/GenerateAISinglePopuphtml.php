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
        display: none;
        position: fixed;
        width: 100%;
        height: 100%;
        left: 0;
        z-index: 999;
        background: rgb(255, 255, 255) url("<?php echo $image_url1; ?>") center no-repeat;
    }
    .overlay2 {
        display: none;
        position: fixed;
        width: 100%;
        height: 100%;
        left: 0;
        z-index: 999;
        background: rgb(255, 255, 255) url("<?php echo $image_url2; ?>") center no-repeat;
    }
    .overlay_ai_data {
        display: none;
        position: fixed;
        width: 100%;
        height: 100%;
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
        border: 1px solid #bfbfbf;
        padding: 5px;
        align-items: center;
        width: 100%;
        border-radius: 5px;
        background: #d3d3d3;
        margin-right: 10px;
    }
    #langerror {
        color: #f00;
    }
    .multi-upload-gallery span {
        display: block;
    }
    ul.multi-upload-gallery.ui-sortable {
        list-style: none;
        padding: 0;
    }
    a.multi-upload-gallery-remove {
        color: red;
        text-decoration: none;
    }
</style>

<!-- Direct Single AI Post Modal (Skip the initial selection) -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">

    <div id="loadingImage" style="display:none ;" class="overlay2">
        <!-- <img src="' . home_url('/') . 'wp-content/plugins/jobseq_jobs_pugin/assets/image/loader.gif" alt="Loading..."> -->
    </div>
    <div id="loadingAIData" style="display:none;" class="overlay_ai_data"></div>

    <div id="loadingAIImage" style="display:none;" class="overlay_ai_image"></div>

    <div class="improveseo-bulk-ai">
        <div class="singlepost-title">
            <h1><img src="<?php echo WT_URL . '/assets/images/latest-images/iconoir_sparks-solid.svg' ?>"
                    alt="iconoir_sparks"> Create Single AI Post</h1>
            <div class="singlepost-close"><img id="close_single_post_direct"
                    src="<?php echo WT_URL . '/assets/images/latest-images/akar-icons_cross.svg' ?>" alt="icons_cross">
            </div>
        </div>
        <form id="pop_up_form" action="singlepost_form_submit" method="post" class="pop_up_form">
            <div class="steps">
                <div class="line-step">
                    <!-- Step 1 -->
                    <div class="percent active">
                        <div class="step">
                            <div class="circle">1</div>
                            <p>Keyword & <br>Post Title</p>
                        </div>
                    </div>
                    <!-- Step 2 -->
                    <div class="percent">
                        <div class="step">
                            <div class="circle">2</div>
                            <p>Content <br>Settings</p>
                        </div>
                    </div>
                    <!-- Step 3 -->
                    <div class="percent">
                        <div class="step">
                            <div class="circle">3</div>
                            <p>Add <br>Media</p>
                        </div>
                    </div>
                    <!-- Step 4 -->
                    <div class="percent">
                        <div class="step">
                            <div class="circle">4</div>
                            <p>SEO <br>Visuals</p>
                        </div>
                    </div>
                    <!-- Step 5 -->
                    <div class="percent">
                        <div class="step">
                            <div class="circle">5</div>
                            <p>Meta Title & <br>Description</p>
                        </div>
                    </div>
                    <!-- Step 6 -->
                    <div class="percent">
                        <div class="step">
                            <div class="circle">6</div>
                            <p>Publish <br>Settings</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="improveseo-sections">
                <!-- Step 1 Content -->
                <div class="data dataJS">
                    <div class="bulk-widths1170">
                        <div class="improve-seo-form-global">
                            <div class="form-group" style="display: flex; gap: 20px; margin-bottom: 20px;">
                                <div style="flex: 1;">
                                    <input type="radio" id="seed_keyword_radio" name="keyword_selection" value="seed" checked>
                                    <label for="seed_keyword_radio" style="margin-left: 8px;">Seed Keyword</label>
                                </div>
                                <div style="flex: 1;">
                                    <input type="radio" id="select_existing_radio" name="keyword_selection" value="select_existing">
                                    <label for="select_existing_radio" style="margin-left: 8px;">Select from keyword list</label>
                                </div>
                            </div>

                            <!-- Seed Keyword Section -->
                            <div id="seed_section" class="keyword-section">
                                <div class="form-group">
                                    <label for="seed_keyword">Enter Seed Keyword</label>
                                    <textarea id="seed_keyword" name="seed_keyword" class="form-control" rows="3"
                                        placeholder="Enter your seed keyword here..."
                                        style="resize: none; width: 100%; padding: 15px;"></textarea>
                                    <span id="error_seed_keyword" style="color: red;"></span>
                                </div>

                                <div class="form-group">
                                    <label for="seed_select">Select Title Type</label>
                                    <select id="seed_select" name="seed_options" class="form-control"
                                        style="width: 100%; padding: 15px;">
                                        <option value="">Select Title Type</option>
                                        <option value="seed_option1">USE KEYWORD AS IS IN TITLE [A.I. will build content]</option>
                                        <option value="seed_option2">CREATE BEST TITLE FROM KEYWORD [A.I. will choose/build content]</option>
                                        <option value="seed_option3">CREATE BEST QUESTION FROM KEYWORD [A.I. will choose/build content]</option>
                                    </select>
                                    <span id="error_seed_select" style="color: red;"></span>
                                </div>

                                <div class="form-group">
                                    <label for="content_type">Tone of Voice</label>
                                    <select id="content_type" name="content_type" class="form-control"
                                        style="width: 100%; padding: 15px;">
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
                                    <span id="error_content_type" style="color: red;"></span>
                                </div>

                                <div id="generated_title_section" style="display: none; margin-top: 20px;">
                                    <label>
                                        <input type="checkbox" id="checkbox_need" />
                                        <span id="maintitlearea" style="border: 1px solid #D2D2D2; padding: 10px; display: inline-block; min-width: 300px; margin-left: 10px;"></span>
                                        <button type="button" id="reload_title" style="margin-left: 10px;">🔄</button>
                                    </label>
                                    <input type="hidden" name="aigeneratedtitle" id="aigeneratedtitle" />
                                    <div id="for_approve_content_validation" style="display: none; color: red; margin-top: 10px;">
                                        Please approve the generated title to proceed.
                                    </div>
                                </div>
                            </div>

                            <!-- Existing Keyword Section -->
                            <div id="existing_section" class="keyword-section" style="display: none;">
                                <div class="form-group">
                                    <label for="keyword_list_name">Project Name</label>
                                    <select id="keyword_list_name" name="keyword_list_name" class="form-control"
                                        style="width: 100%; padding: 15px;">
                                        <option value="">Select a project</option>
                                        <option value="create_new_project">Create New KW List</option>
                                        <?php echo $html_key_to_send; ?>
                                    </select>
                                </div>

                                <div class="form-group" id="keyword_list_container" style="display: none;">
                                    <label for="keyword_list">Keywords</label>
                                    <textarea id="keyword_list" name="keyword_list" class="form-control" rows="10"
                                        style="width: 100%;"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add other steps here similar to the original structure -->
                <!-- Step 2: Content Settings -->
                <div class="data">
                    <div class="bulk-widths1170">
                        <div class="improve-seo-form-global">
                            <div class="form-group">
                                <label for="post_size">Article Size</label>
                                <select id="post_size" name="nos_of_words" class="form-control" style="width: 100%; padding: 15px;">
                                    <option value="">Select Article Size</option>
                                    <option value="600-1200">600-1200 words</option>
                                    <option value="1200-2000">1200-2000 words</option>
                                    <option value="2000-3000">2000-3000 words</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="point_of_view">Point of View</label>
                                <select id="point_of_view" name="point_of_view" class="form-control" style="width: 100%; padding: 15px;">
                                    <option value="none">None</option>
                                    <option value="First person singular (I,me,my,mine)">First person singular (I,me,my,mine)</option>
                                    <option value="First person plural (we,us,our,ours)">First person plural (we,us,our,ours)</option>
                                    <option value="Second Person (you,your,yours)">Second Person (you,your,yours)</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="language">Select Language</label>
                                <select id="language" name="content_lang" class="form-control" style="width: 100%; padding: 15px;">
                                    <option value="">-Select Language-</option>
                                    <option value="english_us">English (US)</option>
                                    <option value="english_uk">English (UK)</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="details_to_include">Details to Include</label>
                                <textarea id="details_to_include" name="details_to_include" class="form-control" rows="3"
                                    placeholder="Enter specific details to include in the content..."></textarea>
                            </div>

                            <div class="form-group">
                                <label for="call_to_action">Call to Action</label>
                                <textarea id="call_to_action" name="call_to_action" class="form-control" rows="3"
                                    placeholder="Enter your call to action..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Add Media -->
                <div class="data">
                    <div class="bulk-widths1170">
                        <div class="improve-seo-form-global">
                            <div class="form-group">
                                <label>Image Options</label>
                                <div style="margin: 20px 0;">
                                    <input type="radio" id="ai_image_auto" name="aiImage" value="ai_auto">
                                    <label for="ai_image_auto" style="margin-left: 8px;">Generate AI Image Based On Title</label>
                                </div>
                                <div style="margin: 20px 0;">
                                    <input type="radio" id="manual_image" name="aiImage" value="Manually_image">
                                    <label for="manual_image" style="margin-left: 8px;">Manually Upload Image</label>
                                </div>
                                <div style="margin: 20px 0;">
                                    <input type="radio" id="ai_image_prompt" name="aiImage" value="manually_promt_image">
                                    <label for="ai_image_prompt" style="margin-left: 8px;">Generate AI Image - Edit Prompt</label>
                                </div>
                            </div>

                            <div id="ai_image_div" style="display:none;">
                                <div id="ai-image-display"></div>
                                <input type="hidden" id="AI-Image-uploaded-path" name="AI-Image-uploaded-path">
                            </div>

                            <div id="manual_image_div" style="display:none;">
                                <input type="file" id="upload-image-button" name="Manually_image">
                                <div id="manually-image-display"></div>
                                <input type="hidden" id="manually-image-uploaded-path" name="manually-image-uploaded-path">
                            </div>

                            <div id="prompt_image_div" style="display:none;">
                                <textarea id="manually_promt_for_image" name="manually_promt_for_image" class="form-control" rows="3"
                                    placeholder="Enter your image generation prompt..."></textarea>
                                <button type="button" id="generate_i_image" class="btn btn-primary" style="margin-top: 10px;">Generate Image</button>
                                <div id="ai-with-prompt-image-display"></div>
                                <input type="hidden" id="AI-Prompt-Image-uploaded-path" name="AI-Prompt-Image-uploaded-path">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 4: SEO Visuals -->
                <div class="data">
                    <div class="bulk-widths1170">
                        <div class="improve-seo-form-global">
                            <h3>Add Shortcodes (Optional)</h3>
                            <div id="shortcode_selection">
                                <?php echo isset($AllShortCode_to_send) ? $AllShortCode_to_send : ''; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 5: Meta Title & Description -->
                <div class="data">
                    <div class="bulk-widths1170">
                        <div class="improve-seo-form-global">
                            <div class="form-group">
                                <label for="meta_title">Meta Title</label>
                                <input type="text" id="meta_title" name="meta_title" class="form-control"
                                    placeholder="Enter meta title...">
                            </div>

                            <div class="form-group">
                                <label for="meta_description">Meta Description</label>
                                <textarea id="meta_description" name="meta_description" class="form-control" rows="3"
                                    placeholder="Enter meta description..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 6: Publish Settings -->
                <div class="data">
                    <div class="bulk-widths1170">
                        <div class="improve-seo-form-global">
                            <div class="form-group">
                                <label>Categories</label>
                                <div><?php echo $select_to_send; ?></div>
                            </div>

                            <div class="form-group">
                                <label for="author_name">Author</label>
                                <?php echo $all_auths_to_send; ?>
                            </div>

                            <div class="form-group">
                                <label for="publish_status">Publish Status</label>
                                <select id="publish_status" name="publish_status" class="form-control">
                                    <option value="draft">Draft</option>
                                    <option value="publish">Publish Immediately</option>
                                    <option value="future">Schedule</option>
                                </select>
                            </div>

                            <div id="ai_content_display" style="margin-top: 30px;">
                                <h3>Generated Content Preview</h3>
                                <div id="showmydataindiv1" style="border: 1px solid #ccc; padding: 15px; min-height: 200px; background: #f9f9f9;">
                                    Content will appear here after generation...
                                </div>
                                <input type="hidden" name="ai_title" id="ai_title" />
                                <input type="hidden" name="AI_Title" id="AI_Title">
                                <input type="hidden" name="AI_descreption" id="AI_descreption">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <input type="hidden" id="step_value" value="1">
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

<script>
// Add the step navigation logic
document.addEventListener("DOMContentLoaded", () => {
    // DOM Elements
    const data = document.querySelectorAll('.data');
    const nextStepButton = document.getElementById("nextStepButton");
    const prevStepButton = document.getElementById("prevStepButton");
    const stepInput = document.getElementById('step_value');
    
    // Radio button handling
    const seedRadio = document.getElementById('seed_keyword_radio');
    const existingRadio = document.getElementById('select_existing_radio');
    const seedSection = document.getElementById('seed_section');
    const existingSection = document.getElementById('existing_section');
    
    // Image option handling
    const imageRadios = document.querySelectorAll('input[name="aiImage"]');
    const imageDevs = {
        'ai_auto': document.getElementById('ai_image_div'),
        'Manually_image': document.getElementById('manual_image_div'),
        'manually_promt_image': document.getElementById('prompt_image_div')
    };

    // State Management
    let currentStep = 0;

    // Initialize the form
    initForm();

    // Core Functions
    function initForm() {
        updateDataDisplay();
        updateSteps();
        prevStepButton.disabled = true;
        
        // Set up radio button listeners
        seedRadio.addEventListener('change', handleKeywordSelection);
        existingRadio.addEventListener('change', handleKeywordSelection);
        
        // Set up image option listeners
        imageRadios.forEach(radio => {
            radio.addEventListener('change', handleImageSelection);
        });
    }

    function handleKeywordSelection() {
        if (seedRadio.checked) {
            seedSection.style.display = 'block';
            existingSection.style.display = 'none';
        } else {
            seedSection.style.display = 'none';
            existingSection.style.display = 'block';
        }
    }

    function handleImageSelection() {
        // Hide all image divs
        Object.values(imageDevs).forEach(div => {
            if (div) div.style.display = 'none';
        });
        
        // Show selected div
        const selectedValue = document.querySelector('input[name="aiImage"]:checked')?.value;
        if (selectedValue && imageDevs[selectedValue]) {
            imageDevs[selectedValue].style.display = 'block';
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

    function updateButtonText() {
        const stepValue = parseInt(stepInput.value, 10);
        let buttonText = 'Next';

        if (stepValue === 6) {
            buttonText = 'Generate AI Post';
        } else if (stepValue === 7) {
            buttonText = 'Submit';
        }

        nextStepButton.innerHTML = `${buttonText} <img src="<?php echo WT_URL . '/assets/images/latest-images/ep_arrow-rights.svg' ?>" alt="arrow-right">`;
    }

    // Event Handlers
    nextStepButton.addEventListener("click", () => {
        if (currentStep >= data.length - 1) return;

        currentStep++;
        updateDataDisplay();
        updateSteps();

        const stepValue = parseInt(stepInput.value, 10);
        if (!isNaN(stepValue)) {
            stepInput.value = stepValue + 1;
            updateButtonText();
        }

        prevStepButton.disabled = false;
    });

    prevStepButton.addEventListener("click", () => {
        if (currentStep <= 0) return;

        currentStep--;
        updateDataDisplay();
        updateSteps();

        const stepValue = parseInt(stepInput.value, 10);
        if (!isNaN(stepValue)) {
            stepInput.value = stepValue - 1;
            updateButtonText();
        }

        prevStepButton.disabled = (currentStep === 0);
    });

    // Close button
    document.getElementById('close_single_post_direct')?.addEventListener('click', () => {
        document.getElementById('exampleModal').style.display = 'none';
        document.querySelector('.modal-backdrop')?.remove();
    });
});
</script>
