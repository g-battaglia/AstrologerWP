<?php
/**
 * Admin settings page handler for Astrologer API.
 *
 * @package Astrologer_API_Playground
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class responsible for managing the plugin settings.
 *
 * It creates a settings page in the WordPress admin where the user can:
 * - Enter the RapidAPI key
 * - Enter the GeoNames username
 * - Configure the base API URL
 * - Set language, house system, theme, etc.
 *
 * @since 1.0.0
 */
class Astrologer_API_Settings {

    /**
     * Option name in the WordPress database.
     *
     * @var string
     */
    private const OPTION_NAME = 'astrologer_api_settings';

    /**
     * Settings page slug.
     *
     * @var string
     */
    private const PAGE_SLUG = 'astrologer-api';

    /**
     * Loads astrology enums/defaults from the shared JSON file.
     *
     * @return array
     */
    private static function get_astrology_enums(): array {
        static $cache = null;
        if ( null !== $cache ) {
            return $cache;
        }

        $path = dirname( __DIR__ ) . '/astrology-enums.json';
        if ( ! file_exists( $path ) ) {
            $cache = array();
            return $cache;
        }

        $raw = file_get_contents( $path );
        if ( false === $raw ) {
            $cache = array();
            return $cache;
        }

        $decoded = json_decode( $raw, true );
        $cache   = is_array( $decoded ) ? $decoded : array();
        return $cache;
    }

    /**
     * @return string[]
     */
    public static function get_allowed_active_points(): array {
        $enums  = self::get_astrology_enums();
        $points = $enums['active_points'] ?? array();
        if ( ! is_array( $points ) ) {
            return array();
        }
        return array_values( array_filter( array_map( 'strval', $points ) ) );
    }

    /**
     * @return string[]
     */
    public static function get_allowed_active_aspects(): array {
        $enums   = self::get_astrology_enums();
        $aspects = $enums['active_aspects'] ?? array();
        if ( ! is_array( $aspects ) ) {
            return array();
        }
        $names = array();
        foreach ( $aspects as $a ) {
            if ( ! is_array( $a ) ) {
                continue;
            }
            $name = isset( $a['name'] ) ? (string) $a['name'] : '';
            if ( '' !== $name ) {
                $names[] = $name;
            }
        }
        return array_values( array_unique( $names ) );
    }

    /**
     * @return string[]
     */
    public static function get_default_active_points(): array {
        $enums    = self::get_astrology_enums();
        $defaults = $enums['default_active_points'] ?? array();
        if ( is_array( $defaults ) && ! empty( $defaults ) ) {
            return array_values( array_filter( array_map( 'strval', $defaults ) ) );
        }
        return self::get_allowed_active_points();
    }

    /**
     * @return array[] Array of {name, orb}
     */
    public static function get_default_active_aspects(): array {
        $enums    = self::get_astrology_enums();
        $defaults = $enums['default_active_aspects'] ?? null;

        if ( is_array( $defaults ) ) {
            $out = array();
            foreach ( $defaults as $a ) {
                if ( ! is_array( $a ) ) {
                    continue;
                }
                $name = isset( $a['name'] ) ? sanitize_text_field( (string) $a['name'] ) : '';
                if ( '' === $name ) {
                    continue;
                }
                $orb_raw = isset( $a['orb'] ) ? (float) $a['orb'] : 0.0;
                $orb     = $orb_raw > 0 ? $orb_raw : 1.0;
                $out[]   = array( 'name' => $name, 'orb' => $orb );
            }
            if ( ! empty( $out ) ) {
                return array_values( $out );
            }
        }

        // Fallback: derive from active_aspects defaultOrb
        $aspects = $enums['active_aspects'] ?? array();
        if ( ! is_array( $aspects ) ) {
            return array();
        }
        $out = array();
        foreach ( $aspects as $a ) {
            if ( ! is_array( $a ) ) {
                continue;
            }
            $name = isset( $a['name'] ) ? sanitize_text_field( (string) $a['name'] ) : '';
            if ( '' === $name ) {
                continue;
            }
            $orb_raw = isset( $a['defaultOrb'] ) ? (float) $a['defaultOrb'] : 0.0;
            $orb     = $orb_raw > 0 ? $orb_raw : 1.0;
            $out[]   = array( 'name' => $name, 'orb' => $orb );
        }
        return array_values( $out );
    }

    /**
     * Constructor - registers admin hooks.
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
    }

    /**
     * Adds the settings page to the WordPress menu.
     *
     * @return void
     */
    public function add_settings_page(): void {
        add_options_page(
            __( 'Astrologer API', 'astrologer-api' ),      // Page title
            __( 'Astrologer API', 'astrologer-api' ),      // Menu title
            'manage_options',                               // Required capability
            self::PAGE_SLUG,                                // Page slug
            array( $this, 'render_settings_page' )          // Render callback
        );
    }

