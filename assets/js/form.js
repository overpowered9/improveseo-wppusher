(function($){
    /* setTimeout(function(){ 
        console.log('tinymce', tinymce);
        
        
    },2000);  */
    $(document).ready(function(){
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
                    nonce: typeof improveseo_vars !== "undefined" ? improveseo_vars.nonce : "",
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
            var name = $('option:selected', $('#improveseo_shortcode')).attr('data-name');
            

            if(improveseo_shortcode_type=='testimonial'){
                text = '[improveseo_testimonial id="'+improveseo_shortcode_id+'" name="'+name+'"]';
            }
            else if(improveseo_shortcode_type=='googlemap'){
                text ='[improveseo_googlemaps id="'+improveseo_shortcode_id+'" name="'+name+'" address="" title=""]';
            }
            else if(improveseo_shortcode_type=='button'){
                text = '[improveseo_buttons id="'+improveseo_shortcode_id+'" name="'+name+'"]';
            }
            else if(improveseo_shortcode_type=='video'){
                text = '[improveseo_video id="'+improveseo_shortcode_id+'" name="'+name+'"]';
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
		// Short fade: at the old 1000ms the modal itself accounted for a full second
		// of the "preview is slow" feeling before any work had even started.
		jQuery("#preview_popup").modal({
			escapeClose: false,
			clickClose: false,
			showClose: false,
			fadeDuration: 150,
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

 /* ── Instant Preview ─────────────────────────────────────────────────────────
    Renders the current title + TinyMCE content server-side via a single AJAX
    call (improveseo_instant_preview) and shows the result in the modal.  No
    temporary project, no builder, no iframe — the preview appears in under a
    second. This file is shared by all three "Preview Post" entry points:
    the single post's create form and its edit-draft screen (both render
    views/posting/form.php) and the bulk task's edit-draft screen
    (views/bulkprojects/edit-ai-content.php) — one handler covers all three.
    ────────────────────────────────────────────────────────────────────────── */

 var _iseoPreviewXhr = null;

 jQuery(document).on('click', '#iseo_preview_cancel', function (e) {
     e.preventDefault();
     if (_iseoPreviewXhr) { try { _iseoPreviewXhr.abort(); } catch (ex) { /* done */ } _iseoPreviewXhr = null; }
     jQuery.modal.close();
 });

 jQuery('#preview_on').click(function (e) {
     e.preventDefault();

     // Reset modal to loading state
     jQuery('#iseo_preview_error').hide();
     jQuery('#iseo_preview_loading').show();
     jQuery('#wh_prev_modal_1').show();
     jQuery('#wh_prev_modal_2').hide();

     // Grab current editor content — works the same on the single post's create
     // form, its edit-draft screen and the bulk edit-draft screen, since all
     // three share the #title / TinyMCE #content ids.
     var content = jQuery.trim(
         tinymce.get('content') ? tinymce.get('content').getContent() : jQuery('#content').val()
     );
     var title = jQuery.trim(jQuery('#title').val() || '');
     var nonce = jQuery('#iseo_preview_nonce').val() || '';

     _iseoPreviewXhr = jQuery.ajax({
         url: form_ajax_vars.ajax_url,
         type: 'POST',
         dataType: 'json',
         data: {
             action: 'improveseo_instant_preview',
             title: title,
             content: content,
             nonce: nonce
         },
         success: function (res) {
             _iseoPreviewXhr = null;
             if (!res || !res.success || !res.data) {
                 _iseoInstantPreviewFailed(res && res.data ? res.data.message : '');
                 return;
             }
             var html = '<article class="iseo-aicontent-article">';
             html += '<h1 class="iseo-aicontent-title">' + jQuery('<div>').text(res.data.title).html() + '</h1>';
             html += '<div class="iseo-aicontent-body">' + res.data.html + '</div>';
             html += '</article>';
             jQuery('#preview_content_area').html(html);
             jQuery('#wh_prev_modal_1').hide();
             jQuery('#wh_prev_modal_2').show();
         },
         error: function () {
             _iseoPreviewXhr = null;
             _iseoInstantPreviewFailed();
         }
     });
 });

 function _iseoInstantPreviewFailed(message) {
     if (typeof message !== 'string' || !message) {
         message = 'Could not generate preview. Please close this and try again.';
     }
     jQuery('#iseo_preview_loading').hide();
     jQuery('#iseo_preview_error_text').text(message);
     jQuery('#iseo_preview_error').show();
 }

 function closeWin() {
     if (_iseoPreviewXhr) { try { _iseoPreviewXhr.abort(); } catch (ex) { /* done */ } _iseoPreviewXhr = null; }
     jQuery.modal.close();
 }






// Edit-draft screen touch-ups, applied from JS so they show even before the PHP views
// (form.php / edit-post.php) redeploy: heading -> "Draft: Edit Post", button relabels, a
// Cancel button, and the order Publish Post, Save Changes, Cancel, Preview Post. Every step
// is idempotent, so once the views redeploy with the same markup this simply does nothing.
jQuery(function () {
    var isEdit = /[?&]action=edit_post(?:&|$)/.test(window.location.search);
    if (!isEdit) return;

    var $buttons = jQuery('#post_form_buttons');
    if (!$buttons.length) return;

    // Not published if the draft ("Save Changes") submit is present.
    var $draft   = $buttons.find('button[name="draft"]');
    var isDraft  = $draft.length > 0;
    var $publish = $buttons.find('button[name="create"]');
    var $preview = jQuery('#preview_on');

    // 1. Heading -> "Draft: Edit Post" (drafts only).
    if (isDraft) {
        jQuery('.project-heading h1').text('Draft: Edit Post');
        jQuery('h1.hidden').text('Draft: Edit Post');
        // Remove the read-only "Permalink: … Edit" line and the "non-editable URL structure
        // is determined by your permalink settings" note. Hide (not remove) the box so its
        // hidden permalink input still submits with the form.
        jQuery('#edit-slug-box').hide();
    }

    // 2. Publish button relabel.
    if ($publish.length && jQuery.trim($publish.text()) === 'Publish Project and Post') {
        $publish.text('Publish Post');
    }

    // 3. Preview button relabel.
    if ($preview.length) { $preview.text('Preview Post'); }

    // 4. Add a Cancel button if the view didn't already render one (avoids a duplicate).
    function findCancel() {
        return $buttons.find('a').filter(function () {
            return jQuery.trim(jQuery(this).text()) === 'Cancel';
        }).first();
    }
    if (!findCancel().length) {
        var cancelUrl = window.location.pathname + '?page=improveseo_projects';
        var $cancel = jQuery('<a></a>')
            .attr('href', cancelUrl)
            .addClass('btn styling_post_page_action_buttons btn-outline-primary')
            .text('Cancel');
        if ($preview.length) { $cancel.insertBefore($preview); }
        else { $buttons.append($cancel); }
    }

    // 5. Enforce order: Publish, Save Changes, Cancel, Preview.
    var $cancelBtn = findCancel();
    var anchor = $publish.length ? $publish : null;
    if ($draft.length && anchor)     { $draft.insertAfter(anchor);     anchor = $draft; }
    if ($cancelBtn.length && anchor) { $cancelBtn.insertAfter(anchor); anchor = $cancelBtn; }
    if ($preview.length && anchor)   { $preview.insertAfter(anchor);   anchor = $preview; }
});