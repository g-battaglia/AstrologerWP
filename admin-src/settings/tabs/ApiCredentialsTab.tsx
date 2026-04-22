/**
 * API Credentials settings tab.
 *
 * @package Astrologer\Api
 */

import { TextControl, Button, Notice } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import type { AstrologerSettings } from '../types';

interface Props {
	settings: AstrologerSettings;
	onSave: ( data: Record< string, unknown > ) => Promise< void >;
	isSaving: boolean;
	testConnection: () => Promise< boolean >;
}

const ApiCredentialsTab = ( { settings, onSave, isSaving, testConnection }: Props ) => {
	const [ rapidapiKey, setRapidapiKey ] = useState( settings.rapidapi_key || '' );
	const [ geonamesUsername, setGeonamesUsername ] = useState(
		settings.geonames_username || ''
	);
	const [ testResult, setTestResult ] = useState< 'success' | 'error' | null >(
		null
	);
	const [ isTesting, setIsTesting ] = useState( false );

	const handleTest = async () => {
		setIsTesting( true );
		setTestResult( null );
		const success = await testConnection();
		setTestResult( success ? 'success' : 'error' );
		setIsTesting( false );
	};

	const handleSave = () => {
		onSave( {
			rapidapi_key: rapidapiKey,
			geonames_username: geonamesUsername,
		} );
	};

	return (
		<div className="astrologer-admin__tab-content">
			<TextControl
				label={ __( 'RapidAPI Key', 'astrologer-api' ) }
				type="password"
				value={ rapidapiKey }
				onChange={ setRapidapiKey }
				help={ __( 'Your RapidAPI key for the Astrologer API.', 'astrologer-api' ) }
				__next40pxDefaultSize
				__nextHasNoMarginBottom
			/>

			<div style={ { margin: '16px 0' } }>
				<Button
					variant="secondary"
					onClick={ handleTest }
					isBusy={ isTesting }
					disabled={ isTesting || ! rapidapiKey }
				>
					{ __( 'Test Connection', 'astrologer-api' ) }
				</Button>
			</div>

			{ testResult === 'success' && (
				<Notice status="success" isDismissible={ false }>
					{ __( 'Connection successful!', 'astrologer-api' ) }
				</Notice>
			) }

			{ testResult === 'error' && (
				<Notice status="error" isDismissible={ false }>
					{ __( 'Connection failed. Please check your API key.', 'astrologer-api' ) }
				</Notice>
			) }

			<TextControl
				label={ __( 'GeoNames Username', 'astrologer-api' ) }
				value={ geonamesUsername }
				onChange={ setGeonamesUsername }
				help={ __( 'Your GeoNames username for geocoding.', 'astrologer-api' ) }
				__next40pxDefaultSize
				__nextHasNoMarginBottom
			/>

			<div style={ { marginTop: '24px' } }>
				<Button
					variant="primary"
					onClick={ handleSave }
					isBusy={ isSaving }
					disabled={ isSaving }
				>
					{ __( 'Save Credentials', 'astrologer-api' ) }
				</Button>
			</div>
		</div>
	);
};

export default ApiCredentialsTab;
