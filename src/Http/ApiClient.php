<?php
/**
 * HTTP client for the Astrologer API via RapidAPI proxy.
 *
 * Handles request construction, exponential retry on 5xx/connection errors,
 * and maps upstream HTTP status codes to typed WP_Error instances.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Http;

use Astrologer\Api\Repository\SettingsRepository;
use WP_Error;

/**
 * RapidAPI proxy client with retry logic and WP_Error error mapping.
 */
final class ApiClient {

	/**
	 * Maximum number of retry attempts for transient failures.
	 *
	 * @var int
	 */
	private const MAX_RETRIES = 2;

	/**
	 * Base backoff in milliseconds for exponential retry.
	 *
	 * @var int
	 */
	private const BASE_BACKOFF_MS = 500;

	/**
	 * Request timeout in seconds.
	 *
	 * @var int
	 */
	private const TIMEOUT = 15;

	/**
	 * Default RapidAPI host header.
	 *
	 * @var string
	 */
	private const DEFAULT_HOST = 'astrologer.p.rapidapi.com';

	/**
	 * Settings repository for API key and base URL.
	 *
	 * @var SettingsRepository
	 */
	private SettingsRepository $settings;

	/**
	 * Constructor.
	 *
	 * @param SettingsRepository $settings Plugin settings (API key, base URL).
	 */
	public function __construct( SettingsRepository $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Send a POST request to the upstream API.
	 *
	 * @param string               $endpoint API endpoint path (e.g. '/api/v5/chart/birth-chart').
	 * @param array<string,mixed>  $payload  JSON-serializable request body.
	 * @return array<string,mixed>|WP_Error  Decoded response body or WP_Error on failure.
	 */
	public function post( string $endpoint, array $payload = array() ): array|WP_Error {
		return $this->request( 'POST', $endpoint, $payload );
	}

	/**
	 * Send a GET request to the upstream API.
	 *
	 * @param string               $endpoint API endpoint path.
	 * @param array<string,mixed>  $query    Query parameters.
	 * @return array<string,mixed>|WP_Error  Decoded response body or WP_Error on failure.
	 */
	public function get( string $endpoint, array $query = array() ): array|WP_Error {
		return $this->request( 'GET', $endpoint, $query );
	}

	/**
	 * Execute an HTTP request with retry logic.
	 *
	 * @param string               $method   HTTP method (GET or POST).
	 * @param string               $endpoint API endpoint path.
	 * @param array<string,mixed>  $data     Payload (POST) or query params (GET).
	 * @return array<string,mixed>|WP_Error
	 */
	private function request( string $method, string $endpoint, array $data = array() ): array|WP_Error {
		$base_url = $this->settings->get( 'api_base_url', 'https://astrologer.p.rapidapi.com' );
		$url      = rtrim( (string) $base_url, '/' ) . '/' . ltrim( $endpoint, '/' );

		if ( 'GET' === $method && ! empty( $data ) ) {
			$url = add_query_arg( $data, $url );
		}

		$api_key = $this->settings->get( 'rapidapi_key', '' );

		if ( ! is_string( $api_key ) || '' === $api_key ) {
			return new WP_Error(
				'auth_failed',
				__( 'RapidAPI key is not configured.', 'astrologer-api' )
			);
		}

		$args = array(
			'method'  => $method,
			'timeout' => self::TIMEOUT,
			'headers' => array(
				'Content-Type'    => 'application/json',
				'X-RapidAPI-Host' => self::DEFAULT_HOST,
				'X-RapidAPI-Key'  => $api_key,
			),
		);

		if ( 'POST' === $method ) {
			$args['body'] = wp_json_encode( $data );
		}

		/** This filter is documented in this class. */
		// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores -- Project convention.
		$args = apply_filters( 'astrologer_api/http_request_args', $args, $endpoint );

		/** This action is documented in this class. */
		// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores -- Project convention.
		do_action( 'astrologer_api/before_http_request', $endpoint, $data );

		$attempt      = 0;
		$max_attempts = 1 + self::MAX_RETRIES;

		while ( $attempt < $max_attempts ) {
			$response = wp_remote_request( $url, $args );

			if ( is_wp_error( $response ) ) {
				// Connection error — retryable.
				++$attempt;

				if ( $attempt < $max_attempts ) {
					$this->backoff( $attempt );
					continue;
				}

				/** This action is documented in this class. */
				// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores -- Project convention.
				do_action( 'astrologer_api/after_http_response', $endpoint, $response );

				return new WP_Error(
					'upstream_error',
					$this->sanitize_message( $response->get_error_message() )
				);
			}

			$code    = wp_remote_retrieve_response_code( $response );
			$body    = wp_remote_retrieve_body( $response );
			$decoded = json_decode( $body, true );

			if ( ! is_array( $decoded ) ) {
				$decoded = array();
			}

			// Successful response.
			if ( $code >= 200 && $code < 300 ) {
				/** This action is documented in this class. */
				// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores -- Project convention.
				do_action( 'astrologer_api/after_http_response', $endpoint, $decoded );

				return $decoded;
			}

			// 5xx — retryable.
			if ( $code >= 500 ) {
				++$attempt;

				if ( $attempt < $max_attempts ) {
					$this->backoff( $attempt );
					continue;
				}

				/** This action is documented in this class. */
				// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores -- Project convention.
				do_action( 'astrologer_api/after_http_response', $endpoint, new WP_Error( 'upstream_error', 'Server error ' . $code ) );

				return new WP_Error(
					'upstream_error',
					$this->sanitize_message(
						$decoded['detail'] ?? $decoded['message'] ?? $body
					)
				);
			}

			// Non-retryable client errors.
			$error = $this->map_error( $code, $decoded, $body );  // @phpstan-ignore argument.type

			/** This action is documented in this class. */
			// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores -- Project convention.
			do_action( 'astrologer_api/after_http_response', $endpoint, $error );

			return $error;
		}

		// Should not reach here, but satisfy static analysis.
		return new WP_Error( 'unknown_error', __( 'Unexpected client state.', 'astrologer-api' ) );
	}

	/**
	 * Map an HTTP status code to a typed WP_Error.
	 *
	 * @param int                  $code    HTTP response code.
	 * @param array<string,mixed>  $decoded Decoded JSON body.
	 * @param string               $raw     Raw response body.
	 * @return WP_Error
	 */
	private function map_error( int $code, array $decoded, string $raw ): WP_Error {
		$message = $decoded['detail'] ?? $decoded['message'] ?? $raw;

		return match ( $code ) {
			401, 403  => new WP_Error( 'auth_failed', $this->sanitize_message( $message ) ),
			422       => new WP_Error( 'validation_failed', $this->sanitize_message( $message ) ),
			429       => new WP_Error( 'rate_limited', $this->sanitize_message( $message ) ),
			default   => new WP_Error( 'unknown_error', $this->sanitize_message( $message ) ),
		};
	}

	/**
	 * Sanitize an error message for safe display.
	 *
	 * Strips all HTML tags and truncates to 200 characters.
	 *
	 * @param string $message Raw error message.
	 * @return string
	 */
	private function sanitize_message( string $message ): string {
		return substr( wp_strip_all_tags( $message ), 0, 200 );
	}

	/**
	 * Exponential backoff sleep.
	 *
	 * @param int $attempt Current attempt number (1-based).
	 */
	private function backoff( int $attempt ): void {
		usleep( self::BASE_BACKOFF_MS * 1000 * (int) pow( 2, $attempt - 1 ) );
	}
}
