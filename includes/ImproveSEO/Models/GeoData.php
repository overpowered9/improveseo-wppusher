<?php

namespace ImproveSEO\Models;
if (!defined('ABSPATH')) exit;

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



    public function cities($country_id, $state_code)
    {
        global $wpdb;
        $sql = "SELECT id, place FROM tablename WHERE country_id = %d AND state_code = %s GROUP BY place";
        return $wpdb->get_results($wpdb->prepare($sql,$country_id,$state_code));
    }

    public function zippo($country_id, $state_code, $city_id)
    {
        global $wpdb;
        $tablename = $this->getTable();
        $sql = "SELECT postal FROM tablename
			WHERE place = (SELECT place FROM tablename WHERE id = %d) AND country_id = %d AND state_code = %s";
        return $wpdb->get_results($wpdb->prepare($sql, array($city_id,$country_id,$state_code)));
    }

    public function getStateName($country_id, $state_code)
    {
        global $wpdb;
        $tablename = $this->getTable();
        $sql = "SELECT state FROM tablename WHERE country_id = %d AND state_code = %s";
        $sql = $wpdb->prepare($sql,$country_id,$state_code);
        return $wpdb->get_row($sql)->state;
    }

    public function getStateCitiesAndPostals($country_id, $state_code)
    {
        global $wpdb;
        $tablename = $this->getTable();
        $sql = "SELECT id, postal FROM tablename WHERE country_id = %d  AND state_code = %s";
        return $wpdb->get_results($wpdb->prepare($sql, array($country_id,$state_code)));
    }

    public function deleteByCountryId($country_id)
    {
        global $wpdb;
        $tablename = $this->getTable();
        $sql = "DELETE FROM tablename WHERE country_id = %d";
        $sql = $wpdb->prepare($sql,$country_id);
        $wpdb->query($sql);
    }
}