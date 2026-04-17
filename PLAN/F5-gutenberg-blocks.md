# F5 — Gutenberg Blocks (22 blocks + Patterns + Variations + FSE + Bindings)

> **Theme:** Tutta la superficie Gutenberg: 22 blocks con block.json v3, dynamic rendering PHP, editor React, patterns ready-made, variations per le 4 scuole, template parts FSE, Block Bindings API per CPT.
> **Effort:** XL (8-10 giorni)
> **Dipendenze:** F3 (REST endpoints), F4 (admin settings per defaults)

---

## Obiettivo

Una libreria completa di blocchi professionali che permetta di costruire un sito astrologico full-WP-native senza scrivere codice: form dinamici, chart display, tabelle, widgets moon phase, compatibilità.

Categoria: `astrology` (custom, con icona dashicons star-filled).

**Lista 22 blocks:**

| Slug | Tipo | Render | Use case |
|---|---|---|---|
| `natal-chart` | display | dynamic PHP | Mostra SVG natale |
| `synastry-chart` | display | dynamic | SVG synastry 2 soggetti |
| `transit-chart` | display | dynamic | SVG transito |
| `composite-chart` | display | dynamic | SVG composite |
| `solar-return-chart` | display | dynamic | Solar return |
| `lunar-return-chart` | display | dynamic | Lunar return |
| `now-chart` | display | dynamic | Chart ora corrente |
| `moon-phase` | display | dynamic | Fase lunare oggi/data |
| `positions-table` | data | dynamic | Tabella posizioni |
| `aspects-table` | data | dynamic | Tabella aspetti |
| `elements-chart` | data | dynamic | Distribuzione elementi (fuoco/acqua/...) |
| `modalities-chart` | data | dynamic | Distribuzione modalità |
| `compatibility-score` | data | dynamic | Score compatibilità |
| `relationship-score` | data | dynamic | Score relazione dettagliato |
| `birth-form` | form | dynamic + interactivity | Form dati nascita |
| `synastry-form` | form | dynamic + interactivity | Form 2 soggetti |
| `transit-form` | form | dynamic + interactivity | Form transito |
| `composite-form` | form | dynamic + interactivity | Form composite |
| `solar-return-form` | form | dynamic + interactivity | Form solar return |
| `lunar-return-form` | form | dynamic + interactivity | Form lunar return |
| `now-form` | form | dynamic + interactivity | Form "chart now" GPS |
| `compatibility-form` | form | dynamic + interactivity | Form compatibilità rapida |

---

## Prerequisiti

- F3 completo. Tutti i REST endpoint rispondono.
- F4 settings permettono school preset scelta.

---

## Tasks

### F5.1 — BlocksRegistry + categoria

**File:** `src/Blocks/BlocksRegistry.php`

`Bootable`. Su `init`:
```php
register_block_type(ASTROLOGER_API_DIR . '/blocks/natal-chart');
// ... uno per ciascuno dei 22
```

**File:** `src/Blocks/BlockCategory.php`

Filter `block_categories_all` per aggiungere categoria `astrology` con icon `star-filled`.

### F5.2 — Shared build config

**File:** `package.json` aggiornamento: entry points dinamici.

**File:** `wp-scripts.config.js` (se serve custom):
```js
module.exports = {
  ...defaultConfig,
  entry: getWebpackEntryPoints('block-json'),
};
```

Un solo comando `npm run build` compila tutti i 22 blocks.

### F5.3 — Template block (birth-form)

Definisce il pattern che tutti gli altri seguono. File set:

**File:** `blocks/birth-form/block.json`

```json
{
  "$schema": "https://schemas.wp.org/trunk/block.json",
  "apiVersion": 3,
  "name": "astrologer-api/birth-form",
  "title": "Birth Form",
  "category": "astrology",
  "icon": "calendar-alt",
  "description": "Form to collect birth data for natal chart calculation.",
  "keywords": ["astrologer", "natal", "form"],
  "textdomain": "astrologer-api",
  "supports": {
    "html": false,
    "align": ["wide", "full"],
    "color": { "background": true, "text": true },
    "spacing": { "margin": true, "padding": true },
    "typography": { "fontSize": true }
  },
  "attributes": {
    "uiLevel": { "type": "string", "default": "basic", "enum": ["basic", "advanced", "expert"] },
    "targetBlockId": { "type": "string", "default": "" },
    "showSaveOption": { "type": "boolean", "default": false },
    "preset": { "type": "string", "default": "auto" },
    "redirectAfterSubmit": { "type": "string", "default": "" }
  },
  "example": {
    "attributes": { "uiLevel": "basic" }
  },
  "editorScript": "file:./edit.tsx",
  "style": "file:./style.css",
  "viewScriptModule": "file:./view.ts",
  "render": "file:./render.php"
}
```

