# Astrologer API — WordPress Plugin ufficiale v1.0 — Master Plan

> **Audience:** Executing agent (`astrologer-builder` in opencode, Ralph loop).
> **Rule:** Ogni fase è self-contained, sequenziale, demoable (dimostrabile localmente) prima di iniziare la successiva.
> **Working style:** Italian-speaking user, English code/UI/docs, no CI/CD, test locali con `make test`.

---

## Obiettivo strategico

Trasformare il MVP bozza in `/Users/giacomo/dev/astrologerwp` nel **plugin ufficiale WordPress.org** dell'API astrologica `astrologer-api` (Python/FastAPI su RapidAPI, 28+ endpoint). Il plugin deve:

- Coprire **tutti** gli endpoint dell'API (chart base + Moon Phase dedicati + AI Context + MCP + Health).
- Essere **WP-native**: Composer/PSR-4, block.json v3, Interactivity API frontend, @wordpress/components admin, theme.json integration, CPT, Block Bindings API, FSE templates.
- Offrire **UX a strati**: 4 preset di scuola astrologica + 3 livelli UI (Basic/Advanced/Expert) + setup wizard + help tabs + documentation page.
- Essere **estensibile**: non integrarsi a plugin specifici (WooCommerce/MemberPress/BuddyPress) ma esporre hooks documentati così che chi costruisce un sito astrologico WP possa combinarli come vuole.
- Essere **pronto WP.org**: GPL v2+, readme.txt compliant, Plugin Check zero issues, WCAG 2.1 AA.

---

## Decisioni confermate (non negoziare)

