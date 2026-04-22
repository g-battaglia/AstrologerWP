/**
 * Interactivity store for the birth-form block.
 *
 * Collects birth data from the user and submits it to the birth-chart
 * REST endpoint. Emits `astrologer:chart-calculated` on the shared bus
 * so chart-display stores can pick up the result.
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

interface BirthFormState {
	name: string;
	year: string;
	month: string;
	day: string;
	hour: string;
	minute: string;
	city: string;
	nation: string;
	isLoading: boolean;
	error: string | null;
	hasResult: boolean;
	chartHtml: string;
}

interface BirthFormActions {
	updateField: (
		event: Event
	) => Generator< Promise< unknown >, void, unknown >;
	submitForm: (
		event: Event
	) => Generator< Promise< unknown >, void, unknown >;
}

interface BirthChartResponse {
	chart?: { svg?: string };
	svg?: string;
	html?: string;
	positions?: unknown[];
	aspects?: unknown[];
}

interface AstrologerBirthFormGlobal {
	restUrl?: string;
	nonce?: string;
}

const initialState: BirthFormState = {
	name: '',
	year: '',
	month: '',
	day: '',
	hour: '',
	minute: '',
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
			astrologerBirthForm?: AstrologerBirthFormGlobal;
		}
	 ).astrologerBirthForm;
	return globals?.nonce ?? '';
}

function validate( s: BirthFormState ): string | null {
	const nameErr = validateName( s.name );
	if ( nameErr ) {
		return nameErr;
	}
	const yearErr = validateYear( Number( s.year ) );
	if ( yearErr ) {
		return yearErr;
	}
	const monthErr = validateMonth( Number( s.month ) );
	if ( monthErr ) {
		return monthErr;
	}
	const dayErr = validateDay( Number( s.day ) );
	if ( dayErr ) {
		return dayErr;
	}
	const hourErr = validateHour( Number( s.hour ) );
	if ( hourErr ) {
		return hourErr;
	}
	const minuteErr = validateMinute( Number( s.minute ) );
	if ( minuteErr ) {
		return minuteErr;
	}
	if ( ! s.city || s.city.trim().length === 0 ) {
		return 'City is required';
	}
	const nationErr = validateCountryCode( s.nation );
	if ( nationErr ) {
		return nationErr;
	}
	return null;
}

const { state, actions } = store< {
	state: BirthFormState;
	actions: BirthFormActions;
} >( 'astrologer/birth-form', {
	state: initialState,
	actions: {
		*updateField(
			event: Event
		): Generator< Promise< unknown >, void, unknown > {
			const target = event.target as
				| HTMLInputElement
				| HTMLSelectElement
				| null;
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

			const validationError = validate( state );
			if ( validationError ) {
				state.error = validationError;
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

				const data = ( yield astrologerFetch< BirthChartResponse >(
					'birth-chart',
					{ subject },
					getNonce()
				) ) as BirthChartResponse;

				const svg = data.chart?.svg ?? data.svg ?? '';
				state.chartHtml = data.html ?? svg ?? '';
				state.hasResult = true;

				emit( 'astrologer:chart-calculated', {
					chartType: 'natal',
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

export { state, actions };
