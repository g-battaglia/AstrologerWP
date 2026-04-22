<?php
/**
 * Minimal bootstrap stub of the WP-CLI surface consumed by this plugin.
 *
 * Loaded by phpstan.neon.dist so static analysis can resolve calls to
 * WP_CLI::success(), ::error(), ::warning(), ::log(), ::colorize() and
 * ::add_command() without requiring the wp-cli/wp-cli package as a
 * Composer dependency.
 *
 * @package Astrologer\Api
 */

// phpcs:ignoreFile

if ( ! class_exists( 'WP_CLI' ) ) {
	class WP_CLI {

		/**
		 * @param string $message
		 * @return void
		 */
		public static function success( string $message ) {}

		/**
		 * @param string $message
		 * @return void
		 */
		public static function error( string $message ) {}

		/**
		 * @param string $message
		 * @return void
		 */
		public static function warning( string $message ) {}

		/**
		 * @param string $message
		 * @return void
		 */
		public static function log( string $message ) {}

		/**
		 * @param string $text
		 * @return string
		 */
		public static function colorize( string $text ): string {
			return $text;
		}

		/**
		 * @param string        $name
		 * @param callable|object|string $callable
		 * @param array<string,mixed>    $args
		 * @return bool
		 */
		public static function add_command( string $name, $callable, array $args = array() ): bool {
			return true;
		}
	}
}
