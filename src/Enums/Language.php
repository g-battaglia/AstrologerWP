<?php
/**
 * Language enum — supported API response languages.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Enums;

/**
 * Languages supported by the Astrologer API for chart responses.
 */
enum Language: string {

	case EN = 'EN';
	case IT = 'IT';
	case FR = 'FR';
	case ES = 'ES';
	case PT = 'PT';
	case CN = 'CN';
	case RU = 'RU';
	case TR = 'TR';
	case DE = 'DE';
	case HI = 'HI';

	/**
	 * Human-readable label.
	 *
	 * @return string
	 */
	public function label(): string {
		return match ( $this ) {
			self::EN => __( 'English', 'astrologer-api' ),
			self::IT => __( 'Italian', 'astrologer-api' ),
			self::FR => __( 'French', 'astrologer-api' ),
			self::ES => __( 'Spanish', 'astrologer-api' ),
			self::PT => __( 'Portuguese', 'astrologer-api' ),
			self::CN => __( 'Chinese', 'astrologer-api' ),
			self::RU => __( 'Russian', 'astrologer-api' ),
			self::TR => __( 'Turkish', 'astrologer-api' ),
			self::DE => __( 'German', 'astrologer-api' ),
			self::HI => __( 'Hindi', 'astrologer-api' ),
		};
	}

	/**
	 * Return the default language.
	 *
	 * @return self
	 */
	public static function get_default(): self {
		return self::EN;
	}
}
