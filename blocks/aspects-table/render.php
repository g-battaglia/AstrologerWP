<?php
/**
 * Server-side render for the aspects-table block.
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
		'data-astrologer-chart-data' => 'aspects',
	)
);
?>
<div <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes returns escaped HTML. ?>>
	<div data-wp-bind--hidden="state.hasData">
		<p class="astrologer-placeholder">
			<?php esc_html_e( 'No aspects data available. Connect this block to a chart source.', 'astrologer-api' ); ?>
		</p>
	</div>

	<div data-wp-bind--hidden="!state.hasData">
		<table class="widefat astrologer-aspects-table">
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Planet A', 'astrologer-api' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Planet B', 'astrologer-api' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Aspect', 'astrologer-api' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Orb', 'astrologer-api' ); ?></th>
				</tr>
			</thead>
			<tbody data-wp-each--aspect="state.aspects">
				<tr>
					<td data-wp-text="context.aspect.planetA"></td>
					<td data-wp-text="context.aspect.planetB"></td>
					<td data-wp-text="context.aspect.type"></td>
					<td data-wp-text="context.aspect.orb"></td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
