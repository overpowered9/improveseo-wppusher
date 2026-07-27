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
   
    // The preview modal is opened by the generation handler below (iseoOpenPreviewModal),
    // AFTER the "up to 1.5 minutes" confirm — so cancelling never flashes the modal open.

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

 /* Preview New Code — renders the preview in an in-modal iframe (same-origin),
    avoiding window.open which Safari's popup blocker silently blocks when called
    from an async AJAX callback. */

 // Remember the last successfully-built preview so an unchanged, repeat "Post preview"
 // reuses it instead of rebuilding. A rebuild is slow: it creates a fresh throwaway draft
 // and re-renders the whole front-end. Reuse is time-boxed under the 30-minute server
 // sweep (improveseo_sweep_preview_orphans) so we never point at a preview already swept.
 var iseoLastPreview = { key: null, url: null, id: null, time: 0 };
 function iseoPreviewKey() {
     var content = jQuery.trim(tinymce.get('content') ? tinymce.get('content').getContent() : jQuery('#content').val());
     var title = jQuery.trim(jQuery('#title').val() || '');
     return title + '|~iseo~|' + content;
 }

 // Opens the preview modal (only ever called after the reuse check / confirm, so cancelling
 // the confirm never flashes it open).
 function iseoOpenPreviewModal() {
     jQuery("#preview_popup").modal({
         escapeClose: false,
         clickClose: false,
         showClose: false,
         fadeDuration: 1000,
         fadeDelay: 0.35
     });
 }

 // In-flight generation state, so the Cancel button can abort a build cleanly.
 var iseoPreviewXhr = null;
 var iseoPreviewFallbackTimer = null;
 var iseoPreviewCanceled = false;

 // Cancel a preview that is generating (or a reused one still loading): abort the request,
 // stop the iframe build, discard any throwaway preview that was created, and close the modal.
 function iseoCancelPreview() {
     iseoPreviewCanceled = true;
     if (iseoPreviewFallbackTimer) { clearTimeout(iseoPreviewFallbackTimer); iseoPreviewFallbackTimer = null; }
     if (iseoPreviewXhr) { try { iseoPreviewXhr.abort(); } catch (e) {} iseoPreviewXhr = null; }
     // Drop the throwaway preview project if the server already created one for this build.
     var pid = jQuery('#preview_id').val();
     if (pid) { preview_delete_ajax(pid); jQuery('#preview_id').val(''); }
     iseoLastPreview = { key: null, url: null, id: null, time: 0 };
     jQuery('#preview_iframe').off('load.iseoPreview').attr('src', 'about:blank');
     // Restore the spinner view for next time (the modal_2 view stays hidden).
     jQuery('#wh_prev_modal_1').show();
     jQuery('#wh_prev_modal_2').hide();
     jQuery.modal.close();
 }

 jQuery('#preview_on').click(function(e){
     e.preventDefault();
     var max_no_posts_old = jQuery('#max-posts').val();
     if (max_no_posts_old > 50) {
         alert("Recommended no. of total posts for preiew is less than 50");
     }
     // Fast path: nothing changed since the last preview and it is still fresh — reuse it
     // instead of rebuilding (which is the slow part the user notices on a repeat click).
     // This is near-instant, so it skips the "1.5 minutes" confirm.
     var iseoKey = iseoPreviewKey();
     if (iseoLastPreview.url && iseoLastPreview.key === iseoKey &&
         (Date.now() - iseoLastPreview.time) < 20 * 60 * 1000) {
         iseoPreviewCanceled = false;
         iseoOpenPreviewModal();
         jQuery('#preview_id').val(iseoLastPreview.id || '');
         jQuery('#is_preview_available').val('yes');
         // Reuse the already-built preview (skips the slow rebuild), but keep the
         // spinner up until the cached page has loaded so the user never sees a blank
         // frame — reveal the content only on the iframe's load event.
         jQuery('#wh_prev_modal_1').show();
         jQuery('#wh_prev_modal_2').hide();
         var $reuseFrame = jQuery('#preview_iframe');
         $reuseFrame.off('load.iseoPreview').on('load.iseoPreview', function() {
             $reuseFrame.off('load.iseoPreview');
             if (iseoPreviewCanceled) { return; }
             jQuery('#wh_prev_modal_1').hide();
             jQuery('#wh_prev_modal_2').show();
         });
         $reuseFrame.attr('src', iseoLastPreview.url);
         return;
     }

     // A rebuild is needed (content changed or first run) — this is the slow path. Warn the
     // user it can take a while and let them back out before anything is built.
     if (!confirm("Generating the preview can take up to 1.5 minutes.\n\nClick OK to start, or Cancel to stop. You can also cancel while it's generating.")) {
         return; // user backed out — nothing built, modal never opened
     }

     iseoPreviewCanceled = false;
     iseoOpenPreviewModal();

     // Content changed (or first run): drop the previous throwaway preview so they don't
     // pile up, then build a fresh one.
     if (iseoLastPreview.id) {
         preview_delete_ajax(iseoLastPreview.id);
     }
     iseoLastPreview = { key: null, url: null, id: null, time: 0 };

     jQuery('#wh_prev_modal_1').show();
     jQuery('#wh_prev_modal_2').hide();
     jQuery('#preview_iframe').attr('src', 'about:blank');

    //var data = jQuery('#main_form').serialize() + '&action=improveseo_generate_preview';
    var form = jQuery('#main_form')[0];
    var data = new FormData(form);
    data.append("action", "improveseo_generate_preview");
    data.append('content', jQuery.trim(tinymce.get('content') ? tinymce.get('content').getContent() : jQuery('#content').val()));

     iseoPreviewXhr = jQuery.ajax({
        url : form_ajax_vars.ajax_url,
        data : data,
        type: "POST",
        dataType: 'json',
        processData: false,
        contentType: false,
         success : function(response) {
            iseoPreviewXhr = null;
            if (iseoPreviewCanceled) { return; } // user cancelled while the request was in flight
            jQuery('#is_preview_available').val('yes');
            jQuery('#preview_id').val(response.project_id);
            var preview_url = form_ajax_vars.admin_url + "?page=improveseo_projects&post_preview=true&preview_id=" + response.project_id;
            var $iframe = jQuery('#preview_iframe');

            // The preview URL first lands on the builder/projects page, which generates
            // the post and then redirects to the real WP preview link. Keep the
            // "Generating preview" spinner up (the iframe still loads/builds while
            // hidden) and only reveal it once it has navigated off the builder page,
            // so the user never sees the wp-admin Projects List flash.
            var revealPreview = function() {
                if (iseoPreviewFallbackTimer) { clearTimeout(iseoPreviewFallbackTimer); iseoPreviewFallbackTimer = null; }
                $iframe.off('load.iseoPreview');
                if (iseoPreviewCanceled) { return; }
                jQuery('#wh_prev_modal_1').hide();
                jQuery('#wh_prev_modal_2').show();
                // Remember this built preview so an unchanged repeat click can reuse it.
                try {
                    var finalUrl = $iframe[0].contentWindow.location.href;
                    if (finalUrl && finalUrl.indexOf('page=improveseo_projects') === -1) {
                        iseoLastPreview = { key: iseoKey, url: finalUrl, id: response.project_id, time: Date.now() };
                    }
                } catch (e) { /* cross-origin (should not happen same-origin); skip caching */ }
            };
            // Safety net: if building stalls, reveal whatever is there after 90s
            // rather than spinning forever.
            iseoPreviewFallbackTimer = setTimeout(revealPreview, 90000);

            $iframe.off('load.iseoPreview').on('load.iseoPreview', function() {
                if (iseoPreviewCanceled) { return; }
                var onBuilderPage = true;
                try {
                    onBuilderPage = $iframe[0].contentWindow.location.href.indexOf('page=improveseo_projects') !== -1;
                } catch (e) {
                    // Same-origin, so this should not throw; reveal to be safe.
                    onBuilderPage = false;
                }
                if (!onBuilderPage) {
                    revealPreview();
                }
            });

            $iframe.attr('src', preview_url);
         },
         error: function(jqXHR, textStatus) {
            iseoPreviewXhr = null;
            if (textStatus === 'abort' || iseoPreviewCanceled) { return; } // user cancelled — already handled
            jQuery('#wh_prev_modal_1').html('<b style="color:#c0392b; font-size:18px;">Could not generate preview. Please close this and try again.</b><br><br><button type="button" class="button button-primary" onclick="closeWin()">Close</button>');
         }
     });
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
     // Keep the built preview server-side so an unchanged repeat "Post preview" reuses it
     // (loads the final preview URL directly, skipping the slow rebuild + builder round
     // trip). The iframe is blanked to free resources; the preview is removed when a
     // different one is built, and by the 30-minute sweep (improveseo_sweep_preview_orphans,
     // designed for exactly this "close handler didn't delete" case) otherwise.
     jQuery('#preview_iframe').off('load.iseoPreview').attr('src', 'about:blank');
     jQuery.modal.close();
 }

// "Open in new tab" — runs on a user click (gesture), so window.open is allowed here.
function changeWin(){
    var preview_id = jQuery('#preview_id').val();
    if (preview_id) {
        window.open(form_ajax_vars.admin_url + "?page=improveseo_projects&post_preview=true&preview_id=" + preview_id, '_blank');
    }
}