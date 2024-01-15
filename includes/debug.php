<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly  

$start = null;
$debug = false;

function improveseo_debug_start() {
	global $start;

	$start = microtime(true);
}

function improveseo_debug_time() {
	global $start;

	$time = microtime(true) - $start;
	$start = microtime(true);

	return $time;
}

function improveseo_debug_message($message) {
	global $debug;
	
	if ($debug) {
		echo $message .'<br>';

		ob_flush();
		flush();
	}
}