<?php
/**
 * Server-side render for the moon-phase block.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

$source_block_id  = $attributes['sourceBlockId'] ?? '';
$display_mode     = $attributes['displayMode'] ?? 'table';
$refresh_interval = isset( $attributes['refreshInterval'] )
	? (int) $attributes['refreshInterval']
	: 3600;

if ( $refresh_interval < 0 ) {
	$refresh_interval = 0;
}
if ( $refresh_interval > 86400 ) {
	$refresh_interval = 86400;
}

$wrapper = get_block_wrapper_attributes(
	array(
		'data-wp-interactive'         => 'astrologer/moon-phase',
		'data-astrologer-source'      => esc_attr( $source_block_id ),
		'data-astrologer-mode'        => esc_attr( $display_mode ),
		'data-astrologer-refresh'     => esc_attr( (string) $refresh_interval ),
	)
);
?>
<div <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes returns escaped HTML. ?>
	data-wp-init="callbacks.init"
	data-wp-watch="callbacks.schedule"
>
	<div data-wp-bind--hidden="state.hasData">
		<p class="astrologer-placeholder">
			<span class="astrologer-moon-emoji" aria-hidden="true">&#127765;</span>
			<span><?php esc_html_e( 'Loading moon phase…', 'astrologer-api' ); ?></span>
		</p>
	</div>

	<div data-wp-bind--hidden="!state.hasData" class="astrologer-moon-phase">
		<span
			class="astrologer-moon-emoji"
			aria-hidden="true"
			data-wp-text="state.emoji"
		>&#127765;</span>
		<span class="astrologer-moon-label" data-wp-text="state.phaseName">
			<?php esc_html_e( 'Full Moon', 'astrologer-api' ); ?>
		</span>
		<span class="astrologer-moon-illumination">
			<span data-wp-text="state.illumination">0</span>%
			<?php esc_html_e( 'illuminated', 'astrologer-api' ); ?>
		</span>
	</div>
</div>
