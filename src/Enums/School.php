<?php
/**
 * Astrological school enum — preset schools with different defaults.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Enums;

/**
 * Astrological school presets.
 *
 * Each school configures default house system, zodiac type, active points, etc.
 */
enum School: string {

	case ModernWestern = 'modern_western';
	case Traditional   = 'traditional';
	case Vedic         = 'vedic';
	case Uranian       = 'uranian';

	/**
	 * Human-readable label.
	 *
	 * @return string
	 */
	public function label(): string {
		return match ( $this ) {
			self::ModernWestern => __( 'Modern Western', 'astrologer-api' ),
			self::Traditional   => __( 'Traditional / Hellenistic', 'astrologer-api' ),
			self::Vedic         => __( 'Vedic / Jyotish', 'astrologer-api' ),
			self::Uranian       => __( 'Uranian / Hamburg', 'astrologer-api' ),
		};
	}

	/**
	 * Return the default school.
	 *
	 * @return self
	 */
	public static function get_default(): self {
		return self::ModernWestern;
	}
}
