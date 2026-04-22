/**
 * E2E test for the Setup Wizard admin page.
 *
 * @package Astrologer\Api
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test.describe( 'Setup Wizard', () => {
	test( 'should load the wizard page', async ( { admin, page } ) => {
		await admin.visitAdminPage( 'admin.php', 'page=astrologer-setup' );

		// The wizard root container should be present.
		const wizardRoot = page.locator( '#astrologer-wizard-root' );
		await expect( wizardRoot ).toBeVisible();

		// Welcome step should be visible.
		await expect(
			page.getByText( 'Welcome to Astrologer API' )
		).toBeVisible();
	} );

	test( 'should navigate through wizard steps', async ( {
		admin,
		page,
	} ) => {
		await admin.visitAdminPage( 'admin.php', 'page=astrologer-setup' );

		// Click Get Started.
		await page.getByRole( 'button', { name: 'Get Started' } ).click();

		// Should see the API Key input.
		await expect( page.getByLabel( 'RapidAPI Key' ) ).toBeVisible();
	} );
} );
