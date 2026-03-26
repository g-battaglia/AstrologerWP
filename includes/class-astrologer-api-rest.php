<?php
/**
 * REST API bridge for Astrologer API.
 *
 * This file manages the REST endpoints that proxy requests to the Astrologer API,
 * keeping the API key secure on the server side (never exposed to frontend JS).
 *
 * @package Astrologer_API_Playground
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class responsible for managing the plugin REST endpoints.
 *
 * It exposes WordPress REST endpoints that:
 * - Receive requests from the React frontend
 * - Add the API credentials stored in the settings
 * - Forward the requests to the Astrologer API
 * - Return the responses to the frontend
 *
 * This approach guarantees that the RapidAPI key is never visible
 * in the browser JavaScript code.
 *
 * @since 1.0.0
 */
class Astrologer_API_REST {

    /**
     * Namespace for REST endpoints.
     *
     * @var string
     */
    private const NAMESPACE = 'astrologer/v1';

    /**
     * Maximum number of API-proxied requests per window (per IP).
     *
     * @var int
     */
    private const RATE_LIMIT_MAX = 60;

    /**
     * Rate-limit window duration in seconds (1 minute).
     *
     * @var int
     */
    private const RATE_LIMIT_WINDOW = 60;

    /**
     * Constructor - registers REST API hooks.
     */
    public function __construct() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    /**
     * Registers all plugin REST endpoints.
     *
     * @return void
     */
    public function register_routes(): void {
        // =================================================================
        // NATAL CHART ENDPOINTS
        // =================================================================

        /**
         * POST /wp-json/astrologer/v1/natal-chart
         * Generates the SVG graphic of the natal chart.
         */
        register_rest_route(
            self::NAMESPACE,
            '/natal-chart',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'handle_natal_chart' ),
                'permission_callback' => '__return_true', // Public
                'args'                => $this->get_subject_args(),
            )
        );

        /**
         * POST /wp-json/astrologer/v1/natal-chart-data
         * Returns the JSON data of the natal chart (aspects, planets, houses).
         */
        register_rest_route(
            self::NAMESPACE,
            '/natal-chart-data',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'handle_natal_chart_data' ),
                'permission_callback' => '__return_true',
                'args'                => $this->get_subject_args(),
            )
        );

        /**
         * POST /wp-json/astrologer/v1/subject
         * Returns the complete data for the subject.
         */
        register_rest_route(
            self::NAMESPACE,
            '/subject',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'handle_subject' ),
                'permission_callback' => '__return_true',
                'args'                => $this->get_subject_args(),
            )
        );

        // =================================================================
        // SYNASTRY CHART ENDPOINTS
        // =================================================================

        /**
         * POST /wp-json/astrologer/v1/synastry-chart
         * Generates the SVG graphic for a synastry chart.
         */
        register_rest_route(
            self::NAMESPACE,
            '/synastry-chart',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'handle_synastry_chart' ),
                'permission_callback' => '__return_true',
                'args'                => $this->get_dual_subject_args(),
            )
        );

        /**
         * POST /wp-json/astrologer/v1/synastry-chart-data
         * Returns the JSON data for the synastry chart.
         */
        register_rest_route(
            self::NAMESPACE,
            '/synastry-chart-data',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'handle_synastry_chart_data' ),
                'permission_callback' => '__return_true',
                'args'                => $this->get_dual_subject_args(),
            )
        );

        // =================================================================
        // TRANSIT CHART ENDPOINTS
        // =================================================================

        /**
         * POST /wp-json/astrologer/v1/transit-chart
         * Generates the SVG graphic for transits.
         */
        register_rest_route(
            self::NAMESPACE,
            '/transit-chart',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'handle_transit_chart' ),
                'permission_callback' => '__return_true',
                'args'                => $this->get_transit_args(),
            )
        );

        /**
         * POST /wp-json/astrologer/v1/transit-chart-data
         * Returns the JSON data for transits.
         */
        register_rest_route(
            self::NAMESPACE,
            '/transit-chart-data',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'handle_transit_chart_data' ),
                'permission_callback' => '__return_true',
                'args'                => $this->get_transit_args(),
            )
        );

        // =================================================================
        // CURRENT MOMENT (NOW) ENDPOINTS
        // =================================================================

        register_rest_route(
            self::NAMESPACE,
            '/now-subject',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'handle_now_subject' ),
                'permission_callback' => '__return_true',
                'args'                => $this->get_now_args(),
            )
        );

        register_rest_route(
            self::NAMESPACE,
            '/now-chart',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'handle_now_chart' ),
                'permission_callback' => '__return_true',
                'args'                => $this->get_now_args(),
            )
        );

        // =================================================================
        // COMPOSITE CHART ENDPOINTS
        // =================================================================

        register_rest_route(
            self::NAMESPACE,
            '/composite-chart',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'handle_composite_chart' ),
                'permission_callback' => '__return_true',
                'args'                => $this->get_dual_subject_args(),
            )
        );

        register_rest_route(
            self::NAMESPACE,
            '/composite-chart-data',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'handle_composite_chart_data' ),
                'permission_callback' => '__return_true',
                'args'                => $this->get_dual_subject_args(),
            )
        );

        // =================================================================
        // SOLAR / LUNAR RETURN ENDPOINTS
        // =================================================================

        register_rest_route(
            self::NAMESPACE,
            '/solar-return-chart-data',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'handle_solar_return_chart_data' ),
                'permission_callback' => '__return_true',
                'args'                => $this->get_return_args(),
            )
        );

        register_rest_route(
            self::NAMESPACE,
            '/solar-return-chart',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'handle_solar_return_chart' ),
                'permission_callback' => '__return_true',
                'args'                => $this->get_return_args(),
            )
        );

        register_rest_route(
            self::NAMESPACE,
            '/lunar-return-chart-data',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'handle_lunar_return_chart_data' ),
                'permission_callback' => '__return_true',
                'args'                => $this->get_return_args(),
            )
        );

        register_rest_route(
            self::NAMESPACE,
            '/lunar-return-chart',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'handle_lunar_return_chart' ),
                'permission_callback' => '__return_true',
                'args'                => $this->get_return_args(),
            )
        );

        // =================================================================
        // COMPATIBILITY SCORE ENDPOINT
        // =================================================================

        register_rest_route(
            self::NAMESPACE,
            '/compatibility-score',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'handle_compatibility_score' ),
                'permission_callback' => '__return_true',
                'args'                => $this->get_dual_subject_args(),
            )
        );

        // =================================================================
        // CITY SEARCH (GEONAMES PROXY) ENDPOINT
        // =================================================================

        register_rest_route(
            self::NAMESPACE,
            '/city-search',
            array(
                'methods'             => 'GET',
                'callback'            => array( $this, 'handle_city_search' ),
                'permission_callback' => '__return_true',
                'args'                => array(
                    'q' => array(
                        'required'          => true,
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                        'validate_callback' => static function ( $value ) {
                            return is_string( $value ) && strlen( trim( $value ) ) >= 2;
                        },
                    ),
                ),
            )
        );

        // =================================================================
        // SETTINGS (ADMIN) ENDPOINTS
        // =================================================================

        register_rest_route(
            self::NAMESPACE,
            '/settings-get',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'handle_get_settings' ),
                'permission_callback' => array( $this, 'check_settings_permissions' ),
                'args'                => array(),
            )
        );

        register_rest_route(
            self::NAMESPACE,
            '/settings-update',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'handle_update_settings' ),
                'permission_callback' => array( $this, 'check_settings_permissions' ),
                'args'                => array(
                    'settings' => array(
                        'type'     => 'object',
                        'required' => true,
                    ),
                ),
            )
        );
    }

    // =========================================================================
    // HANDLERS FOR ENDPOINTS
    // =========================================================================

    /**
     * Handler for POST /natal-chart.
     *
     * @param WP_REST_Request $request REST request.
     * @return WP_REST_Response|WP_Error
     */
    public function handle_natal_chart( WP_REST_Request $request ) {
        $subject = $this->extract_subject( $request );
        $body    = array(
            'subject' => $this->apply_config( $subject ),
            ...$this->get_chart_config(),
        );

        return $this->proxy_request( '/api/v5/chart/birth-chart', $body );
    }

    /**
     * Handler for POST /natal-chart-data.
     *
     * @param WP_REST_Request $request REST request.
     * @return WP_REST_Response|WP_Error
     */
    public function handle_natal_chart_data( WP_REST_Request $request ) {
        $subject = $this->extract_subject( $request );
        $body    = array(
            'subject' => $this->apply_config( $subject ),
            ...$this->get_data_config(),
        );

        return $this->proxy_request( '/api/v5/chart-data/birth-chart', $body );
    }

    /**
     * Handler for POST /subject.
     *
     * @param WP_REST_Request $request REST request.
     * @return WP_REST_Response|WP_Error
     */
    public function handle_subject( WP_REST_Request $request ) {
        $subject = $this->extract_subject( $request );
        $body    = array(
            'subject' => $this->apply_config( $subject ),
            ...$this->get_data_config(),
        );

        return $this->proxy_request( '/api/v5/subject', $body );
    }

    /**
     * Handler for POST /synastry-chart.
     *
     * @param WP_REST_Request $request REST request.
     * @return WP_REST_Response|WP_Error
     */
    public function handle_synastry_chart( WP_REST_Request $request ) {
        $first  = $this->extract_first_subject( $request );
        $second = $this->extract_second_subject( $request );
        $body   = array(
            'first_subject'  => $this->apply_config( $first ),
            'second_subject' => $this->apply_config( $second ),
            ...$this->get_chart_config(),
            ...$this->get_synastry_flags(),
        );

        return $this->proxy_request( '/api/v5/chart/synastry', $body );
    }

    /**
     * Handler for POST /synastry-chart-data.
     *
     * @param WP_REST_Request $request REST request.
     * @return WP_REST_Response|WP_Error
     */
    public function handle_synastry_chart_data( WP_REST_Request $request ) {
        $first  = $this->extract_first_subject( $request );
        $second = $this->extract_second_subject( $request );
        $body   = array(
            'first_subject'  => $this->apply_config( $first ),
            'second_subject' => $this->apply_config( $second ),
            ...$this->get_data_config(),
            ...$this->get_synastry_flags(),
        );

        return $this->proxy_request( '/api/v5/chart-data/synastry', $body );
    }

    /**
     * Handler for POST /transit-chart.
     *
     * @param WP_REST_Request $request REST request.
     * @return WP_REST_Response|WP_Error
     */
    public function handle_transit_chart( WP_REST_Request $request ) {
        $settings                 = Astrologer_API_Settings::get_settings();
        $natal_subject            = $request->get_param( 'natal_subject' );
        $transit_subject          = $request->get_param( 'transit_subject' );

        if ( $request->has_param( 'include_house_comparison' ) ) {
            $include_house_comparison = (bool) $request->get_param( 'include_house_comparison' );
        } else {
            $include_house_comparison = (bool) $settings['transit_include_house_comparison'];
        }

        $body = array(
            'first_subject'            => $this->apply_config( $natal_subject ),
            'transit_subject'          => $transit_subject,
            'include_house_comparison' => $include_house_comparison,
            ...$this->get_chart_config(),
        );

        return $this->proxy_request( '/api/v5/chart/transit', $body );
    }

    /**
     * Handler for POST /transit-chart-data.
     *
     * @param WP_REST_Request $request REST request.
     * @return WP_REST_Response|WP_Error
     */
    public function handle_transit_chart_data( WP_REST_Request $request ) {
        $settings                 = Astrologer_API_Settings::get_settings();
        $natal_subject            = $request->get_param( 'natal_subject' );
        $transit_subject          = $request->get_param( 'transit_subject' );

        if ( $request->has_param( 'include_house_comparison' ) ) {
            $include_house_comparison = (bool) $request->get_param( 'include_house_comparison' );
        } else {
            $include_house_comparison = (bool) $settings['transit_include_house_comparison'];
        }

        $body = array(
            'first_subject'            => $this->apply_config( $natal_subject ),
            'transit_subject'          => $transit_subject,
            'include_house_comparison' => $include_house_comparison,
            ...$this->get_data_config(),
        );

        return $this->proxy_request( '/api/v5/chart-data/transit', $body );
    }

    /**
     * Handler for GET /city-search.
     *
     * Proxies city name queries to the GeoNames searchJSON API so
     * the frontend can offer autocomplete without exposing the
     * GeoNames username to the browser.
     *
     * @param WP_REST_Request $request REST request.
     * @return WP_REST_Response|WP_Error
     */
    public function handle_city_search( WP_REST_Request $request ) {
        // Rate-limit check (skip for logged-in admins)
        $rate_error = $this->check_rate_limit();
        if ( is_wp_error( $rate_error ) ) {
            return $rate_error;
        }

        $settings = Astrologer_API_Settings::get_settings();
        $username = $settings['geonames_username'] ?? '';

        if ( empty( $username ) ) {
            return new WP_Error(
                'geonames_not_configured',
                __( 'GeoNames username is not configured. Go to Settings > Astrologer API.', 'astrologer-api' ),
                array( 'status' => 500 )
            );
        }

        $query = $request->get_param( 'q' );

        $url = add_query_arg(
            array(
                'q'           => $query,
                'maxRows'     => 10,
                'featureClass' => 'P', // populated places only
                'username'    => $username,
                'style'       => 'MEDIUM',
                'type'        => 'json',
            ),
            'https://secure.geonames.org/searchJSON'
        );

        $response = wp_remote_get( $url, array( 'timeout' => 10 ) );

        if ( is_wp_error( $response ) ) {
            return new WP_Error(
                'geonames_connection_error',
                $response->get_error_message(),
                array( 'status' => 502 )
            );
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        $body_raw    = wp_remote_retrieve_body( $response );

        if ( $status_code >= 400 ) {
            $geo_error_msg = wp_strip_all_tags( $body_raw );
            if ( strlen( $geo_error_msg ) > 200 ) {
                $geo_error_msg = substr( $geo_error_msg, 0, 200 ) . '...';
            }
            return new WP_Error(
                'geonames_error',
                sprintf( __( 'GeoNames Error %d: %s', 'astrologer-api' ), $status_code, $geo_error_msg ),
                array( 'status' => $status_code )
            );
        }

        $data = json_decode( $body_raw, true );

        if ( ! is_array( $data ) || ! isset( $data['geonames'] ) ) {
            return new WP_REST_Response( array( 'results' => array() ), 200 );
        }

        // Map to a slim response — only the fields the frontend needs
        $results = array();
        foreach ( $data['geonames'] as $place ) {
            $results[] = array(
                'name'      => sanitize_text_field( $place['name'] ?? '' ),
                'country'   => sanitize_text_field( $place['countryCode'] ?? '' ),
                'latitude'  => (float) ( $place['lat'] ?? 0 ),
                'longitude' => (float) ( $place['lng'] ?? 0 ),
                'timezone'  => sanitize_text_field( $place['timezone']['timeZoneId'] ?? '' ),
                'admin'     => sanitize_text_field( $place['adminName1'] ?? '' ),
            );
        }

        return new WP_REST_Response( array( 'results' => $results ), 200 );
    }

    // =========================================================================
    // PRIVATE HELPER METHODS
    // =========================================================================

    /**
     * Sends a request to the Astrologer API and returns the response.
     *
     * @param string $endpoint API endpoint (for example '/v5/chart/birth-chart').
     * @param array  $body     Request body.
     * @return WP_REST_Response|WP_Error
     */
    private function proxy_request( string $endpoint, array $body ) {
        // Rate-limit check (skip for logged-in admins)
        $rate_error = $this->check_rate_limit();
        if ( is_wp_error( $rate_error ) ) {
            return $rate_error;
        }

        $settings = Astrologer_API_Settings::get_settings();
        $base_url = rtrim( $settings['base_url'], '/' );
        $url      = $base_url . $endpoint;

        // Ensure the API key is configured
        if ( empty( $settings['rapidapi_key'] ) ) {
            return new WP_Error(
                'missing_api_key',
                __( 'RapidAPI key is not configured. Go to Settings > Astrologer API.', 'astrologer-api' ),
                array( 'status' => 500 )
            );
        }

        // Headers for RapidAPI
        $headers = array(
            'Content-Type'    => 'application/json',
            'X-RapidAPI-Host' => wp_parse_url( $base_url, PHP_URL_HOST ),
            'X-RapidAPI-Key'  => $settings['rapidapi_key'],
        );

        // Execute the HTTP request
        $response = wp_remote_post(
            $url,
            array(
                'headers' => $headers,
                'body'    => wp_json_encode( $body ),
                'timeout' => 30,
            )
        );

        // Handle connection errors
        if ( is_wp_error( $response ) ) {
            return new WP_Error(
                'api_connection_error',
                $response->get_error_message(),
                array( 'status' => 500 )
            );
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        $body_raw    = wp_remote_retrieve_body( $response );

        // If the API returned an error status code
        if ( $status_code >= 400 ) {
            $error_msg = wp_strip_all_tags( $body_raw );
            if ( strlen( $error_msg ) > 200 ) {
                $error_msg = substr( $error_msg, 0, 200 ) . '...';
            }
            return new WP_Error(
                'api_error',
                sprintf( __( 'API Error %d: %s', 'astrologer-api' ), $status_code, $error_msg ),
                array( 'status' => $status_code )
            );
        }

        // Decode and return the response
        $data = json_decode( $body_raw, true );

        if ( null === $data && json_last_error() !== JSON_ERROR_NONE ) {
            // If the response is not valid JSON, it might be raw SVG
            return new WP_REST_Response( array( 'svg' => $body_raw ), 200 );
        }

        return new WP_REST_Response( $data, 200 );
    }

    /**
     * Applies saved configuration to a subject payload.
     *
     * @param array $subject Subject data.
     * @return array Subject with configuration applied.
     */
    private function apply_config( array $subject ): array {
        $settings = Astrologer_API_Settings::get_settings();

        return array_merge( $subject, array(
            'zodiac_type'              => $settings['sidereal'] ? 'Sidereal' : 'Tropical',
            'sidereal_mode'            => $settings['sidereal'] ? $settings['sidereal_mode'] : null,
            'perspective_type'         => $settings['perspective_type'] ?? 'Apparent Geocentric',
            'houses_system_identifier' => $settings['house_system'],
            'geonames_username'        => $settings['geonames_username'] ?: null,
        ) );
    }

    /**
     * Returns configuration for chart requests.
     *
     * @return array
     */
    private function get_chart_config(): array {
        $settings = Astrologer_API_Settings::get_settings();

        $allowed_active_points  = Astrologer_API_Settings::get_allowed_active_points();
        $allowed_active_aspects = Astrologer_API_Settings::get_allowed_active_aspects();

        $filter_points = static function ( array $points ) use ( $allowed_active_points ): array {
            $allowed = array_fill_keys( $allowed_active_points, true );
            $out     = array();
            foreach ( $points as $p ) {
                $p = sanitize_text_field( (string) $p );
                if ( '' === $p ) {
                    continue;
                }
                if ( isset( $allowed[ $p ] ) ) {
                    $out[] = $p;
                }
            }
            return array_values( array_unique( $out ) );
        };

        $filter_aspects = static function ( array $aspects ) use ( $allowed_active_aspects ): array {
            $allowed = array_fill_keys( $allowed_active_aspects, true );
            $out     = array();
            foreach ( $aspects as $a ) {
                if ( ! is_array( $a ) ) {
                    continue;
                }
                $name = isset( $a['name'] ) ? sanitize_text_field( (string) $a['name'] ) : '';
                if ( '' === $name || ! isset( $allowed[ $name ] ) ) {
                    continue;
                }
                $orb_raw = isset( $a['orb'] ) ? (float) $a['orb'] : 0.0;
                $orb     = $orb_raw > 0 ? $orb_raw : 1.0;
                $out[]   = array( 'name' => $name, 'orb' => $orb );
            }
            return array_values( $out );
        };

        // Active points and aspects: if not configured (or invalid), use defaults
        $active_points_raw  = ( ! empty( $settings['active_points'] ) && is_array( $settings['active_points'] ) ) ? $settings['active_points'] : $this->get_default_active_points();
        $active_aspects_raw = ( ! empty( $settings['active_aspects'] ) && is_array( $settings['active_aspects'] ) ) ? $settings['active_aspects'] : $this->get_default_active_aspects();

        $active_points  = $filter_points( $active_points_raw );
        $active_aspects = $filter_aspects( $active_aspects_raw );

        if ( empty( $active_points ) ) {
            $active_points = $this->get_default_active_points();
        }
        if ( empty( $active_aspects ) ) {
            $active_aspects = $this->get_default_active_aspects();
        }
        $config = array(
            // Rendering options (only for /chart/* endpoints)
            'language'                    => $settings['language'],
            'theme'                       => $settings['theme'],
            'split_chart'                 => (bool) $settings['split_chart'],
            'transparent_background'      => (bool) $settings['transparent_background'],
            'show_house_position_comparison' => (bool) $settings['show_house_position_comparison'],
            'show_cusp_position_comparison'  => (bool) $settings['show_cusp_position_comparison'],
            'show_degree_indicators'         => (bool) $settings['show_degree_indicators'],

            // Computation options
            'active_points'       => $active_points,
            'active_aspects'      => $active_aspects,
            'distribution_method' => $settings['distribution_method'],
        );

        // Optional custom title for rendered charts (max 40 chars, enforced in settings)
        if ( ! empty( $settings['custom_title'] ) && is_string( $settings['custom_title'] ) ) {
            $config['custom_title'] = $settings['custom_title'];
        }

        // Send custom_distribution_weights only when non-empty and valid
        if ( ! empty( $settings['custom_distribution_weights'] ) && is_array( $settings['custom_distribution_weights'] ) ) {
            $config['custom_distribution_weights'] = $settings['custom_distribution_weights'];
        }

        return $config;
    }

    /**
     * Returns default flags for synastry requests.
     *
     * @return array
     */
    private function get_synastry_flags(): array {
        $settings = Astrologer_API_Settings::get_settings();

        return array(
            'include_house_comparison'   => (bool) $settings['synastry_include_house_comparison'],
            'include_relationship_score' => (bool) $settings['synastry_include_relationship_score'],
        );
    }

    /**
     * Returns configuration for data-only requests.
     *
     * @return array
     */
    private function get_data_config(): array {
        $settings = Astrologer_API_Settings::get_settings();

        $allowed_active_points  = Astrologer_API_Settings::get_allowed_active_points();
        $allowed_active_aspects = Astrologer_API_Settings::get_allowed_active_aspects();

        $filter_points = static function ( array $points ) use ( $allowed_active_points ): array {
            $allowed = array_fill_keys( $allowed_active_points, true );
            $out     = array();
            foreach ( $points as $p ) {
                $p = sanitize_text_field( (string) $p );
                if ( '' === $p ) {
                    continue;
                }
                if ( isset( $allowed[ $p ] ) ) {
                    $out[] = $p;
                }
            }
            return array_values( array_unique( $out ) );
        };

        $filter_aspects = static function ( array $aspects ) use ( $allowed_active_aspects ): array {
            $allowed = array_fill_keys( $allowed_active_aspects, true );
            $out     = array();
            foreach ( $aspects as $a ) {
                if ( ! is_array( $a ) ) {
                    continue;
                }
                $name = isset( $a['name'] ) ? sanitize_text_field( (string) $a['name'] ) : '';
                if ( '' === $name || ! isset( $allowed[ $name ] ) ) {
                    continue;
                }
                $orb_raw = isset( $a['orb'] ) ? (float) $a['orb'] : 0.0;
                $orb     = $orb_raw > 0 ? $orb_raw : 1.0;
                $out[]   = array( 'name' => $name, 'orb' => $orb );
            }
            return array_values( $out );
        };

        $active_points_raw  = ( ! empty( $settings['active_points'] ) && is_array( $settings['active_points'] ) ) ? $settings['active_points'] : $this->get_default_active_points();
        $active_aspects_raw = ( ! empty( $settings['active_aspects'] ) && is_array( $settings['active_aspects'] ) ) ? $settings['active_aspects'] : $this->get_default_active_aspects();

        $active_points  = $filter_points( $active_points_raw );
        $active_aspects = $filter_aspects( $active_aspects_raw );

        if ( empty( $active_points ) ) {
            $active_points = $this->get_default_active_points();
        }
        if ( empty( $active_aspects ) ) {
            $active_aspects = $this->get_default_active_aspects();
        }
        $config = array(
            'active_points'       => $active_points,
            'active_aspects'      => $active_aspects,
            'distribution_method' => $settings['distribution_method'],
        );

        if ( ! empty( $settings['custom_distribution_weights'] ) && is_array( $settings['custom_distribution_weights'] ) ) {
            $config['custom_distribution_weights'] = $settings['custom_distribution_weights'];
        }

        return $config;
    }

    // =========================================================================
    // EXTRA HANDLERS: NOW, COMPOSITE, RETURNS, COMPATIBILITY
    // =========================================================================

    /**
     * Handler for POST /now-subject.
     * Returns subject data for the current UTC moment (no chart).
     *
     * @param WP_REST_Request $request REST request.
     * @return WP_REST_Response|WP_Error
     */
    public function handle_now_subject( WP_REST_Request $request ) {
        $settings = Astrologer_API_Settings::get_settings();

        $body = array(
            'name'                    => sanitize_text_field( $request->get_param( 'name' ) ?? 'Now' ),
            'zodiac_type'             => $settings['sidereal'] ? 'Sidereal' : 'Tropical',
            'sidereal_mode'           => $settings['sidereal'] ? $settings['sidereal_mode'] : null,
            'perspective_type'        => $settings['perspective_type'] ?? 'Apparent Geocentric',
            'houses_system_identifier'=> $settings['house_system'],
        );

        return $this->proxy_request( '/api/v5/now/subject', $body );
    }

    /**
     * Handler for POST /now-chart.
     * Returns data and SVG chart for the current UTC moment.
     *
     * @param WP_REST_Request $request REST request.
     * @return WP_REST_Response|WP_Error
     */
    public function handle_now_chart( WP_REST_Request $request ) {
        $settings = Astrologer_API_Settings::get_settings();

        $subject_config = array(
            'name'                    => sanitize_text_field( $request->get_param( 'name' ) ?? 'Now' ),
            'zodiac_type'             => $settings['sidereal'] ? 'Sidereal' : 'Tropical',
            'sidereal_mode'           => $settings['sidereal'] ? $settings['sidereal_mode'] : null,
            'perspective_type'        => $settings['perspective_type'] ?? 'Apparent Geocentric',
            'houses_system_identifier'=> $settings['house_system'],
        );

        $body = array_merge( $subject_config, $this->get_chart_config() );

        return $this->proxy_request( '/api/v5/now/chart', $body );
    }

    /**
     * Handler for POST /composite-chart.
     * Generates the SVG composite chart.
     *
     * @param WP_REST_Request $request REST request.
     * @return WP_REST_Response|WP_Error
     */
    public function handle_composite_chart( WP_REST_Request $request ) {
        $first  = $this->extract_first_subject( $request );
        $second = $this->extract_second_subject( $request );

        $body = array(
            'first_subject'  => $this->apply_config( $first ),
            'second_subject' => $this->apply_config( $second ),
        );

        $body = array_merge( $body, $this->get_chart_config() );

        return $this->proxy_request( '/api/v5/chart/composite', $body );
    }

    /**
     * Handler for POST /composite-chart-data.
     * Returns JSON data for the composite chart.
     *
     * @param WP_REST_Request $request REST request.
     * @return WP_REST_Response|WP_Error
     */
    public function handle_composite_chart_data( WP_REST_Request $request ) {
        $first  = $this->extract_first_subject( $request );
        $second = $this->extract_second_subject( $request );

        $body = array(
            'first_subject'  => $this->apply_config( $first ),
            'second_subject' => $this->apply_config( $second ),
        );

        $body = array_merge( $body, $this->get_data_config() );

        return $this->proxy_request( '/api/v5/chart-data/composite', $body );
    }

    /**
     * Builds the request body for Solar/Lunar Return endpoints.
     *
     * @param WP_REST_Request $request  REST request.
     * @param bool            $for_chart True to include chart config, false for data only.
     * @return array
     */
    private function build_return_body( WP_REST_Request $request, bool $for_chart ): array {
        $settings    = Astrologer_API_Settings::get_settings();
        $subject_raw = $request->get_param( 'subject' );
        $subject     = is_array( $subject_raw ) ? $this->sanitize_subject( $subject_raw ) : array();

        $year  = intval( $request->get_param( 'year' ) );
        $month = $request->get_param( 'month' );
        $iso   = $request->get_param( 'iso_datetime' );

        $body = array(
            'subject' => $this->apply_config( $subject ),
            'year'    => $year > 0 ? $year : intval( gmdate( 'Y' ) ),
            'month'   => null !== $month ? intval( $month ) : null,
            'iso_datetime' => is_string( $iso ) && '' !== $iso ? $iso : null,
        );

        // wheel_type and include_house_comparison (optional)
        $wheel_type = $request->get_param( 'wheel_type' );
        if ( is_string( $wheel_type ) && '' !== $wheel_type ) {
            $body['wheel_type'] = $wheel_type;
        } else {
            $body['wheel_type'] = $settings['returns_wheel_type'];
        }

        if ( $request->has_param( 'include_house_comparison' ) ) {
            $body['include_house_comparison'] = (bool) $request->get_param( 'include_house_comparison' );
        } else {
            $body['include_house_comparison'] = (bool) $settings['returns_include_house_comparison'];
        }

        // Optional return_location
        $return_location = $request->get_param( 'return_location' );
        if ( is_array( $return_location ) ) {
            $body['return_location'] = array(
                'city'      => sanitize_text_field( $return_location['city'] ?? '' ),
                'nation'    => sanitize_text_field( $return_location['nation'] ?? '' ),
                'longitude' => isset( $return_location['longitude'] ) ? (float) $return_location['longitude'] : null,
                'latitude'  => isset( $return_location['latitude'] ) ? (float) $return_location['latitude'] : null,
                'timezone'  => sanitize_text_field( $return_location['timezone'] ?? 'UTC' ),
            );
        }

        if ( $for_chart ) {
            $body = array_merge( $body, $this->get_chart_config() );
        } else {
            $body = array_merge( $body, $this->get_data_config() );
        }

        return $body;
    }

    /**
     * Handler for POST /solar-return-chart-data.
     *
     * @param WP_REST_Request $request REST request.
     * @return WP_REST_Response|WP_Error
     */
    public function handle_solar_return_chart_data( WP_REST_Request $request ) {
        $body = $this->build_return_body( $request, false );

        return $this->proxy_request( '/api/v5/chart-data/solar-return', $body );
    }

    /**
     * Handler for POST /solar-return-chart.
     *
     * @param WP_REST_Request $request REST request.
     * @return WP_REST_Response|WP_Error
     */
    public function handle_solar_return_chart( WP_REST_Request $request ) {
        $body = $this->build_return_body( $request, true );

        return $this->proxy_request( '/api/v5/chart/solar-return', $body );
    }

    /**
     * Handler for POST /lunar-return-chart-data.
     *
     * @param WP_REST_Request $request REST request.
     * @return WP_REST_Response|WP_Error
     */
    public function handle_lunar_return_chart_data( WP_REST_Request $request ) {
        $body = $this->build_return_body( $request, false );

        return $this->proxy_request( '/api/v5/chart-data/lunar-return', $body );
    }

    /**
     * Handler for POST /lunar-return-chart.
     *
     * @param WP_REST_Request $request REST request.
     * @return WP_REST_Response|WP_Error
     */
    public function handle_lunar_return_chart( WP_REST_Request $request ) {
        $body = $this->build_return_body( $request, true );

        return $this->proxy_request( '/api/v5/chart/lunar-return', $body );
    }

    /**
     * Handler for POST /compatibility-score.
     *
     * @param WP_REST_Request $request REST request.
     * @return WP_REST_Response|WP_Error
     */
    public function handle_compatibility_score( WP_REST_Request $request ) {
        $first  = $this->extract_first_subject( $request );
        $second = $this->extract_second_subject( $request );

        $body = array(
            'first_subject'  => $this->apply_config( $first ),
            'second_subject' => $this->apply_config( $second ),
        );

        // Computation options: reuse the same configuration as chart-data endpoints
        $body = array_merge( $body, $this->get_data_config() );

        return $this->proxy_request( '/api/v5/compatibility-score', $body );
    }

    /**
     * Permission callback for settings endpoints.
     *
     * Only administrators (manage_options) can read or update settings.
     *
     * @param WP_REST_Request $request Request.
     * @return bool
     */
    public function check_settings_permissions( WP_REST_Request $request ): bool { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
        return current_user_can( 'manage_options' );
    }

    /**
     * Returns the current plugin settings for the admin React app.
     *
     * Handler for POST /settings-get.
     *
     * @param WP_REST_Request $request REST request.
     * @return WP_REST_Response
     */
    public function handle_get_settings( WP_REST_Request $request ): WP_REST_Response { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
        $settings = Astrologer_API_Settings::get_settings();

        return new WP_REST_Response( $settings, 200 );
    }

    /**
     * Updates plugin settings from the admin React app.
     *
     * Handler for POST /settings-update.
     *
     * @param WP_REST_Request $request REST request.
     * @return WP_REST_Response|WP_Error
     */
    public function handle_update_settings( WP_REST_Request $request ) {
        $raw_settings = $request->get_param( 'settings' );

        if ( ! is_array( $raw_settings ) ) {
            return new WP_Error(
                'invalid_settings_payload',
                __( 'Invalid settings payload.', 'astrologer-api' ),
                array( 'status' => 400 )
            );
        }

        // Reuse the existing sanitization logic from the settings class.
        $settings_handler = new Astrologer_API_Settings();
        $sanitized        = $settings_handler->sanitize_settings( $raw_settings );

        update_option( 'astrologer_api_settings', $sanitized );

        return new WP_REST_Response( $sanitized, 200 );
    }

    /**
     * Default active points.
     *
     * @return array
     */
    private function get_default_active_points(): array {
        return Astrologer_API_Settings::get_default_active_points();
    }

    /**
     * Default active aspects.
     *
     * @return array
     */
    private function get_default_active_aspects(): array {
        return Astrologer_API_Settings::get_default_active_aspects();
    }

    /**
     * Extracts subject data from the request.
     *
     * @param WP_REST_Request $request Request.
     * @return array
     */
    private function extract_subject( WP_REST_Request $request ): array {
        return array(
            'name'      => sanitize_text_field( $request->get_param( 'name' ) ?? 'Subject' ),
            'year'      => intval( $request->get_param( 'year' ) ),
            'month'     => intval( $request->get_param( 'month' ) ),
            'day'       => intval( $request->get_param( 'day' ) ),
            'hour'      => intval( $request->get_param( 'hour' ) ),
            'minute'    => intval( $request->get_param( 'minute' ) ),
            'city'      => sanitize_text_field( $request->get_param( 'city' ) ?? '' ),
            'nation'    => sanitize_text_field( $request->get_param( 'nation' ) ?? '' ),
            'longitude' => floatval( $request->get_param( 'longitude' ) ),
            'latitude'  => floatval( $request->get_param( 'latitude' ) ),
            'timezone'  => sanitize_text_field( $request->get_param( 'timezone' ) ?? 'UTC' ),
        );
    }

    /**
     * Extracts the first subject from the request (for synastry).
     *
     * @param WP_REST_Request $request Request.
     * @return array
     */
    private function extract_first_subject( WP_REST_Request $request ): array {
        $first = $request->get_param( 'first_subject' );
        if ( is_array( $first ) ) {
            return $this->sanitize_subject( $first );
        }
        return array();
    }

    /**
     * Extracts the second subject from the request (for synastry).
     *
     * @param WP_REST_Request $request Request.
     * @return array
     */
    private function extract_second_subject( WP_REST_Request $request ): array {
        $second = $request->get_param( 'second_subject' );
        if ( is_array( $second ) ) {
            return $this->sanitize_subject( $second );
        }
        return array();
    }

    /**
     * Sanitizes subject data.
     *
     * @param array $subject Raw data.
     * @return array Sanitized data.
     */
    private function sanitize_subject( array $subject ): array {
        return array(
            'name'      => sanitize_text_field( $subject['name'] ?? 'Subject' ),
            'year'      => intval( $subject['year'] ?? 2000 ),
            'month'     => intval( $subject['month'] ?? 1 ),
            'day'       => intval( $subject['day'] ?? 1 ),
            'hour'      => intval( $subject['hour'] ?? 12 ),
            'minute'    => intval( $subject['minute'] ?? 0 ),
            'city'      => sanitize_text_field( $subject['city'] ?? '' ),
            'nation'    => sanitize_text_field( $subject['nation'] ?? '' ),
            'longitude' => floatval( $subject['longitude'] ?? 0 ),
            'latitude'  => floatval( $subject['latitude'] ?? 0 ),
            'timezone'  => sanitize_text_field( $subject['timezone'] ?? 'UTC' ),
        );
    }

    /**
     * Argument definition for a single subject.
     *
     * @return array
     */
    private function get_subject_args(): array {
        return array(
            'name' => array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'year' => array(
                'type'              => 'integer',
                'required'          => true,
                'sanitize_callback' => 'absint',
            ),
            'month' => array(
                'type'              => 'integer',
                'required'          => true,
                'sanitize_callback' => 'absint',
            ),
            'day' => array(
                'type'              => 'integer',
                'required'          => true,
                'sanitize_callback' => 'absint',
            ),
            'hour' => array(
                'type'              => 'integer',
                'required'          => true,
                'sanitize_callback' => 'absint',
            ),
            'minute' => array(
                'type'              => 'integer',
                'required'          => true,
                'sanitize_callback' => 'absint',
            ),
            'longitude' => array(
                'type'              => 'number',
                'required'          => true,
                'validate_callback' => function ( $value ) {
                    return is_numeric( $value ) && $value >= -180 && $value <= 180;
                },
            ),
            'latitude' => array(
                'type'              => 'number',
                'required'          => true,
                'validate_callback' => function ( $value ) {
                    return is_numeric( $value ) && $value >= -90 && $value <= 90;
                },
            ),
            'timezone' => array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'city' => array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'nation' => array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ),
        );
    }

    /**
     * Argument definition for two subjects (synastry).
     *
     * @return array
     */
    private function get_dual_subject_args(): array {
        return array(
            'first_subject' => array(
                'type'     => 'object',
                'required' => true,
            ),
            'second_subject' => array(
                'type'     => 'object',
                'required' => true,
            ),
        );
    }

    /**
     * Argument definition for transit endpoints.
     *
     * @return array
     */
    private function get_transit_args(): array {
        return array(
            'natal_subject' => array(
                'type'     => 'object',
                'required' => true,
            ),
            'transit_subject' => array(
                'type'     => 'object',
                'required' => true,
            ),
            'include_house_comparison' => array(
                'type' => 'boolean',
            ),
        );
    }

    /**
     * Argument definition for now (current moment) endpoints.
     *
     * @return array
     */
    private function get_now_args(): array {
        return array(
            'name' => array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ),
        );
    }

    /**
     * Argument definition for solar/lunar return endpoints.
     *
     * @return array
     */
    private function get_return_args(): array {
        return array(
            'subject' => array(
                'type'     => 'object',
                'required' => true,
            ),
            'year' => array(
                'type'              => 'integer',
                'sanitize_callback' => 'absint',
            ),
            'month' => array(
                'type'              => 'integer',
                'sanitize_callback' => 'absint',
            ),
            'iso_datetime' => array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'wheel_type' => array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'include_house_comparison' => array(
                'type' => 'boolean',
            ),
            'return_location' => array(
                'type' => 'object',
            ),
        );
    }

    /**
     * Checks whether the current visitor has exceeded the rate limit.
     *
     * Uses a WordPress transient keyed by the visitor's IP address.
     * Administrators are exempt from rate limiting.
     *
     * @return WP_Error|null WP_Error if rate-limited, null otherwise.
     */
    private function check_rate_limit() {
        // Skip rate limiting for logged-in administrators.
        if ( current_user_can( 'manage_options' ) ) {
            return null;
        }

        $ip  = $this->get_client_ip();
        $key = 'astrologer_rl_' . md5( $ip );

        $hits = (int) get_transient( $key );

        if ( $hits >= self::RATE_LIMIT_MAX ) {
            return new WP_Error(
                'rate_limit_exceeded',
                __( 'Too many requests. Please try again later.', 'astrologer-api' ),
                array( 'status' => 429 )
            );
        }

        // Increment counter — set expiry on first hit.
        if ( 0 === $hits ) {
            set_transient( $key, 1, self::RATE_LIMIT_WINDOW );
        } else {
            set_transient( $key, $hits + 1, self::RATE_LIMIT_WINDOW );
        }

        return null;
    }

    /**
     * Returns the client IP address, respecting common proxy headers.
     *
     * @return string
     */
    private function get_client_ip(): string {
        $headers = array(
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR',
        );

        foreach ( $headers as $header ) {
            if ( ! empty( $_SERVER[ $header ] ) ) {
                // X-Forwarded-For may contain a comma-separated list; take the first.
                $ip = strtok( sanitize_text_field( wp_unslash( $_SERVER[ $header ] ) ), ',' );
                if ( false !== filter_var( trim( $ip ), FILTER_VALIDATE_IP ) ) {
                    return trim( $ip );
                }
            }
        }

        return '0.0.0.0';
    }
}
