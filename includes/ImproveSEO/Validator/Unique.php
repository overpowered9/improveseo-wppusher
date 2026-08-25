<?php

namespace ImproveSEO\Validator;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


class Unique extends BaseValidator
{
	public static function validate($data, $field, $table, $column = null, $except = null) 
	{
		global $wpdb;

		if (!$column) $column = $field;

		$sql = $wpdb->prepare("SELECT id, $field FROM $table WHERE $field = %s", [$data[$field]]);
		$result = $wpdb->get_row($sql);

		/* translators: %s is the form field name. */
		return $result && $result->$field && (!$except || ($except && $result->id != $except)) ? sprintf( __( '%s already exists.', 'improveseo' ), self::fieldName($field) ) : true;
	}
}