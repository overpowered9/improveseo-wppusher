<?php
add_action( 'admin_enqueue_scripts', 'improveseo_enqueue_admin' );
function improveseo_enqueue_admin(){
	wp_enqueue_style('improveseo-main', IMPROVESEO_DIR . '/assets/css/main.css', array('wp-admin'));

	wp_enqueue_style('improveseo-tree', IMPROVESEO_DIR . '/assets/css/tree.min.css');
	wp_enqueue_style('improveseo-fa', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css');
	wp_enqueue_style('improveseo-modalStyle',IMPROVESEO_DIR . '/assets/js/jquery.modal.min.css');

	wp_enqueue_script('improveseo-main', IMPROVESEO_DIR . '/assets/js/main.js', array('jquery'));
	wp_enqueue_script('improveseo-dialog', IMPROVESEO_DIR . '/assets/js/dialog.js', array('jquery'));

	wp_enqueue_script('improveseo-posting', IMPROVESEO_DIR . '/assets/js/posting.js', array('jquery'));
	wp_enqueue_script('improveseo-imagescraper', IMPROVESEO_DIR . '/assets/js/imagescraper.js', array('jquery'));
	wp_enqueue_script('improveseo-videoscraper', IMPROVESEO_DIR . '/assets/js/videoscraper.js', array('jquery'));
	wp_enqueue_script('improveseo-exif', IMPROVESEO_DIR . '/assets/js/exif.js', array('jquery'));
	wp_enqueue_script('improveseo-wordai', IMPROVESEO_DIR . '/assets/js/wordai.js', array('jquery'));
	
	wp_enqueue_script('improveseo-tree', IMPROVESEO_DIR . '/assets/js/jstree.min.js', array('jquery'));
	wp_enqueue_script('improveseo-notify', IMPROVESEO_DIR . '/assets/js/notify.js', array('jquery'));
	wp_enqueue_script('improveseo-popup', IMPROVESEO_DIR . '/assets/js/popup.js', array('jquery'));
	
	wp_enqueue_script('improveseo-underscore', 'https://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.8.3/underscore-min.js', array('underscore'));
	
	wp_enqueue_script('improveseo-modal',IMPROVESEO_DIR . '/assets/js/jquery.modal.min.js', array('jquery'));	
}

add_action( 'wp_enqueue_scripts', 'improveseo_enqueue_front' );
function improveseo_enqueue_front(){
	wp_enqueue_style('improveseo-front', IMPROVESEO_DIR . '/assets/css/improveseo-front.css', array(), '2.0');
}