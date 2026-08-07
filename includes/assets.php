<?php

/**
 * Returns a cache-busting version string based on the file's last-modified time.
 * This ensures that browsers always fetch the latest version after a cPanel upload.
 * Falls back to IMPROVESEO_VERSION if the file cannot be stat'd.
 *
 * @param string $relative_path Path relative to the plugin root (e.g. 'assets/js/main.js').
 * @return string
 */
function improveseo_asset_ver( $relative_path ) {
	$abs = WT_PATH . '/' . ltrim( $relative_path, '/' );
	return file_exists( $abs ) ? (string) filemtime( $abs ) : IMPROVESEO_VERSION;
}

add_action( 'admin_enqueue_scripts', 'improveseo_enqueue_admin' );

function improveseo_enqueue_admin(){

	$my_current_screen = get_current_screen();



	wp_enqueue_style('improveseo-main', IMPROVESEO_DIR . '/assets/css/main.css', array('wp-admin'));



	wp_enqueue_style('improveseo-tree', IMPROVESEO_DIR . '/assets/css/tree.min.css');
	wp_enqueue_style('improveseo-latest-css1', IMPROVESEO_DIR . '/assets/css/style.css');
	wp_enqueue_style('improveseo-latest-css2', IMPROVESEO_DIR . '/assets/css/step.css');
	wp_enqueue_style('improveseocss12121', IMPROVESEO_DIR . '/assets/css/made_by_me.css', array(), filemtime(IMPROVESEO_ROOT . '/assets/css/made_by_me.css'));
	wp_enqueue_style('improveseo-settings-redesign', IMPROVESEO_DIR . '/assets/css/settings-redesign.css', array(), improveseo_asset_ver('assets/css/settings-redesign.css'));

	wp_enqueue_style('improveseo-fa', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css');

	wp_enqueue_style('improveseo-modalStyle',IMPROVESEO_DIR . '/assets/js/jquery.modal.min.css');



	//wp_enqueue_script('improveseo-bootstrap', IMPROVESEO_DIR . '/assets/js/bootstrap.min.js', array('jquery'), IMPROVESEO_VERSION, true);

	wp_enqueue_script('improveseo-main', IMPROVESEO_DIR . '/assets/js/main.js', array('jquery'), improveseo_asset_ver('assets/js/main.js'), true);

	wp_localize_script('improveseo-main', 'main_ajax_vars', array(

		'site_url'      		=> 	site_url(),

		)

	);



	wp_enqueue_script('improveseo-dialog', IMPROVESEO_DIR . '/assets/js/dialog.js', array('jquery'), improveseo_asset_ver('assets/js/dialog.js'), true);



	wp_enqueue_script('improveseo-posting', IMPROVESEO_DIR . '/assets/js/posting.js', array('jquery'), improveseo_asset_ver('assets/js/posting.js'), true);

	wp_localize_script('improveseo-posting', 'posting_ajax_vars', array(

		'site_url'      		=> 	site_url(),

		)

	);



	wp_enqueue_script('improveseo-imagescraper', IMPROVESEO_DIR . '/assets/js/imagescraper.js', array('jquery'), improveseo_asset_ver('assets/js/imagescraper.js'), true);

	wp_enqueue_script('improveseo-videoscraper', IMPROVESEO_DIR . '/assets/js/videoscraper.js', array('jquery'), improveseo_asset_ver('assets/js/videoscraper.js'), true);

	wp_enqueue_script('improveseo-exif', IMPROVESEO_DIR . '/assets/js/exif.js', array('jquery'), improveseo_asset_ver('assets/js/exif.js'), true);

	wp_enqueue_script('improveseo-wordai', IMPROVESEO_DIR . '/assets/js/wordai.js', array('jquery'), improveseo_asset_ver('assets/js/wordai.js'), true);

	

	wp_enqueue_script('improveseo-tree', IMPROVESEO_DIR . '/assets/js/jstree.min.js', array('jquery'), improveseo_asset_ver('assets/js/jstree.min.js'), true);

	wp_enqueue_script('improveseo-notify', IMPROVESEO_DIR . '/assets/js/notify.js', array('jquery'), improveseo_asset_ver('assets/js/notify.js'), true);

	wp_enqueue_script('improveseo-popup', IMPROVESEO_DIR . '/assets/js/popup.js', array('jquery'), improveseo_asset_ver('assets/js/popup.js'), true);

	

	wp_enqueue_script('improveseo-underscore', 'https://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.8.3/underscore-min.js', array('underscore'));

	

	wp_enqueue_script('improveseo-modal',IMPROVESEO_DIR . '/assets/js/jquery.modal.min.js', array('jquery'), improveseo_asset_ver('assets/js/jquery.modal.min.js'), true);	

	



	if ( isset( $my_current_screen->base )  ) {

		// The bulk draft-edit screen runs the SAME preview flow as the single editor
		// (form.js → improveseo_generate_preview), so it needs the same scripts.
		$allowed_bases = array(
			'toplevel_page_improveseo_dashboard',
			'improve-seo_page_improveseo_posting',
			'improve-seo_page_improveseo_bulkprojects',
		);

		if(in_array($my_current_screen->base, $allowed_bases) && isset($_REQUEST['action'])){

			wp_enqueue_script( 'jquery-ui-autocomplete' );

			$saved_rnos =  get_option('get_saved_random_numbers');

			$autocomplete_arr = array();

			if(!empty($saved_rnos)){

				foreach($saved_rnos as $id){

					$testimonial = get_option('get_testimonials_'.$id);

					$buttons = get_option('get_buttons_'.$id);

					$google_map = get_option('get_googlemaps_'.$id);

					$videos = get_option('get_videos_'.$id);



					if(!empty($testimonial)){

						$autocomplete = array(

							'value' => '@testimonial : '.$id,

							'label' => '@testimonial : '.$id,

							'desc' => '[improveseo_testimonial id='.$id.']'

						);

						$autocomplete_arr[] = $autocomplete;

					}



					if(!empty($buttons)){

						$autocomplete = array(

							'value' => '@button : '.$id,

							'label' => '@button : '.$id,

							'desc' => '[improveseo_buttons id='.$id.']'

						);

						$autocomplete_arr[] = $autocomplete;

					}



					



					if(!empty($google_map)){

						$autocomplete = array(

							'value' => '@googlemap : '.$id,

							'label' => '@googlemap : '.$id,

							'desc' => '[improveseo_googlemaps id='.$id.']'

						);

						$autocomplete_arr[] = $autocomplete;

					}



					if(!empty($videos)){

						$autocomplete = array(

							'value' => '@video : '.$id,

							'label' => '@video : '.$id,

							'desc' => '[improveseo_video id='.$id.']'

						);

						$autocomplete_arr[] = $autocomplete;

					}

				}

			}

			$seo_list = improve_seo_lits();

			if(!empty($seo_list)){

				foreach($seo_list as $li){

					$autocomplete = array(

						'value' => '@list : '.$li,

						'label' => '@list : '.$li,

						'desc' => '@list:list-'.$li

					);

					$autocomplete_arr[] = $autocomplete;

				}

			}

			

			wp_enqueue_script('improveseo-caret-form', IMPROVESEO_DIR.'/assets/js/jquery.caret.js', array('jquery'), improveseo_asset_ver('assets/js/jquery.caret.js'), true);

			wp_enqueue_script('improveseo-form', IMPROVESEO_DIR.'/assets/js/form.js', array('jquery'), improveseo_asset_ver('assets/js/form.js'), true);

			wp_localize_script('improveseo-form', 'form_ajax_vars', array(

				'ajax_url'      		=> 	admin_url( 'admin-ajax.php' ),

				'admin_url'      		=> 	admin_url( 'admin.php' ),

				//'autocomplete_src'		=> $autocomplete_arr

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

	// Version comes from the file's mtime rather than a hand-bumped literal, so a CSS change
	// always reaches visitors' browsers instead of being served from cache (the '2.0' literal
	// this replaces had to be remembered by hand, and wasn't).
	wp_enqueue_style('improveseo-front', IMPROVESEO_DIR . '/assets/css/improveseo-front.css', array(), improveseo_asset_ver('assets/css/improveseo-front.css'));

}

// ─── Onboarding wizard assets (only on the onboarding admin page) ─────────────

add_action( 'admin_enqueue_scripts', 'improveseo_enqueue_onboarding_assets' );

function improveseo_enqueue_onboarding_assets() {
	if ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'improveseo_onboarding' ) {
		return;
	}

	wp_enqueue_style(
		'improveseo-onboarding',
		IMPROVESEO_DIR . '/assets/css/onboarding.css',
		array(),
		improveseo_asset_ver('assets/css/onboarding.css')
	);

	wp_enqueue_script(
		'improveseo-onboarding',
		IMPROVESEO_DIR . '/assets/js/onboarding.js',
		array( 'jquery' ),
		improveseo_asset_ver('assets/js/onboarding.js'),
		true
	);

	$site_url    = home_url( '/' );
	$parsed_host = parse_url( $site_url, PHP_URL_HOST );
	$site_domain = $parsed_host ? preg_replace( '/^www\./i', '', strtolower( $parsed_host ) ) : '';

	$stored_api_key   = get_option( 'improveseo_api_key', '' );
	$stored_site_code = get_option( 'improveseo_site_code', '' );

	wp_localize_script(
		'improveseo-onboarding',
		'improveseoOnboarding',
		array(
			'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
			'nonce'          => wp_create_nonce( 'improveseo_onboarding_nonce' ),
			'siteDomain'     => $site_domain,
			'siteUrl'        => $site_url,
			'siteName'       => get_bloginfo( 'name' ),
			'cmsConnectUrl'  => 'https://account.improveseoplugin.com/connect',
			'dashboardUrl'   => admin_url( 'admin.php?page=improveseo_dashboard' ),
			'firstContentUrl'=> admin_url( 'admin.php?page=improveseo_posting&from=onboarding' ),
			// Edge-case flags so JS can branch without extra AJAX
			'isConnected'    => ! empty( $stored_api_key ) && ! empty( $stored_site_code ),
			'onboardingDone' => get_option( 'improveseo_onboarding_complete', '0' ) === '1',
			'partialConnect' => ( ! empty( $stored_api_key ) ) !== ( ! empty( $stored_site_code ) ),
		)
	);
}

// ─── Onboarding guide assets (single post creation page, from=onboarding) ─────

add_action( 'admin_enqueue_scripts', 'improveseo_enqueue_guide_assets' );

function improveseo_enqueue_guide_assets() {
	if ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'improveseo_posting' ) {
		return;
	}
	if ( ! isset( $_GET['from'] ) || $_GET['from'] !== 'onboarding' ) {
		return;
	}

	wp_enqueue_style(
		'iseo-onboarding-guide',
		IMPROVESEO_DIR . '/assets/css/onboarding-guide.css',
		array(),
		improveseo_asset_ver( 'assets/css/onboarding-guide.css' )
	);

	wp_enqueue_script(
		'iseo-onboarding-guide',
		IMPROVESEO_DIR . '/assets/js/onboarding-guide.js',
		array( 'jquery' ),
		improveseo_asset_ver( 'assets/js/onboarding-guide.js' ),
		true
	);
}
