<?php 
// Exit if accessed directly
if ( ! defined('ABSPATH') ) exit;

class PWAFORWP_Newsletter {
        
	public function __construct () {
		
                add_filter( 'pwaforwp_localize_filter', array( $this, 'pwaforwp_add_localize_footer_data' ), 10, 2 );
	}
	
        public function pwaforwp_add_localize_footer_data( $object, $object_name ) {
        
        $dismissed = explode ( ',', get_user_meta ( wp_get_current_user()->ID, 'dismissed_wp_pointers', true ) );                                
        $do_tour   = ! in_array ( 'pwaforwp_subscribe_pointer', $dismissed ); 
        
        if ( $do_tour ) {

                wp_enqueue_style ( 'wp-pointer' );
                wp_enqueue_script ( 'wp-pointer' );

	}
                        
        if ( $object_name == 'pwaforwp_obj' ) {
                        
                global $current_user;                                
		$tour     = array ();                
                $tab      = '';              
                // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- we are not processing form here
                if ( isset( $_GET['tab'] ) ) { 
                // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- we are not processing form here
                        $tab      = $_GET['tab'];
                }
                                
                if ( ! array_key_exists( $tab, $tour ) ) {
			                                           			            	
                        $object['do_tour']            = $do_tour;        
                        $object['get_home_url']       = get_home_url();                
                        $object['current_user_email'] = esc_attr($current_user->user_email);                
                        $object['current_user_name']  = esc_attr($current_user->display_name);        
			$object['displayID']          = '#toplevel_page_pwaforwp';                        
                        $object['button1']            = esc_html__('No Thanks', 'pwa-for-wp');
                        $object['button2']            = false;
                        $object['function_name']      = '';
		}
		                                                                                                                                                    
        }
        return $object;
         
    }
       
}

new PWAFORWP_Newsletter();