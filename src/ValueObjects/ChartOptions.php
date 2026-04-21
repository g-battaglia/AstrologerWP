<?php
/**
 * ChartOptions value object — all chart rendering configuration.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\ValueObjects;

use Astrologer\Api\Enums\ActivePoint;
use Astrologer\Api\Enums\AspectType;
use Astrologer\Api\Enums\ChartStyle;
use Astrologer\Api\Enums\ChartTheme;
use Astrologer\Api\Enums\DistributionMethod;
use Astrologer\Api\Enums\HouseSystem;
use Astrologer\Api\Enums\Language;
use Astrologer\Api\Enums\PerspectiveType;
use Astrologer\Api\Enums\SiderealMode;
use Astrologer\Api\Enums\ZodiacType;

/**
 * Immutable value object representing all configurable chart rendering options.
 *
 * Maps 1:1 to the query parameters accepted by the upstream Astrologer API
 * chart endpoints.
 */
final readonly class ChartOptions {

	/**
	 * Constructor.
	 *
	 * @param Language                 $language                         Response language.
	 * @param HouseSystem              $house_system                     House system identifier.
	 * @param ZodiacType               $zodiac_type                      Tropical or Sidereal.
	 * @param SiderealMode|null        $sidereal_mode                    Required when zodiac is Sidereal.
	 * @param PerspectiveType          $perspective                      Astronomical perspective.
	 * @param ChartTheme               $theme                            Visual theme for SVG output.
	 * @param ChartStyle               $style                            Classic or modern rendering.
	 * @param list<ActivePoint>        $active_points                    Celestial points to include.
	 * @param list<ActiveAspect>       $active_aspects                   Aspects to calculate with orbs.
	 * @param DistributionMethod       $distribution_method              Element/quality distribution method.
	 * @param array<string,float>|null $custom_distribution_weights      Custom weights per element.
	 * @param bool                     $split_chart                      Whether to render a split (dual) chart.
	 * @param bool                     $transparent_background           Transparent SVG background.
	 * @param bool                     $show_house_position_comparison   Show house position comparison.
	 * @param bool                     $show_cusp_position_comparison    Show cusp position comparison.
	 * @param bool                     $show_degree_indicators           Show degree indicators on chart.
	 * @param bool                     $show_aspect_icons                Show aspect glyph icons.
	 * @param bool                     $show_zodiac_background_ring      Show zodiac background ring.
	 * @param string|null              $custom_title                     Custom title displayed on chart.
	 */
	public function __construct(
		public Language $language,
		public HouseSystem $house_system,
		public ZodiacType $zodiac_type,
		public ?SiderealMode $sidereal_mode,
		public PerspectiveType $perspective,
		public ChartTheme $theme,
		public ChartStyle $style,
		public array $active_points,
		public array $active_aspects,
		public DistributionMethod $distribution_method,
		public ?array $custom_distribution_weights,
		public bool $split_chart,
		public bool $transparent_background,
		public bool $show_house_position_comparison,
		public bool $show_cusp_position_comparison,
		public bool $show_degree_indicators,
		public bool $show_aspect_icons,
		public bool $show_zodiac_background_ring,
		public ?string $custom_title,
	) {
	}

	/**
	 * Create a ChartOptions with sensible defaults for a modern Western chart.
	 *
	 * @return self
	 */
	public static function defaults(): self {
		$default_aspects = array_map(
			static fn ( AspectType $type ): ActiveAspect => new ActiveAspect( $type, $type->default_orb() ),
			AspectType::get_defaults(),
		);

		return new self(
			language: Language::get_default(),
			house_system: HouseSystem::get_default(),
			zodiac_type: ZodiacType::get_default(),
			sidereal_mode: null,
			perspective: PerspectiveType::get_default(),
			theme: ChartTheme::get_default(),
			style: ChartStyle::get_default(),
			active_points: ActivePoint::get_defaults(),
			active_aspects: $default_aspects,
			distribution_method: DistributionMethod::get_default(),
			custom_distribution_weights: null,
			split_chart: false,
			transparent_background: false,
			show_house_position_comparison: true,
			show_cusp_position_comparison: true,
			show_degree_indicators: true,
			show_aspect_icons: true,
			show_zodiac_background_ring: true,
			custom_title: null,
		);
	}

	/**
	 * Create from an associative array, falling back to defaults for missing keys.
	 *
	 * @param array<string,mixed> $data Keyed array with option fields.
	 * @return self
	 */
	public static function from_array( array $data ): self {
		$defaults = self::defaults();

		$sidereal_mode = null;
		if ( isset( $data['sidereal_mode'] ) ) {
			$sidereal_mode = SiderealMode::tryFrom( (string) $data['sidereal_mode'] );
		}

		$active_points = $defaults->active_points;
		if ( isset( $data['active_points'] ) && is_array( $data['active_points'] ) ) {
			$active_points = array_map(
				static fn ( string $p ): ActivePoint => ActivePoint::from( $p ),
				$data['active_points'],
			);
		}

		$active_aspects = $defaults->active_aspects;
		if ( isset( $data['active_aspects'] ) && is_array( $data['active_aspects'] ) ) {
			$active_aspects = array_map(
				static fn ( array $a ): ActiveAspect => ActiveAspect::from_array( $a ),
				$data['active_aspects'],
			);
		}

		$custom_weights = $defaults->custom_distribution_weights;
		if ( isset( $data['custom_distribution_weights'] ) && is_array( $data['custom_distribution_weights'] ) ) {
			$custom_weights = $data['custom_distribution_weights'];
		}

		return new self(
			language: isset( $data['language'] )
				? Language::from( (string) $data['language'] )
				: $defaults->language,
			house_system: isset( $data['houses_system_identifier'] )
				? HouseSystem::from( (string) $data['houses_system_identifier'] )
				: $defaults->house_system,
			zodiac_type: isset( $data['zodiac_type'] )
				? ZodiacType::from( (string) $data['zodiac_type'] )
				: $defaults->zodiac_type,
			sidereal_mode: $sidereal_mode,
			perspective: isset( $data['perspective_type'] )
				? PerspectiveType::from( (string) $data['perspective_type'] )
				: $defaults->perspective,
			theme: isset( $data['theme'] )
				? ChartTheme::from( (string) $data['theme'] )
				: $defaults->theme,
			style: isset( $data['style'] )
				? ChartStyle::from( (string) $data['style'] )
				: $defaults->style,
			active_points: $active_points,
			active_aspects: $active_aspects,
			distribution_method: isset( $data['distribution_method'] )
				? DistributionMethod::from( (string) $data['distribution_method'] )
				: $defaults->distribution_method,
			custom_distribution_weights: $custom_weights,
			split_chart: isset( $data['split_chart'] )
				? (bool) $data['split_chart']
				: $defaults->split_chart,
			transparent_background: isset( $data['transparent_background'] )
				? (bool) $data['transparent_background']
				: $defaults->transparent_background,
			show_house_position_comparison: isset( $data['show_house_position_comparison'] )
				? (bool) $data['show_house_position_comparison']
				: $defaults->show_house_position_comparison,
			show_cusp_position_comparison: isset( $data['show_cusp_position_comparison'] )
				? (bool) $data['show_cusp_position_comparison']
				: $defaults->show_cusp_position_comparison,
			show_degree_indicators: isset( $data['show_degree_indicators'] )
				? (bool) $data['show_degree_indicators']
				: $defaults->show_degree_indicators,
			show_aspect_icons: isset( $data['show_aspect_icons'] )
				? (bool) $data['show_aspect_icons']
				: $defaults->show_aspect_icons,
			show_zodiac_background_ring: isset( $data['show_zodiac_background_ring'] )
				? (bool) $data['show_zodiac_background_ring']
				: $defaults->show_zodiac_background_ring,
			custom_title: $data['custom_title'] ?? $defaults->custom_title,
		);
	}

	/**
	 * Convert to an associative array suitable for API query parameters.
	 *
	 * @return array<string,mixed>
	 */
	public function to_array(): array {
		$result = array(
			'language'                       => $this->language->value,
			'houses_system_identifier'       => $this->house_system->value,
			'zodiac_type'                    => $this->zodiac_type->value,
			'perspective_type'               => $this->perspective->value,
			'theme'                          => $this->theme->value,
			'style'                          => $this->style->value,
			'active_points'                  => array_map(
				static fn ( ActivePoint $p ): string => $p->value,
				$this->active_points,
			),
			'active_aspects'                 => array_map(
				static fn ( ActiveAspect $a ): array => $a->to_array(),
				$this->active_aspects,
			),
			'distribution_method'            => $this->distribution_method->value,
			'split_chart'                    => $this->split_chart,
			'transparent_background'         => $this->transparent_background,
			'show_house_position_comparison' => $this->show_house_position_comparison,
			'show_cusp_position_comparison'  => $this->show_cusp_position_comparison,
			'show_degree_indicators'         => $this->show_degree_indicators,
			'show_aspect_icons'              => $this->show_aspect_icons,
			'show_zodiac_background_ring'    => $this->show_zodiac_background_ring,
		);

		if ( null !== $this->sidereal_mode ) {
			$result['sidereal_mode'] = $this->sidereal_mode->value;
		}

		if ( null !== $this->custom_distribution_weights ) {
			$result['custom_distribution_weights'] = $this->custom_distribution_weights;
		}

		if ( null !== $this->custom_title ) {
			$result['custom_title'] = $this->custom_title;
		}

		return $result;
	}
}