    /**
     * Registers plugin settings with the WordPress Settings API.
     *
     * @return void
     */
    public function register_settings(): void {
        // Register main option
        register_setting(
            'astrologer_api_options',    // Option group
            self::OPTION_NAME,           // Option name
            array(
                'type'              => 'array',
                'sanitize_callback' => array( $this, 'sanitize_settings' ),
                'default'           => $this->get_defaults(),
            )
        );

        // =====================================================================
        // SECTION: API Credentials
        // =====================================================================
        add_settings_section(
            'astrologer_api_credentials',
            __( 'API Credentials', 'astrologer-api' ),
            array( $this, 'render_credentials_section' ),
            self::PAGE_SLUG
        );

        add_settings_field(
            'rapidapi_key',
            __( 'RapidAPI Key', 'astrologer-api' ),
            array( $this, 'render_password_field' ),
            self::PAGE_SLUG,
            'astrologer_api_credentials',
            array(
                'label_for'   => 'rapidapi_key',
                'description' => __( 'Your API key from RapidAPI (X-RapidAPI-Key).', 'astrologer-api' ),
            )
        );

        add_settings_field(
            'geonames_username',
            __( 'GeoNames Username', 'astrologer-api' ),
            array( $this, 'render_text_field' ),
            self::PAGE_SLUG,
            'astrologer_api_credentials',
            array(
                'label_for'   => 'geonames_username',
                'description' => __( 'GeoNames username (optional, for automatic location lookup).', 'astrologer-api' ),
            )
        );

        add_settings_field(
            'base_url',
            __( 'Base URL API', 'astrologer-api' ),
            array( $this, 'render_text_field' ),
            self::PAGE_SLUG,
            'astrologer_api_credentials',
            array(
                'label_for'   => 'base_url',
                'description' => __( 'Base URL for the Astrologer API.', 'astrologer-api' ),
            )
        );

        // =====================================================================
        // SECTION: Charts Configuration
        // =====================================================================
        add_settings_section(
            'astrologer_api_chart_config',
            __( 'Charts Configuration', 'astrologer-api' ),
            array( $this, 'render_chart_section' ),
            self::PAGE_SLUG
        );

        add_settings_field(
            'language',
            __( 'Language', 'astrologer-api' ),
            array( $this, 'render_language_select' ),
            self::PAGE_SLUG,
            'astrologer_api_chart_config',
            array( 'label_for' => 'language' )
        );

        add_settings_field(
            'house_system',
            __( 'House System', 'astrologer-api' ),
            array( $this, 'render_house_system_select' ),
            self::PAGE_SLUG,
            'astrologer_api_chart_config',
            array( 'label_for' => 'house_system' )
        );

        add_settings_field(
            'theme',
            __( 'Chart Theme', 'astrologer-api' ),
            array( $this, 'render_theme_select' ),
            self::PAGE_SLUG,
            'astrologer_api_chart_config',
            array( 'label_for' => 'theme' )
        );

        add_settings_field(
            'ui_theme_mode',
            __( 'UI Theme (components)', 'astrologer-api' ),
            array( $this, 'render_ui_theme_mode_select' ),
            self::PAGE_SLUG,
            'astrologer_api_chart_config',
            array( 'label_for' => 'ui_theme_mode' )
        );

        add_settings_field(
            'sidereal',
            __( 'Sidereal Zodiac', 'astrologer-api' ),
            array( $this, 'render_checkbox_field' ),
            self::PAGE_SLUG,
            'astrologer_api_chart_config',
            array(
                'label_for'   => 'sidereal',
                'description' => __( 'Use the sidereal zodiac instead of the tropical one.', 'astrologer-api' ),
            )
        );

        add_settings_field(
            'sidereal_mode',
            __( 'Ayanamsa (Sidereal Mode)', 'astrologer-api' ),
            array( $this, 'render_sidereal_mode_select' ),
            self::PAGE_SLUG,
            'astrologer_api_chart_config',
            array( 'label_for' => 'sidereal_mode' )
        );

        add_settings_field(
            'perspective_type',
            __( 'Perspective', 'astrologer-api' ),
            array( $this, 'render_perspective_select' ),
            self::PAGE_SLUG,
            'astrologer_api_chart_config',
            array( 'label_for' => 'perspective_type' )
        );

        add_settings_field(
            'split_chart',
            __( 'Split chart / grid', 'astrologer-api' ),
            array( $this, 'render_checkbox_field' ),
            self::PAGE_SLUG,
            'astrologer_api_chart_config',
            array(
                'label_for'   => 'split_chart',
                'description' => __( 'Returns wheel and grid separately when supported.', 'astrologer-api' ),
            )
        );

        add_settings_field(
            'transparent_background',
            __( 'Transparent background', 'astrologer-api' ),
            array( $this, 'render_checkbox_field' ),
            self::PAGE_SLUG,
            'astrologer_api_chart_config',
            array(
                'label_for'   => 'transparent_background',
                'description' => __( 'Use a transparent background in SVG charts.', 'astrologer-api' ),
            )
        );

        add_settings_field(
            'show_house_position_comparison',
            __( 'House comparison table', 'astrologer-api' ),
            array( $this, 'render_checkbox_field' ),
            self::PAGE_SLUG,
            'astrologer_api_chart_config',
            array(
                'label_for'   => 'show_house_position_comparison',
                'description' => __( 'Show the house comparison table when available.', 'astrologer-api' ),
            )
        );

        add_settings_field(
            'show_cusp_position_comparison',
            __( 'Cusp comparison table', 'astrologer-api' ),
            array( $this, 'render_checkbox_field' ),
            self::PAGE_SLUG,
            'astrologer_api_chart_config',
            array(
                'label_for'   => 'show_cusp_position_comparison',
                'description' => __( 'Show the cusp comparison table for dual charts.', 'astrologer-api' ),
            )
        );

        add_settings_field(
            'show_degree_indicators',
            __( 'Degree indicators', 'astrologer-api' ),
            array( $this, 'render_checkbox_field' ),
            self::PAGE_SLUG,
            'astrologer_api_chart_config',
            array(
                'label_for'   => 'show_degree_indicators',
                'description' => __( 'Show radial lines and degree markings on the wheel.', 'astrologer-api' ),
            )
        );

        add_settings_field(
            'collapse_birth_form_on_submit',
            __( 'Collapse birth form after calculation', 'astrologer-api' ),
            array( $this, 'render_checkbox_field' ),
            self::PAGE_SLUG,
            'astrologer_api_chart_config',
            array(
                'label_for'   => 'collapse_birth_form_on_submit',
                'description' => __( 'Hide the birth data form after the natal chart has been calculated (results remain visible).', 'astrologer-api' ),
            )
        );

        add_settings_field(
            'custom_title',
            __( 'Custom chart title', 'astrologer-api' ),
            array( $this, 'render_text_field' ),
            self::PAGE_SLUG,
            'astrologer_api_chart_config',
            array(
                'label_for'   => 'custom_title',
                'description' => __( 'Optional default title override (max 40 characters).', 'astrologer-api' ),
            )
        );

        // =====================================================================
        // SECTION: Computation Configuration (points, aspects, distributions)
        // =====================================================================
        add_settings_section(
            'astrologer_api_computation_config',
            __( 'Computation Configuration', 'astrologer-api' ),
            array( $this, 'render_computation_section' ),
            self::PAGE_SLUG
        );

        add_settings_field(
            'distribution_method',
            __( 'Distribution method', 'astrologer-api' ),
            array( $this, 'render_distribution_method_select' ),
            self::PAGE_SLUG,
            'astrologer_api_computation_config',
            array( 'label_for' => 'distribution_method' )
        );

        add_settings_field(
            'synastry_include_house_comparison',
            __( 'Synastry: include house comparison', 'astrologer-api' ),
            array( $this, 'render_checkbox_field' ),
            self::PAGE_SLUG,
            'astrologer_api_computation_config',
            array(
                'label_for'   => 'synastry_include_house_comparison',
                'description' => __( 'Include house comparison data in synastry calculations.', 'astrologer-api' ),
            )
        );

        add_settings_field(
            'synastry_include_relationship_score',
            __( 'Synastry: include relationship score', 'astrologer-api' ),
            array( $this, 'render_checkbox_field' ),
            self::PAGE_SLUG,
            'astrologer_api_computation_config',
            array(
                'label_for'   => 'synastry_include_relationship_score',
                'description' => __( 'Include compatibility score details in synastry calculations.', 'astrologer-api' ),
            )
        );

        add_settings_field(
            'transit_include_house_comparison',
            __( 'Transits: include house comparison', 'astrologer-api' ),
            array( $this, 'render_checkbox_field' ),
            self::PAGE_SLUG,
            'astrologer_api_computation_config',
            array(
                'label_for'   => 'transit_include_house_comparison',
                'description' => __( 'Include house comparison data in transit calculations.', 'astrologer-api' ),
            )
        );

        add_settings_field(
            'returns_include_house_comparison',
            __( 'Returns: include house comparison', 'astrologer-api' ),
            array( $this, 'render_checkbox_field' ),
            self::PAGE_SLUG,
            'astrologer_api_computation_config',
            array(
                'label_for'   => 'returns_include_house_comparison',
                'description' => __( 'Include house comparison data in solar/lunar return calculations.', 'astrologer-api' ),
            )
        );

        add_settings_field(
            'returns_wheel_type',
            __( 'Returns: wheel type', 'astrologer-api' ),
            array( $this, 'render_returns_wheel_type_select' ),
            self::PAGE_SLUG,
            'astrologer_api_computation_config',
            array( 'label_for' => 'returns_wheel_type' )
        );

        add_settings_field(
            'active_points',
            __( 'Active points', 'astrologer-api' ),
            array( $this, 'render_textarea_field' ),
            self::PAGE_SLUG,
            'astrologer_api_computation_config',
            array(
                'label_for'   => 'active_points',
                'description' => __( 'Comma- or line-separated list of points (e.g. Sun, Moon, Mercury). Leave empty for defaults.', 'astrologer-api' ),
            )
        );

        add_settings_field(
            'active_aspects',
            __( 'Active aspects', 'astrologer-api' ),
            array( $this, 'render_textarea_field' ),
            self::PAGE_SLUG,
            'astrologer_api_computation_config',
            array(
                'label_for'   => 'active_aspects',
                'description' => __( 'One aspect per line in the format name:orb (e.g. conjunction:10). Leave empty for defaults.', 'astrologer-api' ),
            )
        );

        add_settings_field(
            'custom_distribution_weights',
            __( 'Custom distribution weights', 'astrologer-api' ),
            array( $this, 'render_textarea_field' ),
            self::PAGE_SLUG,
            'astrologer_api_computation_config',
            array(
                'label_for'   => 'custom_distribution_weights',
                'description' => __( 'Optional JSON to customize weights (e.g. {"sun":2,"moon":2,"__default__":0.75}).', 'astrologer-api' ),
            )
        );
    }

