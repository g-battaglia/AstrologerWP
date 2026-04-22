<?php
/**
 * Pattern: Daily Dashboard.
 *
 * Combined "today" snapshot: moon phase + now-chart + transits.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

defined( 'ABSPATH' ) || exit;

return '<!-- wp:group {"className":"astrologer-pattern astrologer-pattern--daily-dashboard","layout":{"type":"constrained"}} -->
<div class="wp-block-group astrologer-pattern astrologer-pattern--daily-dashboard"><!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">' . esc_html__( 'Daily Dashboard', 'astrologer-api' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column {"width":"33.33%"} -->
<div class="wp-block-column" style="flex-basis:33.33%"><!-- wp:astrologer-api/moon-phase /--></div>
<!-- /wp:column -->

<!-- wp:column {"width":"66.66%"} -->
<div class="wp-block-column" style="flex-basis:66.66%"><!-- wp:astrologer-api/now-form {"uiLevel":"basic","targetBlockId":"daily-dashboard-target"} /-->

<!-- wp:astrologer-api/now-chart {"targetBlockId":"daily-dashboard-target"} /--></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:astrologer-api/positions-table {"targetBlockId":"daily-dashboard-target"} /--></div>
<!-- /wp:group -->';
