<?php
class pwaforwpFileCreation{
                
	public function pwaforwp_swhtml($is_amp = false){
            $settings                       = pwaforwp_defaultSettings();
	    if( $is_amp ){                
                        if(isset($settings['add_to_home_selector'])){
                  
                        if(strchr($settings['add_to_home_selector'], '#')){
                         $addtohomemanually    ='var a2hsBtn = document.getElementById("'.substr($settings['add_to_home_selector'], 1).'");		
                                                            a2hsBtn.addEventListener("click", (e) => {
                                                               addToHome();	
                                                            });';   
                        }
                        if(strchr($settings['add_to_home_selector'], '.')){
                           $addtohomemanually    ='var a2hsBtn = document.getElementsByClassName("'.substr($settings['add_to_home_selector'], 1).'");		
                                                            a2hsBtn.addEventListener("click", (e) => {
                                                               addToHome();	
                                                            });';  
                        }                                     
                       }else{
                        $addtohomemanually ='';
                       }                                        
                                            
                       $multisite_filename_postfix = '';
                        if ( is_multisite() ) {
                           $multisite_filename_postfix = '-' . get_current_blog_id();
                        }
                        $url 	                        = pwaforwp_front_url();
		        $ServiceWorkerfileName          = $url.'/pwa-amp-sw'.$multisite_filename_postfix;		
			$swHtmlContent 			= file_get_contents(PWAFORWP_PLUGIN_DIR."layouts/sw.html");
			$swHtmlContent 			= str_replace(array("{{serviceWorkerFile}}", "{{addtohomemanually}}"), array($ServiceWorkerfileName, $addtohomemanually), $swHtmlContent);
			return $swHtmlContent;		    
	    }	
	}
    
    public function pwaforwp_swr($is_amp = false){	
                $settings                       = pwaforwp_defaultSettings();
                $server_key                     = $settings['fcm_server_key'];
                $config                         = $settings['fcm_config'];
                
                if(isset($settings['add_to_home_selector'])){
                  
                 if(strchr($settings['add_to_home_selector'], '#')){
                  $addtohomemanually    ='var a2hsBtn = document.getElementById("'.substr($settings['add_to_home_selector'], 1).'");		
                                                     a2hsBtn.addEventListener("click", (e) => {
							addToHome();	
						     });';   
                 }
                 if(strchr($settings['add_to_home_selector'], '.')){
                    $addtohomemanually    ='var a2hsBtn = document.getElementsByClassName("'.substr($settings['add_to_home_selector'], 1).'");		
                                                     for (var i = 0; i < a2hsBtn.length; i++) {
                                                          a2hsBtn[i].addEventListener("click", addToHome); 
                                                        }';  
                 }                                     
                }else{
                 $addtohomemanually ='';
                }
                                                
		$url 	                        = pwaforwp_front_url();
                $multisite_filename_postfix = '';
                if ( is_multisite() ) {
                   $multisite_filename_postfix = '-' . get_current_blog_id();
                }
		$ServiceWorkerfileName 	        = $url.'/pwa-sw'.$multisite_filename_postfix;		
		$swHtmlContent 			= file_get_contents(PWAFORWP_PLUGIN_DIR."layouts/sw_non_amp.js");                                                               
                if($server_key !='' && $config !=''){
                 $firebaseconfig = 'var config ='.$config.'; '
                                                 .'if (!firebase.apps.length) {firebase.initializeApp(config);}		  		  		                                   							
					          const firebaseMessaging = firebase.messaging();';
                 $useserviceworker = 'firebaseMessaging.useServiceWorker(reg);';
                }else{
                 $firebaseconfig = '';  
                 $useserviceworker = '';
                }                                
		$swHtmlContent 			= str_replace(array("{{swfile}}", "{{config}}", "{{userserviceworker}}", "{{addtohomemanually}}"), array($ServiceWorkerfileName, $firebaseconfig, $useserviceworker, $addtohomemanually), $swHtmlContent);
		return $swHtmlContent;		    
    }
    
