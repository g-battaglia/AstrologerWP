/**
 * Jest setup file — suppress WordPress component deprecation warnings.
 *
 * @package Astrologer\Api
 */

beforeEach( () => {
	jest.spyOn( console, 'warn' ).mockImplementation( ( message: string ) => {
		if (
			typeof message === 'string' &&
			message.includes( 'is deprecated' )
		) {
			return;
		}
		// eslint-disable-next-line no-console
		console.warn( message );
	} );
} );
