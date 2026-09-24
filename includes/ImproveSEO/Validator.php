<?php

namespace ImproveSEO;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


use ImproveSEO\Validator\ValidatorException;

class Validator
{
	public static $errorsKey = 'improveseo.validator.errors';
	public static $oldKey = 'improveseo.validator.old';

	public static function old($field, $default = null)
	{
		if (isset($_SESSION[self::$oldKey]) && isset($_SESSION[self::$oldKey][$field])) {
			$value = $_SESSION[self::$oldKey][$field];
			unset($_SESSION[self::$oldKey][$field]);

			return $value;
		}
		else {
			return $default;
		}
	}

	// Static like the rest of this class: validate() calls it as self::saveOld(), and calling a
	// non-static method statically is a fatal Error on PHP 8 — so every refused form (an empty
	// keyword list, a duplicate list name, …) ended on WordPress's "critical error" page instead
	// of coming back with the message and the values that were typed.
	public static function saveOld($field, $value)
	{
		$_SESSION[self::$oldKey][$field] = $value;
	}

	public static function error($field, $message)
	{
		if (!isset($_SESSION[self::$errorsKey][$field])) $_SESSION[self::$errorsKey][$field] = [];

		$_SESSION[self::$errorsKey][$field][] = $message;
	}

	public static function hasError($field)
	{
		return isset($_SESSION[self::$errorsKey]) && isset($_SESSION[self::$errorsKey][$field]);
	}

	public static function getErrors()
	{
		return $_SESSION[self::$errorsKey];
	}

	public static function get($field)
	{
		$error = $_SESSION[self::$errorsKey][$field][0];
		unset($_SESSION[self::$errorsKey][$field]);

		return $error;
	}

	public static function validate($data, $rulesList)
	{
		$_SESSION[self::$errorsKey] = [];
		$success = true;

		foreach ($rulesList as $field => $list) {
			$rules = !is_array($list) ? explode('|', $list) : $list;

			foreach ($rules as $rule) {
				$arguments = array();

				if (stristr($rule, ':')) {
					list($rule, $arguments) = explode(':', $rule);
					$arguments = explode(',', $arguments);
					if (!is_array($arguments)) $arguments = array($arguments);
				}

				$rule = ucwords(str_replace("_", " ", $rule));
				$rule = str_replace(" ", "", $rule);

				$validatorClass = "ImproveSEO\Validator\\$rule";

				if (class_exists($validatorClass)) {
					$arguments = array_merge(array($data, $field), $arguments);

					$result = call_user_func_array(array($validatorClass, 'validate'), $arguments);
					//$result = $validatorClass::validate($data, $field);
					if ($result !== true) {
						$success = false;

						self::error($field, $result);
					}
				} else {
					// esc_html() on the interpolated values: an uncaught exception prints its
					// message, so a throw is an output path like any other. $rule is derived
					// from the validation rules declared in calling code rather than from
					// request data, so this is defence in depth rather than a live hole — but
					// it costs nothing and the sniff is right that the message can reach a
					// screen.
					throw new ValidatorException( "Validator " . esc_html( $rule ) . ": class " . esc_html( $validatorClass ) . " doesn't exist" );
				}
			}
		}

		// Save old form values
		if (!$success) {
			foreach ($data as $field => $value) {
				self::saveOld($field, $value);
			}
		}

		return $success;
	}
}
