<?php

namespace WorkHorse\Models;

class GeoData extends AbstractModel
{
	public $table = 'workhorse_geodata';

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

		return $wpdb->get_results("SELECT state, state_code FROM {$this->getTable()} WHERE country_id = $country_id GROUP BY state");
	}

	public function cities($country_id, $state_code) 
	{
		global $wpdb;

		return $wpdb->get_results($wpdb->prepare("SELECT id, place FROM {$this->getTable()} WHERE country_id = $country_id AND state_code = %s GROUP BY place", $state_code));
	}

	public function zippo($country_id, $state_code, $city_id) 
	{
		global $wpdb;

		return $wpdb->get_results($wpdb->prepare("
			SELECT postal 
			FROM {$this->getTable()} 
			WHERE place = (SELECT place FROM {$this->getTable()} WHERE id = $city_id) AND country_id = $country_id AND state_code = %s", $state_code));
	}

	public function getStateName($country_id, $state_code) 
	{
		global $wpdb;

		return $wpdb->get_row($wpdb->prepare("
			SELECT state FROM {$this->getTable()} WHERE country_id = $country_id AND state_code = %s", $state_code))->state;
	}

	public function getStateCitiesAndPostals($country_id, $state_code) 
	{
		global $wpdb;

		return $wpdb->get_results($wpdb->prepare("
			SELECT id, postal FROM {$this->getTable()} WHERE country_id = $country_id AND state_code = %s
			", $state_code));
	}

	public function deleteByCountryId($country_id) 
	{
		global $wpdb;

		$wpdb->query("DELETE FROM {$this->getTable()} WHERE country_id = $country_id");
	}
}