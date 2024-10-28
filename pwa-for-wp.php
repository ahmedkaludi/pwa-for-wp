<?php
/**
Plugin Name: PWA for WP
Plugin URI: https://wordpress.org/plugins/pwa-for-wp/
Description: We are bringing the power of the Progressive Web Apps to the WP & AMP to take the user experience to the next level!
Author: Magazine3 
Version: 1.7.75
Author URI: http://pwa-for-wp.com
Text Domain: pwa-for-wp
Domain Path: /languages
License: GPL2+
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'PWAFORWP_PLUGIN_FILE',  __FILE__ );
define( 'PWAFORWP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PWAFORWP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'PWAFORWP_PLUGIN_VERSION', '1.7.75' );
define( 'PWAFORWP_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'PWAFORWP_EDD_STORE_URL', 'http://pwa-for-wp.com/' );

require_once PWAFORWP_PLUGIN_DIR."/admin/common-function.php"; 

if ( ! class_exists( 'PWAFORWP_Plugin_Usage_Tracker') ) {
  require_once PWAFORWP_PLUGIN_DIR. '/admin/class-pwaforwp-plugin-usage-tracker.php';
}

if ( ! function_exists( 'pwaforwp_start_plugin_tracking' ) ) {

  function pwaforwp_start_plugin_tracking() {

    $settings = array('pwaforwp_settings' );

    $wisdom = new PWAFORWP_Plugin_Usage_Tracker(
      __FILE__,
      'https://data.ampforwp.com/pwaforwp',
      (array) $settings,
      true,
      true,
      0
    );

  }

  pwaforwp_start_plugin_tracking();

}

require_once PWAFORWP_PLUGIN_DIR."/admin/class-pwaforwp-newsletter.php"; 

require_once PWAFORWP_PLUGIN_DIR."/service-work/class-pwaforwp-file-creation.php";
require_once PWAFORWP_PLUGIN_DIR."/service-work/class-pwaforwp-file-creation-init.php"; 
require_once PWAFORWP_PLUGIN_DIR."/service-work/class-pwaforwp-push-notification.php"; 
require_once PWAFORWP_PLUGIN_DIR."/3rd-party/3rd-party-file-loading.php";

if ( pwaforwp_is_admin() ) {

    add_filter( 'plugin_action_links_' . PWAFORWP_PLUGIN_BASENAME,'pwaforwp_add_action_links');
    require_once PWAFORWP_PLUGIN_DIR."admin/settings.php";

}

add_action( 'plugins_loaded', 'pwaforwp_init_plugin' );

function pwaforwp_init_plugin() {

    global $pwaforwp_globe_admin_notice;
    $pwaforwp_globe_admin_notice = false;

    require_once PWAFORWP_PLUGIN_DIR."/service-work/class-pwaforwp-service-worker.php"; 

    if ( class_exists( 'WP_Service_Workers' ) ) { 
      require_once PWAFORWP_PLUGIN_DIR."/3rd-party/class-pwaforwp-wpwa.php"; 
    }
    //For CDN CODES
    if ( ! is_admin() ) {
            $settings = pwaforwp_defaultSettings(); 
            if( isset( $settings['cdn_setting'] ) && $settings['cdn_setting']==1 ){
                ob_start( 'pwaforwp_revert_src' );
            }
    }

}

function pwaforwp_add_action_links( $links ) {

    $mylinks = array('<a href="' . esc_url(admin_url( 'admin.php?page=pwaforwp' )) . '">'.esc_html__( 'Settings', 'pwa-for-wp' ).'</a>');
    return array_merge( $links, $mylinks );

}

function pwaforwp_revert_src( $content ) {
                                 
	$url = pwaforwp_site_url();                                 
                
        if ((function_exists( 'ampforwp_is_amp_endpoint' )) || function_exists( 'is_amp_endpoint' )) {
            
            preg_match("/<link rel=\"manifest\" href=\"(.*?)"."pwa-amp-manifest".pwaforwp_multisite_postfix()."\.json\">/i", $content, $manifest_match);
        
            if(isset($manifest_match[0])){               
               $amp_mf_url = $url.'pwa-amp-manifest'.pwaforwp_multisite_postfix().'.json';
               $replacewith = '<link rel="manifest" href="'.esc_url($amp_mf_url).'">'; 
               $content = str_replace($manifest_match[0],$replacewith,$content);
            }
                        
            preg_match("/<amp\-install\-serviceworker(.*?)src=\"(.*?)pwa-amp-sw".pwaforwp_multisite_postfix()."\.js\"(.*?)data-iframe-src=\"(.*?)pwa-amp-sw".pwaforwp_multisite_postfix()."\.html/s", $content, $amp_sw_match);

            if(isset($amp_sw_match[0])){
               $amp_sw_url = $url.'pwa-amp-sw'.pwaforwp_multisite_postfix().'.js';
               $dataset_src = 'data-iframe-src="'.esc_url($url).'pwa-amp-sw'.pwaforwp_multisite_postfix().'.html'; 
               $replacewith = '<amp-install-serviceworker '.$amp_sw_match[1].' src="'.esc_url($amp_sw_url).'"'.$amp_sw_match[3].$dataset_src; 
               $content = str_replace($amp_sw_match[0],$replacewith,$content);
            }
                       
        }else{
            // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript	-- not registered/enqueued script here            
            preg_match("/<script src=\"(.*?)"."pwa-register-sw".pwaforwp_multisite_postfix()."\.js\">/i", $content, $sw_match);

            if(isset($sw_match[0])){
               $pwa_r_url = $url.'pwa-register-sw'.pwaforwp_multisite_postfix().'.js';
               // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript	-- not registered/enqueued script here            
               $replacewith = '<script src="'.esc_url($pwa_r_url).'">';
               $content = str_replace($sw_match[0],$replacewith,$content);
            }
            
        }
                        
	return $content;
}

register_activation_hook( __FILE__, 'pwaforwp_on_activation' );
register_deactivation_hook( __FILE__, 'pwaforwp_on_deactivation' );

function pwaforwp_on_deactivation() {
    global $wp_rewrite;
    $wp_rewrite->flush_rules();        
    pwaforwp_delete_pwa_files();
    
}

function pwaforwp_on_activation() {

    flush_rewrite_rules();
    // Flushing rewrite urls ONLY on activation
    global $wp_rewrite;
    $wp_rewrite->flush_rules();
    pwaforwp_admin_notice_activation_hook();            
    pwaforwp_required_file_creation();
    
}

function pwaforwp_admin_notice_activation_hook() {
    set_transient( 'pwaforwp_admin_notice_transient', true );
    update_option( "pwaforwp_activation_date", gmdate("Y-m-d"));
}

add_action( 'admin_notices', 'pwaforwp_admin_notice' );

function pwaforwp_admin_notice(){

    global $pagenow, $pwaforwp_globe_admin_notice;
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- we are not processing form here
    if($pagenow!='admin.php' || !isset($_GET['page']) || (isset($_GET['page']) && $_GET['page']!='pwaforwp') ) {
        return false;
    }
    $screen_id      = ''; 
    $current_screen = get_current_screen();
    
    if(is_object($current_screen)){
       $screen_id =  $current_screen->id;
    }
    /* Check transient, if available display notice */
    
    if(get_transient( 'pwaforwp_pre_cache_post_ids' ) && get_option('pwaforwp_update_pre_cache_list') == 'enable' && $pwaforwp_globe_admin_notice==false){
        $pwaforwp_globe_admin_notice = true
         ?>
        <div class="updated notice">
            <p><?php echo esc_html__('Update your pwa pre caching url list by click on button. ','pwa-for-wp'); ?> <a href="" class="button button-primary pwaforwp-update-pre-caching-urls"> <?php echo esc_html__('Click Here To Update', 'pwa-for-wp') ?></a></p>
        </div>
        <?php
        
    }
    
    if( get_transient( 'pwaforwp_admin_notice_transient' ) && $pwaforwp_globe_admin_notice==false){
        $pwaforwp_globe_admin_notice = true;
        ?>
        <div class="updated notice">
            <p><?php echo esc_html__('Thank you for using','pwa-for-wp'); echo "<strong>".esc_html__(' PWA for WP plugin! ','pwa-for-wp')."</strong>"; ?> </p>
        </div>
        <?php
        /* Delete transient, only display this notice once. */
        delete_transient( 'pwaforwp_admin_notice_transient' );   
    }
        //Feedback notice
        $activation_date =  get_option("pwaforwp_activation_date");  

        $one_day    = gmdate('Y-m-d',strtotime("+1 day", strtotime($activation_date))); 
        $seven_days = gmdate('Y-m-d',strtotime("+7 day", strtotime($activation_date)));
        $one_month  = gmdate('Y-m-d',strtotime("+30 day", strtotime($activation_date)));
        $sixty_days = gmdate('Y-m-d',strtotime("+60 day", strtotime($activation_date)));
        $six_month  = gmdate('Y-m-d',strtotime("+180 day", strtotime($activation_date)));
        $one_year   = gmdate('Y-m-d',strtotime("+365 day", strtotime($activation_date))); 
                     
        $current_date = gmdate("Y-m-d");    
        $list_of_date = array($one_day, $seven_days, $one_month, $sixty_days, $six_month, $one_year);
        
        $review_notice_bar_status_date = get_option( "pwaforwp_review_notice_bar_close_date");
        $review_notice_bar_never       = get_option( "pwaforwp_review_never");
        $datetime1 = new DateTime($review_notice_bar_status_date);
        $datetime2 = new DateTime( $current_date );
        $diff_intrval = round( ($datetime2->format( 'U' ) - $datetime1->format( 'U' )) / (60 * 60 * 24) );

        if(( in_array($current_date,$list_of_date) ||
          (!empty($review_notice_bar_status_date) && $review_notice_bar_status_date != $current_date && $diff_intrval >= 7 )
        )
        && $review_notice_bar_never != 'never' 
        && $pwaforwp_globe_admin_notice == false){
            $pwaforwp_globe_admin_notice = true;
           echo sprintf('<div class="updated notice is-dismissible message notice notice-alt pwaforwp-feedback-notice">
                    <p>
                        <span class="dashicons dashicons-thumbs-up"></span>%s   
                    </p>
                    <p class="notice-action">                     
                        <a target="_blank" href="https://wordpress.org/plugins/pwa-for-wp/#reviews" class="button button-secondry">%s</a>
                        <a href="#" style="margin-left:10px;cursor:pointer;font-size:12px;text-decoration:underline;" class="pwaforwp-feedback-notice-remindme">%s</a>
                        <a href="#" style="margin-left:10px;cursor:pointer;font-size:12px;text-decoration:underline;" class="pwaforwp-feedback-notice-close">%s</a>
                        <a href="#" style="margin-left:10px;cursor:pointer;font-size:12px;text-decoration:underline;" class="pwaforwp-feedback-notice-close">%s</a>
                    </p>
                </div>',
                esc_html__('Excellent! You\'ve been using PWA For WP plugin for over a week. Hope you are enjoying it so far. We have spent countless hours developing this 

                 plugin for you, and we would really appreciate it if you could drop us a rating on wp.org to help us spread the word and boost our motivation.,', 'pwa-for-wp'),
                esc_html__('Write us a review', 'pwa-for-wp'),
                esc_html__('Remind Me Later', 'pwa-for-wp'),
                esc_html__('I already did', 'pwa-for-wp'),
                esc_html__('No, not good enough', 'pwa-for-wp')

            ); 
        } 
    
}

