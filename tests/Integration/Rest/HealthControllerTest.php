<?php
/**
 * Integration tests for HealthController.
 *
 * Uses `pre_http_request` filter to mock upstream API responses and
 * WP_REST_Server::dispatch() to exercise the full REST pipeline.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Tests\Integration\Rest;

use Astrologer\Api\Http\ApiClient;
use Astrologer\Api\Repository\SettingsRepository;
use Astrologer\Api\Rest\Controllers\HealthController;
use Astrologer\Api\Rest\RestServiceProvider;
use Astrologer\Api\Services\ChartService;
use Astrologer\Api\Services\RateLimiter;
use Astrologer\Api\Support\Encryption\EncryptionService;
use WP_REST_Request;
use WP_REST_Server;
use WP_UnitTestCase;

/**
 * @covers \Astrologer\Api\Rest\Controllers\HealthController
 */
class HealthControllerTest extends WP_UnitTestCase {

	/**
	 * REST server instance.
	 *
	 * @var WP_REST_Server
	 */
	private WP_REST_Server $server;

	/**
	 * Set up each test.
	 */
	public function setUp(): void {
		parent::setUp();

		delete_option( 'astrologer_api_settings' );

		$encryption = new EncryptionService();
		$settings   = new SettingsRepository( $encryption );
		$settings->set( 'rapidapi_key', 'test-key-for-health' );

		$client        = new ApiClient( $settings );
		$chart_service = new ChartService( $client );
		$rate_limiter  = new RateLimiter();

		$controller = new HealthController( $chart_service, $rate_limiter );
		$provider   = new RestServiceProvider( $controller );

		/** @var WP_REST_Server $wp_rest_server */
		global $wp_rest_server;
		$this->server   = new WP_REST_Server();
		$wp_rest_server = $this->server;

		$provider->register_routes();
	}

	/**
	 * Tear down each test.
	 */
	public function tearDown(): void {
		remove_all_filters( 'pre_http_request' );
		delete_option( 'astrologer_api_settings' );

		/** @var WP_REST_Server $wp_rest_server */
		global $wp_rest_server;
		$wp_rest_server = null;

		parent::tearDown();
	}

	/**
	 * Returns a mock HTTP response via pre_http_request filter.
	 *
	 * @param int    $code HTTP status code.
	 * @param string $body JSON body string.
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
	 * Test that the health endpoint is publicly accessible (no auth required).
	 */
	public function test_public_access_without_auth(): void {
		wp_set_current_user( 0 );

		add_filter(
			'pre_http_request',
			$this->mock_response( 200, '{"status":"ok","version":"5.0"}' )
		);

		$request  = new WP_REST_Request( 'GET', '/astrologer/v1/health' );
		$response = $this->server->dispatch( $request );

		$this->assertSame( 200, $response->get_status() );

		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertSame( 'ok', $data['status'] );
	}

	/**
	 * Test that the Cache-Control header is set with correct max-age.
	 */
	public function test_cache_control_header_present(): void {
		wp_set_current_user( 0 );

		add_filter(
			'pre_http_request',
			$this->mock_response( 200, '{"status":"ok"}' )
		);

		$request  = new WP_REST_Request( 'GET', '/astrologer/v1/health' );
		$response = $this->server->dispatch( $request );

		$headers = $response->get_headers();
		$this->assertArrayHasKey( 'Cache-Control', $headers );
		$this->assertStringContainsString( 'max-age=10', $headers['Cache-Control'] );
	}

	/**
	 * Test that upstream health data is passed through to the response.
	 */
	public function test_upstream_data_passthrough(): void {
		wp_set_current_user( 0 );

		$upstream_body = wp_json_encode(
			array(
				'status'  => 'ok',
				'version' => '5.2.1',
				'uptime'  => 99.9,
			)
		);

		add_filter(
			'pre_http_request',
			$this->mock_response( 200, $upstream_body )
		);

		$request  = new WP_REST_Request( 'GET', '/astrologer/v1/health' );
		$response = $this->server->dispatch( $request );

		$this->assertSame( 200, $response->get_status() );

		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertSame( '5.2.1', $data['version'] );
		$this->assertSame( 99.9, $data['uptime'] );
	}

	/**
	 * Test that upstream failure returns an error response.
	 */
	public function test_upstream_failure_returns_error(): void {
		wp_set_current_user( 0 );

		add_filter(
			'pre_http_request',
			static function () {
				return new \WP_Error( 'http_request_failed', 'Connection timed out.' );
			}
		);

		$request  = new WP_REST_Request( 'GET', '/astrologer/v1/health' );
		$response = $this->server->dispatch( $request );

		// The error handler maps connection failures to a non-200 status.
		$this->assertGreaterThanOrEqual( 400, $response->get_status() );

		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'code', $data );
	}
}
