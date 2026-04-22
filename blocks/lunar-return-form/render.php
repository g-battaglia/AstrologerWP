<?php
/**
 * Server-side render for the lunar-return-form block.
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
		'data-wp-interactive'       => 'astrologer/lunar-return-form',
		'data-astrologer-ui-level'  => esc_attr( $ui_level ),
		'data-astrologer-preset'    => esc_attr( $preset ),
		'data-astrologer-form-type' => 'lunar-return',
	)
);
?>
<div <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes returns escaped HTML. ?>>
	<form data-wp-on--submit="actions.submitForm" data-wp-bind--aria-busy="state.isLoading">
		<fieldset>
			<legend><?php esc_html_e( 'Birth Data', 'astrologer-api' ); ?></legend>

			<label for="ast-lunar-name"><?php esc_html_e( 'Name', 'astrologer-api' ); ?></label>
			<input type="text" id="ast-lunar-name" name="name" data-wp-bind--value="state.name" data-wp-on--input="actions.updateField" required />

			<label for="ast-lunar-year"><?php esc_html_e( 'Year', 'astrologer-api' ); ?></label>
			<input type="number" id="ast-lunar-year" name="year" min="1900" max="2100" data-wp-bind--value="state.year" data-wp-on--input="actions.updateField" required />

			<label for="ast-lunar-month"><?php esc_html_e( 'Month', 'astrologer-api' ); ?></label>
			<input type="number" id="ast-lunar-month" name="month" min="1" max="12" data-wp-bind--value="state.month" data-wp-on--input="actions.updateField" required />

			<label for="ast-lunar-day"><?php esc_html_e( 'Day', 'astrologer-api' ); ?></label>
			<input type="number" id="ast-lunar-day" name="day" min="1" max="31" data-wp-bind--value="state.day" data-wp-on--input="actions.updateField" required />

			<label for="ast-lunar-hour"><?php esc_html_e( 'Hour', 'astrologer-api' ); ?></label>
			<input type="number" id="ast-lunar-hour" name="hour" min="0" max="23" data-wp-bind--value="state.hour" data-wp-on--input="actions.updateField" required />

			<label for="ast-lunar-minute"><?php esc_html_e( 'Minute', 'astrologer-api' ); ?></label>
			<input type="number" id="ast-lunar-minute" name="minute" min="0" max="59" data-wp-bind--value="state.minute" data-wp-on--input="actions.updateField" required />

			<label for="ast-lunar-city"><?php esc_html_e( 'City', 'astrologer-api' ); ?></label>
			<input type="text" id="ast-lunar-city" name="city" data-wp-bind--value="state.city" data-wp-on--input="actions.updateField" required />

			<label for="ast-lunar-nation"><?php esc_html_e( 'Country Code', 'astrologer-api' ); ?></label>
			<input type="text" id="ast-lunar-nation" name="nation" maxlength="2" data-wp-bind--value="state.nation" data-wp-on--input="actions.updateField" required />
		</fieldset>

		<fieldset>
			<legend><?php esc_html_e( 'Return Date', 'astrologer-api' ); ?></legend>

			<label for="ast-lunar-r-year"><?php esc_html_e( 'Year', 'astrologer-api' ); ?></label>
			<input type="number" id="ast-lunar-r-year" name="return.year" min="1900" max="2100" data-wp-bind--value="state.return.year" data-wp-on--input="actions.updateField" required />

			<label for="ast-lunar-r-month"><?php esc_html_e( 'Month', 'astrologer-api' ); ?></label>
			<input type="number" id="ast-lunar-r-month" name="return.month" min="1" max="12" data-wp-bind--value="state.return.month" data-wp-on--input="actions.updateField" required />

			<label for="ast-lunar-r-day"><?php esc_html_e( 'Day', 'astrologer-api' ); ?></label>
			<input type="number" id="ast-lunar-r-day" name="return.day" min="1" max="31" data-wp-bind--value="state.return.day" data-wp-on--input="actions.updateField" required />

			<label for="ast-lunar-r-hour"><?php esc_html_e( 'Hour', 'astrologer-api' ); ?></label>
			<input type="number" id="ast-lunar-r-hour" name="return.hour" min="0" max="23" data-wp-bind--value="state.return.hour" data-wp-on--input="actions.updateField" required />

			<label for="ast-lunar-r-minute"><?php esc_html_e( 'Minute', 'astrologer-api' ); ?></label>
			<input type="number" id="ast-lunar-r-minute" name="return.minute" min="0" max="59" data-wp-bind--value="state.return.minute" data-wp-on--input="actions.updateField" required />
		</fieldset>

		<button type="submit" data-wp-bind--disabled="state.isLoading">
			<?php esc_html_e( 'Calculate Lunar Return', 'astrologer-api' ); ?>
		</button>

		<div data-wp-bind--hidden="!state.error" role="alert" aria-live="assertive">
			<p data-wp-text="state.error"></p>
		</div>
	</form>

	<div data-wp-bind--hidden="!state.hasResult" data-astrologer-chart-type="lunar-return" class="astrologer-chart-result">
		<div data-wp-html="state.chartHtml"></div>
	</div>
</div>
