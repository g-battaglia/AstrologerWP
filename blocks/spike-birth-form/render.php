<?php
/**
 * Server-side render callback for the spike-birth-form block.
 *
 * Outputs the form markup with Interactivity API directives.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

defined( 'ABSPATH' ) || exit;

$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'data-wp-interactive' => 'astrologer/spike',
	)
);
?>
<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() is escaped. ?>>
	<form data-wp-on--submit="actions.submit" data-wp-bind--aria-busy="state.isSubmitting" style="margin-bottom:16px;">
		<p>
			<label for="spike-name"><?php esc_html_e( 'Name', 'astrologer-api' ); ?></label><br />
			<input
				id="spike-name"
				type="text"
				data-wp-bind--value="state.fields.name"
				data-wp-on--input="actions.updateField"
				data-field="name"
				required
			/>
		</p>
		<p>
			<label for="spike-date"><?php esc_html_e( 'Date', 'astrologer-api' ); ?></label><br />
			<input
				id="spike-date"
				type="date"
				data-wp-bind--value="state.fields.date"
				data-wp-on--input="actions.updateField"
				data-field="date"
				required
			/>
		</p>
		<p>
			<label for="spike-lat"><?php esc_html_e( 'Latitude', 'astrologer-api' ); ?></label><br />
			<input
				id="spike-lat"
				type="text"
				inputmode="decimal"
				data-wp-bind--value="state.fields.lat"
				data-wp-on--input="actions.updateField"
				data-field="lat"
				required
			/>
		</p>
		<p>
			<label for="spike-lng"><?php esc_html_e( 'Longitude', 'astrologer-api' ); ?></label><br />
			<input
				id="spike-lng"
				type="text"
				inputmode="decimal"
				data-wp-bind--value="state.fields.lng"
				data-wp-on--input="actions.updateField"
				data-field="lng"
				required
			/>
		</p>
		<p>
			<button type="submit" data-wp-bind--disabled="state.isSubmitting">
				<?php esc_html_e( 'Calculate', 'astrologer-api' ); ?>
			</button>
		</p>
	</form>

	<div
		data-wp-bind--inner-html="state.svg"
		data-wp-class--hidden="!state.svg"
	></div>

	<p
		data-wp-text="state.error"
		data-wp-class--hidden="!state.error"
		role="alert"
		aria-live="assertive"
		style="color:#c00;"
	></p>
</div>
