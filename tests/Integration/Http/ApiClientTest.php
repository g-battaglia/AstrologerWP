<?php
/**
 * Integration tests for ApiClient.
 *
 * Uses the `pre_http_request` filter to mock upstream responses.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Tests\Integration\Http;

use Astrologer\Api\Http\ApiClient;
use Astrologer\Api\Repository\SettingsRepository;
use Astrologer\Api\Support\Encryption\EncryptionService;
use WP_UnitTestCase;

/**
 * @covers \Astrologer\Api\Http\ApiClient
 */
class ApiClientTest extends WP_UnitTestCase {

	/**
	 * Settings repository instance.
	 *
	 * @var SettingsRepository
	 */
	private SettingsRepository $settings;

	/**
	 * API client under test.
	 *
	 * @var ApiClient
	 */
	private ApiClient $client;

	/**
	 * Set up each test.
	 */
	public function setUp(): void {
		parent::setUp();

		delete_option( 'astrologer_api_settings' );

		$encryption     = new EncryptionService();
		$this->settings = new SettingsRepository( $encryption );
		$this->client   = new ApiClient( $this->settings );

		// Set a valid API key so auth check passes.
		$this->settings->set( 'rapidapi_key', 'test-rapidapi-key-12345' );
	}

	/**
	 * Tear down each test.
	 */
	public function tearDown(): void {
		remove_all_filters( 'pre_http_request' );
		delete_option( 'astrologer_api_settings' );
		parent::tearDown();
	}

	/**
	 * Returns a mock HTTP response via pre_http_request filter.
	 *
	 * @param int    $code    HTTP status code.
	 * @param string $body    JSON body.
	 * @return callable
	 */
	private function mock_response( int $code, string $body ): callable {
		return static function ( $pre, $args, $url ) use ( $code, $body ) {
			return array(
				'headers'  => array( 'content-type' => 'application/json' ),
				'body'     => $body,
				'response' => array( 'code' => $code ),
			);
		};
	}

	/**
	 * Test successful POST returns decoded array.
	 */
	public function test_post_returns_decoded_array_on_200(): void {
		$fixture = file_get_contents( ASTROLOGER_API_DIR . '/tests/fixtures/api/subject-200.json' );

		add_filter( 'pre_http_request', $this->mock_response( 200, $fixture ) );

		$result = $this->client->post( '/api/v5/subject', array( 'name' => 'Test' ) );

		$this->assertIsArray( $result );
		$this->assertSame( 'Test Subject', $result['name'] );
	}

	/**
	 * Test successful GET returns decoded array.
	 */
	public function test_get_returns_decoded_array_on_200(): void {
		$body = '{"status":"ok"}';

		add_filter( 'pre_http_request', $this->mock_response( 200, $body ) );

		$result = $this->client->get( '/health' );

		$this->assertIsArray( $result );
		$this->assertSame( 'ok', $result['status'] );
	}

	/**
	 * Test 422 response maps to validation_failed error.
	 */
	public function test_422_maps_to_validation_failed(): void {
		$body = '{"detail":"Invalid date"}';

		add_filter( 'pre_http_request', $this->mock_response( 422, $body ) );

		$result = $this->client->post( '/api/v5/subject', array() );

		$this->assertWPError( $result );
		$this->assertSame( 'validation_failed', $result->get_error_code() );
	}

	/**
	 * Test 429 response maps to rate_limited error.
	 */
	public function test_429_maps_to_rate_limited(): void {
		$body = '{"message":"Too many requests"}';

		add_filter( 'pre_http_request', $this->mock_response( 429, $body ) );

		$result = $this->client->post( '/api/v5/subject', array() );

		$this->assertWPError( $result );
		$this->assertSame( 'rate_limited', $result->get_error_code() );
	}

	/**
	 * Test 401 response maps to auth_failed error.
	 */
	public function test_401_maps_to_auth_failed(): void {
		$body = '{"message":"Invalid API key"}';

		add_filter( 'pre_http_request', $this->mock_response( 401, $body ) );

		$result = $this->client->post( '/api/v5/subject', array() );

		$this->assertWPError( $result );
		$this->assertSame( 'auth_failed', $result->get_error_code() );
	}

	/**
	 * Test 403 response maps to auth_failed error.
	 */
	public function test_403_maps_to_auth_failed(): void {
		$body = '{"message":"Forbidden"}';

		add_filter( 'pre_http_request', $this->mock_response( 403, $body ) );

		$result = $this->client->post( '/api/v5/subject', array() );

		$this->assertWPError( $result );
		$this->assertSame( 'auth_failed', $result->get_error_code() );
	}

