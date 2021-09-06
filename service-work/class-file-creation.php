<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class pwaforwpFileCreation{
                
    public function pwaforwp_swhtml($is_amp = false){      
            
	    if( $is_amp ){  
          $url = pwaforwp_site_url();
          $home_url = pwaforwp_home_url();
          $scope_url = trailingslashit($home_url).AMP_QUERY_VAR;

          if(pwaforwp_is_automattic_amp( 'amp_support' )){
             $scope_url = trailingslashit($home_url);
          }elseif(function_exists('ampforwp_url_controller')){
            $scope_url = ampforwp_url_controller($home_url);
          }

          if( is_multisite() || trim($url)!==trim($home_url) || !pwaforwp_is_file_inroot() ){
            $ServiceWorkerfileName   = $home_url.'?'.pwaforwp_query_var('sw_query_var').'=1&'.pwaforwp_query_var('sw_file_var').'='.apply_filters('pwaforwp_amp_sw_name_modify', 'pwa-amp-sw'.pwaforwp_multisite_postfix().'.js');   
			       $ServiceWorkerfileName = service_workerUrls($ServiceWorkerfileName, apply_filters('pwaforwp_amp_sw_name_modify', 'pwa-amp-sw'.pwaforwp_multisite_postfix().'.js'));
          }else{
            $ServiceWorkerfileName          = $url.apply_filters('pwaforwp_amp_sw_name_modify', 'pwa-amp-sw'.pwaforwp_multisite_postfix().'.js');
          }
		        		
          $swHtmlContentbody          = $this->pwaforwp_getlayoutfiles("layouts/sw.html");
          $settings                   = pwaforwp_defaultSettings();
                        
          $swHtmlContent = '';
          /*Default Bar will be disabled if custom add to home banners are enabled*/
          $showPwaDefaultbar = apply_filters("pwaforwp_service_showdefault_addtohomebar", $settings['addtohomebanner_feature']);
          $swdefaultaddtohomebar = '';
          if($showPwaDefaultbar==1){
            $swdefaultaddtohomebar = "e.preventDefault();";
          }
          if( isset($swHtmlContentbody) && $swHtmlContentbody){
            $swHtmlContent      = $swHtmlContentbody;
            $swHtmlContent 			= str_replace(array(
                                      "{{serviceWorkerFile}}",
                                      "{{scope_url}}",
                                      "{{swdefaultaddtohomebar}}"
                                    ), 
                                    array($ServiceWorkerfileName,
                                      $scope_url,
                                      $swdefaultaddtohomebar
                                    ), 
                                    $swHtmlContent);
          }                                                
			
			return $swHtmlContent;		    
	    }	            
	  }
    
    public function pwaforwp_pnjs($is_amp = false){
                
            $config = '';
        
            $settings   = pwaforwp_defaultSettings();
                       
            if(isset($settings['notification_feature']) && $settings['notification_feature']==1 && isset($settings['fcm_config'])){
                $config     = $settings['fcm_config'];
            }
                
            $swHtmlContentbody  = $this->pwaforwp_getlayoutfiles("layouts/pn-template.js"); 
            $swHtmlContent      = '';
            
            if(isset($swHtmlContentbody) && $swHtmlContentbody){
                
                $swHtmlContent       = $swHtmlContentbody;
                $firebase_config     = 'var config='.$config.';';
                $swHtmlContent       = str_replace("{{firebaseconfig}}", $firebase_config, $swHtmlContent);  
                                
            }
            return $swHtmlContent;
            
    } 
    
    public function pwaforwp_swr($is_amp = false){	
        
        $settings                       = pwaforwp_defaultSettings();
        $server_key = $config = '';
        if( isset($settings['notification_feature']) && $settings['notification_feature']==1 && isset($settings['notification_options']) && $settings['notification_options']=='fcm_push'){
          $server_key                   = $settings['fcm_server_key'];
          $config                       = $settings['fcm_config'];
        }
        $addtohomemanually              = '';
        
        if(isset($settings['add_to_home_selector'])){
          
         if(strchr($settings['add_to_home_selector'], '#')){
          $addtohomemanually    ='function collectionHas(a, b) { //helper function (see below)
                                    for(var i = 0, len = a.length; i < len; i ++) {
                                      if(a[i] == b) return true;
                                    }
                                    return false;
                                  }
                                   
                                   function findParentBySelector(elm, selector) {
                                    var all = document.querySelectorAll(selector);
                                    var cur = elm.parentNode;
                                    while(cur && !collectionHas(all, cur)) { //keep going up until you find a match
                                      cur = cur.parentNode; //go up
                                    }
                                    return cur; //will return null if not found
                                  }
                                  document.addEventListener("click",function(e){
                                    if(e.target && e.target.id== "'.substr($settings['add_to_home_selector'], 1).'"){
                                       addToHome();
                                     }
                                     if(findParentBySelector(e.target, "'.$settings['add_to_home_selector'].'")){
                                      addToHome();
                                     }
                                  });';    
                                               
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
        
        if(isset($settings['custom_add_to_home_setting']) && $settings['custom_add_to_home_setting']==1){
          
            if(isset($settings['enable_add_to_home_desktop_setting']) && $settings['enable_add_to_home_desktop_setting']==1){
                $banner_on_desktop ='var a2hsdesk = document.getElementById("pwaforwp-add-to-home-click");
                                    var isMobile = /iPhone|iPad|iPod/i.test(navigator.userAgent);
                                    if(a2hsdesk !== null && checkbarClosedOrNot() && !isMobile){
                                        a2hsdesk.style.display = "block";
                                    }'; 
                        
                          
            }else{
                $banner_on_desktop ='var isMobile = /Android/i.test(navigator.userAgent);   if(isMobile){                                                    
                                            var a2hsdesk = document.getElementById("pwaforwp-add-to-home-click");
                                                    if(a2hsdesk !== null  && checkbarClosedOrNot()){
                                                        a2hsdesk.style.display = "block";
                                                    }   
                                        }';     
            }                                                        
            if(isset($settings['show_banner_without_scroll']) && $settings['show_banner_without_scroll']==1){
              $addtohomebanner = $banner_on_desktop;
            }else{
              $addtohomebanner ='var lastScrollTop = 0;                                        
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
                                });';  
           }

           $addtohomebanner .= 'var closeclicked = false; var addtohomeCloseBtn = document.getElementById("pwaforwp-prompt-close");
                                if(addtohomeCloseBtn !==null){
                                  addtohomeCloseBtn.addEventListener("click", (e) => {
                                      closeclicked = true;
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
                                      if(closeclicked){return false;}
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

    $swFilename = apply_filters('pwaforwp_sw_name_modify', 'pwa-sw'.pwaforwp_multisite_postfix().'.js');
    $ServiceWorkerfileName   = $url.$swFilename;
    if( trim($url)!==trim($home_url) || !pwaforwp_is_file_inroot()){
      $ServiceWorkerfileName = service_workerUrls($ServiceWorkerfileName, $swFilename);
    }
    /*Default Bar will be disabled if custom add to home banners are enabled*/
    $showPwaDefaultbar = apply_filters("pwaforwp_service_showdefault_addtohomebar", $settings['addtohomebanner_feature']);
    
    $avoid_default_banner = 0;
    if(isset($settings['avoid_default_banner']) && ($settings['avoid_default_banner']==1 || $settings['avoid_default_banner']==true) ){
      $avoid_default_banner = 1;
    }
    $swdefaultaddtohomebar = '';
    if($showPwaDefaultbar==1 || ($showPwaDefaultbar==0 && $avoid_default_banner==1)){
      $swdefaultaddtohomebar = "e.preventDefault();";
    }
       
		
    $swHtmlContentbody    = $this->pwaforwp_getlayoutfiles("layouts/sw_non_amp.js");                                                               
                
                $swHtmlContent = '';
                
                if(isset($swHtmlContentbody) && $swHtmlContentbody){
                    
                 $swHtmlContent         = $swHtmlContentbody;
                    
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
                $firebaseconfig = apply_filters('pwaforwp_pn_config', $firebaseconfig);
                $useserviceworker = apply_filters('pwaforwp_pn_use_sw', $useserviceworker);

                                
		$swHtmlContent 			= str_replace(array(
                                                "{{swfile}}", 
                                                "{{config}}", 
                                                "{{userserviceworker}}", 
                                                "{{addtohomemanually}}",
                                                "{{addtohomeshortcode}}",
                                                "{{addtohomebanner}}",
                                                "{{addtohomefunction}}",
                                                "{{home_url}}",
                                                "{{swdefaultaddtohomebar}}",
                                                "{{HTML_DEFAULTCACHING}}"
                                            ), 
                                            array(
                                                $ServiceWorkerfileName, 
                                                $firebaseconfig, 
                                                $useserviceworker,
                                                $addtohomemanually,
                                                $addtohomeshortcode,
                                                $addtohomebanner,
                                                $addtohomefunction,
                                                $home_url,
                                                $swdefaultaddtohomebar,
                                                $settings['default_caching']
                                            ), 
                                    $swHtmlContent);
                    
                    
                }        
        $swHtmlContent = apply_filters("pwaforwp_sw_register_template", $swHtmlContent);
		return $swHtmlContent;		    
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
       
    public function pwaforwp_swjs($is_amp = false){
            
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
                if(isset($settings['switch_apple_splash_screen']) && $settings['switch_apple_splash_screen']==1){
                  foreach ($settings['ios_splash_icon'] as $key => $value) {
                    if($value){
                      $pre_cache_urls .= "'".esc_url(pwaforwp_https($value))."',\n";
                      $pre_cache_urls_amp .= "'".esc_url(pwaforwp_https($value))."',\n";
                    }
                  }
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
                
                if(!empty($store_post_id) && isset($settings['precaching_automatic']) && $settings['precaching_automatic']==1){
                    
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
                
                if(isset($settings['excluded_urls']) && !empty($settings['excluded_urls'])){     
                    
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
                if(isset($settings['offline_google_setting']) && $settings['offline_google_setting']==1){
                $offline_google = 'importScripts("https://storage.googleapis.com/workbox-cdn/releases/3.6.1/workbox-sw.js");
                                if(workbox.googleAnalytics){
                                  try{
                                    workbox.googleAnalytics.initialize();
                                  } catch (e){}
                                }';    
                }
                
                $firebasejs = '';
                if(isset($settings['notification_options']) && $settings['notification_options']=='fcm_push'
                 && isset($settings['notification_feature']) && $settings['notification_feature']==1
                ){                                
                  $server_key = $settings['fcm_server_key'];
                  $config     = $settings['fcm_config'];
                  if( $server_key !='' && $config !=''){
                    $firebasejs = $this->pwaforwp_firebase_js();  
                  }
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


		if( $is_amp ){
                        $firebasejs ='';
      if(pwaforwp_is_automattic_amp('amp_support') && function_exists('amp_get_permalink')){
        $offline_page   = amp_get_permalink( pwaforwp_https( $offline_page ) );
        $page404        = amp_get_permalink( pwaforwp_https( $page404 ) );
      }else{
        $offline_page   = pwaforwp_https( $offline_page ).'?amp=1';
        $page404        = pwaforwp_https( $page404 ).'?amp=1';    
      }
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
                                                        "{{REGEX}}",
                                                        "{{DEFAULT_CACHE_STRATEGY}}",
                                                        "{{CSS_JS_CACHE_STRATEGY}}",
                                                        "{{IMAGES_CACHE_STRATEGY}}",
                                                        "{{FONTS_CACHE_STRATEGY}}"
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
                                                         '/<amp-img[^>]+src="(https:\/\/[^">]+)"/g',
                                                         $defaultStrategy,
                                                         $cssjsStrategy,
                                                         $imageStrategy,
                                                         $fontStrategy
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
                                                            $swJsContent);                		
		} 
                    
                }                                          
          $swJsContent = apply_filters( 'pwaforwp_sw_js_template', $swJsContent );
          //fallback remove replacements    
          $swJsContent = str_replace(array(
                                    "{{formMessageData}}", 
                                    "{{fallbackPostRequest}}"
                                    ), array(
                                      "",
                                      "return caches.open(CACHE_VERSIONS.offline).then(function(cache) {
                                        return cache.match(OFFLINE_URL);
                                      });"
                                    ), $swJsContent); 		
	        return $swJsContent;
		
	}
      
    public function pwaforwp_manifest($is_amp = false){ 
        
    	$defaults = pwaforwp_defaultSettings();  
        
        if($is_amp){ 
          if(function_exists('ampforwp_url_controller')){
				      if(isset($defaults['start_page']) && $defaults['start_page'] !=0){
                $homeUrl = trailingslashit(get_permalink($defaults['start_page']));
                $homeUrl = ampforwp_url_controller( $homeUrl ) ;
              }else{
                $homeUrl = ampforwp_url_controller( pwaforwp_home_url() ) ;
                $homeUrl = trailingslashit($homeUrl);
              }
              $scope_url    = ampforwp_url_controller(pwaforwp_home_url());
          }elseif(function_exists('amp_is_available')){
            if ( AMP_Theme_Support::is_paired_available() ) {
              $homeUrl = add_query_arg( amp_get_slug(), '', pwaforwp_home_url() );
            } else {
              if ( ! amp_is_legacy() ) {
                $homeUrl = pwaforwp_home_url();
                if ( ! amp_is_canonical() ) {
                  $homeUrl = add_query_arg( amp_get_slug(), '', $homeUrl );
                }
              }else{
                $homeUrl = pwaforwp_home_url();
                if ( !amp_is_canonical() ) {
                    $parsed_url    = wp_parse_url($homeUrl);
                    $structure     = get_option( 'permalink_structure' );
                    $use_query_var = (
                        empty( $structure )
                        ||
                        ! empty( $parsed_url['query'] )
                      );
                    if ( $use_query_var ) {
                      $homeUrl = add_query_arg( amp_get_slug(), '', $homeUrl );
                    } else {
                      $homeUrl = preg_replace( '/#.*/', '', $homeUrl );
                      $homeUrl = trailingslashit( $homeUrl ) . user_trailingslashit( amp_get_slug(), 'single_amp' );
                      if ( ! empty( $parsed_url['fragment'] ) ) {
                        $homeUrl .= '#' . $parsed_url['fragment'];
                      }
                    }
                }
              }
            }
            $scope_url    = $homeUrl;
          } else {
            $homeUrl = amp_get_current_url();
            $homeUrl = trailingslashit(pwaforwp_home_url()).AMP_QUERY_VAR;
            if(isset($defaults['start_page']) && $defaults['start_page'] !=0 ){
              $homeUrl = trailingslashit(get_permalink($defaults['start_page'])).AMP_QUERY_VAR;
            }			
            $scope_url    = trailingslashit(pwaforwp_home_url()).AMP_QUERY_VAR;	
          }
          if(isset($defaults['utm_setting']) && $defaults['utm_setting']==1){
            $homeUrl = add_query_arg( array_filter($defaults['utm_details']),
              $homeUrl 
            );
          }                         
        } else {
          $homeUrl = pwaforwp_home_url(); 
          if(isset($defaults['start_page']) && $defaults['start_page'] !=0){
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
                    'purpose'=> 'any',
                );
                $icons[] = array(
                    'src'   => esc_url(pwaforwp_https($defaults['icon'])),
                    'sizes' => '192x192', 
                    'type'  => 'image/png', 
                    'purpose'=> 'maskable',
                );
                //Splash icon
                $icons[] = array(
                    'src' 	=> esc_url(pwaforwp_https($defaults['splash_icon'])),
                    'sizes'	=> '512x512', 
                    'type'	=> 'image/png', 
                    'purpose'=> 'any',
                );
                $icons[] = array(
                    'src'   => esc_url(pwaforwp_https($defaults['splash_icon'])),
                    'sizes' => '512x512', 
                    'type'  => 'image/png', 
                    'purpose'=> 'maskable',
                );

                                                             
                $manifest = array();
                                                
                $manifest['name']             = ($defaults['app_blog_name']);
                $manifest['short_name']       = ($defaults['app_blog_short_name']);
                $manifest['description']      = ($defaults['description']);
                $manifest['icons']            = $icons;
                $manifest['background_color'] = esc_attr($defaults['background_color']);
                $manifest['theme_color']      = esc_attr($defaults['theme_color']);
                $manifest['display']          = esc_html($display);
                $manifest['orientation']      = esc_html( $orientation );
                $manifest['start_url']        = esc_url_raw($homeUrl);
                $manifest['scope']            = esc_url_raw($scope_url);     

                if(isset($defaults['urlhandler_feature']) && $defaults['urlhandler_feature']==1 && isset($defaults['urlhandler']) && !empty($defaults['urlhandler'])){
                    $urls = explode("\n", $defaults['urlhandler']);
                    if(is_array($urls)){
                        foreach($urls as $url){
                            $manifest['url_handlers'][]['origin'] = trim($url);
                        }
                    }
                }                        
                
                $manifest = apply_filters( 'pwaforwp_manifest', $manifest );
		
                return json_encode($manifest, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE| JSON_PRETTY_PRINT);					
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


}