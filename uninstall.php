<?php
/**
 * Uninstall AMP For wp
 *
 */// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

$options = get_option('pwaforwp_settings');  

if ( isset( $options['pwa_uninstall_data'] ) && 1 == $options['pwa_uninstall_data'] ) {
	if ( is_multisite() ) {

		delete_site_option('pwaforwp_settings');
		delete_site_option('pwaforwp_pre_cache_post_ids');
		delete_site_option('pwaforwp_update_pre_cache_list');
		delete_site_option('pwaforwp_admin_notice_transient');
		delete_site_option('pwaforwp_review_never');
		delete_site_option('pwaforwp_activation_date');
		delete_site_option('_transient_pwaforwp_restapi_check');
		delete_site_option('pwa_uninstall_data');
	}else{

		delete_option("pwaforwp_settings");
		delete_option("pwaforwp_pre_cache_post_ids");
		delete_option("pwaforwp_update_pre_cache_list");
		delete_option("pwaforwp_admin_notice_transient");
		delete_option("pwaforwp_review_never");
		delete_option("pwaforwp_activation_date");
		delete_option("_transient_pwaforwp_restapi_check");
		delete_option('pwa_uninstall_data');
	}
}