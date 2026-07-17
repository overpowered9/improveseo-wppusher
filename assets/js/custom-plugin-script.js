var $ = jQuery;

var ajaxUrl = standred_var.ajax_url;

// A 402 from the server means either "trial has ended" or plain "out of credits" — two
// different problems with two different fixes. The server sends its own message, so key
// off that rather than the bare status code, and tell the user which one they hit.
// Credits the user purchased keep working after the trial ends, hence the wording.
function improveseoIsTrialEnded(msg) {
  return typeof msg === 'string' && /trial has ended|trial ended/i.test(msg);
}

function improveseoShowCreditNotice(msg, kind) {
  var trialEnded = improveseoIsTrialEnded(msg);
  showImproveSEONotification(
    'warning',
    trialEnded ? 'Free Trial Ended' : 'Out of Credits',
    trialEnded
      ? 'Your free trial has ended. Upgrade your plan to keep generating — any credits you purchased are still usable.'
      : 'You are out of ' + (kind === 'content' ? 'content' : 'image') + ' generation credits. Please purchase more credits to continue.',
    'https://account.improveseoplugin.com/'
  );
}

// Starting template for the "Generate AI Image - Edit Prompt" textarea. The user edits this text
// and it is sent verbatim to the image model, so it must read as a clean, structured image prompt.
function buildEditableImagePrompt(subject) {
  return (
    "Cover photo for a blog article.\n\n" +
    "Subject: " + subject + "\n\n" +
    "Style: documentary photography, natural light, muted slightly desaturated colours, shot on a 50mm lens with shallow depth of field and faint film grain, candid and authentic real-world scene, not staged or studio.\n\n" +
    "Rules: no text, logos, signs or watermarks anywhere in the image; no studio lighting, neon, glow effects or digital overlays; show people only from behind, from the side or at a distance — or include no people at all."
  );
}

function getShortCodeDetails(value) {
  alert(value);

  var formData = new FormData();

  formData.append("action", "improveseo_get_shortcodes");

  formData.append("improveseo_shortcode_type", value);

  jQuery.ajax({
    url: ajaxurl,

    type: "POST",

    data: formData,

    contentType: false,

    processData: false,

    dataType: "json",

    success: function (response) {
      //alert(response);

      console.log(response);

      jQuery("#insertShortcodeDropdown").html(response.shortcode_html);

      // jQuery("#AI_image_div").html("<img src='"+response.data+"' alt='Uploaded Image' style='max-width: 100%'>");

      // jQuery("#image-uploaded-path").val(response.data);

      // jQuery("#loadingImage").hide();
    },

    error: function () {
      alert("Error uploading image.");

      jQuery("#loadingImage").hide();
    },
  });
}

function GenerateCustomImage() {
  jQuery("#loadingAIImage").show();

  var seed_select = jQuery("#seed_select").val();

  if (seed_select == "seed_option1") {
    var title = jQuery("#seed_keyword").val();
  } else {
    var maintitlearea = jQuery("#maintitlearea").val();

    if (maintitlearea == "") {
      var title = jQuery("#aigeneratedtitle").val();
    } else {
      var title = maintitlearea;
    }
  }

  // var title = jQuery("#Image_Prompt").val();

  var formData = new FormData();

  formData.append("action", "fetch_AI_image");

  formData.append("title", title);

  // Route the single-wizard cover image through the v2 (OpenAI) path with the selected niche.
  formData.append("niche", window.iseoSelectedNiche());

  formData.append("use_v2", "1");

  jQuery.ajax({
    url: ajaxurl,

    type: "POST",

    data: formData,

    contentType: false,

    processData: false,

    success: function (response) {
      // Check for insufficient credits error (HTTP 402)
      if (response.success === false) {
        jQuery("#loadingAIImage").hide();
        if (response.data && (response.data.includes('402') || response.data.includes('Insufficient') || response.data.includes('credits') || response.data.includes('trial'))) {
          improveseoShowCreditNotice(response.data, 'image');
        } else {
          // Show error notification for other failures (500, network, etc.)
          showImproveSEONotification(
            'error',
            'Image Generation Failed',
            'Unable to generate image. If you experience errors too often, please contact support.',
            'mailto:support@improveseoplugin.com?subject=Image%20Generation%20Error'
          );
        }
        return;
      }
      
      jQuery("#AI_image_div").html(
        "<img src='" +
          response.data +
          "' alt='Uploaded Image' style='max-width: 100%'>"
      );

      jQuery("#image-uploaded-path").val(response.data);

      jQuery("#loadingImage").hide();
    },

    error: function () {
      alert("Error uploading image.");

      jQuery("#loadingImage").hide();
    },
  });
}

jQuery("#generate_i_image").on("click", function () {
  jQuery("#loadingAIImage").show();
  jQuery("#hide_older_genrated_image_on_step3").hide();

  var seed_select = jQuery("#seed_select").val();

  if (seed_select == "seed_option1") {
    var seed_title = jQuery("#seed_keyword").val();
  } else {
    var maintitlearea = jQuery("#maintitlearea").val();

    if (maintitlearea == "") {
      var seed_title = jQuery("#aigeneratedtitle").val();
    } else {
      var seed_title = maintitlearea;
    }
  }

  var title = jQuery("#manually_promt_for_image").val();

  var formData = new FormData();

  formData.append("action", "fetch_AI_image");

  formData.append("title", title);

  formData.append("seed_title", seed_title);

  formData.append("noedit", 1);

  // Custom-prompt image also uses the v2 (OpenAI) path; the raw prompt is passed through verbatim.
  formData.append("niche", window.iseoSelectedNiche());

  formData.append("use_v2", "1");

  jQuery.ajax({
    url: ajaxurl,

    type: "POST",

    data: formData,

    contentType: false,

    processData: false,

    success: function (response) {
      // Check for insufficient credits error (HTTP 402)
      if (response.success === false) {
        jQuery("#loadingAIImage").hide();
        if (response.data && (response.data.includes('402') || response.data.includes('Insufficient') || response.data.includes('credits') || response.data.includes('trial'))) {
          improveseoShowCreditNotice(response.data, 'image');
        } else {
          // Show error notification for other failures (500, network, etc.)
          showImproveSEONotification(
            'error',
            'Image Generation Failed',
            'Unable to generate image. If you experience errors too often, please contact support.',
            'mailto:support@improveseoplugin.com?subject=Image%20Generation%20Error'
          );
        }
        return;
      }
      
      jQuery("#ai-with-prompt-image-display").html(
        "<img src='" +
          response.data +
          "' alt='Uploaded Image' style='max-width: 100%'>"
      );

      jQuery("#AI-Prompt-Image-uploaded-path").val(response.data);

      jQuery("#prompt_image_div").css("display", "block");

      jQuery("#generate_i_image").val("Regenerate Image");

      jQuery("#loadingAIImage").hide();
    },

    error: function () {
      alert("Error uploading image.");

      jQuery("#loadingAIImage").hide();
    },
  });
});

