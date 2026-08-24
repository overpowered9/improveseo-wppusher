<?php

namespace ImproveSEO\Validator;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


class Numeric extends BaseValidator
{
	public static function validate($data, $field)
	{
		return !isset($data[$field]) || (isset($data[$field]) && empty($data[$field])) || (isset($data[$field]) && !empty($data[$field]) && preg_match("/^[0-9\.\,+\-]+$/", $data[$field])) ? true : sprintf( __( '%s must be numeric', 'improveseo' ), self::fieldName($field) );
	}
}