    /**
     * Returns the default settings values.
     *
     * @return array
     */
    public function get_defaults(): array {
        return array(
            'rapidapi_key'      => '',
            'geonames_username' => '',
            'base_url'          => 'https://astrologer.p.rapidapi.com',
            'language'          => 'EN',
            'house_system'      => 'P',
            'theme'             => 'classic',
            'ui_theme_mode'     => 'light',
            'sidereal'          => false,
            'sidereal_mode'     => 'LAHIRI',
            // Additional configuration to match Astrologer API
            'perspective_type'              => 'Apparent Geocentric',
            'split_chart'                   => false,
            'transparent_background'        => false,
            'show_house_position_comparison' => true,
            'show_cusp_position_comparison' => true,
            'show_degree_indicators'        => true,
            'collapse_birth_form_on_submit' => false,
            'custom_title'                  => '',
            'distribution_method'           => 'weighted',
            'active_points'                 => array(),
            'active_aspects'                => array(),
            'custom_distribution_weights'   => array(),
            'synastry_include_house_comparison'    => true,
            'synastry_include_relationship_score'  => true,
            'transit_include_house_comparison'     => true,
            'returns_include_house_comparison'     => true,
            'returns_wheel_type'                   => 'dual',
            'form_output_mode'                     => 'inline',
        );
    }

