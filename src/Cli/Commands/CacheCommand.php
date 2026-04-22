<?php
/**
 * CacheCommand — `wp astrologer cache ...` WP-CLI sub-command.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Cli\Commands;

/**
 * Clears plugin-owned transients from the options table.
 *
 * ## EXAMPLES
 *
 *     # Delete every transient whose key starts with astrologer_api_.
 *     wp astrologer cache clear
 */
final class CacheCommand {

	/**
	 * Transient key prefix managed by the plugin.
	 *
	 * @var string
	 */
	private const PREFIX = 'astrologer_api_';

	/**
	 * Clear every plugin-owned transient.
	 *
	 * ## OPTIONS
	 *
	 * <action>
	 * : Cache action to perform. Currently only "clear" is supported.
	 *
	 * ## EXAMPLES
	 *
	 *     wp astrologer cache clear
	 *
	 * @param list<string>         $args       Positional arguments.
	 * @param array<string,string> $assoc_args Associative arguments (unused).
	 */
	public function __invoke( array $args, array $assoc_args ): void {
		unset( $assoc_args );

		$action = isset( $args[0] ) ? strtolower( $args[0] ) : '';

		if ( 'clear' !== $action ) {
			\WP_CLI::error( sprintf( 'Unknown cache action "%s". Try "clear".', $action ) );
		}

		$cleared = $this->clear_transients();

		\WP_CLI::success( sprintf( 'Cleared %d transient(s) matching "%s*".', $cleared, self::PREFIX ) );
	}

	/**
	 * Delete transients and their timeouts whose keys start with our prefix.
	 *
	 * @return int Number of transient rows removed (timeout rows excluded).
	 */
	private function clear_transients(): int {
		/** @var \wpdb|null $wpdb */
		global $wpdb;

		if ( ! $wpdb instanceof \wpdb ) {
			return 0;
		}

		$transient_like = $wpdb->esc_like( '_transient_' . self::PREFIX ) . '%';
		$timeout_like   = $wpdb->esc_like( '_transient_timeout_' . self::PREFIX ) . '%';

		$options_table = (string) $wpdb->options;

		$deleted = $this->delete_like( $options_table, $transient_like );

		// Timeouts are cleaned too, but we don't count them in the user-facing number.
		$this->delete_like( $options_table, $timeout_like );

		wp_cache_flush();

		return $deleted;
	}

	/**
	 * Delete every options row matching a LIKE pattern.
	 *
	 * Wraps the single $wpdb call so the interpolation/prepare pattern is
	 * isolated behind PHPCS/PHPStan ignore annotations.
	 *
	 * @param string $table        Fully-qualified options table name.
	 * @param string $like_pattern Already-escaped LIKE pattern.
	 * @return int Number of rows affected.
	 */
	private function delete_like( string $table, string $like_pattern ): int {
		/** @var \wpdb|null $wpdb */
		global $wpdb;

		if ( ! $wpdb instanceof \wpdb ) {
			return 0;
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- $table is a trusted wpdb property, $like_pattern is esc_like'd.
		$sql = $wpdb->prepare( 'DELETE FROM `' . $table . '` WHERE option_name LIKE %s', $like_pattern ); // @phpstan-ignore argument.type

		if ( ! is_string( $sql ) ) {
			return 0;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- Already prepared above.
		return (int) $wpdb->query( $sql );
	}
}
