<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}






use ImproveSEO\Spintax;


use lsolesen\pel\PelIfd;


use lsolesen\pel\PelTag;


use lsolesen\pel\PelExif;


use lsolesen\pel\PelJpeg;


use lsolesen\pel\PelTiff;


use ImproveSEO\Models\Lists;


use ImproveSEO\Models\Country;


use ImproveSEO\Models\GeoData;


use lsolesen\pel\PelEntryByte;


use lsolesen\pel\PelEntryAscii;


use lsolesen\pel\PelEntryRational;


use lsolesen\pel\PelEntryUserComment;





function improveseo_configured() {


	return defined('DISABLE_WP_CRON');


}





function improveseo_permalink($previous = null) {


	$permalink = get_option('permalink_structure');





	$rewritecode = array(


		'%year%',


		'%monthnum%',


		'%day%',


		'%hour%',


		'%minute%',


		'%second%',


		'%postname%',


		'%post_id%',


		'%category%',


		'%author%',


		'%pagename%',


	);





	$date = explode(" ",gmdate('Y m d H i s', time()));


	$rewritereplace =


	array(


		$date[0],


		$date[1],


		$date[2],


		$date[3],


		$date[4],


		$date[5],


		'@title',


		'@id',


		'@category',


		'@author',


		'@title',


	);





	$permalink = home_url( str_replace($rewritecode, $rewritereplace, $permalink) );





	// Add span tag


	$permalink = str_replace('@title', '<span id="permalink">@title</span>', $permalink);





	if ($previous) {


		$permalink = str_replace('@title', $previous, $permalink);


	}





	return $permalink;


}





/**


 * Get current iteration for field.


 */


function improveseo_get_current_subiteration($current, $submax) {


	if ($submax == 0) {


		$submax = 1;


	}





	$double = $current / $submax;


	if (strstr($double, '.')) $double -= floor($double);


	else $double = 1;





	return $submax * $double;


}





/**


 * Get current spintax iteration for field.


 */


function improveseo_get_spintax_subiteration($max, $project, $iteration) {


	return $max < $project->spintax_iterations ? improveseo_get_current_subiteration($iteration, $max) : $iteration;


}





function improveseo_set_list_item($text, $lists, $iteration) {


	foreach ($lists as $name => $list) {


		$index = improveseo_get_current_subiteration($iteration, $list->size);


		$values = explode("\n", $list->list);





		$text = str_replace("@list:$name", trim($values[$index - 1]), $text);


	}





	return $text;


}





function improveseo_get_lists_from_text($fields) {


	$allowed = array('title', 'content', 'custom_title', 'custom_description', 'custom_keywords', 'permalink', 'tags');





	// Uncomment the below snippet to make list shortcode mandatory in title.


	/*if (!preg_match("/\@list:([^\s]+)/ms", $fields['title'])) {


		return array();


	}*/





	$lists = array();





	foreach ($allowed as $field) {


		preg_match_all("/\@list:([^\s]+)/ms", $fields[$field], $matches);


		if (isset($matches[1])) $lists = array_merge($lists, $matches[1]);


	}





	$model = new Lists();


	$lists = array_unique($lists);


	$items = array();





	foreach ($lists as $list) {


		$items[$list] = $model->getByName($list);


	}





	return $items;


}





function improveseo_count_list_items($fields) {


	$lists = improveseo_get_lists_from_text($fields);





	if (sizeof($lists) == 0) {


		return 0;


	}





	$items = 0;





	foreach ($lists as $list) {


		$items = max($items, $list->size);


	}





	return $items;


}





function improveseo_search_geotags($fields) {


	$tags = array();





	foreach ($fields as $field) {


		if (sizeof($tags) == 4) break;





		preg_match_all("/(@zip|@city|@stateshort|@state|@countryshort|@country)/", $field, $matches);





		if (isset($matches[1])) {


			if (!is_array($matches[1])) $matches[1] = array($matches[1]);


			foreach ($matches[1] as $match) {


				$tag = str_replace('@', '', $match);


				if (!in_array($tag, $tags)) $tags[] = $tag;


			}


		}


	}





	$tags = array_unique($tags);


	return $tags;


}





