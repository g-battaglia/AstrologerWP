<?php
/**
 * Shortcodes and Gutenberg blocks manager for Astrologer API.
 *
 * @package Astrologer_API_Playground
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class responsible for managing shortcodes and Gutenberg blocks.
 *
 * It registers:
 * - Traditional shortcodes to insert components in posts/pages
 * - Gutenberg blocks for the modern editor
 *
 * Each React component is mounted in a div container with data-* attributes.
 * The React bundle reads these attributes and renders the appropriate component.
 *
 * @since 1.0.0
 */
class Astrologer_API_Blocks {

    /**
     * Constructor - registers shortcodes and blocks.
     */
    public function __construct() {
        // Register shortcodes
        add_action( 'init', array( $this, 'register_shortcodes' ) );

        // Register Gutenberg blocks
        add_action( 'init', array( $this, 'register_blocks' ) );

        // Register new block category
        add_filter( 'block_categories_all', array( $this, 'register_block_category' ), 10, 2 );
    }

    /**
     * Registers the 'Astrology' block category.
     *
     * @param array $categories Existing categories.
     * @param WP_Block_Editor_Context $context Editor context.
     * @return array Modified categories.
     */
    public function register_block_category( $categories, $context ) {
        return array_merge(
            $categories,
            array(
                array(
                    'slug'  => 'astrology',
                    'title' => __( 'Astrology', 'astrologer-api' ),
                    'icon'  => null,
                ),
            )
        );
    }

    /**
     * Registers all plugin shortcodes.
     *
     * @return void
     */
    public function register_shortcodes(): void {
        // [astrologer_natal_chart] - Natal chart graphic
        add_shortcode( 'astrologer_natal_chart', array( $this, 'render_natal_chart_shortcode' ) );

        // [astrologer_aspects_table] - Aspects table
        add_shortcode( 'astrologer_aspects_table', array( $this, 'render_aspects_table_shortcode' ) );

        // [astrologer_elements_chart] - Elements chart
        add_shortcode( 'astrologer_elements_chart', array( $this, 'render_elements_chart_shortcode' ) );

        // [astrologer_modalities_chart] - Modalities chart
        add_shortcode( 'astrologer_modalities_chart', array( $this, 'render_modalities_chart_shortcode' ) );

        // [astrologer_birth_form] - Full form for birth data input
        add_shortcode( 'astrologer_birth_form', array( $this, 'render_birth_form_shortcode' ) );

        // [astrologer_positions_table] - Planetary positions table
        add_shortcode( 'astrologer_positions_table', array( $this, 'render_positions_table_shortcode' ) );

        // [astrologer_synastry_chart] - Synastry chart (two subjects)
        add_shortcode( 'astrologer_synastry_chart', array( $this, 'render_synastry_chart_shortcode' ) );

        // [astrologer_synastry_form] - Interactive synastry form
        add_shortcode( 'astrologer_synastry_form', array( $this, 'render_synastry_form_shortcode' ) );

        // [astrologer_transit_chart] - Transit chart
        add_shortcode( 'astrologer_transit_chart', array( $this, 'render_transit_chart_shortcode' ) );

        // [astrologer_transit_form] - Interactive transit form
        add_shortcode( 'astrologer_transit_form', array( $this, 'render_transit_form_shortcode' ) );

        // [astrologer_compatibility_chart] - Relationship score display
        add_shortcode( 'astrologer_compatibility_chart', array( $this, 'render_compatibility_chart_shortcode' ) );

        // [astrologer_compatibility_form] - Interactive compatibility form
        add_shortcode( 'astrologer_compatibility_form', array( $this, 'render_compatibility_form_shortcode' ) );

        // [astrologer_composite_chart] - Composite chart (two subjects combined)
        add_shortcode( 'astrologer_composite_chart', array( $this, 'render_composite_chart_shortcode' ) );

        // [astrologer_composite_form] - Interactive composite form
        add_shortcode( 'astrologer_composite_form', array( $this, 'render_composite_form_shortcode' ) );

        // [astrologer_solar_return_chart] - Solar Return chart
        add_shortcode( 'astrologer_solar_return_chart', array( $this, 'render_solar_return_chart_shortcode' ) );

        // [astrologer_solar_return_form] - Interactive Solar Return form
        add_shortcode( 'astrologer_solar_return_form', array( $this, 'render_solar_return_form_shortcode' ) );

        // [astrologer_lunar_return_chart] - Lunar Return chart
        add_shortcode( 'astrologer_lunar_return_chart', array( $this, 'render_lunar_return_chart_shortcode' ) );

        // [astrologer_lunar_return_form] - Interactive Lunar Return form
        add_shortcode( 'astrologer_lunar_return_form', array( $this, 'render_lunar_return_form_shortcode' ) );

        // [astrologer_now_chart] - Current moment chart
        add_shortcode( 'astrologer_now_chart', array( $this, 'render_now_chart_shortcode' ) );

        // [astrologer_now_form] - Interactive now form
        add_shortcode( 'astrologer_now_form', array( $this, 'render_now_form_shortcode' ) );

        // [astrologer_moon_phase] - Moon phase display
        add_shortcode( 'astrologer_moon_phase', array( $this, 'render_moon_phase_shortcode' ) );
    }

