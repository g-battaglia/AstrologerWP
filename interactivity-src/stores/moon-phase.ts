/**
 * Interactivity store for the moon-phase block.
 *
 * Fetches the current moon phase on boot and optionally auto-refreshes
 * at a configurable interval.
 *
 * @package
 */

import { store } from '@wordpress/interactivity';

interface MoonPhaseState {
	phase: string;
	emoji: string;
	illumination: number;
	isLoading: boolean;
	error: string | null;
	refreshInterval: number;
}

interface MoonPhaseActions {
	init: () => void;
	refresh: () => Generator< Promise< unknown >, void, unknown >;
}

interface MoonPhaseResponse {
	phase?: string;
	emoji?: string;
	illumination?: number;
}

interface MoonPhaseGlobal {
	restUrl?: string;
	nonce?: string;
	refreshInterval?: number;
}

function getGlobals(): MoonPhaseGlobal {
	return (
		(
			globalThis as unknown as {
				astrologerMoonPhase?: MoonPhaseGlobal;
			}
		 ).astrologerMoonPhase ?? {}
	);
}

let refreshTimer: ReturnType< typeof setInterval > | null = null;

const initialState: MoonPhaseState = {
	phase: '',
	emoji: '',
	illumination: 0,
	isLoading: false,
	error: null,
	refreshInterval: 0,
};

async function fetchCurrent(): Promise< void > {
	const globals = getGlobals();
	state.isLoading = true;
	state.error = null;
	try {
		const response = await globalThis.fetch(
			'/wp-json/astrologer/v1/moon-phase/current',
			{
				method: 'GET',
				headers: {
					'X-WP-Nonce': globals.nonce ?? '',
				},
			}
		);

		if ( ! response.ok ) {
			throw new Error( 'Request failed' );
		}

		const data = ( await response.json() ) as MoonPhaseResponse;
		state.phase = data.phase ?? '';
		state.emoji = data.emoji ?? '';
		state.illumination =
			typeof data.illumination === 'number' ? data.illumination : 0;
	} catch ( err ) {
		state.error = err instanceof Error ? err.message : 'Request failed';
	} finally {
		state.isLoading = false;
	}
}

const { state } = store< {
	state: MoonPhaseState;
	actions: MoonPhaseActions;
} >( 'astrologer/moon-phase', {
	state: initialState,
	actions: {
		init() {
			const globals = getGlobals();
			const interval = globals.refreshInterval ?? 0;
			state.refreshInterval = interval;

			void fetchCurrent();

			if ( interval > 0 ) {
				if ( refreshTimer ) {
					clearInterval( refreshTimer );
				}
				refreshTimer = setInterval( () => {
					void fetchCurrent();
				}, interval * 1000 );
			}
		},
		*refresh(): Generator< Promise< unknown >, void, unknown > {
			yield fetchCurrent();
		},
	},
} );

export { state };
