<?php
/**
 * Integration tests for ChartService.
 *
 * Uses the `pre_http_request` filter to mock upstream API responses.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Tests\Integration\Services;

use Astrologer\Api\DTO\ChartRequestDTO;
use Astrologer\Api\DTO\ChartResponseDTO;
use Astrologer\Api\DTO\CompatibilityRequestDTO;
use Astrologer\Api\DTO\CompositeRequestDTO;
use Astrologer\Api\DTO\MoonPhaseRequestDTO;
use Astrologer\Api\DTO\NowRequestDTO;
use Astrologer\Api\DTO\ReturnRequestDTO;
use Astrologer\Api\DTO\SubjectDTO;
use Astrologer\Api\DTO\SynastryRequestDTO;
use Astrologer\Api\DTO\TransitRequestDTO;
use Astrologer\Api\Http\ApiClient;
use Astrologer\Api\Repository\SettingsRepository;
use Astrologer\Api\Services\ChartService;
use Astrologer\Api\Support\Encryption\EncryptionService;
use Astrologer\Api\ValueObjects\BirthData;
use Astrologer\Api\ValueObjects\ChartOptions;
use Astrologer\Api\ValueObjects\GeoLocation;
use WP_UnitTestCase;

/**
 * @covers \Astrologer\Api\Services\ChartService
 */
class ChartServiceTest extends WP_UnitTestCase {

	/**
	 * Service under test.
	 *
	 * @var ChartService
	 */
	private ChartService $service;

	/**
	 * Actions fired during the test (collected for assertion).
	 *
	 * @var list<array{hook: string, args: list<mixed>}>
	 */
	private array $fired_actions = array();

	/**
	 * Set up test fixtures.
	 */
	public function setUp(): void {
		parent::setUp();

		// Ensure a fake API key so ApiClient does not short-circuit.
		update_option(
			SettingsRepository::class . '__test_key',
			array( 'rapidapi_key' => 'test-key-123' ),
			false,
		);

		$encryption = new EncryptionService();
		$settings   = new class( $encryption ) extends SettingsRepository {
			/**
			 * Override get() to return a deterministic API key without touching the DB.
			 *
			 * @param string $key      Setting key.
			 * @param mixed  $fallback Default value.
			 * @return mixed
			 */
			public function get( string $key, mixed $fallback = null ): mixed {
				if ( 'rapidapi_key' === $key ) {
					return 'test-rapidapi-key';
				}
				if ( 'api_base_url' === $key ) {
					return 'https://astrologer.p.rapidapi.com';
				}
				return parent::get( $key, $fallback );
			}
		};

		$this->service = new ChartService( new ApiClient( $settings ) );

		$this->fired_actions = array();
	}

	/**
	 * Create a test SubjectDTO.
	 *
	 * @return SubjectDTO
	 */
	private function make_subject(): SubjectDTO {
		return new SubjectDTO(
			new BirthData(
				name: 'Test',
				year: 1990,
				month: 5,
				day: 15,
				hour: 14,
				minute: 30,
				location: new GeoLocation(
					latitude: 41.9028,
					longitude: 12.4964,
					timezone: 'Europe/Rome',
					city: 'Rome',
					nation: 'IT',
				),
			),
		);
	}

	/**
	 * Create a test ChartRequestDTO.
	 *
	 * @param bool $svg Whether to request SVG.
	 * @return ChartRequestDTO
	 */
	private function make_chart_request( bool $svg = true ): ChartRequestDTO {
		return new ChartRequestDTO(
			subject: $this->make_subject(),
			options: ChartOptions::defaults(),
			svg: $svg,
		);
	}

	/**
	 * Intercept HTTP requests with a mock response.
	 *
	 * @param int                  $code HTTP status code.
	 * @param array<string,mixed>  $body JSON body as array.
	 */
	private function mock_http( int $code, array $body ): void {
		add_filter(
			'pre_http_request',
			static function () use ( $code, $body ): array {
				return array(
					'response' => array( 'code' => $code ),
					'body'     => wp_json_encode( $body ),
				);
			},
		);
	}

