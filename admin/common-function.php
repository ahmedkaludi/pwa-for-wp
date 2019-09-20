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
                  
        if(isset($settings['loading_icon']) || isset($settings['add_to_home_sticky']) || isset($settings['add_to_home_menu'])){
            
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
    'fcm_push_icon'   => PWAFORWP_PLUGIN_URL . 'images/logo.png',
		'background_color' 	=> '#D5E0EB',
		'theme_color' 		=> '#D5E0EB',
		'start_url' 		=> 0,
		'start_url_amp'		=> 0,
		'offline_page' 		=> 0,
		'404_page' 		=> 0,
                'start_page' 		=> 0,
		'orientation'		=> 'portrait',
    'display'       => 'standalone',
                'manualfileSetup'	=> 0,
                'cdn_setting'           => 0,
                'normal_enable'         => 1,
                'amp_enable'            => 1,
                'cached_timer'          => array('html'=>3600,'css'=>86400),
                'default_caching'       => 'cacheFirst',
                'default_caching_js_css'=> 'cacheFirst',
                'default_caching_images'=> 'cacheFirst',
                'default_caching_fonts' => 'cacheFirst',
                'on_add_post_notification_title' => ''
	);
    return $defaults;    
}
$pwaforwp_settings;
function pwaforwp_defaultSettings(){
    
	global $pwaforwp_settings;
        $defaults = pwaforwp_get_default_settings_array();
	$pwaforwp_settings = get_option( 'pwaforwp_settings', $defaults ); 
  if(!isset($pwaforwp_settings['normal_enable'])){
    $pwaforwp_settings['normal_enable'] = 1;
  }
	return $pwaforwp_settings;
        
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
                
                    $server_key = $config = '';       
                
                    $fileCreationInit = new PWAFORWP_File_Creation_Init();
                		                    
                    $status = '';                    
                    $status = $fileCreationInit->pwaforwp_swjs_init($action);
                    $status = $fileCreationInit->pwaforwp_manifest_init($action);
                    $status = $fileCreationInit->pwaforwp_swr_init($action);
                    $status = $fileCreationInit->pwaforwp_push_notification_js($action);
                    
                    
                    if(function_exists( 'ampforwp_is_amp_endpoint' ) || function_exists( 'is_amp_endpoint' )){
                                    
                        $status = $fileCreationInit->pwaforwp_swjs_init_amp($action);
                        $status = $fileCreationInit->pwaforwp_manifest_init_amp($action);
                        $status = $fileCreationInit->pwaforwp_swhtml_init_amp($action);
                                            
                    }                    
                    if(!$status){
                        
                        set_transient( 'pwaforwp_file_change_transient', true );
                    }
                    
                    pwaforwp_onesignal_compatiblity($action);                   
		                
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

function pwaforwp_query_var($key=''){
  $default = array(
              'sw_query_var'=>'pwa_for_wp_script',
              'sw_file_var'=> 'sw',
            );
  //if(is_multisite()){
    $default['site_id_var'] = 'site';
  //}
  if(!empty($key) && isset($default[$key])){
    return $default[$key];
  }else{
    return $default;
  }
}

function pwaforwp_manifest_json_url($is_amp=false){
  $link = '';
  $jsonp_enabled = apply_filters( 'rest_jsonp_enabled', true );
  if($jsonp_enabled){
    $link = rest_url( 'pwa-for-wp/v2/pwa-manifest-json' );
    if($is_amp){
      $link = rest_url( 'pwa-for-wp/v2/pwa-manifest-json/amp' );
    }
  }else{
    $url       = pwaforwp_site_url(); 
    $link = parse_url($url.'pwa-manifest'.pwaforwp_multisite_postfix().'.json', PHP_URL_PATH);
    if($is_amp){
      $link = $url.'pwa-amp-manifest'.pwaforwp_multisite_postfix().'.json';
    }
  }
  return $link;
}

add_filter("pwaforwp_file_creation_path", "pwaforwp_check_root_writable", 10, 1);
function pwaforwp_check_root_writable($wppath){
  $uploadArray = wp_upload_dir();
  $uploadBasePath = trailingslashit($uploadArray['basedir']);
  if(!is_writable($uploadBasePath)){
    $uploadPwaFolder = "pwaforwp";
    $newpath = $uploadBasePath.$uploadPwaFolder;
    wp_mkdir_p($newpath);
    return trailingslashit($newpath);
  }
  return $wppath;
}

function service_workerUrls($url, $filename){
  $uploadArray    = wp_upload_dir();
  $uploadBasePath = trailingslashit($uploadArray['basedir']);
  
  $site_url       = pwaforwp_site_url();
  $home_url       = pwaforwp_home_url();  


  if( ( is_multisite() || !pwaforwp_is_file_inroot() || $site_url!= $home_url) &&  !class_exists( 'OneSignal' ) ){
	  $filename = str_replace(".", "-", $filename);
    $url = esc_url_raw($home_url.'?'.pwaforwp_query_var('sw_query_var').'=1&'.pwaforwp_query_var('sw_file_var').'='.$filename); 
  }
  return $url;
}

function pwaforwp_is_file_inroot(){
  if(is_writable(ABSPATH)){
    return true;
  }else{
    return false;
  }
}
