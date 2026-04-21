<?php
/**
 * Plugin activation handler.
 *
 * Registers CPT + taxonomy, seeds capabilities, generates the encryption
 * salt, and flags the setup wizard as pending.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Activation;

use Astrologer\Api\Capabilities\CapabilityManager;
use Astrologer\Api\Enums\ChartType;
use Astrologer\Api\PostType\AstrologerChartPostType;
use Astrologer\Api\PostType\ChartTypeTaxonomy;

/**
 * Runs once when the plugin is activated.
 */
final class Activator {

	/**
	 * Execute activation routines.
	 *
	 * @return void
	 */
	public static function run(): void {
		// Register CPT and taxonomy so flush_rewrite_rules() has them.
		( new AstrologerChartPostType() )->register();
		( new ChartTypeTaxonomy() )->register();

		// Seed default taxonomy terms.
		ChartTypeTaxonomy::seed_terms();

		// Add custom capabilities to roles.
		$cap_manager = new CapabilityManager();
		$cap_manager->add_capabilities();

		// Generate encryption salt if not present (used by EncryptionService fallback).
		self::ensure_encryption_salt();

		// Flag that the setup wizard should run on first admin visit.
		if ( false === get_option( 'astrologer_api_setup_wizard_pending' ) ) {
			add_option( 'astrologer_api_setup_wizard_pending', true );
		}

		flush_rewrite_rules( false );
	}

	/**
	 * Ensure the encryption salt option exists.
	 *
	 * This pre-creates the salt that EncryptionService uses in its fallback
	 * key derivation path, so the option is available from the first request.
	 *
	 * @return void
	 */
	private static function ensure_encryption_salt(): void {
		if ( '' !== (string) get_option( 'astrologer_api_encryption_salt', '' ) ) {
			return;
		}

		$salt = bin2hex( random_bytes( 32 ) );
		update_option( 'astrologer_api_encryption_salt', $salt, false );
	}
}
