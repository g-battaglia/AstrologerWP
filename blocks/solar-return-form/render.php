<?php
/**
 * Server-side render for the solar-return-form block.
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
		'data-wp-interactive'       => 'astrologer/solar-return-form',
		'data-astrologer-ui-level'  => esc_attr( $ui_level ),
		'data-astrologer-preset'    => esc_attr( $preset ),
		'data-astrologer-form-type' => 'solar-return',
	)
);
?>
<div <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes returns escaped HTML. ?>>
	<form data-wp-on--submit="actions.submitForm">
		<fieldset>
			<legend><?php esc_html_e( 'Birth Data', 'astrologer-api' ); ?></legend>

			<label for="ast-solar-name"><?php esc_html_e( 'Name', 'astrologer-api' ); ?></label>
			<input type="text" id="ast-solar-name" name="name" data-wp-bind--value="state.name" data-wp-on--input="actions.updateField" required />

			<label for="ast-solar-year"><?php esc_html_e( 'Year', 'astrologer-api' ); ?></label>
			<input type="number" id="ast-solar-year" name="year" min="1900" max="2100" data-wp-bind--value="state.year" data-wp-on--input="actions.updateField" required />

			<label for="ast-solar-month"><?php esc_html_e( 'Month', 'astrologer-api' ); ?></label>
			<input type="number" id="ast-solar-month" name="month" min="1" max="12" data-wp-bind--value="state.month" data-wp-on--input="actions.updateField" required />

			<label for="ast-solar-day"><?php esc_html_e( 'Day', 'astrologer-api' ); ?></label>
			<input type="number" id="ast-solar-day" name="day" min="1" max="31" data-wp-bind--value="state.day" data-wp-on--input="actions.updateField" required />

			<label for="ast-solar-hour"><?php esc_html_e( 'Hour', 'astrologer-api' ); ?></label>
			<input type="number" id="ast-solar-hour" name="hour" min="0" max="23" data-wp-bind--value="state.hour" data-wp-on--input="actions.updateField" required />

			<label for="ast-solar-minute"><?php esc_html_e( 'Minute', 'astrologer-api' ); ?></label>
			<input type="number" id="ast-solar-minute" name="minute" min="0" max="59" data-wp-bind--value="state.minute" data-wp-on--input="actions.updateField" required />

			<label for="ast-solar-city"><?php esc_html_e( 'City', 'astrologer-api' ); ?></label>
			<input type="text" id="ast-solar-city" name="city" data-wp-bind--value="state.city" data-wp-on--input="actions.updateField" required />

			<label for="ast-solar-nation"><?php esc_html_e( 'Country Code', 'astrologer-api' ); ?></label>
			<input type="text" id="ast-solar-nation" name="nation" maxlength="2" data-wp-bind--value="state.nation" data-wp-on--input="actions.updateField" required />
		</fieldset>

		<fieldset>
			<legend><?php esc_html_e( 'Return Year', 'astrologer-api' ); ?></legend>

			<label for="ast-solar-return-year"><?php esc_html_e( 'Year', 'astrologer-api' ); ?></label>
			<input type="number" id="ast-solar-return-year" name="returnYear" min="1900" max="2100" data-wp-bind--value="state.returnYear" data-wp-on--input="actions.updateField" required />
		</fieldset>

		<button type="submit" data-wp-bind--disabled="state.isLoading">
			<?php esc_html_e( 'Calculate Solar Return', 'astrologer-api' ); ?>
		</button>

		<div data-wp-bind--hidden="!state.error" role="alert" aria-live="assertive">
			<p data-wp-text="state.error"></p>
		</div>
	</form>

	<div data-wp-bind--hidden="!state.hasResult" data-astrologer-chart-type="solar-return" class="astrologer-chart-result">
		<div data-wp-html="state.chartHtml"></div>
	</div>
</div>
