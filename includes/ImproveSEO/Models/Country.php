<?php

namespace ImproveSEO\Models;
if ( ! defined( 'ABSPATH' ) ) exit;

class Country extends AbstractModel
{
	protected $fillable = array('name', 'short');
}