jQuery("#generateapivalue").on("click", function () {
  console.log("Debug Message when gen ai is clicked");
  jQuery("#loadingAIData").show();

  jQuery("#for_testing_only").css("display", "none");

  var AItitle = jQuery(".resultdata").text();

  jQuery("#ai_title").val(AItitle);

  console.log(AItitle);

  var form_inputValue = jQuery("#popup_form").serialize();
  console.log(form_inputValue);
  //  console.log(inputValue);

  jQuery.ajax({
    type: "POST", // or "GET" depending on your form method

    url: ajaxUrl,

    dataType: "json",

    data: { value: form_inputValue, action: "getaaldata" }, // Send the captured value to the server

    success: function (response) {
      // Handle the success response from the server

      // jQuery("#loadingImage").hide();

      console.log("response of generate single ai", response);

      // Check for insufficient credits error
      if (response.success === false || response.data.content?.includes('Error: Content generation server returned error status: 402')) {
        jQuery("#loadingAIData").hide();
        if (response.data.content?.includes('402') || response.data.content?.includes('Insufficient') || response.data.content?.includes('credits') || response.data.content?.includes('trial')) {
          improveseoShowCreditNotice(response.data.content, 'content');
        } else {
          showImproveSEONotification(
            'error',
            'Generation Error',
            response.data.content || 'An error occurred during content generation.',
            null
          );
        }
        return;
      }

      var searchData = response.data.search_data;

      var content = response.data.content;

      var meta_title = response.data.meta_title;

      var meta_descreption = response.data.meta_descreption;

      // Set the value of the input field

      jQuery("#title").val(searchData);

      //jQuery(".wp-editor-area").html(content);

      //jQuery("#tinymce").html(content);

      jQuery("#generateapi").css("display", "block");

      jQuery("#generateapivalue").val("Re-Generate AI Post");

      var AI_image = document.getElementById("AI_image"); //.val();

      var Manually_image = document.getElementById("Manually_image"); //.val();

      var manually_promt_image = document.getElementById(
        "manually_promt_image"
      ); //.val();

      var Image_use = "";

      if (AI_image.checked) {
        Image_use = jQuery("#ai-image-display").html();
      }

      if (Manually_image.checked) {
        Image_use = jQuery("#manually-image-display").html();
      }

      if (manually_promt_image.checked) {
        Image_use = jQuery("#ai-with-prompt-image-display").html();
      }

      Image_use = Image_use.replace(
        'style="max-width: 100%"',
        'style="max-width: 100%"'
      );

      // Get the HTML content of the element

      //var htmlContent = content;

      // Example usage

      //var textContent = "This is a sample text.\nThis is another line of text.";

      var htmlContent = textToHtml(content);

      //console.log(htmlContent);

      // Use a regular expression to replace ** with <h2> and **** with </h2>

      //     // Add line break if a hyphen is found in the content (for **)

      // modifiedHtmlContent = modifiedHtmlContent.replace(/\*\*(.*?)-(.*?)\*\*/g, '<h2 style="margin-top:50px">jQuery1<br>jQuery2</h2>');

      // // Add line break if a hyphen is found in the content (for ****)

      // modifiedHtmlContent = modifiedHtmlContent.replace(/\*\*\*\*(.*?)-(.*?)\*\*\*\*/g, '</h2>jQuery1<br>jQuery2<h2 style="margin-top:50px">');

      // var htmlContent1 = htmlContent.split('****').join('</h2>');

      // var htmlContent2 = htmlContent1.split('**').join('<h2 style="margin-top:50px">');

      // var modifiedHtmlContent = htmlContent2.split('-').join('<br>');

      // var modifiedHtmlContent0 = htmlContent.replace(/\*\*(.*?)\*\*/g, '<h2 style="margin-top:50px">jQuery1</h2>');

      // var modifiedHtmlContent1 = modifiedHtmlContent0.replace(/\*\*\*\*(.*?)\*\*\*\*/g, '</h2>jQuery1<h2 style="margin-top:50px">');

      var modifiedHtmlContent = htmlContent; //modifiedHtmlContent1.replace(/-(.*?)-/g, '<br>jQuery1<br>');

      // Update the HTML content of the element with the modified content

      jQuery("#showmydataindivText").val(
        Image_use + "<br><br><br>" + modifiedHtmlContent
      );

      var hasPreviewImage = jQuery.trim(Image_use) !== "";
      jQuery("#showmydataindiv1").html(
        hasPreviewImage
          ? '<div class="iseo-preview-layout">' +
              '<div class="iseo-preview-media">' + Image_use + "</div>" +
              '<div class="iseo-preview-body">' + modifiedHtmlContent + "</div>" +
            "</div>"
          : '<div class="iseo-preview-body">' + modifiedHtmlContent + "</div>"
      );

      jQuery("#showmydataindiv1").css("display", "block");

      jQuery("#showmydataindiv1").css("height", "700px");

      jQuery("#meta_title").val(meta_title);

      jQuery("#meta_descreption").val(meta_descreption);

      //tinymce.activeEditor.insertContent(content);

      // jQuery("#exampleModal").modal("hide");

      // alert(response.data.search_data);

      console.log(response.data.search_data);

      console.log(response.data.content);

      //jQuery( "#butn" ).trigger( "click" );

      jQuery("#on-page-seo").prop("checked", true);

      jQuery("#custom-title").val(meta_title);

      jQuery("#custom-description").val(meta_descreption);
      
      // Trigger keyup events to update Google Preview
      jQuery("#custom-title").trigger("keyup");
      jQuery("#custom-description").trigger("keyup");

      // // Convert plain text formatting to HTML equivalents

      // var htmlContent = plainTextContent.replace(/\n/g, '<br>');

      // jQuery("#showmydataindiv").html(htmlContent);

      jQuery("#showmydataindiv").css("display", "block");

      // Now you have the content of the textarea as HTML

      // console.log(htmlContent);

      // var htmlContent = convertToHtml(content);

      //tinymce.activeEditor.insertContent(htmlContent);

      jQuery("#loadingAIData").hide();
    },

    error: function (error) {
      // Handle the error

      console.log("error of generate single ai", error);
    },
  });
});

function textToHtml(text) {
  return "<p>" + text.replace(/\n/g, "</p><p>") + "</p>";
}

function convertToHtml(content) {
  // Escape special characters to prevent XSS vulnerabilities

  var escapedContent = escapeHtml(content);

  // Wrap the escaped content in a <div> to ensure it's valid HTML

  var htmlContent = "<div>" + escapedContent + "</div>";

  return htmlContent;
}

