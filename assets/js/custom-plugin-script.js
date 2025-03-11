var $ = jQuery;


function getShortCodeDetails(value) {
alert(value);

//#action: improveseo_get_shortcodes
//#improveseo_shortcode_type: testimonial

    var formData = new FormData();
    formData.append("action", "improveseo_get_shortcodes");
    formData.append("improveseo_shortcode_type", value);
    jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        dataType : "json",
        success: function(response) {
            //alert(response);
            console.log(response);
            jQuery("#insertShortcodeDropdown").html(response.shortcode_html);
            // jQuery("#AI_image_div").html("<img src='"+response.data+"' alt='Uploaded Image' style='max-width: 100%'>");
            // jQuery("#image-uploaded-path").val(response.data);
            // jQuery("#loadingImage").hide();
        },
        error: function() {
            alert("Error uploading image.");
            jQuery("#loadingImage").hide();
        }
    });

}
function GenerateCustomImage() {
    jQuery("#loadingAIImage").show();

    var seed_select = jQuery("#seed_select").val();
    if(seed_select=='seed_option1') {
        var title = jQuery('#seed_keyword').val();
    } else {
        var maintitlearea = jQuery('#maintitlearea').val();
        if(maintitlearea=='') {
            var title = jQuery('#aigeneratedtitle').val();
        } else {
            var title = maintitlearea;
        }
    }

   // var title = jQuery("#Image_Prompt").val();
    var formData = new FormData();
    formData.append("action", "fetch_AI_image");
    formData.append("title", title);
    jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            jQuery("#AI_image_div").html("<img src='"+response.data+"' alt='Uploaded Image' style='max-width: 100%; margin-bottom: 45px;'>");
            jQuery("#image-uploaded-path").val(response.data);
            jQuery("#loadingImage").hide();
        },
        error: function() {
            alert("Error uploading image.");
            jQuery("#loadingImage").hide();
        }
    });
}
jQuery("#generate_i_image").on("click", function() {
    jQuery("#loadingAIImage").show();
    var seed_select = jQuery("#seed_select").val();
    if(seed_select=='seed_option1') {
        var seed_title = jQuery('#seed_keyword').val();
    } else {
        var maintitlearea = jQuery('#maintitlearea').val();
        if(maintitlearea=='') {
            var seed_title = jQuery('#aigeneratedtitle').val();
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
    jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            jQuery("#ai-with-prompt-image-display").html("<img src='"+response.data+"' alt='Uploaded Image' style='max-width: 100%; margin-bottom: 45px;'>");
            jQuery("#AI-Prompt-Image-uploaded-path").val(response.data);
            jQuery("#prompt_image_div").css("display","block");
            jQuery("#loadingAIImage").hide();
        },
        error: function() {
            alert("Error uploading image.");
            jQuery("#loadingAIImage").hide();
        }
    });
});


jQuery("#generateapivalue").on("click", function() {
    jQuery("#loadingAIData").show();
    jQuery("#for_testing_only").css("display","none");

    var AItitle= jQuery(".resultdata").text();
    jQuery("#ai_title").val(AItitle);
    console.log(AItitle);
    var inputValue = jQuery(".pop_up_form").serialize();
    //  console.log(inputValue);
    
    
    jQuery.ajax({
        type: "POST", // or "GET" depending on your form method
        url: ajaxUrl,
        dataType : "json",
        data: { value: inputValue,action: "getaaldata" }, // Send the captured value to the server
        success: function(response) {
            // Handle the success response from the server
            // jQuery("#loadingImage").hide();
            console.log(response);

            var searchData = response.data.search_data;
            var content= response.data.content ;
            var meta_title = response.data.meta_title;
            var meta_descreption = response.data.meta_descreption;
            // Set the value of the input field
            jQuery("#title").val(searchData);
        
            //jQuery(".wp-editor-area").html(content);
            //jQuery("#tinymce").html(content);
            
            jQuery("#generateapi").css("display","block");
            jQuery("#generateapivalue").val('Re-Generate AI Post');
            var AI_image = document.getElementById("AI_image");//.val();
            var Manually_image = document.getElementById("Manually_image");//.val();
            var manually_promt_image = document.getElementById("manually_promt_image");//.val();
            var Image_use = '';
            if(AI_image.checked){
              
                Image_use = jQuery("#ai-image-display").html();
             }
             if(Manually_image.checked){
               
                Image_use = jQuery("#manually-image-display").html();
             }
             if(manually_promt_image.checked){
               
                Image_use = jQuery("#ai-with-prompt-image-display").html();
             }

            Image_use = Image_use.replace('style="max-width: 100%"','style="max-width: 100%"');

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
    var modifiedHtmlContent = htmlContent;//modifiedHtmlContent1.replace(/-(.*?)-/g, '<br>jQuery1<br>');

    // Update the HTML content of the element with the modified content
   



            jQuery("#showmydataindivText").val(Image_use+'<br><br><br>'+modifiedHtmlContent);
            jQuery("#showmydataindiv1").html(Image_use+'<br><br><br>'+modifiedHtmlContent);
            
            jQuery("#showmydataindiv1").css("display","block");
            jQuery("#showmydataindiv1").css("height","600px");
           
            
            jQuery("#meta_title").val(meta_title);
            jQuery("#meta_descreption").val(meta_descreption);
            //tinymce.activeEditor.insertContent(content);
            


            // jQuery("#exampleModal").modal("hide");
            // alert(response.data.search_data);
            console.log(response.data.search_data);
            console.log(response.data.content);
            //jQuery( "#butn" ).trigger( "click" );
            jQuery("#on-page-seo").prop( "checked", true );
            jQuery("#custom-title").val(meta_title);
            jQuery("#custom-description").val(meta_descreption);

            

            // // Convert plain text formatting to HTML equivalents
            // var htmlContent = plainTextContent.replace(/\n/g, '<br>');
            // jQuery("#showmydataindiv").html(htmlContent);
            jQuery("#showmydataindiv").css("display","block");
            


            // Now you have the content of the textarea as HTML
            // console.log(htmlContent);

            // var htmlContent = convertToHtml(content);
            //tinymce.activeEditor.insertContent(htmlContent);

            jQuery("#loadingAIData").hide();
        },
        error: function(error) {
            // Handle the error
            console.log(error);
        }
       
    });

});


