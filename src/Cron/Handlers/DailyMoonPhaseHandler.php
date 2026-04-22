<?php
/**
 * DailyMoonPhaseHandler — computes the current moon phase and caches it.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Cron\Handlers;

use Astrologer\Api\Services\ChartService;
use Throwable;

/**
 * Cron handler that refreshes the cached moon phase once per day.
 */
final class DailyMoonPhaseHandler {

	/**
	 * Transient key where the last successful response is cached.
	 *
	 * @var string
	 */
	public const TRANSIENT_KEY = 'astrologer_api_daily_moon_phase';

	/**
	 * Cron hook name this handler responds to.
	 *
	 * @var string
	 */
	public const HOOK = 'astrologer_api_daily_moon_phase';

	/**
	 * Default cache TTL (25 hours — slightly longer than the cron interval).
	 *
	 * @var int
	 */
	private const TRANSIENT_TTL = 25 * HOUR_IN_SECONDS;

	/**
	 * Chart service for upstream API calls.
	 *
	 * @var ChartService
	 */
	private ChartService $chart_service;

	/**
	 * Constructor.
	 *
	 * @param ChartService $chart_service Chart service.
	 */
	public function __construct( ChartService $chart_service ) {
		$this->chart_service = $chart_service;
	}

	/**
	 * Execute the cron tick.
	 *
	 * Fetches the current-UTC moon phase and caches it. All errors are
	 * logged via error_log() without terminating the request.
	 */
	public function run(): void {
		do_action( 'astrologer_api/cron_before_tick', self::HOOK );

		$started_at = microtime( true );

		try {
			$response = $this->chart_service->moonPhaseNowUtc();

			if ( is_wp_error( $response ) ) {
				error_log( // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Cron handlers must log failures without dying.
					sprintf(
						'[astrologer-api] Daily moon phase cron failed: %s',
						$response->get_error_message()
					)
				);

				do_action(
					'astrologer_api/cron_after_tick',
					self::HOOK,
					array(
						'success'  => false,
						'error'    => $response->get_error_message(),
						'duration' => microtime( true ) - $started_at,
					)
				);

				return;
			}

			$data = $response->to_array();

			set_transient( self::TRANSIENT_KEY, $data, self::TRANSIENT_TTL );

			do_action( 'astrologer_api/daily_moon_phase_calculated', $data );

			do_action(
				'astrologer_api/cron_after_tick',
				self::HOOK,
				array(
					'success'  => true,
					'duration' => microtime( true ) - $started_at,
				)
			);
		} catch ( Throwable $e ) {
			error_log( // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Cron handlers must log failures without dying.
				sprintf( '[astrologer-api] Daily moon phase cron threw: %s', $e->getMessage() )
			);

			do_action(
				'astrologer_api/cron_after_tick',
				self::HOOK,
				array(
					'success'  => false,
					'error'    => $e->getMessage(),
					'duration' => microtime( true ) - $started_at,
				)
			);
		}
	}
}
