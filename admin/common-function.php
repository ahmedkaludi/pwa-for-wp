<?php    
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
        
        $multisite_filename_postfix = '';
        if ( is_multisite() ) {
           $multisite_filename_postfix = '-' . get_current_blog_id();
        }          
        $settings   = pwaforwp_defaultSettings();
        $server_key = $settings['fcm_server_key'];
        $config     = $settings['fcm_config'];
        
         if($server_key !='' && $config !=''){             
             
            $swHtmlContent   = file_get_contents(PWAFORWP_PLUGIN_DIR."layouts/push-notification-template.js");    
            $firebase_config = 'var config='.$config.';';
            $swHtmlContent   = str_replace("{{firebaseconfig}}", $firebase_config, $swHtmlContent);  

            $file_creating_obj = new PWAFORWP_File_Creation_Init();
            $file_creating_obj->pwaforwp_push_notification_js($swHtmlContent);

            wp_register_script('pwaforwp-push-js', PWAFORWP_PLUGIN_URL . 'assets/'.PWAFORWP_FILE_PREFIX.'-push-notification'.$multisite_filename_postfix.'.js', array( 'jquery' ), PWAFORWP_PLUGIN_VERSION, true);

            $object_name = array(
              'ajax_url' => admin_url( 'admin-ajax.php' ),           
            );

            wp_localize_script('pwaforwp-push-js', 'pwaforwp_obj', $object_name);
            wp_enqueue_script('pwaforwp-push-js');      
            
         }  
         
         
        if(isset($settings['loading_icon'])){
            
            wp_register_script('pwaforwp-js', PWAFORWP_PLUGIN_URL . 'assets/pwaforwp.js',array(), PWAFORWP_PLUGIN_VERSION, true); 
         
            wp_enqueue_script('pwaforwp-js'); 
            
        }
        
        
        wp_enqueue_style( 'pwaforwp-style', PWAFORWP_PLUGIN_URL . 'assets/pwaforwp-main.css', false , PWAFORWP_PLUGIN_VERSION );       
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

function pwaforwp_front_url(){
    
        if ( ! is_multisite() ) {
            $link = home_url();
        }
        else {
            $link = network_site_url();
        }    
    $link = str_replace("http:","https:", $link);
    
    return trailingslashit($link);
}

add_action('wp_ajax_pwaforwp_download_require_files', 'pwaforwp_download_require_files');

function pwaforwp_download_require_files(){ 
                                 
          $multisite_filename_postfix = '';
          
          if ( is_multisite() ) {
            $multisite_filename_postfix = '-' . get_current_blog_id();
          }
                                
          $creation_obj = new pwaforwpFileCreation();
     
          $swjs             = $creation_obj->pwaforwp_swjs();
          $manifest         = $creation_obj->pwaforwp_manifest();
          $rswjs            = $creation_obj->pwaforwp_swr();
          
          $ampsw_js         = $creation_obj->pwaforwp_swjs(true);
          $amp_manifest     = $creation_obj->pwaforwp_manifest(true);
          $amp_swhtml       = $creation_obj->pwaforwp_swhtml(true);
          $pn_sw_js         = '';
          
          
          $pn_manifest      = '{"gcm_sender_id": "103953800507"}';
                  
          $files = array(
               "pwa-sw".$multisite_filename_postfix.".js"                           => $swjs,
               "pwa-manifest".$multisite_filename_postfix.".json"                   => $manifest,
               "pwa-register-sw".$multisite_filename_postfix.".js"                  => $rswjs,               
               "pwa-push-notification-manifest".$multisite_filename_postfix.".json" => $pn_manifest,
               "firebase-messaging-sw.js"                                           => $pn_sw_js,
           );
          
           if ((function_exists( 'ampforwp_is_amp_endpoint' ) && ampforwp_is_amp_endpoint()) || function_exists( 'is_amp_endpoint' ) && is_amp_endpoint()) {                  
                                              
               $files["pwa-amp-sw".$multisite_filename_postfix.".js"]         = $ampsw_js;
               $files["pwa-amp-manifest".$multisite_filename_postfix.".json"] = $amp_manifest;
               $files["pwa-amp-sw".$multisite_filename_postfix.".html"]       = $amp_swhtml;
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