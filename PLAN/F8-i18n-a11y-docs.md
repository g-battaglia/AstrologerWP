# F8 — i18n, Accessibilità, Documentazione

> **Theme:** Localizzazione completa EN-only al lancio ma POT pronto. Audit a11y axe-core con 0 violazioni serie. Docs markdown consumate da `DocumentationPage`.
> **Effort:** M (3-4 giorni)
> **Dipendenze:** F4, F5, F6 (tutto il UI deve esistere per poter tradurre e auditare)

---

## Obiettivo

- `.pot` completo generato con `wp i18n make-pot`.
- JSON translations configurate per ogni script handle.
- RTL CSS presente e testato.
- Audit automatizzato axe-core su 6 pagine chiave.
- 6 pagine di documentazione markdown in `docs/`.
- readme.txt finale WP.org compliant.

---

## Prerequisiti

- F4/F5/F6 completi. Tutto il user-facing text è già in `__()` / `_x()` / `_n()`.

---

## Tasks

### F8.1 — POT extraction

**Makefile target:**
```make
pot:
	wp i18n make-pot . languages/astrologer-api.pot --slug=astrologer-api --domain=astrologer-api --exclude=_legacy,node_modules,vendor,tests,build
```

Verifica che il file generato abbia 500+ strings (headers, tab labels, error messages, help tabs, patterns, cron, CLI).

### F8.2 — JSON translations per script handles

`@wordpress/scripts` supporta `*.json` auto-generato se configurato. Aggiungi in `package.json`:
```json
{
  "wp-scripts": {
    "translate": {
      "functions": ["__", "_x", "_n", "_nx"],
      "headers": { "domain": "astrologer-api" }
    }
  }
}
```

**Comando:** `npx po2json` o `wp i18n make-json languages/` dopo aver generato i `.po`. Per v1.0 con solo EN non abbiamo `.po` ancora, quindi genera JSON vuoti placeholder per ogni handle (`astrologer-settings-en_US-<hash>.json`) così il flusso è pronto.

### F8.3 — RTL CSS

`@wordpress/scripts` genera automaticamente `*-rtl.css` da `*.css`. Verifica:
1. Run `npm run build`.
2. Check `blocks/birth-form/style-rtl.css` esiste.
3. Test manuale: setta lang admin su `he_IL`, verifica che il layout è mirrored nei 22 blocks.

Fix eventuali regole che non funzionano con mirror (es. `margin-left` invece di `margin-inline-start`) — modifica sorgente per usare logical properties.

### F8.4 — Script translations loading

**File:** `src/Support/i18n/ScriptTranslations.php`

`Bootable`. Su `admin_enqueue_scripts` e `wp_enqueue_scripts`:
```php
wp_set_script_translations('astrologer-settings', 'astrologer-api', ASTROLOGER_API_DIR . 'languages/json');
wp_set_script_translations('astrologer-wizard', 'astrologer-api', ASTROLOGER_API_DIR . 'languages/json');
foreach ($this->blockHandles as $handle) {
    wp_set_script_translations($handle, 'astrologer-api', ASTROLOGER_API_DIR . 'languages/json');
}
```

### F8.5 — Load textdomain

**File:** `src/Plugin.php` (aggiornamento)

Su `plugins_loaded`:
```php
load_plugin_textdomain('astrologer-api', false, dirname(plugin_basename(ASTROLOGER_API_FILE)) . '/languages');
```

Nota: per WP 6.5+ core carica automaticamente textdomain se slug coincide. Manteniamo il call esplicito per retro compat con 6.5 stretto.

### F8.6 — Accessibility audit setup

**File:** `tests/e2e/a11y.spec.ts`

```ts
import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

const pages = [
  { url: '/wp-admin/admin.php?page=astrologer-api', name: 'settings' },
  { url: '/wp-admin/admin.php?page=astrologer-setup', name: 'wizard' },
  { url: '/wp-admin/admin.php?page=astrologer-docs', name: 'docs' },
  { url: '/demo/natal/', name: 'natal-frontend' },
  { url: '/demo/synastry/', name: 'synastry-frontend' },
  { url: '/demo/moon-phase/', name: 'moon-phase-frontend' },
];

for (const { url, name } of pages) {
  test(`no a11y violations on ${name}`, async ({ page }) => {
    await page.goto(url);
    const results = await new AxeBuilder({ page })
      .withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa'])
      .analyze();
    const serious = results.violations.filter(v => ['serious', 'critical'].includes(v.impact ?? ''));
    expect(serious).toEqual([]);
  });
}
```

### F8.7 — Fix accessibility findings

Dopo il primo run, fix tipici:
- Contrast ratio insufficiente → allinea a theme.json tokens WCAG AA.
- Missing form labels → aggiungi `<label for>` dove manca.
- SVG senza `<title>` → aggiungi `role="img"` + `aria-label` + `<title>`.
- Focus outline rimosso da CSS → rimuovi `outline: 0` non coperto da `:focus-visible` replacement.
- Heading hierarchy skip → rinumera h1→h2→h3 coerente.

