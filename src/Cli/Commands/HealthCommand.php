<?php
/**
 * HealthCommand — `wp astrologer health` WP-CLI sub-command.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Cli\Commands;

use Astrologer\Api\Services\ChartService;

/**
 * Reports upstream API health.
 *
 * Exits 0 on success, 1 otherwise.
 *
 * ## EXAMPLES
 *
 *     wp astrologer health
 */
final class HealthCommand {

	/**
	 * Chart service.
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
	 * Query the upstream `/health` endpoint and print the result.
	 *
	 * ## EXAMPLES
	 *
	 *     wp astrologer health
	 *
	 * @param list<string>         $args       Positional arguments (unused).
	 * @param array<string,string> $assoc_args Associative arguments (unused).
	 */
	public function __invoke( array $args, array $assoc_args ): void {
		unset( $args, $assoc_args );

		$response = $this->chart_service->health();

		if ( is_wp_error( $response ) ) {
			\WP_CLI::error( sprintf( 'Upstream health check failed: %s', $response->get_error_message() ) );
		}

		\WP_CLI::log( (string) wp_json_encode( $response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
		\WP_CLI::success( 'Upstream API healthy.' );
	}
}
