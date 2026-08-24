<?php

namespace ImproveSEO\Validator;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


class Required extends BaseValidator
{
	public static function validate($data, $field)
	{
		return isset($data[$field]) && !empty($data[$field]) ? true : sprintf( __( '%s is required', 'improveseo' ), self::fieldName($field) );
	}
}