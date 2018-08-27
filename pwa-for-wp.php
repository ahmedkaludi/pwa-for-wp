<?php
/**
Plugin Name: PWA for WP
Description: We are bringing the power of the Progressive Web Apps to the WP & AMP to take the user experience to the next level!
Author: Ahmed Kaludi, Mohammed Kaludi
Version: 1.0.1
Author URI: https://ampforwp.com
Text Domain: pwa-for-wp
Domain Path: /languages/
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

define('PWAFORWP_PLUGIN_DIR', plugin_dir_path( __FILE__ ));
define('PWAFORWP_PLUGIN_URL', plugin_dir_url( __FILE__ ));
define('PWAFORWP_PLUGIN_VERSION', '1.0.1');
define('PWAFORWP_FILE_PREFIX', 'pwa');
        
require_once PWAFORWP_PLUGIN_DIR."/admin/common-function.php"; 
require_once PWAFORWP_PLUGIN_DIR."/admin/newsletter.php"; 
require_once PWAFORWP_PLUGIN_DIR."/service-work/class-service-worker.php"; 
require_once PWAFORWP_PLUGIN_DIR."/service-work/class-file-creation.php";
require_once PWAFORWP_PLUGIN_DIR."/service-work/class-init.php"; 

      
if( pwaforwp_is_admin() ){
	add_filter( 'plugin_action_links_' . plugin_basename(__FILE__),'pwaforwp_add_action_links');
	require_once PWAFORWP_PLUGIN_DIR."admin/settings.php";
}
function pwaforwp_add_action_links($links){
    $mylinks = array('<a href="' . admin_url( 'admin.php?page=pwaforwp' ) . '">'.esc_html__( 'Settings', 'pwa-for-wp' ).'</a>');
    return array_merge( $links, $mylinks );
}



//For CDN CODES
if ( !is_admin() ) { 
	$settings = pwaforwp_defaultSettings(); 
		if(isset($settings['cdn_setting']) && $settings['cdn_setting']==1){
			ob_start('pwaforwp_revert_src');
		}
	}
function pwaforwp_revert_src($content){

	$url = str_replace("http:","https:",site_url()); 
	$content = preg_replace_callback("/src=\"(.*?)".PWAFORWP_FILE_PREFIX."-register-sw\.js\"/i",  'pwaforwp_cdn_replace_urls_revert', $content);
	$content = preg_replace_callback("/href=\"(.*?)".PWAFORWP_FILE_PREFIX."-manifest\.json\"/i",  'pwaforwp_cdn_replace_urls_revert_manifest', $content);
	return $content;
}
function pwaforwp_cdn_replace_urls_revert($src){
	$url = str_replace("http:","https:",site_url());    
	if($src[1]==$url){
		return 'src="'.$src.'/'.PWAFORWP_FILE_PREFIX.'-register-sw.js"';
	}else{
		return 'src="'.$url.'/'.PWAFORWP_FILE_PREFIX.'-register-sw.js"';
	}
}
function pwaforwp_cdn_replace_urls_revert_manifest($src){
    $url = str_replace("http:","https:",site_url());    
	if($src[1]==$url){
		return 'href="'.$src.'/'.PWAFORWP_FILE_PREFIX.'-manifest.json"';
	}else{
		return 'href="'.$url.'/'.PWAFORWP_FILE_PREFIX.'-manifest.json"';
	}
}
/**
 * set user defined message on plugin activate
 */
register_activation_hook( __FILE__, 'pwaforwp_admin_notice_activation_hook' );
function pwaforwp_admin_notice_activation_hook() {
    set_transient( 'pwaforwp_admin_notice_transient', true, 5 );
}
add_action( 'admin_notices', 'pwaforwp_admin_notice' );

function pwaforwp_admin_notice(){
    /* Check transient, if available display notice */
    if( get_transient( 'pwaforwp_admin_notice_transient' ) ){
        ?>
        <div class="updated notice is-dismissible">
            <p><?php echo esc_html__('Thank you for using this plugin! Setup the plugin.', 'pwa-for-wp') ?> <a href="<?php echo esc_url(admin_url( 'admin.php?page=pwaforwp' )) ?>"> <?php echo esc_html__('Click here', 'pwa-for-wp') ?></a></p>
        </div>
        <?php
        /* Delete transient, only display this notice once. */
        delete_transient( 'pwaforwp_admin_notice_transient' );
    }
}