<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * OneSignal plugin present (v2 class API or v3+ constants).
 *
 * @return bool
 */
function pwaforwp_is_onesignal_plugin_active() {
	if ( class_exists( 'OneSignal' ) ) {
		return true;
	}
	if ( defined( 'ONESIGNAL_PLUGIN_URL' ) || defined( 'ONESIGNAL_PLUGIN_DIR' ) ) {
		return true;
	}
	return false;
}

/**
 * @return bool
 */
function pwaforwp_is_onesignal_pwa_compat_enabled() {
	$settings = pwaforwp_defaultSettings();
	return ! empty( $settings['one_signal_support_setting'] );
}

/**
 * Called when PWA files are (re)generated.
 *
 * @param mixed $action Optional.
 */
function pwaforwp_onesignal_compatiblity( $action = null ) {

	if ( ! pwaforwp_is_onesignal_plugin_active() ) {
		return;
	}
	if ( ! pwaforwp_is_onesignal_pwa_compat_enabled() ) {
		return;
	}

	pwaforwp_use_custom_manifest( $action );
}

/**
 * Register OneSignal merge filter on every load (not only when files are regenerated),
 * so dynamically generated service workers always include importScripts().
 */
function pwaforwp_onesignal_register_runtime_hooks() {
	if ( ! pwaforwp_is_onesignal_plugin_active() || ! pwaforwp_is_onesignal_pwa_compat_enabled() ) {
		return;
	}
	add_filter( 'pwaforwp_sw_js_template', 'pwaforwp_add_sw_to_onesignal_sw', 10, 1 );
	// Run as late as possible so OneSignal’s footer scripts execute first; we then replace their registration.
	add_action( 'wp_footer', 'pwaforwp_onesignal_reassert_sw_after_sdk', 99999 );
}
add_action( 'init', 'pwaforwp_onesignal_register_runtime_hooks', 20 );

/**
 * Restore OneSignal manifest option when PWA is deactivated (once).
 */
function pwaforwp_onesignal_register_deactivation_hook() {
	static $registered = false;
	if ( $registered || ! class_exists( 'OneSignal' ) ) {
		return;
	}
	$registered = true;
	register_deactivation_hook(
		PWAFORWP_PLUGIN_FILE,
		function () {
			$os_settings                        = \OneSignal::get_onesignal_settings();
			$os_settings['use_custom_manifest'] = false;
			\OneSignal::save_onesignal_settings( $os_settings );
		}
	);
}
add_action( 'plugins_loaded', 'pwaforwp_onesignal_register_deactivation_hook', 30 );

function pwaforwp_use_custom_manifest( $action = null ) {

	$url = pwaforwp_home_url();

	$onesignal_option = get_option( 'OneSignalWPSetting' );

	if ( @$onesignal_option['custom_manifest_url'] == '' && @$onesignal_option['use_custom_manifest'] == false ) {

		$onesignal_option['use_custom_manifest'] = true;
		if ( $action ) {
			$onesignal_option['use_custom_manifest'] = false;
		}
		if ( function_exists( 'ampforwp_is_amp_endpoint' ) && ampforwp_is_amp_endpoint() ) {
			$onesignal_option['custom_manifest_url'] = esc_url( pwaforwp_manifest_json_url( true ) );
		} else {
			$onesignal_option['custom_manifest_url'] = esc_url( pwaforwp_manifest_json_url() );
		}
		update_option( 'OneSignalWPSetting', $onesignal_option );

	}
}

function pwaforwp_onesignal_insert_gcm_sender_id( $manifest ) {

	if ( class_exists( 'OneSignal' ) ) {

		if ( is_array( $manifest ) ) {

			$manifest['gcm_sender_id'] = '482941778795';

		}
	}

	return $manifest;
}

add_filter( 'pwaforwp_manifest', 'pwaforwp_onesignal_insert_gcm_sender_id' );

