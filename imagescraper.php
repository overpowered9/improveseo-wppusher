<?php

use ImproveSEO\View;
if ( ! defined( 'ABSPATH' ) ) exit;
define('IMPROVESEO_ROOT', dirname(__FILE__));

include_once 'autoloader.php';

View::render('imagescraper.index');