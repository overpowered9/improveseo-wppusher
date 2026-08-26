<?php if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly. ?>
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
<?php
// Google Maps is a legitimate external dependency (wp.org permits it) and is not
// enqueued because the callback parameter below hands control to inline code on this
// page; moving it to wp_enqueue_script() would change when that callback fires.
?>
<script<?php // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript -- see note above. ?> src="<?php echo esc_url( '//maps.googleapis.com/maps/api/js?key=' . $google_api_key . '&callback=ImageEXIF.initMap' ); ?>"
    async defer></script>
<?php endif; ?>
