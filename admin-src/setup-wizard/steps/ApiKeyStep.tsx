/**
 * API Key step — enter and test the RapidAPI key.
 *
 * @package
 */

import { TextControl, Button, Notice } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import type { StepProps } from './types';

const ApiKeyStep = ( { data, setData, next, back }: StepProps ) => {
	const [ isTesting, setIsTesting ] = useState( false );
	const [ testResult, setTestResult ] = useState<
		'success' | 'error' | null
	>( null );

	const handleTest = async () => {
		setIsTesting( true );
		setTestResult( null );

		try {
			// Save the key first, then test.
			await apiFetch( {
				path: 'astrologer/v1/settings',
				method: 'POST',
				data: { rapidapi_key: data.rapidapi_key },
			} );

			const result = await apiFetch< { success: boolean } >( {
				path: 'astrologer/v1/settings/test-connection',
				method: 'POST',
			} );

			if ( result.success ) {
				setTestResult( 'success' );
				setTimeout( () => next(), 800 );
			} else {
				setTestResult( 'error' );
			}
		} catch {
			setTestResult( 'error' );
		} finally {
			setIsTesting( false );
		}
	};

	return (
		<div>
			<p>
				{ __(
					'Enter your RapidAPI key to connect to the Astrologer API.',
					'astrologer-api'
				) }
			</p>

			<TextControl
				label={ __( 'RapidAPI Key', 'astrologer-api' ) }
				type="password"
				value={ data.rapidapi_key }
				onChange={ ( value: string ) =>
					setData( { rapidapi_key: value } )
				}
				__next40pxDefaultSize
				__nextHasNoMarginBottom
			/>

			{ testResult === 'success' && (
				<Notice status="success" isDismissible={ false }>
					{ __( 'Connection successful!', 'astrologer-api' ) }
				</Notice>
			) }

			{ testResult === 'error' && (
				<Notice status="error" isDismissible={ false }>
					{ __(
						'Connection failed. Please check your API key.',
						'astrologer-api'
					) }
				</Notice>
			) }

			<div style={ { marginTop: '16px', display: 'flex', gap: '8px' } }>
				<Button variant="secondary" onClick={ back }>
					{ __( 'Back', 'astrologer-api' ) }
				</Button>
				<Button
					variant="primary"
					onClick={ handleTest }
					isBusy={ isTesting }
					disabled={ isTesting || ! data.rapidapi_key }
				>
					{ __( 'Test & Continue', 'astrologer-api' ) }
				</Button>
			</div>
		</div>
	);
};

export default ApiKeyStep;
