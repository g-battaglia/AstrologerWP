#!/usr/bin/env bash
# compress_4_wp.sh — Create a distributable ZIP for WordPress.
#
# Usage:
#   bash compress_4_wp.sh
#
# The script:
#   1. Builds the frontend (npm run build)
#   2. Packages only the files WordPress needs into a ZIP
#   3. Excludes dev files (node_modules, src, .git, Docker, etc.)
#
# Output: astrologer-api-playground.zip (in the repo root)

set -euo pipefail

PLUGIN_SLUG="astrologer-api-playground"
ZIP_FILE="${PLUGIN_SLUG}.zip"
BUILD_DIR=$(mktemp -d)

echo "==> Building frontend..."
(cd frontend && npm run build)

echo "==> Assembling plugin files..."
mkdir -p "${BUILD_DIR}/${PLUGIN_SLUG}"

# PHP files (root)
cp astrologer-api-playground.php "${BUILD_DIR}/${PLUGIN_SLUG}/"
cp uninstall.php                 "${BUILD_DIR}/${PLUGIN_SLUG}/"
cp readme.txt                    "${BUILD_DIR}/${PLUGIN_SLUG}/"

# PHP includes
cp -r includes "${BUILD_DIR}/${PLUGIN_SLUG}/includes"

# Built frontend assets (JS + CSS bundles only)
mkdir -p "${BUILD_DIR}/${PLUGIN_SLUG}/frontend/dist"
cp -r frontend/dist/ "${BUILD_DIR}/${PLUGIN_SLUG}/frontend/dist/"

# WordPress shim (loaded at runtime)
if [ -f frontend/wp-react-shim.js ]; then
    cp frontend/wp-react-shim.js "${BUILD_DIR}/${PLUGIN_SLUG}/frontend/"
fi

# Languages
if [ -d languages ]; then
    cp -r languages "${BUILD_DIR}/${PLUGIN_SLUG}/languages"
fi

# Static assets (icons, banners, screenshots)
if [ -d assets ]; then
    cp -r assets "${BUILD_DIR}/${PLUGIN_SLUG}/assets"
fi

echo "==> Creating ZIP..."
(cd "${BUILD_DIR}" && zip -r -q "${ZIP_FILE}" "${PLUGIN_SLUG}")
mv "${BUILD_DIR}/${ZIP_FILE}" .

echo "==> Cleaning up..."
rm -rf "${BUILD_DIR}"

echo "==> Done! Created ${ZIP_FILE}"
ls -lh "${ZIP_FILE}"
