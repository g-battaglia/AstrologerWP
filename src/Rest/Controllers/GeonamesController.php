<?php
/**
 * GeonamesController — REST endpoints for city search and timezone lookup.
 *
 * Provides two GET routes consumed by the city autocomplete widget
 * and the birth-form timezone resolver.
 *
 * Routes:
 *   GET /geonames/search    — city search by query string.
 *   GET /geonames/timezone  — timezone lookup by coordinates.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Rest\Controllers;

use Astrologer\Api\Http\GeonamesClient;
use Astrologer\Api\Rest\AbstractController;
use Astrologer\Api\Services\RateLimiter;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Handles GeoNames-related REST routes under /astrologer/v1/geonames.
 *
 * Both endpoints require the `astrologer_calculate_chart` capability
 * (also available to guests via the capability map filter) and are
 * rate-limited independently.
 */
final class GeonamesController extends AbstractController {

	/**
	 * GeoNames API client.
	 *
	 * @var GeonamesClient
	 */
	private GeonamesClient $geonames;

	/**
	 * Constructor.
	 *
	 * @param GeonamesClient $geonames    GeoNames client for city/timezone lookups.
	 * @param RateLimiter    $rate_limiter Rate limiting service.
	 */
	public function __construct( GeonamesClient $geonames, RateLimiter $rate_limiter ) {
		parent::__construct( $rate_limiter );
		$this->geonames = $geonames;
	}

	/**
	 * Register GeoNames REST routes.
	 */
	public function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			'/geonames/search',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'handle_search' ),
					'permission_callback' => array( $this, 'default_permission_callback' ),
					'args'                => $this->get_search_args(),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/geonames/timezone',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'handle_timezone' ),
					'permission_callback' => array( $this, 'default_permission_callback' ),
					'args'                => $this->get_timezone_args(),
				),
			)
		);
	}

	/**
	 * Handle GET /geonames/search.
	 *
	 * Returns a list of cities matching the query string.
	 *
	 * @param WP_REST_Request $request The incoming request.
	 * @return WP_REST_Response
	 */
	public function handle_search( WP_REST_Request $request ): WP_REST_Response {
		$query    = $request->get_param( 'q' );
		$limit    = (int) $request->get_param( 'limit' );
		$language = $request->get_param( 'lang' );

		$result = $this->geonames->search( $query, $limit, $language );

		if ( is_wp_error( $result ) ) {
			return $this->handle_service_error( $result );
		}

		/** @var array<int,array<string,mixed>> $result */
		return $this->respond( array( 'results' => $result ) );
	}

	/**
	 * Handle GET /geonames/timezone.
	 *
	 * Returns timezone information for the given coordinates.
	 *
	 * @param WP_REST_Request $request The incoming request.
	 * @return WP_REST_Response
	 */
	public function handle_timezone( WP_REST_Request $request ): WP_REST_Response {
		$latitude  = (float) $request->get_param( 'lat' );
		$longitude = (float) $request->get_param( 'lng' );

		$result = $this->geonames->timezone( $latitude, $longitude );

		if ( is_wp_error( $result ) ) {
			return $this->handle_service_error( $result );
		}

		/** @var array<string,mixed> $result */
		return $this->respond( $result );
	}

	/**
	 * Schema arguments for the search endpoint.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	private function get_search_args(): array {
		return array(
			'q'     => array(
				'description'       => esc_html__( 'City name or partial name to search for.', 'astrologer-api' ),
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => static function ( $value ): bool {
					return is_string( $value ) && '' !== trim( $value );
				},
			),
			'limit' => array(
				'description'       => esc_html__( 'Maximum number of results.', 'astrologer-api' ),
				'type'              => 'integer',
				'default'           => 10,
				'minimum'           => 1,
				'maximum'           => 50,
				'sanitize_callback' => 'absint',
			),
			'lang'  => array(
				'description'       => esc_html__( 'Language code for results.', 'astrologer-api' ),
				'type'              => 'string',
				'default'           => 'en',
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => static function ( $value ): bool {
					return is_string( $value ) && preg_match( '/^[a-z]{2,3}$/i', $value );
				},
			),
		);
	}

	/**
	 * Schema arguments for the timezone endpoint.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	private function get_timezone_args(): array {
		return array(
			'lat' => array(
				'description'       => esc_html__( 'Latitude (-90 to 90).', 'astrologer-api' ),
				'type'              => 'number',
				'required'          => true,
				'minimum'           => -90.0,
				'maximum'           => 90.0,
				'sanitize_callback' => 'floatval',
			),
			'lng' => array(
				'description'       => esc_html__( 'Longitude (-180 to 180).', 'astrologer-api' ),
				'type'              => 'number',
				'required'          => true,
				'minimum'           => -180.0,
				'maximum'           => 180.0,
				'sanitize_callback' => 'floatval',
			),
		);
	}
}
