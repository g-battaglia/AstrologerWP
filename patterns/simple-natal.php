<?php
/**
 * Pattern: Simple Natal Chart.
 *
 * Pairs a birth form with a natal chart + positions/aspects tables.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

defined( 'ABSPATH' ) || exit;

return '<!-- wp:group {"className":"astrologer-pattern astrologer-pattern--simple-natal","layout":{"type":"constrained"}} -->
<div class="wp-block-group astrologer-pattern astrologer-pattern--simple-natal"><!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">' . esc_html__( 'Your Natal Chart', 'astrologer-api' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:astrologer-api/birth-form {"uiLevel":"basic","targetBlockId":"simple-natal-target"} /-->

<!-- wp:astrologer-api/natal-chart {"targetBlockId":"simple-natal-target"} /-->

<!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:astrologer-api/positions-table {"targetBlockId":"simple-natal-target"} /--></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:astrologer-api/aspects-table {"targetBlockId":"simple-natal-target"} /--></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->';
