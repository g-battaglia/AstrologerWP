<?php
/**
 * AstrologerCommand — registers `wp astrologer ...` sub-commands.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Cli;

use Astrologer\Api\Cli\Commands\CacheCommand;
use Astrologer\Api\Cli\Commands\ChartCommand;
use Astrologer\Api\Cli\Commands\DoctorCommand;
use Astrologer\Api\Cli\Commands\HealthCommand;
use Astrologer\Api\Cli\Commands\SettingsCommand;
use Astrologer\Api\Repository\SettingsRepository;
use Astrologer\Api\Services\ChartService;
use Astrologer\Api\Support\Encryption\EncryptionService;

/**
 * Registers every plugin WP-CLI sub-command.
 *
 * Only instantiated when `defined('WP_CLI') && WP_CLI`. The Plugin boot
 * routine is responsible for that guard — see {@see \Astrologer\Api\Plugin::boot()}.
 */
final class AstrologerCommand {

	/**
	 * Chart service (used by chart + health commands).
	 *
	 * @var ChartService
	 */
	private ChartService $chart_service;

	/**
	 * Settings repository.
	 *
	 * @var SettingsRepository
	 */
	private SettingsRepository $settings;

	/**
	 * Encryption service (doctor command checks availability).
	 *
	 * @var EncryptionService
	 */
	private EncryptionService $encryption;

	/**
	 * Constructor.
	 *
	 * @param ChartService       $chart_service Chart service.
	 * @param SettingsRepository $settings      Settings repository.
	 * @param EncryptionService  $encryption    Encryption service.
	 */
	public function __construct(
		ChartService $chart_service,
		SettingsRepository $settings,
		EncryptionService $encryption
	) {
		$this->chart_service = $chart_service;
		$this->settings      = $settings;
		$this->encryption    = $encryption;
	}

	/**
	 * Register every `wp astrologer <...>` sub-command with WP-CLI.
	 */
	public function register(): void {
		if ( ! ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			return;
		}

		if ( ! class_exists( '\WP_CLI' ) ) {
			return;
		}

		\WP_CLI::add_command(
			'astrologer chart',
			new ChartCommand( $this->chart_service )
		);

		\WP_CLI::add_command(
			'astrologer cache',
			new CacheCommand()
		);

		\WP_CLI::add_command(
			'astrologer settings',
			new SettingsCommand( $this->settings )
		);

		\WP_CLI::add_command(
			'astrologer health',
			new HealthCommand( $this->chart_service )
		);

		\WP_CLI::add_command(
			'astrologer doctor',
			new DoctorCommand( $this->settings, $this->encryption )
		);
	}
}