    public function pwaforwp_firebase_js(){	            
		$settings 		= pwaforwp_defaultSettings();                                
                $config = $settings['fcm_config'];
                
                $firebase_str = 'importScripts("https://www.gstatic.com/firebasejs/5.5.4/firebase-app.js");
                                 importScripts("https://www.gstatic.com/firebasejs/5.5.4/firebase-messaging.js");
                                 var config ='.$config.';
                                 if (!firebase.apps.length) {firebase.initializeApp(config);}		  		  		  
                                 const messaging = firebase.messaging();
                                 
                                 messaging.setBackgroundMessageHandler(function(payload) {  
                                 const notificationTitle = payload.data.title;
                                 const notificationOptions = {
                                                    body: payload.data.body,
                                                    icon: payload.data.icon,
                                                    vibrate: [100, 50, 100],
                                                    data: {
                                                        dateOfArrival: Date.now(),
                                                        primarykey: payload.data.primarykey,
                                                        url : payload.data.url
                                                      },
                                                    }
                                        return self.registration.showNotification(notificationTitle, notificationOptions); 

                                });
                                
                                self.addEventListener("notificationclose", function(e) {
                                var notification = e.notification;
                                var primarykey = notification.data.primarykey;
                                console.log("Closed notification: " + primarykey);
                                });
                                
                                self.addEventListener("notificationclick", function(e) {
                                    var notification = e.notification;
                                    var primarykey = notification.data.primarykey;
                                    var action = e.action;
                                    if (action === "close") {
                                      notification.close();
                                    } else {
                                      clients.openWindow(notification.data.url);
                                      notification.close();
                                    }
                                  });                                                                    
                                ';                            
		return $firebase_str;		    
    }
       
    public function pwaforwp_swjs($is_amp = false){
            
		$swJsContent 		= file_get_contents(PWAFORWP_PLUGIN_DIR."layouts/sw.js");
		$settings 		= pwaforwp_defaultSettings();
                if($settings['excluded_urls'] !=''){                   
                  $exclude_from_cache     = $settings['excluded_urls']; 
                  $exclude_from_cache     = str_replace('/', '\/', $exclude_from_cache);     
                  $exclude_from_cache     = '/'.str_replace(',', '/,/', $exclude_from_cache).'/'; 
                }else{
                  $exclude_from_cache     = '';   
                }
                $offline_google = '';
                if(isset($settings['offline_google_setting'])){
                $offline_google = 'importScripts("https://storage.googleapis.com/workbox-cdn/releases/3.6.1/workbox-sw.js");
                                    workbox.googleAnalytics.initialize();';    
                }
                
                
                
                $server_key = $settings['fcm_server_key'];
                $config = $settings['fcm_config'];
                if($server_key !='' && $config !=''){
                 $firebasejs = $this->pwaforwp_firebase_js();  
                }else{
                 $firebasejs = '';    
                }
                
		$offline_page 		= user_trailingslashit(get_permalink( $settings['offline_page'] ) ?  get_permalink( $settings['offline_page'] )  :  get_bloginfo( 'wpurl' ));
		$page404 		= user_trailingslashit(get_permalink( $settings['404_page'] ) ?  get_permalink( $settings['404_page'] ) : get_bloginfo( 'wpurl' ));  
		$site_url 		= user_trailingslashit(str_replace( 'http://', 'https://', site_url() ));  

		$cacheTimerHtml = 3600; $cacheTimerCss = 86400;
		if(isset($settings['cached_timer']) && is_numeric($settings['cached_timer']['html'])){
			$cacheTimerHtml = $settings['cached_timer']['html'];
		}
		if(isset($settings['cached_timer']) && is_numeric($settings['cached_timer']['css'])){
			$cacheTimerCss = $settings['cached_timer']['css'];
		}

		if( $is_amp ){
                        $firebasejs ='';
			$offline_page 	= str_replace( 'http://', 'https://', $offline_page ).'?amp=1';
			$page404 		= str_replace( 'http://', 'https://', $page404 ).'?amp=1';  
			$swJsContent 	= str_replace(array(
							"{{OFFLINE_PAGE}}", "{{404_PAGE}}", "{{CACHE_VERSION}}","{{SITE_URL}}", "{{HTML_CACHE_TIME}}","{{CSS_CACHE_TIME}}", "{{FIREBASEJS}}" , "{{EXCLUDE_FROM_CACHE}}", "{{OFFLINE_GOOGLE}}"), 
							array($offline_page, $page404, PWAFORWP_PLUGIN_VERSION, $site_url, $cacheTimerHtml, $cacheTimerCss, $firebasejs, $exclude_from_cache, $offline_google),
							 $swJsContent);                		
		} else {
			$offline_page 	= str_replace( 'http://', 'https://', $offline_page );
			$page404 		= str_replace( 'http://', 'https://', $page404 );    
			$swJsContent 	= str_replace(array("{{OFFLINE_PAGE}}", "{{404_PAGE}}", "{{CACHE_VERSION}}","{{SITE_URL}}", "{{HTML_CACHE_TIME}}","{{CSS_CACHE_TIME}}", "{{FIREBASEJS}}", "{{EXCLUDE_FROM_CACHE}}", "{{OFFLINE_GOOGLE}}"),
                                                      array($offline_page, $page404, PWAFORWP_PLUGIN_VERSION, $site_url, $cacheTimerHtml, $cacheTimerCss, $firebasejs, $exclude_from_cache, $offline_google), $swJsContent);                		
		}                		
	    return $swJsContent;
		
	}
      