function textToHtml(text) {
    return '<p>' + text.replace(/\n/g, '</p><p>') + '</p>';
}


function convertToHtml(content) {
    // Escape special characters to prevent XSS vulnerabilities
    var escapedContent = escapeHtml(content);
    
    // Wrap the escaped content in a <div> to ensure it's valid HTML
    var htmlContent = '<div>' + escapedContent + '</div>';
    
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
    jQuery(".sw-btn-next").trigger( "click" );
}

var isTinyMCEInitialized = false;
var pendingContent = '';

function saveFinalData() {
    var textarea = document.getElementById('showmydataindivText');
    var plainTextContent = textarea.value;
    var metatitle = jQuery('#meta_title').val();
    var meta_descreption = jQuery('#meta_descreption').val();
    $('#on-page-seo').prop('checked', true);
    jQuery("#custom-title").val(metatitle);
    jQuery("#custom-description").val(meta_descreption);

    jQuery('#exampleModal').hide();
    jQuery('#exampleModal1').hide();
    jQuery("#butn").trigger("click");
    insertContent(plainTextContent);
}



// Function to initialize TinyMCE
function initializeTinyMCE() {
    // Initialize TinyMCE
    tinymce.init({
      selector: '#content',
      setup: function(editor) {
        editor.on('init', function() {
          // Set the flag to true once initialized
          isTinyMCEInitialized = true;
          // After initialization, insert content if there's content to insert
          if (pendingContent) {
            insertContent(pendingContent);
            // Clear pending content after insertion
            pendingContent = '';
          }
        });
      }
    });
}


function insertContent(content) {
    if (isTinyMCEInitialized) {
      // If TinyMCE is initialized, insert content
      tinyMCE.activeEditor.setContent('');
      tinymce.activeEditor.insertContent(content);
    } else {
      // If TinyMCE is not initialized, initialize it and store the content to be inserted
      if (!pendingContent) {
        // Initialize TinyMCE only if it's not already initialized
        initializeTinyMCE();
      }
      // Store the content to be inserted
      pendingContent = content;
      tinymce.activeEditor.insertContent(pendingContent);
    }
}


//

jQuery(document).ready(function() {

    resetSmartWizard();
});


function generateAIMetaJs() {
    
    var maintitlearea = jQuery('#maintitlearea').val();
    if(maintitlearea=='') {
        var aigeneratedtitle = jQuery('#aigeneratedtitle').val();
    } else {
        var aigeneratedtitle = maintitlearea;
    }
    
    
    var ajaxUrl = '".home_url("/")."wp-admin/admin-ajax.php';
    var seedkeyword = jQuery('#seed_keyword').val();
    jQuery.post(ajaxUrl, {
        action: 'generateAIMeta',
        aigeneratedtitle: aigeneratedtitle,
        seedkeyword: seedkeyword,
    })
    .success(function(response) {
        console.log(response);
        jQuery('#meta_title').val(response.data.title);
        jQuery('#meta_descreption').val(response.data.descreption);
    });
}

function countContent() {
    var val = document.getElementById('exampleFormControlTextarea').value;
    var wordCounterwithoutSpace = 0;
    for (var i = 0; i < val.length; i++) {
        if (val[i] == ' ') {
            continue;
        } else {
            wordCounterwithoutSpace++;
        }
    }
    jQuery('#countContent').html(1500-wordCounterwithoutSpace+' characters remaining.');
    if(wordCounterwithoutSpace>=1500) {
        return false;
    }
}


function countContent1() {
    var val = document.getElementById('exampleFormControlTextarea1').value;

   
    var wordCounterwithoutSpace = 0;
    for (var i = 0; i < val.length; i++) {
        if (val[i] == ' ') {
            continue;
        } else {
            wordCounterwithoutSpace++;
        }
    }
    jQuery('#countContent1').html(1500-wordCounterwithoutSpace+' characters remaining.');
    if(wordCounterwithoutSpace>=1500) {
        return false;
    }
}


function countContentCallToAction() {
    var val = document.getElementById('call_to_action').value;
    var wordCounterwithoutSpace = 0;
    for (var i = 0; i < val.length; i++) {
        if (val[i] == ' ') {
            continue;
        } else {
            wordCounterwithoutSpace++;
        }
    }
    jQuery('#countContentCallToAction').html(1000-wordCounterwithoutSpace+' characters remaining.');
    if(wordCounterwithoutSpace>=1000) {
        return false;
    }
}


