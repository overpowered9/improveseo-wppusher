<?php

use ImproveSEO\Spintax;
use ImproveSEO\lsolesen\pel\PelIfd;
use ImproveSEO\lsolesen\pel\PelTag;
use ImproveSEO\lsolesen\pel\PelExif;
use ImproveSEO\lsolesen\pel\PelJpeg;
use ImproveSEO\lsolesen\pel\PelTiff;
use ImproveSEO\Models\Lists;
use ImproveSEO\Models\Country;
use ImproveSEO\Models\GeoData;
use ImproveSEO\lsolesen\pel\PelEntryByte;
use ImproveSEO\lsolesen\pel\PelEntryAscii;
use ImproveSEO\lsolesen\pel\PelEntryRational;
use ImproveSEO\lsolesen\pel\PelEntryUserComment;



if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly  

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

	$date = explode(" ",date('Y m d H i s', time()));
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
function improveseo_set_new_list_item($text, $lists, $iteration) {
    static $counter = 1;

    error_log("Lists in function => ". json_encode($lists));
    foreach ($lists as $name => $list) {
        $index = improveseo_get_current_subiteration($counter++, $list->size);
        $values = preg_split('/\r\n|\r|\n/', $list->list);
//        $text = str_replace("@list:$name", trim($values[$index - 1]), $text);
        $text = $list->list;
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
		preg_match_all('/@list:([a-zA-Z0-9_-]+)/', $fields[$field], $matches);
		if (!empty($matches[1])) {
			$lists = array_merge($lists, $matches[1]);
		}
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
	$sizes = improveseo_get_lists_from_text($fields);

	if (sizeof($sizes) == 0) {
		return 0;
	}

	$items = 0;

	foreach ($sizes as $size) {
		$items = max($items, $size);
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

                        $table_name = $wpdb->prefix . 'improveseo_us_cities';
                        $cities = $wpdb->get_results(
                            $wpdb->prepare(
                                "SELECT id, zip FROM %s WHERE state_code = %s AND 1=1%s",
                                $table_name,
                                $loc,
                                !in_array('zip', $tags) ? ' GROUP BY city, county' : ''
                            )
                        );


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
                        $city = $wpdb->get_row(
                            $wpdb->prepare(
                                "SELECT city FROM {$wpdb->prefix}improveseo_us_cities WHERE id = %d",
                                $loccy[2]
                            )
                        );
                        $zippy = $wpdb->get_results(
                            $wpdb->prepare(
                                "SELECT zip FROM {$wpdb->prefix}improveseo_us_cities WHERE state_code = %s AND city = %s AND 1=1",
                                $loccy[1],
                                $city->city
                            )
                        );
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
                        $cities = $wpdb->get_results(
                            $wpdb->prepare(
                                "SELECT id, postcode FROM {$wpdb->prefix}improveseo_uk_cities WHERE region_id = %s AND 1=1%s",
                                $loc,
                                !in_array('zip', $tags) ? ' GROUP BY name' : ''
                            )
                        );

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
                        $city = $wpdb->get_row(
                            $wpdb->prepare(
                                "SELECT name FROM {$wpdb->prefix}improveseo_uk_cities WHERE id = %d",
                                $loccy[2]
                            )
                        );
                        $zippy = $wpdb->get_results(
                            $wpdb->prepare(
                                "SELECT postcode FROM {$wpdb->prefix}improveseo_uk_cities WHERE region_id = %s AND name = %s AND 1=1",
                                $loccy[1],
                                $city->name
                            )
                        );

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

        $state = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}improveseo_us_states WHERE state_code = %s",
                $path[0]
            )
        );

        $result['state'] = $state->state;
		$result['stateshort'] = $state->state_code;

		if (isset($path[1])) {
            $city = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}improveseo_us_cities WHERE id = %d",
                    $path[1]
                )
            );

            $result['city'] = $city->city;
		}
		if (isset($path[2])) $result['zip'] = $path[2];
	}
	elseif ($country == 'uk') {
		$result['country'] = 'United Kingdom';
		$result['countryshort'] = 'UK';

        $state = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}improveseo_uk_states WHERE id = %d",
                $path[0]
            )
        );

        $result['state'] = $state->name;
		$result['stateshort'] = $state->name;

		if (isset($path[1])) {
            $city = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}improveseo_uk_cities WHERE id = %d",
                    $path[1]
                )
            );

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
	
}

if (!function_exists('improveseo_isJSON')) {
	function improveseo_isJSON($string){
		return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
	}
}

if (!function_exists('improveseo_convertDecimalToDMS')) {
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
	function improveseo_convertDecimalToDMS($degree)
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
	function improveseo_addGpsInfo($input, $output, $description, $longitude, $latitude, $altitude, $date_time) {
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
	     * Use the improveseo_convertDecimalToDMS function to convert the latitude from
	     * something like 12.34пїЅ to 12пїЅ 20' 42"
	     */
	    list ($hours, $minutes, $seconds) = improveseo_convertDecimalToDMS($latitude);

	    /* We interpret a negative latitude as being south. */
	    $latitude_ref = ($latitude < 0) ? 'S' : 'N';

	    $gps_ifd->addEntry(new PelEntryAscii(PelTag::GPS_LATITUDE_REF, $latitude_ref));
	    $gps_ifd->addEntry(new PelEntryRational(PelTag::GPS_LATITUDE, $hours, $minutes, $seconds));

	    /* The longitude works like the latitude. */
	    list ($hours, $minutes, $seconds) = improveseo_convertDecimalToDMS($longitude);
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

