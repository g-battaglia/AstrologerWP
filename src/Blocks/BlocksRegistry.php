<?php
/**
 * BlocksRegistry — registers all Astrologer blocks via block.json metadata.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Blocks;

use Astrologer\Api\Support\Contracts\Bootable;

/**
 * Scans the blocks/ directory and registers each block that has a block.json.
 */
final class BlocksRegistry implements Bootable {

	/**
	 * All block slugs that should be registered.
	 *
	 * @var list<string>
	 */
	private const BLOCKS = array(
		'birth-form',
		'synastry-form',
		'transit-form',
		'composite-form',
		'solar-return-form',
		'lunar-return-form',
		'now-form',
		'compatibility-form',
		'natal-chart',
		'synastry-chart',
		'transit-chart',
		'composite-chart',
		'solar-return-chart',
		'lunar-return-chart',
		'now-chart',
		'moon-phase',
		'positions-table',
		'aspects-table',
		'elements-chart',
		'modalities-chart',
		'compatibility-score',
		'relationship-score',
	);

	/**
	 * Register the init hook.
	 */
	public function boot(): void {
		add_action( 'init', array( $this, 'register_blocks' ) );
	}

	/**
	 * Register all blocks that have a block.json file.
	 */
	public function register_blocks(): void {
		$blocks_dir = ASTROLOGER_API_DIR . '/blocks';

		foreach ( self::BLOCKS as $slug ) {
			$block_dir = $blocks_dir . '/' . $slug;

			if ( ! file_exists( $block_dir . '/block.json' ) ) {
				continue;
			}

			register_block_type( $block_dir );
		}
	}

	/**
	 * Get the list of block slugs.
	 *
	 * @return list<string>
	 */
	public static function get_block_slugs(): array {
		return self::BLOCKS;
	}
}