function LimitText(ref,iLength,textareaid) {
    if(ref.value.length > iLength) {
        if(textareaid==1) {
            jQuery('#countContent').html('Text length cannot be greater than ' + iLength + ' characters Current length is: ' + ref.value.length);
          } else if(textareaid==2) {
            jQuery('#countContentCallToAction').html('Text length cannot be greater than ' + iLength + ' characters Current length is: ' + ref.value.length);
          } else if(textareaid==3) {
            jQuery('#error_manually_promt_for_image').html('Text length cannot be greater than ' + iLength + ' characters Current length is: ' + ref.value.length);
          } else {
          }

      ref.focus();
      
    } else {
        jQuery('#countContent').html('');
        jQuery('#countContentCallToAction').html('');
        jQuery('#error_manually_promt_for_image').html('');
    }
}





function refreshAIImage() {
    jQuery("#loadingAIImage").show();
    // create AI image
    //var title = jQuery("#aigeneratedtitle").val();
    var seed_select = jQuery("#seed_select").val();
    if(seed_select=='seed_option1') {
        var title = jQuery('#seed_keyword').val();
    } else {
        var maintitlearea = jQuery('#maintitlearea').val();
        if(maintitlearea=='') {
            var title = jQuery('#aigeneratedtitle').val();
        } else {
            var title = maintitlearea;
        }
    }
    

    var formData = new FormData();
    formData.append("action", "fetch_AI_image");
    formData.append("title", title);
    jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            jQuery("#ai-image-display").html("<img src='"+response.data+"' alt='Uploaded Image' style='max-width: 100%; margin-bottom: 45px;'>");
            jQuery("#image-uploaded-path").val(response.data);
            jQuery("#loadingAIImage").hide();
        },
        error: function() {
            alert("Error uploading image.");
            jQuery("#loadingAIImage").hide();
        }
    });
}

function getCookie(cname) {
    let name = cname + "=";
    let decodedCookie = decodeURIComponent(document.cookie);
    let ca = decodedCookie.split(';');
    for(let i = 0; i <ca.length; i++) {
      let c = ca[i];
      while (c.charAt(0) == ' ') {
        c = c.substring(1);
      }
      if (c.indexOf(name) == 0) {
        return c.substring(name.length, c.length);
      }
    }
    return "";
  }

jQuery(document).ready(function(jQuery) {

    jQuery("input[type='radio'][name='aiImage']").change(function() {
        jQuery("#loadingImage").show();
        if (this.value == 'Manually_image') {
            /* 2 section display none */
            jQuery("#AI_image_div").css("display","none");
            jQuery("#prompt_image_div").css("display","none");
            jQuery("#Prompt_to_create_Dalle_Image").css("display","none");
            jQuery("#Manually_image_div").css("display","block");
           
        } else if(this.value == 'manually_promt_image') {

            jQuery("#AI_image_div").css("display","none");

            
            var aipromtval = jQuery("#AI-Prompt-Image-uploaded-path").val();
            if(aipromtval!='') {
                jQuery("#prompt_image_div").css("display","block");
                
            }
           
            jQuery("#Prompt_to_create_Dalle_Image").css("display","block");
            jQuery("#Manually_image_div").css("display","none");

            var seed_select = jQuery("#seed_select").val();
            if(seed_select=='seed_option1') {
                var title = jQuery('#seed_keyword').val();
            } else {
                var maintitlearea = jQuery('#maintitlearea').val();
                if(maintitlearea=='') {
                    var title = jQuery('#aigeneratedtitle').val();
                } else {
                    var title = maintitlearea;
                }
            }
            jQuery("#manually_promt_for_image").val("Please wait while we prepare the prompts for you....");

            var formData = new FormData();
            formData.append("action", "getPromptForImages");
            formData.append("title", title);
            jQuery.ajax({
                url: ajaxurl,
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    
                    //var aigeneratedtitle_op = jQuery('#aigeneratedtitle').val();
                jQuery("#manually_promt_for_image").val("You should come up with the cover image for an article. The image should be a very high quality shooting from a distance, high detail, photorealistic, image resolution is  800 pixels, cinematic. Do not include any text on the image. Using the following information generate an image. "+response.data+"");


                },
                error: function() {
                    alert("Error uploading image.");
                    jQuery("#loadingAIImage").hide();
                }
            });


           // var aigeneratedtitle_op = jQuery("#aigeneratedtitle").val();
           /* var maintitlearea = jQuery('#maintitlearea').val();
            if(maintitlearea=='') {
                var AudienceData = getCookie('AudienceData');
                var aigeneratedtitle_op = jQuery('#aigeneratedtitle').val();
                jQuery("#manually_promt_for_image").val("You are provided a word or phrase that is searched by the reader, and the audience data of the reader, including demographic information, tone preferences, reading level preference and emotional needs/pain points. You should come up with the cover image for the article that will be engaging and interesting for the reader who is described in the audience data and search provided word or phrase. Image should be Very high quality shooting from a distance, high detail, photorealistic, image resolution 2146 pixels, cinematic. Using the following information generate an image.<br> Main keyword: seed-keyword Title of the article is '"+aigeneratedtitle_op+"' <br> Audience data:  '"+AudienceData+"'");
            }*/
            
            jQuery("#manually_promt_for_image").css("display","block");
            jQuery("#AI_image_div").css("display","none");
            

        } else if (this.value == 'AI_image') {


            jQuery("#AI_image_div").css("display","block");
            jQuery("#prompt_image_div").css("display","none");
            jQuery("#Prompt_to_create_Dalle_Image").css("display","none");
            jQuery("#Manually_image_div").css("display","none");


            var image_uploaded_path = jQuery("#AI-Image-uploaded-path").val();
            if(image_uploaded_path=='') {
                jQuery("#loadingAIImage").show();
                // create AI image
                //var title = jQuery("#aigeneratedtitle").val();
                var seed_select = jQuery("#seed_select").val();
                if(seed_select=='seed_option1') {
                    var title = jQuery('#seed_keyword').val();
                } else {
                    var maintitlearea = jQuery('#maintitlearea').val();
                    if(maintitlearea=='') {
                        var title = jQuery('#aigeneratedtitle').val();
                    } else {
                        var title = maintitlearea;
                    }
                }

                var formData = new FormData();
                formData.append("action", "fetch_AI_image");
                formData.append("title", title);
                jQuery.ajax({
                    url: ajaxurl,
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        jQuery("#ai-image-display").html("<img src='"+response.data+"' alt='Uploaded Image' style='max-width: 100%; margin-bottom: 45px;'>");
                        jQuery("#AI-Image-uploaded-path").val(response.data);
                        jQuery("#loadingAIImage").hide();
                    },
                    error: function() {
                        alert("Error uploading image.");
                        jQuery("#loadingAIImage").hide();
                    }
                });
            }
        }
        jQuery("#loadingImage").hide();
    });
});

