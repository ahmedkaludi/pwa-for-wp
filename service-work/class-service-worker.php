<?php               
class PWAFORWP_Service_Worker{
	
    public $is_amp = false;
    public $is_amp_front = false;
    public $swjs_path;
    public $swjs_path_amp;
    public $swhtml_path;
    public $minifest_path;
    public $minifest_path_amp;
    public $wppath;
    

    public function __construct() {
        
        
        $settings = pwaforwp_defaultSettings();
        $multisite_filename_postfix = '';       
        if(isset($settings['custom_add_to_home_setting'])){
         add_action('wp_footer', array($this, 'pwaforwp_custom_add_to_home_screen'));   
        }        
        if ( is_multisite() ) {
           $multisite_filename_postfix = '-' . get_current_blog_id();
        }
        
        if(isset($settings['amp_enable'])){
        add_action('pre_amp_render_post', array($this, 'pwaforwp_amp_entry_point'));      
        }                      
        $this->pwaforwp_is_amp_activated();
            
	$url = pwaforwp_front_url();                              
        $this->wppath = str_replace("//","/",str_replace("\\","/",realpath(ABSPATH))."/");
        $this->swjs_path = $url.PWAFORWP_FILE_PREFIX.'-sw'.$multisite_filename_postfix.'.js';
        $this->minifest_path = $url.PWAFORWP_FILE_PREFIX.'-manifest'.$multisite_filename_postfix.'.json';
        
        $this->swjs_path_amp = $url.PWAFORWP_FILE_PREFIX.'-amp-sw'.$multisite_filename_postfix.'.js';
        $this->swhtml_path = $url.PWAFORWP_FILE_PREFIX.'-amp-sw'.$multisite_filename_postfix.'.html';
        $this->minifest_path_amp = $url.PWAFORWP_FILE_PREFIX.'-amp-manifest'.$multisite_filename_postfix.'.json';
        
        
        if(isset($settings['normal_enable'])){
            add_action('wp_footer',array($this, 'pwaforwp_service_worker_non_amp'),35);    
            add_action('wp_head',array($this, 'pwaforwp_paginated_post_add_homescreen'),1);         
        }
                             
        add_action( 'publish_post', array($this, 'pwaforwp_store_latest_post_ids'), 10, 2 );  
        add_action('wp_ajax_pwaforwp_update_pre_caching_urls', array($this, 'pwaforwp_update_pre_caching_urls'));
        
         
                  
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
           
           $post_ids = array();
           $settings = pwaforwp_defaultSettings();
           
           if(isset($settings['precaching_setting']) && $settings['precaching_method'] == 'automatic'){
           
                $post_count =10;
                
                if(isset($settings['precaching_post_count']) && $settings['precaching_post_count'] !=''){
                   $post_count =$settings['precaching_post_count']; 
                }
                
                $args = array( 'numberposts' => $post_count, 'order'=> 'ASC', 'orderby' => 'title' );
                $postslist = get_posts( $args );
                if($postslist){
                     foreach ($postslist as $post){
                         $post_ids[] = $post->ID;
                     }           
                     set_transient('pwaforwp_pre_cache_post_ids', json_encode($post_ids));    
                     update_option('pwaforwp_update_pre_cache_list', 'enable');
                }
               
           }
                                  
        }
        public function pwaforwp_custom_add_to_home_screen(){
            if ((function_exists( 'ampforwp_is_amp_endpoint' ) && ampforwp_is_amp_endpoint()) || function_exists( 'is_amp_endpoint' ) && is_amp_endpoint()) {                  
            }else{                             
               echo '<div id="pwaforwp-add-to-home-click" class="pwaforwp-footer-prompt pwaforwp-bounceInUp pwaforwp-animated"><h3>Add '.get_bloginfo().' to your Homescreen!</h3><div class="pwaforwp-btn pwaforwp-btn-add-to-home">'.esc_html__( 'Add', 'pwa-for-wp' ).'</div></div>'; 
            }
        }

        public function pwaforwp_amp_entry_point(){            
            add_action('amp_post_template_footer',array($this, 'pwaforwp_service_worker'));
            add_filter('amp_post_template_data',array($this, 'pwaforwp_service_worker_script'),35);
            add_action('amp_post_template_head',array($this, 'pwaforwp_paginated_post_add_homescreen_amp'),1); 
        }
	        
