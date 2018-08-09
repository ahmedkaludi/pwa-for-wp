<?php
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
     	        $serviceWorkerObj = new pwaforwpServiceWorker();
        	    $is_amp = $serviceWorkerObj->is_amp;
	// Handing save settings
	if ( isset( $_GET['settings-updated'] ) ) {		
                $settings = pwaforwp_defaultSettings(); 
                $manualfileSetup ="";
                if(array_key_exists('manualfileSetup', $settings)){
                $manualfileSetup = $settings['manualfileSetup'];      
                }		              
		if($manualfileSetup){
                $status = '';    
                $fileCreationInit = new file_creation_init();
                $status = $fileCreationInit->pwaforwp_swjs_init();
                $status = $fileCreationInit->pwaforwp_manifest_init();
                $status = $fileCreationInit->pwaforwp_swr_init();
                if($is_amp){
                $status = $fileCreationInit->pwaforwp_swjs_init_amp();
                $status = $fileCreationInit->pwaforwp_manifest_init_amp();
                $status = $fileCreationInit->pwaforwp_swhtml_init_amp();
                }                 
                if(!$status){
                 echo esc_html__('Check permission or download from manual', 'pwa-for-wp');   
                }
		}		
		settings_errors();
	}
	       $tab = pwaforwp_get_tab('dashboard', array('dashboard','general','design','help'));
        
                $swJsonNonAmp = esc_url(site_url()."/pwa-manifest.json");               
				$file_json_headers = @checkStatus($swJsonNonAmp);                 
				$swJsNonAmp = esc_url(site_url()."/pwa-sw.js");
				$file_js_headers = @checkStatus($swJsNonAmp);
                if($file_json_headers || $file_js_headers){
                 echo '<div class="wrap">';   
                }else{
                 echo '<div class="wrap" style="display: none;">';      
                }
	?>
		                            
		<h1><?php echo esc_html__('Progressive Web Apps For WP', 'pwa-for-wp'); ?></h1>
		<h2 class="nav-tab-wrapper pwaforwp-tabs">
			<?php

			echo '<a href="' . esc_url(pwaforwp_admin_link('dashboard')) . '" class="nav-tab ' . esc_attr( $tab == 'dashboard' ? 'nav-tab-active' : '') . '"><span class="dashicons dashicons-dashboard"></span> ' . esc_html__('Dashboard', 'pwa-for-wp') . '</a>';

			echo '<a href="' . esc_url(pwaforwp_admin_link('general')) . '" class="nav-tab ' . esc_attr( $tab == 'general' ? 'nav-tab-active' : '') . '"><span class="dashicons dashicons-welcome-view-site"></span> ' . esc_html__('General','pwa-for-wp') . '</a>';

			echo '<a href="' . esc_url(pwaforwp_admin_link('design')) . '" class="nav-tab ' . esc_attr( $tab == 'design' ? 'nav-tab-active' : '') . '"><span class="dashicons dashicons-welcome-view-site"></span> ' . esc_html__('Design','pwa-for-wp') . '</a>';

			echo '<a href="' . esc_url(pwaforwp_admin_link('help')) . '" class="nav-tab ' . esc_attr( $tab == 'help' ? 'nav-tab-active' : '') . '"><span class="dashicons dashicons-welcome-view-site"></span> ' . esc_html__('Help','pwa-for-wp') . '</a>';
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
			echo "<div class='pwaforwp-help' ".( $tab != 'help' ? 'style="display:none;"' : '').">";
				echo "<h3>Help Section</h3><a target=\"_blank\" href=\"https://ampforwp.com/tutorials/article/pwa-for-amp/\">View Setup Documentation</a>";
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
            if($file_json_headers || $file_js_headers){
                echo' <div class="manual-setup-button" style="padding: 20px; display:none;">';
                echo '<button class="button pwaforwp-activate-service" type="button">'.esc_html__( 'Start the PWA Setup', 'pwa-for-wp' ).'</button>';
                echo '</div>';
                }else{
                echo' <div class="manual-setup-button" style="padding: 20px;">';
                echo '<button class="button pwaforwp-activate-service" type="button">'.esc_html__( 'Start the PWA Setup', 'pwa-for-wp' ).'</button>';
                echo '</div>';
                }
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
			esc_html__('Application Name', 'pwa-for-wp'),	// Title
			'pwaforwp_app_name_callback',									// CB
			'pwaforwp_general_section',						// Page slug
			'pwaforwp_general_section'						// Settings Section ID
		);

		// Application Short Name
		add_settings_field(
			'pwaforwp_app_short_name',								// ID
			esc_html__('Application Short Name', 'pwa-for-wp'),	// Title
			'pwaforwp_app_short_name_callback',							// CB
			'pwaforwp_general_section',						// Page slug
			'pwaforwp_general_section'						// Settings Section ID
		);

		// Description
		add_settings_field(
			'pwaforwp_app_description',									// ID
			esc_html__('Application Description', 'pwa-for-wp' ),		// Title
			'pwaforwp_description_callback',								// CB
			'pwaforwp_general_section',						// Page slug
			'pwaforwp_general_section'						// Settings Section ID
		);
		
		// Application Icon
		add_settings_field(
			'pwaforwp_app_icons',										// ID
			esc_html__('Application Icon', 'pwa-for-wp'),	// Title
			'pwaforwp_app_icon_callback',									// Callback function
			'pwaforwp_general_section',						// Page slug
			'pwaforwp_general_section'						// Settings Section ID
		);
		
		// Splash Screen Icon
		add_settings_field(
			'pwaforwp_app_splash_icon',									// ID
			esc_html__('Application Splash Screen Icon', 'pwa-for-wp'),	// Title
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

	add_settings_section('pwaforwp_design_section', esc_html__('Splash Screen','pwa-for-wp'), '__return_false', 'pwaforwp_design_section');
		// Splash Screen Background Color
		add_settings_field(
			'pwaforwp_background_color',							// ID
			esc_html__('Background Color', 'pwa-for-wp'),	// Title
			'pwaforwp_background_color_callback',							// CB
			'pwaforwp_design_section',						// Page slug
			'pwaforwp_design_section'						// Settings Section ID
		);
		
		// Theme Color
		add_settings_field(
			'pwaforwp_theme_color',									// ID
			esc_html__('Theme Color', 'pwa-for-wp'),		// Title
			'pwaforwp_theme_color_callback',								// CB
			'pwaforwp_design_section',						// Page slug
			'pwaforwp_design_section'						// Settings Section ID
		);
}

