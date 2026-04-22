<?php
/**
 * Server-side render for the transit-form block.
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
		'data-wp-interactive'       => 'astrologer/transit-form',
		'data-astrologer-ui-level'  => esc_attr( $ui_level ),
		'data-astrologer-preset'    => esc_attr( $preset ),
		'data-astrologer-form-type' => 'transit',
	)
);
?>
<div <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes returns escaped HTML. ?>>
	<form data-wp-on--submit="actions.submitForm">
		<fieldset>
			<legend><?php esc_html_e( 'Birth Data', 'astrologer-api' ); ?></legend>

			<label for="ast-transit-name"><?php esc_html_e( 'Name', 'astrologer-api' ); ?></label>
			<input type="text" id="ast-transit-name" name="name" data-wp-bind--value="state.name" data-wp-on--input="actions.updateField" required />

			<label for="ast-transit-year"><?php esc_html_e( 'Year', 'astrologer-api' ); ?></label>
			<input type="number" id="ast-transit-year" name="year" min="1900" max="2100" data-wp-bind--value="state.year" data-wp-on--input="actions.updateField" required />

			<label for="ast-transit-month"><?php esc_html_e( 'Month', 'astrologer-api' ); ?></label>
			<input type="number" id="ast-transit-month" name="month" min="1" max="12" data-wp-bind--value="state.month" data-wp-on--input="actions.updateField" required />

			<label for="ast-transit-day"><?php esc_html_e( 'Day', 'astrologer-api' ); ?></label>
			<input type="number" id="ast-transit-day" name="day" min="1" max="31" data-wp-bind--value="state.day" data-wp-on--input="actions.updateField" required />

			<label for="ast-transit-hour"><?php esc_html_e( 'Hour', 'astrologer-api' ); ?></label>
			<input type="number" id="ast-transit-hour" name="hour" min="0" max="23" data-wp-bind--value="state.hour" data-wp-on--input="actions.updateField" required />

			<label for="ast-transit-minute"><?php esc_html_e( 'Minute', 'astrologer-api' ); ?></label>
			<input type="number" id="ast-transit-minute" name="minute" min="0" max="59" data-wp-bind--value="state.minute" data-wp-on--input="actions.updateField" required />

			<label for="ast-transit-city"><?php esc_html_e( 'City', 'astrologer-api' ); ?></label>
			<input type="text" id="ast-transit-city" name="city" data-wp-bind--value="state.city" data-wp-on--input="actions.updateField" required />

			<label for="ast-transit-nation"><?php esc_html_e( 'Country Code', 'astrologer-api' ); ?></label>
			<input type="text" id="ast-transit-nation" name="nation" maxlength="2" data-wp-bind--value="state.nation" data-wp-on--input="actions.updateField" required />
		</fieldset>

		<fieldset>
			<legend><?php esc_html_e( 'Transit Date', 'astrologer-api' ); ?></legend>

			<label for="ast-transit-t-year"><?php esc_html_e( 'Year', 'astrologer-api' ); ?></label>
			<input type="number" id="ast-transit-t-year" name="transit.year" min="1900" max="2100" data-wp-bind--value="state.transit.year" data-wp-on--input="actions.updateField" required />

			<label for="ast-transit-t-month"><?php esc_html_e( 'Month', 'astrologer-api' ); ?></label>
			<input type="number" id="ast-transit-t-month" name="transit.month" min="1" max="12" data-wp-bind--value="state.transit.month" data-wp-on--input="actions.updateField" required />

			<label for="ast-transit-t-day"><?php esc_html_e( 'Day', 'astrologer-api' ); ?></label>
			<input type="number" id="ast-transit-t-day" name="transit.day" min="1" max="31" data-wp-bind--value="state.transit.day" data-wp-on--input="actions.updateField" required />

			<label for="ast-transit-t-hour"><?php esc_html_e( 'Hour', 'astrologer-api' ); ?></label>
			<input type="number" id="ast-transit-t-hour" name="transit.hour" min="0" max="23" data-wp-bind--value="state.transit.hour" data-wp-on--input="actions.updateField" required />

			<label for="ast-transit-t-minute"><?php esc_html_e( 'Minute', 'astrologer-api' ); ?></label>
			<input type="number" id="ast-transit-t-minute" name="transit.minute" min="0" max="59" data-wp-bind--value="state.transit.minute" data-wp-on--input="actions.updateField" required />
		</fieldset>

		<button type="submit" data-wp-bind--disabled="state.isLoading">
			<?php esc_html_e( 'Calculate Transit', 'astrologer-api' ); ?>
		</button>

		<div data-wp-bind--hidden="!state.error" role="alert" aria-live="assertive">
			<p data-wp-text="state.error"></p>
		</div>
	</form>

	<div data-wp-bind--hidden="!state.hasResult" data-astrologer-chart-type="transit" class="astrologer-chart-result">
		<div data-wp-html="state.chartHtml"></div>
	</div>
</div>
