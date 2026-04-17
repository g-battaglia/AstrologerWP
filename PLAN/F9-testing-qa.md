# F9 — Testing & QA consolidation

> **Theme:** Completare la suite di test (unit, integration, e2e, a11y), raggiungere copertura minima, passare Plugin Check senza warning.
> **Effort:** L (5-7 giorni)
> **Dipendenze:** tutte (F0-F8)

---

## Obiettivo

- **PHPUnit**: coverage ≥ 70% su `src/`, 100% su Enums/VOs/DTO/Encryption/Repository.
- **Jest**: coverage ≥ 60% su admin React + interactivity stores.
- **Playwright**: 20+ scenari e2e principali.
- **Plugin Check**: 0 errors, 0 warnings nella WP.org Plugin Check tool.
- **phpcs**: 0 violations WordPress-Extra + custom rules.
- **phpstan**: level 8 clean.
- **ESLint + stylelint**: clean.

---

## Prerequisiti

- Fasi precedenti hanno scritto test unit/integration locali. F9 li consolida, riempie i buchi, aggiunge e2e scenari principali, esegue Plugin Check.

---

## Tasks

### F9.1 — PHPUnit consolidation

**Directory:** `tests/Unit/`

Copertura:
- `Enums/*Test.php` — valida cases(), tryFrom(), metodi statici.
- `ValueObjects/*Test.php` — costruzione, immutability, validation.
- `DTO/*Test.php` — toArray() round-trip.
- `Support/Encryption/EncryptionServiceTest.php` — encrypt/decrypt, tampering detection, key rotation.
- `Services/SchoolPresetsServiceTest.php` — 4 preset correctness.
- `Services/RateLimiterTest.php` — limit enforcement, windowing.
- `Support/Svg/SvgSanitizerTest.php` — allowlist behavior, XSS vector blocked.

**Directory:** `tests/Integration/`

Con `wp-phpunit` (load WP core):
- `Http/ApiClientTest.php` — mock `wp_remote_post`, verifica retry, headers, error mapping.
- `Http/GeonamesClientTest.php` — stesso pattern.
- `Repository/ChartRepositoryTest.php` — CRUD, caps, hash dedup.
- `Repository/SettingsRepositoryTest.php` — read/write, encryption of API key, migration.
- `Repository/BirthDataRepositoryTest.php` — user meta.
- `Rest/*Test.php` — tutti 15 controller: auth, schema, happy path, rate limit, error mapping.
- `Cron/*Test.php` — 3 handlers idempotent.
- `Cli/*Test.php` — 5 commands smoke.
- `Capabilities/CapabilityManagerTest.php` — grant/revoke, filter override.

### F9.2 — Jest consolidation

**Directory:** `tests/Jest/`

Tests per:
- `admin/ApiCredentialsTab.test.tsx` — render, submit, test connection button.
- `admin/AstrologyDefaultsTab.test.tsx` — school change triggers modal + update.
- `admin/SetupWizard.test.tsx` — 6 step navigation, validation errors block advance.
- `blocks/birth-form.edit.test.tsx` — InspectorControls interaction.
- `blocks/natal-chart.edit.test.tsx` — source change updates preview.
- `interactivity/birth-form-store.test.ts` — state machine transitions, mocked apiFetch.
- `interactivity/city-autocomplete.test.ts` — debounce, cache, selection.
- `interactivity/chart-display.test.ts` — bus listener, SVG injection.
- `interactivity/validation.test.ts` — edge cases validate functions.

Coverage report: `npx jest --coverage` generato in `coverage/jest/`.

### F9.3 — Playwright e2e scenari

**Directory:** `tests/e2e/`

Setup: `@wordpress/e2e-test-utils-playwright` per admin login, block editor helpers.

Scenari (20+):

**Setup & admin:**
1. `setup-wizard-first-run.spec.ts` — fresh install → wizard auto-redirect → completo → settings persistiti.
2. `settings-api-credentials.spec.ts` — salva API key, reload, key offuscata, test connection ok.
3. `settings-school-preset-change.spec.ts` — cambio preset → confirmation modal → defaults cambiati.
4. `settings-ui-level-toggle.spec.ts`.
5. `settings-cron-toggle.spec.ts`.
6. `documentation-page.spec.ts`.

**Editor:**
7. `block-category-visible.spec.ts` — cat Astrology appare.
8. `block-insert-birth-form.spec.ts`.
9. `block-inspector-ui-level.spec.ts` — cambio uiLevel riflette in preview.
10. `block-pattern-insert.spec.ts` — pattern "Birth Form + Chart" inserito.
11. `block-variations-school.spec.ts` — 4 variations visible in inserter.

