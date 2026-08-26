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

		// $field and $table come from the validation rule declared in calling
		// code, never from request data, but they are identifiers spliced into
		// SQL so hold them to a bare name. Anything else is reported as valid
		// rather than run, which matches the "no duplicate found" result.
		if (! preg_match('/^[A-Za-z0-9_]+$/', $field) || ! preg_match('/^[A-Za-z0-9_]+$/', $table)) {
			return true;
		}

		$sql = $wpdb->prepare("SELECT id, $field FROM $table WHERE $field = %s", [$data[$field]]); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter -- the column and table are identifiers, guarded to a bare name just above; the compared value is bound
		$result = $wpdb->get_row($sql); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter -- the column and table are identifiers, guarded to a bare name just above; the compared value is bound

		/* translators: %s is the form field name. */
		return $result && $result->$field && (!$except || ($except && $result->id != $except)) ? sprintf( __( '%s already exists.', 'improveseo' ), self::fieldName($field) ) : true;
	}
}