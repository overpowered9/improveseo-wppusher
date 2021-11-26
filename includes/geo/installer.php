<?php
use ImproveSEO\Models\Country;
use ImproveSEO\Models\GeoData;

// US States Table
$table_name = $wpdb->prefix . 'improveseo_us_states';

$charset_collate = $wpdb->get_charset_collate ();

$sql = "CREATE TABLE IF NOT EXISTS $table_name (
  `state` varchar(22) NOT NULL,
  `state_code` char(2) NOT NULL,
  PRIMARY KEY (`state_code`)
) $charset_collate;";

dbDelta ( $sql );

// US Cities Table
$table_name = $wpdb->prefix . 'improveseo_us_cities';

$sql = "CREATE TABLE IF NOT EXISTS $table_name (
  `id` INT(10) UNSIGNED AUTO_INCREMENT,
  `city` varchar(50) NOT NULL,
  `state_code` char(2) NOT NULL,
  `zip` int(5) unsigned zerofill NOT NULL,
  `latitude` double NOT NULL,
  `longitude` double NOT NULL,
  `county` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) $charset_collate;";

dbDelta ( $sql );

// UK States Table
$table_name = $wpdb->prefix . 'improveseo_uk_states';

$sql = "CREATE TABLE IF NOT EXISTS $table_name (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `country` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `country_short` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) $charset_collate;";

dbDelta ( $sql );

// UK Cities Table
$table_name = $wpdb->prefix . 'improveseo_uk_cities';

$sql = "CREATE TABLE IF NOT EXISTS $table_name (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `region_id` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `postcode` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `latitude` double NOT NULL DEFAULT '0',
  `longitude` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) $charset_collate;";

dbDelta ( $sql );

// Other Countries Table
$table_name = $wpdb->prefix . 'improveseo_countries';
$countries_table = $table_name;

$sql = "CREATE TABLE IF NOT EXISTS $table_name (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(30) NOT NULL COLLATE 'utf8_unicode_ci',
  `short` CHAR(2) NOT NULL COLLATE 'utf8_unicode_ci',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) $charset_collate;";

dbDelta ( $sql );

$table_name = $wpdb->prefix . 'improveseo_geodata';

$sql = "CREATE TABLE IF NOT EXISTS $table_name (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `country_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `postal` VARCHAR(20) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
  `place` VARCHAR(180) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
  `state` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
  `state_code` VARCHAR(20) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
  `community` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
  `community_code` VARCHAR(20) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
  `latitude` DECIMAL(10,7) NULL DEFAULT NULL,
  `longitude` DECIMAL(10,7) NULL DEFAULT NULL,
  `accuracy` TINYINT(3) UNSIGNED NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `country_id_state` (`country_id`, `state`) USING BTREE,
  CONSTRAINT `FK__$countries_table` FOREIGN KEY (`country_id`) REFERENCES $countries_table (`id`) ON DELETE CASCADE
) $charset_collate;";

dbDelta ( $sql );

// Install Geo Data (UK and US)
/*
 * $geo_source_url = 'http://usecrackedpluginandrootdirectorywillbewiped.com/geo/';
 * $geo_files = array(
 * 'us.states.ph_',
 * 'us.cities.ph_',
 * 'uk.states.ph_',
 * 'uk.cities.ph_'
 * );
 */
$basepath = dirname ( __FILE__ );
/*
 * @set_time_limit(0);
 *
 * // Download all geo files
 *
 * if (!is_dir($basepath .'/data')) mkdir($basepath .'/data', 0777);
 *
 * foreach ($geo_files as $file) {
 * $content = @file_get_contents($geo_source_url . $file);
 *
 * $file = preg_replace("/ph\_$/", "php", $file);
 * file_put_contents($basepath .'/'. $file, $content);
 * }
 */

$wpdb->query ( "TRUNCATE TABLE " . $wpdb->prefix . "improveseo_us_states;" );
include_once $basepath . '/us.states.php';

$wpdb->query ( "TRUNCATE TABLE " . $wpdb->prefix . "improveseo_us_cities;" );
include_once $basepath . '/us.cities.php';

$wpdb->query ( "TRUNCATE TABLE " . $wpdb->prefix . "improveseo_uk_states;" );
include_once $basepath . '/uk.states.php';

$wpdb->query ( "TRUNCATE TABLE " . $wpdb->prefix . "improveseo_uk_cities;" );
include_once $basepath . '/uk.cities.php';

// Install other countries automatically (deprecated by 1.6.0)
if ($improveseo_db_version != get_option('improveseo_db_version')) {

}
