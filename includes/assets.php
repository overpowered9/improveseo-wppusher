<?php
add_action( 'admin_enqueue_scripts', 'improveseo_enqueue_admin' );
function improveseo_enqueue_admin(){
	$my_current_screen = get_current_screen();

	wp_enqueue_style('improveseo-main', IMPROVESEO_DIR . '/assets/css/main.css', array('wp-admin'));

	wp_enqueue_style('improveseo-tree', IMPROVESEO_DIR . '/assets/css/tree.min.css');
	wp_enqueue_style('improveseo-fa', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css');
	wp_enqueue_style('improveseo-modalStyle',IMPROVESEO_DIR . '/assets/js/jquery.modal.min.css');

	//wp_enqueue_script('improveseo-bootstrap', IMPROVESEO_DIR . '/assets/js/bootstrap.min.js', array('jquery'), IMPROVESEO_VERSION, true);
	wp_enqueue_script('improveseo-main', IMPROVESEO_DIR . '/assets/js/main.js', array('jquery'), IMPROVESEO_VERSION, true);
	wp_enqueue_script('improveseo-dialog', IMPROVESEO_DIR . '/assets/js/dialog.js', array('jquery'), IMPROVESEO_VERSION, true);

	wp_enqueue_script('improveseo-posting', IMPROVESEO_DIR . '/assets/js/posting.js', array('jquery'), IMPROVESEO_VERSION, true);
	wp_localize_script('improveseo-posting', 'posting_ajax_vars', array(
		'site_url'      		=> 	site_url(),
		)
	);

	wp_enqueue_script('improveseo-imagescraper', IMPROVESEO_DIR . '/assets/js/imagescraper.js', array('jquery'), IMPROVESEO_VERSION, true);
	wp_enqueue_script('improveseo-videoscraper', IMPROVESEO_DIR . '/assets/js/videoscraper.js', array('jquery'), IMPROVESEO_VERSION, true);
	wp_enqueue_script('improveseo-exif', IMPROVESEO_DIR . '/assets/js/exif.js', array('jquery'), IMPROVESEO_VERSION, true);
	wp_enqueue_script('improveseo-wordai', IMPROVESEO_DIR . '/assets/js/wordai.js', array('jquery'), IMPROVESEO_VERSION, true);
	
	wp_enqueue_script('improveseo-tree', IMPROVESEO_DIR . '/assets/js/jstree.min.js', array('jquery'), IMPROVESEO_VERSION, true);
	wp_enqueue_script('improveseo-notify', IMPROVESEO_DIR . '/assets/js/notify.js', array('jquery'), IMPROVESEO_VERSION, true);
	wp_enqueue_script('improveseo-popup', IMPROVESEO_DIR . '/assets/js/popup.js', array('jquery'), IMPROVESEO_VERSION, true);
	
	wp_enqueue_script('improveseo-underscore', 'https://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.8.3/underscore-min.js', array('underscore'));
	
	wp_enqueue_script('improveseo-modal',IMPROVESEO_DIR . '/assets/js/jquery.modal.min.js', array('jquery'), IMPROVESEO_VERSION, true);	

	if ( isset( $my_current_screen->base )  ) {
		if($my_current_screen->base=="improve-seo_page_improveseo_posting" && isset($_REQUEST['action'])){
			wp_enqueue_script('improveseo-form', IMPROVESEO_DIR.'/assets/js/form.js', array('jquery'), IMPROVESEO_VERSION, true);
			wp_localize_script('improveseo-form', 'form_ajax_vars', array(
				'ajax_url'      		=> 	admin_url( 'admin-ajax.php' ),
				)
			);
		}
	}
}
add_action( 'after_wp_tiny_mce', 'improveseo_after_wp_tiny_mce' );
function improveseo_after_wp_tiny_mce() {
    printf( '<script type="text/javascript" src="%s"></script>',  IMPROVESEO_DIR.'/assets/js/shortcode-popup-button.js' );
}

add_action( 'wp_enqueue_scripts', 'improveseo_enqueue_front' );
function improveseo_enqueue_front(){
	wp_enqueue_style('improveseo-front', IMPROVESEO_DIR . '/assets/css/improveseo-front.css', array(), '2.0');
}