jQuery(document).ready(function(jQuery) {
    jQuery("#upload-image-button").on("change", function() {
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
            success: function(response) {
                jQuery("#manually-image-display").html("<img src='"+response+"' alt='Uploaded Image' style='max-width: 100%; margin-bottom: 100px'>");
                jQuery("#manually-image-uploaded-path").val(response);
                jQuery("#loadingImage").hide();
            },
            error: function() {
                alert("Error uploading image.");
                jQuery("#loadingImage").hide();
            }
        });
    });
});


// function saveFinalDataForMultiple() {

// }

jQuery(document).ready(function($) {
    $('#pop_up_multi_form').submit(function (event) { 
        event.preventDefault();                 
        var form = document.getElementById('pop_up_multi_form'); 
        var formData = new FormData(form);
        formData.append("action", "multiPostData"); 
    
        $.ajax({ 
            url: ajaxurl, 
            method: 'POST', 
            data: formData, 
            processData: false, 
            contentType: false, 
            success: function (response) {  
                console.log(response);
                if(response.data.status=='success')  {
                    alert('Your form has been sent successfully.'); 
                    window.location.replace(response.data.linkredirect);
                } else {
                    alert(response.data.message); 
                }                  
            }, 
            error: function (xhr, status, error) {                        
                alert(response.data.message); 
                console.error(error); 
            } 
        }); 
    }); 
}); 

function resetSmartWizard() {
    // Destroy the existing SmartWizard instance
    jQuery('#smartwizard').smartWizard('destroy');

    // Reinitialize the SmartWizard plugin
    jQuery('#smartwizard').smartWizard({
        // Configuration options
        selected: 0,
        theme: 'default', // theme for the wizard, related CSS need to include for other than default theme
        transitionEffect: 'fade', // Effect on navigation, none/fade/slide/slideleft
        enableURLhash: false, // Enable selection of the step based on url hash
        toolbarSettings: {
            toolbarPosition: 'bottom', // none, top, bottom, both
            toolbarButtonPosition: 'right', // left, right
            showNextButton: true, // show/hide a Next button
            showPreviousButton: true, // show/hide a Previous button
        }
    });

    // Go to the first step
    jQuery('#smartwizard').smartWizard('reset');
}



