# F10 — Release prep & WP.org submission

> **Theme:** Pacchetto finale pulito, screenshot ufficiali, version bump, zip build, submit WP.org review.
> **Effort:** S (1-2 giorni)
> **Dipendenze:** F9 (tutto verde)

---

## Obiettivo

Plugin 1.0.0 installabile come ZIP, screenshot live del plugin, readme.txt e `astrologer-api.php` allineati. Submit al review WP.org pronto.

---

## Prerequisiti

- F9 chiuso. `make test:all` verde. Plugin Check clean.
- Account WP.org contributors impostato, SVN credentials disponibili.

---

## Tasks

### F10.1 — Version bump

File da allineare a `1.0.0`:

- `astrologer-api.php` plugin header:
  ```
  * Version: 1.0.0
  ```
- `package.json`: `"version": "1.0.0"`
- `composer.json`: nessun version field (non richiesto per plugin).
- `readme.txt`: `Stable tag: 1.0.0`.
- Constant nel main file: `define('ASTROLOGER_API_VERSION', '1.0.0')`.

**Commit:** `chore(release): bump to 1.0.0`.

### F10.2 — CHANGELOG.md

**File:** `CHANGELOG.md` (root)

Formato Keep a Changelog:
```md
# Changelog

All notable changes to this project will be documented in this file.

## [1.0.0] — <data release>

### Added
- First stable release of Astrologer API plugin.
- Support for 28+ endpoints from the Astrologer API (natal, synastry, transit, composite, solar return, lunar return, now chart, moon phase, AI context, MCP).
- 22 Gutenberg blocks (7 forms, 7 displays, 7 data visualizations, 1 moon phase).
- 6 block patterns, 4 school-preset variations per form block.
- Setup wizard with 6 steps including API key test.
- 4 astrological school presets (Modern Western, Traditional/Hellenistic, Vedic/Jyotish, Uranian/Hamburg).
- 3 UI levels (Basic, Advanced, Expert) toggleable globally or per block.
- CPT `astrologer_chart` for saving charts with granular capabilities.
- WP-CLI commands: `wp astrologer chart|cache|settings|health|doctor`.
- 3 toggleable cron jobs: daily transits snapshot, daily moon phase cache, solar return reminders.
- Complete public hooks surface under `astrologer_api/*`.
- Full Block Bindings API support for CPT chart fields.
- WCAG 2.1 AA compliant with automated axe-core audit.
- RTL support.
- Interactivity API frontend (no React runtime on frontend).
```

### F10.3 — Screenshot finali

Cattura dal wp-env istanza demo con dati Einstein come subject:

1. Settings page - API Credentials tab compilato (nasconde API key reale).
2. Setup wizard step 3 "Select school".
3. Post editor con block Birth Form + Natal Chart inseriti.
4. Frontend pagina con natal chart SVG + positions table + aspects table.
5. Synastry chart con 2 subject.
6. Moon phase widget standalone.
7. CPT Charts list admin.
8. WP-CLI `wp astrologer health` terminal output.

Salva in `assets/wporg/screenshot-1.png` ... `screenshot-8.png`.

Pesa: max 500KB per screenshot, PNG ottimizzato (pngquant).

### F10.4 — .distignore e build ZIP

**File:** `.distignore` (già in F0)

Verifica esclusione:
```
/_legacy/
/tests/
/node_modules/
/admin-src/
/interactivity-src/
/.wp-env.json
/phpunit.xml.dist
/phpcs.xml.dist
/phpstan.neon.dist
/composer.lock
/package-lock.json
/Makefile
/PLAN/
/docs/*.md
/.github/
/.vscode/
/.idea/
/*.map
/coverage/
```

**Nota**: `docs/*.md` viene escluso dal bundle runtime (caricato solo da `DocumentationPage` in admin, che può leggere da filesystem dopo build). Se `DocumentationPage` richiede i file inclusi, rimuovi `docs/*.md` dal `.distignore` e includi nel ZIP.

**Build ZIP:**

**File:** `Makefile` target `zip`:
```make
zip: build
	@test -d build || mkdir build
	rsync -av --delete --exclude-from=.distignore . build/astrologer-api/
	cd build && zip -r astrologer-api-1.0.0.zip astrologer-api -x "*.DS_Store"
	@echo "Package: build/astrologer-api-1.0.0.zip"
```

**Verifica ZIP manuale:**
1. Unzip in directory temp.
2. `ls -la astrologer-api/` deve contenere: plugin main, readme.txt, LICENSE, src/, blocks/, templates/, languages/, vendor/, build/ (minified JS/CSS), assets/wporg/.
3. `composer install --no-dev` eseguito prima del zip per avere vendor/ senza dev deps.

### F10.5 — Smoke install test

Su fresh wp-env:
```bash
cd /tmp
git clone https://github.com/WordPress/wordpress-develop wp-test && cd wp-test
npx wp-env start
wp plugin install /path/to/build/astrologer-api-1.0.0.zip --activate
wp plugin list  # verifica astrologer-api active
wp astrologer health  # verifica base
```

Test manuale scenario golden path:
- Setup wizard appare.
- Inserisci API key, test connection ok.
- Crea pagina con pattern "Birth Form + Natal Chart".
- Compila form con Einstein.
- SVG appare.

### F10.6 — WP.org review readiness

Review checklist pre-submit:
- [x] GPLv2+ license file presente.
- [x] readme.txt Stable tag matches main file Version.
- [x] Nessun codice ofuscato / minified nel repo sorgente (solo in build).
- [x] Nessun phone-home / tracking senza opt-in esplicito.
- [x] Nessun carico esterno CSS/JS da CDN remoti (tutti local via wp_enqueue).
- [x] Nessun file eseguibile binario (no `.so`, no `.exe`).
- [x] Prefix univoco globale: `astrologer_api_` / `Astrologer\Api\` / `astrologer-api` (no clash con astrologer-api-free o simili).
- [x] Trademark check: "Astrologer" e "WordPress" usati correttamente (non nel plugin name).
- [x] Screenshots caption in `readme.txt`.
- [x] Tested up to 6.5+ latest.
- [x] No AJAX senza nonce.
- [x] No SQL senza `$wpdb->prepare`.
- [x] Capabilities check su ogni azione admin-side.

### F10.7 — Submit plugin to WP.org

Portale: https://wordpress.org/plugins/developers/add/

Upload ZIP. Form fields:
- **Plugin Name**: Astrologer API.
- **Plugin URL**: https://astrologer-api.com (se esiste landing) altrimenti lascia vuoto.
- **Description**: stesso testo di readme.txt === Description ===.
- **ZIP**: `astrologer-api-1.0.0.zip`.

Submission → email automatica "Review pending". Review time tipico: 2-6 settimane.

### F10.8 — Dopo approvazione: SVN push

Una volta approvato, WP.org assegna slug (presumibilmente `astrologer-api` se libero).

Checkout SVN:
```bash
svn co https://plugins.svn.wordpress.org/astrologer-api astrologer-api-svn
cd astrologer-api-svn
cp -R /path/to/build/astrologer-api/* trunk/
cp -R assets/wporg/* assets/
svn add trunk/* assets/*
svn ci -m "Initial release 1.0.0"

# Tag
svn cp trunk tags/1.0.0
svn ci -m "Tagging version 1.0.0"
```

### F10.9 — GitHub release mirror

**Action manuale:**
1. Tag locale: `git tag -a v1.0.0 -m "Release 1.0.0"` (no push automatico).
2. Attendi approvazione umana per `git push origin v1.0.0`.
3. GitHub Release page: carica `astrologer-api-1.0.0.zip` come binary.

### F10.10 — Post-release hooks

- Open GitHub issue template per bug report.
- Setup GitHub Discussions per Q&A community.
- Documentazione pubblica: `docs/` già pronto. Valutare pubblicazione come GitHub Pages separato (solo se utente lo chiede — fuori scope v1.0).

---

## Criterio di demoable

1. `make zip` → `build/astrologer-api-1.0.0.zip` presente, ~2-5MB.
2. Install su wp-env fresh → attivazione senza errori fatal, setup wizard redirect.
3. `wp plugin check astrologer-api` → 0 issues.
4. Screenshots 1-8 presenti e caption nel readme.txt.
5. GitHub tag `v1.0.0` creato (non pushato).

---

## Operazioni che richiedono approvazione umana esplicita

- `git push` (qualsiasi branch o tag).
- Submit form WP.org (richiede login account utente).
- SVN commit (richiede SVN credentials).
- Pubblicazione GitHub release (richiede push).

Questi task nel Ralph loop vanno marcati `[?]` con "RICHIEDE APPROVAZIONE".

---

## Hooks introdotti

Nessuno. F10 è packaging puro.

---

## Chiusura progetto

Quando tutto F0-F10 sono `[x]`:
- Progress file: aggiungi sezione finale con metriche totali (LoC, test coverage, features count).
- `_legacy/` può essere archiviato su branch separato e rimosso da `master` con commit "chore: archive _legacy branch". Approvazione richiesta.

**Messaggio finale atteso:**
```
v1.0.0 release package ready: build/astrologer-api-1.0.0.zip
Plugin Check: clean.
Test suite: green (PHPUnit 78% coverage, Jest 64% coverage, Playwright 21/21 pass, axe-core 0 serious).
Next: human review of readme.txt, approve `git push origin v1.0.0`, submit to WP.org portal.
```
