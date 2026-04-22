# User Guide

Astrologer API is a WordPress plugin that integrates the professional Astrologer REST API with your site. It provides 22 native Gutenberg blocks, a comprehensive REST surface, WP-CLI sub-commands, and a React-powered admin experience for managing charts, birth data, and preferences.

## Requirements

- **WordPress**: 6.5 or newer (tested up to 6.7)
- **PHP**: 8.1 or newer
- **PHP extensions**: `sodium`, `openssl`, `json`
- **API credentials**: a RapidAPI key subscribed to the Astrologer API and, optionally, a GeoNames username for online geocoding

## First-time Setup

On activation the plugin automatically redirects you to the **Setup Wizard** (Dashboard → Astrologer → Setup). The wizard walks you through five steps:

1. **Welcome** — overview of what you are about to configure.
2. **API Key** — paste your RapidAPI key and optional GeoNames username. The values are encrypted at rest using libsodium (`sodium_crypto_secretbox`).
3. **School** — pick an astrological school: Modern Western, Traditional, Vedic, or Uranian. Each school seeds sensible defaults for house systems, orbs, and celestial points.
4. **Preferences** — choose the frontend language and whether blocks expose Basic, Advanced, or Expert controls by default.
5. **Finish** — the wizard persists a `astrologer_setup_completed` flag so it does not run again.

You can re-open the wizard at any time from **Astrologer → Setup**. Re-running the wizard overwrites the matching settings without touching unrelated fields.

## Creating a Chart

Charts live in the `astrologer_chart` custom post type. Three workflows are supported:

- **Inline (block)** — add the **Birth Form** block to any page. The attached natal-chart block renders as soon as the visitor submits the form. No post is persisted by default.
- **Persisted (block attribute)** — enable *Save as post* on the Birth Form. A new `astrologer_chart` post is created, wired to the active user, and visible in the admin list table.
- **REST / WP-CLI** — programmatic callers can POST to `/wp-json/astrologer/v1/charts` or run `wp astrologer chart natal …`. Both paths use the same service layer.

## Managing Settings

Dashboard → **Astrologer → Settings** groups preferences under five tabs: API Keys, Chart Defaults, Display, Security, and Advanced. Each setting is filterable through the `astrologer_api/settings_defaults` hook so themes can override defaults without database writes.

## Getting Help

- The **Help** tab (top-right of every Astrologer admin page) contains contextual documentation pulled from `src/Admin/HelpTabsProvider.php`.
- The **Docs** admin page renders this folder's Markdown files so offline and online audiences see the same content.
