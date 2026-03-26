<?php

namespace AstrologerWP\Utils;

class AstrologerApiAdapter {
    private string $apiKey;
    private const DEFAULT_API_BASE_URL = 'https://astrologer.p.rapidapi.com';
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
     * Get the API base URL. Supports override via:
     * 1. ASTROLOGER_WP_API_BASE_URL environment variable
     * 2. astrologer_wp__api_base_url WordPress option
     * 3. Falls back to default RapidAPI URL
     *
     * @return string The API base URL.
     */
    private function getApiBaseUrl(): string {
        $envUrl = getenv('ASTROLOGER_WP_API_BASE_URL');
        if (!empty($envUrl)) {
            return rtrim($envUrl, '/');
        }

        $optionUrl = get_option('astrologer_wp__api_base_url', '');
        if (!empty($optionUrl)) {
            return rtrim($optionUrl, '/');
        }

        return self::DEFAULT_API_BASE_URL;
    }

    /**
     * Check if using a custom (non-RapidAPI) endpoint.
     *
     * @return bool
     */
    private function isCustomEndpoint(): bool {
        return $this->getApiBaseUrl() !== self::DEFAULT_API_BASE_URL;
    }

    /**
     * Build the rendering parameters array from plugin settings.
     *
     * @return array
     */
    private function getRenderingParams(): array {
        $params = [];

        $theme = get_option('astrologer_wp__chart_theme', 'classic');
        if (!empty($theme)) {
            $params['theme'] = $theme;
        }

        $language = get_option('astrologer_wp__chart_language', 'EN');
        if (!empty($language)) {
            $params['language'] = $language;
        }

        $style = get_option('astrologer_wp__chart_style', 'classic');
        if (!empty($style)) {
            $params['style'] = $style;
        }

        $splitChart = get_option('astrologer_wp__split_chart', false);
        $wheelOnly = get_option('astrologer_wp__wheel_only_chart', false);
        if ($splitChart || $wheelOnly) {
            $params['split_chart'] = true;
        }

        $transparentBackground = get_option('astrologer_wp__transparent_background', false);
        if ($transparentBackground) {
            $params['transparent_background'] = true;
        }

        $showHousePositionComparison = get_option('astrologer_wp__show_house_position_comparison', '1');
        $params['show_house_position_comparison'] = (bool) $showHousePositionComparison;

        $showCuspPositionComparison = get_option('astrologer_wp__show_cusp_position_comparison', '1');
        $params['show_cusp_position_comparison'] = (bool) $showCuspPositionComparison;

        $showDegreeIndicators = get_option('astrologer_wp__show_degree_indicators', '1');
        $params['show_degree_indicators'] = (bool) $showDegreeIndicators;

        $showAspectIcons = get_option('astrologer_wp__show_aspect_icons', '1');
        $params['show_aspect_icons'] = (bool) $showAspectIcons;

        $showZodiacBackgroundRing = get_option('astrologer_wp__show_zodiac_background_ring', '1');
        $params['show_zodiac_background_ring'] = (bool) $showZodiacBackgroundRing;

        $doubleChartAspectGridType = get_option('astrologer_wp__double_chart_aspect_grid_type', 'list');
        if (!empty($doubleChartAspectGridType)) {
            $params['double_chart_aspect_grid_type'] = $doubleChartAspectGridType;
        }

        return $params;
    }