function escapeHtml(unsafe) {
  return unsafe

    .replace(/&/g, "&amp;")

    .replace(/</g, "&lt;")

    .replace(/>/g, "&gt;")

    .replace(/"/g, "&quot;")

    .replace(/'/g, "&#039;");
}

function saveData() {
  jQuery("#nextStepButton").trigger("click");
}

var isTinyMCEInitialized = false;

var pendingContent = "";

// Copy AI popup form values to hidden fields in main_form for project details.
// Safe to call multiple times; only copies values without side-effects.
function copyAIFieldsToHiddenInputs() {
  jQuery("#ai_seed_keyword_hidden").val(jQuery("#seed_keyword").val() || "");
  jQuery("#ai_seed_options_hidden").val(jQuery("select[name='seed_options']").val() || "");
  jQuery("#ai_content_type_hidden").val(jQuery("select[name='content_type']").val() || jQuery("#cotnt_type").val() || "");
  jQuery("#ai_nos_of_words_hidden").val(jQuery("select[name='nos_of_words']").val() || jQuery("#post_size").val() || "");
  jQuery("#ai_point_of_view_hidden").val(jQuery("select[name='point_of_view']").val() || "");
  jQuery("#ai_content_lang_hidden").val(jQuery("select[name='content_lang']").val() || jQuery("#language").val() || "");
  jQuery("#ai_details_to_include_hidden").val(jQuery("textarea[name='details_to_include']").val() || "");
  var _ctaText = jQuery("#call_to_action, #call_to_action_multi").first().val() || "";
  var _ctaUrl  = jQuery("#cta_url, #cta_url_multi").first().val() || "";
  var _ctaCombined = _ctaText + (_ctaUrl ? "\nCTA URL: " + _ctaUrl : "");
  jQuery("#ai_call_to_action_hidden").val(_ctaCombined);
  jQuery("#ai_image_option_hidden").val(jQuery("input[name='aiImage']:checked").val() || "");
  jQuery("#ai_generated_title_hidden").val(jQuery("#AI_Title").val() || jQuery("#ai_title").val() || "");
  jQuery("#ai_for_testing_only_hidden").val(jQuery("#for_testing_only").is(":checked") ? "1" : "0");
}

function saveFinalData() {
  var textarea = document.getElementById("showmydataindivText");

  var plainTextContent = textarea.value;

  var metatitle = jQuery("#meta_title").val();

  var meta_descreption = jQuery("#meta_descreption").val();

  $("#on-page-seo").prop("checked", true);

  jQuery("#custom-title").val(metatitle);

  jQuery("#custom-description").val(meta_descreption);

  copyAIFieldsToHiddenInputs();

  var modalProjectName = document.getElementById('modal_project_name');
  if (modalProjectName && modalProjectName.value.trim()) {
    jQuery('.PostForm__name').val(modalProjectName.value.trim());
  }

  // Sync modal category selections → main form cats[]
  // Remove any hidden inputs from a previous sync to avoid duplicates
  document.querySelectorAll('#main_form input[data-from-modal]').forEach(function(el) {
    el.parentNode.removeChild(el);
  });
  // Disable sidebar cats[] checkboxes so they don't double-submit alongside hidden inputs
  document.querySelectorAll('#main_form input[name="cats[]"]').forEach(function(el) {
    el.disabled = true;
  });
  var _modalForm = document.getElementById('main_form');
  document.querySelectorAll('#single-modal-category-list input[type="checkbox"]').forEach(function(cb) {
    var termId = cb.value;
    // Mirror selection visually on the sidebar
    var sidebar = document.querySelector('#main_form input[name="cats[]"][value="' + termId + '"]');
    if (sidebar) sidebar.checked = cb.checked;
    // Guarantee submission via hidden input for every checked modal category
    if (cb.checked) {
      var hidden = document.createElement('input');
      hidden.type = 'hidden';
      hidden.name = 'cats[]';
      hidden.value = termId;
      hidden.setAttribute('data-from-modal', '1');
      _modalForm.appendChild(hidden);
    }
  });

  jQuery("#exampleModal").hide();

  jQuery("#exampleModal1").hide();

  jQuery("#butn").trigger("click");

  insertContent(plainTextContent);
}

function initializeTinyMCE() {
  // Initialize TinyMCE

  tinymce.init({
    selector: "#content",

    setup: function (editor) {
      editor.on("init", function () {
        // Set the flag to true once initialized

        isTinyMCEInitialized = true;

        // After initialization, insert content if there's content to insert

        if (pendingContent) {
          insertContent(pendingContent);

          // Clear pending content after insertion

          pendingContent = "";
        }
      });
    },
  });
}

function insertContent(content) {
  if (isTinyMCEInitialized) {
    // Normalize content before insertion:
    // 1. Replace &nbsp; and U+00A0 with regular spaces
    content = content.replace(/&nbsp;/g, ' ').replace(/\u00A0/g, ' ');
    
    // 2. Convert multiple consecutive blank lines into single blank lines
    //    (prevents wpautop from creating <p>&nbsp;</p> chains)
    content = content.replace(/\n{3,}/g, '\n\n');
    
    // 3. Clear editor and insert normalized content
    tinyMCE.activeEditor.setContent("");
    tinymce.activeEditor.insertContent(content);
  } else {
    // If TinyMCE is not initialized, initialize it and store the content to be inserted

    if (!pendingContent) {
      // Initialize TinyMCE only if it's not already initialized

      initializeTinyMCE();
    }

    // Store the content to be inserted (normalized)
    content = content.replace(/&nbsp;/g, ' ').replace(/\u00A0/g, ' ');
    content = content.replace(/\n{3,}/g, '\n\n');
    pendingContent = content;

    tinymce.activeEditor.insertContent(pendingContent);
  }
}

jQuery(document).ready(function () {
  resetSmartWizard();
});

function generateAIMetaJs() {
  var maintitlearea = jQuery("#maintitlearea").val();

  if (maintitlearea == "") {
    var aigeneratedtitle = jQuery("#aigeneratedtitle").val();
  } else {
    var aigeneratedtitle = maintitlearea;
  }

  var ajaxUrl = '".home_url("/")."wp-admin/admin-ajax.php';

  var seedkeyword = jQuery("#seed_keyword").val();

  jQuery
    .post(ajaxUrl, {
      action: "generateAIMeta",

      aigeneratedtitle: aigeneratedtitle,

      seedkeyword: seedkeyword,
    })

    .success(function (response) {
      console.log(response);

      jQuery("#meta_title").val(response.data.title);

      jQuery("#meta_descreption").val(response.data.descreption);
    });
}

function countContent() {
  var _ccEl = document.getElementById("exampleFormControlTextarea");
  if (!_ccEl) return; // field replaced by dynamic niche fields
  var val = _ccEl.value;

  var wordCounterwithoutSpace = 0;

  for (var i = 0; i < val.length; i++) {
    if (val[i] == " ") {
      continue;
    } else {
      wordCounterwithoutSpace++;
    }
  }

  jQuery("#countContent").html(
    1500 - wordCounterwithoutSpace + " characters remaining."
  );

  if (wordCounterwithoutSpace >= 1500) {
    return false;
  }
}

function countContent1() {
  var val = document.getElementById("exampleFormControlTextarea1").value;

  var wordCounterwithoutSpace = 0;

  for (var i = 0; i < val.length; i++) {
    if (val[i] == " ") {
      continue;
    } else {
      wordCounterwithoutSpace++;
    }
  }

  jQuery("#countContent1").html(
    1500 - wordCounterwithoutSpace + " characters remaining."
  );

  if (wordCounterwithoutSpace >= 1500) {
    return false;
  }
}

function countContentCallToAction() {
  var val = document.getElementById("call_to_action").value;

  var wordCounterwithoutSpace = 0;

  for (var i = 0; i < val.length; i++) {
    if (val[i] == " ") {
      continue;
    } else {
      wordCounterwithoutSpace++;
    }
  }

  jQuery("#countContentCallToAction").html(
    1000 - wordCounterwithoutSpace + " characters remaining."
  );

  if (wordCounterwithoutSpace >= 1000) {
    return false;
  }
}

function LimitText(ref, iLength, textareaid) {
  if (ref.value.length > iLength) {
    if (textareaid == 1) {
      jQuery("#countContent").html(
        "Text length cannot be greater than " +
          iLength +
          " characters Current length is: " +
          ref.value.length
      );
    } else if (textareaid == 2) {
      jQuery("#countContentCallToAction").html(
        "Text length cannot be greater than " +
          iLength +
          " characters Current length is: " +
          ref.value.length
      );
    } else if (textareaid == 3) {
      jQuery("#error_manually_promt_for_image").html(
        "Text length cannot be greater than " +
          iLength +
          " characters Current length is: " +
          ref.value.length
      );
    } else {
    }

    ref.focus();
  } else {
    jQuery("#countContent").html("");

    jQuery("#countContentCallToAction").html("");

    jQuery("#error_manually_promt_for_image").html("");
  }
}

function refreshAIImage() {
  // Single source of truth for the "AI image from title" cover generation.
  // Triggered by the panel's Generate / Regenerate button. Shows the same
  // #loadingAIImage GIF used by the custom-prompt path while the request is in
  // flight, and disables the button to prevent double submits.
  var $btn = jQuery("#AIrefreshOption button");
  var $preview = jQuery("#iseo-preview-title");
  var $hint = jQuery("#AIrefreshOption .iseo-hint");

  if ($btn.prop("disabled")) return false;

  var seed_select = jQuery("#seed_select").val();
  var title;
  if (seed_select == "seed_option1") {
    title = jQuery("#seed_keyword").val();
  } else {
    title = jQuery("#maintitlearea").val() || jQuery("#aigeneratedtitle").val();
  }
  if (!title) {
    title = jQuery("#seed_keyword").val() || "";
  }

  var originalText = $btn.text();
  $btn.prop("disabled", true).text("Generating…");
  $preview.removeClass("has-image");
  jQuery("#loadingAIImage").show();
  $hint.removeClass("ok").text("Generating your cover image… this can take a few seconds.");

  var formData = new FormData();
  formData.append("action", "fetch_AI_image");
  formData.append("title", title);
  // v2 (OpenAI) cover image with the selected niche; PHP falls back to legacy Flux when use_v2 is absent.
  formData.append("niche", window.iseoSelectedNiche());
  formData.append("use_v2", "1");

  jQuery.ajax({
    url: ajaxurl,
    type: "POST",
    data: formData,
    contentType: false,
    processData: false,
    success: function (response) {
      jQuery("#loadingAIImage").hide();
      $btn.prop("disabled", false);

      if (response.success === false) {
        $btn.text(originalText);
        $hint.removeClass("ok").text("Costs 1 image credit when you press Generate.");
        if (response.data && (response.data.includes('402') || response.data.includes('Insufficient') || response.data.includes('credits') || response.data.includes('trial'))) {
          improveseoShowCreditNotice(response.data, 'image');
        } else {
          showImproveSEONotification(
            'error',
            'Image Generation Failed',
            'Unable to generate image. If you experience errors too often, please contact support.',
            'mailto:support@improveseoplugin.com?subject=Image%20Generation%20Error'
          );
        }
        return;
      }

      jQuery("#ai-image-display").html(
        "<img src='" + response.data + "' alt='AI cover image' style='max-width:100%'>"
      );
      jQuery("#AI-Image-uploaded-path").val(response.data);
      $preview.addClass("has-image");
      $btn.text("Regenerate image");
      $hint.addClass("ok").text("Cover image ready. 1 image credit used.");
    },
    error: function () {
      jQuery("#loadingAIImage").hide();
      $btn.prop("disabled", false).text(originalText);
      $hint.removeClass("ok").text("Costs 1 image credit when you press Generate.");
      showImproveSEONotification(
        'error',
        'Image Generation Failed',
        'Unable to generate image. Please try again.',
        null
      );
    }
  });

  // Prevent form submission when called via inline onclick="return refreshAIImage()"
  return false;
}

function getCookie(cname) {
  let name = cname + "=";

  let decodedCookie = decodeURIComponent(document.cookie);

  let ca = decodedCookie.split(";");

  for (let i = 0; i < ca.length; i++) {
    let c = ca[i];

    while (c.charAt(0) == " ") {
      c = c.substring(1);
    }

    if (c.indexOf(name) == 0) {
      return c.substring(name.length, c.length);
    }
  }

  return "";
}

jQuery(document).ready(function (jQuery) {
  jQuery("input[type='radio'][name='aiImage']").change(function () {
    // Selecting a method only reveals its panel. Nothing generates on select -
    // AI images are created when the user presses the panel's Generate button.
    jQuery("#AI_image_div, #manually_image_div, #Prompt_to_create_Dalle_Image").css("display", "none");
    jQuery("#prompt_image_div").css("display", "none");

    if (this.value == "Manually_image") {
      jQuery("#manually_image_div").css("display", "block");
      if (typeof SeedShow === "function") SeedShow();

    } else if (this.value == "manually_promt_image") {
      jQuery("#Prompt_to_create_Dalle_Image").css("display", "block");

      var aipromtval = jQuery("#AI-Prompt-Image-uploaded-path").val();
      if (aipromtval) {
        jQuery("#prompt_image_div").css("display", "block");
      }

      // Pre-fill the editable prompt from the article title (no image generated here).
      var seed_select = jQuery("#seed_select").val();
      var title;
      if (seed_select == "seed_option1") {
        title = jQuery("#seed_keyword").val();
      } else {
        title = jQuery("#maintitlearea").val() || jQuery("#aigeneratedtitle").val();
      }
      if (!title) {
        title = jQuery("#seed_keyword").val() || jQuery("#maintitlearea").val() || "";
      }

      jQuery("#manually_promt_for_image").val(
        "Please wait while we prepare the prompts for you...."
      );

      var promptForm = new FormData();
      promptForm.append("action", "getPromptForImages");
      promptForm.append("title", title);

      jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        data: promptForm,
        contentType: false,
        processData: false,
        success: function (response) {
          var promptTopic =
            response && typeof response.data === "string" && response.data
              ? response.data
              : title;
          jQuery("#manually_promt_for_image").val(buildEditableImagePrompt(promptTopic));
        },
        error: function () {
          jQuery("#manually_promt_for_image").val(
            buildEditableImagePrompt(
              'The most concrete, recognisable subject of an article titled "' + title + '".'
            )
          );
        }
      });

      jQuery("#manually_promt_for_image").css("display", "block");

    } else if (this.value == "AI_image") {
      jQuery("#AI_image_div").css("display", "block");
      if (typeof SeedHide === "function") SeedHide();

      // Reflect any previously generated image; never auto-generate on select.
      var existingPath = jQuery("#AI-Image-uploaded-path").val();
      if (existingPath) {
        if (!jQuery("#ai-image-display img").length) {
          jQuery("#ai-image-display").html(
            "<img src='" + existingPath + "' alt='AI cover image' style='max-width:100%'>"
          );
        }
        jQuery("#iseo-preview-title").addClass("has-image");
        jQuery("#AIrefreshOption button").text("Regenerate image");
        jQuery("#AIrefreshOption .iseo-hint").addClass("ok").text("Cover image ready. 1 image credit used.");
      } else {
        jQuery("#iseo-preview-title").removeClass("has-image");
        jQuery("#AIrefreshOption button").text("Generate AI image");
        jQuery("#AIrefreshOption .iseo-hint").removeClass("ok").text("Costs 1 image credit when you press Generate.");
      }
    }
  });
});

