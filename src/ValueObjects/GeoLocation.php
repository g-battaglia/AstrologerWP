<?php
/**
 * GeoLocation value object — geographic coordinates and timezone.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\ValueObjects;

/**
 * Immutable value object representing a geographic location on Earth.
 *
 * Used throughout the plugin for birth locations, transit locations,
 * and relocation targets.
 */
final readonly class GeoLocation {

	/**
	 * Constructor.
	 *
	 * @param float       $latitude  Latitude in decimal degrees (-90 to 90).
	 * @param float       $longitude Longitude in decimal degrees (-180 to 180).
	 * @param string      $timezone  IANA timezone identifier (e.g. 'Europe/Rome').
	 * @param float|null  $altitude  Altitude above sea level in metres.
	 * @param bool|null   $is_dst    Whether daylight saving time is in effect.
	 * @param string|null $city      City name (human-readable).
	 * @param string|null $nation    ISO 3166-1 alpha-2 country code (e.g. 'IT').
	 */
	public function __construct(
		public float $latitude,
		public float $longitude,
		public string $timezone,
		public ?float $altitude = null,
		public ?bool $is_dst = null,
		public ?string $city = null,
		public ?string $nation = null,
	) {
		if ( $latitude < -90.0 || $latitude > 90.0 ) {
			throw new \InvalidArgumentException(
				esc_html( sprintf( 'Latitude must be between -90 and 90, got %s.', $latitude ) )
			);
		}

		if ( $longitude < -180.0 || $longitude > 180.0 ) {
			throw new \InvalidArgumentException(
				esc_html( sprintf( 'Longitude must be between -180 and 180, got %s.', $longitude ) )
			);
		}

		if ( '' === $timezone ) {
			throw new \InvalidArgumentException( 'Timezone must not be empty.' );
		}
	}

	/**
	 * Create from an associative array.
	 *
	 * @param array<string,mixed> $data Keyed array with location fields.
	 * @return self
	 */
	public static function from_array( array $data ): self {
		return new self(
			latitude: (float) ( $data['latitude'] ?? 0.0 ),
			longitude: (float) ( $data['longitude'] ?? 0.0 ),
			timezone: (string) ( $data['timezone'] ?? 'UTC' ),
			altitude: isset( $data['altitude'] ) ? (float) $data['altitude'] : null,
			is_dst: isset( $data['is_dst'] ) ? (bool) $data['is_dst'] : null,
			city: isset( $data['city'] ) ? (string) $data['city'] : null,
			nation: isset( $data['nation'] ) ? (string) $data['nation'] : null,
		);
	}

	/**
	 * Convert to an associative array suitable for API payloads.
	 *
	 * @return array<string,mixed>
	 */
	public function to_array(): array {
		$result = array(
			'latitude'  => $this->latitude,
			'longitude' => $this->longitude,
			'timezone'  => $this->timezone,
		);

		if ( null !== $this->altitude ) {
			$result['altitude'] = $this->altitude;
		}

		if ( null !== $this->is_dst ) {
			$result['is_dst'] = $this->is_dst;
		}

		if ( null !== $this->city ) {
			$result['city'] = $this->city;
		}

		if ( null !== $this->nation ) {
			$result['nation'] = $this->nation;
		}

		return $result;
	}
}
