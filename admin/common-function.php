<?php    
if ( ! defined( 'ABSPATH' ) ) exit;

function pwaforwp_loading_icon() {
    
    if( function_exists('is_amp_endpoint') && is_amp_endpoint() || is_preview() || ( function_exists('is_preview_mode') && is_preview_mode() ) ) { return false; }
    $settings = pwaforwp_defaultSettings();
    if( isset( $settings['loading_icon'] ) && $settings['loading_icon'] == 1 ) {
        $color = ( isset($settings['loading_icon_color'] ) && !empty( $settings['loading_icon_color'] ) ) ? $settings['loading_icon_color'] : '';
        $bgcolor = ( isset($settings['loading_icon_bg_color'] ) && !empty( $settings['loading_icon_bg_color'] ) ) ? $settings['loading_icon_bg_color'] : '';
        $color_style = $bg_color_style = '';
        if( $color ) {
            $color_style = 'border-top-color: '.$color;
        }
        if( $bgcolor !== '#ffffff' ) { $bg_color_style = 'background-color: '.esc_attr( $bgcolor ); }
        echo '<div id="pwaforwp_loading_div" style="'.esc_attr($bg_color_style).'"></div>';
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped	-- output is already escaped
        echo apply_filters('pwaforwp_loading_contents', '<div class="pwaforwp-loading-wrapper"><div id="pwaforwp_loading_icon"  style="'.esc_attr($color_style).'"></div></div>');
    }
        
}
if(!is_admin()){
    add_action('wp_footer', 'pwaforwp_loading_icon');
}

function pwaforwp_reset_all_settings(){ 
    
        if ( ! isset( $_POST['pwaforwp_security_nonce'] ) ){
            return; 
        }
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
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
            
            echo wp_json_encode(array('status'=>'t'));            
        
        }else{
            
            echo wp_json_encode(array('status'=>'f'));            
        
        }        
        wp_die();           
}

add_action('wp_ajax_pwaforwp_reset_all_settings', 'pwaforwp_reset_all_settings');

