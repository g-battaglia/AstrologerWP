<?php
/**
 * BlockBindingsSource — registers a Block Bindings source for chart data.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Blocks;

use Astrologer\Api\Support\Contracts\Bootable;
use WP_Block;

/**
 * Registers the `astrologer-api/chart-data` binding source (WP 6.5+).
 *
 * Usage from blocks:
 *
 * ```json
 * { "metadata": { "bindings": {
 *     "content": { "source": "astrologer-api/chart-data",
 *                  "args": { "field": "positions.sun.sign" } }
 * } } }
 * ```
 *
 * The callback reads the current post's `_astrologer_chart_data` meta (assumed
 * to be an associative array) and walks a dot-notation path to extract the
 * requested value. Missing keys fall back to an empty string.
 */
final class BlockBindingsSource implements Bootable {

	/**
	 * Source name registered with `register_block_bindings_source()`.
	 *
	 * @var string
	 */
	public const SOURCE = 'astrologer-api/chart-data';

	/**
	 * Post meta key storing the chart data array.
	 *
	 * @var string
	 */
	public const META_KEY = '_astrologer_chart_data';

	/**
	 * Register the init hook.
	 */
	public function boot(): void {
		add_action( 'init', array( $this, 'register' ), 20 );
	}

	/**
	 * Register the block bindings source when supported.
	 */
	public function register(): void {
		if ( ! function_exists( 'register_block_bindings_source' ) ) {
			return;
		}

		register_block_bindings_source(
			self::SOURCE,
			array(
				'label'              => __( 'Astrologer Chart Data', 'astrologer-api' ),
				'get_value_callback' => array( self::class, 'resolve_value' ),
				'uses_context'       => array( 'postId' ),
			)
		);
	}

	/**
	 * Resolve a binding value from the current post's chart data.
	 *
	 * @param array<string,mixed>  $source_args Args from the binding definition.
	 * @param WP_Block|mixed       $block       Block instance (unused, kept for API signature).
	 * @param string               $_attr_name  Attribute name being bound (unused).
	 * @return string
	 */
	public static function resolve_value( array $source_args, mixed $block = null, string $_attr_name = '' ): string {
		unset( $block, $_attr_name );

		$field = isset( $source_args['field'] ) && is_string( $source_args['field'] )
			? $source_args['field']
			: '';

		if ( '' === $field ) {
			return '';
		}

		$post_id = isset( $source_args['postId'] ) && is_numeric( $source_args['postId'] )
			? (int) $source_args['postId']
			: (int) get_the_ID();

		if ( $post_id <= 0 ) {
			return '';
		}

		$data = get_post_meta( $post_id, self::META_KEY, true );

		if ( ! is_array( $data ) ) {
			return '';
		}

		$value = self::walk_path( $data, $field );

		if ( null === $value ) {
			return '';
		}

		if ( is_scalar( $value ) ) {
			return (string) $value;
		}

		// Arrays / objects: render as JSON for downstream inspection.
		$encoded = wp_json_encode( $value );
		return is_string( $encoded ) ? $encoded : '';
	}

	/**
	 * Walk a dot-notation path on an associative array.
	 *
	 * @param array<array-key,mixed> $data Source data.
	 * @param string                 $path Dot-separated path (e.g. `positions.sun.sign`).
	 * @return mixed Value or null if any segment is missing.
	 */
	private static function walk_path( array $data, string $path ): mixed {
		$segments = explode( '.', $path );
		$current  = $data;

		foreach ( $segments as $segment ) {
			if ( '' === $segment ) {
				return null;
			}

			if ( is_array( $current ) && array_key_exists( $segment, $current ) ) {
				$current = $current[ $segment ];
				continue;
			}

			return null;
		}

		return $current;
	}
}
