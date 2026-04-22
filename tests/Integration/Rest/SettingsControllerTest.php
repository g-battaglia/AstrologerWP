<?php
/**
 * Integration tests for SettingsController.
 *
 * Uses `pre_http_request` filter to mock upstream API responses and
 * WP_REST_Server::dispatch() to exercise the full REST pipeline.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Tests\Integration\Rest;

use Astrologer\Api\Repository\SettingsRepository;
use Astrologer\Api\Rest\Controllers\SettingsController;
use Astrologer\Api\Rest\RestServiceProvider;
use Astrologer\Api\Services\RateLimiter;
use Astrologer\Api\Support\Encryption\EncryptionService;
use WP_REST_Request;
use WP_REST_Server;
use WP_UnitTestCase;

/**
 * @covers \Astrologer\Api\Rest\Controllers\SettingsController
 */
class SettingsControllerTest extends WP_UnitTestCase {

	/**
	 * REST server instance.
	 *
	 * @var WP_REST_Server
	 */
	private WP_REST_Server $server;

	/**
	 * Admin user with settings capability.
	 *
	 * @var int
	 */
	private int $admin_id;

	/**
	 * Subscriber user without settings capability.
	 *
	 * @var int
	 */
	private int $subscriber_id;

	/**
	 * Settings repository instance.
	 *
	 * @var SettingsRepository
	 */
	private SettingsRepository $settings;

	/**
	 * Set up each test.
	 */
	public function setUp(): void {
		parent::setUp();

		delete_option( 'astrologer_api_settings' );

		$encryption     = new EncryptionService();
		$this->settings = new SettingsRepository( $encryption );
		$rate_limiter   = new RateLimiter();

		// Seed a test API key.
		$this->settings->set( 'rapidapi_key', 'secret-test-key-1234' );

		$controller = new SettingsController( $this->settings, $rate_limiter );
		$provider   = new RestServiceProvider( $controller );

		/** @var WP_REST_Server $wp_rest_server */
		global $wp_rest_server;
		$this->server   = new WP_REST_Server();
		$wp_rest_server = $this->server;

		$provider->register_routes();

		// Admin user with settings capability.
		$this->admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$admin          = get_user_by( 'id', $this->admin_id );
		$admin->add_cap( 'astrologer_manage_settings' );

		// Subscriber user without settings capability.
		$this->subscriber_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
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
	 * Test that subscriber gets 403 on GET /settings.
	 */
	public function test_subscriber_returns_403(): void {
		wp_set_current_user( $this->subscriber_id );

		$request  = new WP_REST_Request( 'GET', '/astrologer/v1/settings' );
		$response = $this->server->dispatch( $request );

		$this->assertSame( 403, $response->get_status() );
	}

	/**
	 * Test that admin GET /settings returns masked API key.
	 */
	public function test_admin_get_returns_masked_key(): void {
		wp_set_current_user( $this->admin_id );

		$request  = new WP_REST_Request( 'GET', '/astrologer/v1/settings' );
		$response = $this->server->dispatch( $request );

		$this->assertSame( 200, $response->get_status() );

		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertArrayNotHasKey( 'rapidapi_key', $data );
		$this->assertArrayHasKey( 'has_api_key', $data );
		$this->assertTrue( $data['has_api_key'] );
	}

	/**
	 * Test that admin POST /settings updates settings.
	 */
	public function test_admin_post_updates_settings(): void {
		wp_set_current_user( $this->admin_id );

		$request = new WP_REST_Request( 'POST', '/astrologer/v1/settings' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body(
			(string) wp_json_encode(
				array(
					'language' => 'IT',
					'school'   => 'Placidus',
				)
			)
		);

		$response = $this->server->dispatch( $request );

		$this->assertSame( 200, $response->get_status() );

		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertSame( 'IT', $data['language'] );
	}

	/**
	 * Test that empty POST body returns 400.
	 */
	public function test_empty_body_returns_400(): void {
		wp_set_current_user( $this->admin_id );

		$request = new WP_REST_Request( 'POST', '/astrologer/v1/settings' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body( '' );

		$response = $this->server->dispatch( $request );

		$this->assertSame( 400, $response->get_status() );

		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertSame( 'empty_body', $data['code'] );
	}

	/**
	 * Test that test-connection returns success with valid key.
	 */
	public function test_connection_success(): void {
		wp_set_current_user( $this->admin_id );

		add_filter(
			'pre_http_request',
			$this->mock_response( 200, '{"status":"ok"}' )
		);

		$request = new WP_REST_Request( 'POST', '/astrologer/v1/settings/test-connection' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body(
			(string) wp_json_encode( array( 'api_key' => 'valid-test-key' ) )
		);

		$response = $this->server->dispatch( $request );

		$this->assertSame( 200, $response->get_status() );

		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertTrue( $data['connected'] );
		$this->assertSame( 'Connection successful.', $data['message'] );
	}

	/**
	 * Test that test-connection returns failure on upstream error.
	 */
	public function test_connection_failure(): void {
		wp_set_current_user( $this->admin_id );

		add_filter(
			'pre_http_request',
			$this->mock_response( 401, '{"message":"Unauthorized"}' )
		);

		$request = new WP_REST_Request( 'POST', '/astrologer/v1/settings/test-connection' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body(
			(string) wp_json_encode( array( 'api_key' => 'invalid-key' ) )
		);

		$response = $this->server->dispatch( $request );

		$this->assertSame( 200, $response->get_status() );

		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertFalse( $data['connected'] );
		$this->assertStringContainsString( 'Connection failed', $data['message'] );
	}
}
