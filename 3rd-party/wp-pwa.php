<?php
class PWAforWP_wppwa{
	function init(){
		add_filter("web_app_manifest", array($this, 'manifest_submit'), 10, 1);
		add_action( 'wp_front_service_worker', array( $this, 'add_cdn_script_caching' ) );
	}

	function add_cdn_script_caching( $service_workers ){
		$swJsContentbody  = $this->pwaforwp_getlayoutfiles("layouts/sw.js");
		if(isset($swJsContentbody) && $swJsContentbody){
            $swJsContent            = $swJsContentbody;
			$settings 		= pwaforwp_defaultSettings();   
            
            $external_links ='';
                
            if(isset($settings['external_links_setting']) && $settings['external_links_setting'] ==1){                      
                $external_links = '';                                                                    
            }else{
                $external_links = 'if ( new URL(event.request.url).origin !== location.origin )
                        return;';
            }
                
            $pre_cache_urls     = '';
            $pre_cache_urls_amp = '';

            //icons cache
            if(isset($settings['icon'])){
              $pre_cache_urls .= "'".esc_url(pwaforwp_https($settings['icon']))."',\n";
              $pre_cache_urls_amp .= "'".esc_url(pwaforwp_https($settings['icon']))."',\n";
            }
            if(isset($settings['splash_icon'])){
              $pre_cache_urls .= "'".esc_url(pwaforwp_https($settings['splash_icon']))."',\n";
              $pre_cache_urls_amp .= "'".esc_url(pwaforwp_https($settings['splash_icon']))."',\n";
            }
                
            if(isset($settings['precaching_manual']) && isset($settings['precaching_urls']) && $settings['precaching_urls'] !=''){
                
             	$explod_urls = explode(',', $settings['precaching_urls']);
             
	            foreach ($explod_urls as $url){
	                 
	              $pre_cache_urls .= "'".trim(esc_url($url))."',\n"; 
	              $pre_cache_urls_amp .= "'".trim(esc_url($url))."',\n"; 
	              
	            }   
             
            }
                               
            $store_post_id = array();
            $store_post_id = json_decode(get_transient('pwaforwp_pre_cache_post_ids'));
            
            if(!empty($store_post_id) && isset($settings['precaching_automatic'])){
                foreach ($store_post_id as $post_id){
                    $pre_cache_urls .= "'".trim(get_permalink($post_id))."',\n"; 
                    if ( function_exists('ampforwp_url_controller') ) {
						$pre_cache_urls_amp .= "'".ampforwp_url_controller(get_the_permalink($post_id)). "',\n"; 
					}
                    if (function_exists('amp_get_permalink')) {
                       $pre_cache_urls_amp .= "'".amp_get_permalink($post_id). "',\n"; 
					}              
                }
            }
                
            if($settings['excluded_urls'] !=''){
              $exclude_from_cache     = $settings['excluded_urls']; 
              $exclude_from_cache     = trim($exclude_from_cache, ",");
              $exclude_from_cache     = str_replace('/', '\/', $exclude_from_cache);     
              $exclude_from_cache     = '/'.str_replace(',', '/,/', $exclude_from_cache).'/'; 
              
            }else{
              $exclude_from_cache     = '';   
            }
                
            $offline_google = '';
            $cache_version = PWAFORWP_PLUGIN_VERSION;
                
            if(isset($settings['force_update_sw_setting']) && $settings['force_update_sw_setting'] !=''){
              $cache_version =   $settings['force_update_sw_setting'];
              if(!version_compare($cache_version,PWAFORWP_PLUGIN_VERSION, '>=') ){
                $cache_version = PWAFORWP_PLUGIN_VERSION;
              }
            }
            if(isset($settings['offline_google_setting'])){
            $offline_google = 'importScripts("https://storage.googleapis.com/workbox-cdn/releases/3.6.1/workbox-sw.js");
                                workbox.googleAnalytics.initialize();';    
            }
                                                
            $server_key = $settings['fcm_server_key'];
            $config     = $settings['fcm_config'];
                
            if(isset($settings['notification_feature']) && $settings['notification_feature']==1 && $server_key !='' && $config !=''){
             $firebasejs = $this->pwaforwp_firebase_js();  
            }else{
             $firebasejs = '';    
            }
                                
            $site_url 		= user_trailingslashit(pwaforwp_https( site_url() ));  
			$offline_page 		= user_trailingslashit(get_permalink( $settings['offline_page'] ) ?  pwaforwp_https(get_permalink( $settings['offline_page'] ))  :  pwaforwp_home_url());
			$page404 		= user_trailingslashit(get_permalink( $settings['404_page'] ) ?  pwaforwp_https(get_permalink( $settings['404_page'] )) : pwaforwp_home_url());  
		

			$cacheTimerHtml = 3600; $cacheTimerCss = 86400;
			if(isset($settings['cached_timer']) && is_numeric($settings['cached_timer']['html'])){
				$cacheTimerHtml = $settings['cached_timer']['html'];
			}
			if(isset($settings['cached_timer']) && is_numeric($settings['cached_timer']['css'])){
				$cacheTimerCss = $settings['cached_timer']['css'];
			}

		    /*Caching Strategy*/
		    $defaultStrategy  = $settings['default_caching'];
		    $cssjsStrategy    = $settings['default_caching_js_css'];
		    $imageStrategy    = $settings['default_caching_images'];
		    $fontStrategy     = $settings['default_caching_fonts'];


			$offline_page 	    = pwaforwp_https( $offline_page );
			$page404 			= pwaforwp_https( $page404 );    
			$swJsContent 	    = str_replace(array(
                                            "{{PRE_CACHE_URLS}}",     
                                            "{{OFFLINE_PAGE}}", 
                                            "{{404_PAGE}}", 
                                            "{{CACHE_VERSION}}",
                                            "{{SITE_URL}}", 
                                            "{{HTML_CACHE_TIME}}",
                                            "{{CSS_CACHE_TIME}}", 
                                            "{{FIREBASEJS}}", 
                                            "{{EXCLUDE_FROM_CACHE}}", 
                                            "{{OFFLINE_GOOGLE}}",
                                            "{{EXTERNAL_LINKS}}",
                                            "{{REGEX}}",
                                            "{{DEFAULT_CACHE_STRATEGY}}",
                                            "{{CSS_JS_CACHE_STRATEGY}}",
                                            "{{IMAGES_CACHE_STRATEGY}}",
                                            "{{FONTS_CACHE_STRATEGY}}"
                                            ),
                                      array(
                                            $pre_cache_urls,
                                            $offline_page, 
                                            $page404, 
                                            $cache_version, 
                                            $site_url, 
                                            $cacheTimerHtml, 
                                            $cacheTimerCss, 
                                            $firebasejs, 
                                            $exclude_from_cache, 
                                            $offline_google,
                                            $external_links,
                                            '/<img[^>]+src="(https:\/\/[^">]+)"/g',
                                            $defaultStrategy,
                                            $cssjsStrategy,
                                            $imageStrategy,
                                            $fontStrategy
                                            ), 
                                            $swJsContent
                                        );                		
		}

        $service_workers->register(
			'pwaforwp-runtime-caching',
			static function() use ($swJsContent) {
				
				return "{".$swJsContent."}";
			}
		);
	}

