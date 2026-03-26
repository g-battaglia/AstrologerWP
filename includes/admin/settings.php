<?php

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
        'API Credentials',
        'astrologer_wp_section_credentials_callback',
        'astrologer-wp'
    );

    add_settings_field('astrologer_wp__api_key', 'Astrologer API Key', 'astrologer_wp_api_key_field_callback', 'astrologer-wp', 'astrologer_wp_section_credentials');
    add_settings_field('astrologer_wp__geonames_username', 'Geonames Username', 'astrologer_wp_geonames_username_field_callback', 'astrologer-wp', 'astrologer_wp_section_credentials');

    // --- Section: Chart Calculation ---
    add_settings_section(
        'astrologer_wp_section_calculation',
        'Chart Calculation',
        'astrologer_wp_section_calculation_callback',
        'astrologer-wp'
    );

    add_settings_field('astrologer_wp__zodiac_type', 'Zodiac Type', 'astrologer_wp_zodiac_type_field_callback', 'astrologer-wp', 'astrologer_wp_section_calculation');
    add_settings_field('astrologer_wp__sidereal_mode', 'Sidereal Mode', 'astrologer_wp_sidereal_mode_callback', 'astrologer-wp', 'astrologer_wp_section_calculation');
    add_settings_field('astrologer_wp__houses_system', 'Houses System', 'astrologer_wp_houses_system_field_callback', 'astrologer-wp', 'astrologer_wp_section_calculation');
    add_settings_field('astrologer_wp__chart_language', 'Chart Language', 'astrologer_wp_chart_language_field_callback', 'astrologer-wp', 'astrologer_wp_section_calculation');
    add_settings_field('astrologer_wp__perspective_type', 'Perspective Type', 'astrologer_wp_perspective_type_callback', 'astrologer-wp', 'astrologer_wp_section_calculation');

    // --- Section: Chart Rendering ---
    add_settings_section(
        'astrologer_wp_section_rendering',
        'Chart Rendering',
        'astrologer_wp_section_rendering_callback',
        'astrologer-wp'
    );

    add_settings_field('astrologer_wp__chart_theme', 'Chart Theme', 'astrologer_wp_chart_theme_field_callback', 'astrologer-wp', 'astrologer_wp_section_rendering');
    add_settings_field('astrologer_wp__chart_style', 'Chart Style', 'astrologer_wp_chart_style_field_callback', 'astrologer-wp', 'astrologer_wp_section_rendering');
    add_settings_field('astrologer_wp__wheel_only_chart', 'Wheel Only Chart', 'astrologer_wp_wheel_only_field_callback', 'astrologer-wp', 'astrologer_wp_section_rendering');
    add_settings_field('astrologer_wp__split_chart', 'Split Chart', 'astrologer_wp_split_chart_field_callback', 'astrologer-wp', 'astrologer_wp_section_rendering');
    add_settings_field('astrologer_wp__transparent_background', 'Transparent Background', 'astrologer_wp_transparent_background_field_callback', 'astrologer-wp', 'astrologer_wp_section_rendering');
    add_settings_field('astrologer_wp__double_chart_aspect_grid_type', 'Aspect Grid Type', 'astrologer_wp_double_chart_aspect_grid_type_field_callback', 'astrologer-wp', 'astrologer_wp_section_rendering');

    // --- Section: Chart Display Options ---
    add_settings_section(
        'astrologer_wp_section_display',
        'Chart Display Options',
        'astrologer_wp_section_display_callback',
        'astrologer-wp'
    );

    add_settings_field('astrologer_wp__show_house_position_comparison', 'Show House Position Comparison', 'astrologer_wp_show_house_position_comparison_callback', 'astrologer-wp', 'astrologer_wp_section_display');
    add_settings_field('astrologer_wp__show_cusp_position_comparison', 'Show Cusp Position Comparison', 'astrologer_wp_show_cusp_position_comparison_callback', 'astrologer-wp', 'astrologer_wp_section_display');
    add_settings_field('astrologer_wp__show_degree_indicators', 'Show Degree Indicators', 'astrologer_wp_show_degree_indicators_callback', 'astrologer-wp', 'astrologer_wp_section_display');
    add_settings_field('astrologer_wp__show_aspect_icons', 'Show Aspect Icons', 'astrologer_wp_show_aspect_icons_callback', 'astrologer-wp', 'astrologer_wp_section_display');
    add_settings_field('astrologer_wp__show_zodiac_background_ring', 'Show Zodiac Background Ring', 'astrologer_wp_show_zodiac_background_ring_callback', 'astrologer-wp', 'astrologer_wp_section_display');
}

// --- Section Callbacks ---

function astrologer_wp_section_credentials_callback() {
    echo '<p>Enter your API credentials to connect to the Astrologer API and Geonames location services.</p>';
}

function astrologer_wp_section_calculation_callback() {
    echo '<p>Configure the astrological calculation parameters used for all charts.</p>';
}

function astrologer_wp_section_rendering_callback() {
    echo '<p>Customize how the SVG charts are rendered visually.</p>';
}

