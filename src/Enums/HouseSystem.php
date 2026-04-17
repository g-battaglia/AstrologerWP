<?php
/**
 * House system enum — single-char identifiers matching the upstream API.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Enums;

/**
 * House system identifiers used by the Astrologer API.
 *
 * Backed by single-character string matching the upstream parameter values.
 */
enum HouseSystem: string {

	case Alcabitius     = 'B';
	case Campanus       = 'C';
	case EqualC         = 'D';
	case EqualAsc       = 'A';
	case EqualVehlow    = 'V';
	case EqualWholeSign = 'W';
	case EqualAries     = 'N';
	case Carter         = 'F';
	case Horizon        = 'H';
	case Koch           = 'K';
	case Krusinski      = 'U';
	case Meridian       = 'X';
	case Morinus        = 'M';
	case Placidus       = 'P';
	case PolichPage     = 'T';
	case Porphyry       = 'O';
	case PullenSD       = 'L';
	case PullenSR       = 'Q';
	case Regiomontanus  = 'R';
	case Sripati        = 'S';
	case Sunshine       = 'I';
	case SunshineAlt    = 'i';
	case APC            = 'Y';

	/**
	 * Human-readable label for the house system.
	 *
	 * @return string
	 */
	public function label(): string {
		return match ( $this ) {
			self::Alcabitius     => __( 'Alcabitius', 'astrologer-api' ),
			self::Campanus       => __( 'Campanus', 'astrologer-api' ),
			self::EqualC         => __( 'Equal (MC)', 'astrologer-api' ),
			self::EqualAsc       => __( 'Equal (Ascendant)', 'astrologer-api' ),
			self::EqualVehlow    => __( 'Equal (Vehlow)', 'astrologer-api' ),
			self::EqualWholeSign => __( 'Whole Sign', 'astrologer-api' ),
			self::EqualAries     => __( 'Equal (Aries)', 'astrologer-api' ),
			self::Carter         => __( 'Carter', 'astrologer-api' ),
			self::Horizon        => __( 'Horizon', 'astrologer-api' ),
			self::Koch           => __( 'Koch', 'astrologer-api' ),
			self::Krusinski      => __( 'Krusinski', 'astrologer-api' ),
			self::Meridian       => __( 'Meridian', 'astrologer-api' ),
			self::Morinus        => __( 'Morinus', 'astrologer-api' ),
			self::Placidus       => __( 'Placidus', 'astrologer-api' ),
			self::PolichPage     => __( 'Polich-Page', 'astrologer-api' ),
			self::Porphyry       => __( 'Porphyry', 'astrologer-api' ),
			self::PullenSD       => __( 'Pullen (SD)', 'astrologer-api' ),
			self::PullenSR       => __( 'Pullen (SR)', 'astrologer-api' ),
			self::Regiomontanus  => __( 'Regiomontanus', 'astrologer-api' ),
			self::Sripati        => __( 'Sripati', 'astrologer-api' ),
			self::Sunshine       => __( 'Sunshine', 'astrologer-api' ),
			self::SunshineAlt    => __( 'Sunshine (Alt)', 'astrologer-api' ),
			self::APC            => __( 'APC', 'astrologer-api' ),
		};
	}

	/**
	 * Return the default house system.
	 *
	 * @return self
	 */
	public static function get_default(): self {
		return self::Placidus;
	}
}
