<?php
/**
 * NowRequestDTO — request payload for the current moment chart.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\DTO;

use Astrologer\Api\ValueObjects\ChartOptions;

/**
 * Data Transfer Object for "current moment" chart requests.
 *
 * No birth data is required — this endpoint calculates planetary positions
 * for right now at Greenwich (or a specified location).
 */
final readonly class NowRequestDTO {

	/**
	 * Constructor.
	 *
	 * @param ChartOptions $options Chart rendering options.
	 * @param bool         $svg     Whether to request SVG output.
	 * @param bool         $ai_ctx  Whether to include AI context text.
	 */
	public function __construct(
		public ChartOptions $options,
		public bool $svg = false,
		public bool $ai_ctx = true,
	) {
	}

	/**
	 * Create from an associative array.
	 *
	 * @param array<string,mixed> $data Keyed array with now request fields.
	 * @return self
	 */
	public static function from_array( array $data ): self {
		$options = isset( $data['options'] ) && is_array( $data['options'] )
			? ChartOptions::from_array( $data['options'] )
			: ChartOptions::defaults();

		return new self(
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
			'include_svg'        => $this->svg,
			'include_ai_context' => $this->ai_ctx,
		);
	}
}
