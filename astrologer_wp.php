<?php

/**
 * Plugin Name: AstrologerWP
 * Description: Astrology Charts in WordPress.
 * Version: 0.0.1
 *
 * Author: Giacomo Battaglia
 */

if (!defined('ABSPATH')) {
    exit;
}

// Include files
require_once plugin_dir_path(__FILE__) . 'includes/utils.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin/settings.php';
require_once plugin_dir_path(__FILE__) . 'includes/shortcodes.php';
require_once plugin_dir_path(__FILE__) . 'includes/enqueue-scripts.php';
