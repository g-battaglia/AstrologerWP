# WP-CLI Commands

Every Astrologer CLI entry point lives under `wp astrologer …`. Commands are registered from `src/Cli/AstrologerCommand.php` and only instantiated when the plugin boots under WP-CLI.

All commands respect the same capability model as the REST endpoints. Pipe JSON output to `jq` if you need machine-readable shapes.

## `wp astrologer chart <type> [--flags]`

Calculate a chart without round-tripping through REST. `<type>` is one of `natal`, `birth`, or `now`.

**Flags**:
- `--name=<name>` — subject display name (required for natal/birth).
- `--date=YYYY-MM-DD` — birth date.
- `--time=HH:MM` — birth time (defaults to `00:00`).
- `--latitude=<lat>` / `--longitude=<lng>` — geographic coordinates.
- `--timezone=<tz>` — IANA timezone string (e.g. `Europe/Rome`).
- `--city=<city>` — optional city label.
- `--nation=<cc>` — ISO 3166-1 alpha-2 country code.
- `--format=<fmt>` — `json` (default) or `table`.

```bash
wp astrologer chart natal --name="Jane" --date=1990-05-15 --time=12:30 \
    --latitude=41.9 --longitude=12.5 --timezone=Europe/Rome
wp astrologer chart now --format=table
```

## `wp astrologer cache clear`

Removes every transient owned by the plugin. Keys are matched by the `astrologer_api_` prefix; timeouts are swept alongside the cached payloads, and `wp_cache_flush()` is called at the end. Returns the number of transient rows removed.

```bash
wp astrologer cache clear
```

## `wp astrologer settings <action> [<key>] [<value>]`

Manage the settings repository from the shell. Supported actions:
- `get <key>` — print the current value.
- `set <key> <value>` — write a scalar value (values are coerced on read).
- `reset` — restore defaults (see `astrologer_api/settings_defaults`).
- `export` — emit every setting as pretty-printed JSON, with sensitive fields (`rapidapi_key`, `geonames_username`) masked to `***`.

```bash
wp astrologer settings get language
wp astrologer settings set language IT
wp astrologer settings reset
wp astrologer settings export
```

## `wp astrologer health`

Calls the upstream `/health` endpoint and prints the JSON response. Exits with status 1 on `WP_Error`, 0 otherwise — useful inside deploy pipelines or uptime probes.

```bash
wp astrologer health
```

## `wp astrologer doctor`

Runs a set of environmental diagnostics and reports **pass**, **warn**, or **fail** per check. Any `fail` exits the command with status 1. Checks performed:

1. **PHP version** — must be `>= 8.1.0`.
2. **PHP extensions** — `sodium`, `json`, `openssl` must all be loaded.
3. **Encryption key** — libsodium available and `ASTROLOGER_ENCRYPTION_KEY` constant defined.
4. **RapidAPI key** — configured via settings.
5. **Permalinks** — `permalink_structure` is non-empty (required for REST pretty URLs).
6. **Rewrite rules** — at least one cached rule in the options table.

```bash
wp astrologer doctor
```

Consult `src/Cli/Commands/` for the canonical source of each command's argument schema and return shape.
