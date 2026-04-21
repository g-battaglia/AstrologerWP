<?php
/**
 * Active point enum — celestial bodies, points, fixed stars, and other astrological points.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Enums;

/**
 * Enum representing all selectable astrological points in the Astrologer API.
 *
 * Covers classical planets, lunar nodes, Lilith variants, asteroids,
 * trans-Neptunian objects, hypothetical Uranian planets, fixed stars,
 * Arabic parts, and chart angles.
 */
enum ActivePoint: string {

	// Classical planets (10).
	case Sun     = 'Sun';
	case Moon    = 'Moon';
	case Mercury = 'Mercury';
	case Venus   = 'Venus';
	case Mars    = 'Mars';
	case Jupiter = 'Jupiter';
	case Saturn  = 'Saturn';
	case Uranus  = 'Uranus';
	case Neptune = 'Neptune';
	case Pluto   = 'Pluto';

	// Lunar nodes (4).
	case Mean_North_Lunar_Node = 'Mean_North_Lunar_Node';
	case True_North_Lunar_Node = 'True_North_Lunar_Node';
	case Mean_South_Lunar_Node = 'Mean_South_Lunar_Node';
	case True_South_Lunar_Node = 'True_South_Lunar_Node';

	// Lilith / Dark Moon variants (4).
	case Mean_Lilith         = 'Mean_Lilith';
	case True_Lilith         = 'True_Lilith';
	case Osculating_Lilith   = 'Osculating_Lilith';
	case Interpolated_Lilith = 'Interpolated_Lilith';

	// Priapus, White Moon, Earth.
	case Mean_Priapus = 'Mean_Priapus';
	case True_Priapus = 'True_Priapus';
	case White_Moon   = 'White_Moon';
	case Earth        = 'Earth';

	// Centaurs.
	case Chiron = 'Chiron';
	case Pholus = 'Pholus';

	// Asteroids (4).
	case Ceres  = 'Ceres';
	case Pallas = 'Pallas';
	case Juno   = 'Juno';
	case Vesta  = 'Vesta';

	// Trans-Neptunian objects (8).
	case Eris     = 'Eris';
	case Sedna    = 'Sedna';
	case Haumea   = 'Haumea';
	case Makemake = 'Makemake';
	case Ixion    = 'Ixion';
	case Orcus    = 'Orcus';
	case Quaoar   = 'Quaoar';
	case Chaos    = 'Chaos';

	// Hypothetical Uranian planets (8).
	case Cupido   = 'Cupido';
	case Hades    = 'Hades';
	case Zeus     = 'Zeus';
	case Kronos   = 'Kronos';
	case Apollon  = 'Apollon';
	case Admetos  = 'Admetos';
	case Vulkanus = 'Vulkanus';
	case Poseidon = 'Poseidon';

	// Fixed stars (23).
	case Regulus      = 'Regulus';
	case Spica        = 'Spica';
	case Aldebaran    = 'Aldebaran';
	case Antares      = 'Antares';
	case Sirius       = 'Sirius';
	case Fomalhaut    = 'Fomalhaut';
	case Algol        = 'Algol';
	case Betelgeuse   = 'Betelgeuse';
	case Canopus      = 'Canopus';
	case Procyon      = 'Procyon';
	case Arcturus     = 'Arcturus';
	case Pollux       = 'Pollux';
	case Deneb        = 'Deneb';
	case Altair       = 'Altair';
	case Rigel        = 'Rigel';
	case Achernar     = 'Achernar';
	case Capella      = 'Capella';
	case Vega         = 'Vega';
	case Alcyone      = 'Alcyone';
	case Alphecca     = 'Alphecca';
	case Algorab      = 'Algorab';
	case Deneb_Algedi = 'Deneb_Algedi';
	case Alkaid       = 'Alkaid';

	// Arabic parts (4).
	case Pars_Fortunae = 'Pars_Fortunae';
	case Pars_Spiritus = 'Pars_Spiritus';
	case Pars_Amoris   = 'Pars_Amoris';
	case Pars_Fidei    = 'Pars_Fidei';

