<?php
/**
 * CompositeRequestDTO — request payload for composite chart.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\DTO;

use Astrologer\Api\ValueObjects\ChartOptions;

/**
 * Data Transfer Object for composite chart requests.
 *
 * Merges two subjects into one chart using midpoint or Davison method.
 */
final readonly class CompositeRequestDTO {

	/**
	 * Constructor.
	 *
	 * @param SubjectDTO   $first_subject  First subject.
	 * @param SubjectDTO   $second_subject Second subject.
	 * @param string       $composite_type Composite method: 'Midpoint' or 'Davison'.
	 * @param ChartOptions $options        Chart rendering options.
	 * @param bool         $svg            Whether to request SVG output.
	 * @param bool         $ai_ctx         Whether to include AI context text.
	 */
	public function __construct(
		public SubjectDTO $first_subject,
		public SubjectDTO $second_subject,
		public string $composite_type,
		public ChartOptions $options,
		public bool $svg = false,
		public bool $ai_ctx = true,
	) {
		if ( ! in_array( $composite_type, array( 'Midpoint', 'Davison' ), true ) ) {
			throw new \InvalidArgumentException(
				esc_html( sprintf( 'Composite type must be Midpoint or Davison, got "%s".', $composite_type ) )
			);
		}
	}

	/**
	 * Create from an associative array.
	 *
	 * @param array<string,mixed> $data Keyed array with composite request fields.
	 * @return self
	 */
	public static function from_array( array $data ): self {
		$first   = SubjectDTO::from_array( $data['first_subject'] ?? array() );
		$second  = SubjectDTO::from_array( $data['second_subject'] ?? array() );
		$type    = (string) ( $data['composite_type'] ?? 'Midpoint' );
		$options = isset( $data['options'] ) && is_array( $data['options'] )
			? ChartOptions::from_array( $data['options'] )
			: ChartOptions::defaults();

		return new self(
			first_subject: $first,
			second_subject: $second,
			composite_type: $type,
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
			'first_subject'      => $this->first_subject->to_array(),
			'second_subject'     => $this->second_subject->to_array(),
			'composite_type'     => $this->composite_type,
			'include_svg'        => $this->svg,
			'include_ai_context' => $this->ai_ctx,
		);
	}
}
