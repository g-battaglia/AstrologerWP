<?php
/**
 * ScriptTranslations — wires wp_set_script_translations() for every enqueued
 * plugin script so `@wordpress/i18n` strings are translated client-side.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Support\i18n;

use Astrologer\Api\Blocks\BlocksRegistry;
use Astrologer\Api\Support\Contracts\Bootable;

/**
 * Registers `wp_set_script_translations()` for every script handle shipped
 * by the plugin. Hooks into both the admin and frontend enqueue events so
 * JSON translation files are loaded lazily once a handle is enqueued.
 */
final class ScriptTranslations implements Bootable {

	/**
	 * Plugin text domain — kept here to avoid drift with the plugin header.
	 */
	private const TEXT_DOMAIN = 'astrologer-api';

	/**
	 * Admin-side script handles registered by the React admin apps.
	 *
	 * @var list<string>
	 */
	private const ADMIN_HANDLES = array(
		'astrologer-settings',
		'astrologer-setup-wizard',
		'astrologer-docs',
	);

	/**
	 * Register late-running hooks that call wp_set_script_translations().
	 *
	 * Priority 20 keeps us after the default priority-10 enqueues so every
	 * handle is guaranteed to be registered by the time we wire translations.
	 */
	public function boot(): void {
		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_translations' ), 20 );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_frontend_translations' ), 20 );
		add_action( 'enqueue_block_editor_assets', array( $this, 'register_block_editor_translations' ), 20 );
	}

	/**
	 * Wire translations for every admin React app handle.
	 */
	public function register_admin_translations(): void {
		$path = $this->languages_path();

		foreach ( self::ADMIN_HANDLES as $handle ) {
			if ( ! wp_script_is( $handle, 'registered' ) ) {
				continue;
			}

			wp_set_script_translations( $handle, self::TEXT_DOMAIN, $path );
		}
	}

	/**
	 * Wire translations for every block view script that WP registers
	 * under the conventional `<namespace>-<slug>-view-script` handle.
	 */
	public function register_frontend_translations(): void {
		$path = $this->languages_path();

		foreach ( $this->block_handles() as $handle ) {
			if ( ! wp_script_is( $handle, 'registered' ) ) {
				continue;
			}

			wp_set_script_translations( $handle, self::TEXT_DOMAIN, $path );
		}
	}

	/**
	 * Wire translations for block edit scripts in the block editor.
	 */
	public function register_block_editor_translations(): void {
		$path = $this->languages_path();

		foreach ( $this->block_handles( 'editor' ) as $handle ) {
			if ( ! wp_script_is( $handle, 'registered' ) ) {
				continue;
			}

			wp_set_script_translations( $handle, self::TEXT_DOMAIN, $path );
		}
	}

	/**
	 * Build the conventional WordPress handles for every block script.
	 *
	 * @param 'view'|'editor' $context Which script handle variant to return.
	 * @return list<string>
	 */
	private function block_handles( string $context = 'view' ): array {
		$handles = array();

		foreach ( BlocksRegistry::get_block_slugs() as $slug ) {
			$base = 'astrologer-api-' . $slug;

			$handles[] = 'editor' === $context
				? $base . '-editor-script'
				: $base . '-view-script';
		}

		return $handles;
	}

	/**
	 * Absolute path to the `languages/` directory, resolved against the
	 * plugin root so the path travels with the plugin ZIP.
	 *
	 * @return string
	 */
	private function languages_path(): string {
		return defined( 'ASTROLOGER_API_DIR' )
			? ASTROLOGER_API_DIR . '/languages'
			: dirname( __DIR__, 3 ) . '/languages';
	}
}
