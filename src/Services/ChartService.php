<?php
/**
 * ChartService — orchestrator for all upstream Astrologer API endpoints.
 *
 * Translates DTOs into API payloads via ApiClient, fires hooks, and wraps
 * responses into ChartResponseDTO instances. All downstream consumers (REST,
 * WP-CLI, Cron) call this service — never ApiClient directly.
 *
 * @package Astrologer\Api
 */

// phpcs:disable WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid -- Service methods use camelCase matching spec naming.

declare( strict_types = 1 );

namespace Astrologer\Api\Services;

use Astrologer\Api\DTO\ChartRequestDTO;
use Astrologer\Api\DTO\ChartResponseDTO;
use Astrologer\Api\DTO\CompatibilityRequestDTO;
use Astrologer\Api\DTO\CompositeRequestDTO;
use Astrologer\Api\DTO\MoonPhaseRequestDTO;
use Astrologer\Api\DTO\NowRequestDTO;
use Astrologer\Api\DTO\ReturnRequestDTO;
use Astrologer\Api\DTO\SubjectDTO;
use Astrologer\Api\DTO\SynastryRequestDTO;
use Astrologer\Api\DTO\TransitRequestDTO;
use Astrologer\Api\Http\ApiClient;
use WP_Error;

/**
 * Central service that maps every chart type to its upstream endpoint.
 *
 * Each method follows the same lifecycle:
 *   1. Fire `astrologer_api/before_chart_request` action.
 *   2. Apply `astrologer_api/chart_request_args` filter on payload.
 *   3. Call ApiClient POST/GET.
 *   4. On WP_Error fire `astrologer_api/chart_request_failed` and return.
 *   5. Fire `astrologer_api/after_chart_response` action.
 *   6. Apply `astrologer_api/chart_response` filter and return.
 */
final class ChartService {

	/**
	 * Upstream API endpoint paths.
	 *
	 * Centralised here so WP-CLI `doctor` and future tooling can introspect them.
	 */
	// phpcs:disable Generic.Formatting.MultipleStatementAlignment -- Constant block with varied name lengths.
	private const EP_SUBJECT = '/api/v5/subject';
	private const EP_NOW_SUBJECT = '/api/v5/now/subject';
	private const EP_BIRTH_CHART_DATA = '/api/v5/chart-data/birth-chart';
	private const EP_BIRTH_CHART = '/api/v5/chart/birth-chart';
	private const EP_SYNASTRY_CHART_DATA = '/api/v5/chart-data/synastry';
	private const EP_SYNASTRY_CHART = '/api/v5/chart/synastry';
	private const EP_COMPATIBILITY_SCORE = '/api/v5/compatibility-score';
	private const EP_TRANSIT_CHART_DATA = '/api/v5/chart-data/transit';
	private const EP_TRANSIT_CHART = '/api/v5/chart/transit';
	private const EP_COMPOSITE_CHART_DATA = '/api/v5/chart-data/composite';
	private const EP_COMPOSITE_CHART = '/api/v5/chart/composite';
	private const EP_SOLAR_RETURN_CHART_DATA = '/api/v5/chart-data/solar-return';
	private const EP_SOLAR_RETURN_CHART = '/api/v5/chart/solar-return';
	private const EP_LUNAR_RETURN_CHART_DATA = '/api/v5/chart-data/lunar-return';
	private const EP_LUNAR_RETURN_CHART = '/api/v5/chart/lunar-return';
	private const EP_NOW_CHART = '/api/v5/chart/now';
	private const EP_MOON_PHASE = '/api/v5/moon-phase';
	private const EP_MOON_PHASE_NOW_UTC = '/api/v5/moon-phase/now-utc';
	private const EP_EPHEMERIS = '/api/v5/ephemeris';
	private const EP_HEALTH = '/health';
	// phpcs:enable Generic.Formatting.MultipleStatementAlignment

	/** Context endpoint suffix appended to base chart paths. */
	private const CTX_SUFFIX = '/context';

	/**
	 * API client for HTTP communication with upstream.
	 *
	 * @var ApiClient
	 */
	private ApiClient $client;

	/**
	 * Constructor.
	 *
	 * @param ApiClient $client RapidAPI HTTP client instance.
	 */
	public function __construct( ApiClient $client ) {
		$this->client = $client;
	}

	// -------------------------------------------------------------------------
	// Subject endpoints
	// -------------------------------------------------------------------------

