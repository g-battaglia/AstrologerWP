<?php
/**
 * Astrologer API — Uninstall handler.
 *
 * Runs when the plugin is deleted via the WordPress admin.
 * Delegates all cleanup to the Uninstaller class.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

// Composer autoloader must be loaded for the Uninstaller class.
require_once __DIR__ . '/vendor/autoload.php';

if ( is_multisite() ) {
	$site_ids = get_sites( array( 'fields' => 'ids' ) );

	foreach ( $site_ids as $site_id ) {
		switch_to_blog( $site_id );
		\Astrologer\Api\Activation\Uninstaller::run();
		restore_current_blog();
	}
} else {
	\Astrologer\Api\Activation\Uninstaller::run();
}