    /**
     * Returns the current settings (merged with defaults).
     *
     * @return array
     */
    public static function get_settings(): array {
        $defaults = ( new self() )->get_defaults();
        $settings = get_option( self::OPTION_NAME, array() );
        return wp_parse_args( $settings, $defaults );
    }

    /**
     * Sanitizes settings values before saving.
     *
     * @param array $input Values from the settings form.
     * @return array Sanitized values.
     */
    public function sanitize_settings( array $input ): array {
        $sanitized = array();

        $allowed_active_points  = self::get_allowed_active_points();
        $allowed_active_aspects = self::get_allowed_active_aspects();

        $filter_allowed = static function ( array $values, array $allowed ): array {
            $allowed_map = array_fill_keys( $allowed, true );
            $out         = array();
            foreach ( $values as $val ) {
                $val = sanitize_text_field( (string) $val );
                if ( '' === $val ) {
                    continue;
                }
                if ( isset( $allowed_map[ $val ] ) ) {
                    $out[] = $val;
                }
            }
            return array_values( array_unique( $out ) );
        };

        // API key: sanitized as plain text
        $sanitized['rapidapi_key'] = isset( $input['rapidapi_key'] )
            ? sanitize_text_field( $input['rapidapi_key'] )
            : '';

        // GeoNames username
        $sanitized['geonames_username'] = isset( $input['geonames_username'] )
            ? sanitize_text_field( $input['geonames_username'] )
            : '';

        // Base URL: must be a valid URL
        $sanitized['base_url'] = isset( $input['base_url'] )
            ? esc_url_raw( $input['base_url'] )
            : 'https://astrologer.p.rapidapi.com';

        // Language: only allowed values
        $valid_languages           = array( 'EN', 'IT', 'FR', 'ES', 'PT', 'DE', 'RU', 'TR', 'CN', 'HI' );
        $sanitized['language'] = isset( $input['language'] ) && in_array( $input['language'], $valid_languages, true )
            ? $input['language']
            : 'EN';

        // House system: only allowed values
        $valid_houses               = array( 'P', 'K', 'O', 'R', 'C', 'E', 'W', 'M' );
        $sanitized['house_system'] = isset( $input['house_system'] ) && in_array( $input['house_system'], $valid_houses, true )
            ? $input['house_system']
            : 'P';

        // Theme: only allowed values
        $valid_themes         = array( 'classic', 'light', 'dark', 'dark-high-contrast', 'strawberry', 'black-and-white' );
        $sanitized['theme'] = isset( $input['theme'] ) && in_array( $input['theme'], $valid_themes, true )
            ? $input['theme']
            : 'classic';

        // UI theme mode: light/dark for frontend components
        $valid_ui_theme_modes              = array( 'light', 'dark' );
        $sanitized['ui_theme_mode'] = isset( $input['ui_theme_mode'] ) && in_array( $input['ui_theme_mode'], $valid_ui_theme_modes, true )
            ? $input['ui_theme_mode']
            : 'light';

        // Sidereal: boolean checkbox
        $sanitized['sidereal'] = ! empty( $input['sidereal'] );

        // Sidereal mode
        $valid_modes                = array( 'LAHIRI', 'FAGAN_BRADLEY', 'RAMAN', 'KRISHNAMURTI' );
        $sanitized['sidereal_mode'] = isset( $input['sidereal_mode'] ) && in_array( $input['sidereal_mode'], $valid_modes, true )
            ? $input['sidereal_mode']
            : 'LAHIRI';

        // Perspective type
        $valid_perspectives              = array( 'Apparent Geocentric', 'Heliocentric' );
        $sanitized['perspective_type'] = isset( $input['perspective_type'] ) && in_array( $input['perspective_type'], $valid_perspectives, true )
            ? $input['perspective_type']
            : 'Apparent Geocentric';

        // Chart rendering options
        $sanitized['split_chart']                    = ! empty( $input['split_chart'] );
        $sanitized['transparent_background']         = ! empty( $input['transparent_background'] );
        $sanitized['show_house_position_comparison'] = ! empty( $input['show_house_position_comparison'] );
        $sanitized['show_cusp_position_comparison']  = ! empty( $input['show_cusp_position_comparison'] );
        $sanitized['show_degree_indicators']         = ! empty( $input['show_degree_indicators'] );
        $sanitized['collapse_birth_form_on_submit']  = ! empty( $input['collapse_birth_form_on_submit'] );

        // Custom title (max 40 chars)
        $custom_title_raw = isset( $input['custom_title'] ) ? sanitize_text_field( (string) $input['custom_title'] ) : '';
        if ( strlen( $custom_title_raw ) > 40 ) {
            $custom_title_raw = substr( $custom_title_raw, 0, 40 );
        }
        $sanitized['custom_title'] = $custom_title_raw;

        // Distribution method
        $valid_distribution_methods           = array( 'weighted', 'pure_count' );
        $sanitized['distribution_method'] = isset( $input['distribution_method'] ) && in_array( $input['distribution_method'], $valid_distribution_methods, true )
            ? $input['distribution_method']
            : 'weighted';

        // Synastry / transit / returns flags
        $sanitized['synastry_include_house_comparison']   = ! empty( $input['synastry_include_house_comparison'] );
        $sanitized['synastry_include_relationship_score'] = ! empty( $input['synastry_include_relationship_score'] );
        $sanitized['transit_include_house_comparison']    = ! empty( $input['transit_include_house_comparison'] );
        $sanitized['returns_include_house_comparison']    = ! empty( $input['returns_include_house_comparison'] );

        // Returns wheel type
        $valid_wheel_types             = array( 'dual', 'single' );
        $sanitized['returns_wheel_type'] = isset( $input['returns_wheel_type'] ) && in_array( $input['returns_wheel_type'], $valid_wheel_types, true )
            ? $input['returns_wheel_type']
            : 'dual';

        // Form output mode
        $valid_output_modes             = array( 'inline', 'separated' );
        $sanitized['form_output_mode'] = isset( $input['form_output_mode'] ) && in_array( $input['form_output_mode'], $valid_output_modes, true )
            ? $input['form_output_mode']
            : 'inline';

        // Active points (structured payload only)
        $points = ( isset( $input['active_points'] ) && is_array( $input['active_points'] ) )
            ? $input['active_points']
            : array();
        $sanitized['active_points'] = $filter_allowed( $points, $allowed_active_points );

        // Active aspects (structured payload only)
        $aspects_out = array();
        if ( isset( $input['active_aspects'] ) && is_array( $input['active_aspects'] ) ) {
            foreach ( $input['active_aspects'] as $aspect ) {
                if ( ! is_array( $aspect ) ) {
                    continue;
                }
                $name = isset( $aspect['name'] ) ? sanitize_text_field( (string) $aspect['name'] ) : '';
                if ( '' === $name || ! in_array( $name, $allowed_active_aspects, true ) ) {
                    continue;
                }
                $orb_raw = isset( $aspect['orb'] ) ? (float) $aspect['orb'] : 0.0;
                $orb     = $orb_raw > 0 ? $orb_raw : 1.0;
                $aspects_out[] = array(
                    'name' => $name,
                    'orb'  => $orb,
                );
            }
        }
        $sanitized['active_aspects'] = array_values( $aspects_out );

        // Custom distribution weights (structured payload only)
        $weights_out = array();
        if ( isset( $input['custom_distribution_weights'] ) && is_array( $input['custom_distribution_weights'] ) ) {
            foreach ( $input['custom_distribution_weights'] as $key => $val ) {
                $k = sanitize_text_field( (string) $key );
                $n = (float) $val;
                if ( '' === $k ) {
                    continue;
                }
                if ( ! is_finite( $n ) ) {
                    continue;
                }
                $weights_out[ $k ] = $n;
            }
        }
        $sanitized['custom_distribution_weights'] = $weights_out;

        return $sanitized;
    }

