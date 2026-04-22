<?php
/**
 * Settings page — enqueues the React settings app.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Admin;

use Astrologer\Api\Support\Contracts\Bootable;

/**
 * Enqueues the React-powered settings application on the Astrologer top-level page.
 */
final class SettingsPage implements Bootable {

	/**
	 * Register the admin_enqueue_scripts hook.
	 */
	public function boot(): void {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Enqueue the settings JS and CSS on the Astrologer settings page.
	 *
	 * @param string $hook_suffix The current admin page hook suffix.
	 */
	public function enqueue_assets( string $hook_suffix ): void {
		if ( 'toplevel_page_astrologer-api' !== $hook_suffix ) {
			return;
		}

		$asset_file = ASTROLOGER_API_DIR . '/build/admin-settings.asset.php';
		$asset      = file_exists( $asset_file )
			? require $asset_file
			: array(
				'dependencies' => array(),
				'version'      => ASTROLOGER_API_VERSION,
			);

		$dependencies   = $asset['dependencies'];
		$dependencies[] = 'wp-components';

		wp_enqueue_script(
			'astrologer-settings',
			ASTROLOGER_API_URL . 'build/admin-settings.js',
			$dependencies,
			$asset['version'],
			true
		);

		wp_enqueue_style(
			'astrologer-settings',
			ASTROLOGER_API_URL . 'build/admin-settings.css',
			array( 'wp-components' ),
			$asset['version']
		);

		wp_localize_script(
			'astrologer-settings',
			'astrologerSettings',
			array(
				'restUrl'  => rest_url( 'astrologer/v1/' ),
				'nonce'    => wp_create_nonce( 'wp_rest' ),
				'adminUrl' => admin_url(),
			)
		);
	}
}
