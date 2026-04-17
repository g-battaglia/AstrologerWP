<?php
/**
 * Sidereal ayanamsa mode enum — 48+ modes matching the upstream API.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Enums;

/**
 * Sidereal ayanamsa modes supported by the Astrologer API.
 */
enum SiderealMode: string {

	case FAGAN_BRADLEY        = 'FAGAN_BRADLEY';
	case LAHIRI               = 'LAHIRI';
	case LAHIRI_1940          = 'LAHIRI_1940';
	case LAHIRI_ICRC          = 'LAHIRI_ICRC';
	case LAHIRI_VP285         = 'LAHIRI_VP285';
	case KRISHNAMURTI         = 'KRISHNAMURTI';
	case KRISHNAMURTI_VP291   = 'KRISHNAMURTI_VP291';
	case RAMAN                = 'RAMAN';
	case USHASHASHI           = 'USHASHASHI';
	case JN_BHASIN            = 'JN_BHASIN';
	case YUKTESHWAR           = 'YUKTESHWAR';
	case ARYABHATA            = 'ARYABHATA';
	case ARYABHATA_522        = 'ARYABHATA_522';
	case ARYABHATA_MSUN       = 'ARYABHATA_MSUN';
	case SURYASIDDHANTA       = 'SURYASIDDHANTA';
	case SURYASIDDHANTA_MSUN  = 'SURYASIDDHANTA_MSUN';
	case SS_CITRA             = 'SS_CITRA';
	case SS_REVATI            = 'SS_REVATI';
	case TRUE_CITRA           = 'TRUE_CITRA';
	case TRUE_MULA            = 'TRUE_MULA';
	case TRUE_PUSHYA          = 'TRUE_PUSHYA';
	case TRUE_REVATI          = 'TRUE_REVATI';
	case TRUE_SHEORAN         = 'TRUE_SHEORAN';
	case DELUCE               = 'DELUCE';
	case DJWHAL_KHUL          = 'DJWHAL_KHUL';
	case HIPPARCHOS           = 'HIPPARCHOS';
	case SASSANIAN            = 'SASSANIAN';
	case BABYL_KUGLER1        = 'BABYL_KUGLER1';
	case BABYL_KUGLER2        = 'BABYL_KUGLER2';
	case BABYL_KUGLER3        = 'BABYL_KUGLER3';
	case BABYL_HUBER          = 'BABYL_HUBER';
	case BABYL_ETPSC          = 'BABYL_ETPSC';
	case BABYL_BRITTON        = 'BABYL_BRITTON';
	case GALCENT_0SAG         = 'GALCENT_0SAG';
	case GALCENT_COCHRANE     = 'GALCENT_COCHRANE';
	case GALCENT_MULA_WILHELM = 'GALCENT_MULA_WILHELM';
	case GALCENT_RGILBRAND    = 'GALCENT_RGILBRAND';
	case GALEQU_FIORENZA      = 'GALEQU_FIORENZA';
	case GALEQU_IAU1958       = 'GALEQU_IAU1958';
	case GALEQU_MULA          = 'GALEQU_MULA';
	case GALEQU_TRUE          = 'GALEQU_TRUE';
	case GALALIGN_MARDYKS     = 'GALALIGN_MARDYKS';
	case J2000                = 'J2000';
	case J1900                = 'J1900';
	case B1950                = 'B1950';
	case ALDEBARAN_15TAU      = 'ALDEBARAN_15TAU';
	case VALENS_MOON          = 'VALENS_MOON';
	case USER                 = 'USER';