    /**
     * Make a POST request to the Astrologer API.
     *
     * @param string $endpoint The API endpoint path.
     * @param array $body The request body.
     * @param string $chartType Human-readable chart type for error messages.
     *
     * @return array The response data.
     */
    private function makeRequest(string $endpoint, array $body, string $chartType = 'chart'): array {
        $baseUrl = $this->getApiBaseUrl();
        $url = $baseUrl . $endpoint;

        $headers = array(
            'Content-Type' => 'application/json',
        );

        if ($this->isCustomEndpoint()) {
            // Custom endpoint: send API key as Bearer token or X-API-Key
            if (!empty($this->apiKey)) {
                $headers['x-rapidapi-key'] = $this->apiKey;
            }
        } else {
            // RapidAPI: send RapidAPI-specific headers
            $headers['x-rapidapi-host'] = self::X_RAPIDAPI_HOST;
            $headers['x-rapidapi-key'] = $this->apiKey;
        }

        $response = wp_remote_post($url, array(
            'headers' => $headers,
            'body' => json_encode($body),
            'timeout' => 30
        ));

        if (is_wp_error($response)) {
            error_log('AstrologerWP - Error fetching ' . $chartType . ': ' . $response->get_error_message());

            return [
                'chart' => null,
                'chart_wheel' => null,
                'chart_grid' => null,
                'chart_data' => null,
                'error' => 'Unable to retrieve ' . $chartType . '. Contact the site administrator for more information.'
            ];
        }

        $statusCode = wp_remote_retrieve_response_code($response);
        $responseBody = wp_remote_retrieve_body($response);
        $data = json_decode($responseBody, true);

        if ($statusCode !== 200 || empty($data) || (isset($data['status']) && $data['status'] === 'ERROR')) {
            $errorMessage = isset($data['message']) ? $data['message'] : 'Unknown error';
            error_log('AstrologerWP - API error for ' . $chartType . ' (HTTP ' . $statusCode . '): ' . $errorMessage);
            error_log('AstrologerWP - Response: ' . $responseBody);

            return [
                'chart' => null,
                'chart_wheel' => null,
                'chart_grid' => null,
                'chart_data' => null,
                'error' => 'Unable to retrieve ' . $chartType . '. ' . esc_html($errorMessage)
            ];
        }

        $data['error'] = null;
        return $data;
    }

    /**
     * Retrieve birth chart from Astrologer API.
     *
     * @param Subject $subject
     *
     * @return array The birth chart data.
     */
    public function getBirthChart(Subject $subject): array {
        $body = array_merge(
            ['subject' => $subject->toArray()],
            $this->getRenderingParams()
        );

        return $this->makeRequest('/api/v5/chart/birth-chart', $body, 'birth chart');
    }

    /**
     * Retrieve synastry chart from Astrologer API.
     *
     * @param Subject $firstSubject
     * @param Subject $secondSubject
     *
     * @return array The synastry chart data.
     */
    public function getSynastryChart(
        Subject $firstSubject,
        Subject $secondSubject
    ): array {
        $body = array_merge(
            [
                'first_subject' => $firstSubject->toArray(),
                'second_subject' => $secondSubject->toArray(),
                'include_house_comparison' => true,
                'include_relationship_score' => true,
            ],
            $this->getRenderingParams()
        );

        return $this->makeRequest('/api/v5/chart/synastry', $body, 'synastry chart');
    }

    /**
     * Retrieve transit chart from Astrologer API.
     *
     * @param Subject $firstSubject
     * @param Subject $transitSubject
     *
     * @return array The transit chart data.
     */
    public function getTransitChart(
        Subject $firstSubject,
        Subject $transitSubject
    ): array {
        $transitArray = $transitSubject->toArray();
        unset($transitArray['zodiac_type']);
        unset($transitArray['houses_system_identifier']);
        unset($transitArray['perspective_type']);
        unset($transitArray['sidereal_mode']);

        $body = array_merge(
            [
                'first_subject' => $firstSubject->toArray(),
                'transit_subject' => $transitArray,
                'include_house_comparison' => true,
            ],
            $this->getRenderingParams()
        );

        return $this->makeRequest('/api/v5/chart/transit', $body, 'transit chart');
    }

    /**
     * Retrieve composite chart from Astrologer API.
     *
     * @param Subject $firstSubject
     * @param Subject $secondSubject
     *
     * @return array The composite chart data.
     */
    public function getCompositeChart(
        Subject $firstSubject,
        Subject $secondSubject
    ): array {
        $body = array_merge(
            [
                'first_subject' => $firstSubject->toArray(),
                'second_subject' => $secondSubject->toArray(),
            ],
            $this->getRenderingParams()
        );

        return $this->makeRequest('/api/v5/chart/composite', $body, 'composite chart');
    }

