#!/bin/bash
set -e

# ============================================================
# AstrologerWP - Apple Container (macOS) Test Environment
#
# Usage:
#   ./container-run.sh                                           # defaults
#   ./container-run.sh --api-url http://host.internal:8000       # custom API
#   ./container-run.sh --api-key YOUR_KEY --geonames-user USER   # with keys
#
# Requires: macOS with `container` CLI (macOS 26+)
# ============================================================

# --- Configuration ---
CONTAINER_NAME_DB="astrologerwp-db"
CONTAINER_NAME_WP="astrologerwp-wp"
NETWORK_NAME="astrologerwp-net"
VOLUME_DB="astrologerwp-dbdata"
IMAGE_TAG="astrologerwp-test:latest"
WP_PORT="${WP_PORT:-8080}"

# Parse arguments
ASTROLOGER_API_KEY="${ASTROLOGER_API_KEY:-}"
ASTROLOGER_WP_API_BASE_URL="${ASTROLOGER_WP_API_BASE_URL:-}"
GEONAMES_USERNAME="${GEONAMES_USERNAME:-}"

while [[ $# -gt 0 ]]; do
    case $1 in
        --api-key)
            ASTROLOGER_API_KEY="$2"; shift 2 ;;
        --api-url)
            ASTROLOGER_WP_API_BASE_URL="$2"; shift 2 ;;
        --geonames-user)
            GEONAMES_USERNAME="$2"; shift 2 ;;
        --port)
            WP_PORT="$2"; shift 2 ;;
        --help|-h)
            echo "Usage: $0 [OPTIONS]"
            echo ""
            echo "Options:"
            echo "  --api-key KEY        Astrologer API key (RapidAPI)"
            echo "  --api-url URL        Custom API base URL (for local testing)"
            echo "  --geonames-user USER Geonames username for city lookup"
            echo "  --port PORT          Host port for WordPress (default: 8080)"
            echo "  --help               Show this help"
            echo ""
            echo "Environment variables:"
            echo "  ASTROLOGER_API_KEY, ASTROLOGER_WP_API_BASE_URL, GEONAMES_USERNAME, WP_PORT"
            echo ""
            echo "Example - test against local API:"
            echo "  $0 --api-url http://host.internal:8000 --geonames-user myuser"
            exit 0 ;;
        *)
            echo "Unknown option: $1. Use --help for usage."; exit 1 ;;
    esac
done

# Load .env if present
if [ -f .env ]; then
    echo "==> Loading .env file..."
    set -a
    source .env
    set +a
fi

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"

echo "============================================"
echo "  AstrologerWP - Apple Container Setup"
echo "============================================"

# --- Cleanup previous run ---
echo "==> Cleaning up previous containers (if any)..."
container stop "$CONTAINER_NAME_WP" 2>/dev/null || true
container stop "$CONTAINER_NAME_DB" 2>/dev/null || true
container rm "$CONTAINER_NAME_WP" 2>/dev/null || true
container rm "$CONTAINER_NAME_DB" 2>/dev/null || true

# --- Create network ---
echo "==> Creating network: $NETWORK_NAME"
container network create "$NETWORK_NAME" 2>/dev/null || true

# --- Create volume for DB ---
echo "==> Creating volume: $VOLUME_DB"
container volume create "$VOLUME_DB" 2>/dev/null || true

# --- Build custom WordPress image ---
echo "==> Building WordPress image: $IMAGE_TAG"
container build \
    -t "$IMAGE_TAG" \
    -f "$SCRIPT_DIR/docker/Dockerfile" \
    "$SCRIPT_DIR"

# --- Start MariaDB ---
echo "==> Starting MariaDB..."
container run -d \
    --name "$CONTAINER_NAME_DB" \
    --network "$NETWORK_NAME" \
    -e MYSQL_ROOT_PASSWORD=rootpassword \
    -e MYSQL_DATABASE=wordpress \
    -e MYSQL_USER=wordpress \
    -e MYSQL_PASSWORD=wordpress \
    -v "$VOLUME_DB:/var/lib/mysql" \
    mariadb:11

echo "==> Waiting for MariaDB to initialize..."
sleep 10

# --- Start WordPress ---
echo "==> Starting WordPress on port $WP_PORT..."

# Build env flags
ENV_FLAGS=(
    -e "WORDPRESS_DB_HOST=$CONTAINER_NAME_DB"
    -e "WORDPRESS_DB_USER=wordpress"
    -e "WORDPRESS_DB_PASSWORD=wordpress"
    -e "WORDPRESS_DB_NAME=wordpress"
    -e "WORDPRESS_DEBUG=1"
    -e "WORDPRESS_URL=http://localhost:$WP_PORT"
    -e "WORDPRESS_TITLE=AstrologerWP Test"
    -e "WORDPRESS_ADMIN_USER=admin"
    -e "WORDPRESS_ADMIN_PASSWORD=admin"
    -e "WORDPRESS_ADMIN_EMAIL=admin@test.local"
)

[ -n "$ASTROLOGER_API_KEY" ] && ENV_FLAGS+=(-e "ASTROLOGER_API_KEY=$ASTROLOGER_API_KEY")
[ -n "$ASTROLOGER_WP_API_BASE_URL" ] && ENV_FLAGS+=(-e "ASTROLOGER_WP_API_BASE_URL=$ASTROLOGER_WP_API_BASE_URL")
[ -n "$GEONAMES_USERNAME" ] && ENV_FLAGS+=(-e "GEONAMES_USERNAME=$GEONAMES_USERNAME")

container run -d \
    --name "$CONTAINER_NAME_WP" \
    --network "$NETWORK_NAME" \
    -p "$WP_PORT:80" \
    "${ENV_FLAGS[@]}" \
    -v "$SCRIPT_DIR:/var/www/html/wp-content/plugins/astrologerwp" \
    "$IMAGE_TAG"

# --- Wait for WordPress to be ready ---
echo "==> Waiting for WordPress to start..."
MAX_RETRIES=30
RETRY=0
until curl -sf "http://localhost:$WP_PORT/" > /dev/null 2>&1; do
    RETRY=$((RETRY + 1))
    if [ $RETRY -ge $MAX_RETRIES ]; then
        echo "==> WARNING: WordPress not responding after ${MAX_RETRIES} attempts."
        echo "    Check logs with: container logs $CONTAINER_NAME_WP"
        break
    fi
    sleep 2
done

# --- Run setup ---
echo "==> Running WordPress setup..."
container exec "$CONTAINER_NAME_WP" setup-wordpress.sh

echo ""
echo "============================================"
echo "  Containers running!"
echo ""
echo "  WordPress: http://localhost:$WP_PORT"
echo "  Admin:     http://localhost:$WP_PORT/wp-admin/"
echo "  User:      admin / admin"
echo ""
echo "  Stop with: ./container-stop.sh"
echo "  Logs:      container logs $CONTAINER_NAME_WP"
echo "  Shell:     container exec -it $CONTAINER_NAME_WP bash"
echo "============================================"
