<?php

$start = null;
$debug = false;

function workhorse_debug_start() {
	global $start;

	$start = microtime(true);
}

function workhorse_debug_time() {
	global $start;

	$time = microtime(true) - $start;
	$start = microtime(true);

	return $time;
}

function workhorse_debug_message($message) {
	global $debug;
	
	if ($debug) {
		echo $message .'<br>';

		ob_flush();
		flush();
	}
}