jQuery(document).ready(function (jQuery) {
  jQuery("#upload-image-button").on("change", function () {
    jQuery("#loadingImage").show();

    var image = this.files[0];

    var formData = new FormData();

    formData.append("action", "upload_image");

    formData.append("image", image);

    jQuery.ajax({
      url: ajaxurl,

      type: "POST",

      data: formData,

      contentType: false,

      processData: false,

      success: function (response) {
        jQuery("#manually-image-display").html(
          "<img src='" +
            response +
            "' alt='Uploaded Image' style='max-width: 100%'>"
        );

        jQuery("#manually-image-uploaded-path").val(response);

        jQuery("#loadingImage").hide();
      },

      error: function () {
        alert("Error uploading image.");

        jQuery("#loadingImage").hide();
      },
    });
  });
});

jQuery(document).ready(function ($) {
  $("#pop_up_multi_form").submit(function (event) {
    event.preventDefault();

    var form = document.getElementById("pop_up_multi_form");

    var formData = new FormData(form);

    formData.append("action", "multiPostData");
    
    // Show loading overlay if available
    if (typeof ImproveSEOLoading !== 'undefined' && ImproveSEOLoading.show) {
      ImproveSEOLoading.show({
        title: 'Creating Bulk Project...',
        message: 'Please wait while we create your bulk content generation project. This may take a few moments.'
      });
    }

    $.ajax({
      url: ajaxurl,

      method: "POST",

      data: formData,

      processData: false,

      contentType: false,

      success: function (response) {
        // Hide loading overlay
        if (typeof ImproveSEOLoading !== 'undefined' && ImproveSEOLoading.hide) {
          ImproveSEOLoading.hide();
        }
        
        console.log(response);

        if (response && response.data && response.data.status == "success") {
          // Show success notification
          if (typeof showImproveSEONotification !== 'undefined') {
            showImproveSEONotification(
              'success',
              '✓ Bulk Project Created',
              'Your bulk content generation project has been created successfully. You will receive an email notification when processing is complete.',
              null
            );
          }
          
          // Set button to success state if available
          if (typeof BulkSubmitButton !== 'undefined' && BulkSubmitButton.setSuccess) {
            BulkSubmitButton.setSuccess();
          }
          
          // Redirect after brief delay
          setTimeout(function() {
            window.location.replace(response.data.linkredirect);
          }, 1500);
        } else {
          // Show error notification
          const errorMsg = response && response.data && response.data.message 
            ? response.data.message 
            : 'An error occurred while creating the bulk project.';
          
          if (typeof showImproveSEONotification !== 'undefined') {
            showImproveSEONotification(
              'error',
              '❌ Submission Failed',
              errorMsg,
              null
            );
          } else {
            alert(errorMsg);
          }
          
          // Reset button state if available
          if (typeof BulkSubmitButton !== 'undefined' && BulkSubmitButton.setError) {
            BulkSubmitButton.setError();
          }
        }
      },

      error: function (xhr, status, error) {
        // Hide loading overlay
        if (typeof ImproveSEOLoading !== 'undefined' && ImproveSEOLoading.hide) {
          ImproveSEOLoading.hide();
        }
        
        let errorMsg = 'Network error occurred. Please check your connection and try again.';
        
        if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
          errorMsg = xhr.responseJSON.data.message;
        } else if (xhr.statusText && xhr.statusText !== 'error') {
          errorMsg = 'Server error: ' + xhr.statusText;
        }
        
        // Show error notification
        if (typeof showImproveSEONotification !== 'undefined') {
          showImproveSEONotification(
            'error',
            'Connection Error',
            errorMsg,
            null
          );
        } else {
          alert(errorMsg);
        }
        
        // Reset button state if available
        if (typeof BulkSubmitButton !== 'undefined' && BulkSubmitButton.setError) {
          BulkSubmitButton.setError();
        }

        console.error(error);
      },
    });
  });
});

function resetSmartWizard() {
  var $wizard = jQuery("#smartwizard");

  // Nothing to reset on pages without the wizard (this file loads on every
  // plugin admin page), or if the SmartWizard plugin script isn't present.
  if (!$wizard.length || typeof $wizard.smartWizard !== "function") return;

  // Destroy the existing SmartWizard instance. On first load there is none —
  // SmartWizard's destroy throws on an uninitialised element, which crashed
  // every page load since this call runs on document ready.
  try {
    $wizard.smartWizard("destroy");
  } catch (e) {
    // Not initialised yet — fine, we're about to initialise it fresh.
  }

  // Reinitialize the SmartWizard plugin

  jQuery("#smartwizard").smartWizard({
    // Configuration options

    selected: 0,

    theme: "default", // theme for the wizard, related CSS need to include for other than default theme

    transitionEffect: "fade", // Effect on navigation, none/fade/slide/slideleft

    enableURLhash: false, // Enable selection of the step based on url hash

    toolbarSettings: {
      toolbarPosition: "bottom", // none, top, bottom, both

      toolbarButtonPosition: "right", // left, right

      showNextButton: true, // show/hide a Next button

      showPreviousButton: true, // show/hide a Previous button
    },
  });

  // Go to the first step

  jQuery("#smartwizard").smartWizard("reset");
}

// Function to generate AI title
function generateAITitle() {
  var seedtype = jQuery("#seed_select").val();
  var seedkeyword = jQuery("#seed_keyword").val();
  seedkeyword = seedkeyword ? seedkeyword.trim() : "";

  // When AI title needs to be generated (not seed_option1), ensure seed keyword exists first
  if (seedtype != "seed_option1") {
    if (!seedkeyword) {
      // Show styled notification instead of inline error
      if (typeof ImproveSEONotification !== 'undefined') {
        ImproveSEONotification.warning(
          'Please enter a seed keyword before generating a title.',
          'Seed Keyword Required'
        );
      } else {
        alert("Please enter seed keyword.");
      }
      jQuery("#loader").hide();
      jQuery(".hide_on_seed_option1").hide();
      jQuery("#gettitle").hide();
      return; // do not call API without seed keyword
    } else {
      // Clear any previous error
      jQuery("#error_seed_keyword").html("");
    }

    jQuery("#loader").show();
    jQuery("#gettitle").css({ display: "flex" });
    jQuery(".hide_on_seed_option1").show();
  } else {
    jQuery("#loader").hide();
    jQuery(".hide_on_seed_option1").hide();
    jQuery("#gettitle").hide();
    return; // Don't generate for seed_option1
  }

  var contenttype = jQuery("#cotnt_type").val();

  // Show loading popup
  if (typeof ImproveSEOLoading !== 'undefined') {
    ImproveSEOLoading.show({
      title: 'Generating Title...',
      message: 'Please wait while AI generates your title.'
    });
  }

  // If seedtype requires AI title, we already validated seedkeyword above
  jQuery
    .post(ajaxUrl, {
      action: "getGPTdata",
      seedtype: seedtype,
      seedkeyword: seedkeyword,
      contenttype: contenttype,
    })
    .success(function (data) {
      console.log("" + data);

      jQuery("#loader").hide();

      // Hide loading popup
      if (typeof ImproveSEOLoading !== 'undefined') {
        ImproveSEOLoading.hide();
      }

      // The server echoes the title directly; an empty body means the auxiliary
      // call failed (e.g. missing API key/site code, or the server route errored).
      // A 200 with empty text isn't caught by .fail(), so surface it here instead
      // of silently dropping a blank title into the field.
      var generatedTitle = (typeof data === 'string') ? data.trim() : '';
      if (!generatedTitle) {
        if (typeof ImproveSEONotification !== 'undefined') {
          ImproveSEONotification.error(
            'We couldn\'t generate a title. Please check your ImproveSEO connection in Settings and try again.',
            'Title Generation Failed'
          );
        } else {
          alert("We couldn't generate a title. Please try again.");
        }
        return;
      }

      // jQuery("#maintitle").html(" <div class=\'resultdata\'><textarea id=\'maintitlearea\' name=\'maintitlearea\' class=\'form-control\' rows=\'3\' cols=\'70\'>" + data + "</textarea></div>");
      jQuery("#maintitlearea").val(generatedTitle);
      jQuery("#aigeneratedtitle").val(generatedTitle);

      // Update button text to Regenerate after first generation
      jQuery("#reload-btn-text").text("Regenerate");

      var AudienceData = getCookie("AudienceData");
    })
    .fail(function() {
      jQuery("#loader").hide();
      
      // Hide loading popup
      if (typeof ImproveSEOLoading !== 'undefined') {
        ImproveSEOLoading.hide();
      }
      
      if (typeof ImproveSEONotification !== 'undefined') {
        ImproveSEONotification.error(
          'Failed to generate title. Please try again.',
          'Generation Error'
        );
      } else {
        alert("Failed to generate title. Please try again.");
      }
    });
}

