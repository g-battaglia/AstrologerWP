#!/bin/bash

OUTPUT_FILE="AstrologerWP.zip"

# Remove old zip if exists
rm -f "$OUTPUT_FILE"

# Create archive excluding dev/test files
zip -r "$OUTPUT_FILE" . \
    -x ".*" "*/.*" \
    -x "*.sh" "*.py" \
    -x "Pipfile" "esbuild.config.mjs" "bun.lockb" \
    -x "Makefile" "docker-compose.yml" \
    -x "docker/*" \
    -x "node_modules/*" \
    -x "package-lock.json" \
    -x "IMPLEMENTATION_PLAN.md" \
    -x "README.md" \
    -x "AstrologerWP.zip" \
    -x "assets/src/*"

echo "Archivio creato: $OUTPUT_FILE"
