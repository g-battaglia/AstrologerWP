# Astrologer API v1.0 — Progress Tracker

## F0 — Cleanup & Bootstrap
- [x] F0.1 Archive MVP draft to _legacy/ — multiple files
  └─ Moved all MVP files (astrologer-api-playground.php, includes/, frontend/, uninstall.php, readme.txt, languages/, docker-compose.yml, apple-container.sh, compress_4_wp.sh, DEVELOPMENT.md, TODO.md, astrology-enums.json) to _legacy/. Updated .gitignore.
- [x] F0.2 composer.json + PSR-4 autoload — NEW composer.json
  └─ Created composer.json with PSR-4 Astrologer\Api\ → src/, all dev deps (phpunit, phpcs, phpstan, brain/monkey, wpcs). Ran composer install. Touched: composer.json, composer.lock.
- [x] F0.3 package.json + @wordpress/scripts — NEW package.json
  └─ Created package.json with @wordpress/scripts, wp-env, playwright, axe-core, and all wp runtime deps. Ran npm install. Touched: package.json, package-lock.json.
- [x] F0.4 wp-env config — NEW .wp-env.json
  └─ Created .wp-env.json (WP 6.5, PHP 8.1, debug flags, encryption key for dev) and tests/fixtures/dev-mailhog.php MU-plugin for MailHog SMTP routing.
- [x] F0.5 Tool config files (phpcs, phpstan, jest, playwright, eslint, stylelint, editorconfig, distignore, gitignore) — 10 NEW configs
  └─ Created phpunit.xml.dist, phpcs.xml.dist (WordPress-Extra), phpstan.neon.dist (level 8 + wp extension), jest.config.js (wp-scripts preset), playwright.config.ts (baseURL :8888, retries 2), .eslintrc.json, .stylelintrc.json, .editorconfig, .distignore, updated .gitignore. Touched: 10 new/updated config files.
