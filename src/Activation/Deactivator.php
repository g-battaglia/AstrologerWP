<?php
/**
 * Plugin deactivation handler.
 *
 * Flushes rewrite rules and unschedules cron events.
 * Does NOT remove capabilities or data — that is the Uninstaller's job.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Activation;

/**
 * Runs when the plugin is deactivated.
 */
final class Deactivator {

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
	 * Execute deactivation routines.
	 *
	 * @return void
	 */
	public static function run(): void {
		self::unschedule_cron();
		flush_rewrite_rules( false );
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