	/**
	 * Get subject data (planetary positions, houses) without a chart image.
	 *
	 * @param SubjectDTO $dto Subject birth data.
	 * @return ChartResponseDTO|WP_Error
	 */
	public function subject( SubjectDTO $dto ): ChartResponseDTO|WP_Error {
		return $this->call( 'subject', self::EP_SUBJECT, $dto->to_array() );
	}

	/**
	 * Get current moment subject data.
	 *
	 * @param NowRequestDTO $dto Current moment options.
	 * @return ChartResponseDTO|WP_Error
	 */
	public function nowSubject( NowRequestDTO $dto ): ChartResponseDTO|WP_Error {
		return $this->call( 'now_subject', self::EP_NOW_SUBJECT, $dto->to_array() );
	}

	// -------------------------------------------------------------------------
	// Birth / Natal chart
	// -------------------------------------------------------------------------

	/**
	 * Get birth chart data without SVG.
	 *
	 * @param ChartRequestDTO $dto Chart request.
	 * @return ChartResponseDTO|WP_Error
	 */
	public function birthChartData( ChartRequestDTO $dto ): ChartResponseDTO|WP_Error {
		return $this->call( 'birth_chart_data', self::EP_BIRTH_CHART_DATA, $dto->to_array() );
	}

	/**
	 * Get birth chart with optional SVG.
	 *
	 * @param ChartRequestDTO $dto Chart request.
	 * @return ChartResponseDTO|WP_Error
	 */
	public function birthChart( ChartRequestDTO $dto ): ChartResponseDTO|WP_Error {
		return $this->call( 'birth_chart', self::EP_BIRTH_CHART, $dto->to_array() );
	}

	// -------------------------------------------------------------------------
	// Synastry
	// -------------------------------------------------------------------------

	/**
	 * Get synastry chart data without SVG.
	 *
	 * @param SynastryRequestDTO $dto Synastry request.
	 * @return ChartResponseDTO|WP_Error
	 */
	public function synastryChartData( SynastryRequestDTO $dto ): ChartResponseDTO|WP_Error {
		return $this->call( 'synastry_chart_data', self::EP_SYNASTRY_CHART_DATA, $dto->to_array() );
	}

	/**
	 * Get synastry chart with optional SVG.
	 *
	 * @param SynastryRequestDTO $dto Synastry request.
	 * @return ChartResponseDTO|WP_Error
	 */
	public function synastryChart( SynastryRequestDTO $dto ): ChartResponseDTO|WP_Error {
		return $this->call( 'synastry_chart', self::EP_SYNASTRY_CHART, $dto->to_array() );
	}

	// -------------------------------------------------------------------------
	// Compatibility score
	// -------------------------------------------------------------------------

	/**
	 * Get Ciro Discepolo compatibility score between two subjects.
	 *
	 * @param CompatibilityRequestDTO $dto Compatibility request.
	 * @return ChartResponseDTO|WP_Error
	 */
	public function compatibilityScore( CompatibilityRequestDTO $dto ): ChartResponseDTO|WP_Error {
		return $this->call( 'compatibility_score', self::EP_COMPATIBILITY_SCORE, $dto->to_array() );
	}

	// -------------------------------------------------------------------------
	// Transit
	// -------------------------------------------------------------------------

	/**
	 * Get transit chart data without SVG.
	 *
	 * @param TransitRequestDTO $dto Transit request.
	 * @return ChartResponseDTO|WP_Error
	 */
	public function transitChartData( TransitRequestDTO $dto ): ChartResponseDTO|WP_Error {
		return $this->call( 'transit_chart_data', self::EP_TRANSIT_CHART_DATA, $dto->to_array() );
	}

	/**
	 * Get transit chart with optional SVG.
	 *
	 * @param TransitRequestDTO $dto Transit request.
	 * @return ChartResponseDTO|WP_Error
	 */
	public function transitChart( TransitRequestDTO $dto ): ChartResponseDTO|WP_Error {
		return $this->call( 'transit_chart', self::EP_TRANSIT_CHART, $dto->to_array() );
	}

	// -------------------------------------------------------------------------
	// Composite
	// -------------------------------------------------------------------------

	/**
	 * Get composite chart data without SVG.
	 *
	 * @param CompositeRequestDTO $dto Composite request.
	 * @return ChartResponseDTO|WP_Error
	 */
	public function compositeChartData( CompositeRequestDTO $dto ): ChartResponseDTO|WP_Error {
		return $this->call( 'composite_chart_data', self::EP_COMPOSITE_CHART_DATA, $dto->to_array() );
	}

