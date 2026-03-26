# Development Guide

How to set up a local WordPress for plugin development, work on the React frontend with hot reload, and build for production.

## 1. Quick Start with Docker (recommended)

```bash
# Install frontend dependencies
cd frontend && npm install && cd ..

# Start WordPress + MariaDB
make up

# Open http://localhost:8080, complete the WP installer
# Activate the plugin in Plugins > Astrologer API Playground
# Configure API key in Settings > Astrologer API

# Build frontend assets
make build-fe

# Or run Vite dev server for hot reload
make dev-fe
```

The plugin directory is bind-mounted into the container at `/var/www/html/wp-content/plugins/astrologer-api-playground`, so all file changes are reflected immediately.

## 2. Manual WordPress Setup

If you prefer LocalWP, MAMP, or another stack:

### 2.1 Symlink the plugin (recommended)

```bash
cd /path/to/wordpress/wp-content/plugins
ln -s /path/to/astrologer-api-playground astrologer-api-playground
```

### 2.2 Enable debug mode

In `wp-config.php`:

```php
define( 'WP_DEBUG', true );
```

### 2.3 Activate and configure

1. **Plugins > Astrologer API Playground > Activate**
2. **Settings > Astrologer API** -- enter RapidAPI Key, GeoNames username (optional), language, house system, theme.

## 3. Frontend Development (Vite + TypeScript + Tailwind)

The frontend lives in `frontend/`.

### 3.1 Install dependencies

```bash
cd frontend && npm install
```

### 3.2 Development mode (hot reload)

Requirements for Vite dev server to work:
- `WP_DEBUG` is `true` in `wp-config.php`
- `frontend/dist/assets/main.js` does **not** exist (delete `frontend/dist/` if needed)
- Vite dev server is running on `http://localhost:5173`

```bash
npm run dev
```

The PHP class `Astrologer_API_Frontend` detects whether to load the Vite dev server or the production bundle.

### 3.3 Production build

```bash
npm run build
```

Output goes to `frontend/dist/assets/`. Once the files exist, WordPress loads those instead of the dev server.

### 3.4 How components load in WordPress

PHP side: shortcodes and Gutenberg blocks render `<div>` elements with `data-astrologer-component` and `data-props` attributes.

JS side: `main.tsx` queries all nodes with `data-astrologer-component` and mounts the corresponding React component via `ComponentMounter`.

## 4. Useful Makefile Targets

```
make up        # Start Docker containers
make down      # Stop containers
make logs      # Tail container logs
make shell     # Shell into WP container
make build-fe  # Production build
make dev-fe    # Vite dev server
make pot       # Regenerate .pot translation file
make zip       # Build + create distributable ZIP
make clean     # Remove build artifacts
```

## 5. Debug Tips

- **REST API**: test endpoints with cURL/Postman at `/wp-json/astrologer/v1/natal-chart` etc. If you get `missing_api_key`, check admin settings.
- **Browser console**: JS errors are caught by `ErrorBoundary` and displayed in the DOM.
- **PHP logs**: enable `WP_DEBUG_LOG` in `wp-config.php` to write errors to `wp-content/debug.log`.
- **Permalinks**: if REST endpoints return 404, visit **Settings > Permalinks** and click Save to flush rewrite rules.
