# AstrologerWP - Complete Plugin Implementation Plan

## Overview

Upgrade AstrologerWP from a partial v4 integration to a **complete, production-ready** WordPress plugin covering **100% of the Astrologer API v5** surface. The plugin is the official companion for RapidAPI subscribers.

---

## Current State

| Feature | Status |
|---|---|
| API Version | **v4** (outdated) |
| Birth Chart | Implemented |
| Synastry Chart | Implemented |
| Transit Chart | Implemented |
| Composite Chart | Missing |
| Solar Return Chart | Missing |
| Lunar Return Chart | Missing |
| Moon Phase | Missing |
| Now Chart (current sky) | Missing |
| Compatibility Score | Missing |
| Advanced rendering options | Missing (style, split_chart, transparent_bg, etc.) |
| Chart themes | Partial (missing strawberry, black-and-white) |
| Chart data display (aspects, elements, qualities) | Missing |

---

## Implementation Tasks

### 1. Core Infrastructure Updates

#### 1.1 KerykeionConstants.php
- Add missing themes: `strawberry`, `black-and-white`
- Add `CHART_STYLES`: `['classic', 'modern']`
- Add `WHEEL_TYPES`: `['dual', 'single']`
- Add `ASPECT_GRID_TYPES`: `['list', 'table']`
- Update `CHART_TYPES` with: `Composite`, `SolarReturn`, `LunarReturn`

#### 1.2 Subject.php
- Add `second` field (int, default 0)
- Add `custom_ayanamsa_t0` field (optional float)
- Add `custom_ayanamsa_ayan_t0` field (optional float)
- Update `toArray()` to include all new fields

#### 1.3 AstrologerApiAdapter.php - Full Rewrite
- **Upgrade all endpoints from `/api/v4/` to `/api/v5/chart/`**
- Add **all rendering parameters** to every chart method:
  - `theme`, `language`, `style` (classic/modern)
  - `split_chart`, `transparent_background`
  - `show_house_position_comparison`, `show_cusp_position_comparison`
  - `show_degree_indicators`, `show_aspect_icons`
  - `show_zodiac_background_ring`
  - `double_chart_aspect_grid_type` (list/table)
  - `custom_title`
- Add **new methods**:
  - `getCompositeChart(firstSubject, secondSubject, ...renderingParams)`
  - `getSolarReturnChart(subject, year, month, day, wheelType, returnLocation, ...renderingParams)`
  - `getLunarReturnChart(subject, year, month, day, wheelType, returnLocation, ...renderingParams)`
  - `getMoonPhase(year, month, day, hour, minute, latitude, longitude, timezone)`
  - `getNowChart(...renderingParams)`
  - `getCompatibilityScore(firstSubject, secondSubject)`
- Update **response handling** for v5 format:
  - `chart_data` instead of separate `data`/`aspects`
  - Handle `chart_wheel` + `chart_grid` when `split_chart=true`
- Add synastry-specific params: `include_house_comparison`, `include_relationship_score`
- Add transit-specific params: `include_house_comparison`
- Refactor with private `makeChartRequest()` helper to reduce duplication

### 2. Admin Settings Expansion

#### 2.1 New Settings Fields
| Setting | Type | Default |
|---|---|---|
| `chart_style` | select (classic/modern) | classic |
| `split_chart` | checkbox | false |
| `transparent_background` | checkbox | false |
| `show_house_position_comparison` | checkbox | true |
| `show_cusp_position_comparison` | checkbox | true |
| `show_degree_indicators` | checkbox | true |
| `show_aspect_icons` | checkbox | true |
| `show_zodiac_background_ring` | checkbox | true |
| `double_chart_aspect_grid_type` | select (list/table) | list |

#### 2.2 Admin Page Updates
- Update shortcode recap with all 8 shortcodes
- Group settings into logical sections (API credentials, chart rendering, advanced options)

### 3. Update Existing Shortcodes

#### 3.1 Birth Chart
- Update to v5 response format (`chart_data`)
- Pass all new rendering parameters
- Display chart data: aspects table, element distribution, quality distribution

#### 3.2 Synastry Chart
- Update to v5 response format
- Add `include_house_comparison` and `include_relationship_score` support
- Display relationship score when available
- Display chart data tables

#### 3.3 Transit Chart
- Update to v5 response format
- Add `include_house_comparison` support
- Display chart data tables

### 4. New Shortcodes

