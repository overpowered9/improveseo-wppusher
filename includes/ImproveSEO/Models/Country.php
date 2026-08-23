<?php

namespace ImproveSEO\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


class Country extends AbstractModel
{
	protected $fillable = array('name', 'short');
}