	/**
	 * Human-readable label.
	 *
	 * @return string
	 */
	public function label(): string {
		return match ( $this ) {
			self::FAGAN_BRADLEY        => __( 'Fagan-Bradley', 'astrologer-api' ),
			self::LAHIRI               => __( 'Lahiri', 'astrologer-api' ),
			self::LAHIRI_1940          => __( 'Lahiri (1940)', 'astrologer-api' ),
			self::LAHIRI_ICRC          => __( 'Lahiri (ICRC)', 'astrologer-api' ),
			self::LAHIRI_VP285         => __( 'Lahiri (VP285)', 'astrologer-api' ),
			self::KRISHNAMURTI         => __( 'Krishnamurti', 'astrologer-api' ),
			self::KRISHNAMURTI_VP291   => __( 'Krishnamurti (VP291)', 'astrologer-api' ),
			self::RAMAN                => __( 'Raman', 'astrologer-api' ),
			self::USHASHASHI           => __( 'Ushashashi', 'astrologer-api' ),
			self::JN_BHASIN            => __( 'J.N. Bhasin', 'astrologer-api' ),
			self::YUKTESHWAR           => __( 'Yukteshwar', 'astrologer-api' ),
			self::ARYABHATA            => __( 'Aryabhata', 'astrologer-api' ),
			self::ARYABHATA_522        => __( 'Aryabhata (522)', 'astrologer-api' ),
			self::ARYABHATA_MSUN       => __( 'Aryabhata (MSun)', 'astrologer-api' ),
			self::SURYASIDDHANTA       => __( 'Surya Siddhanta', 'astrologer-api' ),
			self::SURYASIDDHANTA_MSUN  => __( 'Surya Siddhanta (MSun)', 'astrologer-api' ),
			self::SS_CITRA             => __( 'SS Citra', 'astrologer-api' ),
			self::SS_REVATI            => __( 'SS Revati', 'astrologer-api' ),
			self::TRUE_CITRA           => __( 'True Citra', 'astrologer-api' ),
			self::TRUE_MULA            => __( 'True Mula', 'astrologer-api' ),
			self::TRUE_PUSHYA          => __( 'True Pushya', 'astrologer-api' ),
			self::TRUE_REVATI          => __( 'True Revati', 'astrologer-api' ),
			self::TRUE_SHEORAN         => __( 'True Sheoran', 'astrologer-api' ),
			self::DELUCE               => __( 'DeLuce', 'astrologer-api' ),
			self::DJWHAL_KHUL          => __( 'Djwhal Khul', 'astrologer-api' ),
			self::HIPPARCHOS           => __( 'Hipparchos', 'astrologer-api' ),
			self::SASSANIAN            => __( 'Sassanian', 'astrologer-api' ),
			self::BABYL_KUGLER1        => __( 'Babylonian (Kugler 1)', 'astrologer-api' ),
			self::BABYL_KUGLER2        => __( 'Babylonian (Kugler 2)', 'astrologer-api' ),
			self::BABYL_KUGLER3        => __( 'Babylonian (Kugler 3)', 'astrologer-api' ),
			self::BABYL_HUBER          => __( 'Babylonian (Huber)', 'astrologer-api' ),
			self::BABYL_ETPSC          => __( 'Babylonian (ETPSC)', 'astrologer-api' ),
			self::BABYL_BRITTON        => __( 'Babylonian (Britton)', 'astrologer-api' ),
			self::GALCENT_0SAG         => __( 'Galactic Center (0 Sag)', 'astrologer-api' ),
			self::GALCENT_COCHRANE     => __( 'Galactic Center (Cochrane)', 'astrologer-api' ),
			self::GALCENT_MULA_WILHELM => __( 'Galactic Center (Mula-Wilhelm)', 'astrologer-api' ),
			self::GALCENT_RGILBRAND    => __( 'Galactic Center (Rgilbrand)', 'astrologer-api' ),
			self::GALEQU_FIORENZA      => __( 'Galactic Equatorial (Fiorenza)', 'astrologer-api' ),
			self::GALEQU_IAU1958       => __( 'Galactic Equatorial (IAU 1958)', 'astrologer-api' ),
			self::GALEQU_MULA          => __( 'Galactic Equatorial (Mula)', 'astrologer-api' ),
			self::GALEQU_TRUE          => __( 'Galactic Equatorial (True)', 'astrologer-api' ),
			self::GALALIGN_MARDYKS     => __( 'Galactic Alignment (Mardyks)', 'astrologer-api' ),
			self::J2000                => __( 'J2000', 'astrologer-api' ),
			self::J1900                => __( 'J1900', 'astrologer-api' ),
			self::B1950                => __( 'B1950', 'astrologer-api' ),
			self::ALDEBARAN_15TAU      => __( 'Aldebaran (15 Taurus)', 'astrologer-api' ),
			self::VALENS_MOON          => __( 'Valens (Moon)', 'astrologer-api' ),
			self::USER                 => __( 'Custom (User)', 'astrologer-api' ),
		};
	}

	/**
	 * Return the default sidereal mode.
	 *
	 * @return self
	 */
	public static function get_default(): self {
		return self::LAHIRI;
	}
}
