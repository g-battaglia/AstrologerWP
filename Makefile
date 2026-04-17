# Makefile for Astrologer API WordPress plugin development.
#
# Quick start:
#   make install        — Install PHP + JS dependencies
#   make up             — Start wp-env (WordPress on :8888)
#   make build          — Production build (composer + wp-scripts)
#
# Testing:
#   make test           — Run all test suites
#   make test-php       — PHPUnit only
#   make test-js        — Jest only
#   make test-e2e       — Playwright only
#   make test-a11y      — axe-core accessibility audit
#   make test-all       — Lint + all tests
#
# Linting:
#   make lint           — Run all linters
#   make lint-php       — phpcs
#   make lint-js        — eslint
#   make lint-css       — stylelint
#   make stan           — phpstan level 8
#
# Other:
#   make pot            — Extract translatable strings
#   make zip            — Build distributable ZIP
#   make clean          — Remove generated artifacts

.PHONY: install up down build dev test test-php test-js test-e2e test-a11y \
        test-all lint lint-php lint-js lint-css stan pot zip clean

# ---------------------------------------------------------------------------
# Install
# ---------------------------------------------------------------------------

install:
	composer install
	npm install

# ---------------------------------------------------------------------------
# Environment
# ---------------------------------------------------------------------------

up:
	npm run env:start

down:
	npm run env:stop

# ---------------------------------------------------------------------------
# Build
# ---------------------------------------------------------------------------

build:
	composer install --no-dev
	npm run build

dev:
	npm run start

# ---------------------------------------------------------------------------
# Test
# ---------------------------------------------------------------------------

test: test-php test-js test-e2e

test-php:
	vendor/bin/phpunit

test-js:
	npm run test:js

test-e2e:
	npm run test:e2e

test-a11y:
	npx playwright test tests/e2e/a11y-axe.spec.ts

test-all: lint test-php test-js test-e2e test-a11y

# ---------------------------------------------------------------------------
# Lint & Static Analysis
# ---------------------------------------------------------------------------

lint: lint-php lint-js lint-css stan

lint-php:
	vendor/bin/phpcs

lint-js:
	npm run lint:js

lint-css:
	npm run lint:css

stan:
	vendor/bin/phpstan analyse

# ---------------------------------------------------------------------------
# i18n
# ---------------------------------------------------------------------------

pot:
	npm run pot

# ---------------------------------------------------------------------------
# Distribution
# ---------------------------------------------------------------------------

zip:
	./scripts/build-zip.sh

# ---------------------------------------------------------------------------
# Cleanup
# ---------------------------------------------------------------------------

clean:
	rm -rf build/ vendor/ node_modules/ .wp-env/
