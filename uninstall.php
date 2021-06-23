<?php
/**
 * Uninstall AMP For wp
 *
 */// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}
if ( is_multisite() ) {

	delete_site_option('pwaforwp_settings');
	delete_site_option('pwaforwp_pre_cache_post_ids');
	delete_site_option('pwaforwp_update_pre_cache_list');
	delete_site_option('pwaforwp_admin_notice_transient');
	delete_site_option('pwaforwp_review_never');
}else{

	delete_option("pwaforwp_settings");
	delete_option("pwaforwp_pre_cache_post_ids");
	delete_option("pwaforwp_update_pre_cache_list");
	delete_option("pwaforwp_admin_notice_transient");
	delete_option("pwaforwp_review_never");
}