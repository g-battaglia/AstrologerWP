#!/bin/bash

OUTPUT_FILE="AstrologerWP.zip"

# Crea l'archivio escludendo i file e le cartelle nascoste
zip -r "$OUTPUT_FILE" . -x ".*" "*/.*"

echo "Archivio creato con successo: $OUTPUT_FILE"
