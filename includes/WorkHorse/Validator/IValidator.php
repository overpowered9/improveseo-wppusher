<?php

namespace WorkHorse\Validator;

interface IValidator
{
	public static function validate($data, $field);
}