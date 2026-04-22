/**
 * Lightweight fetch wrapper for AstrologerWP REST calls.
 *
 * @package
 */

/**
 * POST a JSON payload to the AstrologerWP REST namespace.
 *
 * @param endpoint Endpoint relative to `/wp-json/astrologer/v1/` (no leading slash).
 * @param body     Payload serialised to JSON.
 * @param nonce    REST nonce attached as the `X-WP-Nonce` header.
 * @return Parsed JSON response on 2xx.
 */
export async function astrologerFetch< T >(
	endpoint: string,
	body: Record< string, unknown >,
	nonce: string
): Promise< T > {
	const url = `/wp-json/astrologer/v1/${ endpoint }`;

	const response = await globalThis.fetch( url, {
		method: 'POST',
		headers: {
			'Content-Type': 'application/json',
			'X-WP-Nonce': nonce,
		},
		body: JSON.stringify( body ),
	} );

	// Try to parse JSON even on error responses so we can surface the
	// server-provided message.
	let json: unknown = null;
	try {
		json = await response.json();
	} catch ( _err ) {
		json = null;
	}

	if ( ! response.ok ) {
		const message =
			json &&
			typeof json === 'object' &&
			'message' in json &&
			typeof ( json as { message?: unknown } ).message === 'string'
				? ( json as { message: string } ).message
				: 'Request failed';
		throw new Error( message );
	}

	return json as T;
}
