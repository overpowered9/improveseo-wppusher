<?php
 if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly  
use ImproveSEO\View;

function improveseo_keyword_generator() {
	View::render('features.keyword');
}
