# DEVELOPMENT – Astrologer API Playground (WordPress Plugin)

This document explains **step by step** how to:

- set up a **local test WordPress** from scratch;
- connect this repository as an **active plugin**;
- develop the **React frontend (Vite + TypeScript + shadcn)** locally with hot reload;
- build for production and verify that everything works.

> All examples assume a **macOS** system and some familiarity with the terminal.

---

## 1. Preparing a local test WordPress

You can use any local WordPress stack (LocalWP, MAMP, XAMPP, Docker, etc.).
Below is a **manual but generic** procedure that works on any LAMP/LNMP stack.

### 1.1. Prerequisites

- PHP 8.0+ with common extensions (mysqli, curl, json, mbstring, xml, zip…).
- MySQL/MariaDB.
- Web server (Apache or Nginx) **or** a pre-packaged stack (MAMP/LocalWP…).

If you want a simple stack, the fastest path is:

- Install **LocalWP** (https://localwp.com/) or **MAMP** (https://www.mamp.info/en/mac/).
- Create a new empty WordPress site via the UI.
- Follow section 2 to connect the plugin.

Below are the more “manual” steps with Apache/MySQL.

### 1.2. Create the database

Log into MySQL (from the terminal or phpMyAdmin) and create an empty DB, for example:

```sql
CREATE DATABASE astrologer_wp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'astrologer_user'@'localhost' IDENTIFIED BY 'password_sicura';
GRANT ALL PRIVILEGES ON astrologer_wp.* TO 'astrologer_user'@'localhost';
FLUSH PRIVILEGES;
```

Make a note of:

- DB name: `astrologer_wp`
- user: `astrologer_user`
- password: `password_sicura`

### 1.3. Download WordPress

Inside the folder where you keep your web projects (e.g. `~/Sites` or Apache’s DocumentRoot):

```bash
cd ~/Sites
curl -O https://wordpress.org/latest.zip
unzip latest.zip
mv wordpress astrologer-wp-test
```

Now you have a site in `~/Sites/astrologer-wp-test`.

Configure Apache/Nginx so it points to that folder or, for quick tests,
use PHP’s built-in server (not for production):

```bash
cd ~/Sites/astrologer-wp-test
php -S localhost:8000
```

Open your browser at `http://localhost:8000`.

### 1.4. WordPress installation wizard

The first time you visit the site URL, WordPress will ask for:

- Installation language
- DB parameters:
  - Database Name: `astrologer_wp`
  - Username: `astrologer_user`
  - Password: `password_sicura`
  - Database Host: `localhost`
  - Table Prefix: `wp_` (you can leave this for a test environment)

Then you’ll choose:

- **Site title** (e.g. “Astrologer Local Dev”)
- **Admin username** (e.g. `admin`)
- **Admin password** (write it down)
- **Admin email**

Once installation is complete you can log into the admin:

- Admin URL: `http://localhost:8000/wp-admin` (or whatever domain you configured)

### 1.5. Enable development mode in WordPress

To make the most of the Vite dev server it’s convenient to have `WP_DEBUG` enabled.

In `wp-config.php` (in the WordPress site root):

```php
define( 'WP_DEBUG', true );
```

> **Note:** in production set this back to `false`.


---

## 2. Connecting the plugin from the repository to the local WordPress

In this repository the plugin lives in:

```text
astrologer-api-playground/WP-Plugin/
```

In WordPress, plugins must live in:

```text
/path/to/wordpress/wp-content/plugins/
```

### 2.1. Option A – Copy the folder

This is the simplest option but you’ll have to recopy files after each change.

```bash
# From the repo root
cd /Users/giacomo/dev/astrologer-api-playground

# Copy the plugin into WordPress’ plugins folder
cp -r WP-Plugin /path/to/astrologer-wp-test/wp-content/plugins/astrologer-api-playground
```

### 2.2. Option B – Symlink (recommended for development)

This way you **edit the code in the repo** and WordPress sees it immediately.

```bash
# from your WordPress plugins folder
cd /path/to/astrologer-wp-test/wp-content/plugins

ln -s /Users/giacomo/dev/astrologer-api-playground/WP-Plugin astrologer-api-playground
```

Now in `wp-content/plugins/` you’ll see a folder `astrologer-api-playground` pointing to the repo.

### 2.3. Activate the plugin

1. Go to **WordPress Dashboard → Plugins**.
2. Find **Astrologer API Playground**.
3. Click **Activate**.

On activation the `Astrologer_API_Playground` class:

- initializes default options (`astrologer_api_settings`),
- registers REST endpoints and blocks,
- runs `flush_rewrite_rules()`.

If you frequently change permalinks/endpoints, it can help to visit
**Settings → Permalinks** and click Save to force a rewrite flush.


---

## 3. Configure the plugin in the admin

In the admin menu you’ll find **Settings → Astrologer API**.

Here you can set:

- **RapidAPI Key**: your `X-RapidAPI-Key`.
- **GeoNames Username** (optional): for automatic location lookups.
- **API Base URL**: usually `https://astrologer.p.rapidapi.com`.
- **Language**, **House System**, **Theme**, **Sidereal Zodiac / Ayanamsa**, etc.

These settings are read by:

- `Astrologer_API_Settings` for the admin page;
- `Astrologer_API_REST` to build the payload for the Astrologer API;
- `Astrologer_API_Frontend` to pass configuration and translations to the React frontend.


---

## 4. React frontend development (Vite + TypeScript + shadcn)

The frontend lives in:

```text
WP-Plugin/frontend/
```

### 4.1. Install dependencies

From the frontend plugin root:

```bash
cd /Users/giacomo/dev/astrologer-api-playground/WP-Plugin/frontend
npm install
```

This will install React, Vite, TypeScript and shadcn-style dependencies.

### 4.2. Development mode with Vite (hot reload)

Conditions for the plugin to use the Vite dev server:

1. In `wp-config.php` you have `define( 'WP_DEBUG', true );`.
2. The built bundle `frontend/dist/assets/index.js` **does not** exist (or is ignored).
3. The Vite dev server is running on `http://localhost:5173`.

The file `class-astrologer-api-frontend.php` does the following:

- if it finds `frontend/dist/assets/index.js` → registers those as assets;
- otherwise, if `WP_DEBUG === true` and Vite responds → uses Vite (`/@vite/client` + `src/main.tsx`).

#### 4.2.1. Start the dev server

```bash
cd /Users/giacomo/dev/astrologer-api-playground/WP-Plugin/frontend
npm run dev
```

Vite will start on `http://localhost:5173`.

> **Tip:** while developing the frontend you can safely
> delete/ignore the `frontend/dist/` folder to force
> the use of the dev server.

#### 4.2.2. Loading components in WordPress

On the PHP side:

- Shortcodes and Gutenberg blocks (class `Astrologer_API_Blocks`) generate `<div>` elements with:
  - `data-astrologer-component="..."`
  - `data-props="{...}"`
- The `Astrologer_API_Frontend` class enqueues the `astrologer-api-frontend` script.

On the frontend side (`src/main.tsx`):

- we query all nodes with `data-astrologer-component`;
- for each one we mount `ComponentMounter`, which chooses the correct component
  (`NatalChart`, `AspectsTable`, `ElementsChart`, `ModalitiesChart`, `BirthForm`).

When you modify files under `frontend/src/**`, Vite automatically reloads
the relevant part of the UI, preserving state when possible.

### 4.3. Production mode (build)

When you want to test the plugin in production-like conditions:

```bash
cd /Users/giacomo/dev/astrologer-api-playground/WP-Plugin/frontend
npm run build
```

This runs:

- `tsc -b` (TypeScript type-check)
- `vite build` (produces an optimized bundle).

The result is in:

```text
frontend/dist/assets/index.js
frontend/dist/assets/index.css
```

At this point, even if `WP_DEBUG` is `true`, the `Astrologer_API_Frontend` class,
seeing the files in `dist/assets/`, will use **those** instead of the dev server.


---

## 5. Set up a fresh test WordPress – Quick recap (checklist)

This is a “one page” checklist to remember the main steps.

### 5.1. WordPress setup

1. Create DB `astrologer_wp` + MySQL user.
2. Download WordPress into a folder (e.g. `~/Sites/astrologer-wp-test`).
3. Start the server (Apache/Nginx or `php -S localhost:8000`).
4. Run the WordPress installer from the browser.
5. Set `WP_DEBUG` to `true` in `wp-config.php`.

### 5.2. Connect the plugin

1. From the local repo:
   - **Recommended symlink**:

     ```bash
     cd /path/to/astrologer-wp-test/wp-content/plugins
     ln -s /Users/giacomo/dev/astrologer-api-playground/WP-Plugin astrologer-api-playground
     ```

   - or a physical copy of the folder.

2. In the admin: **Plugins → Astrologer API Playground → Activate**.
3. In **Settings → Astrologer API** enter:
   - RapidAPI Key,
   - GeoNames Username (optional),
   - API Base URL,
   - Language / House System / Theme.

### 5.3. Frontend development

- One-time: `cd WP-Plugin/frontend && npm install`.
- Dev mode (hot reload): `npm run dev` (Vite on `http://localhost:5173`).
- Verify that pages with shortcodes/blocks load the React components.

### 5.4. Build before “realistic” testing

- `cd WP-Plugin/frontend && npm run build`.
- Make sure `frontend/dist/assets/index.js` and `.css` exist.
- Reload the WordPress page with the browser cache cleared.


---

## 6. Debug tips

- **REST API:**
  - Use `/wp-json/astrologer/v1/natal-chart` and similar endpoints with Postman/cURL.
  - If you get `missing_api_key`, check the admin settings.
- **Browser console:**
  - JavaScript errors appear in the console; the `ErrorBoundary` component
    catches errors and displays them in the DOM.
- **PHP logs:**
  - With `WP_DEBUG` and `WP_DEBUG_LOG` you can inspect PHP errors in the log file.
- **Permalinks/Rewrite:**
  - If REST endpoints return 404, visit **Settings → Permalinks** and click Save.

With these steps you should be able to:

- spin up a WordPress test site in a few minutes;
- work comfortably on the plugin (PHP + REST bridge + React/shadcn);
- switch quickly between dev mode (Vite) and production build.

