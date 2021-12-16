<?php
/**
 * The Tiny MCE button.
 *
 */
// Make sure we don't expose any info if called directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'ImproveSEO_Shortcode_List_Btn' ) ) {

	/**
	 * The Tiny MCE button class.
	 */
	class ImproveSEO_Shortcode_List_Btn {

		/**
		 * Instance of the class.
		 *
		 * @var $instance
		 */
		private static $instance;

		/**
		 * Initiator
		 *
		 * @since 2.0.0
		 */
		public static function init() {
			return self::$instance;
		}
 
		/**
		 * Constructor
		 *
		 * @since 2.0.0
		 */
		public function __construct() {
			add_action( 'admin_footer', array( $this, 'improveseo_shortcodes_list' ) );
			add_action( 'admin_head', array( $this, 'improveseo_shortcodes_button' ) );
		}

		/**
		 * Hooks your functions into the correct filters
		 *
		 * @since 2.0.0
		 * @return void
		 */
		public function improveseo_shortcodes_button() {
			// check user permissions.
			if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
				return;
			}
			// check if WYSIWYG is enabled.
			if ( 'true' == get_user_option( 'rich_editing' ) ) {
				add_filter( 'mce_external_plugins', array( $this, 'improveseo_add_mce_plugin' ) );
				add_filter( 'mce_buttons', array( $this, 'improveseo_register_mce_button' ) );
			}
		}

		/**
		 * Script for our mce button.
		 *
		 * @since 2.0.0
		 * @param string $plugin_array The button.
		 * @return string
		 */
		public function improveseo_add_mce_plugin( $plugin_array ) {
			$plugin_array['improveseo_mce_button'] = IMPROVESEO_DIR . '/assets/js/shortcode-popup-button.js';
			return $plugin_array;
		}

		/**
		 * Register our button in the editor.
		 *
		 * @since 2.0.0
		 * @param array $buttons The Tiny mce button.
		 * @return array
		 */
		public function improveseo_register_mce_button( $buttons ) {
			array_push( $buttons, 'improveseo_mce_button' );
			return $buttons;
		}

	}
}

/**
 *  Kicking this off
 */
$improveseo_shortcode_list_btn = new ImproveSEO_Shortcode_List_Btn();
$improveseo_shortcode_list_btn->init();
