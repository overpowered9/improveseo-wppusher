<?php

namespace ImproveSEO\Validator;

class Unique extends BaseValidator
{
	public static function validate($data, $field, $table, $column = null, $except = null)
	{
		global $wpdb;

		// Default column to field if not provided
		if (!$column) $column = $field;

		// Sanitize field name to prevent SQL injection
		$field = esc_sql($field);

		// Prepare the SQL query with proper escaping
		$sql = $wpdb->prepare("SELECT id, $field FROM $table WHERE $field = %s", $data[$field]);
		$result = $wpdb->get_row($sql);

		// Return error message if value exists and not the exception, otherwise true
		if ($result && $result->$field && (!$except || ($except && $result->id != $except))) {
			return sprintf(__("%s already exists.", "improve-seo"), self::fieldName($field));
		}

		return true;
	}
}
