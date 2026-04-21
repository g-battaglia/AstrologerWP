<?php
/**
 * ActionDef value object — describes a public WordPress action hook.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Services;

/**
 * Immutable definition of a public action hook exposed by the plugin.
 *
 * Used by HooksRegistry as a documentation-centric index. Consumed by
 * DocumentationPage (F4/F8) and DoctorCommand (F7).
 */
final readonly class ActionDef {

	/**
	 * Constructor.
	 *
	 * @param string        $name        Hook name (e.g. 'astrologer_api/before_chart_request').
	 * @param list<string>  $params      Human-readable parameter signatures.
	 * @param string        $description One-sentence description of when the hook fires.
	 */
	public function __construct(
		public string $name,
		public array $params,
		public string $description,
	) {
	}
}
