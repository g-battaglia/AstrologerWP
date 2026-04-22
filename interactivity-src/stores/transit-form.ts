/**
 * Interactivity store for the transit-form block.
 *
 * Submits a single natal subject and transit moment to the transit-chart
 * REST endpoint.
 *
 * @package
 */

import { store } from '@wordpress/interactivity';
import { astrologerFetch } from '../lib/api';
import { emit } from '../lib/bus';
import {
	validateCountryCode,
	validateDay,
	validateHour,
	validateMinute,
	validateMonth,
	validateName,
	validateYear,
} from '../lib/validation';

interface TransitFormState {
	name: string;
	year: string;
	month: string;
	day: string;
	hour: string;
	minute: string;
	city: string;
	nation: string;
	transitYear: string;
	transitMonth: string;
	transitDay: string;
	transitHour: string;
	transitMinute: string;
	isLoading: boolean;
	error: string | null;
	hasResult: boolean;
	chartHtml: string;
}

interface TransitFormActions {
	updateField: (
		event: Event
	) => Generator< Promise< unknown >, void, unknown >;
	submitForm: (
		event: Event
	) => Generator< Promise< unknown >, void, unknown >;
}

interface TransitResponse {
	chart?: { svg?: string };
	svg?: string;
	html?: string;
	positions?: unknown[];
	aspects?: unknown[];
}

interface TransitFormGlobal {
	restUrl?: string;
	nonce?: string;
}

const initialState: TransitFormState = {
	name: '',
	year: '',
	month: '',
	day: '',
	hour: '',
	minute: '',
	city: '',
	nation: '',
	transitYear: '',
	transitMonth: '',
	transitDay: '',
	transitHour: '',
	transitMinute: '',
	isLoading: false,
	error: null,
	hasResult: false,
	chartHtml: '',
};

function getNonce(): string {
	const globals = (
		globalThis as unknown as {
			astrologerTransitForm?: TransitFormGlobal;
		}
	 ).astrologerTransitForm;
	return globals?.nonce ?? '';
}

function validate( s: TransitFormState ): string | null {
	const checks: Array< string | null > = [
		validateName( s.name ),
		validateYear( Number( s.year ) ),
		validateMonth( Number( s.month ) ),
		validateDay( Number( s.day ) ),
		validateHour( Number( s.hour ) ),
		validateMinute( Number( s.minute ) ),
		validateYear( Number( s.transitYear ) ),
		validateMonth( Number( s.transitMonth ) ),
		validateDay( Number( s.transitDay ) ),
		validateHour( Number( s.transitHour ) ),
		validateMinute( Number( s.transitMinute ) ),
		validateCountryCode( s.nation ),
	];
	for ( const err of checks ) {
		if ( err ) {
			return err;
		}
	}
	if ( ! s.city || s.city.trim().length === 0 ) {
		return 'City is required';
	}
	return null;
}

const { state } = store< {
	state: TransitFormState;
	actions: TransitFormActions;
} >( 'astrologer/transit-form', {
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

			const error = validate( state );
			if ( error ) {
				state.error = error;
				return;
			}

			state.error = null;
			state.isLoading = true;

			try {
				const subject = {
					name: state.name,
					year: Number( state.year ),
					month: Number( state.month ),
					day: Number( state.day ),
					hour: Number( state.hour ),
					minute: Number( state.minute ),
					city: state.city,
					nation: state.nation.toUpperCase(),
				};
				const transit = {
					year: Number( state.transitYear ),
					month: Number( state.transitMonth ),
					day: Number( state.transitDay ),
					hour: Number( state.transitHour ),
					minute: Number( state.transitMinute ),
					city: state.city,
					nation: state.nation.toUpperCase(),
				};

				const data = ( yield astrologerFetch< TransitResponse >(
					'transit-chart',
					{ subject, transit },
					getNonce()
				) ) as TransitResponse;

				const svg = data.chart?.svg ?? data.svg ?? '';
				state.chartHtml = data.html ?? svg ?? '';
				state.hasResult = true;

				emit( 'astrologer:chart-calculated', {
					chartType: 'transit',
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
} );

export { state };
