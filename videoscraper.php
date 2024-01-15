<?php

use ImproveSEO\View;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly  


define('IMPROVESEO_ROOT', dirname(__FILE__));

include_once 'autoloader.php';

View::render('videoscraper.index');