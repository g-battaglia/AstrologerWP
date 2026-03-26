<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use AstrologerWP\Constants\KerykeionConstants;

// Registra una nuova pagina nel menu dell'admin.
add_action('admin_menu', 'astrologer_wp_add_admin_menu');
function astrologer_wp_add_admin_menu() {
    add_menu_page(
        'AstrologerWP',
        'AstrologerWP',
        'manage_options',
        'astrologer-wp',
        'astrologer_wp_admin_page',
        'dashicons-admin-generic',
        20
    );
}

// Register settings, section, and fields
add_action('admin_init', 'astrologer_wp_register_settings');
function astrologer_wp_register_settings() {
    // API Credentials
    register_setting('astrologer_wp_settings', 'astrologer_wp__api_key', 'sanitize_text_field');
    register_setting('astrologer_wp_settings', 'astrologer_wp__geonames_username', 'sanitize_text_field');
    register_setting('astrologer_wp_settings', 'astrologer_wp__api_base_url', 'esc_url_raw');

    // Chart Calculation
    register_setting('astrologer_wp_settings', 'astrologer_wp__zodiac_type', 'sanitize_text_field');
    register_setting('astrologer_wp_settings', 'astrologer_wp__houses_system', 'sanitize_text_field');
    register_setting('astrologer_wp_settings', 'astrologer_wp__chart_language', 'sanitize_text_field');
    register_setting('astrologer_wp_settings', 'astrologer_wp__sidereal_mode', 'sanitize_text_field');
    register_setting('astrologer_wp_settings', 'astrologer_wp__perspective_type', 'sanitize_text_field');

    // Chart Rendering
    register_setting('astrologer_wp_settings', 'astrologer_wp__chart_theme', 'sanitize_text_field');
    register_setting('astrologer_wp_settings', 'astrologer_wp__chart_style', 'sanitize_text_field');
    register_setting('astrologer_wp_settings', 'astrologer_wp__wheel_only_chart', 'sanitize_text_field');
    register_setting('astrologer_wp_settings', 'astrologer_wp__split_chart', 'sanitize_text_field');
    register_setting('astrologer_wp_settings', 'astrologer_wp__transparent_background', 'sanitize_text_field');
    register_setting('astrologer_wp_settings', 'astrologer_wp__double_chart_aspect_grid_type', 'sanitize_text_field');

    // Chart Display Options
    register_setting('astrologer_wp_settings', 'astrologer_wp__show_house_position_comparison', 'sanitize_text_field');
    register_setting('astrologer_wp_settings', 'astrologer_wp__show_cusp_position_comparison', 'sanitize_text_field');
    register_setting('astrologer_wp_settings', 'astrologer_wp__show_degree_indicators', 'sanitize_text_field');
    register_setting('astrologer_wp_settings', 'astrologer_wp__show_aspect_icons', 'sanitize_text_field');
    register_setting('astrologer_wp_settings', 'astrologer_wp__show_zodiac_background_ring', 'sanitize_text_field');

    // --- Section: API Credentials ---
    add_settings_section(
        'astrologer_wp_section_credentials',
        __( 'API Credentials', 'astrologerwp' ),
        'astrologer_wp_section_credentials_callback',
        'astrologer-wp'
    );

    add_settings_field('astrologer_wp__api_key', __( 'Astrologer API Key', 'astrologerwp' ), 'astrologer_wp_api_key_field_callback', 'astrologer-wp', 'astrologer_wp_section_credentials');
    add_settings_field('astrologer_wp__geonames_username', __( 'Geonames Username', 'astrologerwp' ), 'astrologer_wp_geonames_username_field_callback', 'astrologer-wp', 'astrologer_wp_section_credentials');
    add_settings_field('astrologer_wp__api_base_url', __( 'API Base URL (Advanced)', 'astrologerwp' ), 'astrologer_wp_api_base_url_field_callback', 'astrologer-wp', 'astrologer_wp_section_credentials');

    // --- Section: Chart Calculation ---
    add_settings_section(
        'astrologer_wp_section_calculation',
        __( 'Chart Calculation', 'astrologerwp' ),
        'astrologer_wp_section_calculation_callback',
        'astrologer-wp'
    );

    add_settings_field('astrologer_wp__zodiac_type', __( 'Zodiac Type', 'astrologerwp' ), 'astrologer_wp_zodiac_type_field_callback', 'astrologer-wp', 'astrologer_wp_section_calculation');
    add_settings_field('astrologer_wp__sidereal_mode', __( 'Sidereal Mode', 'astrologerwp' ), 'astrologer_wp_sidereal_mode_callback', 'astrologer-wp', 'astrologer_wp_section_calculation');
    add_settings_field('astrologer_wp__houses_system', __( 'Houses System', 'astrologerwp' ), 'astrologer_wp_houses_system_field_callback', 'astrologer-wp', 'astrologer_wp_section_calculation');
    add_settings_field('astrologer_wp__chart_language', __( 'Chart Language', 'astrologerwp' ), 'astrologer_wp_chart_language_field_callback', 'astrologer-wp', 'astrologer_wp_section_calculation');
    add_settings_field('astrologer_wp__perspective_type', __( 'Perspective Type', 'astrologerwp' ), 'astrologer_wp_perspective_type_callback', 'astrologer-wp', 'astrologer_wp_section_calculation');

    // --- Section: Chart Rendering ---
    add_settings_section(
        'astrologer_wp_section_rendering',
        __( 'Chart Rendering', 'astrologerwp' ),
        'astrologer_wp_section_rendering_callback',
        'astrologer-wp'
    );

    add_settings_field('astrologer_wp__chart_theme', __( 'Chart Theme', 'astrologerwp' ), 'astrologer_wp_chart_theme_field_callback', 'astrologer-wp', 'astrologer_wp_section_rendering');
    add_settings_field('astrologer_wp__chart_style', __( 'Chart Style', 'astrologerwp' ), 'astrologer_wp_chart_style_field_callback', 'astrologer-wp', 'astrologer_wp_section_rendering');
    add_settings_field('astrologer_wp__wheel_only_chart', __( 'Wheel Only Chart', 'astrologerwp' ), 'astrologer_wp_wheel_only_field_callback', 'astrologer-wp', 'astrologer_wp_section_rendering');
    add_settings_field('astrologer_wp__split_chart', __( 'Split Chart', 'astrologerwp' ), 'astrologer_wp_split_chart_field_callback', 'astrologer-wp', 'astrologer_wp_section_rendering');
    add_settings_field('astrologer_wp__transparent_background', __( 'Transparent Background', 'astrologerwp' ), 'astrologer_wp_transparent_background_field_callback', 'astrologer-wp', 'astrologer_wp_section_rendering');
    add_settings_field('astrologer_wp__double_chart_aspect_grid_type', __( 'Aspect Grid Type', 'astrologerwp' ), 'astrologer_wp_double_chart_aspect_grid_type_field_callback', 'astrologer-wp', 'astrologer_wp_section_rendering');

    // --- Section: Chart Display Options ---
    add_settings_section(
        'astrologer_wp_section_display',
        __( 'Chart Display Options', 'astrologerwp' ),
        'astrologer_wp_section_display_callback',
        'astrologer-wp'
    );

    add_settings_field('astrologer_wp__show_house_position_comparison', __( 'Show House Position Comparison', 'astrologerwp' ), 'astrologer_wp_show_house_position_comparison_callback', 'astrologer-wp', 'astrologer_wp_section_display');
    add_settings_field('astrologer_wp__show_cusp_position_comparison', __( 'Show Cusp Position Comparison', 'astrologerwp' ), 'astrologer_wp_show_cusp_position_comparison_callback', 'astrologer-wp', 'astrologer_wp_section_display');
    add_settings_field('astrologer_wp__show_degree_indicators', __( 'Show Degree Indicators', 'astrologerwp' ), 'astrologer_wp_show_degree_indicators_callback', 'astrologer-wp', 'astrologer_wp_section_display');
    add_settings_field('astrologer_wp__show_aspect_icons', __( 'Show Aspect Icons', 'astrologerwp' ), 'astrologer_wp_show_aspect_icons_callback', 'astrologer-wp', 'astrologer_wp_section_display');
    add_settings_field('astrologer_wp__show_zodiac_background_ring', __( 'Show Zodiac Background Ring', 'astrologerwp' ), 'astrologer_wp_show_zodiac_background_ring_callback', 'astrologer-wp', 'astrologer_wp_section_display');
}

