<?php
/**
 * SubjectDTO — birth data for a single astrological subject.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\DTO;

use Astrologer\Api\ValueObjects\BirthData;

/**
 * Data Transfer Object representing a single subject's birth data.
 *
 * Used to marshal subject information between REST endpoints and the API client.
 */
final readonly class SubjectDTO {

	/**
	 * Constructor.
	 *
	 * @param BirthData $birth_data The subject's birth data.
	 */
	public function __construct(
		public BirthData $birth_data,
	) {
	}

	/**
	 * Create from an associative array.
	 *
	 * @param array<string,mixed> $data Keyed array with subject fields.
	 * @return self
	 */
	public static function from_array( array $data ): self {
		return new self(
			birth_data: BirthData::from_array( $data ),
		);
	}

	/**
	 * Convert to an associative array for the upstream API payload.
	 *
	 * @return array<string,mixed>
	 */
	public function to_array(): array {
		$birth = $this->birth_data;
		$loc   = $birth->location;

		$result = array(
			'name'      => $birth->name,
			'year'      => $birth->year,
			'month'     => $birth->month,
			'day'       => $birth->day,
			'hour'      => $birth->hour,
			'minute'    => $birth->minute,
			'city'      => $loc->city ?? 'Greenwich',
			'nation'    => $loc->nation ?? 'GB',
			'latitude'  => $loc->latitude,
			'longitude' => $loc->longitude,
			'timezone'  => $loc->timezone,
		);

		if ( null !== $birth->iso_datetime ) {
			$result['iso_datetime'] = $birth->iso_datetime;
		}

		if ( null !== $loc->altitude ) {
			$result['altitude'] = $loc->altitude;
		}

		if ( null !== $loc->is_dst ) {
			$result['is_dst'] = $loc->is_dst;
		}

		return $result;
	}
}
