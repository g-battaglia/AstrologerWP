<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use AstrologerWP\Utils\AstrologerApiAdapter;
use AstrologerWP\Utils\Subject;

add_shortcode('astrologer_wp_birth_chart', 'astrologerWpBirthChartShortCode');
function astrologerWpBirthChartShortCode() {
    $apiKey = get_option('astrologer_wp__api_key');
    $chartName = '';
    $datetime = '';
    $longitude = '';
    $latitude = '';
    $city = '';
    $nation = '';
    $timezone = '';
    $zodiacType = get_option('astrologer_wp__zodiac_type', 'Tropical');
    $houseSystem = get_option('astrologer_wp__houses_system', 'P');
    $siderealMode = get_option('astrologer_wp__sidereal_mode', '');
    $perspectiveType = get_option('astrologer_wp__perspective_type', 'Apparent Geocentric');

    $chartHtml = '';
    $error = null;

    if (
        isset($_GET['chartName'])
        && isset($_GET['datetime'])
        && isset($_GET['longitude'])
        && isset($_GET['latitude'])
        && isset($_GET['city'])
        && isset($_GET['nation'])
        && isset($_GET['timezone'])
        && isset($_GET['_wpnonce'])
        && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'astrologer_wp_birth_chart')
    ) {
        $chartName = sanitize_text_field(wp_unslash($_GET['chartName']));
        $datetime = sanitize_text_field(wp_unslash($_GET['datetime']));
        $longitude = (float) sanitize_text_field(wp_unslash($_GET['longitude']));
        $latitude = (float) sanitize_text_field(wp_unslash($_GET['latitude']));
        $city = sanitize_text_field(wp_unslash($_GET['city']));
        $nation = sanitize_text_field(wp_unslash($_GET['nation']));
        $timezone = sanitize_text_field(wp_unslash($_GET['timezone']));

        $datetimeObject = new DateTime($datetime);
        $year = (int) $datetimeObject->format('Y');
        $month = (int) $datetimeObject->format('m');
        $day = (int) $datetimeObject->format('d');
        $hour = (int) $datetimeObject->format('H');
        $minute = (int) $datetimeObject->format('i');

        $subject = new Subject(
            $chartName,
            $year,
            $month,
            $day,
            $hour,
            $minute,
            $longitude,
            $latitude,
            $city,
            $nation,
            $timezone,
            $zodiacType,
            $houseSystem,
            $siderealMode,
            $perspectiveType
        );

        $astrologerApiAdapter = new AstrologerApiAdapter($apiKey);
        $data = $astrologerApiAdapter->getBirthChart($subject);

        if (!empty($data['error'])) {
            $error = $data['error'];
        } else {
            $chartHtml = astrologer_wp_render_chart($data, 'astrologerWpBirthChartWrapper', 'astrologerBirthChart');
        }
    }

    ob_start();
?>
    <div id="astrologerWpBirthChart" data-bs-theme="dark" class="bg-primary">
        <?php if (!empty($error)): ?>
            <div id="astrologerWpBirthChartError" class="alert alert-danger" role="alert">
                <?php echo esc_html($error); ?>
            </div>
        <?php endif; ?>
        <?php echo wp_kses_post($chartHtml); ?>
        <p class="subject-title"><?php esc_html_e('Subject Data', 'astrologerwp'); ?></p>
        <form id="astrologerWpBirthChartForm" method="get">
            <?php wp_nonce_field('astrologer_wp_birth_chart'); ?>
            <input id="astrologerWpBirthChartNameInput" class="form-control"
                type="text" name="chartName" placeholder="<?php echo esc_attr__('Enter name', 'astrologerwp'); ?>" required value="<?php echo esc_attr($chartName); ?>">
            <input id="astrologerWpBirthChartDatetimeInput" class="form-control"
                type="datetime-local" name="datetime" placeholder="<?php echo esc_attr__('Enter date and time', 'astrologerwp'); ?>" required value="<?php echo esc_attr($datetime); ?>"
                min="1801-01-01T00:00" max="2100-12-31T23:59">

            <div class="astrologer-wp-city-wrapper">
                <input id="astrologerWpBirthChartCityInput" class="form-control" autocomplete="off"
                    type="text" name="city" placeholder="<?php echo esc_attr__('Enter city', 'astrologerwp'); ?>" required value="<?php echo esc_attr($city); ?>">
                <ul id="astrologerWpBirthChartCitySuggestions" class="suggestions dropdown-menu form-control" role="listbox">
                </ul>
            </div>

            <!-- Hidden inputs -->
            <input id="astrologerWpBirthChartLongitudeInput" class="form-control"
                type="hidden" name="longitude" required value="<?php echo esc_attr($longitude); ?>">
            <input id="astrologerWpBirthChartLatitudeInput" class="form-control"
                type="hidden" name="latitude" required value="<?php echo esc_attr($latitude); ?>">
            <input id="astrologerWpBirthChartNationInput" class="form-control"
                type="hidden" name="nation" required value="<?php echo esc_attr($nation); ?>">
            <input id="astrologerWpBirthChartTimezoneInput" class="form-control"
                type="hidden" name="timezone" required value="<?php echo esc_attr($timezone); ?>">

            <!-- Submit button -->
            <button type="submit" class="btn"><?php esc_html_e('Get Birth Chart', 'astrologerwp'); ?></button>
        </form>
    </div>
<?php
    $output = ob_get_clean();
    $output = str_replace(array("\n", "\r"), '', $output);
    return $output;
}
