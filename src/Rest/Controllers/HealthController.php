<?php
/**
 * HealthController — REST endpoint for upstream API health check.
 *
 * Provides a single public route that checks the upstream API health.
 * No authentication required. Responses are cache-friendly (10 seconds).
 *
 * Route:
 *   GET /health — upstream API health status.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Rest\Controllers;

use Astrologer\Api\Rest\AbstractController;
use Astrologer\Api\Services\ChartService;
use Astrologer\Api\Services\RateLimiter;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Handles the health-check REST route under /astrologer/v1/health.
 *
 * This endpoint is public (no auth) and sets a short Cache-Control header
 * so clients and proxies can cache the response for up to 10 seconds.
 */
final class HealthController extends AbstractController {

	/**
	 * Cache max-age in seconds for health responses.
	 *
	 * @var int
	 */
	private const CACHE_MAX_AGE = 10;

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
	 * Register the health route.
	 */
	public function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			'/health',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'handle_health' ),
					'permission_callback' => '__return_true',
				),
			)
		);
	}

	/**
	 * Handle GET /health.
	 *
	 * Returns the upstream API health status. No authentication required.
	 * Response includes a Cache-Control header allowing 10-second caching.
	 *
	 * @param WP_REST_Request $request The incoming request.
	 * @return WP_REST_Response
	 */
	public function handle_health( WP_REST_Request $request ): WP_REST_Response {
		$result = $this->chart_service->health();

		if ( is_wp_error( $result ) ) {
			return $this->handle_service_error( $result );
		}

		/** @var array<string,mixed> $result */
		return $this->respond(
			$result,
			200,
			array(
				'Cache-Control' => sprintf( 'public, max-age=%d', self::CACHE_MAX_AGE ),
			)
		);
	}
}
