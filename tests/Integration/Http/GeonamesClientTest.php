<?php
/**
 * Integration tests for GeonamesClient.
 *
 * Uses the `pre_http_request` filter to mock upstream GeoNames responses.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Tests\Integration\Http;

use Astrologer\Api\Http\GeonamesClient;
use Astrologer\Api\Repository\SettingsRepository;
use Astrologer\Api\Support\Encryption\EncryptionService;
use WP_UnitTestCase;

/**
 * @covers \Astrologer\Api\Http\GeonamesClient
 */
class GeonamesClientTest extends WP_UnitTestCase {

	/**
	 * Settings repository instance.
	 *
	 * @var SettingsRepository
	 */
	private SettingsRepository $settings;

	/**
	 * Client under test.
	 *
	 * @var GeonamesClient
	 */
	private GeonamesClient $client;

	/**
	 * Set up each test.
	 */
	public function setUp(): void {
		parent::setUp();

		delete_option( 'astrologer_api_settings' );

		$encryption     = new EncryptionService();
		$this->settings = new SettingsRepository( $encryption );
		$this->client   = new GeonamesClient( $this->settings );
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
	 * Test: search returns WP_Error when GeoNames username is not configured.
	 */
	public function test_search_returns_error_when_not_configured(): void {
		// geonames_username is empty by default.
		$result = $this->client->search( 'Rome' );

		$this->assertWPError( $result );
		$this->assertSame( 'geonames_not_configured', $result->get_error_code() );
	}

	/**
	 * Test: timezone returns WP_Error when GeoNames username is not configured.
	 */
	public function test_timezone_returns_error_when_not_configured(): void {
		$result = $this->client->timezone( 41.90, 12.49 );

		$this->assertWPError( $result );
		$this->assertSame( 'geonames_not_configured', $result->get_error_code() );
	}

	/**
	 * Test: search returns normalized results on success.
	 */
	public function test_search_returns_results_on_success(): void {
		$this->settings->set( 'geonames_username', 'testuser' );

		add_filter( 'pre_http_request', function () {
			return array(
				'response' => array( 'code' => 200 ),
				'body'     => wp_json_encode( array(
					'geonames' => array(
						array(
							'name'        => 'Rome',
							'countryCode' => 'IT',
							'lat'         => '41.90278',
							'lng'         => '12.49637',
							'timezoneId'  => 'Europe/Rome',
							'population'  => 2318895,
						),
						array(
							'name'        => 'Romeoville',
							'countryCode' => 'US',
							'lat'         => '41.58',
							'lng'         => '-88.09',
							'population'  => 39091,
						),
					),
				) ),
			);
		} );

		$results = $this->client->search( 'Rome', 10 );

		$this->assertIsArray( $results );
		$this->assertCount( 2, $results );
		$this->assertSame( 'Rome', $results[0]['name'] );
		$this->assertSame( 'IT', $results[0]['countryCode'] );
		$this->assertSame( 41.90278, $results[0]['lat'] );
		$this->assertSame( 12.49637, $results[0]['lng'] );
		$this->assertSame( 'Europe/Rome', $results[0]['timezone'] );
		$this->assertSame( 2318895, $results[0]['population'] );
		$this->assertSame( 'Romeoville', $results[1]['name'] );
		$this->assertSame( 'US', $results[1]['countryCode'] );
	}

	/**
	 * Test: search handles empty geonames array.
	 */
	public function test_search_handles_empty_results(): void {
		$this->settings->set( 'geonames_username', 'testuser' );

		add_filter( 'pre_http_request', function () {
			return array(
				'response' => array( 'code' => 200 ),
				'body'     => wp_json_encode( array(
					'geonames' => array(),
				) ),
			);
		} );

		$results = $this->client->search( 'xyznonexistent' );

		$this->assertIsArray( $results );
		$this->assertCount( 0, $results );
	}

	/**
	 * Test: search handles GeoNames API error (status object in response).
	 */
	public function test_search_handles_api_error_status(): void {
		$this->settings->set( 'geonames_username', 'testuser' );

		add_filter( 'pre_http_request', function () {
			return array(
				'response' => array( 'code' => 200 ),
				'body'     => wp_json_encode( array(
					'status' => array(
						'value'   => 10,
						'message' => 'user does not exist.',
					),
				) ),
			);
		} );

		$result = $this->client->search( 'Rome' );

		$this->assertWPError( $result );
		$this->assertSame( 'geonames_api_error', $result->get_error_code() );
	}

	/**
	 * Test: search handles HTTP error.
	 */
	public function test_search_handles_http_error(): void {
		$this->settings->set( 'geonames_username', 'testuser' );

		add_filter( 'pre_http_request', function () {
			return array(
				'response' => array( 'code' => 500 ),
				'body'     => 'Internal Server Error',
			);
		} );

		$result = $this->client->search( 'Rome' );

		$this->assertWPError( $result );
		$this->assertSame( 'geonames_http_error', $result->get_error_code() );
	}

	/**
	 * Test: search handles connection error (WP_Error from wp_remote_get).
	 */
	public function test_search_handles_connection_error(): void {
		$this->settings->set( 'geonames_username', 'testuser' );

		add_filter( 'pre_http_request', function () {
			return new \WP_Error( 'http_request_failed', 'Connection timed out.' );
		} );

		$result = $this->client->search( 'Rome' );

		$this->assertWPError( $result );
		$this->assertSame( 'geonames_connection_error', $result->get_error_code() );
	}

	/**
	 * Test: search handles invalid JSON response.
	 */
	public function test_search_handles_invalid_json(): void {
		$this->settings->set( 'geonames_username', 'testuser' );

		add_filter( 'pre_http_request', function () {
			return array(
				'response' => array( 'code' => 200 ),
				'body'     => 'not json',
			);
		} );

		$result = $this->client->search( 'Rome' );

		$this->assertWPError( $result );
		$this->assertSame( 'geonames_parse_error', $result->get_error_code() );
	}

	/**
	 * Test: timezone returns data on success.
	 */
	public function test_timezone_returns_data_on_success(): void {
		$this->settings->set( 'geonames_username', 'testuser' );

		add_filter( 'pre_http_request', function () {
			return array(
				'response' => array( 'code' => 200 ),
				'body'     => wp_json_encode( array(
					'timezoneId'  => 'Europe/Rome',
					'countryCode' => 'IT',
					'countryName' => 'Italy',
					'lat'         => 41.90,
					'lng'         => 12.49,
					'gmtOffset'   => 1.0,
					'dstOffset'   => 2.0,
					'rawOffset'   => 1.0,
				) ),
			);
		} );

		$result = $this->client->timezone( 41.90, 12.49 );

		$this->assertIsArray( $result );
		$this->assertSame( 'Europe/Rome', $result['timezoneId'] );
		$this->assertSame( 'IT', $result['countryCode'] );
		$this->assertSame( 1.0, $result['gmtOffset'] );
	}

	/**
	 * Test: timezone handles API error status.
	 */
	public function test_timezone_handles_api_error(): void {
		$this->settings->set( 'geonames_username', 'testuser' );

		add_filter( 'pre_http_request', function () {
			return array(
				'response' => array( 'code' => 200 ),
				'body'     => wp_json_encode( array(
					'status' => array(
						'value'   => 10,
						'message' => 'user does not exist.',
					),
				) ),
			);
		} );

		$result = $this->client->timezone( 41.90, 12.49 );

		$this->assertWPError( $result );
		$this->assertSame( 'geonames_api_error', $result->get_error_code() );
	}

	/**
	 * Test: geonames_request_args filter is applied to search params.
	 */
	public function test_geonames_request_args_filter_applied(): void {
		$this->settings->set( 'geonames_username', 'testuser' );

		$captured = null;
		add_filter( 'astrologer_api/geonames_request_args', function ( array $params, string $context ) use ( &$captured ) {
			$captured = array( $params, $context );
			return $params;
		}, 10, 2 );

		add_filter( 'pre_http_request', function () {
			return array(
				'response' => array( 'code' => 200 ),
				'body'     => wp_json_encode( array( 'geonames' => array() ) ),
			);
		} );

		$this->client->search( 'Rome' );

		$this->assertNotNull( $captured );
		$this->assertSame( 'search', $captured[1] );
		$this->assertSame( 'Rome', $captured[0]['q'] );
	}

	/**
	 * Test: geonames_request_args filter is applied to timezone params.
	 */
	public function test_geonames_request_args_filter_applied_to_timezone(): void {
		$this->settings->set( 'geonames_username', 'testuser' );

		$captured = null;
		add_filter( 'astrologer_api/geonames_request_args', function ( array $params, string $context ) use ( &$captured ) {
			$captured = array( $params, $context );
			return $params;
		}, 10, 2 );

		add_filter( 'pre_http_request', function () {
			return array(
				'response' => array( 'code' => 200 ),
				'body'     => wp_json_encode( array(
					'timezoneId' => 'Europe/Rome',
				) ),
			);
		} );

		$this->client->timezone( 41.90, 12.49 );

		$this->assertNotNull( $captured );
		$this->assertSame( 'timezone', $captured[1] );
		$this->assertSame( 41.90, $captured[0]['lat'] );
		$this->assertSame( 12.49, $captured[0]['lng'] );
	}
}
