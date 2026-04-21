<?php
/**
 * Settings repository — typed wrapper around wp_options with encryption.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Repository;

use Astrologer\Api\Enums\School;
use Astrologer\Api\Enums\UILevel;
use Astrologer\Api\Support\Encryption\EncryptionService;
use Astrologer\Api\ValueObjects\ChartOptions;

/**
 * Manages plugin settings stored in wp_options.
 *
 * Sensitive fields (rapidapi_key, geonames_username) are encrypted at rest
 * via EncryptionService. All other fields are stored as plain JSON values.
 */
final class SettingsRepository {

	/**
	 * Option key used in wp_options.
	 *
	 * @var string
	 */
	private const OPTION_KEY = 'astrologer_api_settings';

	/**
	 * Fields that must be encrypted at rest.
	 *
	 * @var list<string>
	 */
	private const SENSITIVE_FIELDS = array(
		'rapidapi_key',
		'geonames_username',
	);

	/**
	 * Encryption service instance.
	 *
	 * @var EncryptionService
	 */
	private EncryptionService $encryption;

	/**
	 * Constructor.
	 *
	 * @param EncryptionService $encryption Sodium-based encrypt/decrypt service.
	 */
	public function __construct( EncryptionService $encryption ) {
		$this->encryption = $encryption;
	}

	/**
	 * Return all settings as an associative array with sensitive values decrypted.
	 *
	 * @return array<string,mixed>
	 */
	public function all(): array {
		$raw = get_option( self::OPTION_KEY, array() );

		if ( ! is_array( $raw ) ) {
			$raw = array();
		}

		$settings = $this->apply_defaults( $raw );

		return $this->decrypt_sensitive( $settings );
	}

	/**
	 * Get a single setting value by key.
	 *
	 * @param string $key      Setting key.
	 * @param mixed  $fallback Default value if key is not set.
	 * @return mixed
	 */
	public function get( string $key, mixed $fallback = null ): mixed {
		$all = $this->all();

		return $all[ $key ] ?? $fallback;
	}

	/**
	 * Set a single setting value.
	 *
	 * @param string $key   Setting key.
	 * @param mixed  $value Setting value.
	 */
	public function set( string $key, mixed $value ): void {
		$this->update( array( $key => $value ) );
	}

	/**
	 * Update multiple settings at once.
	 *
	 * @param array<string,mixed> $partial Key-value pairs to update.
	 */
	public function update( array $partial ): void {
		$raw = get_option( self::OPTION_KEY, array() );

		if ( ! is_array( $raw ) ) {
			$raw = array();
		}

		// Encrypt sensitive fields before storage.
		foreach ( self::SENSITIVE_FIELDS as $field ) {
			if ( array_key_exists( $field, $partial ) ) {
				$value = $partial[ $field ];

				if ( is_string( $value ) && '' !== $value ) {
					$partial[ $field ] = $this->encryption->encrypt( $value );
				} elseif ( '' === $value ) {
					// Empty string means "cleared" — store empty, not encrypted.
					$partial[ $field ] = '';
				}
			}
		}

		$merged = array_merge( $raw, $partial );

		update_option( self::OPTION_KEY, $merged, false );
	}

	/**
	 * Reset all settings to their defaults.
	 *
	 * Applies the `astrologer_api/settings_defaults` filter so extensions
	 * can override default values.
	 */
	public function reset(): void {
		/** This filter is documented in this class::default_values(). */
		// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores -- Slash-separated namespace is an intentional design choice.
		$defaults = apply_filters( 'astrologer_api/settings_defaults', $this->default_values() );

		delete_option( self::OPTION_KEY );
		update_option( self::OPTION_KEY, $defaults, false );
	}

	/**
	 * Check whether the plugin is configured with a non-empty RapidAPI key.
	 *
	 * @return bool
	 */
	public function is_configured(): bool {
		$key = $this->get( 'rapidapi_key' );

		return is_string( $key ) && '' !== $key;
	}

	/**
	 * Return the full default settings array.
	 *
	 * @return array<string,mixed>
	 */
	private function default_values(): array {
		return array(
			'rapidapi_key'      => '',
			'geonames_username' => '',
			'api_base_url'      => 'https://astrologer.p.rapidapi.com',
			'language'          => 'EN',
			'school'            => School::get_default()->value,
			'ui_level'          => UILevel::get_default()->value,
			'chart_options'     => ChartOptions::defaults()->to_array(),
			'cron'              => array(
				'daily_transits'        => false,
				'solar_return_reminder' => false,
				'daily_moon_phase'      => false,
			),
			'integrations'      => array(
				'geonames_enabled' => true,
			),
		);
	}

	/**
	 * Merge stored settings with defaults so every key is always present.
	 *
	 * @param array<string,mixed> $stored Raw values from wp_options.
	 * @return array<string,mixed>
	 */
	private function apply_defaults( array $stored ): array {
		/** This filter is documented in this class::default_values(). */
		// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores -- Slash-separated namespace is an intentional design choice.
		$defaults = apply_filters( 'astrologer_api/settings_defaults', $this->default_values() );

		return array_merge( $defaults, $stored );
	}

	/**
	 * Decrypt all sensitive fields in a settings array.
	 *
	 * @param array<string,mixed> $settings Settings with possibly-encrypted values.
	 * @return array<string,mixed> Settings with sensitive fields in plaintext.
	 */
	private function decrypt_sensitive( array $settings ): array {
		foreach ( self::SENSITIVE_FIELDS as $field ) {
			if ( ! isset( $settings[ $field ] ) ) {
				continue;
			}

			$value = $settings[ $field ];

			if ( ! is_string( $value ) || '' === $value ) {
				continue;
			}

			$decrypted = $this->encryption->decrypt( $value );

			// If decryption fails (wrong key, tampered), return empty string.
			$settings[ $field ] = null !== $decrypted ? $decrypted : '';
		}

		return $settings;
	}
}
