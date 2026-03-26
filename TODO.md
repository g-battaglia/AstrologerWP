# AstrologerWP (astrologer-wp) - TODO

All 29 items completed.

## CRITICAL - Bloccanti per produzione

### C1. Rimuovere debug dumps visibili in produzione — DONE

- **Files:** `NowForm.tsx`, `CompatibilityForm.tsx`
- Removed `JSON.stringify` debug dumps.

### C2. Fix CompatibilityForm - campi location mancanti — DONE

- **File:** `CompatibilityForm.tsx`
- Added `latitude`, `longitude`, `city`, `nation` fields.

### C3. Cambiare licenza AGPL → GPL-2.0-or-later — DONE

- LICENSE file updated with GPL-2.0-or-later.
- Plugin header and README already updated.

### C4. Creare readme.txt per WordPress.org — DONE

- **File:** `readme.txt` (created)
- All required WP.org sections including Third-Party Services disclosure.

### C5. Creare uninstall.php — DONE

- **File:** `uninstall.php` (created)
- Option cleanup, transient cleanup, multisite support.

### C6. Rimuovere console.log dal codice di produzione — DONE

- **Files:** `main.tsx`, `blocks/index.tsx`
- Removed all `console.log`/`console.warn` statements.

## HIGH - Necessari prima del rilascio

### H1. i18n frontend forms — DONE

- Wrapped ~94 hardcoded strings in `t()` across all 8 form components + `SubjectFormFields.tsx`.
- Registered ~50 new i18n keys in `get_frontend_config()` in `class-astrologer-api-frontend.php`.

### H2. i18n backend — DONE

- **File:** `class-astrologer-api-settings.php`
- Wrapped all ~30 select option labels in `__()`.

### H3. Generare file .pot — DONE

- **File:** `languages/astrologer-api.pot` (created, 677 lines)
- Generated via `xgettext`.

### H4. Validazione form client-side — DONE

- **Files:** `lib/types.ts`, `SubjectFormFields.tsx`, all 7 form components
- `validateSubjectForm()`, `isFormValid()`, `SubjectFormErrors` type.
- Error state, `submitted` flag, live re-validation, `aria-invalid` attributes.

### H5. City autocomplete (GeoNames) — DONE

- **Files:** `CityAutocomplete.tsx` (created), `class-astrologer-api-rest.php`, `api.ts`, `SubjectFormFields.tsx`
- PHP `GET /astrologer/v1/city-search` endpoint proxying to GeoNames.
- React component with debounce (300ms), keyboard navigation, ARIA, outside-click-to-close.

### H6. Eliminare duplicazione codice nei form — DONE

- **Files:** `lib/types.ts` (created), `SubjectFormFields.tsx` (created), all 7 form files refactored
- Shared `SubjectFormData`, `buildSubject()`, `DEFAULT_SUBJECT`.

### H7. Docker setup — DONE

- **Files:** `docker-compose.yml` (created), `Makefile` (created)
- WordPress 6 + MariaDB 11, bind-mount plugin, targets: up, down, logs, shell, build-fe, dev-fe, pot, zip, clean.

### H8. Script di distribuzione ZIP — DONE

- **File:** `compress_4_wp.sh` (created, chmod +x)
- Builds frontend, assembles plugin files, creates ZIP.

### H9. Creare .gitignore root — DONE

- **File:** `.gitignore` (created)

### H10. Componente Moon Phase — DONE

- **Files:** `MoonPhaseDisplay.tsx` (created), `ComponentMounter.tsx`, `class-astrologer-api-blocks.php`, `class-astrologer-api-frontend.php`
- React component displaying moon emoji, phase name, Sun-Moon angle.
- Listens for `astrologer:birth-data-updated` events.
- Supports `useNow` prop for current-moment mode.
- PHP shortcode `[astrologer_moon_phase]` with `use_now` attribute.
- Gutenberg block `astrologer-api/moon-phase`.
- 10 new i18n keys registered (moon phase names + labels).

## MEDIUM - Miglioramenti qualità

### M1. Rifattorizzare default location (Roma → neutro) — DONE

- **Files:** `lib/types.ts`, `api.ts`, `TransitForm.tsx`, `TransitChart.tsx`, `RelationshipScore.tsx`, `TransitChartBlock.tsx`, `class-astrologer-api-blocks.php`
- All Roma defaults (41.9028, 12.4964, Europe/Rome) replaced with empty/neutral values (0, 0, UTC).

### M2. Pulire dead code — DONE

- Deleted `frontend/vite.config.main.ts`.
- Dead `api.ts` functions left (may be used externally).

### M3. Rimuovere commenti italiani — DONE

- **Files:** `Loader.tsx`, `api.ts`, `blocks.php`, `settings.php`
- Translated to English.

### M4. Fix CSS: rimuovere duplicazione variabili Kerykeion — DONE

- **File:** `frontend/src/index.css`
- Removed duplicate CSS variable blocks (lines 253-303).

### M5. Spostare eslint in devDependencies — DONE

- **File:** `frontend/package.json`
- Also fixed `license` field to `GPL-2.0-or-later`.

### M6. Fix duplicazione SubjectInspector nei blocks — DONE

- **Files:** `wp-utils.tsx`, `SynastryChartBlock.tsx`, `RelationshipScoreBlock.tsx`
- Extracted `SubjectInspector` to shared location.

### M7. Rate limiting sugli endpoint REST pubblici — DONE

- **File:** `class-astrologer-api-rest.php`
- Transient-based rate limiting: 60 requests/minute per IP.
- Admins exempt. IP detection supports Cloudflare/proxy headers.
- Applied to both `proxy_request()` and `handle_city_search()`.

### M8. Validazione range longitude/latitude nell'API REST — DONE

- **File:** `class-astrologer-api-rest.php`
- `validate_callback` for lat (-90/90) and lng (-180/180) in `get_subject_args()`.

### M9. Wrappare i18n la stringa 'API Error' — DONE

- **File:** `class-astrologer-api-rest.php:503`
- Wrapped in `__()`.

## LOW - Nice to have

### L1. Font Berkshire Swash — DONE

- **File:** `frontend/src/index.css`
- Removed `var(--font-berkshire-swash)`, replaced with `serif` fallback.

### L2. Conferma "unsaved changes" nella settings page — DONE

- **File:** `SettingsPage.tsx`
- `beforeunload` listener with `dirty` state tracking.
- Resets on successful save.

### L3. Auto-dismiss messaggio di successo settings — DONE

- **File:** `SettingsPage.tsx`
- `setTimeout` (5 seconds) with cleanup on unmount.

### L4. Popolare campi author e description in package.json — DONE

- **File:** `frontend/package.json`
- Added `author`, translated `description` to English.

### L5. Banner/icon assets per WordPress.org — DONE

- **Files:** `assets/wporg/` directory created with SVG placeholders:
    - `icon-128x128.svg`, `icon-256x256.svg`
    - `banner-772x250.svg`, `banner-1544x500.svg`
    - `README.txt` with export instructions
