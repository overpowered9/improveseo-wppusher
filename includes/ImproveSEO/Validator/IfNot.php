<?php

namespace ImproveSEO\Validator;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


class IfNot extends BaseValidator
{
	public function validate($data, $field, $value, $check = null) 
	{
		$checker = $check ? $check : $data[$field];

		return !isset($data[$field]) || (isset($data[$field]) && $checker == $value) ? true : __('Value not allowed.', 'improveseo');
	}
}