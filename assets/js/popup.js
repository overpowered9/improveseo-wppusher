var WorkHorsePopup = {
	overlays: {},

	init: function(id, overlays) {
		jQuery('\
			<div id="'+ id + '" class="Popup" onclick="WorkHorsePopup.close(\''+ id +'\')">\
				<div class="Popup__container" onclick="Dialog.hideOverlays(\''+ id +'\'); event.cancelBubble = true;">\
					<div class="Popup__title-wrap">\
						<a class="Popup__close" onclick="WorkHorsePopup.close(\''+ id +'\')"><div class="tb-close-icon"></div></a>\
					</div>\
					<div class="Popup__body">\
						\
					</div>\
				</div>\
			</div>').appendTo('body');

		Dialog.overlays[id] = overlays;
	},

	open: function(id) {
		jQuery('#'+ id + '-overlay').show();
		jQuery('#'+ id).show();

		jQuery('body').css({
			overflow: 'hidden'
		});
	},

	close: function(id) {
		jQuery('#'+ id + '-overlay').hide();
		jQuery('#'+ id).hide();

		jQuery('body').css({
			overflow: 'auto'
		});

		WorkHorsePopup.hideOverlays(id);
	},

	hideOverlays: function(id) {
		_.each(WorkHorsePopup.overlays[id], function(overlay) {
			jQuery(overlay).hide();
		});
	},

	body: function(id, body) {
		var $body = jQuery('#'+ id).find('div.Popup__body');

		if (body) $body.html(body);
		return $body;
	}
};