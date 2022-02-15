(function($){
    $(document).ready(function(){
        
        /* $( "#improveseo_shortcode_text" ).autocomplete({
            source: form_ajax_vars.autocomplete_src,
            select: function(event, ui){
                tinymce.activeEditor.insertContent(ui.item.desc);
                $.modal.close();
                tinyMCE.activeEditor.focus();
            },
        }); */
        tinymce.activeEditor.on('keydown', function(e) {
            if(!$.modal.isActive()){

                
                var full_content = tinyMCE.activeEditor.selection.getStart().textContent;
                var length = full_content.length;
                if(e.keyCode==50){
                    
                    var last_char = full_content.charAt((length-1));
                    if($.trim(last_char)==""){
                        e.preventDefault();
                        $("#shortcode_popup").modal({
                            escapeClose: false,
                            fadeDuration: 1000,
                            fadeDelay: 0.35,
                            keyboard: false,
                            focus: true
                        });
                    }
                }

                /* if(e.keyCode==50){
                    e.preventDefault();
                    $('#improveseo_shortcode_text').val('@');
                    $("#all_shortcode_popup").modal({
                        escapeClose: false,
                        fadeDuration: 1000,
                        fadeDelay: 0.35,
                        keyboard: false,
                        focus: true
                    });
                    
                } */
            }
        });
        $('#shortcode_popup').on($.modal.OPEN, function(event, modal) {
            $('#improveseo_shortcode_type').focus();
            $('#improveseo_shortcode_type').val('');
            $('#improveseo_shortcode').html('');
            $('#improveseo_shortcode').attr('disabled', 'disabled');
            $('#improveseo_shortcode_add_btn').parent().addClass('hidden');
            $('#improveseo_shortcode_error').addClass('hidden');
        });

        $('#all_shortcode_popup').on($.modal.OPEN, function(event, modal) {
            $('#improveseo_shortcode_text').focus();
        });
        $('#improveseo_shortcode_type').change(function(e){
            $('#improveseo_shortcode').attr('disabled', 'disabled');
            $('#improveseo_shortcode_add_btn').parent().addClass('hidden');
            $('#improveseo_shortcode_error').addClass('hidden');
            var improveseo_shortcode_type = $(this).val();
            jQuery.ajax({
                url : form_ajax_vars.ajax_url,
                type: "POST",
				dataType: 'json',
                data : ({
                    action : 'improveseo_get_shortcodes',
                    improveseo_shortcode_type : improveseo_shortcode_type,
                }),
                success : function(response) {
                    if(response.status=="success"){
                        $('#improveseo_shortcode').html(response.shortcode_html);
                        $('#improveseo_shortcode').removeAttr('disabled');
                        $('#improveseo_shortcode_add_btn').parent().removeClass('hidden');
                    }else{
                        $('#improveseo_shortcode_error').removeClass('hidden');
                    }
                }
            });
        });
        
        $('#improveseo_shortcode_add_btn').click(function(e){
            e.preventDefault();
            var text = '';
            var improveseo_shortcode_type = $('#improveseo_shortcode_type').val();
            var improveseo_shortcode_id = $('#improveseo_shortcode').val();
            if(improveseo_shortcode_type=='testimonial'){
                text = '[improveseo_testimonial id="'+improveseo_shortcode_id+'"]';
            }
            else if(improveseo_shortcode_type=='googlemap'){
                text ='[improveseo_googlemaps id="'+improveseo_shortcode_id+'" address="" title=""]';
            }
            else if(improveseo_shortcode_type=='button'){
                text = '[improveseo_buttons id="'+improveseo_shortcode_id+'"]';
            }
            else if(improveseo_shortcode_type=='video'){
                text = '[improveseo_video id="'+improveseo_shortcode_id+'"]';
            }
            else if(improveseo_shortcode_type=='list'){
                text = '@list:'+improveseo_shortcode_id;
            }
            tinymce.activeEditor.insertContent(text);
            $.modal.close();
            tinyMCE.activeEditor.focus();
        });

    });
   
    $('#preview_on').click(function(e) {
        //e.preventDefault();
		jQuery("#preview_popup").modal({
			escapeClose: false,
			clickClose: false,
			showClose: false,
			fadeDuration: 1000,
			fadeDelay: 0.35
		});
	});

    $('.google-preview-type').click(function(e){
        var preview_type = $(this).val();
        if(preview_type=="desktop"){
            $('#google-desktop-preview').show();
            $('#google-mobile-preview').hide();
        }else{
            $('#google-desktop-preview').hide();
            $('#google-mobile-preview').show();
        }
    });

    $('#custom-description').keyup(function(e){
		$('.google-description-content').text($(this).val());
        if($(this).val().length > 160){
            $('#custom-description-error').show();
        }else{
            $('#custom-description-error').hide();
        }
	});

    $('#custom-title').keyup(function(e){
		$('.google-mobile-preview-pagename').text($(this).val());
		$('.google-desktop-preview-pagename').text($(this).val());
        if($(this).val().length > 60){
            $('#custom-title-error').show();
        }else{
            $('#custom-title-error').hide();
        }
	});

   
})(jQuery);

 /* Preview New Code */
 let myWindow;
 jQuery('#preview_on').click(function(e){
     e.preventDefault();
     var max_no_posts_old = jQuery('#max-posts').val();
     if (max_no_posts_old > 50) {
         alert("Recommended no. of total posts for preiew is less than 50");				
     }
     jQuery('#wh_prev_modal_1').show();
     jQuery('#wh_prev_modal_2').hide();
     
    //var data = jQuery('#main_form').serialize() + '&action=improveseo_generate_preview';
    var form = jQuery('#main_form')[0];
    var data = new FormData(form);
    data.append("action", "improveseo_generate_preview");
    data.append('content', jQuery.trim(tinymce.get('content') ? tinymce.get('content').getContent() : jQuery('#content').val()));

     var href = form_ajax_vars.admin_url + "?page=improveseo_projects&post_preview=true"; 
     jQuery.ajax({
        url : form_ajax_vars.ajax_url,
        data : data,
        type: "POST",
        dataType: 'json',
        processData: false,
        contentType: false,
         success : function(response) {
            jQuery('#is_preview_available').val('yes');
            jQuery('#preview_id').val(response.project_id);
             myWindow = window.open('about:blank','Popup_Window','toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=0,width=200,height=200,left = 5000,top = 5000');
             myWindow.location.href= form_ajax_vars.admin_url + "?page=improveseo_projects&post_preview=true";
             setTimeout(function(){ 
                jQuery('#wh_prev_modal_1').hide();
                jQuery('#wh_prev_modal_2').show();
                myWindow.resizeTo(720, 360);
                myWindow.moveTo(312, 234);
                myWindow.focus(); 
             }, 5000);
         }
     });

     var delete_interval = setInterval(function() {
        var is_preview_available = jQuery('#is_preview_available').val();
        if(is_preview_available=='yes'){
            if(typeof (myWindow) == 'undefined' || myWindow.closed) {
                if(jQuery.modal.isActive()){
                    var preview_id = jQuery('#preview_id').val();
                    jQuery.modal.close();
                    preview_delete_ajax(preview_id);
                    clearInterval(delete_interval);
                }
            }
        }
    }, 500);
 });
 
function preview_delete_ajax(prev_id){
    jQuery.ajax({
         url : form_ajax_vars.ajax_url,
         data : ({
             action : 'preview_delete_ajax',
             prev_id : prev_id,
             ajax : 1,
         }),
         success : function(data) {
            jQuery('#is_preview_available').val('no');
         }
    });
 }
 function closeWin() {
     jQuery.modal.close();
     var preview_id = jQuery('#preview_id').val();
     preview_delete_ajax(preview_id);
     setTimeout(function() {
         myWindow.close();
     }, 1000);
 }

function changeWin(){
    myWindow.focus(); 
    //myWindow.location.href= form_ajax_vars.admin_url + "?page=improveseo_projects&post_preview=true";
}