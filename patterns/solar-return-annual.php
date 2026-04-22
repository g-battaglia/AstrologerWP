<?php
/**
 * Pattern: Solar Return Annual.
 *
 * Solar return form + chart with annual positions and distributions.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

defined( 'ABSPATH' ) || exit;

return '<!-- wp:group {"className":"astrologer-pattern astrologer-pattern--solar-return-annual","layout":{"type":"constrained"}} -->
<div class="wp-block-group astrologer-pattern astrologer-pattern--solar-return-annual"><!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">' . esc_html__( 'Annual Solar Return', 'astrologer-api' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:astrologer-api/solar-return-form {"uiLevel":"basic","targetBlockId":"solar-return-annual-target"} /-->

<!-- wp:astrologer-api/solar-return-chart {"targetBlockId":"solar-return-annual-target"} /-->

<!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:astrologer-api/elements-chart {"targetBlockId":"solar-return-annual-target"} /--></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:astrologer-api/modalities-chart {"targetBlockId":"solar-return-annual-target"} /--></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->';
