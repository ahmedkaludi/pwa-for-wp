<?php
// Exit if accessed directly
if ( ! defined('ABSPATH') ) exit;

function pwaforwp_onesignal_compatiblity() {
	
	
	if ( class_exists( 'OneSignal' ) ) {
		
		
		if ( ! is_multisite() ) {
		
			
			add_filter( 'pwaforwp_manifest_file', 'pwaforwp_onesignal_insert_gcm_sender_id' );
                        
                        add_filter( 'pwaforwp_update_onsignal_sw', 'pwaforwp_add_sw_to_onesignal_sw' );
			
                        
		}
								
	}
}
add_action( 'plugins_loaded', 'pwaforwp_onesignal_compatiblity' );



function pwaforwp_onesignal_insert_gcm_sender_id( $manifest ) {
	
	$manifest['gcm_sender_id'] = '482941778795';
	
	return $manifest;
}

function pwaforwp_add_sw_to_onesignal_sw(){
    
    
    
}

