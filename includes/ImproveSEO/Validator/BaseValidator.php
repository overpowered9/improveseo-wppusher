<?php

namespace ImproveSEO\Validator;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


class BaseValidator
{
	public static function fieldName($field) 
	{
		return ucwords(implode(" ", explode("_", $field)));
	}
}