<?php
/**
 * SynastryRequestDTO — request payload for synastry (bi-wheel) charts.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\DTO;

use Astrologer\Api\ValueObjects\ChartOptions;

/**
 * Data Transfer Object for synastry chart requests.
 *
 * Requires two subjects (first_subject = natal, second_subject = partner).
 */
final readonly class SynastryRequestDTO {

	/**
	 * Constructor.
	 *
	 * @param SubjectDTO   $first_subject  Natal subject (inner wheel).
	 * @param SubjectDTO   $second_subject Partner subject (outer wheel).
	 * @param ChartOptions $options        Chart rendering options.
	 * @param bool         $svg            Whether to request SVG output.
	 * @param bool         $ai_ctx         Whether to include AI context text.
	 */
	public function __construct(
		public SubjectDTO $first_subject,
		public SubjectDTO $second_subject,
		public ChartOptions $options,
		public bool $svg = false,
		public bool $ai_ctx = true,
	) {
	}

	/**
	 * Create from an associative array.
	 *
	 * @param array<string,mixed> $data Keyed array with synastry request fields.
	 * @return self
	 */
	public static function from_array( array $data ): self {
		$first   = SubjectDTO::from_array( $data['first_subject'] ?? array() );
		$second  = SubjectDTO::from_array( $data['second_subject'] ?? array() );
		$options = isset( $data['options'] ) && is_array( $data['options'] )
			? ChartOptions::from_array( $data['options'] )
			: ChartOptions::defaults();

		return new self(
			first_subject: $first,
			second_subject: $second,
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
		return array(
			'first_subject'            => $this->first_subject->to_array(),
			'second_subject'           => $this->second_subject->to_array(),
			'include_svg'              => $this->svg,
			'include_ai_context'       => $this->ai_ctx,
			'include_house_comparison' => true,
		);
	}
}
