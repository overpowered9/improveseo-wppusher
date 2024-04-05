<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
use ImproveSEO\View;




define('IMPROVESEO_ROOT', dirname(__FILE__));

include_once 'autoloader.php';

View::render('wordai.index');
