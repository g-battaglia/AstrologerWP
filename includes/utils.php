<?php

// Funzione per effettuare la chiamata API per il birth chart
function astrologer_wp__get_birth_chart(
    $astrologerApiKey,
    $year,
    $wheelOnly = false,
) {
    $url = 'https://astrologer.p.rapidapi.com/api/v4/birth-chart';
    $headers = array(
        'Content-Type' => 'application/json',
        'x-rapidapi-host' => 'astrologer.p.rapidapi.com',
        'x-rapidapi-key' => $astrologerApiKey
    );
    $body = json_encode([
        'subject' => [
            'name' => 'Paul',
            'year' => $year,
            'month' => 10,
            'day' => 11,
            'hour' => 9,
            'minute' => 11,
            'longitude' => 12.4963655,
            'latitude' => 41.9027835,
            'city' => 'Roma',
            'nation' => 'IT',
            'timezone' => 'Europe/Rome',
            'zodiac_type' => 'Tropic'
        ],
        "wheel_only" => $wheelOnly ? true : false,
        "theme" => "light"
    ]);

    $response = wp_remote_post($url, array(
        'headers' => $headers,
        'body' => $body
    ));

    if (is_wp_error($response)) {
        return 'Unable to retrieve birth chart.';
    }

    $response_body = wp_remote_retrieve_body($response);
    $data = json_decode($response_body, true);

    if (empty($data)) {
        return 'Unable to retrieve birth chart.';
    }

    return $data;
}
