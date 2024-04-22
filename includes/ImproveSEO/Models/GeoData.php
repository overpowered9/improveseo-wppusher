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

    public function states($country_id)
    {
        global $wpdb;

        return $wpdb->get_results("SELECT state, state_code FROM tablename WHERE country_id = $country_id GROUP BY state");
    }

    public function cities($country_id, $state_code)
    {
        global $wpdb;
        $sql = "SELECT id, place FROM tablename WHERE country_id = $country_id AND state_code = %s GROUP BY place";
        $sql = str_replace("tablename", $this->getTable(), $sql);
        return $wpdb->get_results($wpdb->prepare($sql, $state_code));
    }

    public function zippo($country_id, $state_code, $city_id)
    {
        global $wpdb;
        $sql = "SELECT postal FROM tablename
			WHERE place = (SELECT place FROM tablename WHERE id = $city_id) AND country_id = $country_id AND state_code = %s";
        $sql = str_replace("tablename", $this->getTable(), $sql);
        return $wpdb->get_results($wpdb->prepare($sql, $state_code));
    }

    public function getStateName($country_id, $state_code)
    {
        global $wpdb;
        $sql = "SELECT state FROM tablename WHERE country_id = %d AND state_code = %s";
        $sql = $wpdb->prepare($sql,$country_id,$state_code);
        $sql = str_replace("tablename",$this->getTable(),$sql);
        return $wpdb->get_row($sql)->state;
    }

    public function getStateCitiesAndPostals($country_id, $state_code)
    {
        global $wpdb;
        $sql = "SELECT id, postal FROM tablename WHERE country_id = $country_id AND state_code = %s";
        $sql = str_replace("tablename",$this->getTable(),$sql);
        return $wpdb->get_results($wpdb->prepare($sql, $state_code));
    }

    public function deleteByCountryId($country_id)
    {
        global $wpdb;
        $sql = "DELETE FROM tablename WHERE country_id = %d";
        $sql = str_replace("tablename",$this->getTable(),$sql);
        $sql = $wpdb->prepare($sql,$country_id);
        $wpdb->query($sql);
    }
}