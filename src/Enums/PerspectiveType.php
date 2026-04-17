<?php
/**
 * Perspective type enum — geocentric, heliocentric, etc.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Enums;

/**
 * Astronomical perspective types supported by the Astrologer API.
 */
enum PerspectiveType: string {

	case ApparentGeocentric = 'Apparent Geocentric';
	case TrueGeocentric     = 'True Geocentric';
	case Heliocentric       = 'Heliocentric';
	case Topocentric        = 'Topocentric';
	case Selenocentric      = 'Selenocentric';
	case Mercurycentric     = 'Mercurycentric';
	case Venuscentric       = 'Venuscentric';
	case Marscentric        = 'Marscentric';
	case Jupitercentric     = 'Jupitercentric';
	case Saturncentric      = 'Saturncentric';
	case Barycentric        = 'Barycentric';

	/**
	 * Human-readable label.
	 *
	 * @return string
	 */
	public function label(): string {
		return match ( $this ) {
			self::ApparentGeocentric => __( 'Apparent Geocentric', 'astrologer-api' ),
			self::TrueGeocentric     => __( 'True Geocentric', 'astrologer-api' ),
			self::Heliocentric       => __( 'Heliocentric', 'astrologer-api' ),
			self::Topocentric        => __( 'Topocentric', 'astrologer-api' ),
			self::Selenocentric      => __( 'Selenocentric', 'astrologer-api' ),
			self::Mercurycentric     => __( 'Mercurycentric', 'astrologer-api' ),
			self::Venuscentric       => __( 'Venuscentric', 'astrologer-api' ),
			self::Marscentric        => __( 'Marscentric', 'astrologer-api' ),
			self::Jupitercentric     => __( 'Jupitercentric', 'astrologer-api' ),
			self::Saturncentric      => __( 'Saturncentric', 'astrologer-api' ),
			self::Barycentric        => __( 'Barycentric', 'astrologer-api' ),
		};
	}

	/**
	 * Return the default perspective type.
	 *
	 * @return self
	 */
	public static function get_default(): self {
		return self::ApparentGeocentric;
	}
}
