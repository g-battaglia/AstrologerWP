<?php
/**
 * BlockPatternsRegistry — registers Astrologer block patterns + category.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Blocks;

use Astrologer\Api\Support\Contracts\Bootable;

/**
 * Registers the "astrology" pattern category and 6 starter patterns on init.
 *
 * Each pattern file in `patterns/` returns a markup string. The registry
 * `require`s the file and forwards the content to `register_block_pattern()`.
 */
final class BlockPatternsRegistry implements Bootable {

	/**
	 * Pattern category slug.
	 *
	 * @var string
	 */
	public const CATEGORY = 'astrology';

	/**
	 * Map of pattern slug => metadata.
	 *
	 * The `file` key points to a PHP file in `patterns/` that returns the
	 * block markup string. Title / description / keywords are translated
	 * at registration time.
	 *
	 * @var array<string,array{file:string,title:string,description:string,keywords:list<string>}>
	 */
	private const PATTERNS = array(
		'astrologer-api/simple-natal'        => array(
			'file'        => 'simple-natal.php',
			'title'       => 'Simple Natal Chart',
			'description' => 'Birth form with a natal chart plus positions and aspects tables.',
			'keywords'    => array( 'natal', 'birth', 'chart' ),
		),
		'astrologer-api/synastry-compat'     => array(
			'file'        => 'synastry-compat.php',
			'title'       => 'Synastry Compatibility',
			'description' => 'Compatibility form, synastry chart, and relationship scores.',
			'keywords'    => array( 'synastry', 'compatibility', 'relationship' ),
		),
		'astrologer-api/transit-today'       => array(
			'file'        => 'transit-today.php',
			'title'       => 'Transit Today',
			'description' => 'Transit form + chart + aspects table for the current day.',
			'keywords'    => array( 'transit', 'today', 'aspects' ),
		),
		'astrologer-api/solar-return-annual' => array(
			'file'        => 'solar-return-annual.php',
			'title'       => 'Solar Return (Annual)',
			'description' => 'Solar return form with chart, elements and modalities distribution.',
			'keywords'    => array( 'solar return', 'annual', 'revolution' ),
		),
		'astrologer-api/moon-phase-widget'   => array(
			'file'        => 'moon-phase-widget.php',
			'title'       => 'Moon Phase Widget',
			'description' => 'Compact current moon phase display for sidebars or widget areas.',
			'keywords'    => array( 'moon', 'phase', 'widget' ),
		),
		'astrologer-api/daily-dashboard'     => array(
			'file'        => 'daily-dashboard.php',
			'title'       => 'Daily Dashboard',
			'description' => 'Combined daily snapshot: moon phase, current chart, and positions.',
			'keywords'    => array( 'dashboard', 'daily', 'snapshot' ),
		),
	);

	/**
	 * Register the init hook.
	 */
	public function boot(): void {
		add_action( 'init', array( $this, 'register' ), 11 );
	}

	/**
	 * Register the astrology pattern category and every pattern.
	 */
	public function register(): void {
		if ( function_exists( 'register_block_pattern_category' ) ) {
			register_block_pattern_category(
				self::CATEGORY,
				array(
					'label' => __( 'Astrology', 'astrologer-api' ),
				)
			);
		}

		if ( ! function_exists( 'register_block_pattern' ) ) {
			return;
		}

		$patterns_dir = ASTROLOGER_API_DIR . '/patterns';

		foreach ( self::PATTERNS as $slug => $meta ) {
			$path = $patterns_dir . '/' . $meta['file'];

			if ( ! is_readable( $path ) ) {
				continue;
			}

			/** @var mixed $content */
			$content = require $path;

			if ( ! is_string( $content ) || '' === $content ) {
				continue;
			}

			register_block_pattern(
				$slug,
				array(
					'title'       => __( $meta['title'], 'astrologer-api' ), // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
					'description' => __( $meta['description'], 'astrologer-api' ), // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
					'categories'  => array( self::CATEGORY ),
					'keywords'    => $meta['keywords'],
					'content'     => $content,
				)
			);
		}
	}

	/**
	 * Get the registered pattern slugs.
	 *
	 * @return list<string>
	 */
	public static function get_pattern_slugs(): array {
		return array_keys( self::PATTERNS );
	}
}
