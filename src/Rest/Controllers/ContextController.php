<?php
/**
 * ContextController — REST endpoints for AI context generation.
 *
 * Provides eight routes that return structured textual context for LLM
 * consumption. Each route mirrors a chart endpoint but returns context
 * instead of chart data.
 *
 * Routes:
 *   POST /context/subject
 *   POST /context/natal
 *   POST /context/synastry
 *   POST /context/transit
 *   POST /context/composite
 *   POST /context/solar-return
 *   POST /context/lunar-return
 *   POST /context/moon-phase
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Rest\Controllers;

use Astrologer\Api\DTO\ChartRequestDTO;
use Astrologer\Api\DTO\CompositeRequestDTO;
use Astrologer\Api\DTO\MoonPhaseRequestDTO;
use Astrologer\Api\DTO\NowRequestDTO;
use Astrologer\Api\DTO\ReturnRequestDTO;
use Astrologer\Api\DTO\SubjectDTO;
use Astrologer\Api\DTO\SynastryRequestDTO;
use Astrologer\Api\DTO\TransitRequestDTO;
use Astrologer\Api\Rest\AbstractController;
use Astrologer\Api\Rest\Schemas\ChartOptionsSchema;
use Astrologer\Api\Rest\Schemas\GeoLocationSchema;
use Astrologer\Api\Rest\Schemas\SubjectSchema;
use Astrologer\Api\Services\ChartService;
use Astrologer\Api\Services\RateLimiter;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Handles all AI context REST routes under /astrologer/v1/context.
 *
 * Each handler delegates to the corresponding *Context() method on ChartService,
 * which appends the /context suffix to the upstream endpoint path.
 */
final class ContextController extends AbstractController {

