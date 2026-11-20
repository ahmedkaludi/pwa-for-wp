<?php
/**
 * WP User Frontend compatibility.
 *
 * Account, login, registration, and form pages include nonces and user-specific
 * HTML. Keep those URLs out of the service worker cache.
 *
 * @link https://wordpress.org/plugins/wp-user-frontend/
 *
 * @package PWAforWP
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether WP User Frontend is loaded.
 *
 * Checked at runtime because this file is included before plugins_loaded.
 *
 * @return bool
 */
function pwaforwp_is_wpuf_active() {
	return function_exists( 'wpuf' ) || class_exists( 'WP_User_Frontend' );
}

/**
 * Page ID stored by WP User Frontend.
 *
 * @param string $option  Option key inside the section.
 * @param string $section WPUF settings option name.
 * @return int
 */
function pwaforwp_wpuf_get_page_id( $option, $section ) {
	$value = '';
	if ( function_exists( 'wpuf_get_option' ) ) {
		$value = wpuf_get_option( $option, $section, '' );
	} else {
		$options = get_option( $section, array() );
		if ( is_array( $options ) && isset( $options[ $option ] ) ) {
			$value = $options[ $option ];
		}
	}
	return absint( $value );
}

/**
 * Page IDs selected in WP User Frontend settings.
 *
 * @return int[]
 */
function pwaforwp_wpuf_configured_page_ids() {
	$map = array(
		'wpuf_my_account'       => array( 'account_page' ),
		'wpuf_profile'          => array( 'login_page', 'reg_override_page' ),
		'wpuf_frontend_posting' => array( 'edit_page_id' ),
		'wpuf_payment'          => array( 'subscription_page', 'payment_page', 'payment_success' ),
	);
	$ids = array();
	foreach ( $map as $section => $options ) {
		foreach ( $options as $option ) {
			$id = pwaforwp_wpuf_get_page_id( $option, $section );
			if ( $id ) {
				$ids[] = $id;
			}
		}
	}
	return array_values( array_unique( $ids ) );
}

/**
 * Shortcodes and block markup that mark a WPUF screen.
 *
 * @return string[]
 */
function pwaforwp_wpuf_content_needles() {
	return array(
		'[wpuf_form',
		'[wpuf_dashboard',
		'[wpuf-login',
		'[wpuf_profile',
		'[wpuf_account',
		'[wpuf_edit',
		'[wpuf_sub_pack',
		'[wpuf-registration',
		'<!-- wp:wpuf/',
	);
}

/**
 * Whether post content renders a WP User Frontend form or account screen.
 *
 * @param string $content Post content.
 * @return bool
 */
function pwaforwp_wpuf_content_uses_plugin( $content ) {
	$content = (string) $content;
	if ( '' === $content ) {
		return false;
	}
	$shortcodes = array(
		'wpuf_form',
		'wpuf_dashboard',
		'wpuf-login',
		'wpuf_profile',
		'wpuf_account',
		'wpuf_edit',
		'wpuf_sub_pack',
		'wpuf-registration',
	);
	foreach ( $shortcodes as $shortcode ) {
		if ( has_shortcode( $content, $shortcode ) ) {
			return true;
		}
	}
	return false !== strpos( $content, '<!-- wp:wpuf/' );
}

/**
 * Published posts and pages that embed WP User Frontend.
 *
 * @return int[]
 */
function pwaforwp_wpuf_content_page_ids() {
	global $wpdb;

	$likes = array();
	foreach ( pwaforwp_wpuf_content_needles() as $needle ) {
		$likes[] = $wpdb->prepare( 'post_content LIKE %s', '%' . $wpdb->esc_like( $needle ) . '%' );
	}

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- content search used when building the service worker, not on front-end requests.
	$ids = $wpdb->get_col(
		"SELECT ID FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type IN ('post','page') AND (" . implode( ' OR ', $likes ) . ')'
	);

	return array_values( array_unique( array_map( 'absint', (array) $ids ) ) );
}

/**
 * Path prefix safe for the service worker bypass list.
 *
 * The site root is omitted so a form on the homepage cannot bypass every URL.
 *
 * @param string $path URL path.
 * @return string
 */
