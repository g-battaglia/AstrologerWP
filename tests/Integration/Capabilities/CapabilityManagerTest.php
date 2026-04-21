<?php
/**
 * Integration tests for CapabilityManager.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Tests\Integration\Capabilities;

use Astrologer\Api\Capabilities\CapabilityManager;
use WP_UnitTestCase;

/**
 * @covers \Astrologer\Api\Capabilities\CapabilityManager
 */
class CapabilityManagerTest extends WP_UnitTestCase {

	/**
	 * Manager under test.
	 *
	 * @var CapabilityManager
	 */
	private CapabilityManager $manager;

	/**
	 * Set up each test.
	 */
	public function setUp(): void {
		parent::setUp();
		$this->manager = new CapabilityManager();
		$this->manager->add_capabilities();
	}

	/**
	 * Tear down each test.
	 */
	public function tearDown(): void {
		$this->manager->remove_capabilities();
		parent::tearDown();
	}

	/**
	 * Test that administrator has all custom capabilities.
	 */
	public function test_administrator_has_all_plugin_caps(): void {
		$admin = self::factory()->user->create_and_get( array( 'role' => 'administrator' ) );

		$expected = array(
			'astrologer_manage_settings',
			'astrologer_calculate_chart',
			'astrologer_save_chart',
			'astrologer_view_any_chart',
			'astrologer_run_cli',
		);

		foreach ( $expected as $cap ) {
			$this->assertTrue(
				user_can( $admin, $cap ),
				"Administrator should have {$cap}"
			);
		}
	}

	/**
	 * Test that subscriber has calculate_chart but not manage_settings.
	 */
	public function test_subscriber_has_limited_caps(): void {
		$sub = self::factory()->user->create_and_get( array( 'role' => 'subscriber' ) );

		$this->assertTrue(
			user_can( $sub, 'astrologer_calculate_chart' ),
			'Subscriber should have astrologer_calculate_chart'
		);
		$this->assertFalse(
			user_can( $sub, 'astrologer_manage_settings' ),
			'Subscriber should NOT have astrologer_manage_settings'
		);
		$this->assertFalse(
			user_can( $sub, 'astrologer_view_any_chart' ),
			'Subscriber should NOT have astrologer_view_any_chart'
		);
		$this->assertFalse(
			user_can( $sub, 'astrologer_run_cli' ),
			'Subscriber should NOT have astrologer_run_cli'
		);
	}

	/**
	 * Test that editor has calculate_chart and save_chart.
	 */
	public function test_editor_has_chart_caps(): void {
		$editor = self::factory()->user->create_and_get( array( 'role' => 'editor' ) );

		$this->assertTrue(
			user_can( $editor, 'astrologer_calculate_chart' ),
			'Editor should have astrologer_calculate_chart'
		);
		$this->assertTrue(
			user_can( $editor, 'astrologer_save_chart' ),
			'Editor should have astrologer_save_chart'
		);
		$this->assertFalse(
			user_can( $editor, 'astrologer_manage_settings' ),
			'Editor should NOT have astrologer_manage_settings'
		);
	}

	/**
	 * Test that author has save_chart.
	 */
	public function test_author_has_save_chart(): void {
		$author = self::factory()->user->create_and_get( array( 'role' => 'author' ) );

		$this->assertTrue(
			user_can( $author, 'astrologer_save_chart' ),
			'Author should have astrologer_save_chart'
		);
		$this->assertFalse(
			user_can( $author, 'astrologer_view_any_chart' ),
			'Author should NOT have astrologer_view_any_chart'
		);
	}

	/**
	 * Test CPT edit meta cap resolves for owner.
	 */
	public function test_owner_can_edit_own_chart(): void {
		$author_id = self::factory()->user->create( array( 'role' => 'author' ) );

		$post_id = wp_insert_post( array(
			'post_type'   => 'astrologer_chart',
			'post_title'  => 'Test Chart',
			'post_status' => 'publish',
			'post_author' => $author_id,
		) );

		$this->assertNotWPError( $post_id );

		wp_set_current_user( $author_id );

		$this->assertTrue(
			current_user_can( 'edit_astrologer_chart', $post_id ),
			'Author should be able to edit their own chart'
		);
	}

	/**
	 * Test CPT edit meta cap denies non-owner without edit_others.
	 */
	public function test_non_owner_cannot_edit_others_chart(): void {
		$owner_id  = self::factory()->user->create( array( 'role' => 'author' ) );
		$other_id  = self::factory()->user->create( array( 'role' => 'author' ) );

		$post_id = wp_insert_post( array(
			'post_type'   => 'astrologer_chart',
			'post_title'  => 'Test Chart',
			'post_status' => 'publish',
			'post_author' => $owner_id,
		) );

		$this->assertNotWPError( $post_id );

		wp_set_current_user( $other_id );

		$this->assertFalse(
			current_user_can( 'edit_astrologer_chart', $post_id ),
			'Author should NOT be able to edit another author\'s chart'
		);
	}

	/**
	 * Test administrator can edit any chart.
	 */
	public function test_admin_can_edit_any_chart(): void {
		$author_id = self::factory()->user->create( array( 'role' => 'author' ) );
		$admin_id  = self::factory()->user->create( array( 'role' => 'administrator' ) );

		$post_id = wp_insert_post( array(
			'post_type'   => 'astrologer_chart',
			'post_title'  => 'Test Chart',
			'post_status' => 'publish',
			'post_author' => $author_id,
		) );

		$this->assertNotWPError( $post_id );

		wp_set_current_user( $admin_id );

		$this->assertTrue(
			current_user_can( 'edit_astrologer_chart', $post_id ),
			'Administrator should be able to edit any chart'
		);
	}

	/**
	 * Test get_all_capabilities returns plugin + CPT caps.
	 */
	public function test_get_all_capabilities(): void {
		$all = $this->manager->get_all_capabilities();

		$this->assertContains( 'astrologer_manage_settings', $all );
		$this->assertContains( 'edit_astrologer_charts', $all );
		$this->assertContains( 'read_astrologer_chart', $all );
	}

	/**
	 * Test get_plugin_capabilities returns only plugin caps.
	 */
	public function test_get_plugin_capabilities(): void {
		$plugin_caps = $this->manager->get_plugin_capabilities();

		$this->assertCount( 5, $plugin_caps );
		$this->assertContains( 'astrologer_manage_settings', $plugin_caps );
		$this->assertNotContains( 'edit_astrologer_charts', $plugin_caps );
	}

	/**
	 * Test capability_map filter is applied.
	 */
	public function test_capability_map_filter(): void {
		$custom = function ( array $map ): array {
			$map['astrologer_manage_settings'] = array( 'administrator', 'editor' );
			return $map;
		};

		add_filter( 'astrologer_api/capability_map', $custom );

		$map = $this->manager->get_capability_map();

		$this->assertSame(
			array( 'administrator', 'editor' ),
			$map['astrologer_manage_settings']
		);

		remove_filter( 'astrologer_api/capability_map', $custom );
	}

	/**
	 * Test remove_capabilities cleans up.
	 */
	public function test_remove_capabilities(): void {
		$this->manager->remove_capabilities();

		$admin = get_role( 'administrator' );

		$this->assertInstanceOf( \WP_Role::class, $admin );
		$this->assertFalse(
			$admin->has_cap( 'astrologer_manage_settings' ),
			'Cap should be removed after remove_capabilities()'
		);

		// Re-add for tearDown.
		$this->manager->add_capabilities();
	}
}
