<?php
use ImproveSEO\View;
use ImproveSEO\Spintax;
use ImproveSEO\Validator;
use ImproveSEO\Models\Country;
use ImproveSEO\Models\GeoData;
use ImproveSEO\Models\Shortcode;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly  


if (isset ( $_GET ['api'] ) && $_GET ['api'] == 'improveseo') {
	$act = $_GET ['action'];
	$results = array ();
	
	// Generate JSON data for GEO Tree
	if ($act == 'geo-tree') {
		$country = $_GET ['country'];
		
		// Show US Data
		if ($country == 'us') {
			if (isset ( $_GET ['id'] ) && $_GET ['id'] != '#') {
				$id = urldecode ( $_GET ['id'] );
				
				// Show zip codes
				if (substr_count ( $id, '/' ) == 1) {
					list ( $state, $city ) = explode ( '/', $id );
					$city_obj = $wpdb->get_row ( "SELECT * FROM {$wpdb->prefix}improveseo_us_cities WHERE id = " . $city );
					
					$codes = $wpdb->get_results ( "SELECT * FROM {$wpdb->prefix}improveseo_us_cities WHERE county = '{$city_obj->county}' AND state_code = '{$city_obj->state_code}' AND city = '{$city_obj->city}' ORDER BY zip" );
					
					foreach ( $codes as $code ) {
						$results [] = array (
								'id' => $state . '/' . $city . '/' . $code->zip,
								'text' => $code->zip,
								'children' => false 
						);
					}
				} 				// Show cities
				else {
					$cities = $wpdb->get_results ( "SELECT * FROM {$wpdb->prefix}improveseo_us_cities WHERE state_code = '$id' GROUP BY county, state_code, city ORDER BY city" );
					
					foreach ( $cities as $city ) {
						$results [] = array (
								'id' => $id . '/' . $city->id,
								'text' => $city->city,
								'children' => true 
						);
					}
				}
			} 			// Show states
			else {
				$states = $wpdb->get_results ( "SELECT * FROM {$wpdb->prefix}improveseo_us_states" );
				foreach ( $states as $state ) {
					$results [] = array (
							'id' => $state->state_code,
							'text' => $state->state,
							'children' => true 
					);
				}
				if (count ( $states ) == 0) {
					global $wpdb;
					global $improveseo_db_version;
					global $wp_rewrite;
					
					require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
					include_once 'geo/installer.php';
					$states = $wpdb->get_results ( "SELECT * FROM {$wpdb->prefix}improveseo_us_states" );
					foreach ( $states as $state ) {
						$results [] = array (
								'id' => $state->state_code,
								'text' => $state->state,
								'children' => true 
						);
					}
				}
			}
		}		// Show UK Data
		elseif ($country == 'uk') {
			if (isset ( $_GET ['id'] ) && $_GET ['id'] != '#') {
				$id = urldecode ( $_GET ['id'] );
				
				// Show zip codes
				if (substr_count ( $id, '/' )) {
					list ( $state, $city ) = explode ( '/', $id );
					$city_obj = $wpdb->get_row ( "SELECT * FROM {$wpdb->prefix}improveseo_uk_cities WHERE id = $city" );
					
					$codes = $wpdb->get_results ( "SELECT * FROM {$wpdb->prefix}improveseo_uk_cities WHERE region_id = '{$city_obj->region_id}' AND name = '{$city_obj->name}' ORDER BY postcode" );
					
					foreach ( $codes as $code ) {
						$results [] = array (
								'id' => $state . '/' . $city . '/' . $code->postcode,
								'text' => $code->postcode,
								'children' => false 
						);
					}
				} 				// Show cities
				else {
					$cities = $wpdb->get_results ( "SELECT * FROM {$wpdb->prefix}improveseo_uk_cities WHERE region_id = '" . $id . "' GROUP BY region_id, name ORDER BY name" );
					
					foreach ( $cities as $city ) {
						$results [] = array (
								'id' => $id . '/' . $city->id,
								'text' => $city->name,
								'children' => true 
						);
					}
				}
			} 			// Show states
			else {
				$states = $wpdb->get_results ( "SELECT * FROM {$wpdb->prefix}improveseo_uk_states ORDER BY name" );
				foreach ( $states as $state ) {
					$results [] = array (
							'id' => $state->id,
							'text' => $state->name . ' [' . $state->country_short . ']',
							'children' => true 
					);
				}
				if (count ( $states ) == 0) {
					global $wpdb;
					global $improveseo_db_version;
					global $wp_rewrite;
					
					require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
					include_once 'geo/installer.php';
					$states = $wpdb->get_results ( "SELECT * FROM {$wpdb->prefix}improveseo_uk_states ORDER BY name" );
					foreach ( $states as $state ) {
						$results [] = array (
								'id' => $state->id,
								'text' => $state->name . ' [' . $state->country_short . ']',
								'children' => true 
						);
					}
				}
			}
		} 		// Show all other countries
		else {
			$model = new GeoData ();
			
			if (isset ( $_GET ['id'] ) && $_GET ['id'] != '#') {
				$id = urldecode ( $_GET ['id'] );
				
				// Show zip codes
				if (substr_count ( $id, '/' )) {
					list ( $state, $city ) = explode ( '/', $id );
					
					$zippo = $model->zippo ( $country, $state, $city );
					foreach ( $zippo as $zip ) {
						$results [] = array (
								'id' => "$state/$city/{$zip->postal}",
								'text' => $zip->postal,
								'children' => false 
						);
					}
				} 				// Show cities
				else {
					$cities = $model->cities ( $country, $id );
					foreach ( $cities as $city ) {
						$results [] = array (
								'id' => "$id/{$city->id}",
								'text' => $city->place,
								'children' => true 
						);
					}
				}
			} else {
				// Show states
				$states = $model->states ( $country );
				foreach ( $states as $state ) {
					if (! $state->state)
						continue;
					
					$results [] = array (
							'id' => $state->state_code,
							'text' => $state->state,
							'children' => true 
					);
				}
			}
		}
	} elseif ($act == 'count_posts') {
		include_once ('functions.php');
		
		$tags = improveseo_search_geotags ( array (
				$_POST ['title'],
				$_POST ['content'],
				$_POST ['custom_title'],
				$_POST ['custom_description'],
				$_POST ['custom_keywords'],
				$_POST ['permalink'],
				$_POST ['tags'] 
		) );
		
		$geo_iterations = 0;
		
		if ($_POST ['locations']) {
			$locations = improveseo_expand_geodata ( $_POST ['country'], $_POST ['locations'], $tags );
			$geo_iterations = sizeof ( $locations );
		}
		
		if ($geo_iterations == 0)
			$iterations = max ( 1, Spintax::count ( Spintax::parse ( $title ) ) );
		else
			$iterations = $geo_iterations;
			
			// Count list items
		$items = improveseo_count_list_items ( $_POST );
		if ($items == 0) {
			$items = 1;
		}
		
		$results ['posts'] = $geo_iterations ? $geo_iterations * $items : ($items ? $items : $iterations);
	} elseif ($act == 'shortcode') {
		$shortcodeModel = new Shortcode ();
		
		$shortcode = isset($_POST['shortcode']) ? $_POST['shortcode'] : '';
        $media = isset($_POST['media']) ? $_POST['media'] : '';
        // Check if required fields are present
        if (empty($shortcode) || empty($media)) {
            // Handle missing required fields
            header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request', true, 400);
            echo 'Bad Request: Missing required fields.';
            exit();
        }
        if (!Validator::validate(compact('shortcode', 'media'), array(
            'shortcode' => 'required|unique:' . $shortcodeModel->getTable() . ',shortcode',
            'media' => 'required'
        ))) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
            echo Validator::get('shortcode');
            exit();
        }
		$shortcode = $_POST ['shortcode'];
		
		// Download all media files
		foreach ( $_POST ['media'] as &$media ) {
			if (isset ( $media ['url'] )) {
				$image = str_replace ( ':8000', '', $media ['url'] ); // Fix only for local dev
				$filename = sha1 ( $image . '-' . $shortcode ) . '.jpg';
				
				$exploded = explode ( '.', $image );
				$ext = $exploded [count ( $exploded ) - 1];
				
				if (preg_match ( '/jpg|jpeg/i', $ext ))
					$imageSrc = imagecreatefromjpeg ( $image );
				else if (preg_match ( '/png/i', $ext ))
					$imageSrc = imagecreatefrompng ( $image );
				else if (preg_match ( '/gif/i', $ext ))
					$imageSrc = imagecreatefromgif ( $image );
				else if (preg_match ( '/bmp/i', $ext ))
					$imageSrc = imagecreatefromwbmp ( $image );
				
				$imagedir = 'uploads/' . date ( 'Y' ) . '/' . date ( 'm' ) . '/' . $filename;
				
				include_once 'functions.php';
				improveseo_check_dir ( $imagedir );
				
				imagejpeg ( $imageSrc, WP_CONTENT_DIR . '/' . $imagedir );
				
				$media ['url'] = "/wp-content/$imagedir";
			}
		}
		
		$medias = $_POST ['media'];
		$media = json_encode ( $medias );
		
		$sql = $wpdb->prepare ( "INSERT INTO {$wpdb->prefix}improveseo_shortcodes (shortcode, type, content) VALUES (%s, %s, %s)", array (
				$shortcode,
				'static',
				$media 
		) );
		
		$wpdb->query ( $sql );
		
		$results ['success'] = 1;
	} elseif ($act == 'exif') {
		View::render ( 'exif.index' );
		
		exit ();
	} elseif ($act == 'word-ai') {
		$text = urlencode($_POST['text']);
        $quality = $_POST['quality'];
        $email = urlencode($_POST['email']);
        $pass = $_POST['pass'];

        $query = array(
            's' => $_POST['text'],
            'quality' => $_POST['quality'],
            'email' => get_option('improveseo_word_ai_email'),
            'pass' => get_option('improveseo_word_ai_pass'),
            'nonested' => $_POST['nonested'],
            'paragraph' => $_POST['paragraph'],
            'nooriginal' => $_POST['nooriginal'],
            'protected' => $_POST['protected'],
            'output' => 'json'
        );

        $response = wp_safe_remote_post('http://wordai.com/users/turing-api.php', array(
            'body' => $query,
        ));

        if (!is_wp_error($response)) {
            $results = json_decode(wp_remote_retrieve_body($response), true);
        } else {
            // Handle the error, e.g., $response->get_error_message()
        }
}
}
