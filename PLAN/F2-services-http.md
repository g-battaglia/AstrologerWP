# F2 — Services & HTTP clients

> **Theme:** Client HTTP verso RapidAPI + GeoNames, service orchestratore, preset scuole astrologiche, registro hooks pubblici, rate limiter. Nessuna UI, nessuna REST ancora.
> **Effort:** M (3-4 giorni)
> **Dipendenze:** F1

---

## Obiettivo

Al termine, chiamando direttamente un service via container si ottiene una risposta reale dall'API upstream:

```php
$service = astrologer_api_container()->get(\Astrologer\Api\Services\ChartService::class);
$response = $service->natalChart($chartRequestDto);
echo $response->svg; // SVG reale dell'API
```

Tutti i chiamanti successivi (REST, WP-CLI, Cron) useranno questi service — nessuno farà `wp_remote_post` diretto.

---

## Prerequisiti

- F1 completato, DTO e Enums disponibili.
- Leggi `/Users/giacomo/dev/astrologer-api/openapi.json` o `ENDPOINTS.md` per lo schema esatto dei payload upstream.

---

## Tasks

### F2.1 — ApiClient (RapidAPI proxy)

**File:** `src/Http/ApiClient.php`

Classe con dipendenza injection di `SettingsRepository`.

Responsabilità:
- Costruisce request verso `{api_base_url}{endpoint}` (default `https://astrologer.p.rapidapi.com`).
- Headers: `X-RapidAPI-Host: astrologer.p.rapidapi.com`, `X-RapidAPI-Key: {decrypted-key}`, `Content-Type: application/json`.
- Timeout 15s.
- Retry esponenziale su `5xx` e connection errors (max 2 retry, backoff 500ms * 2^attempt).
- Error mapping: risposta 422 → `WP_Error('validation_failed', ...)`, 429 → `WP_Error('rate_limited', ...)`, 401/403 → `WP_Error('auth_failed', ...)`, 5xx → `WP_Error('upstream_error', ...)`, default → `WP_Error('unknown_error', ...)`.
- Error messages sanitizzati: `wp_strip_all_tags` + troncamento 200 char.

Metodi:
- `post(string $endpoint, array $payload): array|WP_Error`
- `get(string $endpoint, array $query = []): array|WP_Error`

Hooks:
- `apply_filters('astrologer_api/http_request_args', array $args, string $endpoint): array` — permette override timeout, headers, ecc.
- `do_action('astrologer_api/before_http_request', string $endpoint, array $payload)`
- `do_action('astrologer_api/after_http_response', string $endpoint, array|WP_Error $response)`

**Test fixture pattern:** in test, intercettare via `add_filter('pre_http_request', ...)` e restituire JSON fixture da `tests/fixtures/api/*.json`.

### F2.2 — GeonamesClient

**File:** `src/Http/GeonamesClient.php`

Wrapper per `http://api.geonames.org/searchJSON` (plaintext HTTP ammesso da GeoNames, ma usare `https://secure.geonames.org/searchJSON` come default).

Metodi:
- `search(string $query, int $maxRows = 10, ?string $lang = 'en'): array|WP_Error` → lista risultati con `{ name, countryCode, lat, lng, timezone, population }`.
- `timezone(float $lat, float $lng): array|WP_Error` → lookup timezone da coordinates.

Username da `SettingsRepository::get('geonames_username')`. Se vuoto, ritorna `WP_Error('geonames_not_configured', ...)`.

Filter: `apply_filters('astrologer_api/geonames_request_args', ...)`.

### F2.3 — ChartService (orchestratore)

**File:** `src/Services/ChartService.php`

Dipende da `ApiClient`, `SettingsRepository`, `HooksRegistry`.

Metodi — uno per tipo di chart (mappati a endpoint upstream):
- `subject(SubjectDTO $dto): ChartResponseDTO|WP_Error` → `/api/v5/subject`
- `nowSubject(NowRequestDTO $dto): ChartResponseDTO|WP_Error` → `/api/v5/now/subject`
- `birthChartData(ChartRequestDTO $dto): ChartResponseDTO|WP_Error` → `/api/v5/chart-data/birth-chart`
- `birthChart(ChartRequestDTO $dto): ChartResponseDTO|WP_Error` → `/api/v5/chart/birth-chart`
- `synastryChartData(SynastryRequestDTO $dto): ChartResponseDTO|WP_Error`
- `synastryChart(SynastryRequestDTO $dto): ChartResponseDTO|WP_Error`
- `compatibilityScore(CompatibilityRequestDTO $dto): ChartResponseDTO|WP_Error`
- `transitChartData(...)`, `transitChart(...)`
- `compositeChartData(...)`, `compositeChart(...)`
- `solarReturnChartData(...)`, `solarReturnChart(...)`
- `lunarReturnChartData(...)`, `lunarReturnChart(...)`
- `nowChart(...)`
- `moonPhase(MoonPhaseRequestDTO $dto)`, `moonPhaseContext(...)`, `moonPhaseNowUtc(...)`, `moonPhaseNowUtcContext(...)`
- Context endpoints: `subjectContext(...)`, `birthChartContext(...)`, `synastryContext(...)`, `compositeContext(...)`, `transitContext(...)`, `solarReturnContext(...)`, `lunarReturnContext(...)`, `nowContext(...)`
- `mcp(array $jsonRpcPayload): array|WP_Error` → proxy JSON-RPC 2.0 stateless
- `health(): array|WP_Error` → `/health` upstream

