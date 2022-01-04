(function($){
    $(document).ready(function(){
        tinymce.activeEditor.on('keydown', function(e) {
            if(!$.modal.isActive()){
                if(e.keyCode==219){
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
        });
        $('#shortcode_popup').on($.modal.OPEN, function(event, modal) {
            $('#improveseo_shortcode_type').focus();
            $('#improveseo_shortcode_type').val('');
            $('#improveseo_shortcode').html('');
            $('#improveseo_shortcode').attr('disabled', 'disabled');
            $('#improveseo_shortcode_add_btn').parent().addClass('hidden');
            $('#improveseo_shortcode_error').addClass('hidden');
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
        console.log('works');
        
		/* jQuery('#preview_popup').modal();
		
		jQuery("#preview_popup").modal({
			escapeClose: false,
			clickClose: false,
			showClose: false,
			fadeDuration: 1000,
			fadeDelay: 0.35
		}); */

	});
})(jQuery);
