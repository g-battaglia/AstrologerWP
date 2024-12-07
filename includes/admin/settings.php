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
    register_setting('astrologer_wp_settings', 'astrologer_wp__api_key', 'sanitize_text_field');
    register_setting('astrologer_wp_settings', 'astrologer_wp__wheel_only_chart', 'sanitize_text_field');
    register_setting('astrologer_wp_settings', 'astrologer_wp__chart_theme', 'sanitize_text_field');
    register_setting('astrologer_wp_settings', 'astrologer_wp__geonames_username', 'sanitize_text_field');
    register_setting('astrologer_wp_settings', 'astrologer_wp__zodiac_type', 'sanitize_text_field');
    register_setting('astrologer_wp_settings', 'astrologer_wp__houses_system', 'sanitize_text_field');
    register_setting('astrologer_wp_settings', 'astrologer_wp__chart_language', 'sanitize_text_field');
    register_setting('astrologer_wp_settings', 'astrologer_wp__sidereal_mode', 'sanitize_text_field');
    register_setting('astrologer_wp_settings', 'astrologer_wp__perspective_type', 'sanitize_text_field');


    add_settings_section(
        'astrologer_wp_section',
        'AstrologerWP Settings',
        'astrologer_wp_section_callback',
        'astrologer-wp'
    );

    add_settings_field(
        'astrologer_wp__api_key',
        'Astrologer API Key',
        'astrologer_wp_api_key_field_callback',
        'astrologer-wp',
        'astrologer_wp_section'
    );

    add_settings_field(
        'astrologer_wp__chart_theme',
        'Chart Theme',
        'astrologer_wp_chart_theme_field_callback',
        'astrologer-wp',
        'astrologer_wp_section'
    );

    add_settings_field(
        'astrologer_wp__geonames_username',
        'Geonames Username',
        'astrologer_wp_geonames_username_field_callback',
        'astrologer-wp',
        'astrologer_wp_section'
    );

    add_settings_field(
        'astrologer_wp__zodiac_type',
        'Zodiac Type',
        'astrologer_wp_zodiac_type_field_callback',
        'astrologer-wp',
        'astrologer_wp_section'
    );

    add_settings_field(
        'astrologer_wp__houses_system',
        'Houses System',
        'astrologer_wp_houses_system_field_callback',
        'astrologer-wp',
        'astrologer_wp_section'
    );

    add_settings_field(
        'astrologer_wp__chart_language',
        'Chart Language',
        'astrologer_wp_chart_language_field_callback',
        'astrologer-wp',
        'astrologer_wp_section'
    );

    add_settings_field(
        'astrologer_wp__sidereal_mode',
        'Sidereal Modes',
        'astrologer_wp_sidereal_mode_callback',
        'astrologer-wp',
        'astrologer_wp_section'
    );

    add_settings_field(
        'astrologer_wp__perspective_type',
        'Perspective Type',
        'astrologer_wp_perspective_type_callback',
        'astrologer-wp',
        'astrologer_wp_section'
    );

    add_settings_field(
        'astrologer_wp__wheel_only_chart',
        'Wheel Only Chart',
        'astrologer_wp_wheel_only_field_callback',
        'astrologer-wp',
        'astrologer_wp_section'
    );
}

function astrologer_wp_section_callback() {
    // echo '<p> Settings for the AstrologerWP plugin.</p>';
}

function astrologer_wp_api_key_field_callback() {
    $value = get_option('astrologer_wp__api_key');
    echo '<input type="text" id="astrologer_wp__api_key" name="astrologer_wp__api_key" value="' . esc_attr($value) . '" required/>';
    echo '<p class="description">
        Enter your Astrologer API Key here.
        <br>
        You can get your API Key by <a href="https://rapidapi.com/gbattaglia/api/astrologer/pricing" target="_blank">signing up here</a>.
    </p>';
}

function astrologer_wp_wheel_only_field_callback() {
    $value = get_option('astrologer_wp__wheel_only_chart');
    echo '<input type="checkbox" id="astrologer_wp__wheel_only_chart" name="astrologer_wp__wheel_only_chart" value="1" ' . checked(1, $value, false) . ' />';
    echo '<p class="description">Check this box if you want to display only the wheel chart.</p>';
}