jQuery("#seed_select").on("change", function() {
            
    var seedtype = jQuery(this).val();
    
    if (seedtype!="seed_option1")
    {
        jQuery("#loader").show();
        jQuery("#gettitle").css({display: "flex"});
    }
    else
    {
        jQuery("#loader").hide();
        jQuery("#gettitle").hide();
    }
    
    var seedkeyword = jQuery("#seed_keyword").val();
    var contenttype = jQuery("#cotnt_type").val();
    
    // When btn is pressed.
    // jQuery("#more_posts").attr("disabled",true);

    // Disable the button, temp.
    jQuery.post(ajaxUrl, {
        action: "getGPTdata",
        // offset: (page * ppp) + 1,
        seedtype: seedtype,
        seedkeyword: seedkeyword,
        contenttype: contenttype,
    })
    .success(function(data) {
        console.log(""+data);
        // page++;
        // jQuery("#ajax-posts").append(posts);
        // CHANGE THIS!
        // jQuery("#more_posts").attr("disabled", false);

        // alert("????"+posts+">>>");
        // if (posts=="")
        // {
           // jQuery("#more_posts").attr("disabled", false);
        // 	jQuery("#more_posts").html("End of results");
        // 	// jQuery("#more_posts").hide();
        // }
        
        jQuery("#loader").hide();

        jQuery("#maintitle").html(" <div class=\'resultdata\'><textarea id=\'maintitlearea\' name=\'maintitlearea\' class=\'form-control\' rows=\'3\' cols=\'70\'>"+data+"</textarea></div>");
        jQuery("#aigeneratedtitle").val(data);


        var AudienceData = getCookie('AudienceData');
                //var aigeneratedtitle_op = jQuery('#aigeneratedtitle').val();
                //jQuery("#manually_promt_for_image").val("You are provided a word or phrase that is searched by the reader, and the audience data of the reader, including demographic information, tone preferences, reading level preference and emotional needs/pain points. You should come up with the cover image for the article that will be engaging and interesting for the reader who is described in the audience data and search provided word or phrase. Image should be Very high quality shooting from a distance, high detail, photorealistic, image resolution 2146 pixels, cinematic. Using the following information generate an image.<br> Main keyword: seed-keyword Title of the article is '"+data+"' <br> Audience data:  '"+AudienceData+"'");


        //jQuery("#manually_promt_for_image").val("Very high quality shooting from a distance, high detail, photorealistic, image resolution 2146 pixels, cinematic. The theme is `"+data+"`");
        // alert(data);
    });
});

    function SaveResultsButton() {
        var keyword_id = jQuery("#project_name").val();
        var keyword_list = jQuery("#keyword_list").val();
        var content_type = jQuery("#contenttype").val();

        jQuery.post(ajaxUrl, {
            action: "multi_form_data",
            keyword_id: keyword_id,
            keyword_list: keyword_list,
            content_type: content_type,
        })
        .success(function(data) {   
            jQuery("#exampleFormControlTextarea1").text( data );
            jQuery("#exampleFormControlTextarea1").val( data );
            // alert(data);
        });
    };

    function saveProject() {
        var seed_keyword = jQuery('#input').val();
        var results = jQuery('#output').val();
        if (!seed_keyword || !results) {
            return;
        }
        jQuery.ajax({
            url: ajaxUrl,
            method: 'POST',
            dataType: "json",
            data: {
                action: 'sw_saved_search_results_keyword',
                proj_name: seed_keyword,
                search_results: results
            },
            success: function(response) {
                // console.log(response);
                if (response.data.status == 'success') {

                    var select = document.getElementById('keyword_list_name');

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
                    jQuery('#keyword_list_name').val(response.data.id);
                    jQuery('#keyword_list').val(response.data.search_results);
                    jQuery('#keyword_list_container').show();
                    jQuery('#create_keyword_container').hide();
                    // alert('Data saved successfully!');
                } else {
                    alert('An error occurred: ' + response.data.status );
                }
            },
            error: function(xhr, status, error) {
                alert('AJAX error: ' + error);
            }
        });
    };

    jQuery(document).ready(function($) {
        jQuery('.multi-upload-button').click(function(event) {
            event.preventDefault();
            const button = jQuery(this);
            const hiddenField = button.prev('input[type="hidden"]');
            let hiddenFieldValue = hiddenField.val().split(',');
    
            const customUploader = wp.media({
                title: 'Insert images',
                library: {
                    type: 'image'
                },
                button: {
                    text: 'Use these images'
                },
                multiple: true
            }).on('select', function() {
                let selectedImages = customUploader.state().get('selection').map(item => {
                    item = item.toJSON();
                    return item;
                });
                selectedImages.forEach(image => {
                    jQuery('.multi-upload-gallery').append('<li data-id="' + image.id + '"><img src="' + image.url + '" alt="Image ' + (hiddenFieldValue.length + 1) + '" width="80" height="80"><a href="#" class="multi-upload-gallery-remove" style="display: inline;">&#215;</a></li>');
                    hiddenFieldValue.push(image.id);
                });
                jQuery('.multi-upload-gallery').sortable('refresh');
                hiddenField.val(hiddenFieldValue.join(','));
            }).open();
  
        });

     
    
        jQuery(document).on('click', '.multi-upload-gallery-remove', function(event) {
            event.preventDefault();
            const button = jQuery(this);
            const imageId = button.parent().data('id').toString();
            const hiddenField = jQuery('input[name="my_field"]');
            let hiddenFieldValue = hiddenField.val().split(',');
    
            button.parent().remove();
            const index = hiddenFieldValue.indexOf(imageId);
            if (index != -1) {
                hiddenFieldValue.splice(index, 1);
            }
            hiddenField.val(hiddenFieldValue.join(','));
            jQuery('.multi-upload-gallery').sortable('refresh');
        });
    
        // Make the gallery sortable
        jQuery('.multi-upload-gallery').sortable({
            items: 'li',
            cursor: '-webkit-grabbing',
            scrollSensitivity: 40,
            stop: function(event, ui) {
                ui.item.removeAttr('style');
    
                let sort = [];
                const container = jQuery(this);
                container.find('li').each(function() {
                    sort.push(jQuery(this).attr('data-id'));
                });
                container.parent().next('input[type="hidden"]').val(sort.join(','));
            }
        });
    });


// my js start

