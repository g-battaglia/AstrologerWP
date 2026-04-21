<?php
/**
 * FilterDef value object — describes a public WordPress filter hook.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Services;

/**
 * Immutable definition of a public filter hook exposed by the plugin.
 *
 * Used by HooksRegistry as a documentation-centric index. Consumed by
 * DocumentationPage (F4/F8) and DoctorCommand (F7).
 */
final readonly class FilterDef {

	/**
	 * Constructor.
	 *
	 * @param string        $name        Hook name (e.g. 'astrologer_api/chart_request_args').
	 * @param string        $return_type The expected return type after filtering.
	 * @param list<string>  $params      Human-readable parameter signatures.
	 * @param string        $description One-sentence description of what the filter modifies.
	 */
	public function __construct(
		public string $name,
		public string $return_type,
		public array $params,
		public string $description,
	) {
	}
}
