<?php

namespace WorkHorse\Validator;

class BaseValidator
{
	public function fieldName($field) 
	{
		return ucwords(implode(" ", explode("_", $field)));
	}
}