=== Astrologer API Playground ===
Contributors: gbattaglia
Tags: astrology, natal chart, horoscope, zodiac, synastry
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display natal charts, synastry, transits, composite charts, solar/lunar returns, and compatibility scores on your WordPress site.

== Description ==

Astrologer API Playground integrates the [Astrologer API](https://rapidapi.com/gbattaglia/api/astrologer) into WordPress with a modern React-based frontend.

**Features:**

* **Natal Chart** -- SVG birth chart with planetary positions
* **Synastry Chart** -- relationship compatibility between two charts
* **Transit Chart** -- current planetary transits over a natal chart
* **Composite Chart** -- midpoint composite for relationships
* **Solar Return Chart** -- yearly solar return analysis
* **Lunar Return Chart** -- monthly lunar return analysis
* **Compatibility Score** -- numerical relationship compatibility
* **Current Sky (Now)** -- real-time planetary positions
* **Interactive Forms** -- users can input birth data directly on the frontend
* **Gutenberg Blocks** -- drag-and-drop blocks for the modern editor
* **Shortcodes** -- embed charts in any post, page, or widget area
* **Admin Settings** -- configure API key, language, house system, and chart theme

**Shortcodes:**

* `[astrologer_birth_form]` -- interactive birth data form
* `[astrologer_natal_chart]` -- static natal chart
* `[astrologer_aspects_table]` -- planetary aspects table
* `[astrologer_elements_chart]` -- elements distribution
* `[astrologer_modalities_chart]` -- modalities distribution
* `[astrologer_synastry_form]` -- synastry form
* `[astrologer_transit_form]` -- transit form
* `[astrologer_composite_form]` -- composite chart form
* `[astrologer_solar_return_form]` -- solar return form
* `[astrologer_lunar_return_form]` -- lunar return form
* `[astrologer_now_form]` -- current sky form
* `[astrologer_compatibility_form]` -- compatibility score form

== Installation ==

1. Upload the `astrologer-api-playground` folder to `/wp-content/plugins/`.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Go to **Settings > Astrologer API** and enter your RapidAPI key.
4. (Optional) Enter a GeoNames username for city autocomplete.
5. Use shortcodes or Gutenberg blocks to display charts on your site.

== Frequently Asked Questions ==

= Where do I get an API key? =

Sign up at [RapidAPI](https://rapidapi.com/gbattaglia/api/astrologer) and subscribe to the Astrologer API. Copy the API key from your RapidAPI dashboard.

= Is the API key kept secure? =

Yes. The API key is stored in the WordPress database and never exposed to frontend JavaScript. All API calls go through a server-side PHP REST bridge.

= What house systems are supported? =

Placidus, Koch, Porphyrius, Regiomontanus, Campanus, Equal, Whole Sign, and Morinus.

= Can I use this in languages other than English? =

The API supports English, Italian, French, Spanish, Portuguese, German, Russian, Turkish, Chinese, and Hindi. Select the language in Settings.

= What is the GeoNames username for? =

It enables the city autocomplete feature so users can search for a city and have latitude, longitude, and timezone filled in automatically.

== Screenshots ==

1. Natal chart SVG rendered on the frontend.
2. Admin settings page with API configuration.
3. Interactive birth data form with chart output.
4. Synastry chart comparing two birth charts.

== Changelog ==

= 1.0.0 =
* Initial release.
* Natal chart, synastry, transits, composite, solar/lunar return, compatibility, and current sky.
* Gutenberg blocks and shortcodes.
* Admin settings page with React UI.

== Upgrade Notice ==

= 1.0.0 =
Initial release.

== Third-Party Services ==

This plugin connects to external third-party services. By using this plugin, you acknowledge and agree to the terms of service and privacy policies of these providers.

= Astrologer API (via RapidAPI) =

This plugin sends birth data (name, date, time, location) to the Astrologer API to compute astrological charts and data. Requests are made from your server to the API endpoint configured in Settings (default: `https://astrologer.p.rapidapi.com`).

* **Service URL:** [https://rapidapi.com/gbattaglia/api/astrologer](https://rapidapi.com/gbattaglia/api/astrologer)
* **Terms of Service:** [https://rapidapi.com/terms](https://rapidapi.com/terms)
* **Privacy Policy:** [https://rapidapi.com/privacy](https://rapidapi.com/privacy)

= GeoNames =

When a GeoNames username is configured and city autocomplete is used, the plugin sends city search queries to the GeoNames API to retrieve geographic coordinates and timezone data.

* **Service URL:** [https://www.geonames.org/](https://www.geonames.org/)
* **Terms of Service:** [https://www.geonames.org/export/](https://www.geonames.org/export/)
* **Privacy Policy:** [https://www.geonames.org/privacy.html](https://www.geonames.org/privacy.html)