function improveseo_expand_geodata($country, $geodata, $tags) {


	global $wpdb;





	sort($geodata);


	$tweaked = [];





	$geodataModel = new GeoData();





	foreach ($geodata as $key => $loc) {


		if ($country == 'us') {


			// Only state


			if (preg_match("/^[A-z]{2}$/", $loc)) {


				if (in_array('city', $tags) || in_array('zip', $tags)) {


					if ((isset($geodata[$key + 1]) && !preg_match("/^$loc/", $geodata[$key + 1])) || !isset($geodata[$key + 1])) {


						$cities = $wpdb->get_results("SELECT id, zip FROM {$wpdb->prefix}improveseo_us_cities WHERE state_code = '$loc' AND 1=1". (!in_array('zip', $tags) ? ' GROUP BY city, county' : ''));





						foreach ($cities as $city) {


							if (in_array('zip', $tags))	$tweaked[] = "$loc/{$city->id}/{$city->zip}";


							elseif (in_array('city', $tags)) $tweaked[] = "$loc/{$city->id}";


						}


					}


				} else {


					$tweaked[] = $loc;


				}


			}


			// Only city


			elseif (preg_match("/^([A-z]{2})\/(\d+)$/", $loc, $loccy)) {


				if (in_array('zip', $tags)) {


					if ((isset($geodata[$key + 1]) && !preg_match("/^$loccy[1]\/$loccy[2]\//", $geodata[$key + 1])) || !isset($geodata[$key + 1])) {


						$city = $wpdb->get_row("SELECT city FROM {$wpdb->prefix}improveseo_us_cities WHERE id = {$loccy[2]}");


						$zippy = $wpdb->get_results("SELECT zip FROM {$wpdb->prefix}improveseo_us_cities WHERE state_code = '$loccy[1]' AND city = '{$city->city}' AND 1=1");





						foreach ($zippy as $zip) {


							$tweaked[] = "$loc/{$zip->zip}";


						}


					}


				} else {


					$tweaked[] = $loc;


				}


			}


			// Everything else


			else {


				$parts = explode("/", $loc);





				if (!in_array('zip', $tags) && !in_array('city', $tags)) $tweaked[] = $parts[0];


				elseif (!in_array('zip', $tags) && in_array('city', $tags)) $tweaked[] = "$parts[0]/$parts[1]";


				else $tweaked[] = $loc;


			}


		}


		elseif ($country == 'uk') {


			// Only state


			if (preg_match("/^\d+$/", $loc)) {


				if (in_array('city', $tags) || in_array('zip', $tags)) {


					if ((isset($geodata[$key + 1]) && !preg_match("/^$loc/", $geodata[$key + 1])) || !isset($geodata[$key + 1])) {


						$cities = $wpdb->get_results("SELECT id, postcode FROM {$wpdb->prefix}improveseo_uk_cities WHERE region_id = '$loc' AND 1=1". (!in_array('zip', $tags) ? ' GROUP BY name' : ''));





						foreach ($cities as $city) {


							if (in_array('zip', $tags))	$tweaked[] = "$loc/{$city->id}/{$city->postcode}";


							elseif (in_array('city', $tags)) $tweaked[] = "$loc/{$city->id}";


						}


					}


				} else {


					$tweaked[] = $loc;


				}


			}


			// Only city


			elseif (preg_match("/^(\d+)\/(\d+)$/", $loc, $loccy)) {


				if (in_array('zip', $tags)) {


					if ((isset($geodata[$key + 1]) && !preg_match("/^$loccy[1]\/$loccy[2]\//", $geodata[$key + 1])) || !isset($geodata[$key + 1])) {


						$city = $wpdb->get_row("SELECT name FROM {$wpdb->prefix}improveseo_uk_cities WHERE id = {$loccy[2]}");


						$zippy = $wpdb->get_results("SELECT postcode FROM {$wpdb->prefix}improveseo_uk_cities WHERE region_id = '$loccy[1]' AND name = '{$city->name}' AND 1=1");





						foreach ($zippy as $zip) {


							$tweaked[] = "$loc/{$zip->postcode}";


						}


					}


				} else {


					$tweaked[] = $loc;


				}


			}


			// Everything else


			else {


				$parts = explode("/", $loc);





				if (!in_array('zip', $tags) && !in_array('city', $tags)) $tweaked[] = $parts[0];


				elseif (!in_array('zip', $tags) && in_array('city', $tags)) $tweaked[] = "$parts[0]/$parts[1]";


				else $tweaked[] = $loc;


			}


		}


		// All other countries


		else {


			// Zipcode


			if (substr_count($loc, "/") == 2) {


				$parts = explode("/", $loc);





				if (!in_array('zip', $tags) && !in_array('city', $tags)) $tweaked[] = $parts[0];


				elseif (!in_array('zip', $tags) && in_array('city', $tags)) $tweaked[] = "$parts[0]/$parts[1]";


				else $tweaked[] = $loc;


			}


			// City


			elseif (substr_count($loc, "/") == 1) {


				if (in_array('zip', $tags)) {


					$parts = explode("/", $loc);





					if ((isset($geodata[$key + 1]) && !preg_match("/^$parts[0]\/$parts[1]\//", $geodata[$key + 1])) || !isset($geodata[$key + 1])) {


						$zippo = $geodataModel->zippo($country, $parts[0], $parts[1]);





						foreach ($zippo as $zip) {


							$tweaked[] = "$loc/{$zip->postal}";


						}


					}


				} else {


					$tweaked[] = $loc;


				}


			}


			// State


			else {


				if (in_array('city', $tags) || in_array('zip', $tags)) {


					if ((isset($geodata[$key + 1]) && !preg_match("/^$loc/", $geodata[$key + 1])) || !isset($geodata[$key + 1])) {


						if (in_array('zip', $tags)) {


							$postals = $geodataModel->getStateCitiesAndPostals($country, $loc);





							foreach ($postals as $postal) {


								$tweaked[] = "$loc/{$postal->id}/{$postal->postal}";


							}


						}


						elseif (in_array('city', $tags)) {


							$cities = $geodataModel->cities($country, $loc);





							foreach ($cities as $city) {


								$tweaked[] = "$loc/{$city->id}";


							}


						}


					}


				} else {


					$tweaked[] = $loc;


				}


			}


		}


	}





	// Remove non-used parts of locations


	$tweaked = array_unique($tweaked);





	return $tweaked;


}





