<?php
/**
 * Server-side render for the positions-table block.
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
		'data-astrologer-chart-data' => 'positions',
	)
);
?>
<div <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes returns escaped HTML. ?>>
	<div data-wp-bind--hidden="state.hasData">
		<p class="astrologer-placeholder">
			<?php esc_html_e( 'No chart data available. Connect this block to a chart source.', 'astrologer-api' ); ?>
		</p>
	</div>

	<div data-wp-bind--hidden="!state.hasData">
		<table class="widefat astrologer-positions-table">
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Planet', 'astrologer-api' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Sign', 'astrologer-api' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Degree', 'astrologer-api' ); ?></th>
					<th scope="col"><?php esc_html_e( 'House', 'astrologer-api' ); ?></th>
				</tr>
			</thead>
			<tbody data-wp-each--position="state.positions">
				<tr>
					<td data-wp-text="context.position.name"></td>
					<td data-wp-text="context.position.sign"></td>
					<td data-wp-text="context.position.degree"></td>
					<td data-wp-text="context.position.house"></td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
