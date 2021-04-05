<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

class PWAforWP_wppwa{
	
	const INSTALL_SERVICE_WORKER_AMP_QUERY_VAR = 'pwa_amp_install_service_worker_iframe';
	function init(){
		if(class_exists('WP_Web_App_Manifest')){
			$wp_web_app_manifest = new WP_Web_App_Manifest();
			add_action( 'amp_post_template_head', array( $wp_web_app_manifest, 'manifest_link_and_meta' ) );

		
			add_filter("web_app_manifest", array($this, 'manifest_submit'), 10, 1);
			add_action( 'wp_front_service_worker', array( $this, 'add_cdn_script_caching' ) );
			add_action("wp", array($this, 'remove_all_pwa_wp_actions'));
			if(defined('AMPFORWP_VERSION')){
				add_filter( 'query_vars', [ __CLASS__, 'add_query_var' ] );
				
				add_action( 'parse_request', [ __CLASS__, 'handle_service_worker_iframe_install' ] );
				add_action( 'wp', [ __CLASS__, 'add_install_hooks' ] );
				add_action( 'wp_front_service_worker', [ __CLASS__, 'add_amp_cdn_script_caching' ] );
			}
		}
	}
	public function remove_all_pwa_wp_actions(){
		foreach ( [ 'wp_print_scripts', 'wp_print_footer_scripts' ] as $action ) {
				$priority = has_action( $action, 'wp_print_service_workers' );
				if ( false !== $priority ) {
					remove_action( $action, 'wp_print_service_workers', $priority );
				}
			}

		add_filter( 'wp_print_scripts', array($this, 'wp_print_service_workers'), 9 );
	}
	public static function add_query_var( $vars ) {
		$vars[] = self::INSTALL_SERVICE_WORKER_AMP_QUERY_VAR;
		return $vars;
	}
	
	public static function add_install_hooks() {
		// Reader mode integration.
		add_action( 'amp_post_template_footer', [ __CLASS__, 'install_service_worker' ] );
		add_filter(
			'amp_post_template_data',
			static function ( $data ) {
				$data['amp_component_scripts']['amp-install-serviceworker'] = 'https://cdn.ampproject.org/v0/amp-install-serviceworker-latest.js';
				return $data;
			}
		);
	}
	public static function install_service_worker() {
		if ( ! function_exists( 'wp_service_workers' ) || ! function_exists( 'wp_get_service_worker_url' ) ) {
			return;
		}

		$src        = wp_get_service_worker_url( WP_Service_Workers::SCOPE_FRONT );
		$iframe_src = add_query_arg(
			self::INSTALL_SERVICE_WORKER_AMP_QUERY_VAR,
			WP_Service_Workers::SCOPE_FRONT,
			home_url( '/', 'https' )
		);
		?>
		<amp-install-serviceworker
			src="<?php echo esc_url( $src ); ?>"
			data-iframe-src="<?php echo esc_url( $iframe_src ); ?>"
			layout="nodisplay"
		>
		</amp-install-serviceworker>
		<?php
	}
	public static function handle_service_worker_iframe_install() {
		if ( ! isset( $GLOBALS['wp']->query_vars[ self::INSTALL_SERVICE_WORKER_AMP_QUERY_VAR ] ) ) {
			return;
		}

		$scope = (int) $GLOBALS['wp']->query_vars[ self::INSTALL_SERVICE_WORKER_AMP_QUERY_VAR ];
		if ( WP_Service_Workers::SCOPE_ADMIN !== $scope && WP_Service_Workers::SCOPE_FRONT !== $scope ) {
			wp_die(
				esc_html__( 'No service workers registered for the requested scope.', 'amp' ),
				esc_html__( 'Service Worker Installation', 'amp' ),
				[ 'response' => 404 ]
			);
		}

		$front_scope = home_url( '/amp', 'relative' );

		?>
		<!DOCTYPE html>
		<html>
			<head>
				<meta charset="utf-8">
				<title><?php esc_html_e( 'Service Worker Installation', 'amp' ); ?></title>
			</head>
			<body>
				<?php esc_html_e( 'Installing service worker...', 'amp' ); ?>
				<?php
				printf(
					'<script>navigator.serviceWorker.register( %s, %s );</script>',
					wp_json_encode( wp_get_service_worker_url( $scope ) ),
					wp_json_encode( [ 'scope' => $front_scope ] )
				);
				?>
			</body>
		</html>
		<?php

		// Die in a way that can be unit tested.
		add_filter(
			'wp_die_handler',
			static function() {
				return static function() {
					die();
				};
			},
			1
		);
		wp_die();
	}

	function add_cdn_script_caching( $service_workers ){
		if ( ! ( $service_workers instanceof WP_Service_Worker_Scripts ) ) {
			/* translators: %s: WP_Service_Worker_Cache_Registry. */
			_doing_it_wrong( __METHOD__, sprintf( esc_html__( 'Please update to PWA v0.2. Expected argument to be %s.', 'pwa-forwp' ), 'WP_Service_Worker_Cache_Registry' ), '1.1' );
			return;
		}
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


		    if( ( function_exists('ampforwp_is_amp_endpoint') && ampforwp_is_amp_endpoint() ) || ( function_exists('is_amp_endpoint') && is_amp_endpoint() ) ){
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
		}

        $service_workers->register(
			'pwaforwp-runtime-caching',
			static function() use ($swJsContent) {
				
				return "{".$swJsContent."}";
			}
		);
	}

