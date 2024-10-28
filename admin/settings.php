<?php
if ( ! defined( 'ABSPATH' ) ) exit;

require_once PWAFORWP_PLUGIN_DIR.'/admin/class-pwaforwp-utility.php';

function pwaforpw_add_menu_links() {

    $license_alert_icon = '';
    $days = '';
        $get_license_info = get_option( 'pwawppro_license_info');
        if($get_license_info){
            $pwawp_pro_expires = gmdate('Y-m-d', strtotime($get_license_info->expires));
            $license_info_lifetime = $get_license_info->expires;
                    $today = gmdate('Y-m-d');
        $exp_date = $pwawp_pro_expires;
        $date1 = date_create($today);
        $date2 = date_create($exp_date);
        $diff = date_diff($date1,$date2);
        $days = $diff->format("%a");
        $show_exp_lic = 0;
        if( $license_info_lifetime == 'lifetime' ){
            $show_exp_lic = 1;
        }

        else if($exp_date > $today){
            $show_exp_lic = 1;
        }
    $license_alert_icon = $show_exp_lic==0 ? "<span class='pwaforwp_pro_icon dashicons dashicons-warning pwaforwp_pro_alert'></span>": "" ;
    }

	// Main menu page
	add_menu_page( __( 'Progressive Web Apps For WP', 'pwa-for-wp' ), 
                __( 'PWA', 'pwa-for-wp' ).$license_alert_icon, 
				pwaforwp_current_user_can(),                
                'pwaforwp',
                'pwaforwp_admin_interface_render',
                PWAFORWP_PLUGIN_URL.'images/menu-icon.svg', 100 );
	// Settings page - Same as main menu page
	add_submenu_page( 'pwaforwp',
                esc_html__( 'Progressive Web Apps For WP', 'pwa-for-wp' ),
                esc_html__( 'Settings', 'pwa-for-wp' ),
				pwaforwp_current_user_can(),                
                'pwaforwp',
                'pwaforwp_admin_interface_render');	
                                
	if(!pwaforwp_addons_is_active() && current_user_can('manage_options')){
	    global $submenu;
		$permalink = 'javasctipt:void(0);';
		$submenu['pwaforwp'][] = array( '<div style="color:#fff176;" onclick="window.open(\'https://pwa-for-wp.com/pricing/\')">'.esc_html__( 'Upgrade To Premium', 'pwa-for-wp' ).'</div>', 'manage_options', $permalink);
	}
}
add_action( 'admin_menu', 'pwaforpw_add_menu_links');

function pwaforwp_admin_interface_render(){
    if ( ! current_user_can( pwaforwp_current_user_can() ) ) {
		return;
	}			
	// Handing save settings
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- we are not processing form here
	if ( isset( $_GET['settings-updated'] ) ) {	
                                                                                    
        $service_worker = new PWAFORWP_Service_Worker();
        $service_worker->pwaforwp_store_latest_post_ids();
        update_option('pwaforwp_update_pre_cache_list', 'disable');
        pwaforwp_required_file_creation();                                 
		settings_errors();
	}
	$tab = pwaforwp_get_tab('dashboard', array('dashboard','general', 'features','push_notification', 'other_setting', 'precaching_setting', 'tools', 'premium_features','help'));
                                                                        
	?>
	<div class="wrap pwaforwp-wrap">			
	<?php
        if ( class_exists('PWAFORWPPROExtensionManager') ) {
            $license_info = get_option( 'pwawppro_license_info');
            if ( defined('PWAFORWPPRO_PLUGIN_DIR') && !empty($license_info) ){
                $pwaforwp_pro_manager = PWAFORWPPRO_PLUGIN_DIR.'inc/pwa_pro_ext_manager_lic_data.php';                
                if( file_exists($pwaforwp_pro_manager) ){
                    require_once $pwaforwp_pro_manager;
                }
            }
        }else {
            if ( !class_exists('PWAFORWPPROExtensionManager') && !defined('PWAFORWPPRO_PLUGIN_DIR')  ){
                $settings = pwaforwp_defaultSettings(); 	
            	$add_on_list = pwaforwp_list_addons();
            	$expiredLicensedata  = array();

				foreach($add_on_list as $key => $on){
					if( is_plugin_active ($on['p-slug']) ){
					    $addon_prefix = $on['p-smallcaps-prefix'];                        
                        if(isset($settings[$addon_prefix.'_addon_license_key'])){
                          $license_key =   $settings[$addon_prefix.'_addon_license_key'];
                        }
                        $license_status =  !empty($settings[$addon_prefix.'_addon_license_key_status']) ? $settings[$addon_prefix.'_addon_license_key_status'] : NULL ;

                        if(isset($settings[$addon_prefix.'_addon_license_key_message'])){
                          $license_status_msg =   $settings[$addon_prefix.'_addon_license_key_message'];
                        }

                        if (isset($settings[$addon_prefix.'_addon_license_key_user_name'])) {
                            $license_user_name =   $settings[$addon_prefix.'_addon_license_key_user_name'];
                        }

                        
                        $license_download_id =  !empty($settings[$addon_prefix.'_addon_license_key_download_id']) ? $settings[$addon_prefix.'_addon_license_key_download_id'] : NULL ;

                        if (isset($settings[$addon_prefix.'_addon_license_key_expires'])) {
                            $license_expires =   $settings[$addon_prefix.'_addon_license_key_expires'];
                            $expiredLicensedata[$addon_prefix] = $license_expires < 0 ? 1 : 0 ;
                        }
                        if (isset($addon_prefix)) {
                        $license_name = $addon_prefix;
                        }
                        $settings[$addon_prefix.'_name'] = $license_name;
                        $license_name =  !empty( $settings[$addon_prefix.'_name']) ? $settings[$addon_prefix.'_name'] : NULL ;
                    }
                }
          
                if ( isset( $license_user_name )  && $license_user_name!=="" && isset( $license_expires )   ){
                    if ( !empty( $addon_prefix ) && $license_status =='active' ) {
                        $renew = "no";
                        $license_exp = "";
                        $license_k = $license_key;
                        $download_id = $license_download_id;
                        $days = $license_expires;
                        $one_of_plugin_expired = 0;
                        if ( in_array( 1, $expiredLicensedata ) ){
                                $one_of_plugin_expired = 1;
                            }
                        if ( !in_array( 0, $expiredLicensedata ) ){
                                $one_of_plugin_expired = 0;
                            }
                        $exp_id = $expire_msg = $renew_mesg = $span_class = $expire_msg_before = $ZtoS_days = $refresh_addon = $refresh_addon_user = $alert_icon = $auto_refresh_data = $user_refresh_addon = '';
                        $ext_settings_url = 'ext_url';
                        if ( $days == 'Lifetime' ) {
                            $expire_msg = " ".esc_html__('Valid for Lifetime', 'pwa-for-wp')." ";
                            $expire_msg_before = '<span class="pwaforwp_before_msg_active">'.esc_html__('Your License is', 'pwa-for-wp').'</span>';
                            $span_class = "pwaforwp_addon_icon dashicons dashicons-yes pwaforwp_pro_icon";
                            $color = 'color:green';
                        }elseif( $days >= 0 && $days <= 7 ){
                            $renew_url = "https://pwa-for-wp.com/order/?edd_license_key=".$license_k."&download_id=".$download_id."";
                            if ($one_of_plugin_expired == 1) {
                                $expire_msg_before = '<span class="pwaforwp_addon_inactive">'.esc_html__('One of your', 'pwa-for-wp').' <span class="lessthan_0" style="color:red;">'.esc_html__('license key is', 'pwa-for-wp').'</span><span class=\'pwaforwp_one_of_expired\'> Expired </span></span><a target="blank" class="pwaforwp-renewal-license" href="'.$renew_url.'"><span class="pwaforwp-renew-lic">'.esc_html__('Renew', 'pwa-for-wp').'</span></a>';                    
                            }else{
                                $expire_msg_before = '<span class="before_msg">'.esc_html__('Your', 'pwa-for-wp').' <span class="pwaforwp_zero_to_seven">'.esc_html__('License is', 'pwa-for-wp').'</span></span> <span class="pwaforwp-addon-alert">'.esc_html__('expiring in', 'pwa-for-wp').' '.$days.' '.esc_html__('days', 'pwa-for-wp').'</span><a target="blank" class="pwaforwp-renewal-license" href="'.$renew_url.'"><span class="pwaforwp-renew-lic">'.esc_html__('Renew', 'pwa-for-wp').'</span></a>';
                            }
                            $color = 'color:green';
                            $alert_icon = '<span class="pwaforwp_addon_icon dashicons dashicons-warning pwaforwp_pro_warning"></span>';
                            $original_license = $license_key;
                            // Check by Auto Refresh if user did renewal
                           $trans_check = get_transient( 'pwaforwp_addon_zto7' );
                            if ( $trans_check !== 'pwaforwp_addon_zto7_value' ){
                               $auto_refresh_data = '<a addon-is-expired id="pwaforwp_auto_refresh-" days_remaining="'.esc_attr($days).'" licensestatusinternal="'.esc_attr($license_status).'" add-on="'.esc_attr($license_name).'" class="days_remain" data-attr="'.esc_attr($original_license).'" add-onname="pwaforwp_settings['.esc_attr(strtolower($license_name)).'_addon_license_key]"><i addon-is-expired class="dashicons dashicons-update-alt" id="auto_refresh"></i></a>';
                                $auto_refresh_data.= '<input type="hidden" license-status="inactive"  licensestatusinternal="'.esc_attr($license_status).'" add-on="'.esc_attr(strtolower($license_name)).'" class="button button-default pwaforwp_license_activation '.esc_attr($license_status).'mode '.esc_attr(strtolower($license_name)).''.esc_attr(strtolower($license_name)).'" id="pwaforwp_license_deactivation_internal">';
                            }
                            // Check by Auto Refresh End
                        }elseif( $days>=0 && $days<=30 ){
                            $renew_url = "https://pwa-for-wp.com/order/?edd_license_key=".$license_k."&download_id=".$download_id."";
                            if ($one_of_plugin_expired == 1) {
                                $expire_msg_before = '<span class="pwaforwp_addon_inactive">'.esc_html__('One of your', 'pwa-for-wp').' <span class="lessthan_0" style="color:red;">'.esc_html__('license key is', 'pwa-for-wp').'</span><span class=\'pwaforwp_one_of_expired\'>'.esc_html__('Expired', 'pwa-for-wp').'  </span></span><a target="blank" class="pwaforwp-renewal-license" href="'.$renew_url.'"><span class="pwaforwp-renew-lic">'.esc_html__('Renew', 'pwa-for-wp').'</span></a>';                    
                            }else{
                                $expire_msg_before = '<span class="before_msg">'.esc_html__('Your', 'pwa-for-wp').' <span class="pwaforwp_zero_to_30">'.esc_html__('License is', 'pwa-for-wp').'</span></span> <span class="pwaforwp-addon-alert">'.esc_html__('expiring in', 'pwa-for-wp').' '.$days.' '.esc_html__('days', 'pwa-for-wp').'</span><a target="blank" class="pwaforwp-renewal-license" href="'.$renew_url.'"><span class="pwaforwp-renew-lic">'.esc_html__('Renew', 'pwa-for-wp').'</span></a>';
                            }
                            $color = 'color:green';
                            $alert_icon = '<span class="pwaforwp_addon_icon dashicons dashicons-warning pwaforwp_pro_warning"></span>';
                        }elseif($days<0){
                            $ext_settings_url = 'ext_settings_url';
                            $renew_url = "https://pwa-for-wp.com/order/?edd_license_key=".$license_k."&download_id=".$download_id."";
                            if ($one_of_plugin_expired == 1) {
                                $expire_msg_before = '<span class="pwaforwp_addon_inactive">'.esc_html__('One of your', 'pwa-for-wp').' <span class="lessthan_0" style="color:red;">'.esc_html__('license key is', 'pwa-for-wp').'</span></span>';
                            }else{
                                $expire_msg_before = '<span class="pwaforwp_addon_inactive">'.esc_html__('Your', 'pwa-for-wp').' <span class="lessthan_0" style="color:red;">'.esc_html__('License has been', 'pwa-for-wp').'</span></span>';
                            }
                            $expire_msg = " ".esc_html__('Expired', 'pwa-for-wp')." ";
                            $exp_class = 'expired';
                            $exp_id = 'pwaforwp-exp';
                            $exp_class_2 = 'renew_license_key_';
                            $span_class = "pwaforwp_addon_icon dashicons pwaforwp-dashicons-no";

            		        $original_license = $license_key;
            		        $trans_check = get_transient( 'pwaforwp_addons_expired' );
                            if ( $trans_check !== 'pwaforwp_addons_expired_value' ){
                	           $refresh_addon = '<a addon-is-expired id="pwaforwp_refresh_expired_addon-" days_remaining="'.esc_attr($days).'" licensestatusinternal="'.esc_attr($license_status).'" add-on="'.esc_attr($license_name).'" class="days_remain" data-attr="'.esc_attr($original_license).'" add-onname="pwaforwp_settings['.esc_attr(strtolower($license_name)).'_addon_license_key]">
                	                   <i addon-is-expired class="dashicons dashicons-update-alt" id="pwaforwp_refresh_expired_addon"></i>
                	            </a>';
            		          $refresh_addon.= '<input type="hidden" license-status="inactive"  licensestatusinternal="'.esc_attr($license_status).'" add-on="'.esc_attr(strtolower($license_name)).'" class="button button-default pwaforwp_license_activation '.esc_attr($license_status).'mode '.esc_attr(strtolower($license_name)).''.esc_attr(strtolower($license_name)).'" id="pwaforwp_license_deactivation_internal">';
            		        }
            		        // Option for User to manually Check the updated Data if he has renewed after the Expiration

            		        $user_refresh_addon = '<a addon-is-expired id="pwaforwp_user_refresh-" days_remaining="'.esc_attr($days).'" licensestatusinternal="'.esc_attr($license_status).'" add-on="'.esc_attr($license_name).'" class="days_remain" data-attr="'.esc_attr($original_license).'" add-onname="pwaforwp_settings['.esc_attr(strtolower($license_name)).'_addon_license_key]">
                                    <i addon-is-expired class="dashicons dashicons-update-alt" id="user_refresh"></i>
                                </a>
                                <input type="hidden" license-status="inactive"  licensestatusinternal="'.esc_attr($license_status).'" add-on="'.esc_attr(strtolower($license_name)).'" class="button button-default pwaforwp_license_activation '.esc_attr($license_status).'mode '.esc_attr(strtolower($license_name)).''.esc_attr(strtolower($license_name)).'" id="pwaforwp_license_deactivation_internal">';

            			    $renew_mesg = '<a target="blank" class="pwaforwp-renewal-license" href="'.esc_url($renew_url).'"><span class="pwaforwp-renew-lic">'.esc_html__('Renew', 'pwa-for-wp').'</span></a>';
    					    $color = 'color:red';
                        }else{
                            if ($one_of_plugin_expired == 1) {
                                $expire_msg_before = '<span class="pwaforwp_before_msg_active">'.esc_html__('One of your','pwa-for-wp').' <span class=">than_30" style="color:red;">'.esc_html__('license key is','pwa-for-wp').'</span></span>';    
                            }else{
                                $expire_msg_before = '<span class="pwaforwp_before_msg_active">'.esc_html__('Your License is', 'pwa-for-wp').'</span>';
                            }
                            if ($one_of_plugin_expired == 1) {
                                $renew_url = "https://pwa-for-wp.com/order/?edd_license_key=".$license_k."&download_id=".$download_id."";
                                $expire_msg = " <span class='pwaforwp_one_of_expired'>".esc_html__('Expired','pwa-for-wp')."</span> ";
                                $renew_mesg = '<a target="blank" class="pwaforwp-renewal-license" href="'.esc_url($renew_url).'"><span class="pwaforwp-renew-lic">'.esc_html__('Renew', 'pwa-for-wp').'</span></a>';
                            }else{
                                $expire_msg = esc_html__(" Active ",'pwa-for-wp');
                            }
                            if ($one_of_plugin_expired == 1) {
                                $span_class = "pwaforwp_addon_icon dashicons pwaforwp-dashicons-no";
                            }else{
                                $span_class = "pwaforwp_addon_icon dashicons dashicons-yes pwaforwp_pro_icon";
                            }
                            if ($one_of_plugin_expired == 1) {
                                $color = 'color:red';
                            }else{
                                $color = 'color:green';
                            }
                        }
                    
                        $pwaforwp_addon_license_info = "<div class='pwaforwp-main'>
                <span class='pwaforwp-info'>
                ".$alert_icon."<span class='pwaforwp-activated-plugins'>".esc_html__('Hi', 'pwa-for-wp')." <span class='pwaforwp_key_user_name'>".esc_html($license_user_name)."</span>".','."
                <span id='activated-plugins-days_remaining' days_remaining=".$days."> ".$expire_msg_before." <span expired-days-data=".$days." class='pwaforwp_expiredinner_span' id=".esc_attr($exp_id).">".$expire_msg."</span></span>
                <span class='".$span_class."'></span>".$renew_mesg.$refresh_addon.$refresh_addon_user ;
                $trans_check = get_transient( 'pwaforwp_addons_set_transient' );
            
            $pwaforwp_addon_license_info .= $ZtoS_days."
            </span>
            </div>";
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- all data already escapped.
			echo $pwaforwp_addon_license_info;
                    }
                }

            }
        }
    
    ?>
    <h1><?php echo esc_html__('Progressive Web Apps For WP', 'pwa-for-wp'); ?></h1>

			<div class="pwaforwp-main-wrapper">
				<h2 class="nav-tab-wrapper pwaforwp-tabs">
					<?php
					echo '<a href="' . esc_url(pwaforwp_admin_link('dashboard')) . '" class="nav-tab ' . esc_attr( $tab == 'dashboard' ? 'nav-tab-active' : '') . '"><span class="dashicons dashicons-dashboard"></span> ' . esc_html__('Dashboard', 'pwa-for-wp') . '</a>';

					echo '<a href="' . esc_url(pwaforwp_admin_link('general')) . '" class="nav-tab ' . esc_attr( $tab == 'general' ? 'nav-tab-active' : '') . '"><span class="dashicons dashicons-welcome-view-site"></span> ' . esc_html__('Setup','pwa-for-wp') . '</a>';

					echo '<a href="' . esc_url(pwaforwp_admin_link('features')) . '" class="nav-tab ' . esc_attr( $tab == 'features' ? 'nav-tab-active' : '') . '"><span class="dashicons dashicons-admin-generic"></span> ' . esc_html__('Features','pwa-for-wp') . '</a>';
		            
		            echo '<a href="' . esc_url(pwaforwp_admin_link('tools')) . '" class="nav-tab ' . esc_attr( $tab == 'tools' ? 'nav-tab-active' : '') . '"> ' . esc_html__('Tools','pwa-for-wp') . '</a>';

		            echo '<a href="' . esc_url(pwaforwp_admin_link('other_setting')) . '" class="nav-tab ' . esc_attr( $tab == 'other_setting' ? 'nav-tab-active' : '') . '"> ' . esc_html__('Advance','pwa-for-wp') . '</a>';

                    $license_alert = '';
                    if ( function_exists('call_to_action_for_pwa_updater')
                         || function_exists('pwaforwp_lilfp_updater')
                         || function_exists('data_analytics_for_pwa_updater')
                          || function_exists('pwa_to_apk_plugin_for_pwa_updater')
                          || function_exists('pull_to_refresh_for_pwa_updater')
                           || function_exists('scroll_progress_bar_for_pwa_updater')
                           || function_exists('offline_forms_pwa_for_pwa_updater')
                           || function_exists('buddypress_pwaforwp_for_pwa_updater')
                           || function_exists('qafp_plugin_for_pwa_updater')
                           || function_exists('nbfp_plugin_for_pwa_updater')
                           || function_exists('mcfp_plugin_for_pwa_updater') ) {
                        $license_alert = isset($days) && $days<=30 && $days!=='Lifetime' ? "<span class='pwaforwp_addon_icon dashicons dashicons-warningpwaforwp_pro_alert' ></span>": ''  ;
                }
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- all data already escapped.
		        echo '<a href="' . esc_url(pwaforwp_admin_link('premium_features')) . '" class="nav-tab ' . esc_attr( $tab == 'premium_features' ? 'nav-tab-active' : '') . '" data-extmgr="'. ( class_exists('PWAFORWPPROExtensionManager')? "yes": "no" ).'"> '.$license_alert.' ' . esc_html__('Premium Features','pwa-for-wp') . '</a>';

				echo '<a href="' . esc_url(pwaforwp_admin_link('help')) . '" class="nav-tab ' . esc_attr( $tab == 'help' ? 'nav-tab-active' : '') . '"><span class="dashicons dashicons-editor-help"></span> ' . esc_html__('Help','pwa-for-wp') . '</a>';
					?>
				</h2>
	            <form action="options.php" method="post" enctype="multipart/form-data" class="pwaforwp-settings-form">		
					<div class="form-wrap">
						<?php
						// Output nonce, action, and option_page fields for a settings page.
						settings_fields( 'pwaforwp_setting_dashboard_group' );						
						
						echo "<div class='pwaforwp-dashboard' ".( $tab != 'dashboard' ? 'style="display:none;"' : '').">";
						// Status
						do_settings_sections( 'pwaforwp_dashboard_section' );	// Page slug
						echo "</div>";

						echo "<div class='pwaforwp-general pwaforwp-subheading-wrap' ".( $tab != 'general' ? 'style="display:none;"' : '').">";
							/*Sub menu tabs*/

							echo '<div class="pwaforwp-sub-tab-headings">
									<span data-tab-id="subtab-general" class="selected">'.esc_html__('General','pwa-for-wp').'</span>&nbsp;|&nbsp;
									<span data-tab-id="subtab-design">'.esc_html__('Design','pwa-for-wp').'</span>
								</div>';
							echo '<div class="pwaforwp-subheading">';
								// general Application Settings
								echo '<div id="subtab-general" class="selected">';
										do_settings_sections( 'pwaforwp_general_section' );
								echo '</div>';
								echo '<div id="subtab-design" class="pwaforwp-hide">';
										do_settings_sections( 'pwaforwp_design_section' );
								echo '</div>';
							echo '</div>';

						echo "</div>";

						//feature
						echo "<div class='pwaforwp-features' ".( $tab != 'features' ? 'style="display:none;"' : '').">";
							// design Application Settings
							pwaforwp_features_settings();
							
						echo "</div>";
			                        
			            
			                        
			            echo "<div class='pwaforwp-tools pwaforwp-subheading-wrap' ".( $tab != 'tools' ? 'style="display:none;"' : '').">";
							// other_setting Application Settings


								/*Sub menu tabs*/
								echo '<div class="pwaforwp-sub-tab-headings">
										<span data-tab-id="subtab-tools" class="selected">'.esc_html__('Tools','pwa-for-wp').'</span>&nbsp;|&nbsp;
										<span data-tab-id="subtab-compatibility">'.esc_html__('Compatibility','pwa-for-wp').'</span>
									</div>';
								echo '<div class="pwaforwp-subheading">';
									// general Application Settings
									echo '<div id="subtab-tools" class="selected">';
											do_settings_sections( 'pwaforwp_tools_section' );	// Page slug
									echo '</div>';
									echo '<div id="subtab-compatibility" class="pwaforwp-hide">';
											do_settings_sections( 'pwaforwp_compatibility_setting_section' );
									echo '</div>';
								echo '</div>';






						echo "</div>";
			                        
			                        echo "<div class='pwaforwp-premium_features' ".( $tab != 'premium_features' ? 'style="display:none;"' : '').">";
							// other_setting Application Settings
							do_settings_sections( 'pwaforwp_premium_features_section' );	// Page slug
						echo "</div>";
			                        
						echo "<div class='pwaforwp-other_setting' ".( $tab != 'other_setting' ? 'style="display:none;"' : '').">";
							// other_setting Application Settings
							do_settings_sections( 'pwaforwp_other_setting_section' );	// Page slug
						echo "</div>";
			                       
						echo "<div class='pwaforwp-help' ".( $tab != 'help' ? 'style="display:none;"' : '').">";
							echo "<h3>".esc_html__('Documentation', 'pwa-for-wp')."</h3><a target=\"_blank\" class='button' href=\"https://ampforwp.com/tutorials/article/pwa-for-amp/\">".esc_html__('View Setup Documentation', 'pwa-for-wp')."</a>";
							?>	
			        	                   <div class="pwa_contact_us_div">
			        	                   	<h3><?php echo esc_html__('Ask for Technical Support', 'pwa-for-wp') ?></h3>
			        	                   	<p><?php echo esc_html__('We are always available to help you with anything', 'pwa-for-wp') ?></p>
						            <ul>
						                <li><label for="pwaforwp_query_customer"><?php echo esc_html__('Are you existing Premium Customer?', 'pwa-for-wp'); ?></label>
						                    <select class="regular-select" rows="5" cols="60" id="pwaforwp_query_customer" name="pwaforwp_query_customer">
						                    	<option value=""><?php echo esc_html__('Select', 'pwa-for-wp'); ?></option>
						                    	<option value="Yes"><?php echo esc_html__('Yes', 'pwa-for-wp'); ?></option>
						                    	<option value="No"><?php echo esc_html__('No', 'pwa-for-wp'); ?></option>
						                    </select>
						                </li> 
						                <li><label for="pwaforwp_query_message"><?php echo esc_html__('Message', 'pwa-for-wp'); ?></label>
						                    <textarea rows="5" cols="60" id="pwaforwp_query_message" name="pwaforwp_query_message" class="regular-textarea"></textarea>
						                    <br>
						                    <p class="pwa-query-success pwa_hide"><?php echo esc_html__('Message sent successfully, Please wait we will get back to you shortly', 'pwa-for-wp'); ?></p>
						                    <p class="pwa-query-error pwa_hide"><?php echo esc_html__('Message not sent. please check your network connection', 'pwa-for-wp'); ?></p>
						                </li> 
						                <li><button class="button pwa-send-query"><?php echo esc_html__('Send Message', 'pwa-for-wp'); ?></button></li>
						            </ul>            
						                   
						        </div>
							<?php
							// design Application Settings
							do_settings_sections( 'amp_pwa_help_section' );	// Page slug
						echo "</div>";

						?>
					</div>
					<div class="button-wrapper">
		                            <input type="hidden" name="pwaforwp_settings[manualfileSetup]" value="1">
						<?php
						// Output save settings button
					submit_button( esc_html__('Save Settings', 'pwa-for-wp') );
						?>
					</div>
				</form>

			</div>
			<div class="pwaforwp-settings-second-div">
		        <?php
				if(!pwaforwp_addons_is_active()) { ?>
		         <div class="pwaforwp-upgrade-pro">
		        	<h2><?php echo esc_html__('Upgrade to Pro!','pwa-for-wp') ?></h2>
		        	<ul>
		        		<li><?php echo esc_html__('Premium features','pwa-for-wp') ?></li>
		        		<li><?php echo esc_html__('Dedicated PWA Support','pwa-for-wp') ?></li>
		        		<li><?php echo esc_html__('Active Development','pwa-for-wp') ?></li>
		        	</ul>
		        	<a target="_blank" href="https://pwa-for-wp.com/pricing/"><?php echo esc_html__('UPGRADE NOW','pwa-for-wp') ?></a>
		
 				</div>
		         <?php  } ?>
                 <?php            
           
}


/*
	WP Settings API
*/
add_action('admin_init', 'pwaforwp_settings_init');

