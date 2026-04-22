<?php
/**
 * Pattern: Moon Phase Widget.
 *
 * Compact moon phase display for sidebars / widgets.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

defined( 'ABSPATH' ) || exit;

return '<!-- wp:group {"className":"astrologer-pattern astrologer-pattern--moon-phase-widget","layout":{"type":"constrained"}} -->
<div class="wp-block-group astrologer-pattern astrologer-pattern--moon-phase-widget"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">' . esc_html__( 'Current Moon Phase', 'astrologer-api' ) . '</h3>
<!-- /wp:heading -->

<!-- wp:astrologer-api/moon-phase /--></div>
<!-- /wp:group -->';
