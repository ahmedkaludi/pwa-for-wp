<?php
/**
Plugin Name: AMP Service workers
Description: AMP Service Worker
Author: AMP For WP
Version: 0.0.1
Author URI: https://ampforwp.com
Plugin URI: https://ampforwp.com
Text Domain: amp-service-worker
Domain Path: /languages/
SKU: PWA
 *
 * The main plugin file
 *
 */
 
	define('AMPFORWP_SERVICEWORKER_PLUGIN_DIR', plugin_dir_path( __FILE__ ));
	define('AMPFORWP_SERVICEWORKER_PLUGIN_URL', plugin_dir_url( __FILE__ ));
	define('AMPFORWP_SERVICEWORKER_PLUGIN_VERSION', '0.0.1');



	class AMPFORWP_PWA{
		public $wppath;
		public function __construct(){
			$this->wppath = str_replace("//","/",str_replace("\\","/",realpath(ABSPATH))."/");
		}

		public function init(){
			require_once AMPFORWP_SERVICEWORKER_PLUGIN_DIR."service-work/serviceworker-class.php";

			require_once AMPFORWP_SERVICEWORKER_PLUGIN_DIR."admin/common-function.php";
			if( ampforwp_pwa_is_admin() ){
				require_once AMPFORWP_SERVICEWORKER_PLUGIN_DIR."admin/settings.php";
			}

			$serviceWorker = new ServiceWorker();
			$serviceWorker->init();
			//On Activate
			register_activation_hook( __FILE__, array($serviceWorker, 'ampforwp_rewrite_rules_custom_rewrite' ) );
			//On Deactivate
			register_deactivation_hook( __FILE__, array($serviceWorker, 'ampforwp_remove_rewrite_rules_custom_rewrite' ) );


			//Minifest  work
			add_action('amp_post_template_head',array($this, 'ampforwp_paginated_post_add_homescreen'),9);

			//manifest.json
			add_action( 'wp_ajax_ampforwp_pwa_wp_manifest', array($this, 'ampforwp_pwa_wp_manifest') );
			add_action( 'wp_ajax_nopriv_ampforwp_pwa_wp_manifest', array($this, 'ampforwp_pwa_wp_manifest') );
		}

		function ampforwp_pwa_wp_manifest(){
			$defaults = defaultSettings();
			echo '{
			  "name": "'.$defaults['app_blog_name'].'",
			  "short_name": "'.$defaults['app_blog_short_name'].'",
			  "description": "'.$defaults['description'].'",
			  "icons": [
			    {
			      "src": "'.$defaults['icon'].'",
			      "sizes": "192x192",
			      "type": "image\/png"
			    },
			    {
			      "src": "'.$defaults['splash_icon'].'",
			      "sizes": "512x512",
			      "type": "image\/png"
			    }
			  ],
			  "background_color": "'.$defaults['background_color'].'",
			  "theme_color": "'.$defaults['theme_color'].'",
			  "display": "standalone",
			  "orientation": "'.( isset($defaults['orientation']) && $defaults['orientation']!='' ?  $defaults['orientation'] : "portrait" ).'",
			  "start_url": ".",
			  "scope": "\/"
			}';
			wp_die();
		}

		function ampforwp_paginated_post_add_homescreen(){
			echo '<link rel="manifest" href="./manifest.json">';
		}

	}//Class closed

//echo "test";die;
$pwa = new AMPFORWP_PWA();
$pwa->init();