function pwaforwp_settings_init(){
	$settings = pwaforwp_defaultSettings(); 
	if( isset($settings['loading_icon_display_admin']) && $settings['loading_icon_display_admin'] && is_admin() ){
    	add_action('admin_footer', 'pwaforwp_loading_icon');
    	add_action('admin_print_footer_scripts', 'pwaforwp_loading_icon_scripts');
    	add_action('admin_print_styles', 'pwaforwp_loading_icon_styles');
	}
	add_action('admin_print_styles', 'pwaforwp_loading_select2_styles');
	register_setting( 'pwaforwp_setting_dashboard_group', 'pwaforwp_settings','pwaforwp_sanitize_fields' );

	add_settings_section('pwaforwp_dashboard_section', esc_html__('Installation Status','pwa-for-wp').'<span class="pwafw-tooltip"><i class="dashicons dashicons-editor-help"></i> 
	                    <span class="pwafw-help-subtitle">'.esc_html__('PWA status verification', 'pwa-for-wp').' <a href="https://pwa-for-wp.com/docs/article/how-to-install-setup-pwa-in-amp/" target="_blank">'.esc_html__('Learn more', 'pwa-for-wp').'</a></span>
	                </span>', '__return_false', 'pwaforwp_dashboard_section');
		// Manifest status
		add_settings_field(
			'pwaforwp_manifest_status',								// ID
			'',			// Title
			'pwaforwp_files_status_callback',					// Callback
			'pwaforwp_dashboard_section',							// Page slug
			'pwaforwp_dashboard_section'							// Settings Section ID
		);

                // HTTPS status				

	add_settings_section('pwaforwp_general_section', __return_false(), '__return_false', 'pwaforwp_general_section');

		// Application Name
		add_settings_field(
			'pwaforwp_app_name',									// ID
			esc_html__('App Name', 'pwa-for-wp'),	// Title
			'pwaforwp_app_name_callback',									// CB
			'pwaforwp_general_section',						// Page slug
			'pwaforwp_general_section'						// Settings Section ID
		);

		// Application Short Name
		add_settings_field(
			'pwaforwp_app_short_name',								// ID
			esc_html__('App Short Name', 'pwa-for-wp'),	// Title
			'pwaforwp_app_short_name_callback',							// CB
			'pwaforwp_general_section',						// Page slug
			'pwaforwp_general_section'						// Settings Section ID
		);

		// Description
		add_settings_field(
			'pwaforwp_app_description',									// ID
			esc_html__('App Description', 'pwa-for-wp' ),		// Title
			'pwaforwp_description_callback',								// CB
			'pwaforwp_general_section',						// Page slug
			'pwaforwp_general_section'						// Settings Section ID
		);
		
		// Application Icon
		add_settings_field(
			'pwaforwp_app_icons',										// ID
			esc_html__('App Icon', 'pwa-for-wp'),	// Title
			'pwaforwp_app_icon_callback',									// Callback function
			'pwaforwp_general_section',						// Page slug
			'pwaforwp_general_section'						// Settings Section ID
		);
		
		// Application Maskable Icon
		add_settings_field(
			'pwaforwp_app_maskable_icons',										// ID
			esc_html__('App Maskable Icon', 'pwa-for-wp'),	// Title
			'pwaforwp_app_maskable_icon_callback',									// Callback function
			'pwaforwp_general_section',						// Page slug
			'pwaforwp_general_section'						// Settings Section ID
		);
		
		// Monochrome Icon
		add_settings_field(
			'pwaforwp_monochrome',										// ID
			esc_html__('Monochrome Icon', 'pwa-for-wp'),	// Title
			'pwaforwp_monochrome_callback',									// Callback function
			'pwaforwp_general_section',						// Page slug
			'pwaforwp_general_section'						// Settings Section ID
		);
		
		// Splash Screen Icon
		add_settings_field(
			'pwaforwp_app_splash_icon',									// ID
			esc_html__('App Splash Screen Icon', 'pwa-for-wp'),	// Title
			'pwaforwp_splash_icon_callback',								// Callback function
			'pwaforwp_general_section',						// Page slug
			'pwaforwp_general_section'						// Settings Section ID
		);
		
		// Splash Screen Icon
		add_settings_field(
			'pwaforwp_app_splash_maskable_icon',									// ID
			esc_html__('App Splash Maskable Icon', 'pwa-for-wp'),	// Title
			'pwaforwp_splash_maskable_icon_callback',								// Callback function
			'pwaforwp_general_section',						// Page slug
			'pwaforwp_general_section'						// Settings Section ID
		);

        // Screenshot Icon
        add_settings_field(
            'pwaforwp_app_screenshots',                                       // ID
            esc_html__('APP Screenshots', 'pwa-for-wp'),   // Title
            'pwaforwp_app_screenshots_callback',                                   // Callback function
            'pwaforwp_general_section',                     // Page slug
            'pwaforwp_general_section'                      // Settings Section ID
        );

		// Offline Page
		add_settings_field(
			'pwaforwp_offline_page',								// ID
			esc_html__('Offline Page', 'pwa-for-wp'),		// Title
			'pwaforwp_offline_page_callback',								// CB
			'pwaforwp_general_section',						// Page slug
			'pwaforwp_general_section'						// Settings Section ID
		);

		// 404 Page
		add_settings_field(
			'pwaforwp_404_page',								// ID
			esc_html__('404 Page', 'pwa-for-wp'),		// Title
			'pwaforwp_404_page_callback',								// CB
			'pwaforwp_general_section',						// Page slug
			'pwaforwp_general_section'						// Settings Section ID
		);
                
                // Start page
		add_settings_field(
			'pwaforwp_start_page',								// ID
			esc_html__('Start Page', 'pwa-for-wp'),		// Title
			'pwaforwp_start_page_callback',								// CB
			'pwaforwp_general_section',						// Page slug
			'pwaforwp_general_section'						// Settings Section ID
		);
		
		// Orientation
		add_settings_field(
			'pwaforwp_orientation',									// ID
			esc_html__('Orientation', 'pwa-for-wp'),		// Title
			'pwaforpw_orientation_callback',								// CB
			'pwaforwp_general_section',						// Page slug
			'pwaforwp_general_section'						// Settings Section ID
		); 

		// Display
		add_settings_field(
			'pwaforwp_display',									// ID
			esc_html__('Display', 'pwa-for-wp'),		// Title
			'pwaforpw_display_callback',								// CB
			'pwaforwp_general_section',						// Page slug
			'pwaforwp_general_section'						// Settings Section ID
		);
		// Apple mobile web app status bar style
		add_settings_field(
			'pwaforwp_ios_status_bar',									// ID
			esc_html__('iOS APP Status Bar', 'pwa-for-wp'),		// Title
			'pwaforwp_apple_status_bar_callback',								// CB
			'pwaforwp_general_section',						// Page slug
			'pwaforwp_general_section'						// Settings Section ID
		);
		
		add_settings_field(
			'pwaforwp_prefer_related_applications',									// ID
			esc_html__('Prefer Related Application', 'pwa-for-wp'),	// Title
			'pwaforwp_prefer_related_applications_callback',								// Callback function
			'pwaforwp_general_section',						// Page slug
			'pwaforwp_general_section'						// Settings Section ID
		);
		add_settings_field(
			'pwaforwp_app_related_applications',									// ID
			esc_html__('Related Application', 'pwa-for-wp'),	// Title
			'pwaforwp_related_applications_callback',								// Callback function
			'pwaforwp_general_section',						// Page slug
			'pwaforwp_general_section'						// Settings Section ID
		);

	add_settings_section('pwaforwp_design_section', 'Splash Screen', '__return_false', 'pwaforwp_design_section');
		// Splash Screen Background Color
		add_settings_field(
			'pwaforwp_background_color',							// ID
			esc_html__('Background Color', 'pwa-for-wp'),	// Title
			'pwaforwp_background_color_callback',							// CB
			'pwaforwp_design_section',						// Page slug
			'pwaforwp_design_section'						// Settings Section ID
		);
		// Splash Screen Theme Color
		add_settings_field(
			'pwaforwp_theme_color',							// ID
			esc_html__('Theme Color', 'pwa-for-wp'),	// Title
			'pwaforwp_theme_color_callback',							// CB
			'pwaforwp_design_section',						// Page slug
			'pwaforwp_design_section'						// Settings Section ID
		);		
                
                                                
    add_settings_section('pwaforwp_tools_section', ' ', '__return_false', 'pwaforwp_tools_section');
                                                
		add_settings_field(
			'pwaforwp_reset_setting',							// ID
			esc_html__('Reset', 'pwa-for-wp'),	// Title
			'pwaforwp_reset_setting_callback',							// CB
			'pwaforwp_tools_section',						// Page slug
			'pwaforwp_tools_section'						// Settings Section ID
		);

        add_settings_field(
            'pwaforwp_cleandataonuninstall_setting',                           // ID
            '<label for="pwaforwp_settings_navigation_uninstall_setting"><b>'.esc_html__('Remove Data on Uninstall?', 'pwa-for-wp').'</b></label>',  // Title
            'pwaforwp_cleandataonuninstall_setting_callback',                          // CB
            'pwaforwp_tools_section',                       // Page slug
            'pwaforwp_tools_section'                        // Settings Section ID
        );


		//Misc tabs
		add_settings_section('pwaforwp_other_setting_section', ' ', '__return_false', 'pwaforwp_other_setting_section');
		add_settings_field(
			'pwaforwp_cdn_setting',							// ID
			'<label for="pwaforwp_settings_cdn_setting"><b>'.esc_html__('CDN Compatibility', 'pwa-for-wp').'</b></label>',	// Title
			'pwaforwp_cdn_setting_callback',							// CB
			'pwaforwp_other_setting_section',						// Page slug
			'pwaforwp_other_setting_section'						// Settings Section ID
		);                
        add_settings_field(
			'pwaforwp_offline_google_setting',							// ID
			'<label for="pwaforwp_settings[offline_google_setting]"><b>'.esc_html__('Offline Google Analytics', 'pwa-for-wp').'</b></label>',	// Title
			'pwaforwp_offline_google_setting_callback',							// CB
			'pwaforwp_other_setting_section',						// Page slug
			'pwaforwp_other_setting_section'						// Settings Section ID
		);
		add_settings_field(
			'pwaforwp_prefetch_manifest_setting',							// ID
			'<label for="pwaforwp_settings[prefetch_manifest_setting]"><b>'.esc_html__('Prefetch manifest URL link', 'pwa-for-wp').'</b></label>',	// Title
			'pwaforwp_prefetch_manifest_setting_callback',							// CB
			'pwaforwp_other_setting_section',						// Page slug
			'pwaforwp_other_setting_section'						// Settings Section ID
		);
                add_settings_field(
			'pwaforwp_force_update_sw_setting_setting',							// ID
			esc_html__('Force Update Service Worker', 'pwa-for-wp'),	// Title
			'pwaforwp_force_update_sw_setting_callback',							// CB
			'pwaforwp_other_setting_section',						// Page slug
			'pwaforwp_other_setting_section'						// Settings Section ID
		);                
		add_settings_field(
			'pwaforwp_add_to_home',									// ID
			esc_html__('Add To Home On Element Click', 'pwa-for-wp'),		// Title
			'pwaforwp_add_to_home_callback',								// CB
			'pwaforwp_other_setting_section',						// Page slug
			'pwaforwp_other_setting_section'						// Settings Section ID
		);

		add_settings_section('pwaforwp_addtohomescreen_setting_section', ' ', '__return_false', 'pwaforwp_addtohomescreen_setting_section');
        add_settings_field(
			'pwaforwp_custom_add_to_home',									// ID
			esc_html__('Custom Add To Home Banner', 'pwa-for-wp'),		// Title
			'pwaforwp_custom_add_to_home_callback',								// CB
			'pwaforwp_addtohomescreen_setting_section',						// Page slug
			'pwaforwp_addtohomescreen_setting_section'						// Settings Section ID
		);
       
        add_settings_field(
			'pwaforwp_cache_external_links_setting',							// ID
			'<label for="pwaforwp_settings_external_links_setting"><b>'.esc_html__('Cache External Links', 'pwa-for-wp').'</b></label>',	// Title
			'pwaforwp_cache_external_links_setting_callback',							// CB
			'pwaforwp_other_setting_section',						// Page slug
			'pwaforwp_other_setting_section'						// Settings Section ID
		);
        add_settings_section('pwaforwp_utmtracking_setting_section', ' ', '__return_false', 'pwaforwp_utmtracking_setting_section');
		add_settings_field(
			'pwaforwp_utm_setting',							// ID
			esc_html__('UTM Tracking', 'pwa-for-wp'),	// Title
			'pwaforwp_utm_setting_callback',							// CB
			'pwaforwp_utmtracking_setting_section',						// Page slug
			'pwaforwp_utmtracking_setting_section'						// Settings Section ID
		);                
                add_settings_field(
			'pwaforwp_exclude_url_setting',							// ID
			esc_html__('Urls Exclude From Cache List', 'pwa-for-wp'),	// Title
			'pwaforwp_url_exclude_from_cache_list_callback',							// CB
			'pwaforwp_other_setting_section',						// Page slug
			'pwaforwp_other_setting_section'						// Settings Section ID
		);
		add_settings_field(
			'pwaforwp_cache_time_setting',							// ID
			esc_html__('Cached time', 'pwa-for-wp'),	// Title
			'pwaforwp_cache_time_setting_callback',							// CB
			'pwaforwp_other_setting_section',						// Page slug
			'pwaforwp_other_setting_section'						// Settings Section ID
		);
		add_settings_field(
			'pwaforwp_avoid_default_banner_setting',							// ID
			'<label for="pwaforwp_settings[avoid_default_banner]"><b>'.esc_html__('Remove default banner', 'pwa-for-wp').'</b></label>',	// Title
			'pwaforwp_avoid_default_banner_setting_callback',							// CB
			'pwaforwp_other_setting_section',						// Page slug
			'pwaforwp_other_setting_section'						// Settings Section ID
		);
		add_settings_field(
			'pwaforwp_avoid_pwa_loggedin_setting',							// ID
			'<label for="pwaforwp_settings[avoid_loggedin_users]"><b>'.esc_html__('Remove pwa for logged in users', 'pwa-for-wp').'</b></label>',	// Title
			'pwaforwp_avoid_pwa_loggedin_setting_callback',							// CB
			'pwaforwp_other_setting_section',						// Page slug
			'pwaforwp_other_setting_section'						// Settings Section ID
		);
		add_settings_field(
			'pwaforwp_serve_cache_method_setting',							// ID
			'<label for="pwaforwp_settings[serve_js_cache_menthod]"><b>'.esc_html__('PWA alternative method', 'pwa-for-wp').'</b></label>',	// Title
			'pwaforwp_serve_cache_method_setting_callback',							// CB
			'pwaforwp_other_setting_section',						// Page slug
			'pwaforwp_other_setting_section'						// Settings Section ID
		);
		add_settings_field(
			'pwaforwp_reset_cookies_method_setting',							// ID
			'<label for="pwaforwp_settings[reset_cookies]"><b>'.esc_html__('Reset cookies','pwa-for-wp').'</b></label>',	// Title
			'pwaforwp_reset_cookies_method_setting_callback',							// CB
			'pwaforwp_other_setting_section',						// Page slug
			'pwaforwp_other_setting_section'						// Settings Section ID
		);
		add_settings_field(
			'pwaforwp_disallow_data_tracking_setting',							// ID
			esc_html__('Share Anonymous data for improving the UX', 'pwa-for-wp'),	// Title
			'pwaforwp_disallow_data_tracking_setting_callback',							// CB
			'pwaforwp_other_setting_section',						// Page slug
			'pwaforwp_other_setting_section'						// Settings Section ID
		);
		if( function_exists('is_super_admin') &&  is_super_admin() ){
			add_settings_field(
				'pwaforwp_disallow_data_tracking_setting',							// ID
				esc_html__('Role Based Access', 'pwa-for-wp'),	// Title
				'pwaforwp_role_based_access_setting_callback',							// CB
				'pwaforwp_other_setting_section',						// Page slug
				'pwaforwp_other_setting_section'						// Settings Section ID
			);
		}
		add_settings_field(
			'pwaforwp_offline_message_setting',							// ID
			'<label for="pwaforwp_settings[offline_message_setting]"><b>'.esc_html__('Offline Message', 'pwa-for-wp').'</b></label>',	// Title
			'pwaforwp_offline_message_setting_callback',							// CB
			'pwaforwp_other_setting_section',						// Page slug
			'pwaforwp_other_setting_section'						// Settings Section ID
		);
		add_settings_field(
			'pwaforwp_scrollbar_setting',							// ID
			'<label for="pwaforwp_settings[scrollbar_setting]"><b>'.esc_html__('Disable Scrollbar', 'pwa-for-wp').'</b></label>',	// Title
			'pwaforwp_scrollbar_setting_callback',							// CB
			'pwaforwp_other_setting_section',						// Page slug
			'pwaforwp_other_setting_section'						// Settings Section ID
		);
		add_settings_field(
			'pwaforwp_force_rememberme_setting',							// ID
			'<label for="pwaforwp_settings[force_rememberme]"><b>'.esc_html__('Force Remember me', 'pwa-for-wp').'</b></label>',	// Title
			'pwaforwp_force_rememberme_setting_callback',							// CB
			'pwaforwp_other_setting_section',						// Page slug
			'pwaforwp_other_setting_section'						// Settings Section ID
		);
		add_settings_section('pwaforwp_loaders_setting_section', ' ', '__return_false', 'pwaforwp_loaders_setting_section');
		add_settings_field(
			'pwaforwp_loading_setting',							// ID
			esc_html__('Loader', 'pwa-for-wp'),	// Title
			'pwaforwp_loading_setting_callback',							// CB
			'pwaforwp_loaders_setting_section',						// Page slug
			'pwaforwp_loaders_setting_section'						// Settings Section ID
		);
		add_settings_field(
			'pwaforwp_loading_color_setting',							// ID
			esc_html__('Loader color', 'pwa-for-wp'),	// Title
			'pwaforwp_loading_color_setting_callback',							// CB
			'pwaforwp_loaders_setting_section',						// Page slug
			'pwaforwp_loaders_setting_section'						// Settings Section ID
		);
		add_settings_field(
			'pwaforwp_loading_background_color_setting',							// ID
			esc_html__('Loader background color', 'pwa-for-wp'),	// Title
			'pwaforwp_loading_background_color_setting_callback',							// CB
			'pwaforwp_loaders_setting_section',						// Page slug
			'pwaforwp_loaders_setting_section'						// Settings Section ID
		);
		add_settings_field(
			'pwaforwp_show_pwa_option_setting',							// ID
			esc_html__('Show only in PWA', 'pwa-for-wp'),	// Title
			'pwaforwp_loading_display_inpwa_setting_callback',							// CB
			'pwaforwp_loaders_setting_section',						// Page slug
			'pwaforwp_loaders_setting_section'						// Settings Section ID
		);
		add_settings_field(
			'pwaforwp_loading_display_option_setting',							// ID
			esc_html__('Loader enable on', 'pwa-for-wp'),	// Title
			'pwaforwp_loading_display_setting_callback',							// CB
			'pwaforwp_loaders_setting_section',						// Page slug
			'pwaforwp_loaders_setting_section'						// Settings Section ID
		);
        do_action("pwaforwp_loading_icon_libraries", 'pwaforwp_loaders_setting_section');

                
		add_settings_field(
			'pwaforwp_caching_strategies_setting',							// ID
			'<h2>'.esc_html__('Caching Strategies', 'pwa-for-wp').'
			<span class="pwafw-tooltip"><i class="dashicons dashicons-editor-help"></i> 
	                    <span class="pwafw-help-subtitle">Caching preferences <a href="'.esc_url('https://pwa-for-wp.com/docs/article/what-is-caching-strategies-in-pwa-and-how-to-use-it/').'" target="_blank">'.esc_html__('Learn more', 'pwa-for-wp').'</a></span>
	                </span>
			</h2>',	// Title
			'pwaforwp_caching_strategies_setting_callback',							// CB
			'pwaforwp_other_setting_section',						// Page slug
			'pwaforwp_other_setting_section'						// Settings Section ID
		);

		add_settings_section('pwaforwp_compatibility_setting_section', '', '__return_false', 'pwaforwp_compatibility_setting_section');
                add_settings_field(
			'pwaforwp_one_signal_support',									// ID
			'<label for="pwaforwp_settings[one_signal_support_setting]"><b>'.esc_html__('OneSignal', 'pwa-for-wp').'</b></label>',		// Title
			'pwaforwp_one_signal_support_callback',								// CB
			'pwaforwp_compatibility_setting_section',						// Page slug
			'pwaforwp_compatibility_setting_section'						// Settings Section ID
		);
        add_settings_field(
			'pwaforwp_pushnami_support',							// ID
			'<label for="pwaforwp_settings[pushnami_support_setting]"><b>'.esc_html__('Pushnami', 'pwa-for-wp').'</b></label>',					// Title
			'pwaforwp_pushnami_support_callback',					// CB
			'pwaforwp_compatibility_setting_section',				// Page slug
			'pwaforwp_compatibility_setting_section'				// Settings Section ID
		);
		add_settings_field(
			'pwaforwp_webpushr_support',							// ID
			'<label for="pwaforwp_settings[webpusher_support_setting]"><b>'.esc_html__('Webpushr', 'pwa-for-wp').'</b></label>',					// Title
			'pwaforwp_webpushr_support_callback',					// CB
			'pwaforwp_compatibility_setting_section',				// Page slug
			'pwaforwp_compatibility_setting_section'				// Settings Section ID
		);

		add_settings_field(
			'pwaforwp_wphide_support',							// ID
			'<label for="pwaforwp_settings[wphide_support_setting]"><b>'.esc_html__('WP Hide & Security Enhancer', 'pwa-for-wp').'</b></label>',					// Title
			'pwaforwp_wphide_support_callback',					// CB
			'pwaforwp_compatibility_setting_section',				// Page slug
			'pwaforwp_compatibility_setting_section'				// Settings Section ID
		);
                               
		add_settings_section('pwaforwp_visibility_setting_section', '', '__return_false', 'pwaforwp_visibility_setting_section');
		add_settings_field(
			'pwaforwp_visibility_setting',							// ID
			'',	
			'pwaforwp_visibility_setting_callback',							// CB
			'pwaforwp_visibility_setting_section',						// Page slug
			'pwaforwp_visibility_setting_section'						// Settings Section ID
		);  

        add_settings_section('pwaforwp_precaching_setting_section', '', '__return_false', 'pwaforwp_precaching_setting_section');
        add_settings_field(
            'pwaforwp_precaching_setting',                          // ID
            '', 
            'pwaforwp_precaching_setting_callback',                         // CB
            'pwaforwp_precaching_setting_section',                      // Page slug
            'pwaforwp_precaching_setting_section'                       // Settings Section ID
        );  
		add_settings_section('pwaforwp_urlhandler_setting_section', '', '__return_false', 'pwaforwp_urlhandler_setting_section');
		add_settings_field(
			'pwaforwp_urlhandler_setting',							// ID
			esc_html__('Enter URLs (with similar origin)', 'pwa-for-wp'),	
			'pwaforwp_urlhandler_setting_callback',							// CB
			'pwaforwp_urlhandler_setting_section',						// Page slug
			'pwaforwp_urlhandler_setting_section'						// Settings Section ID
		);  
                
                
                add_settings_section('pwaforwp_push_notification_section', '', '__return_false', 'pwaforwp_push_notification_section');
		// Splash Screen Background Color
		add_settings_field(
			'pwaforwp_push_notification',							// ID
			'',	
			'pwaforwp_push_notification_callback',							// CB
			'pwaforwp_push_notification_section',						// Page slug
			'pwaforwp_push_notification_section'						// Settings Section ID
		);
                
                add_settings_section('pwaforwp_premium_features_section', '', '__return_false', 'pwaforwp_premium_features_section');
		// Splash Screen Background Color
		add_settings_field(
			'pwaforwp_premium_features',							// ID
			'',	
			'pwaforwp_premium_features_callback',							// CB
			'pwaforwp_premium_features_section',						// Page slug
			'pwaforwp_premium_features_section'						// Settings Section ID
		);
                
                
                
		
}

function pwaforwp_sanitize_fields($inputs=array()){
	$fields_type_data = pwaforwp_fields_and_type('type');

	foreach ($inputs as $key => $value) {
		if (isset($fields_type_data[$key])) {
			$fields_type = $fields_type_data[$key];
			if (is_array($value)) {
				if($key == 'shortcut') {
					foreach ($value as $k => $vals) {
						foreach ($vals as $kc => $vc) {
							$value[sanitize_key($k)][sanitize_key($kc)] = sanitize_text_field($vc);
						}
					}
				}else{
					foreach ($value as $k => $val) {
						$value[sanitize_key($k)] = sanitize_text_field($val);
					}
				}
				$inputs[sanitize_key($key)] = $value;
			}else{
				switch ($fields_type) {
					case 'text':
						$inputs[sanitize_key($key)] = sanitize_text_field($value);
						break;
					case 'textarea':
						$inputs[sanitize_key($key)] = sanitize_textarea_field($value);
						break;
					case 'checkbox':
						$inputs[sanitize_key($key)] = filter_var($value, FILTER_SANITIZE_NUMBER_INT);
						break;
					
					default:
						$inputs[sanitize_key($key)] = sanitize_text_field($value);
						break;
				}
				
			}
		}else{
			if (is_array($value)) {
				foreach ($value as $k => $val) {
					$value[sanitize_key($k)] = sanitize_text_field($val);
				}		
				$inputs[sanitize_key($key)] = $value;
			}else{
				$inputs[sanitize_key($key)] = sanitize_text_field($value);
			}
		}
	}
	return $inputs;
	
}

function pwaforwp_addon_html(){
    
    $add_on_list = pwaforwp_list_addons();
    $pluginHtml = '';
	if(is_array($add_on_list) && !empty($add_on_list)){
    foreach ($add_on_list as $key => $plugin) {
    	$ctafp_active_text = '';
    	if(is_plugin_active($plugin['p-slug'])){                                           
	       $ctafp_active_text =  pwaforwp_get_license_section_html($plugin['p-short-prefix']);                                         
	    }else{                                            
	       $ctafp_active_text .= '<label class="pwaforwp-sts-txt-inactive">'.esc_html__('Status', 'pwa-for-wp').' :<span class="pwaforwp_addon_uninstalled">'.esc_html__('Inactive', 'pwa-for-wp').'</span></label>'; 
	       if(!class_exists('PWAFORWPPROExtensionManager')){
		       $ctafp_active_text .= '<a target="_blank" href="'.esc_url($plugin['p-url']).'"><span class="pwaforwp-d-btn">'.esc_html__('Download', 'pwa-for-wp').'</span></a>';
		   }
	    }

	    $pluginHtml .= '
                <li>
                <div class="pwafowp-feature-ext">

				<div class="pwaforwp-features-ele">
					<div class="pwaforwp-ele-ic" style="background: '.esc_attr($plugin['p-background-color']).'">
                        <img src="'.esc_url($plugin['p-icon-img']).'">
					</div>
					<div class="pwaforwp-ele-tlt">
						<h3>'.esc_html($plugin['p-title']).'</h3>
						<p>'.esc_html($plugin['p-desc']).'</p>
					</div>
				</div>
				<div class="pwaforwp-sts-btn">                                    
                                   '.$ctafp_active_text.'                                                                           										
				</div>  
                </div>
                </li>';
    }
}

    
    $ext_html = $pluginHtml;

    return $ext_html;
    
}
function pwaforwp_list_addons(){
	$add_on_list = array(
         'ctafp'  => array(
                    'p-slug' => 'call-to-action-for-pwa/call-to-action-for-pwa.php',
                    'p-name' => 'Call To Action',
                    'p-short-prefix'=> 'CTAFP',
                    'p-smallcaps-prefix'=> 'ctafp',
                    'p-title' => 'Call to Action for PWA',
                    'p-url'	 => 'https://pwa-for-wp.com/extensions/call-to-action-for-pwa/',
                    'p-icon-img' => PWAFORWP_PLUGIN_URL.'images/call-to-action.png',
                    'p-background-color'=> '#333333',
                    'p-desc' => esc_html__('Call to Action extension makes it easy for your users to add the website to the home screen', 'pwa-for-wp'),
                    'p-tab'	 => true
         ),
         'lilfp'  => array(
                    'p-slug' => 'loading-icon-library-for-pwa/loading-icon-library-for-pwa.php',
                    'p-name' => 'Loading Icon Library for PWA',
                    'p-short-prefix'=> 'LILFP',
                    'p-smallcaps-prefix'=> 'lilfp',
                    'p-title' => 'Loading Icon Library for PWA',
                    'p-url'	 => 'https://pwa-for-wp.com/extensions/loading-icon-library-for-pwa/',
                    'p-icon-img' => PWAFORWP_PLUGIN_URL.'images/loading-icon-library.png',
                    'p-background-color'=> '#2f696d',
                    'p-desc' => esc_html__('Loading Icon Library extension multiple icons for PWA app', 'pwa-for-wp'),
                    'p-tab'	 => false
         ),
         'dafp'  => array(
                    'p-slug' => 'data-analytics-for-pwa/data-analytics-for-pwa.php',
                    'p-name' => 'Data Analytics for PWA',
                    'p-short-prefix'=> 'DAFP',
                    'p-smallcaps-prefix'=> 'dafp',
                    'p-title' => 'Data Analytics for PWA',
                    'p-url'	 => 'https://pwa-for-wp.com/extensions/data-analytics-for-pwa/',
                    'p-icon-img' => PWAFORWP_PLUGIN_URL.'images/data-analytics-for-pwa.png',
                    'p-background-color'=> '#84dbff',
                    'p-desc' => esc_html__('Data Analytics for PWA installation growth and traffic analysis', 'pwa-for-wp'),
                    'p-tab'	 => false
         ),
         'ptrfp'  => array(
                    'p-slug' => 'pull-to-refresh-for-pwa/pull-to-refresh-for-pwa.php',
                    'p-name' => 'Pull to Refresh for PWA',
                    'p-short-prefix'=> 'PTRFP',
                    'p-smallcaps-prefix'=> 'ptrfp',
                    'p-title' => 'Pull to Refresh for PWA',
                    'p-url'	 => 'https://pwa-for-wp.com/extensions/pull-to-refresh-for-pwa/',
                    'p-icon-img' => PWAFORWP_PLUGIN_URL.'images/pull-to-refresh-for-pwa.png',
                    'p-background-color'=> '#336363',
                    'p-desc' => esc_html__('Pull to Refresh for PWA extension help users to refresh the page inside PWA app', 'pwa-for-wp'),
                    'p-tab'	 => false
         ),
         'spbfp'  => array(
                    'p-slug' => 'scroll-progress-bar-for-pwa/scroll-progress-bar-for-pwa.php',
                    'p-name' => 'Scroll Progress Bar for PWA',
                    'p-short-prefix'=> 'SPBFP',
                    'p-smallcaps-prefix'=> 'spbfp',
                    'p-title' => 'Scroll Progress Bar for PWA',
                    'p-url'	 => 'https://pwa-for-wp.com/extensions/scroll-progress-bar-for-pwa/',
                    'p-icon-img' => PWAFORWP_PLUGIN_URL.'images/scroll-progress-bar-for-pwa.png',
                    'p-background-color'=> '#3e3e3e',
                    'p-desc' => esc_html__('Scroll Progress Bar for PWA extension indicator to display the current reading position', 'pwa-for-wp'),
                    'p-tab'	 => false
         ),
         'ptafp'  => array(
                    'p-slug' => 'pwa-to-apk-plugin/pwa-to-apk-plugin.php',
                    'p-name' => 'PWA to APK Plugin',
                    'p-short-prefix'=> 'PTAFP',
                    'p-smallcaps-prefix'=> 'ptafp',
                    'p-title' => 'PWA to APK Plugin',
                    'p-url'	 => 'https://pwa-for-wp.com/extensions/pwa-to-apk-plugin/',
                    'p-icon-img' => PWAFORWP_PLUGIN_URL.'images/pwa-to-apk-plugin.png',
                    'p-background-color'=> '#afa173',
                    'p-desc' => esc_html__('PWA to APK Plugin for PWA extension to create apk for your website', 'pwa-for-wp'),
                    'p-tab'	 => false
         ),
         'ofpwa'  => array(
                    'p-slug' => 'offline-forms-for-pwa-for-wp/offline-forms-for-pwa-for-wp.php',
                    'p-name' => 'Offline Forms for PWA for WP',
                    'p-short-prefix'=> 'OFPWA',
                    'p-smallcaps-prefix'=> 'ofpwa',
                    'p-title' => 'Offline Forms for PWA for WP',
                    'p-url'	 => 'https://pwa-for-wp.com/extensions/offline-forms-for-pwa-for-wp/',
                    'p-icon-img' => PWAFORWP_PLUGIN_URL.'images/offline-forms-for-pwa-for-wp.png',
                    'p-background-color'=> '#acb1b5',
                    'p-desc' => esc_html__('Offline Forms for PWA extension to store forms for your website', 'pwa-for-wp'),
                    'p-tab'	 => false
         ),
         'bnpwa'  => array(
                    'p-slug' => 'buddypress-for-pwaforwp/buddypress-for-pwaforwp.php',
                    'p-name' => 'BuddyPress for PWAforWP',
                    'p-short-prefix'=> 'BNPWA',
                    'p-smallcaps-prefix'=> 'bnpwa',
                    'p-title' => 'Buddypress for PWA for WP',
                    'p-url'	 => 'https://pwa-for-wp.com/extensions/buddypress-for-pwaforwp/',
                    'p-icon-img' => PWAFORWP_PLUGIN_URL.'images/buddypress-for-pwaforwp.png',
                    'p-background-color'=> '#d94e27',
                    'p-desc' => esc_html__('Buddypress extension to send push notification while core notification will work ex: A member mentions you in an update / A member replies to an update or comments your post', 'pwa-for-wp'),
                    'p-tab'	 => false
         ),
         'qafp'  => array(
                    'p-slug' => 'quick-action-for-pwa/quick-action-for-pwa.php',
                    'p-name' => 'Quick Action for PWA',
                    'p-short-prefix'=> 'QAFP',
                    'p-smallcaps-prefix'=> 'qafp',
                    'p-title' => 'Quick Action for PWA',
                    'p-url'	 => 'https://pwa-for-wp.com/extensions/quick-action-for-pwa/',
                    'p-icon-img' => PWAFORWP_PLUGIN_URL.'images/quick-action-for-pwa.png',
                    'p-background-color'=> '#bbcff0',
                    'p-desc' => esc_html__('Quick action help users give shortcut link, common or recommended pages with in your web app', 'pwa-for-wp'),
                    'p-tab'	 => false
         ),
         'nbfp'  => array(
                    'p-slug' => 'navigation-bar-for-pwa/navigation-bar-for-pwa.php',
                    'p-name' => 'Navigation Bar for PWA',
                    'p-short-prefix'=> 'NBFP',
                    'p-smallcaps-prefix'=> 'nbfp',
                    'p-title' => 'Navigation Bar for PWA',
                    'p-url'	 => 'https://pwa-for-wp.com/extensions/navigation-bar-for-pwa/',
                    'p-icon-img' => PWAFORWP_PLUGIN_URL.'images/navigation-bar-for-pwa.png',
                    'p-background-color'=> '#3872a2',
                    'p-desc' => esc_html__('Top-level pages that need to be accessible from anywhere in the app', 'pwa-for-wp'),
                    'p-tab'	 => false
         ),
         'mcfp'  => array(
                    'p-slug' => 'multilingual-compatibility-for-pwa/multilingual-compatibility-for-pwa.php',
                    'p-name' => 'Multilingual Compatibility for PWA',
                    'p-short-prefix'=> 'MCFP',
                    'p-smallcaps-prefix'=> 'ncfp',
                    'p-title' => 'Multilingual Compatibility for PWA',
                    'p-url'	 => 'https://pwa-for-wp.com/extensions/multilingual-compatibility-for-pwa/',
                    'p-icon-img' => PWAFORWP_PLUGIN_URL.'images/multilingual-compatibility-for-pwa.png',
                    'p-background-color'=> '#cddae2',
                    'p-desc' => esc_html__('Add multilingual support for PWA APP', 'pwa-for-wp'),
                    'p-tab'	 => false
         ),
         'ropi'  => array(
                    'p-slug' => 'rewards-on-pwa-install/rewards-on-pwa-install.php',
                    'p-name' => 'Rewards on PWA install',
                    'p-short-prefix'=> 'ROPI',
                    'p-smallcaps-prefix'=> 'ropi',
                    'p-title' => 'Rewards on PWA install',
                    'p-url'	 => 'https://pwa-for-wp.com/extensions/rewards-on-pwa-install/',
                    'p-icon-img' => PWAFORWP_PLUGIN_URL.'images/rewards-on-pwa-install.png',
                    'p-background-color'=> '#cddae2',
                    'p-desc' => esc_html__('Rewards to the most loyal base of customers', 'pwa-for-wp'),
                    'p-tab'	 => false
         ),
		 
        'qrcode'  => array(
				'p-slug' => 'qr-code-for-pwa/qr-code-for-pwa.php',
				'p-name' => 'QR Code for PWA',
				'p-short-prefix'=> 'QRCODE',
				'p-smallcaps-prefix'=> 'qrcode',
				'p-title' => 'QR Code for PWA',
				'p-url'	 => 'https://pwa-for-wp.com/extensions/qr-code-for-pwa/',
				'p-icon-img' => PWAFORWP_PLUGIN_URL.'images/qr-code-for-pwa.png',
				'p-background-color'=> '#acb1b5',
				'p-desc' => esc_html__('QR Code for PWA extension to user can install app using QR Code ', 'pwa-for-wp'),
				'p-tab'	 => false
			)
			
     );
	return $add_on_list;
}
function pwaforwp_addons_is_active(){
	$add_on_list = pwaforwp_list_addons();
	$add_on_list['pwa_pro'] = array('p-slug' => 'pwa-pro-extension-manager/pwa-pro-extension-manager.php');
	$ext_is_there = false;
	foreach($add_on_list as $key => $on){
         if(is_plugin_active($on['p-slug'])){
           $ext_is_there = true;
           break;
         }
     }
	return $ext_is_there;
}

function pwaforwp_premium_features_callback(){
    
    $add_on_list = pwaforwp_list_addons();
    
    $ext_is_there = pwaforwp_addons_is_active();
          
     if($ext_is_there){
         
         $tabs      = '';
         $container = '';
         $tabs = apply_filters("pwaforwp_premium_features_tabs", $tabs);
         $container = apply_filters("pwaforwp_premium_features_tabs", $container);
         
        ?> 
        <div class="pwaforwp-subheading-wrap">

	       <div id="pwaforwp-ext-container-for-all" class="pwaforwp-subheading">
	            <?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- all required data already escapped.
				echo $container; ?>       
	           <div class="pwaforwp-ext-container selected" id="pwaforwp-addon">
	           	<div class="pwaforwp-ext-wrap">
	    <ul class="pwaforwp-features-blocks">
	                <?php 
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- all required data already escapped.
					echo pwaforwp_addon_html(); ?>
	            </ul>
	           </div>
	           </div>
	           
	       </div>
	   </div>
                                
        <?php 
         
     }else{
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- all required data already escapped.
         echo ' <div class="pwaforwp-ext-wrap" style="width:100%">
        <ul class="pwaforwp-features-blocks">'.
			  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- all required data already escapped
			pwaforwp_addon_html().'
            </ul>
            </div>';
         
     }
             
}

function pwaforwp_caching_strategies_setting_callback(){
	// Get Settings
	$settings = pwaforwp_defaultSettings(); 
	$arrayOPT = array(
                    'staleWhileRevalidate'  => 'Stale While Revalidate',
                    'networkFirst'          => 'Network First',
                    'cacheFirst'            => 'Cache First',
                    'networkOnly'           => 'Network Only'
                );

	?>
	<tr>
		<td><label><b><?php echo esc_html__('Default caching strategy', 'pwa-for-wp'); ?></b></label></td>
		<td><select name="pwaforwp_settings[default_caching]">
			<?php if(is_array($arrayOPT) && !empty($arrayOPT)){
				foreach ($arrayOPT as $key => $opval) {
					$sel = "";
					if($settings['default_caching']==$key){$sel = "selected"; }
					echo '<option value="'.esc_attr($key).'" '.esc_attr($sel).'>'.esc_html($opval).'</option>';
				}
			}
			 ?>

		</select>
		<br/>
		<label style="padding-top: 5px;">
		<input type="checkbox" name="pwaforwp_settings[change_default_on_login]" value="1" <?php if( isset($settings['change_default_on_login']) && $settings['change_default_on_login']==1 ){ echo 'checked'; }?>><p>
		<?php echo esc_html__('If you have a login for normal users (it help users to get updates content)', 'pwa-for-wp'); ?>
		</p></label>
		</td>
	</tr>
	<tr>
		<td><label><b><?php echo esc_html__('Caching strategy for CSS and JS Files', 'pwa-for-wp'); ?></b></label></td>
		<td><select name="pwaforwp_settings[default_caching_js_css]">
			<?php if(is_array($arrayOPT) && !empty($arrayOPT)){
				foreach ($arrayOPT as $key => $opval) {
					$sel = "";
					if($settings['default_caching_js_css']==$key){$sel = "selected"; }
					echo '<option value="'.esc_attr($key).'" '.esc_attr($sel).'>'.esc_html($opval).'</option>';
				}
			}
			 ?>
		</select></td>
	</tr>
	<tr>
		<td><label><b><?php echo esc_html__('Caching strategy for images', 'pwa-for-wp'); ?></b></label></td>
		<td><select name="pwaforwp_settings[default_caching_images]">
			<?php if(is_array($arrayOPT) && !empty($arrayOPT)){
				foreach ($arrayOPT as $key => $opval) {
					$sel = "";
					if($settings['default_caching_images']==$key){$sel = "selected"; }
					echo '<option value="'.esc_attr($key).'" '.esc_attr($sel).'>'.esc_html($opval).'</option>';
				}
			}
			 ?>
		</select></td>
	</tr>
	<tr>
		<td><label><b><?php echo esc_html__('Caching strategy for fonts', 'pwa-for-wp'); ?></b></label></td>
		<td><select name="pwaforwp_settings[default_caching_fonts]">
			<?php if(is_array($arrayOPT) && !empty($arrayOPT)){
				foreach ($arrayOPT as $key => $opval) {
					$sel = "";
					if($settings['default_caching_fonts']==$key){$sel = "selected"; }
					echo '<option value="'.esc_attr($key).'" '.esc_attr($sel).'>'.esc_html($opval).'</option>';
				}
			}
			 ?>
		</select></td>
	</tr>
	<?php
}

function pwaforwp_cache_time_setting_callback(){
	// Get Settings
	$settings = pwaforwp_defaultSettings(); 
	?>
	<p><?php echo esc_html__('Set max cache time for Html Default:', 'pwa-for-wp'); ?> <code>3600</code> <?php echo esc_html__('in seconds;', 'pwa-for-wp'); ?> <?php echo esc_html__('You need to enter time in seconds', 'pwa-for-wp'); ?></p>
        <input type="text" name="pwaforwp_settings[cached_timer][html]" id="pwaforwp_settings[cached_timer][html]" class=""  value="<?php echo (isset( $settings['cached_timer'] )? esc_attr($settings['cached_timer']['html']) : '3600'); ?>">
	<p><?php echo esc_html__('Set max cache time for JS, CSS, JSON Default:', 'pwa-for-wp'); ?> <code>86400</code> <?php echo esc_html__('in seconds;', 'pwa-for-wp'); ?> <?php echo esc_html__('You need to enter time in seconds', 'pwa-for-wp'); ?></p>
        <input type="text" name="pwaforwp_settings[cached_timer][css]" id="pwaforwp_settings[cached_timer][css]" class=""  value="<?php echo (isset( $settings['cached_timer'] )? esc_attr($settings['cached_timer']['css']) : '86400'); ?>">
	<?php
}

function pwaforwp_avoid_default_banner_setting_callback(){
	// Get Settings
	$settings = pwaforwp_defaultSettings(); 
	?>
	<input type="checkbox" name="pwaforwp_settings[avoid_default_banner]" id="pwaforwp_settings[avoid_default_banner]" class=""  <?php echo (isset( $settings['avoid_default_banner'] ) && ($settings['avoid_default_banner']=='true' || $settings['avoid_default_banner']=='1')? esc_attr('checked') : ''); ?> data-uncheck-val="0" value="true">
	<p><?php echo esc_html__('Enable(check) it when you don\'t want to load default PWA Banner','pwa-for-wp'); ?></p>
	<?php
}

function pwaforwp_avoid_pwa_loggedin_setting_callback(){
	// Get Settings
	$settings = pwaforwp_defaultSettings(); 
	?>
	<input type="checkbox" name="pwaforwp_settings[avoid_loggedin_users]" id="pwaforwp_settings[avoid_loggedin_users]" class=""  <?php echo (isset( $settings['avoid_loggedin_users'] ) && ($settings['avoid_loggedin_users']=='true' || $settings['avoid_loggedin_users']=='1')? esc_attr('checked') : ''); ?> data-uncheck-val="0" value="1">
	<p><?php echo esc_html__('(check) it, if you want disable PWA for loggedin users','pwa-for-wp'); ?></p>
	<?php
}

function pwaforwp_serve_cache_method_setting_callback(){
	// Get Settings
	$settings = pwaforwp_defaultSettings(); 
	?>
	<input type="checkbox" name="pwaforwp_settings[serve_js_cache_menthod]" id="pwaforwp_settings[serve_js_cache_menthod]" class=""  <?php echo (isset( $settings['serve_js_cache_menthod'] ) && $settings['serve_js_cache_menthod']=='true'? esc_attr('checked') : ''); ?> data-uncheck-val="0" value="true">
	<p><?php echo esc_html__('Enable(check) it when PWA with OneSignal or root permission functionality not working because of Cache','pwa-for-wp'); ?></p>
	<?php
}

function pwaforwp_reset_cookies_method_setting_callback(){
	// Get Settings
	$settings = pwaforwp_defaultSettings(); 
	?>
	<input type="checkbox" name="pwaforwp_settings[reset_cookies]" id="pwaforwp_settings[reset_cookies]" class=""  <?php echo (isset( $settings['reset_cookies'] ) && $settings['reset_cookies']=='1'? esc_attr('checked') : ''); ?> data-uncheck-val="0" value="1">
	<p><?php echo esc_html__('Check this to delete cookies','pwa-for-wp'); ?></p>
	<?php
}
function pwaforwp_role_based_access_setting_callback(){
	if( function_exists('is_super_admin') &&  is_super_admin() ){
		$settings = pwaforwp_defaultSettings(); 
		?>
			<select id="pwaforwp_role_based_access" class="regular-text" name="pwaforwp_settings[pwaforwp_role_based_access][]" multiple="multiple">
				<?php
					foreach (pwaforwp_get_user_roles() as $key => $opval) {
						$selected = "";
						if (isset($settings['pwaforwp_role_based_access']) && in_array($key,$settings['pwaforwp_role_based_access'])) {
							$selected = "selected";
						}
						?>
						
						<option value="<?php echo esc_attr($key);?>" <?php echo esc_html($selected);?>><?php echo esc_html($opval); ?></option>
					<?php }
				?>
                </select><br/><p>
				<?php
				echo esc_html__('Choose the users whom you want to allow full access of this plugin','pwa-for-wp');
				?>
				</p>
				<?php

		
		
	} 
}
function pwaforwp_disallow_data_tracking_setting_callback(){
	// Get Settings
	$settings = pwaforwp_defaultSettings(); 
	$allow_tracking = get_option( 'wisdom_allow_tracking' );
	$plugin = basename( PWAFORWP_PLUGIN_FILE, '.php' );

	$checked = "";$tracker_url = '';
	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
	$live_url = '//' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	if(isset($allow_tracking[$plugin])){
		$checked = "checked";
		$tracker_url = $url_no = add_query_arg( array(
					'plugin' 		=> $plugin,
					'plugin_action'	=> 'no',
				), $live_url);
	}else{
		$tracker_url = $yes_args = add_query_arg(array(
					'plugin' 		=> $plugin,
					'plugin_action'	=> 'yes'
				), $live_url);
	}
	?>
	<input type="checkbox" <?php echo esc_attr($checked); ?> onclick="window.location = '<?php echo esc_js($tracker_url); ?>'">
	<p><?php echo esc_html__('We guarantee no sensitive data is collected', 'pwa-for-wp'); ?>. <a target="_blank" href="https://pwa-for-wp.com/docs/article/usage-data-tracking/" target="_blank"><?php echo esc_html__('Learn more', 'pwa-for-wp'); ?></a>.</p>
	<?php
}

function pwaforwp_url_exclude_from_cache_list_callback(){
	// Get Settings
	$settings = pwaforwp_defaultSettings(); 
	?>
        <label><textarea placeholder="<?php esc_attr('https://example.com/admin.php?page=newpage, https://example.com/admin.php?page=newpage2') ?>"  rows="4" cols="70" id="pwaforwp_settings[excluded_urls]" name="pwaforwp_settings[excluded_urls]"><?php echo (isset($settings['excluded_urls']) ? esc_attr($settings['excluded_urls']): ''); ?></textarea></label>
        <p><?php echo esc_html__('Note: Put in comma separated, do not add enter in urls', 'pwa-for-wp'); ?></p>
	<p><?php echo esc_html__('Put the list of urls which you do not want to cache by service worker', 'pwa-for-wp'); ?></p>	
	
	<?php
}

function pwaforwp_urlhandler_setting_callback(){
	$settings = pwaforwp_defaultSettings(); 
	echo "<textarea name='pwaforwp_settings[urlhandler]' rows='10' cols='80' placeholder='".esc_attr('https://music.example.com\nhttps://*.music.example.com\nhttps://chat.example.com\nhttps://*.music.example.com')."'>". (isset($settings['urlhandler'])? esc_attr($settings['urlhandler']): '') ."</textarea>";
	?><p><?php echo esc_html__('Note: Put one url in single line', 'pwa-for-wp'); ?></p>
	<br>
	<?php
		if(isset($settings['urlhandler']) && !empty($settings['urlhandler'])){
			$urls = explode("\n", $settings['urlhandler']);
            if(is_array($urls)){
                foreach($urls as $url){
                	$fileData[] = array(
	                			"manifest"=> $url,
						        "details"=> array(
						        	"paths"=> array("/*"),
						        	"exclude_paths"=> array("/wp-admin/*"),
						        )
                			);
                }
                $data = array("web_apps"=>$fileData);
                echo "<p>".esc_html__("Create \"web-app-origin-association\" file for the apple and android.  Need to place the web-app-origin-association file in the /.well-known/ folder at the root of the app. \n example URL https://example.com/.well-known/web-app-origin-association", "pwa-for-wp")." <a href='https://pwa-for-wp.com/docs/article/how-to-use-urlhandler-for-pwa/'>".esc_html__('Learn more', 'pwa-for-wp')."</a></p>";
                echo "<textarea cols='100' rows='20' readonly>".wp_json_encode($data, JSON_PRETTY_PRINT)."</textarea>";
            }
                
		}
	?>
	<?php
}

function pwaforwp_precaching_setting_callback(){
	
	$settings = pwaforwp_defaultSettings(); 
        
        $arrayOPT = array(                    
                        'automatic'=>'Automatic',
                        'manual'=>'Manual',            
                     );
	?>
			
		<tr>
                    <th><strong><?php echo esc_html__('Automatic', 'pwa-for-wp'); ?></strong>
                    	<span class="pwafw-tooltip"><i class="dashicons dashicons-editor-help"></i> 
		                    <span class="pwafw-help-subtitle"><a href="https://pwa-for-wp.com/docs/article/setting-up-precaching-in-pwa/"><?php echo esc_html__('For details click here', 'pwa-for-wp'); ?></a></span>
		                </span>
	            	</th>
                        <td>
                          <input type="checkbox" name="pwaforwp_settings[precaching_automatic]" id="pwaforwp_settings_precaching_automatic" class="" <?php echo (isset( $settings['precaching_automatic'] ) &&  $settings['precaching_automatic'] == 1 ? 'checked="checked"' : ''); ?> data-uncheck-val="0" value="1">   
                        </td>
		</tr>
                <tr>
                <td></td>
                <td> 
                    <table class="pwaforwp-pre-cache-table">
                     <tr>
                         <td>
                          <?php echo esc_html__('Post', 'pwa-for-wp') ?>                             
                         </td>
                         <td>                         
                         <input type="checkbox" name="pwaforwp_settings[precaching_automatic_post]" id="pwaforwp_settings_precaching_automatic_post" class="" <?php echo (isset( $settings['precaching_automatic_post'] ) &&  $settings['precaching_automatic_post'] == 1 ? 'checked="checked"' : ''); ?> data-uncheck-val="0" value="1">     
                         </td>
                         <td>
                         <?php echo esc_html__('Page', 'pwa-for-wp') ?>   
                         </td>
                         <td>
                         <input type="checkbox" name="pwaforwp_settings[precaching_automatic_page]" id="pwaforwp_settings_precaching_automatic_page" class="" <?php echo (isset( $settings['precaching_automatic_page'] ) &&  $settings['precaching_automatic_page'] == 1 ? 'checked="checked"' : ''); ?> data-uncheck-val="0" value="1">         
                         </td>
                         <td>                          
                         <?php echo esc_html__('Custom Post', 'pwa-for-wp') ?>   
                         </td>
                         <td>
                         <input type="checkbox" name="pwaforwp_settings[precaching_automatic_custom_post]" id="pwaforwp_settings_precaching_automatic_custom_post" class="" <?php echo (isset( $settings['precaching_automatic_custom_post'] ) &&  $settings['precaching_automatic_custom_post'] == 1 ? 'checked="checked"' : ''); ?> data-uncheck-val="0" value="1">         
                         </td>                     
                     </tr>
                     
                    </table>
                </td>    
                </tr>
                <tr>
                    <td><strong><?php echo esc_html__('Enter Post Count', 'pwa-for-wp'); ?></strong></td>
                   <td>
                       <input id="pwaforwp_settings_precaching_post_count" name="pwaforwp_settings[precaching_post_count]" value="<?php if(isset($settings['precaching_post_count'])){ echo esc_attr($settings['precaching_post_count']);} ?>" type="number" min="0">   
                   </td>
                </tr>
                <tr>
                    <td><strong><?php echo esc_html__('Manual', 'pwa-for-wp'); ?> </strong>
                    	<span class="pwafw-tooltip"><i class="dashicons dashicons-editor-help"></i> 
		                    <span class="pwafw-help-subtitle"><a href="https://pwa-for-wp.com/docs/article/setting-up-precaching-in-pwa/"><?php echo esc_html__('For details click here','pwa-for-wp'); ?></a></span>
		                </span>
                    </td>
                        <td>
                         <input type="checkbox" name="pwaforwp_settings[precaching_manual]" id="pwaforwp_settings_precaching_manual" class="" <?php echo (isset( $settings['precaching_manual'] ) &&  $settings['precaching_manual'] == 1 ? 'checked="checked"' : ''); ?> data-uncheck-val="0" value="1">    
                        </td>
		</tr>                
                <tr>    
                    <td> <strong> <?php echo esc_html__('Enter Urls To Be Cached', 'pwa-for-wp'); ?> </strong></td>
                   <td>
                       <label><textarea placeholder="<?php esc_attr('https://example.com/2019/06/06/hello-world/, https://example.com/2019/06/06/hello-world-2/')?>"  rows="4" cols="50" id="pwaforwp_settings_precaching_urls" name="pwaforwp_settings[precaching_urls]"><?php if(isset($settings['precaching_urls'])){ echo esc_attr($settings['precaching_urls']);} ?></textarea></label>
                       <p><?php echo esc_html__('Note: Put in comma separated', 'pwa-for-wp'); ?></p>
                       <p><?php echo esc_html__('Put the list of urls which you want to pre cache by service worker', 'pwa-for-wp'); ?></p>
                   </td>
                </tr>
		
	
	<?php
}

function pwaforwp_visibility_setting_callback(){
    
    $settings = pwaforwp_defaultSettings();

    $arrayOPT = array(
                    'post_type'     => 'Post Type',
                    'globally'      => 'Globally',
                    'post'          => 'Post',
                    'post_category' => 'Post Category',
                    'page'          => 'Page',
                    'taxonomy'      => 'Taxonomy Terms',
                    'tags'          => 'Tags',
                    'page_template' => 'Page Template',
                    'user_type'     => 'Logged in User Type'
                );
    
    ?>
        <tr>
            <th colspan="2"><?php echo esc_html__('Which Page Would You Like To Display', 'pwa-for-wp');?></th>
        </tr>

        <tr>
            <th><?php echo esc_html__('Included On', 'pwa-for-wp'); ?> <i class="dashicons dashicons-plus-alt"></i></th> 
        </tr>
        <tr>
            <td colspan="3">
                    
                <div class="visibility-include-target-item-list">
                    <?php $rand = time().wp_rand(000,999);
                    
                    if(!empty( $settings['include_targeting_type']))  {
                        $expo_include_type = explode(',', $settings['include_targeting_type']);
                        $expo_include_data = explode(',', $settings['include_targeting_value']);
                        for ($i=0; $i<count($expo_include_type); $i++) {
                           echo '<span class="pwaforwp-visibility-target-icon-'.esc_attr($rand).'"><input type="hidden" name="include_targeting_type" value="'.esc_attr($expo_include_type[$i]).'">
                                <input type="hidden" name="include_targeting_data" value="'.esc_attr($expo_include_data[$i]).'">';
                            $expo_include_type_test = pwaforwpRemoveExtraValue($expo_include_type[$i]);
                            $expo_include_data_test = pwaforwpRemoveExtraValue($expo_include_data[$i]);
                            echo '<span class="pwaforwp-visibility-target-item"><span class="visibility-include-target-label">'.esc_html($expo_include_type_test.' - '.$expo_include_data_test).'</span>
                            <span class="pwaforwp-visibility-target-icon" data-index="0"><span class="dashicons dashicons-no-alt " aria-hidden="true" onclick="removeIncluded_visibility('.esc_js($rand).')"></span></span></span></span>';
                            $rand++;
                        }
                    }?>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <select id="pwaforwp_settings[visibility_included_post_options]" class="regular-text pwaforwp_visibility_options_select visibility_options_select_include" onchange="get_include_pages()">
                    <option value=""><?php echo esc_html__("Select Visibility Type",'pwa-for-wp');?></option>
                    <?php if(is_array($arrayOPT) && !empty($arrayOPT)){
                        foreach ($arrayOPT as $key => $opval) {?>
                            <option value="<?php echo esc_attr($key);?>"><?php echo esc_html($opval); ?></option>
                        <?php }
                    } ?>
                </select>
                <div class="include_error">&nbsp;</div>
            </td>
            <td class="visibility_options_select">
                <select  id="pwaforwp_settings[visibility_included_options]" class="regular-text pwaforwp_visibility_options_select visibility_include_select_type pwa_for_wp-select2">
                    <option value=""><?php echo esc_html__("Select Visibility Type",'pwa-for-wp');?></option>                    
                </select>
                <div class="include_type_error">&nbsp;</div>
            </td>
            <td class="include-btn-box"><a class="pwaforwp-include-btn button-primary" onclick="add_included_condition()"><?php echo esc_html__('ADD', 'pwa-for-wp'); ?></a></td>
        </tr> 

        <!-- Excluded -->
        <tr>
            <th><?php echo esc_html__('Excluded On', 'pwa-for-wp'); ?> <i class="dashicons dashicons-plus-alt"></i></th> 
        </tr>

        <tr>
            <td colspan="3">
                    
                <div class="visibility-exclude-target-item-list">
                    <?php $rand = time().wp_rand(000,999);
                    if(!empty( $settings['exclude_targeting_type']))  {
                        $expo_exclude_type = explode(',', $settings['exclude_targeting_type']);
                        $expo_exclude_data = explode(',', $settings['exclude_targeting_value']);
                       for ($i=0; $i < count($expo_exclude_type); $i++) {
                           echo '<span class="pwaforwp-visibility-target-icon-'.esc_attr($rand).'"><input type="hidden" name="exclude_targeting_type" value="'.esc_attr($expo_exclude_type[$i]).'">
                                <input type="hidden" name="exclude_targeting_data" value="'.esc_attr($expo_exclude_data[$i]).'">';
                           $expo_exclude_type_test = pwaforwpRemoveExtraValue($expo_exclude_type[$i]);
                           $expo_exclude_data_test = pwaforwpRemoveExtraValue($expo_exclude_data[$i]);

                           echo '<span class="pwaforwp-visibility-target-item"><span class="visibility-include-target-label">'.esc_html($expo_exclude_type_test.' - '.$expo_exclude_data_test).'</span>
                            <span class="pwaforwp-visibility-target-icon" data-index="0"><span class="dashicons dashicons-no-alt " aria-hidden="true" onclick="removeIncluded_visibility('.esc_attr($rand).')"></span></span></span></span>';
                            $rand++;
                        }
                    }?>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <select  id="pwaforwp_settings[visibility_excluded_post_options]" class="regular-text pwaforwp_visibility_options_select visibility_options_select_exclude" onchange="get_exclude_pages()">
                    <option value=""><?php echo esc_html__("Select Visibility Type",'pwa-for-wp');?></option>

                    <?php if(is_array($arrayOPT) && !empty($arrayOPT)){
                        foreach ($arrayOPT as $key => $opval) {?>
                            <option value="<?php echo esc_attr($key);?>"><?php echo esc_html($opval); ?></option> 
                            
                        <?php }
                    } ?>
                                     
                </select>
                 <div class="exclude_error">&nbsp;</div>
            </td>

            <td class="visibility_options_select">
                <select  class="regular-text pwaforwp_visibility_options_select visibility_exclude_select_type pwa_for_wp-select2_exclude">
                    <option value=""><?php echo esc_html__("Select Visibility Type",'pwa-for-wp');?></option>
                    
                    
                </select>
                 <div class="exclude_type_error">&nbsp;</div>
            </td>
            <td class="include-btn-box"><a class="pwaforwp-exclude-btn button-primary" onclick="add_exclude_condition()"><?php echo esc_html__('ADD', 'pwa-for-wp'); ?></a></td>
        </tr>   
    <?php
}

function pwaforwp_utm_setting_callback(){
	// Get Settings
	$settings = pwaforwp_defaultSettings(); 
	$style    = "none";
        
	if(isset($settings['utm_setting']) && $settings['utm_setting']){
		$style="block";
	}
        
	$utm_source  = $utm_medium = $utm_term = $utm_content = $utm_campaign = ''; 
	$utm_url     = pwaforwp_home_url();
	$utm_url_amp = (function_exists('ampforwp_url_controller')? ampforwp_url_controller(pwaforwp_home_url()) : pwaforwp_home_url()."amp");
        
	if(isset($settings['utm_details'])){
            
		$utm_source     = $settings['utm_details']['utm_source'];
		$utm_medium     = $settings['utm_details']['utm_medium'];
                $utm_campaign   = $settings['utm_details']['utm_campaign'];
		$utm_term       = $settings['utm_details']['utm_term'];
		$utm_content    = $settings['utm_details']['utm_content'];
                
		$queryArg['utm_source']   = $utm_source;
		$queryArg['utm_medium']   = $utm_medium;
                $queryArg['utm_campaign'] = $utm_campaign;
		$queryArg['utm_term']     = $utm_term;
		$queryArg['utm_content']  = $utm_content;
                
		$queryArg    = array_filter($queryArg);
		$utm_url     = $utm_url."?".http_build_query($queryArg);
		$utm_url_amp = $utm_url_amp."?".http_build_query($queryArg);

	}
        
	$queryArg = 'utm_source=&utm_medium=&utm_medium=&utm_term=&utm_content'
                
	?>
                
	<label><input type="checkbox" name="pwaforwp_settings[utm_setting]" id="pwaforwp_settings_utm_setting" class="" <?php echo (isset( $settings['utm_setting'] ) &&  $settings['utm_setting'] == 1 ? 'checked="checked"' : ''); ?> data-uncheck-val="0" value="1"><?php echo esc_html__('Enable UTM Tracking', 'pwa-for-wp'); ?></label>
	<p> <?php echo esc_html__('To identify users are coming from your App', 'pwa-for-wp'); ?></p>
	<table class="form-table">
		<tr class="pwawp_utm_values_class" style="display:<?php echo esc_attr($style); ?>;">
			<th><?php echo esc_html__('UTM Source', 'pwa-for-wp'); ?></th>
			<td><input type="text" name="pwaforwp_settings[utm_details][utm_source]" value="<?php echo esc_attr($utm_source); ?>" data-val="<?php echo esc_attr($utm_source); ?>"/></td>
		</tr>
		<tr class="pwawp_utm_values_class" style="display:<?php echo esc_attr($style); ?>;">
			<th><?php echo esc_html__('UTM Medium', 'pwa-for-wp'); ?></th>
			<td><input type="text" name="pwaforwp_settings[utm_details][utm_medium]" value="<?php echo esc_attr($utm_medium); ?>" data-val="<?php echo esc_attr($utm_medium); ?>"/></td>
		</tr>
                <tr class="pwawp_utm_values_class" style="display:<?php echo esc_attr($style); ?>;">
			<th><?php echo esc_html__('UTM Campaign', 'pwa-for-wp'); ?></th>
			<td><input type="text" name="pwaforwp_settings[utm_details][utm_campaign]" value="<?php echo esc_attr($utm_campaign); ?>" data-val="<?php echo esc_attr($utm_campaign); ?>"/></td>
		</tr>
		<tr class="pwawp_utm_values_class" style="display:<?php echo esc_attr($style); ?>;">
			<th><?php echo esc_html__('UTM Term', 'pwa-for-wp'); ?></th>
			<td><input type="text" name="pwaforwp_settings[utm_details][utm_term]" value="<?php echo esc_attr($utm_term); ?>" data-val="<?php echo esc_attr($utm_term); ?>"/></td>
		</tr>
		<tr class="pwawp_utm_values_class" style="display:<?php echo esc_attr($style); ?>;">
			<th><?php echo esc_html__('UTM Content', 'pwa-for-wp'); ?></th>
			<td><input type="text" name="pwaforwp_settings[utm_details][utm_content]" value="<?php echo esc_attr($utm_content); ?>" data-val="<?php echo esc_attr($utm_content); ?>"/></td>
		</tr>
		<tr class="pwawp_utm_values_class expectedValues" style="display:<?php echo esc_attr($style); ?>;">
			<th><?php echo esc_html__('UTM Non-amp Url', 'pwa-for-wp'); ?></th>
			<td><code><?php echo esc_url($utm_url); ?></code></td>
		</tr>
		<tr class="pwawp_utm_values_class expectedValues" style="display:<?php echo esc_attr($style); ?>;">
			<th><?php echo esc_html__('UTM amp Url', 'pwa-for-wp'); ?></th>
			<td><code><?php echo esc_url($utm_url_amp); ?></code></td>
		</tr>
	</table>
	<input type="hidden" name="pwaforwp_settings[utm_details][pwa_utm_change_track]" id="pwa-utm_change_track" value="0">
	<?php
}

function pwaforwp_offline_google_setting_callback(){
	// Get Settings
	$settings = pwaforwp_defaultSettings(); 
	?>
        
	<input type="checkbox" name="pwaforwp_settings[offline_google_setting]" id="pwaforwp_settings[offline_google_setting]" class="" <?php echo (isset( $settings['offline_google_setting'] ) &&  $settings['offline_google_setting'] == 1 ? 'checked="checked"' : ''); ?> data-uncheck-val="0" value="1">
	<p><?php echo esc_html__('Offline analytics is a module that will use background sync to ensure that requests to Google Analytics are made regardless of the current network condition', 'pwa-for-wp'); ?></p>
	<?php
}
function pwaforwp_offline_message_setting_callback(){
	// Get Settings
	$settings = pwaforwp_defaultSettings();
	$offline_message_checked = 'checked="checked';
	if(!isset( $settings['offline_message_setting'] ) || $settings['offline_message_setting'] == 0){
		$offline_message_checked = '';
	}
	?>
        
	<input type="checkbox" name="pwaforwp_settings[offline_message_setting]" id="pwaforwp_settings[offline_message_setting]" class="" <?php echo esc_attr($offline_message_checked); ?> data-uncheck-val="0" value="1">
	<p><?php echo esc_html__('To check whether user is offline and display message You are offline', 'pwa-for-wp'); ?></p>
	<?php
}
function pwaforwp_scrollbar_setting_callback(){
	// Get Settings
	$settings = pwaforwp_defaultSettings();
	$scrollbar_checked = 'checked="checked';
	if(!isset( $settings['scrollbar_setting'] ) || $settings['scrollbar_setting'] == 0){
		$scrollbar_checked = '';
	}
	?>
        
	<input type="checkbox" name="pwaforwp_settings[scrollbar_setting]" id="pwaforwp_settings[scrollbar_setting]" class="" <?php echo esc_attr($scrollbar_checked); ?> data-uncheck-val="0" value="1">
	<p><?php echo esc_html__('To hide scrollbar in pwa', 'pwa-for-wp'); ?></p>
	<?php
}

function pwaforwp_force_rememberme_setting_callback(){
	// Get Settings
	$settings = pwaforwp_defaultSettings();
	$rememberme_checked = 'checked="checked';
	if(!isset( $settings['force_rememberme'] ) || $settings['force_rememberme'] == 0){
		$rememberme_checked = '';
	}
	?>
        
	<input type="checkbox" name="pwaforwp_settings[force_rememberme]" id="pwaforwp_settings[force_rememberme]" class="" <?php echo esc_attr($rememberme_checked); ?> data-uncheck-val="0" value="1">
	<p><?php echo esc_html__('This option forces remember me while log in. Use this option when user is getting logged out while reopening PWA app.', 'pwa-for-wp'); ?></p>
	<?php
}
function pwaforwp_prefetch_manifest_setting_callback(){
	// Get Settings
	$settings = pwaforwp_defaultSettings(); 
	?>
        
	<input type="checkbox" name="pwaforwp_settings[prefetch_manifest_setting]" id="pwaforwp_settings[prefetch_manifest_setting]" class="" <?php echo (isset( $settings['prefetch_manifest_setting'] ) &&  $settings['prefetch_manifest_setting'] == 1 ? 'checked="checked"' : ''); ?> data-uncheck-val="0" value="1">
	<p><?php echo esc_html__('Prefetch manifest URLs provides some control over the request priority', 'pwa-for-wp'); ?></p>
	<?php
}
function pwaforwp_force_update_sw_setting_callback(){
	// Get Settings
	$settings = pwaforwp_defaultSettings(); 
    if(isset($settings['force_update_sw_setting'])){ 
        if(!version_compare($settings['force_update_sw_setting'],PWAFORWP_PLUGIN_VERSION, '>=') ){
            $settings['force_update_sw_setting'] = PWAFORWP_PLUGIN_VERSION;
        }
        // echo esc_attr($settings['force_update_sw_setting']);
        $force_update_sw_setting_value = $settings['force_update_sw_setting'];
    }else{ 
        $force_update_sw_setting_value = PWAFORWP_PLUGIN_VERSION;
    }	
    ?>
        <label><input type="text" id="pwaforwp_settings[force_update_sw_setting]" name="pwaforwp_settings[force_update_sw_setting]" value="<?php echo esc_attr($force_update_sw_setting_value ); ?>"></label>      
        <code><?php echo esc_html__('Current Version', 'pwa-for-wp'); ?> <?php echo esc_attr($force_update_sw_setting_value); ?></code>  
	<p><?php echo esc_html__('Change the version. It will automatically update the service worker for all the users', 'pwa-for-wp'); ?></p>
	<?php
}

function pwaforwp_cdn_setting_callback(){
	// Get Settings
	$settings = pwaforwp_defaultSettings(); 
	?>
	<input type="checkbox" name="pwaforwp_settings[cdn_setting]" id="pwaforwp_settings_cdn_setting" class="" <?php echo (isset( $settings['cdn_setting'] ) &&  $settings['cdn_setting'] == 1 ? 'checked="checked"' : ''); ?> value="1">
	<p><?php echo esc_html__('This helps you remove conflict with the CDN', 'pwa-for-wp'); ?></p>
	<?php
}

function pwaforwp_reset_setting_callback(){		
	?>              
        <button class="button pwaforwp-reset-settings">
            <?php echo esc_html__('Reset','pwa-for-wp'); ?>
        </button>
        
	<?php
}

function pwaforwp_cleandataonuninstall_setting_callback(){  
    // Get Settings
    $settings = pwaforwp_defaultSettings(); 
    ?>            
        <input type="checkbox" name="pwaforwp_settings[pwa_uninstall_data]" id="pwaforwp_settings_navigation_uninstall_setting" class="" <?php echo (isset( $settings['pwa_uninstall_data'] ) &&  $settings['pwa_uninstall_data'] == 1 ? 'checked="checked"' : ''); ?> value="1">
        <p><?php echo esc_html__('Check this box if you would like to completely remove all of its data when the plugin is deleted.', 'pwa-for-wp'); ?></p>
        
    <?php
}

function pwaforwp_loading_setting_callback(){	
    
        $settings = pwaforwp_defaultSettings();
        
	?>              
        <input type="checkbox" name="pwaforwp_settings[loading_icon]" id="pwaforwp_settings_loading_icon_setting" class="" <?php echo (isset( $settings['loading_icon'] ) &&  $settings['loading_icon'] == 1 ? 'checked="checked"' : ''); ?> value="1">
	<p><?php echo esc_html__('This helps show loading icon on page or post load', 'pwa-for-wp'); ?></p>
        
	<?php
}
function pwaforwp_loading_color_setting_callback(){	
    $settings = pwaforwp_defaultSettings(); ?>
    <input type="text" name="pwaforwp_settings[loading_icon_color]" id="pwaforwp_settings[loading_icon_color]" class="pwaforwp-colorpicker" data-alpha-enabled="true" value="<?php echo isset( $settings['loading_icon_color'] ) ? esc_attr( $settings['loading_icon_color']) : '#3498db'; ?>" data-default-color="#3498db">
	<p><?php echo esc_html__('Change the icon color of loader', 'pwa-for-wp'); ?></p><?php
}
function pwaforwp_loading_background_color_setting_callback(){	
    $settings = pwaforwp_defaultSettings(); ?>
    <input type="text" name="pwaforwp_settings[loading_icon_bg_color]" id="pwaforwp_settings[loading_icon_bg_color]" class="pwaforwp-colorpicker" data-alpha-enabled="true" value="<?php echo isset( $settings['loading_icon_bg_color'] ) ? esc_attr( $settings['loading_icon_bg_color']) : '#ffffff'; ?>" data-default-color="#ffffff">
	<p><?php echo esc_html__('Change the background color of loader', 'pwa-for-wp'); ?></p><?php
}
function pwaforwp_loading_display_inpwa_setting_callback(){	
	$settings = pwaforwp_defaultSettings();
	?>
    <label><input type="checkbox" name="pwaforwp_settings[loading_icon_display_pwa]" id="pwaforwp_settings[loading_icon_display_pwa]" class="" value="1" <?php echo isset( $settings['loading_icon_display_pwa'] ) && $settings['loading_icon_display_pwa']==1 ? 'checked' : ''; ?> ><?php echo esc_html__('Only on PWA', 'pwa-for-wp'); ?></label>
    <?php
}
function pwaforwp_loading_display_setting_callback(){	
    $settings = pwaforwp_defaultSettings(); 
    if(!isset($settings['loading_icon_display_desktop']) && $settings['loading_icon']==1){
    	$settings['loading_icon_display_desktop'] = 1;
    }
    if(!isset($settings['loading_icon_display_mobile']) && $settings['loading_icon']==1){
    	$settings['loading_icon_display_mobile'] = 1;
    }
    if(!isset($settings['loading_icon_display_admin']) && $settings['loading_icon']==1){
    	$settings['loading_icon_display_admin'] = 0;
    }
    ?>
    <label><input type="checkbox" name="pwaforwp_settings[loading_icon_display_desktop]" id="pwaforwp_settings[loading_icon_display_desktop]" class="" value="1" <?php echo isset( $settings['loading_icon_display_desktop'] ) && $settings['loading_icon_display_desktop']==1 ? 'checked' : ''; ?> ><?php echo esc_html__('Desktop', 'pwa-for-wp'); ?></label>
    <label><input type="checkbox" name="pwaforwp_settings[loading_icon_display_mobile]" id="pwaforwp_settings[loading_icon_display_mobile]" class="" value="1" <?php echo isset( $settings['loading_icon_display_mobile'] ) && $settings['loading_icon_display_mobile']==1 ? 'checked' : ''; ?> ><?php echo esc_html__('Mobile', 'pwa-for-wp'); ?></label>
    <label><input type="checkbox" name="pwaforwp_settings[loading_icon_display_admin]" id="pwaforwp_settings[loading_icon_display_admin]" class="" value="1" <?php echo isset( $settings['loading_icon_display_admin'] ) && $settings['loading_icon_display_admin']==1 ? 'checked' : ''; ?> ><?php echo esc_html__('Admin', 'pwa-for-wp'); ?></label>
    <?php
}

function pwaforwp_cache_external_links_setting_callback(){
	// Get Settings
	$settings = pwaforwp_defaultSettings(); 
	?>
	<input type="checkbox" name="pwaforwp_settings[external_links_setting]" id="pwaforwp_settings_external_links_setting" class="" <?php echo (isset( $settings['external_links_setting'] ) &&  $settings['external_links_setting'] == 1 ? 'checked="checked"' : ''); ?> value="1">
	<p><?php echo esc_html__('Caches external link\'s resource which are in html', 'pwa-for-wp'); ?></p>
	<?php
}

//Design Settings
function pwaforwp_background_color_callback(){
	// Get Settings
	$settings = pwaforwp_defaultSettings(); ?>
	
	<!-- Background Color -->
        <input type="text" name="pwaforwp_settings[background_color]" id="pwaforwp_settings[background_color]" class="pwaforwp-colorpicker" data-alpha-enabled="true" value="<?php echo isset( $settings['background_color'] ) ? esc_attr( $settings['background_color']) : '#D5E0EB'; ?>" data-default-color="#D5E0EB">
		<p class="description"></p>
	<?php
}
function pwaforwp_theme_color_callback(){
	// Get Settings
	$settings = pwaforwp_defaultSettings(); ?>
	
	<!-- Background Color -->
	<input type="text" name="pwaforwp_settings[theme_color]" id="pwaforwp_settings[theme_color]" class="pwaforwp-colorpicker" data-alpha-enabled="true" value="<?php echo isset( $settings['theme_color'] ) ? esc_attr( $settings['theme_color']) : '#D5E0EB'; ?>" data-default-color="#D5E0EB">
	<p class="description"></p>
	<?php
}

function pwaforwp_push_notification_callback(){	
    
	$settings = pwaforwp_defaultSettings(); 
	$selectedService = 'pushnotifications_io';
	$pushnotifications_style = 'style="display:block;"';
	$fcm_service_style = 'style="display:none;"'; 
    if( (isset($settings['fcm_server_key']) && !empty($settings['fcm_server_key']) && !isset($settings['notification_options'])) 
    	|| (isset($settings['notification_options']) && $settings['notification_options']=="fcm_push")
    ){
    	$selectedService = "fcm_push";
    	$pushnotifications_style = 'style="display:none;"';
		$fcm_service_style = 'style="display:block;"';
    }
    if( isset($settings['notification_options']) ){
    	$selectedService = $settings['notification_options'];
    	if(empty($selectedService)){
    		$selectedService = "";
	    	$pushnotifications_style = 'style="display:none;"';
			$fcm_service_style = 'style="display:none;"';
    	}
    }


        ?>        
        
        <div class="pwafowwp-server-key-section">
        	<table class="pwaforwp-pn-options">
        		<tbody>
        			<th><?php echo esc_html__('Push notification integration', 'pwa-for-wp');?></th>
        			<td>
        				<select name="pwaforwp_settings[notification_options]" id="pwaforwp_settings[notification_options]" class="regular-text pwaforwp-pn-service">
        					<option value=""><?php echo esc_html__('Select', 'pwa-for-wp') ?></option>
        					<option value="pushnotifications_io" <?php selected('pushnotifications_io', $selectedService) ?>><?php echo esc_html__('PushNotifications.io (Recommended)', 'pwa-for-wp') ?></option>
        					<option value="fcm_push" <?php selected('fcm_push', $selectedService) ?> ><?php echo esc_html__('FCM push notification', 'pwa-for-wp') ?></option>
        				</select>
        			</td>
        		</tbody>
        	</table>
            <table class="pwaforwp-push-notificatoin-table" <?php 
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- using style in variable no need to esc.
			echo $fcm_service_style; ?>>
                <tbody>
                    <tr>
                        <th><?php echo esc_html__('FCM Server API Key', 'pwa-for-wp') ?></th>  
                        <td><input class="regular-text" type="text" name="pwaforwp_settings[fcm_server_key]" id="pwaforwp_settings[fcm_server_key]" value="<?php echo (isset($settings['fcm_server_key'])? esc_attr($settings['fcm_server_key']):'') ; ?>"></td>
                    </tr>
                    <tr>
                        <th><?php echo esc_html__('Config', 'pwa-for-wp') ?></th>  
                        <td>
                            <textarea class="regular-text" placeholder="{ <?php echo "\n"; ?>apiKey: '<Your Api Key>', <?php echo "\n"; ?>authDomain: '<Your Auth Domain>',<?php echo "\n"; ?>databaseURL: '<Your Database URL>',<?php echo "\n"; ?>projectId: '<Your Project Id>',<?php echo "\n"; ?>storageBucket: '<Your Storage Bucket>', <?php echo "\n"; ?>messagingSenderId: '<Your Messaging Sender Id>' <?php echo "\n"; ?>}" rows="8" cols="60" id="pwaforwp_settings[fcm_config]" name="pwaforwp_settings[fcm_config]"><?php echo isset($settings['fcm_config']) ? esc_attr($settings['fcm_config']) : ''; ?></textarea>
                            <p><?php echo esc_html__('Note: Create a new firebase project on ', 'pwa-for-wp') ?> <a href="https://firebase.google.com/" target="_blank"><?php echo esc_html__('firebase', 'pwa-for-wp') ?></a> <?php echo esc_html__('console, its completly free by google with some limitations. After creating the project you will find FCM Key and json in project details section.', 'pwa-for-wp') ?></p>
                            <p><?php echo esc_html__('Note: Firebase push notification does not support on AMP. It will support in future', 'pwa-for-wp') ?> </p>
                        </td>
                    </tr>
                    <tr>
                        <th><?php echo esc_html__('FCM Push Notification Icon', 'pwa-for-wp') ?></th>  
                        <td>
                            <input type="text" name="pwaforwp_settings[fcm_push_icon]" id="pwaforwp_settings[fcm_push_icon]" class="pwaforwp-fcm-push-icon regular-text" size="50" value="<?php echo isset( $settings['fcm_push_icon'] ) ? esc_attr( pwaforwp_https($settings['fcm_push_icon'])) : ''; ?>">
							<button type="button" class="button pwaforwp-fcm-push-icon-upload" data-editor="content">
								<span class="dashicons dashicons-format-image" style="margin-top: 4px;"></span> <?php echo esc_html__('Choose Icon', 'pwa-for-wp'); ?> 
							</button>
                            <p><?php echo esc_html__('Change Firebase push notification icon. Default: PWA icon', 'pwa-for-wp') ?> </p>
                        </td>
                    </tr>
                    <tr>
                        <th><?php echo esc_html__('FCM Notification Budge Icon', 'pwa-for-wp') ?></th>  
                        <td>
                            <input type="text" name="pwaforwp_settings[fcm_budge_push_icon]" id="pwaforwp_settings[fcm_budge_push_icon]" class="pwaforwp-fcm-push-budge-icon regular-text" value="<?php echo isset( $settings['fcm_budge_push_icon'] ) ? esc_attr( pwaforwp_https($settings['fcm_budge_push_icon'])) : ''; ?>">
							<button type="button" class="button pwaforwp-fcm-push-budge-icon-upload" data-editor="content">
								<span class="dashicons dashicons-format-image" style="margin-top: 4px;"></span> <?php echo esc_html__('Choose Icon', 'pwa-for-wp'); ?> 
							</button>
                            <p><?php echo esc_html__('Change Firebase push notification budge icon 96x96. Default: Chrome icon', 'pwa-for-wp') ?> </p>
                        </td>
                    </tr>                                                            
                </tbody>   
            </table>                   
            <div class="pwaforwp-pn-recommended-options" <?php 
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- its has style only.
			echo $pushnotifications_style; ?>>
            	<div class="notification-banner" style="width:90%">
            			<?php if(class_exists('Push_Notification_Admin')){ 
            				$auth_settings = push_notification_auth_settings();
            				if(!isset($auth_settings['user_token'])){
            					echo '<div class="pwaforwp-center"><p>'.esc_html__('This feature requires to setup Push Notification','pwa-for-wp').' </p> <a href="'.esc_url_raw(admin_url('admin.php?page=push-notification')).'" target="_blank" class="button button-primary">'.esc_html__('Go to setup', 'pwa-for-wp').'</a></div>';
            				}else{
            					echo '<div class="pwaforwp-center"><p>'.esc_html__('Push notifications has it\'s separate options view','pwa-for-wp').'</p><a href="'. esc_url_raw(admin_url('admin.php?page=push-notification') ).'" class="button button-primary">'.esc_html__(' View Settings', 'pwa-for-wp').'</a></div>';
            				}
            			?>
            			
            		<?php }else{
            			$allplugins = get_transient( 'plugin_slugs');
						if($allplugins){
							$allplugins = array_flip($allplugins);
						}

            			$activate_url ='';
            			$class = 'not-exist';
            			if(file_exists( PWAFORWP_PLUGIN_DIR."/../push-notification/push-notification.php") && !is_plugin_active('push-notification/push-notification.php') ){
            				//plugin deactivated
            				$class = 'pushnotification';
            				$plugin = 'push-notification/push-notification.php';
            				$action = 'activate';
            				if ( strpos( $plugin, '/' ) ) {
								$plugin = str_replace( '\/', '%2F', $plugin );
							}
							$url = sprintf( admin_url( 'plugins.php?action=' . $action . '&plugin=%s&plugin_status=all&paged=1&s' ), $plugin );
							$activate_url = wp_nonce_url( $url, $action . '-plugin_' . $plugin );
            			 }
            			?>
            			<div class="pwaforwp-center">
	            			<p><?php echo esc_html__('This feature requires a Free plugin which integrates with a Free Push Notification service', 'pwa-for-wp'); ?>
	            			</p>
	            			<span data-activate-url="<?php echo esc_url($activate_url); ?>" 
	            				 class="pwaforwp-install-require-plugin button button-primary <?php echo esc_attr($class); ?>" data-secure="<?php echo esc_attr(wp_create_nonce('verify_request')); ?>"
	            				id="pushnotification">
	            				<?php echo esc_html__('Install Plugin', 'pwa-for-wp'); ?>
	            			</span>
	            		</div>
            			<?php
            		} ?>
	            	
            	</div>
            </div>
        </div>
        <div class="pwaforwp-notification-condition-section" <?php 
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- using style only in this variable.
		echo $fcm_service_style; ?> >
        <div>
            <h2><?php echo esc_html__('Send Notification On', 'pwa-for-wp') ?></h2>
            <table class="pwaforwp-push-notificatoin-table">
                <tbody>
                    <tr>
                        <th><?php echo esc_html__('Add New Post', 'pwa-for-wp') ?></th>  
                        <td>
                            <input  type="checkbox" name="pwaforwp_settings[on_add_post]" id="pwaforwp_settings[on_add_post]" class="pwaforwp-fcm-checkbox" <?php echo (isset( $settings['on_add_post'] ) &&  $settings['on_add_post'] == 1 ? 'checked="checked"' : ''); ?> value="1">
                            <?php
                            if(isset($settings['on_add_post']) && $settings['on_add_post'] == 1){
                             echo '<p>'.esc_html__('Notification Title', 'pwa-for-wp').' <input type="text" name="pwaforwp_settings[on_add_post_notification_title]" id="on_add_post_notification_title" placeholder="'.esc_attr__('New Post', 'pwa-for-wp').'" value="'.esc_attr($settings['on_add_post_notification_title']).'"></p>';   
                            }else{
                             echo  '<p class="pwaforwp-hide">'.esc_html__('Notification Title', 'pwa-for-wp').' <input type="text" name="pwaforwp_settings[on_add_post_notification_title]" id="on_add_post_notification_title" placeholder="'.esc_attr__('New Post', 'pwa-for-wp').'" value="'.esc_attr($settings['on_add_post_notification_title']).'"></p>';  
                            }
                            ?>
                            
                        </td>
                    </tr>
                    <tr>
                        <th><?php echo esc_html__('Update Post', 'pwa-for-wp') ?></th>  
                        <td><input type="checkbox" name="pwaforwp_settings[on_update_post]" id="pwaforwp_settings[on_update_post]" class="pwaforwp-fcm-checkbox" <?php echo (isset( $settings['on_update_post'] ) &&  $settings['on_update_post'] == 1 ? 'checked="checked"' : ''); ?> value="1">
                            <?php
                            if(isset($settings['on_update_post']) && $settings['on_update_post']== 1){
                             echo '<p>'.esc_html__('Notification Title', 'pwa-for-wp').' <input type="text" name="pwaforwp_settings[on_update_post_notification_title]" id="on_update_post_notification_title" placeholder="'.esc_attr__("Update Post","pwa-for-wp").'" value="'.(isset($settings['on_update_post_notification_title']) ? esc_attr($settings['on_update_post_notification_title']): '').'"></p>';   
                            }else{
                             echo  '<p class="pwaforwp-hide">'.esc_html__('Notification Title', 'pwa-for-wp').' <input type="text" name="pwaforwp_settings[on_update_post_notification_title]" id="on_update_post_notification_title" placeholder="'.esc_attr__("Update Post","pwa-for-wp").'" value="'.(isset($settings['on_update_post_notification_title']) ? esc_attr($settings['on_update_post_notification_title']) : '').'"></p>';  
                            }
                            ?>
                        </td>
                    </tr>
                     <tr>
                        <th><?php echo esc_html__('Add New Page', 'pwa-for-wp') ?></th>  
                        <td><input type="checkbox" name="pwaforwp_settings[on_add_page]" id="pwaforwp_settings[on_add_page]" class="pwaforwp-fcm-checkbox" <?php echo (isset( $settings['on_add_page'] ) &&  $settings['on_add_page'] == 1 ? 'checked="checked"' : ''); ?> value="1">
                            
                            <?php
                            if(isset($settings['on_add_page']) && $settings['on_add_page'] == 1){
                             echo '<p>'.esc_html__('Notification Title', 'pwa-for-wp').' <input type="text" name="pwaforwp_settings[on_add_page_notification_title]" id="on_add_page_notification_title" placeholder="'.esc_attr__("New Page","pwa-for-wp").'" value="'.(isset($settings['on_add_page_notification_title']) ? esc_attr($settings['on_add_page_notification_title']) : '').'"></p>';   
                            }else{
                             echo  '<p class="pwaforwp-hide">'.esc_html__('Notification Title', 'pwa-for-wp').' <input type="text" name="pwaforwp_settings[on_add_page_notification_title]" id="on_add_page_notification_title" placeholder="'.esc_attr__("New Page","pwa-for-wp").'" value="'.(isset($settings['on_add_page_notification_title']) ? esc_attr($settings['on_add_page_notification_title']) : '').'"></p>';  
                            }
                            ?>
                            
                        </td>
                    </tr>
                    <tr>
                        <th><?php echo esc_html__('Update Page', 'pwa-for-wp') ?></th>  
                        <td><input type="checkbox" name="pwaforwp_settings[on_update_page]" id="pwaforwp_settings[on_update_page]" class="pwaforwp-fcm-checkbox" <?php echo (isset( $settings['on_update_page'] ) &&  $settings['on_update_page'] == 1 ? 'checked="checked"' : ''); ?> value="1">
                            <?php
                            if(isset($settings['on_update_page']) && $settings['on_update_page'] == 1){
                             echo '<p>'.esc_html__('Notification Title', 'pwa-for-wp').' <input type="text" name="pwaforwp_settings[on_update_page_notification_title]" id="on_update_page_notification_title" placeholder="'.esc_attr__("Update Post","pwa-for-wp").'" value="'.(isset($settings['on_update_page_notification_title']) ? esc_attr($settings['on_update_page_notification_title']) : '').'"></p>';   
                            }else{
                             echo  '<p class="pwaforwp-hide">'.esc_html__('Notification Title', 'pwa-for-wp').' <input type="text" name="pwaforwp_settings[on_update_page_notification_title]" id="on_update_page_notification_title" placeholder="'.esc_attr__("Update Post","pwa-for-wp").'" value="'.(isset($settings['on_update_page_notification_title']) ? esc_attr($settings['on_update_page_notification_title']) : '').'"></p>';  
                            }
                            ?>
                        </td>
                    </tr>                                                            
                </tbody>   
            </table>                   
        </div>        
        <div>
            <h2><?php echo esc_html__('Send Manual Notification', 'pwa-for-wp') ?></h2>
            <table class="pwaforwp-push-notificatoin-table">
                <tbody>
                    
                    <tr>
                        <th><?php echo esc_html__('Title', 'pwa-for-wp') ?>:<br/><input style="width: 100%" placeholder="<?php esc_attr__("Title","pwa-for-wp") ?>" type="text" id="pwaforwp_notification_message_title" name="pwaforwp_notification_message_title" value="<?php echo esc_attr(get_bloginfo()); ?>">
                            <br>
			                   
                        </th>  
                        <td></td>
                    </tr>
                     <tr>
                        <th>
                        	<?php echo esc_html__('Redirection Url Onclick of notification', 'pwa-for-wp') ?>:<br/>
                        	<input style="width: 100%" placeholder="<?php esc_attr__("URL","pwa-for-wp") ?>" type="text" id="pwaforwp_notification_message_url" name="pwaforwp_notification_message_url" value="<?php echo esc_attr(pwaforwp_home_url()); ?>">
                            <br>
			                   
                        </th>  
                        <td></td>
                    </tr>
                    <tr>
                        <th>
                        	<?php echo esc_html__('Image Url', 'pwa-for-wp') ?>:<br/>
                        	<input style="width: 100%" placeholder="<?php esc_attr__("Image URL","pwa-for-wp") ?>" type="text" id="pwaforwp_notification_message_image_url" name="pwaforwp_notification_message_image_url" value="">
                            <br>
			                   
                        </th>  
                        <td></td>
                    </tr>   
                    <tr>
                        <th><?php echo esc_html__('Message', 'pwa-for-wp') ?>:<br/><textarea rows="5" cols="60" id="pwaforwp_notification_message" name="pwaforwp_notification_message"> </textarea>
                            <button class="button pwaforwp-manual-notification"> <?php echo esc_html__('Send', 'pwa-for-wp'); ?> </button>
                            <br>
			                    <div class="pwaforwp-notification-success pwa_hide"></div>
			                    <p class="pwaforwp-notification-error pwa_hide"></p>
                        </th>  
                        <td></td>
                    </tr>
                                                                                               
                </tbody>   
            </table>                   
        </div>
        </div>	
	<?php
}

function pwaforwp_custom_banner_design_callback(){
    
        $settings = pwaforwp_defaultSettings(); ?>           
        
        <h2><?php echo esc_html__('Custom Add To Homescreen Customization', 'pwa-for-wp') ?></h2>
        <table class="" style="display: block;">
            <tr><th><strong><?php echo esc_html__('Title', 'pwa-for-wp'); ?></strong></th><td><input type="text" name="pwaforwp_settings[custom_banner_title]" id="pwaforwp_settings[custom_banner_title]" class="" value="<?php echo isset( $settings['custom_banner_title'] ) ? esc_attr( $settings['custom_banner_title']) : 'Add '.esc_attr(get_bloginfo()).' to your Homescreen!'; ?>"></td></tr> 
            <tr><th><strong><?php echo esc_html__('Button Text', 'pwa-for-wp'); ?></strong></th><td><input type="text" name="pwaforwp_settings[custom_banner_button_text]" id="pwaforwp_settings[custom_banner_button_text]" class="" value="<?php echo isset( $settings['custom_banner_button_text'] ) ? esc_attr( $settings['custom_banner_button_text']) : 'Add'; ?>"></td></tr> 
            <tr><th><strong><?php echo esc_html__('Banner Background Color', 'pwa-for-wp'); ?></strong></th><td><input type="text" name="pwaforwp_settings[custom_banner_background_color]" id="pwaforwp_settings[custom_banner_background_color]" class="pwaforwp-colorpicker" data-alpha-enabled="true" value="<?php echo isset( $settings['custom_banner_background_color'] ) ? esc_attr( $settings['custom_banner_background_color']) : '#D5E0EB'; ?>" data-default-color="#D5E0EB"></td></tr> 
            <tr><th><strong><?php echo esc_html__('Banner Title Color', 'pwa-for-wp'); ?></strong></th><td><input type="text" name="pwaforwp_settings[custom_banner_title_color]" id="pwaforwp_settings[custom_banner_title_color]" class="pwaforwp-colorpicker" data-alpha-enabled="true" value="<?php echo isset( $settings['custom_banner_title_color'] ) ? esc_attr( $settings['custom_banner_title_color']) : '#000'; ?>" data-default-color="#000"></td></tr> 
            <tr><th><strong><?php echo esc_html__('Button Text Color', 'pwa-for-wp'); ?></strong></th><td><input type="text" name="pwaforwp_settings[custom_banner_btn_text_color]" id="pwaforwp_settings[custom_banner_btn_text_color]" class="pwaforwp-colorpicker" data-alpha-enabled="true" value="<?php echo isset( $settings['custom_banner_btn_text_color'] ) ? esc_attr( $settings['custom_banner_btn_text_color']) : '#fff'; ?>" data-default-color="#fff"></td></tr> 
            <tr><th><strong><?php echo esc_html__('Button Background Color', 'pwa-for-wp'); ?></strong></th><td><input type="text" name="pwaforwp_settings[custom_banner_btn_color]" id="pwaforwp_settings[custom_banner_btn_color]" class="pwaforwp-colorpicker" data-alpha-enabled="true" value="<?php echo isset( $settings['custom_banner_btn_color'] ) ? esc_attr( $settings['custom_banner_btn_color']) : '#006dda'; ?>" data-default-color="#006dda"></td></tr>                         
        </table>
        <?php
}

//General settings
function pwaforwp_app_name_callback(){
	// Get Settings
	$settings = pwaforwp_defaultSettings(); ?>
	
	<fieldset>
		<input type="text" name="pwaforwp_settings[app_blog_name]" class="regular-text" value="<?php if ( isset( $settings['app_blog_name'] ) && ( ! empty($settings['app_blog_name']) ) ) echo esc_attr($settings['app_blog_name']); ?>"/>
	</fieldset>

	<?php
}

function pwaforwp_app_short_name_callback(){
	// Get Settings
	$settings = pwaforwp_defaultSettings(); ?>
	
	<fieldset>
		<input type="text" name="pwaforwp_settings[app_blog_short_name]" class="regular-text" value="<?php if ( isset( $settings['app_blog_short_name'] ) && ( ! empty($settings['app_blog_short_name']) ) ) echo esc_attr($settings['app_blog_short_name']); ?>"/>
		
	</fieldset>
	<?php
}

function pwaforwp_description_callback(){
	// Get Settings
	$settings = pwaforwp_defaultSettings(); ?>
	<fieldset>
		<input type="text" name="pwaforwp_settings[description]" class="regular-text" value="<?php if ( isset( $settings['description'] ) && ( ! empty( $settings['description'] ) ) ) echo esc_attr( $settings['description'] ); ?>"/>				
	</fieldset>

	<?php
}

function pwaforwp_app_icon_callback(){
	// Get Settings
	$settings = pwaforwp_defaultSettings(); ?>
	
	<!-- Application Icon -->
        <input type="text" name="pwaforwp_settings[icon]" id="pwaforwp_settings[icon]" class="pwaforwp-icon regular-text" size="50" value="<?php echo isset( $settings['icon'] ) ? esc_attr( pwaforwp_https($settings['icon'])) : ''; ?>">
	<button type="button" class="button pwaforwp-icon-upload" data-editor="content">
		<span class="dashicons dashicons-format-image" style="margin-top: 4px;"></span> <?php echo esc_html__('Choose Icon', 'pwa-for-wp'); ?> 
	</button>
	
	<p class="description">
		<?php echo sprintf('%s <strong>%s</strong><br/> %s',
			esc_html__('Icon of your application when installed on the phone. Must be a PNG image exactly','pwa-for-wp'),
			esc_html__('192x192 in size.','pwa-for-wp'),
			esc_html__('- For Apple mobile exact sizes is necessary','pwa-for-wp')
				);
		?>
	</p>
	<?php
}

function pwaforwp_app_maskable_icon_callback() {
	// Get Settings
	$settings = pwaforwp_defaultSettings(); ?>
	
	<!-- Application Icon -->
    <input type="text" name="pwaforwp_settings[app_maskable_icon]" id="pwaforwp_settings[app_maskable_icon]" class="pwaforwp-icon regular-text pwaforwp-maskable-input" size="50" value="<?php echo isset( $settings['app_maskable_icon'] ) ? esc_attr( pwaforwp_https($settings['app_maskable_icon'])) : ''; ?>">
	<button type="button" class="button pwaforwp-maskable-icon-upload" data-editor="content">
		<span class="dashicons dashicons-format-image" style="margin-top: 4px;"></span> <?php echo esc_html__('Choose Icon', 'pwa-for-wp'); ?> 
	</button>
	<button type="button" style="background-color: red; border-color: red; color: #fff; display:none;" class="button pwaforwp_js_remove_maskable" > <?php echo esc_html__('Remove', 'pwa-for-wp'); ?></button>
	
	<p class="description">
		<?php echo sprintf('%s <strong>%s</strong><br/> %s',
			esc_html__('Icon of your application when installed on the phone. Must be a PNG image exactly','pwa-for-wp'),
			esc_html__('192x192 in size.','pwa-for-wp'),
			esc_html__('- For Apple mobile exact sizes is necessary','pwa-for-wp')
				);
		?>
	</p>
	<?php
}

function pwaforwp_monochrome_callback(){
	// Get Settings
	$settings = pwaforwp_defaultSettings(); ?>
	
	<!-- monochrome Icon -->
        <input type="text" name="pwaforwp_settings[monochrome]" id="pwaforwp_settings[monochrome]" class="pwaforwp-monochrome regular-text" size="50" value="<?php echo isset( $settings['monochrome'] ) ? esc_attr( pwaforwp_https($settings['monochrome'])) : ''; ?>">
	<button type="button" class="button pwaforwp-monochrome-upload" data-editor="content">
		<span class="dashicons dashicons-format-image" style="margin-top: 4px;"></span> <?php echo esc_html__('Choose Monochrome Icon', 'pwa-for-wp'); ?> 
	</button>
	
	<p class="description">
		<?php echo sprintf('%s <strong>%s</strong><br/> %s',
			esc_html__('Monochrome Icon for the application .Must be PNG having transparent background','pwa-for-wp'),
			esc_html__('512x512 in size.','pwa-for-wp'),
			esc_html__('- For Apple mobile exact sizes is necessary','pwa-for-wp')
				);
		?>
	</p>
	<?php
}

function pwaforwp_splash_icon_callback(){
	// Get Settings
	$settings = pwaforwp_defaultSettings(); ?>
	
	<!-- Splash Screen Icon -->
        <input type="text" name="pwaforwp_settings[splash_icon]" id="pwaforwp_settings[splash_icon]" class="pwaforwp-splash-icon regular-text" size="50" value="<?php echo isset( $settings['splash_icon'] ) ? esc_attr( pwaforwp_https($settings['splash_icon'])) : ''; ?>">
	<button type="button" class="button pwaforwp-splash-icon-upload" data-editor="content">
		<span class="dashicons dashicons-format-image" style="margin-top: 4px;"></span> <?php echo esc_html__('Choose Icon', 'pwa-for-wp'); ?>
	</button>
	
	<p class="description">
		<?php echo sprintf('%s <strong>%s</strong>',
			esc_html__('Icon displayed on the splash screen of your APPLICATION on supported devices. Must be a PNG image size exactly', 'pwa-for-wp'),
			esc_html__('512x512 in size.', 'pwa-for-wp')
				);
		?>
	</p>
	<label>
	<input type="checkbox" class="switch_apple_splash_screen" name="pwaforwp_settings[switch_apple_splash_screen]" value="1" <?php if(isset($settings['switch_apple_splash_screen']) && $settings['switch_apple_splash_screen']==1){ echo "checked"; } ?> ><?php echo esc_html__('Setup Splash Screen for iOS', 'pwa-for-wp') ?></label>
	<div class="pwaforwp-ios-splash-images" <?php if(isset($settings['switch_apple_splash_screen']) && !$settings['switch_apple_splash_screen']){ echo 'style="display:none"'; }?>>
		<div class="field" style="margin-bottom: 10px;">
			<label style="display: inline-block;width: 50%;"><?php echo esc_html__('iOS Splash Screen Method', 'pwa-for-wp') ?></label>
			<select name="pwaforwp_settings[iosSplashScreenOpt]" id="ios-splash-gen-opt">
				<option value=""><?php echo esc_html__('Select','pwa-for-wp') ?></option>
				<option value="generate-auto" <?php echo isset($settings['iosSplashScreenOpt']) && $settings['iosSplashScreenOpt']=='generate-auto'? 'selected': ''; ?>><?php echo esc_html__('Automatic', 'pwa-for-wp'); ?></option>
				<option  value="manually" <?php echo isset($settings['iosSplashScreenOpt']) && $settings['iosSplashScreenOpt']=='manually'? 'selected': ''; ?>><?php echo esc_html__('Manual', 'pwa-for-wp'); ?></option>
			</select>
		</div>

		<?php
		$currentpic = $splashIcons = pwaforwp_ios_splashscreen_files_data();
		$previewImg = '';
		if( isset( $settings['ios_splash_icon'][key($currentpic)] ) ){
           			$previewImg = '<img src="'.pwaforwp_https($settings['ios_splash_icon'][key($currentpic)]) .'?test='.wp_rand(00,99).'" width="60" height="40">';
		}
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- using custom html.
		echo '<div class="panel pwaforwp-hide" id="generate-auto-1"  style="max-height: 100%;">
				<div class="pwaforwp-ios-splash-screen-creator" style="display:inline-block; width:90%">
					<div class="field"><label>'.esc_html__('Select image (Only PNG)', 'pwa-for-wp').'</label><input type="file" id="file-upload-ios" accept="image/png"><img style="display:none" id="thumbnail"></div>
					<div class="field"><label>'.esc_html__('Background color', 'pwa-for-wp').'</label><input type="text" id="ios-splash-color" value="#FFFFFF"></div>
					<div style="padding-left: 25%;"><input type="button" onclick="pwa_getimageZip(this)" class="button" value="'.esc_attr__('Generate','pwa-for-wp').'">
					<span id="pwa-ios-splashmessage" style="font-size:17px"> </span></div>
				</div>
				<div class="splash_preview_wrp" style="display:inline-block; width:9%">'.
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- i am using custom html in variable
				$previewImg.'
				</div>
			</div>
			
			';
		?>
		<div class="panel pwaforwp-hide" id="manually-1" style="max-height: 100%;">
		<?php
		if(is_array($splashIcons) && !empty($splashIcons)){
		foreach ($splashIcons as $key => $splashValue) {
			
		?>
			<div class="pwaforwp-ios-splash-images-field">
				<label><?php echo esc_html($splashValue['name']." ($key) [".ucfirst($splashValue['orientation'])."]") ?></label>
				<input type="text" name="pwaforwp_settings[ios_splash_icon][<?php echo esc_attr($key) ?>]" id="pwaforwp_settings[ios_splash_icon][<?php echo esc_attr($key) ?>]" class="pwaforwp-splash-icon regular-text" size="50" value="<?php echo isset( $settings['ios_splash_icon'][$key] ) ? esc_attr( pwaforwp_https($settings['ios_splash_icon'][$key])) : ''; ?>">
				<button type="button" class="button pwaforwp-ios-splash-icon-upload" data-editor="content">
					<span class="dashicons dashicons-format-image" style="margin-top: 4px;"></span> <?php echo esc_html__('Choose Icon', 'pwa-for-wp'); ?>
				</button>
			</div>
		<?php } } ?>
		</div>
		
	</div>

	<?php
}

function pwaforwp_splash_maskable_icon_callback() {
	// Get Settings
	$settings = pwaforwp_defaultSettings(); ?>
	
	<!-- Splash  Maskable Screen Icon -->
    <input type="text" name="pwaforwp_settings[splash_maskable_icon]" id="pwaforwp_settings[splash_maskable_icon]" class="pwaforwp-splash-icon regular-text pwaforwp-maskable-input" size="50" value="<?php echo isset( $settings['splash_maskable_icon'] ) ? esc_attr( pwaforwp_https($settings['splash_maskable_icon'])) : ''; ?>">
	<button type="button" class="button pwaforwp-maskable-icon-upload" data-editor="content">
	<span class="dashicons dashicons-format-image" style="margin-top: 4px;"></span> <?php echo esc_html__('Choose Icon', 'pwa-for-wp'); ?>
	</button>
	<button type="button" style="background-color: red; border-color: red; color: #fff; display:none;" class="button pwaforwp_js_remove_maskable" > <?php echo esc_html__('Remove', 'pwa-for-wp'); ?></button>
	
	<p class="description">
		<?php echo sprintf('%s <strong>%s</strong>',
			esc_html__('Icon displayed on the splash screen of your application on supported devices. Must be a PNG image size exactly', 'pwa-for-wp'),
			esc_html__('512x512 in size.', 'pwa-for-wp')
				);
		?>
	</p>
	<?php
}

function pwaforwp_app_screenshots_callback(){
    // Get Settings
    $settings = pwaforwp_defaultSettings();
	?>
	<div class="js_clone_div" style="margin-top: 10px;">
		<input type="text" name="pwaforwp_settings[screenshots]" id="pwaforwp_settings[screenshots]"  class="pwaforwp-screenshots"  value="<?php echo isset( $settings['screenshots'] ) ? esc_attr( pwaforwp_https($settings['screenshots'])) : ''; ?>">
		<button type="button" class="button js_choose_button pwaforwp-screenshots-upload" data-editor="content">
			<span class="dashicons dashicons-format-image" style="margin-top: 4px;"></span> <?php echo esc_html__('Choose Screenshot', 'pwa-for-wp'); ?> 
		</button>
		<select name="pwaforwp_settings[form_factor]" class="pwaforwp_settings_form_factor" style="width:8em;vertical-align:top;">
			<option value="" ><?php esc_html_e( 'Form Factor', 'pwa-for-wp' ); ?>
				</option>
			<option value="narrow" <?php if ( isset( $settings['form_factor'] ) ) { selected( $settings['form_factor'], 'narrow' ); } ?>>
				<?php esc_html_e( 'Narrow', 'pwa-for-wp' ); ?>
			</option>
			<option value="wide" <?php if ( isset( $settings['form_factor'] ) ) { selected( $settings['form_factor'], 'wide' ); } ?>>
				<?php esc_html_e( 'Wide', 'pwa-for-wp' ); ?>
			</option>
		</select>
		<button type="button" class="button button-primary" id="screenshots_add_more"> <?php echo esc_html__('Add', 'pwa-for-wp'); ?> </button>
		<button type="button" style="background-color: red; border-color: red; color: #fff; display:none;" class="button js_remove_screenshot" > <?php echo esc_html__('Remove', 'pwa-for-wp'); ?> 
		</button>
	</div>
	<?php
	if (isset($settings['screenshots_multiple']) && is_array($settings['screenshots_multiple']) && !empty($settings['screenshots_multiple'])) {
		foreach ($settings['screenshots_multiple'] as $key => $screenshot) {
	?>	
		<div class="js_clone_div" style="margin-top: 10px;">
			<input type="text" name="pwaforwp_settings[screenshots_multiple][]"  class="pwaforwp-screenshots" value="<?php echo isset( $screenshot ) ? esc_attr( pwaforwp_https($screenshot)) : ''; ?>">
			<button type="button" class="button js_choose_button pwaforwp-screenshots-multiple-upload" data-editor="content">
				<span class="dashicons dashicons-format-image" style="margin-top: 4px;"></span> <?php echo esc_html__('Choose Screenshot', 'pwa-for-wp'); ?> 
			</button>
			<select name="pwaforwp_settings[form_factor_multiple][]" class="pwaforwp_settings_form_factor_multiple" style="width:8em;vertical-align:top;">
				<option value="" ><?php esc_html_e( 'Form Factor', 'pwa-for-wp' ); ?>
				</option>
				<option value="narrow" <?php if ( isset( $settings['form_factor_multiple'][$key] ) ) { selected( $settings['form_factor_multiple'][$key], 'narrow' ); } ?>>
					<?php esc_html_e( 'Narrow', 'pwa-for-wp' ); ?>
				</option>
				<option value="wide" <?php if ( isset( $settings['form_factor_multiple'][$key] ) ) { selected( $settings['form_factor_multiple'][$key], 'wide' ); } ?>>
					<?php esc_html_e( 'Wide', 'pwa-for-wp' ); ?>
				</option>
			</select>
			<button type="button" style="background-color: red; border-color: red; color: #fff;" class="button js_remove_screenshot" > <?php echo esc_html__('Remove', 'pwa-for-wp'); ?> 
				</button>
		</div>
	<?php
		}
	}
	?>
    <p class="description">
        <?php echo sprintf('%s <strong>%s</strong><br/> %s',
            esc_html__('Screenshots of your application when installed on the phone. Must be a PNG image exactly','pwa-for-wp'),
            esc_html__('512x512 in size.','pwa-for-wp'),
            esc_html__('- For all mobiles exact sizes is necessary','pwa-for-wp')
                );
        ?>
    </p>
	
    <?php
}

function pwaforwp_offline_page_callback(){
	// Get Settings
	$settings = pwaforwp_defaultSettings(); ?>
	<!-- WordPress Pages Dropdown -->
	<label for="pwaforwp_settings[offline_page]">
	<?php 
        $allowed_html = pwaforwp_expanded_allowed_tags();
		$selected = isset($settings['offline_page']) ? esc_attr($settings['offline_page']) : '';
		$showother = 'disabled';$selectedother = '';$selecteddefault = '';$pro = '';
		$extension_active = function_exists('pwaforwp_is_any_extension_active') ? pwaforwp_is_any_extension_active() : false;
		if($selected=='other'){ $selectedother= 'selected';} 
		if($selected=='0'){ $selecteddefault= 'selected';} 
		if($extension_active){$showother="";$pro="style='visibility:hidden'";} 
        $selectHtml = wp_kses(wp_dropdown_pages( array( 
			'name'              => 'pwaforwp_settings[offline_page]', 
			'class'             => 'pwaforwp_select_with_other', 
			'echo'              => 0, 
			'selected'          =>  esc_attr($selected),
		)), $allowed_html);
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- using custom html
		echo preg_replace('/<select(.*?)>(.*?)<\/select>/s', "<select$1><option value='0' ".esc_attr($selecteddefault)."> ".esc_html__('&mdash; Default &mdash;', 'pwa-for-wp')." </option><option value='other' ".esc_attr($selectedother)."> ".esc_html__('Custom URL', 'pwa-for-wp')." </option>$2</select><div class='pwaforwp-upgrade-pro-inline pwaforwp_dnone' ".$pro.">".esc_html__("To use this feature",'pwa-for-wp')." <a target='_blank' href='https://pwa-for-wp.com/pricing/'>".esc_html__('Upgrade', 'pwa-for-wp')." </a></div>", $selectHtml); 
		
	
	?>
	<div class="pwaforwp-sub-tab-headings pwaforwp_dnone"><input type="text" name="pwaforwp_settings[offline_page_other]" id="offline_page_other" class="regular-text" <?php echo esc_attr($showother); ?> placeholder="<?php echo esc_attr__('Custom offline page (Must be in same origin)', 'pwa-for-wp'); ?>" value="<?php echo isset($settings['offline_page_other']) ? esc_attr($settings['offline_page_other']) : ''; ?>"></div>
	
	</label>
	
	
	<p class="description">
		<?php
		/* translators: %s: offline page */
		printf(	esc_html__( 'Offline page is displayed, when the device is offline and the requested page is not already cached. Current offline page is %s', 'pwa-for-wp' ), get_permalink($settings['offline_page']) ? esc_url(get_permalink( $settings['offline_page'] )) : esc_url(get_bloginfo( 'wpurl' )) ); ?>
	</p>

	<?php
}

function pwaforwp_404_page_callback(){
	// Get Settings
	$settings = pwaforwp_defaultSettings(); ?>
	<!-- WordPress Pages Dropdown -->
	<label for="pwaforwp_settings[404_page]">
	<?php 
        $allowed_html = pwaforwp_expanded_allowed_tags();
		$selected = isset($settings['404_page']) ? esc_attr($settings['404_page']) : '';
		$showother = 'disabled';$selectedother = '';$selecteddefault = '';$pro = '';
		$extension_active = function_exists('pwaforwp_is_any_extension_active') ? pwaforwp_is_any_extension_active() : false;
		if($selected=='other'){ $selectedother= 'selected';} 
		if($selected=='0'){ $selecteddefault= 'selected';} 
		if($extension_active){$showother="";$pro="style='visibility:hidden'";} 
        $selectHtml = wp_kses(wp_dropdown_pages( array( 
			'name'              => 'pwaforwp_settings[404_page]', 
			'class'             => 'pwaforwp_select_with_other', 
			'echo'              => 0,
			'selected'          => isset($settings['404_page']) ? esc_attr($settings['404_page']) : '',
		)), $allowed_html); 
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- using custom html
		echo preg_replace('/<select(.*?)>(.*?)<\/select>/s', "<select$1><option value='0' ".esc_attr($selecteddefault)."> ".esc_html__('&mdash; Default &mdash;', 'pwa-for-wp')." </option><option value='other' ".esc_attr($selectedother)."> ".esc_html__('Custom URL', 'pwa-for-wp')." </option>$2</select><div class='pwaforwp-upgrade-pro-inline pwaforwp_dnone' ".$pro.">".esc_html__("To use this feature",'pwa-for-wp')." <a target='_blank' href='https://pwa-for-wp.com/pricing/'>".esc_html__('Upgrade', 'pwa-for-wp')." </a></div>", $selectHtml); 
		
		?>
		<div class="pwaforwp-sub-tab-headings pwaforwp_dnone"><input type="text" name="pwaforwp_settings[404_page_other]" id="404_page_other" class="regular-text"  <?php echo esc_attr($showother); ?> placeholder="<?php echo esc_attr__('Custom 404 page (Must be in same origin)', 'pwa-for-wp'); ?>" value="<?php echo isset($settings['404_page_other']) ? esc_attr($settings['404_page_other']) : ''; ?>"></div>
	
	</label>
	
	<p class="description">
		<?php
		/* translators: %s: 404 page */
		printf(	esc_html__( '404 page is displayed and the requested page is not found. Current 404 page is %s', 'pwa-for-wp' ), esc_url(	get_permalink($settings['404_page']	) ? get_permalink( $settings['404_page'] ) : '' )); ?>
	</p>

	<?php
}
function pwaforwp_start_page_callback(){
	// Get Settings
	$settings = pwaforwp_defaultSettings();
	?>
	<!-- WordPress Pages Dropdown -->
	<label for="pwaforwp_settings[start_page]">
	<?php 
        $allowed_html = pwaforwp_expanded_allowed_tags();  
		$selected = isset($settings['start_page']) ? esc_attr($settings['start_page']) : '';
		$showother = 'disabled';$selectedother = '';$selecteddefault = '';$selectedActiveUrl = '';$pro = '';
		$extension_active = function_exists('pwaforwp_is_any_extension_active') ? pwaforwp_is_any_extension_active() : false;
		if($selected=='other'){ $selectedother= 'selected';} 
		if($selected=='active_url'){
			$selectedActiveUrl= 'selected';
			$delete_permission = current_user_can('delete_posts');
			if(file_exists(ABSPATH.'pwa-manifest.json') && $extension_active && $delete_permission){
				wp_delete_file(ABSPATH.'pwa-manifest.json');
			}
		} 
		if($selected=='0'){ $selecteddefault= 'selected';} 
		if($extension_active){$showother="";$pro="style='visibility:hidden'";} 
        $selectHtml = wp_kses(wp_dropdown_pages( array( 
			'name'              => 'pwaforwp_settings[start_page]', 
			'class'             => 'pwaforwp_select_with_other', 
			'echo'              => 0,
			'selected'          => isset($settings['start_page']) ? esc_attr($settings['start_page']) : '',
		)), $allowed_html); 

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- using custom html
			echo preg_replace('/<select(.*?)>(.*?)<\/select>/s', "<select$1><option value='0' ".esc_attr($selectedother)."> ".esc_html__('&mdash; Homepage &mdash;', 'pwa-for-wp')." </option><option value='other' ".esc_attr($selectedother)."> ".esc_html__('Custom URL', 'pwa-for-wp')." </option><option value='active_url' ".esc_attr($selectedActiveUrl)."> ".esc_html__('Dynamic URL', 'pwa-for-wp')." </option>$2</select><div class='pwaforwp-upgrade-pro-inline pwaforwp_dnone' ".$pro.">".esc_html__("To use this feature",'pwa-for-wp')." <a target='_blank' href='https://pwa-for-wp.com/pricing/'>".esc_html__('Upgrade', 'pwa-for-wp')." </a></div>", $selectHtml); 
		
		?>
		<div class="pwaforwp-sub-tab-headings pwaforwp_dnone" ><input type="text" name="pwaforwp_settings[start_page_other]" id="start_page_other" class="regular-text" <?php echo esc_attr($showother); ?> placeholder="<?php echo esc_attr__('Custom Start page (Must be in same origin)', 'pwa-for-wp'); ?>" value="<?php echo isset($settings['start_page_other']) ? esc_attr($settings['start_page_other']) : ''; ?>"></div> 
		
	</label>
	<p class="description">
		<?php
			$current_page = isset($settings['start_page'])? get_permalink($settings['start_page']):''; 
			echo esc_html__( 'From where you want to launch PWA APP. Current start page is ', 'pwa-for-wp' ) . esc_url($current_page); 
		?>
	</p>

	<?php
}

function pwaforpw_orientation_callback(){
	
	$settings = pwaforwp_defaultSettings();         
        ?>
	
	<!-- Orientation Dropdown -->
	<label for="pwaforwp_settings[orientation]">
		<select name="pwaforwp_settings[orientation]" id="pwaforwp_settings[orientation]">
			<option value="" <?php if ( isset( $settings['orientation'] ) ) { selected( $settings['orientation'],'' ); } ?>>
				<?php echo esc_html__( 'Follow Device Orientation', 'pwa-for-wp' ); ?>
			</option>
			<option value="portrait" <?php if ( isset( $settings['orientation'] ) ) { selected( $settings['orientation'], 'portrait' ); } ?>>
				<?php echo esc_html__( 'Portrait', 'pwa-for-wp' ); ?>
			</option>
			<option value="landscape" <?php if ( isset( $settings['orientation'] ) ) { selected( $settings['orientation'], 'landscape' ); } ?>>
				<?php echo esc_html__( 'Landscape', 'pwa-for-wp' ); ?>
			</option>
			<option value="any" <?php if ( isset( $settings['orientation'] ) ) { selected( $settings['orientation'], 'any' ); selected( $settings['orientation'], 'any' ); } ?>>
				<?php echo esc_html__( 'Auto', 'pwa-for-wp' ); ?>
			</option>
			<option value="landscape-primary" <?php if ( isset( $settings['orientation'] ) ) { selected( $settings['orientation'], 'landscape-primary' ); } ?>>
				<?php echo esc_html__( 'Landscape-primary', 'pwa-for-wp' ); ?>
			</option>
			<option value="landscape-secondary" <?php if ( isset( $settings['orientation'] ) ) { selected( $settings['orientation'], 'landscape-secondary' ); } ?>>
				<?php echo esc_html__( 'Landscape-secondary', 'pwa-for-wp' ); ?>
			</option>
			<option value="portrait-primary" <?php if ( isset( $settings['orientation'] ) ) { selected( $settings['orientation'], 'portrait-primary' ); } ?>>
				<?php echo esc_html__( 'Portrait-primary', 'pwa-for-wp' ); ?>
			</option>
			<option value="portrait-secondary" <?php if ( isset( $settings['orientation'] ) ) { selected( $settings['orientation'], 'portrait-secondary' ); } ?>>
				<?php echo esc_html__( 'Portrait-secondary', 'pwa-for-wp' ); ?>
			</option>
		</select>
	</label>
	
	<p class="description">
		<?php esc_html__( 'Orientation of application on devices. When set to Follow Device Orientation your application will rotate as the device is rotated.', 'pwa-for-wp' ); ?>
	</p>

	<?php
}

function pwaforpw_display_callback(){
	
	$settings = pwaforwp_defaultSettings();         
        ?>
	
	<!-- Orientation Dropdown -->
	<label for="pwaforwp_settings[display]">
		<select name="pwaforwp_settings[display]" id="pwaforwp_settings[display]">
			<option value="" <?php if ( isset( $settings['display'] ) ) { selected( $settings['display'],'' ); } ?>>
				<?php echo esc_html__( 'Device display', 'pwa-for-wp' ); ?>
			</option>
			<option value="fullscreen" <?php if ( isset( $settings['display'] ) ) { selected( $settings['display'], 'fullscreen' ); } ?>>
				<?php echo esc_html__( 'Fullscreen', 'pwa-for-wp' ); ?>
			</option>
			<option value="standalone" <?php if ( isset( $settings['display'] ) ) { selected( $settings['display'], 'standalone' ); } ?>>
				<?php echo esc_html__( 'Standalone', 'pwa-for-wp' ); ?>
			</option>
			<option value="minimal-ui" <?php if ( isset( $settings['display'] ) ) { selected( $settings['display'], 'minimal-ui' ); } ?>>
				<?php echo esc_html__( 'Minimal-ui', 'pwa-for-wp' ); ?>
			</option>
			<option value="browser" <?php if ( isset( $settings['display'] ) ) { selected( $settings['display'], 'browser' ); } ?>>
				<?php echo esc_html__( 'Browser', 'pwa-for-wp' ); ?>
			</option>
		</select>
	</label>
	
	<p class="description">
		<?php esc_html__( 'Orientation of application on devices. When set to Follow Device Orientation your application will rotate as the device is rotated.', 'pwa-for-wp' ); ?>
	</p>

	<?php
}
function pwaforwp_apple_status_bar_callback(){
	$settings = pwaforwp_defaultSettings();         
        ?>
	<!-- iOS status bar -->
	<label for="pwaforwp_settings[ios_status_bar]">
		<select name="pwaforwp_settings[ios_status_bar]" id="pwaforwp_settings[ios_status_bar]">
			<option value="default" <?php if ( isset( $settings['ios_status_bar'] ) ) { selected( $settings['ios_status_bar'],'default' ); } ?>>
				<?php echo esc_html__( 'Default', 'pwa-for-wp' ); ?>
			</option>
			<option value="black" <?php if ( isset( $settings['ios_status_bar'] ) ) { selected( $settings['ios_status_bar'], 'black' ); } ?>>
				<?php echo esc_html__( 'Black', 'pwa-for-wp' ); ?>
			</option>
			<option value="black-translucent" <?php if ( isset( $settings['ios_status_bar'] ) ) { selected( $settings['ios_status_bar'], 'black-translucent' ); } ?>>
				<?php echo esc_html__( 'Black translucent', 'pwa-for-wp' ); ?>
			</option>
		</select>
	</label>
	
	<p class="description">
		<?php esc_html__( 'The status bar at the top of the screen (which usually displays the time and battery status).', 'pwa-for-wp' ); ?>
	</p>

	<?php
}

function pwaforwp_related_applications_callback(){
	// Get Settings
	$settings = pwaforwp_defaultSettings();
	$related_applications_div = 'none';
	if(isset( $settings['prefer_related_applications'] ) && $settings['prefer_related_applications'] == 1){
		$related_applications_div = '';
	}
	
	?>
	<div id="related_applications_div" style="display:<?php echo esc_attr($related_applications_div); ?>">
	<fieldset>
		<label for="pwaforwp_settings[related_applications]"><?php echo esc_html__( 'PlayStore App ID', 'pwa-for-wp' ); ?></label>&nbsp;
		<input type="text" name="pwaforwp_settings[related_applications]" class="regular-text" placeholder="<?php esc_attr__("com.example.app","pwa-for-wp") ?>" value="<?php if ( isset( $settings['related_applications'] ) && ( ! empty($settings['related_applications']) ) ) echo esc_attr($settings['related_applications']); ?>"/>
	</fieldset>
	<fieldset>
		<label for="pwaforwp_settings[related_applications_ios]"><?php echo esc_html__( 'AppStore App ID', 'pwa-for-wp' ); ?></label>&nbsp;
		<input type="text" name="pwaforwp_settings[related_applications_ios]" placeholder="<?php esc_attr__("id123456789","pwa-for-wp") ?>" class="regular-text" value="<?php if ( isset( $settings['related_applications_ios'] ) && ( ! empty($settings['related_applications_ios']) ) ) echo esc_attr($settings['related_applications_ios']); ?>"/>
	</fieldset>
	</div>

	<?php
}

function pwaforwp_prefer_related_applications_callback(){
	// Get Settings
	$settings = pwaforwp_defaultSettings();	
	$prefer_related_applications = '';
	if(isset( $settings['prefer_related_applications'] ) && $settings['prefer_related_applications'] == 1){
		$prefer_related_applications = 'checked="checked';
	}
	?>        
	<input type="checkbox" name="pwaforwp_settings[prefer_related_applications]" id="prefer_related_applications" class="" <?php echo esc_attr($prefer_related_applications); ?> data-uncheck-val="0" value="1">
	<?php
}

function pwaforwp_one_signal_support_callback(){
	// Get Settings
	$settings = pwaforwp_defaultSettings(); 
	?>
	<input type="checkbox" name="pwaforwp_settings[one_signal_support_setting]" id="pwaforwp_settings[one_signal_support_setting]" class="pwaforwp-onesignal-support" <?php echo (isset( $settings['one_signal_support_setting'] ) &&  $settings['one_signal_support_setting'] == 1 ? 'checked="checked"' : ''); ?> value="1">
               
	<?php
}
function pwaforwp_pushnami_support_callback(){
	// Get Settings
	$settings = pwaforwp_defaultSettings();
	?>
	<input type="checkbox" name="pwaforwp_settings[pushnami_support_setting]" id="pwaforwp_settings[pushnami_support_setting]" class="pwaforwp-pushnami-support" <?php echo (isset( $settings['pushnami_support_setting'] ) &&  $settings['pushnami_support_setting'] == 1 ? 'checked="checked"' : ''); ?> value="1">

	<?php
}

function pwaforwp_webpushr_support_callback(){
	// Get Settings
	$settings = pwaforwp_defaultSettings();
	?>
	<input type="checkbox" name="pwaforwp_settings[webpusher_support_setting]" id="pwaforwp_settings[webpusher_support_setting]" class="pwaforwp-pushnami-support" <?php echo (isset( $settings['webpusher_support_setting'] ) &&  $settings['webpusher_support_setting'] == 1 ? 'checked="checked"' : ''); ?> value="1">

	<?php
}

function pwaforwp_wphide_support_callback(){
	// Get Settings
	$settings = pwaforwp_defaultSettings();
	?>
	<input type="checkbox" name="pwaforwp_settings[wphide_support_setting]" id="pwaforwp_settings[wphide_support_setting]" class="pwaforwp-wphide-support" <?php echo (isset( $settings['wphide_support_setting'] ) &&  $settings['wphide_support_setting'] == 1 ? 'checked="checked"' : ''); ?> value="1">

	<?php
}

function pwaforwp_custom_add_to_home_callback(){
	// Get Settings
	$settings = pwaforwp_defaultSettings(); 
	?>
	<input type="checkbox" name="pwaforwp_settings[custom_add_to_home_setting]" id="pwaforwp_settings[custom_add_to_home_setting]" class="pwaforwp-add-to-home-banner-settings" <?php echo (isset( $settings['custom_add_to_home_setting'] ) &&  $settings['custom_add_to_home_setting'] == 1 ? 'checked="checked"' : ''); ?> value="1">
	<p><?php echo esc_html__('Show custom responsive add to home banner popup', 'pwa-for-wp'); ?></p>
        <?php if(isset( $settings['custom_add_to_home_setting'] ) &&  $settings['custom_add_to_home_setting'] == 1) {  ?>
        <div class="pwaforwp-enable-on-desktop">
            <input type="checkbox" name="pwaforwp_settings[enable_add_to_home_desktop_setting]" id="enable_add_to_home_desktop_setting" class="" <?php echo (isset( $settings['enable_add_to_home_desktop_setting'] ) &&  $settings['enable_add_to_home_desktop_setting'] == 1 ? 'checked="checked"' : ''); ?> value="1"><strong><?php echo esc_html__('Enable On Desktop', 'pwa-for-wp'); ?></strong>
            <p><?php echo esc_html__('Note: By default pop up will appear on mobile device, to appear on desktop check enable on desktop', 'pwa-for-wp'); ?></p>
        </div>
        <?php }else{ ?>
        <div class="afw_hide pwaforwp-enable-on-desktop"><input type="checkbox" name="pwaforwp_settings[enable_add_to_home_desktop_setting]" id="enable_add_to_home_desktop_setting" class="" <?php echo (isset( $settings['enable_add_to_home_desktop_setting'] ) &&  $settings['enable_add_to_home_desktop_setting'] == 1 ? 'checked="checked"' : ''); ?> value="1"><strong><?php echo esc_html__('Enable On Desktop', 'pwa-for-wp'); ?></strong>
            <p><?php echo esc_html__('Note: By default pop up will appear on mobile device, to appear on desktop check enable on desktop', 'pwa-for-wp'); ?></p>
        </div>
        <?php }
        //option for static websites
         ?>
        <div class="show-banner-on-static-website">
        	<input type="checkbox" name="pwaforwp_settings[show_banner_without_scroll]" id="show_banner_without_scroll" value="1" <?php echo (isset( $settings['show_banner_without_scroll'] ) &&  $settings['show_banner_without_scroll'] == 1 ? 'checked="checked"' : ''); ?> >
        	<label for="show_banner_without_scroll" style="font-weight:600"><?php echo esc_html__('Show banner without scroll', 'pwa-for-wp');?></label>
        	<p><?php echo esc_html__('By default pop up will appear on scroll', 'pwa-for-wp'); ?></p>
        </div>


	<?php
	pwaforwp_custom_banner_design_callback();
}
function pwaforwp_add_to_home_callback(){
	
	$settings = pwaforwp_defaultSettings();         
        ?>		
        <input type="text" name="pwaforwp_settings[add_to_home_selector]" id="pwaforwp_settings[add_to_home_selector]" class="pwaforwp-add-to-home-selector regular-text" size="50" value="<?php echo isset( $settings['add_to_home_selector'] ) ? esc_attr( $settings['add_to_home_selector']) : ''; ?>">
	<p><?php echo esc_html__('jQuery selector (.element) or (#element)', 'pwa-for-wp'); ?></p>	
        <p><?php echo esc_html__('Note: It is currently available in non AMP', 'pwa-for-wp'); ?></p>	
        <p><?php echo esc_html__('Note: In IOS devices this functionality will not work.', 'pwa-for-wp'); ?></p>	
	<?php
}

// Dashboard
function pwaforwp_files_status_callback(){
    
       $serviceWorkerObj = new PWAFORWP_Service_Worker();
       $is_amp   = $serviceWorkerObj->is_amp;             
	   $settings = pwaforwp_defaultSettings();

	   $nonAmpStatusMsg = $nonampStatusIcon = $nonAmpLearnMoreLink = '';

	   if(!isset( $settings['normal_enable'] ) || (isset( $settings['normal_enable'] ) && $settings['normal_enable'] != 1) ){
			$nonAmpStatusMsg = 'PWA is disabled';
	   }
	   
	    $nonamp_manifest_status = true;
		if(!pwaforwp_is_enabled_pwa_wp()){
			$swUrl = esc_url(pwaforwp_manifest_json_url());
			$nonamp_manifest_status = @pwaforwp_checkStatus($swUrl);
		}
		if(!$nonamp_manifest_status && $nonAmpStatusMsg==''){
			$nonAmpStatusMsg = 'Manifest not working';
		}

		$swFile = apply_filters('pwaforwp_sw_name_modify',"pwa-sw".pwaforwp_multisite_postfix().".js");
		$nonamp_sw_status = true;
		if(!pwaforwp_is_enabled_pwa_wp()){
			$swUrl = esc_url(pwaforwp_home_url().$swFile);
			$swUrl = pwaforwp_service_workerUrls($swUrl, $swFile);
			$nonamp_sw_status = @pwaforwp_checkStatus($swUrl);
		}
		if(!$nonamp_sw_status && $nonAmpStatusMsg==''){
			$nonAmpStatusMsg = 'Service Worker not working';
		}
		if ( !is_ssl() && $nonAmpStatusMsg=='' ) {
			$nonAmpStatusMsg = esc_html__('PWA failed to initialized, the site is not HTTPS','pwa-for-wp');
			$nonAmpLearnMoreLink = '<a href="https://pwa-for-wp.com/docs/article/site-need-https-for-pwa/" target="_blank">'.esc_html__('Learn more', 'pwa-for-wp').'</a>';
		}

		if($nonAmpStatusMsg==''){
			$nonampStatusIcon = '<span class="dashicons dashicons-yes" style="color: #46b450;"></span>';
			$nonAmpStatusMsg = esc_html__('PWA is working','pwa-for-wp');
		}

		if($is_amp){
			$ampStatusMsg = $ampStatusIcon = '';
			if(!isset( $settings['amp_enable'] ) || (isset( $settings['amp_enable'] ) && $settings['amp_enable'] != 1) ){
				$ampStatusMsg = esc_html__('PWA is disabled','pwa-for-wp');
		    }

			$amp_manifest_status = true;
			if(!pwaforwp_is_enabled_pwa_wp()){
			  $swUrl = esc_url(pwaforwp_manifest_json_url(true));
			  $amp_manifest_status = @pwaforwp_checkStatus($swUrl);
			}
			if(!$amp_manifest_status && $ampStatusMsg==''){
				$ampStatusMsg = esc_html__('Manifest not working','pwa-for-wp');
			}
			
			$swFile = "pwa-amp-sw".pwaforwp_multisite_postfix().".js";
			$amp_sw_status = true;
			if(!pwaforwp_is_enabled_pwa_wp()){
				$swUrl = esc_url(pwaforwp_home_url().$swFile);
				$swUrl = pwaforwp_service_workerUrls($swUrl, $swFile);
				$amp_sw_status = @pwaforwp_checkStatus($swUrl);
			}
			if(!$amp_sw_status && $ampStatusMsg==''){
				$ampStatusMsg = esc_html__('Service Worker not working','pwa-for-wp');
			}
			
			if ( !is_ssl() && $ampStatusMsg=='') {
				$ampStatusMsg = '';
				if(isset( $settings['normal_enable'] ) && $settings['normal_enable'] != 1) {
					$ampStatusMsg = esc_html__('PWA failed to initialized, the site is not HTTPS','pwa-for-wp');
				}
			}elseif($ampStatusMsg==''){
				$ampStatusIcon = '<span class="dashicons dashicons-yes" style="color: #46b450;"></span>';
				$ampStatusMsg = esc_html__('PWA is working on AMP','pwa-for-wp');
			}
		}
       
        ?>
        <table class="pwaforwp-files-table">
            <tbody>
                <?php if($is_amp) { ?>
                <tr>
                    <th></th>
                    <th><?php echo esc_html__( 'WordPress (Non-AMP)', 'pwa-for-wp' ) ?></th>
                    <th><?php echo esc_html__( 'AMP', 'pwa-for-wp' ); ?></th>
                </tr>    
                <?php } ?>
				<tr>
                    <th style="width:20%"><?php echo esc_html__( 'Status', 'pwa-for-wp' ) ?></th>
                    <td style="width:40%"><p><?php 
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- using custom html
					echo $nonampStatusIcon .' '. esc_html( $nonAmpStatusMsg ). ' '.$nonAmpLearnMoreLink ?></p></td>
					<?php if($is_amp) { ?>
                    <td style="width:40%"><p><?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- using custom html
					echo $ampStatusIcon.' '.esc_html( $ampStatusMsg ); ?></p></td>
					<?php } ?>
                </tr>
                
                <tr>
                    <th><label for="pwaforwp_settings_normal_enable"><b><?php echo esc_html__( 'Enable / Disable', 'pwa-for-wp' ) ?></label></b></th>
	                <td>
						<label>
							<input type="checkbox"  <?php echo (isset( $settings['normal_enable'] ) && $settings['normal_enable'] == 1 ? 'checked="checked"' : ''); ?> value="1" class="pwaforwp-checkbox-tracker" data-id="pwaforwp_settings[normal_enable]" id="pwaforwp_settings_normal_enable"> 
							<input type="hidden" name="pwaforwp_settings[normal_enable]" id="pwaforwp_settings[normal_enable]" value="<?php echo esc_attr($settings['normal_enable']); ?>" >
						</label>
	               	</td>
                    <td>
                        <?php if($is_amp) { ?>
                        <label><input type="checkbox"  <?php echo (isset( $settings['amp_enable'] ) &&  $settings['amp_enable'] == 1 ? 'checked="checked"' : ''); ?> value="1"  class="pwaforwp-checkbox-tracker" data-id="pwaforwp_settings[amp_enable]"> 
                        	<input type="hidden" name="pwaforwp_settings[amp_enable]" id="pwaforwp_settings[amp_enable]" value="<?php echo esc_attr($settings['amp_enable']); ?>" >
                        </label>
                         <?php } ?>
                    </td>    
                    
                </tr>
            <tr>
                <th>
                 <?php echo esc_html__( 'Manifest', 'pwa-for-wp' ) ?> 
                </th>
                <td>
                   <?php
                   
                  	if(!$nonamp_manifest_status) {
                        printf( '<p><span class="dashicons dashicons-no-alt" style="color: #dc3232;"></span><a class="pwaforwp-service-activate" data-id="pwa-manifest" href="#">'.esc_html__( 'Click here to setup', 'pwa-for-wp' ).'</a> </p>'
                                .'<p class="pwaforwp-ins-note pwaforwp-hide">'.esc_html__( 'Change the permission or downlad the file', 'pwa-for-wp' ).' <a target="_blank" href="http://pwa-for-wp.com/docs/article/how-to-download-required-files-manually-and-place-it-in-root-directory-or-change-the-permission/">'.esc_html__( 'Instruction', 'pwa-for-wp' ).'</a></p>' );
                 }else{
                         printf( '<p><span class="dashicons dashicons-yes" style="color: #46b450;"></span> </p>', 'manifest url' );
                 }
                  ?>   
                </td>
                <td>
                  <?php
                  if($is_amp){
                    if(!$amp_manifest_status) {                                                                
                        printf( '<p><span class="dashicons dashicons-no-alt" style="color: #dc3232;"></span><a class="pwaforwp-service-activate" data-id="pwa-amp-manifest" href="#">'.esc_html__( 'Click here to setup', 'pwa-for-wp' ).'</a></p>'
                                . '<p class="pwaforwp-ins-note pwaforwp-hide">'.esc_html__( 'Change the permission or downlad the file', 'pwa-for-wp' ).' <a target="_blank" href="http://pwa-for-wp.com/docs/article/how-to-download-required-files-manually-and-place-it-in-root-directory-or-change-the-permission/">'.esc_html__( 'Instruction', 'pwa-for-wp' ).'</a></p>' );
                     }else{
                         printf( '<p><span class="dashicons dashicons-yes" style="color: #46b450;"></span> </p>', 'manifest url' );
                    }    
                  }
                  
                  ?>  
                </td>
                
            </tr>
            <tr>
                <th>                 
             <?php echo esc_html__( 'Service Worker', 'pwa-for-wp' ); ?>  
                </th>
                 <td>
                    <?php
                      
                    if(!$nonamp_sw_status) {
                      printf( '<p><span class="dashicons dashicons-no-alt" style="color: #dc3232;"></span> <a class="pwaforwp-service-activate" data-id="pwa-sw" href="#">'.esc_html__( 'Click here to setup', 'pwa-for-wp' ).'</a></p>'
                              . '<p class="pwaforwp-ins-note pwaforwp-hide">'.esc_html__( 'Change the permission or downlad the file', 'pwa-for-wp' ).' <a target="_blank" href="http://pwa-for-wp.com/docs/article/how-to-download-required-files-manually-and-place-it-in-root-directory-or-change-the-permission/">'.esc_html__( 'Instruction', 'pwa-for-wp' ).'</a></p>' );
                   }else{
                      printf( '<p><span class="dashicons dashicons-yes" style="color: #46b450;"></span> </p>', 'manifest url' );
                   }
                  ?>  
                </td>
                <td>
                  <?php
                  if($is_amp){
                      
                    
                    if(!$amp_sw_status) {
                            printf( '<p><span class="dashicons dashicons-no-alt" style="color: #dc3232;"></span><a class="pwaforwp-service-activate" data-id="pwa-amp-sw" href="#">'.esc_html__( 'Click here to setup', 'pwa-for-wp' ).'</a> </p>'
                                    . '<p class="pwaforwp-ins-note pwaforwp-hide">'.esc_html__( 'Change the permission or downlad the file', 'pwa-for-wp' ).' <a target="_blank" href="http://pwa-for-wp.com/docs/article/how-to-download-required-files-manually-and-place-it-in-root-directory-or-change-the-permission/">'.esc_html__( 'Instruction', 'pwa-for-wp' ).'</a></p>' );
                    }else{
                            printf( '<p><span class="dashicons dashicons-yes" style="color: #46b450;"></span> </p>' );
                    }    
                  }
                    
                  ?>  
                </td>
               
            </tr>
            <tr>
                <th>                 
              <?php echo esc_html__( 'HTTPS', 'pwa-for-wp' ) ?> 
                </th>
                <td>
                  <?php
                  if ( is_ssl() ) {
                            echo '<p><span class="dashicons dashicons-yes" style="color: #46b450;"></span> </p>';
                    } else {
                            echo '<p><span class="dashicons dashicons-no-alt" style="color: #dc3232;"></span> </p><p>'.esc_html__( 'This site is not configure with https', 'pwa-for-wp' ).' <a href="https://pwa-for-wp.com/docs/article/site-need-https-for-pwa/" target="_blank">'.esc_html__('Learn more', 'pwa-for-wp').'</a></p>';                                     
                    }
                  ?>  
                </td>
                <td>
                  <?php
                  if ($is_amp && is_ssl() ) {
                            echo '<p><span class="dashicons dashicons-yes" style="color: #46b450;"></span> </p>';
                    } 
                  ?>  
                </td>
            </tr>
            
            </tbody>    
        </table>
        
        <?php
}

function pwaforwp_amp_status_callback(){
    	        
        $swUrl        = esc_url(site_url()."/sw".pwaforwp_multisite_postfix().".js");
	$file_headers = @pwaforwp_checkStatus($swUrl);	
        
	if(!$file_headers) {
		printf( '<p><span class="dashicons dashicons-no-alt" style="color: #dc3232;"></span> </p>' );
	}else{
		printf( '<p><span class="dashicons dashicons-yes" style="color: #46b450;"></span> </p>' );
	}
}

function pwaforwp_checkStatus($swUrl){
    
        $settings = pwaforwp_defaultSettings();
        $manualfileSetup = "";

        if(array_key_exists('manualfileSetup', $settings)){
            $manualfileSetup = $settings['manualfileSetup'];    
        }	
    
	if($manualfileSetup){
		if( !pwaforwp_is_file_inroot() || is_multisite() ){
			$response = wp_remote_get( esc_url_raw( $swUrl ) );
			$response_code       = wp_remote_retrieve_response_code( $response );
			$response_message = wp_remote_retrieve_response_message( $response );

			if ( 200 != $response_code && ! empty( $response_message ) ) {
				return false;
			} elseif ( 200 != $response_code ) {
				return false;
			} else {
				return true;
		        }
		}else{

			$wppath               = str_replace("//","/",str_replace("\\","/",realpath(ABSPATH))."/");
			$wppath         	  = apply_filters("pwaforwp_file_creation_path", $wppath);
			$swjsFile             = $wppath."pwa-amp-sw".pwaforwp_multisite_postfix().".js";
			$swHtmlFile           = $wppath."pwa-amp-sw".pwaforwp_multisite_postfix().".html";
			$swrFile              = $wppath."pwa-register-sw".pwaforwp_multisite_postfix().".js";
			$swmanifestFile       = $wppath."pwa-amp-manifest".pwaforwp_multisite_postfix().".json";                
			$swjsFileNonAmp       = $wppath."pwa-sw".pwaforwp_multisite_postfix().".js";
			$swmanifestFileNonAmp = $wppath."pwa-manifest".pwaforwp_multisite_postfix().".json";
	        
	        switch ($swUrl) {
	            case pwaforwp_manifest_json_url(true):
	                    if(file_exists($swmanifestFile)){
	                            return true;
	                    }
	                    break;
	            case pwaforwp_home_url()."pwa-amp-sw".pwaforwp_multisite_postfix().".js":
					if(file_exists($swjsFile)){
						return true;
					}
					break;
	            case pwaforwp_home_url()."pwa-sw".pwaforwp_multisite_postfix().".js":
					if(file_exists($swjsFileNonAmp)){
						return true;
					}
					break;
	            case pwaforwp_manifest_json_url():
					if(file_exists($swmanifestFileNonAmp)){
						return true;
					}
					break;
	            case pwaforwp_home_url()."pwa-amp-sw".pwaforwp_multisite_postfix().".html":
					if(file_exists($swHtmlFile)){
						return true;
					}
					break;  
	            case pwaforwp_home_url()."pwa-register-sw".pwaforwp_multisite_postfix().".js":
					if(file_exists($swrFile)){
						return true;
					}
					break;          
	                                
				default:
					# code...
					break;
			}
		}
	}
	$ret = true;
	$file_headers = @get_headers($swUrl);       
	if(!$file_headers || $file_headers[0] == 'HTTP/1.0 404 Not Found' || $file_headers[0] == 'HTTP/1.0 301 Moved Permanently' || $file_headers[0] == 'HTTP/1.1 404 Not Found' || $file_headers[0] == 'HTTP/1.1 301 Moved Permanently') {
		 $ret = false;
	}
        
	return $ret;
	// Handle $response here. */
}

/**
 * Enqueue CSS and JS
 */
function pwaforwp_enqueue_style_js( $hook ) {
    // Load only on pwaforwp plugin pages
	if ( !is_admin() ) {
		return;
	}
	$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

	wp_register_script('pwaforwp-all-page-js', PWAFORWP_PLUGIN_URL . 'assets/js/all-page-script'.$suffix.'.js', array( ), PWAFORWP_PLUGIN_VERSION, true);
        
        $object_name = array(
            'ajax_url'                  => admin_url( 'admin-ajax.php' ),
            'uploader_title'            => esc_html__('Application Icon', 'pwa-for-wp'),
            'splash_uploader_title'     => esc_html__('Splash Screen Icon', 'pwa-for-wp'),
            'uploader_button'           => esc_html__('Select Icon', 'pwa-for-wp'),
            'file_status'               => esc_html__('Check permission or download from manual', 'pwa-for-wp'),
            'pwaforwp_security_nonce'   => wp_create_nonce('pwaforwp_ajax_check_nonce'),
            'iosSplashIcon'				=> pwaforwp_ios_splashscreen_files_data(),
        );
        
        $object_name = apply_filters('pwaforwp_localize_filter',$object_name,'pwaforwp_obj');
        
        wp_localize_script('pwaforwp-all-page-js', 'pwaforwp_obj', $object_name);
        wp_enqueue_script('pwaforwp-all-page-js');


	if($hook!='toplevel_page_pwaforwp'){return ; }
	// Color picker CSS
	// @refer https://make.wordpress.org/core/2012/11/30/new-color-picker-in-wp-3-5/
        wp_enqueue_style( 'wp-color-picker' );	
	// Everything needed for media upload
        wp_enqueue_media();   
        add_thickbox();     
        include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
        wp_update_plugins();
        //wp_enqueue_script('thickbox', null, array('jquery'));
        wp_enqueue_script( 'wp-color-picker-alpha', PWAFORWP_PLUGIN_URL . 'assets/js/wp-color-picker-alpha.min.js', array( 'wp-color-picker' ), PWAFORWP_PLUGIN_VERSION, true );


        wp_enqueue_style( 'pwaforwp-main-css', PWAFORWP_PLUGIN_URL . 'assets/css/main-css'.$suffix.'.css',array(), PWAFORWP_PLUGIN_VERSION,'all' );      
		wp_style_add_data( 'pwaforwp-main-css', 'rtl', 'replace' );      
        // Main JS
        wp_enqueue_script('pwaforwp-zip-js', PWAFORWP_PLUGIN_URL . 'assets/js/jszip.min.js', array(), PWAFORWP_PLUGIN_VERSION, true);
        wp_register_script('pwaforwp-main-js', PWAFORWP_PLUGIN_URL . 'assets/js/main-script'.$suffix.'.js', array( 'wp-color-picker', 'wp-color-picker-alpha', 'plugin-install', 'wp-util', 'wp-a11y','updates' ), PWAFORWP_PLUGIN_VERSION, true);
        
        wp_enqueue_script('pwaforwp-main-js');
}
add_action( 'admin_enqueue_scripts', 'pwaforwp_enqueue_style_js' );



/**
 * This is a ajax handler function for sending email from user admin panel to us. 
 * @return type json string
 */
function pwaforwp_send_query_message(){   

		if ( ! current_user_can( pwaforwp_current_user_can() ) ) {
			return;
		}
        if ( ! isset( $_POST['pwaforwp_security_nonce'] ) ){
            return; 
        }
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
        if ( !wp_verify_nonce( $_POST['pwaforwp_security_nonce'], 'pwaforwp_ajax_check_nonce' ) ){
           return;  
        }
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
        $message    = sanitize_textarea_field($_POST['message']);
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotValidated    
        $customer_type    = sanitize_text_field($_POST['customer_type']);        
        $customer_type = empty($customer_type)? $customer_type : 'No';
        $message .= "<table>
        				<tr><td>".esc_html__('Are you existing Premium Customer?','pwa-for-wp')."</td><td>".$customer_type."</td></tr>
        				<tr><td>Plugin</td><td>".esc_html__('PWA for wp','pwa-for-wp')." </td></tr>
        				<tr><td>Version</td><td>".PWAFORWP_PLUGIN_VERSION."</td></tr>
        			</table>";
        $user       = wp_get_current_user();
        
        if($user){
            
            $user_data  = $user->data;        
            $user_email = $user_data->user_email;       
            //php mailer variables
            $to = 'team@magazine3.in';
            $subject = "PWA Customer Query";
            $headers = 'From: '. esc_attr($user_email) . "\r\n" .
            'Reply-To: ' . esc_attr($user_email) . "\r\n";
            // Load WP components, no themes.                      
            $sent = wp_mail($to, $subject, wp_strip_all_tags($message), $headers);        
            
            if($sent){
            echo wp_json_encode(array('status'=>'t'));            
            }else{
            echo wp_json_encode(array('status'=>'f'));            
            }
            
        }
                        
        wp_die();           
}

add_action('wp_ajax_pwaforwp_send_query_message', 'pwaforwp_send_query_message');

// Setting transient after expiry
add_action('wp_ajax_pwaforwp_license_transient', 'pwaforwp_license_transient');
function pwaforwp_license_transient(){
	if ( ! isset( $_POST['pwaforwp_security_nonce'] ) ){
		return; 
	}
	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
	if ( !wp_verify_nonce( $_POST['pwaforwp_security_nonce'], 'pwaforwp_ajax_check_nonce' ) ){
		return;  
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$transient_load =  'pwaforwp_addons_expired';
	$value_load =  'pwaforwp_addons_expired_value';
	$expiration_load =  3600 ;
	set_transient( $transient_load, $value_load, $expiration_load );
}
// Setting transient for 0-7 Days
add_action('wp_ajax_pwaforwp_license_transient_zto7', 'pwaforwp_license_transient_zto7');
function pwaforwp_license_transient_zto7(){
	if ( ! isset( $_POST['pwaforwp_security_nonce'] ) ){
		return; 
	}
	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
	if ( !wp_verify_nonce( $_POST['pwaforwp_security_nonce'], 'pwaforwp_ajax_check_nonce' ) ){
		return;  
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$transient_load =  'pwaforwp_addon_zto7';
	$value_load =  'pwaforwp_addon_zto7_value';
	$expiration_load =  86400 ;
	set_transient( $transient_load, $value_load, $expiration_load );
}

function pwaforwp_get_license_section_html($on){
            
                $settings = pwaforwp_defaultSettings();
    
                $license_key        = '';
                $license_status     = 'inactive';
                $license_status_msg = '';
                
                if(isset($settings[strtolower($on).'_addon_license_key'])){
                  $license_key =   $settings[strtolower($on).'_addon_license_key'];
                }
                
                if(isset($settings[strtolower($on).'_addon_license_key_status'])){
                  $license_status =   $settings[strtolower($on).'_addon_license_key_status'];
                }
                
                if(isset($settings[strtolower($on).'_addon_license_key_message'])){
                  $license_status_msg =   $settings[strtolower($on).'_addon_license_key_message'];
                }

                
                $license_user_name = !empty($settings[strtolower($on).'_addon_license_key_user_name']) ? $settings[strtolower($on).'_addon_license_key_user_name'] : NULL ;
                
                $license_download_id =   !empty($settings[strtolower($on).'_addon_license_key_download_id']) ? $settings[strtolower($on).'_addon_license_key_download_id'] : NULL ;                
                 
                $license_expires = !empty($settings[strtolower($on).'_addon_license_key_expires']) ? $settings[strtolower($on).'_addon_license_key_expires'] : NULL;

                
                $license_exp = !empty($settings[strtolower($on).'_addon_license_key_expires_normal']) ? $settings[strtolower($on).'_addon_license_key_expires_normal'] : NULL ;
                
                
                $response = '';                                                 
                $expire_msg_before = $single_expire_msg = $expire_msg = $license_expires_class = $alert_icon = $when_active = $final_otp = '';
                $response.= '<div class="pwaforwp-ext-active">';
                if( $license_status == 'active' ){

                    $license_Status_ = ''.esc_html__('Active', 'pwa-for-wp').'';
                    $license_Status_id = 'id="pwaforwp_lic_active"';

                    if ( $license_expires == 'Lifetime' ) {
                    $license_Status_ = ''.esc_html__('Active', 'pwa-for-wp').'';
                    $license_Status_id = 'id="pwaforwp_lic_lifetime"';
                }
                else if( $license_expires < 0 ){
                    $license_Status_ = ''.esc_html__('Expired', 'pwa-for-wp').'';
                    $license_Status_id = 'id="pwaforwp_lic_exp"';
                 }
                 else if($license_expires > 0){
                    $license_Status_ = ''.esc_html__('Active', 'pwa-for-wp').'';
                    $license_Status_id = 'id="pwaforwp_lic_active"';
                }

                $original_license = $license_key;
                    $license_name_ = strtolower($on);
                    $renew_url = "https://pwa-for-wp.com/order/?edd_license_key=".$license_key."&download_id=".$license_download_id."";
                    $user_refresh_addon = '<a addon-is-expired id="'.strtolower($license_name_).'" remaining_days_org='.$license_exp.' days_remaining="'.$license_expires.'" licensestatusinternal="'.$license_status.'" add-on="'.$license_name_.'" class="pwaforwp_user_refresh_single_addon" data-attr="'.$original_license.'" add-onname="pwaforwp_settings['.strtolower($license_name_).'_addon_license_key]">
                    <i addon-is-expired class="dashicons dashicons-update-alt" id="user_refresh_'.strtolower($license_name_).'"></i>'.esc_html__('Refresh','pwa-for-wp').'                    
                    </a>
                    <input type="hidden" license-status="inactive"  licensestatusinternal="'.$license_status.'" add-on="'.strtolower($license_name_).'" class="button button-default pwaforwp_license_activation '.$license_status.'mode '.strtolower($license_name_).''.strtolower($license_name_).'" id="pwaforwp_license_deactivation_internal">';
                if ( $license_expires == 'Lifetime' ) {
                    $expire_msg_before = '<span class="pwaforwp_before_msg_active">'.esc_html__('License is', 'pwa-for-wp').'</span>';
                    $single_expire_msg = " ".esc_html__('Valid for Lifetime', 'pwa-for-wp')." ";
                    $renew_text = esc_html__('Renew','pwa-for-wp');
                    $license_expires_class = "pwaforwp_lifetime_";
                }

                else if( $license_expires >= 0 && $license_expires <= 7 ){
                    $expire_msg_before = '<span class="before_msg">'.esc_html__('Your', 'pwa-for-wp').' <span class="pwaforwp_zero_to_30">'.esc_html__('License is', 'pwa-for-wp').'</span></span>';
                    $license_expires_class = "zero2thirty";
                    $single_expire_msg = '<span class="pwaforwp-addon-alert">'.esc_html__('expiring in', 'pwa-for-wp').' '.$license_expires .' '.esc_html__('days', 'pwa-for-wp').'</span>';
                    $renew_text = esc_html__('Renew','pwa-for-wp');
                    $alert_icon = '<span class="pwaforwp_addon_icon dashicons dashicons-warning pwaforwp_single_addon_warning"></span>';
                 }
                 else if( $license_expires >=0 && $license_expires <=30 ){
                    $expire_msg_before = '<span class="before_msg">'.esc_html__('Your', 'pwa-for-wp').' <span class="pwaforwp_zero_to_30">'.esc_html__('License is', 'pwa-for-wp').'</span></span>';
                    $license_expires_class = "zero2thirty";
                    $single_expire_msg = '<span class="pwaforwp-addon-alert">'.esc_html__('expiring in', 'pwa-for-wp').' '.$license_expires .' '.esc_html__('days', 'pwa-for-wp').'</span>';
                    $renew_text = esc_html__('Renew','pwa-for-wp');
                    $alert_icon = '<span class="pwaforwp_addon_icon dashicons dashicons-warning pwaforwp_single_addon_warning"></span>';
                }
                else if( $license_expires < 0 ){
                    $expire_msg_before = '<span class="before_msg">'.esc_html__('Your', 'pwa-for-wp').' <span class="pwaforwp_less_than_zero">'.esc_html__('License is', 'pwa-for-wp').'</span></span>';
                    $single_expire_msg = " ".esc_html__('Expired', 'pwa-for-wp')." ";
                    $renew_text = esc_html__('Renew','pwa-for-wp');
                    $license_expires_class = "expire_msg";
                 }
                else{
                    $expire_msg_before = '<span class="pwaforwp-addon-active"></span>';
                    $single_expire_msg = " ".$license_expires ." ".esc_html__("days remaning", "pwa-for-wp")." ";
                    $license_expires_class = "lic_is_active";
                    $renew_text = esc_html__('Renew License','pwa-for-wp');
                }

                if ( !empty($license_expires) ) {

                $when_active = '<span class="pwaforwp-license-tenure" days_remaining='.$license_expires.'>'.$alert_icon.' '.$expire_msg_before.'
                <span expired-days-data="'.$license_expires.'" class='.$license_expires_class.'>'.$single_expire_msg.'
                <a target="blank" class="pwaforwp-renewal-license" href="'.$renew_url.'">
                <span class="pwaforwp-renew-lic">'.esc_html( $renew_text).'</span></a>'.$user_refresh_addon.'
                </span>
                </span>';
            }

                $renew_url = "https://pwa-for-wp.com/order/?edd_license_key=".$license_key."&download_id=".$license_download_id."";

				 $response.= '<div class="pwaforwp-sts-active-main '.strtolower($on).'_addon "><label class="pwaforwp-sts-txt '.$license_status.'">'.esc_html__('Status', 'pwa-for-wp').' :<span class="addon-activated_'.strtolower($on).'" '.$license_Status_id.'>'.$license_Status_.'</span>
                <input type="password" class="license_key_input_'.$license_Status_.' '.$on.'_'.$license_Status_.'"" placeholder="'.esc_attr__('Enter License Key', 'pwa-for-wp').'" id="'.strtolower($on).'_addon_license_key" name="pwaforwp_settings['.strtolower($on).'_addon_license_key]" value="'.esc_attr($license_key).'">
                <input type="hidden" id="'.strtolower($on).'_addon_license_key_status" name="pwaforwp_settings['.strtolower($on).'_addon_license_key_status]" value="'.esc_attr($license_status).'">
                <input type="hidden" id="'.strtolower($on).'_addon_license_key_user_name" name="pwaforwp_settings['.strtolower($on).'_addon_license_key_user_name]" value="'.esc_attr($license_user_name).'">
                <input type="hidden" id="'.strtolower($on).'_addon_license_key_expires" name="pwaforwp_settings['.strtolower($on).'_addon_license_key_expires]" value="'.esc_attr($license_expires).'">
                <input type="hidden" id="'.strtolower($on).'_addon_license_key_expires_normal" name="pwaforwp_settings['.strtolower($on).'_addon_license_key_expires_normal]" value="'.esc_attr($license_exp).'">

                <span class="lic_btn_inactive_'.strtolower($on).'">
                <a license-status="inactive" add-on="'.strtolower($on).'" class="button button-default pwaforwp_license_activation">'.esc_html__('Deactivate', 'pwa-for-wp').'</a>
                </span>

                </label></div>';

                $response .=  $when_active ;
                    
                }
                else{
                    if( $license_expires < 0 ){
                    $license_Status_ = ''.esc_html__('Expired', 'pwa-for-wp').'';
                    $license_Status_id = 'id="pwaforwp_lic_exp"';
                 }else if($license_expires > 0){
                    $license_Status_ = ''.esc_html__('Inactive', 'pwa-for-wp').'';
                    $license_Status_id = 'id="pwaforwp_lic_inactive"';
                }else{
                    $license_Status_ = ''.esc_html__('Inactive', 'pwa-for-wp').'';
                    $license_Status_id = 'id="pwaforwp_lic_inactive"';
                }
                 
                if (!empty($expire_msg_before) && !empty($single_expire_msg) && !empty($license_expires_class) && !empty($license_expires) ) {
                    $original_license = $license_key;
                    $license_name_ = strtolower($on);
                    $renew_url = "https://pwa-for-wp.com/order/?edd_license_key=".$license_key."&download_id=".$license_download_id."";
                    $user_refresh_addon = '<a addon-is-expired remaining_days_org='.$license_exp.' id="'.strtolower($license_name_).'" days_remaining="'.$license_expires.'" licensestatusinternal="'.$license_status.'" add-on="'.$license_name_.'" class="pwaforwp_user_refresh_single_addon" data-attr="'.$original_license.'" add-onname="pwaforwp_settings['.strtolower($license_name_).'_addon_license_key]">
                    <i addon-is-expired class="dashicons dashicons-update-alt" id="user_refresh_'.strtolower($license_name_).'"></i>'.esc_html__('Refresh','pwa-for-wp').'
                    </a>
                    <input type="hidden" license-status="inactive"  licensestatusinternal="'.$license_status.'" add-on="'.strtolower($license_name_).'" class="button button-default pwaforwp_license_activation '.$license_status.'mode '.strtolower($license_name_).''.strtolower($license_name_).'" id="pwaforwp_license_deactivation_internal">';

                    $final_otp = '';
                if( $license_expires < 0 ){
                    $expire_msg_before = '<span class="expired_before_msg">'.esc_html__('Your', 'pwa-for-wp').' <span class="pwaforwp_less_than_zero">'.esc_html__('License is', 'pwa-for-wp').'</span></span>';
                    $single_expire_msg = " ".esc_html__('Expired', 'pwa-for-wp')." ";
                    $license_expires_class = "expire_msg";
                    $final_otp = '<span class="expired-pwaforwp-license-tenure" days_remaining='.$license_expires.'>'.$alert_icon.' '.$expire_msg_before.'
                <span expired-days-data="'.$license_expires.'" class='.$license_expires_class.'>'.$single_expire_msg.'
                <a target="blank" class="pwaforwp-renewal-license" href="'.esc_url($renew_url).'">
                <span class="pwaforwp-renew-lic">'.esc_html__('Renew', 'pwa-for-wp').'</span></a>'.$user_refresh_addon.'
                </span>
                </span>';
                 }
            }
                	
                	$response.= '<div class="pwaforwp-sts-active-main '.strtolower($on).'_addon "><label class="pwaforwp-sts-txt '.$license_status.'">Status :<span class="addon-inactive_'.strtolower($on).'" '.$license_Status_id.'>'.$license_Status_.'</span>
                	<input type="password" class="license_key_input_'.$license_Status_.' '.$on.'_'.$license_Status_.'"" placeholder="'.esc_attr__('Enter License Key', 'pwa-for-wp').'" id="'.strtolower($on).'_addon_license_key" name="pwaforwp_settings['.strtolower($on).'_addon_license_key]" value="'.esc_attr($license_key).'">
                    <span class="lic_btn_active_'.strtolower($on).'">
                	  <a license-status="active" add-on="'.strtolower($on).'" class="button button-default pwaforwp_license_activation">'.esc_html__('Activate', 'pwa-for-wp').'</a>
                      </span>
					  <p class="message_addon-inactive_'.strtolower($on).'" '.$license_Status_id.'></p>

                    </label></div>';
                    $response .=  $final_otp ;
                    
                }                
                                                
                 $response.= '</div>';               
                
                return $response;
    
}

function pwaforwp_license_status_check(){  
    
        if ( ! current_user_can( 'manage_options' ) ) {
             return;
        }
        if ( ! isset( $_POST['pwaforwp_security_nonce'] ) ){
             return; 
        }
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
        if ( !wp_verify_nonce( $_POST['pwaforwp_security_nonce'], 'pwaforwp_ajax_check_nonce' ) ){
             return;  
        }    
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
        $add_on           = sanitize_text_field($_POST['add_on']);
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
        $license_status   = sanitize_text_field($_POST['license_status']);
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
        $license_key      = sanitize_text_field($_POST['license_key']);
        
        if($add_on && $license_status && $license_key){
            
          $result = pwaforwp_license_status($add_on, $license_status, $license_key);
          
          echo wp_json_encode($result);
                        
        }          
                        
        wp_die();           
}

add_action('wp_ajax_pwaforwp_license_status_check', 'pwaforwp_license_status_check');

function pwaforwp_license_status($add_on, $license_status, $license_key){
                
                $item_name = pwaforwp_list_addons();
                                                                                    
                $edd_action = '';
                if($license_status =='active'){
                   $edd_action = 'activate_license'; 
                }
                
                if($license_status =='inactive'){
                   $edd_action = 'deactivate_license'; 
                }
            // data to send in our API request
		$api_params = array(
			'edd_action' => $edd_action,
			'license'    => $license_key,
			'item_name'  => $item_name[strtolower($add_on)]['p-title'],
			'author'     => 'Magazine3',			
			'url'        => home_url(),
			'beta'       => false,
		);
                
		$message        = '';
		$current_status = '';
		$response       = @wp_remote_post( PWAFORWP_EDD_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );
                // make sure the response came back okay
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			if(!empty($response->get_error_message())){
				$error_message = strtolower($response->get_error_message());
				$error_pos = strpos($error_message, 'operation timed out');
				if($error_pos !== false){
					$message = __('Request timed out, please try again','pwa-for-wp');
				}else{
					$message = esc_html($response->get_error_message());
				}
			}
			if(empty($message)){ 
					 $message =   __( 'An error occurred, please try again.','pwa-for-wp');
			}
			// $message =  ( is_wp_error( $response ) && ! empty( $response->get_error_message() ) ) ? $response->get_error_message() : __( 'An error occurred, please try again.' );
		} else {
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );
                        
			if ( false === $license_data->success ) {
                            
                                $current_status = $license_data->error;
                                
				switch( $license_data->error ) {
					case 'expired' :
					$addon_name = $license_data->item_name;
					if ($addon_name == 'Call to Action for PWA') {
						$addon_name = 'ctafp';
					}
					if ($addon_name == 'Loading Icon Library for PWA') {
						$addon_name = 'lilfp';
					}
                    if ($addon_name == 'Data Analytics for PWA') {
                        $addon_name = 'dafp';
                    }
                    if ($addon_name == 'Pull to Refresh for PWA') {
                        $addon_name = 'ptrfp';
                    }
                    if ($addon_name == 'Scroll Progress Bar for PWA') {
                        $addon_name = 'spbfp';
                    }
                    if ($addon_name == 'PWA to APK Plugin') {
                        $addon_name = 'ptafp';
                    }
                    if ($addon_name == 'Offline Forms for PWA for WP') {
                        $addon_name = 'ofpwa';
                    }
                    if ($addon_name == 'BuddyPress for PWAforWP') {
                        $addon_name = 'bnpwa';
                    }
                    if ($addon_name == 'Quick Action for PWA') {
                        $addon_name = 'qafp';
                    }
                    if ($addon_name == 'QR Code for PWA') {
                        $addon_name = 'qrcode';
                    }
                    
					$license[strtolower($add_on).'_addon_license_key_status']  = 'inactive';
               $license[strtolower($add_on).'_addon_license_key']         = $license_key;
                $license[strtolower($add_on).'_addon_license_key_message'] = 'inactive'; 
                $license[strtolower($add_on).'_addon_name'] = $addon_name; 
                if ($license_data) { 
              // Get UserName 
				$fname = $license_data->customer_name;
				$fname = substr($fname, 0, strpos($fname, ' ')); 
				$check_for_Caps = ctype_upper($fname); 
				if ( $check_for_Caps == 1 ) {
					$fname =  strtolower($fname);
					$fname =  ucwords($fname);
				} else {
					$fname =  ucwords($fname);
				} 
              // Get Expiring Date 
				$license_exp = gmdate('Y-m-d', strtotime($license_data->expires)); 
				$license_info_lifetime = $license_data->expires; 
				$today = gmdate('Y-m-d');
				$exp_date =$license_exp; 
				$date1 = date_create($today);
                $date2 = date_create($exp_date);
				$diff = date_diff($date1,$date2);
				$days = $diff->format("%a");
				if( $license_info_lifetime == 'lifetime' ){
                    $days = 'Lifetime';
					if ($days == 'Lifetime') {
						$expire_msg = esc_html__(" Your License is Valid for Lifetime ",'pwa-for-wp');
					}
				} elseif($today > $exp_date){
					$days = -$days;
				} 
              // Get Download_ID 
				$download_id = $license_data->payment_id;
			} 

				$license[strtolower($add_on).'_addon_license_key_user_name'] = $fname; 
				$license[strtolower($add_on).'_addon_license_key_expires'] = $days;
				$license[strtolower($add_on).'_addon_license_key_expires_normal'] = $license_exp;  
				$license[strtolower($add_on).'_addon_license_key_download_id'] = $download_id; 
				$current_status = 'active'; 
				$message = esc_html__( 'Activated', 'pwa-for-wp'); 
				$days_remaining = $days; 
				$username = $fname;
			   			/* translators: %s: date */
						$message = sprintf(	__( 'Your license key expired on %s.', 'pwa-for-wp' ),
							date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
						);
						break;
					case 'revoked' :
						$message = esc_html__( 'Your license key has been disabled.', 'pwa-for-wp'	);
						break;
					case 'missing' :
						$message = esc_html__( 'Invalid license.', 'pwa-for-wp'	);
						break;
					case 'invalid' :
					case 'site_inactive' :
						$message = esc_html__( 'Your license is not active for this URL.', 'pwa-for-wp'	);
						break;
					case 'item_name_mismatch' :
						$message = esc_html__( 'This appears to be an invalid license key.', 'pwa-for-wp'	);
						break;
					case 'no_activations_left':
						$message = esc_html__( 'Your license key has reached its activation limit.', 'pwa-for-wp'	);
						break;
					default :
						$message = esc_html__( 'An error occurred, please try again.', 'pwa-for-wp'	);
						break;
				}
			}
		}
                if($message){
                    
                        $license[strtolower($add_on).'_addon_license_key_status'] = $current_status;
                        $license[strtolower($add_on).'_addon_license_key']        = $license_key;
                        $license[strtolower($add_on).'_addon_license_key_message']= $message;
                    
                }else{

                    if($license_status == 'active'){
                                                                                         
                        $license[strtolower($add_on).'_addon_license_key_status']  = 'active';
                        $license[strtolower($add_on).'_addon_license_key']         = $license_key;
                        $license[strtolower($add_on).'_addon_license_key_message'] = 'active'; 
                        $license[strtolower($add_on).'_addon_name'] = $addon_name; 
                        
                        if ($license_data) {
                          // Get UserName
                        $fname = $license_data->customer_name;
                        $addon_name = $license_data->item_name;
    					if ($addon_name == 'Call to Action for PWA') {
                        $addon_name = 'ctafp';
                    }
                    if ($addon_name == 'Loading Icon Library for PWA') {
                        $addon_name = 'lilfp';
                    }
                    if ($addon_name == 'Data Analytics for PWA') {
                        $addon_name = 'dafp';
                    }
                    if ($addon_name == 'Pull to Refresh for PWA') {
                        $addon_name = 'ptrfp';
                    }
                    if ($addon_name == 'Scroll Progress Bar for PWA') {
                        $addon_name = 'spbfp';
                    }
                    if ($addon_name == 'PWA to APK Plugin') {
                        $addon_name = 'ptafp';
                    }
                    if ($addon_name == 'Offline Forms for PWA for WP') {
                        $addon_name = 'ofpwa';
                    }
                    if ($addon_name == 'BuddyPress for PWAforWP') {
                        $addon_name = 'bnpwa';
                    }
                    if ($addon_name == 'Quick Action for PWA') {
                        $addon_name = 'qafp';
                    }
					if ($addon_name == 'QR Code for PWA') {
                        $addon_name = 'qrcode';
                    }
                        $fname = substr($fname, 0, strpos($fname, ' '));
                        $check_for_Caps = ctype_upper($fname);
                        if ( $check_for_Caps == 1 ) {
                        $fname =  strtolower($fname);
                        $fname =  ucwords($fname);
                        }
                        else
                          {
                            $fname =  ucwords($fname);
                          }

                          // Get Expiring Date
                          $license_exp = gmdate('Y-m-d', strtotime($license_data->expires));
                          $license_info_lifetime = $license_data->expires;
                          $today = gmdate('Y-m-d');
                          $exp_date =$license_exp;
                          $date1 = date_create($today);
                          $date2 = date_create($exp_date);
                          $diff = date_diff($date1,$date2);
                          $days = $diff->format("%a");
                          if( $license_info_lifetime == 'lifetime' ){
                            $days = 'Lifetime';
                            if ($days == 'Lifetime') {
                            $expire_msg = esc_html__('Your License is Valid for Lifetime','pwa-for-wp');
                          }
                        }
                        elseif($today > $exp_date){
                          $days = -$days;
                        }
                          // Get Download_ID
                          $download_id = $license_data->payment_id;
                        }

                        $license[strtolower($add_on).'_addon_license_key_user_name'] = $fname;

                        $license[strtolower($add_on).'_addon_license_key_expires'] = $days;
                        $license[strtolower($add_on).'_addon_license_key_expires_normal'] = $license_exp; 
                        
                        $license[strtolower($add_on).'_addon_license_key_download_id'] = $download_id;

                        $current_status = 'active';
                        $message = esc_html__('Activated','pwa-for-wp');
                        $days_remaining = $days;
                        $username = $fname;
                    }
                    
                    if($license_status == 'inactive'){

                        $license[strtolower($add_on).'_addon_license_key_status']  = 'deactivated';
                        $license[strtolower($add_on).'_addon_license_key']         = $license_key;
                        $license[strtolower($add_on).'_addon_license_key_message'] = 'Deactivated';

                        $license[strtolower($add_on).'_addon_name'] = $addon_name; 

                        if ($license_data) {
                          // Get UserName
                        $fname = $license_data->customer_name;
                        $fname = substr($fname, 0, strpos($fname, ' '));
                        $check_for_Caps = ctype_upper($fname);
                        if ( $check_for_Caps == 1 ) {
                        $fname =  strtolower($fname);
                        $fname =  ucwords($fname);
                        }
                        else
                          {
                            $fname =  ucwords($fname);
                          }

                          // Get Expiring Date
                          $get_options   = get_option('pwaforwp_settings');
						  $days = $get_options[$add_on.'_addon_license_key_expires'];
                          // Get Download_ID
                          $download_id = $license_data->payment_id;
                        }

                        $license[strtolower($add_on).'_addon_license_key_user_name'] = $fname;

                        $license[strtolower($add_on).'_addon_license_key_expires'] = $days;
                        $license[strtolower($add_on).'_addon_license_key_expires_normal'] = $license_exp; 
                        
                        $license[strtolower($add_on).'_addon_license_key_download_id'] = $download_id;

                        $current_status = 'inactive';
                        $message = esc_html__('deactivated','pwa-for-wp');
                        $days_remaining = $days;
                        $username = $fname;
                        $addon_name = $addon_name; 
                    }
                    
                }
                
                $get_options   = get_option('pwaforwp_settings');
                $merge_options = array_merge($get_options, $license);
                update_option('pwaforwp_settings', $merge_options);  
                
                return array('status'=> $current_status, 'message'=> $message, 'days_remaining' => $days_remaining, 'username' => $fname ,'addon_name' => $addon_name  );
                                                                
}

add_action("pwaforwp_loading_icon_libraries", 'pwaforwp_show_premium_options',10, 1);
function pwaforwp_show_premium_options($section){
	add_settings_field(
			'pwaforwp_loading_icon_selector',							// ID
			esc_html__('Loader icon selector', 'pwa-for-wp'),	// Title
			'pwaforwp_loading_icon_premium_callback',							// CB
			$section,						// Page slug
			$section						// Settings Section ID
		);
}
function pwaforwp_loading_icon_premium_callback(){
	echo sprintf("%s <a target='_blank' href='%s'>%s</a>",
			esc_html__('This feature requires', 'pwa-for-wp'),
			esc_url("https://pwa-for-wp.com/extensions/loading-icon-library-for-pwa/",'pwa-for-wp'),
			esc_html__("Loading Icon Library for PWA extension", 'pwa-for-wp')

		);
}

function pwaforwp_features_settings(){
	$settings = pwaforwp_defaultSettings();
	$addonLists = pwaforwp_list_addons();
	$allplugins = get_transient( 'plugin_slugs');
	if($allplugins){
		$allplugins = array_flip($allplugins);
	}
	$feturesArray = array(
				'notification' => array(
									'enable_field' => esc_html__('notification_feature', 'pwa-for-wp'),
									'section_name' => esc_html__('pwaforwp_push_notification_section', 'pwa-for-wp'),
									'setting_title' =>  esc_html__('Push notification', 'pwa-for-wp'),
									'tooltip_option' => esc_html__('send notification to users', 'pwa-for-wp'),
									'tooltip_link'	=> 'https://pwa-for-wp.com/docs/article/how-to-use-push-notifications-in-pwa/'
									),
				'precaching' => array(
									'enable_field' => esc_html__('precaching_feature','pwa-for-wp'),
									'section_name' => esc_html__('pwaforwp_precaching_setting_section', 'pwa-for-wp'),
									'setting_title' =>  esc_html__('Pre Caching', 'pwa-for-wp'),
									'tooltip_option' => esc_html__('Pre-Cache pages and posts on page load', 'pwa-for-wp'),
                                    'tooltip_link'  => 'https://pwa-for-wp.com/docs/article/setting-up-precaching-in-pwa/',
									),
				'addtohomebanner' => array(
									'enable_field' => esc_html__('addtohomebanner_feature', 'pwa-for-wp'),
									'section_name' => esc_html__('pwaforwp_addtohomescreen_setting_section', 'pwa-for-wp'),
									'setting_title' =>  esc_html__('Custom Add To Home Banner', 'pwa-for-wp'),
									'tooltip_option' => esc_html__('Add a banner website for PWA app install', 'pwa-for-wp'),
                                    'tooltip_link'  => 'https://pwa-for-wp.com/docs/article/how-to-add-custom-add-to-homescreen-banner/',
									),
				'utmtracking' => array(
									'enable_field' => esc_html__('utmtracking_feature', 'pwa-for-wp'),
									'section_name' => esc_html__('pwaforwp_utmtracking_setting_section', 'pwa-for-wp'),
									'setting_title' => esc_html__('UTM Tracking', 'pwa-for-wp'),
									'tooltip_option'=> esc_html__('Urchin Traffic Monitor Tracking', 'pwa-for-wp'),
									'tooltip_link'	=> 'https://pwa-for-wp.com/docs/article/how-to-add-utm-tracking-in-pwa/'
									),
				'loader' => array(
									'enable_field' => esc_html__('loader_feature', 'pwa-for-wp'),
									'section_name' => esc_html__('pwaforwp_loaders_setting_section', 'pwa-for-wp'),
									'setting_title' => esc_html__('Loader', 'pwa-for-wp'),
									'tooltip_option'=> esc_html__('Loader for complete website', 'pwa-for-wp'),
									'tooltip_link'	=> 'https://pwa-for-wp.com/docs/article/how-to-use-loading-icon-library-for-pwa/'
									),
				'urlhandler' => array(
										'enable_field' => esc_html__('urlhandler_feature', 'pwa-for-wp'),
										'section_name' => esc_html__('pwaforwp_urlhandler_setting_section', 'pwa-for-wp'),
										'setting_title' => esc_html__('URL Handlers', 'pwa-for-wp'),
										'tooltip_option'=> esc_html__('PWA as URL Handlers allows apps like music.example.com to register themselves as URL handlers so that links from outside of the PWA', 'pwa-for-wp'),
										'tooltip_link'  => 'https://pwa-for-wp.com/docs/article/how-to-use-urlhandler-for-pwa/'
										),
				'visibility' => array(
										'enable_field' => esc_html__('visibility_feature', 'pwa-for-wp'),
										'section_name' => esc_html__('pwaforwp_visibility_setting_section', 'pwa-for-wp'),
										'setting_title' => esc_html__('Visibility', 'pwa-for-wp'),
										'tooltip_option' => esc_html__('PWA visibility allows apps to control the visibility of the APP on specific pages, posts, and post-types', 'pwa-for-wp'),
										'tooltip_link'  => 'https://pwa-for-wp.com/docs/article/setting-up-visibility-in-pwa/',
										),
				'calltoaction'	=> array(
									'enable_field' => esc_html__('call_to_action', 'pwa-for-wp'),
									'section_name' => esc_html__('pwaforwp_call_to_action_setting_section', 'pwa-for-wp'),
									'setting_title' => esc_html__('Call To Action', 'pwa-for-wp'),
									'is_premium'	=> true,
									'pro_link'		=> $addonLists['ctafp']['p-url'],
									'pro_active'    => (is_plugin_active($addonLists['ctafp']['p-slug'])? 1: 0),
									'pro_deactive'    => (!is_plugin_active($addonLists['ctafp']['p-slug']) && file_exists(PWAFORWP_PLUGIN_DIR."/../".$addonLists['ctafp']['p-slug'])? 1: 0),
									'slug' => 'ctafp',
									'tooltip_option'=> esc_html__('CTA feature for PWA', 'pwa-for-wp'),
									'tooltip_link'	=> 'https://pwa-for-wp.com/docs/article/how-to-use-call-to-action-cta-in-pwa/'
									),
				'rewardspwa' => array(
									'enable_field' => esc_html__('rewardspwa_feature', 'pwa-for-wp'),
									'section_name' => esc_html__('pwaforwp_rewardspwa_setting_section', 'pwa-for-wp'),
									'setting_title' => esc_html__('Rewards on APP Install', 'pwa-for-wp'),
									'is_premium'    => true,
									'pro_link'      => $addonLists['ropi']['p-url'],
									'pro_active'    => (is_plugin_active($addonLists['ropi']['p-slug'])? 1: 0),
                                    'pro_deactive'    => (!is_plugin_active($addonLists['ropi']['p-slug']) && file_exists(PWAFORWP_PLUGIN_DIR."/../".$addonLists['ropi']['p-slug'])? 1: 0),
                                    'slug' => 'mcfp',
									'tooltip_option' => esc_html__('Give Rewards to the customers', 'pwa-for-wp'),
									'tooltip_link'	=> 'https://pwa-for-wp.com/docs/article/how-to-use-rewards-on-pwa-install/'
									),
				'dataAnalytics' => array(
									'enable_field' => esc_html__('data_analytics', 'pwa-for-wp'),
									'section_name' => esc_html__('pwaforwp_data_analytics_setting_section', 'pwa-for-wp'),
									'setting_title' => esc_html__('Data Analytics', 'pwa-for-wp'),
									'is_premium'	=> true,
									'pro_link'		=> $addonLists['dafp']['p-url'],
									'pro_active'    => (is_plugin_active($addonLists['dafp']['p-slug'])? 1: 0),
									'pro_deactive'    => (!is_plugin_active($addonLists['dafp']['p-slug']) && file_exists(PWAFORWP_PLUGIN_DIR."/../".$addonLists['dafp']['p-slug'])? 1: 0),
									'slug' => 'dafp',
									'tooltip_option'=> esc_html__('Analytics for the number of people who are installing PWA', 'pwa-for-wp'),
									'tooltip_link'	=> 'https://pwa-for-wp.com/docs/article/how-to-use-data-analytics-for-pwa/'
									),
				'pulltorefresh' => array(
                                    'enable_field' => esc_html__('pull_to_refresh', 'pwa-for-wp'),
                                    'section_name' => esc_html__('pwaforwp_pull_to_refresh_setting_section', 'pwa-for-wp'),
                                    'setting_title' => esc_html__('Pull To Refresh', 'pwa-for-wp'),
                                    'is_premium'    => true,
                                    'pro_link'      => $addonLists['ptrfp']['p-url'],
                                    'pro_active'    => (is_plugin_active($addonLists['ptrfp']['p-slug'])? 1: 0),
                                    'pro_deactive'    => (!is_plugin_active($addonLists['ptrfp']['p-slug']) && file_exists(PWAFORWP_PLUGIN_DIR."/../".$addonLists['ptrfp']['p-slug'])? 1: 0),
                                    'slug' => 'ptrfp',
                                    'tooltip_option'=> esc_html__('Refresh the PWA APP page', 'pwa-for-wp'),
									'tooltip_link'	=> 'https://pwa-for-wp.com/docs/article/how-to-use-data-analytics-for-pwa/'
                                    ),
				'progressbar' => array(
                                    'enable_field' => esc_html__('scroll_progress_bar', 'pwa-for-wp'),
                                    'section_name' => esc_html__('pwaforwp_scroll_progress_bar_setting_section', 'pwa-for-wp'),
                                    'setting_title' => esc_html__('Scroll Progress Bar', 'pwa-for-wp'),
                                    'is_premium'    => true,
                                    'pro_link'      => $addonLists['spbfp']['p-url'],
                                    'pro_active'    => (is_plugin_active($addonLists['spbfp']['p-slug'])? 1: 0),
                                    'pro_deactive'    => (!is_plugin_active($addonLists['spbfp']['p-slug']) && file_exists(PWAFORWP_PLUGIN_DIR."/../".$addonLists['spbfp']['p-slug'])? 1: 0),
                                    'slug' => 'spbfp',
                                    'tooltip_option'=> esc_html__('Show Scroll progress bar', 'pwa-for-wp'),
									'tooltip_link'	=> 'https://pwa-for-wp.com/docs/article/scroll-progress-bar-for-pwa/'
                                    ),
				'pwatoapkplugin' => array(
                                    'enable_field' => esc_html__('pwa_to_apk_plugin', 'pwa-for-wp'),
                                    'section_name' => esc_html__('pwaforwp_pwa_to_apk_plugin_setting_section', 'pwa-for-wp'),
                                    'setting_title' => esc_html__('PWA to Android APP (APK)', 'pwa-for-wp'),
                                    'is_premium'    => true,
                                    'pro_link'      => $addonLists['ptafp']['p-url'],
                                    'pro_active'    => (is_plugin_active($addonLists['ptafp']['p-slug'])? 1: 0),
                                    'pro_deactive'    => (!is_plugin_active($addonLists['ptafp']['p-slug']) && file_exists(PWAFORWP_PLUGIN_DIR."/../".$addonLists['ptafp']['p-slug'])? 1: 0),
                                    'slug' => $addonLists['ptafp']['p-slug'],
                                    'tooltip_option'=> esc_html__('Generate APK for website', 'pwa-for-wp'),
									'tooltip_link'	=> 'https://pwa-for-wp.com/docs/article/how-to-use-pwa-to-apk-plugin/'
                                    ),
				'offlineforms' => array(
                                    'enable_field' => esc_html__('offline_forms', 'pwa-for-wp'),
                                    'section_name' => esc_html__('pwaforwp_offline_forms_setting_section', 'pwa-for-wp'),
                                    'setting_title' => esc_html__('Offline Forms', 'pwa-for-wp'),
                                    'is_premium'    => true,
                                    'pro_link'      => $addonLists['ofpwa']['p-url'],
                                    'pro_active'    => (is_plugin_active($addonLists['ofpwa']['p-slug'])? 1: 0),
                                    'pro_deactive'    => (!is_plugin_active($addonLists['ofpwa']['p-slug']) && file_exists(PWAFORWP_PLUGIN_DIR."/../".$addonLists['ofpwa']['p-slug'])? 1: 0),
                                    'slug' => 'ofpwa',
                                    'tooltip_option'=> esc_html__('Support forms to work on offline mode', 'pwa-for-wp'),
									'tooltip_link'	=> 'https://pwa-for-wp.com/docs/article/how-to-use-offline-forms/'
                                    ),
				'autosaveforms' => array(
                                    'enable_field' => esc_html__('autosave_forms', 'pwa-for-wp'),
                                    'section_name' => esc_html__('pwaforwp_autosave_forms_setting_section', 'pwa-for-wp'),
                                    'setting_title' => esc_html__('Auto Save Forms', 'pwa-for-wp'),
                                    'is_premium'    => true,
                                    'pro_link'      => $addonLists['ofpwa']['p-url'],
                                    'pro_active'    => (is_plugin_active($addonLists['ofpwa']['p-slug'])? 1: 0),
                                    'pro_deactive'    => (!is_plugin_active($addonLists['ofpwa']['p-slug']) && file_exists(PWAFORWP_PLUGIN_DIR."/../".$addonLists['ofpwa']['p-slug']) ? 1: 0),
                                    'slug' => 'ofpwa',
                                    'tooltip_option'=> esc_html__('It auto saves the data on the fly', 'pwa-for-wp'),
									'tooltip_link'	=> 'https://pwa-for-wp.com/docs/article/how-to-use-auto-save-forms/'
                                    ),
				'buddypress_notification' => array(
                                    'enable_field' => esc_html__('buddypress_notification', 'pwa-for-wp'),
                                    'section_name' => esc_html__('pwaforwp_buddypress_setting_section', 'pwa-for-wp'),
                                    'setting_title' => esc_html__('Buddypress', 'pwa-for-wp'),
                                    'is_premium'    => true,
                                    'pro_link'      => $addonLists['bnpwa']['p-url'],
                                    'pro_active'    => (is_plugin_active($addonLists['bnpwa']['p-slug'])? 1: 0),
                                    'pro_deactive'    => (!is_plugin_active($addonLists['bnpwa']['p-slug']) && file_exists(PWAFORWP_PLUGIN_DIR."/../".$addonLists['bnpwa']['p-slug'])? 1: 0),
                                    'slug' => 'bnpwa',
                                    'tooltip_option'=> esc_html__('Support buddypress push notification with PWA and push notification', 'pwa-for-wp'),
                                    'tooltip_link' => 'https://pwa-for-wp.com/docs/article/how-to-use-buddypress-for-pwaforwp/'
                                    ),
				'quickaction' => array(
									'enable_field' => esc_html__('quick_action', 'pwa-for-wp'),
									'section_name' => esc_html__('pwaforwp_quick_action_setting_section', 'pwa-for-wp'),
									'setting_title' => esc_html__('Quick Action', 'pwa-for-wp'),
									'is_premium'    => true,
									'pro_link'      => $addonLists['qafp']['p-url'],
									'pro_active'    => (is_plugin_active($addonLists['qafp']['p-slug'])? 1: 0),
                                    'pro_deactive'    => (!is_plugin_active($addonLists['qafp']['p-slug']) && file_exists(PWAFORWP_PLUGIN_DIR."/../".$addonLists['qafp']['p-slug'])? 1: 0),
                                    'slug' => 'qafp',
									'tooltip_option' => esc_html__('Quick action help users give shortcut link, common or recommended pages with in your web app', 'pwa-for-wp'),
									'tooltip_link'	=> 'https://pwa-for-wp.com/docs/article/how-to-use-quick-action-for-pwa-for-wp/'
									),
				'navigationbar' => array(
									'enable_field' => esc_html__('navigation_bar','pwa-for-wp'),
									'section_name' => esc_html__('pwaforwp_navigation_bar_setting_section', 'pwa-for-wp'),
									'setting_title' => esc_html__('Navigation Bar', 'pwa-for-wp'),
									'is_premium'    => true,
									'pro_link'      => $addonLists['nbfp']['p-url'],
									'pro_active'    => (is_plugin_active($addonLists['nbfp']['p-slug'])? 1: 0),
                                    'pro_deactive'    => (!is_plugin_active($addonLists['nbfp']['p-slug']) && file_exists(PWAFORWP_PLUGIN_DIR."/../".$addonLists['nbfp']['p-slug'])? 1: 0),
                                    'slug' => 'nbfp',
									'tooltip_option' => esc_html__('Top-level pages that need to be accessible from anywhere in the app', 'pwa-for-wp'),
									'tooltip_link'	=> 'https://pwa-for-wp.com/docs/article/how-to-use-navigation-bar-for-pwa-addon/'
									),
				'multilingual' => array(
									'enable_field' => esc_html__('multilingual', 'pwa-for-wp'),
									'section_name' => esc_html__('pwaforwp_multilingual_setting_section', 'pwa-for-wp'),
									'setting_title' => esc_html__('Multilingual', 'pwa-for-wp'),
									'is_premium'    => true,
									'pro_link'      => $addonLists['mcfp']['p-url'],
									'pro_active'    => (is_plugin_active($addonLists['mcfp']['p-slug'])? 1: 0),
                                    'pro_deactive'    => (!is_plugin_active($addonLists['mcfp']['p-slug']) && file_exists(PWAFORWP_PLUGIN_DIR."/../".$addonLists['mcfp']['p-slug'])? 1: 0),
                                    'slug' => 'mcfp',
									'tooltip_option' => esc_html__('Show respective language page when Multilingual avilable in PWA', 'pwa-for-wp'),
									'tooltip_link'	=> 'https://pwa-for-wp.com/docs/article/how-to-use-multilingual-compatibility-for-pwa-addon/'
									),
				'qr_code_for_pwa' => array(
									'enable_field' => esc_html__('qr_code_for_pwa', 'pwa-for-wp'),
									'section_name' => esc_html__('pwaforwp_qrcode_setting_section', 'pwa-for-wp'),
									'setting_title' => esc_html__('QR Code For PWA', 'pwa-for-wp'),
									'is_premium'    => true,
									'pro_link'      => $addonLists['qrcode']['p-url'],
									'pro_active'    => (is_plugin_active($addonLists['qrcode']['p-slug'])? 1: 0),
									'pro_deactive'    => (!is_plugin_active($addonLists['qrcode']['p-slug']) && file_exists(PWAFORWP_PLUGIN_DIR."/../".$addonLists['qrcode']['p-slug'])? 1: 0),
									'slug' => 'qrcode',
									'tooltip_option'=> esc_html__('QR code for PWA', 'pwa-for-wp'),
									'tooltip_link'	=> 'https://pwa-for-wp.com/docs/article/how-to-use-qr-code-for-pwa/'
									)				
								);
				
	$featuresHtml = '';
	if(is_array($feturesArray) && !empty($feturesArray)){
	foreach ($feturesArray as $key => $featureVal) {
		echo '<div id="'.esc_attr($key).'-contents" class="pwaforwp-hide">';
			echo '<div class="pwaforwp-wrap thickbox-fetures-wrap '.esc_attr($key).'-wrap-tb">';
				do_settings_sections( $featureVal['section_name'] );
				echo '<div class="footer tab_view_submitbtn" style=""><button type="submit" class="button button-primary pwaforwp-submit-feature-opt">'.esc_html__('Submit', 'pwa-for-wp').'</button></div>';
			echo '</div>';
		echo '</div>';
		$settingsHtml = $tooltipHtml = $warnings = '';
		if($key=='notification' && empty($settings['notification_options'])){
			$warnings = "<span class='pwafw-tooltip'><i id='notification-opt-stat' class='dashicons dashicons-warning' style='color: #ffb224d1;' title=''></i><span class='pwafw-help-subtitle'>".esc_html__('Need integration', 'pwa-for-wp')."</span></span>";
		}
		if(isset($settings[$featureVal['enable_field']]) && $settings[$featureVal['enable_field']]){
			$settingsHtml = 'style="opacity:1;"';
		}else{
			$settingsHtml = 'style="opacity:0;"';
		}
		if(isset($featureVal['tooltip_option'])) {
			$tooltipHtml = '<span class="pwafw-tooltip"><i class="dashicons dashicons-editor-help"></i> 
	            <span class="pwafw-help-subtitle">%5$s
	            '.(isset($featureVal['tooltip_link']) && !empty($featureVal['tooltip_link'])? '<a href="'.esc_url($featureVal['tooltip_link']).'" target="_blank">'.esc_html__('Learn more', 'pwa-for-wp').'</a>': '').'
	            </span>
	        </span>';
	    }

	    $premium_alert  = '<div class="card-action">
				<label class="switch">
				  <input type="checkbox" %3$s name="pwaforwp_settings[%4$s]" value="1">
				  <span class="pwaforwp_slider pwaforwp_round"></span>
				</label>
				<div class="card-action-settings" data-content="%2$s-contents" '.$settingsHtml.'>
					<span class="pwaforwp-change-data pwaforwp-setting-icon-tab dashicons dashicons-admin-generic" href="#" data-option="%2$s-contents" title="%1$s"></span>
				</div>
			</div>';

	    $pro_link = '';
	    if(isset($featureVal['pro_deactive']) && $featureVal['pro_deactive'] && $featureVal['pro_deactive']==1  && !class_exists('PWAFORWPPROExtensionManager')){
	    	$wp_nonce = wp_create_nonce("wp_pro_activate");
	    	$premium_alert = '<label class="switch">
				  <input type="checkbox" class="pwa_activate_pro_plugin" value="1" data-secure="'.esc_attr($wp_nonce).'" data-file="'.esc_attr($featureVal['slug']).'">
				  <span class="pwaforwp_slider pwaforwp_round"></span>
				</label>';
	    }elseif(isset($featureVal['is_premium']) && $featureVal['is_premium'] && !$featureVal['pro_active'] && class_exists('PWAFORWPPROExtensionManager')){
		    $premium_alert = '<span class="pro deactivated">'.esc_html__( 'Deactivated', 'pwa-for-wp' ).'</span>';
	    	$pro_link = 'onclick="window.open(\''.esc_js(admin_url("admin.php?page=pwawp-extension-manager")).'\', \'_blank\')"';
	    }
	    elseif(isset($featureVal['is_premium']) && $featureVal['is_premium'] && !$featureVal['pro_active']){
	    	$premium_alert = '<span class="pro">'.esc_html__( 'PRO', 'pwa-for-wp' ).'</span>';
	    	$pro_link = 'onclick="window.open(\''.esc_js($featureVal['pro_link']).'\', \'_blank\')"';
		}

		$featuresHtml .= sprintf('<li class="pwaforwp-card-wrap %6$s" %7$s>
								<div class="pwaforwp-card-content">
									<div class="pwaforwp-tlt-sw">
										<h2>%1$s 
											'.$tooltipHtml.' %8$s
										</h2>
										'.$premium_alert.'
									</div>
									
								</div>
							</li>',
							esc_html($featureVal['setting_title']),
							esc_attr($key),
							(isset($settings[$featureVal['enable_field']]) && $settings[$featureVal['enable_field']] ) ? esc_html("checked") : '',
							$featureVal['enable_field'],
							isset($featureVal['tooltip_option'])? esc_html($featureVal['tooltip_option']): '',
							(isset($settings[$featureVal['enable_field']]) && $settings[$featureVal['enable_field']]? esc_attr('pwaforwp-feature-enabled') : ''),
							$pro_link,
							$warnings

						);
	}}
	echo '<ul class="pwaforwp-feature-cards">
			'.
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- using custom html
			$featuresHtml.'
		</ul>
		<div class="pwawp-modal-mask pwaforwp-hide">
    <div class="pwawp-modal-wrapper">
        <div class="pwawp-modal-container">
			<div class="pwaforwp-visibility-loader">
				<div class="pwaforwp-pwaforwp-visibility-loader-box"></div>
			</div>
            <button type="button" class="pwawp-media-modal-close"><span class="pwawp-media-modal-icon"></span></button>
            <div class="pwawp-modal-content">
                
                <div class="pwawp-modal-header">
                    <h3 class="pwawp-popup-title"></h3>
                </div>
                <div class="pwawp-modal-body">
                    <div class="pwawp-modal-settings">
                        
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="pwawp-modal-footer">
                <!---->
                <button type="button" class="button pwawp-modal-default-button pwawp-save-btn-modal  button-primary">
                    '.esc_html__('Save Changes', 'pwa-for-wp').'
                </button>
                <button type="button" class="button pwawp-close-btn-modal pwawp-modal-default-button">
                    '.esc_html__('Close', 'pwa-for-wp').'
                </button>
            </div>
        </div>
    </div>
</div>

		';
}

add_action("wp_ajax_pwaforwp_update_features_options", 'pwaforwp_update_features_options');
function pwaforwp_update_features_options(){	
	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
	if(!wp_verify_nonce($_POST['pwaforwp_security_nonce'], 'pwaforwp_ajax_check_nonce')){
		echo wp_json_encode(array('status'=> 503, 'message'=> esc_html__( 'Unauthorized access, CSRF token not matched','pwa-for-wp')));
		die;
	}
	if(!isset($_POST['fields_data']) || !is_array($_POST['fields_data'])){
		echo wp_json_encode(array('status'=> 502, 'message'=> esc_html__( 'Feature settings not have any fields.','pwa-for-wp')));
		die;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
        echo wp_json_encode(array('status'=> 501, 'message'=> esc_html__( 'Unauthorized access, permission not allowed','pwa-for-wp')));
		die;
    }
	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$allFields = wp_unslash($_POST['fields_data']);	
	$actualFields = array();
	$navigation_bar_data = array();
	$utm_trackings = array();
	$quick_action = array();
	if(is_array($allFields) && !empty($allFields)){
		foreach ($allFields as $key => $field) {
			// navigation bar features start			
			if (isset($field['var_name']) && $field['var_name'] == 'pwaforwp_settings[navigation][text_font_size]') {
				$navigation_bar_data['navigation']['text_font_size'] = sanitize_text_field($field['var_value']);
			}
			if (isset($field['var_name']) && $field['var_name'] == 'pwaforwp_settings[navigation][text_font_color]') {
				$navigation_bar_data['navigation']['text_font_color'] = sanitize_text_field($field['var_value']);
			}
			if (isset($field['var_name']) && $field['var_name'] == 'pwaforwp_settings[navigation][selected_text_font_color]') {
				$navigation_bar_data['navigation']['selected_text_font_color'] = sanitize_text_field($field['var_value']);
			}
			if (isset($field['var_name']) && $field['var_name'] == 'pwaforwp_settings[navigation][selected_menu_background_color]') {
				$navigation_bar_data['navigation']['selected_menu_background_color'] = sanitize_text_field($field['var_value']);
			}
			if (isset($field['var_name']) && $field['var_name'] == 'pwaforwp_settings[navigation][text_background_color]') {
				$navigation_bar_data['navigation']['text_background_color'] = sanitize_text_field($field['var_value']);
			}
			if (isset($field['var_name']) && $field['var_name'] == 'pwaforwp_settings[navigation][excluded_pages]') {
				if (!empty($field['var_value']) && is_array($field['var_value'])) {
					$navigation_bar_data['navigation']['excluded_pages'] = sanitize_text_field(implode(',',$field['var_value']));
				}
			}
			// navigation bar features end

			// UTM Tracking features start
			if (isset($field['var_name']) && $field['var_name'] == 'pwaforwp_settings[utm_details][utm_source]') {
				$utm_trackings['utm_details']['utm_source'] = sanitize_text_field($field['var_value']);
			}
			if (isset($field['var_name']) && $field['var_name'] == 'pwaforwp_settings[utm_details][utm_medium]') {
				$utm_trackings['utm_details']['utm_medium'] = sanitize_text_field($field['var_value']);
			}
			if (isset($field['var_name']) && $field['var_name'] == 'pwaforwp_settings[utm_details][utm_campaign]') {
				$utm_trackings['utm_details']['utm_campaign'] = sanitize_text_field($field['var_value']);
			}
			if (isset($field['var_name']) && $field['var_name'] == 'pwaforwp_settings[utm_details][utm_term]') {
				$utm_trackings['utm_details']['utm_term'] = sanitize_text_field($field['var_value']);
			}
			if (isset($field['var_name']) && $field['var_name'] == 'pwaforwp_settings[utm_details][utm_content]') {
				$utm_trackings['utm_details']['utm_content'] = sanitize_text_field($field['var_value']);
			}
			if (isset($field['var_name']) && $field['var_name'] == 'pwaforwp_settings[utm_details][pwa_utm_change_track]') {
				$utm_trackings['utm_details']['pwa_utm_change_track'] = sanitize_text_field($field['var_value']);
			}
			// UTM Tracking features end
					
			$variable = str_replace(array('pwaforwp_settings[', ']'), array('',''), $field['var_name']);
			if(strpos($variable, '[')!==false){
				$varArray = explode("[", $variable);
				$newArr = preg_replace('/\\\\/', '', sanitize_textarea_field($field['var_value']));
				if(is_array($newArr) && !empty($newArr)){
					foreach (array_reverse($varArray) as $key) {
						$newArr = [$key => $newArr];
					}
					
					$actualFields = pwaforwp_merge_recursive_ex($actualFields, $newArr);
				}else{
					
					if (isset($actualFields[$varArray[0]][$varArray[1]])) {
						$actualFields[$varArray[0]][$varArray[1]] = preg_replace('/\\\\/', '', sanitize_textarea_field($field['var_value']));
					}

					// for quick action or holding three array index
					
					if ( isset($varArray[0] ) && isset( $varArray[1] ) && isset( $varArray[2] ) ) {
						$quick_action[$varArray[0]][$varArray[1]][$varArray[2]] = preg_replace('/\\\\/', '', sanitize_textarea_field($field['var_value']));
					}
				}
				
			}else{
				$actualFields[$variable] = preg_replace('/\\\\/', '', sanitize_textarea_field($field['var_value']));
			}

		}
		if(!empty($navigation_bar_data)){
			if(isset($navigation_bar_data['navigation']) && count($navigation_bar_data['navigation']) >= 3){
				$pre_settings = pwaforwp_defaultSettings();
				$actualFields = wp_parse_args($navigation_bar_data, $pre_settings);
			}
		}
		if(!empty($quick_action)){
			$pre_settings = pwaforwp_defaultSettings();
			$actualFields = wp_parse_args($quick_action, $pre_settings);
		}
		if(!empty($utm_trackings) && isset($utm_trackings['utm_details'])){
			$pre_settings = pwaforwp_defaultSettings();
			$actualFields = wp_parse_args($utm_trackings, $pre_settings);
		}

		if(isset($actualFields['precaching_feature'])){
			if($actualFields['precaching_feature']==1){
				$actualFields['precaching_automatic'] = 1;
				$actualFields['precaching_automatic_post'] = 1;
			}elseif($actualFields['precaching_feature']==0){
				$actualFields['precaching_automatic'] = 0;
				$actualFields['precaching_automatic_post'] = 0;
			}
		}
		$include_targeting_type_array = array();
        $include_targeting_value_array = array();
        
        if(!empty($allFields) && is_array($allFields)){
                foreach ($allFields as $key => $value) {
					$key = sanitize_key($key);
                    if($value['var_name']=="include_targeting_type"){
                        $include_targeting_type_array[] = sanitize_text_field($value['var_value']);
                    }
                    if($value['var_name']=="include_targeting_data"){
                            $include_targeting_value_array[] = sanitize_text_field($value['var_value']);
                    }
                }
        }
        
        if (!empty($include_targeting_type_array) && is_array($include_targeting_type_array)) {
            $include_targeting_type = implode(',',$include_targeting_type_array);
            $actualFields['include_targeting_type'] = $include_targeting_type; 
        } 
        if (!empty($include_targeting_value_array) && is_array($include_targeting_value_array)) {
            $include_targeting_value = implode(',',$include_targeting_value_array);
            $actualFields['include_targeting_value'] = $include_targeting_value; 
        }
        
        $exclude_targeting_type_array = array();
        $exclude_targeting_value_array = array();
        if(!empty($allFields) && is_array($allFields)){
			foreach ($allFields as $key => $value) {
				if($value['var_name']=="exclude_targeting_type"){
					$exclude_targeting_type_array[] = sanitize_text_field($value['var_value']);
				}
				if($value['var_name']=="exclude_targeting_data"){
						$exclude_targeting_value_array[] = sanitize_text_field($value['var_value']);
				}
			}
        }
        if (!empty($exclude_targeting_type_array) && is_array($exclude_targeting_type_array)) {
            $exclude_targeting_type = implode(',',$exclude_targeting_type_array);
            $actualFields['exclude_targeting_type'] = $exclude_targeting_type; 
        }  
        if (!empty($exclude_targeting_value_array) && is_array($exclude_targeting_value_array)) {
            $exclude_targeting_value = implode(',',$exclude_targeting_value_array);
            $actualFields['exclude_targeting_value'] = $exclude_targeting_value; 
        }
		if(isset($actualFields['addtohomebanner_feature'])){
			if($actualFields['addtohomebanner_feature']==1){
				$actualFields['custom_add_to_home_setting'] = 1;
			}elseif($actualFields['addtohomebanner_feature']==0){
				$actualFields['custom_add_to_home_setting'] = 0;
			}
		}
		if(isset($actualFields['loader_feature'])){
			if($actualFields['loader_feature']==1){
				$actualFields['loading_icon'] = 1;
			}elseif($actualFields['loader_feature']==0){
				$actualFields['loading_icon'] = 0;
			}
		}
		if(isset($actualFields['utmtracking_feature'])){
			if($actualFields['utmtracking_feature']==1){
				$actualFields['utm_setting'] = 1;
			}elseif($actualFields['utmtracking_feature']==0){
				$actualFields['utm_setting'] = 0;
			}
		}
		if(isset($actualFields['fcm_config']) && $actualFields['fcm_config']){
			$actualFields['fcm_config'] = wp_unslash($actualFields['fcm_config']);
		}
		
		$pre_settings = pwaforwp_defaultSettings();
		$actualFields = wp_parse_args($actualFields, $pre_settings);
		

		//dependent settings
		if(isset($actualFields['utm_setting']) && $actualFields['utm_setting']==0){
			$actualFields['utmtracking_feature'] = $actualFields['utm_setting'];
		}
		if(isset($actualFields['loading_icon']) && $actualFields['loading_icon']==0){
			$actualFields['loader_feature'] = $actualFields['loading_icon'];
		}

		
		if(isset($actualFields['custom_add_to_home_setting']) && $actualFields['custom_add_to_home_setting']==0){
			$actualFields['addtohomebanner_feature'] = $actualFields['custom_add_to_home_setting'];
		}
		

		$actualFields = apply_filters('pwaforwp_features_update_data_save', $actualFields);

		update_option( 'pwaforwp_settings', $actualFields ) ;
		global $pwaforwp_settings;
		$pwaforwp_settings = array();
		pwaforwp_required_file_creation();
		echo wp_json_encode(array('status'=> 200, 'message'=> esc_html__('Settings Saved.','pwa-for-wp'), 'options'=>$actualFields));
			die;
	}else{
		echo wp_json_encode(array('status'=> 503, 'message'=> esc_html__('Fields not defined','pwa-for-wp')));	
		die;
	}
}

add_action( 'activated_plugin', 'pwaforwp_active_update_transient' );
function pwaforwp_active_update_transient($plugin){
	delete_transient( 'pwaforwp_restapi_check' ); 
}
add_action( 'deactivated_plugin', 'pwaforwp_deactivate_update_transient' );
function pwaforwp_deactivate_update_transient($plugin){
	delete_transient( 'pwaforwp_restapi_check' ); 
}

add_action("wp_ajax_pwaforwp_include_visibility_setting_callback", 'pwaforwp_include_visibility_setting_callback');
function pwaforwp_include_visibility_setting_callback(){
	if ( ! current_user_can( pwaforwp_current_user_can() ) ) {
		return;
	}
    if ( ! isset( $_POST['pwaforwp_security_nonce'] ) ){
        return; 
    }
	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
    if ( !wp_verify_nonce( $_POST['pwaforwp_security_nonce'], 'pwaforwp_ajax_check_nonce' ) ){
    	return;  
    } 
    // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
   	 $include_type = sanitize_text_field($_POST['include_type']);

    if($include_type == 'post' || $include_type == 'page'){
        $args = array(
            'post_type' => $include_type,
            'post_status' => 'publish',
            'posts_per_page' => 50,
         );  
        $query = new WP_Query($args);
        $option ='<option value="">Select '.esc_html($include_type).' Type</option>';
        while ($query->have_posts()) : $query->the_post();
                    
            $option .= '<option value="'.get_the_title().'">'.get_the_title().'</option>';
             endwhile; 
		wp_reset_postdata();
    }
    if(in_array($include_type, array('post_type','globally'))) {
        if($include_type == 'post_type'){
            // $get_option = array('post', 'page', 'product');
            $get_option = get_post_types();;
            $option ='<option value="">'.esc_html__( 'Select Post Type', 'pwa-for-wp' ).'</option>';
        }
        if($include_type == 'globally'){ 
            $get_option = array('Globally');
            $option ='<option value="">'.esc_html__( 'Select Global Type', 'pwa-for-wp' ).'</option>';
        }
		if(!empty($get_option) && is_array($get_option)){        
        foreach ($get_option as $options_array) {
            $option .= '<option value="'.esc_attr($options_array).'">'.esc_html($options_array).'</option>';
        }}
    }

     if($include_type == 'post_category'){
        $get_option = get_categories(array(
          'hide_empty' => true,
        ));
        $option ='<option value="">'.esc_html__( 'Select Post Category', 'pwa-for-wp' ).'</option>';
		if(!empty($get_option) && is_array($get_option)){   
        foreach ($get_option as $options_array) {
            $option .= '<option value="'.esc_attr($options_array->name).'">'.esc_html($options_array->name).'</option>';
        }}
       
    }
    if($include_type == 'taxonomy'){ 
        $get_option = get_terms( array(
          'hide_empty' => true,
        ) );
        $option ='<option value="">'.esc_html__( 'Select Taxonomy', 'pwa-for-wp' ).'</option>';
		if(!empty($get_option) && is_array($get_option)){  
        foreach ($get_option as $options_array) {
            $option .= '<option value="'.esc_attr($options_array->name).'">'.esc_html($options_array->name).'</option>';
        }}
    }

    if($include_type == 'tags'){ 
        $get_option = get_tags(array(
          'hide_empty' => false
        ));
        $option ='<option value="">'.esc_html__( 'Select Tag', 'pwa-for-wp' ).'</option>';
		if(!empty($get_option) && is_array($get_option)){  
        foreach ($get_option as $options_array) {
            $option .= '<option value="'.esc_attr($options_array->name).'">'.esc_html($options_array->name).'</option>';
        }
	}

    }

    if($include_type == 'user_type'){ 
        $get_options = array("administrator"=>"Administrator", "editor"=>"Editor", "author"=>"Author", "contributor"=>"Contributor","subscriber"=>"Subscriber");
        $get_option = $get_options;
        $option ='<option value="">'.esc_html__( 'Select User', 'pwa-for-wp' ).'</option>';
		if(!empty($get_option) && is_array($get_option)){   
        foreach ($get_option as $key => $value) {
            $option .= '<option value="'.esc_attr($key).'">'.esc_html($value).'</option>';
        }}

    }

    if($include_type == 'page_template'){ 
        $get_option = wp_get_theme()->get_page_templates();
        $option ='<option value="">'.esc_html__( 'Select Page Template', 'pwa-for-wp' ).'</option>';
		if(!empty($get_option) && is_array($get_option)){   
        foreach ($get_option as $key => $value) {
            $option .= '<option value="'.esc_attr($value).'">'.esc_html($value).'</option>';
        }}
    }

    $data = array('success' => 1,'message'=>esc_html__('Success','pwa-for-wp'),'option'=>$option );
    echo wp_json_encode($data);    exit;

}

add_action("wp_ajax_pwaforwp_include_visibility_condition_callback", 'pwaforwp_include_visibility_condition_callback');

function pwaforwp_include_visibility_condition_callback() {
	if ( ! current_user_can( pwaforwp_current_user_can() ) ) {
		return;
	}
    if ( ! isset( $_POST['pwaforwp_security_nonce'] ) ){
        return; 
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
    }
	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
    if ( !wp_verify_nonce( $_POST['pwaforwp_security_nonce'], 'pwaforwp_ajax_check_nonce' ) ){
       return;  
    }
    // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
    $include_targeting_type = sanitize_text_field($_POST['include_targeting_type']);
	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
    $include_targeting_data = sanitize_text_field($_POST['include_targeting_data']);

    $rand = time().wp_rand(000,999);
    $option .= '<span class="pwaforwp-visibility-target-icon-'.esc_attr($rand).'">
    <input type="hidden" name="include_targeting_type" value="'.esc_attr($include_targeting_type).'">
    <input type="hidden" name="include_targeting_data" value="'.esc_attr($include_targeting_data).'">';
    $include_targeting_type = pwaforwpRemoveExtraValue($include_targeting_type);
    $include_targeting_data = pwaforwpRemoveExtraValue($include_targeting_data);
    $option .= '<span class="pwaforwp-visibility-target-item"><span class="visibility-include-target-label">'.esc_html($include_targeting_type.' - '.$include_targeting_data).'</span>
        <span class="pwaforwp-visibility-target-icon" data-index="0"><span class="dashicons dashicons-no-alt " aria-hidden="true" onclick="removeIncluded_visibility('.esc_attr($rand).')"></span></span></span></span>';

    $data = array('success' => 1,'message'=>esc_html__('Success','pwa-for-wp'),'option'=>$option );
    echo wp_json_encode($data);    exit;
}

add_action("wp_ajax_pwaforwp_exclude_visibility_condition_callback", 'pwaforwp_exclude_visibility_condition_callback');

function pwaforwp_exclude_visibility_condition_callback() {
	if ( ! current_user_can( pwaforwp_current_user_can() ) ) {
		return;
	}
    if ( ! isset( $_POST['pwaforwp_security_nonce'] ) ){
        return; 
    }
	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
    if ( !wp_verify_nonce( $_POST['pwaforwp_security_nonce'], 'pwaforwp_ajax_check_nonce' ) ){
       return;  
    } 
    // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
    $exclude_targeting_type = sanitize_text_field($_POST['exclude_targeting_type']);
	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
    $exclude_targeting_data = sanitize_text_field($_POST['exclude_targeting_data']);

    $rand = time().wp_rand(000,999);
    $option .= '<span class="pwaforwp-visibility-target-icon-'.esc_attr($rand).'">
    <input type="hidden" name="exclude_targeting_type" value="'.esc_attr($exclude_targeting_type).'">
    <input type="hidden" name="exclude_targeting_data" value="'.esc_attr($exclude_targeting_data).'">';

    $exclude_targeting_type = pwaforwpRemoveExtraValue($exclude_targeting_type);
    $exclude_targeting_data = pwaforwpRemoveExtraValue($exclude_targeting_data);
    $option .= '<span class="pwaforwp-visibility-target-item"><span class="visibility-include-target-label">'.esc_html($exclude_targeting_type.' - '.$exclude_targeting_data).'</span>
        <span class="pwaforwp-visibility-target-icon" data-index="0"><span class="dashicons dashicons-no-alt " aria-hidden="true" onclick="removeIncluded_visibility('.esc_attr($rand).')"></span></span></span></span>';

    $data = array('success' => 1,'message'=>esc_html__('Success','pwa-for-wp'),'option'=>$option );
    echo wp_json_encode($data);    exit;
}

function pwaforwpRemoveExtraValue($val)
{
    $val = str_replace("_", " ", $val);
    $val = str_replace(".php", "", $val);
    $val = ucwords($val);
    return $val;
}

/**
* Function Create images dynamically
* @param Array $old_value previous values
* @param Array $new_value new updated values of save
*/
add_action('update_option_pwaforwp_settings', 'pwaforwp_resize_images', 10, 3);
function pwaforwp_resize_images( $old_value, $new_value, $option='' ){
	
	if( isset($new_value['ios_splash_icon']['2048x1496']) && !empty($new_value['ios_splash_icon']['2048x1496']) && strrpos($new_value['ios_splash_icon']['2048x1496'], 'uploads/') ){
		$uploadPath = wp_upload_dir();
		$filename = str_replace($uploadPath['baseurl'], $uploadPath['basedir'], $new_value['ios_splash_icon']['2048x1496']);
		if( file_exists($filename) ){
			//Check there is need of file creation
			$createImage = array();
			if(!empty($new_value['ios_splash_icon']) && is_array($new_value['ios_splash_icon'])){   
			foreach ($new_value['ios_splash_icon'] as $key => $value) {
				if(empty($value)){
					$createImage[$key] = '';
				}
			}}
			if(count($createImage)>0){
				$editor = wp_get_image_editor( $filename, array() );
				if(!empty($createImage) && is_array($createImage)){   
				foreach ($createImage as $newkey => $newimages) {
					
					// Grab the editor for the file and the size of the original image.
					if ( !is_wp_error($editor) ) {
					   // Get the dimensions for the size of the current image.
						$dimensions = $editor->get_size();
						$width = $dimensions['width'];
						$height = $dimensions['height'];
						

						// Calculate the new dimensions for the image.
						$keyDim = explode('x', $newkey);
						$newWidth = $keyDim[0];
						$newHeight = $keyDim[1];

						// Resize the image.
						$result = $editor->resize($newWidth, $newHeight, true);

						// If there's no problem, save it; otherwise, print the problem.
						if (!is_wp_error($result)) {
							$newImage = $editor->save($editor->generate_filename());
							$newfilename = str_replace($uploadPath['basedir'], $uploadPath['baseurl'], $newImage['path']);
							$new_value['ios_splash_icon'][$newkey] = sanitize_text_field($newfilename);
						}else{
							// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
							error_log($result->get_error_message()." Width: ".$newWidth." Height:".$newHeight);
						}
					}

				}}//Foreach closed
				update_option( 'pwaforwp_settings', $new_value);

			}
		}
	}

    

}


if(!function_exists('pwaforwp_subscribe_newsletter')){
	add_action('wp_ajax_pwaforwp_subscribe_newsletter','pwaforwp_subscribe_newsletter');

	function pwaforwp_subscribe_newsletter(){
		if ( ! isset( $_POST['pwaforwp_security_nonce'] ) ){
			return; 
		}
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		if ( !wp_verify_nonce( $_POST['pwaforwp_security_nonce'], 'pwaforwp_ajax_check_nonce' ) ){
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
	    $api_url = 'http://magazine3.company/wp-json/api/central/email/subscribe';
	    $api_params = array(
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
	        'name' => sanitize_text_field($_POST['name']),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
	        'email'=> sanitize_email($_POST['email']),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
	        'website'=> sanitize_text_field($_POST['website']),
	        'type'=> 'pwa'
	    );
	    $response = wp_remote_post( $api_url, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			if(!empty($response->get_error_message())){
				$error_message = strtolower($response->get_error_message());
				$error_pos = strpos($error_message, 'operation timed out');
				if($error_pos !== false){
					$message = esc_html__('Request timed out, please try again','pwa-for-wp');
				}else{
					$message = esc_html($response->get_error_message());
				}
			}
			if(empty($message)){ 
					 $message =   esc_html__( 'An error occurred, please try again.','pwa-for-wp');
			}
		}else{
			$response = wp_remote_retrieve_body( $response );
		}
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- using custom html
	    echo $response;
	    die;
	} 	
}

if ( ! function_exists( 'pwaforwp_splashscreen_uploader' ) ) {
	add_action( 'wp_ajax_pwaforwp_splashscreen_uploader', 'pwaforwp_splashscreen_uploader' );

	function pwaforwp_splashscreen_uploader() {
		if ( ! isset( $_GET['pwaforwp_security_nonce'] ) ){
            echo wp_json_encode( array( "status" => 500, "message" => esc_html__( 'Failed! Security check not active', 'pwa-for-wp' ) ) );
            die;
        }
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        if ( ! wp_verify_nonce( $_GET['pwaforwp_security_nonce'], 'pwaforwp_ajax_check_nonce' ) ) {
        	echo wp_json_encode( array( "status" => 500, "message" => esc_html__( "Failed! Security check", 'pwa-for-wp' ) ) );
        	die;
        }
        if( ! current_user_can( 'manage_options' ) ) {
        	echo wp_json_encode( array( "status" => 401, "message" => esc_html__( "Failed! you are not autherized to save",'pwa-for-wp' ) ) );
        	die;
        }
		$pwaforwp_settings = pwaforwp_defaultSettings();

		// 
		$upload = wp_upload_dir();
		$path = $upload['basedir'] . "/pwa-splash-screen/";
		
		// Ensure WP_Filesystem is initialized
		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
		}
		
		global $wp_filesystem;
		WP_Filesystem();
		
		// Create the directory using WP_Filesystem
		$wp_filesystem->mkdir( $path );
		
		// Write the index.html file using WP_Filesystem
		$wp_filesystem->put_contents( $path . '/index.html', '', FS_CHMOD_FILE );
		
		// Define the zip file path
		$zipfilename = $path . "file.zip";
		
		// Open input stream
		$input = fopen('php://input', 'rb');
		
		// Capture the content from the input stream
		$content = stream_get_contents($input);
		
		// Write the content to the ZIP file using WP_Filesystem
		$wp_filesystem->put_contents( $zipfilename, $content, FS_CHMOD_FILE );
		
		// Close the input stream
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- it’s the correct and necessary way to close the resource, it should be accepted.
		fclose($input);

		unzip_file($zipfilename, $path);
		$pathURL = $upload['baseurl']."/pwa-splash-screen/splashscreens/";
		$iosdata = pwaforwp_ios_splashscreen_files_data();

		if ( is_array( $iosdata ) && ! empty( $iosdata ) ) {
			foreach ( $iosdata as $key => $value ) {
				$pwaforwp_settings['ios_splash_icon'][sanitize_key($key)] = sanitize_text_field( $pathURL.$value['file'] );
			}
		}

		$pwaforwp_settings['iosSplashScreenOpt'] = 'generate-auto';

		update_option( 'pwaforwp_settings', $pwaforwp_settings ) ;
		wp_delete_file( $zipfilename );
		echo wp_json_encode( array( "status" => 200, "message" => esc_html__( "Splash screen uploaded successfully", "pwa-for-wp" ) ) );
		die;
	} 	
} 

add_filter('pre_update_option_pwaforwp_settings', 'pwaforwp_update_force_update', 10, 3); 
function pwaforwp_update_force_update( $value, $old_value, $option) {
	if( ! function_exists( 'wp_get_current_user' )) {
		return $value;
	}
	$user = wp_get_current_user();
	$allowed_roles = array('administrator');
	if(! array_intersect($allowed_roles, $user->roles ) ) {
		return $value;
	}
	if(isset($value['force_update_sw_setting'])){
		$version = $value['force_update_sw_setting'];
		if($version){
			$version = explode(".", $version);
			if(count($version)<=3){
				$version = implode(".", (array)$version).".1";
			}else{
				$version[count($version)-1] = $version[count($version)-1]+1;
				$version = implode(".", (array)$version);
			}
		}
		$value['force_update_sw_setting'] = $version;
	}
	return $value;
}

/**
 * Show the loaders on admin section
 * @return Javascript/text [print required javascript to show loader] 
 */
function pwaforwp_loading_icon_scripts(){
	echo "<script type='text/javascript'>window.addEventListener('beforeunload', function(){
    if(document.getElementsByClassName('pwaforwp-loading-wrapper') && typeof document.getElementsByClassName('pwaforwp-loading-wrapper')[0]!=='undefined'){
      document.getElementsByClassName('pwaforwp-loading-wrapper')[0].style.display = 'flex';
    }
    if(document.getElementById('pwaforwp_loading_div')){
      document.getElementById('pwaforwp_loading_div').style.display = 'flex';
    }
    if(document.getElementById('pwaforwp_loading_icon')){
      document.getElementById('pwaforwp_loading_icon').style.display = 'flex';
    }
  });
  if(document.getElementsByClassName('pwaforwp-loading-wrapper') && document.getElementsByClassName('pwaforwp-loading-wrapper').length > 0){
    var tot = document.getElementsByClassName('pwaforwp-loading-wrapper');
    for (var i = 0; i < tot.length; i++) {
      tot[i].style.display = 'none';
    }
  }
  if(document.getElementById('pwaforwp_loading_div')){
    document.getElementById('pwaforwp_loading_div').style.display = 'none';
  }
  if(document.getElementById('pwaforwp_loading_icon')){
    document.getElementById('pwaforwp_loading_icon').style.display = 'none';
  }</script>";
}
/**
 * Show the loaders on admin section
 * @return css/text [print required styles to show loader] 
 */
