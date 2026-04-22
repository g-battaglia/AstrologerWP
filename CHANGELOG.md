# Changelog

All notable changes to the **Astrologer API** WordPress plugin are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] — 2026-04-22

### Added

#### Core infrastructure
- PSR-4 autoload under `Astrologer\Api\` namespace.
- Lazy service container with factory registration.
- `Bootable` interface for module lifecycle.
- PHP 8.1+ requirement, `declare(strict_types=1)` everywhere.

#### Data layer
- 11 string-backed enums (HouseSystem 23 cases, SiderealMode 48, PerspectiveType 11, ChartType 9, ChartTheme 6, School 4, etc.).
- 4 readonly Value Objects (BirthData, GeoLocation, ChartOptions, ActiveAspect).
- 10 readonly DTOs for request/response shapes.
- Custom post type `astrologer_chart` + `chart_type` taxonomy with 7 seeded terms.
- Repositories: `SettingsRepository` (wp_options + sodium encryption), `ChartRepository` (CPT CRUD), `BirthDataRepository` (user meta).

#### Services
- `ApiClient` — RapidAPI proxy with exponential retry (max 2, 500ms base backoff).
- `GeonamesClient` — city autocomplete + timezone lookup via secure.geonames.org.
- `ChartService` — 28+ methods mapping to upstream endpoints.
- `SchoolPresetsService` — 4 immutable presets (modern_western, traditional, vedic, uranian).
- `HooksRegistry` — 10 actions + 14 filters documented.
- `RateLimiter` — transient sliding window, per-IP + per-user buckets, admin exemption.
- `SvgSanitizer` — wp_kses allowlist (24 SVG tags, 75 attrs), strips script/on*/javascript:/data: URIs.

#### REST API (19 controllers)
- Chart controllers: natal, synastry, transit, composite, solar-return, lunar-return, now, birth, birth-data (9 routes).
- Relationship: `/relationship-score`, `/synastry-aspects`.
- MoonPhase (4 routes): current, at, range, next/{phase}.
- AI Context (8 routes): subject, natal, synastry, transit, composite, solar-return, lunar-return, moon-phase.
- Utility: MCP (JSON-RPC 2.0 proxy), Health, Geonames, Settings, Chart CPT CRUD, Bindings metadata.
- All controllers inherit `AbstractController` with permission + rate-limit + error mapping.
- `RestServiceProvider` registers all routes on `rest_api_init`.

#### Admin UI
- Top-level "Astrologer" menu with Settings, Charts, Documentation, Setup Wizard subpages.
- Settings React app (6 tabs): API Credentials, Astrology Defaults, UI, Cron, Capabilities, Integrations.
- Setup Wizard React app (6 steps): Welcome, API Key, School, Language, Demo, Done. First-visit auto-redirect.
- Contextual help tabs on 4 admin screens.
- Documentation page rendering 6 markdown pages via league/commonmark.

#### Gutenberg blocks (22 total)
- 8 form blocks: birth, synastry, transit, composite, solar-return, lunar-return, now, compatibility.
- 7 chart display blocks: natal, synastry, transit, composite, solar-return, lunar-return, now.
- 7 data display blocks: positions-table, aspects-table, elements-chart, modalities-chart, compatibility-score, relationship-score, moon-phase.
- 6 block patterns: simple-natal, synastry-compat, transit-today, solar-return-annual, moon-phase-widget, daily-dashboard.
- 28 block variations (7 form blocks × 4 school presets).
- 2 FSE templates: chart-single, chart-archive (WP 6.7+).
- Block Bindings API source `astrologer-api/chart-data` (WP 6.5+) with dot-notation path lookup.

#### Frontend (Interactivity API)
- 8 form stores with state machine (idle/submitting/success/error).
- Shared chart-display store subscribing to cross-store events via pub/sub bus.
- Debounced city autocomplete (300ms) via Geonames.
- Self-refreshing moon-phase store with configurable interval.
- `AssetEnqueuer` conditionally enqueues view modules via `has_block()` with per-block localized globals.

#### Cron + WP-CLI
- 3 scheduled daily events: daily-transits, daily-moon-phase, solar-return-reminder.
- Solar-return email reminder within 7-day window, per-user meta tracking.
- WP-CLI commands: `wp astrologer chart|cache|settings|health|doctor`.
- Doctor command checks PHP version, extensions, encryption key, API key, permalinks.

#### i18n, a11y, docs
- POT file with ~50 strings seeded from src/ + blocks/.
- Italian JSON placeholder (it_IT) demonstrating JED format.
- `ScriptTranslations` wires `wp_set_script_translations` for all admin + block handles.
- RTL-safe CSS (logical properties: `margin-inline-start`, `text-align: start/end`).
- Axe accessibility E2E suite covering frontend and admin surfaces.
- 6 documentation markdown pages (user-guide, shortcodes, blocks, hooks, cli, rest-api).
- WP.org-compliant `readme.txt`.

#### Security
- Sodium `crypto_secretbox` for stored secrets (RapidAPI key, Geonames username).
- Nonce + capability checks on all REST mutation routes.
- Per-IP + per-user rate limiting.
- SVG sanitization allowlist.
- CAP mapping per-post ownership via `map_meta_cap` + `astrologer_api/capability_map` filter.
- Uninstall cleanup of CPT posts, taxonomy terms, caps, options, transients, user meta, cron.

#### Testing
- PHPUnit unit (Brain/Monkey) + integration (wp-env).
- Jest + @testing-library/react for admin and block components.
- Playwright e2e (7 scenarios) + axe-core accessibility suite.
- 8 API regression fixtures under `tests/fixtures/api/`.
- PHPStan level 8 clean. PHPCS WordPress-Extra clean. ESLint + Stylelint clean.

[1.0.0]: https://github.com/g-battaglia/astrologerwp/releases/tag/v1.0.0
