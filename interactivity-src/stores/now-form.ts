/**
 * Interactivity store for the now-form block.
 *
 * Submits a location and fetches the current-moment chart.
 *
 * @package
 */

import { store } from '@wordpress/interactivity';
import { astrologerFetch } from '../lib/api';
import { emit } from '../lib/bus';
import { validateCountryCode } from '../lib/validation';

interface NowFormState {
	city: string;
	nation: string;
	isLoading: boolean;
	error: string | null;
	hasResult: boolean;
	chartHtml: string;
}

interface NowFormActions {
	updateField: (
		event: Event
	) => Generator< Promise< unknown >, void, unknown >;
	submitForm: (
		event: Event
	) => Generator< Promise< unknown >, void, unknown >;
}

interface NowResponse {
	chart?: { svg?: string };
	svg?: string;
	html?: string;
	positions?: unknown[];
	aspects?: unknown[];
}

interface NowFormGlobal {
	restUrl?: string;
	nonce?: string;
}

const initialState: NowFormState = {
	city: '',
	nation: '',
	isLoading: false,
	error: null,
	hasResult: false,
	chartHtml: '',
};

function getNonce(): string {
	const globals = (
		globalThis as unknown as {
			astrologerNowForm?: NowFormGlobal;
		}
	 ).astrologerNowForm;
	return globals?.nonce ?? '';
}

const { state } = store< { state: NowFormState; actions: NowFormActions } >(
	'astrologer/now-form',
	{
		state: initialState,
		actions: {
			*updateField(
				event: Event
			): Generator< Promise< unknown >, void, unknown > {
				const target = event.target as HTMLInputElement | null;
				if ( ! target ) {
					return;
				}
				const field = target.getAttribute( 'name' );
				if ( ! field || ! ( field in state ) ) {
					return;
				}
				( state as unknown as Record< string, string > )[ field ] =
					target.value;
			},
			*submitForm(
				event: Event
			): Generator< Promise< unknown >, void, unknown > {
				event.preventDefault();

				if ( ! state.city || state.city.trim().length === 0 ) {
					state.error = 'City is required';
					return;
				}
				const nationErr = validateCountryCode( state.nation );
				if ( nationErr ) {
					state.error = nationErr;
					return;
				}

				state.error = null;
				state.isLoading = true;

				try {
					const data = ( yield astrologerFetch< NowResponse >(
						'now-chart',
						{
							city: state.city,
							nation: state.nation.toUpperCase(),
						},
						getNonce()
					) ) as NowResponse;

					const svg = data.chart?.svg ?? data.svg ?? '';
					state.chartHtml = data.html ?? svg ?? '';
					state.hasResult = true;

					emit( 'astrologer:chart-calculated', {
						chartType: 'now',
						svg,
						positions: data.positions ?? [],
						aspects: data.aspects ?? [],
						raw: data,
					} );
				} catch ( err ) {
					state.error =
						err instanceof Error ? err.message : 'Request failed';
				} finally {
					state.isLoading = false;
				}
			},
		},
	}
);

export { state };
