<?php
/**
 * Plugin Name:     Restrict Content Pro - ConvertKit
 * Plugin URI:      http://section214.com
 * Description:     Include a ConvertKit signup option with your RCP registration form
 * Version:         1.0.0
 * Author:          Daniel J Griffiths
 * Author URI:      http://section214.com
 * Text Domain:     rcp-convertkit
 *
 * @package         RCP\ConvertKit
 * @author          Daniel J Griffiths <dgriffiths@section214.com>
 * @copyright       Copyright (c) 2015, Daniel J Griffiths
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}


if( ! class_exists( 'RCP_ConvertKit' ) ) {


	/**
	 * Main RCP_ConvertKit class
	 *
	 * @since       1.0.0
	 */
	class RCP_ConvertKit {


		/**
		 * @var         RCP_ConvertKit $instance The one true RCP_ConvertKit
		 * @since       1.0.0
		 */
		private static $instance;


		/**
		 * @var         object $api_helper The MailChimp API helper object
		 * @since       1.0.0
		 */
		public $api_helper;


		/**
		 * Get active instance
		 *
		 * @access      public
		 * @since       1.0.0
		 * @return      object self::$instance The one true RCP_ConvertKit
		 */
		public static function instance() {
			if( ! self::$instance ) {
				self::$instance = new RCP_ConvertKit();
				self::$instance->setup_constants();
				self::$instance->includes();
				self::$instance->load_textdomain();
				self::$instance->hooks();
				self::$instance->api_helper = new RCP_ConvertKit_API();
			}

			return self::$instance;
		}


		/**
		 * Setup plugin constants
		 *
		 * @access      private
		 * @since       1.0.0
		 * @return      void
		 */
		public function setup_constants() {
			// Plugin version
			define( 'RCP_CONVERTKIT_VER', '1.0.0' );

			// Plugin path
			define( 'RCP_CONVERTKIT_DIR', plugin_dir_path( __FILE__ ) );

			// Plugin URL
			define( 'RCP_CONVERTKIT_URL', plugin_dir_url( __FILE__ ) );
		}


		/**
		 * Include necessary files
		 *
		 * @access      private
		 * @since       1.0.0
		 * @return      void
		 */
		private function includes() {
			require_once RCP_CONVERTKIT_DIR . 'includes/functions.php';
			require_once RCP_CONVERTKIT_DIR . 'includes/libraries/class.convertkit-api.php';
			require_once RCP_CONVERTKIT_DIR . 'includes/template.php';

			if( is_admin() ) {
				require_once RCP_CONVERTKIT_DIR . 'includes/admin/settings/register.php';
				require_once RCP_CONVERTKIT_DIR . 'includes/admin/subscription/meta-boxes.php';
				require_once RCP_CONVERTKIT_DIR . 'includes/admin/user/profile.php';
			}

			if( ! class_exists( 'S214_Plugin_Updater' ) ) {
				require_once RCP_CONVERTKIT_DIR . 'includes/libraries/S214_Plugin_Updater.php';
			}
		}


		/**
		 * Run action and filter hooks
		 *
		 * @access      private
		 * @since       1.0.0
		 * @return      void
		 */
		private function hooks() {
			// Licensing
			$settings   = get_option( 'rcp_convertkit_settings' );
			$license    = ! empty( $settings['license_key'] ) ? trim( $settings['license_key'] ) : false;

			if( $license ) {
				$update = new S214_Plugin_Updater( 'https://section214.com', __FILE__, array(
					'version' => RCP_CONVERTKIT_VER,
					'license' => $license,
					'item_id' => 4969,
					'author'  => 'Daniel J Griffiths'
				) );
			}

			add_action( 'admin_notices', array( $this, 'admin_notice' ) );
		}


		/**
		 * Internationalization
		 *
		 * @access      public
		 * @since       1.0.0
		 * @return      void
		 */
		public static function load_textdomain() {
			// Set filter for language directory
			$lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
			$lang_dir = apply_filters( 'rcp_convertkit_languages_directory', $lang_dir );

			// Traditional WordPress plugin locale filter
			$locale = apply_filters( 'plugin_locale', get_locale(), 'rcp-convertkit' );
			$mofile = sprintf( '%1$s-%2$s.mo', 'rcp-convertkit', $locale );

			// Setup paths to current locale file
			$mofile_local  = $lang_dir . $mofile;
			$mofile_global = WP_LANG_DIR . '/rcp-convertkit/' . $mofile;

			if( file_exists( $mofile_global ) ) {
				// Look in global /wp-content/languages/rcp-convertkit/ folder
				load_textdomain( 'rcp-convertkit', $mofile_global );
			} elseif( file_exists( $mofile_local ) ) {
				// Look in local /wp-content/plugins/rcp-convertkit/languages/ folder
				load_textdomain( 'rcp-convertkit', $mofile_local );
			} else {
				// Load the default language files
				load_plugin_textdomain( 'rcp-convertkit', false, $lang_dir );
			}
		}


		/**
		 * Display admin notices
		 *
		 * @since       1.0.0
		 * @return      void
		 */
		public function admin_notice() {
			if( rcp_convertkit_check_license() === 'expired' ) {
				echo '<div class="error info"><p>' . __( 'Your license key for RCP ConvertKit has expired. Please renew your license to re-enable automatic updates.', 'rcp-convertkit' ) . '</p></div>';
			} elseif( rcp_convertkit_check_license() !== 'valid' ) {
				echo '<div class="notice notice-info"><p>' . sprintf( __( 'Please <a href="%s">enter and activate</a> your license key for RCP ConvertKit to enable automatic updates.', 'rcp-convertkit' ), admin_url( 'admin.php?page=rcp-convertkit' ) ) . '</p></div>';
			}
		}
	}
}


/**
 * The main function responsible for returning the one true RCP_ConvertKit
 * instance to functions everywhere
 *
 * @since       1.0.0
 * @return      RCP_ConvertKit The one true RCP_ConvertKit
 */
function rcp_convertkit() {
	return RCP_ConvertKit::instance();
}
add_action( 'plugins_loaded', 'rcp_convertkit' );
