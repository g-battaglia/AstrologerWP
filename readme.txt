=== AstrologerWP ===
Contributors: gbattaglia
Tags: astrology, birth chart, horoscope, zodiac, moon phase
Requires at least: 5.6
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 2.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

AstrologerWP is the official WordPress plugin for the Astrologer API on RapidAPI. Generate professional astrology charts, synastry analysis, transits, solar/lunar returns, moon phases, and more.

== Description ==

AstrologerWP is the official WordPress plugin for the [Astrologer API](https://rapidapi.com/gbattaglia/api/astrologer) on RapidAPI. It provides a complete suite of astrology tools for your WordPress site with professional SVG chart generation.

**Third-Party Services:**

This plugin relies on the following external services to function:

1. **Astrologer API** (via RapidAPI) - Used for all astrology calculations and chart generation. When a user submits a birth chart form, the birth data (name, date, time, location coordinates) is sent to this API to compute planetary positions and generate SVG charts.
   * Service URL: [https://rapidapi.com/gbattaglia/api/astrologer](https://rapidapi.com/gbattaglia/api/astrologer)
   * RapidAPI Terms of Use: [https://rapidapi.com/terms/](https://rapidapi.com/terms/)
   * RapidAPI Privacy Policy: [https://rapidapi.com/privacy/](https://rapidapi.com/privacy/)

2. **GeoNames API** - Used for city search autocomplete and timezone resolution. When a user types a city name, the city name is sent to GeoNames to retrieve coordinates and timezone information.
   * Service URL: [https://www.geonames.org/](https://www.geonames.org/)
   * GeoNames Terms: [https://www.geonames.org/export/](https://www.geonames.org/export/)

Both services require user-provided credentials (API keys / usernames) configured in the plugin settings. No data is sent to these services until the user explicitly submits a form or types in the city autocomplete field.

**Chart Types:**

* **Birth Chart** - Generate natal birth charts with planetary positions, aspects, houses, and element/quality distribution
* **Synastry Chart** - Relationship compatibility analysis between two people with relationship scoring
* **Transit Chart** - Analyze current or future planetary transits against a natal chart
* **Composite Chart** - Midpoint composite chart for relationship analysis
* **Solar Return Chart** - Yearly solar return analysis with optional relocation
* **Lunar Return Chart** - Monthly lunar return analysis with optional relocation
* **Moon Phase** - Detailed moon phase information including illumination, upcoming phases, and eclipses
* **Current Sky** - Real-time chart of the current sky at UTC/Greenwich

**Features:**

* 6 chart themes: classic, light, dark, dark-high-contrast, strawberry, black-and-white
* 2 chart styles: classic (traditional wheel) and modern (concentric rings)
* 24 house systems (Placidus, Koch, Whole Sign, and more)
* 2 zodiac types: Tropical and Sidereal (with 21 ayanamsha modes)
* 4 astronomical perspectives
* 10 chart languages: EN, FR, PT, IT, CN, ES, RU, TR, DE, HI
* City autocomplete with timezone resolution via Geonames
* Split chart mode (separate wheel and aspect grid)
* Transparent background option
* Configurable display elements (degree indicators, aspect icons, zodiac ring, etc.)
* Dark and light frontend themes
* Fully responsive SVG charts
* WordPress security best practices (nonce verification, input sanitization, output escaping)

== Installation ==

1. Upload the `astrologerwp` folder to `/wp-content/plugins/`
2. Activate the plugin through the "Plugins" menu in WordPress
3. Go to the AstrologerWP settings page in the admin menu
4. Enter your Astrologer API Key (get one at [RapidAPI](https://rapidapi.com/gbattaglia/api/astrologer/pricing))
5. Enter your Geonames username (free at [geonames.org](http://www.geonames.org/login))
6. Configure your preferred chart settings
7. Add shortcodes to your pages or posts

== Shortcodes ==

* `[astrologer_wp_birth_chart]` - Natal birth chart
* `[astrologer_wp_synastry_chart]` - Synastry (relationship) chart
* `[astrologer_wp_transit_chart]` - Transit analysis chart
* `[astrologer_wp_composite_chart]` - Composite (midpoint) chart
* `[astrologer_wp_solar_return_chart]` - Solar return chart
* `[astrologer_wp_lunar_return_chart]` - Lunar return chart
* `[astrologer_wp_moon_phase]` - Moon phase details
* `[astrologer_wp_now_chart]` - Current sky chart (UTC/Greenwich)

== Frequently Asked Questions ==

= How do I get an API key? =
Subscribe to the [Astrologer API](https://rapidapi.com/gbattaglia/api/astrologer/pricing) on RapidAPI.

= Does this plugin require any dependencies? =
Yes, it requires an active API key from the Astrologer API on RapidAPI and a free Geonames username for city/timezone lookup.

= What is the difference between synastry and composite charts? =
Synastry overlays two natal charts to show how two people's planets interact. Composite creates a single chart from the midpoints of both charts, representing the relationship itself.

= What are solar and lunar returns? =
A Solar Return is a chart cast for the exact moment the Sun returns to its natal position each year. A Lunar Return is the same concept for the Moon, occurring monthly. Both can be calculated for your current location or a different one.

= Can I customize which elements appear on the chart? =
Yes. The settings page allows you to toggle degree indicators, aspect icons, house position comparison, cusp comparison, and zodiac background ring. You can also choose between list and table layout for aspect grids.

== Changelog ==

= 2.0.0 =
* Complete rewrite for Astrologer API v5
* Added composite chart shortcode
* Added solar return chart shortcode
* Added lunar return chart shortcode
* Added moon phase shortcode
* Added current sky (now) chart shortcode
* Added new chart themes: strawberry, black-and-white
* Added modern chart style (concentric rings)
* Added split chart mode
* Added transparent background option
* Added configurable display elements (degree indicators, aspect icons, etc.)
* Added aspect grid type setting (list/table)
* Added relationship scoring for synastry charts
* Reorganized admin settings into logical sections
* Improved error handling and API response parsing
* Updated all endpoints to API v5

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 2.0.0 =
Major update with 5 new chart types, new themes, modern chart style, and full API v5 support. Review your settings after upgrading.

== License ==

This plugin is released under the GPLv2 license. For details, see [GPL License](https://www.gnu.org/licenses/gpl-2.0.html).
