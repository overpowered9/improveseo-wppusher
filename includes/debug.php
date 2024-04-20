<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

$improveseo_start = null;
$improveseo_debug = false;

function improveseo_debug_start()
{
    global $improveseo_start;

    $improveseo_start = microtime(true);
}

function improveseo_debug_time()
{
    global $improveseo_start;

    $time = microtime(true) - $improveseo_start;
    $improveseo_start = microtime(true);

    return $time;
}

function improveseo_debug_message($message)
{
    global $improveseo_debug;

    if ($improveseo_debug) {
        echo wp_kses_post($message) . '<br>';


        ob_flush();
        flush();
    }
}