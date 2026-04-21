<?php
/**
 * GeoNames API client for city search and timezone lookup.
 *
 * Wraps the GeoNames searchJSON and timezoneJSON endpoints.
 * Uses HTTPS by default (secure.geonames.org).
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Http;

use Astrologer\Api\Repository\SettingsRepository;
use WP_Error;

/**
 * Client for the GeoNames public API.
 *
 * Provides city search and timezone lookup by coordinates.
 * Username is read from SettingsRepository ('geonames_username').
 */
final class GeonamesClient {

	/**
	 * Default GeoNames base URL (HTTPS).
	 *
	 * @var string
	 */
	private const DEFAULT_BASE_URL = 'https://secure.geonames.org';

	/**
	 * Request timeout in seconds.
	 *
	 * @var int
	 */
	private const TIMEOUT = 10;

	/**
	 * Settings repository for GeoNames username.
	 *
	 * @var SettingsRepository
	 */
	private SettingsRepository $settings;

	/**
	 * Constructor.
	 *
	 * @param SettingsRepository $settings Plugin settings repository.
	 */
	public function __construct( SettingsRepository $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Search for cities by name.
	 *
	 * Returns an array of results, each containing:
	 * name, countryCode, lat, lng, timezone (timezoneId), population.
	 *
	 * @param string      $query     City name or partial name to search for.
	 * @param int         $max_rows  Maximum number of results (default 10).
	 * @param string|null $lang      Language code for results (default 'en').
	 * @return array<int,array<string,mixed>>|WP_Error  List of results or WP_Error.
	 */
	public function search( string $query, int $max_rows = 10, ?string $lang = 'en' ): array|WP_Error {
		$username = $this->get_username();

		if ( is_wp_error( $username ) ) {
			return $username;
		}

		$params = array(
			'q'        => $query,
			'maxRows'  => $max_rows,
			'username' => $username,
			'type'     => 'json',
			'lang'     => $lang ?? 'en',
			'style'    => 'FULL',
		);

		/** This filter is documented in this class. */
		// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores -- Project convention.
		$params = apply_filters( 'astrologer_api/geonames_request_args', $params, 'search' );

		$url  = self::DEFAULT_BASE_URL . '/searchJSON';
		$args = array(
			'timeout' => self::TIMEOUT,
		);

		$response = wp_remote_get( add_query_arg( $params, $url ), $args );

		if ( is_wp_error( $response ) ) {
			return new WP_Error(
				'geonames_connection_error',
				$this->sanitize_message( $response->get_error_message() )
			);
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );

		if ( 200 !== $code ) {
			return new WP_Error(
				'geonames_http_error',
				/* translators: %d: HTTP status code */
				sprintf( __( 'GeoNames returned HTTP %d.', 'astrologer-api' ), $code )
			);
		}

		$decoded = json_decode( $body, true );

		if ( ! is_array( $decoded ) ) {
			return new WP_Error(
				'geonames_parse_error',
				__( 'Failed to parse GeoNames response.', 'astrologer-api' )
			);
		}

		// GeoNames returns errors as JSON with a status object.
		if ( isset( $decoded['status'] ) && is_array( $decoded['status'] ) ) {
			$value   = $decoded['status']['value'] ?? 0;
			$message = $decoded['status']['message'] ?? __( 'Unknown GeoNames error.', 'astrologer-api' );

			return new WP_Error(
				'geonames_api_error',
				$this->sanitize_message( $message )
			);
		}

		$geonames = $decoded['geonames'] ?? array();

		if ( ! is_array( $geonames ) ) {
			$geonames = array();
		}

		return $this->normalize_search_results( $geonames );
	}

	/**
	 * Look up timezone information by coordinates.
	 *
	 * Returns an array with keys: timezoneId, countryCode, countryName,
	 * lat, lng, dstOffset, gmtOffset, rawOffset.
	 *
	 * @param float $latitude  Latitude (-90 to 90).
	 * @param float $longitude Longitude (-180 to 180).
	 * @return array<string,mixed>|WP_Error  Timezone data or WP_Error.
	 */
	public function timezone( float $latitude, float $longitude ): array|WP_Error {
		$username = $this->get_username();

		if ( is_wp_error( $username ) ) {
			return $username;
		}

		$params = array(
			'lat'      => $latitude,
			'lng'      => $longitude,
			'username' => $username,
		);

		/** This filter is documented in this class. */
		// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores -- Project convention.
		$params = apply_filters( 'astrologer_api/geonames_request_args', $params, 'timezone' );

		$url  = self::DEFAULT_BASE_URL . '/timezoneJSON';
		$args = array(
			'timeout' => self::TIMEOUT,
		);

		$response = wp_remote_get( add_query_arg( $params, $url ), $args );

		if ( is_wp_error( $response ) ) {
			return new WP_Error(
				'geonames_connection_error',
				$this->sanitize_message( $response->get_error_message() )
			);
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );

		if ( 200 !== $code ) {
			return new WP_Error(
				'geonames_http_error',
				/* translators: %d: HTTP status code */
				sprintf( __( 'GeoNames returned HTTP %d.', 'astrologer-api' ), $code )
			);
		}

		$decoded = json_decode( $body, true );

		if ( ! is_array( $decoded ) ) {
			return new WP_Error(
				'geonames_parse_error',
				__( 'Failed to parse GeoNames response.', 'astrologer-api' )
			);
		}

		// GeoNames returns errors as JSON with a status object.
		if ( isset( $decoded['status'] ) && is_array( $decoded['status'] ) ) {
			$message = $decoded['status']['message'] ?? __( 'Unknown GeoNames error.', 'astrologer-api' );

			return new WP_Error(
				'geonames_api_error',
				$this->sanitize_message( $message )
			);
		}

		return $decoded;
	}

	/**
	 * Get the GeoNames username from settings.
	 *
	 * @return string|WP_Error  The username string, or WP_Error if not configured.
	 */
	private function get_username(): string|WP_Error {
		$username = $this->settings->get( 'geonames_username', '' );

		if ( ! is_string( $username ) || '' === $username ) {
			return new WP_Error(
				'geonames_not_configured',
				__( 'GeoNames username is not configured.', 'astrologer-api' )
			);
		}

		return $username;
	}

	/**
	 * Normalize GeoNames search results to a consistent shape.
	 *
	 * Each result is mapped to: name, countryCode, lat, lng,
	 * timezone (from timezoneId), population.
	 *
	 * @param array<int,array<string,mixed>> $geonames Raw geonames array from API.
	 * @return array<int,array<string,mixed>>
	 */
	private function normalize_search_results( array $geonames ): array {
		$results = array();

		foreach ( $geonames as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}

			$results[] = array(
				'name'        => $item['name'] ?? '',
				'countryCode' => $item['countryCode'] ?? '',
				'lat'         => (float) ( $item['lat'] ?? 0 ),
				'lng'         => (float) ( $item['lng'] ?? 0 ),
				'timezone'    => $item['timezoneId'] ?? $item['timeZoneId'] ?? '',
				'population'  => (int) ( $item['population'] ?? 0 ),
			);
		}

		return $results;
	}

	/**
	 * Sanitize an error message for safe display.
	 *
	 * @param string $message Raw error message.
	 * @return string
	 */
	private function sanitize_message( string $message ): string {
		return substr( wp_strip_all_tags( $message ), 0, 200 );
	}
}
