# F4 — Admin UI (Settings + Setup Wizard + Help + Docs)

> **Theme:** UI admin React basata su `@wordpress/components`. Setup wizard post-activation. Help tabs contestuali. Pagina documentazione markdown-rendered.
> **Effort:** L (6-7 giorni)
> **Dipendenze:** F3 (Settings REST endpoint)

---

## Obiettivo

Admin area professionale, WP-native (componenti @wordpress/components, non Tailwind/Material), con:
- Menu "Astrologer" top-level + submenu Charts (CPT), Settings, Docs.
- Settings page React con 6 tab (API Credentials, Astrology Defaults, UI, Cron, Capabilities, Integrations).
- Setup wizard multi-step post-activation (redirect automatico al primo login admin dopo install).
- Help tabs ovunque: settings page, wizard, admin pages CPT.
- Documentation page che renderizza `docs/*.md` inline.

---

## Prerequisiti

- F3 completato. `SettingsController` risponde su `/wp-json/astrologer/v1/settings`.
- `@wordpress/scripts`, `@wordpress/components`, `@wordpress/api-fetch`, `@wordpress/i18n` installati in F0.

---

## Tasks

### F4.1 — AdminMenu

**File:** `src/Admin/AdminMenu.php`

`Bootable`. Su `admin_menu` registra:

```php
add_menu_page(
    __('Astrologer', 'astrologer-api'),
    __('Astrologer', 'astrologer-api'),
    'astrologer_manage_settings',
    'astrologer-api',
    [$settingsPage, 'render'],
    'dashicons-star-filled',
    30
);
add_submenu_page('astrologer-api', __('Settings', 'astrologer-api'), __('Settings', 'astrologer-api'), 'astrologer_manage_settings', 'astrologer-api', [$settingsPage, 'render']);
add_submenu_page('astrologer-api', __('Charts', 'astrologer-api'), __('Charts', 'astrologer-api'), 'edit_posts', 'edit.php?post_type=astrologer_chart');
add_submenu_page('astrologer-api', __('Documentation', 'astrologer-api'), __('Documentation', 'astrologer-api'), 'astrologer_manage_settings', 'astrologer-docs', [$docsPage, 'render']);
add_submenu_page(null, __('Setup Wizard', 'astrologer-api'), '', 'astrologer_manage_settings', 'astrologer-setup', [$wizardPage, 'render']);
```

### F4.2 — SettingsPage (React mount)

**File:** `src/Admin/SettingsPage.php`

`render()`:
```php
echo '<div class="wrap"><h1>' . esc_html__('Astrologer API Settings', 'astrologer-api') . '</h1>';
echo '<div id="astrologer-settings-root"></div></div>';
```

`enqueue_assets()`:
- Script `astrologer-settings` (entry `admin-src/settings/index.tsx`, asset file `.asset.php` via wp-scripts).
- Localize: `wp.apiFetch.nonceMiddleware` (core), `astrologerSettings = { restRoot, nonce, capabilities, capsMap }`.
- `wp_set_script_translations('astrologer-settings', 'astrologer-api', plugin_dir_path(__FILE__) . 'languages/json')`.

### F4.3 — Settings React app

**File:** `admin-src/settings/index.tsx`

```tsx
import { createRoot } from '@wordpress/element';
import { SlotFillProvider } from '@wordpress/components';
import { App } from './App';

const root = document.getElementById('astrologer-settings-root');
if (root) createRoot(root).render(<SlotFillProvider><App /></SlotFillProvider>);
```

**File:** `admin-src/settings/App.tsx`

- Layout: `<Panel>` con `<TabPanel>` 6 tab.
- State locale con `useSettings()` hook (api-fetch GET `/settings`, memoized).
- Save button globale con `apiFetch POST /settings`, `Notice` success/error.

**Tabs** (ogni tab un file):
1. `tabs/ApiCredentialsTab.tsx` — campo password per RapidAPI key + Geonames username + test connection button (wired a `/settings/test-connection`).
2. `tabs/AstrologyDefaultsTab.tsx` — select school preset (4 opzioni), checkbox override flags, select default house system/zodiac/perspective/theme/chart style/language. Preset change triggera modal di conferma "this will overwrite your current defaults?".
3. `tabs/UITab.tsx` — radio UI level (Basic/Advanced/Expert) default, checkbox "allow per-block override", select theme.json preset (auto/custom).
4. `tabs/CronTab.tsx` — toggle per ognuno dei 3 cron (`daily_transits`, `daily_moon_phase`, `solar_return_reminder`). Per `solar_return_reminder` campi aggiuntivi: email template editor, giorni prima, target users (all users with birth data / subscribers only / custom role).
5. `tabs/CapabilitiesTab.tsx` — matrix role × capability read-only in v1.0 con link "edit via filter `astrologer_api/capability_map`". Bottone "reset to defaults".
6. `tabs/IntegrationsTab.tsx` — placeholder "No third-party plugins required. Use hooks in `astrologer_api/*` namespace to integrate."

