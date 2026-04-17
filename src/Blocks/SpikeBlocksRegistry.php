<?php
/**
 * Registers the spike block for Interactivity API validation.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Blocks;

use Astrologer\Api\Support\Contracts\Bootable;

/**
 * Temporary Bootable that registers the spike-birth-form block.
 * Will be moved to tests/spikes/ after F0.5 validation.
 */
final class SpikeBlocksRegistry implements Bootable {

	/**
	 * Register the spike block on init.
	 */
	public function boot(): void {
		add_action( 'init', array( $this, 'register_blocks' ) );
	}

	/**
	 * Register the spike-birth-form block via block.json metadata.
	 */
	public function register_blocks(): void {
		register_block_type(
			ASTROLOGER_API_DIR . '/blocks/spike-birth-form'
		);
	}
}
