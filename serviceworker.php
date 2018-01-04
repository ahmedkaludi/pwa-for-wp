<?php
/**
Plugin Name: AMP Service workers
Description: 
Author: AMP For WP
Version: 0.0.1
Author URI: https://ampforwp.com
Plugin URI: https://ampforwp.com
Text Domain: amp-service-worker
Domain Path: /languages/
SKU: PWA
 *
 * The main plugin file
 *
 */
 
	define('AMPFORWP_SERVICEWORKER_PLUGIN_DIR', plugin_dir_path( __FILE__ ));
add_action( 'init', 'ampforwp_serviceeorker_rewrite_rules' );
function ampforwp_serviceeorker_rewrite_rules(){
	add_rewrite_rule('sw.js','wp-content/plugins/amp-service-worker/sw.js','top');
	add_rewrite_rule('offline_index.html','wp-content/plugins/amp-service-worker/layouts/offline/index.html','top');
	add_rewrite_rule('404.html','wp-content/plugins/amp-service-worker/layouts/404.html','top');
}
		add_filter('amp_post_template_data','ampforwp_service_worker_script', 20);
		function ampforwp_service_worker_script( $data ){
			if ( empty( $data['amp_component_scripts']['amp-install-serviceworker'] ) ) {
				$data['amp_component_scripts']['amp-install-serviceworker'] = 'https://cdn.ampproject.org/v0/amp-install-serviceworker-0.1.js';
			}
			return $data;
		}

		add_action('ampforwp_global_after_footer','ampforwp_service_worker',10);
		add_action('amp_before_footer','ampforwp_service_worker',10);
			function ampforwp_service_worker(){
				$url = str_replace("http:","https:",get_site_url());
				?><amp-install-serviceworker src="<?php echo $url."/sw.js"; ?>" data-iframe-src="<?php echo $url.'/sw.html'; ?>"  layout="nodisplay">
		</amp-install-serviceworker><?php
			}

	
	 