function pwaforwp_onesignal_change_sw_name( $name ) {

	if ( ! pwaforwp_is_onesignal_plugin_active() || ! pwaforwp_is_onesignal_pwa_compat_enabled() ) {
		return $name;
	}

	return 'OneSignalSDKUpdaterWorker' . pwaforwp_multisite_postfix() . '.js';
}
add_filter( 'pwaforwp_sw_name_modify', 'pwaforwp_onesignal_change_sw_name' );

function pwaforwp_add_sw_to_onesignal_sw( $content = null ) {

	// OneSignal worker must load before PWA logic in the same merged SW file.
	$onesignal = "importScripts('https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.sw.js');\n";
	$content   = $onesignal . $content;

	return $content;
}

function pwaforwp_onesignal_reassert_sw_after_sdk() {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return;
	}
	if ( ! pwaforwp_is_onesignal_pwa_compat_enabled() ) {
		return;
	}

	$sw_url = pwaforwp_get_main_service_worker_url();
	$scope  = trailingslashit( pwaforwp_home_url() );

	?>
<script id="pwaforwp-onesignal-sw-reassert">
(function() {
	if (!('serviceWorker' in navigator)) return;
	var swUrl = <?php echo wp_json_encode( esc_url_raw( $sw_url ) ); ?>;
	var scope = <?php echo wp_json_encode( esc_url_raw( $scope ) ); ?>;

	function normalizeScope(u) {
		try { return new URL(u, location.href).href.replace(/\/$/, ''); } catch (e) { return u; }
	}
	function sameUrl(a, b) {
		try { return new URL(a, location.href).href === new URL(b, location.href).href; } catch (e) { return false; }
	}
	function isOurScript(scriptUrl) {
		if (!scriptUrl) return false;
		if (sameUrl(scriptUrl, swUrl)) return true;
		try {
			var want = new URL(swUrl, location.href);
			var have = new URL(scriptUrl, location.href);
			if (want.hostname === have.hostname && want.pathname === have.pathname && want.search === have.search) {
				return true;
			}
			var swParam = want.searchParams.get('sw');
			if (swParam && have.searchParams.get('sw') === swParam) {
				return true;
			}
			if (want.pathname && have.pathname && want.pathname === have.pathname && want.search === have.search) {
				return true;
			}
		} catch (e) {}
		return false;
	}
	var targetScope = normalizeScope(scope);

	/**
	 * Unregister other workers for the same scope (e.g. OneSignal’s default file), then register the merged PWA+OneSignal worker.
	 */
	function takeOverPwaServiceWorker() {
		return navigator.serviceWorker.getRegistrations().then(function(registrations) {
			var pending = [];
			registrations.forEach(function(reg) {
				if (normalizeScope(reg.scope) !== targetScope) {
					return;
				}
				var worker = reg.installing || reg.waiting || reg.active;
				var scriptUrl = worker ? worker.scriptURL : '';
				if (isOurScript(scriptUrl)) {
					return;
				}
				pending.push(reg.unregister());
			});
			return Promise.all(pending);
		}).then(function() {
			return navigator.serviceWorker.register(swUrl, { scope: scope });
		}).catch(function() {});
	}

	if (document.readyState === 'complete') {
		takeOverPwaServiceWorker();
	} else {
		window.addEventListener('load', function onLoad() {
			window.removeEventListener('load', onLoad);
			takeOverPwaServiceWorker();
		});
	}

	// OneSignal may register after load; retry a few times.
	[50, 200, 800, 2000, 5000].forEach(function(ms) {
		setTimeout(takeOverPwaServiceWorker, ms);
	});

	// If something else becomes the controller later, try again (debounced).
	var reTimer;
	navigator.serviceWorker.addEventListener('controllerchange', function() {
		clearTimeout(reTimer);
		reTimer = setTimeout(function() {
			var c = navigator.serviceWorker.controller;
			if (c && !isOurScript(c.scriptURL)) {
				takeOverPwaServiceWorker();
			}
		}, 100);
	});
})();
</script>
	<?php
}