/**


 * Get geo information from geopath.


 */


function improveseo_get_geodata($country, $geopath) {


	global $wpdb;





	$path = explode('/', $geopath);


	$result = array('country' => '', 'countryshort' => '', 'state' => '', 'stateshort' => '', 'city' => '', 'zip' => '');





	if ($country == 'us') {


		$result['country'] = 'United States';


		$result['countryshort'] = 'US';





		$state = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}improveseo_us_states WHERE state_code = '". $path[0] ."'");


		$result['state'] = $state->state;


		$result['stateshort'] = $state->state_code;





		if (isset($path[1])) {


			$city = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}improveseo_us_cities WHERE id = ". $path[1]);


			$result['city'] = $city->city;


		}


		if (isset($path[2])) $result['zip'] = $path[2];


	}


	elseif ($country == 'uk') {


		$result['country'] = 'United Kingdom';


		$result['countryshort'] = 'UK';





		$state = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}improveseo_uk_states WHERE id = ". $path[0]);


		$result['state'] = $state->name;


		$result['stateshort'] = $state->name;





		if (isset($path[1])) {


			$city = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}improveseo_uk_cities WHERE id = ". $path[1]);


			$result['city'] = $city->name;


		}


		if (isset($path[2])) $result['zip'] = $path[2];


	}


	// All other countries


	else {


		$countryModel = new Country();


		$geodataModel = new GeoData();





		$country = $countryModel->find($country);





		$result['country'] = $country->name;


		$result['countryshort'] = $country->short;





		$result['state'] = $geodataModel->getStateName($country->id, $path[0]);


		$result['stateshort'] = $path[0];





		if (isset($path[1])) {


			$city = $geodataModel->find($path[1]);


			$result['city'] = $city->place;


		}


		if (isset($path[2])) $result['zip'] = $path[2];


	}





	return $result;


}





function improveseo_spintax_the_field($value, $project, $spintaxIteration, $geo = false, $geoData = null, $lists = null) {


	$spintax = Spintax::parse($value);


	$max = Spintax::count($spintax);





	$iteration = improveseo_get_spintax_subiteration($max, $project, $spintaxIteration);





	$text = Spintax::make($value, $iteration, $spintax);


	


	if ($geo) $text = Spintax::geo($text, $geoData);





	if ($lists) $text = improveseo_set_list_item($value, $lists, $project->iteration);





	return $text;


}





function improveseo_check_dir($dir) {


	$dirs = explode("/", $dir);


	$check = WP_CONTENT_DIR;





	foreach ($dirs as $dir) {


		if (strstr($dir, '.')) continue;





		if (!$check) $check = $dir;


		else $check .= "/". $dir;





		if (!is_dir($check)) wp_mkdir_p($check);


	}


}





if (!function_exists('isJSON')) {


	function isJSON($string){


		return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;


	}


}





