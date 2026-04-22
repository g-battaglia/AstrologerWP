/**
 * Interactivity store for the lunar-return-form block.
 *
 * Submits a birth subject and target date to the lunar-return-chart
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

interface LunarReturnFormState {
	name: string;
	year: string;
	month: string;
	day: string;
	hour: string;
	minute: string;
	city: string;
	nation: string;
	targetYear: string;
	targetMonth: string;
	targetDay: string;
	isLoading: boolean;
	error: string | null;
	hasResult: boolean;
	chartHtml: string;
}

interface LunarReturnFormActions {
	updateField: (
		event: Event
	) => Generator< Promise< unknown >, void, unknown >;
	submitForm: (
		event: Event
	) => Generator< Promise< unknown >, void, unknown >;
}

interface LunarReturnResponse {
	chart?: { svg?: string };
	svg?: string;
	html?: string;
	positions?: unknown[];
	aspects?: unknown[];
}

interface LunarReturnFormGlobal {
	restUrl?: string;
	nonce?: string;
}

const initialState: LunarReturnFormState = {
	name: '',
	year: '',
	month: '',
	day: '',
	hour: '',
	minute: '',
	city: '',
	nation: '',
	targetYear: '',
	targetMonth: '',
	targetDay: '',
	isLoading: false,
	error: null,
	hasResult: false,
	chartHtml: '',
};

function getNonce(): string {
	const globals = (
		globalThis as unknown as {
			astrologerLunarReturnForm?: LunarReturnFormGlobal;
		}
	 ).astrologerLunarReturnForm;
	return globals?.nonce ?? '';
}

function validate( s: LunarReturnFormState ): string | null {
	const checks = [
		validateName( s.name ),
		validateYear( Number( s.year ) ),
		validateMonth( Number( s.month ) ),
		validateDay( Number( s.day ) ),
		validateHour( Number( s.hour ) ),
		validateMinute( Number( s.minute ) ),
		validateYear( Number( s.targetYear ) ),
		validateMonth( Number( s.targetMonth ) ),
		validateDay( Number( s.targetDay ) ),
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
	state: LunarReturnFormState;
	actions: LunarReturnFormActions;
} >( 'astrologer/lunar-return-form', {
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
				// RapidAPI expects snake_case payload field names.
				// eslint-disable-next-line camelcase
				const target_date = {
					year: Number( state.targetYear ),
					month: Number( state.targetMonth ),
					day: Number( state.targetDay ),
				};

				const data = ( yield astrologerFetch< LunarReturnResponse >(
					'lunar-return-chart',
					// eslint-disable-next-line camelcase
					{ subject, target_date },
					getNonce()
				) ) as LunarReturnResponse;

				const svg = data.chart?.svg ?? data.svg ?? '';
				state.chartHtml = data.html ?? svg ?? '';
				state.hasResult = true;

				emit( 'astrologer:chart-calculated', {
					chartType: 'lunar-return',
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
