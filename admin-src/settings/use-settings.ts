/**
 * Custom hook for managing Astrologer settings via the REST API.
 *
 * @package Astrologer\Api
 */

import { useState, useEffect, useCallback } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import type { AstrologerSettings } from './types';

interface UseSettingsReturn {
	settings: AstrologerSettings | null;
	isLoading: boolean;
	isSaving: boolean;
	error: string | null;
	updateSettings: ( data: Partial< AstrologerSettings > ) => Promise< void >;
	testConnection: () => Promise< boolean >;
}

export function useSettings(): UseSettingsReturn {
	const [ settings, setSettings ] = useState< AstrologerSettings | null >(
		null
	);
	const [ isLoading, setIsLoading ] = useState( true );
	const [ isSaving, setIsSaving ] = useState( false );
	const [ error, setError ] = useState< string | null >( null );

	useEffect( () => {
		apiFetch< AstrologerSettings >( {
			path: 'astrologer/v1/settings',
		} )
			.then( ( data ) => {
				setSettings( data );
				setIsLoading( false );
			} )
			.catch( ( err: Error ) => {
				setError( err.message );
				setIsLoading( false );
			} );
	}, [] );

	const updateSettings = useCallback(
		async ( data: Partial< AstrologerSettings > ) => {
			setIsSaving( true );
			setError( null );

			try {
				const updated = await apiFetch< AstrologerSettings >( {
					path: 'astrologer/v1/settings',
					method: 'POST',
					data,
				} );
				setSettings( updated );
			} catch ( err ) {
				setError( ( err as Error ).message );
			} finally {
				setIsSaving( false );
			}
		},
		[]
	);

	const testConnection = useCallback( async (): Promise< boolean > => {
		try {
			const result = await apiFetch< { success: boolean } >( {
				path: 'astrologer/v1/settings/test-connection',
				method: 'POST',
			} );
			return result.success;
		} catch {
			return false;
		}
	}, [] );

	return {
		settings,
		isLoading,
		isSaving,
		error,
		updateSettings,
		testConnection,
	};
}
