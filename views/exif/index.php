<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly
?>

<div class="PixabayWrap">
	<div class="PixabaySearch">
		<input type="text" id="exif-query" placeholder="Address" onkeypress="if (event.keyCode == 13 || event.which == 13) ImageEXIF.find()">
		<button id="exif-search-btn" class="button-primary" onclick="ImageEXIF.find()">Add</button>

		<div id="exif-addresses"></div>
	</div>

	<div id="google-map" style="height: 350px"></div>

	<div class="Pixabay__footer">
		<div class="fl_l Pixabay__status"><strong>0</strong> selected</div>
		<div class="fl_r Pixabay__buttons">
			<button class="button-primary" onclick="Dialog.close('ImageEXIF')">Insert EXIF Data</button>
		</div>
	</div>
</div>

<?php
	$google_api_key = get_option('improveseo_google_api_key');
	if ($google_api_key):
?>
<script src="//maps.googleapis.com/maps/api/js?key=<?php echo htmlspecialchars($google_api_key, ENT_QUOTES); ?>&callback=ImageEXIF.initMap"
    async defer></script>
<?php endif; ?>
