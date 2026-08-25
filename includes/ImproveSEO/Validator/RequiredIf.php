<?php

namespace ImproveSEO\Validator;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


class RequiredIf extends BaseValidator
{
	public static function validate($data, $field, $requiredField)
	{
		/* translators: %s is the form field name. */
		return (isset($data[$requiredField]) && !empty($data[$requiredField]) && isset($data[$field]) && !empty($data[$field])) || !isset($data[$requiredField]) ? true : sprintf( __( '%s is required', 'improveseo' ), self::fieldName($field) );
	}
}