function astrologer_wp_section_display_callback() {
    echo '<p>Toggle individual display elements on the chart.</p>';
}

// --- Credential Fields ---

function astrologer_wp_api_key_field_callback() {
    $value = get_option('astrologer_wp__api_key');
    echo '<input type="text" id="astrologer_wp__api_key" name="astrologer_wp__api_key" value="' . esc_attr($value) . '" class="regular-text" required/>';
    echo '<p class="description">
        Enter your Astrologer API Key here.
        <br>
        You can get your API Key by <a href="https://rapidapi.com/gbattaglia/api/astrologer/pricing" target="_blank">signing up here</a>.
    </p>';
}

function astrologer_wp_geonames_username_field_callback() {
    $value = get_option('astrologer_wp__geonames_username');
    echo '<input type="text" id="astrologer_wp__geonames_username" name="astrologer_wp__geonames_username" value="' . esc_attr($value) . '" class="regular-text" required/>';
    echo '<p class="description">
        Enter your Geonames username for location services.
        <br>
        You can get your username for free by <a href="http://www.geonames.org/login" target="_blank">signing up here</a>.
        <br>
        Geonames is used to get the timezone and coordinates of the location and has no affiliation with the Astrologer service.
        </p>';
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
    echo '<p class="description">Choose the type of zodiac to use. Tropical is the most common in Western astrology.</p>';
}

function astrologer_wp_sidereal_mode_callback() {
    $value = get_option('astrologer_wp__sidereal_mode', '');
    $siderealModes = KerykeionConstants::SIDEREAL_MODES;

    echo '<p id="siderealModeDisabledMessage">Sidereal modes are only available when the zodiac type is set to Sidereal.</p>';
    echo '<div id="siderealModeSelectWrapper">';
    echo '<select id="astrologer_wp__sidereal_mode" name="astrologer_wp__sidereal_mode">';
    foreach ($siderealModes as $mode) {
        echo '<option value="' . esc_attr($mode) . '" ' . selected($mode, $value, false) . '>' . esc_html($mode) . '</option>';
    }
    echo '</select>';
    echo '
    <p class="description">
        Select the sidereal mode (ayanamsha) to use.
        The most common sidereal modes are Fagan-Bradley or Lahiri.
        Select USER if you need a custom ayanamsha.
    </p>';
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
    echo '<p class="description">Select the house system to use for the chart. Placidus (P) is the most commonly used.</p>';
}

function astrologer_wp_chart_language_field_callback() {
    $value = get_option('astrologer_wp__chart_language', 'EN');
    $languages = KerykeionConstants::KERYKEION_CHART_LANGUAGES;

    echo '<select id="astrologer_wp__chart_language" name="astrologer_wp__chart_language">';
    foreach ($languages as $language) {
        echo '<option value="' . esc_attr($language) . '" ' . selected($language, $value, false) . '>' . esc_html($language) . '</option>';
    }
    echo '</select>';
    echo '<p class="description">Select the language for the chart labels.</p>';
}

function astrologer_wp_perspective_type_callback() {
    $value = get_option('astrologer_wp__perspective_type', 'Apparent Geocentric');
    $perspectiveTypes = KerykeionConstants::PERSPECTIVE_TYPES;

    echo '<select id="astrologer_wp__perspective_type" name="astrologer_wp__perspective_type">';
    foreach ($perspectiveTypes as $perspectiveType) {
        echo '<option value="' . esc_attr($perspectiveType) . '" ' . selected($perspectiveType, $value, false) . '>' . esc_html($perspectiveType) . '</option>';
    }
    echo '</select>';
    echo '<p class="description">Select the astronomical perspective type. Apparent Geocentric is the standard for most astrology traditions.</p>';
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
    echo '<p class="description">Select the visual theme for the chart SVG.</p>';
}

function astrologer_wp_chart_style_field_callback() {
    $value = get_option('astrologer_wp__chart_style', 'classic');
    $styles = KerykeionConstants::KERYKEION_CHART_STYLES;

    echo '<select id="astrologer_wp__chart_style" name="astrologer_wp__chart_style">';
    foreach ($styles as $styleName) {
        echo '<option value="' . esc_attr($styleName) . '" ' . selected($styleName, $value, false) . '>' . esc_html($styleName) . '</option>';
    }
    echo '</select>';
    echo '<p class="description">Classic is the traditional wheel layout. Modern uses concentric rings.</p>';
}

function astrologer_wp_wheel_only_field_callback() {
    $value = get_option('astrologer_wp__wheel_only_chart');
    echo '<input type="checkbox" id="astrologer_wp__wheel_only_chart" name="astrologer_wp__wheel_only_chart" value="1" ' . checked(1, $value, false) . ' />';
    echo '<p class="description">Display only the wheel chart without the aspect grid.</p>';
}

function astrologer_wp_split_chart_field_callback() {
    $value = get_option('astrologer_wp__split_chart');
    echo '<input type="checkbox" id="astrologer_wp__split_chart" name="astrologer_wp__split_chart" value="1" ' . checked(1, $value, false) . ' />';
    echo '<p class="description">Return wheel and aspect grid as separate SVGs, displayed one below the other.</p>';
}

