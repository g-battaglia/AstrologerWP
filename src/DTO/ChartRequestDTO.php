<?php
/**
 * ChartRequestDTO — request payload for single-subject chart endpoints.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\DTO;

use Astrologer\Api\Enums\ChartType;
use Astrologer\Api\ValueObjects\ChartOptions;

/**
 * Data Transfer Object for natal/birth chart requests.
 *
 * Wraps a single subject with chart rendering options and chart type.
 */
final readonly class ChartRequestDTO {

	/**
	 * Constructor.
	 *
	 * @param SubjectDTO   $subject   The natal subject.
	 * @param ChartOptions $options   Chart rendering options.
	 * @param ChartType    $type      Chart type identifier.
	 * @param bool         $svg       Whether to request SVG output.
	 * @param bool         $ai_ctx    Whether to include AI context text.
	 */
	public function __construct(
		public SubjectDTO $subject,
		public ChartOptions $options,
		public ChartType $type = ChartType::Natal,
		public bool $svg = false,
		public bool $ai_ctx = true,
	) {
	}

	/**
	 * Create from an associative array.
	 *
	 * @param array<string,mixed> $data Keyed array with chart request fields.
	 * @return self
	 */
	public static function from_array( array $data ): self {
		$subject = SubjectDTO::from_array( $data['subject'] ?? $data );
		$options = isset( $data['options'] ) && is_array( $data['options'] )
			? ChartOptions::from_array( $data['options'] )
			: ChartOptions::defaults();

		$type = ChartType::tryFrom( (string) ( $data['type'] ?? '' ) ) ?? ChartType::Natal;

		return new self(
			subject: $subject,
			options: $options,
			type: $type,
			svg: (bool) ( $data['svg'] ?? false ),
			ai_ctx: (bool) ( $data['ai_ctx'] ?? true ),
		);
	}

	/**
	 * Convert to an associative array for the upstream API payload.
	 *
	 * Merges subject fields with chart option fields at the top level,
	 * matching the Astrologer API query parameter structure.
	 *
	 * @return array<string,mixed>
	 */
	public function to_array(): array {
		$result = $this->subject->to_array();

		$result['type']               = $this->type->value;
		$result['include_svg']        = $this->svg;
		$result['include_ai_context'] = $this->ai_ctx;

		return array_merge( $result, $this->options->to_array() );
	}
}
