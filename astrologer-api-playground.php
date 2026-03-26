<?php
/**
 * Plugin Name: Astrologer API Playground
 * Plugin URI: https://github.com/g-battaglia/astrologer-api-playground
 * Description: WordPress integration for Astrologer API – display natal charts, aspects, elements, and modalities.
 * Version: 1.0.0
 * Author: Giacomo Battaglia
 * Author URI: https://github.com/g-battaglia
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: astrologer-api
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.0
 *
 * @package Astrologer_API_Playground
 */

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/*
|--------------------------------------------------------------------------
| Plugin constants
|--------------------------------------------------------------------------
| Define the main constants used throughout the plugin.
*/

define( 'ASTROLOGER_API_VERSION', '1.0.0' );
define( 'ASTROLOGER_API_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ASTROLOGER_API_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'ASTROLOGER_API_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/*
|--------------------------------------------------------------------------
| Class autoloading
|--------------------------------------------------------------------------
| Manually include the plugin classes for simplicity.
| In a larger project you would typically use Composer autoload.
*/

require_once ASTROLOGER_API_PLUGIN_DIR . 'includes/class-astrologer-api-settings.php';
require_once ASTROLOGER_API_PLUGIN_DIR . 'includes/class-astrologer-api-rest.php';
require_once ASTROLOGER_API_PLUGIN_DIR . 'includes/class-astrologer-api-blocks.php';
require_once ASTROLOGER_API_PLUGIN_DIR . 'includes/class-astrologer-api-frontend.php';

/*
|--------------------------------------------------------------------------
| Plugin initialization
|--------------------------------------------------------------------------
| Main class orchestrating all plugin features.
*/

/**
 * Main class for the Astrologer API Playground plugin.
 *
 * It initializes all components:
 * - Settings (admin page)
 * - REST API (bridge to Astrologer API)
 * - Blocks (shortcodes and Gutenberg blocks)
 * - Frontend (React assets)
 *
 * @since 1.0.0
 */
final class Astrologer_API_Playground {

    /**
     * Singleton instance of the plugin.
     *
     * @var Astrologer_API_Playground|null
     */
    private static ?Astrologer_API_Playground $instance = null;

    /**
     * Admin settings handler.
     *
     * @var Astrologer_API_Settings
     */
    public Astrologer_API_Settings $settings;

    /**
     * REST endpoints handler.
     *
     * @var Astrologer_API_REST
     */
    public Astrologer_API_REST $rest;

    /**
     * Blocks and shortcodes handler.
     *
     * @var Astrologer_API_Blocks
     */
    public Astrologer_API_Blocks $blocks;

    /**
     * Frontend assets handler.
     *
     * @var Astrologer_API_Frontend
     */
    public Astrologer_API_Frontend $frontend;

    /**
     * Private constructor for the singleton pattern.
     */
    private function __construct() {
        $this->init_components();
        $this->register_hooks();
    }

    /**
     * Returns the singleton instance of the plugin.
     *
     * @return Astrologer_API_Playground
     */
    public static function get_instance(): Astrologer_API_Playground {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initializes all plugin components.
     *
     * @return void
     */
    private function init_components(): void {
        $this->settings = new Astrologer_API_Settings();
        $this->rest     = new Astrologer_API_REST();
        $this->blocks   = new Astrologer_API_Blocks();
        $this->frontend = new Astrologer_API_Frontend();
    }

    /**
     * Registers the main WordPress hooks.
     *
     * @return void
     */
    private function register_hooks(): void {
        // Activation/deactivation hooks
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

        // Add settings link to the plugins list page
        add_filter( 'plugin_action_links_' . ASTROLOGER_API_PLUGIN_BASENAME, array( $this, 'add_settings_link' ) );
    }

    /**
     * Runs on plugin activation.
     * Creates default options in the database.
     *
     * @return void
     */
    public function activate(): void {
        // Set default options if they do not exist yet
        if ( false === get_option( 'astrologer_api_settings' ) ) {
            $defaults = array(
                'rapidapi_key'      => '',
                'geonames_username' => '',
                'base_url'          => 'https://astrologer.p.rapidapi.com',
                'language'          => 'EN',
                'house_system'      => 'P',
                'theme'             => 'classic',
                'sidereal'          => false,
                'sidereal_mode'     => 'LAHIRI',
            );
            add_option( 'astrologer_api_settings', $defaults );
        }

        // Flush rewrite rules for the new REST endpoints
        flush_rewrite_rules();
    }

    /**
     * Runs on plugin deactivation.
     *
     * @return void
     */
    public function deactivate(): void {
        flush_rewrite_rules();
    }

    /**
     * Adds the "Settings" link in the plugins list page.
     *
     * @param array $links Existing links.
     * @return array Modified links.
     */
    public function add_settings_link( array $links ): array {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            admin_url( 'options-general.php?page=astrologer-api' ),
            __( 'Settings', 'astrologer-api' )
        );
        array_unshift( $links, $settings_link );
        return $links;
    }
}

/*
|--------------------------------------------------------------------------
| Plugin bootstrap
|--------------------------------------------------------------------------
*/

/**
 * Helper function to access the plugin instance.
 *
 * @return Astrologer_API_Playground
 */
function astrologer_api(): Astrologer_API_Playground {
    return Astrologer_API_Playground::get_instance();
}

// Initialize the plugin
add_action( 'plugins_loaded', 'astrologer_api' );
