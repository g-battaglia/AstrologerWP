# Astrologer API Playground - WordPress Plugin

WordPress plugin to integrate the **Astrologer API** into your site. It generates and displays natal charts, planetary aspects, and the distribution of elements and modalities.

## 🚀 Features

- **Admin Settings Page**: Configure RapidAPI key, GeoNames username, language, house system and chart theme.
- **Secure REST Bridge**: Plugin REST endpoints proxy requests to the API, keeping the API key safe on the server.
- **React Components with shadcn**: Modern frontend with TypeScript and shadcn/ui styling.
- **Shortcodes**: Insert components into any post or page.
- **Gutenberg Blocks**: Dedicated blocks for the modern editor.

## 📋 Requirements

- WordPress 6.0+
- PHP 8.0+
- Node.js 18+ (for frontend development)
- RapidAPI key for Astrologer API

## 🛠️ Installation

### 1. Copy the plugin

Copy the `WP-Plugin` folder into WordPress `wp-content/plugins/` and rename it to `astrologer-api-playground`:

```bash
cp -r WP-Plugin /path/to/wordpress/wp-content/plugins/astrologer-api-playground
```

### 2. Install frontend dependencies

```bash
cd wp-content/plugins/astrologer-api-playground/frontend
npm install
```

### 3. Build assets

```bash
npm run build
```

This generates the files in `frontend/dist/` that will be loaded by the plugin.

### 4. Activate the plugin

Go to **WordPress Admin → Plugins** and activate "Astrologer API Playground".

### 5. Configure settings

Go to **Settings → Astrologer API** and enter:

- **RapidAPI Key**: Your API key from [RapidAPI](https://rapidapi.com/gbattaglia/api/astrologer)
- **GeoNames Username** (optional): For automatic location lookup
- **Base URL API**: Default is `https://astrologer.p.rapidapi.com`
- **Language, House System, Theme**: Options for charts

## 💻 Frontend Development

### Start the dev server

```bash
cd frontend
npm run dev
```

Vite starts on `http://localhost:5173` with hot reload.

### Production build

```bash
npm run build
```

### Frontend structure

```
frontend/
├── src/
│   ├── components/        # Componenti React
│   │   ├── ui/           # Componenti shadcn (Card, Table, Button, etc.)
│   │   ├── NatalChart.tsx
│   │   ├── AspectsTable.tsx
│   │   ├── ElementsChart.tsx
│   │   ├── ModalitiesChart.tsx
│   │   └── BirthForm.tsx
│   ├── lib/              # Utility e API client
│   ├── main.tsx          # Entry point
│   └── index.css         # Stili Tailwind
├── package.json
├── vite.config.ts
└── tsconfig.json
```

## 📝 Shortcodes

### Natal Chart

```
[astrologer_natal_chart name="Mario" year="1990" month="5" day="15" hour="14" minute="30" latitude="41.9028" longitude="12.4964" timezone="Europe/Rome"]
```

### Aspects Table

```
[astrologer_aspects_table year="1990" month="5" day="15" hour="14" minute="30" latitude="41.9028" longitude="12.4964" timezone="Europe/Rome"]
```

### Elements Chart

```
[astrologer_elements_chart year="1990" month="5" day="15" hour="14" minute="30" latitude="41.9028" longitude="12.4964" timezone="Europe/Rome"]
```

### Modalities Chart

```
[astrologer_modalities_chart year="1990" month="5" day="15" hour="14" minute="30" latitude="41.9028" longitude="12.4964" timezone="Europe/Rome"]
```

### Full Interactive Form

```
[astrologer_birth_form show_chart="true" show_aspects="true" show_elements="true" show_modalities="true"]
```

## 🧱 Gutenberg Blocks

In the Gutenberg editor, search for "Astrologer" to find the blocks:

- **Natal Chart**: SVG natal chart
- **Aspects Table**: Planetary aspects table
- **Elements Chart**: Fire/Earth/Air/Water distribution
- **Modalities Chart**: Cardinal/Fixed/Mutable distribution
- **Natal Chart Form**: Interactive form for the user

Each block has a sidebar panel to configure birth data.

## 🔌 REST API Endpoints

The plugin exposes REST endpoints that proxy to the Astrologer API:

| Endpoint                                  | Method | Description         |
| ----------------------------------------- | ------ | ------------------- |
| `/wp-json/astrologer/v1/natal-chart`      | POST   | Generates SVG chart |
| `/wp-json/astrologer/v1/natal-chart-data` | POST   | Natal chart data    |
| `/wp-json/astrologer/v1/subject`          | POST   | Subject data        |
| `/wp-json/astrologer/v1/synastry-chart`   | POST   | Synastry chart      |
| `/wp-json/astrologer/v1/transit-chart`    | POST   | Transit chart       |

### Example call

```javascript
fetch('/wp-json/astrologer/v1/natal-chart', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': wpApiSettings.nonce, // Se autenticato
    },
    body: JSON.stringify({
        name: 'John',
        year: 1990,
        month: 5,
        day: 15,
        hour: 14,
        minute: 30,
        latitude: 41.9028,
        longitude: 12.4964,
        timezone: 'Europe/Rome',
    }),
});
```

## 📁 Plugin Structure

```
WP-Plugin/
├── astrologer-api-playground.php  # File principale plugin
├── includes/
│   ├── class-astrologer-api-settings.php  # Pagina impostazioni
│   ├── class-astrologer-api-rest.php      # Endpoint REST
│   ├── class-astrologer-api-blocks.php    # Shortcodes e blocchi
│   └── class-astrologer-api-frontend.php  # Gestione asset
├── assets/
│   ├── js/
│   │   └── blocks.js              # Script blocchi Gutenberg
│   └── css/
│       └── admin.css              # Stili admin
├── frontend/                      # App React
│   ├── src/
│   ├── dist/                      # Build produzione
│   ├── package.json
│   └── vite.config.ts
└── README.md
```

## 🔒 Security

- The RapidAPI key is stored in the WordPress database and **never exposed** to frontend JavaScript.
- All API calls go through the PHP REST bridge.
- Input data is sanitized using WordPress functions (`sanitize_text_field`, `absint`, etc.).

## 📄 License

This plugin is distributed under the GNU General Public License v2 or later (GPL-2.0-or-later).
See the LICENSE file for the full license text.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/new-feature`)
3. Commit your changes (`git commit -am 'Add new feature'`)
4. Push the branch (`git push origin feature/new-feature`)
5. Open a Pull Request
