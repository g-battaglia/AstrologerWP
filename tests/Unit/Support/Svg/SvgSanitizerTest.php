<?php
/**
 * Unit tests for SvgSanitizer.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Tests\Unit\Support\Svg;

use Astrologer\Api\Support\Svg\SvgSanitizer;
use Brain\Monkey;
use Brain\Monkey\Functions;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class SvgSanitizerTest extends TestCase {

	use MockeryPHPUnitIntegration;

	private SvgSanitizer $sanitizer;

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		// Stub WP functions used by SvgSanitizer.
		Functions\when( 'apply_filters' )->alias(
			static function ( string $tag, mixed $value ): mixed {
				// Return the value unchanged (no filters registered in unit tests).
				return $value;
			},
		);

		Functions\when( 'wp_kses' )->alias(
			static function ( string $string, array $allowed_html ): string {
				// Minimal wp_kses stub: strip <script> and on* attributes.
				// Real wp_kses is far more complex; this stub covers the key cases.
				$result = $string;

				// Remove script tags and content.
				$result = (string) preg_replace( '#<script[^>]*>.*?</script>#si', '', $result );

				// Remove on* event handler attributes.
				$result = (string) preg_replace( '/\s+on\w+\s*=\s*["\'][^"\']*["\']/i', '', $result );

				// Remove tags not in the allowlist.
				$allowed_tags = array_keys( $allowed_html );
				$result = (string) preg_replace_callback(
					'#<(/?)(\w+)([^>]*)>#',
					static function ( array $m ) use ( $allowed_tags ): string {
						$tag_name = strtolower( $m[2] );
						if ( in_array( $tag_name, $allowed_tags, true ) ) {
							return '<' . $m[1] . $m[2] . $m[3] . '>';
						}
						return '';
					},
					$result,
				);

				return $result;
			},
		);

		$this->sanitizer = new SvgSanitizer();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_preserves_valid_svg(): void {
		$svg = '<svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">'
			. '<circle cx="50" cy="50" r="40" fill="red"/>'
			. '</svg>';

		$result = $this->sanitizer->sanitize( $svg );

		$this->assertStringContainsString( '<svg', $result );
		$this->assertStringContainsString( '<circle', $result );
		$this->assertStringContainsString( 'fill="red"', $result );
	}

	public function test_strips_script_tags(): void {
		$svg = '<svg viewBox="0 0 100 100">'
			. '<script>alert("xss")</script>'
			. '<circle cx="50" cy="50" r="40"/>'
			. '</svg>';

		$result = $this->sanitizer->sanitize( $svg );

		$this->assertStringNotContainsString( '<script', $result );
		$this->assertStringNotContainsString( 'alert', $result );
		$this->assertStringContainsString( '<circle', $result );
	}

	public function test_strips_onclick_attribute(): void {
		$svg = '<svg viewBox="0 0 100 100">'
			. '<rect x="10" y="10" width="80" height="80" onclick="alert(1)"/>'
			. '</svg>';

		$result = $this->sanitizer->sanitize( $svg );

		$this->assertStringNotContainsString( 'onclick', $result );
		$this->assertStringContainsString( '<rect', $result );
	}

	public function test_strips_onerror_attribute(): void {
		$svg = '<svg viewBox="0 0 100 100">'
			. '<image href="x" onerror="alert(1)"/>'
			. '</svg>';

		$result = $this->sanitizer->sanitize( $svg );

		$this->assertStringNotContainsString( 'onerror', $result );
	}

	public function test_strips_javascript_uri_in_style(): void {
		$svg = '<svg viewBox="0 0 100 100">'
			. '<rect style="background:url(javascript:alert(1))" x="0" y="0"/>'
			. '</svg>';

		$result = $this->sanitizer->sanitize( $svg );

		$this->assertStringNotContainsString( 'javascript:', $result );
	}

	public function test_preserves_anchor_href(): void {
		$svg = '<svg viewBox="0 0 100 100">'
			. '<use href="#mySymbol" x="0" y="0"/>'
			. '</svg>';

		$result = $this->sanitizer->sanitize( $svg );

		$this->assertStringContainsString( 'href="#mySymbol"', $result );
	}

	public function test_strips_external_href(): void {
		$svg = '<svg viewBox="0 0 100 100">'
			. '<use href="https://evil.com/pwn.svg" x="0" y="0"/>'
			. '</svg>';

		$result = $this->sanitizer->sanitize( $svg );

		$this->assertStringNotContainsString( 'evil.com', $result );
	}

	public function test_preserves_complex_svg_with_gradients(): void {
		$svg = '<svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">'
			. '<defs>'
			. '<linearGradient id="grad1" x1="0%" y1="0%" x2="100%" y2="0%">'
			. '<stop offset="0%" style="stop-color:rgb(255,255,0);stop-opacity:1"/>'
			. '</linearGradient>'
			. '</defs>'
			. '<ellipse cx="50" cy="50" rx="40" ry="20" fill="url(#grad1)"/>'
			. '</svg>';

		$result = $this->sanitizer->sanitize( $svg );

		$this->assertStringContainsString( '<svg', $result );
		$this->assertStringContainsString( '<defs>', $result );
		$this->assertStringContainsString( '<ellipse', $result );
		$this->assertStringContainsString( 'fill="url(#grad1)"', $result );
	}

	public function test_preserves_path_with_d_attribute(): void {
		$svg = '<svg viewBox="0 0 100 100">'
			. '<path d="M10 10 L90 90" stroke="black" stroke-width="2" fill="none"/>'
			. '</svg>';

		$result = $this->sanitizer->sanitize( $svg );

		$this->assertStringContainsString( 'd="M10 10 L90 90"', $result );
		$this->assertStringContainsString( 'stroke="black"', $result );
	}

	public function test_strips_unknown_tags(): void {
		$svg = '<svg viewBox="0 0 100 100">'
			. '<foreignObject><body>evil</body></foreignObject>'
			. '<circle cx="50" cy="50" r="40"/>'
			. '</svg>';

		$result = $this->sanitizer->sanitize( $svg );

		$this->assertStringNotContainsString( 'foreignObject', $result );
		$this->assertStringNotContainsString( '<body', $result );
		$this->assertStringContainsString( '<circle', $result );
	}

	public function test_handles_empty_string(): void {
		$result = $this->sanitizer->sanitize( '' );
		$this->assertSame( '', $result );
	}

	public function test_removes_vbscript_uri(): void {
		$svg = '<svg viewBox="0 0 100 100">'
			. '<rect style="background:url(vbscript:msgbox)" x="0" y="0"/>'
			. '</svg>';

		$result = $this->sanitizer->sanitize( $svg );

		$this->assertStringNotContainsString( 'vbscript:', $result );
	}

	public function test_removes_data_uri_in_style(): void {
		$svg = '<svg viewBox="0 0 100 100">'
			. '<rect style="background:url(data:text/html,<script>alert(1)</script>)" x="0" y="0"/>'
			. '</svg>';

		$result = $this->sanitizer->sanitize( $svg );

		$this->assertStringNotContainsString( 'data:text/html', $result );
	}
}
