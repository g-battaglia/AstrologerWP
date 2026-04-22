/**
 * Input validators for Interactivity form stores.
 *
 * Each validator returns a human-readable error string or `null` if the
 * supplied value is valid.
 *
 * @package
 */

export function validateYear( y: number ): string | null {
	if ( ! Number.isFinite( y ) || ! Number.isInteger( y ) ) {
		return 'Year must be a whole number';
	}
	if ( y < 1900 || y > 2100 ) {
		return 'Year must be between 1900 and 2100';
	}
	return null;
}

export function validateMonth( m: number ): string | null {
	if ( ! Number.isFinite( m ) || ! Number.isInteger( m ) ) {
		return 'Month must be a whole number';
	}
	if ( m < 1 || m > 12 ) {
		return 'Month must be between 1 and 12';
	}
	return null;
}

export function validateDay( d: number ): string | null {
	if ( ! Number.isFinite( d ) || ! Number.isInteger( d ) ) {
		return 'Day must be a whole number';
	}
	if ( d < 1 || d > 31 ) {
		return 'Day must be between 1 and 31';
	}
	return null;
}

export function validateHour( h: number ): string | null {
	if ( ! Number.isFinite( h ) || ! Number.isInteger( h ) ) {
		return 'Hour must be a whole number';
	}
	if ( h < 0 || h > 23 ) {
		return 'Hour must be between 0 and 23';
	}
	return null;
}

export function validateMinute( m: number ): string | null {
	if ( ! Number.isFinite( m ) || ! Number.isInteger( m ) ) {
		return 'Minute must be a whole number';
	}
	if ( m < 0 || m > 59 ) {
		return 'Minute must be between 0 and 59';
	}
	return null;
}

export function validateCountryCode( c: string ): string | null {
	if ( typeof c !== 'string' ) {
		return 'Country code must be text';
	}
	if ( ! /^[A-Za-z]{2}$/.test( c ) ) {
		return 'Country code must be exactly 2 letters';
	}
	return null;
}

export function validateName( n: string ): string | null {
	if ( typeof n !== 'string' ) {
		return 'Name must be text';
	}
	const trimmed = n.trim();
	if ( trimmed.length === 0 ) {
		return 'Name is required';
	}
	if ( trimmed.length > 100 ) {
		return 'Name must be at most 100 characters';
	}
	return null;
}