    // =========================================================================
    // RENDER METHODS
    // =========================================================================

    /**
     * Renders the main settings page.
     *
     * @return void
     */
    public function render_settings_page(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        $settings      = self::get_settings();
        $ui_theme_mode = isset( $settings['ui_theme_mode'] ) ? (string) $settings['ui_theme_mode'] : 'light';

        $classes = 'astrologer-component';
        if ( 'dark' === $ui_theme_mode ) {
            $classes .= ' astrologer-theme-dark dark';
        } else {
            $classes .= ' astrologer-theme-light';
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

            <div
                id="astrologer-admin-settings-root"
                class="<?php echo esc_attr( $classes ); ?>"
                data-astrologer-component="admin-settings"
                data-props="{}"
            >
                <div class="astrologer-loading"><?php esc_html_e( 'Loading...', 'astrologer-api' ); ?></div>
            </div>
        </div>
        <?php
    }

    /**
     * Enqueues React/Tailwind assets in the admin settings page.
     *
     * @param string $hook_suffix Current admin page hook suffix.
     * @return void
     */
    public function enqueue_admin_assets( string $hook_suffix ): void {
        if ( 'settings_page_' . self::PAGE_SLUG !== $hook_suffix ) {
            return;
        }

        if ( class_exists( 'Astrologer_API_Frontend' ) ) {
            Astrologer_API_Frontend::enqueue_frontend_assets();
        }
    }

    /**
     * Renders the description for the credentials section.
     *
     * @return void
     */
    public function render_credentials_section(): void {
        echo '<p>' . esc_html__( 'Enter the credentials to access the Astrologer API.', 'astrologer-api' ) . '</p>';
    }

    /**
     * Renders the description for the charts section.
     *
     * @return void
     */
    public function render_chart_section(): void {
        echo '<p>' . esc_html__( 'Configure the default options for astrological charts.', 'astrologer-api' ) . '</p>';
    }

    /**
     * Renders the description for the computation section.
     *
     * @return void
     */
    public function render_computation_section(): void {
        echo '<p>' . esc_html__( 'Configure computation parameters: active points, aspects and distributions.', 'astrologer-api' ) . '</p>';
    }

    /**
     * Renders a text field.
     *
     * @param array $args Field arguments.
     * @return void
     */
    public function render_text_field( array $args ): void {
        $settings = self::get_settings();
        $id       = $args['label_for'];
        $value    = $settings[ $id ] ?? '';
        ?>
        <input
            type="text"
            id="<?php echo esc_attr( $id ); ?>"
            name="<?php echo esc_attr( self::OPTION_NAME . '[' . $id . ']' ); ?>"
            value="<?php echo esc_attr( $value ); ?>"
            class="regular-text"
        />
        <?php if ( ! empty( $args['description'] ) ) : ?>
            <p class="description"><?php echo esc_html( $args['description'] ); ?></p>
        <?php endif; ?>
        <?php
    }

    /**
     * Renders a generic textarea field.
     *
     * Handles complex fields stored as arrays:
     * - active_points: array of strings -> one per line
     * - active_aspects: array of arrays (name, orb) -> "name:orb" per line
     * - custom_distribution_weights: associative array -> formatted JSON
     *
     * @param array $args Field arguments.
     * @return void
     */
    public function render_textarea_field( array $args ): void {
        $settings = self::get_settings();
        $id       = $args['label_for'];
        $value    = '';

        if ( 'active_points' === $id ) {
            $points = $settings['active_points'] ?? array();
            if ( is_array( $points ) ) {
                $value = implode( "\n", array_map( 'strval', $points ) );
            }
        } elseif ( 'active_aspects' === $id ) {
            $aspects = $settings['active_aspects'] ?? array();
            $lines   = array();

            if ( is_array( $aspects ) ) {
                foreach ( $aspects as $aspect ) {
                    if ( ! is_array( $aspect ) ) {
                        continue;
                    }

                    $name = isset( $aspect['name'] ) ? (string) $aspect['name'] : '';
                    if ( '' === $name ) {
                        continue;
                    }

                    $orb = isset( $aspect['orb'] ) ? (float) $aspect['orb'] : 0.0;
                    $lines[] = $name . ':' . $orb;
                }
            }

            $value = implode( "\n", $lines );
        } elseif ( 'custom_distribution_weights' === $id ) {
            $weights = $settings['custom_distribution_weights'] ?? array();
            if ( ! empty( $weights ) && is_array( $weights ) ) {
                $encoded = wp_json_encode( $weights, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
                if ( is_string( $encoded ) ) {
                    $value = $encoded;
                }
            }
        } else {
            // Generic fallback for other textarea fields.
            $raw = $settings[ $id ] ?? '';
            if ( is_string( $raw ) ) {
                $value = $raw;
            } elseif ( is_array( $raw ) ) {
                $value = implode( "\n", array_map( 'strval', $raw ) );
            } else {
                $value = (string) $raw;
            }
        }
        ?>
        <textarea
            id="<?php echo esc_attr( $id ); ?>"
            name="<?php echo esc_attr( self::OPTION_NAME . '[' . $id . ']' ); ?>"
            rows="5"
            class="large-text code"
        ><?php echo esc_textarea( $value ); ?></textarea>
        <?php if ( ! empty( $args['description'] ) ) : ?>
            <p class="description"><?php echo esc_html( $args['description'] ); ?></p>
        <?php endif; ?>
        <?php
    }

    /**
     * Renders a password field.
     *
     * @param array $args Field arguments.
     * @return void
     */
    public function render_password_field( array $args ): void {
        $settings = self::get_settings();
        $id       = $args['label_for'];
        $value    = $settings[ $id ] ?? '';
        ?>
        <input
            type="password"
            id="<?php echo esc_attr( $id ); ?>"
            name="<?php echo esc_attr( self::OPTION_NAME . '[' . $id . ']' ); ?>"
            value="<?php echo esc_attr( $value ); ?>"
            class="regular-text"
            autocomplete="off"
        />
        <?php if ( ! empty( $args['description'] ) ) : ?>
            <p class="description"><?php echo esc_html( $args['description'] ); ?></p>
        <?php endif; ?>
        <?php
    }

    /**
     * Renders a checkbox field.
     *
     * @param array $args Field arguments.
     * @return void
     */
    public function render_checkbox_field( array $args ): void {
        $settings = self::get_settings();
        $id       = $args['label_for'];
        $checked  = ! empty( $settings[ $id ] );
        ?>
        <input
            type="checkbox"
            id="<?php echo esc_attr( $id ); ?>"
            name="<?php echo esc_attr( self::OPTION_NAME . '[' . $id . ']' ); ?>"
            value="1"
            <?php checked( $checked ); ?>
        />
        <?php if ( ! empty( $args['description'] ) ) : ?>
            <span class="description"><?php echo esc_html( $args['description'] ); ?></span>
        <?php endif; ?>
        <?php
    }

    /**
     * Renders the select field for the language.
     *
     * @param array $args Field arguments.
     * @return void
     */
    public function render_language_select( array $args ): void {
        $settings = self::get_settings();
        $current  = $settings['language'] ?? 'EN';
        $options  = array(
            'EN' => __( 'English', 'astrologer-api' ),
            'IT' => __( 'Italian', 'astrologer-api' ),
            'FR' => __( 'French', 'astrologer-api' ),
            'ES' => __( 'Spanish', 'astrologer-api' ),
            'PT' => __( 'Portuguese', 'astrologer-api' ),
            'DE' => __( 'German', 'astrologer-api' ),
            'RU' => __( 'Russian', 'astrologer-api' ),
            'TR' => __( 'Turkish', 'astrologer-api' ),
            'CN' => __( 'Chinese', 'astrologer-api' ),
            'HI' => __( 'Hindi', 'astrologer-api' ),
        );
        $this->render_select( 'language', $options, $current );
    }

    /**
     * Renders the select field for the house system.
     *
     * @param array $args Field arguments.
     * @return void
     */
    public function render_house_system_select( array $args ): void {
        $settings = self::get_settings();
        $current  = $settings['house_system'] ?? 'P';
        $options  = array(
            'P' => __( 'Placidus', 'astrologer-api' ),
            'K' => __( 'Koch', 'astrologer-api' ),
            'O' => __( 'Porphyrius', 'astrologer-api' ),
            'R' => __( 'Regiomontanus', 'astrologer-api' ),
            'C' => __( 'Campanus', 'astrologer-api' ),
            'E' => __( 'Equal', 'astrologer-api' ),
            'W' => __( 'Whole Sign', 'astrologer-api' ),
            'M' => __( 'Morinus', 'astrologer-api' ),
        );
        $this->render_select( 'house_system', $options, $current );
    }

    /**
     * Renders the select field for the chart theme.
     *
     * @param array $args Field arguments.
     * @return void
     */
    public function render_theme_select( array $args ): void {
        $settings = self::get_settings();
        $current  = $settings['theme'] ?? 'classic';
        $options  = array(
            'classic'             => __( 'Classic', 'astrologer-api' ),
            'light'               => __( 'Light', 'astrologer-api' ),
            'dark'                => __( 'Dark', 'astrologer-api' ),
            'dark-high-contrast'  => __( 'Dark High Contrast', 'astrologer-api' ),
            'strawberry'          => __( 'Strawberry', 'astrologer-api' ),
            'black-and-white'     => __( 'Black and White', 'astrologer-api' ),
        );
        $this->render_select( 'theme', $options, $current );
    }

    public function render_ui_theme_mode_select( array $args ): void {
        $settings = self::get_settings();
        $current  = $settings['ui_theme_mode'] ?? 'light';
        $options  = array(
            'light' => __( 'Light', 'astrologer-api' ),
            'dark'  => __( 'Dark', 'astrologer-api' ),
        );
        $this->render_select( 'ui_theme_mode', $options, $current );
    }

    public function render_distribution_method_select( array $args ): void {
        $settings = self::get_settings();
        $current  = $settings['distribution_method'] ?? 'weighted';
        $options  = array(
            'weighted'   => __( 'Weighted', 'astrologer-api' ),
            'pure_count' => __( 'Pure count', 'astrologer-api' ),
        );
        $this->render_select( 'distribution_method', $options, $current );
    }

    public function render_returns_wheel_type_select( array $args ): void {
        $settings = self::get_settings();
        $current  = $settings['returns_wheel_type'] ?? 'dual';
        $options  = array(
            'dual'   => __( 'Dual (natal + return)', 'astrologer-api' ),
            'single' => __( 'Single (return only)', 'astrologer-api' ),
        );
        $this->render_select( 'returns_wheel_type', $options, $current );
    }

    /**
     * Renders the select field for the perspective (geocentric/heliocentric).
     *
     * @param array $args Field arguments.
     * @return void
     */
    public function render_perspective_select( array $args ): void {
        $settings = self::get_settings();
        $current  = $settings['perspective_type'] ?? 'Apparent Geocentric';
        $options  = array(
            'Apparent Geocentric' => __( 'Apparent Geocentric', 'astrologer-api' ),
            'Heliocentric'        => __( 'Heliocentric', 'astrologer-api' ),
        );
        $this->render_select( 'perspective_type', $options, $current );
    }

    /**
     * Renders the select field for the sidereal mode.
     *
     * @param array $args Field arguments.
     * @return void
     */
    public function render_sidereal_mode_select( array $args ): void {
        $settings = self::get_settings();
        $current  = $settings['sidereal_mode'] ?? 'LAHIRI';
        $options  = array(
            'LAHIRI'        => __( 'Lahiri', 'astrologer-api' ),
            'FAGAN_BRADLEY' => __( 'Fagan/Bradley', 'astrologer-api' ),
            'RAMAN'         => __( 'Raman', 'astrologer-api' ),
            'KRISHNAMURTI'  => __( 'Krishnamurti (KP)', 'astrologer-api' ),
        );
        $this->render_select( 'sidereal_mode', $options, $current );
    }

    /**
     * Helper to render a generic select field.
     *
     * @param string $id      Field ID.
     * @param array  $options Options (value => label).
     * @param string $current Current value.
     * @return void
     */
    private function render_select( string $id, array $options, string $current ): void {
        ?>
        <select
            id="<?php echo esc_attr( $id ); ?>"
            name="<?php echo esc_attr( self::OPTION_NAME . '[' . $id . ']' ); ?>"
        >
            <?php foreach ( $options as $value => $label ) : ?>
                <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $current, $value ); ?>>
                    <?php echo esc_html( $label ); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }
}
