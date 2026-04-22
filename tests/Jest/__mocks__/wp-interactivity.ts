/**
 * Jest mock for `@wordpress/interactivity`.
 *
 * The real package ships ESM-only `exports` and Jest's CommonJS resolver
 * cannot locate it. We never want to exercise the real reactive runtime in
 * unit tests anyway, so this shim just returns the supplied definition
 * unchanged so tests can drive `state` and `actions` directly.
 *
 * @package Astrologer\Api
 */

interface StoreDefinition {
	state?: Record< string, unknown >;
	actions?: Record< string, ( ...args: unknown[] ) => unknown >;
	callbacks?: Record< string, ( ...args: unknown[] ) => unknown >;
}

export function store< T extends StoreDefinition >(
	_namespace: string,
	definition: T
): T {
	return definition;
}

export function getContext< T = Record< string, unknown > >(): T {
	return {} as T;
}

export function getElement(): unknown {
	return null;
}

export function getConfig(): Record< string, unknown > {
	return {};
}