	/**
	 * Intercept HTTP requests with a WP_Error.
	 */
	private function mock_http_error(): void {
		add_filter(
			'pre_http_request',
			static function (): \WP_Error {
				return new \WP_Error( 'http_request_failed', 'Connection timed out.' );
			},
		);
	}

	/**
	 * Track actions fired during a callback scope.
	 *
	 * @param string ...$hooks Hook names to track.
	 */
	private function track_actions( string ...$hooks ): void {
		foreach ( $hooks as $hook ) {
			add_action(
				$hook,
				function ( ...$args ) use ( $hook ): void {
					$this->fired_actions[] = array(
						'hook'  => $hook,
						'args'  => $args,
					);
				},
				10,
				10,
			);
		}
	}

	// ---------------------------------------------------------------------
	// Tests
	// ---------------------------------------------------------------------

	/**
	 * Test subject() returns ChartResponseDTO on success.
	 */
	public function test_subject_returns_dto_on_success(): void {
		$this->mock_http(
			200,
			array(
				'name'     => 'Test Subject',
				'planets'  => array( 'Sun' => array( 'longitude' => 54.5 ) ),
			),
		);

		$result = $this->service->subject( $this->make_subject() );

		$this->assertInstanceOf( ChartResponseDTO::class, $result );
		$this->assertNotNull( $result->raw );
		$this->assertSame( 'Test Subject', $result->raw['name'] );
	}

	/**
	 * Test birthChart() returns SVG when present in response.
	 */
	public function test_birth_chart_returns_svg(): void {
		$this->mock_http(
			200,
			array(
				'svg'       => '<svg xmlns="http://www.w3.org/2000/svg"><circle r="10"/></svg>',
				'positions' => array(),
				'aspects'   => array(),
			),
		);

		$result = $this->service->birthChart( $this->make_chart_request( true ) );

		$this->assertInstanceOf( ChartResponseDTO::class, $result );
		$this->assertTrue( $result->has_svg() );
		$this->assertStringContainsString( '<svg', $result->svg );
	}

	/**
	 * Test service returns WP_Error on upstream failure.
	 */
	public function test_returns_wp_error_on_failure(): void {
		$this->mock_http_error();

		$result = $this->service->birthChart( $this->make_chart_request() );

		$this->assertInstanceOf( \WP_Error::class, $result );
	}

	/**
	 * Test hooks are fired in correct order for a successful call.
	 */
	public function test_hooks_fired_in_order_on_success(): void {
		$this->mock_http( 200, array( 'positions' => array() ) );

		$this->track_actions(
			'astrologer_api/before_chart_request',
			'astrologer_api/after_chart_response',
		);

		$this->service->birthChart( $this->make_chart_request() );

		$this->assertCount( 2, $this->fired_actions );
		$this->assertSame( 'astrologer_api/before_chart_request', $this->fired_actions[0]['hook'] );
		$this->assertSame( 'birth_chart', $this->fired_actions[0]['args'][0] );
		$this->assertSame( 'astrologer_api/after_chart_response', $this->fired_actions[1]['hook'] );
	}

	/**
	 * Test chart_request_failed action fires on error.
	 */
	public function test_failed_action_fired_on_error(): void {
		$this->mock_http_error();
		$this->track_actions( 'astrologer_api/chart_request_failed' );

		$this->service->birthChart( $this->make_chart_request() );

		$this->assertCount( 1, $this->fired_actions );
		$this->assertSame( 'astrologer_api/chart_request_failed', $this->fired_actions[0]['hook'] );
		$this->assertSame( 'birth_chart', $this->fired_actions[0]['args'][0] );
		$this->assertInstanceOf( \WP_Error::class, $this->fired_actions[0]['args'][1] );
	}

	/**
	 * Test chart_request_args filter modifies payload.
	 */
	public function test_request_args_filter_modifies_payload(): void {
		$captured = null;

		add_filter(
			'astrologer_api/chart_request_args',
			static function ( array $payload ) use ( &$captured ): array {
				$captured = $payload;
				$payload['custom_injected'] = true;
				return $payload;
			},
		);

		$this->mock_http( 200, array( 'positions' => array() ) );

		$this->service->birthChart( $this->make_chart_request() );

		$this->assertIsArray( $captured );
		$this->assertArrayHasKey( 'custom_injected', $captured );
	}

