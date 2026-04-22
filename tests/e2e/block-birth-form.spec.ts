/**
 * E2E smoke test for the birth-form block.
 *
 * Inserts the block in the post editor, edits inspector controls, saves, and
 * verifies the block renders on the frontend.
 *
 * Requires wp-env running (make up).
 *
 * @package Astrologer\Api
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test.describe( 'Astrologer Birth Form block', () => {
	test( 'inserts, configures, saves, and renders on the frontend', async ( {
		admin,
		editor,
		page,
	} ) => {
		await admin.createNewPost( {
			postType: 'page',
			title: 'Birth Form Block Test',
		} );

		await editor.insertBlock( {
			name: 'astrologer-api/birth-form',
		} );

		// Placeholder is visible in the editor.
		const placeholder = page.locator(
			'.wp-block-astrologer-api-birth-form'
		);
		await expect( placeholder ).toBeVisible();
		await expect( placeholder ).toContainText( /Birth Form/i );

		// Open the Inspector sidebar and change the UI Level select.
		await editor.openDocumentSettingsSidebar();

		const uiLevelSelect = page.getByLabel( /UI Level/i );
		await expect( uiLevelSelect ).toBeVisible();
		await uiLevelSelect.selectOption( 'advanced' );

		// Inspector preview updates to reflect the new value.
		await expect( placeholder ).toContainText( /UI Level:\s*advanced/i );

		await editor.publishPost();

		// Visit the published page and assert the frontend render wrapper is present.
		const previewPage = await editor.openPreviewPage();
		await previewPage.waitForLoadState( 'domcontentloaded' );

		const frontendBlock = previewPage.locator(
			'.wp-block-astrologer-api-birth-form'
		);
		await expect( frontendBlock ).toBeVisible();
	} );
} );
