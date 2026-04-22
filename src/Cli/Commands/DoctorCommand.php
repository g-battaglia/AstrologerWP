<?php
/**
 * DoctorCommand — `wp astrologer doctor` WP-CLI sub-command.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Cli\Commands;

use Astrologer\Api\Repository\SettingsRepository;
use Astrologer\Api\Support\Encryption\EncryptionService;

/**
 * Runs environmental diagnostics for the plugin.
 *
 * Each check returns either "pass", "warn", or "fail". Any "fail" causes
 * the command to exit with status 1.
 *
 * ## EXAMPLES
 *
 *     wp astrologer doctor
 */
final class DoctorCommand {

	/** Minimum supported PHP version. */
	public const MIN_PHP_VERSION = '8.1.0';

	/** Required PHP extensions. */
	public const REQUIRED_EXTENSIONS = array( 'sodium', 'json', 'openssl' );

	/** Status constants. */
	public const STATUS_PASS = 'pass';
	public const STATUS_WARN = 'warn';
	public const STATUS_FAIL = 'fail';

	/**
	 * Settings repository.
	 *
	 * @var SettingsRepository
	 */
	private SettingsRepository $settings;

	/**
	 * Encryption service.
	 *
	 * @var EncryptionService
	 */
	private EncryptionService $encryption;

	/**
	 * Constructor.
	 *
	 * @param SettingsRepository $settings   Settings repository.
	 * @param EncryptionService  $encryption Encryption service.
	 */
	public function __construct( SettingsRepository $settings, EncryptionService $encryption ) {
		$this->settings   = $settings;
		$this->encryption = $encryption;
	}

	/**
	 * Run every diagnostic and print a formatted report.
	 *
	 * @param list<string>         $args       Positional arguments (unused).
	 * @param array<string,string> $assoc_args Associative arguments (unused).
	 */
	public function __invoke( array $args, array $assoc_args ): void {
		unset( $args, $assoc_args );

		$results  = $this->run_checks();
		$has_fail = false;

		foreach ( $results as $check ) {
			$label  = $check['label'];
			$status = $check['status'];
			$detail = $check['detail'];

			switch ( $status ) {
				case self::STATUS_PASS:
					\WP_CLI::log(
						sprintf( '%s %s — %s', \WP_CLI::colorize( '%gPASS%n' ), $label, $detail )
					);
					break;

				case self::STATUS_WARN:
					\WP_CLI::warning( sprintf( '%s — %s', $label, $detail ) );
					break;

				case self::STATUS_FAIL:
				default:
					$has_fail = true;
					\WP_CLI::log(
						sprintf( '%s %s — %s', \WP_CLI::colorize( '%rFAIL%n' ), $label, $detail )
					);
			}
		}

		if ( $has_fail ) {
			\WP_CLI::error( 'One or more diagnostics failed.' );
		}

		\WP_CLI::success( 'All diagnostics passed.' );
	}

	/**
	 * Execute every diagnostic and return structured results.
	 *
	 * @return list<array{label:string,status:string,detail:string}>
	 */
	public function run_checks(): array {
		return array(
			$this->check_php_version(),
			$this->check_extensions(),
			$this->check_encryption_key(),
			$this->check_rapidapi_key(),
			$this->check_permalinks(),
			$this->check_rewrite_rules(),
		);
	}

	/**
	 * PHP version check.
	 *
	 * @return array{label:string,status:string,detail:string}
	 */
	private function check_php_version(): array {
		$ok = version_compare( PHP_VERSION, self::MIN_PHP_VERSION, '>=' );

		return array(
			'label'  => 'PHP version',
			'status' => $ok ? self::STATUS_PASS : self::STATUS_FAIL,
			'detail' => $ok
				? sprintf( 'running %s (>= %s)', PHP_VERSION, self::MIN_PHP_VERSION )
				: sprintf( 'running %s, need >= %s', PHP_VERSION, self::MIN_PHP_VERSION ),
		);
	}