    /**
     * Registers Gutenberg blocks.
     *
     * @return void
     */
    public function register_blocks(): void {
        // Check if the register_block_type function exists (WP 5.0+)
        if ( ! function_exists( 'register_block_type' ) ) {
            return;
        }

        // Register script for the block editor
        add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );

        // Block: Natal chart
        register_block_type( 'astrologer-api/natal-chart', array(
            'render_callback' => array( $this, 'render_natal_chart_block' ),
            'attributes'      => $this->get_chart_block_attributes(),
        ) );

        // Block: Aspects table
        register_block_type( 'astrologer-api/aspects-table', array(
            'render_callback' => array( $this, 'render_aspects_table_block' ),
            'attributes'      => $this->get_chart_block_attributes(),
        ) );

        // Block: Elements chart
        register_block_type( 'astrologer-api/elements-chart', array(
            'render_callback' => array( $this, 'render_elements_chart_block' ),
            'attributes'      => $this->get_chart_block_attributes(),
        ) );

        // Block: Modalities chart
        register_block_type( 'astrologer-api/modalities-chart', array(
            'render_callback' => array( $this, 'render_modalities_chart_block' ),
            'attributes'      => $this->get_chart_block_attributes(),
        ) );

        // Block: Full birth form
        register_block_type( 'astrologer-api/birth-form', array(
            'render_callback' => array( $this, 'render_birth_form_block' ),
            'attributes'      => array(
                'showChart'      => array( 'type' => 'boolean', 'default' => true ),
                'showAspects'    => array( 'type' => 'boolean', 'default' => true ),
                'showElements'   => array( 'type' => 'boolean', 'default' => true ),
                'showModalities' => array( 'type' => 'boolean', 'default' => true ),
            ),
        ) );
        
        // Block: Synastry Form
        register_block_type( 'astrologer-api/synastry-form', array(
            'render_callback' => array( $this, 'render_synastry_form_block' ),
            'attributes'      => array(
                'show_chart' => array( 'type' => 'string', 'default' => 'true' ),
                'collapse_on_submit' => array( 'type' => 'string', 'default' => 'false' ),
                // Add first_ and second_ subject attributes dynamically if needed, 
                // but the react container handles props passing just fine even if not explicitly defined here 
                // (as long as they are passed from the block editor save/render, wait, 
                // in dynamic blocks attributes passed to render_callback are filtered by block.json/attributes definition.
                // So I SHOULD define them if I want to pre-fill them from the backend. 
                // However, since we use the React component to manage state, we might not strictly need them 
                // UNLESS the user sets defaults in the editor.
                // For simplicity, let's assume we rely on the component defaults or add them later. 
                // But wait, the previous blocks defined them. Let's reuse get_synastry_block_attributes logic + form specific ones.
             ) + $this->get_synastry_block_attributes(), // Merge synastry attributes
        ) );

        // Block: Synastry chart
        register_block_type( 'astrologer-api/synastry-chart', array(
            'render_callback' => array( $this, 'render_synastry_chart_block' ),
            'attributes'      => $this->get_synastry_block_attributes(),
        ) );



        // Block: Transit Form
        register_block_type( 'astrologer-api/transit-form', array(
            'render_callback' => array( $this, 'render_transit_form_block' ),
            'attributes'      => array(
                'show_chart' => array( 'type' => 'string', 'default' => 'true' ),
             ),
        ) );

        // Block: Transit chart
        register_block_type( 'astrologer-api/transit-chart', array(
            'render_callback' => array( $this, 'render_transit_chart_block' ),
            'attributes'      => $this->get_transit_block_attributes(),
        ) );

         // Block: Compatibility chart (Score)
        register_block_type( 'astrologer-api/compatibility-chart', array(
            'render_callback' => array( $this, 'render_compatibility_chart_block' ),
            'attributes'      => $this->get_synastry_block_attributes(), // Same attributes as synastry
        ) );

        // Block: Positions Table
        register_block_type( 'astrologer-api/positions-table', array(
            'render_callback' => array( $this, 'render_positions_table_block' ),
            'attributes'      => $this->get_chart_block_attributes(),
        ) );

