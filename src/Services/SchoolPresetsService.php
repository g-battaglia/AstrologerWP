<?php
/**
 * SchoolPresetsService — immutable presets for 4 astrological schools.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Services;

use Astrologer\Api\Enums\ActivePoint;
use Astrologer\Api\Enums\AspectType;
use Astrologer\Api\Enums\ChartStyle;
use Astrologer\Api\Enums\ChartTheme;
use Astrologer\Api\Enums\DistributionMethod;
use Astrologer\Api\Enums\HouseSystem;
use Astrologer\Api\Enums\Language;
use Astrologer\Api\Enums\PerspectiveType;
use Astrologer\Api\Enums\School;
use Astrologer\Api\Enums\SiderealMode;
use Astrologer\Api\Enums\ZodiacType;
use Astrologer\Api\ValueObjects\ActiveAspect;
use Astrologer\Api\ValueObjects\ChartOptions;

/**
 * Provides immutable ChartOptions presets for each astrological school.
 *
 * Each method returns a fully-hydrated ChartOptions matching the conventions
 * of the given school. The `merge()` method layers user overrides on top of
 * a preset.
 */
final class SchoolPresetsService {

	/**
	 * Get the ChartOptions preset for a given school.
	 *
	 * @param School $school The astrological school.
	 * @return ChartOptions
	 */
	public function get( School $school ): ChartOptions {
		/** @var ChartOptions $options */
		$options = match ( $school ) {
			School::ModernWestern => $this->modern_western(),
			School::Traditional  => $this->traditional(),
			School::Vedic        => $this->vedic(),
			School::Uranian      => $this->uranian(),
		};

		/**
		 * Filter the ChartOptions preset for a given school.
		 *
		 * @param ChartOptions $options The preset options.
		 * @param School       $school  The school identifier.
		 */
		return apply_filters( 'astrologer_api/school_preset', $options, $school );
	}

	/**
	 * Get all presets keyed by School enum.
	 *
	 * @return array<string, ChartOptions>
	 */
	public function all(): array {
		$result = array();

		foreach ( School::cases() as $school ) {
			$result[ $school->value ] = $this->get( $school );
		}

		return $result;
	}

	/**
	 * Apply a preset then layer user overrides on top.
	 *
	 * The overrides array uses the same format accepted by ChartOptions::from_array().
	 *
	 * @param School               $school   The school preset to start from.
	 * @param array<string,mixed>  $overrides User overrides (ChartOptions::from_array format).
	 * @return ChartOptions
	 */
	public function merge( School $school, array $overrides ): ChartOptions {
		$preset = $this->get( $school );

		// Convert preset to array, layer overrides, re-hydrate.
		$merged = array_merge( $preset->to_array(), $overrides );

		return ChartOptions::from_array( $merged );
	}

