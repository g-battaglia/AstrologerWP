<?php

use AstrologerWP\Utils\GeonamesAdapter;

function astrologer_wp_search_city() {
    if (!isset($_GET['city']) || !isset($_GET['nation'])) {
        wp_send_json_error('City or country parameter is missing');
    }

    $city = sanitize_text_field($_GET['city']);
    $nation = sanitize_text_field($_GET['nation']);
    $username = get_option('astrologer_wp__geonames_username');

    if (empty($username)) {
        wp_send_json_error('Geonames username is not set');
    }



    $geonamesAdapter = new GeonamesAdapter($city, $nation, $username);
    $results = $geonamesAdapter->getCityData();

    wp_send_json_success($results);
}
add_action('wp_ajax_search_city', 'astrologer_wp_search_city');
add_action('wp_ajax_nopriv_search_city', 'astrologer_wp_search_city');
