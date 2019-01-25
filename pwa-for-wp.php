<?php
/**
Plugin Name: PWA for WP
Description: We are bringing the power of the Progressive Web Apps to the WP & AMP to take the user experience to the next level!
Author: Ahmed Kaludi, Mohammed Kaludi
Version: 1.0.6
Author URI: http://pwa-for-wp.com
Text Domain: pwa-for-wp
Domain Path: /languages
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

define('PWAFORWP_PLUGIN_DIR', plugin_dir_path( __FILE__ ));
define('PWAFORWP_PLUGIN_URL', plugin_dir_url( __FILE__ ));
define('PWAFORWP_PLUGIN_VERSION', '1.0.6');
define('PWAFORWP_FILE_PREFIX', 'pwa');
        
require_once PWAFORWP_PLUGIN_DIR."/admin/common-function.php"; 
require_once PWAFORWP_PLUGIN_DIR."/admin/newsletter.php"; 
require_once PWAFORWP_PLUGIN_DIR."/service-work/class-service-worker.php"; 
require_once PWAFORWP_PLUGIN_DIR."/service-work/class-file-creation.php";
require_once PWAFORWP_PLUGIN_DIR."/service-work/class-init.php"; 
require_once PWAFORWP_PLUGIN_DIR."/service-work/class-push-notification.php"; 

      
if( pwaforwp_is_admin() ){
	add_filter( 'plugin_action_links_' . plugin_basename(__FILE__),'pwaforwp_add_action_links');
	require_once PWAFORWP_PLUGIN_DIR."admin/settings.php";
}
function pwaforwp_add_action_links($links){
    $mylinks = array('<a href="' . admin_url( 'admin.php?page=pwaforwp' ) . '">'.esc_html__( 'Settings', 'pwa-for-wp' ).'</a>');
    return array_merge( $links, $mylinks );
}
//For CDN CODES
//add_action("wp_loaded", 'pwaforwp_allow_cdn',999);
//function pwaforwp_allow_cdn(){
	if ( !is_admin() ) { 
            
		$settings = pwaforwp_defaultSettings(); 
			if(isset($settings['cdn_setting']) && $settings['cdn_setting']==1){
				ob_start('pwaforwp_revert_src');
		}
	}
//}

function pwaforwp_amp_cdn_replace_urls_revert($src){
	$url = pwaforwp_front_url();
	$multisite_filename_postfix = '';
    if ( is_multisite() ) {
       $multisite_filename_postfix = '-' . get_current_blog_id();
    }
	if($src[1]==$url){
		return 'src="'.$url.PWAFORWP_FILE_PREFIX.'-amp-sw'.$multisite_filename_postfix.'.js"';
	}else{
		return 'src="'.$url.PWAFORWP_FILE_PREFIX.'-amp-sw'.$multisite_filename_postfix.'.js"';
	}
}
function pwaforwp_amp_cdn_replace_urls_revert_manifest($src){
    $url = pwaforwp_front_url();    
    $multisite_filename_postfix = '';
    if ( is_multisite() ) {
       $multisite_filename_postfix = '-' . get_current_blog_id();
    }
	if($src[1]==$url){
		return 'href="'.$url.PWAFORWP_FILE_PREFIX.'-amp-manifest'.$multisite_filename_postfix.'.json"';
	}else{
		return 'href="'.$url.PWAFORWP_FILE_PREFIX.'-amp-manifest'.$multisite_filename_postfix.'.json"';
	}
}
function pwaforwp_amp_cdn_replace_urls_revert_manifest_with_rel($src){
    $url = pwaforwp_front_url(); 
    $multisite_filename_postfix = '';
    if ( is_multisite() ) {
       $multisite_filename_postfix = '-' . get_current_blog_id();
    }   
	if($src[1]==$url){
		return ' rel="manifest" href="'.$url.PWAFORWP_FILE_PREFIX.'-amp-manifest'.$multisite_filename_postfix.'.json"';
	}else{
		return ' rel="manifest" href="'.$url.PWAFORWP_FILE_PREFIX.'-amp-manifest'.$multisite_filename_postfix.'.json"';
	}
}


function pwaforwp_revert_src($content){
	$multisite_filename_postfix = '';
        if ( is_multisite() ) {
           $multisite_filename_postfix = '-' . get_current_blog_id();
        }
	//NON AMP
	$url = pwaforwp_front_url(); 
	$content = preg_replace_callback("/src=\"(.*?)".PWAFORWP_FILE_PREFIX."-register-sw".$multisite_filename_postfix."\.js\"/i",  'pwaforwp_cdn_replace_urls_revert', $content);
	$content = preg_replace_callback("/href=\"(.*?)".PWAFORWP_FILE_PREFIX."-manifest".$multisite_filename_postfix."\.json\"/i",  'pwaforwp_cdn_replace_urls_revert_manifest', $content);

	//AMP
	$content = preg_replace_callback("/src=\"([^\"]+".PWAFORWP_FILE_PREFIX."-amp-sw".$multisite_filename_postfix.".js)\"/",  'pwaforwp_amp_cdn_replace_urls_revert', $content);
	$content.=' ';
	$content = preg_replace_callback("/rel=\"dns-prefetch\"\s*href=\"(.*?)".PWAFORWP_FILE_PREFIX."-amp-manifest".$multisite_filename_postfix."\.json\"/i",  'pwaforwp_amp_cdn_replace_urls_revert_manifest_with_rel', $content);
	$content = preg_replace_callback("/href=\"(.*?)".PWAFORWP_FILE_PREFIX."-amp-manifest".$multisite_filename_postfix."\.json\"/i",  'pwaforwp_amp_cdn_replace_urls_revert_manifest', $content);        
        
	return $content;
}
function pwaforwp_cdn_replace_urls_revert($src){
	$url = pwaforwp_front_url();    
	if($src[1]==$url){
		return 'src="'.$url.PWAFORWP_FILE_PREFIX.'-register-sw.js"';
	}else{
		return 'src="'.$url.PWAFORWP_FILE_PREFIX.'-register-sw.js"';
	}
}

function pwaforwp_cdn_replace_urls_revert_manifest($src){
    $url = pwaforwp_front_url();    
	if($src[1]==$url){
		return 'href="'.$url.PWAFORWP_FILE_PREFIX.'-manifest.json"';
	}else{
		return 'href="'.$url.PWAFORWP_FILE_PREFIX.'-manifest.json"';
	}
}
/**
 * set user defined message on plugin activate
 */
