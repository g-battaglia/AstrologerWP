# F7 — Cron, WP-CLI, Email Reminders

> **Theme:** Automazioni lato server: 3 cron job toggleable, 5 command WP-CLI, email template per solar return reminder.
> **Effort:** M (4 giorni)
> **Dipendenze:** F3 (REST per tick manuali), F4 (settings per toggle)

---

## Obiettivo

- **Cron**: 3 eventi schedulabili giornalieri, tutti disattivabili da settings. Handler idempotent, resilient a errori upstream.
- **WP-CLI**: superficie completa per admin headless (`wp astrologer chart|cache|settings|health|doctor`).
- **Email**: template HTML minimal per solar return reminder (opt-in via user meta o user role).

---

## Prerequisiti

- F4 settings con tab Cron funzionante.
- F3 endpoints per invocazioni REST.

---

## Tasks

### F7.1 — CronRegistry

**File:** `src/Cron/CronRegistry.php`

`Bootable`. Su `init`:
```php
if ($this->settings->get('cron.daily_transits.enabled')) {
    if (!wp_next_scheduled('astrologer_daily_transits')) {
        wp_schedule_event(strtotime('tomorrow 00:30'), 'daily', 'astrologer_daily_transits');
    }
    add_action('astrologer_daily_transits', [$dailyTransitsHandler, 'handle']);
} else {
    wp_clear_scheduled_hook('astrologer_daily_transits');
}
// ... stesso pattern per astrologer_daily_moon_phase e astrologer_solar_return_reminder
```

Registra su `rest_api_init` anche un endpoint `POST /cron/tick` (admin only) per trigger manuale durante test.

### F7.2 — DailyTransits handler

**File:** `src/Cron/Handlers/DailyTransitsHandler.php`

```php
public function handle(): void {
    do_action('astrologer_api/cron_before_tick', 'daily_transits');
    try {
        $response = $this->chartService->nowChart(new NowChartDTO(/* default geo */));
        update_option('astrologer_daily_transits_snapshot', [
            'date' => current_time('mysql'),
            'data' => $response,
        ], false);
    } catch (\Throwable $e) {
        $this->logger->error('Daily transits failed', ['e' => $e->getMessage()]);
    }
    do_action('astrologer_api/cron_after_tick', 'daily_transits');
}
```

Snapshot usato dal block `now-chart` come fallback cache quando in mode "daily".

### F7.3 — DailyMoonPhase handler

**File:** `src/Cron/Handlers/DailyMoonPhaseHandler.php`

- Chiama `GET /moon-phase/current` upstream.
- Salva in `astrologer_current_moon_phase` option.
- Riempie anche `astrologer_next_moon_events` (next 4 quarters).
- Hook `astrologer_api/moon_phase_updated` passato payload.

Block `moon-phase` in mode "current" legge da option prima di fetch (pre-hydration via data attribute in render.php).

### F7.4 — SolarReturnReminder handler

**File:** `src/Cron/Handlers/SolarReturnReminderHandler.php`

Logica:
1. Query utenti con meta `astrologer_birth_data` e opt-in `astrologer_solar_return_reminder_enabled = 1`.
2. Per ogni user: calcolare prossimo solar return (birthday corrente anno).
3. Se `reminder_days_before` giorni prima di oggi → invia email.
4. Traccia sends in user meta `astrologer_solar_return_last_sent` per evitare doppi invii stessa data.

Email template: `templates/emails/solar-return-reminder.php`.

Subject/body localizzati. Body include:
- Saluto personalizzato.
- Data esatta solar return.
- Link a pagina "view my solar return chart" (slug configurabile in settings).
- Footer con unsubscribe link.

### F7.5 — WP-CLI: AstrologerCommand bootstrap

**File:** `src/Cli/AstrologerCommand.php`

```php
if (defined('WP_CLI') && WP_CLI) {
    \WP_CLI::add_command('astrologer', self::class);
}
```

Subcommands registrati come sub-class:

### F7.6 — `wp astrologer chart`

**File:** `src/Cli/Commands/ChartCommand.php`

```
wp astrologer chart create --user=<id> --type=natal --date=YYYY-MM-DD --time=HH:MM --city="..." [--save]
wp astrologer chart list [--user=<id>] [--type=natal] [--format=table|json]
wp astrologer chart get <chart-id> [--format=json|svg]
wp astrologer chart delete <chart-id> [--force]
wp astrologer chart recalculate <chart-id>
```

Esempio:
```bash
wp astrologer chart create --user=1 --type=natal --date=1990-05-15 --time=12:30 --city=Rome --save
# Output: Created chart #42 for user "admin" (natal, Rome, 1990-05-15 12:30)
```

