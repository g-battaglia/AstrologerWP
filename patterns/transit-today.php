<?php
/**
 * Pattern: Transit Today.
 *
 * Transit form + chart for "today" view, with aspects to natal positions.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

defined( 'ABSPATH' ) || exit;

return '<!-- wp:group {"className":"astrologer-pattern astrologer-pattern--transit-today","layout":{"type":"constrained"}} -->
<div class="wp-block-group astrologer-pattern astrologer-pattern--transit-today"><!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">' . esc_html__( 'Today\'s Transits', 'astrologer-api' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:astrologer-api/transit-form {"uiLevel":"basic","targetBlockId":"transit-today-target"} /-->

<!-- wp:astrologer-api/transit-chart {"targetBlockId":"transit-today-target"} /-->

<!-- wp:astrologer-api/aspects-table {"targetBlockId":"transit-today-target"} /--></div>
<!-- /wp:group -->';