**File:** `blocks/birth-form/edit.tsx`

- `InspectorControls` con `<PanelBody>` per uiLevel select, target chart block picker (usa `useEntityRecords('postType','wp_block')` o block tree search), preset select (override da scuola).
- Preview: card con placeholder "Birth Form (basic/advanced/expert)".
- Usa `useBlockProps()` wrapper.

**File:** `blocks/birth-form/render.php`

```php
<?php
use function Astrologer\Api\render_form_template;
echo render_form_template('birth', $attributes, $block);
```

Dove `render_form_template` include `templates/forms/birth.php` con `data-wp-*` attributes per Interactivity (dettagli in F6).

**File:** `blocks/birth-form/style.css`

CSS minimo scoped con `.wp-block-astrologer-api-birth-form`. Uses theme.json tokens.

**File:** `blocks/birth-form/view.ts`

Stub: `import './interactivity';` che carica lo store (definito in F6).

### F5.4 — Altri 6 form blocks

Struttura identica a birth-form, template:
- `synastry-form` → `templates/forms/synastry.php` (2 subject panels)
- `transit-form` → `templates/forms/transit.php` (subject + transit date)
- `composite-form` → `templates/forms/composite.php`
- `solar-return-form` → `templates/forms/solar-return.php`
- `lunar-return-form` → `templates/forms/lunar-return.php`
- `now-form` → `templates/forms/now.php` (geolocation opt-in)
- `compatibility-form` → `templates/forms/compatibility.php` (quick)

### F5.5 — Display chart blocks (7)

**File:** `blocks/natal-chart/block.json` + render.php + edit.tsx

Attributes:
```json
{
  "source": { "type": "string", "default": "form", "enum": ["form", "cpt", "meta", "custom"] },
  "chartId": { "type": "number", "default": 0 },
  "customSubject": { "type": "object", "default": null },
  "theme": { "type": "string", "default": "auto" },
  "wheelOnly": { "type": "boolean", "default": false },
  "showLegend": { "type": "boolean", "default": true },
  "bindingSource": { "type": "string", "default": "" }
}
```

`render.php`:
- Se `source === 'cpt'` carica post `chartId`, SVG da meta.
- Se `source === 'form'` emette placeholder `<div data-wp-interactive="astrologer/chart-display" data-astrologer-chart-type="natal">` che il form aggiornerà via Interactivity store.
- Se `source === 'meta'` Block Bindings prendono da post meta.

Editor: preview SVG statico demo + InspectorControls.

Same pattern per: synastry-chart, transit-chart, composite-chart, solar-return-chart, lunar-return-chart, now-chart.

### F5.6 — Data display blocks (7)

- `positions-table` — SSR tabella con colonne Planet/Sign/House/Degree/Retro. Attributes: `chartId`, `pointsFilter[]`.
- `aspects-table` — tabella con orb filter. Attributes: `chartId`, `aspectFilter[]`, `maxOrb`.
- `elements-chart` — barchart SVG percentuale fuoco/terra/aria/acqua.
- `modalities-chart` — barchart cardinale/fisso/mutevole.
- `compatibility-score` — circular gauge con score + legend.
- `relationship-score` — 4 metriche (love, communication, conflict, overall) con score bar.
- `moon-phase` — big illustration + nome fase + next events.

### F5.7 — Patterns

**File:** `patterns/birth-form-with-chart.php`

```php
<?php
/**
 * Title: Birth Chart — Form + Display
 * Slug: astrologer-api/birth-form-with-chart
 * Categories: astrology
 */
?>
<!-- wp:astrologer-api/birth-form {"uiLevel":"basic"} /-->
<!-- wp:astrologer-api/natal-chart {"source":"form"} /-->
<!-- wp:astrologer-api/positions-table /-->
<!-- wp:astrologer-api/aspects-table /-->
```

