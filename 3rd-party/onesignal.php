<?php
// Exit if accessed directly
if ( ! defined('ABSPATH') ) exit;

function pwaforwp_onesignal_compatiblity() {
	
	
	if ( class_exists( 'OneSignal' ) ) {
		
		
		if ( ! is_multisite() ) {
		
			
			add_filter( 'pwaforwp_manifest', 'pwaforwp_onesignal_insert_gcm_sender_id' );
			
			
			add_filter( 'pwaforwp_sw_file_name', 'pwaforwp_onesignal_sw_filename' );
			
			
			add_filter( 'pwaforwp_sw_js_template', 'pwaforwp_onesignal_sw' );
		}
								
	}
}
add_action( 'plugins_loaded', 'pwaforwp_onesignal_compatiblity' );

function pwaforwp_onesignal_sw( $sw ) {
	
	
	$match = preg_grep( '#Content-Type: text/javascript#i', headers_list() );
	
	if ( ! empty ( $match ) ) {
		
		$onesignal = 'importScripts( \'' . pwaforwp_https( plugin_dir_url( 'onesignal-free-web-push-notifications/onesignal.php' ) ) . 'sdk_files/OneSignalSDKWorker.js.php\' );' . PHP_EOL;
	
		return $onesignal . $sw;
	}
	
	$onesignal  = '<?php' . PHP_EOL; 
	$onesignal .= 'header( "Content-Type: application/javascript" );' . PHP_EOL;
	$onesignal .= 'echo "importScripts( \'' . pwaforwp_https( plugin_dir_url( 'onesignal-free-web-push-notifications/onesignal.php' ) ) . 'sdk_files/OneSignalSDKWorker.js.php\' );";' . PHP_EOL;
	$onesignal .= '?>' . PHP_EOL . PHP_EOL;
	
	return $onesignal . $sw;
}

function pwaforwp_onesignal_insert_gcm_sender_id( $manifest ) {
	
	$manifest['gcm_sender_id'] = '482941778795';
	
	return $manifest;
}

function pwaforwp_onesignal_sw_filename( $sw_file_name ) {
	return 'OneSignalSDKWorker.js.php';
}

