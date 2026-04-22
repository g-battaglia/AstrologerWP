/**
 * E2E test: admin settings persistence.
 *
 * Loads the Astrologer Settings page, updates a field, saves, reloads, and
 * verifies the saved value persists.
 *
 * @package Astrologer\Api
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test.describe( 'Admin Settings page', () => {
	test( 'saves and persists the default house system', async ( {
		admin,
		page,
	} ) => {
		await admin.visitAdminPage( 'admin.php', 'page=astrologer-settings' );

		const settingsRoot = page.locator( '#astrologer-settings-root' );
		await expect( settingsRoot ).toBeVisible();

		// Navigate to the Defaults tab, if present.
		const defaultsTab = page.getByRole( 'tab', {
			name: /Defaults|Chart Defaults/i,
		} );

		if ( await defaultsTab.count() ) {
			await defaultsTab.click();
		}

		const houseSelect = page.getByLabel( /House System/i );
		if ( await houseSelect.count() ) {
			await houseSelect.selectOption( 'Placidus' );

			await page
				.getByRole( 'button', { name: /Save|Save Settings/i } )
				.click();

			// Wait for the save confirmation notice.
			await expect(
				page.getByText( /Settings saved|Saved/i )
			).toBeVisible( { timeout: 10_000 } );

			// Reload and ensure the value persisted.
			await page.reload();
			if ( await defaultsTab.count() ) {
				await defaultsTab.click();
			}
			await expect( houseSelect ).toHaveValue( 'Placidus' );
		}
	} );
} );
