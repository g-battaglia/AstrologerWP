<?php
/**
 * PHPUnit integration bootstrap file.
 *
 * Boots a WordPress test environment via wp-phpunit. Used for integration
 * tests under tests/Integration/ that exercise WP hooks, REST, and DB.
 *
 * Respects the WP_TESTS_DIR environment variable; otherwise defaults to
 * the vendored wp-phpunit path.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = __DIR__ . '/../vendor/wp-phpunit/wp-phpunit';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	fwrite(
		STDERR,
		"Could not find wp-phpunit at {$_tests_dir}. Run `composer install`.\n"
	);
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _astrologer_manually_load_plugin(): void {
	require __DIR__ . '/../astrologer-api.php';
}
tests_add_filter( 'muplugins_loaded', '_astrologer_manually_load_plugin' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';

// Composer autoloader for test utilities.
require_once __DIR__ . '/../vendor/autoload.php';
