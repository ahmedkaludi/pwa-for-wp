<?php            
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
		$link = network_admin_url( 'admin.php?page=' . $page );
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