	public function pwaforwp_firebase_js(){
                $config = $swHtmlContent = '';
                $settings = pwaforwp_defaultSettings();  
                if( isset($settings['notification_feature']) && $settings['notification_feature']==1 &&isset($settings['fcm_config'])){
                    $config   = $settings['fcm_config'];
                }
                $swHtmlContentbody  = $this->pwaforwp_getlayoutfiles("layouts/pn_background.js");
                                                
                if(isset($swHtmlContentbody) && $swHtmlContentbody){
                    $swHtmlContent          = $swHtmlContentbody;
                    $swHtmlContent 	    = str_replace(array("{{config}}"),array($config),$swHtmlContent);
                }
                                                                                                                                                 
		return $swHtmlContent;		    
    }

	public function pwaforwp_getlayoutfiles($filePath){
	    $fileContentResponse = @wp_remote_get(PWAFORWP_PLUGIN_URL.$filePath);
	    if(wp_remote_retrieve_response_code($fileContentResponse)!=200){
	      if(!function_exists('get_filesystem_method')){
	        require_once( ABSPATH . 'wp-admin/includes/file.php' );
	      }
	      $access_type = get_filesystem_method();
	      if($access_type === 'direct')
	      {
	         $creds = request_filesystem_credentials(PWAFORWP_PLUGIN_DIR.$filePath, '', false, false, array());
	        if ( ! WP_Filesystem($creds) ) {
	          return false;
	        }   
	        global $wp_filesystem;
	        $htmlContentbody = $wp_filesystem->get_contents(PWAFORWP_PLUGIN_DIR.$filePath);
	        return $htmlContentbody;
	      }
	      return false;
	    }else{
	      return wp_remote_retrieve_body( $fileContentResponse );
	    }
	}

