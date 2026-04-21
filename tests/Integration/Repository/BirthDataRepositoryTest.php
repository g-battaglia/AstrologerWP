<?php
/**
 * Integration tests for BirthDataRepository.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Tests\Integration\Repository;

use Astrologer\Api\Repository\BirthDataRepository;
use Astrologer\Api\ValueObjects\BirthData;
use Astrologer\Api\ValueObjects\GeoLocation;
use WP_UnitTestCase;

/**
 * @covers \Astrologer\Api\Repository\BirthDataRepository
 */
class BirthDataRepositoryTest extends WP_UnitTestCase {

	/**
	 * Repository under test.
	 *
	 * @var BirthDataRepository
	 */
	private BirthDataRepository $repo;

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

		$this->repo    = new BirthDataRepository();
		$this->user_id = self::factory()->user->create(
			array( 'role' => 'subscriber' )
		);
	}

	/**
	 * Tear down each test.
	 */
	public function tearDown(): void {
		$this->repo->clear_for_user( $this->user_id );
		wp_delete_user( $this->user_id );
		parent::tearDown();
	}

	/**
	 * Get for user returns null when no data stored.
	 */
	public function test_get_for_user_returns_null_when_empty(): void {
		$this->assertNull( $this->repo->get_for_user( $this->user_id ) );
	}

	/**
	 * Set and get roundtrip preserves all fields.
	 */
	public function test_set_and_get_roundtrip(): void {
		$location = new GeoLocation(
			latitude: 41.9028,
			longitude: 12.4964,
			timezone: 'Europe/Rome',
			city: 'Rome',
			nation: 'IT'
		);

		$birth_data = new BirthData(
			name: 'Giacomo',
			year: 1990,
			month: 6,
			day: 15,
			hour: 14,
			minute: 30,
			location: $location
		);

		$this->repo->set_for_user( $this->user_id, $birth_data );

		$result = $this->repo->get_for_user( $this->user_id );

		$this->assertInstanceOf( BirthData::class, $result );
		$this->assertSame( 'Giacomo', $result->name );
		$this->assertSame( 1990, $result->year );
		$this->assertSame( 6, $result->month );
		$this->assertSame( 15, $result->day );
		$this->assertSame( 14, $result->hour );
		$this->assertSame( 30, $result->minute );
		$this->assertSame( 41.9028, $result->location->latitude );
		$this->assertSame( 12.4964, $result->location->longitude );
		$this->assertSame( 'Europe/Rome', $result->location->timezone );
		$this->assertSame( 'Rome', $result->location->city );
		$this->assertSame( 'IT', $result->location->nation );
	}

	/**
	 * Set overwrites previous data.
	 */
	public function test_set_overwrites_previous_data(): void {
		$location = new GeoLocation( 51.5074, -0.1278, 'Europe/London' );

		$first = new BirthData(
			name: 'Alice',
			year: 1985,
			month: 3,
			day: 20,
			hour: 10,
			minute: 0,
			location: $location
		);

		$this->repo->set_for_user( $this->user_id, $first );

		$second = new BirthData(
			name: 'Bob',
			year: 2000,
			month: 1,
			day: 1,
			hour: 0,
			minute: 0,
			location: $location
		);

		$this->repo->set_for_user( $this->user_id, $second );

		$result = $this->repo->get_for_user( $this->user_id );

		$this->assertSame( 'Bob', $result->name );
		$this->assertSame( 2000, $result->year );
	}

	/**
	 * Clear removes stored data.
	 */
	public function test_clear_removes_stored_data(): void {
		$location = new GeoLocation( 48.8566, 2.3522, 'Europe/Paris' );

		$birth_data = new BirthData(
			name: 'Test',
			year: 2000,
			month: 1,
			day: 1,
			hour: 12,
			minute: 0,
			location: $location
		);

		$this->repo->set_for_user( $this->user_id, $birth_data );
		$this->assertNotNull( $this->repo->get_for_user( $this->user_id ) );

		$this->repo->clear_for_user( $this->user_id );

		$this->assertNull( $this->repo->get_for_user( $this->user_id ) );
	}

	/**
	 * Get for user returns null for non-existent user.
	 */
	public function test_get_for_user_returns_null_for_nonexistent_user(): void {
		$this->assertNull( $this->repo->get_for_user( 999999 ) );
	}

	/**
	 * Iso datetime is preserved in roundtrip.
	 */
	public function test_iso_datetime_preserved_in_roundtrip(): void {
		$location = new GeoLocation( 40.7128, -74.0060, 'America/New_York' );

		$birth_data = new BirthData(
			name: 'Test',
			year: 1995,
			month: 12,
			day: 25,
			hour: 8,
			minute: 15,
			location: $location,
			iso_datetime: '1995-12-25T08:15:00-05:00'
		);

		$this->repo->set_for_user( $this->user_id, $birth_data );

		$result = $this->repo->get_for_user( $this->user_id );

		$this->assertSame( '1995-12-25T08:15:00-05:00', $result->iso_datetime );
	}

	/**
	 * Different users have independent birth data.
	 */
	public function test_different_users_independent(): void {
		$other_user_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );

		$location = new GeoLocation( 35.6762, 139.6503, 'Asia/Tokyo' );

		$data_a = new BirthData(
			name: 'UserA',
			year: 1990,
			month: 1,
			day: 1,
			hour: 0,
			minute: 0,
			location: $location
		);

		$data_b = new BirthData(
			name: 'UserB',
			year: 2000,
			month: 6,
			day: 15,
			hour: 12,
			minute: 30,
			location: $location
		);

		$this->repo->set_for_user( $this->user_id, $data_a );
		$this->repo->set_for_user( $other_user_id, $data_b );

		$result_a = $this->repo->get_for_user( $this->user_id );
		$result_b = $this->repo->get_for_user( $other_user_id );

		$this->assertSame( 'UserA', $result_a->name );
		$this->assertSame( 'UserB', $result_b->name );

		// Cleanup.
		$this->repo->clear_for_user( $other_user_id );
		wp_delete_user( $other_user_id );
	}
}
