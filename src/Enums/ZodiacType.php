<?php
/**
 * Zodiac type enum — Tropical or Sidereal.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Enums;

/**
 * Zodiac type used by the Astrologer API.
 */
enum ZodiacType: string {

	case Tropical = 'Tropical';
	case Sidereal = 'Sidereal';

	/**
	 * Human-readable label.
	 *
	 * @return string
	 */
	public function label(): string {
		return match ( $this ) {
			self::Tropical => __( 'Tropical', 'astrologer-api' ),
			self::Sidereal => __( 'Sidereal', 'astrologer-api' ),
		};
	}

	/**
	 * Return the default zodiac type.
	 *
	 * @return self
	 */
	public static function get_default(): self {
		return self::Tropical;
	}
}