function SeedShow() {
    jQuery('#manually_image_div').show();
    jQuery('#multiple_image_div').hide();
}
function SelectexisitingHide() {
    jQuery('#multiple_image_div').show();
    jQuery('#manually_image_div').hide();
}

    jQuery('#create_keyword').on('change', function() {
        var selectedOption = jQuery(this).val();
        jQuery('#copy_paste_container').empty();
        jQuery('#google_suggestion_container').empty();
        jQuery('#ai_suggestion_container').empty();

        if (selectedOption == 'copy_paste') {
            jQuery('#copy_paste_container').append(
                `<section class="form-wrap">
                    <div class="PostForm mt-3">
                        <div class="BasicForm__row">
                            <div class="input-group">
                                <label class="form-label">Title</label>
                                <input type="text" id="input" placeholder="" class="sw-project-name keyword_input form-control" value="" />
                            </div>
                        </div>
                        <div class="BasicForm__row">
                            <label class="form-label">Keywords</label>
                            <textarea id="output" rows="5" class="textarea-control sw-output-ta keyword_input" placeholder=""></textarea>
                        </div>
                        <div style="text-align: end;" class="BasicForm__row mb-3">
                                <input type="button" onclick="return saveProject()" class="btn btn-outline-primary" value="Save Results">
                            </div>
                    </div>
                </section>`
            );
        } else if (selectedOption == 'google_suggestion') {
            jQuery('#google_suggestion_container').append(
                `<section class="form-wrap">
                        <div class="PostForm mt-3">
                            <div class="BasicForm__row">
                                <div class="input-group">
                                    <label class="form-label">Seed Keyword</label>
                                    <input type="text" id="input" placeholder="" class="sw-project-name keyword_input form-control" value="" />
                                </div>
                            </div>
                            <div class="BasicForm__row">
                                <label class="form-label">Results</label>
                                <textarea id="output" rows="5" class="textarea-control sw-output-ta keyword_input" placeholder=""></textarea>
                            </div>
                            <div style="text-align: end;" class="BasicForm__row mb-3">
                                <input id="startjob" onclick="generate();" type="button" class="btn btn-outline-primary mr-2 mb-2 mb-sm-0" value="Generate Keywords!">
                                <input type="button" class="clear-search-results btn btn-outline-primary mr-2 mb-2 mb-sm-0" value="Clear Results">
                                <input type="button" onclick="return saveProject()" class="btn btn-outline-primary" value="Save Results">
                            </div>
                        </div>
                </section>`
            );
        } else if (selectedOption == 'ai_create_keyword') {
            jQuery('#ai_suggestion_container').append(
                `<div class="PostForm mt-3">
                    <div class="BasicForm__row">
                        <div class="input-group">
                            <label class="form-label">Seed Keyword</label>
                            <input type="text" id="input" placeholder="" class="sw-project-name keyword_input form-control" value="" />
                        </div>
                    </div>
                    <div class="BasicForm__row">
                        <label class="form-label">Results</label>
                        <textarea id="output" rows="5" class="textarea-control sw-output-ta keyword_input" placeholder=""></textarea>
                    </div>
                    <div style="text-align: end;" class="BasicForm__row mb-3">
                        <input id="startjob" type="button" class="btn btn-outline-primary mr-2 mb-2 mb-sm-0" value="Ai Generate Keywords!">
                        <input type="button" class="clear-search-results btn btn-outline-primary mr-2 mb-2 mb-sm-0" value="Clear Results">
                        <input type="button" onclick="return saveProject()" class="btn btn-outline-primary" value="Save Results">
                    </div>
                </div>`
            );
        }
    });

// my js end

// file js start 02-06-24

