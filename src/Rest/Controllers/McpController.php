<?php
/**
 * McpController — REST endpoint for MCP (Model Context Protocol) proxy.
 *
 * Proxies JSON-RPC 2.0 requests to the upstream MCP endpoint.
 *
 * Route:
 *   POST /mcp — proxy to upstream MCP endpoint.
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
 * Handles the MCP proxy REST route under /astrologer/v1/mcp.
 */
final class McpController extends AbstractController {

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
	 * Register the MCP route.
	 */
	public function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			'/mcp',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'handle_mcp' ),
					'permission_callback' => array( $this, 'default_permission_callback' ),
					'args'                => $this->get_args(),
				),
			)
		);
	}

	/**
	 * Handle POST /mcp.
	 *
	 * Proxies the JSON-RPC 2.0 request body to the upstream MCP endpoint.
	 *
	 * @param WP_REST_Request $request The incoming request.
	 * @return WP_REST_Response
	 */
	public function handle_mcp( WP_REST_Request $request ): WP_REST_Response {
		$params = $this->resolve_params( $request );

		$result = $this->chart_service->mcp( $params );

		if ( is_wp_error( $result ) ) {
			return $this->handle_service_error( $result );
		}

		/** @var array<string,mixed> $result */
		return $this->respond( $result );
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
	 * Get the argument schema for POST /mcp.
	 *
	 * The MCP endpoint accepts any valid JSON-RPC 2.0 payload.
	 * We validate the minimal required structure.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	private function get_args(): array {
		return array(
			'jsonrpc' => array(
				'description'       => __( 'JSON-RPC version (must be "2.0").', 'astrologer-api' ),
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => static function ( mixed $value ): bool {
					return is_string( $value ) && '2.0' === $value;
				},
			),
			'method'  => array(
				'description'       => __( 'JSON-RPC method name.', 'astrologer-api' ),
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			),
			'params'  => array(
				'description' => __( 'JSON-RPC parameters.', 'astrologer-api' ),
				'type'        => 'object',
			),
			'id'      => array(
				'description'       => __( 'JSON-RPC request identifier.', 'astrologer-api' ),
				'type'              => array( 'string', 'integer' ),
				'sanitize_callback' => static function ( mixed $value ): mixed {
					return is_int( $value ) ? $value : sanitize_text_field( (string) $value );
				},
			),
		);
	}
}
