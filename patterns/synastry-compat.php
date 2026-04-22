<?php
/**
 * Pattern: Synastry + Compatibility.
 *
 * Compatibility form with synastry chart and compatibility/relationship scores.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

defined( 'ABSPATH' ) || exit;

return '<!-- wp:group {"className":"astrologer-pattern astrologer-pattern--synastry-compat","layout":{"type":"constrained"}} -->
<div class="wp-block-group astrologer-pattern astrologer-pattern--synastry-compat"><!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">' . esc_html__( 'Relationship Compatibility', 'astrologer-api' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:astrologer-api/compatibility-form {"uiLevel":"basic","targetBlockId":"synastry-compat-target"} /-->

<!-- wp:astrologer-api/synastry-chart {"targetBlockId":"synastry-compat-target"} /-->

<!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:astrologer-api/compatibility-score {"targetBlockId":"synastry-compat-target"} /--></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:astrologer-api/relationship-score {"targetBlockId":"synastry-compat-target"} /--></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->';
