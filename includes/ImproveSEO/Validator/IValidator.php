<?php

namespace ImproveSEO\Validator;
if ( ! defined( 'ABSPATH' ) ) exit;

interface IValidator
{
	public static function validate($data, $field);
}