	/**
	 * Get composite chart with optional SVG.
	 *
	 * @param CompositeRequestDTO $dto Composite request.
	 * @return ChartResponseDTO|WP_Error
	 */
	public function compositeChart( CompositeRequestDTO $dto ): ChartResponseDTO|WP_Error {
		return $this->call( 'composite_chart', self::EP_COMPOSITE_CHART, $dto->to_array() );
	}

	// -------------------------------------------------------------------------
	// Solar return
	// -------------------------------------------------------------------------

	/**
	 * Get solar return chart data without SVG.
	 *
	 * @param ReturnRequestDTO $dto Solar return request.
	 * @return ChartResponseDTO|WP_Error
	 */
	public function solarReturnChartData( ReturnRequestDTO $dto ): ChartResponseDTO|WP_Error {
		return $this->call( 'solar_return_chart_data', self::EP_SOLAR_RETURN_CHART_DATA, $dto->to_array() );
	}

	/**
	 * Get solar return chart with optional SVG.
	 *
	 * @param ReturnRequestDTO $dto Solar return request.
	 * @return ChartResponseDTO|WP_Error
	 */
	public function solarReturnChart( ReturnRequestDTO $dto ): ChartResponseDTO|WP_Error {
		return $this->call( 'solar_return_chart', self::EP_SOLAR_RETURN_CHART, $dto->to_array() );
	}

	// -------------------------------------------------------------------------
	// Lunar return
	// -------------------------------------------------------------------------

	/**
	 * Get lunar return chart data without SVG.
	 *
	 * @param ReturnRequestDTO $dto Lunar return request.
	 * @return ChartResponseDTO|WP_Error
	 */
	public function lunarReturnChartData( ReturnRequestDTO $dto ): ChartResponseDTO|WP_Error {
		return $this->call( 'lunar_return_chart_data', self::EP_LUNAR_RETURN_CHART_DATA, $dto->to_array() );
	}

	/**
	 * Get lunar return chart with optional SVG.
	 *
	 * @param ReturnRequestDTO $dto Lunar return request.
	 * @return ChartResponseDTO|WP_Error
	 */
	public function lunarReturnChart( ReturnRequestDTO $dto ): ChartResponseDTO|WP_Error {
		return $this->call( 'lunar_return_chart', self::EP_LUNAR_RETURN_CHART, $dto->to_array() );
	}

	// -------------------------------------------------------------------------
	// Now chart
	// -------------------------------------------------------------------------

	/**
	 * Get current moment chart with optional SVG.
	 *
	 * @param NowRequestDTO $dto Now chart options.
	 * @return ChartResponseDTO|WP_Error
	 */
	public function nowChart( NowRequestDTO $dto ): ChartResponseDTO|WP_Error {
		return $this->call( 'now_chart', self::EP_NOW_CHART, $dto->to_array() );
	}

	// -------------------------------------------------------------------------
	// Moon phase (4 routes)
	// -------------------------------------------------------------------------

	/**
	 * Get moon phase for a specific date/time/location.
	 *
	 * @param MoonPhaseRequestDTO $dto Moon phase request.
	 * @return ChartResponseDTO|WP_Error
	 */
	public function moonPhase( MoonPhaseRequestDTO $dto ): ChartResponseDTO|WP_Error {
		return $this->call( 'moon_phase', self::EP_MOON_PHASE, $dto->to_array() );
	}

	/**
	 * Get moon phase for a specific date/time/location with AI context.
	 *
	 * @param MoonPhaseRequestDTO $dto Moon phase request.
	 * @return ChartResponseDTO|WP_Error
	 */
	public function moonPhaseContext( MoonPhaseRequestDTO $dto ): ChartResponseDTO|WP_Error {
		return $this->call( 'moon_phase_context', self::EP_MOON_PHASE . self::CTX_SUFFIX, $dto->to_array() );
	}

	/**
	 * Get current UTC moon phase at Greenwich.
	 *
	 * @return ChartResponseDTO|WP_Error
	 */
	public function moonPhaseNowUtc(): ChartResponseDTO|WP_Error {
		return $this->call( 'moon_phase_now_utc', self::EP_MOON_PHASE_NOW_UTC, array() );
	}

	/**
	 * Get current UTC moon phase at Greenwich with AI context.
	 *
	 * @return ChartResponseDTO|WP_Error
	 */
	public function moonPhaseNowUtcContext(): ChartResponseDTO|WP_Error {
		return $this->call( 'moon_phase_now_utc_context', self::EP_MOON_PHASE_NOW_UTC . self::CTX_SUFFIX, array() );
	}

