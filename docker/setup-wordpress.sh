#!/bin/bash
set -e

# ============================================================
# AstrologerWP - WordPress Auto-Setup Script
# Waits for DB, installs WP, activates plugin, configures settings.
# Run via: docker exec / container exec
# ============================================================

echo "==> Waiting for database to be ready..."
MAX_RETRIES=30
RETRY=0
until wp db check --allow-root --quiet 2>/dev/null; do
    RETRY=$((RETRY + 1))
    if [ $RETRY -ge $MAX_RETRIES ]; then
        echo "==> ERROR: Database not ready after ${MAX_RETRIES} attempts. Exiting."
        exit 1
    fi
    echo "    Attempt $RETRY/$MAX_RETRIES - waiting 2s..."
    sleep 2
done
echo "==> Database is ready."

# Install WordPress if not already installed
if ! wp core is-installed --allow-root 2>/dev/null; then
    echo "==> Installing WordPress..."
    wp core install \
        --allow-root \
        --url="${WORDPRESS_URL:-http://localhost:8080}" \
        --title="${WORDPRESS_TITLE:-AstrologerWP Test}" \
        --admin_user="${WORDPRESS_ADMIN_USER:-admin}" \
        --admin_password="${WORDPRESS_ADMIN_PASSWORD:-admin}" \
        --admin_email="${WORDPRESS_ADMIN_EMAIL:-admin@test.local}" \
        --skip-email
    echo "==> WordPress installed."
else
    echo "==> WordPress already installed, skipping."
fi

# Activate the plugin
echo "==> Activating AstrologerWP plugin..."
wp plugin activate astrologerwp --allow-root 2>/dev/null || true

# Configure plugin settings
if [ -n "$ASTROLOGER_API_KEY" ]; then
    echo "==> Setting Astrologer API Key..."
    wp option update astrologer_wp__api_key "$ASTROLOGER_API_KEY" --allow-root
fi

if [ -n "$GEONAMES_USERNAME" ]; then
    echo "==> Setting Geonames Username..."
    wp option update astrologer_wp__geonames_username "$GEONAMES_USERNAME" --allow-root
fi

if [ -n "$ASTROLOGER_WP_API_BASE_URL" ]; then
    echo "==> Setting custom API Base URL: $ASTROLOGER_WP_API_BASE_URL"
    wp option update astrologer_wp__api_base_url "$ASTROLOGER_WP_API_BASE_URL" --allow-root
fi

# Set default chart configuration
wp option update astrologer_wp__chart_theme "${CHART_THEME:-classic}" --allow-root
wp option update astrologer_wp__chart_style "${CHART_STYLE:-classic}" --allow-root
wp option update astrologer_wp__chart_language "${CHART_LANGUAGE:-EN}" --allow-root
wp option update astrologer_wp__zodiac_type "${ZODIAC_TYPE:-Tropical}" --allow-root
wp option update astrologer_wp__houses_system "${HOUSES_SYSTEM:-P}" --allow-root
wp option update astrologer_wp__perspective_type "${PERSPECTIVE_TYPE:-Apparent Geocentric}" --allow-root

# Enable display options (defaults to on)
wp option update astrologer_wp__show_house_position_comparison "1" --allow-root
wp option update astrologer_wp__show_cusp_position_comparison "1" --allow-root
wp option update astrologer_wp__show_degree_indicators "1" --allow-root
wp option update astrologer_wp__show_aspect_icons "1" --allow-root
wp option update astrologer_wp__show_zodiac_background_ring "1" --allow-root

# Create test pages with all shortcodes (idempotent - skips if page already exists)
echo "==> Creating test pages..."

create_page_if_missing() {
    local title="$1"
    local content="$2"
    local slug="$3"
    if ! wp post list --allow-root --post_type=page --name="$slug" --format=count 2>/dev/null | grep -q '^[1-9]'; then
        wp post create --allow-root --post_type=page --post_status=publish \
            --post_title="$title" --post_name="$slug" --post_content="$content"
        echo "    Created: /$slug/"
    else
        echo "    Exists:  /$slug/"
    fi
}

create_page_if_missing "Birth Chart"     '[astrologer_wp_birth_chart]'        "birth-chart"
create_page_if_missing "Synastry Chart"  '[astrologer_wp_synastry_chart]'     "synastry-chart"
create_page_if_missing "Transit Chart"   '[astrologer_wp_transit_chart]'      "transit-chart"
create_page_if_missing "Composite Chart" '[astrologer_wp_composite_chart]'    "composite-chart"
create_page_if_missing "Solar Return"    '[astrologer_wp_solar_return_chart]' "solar-return"
create_page_if_missing "Lunar Return"    '[astrologer_wp_lunar_return_chart]' "lunar-return"
create_page_if_missing "Moon Phase"      '[astrologer_wp_moon_phase]'         "moon-phase"
create_page_if_missing "Current Sky"     '[astrologer_wp_now_chart]'          "current-sky"

# Set pretty permalinks
wp rewrite structure '/%postname%/' --allow-root 2>/dev/null || true
wp rewrite flush --allow-root 2>/dev/null || true

echo ""
echo "============================================"
echo "  AstrologerWP Test Environment Ready!"
echo "============================================"
echo ""
echo "  WordPress:  ${WORDPRESS_URL:-http://localhost:8080}"
echo "  Admin:      ${WORDPRESS_URL:-http://localhost:8080}/wp-admin/"
echo "  User:       ${WORDPRESS_ADMIN_USER:-admin}"
echo "  Password:   ${WORDPRESS_ADMIN_PASSWORD:-admin}"
echo ""
echo "  Test Pages:"
echo "    /birth-chart/"
echo "    /synastry-chart/"
echo "    /transit-chart/"
echo "    /composite-chart/"
echo "    /solar-return/"
echo "    /lunar-return/"
echo "    /moon-phase/"
echo "    /current-sky/"
echo ""
if [ -n "$ASTROLOGER_WP_API_BASE_URL" ]; then
    echo "  API Endpoint: $ASTROLOGER_WP_API_BASE_URL"
else
    echo "  API Endpoint: https://astrologer.p.rapidapi.com (default)"
fi
echo "============================================"
