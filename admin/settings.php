<?php
if ( ! defined( 'ABSPATH' ) ) exit;
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
	       $tab = pwaforwp_get_tab('dashboard', array('dashboard','general','design','push_notification', 'other_setting', 'precaching_setting', 'tools','help'));
                                                                        
	?>
		<div class="wrap">                            
		<h1><?php echo esc_html__('Progressive Web Apps For WP', 'pwa-for-wp'); ?></h1>
		<h2 class="nav-tab-wrapper pwaforwp-tabs">
			<?php

			echo '<a href="' . esc_url(pwaforwp_admin_link('dashboard')) . '" class="nav-tab ' . esc_attr( $tab == 'dashboard' ? 'nav-tab-active' : '') . '"><span class="dashicons dashicons-dashboard"></span> ' . esc_html__('Dashboard', 'pwa-for-wp') . '</a>';

			echo '<a href="' . esc_url(pwaforwp_admin_link('general')) . '" class="nav-tab ' . esc_attr( $tab == 'general' ? 'nav-tab-active' : '') . '"><span class="dashicons dashicons-welcome-view-site"></span> ' . esc_html__('General','pwa-for-wp') . '</a>';

			echo '<a href="' . esc_url(pwaforwp_admin_link('design')) . '" class="nav-tab ' . esc_attr( $tab == 'design' ? 'nav-tab-active' : '') . '"><span class="dashicons dashicons-art"></span> ' . esc_html__('Design','pwa-for-wp') . '</a>';
                        
                        echo '<a href="' . esc_url(pwaforwp_admin_link('push_notification')) . '" class="nav-tab ' . esc_attr( $tab == 'push_notification' ? 'nav-tab-active' : '') . '"><span class="dashicons dashicons-art"></span> ' . esc_html__('Push Notification','pwa-for-wp') . '</a>';

			echo '<a href="' . esc_url(pwaforwp_admin_link('other_setting')) . '" class="nav-tab ' . esc_attr( $tab == 'other_setting' ? 'nav-tab-active' : '') . '"><span class="dashicons dashicons-clipboard"></span> ' . esc_html__('Advanced','pwa-for-wp') . '</a>';
                        
                        echo '<a href="' . esc_url(pwaforwp_admin_link('precaching_setting')) . '" class="nav-tab ' . esc_attr( $tab == 'precaching_setting' ? 'nav-tab-active' : '') . '"><span class="dashicons dashicons-clipboard"></span> ' . esc_html__('Pre Caching','pwa-for-wp') . '</a>';
                        
                        echo '<a href="' . esc_url(pwaforwp_admin_link('tools')) . '" class="nav-tab ' . esc_attr( $tab == 'tools' ? 'nav-tab-active' : '') . '"><span class="dashicons dashicons-clipboard"></span> ' . esc_html__('Tools','pwa-for-wp') . '</a>';

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

			echo "<div class='pwaforwp-general' ".( $tab != 'general' ? 'style="display:none;"' : '').">";
				// general Application Settings
				do_settings_sections( 'pwaforwp_general_section' );	// Page slug
			echo "</div>";

			echo "<div class='pwaforwp-design' ".( $tab != 'design' ? 'style="display:none;"' : '').">";
				// design Application Settings
				do_settings_sections( 'pwaforwp_design_section' );	// Page slug
			echo "</div>";
                        
                        echo "<div class='pwaforwp-push_notification' ".( $tab != 'push_notification' ? 'style="display:none;"' : '').">";
				// design Application Settings
				do_settings_sections( 'pwaforwp_push_notification_section' );	// Page slug
			echo "</div>";
                        
                        echo "<div class='pwaforwp-tools' ".( $tab != 'tools' ? 'style="display:none;"' : '').">";
				// other_setting Application Settings
				do_settings_sections( 'pwaforwp_tools_section' );	// Page slug
			echo "</div>";
                        
			echo "<div class='pwaforwp-other_setting' ".( $tab != 'other_setting' ? 'style="display:none;"' : '').">";
				// other_setting Application Settings
				do_settings_sections( 'pwaforwp_other_setting_section' );	// Page slug
			echo "</div>";
                        
                        echo "<div class='pwaforwp-precaching_setting' ".( $tab != 'precaching_setting' ? 'style="display:none;"' : '').">";
				// other_setting Application Settings
				do_settings_sections( 'pwaforwp_precaching_setting_section' );	// Page slug
			echo "</div>";
                        
			echo "<div class='pwaforwp-help' ".( $tab != 'help' ? 'style="display:none;"' : '').">";
				echo "<h3>".esc_html__('Help Section', 'pwa-for-wp')."</h3><a target=\"_blank\" href=\"https://ampforwp.com/tutorials/article/pwa-for-amp/\">".esc_html__('View Setup Documentation', 'pwa-for-wp')."</a>";
				?>	
				<hr />	
        	                   <div class="pwa_contact_us_div">
			            <strong><?php echo esc_html__('If you have any query, please write the query in below box or email us at', 'pwa-for-wp') ?> <a href="mailto:team@magazine3.com">team@magazine3.com</a>. <?php echo esc_html__('We will reply to your email address shortly', 'pwa-for-wp') ?></strong>
			       		<hr />	
			            <ul>
			                <li><label for="pwaforwp_query_message"><?php echo esc_html__('Message', 'pwa-for-wp'); ?></label>
			                    <textarea rows="5" cols="60" id="pwaforwp_query_message" name="pwaforwp_query_message"> </textarea>
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
        
	<?php            
           
}


