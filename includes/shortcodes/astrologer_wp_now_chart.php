<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use AstrologerWP\Utils\AstrologerApiAdapter;

add_shortcode('astrologer_wp_now_chart', 'astrologerWpNowChartShortCode');
function astrologerWpNowChartShortCode() {
    $apiKey = get_option('astrologer_wp__api_key');

    $chartHtml = '';
    $error = null;

    if (!empty($apiKey)) {
        $astrologerApiAdapter = new AstrologerApiAdapter($apiKey);
        $data = $astrologerApiAdapter->getNowChart();

        if (!empty($data['error'])) {
            $error = $data['error'];
        } else {
            $chartHtml = astrologer_wp_render_chart($data, 'astrologerWpNowChartWrapper', 'astrologerNowChart');
        }
    } else {
        $error = 'API key is not configured. Please set it in the AstrologerWP settings.';
    }

    ob_start();
?>
    <div id="astrologerWpNowChart" data-bs-theme="dark" class="bg-primary">
        <?php if (!empty($error)): ?>
            <div id="astrologerWpNowChartError" class="alert alert-danger" role="alert">
                <?php echo esc_html($error); ?>
            </div>
        <?php endif; ?>
        <?php echo $chartHtml; ?>
        <p class="astrologer-wp-now-chart-info">Current sky at UTC/Greenwich. Chart is generated at page load time.</p>
    </div>
<?php
    $output = ob_get_clean();
    $output = str_replace(array("\n", "\r"), '', $output);
    return $output;
}
