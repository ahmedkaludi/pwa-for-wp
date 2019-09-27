<?php
// Exit if accessed directly
if ( ! defined('ABSPATH') ) exit;

function pwaforwp_onesignal_compatiblity($action = null) {
        
    if ( class_exists( 'OneSignal' ) ) {
        pwaforwp_use_custom_manifest($action);
        if ( ! is_multisite() ) {              
            pwaforwp_add_sw_to_onesignal_sw($action);
        }
        register_deactivation_hook( PWAFORWP_PLUGIN_FILE, function () {
            $os_settings                        = \OneSignal::get_onesignal_settings();
            $os_settings['use_custom_manifest'] = false;
            \OneSignal::save_onesignal_settings( $os_settings );
        } );
    }
}

function pwaforwp_use_custom_manifest($action = null){
    
    $url = pwaforwp_home_url();
    
    $onesignal_option = get_option('OneSignalWPSetting');
    
    if($onesignal_option['custom_manifest_url'] == '' && $onesignal_option['use_custom_manifest'] == false){
            
        $onesignal_option['use_custom_manifest'] = true;
        if($action){
            $onesignal_option['use_custom_manifest'] = false;
        }        
        if(function_exists('ampforwp_is_amp_endpoint') && ampforwp_is_amp_endpoint()){
            $onesignal_option['custom_manifest_url'] = esc_url( pwaforwp_manifest_json_url(true) );
        }else{
            $onesignal_option['custom_manifest_url'] = esc_url( pwaforwp_manifest_json_url() );//esc_url($url.'pwa-manifest'.pwaforwp_multisite_postfix().'.json');
        }
        update_option('OneSignalWPSetting', $onesignal_option);
        
    }
    //update own settings
    $get_pwaforwp_options   = get_option('pwaforwp_settings');
    $get_pwaforwp_options['one_signal_support_setting'] = 1;
    update_option('pwaforwp_settings', $get_pwaforwp_options);  
    
            
}

function pwaforwp_onesignal_insert_gcm_sender_id( $manifest ) {
                
            
            if ( class_exists( 'OneSignal' ) ) {
                
                if(is_array($manifest)){

                    $manifest['gcm_sender_id'] = '482941778795';

                }
                        
           }
        
                    
    return $manifest;
}

add_filter( 'pwaforwp_manifest', 'pwaforwp_onesignal_insert_gcm_sender_id' );

function pwaforwp_onesignal_change_sw_name($name){
    
    if ( ! is_multisite() ) {
            
            if ( class_exists( 'OneSignal' ) ) {
            
            $name = 'OneSignalSDKWorker.js';
            
            }
        
        }
           
    return $name;
    
}
add_filter( 'pwaforwp_sw_name_modify', 'pwaforwp_onesignal_change_sw_name' );

