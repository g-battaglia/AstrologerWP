<?php
/**
 * Integration tests for ChartRepository.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Tests\Integration\Repository;

use Astrologer\Api\DTO\ChartRequestDTO;
use Astrologer\Api\DTO\ChartResponseDTO;
use Astrologer\Api\DTO\SubjectDTO;
use Astrologer\Api\Enums\ChartType;
use Astrologer\Api\Repository\ChartRepository;
use Astrologer\Api\ValueObjects\BirthData;
use Astrologer\Api\ValueObjects\ChartOptions;
use Astrologer\Api\ValueObjects\GeoLocation;
use WP_UnitTestCase;

/**
 * @covers \Astrologer\Api\Repository\ChartRepository
 * @covers \Astrologer\Api\ValueObjects\ChartRecord
 */
class ChartRepositoryTest extends WP_UnitTestCase {

	/**
	 * Repository under test.
	 *
	 * @var ChartRepository
	 */
	private ChartRepository $repo;

	/**
	 * Test user ID.
	 *
	 * @var int
	 */
	private int $user_id;

	/**
	 * Set up each test.
	 */
	public function setUp(): void {
		parent::setUp();

		$this->user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );

		$this->repo = new ChartRepository();
		$this->repo->boot();

		// Ensure the CPT is registered for tests.
		$pt = new \Astrologer\Api\PostType\AstrologerChartPostType();
		$pt->register();
	}

	/**
	 * Tear down each test.
	 */
	public function tearDown(): void {
		// Clean up any chart posts created during tests.
		$posts = get_posts(
			array(
				'post_type'   => 'astrologer_chart',
				'post_status' => 'any',
				'numberposts' => -1,
			)
		);

		foreach ( $posts as $post ) {
			wp_delete_post( $post->ID, true );
		}

		parent::tearDown();
	}

	/**
	 * Helper to create a test DTO.
	 *
	 * @param ChartType $type Chart type to use.
	 * @return ChartRequestDTO
	 */
	private function make_dto( ChartType $type = ChartType::Natal ): ChartRequestDTO {
		$birth_data = new BirthData(
			name: 'Test Subject',
			year: 1990,
			month: 6,
			day: 15,
			hour: 10,
			minute: 30,
			location: new GeoLocation(
				latitude: 41.9028,
				longitude: 12.4964,
				timezone: 'Europe/Rome',
				city: 'Rome',
				nation: 'IT',
			),
		);

		return new ChartRequestDTO(
			subject: new SubjectDTO( $birth_data ),
			options: ChartOptions::defaults(),
			type: $type,
		);
	}

	/**
	 * Create returns a valid post ID.
	 */
	public function test_create_returns_post_id(): void {
		$dto     = $this->make_dto();
		$post_id = $this->repo->create( $dto, $this->user_id );

		$this->assertIsInt( $post_id );
		$this->assertGreaterThan( 0, $post_id );
	}

	/**
	 * Created chart has private status by default.
	 */
	public function test_create_sets_private_status(): void {
		$dto     = $this->make_dto();
		$post_id = $this->repo->create( $dto, $this->user_id );

		$post = get_post( $post_id );

		$this->assertSame( 'private', $post->post_status );
	}

	/**
	 * Created chart has correct post type.
	 */
	public function test_create_sets_correct_post_type(): void {
		$dto     = $this->make_dto();
		$post_id = $this->repo->create( $dto, $this->user_id );

		$post = get_post( $post_id );

		$this->assertSame( 'astrologer_chart', $post->post_type );
	}

	/**
	 * Created chart stores correct chart type meta.
	 */
	public function test_create_stores_chart_type_meta(): void {
		$dto     = $this->make_dto( ChartType::Synastry );
		$post_id = $this->repo->create( $dto, $this->user_id );

		$this->assertSame( 'synastry', get_post_meta( $post_id, 'chart_type', true ) );
	}

	/**
	 * Created chart stores birth data meta.
	 */
	public function test_create_stores_birth_data_meta(): void {
		$dto     = $this->make_dto();
		$post_id = $this->repo->create( $dto, $this->user_id );

		$meta = get_post_meta( $post_id, 'birth_data', true );

		$this->assertIsArray( $meta );
		$this->assertSame( 'Test Subject', $meta['name'] );
		$this->assertSame( 1990, $meta['year'] );
	}

	/**
	 * Created chart stores chart options meta.
	 */
	public function test_create_stores_chart_options_meta(): void {
		$dto     = $this->make_dto();
		$post_id = $this->repo->create( $dto, $this->user_id );

		$meta = get_post_meta( $post_id, 'chart_options', true );

		$this->assertIsArray( $meta );
		$this->assertArrayHasKey( 'language', $meta );
		$this->assertArrayHasKey( 'houses_system_identifier', $meta );
	}

	/**
	 * Find returns a ChartRecord with all fields populated.
	 */
	public function test_find_returns_chart_record(): void {
		$dto     = $this->make_dto();
		$post_id = $this->repo->create( $dto, $this->user_id );

		$record = $this->repo->find( $post_id );

		$this->assertNotNull( $record );
		$this->assertSame( $post_id, $record->id );
		$this->assertSame( ChartType::Natal, $record->chart_type );
		$this->assertSame( 'Test Subject', $record->birth_data->name );
		$this->assertSame( 1990, $record->birth_data->year );
		$this->assertSame( $this->user_id, $record->author_id );
		$this->assertSame( 'private', $record->status );
	}

	/**
	 * Find returns null for non-existent post.
	 */
	public function test_find_returns_null_for_nonexistent(): void {
		$record = $this->repo->find( 999999 );

		$this->assertNull( $record );
	}

	/**
	 * Find returns null for wrong post type.
	 */
	public function test_find_returns_null_for_wrong_post_type(): void {
		$wrong_post_id = self::factory()->post->create(
			array( 'post_type' => 'post' )
		);

		$record = $this->repo->find( $wrong_post_id );

		$this->assertNull( $record );
	}

	/**
	 * Update changes post title.
	 */
	public function test_update_changes_title(): void {
		$dto     = $this->make_dto();
		$post_id = $this->repo->create( $dto, $this->user_id );

		$this->repo->update( $post_id, array( 'title' => 'New Title' ) );

		$post = get_post( $post_id );
		$this->assertSame( 'New Title', $post->post_title );
	}

	/**
	 * Update changes chart type meta.
	 */
	public function test_update_changes_chart_type(): void {
		$dto     = $this->make_dto();
		$post_id = $this->repo->create( $dto, $this->user_id );

		$this->repo->update( $post_id, array( 'chart_type' => 'transit' ) );

		$this->assertSame( 'transit', get_post_meta( $post_id, 'chart_type', true ) );
	}

	/**
	 * Update stores response data.
	 */
	public function test_update_stores_response_data(): void {
		$dto     = $this->make_dto();
		$post_id = $this->repo->create( $dto, $this->user_id );

		$response = new ChartResponseDTO(
			svg: '<svg></svg>',
			positions: array( 'sun' => array( 'degree' => 85 ) ),
			houses: null,
			aspects: null,
			distributions: null,
			ai_context: null,
			raw: array( 'test' => true ),
		);

		$this->repo->update(
			$post_id,
			array(
				'response_svg'  => $response->svg,
				'response_data' => $response->to_array(),
			)
		);

		$record = $this->repo->find( $post_id );
		$this->assertSame( '<svg></svg>', $record->response_svg );
		$this->assertIsArray( $record->response_data );
		$this->assertArrayHasKey( 'svg', $record->response_data );
	}

	/**
	 * Delete removes the chart post.
	 */
	public function test_delete_removes_post(): void {
		$dto     = $this->make_dto();
		$post_id = $this->repo->create( $dto, $this->user_id );

		$result = $this->repo->delete( $post_id );

		$this->assertTrue( $result );
		$this->assertNull( get_post( $post_id ) );
	}

	/**
	 * Delete returns false for non-chart post.
	 */
	public function test_delete_returns_false_for_wrong_type(): void {
		$wrong_post_id = self::factory()->post->create(
			array( 'post_type' => 'post' )
		);

		$this->assertFalse( $this->repo->delete( $wrong_post_id ) );
	}

	/**
	 * List by user returns only the user's charts.
	 */
	public function test_list_by_user_returns_only_user_charts(): void {
		$other_user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );

		$this->repo->create( $this->make_dto(), $this->user_id );
		$this->repo->create( $this->make_dto( ChartType::Transit ), $this->user_id );
		$this->repo->create( $this->make_dto( ChartType::Synastry ), $other_user_id );

		$records = $this->repo->list_by_user( $this->user_id );

		$this->assertCount( 2, $records );

		foreach ( $records as $record ) {
			$this->assertSame( $this->user_id, $record->author_id );
		}
	}

	/**
	 * Is owner returns true for the chart's author.
	 */
	public function test_is_owner_returns_true_for_author(): void {
		$dto     = $this->make_dto();
		$post_id = $this->repo->create( $dto, $this->user_id );

		$this->assertTrue( $this->repo->is_owner( $post_id, $this->user_id ) );
	}

	/**
	 * Is owner returns false for a different user.
	 */
	public function test_is_owner_returns_false_for_other_user(): void {
		$other_user_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );

		$dto     = $this->make_dto();
		$post_id = $this->repo->create( $dto, $this->user_id );

		$this->assertFalse( $this->repo->is_owner( $post_id, $other_user_id ) );
	}

	/**
	 * Chart title is built from subject name and chart type.
	 */
	public function test_chart_title_contains_name_and_type(): void {
		$dto     = $this->make_dto();
		$post_id = $this->repo->create( $dto, $this->user_id );

		$post = get_post( $post_id );

		$this->assertStringContainsString( 'Test Subject', $post->post_title );
		$this->assertStringContainsString( 'Natal Chart', $post->post_title );
	}

	/**
	 * Create with response stores SVG and data.
	 */
	public function test_create_with_response_stores_all(): void {
		$dto      = $this->make_dto();
		$response = new ChartResponseDTO(
			svg: '<svg><circle/></svg>',
			positions: array( 'sun' => array( 'sign' => 'Gemini' ) ),
			houses: array( array( 'cusp' => 0 ) ),
			aspects: null,
			distributions: null,
			ai_context: 'You are a Gemini sun.',
			raw: array( 'svg' => '<svg><circle/></svg>' ),
		);

		$post_id = $this->repo->create( $dto, $this->user_id, $response );

		$record = $this->repo->find( $post_id );

		$this->assertNotNull( $record );
		$this->assertSame( '<svg><circle/></svg>', $record->response_svg );
		$this->assertIsArray( $record->response_data );
		$this->assertSame( 'Gemini', $record->response_data['positions']['sun']['sign'] );
	}

	/**
	 * Update with no-op does not crash.
	 */
	public function test_update_with_empty_changes_is_noop(): void {
		$dto     = $this->make_dto();
		$post_id = $this->repo->create( $dto, $this->user_id );

		$this->repo->update( $post_id, array() );

		$record = $this->repo->find( $post_id );
		$this->assertNotNull( $record );
		$this->assertSame( ChartType::Natal, $record->chart_type );
	}
}
