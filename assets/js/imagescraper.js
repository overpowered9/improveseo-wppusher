var ImageScraper = {
	selected: [],
	currentPage: 1,
	formLoaded: false,
	moreBusy: false,

	init: function() {
		Dialog.init('ImageScraper', ['div.Pixabay__shortcode-wrap']);
	},

	start: function(el) {
		Dialog.title('ImageScraper', 'Image Scraper');

		if (!ImageScraper.formLoaded) {
			Dialog.body('ImageScraper').load(jQuery(el).attr('href'), function() {
				ImageScraper.formLoaded = true;

				jQuery('#pixabay-search-images').scroll(function()
				{
				    if(jQuery('#pixabay-search-images').scrollTop() == jQuery('#pixabay-images').height() - jQuery('#pixabay-search-images').height() + 20 && !ImageScraper.moreBusy)
				    {
				    	ImageScraper.moreBusy = true;

				    	ImageScraper.currentPage++;
				    	ImageScraper.getImages();
				    }
				});
			});
		}

		Dialog.open('ImageScraper');

		return false;
	},

	find: function() {
		var query = jQuery('#pixabay-query').val();
		if (!jQuery.trim(query)) {
			jQuery('#pixabay-query').focus();
			return;
		}

		ImageScraper.deselect();

		jQuery('#pixabay-images').html('');

		ImageScraper.getImages();
	},

	getImages: function() {
		var query = jQuery('#pixabay-query').val();
		var key = jQuery('#pixabay-api-key').val();

		ImageScraper.toggleProgress(true);
		jQuery.getJSON('https://pixabay.com/api/?key=' + key + '&q=' + query + '&image_type=photo&pretty=true&page='+ ImageScraper.currentPage, function(response) {
			ImageScraper.toggleProgress(false);

			_.each(response.hits, function(image, i) {
				var $elle = jQuery('<div class="fl_l Pixabay__image">\
						<img src="'+ image.previewURL + '">\
						<i class="fa fa-check"></i>\
					</div>');

				$elle.click(function() {
					ImageScraper.handleSelect(this, image);
				});

				$elle.appendTo('#pixabay-images');
			});
		}, function(error) {
			ImageScraper.toggleProgress(false);
		});
	},

	status: function() {
		Dialog.body('ImageScraper').find('div.Pixabay__status').html('<strong>' + ImageScraper.selected.length + '</strong> selected.' + (ImageScraper.selected.length > 0 ? ' <a href="#" onclick="ImageScraper.deselect()">Deselect all</a>' : ''));
	},

	handleSelect: function(el, image) {
		var jQueryel = jQuery(el);

		// Image selected
		if (jQueryel.hasClass('Pixabay__image--selected')) {
			jQueryel.removeClass('Pixabay__image--selected');

			ImageScraper.selected = _.without(ImageScraper.selected, _.findWhere(ImageScraper.selected, {previewURL: image.previewURL}));
		} else {
			jQueryel.addClass('Pixabay__image--selected');

			ImageScraper.selected.push(image);
		}

		ImageScraper.status();
	},

	deselect: function() {
		jQuery('#pixabay-images').find('div.Pixabay__image--selected').click();
	},

	shortcodeCreateProgress: function(busy) {
		var images = jQuery('#pixabay-images');

		if (busy) {
			images.hide();
		} else {
			images.show();
		}

		ImageScraper.toggleProgress(busy);
	},

	toggleProgress: function(busy) {
		var jQuerybtn = jQuery('#pixabay-search-btn');
		var jQueryimages = jQuery('#pixabay-search-images');
		var jQueryspin = jQueryimages.find('div.ispinner');
		var jQuerytext = jQueryimages.find('div.ispinner__text');

		if (busy) {
			jQuerybtn.attr('disabled', true);
			jQueryspin.show();
			jQuerytext.show();
		} else {
			jQuerybtn.attr('disabled', false);
			jQueryspin.hide();
			jQuerytext.hide();

			ImageScraper.moreBusy = false;
		}
	},

	post: function() {
		_.each(ImageScraper.selected, function(image, i) {
			tinyMCE.execCommand('mceInsertContent', false, '<img alt="'+ image.tags + '" height="'+ image.webformatHeight + '" width="'+ image.webformatWidth + '" src="' + image.webformatURL + '"/>');
		});
	},

	showShortcode: function(e) {
		e.stopPropagation();

		if (ImageScraper.selected.length > 0) {
			jQuery('div.Pixabay__shortcode-wrap').show();
			jQuery('#shortcode-name').focus();
		} else {
			jQuery(e.target).notify('Select at least one image', 'error');
		}
	},

	shortcode: function() {
		var name = jQuery('#shortcode-name').val();
		if (!jQuery.trim(name)) {
			jQuery('#shortcode-name').focus();
			return;
		}

		var images = [];
		_.each(ImageScraper.selected, function(image, i) {
			images.push({
				id: image.id,
				tags: image.tags,
				url: image.webformatURL,
				width: image.webformatWidth,
				height: image.webformatHeight
			});
		});

		jQuery('#shortcode-btn').attr('disabled', true);

		ImageScraper.shortcodeCreateProgress(true);

		jQuery.post('/index.php?api=workhorse&action=shortcode', {
			shortcode: name,
			media: images
		}).done(function(response) {
			if (response.success) {
				Dialog.hideOverlays('ImageScraper');
				ImageScraper.deselect();

				WorkHorsePopup.open('info');
				WorkHorsePopup.body('info', 'Congrats! Your shortcode has been created. To call it, please do <strong>['+ name +']</strong> anywhere in the body (post content) of your project!');
			} else {
				jQuery('#shortcode-btn').notify('Error', 'error');
			}
		}).fail(function(xhr) {
			jQuery('#shortcode-btn').notify(xhr.responseText, 'error');
		}).always(function() {
			ImageScraper.shortcodeCreateProgress(false);
			jQuery('#shortcode-btn').attr('disabled', false);
		});
	}
};

jQuery(document.body).ready(function() {

	ImageScraper.init();

});
