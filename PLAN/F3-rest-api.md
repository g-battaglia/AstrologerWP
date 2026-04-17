# F3 — REST API Layer

> **Theme:** Esporre tutti gli endpoint upstream + CPT CRUD + Settings via REST WP namespace `astrologer/v1`. Schema rigoroso, permission callback, rate limiting, nonce.
> **Effort:** L (5-6 giorni)
> **Dipendenze:** F2

---

## Obiettivo

Tutti gli endpoint dell'API upstream devono essere esposti via WordPress REST così che frontend blocks (Interactivity), admin React, WP-CLI, e integrazioni terze possano consumarli in modo uniforme.

Namespace: `astrologer/v1`.

Ogni controller:
- Estende `AbstractController`.
- Registra le rotte su `rest_api_init`.
- Dichiara schema JSON completo con `validate_callback` + `sanitize_callback`.
- Rispetta capability check via `permission_callback`.
- Applica rate limiting (`RateLimiter` da F2).
- Emette hooks pubblici (`astrologer_api/before_chart_request`, `astrologer_api/after_chart_response`).
- Ritorna `WP_REST_Response` con `X-Astrologer-Rate-Remaining` header.

---

## Prerequisiti

- F2 completato (`ApiClient`, `ChartService`, `RateLimiter` operativi).
- `docs/astrologer-api.json` letto per mappare campi upstream (è l'OpenAPI spec).

---

## Tasks

### F3.1 — AbstractController

**File:** `src/Rest/AbstractController.php`

```php
abstract class AbstractController {
    abstract public function register_routes(): void;

    protected function permission_check(WP_REST_Request $request, string $cap): bool|WP_Error {
        if (!current_user_can($cap)) {
            return new WP_Error('rest_forbidden', __('Insufficient permissions.', 'astrologer-api'), ['status' => 403]);
        }
        if (!$this->rate_limiter->is_allowed(get_current_user_id(), $request)) {
            return new WP_Error('rest_rate_limited', __('Too many requests.', 'astrologer-api'), ['status' => 429]);
        }
        return true;
    }

    protected function respond(mixed $data, int $status = 200, array $headers = []): WP_REST_Response {
        $response = new WP_REST_Response($data, $status);
        foreach ($headers as $name => $value) $response->header($name, $value);
        $response->header('X-Astrologer-Rate-Remaining', (string) $this->rate_limiter->remaining(get_current_user_id()));
        return $response;
    }

    protected function handle_service_error(WP_Error $error): WP_REST_Response {
        return $this->respond(['code' => $error->get_error_code(), 'message' => $error->get_error_message()], $error->get_error_data()['status'] ?? 500);
    }
}
```

Registra il controller in `RestServiceProvider` che bootstrappa tutti i controller su `rest_api_init`.

### F3.2 — Schemas condivisi

**File:** `src/Rest/Schemas/SubjectSchema.php`

Ritorna l'array JSON schema per il `subject` (name, year, month, day, hour, minute, longitude, latitude, city, nation, timezone, zodiac_type, sidereal_mode, perspective_type, houses_system_identifier).

**File:** `src/Rest/Schemas/ChartOptionsSchema.php`

Schema per active_points[], active_aspects[], theme, chart_language, wheel_only, theme.

**File:** `src/Rest/Schemas/GeoLocationSchema.php`

Schema per city, nation, latitude, longitude, timezone.

I singoli controller riusano questi schemas tramite `array_merge()`.

### F3.3 — ChartControllers (endpoint chart)

**File:** `src/Rest/Controllers/NatalChartController.php`

Rotta: `POST /astrologer/v1/natal-chart`

- Permission: `astrologer_calculate_chart`.
- Schema: `subject` (required) + chart options.
- Handler: deserializza in `ChartRequestDTO`, chiama `ChartService::natalChart()`, applica `astrologer_api/chart_response` filter, ritorna `['chart' => [...], 'svg' => '...', 'data' => [...]]`.

Stessa struttura per:
- `SynastryChartController` — `POST /synastry-chart`, 2 subjects.
- `TransitChartController` — `POST /transit-chart`, subject + transit_subject (transit può usare "now" come default con flag).
- `CompositeChartController` — `POST /composite-chart`.
- `SolarReturnChartController` — `POST /solar-return-chart`.
- `LunarReturnChartController` — `POST /lunar-return-chart`.
- `NowChartController` — `POST /now-chart` (subject-less, usa GPS/opzionale).
- `BirthChartController` — `POST /birth-chart` (alias natal + full data response).
- `BirthDataController` — `POST /birth-data` (solo positions, no SVG).

### F3.4 — Relationship & Compatibility

**File:** `src/Rest/Controllers/RelationshipScoreController.php` — `POST /relationship-score`
**File:** `src/Rest/Controllers/SynastryAspectsController.php` — `POST /synastry-aspects`

### F3.5 — Moon Phase

**File:** `src/Rest/Controllers/MoonPhaseController.php`

Quattro rotte:
- `GET /moon-phase/current` — stato corrente (cached via moon phase daily cache se feature attiva).
- `POST /moon-phase/at` — moon phase a una data specifica.
- `POST /moon-phase/range` — lista fasi in un range.
- `GET /moon-phase/next/{phase}` — prossima occorrenza di una fase specifica (new, first-quarter, full, last-quarter).

### F3.6 — AI Context endpoints

**File:** `src/Rest/Controllers/ContextController.php`

Otto rotte (namespace `/context/*`):
- `POST /context/subject`
- `POST /context/natal`
- `POST /context/synastry`
- `POST /context/transit`
- `POST /context/composite`
- `POST /context/solar-return`
- `POST /context/lunar-return`
- `POST /context/moon-phase`

Ogni rotta ritorna il contesto testuale strutturato per consumo LLM (formato YAML-like o JSON narrativo).

### F3.7 — MCP + Health

**File:** `src/Rest/Controllers/McpController.php` — `POST /mcp` (proxy all'endpoint MCP upstream, permission `astrologer_calculate_chart`).
**File:** `src/Rest/Controllers/HealthController.php` — `GET /health` (no auth required, stato cache-friendly 10s).

### F3.8 — Geonames

**File:** `src/Rest/Controllers/GeonamesController.php`

- `GET /geonames/search?q=...&limit=10` — permission `astrologer_calculate_chart` (anche guest, rate-limited). Per city autocomplete.
- `GET /geonames/timezone?lat=...&lng=...` — per lookup timezone esterno.

### F3.9 — Settings

**File:** `src/Rest/Controllers/SettingsController.php`

- `GET /settings` — permission `astrologer_manage_settings`. Ritorna settings tranne API key (mai esposta in GET, solo boolean `has_api_key`).
- `POST /settings` — permission `astrologer_manage_settings`. Accetta partial update, valida schema, salva via `SettingsRepository`.
- `POST /settings/test-connection` — permission `astrologer_manage_settings`. Prova chiamata `health` con API key in body (non persistita) per validare credenziali durante setup wizard.

### F3.10 — Chart CPT CRUD

**File:** `src/Rest/Controllers/ChartController.php`

- `GET /charts` — list charts dell'utente corrente (permission `astrologer_save_chart`). Query `type`, `per_page`, `search`.
- `GET /charts/{id}` — dettaglio. Permission check: autore o `astrologer_view_any_chart`.
- `POST /charts` — crea. Valida DTO + hash deduplication.
- `DELETE /charts/{id}` — soft delete (trash) o hard delete se `force=true`.
- `POST /charts/{id}/recalculate` — ricalcola chart salvando nuova versione (utile dopo fix upstream).

Tutte le operazioni triggerano hook `astrologer_api/chart_saved`.

### F3.11 — Bindings metadata

**File:** `src/Rest/Controllers/BindingsController.php`

- `GET /bindings/fields` — ritorna elenco dei campi disponibili per Block Bindings (es. `birth_data.name`, `chart.svg`, `aspects[0].aspect`), con label localizzate. Consumato dall'editor Gutenberg.

### F3.12 — Registration

**File:** `src/Rest/RestServiceProvider.php`

```php
class RestServiceProvider implements Bootable {
    public function boot(): void {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes(): void {
        foreach ($this->controllers as $controller) {
            $controller->register_routes();
        }
    }
}
```

Controller iniettati dal container con dipendenze risolte. `Plugin::boot()` aggiunge `RestServiceProvider` al container.

### F3.13 — Test integration

**File:** `tests/Integration/Rest/NatalChartControllerTest.php`

Con `wp-phpunit`:
1. `test_unauthenticated_user_gets_403()`
2. `test_authenticated_user_gets_chart()` (con ApiClient mockato via fixture)
3. `test_invalid_schema_returns_400()`
4. `test_rate_limit_returns_429_after_60_calls_in_a_minute()`

Ripetuto per almeno 3 controller rappresentativi. Altri controller coperti da smoke test in F9.

---

## Criterio di demoable

1. `curl -X POST http://localhost:8888/wp-json/astrologer/v1/natal-chart -H "Content-Type: application/json" -H "X-WP-Nonce: xxx" -d '{"subject":{...}}'` ritorna 200 con SVG.
2. Plugin Check: nessun warning su permission callback o schema.
3. Test integration verdi.

---

## Hooks introdotti

- Action `astrologer_api/before_chart_request` — passato `$dto`, `$endpoint`.
- Action `astrologer_api/after_chart_response` — passato `$response`, `$dto`, `$endpoint`.
- Action `astrologer_api/chart_saved` — passato `$chart_id`, `$chart_data`.
- Filter `astrologer_api/chart_request_args` — modifica args prima dell'invio.
- Filter `astrologer_api/chart_response` — modifica response prima del return REST.
- Filter `astrologer_api/rest_endpoint_args` — modifica schema (es. aggiungere `additionalProperties`).
- Filter `astrologer_api/rate_limit_per_minute` — override quota per user/role.

Tutti documentati in `src/Services/HooksRegistry.php`.

---

## Decisione open (Checkpoint #3)

`additionalProperties` strategia:
- **`false` strict**: sicuro ma rompe se upstream aggiunge campi.
- **`true` passthrough**: flessibile ma rischio data leak in response.

**Raccomandazione**: `true` con WP filter `astrologer_api/chart_response` per whitelist controllata. Da confermare con te a fine F3.