if (!function_exists('convertDecimalToDMS')) {


	/**


	 * Convert a decimal degree into degrees, minutes, and seconds.


	 *


	 * @param


	 *            int the degree in the form 123.456. Must be in the interval


	 *            [-180, 180].


	 *


	 * @return array a triple with the degrees, minutes, and seconds. Each


	 *         value is an array itself, suitable for passing to a


	 *         PelEntryRational. If the degree is outside the allowed interval,


	 *         null is returned instead.


	 */


	function convertDecimalToDMS($degree)


	{


	    if ($degree > 180 || $degree < - 180) {


	        return null;


	    }





	    $degree = abs($degree); // make sure number is positive


	                            // (no distinction here for N/S


	                            // or W/E).





	    $seconds = $degree * 3600; // Total number of seconds.





	    $degrees = floor($degree); // Number of whole degrees.


	    $seconds -= $degrees * 3600; // Subtract the number of seconds


	                                 // taken by the degrees.





	    $minutes = floor($seconds / 60); // Number of whole minutes.


	    $seconds -= $minutes * 60; // Subtract the number of seconds


	                               // taken by the minutes.





	    $seconds = round($seconds * 100, 0); // Round seconds with a 1/100th


	                                         // second precision.





	    return array(


	        array(


	            $degrees,


	            1


	        ),


	        array(


	            $minutes,


	            1


	        ),


	        array(


	            $seconds,


	            100


	        )


	    );


	}


}





if (!function_exists('addGpsInfo')) {


	/**


	 * Add GPS information to an image basic metadata.


	 * Any old Exif data


	 * is discarded.


	 *


	 * @param


	 *            string the input filename.


	 *


	 * @param


	 *            string the output filename. An updated copy of the input


	 *            image is saved here.


	 *


	 * @param


	 *            string image description.


	 *


	 * @param


	 *            float longitude expressed as a fractional number of degrees,


	 *            e.g. 12.345пїЅ. Negative values denotes degrees west of Greenwich.


	 *


	 * @param


	 *            float latitude expressed as for longitude. Negative values


	 *            denote degrees south of equator.


	 *


	 * @param


	 *            float the altitude, negative values express an altitude


	 *            below sea level.


	 *


	 * @param


	 *            string the date and time.


	 */


	function addGpsInfo($input, $output, $description, $longitude, $latitude, $altitude, $date_time) {


	    /* Load the given image into a PelJpeg object */


	    $jpeg = new PelJpeg($input);





	    /*


	     * Create and add empty Exif data to the image (this throws away any


	     * old Exif data in the image).


	     */


	    $exif = new PelExif();


	    $jpeg->setExif($exif);





	    /*


	     * Create and add TIFF data to the Exif data (Exif data is actually


	     * stored in a TIFF format).


	     */


	    $tiff = new PelTiff();


	    $exif->setTiff($tiff);





	    /*


	     * Create first Image File Directory and associate it with the TIFF


	     * data.


	     */


	    $ifd0 = new PelIfd(PelIfd::IFD0);


	    $tiff->setIfd($ifd0);





	    /*


	     * Create a sub-IFD for holding GPS information. GPS data must be


	     * below the first IFD.


	     */


	    $gps_ifd = new PelIfd(PelIfd::GPS);


	    $ifd0->addSubIfd($gps_ifd);





	    $inter_ifd = new PelIfd(PelIfd::INTEROPERABILITY);


	    $ifd0->addSubIfd($inter_ifd);





	    $ifd0->addEntry(new PelEntryAscii(PelTag::DATE_TIME, $date_time));


	    $ifd0->addEntry(new PelEntryAscii(PelTag::IMAGE_DESCRIPTION, $description));





	    $gps_ifd->addEntry(new PelEntryByte(PelTag::GPS_VERSION_ID, 2, 2, 0, 0));





	    /*


	     * Use the convertDecimalToDMS function to convert the latitude from


	     * something like 12.34пїЅ to 12пїЅ 20' 42"


	     */


	    list ($hours, $minutes, $seconds) = convertDecimalToDMS($latitude);





	    /* We interpret a negative latitude as being south. */


	    $latitude_ref = ($latitude < 0) ? 'S' : 'N';





	    $gps_ifd->addEntry(new PelEntryAscii(PelTag::GPS_LATITUDE_REF, $latitude_ref));


	    $gps_ifd->addEntry(new PelEntryRational(PelTag::GPS_LATITUDE, $hours, $minutes, $seconds));





	    /* The longitude works like the latitude. */


	    list ($hours, $minutes, $seconds) = convertDecimalToDMS($longitude);


	    $longitude_ref = ($longitude < 0) ? 'W' : 'E';





	    $gps_ifd->addEntry(new PelEntryAscii(PelTag::GPS_LONGITUDE_REF, $longitude_ref));


	    $gps_ifd->addEntry(new PelEntryRational(PelTag::GPS_LONGITUDE, $hours, $minutes, $seconds));





	    /*


	     * Add the altitude. The absolute value is stored here, the sign is


	     * stored in the GPS_ALTITUDE_REF tag below.


	     */


	    $gps_ifd->addEntry(new PelEntryRational(PelTag::GPS_ALTITUDE, array(


	        abs($altitude),


	        1


	    )));


	    /*


	     * The reference is set to 1 (true) if the altitude is below sea


	     * level, or 0 (false) otherwise.


	     */


	    $gps_ifd->addEntry(new PelEntryByte(PelTag::GPS_ALTITUDE_REF, (int) ($altitude < 0)));





	    /* Finally we store the data in the output file. */


	    file_put_contents($output, $jpeg->getBytes());


	}


}


