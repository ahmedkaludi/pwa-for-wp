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
            $color_style = 'border-top-color: '.$color;
        }
        if($bgcolor!=='#ffffff'){ $bg_color_style = 'background-color: '.esc_attr($bgcolor); }
        echo '<div id="pwaforwp_loading_div" style="'.esc_attr($bg_color_style).'"></div>';
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
    load_plugin_textdomain( 'pwa-for-wp', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'pwaforwp_load_plugin_textdomain' );

function pwaforwp_review_notice_close(){    
        if ( ! current_user_can( pwaforwp_current_user_can() ) ) {
            return;
        }
        if ( ! isset( $_POST['pwaforwp_security_nonce'] ) ){
           return; 
        }
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
        if ( !wp_verify_nonce( $_POST['pwaforwp_security_nonce'], 'pwaforwp_ajax_check_nonce' ) ){
           return;  
        }    
       
        $result =  update_option( "pwaforwp_review_notice_bar_close_date", date("Y-m-d"));               
        if($result){           
            echo wp_json_encode(array('status'=>'t'));            
        }else{
            echo wp_json_encode(array('status'=>'f'));            
        }        
        wp_die();           
}

add_action('wp_ajax_pwaforwp_review_notice_remindme', 'pwaforwp_review_notice_remindme');

add_action( 'wp_ajax_pwa_add_media', 'pwa_add_media');

