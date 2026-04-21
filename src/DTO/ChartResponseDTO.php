<?php
/**
 * ChartResponseDTO — normalized response from any chart endpoint.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\DTO;

/**
 * Data Transfer Object representing the upstream API chart response.
 *
 * Provides a unified shape for all chart endpoint responses regardless
 * of the chart type (natal, synastry, transit, composite, return, now).
 */
final readonly class ChartResponseDTO {

	/**
	 * Constructor.
	 *
	 * @param string|null               $svg           SVG chart image (if requested).
	 * @param array<string,mixed>|null  $positions     Planetary positions data.
	 * @param array<string,mixed>|null  $houses        House cusps data.
	 * @param array<string,mixed>|null  $aspects       Aspect data.
	 * @param array<string,mixed>|null  $distributions Element/quality distributions.
	 * @param string|null               $ai_context    AI-generated interpretation text.
	 * @param array<string,mixed>|null  $raw           Full raw response for passthrough.
	 */
	public function __construct(
		public ?string $svg,
		public ?array $positions,
		public ?array $houses,
		public ?array $aspects,
		public ?array $distributions,
		public ?string $ai_context,
		public ?array $raw,
	) {
	}

	/**
	 * Create from the upstream API response array.
	 *
	 * @param array<string,mixed> $data Raw API response.
	 * @return self
	 */
	public static function from_array( array $data ): self {
		return new self(
			svg: $data['svg'] ?? $data['chart_svg'] ?? null,
			positions: $data['positions'] ?? null,
			houses: $data['houses'] ?? $data['house_cusps'] ?? null,
			aspects: $data['aspects'] ?? null,
			distributions: $data['distributions'] ?? $data['elements'] ?? null,
			ai_context: $data['ai_context'] ?? null,
			raw: $data,
		);
	}

	/**
	 * Convert to an associative array for storage or REST output.
	 *
	 * @return array<string,mixed>
	 */
	public function to_array(): array {
		$result = array();

		if ( null !== $this->svg ) {
			$result['svg'] = $this->svg;
		}

		if ( null !== $this->positions ) {
			$result['positions'] = $this->positions;
		}

		if ( null !== $this->houses ) {
			$result['houses'] = $this->houses;
		}

		if ( null !== $this->aspects ) {
			$result['aspects'] = $this->aspects;
		}

		if ( null !== $this->distributions ) {
			$result['distributions'] = $this->distributions;
		}

		if ( null !== $this->ai_context ) {
			$result['ai_context'] = $this->ai_context;
		}

		return $result;
	}

	/**
	 * Whether this response contains an SVG chart image.
	 *
	 * @return bool
	 */
	public function has_svg(): bool {
		return null !== $this->svg && '' !== $this->svg;
	}

	/**
	 * Whether this response contains AI context text.
	 *
	 * @return bool
	 */
	public function has_ai_context(): bool {
		return null !== $this->ai_context && '' !== $this->ai_context;
	}
}
