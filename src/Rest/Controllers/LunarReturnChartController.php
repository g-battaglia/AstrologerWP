<?php
/**
 * LunarReturnChartController — REST endpoint for lunar return chart calculations.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Rest\Controllers;

use Astrologer\Api\DTO\ReturnRequestDTO;
use Astrologer\Api\Rest\AbstractController;
use Astrologer\Api\Rest\Schemas\ChartOptionsSchema;
use Astrologer\Api\Services\ChartService;
use Astrologer\Api\Services\RateLimiter;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Handles POST /astrologer/v1/lunar-return-chart.
 *
 * Calculates the chart for when the transiting Moon returns to its exact
 * natal longitude. Month is required to narrow the search window.
 */
final class LunarReturnChartController extends AbstractController {

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
	 * Register the lunar return chart route.
	 */
	public function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			'/lunar-return-chart',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'handle' ),
					'permission_callback' => array( $this, 'default_permission_callback' ),
					'args'                => $this->get_args(),
				),
			)
		);
	}

	/**
	 * Handle the lunar return chart request.
	 *
	 * @param WP_REST_Request $request The incoming request.
	 * @return WP_REST_Response
	 */
	public function handle( WP_REST_Request $request ): WP_REST_Response {
		$params = $this->resolve_params( $request );
		$dto    = ReturnRequestDTO::from_array( $params );

		$result = $this->chart_service->lunarReturnChart( $dto );

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
	 * Get the argument schema for this endpoint.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	private function get_args(): array {
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
				'description'       => __( 'Target year for the lunar return.', 'astrologer-api' ),
				'type'              => 'integer',
				'required'          => true,
				'minimum'           => 1,
				'maximum'           => 3000,
				'sanitize_callback' => 'absint',
			),
			'month'           => array(
				'description'       => __( 'Target month (required for lunar returns).', 'astrologer-api' ),
				'type'              => 'integer',
				'required'          => true,
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
				'description'       => __( 'Display type: dual or single.', 'astrologer-api' ),
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
			'svg'             => array(
				'description'       => __( 'Whether to include SVG chart image.', 'astrologer-api' ),
				'type'              => 'boolean',
				'default'           => true,
				'sanitize_callback' => static fn ( mixed $v ): bool => (bool) $v,
			),
			'ai_ctx'          => array(
				'description'       => __( 'Whether to include AI context.', 'astrologer-api' ),
				'type'              => 'boolean',
				'default'           => true,
				'sanitize_callback' => static fn ( mixed $v ): bool => (bool) $v,
			),
		);

		return array_merge( $extra, $options );
	}
}