// --- Section Callbacks ---

function astrologer_wp_section_credentials_callback() {
    echo '<p>' . esc_html__( 'Enter your API credentials to connect to the Astrologer API and Geonames location services.', 'astrologerwp' ) . '</p>';
}

function astrologer_wp_section_calculation_callback() {
    echo '<p>' . esc_html__( 'Configure the astrological calculation parameters used for all charts.', 'astrologerwp' ) . '</p>';
}

function astrologer_wp_section_rendering_callback() {
    echo '<p>' . esc_html__( 'Customize how the SVG charts are rendered visually.', 'astrologerwp' ) . '</p>';
}

function astrologer_wp_section_display_callback() {
    echo '<p>' . esc_html__( 'Toggle individual display elements on the chart.', 'astrologerwp' ) . '</p>';
}

// --- Credential Fields ---

function astrologer_wp_api_key_field_callback() {
    $value = get_option('astrologer_wp__api_key');
    echo '<input type="text" id="astrologer_wp__api_key" name="astrologer_wp__api_key" value="' . esc_attr($value) . '" class="regular-text" required/>';
    echo '<p class="description">';
    echo wp_kses(
        sprintf(
            /* translators: %s: signup link URL */
            __( 'Enter your Astrologer API Key here.<br>You can get your API Key by <a href="%s" target="_blank">signing up here</a>.', 'astrologerwp' ),
            'https://rapidapi.com/gbattaglia/api/astrologer/pricing'
        ),
        array(
            'br' => array(),
            'a'  => array( 'href' => array(), 'target' => array() ),
        )
    );
    echo '</p>';
}