	/**
	 * Get moon phase data for a date range using the ephemeris endpoint.
	 *
	 * @param string $start_date Start date in ISO format (e.g. '2025-01-01').
	 * @param string $end_date   End date in ISO format.
	 * @param int    $step       Step interval in days.
	 * @param float  $latitude   Observer latitude.
	 * @param float  $longitude  Observer longitude.
	 * @param string $timezone   IANA timezone.
	 * @return ChartResponseDTO|WP_Error
	 */
	public function moonPhaseRange(
		string $start_date,
		string $end_date,
		int $step = 1,
		float $latitude = 51.4769,
		float $longitude = 0.0005,
		string $timezone = 'UTC'
	): ChartResponseDTO|WP_Error {
		return $this->call(
			'moon_phase_range',
			self::EP_EPHEMERIS,
			array(
				'start_date' => $start_date,
				'end_date'   => $end_date,
				'step'       => $step,
				'step_type'  => 'days',
				'latitude'   => $latitude,
				'longitude'  => $longitude,
				'timezone'   => $timezone,
			)
		);
	}

	/**
	 * Get the next occurrence of a specific moon phase.
	 *
	 * Fetches the current moon phase which includes upcoming phases,
	 * then extracts the requested phase from the response.
	 *
	 * @param string               $phase  Phase name (new, first-quarter, full, last-quarter).
	 * @return ChartResponseDTO|WP_Error
	 */
	public function moonPhaseNext( string $phase ): ChartResponseDTO|WP_Error {
		$result = $this->moonPhaseNowUtc();

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// The upstream response includes upcoming_phases with dates for each phase.
		$data = $result->to_array();

		/** @var array<string,mixed>|null $upcoming */
		$upcoming = $data['upcoming_phases'] ?? null;

		if ( is_array( $upcoming ) && isset( $upcoming[ $phase ] ) ) {
			return ChartResponseDTO::from_array(
				array(
					'phase'   => $phase,
					'date'    => $upcoming[ $phase ],
					'source'  => 'upcoming_phases',
					'context' => $data,
				)
			);
		}

		// Fallback: return the full response with a note about the missing phase.
		return ChartResponseDTO::from_array(
			array(
				'phase'   => $phase,
				'date'    => null,
				'message' => 'Requested phase not found in upcoming phases data.',
				'context' => $data,
			)
		);
	}

	// -------------------------------------------------------------------------
	// AI Context endpoints (8 routes)
	// -------------------------------------------------------------------------

	/**
	 * Get AI context for a subject.
	 *
	 * @param SubjectDTO $dto Subject birth data.
	 * @return ChartResponseDTO|WP_Error
	 */
	public function subjectContext( SubjectDTO $dto ): ChartResponseDTO|WP_Error {
		return $this->call( 'subject_context', self::EP_SUBJECT . self::CTX_SUFFIX, $dto->to_array() );
	}

	/**
	 * Get AI context for a birth chart.
	 *
	 * @param ChartRequestDTO $dto Chart request.
	 * @return ChartResponseDTO|WP_Error
	 */
	public function birthChartContext( ChartRequestDTO $dto ): ChartResponseDTO|WP_Error {
		return $this->call( 'birth_chart_context', self::EP_BIRTH_CHART . self::CTX_SUFFIX, $dto->to_array() );
	}

	/**
	 * Get AI context for a synastry chart.
	 *
	 * @param SynastryRequestDTO $dto Synastry request.
	 * @return ChartResponseDTO|WP_Error
	 */
	public function synastryContext( SynastryRequestDTO $dto ): ChartResponseDTO|WP_Error {
		return $this->call( 'synastry_context', self::EP_SYNASTRY_CHART . self::CTX_SUFFIX, $dto->to_array() );
	}

	/**
	 * Get AI context for a composite chart.
	 *
	 * @param CompositeRequestDTO $dto Composite request.
	 * @return ChartResponseDTO|WP_Error
	 */
	public function compositeContext( CompositeRequestDTO $dto ): ChartResponseDTO|WP_Error {
		return $this->call( 'composite_context', self::EP_COMPOSITE_CHART . self::CTX_SUFFIX, $dto->to_array() );
	}

	/**
	 * Get AI context for a transit chart.
	 *
	 * @param TransitRequestDTO $dto Transit request.
	 * @return ChartResponseDTO|WP_Error
	 */
	public function transitContext( TransitRequestDTO $dto ): ChartResponseDTO|WP_Error {
		return $this->call( 'transit_context', self::EP_TRANSIT_CHART . self::CTX_SUFFIX, $dto->to_array() );
	}

