/**
 * Unit tests for the birth-form Interactivity store.
 *
 * @package Astrologer\Api
 */

/* eslint-disable @typescript-eslint/no-explicit-any */

interface StoreRegistration {
	state: Record< string, unknown >;
	actions: Record< string, ( ...args: unknown[] ) => unknown >;
}

interface BirthFormModule {
	state: {
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
	};
	actions: {
		updateField: (
			event: Event
		) => Generator< Promise< unknown >, void, unknown >;
		submitForm: (
			event: Event
		) => Generator< Promise< unknown >, void, unknown >;
	};
}

// Mock @wordpress/interactivity's `store()` with a version that applies the
// supplied definition directly so we can drive it from tests.
jest.mock( '@wordpress/interactivity', () => {
	return {
		store: ( _namespace: string, definition: StoreRegistration ) => {
			return definition;
		},
		getContext: () => ( {} ),
	};
} );

const runGenerator = async (
	gen: Generator< Promise< unknown >, void, unknown >
): Promise< void > => {
	let next = gen.next();
	while ( ! next.done ) {
		try {
			const resolved = await Promise.resolve( next.value );
			next = gen.next( resolved );
		} catch ( err ) {
			next = gen.throw( err );
		}
	}
};

describe( 'birth-form store', () => {
	let mod: BirthFormModule;

	beforeEach( () => {
		jest.resetModules();
		( globalThis as any ).astrologerBirthForm = {
			restUrl: '/wp-json/astrologer/v1/',
			nonce: 'test-nonce',
		};
		// eslint-disable-next-line @typescript-eslint/no-require-imports
		mod = require( '../../../interactivity-src/stores/birth-form' );
	} );

	afterEach( () => {
		delete ( globalThis as any ).astrologerBirthForm;
		delete ( globalThis as any ).fetch;
	} );

	function fillValidState(): void {
		mod.state.name = 'Ada';
		mod.state.year = '1990';
		mod.state.month = '5';
		mod.state.day = '15';
		mod.state.hour = '12';
		mod.state.minute = '30';
		mod.state.city = 'London';
		mod.state.nation = 'GB';
	}

	it( 'updateField writes the input value into state', async () => {
		const event = {
			target: {
				getAttribute: ( name: string ) =>
					name === 'name' ? 'name' : null,
				value: 'Jane',
			},
		} as unknown as Event;

		await runGenerator( mod.actions.updateField( event ) );

		expect( mod.state.name ).toBe( 'Jane' );
	} );

	it( 'submitForm happy path populates chart state and clears loading', async () => {
		fillValidState();

		const response = {
			ok: true,
			status: 200,
			json: async () => ( {
				chart: { svg: '<svg></svg>' },
				html: '<div>chart</div>',
				positions: [ { name: 'Sun' } ],
				aspects: [],
			} ),
		};
		( globalThis as any ).fetch = jest.fn().mockResolvedValue( response );

		const event = { preventDefault: jest.fn() } as unknown as Event;

		await runGenerator( mod.actions.submitForm( event ) );

		expect( ( globalThis as any ).fetch ).toHaveBeenCalledWith(
			'/wp-json/astrologer/v1/birth-chart',
			expect.objectContaining( {
				method: 'POST',
				headers: expect.objectContaining( {
					'X-WP-Nonce': 'test-nonce',
				} ),
			} )
		);
		expect( mod.state.error ).toBeNull();
		expect( mod.state.hasResult ).toBe( true );
		expect( mod.state.chartHtml ).toContain( 'chart' );
		expect( mod.state.isLoading ).toBe( false );
	} );

	it( 'submitForm surfaces a validation error without calling fetch', async () => {
		// Leave state empty to trigger the name validator.
		( globalThis as any ).fetch = jest.fn();

		const event = { preventDefault: jest.fn() } as unknown as Event;

		await runGenerator( mod.actions.submitForm( event ) );

		expect( ( globalThis as any ).fetch ).not.toHaveBeenCalled();
		expect( mod.state.error ).not.toBeNull();
		expect( mod.state.hasResult ).toBe( false );
		expect( mod.state.isLoading ).toBe( false );
	} );

	it( 'submitForm records a network error on failure', async () => {
		fillValidState();

		( globalThis as any ).fetch = jest.fn().mockResolvedValue( {
			ok: false,
			status: 500,
			json: async () => ( { message: 'Server exploded' } ),
		} );

		const event = { preventDefault: jest.fn() } as unknown as Event;

		await runGenerator( mod.actions.submitForm( event ) );

		expect( mod.state.error ).toBe( 'Server exploded' );
		expect( mod.state.hasResult ).toBe( false );
		expect( mod.state.isLoading ).toBe( false );
	} );
} );
