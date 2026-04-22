<?php
/**
 * Setup Wizard page — first-visit redirect and asset enqueue.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Admin;

use Astrologer\Api\Support\Contracts\Bootable;

/**
 * Handles the first-visit redirect to the setup wizard and enqueues its React app.
 */
final class SetupWizardPage implements Bootable {

	/**
	 * Option name that stores whether the wizard has been completed.
	 */
	public const OPTION_COMPLETED = 'astrologer_setup_completed';

	/**
	 * Register hooks.
	 */
	public function boot(): void {
		add_action( 'admin_init', array( $this, 'maybe_redirect' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Redirect to the wizard on first activation.
	 */
	public function maybe_redirect(): void {
		if ( get_option( self::OPTION_COMPLETED ) ) {
			return;
		}

		if ( ! current_user_can( 'astrologer_manage_settings' ) ) {
			return;
		}

		if ( ! get_transient( 'astrologer_activation_redirect' ) ) {
			return;
		}

		delete_transient( 'astrologer_activation_redirect' );

		wp_safe_redirect( admin_url( 'admin.php?page=astrologer-setup' ) );
		exit;
	}

	/**
	 * Enqueue the wizard JS and CSS on the wizard page.
	 *
	 * @param string $hook_suffix The current admin page hook suffix.
	 */
	public function enqueue_assets( string $hook_suffix ): void {
		if ( 'admin_page_astrologer-setup' !== $hook_suffix ) {
			return;
		}

		$asset_file = ASTROLOGER_API_DIR . '/build/admin-setup-wizard.asset.php';
		$asset      = file_exists( $asset_file )
			? require $asset_file
			: array(
				'dependencies' => array(),
				'version'      => ASTROLOGER_API_VERSION,
			);

		$dependencies   = $asset['dependencies'];
		$dependencies[] = 'wp-components';

		wp_enqueue_script(
			'astrologer-setup-wizard',
			ASTROLOGER_API_URL . 'build/admin-setup-wizard.js',
			$dependencies,
			$asset['version'],
			true
		);

		wp_enqueue_style(
			'astrologer-setup-wizard',
			ASTROLOGER_API_URL . 'build/admin-setup-wizard.css',
			array( 'wp-components' ),
			$asset['version']
		);

		wp_localize_script(
			'astrologer-setup-wizard',
			'astrologerSettings',
			array(
				'restUrl'  => rest_url( 'astrologer/v1/' ),
				'nonce'    => wp_create_nonce( 'wp_rest' ),
				'adminUrl' => admin_url(),
			)
		);
	}
}