	/**
	 * Modern Western (default) preset.
	 *
	 * Placidus houses, Tropical zodiac, standard modern planets + nodes + Chiron + Lilith + angles.
	 *
	 * @return ChartOptions
	 */
	private function modern_western(): ChartOptions {
		return new ChartOptions(
			language: Language::get_default(),
			house_system: HouseSystem::Placidus,
			zodiac_type: ZodiacType::Tropical,
			sidereal_mode: null,
			perspective: PerspectiveType::ApparentGeocentric,
			theme: ChartTheme::Classic,
			style: ChartStyle::Classic,
			active_points: array(
				ActivePoint::Sun,
				ActivePoint::Moon,
				ActivePoint::Mercury,
				ActivePoint::Venus,
				ActivePoint::Mars,
				ActivePoint::Jupiter,
				ActivePoint::Saturn,
				ActivePoint::Uranus,
				ActivePoint::Neptune,
				ActivePoint::Pluto,
				ActivePoint::True_North_Lunar_Node,
				ActivePoint::True_South_Lunar_Node,
				ActivePoint::Chiron,
				ActivePoint::Mean_Lilith,
				ActivePoint::Ascendant,
				ActivePoint::Medium_Coeli,
				ActivePoint::Descendant,
				ActivePoint::Imum_Coeli,
			),
			active_aspects: array(
				new ActiveAspect( AspectType::Conjunction, 8.0 ),
				new ActiveAspect( AspectType::Opposition, 8.0 ),
				new ActiveAspect( AspectType::Trine, 8.0 ),
				new ActiveAspect( AspectType::Square, 8.0 ),
				new ActiveAspect( AspectType::Sextile, 6.0 ),
				new ActiveAspect( AspectType::Quincunx, 2.0 ),
			),
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
	 * Traditional / Hellenistic preset.
	 *
	 * Whole-Sign houses, Tropical zodiac, classical planets only, Arabic parts, tight orbs.
	 *
	 * @return ChartOptions
	 */
	private function traditional(): ChartOptions {
		return new ChartOptions(
			language: Language::get_default(),
			house_system: HouseSystem::EqualWholeSign,
			zodiac_type: ZodiacType::Tropical,
			sidereal_mode: null,
			perspective: PerspectiveType::ApparentGeocentric,
			theme: ChartTheme::Classic,
			style: ChartStyle::Classic,
			active_points: array(
				ActivePoint::Sun,
				ActivePoint::Moon,
				ActivePoint::Mercury,
				ActivePoint::Venus,
				ActivePoint::Mars,
				ActivePoint::Jupiter,
				ActivePoint::Saturn,
				ActivePoint::True_North_Lunar_Node,
				ActivePoint::True_South_Lunar_Node,
				ActivePoint::Pars_Fortunae,
				ActivePoint::Pars_Spiritus,
				ActivePoint::Ascendant,
				ActivePoint::Medium_Coeli,
			),
			active_aspects: array(
				new ActiveAspect( AspectType::Conjunction, 6.0 ),
				new ActiveAspect( AspectType::Opposition, 6.0 ),
				new ActiveAspect( AspectType::Trine, 6.0 ),
				new ActiveAspect( AspectType::Square, 6.0 ),
				new ActiveAspect( AspectType::Sextile, 3.0 ),
			),
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
	 * Vedic / Jyotish preset.
	 *
	 * Whole-Sign houses, Sidereal zodiac (Lahiri ayanamsa), classical planets + Rahu/Ketu,
	 * wider orbs (drishti convention).
	 *
	 * @return ChartOptions
	 */
	private function vedic(): ChartOptions {
		return new ChartOptions(
			language: Language::get_default(),
			house_system: HouseSystem::EqualWholeSign,
			zodiac_type: ZodiacType::Sidereal,
			sidereal_mode: SiderealMode::LAHIRI,
			perspective: PerspectiveType::ApparentGeocentric,
			theme: ChartTheme::Classic,
			style: ChartStyle::Classic,
			active_points: array(
				ActivePoint::Sun,
				ActivePoint::Moon,
				ActivePoint::Mercury,
				ActivePoint::Venus,
				ActivePoint::Mars,
				ActivePoint::Jupiter,
				ActivePoint::Saturn,
				ActivePoint::Mean_North_Lunar_Node,
				ActivePoint::Mean_South_Lunar_Node,
				ActivePoint::Ascendant,
			),
			active_aspects: array(
				new ActiveAspect( AspectType::Conjunction, 10.0 ),
				new ActiveAspect( AspectType::Opposition, 10.0 ),
				new ActiveAspect( AspectType::Trine, 10.0 ),
				new ActiveAspect( AspectType::Square, 10.0 ),
				new ActiveAspect( AspectType::Sextile, 6.0 ),
			),
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
	 * Uranian / Hamburg preset.
	 *
	 * Meridian houses, Tropical zodiac, all classical planets + 8 hypothetical Uranian bodies,
	 * very tight orbs (typical Uranian 90-degree dial work).
	 *
	 * @return ChartOptions
	 */
	private function uranian(): ChartOptions {
		return new ChartOptions(
			language: Language::get_default(),
			house_system: HouseSystem::Meridian,
			zodiac_type: ZodiacType::Tropical,
			sidereal_mode: null,
			perspective: PerspectiveType::ApparentGeocentric,
			theme: ChartTheme::Classic,
			style: ChartStyle::Classic,
			active_points: array(
				ActivePoint::Sun,
				ActivePoint::Moon,
				ActivePoint::Mercury,
				ActivePoint::Venus,
				ActivePoint::Mars,
				ActivePoint::Jupiter,
				ActivePoint::Saturn,
				ActivePoint::Uranus,
				ActivePoint::Neptune,
				ActivePoint::Pluto,
				ActivePoint::Mean_North_Lunar_Node,
				ActivePoint::Cupido,
				ActivePoint::Hades,
				ActivePoint::Zeus,
				ActivePoint::Kronos,
				ActivePoint::Apollon,
				ActivePoint::Admetos,
				ActivePoint::Vulkanus,
				ActivePoint::Poseidon,
			),
			active_aspects: array(
				new ActiveAspect( AspectType::Conjunction, 1.0 ),
				new ActiveAspect( AspectType::Semi_Square, 0.5 ),
				new ActiveAspect( AspectType::Square, 1.0 ),
				new ActiveAspect( AspectType::Sesquiquadrate, 0.5 ),
				new ActiveAspect( AspectType::Opposition, 1.0 ),
			),
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
}
