<?php
/**
 * RateLimiter — transient-based per-IP + per-user rate limiting.
 *
 * Uses WordPress transients as sliding-window counters. Admins with
 * manage_options capability are always exempt.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Services;

/**
 * Enforce per-minute request limits per bucket/IP/user.
 */
final class RateLimiter {

	/**
	 * Check whether a request is allowed under the rate limit.
	 *
	 * @param string $bucket     Logical bucket name (e.g. 'chart', 'moon_phase').
	 * @param int    $user_id    WordPress user ID (0 for anonymous).
	 * @param string $ip         Client IP address.
	 * @param int    $limit      Max requests per window. Default 60.
	 * @param int    $window_sec Window length in seconds. Default 60.
	 * @return bool True if the request is allowed, false if rate-limited.
	 */
	public function check(
		string $bucket,
		int $user_id,
		string $ip,
		int $limit = 60,
		int $window_sec = 60,
	): bool {
		// Allow filter to override the limit per endpoint/user.
		/** @var int $limit */
		$limit = (int) apply_filters(
			'astrologer_api/rate_limit_per_minute',
			$limit,
			$bucket,
			$user_id,
		);

		// Admins are always exempt.
		if ( 0 !== $user_id && user_can( $user_id, 'manage_options' ) ) {
			return true;
		}

		$key   = $this->transient_key( $bucket, $user_id, $ip );
		$count = $this->increment( $key, $window_sec );

		return $count <= $limit;
	}

	/**
	 * Reset the counter for a given bucket/user/IP combination.
	 *
	 * @param string $bucket  Logical bucket name.
	 * @param int    $user_id WordPress user ID.
	 * @param string $ip      Client IP address.
	 */
	public function reset( string $bucket, int $user_id, string $ip ): void {
		$key = $this->transient_key( $bucket, $user_id, $ip );
		delete_transient( $key );
	}

	/**
	 * Detect the client IP from the current request.
	 *
	 * Priority: Cloudflare > X-Forwarded-For > X-Real-IP > REMOTE_ADDR.
	 * Each result is filterable via 'astrologer_api/client_ip'.
	 *
	 * @return string Client IP address (defaults to '0.0.0.0' when not available).
	 */
	public static function detect_ip(): string {
		// phpcs:disable WordPress.Security.ValidatedSanitizedInput -- Sanitized via sanitize_text_field.
		$ip = '0.0.0.0';

		if ( ! empty( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_CONNECTING_IP'] ) );
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$forwarded  = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
			$candidates = array_map( 'trim', explode( ',', $forwarded ) );
			$ip         = $candidates[0] ?? '0.0.0.0';
		} elseif ( ! empty( $_SERVER['HTTP_X_REAL_IP'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_REAL_IP'] ) );
		} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}
		// phpcs:enable WordPress.Security.ValidatedSanitizedInput

		/** @var string $ip */
		return (string) apply_filters( 'astrologer_api/client_ip', $ip );
	}

	/**
	 * Build the transient key for a given bucket/user/IP combination.
	 *
	 * @param string $bucket  Logical bucket name.
	 * @param int    $user_id WordPress user ID.
	 * @param string $ip      Client IP address.
	 * @return string Transient key (max 172 chars).
	 */
	private function transient_key( string $bucket, int $user_id, string $ip ): string {
		$identifier = 0 !== $user_id
			? 'u' . $user_id
			: md5( $ip );

		return 'astrologer_rl_' . $bucket . '_' . $identifier;
	}

	/**
	 * Increment the counter stored in a transient.
	 *
	 * @param string $key        Transient key.
	 * @param int    $window_sec Window length in seconds (used as TTL).
	 * @return int The new counter value after increment.
	 */
	private function increment( string $key, int $window_sec ): int {
		$raw = get_transient( $key );

		if ( false === $raw ) {
			// First request in window.
			set_transient( $key, 1, $window_sec );
			return 1;
		}

		$current = (int) $raw;
		++$current;
		set_transient( $key, $current, $window_sec );

		return $current;
	}
}
