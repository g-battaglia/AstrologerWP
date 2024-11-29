<?php
// Admin Scripts/CSS
add_action('admin_enqueue_scripts', 'my_plugin_enqueue_admin_scripts');
function my_plugin_enqueue_admin_scripts() {
    wp_enqueue_script('my-plugin-admin-js', plugin_dir_url(__FILE__) . '../assets/dist/js/admin.js', array(), '1.0');
    wp_enqueue_style('my-plugin-admin-css', plugin_dir_url(__FILE__) . '../assets/dist/css/styles.css', array(), '1.0');
}

// Frontend Scripts/CSS
add_action('wp_enqueue_scripts', 'my_plugin_enqueue_frontend_scripts');
function my_plugin_enqueue_frontend_scripts() {
    wp_enqueue_script('my-plugin-frontend-js', plugin_dir_url(__FILE__) . '../assets/dist/js/frontend.js', array(), '1.0');
    wp_enqueue_style('my-plugin-frontend-css', plugin_dir_url(__FILE__) . '../assets/dist/css/styles.css', array(), '1.0');
}
