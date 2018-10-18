<?php      
/*
 *	 REGISTER ALL NON-ADMIN SCRIPTS
 */
function pwaforwp_frontend_enqueue(){
        
        $multisite_filename_postfix = '';
        if ( is_multisite() ) {
           $multisite_filename_postfix = '-' . get_current_blog_id();
        }          
        $settings = pwaforwp_defaultSettings();
        $server_key = $settings['fcm_server_key'];
        $config = $settings['fcm_config'];
         if($server_key !='' && $config !=''){             
          $swHtmlContent = file_get_contents(PWAFORWP_PLUGIN_DIR."layouts/push-notification-template.js");    
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

function pwaforwp_defaultSettings(){
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
        'cdn_setting'       => 0,
        'normal_enable'       => 1,
        'amp_enable'       => 1,
        'cached_timer'      => array('html'=>3600,'css'=>86400),
        'default_caching'   => 'cacheFirst',
        'default_caching_js_css'   => 'cacheFirst',
        'default_caching_images'   => 'cacheFirst',
        'default_caching_fonts'   => 'cacheFirst',
	);
        
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
            'width'     => array(),
            
    ); 
    //number
    $my_allowed['number'] = array(
            'class'        => array(),
            'id'           => array(),
            'name'         => array(),
            'value'        => array(),
            'type'         => array(),
            'style'        => array(),                    
            'width'     => array(),
            
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
    return esc_url(trailingslashit($link));
}