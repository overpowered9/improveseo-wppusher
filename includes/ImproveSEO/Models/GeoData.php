<?php

namespace ImproveSEO\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


class GeoData extends AbstractModel
{
	public $table = 'improveseo_geodata';

	protected $fillable = array(
		'country_id', 
		'postal', 
		'place', 
		'state', 
		'state_code', 
		'community', 
		'community_code', 
		'latitude', 
		'longitude', 
		'accuracy'
	);

	public function states($country_id) 
	{
		global $wpdb;

		return $wpdb->get_results($wpdb->prepare("SELECT state, state_code FROM {$this->getTable()} WHERE country_id = %d GROUP BY state", $country_id)); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter -- table name is an identifier and cannot be bound as a placeholder
	}

	public function cities($country_id, $state_code) 
	{
		global $wpdb;

		return $wpdb->get_results($wpdb->prepare("SELECT id, place FROM {$this->getTable()} WHERE country_id = %d AND state_code = %s GROUP BY place", $country_id, $state_code)); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter -- table name is an identifier and cannot be bound as a placeholder
	}

	public function zippo($country_id, $state_code, $city_id) 
	{
		global $wpdb;

		return $wpdb->get_results($wpdb->prepare("
			SELECT postal 
			FROM {$this->getTable()} 
			WHERE place = (SELECT place FROM {$this->getTable()} WHERE id = %d) AND country_id = %d AND state_code = %s", $city_id, $country_id, $state_code)); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter -- table name is an identifier and cannot be bound as a placeholder
	}

	public function getStateName($country_id, $state_code) 
	{
		global $wpdb;

		return $wpdb->get_row($wpdb->prepare("
			SELECT state FROM {$this->getTable()} WHERE country_id = %d AND state_code = %s", $country_id, $state_code))->state; // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter -- table name is an identifier and cannot be bound as a placeholder
	}

	public function getStateCitiesAndPostals($country_id, $state_code) 
	{
		global $wpdb;

		return $wpdb->get_results($wpdb->prepare("
			SELECT id, postal FROM {$this->getTable()} WHERE country_id = %d AND state_code = %s
			", $country_id, $state_code)); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter -- table name is an identifier and cannot be bound as a placeholder
	}

	public function deleteByCountryId($country_id) 
	{
		global $wpdb;

		$wpdb->query($wpdb->prepare("DELETE FROM {$this->getTable()} WHERE country_id = %d", $country_id)); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter -- table name is an identifier and cannot be bound as a placeholder
	}
}