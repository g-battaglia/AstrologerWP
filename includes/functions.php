<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'wp_enqueue_scripts', 'astrologer_wp_theme_inline_script' );
function astrologer_wp_theme_inline_script() {
    $chart_theme = get_option( 'astrologer_wp__chart_theme' );
    if ( $chart_theme === 'dark' || $chart_theme === 'dark-high-contrast' ) {
        wp_add_inline_script(
            'astrologer-wp-frontend-js',
            'document.body.classList.add("astrologer-wp-dark-theme");',
            'after'
        );
    }
}