#### 4.1 `[astrologer_wp_composite_chart]`
- Two-subject form (same layout as synastry)
- Composite (midpoint) chart generation

#### 4.2 `[astrologer_wp_solar_return_chart]`
- Subject birth data + return year selection
- Optional return location override
- Wheel type: dual/single

#### 4.3 `[astrologer_wp_lunar_return_chart]`
- Subject birth data + return year/month selection
- Optional return location override
- Wheel type: dual/single

#### 4.4 `[astrologer_wp_moon_phase]`
- Date/time + location form
- Moon phase display: illumination, phase name, next phases, eclipses

#### 4.5 `[astrologer_wp_now_chart]`
- No form needed (shows current sky at UTC/Greenwich)
- Auto-refreshable display
- Shows chart + chart data

### 5. Frontend Updates

#### 5.1 JavaScript (frontend.js)
- Add `init_searchCityOnInput()` calls for:
  - Composite chart (2 city inputs)
  - Solar return chart (1 birth city + 1 optional return location city)
  - Lunar return chart (1 birth city + 1 optional return location city)
  - Moon phase (1 city input)

#### 5.2 SCSS
- Create `_composite-chart.scss` (same pattern as synastry)
- Create `_solar-return-chart.scss`
- Create `_lunar-return-chart.scss`
- Create `_moon-phase.scss`
- Create `_now-chart.scss`
- Update `styles.scss` to import all new partials

### 6. Plugin Registration & Docs

#### 6.1 astrologer_wp.php
- Include all new shortcode files
- Bump version to 2.0.0

#### 6.2 readme.txt
- Update description with all features
- Document all 8 shortcodes
- Update FAQ
- Add changelog entry for 2.0.0

---

## New Shortcodes Summary

| Shortcode | Description |
|---|---|
| `[astrologer_wp_birth_chart]` | Natal birth chart |
| `[astrologer_wp_synastry_chart]` | Relationship synastry chart |
| `[astrologer_wp_transit_chart]` | Transit analysis chart |
| `[astrologer_wp_composite_chart]` | Composite midpoint chart |
| `[astrologer_wp_solar_return_chart]` | Yearly solar return chart |
| `[astrologer_wp_lunar_return_chart]` | Monthly lunar return chart |
| `[astrologer_wp_moon_phase]` | Moon phase details |
| `[astrologer_wp_now_chart]` | Current sky chart (UTC) |

---

## API v5 Endpoints Coverage

| API Endpoint | Plugin Coverage |
|---|---|
| `POST /api/v5/chart/birth-chart` | `[astrologer_wp_birth_chart]` |
| `POST /api/v5/chart/synastry` | `[astrologer_wp_synastry_chart]` |
| `POST /api/v5/chart/transit` | `[astrologer_wp_transit_chart]` |
| `POST /api/v5/chart/composite` | `[astrologer_wp_composite_chart]` |
| `POST /api/v5/chart/solar-return` | `[astrologer_wp_solar_return_chart]` |
| `POST /api/v5/chart/lunar-return` | `[astrologer_wp_lunar_return_chart]` |
| `POST /api/v5/moon-phase` | `[astrologer_wp_moon_phase]` |
| `POST /api/v5/now/chart` | `[astrologer_wp_now_chart]` |
| `POST /api/v5/compatibility-score` | Via synastry (include_relationship_score) |

---

## All Configuration Parameters (Admin Settings)

### API Credentials
- Astrologer API Key (RapidAPI)
- Geonames Username

### Chart Calculation
- Zodiac Type (Tropical / Sidereal)
- Sidereal Mode (20+ modes, conditional on Sidereal zodiac)
- Houses System (24 systems)
- Perspective Type (4 types)
- Chart Language (10 languages)

### Chart Rendering
- Chart Theme (classic, light, dark, dark-high-contrast, strawberry, black-and-white)
- Chart Style (classic / modern)
- Wheel Only Chart (checkbox)
- Split Chart (checkbox - separate wheel and grid SVGs)
- Transparent Background (checkbox)
- Double Chart Aspect Grid Type (list / table)

### Chart Display Options
- Show House Position Comparison (checkbox, default: on)
- Show Cusp Position Comparison (checkbox, default: on)
- Show Degree Indicators (checkbox, default: on)
- Show Aspect Icons (checkbox, default: on)
- Show Zodiac Background Ring (checkbox, modern style only, default: on)
