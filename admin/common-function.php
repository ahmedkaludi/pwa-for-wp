<?php    
if ( ! defined( 'ABSPATH' ) ) exit;

function pwaforwp_loading_icon() {
    
    if( function_exists('is_amp_endpoint') && is_amp_endpoint() || is_preview() || (function_exists('is_preview_mode') && is_preview_mode()) ){return false;}
    $settings = pwaforwp_defaultSettings();
    if(isset($settings['loading_icon']) && $settings['loading_icon']==1){
        $color = (isset($settings['loading_icon_color']) && !empty($settings['loading_icon_color']))? $settings['loading_icon_color'] : '';
        $bgcolor = (isset($settings['loading_icon_bg_color']) && !empty($settings['loading_icon_bg_color']))? $settings['loading_icon_bg_color'] : '';
        $color_style = $bg_color_style = '';
        if($color){
            $color_style = 'style="border-top-color: '.$color.'"';
        }
        if($bgcolor!=='#ffffff'){ $bg_color_style = 'style="background-color: '.$bgcolor.'"'; }
        echo '<div id="pwaforwp_loading_div" '.$bg_color_style.'></div>';
        echo apply_filters('pwaforwp_loading_contents', '<div class="pwaforwp-loading-wrapper"><div id="pwaforwp_loading_icon"  '.$color_style.'></div></div>');
    }
        
}
if(!is_admin()){
    add_action('wp_footer', 'pwaforwp_loading_icon');
}

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
        delete_transient('pwaforwp_restapi_check');   
        
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
    if ( class_exists('\Elementor\Plugin') && \Elementor\Plugin::$instance->preview->is_preview_mode() ) { return ; }
                               
        $server_key = $config = '';
        
        $settings   = pwaforwp_defaultSettings();
        
        if(isset($settings['normal_enable']) && $settings['normal_enable']==1){
            
            if(isset($settings['fcm_server_key'])){
            $server_key = $settings['fcm_server_key'];
        }
        
        if(isset($settings['fcm_config'])){
            $config     = $settings['fcm_config'];
        }
                        
         if(isset($settings['notification_feature']) && $settings['notification_feature']==1 && isset($settings['notification_options']) && $settings['notification_options']=='fcm_push' && ($server_key !='' && $config !='')){             
                                                                         
            wp_register_script('pwaforwp-push-js', PWAFORWP_PLUGIN_URL . 'assets/js/pwa-push-notification'.pwaforwp_multisite_postfix().'.js', array('pwa-main-script'), PWAFORWP_PLUGIN_VERSION, true);

            $object_name = array(
              'ajax_url'                  => admin_url( 'admin-ajax.php' ),
              'pwa_ms_prefix'             => pwaforwp_multisite_postfix(),
              'pwa_home_url'              => pwaforwp_home_url(), 
              'pwaforwp_security_nonce'   => wp_create_nonce('pwaforwp_ajax_check_nonce')  
            );

            wp_localize_script('pwaforwp-push-js', 'pwaforwp_obj', $object_name);
            wp_enqueue_script('pwaforwp-push-js');      
            
         }  
                  
        if( (isset($settings['loading_icon']) && $settings['loading_icon']==1) || isset($settings['add_to_home_sticky']) || isset($settings['add_to_home_menu'])){
            
            wp_register_script('pwaforwp-js', PWAFORWP_PLUGIN_URL . 'assets/js/pwaforwp.min.js',array(), PWAFORWP_PLUGIN_VERSION, true); 
            
            $loader_desktop = $loader_mobile = $loader_only_pwa = 0;
            //For desktop
            if( isset($settings['loading_icon_display_pwa']) && !empty($settings['loading_icon_display_pwa']) ){
                $loader_only_pwa = $settings['loading_icon_display_pwa'];
            }

            //For desktop
            if(isset($settings['loading_icon_display_desktop'])):
                $loader_desktop = $settings['loading_icon_display_desktop'];
            elseif(isset($settings['loading_icon']) && $settings['loading_icon']==1) ://Falback for old users
                $loader_desktop = 1;
            endif;

            //For mobile
            if(isset($settings['loading_icon_display_mobile'])):
                $loader_mobile = $settings['loading_icon_display_mobile'];
            elseif(isset($settings['loading_icon']) && $settings['loading_icon']==1) ://Falback for old users
                $loader_mobile = 1;
            endif;
            $reset_cookies=0;
            if(isset($settings['reset_cookies']) && $settings['reset_cookies']==1){
                $reset_cookies=1;
            }
            $object_js_name = array(
              'ajax_url'       => admin_url( 'admin-ajax.php' ),
              'pwa_ms_prefix'  => pwaforwp_multisite_postfix(),
              'pwa_home_url'   => pwaforwp_home_url(),  
              'loader_desktop' => $loader_desktop,
              'loader_mobile'  => $loader_mobile,
              'loader_only_pwa'  => $loader_only_pwa,
              'reset_cookies'  => $reset_cookies,
            );
            
            wp_localize_script('pwaforwp-js', 'pwaforwp_js_obj', $object_js_name);
            
            wp_enqueue_script('pwaforwp-js'); 
            
        }
                
        wp_enqueue_style( 'pwaforwp-style', PWAFORWP_PLUGIN_URL . 'assets/css/pwaforwp-main.min.css', false , PWAFORWP_PLUGIN_VERSION );       
        wp_style_add_data( 'pwaforwp-style', 'rtl', 'replace' );
        }
        
}
add_action( 'wp_enqueue_scripts', 'pwaforwp_frontend_enqueue', 35 );

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
		'app_blog_name'		     => get_bloginfo( 'name' ),
		'app_blog_short_name'	 => get_bloginfo( 'name' ),
		'description'		     => get_bloginfo( 'description' ),
		'icon'			         => PWAFORWP_PLUGIN_URL . 'images/logo.png',
		'splash_icon'		     => PWAFORWP_PLUGIN_URL . 'images/logo-512x512.png',
        //Splash icon
        'switch_apple_splash_screen'=>0,
        'ios_splash_icon'=> array(
                                '640x1136'=>'',
                                '750x1334'=>'',
                                '1242x2208'=>'',
                                '1125x2436'=>'',
                                '828x1792'=>'',
                                '1242x2688'=>'',
                                '1536x2048'=>'',
                                '1668x2224'=>'',
                                '1668x2388'=>'',
                                '2048x2732'=>'',
                                ),
        'custom_banner_background_color'=>'#D5E0EB',
        'custom_banner_title_color'=>'#000',
        'custom_banner_btn_color'=>'#006dda',
        'custom_banner_btn_text_color'=>'#fff',
        'fcm_push_icon'   => PWAFORWP_PLUGIN_URL . 'images/logo.png',
        'background_color' 	=> '#D5E0EB',
        'theme_color' 		=> '#D5E0EB',
        'start_url' 		=> 0,
        'start_url_amp'		=> 0,
        'offline_page' 		=> 0,
        '404_page' 		    => 0,
        'start_page' 		=> 0,
        'orientation'		=> 'portrait',
        'display'           => 'standalone',
        'ios_status_bar'    => 'default',
        'manualfileSetup'	=> 0,
        'cdn_setting'           => 0,
        'normal_enable'         => 1,
        'amp_enable'            => 1,
        'cached_timer'          => array('html'=>3600,'css'=>86400),
        'serve_js_cache_menthod'=> "false",
        'default_caching'       => 'cacheFirst',
        'default_caching_js_css'=> 'cacheFirst',
        'default_caching_images'=> 'cacheFirst',
        'default_caching_fonts' => 'cacheFirst',
        'on_add_post_notification_title' => '',

    /*Push notification services*/
        'notification_options'  => '',
    /*Features settings*/
        'notification_feature'  => 0,
        'precaching_feature'    => 0,
        'addtohomebanner_feature'=> 0,
        'utmtracking_feature'   => 0,
        'loader_feature'         => 0,

    /*UTM*/
        'utm_setting'   => 0,
        'utm_details' => array(
                        'utm_source'=> 'pwa-app',
                        'utm_medium'=> 'pwa-app',
                        'utm_campaign'=> 'pwa-campaign',
                        'utm_term'  => 'pwa-term',
                        'utm_content'  => 'pwa-content',
                        ),
    /*Pre caching*/
        'precaching_automatic'=> 0,
        'precaching_automatic_post'=> 0,
        'precaching_automatic_page'=> 0,
        'precaching_post_count'=> 5,
        'precaching_automatic_custom_post'=> 0,
        'precaching_manual'    => 0, 
        'precaching_urls'    => '', 
    /*loader icon*/
        'loading_icon'      => 0,
        'loading_icon_color'=> '#3498db',
        'loading_icon_bg_color'=> '#ffffff',
        'loading_icon_display_admin'=>0,
	);
    $defaults = apply_filters("pwaforwp_default_settings_vals",$defaults);
    return $defaults;    
}
$pwaforwp_settings = array();
function pwaforwp_defaultSettings(){
    
	global $pwaforwp_settings;
	if( empty($pwaforwp_settings) || (is_array($pwaforwp_settings) && count($pwaforwp_settings)==0) ){
        $defaults = pwaforwp_get_default_settings_array();
        $pwaforwp_settings = get_option( 'pwaforwp_settings', $defaults ); 
        $pwaforwp_settings = wp_parse_args($pwaforwp_settings, $defaults);
    }

    //Fallback for features tab
    $pwaforwp_settings = pwaforwp_migration_setup_fetures($pwaforwp_settings);

    //autoptimize cdn compatibility
    $cdnUrl = false;
    if(function_exists('autoptimize_autoload')){
        $cdnUrl = get_option( 'autoptimize_cdn_url', '' );
    }
    if($cdnUrl){
        $pwaforwp_settings['external_links_setting'] = 1;
    }
    $pwaforwp_settings = apply_filters("pwaforwp_final_settings_vals",$pwaforwp_settings);
	return $pwaforwp_settings;
        
}

