#!/usr/bin/env bash
# apple-container.sh — Run WordPress + MariaDB using Apple Containers.
#
# Usage:
#   ./apple-container.sh up       Start containers (auto-installs WP + activates plugin)
#   ./apple-container.sh down     Stop and remove containers
#   ./apple-container.sh logs     Tail WordPress container logs
#   ./apple-container.sh shell    Shell into the WordPress container
#   ./apple-container.sh clean    Stop containers + remove volumes and network
#   ./apple-container.sh status   Show container status
#
# Requirements:
#   - macOS 26+ with Apple Containers (`container` CLI)
#   - Plugin repo at the current directory

set -euo pipefail

# --- Configuration ---
NETWORK="astrologer-net"
DB_CONTAINER="astrologer-db"
WP_CONTAINER="astrologer-wp"
DB_VOLUME="astrologer-db-data"
WP_VOLUME="astrologer-wp-data"
PLUGIN_DIR="$(cd "$(dirname "$0")" && pwd)"
HOST_PORT=8080

DB_ROOT_PASSWORD="rootpass"
DB_NAME="wordpress"
DB_USER="wp"
DB_PASSWORD="wp"

# Optional: set these env vars before running to auto-configure the plugin
# export RAPIDAPI_KEY="your-key"
# export GEONAMES_USERNAME="your-username"

# --- Helpers ---
info()  { printf "==> %s\n" "$*"; }
error() { printf "ERROR: %s\n" "$*" >&2; exit 1; }

ensure_system() {
    if ! container list &>/dev/null; then
        info "Starting Apple Container system service..."
        container system start 2>/dev/null || true
        sleep 3
        container list &>/dev/null || error "Could not start container system. Run 'container system start' manually."
    fi
}

ensure_network() {
    local inspect
    inspect=$(container network inspect "$NETWORK" 2>/dev/null || echo "[]")
    if [ "$inspect" = "[]" ]; then
        info "Creating network $NETWORK..."
        container network create "$NETWORK"
        sleep 2  # Wait for network to be fully ready
    fi
}

ensure_volumes() {
    for vol in "$DB_VOLUME" "$WP_VOLUME"; do
        local inspect
        inspect=$(container volume inspect "$vol" 2>/dev/null || echo "[]")
        if [ "$inspect" = "[]" ]; then
            info "Creating volume $vol..."
            container volume create "$vol"
        fi
    done
}

get_container_ip() {
    container inspect "$1" 2>/dev/null \
        | sed -n 's/.*"ipv4Address":"\([0-9.]*\).*/\1/p' \
        | head -1
}

is_running() {
    container inspect "$1" 2>/dev/null | grep -q '"status":"running"'
}

wait_for_db() {
    info "Waiting for MariaDB to accept connections..."
    local max_wait=60 elapsed=0
    while [ $elapsed -lt $max_wait ]; do
        if container exec "$DB_CONTAINER" mariadb -u"$DB_USER" -p"$DB_PASSWORD" -e "SELECT 1" "$DB_NAME" &>/dev/null; then
            info "MariaDB is ready."
            return 0
        fi
        sleep 3
        elapsed=$((elapsed + 3))
    done
    error "MariaDB did not become ready within ${max_wait}s"
}

wait_for_wp_files() {
    info "Waiting for WordPress files to be extracted..."
    local max_wait=90 elapsed=0
    while [ $elapsed -lt $max_wait ]; do
        if container exec "$WP_CONTAINER" test -f /var/www/html/wp-includes/version.php &>/dev/null; then
            return 0
        fi
        sleep 3
        elapsed=$((elapsed + 3))
    done
    error "WordPress files did not appear within ${max_wait}s"
}

# --- Commands ---

