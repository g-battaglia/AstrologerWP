<?php
/**
 * Aspect type enum — major, minor, and declination aspects.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Enums;

/**
 * Enum representing all aspect types used by the Astrologer API.
 *
 * Includes the 11 longitudinal aspects plus parallel and contra-parallel
 * (declination aspects).
 */
enum AspectType: string {

	case Conjunction     = 'Conjunction';
	case Semi_Sextile    = 'Semi_Sextile';
	case Semi_Square     = 'Semi_Square';
	case Sextile         = 'Sextile';
	case Quintile        = 'Quintile';
	case Square          = 'Square';
	case Trine           = 'Trine';
	case Sesquiquadrate  = 'Sesquiquadrate';
	case Biquintile      = 'Biquintile';
	case Quincunx        = 'Quincunx';
	case Opposition      = 'Opposition';
	case Parallel        = 'Parallel';
	case Contra_Parallel = 'Contra_Parallel';

	/**
	 * Human-readable label for the aspect type.
	 *
	 * @return string
	 */
	public function label(): string {
		return match ( $this ) {
			self::Conjunction      => __( 'Conjunction', 'astrologer-api' ),
			self::Semi_Sextile     => __( 'Semi-Sextile', 'astrologer-api' ),
			self::Semi_Square      => __( 'Semi-Square', 'astrologer-api' ),
			self::Sextile          => __( 'Sextile', 'astrologer-api' ),
			self::Quintile         => __( 'Quintile', 'astrologer-api' ),
			self::Square           => __( 'Square', 'astrologer-api' ),
			self::Trine            => __( 'Trine', 'astrologer-api' ),
			self::Sesquiquadrate   => __( 'Sesquiquadrate', 'astrologer-api' ),
			self::Biquintile       => __( 'Biquintile', 'astrologer-api' ),
			self::Quincunx         => __( 'Quincunx', 'astrologer-api' ),
			self::Opposition       => __( 'Opposition', 'astrologer-api' ),
			self::Parallel         => __( 'Parallel', 'astrologer-api' ),
			self::Contra_Parallel  => __( 'Contra-Parallel', 'astrologer-api' ),
		};
	}

	/**
	 * Return the default orb in degrees for this aspect type.
	 *
	 * Major aspects (conjunction, opposition, trine, square): 8 degrees.
	 * Sextile: 6 degrees.
	 * Minor aspects (semi-sextile, semi-square, sesquiquadrate, quincunx): 2 degrees.
	 * Quintile family (quintile, biquintile): 1 degree.
	 * Declination aspects (parallel, contra-parallel): 2 degrees.
	 *
	 * @return float
	 */
	public function default_orb(): float {
		return match ( $this ) {
			self::Conjunction, self::Opposition, self::Trine, self::Square => 8.0,
			self::Sextile                                                  => 6.0,
			self::Semi_Sextile, self::Semi_Square,
			self::Sesquiquadrate, self::Quincunx                           => 2.0,
			self::Quintile, self::Biquintile                               => 1.0,
			self::Parallel, self::Contra_Parallel                          => 2.0,
		};
	}

	/**
	 * Return the default set of active aspects for a standard chart.
	 *
	 * @return list<self>
	 */
	public static function get_defaults(): array {
		return array(
			self::Conjunction,
			self::Semi_Sextile,
			self::Semi_Square,
			self::Sextile,
			self::Quintile,
			self::Square,
			self::Trine,
			self::Sesquiquadrate,
			self::Biquintile,
			self::Quincunx,
			self::Opposition,
		);
	}

	/**
	 * Whether this is a major (Ptolemaic) aspect.
	 *
	 * @return bool
	 */
	public function is_major(): bool {
		return match ( $this ) {
			self::Conjunction, self::Opposition, self::Trine,
			self::Square, self::Sextile => true,
			default => false,
		};
	}

	/**
	 * Whether this is a declination-based aspect.
	 *
	 * @return bool
	 */
	public function is_declination(): bool {
		return match ( $this ) {
			self::Parallel, self::Contra_Parallel => true,
			default => false,
		};
	}
}
