<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use AstrologerWP\Utils\AstrologerApiAdapter;
use AstrologerWP\Utils\Subject;

add_shortcode('astrologer_wp_lunar_return_chart', 'astrologerWpLunarReturnChartShortCode');
function astrologerWpLunarReturnChartShortCode() {
    $apiKey = get_option('astrologer_wp__api_key');
    $chartName = '';
    $datetime = '';
    $longitude = '';
    $latitude = '';
    $city = '';
    $nation = '';
    $timezone = '';
    $returnYear = '';
    $returnMonth = '';
    $returnLongitude = '';
    $returnLatitude = '';
    $returnCity = '';
    $returnNation = '';
    $returnTimezone = '';
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
        && isset($_GET['returnYear'])
        && isset($_GET['returnMonth'])
        && isset($_GET['_wpnonce'])
        && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'astrologer_wp_lunar_return_chart')
    ) {
        $chartName = sanitize_text_field(wp_unslash($_GET['chartName']));
        $datetime = sanitize_text_field(wp_unslash($_GET['datetime']));
        $longitude = (float) sanitize_text_field(wp_unslash($_GET['longitude']));
        $latitude = (float) sanitize_text_field(wp_unslash($_GET['latitude']));
        $city = sanitize_text_field(wp_unslash($_GET['city']));
        $nation = sanitize_text_field(wp_unslash($_GET['nation']));
        $timezone = sanitize_text_field(wp_unslash($_GET['timezone']));
        $returnYear = (int) sanitize_text_field(wp_unslash($_GET['returnYear']));
        $returnMonth = (int) sanitize_text_field(wp_unslash($_GET['returnMonth']));

        $datetimeObject = new DateTime($datetime);
        $year = (int) $datetimeObject->format('Y');
        $month = (int) $datetimeObject->format('m');
        $day = (int) $datetimeObject->format('d');
        $hour = (int) $datetimeObject->format('H');
        $minute = (int) $datetimeObject->format('i');

        $subject = new Subject(
            $chartName, $year, $month, $day, $hour, $minute,
            $longitude, $latitude, $city, $nation, $timezone,
            $zodiacType, $houseSystem, $siderealMode, $perspectiveType
        );

        // Build optional return location
        $returnLocation = null;
        if (!empty($_GET['returnLongitude']) && !empty($_GET['returnLatitude']) && !empty($_GET['returnTimezone'])) {
            $returnLongitude = (float) sanitize_text_field(wp_unslash($_GET['returnLongitude']));
            $returnLatitude = (float) sanitize_text_field(wp_unslash($_GET['returnLatitude']));
            $returnCity = sanitize_text_field(wp_unslash($_GET['returnCity']));
            $returnNation = sanitize_text_field(wp_unslash($_GET['returnNation']));
            $returnTimezone = sanitize_text_field(wp_unslash($_GET['returnTimezone']));

            $returnLocation = [
                'longitude' => $returnLongitude,
                'latitude' => $returnLatitude,
                'city' => $returnCity,
                'nation' => $returnNation,
                'timezone' => $returnTimezone,
            ];
        }

        $astrologerApiAdapter = new AstrologerApiAdapter($apiKey);
        $data = $astrologerApiAdapter->getLunarReturnChart($subject, $returnYear, $returnMonth, null, 'dual', $returnLocation);

        if (!empty($data['error'])) {
            $error = $data['error'];
        } else {
            $chartHtml = astrologer_wp_render_chart($data, 'astrologerWpLunarReturnChartWrapper', 'astrologerLunarReturnChart');
        }
    }

    $currentYear = (int) date('Y');
    $currentMonth = (int) date('m');

    ob_start();
