<?php
/**
 * Unit tests for RateLimiter.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Tests\Integration\Services;

use Astrologer\Api\Services\RateLimiter;
use WP_UnitTestCase;

/**
 * @covers \Astrologer\Api\Services\RateLimiter
 */
class RateLimiterTest extends WP_UnitTestCase {

	/**
	 * RateLimiter under test.
	 *
	 * @var RateLimiter
	 */
	private RateLimiter $limiter;

	/**
	 * Set up each test.
	 */
	public function setUp(): void {
		parent::setUp();
		$this->limiter = new RateLimiter();
	}

	/**
	 * Tear down each test — clean transients.
	 */
	public function tearDown(): void {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- Cleanup only.
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
				'_transient_astrologer_rl_%',
				'_transient_timeout_astrologer_rl_%',
			),
		);
		parent::tearDown();
	}

	/**
	 * First request within limit should be allowed.
	 */
	public function test_check_first_request_allowed(): void {
		$result = $this->limiter->check( 'chart', 0, '1.2.3.4', 60, 60 );
		$this->assertTrue( $result );
	}

	/**
	 * Requests up to the limit should all be allowed.
	 */
	public function test_check_up_to_limit_allowed(): void {
		$limit = 10;

		for ( $i = 0; $i < $limit; $i++ ) {
			$result = $this->limiter->check( 'chart', 0, '1.2.3.4', $limit, 60 );
			$this->assertTrue( $result, "Request {$i} should be allowed." );
		}
	}

	/**
	 * 61st call with limit 60 should return false.
	 */
	public function test_check_61st_call_returns_false(): void {
		$limit = 60;

		for ( $i = 0; $i < $limit; $i++ ) {
			$this->limiter->check( 'chart', 0, '5.6.7.8', $limit, 60 );
		}

		// 61st call.
		$result = $this->limiter->check( 'chart', 0, '5.6.7.8', $limit, 60 );
		$this->assertFalse( $result, '61st request should be rate-limited.' );
	}

	/**
	 * Different IPs should have independent counters.
	 */
	public function test_check_different_ips_independent(): void {
		$limit = 5;

		for ( $i = 0; $i < $limit; $i++ ) {
			$this->limiter->check( 'chart', 0, '10.0.0.1', $limit, 60 );
		}

		// First IP should be blocked.
		$this->assertFalse( $this->limiter->check( 'chart', 0, '10.0.0.1', $limit, 60 ) );

		// Second IP should still be allowed.
		$this->assertTrue( $this->limiter->check( 'chart', 0, '10.0.0.2', $limit, 60 ) );
	}

	/**
	 * Different users should have independent counters.
	 */
	public function test_check_different_users_independent(): void {
		$limit = 3;

		for ( $i = 0; $i < $limit; $i++ ) {
			$this->limiter->check( 'chart', 1, '10.0.0.1', $limit, 60 );
		}

		// User 1 should be blocked.
		$this->assertFalse( $this->limiter->check( 'chart', 1, '10.0.0.1', $limit, 60 ) );

		// User 2 at same IP should be allowed.
		$this->assertTrue( $this->limiter->check( 'chart', 2, '10.0.0.1', $limit, 60 ) );
	}

	/**
	 * Different buckets should have independent counters.
	 */
	public function test_check_different_buckets_independent(): void {
		$limit = 3;

		for ( $i = 0; $i < $limit; $i++ ) {
			$this->limiter->check( 'chart', 0, '10.0.0.1', $limit, 60 );
		}

		$this->assertFalse( $this->limiter->check( 'chart', 0, '10.0.0.1', $limit, 60 ) );
		$this->assertTrue( $this->limiter->check( 'moon_phase', 0, '10.0.0.1', $limit, 60 ) );
	}

	/**
	 * Admin user with manage_options should always be exempt.
	 */
	public function test_check_admin_exempt(): void {
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );

		// Exhaust normal limit.
		$limit = 5;
		for ( $i = 0; $i < $limit + 10; $i++ ) {
			$result = $this->limiter->check( 'chart', $admin_id, '10.0.0.1', $limit, 60 );
			$this->assertTrue( $result, 'Admin should always be allowed.' );
		}

		wp_set_current_user( 0 );
	}

	/**
	 * Reset should clear the counter.
	 */
	public function test_reset_clears_counter(): void {
		$limit = 5;

		for ( $i = 0; $i < $limit; $i++ ) {
			$this->limiter->check( 'chart', 0, '9.8.7.6', $limit, 60 );
		}

		// Should be blocked.
		$this->assertFalse( $this->limiter->check( 'chart', 0, '9.8.7.6', $limit, 60 ) );

		// Reset.
		$this->limiter->reset( 'chart', 0, '9.8.7.6' );

		// Should be allowed again.
		$this->assertTrue( $this->limiter->check( 'chart', 0, '9.8.7.6', $limit, 60 ) );
	}

	/**
	 * Rate limit filter should override the default limit.
	 */
	public function test_filter_overrides_limit(): void {
		$override = static function (): int {
			return 2;
		};

		add_filter( 'astrologer_api/rate_limit_per_minute', $override );

		$this->assertTrue( $this->limiter->check( 'chart', 0, '3.3.3.3', 60, 60 ) );
		$this->assertTrue( $this->limiter->check( 'chart', 0, '3.3.3.3', 60, 60 ) );
		$this->assertFalse( $this->limiter->check( 'chart', 0, '3.3.3.3', 60, 60 ) );

		remove_filter( 'astrologer_api/rate_limit_per_minute', $override );
	}

	/**
	 * Transient key should use user ID for logged-in users, not IP hash.
	 */
	public function test_logged_in_user_uses_id_not_ip(): void {
		$limit = 3;

		// Exhaust limit for user 42 with IP A.
		for ( $i = 0; $i < $limit; $i++ ) {
			$this->limiter->check( 'chart', 42, '1.1.1.1', $limit, 60 );
		}
		$this->assertFalse( $this->limiter->check( 'chart', 42, '1.1.1.1', $limit, 60 ) );

		// Same user 42 from different IP should still be blocked (counter is per-user).
		$this->assertFalse( $this->limiter->check( 'chart', 42, '2.2.2.2', $limit, 60 ) );
	}
}
