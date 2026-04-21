<?php
/**
 * Plugin Name: Astrologer API
 * Plugin URI:  https://github.com/astrologer-api/astrologer-api-wp
 * Description: Official WordPress plugin for the Astrologer API — birth charts, synastry, transits, moon phases and more via RapidAPI.
 * Version:     1.0.0
 * Author:      Astrologer API
 * Author URI:  https://github.com/astrologer-api
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: astrologer-api
 * Domain Path: /languages
 * Requires at least: 6.5
 * Requires PHP: 8.1
 * Tested up to: 6.7
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

defined( 'ABSPATH' ) || exit;

// Constants.
define( 'ASTROLOGER_API_VERSION', '1.0.0' );
define( 'ASTROLOGER_API_FILE', __FILE__ );
define( 'ASTROLOGER_API_DIR', __DIR__ );
define( 'ASTROLOGER_API_URL', plugin_dir_url( __FILE__ ) );

// Composer autoloader.
require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap the plugin after WordPress is loaded.
add_action(
	'plugins_loaded',
	static function (): void {
		\Astrologer\Api\Plugin::instance()->boot();
	}
);

// Activation hook.
register_activation_hook(
	__FILE__,
	static function (): void {
		\Astrologer\Api\Activation\Activator::run();
	}
);

// Deactivation hook.
register_deactivation_hook(
	__FILE__,
	static function (): void {
		\Astrologer\Api\Activation\Deactivator::run();
	}
);
