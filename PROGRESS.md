# Astrologer API v1.0 — Progress Tracker

## F0 — Cleanup & Bootstrap
- [x] F0.1 Archive MVP draft to _legacy/ — multiple files
  └─ Moved all MVP files (astrologer-api-playground.php, includes/, frontend/, uninstall.php, readme.txt, languages/, docker-compose.yml, apple-container.sh, compress_4_wp.sh, DEVELOPMENT.md, TODO.md, astrology-enums.json) to _legacy/. Updated .gitignore.
- [x] F0.2 composer.json + PSR-4 autoload — NEW composer.json
  └─ Created composer.json with PSR-4 Astrologer\Api\ → src/, all dev deps (phpunit, phpcs, phpstan, brain/monkey, wpcs). Ran composer install. Touched: composer.json, composer.lock.
- [ ] F0.3 package.json + @wordpress/scripts — NEW package.json
- [ ] F0.4 wp-env config — NEW .wp-env.json
- [ ] F0.5 Tool config files (phpcs, phpstan, jest, playwright, eslint, stylelint, editorconfig, distignore, gitignore) — 10 NEW configs
- [ ] F0.6 Plugin main file + Plugin.php + Container.php + Bootable + uninstall.php — NEW src/*.php
- [ ] F0.7 Makefile — NEW Makefile
- [ ] F0.8 Admin menu placeholder — NEW src/Admin/AdminMenu.php

## F0.5 — Spike Interactivity API
- [ ] F0.5.1 REST mock endpoint SpikeController — NEW src/Rest/SpikeController.php
- [ ] F0.5.2 Block spike spike-birth-form — NEW blocks/spike-birth-form/*
- [ ] F0.5.3 Interactivity store for spike — blocks/spike-birth-form/view.ts
- [ ] F0.5.4 Register spike block — NEW src/Blocks/SpikeBlocksRegistry.php
- [ ] F0.5.5 Test spike in editor + frontend (manual + E2E) — tests/e2e/spike-interactivity.spec.ts

## F1 — Core Data Layer
- [ ] F1.1 Enums (HouseSystem, ZodiacType, SiderealMode, PerspectiveType, ChartTheme, ChartStyle, DistributionMethod, School, UILevel, Language, ChartType) — NEW src/Enums/*.php
- [ ] F1.2 ActivePoint + AspectType constants — NEW src/Enums/ActivePoint.php, AspectType.php
- [ ] F1.3 Value Objects (BirthData, GeoLocation, ChartOptions, ActiveAspect) — NEW src/ValueObjects/*.php
- [ ] F1.4 DTOs (SubjectDTO, ChartRequestDTO, SynastryRequestDTO, etc.) — NEW src/DTO/*.php
- [ ] F1.5 EncryptionService (sodium_crypto_secretbox) — NEW src/Support/Encryption/EncryptionService.php
- [ ] F1.6 SettingsRepository (wp_options + encryption) — NEW src/Repository/SettingsRepository.php
- [ ] F1.7 Custom Post Type astrologer_chart — NEW src/PostType/AstrologerChartPostType.php
- [ ] F1.8 ChartRepository (CPT CRUD) — NEW src/Repository/ChartRepository.php
- [ ] F1.9 BirthDataRepository (user meta) — NEW src/Repository/BirthDataRepository.php
- [ ] F1.10 CapabilityManager — NEW src/Capabilities/CapabilityManager.php
- [ ] F1.11 Activation / Deactivation / Uninstall — NEW src/Activation/*.php

## F2 — Services & HTTP
- [ ] F2.1 ApiClient (RapidAPI proxy with retry) — NEW src/Http/ApiClient.php
- [ ] F2.2 GeonamesClient — NEW src/Http/GeonamesClient.php
- [ ] F2.3 ChartService (orchestrator, 28+ methods) — NEW src/Services/ChartService.php
- [ ] F2.4 SchoolPresetsService (4 presets) — NEW src/Services/SchoolPresetsService.php
- [ ] F2.5 HooksRegistry (documentation index) — NEW src/Services/HooksRegistry.php
- [ ] F2.6 RateLimiter (transient-based) — NEW src/Services/RateLimiter.php
- [ ] F2.7 SvgSanitizer — NEW src/Support/Svg/SvgSanitizer.php

## F3 — REST API Layer
- [ ] F3.1 AbstractController — NEW src/Rest/AbstractController.php
- [ ] F3.2 Shared REST schemas (Subject, ChartOptions, GeoLocation) — NEW src/Rest/Schemas/*.php
- [ ] F3.3 ChartControllers (natal, synastry, transit, composite, solar/lunar return, now, birth, birth-data) — NEW src/Rest/Controllers/*.php
- [ ] F3.4 Relationship & Compatibility controllers — NEW src/Rest/Controllers/RelationshipScoreController.php, SynastryAspectsController.php
- [ ] F3.5 MoonPhase controller (4 routes) — NEW src/Rest/Controllers/MoonPhaseController.php
- [ ] F3.6 AI Context controller (8 routes) — NEW src/Rest/Controllers/ContextController.php
- [ ] F3.7 MCP + Health controllers — NEW src/Rest/Controllers/McpController.php, HealthController.php
- [ ] F3.8 Geonames REST controller — NEW src/Rest/Controllers/GeonamesController.php
- [ ] F3.9 Settings REST controller — NEW src/Rest/Controllers/SettingsController.php
- [ ] F3.10 Chart CPT CRUD REST controller — NEW src/Rest/Controllers/ChartController.php
- [ ] F3.11 Bindings metadata controller — NEW src/Rest/Controllers/BindingsController.php
- [ ] F3.12 RestServiceProvider registration — NEW src/Rest/RestServiceProvider.php
- [ ] F3.13 REST integration tests — tests/Integration/Rest/*.php

## F4 — Admin UI
- [ ] F4.1 AdminMenu (top-level + submenus) — UPDATE src/Admin/AdminMenu.php
- [ ] F4.2 SettingsPage (React mount) — NEW src/Admin/SettingsPage.php
- [ ] F4.3 Settings React app (6 tabs) — NEW admin-src/settings/*.tsx
- [ ] F4.4 Setup Wizard (6 steps) — NEW src/Admin/SetupWizardPage.php + admin-src/setup-wizard/*.tsx
- [ ] F4.5 HelpTabsProvider — NEW src/Admin/HelpTabsProvider.php
- [ ] F4.6 DocumentationPage — NEW src/Admin/DocumentationPage.php + admin-src/documentation/*.tsx
- [ ] F4.7 Admin CSS scoping — NEW admin-src/shared/admin.scss
- [ ] F4.8 Admin tests (Jest + E2E) — tests/Jest/admin/*.tsx, tests/e2e/admin-setup-wizard.spec.ts

## F5 — Gutenberg Blocks (22 blocks)
- [ ] F5.1 BlocksRegistry + category — NEW src/Blocks/BlocksRegistry.php, BlockCategory.php
- [ ] F5.2 Shared build config (wp-scripts multi-entry) — UPDATE package.json
- [ ] F5.3 Template block: birth-form — NEW blocks/birth-form/*
- [ ] F5.4 Other 6 form blocks — NEW blocks/synastry-form/*, transit-form/*, composite-form/*, solar-return-form/*, lunar-return-form/*, now-form/*, compatibility-form/*
- [ ] F5.5 Display chart blocks (7) — NEW blocks/natal-chart/*, synastry-chart/*, transit-chart/*, composite-chart/*, solar-return-chart/*, lunar-return-chart/*, now-chart/*
- [ ] F5.6 Data display blocks (7) — NEW blocks/positions-table/*, aspects-table/*, elements-chart/*, modalities-chart/*, compatibility-score/*, relationship-score/*, moon-phase/*
- [ ] F5.7 Block Patterns (6 patterns) — NEW patterns/*.php + src/Blocks/BlockPatternsRegistry.php
- [ ] F5.8 Variations per school (4 per form block) — NEW src/Blocks/VariationsRegistry.php
- [ ] F5.9 FSE templates + parts — NEW src/Blocks/FseTemplatesRegistry.php
- [ ] F5.10 Block Bindings API source — NEW src/Blocks/BlockBindingsSource.php
- [ ] F5.11 Block tests — tests/Jest/blocks/*.test.tsx, tests/Integration/Blocks/*.php, tests/e2e/block-birth-form.spec.ts

## F6 — Frontend Interactivity API
- [ ] F6.1 Directory structure interactivity-src/ — NEW interactivity-src/stores/*.ts, lib/*.ts
- [ ] F6.2 Bus event cross-store — NEW interactivity-src/lib/bus.ts
- [ ] F6.3 Birth form store — NEW interactivity-src/stores/birth-form.ts
- [ ] F6.4 Other form stores (synastry, transit, composite, solar/lunar return, now, compatibility) — NEW interactivity-src/stores/*.ts
- [ ] F6.5 Chart display store — NEW interactivity-src/stores/chart-display.ts
- [ ] F6.6 City autocomplete store — NEW interactivity-src/stores/city-autocomplete.ts
- [ ] F6.7 Moon phase store — NEW interactivity-src/stores/moon-phase.ts
- [ ] F6.8 API lib wrapper — NEW interactivity-src/lib/api.ts
- [ ] F6.9 Validation lib — NEW interactivity-src/lib/validation.ts
- [ ] F6.10 AssetEnqueuer — NEW src/Frontend/AssetEnqueuer.php
- [ ] F6.11 Interactivity tests — tests/Jest/interactivity/*.test.ts, tests/e2e/natal-form-submit.spec.ts

## F7 — Cron, WP-CLI, Email
- [ ] F7.1 CronRegistry — NEW src/Cron/CronRegistry.php
- [ ] F7.2 DailyTransits handler — NEW src/Cron/Handlers/DailyTransitsHandler.php
- [ ] F7.3 DailyMoonPhase handler — NEW src/Cron/Handlers/DailyMoonPhaseHandler.php
- [ ] F7.4 SolarReturnReminder handler + email template — NEW src/Cron/Handlers/SolarReturnReminderHandler.php, templates/emails/solar-return-reminder.php
- [ ] F7.5 WP-CLI AstrologerCommand bootstrap — NEW src/Cli/AstrologerCommand.php
- [ ] F7.6 wp astrologer chart command — NEW src/Cli/Commands/ChartCommand.php
- [ ] F7.7 wp astrologer cache command — NEW src/Cli/Commands/CacheCommand.php
- [ ] F7.8 wp astrologer settings command — NEW src/Cli/Commands/SettingsCommand.php
- [ ] F7.9 wp astrologer health command — NEW src/Cli/Commands/HealthCommand.php
- [ ] F7.10 wp astrologer doctor command — NEW src/Cli/Commands/DoctorCommand.php
- [ ] F7.11 CLI tests — tests/Integration/Cli/*.php

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
