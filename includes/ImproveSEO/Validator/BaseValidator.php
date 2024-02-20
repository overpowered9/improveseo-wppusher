<?php

namespace ImproveSEO\Validator;
if ( ! defined( 'ABSPATH' ) ) exit;

class BaseValidator
{
	
	public static function fieldName($field) 
	{
		return ucwords(implode(" ", explode("_", $field)));
	}
}