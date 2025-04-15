<?php

namespace ImproveSEO\Validator;

class BaseValidator
{
	public static function fieldName($field) 
	{
		return ucwords(implode(" ", explode("_", $field)));
	}
}