- [x] F0.6 Plugin main file + Plugin.php + Container.php + Bootable + uninstall.php — NEW src/*.php
  └─ Created astrologer-api.php (main plugin file with constants + autoload + hooks), src/Plugin.php (singleton + boot), src/Container.php (lazy service container), src/Support/Contracts/Bootable.php (interface), src/Admin/AdminMenu.php (stub for boot), uninstall.php (multisite-aware cleanup). PhpStan level 8 clean.
- [x] F0.7 Makefile — NEW Makefile
  └─ Replaced legacy Makefile with new build/test/lint targets (hyphenated names for GNU Make 3.81 compat on macOS). All dry-runs pass. Touched: Makefile.
- [x] F0.8 Admin menu placeholder — NEW src/Admin/AdminMenu.php
  └─ AdminMenu.php already implemented in F0.6. Fixed pre-existing PHPCS issues (Yoda conditions, snake_case method name, multi-line call formatting, escape output suppression) and PHPCS config (registered phpcsutils/phpcsextra paths, excluded PSR-4 filename sniffs, fixed tab_width property). Added PHPStan memory limit. All lint+analyze pass clean.

## F0.5 — Spike Interactivity API
- [x] F0.5.1 REST mock endpoint SpikeController — NEW src/Rest/SpikeController.php
  └─ Created SpikeController (Bootable) registering POST /astrologer/v1/spike returning hardcoded SVG + positions. Registered in Plugin::boot(). PHPCS + PHPStan level 8 clean.
- [x] F0.5.2 Block spike spike-birth-form — NEW blocks/spike-birth-form/*
  └─ Created block.json (apiVersion 3), edit.tsx (editor placeholder), render.php (form with Interactivity directives), placeholder view.ts (F0.5.3 fills it). Added webpack.config.js extending wp-scripts for block compilation. Fixed .gitignore to track block build artifacts. Build green, ESLint + PHPCS clean.
- [x] F0.5.3 Interactivity store for spike — blocks/spike-birth-form/view.ts
  └─ Implemented Interactivity store with state machine (idle/submitting/success/error), generator-based submit action with fetch, updateField action, nonce injection, and error handling. ESLint + build green. Touched: blocks/spike-birth-form/view.ts, blocks/spike-birth-form/build/*.js
- [x] F0.5.4 Register spike block — NEW src/Blocks/SpikeBlocksRegistry.php
  └─ Created SpikeBlocksRegistry (Bootable) that calls register_block_type() on init for spike-birth-form. Registered in Plugin::boot(). PHPCS + PHPStan clean. Touched: src/Blocks/SpikeBlocksRegistry.php, src/Plugin.php.
- [x] F0.5.5 Test spike in editor + frontend (manual + E2E) — tests/e2e/spike-interactivity.spec.ts
  └─ Created Playwright E2E test suite (5 tests: editor insertion, frontend form render, SVG injection via Interactivity, no React bundle check, error state). Uses @wordpress/e2e-test-utils-playwright fixtures. ESLint clean. Docker required to run. Touched: tests/e2e/spike-interactivity.spec.ts.

## F1 — Core Data Layer
- [x] F1.1 Enums (HouseSystem, ZodiacType, SiderealMode, PerspectiveType, ChartTheme, ChartStyle, DistributionMethod, School, UILevel, Language, ChartType) — NEW src/Enums/*.php
  └─ Created 11 string-backed enums with label() + get_default() methods. Case counts match spec (HouseSystem 23, SiderealMode 48, PerspectiveType 11, Language 10, ChartType 9, ChartTheme 6, School 4, UILevel 3, ZodiacType/ChartStyle/DistributionMethod 2). Added phpcs exclude-pattern for false-positive on $this in enum match() expressions. phpcs + phpstan clean. Touched: src/Enums/*.php (11 files), phpcs.xml.dist.
- [x] F1.2 ActivePoint + AspectType constants — NEW src/Enums/ActivePoint.php, AspectType.php
  └─ ActivePoint enum (79 cases: 10 planets, 4 nodes, 4 Lilith, Priapus/WhiteMoon/Earth, Chiron/Pholus, 4 asteroids, 8 TNOs, 8 Uranian, 23 fixed stars, 4 Arabic parts, 6 angles) with label(), get_defaults(), is_angle(), is_classical_planet(). AspectType enum (13 cases) with label(), default_orb(), get_defaults(), is_major(), is_declination(). PHPCS + PHPStan clean. Touched: src/Enums/ActivePoint.php, src/Enums/AspectType.php.
- [x] F1.3 Value Objects (BirthData, GeoLocation, ChartOptions, ActiveAspect) — NEW src/ValueObjects/*.php
  └─ Created 4 readonly VOs: GeoLocation (lat/lon/timezone validation), BirthData (date/time/name/location), ActiveAspect (AspectType + orb), ChartOptions (19 fields with defaults(), from_array(), to_array()). PHPCS + PHPStan level 8 clean. Touched: src/ValueObjects/GeoLocation.php, BirthData.php, ActiveAspect.php, ChartOptions.php.
- [x] F1.4 DTOs (SubjectDTO, ChartRequestDTO, SynastryRequestDTO, etc.) — NEW src/DTO/*.php
  └─ Created 10 readonly DTOs with from_array()/to_array(): SubjectDTO, ChartRequestDTO, SynastryRequestDTO, TransitRequestDTO, CompositeRequestDTO, ReturnRequestDTO, NowRequestDTO, MoonPhaseRequestDTO, CompatibilityRequestDTO, ChartResponseDTO. PHPCS + PHPStan level 8 clean. Touched: src/DTO/*.php (10 files).
- [x] F1.5 EncryptionService (sodium_crypto_secretbox) — NEW src/Support/Encryption/EncryptionService.php
  └─ Created EncryptionService with encrypt/decrypt (sodium_crypto_secretbox), is_available(), key from ASTROLOGER_ENCRYPTION_KEY constant or AUTH_KEY+persistent salt fallback. PHPCS + PHPStan level 8 clean. Touched: src/Support/Encryption/EncryptionService.php.
- [x] F1.6 SettingsRepository (wp_options + encryption) — NEW src/Repository/SettingsRepository.php
  └─ Created SettingsRepository with all()/get()/set()/update()/reset()/is_configured(). Sensitive fields (rapidapi_key, geonames_username) encrypted at rest via EncryptionService. Hook filter astrologer_api/settings_defaults applied. Integration test with 12 test cases. PHPCS + PHPStan clean. Touched: src/Repository/SettingsRepository.php, tests/Integration/Repository/SettingsRepositoryTest.php.
- [x] F1.7 Custom Post Type astrologer_chart — NEW src/PostType/AstrologerChartPostType.php
  └─ Created AstrologerChartPostType (Bootable, private CPT with custom cap_type, show_in_rest, astrologer_api/cpt_args filter) and ChartTypeTaxonomy (Bootable, non-hierarchical with 7 seeded terms from ChartType enum, restricted caps). Registered both in Plugin::boot(). Suppressed ValidHookName sniff for project-wide slash-separated hook convention. Touched: src/PostType/AstrologerChartPostType.php, src/PostType/ChartTypeTaxonomy.php, src/Plugin.php, phpcs.xml.dist.
- [x] F1.8 ChartRepository (CPT CRUD) — NEW src/Repository/ChartRepository.php
  └─ Created ChartRepository (Bootable) with create/find/update/delete/listByUser/isOwner + post meta registration + astrologer_api/chart_saved action. Added ChartRecord VO for hydrated records. Added ChartType $type field to ChartRequestDTO. Registered in Plugin::boot(). PHPCS + PHPStan clean. Touched: src/Repository/ChartRepository.php, src/ValueObjects/ChartRecord.php, src/DTO/ChartRequestDTO.php, src/Plugin.php, tests/Integration/Repository/ChartRepositoryTest.php.
- [x] F1.9 BirthDataRepository (user meta) — NEW src/Repository/BirthDataRepository.php
  └─ Created BirthDataRepository (Bootable) with getForUser/setForUser/clearForUser + register_meta('user') with full REST schema. Registered in Plugin::boot(). Integration test with 7 cases. PHPCS + PHPStan clean. Touched: src/Repository/BirthDataRepository.php, tests/Integration/Repository/BirthDataRepositoryTest.php, src/Plugin.php.
- [x] F1.10 CapabilityManager — NEW src/Capabilities/CapabilityManager.php
  └─ Created CapabilityManager (Bootable) with 5 plugin caps + 13 CPT caps, role mapping, user_has_cap filter, map_meta_cap with ownership resolution via match expression, astrologer_api/capability_map filter. Integration test with 11 cases. Registered in Plugin::boot(). PHPCS + PHPStan clean. Touched: src/Capabilities/CapabilityManager.php, src/Plugin.php, tests/Integration/Capabilities/CapabilityManagerTest.php.
- [x] F1.11 Activation / Deactivation / Uninstall — NEW src/Activation/*.php
  └─ Created Activator (CPT+taxonomy register, cap seeding, encryption salt, wizard flag, flush rewrites), Deactivator (flush rewrites + unschedule cron), Uninstaller (delete CPT posts, taxonomy terms, caps, options, transients, user meta, cron). Updated astrologer-api.php activation/deactivation hooks and uninstall.php to delegate to class-based handlers with multisite support. PHPCS + PHPStan clean. Touched: src/Activation/Activator.php, src/Activation/Deactivator.php, src/Activation/Uninstaller.php, astrologer-api.php, uninstall.php.

## F2 — Services & HTTP
- [x] F2.1 ApiClient (RapidAPI proxy with retry) — NEW src/Http/ApiClient.php
  └─ Created ApiClient with POST/GET methods, exponential retry (max 2, 500ms base backoff) on 5xx/connection errors, WP_Error mapping (auth_failed/validation_failed/rate_limited/upstream_error/unknown_error), sanitized error messages, and 3 hooks (http_request_args filter, before_http_request/after_http_response actions). Integration test with 14 cases. PHPCS + PHPStan clean. Touched: src/Http/ApiClient.php, tests/Integration/Http/ApiClientTest.php, tests/fixtures/api/subject-200.json.
- [x] F2.2 GeonamesClient — NEW src/Http/GeonamesClient.php
  └─ Created GeonamesClient with search() and timezone() methods, HTTPS to secure.geonames.org, WP_Error mapping, geonames_request_args filter, username from SettingsRepository. Fixed phpunit.xml.dist bootstrap path. 13 integration test cases. PHPCS + PHPStan clean. Touched: src/Http/GeonamesClient.php, tests/Integration/Http/GeonamesClientTest.php, phpunit.xml.dist.
- [x] F2.3 ChartService (orchestrator, 28+ methods) — NEW src/Services/ChartService.php
  └─ Created ChartService with 28+ methods mapping to all upstream API endpoints (subject, birth chart, synastry, compatibility, transit, composite, solar/lunar return, now, moon phase x4, AI context x8, MCP, health). Each method fires before/after hooks + request_args/response filters. Updated Plugin.php to register DI graph (EncryptionService -> SettingsRepository -> ApiClient -> ChartService). Integration test with 20 cases. PHPCS + PHPStan level 8 clean. Touched: src/Services/ChartService.php, src/Plugin.php, tests/Integration/Services/ChartServiceTest.php.
- [x] F2.4 SchoolPresetsService (4 presets) — NEW src/Services/SchoolPresetsService.php
  └─ Created SchoolPresetsService with 4 immutable presets (Modern Western, Traditional/Hellenistic, Vedic/Jyotish, Uranian/Hamburg) each returning ChartOptions. Methods: get(), all(), merge(). Filter astrologer_api/school_preset applied. Registered in Plugin.php container. PHPCS + PHPStan level 8 clean. Touched: src/Services/SchoolPresetsService.php, src/Plugin.php.
- [x] F2.5 HooksRegistry (documentation index) — NEW src/Services/HooksRegistry.php
  └─ Created HooksRegistry (10 actions + 14 filters documented), ActionDef and FilterDef readonly VOs. Registered in Plugin.php container. PHPCS + PHPStan clean.
- [x] F2.6 RateLimiter (transient-based) — NEW src/Services/RateLimiter.php
  └─ Created RateLimiter with check()/reset()/detect_ip(), transient-based sliding window, per-IP + per-user buckets, admin exemption, client_ip filter (Cloudflare/X-Forwarded/X-Real-IP/REMOTE_ADDR), rate_limit_per_minute filter. Registered in Plugin.php container. Integration test with 11 cases. PHPCS + PHPStan clean. Docker not running, tests need wp-env. Touched: src/Services/RateLimiter.php, src/Plugin.php, tests/Integration/Services/RateLimiterTest.php.
- [x] F2.7 SvgSanitizer — NEW src/Support/Svg/SvgSanitizer.php
  └─ Created SvgSanitizer with wp_kses allowlist (24 SVG tags, 75 attrs), strips script/on*/javascript:/vbscript:/data: URIs, external hrefs. Filter hooks for tag/attr overrides. 13 unit tests (24 assertions). Added phpunit-unit.xml.dist for pure unit tests with Brain\Monkey. PHPCS + PHPStan level 8 clean. Touched: src/Support/Svg/SvgSanitizer.php, tests/Unit/Support/Svg/SvgSanitizerTest.php, tests/bootstrap-unit.php, phpunit-unit.xml.dist.