	/**
	 * Required extensions check.
	 *
	 * @return array{label:string,status:string,detail:string}
	 */
	private function check_extensions(): array {
		$missing = array();

		foreach ( self::REQUIRED_EXTENSIONS as $ext ) {
			if ( ! extension_loaded( $ext ) ) {
				$missing[] = $ext;
			}
		}

		if ( empty( $missing ) ) {
			return array(
				'label'  => 'PHP extensions',
				'status' => self::STATUS_PASS,
				'detail' => 'sodium, json, openssl all loaded',
			);
		}

		return array(
			'label'  => 'PHP extensions',
			'status' => self::STATUS_FAIL,
			'detail' => 'missing: ' . implode( ', ', $missing ),
		);
	}

	/**
	 * Encryption availability check.
	 *
	 * @return array{label:string,status:string,detail:string}
	 */
	private function check_encryption_key(): array {
		$configured = defined( 'ASTROLOGER_ENCRYPTION_KEY' ) && '' !== ASTROLOGER_ENCRYPTION_KEY;

		if ( ! $this->encryption->is_available() ) {
			return array(
				'label'  => 'Encryption key',
				'status' => self::STATUS_FAIL,
				'detail' => 'libsodium unavailable',
			);
		}

		if ( ! $configured ) {
			return array(
				'label'  => 'Encryption key',
				'status' => self::STATUS_FAIL,
				'detail' => 'ASTROLOGER_ENCRYPTION_KEY not defined',
			);
		}

		return array(
			'label'  => 'Encryption key',
			'status' => self::STATUS_PASS,
			'detail' => 'sourced from ASTROLOGER_ENCRYPTION_KEY',
		);
	}

	/**
	 * RapidAPI key presence check.
	 *
	 * @return array{label:string,status:string,detail:string}
	 */
	private function check_rapidapi_key(): array {
		$key = $this->settings->get( 'rapidapi_key' );

		if ( is_string( $key ) && '' !== $key ) {
			return array(
				'label'  => 'RapidAPI key',
				'status' => self::STATUS_PASS,
				'detail' => 'configured',
			);
		}

		return array(
			'label'  => 'RapidAPI key',
			'status' => self::STATUS_FAIL,
			'detail' => 'not set in settings',
		);
	}

	/**
	 * Permalinks check.
	 *
	 * @return array{label:string,status:string,detail:string}
	 */
	private function check_permalinks(): array {
		$structure = function_exists( 'get_option' ) ? (string) get_option( 'permalink_structure', '' ) : '';

		if ( '' !== $structure ) {
			return array(
				'label'  => 'Permalinks',
				'status' => self::STATUS_PASS,
				'detail' => sprintf( 'pretty permalinks active (%s)', $structure ),
			);
		}

		return array(
			'label'  => 'Permalinks',
			'status' => self::STATUS_FAIL,
			'detail' => 'permalink_structure is empty — enable pretty permalinks',
		);
	}

	/**
	 * Rewrite rules flushed check.
	 *
	 * @return array{label:string,status:string,detail:string}
	 */
	private function check_rewrite_rules(): array {
		if ( ! function_exists( 'get_option' ) ) {
			return array(
				'label'  => 'Rewrite rules',
				'status' => self::STATUS_WARN,
				'detail' => 'WordPress functions not available',
			);
		}

		$rules = get_option( 'rewrite_rules' );

		if ( is_array( $rules ) && ! empty( $rules ) ) {
			return array(
				'label'  => 'Rewrite rules',
				'status' => self::STATUS_PASS,
				'detail' => sprintf( '%d rules cached', count( $rules ) ),
			);
		}

		return array(
			'label'  => 'Rewrite rules',
			'status' => self::STATUS_WARN,
			'detail' => 'no cached rules — run wp rewrite flush',
		);
	}
}
