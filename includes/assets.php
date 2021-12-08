<?php

$assets_dir = IMPROVESEO_DIR . '/assets';

wp_enqueue_style('improveseo-main', $assets_dir .'/css/main.css', array('wp-admin'));

if (is_admin()) {
	wp_enqueue_style('improveseo-tree', $assets_dir .'/css/tree.min.css');
	wp_enqueue_style('improveseo-fa', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css');
	wp_enqueue_style('improveseo-modalStyle',$assets_dir .'/js/jquery.modal.min.css');

	wp_enqueue_script('improveseo-main', $assets_dir .'/js/main.js', array('jquery'));
	wp_enqueue_script('improveseo-dialog', $assets_dir .'/js/dialog.js', array('jquery'));

	wp_enqueue_script('improveseo-posting', $assets_dir .'/js/posting.js', array('jquery'));
	wp_enqueue_script('improveseo-imagescraper', $assets_dir .'/js/imagescraper.js', array('jquery'));
	wp_enqueue_script('improveseo-videoscraper', $assets_dir .'/js/videoscraper.js', array('jquery'));
	wp_enqueue_script('improveseo-exif', $assets_dir .'/js/exif.js', array('jquery'));
	wp_enqueue_script('improveseo-wordai', $assets_dir .'/js/wordai.js', array('jquery'));
	
	wp_enqueue_script('improveseo-tree', $assets_dir .'/js/jstree.min.js', array('jquery'));
	wp_enqueue_script('improveseo-notify', $assets_dir .'/js/notify.js', array('jquery'));
	wp_enqueue_script('improveseo-popup', $assets_dir .'/js/popup.js', array('jquery'));
	
	wp_enqueue_script('improveseo-underscore', 'https://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.8.3/underscore-min.js', array('underscore'));
	
	//wp_enqueue_script('improveseo-jquery',$assets_dir .'/js/jquery.min.js', array('jquery'));
	wp_enqueue_script('improveseo-modal',$assets_dir .'/js/jquery.modal.min.js', array('jquery'));
	
}