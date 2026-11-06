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

/**
 * Prints the inside of an AI loading overlay (#loadingAIImage / #loadingAIData):
 * four brand-coloured squares (assets/css/style.css, .iseo-img-loader) and a status
 * line that changes every few seconds while the overlay is open (rotation in
 * assets/js/custom-plugin-script.js). The messages are real, translatable text,
 * replacing the ones that used to be baked into AI-generated.gif and
 * Writing-Optimization.gif.
 *
 * @param string $kind 'image' while a cover image is generated, 'content' while the article is written.
 */
function improveseo_ai_loader( $kind = 'image' ) {
	if ( 'content' === $kind ) {
		// Same messages, in the same order, as Writing-Optimization.gif showed.
		$messages = array(
			__( 'Analyzing your input details', 'improveseo' ),
			__( 'Finding strong SEO keywords', 'improveseo' ),
			__( 'Pinpointing your ideal audience', 'improveseo' ),
			__( 'Shaping a clear, confident intro', 'improveseo' ),
			__( 'Crafting quality content', 'improveseo' ),
			__( 'Searching for real FAQs', 'improveseo' ),
			__( 'Crafting a natural, effective call to action', 'improveseo' ),
		);
	} else {
		// The first line is the message AI-generated.gif showed; the rest keep the user informed.
		$messages = array(
			__( 'We’re getting your AI image ready, just a moment!', 'improveseo' ),
			__( 'Reading your title and keyword', 'improveseo' ),
			__( 'Choosing the right style for your audience', 'improveseo' ),
			__( 'Composing the scene', 'improveseo' ),
			__( 'Adding colour and fine details', 'improveseo' ),
			__( 'Optimizing the image for your post', 'improveseo' ),
			__( 'Almost there — adding the finishing touches', 'improveseo' ),
		);
	}
	?>
	<div class="iseo-img-loader iseo-img-loader--<?php echo esc_attr( $kind ); ?>" role="status" aria-live="polite" data-iseo-messages="<?php echo esc_attr( wp_json_encode( $messages ) ); ?>">
		<p class="iseo-img-loader-text"><?php echo esc_html( $messages[0] ); ?></p>
		<div class="iseo-img-loader-anim" aria-hidden="true"><div></div><div></div><div></div><div></div></div>
	</div>
	<?php
}
