<?php
class pwaforwpFileCreation{
                
	public function pwaforwp_swhtml($is_amp = false){	            
	    if( $is_amp ){
		    $ServiceWorkerfileName 	= 'pwa-amp-sw';		
			$swHtmlContent 			= file_get_contents(PWAFORWP_PLUGIN_DIR."layouts/sw.html");
			$swHtmlContent 			= str_replace("{{serviceWorkerFile}}", $ServiceWorkerfileName, $swHtmlContent);
			return $swHtmlContent;		    
	    }	
	}
    
    public function pwaforwp_swr($is_amp = false){	            
		$url 					= str_replace("http:","https:",site_url());
		$ServiceWorkerfileName 	= $url.'/pwa-sw';		
		$swHtmlContent 			= file_get_contents(PWAFORWP_PLUGIN_DIR."layouts/sw_non_amp.js");
		$swHtmlContent 			= str_replace("{{swfile}}", $ServiceWorkerfileName, $swHtmlContent);
		return $swHtmlContent;		    
	}		        
       
    public function pwaforwp_swjs($is_amp = false){
            
		$swJsContent 		= file_get_contents(PWAFORWP_PLUGIN_DIR."layouts/sw.js");
		$settings 			= pwaforwp_defaultSettings();		
		$offline_page 		= get_permalink( $settings['offline_page'] ) ?  get_permalink( $settings['offline_page'] )  :  get_bloginfo( 'wpurl' );
		$page404 			= get_permalink( $settings['404_page'] ) ?  get_permalink( $settings['404_page'] ) : get_bloginfo( 'wpurl' );  
		$site_url 		= str_replace( 'http://', 'https://', site_url() );  

		$cacheTimerHtml = 3600; $cacheTimerCss = 86400;
		if(isset($settings['cached_timer']) && is_numeric($settings['cached_timer']['html'])){
			$cacheTimerHtml = $settings['cached_timer']['html'];
		}
		if(isset($settings['cached_timer']) && is_numeric($settings['cached_timer']['css'])){
			$cacheTimerCss = $settings['cached_timer']['css'];
		}

		if( $is_amp ){
			$offline_page 	= str_replace( 'http://', 'https://', $offline_page ).'?amp=1';
			$page404 		= str_replace( 'http://', 'https://', $page404 ).'?amp=1';  
			$swJsContent 	= str_replace(array(
							"{{OFFLINE_PAGE}}", "{{404_PAGE}}", "{{CACHE_VERSION}}","{{SITE_URL}}", "{{HTML_CACHE_TIME}}","{{CSS_CACHE_TIME}}"), 
							array($offline_page, $page404, PWAFORWP_PLUGIN_VERSION, $site_url, $cacheTimerHtml, $cacheTimerCss),
							 $swJsContent);                		
		} else {
			$offline_page 	= str_replace( 'http://', 'https://', $offline_page );
			$page404 		= str_replace( 'http://', 'https://', $page404 );    
			$swJsContent 	= str_replace(array("{{OFFLINE_PAGE}}", "{{404_PAGE}}", "{{CACHE_VERSION}}","{{SITE_URL}}", "{{HTML_CACHE_TIME}}","{{CSS_CACHE_TIME}}"), array($offline_page, $page404, PWAFORWP_PLUGIN_VERSION, $site_url, $cacheTimerHtml, $cacheTimerCss), $swJsContent);                		
		}                		
	    return $swJsContent;
		
	}
      
    public function pwaforwp_manifest($is_amp = false){                        
    	$defaults = pwaforwp_defaultSettings();

        if($is_amp){ 
            if(function_exists('ampforwp_url_controller')){
				$homeUrl = ampforwp_url_controller( get_home_url() ) ;
				if($defaults['utm_setting'] && $defaults['utm_setting']==1){
					$homeUrl = $homeUrl."?".http_build_query($defaults['utm_details']);
				}
			} else {
				$homeUrl = get_home_url().AMP_QUERY_VAR;
				if($defaults['utm_setting'] && $defaults['utm_setting']==1){
					$homeUrl = $homeUrl."?".http_build_query($defaults['utm_details']);
				}
			}
        } else {
            $homeUrl = get_home_url(); 
            if($defaults['utm_setting'] && $defaults['utm_setting']==1){
	            $homeUrl = $homeUrl."?".http_build_query($defaults['utm_details']);
	        }
        }                                            
        $homeUrl 		= str_replace("http://", "https://", $homeUrl);
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