<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly  

add_action('admin_init', 'improveseo_init_settings');

function improveseo_init_settings() {
	register_setting('improveseo_settings', 'improveseo_pixabay_key');
	register_setting('improveseo_settings', 'improveseo_google_api_key');
	register_setting('improveseo_settings', 'improveseo_word_ai_pass');
	register_setting('improveseo_settings', 'improveseo_word_ai_email');
}
