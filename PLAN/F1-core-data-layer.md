# F1 — Core Data Layer

> **Theme:** Fondazioni tipizzate: Enums, Value Objects, DTO, Repository, CPT, user meta, custom capabilities, EncryptionService. Nessuna UI.
> **Effort:** M (3-4 giorni)
> **Dipendenze:** F0

---

## Obiettivo

Spina dorsale del plugin: persistenza settings criptata, CPT `astrologer_chart`, user meta `astrologer_birth_data`, custom capabilities registrate, enums e DTO riusabili ovunque.

Al termine, via `wp shell` devi poter:
1. Salvare una API key → la trovi criptata in `wp_options` (non leggibile a occhio).
2. Leggere la stessa API key via `SettingsRepository` → ottieni il plaintext.
3. Creare un post `astrologer_chart` privato via `ChartRepository::create($dto)`.
4. Verificare che il ruolo `administrator` ha tutte le custom capabilities, mentre `subscriber` ha solo `astrologer_calculate_chart`.

---

## Prerequisiti

- F0 completato. Composer autoload funzionante.
- `astrology-enums.json` (archiviato in `_legacy/astrology-enums.json`) letto per estrarre elenco 43 points + 11 aspects.
- Leggi `/Users/giacomo/dev/astrologer-api/openapi.json` (o ENDPOINTS.md) per i valori esatti degli enum upstream (house systems, sidereal modes, perspective types).

---

## Tasks

### F1.1 — Enums

**File** (tutti in `src/Enums/`):

| Enum | Backing | Cases |
|---|---|---|
| `HouseSystem` | string (single char) | `Alcabitius='B'`, `Campanus='C'`, `EqualC='D'`, `EqualAsc='A'`, `EqualVehlow='V'`, `EqualWholeSign='W'`, `EqualAries='N'`, `Carter='F'`, `Horizon='H'`, `Koch='K'`, `Krusinski='U'`, `Meridian='X'`, `Morinus='M'`, `Placidus='P'` (default), `PolichPage='T'`, `Porphyry='O'`, `PullenSD='L'`, `PullenSR='Q'`, `Regiomontanus='R'`, `Sripati='S'`, `Sunshine='I'`, `SunshineAlt='i'`, `APC='Y'` |
| `ZodiacType` | string | `Tropical`, `Sidereal` |
| `SiderealMode` | string | 48+ valori: `FAGAN_BRADLEY`, `LAHIRI`, `LAHIRI_1940`, `LAHIRI_ICRC`, `LAHIRI_VP285`, `KRISHNAMURTI`, `KRISHNAMURTI_VP291`, `RAMAN`, `USHASHASHI`, `JN_BHASIN`, `YUKTESHWAR`, `ARYABHATA`, `ARYABHATA_522`, `ARYABHATA_MSUN`, `SURYASIDDHANTA`, `SURYASIDDHANTA_MSUN`, `SS_CITRA`, `SS_REVATI`, `TRUE_CITRA`, `TRUE_MULA`, `TRUE_PUSHYA`, `TRUE_REVATI`, `TRUE_SHEORAN`, `DELUCE`, `DJWHAL_KHUL`, `HIPPARCHOS`, `SASSANIAN`, `BABYL_KUGLER1`, `BABYL_KUGLER2`, `BABYL_KUGLER3`, `BABYL_HUBER`, `BABYL_ETPSC`, `BABYL_BRITTON`, `GALCENT_0SAG`, `GALCENT_COCHRANE`, `GALCENT_MULA_WILHELM`, `GALCENT_RGILBRAND`, `GALEQU_FIORENZA`, `GALEQU_IAU1958`, `GALEQU_MULA`, `GALEQU_TRUE`, `GALALIGN_MARDYKS`, `J2000`, `J1900`, `B1950`, `ALDEBARAN_15TAU`, `VALENS_MOON`, `USER` |
| `PerspectiveType` | string | `Apparent Geocentric` (default), `True Geocentric`, `Heliocentric`, `Topocentric`, `Selenocentric`, `Mercurycentric`, `Venuscentric`, `Marscentric`, `Jupitercentric`, `Saturncentric`, `Barycentric` |
| `ChartTheme` | string | `classic` (default), `dark`, `dark-high-contrast`, `light`, `strawberry`, `black-and-white` |
| `ChartStyle` | string | `classic` (default), `modern` |
| `DistributionMethod` | string | `weighted` (default), `pure_count` |
| `School` | string | `modern_western` (default), `traditional`, `vedic`, `uranian` |
| `UILevel` | string | `basic` (default), `advanced`, `expert` |
| `Language` | string | `EN` (default), `IT`, `FR`, `ES`, `PT`, `CN`, `RU`, `TR`, `DE`, `HI` |
| `ChartType` | string | `natal`, `synastry`, `transit`, `composite`, `solar_return`, `lunar_return`, `now`, `moon_phase`, `compatibility` |