	/**
	 * Test chart_response filter can modify the DTO.
	 */
	public function test_response_filter_modifies_dto(): void {
		$this->mock_http(
			200,
			array(
				'positions' => array(),
				'ai_context' => 'Original text',
			),
		);

		add_filter(
			'astrologer_api/chart_response',
			static function ( ChartResponseDTO $dto ): ChartResponseDTO {
				// Return a new DTO with modified ai_context — readonly means we need from_array.
				return ChartResponseDTO::from_array(
					array_merge( $dto->raw, array( 'ai_context' => 'Filtered text' ) ),
				);
			},
		);

		$result = $this->service->birthChart( $this->make_chart_request() );

		$this->assertInstanceOf( ChartResponseDTO::class, $result );
		$this->assertSame( 'Filtered text', $result->ai_context );
	}

	/**
	 * Test synastry endpoint uses two subjects.
	 */
	public function test_synastry_chart(): void {
		$this->mock_http( 200, array( 'aspects' => array( array( 'type' => 'conjunction' ) ) ) );

		$dto = new SynastryRequestDTO(
			first_subject: $this->make_subject(),
			second_subject: $this->make_subject(),
			options: ChartOptions::defaults(),
		);

		$result = $this->service->synastryChart( $dto );

		$this->assertInstanceOf( ChartResponseDTO::class, $result );
	}

	/**
	 * Test compatibility score endpoint.
	 */
	public function test_compatibility_score(): void {
		$this->mock_http(
			200,
			array(
				'score'       => 75,
				'description' => 'Good match',
			),
		);

		$dto = new CompatibilityRequestDTO(
			first_subject: $this->make_subject(),
			second_subject: $this->make_subject(),
		);

		$result = $this->service->compatibilityScore( $dto );

		$this->assertInstanceOf( ChartResponseDTO::class, $result );
		$this->assertSame( 75, $result->raw['score'] );
	}

	/**
	 * Test transit endpoint.
	 */
	public function test_transit_chart(): void {
		$this->mock_http( 200, array( 'positions' => array() ) );

		$dto = new TransitRequestDTO(
			first_subject: $this->make_subject(),
			transit_subject: $this->make_subject(),
			options: ChartOptions::defaults(),
		);

		$result = $this->service->transitChart( $dto );

		$this->assertInstanceOf( ChartResponseDTO::class, $result );
	}

	/**
	 * Test composite endpoint.
	 */
	public function test_composite_chart(): void {
		$this->mock_http( 200, array( 'positions' => array() ) );

		$dto = new CompositeRequestDTO(
			first_subject: $this->make_subject(),
			second_subject: $this->make_subject(),
			composite_type: 'Midpoint',
			options: ChartOptions::defaults(),
		);

		$result = $this->service->compositeChart( $dto );

		$this->assertInstanceOf( ChartResponseDTO::class, $result );
	}

	/**
	 * Test solar return endpoint.
	 */
	public function test_solar_return_chart(): void {
		$this->mock_http( 200, array( 'positions' => array() ) );

		$dto = new ReturnRequestDTO(
			subject: $this->make_subject(),
			year: 2025,
			month: null,
			day: null,
			iso_datetime: null,
			wheel_type: 'dual',
			return_location: null,
			options: ChartOptions::defaults(),
		);

		$result = $this->service->solarReturnChart( $dto );

		$this->assertInstanceOf( ChartResponseDTO::class, $result );
	}

	/**
	 * Test lunar return endpoint.
	 */
	public function test_lunar_return_chart(): void {
		$this->mock_http( 200, array( 'positions' => array() ) );

		$dto = new ReturnRequestDTO(
			subject: $this->make_subject(),
			year: 2025,
			month: 6,
			day: null,
			iso_datetime: null,
			wheel_type: 'dual',
			return_location: null,
			options: ChartOptions::defaults(),
		);

		$result = $this->service->lunarReturnChart( $dto );

		$this->assertInstanceOf( ChartResponseDTO::class, $result );
	}

	/**
	 * Test now chart endpoint.
	 */
	public function test_now_chart(): void {
		$this->mock_http( 200, array( 'positions' => array() ) );

		$dto = new NowRequestDTO( options: ChartOptions::defaults() );

		$result = $this->service->nowChart( $dto );

		$this->assertInstanceOf( ChartResponseDTO::class, $result );
	}