function pwaforwp_migration_setup_fetures($pwaforwp_settings){
    if(isset($pwaforwp_settings['precaching_feature']) && $pwaforwp_settings['precaching_feature']==0 && isset($pwaforwp_settings['precaching_automatic']) && $pwaforwp_settings['precaching_automatic'] == 1 ){
        $pwaforwp_settings['precaching_feature'] = 1;
    }

    if(isset($pwaforwp_settings['addtohomebanner_feature']) && $pwaforwp_settings['addtohomebanner_feature']==0 && isset($pwaforwp_settings['custom_add_to_home_setting']) && $pwaforwp_settings['custom_add_to_home_setting'] == 1 ){
        $pwaforwp_settings['addtohomebanner_feature'] = 1;
    }
    if(isset($pwaforwp_settings['utmtracking_feature']) && $pwaforwp_settings['utmtracking_feature']==0 && isset($pwaforwp_settings['utm_setting']) && $pwaforwp_settings['utm_setting'] == 1 ){
        $pwaforwp_settings['utmtracking_feature'] = 1;
    }
    if(isset($pwaforwp_settings['loader_feature']) && $pwaforwp_settings['loader_feature']==0 && isset($pwaforwp_settings['loading_icon']) && $pwaforwp_settings['loading_icon'] == 1 ){
        $pwaforwp_settings['loader_feature'] = 1;
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
        
        if ( function_exists( 'ampforwp_is_amp_endpoint' ) || function_exists( 'is_amp_endpoint' ) && !defined('AMP_WP_DIR_PATH')) {
         
            global $redux_builder_amp;

            if(isset($redux_builder_amp['ampforwp-amp-takeover'])){
                
                if($redux_builder_amp['ampforwp-amp-takeover'] == 1){
                    $amp_take_over = true;
                }
                                
            }else{
                
                if(function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() && (is_front_page()||is_home()) ){
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

                    pwaforwp_onesignal_compatiblity($action);
                    pwaforwp_pushnami()->pushnami_compatiblity($action); 
                		                    
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
  $fileCheck = false;
  $multisite_postfix = pwaforwp_multisite_postfix();
  $wppath = ABSPATH;
  $wppath = apply_filters("pwaforwp_file_creation_path", $wppath);
  if(!is_admin() && !is_multisite()){
      $fileCheck = file_exists($wppath .apply_filters('pwaforwp_manifest_file_name', "pwa-manifest".pwaforwp_multisite_postfix().".json"));
      if($is_amp){
        $fileCheck = file_exists($wppath .apply_filters('pwaforwp_amp_manifest_file_name', "pwa-amp-manifest".pwaforwp_multisite_postfix().".json"));
      }
   }
  if($fileCheck && !$multisite_postfix){
    $restApiEnabled = 400;
  }else{
    $restApiEnabled = get_transient( 'pwaforwp_restapi_check' ); 
    if ( $restApiEnabled===false || empty($restApiEnabled) ) {
        $response = wp_remote_get( rest_url( 'pwa-for-wp/v2/pwa-manifest-json' ) );
        $restApiEnabled = wp_remote_retrieve_response_code($response);
        set_transient( "pwaforwp_restapi_check", $restApiEnabled );
    }
  }

  if($restApiEnabled==200){
    $link = rest_url( 'pwa-for-wp/v2/pwa-manifest-json' );
    if($is_amp){
      $link = rest_url( 'pwa-for-wp/v2/pwa-manifest-json/amp' );
    }
  }else{
    $url       = pwaforwp_site_url(); 
    $link = $url.apply_filters('pwaforwp_manifest_file_name', "pwa-manifest".pwaforwp_multisite_postfix().".json");
    if($is_amp){
      $link = $url.apply_filters('pwaforwp_amp_manifest_file_name', "pwa-amp-manifest".pwaforwp_multisite_postfix().".json");
    }
  }
  return $link;
}

add_filter("pwaforwp_file_creation_path", "pwaforwp_check_root_writable", 10, 1);
function pwaforwp_check_root_writable($wppath){
  $uploadArray = wp_upload_dir();
  $uploadBasePath = trailingslashit($uploadArray['basedir']);
  if(!is_writable($wppath) && is_writable(realpath(WP_CONTENT_DIR."/../"))){
      return trailingslashit(realpath(WP_CONTENT_DIR."/../"));
  }
  if(!is_writable($wppath) && is_writable($uploadBasePath)){
    $uploadPwaFolder = "pwaforwp";
    $newpath = $uploadBasePath.$uploadPwaFolder;
    wp_mkdir_p($newpath);
    return trailingslashit($newpath);
  }
  return trailingslashit($wppath);
}

function service_workerUrls($url, $filename){
  $uploadArray    = wp_upload_dir();
  $uploadBasePath = trailingslashit($uploadArray['basedir']);
  $settings = pwaforwp_defaultSettings(); 
  
  $site_url       = pwaforwp_site_url();
  $home_url       = pwaforwp_home_url();  


  if( ( !pwaforwp_is_file_inroot() || $site_url!= $home_url) && !class_exists( 'WPPushnami' ) ){
	  $filename = str_replace(".", "-", $filename);
      $home_url = rtrim($home_url, "/");
      $home_url = add_query_arg(pwaforwp_query_var('sw_query_var'), 1, $home_url);
      $home_url = add_query_arg(pwaforwp_query_var('sw_file_var'), $filename, $home_url);
      $url = $home_url;
  }
  if(isset($settings['serve_js_cache_menthod']) && $settings['serve_js_cache_menthod']=='true'){
    $url = esc_url_raw(admin_url( 'admin-ajax.php?action=pwaforwp_sw_files&'.pwaforwp_query_var('sw_query_var').'=1&'.pwaforwp_query_var('sw_file_var').'='.$filename ));
  }
  return $url;
}

function pwaforwp_is_file_inroot(){
    $wppath = ABSPATH;
    $wppath = apply_filters("pwaforwp_file_creation_path", $wppath);
  if(is_writable($wppath)){
    return true;
  }else{
    return false;
  }
}

/**
* only for Automattic amp Support
* When user enabled Standard & Transitional mode 
* it will check and give respective values
*/

function pwaforwp_is_automattic_amp($case=null){
    //Check if current theme support amp
    switch ($case) {
        case 'amp_support':
            if(class_exists('AMP_Theme_Support')){
                return current_theme_supports( AMP_Theme_Support::SLUG );
            }
            break;
        default:
            if ( current_theme_supports( 'amp' ) && function_exists('is_amp_endpoint') && is_amp_endpoint() ) {
                return true;
            }
            break;
    }
    return false;
}

/**
* PWA WP Enabled
*/
function pwaforwp_is_enabled_pwa_wp(){
    if ( class_exists( 'WP_Service_Workers' ) ) {
        return true;
    }
    return false;
    
}

function ios_splashscreen_files_data(){
    $iosSplashData = array(
            '1136x640'=> array("device-width"=> '320px', "device-height"=> "568px","ratio"=> 2,"orientation"=> "landscape","file"=> "icon_1136x640.png",'name'=> 'iPhone 5/iPhone SE'),
            '640x1136'=> array("device-width"=> '320px', "device-height"=> "568px","ratio"=> 2,"orientation"=> "portrait", "file"=> "icon_640x1136.png",'name'=> 'iPhone 5/iPhone SE'),
            '2688x1242'=>array("device-width"=> '414px', "device-height"=> "896px","ratio"=> 3,"orientation"=> "landscape", "file"=> "icon_2688x1242.png", 'name'=>'iPhone XS Max'),
            '1792x828'=> array("device-width"=> '414px', "device-height"=> "896px","ratio"=> 2, "orientation"=> "landscape", "file"=> "icon_1792x828.png", 'name'=>'iPhone XR'),
            '1125x2436'=>array("device-width"=> '375px', "device-height"=> "812px","ratio"=> 3,"orientation"=> 'portrait', "file"=>"icon_1125x2436.png", 'name'=> 'iPhone X/Xs'),
            '828x1792'=> array("device-width"=> "414px", "device-height"=> "896px","ratio"=> 2,"orientation"=> "portrait","file"=>"icon_828x1792.png",'name' => 'iPhone Xr'),
            '2436x1125'=> array("device-width"=> "375px","device-height"=> "812px","ratio"=> 3,"orientation"=> "landscape", "file"=>"icon_2436x1125.png", 'name'=> 'iPhone X/Xs'),
            '1242x2208'=> array("device-width"=> "414px","device-height"=> "736px","ratio"=> 3,"orientation"=> "portrait", "file"=>"icon_1242x2208.png", 'name'=> 'iPhone 6/7/8 Plus'),
            '2208x1242'=>array("device-width"=> "414px","device-height"=> "736px","ratio"=> 3,"orientation"=> "landscape", "file"=>"icon_2208x1242.png", 'name'=> 'iPhone 6/7/8 Plus'),
            '1334x750'=>array("device-width"=> "375px","device-height"=> "667px","ratio"=> 2,"orientation"=> "landscape", "file"=>"icon_1334x750.png", 'name'=> 'iPhone 6/7/8'),
            '750x1334'=>array("device-width"=> "375px","device-height"=> "667px","ratio"=> 2,"orientation"=> "portrait","file"=>"icon_750x1334.png", 'name'=> 'iPhone 6/7/8'),
            '2732x2048'=>array("device-width"=> "1024px","device-height"=>"1366px","ratio"=> 2,"orientation"=> "landscape","file"=>"icon_2732x2048.png", 'name'=> 'iPad Pro 12.9"'),
            '2048x2732'=>array("device-width"=> "1024px","device-height"=> "1366px","ratio"=> 2,"orientation"=> "portrait","file"=>"icon_2048x2732.png", 'name'=> 'iPad Pro 12.9"'),
            '2388x1668'=>array("device-width"=> "834px","device-height"=> "1194px","ratio"=> 2,"orientation"=> "landscape", "file"=>"icon_2388x1668.png",'name'=> 'iPad Pro 11"'),
            '1668x2388'=>array("device-width"=> "834px","device-height"=> "1194px","ratio"=> 2,"orientation"=> "portrait","file"=>"icon_1668x2388.png",'name'=> 'iPad Pro 11"'),
            '2224x1668'=>array("device-width"=> "834px", "device-height"=> "1112px","ratio"=> 2,"orientation"=>"landscape","file"=>"icon_2224x1668.png", 'name'=> 'iPad Pro 10.5"'),
            '1242x2688'=>array("device-width"=> "414px","device-height"=> "896px","ratio"=> 3, "orientation"=> "portrait","file"=>"icon_1242x2688.png", 'name' => 'iPhone Xs Max'),
            '1668x2224'=>array("device-width"=> "834px","device-height"=> "1112px","ratio"=> 2, "orientation"=> "portrait","file"=>"icon_1668x2224.png", 'name'=> 'iPad Pro 10.5"'),
            '1536x2048'=>array("device-width"=> "768px","device-height"=> "1024px","ratio"=> 2, "orientation"=> "portrait","file"=>"icon_1536x2048.png", 'name'=> 'iPad Mini/iPad Air'),
            '2048x1536'=>array("device-width"=> "768px","device-height"=> "1024px","ratio"=> 2,"orientation"=> "landscape","file"=>"icon_2048x1536.png", 'name'=> 'iPad Mini/iPad Air'),
            );
    return $iosSplashData;
}