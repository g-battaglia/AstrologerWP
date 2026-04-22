/**
 * Simple pub/sub event bus for cross-store communication.
 *
 * Pure TypeScript — no WordPress dependencies. Used by Interactivity stores
 * to coordinate across namespaces (e.g. a form notifies a chart display
 * when a calculation has completed).
 *
 * @package
 */

type Handler = ( data: unknown ) => void;

const handlers: Map< string, Set< Handler > > = new Map();

/**
 * Emit an event to all subscribed handlers.
 *
 * @param event Event name.
 * @param data  Payload to pass to handlers.
 */
export function emit( event: string, data: unknown ): void {
	const set = handlers.get( event );
	if ( ! set ) {
		return;
	}
	for ( const handler of set ) {
		try {
			handler( data );
		} catch ( err ) {
			// Swallow handler errors so one bad subscriber does not break
			// the rest of the chain.
			// eslint-disable-next-line no-console
			console.error( 'astrologer bus handler error', err );
		}
	}
}

/**
 * Subscribe to an event. Returns an unsubscribe function.
 *
 * @param event   Event name.
 * @param handler Callback invoked whenever `event` is emitted.
 * @return Function that removes the subscription when called.
 */
export function on( event: string, handler: Handler ): () => void {
	let set = handlers.get( event );
	if ( ! set ) {
		set = new Set< Handler >();
		handlers.set( event, set );
	}
	set.add( handler );

	return () => {
		const current = handlers.get( event );
		if ( ! current ) {
			return;
		}
		current.delete( handler );
		if ( current.size === 0 ) {
			handlers.delete( event );
		}
	};
}
