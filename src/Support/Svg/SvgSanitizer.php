<?php
/**
 * SvgSanitizer — sanitizes SVG output from the upstream API before DOM injection.
 *
 * Uses wp_kses with a custom allowlist of SVG-specific tags and attributes.
 * Strips dangerous content: <script>, on* event handlers, javascript: URIs,
 * and external href references (only #anchor refs allowed).
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Support\Svg;

/**
 * Sanitizes SVG strings using a strict allowlist approach via wp_kses.
 */
final class SvgSanitizer {

	/**
	 * Default allowed SVG element tags.
	 *
	 * @var array<string, array<string, true>>
	 */
	private const DEFAULT_TAGS = array(
		'svg'            => array(),
		'g'              => array(),
		'path'           => array(),
		'circle'         => array(),
		'ellipse'        => array(),
		'line'           => array(),
		'polyline'       => array(),
		'polygon'        => array(),
		'rect'           => array(),
		'text'           => array(),
		'tspan'          => array(),
		'defs'           => array(),
		'use'            => array(),
		'title'          => array(),
		'desc'           => array(),
		'style'          => array(),
		'lineargradient' => array(),
		'radialgradient' => array(),
		'stop'           => array(),
		'clippath'       => array(),
		'mask'           => array(),
		'filter'         => array(),
		'pattern'        => array(),
		'symbol'         => array(),
	);

	/**
	 * Default allowed attribute names.
	 *
	 * Each attribute is listed in lowercase as wp_kses normalises attribute names.
	 *
	 * @var list<string>
	 */
	private const DEFAULT_ATTRS = array(
		'viewbox',
		'xmlns',
		'width',
		'height',
		'fill',
		'stroke',
		'stroke-width',
		'stroke-dasharray',
		'stroke-linecap',
		'stroke-linejoin',
		'stroke-opacity',
		'fill-opacity',
		'fill-rule',
		'clip-rule',
		'transform',
		'd',
		'x',
		'y',
		'cx',
		'cy',
		'r',
		'rx',
		'ry',
		'x1',
		'y1',
		'x2',
		'y2',
		'points',
		'class',
		'id',
		'font-family',
		'font-size',
		'font-weight',
		'font-style',
		'text-anchor',
		'dominant-baseline',
		'dy',
		'dx',
		'opacity',
		'style',
		'offset',
		'stop-color',
		'stop-opacity',
		'gradientunits',
		'gradienttransform',
		'patternunits',
		'patterntransform',
		'clippathunits',
		'preserveaspectratio',
		'color',
		'display',
		'vector-effect',
		'href',
		'xlink:href',
		'target',
		'result',
		'in',
		'in2',
		'stddeviation',
		'flood-color',
		'flood-opacity',
		'operator',
		'k1',
		'k2',
		'k3',
		'k4',
		'mode',
		'type',
		'values',
		'markerwidth',
		'markerheight',
		'refx',
		'refy',
		'orient',
		'markerunits',
	);

	/**
	 * Sanitize an SVG string by stripping dangerous elements and attributes.
	 *
	 * @param string $svg Raw SVG string from upstream API or user input.
	 * @return string Cleaned SVG string safe for DOM injection.
	 */
	public function sanitize( string $svg ): string {
		$tags  = $this->get_allowed_tags();
		$attrs = $this->get_allowed_attrs();

		// Build the wp_kses allowed_html array: each tag gets all allowed attrs.
		$allowed = array();
		foreach ( $tags as $tag ) {
			$allowed[ $tag ] = array_fill_keys( $attrs, true );
		}

		$clean = wp_kses( $svg, $allowed );

		// Strip remaining javascript: and data: URIs that wp_kses may leave in style attrs.
		$clean = $this->strip_dangerous_uris( $clean );

		return $clean;
	}

	/**
	 * Get the list of allowed SVG tags, with filter override.
	 *
	 * @return list<string> Lowercase tag names.
	 */
	private function get_allowed_tags(): array {
		/** This filter is documented in src/Services/HooksRegistry.php */
		$tags = apply_filters(
			'astrologer_api/svg_allowed_tags',
			array_keys( self::DEFAULT_TAGS ),
		);

		return array_map( 'strtolower', (array) $tags );
	}

	/**
	 * Get the list of allowed SVG attributes, with filter override.
	 *
	 * @return list<string> Lowercase attribute names.
	 */
	private function get_allowed_attrs(): array {
		/** This filter is documented in src/Services/HooksRegistry.php */
		$attrs = apply_filters(
			'astrologer_api/svg_allowed_attrs',
			self::DEFAULT_ATTRS,
		);

		return array_map( 'strtolower', (array) $attrs );
	}

	/**
	 * Remove javascript:, data:, and vbscript: URIs from style attributes
	 * and from any remaining href/xlink:href attributes with non-local refs.
	 *
	 * wp_kses handles most of this, but style attributes can slip through.
	 *
	 * @param string $html Partially-cleaned SVG markup.
	 * @return string Further cleaned markup.
	 */
	private function strip_dangerous_uris( string $html ): string {
		// Remove style attribute values containing dangerous schemes.
		$html = (string) preg_replace_callback(
			'/style\s*=\s*["\']([^"\']*)["\']/i',
			static function ( array $m ): string {
				$val = $m[1];
				if ( preg_match( '/(?:javascript|vbscript|data)\s*:/i', $val ) ) {
					return '';
				}
				return $m[0];
			},
			$html,
		);

		// Remove href/xlink:href that point to external URLs (not #anchor).
		$html = (string) preg_replace_callback(
			'/(?:xlink:)?href\s*=\s*["\']([^"\']*)["\']/i',
			static function ( array $m ): string {
				$ref = trim( $m[1] );
				// Allow empty href, #anchor refs, and data:image/svg+xml.
				if ( '' === $ref || str_starts_with( $ref, '#' ) ) {
					return $m[0];
				}
				// Remove any remaining external href.
				return '';
			},
			$html,
		);

		return $html;
	}
}
