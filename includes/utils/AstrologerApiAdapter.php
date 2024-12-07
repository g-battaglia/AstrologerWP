<?php

namespace AstrologerWP\Utils;

class AstrologerApiAdapter {
    private string $apiKey;
    private const API_BASE_URL = 'https://astrologer.p.rapidapi.com';
    private const X_RAPIDAPI_HOST = 'astrologer.p.rapidapi.com';

    /**
     * Constructor for AstrologerApiAdapter.
     *
     * @param string $apiKey The API key.
     */
    public function __construct(string $apiKey) {
        $this->apiKey = $apiKey;
    }

    /**
     * Retrieve birth chart from Astrologer API.
     *
     * @param Subject $subject
     * @param bool $wheelOnly
     * @param string $theme
     * @param string $language
     *
     * @return array The birth chart data with the chart and data keys.
     */
    public function getBirthChart(
        Subject $subject,
        bool $wheelOnly,
        string $theme,
        string $language,
    ): array {
        $url = self::API_BASE_URL . '/api/v4/birth-chart';
        $headers = array(
            'Content-Type' => 'application/json',
            'x-rapidapi-host' => self::X_RAPIDAPI_HOST,
            'x-rapidapi-key' => $this->apiKey
        );

        $body = json_encode([
            'subject' => $subject->toArray(),
            "wheel_only" => $wheelOnly,
            "theme" => $theme,
            "language" => $language,
        ]);

        $response = wp_remote_post($url, array(
            'headers' => $headers,
            'body' => $body
        ));

        if (is_wp_error($response)) {
            error_log($response->get_error_message());
            error_log(print_r($response, true));

            return [
                'chart' => null,
                'data' => null,
                'error' => 'Unable to retrieve birth chart. Contact the site administrator for more information.'
            ];
        }

        $response_body = wp_remote_retrieve_body($response);
        $data = json_decode($response_body, true);

        if (empty($data) || !isset($data['chart'])) {
            error_log('Error fetching birth chart');
            error_log(print_r($response, true));

            return [
                'chart' => null,
                'data' => null,
                'error' => 'Unable to retrieve birth chart. Contact the site administrator for more information.'
            ];
        }

        $data['error'] = null;
        return $data;
    }

    /**
     * Retrieve synastry chart from Astrologer API.
     *
     * @param Subject $firstSubject
     * @param Subject $secondSubject
     * @param bool $wheelOnly
     * @param string $theme
     * @param string $language
     *
     * @return array The synastry chart data with the chart and data keys.
     */
    public function getSynastryChart(
        Subject $firstSubject,
        Subject $secondSubject,
        bool $wheelOnly,
        string $theme,
        string $language,
    ): array {
        $url = self::API_BASE_URL . '/api/v4/synastry-chart';
        $headers = array(
            'Content-Type' => 'application/json',
            'x-rapidapi-host' => self::X_RAPIDAPI_HOST,
            'x-rapidapi-key' => $this->apiKey
        );

        $body = json_encode([
            'first_subject' => $firstSubject->toArray(),
            'second_subject' => $secondSubject->toArray(),
            'wheel_only' => $wheelOnly,
            'theme' => $theme,
            'language' => $language,
        ]);

        $response = wp_remote_post($url, array(
            'headers' => $headers,
            'body' => $body
        ));

        if (is_wp_error($response)) {
            error_log($response->get_error_message());
            error_log(print_r($response, true));

            return [
                'chart' => null,
                'data' => null,
                'error' => 'Unable to retrieve synastry chart. Contact the site administrator for more information.'
            ];
        }

        $response_body = wp_remote_retrieve_body($response);
        $data = json_decode($response_body, true);

        if (empty($data) || !isset($data['chart'])) {
            error_log('Error fetching synastry chart');
            error_log(print_r($response, true));

            return [
                'chart' => null,
                'data' => null,
                'error' => 'Unable to retrieve synastry chart. Contact the site administrator for more information.'
            ];
        }

        $data['error'] = null;
        return $data;
    }

    /**
     * Retrieve transit chart from Astrologer API.
     *
     * @param Subject $firstSubject
     * @param Subject $transitSubject
     * @param bool $wheelOnly
     * @param string $theme
     * @param string $language
     *
     * @return array The transit chart data with the chart and data keys.
     */
    public function getTransitChart(
        Subject $firstSubject,
        Subject $transitSubject,
        bool $wheelOnly,
        string $theme,
        string $language,
    ): array {
        $url = self::API_BASE_URL . '/api/v4/transit-chart';
        $headers = array(
            'Content-Type' => 'application/json',
            'x-rapidapi-host' => self::X_RAPIDAPI_HOST,
            'x-rapidapi-key' => $this->apiKey
        );

        $body = json_encode([
            'first_subject' => $firstSubject->toArray(),
            'transit_subject' => $transitSubject->toArray(),
            'wheel_only' => $wheelOnly,
            'theme' => $theme,
            'language' => $language,
        ]);

        $response = wp_remote_post($url, array(
            'headers' => $headers,
            'body' => $body
        ));

        if (is_wp_error($response)) {
            error_log($response->get_error_message());
            error_log(print_r($response, true));

            return [
                'chart' => null,
                'data' => null,
                'error' => 'Unable to retrieve synastry chart. Contact the site administrator for more information.'
            ];
        }

        $response_body = wp_remote_retrieve_body($response);
        $data = json_decode($response_body, true);

        if (empty($data) || !isset($data['chart'])) {
            error_log('Error fetching synastry chart');
            error_log(print_r($response, true));

            return [
                'chart' => null,
                'data' => null,
                'error' => 'Unable to retrieve synastry chart. Contact the site administrator for more information.'
            ];
        }

        $data['error'] = null;
        return $data;
    }
}
