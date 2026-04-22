=== Astrologer API ===
Contributors: astrologerapi
Donate link: https://github.com/astrologer-api
Tags: astrology, natal chart, synastry, transits, moon phase
Requires at least: 6.5
Tested up to: 6.9
Stable tag: 1.0.0
Requires PHP: 8.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Birth charts, synastry, transits, moon phases, and more — powered by the Astrologer API via RapidAPI.

== Description ==

**Astrologer API** integrates the professional Astrologer REST service into your WordPress site. It ships 22 Gutenberg blocks, a full REST namespace (`astrologer/v1`), WP-CLI sub-commands, and a React-powered admin experience that covers everything from first-time setup to daily operations.

= Headline features =

* **22 Gutenberg blocks** — birth form, synastry form, transit form, composite form, solar return, lunar return, now form, compatibility form, natal chart, synastry chart, transit chart, composite chart, solar return chart, lunar return chart, now chart, moon phase widget, positions table, aspects table, elements chart, modalities chart, compatibility score, and relationship score.
* **REST API** — every chart type exposed under `/wp-json/astrologer/v1/` with consistent rate limiting, permission checks, and JSON schemas.
* **WP-CLI** — `wp astrologer chart | cache | settings | health | doctor` for scripted automation.
* **Setup Wizard** — React wizard that walks new administrators through API key configuration, school selection (Modern Western, Traditional, Vedic, Uranian), and language/UI preferences.
* **Four astrological schools** — each seeds defaults for house systems, aspects, and orbs; individual settings remain overridable.
* **Multilingual** — strings are translation-ready. A seed POT file ships under `languages/` and JSON translations load automatically for any enqueued script handle.
* **Accessible by design** — blocks use semantic form markup (fieldset/legend, associated labels, `role="alert"` for errors, `aria-busy` on submitting forms). The admin React apps inherit WordPress's focus ring and high-contrast themes.
* **Secure** — RapidAPI credentials are encrypted at rest with libsodium; a `doctor` command verifies PHP version, extensions, encryption key, permalinks, and rewrite rules.

= Developer surface =

* 10 action hooks and 14 filter hooks, all under the `astrologer_api/` prefix and documented in `docs/hooks.md`.
* Strongly typed services (PHP 8.1+, `declare(strict_types=1)`) with PHPStan level 8 coverage.
* Container-based dependency injection (`src/Container.php`) and `Bootable` contract for module registration.

== Installation ==

1. Upload the plugin ZIP via **Plugins → Add New → Upload**, or extract it to `wp-content/plugins/astrologer-api`.
2. Activate **Astrologer API** from the Plugins screen. You will be redirected to the Setup Wizard on first activation.
3. Paste a RapidAPI key subscribed to the Astrologer API (and optionally a GeoNames username for online geocoding).
4. Pick an astrological school to seed defaults.
5. Define `ASTROLOGER_ENCRYPTION_KEY` in `wp-config.php` to enable secret-at-rest encryption:

   `define( 'ASTROLOGER_ENCRYPTION_KEY', base64_encode( random_bytes( 32 ) ) );`

6. Insert the **Birth Form** block on any page; pair it with a **Natal Chart** block to render the chart for visitors.

== Frequently Asked Questions ==

= Do I need a RapidAPI subscription? =

Yes. The plugin acts as a WordPress-native client for the Astrologer service on RapidAPI. A free tier is available for evaluation; production sites typically move to a paid plan for higher rate limits.

= Does the plugin store birth data? =

Only when you explicitly ask it to. The `Save as post` toggle on form blocks creates an `astrologer_chart` post owned by the current user. All repository access is capability-gated (`astrologer_calculate_chart`, `astrologer_manage_settings`).

= Which PHP version is supported? =

PHP 8.1 or newer. The plugin relies on readonly properties, enums, first-class callable syntax, and the sodium extension. Older PHP versions are intentionally unsupported.

= Can I use the API without the frontend blocks? =

Absolutely. Every block shares its service layer with the REST controllers and the WP-CLI commands. Call `/wp-json/astrologer/v1/natal-chart` directly or script `wp astrologer chart natal …` from your deploy pipelines.

= How do I translate the plugin? =

Copy `languages/astrologer-api.pot` into a locale-specific `.po` file, translate, and place the compiled `.mo` file in `languages/`. For JavaScript translations, run `wp i18n make-json languages` to generate the JSON files that WordPress loads alongside script handles.

= Is the plugin accessible? =

Yes. Forms use semantic markup (`fieldset`, `legend`, associated `<label>`s), errors are announced through `role="alert"` + `aria-live="assertive"`, and the loading state is surfaced via `aria-busy`. RTL layouts are covered with logical-property CSS where possible.

== Screenshots ==

1. Astrologer admin settings page with the five-tab layout.
2. The setup wizard's school-selection step.
3. A published page rendering a natal chart from the Birth Form block.
4. The block inserter highlighting the Birth Form block under the Astrology category.

== Changelog ==

= 1.0.0 =
* Initial public release.
* 22 Gutenberg blocks covering forms, charts, tables, and score widgets.
* REST namespace `astrologer/v1` with per-endpoint rate limiting and permission checks.
* React admin experience: Settings, Setup Wizard, Help Tabs, Documentation.
* WP-CLI sub-commands: `chart`, `cache`, `settings`, `health`, `doctor`.
* Four astrological school presets, custom CPT `astrologer_chart`, custom taxonomy `astrologer_chart_type`.
* Seed `languages/astrologer-api.pot` and JSON translations infrastructure.
* Accessibility improvements: `aria-busy` on submitting forms, semantic landmarks in block markup, logical-property CSS for RTL.

== Upgrade Notice ==

= 1.0.0 =
First stable release. Define `ASTROLOGER_ENCRYPTION_KEY` in `wp-config.php` before activating to enable credential encryption.