	/**
	* Add the fire base contents in PWA js
	*/
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

    /**
    * To grab the Template files content use this function
    * @filePath is the name of file path after plugin folder
    */
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

	/**
	* Updated Manifest of PWA-WP 
	* Added Our custom options value related with option panel 
	*/
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
        $manifest['background_color'] = esc_attr($defaults['background_color']);
        $manifest['theme_color']      = esc_attr($defaults['theme_color']);
        $manifest['display']          = esc_html($display);
        $manifest['orientation']      = esc_html( $orientation );
        $manifest['start_url']        = esc_url($homeUrl);
        $manifest['scope']            = esc_url($scope_url);
        
        $manifest = apply_filters( 'pwaforwp_manifest', $manifest );

		return $manifest;
	}

	public function wp_print_service_workers() {
		/*
		 * Similar to PWA-WP but Removed serviceworker registeraation in admin section 
		 * because its not shows live saved data
		 * and cause issue in admin-login screen 
		 */
		if ( is_embed() ) {
			return;
		}

		global $pagenow;
		$scopes = array();

		$home_port  = wp_parse_url( home_url(), PHP_URL_PORT );
		$admin_port = wp_parse_url( admin_url(), PHP_URL_PORT );

		$home_host  = wp_parse_url( home_url(), PHP_URL_HOST );
		$admin_host = wp_parse_url( admin_url(), PHP_URL_HOST );

		$home_url  = ( $home_port ) ? "$home_host:$home_port" : $home_host;
		$admin_url = ( $admin_port ) ? "$admin_host:$admin_port" : $admin_host;

		$on_front_domain = isset( $_SERVER['HTTP_HOST'] ) && $home_url === $_SERVER['HTTP_HOST'];
		$on_admin_domain = isset( $_SERVER['HTTP_HOST'] ) && $admin_url === $_SERVER['HTTP_HOST'];

		// Install the front service worker if currently on the home domain.
		if ( $on_front_domain ) {
			$scopes[ WP_Service_Workers::SCOPE_FRONT ] = home_url( '/', 'relative' ); // The home_url() here will account for subdirectory installs.
		}
		if( ( function_exists('ampforwp_is_amp_endpoint') && ampforwp_is_amp_endpoint() ) || ( function_exists('is_amp_endpoint') && is_amp_endpoint() ) ){
			$scopes[ WP_Service_Workers::SCOPE_FRONT.'/amp' ] = home_url( '/amp', 'relative' ); // The 
		}

		if ( empty( $scopes ) ) {
			return;
		}

		?>
		<script>
			if ( navigator.serviceWorker ) {
				window.addEventListener( 'load', function() {
					<?php foreach ( $scopes as $name => $scope ) : ?>
						{
							let updatedSw;
							navigator.serviceWorker.register(
								<?php echo wp_json_encode( wp_get_service_worker_url( $name ) ); ?>,
								<?php echo wp_json_encode( compact( 'scope' ) ); ?>
							).then( reg => {
								<?php if ( WP_Service_Workers::SCOPE_ADMIN === $name ) : ?>
									document.cookie = <?php echo wp_json_encode( sprintf( 'wordpress_sw_installed=1; path=%s; expires=Fri, 31 Dec 9999 23:59:59 GMT; secure; samesite=strict', $scope ) ); ?>;
								<?php endif; ?>
								<?php if ( ! wp_service_worker_skip_waiting() ) : ?>
									reg.addEventListener( 'updatefound', () => {
										if ( ! reg.installing ) {
											return;
										}
										updatedSw = reg.installing;

										/* If new service worker is available, show notification. */
										updatedSw.addEventListener( 'statechange', () => {
											if ( 'installed' === updatedSw.state && navigator.serviceWorker.controller ) {
												const notification = document.getElementById( 'wp-admin-bar-pwa-sw-update-notice' );
												if ( notification ) {
													notification.style.display = 'block';
												}
											}
										} );
									} );
								<?php endif; ?>
							} );

							<?php if ( is_admin_bar_showing() && ! wp_service_worker_skip_waiting() ) : ?>
								/* Post message to Service Worker for skipping the waiting phase. */
								const reloadBtn = document.getElementById( 'wp-admin-bar-pwa-sw-update-notice' );
								if ( reloadBtn ) {
									reloadBtn.addEventListener( 'click', ( event ) => {
										event.preventDefault();
										if ( updatedSw ) {
											updatedSw.postMessage( { action: 'skipWaiting' } );
										}
									} );
								}
							<?php endif; ?>
						}
					<?php endforeach; ?>

					let refreshedPage = false;
					navigator.serviceWorker.addEventListener( 'controllerchange', () => {
						if ( ! refreshedPage ) {
							refreshedPage = true;
							window.location.reload();
						}
					} );
				} );
			}
		</script>
		<?php
	}
}
$PWAforWP_wppwa = new PWAforWP_wppwa();
$PWAforWP_wppwa->init();