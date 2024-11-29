<?php
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
    // Register settings
    register_setting('astrologer_wp_settings', 'astrologer_wp_name');
    register_setting('astrologer_wp_settings', 'astrologer_wp_date');
    register_setting('astrologer_wp_settings', 'astrologer_wp_timezone');
    register_setting('astrologer_wp_settings', 'astrologer_wp_longitude');
    register_setting('astrologer_wp_settings', 'astrologer_wp_latitude');
    register_setting('astrologer_wp_settings', 'astrologer_wp__wheel_only_chart');
    register_setting('astrologer_wp_settings', 'astrologer_wp__api_key');

    // Register a new section in the "astrologer_wp" page.
    add_settings_section(
        'astrologer_wp_section',
        'My Plugin Settings',
        'astrologer_wp_section_callback',
        'astrologer-wp'
    );

    // Register fields in the "astrologer_wp_section" section.
    add_settings_field(
        'astrologer_wp_name',
        'Nome',
        'astrologer_wp_name_field_callback',
        'astrologer-wp',
        'astrologer_wp_section'
    );

    add_settings_field(
        'astrologer_wp_date',
        'Data e Ora',
        'astrologer_wp_date_field_callback',
        'astrologer-wp',
        'astrologer_wp_section'
    );

    add_settings_field(
        'astrologer_wp_timezone',
        'Fuso Orario',
        'astrologer_wp_timezone_field_callback',
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

    // Rapid API Astrologer API Key
    add_settings_field(
        'astrologer_wp_longitude',
        'Astrologer API Key',
        'astrologer_wp__api_key_field_callback',
        'astrologer-wp',
        'astrologer_wp_section'
    );
}

function astrologer_wp_section_callback() {
    echo '<p>
        Settings for the AstrologerWP plugin.
    </p>';
}

function astrologer_wp_name_field_callback() {
    $value = get_option('astrologer_wp_name');
    echo '<input type="text" id="astrologer_wp_name" name="astrologer_wp_name" value="' . esc_attr($value) . '" />';
}

function astrologer_wp_date_field_callback() {
    $value = get_option('astrologer_wp_date');
    echo '<input type="datetime-local" id="astrologer_wp_date" name="astrologer_wp_date" value="' . esc_attr($value) . '" />';
}

function astrologer_wp_timezone_field_callback() {
    $value = get_option('astrologer_wp_timezone');
    $timezones = timezone_identifiers_list();
    echo '<select id="astrologer_wp_timezone" name="astrologer_wp_timezone">';
    foreach ($timezones as $timezone) {
        echo '<option value="' . esc_attr($timezone) . '" ' . selected($value, $timezone, false) . '>' . esc_html($timezone) . '</option>';
    }
    echo '</select>';
}

function astrologer_wp_longitude_field_callback() {
    $value = get_option('astrologer_wp_longitude');
    echo '<input type="number" step="0.000001" id="astrologer_wp_longitude" name="astrologer_wp_longitude" value="' . esc_attr($value) . '" />';
}

function astrologer_wp_wheel_only_field_callback() {
    $value = get_option('astrologer_wp__wheel_only_chart');
    echo '<input type="checkbox" id="astrologer_wp__wheel_only_chart" name="astrologer_wp__wheel_only_chart" value="1" ' . checked(1, $value, false) . ' />';
}

function astrologer_wp__api_key_field_callback() {
    $value = get_option('astrologer_wp__api_key');
    echo '<input type="text" id="astrologer_wp__api_key" name="astrologer_wp__api_key" value="' . esc_attr($value) . '" required/>';
}

function astrologer_wp_sanitize_longitude($input) {
    if (is_numeric($input) && $input >= -180 && $input <= 180) {
        return $input;
    }
    add_settings_error('astrologer_wp_longitude', 'invalid-longitude', 'Please enter a valid longitude between -180 and 180.');
    return get_option('astrologer_wp_longitude');
}

function astrologer_wp_sanitize_latitude($input) {
    if (is_numeric($input) && $input >= -90 && $input <= 90) {
        return $input;
    }
    add_settings_error('astrologer_wp_latitude', 'invalid-latitude', 'Please enter a valid latitude between -90 and 90.');
    return get_option('astrologer_wp_latitude');
}

function astrologer_wp_admin_page() {
    ?>
    <div class="wrap">
        <h1>
            AstrologerWP
        </h1>
        <?php settings_errors(); ?>
        <form id="myPluginForm" method="post" action="options.php">
            <?php
            settings_fields('astrologer_wp_settings');
            do_settings_sections('astrologer-wp');
            submit_button();
            ?>
        </form>
        <h2>Preview</h2>
        <div id="myPluginPreview">
            <p id="previewName">
                <?php echo esc_html(get_option('astrologer_wp_name')); ?>
            </p>
            <p id="previewDate">
                <?php echo esc_html(get_option('astrologer_wp_date')); ?>
            </p>
            <p id="previewTime">
            </p>
            <p id="previewTimezone">
                <?php echo esc_html(get_option('astrologer_wp_timezone')); ?>
            </p>
            <p id="previewLongitude">
                <?php echo esc_html(get_option('astrologer_wp_longitude')); ?>
            </p>
            <p id="previewLatitude">
                <?php echo esc_html(get_option('astrologer_wp_latitude')); ?>
            </p>
        </div>
    </div>
    <?php
}
