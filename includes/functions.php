<?php
add_action('wp_footer', 'astrologer_wp_theme_class');
function astrologer_wp_theme_class() {
    $chart_theme = get_option('astrologer_wp__chart_theme');
    if ($chart_theme === 'dark' || $chart_theme === "dark-high-contrast") {
?>
        <script type="text/javascript">
            document.body.classList.add('astrologer-wp-dark-theme');
        </script>
<?php
    }
}
