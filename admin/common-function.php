<?php    
if ( ! defined( 'ABSPATH' ) ) exit;

function pwaforwp_loading_icon() {
    
    $settings = pwaforwp_defaultSettings();
    
    if(isset($settings['loading_icon'])){
        
        echo '<div id="pwaforwp_loading_div"></div>'
          . '<div id="pwaforwp_loading_icon"></div>';
        
    }
        
}

add_action('wp_footer', 'pwaforwp_loading_icon');

function pwaforwp_reset_all_settings(){ 
    
        if ( ! isset( $_POST['pwaforwp_security_nonce'] ) ){
           return; 
        }
        if ( !wp_verify_nonce( $_POST['pwaforwp_security_nonce'], 'pwaforwp_ajax_check_nonce' ) ){
           return;  
        }    
        if ( ! current_user_can( 'manage_options' ) ) {
           return;
        }
        
        $default = pwaforwp_get_default_settings_array();                        
        $result  = update_option('pwaforwp_settings', $default);   
        
        if($result){    
            
            echo json_encode(array('status'=>'t'));            
        
        }else{
            
            echo json_encode(array('status'=>'f'));            
        
        }        
        wp_die();           
}

add_action('wp_ajax_pwaforwp_reset_all_settings', 'pwaforwp_reset_all_settings');