    /**
     * Retrieve solar return chart from Astrologer API.
     *
     * @param Subject $subject
     * @param int $year
     * @param int|null $month
     * @param int|null $day
     * @param string $wheelType 'dual' or 'single'
     * @param array|null $returnLocation Optional return location override.
     *
     * @return array The solar return chart data.
     */
    public function getSolarReturnChart(
        Subject $subject,
        int $year,
        ?int $month = null,
        ?int $day = null,
        string $wheelType = 'dual',
        ?array $returnLocation = null
    ): array {
        $body = array_merge(
            [
                'subject' => $subject->toArray(),
                'year' => $year,
                'wheel_type' => $wheelType,
                'include_house_comparison' => ($wheelType === 'dual'),
            ],
            $this->getRenderingParams()
        );

        if ($month !== null) {
            $body['month'] = $month;
        }

        if ($day !== null) {
            $body['day'] = $day;
        }

        if ($returnLocation !== null) {
            $body['return_location'] = $returnLocation;
        }

        return $this->makeRequest('/api/v5/chart/solar-return', $body, 'solar return chart');
    }

    /**
     * Retrieve lunar return chart from Astrologer API.
     *
     * @param Subject $subject
     * @param int $year
     * @param int|null $month
     * @param int|null $day
     * @param string $wheelType 'dual' or 'single'
     * @param array|null $returnLocation Optional return location override.
     *
     * @return array The lunar return chart data.
     */
    public function getLunarReturnChart(
        Subject $subject,
        int $year,
        ?int $month = null,
        ?int $day = null,
        string $wheelType = 'dual',
        ?array $returnLocation = null
    ): array {
        $body = array_merge(
            [
                'subject' => $subject->toArray(),
                'year' => $year,
                'wheel_type' => $wheelType,
                'include_house_comparison' => ($wheelType === 'dual'),
            ],
            $this->getRenderingParams()
        );

        if ($month !== null) {
            $body['month'] = $month;
        }

        if ($day !== null) {
            $body['day'] = $day;
        }

        if ($returnLocation !== null) {
            $body['return_location'] = $returnLocation;
        }

        return $this->makeRequest('/api/v5/chart/lunar-return', $body, 'lunar return chart');
    }

    /**
     * Retrieve moon phase data from Astrologer API.
     *
     * @param int $year
     * @param int $month
     * @param int $day
     * @param int $hour
     * @param int $minute
     * @param float $latitude
     * @param float $longitude
     * @param string $timezone
     *
     * @return array The moon phase data.
     */
    public function getMoonPhase(
        int $year,
        int $month,
        int $day,
        int $hour,
        int $minute,
        float $latitude,
        float $longitude,
        string $timezone
    ): array {
        $body = [
            'year' => $year,
            'month' => $month,
            'day' => $day,
            'hour' => $hour,
            'minute' => $minute,
            'second' => 0,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'timezone' => $timezone,
        ];

        return $this->makeRequest('/api/v5/moon-phase', $body, 'moon phase');
    }

    /**
     * Retrieve current sky chart from Astrologer API.
     *
     * @return array The now chart data.
     */
    public function getNowChart(): array {
        $body = $this->getRenderingParams();

        $zodiacType = get_option('astrologer_wp__zodiac_type', 'Tropical');
        $body['zodiac_type'] = $zodiacType;

        $houseSystem = get_option('astrologer_wp__houses_system', 'P');
        $body['houses_system_identifier'] = $houseSystem;

        $perspectiveType = get_option('astrologer_wp__perspective_type', 'Apparent Geocentric');
        $body['perspective_type'] = $perspectiveType;

        if ($zodiacType === 'Sidereal') {
            $siderealMode = get_option('astrologer_wp__sidereal_mode', '');
            if (!empty($siderealMode)) {
                $body['sidereal_mode'] = $siderealMode;
            }
        }

        return $this->makeRequest('/api/v5/now/chart', $body, 'current sky chart');
    }

    /**
     * Retrieve compatibility score from Astrologer API.
     *
     * @param Subject $firstSubject
     * @param Subject $secondSubject
     *
     * @return array The compatibility score data.
     */
    public function getCompatibilityScore(
        Subject $firstSubject,
        Subject $secondSubject
    ): array {
        $body = [
            'first_subject' => $firstSubject->toArray(),
            'second_subject' => $secondSubject->toArray(),
        ];

        return $this->makeRequest('/api/v5/compatibility-score', $body, 'compatibility score');
    }
}
