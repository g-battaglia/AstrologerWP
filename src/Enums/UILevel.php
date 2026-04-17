<?php
/**
 * UI level enum — controls which options are visible to the user.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Enums;

/**
 * UI complexity level.
 *
 * Basic: minimal options. Advanced: most options. Expert: all options.
 */
enum UILevel: string {

	case Basic    = 'basic';
	case Advanced = 'advanced';
	case Expert   = 'expert';

	/**
	 * Human-readable label.
	 *
	 * @return string
	 */
	public function label(): string {
		return match ( $this ) {
			self::Basic    => __( 'Basic', 'astrologer-api' ),
			self::Advanced => __( 'Advanced', 'astrologer-api' ),
			self::Expert   => __( 'Expert', 'astrologer-api' ),
		};
	}

	/**
	 * Return the default UI level.
	 *
	 * @return self
	 */
	public static function get_default(): self {
		return self::Basic;
	}
}
