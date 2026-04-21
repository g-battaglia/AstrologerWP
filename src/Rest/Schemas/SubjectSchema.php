<?php
/**
 * SubjectSchema — shared JSON schema for the `subject` parameter.
 *
 * Used by all chart endpoints that accept birth data (natal, synastry,
 * transit, composite, solar/lunar return, etc.).
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Rest\Schemas;

use Astrologer\Api\Enums\HouseSystem;
use Astrologer\Api\Enums\PerspectiveType;
use Astrologer\Api\Enums\SiderealMode;
use Astrologer\Api\Enums\ZodiacType;

/**
 * Provides the JSON schema definition for a single astrological subject.
 *
 * Controllers reuse this via SubjectSchema::get() to avoid duplicating
 * field definitions across multiple route registrations.
 */
final class SubjectSchema {

	/**
	 * Return the JSON schema array for a `subject` parameter.
	 *
	 * The schema follows the WP REST API convention where each top-level
	 * key is a field name and the value is its schema definition including
	 * validate_callback and sanitize_callback.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public static function get(): array {
		$house_values       = array_map(
			static fn ( HouseSystem $h ): string => $h->value,
			HouseSystem::cases(),
		);
		$zodiac_values      = array_map(
			static fn ( ZodiacType $z ): string => $z->value,
			ZodiacType::cases(),
		);
		$sidereal_values    = array_map(
			static fn ( SiderealMode $m ): string => $m->value,
			SiderealMode::cases(),
		);
		$perspective_values = array_map(
			static fn ( PerspectiveType $p ): string => $p->value,
			PerspectiveType::cases(),
		);

		return array(
			'name'                     => array(
				'description'       => __( 'Display name for the subject.', 'astrologer-api' ),
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => static function ( mixed $value ): bool {
					return is_string( $value ) && '' !== $value;
				},
			),
			'year'                     => array(
				'description'       => __( 'Birth year (1 CE = 1, 1 BCE = 0, etc.).', 'astrologer-api' ),
				'type'              => 'integer',
				'required'          => true,
				'minimum'           => -13200,
				'maximum'           => 3000,
				'sanitize_callback' => 'absint',
				'validate_callback' => static function ( mixed $value ): bool {
					return is_numeric( $value ) && (int) $value >= -13200 && (int) $value <= 3000;
				},
			),
			'month'                    => array(
				'description'       => __( 'Birth month (1-12).', 'astrologer-api' ),
				'type'              => 'integer',
				'required'          => true,
				'minimum'           => 1,
				'maximum'           => 12,
				'sanitize_callback' => 'absint',
				'validate_callback' => static function ( mixed $value ): bool {
					return is_numeric( $value ) && (int) $value >= 1 && (int) $value <= 12;
				},
			),
			'day'                      => array(
				'description'       => __( 'Birth day (1-31).', 'astrologer-api' ),
				'type'              => 'integer',
				'required'          => true,
				'minimum'           => 1,
				'maximum'           => 31,
				'sanitize_callback' => 'absint',
				'validate_callback' => static function ( mixed $value ): bool {
					return is_numeric( $value ) && (int) $value >= 1 && (int) $value <= 31;
				},
			),
			'hour'                     => array(
				'description'       => __( 'Birth hour (0-23).', 'astrologer-api' ),
				'type'              => 'integer',
				'required'          => true,
				'minimum'           => 0,
				'maximum'           => 23,
				'sanitize_callback' => 'absint',
				'validate_callback' => static function ( mixed $value ): bool {
					return is_numeric( $value ) && (int) $value >= 0 && (int) $value <= 23;
				},
			),
			'minute'                   => array(
				'description'       => __( 'Birth minute (0-59).', 'astrologer-api' ),
				'type'              => 'integer',
				'required'          => true,
				'minimum'           => 0,
				'maximum'           => 59,
				'sanitize_callback' => 'absint',
				'validate_callback' => static function ( mixed $value ): bool {
					return is_numeric( $value ) && (int) $value >= 0 && (int) $value <= 59;
				},
			),
			'city'                     => array(
				'description'       => __( 'City name.', 'astrologer-api' ),
				'type'              => 'string',
				'default'           => 'Greenwich',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'nation'                   => array(
				'description'       => __( 'ISO 3166-1 alpha-2 country code.', 'astrologer-api' ),
				'type'              => 'string',
				'default'           => 'GB',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'longitude'                => array(
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
			'latitude'                 => array(
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
			'timezone'                 => array(
				'description'       => __( 'IANA timezone (e.g. Europe/Rome).', 'astrologer-api' ),
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => static function ( mixed $value ): bool {
					return is_string( $value ) && '' !== $value;
				},
			),
			'zodiac_type'              => array(
				'description'       => __( 'Tropical or Sidereal.', 'astrologer-api' ),
				'type'              => 'string',
				'default'           => ZodiacType::get_default()->value,
				'enum'              => $zodiac_values,
				'sanitize_callback' => 'sanitize_text_field',
			),
			'sidereal_mode'            => array(
				'description'       => __( 'Sidereal ayanamsha (required when zodiac is Sidereal).', 'astrologer-api' ),
				'type'              => 'string',
				'enum'              => $sidereal_values,
				'sanitize_callback' => 'sanitize_text_field',
			),
			'perspective_type'         => array(
				'description'       => __( 'Astronomical perspective.', 'astrologer-api' ),
				'type'              => 'string',
				'default'           => PerspectiveType::get_default()->value,
				'enum'              => $perspective_values,
				'sanitize_callback' => 'sanitize_text_field',
			),
			'houses_system_identifier' => array(
				'description'       => __( 'House system identifier.', 'astrologer-api' ),
				'type'              => 'string',
				'default'           => HouseSystem::get_default()->value,
				'enum'              => $house_values,
				'sanitize_callback' => 'sanitize_text_field',
			),
			'altitude'                 => array(
				'description'       => __( 'Altitude above sea level in meters.', 'astrologer-api' ),
				'type'              => 'number',
				'sanitize_callback' => 'floatval',
			),
			'is_dst'                   => array(
				'description'       => __( 'Whether daylight saving time is in effect.', 'astrologer-api' ),
				'type'              => 'boolean',
				'sanitize_callback' => static function ( mixed $value ): ?bool {
					return null === $value ? null : (bool) $value;
				},
			),
			'iso_datetime'             => array(
				'description'       => __( 'ISO datetime string for precise time specification.', 'astrologer-api' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'second'                   => array(
				'description'       => __( 'Birth second (0-59).', 'astrologer-api' ),
				'type'              => 'integer',
				'default'           => 0,
				'minimum'           => 0,
				'maximum'           => 59,
				'sanitize_callback' => 'absint',
			),
			'geonames_username'        => array(
				'description'       => __( 'GeoNames username for online geocoding.', 'astrologer-api' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
		);
	}
}