Ogni metodo:
1. Fire `do_action('astrologer_api/before_chart_request', string $chartType, array $payload)`.
2. `apply_filters('astrologer_api/chart_request_args', array $payload, string $chartType): array`.
3. Call `ApiClient->post()` / `->get()`.
4. Se error → return `WP_Error`, fire `do_action('astrologer_api/chart_request_failed', ...)`.
5. Fire `do_action('astrologer_api/after_chart_response', string $chartType, ChartResponseDTO $response)`.
6. `apply_filters('astrologer_api/chart_response', ChartResponseDTO $response, string $chartType): ChartResponseDTO`.

### F2.4 — SchoolPresetsService

**File:** `src/Services/SchoolPresetsService.php`

4 preset immutabili come metodi statici che ritornano `ChartOptions`:

**Modern Western (default):**
- `houseSystem: Placidus`, `zodiacType: Tropical`, `perspective: Apparent Geocentric`
- `activePoints`: Sun, Moon, Mercury, Venus, Mars, Jupiter, Saturn, Uranus, Neptune, Pluto, True_North_Lunar_Node, True_South_Lunar_Node, Chiron, Mean_Lilith, Ascendant, Medium_Coeli, Descendant, Imum_Coeli
- `activeAspects`: conjunction (8°), opposition (8°), trine (8°), square (8°), sextile (6°), quincunx (2°)
- `theme: classic`, `style: classic`

**Traditional / Hellenistic:**
- `houseSystem: EqualWholeSign`, `zodiacType: Tropical`
- `activePoints`: Sun, Moon, Mercury, Venus, Mars, Jupiter, Saturn, True_North_Lunar_Node, True_South_Lunar_Node, Pars_Fortunae, Pars_Spiritus, Ascendant, Medium_Coeli
- `activeAspects`: solo maggiori (conjunction, opposition, trine, square, sextile), orbs stretti (6° major, 3° sextile).

**Vedic / Jyotish:**
- `houseSystem: EqualWholeSign`, `zodiacType: Sidereal`, `siderealMode: LAHIRI`
- `activePoints`: Sun, Moon, Mercury, Venus, Mars, Jupiter, Saturn, Mean_North_Lunar_Node (Rahu), Mean_South_Lunar_Node (Ketu), Ascendant
- `activeAspects`: conjunction (10°), opposition (10°), trine (10°), square (10°), sextile (6°) — orbs più larghi per drishti.

**Uranian / Hamburg:**
- `houseSystem: Meridian`, `zodiacType: Tropical`, `perspective: Apparent Geocentric`
- `activePoints`: Sun, Moon, Mercury, Venus, Mars, Jupiter, Saturn, Uranus, Neptune, Pluto, Mean_North_Lunar_Node, + 8 ipotetici (Cupido, Hades, Zeus, Kronos, Apollon, Admetos, Vulkanus, Poseidon).
- `activeAspects`: conjunction (1°), semi-square (0.5°), square (1°), sesquiquadrate (0.5°), opposition (1°) — orbs molto stretti tipici Uranian.

Metodi:
- `get(School $school): ChartOptions`
- `all(): array<string, ChartOptions>`
- `merge(School $school, array $overrides): ChartOptions` — applica preset + override utente.

Filter: `apply_filters('astrologer_api/school_preset', ChartOptions $options, School $school): ChartOptions` (per personalizzare singolo preset).

### F2.5 — HooksRegistry

**File:** `src/Services/HooksRegistry.php`

Classe documentazione-centrica: elenca tutti gli hooks pubblici con commenti PHPDoc completi (parametri, return, esempio uso). Non fa `do_action`/`apply_filters` direttamente — è l'indice documentale.