**Nota:** ogni enum deve avere metodo `label(): string` per i18n friendly name (ritorna stringa wrappata in `__('Placidus', 'astrologer-api')` ecc.).

### F1.2 — Active Points + Aspects constants

**File:** `src/Enums/ActivePoint.php`

Enum stringa con ~80 cases da leggere dal `_legacy/astrology-enums.json` + aggiunte:
- 10 pianeti classici, 4 nodi lunari (MEAN/TRUE × NORTH/SOUTH), 4 Lilith variants, Priapus (Mean/True), White Moon, Earth, Chiron, Pholus.
- 4 asteroidi (Ceres, Pallas, Juno, Vesta).
- 8 trans-nettuniani (Eris, Sedna, Haumea, Makemake, Ixion, Orcus, Quaoar, Chaos).
- 8 ipotetici uraniani (Cupido, Hades, Zeus, Kronos, Apollon, Admetos, Vulkanus, Poseidon).
- 23 stelle fisse (Regulus, Spica, Aldebaran, Antares, Sirius, Fomalhaut, Algol, Betelgeuse, Canopus, Procyon, Arcturus, Pollux, Deneb, Altair, Rigel, Achernar, Capella, Vega, Alcyone, Alphecca, Algorab, Deneb_Algedi, Alkaid).
- 4 parti arabe (Pars_Fortunae, Pars_Spiritus, Pars_Amoris, Pars_Fidei).
- Angoli (Ascendant, Medium_Coeli, Descendant, Imum_Coeli, Vertex, Anti_Vertex).

**File:** `src/Enums/AspectType.php`

13 cases: `Conjunction`, `Semi_Sextile`, `Semi_Square`, `Sextile`, `Quintile`, `Square`, `Trine`, `Sesquiquadrate`, `Biquintile`, `Quincunx`, `Opposition`, `Parallel`, `Contra_Parallel`.

Metodi: `defaultOrb(): float` ritorna orb default per ogni aspetto (8° major, 6° sextile, 2° minor, 2° parallel).

### F1.3 — Value Objects readonly

**File:** `src/ValueObjects/BirthData.php`

```php
final readonly class BirthData {
    public function __construct(
        public string $name,
        public int $year,
        public int $month,
        public int $day,
        public int $hour,
        public int $minute,
        public GeoLocation $location,
        public ?string $isoDatetime = null,
    ) {
        // validate: 1 CE <= year <= 3000, 1 <= month <= 12, ...
    }
    public static function fromArray(array $data): self { /* ... */ }
    public function toArray(): array { /* ... */ }
}
```

**File:** `src/ValueObjects/GeoLocation.php`

```php
final readonly class GeoLocation {
    public function __construct(
        public float $latitude,   // -90..90
        public float $longitude,  // -180..180
        public string $timezone,  // IANA
        public ?float $altitude = null,
        public ?bool $isDst = null,
        public ?string $city = null,
        public ?string $nation = null, // ISO 3166-1 alpha-2
    ) { /* validate */ }
}
```

**File:** `src/ValueObjects/ChartOptions.php`

```php
final readonly class ChartOptions {
    public function __construct(
        public Language $language,
        public HouseSystem $houseSystem,
        public ZodiacType $zodiacType,
        public ?SiderealMode $siderealMode,
        public PerspectiveType $perspective,
        public ChartTheme $theme,
        public ChartStyle $style,
        /** @var list<ActivePoint> */ public array $activePoints,
        /** @var list<ActiveAspect> */ public array $activeAspects,
        public DistributionMethod $distributionMethod,
        public ?array $customDistributionWeights,
        public bool $splitChart,
        public bool $transparentBackground,
        public bool $showHousePositionComparison,
        public bool $showCuspPositionComparison,
        public bool $showDegreeIndicators,
        public bool $showAspectIcons,
        public bool $showZodiacBackgroundRing,
        public ?string $customTitle,
    ) {}
    public static function defaults(): self { /* sensible defaults */ }
}
```

**File:** `src/ValueObjects/ActiveAspect.php`

```php
final readonly class ActiveAspect {
    public function __construct(
        public AspectType $type,
        public float $orb,
    ) {}
}
```

### F1.4 — DTOs