jQuery("#seed_select").on("change", function () {
  var seedtype = jQuery(this).val();
  
  // Just show/hide the title generation area based on selection
  if (seedtype != "seed_option1") {
    jQuery(".hide_on_seed_option1").show();
    jQuery("#gettitle").css({ display: "flex" });
  } else {
    jQuery(".hide_on_seed_option1").hide();
    jQuery("#gettitle").hide();
    // Clear the title area when switching to seed_option1
    jQuery("#maintitlearea").val("");
    jQuery("#aigeneratedtitle").val("");
  }
});

function SaveResultsButton() {
  var keyword_id = jQuery("#project_name").val();

  var keyword_list = jQuery("#keyword_list").val();

  var content_type = jQuery('#pop_up_multi_form [name="content_type"]').val() || '';

  // Show loading message and disable button
  jQuery("#exampleFormControlTextarea1").text("Wait! Generating content...");
  jQuery("#exampleFormControlTextarea1").val("Wait! Generating content...");

  // Disable the button to prevent multiple clicks
  var button = jQuery('input[onclick="return SaveResultsButton();"]');
  var originalValue = button.val();
  button.val("Generating...").prop("disabled", true);

  jQuery
    .post(ajaxUrl, {
      action: "multi_form_data",
      keyword_id: keyword_id,
      keyword_list: keyword_list,
      content_type: content_type,
    })
    .success(function (data) {
      jQuery("#exampleFormControlTextarea1").text(data);
      jQuery("#exampleFormControlTextarea1").val(data);
      // alert(data);
    })
    .fail(function () {
      // Handle error case
      jQuery("#exampleFormControlTextarea1").text(
        "Error generating content. Please try again."
      );
      jQuery("#exampleFormControlTextarea1").val(
        "Error generating content. Please try again."
      );
    })
    .always(function () {
      // Re-enable button regardless of success or failure
      button.val(originalValue).prop("disabled", false);
    });
}

function saveProject() {
  var seed_keyword = jQuery("#input").val();

  var results = jQuery("#output").val();

  if (!seed_keyword || !results) {
    return;
  }

  jQuery.ajax({
    url: ajaxUrl,

    method: "POST",

    dataType: "json",

    data: {
      action: "sw_saved_search_results_keyword",

      proj_name: seed_keyword,

      search_results: results,
    },

    success: function (response) {
      // console.log(response);

      if (response.data.status == "success") {
        var select = document.getElementById("keyword_list_name");

        // Create a new option element

        var newOption = document.createElement("option");

        newOption.value = response.data.id;

        newOption.text = response.data.proj_name;

        // Prepend the new option to the select element

        select.insertBefore(newOption, select.firstChild);

        // var newOption = jQuery('<option></option>')

        //     .attr('value', response.data.id)

        //     .text(response.data.proj_name);

        // jQuery('#keyword_list_name').append(newOption);

        jQuery("#keyword_list_name").val(response.data.id);

        jQuery("#keyword_list").val(response.data.search_results);

        jQuery("#keyword_list_container").show();

        jQuery("#create_keyword_container").hide();

        // alert('Data saved successfully!');
      } else {
        alert("An error occurred: " + response.data.status);
      }
    },

    error: function (xhr, status, error) {
      alert("AJAX error: " + error);
    },
  });
}

jQuery(document).ready(function ($) {
  jQuery(".multi-upload-button").click(function (event) {
    event.preventDefault();

    const button = jQuery(this);

    const hiddenField = button.prev('input[type="hidden"]');

    let hiddenFieldValue = hiddenField.val().split(",");

    const customUploader = wp
      .media({
        title: "Insert images",

        library: {
          type: "image",
        },

        button: {
          text: "Use these images",
        },

        multiple: true,
      })
      .on("select", function () {
        let selectedImages = customUploader
          .state()
          .get("selection")
          .map((item) => {
            item = item.toJSON();

            return item;
          });

        selectedImages.forEach((image) => {
          jQuery(".multi-upload-gallery").append(
            '<li data-id="' +
              image.id +
              '"><img src="' +
              image.url +
              '" alt="Image ' +
              (hiddenFieldValue.length + 1) +
              '" width="80" height="80"><a href="#" class="multi-upload-gallery-remove" style="display: inline;">&#215;</a></li>'
          );

          hiddenFieldValue.push(image.id);
        });

        jQuery(".multi-upload-gallery").sortable("refresh");

        hiddenField.val(hiddenFieldValue.join(","));
      })
      .open();
  });

  jQuery(document).on(
    "click",
    ".multi-upload-gallery-remove",
    function (event) {
      event.preventDefault();

      const button = jQuery(this);

      const imageId = button.parent().data("id").toString();

      const hiddenField = jQuery('input[name="my_field"]');

      let hiddenFieldValue = hiddenField.val().split(",");

      button.parent().remove();

      const index = hiddenFieldValue.indexOf(imageId);

      if (index != -1) {
        hiddenFieldValue.splice(index, 1);
      }

      hiddenField.val(hiddenFieldValue.join(","));

      jQuery(".multi-upload-gallery").sortable("refresh");
    }
  );

  // Make the gallery sortable

  jQuery(".multi-upload-gallery").sortable({
    items: "li",

    cursor: "-webkit-grabbing",

    scrollSensitivity: 40,

    stop: function (event, ui) {
      ui.item.removeAttr("style");

      let sort = [];

      const container = jQuery(this);

      container.find("li").each(function () {
        sort.push(jQuery(this).attr("data-id"));
      });

      container.parent().next('input[type="hidden"]').val(sort.join(","));
    },
  });
});

// my js start

function SeedShow() {
  jQuery("#manually_image_div").show();

  jQuery("#multiple_image_div").hide();
}
function SeedHide() {
  jQuery("#manually_image_div").hide();
  jQuery("#multiple_image_div").hide();
}

function SelectexisitingHide() {
  jQuery("#multiple_image_div").show();

  jQuery("#manually_image_div").hide();
  jQuery("#ai-image-generating").hide();
}
function multiple_image_div() {
  jQuery("#multiple_image_div").hide();
  jQuery("#ai-image-generating").show();
  jQuery("#manually_image_div").hide();
}

