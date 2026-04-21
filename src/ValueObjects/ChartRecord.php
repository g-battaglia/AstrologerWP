<?php
/**
 * ChartRecord value object — a persisted chart with all its metadata.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\ValueObjects;

use Astrologer\Api\Enums\ChartType;

/**
 * Immutable value object representing a persisted chart record.
 *
 * Wraps the CPT post data and meta into a single typed object.
 */
final readonly class ChartRecord {

	/**
	 * Constructor.
	 *
	 * @param int                        $id             Post ID.
	 * @param ChartType                  $chart_type     Type of chart.
	 * @param BirthData                  $birth_data     Subject birth data.
	 * @param ChartOptions               $chart_options  Chart rendering options.
	 * @param int                        $author_id      Post author (user who created the chart).
	 * @param string                     $status         Post status (e.g. 'private', 'publish').
	 * @param string                     $title          Post title.
	 * @param string|null                $response_svg   SVG chart image, if saved.
	 * @param array<string,mixed>|null   $response_data  Full API response data, if saved.
	 * @param string|null                $created_date   MySQL datetime the chart was created.
	 */
	public function __construct(
		public int $id,
		public ChartType $chart_type,
		public BirthData $birth_data,
		public ChartOptions $chart_options,
		public int $author_id,
		public string $status,
		public string $title,
		public ?string $response_svg,
		public ?array $response_data,
		public ?string $created_date,
	) {
	}
}
