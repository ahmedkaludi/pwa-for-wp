<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class pwaforwpFileCreation{
                
    public function pwaforwp_swhtml($is_amp = false){      
            
	    if( $is_amp ){  
          $url = pwaforwp_site_url();
          $home_url = pwaforwp_home_url();
          $scope_url = trailingslashit($home_url).AMP_QUERY_VAR;

          if( is_multisite() || trim($url)!==trim($home_url) || !pwaforwp_is_file_inroot() ){
            $ServiceWorkerfileName   = $home_url.'?'.pwaforwp_query_var('sw_query_var').'=1&'.pwaforwp_query_var('sw_file_var').'='.apply_filters('pwaforwp_amp_sw_name_modify', 'pwa-amp-sw'.pwaforwp_multisite_postfix().'.js');   
			$ServiceWorkerfileName = service_workerUrls($ServiceWorkerfileName, apply_filters('pwaforwp_amp_sw_name_modify', 'pwa-amp-sw'.pwaforwp_multisite_postfix().'.js'));
          }else{
            $ServiceWorkerfileName          = $url.apply_filters('pwaforwp_amp_sw_name_modify', 'pwa-amp-sw'.pwaforwp_multisite_postfix().'.js');
          }
		        		
			$swHtmlContentbody 	        = @wp_remote_get(PWAFORWP_PLUGIN_URL."layouts/sw.html");
                        
                        $swHtmlContent = '';
                        
                        if(is_array($swHtmlContentbody) && isset($swHtmlContentbody['body'])){
                            $swHtmlContent                      = $swHtmlContentbody['body'];
                            $swHtmlContent 			= str_replace(array(
                                                                  "{{serviceWorkerFile}}",
                                                                  "{{scope_url}}"
                                                                  ), 
                                                                  array($ServiceWorkerfileName,
                                                                    $scope_url), 
                                                                  $swHtmlContent);
                        }                                                
			
			return $swHtmlContent;		    
	    }	
            
	}
    
    public function pwaforwp_pnjs($is_amp = false){
                
            $config = '';
        
            $settings   = pwaforwp_defaultSettings();
                       
            if(isset($settings['fcm_config'])){
                $config     = $settings['fcm_config'];
            }
                
            $swHtmlContentbody  = @wp_remote_get(PWAFORWP_PLUGIN_URL."layouts/pn-template.js"); 
            $swHtmlContent      = '';
            
            if(is_array($swHtmlContentbody) && isset($swHtmlContentbody['body'])){
                
                $swHtmlContent       = $swHtmlContentbody['body'];
                $firebase_config     = 'var config='.$config.';';
                $swHtmlContent       = str_replace("{{firebaseconfig}}", $firebase_config, $swHtmlContent);  
                                
            }
            return $swHtmlContent;
            
    } 
    
    public function pwaforwp_swr($is_amp = false){	
        
        $settings                       = pwaforwp_defaultSettings();
        $server_key                     = $settings['fcm_server_key'];
        $config                         = $settings['fcm_config'];
        $addtohomemanually              = '';
        
        if(isset($settings['add_to_home_selector'])){
          
         if(strchr($settings['add_to_home_selector'], '#')){
          $addtohomemanually    ='var a2hsBtn = document.getElementById("'.substr($settings['add_to_home_selector'], 1).'");
                                            if(a2hsBtn !== null){
                                                a2hsBtn.addEventListener("click", (e) => {
                                                    addToHome();	
                                                 });
                                            }';    
                                               
         }
         if(strchr($settings['add_to_home_selector'], '.')){
            $addtohomemanually    ='var a2hsBtn = document.getElementsByClassName("'.substr($settings['add_to_home_selector'], 1).'");
                                                if(a2hsBtn !== null){
                                                    for (var i = 0; i < a2hsBtn.length; i++) {
                                                      a2hsBtn[i].addEventListener("click", addToHome); 
                                                  }
                                                }';
                                               
        }
        
        }else{
         $addtohomemanually ='';
        }
        
        if(isset($settings['custom_add_to_home_setting'])){
          
            if(isset($settings['enable_add_to_home_desktop_setting'])){
                $banner_on_desktop ='var a2hsdesk = document.getElementById("pwaforwp-add-to-home-click");
                                    if(a2hsdesk !== null){
                                        a2hsdesk.style.display = "block";
                                    }'; 
                        
                          
            }else{
                $banner_on_desktop ='var isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);   if(isMobile){                                                    
                                            var a2hsdesk = document.getElementById("pwaforwp-add-to-home-click");
                                                    if(a2hsdesk !== null){
                                                        a2hsdesk.style.display = "block";
                                                    }   
                                        }';     
            }                                                        
           $addtohomebanner ='function PWAforwpreadCookie(name) {
                                  var nameEQ = name + "=";
                                  var ca = document.cookie.split(";");
                                  for(var i=0;i < ca.length;i++) {
                                      var c = ca[i];
                                      while (c.charAt(0)==" ") c = c.substring(1,c.length);
                                      if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
                                  }
                                  return null;
                              }

                              var lastScrollTop = 0;                                        
                              window.addEventListener("scroll", (evt) => {
                                var st = document.documentElement.scrollTop;
                                var closedTime = PWAforwpreadCookie("pwaforwp_prompt_close")
                                    if(closedTime){
                                      var today = new Date();
                                      var closedTime = new Date(closedTime);
                                      var diffMs = (today-closedTime);
                                      var diffMins = Math.round(((diffMs % 86400000) % 3600000) / 60000); // minutes
                                      if(diffMins<4){
                                        return false;
                                      }
                                    }
                                    if (st > lastScrollTop){
                                       if(deferredPrompt !=null){
                                       '.$banner_on_desktop.'                                                                 
                                       }                                              
                                    } else {
                                    var bhidescroll = document.getElementById("pwaforwp-add-to-home-click");
                                    if(bhidescroll !== null){
                                    bhidescroll.style.display = "none";
                                    }                                              
                                    }
                                 lastScrollTop = st;  
                                });

                              var addtohomeCloseBtn = document.getElementById("pwaforwp-prompt-close");
                                if(addtohomeCloseBtn !==null){
                                  addtohomeCloseBtn.addEventListener("click", (e) => {
                                      var bhidescroll = document.getElementById("pwaforwp-add-to-home-click");
                                      if(bhidescroll !== null){
                                        bhidescroll.style.display = "none";
                                        document.cookie = "pwaforwp_prompt_close="+new Date();
                                      }                                         
                                  });
                                }
                              var addtohomeBtn = document.getElementById("pwaforwp-add-to-home-click");	
                                if(addtohomeBtn !==null){
                                    addtohomeBtn.addEventListener("click", (e) => {
                                    addToHome();	
                                });
                                }';  
           
                                
        }else{
           $addtohomebanner ='';                   
        }
        
        if(isset($settings['add_to_home_selector']) || isset($settings['custom_add_to_home_setting'])){
            
            $addtohomefunction ='document.getElementById("pwaforwp-add-to-home-click").style.display = "none";';
            
        }else{
            
            $addtohomefunction ='';
            
        }

		$url = pwaforwp_site_url();
    $home_url = pwaforwp_home_url();

    if( is_multisite() || trim($url)!==trim($home_url) || !pwaforwp_is_file_inroot()){
      $ServiceWorkerfileName   = $home_url.'?'.pwaforwp_query_var('sw_query_var').'=1&'.pwaforwp_query_var('sw_file_var').'='.apply_filters('pwaforwp_sw_name_modify', 'pwa-sw'.pwaforwp_multisite_postfix().'.js');   
	  $ServiceWorkerfileName = service_workerUrls($ServiceWorkerfileName, apply_filters('pwaforwp_sw_name_modify', 'pwa-sw'.pwaforwp_multisite_postfix().'.js'));
    }else{
      $ServiceWorkerfileName   = $url.apply_filters('pwaforwp_sw_name_modify', 'pwa-sw'.pwaforwp_multisite_postfix().'.js');   
    }
       
		
		$swHtmlContentbody 		= @wp_remote_get(PWAFORWP_PLUGIN_URL."layouts/sw_non_amp.js");                                                               
                
                $swHtmlContent = '';
                
                if(is_array($swHtmlContentbody) && $swHtmlContentbody['body']){
                    
                 $swHtmlContent         = $swHtmlContentbody['body'];
                    
                 if($server_key !='' && $config !=''){
                 $firebaseconfig   = 'var config ='.$config.';'
                                     .'if (!firebase.apps.length) {firebase.initializeApp(config);}		  		  		                                   							
                                     const firebaseMessaging = firebase.messaging();';
                 $useserviceworker = 'firebaseMessaging.useServiceWorker(reg);';
                }else{
                 $firebaseconfig   = '';  
                 $useserviceworker = '';
                } 
                
                $addtohomeshortcode = apply_filters('pwaforwp_add_home_shortcode_modify', '');
                                
		$swHtmlContent 			= str_replace(array(
                                                "{{swfile}}", 
                                                "{{config}}", 
                                                "{{userserviceworker}}", 
                                                "{{addtohomemanually}}",
                                                "{{addtohomeshortcode}}",
                                                "{{addtohomebanner}}",
                                                "{{addtohomefunction}}",
                                                "{{home_url}}"
                                            ), 
                                            array(
                                                $ServiceWorkerfileName, 
                                                $firebaseconfig, 
                                                $useserviceworker,
                                                $addtohomemanually,
                                                $addtohomeshortcode,
                                                $addtohomebanner,
                                                $addtohomefunction,
                                                $home_url
                                            ), 
                                    $swHtmlContent);
                    
                    
                }                                                
		return $swHtmlContent;		    
    }
        
    public function pwaforwp_firebase_js(){
            
                $config = $swHtmlContent = '';
                $settings = pwaforwp_defaultSettings();  
                                
                if(isset($settings['fcm_config'])){
                    $config   = $settings['fcm_config'];
                }
                                                                   
                $swHtmlContentbody  = @wp_remote_get(PWAFORWP_PLUGIN_URL."layouts/pn_background.js");
                                                
                if(is_array($swHtmlContentbody)&& isset($swHtmlContentbody['body'])){
                    $swHtmlContent          = $swHtmlContentbody['body'];
                    $swHtmlContent 	    = str_replace(array("{{config}}"),array($config),$swHtmlContent);
                }
                                                                                                                                                 
		return $swHtmlContent;		    
    }
       
    public function pwaforwp_swjs($is_amp = false){
            
		$swJsContentbody 	= @wp_remote_get(PWAFORWP_PLUGIN_URL."layouts/sw.js");
                
                if(is_array($swJsContentbody) && isset($swJsContentbody['body'])){
                 
                $swJsContent            = $swJsContentbody['body'];
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
                                              
                       if ( is_plugin_active('accelerated-mobile-pages/accelerated-moblie-pages.php')) {
				
                           $pre_cache_urls_amp .= "'".user_trailingslashit(trim(get_permalink($post_id))).'amp'. "',\n"; 
			}
                        
                        if (is_plugin_active('amp/amp.php')) {
				
                           $pre_cache_urls_amp .= "'".user_trailingslashit(trim(get_permalink($post_id))). "',\n"; 
			}
                                                                                                                   
                    }
                }
                
                if($settings['excluded_urls'] !=''){     
                    
                  $exclude_from_cache     = $settings['excluded_urls']; 
                  $exclude_from_cache     = str_replace('/', '\/', $exclude_from_cache);     
                  $exclude_from_cache     = '/'.str_replace(',', '/,/', $exclude_from_cache).'/'; 
                  
                }else{
                  $exclude_from_cache     = '';   
                }
                
                $offline_google = '';
                $cache_version = PWAFORWP_PLUGIN_VERSION;
                
                if(isset($settings['force_update_sw_setting']) && $settings['force_update_sw_setting'] !=''){
                  $cache_version =   $settings['force_update_sw_setting'];
                }
                if(isset($settings['offline_google_setting'])){
                $offline_google = 'importScripts("https://storage.googleapis.com/workbox-cdn/releases/3.6.1/workbox-sw.js");
                                    workbox.googleAnalytics.initialize();';    
                }
                                                
                $server_key = $settings['fcm_server_key'];
                $config     = $settings['fcm_config'];
                
                if($server_key !='' && $config !=''){
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

		if( $is_amp ){
                        $firebasejs ='';
			$offline_page 	= pwaforwp_https( $offline_page ).'?amp=1';
			$page404 	= pwaforwp_https( $page404 ).'?amp=1';  
			$swJsContent 	= str_replace(array(
                                                        "{{PRE_CACHE_URLS}}", 
							"{{OFFLINE_PAGE}}", 
                                                        "{{404_PAGE}}", 
                                                        "{{CACHE_VERSION}}",
                                                        "{{SITE_URL}}", 
                                                        "{{HTML_CACHE_TIME}}",
                                                        "{{CSS_CACHE_TIME}}", 
                                                        "{{FIREBASEJS}}" , 
                                                        "{{EXCLUDE_FROM_CACHE}}", 
                                                        "{{OFFLINE_GOOGLE}}",
                                                        "{{EXTERNAL_LINKS}}",
                                                        "{{REGEX}}"
                                                            ), 
                                                     array(
                                                         $pre_cache_urls_amp,
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
                                                         '/<amp-img[^>]+src="(https:\/\/[^">]+)"/g'
                                                        ),
							 $swJsContent
                                                        );                		
		} else {
			$offline_page 	        = pwaforwp_https( $offline_page );
			$page404 		= pwaforwp_https( $page404 );    
			$swJsContent 	        = str_replace(array(
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
                                                            "{{REGEX}}"
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
                                                            '/<img[^>]+src="(https:\/\/[^">]+)"/g'
                                                            ), 
                                                            $swJsContent);                		
		} 
                    
                }                                               		
	        return apply_filters( 'pwaforwp_sw_js_template', $swJsContent );
		
	}
      
    public function pwaforwp_manifest($is_amp = false){ 
        
    	$defaults = pwaforwp_defaultSettings();  
        
        if($is_amp){ 
                        if(function_exists('ampforwp_url_controller')){
				$homeUrl = ampforwp_url_controller( pwaforwp_home_url() ) ;
                                $homeUrl = trailingslashit($homeUrl);
                                
                                if(isset($defaults['start_page']) && $defaults['start_page'] !=0){
                                        $homeUrl = trailingslashit(get_permalink($defaults['start_page']));
                                        $homeUrl = ampforwp_url_controller( $homeUrl ) ;
                                }
                                                                
				if(isset($defaults['utm_setting']) && $defaults['utm_setting']==1){
					$homeUrl = $homeUrl."?".http_build_query(array_filter($defaults['utm_details']));
				}
			} else {
                            
                                $homeUrl = trailingslashit(pwaforwp_home_url()).AMP_QUERY_VAR;
                                
                                if(isset($defaults['start_page']) && $defaults['start_page'] !=0 ){
                                  $homeUrl = trailingslashit(get_permalink($defaults['start_page'])).AMP_QUERY_VAR;
                                }				
				if(isset($defaults['utm_setting']) && $defaults['utm_setting']==1){
					$homeUrl = $homeUrl."?".http_build_query(array_filter($defaults['utm_details']));
				}
			}                       
                        $scope_url    = trailingslashit(pwaforwp_home_url()).AMP_QUERY_VAR;
                        
        } else {
            
                $homeUrl = pwaforwp_home_url(); 
                
                if(isset($defaults['start_page']) && $defaults['start_page'] !=0){                    
                    $homeUrl = trailingslashit(get_permalink($defaults['start_page']));
                }
            
                if(isset($defaults['utm_setting']) && $defaults['utm_setting']==1){
	            $homeUrl = $homeUrl."?".http_build_query(array_filter($defaults['utm_details']));
	        }
                
                $scope_url = pwaforwp_home_url();//Scope Url should be serving url
                
        }                                            
                $homeUrl        = trailingslashit(pwaforwp_https($homeUrl));
                $scope_url      = trailingslashit(pwaforwp_https($scope_url));
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
                                                             
                $manifest = array();
                                                
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
		
                return json_encode($manifest, JSON_PRETTY_PRINT);					
	}        
}