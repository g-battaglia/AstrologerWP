/**
 * Interactivity store for spike-birth-form.
 *
 * Validates the Interactivity API pattern: form state management,
 * async fetch via generator, and SVG injection.
 *
 * @package
 */

import { store } from '@wordpress/interactivity';

interface SpikeState {
	status: 'idle' | 'submitting' | 'success' | 'error';
	isSubmitting: boolean;
	fields: {
		name: string;
		date: string;
		lat: string;
		lng: string;
	};
	svg: string;
	error: string;
}

interface SpikeActions {
	submit: (
		e: SubmitEvent
	) => Generator< Promise< unknown >, void, unknown >;
	updateField: ( e: InputEvent ) => void;
}

const { state } = store< SpikeState, SpikeActions >( 'astrologer/spike', {
	state: {
		status: 'idle',
		isSubmitting: false,
		fields: {
			name: '',
			date: '',
			lat: '',
			lng: '',
		},
		svg: '',
		error: '',
	},
	actions: {
		*submit( e: SubmitEvent ) {
			e.preventDefault();
			state.status = 'submitting';
			state.isSubmitting = true;
			state.error = '';
			state.svg = '';

			try {
				const nonce = (
					window as unknown as {
						wpApiSettings?: { nonce: string };
					}
				 ).wpApiSettings?.nonce;

				const res = ( yield fetch( '/wp-json/astrologer/v1/spike', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						...( nonce ? { 'X-WP-Nonce': nonce } : {} ),
					},
					body: JSON.stringify( state.fields ),
				} ) ) as Response;

				if ( ! res.ok ) {
					throw new Error(
						`Request failed with status ${ res.status }`
					);
				}

				const data = ( yield res.json() ) as {
					svg?: string;
					positions?: unknown[];
				};

				state.svg = data.svg ?? '';
				state.status = 'success';
			} catch ( err: unknown ) {
				state.error =
					err instanceof Error ? err.message : 'Unknown error';
				state.status = 'error';
			} finally {
				state.isSubmitting = false;
			}
		},
		updateField( e: InputEvent ) {
			const target = e.target as HTMLInputElement;
			const field = target.getAttribute( 'data-field' );
			if ( field && field in state.fields ) {
				( state.fields as Record< string, string > )[ field ] =
					target.value;
			}
		},
	},
} );