	/**
	 * Test 500 response triggers retry and eventually returns upstream_error.
	 */
	public function test_5xx_triggers_retry_and_returns_upstream_error(): void {
		$attempt = 0;

		$callback = static function ( $pre, $args, $url ) use ( &$attempt ) {
			$attempt++;
			return array(
				'headers'  => array( 'content-type' => 'text/plain' ),
				'body'     => 'Internal Server Error',
				'response' => array( 'code' => 500 ),
			);
		};

		add_filter( 'pre_http_request', $callback );

		$result = $this->client->post( '/api/v5/subject', array() );

		// Should have retried: initial + MAX_RETRIES = 3 total attempts.
		$this->assertSame( 3, $attempt );
		$this->assertWPError( $result );
		$this->assertSame( 'upstream_error', $result->get_error_code() );
	}

	/**
	 * Test 5xx retry recovers on third attempt.
	 */
	public function test_5xx_retry_succeeds_on_third_attempt(): void {
		$attempt    = 0;
		$success_body = '{"status":"recovered"}';

		$callback = static function ( $pre, $args, $url ) use ( &$attempt, $success_body ) {
			$attempt++;

			if ( $attempt < 3 ) {
				return array(
					'headers'  => array( 'content-type' => 'text/plain' ),
					'body'     => 'Service Unavailable',
					'response' => array( 'code' => 503 ),
				);
			}

			return array(
				'headers'  => array( 'content-type' => 'application/json' ),
				'body'     => $success_body,
				'response' => array( 'code' => 200 ),
			);
		};

		add_filter( 'pre_http_request', $callback );

		$result = $this->client->post( '/api/v5/subject', array() );

		$this->assertSame( 3, $attempt );
		$this->assertIsArray( $result );
		$this->assertSame( 'recovered', $result['status'] );
	}

	/**
	 * Test WP_Error connection failure triggers retry and returns upstream_error.
	 */
	public function test_connection_error_triggers_retry(): void {
		$attempt = 0;

		$callback = static function ( $pre, $args, $url ) use ( &$attempt ) {
			$attempt++;
			return new \WP_Error( 'http_request_failed', 'Connection timed out.' );
		};

		add_filter( 'pre_http_request', $callback );

		$result = $this->client->post( '/api/v5/subject', array() );

		// Initial + MAX_RETRIES = 3 total attempts.
		$this->assertSame( 3, $attempt );
		$this->assertWPError( $result );
		$this->assertSame( 'upstream_error', $result->get_error_code() );
	}

	/**
	 * Test missing API key returns auth_failed immediately.
	 */
	public function test_missing_api_key_returns_auth_failed(): void {
		// Clear the API key.
		$this->settings->set( 'rapidapi_key', '' );

		$result = $this->client->post( '/api/v5/subject', array() );

		$this->assertWPError( $result );
		$this->assertSame( 'auth_failed', $result->get_error_code() );
	}

	/**
	 * Test unknown error code maps to unknown_error.
	 */
	public function test_unknown_error_code_maps_to_unknown_error(): void {
		$body = '{"detail":"Payment Required"}';

		add_filter( 'pre_http_request', $this->mock_response( 402, $body ) );

		$result = $this->client->post( '/api/v5/subject', array() );

		$this->assertWPError( $result );
		$this->assertSame( 'unknown_error', $result->get_error_code() );
	}

	/**
	 * Test http_request_args filter is applied.
	 */
	public function test_http_request_args_filter_is_applied(): void {
		$body = '{"status":"ok"}';

		add_filter( 'pre_http_request', $this->mock_response( 200, $body ) );

		$filtered_timeout = null;

		add_filter(
			// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores -- Project convention.
			'astrologer_api/http_request_args',
			static function ( array $args, string $endpoint ) use ( &$filtered_timeout ) {
				$filtered_timeout = $args['timeout'];
				return $args;
			},
			10,
			2
		);

		$this->client->get( '/health' );

		$this->assertSame( 15, $filtered_timeout );
	}

	/**
	 * Test before_http_request and after_http_response actions fire.
	 */
	public function test_hooks_fire_on_success(): void {
		$body = '{"status":"ok"}';

		add_filter( 'pre_http_request', $this->mock_response( 200, $body ) );

		$before_fired = false;
		$after_fired  = false;

		add_action(
			// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores -- Project convention.
			'astrologer_api/before_http_request',
			static function ( string $endpoint, array $payload ) use ( &$before_fired ) {
				$before_fired = true;
			},
			10,
			2
		);

		add_action(
			// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores -- Project convention.
			'astrologer_api/after_http_response',
			static function ( string $endpoint, $response ) use ( &$after_fired ) {
				$after_fired = true;
			},
			10,
			2
		);

		$this->client->get( '/health' );

		$this->assertTrue( $before_fired );
		$this->assertTrue( $after_fired );
	}

	/**
	 * Test error messages are sanitized (stripped and truncated).
	 */
	public function test_error_message_is_sanitized(): void {
		$long_msg = str_repeat( 'a', 300 );

		add_filter( 'pre_http_request', $this->mock_response( 422, '{"detail":"' . $long_msg . '"}' ) );

		$result = $this->client->post( '/api/v5/subject', array() );

		$this->assertWPError( $result );
		$this->assertSame( 200, strlen( $result->get_error_message() ) );
	}
}