function astrologer_wp_geonames_username_field_callback() {
    $value = get_option('astrologer_wp__geonames_username');
    echo '<input type="text" id="astrologer_wp__geonames_username" name="astrologer_wp__geonames_username" value="' . esc_attr($value) . '" class="regular-text" required/>';
    echo '<p class="description">';
    echo wp_kses(
        sprintf(
            /* translators: %s: signup link URL */
            __( 'Enter your Geonames username for location services.<br>You can get your username for free by <a href="%s" target="_blank">signing up here</a>.<br>Geonames is used to get the timezone and coordinates of the location and has no affiliation with the Astrologer service.', 'astrologerwp' ),
            'http://www.geonames.org/login'
        ),
        array(
            'br' => array(),
            'a'  => array( 'href' => array(), 'target' => array() ),
        )
    );
    echo '</p>';
}

function astrologer_wp_api_base_url_field_callback() {
    $value = get_option('astrologer_wp__api_base_url', '');
    $envValue = getenv('ASTROLOGER_WP_API_BASE_URL');
    echo '<input type="url" id="astrologer_wp__api_base_url" name="astrologer_wp__api_base_url" value="' . esc_attr($value) . '" class="regular-text" placeholder="https://astrologer.p.rapidapi.com"/>';
    echo '<p class="description">';
    $env_notice = $envValue
        ? ' ' . sprintf(
            /* translators: %s: current environment variable value */
            __( '(currently: %s)', 'astrologerwp' ),
            '<code>' . esc_html($envValue) . '</code>'
        )
        : '';
    echo wp_kses(
        sprintf(
            /* translators: %1$s: environment variable name with code tag, %2$s: optional current value notice */
            __( 'Leave empty to use the default RapidAPI endpoint. Set a custom URL to point to your own API instance (e.g. for local testing).<br>Can also be set via the %1$s environment variable%2$s.', 'astrologerwp' ),
            '<code>ASTROLOGER_WP_API_BASE_URL</code>',
            $env_notice
        ),
        array(
            'br'   => array(),
            'code' => array(),
        )
    );
    echo '</p>';
}

// --- Calculation Fields ---

function astrologer_wp_zodiac_type_field_callback() {
    $value = get_option('astrologer_wp__zodiac_type', 'Tropical');
    $zodiacTypes = KerykeionConstants::ZODIAC_TYPES;

    echo '<select id="astrologer_wp__zodiac_type" name="astrologer_wp__zodiac_type">';
    foreach ($zodiacTypes as $zodiacType) {
        echo '<option value="' . esc_attr($zodiacType) . '" ' . selected($zodiacType, $value, false) . '>' . esc_html($zodiacType) . '</option>';
    }
    echo '</select>';
    echo '<p class="description">' . esc_html__( 'Choose the type of zodiac to use. Tropical is the most common in Western astrology.', 'astrologerwp' ) . '</p>';
}

