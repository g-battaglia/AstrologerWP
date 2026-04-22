<?php
/**
 * Integration tests for BlocksRegistry.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Tests\Integration\Blocks;

use Astrologer\Api\Blocks\BlocksRegistry;
use WP_Block_Type_Registry;
use WP_UnitTestCase;

/**
 * @covers \Astrologer\Api\Blocks\BlocksRegistry
 */
class BlocksRegistryTest extends WP_UnitTestCase {

	/**
	 * Boot the registry once and assert every expected block is registered.
	 */
	public function test_all_plugin_blocks_are_registered(): void {
		$registry = new BlocksRegistry();
		$registry->boot();

		// Ensure the init hook fires so blocks are registered in the current test run.
		do_action( 'init' );

		$wp_registry = WP_Block_Type_Registry::get_instance();

		foreach ( BlocksRegistry::get_block_slugs() as $slug ) {
			$name = 'astrologer-api/' . $slug;

			$this->assertTrue(
				$wp_registry->is_registered( $name ),
				sprintf( 'Expected block "%s" to be registered.', $name )
			);
		}
	}

	/**
	 * The registry exposes all 22 Astrologer blocks by slug.
	 */
	public function test_block_slugs_contains_22_entries(): void {
		$slugs = BlocksRegistry::get_block_slugs();

		$this->assertCount(
			22,
			$slugs,
			'Expected exactly 22 plugin block slugs registered in BlocksRegistry.'
		);

		$this->assertContains( 'birth-form', $slugs );
		$this->assertContains( 'natal-chart', $slugs );
		$this->assertContains( 'moon-phase', $slugs );
	}
}