add_filter('plugin_row_meta' , 'pwaforwp_add_plugin_meta_links', 10, 2);
/**
  * Add meta actions to plugins list
  */
function pwaforwp_add_plugin_meta_links( $meta_fields, $file ) {
    
    if ( PWAFORWP_PLUGIN_BASENAME == $file ) {

      $plugin_url = "https://wordpress.org/support/plugin/pwa-for-wp";         
      $meta_fields[] = "<a href='" . esc_url($plugin_url) . "' target='_blank'>" . esc_html__('Support Forum', 'pwa-for-wp') . "</a>";

    }

    return $meta_fields;
    
  }

  function pwaforwp_generate_sw_and_manifest_on_fly( $query ) {

	if ( ! property_exists( $query, 'query_vars' ) || ! is_array( $query->query_vars ) ) {
		return;
	}
    
	$query_vars_as_string = http_build_query( $query->query_vars );
	$manifest_filename    = pwaforwp_get_manifest_filename();

	if ( strpos( $query_vars_as_string, $manifest_filename ) !== false ) {
		// Generate manifest from Settings and send the response w/ header.
		$pagemid =  isset($query->query_vars['pwaforwp_mid'])? $query->query_vars['pwaforwp_mid'] : null;

		header( 'Content-Type: application/json' );
        $p_file_c = new PWAforwp_File_Creation();		
        $p_file_escaped = $p_file_c->pwaforwp_manifest( false, $pagemid );
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped	-- already escaped
        echo $p_file_escaped;
		exit();

	}
    // Needed new query_vars of pagename for Wp Fastest Cache 
	if ( class_exists('WpFastestCache') ) {

		$query_vars_as_string = isset($query->query_vars['pagename']) ? $query->query_vars['pagename'] : false;
		if ( $query_vars_as_string == false ) {
		    $query_vars_as_string = isset($query->query_vars['name']) ? $query->query_vars['name'] : '';
	    }

    }
	
}

