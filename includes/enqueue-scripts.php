<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'ASTROLOGER_WP_VERSION', '2.0.0' );

// Admin Scripts/CSS - only on the plugin's settings page
add_action( 'admin_enqueue_scripts', 'astrologer_wp_enqueue_admin_scripts' );
function astrologer_wp_enqueue_admin_scripts( $hook ) {
    if ( 'toplevel_page_astrologer-wp' !== $hook ) {
        return;
    }
    wp_enqueue_script( 'astrologer-wp-admin-js', plugin_dir_url( __FILE__ ) . '../assets/dist/js/admin.js', array(), ASTROLOGER_WP_VERSION, true );
    wp_enqueue_style( 'astrologer-wp-admin-css', plugin_dir_url( __FILE__ ) . '../assets/dist/css/styles.css', array(), ASTROLOGER_WP_VERSION );
}

// Frontend Scripts/CSS
add_action( 'wp_enqueue_scripts', 'astrologer_wp_enqueue_frontend_scripts' );
function astrologer_wp_enqueue_frontend_scripts() {
    wp_enqueue_script( 'astrologer-wp-frontend-js', plugin_dir_url( __FILE__ ) . '../assets/dist/js/frontend.js', array(), ASTROLOGER_WP_VERSION, true );
    wp_enqueue_style( 'astrologer-wp-frontend-css', plugin_dir_url( __FILE__ ) . '../assets/dist/css/styles.css', array(), ASTROLOGER_WP_VERSION );

    wp_localize_script( 'astrologer-wp-frontend-js', 'astrologerWpAjax', array(
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'astrologer_wp_city_search' ),
    ) );
}
