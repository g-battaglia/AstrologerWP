#!/bin/bash

OUTPUT_FILE="AstrologerWP.zip"

# Crea l'archivio escludendo i file e le cartelle nascoste
zip -r "$OUTPUT_FILE" . -x ".*" "*/.*" "*.sh" "*.py" "Pipfile" "esbuild.config.mjs" "bun.lockb"

echo "Archivio creato con successo: $OUTPUT_FILE"
