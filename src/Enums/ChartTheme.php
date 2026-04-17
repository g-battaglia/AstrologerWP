<?php
/**
 * Chart theme enum — visual style for SVG chart rendering.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Enums;

/**
 * Chart visual themes supported by the Astrologer API.
 */
enum ChartTheme: string {

	case Classic          = 'classic';
	case Dark             = 'dark';
	case DarkHighContrast = 'dark-high-contrast';
	case Light            = 'light';
	case Strawberry       = 'strawberry';
	case BlackAndWhite    = 'black-and-white';

	/**
	 * Human-readable label.
	 *
	 * @return string
	 */
	public function label(): string {
		return match ( $this ) {
			self::Classic          => __( 'Classic', 'astrologer-api' ),
			self::Dark             => __( 'Dark', 'astrologer-api' ),
			self::DarkHighContrast => __( 'Dark (High Contrast)', 'astrologer-api' ),
			self::Light            => __( 'Light', 'astrologer-api' ),
			self::Strawberry       => __( 'Strawberry', 'astrologer-api' ),
			self::BlackAndWhite    => __( 'Black & White', 'astrologer-api' ),
		};
	}

	/**
	 * Return the default chart theme.
	 *
	 * @return self
	 */
	public static function get_default(): self {
		return self::Classic;
	}
}
