<?php
if ( ! defined( 'ABSPATH' ) ) exit;

require_once PWAFORWP_PLUGIN_DIR.'/admin/pwa-utility.php';
function pwaforpw_add_menu_links() {	
	// Main menu page
	add_menu_page( esc_html__( 'Progressive Web Apps For WP', 'pwa-for-wp' ), 
                esc_html__( 'PWA', 'pwa-for-wp' ), 
                'manage_options',
                'pwaforwp',
                'pwaforwp_admin_interface_render',
                '', 100 );
	
	// Settings page - Same as main menu page
	add_submenu_page( 'pwaforwp',
                esc_html__( 'Progressive Web Apps For WP', 'pwa-for-wp' ),
                esc_html__( 'Settings', 'pwa-for-wp' ),
                'manage_options',
                'pwaforwp',
                'pwaforwp_admin_interface_render');	
                                
	if(!pwaforwp_addons_is_active()){
	    global $submenu;
		$permalink = 'javasctipt:void(0);';
		$submenu['pwaforwp'][] = array( '<div style="color:#fff176;" onclick="window.open(\'https://pwa-for-wp.com/pricing/\')">'.esc_html__( 'Upgrade To Premium', 'pwa-for-wp' ).'</div>', 'manage_options', $permalink);
	}
}
add_action( 'admin_menu', 'pwaforpw_add_menu_links');

