<?php
/**
 * Admin Settings
 * Function ampforwp_pwa_add_menu_links
 *
 */
function ampforwp_pwa_add_menu_links() {
	
	// Main menu page
	add_menu_page( __( 'AMPforWP Progressive Web Apps', 'ampforwp-progressive-web-app' ), __( 'AMPforWP PWA', 'ampforwp-progressive-web-app' ), 'manage_options', 'ampforwp-pwa','ampforwppwa_admin_interface_render', '', 100 );
	
	// Settings page - Same as main menu page
	add_submenu_page( 'ampforwp-pwa', __( 'AMPforWP Progressive Web Apps', 'ampforwp-progressive-web-app' ), __( 'Settings', 'ampforwp-progressive-web-app' ), 'manage_options', 'ampforwp-pwa', 'ampforwppwa_admin_interface_render' );
	
}
add_action( 'admin_menu', 'ampforwp_pwa_add_menu_links' );

function ampforwppwa_admin_interface_render(){
	// Authentication
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	global $wpdb;

	// Handing save settings
	if ( isset( $_GET['amp-pwa-settings-updated'] ) ) {
		
		// Add settings saved message with the class of "updated"
		add_settings_error( 'amppwa_setting_dashboard_group', 'amppwa_settings_saved_message', __( 'Settings saved.', 'ampforwp-progressive-web-app' ), 'updated' );
		
		// Show Settings Saved Message
		settings_errors( 'amppwa_setting_dashboard_group' );
	}
	$tab = ampforwp_pwa_get_tab('dashboard', array('dashboard','general','design','help'));
	?>
	<div class="wrap">	
		<h1>AMPforWp Progressive Web Apps</h1>
		<h2 class="nav-tab-wrapper amppwa-tabs">
			<?php

			echo '<a href="' . ampforwp_pwa_admin_link() . '" class="nav-tab ' . ( $tab == 'dashboard' ? 'nav-tab-active' : '') . '"><span class="dashicons dashicons-dashboard"></span> ' . __('Dashboard') . '</a>';

			echo '<a href="' . ampforwp_pwa_admin_link('general') . '" class="nav-tab ' . ( $tab == 'general' ? 'nav-tab-active' : '') . '"><span class="dashicons dashicons-welcome-view-site"></span> ' . __('General','ampforwp-progressive-web-app') . '</a>';

			echo '<a href="' . ampforwp_pwa_admin_link('design') . '" class="nav-tab ' . ( $tab == 'design' ? 'nav-tab-active' : '') . '"><span class="dashicons dashicons-welcome-view-site"></span> ' . __('Design','ampforwp-progressive-web-app') . '</a>';

			echo '<a href="' . ampforwp_pwa_admin_link('help') . '" class="nav-tab ' . ( $tab == 'help' ? 'nav-tab-active' : '') . '"><span class="dashicons dashicons-welcome-view-site"></span> ' . __('Help','ampforwp-progressive-web-app') . '</a>';
			?>
		</h2>
		<?php settings_errors(); ?>
		<form action="options.php" method="post" enctype="multipart/form-data">		
			<?php
			// Output nonce, action, and option_page fields for a settings page.
			settings_fields( 'amppwa_setting_dashboard_group' );
			
			
			
			echo "<div class='amp-pwa-dashboard' ".( $tab != 'dashboard' ? 'style="display:none;"' : '').">";
			// Status
			do_settings_sections( 'amp_pwa_dashboard_section' );	// Page slug
			echo "</div>";

			echo "<div class='amp-pwa-general' ".( $tab != 'general' ? 'style="display:none;"' : '').">";
				// general Application Settings
				do_settings_sections( 'amp_pwa_general_section' );	// Page slug
			echo "</div>";

			echo "<div class='amp-pwa-design' ".( $tab != 'design' ? 'style="display:none;"' : '').">";
				// design Application Settings
				do_settings_sections( 'amp_pwa_design_section' );	// Page slug
			echo "</div>";
			echo "<div class='amp-pwa-help' ".( $tab != 'help' ? 'style="display:none;"' : '').">";
				// design Application Settings
				do_settings_sections( 'amp_pwa_help_section' );	// Page slug
			echo "</div>";

			// Output save settings button
			submit_button( __('Save Settings', 'ampforwp-progressive-web-app') );
			?>
		</form>
	</div>
	<?php
}


