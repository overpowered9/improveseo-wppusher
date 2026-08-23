<?php

namespace ImproveSEO\Validator;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


class PostType extends BaseValidator
{
	public static function validate($data, $field) 
	{
		return in_array($data[$field], ['post', 'page']) ? true : _('Not allowed post type');
	}
}