        // Block: Composite chart
        register_block_type( 'astrologer-api/composite-chart', array(
            'render_callback' => array( $this, 'render_composite_chart_block' ),
            'attributes'      => $this->get_synastry_block_attributes(),
        ) );

        // Block: Composite form
        register_block_type( 'astrologer-api/composite-form', array(
            'render_callback' => array( $this, 'render_composite_form_block' ),
            'attributes'      => array(
                'show_chart' => array( 'type' => 'string', 'default' => 'true' ),
            ),
        ) );

        // Block: Now chart (current moment)
        register_block_type( 'astrologer-api/now-chart', array(
            'render_callback' => array( $this, 'render_now_chart_block' ),
            'attributes'      => array(
                'autoRefresh'       => array( 'type' => 'number', 'default' => 0 ),
                'showRefreshButton' => array( 'type' => 'boolean', 'default' => true ),
            ),
        ) );

        // Block: Solar Return chart
        register_block_type( 'astrologer-api/solar-return-chart', array(
            'render_callback' => array( $this, 'render_solar_return_chart_block' ),
            'attributes'      => $this->get_return_chart_block_attributes(),
        ) );

        // Block: Solar Return form
        register_block_type( 'astrologer-api/solar-return-form', array(
            'render_callback' => array( $this, 'render_solar_return_form_block' ),
            'attributes'      => array(
                'show_chart' => array( 'type' => 'string', 'default' => 'true' ),
            ),
        ) );

        // Block: Lunar Return chart
        register_block_type( 'astrologer-api/lunar-return-chart', array(
            'render_callback' => array( $this, 'render_lunar_return_chart_block' ),
            'attributes'      => $this->get_return_chart_block_attributes( true ),
        ) );

        // Block: Lunar Return form
        register_block_type( 'astrologer-api/lunar-return-form', array(
            'render_callback' => array( $this, 'render_lunar_return_form_block' ),
            'attributes'      => array(
                'show_chart' => array( 'type' => 'string', 'default' => 'true' ),
            ),
        ) );

