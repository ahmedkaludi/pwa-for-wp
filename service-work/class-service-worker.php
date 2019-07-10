<?php  
if ( ! defined( 'ABSPATH' ) ) exit;

class PWAFORWP_Service_Worker{
	
    public $is_amp = false;       
            
    public function __construct() {
        
        add_action( 'wp', array($this, 'pwaforwp_service_worker_init'), 1);
                
        $settings = pwaforwp_defaultSettings();
            
        if(isset($settings['custom_add_to_home_setting']) && isset($settings['normal_enable'])){
         add_action('wp_footer', array($this, 'pwaforwp_custom_add_to_home_screen'));   
        }        
                
        if(isset($settings['amp_enable'])){
         add_action('pre_amp_render_post', array($this, 'pwaforwp_amp_entry_point'));      
        }  
        
        $this->pwaforwp_is_amp_activated();
            	                                                                                                                                  
        add_action( 'publish_post', array($this, 'pwaforwp_store_latest_post_ids'), 10, 2 );
        add_action( 'publish_page', array($this, 'pwaforwp_store_latest_post_ids'), 10, 2 );
        add_action( 'wp_ajax_pwaforwp_update_pre_caching_urls', array($this, 'pwaforwp_update_pre_caching_urls'));
                                                  
        }
        
        public function pwaforwp_service_worker_init(){
            
            $settings = pwaforwp_defaultSettings();
            
            if(isset($settings['amp_enable']) && pwaforwp_amp_takeover_status()){
                
                add_action('wp_footer',array($this, 'pwaforwp_service_worker'));
                add_filter('amp_post_template_data',array($this, 'pwaforwp_service_worker_script'),35);
                add_action('wp_head',array($this, 'pwaforwp_paginated_post_add_homescreen_amp'),1);                
                
            }else{
                
               if(isset($settings['normal_enable'])){
                   
                 add_action('wp_footer',array($this, 'pwaforwp_service_worker_non_amp'),35);    
                 add_action('wp_head',array($this, 'pwaforwp_paginated_post_add_homescreen'),1);  
                 
               } 
               
            }
        }
        public function pwaforwp_update_pre_caching_urls(){
                        
            if ( ! isset( $_GET['pwaforwp_security_nonce'] ) ){
                return; 
            }       
            if ( !wp_verify_nonce( $_GET['pwaforwp_security_nonce'], 'pwaforwp_ajax_check_nonce' ) ){
               return;  
            } 
            
            $file_creation_init_obj = new PWAFORWP_File_Creation_Init(); 
            $result = $file_creation_init_obj->pwaforwp_swjs_init();
            $result = $file_creation_init_obj->pwaforwp_swjs_init_amp();
            
            update_option('pwaforwp_update_pre_cache_list', 'disable'); 
            delete_transient( 'pwaforwp_pre_cache_post_ids' );
            echo json_encode(array('status' => 't'));
            
            wp_die();   
        }
        public function pwaforwp_store_latest_post_ids(){
           
           if ( ! current_user_can( 'manage_options' ) ) {
                 return;
           }
           
           $post_ids = array();           
           $settings = pwaforwp_defaultSettings();
           
           if(isset($settings['precaching_automatic'])){
           
                $post_count =10;
                
                if(isset($settings['precaching_post_count']) && $settings['precaching_post_count'] !=''){
                   $post_count =$settings['precaching_post_count']; 
                }                
                $post_args = array( 'numberposts' => $post_count  );                                                          
                $page_args = array( 'number'       => $post_count );                                                                                        
                $postslist = get_posts( $post_args );
                $pageslist = get_pages( $page_args );
                
                if($postslist || $pageslist){
                                        
                    if($postslist && isset($settings['precaching_automatic_post'])){
                     
                        foreach ($postslist as $post){
                         $post_ids[] = $post->ID;
                       }
                        
                    }
                    
                    if($pageslist && isset($settings['precaching_automatic_page'])){
                     
                        foreach ($pageslist as $post){
                         $post_ids[] = $post->ID;
                       }                        
                    }   
                    
                     set_transient('pwaforwp_pre_cache_post_ids', json_encode($post_ids));    
                     update_option('pwaforwp_update_pre_cache_list', 'enable');
                }               
           }                                  
        }
        public function pwaforwp_custom_add_to_home_screen(){
            
            $settings        = pwaforwp_defaultSettings();
            $button_text     = esc_html__( 'Add', 'pwa-for-wp' );
            $banner_title    = esc_html__( 'Add', 'pwa-for-wp' ).' '.get_bloginfo().' '.esc_html__( 'to your Homescreen!', 'pwa-for-wp' );
                        
            if($settings['custom_banner_title'] && $settings['custom_banner_title'] != ''){
                $banner_title = $settings['custom_banner_title'];
            }
            
            if($settings['custom_banner_button_text'] && $settings['custom_banner_button_text'] !=''){
                $button_text = $settings['custom_banner_button_text'];
            }
                                                
            if ((function_exists( 'ampforwp_is_amp_endpoint' ) && ampforwp_is_amp_endpoint()) || function_exists( 'is_amp_endpoint' ) && is_amp_endpoint()) {                  
            }else{                             
                    echo '<div id="pwaforwp-add-to-home-click" style="background-color:'.sanitize_hex_color($settings['custom_banner_background_color']).'" class="pwaforwp-footer-prompt pwaforwp-bounceInUp pwaforwp-animated">'
                       . '<h3 style="color:'.sanitize_hex_color($settings['custom_banner_title_color']).'">'. esc_attr($banner_title).'</h3>'
                       . '<div style="background-color:'.sanitize_hex_color($settings['custom_banner_btn_color']).'; color:'.sanitize_hex_color($settings['custom_banner_btn_text_color']).'" class="pwaforwp-btn pwaforwp-btn-add-to-home">'.esc_attr($button_text).'</div>'
                       . '</div>'; 
            }
            
        }

