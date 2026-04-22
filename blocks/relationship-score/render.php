<?php
/**
 * Server-side render for the relationship-score block.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

$source_block_id = $attributes['sourceBlockId'] ?? '';
$display_mode    = $attributes['displayMode'] ?? 'table';
$wrapper         = get_block_wrapper_attributes(
	array(
		'data-wp-interactive'        => 'astrologer/chart-display',
		'data-astrologer-source'     => esc_attr( $source_block_id ),
		'data-astrologer-mode'       => esc_attr( $display_mode ),
		'data-astrologer-chart-data' => 'relationship',
	)
);
?>
<div <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes returns escaped HTML. ?>>
	<div data-wp-bind--hidden="state.hasData">
		<p class="astrologer-placeholder">
			<?php esc_html_e( 'No relationship data available. Connect this block to a relationship source.', 'astrologer-api' ); ?>
		</p>
	</div>

	<div data-wp-bind--hidden="!state.hasData" class="astrologer-score-card">
		<div class="astrologer-score-heading">
			<?php esc_html_e( 'Relationship Score', 'astrologer-api' ); ?>
		</div>
		<div class="astrologer-score-value" data-wp-text="state.score"></div>
		<div class="astrologer-score-label" data-wp-text="state.scoreLabel"></div>
		<p class="astrologer-score-context" data-wp-text="state.context"></p>
	</div>
</div>
