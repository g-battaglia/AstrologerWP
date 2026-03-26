<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use AstrologerWP\Utils\AstrologerApiAdapter;
use AstrologerWP\Utils\Subject;

add_shortcode('astrologer_wp_composite_chart', 'astrologerWpCompositeChartShortCode');
function astrologerWpCompositeChartShortCode() {
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
    $zodiacType = get_option('astrologer_wp__zodiac_type', 'Tropical');
    $houseSystem = get_option('astrologer_wp__houses_system', 'P');
    $siderealMode = get_option('astrologer_wp__sidereal_mode', '');
    $perspectiveType = get_option('astrologer_wp__perspective_type', 'Apparent Geocentric');

    $chartHtml = '';
    $error = null;

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
        && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'astrologer_wp_composite_chart')
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
            $firstChartName, $firstYear, $firstMonth, $firstDay, $firstHour, $firstMinute,
            $firstLongitude, $firstLatitude, $firstCity, $firstNation, $firstTimezone,
            $zodiacType, $houseSystem, $siderealMode, $perspectiveType
        );

        $secondSubject = new Subject(
            $secondChartName, $secondYear, $secondMonth, $secondDay, $secondHour, $secondMinute,
            $secondLongitude, $secondLatitude, $secondCity, $secondNation, $secondTimezone,
            $zodiacType, $houseSystem, $siderealMode, $perspectiveType
        );

        $astrologerApiAdapter = new AstrologerApiAdapter($apiKey);
        $data = $astrologerApiAdapter->getCompositeChart($firstSubject, $secondSubject);

        if (!empty($data['error'])) {
            $error = $data['error'];
        } else {
            $chartHtml = astrologer_wp_render_chart($data, 'astrologerWpCompositeChartWrapper', 'astrologerCompositeChart');
        }
    }

    ob_start();
?>
    <div id="astrologerWpCompositeChart" data-bs-theme="dark" class="bg-primary">
        <?php if (!empty($error)): ?>
            <div id="astrologerWpCompositeChartError" class="alert alert-danger" role="alert">
                <?php echo esc_html($error); ?>
            </div>
        <?php endif; ?>
        <?php echo wp_kses_post($chartHtml); ?>
        <form id="astrologerWpCompositeChartForm" method="get">
            <?php wp_nonce_field('astrologer_wp_composite_chart'); ?>
            <div class="subjects-data-wrapper">
                <div id="astrologerWpCompositeChartFirstSubjectData" class="subject-data first-subject-data">
                    <p class="subject-title"><?php esc_html_e('Person A', 'astrologerwp'); ?></p>
                    <input id="astrologerWpCompositeChartFirstChartNameInput" class="form-control"
                        type="text" name="firstChartName" placeholder="<?php echo esc_attr__('Enter first person name', 'astrologerwp'); ?>" required value="<?php echo esc_attr($firstChartName); ?>">
                    <input id="astrologerWpCompositeChartFirstDatetimeInput" class="form-control"
                        type="datetime-local" name="firstDatetime" placeholder="<?php echo esc_attr__('Enter date and time', 'astrologerwp'); ?>" required value="<?php echo esc_attr($firstDatetime); ?>"
                        min="1801-01-01T00:00" max="2100-12-31T23:59">

                    <div class="astrologer-wp-city-wrapper">
                        <input id="astrologerWpCompositeChartFirstCityInput" class="form-control" autocomplete="off"
                            type="text" name="firstCity" placeholder="<?php echo esc_attr__('Enter city', 'astrologerwp'); ?>" required value="<?php echo esc_attr($firstCity); ?>">
                        <ul id="astrologerWpCompositeChartFirstCitySuggestions" class="suggestions dropdown-menu form-control" role="listbox">
                        </ul>
                    </div>

                    <input id="astrologerWpCompositeChartFirstLongitudeInput" type="hidden" name="firstLongitude" required value="<?php echo esc_attr($firstLongitude); ?>">
                    <input id="astrologerWpCompositeChartFirstLatitudeInput" type="hidden" name="firstLatitude" required value="<?php echo esc_attr($firstLatitude); ?>">
                    <input id="astrologerWpCompositeChartFirstNationInput" type="hidden" name="firstNation" required value="<?php echo esc_attr($firstNation); ?>">
                    <input id="astrologerWpCompositeChartFirstTimezoneInput" type="hidden" name="firstTimezone" required value="<?php echo esc_attr($firstTimezone); ?>">
                </div>

                <div id="astrologerWpCompositeChartSecondSubjectData" class="subject-data second-subject-data">
                    <p class="subject-title"><?php esc_html_e('Person B', 'astrologerwp'); ?></p>
                    <input id="astrologerWpCompositeChartSecondChartNameInput" class="form-control"
                        type="text" name="secondChartName" placeholder="<?php echo esc_attr__('Enter second person name', 'astrologerwp'); ?>" required value="<?php echo esc_attr($secondChartName); ?>">
                    <input id="astrologerWpCompositeChartSecondDatetimeInput" class="form-control"
                        type="datetime-local" name="secondDatetime" placeholder="<?php echo esc_attr__('Enter date and time', 'astrologerwp'); ?>" required value="<?php echo esc_attr($secondDatetime); ?>"
                        min="1801-01-01T00:00" max="2100-12-31T23:59">

                    <div class="astrologer-wp-city-wrapper">
                        <input id="astrologerWpCompositeChartSecondCityInput" class="form-control" autocomplete="off"
                            type="text" name="secondCity" placeholder="<?php echo esc_attr__('Enter city', 'astrologerwp'); ?>" required value="<?php echo esc_attr($secondCity); ?>">
                        <ul id="astrologerWpCompositeChartSecondCitySuggestions" class="suggestions dropdown-menu form-control" role="listbox">
                        </ul>
                    </div>

                    <input id="astrologerWpCompositeChartSecondLongitudeInput" type="hidden" name="secondLongitude" required value="<?php echo esc_attr($secondLongitude); ?>">
                    <input id="astrologerWpCompositeChartSecondLatitudeInput" type="hidden" name="secondLatitude" required value="<?php echo esc_attr($secondLatitude); ?>">
                    <input id="astrologerWpCompositeChartSecondNationInput" type="hidden" name="secondNation" required value="<?php echo esc_attr($secondNation); ?>">
                    <input id="astrologerWpCompositeChartSecondTimezoneInput" type="hidden" name="secondTimezone" required value="<?php echo esc_attr($secondTimezone); ?>">
                </div>
            </div>

            <!-- Submit button -->
            <button type="submit" class="btn"><?php esc_html_e('Get Composite Chart', 'astrologerwp'); ?></button>
        </form>
    </div>
<?php
    $output = ob_get_clean();
    $output = str_replace(array("\n", "\r"), '', $output);
    return $output;
}
