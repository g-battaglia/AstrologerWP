<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

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
    $transitDatetime = '';
    $transitLongitude = '';
    $transitLatitude = '';
    $transitCity = '';
    $transitNation = '';
    $transitTimezone = '';
    $zodiacType = get_option('astrologer_wp__zodiac_type', 'Tropical');
    $houseSystem = get_option('astrologer_wp__houses_system', 'P');
    $siderealMode = get_option('astrologer_wp__sidereal_mode', '');
    $perspectiveType = get_option('astrologer_wp__perspective_type', 'Apparent Geocentric');

    $chartHtml = '';
    $error = null;

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
            $subjectChartName, $subjectYear, $subjectMonth, $subjectDay, $subjectHour, $subjectMinute,
            $subjectLongitude, $subjectLatitude, $subjectCity, $subjectNation, $subjectTimezone,
            $zodiacType, $houseSystem, $siderealMode, $perspectiveType
        );

        $transitSubject = new Subject(
            'Transit', $transitYear, $transitMonth, $transitDay, $transitHour, $transitMinute,
            $transitLongitude, $transitLatitude, $transitCity, $transitNation, $transitTimezone,
            $zodiacType, $houseSystem, $siderealMode, $perspectiveType
        );

        $astrologerApiAdapter = new AstrologerApiAdapter($apiKey);
        $data = $astrologerApiAdapter->getTransitChart($subjectSubject, $transitSubject);

        if (!empty($data['error'])) {
            $error = $data['error'];
        } else {
            $chartHtml = astrologer_wp_render_chart($data, 'astrologerWpTransitChartWrapper', 'astrologerTransitChart');
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
        <?php echo $chartHtml; ?>
        <form id="astrologerWpTransitChartForm" method="get">
            <?php wp_nonce_field('astrologer_wp_transit_chart'); ?>
            <div class="subjects-data-wrapper">
                <div id="astrologerWpTransitChartSubjectSubjectData" class="subject-data subject-subject-data">
                    <p class="subject-title">Subject</p>
                    <input id="astrologerWpTransitChartSubjectChartNameInput" class="form-control"
                        type="text" name="subjectChartName" placeholder="Enter name" required value="<?php echo esc_attr($subjectChartName); ?>">
                    <input id="astrologerWpTransitChartSubjectDatetimeInput" class="form-control"
                        type="datetime-local" name="subjectDatetime" placeholder="Enter date and time" required value="<?php echo esc_attr($subjectDatetime); ?>"
                        min="1801-01-01T00:00" max="2100-12-31T23:59">

                    <div class="astrologer-wp-city-wrapper">
                        <input id="astrologerWpTransitChartSubjectCityInput" class="form-control" autocomplete="off"
                            type="text" name="subjectCity" placeholder="Enter city" required value="<?php echo esc_attr($subjectCity); ?>">
                        <ul id="astrologerWpTransitChartSubjectCitySuggestions" class="suggestions dropdown-menu form-control" role="listbox">
                        </ul>
                    </div>

                    <input id="astrologerWpTransitChartSubjectLongitudeInput" type="hidden" name="subjectLongitude" required value="<?php echo esc_attr($subjectLongitude); ?>">
                    <input id="astrologerWpTransitChartSubjectLatitudeInput" type="hidden" name="subjectLatitude" required value="<?php echo esc_attr($subjectLatitude); ?>">
                    <input id="astrologerWpTransitChartSubjectNationInput" type="hidden" name="subjectNation" required value="<?php echo esc_attr($subjectNation); ?>">
                    <input id="astrologerWpTransitChartSubjectTimezoneInput" type="hidden" name="subjectTimezone" required value="<?php echo esc_attr($subjectTimezone); ?>">
                </div>

                <div id="astrologerWpTransitChartTransitSubjectData" class="subject-data transit-subject-data">
                    <p class="subject-title">Transit</p>
                    <input id="astrologerWpTransitChartTransitDatetimeInput" class="form-control"
                        type="datetime-local" name="transitDatetime" placeholder="Enter transit date and time" required value="<?php echo esc_attr($transitDatetime); ?>"
                        min="1801-01-01T00:00" max="2100-12-31T23:59">

                    <div class="astrologer-wp-city-wrapper">
                        <input id="astrologerWpTransitChartTransitCityInput" class="form-control" autocomplete="off"
                            type="text" name="transitCity" placeholder="Enter transit city" required value="<?php echo esc_attr($transitCity); ?>">
                        <ul id="astrologerWpTransitChartTransitCitySuggestions" class="suggestions dropdown-menu form-control" role="listbox">
                        </ul>
                    </div>

                    <input id="astrologerWpTransitChartTransitLongitudeInput" type="hidden" name="transitLongitude" required value="<?php echo esc_attr($transitLongitude); ?>">
                    <input id="astrologerWpTransitChartTransitLatitudeInput" type="hidden" name="transitLatitude" required value="<?php echo esc_attr($transitLatitude); ?>">
                    <input id="astrologerWpTransitChartTransitNationInput" type="hidden" name="transitNation" required value="<?php echo esc_attr($transitNation); ?>">
                    <input id="astrologerWpTransitChartTransitTimezoneInput" type="hidden" name="transitTimezone" required value="<?php echo esc_attr($transitTimezone); ?>">
                </div>
            </div>

            <!-- Submit button -->
            <button type="submit" class="btn">Get Transit Chart</button>
        </form>
    </div>
<?php
    $output = ob_get_clean();
    $output = str_replace(array("\n", "\r"), '', $output);
    return $output;
}