Usata da:
- `Admin\DocumentationPage` (F4/F8) per generare pagina "Hooks Reference" automaticamente.
- `Cli\Commands\DoctorCommand` (F7) per ispezionare hook registrati.

Struttura:

```php
final class HooksRegistry {
    /** @return list<ActionDef> */
    public function actions(): array {
        return [
            new ActionDef('astrologer_api/before_chart_request', ['string $chartType', 'array $payload'], 'Fired before a chart API call.'),
            new ActionDef('astrologer_api/after_chart_response', ['string $chartType', 'ChartResponseDTO $response'], 'Fired after successful API call.'),
            new ActionDef('astrologer_api/chart_saved', ['int $postId', 'ChartRequestDTO $dto', 'int $userId'], 'Fired when a chart is persisted to CPT.'),
            new ActionDef('astrologer_api/settings_updated', ['array $newSettings', 'array $oldSettings'], 'Fired after settings save.'),
            new ActionDef('astrologer_api/cron_before_tick', ['string $cronName'], 'Before cron handler executes.'),
            new ActionDef('astrologer_api/cron_after_tick', ['string $cronName', 'array $stats'], 'After cron handler executes.'),
            new ActionDef('astrologer_api/setup_wizard_completed', ['array $wizardData'], 'Fired when user finishes setup wizard.'),
            // ...
        ];
    }

    /** @return list<FilterDef> */
    public function filters(): array {
        return [
            new FilterDef('astrologer_api/chart_request_args', 'array', ['array $payload', 'string $chartType'], 'Modify outgoing payload.'),
            new FilterDef('astrologer_api/chart_response', 'ChartResponseDTO', ['ChartResponseDTO $response', 'string $chartType'], 'Modify API response before return.'),
            new FilterDef('astrologer_api/settings_defaults', 'array', ['array $defaults'], 'Modify default settings values.'),
            new FilterDef('astrologer_api/cpt_args', 'array', ['array $args'], 'Modify CPT registration args.'),
            new FilterDef('astrologer_api/capability_map', 'array', ['array $map'], 'Modify role → capabilities mapping.'),
            new FilterDef('astrologer_api/rest_endpoint_args', 'array', ['array $args', 'string $routeName'], 'Modify REST route args.'),
            new FilterDef('astrologer_api/block_attributes_defaults', 'array', ['array $attrs', 'string $blockName'], 'Modify block default attributes.'),
            new FilterDef('astrologer_api/school_preset', 'ChartOptions', ['ChartOptions $options', 'School $school'], 'Modify a preset.'),
            new FilterDef('astrologer_api/rate_limit_per_minute', 'int', ['int $limit', 'string $endpoint', 'int $userId'], 'Modify rate limit.'),
            new FilterDef('astrologer_api/svg_allowed_tags', 'array', ['array $tags'], 'Extend SVG sanitizer allowlist.'),
            new FilterDef('astrologer_api/http_request_args', 'array', ['array $args', 'string $endpoint'], 'Modify wp_remote_post args.'),
            // ...
        ];
    }
}
```

Aggiungi VO helper `ActionDef` e `FilterDef` in `src/Services/` (readonly).

### F2.6 — RateLimiter

**File:** `src/Services/RateLimiter.php`

Transient-based, per-IP + per-user.

Metodi:
- `check(string $bucket, int $userId, string $ip, int $limit = 60, int $windowSec = 60): bool` → true se consentito, false se rate-limited.
- `reset(string $bucket, int $userId, string $ip): void`

Transient key: `astrologer_rl_{bucket}_{md5(ip|user)}` TTL = window.

Admin (`manage_options`) exempt: true.

Hook: `apply_filters('astrologer_api/rate_limit_per_minute', int $limit, string $endpoint, int $userId): int`.

IP detection priority (con filter `astrologer_api/client_ip`):
1. `HTTP_CF_CONNECTING_IP` (Cloudflare)
2. `HTTP_X_FORWARDED_FOR` (primo)
3. `HTTP_X_REAL_IP`
4. `REMOTE_ADDR`

### F2.7 — SvgSanitizer

**File:** `src/Support/Svg/SvgSanitizer.php`

Sanitizza SVG upstream prima dell'injection in DOM:
- Allowlist tags: `svg`, `g`, `path`, `circle`, `ellipse`, `line`, `polyline`, `polygon`, `rect`, `text`, `tspan`, `defs`, `use`, `title`, `desc`, `style`, `linearGradient`, `radialGradient`, `stop`, `clipPath`, `mask`, `filter`, `pattern`, `symbol`.
- Allowlist attrs: `viewBox`, `xmlns`, `width`, `height`, `fill`, `stroke`, `stroke-width`, `transform`, `d`, `x`, `y`, `cx`, `cy`, `r`, `rx`, `ry`, `x1`, `y1`, `x2`, `y2`, `points`, `class`, `id`, `font-family`, `font-size`, `text-anchor`, `dy`, `opacity`, `style`.
- Strip `<script>`, `on*` event handlers, `javascript:` URLs, external refs `href="http..."` (tranne `#anchor`).
- Usa `wp_kses` con custom allowlist.