/*
	WP Settings API
*/
add_action('admin_init', 'ampforwp_pwa_settings_init');

function ampforwp_pwa_settings_init(){
	register_setting( 'amppwa_setting_dashboard_group', 'dashboard' );

	add_settings_section('amp_pwa_dashboard_section', __('Status','ampforwp-progressive-web-app'), '__return_false', 'amp_pwa_dashboard_section');
		// Manifest status
		add_settings_field(
			'amppwa_manifest_status',								// ID
			__('Manifest', 'ampforwp-progressive-web-app'),			// Title
			'amp_pwa_manifest_status_callback',					// Callback
			'amp_pwa_dashboard_section',							// Page slug
			'amp_pwa_dashboard_section'							// Settings Section ID
		);

		add_settings_field(
			'amppwa_sw_status',									// ID
			__('Service Worker', 'ampforwp-progressive-web-app'),		// Title
			'amp_pwa_sw_status_callback',								// Callback
			'amp_pwa_dashboard_section',							// Page slug
			'amp_pwa_dashboard_section'							// Settings Section ID
		);	

		// HTTPS status
		add_settings_field(
			'amppwa_https_status',								// ID
			__('HTTPS', 'ampforwp-progressive-web-app'),				// Title
			'amp_pwa_https_status_callback',								// CB
			'amp_pwa_dashboard_section',							// Page slug
			'amp_pwa_dashboard_section'							// Settings Section ID
		);

	add_settings_section('amp_pwa_general_section', __return_false(), '__return_false', 'amp_pwa_general_section');

		// Application Name
		add_settings_field(
			'amppwa_app_name',									// ID
			__('Application Name', 'ampforwp-progressive-web-app'),	// Title
			'amp_pwa_app_name_callback',									// CB
			'amp_pwa_general_section',						// Page slug
			'amp_pwa_general_section'						// Settings Section ID
		);

		// Application Short Name
		add_settings_field(
			'amppwa_app_short_name',								// ID
			__('Application Short Name', 'ampforwp-progressive-web-app'),	// Title
			'amp_pwa_app_short_name_callback',							// CB
			'amp_pwa_general_section',						// Page slug
			'amp_pwa_general_section'						// Settings Section ID
		);

		// Description
		add_settings_field(
			'amppwa_app_description',									// ID
			__( 'Description', 'ampforwp-progressive-web-app' ),		// Title
			'amp_pwa_description_callback',								// CB
			'amp_pwa_general_section',						// Page slug
			'amp_pwa_general_section'						// Settings Section ID
		);
		
		// Application Icon
		add_settings_field(
			'amppwa_app_icons',										// ID
			__('Application Icon', 'ampforwp-progressive-web-app'),	// Title
			'amp_pwa_app_icon_callback',									// Callback function
			'amp_pwa_general_section',						// Page slug
			'amp_pwa_general_section'						// Settings Section ID
		);
		
		// Splash Screen Icon
		add_settings_field(
			'amppwa_app_splash_icon',									// ID
			__('Splash Screen Icon', 'ampforwp-progressive-web-app'),	// Title
			'amp_pwa_splash_icon_callback',								// Callback function
			'amp_pwa_general_section',						// Page slug
			'amp_pwa_general_section'						// Settings Section ID
		);

		// Offline Page
		add_settings_field(
			'amppwa_offline_page',								// ID
			__('Offline Page', 'ampforwp-progressive-web-app'),		// Title
			'amp_pwa_offline_page_callback',								// CB
			'amp_pwa_general_section',						// Page slug
			'amp_pwa_general_section'						// Settings Section ID
		);

		// 404 Page
		add_settings_field(
			'amppwa_404_page',								// ID
			__('404 Page', 'ampforwp-progressive-web-app'),		// Title
			'amp_pwa_404_page_callback',								// CB
			'amp_pwa_general_section',						// Page slug
			'amp_pwa_general_section'						// Settings Section ID
		);
		
		// Orientation
		add_settings_field(
			'amppwa_orientation',									// ID
			__('Orientation', 'ampforwp-progressive-web-app'),		// Title
			'amp_pwa_orientation_callback',								// CB
			'amp_pwa_general_section',						// Page slug
			'amp_pwa_general_section'						// Settings Section ID
		);

	add_settings_section('amp_pwa_design_section', __return_false(), '__return_false', 'amp_pwa_design_section');
		// Splash Screen Background Color
		add_settings_field(
			'amppwa_background_color',							// ID
			__('Background Color', 'ampforwp-progressive-web-app'),	// Title
			'amp_pwa_background_color_callback',							// CB
			'amp_pwa_design_section',						// Page slug
			'amp_pwa_design_section'						// Settings Section ID
		);
		
		// Theme Color
		add_settings_field(
			'amppwa_theme_color',									// ID
			__('Theme Color', 'ampforwp-progressive-web-app'),		// Title
			'amp_pwa_theme_color_callback',								// CB
			'amp_pwa_design_section',						// Page slug
			'amp_pwa_design_section'						// Settings Section ID
		);
}

