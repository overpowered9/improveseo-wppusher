<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Post preview for the Single Projects screens: the list's row-level "Preview Post"
// (views/projects/index.php) and the Project Details "Preview Post" button
// (views/projects/project-details.php). Same in-modal instant preview as the editor's
// "Preview Post" (assets/js/form.js), but rendered from the SAVED project by id
// (improveseo_project_preview_by_id in modules/ajax.php), since neither screen has an
// editor to read from. form.js isn't loaded on these screens, so this is self-contained.
// Styles live in assets/css/made_by_me.css, scoped under #preview_content_area.
?>
<div id="preview_popup" class="modal" style="text-align:center; width:90%; max-width:1100px;">
	<div id="wh_prev_modal_1">
		<div id="iseo_preview_loading" class="iseo-preview-loading">
			<div class="iseo-preview-spinner" role="status" aria-label="Generating preview"></div>
			<b class="iseo-preview-loading-title">Generating preview</b>
			<span class="iseo-preview-loading-note">Rendering this post.</span>
			<button type="button" class="button iseo-preview-action" onclick="iseoCloseProjectPreview()">Cancel</button>
		</div>
		<div id="iseo_preview_error" class="iseo-preview-error" style="display:none;">
			<p id="iseo_preview_error_text"></p>
			<button type="button" class="button iseo-preview-action iseo-preview-action--primary"
				onclick="iseoCloseProjectPreview()">Close</button>
		</div>
	</div>
	<div id="wh_prev_modal_2" style="display:none;">
		<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
			<b style="font-size:18px">Post preview</b>
			<button type="button" id="open_win"
				class="button button-primary iseo-preview-action iseo-preview-action--primary"
				onclick="iseoCloseProjectPreview()">Close preview</button>
		</div>
		<div id="preview_content_area" class="iseo-aicontent-wrap"></div>
	</div>
</div>

<script>
	var _iseoProjectPreviewXhr = null;

	function iseoCloseProjectPreview() {
		if (_iseoProjectPreviewXhr) { try { _iseoProjectPreviewXhr.abort(); } catch (ex) { /* done */ } _iseoProjectPreviewXhr = null; }
		jQuery.modal.close();
	}

	function iseoProjectPreviewFailed(message) {
		if (typeof message !== 'string' || !message) {
			message = 'Could not generate preview. Please close this and try again.';
		}
		jQuery('#iseo_preview_loading').hide();
		jQuery('#iseo_preview_error_text').text(message);
		jQuery('#iseo_preview_error').show();
	}

	function iseoPreviewProject(id) {
		jQuery('.actionpopup').hide(); // the list's row menu, if it was open
		jQuery('#iseo_preview_error').hide();
		jQuery('#iseo_preview_loading').show();
		jQuery('#wh_prev_modal_1').show();
		jQuery('#wh_prev_modal_2').hide();
		jQuery('#preview_popup').modal({
			escapeClose: false,
			clickClose: false,
			showClose: false,
			fadeDuration: 150,
			fadeDelay: 0.35
		});

		_iseoProjectPreviewXhr = jQuery.ajax({
			url: <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'improveseo_project_preview_by_id',
				id: id,
				nonce: <?php echo wp_json_encode( wp_create_nonce( 'improveseo_project_preview' ) ); ?>
			},
			success: function (res) {
				_iseoProjectPreviewXhr = null;
				if (!res || !res.success || !res.data) {
					iseoProjectPreviewFailed(res && res.data ? res.data.message : '');
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
			error: function (xhr, status) {
				_iseoProjectPreviewXhr = null;
				if (status !== 'abort') { iseoProjectPreviewFailed(); }
			}
		});
	}
</script>