function improveseo_generate_youtube_url($video_url){


    return preg_replace(


        "/\s*[a-zA-Z\/\/:\.]*youtu(be.com\/watch\?v=|.be\/)([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i",


        "//www.youtube.com/embed/$2",


        $video_url


    );





}


function improveseo_generate_vimeo_url($url){


    //This is a general function for generating an embed link of an FB/Vimeo/Youtube Video.


    $finalUrl = '';


    if(strpos($url, 'vimeo.com/') !== false) {


        //it is Vimeo video


        $videoId = explode("vimeo.com/",$url)[1];


        if(strpos($videoId, '&') !== false){


            $videoId = explode("&",$videoId)[0];


        }


        $finalUrl.='https://player.vimeo.com/video/'.$videoId;


    }


    return $finalUrl;


}


function improveseo_get_youtube_id($url)


{


    if (stristr($url,'youtu.be/'))


        {preg_match('/(https:|http:|)(\/\/www\.|\/\/|)(.*?)\/(.{11})/i', $url, $final_ID); return $final_ID[4]; }


    else 


        {@preg_match('/(https:|http:|):(\/\/www\.|\/\/|)(.*?)\/(embed\/|watch.*?v=|)([a-z_A-Z0-9\-]{11})/i', $url, $IDD); return $IDD[5]; }


}





// Function to add http


function improveseo_addHttp($url) {


      


    // Search the pattern


    if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {


          


        // If not exist then add http


        $url = "http://" . $url;


    }


      


    // Return the URL


    return $url;


}





function test_spintax(){


	$string = '{Best|Top|Reliable} Brick And Stone Contractor Chimayo, NM | Rakso Construction';


	//$string = 'The {best|good|amazing} spintax {tool|generator} &#124; test';


	//$string = 'I {love {PHP|Java|C|C++|JavaScript|Python} | hate {Ruby|PP}}';





	$text = improveseo_spintax_the_field($string, $project = array(), 3);


	echo $text;


	die;


}


//add_action('init', 'test_spintax');

/**
 * Get ImproveSEO server configuration
 * 
 * @return array Server configuration array
 */
function improveseo_get_server_config() {
    return array(
        'server_url' => 'https://imporve-seo-admin-server-nzbm.onrender.com',
        'api_key' => get_option('improveseo_api_key'),
        'site_code' => get_option('improveseo_site_code')
    );
}

/**
 * Check if ImproveSEO server is properly configured
 * 
 * @return bool True if configured, false otherwise
 */
function improveseo_is_server_configured() {
    $config = improveseo_get_server_config();
    return !empty($config['api_key']) && !empty($config['site_code']);
}

/**
 * Locate the FAQ section inside generated post content.
 *
 * The FAQ is delimited by a heading whose text is FAQ / FAQs / Frequently Asked Questions, and runs
 * until the next heading at the SAME OR HIGHER level (an <h3> inside an <h2> FAQ section belongs to
 * the FAQ; the next <h2> does not) — or to the end of the content when the FAQ is last, which is the
 * usual shape.
 *
 * @param string $html Post content, or any fragment of it.
 * @return array{start:int,length:int,body:string}|null Offsets of the section BODY (the heading
 *         itself is excluded and left in place), or null when there is no FAQ heading.
 */
function improveseo_locate_faq_section( $html ) {
    if ( ! is_string( $html ) || $html === '' ) {
        return null;
    }

    // The heading text may be wrapped (<h2><strong>FAQ</strong></h2>), hence the optional inner tags.
    $heading = '~<h([1-6])\b[^>]*>\s*(?:<[^>]+>\s*)*(?:FAQ|FAQs|F\.A\.Q\.?|Frequently\s+Asked\s+Questions)\b[^<]*(?:<[^>]+>\s*)*</h\1>~i';
    if ( ! preg_match( $heading, $html, $m, PREG_OFFSET_CAPTURE ) ) {
        return null;
    }

    $level = (int) $m[1][0];
    $start = $m[0][1] + strlen( $m[0][0] );
    $rest  = substr( $html, $start );

    // End at the next heading of the same or higher rank (h1..h$level).
    $length = strlen( $rest );
    if ( preg_match( '~<h([1-' . $level . '])\b[^>]*>~i', $rest, $next, PREG_OFFSET_CAPTURE ) ) {
        $length = $next[0][1];
    }

    return array(
        'start'  => $start,
        'length' => $length,
        'body'   => substr( $html, $start, $length ),
    );
}

