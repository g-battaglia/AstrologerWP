<?php
/**
 * Frontend asset enqueuer — localises globals for Interactivity stores.
 *
 * Each AstrologerWP block renders its own `viewScriptModule` (registered via
 * `block.json`) which WordPress enqueues automatically when the block is
 * present on the page. This class is responsible for injecting the small
 * `window.astrologer*` globals (REST URL + nonce + a few config values)
 * those stores need, but only when the relevant block is actually rendered.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Frontend;

use Astrologer\Api\Support\Contracts\Bootable;

/**
 * Conditionally localises window globals for every block that ships an
 * Interactivity store.
 */
final class AssetEnqueuer implements Bootable {

	/**
	 * Map of block slug => `window.*` global name used by the store.
	 *
	 * @var array<string, string>
	 */
	private const BLOCK_GLOBALS = array(
		'birth-form'         => 'astrologerBirthForm',
		'synastry-form'      => 'astrologerSynastryForm',
		'transit-form'       => 'astrologerTransitForm',
		'composite-form'     => 'astrologerCompositeForm',
		'solar-return-form'  => 'astrologerSolarReturnForm',
		'lunar-return-form'  => 'astrologerLunarReturnForm',
		'now-form'           => 'astrologerNowForm',
		'compatibility-form' => 'astrologerCompatibilityForm',
		'moon-phase'         => 'astrologerMoonPhase',
	);

	/**
	 * Register the wp_enqueue_scripts hook.
	 */
	public function boot(): void {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Inject a window global for every AstrologerWP block present in the
	 * current post. No-op in the admin.
	 */
	public function enqueue_assets(): void {
		if ( is_admin() ) {
			return;
		}

		$post = get_post();
		if ( null === $post ) {
			return;
		}

		$rest_url = rest_url( 'astrologer/v1/' );
		$nonce    = wp_create_nonce( 'wp_rest' );

		foreach ( self::BLOCK_GLOBALS as $slug => $global_name ) {
			if ( ! has_block( 'astrologer-api/' . $slug, $post ) ) {
				continue;
			}

			$data = array(
				'restUrl' => $rest_url,
				'nonce'   => $nonce,
			);

			if ( 'moon-phase' === $slug ) {
				$data['refreshInterval'] = 0;
			}

			$this->inline_global( $global_name, $data );
		}
	}

	/**
	 * Print a `<script>` tag assigning the supplied data to the given window
	 * global. Data is JSON-encoded server-side so no further escaping is
	 * needed on the client.
	 *
	 * @param string               $global_name Name of the `window.*` property.
	 * @param array<string, mixed> $data        Data to expose.
	 */
	private function inline_global( string $global_name, array $data ): void {
		$json = wp_json_encode( $data );
		if ( false === $json ) {
			return;
		}

		$script = sprintf(
			'window.%s = %s;',
			$global_name,
			$json
		);

		$handle = 'astrologer-' . $global_name;
		wp_register_script( $handle, '', array(), ASTROLOGER_API_VERSION, false );
		wp_enqueue_script( $handle );
		wp_add_inline_script( $handle, $script, 'before' );
	}
}
