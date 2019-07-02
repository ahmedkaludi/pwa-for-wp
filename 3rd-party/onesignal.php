<?php
// Exit if accessed directly
if ( ! defined('ABSPATH') ) exit;

function pwaforwp_onesignal_compatiblity($action = null) {
		
	if ( class_exists( 'OneSignal' ) ) {
				
		if ( ! is_multisite() ) {
								                        
                        pwaforwp_add_sw_to_onesignal_sw($action);
                        pwaforwp_use_custom_manifest($action);
			                        
		}
								
	}
}

function pwaforwp_use_custom_manifest($action = null){
    
    $url = pwaforwp_home_url();
    
    $onesignal_option = get_option('OneSignalWPSetting');
    
    if($onesignal_option['custom_manifest_url'] == '' && $onesignal_option['use_custom_manifest'] == false){
            
        $onesignal_option['use_custom_manifest'] = true;
        if($action){
            $onesignal_option['use_custom_manifest'] = false;
        }        
        $onesignal_option['custom_manifest_url'] = esc_url($url.'pwa-manifest'.pwaforwp_multisite_postfix().'.json');
        update_option('OneSignalWPSetting', $onesignal_option);
        
    }
    
            
}

function pwaforwp_onesignal_insert_gcm_sender_id( $manifest ) {
                
        if ( ! is_multisite() ) {
            
            if ( class_exists( 'OneSignal' ) ) {
                
                if(is_array($manifest)){

                    $manifest['gcm_sender_id'] = '482941778795';

                }
                        
           }
        
        }
        			
	return $manifest;
}

add_filter( 'pwaforwp_manifest', 'pwaforwp_onesignal_insert_gcm_sender_id' );

function pwaforwp_onesignal_change_sw_name($name){
    
    if ( ! is_multisite() ) {
            
            if ( class_exists( 'OneSignal' ) ) {
            
            $name = 'OneSignalSDKWorker.js';
            
            }
        
        }
           
    return $name;
    
}
add_filter( 'pwaforwp_sw_name_modify', 'pwaforwp_onesignal_change_sw_name' );

function pwaforwp_add_sw_to_onesignal_sw($action = null){
    
        $abs_path              = str_replace("//","/",str_replace("\\","/",realpath(ABSPATH))."/");
        $onesignal_sdk         = $abs_path.'OneSignalSDKWorker.js';
        $onesignal_sdk_updator = $abs_path.'OneSignalSDKUpdaterWorker.js';
        $url                   = pwaforwp_home_url();
                                              
        $content  = "";  
        
        if(!$action){
            $content .= "importScripts('".esc_url($url.'pwa-sw.js')."')".PHP_EOL;
            $content .= "importScripts('https://cdn.onesignal.com/sdks/OneSignalSDKWorker.js')".PHP_EOL;
        }
                                                
        $status = pwaforwp_write_a_file($onesignal_sdk, $content);
        $status = pwaforwp_write_a_file($onesignal_sdk_updator, $content);
       
        return $status;
    
}