/**
 * Parse the FAQ section into question/answer pairs.
 *
 * THE single source of truth for both the rebuilt markup (improveseo_normalize_faq_markup) and the
 * FAQPage JSON-LD (improveseo_build_faq_schema), so the structured data can never drift from what
 * the reader actually sees.
 *
 * Four input shapes are recognised, first one that yields pairs wins:
 *   1. Already-normalised .improveseo-faq-question / -answer blocks (makes this idempotent).
 *   2. The v2 server shape: every pair concatenated into ONE <p>, each question wrapped only in
 *      <strong>. This is the bug being fixed — <strong> is inline, so the whole section renders as
 *      continuous prose.
 *   3. Legacy "<strong>Q: …</strong>" / "A: …" pairs.
 *   4. Adjacent paragraphs, where a <p> ending in "?" is the question and the next <p> the answer.
 *
 * @param string $html Post content, or any fragment of it.
 * @return array<int, array{q:string,a:string}> Cleaned pairs; empty when there is no parsable FAQ.
 */
function improveseo_parse_faq_pairs( $html ) {
    $section = improveseo_locate_faq_section( $html );
    if ( ! $section ) {
        return array();
    }
    $body = $section['body'];

    // ── 1. Already normalised ────────────────────────────────────────────────
    if ( preg_match_all(
        '~<h3\b[^>]*class=["\'][^"\']*\bimproveseo-faq-question\b[^"\']*["\'][^>]*>(.*?)</h3>\s*<p\b[^>]*class=["\'][^"\']*\bimproveseo-faq-answer\b[^"\']*["\'][^>]*>(.*?)</p>~is',
        $body,
        $matches,
        PREG_SET_ORDER
    ) ) {
        return improveseo_clean_faq_pairs( $matches );
    }

    // ── 2/3. Bold-run split — the shape that produces the single-paragraph bug ─
    // Splitting (rather than matching pairs) is what handles the merged case: the
    // questions are the only landmarks in the run, so everything between one bold
    // question and the next is that question's answer, regardless of how many <p>
    // boundaries the generator did or did not emit.
    $parts = preg_split(
        '~<(?:strong|b)\b[^>]*>\s*((?:(?!</?(?:strong|b)\b).)*?\?)\s*</(?:strong|b)>~is',
        $body,
        -1,
        PREG_SPLIT_DELIM_CAPTURE
    );

    if ( is_array( $parts ) && count( $parts ) >= 3 ) {
        $pairs = array();
        // $parts[0] is whatever preceded the first question (a section intro). It is not a pair, and
        // normalize() re-emits it above the FAQ block rather than dropping it.
        for ( $i = 1; $i < count( $parts ); $i += 2 ) {
            $answer = isset( $parts[ $i + 1 ] ) ? $parts[ $i + 1 ] : '';
            $pairs[] = array( 1 => $parts[ $i ], 2 => $answer );
        }
        $cleaned = improveseo_clean_faq_pairs( $pairs );
        if ( ! empty( $cleaned ) ) {
            return $cleaned;
        }
    }

    // ── 4. Adjacent paragraphs ───────────────────────────────────────────────
    if ( preg_match_all( '~<p\b[^>]*>(.*?)</p>~is', $body, $paras ) ) {
        $pairs = array();
        $count = count( $paras[1] );
        for ( $i = 0; $i < $count - 1; $i++ ) {
            $text = trim( wp_strip_all_tags( $paras[1][ $i ] ) );
            $next = trim( wp_strip_all_tags( $paras[1][ $i + 1 ] ) );
            if ( $text !== '' && substr( $text, -1 ) === '?' && $next !== '' && substr( $next, -1 ) !== '?' ) {
                $pairs[] = array( 1 => $paras[1][ $i ], 2 => $paras[1][ $i + 1 ] );
                $i++; // consume the answer paragraph
            }
        }
        return improveseo_clean_faq_pairs( $pairs );
    }

    return array();
}

/**
 * Normalise raw regex matches into clean {q, a} pairs.
 *
 * Questions are reduced to plain text — any <strong>/<b> the generator wrapped them in is stripped
 * here, so nothing leaks through when the question is promoted to an <h3>. Answers keep inline
 * markup (links especially) but can never carry a block tag, which would be invalid inside the
 * <p class="improveseo-faq-answer"> they end up in.
 *
 * @param array $matches Rows where index 1 is the raw question and index 2 the raw answer.
 * @return array<int, array{q:string,a:string}>
 */