jQuery("#create_keyword").on("change", function () {
  var selectedOption = jQuery(this).val();

  jQuery("#copy_paste_container").empty();

  jQuery("#google_suggestion_container").empty();

  jQuery("#ai_suggestion_container").empty();

  if (selectedOption == "copy_paste") {
    jQuery("#copy_paste_container").append(
      `<section class="form-wrap">

                    <div class="PostForm mt-3">

                        <div class="BasicForm__row" style="max-width: 100% !important; width: 100%; ">

                            <div class="input-group" style="max-width: 100% !important; width: 100%;">

                                <label style="padding-left:20px;" class="form-label">Title</label>

                                <input type="text" style="max-width: 100% !important; width: 100%; padding: 20px !important; background-color:white; color: rgba(80, 87, 94, 0.8) !important;" id="input" placeholder="" class="sw-project-name keyword_input form-control" value="" />

                            </div>

                        </div>

                        <div class="BasicForm__row" style="max-width: 100% !important; width: 100%;">

                            <label  style="padding-left:20px;"  class="form-label">Keywords</label>

                            <textarea id="output" style="max-width: 100% !important; width: 100%; color: rgba(80, 87, 94, 0.8) !important;   font-weight: 400 !important;" rows="5" class="textarea-control sw-output-ta keyword_input" placeholder=""></textarea>

                        </div>

                        <div style="text-align: end;" class="BasicForm__row mb-3">

                                <input type="button" onclick="return saveProject()" class="btn styling_post_page_action_buttons  btn-outline-primary" value="Save Results">

                            </div>

                    </div>

                </section>`
    );
  } else if (selectedOption == "google_suggestion") {
    jQuery("#google_suggestion_container").append(
      `<section class="form-wrap">

                        <div class="PostForm mt-3">

                            <div class="BasicForm__row" style="max-width: 100% !important; width: 100%;">

                                <div class="input-group" style="max-width: 100% !important; width: 100%;">

                                    <label style="padding-left:20px;" class="form-label">Seed Keyword</label>

                                    <input  style="max-width: 100% !important; width: 100%; padding: 20px !important; background-color:white; color: rgba(80, 87, 94, 0.8) !important;"type="text" id="input" placeholder="" class="sw-project-name keyword_input form-control" value="" />

                                </div>

                            </div>

                            <div class="BasicForm__row" style="max-width: 100% !important; width: 100%;">

                                <label  style="padding-left:20px;"  class="form-label">Results</label>

                                <textarea style="max-width: 100% !important; width: 100%;  color: rgba(80, 87, 94, 0.8) !important;   font-weight: 400 !important;" id="output" rows="5" class="textarea-control sw-output-ta keyword_input" placeholder=""></textarea>

                            </div>

                            <div style="text-align: end;" class="BasicForm__row mb-3">

                                <input id="startjob" onclick="generate();" type="button" class="btn btn-outline-primary styling_post_page_action_buttons " value="Generate Keywords!">

                                <input type="button" class="clear-search-results btn btn-outline-primary styling_post_page_action_buttons " style="margin-left:10px;" value="Clear Results">

                                <input type="button" onclick="return saveProject()" class="btn styling_post_page_action_buttons  btn-outline-primary" style="margin-left:10px;" value="Save Results">

                            </div>

                        </div>

                </section>`
    );
  } else if (selectedOption == "ai_create_keyword") {
    jQuery("#ai_suggestion_container").append(
      `<div class="PostForm mt-3">

                    <div class="BasicForm__row" style="max-width: 100% !important; width: 100%;">

                        <div class="input-group" style="max-width: 100% !important; width: 100%;">

                            <label style="padding-left:20px;" class="form-label">Seed Keyword</label>

                            <input style="max-width: 100% !important; width: 100%; padding:15px !important; border-radius:50px;    color: rgba(80, 87, 94, 0.8) !important;"  type="text" id="input" placeholder="" class="sw-project-name keyword_input form-control" value="" />

                        </div>

                    </div>

                    <div class="BasicForm__row" style="max-width: 100% !important; width: 100%;">

                        <label  style="padding-left:20px;"  class="form-label">Results</label>

                        <textarea style="max-width: 100% !important; width: 100%;    color: rgba(80, 87, 94, 0.8) !important;    font-weight: 400 !important;" id="output" rows="5" class="textarea-control sw-output-ta keyword_input" placeholder=""></textarea>

                    </div>

                    <div style="text-align: end;"  class="BasicForm__row mb-3">

                        <input id="startjob" type="button" class="btn btn-outline-primary styling_post_page_action_buttons " value="Ai Generate Keywords!">

                        <input type="button" class="clear-search-results btn btn-outline-primary styling_post_page_action_buttons " style="margin-left:10px;" value="Clear Results">

                        <input type="button" onclick="return saveProject()" class="btn styling_post_page_action_buttons  btn-outline-primary" style="margin-left:10px;" value="Save Results">

                    </div>

                </div>`
    );
  }
});

// my js end

// file js start 02-06-24

jQuery(document).ready(function () {
  jQuery("#gettitle").css({ display: "none" });

  // Only generate when the button is clicked, not on selection changes
  jQuery("#reload").on("click", function () {
    generateAITitle();
  });

  // Remove auto-generation on tone of voice changes
  // Just let the user make their selections, they'll click Generate when ready
  jQuery('input[name="content_type"]').on("click", function () {
    // Do nothing - user will click Generate button
  });

  jQuery("#cotnt_type").on("change", function () {
    // Do nothing - user will click Generate button
  });

  /* jQuery('select#shortcodetype').on('change', function() {

           jQuery('#popupcontainer input[type=checkbox]').prop('checked', false);

       }); */

  jQuery("#popupcontainer button").click(function () {
    var getid = jQuery(this).attr("id");

    //console.log(getid);

    if (
      jQuery("input[type=checkbox].option_" + getid).prop("checked") == true
    ) {
      jQuery("input[type=checkbox].option_" + getid).prop("checked", false);

      jQuery("#getpopupselected .result_" + getid).remove();
    } else {
      jQuery("input[type=checkbox].option_" + getid).prop("checked", true);

      //console.log(jQuery('.option_'+getid).prop('checked'));

      if (jQuery(".option_" + getid).prop("checked") == true) {
        var selectedshortcode =
          '<div class="result_' +
          getid +
          '">' +
          jQuery(".option_" + getid).val() +
          "</div>";

        //console.log('ccc'+selectedshortcode);

        jQuery("#getpopupselected").append(selectedshortcode);
      }
    }
  });

  jQuery("#smartwizard").on(
    "leaveStep",
    function (e, anchorObject, stepNumber, stepDirection) {
      if (stepDirection === "forward") {
        var aigeneratedtitle_op = jQuery("#maintitlearea").val();

        if (stepNumber == 0) {
          var AudienceData = getCookie("AudienceData");

          //var aigeneratedtitle_op = jQuery('#aigeneratedtitle').val();

          jQuery("#manually_promt_for_image").val(
            buildEditableImagePrompt(
              'The most concrete, recognisable subject of an article titled "' +
                aigeneratedtitle_op +
                '".'
            )
          );

          //jQuery('#manually_promt_for_image').val('Very high quality shooting from a distance, high detail, photorealistic, image resolution 2146 pixels, cinematic. The theme is `'+aigeneratedtitle_op+'`');
        }

        // Perform actions related to the first step

        // For example:

        //console.log('First step: ' + firstStep);

        var seed_keyword = jQuery("#seed_keyword").val();

        var seed_select = jQuery("#seed_select").val();

        var step1_error = 0;

        if (seed_keyword == "") {
          document.getElementById("error_seed_keyword").innerText =
            "Please enter seed keyword.";

          step1_error++;
        } else {
          jQuery("#error_seed_keyword").html("");
        }

        if (seed_select == "") {
          document.getElementById("error_seed_select").innerText =
            "Please select title type.";

          step1_error++;
        } else {
          jQuery("#error_seed_select").html("");

          if (seed_select != "seed_option1") {
            var checkbox = document.getElementById("checkbox_need");

            if (checkbox.checked) {
              return true;
            } else {
              var errorSpan = document.createElement("span");

              errorSpan.innerText =
                "You need to check the checkbox if you want to use the AI-generated title as the title";

              errorContainer.innerHTML = ""; // Clear previous error messages

              errorContainer.appendChild(errorSpan); // Append the error message

              return false;
            }
          }
        }

        if (step1_error == 0) {
          return true;
        } else {
          return false;
        }

        //alert('Next button clicked');

        // Your condition to prevent moving to the next step

        // if (someConditionIsNotMet) {

        //return false;

        // }
      } else if (stepDirection === "backward") {
        //alert('Previous button clicked');
        // Your condition to prevent moving to the previous step
        // if (someConditionIsNotMet) {
        // return false;
        // }
      }
    }
  );

  jQuery("#smartwizard_multi").smartWizard({
    selected: 0,

    theme: "default", // theme for the wizard, related CSS need to include for other than default theme

    transitionEffect: "fade", // Effect on navigation, none/fade/slide/slideleft

    enableURLhash: false, // Enable selection of the step based on url hash

    toolbarSettings: {
      toolbarPosition: "bottom", // none, top, bottom, both

      toolbarButtonPosition: "right", // left, right

      showNextButton: true, // show/hide a Next button

      showPreviousButton: true, // show/hide a Previous button
    },
  });

  jQuery("#smartwizard_multi").on(
    "leaveStep",
    function (e, anchorObject, stepNumber, stepDirection) {
      if (stepDirection == "forward") {
        var aigeneratedtitle_op = jQuery("#maintitlearea").val();

        if (stepNumber == 0) {
          var AudienceData = getCookie("AudienceData");

          // var aigeneratedtitle_op = jQuery('#aigeneratedtitle').val();

          jQuery("#manually_promt_for_image").val(
            buildEditableImagePrompt(
              'The most concrete, recognisable subject of an article titled "' +
                aigeneratedtitle_op +
                '".'
            )
          );

          //jQuery('#manually_promt_for_image').val('Very high quality shooting from a distance, high detail, photorealistic, image resolution 2146 pixels, cinematic. The theme is `'+aigeneratedtitle_op+'`');
        }

        if (stepNumber == 4) {
          // var keywordCount = (jQuery('#keyword_list').val()).split('\n').length;

          // var keywordMin = keywordCount * 3;

          // var keywordTime = (keywordMin / 60).toFixed(2);

          // jQuery('#keywordcounts').text(keywordCount);

          // jQuery('#keywordtime').text(keywordTime);

          // Get the value of the textarea

          var text = jQuery("#keyword_list").val();

          // Split the text into lines

          var lines = text.split("\n");

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

          jQuery("#keywordcounts").text(keywordCount);

          jQuery("#keywordtime").text(keywordTime);
        }

        if (stepNumber == 6) {
          if (jQuery("#schedule_posts_input_wise").is(":checked")) {
            var numberOfPosts = jQuery("#number_of_post_schedule").val();

            // var frequency = $('#schedule_frequency').val();

            if (numberOfPosts == "") {
              document.getElementById(
                "error_number_of_post_schedule"
              ).innerText = "Please enter the number of post.";

              return false;
            } else {
              return true;
            }
          }
        }

        if (stepNumber == 8) {
          jQuery(".category_improveseo input[type='checkbox']").each(
            function () {
              var checkboxValue = $(this).val(); // Get the value of the checkbox

              var isChecked = $(this).prop("checked"); // Check if it's checked

              // Find the corresponding checkbox in .category_improveseo_bulk and set its checked state

              jQuery(
                ".category_improveseo_bulk input[type='checkbox'][value='" +
                  checkboxValue +
                  "']"
              ).prop("checked", isChecked);
            }
          );
        }

        // Perform actions related to the first step

        // For example:

        //console.log('First step: ' + firstStep);

        var project_name = jQuery("#keyword_list_name").val();

        // var cotnt_type = jQuery('#cotnt_type').val();

        var existing_select = jQuery("#existing_select").val();

        var step1_error = 0;

        if (project_name == "") {
          document.getElementById("error_project_name").innerText =
            "Please Select Project Name.";

          step1_error++;
        } else {
          jQuery("#error_project_name").html("");
        }

        if (existing_select == "") {
          document.getElementById("error_existing_select").innerText =
            "Please Select Contant Type.";

          step1_error++;
        } else {
          jQuery("#error_existing_select").html("");
        }

        if (step1_error == 0) {
          return true;
        } else {
          return false;
        }

        //alert('Next button clicked');

        // Your condition to prevent moving to the next step

        // if (someConditionIsNotMet) {

        //return false;

        // }
      } else if (stepDirection === "backward") {
        //alert('Previous button clicked');
        // Your condition to prevent moving to the previous step
        // if (someConditionIsNotMet) {
        // return false;
        // }
      }
    }
  );

  //jQuery('.sw-btn-next').prop('disabled', true);

  // Validate the checkbox

  // jQuery('#checkbox_need').on('change', function() {

  // 	alert('test');

  // 	if (jQuery(this).is(':checked')) {

  // 		// Enable the next button if the checkbox is checked

  // 		jQuery('.sw-btn-next').prop('disabled', false);

  // 	} else {

  // 		// Disable the next button if the checkbox is unchecked

  // 		jQuery('.sw-btn-next').prop('disabled', true);

  // 	}

  // });

  jQuery("#popupcontainer button").click(function () {
    //   jQuery('button').removeClass('selected');

    //   jQuery(this).addClass('selected');

    jQuery(this).toggleClass("selected");
  });
});