	public function pwaforwp_service_worker(){ 
                ?>
		<amp-install-serviceworker src="<?php echo esc_url($this->swjs_path_amp); ?>" data-iframe-src="<?php echo esc_url($this->swhtml_path); ?>"  layout="nodisplay">
			</amp-install-serviceworker>
		<?php
	}
	//Load Script
	public function pwaforwp_service_worker_script( $data ){
		if ( empty( $data['amp_component_scripts']['amp-install-serviceworker'] ) ) {
			$data['amp_component_scripts']['amp-install-serviceworker'] = 'https://cdn.ampproject.org/v0/amp-install-serviceworker-0.1.js';
		}
		return $data;
	}
       	
	public function pwaforwp_service_worker_non_amp(){
		$multisite_filename_postfix = '';
        if ( is_multisite() ) {
           $multisite_filename_postfix = '-' . get_current_blog_id();
        }
        $url 			 = pwaforwp_front_url();	
		$settings 		 = pwaforwp_defaultSettings();
		$manualfileSetup = $settings['manualfileSetup'];
		if( $manualfileSetup ){
            echo '<script src="'.esc_url($url.PWAFORWP_FILE_PREFIX.'-register-sw'.$multisite_filename_postfix.'.js').'"></script>';    		
		}           		
	}              
    
        public function pwaforwp_paginated_post_add_homescreen_amp(){           
		$url 			 = pwaforwp_front_url();	
		$settings 		 = pwaforwp_defaultSettings();
		$manualfileSetup = $settings['manualfileSetup'];
		$multisite_filename_postfix = '';
		if ( is_multisite() ) {
			$multisite_filename_postfix = '-' . get_current_blog_id();
		}
		if($manualfileSetup){
		    echo '<link rel="manifest" href="'. esc_url($url.PWAFORWP_FILE_PREFIX.'-amp-manifest'.$multisite_filename_postfix.'.json').'">
		    	<meta name="pwaforwp" content="wordpress-plugin"/>
		    	<meta name="theme-color" content="'.$settings['theme_color'].'">'.PHP_EOL;
		    if(isset($settings['icon']) && !empty($settings['icon'])){
		    	echo '<link rel="apple-touch-icon" sizes="192x192" href="' . $settings['icon'] . '">'.PHP_EOL;
		    }
		    if(isset($settings['splash_icon']) && !empty($settings['splash_icon'])){
		    	echo '<link rel="apple-touch-icon" sizes="512x512" href="' . $settings['splash_icon'] . '">'.PHP_EOL;
		    }

		}
	}

	public function pwaforwp_paginated_post_add_homescreen(){                       
		$url 			 = pwaforwp_front_url();	
		$settings 		 = pwaforwp_defaultSettings();
		$manualfileSetup = $settings['manualfileSetup'];
		$multisite_filename_postfix = '';
		if ( is_multisite() ) {
			$url =  trailingslashit(str_replace("http:","https:",network_site_url()));
			$multisite_filename_postfix = '-' . get_current_blog_id();
		}
		if($manualfileSetup){
           	echo '<meta name="pwaforwp" content="wordpress-plugin"/>
                <meta name="theme-color" content="'.$settings['theme_color'].'">';
			echo '<link rel="manifest" href="'. parse_url(pwaforwp_front_url().PWAFORWP_FILE_PREFIX.'-manifest'.$multisite_filename_postfix.'.json', PHP_URL_PATH).'"/>';
			if(isset($settings['icon']) && !empty($settings['icon'])){
		    	echo '<link rel="apple-touch-icon" sizes="192x192" href="' . $settings['icon'] . '">'.PHP_EOL;
		    }
		    if(isset($settings['splash_icon']) && !empty($settings['splash_icon'])){
		    	echo '<link rel="apple-touch-icon" sizes="512x512" href="' . $settings['splash_icon'] . '">'.PHP_EOL;
		    }
		}
	}

	public function pwaforwp_is_amp_activated() {    
		if(function_exists('is_plugin_active')){
			if ( is_plugin_active('accelerated-mobile-pages/accelerated-moblie-pages.php') || is_plugin_active('amp/amp.php')) {
				$this->is_amp = true;
			}
		}    
    }                                 
}
if (class_exists('PWAFORWP_Service_Worker')) {
	new PWAFORWP_Service_Worker;
};