//Design Settings
function amp_pwa_background_color_callback(){
	// Get Settings
	$settings = ampforwp_pwa_defaultSettings(); ?>
	
	<!-- Background Color -->
	<input type="text" name="ampforwp_pwa_settings[background_color]" id="ampforwp_pwa_settings[background_color]" class="ampforwp-pwa-colorpicker" value="<?php echo isset( $settings['background_color'] ) ? esc_attr( $settings['background_color']) : '#D5E0EB'; ?>" data-default-color="#D5E0EB">
	
	<p class="description">
		<?php _e('Background color of the splash screen.', 'ampforwp-progressive-web-app'); ?>
	</p>

	<?php
}
function amp_pwa_theme_color_callback(){
	// Get Settings
	$settings = ampforwp_pwa_defaultSettings(); ?>
	<!-- Theme Color -->
	<input type="text" name="ampforwp_pwa_settings[theme_color]" id="ampforwp_pwa_settings[theme_color]" class="ampforwp-pwa-colorpicker" value="<?php echo isset( $settings['theme_color'] ) ? esc_attr( $settings['theme_color']) : '#D5E0EB'; ?>" data-default-color="#D5E0EB">
	
	<p class="description">
		<?php _e('Theme color is used on supported devices to tint the UI elements of the browser and app switcher. When in doubt, use the same color as <code>Background Color</code>.', 'ampforwp-pwa-progressive-web-apps'); ?>
	</p>
	<?php
}

//General settings
function amp_pwa_app_name_callback(){
	// Get Settings
	$settings = ampforwp_pwa_defaultSettings(); ?>
	
	<fieldset>
		<input type="text" name="ampforwp_pwa_settings[app_blog_name]" class="regular-text" value="<?php if ( isset( $settings['app_blog_name'] ) && ( ! empty($settings['app_blog_name']) ) ) echo esc_attr($settings['app_blog_name']); ?>"/>
	</fieldset>

	<?php
}

function amp_pwa_app_short_name_callback(){
	// Get Settings
	$settings = ampforwp_pwa_defaultSettings(); ?>
	
	<fieldset>
		<input type="text" name="ampforwp_pwa_settings[app_blog_short_name]" class="regular-text" value="<?php if ( isset( $settings['app_blog_short_name'] ) && ( ! empty($settings['app_blog_short_name']) ) ) echo esc_attr($settings['app_blog_short_name']); ?>"/>
		<p class="description">
			<?php _e('Used space to display the full name of the application. <code>12</code> characters or less.', 'ampforwp-progressive-web-app'); ?>
		</p>
	</fieldset>
	<?php
}