### F7.7 — `wp astrologer cache`

**File:** `src/Cli/Commands/CacheCommand.php`

Per gestire la singola cache (moon phase):
```
wp astrologer cache refresh-moon-phase
wp astrologer cache clear-moon-phase
wp astrologer cache status
```

### F7.8 — `wp astrologer settings`

**File:** `src/Cli/Commands/SettingsCommand.php`

```
wp astrologer settings get [<key>] [--format=json|table]
wp astrologer settings set <key> <value>
wp astrologer settings export > backup.json
wp astrologer settings import < backup.json
wp astrologer settings reset [--yes]
wp astrologer settings test-connection
```

Chiave notation dot: `wp astrologer settings set school.default traditional`.

### F7.9 — `wp astrologer health`

**File:** `src/Cli/Commands/HealthCommand.php`

Output table:
```
┌─────────────────────────────┬──────────┬──────────────────┐
│ Check                        │ Status    │ Detail           │
├─────────────────────────────┼──────────┼──────────────────┤
│ PHP version                  │ OK        │ 8.1.27           │
│ WP version                   │ OK        │ 6.5.2            │
│ Composer autoload            │ OK        │ 142 classes      │
│ Encryption key               │ OK        │ from env var     │
│ API key present              │ OK        │ •••••••••••abcd  │
│ API connection               │ OK        │ 120ms /health    │
│ Geonames connection          │ WARN      │ Not configured   │
│ Cron events scheduled        │ OK        │ 2 of 3           │
│ CPT registered               │ OK        │ astrologer_chart │
│ Writable /uploads            │ OK        │                   │
│ Capabilities registered      │ OK        │ 5/5              │
└─────────────────────────────┴──────────┴──────────────────┘
```

Exit code 0 se tutto OK, 1 se almeno un FAIL, 2 per WARN.

### F7.10 — `wp astrologer doctor`

**File:** `src/Cli/Commands/DoctorCommand.php`

Diagnostic attivo: prova ogni endpoint upstream con subject dummy, misura latenza, rileva deprecazioni. Output verbose con remediation suggestions.

### F7.11 — Test CLI

**File:** `tests/Integration/Cli/ChartCommandTest.php` — usa `WP_CLI::run_command` programmatico, assert output con `WP_CLI::get_runner()` mock.

Smoke test per ciascun command in F9.

---

## Criterio di demoable

1. Settings → Cron tab → toggle "Daily Moon Phase" ON → `wp cron event list` mostra `astrologer_daily_moon_phase` scheduled daily.
2. `wp cron event run astrologer_daily_moon_phase` → option `astrologer_current_moon_phase` aggiornato.
3. `wp astrologer health` → esce con codice 0, tabella con tutti i check verdi (assumendo API key valida).
4. `wp astrologer chart create --user=1 --type=natal --date=1990-05-15 --time=12:30 --city=Rome --save` → post CPT creato con ID visibile.
5. Email reminder: override di 1 user con `astrologer_solar_return_reminder_enabled=1` + birthday fra 3 giorni → `wp cron event run astrologer_solar_return_reminder` → email in MailHog (wp-env default SMTP).
6. `wp astrologer settings export` produce JSON valido importabile con `import`.

---

## Decisione open (Checkpoint #5)

Solar return email reminder: v1.0 core o v1.1?

**Pro v1.0**: feature distintiva, raro in altri plugin astrologici WP.
**Contro**: richiede deliverability (SMTP), anti-spam headers, unsubscribe flow compliant. Test complicato senza CI.

**Raccomandazione**: v1.0 ma feature-flagged (toggle default OFF), documentato come "preview" in readme. Se in F10 il testing non basta → delay a v1.1 e toglierlo dal toggle menu.

---

## Hooks introdotti

- Action `astrologer_api/cron_before_tick` — passato `$cron_name`.
- Action `astrologer_api/cron_after_tick` — passato `$cron_name`, `$success`.
- Action `astrologer_api/moon_phase_updated` — passato `$payload`.
- Filter `astrologer_api/solar_return_email_subject`.
- Filter `astrologer_api/solar_return_email_body`.
- Filter `astrologer_api/solar_return_reminder_recipients` — override lista utenti.
- Filter `astrologer_api/cli_command_args` — per ogni subcommand.

---

## Sicurezza email

- `From:` = admin email + alias (`astrologer@site.com`).
- Unsubscribe link firmato con hmac (`wp_hash`) per evitare abuse.
- No tracking pixel.
- Rate: max 1 email per user per 24h.
