<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

class PWA_Utility{
	public function init(){
		add_action("wp_ajax_pwafowp_enable_modules_upgread", array($this, 'enable_modules') );

	}

	public function enable_modules(){
		if(!wp_verify_nonce( $_REQUEST['verify_nonce'], 'verify_request' ) ) {
	        echo json_encode(array("status"=>300,"message"=>'Request not valid'));
	        exit();
	    }
	    // Exit if the user does not have proper permissions
	    if(! current_user_can( 'install_plugins' ) ) {
	        echo json_encode(array("status"=>300,"message"=>'User Request not valid'));
	        exit();
	    }

	    $plugins = array();
	    $redirectSettingsUrl = '';
	    $currentActivateModule = sanitize_text_field( wp_unslash($_REQUEST['activate']));
	    switch($currentActivateModule){
	    	case 'pushnotification': 
	            $nonceUrl = add_query_arg(
	                                    array(
	                                        'action'        => 'activate',
	                                        'plugin'        => 'push-notification',
	                                        'plugin_status' => 'all',
	                                        'paged'         => '1',
	                                        '_wpnonce'      => wp_create_nonce( 'activate-plugin_push-notification' ),
	                                    ),
	                        esc_url(network_admin_url( 'plugins.php' ))
	                        );
	            $plugins[] = array(
	                            'name' => 'push-notification',
	                            'path_' => 'https://downloads.wordpress.org/plugin/push-notification.zip',
	                            'path' => $nonceUrl,
	                            'install' => 'push-notification/push-notification.php',
	                        );
	            $redirectSettingsUrl = admin_url('admin.php?page=push-notification&reference=pwaforwp');
	        break;
	    }

	    if(count($plugins)>0){
	       echo json_encode( array( "status"=>200, "message"=>"Module successfully Added",'redirect_url'=>esc_url($redirectSettingsUrl) , "slug"=>$plugins[0]['name'], 'path'=> $plugins[0]['path'] ) );
	    }else{
	        echo json_encode(array("status"=>300, "message"=>"Modules not Found"));
	    }
	    wp_die();

	}
}

$PWA_UtilityObj = new PWA_Utility();
$PWA_UtilityObj->init();