**File:** `src/DTO/SubjectDTO.php`, `src/DTO/ChartRequestDTO.php`, `src/DTO/SynastryRequestDTO.php`, `src/DTO/TransitRequestDTO.php`, `src/DTO/CompositeRequestDTO.php`, `src/DTO/ReturnRequestDTO.php`, `src/DTO/NowRequestDTO.php`, `src/DTO/MoonPhaseRequestDTO.php`, `src/DTO/CompatibilityRequestDTO.php`, `src/DTO/ChartResponseDTO.php`.

Tutti readonly. Metodi `fromArray()` e `toArray()` per marshalling REST.

### F1.5 — Encryption service

**File:** `src/Support/Encryption/EncryptionService.php`

Responsabilità:
- Recupera chiave da `ASTROLOGER_ENCRYPTION_KEY` constant. Se assente, fallback deterministico da `AUTH_KEY` + salt persistente in option `astrologer_api_encryption_salt` (generato on-first-use con `random_bytes(32)`).
- Metodi:
  - `encrypt(string $plaintext): string` ritorna base64(nonce|ciphertext) via `sodium_crypto_secretbox`.
  - `decrypt(string $ciphertext): ?string` null se decryption fallisce (wrong key, tampered).
  - `isAvailable(): bool` true se libsodium disponibile (sempre su PHP 8.1+).

**Gotcha:** se `AUTH_KEY` cambia o il salt è perso, decryption fallisce → mostrare admin notice "API key lost, please re-enter" (gestito in F4).

### F1.6 — Settings Repository

**File:** `src/Repository/SettingsRepository.php`

Wrapper su `wp_options` con key `astrologer_api_settings` (JSON encoded). Campi sensibili (`rapidapi_key`, `geonames_username`) cifrati via `EncryptionService`.

Metodi:
- `all(): array` → array associativo (chiavi sensibili decriptate)
- `get(string $key, mixed $default = null): mixed`
- `set(string $key, mixed $value): void`
- `update(array $partial): void`
- `reset(): void` → riporta ai default + applica filtro `apply_filters('astrologer_api/settings_defaults', [...])`
- `isConfigured(): bool` → true se rapidapi_key presente

Schema default:
```
rapidapi_key: ''
geonames_username: ''
api_base_url: 'https://astrologer.p.rapidapi.com'
language: 'EN'
school: 'modern_western'
ui_level: 'basic'
chart_options: ChartOptions::defaults()->toArray()
cron: { daily_transits: false, solar_return_reminder: false, daily_moon_phase: false }
integrations: { geonames_enabled: true }
```

### F1.7 — Custom Post Type

**File:** `src/PostType/AstrologerChartPostType.php`

Classe `Bootable`:
- `register_post_type('astrologer_chart', [...])` su `init`
- Args: `public: false`, `show_ui: true` (admin visibile), `show_in_rest: true` (per block bindings), `supports: ['title', 'author', 'custom-fields']`, `has_archive: false`, `rewrite: false`, `capability_type: ['astrologer_chart', 'astrologer_charts']` (custom caps!), `map_meta_cap: true`.
- **Privato di default**: imposta `post_status: 'private'` al create.
- Filtro `astrologer_api/cpt_args` per permettere override.

**File:** `src/PostType/ChartTypeTaxonomy.php` (opzionale v1.0, ma utile)

Registra taxonomy `astrologer_chart_type` con termini fissi: `natal`, `synastry`, `transit`, `composite`, `solar_return`, `lunar_return`, `now`.

### F1.8 — Chart Repository

**File:** `src/Repository/ChartRepository.php`

Metodi:
- `create(ChartRequestDTO $dto, int $userId, ?array $responseData = null): int` → crea post + meta, ritorna post_id
- `find(int $id): ?ChartRecord` → legge CPT + meta + ritorna VO `ChartRecord`
- `update(int $id, array $changes): void`
- `delete(int $id): bool`
- `listByUser(int $userId, array $args = []): array` → wrapper `WP_Query`
- `isOwner(int $postId, int $userId): bool`

Post meta registered via `register_post_meta()` con `show_in_rest: true` + schema:
- `chart_type` (string, enum di `ChartType::values()`)
- `birth_data` (object, schema BirthData)
- `chart_options` (object, schema ChartOptions)
- `response_svg` (string, sanitized)
- `response_data` (object)

### F1.9 — Birth Data Repository (user meta)

**File:** `src/Repository/BirthDataRepository.php`

Metodi:
- `getForUser(int $userId): ?BirthData`
- `setForUser(int $userId, BirthData $data): void`
- `clearForUser(int $userId): void`

User meta key: `astrologer_birth_data` (registered con `register_meta('user', ...)` con schema e `show_in_rest: true`).

### F1.10 — Capability Manager