function pwa_add_media() {

	if ( ! isset( $_POST['pwaforwp_security_nonce'] ) ){
        return; 
    }
    if ( !wp_verify_nonce( $_POST['pwaforwp_security_nonce'], 'pwaforwp_ajax_check_nonce' ) ){
        return;  
    } 

	 

	// check for the referrer.
	// check_ajax_referer( 'mpp_add_media' );

	$response = array();

	$file = $_FILES;

	// input file name, set via the mpp.Uploader
	// key name in the files array.
	$file_id = '_mpp_file';

	// find the components we are trying to add for.
	$component    = isset( $_POST['component'] ) ? trim( $_POST['component'] ) : null;
	$component_id = isset( $_POST['component_id'] ) ? absint( $_POST['component_id'] ) : 0;
	$context      = isset( $_POST['context'] ) ? $_POST['context'] : '';

	$context      = mpp_get_upload_context( false, $context );

	if ( ! $component ) {
		$component = mpp_get_current_component();
	}

	if ( ! $component_id ) {
		$component_id = mpp_get_current_component_id();
	}

	// To allow posting on other member's wall, we will need to
	// change the component id to current user id if the context is activity.
	if ( 'activity' === $context && 'members' === $component ) {
		$component_id = get_current_user_id();
	}

	// Check if MediaPress is enabled for this component/component id.
	if ( ! mpp_is_enabled( $component, $component_id ) ) {
		wp_send_json_error( array(
			'message' => __( 'Sorry, the upload functionality is disabled temporarily.', 'mediapress' ),
		) );
	}

	// get the uploader.
	// should we pass the component?
	// should we check for the existence of the default storage method?
	$uploader = mpp_get_storage_manager();

	// check if the server can handle the upload?
	if ( ! $uploader->can_handle() ) {
		wp_send_json_error( array(
			'message' => __( 'Server can not handle this much amount of data. Please upload a smaller file or ask your server administrator to change the settings.', 'mediapress' ),
		) );
	}

	// check if the user has available storage for his profile
	// or the component gallery(component could be groups, sitewide).
	if ( ! mpp_has_available_space( $component, $component_id ) ) {
		wp_send_json_error( array(
			'message' => __( 'Unable to upload. You have used the allowed storage quota!', 'mediapress' ),
		) );
	}
	// if we are here, the server can handle upload.
	$gallery_id = 0;

	if ( isset( $_POST['gallery_id'] ) ) {
		$gallery_id = absint( $_POST['gallery_id'] );
	}

	// did the client send us gallery id? If yes, let us try to fetch the gallery object.
	if ( $gallery_id ) {
		$gallery = mpp_get_gallery( $gallery_id );
	} else {
		// not set.
		$gallery = null;
	}

	// get media type from file extension.
	// print_r(mpp_get_file_extension( $file[ $file_id ]['name'] ));die;
	$media_type = mpp_get_media_type_from_extension( mpp_get_file_extension( $file[ $file_id ]['name'] ) );


	// Invalid media type?
	if ( ! $media_type || ! mpp_component_supports_type( $component, $media_type ) ) {
		wp_send_json_error( array( 'message' => __( 'This file type is not supported.', 'mediapress' ) ) );
	}

	// if there is no gallery type defined.
	// It wil happen in case of new gallery creation from admin page
	// we will set the gallery type as the type of the first media.
	if ( $gallery && empty( $gallery->type ) ) {
		// update gallery type
		// set it to media type.
		mpp_update_gallery_type( $gallery, $media_type );
	}

	// fallback to fetch context based gallery, if gallery is not specified.
	// if there is no gallery id given, we may want to auto create the gallery
	// try fetching the available default gallery for the context.
	if ( ! $gallery ) {
		// try fetching context gallery?
		$gallery = mpp_get_context_gallery( array(
			'component'    => $component,
			'component_id' => $component_id,
			'user_id'      => get_current_user_id(),
			'type'         => $media_type,
			'context'      => $context,
		) );
	}

	if ( ! $gallery ) {
		wp_send_json_error( array( 'message' => __( 'The gallery is not selected.', 'mediapress' ) ) );
	}

	// if we are here, It means we have found a gallery to upload
	// check if gallery has a valid status?
	$is_valid_status = mpp_is_active_status( $gallery->status );
	if ( ! $is_valid_status ) {
		$default_status = mpp_get_default_status();
		// Check and update status if applicable.
		if (  mpp_is_active_status( $default_status ) && mpp_component_supports_status( $component, $default_status ) ) {
			// the current gallery status is invalid,
			// update status to current default privacy.
			mpp_update_gallery_status( $gallery, $default_status );
			$gallery->status = $default_status;
		} else {
			// should we inform user that we can't handle this request due to status issue?
			wp_send_json_error( array( 'message' => __( 'There was a problem with the privacy of your gallery.', 'mediapress' ) ) );
		}
	}

	// we may want to check the upload type and set the gallery to activity gallery etc if it is not set already.
	$error = false;

	// detect media type of uploaded file here and then upload it accordingly.
	// also check if the media type uploaded and the gallery type matches or not.
	// let us build our response for javascript
	// if we are uploading to a gallery, check for type.
	// since we will be allowing upload without gallery too,
	// It is required to make sure $gallery is present or not.
	if ( ! mpp_is_mixed_gallery( $gallery ) && $media_type !== $gallery->type ) {
		// if we are uploading to a gallery and It is not a mixed gallery, the media type must match the gallery type.
		wp_send_json_error( array(
			'message' => sprintf( __( 'This file type is not allowed in current gallery. Only <strong>%s</strong> files are allowed!', 'mediapress' ), mpp_get_allowed_file_extensions_as_string( $gallery->type ) ),
		) );
	}

	// If gallery is given, reset component and component_id to that of gallery's.
	if ( $gallery ) {
		$gallery_id = $gallery->id;
		// reset component and component_id
		// if they are set on gallery.
		if ( ! empty( $gallery->component ) && mpp_is_active_component( $gallery->component ) ) {
			$component = $gallery->component;
		}

		if ( ! empty( $gallery->component_id ) ) {
			$component_id = $gallery->component_id;
		}
	}


	// if we are here, all is well :).
	if ( ! mpp_user_can_upload( $component, $component_id, $gallery ) ) {

		$error_message = apply_filters( 'mpp_upload_permission_denied_message', __( "You don't have sufficient permissions to upload.", 'mediapress' ), $component, $component_id, $gallery );

		wp_send_json_error( array( 'message' => $error_message ) );
	}

	$status = isset( $_POST['media_status'] ) ? $_POST['media_status'] : '';

	if ( empty( $status ) && $gallery ) {
		// inherit from parent,gallery must have an status.
		$status = $gallery->status;
	}

	// we may need some more enhancements here.
	if ( ! $status ) {
		$status = mpp_get_default_status();
	}

	if ( ! mpp_is_active_status( $status ) || ! mpp_component_supports_status( $component, $status ) ) {
		// The status must be valid and supported by current component.
		// else we won't process upload.
		wp_send_json_error( array( 'message' => __( 'There was a problem with the privacy.', 'mediapress' ) ) );
	}

	// if we are here, we have checked for all the basic errors, so let us just upload now.
	$uploaded = $uploader->upload( $file, array(
		'file_id'      => $file_id,
		'gallery_id'   => $gallery_id,
		'component'    => $component,
		'component_id' => $component_id,
	) );

	// upload was successful?
	if ( ! isset( $uploaded['error'] ) ) {

		// file was uploaded successfully.
		if ( apply_filters( 'mpp_use_processed_file_name_as_media_title', false ) ) {
			$title = wp_basename( $uploaded['file'] );
		} else {
			$title = wp_basename( $_FILES[ $file_id ]['name'] );
		}

		$title_parts = pathinfo( $title );
		$title       = trim( substr( $title, 0, - ( 1 + strlen( $title_parts['extension'] ) ) ) );

		$url  = $uploaded['url'];
		$type = $uploaded['type'];
		$file = $uploaded['file'];

		//$title = isset( $_POST['media_title'] ) ? $_POST['media_title'] : '';

		$content = isset( $_POST['media_description'] ) ? $_POST['media_description'] : '';

		$meta = $uploader->get_meta( $uploaded );


		$title_desc = mpp_get_title_desc_from_meta( $type, $meta );

		if ( ! empty( $title_desc ) ) {

			if ( empty( $title ) && ! empty( $title_desc['title'] ) ) {
				$title = $title_desc['title'];
			}

			if ( empty( $content ) && ! empty( $title_desc['content'] ) ) {
				$content = $title_desc['content'];
			}
		}

		$is_orphan = 0;
		// Any media uploaded via activity is marked as orphan
		// Orphan means not associated with the mediapress unless the activity to which it was attached is actually created,
		// check core/activity/actions.php to see how the orphaned media is adopted by the activity :).
		if ( 'activity' === $context ) {
			// by default mark all uploaded media via activity as orphan.
			$is_orphan = 1;
		}

		$media_data = array(
			'title'          => $title,
			'description'    => $content,
			'gallery_id'     => $gallery_id,
			'user_id'        => get_current_user_id(),
			'is_remote'      => false,
			'type'           => $media_type,
			'mime_type'      => $type,
			'src'            => $file,
			'url'            => $url,
			'status'         => $status,
			'comment_status' => 'open',
			'storage_method' => mpp_get_storage_method(),
			'component_id'   => $component_id,
			'component'      => $component,
			'context'        => $context,
			'is_orphan'      => $is_orphan,
		);

		$id = mpp_add_media( $media_data );

		// if the media is not uploaded from activity and auto publishing is not enabled,
		// record as unpublished.
		if (  'activity' !== $context && ! mpp_is_auto_publish_to_activity_enabled( 'add_media' ) ) {
			mpp_gallery_add_unpublished_media( $gallery_id, $id );
		}

		mpp_gallery_increment_media_count( $gallery_id );

		$attachment = mpp_media_to_json( $id );
		echo json_encode( array(
			'success' => true,
			'data'    => $attachment,
		) );

		exit( 0 );
	} else {

		wp_send_json_error( array( 'message' => $uploaded['error'] ) );
	}
}
/*
 *	 REGISTER ALL NON-ADMIN SCRIPTS
 */