| Area | Scelta |
|---|---|
| Versione | v1.0 **originale**, nessuna backcompat. MVP attuale archiviato in `_legacy/`. |
| PHP | 8.1+ (typed properties, readonly, enum) |
| WordPress | 6.5+ (Block Bindings, Interactivity API) |
| Autoload | Composer PSR-4 `Astrologer\Api\` → `src/` |
| Build JS | `@wordpress/scripts` (nativo block.json, `.asset.php`, translations) |
| Frontend | Hybrid: **React in editor, Interactivity API in frontend** (form inclusi) |
| UI library | `@wordpress/components` + theme.json integration |
| Endpoint API | Tutti 28+ (chart base + Moon Phase ×4 + AI Context ×8 + MCP ×1 + Health ×1) |
| Preset scuole | Moderna Occidentale (default), Tradizionale/Ellenistica, Vedica/Jyotish, Uraniana/Amburghese |
| Livelli UI | Basic / Advanced / Expert (toggle in settings + per block) |
| Onboarding | Setup wizard multi-step post-activation |
| Gutenberg | block.json v3, Dynamic blocks, Patterns, Variations per preset, FSE templates, Block Bindings API, categoria "Astrology" |
| Data model | CPT `astrologer_chart` privato default (opt-in public), user meta `astrologer_birth_data` |
| WP-CLI | `wp astrologer chart \| cache \| settings \| health \| doctor` |
| Capabilities | Custom granulari (`astrologer_manage_settings`, `astrologer_calculate_chart`, `astrologer_save_chart`, `astrologer_view_any_chart`, `astrologer_run_cli`) |
| Hooks pubblici | Ampia superficie documentata (`astrologer_api/...` actions + filters) |
| Cron | Daily transits + Solar return reminder + Daily moon phase — **tutti toggleable da admin** |
| Sicurezza API key | Encryption at rest `sodium_crypto_secretbox`, chiave da `ASTROLOGER_ENCRYPTION_KEY` (fallback `AUTH_KEY` + salt persistente) |
| Caching | **Nessuno** per chiamate API. CPT = archivio esplicito. Unica eccezione: moon phase daily cache (feature dedicata) |
| Integrazioni plugin terzi | **Nessuna hardcoded**. Solo hooks. |
| Testing | PHPUnit + Jest + Playwright completi, eseguibili con `make test`. **Nessun CI/CD.** |
| Accessibilità | WCAG 2.1 AA + axe-core automated audit |
| i18n | Solo EN al lancio. `.pot` completo. JSON translations. RTL support. |
| Documentazione | Help tabs + Documentation page in admin + tooltip inline + link esterni |
| Distribuzione | WP.org free (utente deve avere subscription RapidAPI personale all'API upstream) |

---

## Fasi di implementazione

Ordine sequenziale. Effort stimato: ~55 giorni-uomo (1 dev senior FT, ~11 settimane).

| # | File | Titolo | Effort |
|---|---|---|---|
| **F0** | [F0-bootstrap.md](F0-bootstrap.md) | Cleanup & Bootstrap (composer, wp-env, PSR-4, Plugin.php, Container) | M (2-3g) |
| **F0.5** | [F0.5-spike-interactivity.md](F0.5-spike-interactivity.md) | Spike Interactivity API (validazione early) | XS (0.5g) |
| **F1** | [F1-core-data-layer.md](F1-core-data-layer.md) | Enums, DTO, Repository, CPT, caps, encryption | M (3-4g) |
| **F2** | [F2-services-http.md](F2-services-http.md) | ApiClient, GeonamesClient, SchoolPresets, HooksRegistry | M (3-4g) |
| **F3** | [F3-rest-api.md](F3-rest-api.md) | AbstractController + tutti i controller REST | L (5-6g) |
| **F4** | [F4-admin-ui.md](F4-admin-ui.md) | Settings React + Setup wizard + Help tabs + Docs page | L (6-7g) |
| **F5** | [F5-gutenberg-blocks.md](F5-gutenberg-blocks.md) | 22 blocks + Patterns + Variations + FSE + Bindings | XL (8-10g) |
| **F6** | [F6-frontend-interactivity.md](F6-frontend-interactivity.md) | Stores Interactivity + chart display + city autocomplete | L (6-8g) |
| **F7** | [F7-cron-cli.md](F7-cron-cli.md) | Cron handlers + WP-CLI + email reminder | M (4g) |
| **F8** | [F8-i18n-a11y-docs.md](F8-i18n-a11y-docs.md) | `.pot`, JSON translations, axe audit, docs markdown | M (3-4g) |
| **F9** | [F9-testing-qa.md](F9-testing-qa.md) | Suite test completa, Plugin Check, coverage report | L (5-7g) |
| **F10** | [F10-release-prep.md](F10-release-prep.md) | Version bump, screenshots, ZIP, submit WP.org | S (1-2g) |

### Checkpoint decisionali

1. **Post F0**: conferma `@wordpress/scripts` vs Vite → raccomandato `@wordpress/scripts`.
2. **Post F0.5**: Interactivity API spike OK? Se fallisce → fallback React frontend (riduce F6 ~40%).
3. **Post F3**: JSON schema `additionalProperties: false` vs passthrough (rischio breaking quando upstream aggiunge campi).
4. **Fine F5**: 22 blocks troppi per WP.org review? Eventualmente super-block con variations.
5. **Fine F7**: email reminder solar return core v1.0 o scope-cut a v1.1?
6. **Pre F10**: review readme.txt con terzo occhio prima del submit WP.org.

---

## Architettura riassunto

### Namespace PHP (PSR-4)

```
Astrologer\Api\
├── Plugin                     # Bootstrap
├── Container                  # Service container leggero
├── Activation\                # Activator, Deactivator, Uninstaller
├── Enums\                     # HouseSystem, ZodiacType, School, UILevel, ...
├── ValueObjects\              # BirthData, GeoLocation, ChartOptions (readonly)
├── DTO\                       # *RequestDTO, *ResponseDTO
├── PostType\                  # AstrologerChartPostType
├── Repository\                # SettingsRepository, ChartRepository, BirthDataRepository
├── Capabilities\              # CapabilityManager
├── Support\
│   ├── Encryption\            # EncryptionService (sodium)
│   ├── i18n\                  # ScriptTranslations
│   ├── Svg\                   # SvgSanitizer (wp_kses allowlist)
│   └── Contracts\             # Bootable
├── Http\                      # ApiClient, GeonamesClient
├── Services\                  # ChartService, SchoolPresetsService, HooksRegistry, RateLimiter
├── Rest\                      # AbstractController, Schemas, Controllers
├── Admin\                     # AdminMenu, SettingsPage, SetupWizardPage, HelpTabsProvider, DocumentationPage
├── Blocks\                    # BlocksRegistry, Patterns, Variations, FseTemplates, Bindings
├── Frontend\                  # AssetEnqueuer, TemplatesLocator
├── Cron\                      # CronRegistry, Handlers
└── Cli\                       # AstrologerCommand, Commands
```

### Capability map default

| Capability | Admin | Editor | Author | Subscriber | Guest |
|---|---|---|---|---|---|
| `astrologer_manage_settings` | ✓ | | | | |
| `astrologer_calculate_chart` | ✓ | ✓ | ✓ | ✓ | (filterable) |
| `astrologer_save_chart` | ✓ | ✓ | ✓ | ✓ | |
| `astrologer_view_any_chart` | ✓ | | | | |
| `astrologer_run_cli` | ✓ | | | | |

### Hooks pubblici (nomenclatura `astrologer_api/...`)

Actions: `before_chart_request`, `after_chart_response`, `chart_saved`, `settings_updated`, `cron_before_tick`, `cron_after_tick`, `setup_wizard_completed`.

Filters: `chart_request_args`, `chart_response`, `settings_defaults`, `cpt_args`, `capability_map`, `rest_endpoint_args`, `block_attributes_defaults`, `school_preset`, `rate_limit_per_minute`, `svg_allowed_tags`.

---

## Regole trasversali (valide in ogni fase)

1. **Archivia `_legacy/` prima di sovrascrivere**: mai cancellare la bozza originale, serve come spec grezza.
2. **Ogni fase deve essere demoable**: al termine si dimostra funzionante in `wp-env`.
3. **Test shift-left**: ogni fase scrive i propri test (unit + integration). F9 consolida + Plugin Check.
4. **Commit atomici**: uno per task, mai "while I'm here" cleanup.
5. **Mai `git push`**, mai `--no-verify`, mai `git reset --hard` senza approvazione umana.
6. **Mai modificare `PLAN/*.md`** durante l'esecuzione: sono spec immutabili per la v1.0.
7. **UI in inglese, commenti in inglese**, code style consistente con file esistenti.
8. **Niente librerie nuove** senza giustificazione dentro al PLAN (no feature creep).

---

## Verifica finale end-to-end

Dopo F10:

```bash
cd /Users/giacomo/dev/astrologerwp
make up                     # wp-env on 8888
make build                  # wp-scripts build + composer dump-autoload
make test:all               # PHPUnit + Jest + Playwright + axe
make lint                   # phpcs + phpstan + eslint + stylelint
wp plugin install ./build/astrologer-api.zip --activate
```

Scenari accettazione — vedi dettaglio per ogni fase.

---

## File di progresso

`PROGRESS.md` (creato in root dalla prima iterazione Ralph) traccia lo stato di ogni task con checkbox `[ ] [~] [x] [!] [?]`. Vedi `PROMPT.md` per il protocollo.
