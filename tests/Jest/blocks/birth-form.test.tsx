/**
 * Tests for the birth-form block's Edit component.
 *
 * @package Astrologer\Api
 */

import { render, screen } from '@testing-library/react';
import '@testing-library/jest-dom';
import Edit from '../../../blocks/birth-form/edit';

interface Attributes {
	uiLevel: string;
	targetBlockId: string;
	showSaveOption: boolean;
	preset: string;
	redirectAfterSubmit: string;
}

const defaultAttributes: Attributes = {
	uiLevel: 'basic',
	targetBlockId: '',
	showSaveOption: false,
	preset: 'auto',
	redirectAfterSubmit: '',
};

function renderEdit( overrides: Partial< Attributes > = {} ) {
	const setAttributes = jest.fn();
	const attributes = { ...defaultAttributes, ...overrides };
	const utils = render(
		<Edit attributes={ attributes } setAttributes={ setAttributes } />
	);
	return { ...utils, setAttributes, attributes };
}

/**
 * Explicitly acknowledge the `@wordpress/components` SelectControl bottom
 * margin deprecation that fires on first render — jest-console would
 * otherwise fail the suite because it forbids unexpected warnings.
 */
function acknowledgeDeprecationWarnings(): void {
	const calls = ( console.warn as unknown as jest.Mock ).mock?.calls ?? [];
	if (
		calls.some(
			( call ) =>
				typeof call[ 0 ] === 'string' &&
				call[ 0 ].includes( 'is deprecated since version' )
		)
	) {
		expect( console ).toHaveWarned();
	}
}

describe( 'blocks/birth-form Edit', () => {
	afterEach( () => {
		acknowledgeDeprecationWarnings();
	} );

	it( 'renders the editor placeholder with the default UI level', () => {
		renderEdit();
		expect( screen.getByText( /Birth Form/i ) ).toBeInTheDocument();
		expect( screen.getByText( /UI Level:\s*basic/i ) ).toBeInTheDocument();
	} );

	it( 'reflects a non-default uiLevel attribute in the preview', () => {
		renderEdit( { uiLevel: 'advanced' } );
		expect(
			screen.getByText( /UI Level:\s*advanced/i )
		).toBeInTheDocument();
	} );

	it( 'renders inspector controls with the preset selector', () => {
		renderEdit( { preset: 'vedic' } );
		// The SelectControl for "School Preset" should be in the document
		// with the `vedic` option selected.
		const select = screen.getByLabelText( /School Preset/i );
		expect( select ).toBeInTheDocument();
		expect( select ).toHaveValue( 'vedic' );
	} );
} );