	// Chart angles (6).
	case Ascendant    = 'Ascendant';
	case Medium_Coeli = 'Medium_Coeli';
	case Descendant   = 'Descendant';
	case Imum_Coeli   = 'Imum_Coeli';
	case Vertex       = 'Vertex';
	case Anti_Vertex  = 'Anti_Vertex';

	/**
	 * Human-readable label for the active point.
	 *
	 * @return string
	 */
	public function label(): string {
		return match ( $this ) {
			self::Sun                     => __( 'Sun', 'astrologer-api' ),
			self::Moon                    => __( 'Moon', 'astrologer-api' ),
			self::Mercury                 => __( 'Mercury', 'astrologer-api' ),
			self::Venus                   => __( 'Venus', 'astrologer-api' ),
			self::Mars                    => __( 'Mars', 'astrologer-api' ),
			self::Jupiter                 => __( 'Jupiter', 'astrologer-api' ),
			self::Saturn                  => __( 'Saturn', 'astrologer-api' ),
			self::Uranus                  => __( 'Uranus', 'astrologer-api' ),
			self::Neptune                 => __( 'Neptune', 'astrologer-api' ),
			self::Pluto                   => __( 'Pluto', 'astrologer-api' ),
			self::Mean_North_Lunar_Node   => __( 'Mean North Node', 'astrologer-api' ),
			self::True_North_Lunar_Node   => __( 'True North Node', 'astrologer-api' ),
			self::Mean_South_Lunar_Node   => __( 'Mean South Node', 'astrologer-api' ),
			self::True_South_Lunar_Node   => __( 'True South Node', 'astrologer-api' ),
			self::Mean_Lilith             => __( 'Mean Lilith', 'astrologer-api' ),
			self::True_Lilith             => __( 'True Lilith', 'astrologer-api' ),
			self::Osculating_Lilith       => __( 'Osculating Lilith', 'astrologer-api' ),
			self::Interpolated_Lilith     => __( 'Interpolated Lilith', 'astrologer-api' ),
			self::Mean_Priapus            => __( 'Mean Priapus', 'astrologer-api' ),
			self::True_Priapus            => __( 'True Priapus', 'astrologer-api' ),
			self::White_Moon              => __( 'White Moon', 'astrologer-api' ),
			self::Earth                   => __( 'Earth', 'astrologer-api' ),
			self::Chiron                  => __( 'Chiron', 'astrologer-api' ),
			self::Pholus                  => __( 'Pholus', 'astrologer-api' ),
			self::Ceres                   => __( 'Ceres', 'astrologer-api' ),
			self::Pallas                  => __( 'Pallas', 'astrologer-api' ),
			self::Juno                    => __( 'Juno', 'astrologer-api' ),
			self::Vesta                   => __( 'Vesta', 'astrologer-api' ),
			self::Eris                    => __( 'Eris', 'astrologer-api' ),
			self::Sedna                   => __( 'Sedna', 'astrologer-api' ),
			self::Haumea                  => __( 'Haumea', 'astrologer-api' ),
			self::Makemake                => __( 'Makemake', 'astrologer-api' ),
			self::Ixion                   => __( 'Ixion', 'astrologer-api' ),
			self::Orcus                   => __( 'Orcus', 'astrologer-api' ),
			self::Quaoar                  => __( 'Quaoar', 'astrologer-api' ),
			self::Chaos                   => __( 'Chaos', 'astrologer-api' ),
			self::Cupido                  => __( 'Cupido', 'astrologer-api' ),
			self::Hades                   => __( 'Hades', 'astrologer-api' ),
			self::Zeus                    => __( 'Zeus', 'astrologer-api' ),
			self::Kronos                  => __( 'Kronos', 'astrologer-api' ),
			self::Apollon                 => __( 'Apollon', 'astrologer-api' ),
			self::Admetos                 => __( 'Admetos', 'astrologer-api' ),
			self::Vulkanus                => __( 'Vulkanus', 'astrologer-api' ),
			self::Poseidon                => __( 'Poseidon', 'astrologer-api' ),
			self::Regulus                 => __( 'Regulus', 'astrologer-api' ),
			self::Spica                   => __( 'Spica', 'astrologer-api' ),
			self::Aldebaran               => __( 'Aldebaran', 'astrologer-api' ),
			self::Antares                 => __( 'Antares', 'astrologer-api' ),
			self::Sirius                  => __( 'Sirius', 'astrologer-api' ),
			self::Fomalhaut               => __( 'Fomalhaut', 'astrologer-api' ),
			self::Algol                   => __( 'Algol', 'astrologer-api' ),
			self::Betelgeuse              => __( 'Betelgeuse', 'astrologer-api' ),
			self::Canopus                 => __( 'Canopus', 'astrologer-api' ),
			self::Procyon                 => __( 'Procyon', 'astrologer-api' ),
			self::Arcturus                => __( 'Arcturus', 'astrologer-api' ),
			self::Pollux                  => __( 'Pollux', 'astrologer-api' ),
			self::Deneb                   => __( 'Deneb', 'astrologer-api' ),
			self::Altair                  => __( 'Altair', 'astrologer-api' ),
			self::Rigel                   => __( 'Rigel', 'astrologer-api' ),
			self::Achernar                => __( 'Achernar', 'astrologer-api' ),
			self::Capella                 => __( 'Capella', 'astrologer-api' ),
			self::Vega                    => __( 'Vega', 'astrologer-api' ),
			self::Alcyone                 => __( 'Alcyone', 'astrologer-api' ),
			self::Alphecca                => __( 'Alphecca', 'astrologer-api' ),
			self::Algorab                 => __( 'Algorab', 'astrologer-api' ),
			self::Deneb_Algedi            => __( 'Deneb Algedi', 'astrologer-api' ),
			self::Alkaid                  => __( 'Alkaid', 'astrologer-api' ),
			self::Pars_Fortunae           => __( 'Pars Fortunae', 'astrologer-api' ),
			self::Pars_Spiritus           => __( 'Pars Spiritus', 'astrologer-api' ),
			self::Pars_Amoris             => __( 'Pars Amoris', 'astrologer-api' ),
			self::Pars_Fidei              => __( 'Pars Fidei', 'astrologer-api' ),
			self::Ascendant               => __( 'Ascendant', 'astrologer-api' ),
			self::Medium_Coeli            => __( 'Medium Coeli', 'astrologer-api' ),
			self::Descendant              => __( 'Descendant', 'astrologer-api' ),
			self::Imum_Coeli              => __( 'Imum Coeli', 'astrologer-api' ),
			self::Vertex                  => __( 'Vertex', 'astrologer-api' ),
			self::Anti_Vertex             => __( 'Anti-Vertex', 'astrologer-api' ),
		};
	}