function addcategory() {
  var fData = jQuery("#add_category_1").val();

  // console.log(fData);

  //add_category_form

  jQuery.ajax({
    type: "POST",

    url: ajaxUrl,

    dataType: "json",

    data: { action: "add_category_form", fData: fData },

    success: function (response) {
      console.log(response);

      if (response.success) {
        alert(response.message);

        jQuery(".cta-check").append(response.result);
        jQuery(".cta-check_multi").append(response.result);

        jQuery("#add_category_1").val("");
      } else {
        alert("Error: " + response.message);
      }
    },

    error: function (xhr, status, error) {
      console.error("AJAX Error: " + error);
    },
  });
}

jQuery("#post_size").on("change", function () {
  // Get the selected option value

  var selectedOption = jQuery(this).val();

  // Display the selected option in the h2 element

  jQuery("#post_size_select").val(selectedOption);
});

jQuery("#post_size_bulk").on("change", function () {
  // Get the selected option value

  var selectedOption = jQuery(this).val();

  // Display the selected option in the h2 element

  jQuery("#post_size_select_bulk").val(selectedOption);
});

// file js end 02-06-24

jQuery("input[type='radio'][name='assigning_authors']").change(function () {
  var inputValue = jQuery(this).attr("value");

  if (inputValue == "assigning_authors") {
    jQuery("#author_number").show();

    jQuery("#authors_number").hide();
  } else if (inputValue == "assigning_multi_authors") {
    jQuery("#authors_number").show();

    jQuery("#author_number").hide();
  }
});

jQuery("input[type='radio'][name='schedule_posts']").change(function () {
  var inputValue = jQuery(this).attr("value");

  if (inputValue == "schedule_posts_input_wise") {
    jQuery("#number_of_post_schedule_box").show();
  } else {
    jQuery("#number_of_post_schedule_box").hide();
  }
});

// upload.js

jQuery(document).ready(function ($) {
  jQuery("#uploadBtn").on("click", function (e) {
    e.preventDefault();

    let formData = new FormData();

    let files = $("#images")[0].files;

    for (let i = 0; i < files.length; i++) {
      formData.append("images[]", files[i]);
    }

    formData.append("action", "my_plugin_upload");

    jQuery.ajax({
      url: ajaxurl,

      type: "POST",

      data: formData,

      contentType: false,

      processData: false,

      success: function (response) {
        $("#response").html(response);

        $("#hiddenInputs").empty();

        // Append hidden input fields for each URL

        response.data.forEach(function (url) {
          $("#hiddenInputs").append(
            '<input type="hidden" name="uploaded_images[]" value="' + url + '">'
          );
        });
      },

      error: function (err) {
        $("#response").html("<p>An error occurred!</p>");
      },
    });
  });

  jQuery("#images").on("change", function () {
    let files = this.files;

    jQuery("#preview").html("");

    for (let i = 0; i < files.length; i++) {
      let reader = new FileReader();

      reader.onload = function (e) {
        $("#preview").append(
          '<img src="' + e.target.result + '" width="100" style="margin:5px;">'
        );
      };

      reader.readAsDataURL(files[i]);
    }
  });
});

// my js for action button popup

document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll(".action-btn-pop").forEach((button) => {
    button.addEventListener("click", function (event) {
      event.preventDefault();

      let popup = this.closest("td").querySelector(".actionpopup"); // Correctly selecting the popup

      // Close any other open popups
      document.querySelectorAll(".actionpopup").forEach((p) => {
        if (p !== popup) {
          p.style.display = "none";
        }
      });

      // Toggle current popup
      popup.style.display = popup.style.display === "block" ? "none" : "block";
    });
  });

  // Close popup when clicking outside
  document.addEventListener("click", function (event) {
    if (!event.target.closest("td.actions-btn")) {
      // Correct selector
      document
        .querySelectorAll(".actionpopup")
        .forEach((popup) => (popup.style.display = "none"));
    }
  });
});
document.addEventListener("DOMContentLoaded", function () {
  document
    .querySelectorAll(".category input[type='checkbox']")
    .forEach(function (checkbox) {
      checkbox.addEventListener("change", function () {
        const span = this.closest(".category"); // Get the parent <span>

        if (this.checked) {
          span.classList.add("category_active");
        } else {
          span.classList.remove("category_active");
        }
      });
    });
});

/* ──────────────────────────────────────────────────────────────────────────
   Niche-specific detail fields (Step 2)
   Renders 3-4 labelled inputs based on the Business Niche chosen in Step 1.
   Each input is named nd_<id> and is collected into niche_data by getaaldata.
   ────────────────────────────────────────────────────────────────────────── */
