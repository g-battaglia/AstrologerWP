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

# Create test pages with all shortcodes
echo "==> Creating test pages..."

wp post create --allow-root --post_type=page --post_status=publish \
    --post_title="Birth Chart" \
    --post_content='[astrologer_wp_birth_chart]' 2>/dev/null || true

wp post create --allow-root --post_type=page --post_status=publish \
    --post_title="Synastry Chart" \
    --post_content='[astrologer_wp_synastry_chart]' 2>/dev/null || true

wp post create --allow-root --post_type=page --post_status=publish \
    --post_title="Transit Chart" \
    --post_content='[astrologer_wp_transit_chart]' 2>/dev/null || true

wp post create --allow-root --post_type=page --post_status=publish \
    --post_title="Composite Chart" \
    --post_content='[astrologer_wp_composite_chart]' 2>/dev/null || true

wp post create --allow-root --post_type=page --post_status=publish \
    --post_title="Solar Return" \
    --post_content='[astrologer_wp_solar_return_chart]' 2>/dev/null || true

wp post create --allow-root --post_type=page --post_status=publish \
    --post_title="Lunar Return" \
    --post_content='[astrologer_wp_lunar_return_chart]' 2>/dev/null || true

wp post create --allow-root --post_type=page --post_status=publish \
    --post_title="Moon Phase" \
    --post_content='[astrologer_wp_moon_phase]' 2>/dev/null || true

wp post create --allow-root --post_type=page --post_status=publish \
    --post_title="Current Sky" \
    --post_content='[astrologer_wp_now_chart]' 2>/dev/null || true

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
