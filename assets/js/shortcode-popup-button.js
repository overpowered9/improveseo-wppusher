(function() {
	tinymce.PluginManager.add('improveseo_mce_button', function( editor, url ) {
		editor.addButton('improveseo_mce_button', {
			text: false,
            icon: false,
			image: url + '/shortcode-icon.png',
            tooltip: 'ImproveSEO Shortcodes',
            onclick: function () {
                jQuery("#shortcode_popup").modal({
					escapeClose: false,
					fadeDuration: 1000,
					fadeDelay: 0.35,
					keyboard: false,
					focus: true
				});
			}
		});
	});
})();