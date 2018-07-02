<?php
/**
Plugin Name: PWA for WordPress
Description: PWA for WordPress
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
 
	define('AMPFORWP_PWA_PLUGIN_DIR', plugin_dir_path( __FILE__ ));
	define('AMPFORWP_PWA_PLUGIN_URL', plugin_dir_url( __FILE__ ));
	define('AMPFORWP_PWA_PLUGIN_VERSION', '0.0.1');

	// this is the URL our updater / license checker pings. This should be the URL of the site with EDD installed
	define( 'AMP_PWA_STORE_URL', 'https://accounts.ampforwp.com/' ); // you should use your own CONSTANT name, and be sure to replace it throughout this file

	// the name of your product. This should match the download name in EDD exactly
	define( 'AMP_PWA_ITEM_NAME', 'PWA For WordPress' );

	// the download ID. This is the ID of your product in EDD and should match the download ID visible in your Downloads list (see example below)
	//define( 'AMPFORWP_ITEM_ID', 2502 );
	// the name of the settings page for the license input to be displayed
	define( 'AMP_PWA_LICENSE_PAGE', 'pwa-for-wordpress' );
	if(! defined('AMP_PWA_ITEM_FOLDER_NAME')){
	    $folderName = basename(__DIR__);
	    define( 'AMP_PWA_ITEM_FOLDER_NAME', $folderName );
	}



	class AMPFORWP_PWA{
		public $wppath;
		public function __construct(){
			$this->wppath = str_replace("//","/",str_replace("\\","/",realpath(ABSPATH))."/");
		}

		public function init(){
			require_once AMPFORWP_PWA_PLUGIN_DIR."service-work/serviceworker-class.php";

			$serviceWorker = new ServiceWorker();
			$serviceWorker->init();

			require_once AMPFORWP_PWA_PLUGIN_DIR."admin/common-function.php";
			if( ampforwp_pwa_is_admin() ){
				add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array($this, 'amppwa_add_action_links') );
				require_once AMPFORWP_PWA_PLUGIN_DIR."admin/settings.php";
			}

			
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

		function amppwa_add_action_links($links){
			$mylinks = array(
			 '<a href="' . admin_url( 'admin.php?page=ampforwp-pwa' ) . '">Settings</a>',
			 );
			return array_merge( $links, $mylinks );
		}

		function ampforwp_pwa_wp_manifest(){
			$defaults = ampforwp_pwa_defaultSettings();
			header('Content-Type: application/json');
			echo '{
			  "short_name": "'.esc_attr($defaults['app_blog_short_name']).'",
			  "name": "'.esc_attr($defaults['app_blog_name']).'",
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
			  "background_color": "'.esc_html($defaults['background_color']).'",
			  "theme_color": "'.esc_html($defaults['theme_color']).'",
			  "display": "standalone",
			  "orientation": "'.esc_html( isset($defaults['orientation']) && $defaults['orientation']!='' ?  $defaults['orientation'] : "portrait" ).'",
			  "start_url": ".",
			  "scope": "\/"
			}';
			wp_die();
		}

		function ampforwp_paginated_post_add_homescreen(){
			$url = str_replace("http:","https:",site_url());	
			echo '<link rel="manifest" href="'. esc_url($url.'/manifest.json').'">';
		}

	}//Class closed

$pwa = new AMPFORWP_PWA();
$pwa->init();






/***
* License Activation code
***/

require_once dirname( __FILE__ ) . '/updater/EDD_SL_Plugin_Updater.php';

// Check for updates
function pwa_for_wp_plugin_updater() {

	// retrieve our license key from the DB
	//$license_key = trim( get_option( 'amp_ads_license_key' ) );
	$selectedOption = get_option('redux_builder_amp',true);
    $license_key = '';//trim( get_option( 'amp_ads_license_key' ) );
    $pluginItemName = '';
    $pluginItemStoreUrl = '';
    $pluginstatus = '';
    if( isset($selectedOption['amp-license']) && "" != $selectedOption['amp-license'] && isset($selectedOption['amp-license'][AMP_PWA_ITEM_FOLDER_NAME])){

       $pluginsDetail = $selectedOption['amp-license'][AMP_PWA_ITEM_FOLDER_NAME];
       $license_key = $pluginsDetail['license'];
       $pluginItemName = $pluginsDetail['item_name'];
       $pluginItemStoreUrl = $pluginsDetail['store_url'];
       $pluginstatus = $pluginsDetail['status'];
    }
	
	// setup the updater
	$edd_updater = new AMP_PWA_EDD_SL_Plugin_Updater( AMP_PWA_STORE_URL, __FILE__, array(
			'version' 	=> AMPFORWP_PWA_PLUGIN_VERSION, 				// current version number
			'license' 	=> $license_key, 						// license key (used get_option above to retrieve from DB)
			'license_status'=>$pluginstatus,
			'item_name' => AMP_PWA_ITEM_NAME, 			// name of this plugin
			'author' 	=> 'Mohammed Kaludi',  					// author of this plugin
			'beta'		=> false,
		)
	);
}
add_action( 'admin_init', 'pwa_for_wp_plugin_updater', 0 );

// Notice to enter license key once activate the plugin

$path = plugin_basename( __FILE__ );
	add_action("after_plugin_row_{$path}", function( $plugin_file, $plugin_data, $status ) {
		global $redux_builder_amp;
		if(! defined('AMP_PWA_ITEM_FOLDER_NAME')){
	    $folderName = basename(__DIR__);
            define( 'AMP_PWA_ITEM_FOLDER_NAME', $folderName );
        }
        $pluginsDetail = @$redux_builder_amp['amp-license'][AMP_PWA_ITEM_FOLDER_NAME];
        $pluginstatus = @$pluginsDetail['status'];

        if(empty($redux_builder_amp['amp-license'][AMP_PWA_ITEM_FOLDER_NAME]['license'])){
			echo "<tr class='active'><td>&nbsp;</td><td colspan='2'><a href='".esc_url(  self_admin_url( 'admin.php?page=amp_options&tabid=opt-go-premium' )  )."'>Please enter the license key</a> to get the <strong>latest features</strong> and <strong>stable updates</strong></td></tr>";
			   }elseif($pluginstatus=="valid"){
			   	$update_cache = get_site_transient( 'update_plugins' );
            $update_cache = is_object( $update_cache ) ? $update_cache : new stdClass();
            if(isset($update_cache->response[ AMP_PWA_ITEM_FOLDER_NAME ]) 
                && empty($update_cache->response[ AMP_PWA_ITEM_FOLDER_NAME ]->download_link) 
              ){
               unset($update_cache->response[ AMP_PWA_ITEM_FOLDER_NAME ]);
            }
            set_site_transient( 'update_plugins', $update_cache );
            
        }
    }, 10, 3 );