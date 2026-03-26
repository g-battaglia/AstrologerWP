#!/bin/bash

# ============================================================
# AstrologerWP - Stop Apple Container Environment
# ============================================================

CONTAINER_NAME_DB="astrologerwp-db"
CONTAINER_NAME_WP="astrologerwp-wp"
NETWORK_NAME="astrologerwp-net"
VOLUME_DB="astrologerwp-dbdata"

PURGE=false
if [ "$1" = "--purge" ]; then
    PURGE=true
fi

echo "==> Stopping containers..."
container stop "$CONTAINER_NAME_WP" 2>/dev/null && echo "    Stopped $CONTAINER_NAME_WP" || true
container stop "$CONTAINER_NAME_DB" 2>/dev/null && echo "    Stopped $CONTAINER_NAME_DB" || true

echo "==> Removing containers..."
container rm "$CONTAINER_NAME_WP" 2>/dev/null && echo "    Removed $CONTAINER_NAME_WP" || true
container rm "$CONTAINER_NAME_DB" 2>/dev/null && echo "    Removed $CONTAINER_NAME_DB" || true

if [ "$PURGE" = true ]; then
    echo "==> Purging volumes and network..."
    container volume rm "$VOLUME_DB" 2>/dev/null && echo "    Removed volume $VOLUME_DB" || true
    container network rm "$NETWORK_NAME" 2>/dev/null && echo "    Removed network $NETWORK_NAME" || true
    echo "==> Full cleanup done."
else
    echo ""
    echo "Data volumes preserved. Use --purge to delete everything:"
    echo "  ./container-stop.sh --purge"
fi
