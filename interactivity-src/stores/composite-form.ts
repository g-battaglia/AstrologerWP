/**
 * Interactivity store for the composite-form block.
 *
 * Collects two subjects and submits to the composite-chart REST endpoint.
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

interface SubjectState {
	name: string;
	year: string;
	month: string;
	day: string;
	hour: string;
	minute: string;
	city: string;
	nation: string;
}

interface CompositeFormState {
	subject1: SubjectState;
	subject2: SubjectState;
	isLoading: boolean;
	error: string | null;
	hasResult: boolean;
	chartHtml: string;
}

interface CompositeFormActions {
	updateField: (
		event: Event
	) => Generator< Promise< unknown >, void, unknown >;
	submitForm: (
		event: Event
	) => Generator< Promise< unknown >, void, unknown >;
}

interface CompositeResponse {
	chart?: { svg?: string };
	svg?: string;
	html?: string;
	positions?: unknown[];
	aspects?: unknown[];
}

interface CompositeFormGlobal {
	restUrl?: string;
	nonce?: string;
}

function emptySubject(): SubjectState {
	return {
		name: '',
		year: '',
		month: '',
		day: '',
		hour: '',
		minute: '',
		city: '',
		nation: '',
	};
}

function getNonce(): string {
	const globals = (
		globalThis as unknown as {
			astrologerCompositeForm?: CompositeFormGlobal;
		}
	 ).astrologerCompositeForm;
	return globals?.nonce ?? '';
}

function validateSubject( s: SubjectState ): string | null {
	const checks = [
		validateName( s.name ),
		validateYear( Number( s.year ) ),
		validateMonth( Number( s.month ) ),
		validateDay( Number( s.day ) ),
		validateHour( Number( s.hour ) ),
		validateMinute( Number( s.minute ) ),
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

function toPayload( s: SubjectState ): Record< string, unknown > {
	return {
		name: s.name,
		year: Number( s.year ),
		month: Number( s.month ),
		day: Number( s.day ),
		hour: Number( s.hour ),
		minute: Number( s.minute ),
		city: s.city,
		nation: s.nation.toUpperCase(),
	};
}

const initialState: CompositeFormState = {
	subject1: emptySubject(),
	subject2: emptySubject(),
	isLoading: false,
	error: null,
	hasResult: false,
	chartHtml: '',
};

const { state } = store< {
	state: CompositeFormState;
	actions: CompositeFormActions;
} >( 'astrologer/composite-form', {
	state: initialState,
	actions: {
		*updateField(
			event: Event
		): Generator< Promise< unknown >, void, unknown > {
			const target = event.target as HTMLInputElement | null;
			if ( ! target ) {
				return;
			}
			const name = target.getAttribute( 'name' );
			if ( ! name ) {
				return;
			}
			const [ subjectKey, field ] = name.split( '.' );
			if (
				( subjectKey !== 'subject1' && subjectKey !== 'subject2' ) ||
				! field
			) {
				return;
			}
			const subject = state[ subjectKey ] as unknown as Record<
				string,
				string
			>;
			if ( field in subject ) {
				subject[ field ] = target.value;
			}
		},
		*submitForm(
			event: Event
		): Generator< Promise< unknown >, void, unknown > {
			event.preventDefault();

			const err1 = validateSubject( state.subject1 );
			if ( err1 ) {
				state.error = `Person A: ${ err1 }`;
				return;
			}
			const err2 = validateSubject( state.subject2 );
			if ( err2 ) {
				state.error = `Person B: ${ err2 }`;
				return;
			}

			state.error = null;
			state.isLoading = true;

			try {
				const data = ( yield astrologerFetch< CompositeResponse >(
					'composite-chart',
					{
						first_subject: toPayload( state.subject1 ),
						second_subject: toPayload( state.subject2 ),
					},
					getNonce()
				) ) as CompositeResponse;

				const svg = data.chart?.svg ?? data.svg ?? '';
				state.chartHtml = data.html ?? svg ?? '';
				state.hasResult = true;

				emit( 'astrologer:chart-calculated', {
					chartType: 'composite',
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