function pwaforwp_loading_icon_styles(){
	echo '<style>#pwaforwp_loading_div {width: 100%;height: 200%;position: fixed;top: 0;left: 0;background-color: white;z-index: 9999;}
	.pwaforwp-loading-wrapper{display:none;}
	#pwaforwp_loading_icon {position: fixed;left: 50%;top: 50%;z-index: 10000;margin: -60px 0 0 -60px;border: 16px solid #f3f3f3;border-radius: 50%;border-top: 16px solid #3498db;width: 120px;height: 120px;-webkit-animation: spin 2s linear infinite;animation: spin 2s linear infinite;}

	@-webkit-keyframes spin {0% { -webkit-transform: rotate(0deg); }100% { -webkit-transform: rotate(360deg); }}
	@keyframes spin {0% { transform: rotate(0deg); }100% { transform: rotate(360deg); }}
	</style>';
}

function pwaforwp_loading_select2_styles(){
	echo '<style>
	.select2-container .select2-selection--single {
		height:44px !important;
		vertical-align: middle;
	}
	.select2-container--default .select2-selection--single .select2-selection__rendered {
		line-height: 40px !important;
	}
	.select2-container{z-index:999999}
	</style>';
}

/**
 * pwaforwp_merge_recursive_ex merge any multidimensional Array
 * @param Array1(array) Array2(array)
 */