function astrologer_wp_geonames_username_field_callback() {
    $value = get_option('astrologer_wp__geonames_username');
    echo '<input type="text" id="astrologer_wp__geonames_username" name="astrologer_wp__geonames_username" value="' . esc_attr($value) . '" required/>';
    echo '<p class="description">
        Enter your Geonames username for location services.
        <br>
        You can get your username for free by <a href="http://www.geonames.org/login" target="_blank">signing up here</a>.
        <br>
        Geonames is used to get the timezone and coordinates of the location and has no affiliation with the Astrologer service.
        </p>';
}

function astrologer_wp_chart_theme_field_callback() {
    $value = get_option('astrologer_wp__chart_theme');
    $themes = KerykeionConstants::KERYKEION_CHART_THEMES;

    echo '<select id="astrologer_wp__chart_theme" name="astrologer_wp__chart_theme">';
    foreach ($themes as $themeName) {
        echo '<option value="' . esc_attr($themeName) . '" ' . selected($themeName, $value, false) . '>' . esc_html($themeName) . '</option>';
    }
    echo '</select>';
    echo '<p class="description">Select the theme for the chart.</p>';
}

function astrologer_wp_zodiac_type_field_callback() {
    $value = get_option('astrologer_wp__zodiac_type', 'tropical');
    $zodiacTypes = KerykeionConstants::ZODIAC_TYPES;

    echo '<select id="astrologer_wp__zodiac_type" name="astrologer_wp__zodiac_type">';
    foreach ($zodiacTypes as $zodiacType) {
        echo '<option value="' . esc_attr($zodiacType) . '" ' . selected($zodiacType, $value, false) . '>' . esc_html($zodiacType) . '</option>';
    }
    echo '</select>';
    echo '<p class="description">Choose the type of zodiac to use.</p>';
}

function astrologer_wp_houses_system_field_callback() {
    $value = get_option('astrologer_wp__houses_system');
    $housesSystems = KerykeionConstants::HOUSES_SYSTEM_IDENTIFIERS;

    echo '<select id="astrologer_wp__houses_system" name="astrologer_wp__houses_system">';
    foreach ($housesSystems as $system_key => $system_name) {
        echo '<option value="' . esc_attr($system_key) . '" ' . selected($system_key, $value, false) . '>' . esc_html($system_name) . '</option>';
    }
    echo '</select>';
    echo '<p class="description">Select the house system to use for the chart.</p>';
}

function astrologer_wp_chart_language_field_callback() {
    $value = get_option('astrologer_wp__chart_language', 'en');
    $languages = KerykeionConstants::KERYKEION_CHART_LANGUAGES;

    echo '<select id="astrologer_wp__chart_language" name="astrologer_wp__chart_language">';
    foreach ($languages as $language) {
        echo '<option value="' . esc_attr($language) . '" ' . selected($language, $value, false) . '>' . esc_html($language) . '</option>';
    }
    echo '</select>';
    echo '<p class="description">Select the language for the chart.</p>';
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
        Select the sidereal mode to use.
        The most common sidereal mode is Fagan-Bradley or Lahiri.
    </p>';
    echo '</div>';
}

function astrologer_wp_perspective_type_callback() {
    $value = get_option('astrologer_wp__perspective_type', 'Apparent Geocentric');
    $perspectiveTypes = KerykeionConstants::PERSPECTIVE_TYPES;

    echo '<select id="astrologer_wp__perspective_type" name="astrologer_wp__perspective_type">';
    foreach ($perspectiveTypes as $perspectiveType) {
        echo '<option value="' . esc_attr($perspectiveType) . '" ' . selected($perspectiveType, $value, false) . '>' . esc_html($perspectiveType) . '</option>';
    }
    echo '</select>';
    echo '<p class="description">Select the perspective type for the chart.</p>';
}

function astrologer_wp_admin_page() {
?>
    <div class="wrap">
        <h1>
            AstrologerWP
        </h1>

        <h2>Shortcodes Recap</h2>
        <p>Use the following shortcodes to display various elements:</p>
        <ul>
            <li>
                <code id="astrologerWpBirthChartAdminShortCode" class=".astrologer-wp-birth-chart-admin-shortcode">[astrologer_wp_birth_chart]</code>
                - Displays the birth chart.
            </li>
            <li>
                <code id="astrologerWpSynastryChartAdminShortCode" class=".astrologer-wp-synastry-chart-admin-shortcode">[astrologer_wp_synastry_chart]</code>
                - Displays the synastry chart.
            </li>
            <li>
                <code id="astrologerWpTranistChartAdminShortCode" class=".astrologer-wp-transit-chart-admin-shortcode">[astrologer_wp_transit_chart]</code>
                - Displays the transit chart.
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