        // Block: Moon Phase display
        register_block_type( 'astrologer-api/moon-phase', array(
            'render_callback' => array( $this, 'render_moon_phase_block' ),
            'attributes'      => array_merge(
                $this->get_chart_block_attributes(),
                array(
                    'use_now' => array( 'type' => 'boolean', 'default' => false ),
                )
            ),
        ) );
    }

    /**
     * Enqueues assets for the block editor.
     *
     * @return void
     */
    public function enqueue_block_editor_assets(): void {
        $dist_path = ASTROLOGER_API_PLUGIN_DIR . 'frontend/dist/';
        $dist_url  = ASTROLOGER_API_PLUGIN_URL . 'frontend/dist/';

        // 1. Production Build
        $blocks_js_path = $dist_path . 'assets/blocks.js';
        $blocks_js_url  = $dist_url . 'assets/blocks.js';
        $style_url      = $dist_url . 'assets/index.css';

        if ( file_exists( $blocks_js_path ) ) {
            wp_enqueue_script(
                'astrologer-api-blocks',
                $blocks_js_url,
                array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n' ),
                ASTROLOGER_API_VERSION,
                true
            );

            // Add type="module" for ES modules (chunks support) - handled by add_module_type but we are using IIFE/Standard now technically? 
            // Actually we went back to auto runtime but not IIFE? Wait, we reverted to default format which is ESM.
            // So we DO need type="module".
            add_filter( 'script_loader_tag', array( $this, 'add_module_type' ), 10, 3 );
            
            wp_enqueue_style(
                 'astrologer-api-blocks-style',
                 $style_url,
                 array(),
                 ASTROLOGER_API_VERSION
            );
            
            // Pass configuration to the blocks (nonce, settings, etc.)
            wp_localize_script( 'astrologer-api-blocks', 'astrologerApiConfig', Astrologer_API_Frontend::get_frontend_config() );
            
            return;
        }

        // 2. Dev Mode (Vite HMR) - Fallback if build is missing
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
             $this->enqueue_dev_assets();
        }
    }

    /**
     * Enqueues assets from the Vite dev server.
     */
    private function enqueue_dev_assets(): void {
        $vite_server = 'http://[::1]:5173'; // Dev server URL

        // 1. Vite Client
        wp_enqueue_script(
            'vite-client',
            $vite_server . '/@vite/client',
            array(),
            null, // No versioning for dev
            true
        );

        // 2. Entry Point for Blocks
        wp_enqueue_script(
            'astrologer-api-blocks',
            $vite_server . '/src/blocks/index.tsx', // Direct TSX source
            array( 'vite-client', 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n' ),
            time(), // Force cache bust
            true
        );

        // Add type="module" for both
        add_filter( 'script_loader_tag', array( $this, 'add_module_type' ), 10, 3 );
        
        // Pass configuration to the blocks (nonce, settings, etc.)
        wp_localize_script( 'astrologer-api-blocks', 'astrologerApiConfig', Astrologer_API_Frontend::get_frontend_config() );
    }

    /**
     * Adds type="module" to script tags for ES modules.
     *
     * @param string $tag    Original HTML tag.
     * @param string $handle Script handle.
     * @param string $src    Script URL.
     * @return string Modified HTML tag.
     */
    public function add_module_type( string $tag, string $handle, string $src ): string {
        if ( 'astrologer-api-blocks' === $handle || 'vite-client' === $handle ) {
            $tag = str_replace( '<script ', '<script type="module" ', $tag );
        }
        return $tag;
    }

    /**
     * Common attributes for blocks that display charts.
     *
     * @return array
     */
    private function get_chart_block_attributes(): array {
        return array(
            'name'      => array( 'type' => 'string', 'default' => 'Example Subject' ),
            'year'      => array( 'type' => 'number', 'default' => 1988 ),
            'month'     => array( 'type' => 'number', 'default' => 3 ),
            'day'       => array( 'type' => 'number', 'default' => 15 ),
            'hour'      => array( 'type' => 'number', 'default' => 14 ),
            'minute'    => array( 'type' => 'number', 'default' => 30 ),
            'latitude'  => array( 'type' => 'number', 'default' => 51.5074 ), // London
            'longitude' => array( 'type' => 'number', 'default' => -0.1278 ),
            'timezone'  => array( 'type' => 'string', 'default' => 'Europe/London' ),
            'city'      => array( 'type' => 'string', 'default' => 'London' ),
            'nation'    => array( 'type' => 'string', 'default' => 'GB' ),
        );
    }

    /**
     * Attributes for Synastry/Compatibility blocks (Two subjects).
     */
     private function get_synastry_block_attributes(): array {
        $atts = array();
        // First subject (Defaults to London 1988)
        foreach ($this->get_chart_block_attributes() as $key => $conf) {
            $atts["first_{$key}"] = $conf;
            if ($key === 'name') $atts["first_{$key}"]['default'] = 'Person A';
        }
        // Second subject (Different Defaults: NYC 1992)
        foreach ($this->get_chart_block_attributes() as $key => $conf) {
            $atts["second_{$key}"] = $conf;
            if ($key === 'name') $atts["second_{$key}"]['default'] = 'Person B';
            if ($key === 'year') $atts["second_{$key}"]['default'] = 1992;
            if ($key === 'month') $atts["second_{$key}"]['default'] = 6;
            if ($key === 'day') $atts["second_{$key}"]['default'] = 2;
            if ($key === 'hour') $atts["second_{$key}"]['default'] = 9;
            if ($key === 'latitude') $atts["second_{$key}"]['default'] = 40.7128; // NYC
            if ($key === 'longitude') $atts["second_{$key}"]['default'] = -74.0060;
            if ($key === 'timezone') $atts["second_{$key}"]['default'] = 'America/New_York';
            if ($key === 'city') $atts["second_{$key}"]['default'] = 'New York';
            if ($key === 'nation') $atts["second_{$key}"]['default'] = 'US';
        }
        return $atts;
    }

    /**
     * Attributes for Transit blocks.
     */
    private function get_transit_block_attributes(): array {
        $atts = $this->get_chart_block_attributes();
        
        $atts['transit_year'] = array('type' => 'number', 'default' => (int) gmdate('Y'));
        $atts['transit_month'] = array('type' => 'number', 'default' => (int) gmdate('n'));
        $atts['transit_day'] = array('type' => 'number', 'default' => (int) gmdate('j'));
        $atts['transit_hour'] = array('type' => 'number', 'default' => 12);
        $atts['transit_minute'] = array('type' => 'number', 'default' => 0);
        $atts['transit_city'] = array('type' => 'string', 'default' => '');
        $atts['transit_nation'] = array('type' => 'string', 'default' => '');
        $atts['transit_latitude'] = array('type' => 'number', 'default' => 0);
        $atts['transit_longitude'] = array('type' => 'number', 'default' => 0);
        $atts['transit_timezone'] = array('type' => 'string', 'default' => 'UTC');

        return $atts;
    }

    // =========================================================================
    // RENDER SHORTCODES
    // =========================================================================

    /**
     * Renders the [astrologer_natal_chart] shortcode.
     *
     * Supported attributes:
     * - name: Subject name
     * - year, month, day: Birth date
     * - hour, minute: Birth time
     * - latitude, longitude: Coordinates
     * - timezone: Timezone
     * - city, nation: Location (optional, for display)
     *
     * @param array $atts Shortcode attributes.
     * @return string Container HTML.
     */
    public function render_natal_chart_shortcode( $atts ): string {
        $atts = shortcode_atts( $this->get_default_subject_atts(), $atts, 'astrologer_natal_chart' );
        return $this->render_react_container( 'natal-chart', $atts );
    }

    /**
     * Renders the [astrologer_aspects_table] shortcode.
     *
     * @param array $atts Shortcode attributes.
     * @return string Container HTML.
     */
    public function render_aspects_table_shortcode( $atts ): string {
        $atts = shortcode_atts( $this->get_default_subject_atts(), $atts, 'astrologer_aspects_table' );
        return $this->render_react_container( 'aspects-table', $atts );
    }

    /**
     * Renders the [astrologer_elements_chart] shortcode.
     *
     * @param array $atts Shortcode attributes.
     * @return string Container HTML.
     */
    public function render_elements_chart_shortcode( $atts ): string {
        $atts = shortcode_atts( $this->get_default_subject_atts(), $atts, 'astrologer_elements_chart' );
        return $this->render_react_container( 'elements-chart', $atts );
    }

    /**
     * Renders the [astrologer_modalities_chart] shortcode.
     *
     * @param array $atts Shortcode attributes.
     * @return string Container HTML.
     */
    public function render_modalities_chart_shortcode( $atts ): string {
        $atts = shortcode_atts( $this->get_default_subject_atts(), $atts, 'astrologer_modalities_chart' );
        return $this->render_react_container( 'modalities-chart', $atts );
    }

    /**
     * Renders the [astrologer_birth_form] shortcode.
     *
     * @param array $atts Shortcode attributes.
     * @return string Container HTML.
     */
    public function render_birth_form_shortcode( $atts ): string {
        $atts = shortcode_atts( array(
            'show_chart'      => 'true',
            'show_aspects'    => 'true',
            'show_elements'   => 'true',
            'show_modalities' => 'true',
        ), $atts, 'astrologer_birth_form' );

        return $this->render_react_container( 'birth-form', $atts );
    }

    /**
     * Renders the [astrologer_positions_table] shortcode.
     *
     * @param array $atts Shortcode attributes.
     * @return string Container HTML.
     */
    public function render_positions_table_shortcode( $atts ): string {
        $atts = shortcode_atts( $this->get_default_subject_atts(), $atts, 'astrologer_positions_table' );
        return $this->render_react_container( 'positions-table', $atts );
    }

    /**
     * Renders the [astrologer_synastry_chart] shortcode.
     *
     * Supported attributes (prefixed with first_ and second_):
     * - first_name, first_year, first_month, first_day, first_hour, first_minute
     * - first_latitude, first_longitude, first_timezone, first_city, first_nation
     * - second_name, second_year, second_month, second_day, second_hour, second_minute
     * - second_latitude, second_longitude, second_timezone, second_city, second_nation
     *
     * @param array $atts Shortcode attributes.
     * @return string Container HTML.
     */
    public function render_synastry_chart_shortcode( $atts ): string {
        $atts = shortcode_atts( $this->get_default_synastry_atts(), $atts, 'astrologer_synastry_chart' );
        return $this->render_react_container( 'synastry-chart', $atts );
    }

    /**
     * Renders the [astrologer_synastry_form] shortcode.
     *
     * @param array $atts Shortcode attributes.
     * @return string Container HTML.
     */
    public function render_synastry_form_shortcode( $atts ): string {
        $atts = shortcode_atts( array(
            'show_chart'         => 'true',
            'collapse_on_submit' => 'false',
        ), $atts, 'astrologer_synastry_form' );

        return $this->render_react_container( 'synastry-form', $atts );
    }

    /**
     * Renders the [astrologer_transit_chart] shortcode.
     *
     * Combines natal subject attributes with transit_ prefixed attributes:
     * - name, year, month, day, hour, minute, latitude, longitude, timezone (natal)
     * - transit_year, transit_month, transit_day, transit_hour, transit_minute
     * - transit_latitude, transit_longitude, transit_timezone, transit_city, transit_nation
     *
     * @param array $atts Shortcode attributes.
     * @return string Container HTML.
     */
    public function render_transit_chart_shortcode( $atts ): string {
        $atts = shortcode_atts( $this->get_default_transit_atts(), $atts, 'astrologer_transit_chart' );
        return $this->render_react_container( 'transit-chart', $atts );
    }

    /**
     * Renders the [astrologer_transit_form] shortcode.
     *
     * @param array $atts Shortcode attributes.
     * @return string Container HTML.
     */
    public function render_transit_form_shortcode( $atts ): string {
        $atts = shortcode_atts( array(
            'show_chart' => 'true',
        ), $atts, 'astrologer_transit_form' );

        return $this->render_react_container( 'transit-form', $atts );
    }

    /**
     * Renders the [astrologer_compatibility_chart] shortcode.
     *
     * Uses same attributes as synastry_chart (first_ and second_ prefixed).
     *
     * @param array $atts Shortcode attributes.
     * @return string Container HTML.
     */
    public function render_compatibility_chart_shortcode( $atts ): string {
        $atts = shortcode_atts( $this->get_default_synastry_atts(), $atts, 'astrologer_compatibility_chart' );
        return $this->render_react_container( 'compatibility-chart', $atts );
    }

    /**
     * Renders the [astrologer_compatibility_form] shortcode.
     *
     * @param array $atts Shortcode attributes.
     * @return string Container HTML.
     */
    public function render_compatibility_form_shortcode( $atts ): string {
        $atts = shortcode_atts( array(
            'show_chart'         => 'true',
            'collapse_on_submit' => 'false',
        ), $atts, 'astrologer_compatibility_form' );

        return $this->render_react_container( 'compatibility-form', $atts );
    }

    /**
     * Renders the [astrologer_composite_chart] shortcode.
     *
     * Uses same attributes as synastry_chart (first_ and second_ prefixed).
     *
     * @param array $atts Shortcode attributes.
     * @return string Container HTML.
     */
    public function render_composite_chart_shortcode( $atts ): string {
        $atts = shortcode_atts( $this->get_default_synastry_atts(), $atts, 'astrologer_composite_chart' );
        return $this->render_react_container( 'composite-chart', $atts );
    }

    /**
     * Renders the [astrologer_composite_form] shortcode.
     *
     * @param array $atts Shortcode attributes.
     * @return string Container HTML.
     */
    public function render_composite_form_shortcode( $atts ): string {
        $atts = shortcode_atts( array(
            'show_chart' => 'true',
        ), $atts, 'astrologer_composite_form' );

        return $this->render_react_container( 'composite-form', $atts );
    }

    /**
     * Renders the [astrologer_now_chart] shortcode.
     *
     * Shows the chart for the current moment.
     *
     * @param array $atts Shortcode attributes.
     * @return string Container HTML.
     */
    public function render_now_chart_shortcode( $atts ): string {
        $atts = shortcode_atts( array(), $atts, 'astrologer_now_chart' );
        return $this->render_react_container( 'now-chart', $atts );
    }

    /**
     * Renders the [astrologer_now_form] shortcode.
     *
     * @param array $atts Shortcode attributes.
     * @return string Container HTML.
     */
    public function render_now_form_shortcode( $atts ): string {
        $atts = shortcode_atts( array(
            'show_chart' => 'true',
        ), $atts, 'astrologer_now_form' );

        return $this->render_react_container( 'now-form', $atts );
    }

    /**
     * Renders the [astrologer_moon_phase] shortcode.
     *
     * Supported attributes:
     * - All subject attributes (name, year, month, day, hour, minute, latitude, longitude, timezone, city, nation)
     * - use_now: If "true", shows the moon phase for the current moment instead
     *
     * @param array $atts Shortcode attributes.
     * @return string Container HTML.
     */
    public function render_moon_phase_shortcode( $atts ): string {
        $defaults = array_merge(
            $this->get_default_subject_atts(),
            array( 'use_now' => 'false' )
        );
        $atts = shortcode_atts( $defaults, $atts, 'astrologer_moon_phase' );

        // Convert string "true"/"false" to boolean for React
        if ( 'true' === $atts['use_now'] || '1' === $atts['use_now'] ) {
            $atts['useNow'] = true;
        }
        unset( $atts['use_now'] );

        return $this->render_react_container( 'moon-phase', $atts );
    }

    // =========================================================================
    // RENDER GUTENBERG BLOCKS
    // =========================================================================

    /**
     * Renders the Gutenberg block for the natal chart.
     *
     * @param array $attributes Block attributes.
     * @return string Container HTML.
     */
    public function render_natal_chart_block( array $attributes ): string {
        return $this->render_react_container( 'natal-chart', $attributes );
    }

    /**
     * Renders the Gutenberg block for the aspects table.
     *
     * @param array $attributes Block attributes.
     * @return string Container HTML.
     */
    public function render_aspects_table_block( array $attributes ): string {
        return $this->render_react_container( 'aspects-table', $attributes );
    }

    /**
     * Renders the Gutenberg block for the elements chart.
     *
     * @param array $attributes Block attributes.
     * @return string Container HTML.
     */
    public function render_elements_chart_block( array $attributes ): string {
        return $this->render_react_container( 'elements-chart', $attributes );
    }

    /**
     * Renders the Gutenberg block for the modalities chart.
     *
     * @param array $attributes Block attributes.
     * @return string Container HTML.
     */
    public function render_modalities_chart_block( array $attributes ): string {
        return $this->render_react_container( 'modalities-chart', $attributes );
    }

    /**
     * Renders the Gutenberg block for the birth form.
     *
     * @param array $attributes Block attributes.
     * @return string Container HTML.
     */
    public function render_birth_form_block( array $attributes ): string {
        return $this->render_react_container( 'birth-form', $attributes );
    }

    /**
     * Renders the Gutenberg block for the synastry form.
     */
    public function render_synastry_form_block( array $attributes ): string {
        return $this->render_react_container( 'synastry-form', $attributes );
    }

    /**
     * Renders the Gutenberg block for the transit form.
     */
    public function render_transit_form_block( array $attributes ): string {
        return $this->render_react_container( 'transit-form', $attributes );
    }

    /**
     * Renders the Gutenberg block for the synastry chart.
     */
    public function render_synastry_chart_block( array $attributes ): string {
        return $this->render_react_container( 'synastry-chart', $attributes );
    }

    /**
     * Renders the Gutenberg block for the transit chart.
     */
    public function render_transit_chart_block( array $attributes ): string {
        return $this->render_react_container( 'transit-chart', $attributes );
    }

    /**
     * Renders the Gutenberg block for the compatibility chart.
     */
    public function render_compatibility_chart_block( array $attributes ): string {
        return $this->render_react_container( 'compatibility-chart', $attributes );
    }

    /**
     * Renders the Gutenberg block for the positions table.
     */
    public function render_positions_table_block( array $attributes ): string {
        return $this->render_react_container( 'positions-table', $attributes );
    }

    /**
     * Renders the Gutenberg block for the composite chart.
     */
    public function render_composite_chart_block( array $attributes ): string {
        return $this->render_react_container( 'composite-chart', $attributes );
    }

    /**
     * Renders the Gutenberg block for the composite form.
     */
    public function render_composite_form_block( array $attributes ): string {
        return $this->render_react_container( 'composite-form', $attributes );
    }

    /**
     * Renders the Gutenberg block for the now chart.
     */
    public function render_now_chart_block( array $attributes ): string {
        return $this->render_react_container( 'now-chart', $attributes );
    }

    /**
     * Renders the Gutenberg block for the Solar Return chart.
     */
    public function render_solar_return_chart_block( array $attributes ): string {
        return $this->render_react_container( 'solar-return-chart', $attributes );
    }

    /**
     * Renders the Gutenberg block for the Solar Return form.
     */
    public function render_solar_return_form_block( array $attributes ): string {
        return $this->render_react_container( 'solar-return-form', $attributes );
    }

    /**
     * Renders the Gutenberg block for the Lunar Return chart.
     */
    public function render_lunar_return_chart_block( array $attributes ): string {
        return $this->render_react_container( 'lunar-return-chart', $attributes );
    }

    /**
     * Renders the Gutenberg block for the Lunar Return form.
     */
    public function render_lunar_return_form_block( array $attributes ): string {
        return $this->render_react_container( 'lunar-return-form', $attributes );
    }

    /**
     * Renders the Gutenberg block for the Moon Phase display.
     */
    public function render_moon_phase_block( array $attributes ): string {
        // Convert use_now boolean to useNow for React prop convention
        if ( isset( $attributes['use_now'] ) ) {
            $attributes['useNow'] = (bool) $attributes['use_now'];
            unset( $attributes['use_now'] );
        }
        return $this->render_react_container( 'moon-phase', $attributes );
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    /**
     * Default attributes for a subject.
     *
     * @return array
     */
    private function get_default_subject_atts(): array {
        return array(
            'name'      => '',
            'year'      => '',
            'month'     => '',
            'day'       => '',
            'hour'      => '',
            'minute'    => '',
            'latitude'  => '',
            'longitude' => '',
            'timezone'  => '',
            'city'      => '',
            'nation'    => '',
        );
    }

    /**
     * Default attributes for synastry shortcodes (two subjects).
     *
     * @return array
     */
    private function get_default_synastry_atts(): array {
        $atts = array();

        // First subject attributes
        foreach ( $this->get_default_subject_atts() as $key => $value ) {
            $atts[ "first_{$key}" ] = $value;
        }

        // Second subject attributes
        foreach ( $this->get_default_subject_atts() as $key => $value ) {
            $atts[ "second_{$key}" ] = $value;
        }

        return $atts;
    }

    /**
     * Default attributes for return chart shortcodes.
     *
     * @param bool $include_month Whether to include month attribute (for lunar return).
     * @return array
     */
    private function get_default_return_atts( bool $include_month = false ): array {
        $atts = $this->get_default_subject_atts();
        $atts['return_year'] = '';
        
        if ( $include_month ) {
            $atts['return_month'] = '';
        }

        return $atts;
    }

    /**
     * Returns block attributes for return charts (Solar/Lunar Return).
     *
     * @param bool $include_month Whether to include month attribute.
     * @return array
     */
    private function get_return_chart_block_attributes( bool $include_month = false ): array {
        $atts = $this->get_chart_block_attributes();
        $atts['return_year'] = array( 'type' => 'number', 'default' => 0 );
        
        if ( $include_month ) {
            $atts['return_month'] = array( 'type' => 'number', 'default' => 0 );
        }

        return $atts;
    }

    /**
     * Renders the [astrologer_solar_return_chart] shortcode.
     *
     * @param array $atts Shortcode attributes.
     * @return string Container HTML.
     */
    public function render_solar_return_chart_shortcode( $atts ): string {
        $atts = shortcode_atts( $this->get_default_return_atts(), $atts, 'astrologer_solar_return_chart' );
        return $this->render_react_container( 'solar-return-chart', $atts );
    }

    /**
     * Renders the [astrologer_solar_return_form] shortcode.
     *
     * @param array $atts Shortcode attributes.
     * @return string Container HTML.
     */
    public function render_solar_return_form_shortcode( $atts ): string {
        $atts = shortcode_atts( array(
            'show_chart' => 'true',
        ), $atts, 'astrologer_solar_return_form' );

        return $this->render_react_container( 'solar-return-form', $atts );
    }

    /**
     * Renders the [astrologer_lunar_return_chart] shortcode.
     *
     * @param array $atts Shortcode attributes.
     * @return string Container HTML.
     */
    public function render_lunar_return_chart_shortcode( $atts ): string {
        $atts = shortcode_atts( $this->get_default_return_atts( true ), $atts, 'astrologer_lunar_return_chart' );
        return $this->render_react_container( 'lunar-return-chart', $atts );
    }

    /**
     * Renders the [astrologer_lunar_return_form] shortcode.
     *
     * @param array $atts Shortcode attributes.
     * @return string Container HTML.
     */
    public function render_lunar_return_form_shortcode( $atts ): string {
        $atts = shortcode_atts( array(
            'show_chart' => 'true',
        ), $atts, 'astrologer_lunar_return_form' );

        return $this->render_react_container( 'lunar-return-form', $atts );
    }

    /**
     * Default attributes for transit shortcodes.
     *
     * @return array
     */
    private function get_default_transit_atts(): array {
        $atts = $this->get_default_subject_atts();

        // Transit date/location attributes
        $atts['transit_year']      = '';
        $atts['transit_month']     = '';
        $atts['transit_day']       = '';
        $atts['transit_hour']      = '';
        $atts['transit_minute']    = '';
        $atts['transit_latitude']  = '';
        $atts['transit_longitude'] = '';
        $atts['transit_timezone']  = '';
        $atts['transit_city']      = '';
        $atts['transit_nation']    = '';

        return $atts;
    }

    /**
     * Generates a div container for mounting a React component.
     *
     * The React bundle will look for elements with data-astrologer-component
     * and mount the appropriate component by reading data-props.
     *
     * @param string $component Component name.
     * @param array  $props     Props to pass to the component.
     * @return string Container HTML.
     */
    private function render_react_container( string $component, array $props ): string {
        // Generate a unique ID for this container
        $unique_id = 'astrologer-' . $component . '-' . wp_rand();

        // Strip out empty props
        $clean_props = array_filter( $props, function( $value ) {
            return $value !== '' && $value !== null;
        } );

        // JSON encode props
        $props_json = wp_json_encode( $clean_props );

        // Read global UI theme mode from settings (light/dark)
        $settings      = Astrologer_API_Settings::get_settings();
        $ui_theme_mode = isset( $settings['ui_theme_mode'] ) ? (string) $settings['ui_theme_mode'] : 'light';

        $classes = 'astrologer-component';
        if ( 'dark' === $ui_theme_mode ) {
            // Local dark theme for all injected components
            $classes .= ' astrologer-theme-dark dark';
        } else {
            $classes .= ' astrologer-theme-light';
        }

        // Ensure that frontend assets are enqueued
        Astrologer_API_Frontend::enqueue_frontend_assets();

        return sprintf(
            '<div id="%s" class="%s" data-astrologer-component="%s" data-props="%s"><div class="astrologer-loading">%s</div></div>',
            esc_attr( $unique_id ),
            esc_attr( $classes ),
            esc_attr( $component ),
            esc_attr( $props_json ),
            esc_html__( 'Loading...', 'astrologer-api' )
        );
    }
}
