<?php

namespace ImproveSEO\Validator;
if ( ! defined( 'ABSPATH' ) ) exit;

class PostType extends BaseValidator
{
	public static function validate($data, $field) 
	{
		return in_array($data[$field], ['post', 'page']) ? true : _('Not allowed post type');
	}
}