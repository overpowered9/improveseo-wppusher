
function GenerateCustomImage() {
    jQuery("#loadingAIImage").show();
    var title = jQuery("#Image_Prompt").val();
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
            jQuery("#AI_image_div").html("<img src='"+response.data+"' alt='Uploaded Image' style='max-width: 200px;'>");
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
    var title = jQuery("#manually_promt_for_image").val();
    var formData = new FormData();
    formData.append("action", "fetch_AI_image");
    formData.append("title", title);
    formData.append("noedit", 1);
    jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            jQuery("#ai-with-prompt-image-display").html("<img src='"+response.data+"' alt='Uploaded Image' style='max-width: 200px;'>");
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
            jQuery("#showmydataindivText").val(content);
            jQuery("#showmydataindivText").css("display","block");
            
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

            var textarea = document.getElementById('showmydataindivText');
            
            // Get the plain text content of the textarea
            var plainTextContent = textarea.value;

            // Convert plain text formatting to HTML equivalents
            var htmlContent = plainTextContent.replace(/\n/g, '<br>');
            jQuery("#showmydataindiv").html(htmlContent);
            jQuery("#showmydataindiv").css("display","block");
            


            // Now you have the content of the textarea as HTML
            // console.log(htmlContent);

            // var htmlContent = convertToHtml(content);
            tinymce.activeEditor.insertContent(htmlContent);

            jQuery("#loadingAIData").hide();
        },
        error: function(error) {
            // Handle the error
            console.log(error);
        }
       
    });

});



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

function saveFinalData() {
    jQuery( "#butn" ).trigger( "click" );
}
//

$(document).ready(function() {
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
    var val = document.getElementById('exampleFormControlTextarea1').value;
    var wordCounterwithoutSpace = 0;
    for (var i = 0; i < val.length; i++) {
        if (val[i] == ' ') {
            continue;
        } else {
            wordCounterwithoutSpace++;
        }
    }
    jQuery('#countContent').html(500-wordCounterwithoutSpace+' characters remaining.');
    if(wordCounterwithoutSpace>=500) {
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
    jQuery('#countContentCallToAction').html(500-wordCounterwithoutSpace+' characters remaining.');
    if(wordCounterwithoutSpace>=500) {
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


function GenerateCustomImage() {
    jQuery("#loadingAIImage").show();
            // create AI image
        var title = jQuery("#Image_Prompt").val();
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
                jQuery("#AI_image_div").html("<img src='"+response.data+"' alt='Uploaded Image' style='max-width: 200px;'>");
                jQuery("#image-uploaded-path").val(response.data);
                jQuery("#loadingImage").hide();
            },
            error: function() {
                alert("Error uploading image.");
                jQuery("#loadingImage").hide();
            }
        });
}


function refreshAIImage() {
    jQuery("#loadingAIImage").show();
    // create AI image
    //var title = jQuery("#aigeneratedtitle").val();
    var maintitlearea = jQuery('#maintitlearea').val();
    if(maintitlearea=='') {
        var title = jQuery('#aigeneratedtitle').val();
    } else {
        var title = maintitlearea;
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
            jQuery("#ai-image-display").html("<img src='"+response.data+"' alt='Uploaded Image' style='max-width: 200px;'>");
            jQuery("#image-uploaded-path").val(response.data);
            jQuery("#loadingAIImage").hide();
        },
        error: function() {
            alert("Error uploading image.");
            jQuery("#loadingAIImage").hide();
        }
    });
}

jQuery(document).ready(function(jQuery) {

    
 

    jQuery("input[type='radio'][name='aiImage']").change(function() {
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

           // var aigeneratedtitle_op = jQuery("#aigeneratedtitle").val();
            var maintitlearea = jQuery('#maintitlearea').val();
            if(maintitlearea=='') {
                var aigeneratedtitle_op = jQuery('#aigeneratedtitle').val();
                jQuery("#manually_promt_for_image").val("Very high quality shooting from a distance, high detail, photorealistic, image resolution 2146 pixels, cinematic. The theme is "+aigeneratedtitle_op);
            }
            
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
                var maintitlearea = jQuery('#maintitlearea').val();
                if(maintitlearea=='') {
                    var title = jQuery('#aigeneratedtitle').val();
                } else {
                    var title = maintitlearea;
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
                        jQuery("#ai-image-display").html("<img src='"+response.data+"' alt='Uploaded Image' style='max-width: 200px;'>");
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
                jQuery("#manually-image-display").html("<img src='"+response+"' alt='Uploaded Image' style='max-width: 200px;'>");
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

//


