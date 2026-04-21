<?php
/**
 * MoonPhaseController — REST endpoints for moon phase calculations.
 *
 * Provides four routes:
 *   GET  /moon-phase/current        — current moon phase (proxied to upstream).
 *   POST /moon-phase/at             — moon phase at a specific date/time/location.
 *   POST /moon-phase/range          — moon phases over a date range (ephemeris).
 *   GET  /moon-phase/next/(?P<phase>[\w-]+) — next occurrence of a specific phase.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Rest\Controllers;

use Astrologer\Api\DTO\MoonPhaseRequestDTO;
use Astrologer\Api\Rest\AbstractController;
use Astrologer\Api\Rest\Schemas\GeoLocationSchema;
use Astrologer\Api\Services\ChartService;
use Astrologer\Api\Services\RateLimiter;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Handles all moon-phase REST routes under /astrologer/v1/moon-phase.
 */
final class MoonPhaseController extends AbstractController {

	/**
	 * Chart service instance.
	 *
	 * @var ChartService
	 */
	private ChartService $chart_service;

	/**
	 * Valid moon phase names for the /next/{phase} endpoint.
	 *
	 * @var string[]
	 */
	private const VALID_PHASES = array(
		'new',
		'first-quarter',
		'full',
		'last-quarter',
	);

	/**
	 * Constructor.
	 *
	 * @param ChartService $chart_service Chart calculation service.
	 * @param RateLimiter  $rate_limiter  Rate limiting service.
	 */
	public function __construct( ChartService $chart_service, RateLimiter $rate_limiter ) {
		parent::__construct( $rate_limiter );
		$this->chart_service = $chart_service;
	}