	/**
	 * Test moon phase endpoint.
	 */
	public function test_moon_phase(): void {
		$this->mock_http(
			200,
			array(
				'phase_name'   => 'Waxing Crescent',
				'illumination' => 25.5,
			),
		);

		$dto = new MoonPhaseRequestDTO(
			year: 2025,
			month: 6,
			day: 15,
			hour: 12,
			minute: 0,
			latitude: 41.9028,
			longitude: 12.4964,
			timezone: 'Europe/Rome',
		);

		$result = $this->service->moonPhase( $dto );

		$this->assertInstanceOf( ChartResponseDTO::class, $result );
		$this->assertSame( 'Waxing Crescent', $result->raw['phase_name'] );
	}

	/**
	 * Test moon phase now UTC endpoint.
	 */
	public function test_moon_phase_now_utc(): void {
		$this->mock_http(
			200,
			array( 'phase_name' => 'Full Moon' ),
		);

		$result = $this->service->moonPhaseNowUtc();

		$this->assertInstanceOf( ChartResponseDTO::class, $result );
	}

	/**
	 * Test context endpoint works like regular endpoint but with /context suffix.
	 */
	public function test_subject_context(): void {
		$this->mock_http(
			200,
			array(
				'ai_context' => 'Test subject is born with Sun in Taurus...',
				'positions'  => array(),
			),
		);

		$result = $this->service->subjectContext( $this->make_subject() );

		$this->assertInstanceOf( ChartResponseDTO::class, $result );
		$this->assertTrue( $result->has_ai_context() );
	}

	/**
	 * Test health() returns raw array, not ChartResponseDTO.
	 */
	public function test_health_returns_raw_array(): void {
		$this->mock_http( 200, array( 'status' => 'ok' ) );

		$result = $this->service->health();

		$this->assertIsArray( $result );
		$this->assertSame( 'ok', $result['status'] );
	}

	/**
	 * Test mcp() returns raw array on success.
	 */
	public function test_mcp_returns_raw_array(): void {
		$this->mock_http( 200, array( 'jsonrpc' => '2.0', 'result' => 'ok' ) );

		$result = $this->service->mcp( array( 'jsonrpc' => '2.0', 'method' => 'test' ) );

		$this->assertIsArray( $result );
		$this->assertSame( '2.0', $result['jsonrpc'] );
	}

	/**
	 * Test mcp() returns WP_Error on failure.
	 */
	public function test_mcp_returns_error_on_failure(): void {
		$this->mock_http_error();

		$result = $this->service->mcp( array( 'jsonrpc' => '2.0', 'method' => 'test' ) );

		$this->assertInstanceOf( \WP_Error::class, $result );
	}

	/**
	 * Test that all chart-type methods call the right hook chart_type.
	 */
	public function test_chart_type_identifiers_are_correct(): void {
		$expected_types = array();

		$this->track_actions( 'astrologer_api/before_chart_request' );

		// Test a few key chart types.
		$capture_type = function () use ( &$expected_types ): void {
			add_action(
				'astrologer_api/before_chart_request',
				static function ( string $type ) use ( &$expected_types ): void {
					$expected_types[] = $type;
				},
			);
		};

		$capture_type();

		$this->mock_http( 200, array( 'positions' => array() ) );
		$this->service->birthChartData( $this->make_chart_request() );

		$this->mock_http( 200, array( 'positions' => array() ) );
		$this->service->synastryChartData(
			new SynastryRequestDTO(
				first_subject: $this->make_subject(),
				second_subject: $this->make_subject(),
				options: ChartOptions::defaults(),
			),
		);

		$this->mock_http( 200, array( 'positions' => array() ) );
		$this->service->transitChartData(
			new TransitRequestDTO(
				first_subject: $this->make_subject(),
				transit_subject: $this->make_subject(),
				options: ChartOptions::defaults(),
			),
		);

		$this->assertSame(
			array( 'birth_chart_data', 'synastry_chart_data', 'transit_chart_data' ),
			$expected_types,
		);
	}
}