	function manifest_submit($manifest){
		$defaults = pwaforwp_defaultSettings();  
		$is_amp = false;
		if($is_amp){ 
            if(function_exists('ampforwp_url_controller')){
				$homeUrl = ampforwp_url_controller( pwaforwp_home_url() ) ;
            	$homeUrl = trailingslashit($homeUrl);
                if(isset($defaults['start_page']) && $defaults['start_page'] !=0){
                    $homeUrl = trailingslashit(get_permalink($defaults['start_page']));
                    $homeUrl = ampforwp_url_controller( $homeUrl ) ;
                }
          		$scope_url    = ampforwp_url_controller(pwaforwp_home_url());
            } else {
            	$homeUrl = add_query_arg(AMP_QUERY_VAR, 1, trailingslashit(pwaforwp_home_url()));
                if(isset($defaults['start_page']) && !empty($defaults['start_page']) ){
                	$homeUrl = add_query_arg( AMP_QUERY_VAR, 1, trailingslashit( get_permalink($defaults['start_page']) ) );
                }			
                $scope_url    = $homeUrl = add_query_arg( AMP_QUERY_VAR, 1, trailingslashit( pwaforwp_home_url() ) );
           	}
          	if(isset($defaults['utm_setting']) && $defaults['utm_setting']==1){
            	$homeUrl = add_query_arg( array_filter($defaults['utm_details']), $homeUrl  );
          	}             
        } else { //Non AMP
                $homeUrl = pwaforwp_home_url(); 
                if(isset($defaults['start_page']) && $defaults['start_page'] !=0){ 
                	print_r($defaults['start_page']);die;                   
                    $homeUrl = trailingslashit(get_permalink($defaults['start_page']));
                }
            
                if(isset($defaults['utm_setting']) && $defaults['utm_setting']==1){
                  $homeUrl = add_query_arg( array_filter($defaults['utm_details']),
                              $homeUrl 
                            );
	        }
            $scope_url = pwaforwp_home_url();//Scope Url should be serving url    
        }

        $homeUrl        = pwaforwp_https($homeUrl);
        $scope_url      = pwaforwp_https($scope_url);
        $orientation 	= isset($defaults['orientation']) && !empty($defaults['orientation']) ?  $defaults['orientation'] : "portrait";
        $display  = isset($defaults['display']) && !empty($defaults['display']) ?  $defaults['display'] : "standalone";
		if(isset($defaults['utm_setting']) && $defaults['utm_setting']==1){
			$homeUrl = rtrim($homeUrl, '/\\');
		}
                        
        $icons = array();
        //App icon
        $icons[] = array(
            'src' 	=> esc_url(pwaforwp_https($defaults['icon'])),
            'sizes'	=> '192x192', 
            'type'	=> 'image/png', 
        );
        //Splash icon
        $icons[] = array(
            'src' 	=> esc_url(pwaforwp_https($defaults['splash_icon'])),
            'sizes'	=> '512x512', 
            'type'	=> 'image/png', 
        );
                                                     
        $manifest['name']             = esc_attr($defaults['app_blog_name']);
        $manifest['short_name']       = esc_attr($defaults['app_blog_short_name']);
        $manifest['description']      = esc_attr($defaults['description']);
        $manifest['icons']            = $icons;
        $manifest['background_color'] = sanitize_hex_color($defaults['background_color']);
        $manifest['theme_color']      = sanitize_hex_color($defaults['theme_color']);
        $manifest['display']          = esc_html($display);
        $manifest['orientation']      = esc_html( $orientation );
        $manifest['start_url']        = esc_url($homeUrl);
        $manifest['scope']            = esc_url($scope_url);
        
        $manifest = apply_filters( 'pwaforwp_manifest', $manifest );

		return $manifest;
	}
}
$PWAforWP_wppwa = new PWAforWP_wppwa();
$PWAforWP_wppwa->init();