	/**
	 * Chart service instance.
	 *
	 * @var ChartService
	 */
	private ChartService $chart_service;

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
	 * Register the eight AI context routes.
	 */
	public function register_routes(): void {
		// POST /context/subject — AI context for a subject (positions only).
		register_rest_route(
			self::NAMESPACE,
			'/context/subject',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'handle_subject' ),
					'permission_callback' => array( $this, 'default_permission_callback' ),
					'args'                => $this->get_subject_args(),
				),
			)
		);

		// POST /context/natal — AI context for a natal chart.
		register_rest_route(
			self::NAMESPACE,
			'/context/natal',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'handle_natal' ),
					'permission_callback' => array( $this, 'default_permission_callback' ),
					'args'                => $this->get_single_subject_args(),
				),
			)
		);

		// POST /context/synastry — AI context for a synastry chart.
		register_rest_route(
			self::NAMESPACE,
			'/context/synastry',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'handle_synastry' ),
					'permission_callback' => array( $this, 'default_permission_callback' ),
					'args'                => $this->get_dual_subject_args(),
				),
			)
		);

		// POST /context/transit — AI context for a transit chart.
		register_rest_route(
			self::NAMESPACE,
			'/context/transit',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'handle_transit' ),
					'permission_callback' => array( $this, 'default_permission_callback' ),
					'args'                => $this->get_transit_args(),
				),
			)
		);

		// POST /context/composite — AI context for a composite chart.
		register_rest_route(
			self::NAMESPACE,
			'/context/composite',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'handle_composite' ),
					'permission_callback' => array( $this, 'default_permission_callback' ),
					'args'                => $this->get_composite_args(),
				),
			)
		);

		// POST /context/solar-return — AI context for a solar return chart.
		register_rest_route(
			self::NAMESPACE,
			'/context/solar-return',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'handle_solar_return' ),
					'permission_callback' => array( $this, 'default_permission_callback' ),
					'args'                => $this->get_return_args(),
				),
			)
		);

		// POST /context/lunar-return — AI context for a lunar return chart.
		register_rest_route(
			self::NAMESPACE,
			'/context/lunar-return',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'handle_lunar_return' ),
					'permission_callback' => array( $this, 'default_permission_callback' ),
					'args'                => $this->get_return_args(),
				),
			)
		);

		// POST /context/moon-phase — AI context for a moon phase.
		register_rest_route(
			self::NAMESPACE,
			'/context/moon-phase',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'handle_moon_phase' ),
					'permission_callback' => array( $this, 'default_permission_callback' ),
					'args'                => $this->get_moon_phase_args(),
				),
			)
		);
	}

	// -------------------------------------------------------------------------
	// Handlers
	// -------------------------------------------------------------------------

	/**
	 * Handle POST /context/subject.
	 *
	 * Returns AI context for a single subject (planetary positions, houses).
	 *
	 * @param WP_REST_Request $request The incoming request.
	 * @return WP_REST_Response
	 */
	public function handle_subject( WP_REST_Request $request ): WP_REST_Response {
		$params = $this->resolve_params( $request );
		$dto    = SubjectDTO::from_array( $params );

		$result = $this->chart_service->subjectContext( $dto );

		if ( is_wp_error( $result ) ) {
			return $this->handle_service_error( $result );
		}

		/** @var \Astrologer\Api\DTO\ChartResponseDTO $result */
		return $this->respond( $result->to_array() );
	}

	/**
	 * Handle POST /context/natal.
	 *
	 * Returns AI context for a natal chart.
	 *
	 * @param WP_REST_Request $request The incoming request.
	 * @return WP_REST_Response
	 */
	public function handle_natal( WP_REST_Request $request ): WP_REST_Response {
		$params = $this->resolve_params( $request );
		$dto    = ChartRequestDTO::from_array( $params );

		$result = $this->chart_service->birthChartContext( $dto );

		if ( is_wp_error( $result ) ) {
			return $this->handle_service_error( $result );
		}

		/** @var \Astrologer\Api\DTO\ChartResponseDTO $result */
		return $this->respond( $result->to_array() );
	}

	/**
	 * Handle POST /context/synastry.
	 *
	 * Returns AI context for a synastry chart.
	 *
	 * @param WP_REST_Request $request The incoming request.
	 * @return WP_REST_Response
	 */
	public function handle_synastry( WP_REST_Request $request ): WP_REST_Response {
		$params = $this->resolve_params( $request );
		$dto    = SynastryRequestDTO::from_array( $params );

		$result = $this->chart_service->synastryContext( $dto );

		if ( is_wp_error( $result ) ) {
			return $this->handle_service_error( $result );
		}

		/** @var \Astrologer\Api\DTO\ChartResponseDTO $result */
		return $this->respond( $result->to_array() );
	}

	/**
	 * Handle POST /context/transit.
	 *
	 * Returns AI context for a transit chart.
	 *
	 * @param WP_REST_Request $request The incoming request.
	 * @return WP_REST_Response
	 */
	public function handle_transit( WP_REST_Request $request ): WP_REST_Response {
		$params = $this->resolve_params( $request );
		$dto    = TransitRequestDTO::from_array( $params );

		$result = $this->chart_service->transitContext( $dto );

		if ( is_wp_error( $result ) ) {
			return $this->handle_service_error( $result );
		}

		/** @var \Astrologer\Api\DTO\ChartResponseDTO $result */
		return $this->respond( $result->to_array() );
	}

	/**
	 * Handle POST /context/composite.
	 *
	 * Returns AI context for a composite chart.
	 *
	 * @param WP_REST_Request $request The incoming request.
	 * @return WP_REST_Response
	 */
	public function handle_composite( WP_REST_Request $request ): WP_REST_Response {
		$params = $this->resolve_params( $request );
		$dto    = CompositeRequestDTO::from_array( $params );

		$result = $this->chart_service->compositeContext( $dto );

		if ( is_wp_error( $result ) ) {
			return $this->handle_service_error( $result );
		}

		/** @var \Astrologer\Api\DTO\ChartResponseDTO $result */
		return $this->respond( $result->to_array() );
	}

	/**
	 * Handle POST /context/solar-return.
	 *
	 * Returns AI context for a solar return chart.
	 *
	 * @param WP_REST_Request $request The incoming request.
	 * @return WP_REST_Response
	 */
	public function handle_solar_return( WP_REST_Request $request ): WP_REST_Response {
		$params = $this->resolve_params( $request );
		$dto    = ReturnRequestDTO::from_array( $params );

		$result = $this->chart_service->solarReturnContext( $dto );

		if ( is_wp_error( $result ) ) {
			return $this->handle_service_error( $result );
		}

		/** @var \Astrologer\Api\DTO\ChartResponseDTO $result */
		return $this->respond( $result->to_array() );
	}

	/**
	 * Handle POST /context/lunar-return.
	 *
	 * Returns AI context for a lunar return chart.
	 *
	 * @param WP_REST_Request $request The incoming request.
	 * @return WP_REST_Response
	 */
	public function handle_lunar_return( WP_REST_Request $request ): WP_REST_Response {
		$params = $this->resolve_params( $request );
		$dto    = ReturnRequestDTO::from_array( $params );

		$result = $this->chart_service->lunarReturnContext( $dto );

		if ( is_wp_error( $result ) ) {
			return $this->handle_service_error( $result );
		}

		/** @var \Astrologer\Api\DTO\ChartResponseDTO $result */
		return $this->respond( $result->to_array() );
	}

	/**
	 * Handle POST /context/moon-phase.
	 *
	 * Returns AI context for a moon phase at a specific date/time/location.
	 *
	 * @param WP_REST_Request $request The incoming request.
	 * @return WP_REST_Response
	 */
	public function handle_moon_phase( WP_REST_Request $request ): WP_REST_Response {
		$params = $this->resolve_params( $request );
		$dto    = MoonPhaseRequestDTO::from_array( $params );

		$result = $this->chart_service->moonPhaseContext( $dto );

		if ( is_wp_error( $result ) ) {
			return $this->handle_service_error( $result );
		}

		/** @var \Astrologer\Api\DTO\ChartResponseDTO $result */
		return $this->respond( $result->to_array() );
	}

	// -------------------------------------------------------------------------
	// Schema helpers
	// -------------------------------------------------------------------------

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
	 * Get args for the subject-only context route (no chart options).
	 *
	 * @return array<string,array<string,mixed>>
	 */
	private function get_subject_args(): array {
		$subject_schema = SubjectSchema::get();

		$extra = array(
			'name'      => array(
				'description'       => __( 'Subject display name.', 'astrologer-api' ),
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			),
			'year'      => array(
				'description'       => __( 'Birth year.', 'astrologer-api' ),
				'type'              => 'integer',
				'required'          => true,
				'minimum'           => -13200,
				'maximum'           => 3000,
				'sanitize_callback' => 'intval',
			),
			'month'     => array(
				'description'       => __( 'Birth month (1-12).', 'astrologer-api' ),
				'type'              => 'integer',
				'required'          => true,
				'minimum'           => 1,
				'maximum'           => 12,
				'sanitize_callback' => 'absint',
			),
			'day'       => array(
				'description'       => __( 'Birth day (1-31).', 'astrologer-api' ),
				'type'              => 'integer',
				'required'          => true,
				'minimum'           => 1,
				'maximum'           => 31,
				'sanitize_callback' => 'absint',
			),
			'hour'      => array(
				'description'       => __( 'Birth hour (0-23).', 'astrologer-api' ),
				'type'              => 'integer',
				'required'          => true,
				'minimum'           => 0,
				'maximum'           => 23,
				'sanitize_callback' => 'absint',
			),
			'minute'    => array(
				'description'       => __( 'Birth minute (0-59).', 'astrologer-api' ),
				'type'              => 'integer',
				'required'          => true,
				'minimum'           => 0,
				'maximum'           => 59,
				'sanitize_callback' => 'absint',
			),
			'city'      => array(
				'description'       => __( 'City name.', 'astrologer-api' ),
				'type'              => 'string',
				'default'           => 'Greenwich',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'nation'    => array(
				'description'       => __( 'ISO 3166-1 alpha-2 country code.', 'astrologer-api' ),
				'type'              => 'string',
				'default'           => 'GB',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'latitude'  => array(
				'description'       => __( 'Latitude (-90 to 90).', 'astrologer-api' ),
				'type'              => 'number',
				'required'          => true,
				'sanitize_callback' => 'floatval',
			),
			'longitude' => array(
				'description'       => __( 'Longitude (-180 to 180).', 'astrologer-api' ),
				'type'              => 'number',
				'required'          => true,
				'sanitize_callback' => 'floatval',
			),
			'timezone'  => array(
				'description'       => __( 'IANA timezone.', 'astrologer-api' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
		);

		return array_merge( $extra, $subject_schema );
	}

	/**
	 * Get args for single-subject chart context routes (natal, now).
	 *
	 * @return array<string,array<string,mixed>>
	 */
	private function get_single_subject_args(): array {
		$options = ChartOptionsSchema::get();

		$extra = array(
			'subject' => array(
				'description'       => __( 'Birth data for the subject.', 'astrologer-api' ),
				'type'              => 'object',
				'required'          => true,
				'validate_callback' => static function ( mixed $value ): bool {
					return is_array( $value ) && ! empty( $value );
				},
			),
			'options' => array(
				'description' => __( 'Chart rendering options.', 'astrologer-api' ),
				'type'        => 'object',
			),
		);

		return array_merge( $extra, $options );
	}

	/**
	 * Get args for dual-subject chart context routes (synastry).
	 *
	 * @return array<string,array<string,mixed>>
	 */
	private function get_dual_subject_args(): array {
		$options = ChartOptionsSchema::get();

		$extra = array(
			'first_subject'  => array(
				'description'       => __( 'First (natal) subject birth data.', 'astrologer-api' ),
				'type'              => 'object',
				'required'          => true,
				'validate_callback' => static function ( mixed $value ): bool {
					return is_array( $value ) && ! empty( $value );
				},
			),
			'second_subject' => array(
				'description'       => __( 'Second (partner) subject birth data.', 'astrologer-api' ),
				'type'              => 'object',
				'required'          => true,
				'validate_callback' => static function ( mixed $value ): bool {
					return is_array( $value ) && ! empty( $value );
				},
			),
			'options'        => array(
				'description' => __( 'Chart rendering options.', 'astrologer-api' ),
				'type'        => 'object',
			),
		);

		return array_merge( $extra, $options );
	}

	/**
	 * Get args for transit context route (first_subject + transit_subject).
	 *
	 * @return array<string,array<string,mixed>>
	 */
	private function get_transit_args(): array {
		$options = ChartOptionsSchema::get();

		$extra = array(
			'first_subject'   => array(
				'description'       => __( 'Natal (birth) chart subject.', 'astrologer-api' ),
				'type'              => 'object',
				'required'          => true,
				'validate_callback' => static function ( mixed $value ): bool {
					return is_array( $value ) && ! empty( $value );
				},
			),
			'transit_subject' => array(
				'description'       => __( 'Transit moment subject.', 'astrologer-api' ),
				'type'              => 'object',
				'required'          => true,
				'validate_callback' => static function ( mixed $value ): bool {
					return is_array( $value ) && ! empty( $value );
				},
			),
			'options'         => array(
				'description' => __( 'Chart rendering options.', 'astrologer-api' ),
				'type'        => 'object',
			),
		);

		return array_merge( $extra, $options );
	}

	/**
	 * Get args for composite context route (first_subject + second_subject + composite_type).
	 *
	 * @return array<string,array<string,mixed>>
	 */
	private function get_composite_args(): array {
		$options = ChartOptionsSchema::get();

		$extra = array(
			'first_subject'  => array(
				'description'       => __( 'First subject birth data.', 'astrologer-api' ),
				'type'              => 'object',
				'required'          => true,
				'validate_callback' => static function ( mixed $value ): bool {
					return is_array( $value ) && ! empty( $value );
				},
			),
			'second_subject' => array(
				'description'       => __( 'Second subject birth data.', 'astrologer-api' ),
				'type'              => 'object',
				'required'          => true,
				'validate_callback' => static function ( mixed $value ): bool {
					return is_array( $value ) && ! empty( $value );
				},
			),
			'composite_type' => array(
				'description'       => __( 'Composite method: Midpoint or Davison.', 'astrologer-api' ),
				'type'              => 'string',
				'default'           => 'Midpoint',
				'enum'              => array( 'Midpoint', 'Davison' ),
				'sanitize_callback' => 'sanitize_text_field',
			),
			'options'        => array(
				'description' => __( 'Chart rendering options.', 'astrologer-api' ),
				'type'        => 'object',
			),
		);

		return array_merge( $extra, $options );
	}

	/**
	 * Get args for solar/lunar return context routes.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	private function get_return_args(): array {
		$options = ChartOptionsSchema::get();

		$extra = array(
			'subject'         => array(
				'description'       => __( 'Natal subject birth data.', 'astrologer-api' ),
				'type'              => 'object',
				'required'          => true,
				'validate_callback' => static function ( mixed $value ): bool {
					return is_array( $value ) && ! empty( $value );
				},
			),
			'year'            => array(
				'description'       => __( 'Target year for the return.', 'astrologer-api' ),
				'type'              => 'integer',
				'required'          => true,
				'minimum'           => 1,
				'maximum'           => 3000,
				'sanitize_callback' => 'absint',
			),
			'month'           => array(
				'description'       => __( 'Optional month to narrow search window.', 'astrologer-api' ),
				'type'              => 'integer',
				'minimum'           => 1,
				'maximum'           => 12,
				'sanitize_callback' => 'absint',
			),
			'day'             => array(
				'description'       => __( 'Optional day to narrow search window.', 'astrologer-api' ),
				'type'              => 'integer',
				'minimum'           => 1,
				'maximum'           => 31,
				'sanitize_callback' => 'absint',
			),
			'iso_datetime'    => array(
				'description'       => __( 'Precise ISO datetime for search start.', 'astrologer-api' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'wheel_type'      => array(
				'description'       => __( 'Display type: dual (natal + return) or single.', 'astrologer-api' ),
				'type'              => 'string',
				'default'           => 'dual',
				'enum'              => array( 'dual', 'single' ),
				'sanitize_callback' => 'sanitize_text_field',
			),
			'return_location' => array(
				'description' => __( 'Relocated return location override.', 'astrologer-api' ),
				'type'        => 'object',
			),
			'options'         => array(
				'description' => __( 'Chart rendering options.', 'astrologer-api' ),
				'type'        => 'object',
			),
		);

		return array_merge( $extra, $options );
	}

	/**
	 * Get args for moon-phase context route.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	private function get_moon_phase_args(): array {
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
		);

		return array_merge( $date_time, $geo );
	}
}