**File:** `src/Capabilities/CapabilityManager.php`

Classe `Bootable`:
- Registra custom capabilities mappate ai ruoli (chiamato dall'Activator di F1.11).
- Capabilities:
  - `astrologer_manage_settings` → admin
  - `astrologer_calculate_chart` → admin, editor, author, contributor, subscriber
  - `astrologer_save_chart` → admin, editor, author, subscriber
  - `astrologer_view_any_chart` → admin
  - `astrologer_run_cli` → admin
- Map meta caps per CPT (`edit_astrologer_chart`, `edit_others_astrologer_charts`, ecc.) che risolvono ownership.
- Filtro `astrologer_api/capability_map` per override custom.

### F1.11 — Activation / Deactivation / Uninstall

**File:** `src/Activation/Activator.php`
- Registra CPT + taxonomy (per flush rewrite)
- Registra capabilities
- Genera salt encryption (se assente)
- Set option `astrologer_api_setup_wizard_pending` = true (usato in F4 per redirect wizard)
- `flush_rewrite_rules()`

**File:** `src/Activation/Deactivator.php`
- `flush_rewrite_rules()`
- Unschedule cron events (aggiunto in F7)
- **Non** eliminare capabilities né dati: quello è `Uninstaller`.

**File:** `src/Activation/Uninstaller.php`
- Elimina tutti i post `astrologer_chart` (loop + `wp_delete_post($id, true)`).
- Drop custom capabilities da tutti i ruoli.
- Delete option `astrologer_api_settings`.
- Delete option `astrologer_api_encryption_salt`.
- Delete user meta `astrologer_birth_data` per tutti gli utenti.
- Unschedule cron events.
- Multisite: foreach blog via `get_sites()`.

Collegare in `astrologer-api.php` via `register_activation_hook`, `register_deactivation_hook`, e `uninstall.php` richiama `Uninstaller::run()`.

---

## Criterio demoable

```bash
make up
wp @astrologer shell   # wp-env shell alias
```

Dentro la shell:

```php
$settings = astrologer_api_container()->get(\Astrologer\Api\Repository\SettingsRepository::class);
$settings->set('rapidapi_key', 'my-secret-key-123');

// Verifica nel DB: la option contiene un blob criptato, non "my-secret-key-123" in chiaro.
echo get_option('astrologer_api_settings')['rapidapi_key']; // base64 encrypted
echo $settings->get('rapidapi_key'); // 'my-secret-key-123'

// CPT
$repo = astrologer_api_container()->get(\Astrologer\Api\Repository\ChartRepository::class);
$dto = new \Astrologer\Api\DTO\ChartRequestDTO(
    subject: new \Astrologer\Api\DTO\SubjectDTO(...),
    options: \Astrologer\Api\ValueObjects\ChartOptions::defaults(),
    type: \Astrologer\Api\Enums\ChartType::Natal,
);
$postId = $repo->create($dto, get_current_user_id());
$record = $repo->find($postId);
var_dump($record->chartType); // ChartType::Natal
```

Verifica ruoli:
```bash
wp @astrologer role list --fields=name,capabilities | grep astrologer
```

---

## Test da scrivere

- `tests/Unit/Enums/HouseSystemTest.php`: valori, label i18n.
- `tests/Unit/ValueObjects/BirthDataTest.php`: validation range, fromArray/toArray roundtrip.
- `tests/Unit/ValueObjects/ChartOptionsTest.php`: defaults, immutability.
- `tests/Unit/Support/Encryption/EncryptionServiceTest.php`: encrypt/decrypt roundtrip, null su tampered, fallback salt.
- `tests/Integration/Repository/SettingsRepositoryTest.php`: save/read con encryption, filter `astrologer_api/settings_defaults`.
- `tests/Integration/Repository/ChartRepositoryTest.php`: create/find/update/delete, privacy default.
- `tests/Integration/Capabilities/CapabilityManagerTest.php`: ogni ruolo ha le caps attese; map_meta_cap resolve ownership.

Target coverage F1: ≥80% su repositories + encryption + value objects.

---

## Hook pubblici introdotti

- `apply_filters('astrologer_api/settings_defaults', array $defaults): array`
- `apply_filters('astrologer_api/cpt_args', array $args): array`
- `apply_filters('astrologer_api/capability_map', array $map): array`
- `do_action('astrologer_api/chart_saved', int $postId, ChartRequestDTO $dto, int $userId)`

**Commit pattern:** `feat(data): enums + VOs`, `feat(data): EncryptionService`, `feat(data): SettingsRepository`, `feat(data): CPT astrologer_chart`, `feat(data): custom capabilities`.
