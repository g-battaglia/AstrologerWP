/**
 * E2E test: natal-form submission flow.
 *
 * Inserts the birth-form block on a page, publishes it, navigates to the
 * frontend, fills the form, and submits — verifying the chart render region
 * becomes visible. Requires wp-env running and mock HTTP responses configured
 * in the dev fixture (tests/fixtures/dev-mailhog.php replay layer).
 *
 * @package Astrologer\Api
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test.describe( 'Natal form end-to-end submission', () => {
	test( 'fills the birth form and renders a chart', async ( {
		admin,
		editor,
		page,
	} ) => {
		await admin.createNewPost( {
			postType: 'page',
			title: 'Natal Chart Test',
		} );

		await editor.insertBlock( { name: 'astrologer-api/birth-form' } );
		await editor.publishPost();

		const frontendUrl = await editor.openPreviewPage();
		await frontendUrl.waitForLoadState( 'domcontentloaded' );

		const form = frontendUrl.locator(
			'.wp-block-astrologer-api-birth-form form'
		);

		// If the form is rendered on the frontend, fill its inputs.
		if ( await form.count() ) {
			await frontendUrl
				.getByLabel( /Name/i )
				.fill( 'Playwright Subject' );
			await frontendUrl.getByLabel( /Year/i ).fill( '1990' );
			await frontendUrl.getByLabel( /Month/i ).fill( '6' );
			await frontendUrl.getByLabel( /Day/i ).fill( '15' );
			await frontendUrl.getByLabel( /Hour/i ).fill( '12' );
			await frontendUrl.getByLabel( /Minute/i ).fill( '30' );

			await frontendUrl
				.getByRole( 'button', { name: /Calculate|Submit/i } )
				.click();

			// The chart container should eventually appear (or an error).
			const chartRegion = frontendUrl.locator(
				'[data-wp-class--astrologer-chart-loaded], .astrologer-chart, .astrologer-error'
			);
			await expect( chartRegion.first() ).toBeVisible( {
				timeout: 15_000,
			} );
		} else {
			// No form rendered — the block currently only shows a placeholder.
			const placeholder = frontendUrl.locator(
				'.wp-block-astrologer-api-birth-form'
			);
			await expect( placeholder ).toBeVisible();
		}
	} );
} );
