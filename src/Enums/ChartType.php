<?php
/**
 * Chart type enum — identifies the kind of astrological chart.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Enums;

/**
 * Chart type identifiers used throughout the plugin.
 */
enum ChartType: string {

	case Natal         = 'natal';
	case Synastry      = 'synastry';
	case Transit       = 'transit';
	case Composite     = 'composite';
	case SolarReturn   = 'solar_return';
	case LunarReturn   = 'lunar_return';
	case Now           = 'now';
	case MoonPhase     = 'moon_phase';
	case Compatibility = 'compatibility';

	/**
	 * Human-readable label.
	 *
	 * @return string
	 */
	public function label(): string {
		return match ( $this ) {
			self::Natal        => __( 'Natal Chart', 'astrologer-api' ),
			self::Synastry     => __( 'Synastry', 'astrologer-api' ),
			self::Transit      => __( 'Transit', 'astrologer-api' ),
			self::Composite    => __( 'Composite', 'astrologer-api' ),
			self::SolarReturn  => __( 'Solar Return', 'astrologer-api' ),
			self::LunarReturn  => __( 'Lunar Return', 'astrologer-api' ),
			self::Now          => __( 'Current Moment', 'astrologer-api' ),
			self::MoonPhase    => __( 'Moon Phase', 'astrologer-api' ),
			self::Compatibility => __( 'Compatibility', 'astrologer-api' ),
		};
	}

	/**
	 * Whether this chart type requires two subjects.
	 *
	 * @return bool
	 */
	public function is_dual_subject(): bool {
		return match ( $this ) {
			self::Synastry,
			self::Composite,
			self::Compatibility => true,
			default             => false,
		};
	}

	/**
	 * Whether this chart type involves a transit moment.
	 *
	 * @return bool
	 */
	public function is_transit_based(): bool {
		return match ( $this ) {
			self::Transit,
			self::SolarReturn,
			self::LunarReturn,
			self::Now => true,
			default   => false,
		};
	}
}
