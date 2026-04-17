<?php
/**
 * Distribution method enum — element/quality distribution calculation.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Enums;

/**
 * Distribution method for element/quality calculations.
 */
enum DistributionMethod: string {

	case Weighted  = 'weighted';
	case PureCount = 'pure_count';

	/**
	 * Human-readable label.
	 *
	 * @return string
	 */
	public function label(): string {
		return match ( $this ) {
			self::Weighted  => __( 'Weighted', 'astrologer-api' ),
			self::PureCount => __( 'Pure Count', 'astrologer-api' ),
		};
	}

	/**
	 * Return the default distribution method.
	 *
	 * @return self
	 */
	public static function get_default(): self {
		return self::Weighted;
	}
}