function amp_pwa_description_callback(){
	// Get Settings
	$settings = ampforwp_pwa_defaultSettings(); ?>
	<fieldset>
		<input type="text" name="ampforwp_pwa_settings[description]" class="regular-text" value="<?php if ( isset( $settings['description'] ) && ( ! empty( $settings['description'] ) ) ) echo esc_attr( $settings['description'] ); ?>"/>
		
		<p class="description">
			<?php _e( 'Brief description of your APP.', 'ampforwp-progressive-web-app' ); ?>
		</p>
	</fieldset>

	<?php
}

function amp_pwa_app_icon_callback(){
	// Get Settings
	$settings = ampforwp_pwa_defaultSettings(); ?>
	
	<!-- Application Icon -->
	<input type="text" name="ampforwp_pwa_settings[icon]" id="ampforwp_pwa_settings[icon]" class="amppwa-icon regular-text" size="50" value="<?php echo isset( $settings['icon'] ) ? esc_attr( $settings['icon']) : ''; ?>">
	<button type="button" class="button ampforwp-pwa-icon-upload" data-editor="content">
		<span class="dashicons dashicons-format-image" style="margin-top: 4px;"></span> Choose Icon
	</button>
	
	<p class="description">
		<?php _e('This will be the icon of your app when installed on the phone. Must be a <code>PNG</code> image exactly <code>192x192</code> in size.', 'ampforwp-progressive-web-app'); ?>
	</p>
	<?php
}

function amp_pwa_splash_icon_callback(){
	// Get Settings
	$settings = ampforwp_pwa_defaultSettings(); ?>
	
	<!-- Splash Screen Icon -->
	<input type="text" name="ampforwp_pwa_settings[splash_icon]" id="ampforwp_pwa_settings[splash_icon]" class="amppwa-splash-icon regular-text" size="50" value="<?php echo isset( $settings['splash_icon'] ) ? esc_attr( $settings['splash_icon']) : ''; ?>">
	<button type="button" class="button amppwa-splash-icon-upload" data-editor="content">
		<span class="dashicons dashicons-format-image" style="margin-top: 4px;"></span> Choose Icon
	</button>
	
	<p class="description">
		<?php _e('This icon will be displayed on the splash screen of your APP on supported devices. Must be a <code>PNG</code> image exactly <code>512x512</code> in size.', 'ampforwp-progressive-web-app'); ?>
	</p>

	<?php
}

function amp_pwa_offline_page_callback(){
	// Get Settings
	$settings = ampforwp_pwa_defaultSettings(); ?>
	<!-- WordPress Pages Dropdown -->
	<label for="ampforwp_pwa_settings[offline_page]">
	<?php echo wp_dropdown_pages( array( 
			'name' => 'ampforwp_pwa_settings[offline_page]', 
			'echo' => 0, 
			'show_option_none' => __( '&mdash; Default &mdash;' ), 
			'option_none_value' => '0', 
			'selected' =>  isset($settings['offline_page']) ? $settings['offline_page'] : '',
		)); ?>
	</label>
	
	<p class="description">
		<?php printf( __( 'Offline page is displayed when the device is offline and the requested page is not already cached. Current offline page is <code>%s</code>', 'ampforwp-progressive-web-app' ), get_permalink($settings['offline_page']) ? get_permalink( $settings['offline_page'] ) : get_bloginfo( 'wpurl' ) ); ?>
	</p>

	<?php
}

