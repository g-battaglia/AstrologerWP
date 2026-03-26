<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use AstrologerWP\Utils\AstrologerApiAdapter;

add_shortcode('astrologer_wp_moon_phase', 'astrologerWpMoonPhaseShortCode');
function astrologerWpMoonPhaseShortCode() {
    $apiKey = get_option('astrologer_wp__api_key');
    $datetime = '';
    $longitude = '';
    $latitude = '';
    $city = '';
    $nation = '';
    $timezone = '';

    $moonPhaseData = null;
    $error = null;

    if (
        isset($_GET['datetime'])
        && isset($_GET['longitude'])
        && isset($_GET['latitude'])
        && isset($_GET['city'])
        && isset($_GET['timezone'])
        && isset($_GET['_wpnonce'])
        && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'astrologer_wp_moon_phase')
    ) {
        $datetime = sanitize_text_field(wp_unslash($_GET['datetime']));
        $longitude = (float) sanitize_text_field(wp_unslash($_GET['longitude']));
        $latitude = (float) sanitize_text_field(wp_unslash($_GET['latitude']));
        $city = sanitize_text_field(wp_unslash($_GET['city']));
        $nation = isset($_GET['nation']) ? sanitize_text_field(wp_unslash($_GET['nation'])) : '';
        $timezone = sanitize_text_field(wp_unslash($_GET['timezone']));

        $datetimeObject = new DateTime($datetime);
        $year = (int) $datetimeObject->format('Y');
        $month = (int) $datetimeObject->format('m');
        $day = (int) $datetimeObject->format('d');
        $hour = (int) $datetimeObject->format('H');
        $minute = (int) $datetimeObject->format('i');

        $astrologerApiAdapter = new AstrologerApiAdapter($apiKey);
        $data = $astrologerApiAdapter->getMoonPhase($year, $month, $day, $hour, $minute, $latitude, $longitude, $timezone);

        if (!empty($data['error'])) {
            $error = $data['error'];
        } else {
            $moonPhaseData = isset($data['moon_phase_overview']) ? $data['moon_phase_overview'] : null;
        }
    }

    ob_start();
