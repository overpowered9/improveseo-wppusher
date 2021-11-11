<?php

namespace WorkHorse;

use WorkHorse\Models\GeoData;

class Geo
{
	private static $geo_source_url = 'http://usecrackedpluginandrootdirectorywillbewiped.com/geo/';

	public static function getCountriesList()
	{
		$contents = file_get_contents(self::$geo_source_url . 'list.php');
		return json_decode($contents);
	}

	public static function installCountry($country_id, $country)
	{
		$geodataModel = new GeoData();

		$fileHandle = fopen(self::$geo_source_url ."data/". rawurlencode($country->filename), "r");
		if ($fileHandle) {
			while (($buffer = fgets($fileHandle, 4096)) !== false) {
				$fields = explode("\t", $buffer);

				if (empty($fields[4])) $fields[4] = substr(md5($fields[3]), 0, 8);

				$geodataModel->create(array(
					'country_id'      => $country_id,
					'postal'          => $fields[1],
					'place'           => $fields[2],
					'state'           => $fields[3],
					'state_code'      => $fields[4],
					'community'       => $fields[5],
					'community_code'  => $fields[6],
					'latitude'        => $fields[7],
					'longitude'       => $fields[8],
					'accuracy'        => $fields[9]
				));
			}
			fclose($fileHandle);
		} else {
			error_log("============= No file handle");
		}
	}
}
