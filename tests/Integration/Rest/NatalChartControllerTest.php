<?php
/**
 * Integration tests for NatalChartController.
 *
 * Uses `pre_http_request` filter to mock upstream API responses and
 * WP_REST_Server::dispatch() to exercise the full REST pipeline.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Tests\Integration\Rest;

use Astrologer\Api\Capabilities\CapabilityManager;
use Astrologer\Api\Http\ApiClient;
use Astrologer\Api\Repository\SettingsRepository;
use Astrologer\Api\Rest\Controllers\NatalChartController;
use Astrologer\Api\Rest\RestServiceProvider;
use Astrologer\Api\Services\ChartService;
use Astrologer\Api\Services\RateLimiter;
use Astrologer\Api\Support\Encryption\EncryptionService;
use WP_REST_Request;
use WP_REST_Server;
use WP_UnitTestCase;

/**
 * @covers \Astrologer\Api\Rest\Controllers\NatalChartController
 */
class NatalChartControllerTest extends WP_UnitTestCase {

	/**
	 * REST server instance.
	 *
	 * @var WP_REST_Server
	 */
	private WP_REST_Server $server;

	/**
	 * Test user with chart capabilities.
	 *
	 * @var int
	 */
	private int $user_id;

	/**
	 * Set up each test.
	 */
	public function setUp(): void {
		parent::setUp();

		delete_option( 'astrologer_api_settings' );

		// Set up a valid API key.
		$encryption = new EncryptionService();
		$settings   = new SettingsRepository( $encryption );
		$settings->set( 'rapidapi_key', 'test-key-for-natal' );

		// Create and register the controller.
		$client        = new ApiClient( $settings );
		$chart_service = new ChartService( $client );
		$rate_limiter  = new RateLimiter();

		$controller = new NatalChartController( $chart_service, $rate_limiter );
		$provider   = new RestServiceProvider( $controller );

		/** @var WP_REST_Server $wp_rest_server */
		global $wp_rest_server;
		$this->server = new WP_REST_Server();
		$wp_rest_server = $this->server;

		$provider->register_routes();

		// Create a user with the astrologer_calculate_chart capability.
		$this->user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$user          = get_user_by( 'id', $this->user_id );
		$user->add_cap( 'astrologer_calculate_chart' );
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
	 * Build a valid natal chart request body.
	 *
	 * @return array<string,mixed>
	 */
	private function valid_natal_body(): array {
		return array(
			'subject' => array(
				'name'      => 'Test Subject',
				'year'      => 1990,
				'month'     => 5,
				'day'       => 15,
				'hour'      => 14,
				'minute'    => 30,
				'city'      => 'Rome',
				'nation'    => 'IT',
				'latitude'  => 41.9028,
				'longitude' => 12.4964,
				'timezone'  => 'Europe/Rome',
			),
		);
	}

	/**
	 * Test that unauthenticated requests return 403.
	 */
	public function test_unauthenticated_returns_403(): void {
		wp_set_current_user( 0 );

		$request = new WP_REST_Request( 'POST', '/astrologer/v1/natal-chart' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body( (string) wp_json_encode( $this->valid_natal_body() ) );

		$response = $this->server->dispatch( $request );

		$this->assertSame( 403, $response->get_status() );
	}

	/**
	 * Test that authenticated request with mocked API returns 200.
	 */
	public function test_authenticated_returns_200_with_mocked_api(): void {
		wp_set_current_user( $this->user_id );

		$fixture = wp_json_encode(
			array(
				'svg'       => '<svg xmlns="http://www.w3.org/2000/svg"><circle r="10"/></svg>',
				'positions' => array(
					array(
						'point'  => 'Sun',
						'sign'   => 'Taurus',
						'degree' => 24.5,
					),
				),
				'aspects'   => array(),
			)
		);

		add_filter( 'pre_http_request', $this->mock_response( 200, $fixture ) );

		$request = new WP_REST_Request( 'POST', '/astrologer/v1/natal-chart' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body( (string) wp_json_encode( $this->valid_natal_body() ) );

		$response = $this->server->dispatch( $request );

		$this->assertSame( 200, $response->get_status() );

		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'svg', $data );
	}

	/**
	 * Test that invalid schema returns 400.
	 */
	public function test_invalid_schema_returns_400(): void {
		wp_set_current_user( $this->user_id );

		// Missing required 'subject' key.
		$request = new WP_REST_Request( 'POST', '/astrologer/v1/natal-chart' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body( (string) wp_json_encode( array( 'not_subject' => 'invalid' ) ) );

		$response = $this->server->dispatch( $request );

		$this->assertSame( 400, $response->get_status() );
	}

	/**
	 * Test that rate limit header is present in the response.
	 */
	public function test_rate_limit_header_present(): void {
		wp_set_current_user( $this->user_id );

		$fixture = wp_json_encode(
			array(
				'svg'       => '<svg/>',
				'positions' => array(),
				'aspects'   => array(),
			)
		);

		add_filter( 'pre_http_request', $this->mock_response( 200, $fixture ) );

		$request = new WP_REST_Request( 'POST', '/astrologer/v1/natal-chart' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body( (string) wp_json_encode( $this->valid_natal_body() ) );

		$response = $this->server->dispatch( $request );

		$headers = $response->get_headers();
		$this->assertArrayHasKey( 'X-Astrologer-Rate-Remaining', $headers );
	}
}
