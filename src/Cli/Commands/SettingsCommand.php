<?php
/**
 * SettingsCommand — `wp astrologer settings ...` WP-CLI sub-command.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Cli\Commands;

use Astrologer\Api\Repository\SettingsRepository;

/**
 * Manage plugin settings from WP-CLI.
 *
 * ## EXAMPLES
 *
 *     # Read a single value.
 *     wp astrologer settings get language
 *
 *     # Write a scalar value.
 *     wp astrologer settings set language IT
 *
 *     # Reset all values to their defaults.
 *     wp astrologer settings reset
 *
 *     # Export every setting as JSON (sensitive values shown as "***").
 *     wp astrologer settings export
 */
final class SettingsCommand {

	/**
	 * Settings repository.
	 *
	 * @var SettingsRepository
	 */
	private SettingsRepository $settings;

	/**
	 * Fields whose values are masked in export output.
	 *
	 * @var list<string>
	 */
	private const SENSITIVE_FIELDS = array(
		'rapidapi_key',
		'geonames_username',
	);

	/**
	 * Constructor.
	 *
	 * @param SettingsRepository $settings Settings repository.
	 */
	public function __construct( SettingsRepository $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Dispatch settings sub-command.
	 *
	 * ## OPTIONS
	 *
	 * <action>
	 * : Sub-action — one of "get", "set", "reset", "export".
	 *
	 * [<key>]
	 * : Setting key to read/write (required for get/set).
	 *
	 * [<value>]
	 * : Value to write (required for set).
	 *
	 * ## EXAMPLES
	 *
	 *     wp astrologer settings get language
	 *     wp astrologer settings set language IT
	 *     wp astrologer settings reset
	 *     wp astrologer settings export
	 *
	 * @param list<string>         $args       Positional arguments.
	 * @param array<string,string> $assoc_args Associative arguments (unused).
	 */
	public function __invoke( array $args, array $assoc_args ): void {
		unset( $assoc_args );

		$action = isset( $args[0] ) ? strtolower( $args[0] ) : '';

		switch ( $action ) {
			case 'get':
				$this->handle_get( $args );
				return;

			case 'set':
				$this->handle_set( $args );
				return;

			case 'reset':
				$this->settings->reset();
				\WP_CLI::success( 'Settings reset to defaults.' );
				return;

			case 'export':
				$this->handle_export();
				return;

			default:
				\WP_CLI::error( sprintf( 'Unknown action "%s". Try "get", "set", "reset", or "export".', $action ) );
		}
	}

	/**
	 * Handle `settings get <key>`.
	 *
	 * @param list<string> $args Positional arguments.
	 */
	private function handle_get( array $args ): void {
		$key = $args[1] ?? '';

		if ( '' === $key ) {
			\WP_CLI::error( 'Missing <key>. Usage: wp astrologer settings get <key>' );
		}

		$value = $this->settings->get( $key );

		if ( null === $value ) {
			\WP_CLI::warning( sprintf( 'Setting "%s" is not defined.', $key ) );

			return;
		}

		if ( is_scalar( $value ) ) {
			\WP_CLI::log( (string) $value );

			return;
		}

		\WP_CLI::log( (string) wp_json_encode( $value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
	}

	/**
	 * Handle `settings set <key> <value>`.
	 *
	 * @param list<string> $args Positional arguments.
	 */
	private function handle_set( array $args ): void {
		$key   = $args[1] ?? '';
		$value = $args[2] ?? null;

		if ( '' === $key || null === $value ) {
			\WP_CLI::error( 'Usage: wp astrologer settings set <key> <value>' );
		}

		$this->settings->set( $key, $value );

		\WP_CLI::success( sprintf( 'Setting "%s" updated.', $key ) );
	}

	/**
	 * Handle `settings export` — dump the current settings as JSON.
	 */
	private function handle_export(): void {
		$all = $this->settings->all();

		foreach ( self::SENSITIVE_FIELDS as $field ) {
			if ( array_key_exists( $field, $all ) && '' !== $all[ $field ] ) {
				$all[ $field ] = '***';
			}
		}

		\WP_CLI::log( (string) wp_json_encode( $all, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
	}
}
