<?php
/**
 * Server-side render for the now-form block.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

$ui_level = $attributes['uiLevel'] ?? 'basic';
$preset   = $attributes['preset'] ?? 'auto';
$wrapper  = get_block_wrapper_attributes(
	array(
		'data-wp-interactive'       => 'astrologer/now-form',
		'data-astrologer-ui-level'  => esc_attr( $ui_level ),
		'data-astrologer-preset'    => esc_attr( $preset ),
		'data-astrologer-form-type' => 'now',
	)
);
?>
<div <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes returns escaped HTML. ?>>
	<form data-wp-on--submit="actions.submitForm" data-wp-bind--aria-busy="state.isLoading">
		<fieldset>
			<legend><?php esc_html_e( 'Location', 'astrologer-api' ); ?></legend>

			<label for="ast-now-city"><?php esc_html_e( 'City', 'astrologer-api' ); ?></label>
			<input type="text" id="ast-now-city" name="city" data-wp-bind--value="state.city" data-wp-on--input="actions.updateField" required />

			<label for="ast-now-nation"><?php esc_html_e( 'Country Code', 'astrologer-api' ); ?></label>
			<input type="text" id="ast-now-nation" name="nation" maxlength="2" data-wp-bind--value="state.nation" data-wp-on--input="actions.updateField" required />
		</fieldset>

		<button type="submit" data-wp-bind--disabled="state.isLoading">
			<?php esc_html_e( 'Calculate Current Sky', 'astrologer-api' ); ?>
		</button>

		<div data-wp-bind--hidden="!state.error" role="alert" aria-live="assertive">
			<p data-wp-text="state.error"></p>
		</div>
	</form>

	<div data-wp-bind--hidden="!state.hasResult" data-astrologer-chart-type="now" class="astrologer-chart-result">
		<div data-wp-html="state.chartHtml"></div>
	</div>
</div>
