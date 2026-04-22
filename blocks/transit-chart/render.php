<?php
/**
 * Server-side render for the transit-chart block.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

$source_block_id = $attributes['sourceBlockId'] ?? '';
$show_svg        = ! empty( $attributes['showSvg'] );
$show_positions  = ! empty( $attributes['showPositions'] );
$show_aspects    = ! empty( $attributes['showAspects'] );
$chart_theme     = $attributes['chartTheme'] ?? 'classic';
$theme           = $attributes['theme'] ?? 'wheel';

$wrapper = get_block_wrapper_attributes(
	array(
		'data-wp-interactive'          => 'astrologer/chart-display',
		'data-astrologer-chart-type'   => 'transit',
		'data-astrologer-source'       => esc_attr( $source_block_id ),
		'data-astrologer-chart-theme'  => esc_attr( $chart_theme ),
		'data-astrologer-theme'        => esc_attr( $theme ),
	)
);
?>
<div <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes returns escaped HTML. ?>>
	<div class="astrologer-chart-placeholder" data-wp-bind--hidden="state.hasResult">
		<p><?php esc_html_e( 'Chart will appear here after form submission', 'astrologer-api' ); ?></p>
	</div>

	<?php if ( $show_svg ) : ?>
		<div class="astrologer-chart-svg" data-wp-bind--hidden="!state.hasResult" data-wp-html="state.chartSvg"></div>
	<?php endif; ?>

	<?php if ( $show_positions ) : ?>
		<div class="astrologer-chart-positions" data-wp-bind--hidden="!state.hasResult" data-wp-html="state.positionsHtml"></div>
	<?php endif; ?>

	<?php if ( $show_aspects ) : ?>
		<div class="astrologer-chart-aspects" data-wp-bind--hidden="!state.hasResult" data-wp-html="state.aspectsHtml"></div>
	<?php endif; ?>
</div>