        public function pwaforwp_amp_entry_point(){  
            
            add_action('amp_post_template_footer',array($this, 'pwaforwp_service_worker'));
            add_filter('amp_post_template_data',array($this, 'pwaforwp_service_worker_script'),35);
            add_action('amp_post_template_head',array($this, 'pwaforwp_paginated_post_add_homescreen_amp'),1); 
            
        }
	        
	public function pwaforwp_service_worker(){ 
                            
                $swjs_path_amp     = pwaforwp_site_url().'pwa-amp-sw'.pwaforwp_multisite_postfix().'.js';
                $swhtml            = pwaforwp_site_url().'pwa-amp-sw'.pwaforwp_multisite_postfix().'.html';
            
                ?>
                        <amp-install-serviceworker data-scope="<?php echo pwaforwp_home_url(); ?>" 
                        src="<?php echo esc_url($swjs_path_amp); ?>" 
                        data-iframe-src="<?php echo esc_url($swhtml); ?>"  
                        layout="nodisplay">
			</amp-install-serviceworker>
		<?php
                
	}
	
	public function pwaforwp_service_worker_script( $data ){
            
		if ( empty( $data['amp_component_scripts']['amp-install-serviceworker'] ) ) {
			$data['amp_component_scripts']['amp-install-serviceworker'] = 'https://cdn.ampproject.org/v0/amp-install-serviceworker-0.1.js';
		}
		return $data;
                
	}
       	
	public function pwaforwp_service_worker_non_amp(){
		
                $url 			 = pwaforwp_site_url();	
		$settings 		 = pwaforwp_defaultSettings();
		$manualfileSetup         = $settings['manualfileSetup'];
                
		if( $manualfileSetup ){
                echo '<script src="'.esc_url($url.'pwa-register-sw'.pwaforwp_multisite_postfix().'.js').'"></script>';    		
		}  
                
	}              
    
        public function pwaforwp_paginated_post_add_homescreen_amp(){  
            
		$url 			 = pwaforwp_site_url();	
		$settings 		 = pwaforwp_defaultSettings();
		$manualfileSetup         = $settings['manualfileSetup'];
		
		if($manualfileSetup){
                    
		    echo '<link rel="manifest" href="'. esc_url($url.'pwa-amp-manifest'.pwaforwp_multisite_postfix().'.json').'">
		    	<meta name="pwaforwp" content="wordpress-plugin"/>
		    	<meta name="theme-color" content="'.sanitize_hex_color($settings['theme_color']).'">'.PHP_EOL;
		    if(isset($settings['icon']) && !empty($settings['icon'])){
		    	echo '<link rel="apple-touch-icon" sizes="192x192" href="' . esc_url(pwaforwp_https($settings['icon'])) . '">'.PHP_EOL;
		    }
                    
		    if(isset($settings['splash_icon']) && !empty($settings['splash_icon'])){
		    	echo '<link rel="apple-touch-icon" sizes="512x512" href="' . esc_url(pwaforwp_https($settings['splash_icon'])) . '">'.PHP_EOL;
		    }

		}
	}

	public function pwaforwp_paginated_post_add_homescreen(){    
            
		$url 			 = pwaforwp_site_url();	
		$settings 		 = pwaforwp_defaultSettings();
		$manualfileSetup         = $settings['manualfileSetup'];
		
		if($manualfileSetup){
                    
           	echo '<meta name="pwaforwp" content="wordpress-plugin"/>
                      <meta name="theme-color" content="'.sanitize_hex_color($settings['theme_color']).'">'.PHP_EOL;
			echo '<link rel="manifest" href="'. parse_url($url.'pwa-manifest'.pwaforwp_multisite_postfix().'.json', PHP_URL_PATH).'"/>'.PHP_EOL;
			if(isset($settings['icon']) && !empty($settings['icon'])){
		    	echo '<link rel="apple-touch-icon" sizes="192x192" href="' . esc_url(pwaforwp_https($settings['icon'])) . '">'.PHP_EOL;
		    }
		    if(isset($settings['splash_icon']) && !empty($settings['splash_icon'])){
		    	echo '<link rel="apple-touch-icon" sizes="512x512" href="' . esc_url(pwaforwp_https($settings['splash_icon'])) . '">'.PHP_EOL;
		    }
                    
		}
                
	}

    public function pwaforwp_is_amp_activated() {    
		
        if ( function_exists( 'ampforwp_is_amp_endpoint' ) || function_exists( 'is_amp_endpoint' ) ) {
                $this->is_amp = true;
        }
		  
    }      
                
}
if (class_exists('PWAFORWP_Service_Worker')) {
	new PWAFORWP_Service_Worker;
};