function improveseo_clean_faq_pairs( $matches ) {
    $faqs = array();
    $inline_ok = array(
        'a'      => array( 'href' => array(), 'title' => array(), 'target' => array(), 'rel' => array() ),
        'em'     => array(),
        'i'      => array(),
        'strong' => array(),
        'b'      => array(),
        'br'     => array(),
        'code'   => array(),
    );

    foreach ( $matches as $m ) {
        if ( ! isset( $m[1] ) || ! isset( $m[2] ) ) {
            continue;
        }

        $q = trim( wp_strip_all_tags( $m[1] ) );
        $q = preg_replace( '~\s+~u', ' ', $q );
        // Drop a leading "Q:" / "Q." / "Question:" label from the legacy shape.
        $q = trim( preg_replace( '~^(?:Q|Question)\s*[:.\)]\s*~i', '', $q ) );

        // Answers arrive as a run of markup: unwrap the block tags into plain text + inline markup,
        // then kses down to the inline allowlist.
        $a = preg_replace( '~<br\s*/?>~i', ' ', $m[2] );
        $a = preg_replace( '~</(?:p|div|li|h[1-6])>~i', ' ', $a );
        $a = preg_replace( '~<(?:p|div|li|ul|ol|h[1-6])\b[^>]*>~i', ' ', $a );
        $a = wp_kses( $a, $inline_ok );
        $a = trim( preg_replace( '~\s+~u', ' ', $a ) );
        $a = trim( preg_replace( '~^(?:A|Answer)\s*[:.\)]\s*~i', '', $a ) );

        if ( $q !== '' && $a !== '' ) {
            $faqs[] = array( 'q' => $q, 'a' => $a );
        }
    }

    return $faqs;
}

/**
 * Rebuild a post's FAQ section as discrete, block-level question/answer pairs.
 *
 * Runs regardless of what the generation server returned, so the output shape is guaranteed even
 * when the model ignores its instructions. The result is always:
 *
 *   <div class="improveseo-faq">
 *     <div class="improveseo-faq-item">
 *       <h3 class="improveseo-faq-question">Question text?</h3>
 *       <p class="improveseo-faq-answer">Answer text.</p>
 *     </div>
 *   </div>
 *
 * Idempotent: content that already carries this structure is returned byte-for-byte unchanged, which
 * is what lets this run at BOTH the generation seam and at render without compounding.
 *
 * Fails safe — if no pairs can be parsed the content is returned untouched. Never destroys content.
 *
 * @param string $html Post content, or any fragment of it.
 * @return string
 */
function improveseo_normalize_faq_markup( $html ) {
    if ( ! is_string( $html ) || $html === '' ) {
        return $html;
    }
    // Fast path: already normalised.
    if ( strpos( $html, 'improveseo-faq-item' ) !== false ) {
        return $html;
    }

    $section = improveseo_locate_faq_section( $html );
    if ( ! $section ) {
        return $html;
    }

    $faqs = improveseo_parse_faq_pairs( $html );
    if ( empty( $faqs ) ) {
        return $html;
    }

    // Preserve any lead-in prose that sat before the first question, so rebuilding the section can
    // never silently drop copy.
    $intro = '';
    if ( preg_match( '~^(.*?)<(?:strong|b)\b~is', $section['body'], $lead ) ) {
        $lead_text = trim( wp_strip_all_tags( $lead[1] ) );
        if ( $lead_text !== '' ) {
            $intro = '<p>' . esc_html( $lead_text ) . '</p>' . "\n";
        }
    }

    $rebuilt = $intro . '<div class="improveseo-faq">' . "\n";
    foreach ( $faqs as $f ) {
        $rebuilt .= '<div class="improveseo-faq-item">' . "\n";
        $rebuilt .= '<h3 class="improveseo-faq-question">' . esc_html( $f['q'] ) . '</h3>' . "\n";
        // $f['a'] is already kses'd to the inline allowlist in improveseo_clean_faq_pairs().
        $rebuilt .= '<p class="improveseo-faq-answer">' . $f['a'] . '</p>' . "\n";
        $rebuilt .= '</div>' . "\n";
    }
    $rebuilt .= '</div>' . "\n";

    return substr_replace( $html, $rebuilt, $section['start'], $section['length'] );
}

/**
 * Extract FAQ question/answer pairs from generated content.
 *
 * Thin wrapper kept for the schema callers below; improveseo_parse_faq_pairs() does the work and
 * handles every shape (normalised blocks, the merged single-<p> v2 output, legacy Q:/A:, adjacent
 * paragraphs). Sharing that one parser is what keeps the JSON-LD and the rendered markup in step.
 */