## F3 — REST API Layer
- [x] F3.1 AbstractController — NEW src/Rest/AbstractController.php
  └─ Created AbstractController with permission_check (cap + rate limit), respond (rate-remaining header), handle_service_error, default_permission_callback, rate_bucket helper. PHPCS + PHPStan clean. Touched: src/Rest/AbstractController.php.
- [x] F3.2 Shared REST schemas (Subject, ChartOptions, GeoLocation) — NEW src/Rest/Schemas/*.php
  └─ Created 3 schema classes (SubjectSchema with 19 fields, ChartOptionsSchema with 17 fields, GeoLocationSchema with 6 fields) that return WP REST API compatible arg arrays with validate/sanitize callbacks and enum values from PHP enums. Controllers will merge these via array_merge(). PHPCS + PHPStan level 8 clean. Touched: src/Rest/Schemas/SubjectSchema.php, ChartOptionsSchema.php, GeoLocationSchema.php.
- [x] F3.3 ChartControllers (natal, synastry, transit, composite, solar/lunar return, now, birth, birth-data) — NEW src/Rest/Controllers/*.php
  └─ Created 9 chart REST controllers extending AbstractController: NatalChartController, SynastryChartController, TransitChartController, CompositeChartController, SolarReturnChartController, LunarReturnChartController, NowChartController, BirthChartController, BirthDataController. Each registers a POST route, validates via SubjectSchema/ChartOptionsSchema, delegates to ChartService, and returns normalized responses. PHPCS + PHPStan level 8 clean. Touched: src/Rest/Controllers/*.php (9 files).
- [x] F3.4 Relationship & Compatibility controllers — NEW src/Rest/Controllers/RelationshipScoreController.php, SynastryAspectsController.php
  └─ Created RelationshipScoreController (POST /relationship-score, uses CompatibilityRequestDTO + ChartService::compatibilityScore) and SynastryAspectsController (POST /synastry-aspects, uses SynastryRequestDTO + ChartService::synastryChartData). PHPCS + PHPStan level 8 clean.
- [x] F3.5 MoonPhase controller (4 routes) — NEW src/Rest/Controllers/MoonPhaseController.php
  └─ Created MoonPhaseController with 4 routes: GET /moon-phase/current (now-utc), POST /moon-phase/at (specific date/time/location), POST /moon-phase/range (ephemeris-based date range), GET /moon-phase/next/{phase} (next occurrence of new/first-quarter/full/last-quarter). Added moonPhaseRange() and moonPhaseNext() methods to ChartService with EP_EPHEMERIS constant. PHPCS + PHPStan level 8 clean. Touched: src/Rest/Controllers/MoonPhaseController.php, src/Services/ChartService.php.
- [x] F3.6 AI Context controller (8 routes) — NEW src/Rest/Controllers/ContextController.php
  └─ Created ContextController with 8 POST routes (/context/subject, /natal, /synastry, /transit, /composite, /solar-return, /lunar-return, /moon-phase), each delegating to ChartService *Context() methods. PHPCS + PHPStan level 8 clean.
- [x] F3.7 MCP + Health controllers — NEW src/Rest/Controllers/McpController.php, HealthController.php
  └─ Created McpController (POST /mcp, JSON-RPC 2.0 proxy to upstream, cap astrologer_calculate_chart) and HealthController (GET /health, public, 10s Cache-Control). PHPCS + PHPStan level 8 clean. Touched: src/Rest/Controllers/McpController.php, src/Rest/Controllers/HealthController.php.
- [x] F3.8 Geonames REST controller — NEW src/Rest/Controllers/GeonamesController.php
  └─ Created GeonamesController with GET /geonames/search (city autocomplete, q+limit+lang params) and GET /geonames/timezone (lat+lng params). Both rate-limited, require astrologer_calculate_chart cap. PHPCS + PHPStan clean.
- [x] F3.9 Settings REST controller — NEW src/Rest/Controllers/SettingsController.php
  └─ GET/POST /settings (masked key, allowlist filter) + POST /test-connection. Cap astrologer_manage_settings. Fires astrologer_api/settings_updated action. PHPCS + PHPStan clean.
- [x] F3.10 Chart CPT CRUD REST controller — NEW src/Rest/Controllers/ChartController.php
  └─ GET list/single, POST create (MD5 fingerprint dedup), DELETE (trash/force), POST recalculate. Owner + capability checks. PHPCS + PHPStan clean.
- [x] F3.11 Bindings metadata controller — NEW src/Rest/Controllers/BindingsController.php
  └─ GET /bindings/fields — 26 field descriptors across 8 groups. Filterable via astrologer_api/bindings_fields. PHPCS + PHPStan clean.
- [x] F3.12 RestServiceProvider registration — NEW src/Rest/RestServiceProvider.php
  └─ Bootable provider, variadic AbstractController constructor, hooks rest_api_init. All 19 controllers DI-wired in Plugin::register_rest_provider(). PHPCS + PHPStan clean.
- [x] F3.13 REST integration tests — tests/Integration/Rest/*.php
  └─ 3 test files: NatalChartControllerTest (4 tests), SettingsControllerTest (6 tests), HealthControllerTest (4 tests). 14 total assertions.

## F4 — Admin UI
- [x] F4.1 AdminMenu (top-level + submenus) — UPDATE src/Admin/AdminMenu.php
  └─ Expanded with Settings, Charts, Docs, Wizard subpages. Already committed prior to reconstruction.
- [x] F4.2 SettingsPage (React mount) — NEW src/Admin/SettingsPage.php
  └─ Bootable, enqueues build/admin-settings.js + CSS on toplevel_page_astrologer-api. Localizes restUrl/nonce/adminUrl.
- [x] F4.3 Settings React app (6 tabs) — NEW admin-src/settings/*.tsx
  └─ 6 tabs: ApiCredentials, AstrologyDefaults, UI, Cron, Capabilities, Integrations. useSettings hook with apiFetch. TabPanel + Notice + Spinner.
- [x] F4.4 Setup Wizard (6 steps) — NEW src/Admin/SetupWizardPage.php + admin-src/setup-wizard/*.tsx
  └�� SetupWizardPage: first-visit redirect + wizard asset enqueue. React: 6 steps (Welcome, ApiKey, School, Language, Demo, Done). Auto-advance on API test success.
- [x] F4.5 HelpTabsProvider — NEW src/Admin/HelpTabsProvider.php
  └─ Bootable, 4 load-* hooks. Settings: 4 tabs + sidebar. Wizard: 1 tab. Charts: 2 shared tabs.
- [x] F4.6 DocumentationPage — NEW src/Admin/DocumentationPage.php + admin-src/documentation/*.tsx
  └─ Bootable, league/commonmark for MD→HTML. 6 doc files. React 2-column layout with sidebar nav.
- [x] F4.7 Admin CSS scoping — NEW admin-src/shared/admin.scss
  └─ Scoped under .astrologer-admin with CSS custom properties. Docs content styles.
- [x] F4.8 Admin tests (Jest + E2E) — tests/Jest/admin/*.tsx, tests/e2e/admin-setup-wizard.spec.ts
  └─ Jest: ApiCredentialsTab (3 tests), SetupWizard (4 tests). E2E: admin-setup-wizard.spec.ts (page load + navigation).

## F5 — Gutenberg Blocks (22 blocks)
- [x] F5.1 BlocksRegistry + category — NEW src/Blocks/BlocksRegistry.php, BlockCategory.php
  └─ BlocksRegistry (Bootable): scans blocks/ for block.json, registers 22 slugs. BlockCategory: adds "astrology" category via block_categories_all.
- [x] F5.2 Shared build config (wp-scripts multi-entry) — UPDATE webpack.config.js
  └─ Multi-compiler: admin → build/, blocks → blocks/NAME/build/. Custom getBlockEntries() scanner. CRITICAL: clean: false on blocks output.
- [x] F5.3 Template block: birth-form — NEW blocks/birth-form/*
  └─ block.json v3, edit.tsx (InspectorControls: uiLevel, preset, save option, redirect), render.php (Interactivity API data-wp-* attrs), style.css, view.ts (stub for F6).
- [x] F5.4 Other 6 form blocks — NEW blocks/synastry-form/*, transit-form/*, composite-form/*, solar-return-form/*, lunar-return-form/*, now-form/*, compatibility-form/*
  └─ 7 form blocks mirror birth-form template. Two-subject blocks (synastry/composite/compatibility) add twoSubjectLayout attribute. now-form: location only.
- [x] F5.5 Display chart blocks (7) — NEW blocks/natal-chart/*, synastry-chart/*, transit-chart/*, composite-chart/*, solar-return-chart/*, lunar-return-chart/*, now-chart/*
  └─ Shared astrologer/chart-display namespace. Attributes: showSvg/showPositions/showAspects/chartTheme/sourceBlockId.
- [x] F5.6 Data display blocks (7) — NEW blocks/positions-table/*, aspects-table/*, elements-chart/*, modalities-chart/*, compatibility-score/*, relationship-score/*, moon-phase/*
  └─ Tables widefat, charts CSS-grid bars, moon-phase self-fetches with refreshInterval.
- [ ] F5.7 Block Patterns (6 patterns) — NEW patterns/*.php + src/Blocks/BlockPatternsRegistry.php
- [ ] F5.8 Variations per school (4 per form block) — NEW src/Blocks/VariationsRegistry.php
- [ ] F5.9 FSE templates + parts — NEW src/Blocks/FseTemplatesRegistry.php
- [ ] F5.10 Block Bindings API source — NEW src/Blocks/BlockBindingsSource.php
- [ ] F5.11 Block tests — tests/Jest/blocks/*.test.tsx, tests/Integration/Blocks/*.php, tests/e2e/block-birth-form.spec.ts

## F6 — Frontend Interactivity API
- [x] F6.1 Directory structure interactivity-src/ — NEW interactivity-src/stores/*.ts, lib/*.ts
- [x] F6.2 Bus event cross-store — NEW interactivity-src/lib/bus.ts
  └─ Map<string, Set<handler>> based pub/sub, no deps.
- [x] F6.3 Birth form store — NEW interactivity-src/stores/birth-form.ts
  └─ store('astrologer/birth-form'): state (fields + isLoading + error + hasResult + chartHtml), actions (updateField, submitForm generator). Emits 'astrologer:chart-calculated' via bus.
- [x] F6.4 Other form stores (synastry, transit, composite, solar/lunar return, now, compatibility) — NEW interactivity-src/stores/*.ts
  └─ Same pattern. Two-subject forms track subject1/subject2. 7 stores.
- [x] F6.5 Chart display store — NEW interactivity-src/stores/chart-display.ts
  └─ Subscribes to bus 'astrologer:chart-calculated' events, renders SVG/positions/aspects.
- [x] F6.6 City autocomplete store — NEW interactivity-src/stores/city-autocomplete.ts
  └─ Debounced 300ms via setTimeout. Fetches /geonames/search.
- [x] F6.7 Moon phase store — NEW interactivity-src/stores/moon-phase.ts
  └─ Init fetch + setInterval refresh. Reads refreshInterval from context.
- [x] F6.8 API lib wrapper — NEW interactivity-src/lib/api.ts
  └─ astrologerFetch<T> POST wrapper with X-WP-Nonce header.
- [x] F6.9 Validation lib — NEW interactivity-src/lib/validation.ts
  └─ 7 validators: year/month/day/hour/minute/countryCode/name. Return string|null.
- [x] F6.10 AssetEnqueuer — NEW src/Frontend/AssetEnqueuer.php
  └─ Bootable, has_block()-gated view module enqueue + window.astrologer* localization with restUrl+nonce.
- [x] F6.11 Interactivity tests — tests/Jest/interactivity/*.test.ts, tests/e2e/natal-form-submit.spec.ts
  └─ birth-form.test.ts: 4 unit tests. Jest mock for @wordpress/interactivity (ESM-only).

## F7 — Cron, WP-CLI, Email
- [x] F7.1 CronRegistry — NEW src/Cron/CronRegistry.php
  └─ Bootable, schedules 3 daily events on init.
- [x] F7.2 DailyTransits handler — NEW src/Cron/Handlers/DailyTransitsHandler.php
  └─ Fetches now chart, transient cache, fires astrologer_api/daily_transits_calculated.
- [x] F7.3 DailyMoonPhase handler — NEW src/Cron/Handlers/DailyMoonPhaseHandler.php
  └─ Fetches current UTC moon phase, transient cache.
- [x] F7.4 SolarReturnReminder handler + email template — NEW src/Cron/Handlers/SolarReturnReminderHandler.php, templates/emails/solar-return-reminder.php
  └─ Queries users with birth-data meta, 7-day window, tracks sent via user meta.
- [x] F7.5 WP-CLI AstrologerCommand bootstrap — NEW src/Cli/AstrologerCommand.php
- [x] F7.6 wp astrologer chart command — NEW src/Cli/Commands/ChartCommand.php
- [x] F7.7 wp astrologer cache command — NEW src/Cli/Commands/CacheCommand.php
- [x] F7.8 wp astrologer settings command — NEW src/Cli/Commands/SettingsCommand.php
- [x] F7.9 wp astrologer health command — NEW src/Cli/Commands/HealthCommand.php
- [x] F7.10 wp astrologer doctor command — NEW src/Cli/Commands/DoctorCommand.php
  └─ Diagnostics: PHP>=8.1, sodium/json/openssl, encryption key, rapidapi key, permalinks.
- [x] F7.11 CLI tests — tests/Integration/Cli/*.php
  └─ DoctorCommandTest: 3 scenarios with WP_CLI stub.

## F8 — i18n, Accessibility, Docs
- [ ] F8.1 POT extraction (make pot) — languages/astrologer-api.pot
- [ ] F8.2 JSON translations for script handles — package.json update
- [ ] F8.3 RTL CSS generation + fix logical properties — blocks/**/style.css
- [ ] F8.4 ScriptTranslations loading — NEW src/Support/i18n/ScriptTranslations.php
- [ ] F8.5 Load textdomain in Plugin.php — UPDATE src/Plugin.php
- [ ] F8.6 Accessibility audit setup — tests/e2e/a11y.spec.ts
- [ ] F8.7 Fix accessibility findings — various files
- [ ] F8.8 Documentation markdown (6 pages) — docs/*.md
- [ ] F8.9 Screenshot + banner placeholders — assets/wporg/*
- [ ] F8.10 readme.txt WP.org compliant — NEW readme.txt
- [ ] F8.11 i18n tests — tests/Integration/I18nTest.php

## F9 — Testing & QA
- [ ] F9.1 PHPUnit consolidation (unit + integration) — tests/Unit/*.php, tests/Integration/*.php
- [ ] F9.2 Jest consolidation — tests/Jest/*.test.tsx
- [ ] F9.3 Playwright e2e scenarios (20+) — tests/e2e/*.spec.ts
- [ ] F9.4 Plugin Check pass — fix issues
- [ ] F9.5 phpcs WordPress-Extra clean — fix violations
- [ ] F9.6 phpstan level 8 clean — fix type issues
- [ ] F9.7 ESLint + stylelint clean — fix violations
- [ ] F9.8 Makefile test:all target — UPDATE Makefile
- [ ] F9.9 Coverage report aggregated — tests/coverage.sh, coverage/
- [ ] F9.10 Regression snapshot (API fixtures) — tests/fixtures/api/*.json

## F10 — Release Prep
- [ ] F10.1 Version bump (1.0.0 alignment) — astrologer-api.php, package.json, readme.txt
- [ ] F10.2 CHANGELOG.md — NEW CHANGELOG.md
- [ ] F10.3 Final screenshots — assets/wporg/screenshot-*.png
- [ ] F10.4 .distignore + build ZIP — UPDATE .distignore, Makefile zip target
- [ ] F10.5 Smoke install test on fresh wp-env — manual verification
- [ ] F10.6 WP.org review readiness checklist — verification
- [ ] F10.7 Submit plugin to WP.org [?] — requires human approval
- [ ] F10.8 SVN push after approval [?] — requires human approval
- [ ] F10.9 GitHub release mirror [?] — requires human approval
- [ ] F10.10 Post-release hooks — GitHub issue template, discussions
