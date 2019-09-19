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
		add_action( 'init',  array($this,'pwaforwp_onesignal_rewrite' ));
        
        /*
        load manifest on using Rest API
        * This change for manifest
        */
        add_action( 'rest_api_init', array( $this, 'register_manifest_rest_route' ) );
            //Only when Searve url & Installation Url Different
            $url = pwaforwp_site_url();
            $home_url = pwaforwp_home_url();
            if(is_multisite() || $url!==$home_url || !pwaforwp_is_file_inroot()){
                add_action( 'init', array($this, 'pwa_add_error_template_query_var') );
                add_action( 'parse_query', array($this, 'pwaforwp_load_service_worker') );
            }
                                                  
        }
		
		function pwaforwp_onesignal_rewrite(){
            flush_rewrite_rules();
            // Flushing rewrite urls ONLY on activation
            global $wp_rewrite;
            $wp_rewrite->flush_rules();
			add_rewrite_rule("onesignal_js/([0-9]{1,})?$", 'index.php?'.pwaforwp_query_var('sw_query_var').'=1&'.pwaforwp_query_var('sw_file_var').'='.'dynamic_onesignal'."&".pwaforwp_query_var('site_id_var').'=$matches[1]', 'top');

		}

        function pwaforwp_load_service_worker( WP_Query $query ){
            if ( $query->is_main_query() && $query->get( pwaforwp_query_var('sw_query_var') )) {
                @ini_set( 'display_errors', 0 );
                @header( 'Cache-Control: no-cache' );
                @header( 'Content-Type: text/javascript; charset=utf-8' );
                $fileRawName = $filename = $query->get( pwaforwp_query_var('sw_file_var') );
                if($filename == 'dynamic_onesignal'){//work with onesignal only
                    $home_url = pwaforwp_home_url();
                    $site_id = $query->get( pwaforwp_query_var('site_id_var') );
                    
                    $url = ($home_url.'?'.pwaforwp_query_var('sw_query_var').'=1&'.pwaforwp_query_var('sw_file_var').'='.'pwa-sw-'.$site_id.'-js');   
					header("Service-Worker-Allowed: /");
					header("Content-Type: application/javascript");
					header("X-Robots-Tag: none");
                    $content .= "importScripts('".$url."')".PHP_EOL;
                    $content .= "importScripts('https://cdn.onesignal.com/sdks/OneSignalSDKWorker.js')".PHP_EOL;
                    echo $content;
                    exit;
                }
				if( strpos($filename, '-js', -3) !== -1 ){
					$filename = str_replace("-js", ".js", $filename);
				}if( strpos($filename, '-html', -5) !== -1 ){
                    $filename = str_replace("-html", ".html", $filename);
                }

                $filename = apply_filters('pwaforwp_file_creation_path', ABSPATH).$filename;
                $path_info = pathinfo($filename);
                if ( !isset($path_info['extension']) 
                    || ( (isset($path_info['extension']) && $path_info['extension']!='js') 
                        && $fileRawName !== ( 'pwa-amp-sw.html'|| 'pwa-amp-sw-html' )
                        )
                ) {
                    status_header( 304 );
                    return;
                }
                if( file_exists($filename) ){
                    $file_data = file_get_contents( $filename );
                }else{
                    $fileCreation = new pwaforwpFileCreation();
                    if( strpos($fileRawName, '-js', -3) !== -1 ){
                        $fileRawName = str_replace("-js", ".js", $fileRawName);
                    }elseif( strpos($filename, '-html', -5) !== -1 ){
                        $fileRawName = str_replace("-html", ".html", $fileRawName);
                    }
                    switch ($fileRawName) {
                        case apply_filters('pwaforwp_sw_file_name', "pwa-sw".pwaforwp_multisite_postfix().".js"):
                            $swjsContent = $fileCreation->pwaforwp_swjs();
                            echo $swjsContent;
                            break;
                        case apply_filters('pwaforwp_sw_file_name', "pwa-register-sw".pwaforwp_multisite_postfix().".js"):
                            $swjsContent = $fileCreation->pwaforwp_swr();
                            echo $swjsContent;
                            break;
                        case apply_filters('pwaforwp_amp_sw_file_name',       "pwa-amp-sw".pwaforwp_multisite_postfix().".js"):
                            $swjsContent = $fileCreation->pwaforwp_swjs(true);
                            echo $swjsContent;
                            break;
                        case apply_filters('pwaforwp_amp_sw_html_file_name',  "pwa-amp-sw".pwaforwp_multisite_postfix().".html"):
                            @header( 'Content-Type: text/html; charset=utf-8' );
                            $swjsContent = $fileCreation->pwaforwp_swhtml(true);
                            echo $swjsContent;
                            break;
                        
                        default:
                            # code...
                            break;
                    }
                }
                echo $file_data;
                exit;
            }
        }

        function pwa_add_error_template_query_var() {
            global $wp;
            $allQueryVar = pwaforwp_query_var();
            if(is_array($allQueryVar)){
                foreach ($allQueryVar as $key => $value) {
                    $wp->add_query_var( $value );
                }
            }
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
           
                $post_count = 10;
                
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
                    echo '<div id="pwaforwp-add-to-home-click" style="background-color:'.sanitize_hex_color($settings['custom_banner_background_color']).'" class="pwaforwp-footer-prompt pwaforwp-bounceInUp pwaforwp-animated"> <span id="pwaforwp-prompt-close" class="pwaforwp-prompt-close">X</span>'
                       . '<h3 style="color:'.sanitize_hex_color($settings['custom_banner_title_color']).'">'. esc_html__($banner_title, 'pwa-for-wp').'</h3>'
                       . '<div style="background-color:'.sanitize_hex_color($settings['custom_banner_btn_color']).'; color:'.sanitize_hex_color($settings['custom_banner_btn_text_color']).'" class="pwaforwp-btn pwaforwp-btn-add-to-home">'.esc_html__($button_text, 'pwa-for-wp').'</div>'
                       . '</div>'; 
            }
            
        }
        public function pwaforwp_amp_entry_point(){  
            
            add_action('amp_post_template_footer',array($this, 'pwaforwp_service_worker'));
            add_filter('amp_post_template_data',array($this, 'pwaforwp_service_worker_script'),35);
            add_action('amp_post_template_head',array($this, 'pwaforwp_paginated_post_add_homescreen_amp'),1); 
            
        }	        
	public function pwaforwp_service_worker(){ 
                            
                //$swjs_path_amp     = pwaforwp_site_url().'pwa-amp-sw'.pwaforwp_multisite_postfix().'.js';
                $swhtml            = pwaforwp_site_url().'pwa-amp-sw'.pwaforwp_multisite_postfix().'.html';
                $swhtml            = service_workerUrls($swhtml, 'pwa-amp-sw'.pwaforwp_multisite_postfix().'.html');

                $url = pwaforwp_site_url();
                $home_url = pwaforwp_home_url();
                if( is_multisite() || trim($url)!==trim($home_url) || !pwaforwp_is_file_inroot()){
                    $swjs_path_amp   = $home_url.'?'.pwaforwp_query_var('sw_query_var').'=1&'.pwaforwp_query_var('sw_file_var').'='.apply_filters('pwaforwp_amp_sw_name_modify', 'pwa-amp-sw'.pwaforwp_multisite_postfix().'.js');   
                }else{
                    $swjs_path_amp     = pwaforwp_site_url().'pwa-amp-sw'.pwaforwp_multisite_postfix().'.js';
                }

            
                ?>
                        <amp-install-serviceworker data-scope="<?php echo trailingslashit(pwaforwp_home_url()).AMP_QUERY_VAR; ?>" 
                        src="<?php echo esc_url_raw($swjs_path_amp); ?>" 
                        data-iframe-src="<?php echo esc_url_raw($swhtml); ?>"  
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
                
		if( $manualfileSetup && !class_exists('OneSignal')){
            $filename = 'pwa-register-sw'.pwaforwp_multisite_postfix().'.js';
            $url = $url.$filename;
            $url = service_workerUrls($url, $filename);
                  
            echo '<script src="'.esc_url($url).'"></script>'; 
               
		}  
                
	}                  
        public function pwaforwp_paginated_post_add_homescreen_amp(){  
            
		$url 			 = pwaforwp_site_url();	
		$settings 		 = pwaforwp_defaultSettings();
		$manualfileSetup         = $settings['manualfileSetup'];
		
		if($manualfileSetup){
            $icons = isset( $settings['icons'] ) ? $settings['icons'] : array();
            usort( $icons, array( $this, 'sort_icons_callback' ) );
            $icon = array_shift( $icons );
                    
		    //<link rel="manifest" href="'. esc_url($url.'pwa-amp-manifest'.pwaforwp_multisite_postfix().'.json').'">
            echo '<link rel="manifest" href="'. esc_url( pwaforwp_manifest_json_url(true) ).'">
		    	<meta name="pwaforwp" content="wordpress-plugin"/>
		    	<meta name="theme-color" content="'.sanitize_hex_color($settings['theme_color']).'">
                <meta name="apple-mobile-web-app-title" content="'.esc_attr($settings['app_blog_name']).'">
                <meta name="application-name" content="'.esc_attr($settings['app_blog_name']).'">
                <meta name="apple-mobile-web-app-capable" content="yes">
                <meta name="mobile-web-app-capable" content="yes">
                <meta name="apple-touch-fullscreen" content="YES">'.PHP_EOL;
            if ( ! empty( $icon ) ) : 
                echo '<link rel="apple-touch-startup-image" href="'.esc_url( $icon['src'] ).'">';
            endif; 
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
            $icons = isset( $settings['icons'] ) ? $settings['icons'] : array();
            usort( $icons, array( $this, 'sort_icons_callback' ) );
            $icon = array_shift( $icons );
                    
           	echo '<meta name="pwaforwp" content="wordpress-plugin"/>
                      <meta name="theme-color" content="'.sanitize_hex_color($settings['theme_color']).'">'.PHP_EOL;
			//echo '<link rel="manifest" href="'. parse_url($url.'pwa-manifest'.pwaforwp_multisite_postfix().'.json', PHP_URL_PATH).'"/>'.PHP_EOL;
            echo '<link rel="manifest" href="'. esc_url( pwaforwp_manifest_json_url() ).'">'.PHP_EOL;
            echo '<meta name=" " content="'.esc_attr($settings['app_blog_name']).'">
            <meta name="application-name" content="'.esc_attr($settings['app_blog_name']).'">
            <meta name="apple-mobile-web-app-capable" content="yes">
            <meta name="mobile-web-app-capable" content="yes">
            <meta name="apple-touch-fullscreen" content="YES">';
            if ( ! empty( $icon ) ) : 
                echo '<link rel="apple-touch-startup-image" href="'.esc_url( $icon['src'] ).'">';
            endif; 
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

    /**
     * Registers the rest route to get the manifest.
     */
    public function register_manifest_rest_route() {
        $rest_namepace = 'pwa-for-wp/v2';
        $route = 'pwa-manifest-json';
        register_rest_route(
            $rest_namepace,
            'pwa-manifest-json',
            array(
                'methods'             => 'GET',
                'callback'            => array( $this, 'get_manifest' ),
                'permission_callback' => array( $this, 'rest_permission' ),
            )
        );
        register_rest_route(
            $rest_namepace,
            $route.'/(?P<is_amp>[a-zA-Z0-9-]+)',
            array(
                'methods'             => 'GET',
                'callback'            => array( $this, 'get_manifest' ),
                'permission_callback' => array( $this, 'rest_permission' ),
            )
        );
    }   

    /**
     * Registers the rest route to get the manifest.
     *
     * Mainly copied from WP_REST_Posts_Controller::get_items_permissions_check().
     * This should ndt allow a request in the 'edit' context.
     *
     * @param WP_REST_Request $request Full details about the request.
     * @return true|WP_Error True if the request is allowed, WP_Error if the request is in the 'edit' context.
     */
    public function rest_permission( WP_REST_Request $request ) {
        if ( 'edit' === $request['context'] ) {
            return new WP_Error( 'rest_forbidden_context', __( 'Sorry, you are not allowed to edit the manifest.', 'default' ), array( 'status' => rest_authorization_required_code() ) );
        }
        return true;
    }

    public function get_manifest($request){
        $dataObj = new pwaforwpFileCreation();
        if(isset($request['is_amp']) && $request['is_amp'] == 'amp' && defined('AMP_QUERY_VAR')){
            return json_decode($dataObj->pwaforwp_manifest(true),true);
        }else{
            return json_decode($dataObj->pwaforwp_manifest(),true);
        }

    }    
    /**
     * Sort icon sizes.
     *
     * Used as a callback in usort(), called from the manifest_link_and_meta() method.
     *
     * @param array $a The 1st icon item in our comparison.
     * @param array $b The 2nd icon item in our comparison.
     * @return int
     */
    public function sort_icons_callback( $a, $b ) {
        return (int) strtok( $a['sizes'], 'x' ) - (int) strtok( $b['sizes'], 'x' );
    }  
                
}
if (class_exists('PWAFORWP_Service_Worker')) {
	new PWAFORWP_Service_Worker;
};