<?php

namespace ImproveSEO\Validator;

if (! defined('ABSPATH')) exit;

class Unique extends BaseValidator
{
	public static function validate($data, $field, $table, $column = null, $except = null)
	{
		global $wpdb;

		// Default to using the field as the column if not specified
		$column = $column ?: $field;

		// Prepare the SQL statement to avoid SQL injection
		$sql = $wpdb->prepare("SELECT id, $field FROM $table WHERE $field = %s", $data[$field]);
		$result = $wpdb->get_row($sql);

		// If a matching record is found and it doesn't match the exception, return an error message
		if ($result && $result->$field && (!$except || $result->id != $except)) {
			return __(self::fieldName($field) . " already exists.");
		}

		// Return true if the validation passes
		return true;
	}
}