	/**
	 * Return the default set of active points used in a standard chart.
	 *
	 * @return list<self>
	 */
	public static function get_defaults(): array {
		return array(
			self::Sun,
			self::Moon,
			self::Mercury,
			self::Venus,
			self::Mars,
			self::Jupiter,
			self::Saturn,
			self::Uranus,
			self::Neptune,
			self::Pluto,
			self::True_North_Lunar_Node,
			self::True_South_Lunar_Node,
			self::Chiron,
			self::Mean_Lilith,
			self::Ascendant,
			self::Medium_Coeli,
			self::Descendant,
			self::Imum_Coeli,
		);
	}

	/**
	 * Whether this point is a chart angle (ASC/MC/DSC/IC/Vertex).
	 *
	 * @return bool
	 */
	public function is_angle(): bool {
		return match ( $this ) {
			self::Ascendant, self::Medium_Coeli, self::Descendant,
			self::Imum_Coeli, self::Vertex, self::Anti_Vertex => true,
			default => false,
		};
	}

	/**
	 * Whether this point is a classical planet (Sun through Pluto).
	 *
	 * @return bool
	 */
	public function is_classical_planet(): bool {
		return match ( $this ) {
			self::Sun, self::Moon, self::Mercury, self::Venus,
			self::Mars, self::Jupiter, self::Saturn, self::Uranus,
			self::Neptune, self::Pluto => true,
			default => false,
		};
	}
}
