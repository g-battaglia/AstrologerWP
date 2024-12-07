<?php

use AstrologerWP\Utils\AstrologerApiAdapter;
use AstrologerWP\Utils\Subject;

add_shortcode('astrologer_wp_transit_chart', 'astrologerWpTransitChartShortCode');
function astrologerWpTransitChartShortCode() {
    $apiKey = get_option('astrologer_wp__api_key');
    $subjectChartName = '';
    $subjectDatetime = '';
    $subjectLongitude = '';
    $subjectLatitude = '';
    $subjectCity = '';
    $subjectNation = '';
    $subjectTimezone = '';
    $transitChartName = '';
    $transitDatetime = '';
    $transitLongitude = '';
    $transitLatitude = '';
    $transitCity = '';
    $transitNation = '';
    $transitTimezone = '';
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
        isset($_GET['subjectChartName'])
        && isset($_GET['subjectDatetime'])
        && isset($_GET['subjectLongitude'])
        && isset($_GET['subjectLatitude'])
        && isset($_GET['subjectCity'])
        && isset($_GET['subjectNation'])
        && isset($_GET['subjectTimezone'])
        && isset($_GET['transitDatetime'])
        && isset($_GET['transitLongitude'])
        && isset($_GET['transitLatitude'])
        && isset($_GET['transitCity'])
        && isset($_GET['transitNation'])
        && isset($_GET['transitTimezone'])
        && isset($_GET['_wpnonce'])
        && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'astrologer_wp_transit_chart')
    ) {
        $subjectChartName = sanitize_text_field(wp_unslash($_GET['subjectChartName']));
        $subjectDatetime = sanitize_text_field(wp_unslash($_GET['subjectDatetime']));
        $subjectLongitude = (float) sanitize_text_field(wp_unslash($_GET['subjectLongitude']));
        $subjectLatitude = (float) sanitize_text_field(wp_unslash($_GET['subjectLatitude']));
        $subjectCity = sanitize_text_field(wp_unslash($_GET['subjectCity']));
        $subjectNation = sanitize_text_field(wp_unslash($_GET['subjectNation']));
        $subjectTimezone = sanitize_text_field(wp_unslash($_GET['subjectTimezone']));

        $transitDatetime = sanitize_text_field(wp_unslash($_GET['transitDatetime']));
        $transitLongitude = (float) sanitize_text_field(wp_unslash($_GET['transitLongitude']));
        $transitLatitude = (float) sanitize_text_field(wp_unslash($_GET['transitLatitude']));
        $transitCity = sanitize_text_field(wp_unslash($_GET['transitCity']));
        $transitNation = sanitize_text_field(wp_unslash($_GET['transitNation']));
        $transitTimezone = sanitize_text_field(wp_unslash($_GET['transitTimezone']));

        // Estrai data e ora dal campo datetime
        $subjectDatetimeObject = new DateTime($subjectDatetime);
        $subjectYear = (int) $subjectDatetimeObject->format('Y');
        $subjectMonth = (int) $subjectDatetimeObject->format('m');
        $subjectDay = (int) $subjectDatetimeObject->format('d');
        $subjectHour = (int) $subjectDatetimeObject->format('H');
        $subjectMinute = (int) $subjectDatetimeObject->format('i');

        $transitDatetimeObject = new DateTime($transitDatetime);
        $transitYear = (int) $transitDatetimeObject->format('Y');
        $transitMonth = (int) $transitDatetimeObject->format('m');
        $transitDay = (int) $transitDatetimeObject->format('d');
        $transitHour = (int) $transitDatetimeObject->format('H');
        $transitMinute = (int) $transitDatetimeObject->format('i');

        $subjectSubject = new Subject(
            $subjectChartName,
            $subjectYear,
            $subjectMonth,
            $subjectDay,
            $subjectHour,
            $subjectMinute,
            $subjectLongitude,
            $subjectLatitude,
            $subjectCity,
            $subjectNation,
            $subjectTimezone,
            $zodiacType,
            $houseSystem,
            $siderealMode,
            $perspectiveType
        );

        $transitSubject = new Subject(
            'Transit',
            $transitYear,
            $transitMonth,
            $transitDay,
            $transitHour,
            $transitMinute,
            $transitLongitude,
            $transitLatitude,
            $transitCity,
            $transitNation,
            $transitTimezone,
            $zodiacType,
            $houseSystem,
            $siderealMode,
            $perspectiveType
        );

        $astrologerApiAdapter = new AstrologerApiAdapter($apiKey);

        $data = $astrologerApiAdapter->getTransitChart(
            $subjectSubject,
            $transitSubject,
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
    <div id="astrologerWpTransitChart" data-bs-theme="dark" class="bg-primary">
        <?php if (!empty($error)): ?>
            <div id="astrologerWpTransitChartError" class="alert alert-danger" role="alert">
                <?php echo esc_html($error); ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($chart)): ?>
            <div class="astrologer-wp-chart-wrapper" id="astrologerWpTransitChartWrapper">
            </div>
        <?php endif; ?>
        <form id="astrologerWpTransitChartForm" method="get">
            <?php wp_nonce_field('astrologer_wp_transit_chart'); ?>
            <div class="subjects-data-wrapper">
                <div id="astrologerWpTransitChartSubjectSubjectData" class="subject-data subject-subject-data">
                    <p class="subject-title">Subject</p>
                    <input id="astrologerWpTransitChartSubjectChartNameInput" class="form-control"
                        type="text" name="subjectChartName" placeholder="Enter subject partner name" required value="<?php echo esc_html($subjectChartName); ?>">
                    <input id="astrologerWpTransitChartSubjectDatetimeInput" class="form-control"
                        type="datetime-local" name="subjectDatetime" placeholder="Enter subject partner date and time" required value="<?php echo esc_html($subjectDatetime); ?>"
                        min="1801-01-01T00:00" max="2100-12-31T23:59">

                    <div class="astrologer-wp-city-wrapper">
                        <input id="astrologerWpTransitChartSubjectCityInput" class="form-control" autocomplete="off"
                            type="text" name="subjectCity" placeholder="Enter subject partner city" required value="<?php echo esc_html($subjectCity); ?>">
                        <ul id="astrologerWpTransitChartSubjectCitySuggestions" class="suggestions dropdown-menu form-control" role="listbox">
                        </ul>
                    </div>

                    <input id="astrologerWpTransitChartSubjectLongitudeInput" class="form-control"
                        type="hidden" name="subjectLongitude" placeholder="Enter subject partner longitude" required value="<?php echo esc_html($subjectLongitude); ?>">
                    <input id="astrologerWpTransitChartSubjectLatitudeInput" class="form-control"
                        type="hidden" name="subjectLatitude" placeholder="Enter subject partner latitude" required value="<?php echo esc_html($subjectLatitude); ?>">
                    <input id="astrologerWpTransitChartSubjectNationInput" class="form-control"
                        type="hidden" name="subjectNation" placeholder="Enter subject partner nation" required value="<?php echo esc_html($subjectNation); ?>">
                    <input id="astrologerWpTransitChartSubjectTimezoneInput" class="form-control"
                        type="hidden" name="subjectTimezone" placeholder="Enter subject partner timezone" required value="<?php echo esc_html($subjectTimezone); ?>">

                </div>

                <div id="astrologerWpTransitChartTransitSubjectData" class="subject-data transit-subject-data">
                    <p class="subject-title">Transit</p>
                    <input id="astrologerWpTransitChartTransitDatetimeInput" class="form-control"
                        type="datetime-local" name="transitDatetime" placeholder="Enter transit partner date and time" required value="<?php echo esc_html($transitDatetime); ?>"
                        min="1801-01-01T00:00" max="2100-12-31T23:59">

                    <div class="astrologer-wp-city-wrapper">
                        <input id="astrologerWpTransitChartTransitCityInput" class="form-control" autocomplete="off"
                            type="text" name="transitCity" placeholder="Enter transit partner city" required value="<?php echo esc_html($transitCity); ?>">
                        <ul id="astrologerWpTransitChartTransitCitySuggestions" class="suggestions dropdown-menu form-control" role="listbox">
                        </ul>
                    </div>

                    <input id="astrologerWpTransitChartTransitLongitudeInput" class="form-control"
                        type="hidden" name="transitLongitude" placeholder="Enter subject partner longitude" required value="<?php echo esc_html($transitLongitude); ?>">
                    <input id="astrologerWpTransitChartTransitLatitudeInput" class="form-control"
                        type="hidden" name="transitLatitude" placeholder="Enter subject partner latitude" required value="<?php echo esc_html($transitLatitude); ?>">
                    <input id="astrologerWpTransitChartTransitNationInput" class="form-control"
                        type="hidden" name="transitNation" placeholder="Enter subject partner nation" required value="<?php echo esc_html($transitNation); ?>">
                    <input id="astrologerWpTransitChartTransitTimezoneInput" class="form-control"
                        type="hidden" name="transitTimezone" placeholder="Enter subject partner timezone" required value="<?php echo esc_html($transitTimezone); ?>">
                </div>
            </div>

            <!-- Submit button -->
            <button type="submit" class="btn">Get Transit Chart</button>
        </form>

        <script>
            (() => {
                const encodedChart = '<?php echo esc_js($chart ? htmlspecialchars($chart, ENT_QUOTES, 'UTF-8') : null); ?>';

                if (!encodedChart) {
                    return;
                }

                const astrologerWpTransitChartWrapper = document.getElementById('astrologerWpTransitChartWrapper');
                const decodedChart = new TextDecoder("utf-8").decode(Uint8Array.from(atob(encodedChart), c => c.charCodeAt(0)));
                astrologerWpTransitChartWrapper.innerHTML = decodedChart;

                window.astrologerTransitChartData = <?php echo json_encode($chartData); ?>;
                window.astrologerTransitChartDataChartAspects = <?php echo json_encode($chartAspects); ?>;
            })()
        </script>
    </div>
<?php
    $output = ob_get_clean();
    $output = str_replace(array("\n", "\r"), '', $output);
    return $output;
}