?>
    <div id="astrologerWpLunarReturnChart" data-bs-theme="dark" class="bg-primary">
        <?php if (!empty($error)): ?>
            <div id="astrologerWpLunarReturnChartError" class="alert alert-danger" role="alert">
                <?php echo esc_html($error); ?>
            </div>
        <?php endif; ?>
        <?php echo $chartHtml; ?>
        <form id="astrologerWpLunarReturnChartForm" method="get">
            <?php wp_nonce_field('astrologer_wp_lunar_return_chart'); ?>
            <div class="subjects-data-wrapper">
                <div id="astrologerWpLunarReturnChartSubjectData" class="subject-data birth-subject-data">
                    <p class="subject-title">Birth Data</p>
                    <input id="astrologerWpLunarReturnChartNameInput" class="form-control"
                        type="text" name="chartName" placeholder="Enter name" required value="<?php echo esc_attr($chartName); ?>">
                    <input id="astrologerWpLunarReturnChartDatetimeInput" class="form-control"
                        type="datetime-local" name="datetime" placeholder="Enter birth date and time" required value="<?php echo esc_attr($datetime); ?>"
                        min="1801-01-01T00:00" max="2100-12-31T23:59">

                    <div class="astrologer-wp-city-wrapper">
                        <input id="astrologerWpLunarReturnChartCityInput" class="form-control" autocomplete="off"
                            type="text" name="city" placeholder="Enter birth city" required value="<?php echo esc_attr($city); ?>">
                        <ul id="astrologerWpLunarReturnChartCitySuggestions" class="suggestions dropdown-menu form-control" role="listbox">
                        </ul>
                    </div>

                    <input id="astrologerWpLunarReturnChartLongitudeInput" type="hidden" name="longitude" required value="<?php echo esc_attr($longitude); ?>">
                    <input id="astrologerWpLunarReturnChartLatitudeInput" type="hidden" name="latitude" required value="<?php echo esc_attr($latitude); ?>">
                    <input id="astrologerWpLunarReturnChartNationInput" type="hidden" name="nation" required value="<?php echo esc_attr($nation); ?>">
                    <input id="astrologerWpLunarReturnChartTimezoneInput" type="hidden" name="timezone" required value="<?php echo esc_attr($timezone); ?>">
                </div>

                <div id="astrologerWpLunarReturnChartReturnData" class="subject-data return-subject-data">
                    <p class="subject-title">Lunar Return</p>
                    <input id="astrologerWpLunarReturnChartReturnYearInput" class="form-control"
                        type="number" name="returnYear" placeholder="Return year" required
                        value="<?php echo esc_attr($returnYear ? $returnYear : $currentYear); ?>"
                        min="1801" max="2100">
                    <select id="astrologerWpLunarReturnChartReturnMonthInput" class="form-control" name="returnMonth" required>
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?php echo $m; ?>" <?php selected($m, $returnMonth ? $returnMonth : $currentMonth); ?>>
                                <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                            </option>
                        <?php endfor; ?>
                    </select>

                    <p class="subject-subtitle">Return Location (optional)</p>
                    <div class="astrologer-wp-city-wrapper">
                        <input id="astrologerWpLunarReturnChartReturnCityInput" class="form-control" autocomplete="off"
                            type="text" name="returnCity" placeholder="Enter return location city" value="<?php echo esc_attr($returnCity); ?>">
                        <ul id="astrologerWpLunarReturnChartReturnCitySuggestions" class="suggestions dropdown-menu form-control" role="listbox">
                        </ul>
                    </div>

                    <input id="astrologerWpLunarReturnChartReturnLongitudeInput" type="hidden" name="returnLongitude" value="<?php echo esc_attr($returnLongitude); ?>">
                    <input id="astrologerWpLunarReturnChartReturnLatitudeInput" type="hidden" name="returnLatitude" value="<?php echo esc_attr($returnLatitude); ?>">
                    <input id="astrologerWpLunarReturnChartReturnNationInput" type="hidden" name="returnNation" value="<?php echo esc_attr($returnNation); ?>">
                    <input id="astrologerWpLunarReturnChartReturnTimezoneInput" type="hidden" name="returnTimezone" value="<?php echo esc_attr($returnTimezone); ?>">
                </div>
            </div>

            <!-- Submit button -->
            <button type="submit" class="btn">Get Lunar Return Chart</button>
        </form>
    </div>
<?php
    $output = ob_get_clean();
    $output = str_replace(array("\n", "\r"), '', $output);
    return $output;
}
