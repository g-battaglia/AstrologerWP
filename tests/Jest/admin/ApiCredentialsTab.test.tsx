/**
 * Tests for the ApiCredentialsTab component.
 *
 * @package Astrologer\Api
 */

import { render, screen, fireEvent } from '@testing-library/react';
import '@testing-library/jest-dom';
import ApiCredentialsTab from '../../../admin-src/settings/tabs/ApiCredentialsTab';
import type { AstrologerSettings } from '../../../admin-src/settings/types';

const mockSettings: AstrologerSettings = {
	has_api_key: true,
	rapidapi_key: 'test-key-123',
	geonames_username: 'testuser',
	api_base_url: 'https://api.example.com',
	language: 'EN',
	school: 'modern_western',
	ui_level: 'basic',
	chart_options: {},
	cron: {
		daily_transits: false,
		daily_moon_phase: false,
		solar_return_reminder: false,
	},
	integrations: {
		enabled: false,
	},
};

describe( 'ApiCredentialsTab', () => {
	const defaultProps = {
		settings: mockSettings,
		onSave: jest.fn().mockResolvedValue( undefined ),
		isSaving: false,
		testConnection: jest.fn().mockResolvedValue( true ),
	};

	it( 'renders API key and GeoNames username inputs', () => {
		render( <ApiCredentialsTab { ...defaultProps } /> );

		expect(
			screen.getByLabelText( /RapidAPI Key/i )
		).toBeInTheDocument();
		expect(
			screen.getByLabelText( /GeoNames Username/i )
		).toBeInTheDocument();
	} );

	it( 'saves with the rapidapi_key field', () => {
		render( <ApiCredentialsTab { ...defaultProps } /> );

		const saveButton = screen.getByRole( 'button', {
			name: /Save Credentials/i,
		} );
		fireEvent.click( saveButton );

		expect( defaultProps.onSave ).toHaveBeenCalledWith(
			expect.objectContaining( {
				rapidapi_key: 'test-key-123',
			} )
		);
	} );

	it( 'calls testConnection when test button is clicked', () => {
		render( <ApiCredentialsTab { ...defaultProps } /> );

		const testButton = screen.getByRole( 'button', {
			name: /Test Connection/i,
		} );
		fireEvent.click( testButton );

		expect( defaultProps.testConnection ).toHaveBeenCalled();
	} );
} );
