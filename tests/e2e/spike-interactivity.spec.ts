/**
 * E2E test for the spike-birth-form Interactivity API validation (F0.5).
 *
 * Validates the 5 acceptance criteria:
 * 1. Block can be inserted in the editor and published.
 * 2. Frontend renders the form with all 4 fields.
 * 3. Submit triggers POST /wp-json/astrologer/v1/spike returning JSON.
 * 4. SVG is injected via data-wp-bind--inner-html.
 * 5. No React runtime bundle is loaded on the frontend.
 *
 * Requires wp-env running (make up).
 *
 * @package
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test.describe( 'Spike Birth Form — Interactivity API', () => {
	let publishedPage: Page;

	test( 'should insert the spike block in the editor', async ( {
		admin,
		editor,
		page,
	} ) => {
		await admin.createNewPost( {
			postType: 'page',
			title: 'Spike Test Page',
		} );

		await editor.insertBlock( {
			name: 'astrologer-api/spike-birth-form',
		} );

		// Verify the editor placeholder is visible.
		const placeholder = page.locator(
			'.wp-block-astrologer-api-spike-birth-form'
		);
		await expect( placeholder ).toBeVisible();
		await expect( placeholder ).toContainText( 'Spike Birth Form' );

		// Publish the post and open the frontend preview.
		await editor.publishPost();
		publishedPage = await editor.openPreviewPage();
	} );

	test( 'should render the form on the frontend', async () => {
		// Use the page opened from the preview, or navigate manually as fallback.
		const target = publishedPage;
		await target.waitForLoadState( 'domcontentloaded' );

		// Verify the form root element with Interactivity directive.
		const root = target.locator(
			'[data-wp-interactive="astrologer/spike"]'
		);
		await expect( root ).toBeVisible();

		// Verify all 4 fields exist.
		await expect( target.locator( '#spike-name' ) ).toBeVisible();
		await expect( target.locator( '#spike-date' ) ).toBeVisible();
		await expect( target.locator( '#spike-lat' ) ).toBeVisible();
		await expect( target.locator( '#spike-lng' ) ).toBeVisible();

		// Verify submit button.
		await expect(
			target.locator(
				'[data-wp-interactive="astrologer/spike"] button[type="submit"]'
			)
		).toBeVisible();
	} );

	test( 'should submit the form and inject SVG', async ( { page } ) => {
		// Navigate to the published page.
		await page.goto( '/spike-test-page/' );

		// Fill all 4 fields.
		await page.locator( '#spike-name' ).fill( 'Test Subject' );
		await page.locator( '#spike-date' ).fill( '1990-06-15' );
		await page.locator( '#spike-lat' ).fill( '41.9028' );
		await page.locator( '#spike-lng' ).fill( '12.4964' );

		// Set up a network listener before submitting.
		const spikeResponsePromise = page.waitForResponse(
			( resp: {
				url: () => string;
				request: () => { method: () => string };
			} ) =>
				resp.url().includes( '/wp-json/astrologer/v1/spike' ) &&
				resp.request().method() === 'POST'
		);

		// Submit the form.
		await page
			.locator(
				'[data-wp-interactive="astrologer/spike"] button[type="submit"]'
			)
			.click();

		// Verify the POST was made and returned 200.
		const spikeResponse = await spikeResponsePromise;
		expect( spikeResponse.status() ).toBe( 200 );

		// Verify the response body contains svg and positions.
		const body = await spikeResponse.json();
		expect( body.svg ).toContain( '<svg' );
		expect( Array.isArray( body.positions ) ).toBe( true );

		// Verify SVG was injected into the DOM via inner-html binding.
		const svgContainer = page.locator(
			'[data-wp-bind--inner-html="state.svg"]'
		);
		await expect( svgContainer ).toBeVisible();

		// The inner HTML should contain the SVG element.
		const innerHtml = await svgContainer.evaluate(
			( el: Element ) => el.innerHTML
		);
		expect( innerHtml ).toContain( '<svg' );
		expect( innerHtml ).toContain( 'Test Subject' );
	} );

	test( 'should not load React runtime on the frontend', async ( {
		page,
	} ) => {
		// Navigate to the published page.
		await page.goto( '/spike-test-page/' );

		// Collect all script sources loaded on the frontend page.
		const scriptSrcs = await page.evaluate( () =>
			Array.from( document.querySelectorAll( 'script[src]' ) ).map(
				( el ) => ( el as HTMLScriptElement ).src
			)
		);

		// No React/ReactDOM bundles should be loaded.
		const reactBundles = scriptSrcs.filter(
			( src: string ) =>
				src.includes( 'react-dom' ) ||
				src.includes( 'react.' ) ||
				src.includes( 'react_js' )
		);
		expect(
			reactBundles,
			'No React runtime bundles should be loaded on the frontend'
		).toHaveLength( 0 );

		// The interactivity store module should be loaded.
		const interactivityModules = scriptSrcs.filter( ( src: string ) =>
			src.includes( 'spike-birth-form-view' )
		);
		expect(
			interactivityModules.length,
			'Interactivity view module should be loaded'
		).toBeGreaterThanOrEqual( 1 );
	} );

	test( 'should show error state when API is unavailable', async ( {
		page,
	} ) => {
		// Intercept the spike endpoint and force a server error.
		await page.route( '**/wp-json/astrologer/v1/spike', ( route ) =>
			route.fulfill( {
				status: 500,
				contentType: 'application/json',
				body: JSON.stringify( {
					code: 'internal_server_error',
					message: 'Internal Server Error',
					data: { status: 500 },
				} ),
			} )
		);

		await page.goto( '/spike-test-page/' );

		await page.locator( '#spike-name' ).fill( 'Error Test' );
		await page.locator( '#spike-date' ).fill( '2000-01-01' );
		await page.locator( '#spike-lat' ).fill( '0' );
		await page.locator( '#spike-lng' ).fill( '0' );

		await page
			.locator(
				'[data-wp-interactive="astrologer/spike"] button[type="submit"]'
			)
			.click();

		// Verify the error message is displayed.
		const errorEl = page.locator( '[data-wp-text="state.error"]' );
		await expect( errorEl ).toBeVisible();
		await expect( errorEl ).toContainText( 'Request failed' );

		// Clean up the route interception.
		await page.unroute( '**/wp-json/astrologer/v1/spike' );
	} );
} );
