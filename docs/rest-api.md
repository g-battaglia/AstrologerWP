# REST API

All plugin endpoints live under the `astrologer/v1` namespace (`/wp-json/astrologer/v1/…`). Requests require a logged-in user with the `astrologer_calculate_chart` capability (or `manage_options`) and the usual `X-WP-Nonce` header for cookie-based auth.

Every response includes an `X-Astrologer-Rate-Remaining` header so clients can self-throttle. The limit is configurable through the `astrologer_api/rate_limit_per_minute` filter and defaults to 60 requests per minute per user/IP bucket.

## Chart endpoints

Each chart endpoint accepts a POST body containing the subject(s) and a `chart_options` object. Responses are a `ChartResponseDTO` (positions, aspects, houses, optional SVG and AI context). A full endpoint list follows:

| Method | Path | Purpose |
|--------|------|---------|
| `POST` | `/natal-chart` | Basic natal wheel, sidereal/tropical configurable. |
| `POST` | `/birth-chart` | Extended natal report (same DTO, richer body). |
| `POST` | `/synastry-chart` | Bi-wheel for two subjects. |
| `POST` | `/synastry-aspects` | Cross-aspect grid only (lighter payload). |
| `POST` | `/transit-chart` | Natal + transit datetime. |
| `POST` | `/composite-chart` | Midpoint or Davison composite. |
| `POST` | `/solar-return-chart` | Natal subject + return year. |
| `POST` | `/lunar-return-chart` | Natal subject + target return datetime. |
| `POST` | `/now-chart` | Current sky for a location. |
| `POST` | `/relationship-score` | Heuristic relationship score. |

## Moon phase endpoints

| Method | Path | Purpose |
|--------|------|---------|
| `POST` | `/moon-phase/current` | Current moon phase at observer coordinates. |
| `POST` | `/moon-phase/at` | Phase at a specific ISO datetime. |
| `POST` | `/moon-phase/range` | Phases between `start_date` and `end_date`. |
| `POST` | `/moon-phase/next/{phase}` | Next occurrence of `new` / `first-quarter` / `full` / `last-quarter`. |

## Context endpoints

`/context/*` endpoints return prose summaries suitable for downstream LLM prompts. Subjects supported: `subject`, `natal`, `synastry`, `transit`, `composite`, `solar-return`, `lunar-return`, `moon-phase`.

```
POST /context/natal
POST /context/synastry
POST /context/transit
POST /context/composite
POST /context/solar-return
POST /context/lunar-return
POST /context/moon-phase
POST /context/subject
```

## Persistence & settings

| Method | Path | Purpose |
|--------|------|---------|
| `GET` | `/charts` | Paginated list of charts the current user can access. |
| `POST` | `/charts` | Persist a calculated chart as an `astrologer_chart` post. |
| `GET` | `/charts/{id}` | Fetch a single chart. |
| `DELETE` | `/charts/{id}` | Remove a chart. |
| `POST` | `/charts/{id}/recalculate` | Re-run the underlying computation for an existing chart. |
| `GET` | `/birth-data` | List saved birth subjects for the current user. |
| `POST` | `/birth-data` | Create a new birth subject. |
| `GET` | `/settings` | Read all settings (sensitive fields masked). |
| `POST` | `/settings` | Write settings. |
| `POST` | `/settings/test-connection` | Validate the configured RapidAPI key. |
| `GET` | `/bindings/fields` | Enumerate block-binding fields for the editor. |

## Ancillary endpoints

| Method | Path | Purpose |
|--------|------|---------|
| `GET` | `/health` | Upstream health probe — used by the `doctor` CLI and admin dashboard. |
| `POST` | `/mcp` | JSON-RPC bridge for Model-Context-Protocol clients. |
| `GET` | `/geonames/search` | Proxy GeoNames location search. |
| `GET` | `/geonames/timezone` | Resolve a timezone for a lat/lng pair. |

## Error shape

Unsuccessful requests return a JSON envelope with `code`, `message`, and an HTTP status. Rate-limit violations return HTTP 429 with code `rest_rate_limited`; permission failures return HTTP 403 with code `rest_forbidden`.

See `src/Rest/Controllers/` for the canonical source of every schema; `src/Rest/AbstractController.php` contains the shared permission, rate-limit, and response helpers.