?>
    <div id="astrologerWpMoonPhase" data-bs-theme="dark" class="bg-primary">
        <?php if (!empty($error)): ?>
            <div id="astrologerWpMoonPhaseError" class="alert alert-danger" role="alert">
                <?php echo esc_html($error); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($moonPhaseData)): ?>
            <div class="astrologer-wp-moon-phase-result">
                <div class="moon-phase-header">
                    <span class="moon-phase-emoji"><?php echo esc_html($moonPhaseData['phase_emoji'] ?? ''); ?></span>
                    <h3 class="moon-phase-name"><?php echo esc_html($moonPhaseData['phase_name'] ?? __('Unknown', 'astrologerwp')); ?></h3>
                </div>
                <div class="moon-phase-details">
                    <?php if (isset($moonPhaseData['illumination'])): ?>
                        <div class="moon-phase-detail">
                            <span class="detail-label"><?php esc_html_e('Illumination', 'astrologerwp'); ?></span>
                            <span class="detail-value"><?php echo esc_html(round($moonPhaseData['illumination'], 1)); ?>%</span>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($moonPhaseData['stage'])): ?>
                        <div class="moon-phase-detail">
                            <span class="detail-label"><?php esc_html_e('Stage', 'astrologerwp'); ?></span>
                            <span class="detail-value"><?php echo esc_html(ucfirst($moonPhaseData['stage'])); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($moonPhaseData['moon_age_days'])): ?>
                        <div class="moon-phase-detail">
                            <span class="detail-label"><?php esc_html_e('Moon Age', 'astrologerwp'); ?></span>
                            <?php /* translators: %s: number of days */ ?>
                            <span class="detail-value"><?php echo esc_html(sprintf(__('%s days', 'astrologerwp'), round($moonPhaseData['moon_age_days'], 1))); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($moonPhaseData['next_new_moon'])): ?>
                        <div class="moon-phase-detail">
                            <span class="detail-label"><?php esc_html_e('Next New Moon', 'astrologerwp'); ?></span>
                            <span class="detail-value"><?php echo esc_html($moonPhaseData['next_new_moon']); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($moonPhaseData['next_full_moon'])): ?>
                        <div class="moon-phase-detail">
                            <span class="detail-label"><?php esc_html_e('Next Full Moon', 'astrologerwp'); ?></span>
                            <span class="detail-value"><?php echo esc_html($moonPhaseData['next_full_moon']); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($moonPhaseData['sunrise'])): ?>
                        <div class="moon-phase-detail">
                            <span class="detail-label"><?php esc_html_e('Sunrise', 'astrologerwp'); ?></span>
                            <span class="detail-value"><?php echo esc_html($moonPhaseData['sunrise']); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($moonPhaseData['sunset'])): ?>
                        <div class="moon-phase-detail">
                            <span class="detail-label"><?php esc_html_e('Sunset', 'astrologerwp'); ?></span>
                            <span class="detail-value"><?php echo esc_html($moonPhaseData['sunset']); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($moonPhaseData['next_lunar_eclipse'])): ?>
                        <div class="moon-phase-detail">
                            <span class="detail-label"><?php esc_html_e('Next Lunar Eclipse', 'astrologerwp'); ?></span>
                            <span class="detail-value"><?php echo esc_html($moonPhaseData['next_lunar_eclipse']); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($moonPhaseData['next_solar_eclipse'])): ?>
                        <div class="moon-phase-detail">
                            <span class="detail-label"><?php esc_html_e('Next Solar Eclipse', 'astrologerwp'); ?></span>
                            <span class="detail-value"><?php echo esc_html($moonPhaseData['next_solar_eclipse']); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php
            wp_add_inline_script(
                'astrologer-wp-frontend-js',
                'window.astrologerMoonPhaseData = ' . wp_json_encode( $moonPhaseData ) . ';',
                'before'
            );
            ?>
        <?php endif; ?>

        <p class="subject-title"><?php esc_html_e('Moon Phase Data', 'astrologerwp'); ?></p>
        <form id="astrologerWpMoonPhaseForm" method="get">
            <?php wp_nonce_field('astrologer_wp_moon_phase'); ?>
            <input id="astrologerWpMoonPhaseDatetimeInput" class="form-control"
                type="datetime-local" name="datetime" placeholder="<?php echo esc_attr__('Enter date and time', 'astrologerwp'); ?>" required value="<?php echo esc_attr($datetime); ?>"
                min="1801-01-01T00:00" max="2100-12-31T23:59">

            <div class="astrologer-wp-city-wrapper">
                <input id="astrologerWpMoonPhaseCityInput" class="form-control" autocomplete="off"
                    type="text" name="city" placeholder="<?php echo esc_attr__('Enter city', 'astrologerwp'); ?>" required value="<?php echo esc_attr($city); ?>">
                <ul id="astrologerWpMoonPhaseCitySuggestions" class="suggestions dropdown-menu form-control" role="listbox">
                </ul>
            </div>

            <input id="astrologerWpMoonPhaseLongitudeInput" type="hidden" name="longitude" required value="<?php echo esc_attr($longitude); ?>">
            <input id="astrologerWpMoonPhaseLatitudeInput" type="hidden" name="latitude" required value="<?php echo esc_attr($latitude); ?>">
            <input id="astrologerWpMoonPhaseNationInput" type="hidden" name="nation" value="<?php echo esc_attr($nation); ?>">
            <input id="astrologerWpMoonPhaseTimezoneInput" type="hidden" name="timezone" required value="<?php echo esc_attr($timezone); ?>">

            <button type="submit" class="btn"><?php esc_html_e('Get Moon Phase', 'astrologerwp'); ?></button>
        </form>
    </div>
<?php
    $output = ob_get_clean();
    $output = str_replace(array("\n", "\r"), '', $output);
    return $output;
}
