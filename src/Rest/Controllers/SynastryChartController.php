<?php
/**
 * SynastryChartController — REST endpoint for synastry chart calculations.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Rest\Controllers;

use Astrologer\Api\DTO\SynastryRequestDTO;
use Astrologer\Api\Rest\AbstractController;
use Astrologer\Api\Rest\Schemas\ChartOptionsSchema;
use Astrologer\Api\Rest\Schemas\SubjectSchema;
use Astrologer\Api\Services\ChartService;
use Astrologer\Api\Services\RateLimiter;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Handles POST /astrologer/v1/synastry-chart.
 *
 * Accepts two subjects (first_subject, second_subject) with optional chart
 * options, proxies to ChartService::synastryChart(), returns bi-wheel data.
 */
final class SynastryChartController extends AbstractController {

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
	 * Register the synastry chart route.
	 */
	public function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			'/synastry-chart',
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
	 * Handle the synastry chart request.
	 *
	 * @param WP_REST_Request $request The incoming request.
	 * @return WP_REST_Response
	 */
	public function handle( WP_REST_Request $request ): WP_REST_Response {
		$params = $this->resolve_params( $request );
		$dto    = SynastryRequestDTO::from_array( $params );

		$result = $this->chart_service->synastryChart( $dto );

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
		$subject_schema = SubjectSchema::get();
		$options        = ChartOptionsSchema::get();

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
			'svg'            => array(
				'description'       => __( 'Whether to include SVG chart image.', 'astrologer-api' ),
				'type'              => 'boolean',
				'default'           => true,
				'sanitize_callback' => static fn ( mixed $v ): bool => (bool) $v,
			),
			'ai_ctx'         => array(
				'description'       => __( 'Whether to include AI context.', 'astrologer-api' ),
				'type'              => 'boolean',
				'default'           => true,
				'sanitize_callback' => static fn ( mixed $v ): bool => (bool) $v,
			),
		);

		return array_merge( $extra, $options );
	}
}
