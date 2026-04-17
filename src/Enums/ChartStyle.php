<?php
/**
 * Chart style enum — classic or modern rendering.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Enums;

/**
 * Chart rendering style.
 */
enum ChartStyle: string {

	case Classic = 'classic';
	case Modern  = 'modern';

	/**
	 * Human-readable label.
	 *
	 * @return string
	 */
	public function label(): string {
		return match ( $this ) {
			self::Classic => __( 'Classic', 'astrologer-api' ),
			self::Modern  => __( 'Modern', 'astrologer-api' ),
		};
	}

	/**
	 * Return the default chart style.
	 *
	 * @return self
	 */
	public static function get_default(): self {
		return self::Classic;
	}
}