function pwaforwp_after_activation_redirect( $plugin ) {
    if( $plugin == plugin_basename( __FILE__ ) ) {
        exit( wp_redirect( admin_url( 'admin.php?page=pwaforwp' ) ) );
    }
}
add_action( 'activated_plugin', 'pwaforwp_after_activation_redirect' );

register_activation_hook( __FILE__, 'pwaforwp_admin_notice_activation_hook' );
function pwaforwp_admin_notice_activation_hook() {
    set_transient( 'pwaforwp_admin_notice_transient', true );
    update_option( "pwaforwp_activation_date", date("Y-m-d"));
}
add_action( 'admin_notices', 'pwaforwp_admin_notice' );

function pwaforwp_admin_notice(){
    /* Check transient, if available display notice */
    
    if(get_transient( 'pwaforwp_pre_cache_post_ids' ) && get_option('pwaforwp_update_pre_cache_list') == 'enable'){
         ?>
        <div class="updated notice">
            <p><?php echo esc_html__('Update your pwa pre caching url list by clicking on button. ','pwa-for-wp'); ?> <a href="" class="button button-primary pwaforwp-update-pre-caching-urls"> <?php echo esc_html__('Click', 'pwa-for-wp') ?></a></p>
        </div>
        <?php
        
    }
    
    if( get_transient( 'pwaforwp_admin_notice_transient' ) ){
        ?>
        <div class="updated notice">
            <p><?php echo esc_html__('Thank you for using this','pwa-for-wp'); echo "<strong>".esc_html__(' PWA for WP plugin! ','pwa-for-wp')."</strong>"; echo esc_html__('Setup the plugin.', 'pwa-for-wp') ?> <a href="<?php echo esc_url(admin_url( 'admin.php?page=pwaforwp' )) ?>"> <?php echo esc_html__('Click here', 'pwa-for-wp') ?></a></p>
        </div>
        <?php
        /* Delete transient, only display this notice once. */
        delete_transient( 'pwaforwp_admin_notice_transient' );   
    }
        //Feedback notice
        $activation_date =  get_option("pwaforwp_activation_date");  

        $one_day    = date('Y-m-d',strtotime("+1 day", strtotime($activation_date))); 
        $seven_days = date('Y-m-d',strtotime("+7 day", strtotime($activation_date)));
        $one_month  = date('Y-m-d',strtotime("+30 day", strtotime($activation_date)));
        $sixty_days = date('Y-m-d',strtotime("+60 day", strtotime($activation_date)));
        $six_month  = date('Y-m-d',strtotime("+180 day", strtotime($activation_date)));
        $one_year   = date('Y-m-d',strtotime("+365 day", strtotime($activation_date))); 
                     
        $current_date = date("Y-m-d");    
        $list_of_date = array($one_day, $seven_days, $one_month, $sixty_days, $six_month, $one_year);
        $review_notice_bar_status_date = get_option( "pwaforwp_review_notice_bar_close_date");
        $review_notice_bar_never = get_option( "pwaforwp_review_never");
        
        if(in_array($current_date,$list_of_date) && $review_notice_bar_status_date !=$current_date && $review_notice_bar_never !='never'){
            echo '<div class="updated notice is-dismissible message notice notice-alt pwaforwp-feedback-notice">
                <p><span class="dashicons dashicons-thumbs-up"></span> 
                '.esc_html__('You have been using the PWA For WP plugin for some time now, do you like it?, If so,', 'pwa-for-wp').'						
                <a target="_blank" href="https://wordpress.org/plugins/pwa-for-wp/#reviews">				
                '.esc_html__('please write us a review', 'pwa-for-wp').'
                </a>
                <button style="margin-left:10px;" class="button button-primary pwaforwp-feedback-notice-remindme">'.esc_html__('Remind Me Later', 'pwa-for-wp').'</button>
                <button style="margin-left:10px;" class="button button-primary pwaforwp-feedback-notice-close">'.esc_html__('No Thanks', 'pwa-for-wp').'</button>'
                .'</p> '
                .'</div>';                       
        } 
    
}

