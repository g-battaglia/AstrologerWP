<?php
/**
 * Admin menu placeholder. Will be expanded in F0.8 / F4.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Admin;

use Astrologer\Api\Support\Contracts\Bootable;

/**
 * Registers the admin menu page. Placeholder for now.
 */
final class AdminMenu implements Bootable {

	/**
	 * Register the admin_menu hook.
	 */
	public function boot(): void {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
	}

	/**
	 * Add the top-level "Astrologer" menu and a Settings subpage.
	 */
	public function register_menu(): void {
		add_menu_page(
			__( 'Astrologer API', 'astrologer-api' ),
			__( 'Astrologer', 'astrologer-api' ),
			'manage_options',
			'astrologer-api',
			array( $this, 'render_settings_page' ),
			'dashicons-star-filled',
			56
		);

		add_submenu_page(
			'astrologer-api',
			__( 'Settings', 'astrologer-api' ),
			__( 'Settings', 'astrologer-api' ),
			'manage_options',
			'astrologer-api',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Render the placeholder settings page.
	 */
	public function render_settings_page(): void {
		printf(
			'<div class="wrap"><h1>%s</h1><p>%s</p></div>',
			esc_html__( 'Astrologer API', 'astrologer-api' ),
			esc_html__( 'v1.0 bootstrap OK — more coming in F4.', 'astrologer-api' )
		);
	}
}