function pwaforwp_load_plugin_textdomain() {
    load_plugin_textdomain( 'pwa-for-wp', false, basename( dirname( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'pwaforwp_load_plugin_textdomain' );

function pwaforwp_review_notice_close(){    
        if ( ! current_user_can( pwaforwp_current_user_can() ) ) {
            return;
        }
        if ( ! isset( $_POST['pwaforwp_security_nonce'] ) ){
           return; 
        }
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
        if ( !wp_verify_nonce( $_POST['pwaforwp_security_nonce'], 'pwaforwp_ajax_check_nonce' ) ){
           return;  
        }    
       
        $result =  update_option( "pwaforwp_review_never", 'never');               
        if($result){           
        echo wp_json_encode(array('status'=>'t'));            
        }else{
        echo wp_json_encode(array('status'=>'f'));            
        }        
        wp_die();           
}

add_action('wp_ajax_pwaforwp_review_notice_close', 'pwaforwp_review_notice_close');


function pwaforwp_review_notice_remindme(){   
        if ( ! current_user_can( pwaforwp_current_user_can() ) ) {
            return;
        }
        if ( ! isset( $_POST['pwaforwp_security_nonce'] ) ){
            return; 
        }
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
        if ( !wp_verify_nonce( $_POST['pwaforwp_security_nonce'], 'pwaforwp_ajax_check_nonce' ) ){
            return;  
        }

        $result =  update_option( "pwaforwp_review_notice_bar_close_date", gmdate("Y-m-d"));               
        if($result){           
            echo wp_json_encode(array('status'=>'t'));            
        }else{
            echo wp_json_encode(array('status'=>'f'));            
        }        
        wp_die();           
}

add_action('wp_ajax_pwaforwp_review_notice_remindme', 'pwaforwp_review_notice_remindme');

/*
 *	 REGISTER ALL NON-ADMIN SCRIPTS
 */
function pwaforwp_frontend_enqueue(){
        $force_rememberme=0;
        if ( class_exists('\Elementor\Plugin') && \Elementor\Plugin::$instance->preview->is_preview_mode() ) { return ; }
        $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
        $server_key = $config = '';
        
        $settings   = pwaforwp_defaultSettings();

        if(isset($settings['force_update_sw_setting'])){ 
            if(!version_compare($settings['force_update_sw_setting'],PWAFORWP_PLUGIN_VERSION, '>=') ){
                $settings['force_update_sw_setting'] = PWAFORWP_PLUGIN_VERSION;
            }
            $force_update_sw_setting_value = $settings['force_update_sw_setting'];
        }else{ 
            $force_update_sw_setting_value = PWAFORWP_PLUGIN_VERSION;
        }
        
        if(isset($settings['normal_enable']) && $settings['normal_enable']==1){
            
            if(isset($settings['fcm_server_key'])){
                $server_key = $settings['fcm_server_key'];
            }
        
            if(isset($settings['fcm_config'])){
                $config     = $settings['fcm_config'];
            }
                        
            if(isset($settings['notification_feature']) && $settings['notification_feature']==1 && isset($settings['notification_options']) && $settings['notification_options']=='fcm_push' && ($server_key !='' && $config !='')){             
                                                                            
                wp_register_script('pwaforwp-push-js', PWAFORWP_PLUGIN_URL . 'assets/js/pwa-push-notification'.pwaforwp_multisite_postfix().'.js', array('pwa-main-script'), $force_update_sw_setting_value, true);

                $object_name = array(
                    'ajax_url'                  => admin_url( 'admin-ajax.php' ),
                    'pwa_ms_prefix'             => pwaforwp_multisite_postfix(),
                    'pwa_home_url'              => pwaforwp_home_url(), 
                    'pwaforwp_security_nonce'   => wp_create_nonce('pwaforwp_ajax_check_nonce')  
                );

                wp_localize_script('pwaforwp-push-js', 'pwaforwp_obj', $object_name);
                wp_enqueue_script('pwaforwp-push-js');      
                
            }
         
            
            if(isset($settings['force_rememberme']) && $settings['force_rememberme']==1){
                $force_rememberme=1;
            }
            if( (isset($settings['loading_icon']) && $settings['loading_icon']==1) || isset($settings['add_to_home_sticky']) || isset($settings['add_to_home_menu'])){
                
                wp_register_script('pwaforwp-js', PWAFORWP_PLUGIN_URL . 'assets/js/pwaforwp'.$suffix.'.js',array(), $force_update_sw_setting_value, true); 
                
                $loader_desktop = $loader_mobile = $loader_admin = $loader_only_pwa = 0;
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

                //For Admin
                if(isset($settings['loading_icon_display_admin'])):
                    $loader_admin = $settings['loading_icon_display_admin'];
                elseif(isset($settings['loading_icon']) && $settings['loading_icon']==1) ://Falback for old users
                    $loader_admin = 1;
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
                'loader_admin'  => $loader_admin,
                'user_admin'  => is_user_logged_in(),
                'loader_only_pwa'  => $loader_only_pwa,
                'reset_cookies'  => $reset_cookies,
                'force_rememberme'=>$force_rememberme
                );
                
                wp_localize_script('pwaforwp-js', 'pwaforwp_js_obj', $object_js_name);
                
                wp_enqueue_script('pwaforwp-js'); 
                
            }
                
            wp_enqueue_style( 'pwaforwp-style', PWAFORWP_PLUGIN_URL . 'assets/css/pwaforwp-main'.$suffix.'.css', false , $force_update_sw_setting_value );       
            wp_style_add_data( 'pwaforwp-style', 'rtl', 'replace' );

            if (isset($settings['scrollbar_setting']) && $settings['scrollbar_setting']) {
                wp_enqueue_style( 'pwa-scrollbar-css', PWAFORWP_PLUGIN_URL . "assets/css/pwaforwp-scrollbar.css",false,$force_update_sw_setting_value );
            }

        }

        wp_register_script('pwaforwp-video-js', PWAFORWP_PLUGIN_URL . 'assets/js/pwaforwp-video'.$suffix.'.js',array(), $force_update_sw_setting_value, true); 
        wp_enqueue_script('pwaforwp-video-js');
        
        wp_register_script('pwaforwp-download-js', PWAFORWP_PLUGIN_URL . 'assets/js/pwaforwp-download'.$suffix.'.js',array(), $force_update_sw_setting_value, true); 
        $object_js_download = array(
            'force_rememberme'=>$force_rememberme
        );
          
        wp_localize_script('pwaforwp-download-js', 'pwaforwp_download_js_obj', $object_js_download);
        wp_enqueue_script('pwaforwp-download-js');
        
}
add_action( 'wp_enqueue_scripts', 'pwaforwp_frontend_enqueue', 35 );

if ( ! function_exists('pwaforwp_is_admin') ) {
    
	function pwaforwp_is_admin() {
            
		if ( is_admin() ) {
			return true;
		}             
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- we are not processing form here
		if ( isset( $_GET['page'] ) && false !== strpos( $_GET['page'], 'pwaforwp' ) ) {
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

	if ( !empty($args) ) {
		foreach ( $args as $arg => $value ) {
			$link .= '&' . $arg . '=' . urlencode( $value );
		}
	}

	return esc_url($link);
}


function pwaforwp_get_tab( $default = '', $available = array() ) {
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- we are not processing form here
	$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $default;
        
	if ( ! in_array( $tab, $available ) ) {
		$tab = $default;
	}

	return $tab;
}

function pwaforwp_fields_and_type($data_type = 'value'){
    
    $defaults = array(
		'app_blog_name'		     => array('type'=>'text','value'=>get_bloginfo( 'name' )),
		'app_blog_short_name'	 => array('type'=>'text','value'=>get_bloginfo( 'name' )),
		'description'		     => array('type'=>'text','value'=>get_bloginfo( 'description' )),
		'icon'			         => array('type'=>'text','value'=>PWAFORWP_PLUGIN_URL . 'images/logo.png'),
		'app_maskable_icon'			         => array('type'=>'text','value'=>""),
		'splash_icon'		     => array('type'=>'text','value'=>PWAFORWP_PLUGIN_URL . 'images/logo-512x512.png'),
		'splash_maskable_icon'		     => array('type'=>'text','value'=>""),
        //Splash icon
        'switch_apple_splash_screen'=>array('type'=>'checkbox','value'=>0),
        'ios_splash_icon'=> array('type'=>'text','value'=>array(
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
                                                                )),
        'screenshots'            => array('type'=>'text','value'=>PWAFORWP_PLUGIN_URL . 'images/logo-512x512.png'),
        'custom_banner_background_color'=>array('type'=>'text','value'=>'#D5E0EB'),
        'custom_banner_title_color'=>array('type'=>'text','value'=>'#000'),
        'custom_banner_btn_color'=>array('type'=>'text','value'=>'#006dda'),
        'custom_banner_btn_text_color'=>array('type'=>'text','value'=>'#fff'),
        'fcm_push_icon'   => array('type'=>'text','value'=>PWAFORWP_PLUGIN_URL . 'images/logo.png'),
        'background_color' 	=> array('type'=>'text','value'=>'#D5E0EB'),
        'theme_color' 		=> array('type'=>'text','value'=>'#D5E0EB'),
        'start_url' 		=> array('type'=>'text','value'=>0),
        'start_url_amp'		=> array('type'=>'text','value'=>0),
        'offline_page' 		=> array('type'=>'text','value'=>0),
        'offline_page_other'=> array('type'=>'text','value'=>''),
        '404_page' 		    => array('type'=>'text','value'=>0),
        '404_page_other'    => array('type'=>'text','value'=>''),
        'start_page' 		=> array('type'=>'text','value'=>0),
        'start_page_other' 	=> array('type'=>'text','value'=>''),
        'orientation'		=> array('type'=>'text','value'=>'portrait'),
        'display'           => array('type'=>'text','value'=>'standalone'),
        'ios_status_bar'    => array('type'=>'text','value'=>'default'),
        'manualfileSetup'	=> array('type'=>'text','value'=>0),
        'cdn_setting'           => array('type'=>'checkbox','value'=>0),
        'normal_enable'         => array('type'=>'checkbox','value'=>1),
        'amp_enable'            => array('type'=>'checkbox','value'=>1),
        'cached_timer'          => array('type'=>'text','value'=>array('html'=>3600,'css'=>86400)),
        'serve_js_cache_menthod'=> array('type'=>'checkbox','value'=>false),
        'default_caching'       => array('type'=>'text','value'=>'cacheFirst'),
        'default_caching_js_css'=> array('type'=>'text','value'=>'cacheFirst'),
        'default_caching_images'=> array('type'=>'text','value'=>'cacheFirst'),
        'default_caching_fonts' => array('type'=>'text','value'=>'cacheFirst'),
        'on_add_post_notification_title' => array('type'=>'text','value'=>''),
        'is_static_manifest' => array('type'=>'checkbox','value'=>0),

    /*Push notification services*/
        'notification_options'  => array('type'=>'text','value'=>''),
    /*Features settings*/
        'notification_feature'  => array('type'=>'checkbox','value'=>0),
        'precaching_feature'    => array('type'=>'checkbox','value'=>0),
        'addtohomebanner_feature'=> array('type'=>'checkbox','value'=>0),
        'utmtracking_feature'   => array('type'=>'checkbox','value'=>0),
        'loader_feature'         => array('type'=>'checkbox','value'=>0),

    /*UTM*/
        'utm_setting'   => array('type'=>'checkbox','value'=>0),
        'utm_details' => array('type'=>'text','value'=>array(
            'utm_source'=> 'pwa-app',
            'utm_medium'=> 'pwa-app',
            'utm_campaign'=> 'pwa-campaign',
            'utm_term'  => 'pwa-term',
            'utm_content'  => 'pwa-content',
            )),
    /*Pre caching*/
        'precaching_automatic'=> array('type'=>'checkbox','value'=>0),
        'precaching_automatic_post'=> array('type'=>'checkbox','value'=>0),
        'precaching_automatic_page'=> array('type'=>'checkbox','value'=>0),
        'precaching_post_count'=> array('type'=>'number','value'=>5),
        'precaching_automatic_custom_post'=> array('type'=>'checkbox','value'=>0),
        'precaching_manual'    => array('type'=>'checkbox','value'=>0), 
        'precaching_urls'    => array('type'=>'textarea','value'=>''), 
    /*loader icon*/
        'loading_icon'      => array('type'=>'checkbox','value'=>0),
        'loading_icon_color'=> array('type'=>'text','value'=>'#3498db'),
        'loading_icon_bg_color'=> array('type'=>'text','value'=>'#ffffff'),
        'loading_icon_display_admin'=>array('type'=>'checkbox','value'=>0),
        'add_to_home_sticky'=>array('type'=>'checkbox','value'=>1),

        'change_default_on_login'=>array('type'=>'checkbox','value'=>0),
        'force_rememberme'=>array('type'=>'checkbox','value'=>0),
        'offline_message_setting'=>array('type'=>'checkbox','value'=>0),
        'reset_cookies'=>array('type'=>'checkbox','value'=>0),
        'avoid_loggedin_users'=>array('type'=>'checkbox','value'=>0),
        'avoid_default_banner'=>array('type'=>'checkbox','value'=>0),
        'external_links_setting'=>array('type'=>'checkbox','value'=>0),
        'prefetch_manifest_setting'=>array('type'=>'checkbox','value'=>0),
        'offline_google_setting'=>array('type'=>'checkbox','value'=>0),

        'one_signal_support_setting'=>array('type'=>'checkbox','value'=>0),
        'pushnami_support_setting'=>array('type'=>'checkbox','value'=>0),
        'webpusher_support_setting'=>array('type'=>'checkbox','value'=>0),
        'wphide_support_setting'=>array('type'=>'checkbox','value'=>0),

        'pwa_uninstall_data'=>array('type'=>'checkbox','value'=>0),

        'prefer_related_applications'=>array('type'=>'checkbox','value'=>0),

        'excluded_urls'=>array('type'=>'textarea','value'=>''),
        'add_to_home_selector'=>array('type'=>'text','value'=>''),
        'force_update_sw_setting'=>array('type'=>'text','value'=>''),
        'related_applications'=>array('type'=>'text','value'=>''),
        'related_applications_ios'=>array('type'=>'text','value'=>''),
        'screenshots_multiple'=>array('type'=>'text','value'=>''),
        'shortcut'=>array('type'=>'text','value'=>''),
	);
    $response = [];
    switch ($data_type) {
        case 'type':
            foreach ($defaults as $key => $value) {
                $response[$key] = $value['type'];
            }
            break;
        case 'value':
            foreach ($defaults as $key => $value) {
                $response[$key] = $value['value'];
            }
            break;
        
        default:
            $response = $defaults;
            break;
    }
    
    return $response;    
}
function pwaforwp_get_default_settings_array(){
    
    $defaults = pwaforwp_fields_and_type();
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

function pwaforwp_write_a_file( $path, $content, $action = null ) {

        global $wp_filesystem;

        // Initialize the WP_Filesystem global
        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
        }

        WP_Filesystem();

        $writestatus = '';                        
        
        if ( $wp_filesystem->exists( $path ) ) {
            $writestatus =  wp_delete_file( $path );
        }
                
        if ( ! $action ) {
            if ( ! $wp_filesystem->exists( $path ) && $wp_filesystem->is_writable( dirname( $path ) ) && $content ) {
                $writestatus = $wp_filesystem->put_contents( $path, $content, FS_CHMOD_FILE );
            }
        }

        if( $writestatus ){
            return true;   
        }
        return false;   
                
}

function pwaforwp_delete_pwa_files() {
    pwaforwp_required_file_creation( true );
}

function pwaforwp_required_file_creation( $action = null) {
    
    $settings = pwaforwp_defaultSettings(); 

    $server_key = $config = '';       

    $fileCreationInit = new PWAFORWP_File_Creation_Init();

    pwaforwp_onesignal_compatiblity( $action );
    pwaforwp_pushnami()->pushnami_compatiblity( $action ); 
                            
    $status = '';                    
    $status = $fileCreationInit->pwaforwp_swjs_init( $action );
    $status = $fileCreationInit->pwaforwp_manifest_init( $action );
    $status = $fileCreationInit->pwaforwp_swr_init( $action );
    $status = $fileCreationInit->pwaforwp_push_notification_js( $action );
    
    
    if ( function_exists( 'ampforwp_is_amp_endpoint' ) || function_exists( 'is_amp_endpoint' ) ) {
        $status = $fileCreationInit->pwaforwp_swjs_init_amp( $action );
        $status = $fileCreationInit->pwaforwp_manifest_init_amp( $action );
        $status = $fileCreationInit->pwaforwp_swhtml_init_amp( $action );
    }                    
    if ( ! $status ) {
        set_transient( 'pwaforwp_file_change_transient', true );
    }
    
    if ( isset( $settings['fcm_server_key'] ) ) {
            $server_key = $settings['fcm_server_key'];    
    }

    if ( isset( $settings['fcm_config'] ) ) {
        $config = $settings['fcm_config'];   
    }

    if ( $server_key != '' && $config != '' ) {
        $fileCreationInit->pwaforwp_swhtml_init_firebase_js( $action );  
    }
    
}

function pwaforwp_query_var( $key = '' ) {
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

function pwaforwp_check_root_writable( $wppath ) {
    global $wp_filesystem;

    // Initialize the WP_Filesystem global
    if ( ! function_exists( 'WP_Filesystem' ) ) {
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
    }

    WP_Filesystem();

    $uploadArray = wp_upload_dir();
    $uploadBasePath = trailingslashit($uploadArray['basedir']);

    // Check if the path is writable using WP_Filesystem
    if ( ! $wp_filesystem->is_writable( $wppath ) && $wp_filesystem->is_writable( realpath(WP_CONTENT_DIR . "/../") ) ) {
        return trailingslashit( realpath( WP_CONTENT_DIR . "/../" ) );
    }

    if ( ! $wp_filesystem->is_writable( $wppath ) && $wp_filesystem->is_writable( $uploadBasePath ) ) {
        $uploadPwaFolder = "pwaforwp";
        $newpath = $uploadBasePath . $uploadPwaFolder;
        wp_mkdir_p( $newpath );
        return trailingslashit( $newpath );
    }

    return trailingslashit( $wppath );
}


function pwaforwp_service_workerUrls($url, $filename){
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

function pwaforwp_is_file_inroot() {
    $wppath = ABSPATH;
    $wppath = apply_filters( "pwaforwp_file_creation_path", $wppath );

    global $wp_filesystem;

    // Initialize the WP_Filesystem global
    if ( ! function_exists( 'WP_Filesystem' ) ) {
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
    }

    WP_Filesystem();

    if ( $wp_filesystem->is_writable( $wppath ) ) {
        return true;
    }
    return false;
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

function pwaforwp_ios_splashscreen_files_data(){
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
            '1170x2532'=>array("device-width"=> "390px","device-height"=> "844px","ratio"=> 3,"orientation"=> "portrait","file"=>"icon_1170x2532.png", 'name'=> 'iPhone 12/13/14'),
            '2532x1170'=>array("device-width"=> "844px","device-height"=> "390px","ratio"=> 3,"orientation"=> "landscape","file"=>"icon_2532x1170.png", 'name'=> 'iPhone 12/13/14'),
            '2778x1284'=>array("device-width"=> "926px","device-height"=> "428px","ratio"=> 3,"orientation"=> "landscape","file"=>"icon_2778x1284.png", 'name'=> 'iPhone 12 Pro Max/13 Pro Max/14 Plus'),
            '1284x2778'=>array("device-width"=> "428px","device-height"=> "926px","ratio"=> 3,"orientation"=> "portrait","file"=>"icon_2532x1170.png", 'name'=> 'iPhone 12 Pro Max/13 Pro Max/14 Plus'),
            '2556x1179'=>array("device-width"=> "852px","device-height"=> "393px","ratio"=> 3,"orientation"=> "landscape","file"=>"icon_2556x1179.png", 'name'=> 'iPhone 14 Pro'),
            '1179x2556'=>array("device-width"=> "393px","device-height"=> "852px","ratio"=> 3,"orientation"=> "portrait","file"=>"icon_1179x2556.png", 'name'=> 'iPhone 14 Pro'),
            '2796x1290'=>array("device-width"=> "932px","device-height"=> "430px","ratio"=> 3,"orientation"=> "landscape","file"=>"icon_2796x1290.png", 'name'=> 'iPhone 14 Pro Max'),
            '1290x2796'=>array("device-width"=> "430px","device-height"=> "932px","ratio"=> 3,"orientation"=> "portrait","file"=>"icon_1290x2796.png", 'name'=> 'iPhone 14 Pro Max'),
            );
    return $iosSplashData;
}

function pwaforwp_get_user_roles(){
    global $wp_roles;
    $allroles = array();
    if (!empty($wp_roles->roles)) {
        foreach ( $wp_roles->roles as $key=>$value ){
            $allroles[esc_attr($key)] = esc_html($value['name']);
        }
    }
    return $allroles;
}

function pwaforwp_get_capability_by_role($role){
    $cap = apply_filters('pwaforwp_default_manage_option_capability', 'manage_options' );
    switch ($role) {
        case 'wpseo_editor':
            $cap = 'edit_pages';                
            break;                  
        case 'editor':
            $cap = 'edit_pages';                
            break;            
        case 'author':
            $cap = 'publish_posts';                
            break;
        case 'contributor':
            $cap = 'edit_posts';                
            break;
        case 'wpseo_manager':
            $cap = 'edit_posts';                
            break;
        case 'subscriber':
            $cap = 'read';                
            break;
        default:
            break;
    }
    return $cap;

}

function pwaforwp_current_user_allowed(){
    $currentuserrole = array();
    if( ( function_exists('is_user_logged_in') && is_user_logged_in() )  && function_exists('wp_get_current_user') ) {
        $settings       = pwaforwp_defaultSettings();
        $currentUser    = wp_get_current_user();        
        $pwaforwp_roles = isset($settings['pwaforwp_role_based_access']) ? $settings['pwaforwp_role_based_access'] : array('administrator');
        if($currentUser){
            if($currentUser->roles){
                $currentuserrole = (array) $currentUser->roles;
            }else{
                if( isset($currentUser->caps['administrator']) ){
                    $currentuserrole = array('administrator');
                }	
            }
            if( is_array($currentuserrole) ){
                $hasrole         = array_intersect( $currentuserrole, $pwaforwp_roles );
                if( !empty($hasrole)){                                     
                    return reset($hasrole);
                }
            }
        }       
    }
    return false;
}

function pwaforwp_current_user_can(){
    $capability = pwaforwp_current_user_allowed() ? pwaforwp_get_capability_by_role(pwaforwp_current_user_allowed()) : 'manage_options';
    return $capability;                    
}



// Function to check if any plugin from the extension is active
function pwaforwp_is_any_extension_active() {
    $addons_list = array('call-to-action-for-pwa/call-to-action-for-pwa.php', 'buddypress-for-pwaforwp/buddypress-for-pwaforwp.php', 'data-analytics-for-pwa/data-analytics-for-pwa.php', 'loading-icon-library-for-pwa/loading-icon-library-for-pwa.php', 'multilingual-compatibility-for-pwa/multilingual-compatibility-for-pwa.php', 'navigation-bar-for-pwa/navigation-bar-for-pwa.php', 'offline-forms-for-pwa-for-wp/offline-forms-for-pwa-for-wp.php', 'pull-to-refresh-for-pwa/pull-to-refresh-for-pwa.php', 'pwa-to-apk-plugin/pwa-to-apk-plugin.php', 'qr-code-for-pwa/qr-code-for-pwa.php','quick-action-for-pwa/quick-action-for-pwa.php','scroll-progress-bar-for-pwa/scroll-progress-bar-for-pwa.php','rewards-on-pwa-install/rewards-on-pwa-install.php');
    $active_list = apply_filters('active_plugins', get_option('active_plugins'));
    $addons_active_list = array_intersect($addons_list, $active_list);

    if(!empty($addons_active_list)){
        return true;
    }
    return false; // None of the plugins from the list are active
}

/*
*@package PWAforWP
*@version 1.7.67
*@description update icon urls in manhifest when WP Hide & Security Enhancer is used
* https://wp-hide.com/
*/
add_filter('pwaforwp_manifest_images_src','pwaforwp_manifest_images_src',10,1);
function pwaforwp_manifest_images_src($src){
	// if WP Hide & Security Enhancer is active 
    $settings       = pwaforwp_defaultSettings();
	if(class_exists('WPH') && !empty($settings['wphide_support_setting']) && $settings['wphide_support_setting'] ==1){
        $pwafrowp_wph = get_option('wph_settings', false );
        if( $pwafrowp_wph && !empty($pwafrowp_wph['module_settings']['new_upload_path'])){
            $new_url =$pwafrowp_wph['module_settings']['new_upload_path'];
            $src = str_replace('wp-content/uploads', $new_url, $src);
        }
        if( $pwafrowp_wph && !empty($pwafrowp_wph['module_settings']['new_plugin_path'])){
            $new_url =$pwafrowp_wph['module_settings']['new_plugin_path'];
            $src = str_replace('wp-content/plugins', $new_url, $src);
        }
	}
	return $src;
}

function pwaforwp_get_manifest_filename() {
    return 'pwa-manifest' . pwaforwp_multisite_postfix() . '.json';
}

function pwaforwp_manifest_url( $arg = 'src' ) {
    $manifest_filename = pwaforwp_get_manifest_filename();

    switch ( $arg ) {
        case 'filename':
            return $manifest_filename;
            break;

        
        case 'abs':
            $filepath = trailingslashit( ABSPATH ) . $manifest_filename;
            if(!file_exists($filepath)){
                $filepath = trailingslashit( get_home_path() ). $manifest_filename;
            }
            return $filepath;
            break;

        // Link to manifest
        case 'src':
        default:
        
            // Get Settings
            $settings = pwaforwp_defaultSettings();
            if ( $settings['is_static_manifest'] === 1 ) {
                return trailingslashit( network_site_url() ) . $manifest_filename;
            }
            return home_url( '/' ) . $manifest_filename;
            
            // For dynamic files, return the home_url
            
            
            break;
    }
}

function pwaforwp_add_manifest_variables($url) {
    $settings = pwaforwp_defaultSettings();

    $pro_extension_exists = function_exists('pwaforwp_is_any_extension_active')?pwaforwp_is_any_extension_active():false;

    

    if ($pro_extension_exists && isset( $settings['start_page'] ) && $settings['start_page'] == 'active_url') {
        $parsedUrl = parse_url( $url );
        global $post;
        $cache_version = PWAFORWP_PLUGIN_VERSION;
        if(isset($settings['force_update_sw_setting']) && $settings['force_update_sw_setting'] !=''){
            $cache_version =   $settings['force_update_sw_setting'];
            if(!version_compare($cache_version,PWAFORWP_PLUGIN_VERSION, '>=') ){
                $cache_version = PWAFORWP_PLUGIN_VERSION;
            }
        }
        // Extract the query string parameters
        $queryParams = [];
        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $queryParams);
        }
    
        if (!isset($queryParams['pwaforwp_mid']) && isset($post->ID)) {
            $queryParams['pwaforwp_mid'] = $post->ID;
        }
        if (!isset($queryParams['v'])) {
            $queryParams['v'] = $cache_version;
        }
    
        // Rebuild the query string
        $newQueryString = http_build_query($queryParams);
    
        if (isset($parsedUrl['path'])) {
            $newUrl = $parsedUrl['path'] . '?' . $newQueryString;
        } else {
            $newUrl = '?' . $newQueryString;
        }	
        return $newUrl;
    }
    return parse_url( $url, PHP_URL_PATH ) ;
}

function pwaforwp_visibility_get_data_by_type($type,$from){
    $response_array = array();
    global $pwaforwp_settings;
    $settings = $pwaforwp_settings;
    if((isset($settings['include_targeting_value']) && isset($settings['include_targeting_type'])) || (isset($settings['exclude_targeting_value']) && isset($settings['exclude_targeting_type']))){
        $expo_include_type = array();
        $expo_include_data = array();

        if (!empty($settings['include_targeting_type'])) {
            $expo_include_type = explode(',', $settings['include_targeting_type']);
        }
        if (!empty($settings['include_targeting_value'])) {
            $expo_include_data = explode(',', $settings['include_targeting_value']);
        }

        
        $expo_exclude_type = array();
        $expo_exclude_data = array();
        if (!empty($settings['exclude_targeting_type'])) {
            $expo_exclude_type = explode(',', $settings['exclude_targeting_type']);
        }

        if (!empty($settings['exclude_targeting_value'])) {
            $expo_exclude_data = explode(',', $settings['exclude_targeting_value']);
        }

        if ($from == 'excluding') {
            foreach ($expo_exclude_type as $key => $value) {
                if ($value == $type) {
                    $response_array[$key] = $expo_exclude_data[$key];
                }
            }
        }else{
            foreach ($expo_include_type as $key => $value) {
                if ($value == $type) {
                    $response_array[$key] = $expo_include_data[$key];
                }
            }
        }
    }

    return $response_array;

}

function pwaforwp_visibility_check(){
    global $pwaforwp_settings;
    $settings = $pwaforwp_settings;
    if((isset($settings['include_targeting_value']) && isset($settings['include_targeting_type'])  && !empty($settings['include_targeting_type'])) || (isset($settings['exclude_targeting_value']) && isset($settings['exclude_targeting_type']) && !empty($settings['exclude_targeting_type']))){
        $expo_include_type = array();
        $expo_include_data = array();

        if (!empty($settings['include_targeting_type'])) {
            $expo_include_type = explode(',', $settings['include_targeting_type']);
        }
        if (!empty($settings['include_targeting_value'])) {
            $expo_include_data = explode(',', $settings['include_targeting_value']);
        }

        $expo_exclude_type = array();
        $expo_exclude_data = array();
        if (!empty($settings['exclude_targeting_type'])) {
            $expo_exclude_type = explode(',', $settings['exclude_targeting_type']);
        }

        if (!empty($settings['exclude_targeting_value'])) {
            $expo_exclude_data = explode(',', $settings['exclude_targeting_value']);
        }

        $current_page_type = '';
        if(is_singular() && !is_front_page()){
            $current_page_type = get_post_type();
        }

        $current_page_title = single_post_title("",false);

        if ( empty($current_page_type) ) {
            global $wp;
            $slug = $wp->request;
            $q = get_page_by_path( $slug, OBJECT, array( 'post', 'page' ) );
            if ( ! empty($q) && isset($q->post_type) ) {
                $current_page_type = $q->post_type;
                $current_page_title = $q->post_title;
            }
        }

        $is_desplay = 0;

        if(!empty(get_the_category()[0]->cat_name)){
            if(in_array(get_the_category()[0]->cat_name,$expo_include_data)){
                $current_page_type= 'post_category';
                $current_page_title =  get_the_category()[0]->cat_name;
            }
        }
        
        if(in_array('tags',$expo_include_type)){
            $tag = get_queried_object();
            if(in_array($tag->name,$expo_include_data)){
                $current_page_title =  $tag->name;
                $current_page_type = 'tags';
            }
        }

        if(in_array('taxonomy',$expo_include_type)){
            $tag = get_queried_object();
            if(in_array($tag->name,$expo_include_data)){
                $current_page_title =  $tag->name;
                $current_page_type = 'taxonomy';
            }
        }

        if(in_array('page_template',$expo_include_type)){
            $page_template = wp_get_theme()->get_page_templates();
            if(!empty($page_template) && is_array($page_template)){
            foreach ($page_template as $key => $value) {
                if(in_array($value,$expo_include_data)){
                    $current_page_title =  $value;
                    $current_page_type = 'page_template';
                }
            }}
        }

        if(function_exists('is_user_logged_in') && is_user_logged_in() ) {
            $user = wp_get_current_user();
            if(in_array($user->roles,$expo_include_data)){
                $current_page_title =  $user->roles;
                $current_page_type = 'user_type';
            }
        }
               
        if(in_array($current_page_type,$expo_include_type)){
            $find_from = pwaforwp_visibility_get_data_by_type($current_page_type,'including');
            if (in_array($current_page_title, $find_from)) {
                $is_desplay = 1; 
            }
        }

        if (in_array('post_type',$expo_include_type)) {
            $find_from = pwaforwp_visibility_get_data_by_type('post_type','including');
            if(in_array($current_page_type,$find_from)){
                    $is_desplay = 1;
            }
        }

        if(in_array('globally',$expo_include_type)){
            $is_desplay = 1; 
        }
        /*Include code end */

        /*Exldude code start*/
    
        if(in_array('tags',$expo_exclude_type)){
            $tag = get_queried_object();
            if(in_array($tag->name,$expo_exclude_data)){
                $current_page_title =  $tag->name;
                $current_page_type = 'tags';
            }  
        }

        if(in_array('taxonomy',$expo_exclude_type)){
            $tag = get_queried_object();
            if(in_array($tag->name,$expo_exclude_data)){
                $current_page_title =  $tag->name;
                $current_page_type = 'taxonomy';
            }
        }

        if(in_array('page_template',$expo_exclude_type)){
            $page_template = wp_get_theme()->get_page_templates();
            if(!empty($page_template) && is_array($page_template)){
            foreach ($page_template as $key => $value) {
                if(in_array($value,$expo_exclude_data)){
                    $current_page_title =  $value;
                    $current_page_type = 'page_template';
                }
            }}
        }

        if(function_exists('is_user_logged_in') && is_user_logged_in() ) {
            $user = wp_get_current_user();
            if(in_array($user->roles,$expo_exclude_data)){
                $current_page_title =  $user->roles;
                $current_page_type = 'user_type';
            }
        }
        if(in_array($current_page_type,$expo_exclude_type)){
            $find_from = pwaforwp_visibility_get_data_by_type($current_page_type,'excluding');
            if (in_array($current_page_title, $find_from)) {
                $is_desplay = 0; 
            }
        }

        if (in_array('post_type',$expo_exclude_type)) {
            $find_from = pwaforwp_visibility_get_data_by_type('post_type','excluding');
            if(in_array($current_page_type,$find_from)){
                $is_desplay = 0;
            }
        }
        if(in_array('globally',$expo_exclude_type)){
            $is_desplay = 0; 
        }
        return $is_desplay;

    }else{
        return 1;
    }
}

add_filter('pre_update_option_pwaforwp_settings', 'pwaforwp_fix_empty_option_update', 10, 3);

/**
 * Prevents empty values from overwriting existing ones in the pwaforwp_settings option.
 * Special case only for visibility settings.
 *
 * @param mixed $new_value The new value of the option.
 * @param mixed $old_value The old value of the option.
 * @param string $option The name of the option.
 * @return mixed The updated option value.
 */
function pwaforwp_fix_empty_option_update($new_value, $old_value, $option) {

    if (!empty($old_value['include_targeting_type']) && empty($new_value['include_targeting_type'])) {
        $new_value['include_targeting_type'] = $old_value['include_targeting_type'];
    }

    if (!empty($old_value['include_targeting_value']) && empty($new_value['include_targeting_value'])) {
        $new_value['include_targeting_value'] = $old_value['include_targeting_value'];
    }

    if (!empty($old_value['exclude_targeting_type']) && empty($new_value['exclude_targeting_type'])) {
        $new_value['exclude_targeting_type'] = $old_value['exclude_targeting_type'];
    }

    if (!empty($old_value['exclude_targeting_value']) && empty($new_value['exclude_targeting_value'])) {
        $new_value['exclude_targeting_value'] = $old_value['exclude_targeting_value'];
    }

    return $new_value;
}

function pwaforwp_local_file_get_contents( $file_path ) {

    // Include WordPress Filesystem API
    if ( ! function_exists( 'WP_Filesystem' ) ) {
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
    }
    // Initialize the API
    global $wp_filesystem;
    if ( ! WP_Filesystem() ) {
        WP_Filesystem();
    }
    // Check if the file exists
    if ( $wp_filesystem->exists( $file_path ) ) {
        return $wp_filesystem->get_contents( $file_path );
    }
    return esc_html__( 'File does not exist.', "pwa-for-wp" );

}