function pwaforwp_frontend_enqueue(){
        $force_rememberme=0;
        if ( class_exists('\Elementor\Plugin') && \Elementor\Plugin::$instance->preview->is_preview_mode() ) { return ; }
                               
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
                
                wp_register_script('pwaforwp-js', PWAFORWP_PLUGIN_URL . 'assets/js/pwaforwp.min.js',array(), $force_update_sw_setting_value, true); 
                
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
                
            wp_enqueue_style( 'pwaforwp-style', PWAFORWP_PLUGIN_URL . 'assets/css/pwaforwp-main.min.css', false , $force_update_sw_setting_value );       
            wp_style_add_data( 'pwaforwp-style', 'rtl', 'replace' );

            if (isset($settings['scrollbar_setting']) && $settings['scrollbar_setting']) {
                wp_enqueue_style( 'pwa-scrollbar-css', PWAFORWP_PLUGIN_URL . "assets/css/pwaforwp-scrollbar.css",false,$force_update_sw_setting_value );
            }

        }

        wp_register_script('pwaforwp-video-js', PWAFORWP_PLUGIN_URL . 'assets/js/pwaforwp-video.js',array(), $force_update_sw_setting_value, true); 
        wp_enqueue_script('pwaforwp-video-js');
        
        wp_register_script('pwaforwp-download-js', PWAFORWP_PLUGIN_URL . 'assets/js/pwaforwp-download.js',array(), $force_update_sw_setting_value, true); 
        $object_js_download = array(
            'force_rememberme'=>$force_rememberme
        );
          
        wp_localize_script('pwaforwp-download-js', 'pwaforwp_download_js_obj', $object_js_download);
        wp_enqueue_script('pwaforwp-download-js');
        
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

	if ( !empty($args) ) {
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
        'screenshots'            => PWAFORWP_PLUGIN_URL . 'images/logo-512x512.png',
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
        'offline_page_other'=> '',
        '404_page' 		    => 0,
        '404_page_other'    => '',
        'start_page' 		=> 0,
        'start_page_other' 	=> '',
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
        'is_static_manifest' => 0,

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
        'add_to_home_sticky'=>1,
    /*Share Target*/
        'share_target'      => 0,
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
            if(!file_exists($path) && !is_writable($path) && $content){
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
  if(isset($settings['share_target']) && $settings['share_target']==1){
    // $url = esc_url_raw(admin_url( 'admin-ajax.php?action=pwaforwp_sw_files_share_target&'.pwaforwp_query_var('sw_query_var').'=1&'.pwaforwp_query_var('sw_file_var').'=pwa-sw-js' ));
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

    

    if ($pro_extension_exists && isset( $settings['start_page'] ) && $settings['start_page'] == 'active_url' || (isset( $settings['share_target'] ) && $settings['share_target'] == 1)) {
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

        $current_user = wp_get_current_user();
		$user_login = $current_user->user_nicename;
        if ($user_login) {
            $queryParams['user_path'] = $user_login;
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