function astrologer_wp_sidereal_mode_callback() {
    $value = get_option('astrologer_wp__sidereal_mode', '');
    $siderealModes = KerykeionConstants::SIDEREAL_MODES;

    echo '<p id="siderealModeDisabledMessage">' . esc_html__( 'Sidereal modes are only available when the zodiac type is set to Sidereal.', 'astrologerwp' ) . '</p>';
    echo '<div id="siderealModeSelectWrapper">';
    echo '<select id="astrologer_wp__sidereal_mode" name="astrologer_wp__sidereal_mode">';
    foreach ($siderealModes as $mode) {
        echo '<option value="' . esc_attr($mode) . '" ' . selected($mode, $value, false) . '>' . esc_html($mode) . '</option>';
    }
    echo '</select>';
    echo '<p class="description">' . esc_html__( 'Select the sidereal mode (ayanamsha) to use. The most common sidereal modes are Fagan-Bradley or Lahiri. Select USER if you need a custom ayanamsha.', 'astrologerwp' ) . '</p>';
    echo '</div>';
}

function astrologer_wp_houses_system_field_callback() {
    $value = get_option('astrologer_wp__houses_system', 'P');
    $housesSystems = KerykeionConstants::HOUSES_SYSTEM_IDENTIFIERS;

    echo '<select id="astrologer_wp__houses_system" name="astrologer_wp__houses_system">';
    foreach ($housesSystems as $system_key => $system_name) {
        echo '<option value="' . esc_attr($system_key) . '" ' . selected($system_key, $value, false) . '>' . esc_html($system_key . ' - ' . $system_name) . '</option>';
    }
    echo '</select>';
    echo '<p class="description">' . esc_html__( 'Select the house system to use for the chart. Placidus (P) is the most commonly used.', 'astrologerwp' ) . '</p>';
}

function astrologer_wp_chart_language_field_callback() {
    $value = get_option('astrologer_wp__chart_language', 'EN');
    $languages = KerykeionConstants::KERYKEION_CHART_LANGUAGES;

    echo '<select id="astrologer_wp__chart_language" name="astrologer_wp__chart_language">';
    foreach ($languages as $language) {
        echo '<option value="' . esc_attr($language) . '" ' . selected($language, $value, false) . '>' . esc_html($language) . '</option>';
    }
    echo '</select>';
    echo '<p class="description">' . esc_html__( 'Select the language for the chart labels.', 'astrologerwp' ) . '</p>';
}

function astrologer_wp_perspective_type_callback() {
    $value = get_option('astrologer_wp__perspective_type', 'Apparent Geocentric');
    $perspectiveTypes = KerykeionConstants::PERSPECTIVE_TYPES;

    echo '<select id="astrologer_wp__perspective_type" name="astrologer_wp__perspective_type">';
    foreach ($perspectiveTypes as $perspectiveType) {
        echo '<option value="' . esc_attr($perspectiveType) . '" ' . selected($perspectiveType, $value, false) . '>' . esc_html($perspectiveType) . '</option>';
    }
    echo '</select>';
    echo '<p class="description">' . esc_html__( 'Select the astronomical perspective type. Apparent Geocentric is the standard for most astrology traditions.', 'astrologerwp' ) . '</p>';
}

// --- Rendering Fields ---

function astrologer_wp_chart_theme_field_callback() {
    $value = get_option('astrologer_wp__chart_theme', 'classic');
    $themes = KerykeionConstants::KERYKEION_CHART_THEMES;

    echo '<select id="astrologer_wp__chart_theme" name="astrologer_wp__chart_theme">';
    foreach ($themes as $themeName) {
        echo '<option value="' . esc_attr($themeName) . '" ' . selected($themeName, $value, false) . '>' . esc_html($themeName) . '</option>';
    }
    echo '</select>';
    echo '<p class="description">' . esc_html__( 'Select the visual theme for the chart SVG.', 'astrologerwp' ) . '</p>';
}

