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

 /* Preview New Code — renders the preview in an in-modal iframe (same-origin),
    avoiding window.open which Safari's popup blocker silently blocks when called
    from an async AJAX callback. */

 // Remember the last successfully-built preview so an unchanged, repeat "Post preview"
 // reuses it instead of rebuilding. A rebuild is slow: it creates a fresh throwaway draft
 // and re-renders the whole front-end. Reuse is time-boxed under the 30-minute server
 // sweep (improveseo_sweep_preview_orphans) so we never point at a preview already swept.
 var iseoLastPreview = { key: null, url: null, id: null, time: 0 };

 // Cancellation state. Each run of the preview gets a token; Cancel bumps the token
 // before aborting, so any callback from a request that was already in flight sees a
 // stale token and does nothing (an aborted jqXHR still fires its error handler).
 var iseoPreviewRun = 0;
 var iseoPreviewXhrs = [];
 var iseoPreviewBuildStarted = false;
 var iseoPreviewPendingId = null;

 function iseoPreviewKey() {
     var content = jQuery.trim(tinymce.get('content') ? tinymce.get('content').getContent() : jQuery('#content').val());
     var title = jQuery.trim(jQuery('#title').val() || '');
     return title + '|~iseo~|' + content;
 }

 // Start a run: reset the modal to its loading state and hand back this run's token.
 function iseoStartPreviewRun() {
     iseoPreviewXhrs = [];
     iseoPreviewBuildStarted = false;
     // A run that errored out left its throwaway project behind; queue it for the
     // cleanup the rebuild path already performs on iseoLastPreview.id.
     if (iseoPreviewPendingId && !iseoLastPreview.id) {
         iseoLastPreview.id = iseoPreviewPendingId;
     }
     iseoPreviewPendingId = null;
     jQuery('#iseo_preview_error').hide();
     jQuery('#iseo_preview_loading').show();
     jQuery('#wh_prev_modal_1').show();
     jQuery('#wh_prev_modal_2').hide();
     return ++iseoPreviewRun;
 }

 function iseoCancelPreview() {
     iseoPreviewRun++;

     for (var i = 0; i < iseoPreviewXhrs.length; i++) {
         try { iseoPreviewXhrs[i].abort(); } catch (e) { /* already finished */ }
     }
     iseoPreviewXhrs = [];

     // Aborting the request does not stop the build: improveseo_builder() runs under
     // ignore_user_abort(true) and finishes regardless. So only delete the throwaway
     // project outright when the build was never dispatched; once it has been, deleting
     // the row now would strand the draft the build is about to insert. In that case
     // hand the id to iseoLastPreview so the next preview run drops it (by then the
     // build is long done), with the 30-minute server sweep as the backstop.
     if (iseoPreviewPendingId) {
         if (iseoPreviewBuildStarted) {
             iseoLastPreview = { key: null, url: null, id: iseoPreviewPendingId, time: 0 };
         } else {
             preview_delete_ajax(iseoPreviewPendingId);
         }
     }
     iseoPreviewPendingId = null;
     iseoPreviewBuildStarted = false;

     jQuery('#is_preview_available').val('no');
     jQuery('#preview_iframe').off('load.iseoPreview').attr('src', 'about:blank');
     jQuery.modal.close();
 }

 jQuery(document).on('click', '#iseo_preview_cancel', function(e) {
     e.preventDefault();
     iseoCancelPreview();
 });

 jQuery('#preview_on').click(function(e){
     e.preventDefault();
     var max_no_posts_old = jQuery('#max-posts').val();
     if (max_no_posts_old > 50) {
         alert("Recommended no. of total posts for preiew is less than 50");
     }
     var iseoKey = iseoPreviewKey();
     var runToken = iseoStartPreviewRun();

     // Fast path: nothing changed since the last preview and it is still fresh — reuse it
     // instead of rebuilding (which is the slow part the user notices on a repeat click).
     if (iseoLastPreview.url && iseoLastPreview.key === iseoKey &&
         (Date.now() - iseoLastPreview.time) < 20 * 60 * 1000) {
         jQuery('#preview_id').val(iseoLastPreview.id || '');
         jQuery('#is_preview_available').val('yes');
         // Reuse the already-built preview (skips the slow rebuild), but keep the
         // spinner up until the cached page has loaded so the user never sees a blank
         // frame — reveal the content only on the iframe's load event.
         var $reuseFrame = jQuery('#preview_iframe');
         $reuseFrame.off('load.iseoPreview').on('load.iseoPreview', function() {
             $reuseFrame.off('load.iseoPreview');
             if (runToken !== iseoPreviewRun) return;
             jQuery('#wh_prev_modal_1').hide();
             jQuery('#wh_prev_modal_2').show();
         });
         $reuseFrame.attr('src', iseoLastPreview.url);
         return;
     }

     // Content changed (or first run): drop the previous throwaway preview so they don't
     // pile up, then build a fresh one.
     if (iseoLastPreview.id) {
         preview_delete_ajax(iseoLastPreview.id);
     }
     iseoLastPreview = { key: null, url: null, id: null, time: 0 };

     jQuery('#preview_iframe').attr('src', 'about:blank');

    //var data = jQuery('#main_form').serialize() + '&action=improveseo_generate_preview';
    var form = jQuery('#main_form')[0];
    var data = new FormData(form);
    data.append("action", "improveseo_generate_preview");
    data.append('content', jQuery.trim(tinymce.get('content') ? tinymce.get('content').getContent() : jQuery('#content').val()));

     iseoPreviewXhrs.push(jQuery.ajax({
        url : form_ajax_vars.ajax_url,
        data : data,
        type: "POST",
        dataType: 'json',
        processData: false,
        contentType: false,
         success : function(response) {
            if (runToken !== iseoPreviewRun) return;
            iseoPreviewPendingId = response.project_id;
            jQuery('#is_preview_available').val('yes');
            jQuery('#preview_id').val(response.project_id);
            iseoBuildAndShowPreview(response.project_id, iseoKey, runToken);
         },
         error: function() {
            if (runToken !== iseoPreviewRun) return;
            iseoPreviewFailed();
         }
     }));
 });

 function iseoPreviewFailed(message) {
     if (typeof message !== 'string' || !message) {
         message = 'Could not generate preview. Please close this and try again.';
     }
     jQuery('#iseo_preview_loading').hide();
     jQuery('#iseo_preview_error_text').text(message);
     jQuery('#iseo_preview_error').show();
 }

 // Build the throwaway preview post, then point the iframe straight at the finished
 // preview. Both steps run over admin-ajax on purpose: the previous flow navigated the
 // iframe to the Projects List admin screen, which rendered that entire wp-admin page
 // (list query plus one un-indexed postmeta lookup per row) once to kick the build off
 // and a second time after it, before ever reaching the preview. Those two renders were
 // the bulk of the wait, and neither was ever shown to the user.
 function iseoBuildAndShowPreview(projectId, cacheKey, runToken) {
     var $iframe = jQuery('#preview_iframe');

     iseoPreviewBuildStarted = true;
     iseoPreviewXhrs.push(jQuery.ajax({
         url: form_ajax_vars.ajax_url,
         data: { action: 'workdex_builder_ajax', ajax: 1, page: 1, id: projectId },
         // The build is the genuinely slow step; keep the old 90s ceiling as the point
         // where we stop waiting rather than spinning forever.
         timeout: 90000,
         success: function() {
             if (runToken !== iseoPreviewRun) return;
             iseoPreviewXhrs.push(jQuery.ajax({
                 url: form_ajax_vars.ajax_url,
                 data: { action: 'improveseo_preview_url', id: projectId },
                 dataType: 'json',
                 success: function(res) {
                     if (runToken !== iseoPreviewRun) return;
                     if (!res || !res.success || !res.data || !res.data.url) {
                         iseoPreviewFailed(res && res.data ? res.data.message : '');
                         return;
                     }
                     var url = res.data.url;
                     $iframe.off('load.iseoPreview').on('load.iseoPreview', function() {
                         $iframe.off('load.iseoPreview');
                         if (runToken !== iseoPreviewRun) return;
                         jQuery('#wh_prev_modal_1').hide();
                         jQuery('#wh_prev_modal_2').show();
                         // Remember this built preview so an unchanged repeat click can reuse it.
                         iseoLastPreview = { key: cacheKey, url: url, id: projectId, time: Date.now() };
                         iseoPreviewPendingId = null;
                     });
                     $iframe.attr('src', url);
                 },
                 error: function() {
                     if (runToken !== iseoPreviewRun) return;
                     iseoPreviewFailed();
                 }
             }));
         },
         error: function() {
             if (runToken !== iseoPreviewRun) return;
             iseoPreviewFailed();
         }
     }));
 }

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
    // The preview is already built and its final URL is known, so open that directly
    // instead of routing through the Projects List page and its redirect.
    if (iseoLastPreview.url) {
        window.open(iseoLastPreview.url, '_blank');
        return;
    }
    var preview_id = jQuery('#preview_id').val();
    if (preview_id) {
        window.open(form_ajax_vars.admin_url + "?page=improveseo_projects&post_preview=true&preview_id=" + preview_id, '_blank');
    }
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
    }

    // Remove the read-only "Permalink: … Edit" line and the "non-editable URL structure is
    // determined by your permalink settings" note on ANY edit screen — not gated on draft
    // detection, which can come back false. Hide (not remove) the box so its hidden permalink
    // input still submits with the form.
    jQuery('#edit-slug-box').hide();

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