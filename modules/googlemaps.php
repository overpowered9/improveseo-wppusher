<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly  
$id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : 0;

if (empty($address)) {
    echo '<h2>Please Enter The Address and Title with shortcode For Showing Map</h2>';
    return;
}

$html = '';
$saved_id = get_option('get_googlemaps_' . $id);
$apikey = isset($saved_id['tw_maps_apikey']) ? $saved_id['tw_maps_apikey'] : '';

$ob = new Improveseo_Testimonial;
$add_array = $ob->getDistance($address, $apikey);

$longi = $add_array['longitude'];
$lati = $add_array['latitude'];
?>
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo esc_url($apikey); ?>"></script>
<script>
    var myMap;
    var myLatlng = new google.maps.LatLng(<?php echo esc_js($lati); ?>, <?php echo esc_js($longi); ?>);

    function initialize() {
        var mapOptions = {
            zoom: 8,
            center: myLatlng,
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            scrollwheel: false
        };
        myMap = new google.maps.Map(document.getElementById('map'), mapOptions);
        var marker = new google.maps.Marker({
            position: myLatlng,
            map: myMap,
            title: '<?php echo esc_js(esc_html($title)); ?>',
            icon: '<?php echo esc_url(IMPROVESEO_DIR . '/assets/images/red-dot.png'); ?>'
        });
    }
    google.maps.event.addDomListener(window, 'load', initialize);
</script>

<div id="map" style="height: 800px;"></div>