<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
add_action( 'admin_enqueue_scripts', 'improveseo_enqueue_admin' );



function improveseo_enqueue_admin(){
	$my_current_screen = get_current_screen();

	wp_enqueue_style('improveseo-main', IMPROVESEO_DIR . '/assets/css/main.css', array('wp-admin'));

	wp_enqueue_style('improveseo-tree', IMPROVESEO_DIR . '/assets/css/tree.min.css');
	wp_enqueue_style('improveseo-fa', IMPROVESEO_DIR.'/assets/css/font-awesome.min.css');
	wp_enqueue_style('improveseo-modalStyle',IMPROVESEO_DIR . '/assets/js/jquery.modal.min.css');

	//wp_enqueue_script('improveseo-bootstrap', IMPROVESEO_DIR . '/assets/js/bootstrap.min.js', array('jquery'), IMPROVESEO_VERSION, true);
	wp_enqueue_script('improveseo-main', IMPROVESEO_DIR . '/assets/js/main.js', array('jquery'), IMPROVESEO_VERSION, true);
	wp_localize_script('improveseo-main', 'main_ajax_vars', array(
		'site_url'      		=> 	site_url(),
		)
	);

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
	
	wp_enqueue_script('improveseo-notify', IMPROVESEO_DIR . '/assets/js/notify.js', array('jquery'), IMPROVESEO_VERSION, true);
	wp_enqueue_script('improveseo-popup', IMPROVESEO_DIR . '/assets/js/popup.js', array('jquery'), IMPROVESEO_VERSION, true);
	
	wp_enqueue_script('improveseo-underscore', "/wp-includes/js/underscore.js" , array('underscore'));
	


	if ( isset( $my_current_screen->base )  ) {
		$allowed_bases = array('toplevel_page_improveseo_dashboard', 'improve-seo_page_improveseo_posting');
		if(in_array($my_current_screen->base, $allowed_bases) && isset($_REQUEST['action'])){
			wp_enqueue_script( 'jquery-ui-autocomplete' );
			$saved_rnos =  get_option('improveseo_get_saved_random_numbers');
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
			$seo_list = improveseo_lits();
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
			
			wp_enqueue_script('improveseo-caret-form', IMPROVESEO_DIR.'/assets/js/jquery.caret.js', array('jquery'), IMPROVESEO_VERSION, true);
			wp_enqueue_script('improveseo-form', IMPROVESEO_DIR.'/assets/js/form.js', array('jquery'), IMPROVESEO_VERSION, true);
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
	wp_enqueue_style('improveseo-front', IMPROVESEO_DIR . '/assets/css/improveseo-front.css', array(), '2.0');
}