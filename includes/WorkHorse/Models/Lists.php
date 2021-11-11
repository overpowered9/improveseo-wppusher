<?php

namespace WorkHorse\Models;

class Lists extends AbstractModel
{
	public $table = 'workhorse_lists';

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