function pwaforwp_add_rewrite_rules() {
	$manifest_filename = pwaforwp_get_manifest_filename();
	add_rewrite_rule( "^/{$manifest_filename}$",
		"index.php?{$manifest_filename}=1"
	);
}


function pwaforwp_setup_hooks() {

    $defaults = pwaforwp_defaultSettings();
    $pro_extension_exists = function_exists('pwaforwp_is_any_extension_active')?pwaforwp_is_any_extension_active():false;

    if($pro_extension_exists && isset( $defaults['start_page'] ) && $defaults['start_page'] == 'active_url'){

        add_action( 'init', 'pwaforwp_add_rewrite_rules' );
        add_action( 'parse_request', 'pwaforwp_generate_sw_and_manifest_on_fly' );

    }
}

add_action( 'plugins_loaded', 'pwaforwp_setup_hooks' );

add_filter('query_vars', 'pwaforwp_manifest_query_vars');

function pwaforwp_manifest_query_vars($vars) {

    $defaults = pwaforwp_defaultSettings();
    $pro_extension_exists = function_exists( 'pwaforwp_is_any_extension_active' ) ? pwaforwp_is_any_extension_active() : false;
    
    if ( $pro_extension_exists && isset( $defaults['start_page'] ) && $defaults['start_page'] == 'active_url' ) {

        $vars[] = 'pwaforwp_mid';

    }
    
    return $vars;
}