<?php
/**
 * Integration tests for SettingsRepository.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Tests\Integration\Repository;

use Astrologer\Api\Repository\SettingsRepository;
use Astrologer\Api\Support\Encryption\EncryptionService;
use WP_UnitTestCase;

/**
 * @covers \Astrologer\Api\Repository\SettingsRepository
 */
class SettingsRepositoryTest extends WP_UnitTestCase {

	/**
	 * Repository under test.
	 *
	 * @var SettingsRepository
	 */
	private SettingsRepository $repo;

	/**
	 * Set up each test.
	 */
	public function setUp(): void {
		parent::setUp();

		// Clean the option before each test.
		delete_option( 'astrologer_api_settings' );

		$encryption    = new EncryptionService();
		$this->repo    = new SettingsRepository( $encryption );
	}

	/**
	 * Tear down each test.
	 */
	public function tearDown(): void {
		delete_option( 'astrologer_api_settings' );
		parent::tearDown();
	}

	/**
	 * All returns defaults when option is empty.
	 */
	public function test_all_returns_defaults_when_empty(): void {
		$all = $this->repo->all();

		$this->assertSame( '', $all['rapidapi_key'] );
		$this->assertSame( '', $all['geonames_username'] );
		$this->assertSame( 'https://astrologer.p.rapidapi.com', $all['api_base_url'] );
		$this->assertSame( 'EN', $all['language'] );
		$this->assertSame( 'modern_western', $all['school'] );
		$this->assertSame( 'basic', $all['ui_level'] );
		$this->assertIsArray( $all['chart_options'] );
		$this->assertIsArray( $all['cron'] );
		$this->assertIsArray( $all['integrations'] );
	}

	/**
	 * Set and get roundtrip for non-sensitive value.
	 */
	public function test_set_and_get_non_sensitive(): void {
		$this->repo->set( 'language', 'IT' );

		$this->assertSame( 'IT', $this->repo->get( 'language' ) );
	}

	/**
	 * Set and get roundtrip for sensitive value (encrypted at rest).
	 */
	public function test_set_and_get_sensitive_value_encrypted_at_rest(): void {
		$this->repo->set( 'rapidapi_key', 'my-secret-key-123' );

		// The raw option should NOT contain the plaintext.
		$raw = get_option( 'astrologer_api_settings', array() );
		$this->assertNotSame( 'my-secret-key-123', $raw['rapidapi_key'] );
		$this->assertNotSame( '', $raw['rapidapi_key'] );

		// But get() should decrypt to plaintext.
		$this->assertSame( 'my-secret-key-123', $this->repo->get( 'rapidapi_key' ) );
	}

	/**
	 * Get returns fallback for missing key.
	 */
	public function test_get_returns_fallback_for_missing_key(): void {
		$this->assertSame( 'fallback', $this->repo->get( 'nonexistent', 'fallback' ) );
	}

	/**
	 * Get returns null by default for missing key.
	 */
	public function test_get_returns_null_for_missing_key(): void {
		$this->assertNull( $this->repo->get( 'nonexistent' ) );
	}

	/**
	 * Update merges multiple values at once.
	 */
	public function test_update_merges_multiple_values(): void {
		$this->repo->update(
			array(
				'language' => 'FR',
				'school'   => 'vedic',
			)
		);

		$this->assertSame( 'FR', $this->repo->get( 'language' ) );
		$this->assertSame( 'vedic', $this->repo->get( 'school' ) );
	}

	/**
	 * Update preserves existing values.
	 */
	public function test_update_preserves_existing_values(): void {
		$this->repo->set( 'language', 'IT' );
		$this->repo->update( array( 'school' => 'traditional' ) );

		$this->assertSame( 'IT', $this->repo->get( 'language' ) );
		$this->assertSame( 'traditional', $this->repo->get( 'school' ) );
	}

	/**
	 * Clearing a sensitive field stores empty string.
	 */
	public function test_clear_sensitive_field_stores_empty(): void {
		$this->repo->set( 'rapidapi_key', 'secret' );
		$this->repo->set( 'rapidapi_key', '' );

		$this->assertSame( '', $this->repo->get( 'rapidapi_key' ) );
	}

	/**
	 * Is configured returns false without API key.
	 */
	public function test_is_configured_returns_false_without_key(): void {
		$this->assertFalse( $this->repo->is_configured() );
	}

	/**
	 * Is configured returns true with API key.
	 */
	public function test_is_configured_returns_true_with_key(): void {
		$this->repo->set( 'rapidapi_key', 'valid-key' );

		$this->assertTrue( $this->repo->is_configured() );
	}

	/**
	 * Reset restores all defaults.
	 */
	public function test_reset_restores_defaults(): void {
		$this->repo->set( 'rapidapi_key', 'some-key' );
		$this->repo->set( 'language', 'DE' );

		$this->repo->reset();

		$this->assertSame( '', $this->repo->get( 'rapidapi_key' ) );
		$this->assertSame( 'EN', $this->repo->get( 'language' ) );
	}

	/**
	 * Settings defaults can be filtered.
	 */
	public function test_settings_defaults_filter(): void {
		$callback = static function ( array $defaults ): array {
			$defaults['language'] = 'FR';

			return $defaults;
		};

		add_filter( 'astrologer_api/settings_defaults', $callback );

		$all = $this->repo->all();

		remove_filter( 'astrologer_api/settings_defaults', $callback );

		$this->assertSame( 'FR', $all['language'] );
	}

	/**
	 * Geonames username is also encrypted.
	 */
	public function test_geonames_username_is_encrypted(): void {
		$this->repo->set( 'geonames_username', 'myuser' );

		$raw = get_option( 'astrologer_api_settings', array() );
		$this->assertNotSame( 'myuser', $raw['geonames_username'] );

		$this->assertSame( 'myuser', $this->repo->get( 'geonames_username' ) );
	}

	/**
	 * Cron sub-array is stored correctly.
	 */
	public function test_cron_settings_stored_correctly(): void {
		$this->repo->set(
			'cron',
			array(
				'daily_transits'        => true,
				'solar_return_reminder' => true,
				'daily_moon_phase'      => false,
			)
		);

		$cron = $this->repo->get( 'cron' );

		$this->assertTrue( $cron['daily_transits'] );
		$this->assertTrue( $cron['solar_return_reminder'] );
		$this->assertFalse( $cron['daily_moon_phase'] );
	}
}
