<?php
/**
 * Plugin uninstall handler.
 *
 * Removes all plugin data: options, transients, user meta, CPT posts,
 * custom capabilities, cron events, and taxonomy terms.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Activation;

use Astrologer\Api\Capabilities\CapabilityManager;

/**
 * Runs when the plugin is deleted via the WordPress admin.
 */
final class Uninstaller {

	/**
	 * Cron hook names managed by this plugin.
	 *
	 * @var list<string>
	 */
	private const CRON_HOOKS = array(
		'astrologer_api_daily_transits',
		'astrologer_api_daily_moon_phase',
		'astrologer_api_solar_return_reminder',
	);

	/**
	 * Options to delete on uninstall.
	 *
	 * @var list<string>
	 */
	private const OPTIONS = array(
		'astrologer_api_settings',
		'astrologer_api_encryption_salt',
		'astrologer_api_setup_wizard_pending',
	);

	/**
	 * Execute full uninstall for the current site.
	 *
	 * @return void
	 */
	public static function run(): void {
		self::delete_cpt_posts();
		self::delete_taxonomy_terms();
		self::remove_capabilities();
		self::delete_options();
		self::delete_transients();
		self::delete_user_meta();
		self::unschedule_cron();
	}

	/**
	 * Delete all astrologer_chart CPT posts (force-delete, bypass trash).
	 *
	 * @return void
	 */
	private static function delete_cpt_posts(): void {
		$posts = get_posts(
			array(
				'post_type'   => 'astrologer_chart',
				'numberposts' => -1,
				'post_status' => 'any',
			)
		);

		foreach ( $posts as $post ) {
			wp_delete_post( $post->ID, true );
		}
	}

	/**
	 * Delete all terms from the astrologer_chart_type taxonomy.
	 *
	 * @return void
	 */
	private static function delete_taxonomy_terms(): void {
		$terms = get_terms(
			array(
				'taxonomy'   => 'astrologer_chart_type',
				'hide_empty' => false,
			)
		);

		if ( is_wp_error( $terms ) ) {
			return;
		}

		foreach ( $terms as $term ) {
			wp_delete_term( $term->term_id, 'astrologer_chart_type' );
		}
	}

	/**
	 * Remove all custom capabilities from all roles.
	 *
	 * @return void
	 */
	private static function remove_capabilities(): void {
		$cap_manager = new CapabilityManager();
		$cap_manager->remove_capabilities();
	}

	/**
	 * Delete known plugin options.
	 *
	 * @return void
	 */
	private static function delete_options(): void {
		foreach ( self::OPTIONS as $option ) {
			delete_option( $option );
		}
	}

	/**
	 * Delete any transients prefixed with astrologer_api_.
	 *
	 * @return void
	 */
	private static function delete_transients(): void {
		global $wpdb;

		$transients = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
				'_transient_astrologer_api_%',
				'_transient_timeout_astrologer_api_%'
			)
		);

		foreach ( $transients as $transient ) {
			delete_option( $transient );
		}
	}

	/**
	 * Delete astrologer_birth_data user meta for all users.
	 *
	 * @return void
	 */
	private static function delete_user_meta(): void {
		global $wpdb;

		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE %s",
				$wpdb->esc_like( 'astrologer_birth_data' ) . '%'
			)
		);
	}

	/**
	 * Unschedule all plugin cron events.
	 *
	 * @return void
	 */
	private static function unschedule_cron(): void {
		foreach ( self::CRON_HOOKS as $hook ) {
			$timestamp = wp_next_scheduled( $hook );

			while ( false !== $timestamp ) {
				wp_unschedule_event( $timestamp, $hook );
				$timestamp = wp_next_scheduled( $hook );
			}
		}
	}
}
