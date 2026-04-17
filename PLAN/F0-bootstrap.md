# F0 — Cleanup & Bootstrap

> **Theme:** Rimuovi il MVP bozza, avvia toolchain PHP + JS moderni, plugin installabile in wp-env con pagina admin placeholder.
> **Effort:** M (2-3 giorni)
> **Dipendenze:** nessuna

---

## Obiettivo

Plugin vuoto ma **installabile/attivabile** con:
- Composer PSR-4 funzionante (`Astrologer\Api\` → `src/`).
- `@wordpress/scripts` come build JS con entry points per blocks, admin React, interactivity.
- `wp-env` come ambiente locale.
- Plugin header + bootstrap + service container leggero.
- Pagina admin placeholder che dice "Astrologer API v1.0 — bootstrap OK".
- Tutti i test runner (PHPUnit, Jest, Playwright) girano vuoti con esito verde.

---

## Prerequisiti

- Node 20+, npm 10+, PHP 8.1+, Composer 2+, Docker desktop (richiesto da `wp-env`).
- Leggi prima il MVP bozza attuale per capire cosa archiviamo: `astrologer-api-playground.php`, `includes/`, `frontend/`, `uninstall.php`, `readme.txt`, `languages/`.

---

## Tasks

### F0.1 — Archivia MVP bozza in `_legacy/`

**What:** Spostare tutto il codice attuale dentro `_legacy/` (non cancellare, serve come reference). Aggiornare `.gitignore` per ignorare solo `_legacy/node_modules/` e `_legacy/vendor/` (non l'intera `_legacy/`).

**File da spostare** (preservare struttura interna):
- `astrologer-api-playground.php` → `_legacy/astrologer-api-playground.php`
- `includes/` → `_legacy/includes/`
- `frontend/` → `_legacy/frontend/`
- `uninstall.php` → `_legacy/uninstall.php`
- `readme.txt` → `_legacy/readme.txt`
- `languages/` → `_legacy/languages/`
- `docker-compose.yml`, `apple-container.sh` → `_legacy/`
- `compress_4_wp.sh` → `_legacy/`
- `DEVELOPMENT.md`, `TODO.md`, `astrologer-api-playground.zip`, `astrology-enums.json` → `_legacy/` (ma `astrology-enums.json` andrà riusato in F1 come fonte per Enums PHP)

**Conserva invariati in root**: `LICENSE`, `README.md` (verrà riscritto), `assets/wporg/`, `docs/` (spec API), `PLAN/`.

**Commit:** `chore: archive MVP draft to _legacy/`

---

### F0.2 — composer.json + autoload PSR-4

**File:** `composer.json` (root)

```json
{
  "name": "astrologer/astrologer-api",
  "description": "Official WordPress plugin for the Astrologer API.",
  "type": "wordpress-plugin",
  "license": "GPL-2.0-or-later",
  "require": {
    "php": ">=8.1"
  },
  "require-dev": {
    "phpunit/phpunit": "^10",
    "wp-phpunit/wp-phpunit": "^6.5",
    "yoast/phpunit-polyfills": "^2",
    "brain/monkey": "^2",
    "wp-coding-standards/wpcs": "^3",
    "phpcompatibility/phpcompatibility-wp": "^2",
    "phpstan/phpstan": "^1",
    "szepeviktor/phpstan-wordpress": "^1",
    "dealerdirect/phpcodesniffer-composer-installer": "^1"
  },
  "autoload": {
    "psr-4": {
      "Astrologer\\Api\\": "src/"
    }
  },
  "autoload-dev": {
    "psr-4": {
      "Astrologer\\Api\\Tests\\": "tests/"
    }
  },
  "scripts": {
    "lint": "phpcs",
    "stan": "phpstan analyse",
    "test": "phpunit"
  },
  "config": {
    "allow-plugins": {
      "dealerdirect/phpcodesniffer-composer-installer": true
    }
  }
}
```

**Commit:** `chore: add composer.json with psr-4 autoload`

---

### F0.3 — package.json + @wordpress/scripts

**File:** `package.json` (root)

```json
{
  "name": "astrologer-api",
  "version": "1.0.0",
  "private": true,
  "license": "GPL-2.0-or-later",
  "scripts": {
    "build": "wp-scripts build",
    "start": "wp-scripts start",
    "format": "wp-scripts format",
    "lint:js": "wp-scripts lint-js",
    "lint:css": "wp-scripts lint-style",
    "test:js": "wp-scripts test-unit-js",
    "test:e2e": "playwright test",
    "pot": "wp i18n make-pot . languages/astrologer-api.pot --slug=astrologer-api",
    "env:start": "wp-env start",
    "env:stop": "wp-env stop"
  },
  "devDependencies": {
    "@playwright/test": "^1.50",
    "@wordpress/e2e-test-utils-playwright": "^1",
    "@wordpress/env": "^10",
    "@wordpress/scripts": "^30",
    "@axe-core/playwright": "^4"
  },
  "dependencies": {
    "@wordpress/api-fetch": "^7",
    "@wordpress/block-editor": "^14",
    "@wordpress/blocks": "^13",
    "@wordpress/components": "^28",
    "@wordpress/data": "^10",
    "@wordpress/element": "^6",
    "@wordpress/i18n": "^5",
    "@wordpress/icons": "^10",
    "@wordpress/interactivity": "^6"
  }
}
```

**Commit:** `chore: add package.json with wordpress scripts and deps`

---

### F0.4 — wp-env config

**File:** `.wp-env.json`

```json
{
  "core": "WordPress/WordPress#6.5",
  "phpVersion": "8.1",
  "plugins": ["."],
  "config": {
    "WP_DEBUG": true,
    "WP_DEBUG_LOG": true,
    "WP_DEBUG_DISPLAY": false,
    "ASTROLOGER_ENCRYPTION_KEY": "dev-only-change-in-prod-0000000000000000000000000000000000000000",
    "SCRIPT_DEBUG": true
  },
  "mappings": {
    "wp-content/mu-plugins/dev-mailhog.php": "./tests/fixtures/dev-mailhog.php"
  }
}
```

**Commit:** `chore: add wp-env config`

---

### F0.5 — Tool config files

**Files da creare:**
- `phpunit.xml.dist` — bootstrap WP test suite
- `phpcs.xml.dist` — ruleset WordPress-Extra + custom exclude `_legacy/` e `vendor/`
- `phpstan.neon.dist` — level 8, include `szepeviktor/phpstan-wordpress`
- `jest.config.js` — preset `@wordpress/scripts`
- `playwright.config.ts` — baseURL `http://localhost:8888`, `webServer` non required (wp-env sollevato esternamente), `retries: 2`
- `.eslintrc.json` — extends `plugin:@wordpress/eslint-plugin/recommended`
- `.stylelintrc.json` — extends `@wordpress/stylelint-config`
- `.editorconfig` — WP standard indent_style=tab per PHP, spaces=2 per JSON/YAML
- `.distignore` — esclude `_legacy/`, `node_modules/`, `vendor/bin/`, `tests/`, `.github/`, `.wp-env.json`, `*.md` (tranne `readme.txt`), `package*.json`, `composer*.json`, `phpunit.xml.dist`, `phpcs.xml.dist`, `phpstan.neon.dist`, `jest.config.js`, `playwright.config.ts`, `PLAN/`, `PROMPT.md`, `PROGRESS.md`
- `.gitignore` — `vendor/`, `node_modules/`, `build/`, `.wp-env/`, `.phpunit.result.cache`, `_legacy/node_modules/`, `_legacy/vendor/`

**Commit:** `chore: add tooling config files (phpcs, phpstan, jest, playwright, eslint, editorconfig)`

---

### F0.6 — Plugin main file + bootstrap

**File:** `astrologer-api.php` (nuovo, root)

Contenuto essenziale:
- Plugin Header completo (Name: "Astrologer API", Plugin URI, Description, Version 1.0.0, Author, License GPL-2.0-or-later, License URI, Text Domain astrologer-api, Domain Path /languages, Requires at least 6.5, Requires PHP 8.1, Tested up to 6.7)
- `defined('ABSPATH') || exit;`
- `require_once __DIR__ . '/vendor/autoload.php';`
- Define constants: `ASTROLOGER_API_VERSION`, `ASTROLOGER_API_FILE`, `ASTROLOGER_API_DIR`, `ASTROLOGER_API_URL`
- Bootstrap: `add_action('plugins_loaded', fn() => \Astrologer\Api\Plugin::instance()->boot());`
- Register activation/deactivation/uninstall hooks

**File:** `src/Plugin.php`

Responsabilità:
- Singleton accessor `Plugin::instance()`
- Metodo `boot()`: istanzia `Container`, registra moduli `Bootable`, chiama `boot()` su ciascuno
- Al lancio v1.0 i moduli registrati saranno: `Admin\AdminMenu` (solo per la placeholder page F0.8), gli altri si aggiungono nelle fasi successive

**File:** `src/Container.php`

Micro service container:
- Array-based, no dipendenze esterne
- Metodi: `set(string $id, Closure|object $factory)`, `get(string $id): object`, `has(string $id): bool`
- Resolve lazy + caching singleton

**File:** `src/Support/Contracts/Bootable.php`

```php
interface Bootable {
    public function boot(): void;
}
```

**File:** `uninstall.php` (nuovo)

Minimale ma presente:
- `defined('WP_UNINSTALL_PLUGIN') || exit;`
- Delete options (astrologer_api_*), delete transients, drop custom caps, unschedule cron events, delete CPT posts (solo in F1 una volta che esistono)
- Multisite-aware con loop su blogs

**Commit:** `feat: bootstrap plugin with composer autoload, Container, Plugin singleton`

---

### F0.7 — Makefile

**File:** `Makefile` (nuovo)

Target principali (phony):
- `install` → `composer install && npm install`
- `up` → `npm run env:start`
- `down` → `npm run env:stop`
- `build` → `composer install --no-dev && npm run build`
- `dev` → `npm run start`
- `test` → dipende da `test:php`, `test:js`, `test:e2e`
- `test:php` → `vendor/bin/phpunit`
- `test:js` → `npm run test:js`
- `test:e2e` → `npm run test:e2e`
- `test:a11y` → `npx playwright test tests/e2e/a11y-axe.spec.ts`
- `lint` → dipende da `lint:php`, `lint:js`, `lint:css`, `stan`
- `lint:php` → `vendor/bin/phpcs`
- `lint:js` → `npm run lint:js`
- `lint:css` → `npm run lint:css`
- `stan` → `vendor/bin/phpstan analyse`
- `pot` → `npm run pot`
- `zip` → `./scripts/build-zip.sh`
- `clean` → `rm -rf build/ vendor/ node_modules/ .wp-env/`

**Commit:** `chore: add Makefile`

---

### F0.8 — Admin menu placeholder

**File:** `src/Admin/AdminMenu.php`

Classe `AdminMenu implements Bootable`:
- `boot()` registra `admin_menu` action
- Aggiunge top-level menu "Astrologer" con capability `manage_options`
- Subpage "Settings" che renderizza: `<div class="wrap"><h1>Astrologer API</h1><p>v1.0 bootstrap OK — more coming in F4.</p></div>`

Registra in `Plugin::boot()` via container.

**Commit:** `feat: add admin menu placeholder`

---

## Criterio demoable

```bash
cd /Users/giacomo/dev/astrologerwp
make install
make up
```

Apri `http://localhost:8888/wp-admin`:
- Login (admin/password di wp-env default)
- Plugin "Astrologer API" → attiva
- Menu laterale mostra "Astrologer" → Settings → pagina placeholder visibile.

```bash
make test:php       # PHPUnit vuoto → PASS (0 test)
make test:js        # Jest vuoto → PASS
make test:e2e       # Playwright vuoto → PASS
make lint           # phpcs + phpstan + eslint → PASS
```

---

## Test da scrivere

- `tests/Unit/ContainerTest.php`: container.set()/get()/has() basic, singleton caching.
- `tests/Unit/PluginBootTest.php`: Plugin::instance() returns same instance; boot() registers bootable modules exactly once.
- `tests/e2e/smoke.spec.ts`: plugin attivo in wp-env, menu "Astrologer" visibile, pagina Settings risponde 200 con testo placeholder.

---

## Note

- **Checkpoint #1**: dopo F0, valutare se `@wordpress/scripts` copre tutti i casi (multi-entry per blocks + admin + interactivity). Se troppo rigido, valutare Vite con plugin custom. Probabilmente non necessario.
- `_legacy/` va **mantenuto** nel repo: serve come riferimento per mapping shortcode/i18n keys/validation forms quando si scrivono i nuovi blocks e stores.
- Non registrare ancora CPT, caps, REST endpoint, blocks: questi sono compiti di F1-F5.
