<?php
// Exit if accessed directly
if ( ! defined('ABSPATH') ) exit;

function pwaforwp_onesignal_compatiblity() {
	
	
	if ( class_exists( 'OneSignal' ) ) {
		
		// Filter manifest and service worker for singe websites and not for multisites.
		if ( ! is_multisite() ) {
		
			// Add gcm_sender_id to SuperPWA manifest
			add_filter( 'pwaforwp_manifest', 'pwaforwp_onesignal_add_gcm_sender_id' );
			
			// Change service worker filename to match OneSignal's service worker
			add_filter( 'pwaforwp_sw_filename', 'pwaforwp_onesignal_sw_filename' );
			
			// Import OneSignal service worker in SuperPWA
			add_filter( 'pwaforwp_sw_template', 'pwaforwp_onesignal_sw' );
		}
		
		// Show admin notice.
		add_action( 'admin_notices', 'pwaforwp_onesignal_admin_notices', 9 );
		add_action( 'network_admin_notices', 'pwaforwp_onesignal_admin_notices', 9 );
	}
}
add_action( 'plugins_loaded', 'pwaforwp_onesignal_todo' );