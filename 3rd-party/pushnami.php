<?php
// Exit if accessed directly
if ( ! defined('ABSPATH') ) exit;

class PWAforwp_pushnami{
	public function __construct(){
		$settings = pwaforwp_defaultSettings();
		if(isset($settings['pushnami_support_setting']) && $settings['pushnami_support_setting']==1){
			add_filter( 'pwaforwp_manifest', array($this, 'pushnami_insert_gcm_sender_id') );
			add_filter( 'pwaforwp_sw_name_modify', array($this, 'pwaforwp_pushnami_change_sw_name' ));
			add_action("wp", array($this, 'pushnami_for_multisite'));
		}
	}

	public function pushnami_compatiblity($action = null) {
		if ( class_exists( 'WPPushnami' ) ) {
			$this->use_custom_manifest($action);
			if ( ! is_multisite() ) {              
				$this->add_sw_to_pushnami_sw($action);
			}
			register_deactivation_hook( PWAFORWP_PLUGIN_FILE, function () {
				$pn_settings					  = \WPPushnami::get_script_options();
				$pn_settings->use_custom_manifest = false;
				\WPPushnami::save_pushnami_options( $pn_settings );
			} );
		}
	}

	function use_custom_manifest($action = null) {
		$url = pwaforwp_home_url();
		$pushnami_option = \WPPushnami::get_script_options();
		if ( $pushnami_option->use_custom_manifest == false ) {
			$pushnami_option->use_custom_manifest = true;
			if ( $action ) {
				$pushnami_option->use_custom_manifest = false;
			}
			if ( function_exists('ampforwp_is_amp_endpoint') && ampforwp_is_amp_endpoint() ) {
				$pushnami_option->custom_manifest_url = esc_url( pwaforwp_manifest_json_url(true) );
			} else {
				$pushnami_option->custom_manifest_url = esc_url( pwaforwp_manifest_json_url() );
			}
			\WPPushnami::save_pushnami_options( $pushnami_option );
		}

		//update own settings
		$get_pwaforwp_options = pwaforwp_defaultSettings();
		$get_pwaforwp_options['pushnami_support_setting'] = 1;
		update_option('pwaforwp_settings', $get_pwaforwp_options);

	}
	function pushnami_insert_gcm_sender_id( $manifest ) {
		if ( class_exists( 'WPPushnami' ) ) {
			if(is_array($manifest)){
				$manifest['gcm_sender_id'] = '733213273952';
			}
		}
		return $manifest;
	}

	function pwaforwp_pushnami_change_sw_name( $name ) {
		if ( ! is_multisite() ) {
			if ( class_exists( 'WPPushnami' ) ) {
				$name = 'service-worker.js';
			}
		}
		return $name;
	}

	function add_sw_to_pushnami_sw( $action = null ) {
		$abs_path     = str_replace("//","/",str_replace("\\","/",realpath(ABSPATH))."/");
		$pn_worker = $abs_path.'service-worker.js';
		$url = pwaforwp_site_url();
		$home_url = pwaforwp_home_url();

		$pn_options = \WPPushnami::get_script_options();
		$pn_api_key = $pn_options->api_key;

		if ( !is_multisite() && trim($url)!==trim($home_url) ) {
			$url = esc_url_raw($home_url.'?'.pwaforwp_query_var('sw_query_var').'=1&'.pwaforwp_query_var('sw_file_var').'='.'pwa-sw.js');
		} else {
			$url = esc_url(pwaforwp_home_url().'pwa-sw.js');
		}

		$content  = "";
		if (!$action) {
			$content .= "importScripts('".$url."')".PHP_EOL;
			$content .= "importScripts('https://api.pushnami.com/scripts/v2/pushnami-sw/".$pn_api_key."')".PHP_EOL;
		}
		$status = pwaforwp_write_a_file($pn_worker, $content);
		return $status;
	}

	function pushnami_for_multisite() {
		if (  class_exists('WPPushnami') ) {//is_multisite() &&
			remove_action( 'wp_head', [ 'Pushnami_Public', 'pushnami_header' ] );
			add_action( 'wp_head',  'pwaforwp_pushnami_init_pushnami_head' );
		}
	}
	function pwaforwp_pushnami_init_pushnami_head() {
		$url = pwaforwp_site_url();
		$home_url = pwaforwp_home_url();

		if( is_multisite() || trim($url)!==trim($home_url) || !pwaforwp_is_file_inroot()) {
			$ServiceWorkerfileName = $home_url.'?'.pwaforwp_query_var('sw_query_var').'=1&'.pwaforwp_query_var('sw_file_var').'='.apply_filters('pwaforwp_sw_name_modify', 'pwa-sw'.pwaforwp_multisite_postfix().'.js');
			$ServiceWorkerfileName = service_workerUrls($ServiceWorkerfileName, apply_filters('pwaforwp_sw_name_modify', 'pwa-sw'.pwaforwp_multisite_postfix().'.js'));
		} else {
			$ServiceWorkerfileName = $url.apply_filters('pwaforwp_sw_name_modify', 'pwa-sw'.pwaforwp_multisite_postfix().'.js');
		}

		$options = \WPPushnami::get_script_options();
		$options->swPath = $ServiceWorkerfileName;
		$script = \WPPushnami::render_inline_script($options);

		echo PHP_EOL
			.'<meta name="pushnami" content="wordpress-plugin"/>'.PHP_EOL
			.'<script>'.PHP_EOL
			.	\WPPushnami::render_inline_script($options).PHP_EOL
			.'</script>'.PHP_EOL
		;
	}
}
global $pwaforwp_pushnami;
function pwaforwp_pushnami(){
	global $pwaforwp_pushnami;
	if(! $pwaforwp_pushnami instanceof PWAforwp_pushnami){
		$pwaforwp_pushnami = new PWAforwp_pushnami();
	}
	return $pwaforwp_pushnami;
}
pwaforwp_pushnami();