jQuery(document).ready(function(){
       
    jQuery('#gettitle').css({display: 'none'});
    
    jQuery('#reload').on('click', function(){
           jQuery('#seed_select').trigger('change');
    });
    
    jQuery('input[name=\"content_type\"]').on('click', function(){
           jQuery('#seed_select').trigger('change');
    });
    

     jQuery('#cotnt_type').on('change', function(){
           jQuery('#seed_select').trigger('change');
    });


    /* jQuery('select#shortcodetype').on('change', function() {
           jQuery('#popupcontainer input[type=checkbox]').prop('checked', false);
       }); */

       jQuery('#popupcontainer button').click(function(){
           var getid = jQuery(this).attr('id');
           //console.log(getid);
           if (jQuery('input[type=checkbox].option_'+getid).prop('checked')==true)
           {
               jQuery('input[type=checkbox].option_'+getid).prop('checked',false);
               jQuery('#getpopupselected .result_'+getid).remove();
           }
           else
           {
               jQuery('input[type=checkbox].option_'+getid).prop('checked', true);
               //console.log(jQuery('.option_'+getid).prop('checked'));
               
               if (jQuery('.option_'+getid).prop('checked')==true)
               {
                   var selectedshortcode = '<div class=\"result_'+getid+'\">'+jQuery('.option_'+getid).val()+'</div>';
                   //console.log('ccc'+selectedshortcode);
                   jQuery('#getpopupselected').append(selectedshortcode);
               }
               
           }
       });

       jQuery('#smartwizard').on('leaveStep', function(e, anchorObject, stepNumber, stepDirection) {
         if (stepDirection === 'forward') {
             var aigeneratedtitle_op = jQuery('#maintitlearea').val();
             if(stepNumber==0) {

                var AudienceData = getCookie('AudienceData');
                //var aigeneratedtitle_op = jQuery('#aigeneratedtitle').val();
                jQuery("#manually_promt_for_image").val("You are provided a word or phrase that is searched by the reader, and the audience data of the reader, including demographic information, tone preferences, reading level preference and emotional needs/pain points. You should come up with the cover image for the article that will be engaging and interesting for the reader who is described in the audience data and search provided word or phrase. Image should be Very high quality shooting from a distance, high detail, photorealistic, image resolution 2146 pixels, cinematic. Using the following information generate an image.<br> Main keyword: seed-keyword Title of the article is '"+aigeneratedtitle_op+"' <br> Audience data:  '"+AudienceData+"'");


                 //jQuery('#manually_promt_for_image').val('Very high quality shooting from a distance, high detail, photorealistic, image resolution 2146 pixels, cinematic. The theme is `'+aigeneratedtitle_op+'`');
             }
            
            // Perform actions related to the first step
            // For example:
            //console.log('First step: ' + firstStep);

             var seed_keyword = jQuery('#seed_keyword').val();
             var seed_select = jQuery('#seed_select').val();
             var step1_error = 0;
             if(seed_keyword=='') {
                 document.getElementById('error_seed_keyword').innerText = 'Please enter seed keyword.';
                 step1_error++;
             } else {
                 jQuery('#error_seed_keyword').html('');
             }

             if(seed_select=='') {
                 document.getElementById('error_seed_select').innerText = 'Please select title type.';
                 step1_error++;
             } else {
                 jQuery('#error_seed_select').html('');
                 if(seed_select!='seed_option1')  {
                     var checkbox = document.getElementById('checkbox_need');
                     if (checkbox.checked) {
                         return true;
                     } else {
                         var errorSpan = document.createElement('span');
                             errorSpan.innerText = 'You need to check the checkbox if you want to use the AI-generated title as the title';
                             errorContainer.innerHTML = ''; // Clear previous error messages
                             errorContainer.appendChild(errorSpan); // Append the error message
                         return false;
                     }
                 }
             }
         
            if(step1_error==0) {
                return true;
            } else {
                return false;
            }
            //alert('Next button clicked');
            // Your condition to prevent moving to the next step
            // if (someConditionIsNotMet) {
                 //return false;
            // }
        } else if (stepDirection === 'backward') {
            //alert('Previous button clicked');
            // Your condition to prevent moving to the previous step
            // if (someConditionIsNotMet) {
                // return false;
            // }
        }
    });

    jQuery('#smartwizard_multi').smartWizard({
     selected: 0,
        theme: 'default', // theme for the wizard, related CSS need to include for other than default theme
        transitionEffect: 'fade', // Effect on navigation, none/fade/slide/slideleft
        enableURLhash: false, // Enable selection of the step based on url hash
        toolbarSettings: {
            toolbarPosition: 'bottom', // none, top, bottom, both
            toolbarButtonPosition: 'right', // left, right
            showNextButton: true, // show/hide a Next button
            showPreviousButton: true, // show/hide a Previous button
        }
    });

 jQuery('#smartwizard_multi').on('leaveStep', function(e, anchorObject, stepNumber, stepDirection) {
        if (stepDirection == 'forward') {
            var aigeneratedtitle_op = jQuery('#maintitlearea').val();
            if(stepNumber==0) {

                var AudienceData = getCookie('AudienceData');
               // var aigeneratedtitle_op = jQuery('#aigeneratedtitle').val();
                jQuery("#manually_promt_for_image").val("You are provided a word or phrase that is searched by the reader, and the audience data of the reader, including demographic information, tone preferences, reading level preference and emotional needs/pain points. You should come up with the cover image for the article that will be engaging and interesting for the reader who is described in the audience data and search provided word or phrase. Image should be Very high quality shooting from a distance, high detail, photorealistic, image resolution 2146 pixels, cinematic. Using the following information generate an image.<br> Main keyword: seed-keyword Title of the article is '"+aigeneratedtitle_op+"' <br> Audience data:  '"+AudienceData+"'");


                //jQuery('#manually_promt_for_image').val('Very high quality shooting from a distance, high detail, photorealistic, image resolution 2146 pixels, cinematic. The theme is `'+aigeneratedtitle_op+'`');
            }

            if(stepNumber==4) {
                // var keywordCount = (jQuery('#keyword_list').val()).split('\n').length;
				// var keywordMin = keywordCount * 3;
				// var keywordTime = (keywordMin / 60).toFixed(2);

				// jQuery('#keywordcounts').text(keywordCount);
				// jQuery('#keywordtime').text(keywordTime);

                // Get the value of the textarea
                var text = jQuery('#keyword_list').val();

                // Split the text into lines
                var lines = text.split('\n');

                // Filter out empty lines
                var nonEmptyLines = lines.filter(function(line) {
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

            if(stepNumber==6) {
                if (jQuery('#schedule_posts_input_wise').is(':checked')) {
                    var numberOfPosts = jQuery('#number_of_post_schedule').val();
                   // var frequency = $('#schedule_frequency').val();

                    if (numberOfPosts=='') {
                       
                        document.getElementById('error_number_of_post_schedule').innerText = 'Please enter the number of post.';
                        return false;
                    } else {
                        return true;
                    }
                }
            }


            if(stepNumber==8) {

                jQuery(".category_improveseo input[type='checkbox']").each(function () {
                    var checkboxValue = $(this).val(); // Get the value of the checkbox
                    var isChecked = $(this).prop("checked"); // Check if it's checked
        
                    // Find the corresponding checkbox in .category_improveseo_bulk and set its checked state
                    jQuery(".category_improveseo_bulk input[type='checkbox'][value='" + checkboxValue + "']").prop("checked", isChecked);
                });
            }
   
         // Perform actions related to the first step
         // For example:
         //console.log('First step: ' + firstStep);
            var project_name = jQuery('#keyword_list_name').val();
            // var cotnt_type = jQuery('#cotnt_type').val();
            var existing_select = jQuery('#existing_select').val();
            var step1_error = 0;
         
             if (project_name == '') {
                     document.getElementById('error_project_name').innerText = 'Please Select Project Name.';
                     step1_error++;
                 } else {
                     jQuery('#error_project_name').html('');
                 }
         
                 if (existing_select == '') {
                     document.getElementById('error_existing_select').innerText = 'Please Select Contant Type.';
                     step1_error++;
                 }
                 else {
                    jQuery('#error_existing_select').html('');
                 } 
         
            if(step1_error==0) {
                return true;
            } else {
                return false;
            }
            //alert('Next button clicked');
            // Your condition to prevent moving to the next step
            // if (someConditionIsNotMet) {
                 //return false;
            // }
        } else if (stepDirection === 'backward') {
            //alert('Previous button clicked');
            // Your condition to prevent moving to the previous step
            // if (someConditionIsNotMet) {
                // return false;
            // }
        }
    });

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

    jQuery('#popupcontainer button').click(function(){
     //   jQuery('button').removeClass('selected');
     //   jQuery(this).addClass('selected');
     jQuery(this).toggleClass('selected');
    });
});

// jQuery(document).ready(function(){
// 	  jQuery(\"input[name$='keyword_selection']\").click(function() {
// 	  	var test = jQuery(this).val();
// 	  	console.log(test);

// 	  	jQuery('div.desc').hide();
//         jQuery('#' + test).show();

//         jQuery(\"#seed_select option[value='']\").attr('selected', true);
//         // jQuery('textarea[name=\"seed_keyword\"]').hide();

//         jQuery(\"#existing_select option[value='']\").attr('selected', true)
//         jQuery('.show_lists').hide();
//   		jQuery('textarea[name=\"existing_keyword\"]').hide();
// 	  });

// 	  jQuery('select[name=\"select_exisiting_options\"]').change(function(){
// 	  	var selvalue = jQuery(this).val();

// 	  	if (selvalue=='')
// 	  	{
// 	  		jQuery('.show_lists').hide();
// 	  		jQuery('textarea[name=\"existing_keyword\"]').hide();
// 	  	}
// 	  	else
// 	  	{
// 	  		jQuery('.show_lists').show();
// 	  		jQuery('textarea[name=\"existing_keyword\"]').show();
// 	  	}
// 	  });
//   });



// jQuery(document).ready(function(){
//     jQuery("#language").on("change", function() {
        
//         var language = jQuery(this).val();
        
//         if ((language=="english_us") || (language=="english_uk")) {
//         console.log(language);
//             jQuery("#langerror").html("");
//             jQuery(".sw-btn-next").removeClass("disabled");
//             jQuery(".sw-btn-next").removeAttr("disabled", "disabled");
//             //return false;
//         }
//         else
//         {
//         console.log(language);
//             jQuery("#langerror").html("Application API does not support this language");
//             jQuery(".sw-btn-next").addClass("disabled");
//             jQuery(".sw-btn-next").attr("disabled", "disabled");
//             //return true;
//         }
//     });
// });




function addcategory() {
    var fData = jQuery("#add_category_1").val();
    // console.log(fData);
    //add_category_form
    jQuery.ajax({
        type: 'POST',
        url: ajaxUrl,
        dataType: "json",
        data: {action: 'add_category_form', "fData":fData},
        success: function(response) {
            console.log(response);
            if (response.success) {
                alert(response.message);
                jQuery(".cta-check").append(response.result);
                jQuery("#add_category_1").val('');
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error: ' + error);
        }
    });
}

jQuery("#post_size").on("change", function() {
 // Get the selected option value
 var selectedOption = jQuery(this).val();

 // Display the selected option in the h2 element
 jQuery("#post_size_select").val(selectedOption);
});


jQuery("#post_size_bulk").on("change", function() {
    // Get the selected option value
    var selectedOption = jQuery(this).val();
   
    // Display the selected option in the h2 element
    jQuery("#post_size_select_bulk").val(selectedOption);
});
// file js end 02-06-24

jQuery("input[type='radio'][name='assigning_authors']").change(function() {
        var inputValue = jQuery(this).attr("value");

        if(inputValue == 'assigning_authors'){
            jQuery('#author_number').show();
            jQuery('#authors_number').hide();
        }
        else if(inputValue == 'assigning_multi_authors'){
            jQuery('#authors_number').show();
            jQuery('#author_number').hide();
        }
    });


    jQuery("input[type='radio'][name='schedule_posts']").change(function() {
        var inputValue = jQuery(this).attr("value");

        if(inputValue == 'schedule_posts_input_wise'){
            jQuery('#number_of_post_schedule_box').show();
        } else {
            jQuery('#number_of_post_schedule_box').hide();
        }
        
    });


    // upload.js
jQuery(document).ready(function($) {
    jQuery('#uploadBtn').on('click', function(e) {
        e.preventDefault();

        let formData = new FormData();
        let files = $('#images')[0].files;

        for (let i = 0; i < files.length; i++) {
            formData.append('images[]', files[i]);
        }
        formData.append('action', 'my_plugin_upload');
       

        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                $('#response').html(response);
                $('#hiddenInputs').empty();
                // Append hidden input fields for each URL
                response.data.forEach(function(url) {
                    $('#hiddenInputs').append(
                        '<input type="hidden" name="uploaded_images[]" value="' + url + '">'
                    );
                });
            },
            error: function(err) {
                $('#response').html('<p>An error occurred!</p>');
            }
        });
    });

    jQuery('#images').on('change', function() {
        let files = this.files;
        jQuery('#preview').html('');
        for (let i = 0; i < files.length; i++) {
            let reader = new FileReader();
            reader.onload = function(e) {
                $('#preview').append('<img src="' + e.target.result + '" width="100" style="margin:5px;">');
            };
            reader.readAsDataURL(files[i]);
        }
    });
});



   

