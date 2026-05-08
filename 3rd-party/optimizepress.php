<?php
/**
 * OptimizePress 3 compatibility: blank template disables most third-party scripts.
 *
 * @link https://docs.optimizepress.com/en/articles/23-hooks-whitelist-scripts-or-styles
 *
 * @package PWAforWP
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Allow PWA for WP service worker registration script on OP blank templates.
 *
 * @param bool   $allowed Whether the script is allowed.
 * @param string $handle  WordPress script handle.
 * @return bool
 */
function pwaforwp_optimizepress_allow_pwa_register_script( $allowed, $handle ) {
	if ( 'pwa-main-script' === $handle ) {
		return true;
	}
	return $allowed;
}
add_filter( 'op3_script_is_allowed_in_blank_template', 'pwaforwp_optimizepress_allow_pwa_register_script', 10, 2 );