function pwaforwp_merge_recursive_ex(array $array1, array $array2)
{
    $merged = $array1;
	if(is_array($array2) && !empty($array2)){
    foreach ($array2 as $key => & $value) {
		$key = sanitize_key($key);
		$value = sanitize_text_field($value);
        if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
            $merged[$key] = pwaforwp_merge_recursive_ex($merged[$key], $value);
        } else if (is_numeric($key)) {
             if (!in_array($value, $merged)) {
                $merged[] = $value;
             }
        } else {
            $merged[$key] = $value;
        }
    }}

    return $merged;
}

function pwaforwp_get_data_by_type($include_type='post',$search=null){
	$result = array();
	$posts_per_page = 50;
	
	if($include_type == 'post' || $include_type == 'page'){
		$args = array(
			'post_type' => $include_type,
			'post_status' => 'publish',
			'posts_per_page' => $posts_per_page,
		);
		if(!empty($search)){
			$args['s']	= $search;
		}

    	$meta_query = new WP_Query($args);        
            
      	if($meta_query->have_posts()) {
			while($meta_query->have_posts()) {
				$meta_query->the_post();
				$result[] = array('id' => get_the_ID(), 'text' => get_the_title());
          	}
			wp_reset_postdata();
      	}
		
    }
    if(in_array($include_type, array('post_type','globally'))) {
        if($include_type == 'post_type'){
			$args['public'] = true;
			if(!empty($search)){
				$args['name']	= $search;
			}
          	$get_option = get_post_types( $args, 'names');
        }
        if($include_type == 'globally'){ 
            $get_option = array('Globally');
        }
		if(!empty($get_option) && is_array($get_option)){        
        foreach ($get_option as $options_array) {
			$result[] = array('id' => $options_array, 'text' => $options_array);
        }}
    }

     if ( $include_type == 'post_category' ) {

		$args = array( 
			'taxonomy'   => 'category',
			'hide_empty' => true,
			'number'     => $posts_per_page, 
		);

		if(!empty($search)){
			$args['name__like'] = $search;
		}

		$get_option = get_terms( $args );

		if ( ! empty( $get_option ) && is_array( $get_option ) ) {   

			foreach ( $get_option as $options_array ) {
				$result[] = array( 'id' => $options_array->name, 'text' => $options_array->name );
			}
		}
       
    }

    if($include_type == 'taxonomy'){
		$args = array( 
			'hide_empty' => true,
			'number'     => $posts_per_page, 
		);

		if(!empty($search)){
			$args['name__like'] = $search;
		}
        $get_option = get_terms($args);
		if(!empty($get_option) && is_array($get_option)){  
			foreach ($get_option as $options_array) {
				$result[] = array('id' => $options_array->name, 'text' => $options_array->name);
			}
		}
    }

    if($include_type == 'tags'){
		$args['hide_empty'] = false;
        $get_option = get_tags($args);
		if(!empty($get_option) && is_array($get_option)){  
			foreach ($get_option as $options_array) {
				$result[] = array('id' => $options_array->name, 'text' => $options_array->name);
			}
		}
	}

    if($include_type == 'user_type'){ 
        $get_options = array("administrator"=>"Administrator", "editor"=>"Editor", "author"=>"Author", "contributor"=>"Contributor","subscriber"=>"Subscriber");
        $get_option = $get_options;
		if(!empty($get_option) && is_array($get_option)){   
			foreach ($get_option as $key => $value) {
				$result[] = array('id' => $key, 'text' => $value);
			}
		}

    }

    if($include_type == 'page_template'){ 
        $get_option = wp_get_theme()->get_page_templates();
		if(!empty($get_option) && is_array($get_option)){   
			foreach ($get_option as $key => $value) {
				$result[] = array('id' => $value, 'text' => $value);
			}
		}
    }

	return $result;
}


