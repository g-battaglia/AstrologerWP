<?php
// Admin Scripts/CSS
add_action('admin_enqueue_scripts', 'astrologer_wp_enqueue_admin_scripts');
function astrologer_wp_enqueue_admin_scripts() {
    wp_enqueue_script('astrologer-wp-admin-js', plugin_dir_url(__FILE__) . '../assets/dist/js/admin.js', array(), '1.0', true);
    wp_enqueue_style('astrologer-wp-admin-css', plugin_dir_url(__FILE__) . '../assets/dist/css/styles.css', array(), '1.0');
}

// Frontend Scripts/CSS
add_action('wp_enqueue_scripts', 'astrologer_wp_enqueue_frontend_scripts');
function astrologer_wp_enqueue_frontend_scripts() {
    wp_enqueue_script('astrologer-wp-frontend-js', plugin_dir_url(__FILE__) . '../assets/dist/js/frontend.js', array('jquery'), '1.0', true);
    wp_enqueue_style('astrologer-wp-frontend-css', plugin_dir_url(__FILE__) . '../assets/dist/css/styles.css', array(), '1.0');

    // Localize the script with new data
    wp_localize_script('astrologer-wp-frontend-js', 'astrologerWpAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php')
    ));
}
