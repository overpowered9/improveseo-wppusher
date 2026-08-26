<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
 

 /*======= loading template files ======*/
function wt_load_templates( $template_name, $vars = null) {
    if( $vars != null && is_array($vars) ){
       extract( $vars );
    };

    $template_path =  WT_PATH . "/modules/{$template_name}";
    if( file_exists( $template_path ) ){
        include  $template_path ;
    } else {
       // The path is logged rather than printed: it exposes the filesystem layout to whoever
		// triggered the failure, and is only useful to a developer reading the log anyway.
		error_log( "improveseo: error while loading file {$template_path}" );
		die( 'Error while loading a plugin template.' );
    }
}


/* ==== print defualt array ==== */
function wt_pa($arr){
   echo '<pre>';
   print_r($arr);
   echo '</pre>';
}