function pwaforwp_wpuf_normalize_path( $path ) {
	$path = '/' . ltrim( (string) $path, '/' );
	$path = untrailingslashit( $path );
	if ( '' === $path ) {
		$path = '/';
	}

	$home = wp_parse_url( home_url( '/' ), PHP_URL_PATH );
	$home = is_string( $home ) ? untrailingslashit( $home ) : '';
	if ( '' === $home ) {
		$home = '/';
	}

	if ( '/' === $path || $path === $home || ! pwaforwp_wpuf_is_safe_bypass_path( $path ) ) {
		return '';
	}

	return $path;
}

/**
 * Reject paths that would turn off caching for the whole site or for PWA files.
 *
 * The service worker treats each bypass entry as a prefix, so a short or core
 * path would also skip every URL under it.
 *
 * @param string $path Normalized pathname.
 * @return bool
 */
function pwaforwp_wpuf_is_safe_bypass_path( $path ) {
	$segments = array_values( array_filter( explode( '/', trim( (string) $path, '/' ) ), 'strlen' ) );
	if ( empty( $segments ) ) {
		return false;
	}

	$blocked_segments = array( 'wp-admin', 'wp-includes', 'wp-content' );
	foreach ( $segments as $segment ) {
		if ( in_array( $segment, $blocked_segments, true ) ) {
			return false;
		}
	}

	$json_at = array_search( 'wp-json', $segments, true );
	if ( false !== $json_at ) {
		$next = isset( $segments[ $json_at + 1 ] ) ? $segments[ $json_at + 1 ] : '';
		if ( 'wpuf' !== $next ) {
			return false;
		}
	}

	$basename = end( $segments );
	if ( preg_match( '/^pwa-(sw|register-sw|manifest|amp-sw|amp-manifest|push-notification-manifest)(-[0-9]+)?\\.(js|json|html)$/', $basename ) ) {
		return false;
	}
	if ( 'firebase-messaging-sw.js' === $basename ) {
		return false;
	}

	return true;
}

/**
 * Pathname for a published post, or an empty string when it should not be bypassed.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function pwaforwp_wpuf_path_from_post_id( $post_id ) {
	$post_id = absint( $post_id );
	if ( ! $post_id || 'publish' !== get_post_status( $post_id ) ) {
		return '';
	}

	$permalink = get_permalink( $post_id );
	if ( ! $permalink ) {
		return '';
	}

	$path = wp_parse_url( $permalink, PHP_URL_PATH );
	if ( ! is_string( $path ) ) {
		return '';
	}

	return pwaforwp_wpuf_normalize_path( $path );
}

/**
 * Service worker path prefixes for WP User Frontend.
 *
 * @param bool $reset Drop the in-request cache.
 * @return string[]
 */
function pwaforwp_wpuf_bypass_paths( $reset = false ) {
	static $paths = null;

	if ( $reset ) {
		$paths = null;
	}
	if ( null !== $paths ) {
		return $paths;
	}

	$paths = array();
	if ( ! pwaforwp_is_wpuf_active() ) {
		return $paths;
	}

	$post_ids = array_merge( pwaforwp_wpuf_configured_page_ids(), pwaforwp_wpuf_content_page_ids() );
	foreach ( array_unique( $post_ids ) as $post_id ) {
		$path = pwaforwp_wpuf_path_from_post_id( $post_id );
		if ( '' !== $path ) {
			$paths[] = $path;
		}
	}

	$rest_path = '';
	if ( function_exists( 'rest_url' ) ) {
		$rest_url  = rest_url( 'wpuf' );
		$rest_path = wp_parse_url( $rest_url, PHP_URL_PATH );
	}
	if ( ! is_string( $rest_path ) || '' === $rest_path ) {
		$rest_path = '/wp-json/wpuf';
	}
	$rest_path = pwaforwp_wpuf_normalize_path( $rest_path );
	if ( '' !== $rest_path ) {
		$paths[] = $rest_path;
	}

	$paths = array_values( array_unique( $paths ) );
	sort( $paths );

	return $paths;
}

/**
 * Add WP User Frontend URLs to the service worker bypass list.
 *
 * @param string[] $paths Existing bypass paths.
 * @return string[]
 */
