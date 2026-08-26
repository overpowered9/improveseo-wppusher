<?php

namespace ImproveSEO\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


class Shortcode extends AbstractModel
{
	public $fillable = array('shortcode', 'type', 'content');

	public function getStatic($limit = 20) 
	{
		global $wpdb;

		$sql = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}{$this->table} WHERE type = 'static' LIMIT %d, %d", [$this->offset, $limit]); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter -- table name is an identifier and cannot be bound as a placeholder
		return $wpdb->get_results($sql); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter -- table name is an identifier and cannot be bound as a placeholder
	}

	public function countStatic() 
	{
		global $wpdb;

		$row = $wpdb->get_row("SELECT COUNT(*) AS total FROM {$wpdb->prefix}{$this->table} WHERE type = 'static'"); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter -- table name is an identifier and cannot be bound as a placeholder
		return $row->total;
	}

	public function getDynamic($limit = 20) 
	{
		global $wpdb;

		$sql = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}{$this->table} WHERE type = 'dynamic' LIMIT %d, %d", [$this->offset, $limit]); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter -- table name is an identifier and cannot be bound as a placeholder
		return $wpdb->get_results($sql); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter -- table name is an identifier and cannot be bound as a placeholder
	}

	public function countDynamic() 
	{
		global $wpdb;

		$row = $wpdb->get_row("SELECT COUNT(*) AS total FROM {$wpdb->prefix}{$this->table} WHERE type = 'dynamic'"); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter -- table name is an identifier and cannot be bound as a placeholder
		return $row->total;
	}
}