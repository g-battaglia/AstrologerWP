<?php
/**
 * CronRegistry — schedules and wires all plugin cron events.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Cron;

use Astrologer\Api\Cron\Handlers\DailyMoonPhaseHandler;
use Astrologer\Api\Cron\Handlers\DailyTransitsHandler;
use Astrologer\Api\Cron\Handlers\SolarReturnReminderHandler;
use Astrologer\Api\Support\Contracts\Bootable;

/**
 * Registers plugin cron events and binds handlers to their hook names.
 *
 * Scheduling happens lazily on the WordPress `init` action so the wp-cron
 * subsystem is fully loaded. Deactivation / uninstall unschedules via
 * {@see \Astrologer\Api\Activation\Deactivator}.
 */
final class CronRegistry implements Bootable {

	/**
	 * Map of hook name → scheduled UTC time of day (HH:MM).
	 *
	 * @var array<string,string>
	 */
	private const SCHEDULES = array(
		DailyTransitsHandler::HOOK       => '00:05',
		DailyMoonPhaseHandler::HOOK      => '00:10',
		SolarReturnReminderHandler::HOOK => '08:00',
	);

	/**
	 * Daily-transits handler.
	 *
	 * @var DailyTransitsHandler
	 */
	private DailyTransitsHandler $transits;

	/**
	 * Daily moon-phase handler.
	 *
	 * @var DailyMoonPhaseHandler
	 */
	private DailyMoonPhaseHandler $moon_phase;

	/**
	 * Solar-return reminder handler.
	 *
	 * @var SolarReturnReminderHandler
	 */
	private SolarReturnReminderHandler $solar_return;

	/**
	 * Constructor.
	 *
	 * @param DailyTransitsHandler       $transits     Daily-transits handler.
	 * @param DailyMoonPhaseHandler      $moon_phase   Daily moon-phase handler.
	 * @param SolarReturnReminderHandler $solar_return Solar-return reminder handler.
	 */
	public function __construct(
		DailyTransitsHandler $transits,
		DailyMoonPhaseHandler $moon_phase,
		SolarReturnReminderHandler $solar_return
	) {
		$this->transits     = $transits;
		$this->moon_phase   = $moon_phase;
		$this->solar_return = $solar_return;
	}

	/**
	 * Register scheduling and handler hooks.
	 */
	public function boot(): void {
		add_action( 'init', array( $this, 'schedule_events' ) );

		add_action( DailyTransitsHandler::HOOK, array( $this->transits, 'run' ) );
		add_action( DailyMoonPhaseHandler::HOOK, array( $this->moon_phase, 'run' ) );
		add_action( SolarReturnReminderHandler::HOOK, array( $this->solar_return, 'run' ) );
	}

	/**
	 * Ensure every plugin cron event is scheduled exactly once.
	 *
	 * Idempotent: calling this repeatedly is a no-op after the first call.
	 */
	public function schedule_events(): void {
		foreach ( self::SCHEDULES as $hook => $time ) {
			if ( false !== wp_next_scheduled( $hook ) ) {
				continue;
			}

			$timestamp = $this->next_utc_timestamp( $time );

			wp_schedule_event( $timestamp, 'daily', $hook );
		}
	}

	/**
	 * List the cron hooks managed by this registry (for tests / doctor).
	 *
	 * @return list<string>
	 */
	public static function hooks(): array {
		return array_keys( self::SCHEDULES );
	}

	/**
	 * Compute the next UTC timestamp for a given "HH:MM" time of day.
	 *
	 * @param string $time_of_day Time in HH:MM 24-hour format (UTC).
	 * @return int Unix timestamp.
	 */
	private function next_utc_timestamp( string $time_of_day ): int {
		$parts = explode( ':', $time_of_day );
		$hour  = isset( $parts[0] ) ? (int) $parts[0] : 0;
		$min   = isset( $parts[1] ) ? (int) $parts[1] : 0;

		$now = new \DateTimeImmutable( 'now', new \DateTimeZone( 'UTC' ) );

		$next = $now->setTime( $hour, $min, 0 );

		if ( $next <= $now ) {
			$next = $next->modify( '+1 day' );
		}

		return $next->getTimestamp();
	}
}