### F4.4 — Setup Wizard

**File:** `src/Admin/SetupWizardPage.php`

`Bootable`. Su `admin_init` se `get_option('astrologer_setup_completed')` === false e user ha `astrologer_manage_settings` cap → redirect a `admin.php?page=astrologer-setup` (solo prima visita post-activate).

`render()` → mount `<div id="astrologer-wizard-root"></div>`.

**File:** `admin-src/setup-wizard/index.tsx`

6 step con `<Stepper>`:
1. **Welcome** — overview + "Get API key" link esterno a RapidAPI subscription.
2. **API Key** — input + test connection button. Se test OK: green check + auto-advance.
3. **Astrological School** — 4 card con nome preset + descrizione 1-line + "Select" button. Scelta salva defaults nel settings.
4. **Language & UI Level** — select lingua default chart + radio UI level (Basic/Advanced/Expert).
5. **Demo** — iframe embedded preview natal chart con dati Einstein hardcoded per vedere "come appare". Bottone "Try with my data" porta al block editor con demo page.
6. **Done** — checklist di cosa è stato configurato + "Create first page with astrologer blocks" → redirect a `post-new.php?post_type=page` con URL flag che pre-popola con pattern "Natal chart form + display".

On finish: salva `astrologer_setup_completed = current_time('mysql')`, trigger `astrologer_api/setup_wizard_completed` action.

### F4.5 — HelpTabsProvider

**File:** `src/Admin/HelpTabsProvider.php`

`Bootable`. Su `load-{screen_id}` hook per ogni pagina nostra aggiunge help tabs contestuali:

Settings page:
- Tab "Overview"
- Tab "API Credentials"
- Tab "Schools"
- Tab "Troubleshooting"
- Sidebar "For more help → link a Documentation page"

Wizard page: 1 tab overview.

CPT list + edit: tab "About charts" + "Capabilities".

Contenuto di ogni tab in HTML statico italiano-localizzato.

### F4.6 — DocumentationPage

**File:** `src/Admin/DocumentationPage.php`

Carica files `docs/*.md` via filesystem, converte con `league/commonmark` (dep già in composer).

**File:** `admin-src/documentation/index.tsx`

Layout a 2 colonne:
- Sidebar con lista file (`user-guide`, `shortcodes`, `blocks`, `hooks`, `cli`, `rest-api`).
- Main con contenuto renderizzato HTML sanitizzato.

Ogni link interno formato `[Link](#section)` gestito via JS scroll anchor.

### F4.7 — Admin CSS scoping

**File:** `admin-src/shared/admin.scss`

Tutto scoped sotto `.astrologer-admin` per non conflictare con WP core. Usa CSS variables allineate a theme.json:
```scss
.astrologer-admin {
  --ast-spacing: var(--wp--preset--spacing--40, 1rem);
  --ast-border: var(--wp--preset--color--contrast, #1e1e1e);
}
```

### F4.8 — Test admin

**File:** `tests/Jest/admin/ApiCredentialsTab.test.tsx` — render, fill field, click test connection, expect fetch call.
**File:** `tests/Jest/admin/SetupWizard.test.tsx` — step navigation.
**File:** `tests/e2e/admin-setup-wizard.spec.ts` — full flow Playwright.

---

## Criterio di demoable

1. **Fresh install**: attivo plugin → `wp-admin/admin.php?page=astrologer-setup` appare, completo wizard → redirect a post editor.
2. **Settings persist**: salvo API key → reload → api key presente ma offuscata, `has_api_key: true`.
3. **Test connection**: inserisco key valida → click "Test" → alert green.
4. **School preset**: seleziono "Vedic" → defaults cambiano (zodiac: sidereal, sidereal_mode: LAHIRI, house_system: W).
5. **Help tabs**: clicco "Help" top-right in settings → pannello si apre con 4 tab.
6. **Docs page**: apro `astrologer-docs` → markdown renderizzato, link anchor funziona.

---

## Hooks introdotti

- Action `astrologer_api/setup_wizard_completed` — passato `$chosen_school`, `$user_id`.
- Filter `astrologer_api/wizard_steps` — add/remove step custom.
- Filter `astrologer_api/help_tabs` — aggiungere tab.
- Filter `astrologer_api/documentation_pages` — aggiungere pagine doc custom.

---

## Accessibilità

- Tutti i form campi con `<label for>` o `aria-label`.
- Focus management: wizard step change → focus to heading.
- `aria-live="polite"` per notices.
- Keyboard nav testata: Tab cycle, Enter submit, Esc chiude modal.
- Test in F8 axe-core audit.
