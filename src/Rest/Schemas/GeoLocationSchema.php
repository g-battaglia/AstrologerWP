<?php
/**
 * GeoLocationSchema — shared JSON schema for geographic location parameters.
 *
 * Used by endpoints that accept standalone location data without full
 * birth data (e.g. moon-phase/at, geonames/timezone).
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Rest\Schemas;

/**
 * Provides the JSON schema definition for a geographic location.
 *
 * Controllers merge this into their route args where a location is
 * needed independently of a full subject.
 */
final class GeoLocationSchema {

	/**
	 * Return the JSON schema array for geo-location parameters.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public static function get(): array {
		return array(
			'city'      => array(
				'description'       => __( 'City name.', 'astrologer-api' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'nation'    => array(
				'description'       => __( 'ISO 3166-1 alpha-2 country code.', 'astrologer-api' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'latitude'  => array(
				'description'       => __( 'Latitude (-90 to 90).', 'astrologer-api' ),
				'type'              => 'number',
				'required'          => true,
				'minimum'           => -90.0,
				'maximum'           => 90.0,
				'sanitize_callback' => 'floatval',
				'validate_callback' => static function ( mixed $value ): bool {
					return is_numeric( $value ) && (float) $value >= -90.0 && (float) $value <= 90.0;
				},
			),
			'longitude' => array(
				'description'       => __( 'Longitude (-180 to 180).', 'astrologer-api' ),
				'type'              => 'number',
				'required'          => true,
				'minimum'           => -180.0,
				'maximum'           => 180.0,
				'sanitize_callback' => 'floatval',
				'validate_callback' => static function ( mixed $value ): bool {
					return is_numeric( $value ) && (float) $value >= -180.0 && (float) $value <= 180.0;
				},
			),
			'timezone'  => array(
				'description'       => __( 'IANA timezone identifier.', 'astrologer-api' ),
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => static function ( mixed $value ): bool {
					return is_string( $value ) && '' !== $value;
				},
			),
			'altitude'  => array(
				'description'       => __( 'Altitude above sea level in meters.', 'astrologer-api' ),
				'type'              => 'number',
				'sanitize_callback' => 'floatval',
			),
		);
	}
}
