# Hooks Reference

Astrologer API exposes a curated set of action and filter hooks. They are declared in `src/Services/HooksRegistry.php` so the admin documentation page, the `doctor` CLI command, and third-party integrations all share a single source of truth.

Every hook is prefixed with `astrologer_api/` to avoid collisions with WordPress core or other plugins.

## Actions

### `astrologer_api/before_chart_request`
Fires before a chart API call is sent upstream.
**Args**: `string $chartType`, `array $payload`.

### `astrologer_api/after_chart_response`
Fires after a successful chart API response.
**Args**: `string $chartType`, `ChartResponseDTO $response`.

### `astrologer_api/chart_request_failed`
Fires when a chart API call returns a `WP_Error`.
**Args**: `string $chartType`, `WP_Error $error`.

### `astrologer_api/before_http_request`
Fires before any HTTP request to the upstream API.
**Args**: `string $endpoint`, `array $payload`.

### `astrologer_api/after_http_response`
Fires after any HTTP response from the upstream API.
**Args**: `string $endpoint`, `array|WP_Error $response`.

### `astrologer_api/chart_saved`
Fires when a chart is persisted to the `astrologer_chart` CPT.
**Args**: `int $postId`, `ChartRequestDTO $dto`, `int $userId`.

### `astrologer_api/settings_updated`
Fires after plugin settings are saved.
**Args**: `array $newSettings`, `array $oldSettings`.

### `astrologer_api/cron_before_tick`
Fires before a cron handler executes.
**Args**: `string $cronName`.

### `astrologer_api/cron_after_tick`
Fires after a cron handler completes, with execution stats.
**Args**: `string $cronName`, `array $stats`.

### `astrologer_api/setup_wizard_completed`
Fires when a user finishes the setup wizard.
**Args**: `array $wizardData`.

## Filters

### `astrologer_api/chart_request_args`
Modify the outgoing API payload before it is sent.
**Return**: `array`. **Args**: `array $payload`, `string $chartType`.

### `astrologer_api/chart_response`
Modify the API response DTO before it is returned to the caller.
**Return**: `ChartResponseDTO`. **Args**: `ChartResponseDTO $response`, `string $chartType`.

### `astrologer_api/settings_defaults`
Modify default settings values before first save.
**Return**: `array`. **Args**: `array $defaults`.

### `astrologer_api/cpt_args`
Modify CPT registration arguments for `astrologer_chart`.
**Return**: `array`. **Args**: `array $args`.

### `astrologer_api/capability_map`
Modify the role-to-capabilities mapping.
**Return**: `array`. **Args**: `array $map`.

### `astrologer_api/rest_endpoint_args`
Modify REST route schema/args for a given endpoint.
**Return**: `array`. **Args**: `array $args`, `string $routeName`.

### `astrologer_api/block_attributes_defaults`
Modify default block attributes before rendering.
**Return**: `array`. **Args**: `array $attrs`, `string $blockName`.

### `astrologer_api/school_preset`
Modify the `ChartOptions` preset for a given school.
**Return**: `ChartOptions`. **Args**: `ChartOptions $options`, `School $school`.

### `astrologer_api/rate_limit_per_minute`
Modify the per-minute rate limit for API requests.
**Return**: `int`. **Args**: `int $limit`, `string $endpoint`, `int $userId`.

### `astrologer_api/http_request_args`
Modify `wp_remote_post` / `wp_remote_get` arguments before dispatch.
**Return**: `array`. **Args**: `array $args`, `string $endpoint`.

### `astrologer_api/geonames_request_args`
Modify GeoNames API request arguments.
**Return**: `array`. **Args**: `array $args`.

### `astrologer_api/svg_allowed_tags`
Extend the SVG sanitizer tag allowlist.
**Return**: `array`. **Args**: `array $tags`.

### `astrologer_api/svg_allowed_attrs`
Extend the SVG sanitizer attribute allowlist.
**Return**: `array`. **Args**: `array $attrs`.

### `astrologer_api/client_ip`
Override client IP detection for rate limiting.
**Return**: `string`. **Args**: `string $ip`.
