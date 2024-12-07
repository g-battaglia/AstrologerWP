<?php

namespace AstrologerWP\Utils;

class GeonamesAdapter {
    private $username;
    private $cityName;
    private $countryCode;
    private $baseUrl = "http://api.geonames.org/searchJSON";
    private $timezoneUrl = "http://api.geonames.org/timezoneJSON";

    /**
     * GeonamesAdapter constructor.
     *
     * @param string $cityName The name of the city.
     * @param string $countryCode The country code of the city in ISO 3166-1 alpha-2 format (e.g. "US").
     * @param string $username The username for the GeoNames API.
     *      You can get one by registering at http://www.geonames.org/login.
     */
    public function __construct($cityName, $countryCode, $username) {
        $this->username = $username;
        $this->cityName = $cityName;
        $this->countryCode = $countryCode;
    }

    /**
     * Get the timezone data from GeoNames timezone API.
     *
     * @param int|float $lat The latitude of the city.
     * @param int|float $lon The longitude of the city.
     *
     * @return array The timezone data.
     */
    private function getTimezoneData(string $lat, string $lng): array {
        $cacheKey = 'geonames_timezone_data_' . md5($lat . $lng);
        $cachedData = get_transient($cacheKey);

        if ($cachedData !== false) {
            return $cachedData;
        }

        $timezoneData = [];
        $url = $this->timezoneUrl . "?lat=$lat&lng=$lng&username=" . $this->username;

        error_log("Requesting data from GeoName timezones: $url");

        try {
            $response = wp_remote_get($url, [
                'timeout' => 10,
            ]);

            if (is_wp_error($response)) {
                throw new \Exception($response->get_error_message());
            }

            $responseBody = wp_remote_retrieve_body($response);
            $responseJson = json_decode($responseBody, true);
            $timezoneData["timezonestr"] = $responseJson["timezoneId"] ?? null;

            // Cache the response for 1 hour
            set_transient($cacheKey, $timezoneData, HOUR_IN_SECONDS);
        } catch (\Exception $e) {
            error_log("Error fetching {$this->timezoneUrl}: " . $e->getMessage());
            return [];
        }

        return $timezoneData;
    }

    /**
     * Get the city data from GeoNames basic API.
     *
     * @param string $cityName The name of the city.
     * @param string $countryCode The country code of the city.
     *
     * @return array Array containing other arrays with the city data.
     */
    private function getBasicCityData(string $cityName): array {
        $cacheKey = 'geonames_city_data_' . md5($cityName);
        $cachedData = get_transient($cacheKey);

        if ($cachedData !== false) {
            return $cachedData;
        }

        $cityDataWithoutTz = [];
        $params = http_build_query([
            "q" => $cityName,
            "username" => $this->username,
            "maxRows" => 5,
            "style" => "SHORT",
            "featureClass" => ["A", "P"]
        ]);

        $url = $this->baseUrl . "?" . $params;

        try {
            $response = wp_remote_get($url, [
                'timeout' => 10,
            ]);

            if (is_wp_error($response)) {
                throw new \Exception($response->get_error_message());
            }

            $responseBody = wp_remote_retrieve_body($response);
            $responseJson = json_decode($responseBody, true);
            $cityDataWithoutTz = $responseJson["geonames"] ?? [];

            // Cache the response for 1 hour
            set_transient($cacheKey, $cityDataWithoutTz, HOUR_IN_SECONDS);
        } catch (\Exception $e) {
            error_log("Error fetching {$this->baseUrl}: " . $e->getMessage());
            return [];
        }

        return $cityDataWithoutTz;
    }

    /**
     * Get the serialized data from GeoNames API.
     *
     * @return array The serialized data.
     */
    public function getCityData(): array {
        $cityDataResponse = $this->getBasicCityData($this->cityName, $this->countryCode);

        if (empty($cityDataResponse)) {
            error_log("City data is empty");
            return [];
        }

        foreach ($cityDataResponse as $key => $cityData) {
            try {
                $timezoneResponse = $this->getTimezoneData($cityData["lat"], $cityData["lng"]);
                $cityDataResponse[$key] = array_merge($timezoneResponse, $cityData);
            } catch (\Exception $e) {
                error_log("Error fetching timezone: " . $e->getMessage());
                return [];
            }
        }

        return $cityDataResponse;
    }
}
