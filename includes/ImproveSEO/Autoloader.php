<?php

namespace ImproveSEO;
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Autoloader
 */
class Autoloader
{
		
	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() 
	{
		spl_autoload_register(array($this, 'loader'));
	}

		
	/**
	 * loader
	 *
	 * @param  mixed $className
	 * @return void
	 */
	public function loader($className) 
	{
		// Corrections
		$className = str_replace('\\', '/', $className);
		
		$file = IMPROVESEO_ROOT .'/includes/'. $className . '.php';
		
		if (file_exists($file)) include $file;
	}
}