Metodi:
- `sanitize(string $svg): string`

Filter: `apply_filters('astrologer_api/svg_allowed_tags', array $tags): array`, `apply_filters('astrologer_api/svg_allowed_attrs', array $attrs): array`.

---

## Criterio demoable

Imposta `rapidapi_key` via `SettingsRepository`, poi via `wp shell`:

```php
$service = astrologer_api_container()->get(\Astrologer\Api\Services\ChartService::class);

$subject = new \Astrologer\Api\DTO\SubjectDTO(
    name: 'Test',
    year: 1990, month: 5, day: 15, hour: 14, minute: 30,
    location: new \Astrologer\Api\ValueObjects\GeoLocation(
        latitude: 41.9028, longitude: 12.4964, timezone: 'Europe/Rome'
    ),
);

$dto = new \Astrologer\Api\DTO\ChartRequestDTO(
    subject: $subject,
    options: \Astrologer\Api\Services\SchoolPresetsService::modernWestern(),
    type: \Astrologer\Api\Enums\ChartType::Natal,
);

$response = $service->birthChart($dto);

echo substr($response->svg, 0, 200); // SVG reale
var_dump($response->aspects[0]);
```

Test retry: aggiungere filter che simula 503:
```php
add_filter('pre_http_request', function($pre, $args, $url) {
    static $attempts = 0;
    if ($attempts++ < 2) return ['response' => ['code' => 503], 'body' => ''];
    return false; // passa alla vera chiamata
}, 10, 3);
```

Verifica log che ci sono stati 2 retry e poi success.

---

## Test da scrivere

- `tests/Unit/Services/SchoolPresetsServiceTest.php`: 4 preset hanno active_points attesi, filter `school_preset` override funziona.
- `tests/Unit/Services/RateLimiterTest.php`: check incrementa counter, 61° chiamata false, reset azzera.
- `tests/Unit/Support/Svg/SvgSanitizerTest.php`: strip `<script>`, `on*`, `javascript:`, preserva SVG valido.
- `tests/Integration/Http/ApiClientTest.php`: usa `pre_http_request` filter per mockare upstream. Scenari: 200 OK, 422 validation, 429 rate limit, 5xx con retry, auth failure.
- `tests/Integration/Services/ChartServiceTest.php`: orchestrator fires hooks in right order, payload trasformato da filter.

---

## Hook pubblici introdotti (novità oltre F1)

- `apply_filters('astrologer_api/http_request_args', ...)`
- `apply_filters('astrologer_api/geonames_request_args', ...)`
- `apply_filters('astrologer_api/chart_request_args', ...)`
- `apply_filters('astrologer_api/chart_response', ...)`
- `apply_filters('astrologer_api/school_preset', ...)`
- `apply_filters('astrologer_api/rate_limit_per_minute', ...)`
- `apply_filters('astrologer_api/client_ip', ...)`
- `apply_filters('astrologer_api/svg_allowed_tags', ...)`
- `apply_filters('astrologer_api/svg_allowed_attrs', ...)`
- `do_action('astrologer_api/before_http_request', ...)`
- `do_action('astrologer_api/after_http_response', ...)`
- `do_action('astrologer_api/before_chart_request', ...)`
- `do_action('astrologer_api/after_chart_response', ...)`
- `do_action('astrologer_api/chart_request_failed', ...)`

---

## Note

- **Nessun caching** automatico. Se futuro sviluppatore vuole cache, lo implementa via hook `astrologer_api/chart_response` esternamente.
- `ApiClient` non sa nulla di enum o preset: riceve solo array. `ChartService` converte DTO → payload.
- Se `ASTROLOGER_ENCRYPTION_KEY` non è settato e non c'è `AUTH_KEY`, `EncryptionService` fallback su salt generato → log `Warning` in dev, OK in prod.

**Commit pattern:** `feat(http): ApiClient with retry and WP_Error mapping`, `feat(http): GeonamesClient`, `feat(services): ChartService orchestrator`, `feat(services): SchoolPresetsService 4 presets`, `feat(services): HooksRegistry documentation index`, `feat(services): RateLimiter transient-based`, `feat(security): SvgSanitizer`.
