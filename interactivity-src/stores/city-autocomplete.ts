/**
 * Interactivity store for debounced city autocomplete.
 *
 * Performs a debounced GET against `/wp-json/astrologer/v1/geonames/search`
 * as the user types and exposes the result list via `state.suggestions`.
 *
 * @package
 */

import { store } from '@wordpress/interactivity';

interface CitySuggestion {
	name: string;
	country: string;
	countryCode: string;
	latitude: number;
	longitude: number;
}

interface CityAutocompleteState {
	query: string;
	suggestions: CitySuggestion[];
	isOpen: boolean;
	isLoading: boolean;
	error: string | null;
}

interface CityAutocompleteActions {
	onInput: ( event: Event ) => void;
	selectSuggestion: ( event: Event ) => void;
	close: () => void;
}

interface GeonamesGlobal {
	restUrl?: string;
	nonce?: string;
}

interface GeonamesResponse {
	results?: Array< {
		name?: string;
		country?: string;
		country_code?: string;
		lat?: number;
		lng?: number;
	} >;
}

const DEBOUNCE_MS = 300;

let debounceTimer: ReturnType< typeof setTimeout > | null = null;

function getNonce(): string {
	const globals = (
		globalThis as unknown as {
			astrologerCityAutocomplete?: GeonamesGlobal;
		}
	 ).astrologerCityAutocomplete;
	return globals?.nonce ?? '';
}

async function runSearch( query: string ): Promise< void > {
	if ( query.trim().length < 2 ) {
		state.suggestions = [];
		state.isOpen = false;
		return;
	}
	state.isLoading = true;
	state.error = null;

	try {
		const url = `/wp-json/astrologer/v1/geonames/search?q=${ encodeURIComponent(
			query
		) }`;
		const response = await globalThis.fetch( url, {
			method: 'GET',
			headers: {
				'X-WP-Nonce': getNonce(),
			},
		} );

		if ( ! response.ok ) {
			throw new Error( 'Search failed' );
		}

		const data = ( await response.json() ) as GeonamesResponse;
		state.suggestions = ( data.results ?? [] ).map( ( item ) => ( {
			name: item.name ?? '',
			country: item.country ?? '',
			countryCode: item.country_code ?? '',
			latitude: typeof item.lat === 'number' ? item.lat : 0,
			longitude: typeof item.lng === 'number' ? item.lng : 0,
		} ) );
		state.isOpen = state.suggestions.length > 0;
	} catch ( err ) {
		state.error = err instanceof Error ? err.message : 'Search failed';
		state.suggestions = [];
	} finally {
		state.isLoading = false;
	}
}

const initialState: CityAutocompleteState = {
	query: '',
	suggestions: [],
	isOpen: false,
	isLoading: false,
	error: null,
};

const { state } = store< {
	state: CityAutocompleteState;
	actions: CityAutocompleteActions;
} >( 'astrologer/city-autocomplete', {
	state: initialState,
	actions: {
		onInput( event: Event ) {
			const target = event.target as HTMLInputElement | null;
			if ( ! target ) {
				return;
			}
			state.query = target.value;

			if ( debounceTimer ) {
				clearTimeout( debounceTimer );
			}
			debounceTimer = setTimeout( () => {
				void runSearch( state.query );
			}, DEBOUNCE_MS );
		},
		selectSuggestion( event: Event ) {
			const target = event.currentTarget as HTMLElement | null;
			if ( ! target ) {
				return;
			}
			state.query = target.getAttribute( 'data-city' ) ?? state.query;
			state.isOpen = false;
		},
		close() {
			state.isOpen = false;
		},
	},
} );

export { state };
