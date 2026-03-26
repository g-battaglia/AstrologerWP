<?php

/**
 * Render chart SVG from API response data.
 *
 * Handles both single chart and split chart (wheel + grid) responses.
 * Also handles wheel_only mode (split_chart internally, display only wheel).
 *
 * @param array $data The API response data.
 * @param string $wrapperId The HTML wrapper element ID.
 * @param string $jsVarPrefix The prefix for window JS variables.
 *
 * @return string HTML output for the chart.
 */
function astrologer_wp_render_chart(array $data, string $wrapperId, string $jsVarPrefix): string {
    $wheelOnly = get_option('astrologer_wp__wheel_only_chart', false);
    $splitChart = get_option('astrologer_wp__split_chart', false);

    $chartSvg = null;
    $chartWheelSvg = null;
    $chartGridSvg = null;

    if (!empty($data['chart'])) {
        $chartSvg = base64_encode(mb_convert_encoding($data['chart'], 'UTF-8', 'auto'));
    }

    if (!empty($data['chart_wheel'])) {
        $chartWheelSvg = base64_encode(mb_convert_encoding($data['chart_wheel'], 'UTF-8', 'auto'));
    }

    if (!empty($data['chart_grid'])) {
        $chartGridSvg = base64_encode(mb_convert_encoding($data['chart_grid'], 'UTF-8', 'auto'));
    }

    $chartData = isset($data['chart_data']) ? $data['chart_data'] : null;

    $hasChart = !empty($chartSvg) || !empty($chartWheelSvg);

    ob_start();
    if ($hasChart): ?>
        <div class="astrologer-wp-chart-wrapper" id="<?php echo esc_attr($wrapperId); ?>">
        </div>
        <?php if (!$wheelOnly && $splitChart && !empty($chartGridSvg)): ?>
            <div class="astrologer-wp-chart-grid-wrapper" id="<?php echo esc_attr($wrapperId); ?>Grid">
            </div>
        <?php endif; ?>
        <script>
            (() => {
                <?php if (!empty($chartWheelSvg)): ?>
                    const encodedWheelChart = '<?php echo esc_js(htmlspecialchars($chartWheelSvg, ENT_QUOTES, 'UTF-8')); ?>';
                    const wheelWrapper = document.getElementById('<?php echo esc_js($wrapperId); ?>');
                    if (wheelWrapper && encodedWheelChart) {
                        wheelWrapper.innerHTML = new TextDecoder("utf-8").decode(Uint8Array.from(atob(encodedWheelChart), c => c.charCodeAt(0)));
                    }
                    <?php if (!$wheelOnly && $splitChart && !empty($chartGridSvg)): ?>
                        const encodedGridChart = '<?php echo esc_js(htmlspecialchars($chartGridSvg, ENT_QUOTES, 'UTF-8')); ?>';
                        const gridWrapper = document.getElementById('<?php echo esc_js($wrapperId); ?>Grid');
                        if (gridWrapper && encodedGridChart) {
                            gridWrapper.innerHTML = new TextDecoder("utf-8").decode(Uint8Array.from(atob(encodedGridChart), c => c.charCodeAt(0)));
                        }
                    <?php endif; ?>
                <?php elseif (!empty($chartSvg)): ?>
                    const encodedChart = '<?php echo esc_js(htmlspecialchars($chartSvg, ENT_QUOTES, 'UTF-8')); ?>';
                    const wrapper = document.getElementById('<?php echo esc_js($wrapperId); ?>');
                    if (wrapper && encodedChart) {
                        wrapper.innerHTML = new TextDecoder("utf-8").decode(Uint8Array.from(atob(encodedChart), c => c.charCodeAt(0)));
                    }
                <?php endif; ?>

                window.<?php echo esc_js($jsVarPrefix); ?>Data = <?php echo wp_json_encode($chartData); ?>;
            })();
        </script>
    <?php endif;

    return ob_get_clean();
}