	/**
	 * Get AI context for a solar return chart.
	 *
	 * @param ReturnRequestDTO $dto Solar return request.
	 * @return ChartResponseDTO|WP_Error
	 */
	public function solarReturnContext( ReturnRequestDTO $dto ): ChartResponseDTO|WP_Error {
		return $this->call( 'solar_return_context', self::EP_SOLAR_RETURN_CHART . self::CTX_SUFFIX, $dto->to_array() );
	}

	/**
	 * Get AI context for a lunar return chart.
	 *
	 * @param ReturnRequestDTO $dto Lunar return request.
	 * @return ChartResponseDTO|WP_Error
	 */
	public function lunarReturnContext( ReturnRequestDTO $dto ): ChartResponseDTO|WP_Error {
		return $this->call( 'lunar_return_context', self::EP_LUNAR_RETURN_CHART . self::CTX_SUFFIX, $dto->to_array() );
	}

	/**
	 * Get AI context for a current moment chart.
	 *
	 * @param NowRequestDTO $dto Now chart options.
	 * @return ChartResponseDTO|WP_Error
	 */
	public function nowContext( NowRequestDTO $dto ): ChartResponseDTO|WP_Error {
		return $this->call( 'now_context', self::EP_NOW_CHART . self::CTX_SUFFIX, $dto->to_array() );
	}

	// -------------------------------------------------------------------------
	// MCP + Health
	// -------------------------------------------------------------------------

	/**
	 * Proxy a JSON-RPC 2.0 request to the upstream MCP endpoint.
	 *
	 * @param array<string,mixed> $payload JSON-RPC 2.0 request payload.
	 * @return array<string,mixed>|WP_Error
	 */
	public function mcp( array $payload ): array|WP_Error {
		// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores -- Project convention.
		do_action( 'astrologer_api/before_chart_request', 'mcp', $payload );

		/** @var array<string,mixed> $filtered */
		// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores -- Project convention.
		$filtered = apply_filters( 'astrologer_api/chart_request_args', $payload, 'mcp' );

		$response = $this->client->post( '/mcp', $filtered );

		if ( is_wp_error( $response ) ) {
			// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores -- Project convention.
			do_action( 'astrologer_api/chart_request_failed', 'mcp', $response );

			return $response;
		}

		// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores -- Project convention.
		do_action( 'astrologer_api/after_http_response', '/mcp', $response );

		return $response;
	}

	/**
	 * Check upstream API health.
	 *
	 * @return array<string,mixed>|WP_Error
	 */
	public function health(): array|WP_Error {
		return $this->client->get( self::EP_HEALTH );
	}

	// -------------------------------------------------------------------------
	// Internal: unified call pipeline
	// -------------------------------------------------------------------------

	/**
	 * Execute a chart API call through the full hook lifecycle.
	 *
	 * @param string               $chart_type Chart type identifier for hooks.
	 * @param string               $endpoint   Upstream API endpoint path.
	 * @param array<string,mixed>  $payload    Request payload.
	 * @return ChartResponseDTO|WP_Error
	 */
	private function call( string $chart_type, string $endpoint, array $payload ): ChartResponseDTO|WP_Error {
		// 1. Fire before action.
		// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores -- Project convention.
		do_action( 'astrologer_api/before_chart_request', $chart_type, $payload );

		// 2. Apply request filter.
		/** @var array<string,mixed> $filtered */
		// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores -- Project convention.
		$filtered = apply_filters( 'astrologer_api/chart_request_args', $payload, $chart_type );

		// 3. Call upstream.
		$response = $this->client->post( $endpoint, $filtered );

		// 4. Error path.
		if ( is_wp_error( $response ) ) {
			// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores -- Project convention.
			do_action( 'astrologer_api/chart_request_failed', $chart_type, $response );

			return $response;
		}

		// 5. Wrap into DTO.
		$dto = ChartResponseDTO::from_array( $response );

		// 6. Fire after action.
		// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores -- Project convention.
		do_action( 'astrologer_api/after_chart_response', $chart_type, $dto );

		// 7. Apply response filter.
		/** @var ChartResponseDTO $filtered_dto */
		// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores -- Project convention.
		$filtered_dto = apply_filters( 'astrologer_api/chart_response', $dto, $chart_type );

		return $filtered_dto;
	}
}