function pwaforwp_admin_interface_render(){
    
	// Authentication
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
		
	// Handing save settings
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
			<h1><?php echo esc_html__('Progressive Web Apps For WP', 'pwa-for-wp'); ?></h1>
			<div class="pwaforwp-main-wrapper">
				<h2 class="nav-tab-wrapper pwaforwp-tabs">
					<?php
					echo '<a href="' . esc_url(pwaforwp_admin_link('dashboard')) . '" class="nav-tab ' . esc_attr( $tab == 'dashboard' ? 'nav-tab-active' : '') . '"><span class="dashicons dashicons-dashboard"></span> ' . esc_html__('Dashboard', 'pwa-for-wp') . '</a>';

					echo '<a href="' . esc_url(pwaforwp_admin_link('general')) . '" class="nav-tab ' . esc_attr( $tab == 'general' ? 'nav-tab-active' : '') . '"><span class="dashicons dashicons-welcome-view-site"></span> ' . esc_html__('Setup','pwa-for-wp') . '</a>';

					echo '<a href="' . esc_url(pwaforwp_admin_link('features')) . '" class="nav-tab ' . esc_attr( $tab == 'features' ? 'nav-tab-active' : '') . '"><span class="dashicons dashicons-admin-generic"></span> ' . esc_html__('Features','pwa-for-wp') . '</a>';
		            
		            echo '<a href="' . esc_url(pwaforwp_admin_link('tools')) . '" class="nav-tab ' . esc_attr( $tab == 'tools' ? 'nav-tab-active' : '') . '"> ' . esc_html__('Tools','pwa-for-wp') . '</a>';

		            echo '<a href="' . esc_url(pwaforwp_admin_link('other_setting')) . '" class="nav-tab ' . esc_attr( $tab == 'other_setting' ? 'nav-tab-active' : '') . '"> ' . esc_html__('Advance','pwa-for-wp') . '</a>';
		            
		            echo '<a href="' . esc_url(pwaforwp_admin_link('premium_features')) . '" class="nav-tab ' . esc_attr( $tab == 'premium_features' ? 'nav-tab-active' : '') . '" data-extmgr="'. ( class_exists('PWAFORWPPROExtensionManager')? "yes": "no" ).'"> ' . esc_html__('Premium Features','pwa-for-wp') . '</a>';

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
						                    	<option value="">Select</option>
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
		        <?php if(!pwaforwp_addons_is_active()) { ?>
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

		    </div>
	</div>
        
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
	register_setting( 'pwaforwp_setting_dashboard_group', 'pwaforwp_settings' );

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
		
		// Splash Screen Icon
		add_settings_field(
			'pwaforwp_app_splash_icon',									// ID
			esc_html__('App Splash Screen Icon', 'pwa-for-wp'),	// Title
			'pwaforwp_splash_icon_callback',								// Callback function
			'pwaforwp_general_section',						// Page slug
			'pwaforwp_general_section'						// Settings Section ID
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
                
                                                
    add_settings_section('pwaforwp_tools_section', esc_html__(' ','pwa-for-wp'), '__return_false', 'pwaforwp_tools_section');
                                                
		add_settings_field(
			'pwaforwp_reset_setting',							// ID
			esc_html__('Reset', 'pwa-for-wp'),	// Title
			'pwaforwp_reset_setting_callback',							// CB
			'pwaforwp_tools_section',						// Page slug
			'pwaforwp_tools_section'						// Settings Section ID
		);

		//Misc tabs
		add_settings_section('pwaforwp_other_setting_section', esc_html__(' ','pwa-for-wp'), '__return_false', 'pwaforwp_other_setting_section');
		add_settings_field(
			'pwaforwp_cdn_setting',							// ID
			esc_html__('CDN Compatibility', 'pwa-for-wp'),	// Title
			'pwaforwp_cdn_setting_callback',							// CB
			'pwaforwp_other_setting_section',						// Page slug
			'pwaforwp_other_setting_section'						// Settings Section ID
		);                
        add_settings_field(
			'pwaforwp_offline_google_setting',							// ID
			esc_html__('Offline Google Analytics', 'pwa-for-wp'),	// Title
			'pwaforwp_offline_google_setting_callback',							// CB
			'pwaforwp_other_setting_section',						// Page slug
			'pwaforwp_other_setting_section'						// Settings Section ID
		);
		add_settings_field(
			'pwaforwp_prefetch_manifest_setting',							// ID
			esc_html__('Prefetch manifest URL link', 'pwa-for-wp'),	// Title
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

		add_settings_section('pwaforwp_addtohomescreen_setting_section', esc_html__(' ','pwa-for-wp'), '__return_false', 'pwaforwp_addtohomescreen_setting_section');
        add_settings_field(
			'pwaforwp_custom_add_to_home',									// ID
			esc_html__('Custom Add To Home Banner', 'pwa-for-wp'),		// Title
			'pwaforwp_custom_add_to_home_callback',								// CB
			'pwaforwp_addtohomescreen_setting_section',						// Page slug
			'pwaforwp_addtohomescreen_setting_section'						// Settings Section ID
		);
        // Add to Home screen Color
        /*add_settings_field(
			'pwaforwp_custom_banner_design',									// ID
			esc_html__('', 'pwa-for-wp'),		// Title
			'pwaforwp_custom_banner_design_callback',								// CB
			'pwaforwp_addtohomescreen_setting_section',						// Page slug
			'pwaforwp_addtohomescreen_setting_section'						// Settings Section ID
		);*/
                
                add_settings_field(
			'pwaforwp_cache_external_links_setting',							// ID
			esc_html__('Cache External Links', 'pwa-for-wp'),	// Title
			'pwaforwp_cache_external_links_setting_callback',							// CB
			'pwaforwp_other_setting_section',						// Page slug
			'pwaforwp_other_setting_section'						// Settings Section ID
		);
        add_settings_section('pwaforwp_utmtracking_setting_section', esc_html__(' ','pwa-for-wp'), '__return_false', 'pwaforwp_utmtracking_setting_section');
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
			esc_html__('Remove default banner', 'pwa-for-wp'),	// Title
			'pwaforwp_avoid_default_banner_setting_callback',							// CB
			'pwaforwp_other_setting_section',						// Page slug
			'pwaforwp_other_setting_section'						// Settings Section ID
		);
		add_settings_field(
			'pwaforwp_avoid_pwa_loggedin_setting',							// ID
			esc_html__('Remove pwa for logged in users', 'pwa-for-wp'),	// Title
			'pwaforwp_avoid_pwa_loggedin_setting_callback',							// CB
			'pwaforwp_other_setting_section',						// Page slug
			'pwaforwp_other_setting_section'						// Settings Section ID
		);
		add_settings_field(
			'pwaforwp_serve_cache_method_setting',							// ID
			esc_html__('PWA alternative method', 'pwa-for-wp'),	// Title
			'pwaforwp_serve_cache_method_setting_callback',							// CB
			'pwaforwp_other_setting_section',						// Page slug
			'pwaforwp_other_setting_section'						// Settings Section ID
		);
		add_settings_field(
			'pwaforwp_reset_cookies_method_setting',							// ID
			esc_html__('Reset cookies', 'pwa-for-wp'),	// Title
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
		add_settings_section('pwaforwp_loaders_setting_section', esc_html__(' ','pwa-for-wp'), '__return_false', 'pwaforwp_loaders_setting_section');
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

		add_settings_section('pwaforwp_compatibility_setting_section', esc_html__(' ','pwa-for-wp'), '__return_false', 'pwaforwp_compatibility_setting_section');
                add_settings_field(
			'pwaforwp_one_signal_support',									// ID
			esc_html__('OneSignal', 'pwa-for-wp'),		// Title
			'pwaforwp_one_signal_support_callback',								// CB
			'pwaforwp_compatibility_setting_section',						// Page slug
			'pwaforwp_compatibility_setting_section'						// Settings Section ID
		);
        add_settings_field(
			'pwaforwp_pushnami_support',							// ID
			esc_html__('Pushnami', 'pwa-for-wp'),					// Title
			'pwaforwp_pushnami_support_callback',					// CB
			'pwaforwp_compatibility_setting_section',				// Page slug
			'pwaforwp_compatibility_setting_section'				// Settings Section ID
		);
		add_settings_field(
			'pwaforwp_webpushr_support',							// ID
			esc_html__('Webpushr', 'pwa-for-wp'),					// Title
			'pwaforwp_webpushr_support_callback',					// CB
			'pwaforwp_compatibility_setting_section',				// Page slug
			'pwaforwp_compatibility_setting_section'				// Settings Section ID
		);
                               
                add_settings_section('pwaforwp_precaching_setting_section', esc_html__(' ','pwa-for-wp'), '__return_false', 'pwaforwp_precaching_setting_section');
		add_settings_field(
			'pwaforwp_precaching_setting',							// ID
			'',	
			'pwaforwp_precaching_setting_callback',							// CB
			'pwaforwp_precaching_setting_section',						// Page slug
			'pwaforwp_precaching_setting_section'						// Settings Section ID
		);  
		add_settings_section('pwaforwp_urlhandler_setting_section', esc_html__(' ','pwa-for-wp'), '__return_false', 'pwaforwp_urlhandler_setting_section');
		add_settings_field(
			'pwaforwp_urlhandler_setting',							// ID
			esc_html__('Enter URLs (with similar origin)', 'pwa-for-wp'),	
			'pwaforwp_urlhandler_setting_callback',							// CB
			'pwaforwp_urlhandler_setting_section',						// Page slug
			'pwaforwp_urlhandler_setting_section'						// Settings Section ID
		);  
                
                
                add_settings_section('pwaforwp_push_notification_section', esc_html__(' ','pwa-for-wp'), '__return_false', 'pwaforwp_push_notification_section');
		// Splash Screen Background Color
		add_settings_field(
			'pwaforwp_push_notification',							// ID
			'',	
			'pwaforwp_push_notification_callback',							// CB
			'pwaforwp_push_notification_section',						// Page slug
			'pwaforwp_push_notification_section'						// Settings Section ID
		);
                
                add_settings_section('pwaforwp_premium_features_section', esc_html__(' ','pwa-for-wp'), '__return_false', 'pwaforwp_premium_features_section');
		// Splash Screen Background Color
		add_settings_field(
			'pwaforwp_premium_features',							// ID
			'',	
			'pwaforwp_premium_features_callback',							// CB
			'pwaforwp_premium_features_section',						// Page slug
			'pwaforwp_premium_features_section'						// Settings Section ID
		);
                
                
                
		
}

function pwaforwp_addon_html(){
    
    $add_on_list = pwaforwp_list_addons();
    $pluginHtml = '';
    foreach ($add_on_list as $key => $plugin) {
    	$ctafp_active_text = '';
    	if(is_plugin_active($plugin['p-slug'])){                                           
	       $ctafp_active_text =  pwaforwp_get_license_section_html($plugin['p-short-prefix']);                                         
	    }else{                                            
	       $ctafp_active_text .= '<label class="pwaforwp-sts-txt">'.esc_html__('Status', 'pwa-for-wp').' :<span>'.esc_html__('Inactive', 'pwa-for-wp').'</span></label>'; 
	       if(!class_exists('PWAFORWPPROExtensionManager')){
		       $ctafp_active_text .= '<a target="_blank" href="'.$plugin['p-url'].'"><span class="pwaforwp-d-btn">'.esc_html__('Download', 'pwa-for-wp').'</span></a>';
		   }
	    }	
	    $pluginHtml .= '<div class="pwaforwp-ext-wrap">
                <div class="pwafowp-feature-ext">    
				<div class="pwaforwp-features-ele">
					<div class="pwaforwp-ele-ic" style="background: '.$plugin['p-background-color'].'">
                        <img src="'.$plugin['p-icon-img'].'">
					</div>
					<div class="pwaforwp-ele-tlt">
						<h3>'.esc_html__($plugin['p-title'],'pwa-for-wp').'</h3>
						<p>'.esc_html__($plugin['p-desc'],'pwa-for-wp').'</p>
					</div>
				</div>
				<div class="pwaforwp-sts-btn">                                    
                                   '.$ctafp_active_text.'                                                                           										
				</div>  
                </div>            
        </div>';
    }

    
    $ext_html = '<div class="pwaforwp-ext-list-table">'.$pluginHtml.'</div>';

    return $ext_html;
    
}
function pwaforwp_list_addons(){
	$add_on_list = array(
         'ctafp'  => array(
                    'p-slug' => 'call-to-action-for-pwa/call-to-action-for-pwa.php',
                    'p-name' => 'Call To Action',
                    'p-short-prefix'=> 'CTAFP',
                    'p-title' => 'Call to Action for PWA',
                    'p-url'	 => 'https://pwa-for-wp.com/extensions/call-to-action-for-pwa/',
                    'p-icon-img' => PWAFORWP_PLUGIN_URL.'images/call-to-action.png',
                    'p-background-color'=> '#333333',
                    'p-desc' => 'Call to Action extension makes it easy for your users to add the website to the home screen',
                    'p-tab'	 => true
         ),
         'lilfp'  => array(
                    'p-slug' => 'loading-icon-library-for-pwa/loading-icon-library-for-pwa.php',
                    'p-name' => 'Loading Icon Library for PWA',
                    'p-short-prefix'=> 'LILFP',
                    'p-title' => 'Loading Icon Library for PWA',
                    'p-url'	 => 'https://pwa-for-wp.com/extensions/loading-icon-library-for-pwa/',
                    'p-icon-img' => PWAFORWP_PLUGIN_URL.'images/loading-icon-library.png',
                    'p-background-color'=> '#2f696d',
                    'p-desc' => 'Loading Icon Library extension multiple icons for PWA app',
                    'p-tab'	 => false
         ),
         'dafp'  => array(
                    'p-slug' => 'data-analytics-for-pwa/data-analytics-for-pwa.php',
                    'p-name' => 'Data Analytics for PWA',
                    'p-short-prefix'=> 'DAFP',
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
                    'p-title' => 'Pull to Refresh for PWA',
                    'p-url'	 => 'https://pwa-for-wp.com/extensions/pull-to-refresh-for-pwa/',
                    'p-icon-img' => PWAFORWP_PLUGIN_URL.'images/pull-to-refresh-for-pwa.png',
                    'p-background-color'=> '#336363',
                    'p-desc' => 'Pull to Refresh for PWA extension help users to refresh the page inside PWA app',
                    'p-tab'	 => false
         ),
         'spbfp'  => array(
                    'p-slug' => 'scroll-progress-bar-for-pwa/scroll-progress-bar-for-pwa.php',
                    'p-name' => 'Scroll Progress Bar for PWA',
                    'p-short-prefix'=> 'SPBFP',
                    'p-title' => 'Scroll Progress Bar for PWA',
                    'p-url'	 => 'https://pwa-for-wp.com/extensions/scroll-progress-bar-for-pwa/',
                    'p-icon-img' => PWAFORWP_PLUGIN_URL.'images/scroll-progress-bar-for-pwa.png',
                    'p-background-color'=> '#3e3e3e',
                    'p-desc' => 'Scroll Progress Bar for PWA extension indicator to display the current reading position',
                    'p-tab'	 => false
         ),
         'ptafp'  => array(
                    'p-slug' => 'pwa-to-apk-plugin/pwa-to-apk-plugin.php',
                    'p-name' => 'PWA to APK Plugin',
                    'p-short-prefix'=> 'PTAFP',
                    'p-title' => 'PWA to APK Plugin',
                    'p-url'	 => 'https://pwa-for-wp.com/extensions/pwa-to-apk-plugin/',
                    'p-icon-img' => PWAFORWP_PLUGIN_URL.'images/pwa-to-apk-plugin.png',
                    'p-background-color'=> '#afa173',
                    'p-desc' => 'PWA to APK Plugin for PWA extension to create apk for your website',
                    'p-tab'	 => false
         ),
         'ofpwa'  => array(
                    'p-slug' => 'offline-forms-for-pwa-for-wp/offline-forms-for-pwa-for-wp.php',
                    'p-name' => 'Offline Forms for PWA for WP',
                    'p-short-prefix'=> 'OFPWA',
                    'p-title' => 'Offline Forms for PWA for WP',
                    'p-url'	 => 'https://pwa-for-wp.com/extensions/offline-forms-for-pwa-for-wp/',
                    'p-icon-img' => PWAFORWP_PLUGIN_URL.'images/offline-forms-for-pwa-for-wp.png',
                    'p-background-color'=> '#acb1b5',
                    'p-desc' => 'Offline Forms for PWA extension to store forms for your website',
                    'p-tab'	 => false
         ),
         'bnpwa'  => array(
                    'p-slug' => 'buddypress-for-pwaforwp/buddypress-for-pwaforwp.php',
                    'p-name' => 'BuddyPress for PWAforWP',
                    'p-short-prefix'=> 'BNPWA',
                    'p-title' => 'Buddypress for PWA for WP',
                    'p-url'	 => 'https://pwa-for-wp.com/extensions/buddypress-for-pwaforwp/',
                    'p-icon-img' => PWAFORWP_PLUGIN_URL.'images/buddypress-for-pwaforwp.png',
                    'p-background-color'=> '#d94e27',
                    'p-desc' => 'Buddypress extension to send push notification while core notification will work ex: A member mentions you in an update / A member replies to an update or comments your post',
                    'p-tab'	 => false
         ),
         'qafp'  => array(
                    'p-slug' => 'quick-action-for-pwa/quick-action-for-pwa.php',
                    'p-name' => 'Quick Action for PWA',
                    'p-short-prefix'=> 'QAFP',
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
                    'p-title' => 'Rewards on PWA install',
                    'p-url'	 => 'https://pwa-for-wp.com/extensions/rewards-on-pwa-install/',
                    'p-icon-img' => PWAFORWP_PLUGIN_URL.'images/rewards-on-pwa-install.png',
                    'p-background-color'=> '#cddae2',
                    'p-desc' => esc_html__('Rewards to the most loyal base of customers', 'pwa-for-wp'),
                    'p-tab'	 => false
         ),
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
	       <div class="pwaforwp-sub-tab-headings" style="margin-top: 10px;">                            
	           <?php echo $tabs; ?>   
	           <span data-tab-id="pwaforwp-addon" class="selected"><?php echo esc_html__('Add Ons', 'pwa-for-wp'); ?></span> 
	       </div>

	       <div id="pwaforwp-ext-container-for-all" class="pwaforwp-subheading" style="margin-top: 10px;">
	            <?php echo $container; ?>       
	           <div class="pwaforwp-ext-container selected" id="pwaforwp-addon">
	                <?php echo pwaforwp_addon_html(); ?>
	           </div>
	           
	       </div>
	   </div>
                                
        <?php 
         
     }else{
        
         echo ' <div pwaforwp-extenstion-list>      
             '.pwaforwp_addon_html().'
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
		<td><label><?php echo esc_html__('Default caching strategy', 'pwa-for-wp'); ?></label></td>
		<td><select name="pwaforwp_settings[default_caching]">
			<?php if($arrayOPT){
				foreach ($arrayOPT as $key => $opval) {
					$sel = "";
					if($settings['default_caching']==$key){$sel = "selected"; }
					echo '<option value="'.esc_attr($key).'" '.esc_attr($sel).'>'.esc_html__($opval, 'pwa-for-wp').'</option>';
				}
			}
			 ?>

		</select>
		<br/>
		<label>
		<input type="checkbox" name="pwaforwp_settings[change_default_on_login]" value="1" <?php if( isset($settings['change_default_on_login']) && $settings['change_default_on_login']==1 ){ echo 'checked'; }?>>
		<?php echo esc_html('If you have a login for normal users (it help users to get updates content)', 'pwa-for-wp'); ?>
		</label>
		</td>
	</tr>
	<tr>
		<td><label><?php echo esc_html__('Caching strategy for CSS and JS Files', 'pwa-for-wp'); ?></label></td>
		<td><select name="pwaforwp_settings[default_caching_js_css]">
			<?php if($arrayOPT){
				foreach ($arrayOPT as $key => $opval) {
					$sel = "";
					if($settings['default_caching_js_css']==$key){$sel = "selected"; }
					echo '<option value="'.esc_attr($key).'" '.esc_attr($sel).'>'.esc_html__($opval, 'pwa-for-wp').'</option>';
				}
			}
			 ?>
		</select></td>
	</tr>
	<tr>
		<td><label><?php echo esc_html__('Caching strategy for images', 'pwa-for-wp'); ?></label></td>
		<td><select name="pwaforwp_settings[default_caching_images]">
			<?php if($arrayOPT){
				foreach ($arrayOPT as $key => $opval) {
					$sel = "";
					if($settings['default_caching_images']==$key){$sel = "selected"; }
					echo '<option value="'.esc_attr($key).'" '.esc_attr($sel).'>'.esc_html__($opval, 'pwa-for-wp').'</option>';
				}
			}
			 ?>
		</select></td>
	</tr>
	<tr>
		<td><label><?php echo esc_html__('Caching strategy for fonts', 'pwa-for-wp'); ?></label></td>
		<td><select name="pwaforwp_settings[default_caching_fonts]">
			<?php if($arrayOPT){
				foreach ($arrayOPT as $key => $opval) {
					$sel = "";
					if($settings['default_caching_fonts']==$key){$sel = "selected"; }
					echo '<option value="'.esc_attr($key).'" '.esc_attr($sel).'>'.esc_html__($opval, 'pwa-for-wp').'</option>';
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
function pwaforwp_disallow_data_tracking_setting_callback(){
	// Get Settings
	$settings = pwaforwp_defaultSettings(); 
	$allow_tracking = get_option( 'wisdom_allow_tracking' );
	$plugin = basename( PWAFORWP_PLUGIN_FILE, '.php' );

	$checked = "";$tracker_url = '';

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
	<input type="checkbox" <?php echo $checked; ?> onclick="window.location = '<?php echo $tracker_url; ?>'">
	<p><?php echo esc_html__('We guarantee no sensitive data is collected', 'pwa-for-wp'); ?>. <a target="_blank" href="https://pwa-for-wp.com/docs/article/usage-data-tracking/" target="_blank"><?php echo esc_html__('Learn more', 'pwa-for-wp'); ?></a>.</p>
	<?php
}

function pwaforwp_url_exclude_from_cache_list_callback(){
	// Get Settings
	$settings = pwaforwp_defaultSettings(); 
	?>
        <label><textarea placeholder="https://example.com/admin.php?page=newpage, https://example.com/admin.php?page=newpage2 "  rows="4" cols="70" id="pwaforwp_settings[excluded_urls]" name="pwaforwp_settings[excluded_urls]"><?php echo (isset($settings['excluded_urls']) ? esc_attr($settings['excluded_urls']): ''); ?></textarea></label>
        <p><?php echo esc_html__('Note: Put in comma separated, do not add enter in urls', 'pwa-for-wp'); ?></p>
	<p><?php echo esc_html__('Put the list of urls which you do not want to cache by service worker', 'pwa-for-wp'); ?></p>	
	
	<?php
}

function pwaforwp_urlhandler_setting_callback(){
	$settings = pwaforwp_defaultSettings(); 
	echo "<textarea name='pwaforwp_settings[urlhandler]' rows='10' cols='80' placeholder='https://music.example.com\nhttps://*.music.example.com\nhttps://chat.example.com\nhttps://*.music.example.com'>". (isset($settings['urlhandler'])? $settings['urlhandler']: '') ."</textarea>";
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
                echo "<textarea cols='100' rows='20' readonly>".json_encode($data, JSON_PRETTY_PRINT)."</textarea>";
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
                       <label><textarea placeholder="https://example.com/2019/06/06/hello-world/, https://example.com/2019/06/06/hello-world-2/ "  rows="4" cols="50" id="pwaforwp_settings_precaching_urls" name="pwaforwp_settings[precaching_urls]"><?php if(isset($settings['precaching_urls'])){ echo esc_attr($settings['precaching_urls']);} ?></textarea></label>
                       <p><?php echo esc_html__('Note: Put in comma separated', 'pwa-for-wp'); ?></p>
                       <p><?php echo esc_html__('Put the list of urls which you want to pre cache by service worker', 'pwa-for-wp'); ?></p>
                   </td>
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
	?>
        <label><input type="text" id="pwaforwp_settings[force_update_sw_setting]" name="pwaforwp_settings[force_update_sw_setting]" value="<?php if(isset($settings['force_update_sw_setting'])){ 
        	if(!version_compare($settings['force_update_sw_setting'],PWAFORWP_PLUGIN_VERSION, '>=') ){
				$settings['force_update_sw_setting'] = PWAFORWP_PLUGIN_VERSION;
			}
        	echo esc_attr($settings['force_update_sw_setting']);
        }else{ echo PWAFORWP_PLUGIN_VERSION; } ?>"></label>      
        <code>Current Version <?php echo PWAFORWP_PLUGIN_VERSION; ?></code>  
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
        					<option value="">Select</option>
        					<option value="pushnotifications_io" <?php selected('pushnotifications_io', $selectedService) ?>>PushNotifications.io (Recommended)</option>
        					<option value="fcm_push" <?php selected('fcm_push', $selectedService) ?> >FCM push notification</option>
        				</select>
        			</td>
        		</tbody>
        	</table>
            <table class="pwaforwp-push-notificatoin-table" <?php echo $fcm_service_style; ?>>
                <tbody>
                    <tr>
                        <th><?php echo esc_html__('FCM Server API Key', 'pwa-for-wp') ?></th>  
                        <td><input class="regular-text" type="text" name="pwaforwp_settings[fcm_server_key]" id="pwaforwp_settings[fcm_server_key]" value="<?php echo (isset($settings['fcm_server_key'])? esc_attr($settings['fcm_server_key']):'') ; ?>"></td>
                    </tr>
                    <tr>
                        <th><?php echo esc_html__('Config', 'pwa-for-wp') ?></th>  
                        <td>
                            <textarea class="regular-text" placeholder="{ <?="\n"?>apiKey: '<Your Api Key>', <?="\n"?>authDomain: '<Your Auth Domain>',<?="\n"?>databaseURL: '<Your Database URL>',<?="\n"?>projectId: '<Your Project Id>',<?="\n"?>storageBucket: '<Your Storage Bucket>', <?="\n"?>messagingSenderId: '<Your Messaging Sender Id>' <?="\n"?>}" rows="8" cols="60" id="pwaforwp_settings[fcm_config]" name="pwaforwp_settings[fcm_config]"><?php echo isset($settings['fcm_config']) ? esc_attr($settings['fcm_config']) : ''; ?></textarea>
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
            <div class="pwaforwp-pn-recommended-options" <?php echo $pushnotifications_style; ?>>
            	<div class="notification-banner" style="width:90%">
            			<?php if(class_exists('Push_Notification_Admin')){ 
            				$auth_settings = push_notification_auth_settings();
            				if(!isset($auth_settings['user_token'])){
            					echo '<div class="pwaforwp-center"><p>This feature requires to setup Push Notification </p> <a href="'.esc_url_raw(admin_url('admin.php?page=push-notification')).'" target="_blank" class="button button-primary">'.esc_html__('Go to setup', 'pwa-for-wp').'</a></div>';
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
	            			<span data-activate-url="<?php echo $activate_url; ?>" 
	            				 class="pwaforwp-install-require-plugin button button-primary <?php echo $class; ?>" data-secure="<?php echo wp_create_nonce('verify_request'); ?>"
	            				id="pushnotification">
	            				<?php echo esc_html__('Install Plugin', 'pwa-for-wp'); ?>
	            			</span>
	            		</div>
            			<?php
            		} ?>
	            	
            	</div>
            </div>
        </div>
        <div class="pwaforwp-notification-condition-section" <?php echo $fcm_service_style; ?> >
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
                             echo '<p>'.esc_html__('Notification Title', 'pwa-for-wp').' <input type="text" name="pwaforwp_settings[on_add_post_notification_title]" id="on_add_post_notification_title" placeholder="New Post" value="'.esc_attr($settings['on_add_post_notification_title']).'"></p>';   
                            }else{
                             echo  '<p class="pwaforwp-hide">'.esc_html__('Notification Title', 'pwa-for-wp').' <input type="text" name="pwaforwp_settings[on_add_post_notification_title]" id="on_add_post_notification_title" placeholder="New Post" value="'.esc_attr($settings['on_add_post_notification_title']).'"></p>';  
                            }
                            ?>
                            
                        </td>
                    </tr>
                    <tr>
                        <th><?php echo esc_html__('Update Post', 'pwa-for-wp') ?></th>  
                        <td><input type="checkbox" name="pwaforwp_settings[on_update_post]" id="pwaforwp_settings[on_update_post]" class="pwaforwp-fcm-checkbox" <?php echo (isset( $settings['on_update_post'] ) &&  $settings['on_update_post'] == 1 ? 'checked="checked"' : ''); ?> value="1">
                            <?php
                            if(isset($settings['on_update_post']) && $settings['on_update_post']== 1){
                             echo '<p>'.esc_html__('Notification Title', 'pwa-for-wp').' <input type="text" name="pwaforwp_settings[on_update_post_notification_title]" id="on_update_post_notification_title" placeholder="Update Post" value="'.(isset($settings['on_update_post_notification_title']) ? esc_attr($settings['on_update_post_notification_title']): '').'"></p>';   
                            }else{
                             echo  '<p class="pwaforwp-hide">'.esc_html__('Notification Title', 'pwa-for-wp').' <input type="text" name="pwaforwp_settings[on_update_post_notification_title]" id="on_update_post_notification_title" placeholder="Update Post" value="'.(isset($settings['on_update_post_notification_title']) ? esc_attr($settings['on_update_post_notification_title']) : '').'"></p>';  
                            }
                            ?>
                        </td>
                    </tr>
                     <tr>
                        <th><?php echo esc_html__('Add New Page', 'pwa-for-wp') ?></th>  
                        <td><input type="checkbox" name="pwaforwp_settings[on_add_page]" id="pwaforwp_settings[on_add_page]" class="pwaforwp-fcm-checkbox" <?php echo (isset( $settings['on_add_page'] ) &&  $settings['on_add_page'] == 1 ? 'checked="checked"' : ''); ?> value="1">
                            
                            <?php
                            if(isset($settings['on_add_page']) && $settings['on_add_page'] == 1){
                             echo '<p>'.esc_html__('Notification Title', 'pwa-for-wp').' <input type="text" name="pwaforwp_settings[on_add_page_notification_title]" id="on_add_page_notification_title" placeholder="New Page" value="'.(isset($settings['on_add_page_notification_title']) ? esc_attr($settings['on_add_page_notification_title']) : '').'"></p>';   
                            }else{
                             echo  '<p class="pwaforwp-hide">'.esc_html__('Notification Title', 'pwa-for-wp').' <input type="text" name="pwaforwp_settings[on_add_page_notification_title]" id="on_add_page_notification_title" placeholder="New Page" value="'.(isset($settings['on_add_page_notification_title']) ? esc_attr($settings['on_add_page_notification_title']) : '').'"></p>';  
                            }
                            ?>
                            
                        </td>
                    </tr>
                    <tr>
                        <th><?php echo esc_html__('Update Page', 'pwa-for-wp') ?></th>  
                        <td><input type="checkbox" name="pwaforwp_settings[on_update_page]" id="pwaforwp_settings[on_update_page]" class="pwaforwp-fcm-checkbox" <?php echo (isset( $settings['on_update_page'] ) &&  $settings['on_update_page'] == 1 ? 'checked="checked"' : ''); ?> value="1">
                            <?php
                            if(isset($settings['on_update_page']) && $settings['on_update_page'] == 1){
                             echo '<p>'.esc_html__('Notification Title', 'pwa-for-wp').' <input type="text" name="pwaforwp_settings[on_update_page_notification_title]" id="on_update_page_notification_title" placeholder="Update Post" value="'.(isset($settings['on_update_page_notification_title']) ? esc_attr($settings['on_update_page_notification_title']) : '').'"></p>';   
                            }else{
                             echo  '<p class="pwaforwp-hide">'.esc_html__('Notification Title', 'pwa-for-wp').' <input type="text" name="pwaforwp_settings[on_update_page_notification_title]" id="on_update_page_notification_title" placeholder="Update Post" value="'.(isset($settings['on_update_page_notification_title']) ? esc_attr($settings['on_update_page_notification_title']) : '').'"></p>';  
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
                        <th><?php echo esc_html__('Title', 'pwa-for-wp') ?>:<br/><input style="width: 100%" placeholder="Title" type="text" id="pwaforwp_notification_message_title" name="pwaforwp_notification_message_title" value="<?php echo get_bloginfo(); ?>">
                            <br>
			                   
                        </th>  
                        <td></td>
                    </tr>
                     <tr>
                        <th>
                        	<?php echo esc_html__('Redirection Url Onclick of notification', 'pwa-for-wp') ?>:<br/>
                        	<input style="width: 100%" placeholder="URL" type="text" id="pwaforwp_notification_message_url" name="pwaforwp_notification_message_url" value="<?php echo pwaforwp_home_url(); ?>">
                            <br>
			                   
                        </th>  
                        <td></td>
                    </tr>
                    <tr>
                        <th>
                        	<?php echo esc_html__('Image Url', 'pwa-for-wp') ?>:<br/>
                        	<input style="width: 100%" placeholder="Image URL" type="text" id="pwaforwp_notification_message_image_url" name="pwaforwp_notification_message_image_url" value="">
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
            <tr><th><strong><?php echo esc_html__('Title', 'pwa-for-wp'); ?></strong></th><td><input type="text" name="pwaforwp_settings[custom_banner_title]" id="pwaforwp_settings[custom_banner_title]" class="" value="<?php echo isset( $settings['custom_banner_title'] ) ? esc_attr( $settings['custom_banner_title']) : 'Add '.get_bloginfo().' to your Homescreen!'; ?>"></td></tr> 
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
			esc_html__('Icon of your application when installed on the phone. Must be a PNG image exactly'),
			esc_html__('192x192 in size.'),
			esc_html__('- For Apple mobile exact sizes is necessary')
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
	<div class="ios-splash-images" <?php if(isset($settings['switch_apple_splash_screen']) && !$settings['switch_apple_splash_screen']){ echo 'style="display:none"'; }?>>
		<div class="field" style="margin-bottom: 10px;">
			<label style="display: inline-block;width: 50%;"><?php echo esc_html__('iOS Splash Screen Method', 'pwa-for-wp') ?></label>
			<select name="pwaforwp_settings[iosSplashScreenOpt]" id="ios-splash-gen-opt">
				<option value="">Select</option>
				<option value="generate-auto" <?php echo isset($settings['iosSplashScreenOpt']) && $settings['iosSplashScreenOpt']=='generate-auto'? 'selected': ''; ?>>Automatic</option>
				<option  value="manually" <?php echo isset($settings['iosSplashScreenOpt']) && $settings['iosSplashScreenOpt']=='manually'? 'selected': ''; ?>>Manual</option>
			</select>
		</div>

		<?php
		$currentpic = $splashIcons = ios_splashscreen_files_data();
		reset($currentpic);
		$previewImg = '';
		if( isset( $settings['ios_splash_icon'][key($currentpic)] ) ){
			$previewImg = '<img src="'.pwaforwp_https($settings['ios_splash_icon'][key($currentpic)]) .'" width="50" height="50">';
		}
		echo '<div class="panel pwaforwp-hide" id="generate-auto-1"  style="max-height: 100%;">
				<div class="ios-splash-screen-creator" style="display:inline-block; width:90%">
					<div class="field"><label>'.esc_html__('Select image (Only PNG)', 'pwa-for-wp').'</label><input type="file" id="file-upload-ios" accept="image/png"><img style="display:none" id="thumbnail"></div>
					<div class="field"><label>'.esc_html__('Background color', 'pwa-for-wp').'</label><input type="text" id="ios-splash-color" value="#FFFFFF"></div>
					<div style="padding-left: 25%;"><input type="button" onclick="pwa_getimageZip(this)" class="button" value="Generate">
					<span id="pwa-ios-splashmessage" style="font-size:17px"> </span></div>
				</div>
				<div class="splash_preview_wrp" style="display:inline-block; width:9%">
				'.$previewImg.'
				</div>
			</div>
			
			';
		?>
		<div class="panel pwaforwp-hide" id="manually-1" style="max-height: 100%;">
		<?php
		foreach ($splashIcons as $key => $splashValue) {
			
		?>
			<div class="ios-splash-images-field">
				<label><?php echo $splashValue['name']." ($key) [".ucfirst($splashValue['orientation'])."]" ?></label>
				<input type="text" name="pwaforwp_settings[ios_splash_icon][<?php echo $key ?>]" id="pwaforwp_settings[ios_splash_icon][<?php echo $key ?>]" class="pwaforwp-splash-icon regular-text" size="50" value="<?php echo isset( $settings['ios_splash_icon'][$key] ) ? esc_attr( pwaforwp_https($settings['ios_splash_icon'][$key])) : ''; ?>">
				<button type="button" class="button pwaforwp-ios-splash-icon-upload" data-editor="content">
					<span class="dashicons dashicons-format-image" style="margin-top: 4px;"></span> <?php echo esc_html__('Choose Icon', 'pwa-for-wp'); ?>
				</button>
			</div>
		<?php } ?>
		</div>
		
	</div>

	<?php
}

function pwaforwp_offline_page_callback(){
	// Get Settings
	$settings = pwaforwp_defaultSettings(); ?>
	<!-- WordPress Pages Dropdown -->
	<label for="pwaforwp_settings[offline_page]">
	<?php 
        $allowed_html = pwaforwp_expanded_allowed_tags();
        echo wp_kses(wp_dropdown_pages( array( 
			'name'              => esc_attr('pwaforwp_settings[offline_page]'), 
			'echo'              => 0, 
			'show_option_none'  => esc_attr( '&mdash; Default &mdash;' ), 
			'option_none_value' => '0', 
			'selected'          =>  isset($settings['offline_page']) ? esc_attr($settings['offline_page']) : '',
		)), $allowed_html); ?>
	</label>
	
	<p class="description">
		<?php printf( esc_html__( 'Offline page is displayed, when the device is offline and the requested page is not already cached. Current offline page is %s', 'pwa-for-wp' ), get_permalink($settings['offline_page']) ? get_permalink( $settings['offline_page'] ) : esc_url(get_bloginfo( 'wpurl' )) ); ?>
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
        echo wp_kses(wp_dropdown_pages( array( 
			'name'              => esc_attr('pwaforwp_settings[404_page]'), 
			'echo'              => 0, 
			'show_option_none'  => esc_attr( '&mdash; Default &mdash;' ), 
			'option_none_value' => '0', 
			'selected'          => isset($settings['404_page']) ? esc_attr($settings['404_page']) : '',
		)), $allowed_html); ?>
	</label>
	
	<p class="description">
		<?php printf( esc_html__( '404 page is displayed and the requested page is not found. Current 404 page is %s', 'pwa-for-wp' ), esc_url(get_permalink($settings['404_page']) ? get_permalink( $settings['404_page'] ) : '' )); ?>
	</p>

	<?php
}
function pwaforwp_start_page_callback(){
	// Get Settings
	$settings = pwaforwp_defaultSettings(); ?>
	<!-- WordPress Pages Dropdown -->
	<label for="pwaforwp_settings[start_page]">
	<?php 
        $allowed_html = pwaforwp_expanded_allowed_tags();        
        echo wp_kses(wp_dropdown_pages( array( 
			'name'              => esc_attr('pwaforwp_settings[start_page]'), 
			'echo'              => 0, 
			'show_option_none'  => esc_attr( '&mdash; Homepage &mdash;' ), 
			'option_none_value' => '0', 
			'selected'          => isset($settings['start_page']) ? esc_attr($settings['start_page']) : '',
		)), $allowed_html); ?>
	</label>
	
	<p class="description">
		<?php 
                $current_page = isset($settings['start_page'])? get_permalink($settings['start_page']):''; 
                printf( esc_html__( 'From where you want to launch PWA APP. Current start page is %s', 'pwa-for-wp' ), $current_page); ?>
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
        	<label for="show_banner_without_scroll" style="font-weight:600">Show banner without scroll</label>
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
			$swUrl = service_workerUrls($swUrl, $swFile);
			$nonamp_sw_status = @pwaforwp_checkStatus($swUrl);
		}
		if(!$nonamp_sw_status && $nonAmpStatusMsg==''){
			$nonAmpStatusMsg = 'Service Worker not working';
		}
		if ( !is_ssl() && $nonAmpStatusMsg=='' ) {
			$nonAmpStatusMsg = 'PWA failed to initialized, the site is not HTTPS';
			$nonAmpLearnMoreLink = '<a href="https://pwa-for-wp.com/docs/article/site-need-https-for-pwa/" target="_blank">'.esc_html__('Learn more', 'pwa-for-wp').'</a>';
		}

		if($nonAmpStatusMsg==''){
			$nonampStatusIcon = '<span class="dashicons dashicons-yes" style="color: #46b450;"></span>';
			$nonAmpStatusMsg = 'PWA is working';
		}

		if($is_amp){
			$ampStatusMsg = $ampStatusIcon = '';
			if(!isset( $settings['amp_enable'] ) || (isset( $settings['amp_enable'] ) && $settings['amp_enable'] != 1) ){
				$ampStatusMsg = 'PWA is disabled';
		    }

			$amp_manifest_status = true;
			if(!pwaforwp_is_enabled_pwa_wp()){
			  $swUrl = esc_url(pwaforwp_manifest_json_url(true));
			  $amp_manifest_status = @pwaforwp_checkStatus($swUrl);
			}
			if(!$amp_manifest_status && $ampStatusMsg==''){
				$ampStatusMsg = 'Manifest not working';
			}
			
			$swFile = "pwa-amp-sw".pwaforwp_multisite_postfix().".js";
			$amp_sw_status = true;
			if(!pwaforwp_is_enabled_pwa_wp()){
				$swUrl = esc_url(pwaforwp_home_url().$swFile);
				$swUrl = service_workerUrls($swUrl, $swFile);
				$amp_sw_status = @pwaforwp_checkStatus($swUrl);
			}
			if(!$amp_sw_status && $ampStatusMsg==''){
				$ampStatusMsg = 'Service Worker not working';
			}
			
			if ( !is_ssl() && $ampStatusMsg=='') {
				$ampStatusMsg = '';
				if(isset( $settings['normal_enable'] ) && $settings['normal_enable'] != 1) {
					$ampStatusMsg = 'PWA failed to initialized, the site is not HTTPS';
				}
			}elseif($ampStatusMsg==''){
				$ampStatusIcon = '<span class="dashicons dashicons-yes" style="color: #46b450;"></span>';
				$ampStatusMsg = 'PWA is working on AMP';
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
                    <td style="width:40%"><p><?php echo $nonampStatusIcon .' '. esc_html__( $nonAmpStatusMsg, 'pwa-for-wp' ). ' '.$nonAmpLearnMoreLink ?></p></td>
					<?php if($is_amp) { ?>
                    <td style="width:40%"><p><?php echo $ampStatusIcon.' '.esc_html__( $ampStatusMsg, 'pwa-for-wp' ); ?></p></td>
					<?php } ?>
                </tr>
                
                <tr>
                    <th><?php echo esc_html__( 'Enable / Disable', 'pwa-for-wp' ) ?></th>
	                <td> 
	                	<label><input type="checkbox"  <?php echo (isset( $settings['normal_enable'] ) && $settings['normal_enable'] == 1 ? 'checked="checked"' : ''); ?> value="1" class="pwaforwp-checkbox-tracker" data-id="pwaforwp_settings[normal_enable]"> 
	                		<input type="hidden" name="pwaforwp_settings[normal_enable]" id="pwaforwp_settings[normal_enable]" value="<?php echo $settings['normal_enable']; ?>" >
	                	</label>
	               	</td>
                    <td>
                        <?php if($is_amp) { ?>
                        <label><input type="checkbox"  <?php echo (isset( $settings['amp_enable'] ) &&  $settings['amp_enable'] == 1 ? 'checked="checked"' : ''); ?> value="1"  class="pwaforwp-checkbox-tracker" data-id="pwaforwp_settings[amp_enable]"> 
                        	<input type="hidden" name="pwaforwp_settings[amp_enable]" id="pwaforwp_settings[amp_enable]" value="<?php echo $settings['amp_enable']; ?>" >
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

	wp_register_script('pwaforwp-all-page-js', PWAFORWP_PLUGIN_URL . 'assets/js/all-page-script.js', array( ), PWAFORWP_PLUGIN_VERSION, true);
        
        $object_name = array(
            'ajax_url'                  => admin_url( 'admin-ajax.php' ),
            'uploader_title'            => esc_html__('Application Icon', 'pwa-for-wp'),
            'splash_uploader_title'     => esc_html__('Splash Screen Icon', 'pwa-for-wp'),
            'uploader_button'           => esc_html__('Select Icon', 'pwa-for-wp'),
            'file_status'               => esc_html__('Check permission or download from manual', 'pwa-for-wp'),
            'pwaforwp_security_nonce'   => wp_create_nonce('pwaforwp_ajax_check_nonce'),
            'iosSplashIcon'				=> ios_splashscreen_files_data(),
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


        wp_enqueue_style( 'pwaforwp-main-css', PWAFORWP_PLUGIN_URL . 'assets/css/main-css.min.css',array(), PWAFORWP_PLUGIN_VERSION,'all' );      
		wp_style_add_data( 'pwaforwp-main-css', 'rtl', 'replace' );      
        // Main JS
        wp_enqueue_script('pwaforwp-zip-js', PWAFORWP_PLUGIN_URL . 'assets/js/jszip.min.js', array(), PWAFORWP_PLUGIN_VERSION, true);
        wp_register_script('pwaforwp-main-js', PWAFORWP_PLUGIN_URL . 'assets/js/main-script.min.js', array( 'wp-color-picker', 'wp-color-picker-alpha', 'plugin-install', 'wp-util', 'wp-a11y','updates' ), PWAFORWP_PLUGIN_VERSION, true);
        
        wp_enqueue_script('pwaforwp-main-js');
}
add_action( 'admin_enqueue_scripts', 'pwaforwp_enqueue_style_js' );



/**
 * This is a ajax handler function for sending email from user admin panel to us. 
 * @return type json string
 */
function pwaforwp_send_query_message(){   
    
        if ( ! isset( $_POST['pwaforwp_security_nonce'] ) ){
            return; 
        }
        if ( !wp_verify_nonce( $_POST['pwaforwp_security_nonce'], 'pwaforwp_ajax_check_nonce' ) ){
           return;  
        }
        
        $message    = sanitize_textarea_field($_POST['message']);        
        $customer_type    = sanitize_text_field($_POST['customer_type']);        
        $customer_type = empty($customer_type)? $customer_type : 'No';
        $message .= "<table>
        				<tr><td>Are you existing Premium Customer?</td><td>".$customer_type."</td></tr>
        				<tr><td>Plugin</td><td>PWA for wp </td></tr>
        				<tr><td>Version</td><td>".PWAFORWP_PLUGIN_VERSION."</td></tr>
        			</table>";
        $user       = wp_get_current_user();
        
        if($user){
            
            $user_data  = $user->data;        
            $user_email = $user_data->user_email;       
            //php mailer variables
            $to = 'team@magazine3.com';
            $subject = "PWA Customer Query";
            $headers = 'From: '. esc_attr($user_email) . "\r\n" .
            'Reply-To: ' . esc_attr($user_email) . "\r\n";
            // Load WP components, no themes.                      
            $sent = wp_mail($to, $subject, strip_tags($message), $headers);        
            
            if($sent){
            echo json_encode(array('status'=>'t'));            
            }else{
            echo json_encode(array('status'=>'f'));            
            }
            
        }
                        
           wp_die();           
}

add_action('wp_ajax_pwaforwp_send_query_message', 'pwaforwp_send_query_message');

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
                
                $response = '';                                                                             
                $response.= '<div class="pwaforwp-ext-active">';
                if($license_status == 'active'){
                
                    $response.= '<span class="dashicons dashicons-yes pwaforwp-'.strtolower($on).'-dashicons" style="color: #46b450;"></span>';    
                    
                }else{
                
                    $response.= '<span class="dashicons dashicons-no-alt pwaforwp-'.strtolower($on).'-dashicons" style="color: #dc3232;"></span>';
                    
                }
                                                
                $response.= '<input type="password" placeholder="'.esc_attr__('Enter License Key', 'pwa-for-wp').'" id="'.strtolower($on).'_addon_license_key" name="pwaforwp_settings['.strtolower($on).'_addon_license_key]" value="'.esc_attr($license_key).'">';
                
                $response.= '<input type="hidden" id="'.strtolower($on).'_addon_license_key_status" name="pwaforwp_settings['.strtolower($on).'_addon_license_key_status]" value="'.esc_attr($license_status).'">';                
                
                if($license_status == 'active'){
                
                    $response.= '<a license-status="inactive" add-on="'.strtolower($on).'" class="button button-default pwaforwp_license_activation">'.esc_html__('Deactivate', 'pwa-for-wp').'</a>';
                    
                }else{
                
                    $response.= '<a license-status="active" add-on="'.strtolower($on).'" class="button button-default pwaforwp_license_activation">'.esc_html__('Activate', 'pwa-for-wp').'</a>';
                    
                }
                
                if($license_status_msg !='active'){
                    
                    $response.= '<p style="color:red;" add-on="'.strtolower($on).'" class="pwaforwp_license_status_msg">'.$license_status_msg.'</p>';
                }                
                                                
                 $response.= '<p>'.esc_html__('Enter addon license key to activate updates & support.','pwa-for-wp').'</p>';
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
        if ( !wp_verify_nonce( $_POST['pwaforwp_security_nonce'], 'pwaforwp_ajax_check_nonce' ) ){
             return;  
        }    
        
        $add_on           = sanitize_text_field($_POST['add_on']);
        $license_status   = sanitize_text_field($_POST['license_status']);
        $license_key      = sanitize_text_field($_POST['license_key']);
        
        if($add_on && $license_status && $license_key){
            
          $result = pwaforwp_license_status($add_on, $license_status, $license_key);
          
          echo json_encode($result);
                        
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
			$message =  ( is_wp_error( $response ) && ! empty( $response->get_error_message() ) ) ? $response->get_error_message() : __( 'An error occurred, please try again.' );
		} else {
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );
                        
			if ( false === $license_data->success ) {
                            
                                $current_status = $license_data->error;
                                
				switch( $license_data->error ) {
					case 'expired' :
						$message = sprintf(
							__( 'Your license key expired on %s.' ),
							date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
						);
						break;
					case 'revoked' :
						$message = __( 'Your license key has been disabled.' );
						break;
					case 'missing' :
						$message = __( 'Invalid license.' );
						break;
					case 'invalid' :
					case 'site_inactive' :
						$message = __( 'Your license is not active for this URL.' );
						break;
					case 'item_name_mismatch' :
						$message = __( 'This appears to be an invalid license key.' );
						break;
					case 'no_activations_left':
						$message = __( 'Your license key has reached its activation limit.' );
						break;
					default :
						$message = __( 'An error occurred, please try again.' );
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
                                                                        
                        $current_status = 'active';
                        $message        = 'Activated';
                    }
                    
                    if($license_status == 'inactive'){
                        
                        $license[strtolower($add_on).'_addon_license_key_status']  = 'deactivated';
                        $license[strtolower($add_on).'_addon_license_key']         = $license_key;
                        $license[strtolower($add_on).'_addon_license_key_message'] = 'Deactivated';
                        $current_status = 'deactivated';
                        $message        = 'Deactivated';
                        
                    }
                    
                }
                
                $get_options   = get_option('pwaforwp_settings');
                $merge_options = array_merge($get_options, $license);
                update_option('pwaforwp_settings', $merge_options);  
                
                return array('status'=> $current_status, 'message'=> $message);
                                                                
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
			esc_url("https://pwa-for-wp.com/extensions/loading-icon-library-for-pwa/"),
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
									'enable_field' => 'notification_feature',
									'section_name' => 'pwaforwp_push_notification_section',
									'setting_title' => 'Push notification',
									'tooltip_option' => 'send notification to users',
									'tooltip_link'	=> 'https://pwa-for-wp.com/docs/article/how-to-use-push-notifications-in-pwa/'
									),
				'precaching' => array(
									'enable_field' => 'precaching_feature',
									'section_name' => 'pwaforwp_precaching_setting_section',
									'setting_title' => 'Pre Caching',
									'tooltip_option' => 'Pre-Cache pages and posts on page load',
									),
				'addtohomebanner' => array(
									'enable_field' => 'addtohomebanner_feature',
									'section_name' => 'pwaforwp_addtohomescreen_setting_section',
									'setting_title' => 'Custom Add To Home Banner',
									'tooltip_option' => 'Add a banner website for PWA app install',
									),
				'utmtracking' => array(
									'enable_field' => 'utmtracking_feature',
									'section_name' => 'pwaforwp_utmtracking_setting_section',
									'setting_title' => 'UTM Tracking',
									'tooltip_option'=> 'Urchin Traffic Monitor Tracking',
									'tooltip_link'	=> 'https://pwa-for-wp.com/docs/article/how-to-add-utm-tracking-in-pwa/'
									),
				'loader' => array(
									'enable_field' => 'loader_feature',
									'section_name' => 'pwaforwp_loaders_setting_section',
									'setting_title' => 'Loader',
									'tooltip_option'=> 'Loader for complete website',
									'tooltip_link'	=> 'https://pwa-for-wp.com/docs/article/how-to-use-loading-icon-library-for-pwa/'
									),
				'calltoaction'	=> array(
									'enable_field' => 'call_to_action',
									'section_name' => 'pwaforwp_call_to_action_setting_section',
									'setting_title' => 'Call To Action',
									'is_premium'	=> true,
									'pro_link'		=> $addonLists['ctafp']['p-url'],
									'pro_active'    => (is_plugin_active($addonLists['ctafp']['p-slug'])? 1: 0),
									'pro_deactive'    => (!is_plugin_active($addonLists['ctafp']['p-slug']) && file_exists(PWAFORWP_PLUGIN_DIR."/../".$addonLists['ctafp']['p-slug'])? 1: 0),
									'slug' => 'ctafp',
									'tooltip_option'=> 'CTA feature for PWA',
									'tooltip_link'	=> 'https://pwa-for-wp.com/docs/article/how-to-use-call-to-action-cta-in-pwa/'
									),
				'rewardspwa' => array(
									'enable_field' => 'rewardspwa_feature',
									'section_name' => 'pwaforwp_rewardspwa_setting_section',
									'setting_title' => 'Rewards on APP Install',
									'is_premium'    => true,
									'pro_link'      => $addonLists['ropi']['p-url'],
									'pro_active'    => (is_plugin_active($addonLists['ropi']['p-slug'])? 1: 0),
                                    'pro_deactive'    => (!is_plugin_active($addonLists['ropi']['p-slug']) && file_exists(PWAFORWP_PLUGIN_DIR."/../".$addonLists['ropi']['p-slug'])? 1: 0),
                                    'slug' => 'mcfp',
									'tooltip_option' => esc_html__('Give Rewards to the customers', 'pwa-for-wp'),
									'tooltip_link'	=> 'https://pwa-for-wp.com/docs/article/how-to-use-rewards-on-pwa-install/'
									),
				'dataAnalytics' => array(
									'enable_field' => 'data_analytics',
									'section_name' => 'pwaforwp_data_analytics_setting_section',
									'setting_title' => 'Data Analytics',
									'is_premium'	=> true,
									'pro_link'		=> $addonLists['dafp']['p-url'],
									'pro_active'    => (is_plugin_active($addonLists['dafp']['p-slug'])? 1: 0),
									'pro_deactive'    => (!is_plugin_active($addonLists['dafp']['p-slug']) && file_exists(PWAFORWP_PLUGIN_DIR."/../".$addonLists['dafp']['p-slug'])? 1: 0),
									'slug' => 'dafp',
									'tooltip_option'=> 'Analytics for the number of people who are installing PWA',
									'tooltip_link'	=> 'https://pwa-for-wp.com/docs/article/how-to-use-data-analytics-for-pwa/'
									),
				'pulltorefresh' => array(
                                    'enable_field' => 'pull_to_refresh',
                                    'section_name' => 'pwaforwp_pull_to_refresh_setting_section',
                                    'setting_title' => 'Pull To Refresh',
                                    'is_premium'    => true,
                                    'pro_link'      => $addonLists['ptrfp']['p-url'],
                                    'pro_active'    => (is_plugin_active($addonLists['ptrfp']['p-slug'])? 1: 0),
                                    'pro_deactive'    => (!is_plugin_active($addonLists['ptrfp']['p-slug']) && file_exists(PWAFORWP_PLUGIN_DIR."/../".$addonLists['ptrfp']['p-slug'])? 1: 0),
                                    'slug' => 'ptrfp',
                                    'tooltip_option'=> 'Refresh the PWA APP page',
									'tooltip_link'	=> 'https://pwa-for-wp.com/docs/article/how-to-use-data-analytics-for-pwa/'
                                    ),
				'progressbar' => array(
                                    'enable_field' => 'scroll_progress_bar',
                                    'section_name' => 'pwaforwp_scroll_progress_bar_setting_section',
                                    'setting_title' => 'Scroll Progress Bar',
                                    'is_premium'    => true,
                                    'pro_link'      => $addonLists['spbfp']['p-url'],
                                    'pro_active'    => (is_plugin_active($addonLists['spbfp']['p-slug'])? 1: 0),
                                    'pro_deactive'    => (!is_plugin_active($addonLists['spbfp']['p-slug']) && file_exists(PWAFORWP_PLUGIN_DIR."/../".$addonLists['spbfp']['p-slug'])? 1: 0),
                                    'slug' => 'spbfp',
                                    'tooltip_option'=> 'Show Scroll progress bar',
									'tooltip_link'	=> 'https://pwa-for-wp.com/docs/article/scroll-progress-bar-for-pwa/'
                                    ),
				'pwatoapkplugin' => array(
                                    'enable_field' => 'pwa_to_apk_plugin',
                                    'section_name' => 'pwaforwp_pwa_to_apk_plugin_setting_section',
                                    'setting_title' => 'PWA to Android APP (APK)',
                                    'is_premium'    => true,
                                    'pro_link'      => $addonLists['ptafp']['p-url'],
                                    'pro_active'    => (is_plugin_active($addonLists['ptafp']['p-slug'])? 1: 0),
                                    'pro_deactive'    => (!is_plugin_active($addonLists['ptafp']['p-slug']) && file_exists(PWAFORWP_PLUGIN_DIR."/../".$addonLists['ptafp']['p-slug'])? 1: 0),
                                    'slug' => $addonLists['ptafp']['p-slug'],
                                    'tooltip_option'=> 'Generate APK for website',
									'tooltip_link'	=> 'https://pwa-for-wp.com/docs/article/how-to-use-pwa-to-apk-plugin/'
                                    ),
				'offlineforms' => array(
                                    'enable_field' => 'offline_forms',
                                    'section_name' => 'pwaforwp_offline_forms_setting_section',
                                    'setting_title' => 'Offline Forms',
                                    'is_premium'    => true,
                                    'pro_link'      => $addonLists['ofpwa']['p-url'],
                                    'pro_active'    => (is_plugin_active($addonLists['ofpwa']['p-slug'])? 1: 0),
                                    'pro_deactive'    => (!is_plugin_active($addonLists['ofpwa']['p-slug']) && file_exists(PWAFORWP_PLUGIN_DIR."/../".$addonLists['ofpwa']['p-slug'])? 1: 0),
                                    'slug' => 'ofpwa',
                                    'tooltip_option'=> 'Support forms to work on offline mode',
									'tooltip_link'	=> 'https://pwa-for-wp.com/docs/article/how-to-use-offline-forms/'
                                    ),
				'autosaveforms' => array(
                                    'enable_field' => 'autosave_forms',
                                    'section_name' => 'pwaforwp_autosave_forms_setting_section',
                                    'setting_title' => 'Auto Save Forms',
                                    'is_premium'    => true,
                                    'pro_link'      => $addonLists['ofpwa']['p-url'],
                                    'pro_active'    => (is_plugin_active($addonLists['ofpwa']['p-slug'])? 1: 0),
                                    'pro_deactive'    => (!is_plugin_active($addonLists['ofpwa']['p-slug']) && file_exists(PWAFORWP_PLUGIN_DIR."/../".$addonLists['ofpwa']['p-slug']) ? 1: 0),
                                    'slug' => 'ofpwa',
                                    'tooltip_option'=> 'It auto saves the data on the fly',
									'tooltip_link'	=> 'https://pwa-for-wp.com/docs/article/how-to-use-auto-save-forms/'
                                    ),
				'buddypress_notification' => array(
                                    'enable_field' => 'buddypress_notification',
                                    'section_name' => 'pwaforwp_buddypress_setting_section',
                                    'setting_title' => 'Buddypress',
                                    'is_premium'    => true,
                                    'pro_link'      => $addonLists['bnpwa']['p-url'],
                                    'pro_active'    => (is_plugin_active($addonLists['bnpwa']['p-slug'])? 1: 0),
                                    'pro_deactive'    => (!is_plugin_active($addonLists['bnpwa']['p-slug']) && file_exists(PWAFORWP_PLUGIN_DIR."/../".$addonLists['bnpwa']['p-slug'])? 1: 0),
                                    'slug' => 'bnpwa',
                                    'tooltip_option'=> 'Support buddypress push notification with PWA and push notification',
                                    'tooltip_link' => 'https://pwa-for-wp.com/docs/article/how-to-use-buddypress-for-pwaforwp/'
                                    ),
				'quickaction' => array(
									'enable_field' => 'quick_action',
									'section_name' => 'pwaforwp_quick_action_setting_section',
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
									'enable_field' => 'navigation_bar',
									'section_name' => 'pwaforwp_navigation_bar_setting_section',
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
									'enable_field' => 'multilingual',
									'section_name' => 'pwaforwp_multilingual_setting_section',
									'setting_title' => esc_html__('Multilingual', 'pwa-for-wp'),
									'is_premium'    => true,
									'pro_link'      => $addonLists['mcfp']['p-url'],
									'pro_active'    => (is_plugin_active($addonLists['mcfp']['p-slug'])? 1: 0),
                                    'pro_deactive'    => (!is_plugin_active($addonLists['mcfp']['p-slug']) && file_exists(PWAFORWP_PLUGIN_DIR."/../".$addonLists['mcfp']['p-slug'])? 1: 0),
                                    'slug' => 'mcfp',
									'tooltip_option' => esc_html__('Show respective language page when Multilingual avilable in PWA', 'pwa-for-wp'),
									'tooltip_link'	=> 'https://pwa-for-wp.com/docs/article/how-to-use-multilingual-compatibility-for-pwa-addon/'
									),
				'urlhandler' => array(
									'enable_field' => 'urlhandler_feature',
									'section_name' => 'pwaforwp_urlhandler_setting_section',
									'setting_title' => 'URL Handlers',
									'tooltip_option'=> 'PWA as URL Handlers allows apps like music.example.com to register themselves as URL handlers so that links from outside of the PWA',
									'tooltip_link'	=> 'https://pwa-for-wp.com/docs/article/how-to-use-urlhandler-for-pwa/'
									),
				
								);
				
	$featuresHtml = '';
	foreach ($feturesArray as $key => $featureVal) {
		echo '<div id="'.$key.'-contents" class="pwaforwp-hide">';
			echo '<div class="pwaforwp-wrap thickbox-fetures-wrap '.$key.'-wrap-tb">';
				do_settings_sections( $featureVal['section_name'] );
				echo '<div class="footer tab_view_submitbtn" style=""><button type="submit" class="button button-primary pwaforwp-submit-feature-opt">Submit</button></div>';
			echo '</div>';
		echo '</div>';
		$settingsHtml = $tooltipHtml = $warnings = '';
		if($key=='notification' && empty($settings['notification_options'])){
			$warnings = "<span class='pwafw-tooltip'><i id='notification-opt-stat' class='dashicons dashicons-warning' style='color: #ffb224d1;' title=''></i><span class='pwafw-help-subtitle'>Need integration</span></span>";
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
				  <span class="slider round"></span>
				</label>
				<div class="card-action-settings" data-content="%2$s-contents" '.$settingsHtml.'>
					<span class="pwaforwp-change-data pwaforwp-setting-icon-tab dashicons dashicons-admin-generic" href="#" data-option="%2$s-contents" title="%1$s"></span>
				</div>
			</div>';

	    $pro_link = '';
	    if(isset($featureVal['pro_deactive']) && $featureVal['pro_deactive'] && $featureVal['pro_deactive']==1  && !class_exists('PWAFORWPPROExtensionManager')){
	    	//$Plugins = get_transient( 'plugin_slugs');
	    	//<span class="pro deactivated">Deactivated</span>
	    	$wp_nonce = wp_create_nonce("wp_pro_activate");
	    	$premium_alert = '<label class="switch">
				  <input type="checkbox" class="pwa_activate_pro_plugin" value="1" data-secure="'.$wp_nonce.'" data-file="'.$featureVal['slug'].'">
				  <span class="slider round"></span>
				</label>';
	    }elseif(isset($featureVal['is_premium']) && $featureVal['is_premium'] && !$featureVal['pro_active'] && class_exists('PWAFORWPPROExtensionManager')){
		    $premium_alert = '<span class="pro deactivated">Deactivated</span>';
	    	$pro_link = 'onclick="window.open(\''.admin_url("admin.php?page=pwawp-extension-manager").'\', \'_blank\')"';
	    }
	    elseif(isset($featureVal['is_premium']) && $featureVal['is_premium'] && !$featureVal['pro_active']){
	    	$premium_alert = '<span class="pro">PRO</span>';
	    	$pro_link = 'onclick="window.open(\''.$featureVal['pro_link'].'\', \'_blank\')"';
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
							esc_html__($featureVal['setting_title'], 'pwa-for-wp'),
							esc_attr($key),
							(isset($settings[$featureVal['enable_field']]) && $settings[$featureVal['enable_field']] ) ? esc_html("checked") : '',
							$featureVal['enable_field'],
							isset($featureVal['tooltip_option'])? esc_html($featureVal['tooltip_option']): '',
							(isset($settings[$featureVal['enable_field']]) && $settings[$featureVal['enable_field']]? esc_attr('pwaforwp-feature-enabled') : ''),
							$pro_link,
							$warnings

						);
	}
	echo '<ul class="pwaforwp-feature-cards">
			'.$featuresHtml.'
		</ul>
		<div class="pwawp-modal-mask pwaforwp-hide">
    <div class="pwawp-modal-wrapper">
        <div class="pwawp-modal-container">
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
	if(!wp_verify_nonce($_POST['pwaforwp_security_nonce'], 'pwaforwp_ajax_check_nonce')){
		echo json_encode(array('status'=> 503, 'message'=> 'Unauthorized access, CSRF token not matched'));
		die;
	}
	if(!isset($_POST['fields_data']) || !is_array($_POST['fields_data'])){
		echo json_encode(array('status'=> 502, 'message'=> 'Feature settings not have any fields.'));
		die;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
        echo json_encode(array('status'=> 501, 'message'=> 'Unauthorized access, permission not allowed'));
		die;
    }
	$allFields = $_POST['fields_data'];
	$actualFields = array();
	if(is_array($allFields)){
		foreach ($allFields as $key => $field) {
			
			$variable = str_replace(array('pwaforwp_settings[', ']'), array('',''), $field['var_name']);
			if(strpos($variable, '[')!==false){
				$varArray = explode("[", $variable);
				$newArr = preg_replace('/\\\\/', '', sanitize_textarea_field($field['var_value']));
				foreach (array_reverse($varArray) as $key) {
					$newArr = [$key => $newArr];
				}
				$actualFields = pwaforwp_merge_recursive_ex($actualFields, $newArr);
				//$actualFields[$varArray[0]][$varArray[1]] = preg_replace('/\\\\/', '', sanitize_textarea_field($field['var_value']));
			}else{
				$actualFields[$variable] = preg_replace('/\\\\/', '', sanitize_textarea_field($field['var_value']));
			}
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
		/*if(isset($actualFields['precaching_automatic']) && $actualFields['precaching_automatic']==0){
			$actualFields['precaching_feature'] = $actualFields['precaching_automatic'];
		}*/

		$actualFields = apply_filters('pwaforwp_features_update_data_save', $actualFields);

		update_option( 'pwaforwp_settings', $actualFields ) ;
		global $pwaforwp_settings;
		$pwaforwp_settings = array();
		pwaforwp_required_file_creation();
		echo json_encode(array('status'=> 200, 'message'=> 'Settings Saved.', 'options'=>$actualFields));
			die;
	}else{
		echo json_encode(array('status'=> 503, 'message'=> 'Fields not defined'));	
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
		
		//$filename = '/Users/tommcfarlin/Projects/acme/wp-content/uploads/2018/06/original-image.jpg';
		if( file_exists($filename) ){
			//Check there is need of file creation
			$createImage = array();
			foreach ($new_value['ios_splash_icon'] as $key => $value) {
				if(empty($value)){
					$createImage[$key] = '';
				}
			}
			if(count($createImage)>0){
				$editor = wp_get_image_editor( $filename, array() );
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
						  $new_value['ios_splash_icon'][$newkey] = $newfilename;
						}else{
							error_log($result->get_error_message()." Width: ".$newWidth." Height:".$newHeight);
						}
					}

				}//Foreach closed
				update_option( 'pwaforwp_settings', $new_value);

			}
		}
	}

    

/* else {
	   // Handle the problem however you deem necessary.
	}
*/	
}


if(!function_exists('pwaforwp_subscribe_newsletter')){
	add_action('wp_ajax_pwaforwp_subscribe_newsletter','pwaforwp_subscribe_newsletter');

	function pwaforwp_subscribe_newsletter(){
	    $api_url = 'http://magazine3.company/wp-json/api/central/email/subscribe';
	    $api_params = array(
	        'name' => sanitize_text_field($_POST['name']),
	        'email'=> sanitize_text_field($_POST['email']),
	        'website'=> sanitize_text_field($_POST['website']),
	        'type'=> 'pwa'
	    );
	    $response = wp_remote_post( $api_url, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );
	    $response = wp_remote_retrieve_body( $response );
	    echo $response;
	    die;
	} 	
} 

if(!function_exists('pwaforwp_splashscreen_uploader')){
	add_action('wp_ajax_pwaforwp_splashscreen_uploader','pwaforwp_splashscreen_uploader');

	function pwaforwp_splashscreen_uploader(){
		if ( ! isset( $_GET['pwaforwp_security_nonce'] ) ){
            echo json_encode(array("status"=>500, "message"=> "Failed! Security check not active"));
            die;
        }
        if ( !wp_verify_nonce( $_GET['pwaforwp_security_nonce'], 'pwaforwp_ajax_check_nonce' ) ){
           echo json_encode(array("status"=>500, "message"=> "Failed! Security check"));
           die;
        }
        if( !current_user_can('manage_options') ){
        	echo json_encode(array("status"=>401, "message"=> "Failed! you are not autherized to save"));
        	die;
        }
		$pwaforwp_settings = pwaforwp_defaultSettings();
		
		$upload = wp_upload_dir();
		$path = $upload['basedir']."/pwa-splash-screen/";
		wp_mkdir_p($path);
		  file_put_contents($path.'/index.html','');
		  $zipfilename = $path."file.zip";
	      $input = fopen('php://input', 'rb');
		  $file = fopen($zipfilename, 'wb'); 

		  // Note: we don't need open and stream to stream, 
		  // we could've used file_get_contents and file_put_contents
		  stream_copy_to_stream($input, $file);
		  fclose($input);
		  fclose($file);

		if(function_exists('WP_Filesystem')){ WP_Filesystem(); }
		unzip_file($zipfilename, $path);
		$pathURL = $upload['baseurl']."/pwa-splash-screen/splashscreens/";
		$iosdata = ios_splashscreen_files_data(); 
		foreach ($iosdata as $key => $value) {
			$pwaforwp_settings['ios_splash_icon'][$key] = $pathURL.$value['file'];
		}
		$pwaforwp_settings['iosSplashScreenOpt']='generate-auto';

		update_option( 'pwaforwp_settings', $pwaforwp_settings ) ;
		unlink($zipfilename);
		echo json_encode(array("status"=>200, "message"=> "Splash screen uploaded successfully"));
		  die;
	} 	
} 

add_filter('pre_update_option_pwaforwp_settings', 'pwaforwp_update_force_update', 10, 3); 
function pwaforwp_update_force_update($value, $old_value, $option){
	if(!function_exists('wp_get_current_user')){
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
				$version = implode(".", $version).".1";
			}else{
				$version[count($version)-1] = $version[count($version)-1]+1;
				$version = implode(".", $version);
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

/**
 * pwaforwp_merge_recursive_ex merge any multidimensional Array
 * @param Array1(array) Array2(array)
 */
function pwaforwp_merge_recursive_ex(array $array1, array $array2)
{
    $merged = $array1;

    foreach ($array2 as $key => & $value) {
        if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
            $merged[$key] = pwaforwp_merge_recursive_ex($merged[$key], $value);
        } else if (is_numeric($key)) {
             if (!in_array($value, $merged)) {
                $merged[] = $value;
             }
        } else {
            $merged[$key] = $value;
        }
    }

    return $merged;
}