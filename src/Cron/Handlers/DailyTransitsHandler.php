<?php
/**
 * DailyTransitsHandler — computes today's transits and caches them.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Cron\Handlers;

use Astrologer\Api\DTO\NowRequestDTO;
use Astrologer\Api\Services\ChartService;
use Throwable;

/**
 * Cron handler that fetches the current-moment chart once per day.
 *
 * Results are cached in a transient for quick consumption by blocks / widgets
 * and a namespaced action is fired so integrations can react to new data.
 */
final class DailyTransitsHandler {

	/**
	 * Transient key where the last successful response is cached.
	 *
	 * @var string
	 */
	public const TRANSIENT_KEY = 'astrologer_api_daily_transits';

	/**
	 * Cron hook name this handler responds to.
	 *
	 * @var string
	 */
	public const HOOK = 'astrologer_api_daily_transits';

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
	 * Caches the response as an associative array and broadcasts the
	 * `astrologer_api/daily_transits_calculated` hook on success.
	 * Exceptions are caught and logged so the scheduler is never broken.
	 */
	public function run(): void {
		do_action( 'astrologer_api/cron_before_tick', self::HOOK );

		$started_at = microtime( true );

		try {
			$dto      = NowRequestDTO::from_array( array() );
			$response = $this->chart_service->nowChart( $dto );

			if ( is_wp_error( $response ) ) {
				error_log( // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Cron handlers must log failures without dying.
					sprintf(
						'[astrologer-api] Daily transits cron failed: %s',
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

			do_action( 'astrologer_api/daily_transits_calculated', $data );

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
				sprintf( '[astrologer-api] Daily transits cron threw: %s', $e->getMessage() )
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