    public function pwaforwp_manifest($is_amp = false){                        
    	$defaults = pwaforwp_defaultSettings();

        if($is_amp){ 
            if(function_exists('ampforwp_url_controller')){
				$homeUrl = ampforwp_url_controller( get_home_url() ) ;
				if(isset($defaults['utm_setting']) && $defaults['utm_setting']==1){
					$homeUrl = $homeUrl."?".http_build_query(array_filter($defaults['utm_details']));
				}
			} else {
				$homeUrl = get_home_url().AMP_QUERY_VAR;
				if(isset($defaults['utm_setting']) && $defaults['utm_setting']==1){
					$homeUrl = $homeUrl."?".http_build_query(array_filter($defaults['utm_details']));
				}
			}
        } else {
            $homeUrl = get_home_url(); 
            if(isset($defaults['utm_setting']) && $defaults['utm_setting']==1){
	            $homeUrl = $homeUrl."?".http_build_query(array_filter($defaults['utm_details']));
	        }
        }                                            
                $homeUrl = user_trailingslashit(str_replace("http://", "https://", $homeUrl));
		$orientation 	= isset($defaults['orientation']) && $defaults['orientation']!='' ?  $defaults['orientation'] : "portrait";

		if($orientation==0) { $orientation = "portrait"; }
		$manifest = '{			  
			"name": "'.esc_attr($defaults['app_blog_name']).'",
			"short_name": "'.esc_attr($defaults['app_blog_short_name']).'",
			"description": "'.esc_attr($defaults['description']).'",
			"icons": [
			    {
			      "src": "'.esc_url($defaults['icon']).'",
			      "sizes": "192x192",
			      "type": "image\/png"
			    },
			    {
			      "src": "'.esc_url($defaults['splash_icon']).'",
			      "sizes": "512x512",
			      "type": "image\/png"
			    }
			],
			"background_color": "'.sanitize_hex_color($defaults['background_color']).'",
			"theme_color": "'.sanitize_hex_color($defaults['theme_color']).'",
			"display": "standalone",
			"orientation": "'.esc_html( $orientation ).'",
			"start_url": "'.esc_url($homeUrl).'",
			"scope": "'.esc_url($homeUrl).'"
		}';
		return $manifest;				
	}        
}