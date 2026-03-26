<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Render chart SVG from API response data.
 *
 * Outputs only HTML with data attributes. JavaScript (frontend.js) handles
 * the actual SVG decoding and rendering on the client side.
 *
 * @param array  $data        The API response data.
 * @param string $wrapperId   The HTML wrapper element ID.
 * @param string $jsVarPrefix The prefix for window JS variables.
 *
 * @return string HTML output for the chart.
 */
function astrologer_wp_render_chart( array $data, string $wrapperId, string $jsVarPrefix ): string {
    $wheelOnly  = get_option( 'astrologer_wp__wheel_only_chart', false );
    $splitChart = get_option( 'astrologer_wp__split_chart', false );

    $chartSvg      = '';
    $chartWheelSvg = '';
    $chartGridSvg  = '';

    if ( ! empty( $data['chart'] ) ) {
        $chartSvg = base64_encode( mb_convert_encoding( $data['chart'], 'UTF-8', 'auto' ) );
    }

    if ( ! empty( $data['chart_wheel'] ) ) {
        $chartWheelSvg = base64_encode( mb_convert_encoding( $data['chart_wheel'], 'UTF-8', 'auto' ) );
    }

    if ( ! empty( $data['chart_grid'] ) ) {
        $chartGridSvg = base64_encode( mb_convert_encoding( $data['chart_grid'], 'UTF-8', 'auto' ) );
    }

    $chartData = isset( $data['chart_data'] ) ? $data['chart_data'] : null;
    $svgData   = ! empty( $chartWheelSvg ) ? $chartWheelSvg : $chartSvg;
    $hasChart  = ! empty( $svgData );

    if ( ! $hasChart ) {
        return '';
    }

    // Pass chart data to JS via wp_add_inline_script (printed in footer with frontend.js)
    if ( $chartData !== null ) {
        wp_add_inline_script(
            'astrologer-wp-frontend-js',
            'window.' . esc_js( $jsVarPrefix ) . 'Data = ' . wp_json_encode( $chartData ) . ';',
            'before'
        );
    }

    ob_start();
    ?>
    <div class="astrologer-wp-chart-wrapper"
         id="<?php echo esc_attr( $wrapperId ); ?>"
         data-chart-svg="<?php echo esc_attr( $svgData ); ?>">
    </div>
    <?php if ( ! $wheelOnly && $splitChart && ! empty( $chartGridSvg ) ) : ?>
        <div class="astrologer-wp-chart-grid-wrapper"
             id="<?php echo esc_attr( $wrapperId ); ?>Grid"
             data-chart-svg="<?php echo esc_attr( $chartGridSvg ); ?>">
        </div>
    <?php endif; ?>
    <?php

    return ob_get_clean();
}