function astrologer_wp_transparent_background_field_callback() {
    $value = get_option('astrologer_wp__transparent_background');
    echo '<input type="checkbox" id="astrologer_wp__transparent_background" name="astrologer_wp__transparent_background" value="1" ' . checked(1, $value, false) . ' />';
    echo '<p class="description">Render chart with transparent background instead of the theme default.</p>';
}

function astrologer_wp_double_chart_aspect_grid_type_field_callback() {
    $value = get_option('astrologer_wp__double_chart_aspect_grid_type', 'list');
    $types = KerykeionConstants::DOUBLE_CHART_ASPECT_GRID_TYPES;

    echo '<select id="astrologer_wp__double_chart_aspect_grid_type" name="astrologer_wp__double_chart_aspect_grid_type">';
    foreach ($types as $type) {
        echo '<option value="' . esc_attr($type) . '" ' . selected($type, $value, false) . '>' . esc_html($type) . '</option>';
    }
    echo '</select>';
    echo '<p class="description">Layout for double-chart (synastry/transit) aspect display: list (vertical) or table (grid matrix).</p>';
}

// --- Display Option Fields ---

function astrologer_wp_show_house_position_comparison_callback() {
    $value = get_option('astrologer_wp__show_house_position_comparison', '1');
    echo '<input type="checkbox" id="astrologer_wp__show_house_position_comparison" name="astrologer_wp__show_house_position_comparison" value="1" ' . checked(1, $value, false) . ' />';
    echo '<p class="description">Display the house comparison table next to the chart wheel.</p>';
}

function astrologer_wp_show_cusp_position_comparison_callback() {
    $value = get_option('astrologer_wp__show_cusp_position_comparison', '1');
    echo '<input type="checkbox" id="astrologer_wp__show_cusp_position_comparison" name="astrologer_wp__show_cusp_position_comparison" value="1" ' . checked(1, $value, false) . ' />';
    echo '<p class="description">Display the cusp position comparison table (for dual charts like synastry and transits).</p>';
}

function astrologer_wp_show_degree_indicators_callback() {
    $value = get_option('astrologer_wp__show_degree_indicators', '1');
    echo '<input type="checkbox" id="astrologer_wp__show_degree_indicators" name="astrologer_wp__show_degree_indicators" value="1" ' . checked(1, $value, false) . ' />';
    echo '<p class="description">Display radial lines and degree numbers for planet positions on the chart wheel.</p>';
}

function astrologer_wp_show_aspect_icons_callback() {
    $value = get_option('astrologer_wp__show_aspect_icons', '1');
    echo '<input type="checkbox" id="astrologer_wp__show_aspect_icons" name="astrologer_wp__show_aspect_icons" value="1" ' . checked(1, $value, false) . ' />';
    echo '<p class="description">Display aspect icons on the chart wheel aspect lines.</p>';
}

function astrologer_wp_show_zodiac_background_ring_callback() {
    $value = get_option('astrologer_wp__show_zodiac_background_ring', '1');
    echo '<input type="checkbox" id="astrologer_wp__show_zodiac_background_ring" name="astrologer_wp__show_zodiac_background_ring" value="1" ' . checked(1, $value, false) . ' />';
    echo '<p class="description">Show colored zodiac sign wedges on the wheel. Only affects the modern chart style.</p>';
}

// --- Admin Page ---

function astrologer_wp_admin_page() {
?>
    <div class="wrap">
        <h1>
            AstrologerWP
        </h1>

        <h2>Shortcodes Recap</h2>
        <p>Use the following shortcodes to display various astrology elements on your pages:</p>
        <ul>
            <li>
                <code class="astrologer-wp-admin-shortcode">[astrologer_wp_birth_chart]</code>
                - Natal birth chart
            </li>
            <li>
                <code class="astrologer-wp-admin-shortcode">[astrologer_wp_synastry_chart]</code>
                - Synastry (relationship) chart
            </li>
            <li>
                <code class="astrologer-wp-admin-shortcode">[astrologer_wp_transit_chart]</code>
                - Transit analysis chart
            </li>
            <li>
                <code class="astrologer-wp-admin-shortcode">[astrologer_wp_composite_chart]</code>
                - Composite (midpoint) chart
            </li>
            <li>
                <code class="astrologer-wp-admin-shortcode">[astrologer_wp_solar_return_chart]</code>
                - Solar return chart
            </li>
            <li>
                <code class="astrologer-wp-admin-shortcode">[astrologer_wp_lunar_return_chart]</code>
                - Lunar return chart
            </li>
            <li>
                <code class="astrologer-wp-admin-shortcode">[astrologer_wp_moon_phase]</code>
                - Moon phase details
            </li>
            <li>
                <code class="astrologer-wp-admin-shortcode">[astrologer_wp_now_chart]</code>
                - Current sky chart (UTC/Greenwich)
            </li>
        </ul>
        <p>
            <a href="https://wordpress.com/support/wordpress-editor/blocks/shortcode-block/" target="_blank"> Here's a guide</a> on how to use the shortcodes with the Block editor.
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