	/**
	 * Register the moon phase routes.
	 */
	public function register_routes(): void {
		// GET /moon-phase/current — current moon phase.
		register_rest_route(
			self::NAMESPACE,
			'/moon-phase/current',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'handle_current' ),
					'permission_callback' => array( $this, 'default_permission_callback' ),
				),
			)
		);

		// POST /moon-phase/at — moon phase at a specific date/time/location.
		register_rest_route(
			self::NAMESPACE,
			'/moon-phase/at',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'handle_at' ),
					'permission_callback' => array( $this, 'default_permission_callback' ),
					'args'                => $this->get_at_args(),
				),
			)
		);

		// POST /moon-phase/range — moon phases over a date range.
		register_rest_route(
			self::NAMESPACE,
			'/moon-phase/range',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'handle_range' ),
					'permission_callback' => array( $this, 'default_permission_callback' ),
					'args'                => $this->get_range_args(),
				),
			)
		);

		// GET /moon-phase/next/{phase} — next occurrence of a specific phase.
		register_rest_route(
			self::NAMESPACE,
			'/moon-phase/next/(?P<phase>[\w-]+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'handle_next' ),
					'permission_callback' => array( $this, 'default_permission_callback' ),
					'args'                => array(
						'phase' => array(
							'description'       => __( 'Moon phase name (new, first-quarter, full, last-quarter).', 'astrologer-api' ),
							'type'              => 'string',
							'required'          => true,
							'sanitize_callback' => 'sanitize_text_field',
							'validate_callback' => static function ( mixed $value ): bool {
								return is_string( $value ) && in_array( $value, self::VALID_PHASES, true );
							},
						),
					),
				),
			)
		);
	}

	/**
	 * Handle GET /moon-phase/current.
	 *
	 * Returns the current moon phase from the upstream now-utc endpoint.
	 *
	 * @param WP_REST_Request $request The incoming request.
	 * @return WP_REST_Response
	 */
	public function handle_current( WP_REST_Request $request ): WP_REST_Response {
		$result = $this->chart_service->moonPhaseNowUtc();

		if ( is_wp_error( $result ) ) {
			return $this->handle_service_error( $result );
		}

		/** @var \Astrologer\Api\DTO\ChartResponseDTO $result */
		return $this->respond( $result->to_array() );
	}

	/**
	 * Handle POST /moon-phase/at.
	 *
	 * Returns the moon phase at a specific date, time, and location.
	 *
	 * @param WP_REST_Request $request The incoming request.
	 * @return WP_REST_Response
	 */
	public function handle_at( WP_REST_Request $request ): WP_REST_Response {
		$params = $this->resolve_params( $request );
		$dto    = MoonPhaseRequestDTO::from_array( $params );

		$result = $this->chart_service->moonPhase( $dto );

		if ( is_wp_error( $result ) ) {
			return $this->handle_service_error( $result );
		}

		/** @var \Astrologer\Api\DTO\ChartResponseDTO $result */
		return $this->respond( $result->to_array() );
	}

	/**
	 * Handle POST /moon-phase/range.
	 *
	 * Returns moon phase data for each day in the requested date range.
	 * Uses the ephemeris endpoint to get daily positions.
	 *
	 * @param WP_REST_Request $request The incoming request.
	 * @return WP_REST_Response
	 */
	public function handle_range( WP_REST_Request $request ): WP_REST_Response {
		$params = $this->resolve_params( $request );

		$start_date = $params['start_date'] ?? '';
		$end_date   = $params['end_date'] ?? '';

		if ( '' === $start_date || '' === $end_date ) {
			return $this->respond(
				array(
					'code'    => 'rest_invalid_param',
					'message' => __( 'Both start_date and end_date are required.', 'astrologer-api' ),
				),
				400
			);
		}

		$result = $this->chart_service->moonPhaseRange(
			$start_date,
			$end_date,
			(int) ( $params['step'] ?? 1 ),
			(float) ( $params['latitude'] ?? 51.4769 ),
			(float) ( $params['longitude'] ?? 0.0005 ),
			(string) ( $params['timezone'] ?? 'UTC' )
		);

		if ( is_wp_error( $result ) ) {
			return $this->handle_service_error( $result );
		}

		/** @var \Astrologer\Api\DTO\ChartResponseDTO $result */
		return $this->respond( $result->to_array() );
	}

	/**
	 * Handle GET /moon-phase/next/{phase}.
	 *
	 * Returns data about the next occurrence of a specific moon phase.
	 *
	 * @param WP_REST_Request $request The incoming request.
	 * @return WP_REST_Response
	 */
	public function handle_next( WP_REST_Request $request ): WP_REST_Response {
		$phase = $request->get_param( 'phase' );

		$result = $this->chart_service->moonPhaseNext( $phase );

		if ( is_wp_error( $result ) ) {
			return $this->handle_service_error( $result );
		}

		/** @var \Astrologer\Api\DTO\ChartResponseDTO $result */
		return $this->respond( $result->to_array() );
	}

	/**
	 * Resolve parameters from either JSON body or POST fields.
	 *
	 * @param WP_REST_Request $request The incoming request.
	 * @return array<string,mixed>
	 */
	private function resolve_params( WP_REST_Request $request ): array {
		$json = $request->get_json_params();
		if ( is_array( $json ) && ! empty( $json ) ) {
			return $json;
		}
		return $request->get_params();
	}

	/**
	 * Get the argument schema for POST /moon-phase/at.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	private function get_at_args(): array {
		$geo = GeoLocationSchema::get();

		$date_time = array(
			'year'   => array(
				'description'       => __( 'Year for the calculation.', 'astrologer-api' ),
				'type'              => 'integer',
				'required'          => true,
				'minimum'           => 1,
				'maximum'           => 3000,
				'sanitize_callback' => 'absint',
				'validate_callback' => static function ( mixed $value ): bool {
					return is_numeric( $value ) && (int) $value >= 1 && (int) $value <= 3000;
				},
			),
			'month'  => array(
				'description'       => __( 'Month (1-12).', 'astrologer-api' ),
				'type'              => 'integer',
				'required'          => true,
				'minimum'           => 1,
				'maximum'           => 12,
				'sanitize_callback' => 'absint',
				'validate_callback' => static function ( mixed $value ): bool {
					return is_numeric( $value ) && (int) $value >= 1 && (int) $value <= 12;
				},
			),
			'day'    => array(
				'description'       => __( 'Day (1-31).', 'astrologer-api' ),
				'type'              => 'integer',
				'required'          => true,
				'minimum'           => 1,
				'maximum'           => 31,
				'sanitize_callback' => 'absint',
				'validate_callback' => static function ( mixed $value ): bool {
					return is_numeric( $value ) && (int) $value >= 1 && (int) $value <= 31;
				},
			),
			'hour'   => array(
				'description'       => __( 'Hour (0-23).', 'astrologer-api' ),
				'type'              => 'integer',
				'required'          => true,
				'minimum'           => 0,
				'maximum'           => 23,
				'sanitize_callback' => 'absint',
				'validate_callback' => static function ( mixed $value ): bool {
					return is_numeric( $value ) && (int) $value >= 0 && (int) $value <= 23;
				},
			),
			'minute' => array(
				'description'       => __( 'Minute (0-59).', 'astrologer-api' ),
				'type'              => 'integer',
				'required'          => true,
				'minimum'           => 0,
				'maximum'           => 59,
				'sanitize_callback' => 'absint',
				'validate_callback' => static function ( mixed $value ): bool {
					return is_numeric( $value ) && (int) $value >= 0 && (int) $value <= 59;
				},
			),
			'second' => array(
				'description'       => __( 'Second (0-59).', 'astrologer-api' ),
				'type'              => 'integer',
				'default'           => 0,
				'minimum'           => 0,
				'maximum'           => 59,
				'sanitize_callback' => 'absint',
			),
			'ai_ctx' => array(
				'description'       => __( 'Whether to include AI context text.', 'astrologer-api' ),
				'type'              => 'boolean',
				'default'           => false,
				'sanitize_callback' => static fn ( mixed $v ): bool => (bool) $v,
			),
		);

		return array_merge( $date_time, $geo );
	}

	/**
	 * Get the argument schema for POST /moon-phase/range.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	private function get_range_args(): array {
		return array(
			'start_date' => array(
				'description'       => __( 'Start date in ISO format (e.g. 2025-01-01).', 'astrologer-api' ),
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => static function ( mixed $value ): bool {
					return is_string( $value ) && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value );
				},
			),
			'end_date'   => array(
				'description'       => __( 'End date in ISO format (e.g. 2025-01-31).', 'astrologer-api' ),
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => static function ( mixed $value ): bool {
					return is_string( $value ) && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value );
				},
			),
			'step'       => array(
				'description'       => __( 'Step interval in days (1-30).', 'astrologer-api' ),
				'type'              => 'integer',
				'default'           => 1,
				'minimum'           => 1,
				'maximum'           => 30,
				'sanitize_callback' => 'absint',
			),
			'latitude'   => array(
				'description'       => __( 'Observer latitude (-90 to 90).', 'astrologer-api' ),
				'type'              => 'number',
				'default'           => 51.4769,
				'minimum'           => -90.0,
				'maximum'           => 90.0,
				'sanitize_callback' => 'floatval',
			),
			'longitude'  => array(
				'description'       => __( 'Observer longitude (-180 to 180).', 'astrologer-api' ),
				'type'              => 'number',
				'default'           => 0.0005,
				'minimum'           => -180.0,
				'maximum'           => 180.0,
				'sanitize_callback' => 'floatval',
			),
			'timezone'   => array(
				'description'       => __( 'IANA timezone identifier.', 'astrologer-api' ),
				'type'              => 'string',
				'default'           => 'UTC',
				'sanitize_callback' => 'sanitize_text_field',
			),
		);
	}
}
