#!/usr/bin/env bash
# Build distributable ZIP for WP.org submission.
# Excludes files listed in .distignore, installs production composer deps only.

set -euo pipefail

PLUGIN_SLUG="astrologer-api"
PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_DIR"

VERSION=$(grep -E '^[[:space:]]*\*[[:space:]]*Version:' astrologer-api.php | sed -E 's/.*Version:[[:space:]]*([0-9.]+).*/\1/' | head -1)

if [ -z "$VERSION" ]; then
	echo "ERROR: could not determine plugin version from astrologer-api.php" >&2
	exit 1
fi

DIST_DIR="dist"
STAGING_DIR="$DIST_DIR/$PLUGIN_SLUG"
ZIP_FILE="$DIST_DIR/${PLUGIN_SLUG}-${VERSION}.zip"

echo "Building $ZIP_FILE..."

rm -rf "$STAGING_DIR" "$ZIP_FILE"
mkdir -p "$STAGING_DIR"

# Copy repo into staging, keeping composer.json so we can rebuild vendor/ production-only.
# We'll strip composer.json/lock from staging after composer install.
rsync -a \
	--exclude="$DIST_DIR/" \
	--exclude="_legacy/" \
	--exclude="node_modules/" \
	--exclude="admin-src/" \
	--exclude="interactivity-src/" \
	--exclude="tests/" \
	--exclude="coverage/" \
	--exclude=".github/" \
	--exclude=".wp-env.json" \
	--exclude="phpunit.xml.dist" \
	--exclude="phpunit-unit.xml.dist" \
	--exclude="phpcs.xml.dist" \
	--exclude="phpstan.neon.dist" \
	--exclude="phpstan-constants.php" \
	--exclude="phpstan-stubs/" \
	--exclude="jest.config.js" \
	--exclude="playwright.config.ts" \
	--exclude=".eslintrc.json" \
	--exclude=".stylelintrc.json" \
	--exclude=".editorconfig" \
	--exclude="tsconfig.json" \
	--exclude="package.json" \
	--exclude="package-lock.json" \
	--exclude="Makefile" \
	--exclude="webpack.config.js" \
	--exclude="scripts/" \
	--exclude="*.md" \
	--include="readme.txt" \
	--exclude="PLAN/" \
	--exclude="PROGRESS.md" \
	--exclude="PROMPT.md" \
	--exclude="CHANGELOG.md" \
	--exclude="docs/" \
	--exclude="README.dev.md" \
	--exclude=".idea/" \
	--exclude=".vscode/" \
	--exclude=".DS_Store" \
	--exclude=".git/" \
	--exclude=".gitignore" \
	--exclude=".distignore" \
	--exclude=".opencode/" \
	--exclude=".claude/" \
	--exclude=".husky/" \
	--exclude="dist/" \
	--exclude="*.zip" \
	--exclude="*.tar.gz" \
	--exclude=".env*" \
	--exclude="assets/wporg/*.placeholder.txt" \
	--exclude="blocks/*/edit.tsx" \
	--exclude="blocks/*/edit.ts" \
	--exclude="blocks/*/view.tsx" \
	--exclude="blocks/*/view.ts" \
	--exclude="blocks/*/index.tsx" \
	--exclude="blocks/*/index.ts" \
	--exclude="vendor/" \
	./ "$STAGING_DIR/"

# Copy only composer.json/lock for production install, then remove them.
cp composer.json composer.lock "$STAGING_DIR/" 2>/dev/null || true

if [ -f "$STAGING_DIR/composer.json" ]; then
	(cd "$STAGING_DIR" && composer install --no-dev --optimize-autoloader --quiet --no-interaction)
	rm -f "$STAGING_DIR/composer.json" "$STAGING_DIR/composer.lock"
fi

# Build ZIP.
(cd "$DIST_DIR" && zip -rq "${PLUGIN_SLUG}-${VERSION}.zip" "$PLUGIN_SLUG")

rm -rf "$STAGING_DIR"

echo "Built: $ZIP_FILE"
ls -lh "$ZIP_FILE"
