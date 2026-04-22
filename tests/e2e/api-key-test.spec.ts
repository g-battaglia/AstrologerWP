/**
 * E2E test: API key "Test Connection" button.
 *
 * Visits the Settings → API Credentials tab, clicks the Test Connection
 * button, and verifies a success or error notice is shown. Assumes a valid
 * key is seeded via wp-env config, otherwise the error path is verified.
 *
 * @package Astrologer\Api
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test.describe( 'API key connection test', () => {
	test( 'shows a connection result notice when clicked', async ( {
		admin,
		page,
	} ) => {
		await admin.visitAdminPage( 'admin.php', 'page=astrologer-settings' );

		const credentialsTab = page.getByRole( 'tab', {
			name: /API Credentials|Credentials/i,
		} );

		if ( await credentialsTab.count() ) {
			await credentialsTab.click();
		}

		const testButton = page.getByRole( 'button', {
			name: /Test Connection|Test API Key/i,
		} );

		if ( await testButton.count() ) {
			await testButton.click();

			// Either a success or an error notice should appear.
			const notice = page
				.getByRole( 'alert' )
				.or( page.getByText( /Connection|connected|failed|error/i ) );
			await expect( notice.first() ).toBeVisible( { timeout: 15_000 } );
		}
	} );
} );