function pwaforwp_get_select2_data(){
	if ( ! isset( $_GET['pwaforwp_security_nonce'] ) ){
	  return; 
	}
	if ( ! current_user_can( pwaforwp_current_user_can() ) ) {
		return;
	}
	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	if ( (wp_verify_nonce( $_GET['pwaforwp_security_nonce'], 'pwaforwp_ajax_check_nonce' ) )){
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$search        = isset( $_GET['q'] ) ? sanitize_text_field( $_GET['q'] ) : '';
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash                                   
		$type          = isset( $_GET['type'] ) ? sanitize_text_field( $_GET['type'] ) : '';
		
		$result = pwaforwp_get_data_by_type($type,$search);		
		wp_send_json(['results' => $result] );            

	}else{
		return;  
	}
	wp_die();
}

add_action( 'wp_ajax_pwaforwp_get_select2_data', 'pwaforwp_get_select2_data');

function pwaforwp_enqueue_select2_js( $hook ) {
	if($hook  == 'toplevel_page_pwaforwp'){

		wp_dequeue_script( 'select2-js' );   
		wp_dequeue_script( 'select2' );
		wp_deregister_script( 'select2' );
		//conflict with jupitor theme fixed starts here
		wp_dequeue_script( 'mk-select2' );
		wp_deregister_script( 'mk-select2' );                
		//conflict with jupitor theme fixed ends here                
		wp_dequeue_script( 'wds-shared-ui' );
		wp_deregister_script( 'wds-shared-ui' );
		wp_dequeue_script( 'pum-admin-general' );
		wp_deregister_script( 'pum-admin-general' );
		//Hide vidoe pro select2 on schema type dashboard
		wp_dequeue_script( 'cmb-select2' );
		wp_deregister_script( 'cmb-select2' );

		wp_enqueue_style('pwaforwp-select2-style', PWAFORWP_PLUGIN_URL. 'assets/css/select2.min.css' , false, PWAFORWP_PLUGIN_VERSION);
		wp_enqueue_script('select2', PWAFORWP_PLUGIN_URL. 'assets/js/select2.min.js', array( 'jquery'), PWAFORWP_PLUGIN_VERSION, true);
		wp_enqueue_script('select2-extended-script', PWAFORWP_PLUGIN_URL. 'assets/js/select2-extended.min.js', array( 'jquery' ), PWAFORWP_PLUGIN_VERSION, true);
	}

}
add_action( 'admin_enqueue_scripts', 'pwaforwp_enqueue_select2_js',9999 );