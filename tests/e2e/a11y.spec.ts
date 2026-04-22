/**
 * Accessibility audit — runs axe-core on public and admin surfaces.
 *
 * F8.6: exercises the birth-form block rendered on the frontend, the React
 * settings page, and the setup wizard. Failures are reported for any axe
 * violation with impact higher than "minor".
 *
 * @package Astrologer\Api
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';
import AxeBuilder from '@axe-core/playwright';
import type { Page } from '@playwright/test';

type AxeImpact = 'minor' | 'moderate' | 'serious' | 'critical';

const DEFAULT_TAGS = [ 'wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa' ];

/**
 * Run axe-core and assert no violations above the "minor" severity threshold.
 */
async function runAxe(
	page: Page,
	label: string,
	selector?: string
): Promise< void > {
	let builder = new AxeBuilder( { page } ).withTags( DEFAULT_TAGS );

	if ( selector ) {
		builder = builder.include( selector );
	}

	const results = await builder.analyze();
	const blocking = results.violations.filter( ( violation ) => {
		const impact = violation.impact as AxeImpact | null;
		return impact !== null && impact !== 'minor';
	} );

	if ( blocking.length > 0 ) {
		// eslint-disable-next-line no-console
		console.error(
			`axe violations on ${ label }:`,
			JSON.stringify( blocking, null, 2 )
		);
	}

	expect( blocking, `axe violations on ${ label }` ).toEqual( [] );
}

test.describe( 'Accessibility audit', () => {
	test( 'birth-form block renders accessibly on the frontend', async ( {
		page,
		admin,
		editor,
	} ) => {
		await admin.createNewPost( { title: 'Axe: Birth Form' } );

		await editor.insertBlock( {
			name: 'astrologer-api/birth-form',
			attributes: { uiLevel: 'basic' },
		} );

		await editor.publishPost();
		const postUrl = page.url().replace( /[?&].*/, '' );
		await page.goto( postUrl );

		await runAxe(
			page,
			'frontend birth-form',
			'.wp-block-astrologer-api-birth-form'
		);
	} );

	test( 'admin settings page is accessible', async ( { admin, page } ) => {
		await admin.visitAdminPage( 'admin.php', 'page=astrologer-api' );
		await page.waitForSelector( '#astrologer-settings-root', {
			state: 'attached',
		} );

		await runAxe( page, 'admin settings', '#astrologer-settings-root' );
	} );

	test( 'setup wizard is accessible', async ( { admin, page } ) => {
		await admin.visitAdminPage( 'admin.php', 'page=astrologer-setup' );
		await page.waitForSelector( '#astrologer-wizard-root', {
			state: 'attached',
		} );

		await runAxe( page, 'setup wizard', '#astrologer-wizard-root' );
	} );
} );
