<?php

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
    $zodiacType = get_option('astrologer_wp__zodiac_type');
    $wheelOnly = get_option('astrologer_wp__wheel_only_chart');
    $theme = get_option('astrologer_wp__chart_theme');
    $houseSystem = get_option('astrologer_wp__houses_system');
    $language = get_option('astrologer_wp__chart_language');
    $siderealMode = get_option('astrologer_wp__sidereal_mode');
    $perspectiveType = get_option('astrologer_wp__perspective_type');

    $chart = null;
    $chartData = null;
    $chartAspects = null;

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

        // Estrai data e ora dal campo datetime
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

        $data = $astrologerApiAdapter->getBirthChart(
            $subject,
            $wheelOnly,
            $theme,
            $language,
        );

        $chart = base64_encode(mb_convert_encoding($data['chart'], 'UTF-8', 'auto'));
        $chartData = $data['data'];
        $chartAspects = $data['aspects'];
        $error = null;

        if (empty($chart)) {
            $error = $data['error'];
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
        <?php if (!empty($chart)): ?>
            <div class="astrologer-wp-chart-wrapper" id="astrologerWpBirthChartWrapper">
            </div>
        <?php endif; ?>
        <p class="subject-title">Subject Data</p>
        <form id="astrologerWpBirthChartForm" method="get">
            <?php wp_nonce_field('astrologer_wp_birth_chart'); ?>
            <input id="astrologerWpBirthChartNameInput" class="form-control"
                type="text" name="chartName" placeholder="Enter name" required value="<?php echo esc_html($chartName); ?>">
            <input id="astrologerWpBirthChartDatetimeInput" class="form-control"
                type="datetime-local" name="datetime" placeholder="Enter date and time" required value="<?php echo esc_html($datetime); ?>"
                min="1801-01-01T00:00" max="2100-12-31T23:59">

            <div class="astrologer-wp-city-wrapper">
                <input id="astrologerWpBirthChartCityInput" class="form-control" autocomplete="off"
                    type="text" name="city" placeholder="Enter city" required value="<?php echo esc_html($city); ?>">
                <ul id="astrologerWpBirthChartCitySuggestions" class="suggestions dropdown-menu form-control" role="listbox">
                </ul>
            </div>

            <!-- Hidden inputs -->
            <input id="astrologerWpBirthChartLongitudeInput" class="form-control"
                type="hidden" name="longitude" placeholder="Enter longitude" required value="<?php echo esc_html($longitude); ?>">
            <input id="astrologerWpBirthChartLatitudeInput" class="form-control"
                type="hidden" name="latitude" placeholder="Enter latitude" required value="<?php echo esc_html($latitude); ?>">
            <input id="astrologerWpBirthChartNationInput" class="form-control"
                type="hidden" name="nation" placeholder="Enter nation" required value="<?php echo esc_html($nation); ?>">
            <input id="astrologerWpBirthChartTimezoneInput" class="form-control"
                type="hidden" name="timezone" placeholder="Enter timezone" required value="<?php echo esc_html($timezone); ?>">

            <!-- Submit button -->
            <button type="submit" class="btn">Get Birth Chart</button>
        </form>

        <script>
            (() => {
                const astrologerWpBirthChartWrapper = document.getElementById('astrologerWpBirthChartWrapper');
                const encodedChart = '<?php echo esc_js($chart ? htmlspecialchars($chart, ENT_QUOTES, 'UTF-8') : null); ?>';

                if (!encodedChart) {
                    return;
                }

                const decodedChart = new TextDecoder("utf-8").decode(Uint8Array.from(atob(encodedChart), c => c.charCodeAt(0)));
                astrologerWpBirthChartWrapper.innerHTML = decodedChart;

                window.astrologerBirthChartData = <?php echo json_encode($chartData); ?>;
                window.astrologerBirthChartAspects = <?php echo json_encode($chartAspects); ?>;
            })();
        </script>
    </div>
<?php
    $output = ob_get_clean();
    $output = str_replace(array("\n", "\r"), '', $output);
    return $output;
}