### F8.8 — Documentazione markdown

**File:** `docs/user-guide.md`

Sezioni:
1. Introduction — cosa è il plugin, chi lo usa.
2. Getting started — API key setup, setup wizard.
3. Creating your first chart — passo-passo.
4. Blocks overview — tabella 22 blocks con screenshot.
5. Saving charts (CPT) — come archiviare, chi può vedere.
6. Schools of astrology — 4 preset spiegati.
7. UI levels — Basic/Advanced/Expert quando usare quale.
8. Troubleshooting — errori comuni, FAQ.

**File:** `docs/shortcodes.md`

Elenco shortcode di compat (se decidiamo di aggiungerne per backward compat con altri plugin): `[astrologer_natal_form]`, `[astrologer_moon_phase]`, ecc. Ognuno con attributi documentati. In v1.0 può essere vuoto con "Shortcodes not supported, use Gutenberg blocks".

**File:** `docs/blocks.md`

Un capitolo per block con: attributes, example usage, supported bindings, hooks relevant.

**File:** `docs/hooks.md`

Elenco completo actions + filters con signature, quando triggered, esempio.

**File:** `docs/cli.md`

Ogni comando `wp astrologer ...` con flags + example.

**File:** `docs/rest-api.md`

Documentazione endpoint REST: metodo + path + params + esempio curl + response shape.

### F8.9 — Screenshot + banner placeholder

`assets/wporg/`:
- `icon-128x128.png` + `icon-256x256.png`
- `banner-772x250.png` + `banner-1544x500.png` (retina)
- `screenshot-1.png` (settings page)
- `screenshot-2.png` (setup wizard)
- `screenshot-3.png` (birth form + natal chart)
- `screenshot-4.png` (synastry page)
- `screenshot-5.png` (moon phase widget)
- `screenshot-6.png` (CPT list)
- `screenshot-7.png` (block variations menu)

Se assets già esistono da MVP, aggiornarli. F10 si occupa dello shoot finale.

### F8.10 — readme.txt WP.org

**File:** `readme.txt`

Campi:
```
=== Astrologer API ===
Contributors: astrologer
Tags: astrology, astrological chart, natal chart, birth chart, horoscope
Requires at least: 6.5
Tested up to: 6.5
Requires PHP: 8.1
Stable tag: 1.0.0
License: GPLv2 or later

Official WordPress plugin for the Astrologer API.

== Description ==

Full description con markdown, 3 paragrafi. Feature bullet list, call-to-action.

== Installation ==

1. Upload, 2. Activate, 3. Complete setup wizard, 4. Get RapidAPI subscription, 5. Insert API key.

== Frequently Asked Questions ==

= Do I need an API subscription? = Yes. Free tier available on RapidAPI.
= Can I save charts? = Yes, via CPT. Private by default.
= Which schools are supported? = Modern Western, Traditional/Hellenistic, Vedic/Jyotish, Uranian/Hamburg.
= Are third-party integrations supported? = Not hardcoded. Use `astrologer_api/*` hooks.

== Screenshots ==

1. Settings page
2. Setup wizard
3. ...

== Changelog ==

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.0 = First stable release.
```

Validazione: pasta il contenuto in https://wordpress.org/plugins/developers/readme-validator/ quando stai facendo il release prep F10.

### F8.11 — Test i18n

**File:** `tests/Integration/I18nTest.php`

- `test_pot_file_exists_and_not_empty()`.
- `test_all_user_facing_strings_localized()` — grep pattern `echo\s+['"]` nei file PHP di UI, fail se ne trova.
- `test_rtl_stylesheets_generated()` — per ogni `build/*.css` verifica che esista `*-rtl.css`.

---

## Criterio di demoable

1. `make pot` genera `languages/astrologer-api.pot` con 500+ strings.
2. `make test:a11y` → 0 violazioni serious/critical su tutte le 6 pagine.
3. Admin lang switch he_IL → settings page mirrored, wizard mirrored.
4. `admin.php?page=astrologer-docs` carica 6 pagine, link anchor funzionano, code blocks renderizzati.
5. `readme.txt` validator online → 0 errori.

---

## Hooks introdotti

- Filter `astrologer_api/documentation_pages` — add pagine custom.
- Filter `astrologer_api/a11y_axe_options` — modifica opzioni AxeBuilder in test.

---

## Accessibilità checklist WCAG 2.1 AA

- [x] 1.1.1 Non-text content: SVG chart con title/desc.
- [x] 1.3.1 Info and relationships: heading structure logica.
- [x] 1.4.3 Contrast minimum: token theme.json conformi.
- [x] 2.1.1 Keyboard: tutte le azioni operabili da tastiera.
- [x] 2.4.3 Focus order: Tab sequence logica.
- [x] 2.4.7 Focus visible: outline presente.
- [x] 3.2.2 On input: form submit richiede explicit action.
- [x] 3.3.1 Error identification: errori visibili + annunciati.
- [x] 4.1.2 Name, role, value: aria completi.
- [x] 4.1.3 Status messages: `aria-live` regions.
