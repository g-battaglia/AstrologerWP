<?php
/**
 * Uninstall handler for the Astrologer API Playground plugin.
 *
 * Fired when the plugin is deleted via the WordPress admin.
 * Removes all options and transients created by the plugin.
 *
 * @package Astrologer_API_Playground
 * @since   1.0.0
 */

// Abort if not called by WordPress.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/*
|--------------------------------------------------------------------------
| Remove plugin options
|--------------------------------------------------------------------------
*/

delete_option( 'astrologer_api_settings' );

/*
|--------------------------------------------------------------------------
| Remove transients
|--------------------------------------------------------------------------
| The plugin may store transient caches for API responses or rate-limiting.
| We use a LIKE query to catch any prefixed transients.
*/

global $wpdb;

$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
		'_transient_astrologer_api_%',
		'_transient_timeout_astrologer_api_%'
	)
);

/*
|--------------------------------------------------------------------------
| Multisite support
|--------------------------------------------------------------------------
| If running on a multisite network and the plugin was network-activated,
| clean up options on every site.
*/

if ( is_multisite() ) {
	$site_ids = get_sites( array( 'fields' => 'ids' ) );

	foreach ( $site_ids as $site_id ) {
		switch_to_blog( $site_id );

		delete_option( 'astrologer_api_settings' );

		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
				'_transient_astrologer_api_%',
				'_transient_timeout_astrologer_api_%'
			)
		);

		restore_current_blog();
	}
}