function astrologer_wp_chart_style_field_callback() {
    $value = get_option('astrologer_wp__chart_style', 'classic');
    $styles = KerykeionConstants::KERYKEION_CHART_STYLES;

    echo '<select id="astrologer_wp__chart_style" name="astrologer_wp__chart_style">';
    foreach ($styles as $styleName) {
        echo '<option value="' . esc_attr($styleName) . '" ' . selected($styleName, $value, false) . '>' . esc_html($styleName) . '</option>';
    }
    echo '</select>';
    echo '<p class="description">' . esc_html__( 'Classic is the traditional wheel layout. Modern uses concentric rings.', 'astrologerwp' ) . '</p>';
}

function astrologer_wp_wheel_only_field_callback() {
    $value = get_option('astrologer_wp__wheel_only_chart');
    echo '<input type="checkbox" id="astrologer_wp__wheel_only_chart" name="astrologer_wp__wheel_only_chart" value="1" ' . checked(1, $value, false) . ' />';
    echo '<p class="description">' . esc_html__( 'Display only the wheel chart without the aspect grid.', 'astrologerwp' ) . '</p>';
}

function astrologer_wp_split_chart_field_callback() {
    $value = get_option('astrologer_wp__split_chart');
    echo '<input type="checkbox" id="astrologer_wp__split_chart" name="astrologer_wp__split_chart" value="1" ' . checked(1, $value, false) . ' />';
    echo '<p class="description">' . esc_html__( 'Return wheel and aspect grid as separate SVGs, displayed one below the other.', 'astrologerwp' ) . '</p>';
}

function astrologer_wp_transparent_background_field_callback() {
    $value = get_option('astrologer_wp__transparent_background');
    echo '<input type="checkbox" id="astrologer_wp__transparent_background" name="astrologer_wp__transparent_background" value="1" ' . checked(1, $value, false) . ' />';
    echo '<p class="description">' . esc_html__( 'Render chart with transparent background instead of the theme default.', 'astrologerwp' ) . '</p>';
}

function astrologer_wp_double_chart_aspect_grid_type_field_callback() {
    $value = get_option('astrologer_wp__double_chart_aspect_grid_type', 'list');
    $types = KerykeionConstants::DOUBLE_CHART_ASPECT_GRID_TYPES;

    echo '<select id="astrologer_wp__double_chart_aspect_grid_type" name="astrologer_wp__double_chart_aspect_grid_type">';
    foreach ($types as $type) {
        echo '<option value="' . esc_attr($type) . '" ' . selected($type, $value, false) . '>' . esc_html($type) . '</option>';
    }
    echo '</select>';
    echo '<p class="description">' . esc_html__( 'Layout for double-chart (synastry/transit) aspect display: list (vertical) or table (grid matrix).', 'astrologerwp' ) . '</p>';
}

// --- Display Option Fields ---

function astrologer_wp_show_house_position_comparison_callback() {
    $value = get_option('astrologer_wp__show_house_position_comparison', '1');
    echo '<input type="checkbox" id="astrologer_wp__show_house_position_comparison" name="astrologer_wp__show_house_position_comparison" value="1" ' . checked(1, $value, false) . ' />';
    echo '<p class="description">' . esc_html__( 'Display the house comparison table next to the chart wheel.', 'astrologerwp' ) . '</p>';
}

function astrologer_wp_show_cusp_position_comparison_callback() {
    $value = get_option('astrologer_wp__show_cusp_position_comparison', '1');
    echo '<input type="checkbox" id="astrologer_wp__show_cusp_position_comparison" name="astrologer_wp__show_cusp_position_comparison" value="1" ' . checked(1, $value, false) . ' />';
    echo '<p class="description">' . esc_html__( 'Display the cusp position comparison table (for dual charts like synastry and transits).', 'astrologerwp' ) . '</p>';
}

function astrologer_wp_show_degree_indicators_callback() {
    $value = get_option('astrologer_wp__show_degree_indicators', '1');
    echo '<input type="checkbox" id="astrologer_wp__show_degree_indicators" name="astrologer_wp__show_degree_indicators" value="1" ' . checked(1, $value, false) . ' />';
    echo '<p class="description">' . esc_html__( 'Display radial lines and degree numbers for planet positions on the chart wheel.', 'astrologerwp' ) . '</p>';
}

