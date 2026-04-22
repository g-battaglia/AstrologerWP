<?php
/**
 * BlockCategory — registers the "astrology" block category.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Blocks;

use Astrologer\Api\Support\Contracts\Bootable;

/**
 * Adds the "astrology" category to the block editor inserter.
 */
final class BlockCategory implements Bootable {

	/**
	 * Category slug.
	 *
	 * @var string
	 */
	public const SLUG = 'astrology';

	/**
	 * Register the category filter.
	 */
	public function boot(): void {
		add_filter( 'block_categories_all', array( $this, 'add_category' ), 10, 1 );
	}

	/**
	 * Add the astrology category at the beginning of the list.
	 *
	 * @param array<int,array<string,mixed>> $categories Existing block categories.
	 * @return array<int,array<string,mixed>>
	 */
	public function add_category( array $categories ): array {
		array_unshift(
			$categories,
			array(
				'slug'  => self::SLUG,
				'title' => __( 'Astrology', 'astrologer-api' ),
				'icon'  => 'star-filled',
			)
		);

		return $categories;
	}
}
