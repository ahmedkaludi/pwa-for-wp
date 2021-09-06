<?php
// Exit if accessed directly
if ( ! defined('ABSPATH') ) exit;

function pwaforwp_onesignal_compatiblity($action = null) {
        
    if ( class_exists( 'OneSignal' ) ) {
        pwaforwp_use_custom_manifest($action);
        add_filter('pwaforwp_sw_js_template', 'pwaforwp_add_sw_to_onesignal_sw',10,1);
        
        register_deactivation_hook( PWAFORWP_PLUGIN_FILE, function () {
            $os_settings                        = \OneSignal::get_onesignal_settings();
            $os_settings['use_custom_manifest'] = false;
            \OneSignal::save_onesignal_settings( $os_settings );
        } );
    }
}

function pwaforwp_use_custom_manifest($action = null){
    
    $url = pwaforwp_home_url();
    
    $onesignal_option = get_option('OneSignalWPSetting');
    
    if(@$onesignal_option['custom_manifest_url'] == '' && @$onesignal_option['use_custom_manifest'] == false){
            
        $onesignal_option['use_custom_manifest'] = true;
        if($action){
            $onesignal_option['use_custom_manifest'] = false;
        }        
        if(function_exists('ampforwp_is_amp_endpoint') && ampforwp_is_amp_endpoint()){
            $onesignal_option['custom_manifest_url'] = esc_url( pwaforwp_manifest_json_url(true) );
        }else{
            $onesignal_option['custom_manifest_url'] = esc_url( pwaforwp_manifest_json_url() );//esc_url($url.'pwa-manifest'.pwaforwp_multisite_postfix().'.json');
        }
        update_option('OneSignalWPSetting', $onesignal_option);
        
    }
    //update own settings
    $get_pwaforwp_options   = pwaforwp_defaultSettings();
    $get_pwaforwp_options['one_signal_support_setting'] = 1;
    update_option('pwaforwp_settings', $get_pwaforwp_options);  
    
            
}

function pwaforwp_onesignal_insert_gcm_sender_id( $manifest ) {
                
            
            if ( class_exists( 'OneSignal' ) ) {
                
                if(is_array($manifest)){

                    $manifest['gcm_sender_id'] = '482941778795';

                }
                        
           }
        
                    
    return $manifest;
}

add_filter( 'pwaforwp_manifest', 'pwaforwp_onesignal_insert_gcm_sender_id' );

function pwaforwp_onesignal_change_sw_name($name){
    
            
            if ( class_exists( 'OneSignal' ) ) {
            
            $name = 'OneSignalSDKWorker'.pwaforwp_multisite_postfix().'.js.php';
            
            }
        
           
    return $name;
    
}
add_filter( 'pwaforwp_sw_name_modify', 'pwaforwp_onesignal_change_sw_name' );

function pwaforwp_add_sw_to_onesignal_sw($content = null){
    
    $onesignal = '<?php header("Service-Worker-Allowed: /");
    header("Content-Type: application/javascript");
    header("X-Robots-Tag: none"); ?>
    importScripts( \'' . pwaforwp_https( plugin_dir_url( 'onesignal-free-web-push-notifications/onesignal.php' ) ) . 'sdk_files/OneSignalSDKWorker.js.php\' );' . PHP_EOL;
    $content = $onesignal . $content;
    
    return $content;
    
}