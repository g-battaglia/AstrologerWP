<?php
/**
 * ChartOptionsSchema — shared JSON schema for chart rendering options.
 *
 * Used by chart, synastry, transit, and return endpoints that accept
 * visual configuration parameters.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Rest\Schemas;

use Astrologer\Api\Enums\ActivePoint;
use Astrologer\Api\Enums\AspectType;
use Astrologer\Api\Enums\ChartStyle;
use Astrologer\Api\Enums\ChartTheme;
use Astrologer\Api\Enums\DistributionMethod;
use Astrologer\Api\Enums\Language;

/**
 * Provides the JSON schema definition for chart rendering options.
 *
 * Controllers merge this into their route args alongside SubjectSchema
 * or other endpoint-specific parameters.
 */
final class ChartOptionsSchema {

	/**
	 * Return the JSON schema array for chart option parameters.
	 *
	 * All fields are optional (have defaults) so callers can merge
	 * them into route args without breaking required-field checks.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public static function get(): array {
		$point_values    = array_map(
			static fn ( ActivePoint $p ): string => $p->value,
			ActivePoint::cases(),
		);
		$aspect_values   = array_map(
			static fn ( AspectType $a ): string => $a->value,
			AspectType::cases(),
		);
		$theme_values    = array_map(
			static fn ( ChartTheme $t ): string => $t->value,
			ChartTheme::cases(),
		);
		$style_values    = array_map(
			static fn ( ChartStyle $s ): string => $s->value,
			ChartStyle::cases(),
		);
		$language_values = array_map(
			static fn ( Language $l ): string => $l->value,
			Language::cases(),
		);
		$dist_values     = array_map(
			static fn ( DistributionMethod $d ): string => $d->value,
			DistributionMethod::cases(),
		);

		return array(
			'language'                       => array(
				'description'       => __( 'Response language.', 'astrologer-api' ),
				'type'              => 'string',
				'default'           => Language::get_default()->value,
				'enum'              => $language_values,
				'sanitize_callback' => 'sanitize_text_field',
			),
			'theme'                          => array(
				'description'       => __( 'Visual theme for SVG output.', 'astrologer-api' ),
				'type'              => 'string',
				'default'           => ChartTheme::get_default()->value,
				'enum'              => $theme_values,
				'sanitize_callback' => 'sanitize_text_field',
			),
			'style'                          => array(
				'description'       => __( 'Chart rendering style.', 'astrologer-api' ),
				'type'              => 'string',
				'default'           => ChartStyle::get_default()->value,
				'enum'              => $style_values,
				'sanitize_callback' => 'sanitize_text_field',
			),
			'active_points'                  => array(
				'description'       => __( 'Celestial points to include.', 'astrologer-api' ),
				'type'              => 'array',
				'items'             => array(
					'type' => 'string',
					'enum' => $point_values,
				),
				'sanitize_callback' => static function ( mixed $value ): array {
					if ( ! is_array( $value ) ) {
						return array();
					}
					return array_map( 'sanitize_text_field', $value );
				},
				'validate_callback' => static function ( mixed $value ): bool {
					if ( ! is_array( $value ) ) {
						return true; // Optional field, absence is valid.
					}
					foreach ( $value as $item ) {
						if ( ! is_string( $item ) ) {
							return false;
						}
					}
					return true;
				},
			),
			'active_aspects'                 => array(
				'description'       => __( 'Aspects to calculate with orbs.', 'astrologer-api' ),
				'type'              => 'array',
				'items'             => array(
					'type'       => 'object',
					'properties' => array(
						'type' => array(
							'type' => 'string',
							'enum' => $aspect_values,
						),
						'orb'  => array(
							'type'    => 'number',
							'minimum' => 0,
						),
					),
				),
				'sanitize_callback' => static function ( mixed $value ): array {
					if ( ! is_array( $value ) ) {
						return array();
					}
					return array_map(
						static function ( mixed $item ): array {
							if ( ! is_array( $item ) ) {
								return array();
							}
							return array(
								'type' => isset( $item['type'] ) ? sanitize_text_field( (string) $item['type'] ) : '',
								'orb'  => isset( $item['orb'] ) ? (float) $item['orb'] : 0.0,
							);
						},
						$value,
					);
				},
				'validate_callback' => static function ( mixed $value ): bool {
					if ( ! is_array( $value ) ) {
						return true;
					}
					foreach ( $value as $item ) {
						if ( ! is_array( $item ) ) {
							return false;
						}
						if ( ! isset( $item['type'] ) || ! is_string( $item['type'] ) ) {
							return false;
						}
					}
					return true;
				},
			),
			'distribution_method'            => array(
				'description'       => __( 'Element/quality distribution method.', 'astrologer-api' ),
				'type'              => 'string',
				'default'           => DistributionMethod::get_default()->value,
				'enum'              => $dist_values,
				'sanitize_callback' => 'sanitize_text_field',
			),
			'custom_distribution_weights'    => array(
				'description'       => __( 'Custom weights per element for distribution.', 'astrologer-api' ),
				'type'              => 'object',
				'sanitize_callback' => static function ( mixed $value ): ?array {
					if ( ! is_array( $value ) ) {
						return null;
					}
					$clean = array();
					foreach ( $value as $key => $val ) {
						$clean[ sanitize_text_field( (string) $key ) ] = (float) $val;
					}
					return $clean;
				},
			),
			'split_chart'                    => array(
				'description'       => __( 'Whether to render a split (dual) chart.', 'astrologer-api' ),
				'type'              => 'boolean',
				'default'           => false,
				'sanitize_callback' => static fn ( mixed $v ): bool => (bool) $v,
			),
			'transparent_background'         => array(
				'description'       => __( 'Transparent SVG background.', 'astrologer-api' ),
				'type'              => 'boolean',
				'default'           => false,
				'sanitize_callback' => static fn ( mixed $v ): bool => (bool) $v,
			),
			'show_house_position_comparison' => array(
				'description'       => __( 'Show house position comparison.', 'astrologer-api' ),
				'type'              => 'boolean',
				'default'           => true,
				'sanitize_callback' => static fn ( mixed $v ): bool => (bool) $v,
			),
			'show_cusp_position_comparison'  => array(
				'description'       => __( 'Show cusp position comparison.', 'astrologer-api' ),
				'type'              => 'boolean',
				'default'           => true,
				'sanitize_callback' => static fn ( mixed $v ): bool => (bool) $v,
			),
			'show_degree_indicators'         => array(
				'description'       => __( 'Show degree indicators on chart.', 'astrologer-api' ),
				'type'              => 'boolean',
				'default'           => true,
				'sanitize_callback' => static fn ( mixed $v ): bool => (bool) $v,
			),
			'show_aspect_icons'              => array(
				'description'       => __( 'Show aspect glyph icons.', 'astrologer-api' ),
				'type'              => 'boolean',
				'default'           => true,
				'sanitize_callback' => static fn ( mixed $v ): bool => (bool) $v,
			),
			'show_zodiac_background_ring'    => array(
				'description'       => __( 'Show zodiac background ring.', 'astrologer-api' ),
				'type'              => 'boolean',
				'default'           => true,
				'sanitize_callback' => static fn ( mixed $v ): bool => (bool) $v,
			),
			'custom_title'                   => array(
				'description'       => __( 'Custom title displayed on chart.', 'astrologer-api' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'axis_orb_limit'                 => array(
				'description'       => __( 'Orb limit for axis aspects.', 'astrologer-api' ),
				'type'              => 'number',
				'sanitize_callback' => 'floatval',
			),
		);
	}
}
