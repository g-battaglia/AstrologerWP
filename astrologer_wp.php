<?php

/**
 * Plugin Name: AstrologerWP
 * Description: Astrology Charts in WordPress.
 * Version: 1.0.0
 *
 * Author: Giacomo Battaglia
 * Author URI: https://www.kerykeion.net/
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit;
}

// Include files
require_once plugin_dir_path(__FILE__) . 'includes/utils/KerykeionConstants.php';
require_once plugin_dir_path(__FILE__) . 'includes/utils/AstrologerApiAdapter.php';
require_once plugin_dir_path(__FILE__) . 'includes/utils/GeonamesAdapter.php';
require_once plugin_dir_path(__FILE__) . 'includes/utils/Subject.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin/settings.php';
require_once plugin_dir_path(__FILE__) . 'includes/functions.php';
require_once plugin_dir_path(__FILE__) . 'includes/shortcodes/astrologer_wp_birth_chart.php';
require_once plugin_dir_path(__FILE__) . 'includes/shortcodes/astrologer_wp_synastry_chart.php';
require_once plugin_dir_path(__FILE__) . 'includes/shortcodes/astrologer_wp_transit_chart.php';
require_once plugin_dir_path(__FILE__) . 'includes/enqueue-scripts.php';
require_once plugin_dir_path(__FILE__) . 'includes/ajax-handlers.php';