function astrologer_wp_show_aspect_icons_callback() {
    $value = get_option('astrologer_wp__show_aspect_icons', '1');
    echo '<input type="checkbox" id="astrologer_wp__show_aspect_icons" name="astrologer_wp__show_aspect_icons" value="1" ' . checked(1, $value, false) . ' />';
    echo '<p class="description">' . esc_html__( 'Display aspect icons on the chart wheel aspect lines.', 'astrologerwp' ) . '</p>';
}

function astrologer_wp_show_zodiac_background_ring_callback() {
    $value = get_option('astrologer_wp__show_zodiac_background_ring', '1');
    echo '<input type="checkbox" id="astrologer_wp__show_zodiac_background_ring" name="astrologer_wp__show_zodiac_background_ring" value="1" ' . checked(1, $value, false) . ' />';
    echo '<p class="description">' . esc_html__( 'Show colored zodiac sign wedges on the wheel. Only affects the modern chart style.', 'astrologerwp' ) . '</p>';
}

// --- Admin Page ---

function astrologer_wp_admin_page() {
?>
    <div class="wrap">
        <h1>
            <?php esc_html_e( 'AstrologerWP', 'astrologerwp' ); ?>
        </h1>

        <h2><?php esc_html_e( 'Shortcodes Recap', 'astrologerwp' ); ?></h2>
        <p><?php esc_html_e( 'Use the following shortcodes to display various astrology elements on your pages:', 'astrologerwp' ); ?></p>
        <ul>
            <li>
                <code class="astrologer-wp-admin-shortcode">[astrologer_wp_birth_chart]</code>
                - <?php esc_html_e( 'Natal birth chart', 'astrologerwp' ); ?>
            </li>
            <li>
                <code class="astrologer-wp-admin-shortcode">[astrologer_wp_synastry_chart]</code>
                - <?php esc_html_e( 'Synastry (relationship) chart', 'astrologerwp' ); ?>
            </li>
            <li>
                <code class="astrologer-wp-admin-shortcode">[astrologer_wp_transit_chart]</code>
                - <?php esc_html_e( 'Transit analysis chart', 'astrologerwp' ); ?>
            </li>
            <li>
                <code class="astrologer-wp-admin-shortcode">[astrologer_wp_composite_chart]</code>
                - <?php esc_html_e( 'Composite (midpoint) chart', 'astrologerwp' ); ?>
            </li>
            <li>
                <code class="astrologer-wp-admin-shortcode">[astrologer_wp_solar_return_chart]</code>
                - <?php esc_html_e( 'Solar return chart', 'astrologerwp' ); ?>
            </li>
            <li>
                <code class="astrologer-wp-admin-shortcode">[astrologer_wp_lunar_return_chart]</code>
                - <?php esc_html_e( 'Lunar return chart', 'astrologerwp' ); ?>
            </li>
            <li>
                <code class="astrologer-wp-admin-shortcode">[astrologer_wp_moon_phase]</code>
                - <?php esc_html_e( 'Moon phase details', 'astrologerwp' ); ?>
            </li>
            <li>
                <code class="astrologer-wp-admin-shortcode">[astrologer_wp_now_chart]</code>
                - <?php esc_html_e( 'Current sky chart (UTC/Greenwich)', 'astrologerwp' ); ?>
            </li>
        </ul>
        <p>
            <?php
            echo wp_kses(
                sprintf(
                    /* translators: %s: URL to WordPress shortcode block guide */
                    __( '<a href="%s" target="_blank">Here\'s a guide</a> on how to use the shortcodes with the Block editor.', 'astrologerwp' ),
                    'https://wordpress.com/support/wordpress-editor/blocks/shortcode-block/'
                ),
                array(
                    'a' => array( 'href' => array(), 'target' => array() ),
                )
            );
            ?>
        </p>
        <br>

        <?php settings_errors(); ?>
        <form id="astrologerWpBirthChartAdminSettings" method="post" action="options.php">
            <?php
            settings_fields('astrologer_wp_settings');
            do_settings_sections('astrologer-wp');
            submit_button();
            ?>
        </form>
    </div>
<?php
}