function pwaforwp_wpuf_filter_sw_bypass_paths( $paths ) {
	if ( ! pwaforwp_is_wpuf_active() ) {
		return $paths;
	}
	$extra = pwaforwp_wpuf_bypass_paths();
	if ( empty( $extra ) ) {
		return $paths;
	}
	return array_values( array_unique( array_merge( (array) $paths, $extra ) ) );
}
add_filter( 'pwaforwp_visibility_sw_bypass_paths', 'pwaforwp_wpuf_filter_sw_bypass_paths' );

/**
 * Stop the browser HTTP cache from keeping a stale WPUF form or account page.
 */
function pwaforwp_wpuf_send_nocache_headers() {
	if ( ! pwaforwp_is_wpuf_active() || ! is_singular() ) {
		return;
	}

	$post = get_queried_object();
	if ( ! ( $post instanceof WP_Post ) ) {
		return;
	}

	$is_configured = in_array( (int) $post->ID, pwaforwp_wpuf_configured_page_ids(), true );
	if ( ! $is_configured && ! pwaforwp_wpuf_content_uses_plugin( $post->post_content ) ) {
		return;
	}

	nocache_headers();
}
add_action( 'send_headers', 'pwaforwp_wpuf_send_nocache_headers' );

/**
 * Rewrite only the service worker scripts so the bypass list matches WPUF pages.
 *
 * Manifest, push-notification, and register files are left as they are.
 * Front-end and AJAX requests are skipped.
 */
function pwaforwp_wpuf_regenerate_service_worker() {
	static $running = false;

	if ( $running || ! is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
		return;
	}
	if ( ! class_exists( 'PWAFORWP_File_Creation_Init' ) ) {
		return;
	}
	if ( function_exists( 'pwaforwp_current_user_can' ) && ! current_user_can( pwaforwp_current_user_can() ) ) {
		return;
	}

	$running = true;
	pwaforwp_wpuf_bypass_paths( true );
	$paths  = pwaforwp_is_wpuf_active() ? pwaforwp_wpuf_bypass_paths() : array();
	$writer = new PWAFORWP_File_Creation_Init();
	$status = $writer->pwaforwp_swjs_init();

	if ( $status && ( function_exists( 'ampforwp_is_amp_endpoint' ) || function_exists( 'is_amp_endpoint' ) ) ) {
		$writer->pwaforwp_swjs_init_amp();
	}

	if ( $status ) {
		update_option( 'pwaforwp_wpuf_bypass_hash', md5( wp_json_encode( $paths ) ), false );
	}
	$running = false;
}

/**
 * Regenerate when a WPUF settings section is saved.
 */
function pwaforwp_wpuf_on_settings_update() {
	pwaforwp_wpuf_regenerate_service_worker();
}

/**
 * Regenerate once an admin loads if the bypass list has changed.
 *
 * Covers the first request after this compatibility is installed, slug edits,
 * and shortcode pages created outside the WPUF settings screens.
 */
function pwaforwp_wpuf_maybe_regenerate_service_worker() {
	if ( wp_doing_ajax() || wp_doing_cron() ) {
		return;
	}
	if ( function_exists( 'pwaforwp_current_user_can' ) && ! current_user_can( pwaforwp_current_user_can() ) ) {
		return;
	}

	$saved = get_option( 'pwaforwp_wpuf_bypass_hash', '' );
	if ( '' === $saved && ! pwaforwp_is_wpuf_active() ) {
		return;
	}

	$paths = pwaforwp_is_wpuf_active() ? pwaforwp_wpuf_bypass_paths() : array();
	$hash  = md5( wp_json_encode( $paths ) );

	if ( (string) $saved === $hash ) {
		return;
	}

	pwaforwp_wpuf_regenerate_service_worker();
}
add_action( 'admin_init', 'pwaforwp_wpuf_maybe_regenerate_service_worker', 20 );

foreach ( array( 'wpuf_my_account', 'wpuf_profile', 'wpuf_frontend_posting', 'wpuf_payment' ) as $pwaforwp_wpuf_option ) {
	add_action( 'update_option_' . $pwaforwp_wpuf_option, 'pwaforwp_wpuf_on_settings_update', 20 );
	add_action( 'add_option_' . $pwaforwp_wpuf_option, 'pwaforwp_wpuf_on_settings_update', 20 );
}
unset( $pwaforwp_wpuf_option );
