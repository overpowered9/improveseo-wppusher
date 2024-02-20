<?php

namespace ImproveSEO\Models;
if ( ! defined( 'ABSPATH' ) ) exit;

class Task extends AbstractModel
{
	public $fillable = array('name', 'content', 'options', 'iteration', 'spintax_iterations', 'max_iterations', 'deleted_at', 'finished_at', 'state', 'cats');

	protected $casts = array(
		'content' => 'array|b64',
		'options' => 'array|b64'
	);

	public function getDraft()
	{
		return $this->getByState('Draft');
	}

	public function getPublished()
	{
		return $this->getByState('Published');
	}

	public function getActive()
	{
		global $wpdb;

		return $wpdb->get_results("SELECT * FROM ". $this->getTable() ." WHERE iteration < max_iterations");
	}
}
