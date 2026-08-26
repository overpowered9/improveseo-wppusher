<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
 
	
	if(empty($address)){
	    echo '<h2>Please Enter The Address and Title with shortcode For Showing Map</h2>';
	    return;
	}
	
	$html = '';
  	$saved_id = get_option('get_googlemaps_'.$id[0]);
  	$apikey = isset($saved_id['tw_maps_apikey']) ? $saved_id['tw_maps_apikey'] : '';
  	
  	$ob = new WC_Testimonial;
  	$add_array = $ob->getDistance($address , $apikey);
  	
  	
    $longi = $add_array['longitude'];
    $lati = $add_array['latitude'];
    
  	
?>
<?php
// Google Maps is a legitimate external dependency and is not bundled (wp.org permits it).
// It is not enqueued because the inline initialiser directly below assumes google.maps is
// already defined; moving this to wp_enqueue_script() would load it after that code runs.
?>
<script src="<?php echo esc_url( 'https://maps.googleapis.com/maps/api/js?key=' . $apikey ); ?>"></script><?php // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript -- see note above. ?>
<script>
    var myMap;
    // floatval(): these are coordinates being written into JavaScript. Unescaped, a stored
    // value like "0);alert(1)//" would execute here.
    var myLatlng = new google.maps.LatLng(<?php echo floatval( $lati );?>,<?php echo floatval( $longi );?>);
    function initialize() {
        var mapOptions = {
            zoom: 8,
            center: myLatlng,
            mapTypeId: google.maps.MapTypeId.ROADMAP  ,
            scrollwheel: false
        }
        myMap = new google.maps.Map(document.getElementById('map'), mapOptions);
        var marker = new google.maps.Marker({
            position: myLatlng,
            map: myMap,
            title: '<?php echo esc_js( $title );?>',
            icon: 'http://www.google.com/intl/en_us/mapfiles/ms/micons/red-dot.png'
        });
    }
    google.maps.event.addDomListener(window, 'load', initialize);
</script>

<div id="map" style="height: 800px;">

</div>