function pwaforwp_add_sw_to_onesignal_sw($action = null){
    
        $abs_path              = str_replace("//","/",str_replace("\\","/",realpath(ABSPATH))."/");
        $onesignal_sdk         = $abs_path.'OneSignalSDKWorker.js';
        $onesignal_sdk_updator = $abs_path.'OneSignalSDKUpdaterWorker.js';
        $url = pwaforwp_site_url();
        $home_url = pwaforwp_home_url();

        if( !is_multisite() && trim($url)!==trim($home_url) ){
          $url = esc_url_raw($home_url.'?'.pwaforwp_query_var('sw_query_var').'=1&'.pwaforwp_query_var('sw_file_var').'='.'pwa-sw.js');   
        }else{
            $url                   = esc_url(pwaforwp_home_url().'pwa-sw.js');
        }
                                              
        $content  = "";  
        
        if(!$action){
            $content .= "importScripts('".$url."')".PHP_EOL;
            $content .= "importScripts('https://cdn.onesignal.com/sdks/OneSignalSDKWorker.js')".PHP_EOL;
        }
                                                
        $status = pwaforwp_write_a_file($onesignal_sdk, $content);
        $status = pwaforwp_write_a_file($onesignal_sdk_updator, $content);
       
        return $status;
    
}
add_action("wp", 'pwaforwp_onesignal_for_multisite');
function pwaforwp_onesignal_for_multisite(){
    if(  class_exists('OneSignal') ){//is_multisite() &&
        remove_action( 'wp_head', [ 'OneSignal_Public', 'onesignal_header' ] );
        add_action( 'wp_head',  'pwaforwp_onesignal_init_onesignal_head' );
    }
}
function pwaforwp_onesignal_init_onesignal_head(){
    $home_url = trailingslashit(pwaforwp_home_url());
    //$url = esc_url_raw($home_url.'?'.pwaforwp_query_var('sw_query_var').'=1&'.pwaforwp_query_var('sw_file_var').'='.'dynamic_onesignal')."&".pwaforwp_query_var('site_id_var')."=".get_current_blog_id()
    if(is_multisite()){
        $url = ('onesignal_js/'.get_current_blog_id());   
    }else{
        $url = ('onesignal_js');   
    }

    $onesignal_wp_settings = \OneSignal::get_onesignal_settings();
        echo '<meta name="onesignal" content="wordpress-plugin"/>
        <script src="https://cdn.onesignal.com/sdks/OneSignalSDK.js" async></script>';
        $current_plugin_url = ONESIGNAL_PLUGIN_URL;
    ?>
    <script>

      window.OneSignal = window.OneSignal || [];

      OneSignal.push( function() {
        OneSignal.SERVICE_WORKER_UPDATER_PATH = '<?php echo $url; ?>';
        OneSignal.SERVICE_WORKER_PATH = '<?php echo $url; ?>';
        OneSignal.SERVICE_WORKER_PARAM = { scope: '/' };

        <?php

        if ($onesignal_wp_settings['default_icon'] != '') {
            echo 'OneSignal.setDefaultIcon("'.\OneSignalUtils::decode_entities($onesignal_wp_settings['default_icon'])."/\");\n";
        }

        if ($onesignal_wp_settings['default_url'] != '') {
            echo 'OneSignal.setDefaultNotificationUrl("'.\OneSignalUtils::decode_entities($onesignal_wp_settings['default_url']).'");';
        } else {
            echo 'OneSignal.setDefaultNotificationUrl("'.\OneSignalUtils::decode_entities(get_site_url())."\");\n";
        } ?>
        var oneSignal_options = {};
        window._oneSignalInitOptions = oneSignal_options;

        <?php
        echo "oneSignal_options['wordpress'] = true;\n";
        echo "oneSignal_options['appId'] = '".\OneSignalUtils::html_safe($onesignal_wp_settings['app_id'])."';\n";

        if ($onesignal_wp_settings['prompt_auto_register'] == '1') {
            echo "oneSignal_options['autoRegister'] = true;\n";
        } else {
            echo "oneSignal_options['autoRegister'] = false;\n";
        }

        if ($onesignal_wp_settings['use_http_permission_request'] == '1') {
            echo "oneSignal_options['httpPermissionRequest'] = { };\n";
            echo "oneSignal_options['httpPermissionRequest']['enable'] = true;\n";

            if (array_key_exists('customize_http_permission_request', $onesignal_wp_settings) && $onesignal_wp_settings['customize_http_permission_request'] == '1') {
                echo "oneSignal_options['httpPermissionRequest']['modalTitle'] = \"".\OneSignalUtils::html_safe($onesignal_wp_settings['http_permission_request_modal_title'])."\";\n";
                echo "oneSignal_options['httpPermissionRequest']['modalMessage'] = \"".\OneSignalUtils::html_safe($onesignal_wp_settings['http_permission_request_modal_message'])."\";\n";
                echo "oneSignal_options['httpPermissionRequest']['modalButtonText'] = \"".\OneSignalUtils::html_safe($onesignal_wp_settings['http_permission_request_modal_button_text'])."\";\n";
            }
        }

        if ($onesignal_wp_settings['send_welcome_notification'] == '1') {
            echo "oneSignal_options['welcomeNotification'] = { };\n";
            echo "oneSignal_options['welcomeNotification']['title'] = \"".\OneSignalUtils::html_safe($onesignal_wp_settings['welcome_notification_title'])."\";\n";
            echo "oneSignal_options['welcomeNotification']['message'] = \"".\OneSignalUtils::html_safe($onesignal_wp_settings['welcome_notification_message'])."\";\n";
            if ($onesignal_wp_settings['welcome_notification_url'] != '') {
                echo "oneSignal_options['welcomeNotification']['url'] = \"".\OneSignalUtils::html_safe($onesignal_wp_settings['welcome_notification_url'])."\";\n";
            }
        } else {
            echo "oneSignal_options['welcomeNotification'] = { };\n";
            echo "oneSignal_options['welcomeNotification']['disable'] = true;\n";
        }

        if ($onesignal_wp_settings['subdomain'] != '') {
            echo "oneSignal_options['subdomainName'] = \"".\OneSignalUtils::html_safe($onesignal_wp_settings['subdomain'])."\";\n";
        } else {
            echo "oneSignal_options['path'] = \"".$home_url."\";\n";
        }

        if (@$onesignal_wp_settings['safari_web_id']) {
            echo "oneSignal_options['safari_web_id'] = \"".\OneSignalUtils::html_safe($onesignal_wp_settings['safari_web_id'])."\";\n";
        }

        if ($onesignal_wp_settings['persist_notifications'] == 'platform-default') {
            echo "oneSignal_options['persistNotification'] = false;\n";
        } elseif ($onesignal_wp_settings['persist_notifications'] == 'yes-all') {
            echo "oneSignal_options['persistNotification'] = true;\n";
        }

        echo "oneSignal_options['promptOptions'] = { };\n";
        if (array_key_exists('prompt_customize_enable', $onesignal_wp_settings) && $onesignal_wp_settings['prompt_customize_enable'] == '1') {
            if ($onesignal_wp_settings['prompt_action_message'] != '') {
                echo "oneSignal_options['promptOptions']['actionMessage'] = '".\OneSignalUtils::html_safe($onesignal_wp_settings['prompt_action_message'])."';\n";
            }
            if ($onesignal_wp_settings['prompt_example_notification_title_desktop'] != '') {
                echo "oneSignal_options['promptOptions']['exampleNotificationTitleDesktop'] = '".\OneSignalUtils::html_safe($onesignal_wp_settings['prompt_example_notification_title_desktop'])."';\n";
            }
            if ($onesignal_wp_settings['prompt_example_notification_message_desktop'] != '') {
                echo "oneSignal_options['promptOptions']['exampleNotificationMessageDesktop'] = '".\OneSignalUtils::html_safe($onesignal_wp_settings['prompt_example_notification_message_desktop'])."';\n";
            }
            if ($onesignal_wp_settings['prompt_example_notification_title_mobile'] != '') {
                echo "oneSignal_options['promptOptions']['exampleNotificationTitleMobile'] = '".\OneSignalUtils::html_safe($onesignal_wp_settings['prompt_example_notification_title_mobile'])."';\n";
            }
            if ($onesignal_wp_settings['prompt_example_notification_message_mobile'] != '') {
                echo "oneSignal_options['promptOptions']['exampleNotificationMessageMobile'] = '".\OneSignalUtils::html_safe($onesignal_wp_settings['prompt_example_notification_message_mobile'])."';\n";
            }
            if ($onesignal_wp_settings['prompt_example_notification_caption'] != '') {
                echo "oneSignal_options['promptOptions']['exampleNotificationCaption'] = '".\OneSignalUtils::html_safe($onesignal_wp_settings['prompt_example_notification_caption'])."';\n";
            }
            if ($onesignal_wp_settings['prompt_accept_button_text'] != '') {
                echo "oneSignal_options['promptOptions']['acceptButtonText'] = '".\OneSignalUtils::html_safe($onesignal_wp_settings['prompt_accept_button_text'])."';\n";
            }
            if ($onesignal_wp_settings['prompt_cancel_button_text'] != '') {
                echo "oneSignal_options['promptOptions']['cancelButtonText'] = '".\OneSignalUtils::html_safe($onesignal_wp_settings['prompt_cancel_button_text'])."';\n";
            }
            if ($onesignal_wp_settings['prompt_site_name'] != '') {
                echo "oneSignal_options['promptOptions']['siteName'] = '".\OneSignalUtils::html_safe($onesignal_wp_settings['prompt_site_name'])."';\n";
            }
            if ($onesignal_wp_settings['prompt_auto_accept_title'] != '') {
                echo "oneSignal_options['promptOptions']['autoAcceptTitle'] = '".\OneSignalUtils::html_safe($onesignal_wp_settings['prompt_auto_accept_title'])."';\n";
            }
        }

        if (array_key_exists('notifyButton_enable', $onesignal_wp_settings) && $onesignal_wp_settings['notifyButton_enable'] == '1') {
            echo "oneSignal_options['notifyButton'] = { };\n";
            echo "oneSignal_options['notifyButton']['enable'] = true;\n";

            if (array_key_exists('notifyButton_position', $onesignal_wp_settings) && $onesignal_wp_settings['notifyButton_position'] != '') {
                echo "oneSignal_options['notifyButton']['position'] = '".\OneSignalUtils::html_safe($onesignal_wp_settings['notifyButton_position'])."';\n";
            }
            if (array_key_exists('notifyButton_theme', $onesignal_wp_settings) && $onesignal_wp_settings['notifyButton_theme'] != '') {
                echo "oneSignal_options['notifyButton']['theme'] = '".\OneSignalUtils::html_safe($onesignal_wp_settings['notifyButton_theme'])."';\n";
            }
            if (array_key_exists('notifyButton_size', $onesignal_wp_settings) && $onesignal_wp_settings['notifyButton_size'] != '') {
                echo "oneSignal_options['notifyButton']['size'] = '".\OneSignalUtils::html_safe($onesignal_wp_settings['notifyButton_size'])."';\n";
            }

            if ($onesignal_wp_settings['notifyButton_prenotify'] == '1') {
                echo "oneSignal_options['notifyButton']['prenotify'] = true;\n";
            } else {
                echo "oneSignal_options['notifyButton']['prenotify'] = false;\n";
            }

            if ($onesignal_wp_settings['notifyButton_showAfterSubscribed'] !== true) {
                echo "oneSignal_options['notifyButton']['displayPredicate'] = function() {
              return OneSignal.isPushNotificationsEnabled()
                      .then(function(isPushEnabled) {
                          return !isPushEnabled;
                      });
            };\n";
            }

            if ($onesignal_wp_settings['use_modal_prompt'] == '1') {
                echo "oneSignal_options['notifyButton']['modalPrompt'] = true;\n";
            }

            if ($onesignal_wp_settings['notifyButton_showcredit'] == '1') {
                echo "oneSignal_options['notifyButton']['showCredit'] = true;\n";
            } else {
                echo "oneSignal_options['notifyButton']['showCredit'] = false;\n";
            }

            if (array_key_exists('notifyButton_customize_enable', $onesignal_wp_settings) && $onesignal_wp_settings['notifyButton_customize_enable'] == '1') {
                echo "oneSignal_options['notifyButton']['text'] = {};\n";
                if ($onesignal_wp_settings['notifyButton_message_prenotify'] != '') {
                    echo "oneSignal_options['notifyButton']['text']['message.prenotify'] = '".\OneSignalUtils::html_safe($onesignal_wp_settings['notifyButton_message_prenotify'])."';\n";
                }
                if ($onesignal_wp_settings['notifyButton_tip_state_unsubscribed'] != '') {
                    echo "oneSignal_options['notifyButton']['text']['tip.state.unsubscribed'] = '".\OneSignalUtils::html_safe($onesignal_wp_settings['notifyButton_tip_state_unsubscribed'])."';\n";
                }
                if ($onesignal_wp_settings['notifyButton_tip_state_subscribed'] != '') {
                    echo "oneSignal_options['notifyButton']['text']['tip.state.subscribed'] = '".\OneSignalUtils::html_safe($onesignal_wp_settings['notifyButton_tip_state_subscribed'])."';\n";
                }
                if ($onesignal_wp_settings['notifyButton_tip_state_blocked'] != '') {
                    echo "oneSignal_options['notifyButton']['text']['tip.state.blocked'] = '".\OneSignalUtils::html_safe($onesignal_wp_settings['notifyButton_tip_state_blocked'])."';\n";
                }
                if ($onesignal_wp_settings['notifyButton_message_action_subscribed'] != '') {
                    echo "oneSignal_options['notifyButton']['text']['message.action.subscribed'] = '".\OneSignalUtils::html_safe($onesignal_wp_settings['notifyButton_message_action_subscribed'])."';\n";
                }
                if ($onesignal_wp_settings['notifyButton_message_action_resubscribed'] != '') {
                    echo "oneSignal_options['notifyButton']['text']['message.action.resubscribed'] = '".\OneSignalUtils::html_safe($onesignal_wp_settings['notifyButton_message_action_resubscribed'])."';\n";
                }
                if ($onesignal_wp_settings['notifyButton_message_action_unsubscribed'] != '') {
                    echo "oneSignal_options['notifyButton']['text']['message.action.unsubscribed'] = '".OneSignalUtils::html_safe($onesignal_wp_settings['notifyButton_message_action_unsubscribed'])."';\n";
                }
                if ($onesignal_wp_settings['notifyButton_dialog_main_title'] != '') {
                    echo "oneSignal_options['notifyButton']['text']['dialog.main.title'] = '".\OneSignalUtils::html_safe($onesignal_wp_settings['notifyButton_dialog_main_title'])."';\n";
                }
                if ($onesignal_wp_settings['notifyButton_dialog_main_button_subscribe'] != '') {
                    echo "oneSignal_options['notifyButton']['text']['dialog.main.button.subscribe'] = '".\OneSignalUtils::html_safe($onesignal_wp_settings['notifyButton_dialog_main_button_subscribe'])."';\n";
                }
                if ($onesignal_wp_settings['notifyButton_dialog_main_button_unsubscribe'] != '') {
                    echo "oneSignal_options['notifyButton']['text']['dialog.main.button.unsubscribe'] = '".\OneSignalUtils::html_safe($onesignal_wp_settings['notifyButton_dialog_main_button_unsubscribe'])."';\n";
                }
                if ($onesignal_wp_settings['notifyButton_dialog_blocked_title'] != '') {
                    echo "oneSignal_options['notifyButton']['text']['dialog.blocked.title'] = '".\OneSignalUtils::html_safe($onesignal_wp_settings['notifyButton_dialog_blocked_title'])."';\n";
                }
                if ($onesignal_wp_settings['notifyButton_dialog_blocked_message'] != '') {
                    echo "oneSignal_options['notifyButton']['text']['dialog.blocked.message'] = '".\OneSignalUtils::html_safe($onesignal_wp_settings['notifyButton_dialog_blocked_message'])."';\n";
                }
            }

            if (array_key_exists('notifyButton_customize_colors_enable', $onesignal_wp_settings) && $onesignal_wp_settings['notifyButton_customize_colors_enable'] == '1') {
                echo "oneSignal_options['notifyButton']['colors'] = {};\n";
                if ($onesignal_wp_settings['notifyButton_color_background'] != '') {
                    echo "oneSignal_options['notifyButton']['colors']['circle.background'] = '".\OneSignalUtils::html_safe($onesignal_wp_settings['notifyButton_color_background'])."';\n";
                }
                if ($onesignal_wp_settings['notifyButton_color_foreground'] != '') {
                    echo "oneSignal_options['notifyButton']['colors']['circle.foreground'] = '".\OneSignalUtils::html_safe($onesignal_wp_settings['notifyButton_color_foreground'])."';\n";
                }
                if ($onesignal_wp_settings['notifyButton_color_badge_background'] != '') {
                    echo "oneSignal_options['notifyButton']['colors']['badge.background'] = '".\OneSignalUtils::html_safe($onesignal_wp_settings['notifyButton_color_badge_background'])."';\n";
                }
                if ($onesignal_wp_settings['notifyButton_color_badge_foreground'] != '') {
                    echo "oneSignal_options['notifyButton']['colors']['badge.foreground'] = '".\OneSignalUtils::html_safe($onesignal_wp_settings['notifyButton_color_badge_foreground'])."';\n";
                }
                if ($onesignal_wp_settings['notifyButton_color_badge_border'] != '') {
                    echo "oneSignal_options['notifyButton']['colors']['badge.bordercolor'] = '".\OneSignalUtils::html_safe($onesignal_wp_settings['notifyButton_color_badge_border'])."';\n";
                }
                if ($onesignal_wp_settings['notifyButton_color_pulse'] != '') {
                    echo "oneSignal_options['notifyButton']['colors']['pulse.color'] = '".\OneSignalUtils::html_safe($onesignal_wp_settings['notifyButton_color_pulse'])."';\n";
                }
                if ($onesignal_wp_settings['notifyButton_color_popup_button_background'] != '') {
                    echo "oneSignal_options['notifyButton']['colors']['dialog.button.background'] = '".\OneSignalUtils::html_safe($onesignal_wp_settings['notifyButton_color_popup_button_background'])."';\n";
                }
                if ($onesignal_wp_settings['notifyButton_color_popup_button_background_hover'] != '') {
                    echo "oneSignal_options['notifyButton']['colors']['dialog.button.background.hovering'] = '".\OneSignalUtils::html_safe($onesignal_wp_settings['notifyButton_color_popup_button_background_hover'])."';\n";
                }
                if ($onesignal_wp_settings['notifyButton_color_popup_button_background_active'] != '') {
                    echo "oneSignal_options['notifyButton']['colors']['dialog.button.background.active'] = '".\OneSignalUtils::html_safe($onesignal_wp_settings['notifyButton_color_popup_button_background_active'])."';\n";
                }
                if ($onesignal_wp_settings['notifyButton_color_popup_button_color'] != '') {
                    echo "oneSignal_options['notifyButton']['colors']['dialog.button.foreground'] = '".\OneSignalUtils::html_safe($onesignal_wp_settings['notifyButton_color_popup_button_color'])."';\n";
                }
            }

            if (array_key_exists('notifyButton_customize_offset_enable', $onesignal_wp_settings) && $onesignal_wp_settings['notifyButton_customize_offset_enable'] == '1') {
                echo "oneSignal_options['notifyButton']['offset'] = {};\n";
                if ($onesignal_wp_settings['notifyButton_offset_bottom'] != '') {
                    echo "oneSignal_options['notifyButton']['offset']['bottom'] = '".\OneSignalUtils::html_safe($onesignal_wp_settings['notifyButton_offset_bottom'])."';\n";
                }
                if ($onesignal_wp_settings['notifyButton_offset_left'] != '') {
                    echo "oneSignal_options['notifyButton']['offset']['left'] = '".\OneSignalUtils::html_safe($onesignal_wp_settings['notifyButton_offset_left'])."';\n";
                }
                if ($onesignal_wp_settings['notifyButton_offset_right'] != '') {
                    echo "oneSignal_options['notifyButton']['offset']['right'] = '".\OneSignalUtils::html_safe($onesignal_wp_settings['notifyButton_offset_right'])."';\n";
                }
            }
        }

        $use_custom_sdk_init = $onesignal_wp_settings['use_custom_sdk_init'];
        if (!$use_custom_sdk_init) {
            if (has_filter('onesignal_initialize_sdk')) {
                \onesignal_debug('Applying onesignal_initialize_sdk filter.');
                if (apply_filters('onesignal_initialize_sdk', $onesignal_wp_settings)) {
                    // If the filter returns "$do_initialize_sdk: true", initialize the web SDK
              ?>
              OneSignal.init(window._oneSignalInitOptions);
              <?php
                } else {
                    ?>
              /* OneSignal: onesignal_initialize_sdk filter preventing SDK initialization. */
              <?php
                }
            } else {
                if (array_key_exists('use_slidedown_permission_message_for_https', $onesignal_wp_settings) && $onesignal_wp_settings['use_slidedown_permission_message_for_https'] == '1') {
                    ?>
              oneSignal_options['autoRegister'] = false;
              OneSignal.showHttpPrompt();
              OneSignal.init(window._oneSignalInitOptions);
              <?php
                } else {
                    ?>
              OneSignal.init(window._oneSignalInitOptions);
              <?php
                }
            }
        } else {
            ?>
          /* OneSignal: Using custom SDK initialization. */
          <?php
        } ?>
      });

      function documentInitOneSignal() {
        var oneSignal_elements = document.getElementsByClassName("OneSignal-prompt");

        <?php
        if ($onesignal_wp_settings['use_modal_prompt'] == '1') {
            echo "var oneSignalLinkClickHandler = function(event) { OneSignal.push(['registerForPushNotifications', {modalPrompt: true}]); event.preventDefault(); };";
        } else {
            echo "var oneSignalLinkClickHandler = function(event) { OneSignal.push(['registerForPushNotifications']); event.preventDefault(); };";
        } ?>
        for(var i = 0; i < oneSignal_elements.length; i++)
          oneSignal_elements[i].addEventListener('click', oneSignalLinkClickHandler, false);
      }

      if (document.readyState === 'complete') {
           documentInitOneSignal();
      }
      else {
           window.addEventListener("load", function(event){
               documentInitOneSignal();
          });
      }
    </script>

<?php
}