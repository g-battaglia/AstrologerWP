<?php
/**
 * MoonPhaseRequestDTO — request payload for moon phase endpoint.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\DTO;

/**
 * Data Transfer Object for moon phase requests.
 *
 * Provides date, time, and location for computing detailed moon phase
 * information including illumination, phase name, and upcoming phases.
 */
final readonly class MoonPhaseRequestDTO {

	/**
	 * Constructor.
	 *
	 * @param int    $year      Year for the moon phase calculation.
	 * @param int    $month     Month (1-12).
	 * @param int    $day       Day (1-31).
	 * @param int    $hour      Hour (0-23).
	 * @param int    $minute    Minute (0-59).
	 * @param float  $latitude  Observer latitude (-90 to 90).
	 * @param float  $longitude Observer longitude (-180 to 180).
	 * @param string $timezone  IANA timezone identifier.
	 * @param int    $second    Second (0-59).
	 * @param bool   $ai_ctx    Whether to include AI context text.
	 */
	public function __construct(
		public int $year,
		public int $month,
		public int $day,
		public int $hour,
		public int $minute,
		public float $latitude,
		public float $longitude,
		public string $timezone,
		public int $second = 0,
		public bool $ai_ctx = true,
	) {
	}

	/**
	 * Create from an associative array.
	 *
	 * @param array<string,mixed> $data Keyed array with moon phase request fields.
	 * @return self
	 */
	public static function from_array( array $data ): self {
		return new self(
			year: (int) ( $data['year'] ?? (int) gmdate( 'Y' ) ),
			month: (int) ( $data['month'] ?? (int) gmdate( 'n' ) ),
			day: (int) ( $data['day'] ?? (int) gmdate( 'j' ) ),
			hour: (int) ( $data['hour'] ?? (int) gmdate( 'G' ) ),
			minute: (int) ( $data['minute'] ?? 0 ),
			latitude: (float) ( $data['latitude'] ?? 51.4769 ),
			longitude: (float) ( $data['longitude'] ?? 0.0005 ),
			timezone: (string) ( $data['timezone'] ?? 'UTC' ),
			second: (int) ( $data['second'] ?? 0 ),
			ai_ctx: (bool) ( $data['ai_ctx'] ?? true ),
		);
	}

	/**
	 * Convert to an associative array for the upstream API payload.
	 *
	 * @return array<string,mixed>
	 */
	public function to_array(): array {
		return array(
			'year'               => $this->year,
			'month'              => $this->month,
			'day'                => $this->day,
			'hour'               => $this->hour,
			'minute'             => $this->minute,
			'latitude'           => $this->latitude,
			'longitude'          => $this->longitude,
			'timezone'           => $this->timezone,
			'second'             => $this->second,
			'include_ai_context' => $this->ai_ctx,
		);
	}
}
