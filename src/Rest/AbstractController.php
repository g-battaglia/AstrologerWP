<?php
/**
 * Abstract base for all REST controllers in the astrologer/v1 namespace.
 *
 * Provides shared permission checks, rate-limit headers, response helpers,
 * and error mapping. Every concrete controller extends this class.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Rest;

use Astrologer\Api\Services\RateLimiter;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Base REST controller with rate limiting and permission helpers.
 *
 * Concrete controllers override register_routes() and use the protected
 * helpers to build consistent, rate-limited REST endpoints.
 */
abstract class AbstractController {

	/**
	 * REST API namespace.
	 *
	 * @var string
	 */
	protected const NAMESPACE = 'astrologer/v1';

	/**
	 * Rate limiter service.
	 *
	 * @var RateLimiter
	 */
	protected RateLimiter $rate_limiter;

	/**
	 * Constructor.
	 *
	 * @param RateLimiter $rate_limiter Transient-based rate limiter.
	 */
	public function __construct( RateLimiter $rate_limiter ) {
		$this->rate_limiter = $rate_limiter;
	}

	/**
	 * Register REST routes. Called during rest_api_init by RestServiceProvider.
	 */
	abstract public function register_routes(): void;

	/**
	 * Check that the current user has the required capability and is not rate-limited.
	 *
	 * @param WP_REST_Request $request The incoming request.
	 * @param string          $cap     Required WordPress capability.
	 * @return true|\WP_Error True if allowed, WP_Error otherwise.
	 */
	protected function permission_check( WP_REST_Request $request, string $cap ): true|WP_Error {
		if ( ! current_user_can( $cap ) ) {
			return new WP_Error(
				'rest_forbidden',
				esc_html__( 'Insufficient permissions.', 'astrologer-api' ),
				array( 'status' => 403 )
			);
		}

		$user_id = get_current_user_id();
		$ip      = RateLimiter::detect_ip();
		$bucket  = $this->rate_bucket( $request );

		if ( ! $this->rate_limiter->check( $bucket, $user_id, $ip ) ) {
			return new WP_Error(
				'rest_rate_limited',
				esc_html__( 'Too many requests. Please try again later.', 'astrologer-api' ),
				array( 'status' => 429 )
			);
		}

		return true;
	}

	/**
	 * Public permission callback that can be used as a permission_callback
	 * in register_rest_route(). Delegates to permission_check() with a
	 * configurable capability (defaults to 'astrologer_calculate_chart').
	 *
	 * @param WP_REST_Request $request The incoming request.
	 * @return true|\WP_Error
	 */
	public function default_permission_callback( WP_REST_Request $request ): true|WP_Error {
		return $this->permission_check( $request, 'astrologer_calculate_chart' );
	}

	/**
	 * Build a standardised REST response with rate-limit headers.
	 *
	 * @param mixed $data    Response data (array, scalar, null).
	 * @param int   $status  HTTP status code. Default 200.
	 * @param array<string,string> $headers Additional HTTP headers.
	 * @return WP_REST_Response
	 */
	protected function respond( mixed $data, int $status = 200, array $headers = array() ): WP_REST_Response {
		$response = new WP_REST_Response( $data, $status );

		foreach ( $headers as $name => $value ) {
			$response->header( $name, $value );
		}

		$response->header(
			'X-Astrologer-Rate-Remaining',
			(string) $this->rate_remaining()
		);

		return $response;
	}

	/**
	 * Convert a WP_Error from ChartService / ApiClient into a REST response.
	 *
	 * @param WP_Error $error Error from service layer.
	 * @return WP_REST_Response
	 */
	protected function handle_service_error( WP_Error $error ): WP_REST_Response {
		$error_data = $error->get_error_data();
		$status     = is_array( $error_data ) && isset( $error_data['status'] )
			? (int) $error_data['status']
			: 500;

		return $this->respond(
			array(
				'code'    => $error->get_error_code(),
				'message' => $error->get_error_message(),
			),
			$status
		);
	}

	/**
	 * Derive a rate-limit bucket name from the request route.
	 *
	 * Uses the route base (e.g. 'natal-chart' from '/natal-chart') so
	 * each endpoint gets its own rate counter.
	 *
	 * @param WP_REST_Request $request The incoming request.
	 * @return string Bucket identifier.
	 */
	protected function rate_bucket( WP_REST_Request $request ): string {
		$route  = $request->get_route();
		$prefix = '/' . static::NAMESPACE . '/';
		$path   = str_starts_with( $route, $prefix )
			? substr( $route, strlen( $prefix ) )
			: $route;

		$base = sanitize_key( basename( $path ) );
		return '' !== $base ? $base : 'rest';
	}

	/**
	 * Get the approximate number of remaining requests for the current user/IP.
	 *
	 * This is a best-effort counter — it reads from the same transient used
	 * by RateLimiter::check(). When no transient exists yet, the full limit
	 * is reported.
	 *
	 * @return int Approximate remaining requests.
	 */
	protected function rate_remaining(): int {
		$user_id = get_current_user_id();
		$ip      = RateLimiter::detect_ip();

		// Admins are not rate limited, report a large number.
		if ( 0 !== $user_id && user_can( $user_id, 'manage_options' ) ) {
			return 9999;
		}

		/** @var int $limit */
		$limit = (int) apply_filters( 'astrologer_api/rate_limit_per_minute', 60, 'rest', $user_id );

		// We cannot read the exact bucket without a request, so use a generic key.
		$key   = 'astrologer_rl_rest_' . ( 0 !== $user_id ? 'u' . $user_id : md5( $ip ) );
		$count = (int) get_transient( $key );

		return max( 0, $limit - $count );
	}
}
