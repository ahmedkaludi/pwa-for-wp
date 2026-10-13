<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class PWAFORWP_Push_Notification {
            
     public function pwaforwp_push_notification_hooks() {

        // Always harden/migrate FCM credentials even if push is currently disabled.
        add_action( 'admin_init', array( $this, 'pwaforwp_maybe_secure_fcm_credentials' ) );

        $pwaSettings = pwaforwp_defaultSettings();
        $showFirebase = true;
        $showFirebase = apply_filters("pwaforwp_show_pwa_firebase", $showFirebase);        
        
        if( $pwaSettings['notification_feature']==1 && isset($pwaSettings['notification_options']) && $pwaSettings['notification_options']=='fcm_push' && $showFirebase ) {
          
            add_action('transition_post_status', array($this, 'pwaforwp_send_notification_on_post_save'), 10, 3);                          
            add_filter('pwaforwp_manifest', array($this, 'pwaforwp_load_pn_manifest'), 35); 
            add_action('wp_enqueue_scripts', array($this, 'pwaforwp_load_pn_script_add'), 34);
            add_action('wp_ajax_nopriv_pwaforwp_store_token', array($this,'pwaforwp_store_token')); 
            add_action('wp_ajax_pwaforwp_store_token', array($this, 'pwaforwp_store_token'));             
            add_action('wp_ajax_pwaforwp_send_notification_manually', array($this, 'pwaforwp_send_notification_manually'));
            add_action('wp_ajax_pwaforwp_upload_fcm_json', array($this, 'pwaforwp_upload_fcm_json'));
        }
                           
     }

     public function pwaforwp_send_notification_manually() {
      
            if ( ! current_user_can( pwaforwp_current_user_can() ) ) {
              return;
            }              
         
            if ( ! isset( $_POST['pwaforwp_security_nonce'] ) ){
                return; 
            }
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            if ( !wp_verify_nonce( $_POST['pwaforwp_security_nonce'], 'pwaforwp_ajax_check_nonce' ) ){
              return;  
            }
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated, WordPress.Security.ValidatedSanitizedInput.MissingUnslash     
            $body             = sanitize_textarea_field($_POST['message']);
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated, WordPress.Security.ValidatedSanitizedInput.MissingUnslash                      
            $message['title'] = sanitize_text_field($_POST['title']);
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated, WordPress.Security.ValidatedSanitizedInput.MissingUnslash 
            $url              = sanitize_url($_POST['url']);
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated, WordPress.Security.ValidatedSanitizedInput.MissingUnslash 
            $image_url        = sanitize_url($_POST['image_url']);
            $message['body']  = $body;
            $message['url']   = (!empty($url)? $url : site_url());
            $message['image_url']   = (!empty($image_url)? $image_url : '');
                        
            $result           = $this->pwaforwp_send_push_notification($message); 
            
            $result = json_decode($result, true);                 

            if ( ! empty( $result ) && isset( $result['success'] ) && $result['success'] != 0 ) {             

            echo wp_json_encode( array( 'status'=>'t', 'success'=> $result['message'], 'failure'=> $result['failure'] ) );    

            } else {

            echo wp_json_encode( array( 'status'=> 'f', 'mesg' => esc_html__( 'Notification not sent. Something went wrong','pwa-for-wp' ), 'result' => $result ) );    

           }

           wp_die();
     }
     
     public function pwaforwp_send_notification_on_post_save($new_status, $old_status, $post){
            if ( 'publish' !== $new_status ){
                  return;
            }
            //global $post;              
            $settings = pwaforwp_defaultSettings();  
            $message  = array();
            if(is_object($post)){
                switch ($post->post_type) {
                case 'post':
                    
                  $send_notification = false;
                  //for Edit
                  if(isset($settings['on_update_post']) && $settings['on_update_post']==1){
                    if ( $new_status === $old_status) {
                      $message['title'] = isset($settings['on_update_post_notification_title']) && !empty($settings['on_update_post_notification_title'])? $settings['on_update_post_notification_title']: esc_html__('Post Updated', 'pwa-for-wp'); 
                      $message['body']  = get_the_title($post)."\n".get_permalink ($post);
                      $message['url']   = get_permalink ($post);
                      $image_url = '';
                      if(has_post_thumbnail($post)){
                        $image_url = esc_url_raw(get_the_post_thumbnail_url($post));
                      }
                      $message['image_url']   =  $image_url;
                      $this->pwaforwp_send_push_notification($message);
                      $send_notification = true;
                    }
                  }
                  //for publish
                  if(!$send_notification && isset($settings['on_add_post']) && $settings['on_add_post']==1){
                    if ( $new_status !== $old_status) {
                      $message['title'] = isset($settings['on_add_post_notification_title']) && !empty($settings['on_add_post_notification_title'])? $settings['on_add_post_notification_title']: esc_html__('New Post', 'pwa-for-wp'); 
                      $message['body']  = get_the_title($post)."\n".get_permalink ($post);
                      $message['url']   = get_permalink ($post);
                      $image_url = '';
                      if(has_post_thumbnail($post)){
                        $image_url = esc_url_raw(get_the_post_thumbnail_url($post));
                      }
                      $message['image_url']   =  $image_url;
                      $this->pwaforwp_send_push_notification($message);
                    }
                  }
                    break;
                case 'page':

                  $send_notification = false;
                  //for Edit
                  if(isset($settings['on_update_page']) && $settings['on_update_page']==1){
                    if ( $new_status === $old_status) {
                      $message['title'] = isset($settings['on_update_page_notification_title']) && !empty($settings['on_update_page_notification_title'])? $settings['on_update_page_notification_title']: esc_html__('Page Updated', 'pwa-for-wp'); 
                      $message['body']  = get_the_title($post)."\n".get_permalink ($post);
                      $message['url']   = get_permalink ($post);
                      $image_url = '';
                      if(has_post_thumbnail($post)){
                        $image_url = esc_url_raw(get_the_post_thumbnail_url($post));
                      }
                      $message['image_url']   =  $image_url;
                      $this->pwaforwp_send_push_notification($message);
                      $send_notification = true;
                    }
                  }
                  //for publish
                  if(!$send_notification && isset($settings['on_add_page']) && $settings['on_add_page']==1){
                    if ( $new_status !== $old_status) {
                      $message['title'] = isset($settings['on_add_page_notification_title']) && !empty($settings['on_add_page_notification_title'])? $settings['on_add_page_notification_title']: esc_html__('New Page', 'pwa-for-wp'); 
                      $message['body']  = get_the_title($post)."\n".get_permalink ($post);
                      $message['url']   = get_permalink ($post);
                      $image_url = '';
                      if(has_post_thumbnail($post)){
                        $image_url = esc_url_raw(get_the_post_thumbnail_url($post));
                      }
                      $message['image_url']   =  $image_url;
                      $this->pwaforwp_send_push_notification($message);
                    }
                  }
                    break;

                default:
                    break;
            }
            }
            
                              
     }
     
     public function pwaforwp_load_pn_manifest($manifest){	
            $settings     = pwaforwp_defaultSettings();
            $server_key = $config = '';
            
            if(isset($settings['fcm_server_key'])){
                $server_key   = $settings['fcm_server_key'];
            }
            if(isset($settings['fcm_config'])){
                $config       = $settings['fcm_config'];
            }                        
                                                              
            if(!empty($server_key) && !empty($config) && isset($settings['normal_enable']) && $settings['normal_enable']==1){	             
                $manifest['gcm_sender_id'] = '103953800507';
            }
          return $manifest;

     }  
     public function pwaforwp_load_pn_script_add(){  
         
            $url    = pwaforwp_home_url();
            $settings     = pwaforwp_defaultSettings();      
            
            $server_key = $config = '';
            
            if(isset($settings['fcm_server_key'])){
                $server_key   = $settings['fcm_server_key'];
            }
            if(isset($settings['fcm_config'])){
                $config       = $settings['fcm_config'];
            }                        
                                                              
            if( isset($settings['notification_feature']) && $settings['notification_feature']==1 && !empty($server_key) && !empty($config) && isset($settings['normal_enable']) && $settings['normal_enable']==1 ){
                wp_register_script( "pwa-main-firebase-script", esc_url(PWAFORWP_PLUGIN_URL.'assets/vendor/js/firebase-app.min.js'), array(), PWAFORWP_PLUGIN_VERSION, true );
                wp_register_script( "pwa-main-firebase-message-script", esc_url(PWAFORWP_PLUGIN_URL.'assets/vendor/js/firebase-messaging.min.js'), array('pwa-main-firebase-script'), PWAFORWP_PLUGIN_VERSION, true );

             
                wp_enqueue_script( "pwa-main-firebase-script"); 
                wp_enqueue_script( "pwa-main-firebase-message-script"); 
            }                    
     }         
     public function pwaforwp_store_token(){
                     
            if ( ! isset( $_POST['pwaforwp_security_nonce'] ) ){
                return; 
            }
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash 
            if ( !wp_verify_nonce( $_POST['pwaforwp_security_nonce'], 'pwaforwp_ajax_check_nonce' ) ){
              return;  
            }
            $get_token_list = array();  
            $result         = false;
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated, WordPress.Security.ValidatedSanitizedInput.MissingUnslash 
            $token          = sanitize_text_field($_POST['token']);             
            
            if($token){
                do_action('pwaforwp_before_save_token_action', $token);
                $get_token_list = (array)json_decode(get_option('pwa_token_list'), true);               
                array_push($get_token_list, $token);                
                $result = update_option('pwa_token_list', wp_json_encode($get_token_list));
                
            } 
            
            if($result){
                echo wp_json_encode(array('status'=>'t', 'mesg'=> esc_html__('Token Saved Successfully','pwa-for-wp')));    
            }else{
                echo wp_json_encode(array('status'=>'f', 'mesg'=> esc_html__('Token Not Saved','pwa-for-wp')));    
            }
             wp_die();
      }
protected function pwaforwp_send_push_notification($message) {
    $settings   = pwaforwp_defaultSettings();
    $service_account_key = $this->pwaforwp_read_json_file($settings['fcm_server_key']);
    $firebase_config = $settings['fcm_config'];

    // Fix unquoted keys in firebase_config JSON string
    $firebase_config = preg_replace_callback( '/([{,]\s*)([a-zA-Z0-9_]+)\s*:/', function ( $matches ) {
        return $matches[1] . '"' . $matches[2] . '":';
    }, $firebase_config );

    $firebase_config =  json_decode($firebase_config, true);

    if ( empty( $service_account_key ) || empty( $firebase_config ) ) {
        return json_encode([
            'success' => 0,
            'message' => esc_html__('Service Account Key or Firebase Config is missing', 'pwa-for-wp')
        ]);
    }

    $project_id = $firebase_config['projectId'];
    $tokens     = (array) json_decode(get_option('pwa_token_list'), true);

    if ( empty( $tokens ) || empty( $service_account_key ) ) {
        return json_encode([
            'success' => 0,
            'message' => esc_html__('Tokens or Service Account Key are missing', 'pwa-for-wp')
        ]);
    }

    if ( empty( $project_id ) ) {
        return json_encode([
            'success' => 0,
            'message' => esc_html__('Project ID is missing', 'pwa-for-wp')
        ]);
    }

    // Get access token using service account key
    $access_token = $this->pwaforwp_get_access_token( $service_account_key );

    if ( empty( $access_token ) ) {
        return json_encode([
            'success' => 0,
            'message' => esc_html__('Access token could not be generated', 'pwa-for-wp')
        ]);
    }

   $header = [
    'Authorization' => 'Bearer ' . $access_token,
    'Content-Type'  => 'application/json'
  ];

    $msg = [
        'title'        => $message['title'],
        'body'         => $message['body'],
        'image'        => isset($message['image_url']) ? $message['image_url'] : '',
    ];

       $data = [
        'title'        => $message['title'],
        'body'         => $message['body'],
        'icon'         => isset( $settings['fcm_push_icon'] ) ? esc_attr($settings['fcm_push_icon']) : PWAFORWP_PLUGIN_URL . '/images/notification_icon.jpg',
        'badge'        => isset( $settings['fcm_budge_push_icon'] ) ? esc_attr($settings['fcm_budge_push_icon']) : PWAFORWP_PLUGIN_URL . '/images/notification_icon.jpg',
        'url' => $message['url'],
        'image'        => isset( $message['image_url'] ) ? $message['image_url'] : '',
    ];
    $successCount = 0;
    $errors = [];

    foreach ( $tokens as $token ) {
        $payload = [
            'message' => [
                'token'        => $token,
                'notification' => $msg,
                'data'         => $data,
                'webpush'      => [
                    'fcm_options' => [
                        'link' => $message['url'],
                    ],
                ],
            ]
        ];

        $args = [
            'body'      => wp_json_encode( $payload ),
            'timeout'   => 15,
            'headers'   => $header,
            'sslverify' => false,
        ];

        $response = wp_remote_post( 'https://fcm.googleapis.com/v1/projects/' . $project_id . '/messages:send', $args);


        if (is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
            if (  is_wp_error( $response ) ) {
                $errors[] = $response->get_error_message();
            } else {
                $errors[] = 'HTTP error code: ' . wp_remote_retrieve_response_code( $response );
            }
            continue;
        }

        $body = json_decode(wp_remote_retrieve_body( $response ), true );
        if ( isset( $body['name'] ) ) {
            $successCount++;
        } else {
            $errors[] = 'Unknown error sending to token: ' . $token;
        }
    }

    if ( $successCount > 0 ) {
        return json_encode([
            'success' => 1,
            'message' => esc_html__("Notifications sent successfully to ", 'pwa-for-wp') . esc_html( $successCount ) . esc_html__(" device(s).", 'pwa-for-wp'),
            'errors'  => $errors,
        ]);
    } else {
        return json_encode([
            'success' => 0,
            'message' => esc_html__('Failed to send notifications.', 'pwa-for-wp'),
            'errors'  => $errors,
        ]);
    }
}

// Helper function for URL-safe Base64 encoding used in JWT
private function base64url_encode( $data ) {
    return rtrim(strtr(base64_encode($data ), '+/', '-_'), '=');
}

private function pwaforwp_get_access_token( $service_account_key ) {
    $client_email = $service_account_key['client_email'];
    $private_key  = $service_account_key['private_key'];

    $header = ['alg' => 'RS256', 'typ' => 'JWT'];
    $payload = [
        'iss'   => $client_email,
        'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        'aud'   => 'https://oauth2.googleapis.com/token',
        'exp'   => time() + 3600,
        'iat'   => time(),
    ];

    $jwtHeader  = $this->base64url_encode( json_encode( $header ) );
    $jwtPayload = $this->base64url_encode( json_encode( $payload ) );
    $data       = $jwtHeader . '.' . $jwtPayload;

    openssl_sign($data, $signature, $private_key, OPENSSL_ALGO_SHA256);
    $jwtSignature = $this->base64url_encode( $signature );

    $jwt = $data . '.' . $jwtSignature;

    $args = [
        'body' => [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => $jwt,
        ],
    ];

    $response = wp_remote_post( 'https://oauth2.googleapis.com/token', $args );
    $body = json_decode(wp_remote_retrieve_body($response), true);

    return $body['access_token'] ?? '';
}

/**
 * Returns the non-public directory used for FCM service-account JSON.
 *
 * Stored under WP_CONTENT_DIR (outside uploads) so it is not treated as a
 * media file and can be blocked from HTTP access.
 *
 * @return string Absolute path with trailing slash.
 */
public function pwaforwp_get_fcm_private_dir() {
	return trailingslashit( wp_normalize_path( WP_CONTENT_DIR ) ) . 'pwaforwp-private/';
}

/**
 * Legacy publicly served directory used before the security fix.
 *
 * @return string Absolute path with trailing slash.
 */
public function pwaforwp_get_legacy_fcm_private_dir() {
	$upload_dir = wp_upload_dir();
	return trailingslashit( wp_normalize_path( $upload_dir['basedir'] ) ) . 'pwaforwp-private/';
}

/**
 * Ensures the private directory exists and cannot be served over HTTP.
 *
 * @param string $dir Absolute directory path.
 * @return bool True when the directory is ready.
 */
public function pwaforwp_ensure_protected_private_dir( $dir ) {
	$dir = trailingslashit( wp_normalize_path( $dir ) );

	if ( ! file_exists( $dir ) && ! wp_mkdir_p( $dir ) ) {
		return false;
	}

	$this->pwaforwp_protect_private_dir( $dir );
	return true;
}

/**
 * Writes deny-all web server rules and an index stub for a private directory.
 *
 * @param string $dir Absolute directory path.
 * @return void
 */
public function pwaforwp_protect_private_dir( $dir ) {
	$dir = trailingslashit( wp_normalize_path( $dir ) );

	if ( ! function_exists( 'WP_Filesystem' ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
	}

	global $wp_filesystem;
	if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base' ) ) {
		WP_Filesystem();
	}
	if ( ! $wp_filesystem ) {
		return;
	}

	$htaccess = $dir . '.htaccess';
	if ( ! $wp_filesystem->exists( $htaccess ) ) {
		$rules  = "# Deny all HTTP access to FCM credentials\n";
		$rules .= "<IfModule mod_authz_core.c>\n";
		$rules .= "\tRequire all denied\n";
		$rules .= "</IfModule>\n";
		$rules .= "<IfModule !mod_authz_core.c>\n";
		$rules .= "\tOrder deny,allow\n";
		$rules .= "\tDeny from all\n";
		$rules .= "</IfModule>\n";
		$wp_filesystem->put_contents( $htaccess, $rules );
	}

	$web_config = $dir . 'web.config';
	if ( ! $wp_filesystem->exists( $web_config ) ) {
		$iis  = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
		$iis .= "<configuration>\n";
		$iis .= "\t<system.webServer>\n";
		$iis .= "\t\t<authorization>\n";
		$iis .= "\t\t\t<deny users=\"*\" />\n";
		$iis .= "\t\t</authorization>\n";
		$iis .= "\t</system.webServer>\n";
		$iis .= "</configuration>\n";
		$wp_filesystem->put_contents( $web_config, $iis );
	}

	$index = $dir . 'index.php';
	if ( ! $wp_filesystem->exists( $index ) ) {
		$wp_filesystem->put_contents( $index, "<?php\n// Silence is golden.\n" );
	}
}

/**
 * Whether a path is inside an allowed FCM private directory.
 *
 * @param string $file_path Absolute file path.
 * @return bool
 */
public function pwaforwp_is_allowed_fcm_key_path( $file_path ) {
	$file_path = wp_normalize_path( $file_path );
	$real_file = realpath( $file_path );
	if ( false === $real_file ) {
		return false;
	}
	$real_file = wp_normalize_path( $real_file );

	$allowed_dirs = array(
		$this->pwaforwp_get_fcm_private_dir(),
		$this->pwaforwp_get_legacy_fcm_private_dir(),
	);

	foreach ( $allowed_dirs as $dir ) {
		$real_dir = realpath( $dir );
		if ( false === $real_dir ) {
			continue;
		}
		$real_dir = trailingslashit( wp_normalize_path( $real_dir ) );
		if ( 0 === strpos( $real_file, $real_dir ) && substr( $real_file, -5 ) === '.json' ) {
			return true;
		}
	}

	return false;
}

/**
 * One-time migration: move credentials out of publicly served uploads and harden directories.
 *
 * @return void
 */
public function pwaforwp_maybe_secure_fcm_credentials() {
	if ( get_option( 'pwaforwp_fcm_credentials_secured' ) === PWAFORWP_PLUGIN_VERSION ) {
		return;
	}

	$settings   = get_option( 'pwaforwp_settings', array() );
	$legacy_dir = $this->pwaforwp_get_legacy_fcm_private_dir();
	$secure_dir = $this->pwaforwp_get_fcm_private_dir();

	$this->pwaforwp_ensure_protected_private_dir( $secure_dir );

	if ( file_exists( $legacy_dir ) ) {
		$this->pwaforwp_protect_private_dir( $legacy_dir );
	}

	if ( ! empty( $settings['fcm_server_key'] ) ) {
		$current = wp_normalize_path( $settings['fcm_server_key'] );
		$legacy  = wp_normalize_path( $legacy_dir );

		if ( 0 === strpos( $current, $legacy ) && file_exists( $current ) ) {
			$new_path = $this->pwaforwp_relocate_fcm_json( $current, $secure_dir );
			if ( $new_path ) {
				$settings['fcm_server_key'] = $new_path;
				update_option( 'pwaforwp_settings', $settings );
			}
		} elseif ( file_exists( $current ) ) {
			// Ensure existing secure-dir files also have restrictive permissions.
			$this->pwaforwp_set_file_permission( $current, 0600 );
		}
	}

	// Remove leftover guessable service-account-*.json files from the public uploads dir.
	$this->pwaforwp_cleanup_legacy_fcm_json_files( $legacy_dir, isset( $settings['fcm_server_key'] ) ? $settings['fcm_server_key'] : '' );

	update_option( 'pwaforwp_fcm_credentials_secured', PWAFORWP_PLUGIN_VERSION, false );
}

/**
 * Copies a service-account JSON into the secure private directory with a random name.
 *
 * @param string $source     Absolute source path.
 * @param string $secure_dir Absolute destination directory.
 * @return string|false New absolute path or false on failure.
 */
public function pwaforwp_relocate_fcm_json( $source, $secure_dir ) {
	if ( ! function_exists( 'WP_Filesystem' ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
	}

	global $wp_filesystem;
	if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base' ) ) {
		WP_Filesystem();
	}
	if ( ! $wp_filesystem || ! $wp_filesystem->exists( $source ) ) {
		return false;
	}

	$content = $wp_filesystem->get_contents( $source );
	if ( false === $content || '' === $content ) {
		return false;
	}

	$filename    = 'service-account-' . wp_generate_password( 32, false, false ) . '.json';
	$destination = trailingslashit( $secure_dir ) . $filename;

	if ( false === $wp_filesystem->put_contents( $destination, $content ) ) {
		return false;
	}

	$this->pwaforwp_set_file_permission( $destination, 0600 );
	$wp_filesystem->delete( $source );

	return $destination;
}

/**
 * Deletes orphaned service-account JSON files from the legacy public directory.
 *
 * @param string $legacy_dir      Absolute legacy directory.
 * @param string $keep_file_path  Absolute path that must not be deleted.
 * @return void
 */
public function pwaforwp_cleanup_legacy_fcm_json_files( $legacy_dir, $keep_file_path = '' ) {
	$legacy_dir = trailingslashit( wp_normalize_path( $legacy_dir ) );
	if ( ! is_dir( $legacy_dir ) ) {
		return;
	}

	$keep_file_path = wp_normalize_path( (string) $keep_file_path );
	$files          = glob( $legacy_dir . 'service-account-*.json' );
	if ( empty( $files ) ) {
		return;
	}

	if ( ! function_exists( 'WP_Filesystem' ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
	}
	global $wp_filesystem;
	if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base' ) ) {
		WP_Filesystem();
	}
	if ( ! $wp_filesystem ) {
		return;
	}

	foreach ( $files as $file ) {
		$file = wp_normalize_path( $file );
		if ( $keep_file_path && $file === $keep_file_path ) {
			continue;
		}
		$wp_filesystem->delete( $file );
	}
}

/**
 * Reads a JSON file and returns its content as an associative array.
 *
 * @param string $file_path The path to the JSON file.
 * @return array|null Returns the decoded JSON data as an associative array, or null if the file does not exist, is not readable, or contains invalid JSON.
 */
public function pwaforwp_read_json_file( $file_path ) {
	if ( empty( $file_path ) ) {
		return null;
	}

	$file_path = wp_normalize_path( $file_path );

	if ( ! $this->pwaforwp_is_allowed_fcm_key_path( $file_path ) ) {
		return null;
	}

	if ( ! file_exists( $file_path ) || ! is_readable( $file_path ) ) {
		return null;
	}

	// Load WordPress Filesystem API
	if ( ! function_exists( 'WP_Filesystem' ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
	}

	global $wp_filesystem;

	// Initialize the filesystem if not already initialized
	if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base' ) ) {
		WP_Filesystem();
	}

	// Double-check if filesystem initialized correctly
	if ( ! $wp_filesystem || ! $wp_filesystem->exists( $file_path ) ) {
		return null;
	}

	// Read and decode the JSON content
	$contents = $wp_filesystem->get_contents( $file_path );

	if ( empty( $contents ) ) {
		return null;
	}

	$data = json_decode( $contents, true );

	return is_array( $data ) ? $data : null;
}

/**
 * Safely sets file permissions using the WordPress Filesystem API.
 *
 * @param string $file_path Absolute path to the file.
 * @param int    $permission File permission in octal format (e.g., 0600).
 * @return bool True on success, false on failure.
 */
public function pwaforwp_set_file_permission( $file_path, $permission = 0600 ) {
	if ( empty( $file_path ) || ! file_exists( $file_path ) ) {
		return false;
	}

	if ( ! function_exists( 'WP_Filesystem' ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
	}

	global $wp_filesystem;

	if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base' ) ) {
		WP_Filesystem();
	}

	if ( ! $wp_filesystem || ! $wp_filesystem->exists( $file_path ) ) {
		return false;
	}

	return $wp_filesystem->chmod( $file_path, $permission, false );
}

public function pwaforwp_upload_fcm_json() {
	check_ajax_referer( 'pwaforwp_ajax_check_nonce', 'pwaforwp_security_nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json( array( 'status' => 0, 'message' => 'Permission denied' ) );
	}

	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Validated with is_uploaded_file() below.
	$tmp_name = isset( $_FILES['fcm_service_account_json']['tmp_name'] ) ? $_FILES['fcm_service_account_json']['tmp_name'] : '';
	if ( empty( $tmp_name ) || ! is_uploaded_file( $tmp_name ) ) {
		wp_send_json( array( 'status' => 0, 'message' => 'No file uploaded' ) );
	}

	$content = file_get_contents( $tmp_name );
	if ( false === $content ) {
		wp_send_json( array( 'status' => 0, 'message' => 'Upload failed' ) );
	}

	$decoded = json_decode( $content, true );
	if (
		! is_array( $decoded )
		|| empty( $decoded['type'] )
		|| 'service_account' !== $decoded['type']
		|| empty( $decoded['private_key'] )
		|| empty( $decoded['client_email'] )
		|| empty( $decoded['project_id'] )
	) {
		wp_send_json( array( 'status' => 0, 'message' => 'Invalid service account JSON' ) );
	}

	$private_dir = $this->pwaforwp_get_fcm_private_dir();
	if ( ! $this->pwaforwp_ensure_protected_private_dir( $private_dir ) ) {
		wp_send_json( array( 'status' => 0, 'message' => 'Upload failed' ) );
	}

	// Also harden the legacy uploads path if it still exists.
	$legacy_dir = $this->pwaforwp_get_legacy_fcm_private_dir();
	if ( file_exists( $legacy_dir ) ) {
		$this->pwaforwp_protect_private_dir( $legacy_dir );
	}

	$filename    = 'service-account-' . wp_generate_password( 32, false, false ) . '.json';
	$destination = $private_dir . $filename;

	if ( ! function_exists( 'WP_Filesystem' ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
	}
	global $wp_filesystem;
	if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base' ) ) {
		WP_Filesystem();
	}
	if ( ! $wp_filesystem ) {
		wp_send_json( array( 'status' => 0, 'message' => 'Upload failed' ) );
	}

	if ( false === $wp_filesystem->put_contents( $destination, $content ) ) {
		wp_send_json( array( 'status' => 0, 'message' => 'Upload failed' ) );
	}

	$this->pwaforwp_set_file_permission( $destination, 0600 );

	$settings = get_option( 'pwaforwp_settings', pwaforwp_defaultSettings() );
	$previous = ! empty( $settings['fcm_server_key'] ) ? wp_normalize_path( $settings['fcm_server_key'] ) : '';

	// Replace previous credential file so old copies are not left behind.
	if ( $previous && $previous !== $destination && $this->pwaforwp_is_allowed_fcm_key_path( $previous ) && $wp_filesystem->exists( $previous ) ) {
		$wp_filesystem->delete( $previous );
	}
	$this->pwaforwp_cleanup_legacy_fcm_json_files( $legacy_dir, $destination );

	$settings['fcm_server_key'] = $destination;
	update_option( 'pwaforwp_settings', $settings );
	update_option( 'pwaforwp_fcm_credentials_secured', PWAFORWP_PLUGIN_VERSION, false );

	wp_send_json( array( 'status' => 1, 'path' => $destination ) );
}

                 
}
if ( class_exists( 'PWAFORWP_Push_Notification' ) ) {
	      $pwaforwp_push_object = new PWAFORWP_Push_Notification;
        $pwaforwp_push_object->pwaforwp_push_notification_hooks();
};
