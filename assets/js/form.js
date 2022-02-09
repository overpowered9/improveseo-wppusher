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
                    
                    console.log(full_content);
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
	});

    $('#custom-title').keyup(function(e){
		$('.google-mobile-preview-pagename').text($(this).val());
		$('.google-desktop-preview-pagename').text($(this).val());
	});
})(jQuery);
let form_action_old_wh = document.getElementById('main_form').action;
	function openWin() {
    	document.getElementById("main_form").action = form_ajax_vars.admin_url + "?page=improveseo_dashboard&action=do_create_post&noheader=true";
		
		let max_no_posts_old = document.getElementById('max-posts').value;					
		if (max_no_posts_old > 50) {
			alert("Recommended no. of total posts for preiew is less than 50");				
		}
		var myForm = document.getElementById('main_form');
		myForm.onsubmit = function() {
			myWindow = window.open('about:blank','Popup_Window','toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=0,width=200,height=200,left = 5000,top = 5000');
			this.target = 'Popup_Window';
		}

        var wh_modal_1_style = document.getElementById("wh_prev_modal_1");
		var wh_modal_2_style = document.getElementById("wh_prev_modal_2");
		wh_modal_1_style.style.display = "block";
		wh_modal_2_style.style.display = "none";

        var preview_id= 0;					
		var check_status = setInterval(function() {
            if(myWindow.location.href.indexOf('?page=improveseo_projects') > 0) {
				var location = myWindow.location.search;
				var location_2 = location.split('&');
				var location_3 = location_2[1];
				var preview_ids = location_3.split('=');
				preview_id = preview_ids[1];
    			myWindow.location.href= form_ajax_vars.admin_url + "?page=improveseo_projects&post_preview=true"; 
                    clearInterval(check_status);
            }
        }, 2000);

        var check_status_2 = setInterval(function() {
    		if(myWindow.location.href.indexOf('?id=') > 0) {
				wh_modal_1_style.style.display = "none";
				wh_modal_2_style.style.display = "block";
                myWindow.resizeTo(720, 360);
                myWindow.moveTo(312, 234); 
                clearInterval(check_status_2);
			}
        }, 2500);

        function preview_delete_ajax(prev_id){
            jQuery.ajax({
                url : form_ajax_vars.ajax_url,
                data : ({
                    action : 'preview_delete_ajax',
                    prev_id : prev_id,
                    ajax : 1,
				}),
                success : function(data) {
					//alert(data);
				}
            });
		}

        var check_prev_win = setInterval(function() {
			if(typeof (myWindow) == 'undefined' || myWindow.closed) {
				if(jQuery.modal.isActive()){
                    preview_delete_ajax(preview_id);
					jQuery.modal.close();
					document.getElementById("main_form").action = form_action_old_wh;
					//document.getElementById('hidden_input_wh').setAttribute('value', reset_hidden_input_wh);
					var myForm = document.getElementById('main_form');
					myForm.onsubmit = function() {}
			    }
				clearInterval(check_prev_win);
			}
		}, 500);
	}
				
	function closeWin() {
		jQuery.modal.close();
		document.getElementById("main_form").action = form_action_old_wh;
		//document.getElementById('hidden_input_wh').setAttribute('value', reset_hidden_input_wh);
        var location = myWindow.location.search;
        var preview_ids = location.split("=");
        var id = preview_ids[1];
        myWindow.location.href= form_ajax_vars.admin_url + "?page=improveseo_projects&action=delete&id="+ id +"&noheader=true";
            setTimeout(function() {
                myWindow.close();
            }, 10000);
        var myForm = document.getElementById('main_form');
        myForm.onsubmit = function() {}
	}
				
    function changeWin(){
		myWindow.focus(); 
		myWindow.location.href= form_ajax_vars.admin_url + "?page=improveseo_projects&post_preview=true";
	}

    
