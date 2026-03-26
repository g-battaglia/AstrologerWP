# AstrologerWP - WordPress Plugin

WordPress plugin that integrates the [Astrologer API](https://rapidapi.com/gbattaglia/api/astrologer) into your site. Display natal charts, synastry, transits, composite charts, solar/lunar returns, compatibility scores, and more with a modern React frontend.

## Features

- **Natal Chart** -- SVG birth chart with planetary positions
- **Aspects Table** -- planetary aspects in tabular form
- **Elements & Modalities** -- Fire/Earth/Air/Water and Cardinal/Fixed/Mutable distribution
- **Positions Table** -- detailed planetary positions
- **Synastry Chart** -- relationship chart comparing two subjects
- **Transit Chart** -- current transits over a natal chart
- **Composite Chart** -- midpoint composite for relationships
- **Solar Return Chart** -- yearly solar return analysis
- **Lunar Return Chart** -- monthly lunar return analysis
- **Compatibility Score** -- numerical relationship compatibility
- **Current Sky (Now)** -- real-time planetary positions
- **Moon Phase** -- current moon phase display
- **Interactive Forms** -- users input birth data directly on the frontend
- **City Autocomplete** -- GeoNames-powered location search with timezone detection
- **Gutenberg Blocks** -- drag-and-drop blocks for the modern editor
- **Shortcodes** -- embed any component in posts, pages, or widget areas
- **Admin Settings** -- configure API key, language, house system, chart theme, sidereal mode
- **Secure REST Bridge** -- API key stays server-side, never exposed to the browser
- **Rate Limiting** -- 60 req/min per IP on public endpoints
- **i18n Ready** -- fully translatable (frontend + backend), .pot file included

## Requirements

- WordPress 6.0+
- PHP 8.0+
- Node.js 18+ (for frontend development)
- [RapidAPI key](https://rapidapi.com/gbattaglia/api/astrologer) for the Astrologer API
- GeoNames username (optional, for city autocomplete)

## Installation

### From ZIP (production)

1. Run `bash compress_4_wp.sh` (or `make zip`) to build the frontend and create the ZIP.
2. Upload `astrologer-api-playground.zip` via **WordPress Admin > Plugins > Add New > Upload**.
3. Activate the plugin.
4. Go to **Settings > Astrologer API** and enter your RapidAPI key.

### From source (development)

```bash
# Clone the repo
git clone https://github.com/g-battaglia/astrologer-api-playground.git
cd astrologer-api-playground

# Install frontend dependencies
cd frontend && npm install && cd ..

# Start WordPress via Docker
make up
# Open http://localhost:8080, complete WP setup, activate the plugin

# Build frontend for production
make build-fe

# Or start Vite dev server (hot reload, requires WP_DEBUG=true)
make dev-fe
```

## Docker Development Environment

The project includes a `docker-compose.yml` for local development:

```bash
make up       # Start WordPress 6 + MariaDB 11 (http://localhost:8080)
make down     # Stop containers
make logs     # Tail container logs
make shell    # Shell into the WordPress container
```

The plugin directory is bind-mounted into the container, so file changes are reflected immediately.

## Shortcodes

| Shortcode | Description |
|---|---|
| `[astrologer_birth_form]` | Interactive birth data form with chart output |
| `[astrologer_natal_chart]` | Static natal chart (requires birth data attributes) |
| `[astrologer_aspects_table]` | Planetary aspects table |
| `[astrologer_elements_chart]` | Elements distribution chart |
| `[astrologer_modalities_chart]` | Modalities distribution chart |
| `[astrologer_positions_table]` | Planetary positions table |
| `[astrologer_synastry_chart]` | Synastry chart (two subjects) |
| `[astrologer_synastry_form]` | Interactive synastry form |
| `[astrologer_transit_chart]` | Transit chart |
| `[astrologer_transit_form]` | Interactive transit form |
| `[astrologer_composite_chart]` | Composite chart |
| `[astrologer_composite_form]` | Interactive composite form |
| `[astrologer_solar_return_chart]` | Solar return chart |
| `[astrologer_solar_return_form]` | Interactive solar return form |
| `[astrologer_lunar_return_chart]` | Lunar return chart |
| `[astrologer_lunar_return_form]` | Interactive lunar return form |
| `[astrologer_now_chart]` | Current sky chart |
| `[astrologer_now_form]` | Interactive current sky form |
| `[astrologer_compatibility_chart]` | Compatibility score display |
| `[astrologer_compatibility_form]` | Interactive compatibility form |
| `[astrologer_moon_phase]` | Moon phase display |

Static shortcodes accept birth data attributes: `name`, `year`, `month`, `day`, `hour`, `minute`, `latitude`, `longitude`, `timezone`.

Example:
```
[astrologer_natal_chart name="John" year="1990" month="5" day="15" hour="14" minute="30" latitude="41.9028" longitude="12.4964" timezone="Europe/Rome"]
```

## Gutenberg Blocks

In the editor, search for "Astrologer" or browse the "Astrology" block category. Each block has a sidebar panel to configure birth data attributes.

## REST API Endpoints

All endpoints are under `/wp-json/astrologer/v1/`. Public endpoints are rate-limited (60 req/min per IP).

| Endpoint | Method | Description |
|---|---|---|
| `/natal-chart` | POST | SVG natal chart |
| `/natal-chart-data` | POST | Natal chart JSON data |
| `/subject` | POST | Subject data |
| `/synastry-chart` | POST | Synastry SVG chart |
| `/synastry-chart-data` | POST | Synastry JSON data |
| `/transit-chart` | POST | Transit SVG chart |
| `/transit-chart-data` | POST | Transit JSON data |
| `/composite-chart` | POST | Composite SVG chart |
| `/composite-chart-data` | POST | Composite JSON data |
| `/solar-return-chart` | POST | Solar return SVG chart |
| `/solar-return-chart-data` | POST | Solar return JSON data |
| `/lunar-return-chart` | POST | Lunar return SVG chart |
| `/lunar-return-chart-data` | POST | Lunar return JSON data |
| `/compatibility-score` | POST | Compatibility score |
| `/now-subject` | POST | Current moment subject |
| `/now-chart` | POST | Current sky SVG chart |
| `/city-search` | GET | GeoNames city autocomplete |
| `/settings-get` | GET | Read plugin settings (admin) |
| `/settings-update` | POST | Update plugin settings (admin) |

## Project Structure

```
astrologer-api-playground/
├── astrologer-api-playground.php    # Main plugin file
├── uninstall.php                    # Cleanup on uninstall
├── readme.txt                       # WordPress.org readme
├── includes/
│   ├── class-astrologer-api-settings.php   # Admin settings page
│   ├── class-astrologer-api-rest.php       # REST API bridge
│   ├── class-astrologer-api-blocks.php     # Shortcodes & Gutenberg blocks
│   └── class-astrologer-api-frontend.php   # Asset enqueuing & config
├── frontend/                        # React app (Vite + TypeScript + Tailwind)
│   ├── src/
│   │   ├── main.tsx                 # Entry point (mounts components)
│   │   ├── ComponentMounter.tsx     # Dynamic component resolver
│   │   ├── components/              # React components
│   │   │   ├── NatalChart.tsx
│   │   │   ├── AspectsTable.tsx
│   │   │   ├── ElementsChart.tsx
│   │   │   ├── ModalitiesChart.tsx
│   │   │   ├── PositionsTable.tsx
│   │   │   ├── BirthForm.tsx
│   │   │   ├── SynastryChart.tsx
│   │   │   ├── SynastryForm.tsx
│   │   │   ├── TransitChart.tsx
│   │   │   ├── TransitForm.tsx
│   │   │   ├── CompositeChart.tsx
│   │   │   ├── CompositeForm.tsx
│   │   │   ├── SolarReturnChart.tsx
│   │   │   ├── SolarReturnForm.tsx
│   │   │   ├── LunarReturnChart.tsx
│   │   │   ├── LunarReturnForm.tsx
│   │   │   ├── NowChart.tsx
│   │   │   ├── NowForm.tsx
│   │   │   ├── RelationshipScore.tsx
│   │   │   ├── CompatibilityForm.tsx
│   │   │   ├── MoonPhaseDisplay.tsx
│   │   │   ├── CityAutocomplete.tsx
│   │   │   ├── SubjectFormFields.tsx
│   │   │   ├── SettingsPage.tsx
│   │   │   ├── ErrorBoundary.tsx
│   │   │   └── ui/                  # shadcn-style UI primitives
│   │   ├── blocks/                  # Gutenberg block definitions
│   │   └── lib/                     # API client, types, utilities
│   ├── vite.config.ts
│   └── package.json
├── languages/
│   └── astrologer-api.pot           # Translation template
├── assets/
│   └── wporg/                       # WordPress.org icons & banners
├── docker-compose.yml               # Local WP dev environment
├── Makefile                         # Build & dev targets
└── compress_4_wp.sh                 # ZIP distribution builder
```

## Makefile Targets

```
make up        # Start Docker containers
make down      # Stop containers
make logs      # Tail logs
make shell     # Shell into WP container
make build-fe  # Production build of frontend
make dev-fe    # Start Vite dev server
make pot       # Regenerate .pot translation file
make zip       # Build frontend + create distributable ZIP
make clean     # Remove build artifacts
```

## Security

- The RapidAPI key is stored in the WordPress database and never exposed to frontend JavaScript.
- All API calls go through the PHP REST bridge.
- Input is sanitized using WordPress functions (`sanitize_text_field`, `absint`, etc.).
- Public endpoints are rate-limited (60 requests/minute per IP, admins exempt).
- Latitude/longitude values are validated server-side.

## License

GPL-2.0-or-later. See [LICENSE](LICENSE).
