<?php

namespace ImproveSEO\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


class Lists extends AbstractModel
{
	public $table = 'improveseo_lists';

	public $timestamps = false;

	protected $fillable = array(
		'name',
		'list',
		'size',
		// JSON map of keyword => { search_volume, keyword_difficulty, intent, scope, fetched_at }.
		// Nullable: lists made by hand or before the Keyword Generator fetched metrics have none.
		'metrics'
	);

	public function setNameAttribute($value) 
	{
		$value = str_replace(' ', '-', strtolower($value));
		return $value;
	}
}