window.ISEO_NICHE_FIELDS = {
  hvac: [
    { id: "pricing", label: "Your pricing range for this service", ph: "e.g. AC replacement runs $3,800-$6,200 depending on unit size" },
    { id: "local_detail", label: "Local climate or seasonal context", ph: "e.g. In Phoenix, units work harder and fail sooner than the national average" },
    { id: "real_result", label: "A real job result you can reference (optional)", ph: "e.g. Most emergency calls this summer were capacitor failures" },
    { id: "differentiator", label: "What makes your service different (optional)", ph: "e.g. Same-day service, no emergency fees, licensed master tech" }
  ],
  plumbing: [
    { id: "pricing", label: "Typical pricing for this job", ph: "e.g. Most emergency calls run $180-$350 depending on the issue" },
    { id: "local_detail", label: "Local context", ph: "e.g. Older homes here have galvanised pipes that fail suddenly" },
    { id: "real_result", label: "A real job you can reference (optional)", ph: "e.g. We cleared 40+ burst-pipe calls after the last freeze" },
    { id: "differentiator", label: "What makes you different (optional)", ph: "e.g. Licensed master plumber on every job, no subcontractors" }
  ],
  electrical: [
    { id: "services", label: "Services to feature", ph: "e.g. Panel upgrades, EV charger installs, troubleshooting" },
    { id: "safety", label: "A safety or code angle", ph: "e.g. Older panels in this area often fail inspection" },
    { id: "differentiator", label: "What makes you different (optional)", ph: "e.g. Upfront flat-rate pricing, same-day diagnostics" }
  ],
  roofing: [
    { id: "pricing", label: "Typical project cost range", ph: "e.g. Full replacement $8,000-$14,000 for a 2,000 sq ft home" },
    { id: "local_detail", label: "Local weather or material context", ph: "e.g. Hail damage is our most common claim job here" },
    { id: "trust_signal", label: "A trust or proof signal", ph: "e.g. GAF Master Elite contractor, 500+ roofs locally" }
  ],
  contractor: [
    { id: "services", label: "Projects you take on", ph: "e.g. Kitchen remodels, additions, whole-home renovations" },
    { id: "pricing", label: "Typical project cost range", ph: "e.g. Kitchen remodels here run $25,000-$60,000" },
    { id: "trust_signal", label: "A trust or proof signal (optional)", ph: "e.g. Licensed GC, 20 years local, 300+ completed projects" }
  ],
  home_services: [
    { id: "services", label: "Services to feature", ph: "e.g. Quarterly pest control, deep cleaning, emergency lockouts" },
    { id: "local_detail", label: "Local or seasonal context", ph: "e.g. Ant season peaks here in June" },
    { id: "differentiator", label: "What makes you different (optional)", ph: "e.g. Background-checked techs, same-day service" }
  ],
  landscaping: [
    { id: "services", label: "Services to feature", ph: "e.g. Tree removal, seasonal cleanup, irrigation" },
    { id: "local_detail", label: "Local or seasonal context", ph: "e.g. Spring storms drop a lot of limbs in this area" },
    { id: "differentiator", label: "What makes you different (optional)", ph: "e.g. Licensed arborist on staff, free estimates" }
  ],
  dental: [
    { id: "procedures", label: "Procedures you want to feature", ph: "e.g. Invisalign, implants, same-day emergency appointments" },
    { id: "insurance", label: "Insurance or payment options", ph: "e.g. Delta Dental, 0% CareCredit financing" },
    { id: "patient_concern", label: "Main patient concern to address", ph: "e.g. Anxiety, we offer sedation options" }
  ],
  medical: [
    { id: "services", label: "Services or specialties to feature", ph: "e.g. Same-day sick visits, chronic care, telehealth" },
    { id: "insurance", label: "Insurance or payment options", ph: "e.g. Most major insurers, transparent self-pay pricing" },
    { id: "patient_concern", label: "Main patient concern to address", ph: "e.g. Wait times, we offer online scheduling" }
  ],
  wellness: [
    { id: "services", label: "Services to feature", ph: "e.g. Chiropractic adjustments, deep tissue massage, acupuncture" },
    { id: "patient_concern", label: "Main client concern to address", ph: "e.g. Chronic back pain from desk work" },
    { id: "differentiator", label: "What makes you different (optional)", ph: "e.g. Direct insurance billing, evening appointments" }
  ],
  auto_repair: [
    { id: "services", label: "Services to feature", ph: "e.g. Brakes, diagnostics, collision repair" },
    { id: "pricing", label: "A pricing or cost angle", ph: "e.g. Free estimates, brake jobs from $250" },
    { id: "trust_signal", label: "A trust or proof signal (optional)", ph: "e.g. ASE-certified techs, 3-year parts warranty" }
  ],
  legal_services: [
    { id: "case_types", label: "Practice areas and case types you handle", ph: "e.g. Personal injury, divorce and custody, estate planning" },
    { id: "fee", label: "Fee structure", ph: "e.g. No fee unless we win, free 30-minute consultation" },
    { id: "proof", label: "A proof or result signal (optional)", ph: "e.g. Recovered millions for local clients, 25 years in practice" }
  ],
  realestate: [
    { id: "market", label: "Local market detail", ph: "e.g. Median home price and how fast homes sell here" },
    { id: "focus", label: "Who you help", ph: "e.g. First-time buyers, sellers, relocations" },
    { id: "differentiator", label: "What makes you different (optional)", ph: "e.g. 15 years in this market, free home valuation" }
  ],
  mortgage: [
    { id: "products", label: "Products or services to feature", ph: "e.g. FHA loans, life insurance, retirement planning" },
    { id: "rates", label: "A rate or cost angle", ph: "e.g. We shop multiple lenders and carriers for the best rate" },
    { id: "differentiator", label: "What makes you different (optional)", ph: "e.g. Local underwriting, close in 21 days" }
  ],
  accounting: [
    { id: "services", label: "Services to feature", ph: "e.g. Small-business bookkeeping, tax prep, payroll" },
    { id: "audience", label: "Who you serve", ph: "e.g. Contractors and trades businesses under $5M revenue" },
    { id: "differentiator", label: "What makes you different (optional)", ph: "e.g. Flat monthly pricing, year-round support" }
  ],
  education: [
    { id: "subjects", label: "Subjects or tests you cover", ph: "e.g. SAT/ACT prep, algebra, reading intervention" },
    { id: "result", label: "A result you can reference", ph: "e.g. Students gain 150+ SAT points on average" },
    { id: "format", label: "Format and availability (optional)", ph: "e.g. Online 1-on-1, small groups at our center" }
  ],
  restaurant: [
    { id: "cuisine", label: "Cuisine or signature dishes", ph: "e.g. Wood-fired pizza, house-made pasta" },
    { id: "local_detail", label: "Local angle", ph: "e.g. We source produce from farms 20 miles out" },
    { id: "offer", label: "An offer or detail (optional)", ph: "e.g. Happy hour 4-6pm, private events" }
  ],
  fitness: [
    { id: "services", label: "Programs you offer", ph: "e.g. 1-on-1 coaching, small group, nutrition plans" },
    { id: "audience", label: "Who you train", ph: "e.g. Busy parents, beginners, post-injury recovery" },
    { id: "result", label: "A real client result (optional)", ph: "e.g. Helped a client lose 30 lbs in 4 months" }
  ],
  beauty: [
    { id: "services", label: "Services to feature", ph: "e.g. Balayage, gel manicures, facials" },
    { id: "specialty", label: "Your specialty", ph: "e.g. Color correction, sensitive-skin facials" },
    { id: "offer", label: "An offer or detail (optional)", ph: "e.g. First-visit discount, online booking" }
  ],
  pet_services: [
    { id: "services", label: "Services to feature", ph: "e.g. Grooming, boarding, wellness exams" },
    { id: "approach", label: "Your approach", ph: "e.g. Fear-free handling, small daily groups" },
    { id: "differentiator", label: "What makes you different (optional)", ph: "e.g. Vet on-site, webcam access for owners" }
  ],
  ecommerce: [
    { id: "product", label: "Product or category this article covers", ph: "e.g. The product or collection this article is about" },
    { id: "benefit", label: "Key benefit or differentiator", ph: "e.g. What makes it better than the alternatives" },
    { id: "proof", label: "Proof or social proof (optional)", ph: "e.g. 4.8 stars across 2,000 reviews" }
  ],
  general_blog: [
    { id: "personal_exp", label: "Your personal experience with this topic", ph: "e.g. I have tested 6 of these tools over the past year" },
    { id: "opinion", label: "Your honest opinion or contrarian take", ph: "e.g. Most advice on this is wrong, here is what I actually found" },
    { id: "result", label: "A specific result or example to include", ph: "e.g. This approach took my open rate from 18% to 31%" }
  ]
};

window.ISEO_NICHE_FIELDS_DEFAULT = [
  { id: "detail", label: "Key detail to include", ph: "A specific fact, service, or angle for this article" },
  { id: "local_detail", label: "Local or audience context (optional)", ph: "Location, season, or who this is for" },
  { id: "proof", label: "A real result or example (optional)", ph: "Something that shows real, first-hand experience" }
];

window.iseoRenderNicheFields = function (niche) {
  var container = document.getElementById("niche_fields_container");
  if (!container) return;
  function esc(s) { return String(s == null ? "" : s).replace(/&/g, "&amp;").replace(/"/g, "&quot;").replace(/</g, "&lt;"); }
  var fields = (window.ISEO_NICHE_FIELDS && window.ISEO_NICHE_FIELDS[niche]) || window.ISEO_NICHE_FIELDS_DEFAULT;
  var html = "";
  for (var i = 0; i < fields.length; i++) {
    var f = fields[i];
    html += '<div class="seo-form-field iseo-niche-field">'
      + '<label for="nd_' + esc(f.id) + '">' + esc(f.label) + "</label>"
      + '<input type="text" class="form-control" id="nd_' + esc(f.id) + '" name="nd_' + esc(f.id) + '" placeholder="' + esc(f.ph) + '">'
      + "</div>";
  }
  // Always-available freeform catch-all.
  html += '<div class="seo-form-field iseo-niche-field">'
    + '<label for="nd_extra">Anything else to include (optional)</label>'
    + '<textarea class="form-control" id="nd_extra" name="nd_extra" rows="2" placeholder="Any other specifics for this article."></textarea>'
    + "</div>";
  container.innerHTML = html;
};

/* Effective niche for API payloads: the typed-in niche when "Other" is chosen, else the selected key. */
window.iseoSelectedNiche = function () {
  var val = jQuery("#niche_select").val() || "";
  if (val === "other") {
    var custom = jQuery.trim(jQuery("#niche_other").val() || "");
    if (custom) return custom;
  }
  return val;
};

jQuery(document).ready(function () {
  var sel = document.getElementById("niche_select");
  if (!sel) return;
  var otherWrap = document.getElementById("niche_other_wrap");
  function syncNicheUI() {
    window.iseoRenderNicheFields(sel.value);
    if (otherWrap) otherWrap.style.display = sel.value === "other" ? "" : "none";
  }
  syncNicheUI();
  sel.addEventListener("change", syncNicheUI);
});