function pwaforwp_load_plugin_textdomain() {
    load_plugin_textdomain( 'pwa-for-wp', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'pwaforwp_load_plugin_textdomain' );

function pwaforwp_review_notice_close(){    
    
        if ( ! isset( $_POST['pwaforwp_security_nonce'] ) ){
           return; 
        }
        if ( !wp_verify_nonce( $_POST['pwaforwp_security_nonce'], 'pwaforwp_ajax_check_nonce' ) ){
           return;  
        }    
       
        $result =  update_option( "pwaforwp_review_never", 'never');               
        if($result){           
        echo json_encode(array('status'=>'t'));            
        }else{
        echo json_encode(array('status'=>'f'));            
        }        
        wp_die();           
}

add_action('wp_ajax_pwaforwp_review_notice_close', 'pwaforwp_review_notice_close');


function pwaforwp_review_notice_remindme(){   
    
        if ( ! isset( $_POST['pwaforwp_security_nonce'] ) ){
           return; 
        }
        if ( !wp_verify_nonce( $_POST['pwaforwp_security_nonce'], 'pwaforwp_ajax_check_nonce' ) ){
           return;  
        }    
       
        $result =  update_option( "pwaforwp_review_notice_bar_close_date", date("Y-m-d"));               
        if($result){           
            echo json_encode(array('status'=>'t'));            
        }else{
            echo json_encode(array('status'=>'f'));            
        }        
        wp_die();           
}

add_action('wp_ajax_pwaforwp_review_notice_remindme', 'pwaforwp_review_notice_remindme');
/*
 *	 REGISTER ALL NON-ADMIN SCRIPTS
 */
function pwaforwp_frontend_enqueue(){
                               
        $server_key = $config = '';
        
        $settings   = pwaforwp_defaultSettings();
        
        if(isset($settings['normal_enable'])){
            
            if(isset($settings['fcm_server_key'])){
            $server_key = $settings['fcm_server_key'];
        }
        
        if(isset($settings['fcm_config'])){
            $config     = $settings['fcm_config'];
        }
                        
         if(($server_key !='' && $config !='')){             
             
            $swHtmlContentbody   = @wp_remote_get(PWAFORWP_PLUGIN_URL."layouts/push-notification-template.js"); 
            
            if(is_array($swHtmlContentbody) && isset($swHtmlContentbody['body'])){
                $swHtmlContent       = $swHtmlContentbody['body'];
                $firebase_config     = 'var config='.$config.';';
                $swHtmlContent       = str_replace("{{firebaseconfig}}", $firebase_config, $swHtmlContent);  

                $file_creating_obj = new PWAFORWP_File_Creation_Init();
                $file_creating_obj->pwaforwp_push_notification_js($swHtmlContent);
            }                                    
            
            wp_register_script('pwaforwp-push-js', PWAFORWP_PLUGIN_URL . 'assets/js/pwa-push-notification'.pwaforwp_multisite_postfix().'.js', array( 'jquery' ), PWAFORWP_PLUGIN_VERSION, true);

            $object_name = array(
              'ajax_url'                  => admin_url( 'admin-ajax.php' ),
              'pwa_ms_prefix'             => pwaforwp_multisite_postfix(),
              'pwa_home_url'              => pwaforwp_home_url(), 
              'pwaforwp_security_nonce'   => wp_create_nonce('pwaforwp_ajax_check_nonce')  
            );

            wp_localize_script('pwaforwp-push-js', 'pwaforwp_obj', $object_name);
            wp_enqueue_script('pwaforwp-push-js');      
            
         }  
                  
        if(isset($settings['loading_icon'])){
            
            wp_register_script('pwaforwp-js', PWAFORWP_PLUGIN_URL . 'assets/js/pwaforwp.min.js',array('jquery'), PWAFORWP_PLUGIN_VERSION, true); 
         
            $object_js_name = array(
              'ajax_url'       => admin_url( 'admin-ajax.php' ),
              'pwa_ms_prefix'  => pwaforwp_multisite_postfix(),
              'pwa_home_url'   => pwaforwp_home_url(),  
            );
            
            wp_localize_script('pwaforwp-js', 'pwaforwp_js_obj', $object_js_name);
            
            wp_enqueue_script('pwaforwp-js'); 
            
        }
                
        wp_enqueue_style( 'pwaforwp-style', PWAFORWP_PLUGIN_URL . 'assets/css/pwaforwp-main.min.css', false , PWAFORWP_PLUGIN_VERSION );       
        }
        
}
add_action( 'wp_enqueue_scripts', 'pwaforwp_frontend_enqueue' );

if(!function_exists('pwaforwp_is_admin')){
    
	function pwaforwp_is_admin(){
            
		if ( is_admin() ) {
			return true;
		}                
		if ( isset( $_GET['page'] ) && false !== strpos( sanitize_text_field($_GET['page']), 'pwaforwp' ) ) {
			return true;
		}
		return false;
	}
}
function pwaforwp_admin_link($tab = '', $args = array()){	
    
	$page = 'pwaforwp';

	if ( ! is_multisite() ) {
		$link = admin_url( 'admin.php?page=' . $page );
	}
	else {
		$link = admin_url( 'admin.php?page=' . $page );
	}

	if ( $tab ) {
		$link .= '&tab=' . $tab;
	}

	if ( $args ) {
		foreach ( $args as $arg => $value ) {
			$link .= '&' . $arg . '=' . urlencode( $value );
		}
	}

	return esc_url($link);
}


function pwaforwp_get_tab( $default = '', $available = array() ) {

	$tab = isset( $_GET['tab'] ) ? sanitize_text_field($_GET['tab']) : $default;
        
	if ( ! in_array( $tab, $available ) ) {
		$tab = $default;
	}

	return $tab;
}

function pwaforwp_get_default_settings_array(){
    
    $defaults = array(
		'app_blog_name'		=> get_bloginfo( 'name' ),
		'app_blog_short_name'	=> get_bloginfo( 'name' ),
		'description'		=> get_bloginfo( 'description' ),
		'icon'			=> PWAFORWP_PLUGIN_URL . 'images/logo.png',
		'splash_icon'		=> PWAFORWP_PLUGIN_URL . 'images/logo-512x512.png',
		'background_color' 	=> '#D5E0EB',
		'theme_color' 		=> '#D5E0EB',
		'start_url' 		=> 0,
		'start_url_amp'		=> 0,
		'offline_page' 		=> 0,
		'404_page' 		=> 0,
		'orientation'		=> 'portrait',
                'manualfileSetup'	=> 0,
                'cdn_setting'           => 0,
                'normal_enable'         => 1,
                'amp_enable'            => 1,
                'cached_timer'          => array('html'=>3600,'css'=>86400),
                'default_caching'       => 'cacheFirst',
                'default_caching_js_css'=> 'cacheFirst',
                'default_caching_images'=> 'cacheFirst',
                'default_caching_fonts' => 'cacheFirst',
	);
    return $defaults;    
}

function pwaforwp_defaultSettings(){
	
        $defaults = pwaforwp_get_default_settings_array();
	$settings = get_option( 'pwaforwp_settings', $defaults ); 
	return $settings;
}

function pwaforwp_expanded_allowed_tags() {
    
    $my_allowed = wp_kses_allowed_html( 'post' );
    // form fields - input
    $my_allowed['input'] = array(
            'class'        => array(),
            'id'           => array(),
            'name'         => array(),
            'value'        => array(),
            'type'         => array(),
            'style'        => array(),
            'placeholder'  => array(),
            'maxlength'    => array(),
            'checked'      => array(),
            'readonly'     => array(),
            'disabled'     => array(),
            'width'        => array(),
            
    ); 
    //number
    $my_allowed['number'] = array(
            'class'        => array(),
            'id'           => array(),
            'name'         => array(),
            'value'        => array(),
            'type'         => array(),
            'style'        => array(),                    
            'width'        => array(),
            
    ); 
    //textarea
     $my_allowed['textarea'] = array(
            'class' => array(),
            'id'    => array(),
            'name'  => array(),
            'value' => array(),
            'type'  => array(),
            'style'  => array(),
            'rows'  => array(),                                                            
    );              
    // select
    $my_allowed['select'] = array(
            'class'  => array(),
            'id'     => array(),
            'name'   => array(),
            'value'  => array(),
            'type'   => array(),
            'required' => array(),
    );
    //  options
    $my_allowed['option'] = array(
            'selected' => array(),
            'value' => array(),
    );                       
    // style
    $my_allowed['style'] = array(
            'types' => array(),
    );
    return $my_allowed;
}  

function pwaforwp_home_url(){
    
        if ( is_multisite() ) {
            $link = get_site_url();              
        }
        else {
            $link = home_url();
        }    
            $link = pwaforwp_https($link);
    
        return trailingslashit($link);
}
function pwaforwp_site_url(){
    
        if (is_multisite() ) {
            
           $link = get_site_url();   
           
        }
        else {
            $link = site_url();
        }    
            $link = pwaforwp_https($link);
            
        return trailingslashit($link);
}

function pwaforwp_amp_takeover_status(){
    
       $amp_take_over = false;
        
        if ( function_exists( 'ampforwp_is_amp_endpoint' ) || function_exists( 'is_amp_endpoint' )) {
         
            global $redux_builder_amp;

            if(isset($redux_builder_amp['ampforwp-amp-takeover'])){
                
                if($redux_builder_amp['ampforwp-amp-takeover'] == 1){
                    $amp_take_over = true;
                }
                                
            }else{
                
                if(function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() && is_front_page()||is_home() ){
                    $amp_take_over = true;
                }
                
            }
                            
        }
        
        return $amp_take_over;
        
}

function pwaforwp_https( $url ) {
    
        if(strpos($url, 'localhost') === false){            
           return str_replace( 'http://', 'https://', $url );            
        }else{
           return $url;
        }
        	
}

function pwaforwp_multisite_postfix(){
    
        $multisite_postfix = '';
        if ( is_multisite() ) {
           $multisite_postfix = '-' . get_current_blog_id();
        }
        return $multisite_postfix;
                        
}

function pwaforwp_write_a_file($path, $content, $action = null){
        
        $writestatus = '';                        
        
        if(file_exists($path)){
         $writestatus =  unlink($path);
        }
                
        if(!$action){
            if(!file_exists($path) && $content){            
            $handle      = @fopen($path, 'w');
            $writestatus = @fwrite($handle, $content);
            @fclose($handle);
         }
        }
                                        
        if($writestatus){
            return true;   
        }else{
            return false;   
        }
                
}

function pwaforwp_delete_pwa_files(){
    pwaforwp_required_file_creation(true);
}

function pwaforwp_required_file_creation($action = null){
    
                $settings = pwaforwp_defaultSettings(); 
                
                $manualfileSetup = $server_key = $config = '';       
                                                                       
                if(array_key_exists('manualfileSetup', $settings)){
                    $manualfileSetup = $settings['manualfileSetup'];      
                }
                
                $fileCreationInit = new PWAFORWP_File_Creation_Init();
		if($manualfileSetup){
                    
                    $status = '';                    
                    $status = $fileCreationInit->pwaforwp_swjs_init($action);
                    $status = $fileCreationInit->pwaforwp_manifest_init($action);
                    $status = $fileCreationInit->pwaforwp_swr_init($action);
                    
                    
                    if(function_exists( 'ampforwp_is_amp_endpoint' ) || function_exists( 'is_amp_endpoint' )){
                                    
                        $status = $fileCreationInit->pwaforwp_swjs_init_amp($action);
                        $status = $fileCreationInit->pwaforwp_manifest_init_amp($action);
                        $status = $fileCreationInit->pwaforwp_swhtml_init_amp($action);
                                            
                    }                    
                    if(!$status){
                        
                        set_transient( 'pwaforwp_file_change_transient', true );
                    }
                    pwaforwp_onesignal_compatiblity($action);                   
		}
                
                if(isset($settings['fcm_server_key'])){
                 $server_key = $settings['fcm_server_key'];    
                }
                
                if(isset($settings['fcm_config'])){
                 $config     = $settings['fcm_config'];   
                }
                                                 
                if($server_key !='' && $config !=''){
                  $fileCreationInit->pwaforwp_swhtml_init_firebase_js($action);  
                }
    
}

add_action('wp_ajax_pwaforwp_download_require_files', 'pwaforwp_download_require_files');

function pwaforwp_download_require_files(){ 
    
        if ( ! current_user_can( 'manage_options' ) ) {
             return;
        }
        if ( ! isset( $_GET['_wpnonce'] ) ){
             return; 
        }
        if ( !wp_verify_nonce( $_GET['_wpnonce'], '_wpnonce' ) ){
             return;  
        }
        
          $creation_obj     = new pwaforwpFileCreation();
     
          $swjs             = $creation_obj->pwaforwp_swjs();
          $manifest         = $creation_obj->pwaforwp_manifest();
          $rswjs            = $creation_obj->pwaforwp_swr();
          
          $ampsw_js         = $creation_obj->pwaforwp_swjs(true);
          $amp_manifest     = $creation_obj->pwaforwp_manifest(true);
          $amp_swhtml       = $creation_obj->pwaforwp_swhtml(true);
          $pn_sw_js         = '';
                    
          $pn_manifest      = '{"gcm_sender_id": "103953800507"}';
                  
          $files = array(
               "pwa-sw".pwaforwp_multisite_postfix().".js"                           => $swjs,
               "pwa-manifest".pwaforwp_multisite_postfix().".json"                   => $manifest,
               "pwa-register-sw".pwaforwp_multisite_postfix().".js"                  => $rswjs,               
               "pwa-push-notification-manifest".pwaforwp_multisite_postfix().".json" => $pn_manifest,
               "firebase-messaging-sw.js"                                            => $pn_sw_js,
           );
          
           if ((function_exists( 'ampforwp_is_amp_endpoint' )) || function_exists( 'is_amp_endpoint' )) {                  
                                              
               $files["pwa-amp-sw".pwaforwp_multisite_postfix().".js"]         = $ampsw_js;
               $files["pwa-amp-manifest".pwaforwp_multisite_postfix().".json"] = $amp_manifest;
               $files["pwa-amp-sw".pwaforwp_multisite_postfix().".html"]       = $amp_swhtml;
           }
 
           # create new zip opbject
           $zip = new ZipArchive();

           # create a temp file & open it
           $tmp_file = tempnam('.','');
           $zip->open($tmp_file, ZipArchive::CREATE);

           # loop through each file
           foreach($files as $file => $value){
              
               # download file
               @file_put_contents($file,$value);
               $download_file = @file_get_contents($file);
               
               #add it to the zip
               $zip->addFromString(basename($file),$download_file);

           }

           # close zip
           $zip->close();

           # send the file to the browser as a download
           header('Content-disposition: attachment; filename=download.zip');
           header('Content-type: application/zip');
           readfile($tmp_file);
           unlink($tmp_file);
     
        wp_die();
    
}