function improveseo_extract_faq_pairs( $html ) {
    if ( empty( $html ) ) {
        return array();
    }
    return improveseo_parse_faq_pairs( $html );
}

/** Build a FAQPage JSON-LD array from extracted Q/A pairs, or null if there are none. */
function improveseo_build_faq_schema( $faqs ) {
    if ( empty( $faqs ) ) {
        return null;
    }
    $entities = array();
    foreach ( $faqs as $f ) {
        $entities[] = array(
            '@type'          => 'Question',
            'name'           => $f['q'],
            'acceptedAnswer' => array(
                '@type' => 'Answer',
                'text'  => $f['a'],
            ),
        );
    }
    return array(
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        'mainEntity' => $entities,
    );
}

/**
 * Store the FAQPage schema as post meta at publish time. Called from the post builder right after the
 * post is created, so the schema is computed once and is guaranteed to match the published FAQ.
 * Stored as a JSON string under _improveseo_faq_schema. Returns true if stored.
 */
function improveseo_store_faq_schema_from_content( $post_id, $content ) {
    if ( empty( $post_id ) || is_wp_error( $post_id ) || empty( $content ) ) {
        return false;
    }
    if ( strpos( $content, 'improveseo-v2-content' ) === false
        && strpos( $content, 'main-content-section-improveseo' ) === false ) {
        return false;
    }
    $schema = improveseo_build_faq_schema( improveseo_extract_faq_pairs( $content ) );
    if ( ! $schema ) {
        return false;
    }
    update_post_meta( $post_id, '_improveseo_faq_schema', wp_json_encode( $schema ) );
    return true;
}

/**
 * On any post save, (re)store the FAQ schema if the post is ImproveSEO-generated content. This covers
 * every creation path (builder, cron, bulk projects) and keeps the schema in sync if the FAQ is edited.
 * The content-wrapper check in the helper makes this a no-op for everything else.
 */
function improveseo_maybe_store_faq_schema( $post_id, $post = null ) {
    if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
        return;
    }
    if ( ! $post ) {
        $post = get_post( $post_id );
    }
    if ( ! $post || empty( $post->post_content ) ) {
        return;
    }
    improveseo_store_faq_schema_from_content( $post_id, $post->post_content );
}
add_action( 'save_post', 'improveseo_maybe_store_faq_schema', 20, 2 );

/**
 * Inject FAQPage JSON-LD into the head of singular ImproveSEO posts.
 *
 * Prefers the schema stored at publish time (post meta) — robust, no parsing at render. Falls back to
 * parsing the content live for posts created before the meta was stored.
 */
function improveseo_inject_faq_schema() {
    if ( ! is_singular() ) {
        return;
    }
    $post = get_post();
    if ( ! $post ) {
        return;
    }

    // Prefer the schema stored at publish time (already valid JSON).
    $stored = get_post_meta( $post->ID, '_improveseo_faq_schema', true );
    if ( ! empty( $stored ) ) {
        echo "\n" . '<script type="application/ld+json">' . $stored . '</script>' . "\n";
        return;
    }

    // Fallback: parse from content (older posts, or if meta was never stored).
    if ( empty( $post->post_content ) ) {
        return;
    }
    if ( strpos( $post->post_content, 'improveseo-v2-content' ) === false
        && strpos( $post->post_content, 'main-content-section-improveseo' ) === false ) {
        return;
    }
    $schema = improveseo_build_faq_schema( improveseo_extract_faq_pairs( $post->post_content ) );
    if ( ! $schema ) {
        return;
    }
    echo "\n" . '<script type="application/ld+json">' . wp_json_encode( $schema ) . '</script>' . "\n";
}
add_action( 'wp_head', 'improveseo_inject_faq_schema' );

/**
 * Normalise the FAQ markup of ImproveSEO posts as they render on the front end.
 *
 * Newly generated content is already normalised at the generation seam (createAIpost2 /
 * createAIpost2bulk), so for those posts this is a no-op via the idempotence fast path. It exists
 * for posts created BEFORE the fix, whose stored post_content still has the whole FAQ merged into a
 * single <p>. Without it those posts would render differently on the front end than in the admin
 * preview, which also normalises.
 *
 * Gated on the ImproveSEO content wrapper, so it never touches a post this plugin did not generate.
 */
function improveseo_filter_faq_markup( $content ) {
    if ( empty( $content ) || ! is_string( $content ) ) {
        return $content;
    }
    if ( strpos( $content, 'improveseo-v2-content' ) === false
        && strpos( $content, 'main-content-section-improveseo' ) === false ) {
        return $content;
    }
    return improveseo_normalize_faq_markup( $content );
}
add_filter( 'the_content', 'improveseo_filter_faq_markup', 9 );