/*
	WP Settings API
*/
add_action('admin_init', 'pwaforwp_settings_init');

function pwaforwp_settings_init(){
    
	register_setting( 'pwaforwp_setting_dashboard_group', 'pwaforwp_settings' );

	add_settings_section('pwaforwp_dashboard_section', esc_html__('Installation Status','pwa-for-wp'), '__return_false', 'pwaforwp_dashboard_section');
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
		
		// Orientation
		add_settings_field(
			'pwaforwp_orientation',									// ID
			esc_html__('Orientation', 'pwa-for-wp'),		// Title
			'pwaforpw_orientation_callback',								// CB
			'pwaforwp_general_section',						// Page slug
			'pwaforwp_general_section'						// Settings Section ID
		);                                

	add_settings_section('pwaforwp_design_section', '', '__return_false', 'pwaforwp_design_section');
		// Splash Screen Background Color
		add_settings_field(
			'pwaforwp_background_color',							// ID
			esc_html__('', 'pwa-for-wp'),	// Title
			'pwaforwp_background_color_callback',							// CB
			'pwaforwp_design_section',						// Page slug
			'pwaforwp_design_section'						// Settings Section ID
		);		
                // Add to Home screen Color
                add_settings_field(
			'pwaforwp_custom_banner_design',									// ID
			esc_html__('', 'pwa-for-wp'),		// Title
			'pwaforwp_custom_banner_design_callback',								// CB
			'pwaforwp_design_section',						// Page slug
			'pwaforwp_design_section'						// Settings Section ID
		);
                                                
                add_settings_section('pwaforwp_tools_section', esc_html__('','pwa-for-wp'), '__return_false', 'pwaforwp_tools_section');
                                                
		add_settings_field(
			'pwaforwp_reset_setting',							// ID
			esc_html__('Reset', 'pwa-for-wp'),	// Title
			'pwaforwp_reset_setting_callback',							// CB
			'pwaforwp_tools_section',						// Page slug
			'pwaforwp_tools_section'						// Settings Section ID
		);
                
                add_settings_field(
			'pwaforwp_loading_setting',							// ID
			esc_html__('Loading Icon', 'pwa-for-wp'),	// Title
			'pwaforwp_loading_setting_callback',							// CB
			'pwaforwp_tools_section',						// Page slug
			'pwaforwp_tools_section'						// Settings Section ID
		);

		//Misc tabs
		add_settings_section('pwaforwp_other_setting_section', esc_html__('','pwa-for-wp'), '__return_false', 'pwaforwp_other_setting_section');
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
                add_settings_field(
			'pwaforwp_custom_add_to_home',									// ID
			esc_html__('Custom Add To Home Banner', 'pwa-for-wp'),		// Title
			'pwaforwp_custom_add_to_home_callback',								// CB
			'pwaforwp_other_setting_section',						// Page slug
			'pwaforwp_other_setting_section'						// Settings Section ID
		);
                add_settings_field(
			'pwaforwp_one_signal_support',									// ID
			esc_html__('OneSignal Compatibility', 'pwa-for-wp'),		// Title
			'pwaforwp_one_signal_support_callback',								// CB
			'pwaforwp_other_setting_section',						// Page slug
			'pwaforwp_other_setting_section'						// Settings Section ID
		);
                add_settings_field(
			'pwaforwp_cache_external_links_setting',							// ID
			esc_html__('Cache External Links', 'pwa-for-wp'),	// Title
			'pwaforwp_cache_external_links_setting_callback',							// CB
			'pwaforwp_other_setting_section',						// Page slug
			'pwaforwp_other_setting_section'						// Settings Section ID
		);
		add_settings_field(
			'pwaforwp_utm_setting',							// ID
			esc_html__('UTM Tracking', 'pwa-for-wp'),	// Title
			'pwaforwp_utm_setting_callback',							// CB
			'pwaforwp_other_setting_section',						// Page slug
			'pwaforwp_other_setting_section'						// Settings Section ID
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
			'pwaforwp_caching_strategies_setting',							// ID
			esc_html__('Caching Strategies', 'pwa-for-wp'),	// Title
			'pwaforwp_caching_strategies_setting_callback',							// CB
			'pwaforwp_other_setting_section',						// Page slug
			'pwaforwp_other_setting_section'						// Settings Section ID
		);
                
                add_settings_section('pwaforwp_precaching_setting_section', esc_html__('','pwa-for-wp'), '__return_false', 'pwaforwp_precaching_setting_section');
		add_settings_field(
			'pwaforwp_precaching_setting',							// ID
			'',	
			'pwaforwp_precaching_setting_callback',							// CB
			'pwaforwp_precaching_setting_section',						// Page slug
			'pwaforwp_precaching_setting_section'						// Settings Section ID
		);  
                
                
                add_settings_section('pwaforwp_push_notification_section', esc_html__('','pwa-for-wp'), '__return_false', 'pwaforwp_push_notification_section');
		// Splash Screen Background Color
		add_settings_field(
			'pwaforwp_push_notification',							// ID
			'',	
			'pwaforwp_push_notification_callback',							// CB
			'pwaforwp_push_notification_section',						// Page slug
			'pwaforwp_push_notification_section'						// Settings Section ID
		);
                
                
                
		
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

		</select></td>
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

function pwaforwp_url_exclude_from_cache_list_callback(){
	// Get Settings
	$settings = pwaforwp_defaultSettings(); 
	?>
        <label><textarea placeholder="https://example.com/admin.php?page=newpage, https://example.com/admin.php?page=newpage2 "  rows="4" cols="50" id="pwaforwp_settings[excluded_urls]" name="pwaforwp_settings[excluded_urls]"><?php echo (isset($settings['excluded_urls']) ? esc_attr($settings['excluded_urls']): ''); ?></textarea></label>
        <p><?php echo esc_html__('Note: Put in comma separated', 'pwa-for-wp'); ?></p>
	<p><?php echo esc_html__('Put the list of urls which you do not want to cache by service worker', 'pwa-for-wp'); ?></p>	
	
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
                    <td><strong><?php echo esc_html__('Automatic', 'pwa-for-wp'); ?></strong></td>
                        <td>
                          <input type="checkbox" name="pwaforwp_settings[precaching_automatic]" id="pwaforwp_settings_precaching_automatic" class="" <?php echo (isset( $settings['precaching_automatic'] ) &&  $settings['precaching_automatic'] == 1 ? 'checked="checked"' : ''); ?> value="1">   
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
                         <input type="checkbox" name="pwaforwp_settings[precaching_automatic_post]" id="pwaforwp_settings_precaching_automatic_post" class="" <?php echo (isset( $settings['precaching_automatic_post'] ) &&  $settings['precaching_automatic_post'] == 1 ? 'checked="checked"' : ''); ?> value="1">     
                         </td>
                         <td>
                         <?php echo esc_html__('Page', 'pwa-for-wp') ?>   
                         </td>
                         <td>
                         <input type="checkbox" name="pwaforwp_settings[precaching_automatic_page]" id="pwaforwp_settings_precaching_automatic_page" class="" <?php echo (isset( $settings['precaching_automatic_page'] ) &&  $settings['precaching_automatic_page'] == 1 ? 'checked="checked"' : ''); ?> value="1">         
                         </td>
                         <td>                          
                         <?php echo esc_html__('Custom Post', 'pwa-for-wp') ?>   
                         </td>
                         <td>
                         <input type="checkbox" name="pwaforwp_settings[precaching_automatic_custom_post]" id="pwaforwp_settings_precaching_automatic_custom_post" class="" <?php echo (isset( $settings['precaching_automatic_custom_post'] ) &&  $settings['precaching_automatic_custom_post'] == 1 ? 'checked="checked"' : ''); ?> value="1">         
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
                    <td><strong> <?php echo esc_html__('Manual', 'pwa-for-wp'); ?> </strong></td>
                        <td>
                         <input type="checkbox" name="pwaforwp_settings[precaching_manual]" id="pwaforwp_settings_precaching_manual" class="" <?php echo (isset( $settings['precaching_manual'] ) &&  $settings['precaching_manual'] == 1 ? 'checked="checked"' : ''); ?> value="1">    
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
                
	<label><input type="checkbox" name="pwaforwp_settings[utm_setting]" id="pwaforwp_settings_utm_setting" class="" <?php echo (isset( $settings['utm_setting'] ) &&  $settings['utm_setting'] == 1 ? 'checked="checked"' : ''); ?> value="1"><?php echo esc_html__('Enable UTM Tracking', 'pwa-for-wp'); ?></label>
	<p> <?php echo esc_html__('To identify users are coming from your App', 'pwa-for-wp'); ?></p>
	<table class="form-table">
		<tr class="pwawp_utm_values_class" style="display:<?php echo esc_attr($style); ?>;">
			<td><?php echo esc_html__('UTM Source', 'pwa-for-wp'); ?></td>
			<td><input type="text" name="pwaforwp_settings[utm_details][utm_source]" value="<?php echo esc_attr($utm_source); ?>" data-val="<?php echo esc_attr($utm_source); ?>"/></td>
		</tr>
		<tr class="pwawp_utm_values_class" style="display:<?php echo esc_attr($style); ?>;">
			<td><?php echo esc_html__('UTM Medium', 'pwa-for-wp'); ?></td>
			<td><input type="text" name="pwaforwp_settings[utm_details][utm_medium]" value="<?php echo esc_attr($utm_medium); ?>" data-val="<?php echo esc_attr($utm_medium); ?>"/></td>
		</tr>
                <tr class="pwawp_utm_values_class" style="display:<?php echo esc_attr($style); ?>;">
			<td><?php echo esc_html__('UTM Campaign', 'pwa-for-wp'); ?></td>
			<td><input type="text" name="pwaforwp_settings[utm_details][utm_campaign]" value="<?php echo esc_attr($utm_campaign); ?>" data-val="<?php echo esc_attr($utm_campaign); ?>"/></td>
		</tr>
		<tr class="pwawp_utm_values_class" style="display:<?php echo esc_attr($style); ?>;">
			<td><?php echo esc_html__('UTM Term', 'pwa-for-wp'); ?></td>
			<td><input type="text" name="pwaforwp_settings[utm_details][utm_term]" value="<?php echo esc_attr($utm_term); ?>" data-val="<?php echo esc_attr($utm_term); ?>"/></td>
		</tr>
		<tr class="pwawp_utm_values_class" style="display:<?php echo esc_attr($style); ?>;">
			<td><?php echo esc_html__('UTM Content', 'pwa-for-wp'); ?></td>
			<td><input type="text" name="pwaforwp_settings[utm_details][utm_content]" value="<?php echo esc_attr($utm_content); ?>" data-val="<?php echo esc_attr($utm_content); ?>"/></td>
		</tr>
		<tr class="pwawp_utm_values_class expectedValues" style="display:<?php echo esc_attr($style); ?>;">
			<td><?php echo esc_html__('UTM Non-amp Url', 'pwa-for-wp'); ?></td>
			<td><code><?php echo esc_url($utm_url); ?></code></td>
		</tr>
		<tr class="pwawp_utm_values_class expectedValues" style="display:<?php echo esc_attr($style); ?>;">
			<td><?php echo esc_html__('UTM amp Url', 'pwa-for-wp'); ?></td>
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
        
	<input type="checkbox" name="pwaforwp_settings[offline_google_setting]" id="pwaforwp_settings[offline_google_setting]" class="" <?php echo (isset( $settings['offline_google_setting'] ) &&  $settings['offline_google_setting'] == 1 ? 'checked="checked"' : ''); ?> value="1">
	<p><?php echo esc_html__('Offline analytics is a module that will use background sync to ensure that requests to Google Analytics are made regardless of the current network condition', 'pwa-for-wp'); ?></p>
	<?php
}
function pwaforwp_force_update_sw_setting_callback(){
	// Get Settings
	$settings = pwaforwp_defaultSettings(); 
	?>
        <label><input type="text" id="pwaforwp_settings[force_update_sw_setting]" name="pwaforwp_settings[force_update_sw_setting]" value="<?php if(isset($settings['force_update_sw_setting'])){ echo esc_attr($settings['force_update_sw_setting']);}else{ echo PWAFORWP_PLUGIN_VERSION; } ?>"></label>        
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
        <h2><?php echo esc_html__('Splash Screen', 'pwa-for-wp') ?></h2>
        <table>
            <tr><td><strong><?php echo esc_html__('Background Color', 'pwa-for-wp') ?></strong></td><td><input type="text" name="pwaforwp_settings[background_color]" id="pwaforwp_settings[background_color]" class="pwaforwp-colorpicker" value="<?php echo isset( $settings['background_color'] ) ? sanitize_hex_color( $settings['background_color']) : '#D5E0EB'; ?>" data-default-color="#D5E0EB"></td></tr>
        <tr><td><strong><?php echo esc_html__('Theme Color', 'pwa-for-wp') ?></strong></td><td><input type="text" name="pwaforwp_settings[theme_color]" id="pwaforwp_settings[theme_color]" class="pwaforwp-colorpicker" value="<?php echo isset( $settings['theme_color'] ) ? sanitize_hex_color( $settings['theme_color']) : '#D5E0EB'; ?>" data-default-color="#D5E0EB"></td></tr>                
        </table>        	
	
	<?php
}

function pwaforwp_push_notification_callback(){	
    
	$settings = pwaforwp_defaultSettings(); 
        
        ?>        
        
        <div class="pwafowwp-server-key-section">
            <h2><?php echo esc_html__('Settings', 'pwa-for-wp') ?></h2>
            <table class="pwaforwp-push-notificatoin-table">
                <tbody>
                    <tr>
                        <th><?php echo esc_html__('FCM Server API Key', 'pwa-for-wp') ?></th>  
                        <td><input type="text" name="pwaforwp_settings[fcm_server_key]" id="pwaforwp_settings[fcm_server_key]" value="<?php echo (isset($settings['fcm_server_key'])? esc_attr($settings['fcm_server_key']):'') ; ?>"></td>
                    </tr>
                    <tr>
                        <th><?php echo esc_html__('Config', 'pwa-for-wp') ?></th>  
                        <td>
                            <textarea placeholder="{ <?="\n"?>apiKey: '<Your Api Key>', <?="\n"?>authDomain: '<Your Auth Domain>',<?="\n"?>databaseURL: '<Your Database URL>',<?="\n"?>projectId: '<Your Project Id>',<?="\n"?>storageBucket: '<Your Storage Bucket>', <?="\n"?>messagingSenderId: '<Your Messaging Sender Id>' <?="\n"?>}" rows="8" cols="60" id="pwaforwp_settings[fcm_config]" name="pwaforwp_settings[fcm_config]"><?php echo isset($settings['fcm_config']) ? esc_attr($settings['fcm_config']) : ''; ?></textarea>
                            <p><?php echo esc_html__('Note: Create a new firebase project on ', 'pwa-for-wp') ?> <a href="https://firebase.google.com/" target="_blank"><?php echo esc_html__('firebase', 'pwa-for-wp') ?></a> <?php echo esc_html__('console, its completly free by google with some limitations. After creating the project you will find FCM Key and json in project details section.', 'pwa-for-wp') ?></p>
                            <p><?php echo esc_html__('Note: Firebase push notification does not support on AMP. It will support in future', 'pwa-for-wp') ?> </p>
                        </td>
                    </tr>                                                            
                </tbody>   
            </table>                   
        </div>
        <div class="pwaforwp-notification-condition-section" <?php echo ($settings['fcm_server_key'] !='' ? 'style="display:block;"' : 'style="display:none;"'); ?>>
        <div>
            <h2><?php echo esc_html__('Send Notification On', 'pwa-for-wp') ?></h2>
            <table class="pwaforwp-push-notificatoin-table">
                <tbody>
                    <tr>
                        <th><?php echo esc_html__('Add New Post', 'pwa-for-wp') ?></th>  
                        <td>
                            <input  type="checkbox" name="pwaforwp_settings[on_add_post]" id="pwaforwp_settings[on_add_post]" class="pwaforwp-fcm-checkbox" <?php echo (isset( $settings['on_add_post'] ) &&  $settings['on_add_post'] == 1 ? 'checked="checked"' : ''); ?> value="1">
                            <?php
                            if(isset($settings['on_add_post']) == 1){
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
                            if(isset($settings['on_update_post']) == 1){
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
                            if(isset($settings['on_add_page']) == 1){
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
                            if(isset($settings['on_update_page']) == 1){
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
                        <th><input style="width: 100%" placeholder="Title" type="text" id="pwaforwp_notification_message_title" name="pwaforwp_notification_message_title">
                            <br>
			                   
                        </th>  
                        <td></td>
                    </tr>
                    
                    <tr>
                        <th><textarea rows="5" cols="60" id="pwaforwp_notification_message" name="pwaforwp_notification_message"> </textarea>
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
            <tr><td><strong><?php echo esc_html__('Title', 'pwa-for-wp'); ?></strong></td><td><input type="text" name="pwaforwp_settings[custom_banner_title]" id="pwaforwp_settings[custom_banner_title]" class="" value="<?php echo isset( $settings['custom_banner_title'] ) ? esc_attr( $settings['custom_banner_title']) : 'Add '.get_bloginfo().' to your Homescreen!'; ?>"></td></tr> 
            <tr><td><strong><?php echo esc_html__('Button Text', 'pwa-for-wp'); ?></strong></td><td><input type="text" name="pwaforwp_settings[custom_banner_button_text]" id="pwaforwp_settings[custom_banner_button_text]" class="" value="<?php echo isset( $settings['custom_banner_button_text'] ) ? esc_attr( $settings['custom_banner_button_text']) : 'Add'; ?>"></td></tr> 
            <tr><td><strong><?php echo esc_html__('Banner Background Color', 'pwa-for-wp'); ?></strong></td><td><input type="text" name="pwaforwp_settings[custom_banner_background_color]" id="pwaforwp_settings[custom_banner_background_color]" class="pwaforwp-colorpicker" value="<?php echo isset( $settings['custom_banner_background_color'] ) ? sanitize_hex_color( $settings['custom_banner_background_color']) : '#D5E0EB'; ?>" data-default-color="#fff"></td></tr> 
            <tr><td><strong><?php echo esc_html__('Banner Title Color', 'pwa-for-wp'); ?></strong></td><td><input type="text" name="pwaforwp_settings[custom_banner_title_color]" id="pwaforwp_settings[custom_banner_title_color]" class="pwaforwp-colorpicker" value="<?php echo isset( $settings['custom_banner_title_color'] ) ? sanitize_hex_color( $settings['custom_banner_title_color']) : '#000'; ?>" data-default-color="#000"></td></tr> 
            <tr><td><strong><?php echo esc_html__('Button Text Color', 'pwa-for-wp'); ?></strong></td><td><input type="text" name="pwaforwp_settings[custom_banner_btn_text_color]" id="pwaforwp_settings[custom_banner_btn_text_color]" class="pwaforwp-colorpicker" value="<?php echo isset( $settings['custom_banner_btn_text_color'] ) ? sanitize_hex_color( $settings['custom_banner_btn_text_color']) : '#fff'; ?>" data-default-color="#fff"></td></tr> 
            <tr><td><strong><?php echo esc_html__('Button Background Color', 'pwa-for-wp'); ?></strong></td><td><input type="text" name="pwaforwp_settings[custom_banner_btn_color]" id="pwaforwp_settings[custom_banner_btn_color]" class="pwaforwp-colorpicker" value="<?php echo isset( $settings['custom_banner_btn_color'] ) ? sanitize_hex_color( $settings['custom_banner_btn_color']) : '#D5E0EB'; ?>" data-default-color="#fdc309"></td></tr>                         
        </table>
        <?php
}

function pwaforwp_theme_color_callback(){
	// Get Settings
	$settings = pwaforwp_defaultSettings(); ?>
	<!-- Theme Color -->
	<input type="text" name="pwaforwp_settings[theme_color]" id="pwaforwp_settings[theme_color]" class="pwaforwp-colorpicker" value="<?php echo isset( $settings['theme_color'] ) ? sanitize_hex_color( $settings['theme_color']) : '#D5E0EB'; ?>" data-default-color="#D5E0EB">
	
	<p class="description">
		
	</p>
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
		<?php esc_html__('Icon of your application when installed on the phone. Must be a PNG image exactly 192x192 in size.', 'pwa-for-wp'); ?>
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
		<?php esc_html__('Icon displayed on the splash screen of your APPLICATION on supported devices. Must be a PNG image size exactly 512x512.', 'pwa-for-wp'); ?>
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
		</select>
	</label>
	
	<p class="description">
		<?php esc_html__( 'Orientation of application on devices. When set to Follow Device Orientation your application will rotate as the device is rotated.', 'pwa-for-wp' ); ?>
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
function pwaforwp_custom_add_to_home_callback(){
	// Get Settings
	$settings = pwaforwp_defaultSettings(); 
	?>
	<input type="checkbox" name="pwaforwp_settings[custom_add_to_home_setting]" id="pwaforwp_settings[custom_add_to_home_setting]" class="pwaforwp-add-to-home-banner-settings" <?php echo (isset( $settings['custom_add_to_home_setting'] ) &&  $settings['custom_add_to_home_setting'] == 1 ? 'checked="checked"' : ''); ?> value="1">
	<p><?php echo esc_html__('Show custom responsive add to home banner popup', 'pwa-for-wp'); ?></p>
        
        <?php if(isset( $settings['custom_add_to_home_setting'] ) &&  $settings['custom_add_to_home_setting'] == 1) {  ?>
        <div class="pwaforwp-enable-on-desktop">
            <input type="checkbox" name="pwaforwp_settings[enable_add_to_home_desktop_setting]" id="enable_add_to_home_desktop_setting" class="" <?php echo (isset( $settings['enable_add_to_home_desktop_setting'] ) &&  $settings['enable_add_to_home_desktop_setting'] == 1 ? 'checked="checked"' : ''); ?> value="1"><strong><?php echo esc_html__('Enable On Desktop', 'pwa-for-wp'); ?></strong>
            <p><?php echo esc_html__('Note: By default pop up will appear on mobile device to appear on desktop check enable on desktop', 'pwa-for-wp'); ?></p>
        </div>
        <?php }else{ ?>
        <div class="afw_hide pwaforwp-enable-on-desktop"><input type="checkbox" name="pwaforwp_settings[enable_add_to_home_desktop_setting]" id="enable_add_to_home_desktop_setting" class="" <?php echo (isset( $settings['enable_add_to_home_desktop_setting'] ) &&  $settings['enable_add_to_home_desktop_setting'] == 1 ? 'checked="checked"' : ''); ?> value="1"><strong><?php echo esc_html__('Enable On Desktop', 'pwa-for-wp'); ?></strong>
            <p><?php echo esc_html__('Note: By default pop up will appear on mobile device to appear on desktop check enable on desktop', 'pwa-for-wp'); ?></p>
        </div>
        <?php } ?>
	<?php
}
function pwaforwp_add_to_home_callback(){
	
	$settings = pwaforwp_defaultSettings();         
        ?>		
        <input type="text" name="pwaforwp_settings[add_to_home_selector]" id="pwaforwp_settings[add_to_home_selector]" class="pwaforwp-add-to-home-selector regular-text" size="50" value="<?php echo isset( $settings['add_to_home_selector'] ) ? esc_attr( $settings['add_to_home_selector']) : ''; ?>">
	<p><?php echo esc_html__('jQuery selector (.element) or (#element)', 'pwa-for-wp'); ?></p>	
        <p><?php echo esc_html__('Note: It is currently available in non AMP', 'pwa-for-wp'); ?></p>	
	<?php
}

// Dashboard
function pwaforwp_files_status_callback(){
    
       $serviceWorkerObj = new PWAFORWP_Service_Worker();
       $is_amp   = $serviceWorkerObj->is_amp;             
       $settings = pwaforwp_defaultSettings();
       
        ?>
        <table class="pwaforwp-files-table">
            <tbody>
                <?php if($is_amp) { ?>
                <tr>
                    <th></th>
                    <th><?php echo esc_html__( 'Wordpress (Non-AMP)', 'pwa-for-wp' ) ?></th>
                    <th><?php echo esc_html__( 'AMP', 'pwa-for-wp' ); ?></th>
                </tr>    
                <?php } ?>
                
                <tr>
                    <th><?php echo esc_html__( 'Status', 'pwa-for-wp' ) ?></th>    
                    <td> <label><input type="checkbox" name="pwaforwp_settings[normal_enable]" id="pwaforwp_settings[normal_enable]"  <?php echo (isset( $settings['normal_enable'] ) &&  $settings['normal_enable'] == 1 ? 'checked="checked"' : ''); ?> value="1"> </label> </td>    
                    <td>
                        <?php if($is_amp) { ?>
                        <label><input type="checkbox" name="pwaforwp_settings[amp_enable]" id="pwaforwp_settings[amp_enable]" <?php echo (isset( $settings['amp_enable'] ) &&  $settings['amp_enable'] == 1 ? 'checked="checked"' : ''); ?> value="1"> </label>
                         <?php } ?>
                    </td>    
                    
                </tr>
            <tr>
                <th>
                 <?php echo esc_html__( 'Manifest', 'pwa-for-wp' ) ?> 
                </th>
                <td>
                   <?php
                    $swUrl = esc_url(pwaforwp_home_url()."pwa-manifest". pwaforwp_multisite_postfix().".json");
                    $file_headers = @checkStatus($swUrl);
                  if(!$file_headers) {
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
                    $swUrl = esc_url(pwaforwp_home_url()."pwa-amp-manifest".pwaforwp_multisite_postfix().".json");
                    $file_headers = @checkStatus($swUrl);
                    if(!$file_headers) {                                                                
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
                      $swUrl = esc_url(pwaforwp_home_url()."pwa-sw".pwaforwp_multisite_postfix().".js");
                      $file_headers = @checkStatus($swUrl);
                    if(!$file_headers) {
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
                      
                    $swUrl = esc_url(pwaforwp_home_url()."pwa-amp-sw".pwaforwp_multisite_postfix().".js");
                    $file_headers = @checkStatus($swUrl);  
                    
                    if(!$file_headers) {
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
                <td colspan="2">
                  <?php
                  if ( is_ssl() ) {
                            echo '<p><span class="dashicons dashicons-yes" style="color: #46b450;"></span> </p><p>'.esc_html__( 'This site is configure with https', 'pwa-for-wp' ).'</p>' ;
                    } else {
                            echo '<p><span class="dashicons dashicons-no-alt" style="color: #dc3232;"></span> </p><p>'.esc_html__( 'This site is not configure with https', 'pwa-for-wp' ).'</p>';                                     
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
	$file_headers = @checkStatus($swUrl);	
        
	if(!$file_headers) {
		printf( '<p><span class="dashicons dashicons-no-alt" style="color: #dc3232;"></span> </p>' );
	}else{
		printf( '<p><span class="dashicons dashicons-yes" style="color: #46b450;"></span> </p>' );
	}
}

function checkStatus($swUrl){
    
        $settings = pwaforwp_defaultSettings();
        $manualfileSetup = "";

        if(array_key_exists('manualfileSetup', $settings)){
            $manualfileSetup = $settings['manualfileSetup'];    
        }	
    
	if($manualfileSetup){
	
		$wppath               = str_replace("//","/",str_replace("\\","/",realpath(ABSPATH))."/");
		$swjsFile             = $wppath."pwa-amp-sw".pwaforwp_multisite_postfix().".js";
		$swHtmlFile           = $wppath."pwa-amp-sw".pwaforwp_multisite_postfix().".html";
                $swrFile              = $wppath."pwa-register-sw".pwaforwp_multisite_postfix().".js";
		$swmanifestFile       = $wppath."pwa-amp-manifest".pwaforwp_multisite_postfix().".json";                
                $swjsFileNonAmp       = $wppath."pwa-sw".pwaforwp_multisite_postfix().".js";
                $swmanifestFileNonAmp = $wppath."pwa-manifest".pwaforwp_multisite_postfix().".json";
        
        switch ($swUrl) {
            case pwaforwp_home_url()."pwa-amp-manifest".pwaforwp_multisite_postfix().".json":
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
            case pwaforwp_home_url()."pwa-manifest".pwaforwp_multisite_postfix().".json":
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
	// Color picker CSS
	// @refer https://make.wordpress.org/core/2012/11/30/new-color-picker-in-wp-3-5/
        wp_enqueue_style( 'wp-color-picker' );	
	// Everything needed for media upload
        wp_enqueue_media();        
	
        wp_enqueue_style( 'pwaforwp-main-css', PWAFORWP_PLUGIN_URL . 'assets/css/main-css.min.css',PWAFORWP_PLUGIN_VERSION,true );            
        // Main JS
        wp_register_script('pwaforwp-main-js', PWAFORWP_PLUGIN_URL . 'assets/js/main-script.min.js', array( 'wp-color-picker' ), PWAFORWP_PLUGIN_VERSION, true);
        
        $object_name = array(
            'ajax_url'                  => admin_url( 'admin-ajax.php' ),
            'uploader_title'            => esc_html('Application Icon', 'pwa-for-wp'),
            'splash_uploader_title'     => esc_html('Splash Screen Icon', 'pwa-for-wp'),
            'uploader_button'           => esc_html('Select Icon', 'pwa-for-wp'),
            'file_status'               => esc_html('Check permission or download from manual', 'pwa-for-wp'),
            'pwaforwp_security_nonce'   => wp_create_nonce('pwaforwp_ajax_check_nonce')
        );
        
        $object_name = apply_filters('pwaforwp_localize_filter',$object_name,'pwaforwp_obj');
        
        wp_localize_script('pwaforwp-main-js', 'pwaforwp_obj', $object_name);
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
        $message .= "<table>
        				<tr><td>Plugin</td><td>PWA for wp</td></tr>
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