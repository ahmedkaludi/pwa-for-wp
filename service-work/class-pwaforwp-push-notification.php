<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class PWAFORWP_Push_Notification {
            
     public function pwaforwp_push_notification_hooks() {

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

            echo wp_json_encode( array( 'status'=>'t', 'success'=> $result['success'], 'failure'=> $result['failure'] ) );    

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
            if ( ! current_user_can( pwaforwp_current_user_can() ) ) {
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
      protected function pwaforwp_send_push_notification($message){
          
            $settings   = pwaforwp_defaultSettings();                        
            $server_key = $settings['fcm_server_key'];           
            $tokens     = (array)json_decode(get_option('pwa_token_list'), true); 
            
            if(empty($tokens) || $server_key ==''){
                return false;
            }            
            $header = [
                    'Authorization: Key='. $server_key,
                    'Content-Type: Application/json'
            ];
            $msg = [
                    'title' => $message['title'],
                    'body'  => $message['body'],
                    'icon'  => (isset($settings['fcm_push_icon'])? esc_attr( $settings['fcm_push_icon']) : PWAFORWP_PLUGIN_URL.'/images/notification_icon.jpg'),
                    'budge'  => (isset($settings['fcm_budge_push_icon'])? esc_attr( $settings['fcm_budge_push_icon']) : PWAFORWP_PLUGIN_URL.'/images/notification_icon.jpg'),
                    'url'  => $message['url'],
                    'primarykey'  => uniqid(),
                    'image' => isset($message['image_url'])? $message['image_url'] : '',
            ];             
            $payload = [
                    'registration_ids' => $tokens,
                    'data'             => $msg  
            ];

            $args = array(
              'body'        => wp_json_encode($payload),
              'timeout'     => '15',
              'headers'     => $header,
              'sslverify'   => false,
            );

            $response = wp_remote_post( 'https://fcm.googleapis.com/fcm/send', $args);
            if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
              if(!empty($response->get_error_message())){
                $error_message = strtolower($response->get_error_message());
                $error_pos = strpos($error_message, 'operation timed out');
                if($error_pos !== false){
                  $message = esc_html__('Request timed out, please try again', 'pwa-for-wp');
                }else{
                  $message = esc_html($response->get_error_message());
                }
              }
              if(empty($message)){ 
                   $message =   esc_html__( 'An error occurred, please try again.', 'pwa-for-wp');
              }
              $response = array('success'=>0,'message'=>$message);
            }else{
              $response = wp_remote_retrieve_body( $response );
            }
            return $response;          
      }
                 
}
if ( class_exists( 'PWAFORWP_Push_Notification' ) ) {
	      $object = new PWAFORWP_Push_Notification;
        $object->pwaforwp_push_notification_hooks();
};
