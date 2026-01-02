<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

class PWAFORWP_Gravitec {
	/**
	 * get the unique instance of the class
	 * @var PWAFORWP_Gravitec
	 */
	private static $instance;

	private $settings = array();
	
	/**
	 * Constructor
	 */
	public function __construct() {

		if(!$this->settings){
			$this->settings = pwaforwp_defaultSettings();
		}

		add_action('plugins_loaded', array($this, 'gravitec_init'));

	}
	
	/**
	 * Check and entry function for compatibility with Gravitec plugin
	 * @return null
	 */
	public function gravitec_init() {

		if( isset( $this->settings['gravitec_support_setting'] ) && $this->settings['gravitec_support_setting'] == 1 ) {
			add_filter( 'pwaforwp_sw_js_template', array( $this, 'add_gravitec' ) );
			
			// Check if Gravitec plugin is active
			if ( class_exists( 'Gravitecnet' ) || defined( 'GRAVITECNET_VERSION' ) ) {
				$this->gravitec_compatibility();
			}
		}

	}

	/**
	 * Handle Gravitec plugin compatibility
	 * @return null
	 */
	public function gravitec_compatibility($action = null) {

		// Register deactivation hook to clean up if needed
		register_deactivation_hook( PWAFORWP_PLUGIN_FILE, function () {
			$this->cleanup_on_deactivation();
		} );
	}

	/**
	 * Cleanup on deactivation
	 * @return null
	 */
	public function cleanup_on_deactivation() {
		// Add any cleanup logic here if needed in the future
	}

	/**
	 * add content in service worker for Gravitec call
	 * @param string $content javascript content
	 * @return string $content javascript content
	 */
	public function add_gravitec( $content ) {

		// Get Gravitec service worker URL
		$gravitec_sw_url = $this->get_gravitec_sw_url();
		
		if ( $gravitec_sw_url ) {
			$content = "importScripts('".esc_url($gravitec_sw_url)."');\n".$content;
		}

		return $content;
	}

	/**
	 * Get Gravitec service worker URL from plugin
	 * @return string|false
	 */
	private function get_gravitec_sw_url() {
		$app_key = '';
		
		// Try to get app key from Gravitecnet_Settings class if available
		if ( class_exists( 'Gravitecnet_Settings' ) ) {
			$gravitec_settings = new Gravitecnet_Settings();
			$app_key = $gravitec_settings->get_app_key();
		}
		
		// Fallback to direct option access if class not available
		if ( empty( $app_key ) ) {
			$app_key = get_option( 'gravitecnet_option_app_key' );
		}
		
		// If we have app_key, construct the service worker URL
		if ( ! empty( $app_key ) ) {
			// Get plugin directory URL for Gravitec plugin
			// Use plugins_url() for better reliability
			$gravitec_plugin_url = plugins_url( 'sdk_files/sw.php', 'gravitec-net-web-push-notifications/gravitecnet.php' );
			
			// Fallback to content_url if plugins_url didn't work
			if ( empty( $gravitec_plugin_url ) || strpos( $gravitec_plugin_url, 'gravitec' ) === false ) {
				$gravitec_plugin_url = content_url( 'plugins/gravitec-net-web-push-notifications/sdk_files/sw.php' );
			}
			
			// Build URL with required parameters
			// Based on sw.php file, it needs appKey parameter
			// version and track_inactive are optional but commonly used
			$gravitec_sw_url = add_query_arg( array(
				'version' => '6',
				'appKey' => $app_key,
				'track_inactive' => 'false'
			), $gravitec_plugin_url );
			
			return $gravitec_sw_url;
		}

		return false;
	}

	/**
     * Gets an instance of our PWAFORWP_Gravitec class.
     *
     * @return PWAFORWP_Gravitec
     */
	public static function get_instance() {

		if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;

	}

}

function pwaforwp_gravitec(){

	return PWAFORWP_Gravitec::get_instance();

}
pwaforwp_gravitec();

