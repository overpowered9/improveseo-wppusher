<?php

add_action( 'admin_enqueue_scripts', 'improveseo_enqueue_admin' );

function improveseo_enqueue_admin(){

	$my_current_screen = get_current_screen();



	wp_enqueue_style('improveseo-main', IMPROVESEO_DIR . '/assets/css/main.css', array('wp-admin'));



	wp_enqueue_style('improveseo-tree', IMPROVESEO_DIR . '/assets/css/tree.min.css');
	wp_enqueue_style('improveseo-latest-css1', IMPROVESEO_DIR . '/assets/css/style.css');
	wp_enqueue_style('improveseo-latest-css2', IMPROVESEO_DIR . '/assets/css/step.css');
	wp_enqueue_style('improveseocss12121', IMPROVESEO_DIR . '/assets/css/made_by_me.css');

	wp_enqueue_style('improveseo-fa', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css');

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

	

	wp_enqueue_script('improveseo-tree', IMPROVESEO_DIR . '/assets/js/jstree.min.js', array('jquery'), IMPROVESEO_VERSION, true);

	wp_enqueue_script('improveseo-notify', IMPROVESEO_DIR . '/assets/js/notify.js', array('jquery'), IMPROVESEO_VERSION, true);

	wp_enqueue_script('improveseo-popup', IMPROVESEO_DIR . '/assets/js/popup.js', array('jquery'), IMPROVESEO_VERSION, true);

	

	wp_enqueue_script('improveseo-underscore', 'https://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.8.3/underscore-min.js', array('underscore'));

	

	wp_enqueue_script('improveseo-modal',IMPROVESEO_DIR . '/assets/js/jquery.modal.min.js', array('jquery'), IMPROVESEO_VERSION, true);	

	



	if ( isset( $my_current_screen->base )  ) {

		$allowed_bases = array('toplevel_page_improveseo_dashboard', 'improve-seo_page_improveseo_posting');

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

// Add global notification system to admin footer
add_action( 'admin_footer', 'improveseo_add_notification_system' );

function improveseo_add_notification_system() {
	?>
	<style>
		/* ImproveSEO Notification Popup Styles */
		.improveseo-notification-overlay {
			display: none;
			position: fixed;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background: rgba(0, 0, 0, 0.6);
			z-index: 99999;
			justify-content: center;
			align-items: center;
			animation: improveseoFadeIn 0.3s ease-in-out;
		}

		.improveseo-notification-overlay.active {
			display: flex;
		}

		.improveseo-notification-box {
			background: white;
			border-radius: 12px;
			box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
			max-width: 450px;
			width: 90%;
			overflow: hidden;
			animation: improveseoSlideUp 0.3s ease-out;
			position: relative;
		}

		.improveseo-notification-header {
			padding: 24px 24px 16px;
			display: flex;
			align-items: center;
			gap: 12px;
			border-bottom: 1px solid #f0f0f0;
		}

		.improveseo-notification-icon {
			width: 48px;
			height: 48px;
			border-radius: 50%;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 24px;
			flex-shrink: 0;
		}

		.improveseo-notification-icon.warning {
			background: linear-gradient(135deg, #FFA500 0%, #FF8C00 100%);
			color: white;
		}

		.improveseo-notification-icon.error {
			background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
			color: white;
		}

		.improveseo-notification-icon.success {
			background: linear-gradient(135deg, #28a745 0%, #218838 100%);
			color: white;
		}

		.improveseo-notification-icon.info {
			background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
			color: white;
		}

		.improveseo-notification-title {
			flex: 1;
		}

		.improveseo-notification-title h3 {
			margin: 0;
			font-size: 20px;
			font-weight: 600;
			color: #23282d;
			line-height: 1.3;
		}

		.improveseo-notification-body {
			padding: 20px 24px;
		}

		.improveseo-notification-body p {
			margin: 0;
			font-size: 15px;
			color: #50575e;
			line-height: 1.6;
		}

		.improveseo-notification-footer {
			padding: 16px 24px 24px;
			display: flex;
			justify-content: flex-end;
			gap: 12px;
		}

		.improveseo-notification-btn {
			padding: 10px 24px;
			border-radius: 6px;
			font-size: 14px;
			font-weight: 500;
			cursor: pointer;
			border: none;
			outline: none;
			transition: all 0.2s ease;
			font-family: inherit;
		}

		.improveseo-notification-btn-primary {
			background: linear-gradient(135deg, #0073aa 0%, #005a87 100%);
			color: white;
		}

		.improveseo-notification-btn-primary:hover {
			background: linear-gradient(135deg, #005a87 0%, #00405f 100%);
			transform: translateY(-1px);
			box-shadow: 0 4px 12px rgba(0, 115, 170, 0.3);
		}

		/* Loading Notification Styles */
		.improveseo-loading-overlay {
			display: none;
			position: fixed;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background: rgba(0, 0, 0, 0.6);
			z-index: 99999;
			justify-content: center;
			align-items: center;
			animation: improveseoFadeIn 0.3s ease-in-out;
		}

		.improveseo-loading-overlay.active {
			display: flex;
		}

		.improveseo-loading-box {
			background: white;
			border-radius: 12px;
			box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
			max-width: 400px;
			width: 90%;
			padding: 32px 24px;
			text-align: center;
			animation: improveseoSlideUp 0.3s ease-out;
		}

		.improveseo-loading-spinner {
			width: 60px;
			height: 60px;
			margin: 0 auto 20px;
			border: 4px solid #f3f3f3;
			border-top: 4px solid #0073aa;
			border-radius: 50%;
			animation: improveseoSpin 1s linear infinite;
		}

		.improveseo-loading-title {
			font-size: 20px;
			font-weight: 600;
			color: #23282d;
			margin: 0 0 12px 0;
		}

		.improveseo-loading-message {
			font-size: 15px;
			color: #50575e;
			margin: 0;
			line-height: 1.6;
		}

		@keyframes improveseoFadeIn {
			from { opacity: 0; }
			to { opacity: 1; }
		}

		@keyframes improveseoSlideUp {
			from {
				transform: translateY(50px);
				opacity: 0;
			}
			to {
				transform: translateY(0);
				opacity: 1;
			}
		}

		@keyframes improveseoSpin {
			0% { transform: rotate(0deg); }
			100% { transform: rotate(360deg); }
		}
	</style>

	<!-- ImproveSEO Notification Popup -->
	<div class="improveseo-notification-overlay" id="improveseoNotification">
		<div class="improveseo-notification-box">
			<div class="improveseo-notification-header">
				<div class="improveseo-notification-icon" id="improveseoNotificationIcon">
					⚠️
				</div>
				<div class="improveseo-notification-title">
					<h3 id="improveseoNotificationTitle">Notification</h3>
				</div>
			</div>
			<div class="improveseo-notification-body">
				<p id="improveseoNotificationMessage">This is a notification message.</p>
			</div>
			<div class="improveseo-notification-footer">
				<button class="improveseo-notification-btn improveseo-notification-btn-primary" id="improveseoNotificationOk">
					OK
				</button>
			</div>
		</div>
	</div>

	<!-- ImproveSEO Loading Popup -->
	<div class="improveseo-loading-overlay" id="improveseoLoading">
		<div class="improveseo-loading-box">
			<div class="improveseo-loading-spinner"></div>
			<h3 class="improveseo-loading-title" id="improveseoLoadingTitle">Loading...</h3>
			<p class="improveseo-loading-message" id="improveseoLoadingMessage">Please wait...</p>
		</div>
	</div>

	<script>
	// ImproveSEO Notification System
	if (typeof window.ImproveSEONotification === 'undefined') {
		window.ImproveSEONotification = {
			show: function(options) {
				const {
					title = 'Notification',
					message = '',
					type = 'warning',
					buttonText = 'OK',
					onClose = null
				} = options;

				const overlay = document.getElementById('improveseoNotification');
				const iconEl = document.getElementById('improveseoNotificationIcon');
				const titleEl = document.getElementById('improveseoNotificationTitle');
				const messageEl = document.getElementById('improveseoNotificationMessage');
				const okBtn = document.getElementById('improveseoNotificationOk');

				if (!overlay) return;

				const icons = {
					warning: '⚠️',
					error: '❌',
					success: '✓',
					info: 'ℹ️'
				};

				iconEl.classList.remove('warning', 'error', 'success', 'info');
				iconEl.classList.add(type);
				iconEl.textContent = icons[type] || icons.warning;

				titleEl.textContent = title;
				messageEl.textContent = message;
				okBtn.textContent = buttonText;

				overlay.classList.add('active');

				const closeHandler = function() {
					overlay.classList.remove('active');
					okBtn.removeEventListener('click', closeHandler);
					overlay.removeEventListener('click', overlayClickHandler);
					if (onClose && typeof onClose === 'function') {
						onClose();
					}
				};

				const overlayClickHandler = function(e) {
					if (e.target === overlay) {
						closeHandler();
					}
				};

				okBtn.addEventListener('click', closeHandler);
				overlay.addEventListener('click', overlayClickHandler);

				return { close: closeHandler };
			},

			warning: function(message, title = 'Warning') {
				return this.show({ title, message, type: 'warning' });
			},

			error: function(message, title = 'Error') {
				return this.show({ title, message, type: 'error' });
			},

			success: function(message, title = 'Success') {
				return this.show({ title, message, type: 'success' });
			},

			info: function(message, title = 'Information') {
				return this.show({ title, message, type: 'info' });
			}
		};
	}

	// ImproveSEO Loading System
	if (typeof window.ImproveSEOLoading === 'undefined') {
		window.ImproveSEOLoading = {
			show: function(options) {
				const {
					title = 'Loading...',
					message = 'Please wait...'
				} = options || {};

				const overlay = document.getElementById('improveseoLoading');
				if (!overlay) return;

				const titleEl = document.getElementById('improveseoLoadingTitle');
				const messageEl = document.getElementById('improveseoLoadingMessage');

				titleEl.textContent = title;
				messageEl.textContent = message;
				overlay.classList.add('active');
			},

			hide: function() {
				const overlay = document.getElementById('improveseoLoading');
				if (overlay) {
					overlay.classList.remove('active');
				}
			}
		};
	}
	</script>
	<?php
}