Altri patterns:
- `synastry-compatibility-page.php` — form synastry + chart + compatibility score.
- `moon-phase-widget.php` — moon phase standalone.
- `daily-horoscope-layout.php` — moon phase + now chart.
- `astrologer-homepage-hero.php` — hero con moon + birth form CTA.
- `chart-archive-single.php` — template per CPT single chart (embed natal-chart con source=cpt).

Registra in `src/Blocks/BlockPatternsRegistry.php` via `register_block_pattern`.

### F5.8 — Variations per scuole

**File:** `src/Blocks/VariationsRegistry.php`

Per ogni form block registra 4 variations:
```php
register_block_variation('astrologer-api/birth-form', [
    'name' => 'modern-western',
    'title' => __('Birth Form — Modern Western', 'astrologer-api'),
    'attributes' => ['preset' => 'modern-western', 'uiLevel' => 'basic'],
    'scope' => ['inserter', 'transform'],
]);
```

Replicato per `traditional`, `vedic`, `uranian`.

### F5.9 — FSE templates + parts

**File:** `src/Blocks/FseTemplatesRegistry.php`

Registra template parts:
- `parts/astrologer-chart-sidebar.html` — sidebar con moon phase + quick form.
- `templates/single-astrologer_chart.html` — template for single CPT chart.

Via filter `theme_file_path` fallback se theme non li ha.

### F5.10 — Block Bindings API

**File:** `src/Blocks/BlockBindingsSource.php`

Registra source `astrologer-api/chart-field`:
```php
register_block_bindings_source('astrologer-api/chart-field', [
    'label' => __('Astrologer chart field', 'astrologer-api'),
    'get_value_callback' => function ($source_args, $block_instance, $attribute_name) {
        $post_id = $block_instance->context['postId'] ?? 0;
        if (!$post_id || get_post_type($post_id) !== 'astrologer_chart') return null;
        $field = $source_args['field'] ?? '';
        $chart_data = get_post_meta($post_id, '_astrologer_chart_data', true);
        return data_get($chart_data, $field); // helper dot-path
    },
]);
```

Campi disponibili esposti via REST `/bindings/fields` (F3.11): `subject.name`, `subject.city`, `chart.svg`, `aspects.*`, `moon_phase.name`, ecc.

### F5.11 — Test blocks

**File:** `tests/Jest/blocks/birth-form.edit.test.tsx` — render edit, interact con Inspector, expect attribute change.
**File:** `tests/Integration/Blocks/BlocksRegistryTest.php` — `register_block_type` non throws, blocks list in `get_registered_blocks` include 22 entries.
**File:** `tests/e2e/block-birth-form.spec.ts` — inserisco block, publico pagina, vedo form renderizzato.

---

## Criterio di demoable

1. **Editor**: apro post editor → block inserter → cat "Astrology" → vedo 22 blocks + patterns.
2. **Insert pattern**: inserisco "Birth Chart — Form + Display" → 4 blocks appaiono già connessi.
3. **Variations**: inserter per "birth-form" mostra 4 variations scuola.
4. **CPT single**: apro admin → Charts → edit → frontend single template usa FSE template astrologer_chart + binding legge da post meta.
5. **Bindings**: inserisco paragraph con binding `astrologer-api/chart-field` field `subject.name` dentro CPT single → mostra nome.

---

## Accessibilità

- Tutti i form con label esplicita.
- `<fieldset>` + `<legend>` per grouping (date, location).
- Error messages con `aria-describedby`.
- Chart SVG con `<title>` e `<desc>` elementi + `role="img"` aria-label descrittiva.

---

## Decisione open (Checkpoint #4)

22 blocks sono **molti** per il Plugin Check e review WP.org. Fallback se review rejects:
- Consolidare in 4 super-blocks (`astrologer-form`, `astrologer-chart`, `astrologer-data`, `astrologer-compatibility`) con variations molto più granulari.
- Pro: meno file, review più semplice. Contro: DX editor peggiore (meno discoverability).

**Raccomandazione**: procedere con 22 e monitorare il feedback review. Variations-first design permette il refactor se servisse.

---

## Hooks introdotti

- Filter `astrologer_api/block_attributes_defaults` — modifica defaults attributes.
- Filter `astrologer_api/block_variations` — add/remove variations.
- Filter `astrologer_api/block_patterns` — add/remove patterns.
- Filter `astrologer_api/bindings_fields` — aggiungere custom binding fields.
- Action `astrologer_api/block_rendered` — passato `$block_name`, `$attributes`.
