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



$table_name = $wpdb->prefix . 'improveseo_bulktasks';


$sql = "CREATE TABLE IF NOT EXISTS $table_name (
  `id` int(80) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `number_of_tasks` int(10) NOT NULL,
  `schedule_posts` varchar(200) DEFAULT NULL,
  `number_of_post_schedule` varchar(20) DEFAULT NULL,
  `number_of_completed_task` int(20) DEFAULT 0,
  `schedule_frequency` varchar(200) DEFAULT NULL,
  `state` varchar(50) DEFAULT 'Unpublished',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

dbDelta ( $sql );




$table_name = $wpdb->prefix . 'improveseo_bulktasksdetails';


$sql = "CREATE TABLE IF NOT EXISTS $table_name (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bulktask_id` int(100) DEFAULT NULL,
  `keyword_list_name` text DEFAULT NULL,
  `keyword_name` varchar(200) DEFAULT NULL,
  `tone_of_voice` varchar(100) DEFAULT NULL,
  `select_exisiting_options` varchar(150) DEFAULT NULL,
  `details_to_include` text DEFAULT NULL,
  `content_lang` varchar(150) DEFAULT NULL,
  `point_of_view` varchar(150) DEFAULT NULL,
  `call_to_action` text DEFAULT NULL,
  `nos_of_words` varchar(150) DEFAULT NULL,
  `aiImage` varchar(200) DEFAULT NULL,
  `schedule_posts` varchar(100) DEFAULT NULL,
  `number_of_post_schedule` varchar(20) DEFAULT NULL,
  `assigning_authors` varchar(200) DEFAULT NULL,
  `assigning_authors_value` varchar(100) DEFAULT NULL,
  `cats` varchar(100) DEFAULT NULL,
  `testimonial` text DEFAULT NULL,
  `schedule_frequency` text DEFAULT NULL,
  `Button_SC` text DEFAULT NULL,
  `GoogleMap_SC` text DEFAULT NULL,
  `Video_SC` text DEFAULT NULL,
  `ai_title` text DEFAULT NULL,
  `ai_content` longtext DEFAULT NULL,
  `ai_image` text DEFAULT NULL,
  `published_on` varchar(12) DEFAULT NULL,
  `post_id` int(11) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `state` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY  (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

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

/* $wpdb->query ( "TRUNCATE TABLE " . $wpdb->prefix . "improveseo_us_states;" );
include_once $basepath . '/us.states.php';

$wpdb->query ( "TRUNCATE TABLE " . $wpdb->prefix . "improveseo_us_cities;" );
include_once $basepath . '/us.cities.php';

$wpdb->query ( "TRUNCATE TABLE " . $wpdb->prefix . "improveseo_uk_states;" );
include_once $basepath . '/uk.states.php';

$wpdb->query ( "TRUNCATE TABLE " . $wpdb->prefix . "improveseo_uk_cities;" );
include_once $basepath . '/uk.cities.php'; */

// Install other countries automatically (deprecated by 1.6.0)
if ($improveseo_db_version != get_option('improveseo_db_version')) {

}
