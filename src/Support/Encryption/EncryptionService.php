<?php
/**
 * Sodium-based encryption service for sensitive option values.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Support\Encryption;

/**
 * Encrypts and decrypts strings using libsodium secretbox.
 *
 * Key resolution order:
 *  1. ASTROLOGER_ENCRYPTION_KEY constant (preferred).
 *  2. Deterministic key derived from AUTH_KEY + a persistently stored salt.
 *
 * The persistent salt is auto-generated on first use and stored in the
 * `astrologer_api_encryption_salt` option so it survives across requests.
 */
final class EncryptionService {

	/**
	 * Option name for the persistent salt used in fallback key derivation.
	 *
	 * @var string
	 */
	private const SALT_OPTION = 'astrologer_api_encryption_salt';

	/**
	 * Cached encryption key (raw binary, 32 bytes).
	 *
	 * @var string|null
	 */
	private ?string $key = null;

	/**
	 * Whether libsodium is available.
	 *
	 * @return bool
	 */
	public function is_available(): bool {
		return function_exists( 'sodium_crypto_secretbox' );
	}

	/**
	 * Encrypt a plaintext string.
	 *
	 * Returns a base64-encoded string containing the nonce concatenated with
	 * the ciphertext, suitable for storage in wp_options or post meta.
	 *
	 * @param string $plaintext The value to encrypt.
	 * @return string Base64-encoded nonce + ciphertext.
	 * @throws \RuntimeException If libsodium is not available.
	 */
	public function encrypt( string $plaintext ): string {
		$this->ensure_available();

		$key   = $this->get_key();
		$nonce = random_bytes( SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );

		$ciphertext = sodium_crypto_secretbox( $plaintext, $nonce, $key );

		// Prepend nonce so we can extract it during decryption.
		$combined = $nonce . $ciphertext;

		return base64_encode( $combined ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Used for binary data storage, not obfuscation.
	}

	/**
	 * Decrypt a previously encrypted string.
	 *
	 * @param string $encoded Base64-encoded nonce + ciphertext (output of encrypt()).
	 * @return string|null Decrypted plaintext, or null if decryption fails.
	 * @throws \RuntimeException If libsodium is not available.
	 */
	public function decrypt( string $encoded ): ?string {
		$this->ensure_available();

		$combined = base64_decode( $encoded, true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode -- Counterpart to base64_encode above.

		if ( false === $combined ) {
			return null;
		}

		$nonce_length = SODIUM_CRYPTO_SECRETBOX_NONCEBYTES;

		if ( strlen( $combined ) < $nonce_length + SODIUM_CRYPTO_SECRETBOX_MACBYTES ) {
			return null;
		}

		$nonce      = substr( $combined, 0, $nonce_length );
		$ciphertext = substr( $combined, $nonce_length );

		$key = $this->get_key();

		$plaintext = sodium_crypto_secretbox_open( $ciphertext, $nonce, $key );

		if ( false === $plaintext ) {
			return null;
		}

		return $plaintext;
	}

	/**
	 * Get or derive the encryption key.
	 *
	 * @return string Raw 32-byte key.
	 */
	private function get_key(): string {
		if ( null !== $this->key ) {
			return $this->key;
		}

		// Prefer dedicated constant.
		if ( defined( 'ASTROLOGER_ENCRYPTION_KEY' ) && '' !== ASTROLOGER_ENCRYPTION_KEY ) {
			$this->key = $this->derive_key( ASTROLOGER_ENCRYPTION_KEY, 'astrologer-encryption' );

			return $this->key;
		}

		// Fallback: AUTH_KEY + persistent salt.
		$auth_key = defined( 'AUTH_KEY' ) ? (string) AUTH_KEY : '';
		$salt     = $this->get_persistent_salt();

		$this->key = $this->derive_key( $auth_key . $salt, 'astrologer-encryption-fallback' );

		return $this->key;
	}

	/**
	 * Derive a 32-byte key from arbitrary-length input material.
	 *
	 * @param string $material Key material (constant, AUTH_KEY + salt, etc.).
	 * @param string $context  Context string for domain separation.
	 * @return string Raw 32-byte key.
	 */
	private function derive_key( string $material, string $context ): string {
		if ( function_exists( 'sodium_crypto_generichash' ) ) {
			return sodium_crypto_generichash(
				$material,
				$context,
				SODIUM_CRYPTO_SECRETBOX_KEYBYTES
			);
		}

		// Polyfill: hash-based key derivation for environments without sodium generichash.
		return hash( 'sha256', $material . $context, true );
	}

	/**
	 * Retrieve or generate the persistent salt stored in wp_options.
	 *
	 * The salt is generated once and reused so that existing encrypted values
	 * remain decryptable across requests. If the option is lost, any values
	 * encrypted with it become unrecoverable (handled by admin notice in F4).
	 *
	 * @return string Hex-encoded salt (64 chars = 32 bytes).
	 */
	private function get_persistent_salt(): string {
		$salt = get_option( self::SALT_OPTION, '' );

		if ( '' !== $salt && is_string( $salt ) ) {
			return $salt;
		}

		// Generate and persist a new salt.
		$new_salt = bin2hex( random_bytes( 32 ) );

		update_option( self::SALT_OPTION, $new_salt, false );

		return $new_salt;
	}

	/**
	 * Guard: throw if libsodium is not available.
	 *
	 * @throws \RuntimeException If sodium extension is missing.
	 */
	private function ensure_available(): void {
		if ( ! $this->is_available() ) {
			throw new \RuntimeException(
				'The sodium extension is required for encryption. PHP 8.1+ includes it by default.'
			);
		}
	}
}
