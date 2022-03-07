<?php

/**
 * Helper Functions
 *
 * @package     saswp
 * @subpackage  Helper/Templates
 * @copyright   Copyright (c) 2016, René Hermenau
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) )
    exit;

/**
 * Helper method to check if user is in the plugins page.
 *
 * @author René Hermenau
 * @since  1.4.0
 *
 * @return bool
 */
function pwa_is_plugins_page() {
    global $pagenow;

    return ( 'plugins.php' === $pagenow );
}

/**
 * display deactivation logic on plugins page
 * 
 * @since 1.4.0
 */


function pwa_add_deactivation_feedback_modal() {
    
    if( !is_admin() && !pwa_is_plugins_page()) {
        return;
    }

    $current_user = wp_get_current_user();
    if( !($current_user instanceof WP_User) ) {
        $email = '';
    } else {
        $email = trim( $current_user->user_email );
    }

    require_once PWAFORWP_PLUGIN_DIR."admin/deactivate-feedback.php";
}

/**
 * send feedback via email
 * 
 * @since 1.4.0
 */
function pwa_send_feedback() {

    if( isset( $_POST['data'] ) ) {
        parse_str( $_POST['data'], $form );
    }

    $text = '';
    if( isset( $form['pwa_disable_text'] ) ) {
        $text = implode( "\n\r", $form['pwa_disable_text'] );
    }

    $headers = array();

    $from = isset( $form['pwa_disable_from'] ) ? $form['pwa_disable_from'] : '';
    if( $from ) {
        $headers[] = "From: $from";
        $headers[] = "Reply-To: $from";
    }

    $subject = isset( $form['pwa_disable_reason'] ) ? $form['pwa_disable_reason'] : '(no reason given)';

    $subject = $subject.' - PWA for WP';

    if($subject == 'technical issue - PWA for WP'){

          $text = trim($text);

          if(!empty($text)){

            $text = 'technical issue description: '.$text;

          }else{

            $text = 'no description: '.$text;
          }
      
    }

    $success = wp_mail( 'makebetter@magazine3.in', $subject, $text, $headers );

    die();
}
add_action( 'wp_ajax_pwa_send_feedback', 'pwa_send_feedback' );



add_action( 'admin_enqueue_scripts', 'pwa_enqueue_makebetter_email_js' );

function pwa_enqueue_makebetter_email_js(){
    
    if( !is_admin() && !pwa_is_plugins_page()) {
        return;
    }

    wp_enqueue_script( 'pwa-make-better-js', PWAFORWP_PLUGIN_URL . 'admin/make-better-admin.js', array( 'jquery' ), PWAFORWP_PLUGIN_VERSION);

    wp_enqueue_style( 'pwa-make-better-css', PWAFORWP_PLUGIN_URL . 'admin/make-better-admin.css', false , PWAFORWP_PLUGIN_VERSION );
}

if( is_admin() && pwa_is_plugins_page()) {
    add_filter('admin_footer', 'pwa_add_deactivation_feedback_modal');
}


