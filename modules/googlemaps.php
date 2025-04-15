<?php 
	
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
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $apikey ?>"></script>
<script>
    var myMap;
    var myLatlng = new google.maps.LatLng(<?php echo $lati;?>,<?php echo $longi;?>);
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
            title: '<?php echo $title;?>',
            icon: 'http://www.google.com/intl/en_us/mapfiles/ms/micons/red-dot.png'
        });
    }
    google.maps.event.addDomListener(window, 'load', initialize);
</script>

<div id="map" style="height: 800px;">

</div>