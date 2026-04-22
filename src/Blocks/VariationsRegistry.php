<?php
/**
 * VariationsRegistry — enqueues the JS bundle that registers block variations.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Blocks;

use Astrologer\Api\Support\Contracts\Bootable;

/**
 * Enqueues `build/admin-variations.js` on the block editor screen.
 *
 * Variations are registered client-side via `wp.blocks.registerBlockVariation`.
 * Each of the 7 form blocks gets 4 school variations (modern_western,
 * traditional, vedic, uranian) that pre-set the `preset` attribute.
 */
final class VariationsRegistry implements Bootable {

	/**
	 * Handle of the enqueued script.
	 *
	 * @var string
	 */
	public const HANDLE = 'astrologer-api-block-variations';

	/**
	 * Register the enqueue hook for the editor.
	 */
	public function boot(): void {
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue' ) );
	}

	/**
	 * Enqueue the variations bundle in the block editor.
	 */
	public function enqueue(): void {
		$asset_file = ASTROLOGER_API_DIR . '/build/admin-variations.asset.php';
		$script     = ASTROLOGER_API_DIR . '/build/admin-variations.js';

		if ( ! file_exists( $script ) ) {
			return;
		}

		$asset = array(
			'dependencies' => array( 'wp-blocks', 'wp-i18n' ),
			'version'      => defined( 'ASTROLOGER_API_VERSION' ) ? ASTROLOGER_API_VERSION : '1.0.0',
		);

		if ( file_exists( $asset_file ) ) {
			/** @var mixed $loaded */
			$loaded = include $asset_file;
			if ( is_array( $loaded ) ) {
				$asset = array_merge( $asset, $loaded );
			}
		}

		wp_enqueue_script(
			self::HANDLE,
			ASTROLOGER_API_URL . 'build/admin-variations.js',
			is_array( $asset['dependencies'] ?? null ) ? $asset['dependencies'] : array( 'wp-blocks', 'wp-i18n' ),
			is_string( $asset['version'] ?? null ) ? $asset['version'] : '1.0.0',
			true
		);

		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( self::HANDLE, 'astrologer-api' );
		}
	}
}
