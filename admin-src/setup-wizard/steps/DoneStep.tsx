/**
 * Done step — saves settings and shows completion summary.
 *
 * @package Astrologer\Api
 */

import { Button, Notice, Spinner } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import type { StepProps } from './types';

const DoneStep = ( { data }: StepProps ) => {
	const [ isSaving, setIsSaving ] = useState( true );
	const [ error, setError ] = useState< string | null >( null );
	const [ saved, setSaved ] = useState( false );

	const adminUrl = window.astrologerSettings?.adminUrl || '/wp-admin/';

	useEffect( () => {
		apiFetch( {
			path: 'astrologer/v1/settings',
			method: 'POST',
			data: {
				rapidapi_key: data.rapidapi_key,
				school: data.school,
				language: data.language,
				ui_level: data.ui_level,
				setup_completed: true,
			},
		} )
			.then( () => {
				setSaved( true );
				setIsSaving( false );
			} )
			.catch( ( err: Error ) => {
				setError( err.message );
				setIsSaving( false );
			} );
	}, [ data ] );

	if ( isSaving ) {
		return (
			<div>
				<Spinner />
				<p>{ __( 'Saving your settings...', 'astrologer-api' ) }</p>
			</div>
		);
	}

	if ( error ) {
		return (
			<Notice status="error" isDismissible={ false }>
				{ error }
			</Notice>
		);
	}

	return (
		<div>
			<h2>{ __( 'Setup Complete!', 'astrologer-api' ) }</h2>

			{ saved && (
				<Notice status="success" isDismissible={ false }>
					{ __( 'All settings have been saved.', 'astrologer-api' ) }
				</Notice>
			) }

			<h3>{ __( 'Configuration Summary', 'astrologer-api' ) }</h3>
			<ul>
				<li>
					{ `✓ ${ __( 'API Key', 'astrologer-api' ) }: ${ __( 'Configured', 'astrologer-api' ) }` }
				</li>
				<li>
					{ `✓ ${ __( 'School', 'astrologer-api' ) }: ${ data.school.replace( '_', ' ' ) }` }
				</li>
				<li>
					{ `✓ ${ __( 'Language', 'astrologer-api' ) }: ${ data.language }` }
				</li>
				<li>
					{ `✓ ${ __( 'UI Level', 'astrologer-api' ) }: ${ data.ui_level }` }
				</li>
			</ul>

			<h3>{ __( 'What\'s Next?', 'astrologer-api' ) }</h3>

			<div style={ { display: 'flex', gap: '8px', marginTop: '16px' } }>
				<Button
					variant="primary"
					href={ `${ adminUrl }post-new.php?post_type=astrologer_chart` }
				>
					{ __( 'Create Your First Chart', 'astrologer-api' ) }
				</Button>
				<Button
					variant="secondary"
					href={ `${ adminUrl }admin.php?page=astrologer-api` }
				>
					{ __( 'Go to Settings', 'astrologer-api' ) }
				</Button>
			</div>
		</div>
	);
};

export default DoneStep;