//Design Settings
function pwaforwp_background_color_callback(){
	// Get Settings
	$settings = pwaforwp_defaultSettings(); ?>
	
	<!-- Background Color -->
	<input type="text" name="pwaforwp_settings[background_color]" id="pwaforwp_settings[background_color]" class="pwaforwp-colorpicker" value="<?php echo isset( $settings['background_color'] ) ? sanitize_hex_color( $settings['background_color']) : '#D5E0EB'; ?>" data-default-color="#D5E0EB">
	
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
	<input type="text" name="pwaforwp_settings[icon]" id="pwaforwp_settings[icon]" class="pwaforwp-icon regular-text" size="50" value="<?php echo isset( $settings['icon'] ) ? esc_attr( $settings['icon']) : ''; ?>">
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
	<input type="text" name="pwaforwp_settings[splash_icon]" id="pwaforwp_settings[splash_icon]" class="pwaforwp-splash-icon regular-text" size="50" value="<?php echo isset( $settings['splash_icon'] ) ? esc_attr( $settings['splash_icon']) : ''; ?>">
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
			'name' => esc_attr('pwaforwp_settings[offline_page]'), 
			'echo' => 0, 
			'show_option_none' => esc_attr( '&mdash; Default &mdash;' ), 
			'option_none_value' => '0', 
			'selected' =>  isset($settings['offline_page']) ? $settings['offline_page'] : '',
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
			'name' => esc_attr('pwaforwp_settings[404_page]'), 
			'echo' => 0, 
			'show_option_none' => esc_attr( '&mdash; Default &mdash;' ), 
			'option_none_value' => '0', 
			'selected' =>  isset($settings['404_page']) ? $settings['404_page'] : '',
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

// Dashboard
function pwaforwp_files_status_callback(){
       $serviceWorkerObj = new pwaforwpServiceWorker();
       $is_amp = $serviceWorkerObj->is_amp;             
        
        ?>
        <table class="pwaforwp-files-table">
            <tbody>
                <tr>
                    <th><?php echo esc_html__( 'File', 'pwa-for-wp' ) ?></th>
                    <th><?php echo esc_html__( 'Normal', 'pwa-for-wp' ) ?></th>
                    <th><?php if($is_amp){ echo esc_html__( 'AMP', 'pwa-for-wp' );} ?></th>
                </tr>    
            <tr>
                <th>
                 <?php echo esc_html__( 'Manifest', 'pwa-for-wp' ) ?> 
                </th>
                <td>
                   <?php
                  $swUrl = esc_url(site_url()."/pwa-manifest.json");
                    $file_headers = @checkStatus($swUrl);
                  if(!$file_headers) {
                    printf( '<p><span class="dashicons dashicons-no-alt" style="color: #dc3232;"></span><a class="pwaforwp-service-activate" data-id="pwa-manifest" href="#">'.esc_html__( 'Click here to setup', 'pwa-for-wp' ).'</a> </p>' );
                 }else{
                         printf( '<p><span class="dashicons dashicons-yes" style="color: #46b450;"></span> </p>', 'manifest url' );
                 }
                  ?>   
                </td>
                <td>
                  <?php
                  if($is_amp){
                    $swUrl = esc_url(site_url()."/pwa-amp-manifest.json");
                    $file_headers = @checkStatus($swUrl);
                    if(!$file_headers) {                                                                
                        printf( '<p><span class="dashicons dashicons-no-alt" style="color: #dc3232;"></span><a class="pwaforwp-service-activate" data-id="pwa-amp-manifest" href="#">'.esc_html__( 'Click here to setup', 'pwa-for-wp' ).'</a></p>' );
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
                      $swUrl = esc_url(site_url()."/pwa-sw.js");
                      $file_headers = @checkStatus($swUrl);
                    if(!$file_headers) {
                      printf( '<p><span class="dashicons dashicons-no-alt" style="color: #dc3232;"></span> <a class="pwaforwp-service-activate" data-id="pwa-sw" href="#">'.esc_html__( 'Click here to setup', 'pwa-for-wp' ).'</a></p>' );
                   }else{
                      printf( '<p><span class="dashicons dashicons-yes" style="color: #46b450;"></span> </p>', 'manifest url' );
                   }
                  ?>  
                </td>
                <td>
                  <?php
                  if($is_amp){
                    $swUrl = esc_url(site_url()."/pwa-amp-sw.js");
                    $file_headers = @checkStatus($swUrl);                   
                    if(!$file_headers) {
                            printf( '<p><span class="dashicons dashicons-no-alt" style="color: #dc3232;"></span><a class="pwaforwp-service-activate" data-id="pwa-amp-sw" href="#">'.esc_html__( 'Click here to setup', 'pwa-for-wp' ).'</a> </p>' );
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
                            echo '<p><span class="dashicons dashicons-yes" style="color: #46b450;"></span> </p><p>'.esc_html__( 'This site is configure with https', 'pwa-for-wp' ).'</p>' ;
                    } else {
                            echo '<p><span class="dashicons dashicons-no-alt" style="color: #dc3232;"></span> </p><p>'.esc_html__( 'This site is not configure with https', 'pwa-for-wp' ).'</p>';                                     
                    }
                  ?>  
                </td>
                <td>
                    
                </td>
            </tr>
            
            </tbody>    
        </table>
        
        <?php
}

function pwaforwp_amp_status_callback(){
        $swUrl = esc_url(site_url()."/sw.js");
	$file_headers = @checkStatus($swUrl);	
	if(!$file_headers) {
		printf( '<p><span class="dashicons dashicons-no-alt" style="color: #dc3232;"></span> </p>' );
	}else{
		printf( '<p><span class="dashicons dashicons-yes" style="color: #46b450;"></span> </p>' );
	}
}

function checkStatus($swUrl){
	$settings = pwaforwp_defaultSettings();
        $manualfileSetup ="";
        if(array_key_exists('manualfileSetup', $settings)){
        $manualfileSetup = $settings['manualfileSetup'];    
        }	
	if($manualfileSetup){
		$wppath = str_replace("//","/",str_replace("\\","/",realpath(ABSPATH))."/");
		$swjsFile = $wppath.PWAFORWP_FILE_PREFIX."-amp-sw.js";
		$swHtmlFile = $wppath.PWAFORWP_FILE_PREFIX."-amp-sw.html";
                $swrFile = $wppath.PWAFORWP_FILE_PREFIX."-pwa-register-sw.js";
		$swmanifestFile = $wppath.PWAFORWP_FILE_PREFIX."-amp-manifest.json";
                
                $swjsFileNonAmp = $wppath.PWAFORWP_FILE_PREFIX."-sw.js";
                $swmanifestFileNonAmp = $wppath.PWAFORWP_FILE_PREFIX."-manifest.json";                
		switch ($swUrl) {
			case site_url()."/pwa-amp-manifest.json":
				if(file_exists($swmanifestFile)){
					return true;
				}
				break;
			case site_url()."/pwa-amp-sw.js":
				if(file_exists($swjsFile)){
					return true;
				}
				break;
                        case site_url()."/pwa-sw.js":
				if(file_exists($swjsFileNonAmp)){
					return true;
				}
				break;
                        case site_url()."/pwa-manifest.json":
				if(file_exists($swmanifestFileNonAmp)){
					return true;
				}
				break;
                        case site_url()."/pwa-amp-sw.html":
				if(file_exists($swHtmlFile)){
					return true;
				}
				break;  
                        case site_url()."/pwa-register-sw.js":
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
	if(!$file_headers || $file_headers[0] == 'HTTP/1.0 404 Not Found' || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
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
	if ( strpos( $hook, 'pwaforwp' ) === false ) {
		return;
	}	
	// Color picker CSS
	// @refer https://make.wordpress.org/core/2012/11/30/new-color-picker-in-wp-3-5/
        wp_enqueue_style( 'wp-color-picker' );	
	// Everything needed for media upload
        wp_enqueue_media();        
	
        wp_enqueue_style( 'pwaforwp-main-css', PWAFORWP_PLUGIN_URL . 'assets/main-css.css',PWAFORWP_PLUGIN_VERSION,true );            
        // Main JS
        wp_register_script('pwaforwp-main-js', PWAFORWP_PLUGIN_URL . 'assets/main-script.js', array( 'wp-color-picker' ), PWAFORWP_PLUGIN_VERSION, true);
        $object_name = array(
            'uploader_title' => esc_html('Application Icon', 'pwa-for-wp'),
            'splash_uploader_title' => esc_html('Splash Screen Icon', 'pwa-for-wp'),
            'uploader_button' => esc_html('Select Icon', 'pwa-for-wp'),
            'file_status' => esc_html('Check permission or download from manual', 'pwa-for-wp'),
        );
        wp_localize_script('pwaforwp-main-js', 'pwaforwp_obj', $object_name);
        wp_enqueue_script('pwaforwp-main-js');
}
add_action( 'admin_enqueue_scripts', 'pwaforwp_enqueue_style_js' );