cmd_up() {
    ensure_system
    ensure_network
    ensure_volumes

    # --- Start MariaDB ---
    if ! is_running "$DB_CONTAINER"; then
        info "Starting MariaDB..."
        container rm "$DB_CONTAINER" &>/dev/null || true

        container run -d \
            --name "$DB_CONTAINER" \
            --network "$NETWORK" \
            -e MYSQL_ROOT_PASSWORD="$DB_ROOT_PASSWORD" \
            -e MYSQL_DATABASE="$DB_NAME" \
            -e MYSQL_USER="$DB_USER" \
            -e MYSQL_PASSWORD="$DB_PASSWORD" \
            -v "$DB_VOLUME:/var/lib/mysql" \
            mariadb:11

        wait_for_db
    else
        info "MariaDB already running."
    fi

    # Get the DB container's IP (Apple Containers has no built-in DNS between containers)
    local db_ip
    db_ip=$(get_container_ip "$DB_CONTAINER")
    [ -n "$db_ip" ] || error "Could not determine MariaDB container IP address"
    info "MariaDB IP: $db_ip"

    # --- Start WordPress ---
    if ! is_running "$WP_CONTAINER"; then
        info "Starting WordPress..."
        container rm "$WP_CONTAINER" &>/dev/null || true

        container run -d \
            --name "$WP_CONTAINER" \
            --network "$NETWORK" \
            -p "${HOST_PORT}:80" \
            -e WORDPRESS_DB_HOST="$db_ip" \
            -e WORDPRESS_DB_NAME="$DB_NAME" \
            -e WORDPRESS_DB_USER="$DB_USER" \
            -e WORDPRESS_DB_PASSWORD="$DB_PASSWORD" \
            -e WORDPRESS_DEBUG=1 \
            -v "$WP_VOLUME:/var/www/html" \
            -v "${PLUGIN_DIR}:/var/www/html/wp-content/plugins/astrologer-api-playground" \
            wordpress:6-php8.2-apache

        wait_for_wp_files
    else
        info "WordPress already running."
    fi

    # --- Auto-install WordPress + activate plugin ---
    info "Setting up WordPress (WP-CLI)..."
    sleep 5  # Let Apache fully start

    # Install WP-CLI inside the container
    container exec "$WP_CONTAINER" bash -c '
        if ! command -v wp &>/dev/null; then
            curl -sf -o /tmp/wp-cli.phar https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
                && chmod +x /tmp/wp-cli.phar \
                && mv /tmp/wp-cli.phar /usr/local/bin/wp \
                || echo "WP-CLI download failed (no internet?), skipping auto-setup"
        fi
    '

    # Install WordPress core
    container exec "$WP_CONTAINER" bash -c "
        cd /var/www/html
        if ! wp core is-installed --allow-root 2>/dev/null; then
            wp core install \
                --allow-root \
                --url='http://localhost:${HOST_PORT}' \
                --title='AstrologerWP Test' \
                --admin_user=admin \
                --admin_password=admin \
                --admin_email=admin@example.com \
                --skip-email
        fi
        wp plugin activate astrologer-api-playground --allow-root 2>/dev/null || true
    "

    # Pre-configure API keys if provided via environment
    local rapidapi_key="${RAPIDAPI_KEY:-}"
    local geonames_user="${GEONAMES_USERNAME:-}"
    if [ -n "$rapidapi_key" ] || [ -n "$geonames_user" ]; then
        info "Configuring plugin settings..."
        container exec "$WP_CONTAINER" bash -c "
            cd /var/www/html
            wp eval --allow-root '
                \\\$s = get_option(\"astrologer_api_settings\", array());
                \\\$key = \"${rapidapi_key}\";
                \\\$geo = \"${geonames_user}\";
                if (\\\$key) \\\$s[\"rapidapi_key\"] = \\\$key;
                if (\\\$geo) \\\$s[\"geonames_username\"] = \\\$geo;
                update_option(\"astrologer_api_settings\", \\\$s);
            '
        "
    fi

    echo ""
    echo "============================================"
    echo "  WordPress ready at http://localhost:${HOST_PORT}"
    echo "  Admin: http://localhost:${HOST_PORT}/wp-admin"
    echo "  User: admin / Password: admin"
    echo "  Plugin: Astrologer API Playground (active)"
    [ -n "$rapidapi_key" ] && echo "  RapidAPI Key: configured"
    [ -n "$geonames_user" ] && echo "  GeoNames: configured ($geonames_user)"
    echo "============================================"
}

cmd_down() {
    ensure_system
    info "Stopping containers..."
    container stop "$WP_CONTAINER" &>/dev/null || true
    container stop "$DB_CONTAINER" &>/dev/null || true
    container rm "$WP_CONTAINER" &>/dev/null || true
    container rm "$DB_CONTAINER" &>/dev/null || true
    info "Containers stopped."
}

cmd_logs() {
    ensure_system
    container logs "$WP_CONTAINER"
}

cmd_shell() {
    ensure_system
    container exec -it "$WP_CONTAINER" bash
}

cmd_status() {
    ensure_system
    echo "Containers:"
    container list 2>/dev/null || echo "  (none running)"
    echo ""
    echo "Networks:"
    container network list 2>/dev/null || echo "  (none)"
    echo ""
    echo "Volumes:"
    container volume list 2>/dev/null || echo "  (none)"
}

cmd_clean() {
    cmd_down
    info "Removing volumes and network..."
    container volume rm "$DB_VOLUME" &>/dev/null || true
    container volume rm "$WP_VOLUME" &>/dev/null || true
    container network rm "$NETWORK" &>/dev/null || true
    info "Clean complete."
}

# --- Main ---
case "${1:-}" in
    up)     cmd_up ;;
    down)   cmd_down ;;
    logs)   cmd_logs ;;
    shell)  cmd_shell ;;
    status) cmd_status ;;
    clean)  cmd_clean ;;
    *)
        echo "Usage: $0 {up|down|logs|shell|status|clean}"
        echo ""
        echo "Commands:"
        echo "  up      Start WordPress + MariaDB (auto-installs WP + activates plugin)"
        echo "  down    Stop and remove containers"
        echo "  logs    Tail WordPress container logs"
        echo "  shell   Shell into the WordPress container"
        echo "  status  Show container/network/volume status"
        echo "  clean   Stop everything + remove volumes and network"
        exit 1
        ;;
esac
