<?php
/**
 * Server-side render for the synastry-form block.
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
$layout   = $attributes['twoSubjectLayout'] ?? 'stacked';
$wrapper  = get_block_wrapper_attributes(
	array(
		'data-wp-interactive'       => 'astrologer/synastry-form',
		'data-astrologer-ui-level'  => esc_attr( $ui_level ),
		'data-astrologer-preset'    => esc_attr( $preset ),
		'data-astrologer-form-type' => 'synastry',
		'data-astrologer-layout'    => esc_attr( $layout ),
	)
);
?>
<div <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes returns escaped HTML. ?>>
	<form data-wp-on--submit="actions.submitForm" data-wp-bind--aria-busy="state.isLoading">
		<div class="astrologer-subjects astrologer-subjects--<?php echo esc_attr( $layout ); ?>">
			<fieldset>
				<legend><?php esc_html_e( 'Person A', 'astrologer-api' ); ?></legend>

				<label for="ast-synastry-s1-name"><?php esc_html_e( 'Name', 'astrologer-api' ); ?></label>
				<input type="text" id="ast-synastry-s1-name" name="subject1.name" data-wp-bind--value="state.subject1.name" data-wp-on--input="actions.updateField" required />

				<label for="ast-synastry-s1-year"><?php esc_html_e( 'Year', 'astrologer-api' ); ?></label>
				<input type="number" id="ast-synastry-s1-year" name="subject1.year" min="1900" max="2100" data-wp-bind--value="state.subject1.year" data-wp-on--input="actions.updateField" required />

				<label for="ast-synastry-s1-month"><?php esc_html_e( 'Month', 'astrologer-api' ); ?></label>
				<input type="number" id="ast-synastry-s1-month" name="subject1.month" min="1" max="12" data-wp-bind--value="state.subject1.month" data-wp-on--input="actions.updateField" required />

				<label for="ast-synastry-s1-day"><?php esc_html_e( 'Day', 'astrologer-api' ); ?></label>
				<input type="number" id="ast-synastry-s1-day" name="subject1.day" min="1" max="31" data-wp-bind--value="state.subject1.day" data-wp-on--input="actions.updateField" required />

				<label for="ast-synastry-s1-hour"><?php esc_html_e( 'Hour', 'astrologer-api' ); ?></label>
				<input type="number" id="ast-synastry-s1-hour" name="subject1.hour" min="0" max="23" data-wp-bind--value="state.subject1.hour" data-wp-on--input="actions.updateField" required />

				<label for="ast-synastry-s1-minute"><?php esc_html_e( 'Minute', 'astrologer-api' ); ?></label>
				<input type="number" id="ast-synastry-s1-minute" name="subject1.minute" min="0" max="59" data-wp-bind--value="state.subject1.minute" data-wp-on--input="actions.updateField" required />

				<label for="ast-synastry-s1-city"><?php esc_html_e( 'City', 'astrologer-api' ); ?></label>
				<input type="text" id="ast-synastry-s1-city" name="subject1.city" data-wp-bind--value="state.subject1.city" data-wp-on--input="actions.updateField" required />

				<label for="ast-synastry-s1-nation"><?php esc_html_e( 'Country Code', 'astrologer-api' ); ?></label>
				<input type="text" id="ast-synastry-s1-nation" name="subject1.nation" maxlength="2" data-wp-bind--value="state.subject1.nation" data-wp-on--input="actions.updateField" required />
			</fieldset>

			<fieldset>
				<legend><?php esc_html_e( 'Person B', 'astrologer-api' ); ?></legend>

				<label for="ast-synastry-s2-name"><?php esc_html_e( 'Name', 'astrologer-api' ); ?></label>
				<input type="text" id="ast-synastry-s2-name" name="subject2.name" data-wp-bind--value="state.subject2.name" data-wp-on--input="actions.updateField" required />

				<label for="ast-synastry-s2-year"><?php esc_html_e( 'Year', 'astrologer-api' ); ?></label>
				<input type="number" id="ast-synastry-s2-year" name="subject2.year" min="1900" max="2100" data-wp-bind--value="state.subject2.year" data-wp-on--input="actions.updateField" required />

				<label for="ast-synastry-s2-month"><?php esc_html_e( 'Month', 'astrologer-api' ); ?></label>
				<input type="number" id="ast-synastry-s2-month" name="subject2.month" min="1" max="12" data-wp-bind--value="state.subject2.month" data-wp-on--input="actions.updateField" required />

				<label for="ast-synastry-s2-day"><?php esc_html_e( 'Day', 'astrologer-api' ); ?></label>
				<input type="number" id="ast-synastry-s2-day" name="subject2.day" min="1" max="31" data-wp-bind--value="state.subject2.day" data-wp-on--input="actions.updateField" required />

				<label for="ast-synastry-s2-hour"><?php esc_html_e( 'Hour', 'astrologer-api' ); ?></label>
				<input type="number" id="ast-synastry-s2-hour" name="subject2.hour" min="0" max="23" data-wp-bind--value="state.subject2.hour" data-wp-on--input="actions.updateField" required />

				<label for="ast-synastry-s2-minute"><?php esc_html_e( 'Minute', 'astrologer-api' ); ?></label>
				<input type="number" id="ast-synastry-s2-minute" name="subject2.minute" min="0" max="59" data-wp-bind--value="state.subject2.minute" data-wp-on--input="actions.updateField" required />

				<label for="ast-synastry-s2-city"><?php esc_html_e( 'City', 'astrologer-api' ); ?></label>
				<input type="text" id="ast-synastry-s2-city" name="subject2.city" data-wp-bind--value="state.subject2.city" data-wp-on--input="actions.updateField" required />

				<label for="ast-synastry-s2-nation"><?php esc_html_e( 'Country Code', 'astrologer-api' ); ?></label>
				<input type="text" id="ast-synastry-s2-nation" name="subject2.nation" maxlength="2" data-wp-bind--value="state.subject2.nation" data-wp-on--input="actions.updateField" required />
			</fieldset>
		</div>

		<button type="submit" data-wp-bind--disabled="state.isLoading">
			<?php esc_html_e( 'Calculate Synastry', 'astrologer-api' ); ?>
		</button>

		<div data-wp-bind--hidden="!state.error" role="alert" aria-live="assertive">
			<p data-wp-text="state.error"></p>
		</div>
	</form>

	<div data-wp-bind--hidden="!state.hasResult" data-astrologer-chart-type="synastry" class="astrologer-chart-result">
		<div data-wp-html="state.chartHtml"></div>
	</div>
</div>
