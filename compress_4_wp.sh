#!/bin/bash

# ============================================================
# Create AstrologerWP.zip for WordPress plugin submission.
# The ZIP contains a single top-level "astrologerwp/" folder.
# ============================================================

set -e

OUTPUT_FILE="AstrologerWP.zip"
PLUGIN_SLUG="astrologerwp"
TMP_DIR="/tmp/astrologerwp-build"

# Clean up
rm -f "$OUTPUT_FILE"
rm -rf "$TMP_DIR"

# Create temp structure
mkdir -p "$TMP_DIR/$PLUGIN_SLUG"

# Copy plugin files preserving structure
cp astrologer_wp.php "$TMP_DIR/$PLUGIN_SLUG/"
cp readme.txt        "$TMP_DIR/$PLUGIN_SLUG/"
cp LICENSE           "$TMP_DIR/$PLUGIN_SLUG/"
cp package.json      "$TMP_DIR/$PLUGIN_SLUG/"

cp -R includes  "$TMP_DIR/$PLUGIN_SLUG/includes"
cp -R assets    "$TMP_DIR/$PLUGIN_SLUG/assets"
cp -R languages "$TMP_DIR/$PLUGIN_SLUG/languages"

# Remove source maps from dist (not needed in submission)
find "$TMP_DIR/$PLUGIN_SLUG/assets/dist" -name "*.map" -delete 2>/dev/null

# Remove any .DS_Store
find "$TMP_DIR" -name ".DS_Store" -delete 2>/dev/null

# Create ZIP
cd "$TMP_DIR"
zip -r "$OLDPWD/$OUTPUT_FILE" "$PLUGIN_SLUG"
cd "$OLDPWD"

# Clean up temp
rm -rf "$TMP_DIR"

echo ""
echo "Created: $OUTPUT_FILE"
unzip -l "$OUTPUT_FILE" | head -20
echo "..."
unzip -l "$OUTPUT_FILE" | tail -1
