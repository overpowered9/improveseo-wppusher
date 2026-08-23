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
		'size'
	);

	public function setNameAttribute($value) 
	{
		$value = str_replace(' ', '-', strtolower($value));
		return $value;
	}
}