add_filter('plugin_row_meta' , 'pwaforwp_add_plugin_meta_links', 10, 2);

function pwaforwp_add_plugin_meta_links($meta_fields, $file) {
    
    if ( plugin_basename(__FILE__) == $file ) {
      $plugin_url = "https://wordpress.org/support/plugin/pwa-for-wp";   
      $hire_url = "https://ampforwp.com/hire/";
      $meta_fields[] = "<a href='" . esc_url($plugin_url) . "' target='_blank'>" . esc_html__('Support Forum', 'pwa-for-wp') . "</a>";
      $meta_fields[] = "<a href='" . esc_url($hire_url) . "' target='_blank'>" . esc_html__('Hire Us', 'pwa-for-wp') . "</a>";
      $meta_fields[] = "<a href='" . esc_url($plugin_url) . "/reviews#new-post' target='_blank' title='" . esc_html__('Rate', 'pwa-for-wp') . "'>
            <i class='pwaforwp-wdi-rate-stars'>"
        . "<svg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-star'><polygon points='12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'/></svg>"
        . "<svg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-star'><polygon points='12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'/></svg>"
        . "<svg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-star'><polygon points='12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'/></svg>"
        . "<svg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-star'><polygon points='12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'/></svg>"
        . "<svg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-star'><polygon points='12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'/></svg>"
        . "</i></a>";      
      echo "<style>"
        . ".pwaforwp-wdi-rate-stars{display:inline-block;color:#ffb900;position:relative;top:3px;}"
        . ".pwaforwp-wdi-rate-stars svg{fill:#ffb900;}"
        . ".pwaforwp-wdi-rate-stars svg:hover{fill:#ffb900}"
        . ".pwaforwp-wdi-rate-stars svg:hover ~ svg{fill:none;}"
        . "</style>";
    }

    return $meta_fields;
    
  }