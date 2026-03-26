<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use AstrologerWP\Utils\GeonamesAdapter;

function astrologer_wp_search_city() {
    check_ajax_referer( 'astrologer_wp_city_search', '_ajax_nonce' );

    if ( ! isset( $_GET['city'] ) ) {
        wp_send_json_error( __( 'City parameter is missing.', 'astrologerwp' ) );
    }

    $city    = sanitize_text_field( wp_unslash( $_GET['city'] ) );
    $nation  = isset( $_GET['nation'] ) ? sanitize_text_field( wp_unslash( $_GET['nation'] ) ) : '';
    $username = get_option( 'astrologer_wp__geonames_username' );

    if ( empty( $username ) ) {
        wp_send_json_error( __( 'Geonames username is not configured.', 'astrologerwp' ) );
    }

    $geonamesAdapter = new GeonamesAdapter( $city, $nation, $username );
    $results = $geonamesAdapter->getCityData();

    wp_send_json_success( $results );
}
add_action( 'wp_ajax_search_city', 'astrologer_wp_search_city' );
add_action( 'wp_ajax_nopriv_search_city', 'astrologer_wp_search_city' );
