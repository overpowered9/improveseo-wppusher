<?php





add_action('admin_init', 'improveseo_init_settings');





function improveseo_init_settings() {

	// Legacy settings (kept for compatibility)
	register_setting('improveseo_settings', 'improveseo_pixabay_key');
	register_setting('improveseo_settings', 'improveseo_google_api_key');
	register_setting('improveseo_settings', 'improveseo_chatgpt_api_key');
	register_setting('improveseo_settings', 'improveseo_word_ai_pass');
	register_setting('improveseo_settings', 'improveseo_word_ai_email');
	
	// New ImproveSEO server settings
	// register_setting('improveseo_settings', 'improveseo_server_url'); // Fixed URL, not user configurable
	register_setting('improveseo_settings', 'improveseo_api_key');
	register_setting('improveseo_settings', 'improveseo_site_code');

	// Business details (collected during onboarding, used for schema markup & AI content)
	register_setting('improveseo_settings', 'improveseo_business_type',    array( 'sanitize_callback' => 'sanitize_text_field' ));
	register_setting('improveseo_settings', 'improveseo_business_city',    array( 'sanitize_callback' => 'sanitize_text_field' ));
	register_setting('improveseo_settings', 'improveseo_business_service', array( 'sanitize_callback' => 'sanitize_text_field' ));

	// Featured image toggles
	register_setting('improveseo_settings', 'improveseo_featured_images_enabled', array( 'sanitize_callback' => 'absint' ));
	register_setting('improveseo_settings', 'improveseo_featured_images_bulk',    array( 'sanitize_callback' => 'absint' ));
	register_setting('improveseo_settings', 'improveseo_featured_images_single',  array( 'sanitize_callback' => 'absint' ));

}