**Frontend:**
12. `natal-form-submit.spec.ts` — form submit → SVG appare → no React chunk in network.
13. `synastry-form-submit.spec.ts`.
14. `city-autocomplete.spec.ts`.
15. `moon-phase-widget.spec.ts`.
16. `rate-limit-hit.spec.ts` — 61+ submits → 429 shown.
17. `chart-cpt-single.spec.ts` — visualizza single CPT con block bindings.
18. `save-chart-flow.spec.ts` — form con "save" checkbox → CPT creato.

**A11y:**
19. `a11y.spec.ts` — già fatto in F8.

**WP-CLI:**
20. `cli-health-doctor.spec.ts` — esegui via `@wordpress/env/cli` wrapper.

**i18n:**
21. `i18n-rtl.spec.ts` — setta he_IL, verifica mirror.

### F9.4 — Plugin Check

Installa `plugin-check` di Google/WP:
```bash
wp plugin install plugin-check --activate
wp plugin check astrologer-api --format=json > plugin-check-report.json
```

Fix tutti gli errors + warnings. Tipici:
- Missing `readme.txt` fields.
- Plugin header mancante di `Tested up to`.
- Direct DB query sostituibile con `$wpdb->prepare`.
- Unescaped output → aggiungi `esc_html` / `esc_attr`.
- Missing text domain su `__()`.
- `_deprecated_function` non catturato.
- Readme.txt stable tag mismatch.

### F9.5 — phpcs (WordPress-Extra)

**File:** `phpcs.xml.dist` (già in F0)

```bash
composer run lint:php
```

Fix ogni violation. Eventuali esclusioni giustificate in commento in `phpcs.xml.dist`.

### F9.6 — phpstan level 8

```bash
composer run analyze
```

Level 8 è strict. Tipici fix:
- Aggiungere type hints mancanti.
- `array-shape` annotations per array strutturati (DTOs).
- `@phpstan-ignore-line` solo dove WordPress API costringe (es. globals `$wpdb`).

### F9.7 — ESLint + stylelint

```bash
npm run lint:js
npm run lint:css
```

Fix ogni warn. `@wordpress/eslint-plugin` config.

### F9.8 — Makefile target test:all

```make
test:all:
	composer run lint:php
	composer run analyze
	composer run test
	npm run lint:js
	npm run lint:css
	npm run test:jest
	npm run test:e2e
	npm run test:a11y
```

Un solo comando che risponde al "is everything green?".

### F9.9 — Coverage report aggregato

**File:** `tests/coverage.sh`

Script che:
1. Esegue PHPUnit con `--coverage-html coverage/php/` + `--coverage-clover clover.xml`.
2. Esegue Jest con `--coverage --coverageDirectory=coverage/jest/`.
3. Genera `coverage/index.html` che combina i report in un'unica vista.

Aggiunto al Makefile: `make coverage`.

### F9.10 — Regression snapshot

**File:** `tests/fixtures/api/` (già in F2)

Contiene response reali di ciascun endpoint upstream come JSON. Test integration li usa per mock + serve da snapshot: se upstream cambia formato, il test cattura il breaking change.

**Comando refresh:** `npm run fixtures:refresh` (chiama upstream con API key dev e salva response).

---

## Criterio di demoable

1. `make test:all` → exit 0, tutti i runner verdi.
2. `make coverage` → report aperto in browser mostra PHPUnit ≥70%, Jest ≥60%.
3. `wp plugin check astrologer-api` → "No issues found".
4. Eventuale snapshot diff su upstream catturato con test chiaro e bloccante.

---

## Checkpoint #6 (review readme.txt + plugin check)

Prima del submit F10:
- Terzo occhio legge `readme.txt`.
- Verifica Plugin Check report (`plugin-check-report.json`) con terza lettura.
- Lista conscia di eventuali warning non risolvibili documentata in commit message.

---

## Rischi identificati

- **WP-env flakiness** in e2e: Playwright test a volte timeout per DB slow. Mitigazione: `test.setTimeout(60_000)` per scenari heavy.
- **wp-phpunit version mismatch**: fissare `wp-phpunit/wp-phpunit: ^6.5` bleeding edge.
- **Coverage gap su Interactivity**: `@wordpress/interactivity` core non è instrumentable; coverage si limita ai store custom.
