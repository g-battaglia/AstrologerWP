<?php
/**
 * Integration tests for the plugin's i18n setup.
 *
 * Ensures the text domain is loaded on boot, the seed POT file exists with
 * valid gettext headers, and a handful of known strings from the plugin
 * surface area are present in the POT.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Tests\Integration;

use Astrologer\Api\Plugin;
use WP_UnitTestCase;

/**
 * @covers \Astrologer\Api\Plugin
 * @covers \Astrologer\Api\Support\i18n\ScriptTranslations
 */
final class I18nTest extends WP_UnitTestCase {

	/**
	 * Plugin text domain constant — kept local so the assertion remains
	 * readable if the constant ever gets promoted to a class.
	 */
	private const DOMAIN = 'astrologer-api';

	/**
	 * Absolute path to the languages directory.
	 */
	private string $languages_dir;

	/**
	 * POT path derived from the languages directory.
	 */
	private string $pot_path;

	/**
	 * Cached POT contents so we only read the file once per test class.
	 */
	private static ?string $pot_contents = null;

	/**
	 * Set up shared fixtures.
	 */
	protected function setUp(): void {
		parent::setUp();

		$this->languages_dir = defined( 'ASTROLOGER_API_DIR' )
			? ASTROLOGER_API_DIR . '/languages'
			: dirname( __DIR__, 2 ) . '/languages';

		$this->pot_path = $this->languages_dir . '/astrologer-api.pot';

		if ( null === self::$pot_contents && file_exists( $this->pot_path ) ) {
			self::$pot_contents = (string) file_get_contents( $this->pot_path );
		}
	}

	/**
	 * Booting the plugin must register the text domain with WordPress.
	 */
	public function test_textdomain_is_loaded_on_boot(): void {
		Plugin::instance()->boot();

		$this->assertTrue(
			is_textdomain_loaded( self::DOMAIN ),
			'Expected textdomain "astrologer-api" to be loaded after Plugin::boot().'
		);
	}

	/**
	 * The POT file should exist and ship with valid gettext headers.
	 */
	public function test_pot_file_exists_with_required_headers(): void {
		$this->assertFileExists(
			$this->pot_path,
			'languages/astrologer-api.pot is missing.'
		);

		$contents = self::$pot_contents;
		$this->assertNotNull( $contents, 'POT contents could not be read.' );

		$required_headers = array(
			'Project-Id-Version: Astrologer API',
			'Content-Type: text/plain; charset=UTF-8',
			'Content-Transfer-Encoding: 8bit',
			'X-Generator: AstrologerWP',
			'X-Domain: astrologer-api',
			'Language-Team:',
		);

		foreach ( $required_headers as $header ) {
			$this->assertStringContainsString(
				$header,
				$contents,
				sprintf( 'Required POT header missing: "%s".', $header )
			);
		}
	}

	/**
	 * A handful of real strings from src/ and blocks/ must appear in the POT
	 * so translators have something to anchor on from day one.
	 *
	 * @dataProvider known_strings_provider
	 */
	public function test_known_strings_are_present_in_pot( string $needle ): void {
		$contents = self::$pot_contents;
		$this->assertNotNull( $contents, 'POT contents could not be read.' );

		$this->assertStringContainsString(
			'msgid "' . $needle . '"',
			$contents,
			sprintf( 'Expected "%s" to appear in the POT file.', $needle )
		);
	}

	/**
	 * Strings that must be present in the seed POT.
	 *
	 * @return list<array{0: string}>
	 */
	public static function known_strings_provider(): array {
		return array(
			array( 'Insufficient permissions.' ),
			array( 'Too many requests. Please try again later.' ),
			array( 'Astrology' ),
			array( 'Birth Data' ),
			array( 'Calculate Chart' ),
			array( 'Birth Form' ),
			array( 'Natal Chart' ),
		);
	}
}
