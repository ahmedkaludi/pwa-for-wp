<?php
// Exit if accessed directly
if ( ! defined('ABSPATH') ) exit;

function pwaforwp_pushnami_compatiblity($action = null) {

	if ( class_exists( 'WPPushnami' ) ) {
		pn_pwaforwp_use_custom_manifest($action);
		if ( ! is_multisite() ) {              
			pwaforwp_add_sw_to_pushnami_sw($action);
		}
		register_deactivation_hook( PWAFORWP_PLUGIN_FILE, function () {
			$pn_settings						= \WPPushnami::get_script_options();
			$pn_settings['use_custom_manifest'] = false;
			\WPPushnami::save_pushnami_options( $pn_settings );
		} );
	}

}

function pn_pwaforwp_use_custom_manifest($action = null) {

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

function pwaforwp_pushnami_insert_gcm_sender_id( $manifest ) {

	if ( class_exists( 'WPPushnami' ) ) {

		if(is_array($manifest)){

			$manifest['gcm_sender_id'] = '733213273952';

		}

	}

	return $manifest;
}
add_filter( 'pwaforwp_manifest', 'pwaforwp_pushnami_insert_gcm_sender_id' );

function pwaforwp_pushnami_change_sw_name( $name ) {

	if ( ! is_multisite() ) {

		if ( class_exists( 'WPPushnami' ) ) {

			$name = 'PushnamiWorker.js';

		}

	}

	return $name;

}
add_filter( 'pwaforwp_sw_name_modify', 'pwaforwp_pushnami_change_sw_name' );

function pwaforwp_add_sw_to_pushnami_sw( $action = null ) {

	$abs_path     = str_replace("//","/",str_replace("\\","/",realpath(ABSPATH))."/");
	$pn_worker = $abs_path.'PushnamiWorker.js';
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
add_action("wp", 'pwaforwp_pushnami_for_multisite');

function pwaforwp_pushnami_for_multisite() {

	if (  class_exists('WPPushnami') ) {//is_multisite() &&

		remove_action( 'wp_head', [ 'Pushnami_Public', 'pushnami_header' ] );
		add_action( 'wp_head',  'pwaforwp_pushnami_init_pushnami_head' );

	}

}

function pwaforwp_pushnami_init_pushnami_head() {

	$options = \WPPushnami::get_script_options();
	$home_url = trailingslashit(pwaforwp_home_url());
	$options->swPath = $home_url . '?pwa_for_wp_script=1&sw=PushnamiWorker.js';

	echo PHP_EOL
		.'<meta name="pushnami" content="wordpress-plugin"/>'.PHP_EOL
		.'<script>'.PHP_EOL
		.	\WPPushnami::render_inline_script($options).PHP_EOL
		.'</script>'.PHP_EOL
	;

}
