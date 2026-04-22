# Developer Guide

Quick reference for maintainers. Non-exhaustive — see `Makefile` for the full list of targets.

## Prerequisites

- PHP 8.1+
- Node.js 20+
- Composer 2
- WP-CLI (optional, for Plugin Check)
- Docker (for wp-env)

## Install

```bash
make install         # composer install + npm install
```

## Running the environment

```bash
make up              # boot wp-env on http://localhost:8888 (admin/password)
make down            # stop wp-env
```

## Tests

```bash
# All test suites
make test-all

# Individual suites
composer test:unit           # phpunit -c phpunit-unit.xml.dist (no WP)
composer test:integration    # phpunit -c phpunit.xml.dist (requires wp-env)
npx jest                     # Jest + RTL
npx playwright test          # Playwright e2e (requires wp-env)
```

## Lint & Static Analysis

```bash
make lint-all        # phpcs + phpstan + eslint + stylelint

# Or individual:
vendor/bin/phpcs
vendor/bin/phpstan analyse --memory-limit=1G
npx eslint admin-src/ blocks/ interactivity-src/
npx stylelint "blocks/**/*.css" "admin-src/**/*.scss"
```

## Coverage

```bash
./tests/coverage.sh  # runs phpunit + jest with coverage reports in coverage/
```

## Plugin Check

When a local WP install with wp-cli is available:

```bash
wp plugin check astrologer-api --format=table
```

This runs the official [Plugin Check plugin](https://wordpress.org/plugins/plugin-check/) rules: escaping, nonces, file headers, i18n, etc.

From a wp-env container:

```bash
npx wp-env run cli wp plugin check astrologer-api
```

## Release

```bash
make build           # production build
make zip             # distributable ZIP under build/
```
