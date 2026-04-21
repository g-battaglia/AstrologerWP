<?php
/**
 * ReturnRequestDTO — request payload for solar/lunar return charts.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\DTO;

use Astrologer\Api\ValueObjects\ChartOptions;
use Astrologer\Api\ValueObjects\GeoLocation;

/**
 * Data Transfer Object for solar return and lunar return chart requests.
 *
 * A return occurs when a transiting planet returns to its exact natal longitude.
 * The year (and month for lunar) is required to narrow the search window.
 */
final readonly class ReturnRequestDTO {

	/**
	 * Constructor.
	 *
	 * @param SubjectDTO      $subject         The natal subject.
	 * @param int             $year            Target year for the return.
	 * @param int|null        $month           Target month (required for lunar returns).
	 * @param int|null        $day             Target day (optional search start).
	 * @param string|null     $iso_datetime    Precise search start moment.
	 * @param string          $wheel_type      'dual' (natal + return) or 'single'.
	 * @param GeoLocation|null $return_location Relocated return location override.
	 * @param ChartOptions    $options         Chart rendering options.
	 * @param bool            $svg             Whether to request SVG output.
	 * @param bool            $ai_ctx          Whether to include AI context text.
	 */
	public function __construct(
		public SubjectDTO $subject,
		public int $year,
		public ?int $month,
		public ?int $day,
		public ?string $iso_datetime,
		public string $wheel_type,
		public ?GeoLocation $return_location,
		public ChartOptions $options,
		public bool $svg = false,
		public bool $ai_ctx = true,
	) {
		if ( ! in_array( $wheel_type, array( 'dual', 'single' ), true ) ) {
			throw new \InvalidArgumentException(
				esc_html( sprintf( 'Wheel type must be "dual" or "single", got "%s".', $wheel_type ) )
			);
		}
	}

	/**
	 * Create from an associative array.
	 *
	 * @param array<string,mixed> $data Keyed array with return request fields.
	 * @return self
	 */
	public static function from_array( array $data ): self {
		$subject = SubjectDTO::from_array( $data['subject'] ?? array() );
		$options = isset( $data['options'] ) && is_array( $data['options'] )
			? ChartOptions::from_array( $data['options'] )
			: ChartOptions::defaults();

		$return_location = null;
		if ( isset( $data['return_location'] ) && is_array( $data['return_location'] ) ) {
			$return_location = GeoLocation::from_array( $data['return_location'] );
		}

		return new self(
			subject: $subject,
			year: (int) ( $data['year'] ?? gmdate( 'Y' ) ),
			month: isset( $data['month'] ) ? (int) $data['month'] : null,
			day: isset( $data['day'] ) ? (int) $data['day'] : null,
			iso_datetime: $data['iso_datetime'] ?? null,
			wheel_type: (string) ( $data['wheel_type'] ?? 'dual' ),
			return_location: $return_location,
			options: $options,
			svg: (bool) ( $data['svg'] ?? false ),
			ai_ctx: (bool) ( $data['ai_ctx'] ?? true ),
		);
	}

	/**
	 * Convert to an associative array for the upstream API payload.
	 *
	 * @return array<string,mixed>
	 */
	public function to_array(): array {
		$result = array(
			'subject'            => $this->subject->to_array(),
			'year'               => $this->year,
			'wheel_type'         => $this->wheel_type,
			'include_svg'        => $this->svg,
			'include_ai_context' => $this->ai_ctx,
		);

		if ( null !== $this->month ) {
			$result['month'] = $this->month;
		}

		if ( null !== $this->day ) {
			$result['day'] = $this->day;
		}

		if ( null !== $this->iso_datetime ) {
			$result['iso_datetime'] = $this->iso_datetime;
		}

		if ( null !== $this->return_location ) {
			$result['return_location'] = array(
				'latitude'  => $this->return_location->latitude,
				'longitude' => $this->return_location->longitude,
				'timezone'  => $this->return_location->timezone,
			);

			if ( null !== $this->return_location->city ) {
				$result['return_location']['city'] = $this->return_location->city;
			}
		}

		return $result;
	}
}
