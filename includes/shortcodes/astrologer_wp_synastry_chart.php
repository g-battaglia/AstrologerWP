<?php

use AstrologerWP\Utils\AstrologerApiAdapter;
use AstrologerWP\Utils\Subject;

add_shortcode('astrologer_wp_synastry_chart', 'astrologerWpSynastryChartShortCode');
function astrologerWpSynastryChartShortCode() {
    $apiKey = get_option('astrologer_wp__api_key');
    $firstChartName = '';
    $firstDatetime = '';
    $firstLongitude = '';
    $firstLatitude = '';
    $firstCity = '';
    $firstNation = '';
    $firstTimezone = '';
    $secondChartName = '';
    $secondDatetime = '';
    $secondLongitude = '';
    $secondLatitude = '';
    $secondCity = '';
    $secondNation = '';
    $secondTimezone = '';
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
        isset($_GET['firstChartName'])
        && isset($_GET['firstDatetime'])
        && isset($_GET['firstLongitude'])
        && isset($_GET['firstLatitude'])
        && isset($_GET['firstCity'])
        && isset($_GET['firstNation'])
        && isset($_GET['firstTimezone'])
        && isset($_GET['secondChartName'])
        && isset($_GET['secondDatetime'])
        && isset($_GET['secondLongitude'])
        && isset($_GET['secondLatitude'])
        && isset($_GET['secondCity'])
        && isset($_GET['secondNation'])
        && isset($_GET['secondTimezone'])
        && isset($_GET['_wpnonce'])
        && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'astrologer_wp_synastry_chart')
    ) {
        $firstChartName = sanitize_text_field(wp_unslash($_GET['firstChartName']));
        $firstDatetime = sanitize_text_field(wp_unslash($_GET['firstDatetime']));
        $firstLongitude = (float) sanitize_text_field(wp_unslash($_GET['firstLongitude']));
        $firstLatitude = (float) sanitize_text_field(wp_unslash($_GET['firstLatitude']));
        $firstCity = sanitize_text_field(wp_unslash($_GET['firstCity']));
        $firstNation = sanitize_text_field(wp_unslash($_GET['firstNation']));
        $firstTimezone = sanitize_text_field(wp_unslash($_GET['firstTimezone']));

        $secondChartName = sanitize_text_field(wp_unslash($_GET['secondChartName']));
        $secondDatetime = sanitize_text_field(wp_unslash($_GET['secondDatetime']));
        $secondLongitude = (float) sanitize_text_field(wp_unslash($_GET['secondLongitude']));
        $secondLatitude = (float) sanitize_text_field(wp_unslash($_GET['secondLatitude']));
        $secondCity = sanitize_text_field(wp_unslash($_GET['secondCity']));
        $secondNation = sanitize_text_field(wp_unslash($_GET['secondNation']));
        $secondTimezone = sanitize_text_field(wp_unslash($_GET['secondTimezone']));

        // Estrai data e ora dal campo datetime
        $firstDatetimeObject = new DateTime($firstDatetime);
        $firstYear = (int) $firstDatetimeObject->format('Y');
        $firstMonth = (int) $firstDatetimeObject->format('m');
        $firstDay = (int) $firstDatetimeObject->format('d');
        $firstHour = (int) $firstDatetimeObject->format('H');
        $firstMinute = (int) $firstDatetimeObject->format('i');

        $secondDatetimeObject = new DateTime($secondDatetime);
        $secondYear = (int) $secondDatetimeObject->format('Y');
        $secondMonth = (int) $secondDatetimeObject->format('m');
        $secondDay = (int) $secondDatetimeObject->format('d');
        $secondHour = (int) $secondDatetimeObject->format('H');
        $secondMinute = (int) $secondDatetimeObject->format('i');

        $firstSubject = new Subject(
            $firstChartName,
            $firstYear,
            $firstMonth,
            $firstDay,
            $firstHour,
            $firstMinute,
            $firstLongitude,
            $firstLatitude,
            $firstCity,
            $firstNation,
            $firstTimezone,
            $zodiacType,
            $houseSystem,
            $siderealMode,
            $perspectiveType
        );

        $secondSubject = new Subject(
            $secondChartName,
            $secondYear,
            $secondMonth,
            $secondDay,
            $secondHour,
            $secondMinute,
            $secondLongitude,
            $secondLatitude,
            $secondCity,
            $secondNation,
            $secondTimezone,
            $zodiacType,
            $houseSystem,
            $siderealMode,
            $perspectiveType
        );

        $astrologerApiAdapter = new AstrologerApiAdapter($apiKey);

        $data = $astrologerApiAdapter->getSynastryChart(
            $firstSubject,
            $secondSubject,
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
    <div id="astrologerWpSynastryChart" data-bs-theme="dark" class="bg-primary">
        <?php if (!empty($error)): ?>
            <div id="astrologerWpSynastryChartError" class="alert alert-danger" role="alert">
                <?php echo esc_html($error); ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($chart)): ?>
            <div class="astrologer-wp-chart-wrapper" id="astrologerWpSynastryChartWrapper">
            </div>
        <?php endif; ?>
        <form id="astrologerWpSynastryChartForm" method="get">
            <?php wp_nonce_field('astrologer_wp_synastry_chart'); ?>
            <div class="subjects-data-wrapper">
                <div id="astrologerWpSynastryChartFirstSubjectData" class="subject-data first-subject-data">
                    <p class="subject-title">Partner A</p>
                    <input id="astrologerWpSynastryChartFirstChartNameInput" class="form-control"
                        type="text" name="firstChartName" placeholder="Enter first partner name" required value="<?php echo esc_html($firstChartName); ?>">
                    <input id="astrologerWpSynastryChartFirstDatetimeInput" class="form-control"
                        type="datetime-local" name="firstDatetime" placeholder="Enter first partner date and time" required value="<?php echo esc_html($firstDatetime); ?>"
                        min="1801-01-01T00:00" max="2100-12-31T23:59">

                    <div class="astrologer-wp-city-wrapper">
                        <input id="astrologerWpSynastryChartFirstCityInput" class="form-control" autocomplete="off"
                            type="text" name="firstCity" placeholder="Enter first partner city" required value="<?php echo esc_html($firstCity); ?>">
                        <ul id="astrologerWpSynastryChartFirstCitySuggestions" class="suggestions dropdown-menu form-control" role="listbox">
                        </ul>
                    </div>

                    <input id="astrologerWpSynastryChartFirstLongitudeInput" class="form-control"
                        type="hidden" name="firstLongitude" placeholder="Enter first partner longitude" required value="<?php echo esc_html($firstLongitude); ?>">
                    <input id="astrologerWpSynastryChartFirstLatitudeInput" class="form-control"
                        type="hidden" name="firstLatitude" placeholder="Enter first partner latitude" required value="<?php echo esc_html($firstLatitude); ?>">
                    <input id="astrologerWpSynastryChartFirstNationInput" class="form-control"
                        type="hidden" name="firstNation" placeholder="Enter first partner nation" required value="<?php echo esc_html($firstNation); ?>">
                    <input id="astrologerWpSynastryChartFirstTimezoneInput" class="form-control"
                        type="hidden" name="firstTimezone" placeholder="Enter first partner timezone" required value="<?php echo esc_html($firstTimezone); ?>">

                </div>

                <div id="astrologerWpSynastryChartSecondSubjectData" class="subject-data second-subject-data">
                    <p class="subject-title">Partner B</p>
                    <input id="astrologerWpSynastryChartSecondChartNameInput" class="form-control"
                        type="text" name="secondChartName" placeholder="Enter second partner name" required value="<?php echo esc_html($secondChartName); ?>">
                    <input id="astrologerWpSynastryChartSecondDatetimeInput" class="form-control"
                        type="datetime-local" name="secondDatetime" placeholder="Enter second partner date and time" required value="<?php echo esc_html($secondDatetime); ?>"
                        min="1801-01-01T00:00" max="2100-12-31T23:59">

                    <div class="astrologer-wp-city-wrapper">
                        <input id="astrologerWpSynastryChartSecondCityInput" class="form-control" autocomplete="off"
                            type="text" name="secondCity" placeholder="Enter second partner city" required value="<?php echo esc_html($secondCity); ?>">
                        <ul id="astrologerWpSynastryChartSecondCitySuggestions" class="suggestions dropdown-menu form-control" role="listbox">
                        </ul>
                    </div>

                    <input id="astrologerWpSynastryChartSecondLongitudeInput" class="form-control"
                        type="hidden" name="secondLongitude" placeholder="Enter second partner longitude" required value="<?php echo esc_html($secondLongitude); ?>">
                    <input id="astrologerWpSynastryChartSecondLatitudeInput" class="form-control"
                        type="hidden" name="secondLatitude" placeholder="Enter second partner latitude" required value="<?php echo esc_html($secondLatitude); ?>">
                    <input id="astrologerWpSynastryChartSecondNationInput" class="form-control"
                        type="hidden" name="secondNation" placeholder="Enter second partner nation" required value="<?php echo esc_html($secondNation); ?>">
                    <input id="astrologerWpSynastryChartSecondTimezoneInput" class="form-control"
                        type="hidden" name="secondTimezone" placeholder="Enter second partner timezone" required value="<?php echo esc_html($secondTimezone); ?>">
                </div>
            </div>

            <!-- Submit button -->
            <button type="submit" class="btn">Get Synastry Chart</button>
        </form>

        <script>
            (() => {
                const encodedChart = '<?php echo esc_js($chart ? htmlspecialchars($chart, ENT_QUOTES, 'UTF-8') : null); ?>';

                if (!encodedChart) {
                    return;
                }

                const astrologerWpSynastryChartWrapper = document.getElementById('astrologerWpSynastryChartWrapper');
                const decodedChart = new TextDecoder("utf-8").decode(Uint8Array.from(atob(encodedChart), c => c.charCodeAt(0)));
                astrologerWpSynastryChartWrapper.innerHTML = decodedChart;

                window.astrologerSynastryChartData = <?php echo json_encode($chartData); ?>;
                window.astrologerSynastryChartDataChartAspects = <?php echo json_encode($chartAspects); ?>;
            })()
        </script>
    </div>
<?php
    $output = ob_get_clean();
    $output = str_replace(array("\n", "\r"), '', $output);
    return $output;
}
