<?php
/**
 * BirthData value object — birth date, time, name, and location.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\ValueObjects;

/**
 * Immutable value object representing a person's birth data.
 *
 * Combines birth date/time with a geographic location and an optional
 * ISO datetime string for precise time specification.
 */
final readonly class BirthData {

	/**
	 * Constructor.
	 *
	 * @param string      $name          Display name for the subject.
	 * @param int         $year          Birth year (1 CE = 1, 1 BCE = 0, etc.).
	 * @param int         $month         Birth month (1-12).
	 * @param int         $day           Birth day (1-31).
	 * @param int         $hour          Birth hour (0-23).
	 * @param int         $minute        Birth minute (0-59).
	 * @param GeoLocation $location      Birth location.
	 * @param string|null $iso_datetime  Optional ISO datetime string override.
	 */
	public function __construct(
		public string $name,
		public int $year,
		public int $month,
		public int $day,
		public int $hour,
		public int $minute,
		public GeoLocation $location,
		public ?string $iso_datetime = null,
	) {
		if ( $year < 0 || $year > 3000 ) {
			throw new \InvalidArgumentException(
				esc_html( sprintf( 'Year must be between 0 (1 BCE) and 3000, got %d.', $year ) )
			);
		}

		if ( $month < 1 || $month > 12 ) {
			throw new \InvalidArgumentException(
				esc_html( sprintf( 'Month must be between 1 and 12, got %d.', $month ) )
			);
		}

		if ( $day < 1 || $day > 31 ) {
			throw new \InvalidArgumentException(
				esc_html( sprintf( 'Day must be between 1 and 31, got %d.', $day ) )
			);
		}

		if ( $hour < 0 || $hour > 23 ) {
			throw new \InvalidArgumentException(
				esc_html( sprintf( 'Hour must be between 0 and 23, got %d.', $hour ) )
			);
		}

		if ( $minute < 0 || $minute > 59 ) {
			throw new \InvalidArgumentException(
				esc_html( sprintf( 'Minute must be between 0 and 59, got %d.', $minute ) )
			);
		}

		if ( '' === $name ) {
			throw new \InvalidArgumentException( 'Name must not be empty.' );
		}
	}

	/**
	 * Create from an associative array.
	 *
	 * @param array<string,mixed> $data Keyed array with birth data fields.
	 * @return self
	 */
	public static function from_array( array $data ): self {
		$location = GeoLocation::from_array( $data['location'] ?? array() );

		return new self(
			name: (string) ( $data['name'] ?? '' ),
			year: (int) ( $data['year'] ?? 2000 ),
			month: (int) ( $data['month'] ?? 1 ),
			day: (int) ( $data['day'] ?? 1 ),
			hour: (int) ( $data['hour'] ?? 0 ),
			minute: (int) ( $data['minute'] ?? 0 ),
			location: $location,
			iso_datetime: isset( $data['iso_datetime'] ) ? (string) $data['iso_datetime'] : null,
		);
	}

	/**
	 * Convert to an associative array suitable for API payloads.
	 *
	 * @return array<string,mixed>
	 */
	public function to_array(): array {
		$result = array(
			'name'     => $this->name,
			'year'     => $this->year,
			'month'    => $this->month,
			'day'      => $this->day,
			'hour'     => $this->hour,
			'minute'   => $this->minute,
			'location' => $this->location->to_array(),
		);

		if ( null !== $this->iso_datetime ) {
			$result['iso_datetime'] = $this->iso_datetime;
		}

		return $result;
	}
}