function amp_pwa_404_page_callback(){
	// Get Settings
	$settings = ampforwp_pwa_defaultSettings(); ?>
	<!-- WordPress Pages Dropdown -->
	<label for="ampforwp_pwa_settings[404_page]">
	<?php echo wp_dropdown_pages( array( 
			'name' => 'ampforwp_pwa_settings[404_page]', 
			'echo' => 0, 
			'show_option_none' => __( '&mdash; Default &mdash;' ), 
			'option_none_value' => '0', 
			'selected' =>  isset($settings['404_page']) ? $settings['404_page'] : '',
		)); ?>
	</label>
	
	<p class="description">
		<?php printf( __( '404 page is displayed and the requested page is not already cached. Current offline page is <code>%s</code>', 'ampforwp-progressive-web-app' ), get_permalink($settings['404_page']) ? get_permalink( $settings['404_page'] ) : '' ); ?>
	</p>

	<?php
}

function amp_pwa_orientation_callback(){
	// Get Settings
	$settings = ampforwp_pwa_defaultSettings(); ?>
	
	<!-- Orientation Dropdown -->
	<label for="ampforwp_pwa_settings[orientation]">
		<select name="ampforwp_pwa_settings[orientation]" id="ampforwp_pwa_settings[orientation]">
			<option value="0" <?php if ( isset( $settings['orientation'] ) ) { selected( $settings['orientation'], 0 ); } ?>>
				<?php _e( 'Follow Device Orientation', 'ampforwp-progressive-web-app' ); ?>
			</option>
			<option value="1" <?php if ( isset( $settings['orientation'] ) ) { selected( $settings['orientation'], 1 ); } ?>>
				<?php _e( 'Portrait', 'ampforwp-progressive-web-app' ); ?>
			</option>
			<option value="2" <?php if ( isset( $settings['orientation'] ) ) { selected( $settings['orientation'], 2 ); } ?>>
				<?php _e( 'Landscape', 'ampforwp-progressive-web-app' ); ?>
			</option>
		</select>
	</label>
	
	<p class="description">
		<?php _e( 'Set the orientation of your app on devices. When set to <code>Follow Device Orientation</code> your app will rotate as the device is rotated.', 'ampforwp-progressive-web-app' ); ?>
	</p>

	<?php
}




// Dashboard
function amp_pwa_manifest_status_callback(){
	printf( '<p><span class="dashicons dashicons-yes" style="color: #46b450;"></span> ' . __( 'Manifest generated successfully. You can <a href="%s" target="_blank">see it here &rarr;</a>', 'ampforwp-progressive-web-app' ) . '</p>', 'manifest url' );
}

function amp_pwa_sw_status_callback(){
	printf( '<p><span class="dashicons dashicons-yes" style="color: #46b450;"></span> ' . __( 'Service worker generated successfully.', 'ampforwp-progressive-web-app' ) . '</p>' );
}

function amp_pwa_https_status_callback(){
	if ( is_ssl() ) {
		
		printf( '<p><span class="dashicons dashicons-yes" style="color: #46b450;"></span> ' . __( 'Your website is served over HTTPS.', 'ampforwp-progressive-web-app' ) . '</p>' );
	} else {
		
		printf( '<p><span class="dashicons dashicons-no-alt" style="color: #dc3232;"></span> ' . __( 'Progressive Web Apps require that your website is served over HTTPS. Please contact your host to add a SSL certificate to your domain.', 'ampforwp-progressive-web-app' ) . '</p>' );
	}
}


/**
 * Enqueue CSS and JS
 */
function ampforwppwa_enqueue_style_js( $hook ) {
    // Load only on ampforwp-pwa plugin pages
	if ( strpos( $hook, 'ampforwp-pwa' ) === false ) {
		return;
	}
	
	// Color picker CSS
	// @refer https://make.wordpress.org/core/2012/11/30/new-color-picker-in-wp-3-5/
    wp_enqueue_style( 'wp-color-picker' );
	
	// Everything needed for media upload
	wp_enqueue_media();
	
	// Main JS
    wp_enqueue_script( 'amp-pwa-main-js', AMPFORWP_SERVICEWORKER_PLUGIN_URL . 'admin/main-script.js', array( 'wp-color-picker' ), AMPFORWP_SERVICEWORKER_PLUGIN_VERSION, true );
}
add_action( 'admin_enqueue_scripts', 'ampforwppwa_enqueue_style_js' );