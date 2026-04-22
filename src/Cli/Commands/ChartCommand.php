<?php
/**
 * ChartCommand — `wp astrologer chart ...` WP-CLI sub-command.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Cli\Commands;

use Astrologer\Api\DTO\ChartRequestDTO;
use Astrologer\Api\DTO\NowRequestDTO;
use Astrologer\Api\DTO\SubjectDTO;
use Astrologer\Api\Enums\ChartType;
use Astrologer\Api\Services\ChartService;
use Astrologer\Api\ValueObjects\BirthData;
use Astrologer\Api\ValueObjects\ChartOptions;
use Astrologer\Api\ValueObjects\GeoLocation;

/**
 * WP-CLI command for computing charts without hitting the REST layer.
 *
 * ## EXAMPLES
 *
 *     # Calculate a natal chart and print JSON.
 *     wp astrologer chart natal --name="Jane" --date=1990-05-15 --time=12:30 \
 *         --latitude=41.9 --longitude=12.5 --timezone=Europe/Rome
 *
 *     # Calculate the current-moment chart.
 *     wp astrologer chart now
 *
 *     # Print a concise table instead of JSON.
 *     wp astrologer chart natal --name="Jane" --date=1990-05-15 --time=12:30 \
 *         --latitude=41.9 --longitude=12.5 --timezone=Europe/Rome --format=table
 */
final class ChartCommand {

	/**
	 * Chart service.
	 *
	 * @var ChartService
	 */
	private ChartService $chart_service;

	/**
	 * Constructor.
	 *
	 * @param ChartService $chart_service Chart service.
	 */
	public function __construct( ChartService $chart_service ) {
		$this->chart_service = $chart_service;
	}

	/**
	 * Calculate a chart for the given type.
	 *
	 * ## OPTIONS
	 *
	 * <type>
	 * : Chart type identifier — one of "natal", "birth", or "now".
	 *
	 * [--name=<name>]
	 * : Subject display name. Required for natal/birth.
	 *
	 * [--date=<date>]
	 * : Birth date in YYYY-MM-DD.
	 *
	 * [--time=<time>]
	 * : Birth time in HH:MM.
	 *
	 * [--latitude=<lat>]
	 * : Birth location latitude.
	 *
	 * [--longitude=<lng>]
	 * : Birth location longitude.
	 *
	 * [--timezone=<tz>]
	 * : IANA timezone string.
	 *
	 * [--city=<city>]
	 * : Birth city name.
	 *
	 * [--nation=<code>]
	 * : ISO 3166-1 alpha-2 country code.
	 *
	 * [--format=<format>]
	 * : Output format. Accepted values: json, table. Default: json.
	 *
	 * ## EXAMPLES
	 *
	 *     wp astrologer chart now --format=table
	 *     wp astrologer chart natal --name="Jane" --date=1990-05-15 --time=12:30 \
	 *         --latitude=41.9 --longitude=12.5 --timezone=Europe/Rome
	 *
	 * @param list<string>         $args       Positional arguments — requires the chart type.
	 * @param array<string,string> $assoc_args Associative arguments from WP-CLI.
	 */
	public function __invoke( array $args, array $assoc_args ): void {
		$type   = isset( $args[0] ) ? strtolower( $args[0] ) : '';
		$format = strtolower( $assoc_args['format'] ?? 'json' );

		if ( '' === $type ) {
			\WP_CLI::error( 'Chart type is required. Try "natal", "birth", or "now".' );
		}

		if ( 'now' === $type ) {
			$dto      = NowRequestDTO::from_array( array() );
			$response = $this->chart_service->nowChart( $dto );
		} elseif ( in_array( $type, array( 'natal', 'birth' ), true ) ) {
			$dto      = $this->build_chart_request( $assoc_args );
			$response = $this->chart_service->birthChart( $dto );
		} else {
			\WP_CLI::error( sprintf( 'Unsupported chart type "%s".', $type ) );
		}

		if ( is_wp_error( $response ) ) {
			\WP_CLI::error( $response->get_error_message() );
		}

		$data = $response->to_array();

		if ( 'table' === $format ) {
			$this->render_table( $data );

			return;
		}

		\WP_CLI::log( (string) wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
	}

	/**
	 * Build a ChartRequestDTO from CLI arguments.
	 *
	 * @param array<string,string> $assoc_args Associative CLI arguments.
	 * @return ChartRequestDTO
	 */
	private function build_chart_request( array $assoc_args ): ChartRequestDTO {
		$date = $assoc_args['date'] ?? '';
		$time = $assoc_args['time'] ?? '00:00';

		$parts_date = explode( '-', (string) $date );
		$parts_time = explode( ':', (string) $time );

		$year  = isset( $parts_date[0] ) ? (int) $parts_date[0] : 2000;
		$month = isset( $parts_date[1] ) ? (int) $parts_date[1] : 1;
		$day   = isset( $parts_date[2] ) ? (int) $parts_date[2] : 1;
		$hour  = isset( $parts_time[0] ) ? (int) $parts_time[0] : 0;
		$min   = isset( $parts_time[1] ) ? (int) $parts_time[1] : 0;

		$location = new GeoLocation(
			latitude: (float) ( $assoc_args['latitude'] ?? 51.4769 ),
			longitude: (float) ( $assoc_args['longitude'] ?? 0.0005 ),
			timezone: (string) ( $assoc_args['timezone'] ?? 'UTC' ),
			altitude: null,
			is_dst: null,
			city: isset( $assoc_args['city'] ) ? (string) $assoc_args['city'] : null,
			nation: isset( $assoc_args['nation'] ) ? (string) $assoc_args['nation'] : null,
		);

		$name = (string) ( $assoc_args['name'] ?? 'CLI Subject' );

		if ( '' === $name ) {
			$name = 'CLI Subject';
		}

		$birth = new BirthData(
			name: $name,
			year: $year,
			month: $month,
			day: $day,
			hour: $hour,
			minute: $min,
			location: $location,
		);

		$subject = new SubjectDTO( birth_data: $birth );

		return new ChartRequestDTO(
			subject: $subject,
			options: ChartOptions::defaults(),
			type: ChartType::Natal,
			svg: false,
			ai_ctx: false,
		);
	}

	/**
	 * Render the chart response as a flat two-column table.
	 *
	 * @param array<string,mixed> $data Chart response in array form.
	 */
	private function render_table( array $data ): void {
		foreach ( $data as $key => $value ) {
			$display = is_scalar( $value ) ? (string) $value : wp_json_encode( $value );

			\WP_CLI::log( sprintf( '%-24s %s', (string) $key, (string) $display ) );
		}
	}
}
