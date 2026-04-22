<?php
/**
 * Server-side render for the elements-chart block.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

$source_block_id = $attributes['sourceBlockId'] ?? '';
$display_mode    = $attributes['displayMode'] ?? 'bar';
$wrapper         = get_block_wrapper_attributes(
	array(
		'data-wp-interactive'        => 'astrologer/chart-display',
		'data-astrologer-source'     => esc_attr( $source_block_id ),
		'data-astrologer-mode'       => esc_attr( $display_mode ),
		'data-astrologer-chart-data' => 'elements',
	)
);

$elements = array(
	'fire'  => __( 'Fire', 'astrologer-api' ),
	'earth' => __( 'Earth', 'astrologer-api' ),
	'air'   => __( 'Air', 'astrologer-api' ),
	'water' => __( 'Water', 'astrologer-api' ),
);
?>
<div <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes returns escaped HTML. ?>>
	<div data-wp-bind--hidden="state.hasData">
		<p class="astrologer-placeholder">
			<?php esc_html_e( 'No chart data available. Connect this block to a chart source.', 'astrologer-api' ); ?>
		</p>
	</div>

	<div data-wp-bind--hidden="!state.hasData" class="astrologer-elements-chart">
		<?php foreach ( $elements as $key => $label ) : ?>
			<div class="astrologer-bar-row" data-astrologer-element="<?php echo esc_attr( $key ); ?>">
				<span class="astrologer-bar-label"><?php echo esc_html( $label ); ?></span>
				<div class="astrologer-bar-track">
					<div
						class="astrologer-bar-fill astrologer-bar-<?php echo esc_attr( $key ); ?>"
						data-wp-style--width="state.elements.<?php echo esc_attr( $key ); ?>Percent"
					></div>
				</div>
				<span class="astrologer-bar-value" data-wp-text="state.elements.<?php echo esc_attr( $key ); ?>Display"></span>
			</div>
		<?php endforeach; ?>
	</div>
</div>
